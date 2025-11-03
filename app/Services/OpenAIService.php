<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    private ?string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = $this->flexSetting('openai_api_key')
            ?: config('services.openai.key', env('OPENAI_API_KEY'));
        $this->model  = $this->flexSetting('openai_model')
            ?: (config('services.openai.model', env('OPENAI_MODEL')) ?: 'gpt-4o-mini');

        Log::info('OpenAIService:init', [
            'configured' => $this->isConfigured(),
            'model' => $this->model,
        ]);
    }

    /**
     * Gera sugestões de upsell a partir do carrinho, catálogo e histórico
     * Retorna até 3 sugestões: [{product_id, name, reason, pitch}]
     */
    public function generateUpsellSuggestions(array $cartItems, array $catalog, array $pastOrders = []): array
    {
        if (!$this->isConfigured()) { Log::warning('OpenAI:upsell:not_configured'); return []; }

        $cartList = array_map(function($i){
            $item = ($i['qty'] ?? 1).'x '.($i['name'] ?? 'Item');
            if (!empty($i['variant'])) {
                $item .= ' (Variação: '.$i['variant'].')';
            }
            return $item;
        }, $cartItems);

        $catalogList = array_map(function($p){
            $item = ($p['id'] ?? '').' - '.($p['name'] ?? '');
            if (isset($p['price'])) {
                $item .= ' (R$ '.number_format((float)$p['price'],2,',','.').')';
            }
            if (!empty($p['description'])) {
                $item .= ' - '.mb_substr($p['description'], 0, 150);
            }
            return $item;
        }, $catalog);

        $historyList = [];
        foreach ($pastOrders as $o) {
            foreach (($o['items'] ?? []) as $it) {
                $historyList[] = $it['name'] ?? '';
            }
        }

        $system = 'Você é um especialista em padaria artesanal e vendas. Sua missão é sugerir produtos que REALMENTE combinam com o que o cliente já escolheu, explicando POR QUE cada sugestão faz sentido. Seja específico, convincente e use a descrição do produto para criar pitches que despertem o desejo de compra.';
        
        $user = "CARRINHO ATUAL DO CLIENTE:\n- ".implode("\n- ", $cartList).
            "\n\nCATÁLOGO DISPONÍVEL (id - nome (preço) - descrição):\n- ".implode("\n- ", $catalogList).
            (count($historyList) ? ("\n\nHISTÓRICO DE PEDIDOS ANTERIORES DO CLIENTE:\n- ".implode("\n- ", array_slice($historyList,0,20))) : '').
            "\n\nINSTRUÇÕES IMPORTANTES:\n".
            "1. Analise cuidadosamente o carrinho atual e identifique O QUE o cliente está comprando.\n".
            "2. Selecione ATÉ 3 produtos do catálogo que COMPLEMENTAM ou COMBINAM diretamente com o carrinho.\n".
            "3. Para cada sugestão, crie:\n".
            "   - 'reason': Uma explicação clara e específica (30-50 palavras) de POR QUE esse produto combina com o carrinho atual. Seja específico sobre a combinação (ex: 'Este queijo brie artesanal complementa perfeitamente seus pães de fermentação natural...').\n".
            "   - 'pitch': Uma frase de venda curta e apetitosa (15-25 palavras) destacando os benefícios e o apelo sensorial (ex: 'Queijo brie cremoso com casca branca, perfeito para acompanhar seus pães artesanais').\n".
            "4. NÃO sugira produtos sem relação clara com o carrinho.\n".
            "5. Use a descrição do produto no catálogo para criar pitches autênticos e atraentes.\n".
            "6. Se o carrinho tiver pães doces, sugira geleias, mel ou manteigas.\n".
            "7. Se o carrinho tiver pães salgados, sugira queijos, patês ou embutidos.\n".
            "8. Se o carrinho tiver café ou bebidas, sugira acompanhamentos doces ou salgados.\n\n".
            "Responda APENAS em JSON válido: [{\"product_id\": number, \"reason\": string, \"pitch\": string}]";

        Log::info('OpenAI:upsell:request', ['model'=>$this->model, 'cart_items'=>count($cartItems), 'catalog_items'=>count($catalog), 'history_orders'=>count($pastOrders)]);
        // Log do script enviado (truncado para 2k chars)
        $sysPreview = mb_substr($system, 0, 2000);
        $usrPreview = mb_substr($user, 0, 2000);
        Log::info('OpenAI:upsell:prompt', ['system'=>$sysPreview, 'user'=>$usrPreview]);

        $tokParam = str_starts_with($this->model, 'gpt-5') ? 'max_completion_tokens' : 'max_tokens';
        $tempSupported = !str_starts_with($this->model, 'gpt-5');

        $res = Http::withToken($this->apiKey)
            ->acceptJson()->asJson()
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role'=>'system','content'=>$system],
                    ['role'=>'user','content'=>$user],
                ],
                // Para modelos gpt-5, temperature não é suportado (fixo em 1)
                ...($tempSupported ? ['temperature'=>0.7] : []),
                $tokParam => 2000,
            ]);
        
        if ($res->failed()) {
            Log::error('OpenAI:upsell:failed', [
                'status'=>$res->status(), 
                'body'=>$res->body(),
                'model'=>$this->model
            ]);
            // fallback de modelo
            $fallback = in_array($this->model, ['gpt-4o','gpt-4o-mini']) ? 'gpt-4o-mini' : 'gpt-4o';
            $res = Http::withToken($this->apiKey)->acceptJson()->asJson()->post('https://api.openai.com/v1/chat/completions', [
                'model' => $fallback,
                'messages' => [ ['role'=>'system','content'=>$system], ['role'=>'user','content'=>$user] ],
                ...(str_starts_with($fallback,'gpt-5') ? [] : ['temperature'=>0.7]), 
                (str_starts_with($fallback,'gpt-5')?'max_completion_tokens':'max_tokens') => 800,
            ]);
            if ($res->failed()) { 
                Log::error('OpenAI:upsell:fallback_failed', [
                    'status'=>$res->status(),
                    'body'=>$res->body(),
                    'fallback_model'=>$fallback
                ]); 
                return []; 
            }
        }
        
        $responseData = $res->json();
        $content = data_get($responseData, 'choices.0.message.content');
        $finishReason = data_get($responseData, 'choices.0.finish_reason');
        $completionTokens = data_get($responseData, 'usage.completion_tokens', 0);
        $modelUsed = data_get($responseData, 'model', $this->model);
        
        Log::info('OpenAI:upsell:raw_content', [
            'has_content'=>!empty($content),
            'status'=>$res->status(),
            'finish_reason'=>$finishReason,
            'completion_tokens'=>$completionTokens,
            'model'=>$modelUsed,
            'content_length'=>mb_strlen($content ?? ''),
            'content_preview'=>mb_substr($content ?? '', 0, 200),
            'response_keys'=>array_keys($responseData ?? [])
        ]);
        if (is_string($content)) {
            $content = preg_replace('/^```(json)?\s*/i', '', trim($content));
            $content = preg_replace('/```\s*$/', '', trim($content));
        }
        // Se o conteúdo está vazio ou finish_reason é "length", tentar fallback de modelo
        if (!$content || $finishReason === 'length') {
            Log::warning('OpenAI:upsell:empty_content_or_truncated', [
                'finish_reason'=>$finishReason,
                'completion_tokens'=>$completionTokens,
                'model'=>$modelUsed,
                'response_status'=>$res->status(),
                'has_content'=>!empty($content),
                'response_body_preview'=>mb_substr($res->body(), 0, 500)
            ]);
            
            // Se finish_reason é "length" e temos modelo gpt-5, tentar fallback para gpt-4o
            if ($finishReason === 'length' && str_starts_with($modelUsed, 'gpt-5')) {
                Log::info('OpenAI:upsell:trying_fallback_for_length', ['from'=>$modelUsed, 'to'=>'gpt-4o']);
                $fallbackRes = Http::withToken($this->apiKey)->acceptJson()->asJson()->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o',
                    'messages' => [ ['role'=>'system','content'=>$system], ['role'=>'user','content'=>$user] ],
                    'temperature' => 0.7,
                    'max_tokens' => 2000,
                ]);
                if (!$fallbackRes->failed()) {
                    $fallbackData = $fallbackRes->json();
                    $fallbackContent = data_get($fallbackData, 'choices.0.message.content');
                    if (!empty($fallbackContent)) {
                        $content = $fallbackContent;
                        $modelUsed = 'gpt-4o';
                        Log::info('OpenAI:upsell:fallback_success', [
                            'has_content'=>!empty($content),
                            'content_length'=>mb_strlen($content),
                            'model'=>$modelUsed
                        ]);
                        // Limpar markdown se houver
                        if (is_string($content)) {
                            $content = preg_replace('/^```(json)?\s*/i', '', trim($content));
                            $content = preg_replace('/```\s*$/', '', trim($content));
                        }
                    }
                }
            }
            
            // Se ainda não temos conteúdo, usar fallback heurístico
            if (!$content) {
                Log::info('OpenAI:upsell:using_heuristic_fallback');
                // Fallback heurístico: seleciona até 3 itens do catálogo não repetidos
                $pick = [];
                foreach ($catalog as $p) {
                    if (count($pick) >= 3) break;
                    $pid = (int)($p['id'] ?? 0);
                    $price = (float)($p['price'] ?? 0);
                    if ($pid <= 0 || $price <= 0) continue; // evita itens sem preço
                    // Verificar se o produto já está no carrinho
                    $inCart = false;
                    foreach ($cartItems as $ci) {
                        if ((int)($ci['product_id'] ?? 0) === $pid) {
                            $inCart = true;
                            break;
                        }
                    }
                    if ($inCart) continue; // não sugerir o que já está no carrinho
                    $pick[] = [
                        'product_id' => $pid,
                        'reason' => 'Combina com os itens do seu carrinho.',
                        'pitch' => ($p['name'] ?? 'Produto') . ' para acompanhar seus produtos escolhidos.',
                    ];
                }
                Log::info('OpenAI:upsell:heuristic_fallback', ['suggestions_count'=>count($pick)]);
                return $pick;
            }
        }
        try {
            $json = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($json)) return [];
            return array_values(array_filter(array_map(function($s){
                return [
                    'product_id' => (int)($s['product_id'] ?? 0),
                    'reason' => trim((string)($s['reason'] ?? '')),
                    'pitch' => trim((string)($s['pitch'] ?? '')),
                ];
            }, $json), fn($r)=>$r['product_id']>0));
        } catch (\Throwable $e) {
            Log::error('OpenAI:upsell:parse_error', [
                'error'=>$e->getMessage(),
                'content_preview'=>mb_substr($content ?? '', 0, 500),
                'trace'=>$e->getTraceAsString()
            ]);
            // Fallback heurístico em caso de erro de parse
            $pick = [];
            foreach ($catalog as $p) {
                if (count($pick) >= 3) break;
                $pid = (int)($p['id'] ?? 0);
                $price = (float)($p['price'] ?? 0);
                if ($pid <= 0 || $price <= 0) continue;
                $inCart = false;
                foreach ($cartItems as $ci) {
                    if ((int)($ci['product_id'] ?? 0) === $pid) {
                        $inCart = true;
                        break;
                    }
                }
                if ($inCart) continue;
                $pick[] = [
                    'product_id' => $pid,
                    'reason' => 'Combina com os itens do seu carrinho.',
                    'pitch' => ($p['name'] ?? 'Produto') . ' para acompanhar seus produtos escolhidos.',
                ];
            }
            return $pick;
        }
    }
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Gera descrições (completa e rótulo) com base em informações completas do produto
     */
    public function generateProductDescriptions(
        string $productName, 
        string $ingredientsList = '', 
        ?string $existingDescription = null,
        ?int $weightGrams = null,
        array $variants = [],
        array $allergenNames = []
    ): array
    {
        if (!$this->isConfigured()) {
            Log::warning('OpenAI:desc:not_configured');
            return ['description'=>null, 'label'=>null];
        }

        // Construir informações sobre variantes
        $variantsInfo = '';
        if (!empty($variants)) {
            $variantList = array_map(function($v) {
                $parts = [];
                if (!empty($v['name'])) $parts[] = $v['name'];
                if (isset($v['weight_grams']) && $v['weight_grams'] > 0) {
                    $parts[] = $v['weight_grams'] . 'g';
                }
                if (isset($v['price']) && $v['price'] > 0) {
                    $parts[] = 'R$ ' . number_format($v['price'], 2, ',', '.');
                }
                return implode(' - ', $parts);
            }, array_filter($variants, fn($v) => !empty($v['name'])));
            if (!empty($variantList)) {
                $variantsInfo = "\nVariantes disponíveis: " . implode(', ', array_slice($variantList, 0, 5));
                if (count($variantList) > 5) $variantsInfo .= ' e mais...';
            }
        }

        // Construir informações de peso
        $weightInfo = '';
        if ($weightGrams && $weightGrams > 0) {
            $weightInfo = "\nPeso: {$weightGrams}g";
        }

        // Construir informações de alergênicos (da lista de ingredientes se não fornecida)
        // Formatar de acordo com normas da vigilância sanitária
        $allergenInfo = '';
        $allergenWarning = '';
        if (!empty($allergenNames)) {
            // Se temos alérgenos confirmados, formatar conforme vigilância sanitária
            $allergensFormatted = [];
            foreach ($allergenNames as $name) {
                $nameLower = mb_strtolower($name);
                if (stripos($nameLower, 'leite') !== false || stripos($nameLower, 'lactose') !== false) {
                    $allergensFormatted[] = 'LEITE E/OU DERIVADOS DO LEITE';
                } elseif (stripos($nameLower, 'ovo') !== false) {
                    $allergensFormatted[] = 'OVOS';
                } elseif (stripos($nameLower, 'trigo') !== false || stripos($nameLower, 'glúten') !== false || stripos($nameLower, 'gluten') !== false || 
                          stripos($nameLower, 'aveia') !== false || stripos($nameLower, 'cevada') !== false || stripos($nameLower, 'centeio') !== false) {
                    $allergensFormatted[] = 'TRIGO E DERIVADOS';
                } elseif (stripos($nameLower, 'soja') !== false) {
                    $allergensFormatted[] = 'DERIVADOS DE SOJA';
                } elseif (stripos($nameLower, 'amendoim') !== false) {
                    $allergensFormatted[] = 'AMENDOIM';
                } elseif (stripos($nameLower, 'castanha') !== false || stripos($nameLower, 'noz') !== false || stripos($nameLower, 'amêndoa') !== false) {
                    $allergensFormatted[] = 'CASTANHAS';
                } elseif (stripos($nameLower, 'peixe') !== false || stripos($nameLower, 'camarão') !== false || stripos($nameLower, 'crustáceos') !== false) {
                    $allergensFormatted[] = 'PEIXES E FRUTOS DO MAR';
                } else {
                    $allergensFormatted[] = mb_strtoupper($name);
                }
            }
            $allergensFormatted = array_unique($allergensFormatted);
            if (!empty($allergensFormatted)) {
                $allergenInfo = "\nAlergênicos confirmados: " . implode(', ', $allergensFormatted);
                $allergenWarning = "\nAVISO ALERGÊNICO (deve ser incluído na descrição principal): CONTÉM " . implode(". CONTÉM ", $allergensFormatted) . ". ALÉRGICOS: CONSULTE OS INGREDIENTES E ALÉRGENOS ANTES DO CONSUMO.";
            }
        } elseif (!empty($ingredientsList)) {
            // Detectar alergênicos comuns na lista de ingredientes
            $commonAllergens = [
                'glúten', 'trigo', 'aveia', 'cevada', 'centeio',
                'leite', 'lactose', 'laticínios',
                'ovo', 'ovos',
                'amendoim', 'castanha', 'amêndoa', 'nozes', 'noz',
                'soja',
                'peixe', 'frutos do mar', 'camarão', 'crustáceos'
            ];
            $ingredientsLower = mb_strtolower($ingredientsList);
            $detected = [];
            foreach ($commonAllergens as $allergen) {
                if (stripos($ingredientsLower, $allergen) !== false && !in_array($allergen, $detected)) {
                    $detected[] = ucfirst($allergen);
                }
            }
            if (!empty($detected)) {
                $allergenInfo = "\nPossíveis alergênicos detectados: " . implode(', ', array_slice($detected, 0, 5));
                // Criar aviso formatado
                $warnings = [];
                foreach ($detected as $d) {
                    $dLower = mb_strtolower($d);
                    if (stripos($dLower, 'leite') !== false || stripos($dLower, 'lactose') !== false) {
                        $warnings[] = 'LEITE E/OU DERIVADOS DO LEITE';
                    } elseif (stripos($dLower, 'ovo') !== false) {
                        $warnings[] = 'OVOS';
                    } elseif (stripos($dLower, 'trigo') !== false || stripos($dLower, 'glúten') !== false || stripos($dLower, 'gluten') !== false) {
                        $warnings[] = 'TRIGO E DERIVADOS';
                    } elseif (stripos($dLower, 'soja') !== false) {
                        $warnings[] = 'DERIVADOS DE SOJA';
                    }
                }
                if (!empty($warnings)) {
                    $warnings = array_unique($warnings);
                    $allergenWarning = "\nAVISO ALERGÊNICO (deve ser incluído na descrição principal): PODE CONTER " . implode(". PODE CONTER ", $warnings) . ". ALÉRGICOS: CONSULTE OS INGREDIENTES ANTES DO CONSUMO.";
                }
            }
        }

        // Descrição existente (se houver, para contexto)
        $descContext = '';
        if (!empty($existingDescription)) {
            $descContext = "\nDescrição existente (use como referência/contexto): {$existingDescription}";
        }

        $system = 'Você é um redator especialista em padaria artesanal. Escreva descrições curtas, apetitosas, claras e que despertem o desejo de compra. Use informações técnicas (peso, variantes, ingredientes) de forma natural no texto.';
        
        $user = "INFORMAÇÕES DO PRODUTO:\n".
            "Nome: {$productName}".
            ($weightInfo ?: '').
            ($ingredientsList ? "\nIngredientes: {$ingredientsList}" : '').
            ($variantsInfo ?: '').
            ($allergenInfo ?: '').
            ($allergenWarning ?: '').
            ($descContext ?: '').
            "\n\nTAREFAS:\n".
            "1) Descrição de venda (OBJETIVO: entre 850-900 caracteres, mínimo 800): escreva um texto acolhedor, detalhado e envolvente que desperte o desejo de compra. Destaque o caráter artesanal, o processo de produção cuidadoso, mencione ingredientes principais com destaque, peso/variantes se aplicável, textura, sabor e aroma quando relevante. Evite promessas de saúde. Se houver descrição existente, melhore-a significativamente mantendo a essência, mas tornando-a muito mais rica, detalhada e informativa. IMPORTANTE: Se houver 'AVISO ALERGÊNICO' nas informações acima, VOCÊ DEVE incluir essa informação completa no final da descrição de venda, exatamente como formatada (em letras maiúsculas ou conforme indicado), conforme exigências da vigilância sanitária. A descrição deve ter entre 850-900 caracteres incluindo o aviso alergênico.\n".
            "2) Descrição para rótulo (até 200 caracteres): texto direto, prático, mencione nome, ingredientes principais (resumidos) e peso/variantes se relevante, SEM preço e SEM aviso alergênico detalhado.\n\n".
            "Responda APENAS em JSON válido: {\"description\": string, \"label\": string}.";

        Log::info('OpenAI:desc:request', ['model'=>$this->model, 'has_name'=>trim($productName) !== '', 'ingredients_len'=>mb_strlen($ingredientsList)]);
        // Log do script enviado (truncado para 2k chars)
        $sysPreview = mb_substr($system, 0, 2000);
        $usrPreview = mb_substr($user, 0, 2000);
        Log::info('OpenAI:desc:prompt', ['system'=>$sysPreview, 'user'=>$usrPreview]);

        $tokParam = str_starts_with($this->model, 'gpt-5') ? 'max_completion_tokens' : 'max_tokens';
        $tempSupported = !str_starts_with($this->model, 'gpt-5');

        $res = Http::withToken($this->apiKey)
            ->acceptJson()
            ->asJson()
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role'=>'system','content'=>$system],
                    ['role'=>'user','content'=>$user],
                ],
                // Para modelos gpt-5, temperature não é suportado (fixo em 1)
                ...($tempSupported ? ['temperature'=>0.7] : []),
                $tokParam => 2000,
            ]);

        if ($res->failed()) {
            Log::error('OpenAI:desc:failed', ['status'=>$res->status(), 'body'=>$res->body()]);
            // fallback de modelo
            $fallback = in_array($this->model, ['gpt-4o','gpt-4o-mini']) ? 'gpt-4o-mini' : 'gpt-4o';
            $res = Http::withToken($this->apiKey)->acceptJson()->asJson()->post('https://api.openai.com/v1/chat/completions', [
                'model' => $fallback,
                'messages' => [ ['role'=>'system','content'=>$system], ['role'=>'user','content'=>$user] ],
                ...(str_starts_with($fallback,'gpt-5') ? [] : ['temperature'=>0.7]), 
                (str_starts_with($fallback,'gpt-5')?'max_completion_tokens':'max_tokens') => 2000,
            ]);
            if ($res->failed()) {
                Log::error('OpenAI:desc:fallback_failed', ['status'=>$res->status(), 'body'=>$res->body()]);
                return ['description'=>null, 'label'=>null];
            }
        }

        $responseData = $res->json();
        $content = data_get($responseData, 'choices.0.message.content');
        $finishReason = data_get($responseData, 'choices.0.finish_reason');
        $completionTokens = data_get($responseData, 'usage.completion_tokens', 0);
        
        Log::info('OpenAI:desc:raw_content', [
            'has_content'=>!empty($content),
            'status'=>$res->status(),
            'finish_reason'=>$finishReason,
            'completion_tokens'=>$completionTokens,
            'model'=>data_get($responseData, 'model'),
            'content_length'=>mb_strlen($content ?? ''),
            'content_preview'=>mb_substr($content ?? '', 0, 200),
            'full_response_keys'=>array_keys($responseData ?? [])
        ]);
        
        // Se finish_reason é "length", significa que cortou - tentar usar fallback de modelo
        if ($finishReason === 'length' && empty($content)) {
            Log::warning('OpenAI:desc:truncated_response', [
                'completion_tokens'=>$completionTokens,
                'model'=>$this->model,
                'trying_fallback'=>true
            ]);
            // Tentar com modelo fallback que suporta mais tokens
            $fallback = 'gpt-4o';
            $res = Http::withToken($this->apiKey)->acceptJson()->asJson()->post('https://api.openai.com/v1/chat/completions', [
                'model' => $fallback,
                'messages' => [ ['role'=>'system','content'=>$system], ['role'=>'user','content'=>$user] ],
                'temperature' => 0.7,
                'max_tokens' => 2000,
            ]);
            if (!$res->failed()) {
                $responseData = $res->json();
                $content = data_get($responseData, 'choices.0.message.content');
                Log::info('OpenAI:desc:fallback_success', [
                    'has_content'=>!empty($content),
                    'content_length'=>mb_strlen($content ?? '')
                ]);
            }
        }
        
        // Remove cercas ``` e marca json se vierem no conteúdo
        if (is_string($content)) {
            $content = preg_replace('/^```(json)?\s*/i', '', trim($content));
            $content = preg_replace('/```\s*$/', '', trim($content));
        }
        if (!$content) {
            Log::warning('OpenAI:desc:empty_content', [
                'response_status'=>$res->status(),
                'response_body_preview'=>mb_substr($res->body(), 0, 500),
                'finish_reason'=>$finishReason,
                'completion_tokens'=>$completionTokens
            ]);
            // Fallback determinístico quando não há conteúdo
            $ingredients = array_filter(array_map('trim', preg_split('/[,\n]+/', (string)$ingredientsList)));
            $top = implode(', ', array_slice($ingredients, 0, 5));
            $descSynth = "".$productName." artesanal, feito com cuidado e ingredientes selecionados.";
            if ($top) { $descSynth .= " Destaques: ".$top."."; }
            $labelSynth = $productName.( $top ? " — ingredientes: ".$top."." : ".");
            Log::info('OpenAI:desc:synthetic_fallback:empty_content', ['desc_len'=>mb_strlen($descSynth), 'label_len'=>mb_strlen($labelSynth)]);
            return [ 'description' => $descSynth, 'label' => $labelSynth ];
        }

        $json = null;
        try { $json = json_decode($content, true, 512, JSON_THROW_ON_ERROR); }
        catch (\Throwable $e) { Log::error('OpenAI:desc:parse_error', ['error'=>$e->getMessage(), 'raw'=>$content]); $json = null; }
        if (is_array($json)) {
            $desc  = trim((string)($json['description'] ?? '')) ?: null;
            $label = trim((string)($json['label'] ?? '')) ?: null;
            Log::info('OpenAI:desc:parsed', ['desc_len'=>mb_strlen($desc ?? ''), 'label_len'=>mb_strlen($label ?? '')]);
            return [ 'description' => $desc, 'label' => $label ];
        }

        // Fallback: tenta extrair linhas
        $parts = explode("\n", trim($content));
        $desc = trim($parts[0] ?? '');
        $label = trim($parts[1] ?? '');
        Log::info('OpenAI:desc:fallback_lines', ['desc_len'=>mb_strlen($desc), 'label_len'=>mb_strlen($label)]);
        if ($desc || $label) {
            return [ 'description' => ($desc ?: null), 'label' => ($label ?: null) ];
        }

        // Fallback determinístico quando o provider não retorna conteúdo
        $ingredients = array_filter(array_map('trim', preg_split('/[,\n]+/', (string)$ingredientsList)));
        $top = implode(', ', array_slice($ingredients, 0, 5));
        $descSynth = "".$productName." artesanal, feito com cuidado e ingredientes selecionados.";
        if ($top) { $descSynth .= " Destaques: ".$top."."; }
        $labelSynth = $productName.( $top ? " — ingredientes: ".$top."." : ".");
        Log::info('OpenAI:desc:synthetic_fallback', ['desc_len'=>mb_strlen($descSynth), 'label_len'=>mb_strlen($labelSynth)]);
        return [ 'description' => $descSynth, 'label' => $labelSynth ];
    }

    private function flexSetting(string $key): ?string
    {
        if (!Schema::hasTable('settings')) {
            // fallback payment_settings
            if (Schema::hasTable('payment_settings')) {
                $alt = DB::table('payment_settings')->where('key',$key)->value('value');
                return $alt !== null ? (string)$alt : null;
            }
            return null;
        }
        $keyCol = collect(['key','name','config_key','setting_key','option','option_name'])
            ->first(fn($c)=>Schema::hasColumn('settings',$c));
        $valCol = collect(['value','val','config_value','content','data','option_value'])
            ->first(fn($c)=>Schema::hasColumn('settings',$c));
        if (!$keyCol || !$valCol) { return null; }
        $val = DB::table('settings')->where($keyCol, $key)->value($valCol);
        if ($val === null && Schema::hasTable('payment_settings')) {
            $alt = DB::table('payment_settings')->where('key',$key)->value('value');
            return $alt !== null ? (string)$alt : null;
        }
        return $val !== null ? (string)$val : null;
    }

}
