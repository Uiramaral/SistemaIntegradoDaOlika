<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SettingsController extends Controller
{
    public function index()
    {
        // Buscar configurações do WhatsApp
        $whatsappSettings = DB::table('whatsapp_settings')->where('active', 1)->first();
        
        // Buscar configurações de pagamento
        $paymentSettings = DB::table('payment_settings')->pluck('value', 'key');
        
        // Configurações gerais da loja
        $storeSettings = [
            'store_name' => 'Olika Cozinha Artesanal',
            'store_email' => 'olikacozinhaartesanal@gmail.com',
            'store_phone' => '(71) 987019420',
            'store_cnpj' => '50.910.565/0001-84',
            'service_fee' => 0,
            'min_order' => 0,
        ];
        
        // Chaves de API + loja (free shipping) salvas em settings
        // Nota: cashback_percent removido - agora gerenciado na aba dedicada de Cashback
        // APIs (OpenAI, Google Maps, Gemini) movidas para Master → /master/settings
        $apiSettings = $this->getSettingsFromFlexibleTable([
            'free_shipping_min_total',
            'order_number_prefix','next_order_number',
            // BotConversa
            'botconversa_webhook_url','botconversa_paid_webhook_url','botconversa_token',
            // Agendamento/Entrega
            'delivery_slot_capacity','advance_order_days','default_cutoff_time',
        ]);
        
        // Limpar valores inválidos (emails no campo de URL) e usar .env como fallback
        foreach (['botconversa_webhook_url', 'botconversa_paid_webhook_url'] as $key) {
            $value = $apiSettings[$key] ?? '';
            // Se for um email (contém @ mas não é uma URL válida), limpar e usar .env
            if (!empty($value) && strpos($value, '@') !== false && !filter_var($value, FILTER_VALIDATE_URL)) {
                Log::info("SettingsController: Limpando valor inválido (email) do campo {$key}, usando .env");
                $apiSettings[$key] = '';
                // Usar .env como fallback se o valor do banco for inválido
                $envKey = strtoupper($key);
                $envValue = env($envKey) ?: config("services.botconversa." . str_replace('botconversa_', '', $key));
                if ($envValue && filter_var($envValue, FILTER_VALIDATE_URL)) {
                    $apiSettings[$key] = $envValue;
                    Log::info("SettingsController: Usando valor do .env para {$key}");
                }
            }
        }
        // Fallback para .env/config quando não houver no banco
        $envDefaults = [
            'free_shipping_min_total' => env('FREE_SHIPPING_MIN_TOTAL'),
            // cashback_percent removido - agora gerenciado na aba dedicada de Cashback
            // BotConversa do .env
            'botconversa_webhook_url' => config('services.botconversa.webhook_url'),
            'botconversa_paid_webhook_url' => config('services.botconversa.paid_webhook'),
            'botconversa_token' => config('services.botconversa.token'),
            // Defaults para entrega
            'delivery_slot_capacity' => 2,
            'advance_order_days' => 2,
        ];
        foreach ($envDefaults as $k => $v) {
            // Para BotConversa URLs, só usar .env se o valor do banco estiver vazio ou inválido
            if (in_array($k, ['botconversa_webhook_url', 'botconversa_paid_webhook_url'])) {
                $dbValue = $apiSettings[$k] ?? '';
                // Se o valor do banco é vazio ou inválido (email), usar .env
                if (empty($dbValue) || (strpos($dbValue, '@') !== false && !filter_var($dbValue, FILTER_VALIDATE_URL))) {
                    if ($v !== null && $v !== '') { 
                        $apiSettings[$k] = $v;
                    }
                }
            } else {
                // Para outros campos, comportamento normal
                if (!isset($apiSettings[$k]) || $apiSettings[$k] === null || $apiSettings[$k] === '') {
                    if ($v !== null && $v !== '') { $apiSettings[$k] = $v; }
                }
            }
        }

        $assistenteIaNome = \App\Models\PaymentSetting::getValue('assistente_ia_nome', 'ChefIA');
        
        // Buscar configurações de produção
        $clientId = currentClientId();
        $productionSettings = \App\Models\Setting::getSettings($clientId);

        // Buscar configurações gerais
        $generalSettings = $this->getSettingsFromFlexibleTable([
            'language', 'currency', 'company_name', 'company_phone', 'company_email'
        ]);

        // Buscar configurações de personalização
        $personalizationSettings = $this->getSettingsFromFlexibleTable([
            'theme_color', 'logo', 'favicon'
        ]);

        // Adicionar URLs completas para logo e favicon se existirem
        if (isset($personalizationSettings['logo']) && $personalizationSettings['logo']) {
            $personalizationSettings['logo_url'] = asset('storage/' . $personalizationSettings['logo']);
        }
        if (isset($personalizationSettings['favicon']) && $personalizationSettings['favicon']) {
            $personalizationSettings['favicon_url'] = asset('storage/' . $personalizationSettings['favicon']);
        }

        // Buscar configurações de impressão
        $printingSettings = (object) [
            'printer_type' => $this->getSettingValue('printer_type', 'thermal'),
            'receipt_size' => $this->getSettingValue('receipt_size', 'half-page'),
            'default_copies' => $this->getSettingValue('default_copies', 1),
            'page_orientation' => $this->getSettingValue('page_orientation', 'portrait'),
            'show_logo' => $this->getSettingValue('show_logo', true),
            'show_qrcode' => $this->getSettingValue('show_qrcode', true),
        ];

        return view('dashboard.settings.index', compact('whatsappSettings', 'paymentSettings', 'storeSettings', 'apiSettings', 'assistenteIaNome', 'productionSettings', 'generalSettings', 'personalizationSettings', 'printingSettings'));
    }

    public function apisSave(Request $r)
    {
        // Log dos dados recebidos ANTES da validação
        Log::info('SettingsController: Dados recebidos do formulário', [
            'all_input' => $r->all(),
            'botconversa_webhook_url_raw' => $r->input('botconversa_webhook_url'),
            'botconversa_paid_webhook_url_raw' => $r->input('botconversa_paid_webhook_url'),
            'botconversa_token_raw' => $r->has('botconversa_token') ? 'present' : 'absent',
        ]);
        
        // Validação adicional: se o campo paid_webhook_url contém um email, rejeitar
        $paidWebhookUrl = $r->input('botconversa_paid_webhook_url');
        if (!empty($paidWebhookUrl) && strpos($paidWebhookUrl, '@') !== false && !filter_var($paidWebhookUrl, FILTER_VALIDATE_URL)) {
            return back()->withErrors([
                'botconversa_paid_webhook_url' => 'Este campo deve ser uma URL válida, não um email.'
            ])->withInput();
        }

        $data = $r->validate([
            'free_shipping_min_total' => 'nullable|numeric|min:0',
            'order_number_prefix' => 'nullable|string|max:12',
            'next_order_number' => 'nullable|integer|min:1',
            'assistente_ia_nome' => 'nullable|string|max:64',
            // BotConversa - validar URL apenas se não estiver vazio (removido FILTER_VALIDATE_URL muito restritivo)
            'botconversa_webhook_url' => 'nullable|string|max:500',
            'botconversa_paid_webhook_url' => 'nullable|string|max:500',
            'botconversa_token' => 'nullable|string|max:255',
            // Entrega
            'delivery_slot_capacity' => 'nullable|integer|min:1|max:20',
            'advance_order_days' => 'nullable|integer|min:0|max:30',
            'default_cutoff_time' => 'nullable|date_format:H:i',
        ]);

        // Log dos dados APÓS validação
        Log::info('SettingsController: Dados validados', [
            'botconversa_webhook_url' => $data['botconversa_webhook_url'] ?? 'NOT_SET',
            'botconversa_paid_webhook_url' => $data['botconversa_paid_webhook_url'] ?? 'NOT_SET',
            'botconversa_token' => isset($data['botconversa_token']) ? 'PRESENT' : 'NOT_SET',
            'all_keys' => array_keys($data),
        ]);

        $this->saveSettingsIntoFlexibleTable($data);

        // Verificar se foi salvo corretamente
        $saved = $this->getSettingsFromFlexibleTable(['botconversa_paid_webhook_url']);
        Log::info('SettingsController: Verificação após salvamento', [
            'botconversa_paid_webhook_url_saved' => $saved['botconversa_paid_webhook_url'] ?? 'NOT_FOUND',
        ]);

        Log::info('SettingsController: Configurações salvas com sucesso');

        return back()->with('success', 'Configurações salvas com sucesso.');
    }

    /**
     * Lê múltiplas chaves da tabela payment_settings (tabela chave-valor)
     */
    private function getSettingsFromFlexibleTable(array $keys): array
    {
        // Usar payment_settings como tabela principal para configurações flexíveis
        if (Schema::hasTable('payment_settings')) {
            return DB::table('payment_settings')->whereIn('key', $keys)->pluck('value','key')->toArray();
        }
        
        // Fallback: tentar settings se tiver estrutura chave-valor (improvável)
        if (Schema::hasTable('settings')) {
            $keyCol = collect(['key','name','config_key','setting_key','option','option_name'])
                ->first(fn($c)=>Schema::hasColumn('settings',$c));
            $valCol = collect(['value','val','config_value','content','data','option_value'])
                ->first(fn($c)=>Schema::hasColumn('settings',$c));
            if ($keyCol && $valCol) {
                return DB::table('settings')->whereIn($keyCol, $keys)->pluck($valCol, $keyCol)->toArray();
            }
        }
        return [];
    }

    /**
     * Salva chaves/valores na tabela payment_settings (tabela chave-valor)
     */
    private function saveSettingsIntoFlexibleTable(array $data): void
    {
        Log::info('SettingsController: saveSettingsIntoFlexibleTable - Iniciando salvamento', [
            'keys' => array_keys($data),
            'payment_settings_exists' => Schema::hasTable('payment_settings'),
        ]);

        // Usar payment_settings como tabela principal para configurações flexíveis
        if (Schema::hasTable('payment_settings')) {
            foreach ($data as $k => $v) {
                // Validar URLs - se for um email, limpar o valor
                if (in_array($k, ['botconversa_webhook_url', 'botconversa_paid_webhook_url']) && !empty($v)) {
                    // Se contém @ mas não é uma URL válida, limpar
                    if (strpos($v, '@') !== false && !filter_var($v, FILTER_VALIDATE_URL)) {
                        Log::warning("SettingsController: Valor inválido detectado (email) no campo {$k}, limpando...", ['value' => substr($v, 0, 50)]);
                        $v = '';
                    }
                }
                
                // Manter valor como está (não converter string vazia para null)
                $value = $v;
                
                // Salvar em payment_settings usando Model (filtra por client_id automaticamente)
                $clientId = currentClientId();
                $result = \App\Models\PaymentSetting::updateOrCreate(
                    [
                        'client_id' => $clientId,
                        'key' => $k
                    ],
                    [
                        'value' => $value,
                        'is_active' => true,
                        'updated_at' => now(), 
                        'created_at' => DB::raw('COALESCE(created_at, NOW())')
                    ]
                );
                
                // Verificar se foi salvo corretamente
                $saved = \App\Models\PaymentSetting::where('client_id', $clientId)->where('key', $k)->first();
                $savedValue = $saved ? ($saved->value ?? 'NULL') : 'NOT_FOUND';
                
                Log::info('SettingsController: Salvo em payment_settings', [
                    'key' => $k, 
                    'value_preview' => is_string($value) ? substr($value, 0, 100) : (is_null($value) ? 'null' : gettype($value)),
                    'updateOrInsert_result' => $result,
                    'saved_value_preview' => is_string($savedValue) ? substr($savedValue, 0, 100) : $savedValue,
                    'saved_exists' => $saved ? true : false,
                ]);
            }
            return;
        }
        
        // Fallback: tentar settings se tiver estrutura chave-valor (improvável)
        if (Schema::hasTable('settings')) {
            $keyCol = collect(['key','name','config_key','setting_key','option','option_name'])
                ->first(fn($c)=>Schema::hasColumn('settings',$c));
            $valCol = collect(['value','val','config_value','content','data','option_value'])
                ->first(fn($c)=>Schema::hasColumn('settings',$c));
            if ($keyCol && $valCol) {
                $hasCreated = Schema::hasColumn('settings','created_at');
                $hasUpdated = Schema::hasColumn('settings','updated_at');
                foreach ($data as $k => $v) {
                    $value = $v;
                    $update = [$valCol => $value];
                    if ($hasUpdated) { $update['updated_at'] = now(); }
                    if ($hasCreated) { $update['created_at'] = DB::raw('COALESCE(created_at, NOW())'); }
                    DB::table('settings')->updateOrInsert([$keyCol => $k], $update);
                    Log::info('SettingsController: Salvo em settings (fallback)', ['key' => $k]);
                }
                return;
            }
        }
        
        Log::error('SettingsController: Nenhuma tabela adequada encontrada para salvar configurações');
    }
    public function whatsapp()
    {
        $clientId = currentClientId();
        
        // Buscar configurações do WhatsApp do cliente atual (filtrar por client_id)
        $row = DB::table('whatsapp_settings')
            ->where('active', 1)
            ->when($clientId, function($q) use ($clientId) {
                $q->where('client_id', $clientId);
            })
            ->first();
        
        // Buscar múltiplas instâncias do cliente atual PRIMEIRO (antes de usar $connectedPhone)
        try {
            $instancesQuery = \App\Models\WhatsappInstance::query();
            if ($clientId && method_exists(\App\Models\WhatsappInstance::class, 'where')) {
                // Se a tabela tiver client_id, filtrar
                $instancesQuery->where('client_id', $clientId);
            }
            $instances = $instancesQuery->orderBy('name')->get();
        } catch (\Exception $e) {
            // Se a tabela não existir ainda, retornar collection vazia
            $instances = collect([]);
            Log::warning('Tabela whatsapp_instances não encontrada. Execute o SQL de criação primeiro.');
        }
        
        // Buscar plano do cliente para verificar limite de instâncias
        $maxInstances = 1; // Padrão: 1 instância
        $client = \App\Models\Client::find($clientId);
        if ($client && $client->subscription && $client->subscription->plan) {
            $plan = $client->subscription->plan;
            $maxInstances = $plan->max_whatsapp_instances ?? 1;
        }
        
        // Buscar instância conectada para número padrão de confirmação de pagamento
        $connectedInstance = $instances->firstWhere('status', 'CONNECTED');
        $connectedPhone = $connectedInstance ? $connectedInstance->phone_number : null;
        
        // Se não tem instância conectada, usar primeira disponível
        if (!$connectedPhone && $instances->isNotEmpty()) {
            $connectedPhone = $instances->first()->phone_number;
        }
        
        // Se não tem configuração, criar um registro básico para evitar erros na view
        if (!$row) {
            $hasTimestamps = Schema::hasColumn('whatsapp_settings', 'created_at') && 
                             Schema::hasColumn('whatsapp_settings', 'updated_at');
            $hasClientId = Schema::hasColumn('whatsapp_settings', 'client_id');
            
            $insertData = [
                'instance_name' => 'Principal',
                'api_url' => env('WHATSAPP_API_URL', ''),
                'api_key' => env('WHATSAPP_API_KEY', env('API_SECRET', '')),
                'sender_name' => 'Olika Bot',
                'whatsapp_phone' => '',
                'admin_notification_phone' => null,
                'default_payment_confirmation_phone' => $connectedPhone, // Usar número conectado se disponível
                'active' => 1
            ];
            
            if ($hasClientId && $clientId) {
                $insertData['client_id'] = $clientId;
            }
            
            if ($hasTimestamps) {
                $insertData['created_at'] = now();
                $insertData['updated_at'] = now();
            }
            
            DB::table('whatsapp_settings')->insert($insertData);
            $row = DB::table('whatsapp_settings')
                ->where('active', 1)
                ->when($clientId, function($q) use ($clientId) {
                    $q->where('client_id', $clientId);
                })
                ->first();
        } else {
            // Se já tem registro, atualizar número padrão de confirmação de pagamento se houver instância conectada
            if ($connectedPhone && (!$row->default_payment_confirmation_phone || $row->default_payment_confirmation_phone != $connectedPhone)) {
                DB::table('whatsapp_settings')
                    ->where('id', $row->id)
                    ->update(['default_payment_confirmation_phone' => $connectedPhone]);
                // Recarregar para ter o valor atualizado
                $row = DB::table('whatsapp_settings')
                    ->where('id', $row->id)
                    ->first();
            }
        }
        
        // Se não tem configuração, criar um registro básico para evitar erros na view
        if (!$row) {
            $hasTimestamps = Schema::hasColumn('whatsapp_settings', 'created_at') && 
                             Schema::hasColumn('whatsapp_settings', 'updated_at');
            $hasClientId = Schema::hasColumn('whatsapp_settings', 'client_id');
            
            $insertData = [
                'instance_name' => 'Principal',
                'api_url' => env('WHATSAPP_API_URL', ''),
                'api_key' => env('WHATSAPP_API_KEY', env('API_SECRET', '')),
                'sender_name' => 'Olika Bot',
                'whatsapp_phone' => '',
                'active' => 1
            ];
            
            if ($hasClientId && $clientId) {
                $insertData['client_id'] = $clientId;
            }
            
            if ($hasTimestamps) {
                $insertData['created_at'] = now();
                $insertData['updated_at'] = now();
            }
            
            DB::table('whatsapp_settings')->insert($insertData);
            $row = DB::table('whatsapp_settings')
                ->where('active', 1)
                ->when($clientId, function($q) use ($clientId) {
                    $q->where('client_id', $clientId);
                })
                ->first();
        }
        
        // Buscar templates
        $templates = DB::table('whatsapp_templates')
            ->orderBy('slug')
            ->get();
        
        // Buscar status e suas notificações
        $statuses = DB::table('order_statuses')
            ->leftJoin('whatsapp_templates', 'order_statuses.whatsapp_template_id', '=', 'whatsapp_templates.id')
            ->select(
                'order_statuses.*',
                'whatsapp_templates.slug as template_slug'
            )
            ->orderBy('order_statuses.created_at')
            ->get();
        
        // Estatísticas básicas
        $stats = [
            'total_templates' => $templates->count(),
            'active_templates' => $templates->where('active', true)->count(),
            'total_statuses' => $statuses->count(),
            'statuses_with_notifications' => $statuses->where('notify_customer', true)->count(),
        ];

        // URL da API do Railway (do banco ou .env)
        $whatsappApiUrl = $row->api_url ?? env('WHATSAPP_API_URL', 'https://olika-whatsapp-integration-production.up.railway.app');
        $whatsappApiKey = $row->api_key ?? env('WHATSAPP_API_KEY', env('API_SECRET'));
        
        return view('dashboard.settings.whatsapp', compact('row', 'instances', 'templates', 'statuses', 'stats', 'whatsappApiUrl', 'whatsappApiKey', 'maxInstances', 'connectedPhone'));
    }
    
    /**
     * Proxy para obter QR Code do bot WhatsApp
     */
    public function whatsappQR()
    {
        try {
            $row = DB::table('whatsapp_settings')->where('active', 1)->first();
            if (!$row) {
                return response()->json(['error' => 'Configuração WhatsApp não encontrada'], 404);
            }
            
            $apiUrl = rtrim($row->api_url ?? env('WHATSAPP_API_URL', 'https://olika-whatsapp-integration-production.up.railway.app'), '/');
            $apiKey = $row->api_key ?? env('WHATSAPP_API_KEY', env('API_SECRET'));
            
            $ch = curl_init($apiUrl . '/api/whatsapp/qr');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'x-api-token: ' . $apiKey,
                    'Content-Type: application/json'
                ],
                CURLOPT_TIMEOUT => 10
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode >= 200 && $httpCode < 300) {
                return response()->json(json_decode($response, true));
            }
            
            return response()->json(['error' => 'Erro ao buscar QR Code'], $httpCode);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar QR Code: ' . $e->getMessage());
            return response()->json(['error' => 'Erro interno'], 500);
        }
    }
    
    /**
     * Proxy para obter status da conexão WhatsApp
     */
    public function whatsappStatus()
    {
        try {
            $row = DB::table('whatsapp_settings')->where('active', 1)->first();
            if (!$row) {
                return response()->json(['error' => 'Configuração WhatsApp não encontrada'], 404);
            }
            
            $apiUrl = rtrim($row->api_url ?? env('WHATSAPP_API_URL', 'https://olika-whatsapp-integration-production.up.railway.app'), '/');
            $apiKey = $row->api_key ?? env('WHATSAPP_API_KEY', env('API_SECRET'));
            
            $ch = curl_init($apiUrl . '/api/whatsapp/status');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'x-api-token: ' . $apiKey,
                    'Content-Type: application/json'
                ],
                CURLOPT_TIMEOUT => 10
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode >= 200 && $httpCode < 300) {
                return response()->json(json_decode($response, true));
            }
            
            return response()->json(['error' => 'Erro ao buscar status'], $httpCode);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar status: ' . $e->getMessage());
            return response()->json(['error' => 'Erro interno'], 500);
        }
    }
    
    /**
     * Proxy para limpar credenciais corrompidas
     */
    public function whatsappClearAuth()
    {
        try {
            $row = DB::table('whatsapp_settings')->where('active', 1)->first();
            if (!$row) {
                return response()->json(['error' => 'Configuração WhatsApp não encontrada'], 404);
            }
            
            $apiUrl = rtrim($row->api_url ?? env('WHATSAPP_API_URL', 'https://olika-whatsapp-integration-production.up.railway.app'), '/');
            $apiKey = $row->api_key ?? env('WHATSAPP_API_KEY', env('API_SECRET'));
            
            $ch = curl_init($apiUrl . '/api/whatsapp/clear-auth');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'x-api-token: ' . $apiKey,
                    'Content-Type: application/json'
                ],
                CURLOPT_TIMEOUT => 15
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode >= 200 && $httpCode < 300) {
                return response()->json(json_decode($response, true));
            }
            
            return response()->json(['error' => 'Erro ao limpar credenciais'], $httpCode);
        } catch (\Exception $e) {
            Log::error('Erro ao limpar credenciais WhatsApp: ' . $e->getMessage());
            return response()->json(['error' => 'Erro interno'], 500);
        }
    }
    
    /**
     * Proxy para iniciar conexão WhatsApp manualmente
     */
    public function whatsappConnect()
    {
        try {
            $row = DB::table('whatsapp_settings')->where('active', 1)->first();
            if (!$row) {
                return response()->json(['error' => 'Configuração WhatsApp não encontrada'], 404);
            }
            
            $apiUrl = rtrim($row->api_url ?? env('WHATSAPP_API_URL', 'https://olika-whatsapp-integration-production.up.railway.app'), '/');
            $apiKey = $row->api_key ?? env('WHATSAPP_API_KEY', env('API_SECRET'));
            
            $ch = curl_init($apiUrl . '/api/whatsapp/connect');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'x-api-token: ' . $apiKey,
                    'Content-Type: application/json'
                ],
                CURLOPT_TIMEOUT => 15
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode >= 200 && $httpCode < 300) {
                return response()->json(json_decode($response, true));
            }
            
            return response()->json(['error' => 'Erro ao iniciar conexão'], $httpCode);
        } catch (\Exception $e) {
            Log::error('Erro ao iniciar conexão WhatsApp: ' . $e->getMessage());
            return response()->json(['error' => 'Erro interno'], 500);
        }
    }
    
    /**
     * Proxy para desconectar WhatsApp manualmente
     */
    public function whatsappDisconnect()
    {
        try {
            $row = DB::table('whatsapp_settings')->where('active', 1)->first();
            if (!$row) {
                return response()->json(['error' => 'Configuração WhatsApp não encontrada'], 404);
            }
            
            $apiUrl = rtrim($row->api_url ?? env('WHATSAPP_API_URL', 'https://olika-whatsapp-integration-production.up.railway.app'), '/');
            $apiKey = $row->api_key ?? env('WHATSAPP_API_KEY', env('API_SECRET'));
            
            $ch = curl_init($apiUrl . '/api/whatsapp/disconnect');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'x-api-token: ' . $apiKey,
                    'Content-Type: application/json'
                ],
                CURLOPT_TIMEOUT => 15
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode >= 200 && $httpCode < 300) {
                return response()->json(json_decode($response, true));
            }
            
            return response()->json(['error' => 'Erro ao desconectar'], $httpCode);
        } catch (\Exception $e) {
            Log::error('Erro ao desconectar WhatsApp: ' . $e->getMessage());
            return response()->json(['error' => 'Erro interno'], 500);
        }
    }

    public function whatsappSave(Request $r)
    {
        // Se apenas admin_notification_phone foi enviado, validar apenas ele
        if ($r->has('admin_notification_phone') && !$r->has('instance_name')) {
            $clientId = currentClientId();
            
            // Normalizar número de telefone antes de validar
            $phone = $r->input('admin_notification_phone');
            if ($phone) {
                $phone = $this->normalizePhoneNumber($phone);
            }
            
            $data = [
                'admin_notification_phone' => $phone ?: null,
            ];
            
            // Buscar configuração do cliente atual (filtrar por client_id)
            $row = DB::table('whatsapp_settings')
                ->where('active', 1)
                ->when($clientId, function($q) use ($clientId) {
                    $q->where('client_id', $clientId);
                })
                ->first();
            
            if ($row) {
                $hasTimestamps = Schema::hasColumn('whatsapp_settings', 'updated_at');
                
                // Buscar instância conectada para definir número padrão de confirmação de pagamento
                $connectedInstance = \App\Models\WhatsappInstance::withoutGlobalScopes()
                    ->where('client_id', $clientId)
                    ->where('status', 'CONNECTED')
                    ->first();
                
                $updateData = [
                    'admin_notification_phone' => $data['admin_notification_phone'] ?? null,
                ];
                
                // Se tem instância conectada, atualizar número padrão de confirmação automaticamente
                if ($connectedInstance && $connectedInstance->phone_number) {
                    $updateData['default_payment_confirmation_phone'] = $connectedInstance->phone_number;
                }
                
                if ($hasTimestamps) {
                    $updateData['updated_at'] = now();
                }
                DB::table('whatsapp_settings')->where('id', $row->id)->update($updateData);
            } else {
                // Se não existe registro, criar um básico
                $hasTimestamps = Schema::hasColumn('whatsapp_settings', 'created_at') && 
                                 Schema::hasColumn('whatsapp_settings', 'updated_at');
                $insertData = [
                    'instance_name' => 'Principal',
                    'api_url' => env('WHATSAPP_API_URL', ''),
                    'api_key' => env('WHATSAPP_API_KEY', env('API_SECRET', '')),
                    'sender_name' => 'Olika Bot',
                    'whatsapp_phone' => '',
                    'admin_notification_phone' => $data['admin_notification_phone'] ?? null,
                    'default_payment_confirmation_phone' => null, // Será preenchido quando houver instância conectada
                    'active' => 1
                ];
                if ($hasTimestamps) {
                    $insertData['created_at'] = now();
                    $insertData['updated_at'] = now();
                }
                DB::table('whatsapp_settings')->insert($insertData);
            }
            
            return back()->with('success', 'Número de notificação de admin salvo com sucesso!');
        }
        
        // Validação completa para o formulário antigo
        $data = $r->validate([
            'instance_name' => 'required|string|max:100',
            'api_url' => 'required|url|max:255',
            'api_key' => 'required|string|max:255',
            'sender_name' => 'nullable|string|max:100',
            'whatsapp_phone' => 'required|string|max:20|regex:/^[0-9]+$/',
            'admin_notification_phone' => 'nullable|string|max:20|regex:/^[0-9]+$/',
            'default_payment_confirmation_phone' => 'nullable|string|max:20|regex:/^[0-9]+$/',
        ]);

        $row = DB::table('whatsapp_settings')->where('active', 1)->first();

        // Verificar se a tabela tem colunas de timestamp
        $hasTimestamps = Schema::hasColumn('whatsapp_settings', 'created_at') && 
                         Schema::hasColumn('whatsapp_settings', 'updated_at');

        if ($row) {
            $updateData = $data;
            if ($hasTimestamps) {
                $updateData['updated_at'] = now();
            }
            DB::table('whatsapp_settings')->where('id', $row->id)->update($updateData);
        } else {
            $insertData = $data + ['active' => 1];
            if ($hasTimestamps) {
                $insertData['created_at'] = now();
                $insertData['updated_at'] = now();
            }
            DB::table('whatsapp_settings')->insert($insertData);
        }

        // Notificar o bot Node.js sobre a mudança do número (se mudou)
        if ($row && isset($data['whatsapp_phone']) && $row->whatsapp_phone !== $data['whatsapp_phone']) {
            Log::info('Número do WhatsApp alterado de ' . $row->whatsapp_phone . ' para ' . $data['whatsapp_phone']);
            
            // Notificar o bot para reiniciar com o novo número
            try {
                $apiUrl = rtrim($row->api_url ?? env('WHATSAPP_API_URL', 'https://olika-whatsapp-integration-production.up.railway.app'), '/');
                $apiKey = $row->api_key ?? env('WHATSAPP_API_KEY', env('API_SECRET'));
                
                $ch = curl_init($apiUrl . '/api/whatsapp/restart');
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_HTTPHEADER => [
                        'x-api-token: ' . $apiKey,
                        'Content-Type: application/json'
                    ],
                    CURLOPT_TIMEOUT => 10
                ]);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode >= 200 && $httpCode < 300) {
                    Log::info('✅ Bot notificado para reiniciar com novo número');
                } else {
                    Log::warning('⚠️ Falha ao notificar bot sobre mudança de número. HTTP: ' . $httpCode);
                }
            } catch (\Exception $e) {
                Log::error('Erro ao notificar bot sobre mudança de número: ' . $e->getMessage());
            }
        }
        
        return back()->with('success', 'Configurações do WhatsApp salvas com sucesso!');
    }
    
    /**
     * API endpoint para retornar configurações do WhatsApp (usado pelo bot Node.js)
     */
    public function whatsappSettingsApi()
    {
        // Autenticação por token (para Node.js)
        $token = request()->header('X-API-Token');
        $validToken = env('API_SECRET') ?? env('WEBHOOK_TOKEN');
        
        // Log para debug
        Log::info('whatsappSettingsApi: Verificando autenticação', [
            'token_recebido' => $token ? '***' . substr($token, -4) : 'não fornecido',
            'token_valido_existe' => !empty($validToken),
            'token_valido_preview' => $validToken ? '***' . substr($validToken, -4) : 'não definido',
            'tokens_iguais' => $token === $validToken,
            'token_recebido_length' => $token ? strlen($token) : 0,
            'token_valido_length' => $validToken ? strlen($validToken) : 0,
            'ip' => request()->ip()
        ]);
        
        if (empty($validToken)) {
            Log::error('whatsappSettingsApi: Token de validação não configurado no .env (API_SECRET ou WEBHOOK_TOKEN)');
            return response()->json([
                'error' => 'Server configuration error',
                'whatsapp_phone' => env('WHATSAPP_PHONE', '5571987019420')
            ], 500);
        }
        
        if ($token !== $validToken) {
            Log::warning('whatsappSettingsApi: Tentativa de acesso não autorizado', [
                'token_recebido' => $token ? '***' . substr($token, -4) : 'não fornecido',
                'token_esperado' => '***' . substr($validToken, -4),
                'tokens_iguais' => $token === $validToken,
                'ip' => request()->ip()
            ]);
            return response()->json([
                'error' => 'Unauthorized',
                'whatsapp_phone' => env('WHATSAPP_PHONE', '5571987019420') // Fallback mesmo em erro
            ], 403);
        }
        
        try {
            $row = DB::table('whatsapp_settings')->where('active', 1)->first();
            
            if (!$row) {
                Log::warning('whatsappSettingsApi: Nenhuma configuração ativa encontrada');
                return response()->json([
                    'whatsapp_phone' => env('WHATSAPP_PHONE', '5571987019420')
                ]);
            }
            
            // Log para debug
            Log::info('whatsappSettingsApi: Configuração encontrada', [
                'whatsapp_phone' => $row->whatsapp_phone,
                'whatsapp_phone_raw' => $row->whatsapp_phone ?? 'NULL',
                'env_whatsapp_phone' => env('WHATSAPP_PHONE'),
                'has_whatsapp_phone' => isset($row->whatsapp_phone) && !empty($row->whatsapp_phone),
                'row_id' => $row->id ?? null
            ]);
            
            // ✅ PRIORIDADE: Banco de dados primeiro, depois .env
            // Garantir que sempre retornamos uma string, nunca NULL
            $phone = !empty($row->whatsapp_phone) ? (string) trim($row->whatsapp_phone) : env('WHATSAPP_PHONE', '5571987019420');
            
            Log::info('whatsappSettingsApi: Retornando número', [
                'phone' => $phone,
                'fonte' => !empty($row->whatsapp_phone) ? 'banco_de_dados' : 'env'
            ]);
            
            return response()->json([
                'whatsapp_phone' => $phone
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar configurações WhatsApp: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'whatsapp_phone' => env('WHATSAPP_PHONE', '5571987019420')
            ]);
        }
    }
    
    /**
     * Atualiza notificações automáticas dos status
     */
    public function whatsappNotificationsSave(Request $r)
    {
        $notifications = $r->input('notifications', []);
        
        foreach ($notifications as $statusId => $settings) {
            DB::table('order_statuses')
                ->where('id', $statusId)
                ->update([
                    'notify_customer' => isset($settings['customer']) ? 1 : 0,
                    'notify_admin' => isset($settings['admin']) ? 1 : 0,
                    'updated_at' => now(),
                ]);
        }

        return back()->with('success', 'Configurações de notificações salvas com sucesso!');
    }

    public function waConnect()
    {
        try{
            $wa = new \App\Services\WhatsAppService();
            $res = $wa->connectInstance();
            
            if(!$res){ 
                return response()->json(['ok'=>false,'msg'=>'Falha ao conectar. Veja os logs.']); 
            }
            
            // Normalize possíveis campos
            $qrBase64 = $res['base64'] ?? $res['qrCode'] ?? null; // algumas builds usam 'base64', outras 'qrCode'
            $pair     = $res['pairingCode'] ?? $res['code'] ?? null;
            
            return response()->json([
                'ok' => true,
                'pairing_code' => $pair,
                'qr_base64' => $qrBase64,  // pode vir 'data:image/png;base64,...' ou só o base64 puro
                'raw' => $res
            ]);
            
        } catch(\Throwable $e){
            \Log::error('waConnect error: '.$e->getMessage());
            return response()->json(['ok'=>false,'msg'=>'Erro interno ao conectar']);
        }
    }

    public function waHealth()
    {
        try{
            $row = \DB::table('whatsapp_settings')->where('active',1)->first();
            if(!$row) return response()->json(['ok'=>false,'msg'=>'Sem configuração.']);

            $url = rtrim($row->api_url,'/')."/instance/health/{$row->instance_name}";
            $ch  = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER=>true,
                CURLOPT_HTTPHEADER => ["apikey: {$row->api_key}"],
                CURLOPT_TIMEOUT => 15
            ]);
            
            $resp = curl_exec($ch);
            if($resp === false){ return response()->json(['ok'=>false,'msg'=>curl_error($ch)]); }
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
            if($code >= 300) return response()->json(['ok'=>false,'msg'=>"HTTP {$code}",'raw'=>$resp]);
            
            $j = json_decode($resp,true);
            return response()->json(['ok'=>true,'data'=>$j]);
            
        }catch(\Throwable $e){
            \Log::error('waHealth error: '.$e->getMessage());
            return response()->json(['ok'=>false,'msg'=>'erro interno']);
        }
    }

    public function mp()
    {
        $clientId = currentClientId();
        
        // Usar Model PaymentSetting que já filtra por client_id automaticamente
        $keys = \App\Models\PaymentSetting::whereIn('key', [
                'mercadopago_access_token',
                'mercadopago_public_key',
                'mercadopago_environment',
                'mercadopago_webhook_url'
            ])
            ->pluck('value', 'key')
            ->toArray();

        // Buscar dados reais de pedidos pagos (Order já filtra por client_id via Global Scope)
        // Pedidos pagos através do Mercado Pago (status approved ou paid)
        $paidOrders = \App\Models\Order::with('customer')
            ->whereIn('payment_status', ['approved', 'paid'])
            ->get();

        // Total processado (soma de final_amount de pedidos pagos)
        $totalProcessed = $paidOrders->sum('final_amount');

        // Quantidade de transações
        $totalTransactions = $paidOrders->count();

        // Total de pedidos com pagamento (incluindo pendentes, pagos, falhados)
        $totalOrdersWithPayment = \App\Models\Order::where(function($query) {
                $query->whereNotNull('payment_id')
                      ->orWhereNotNull('payment_status');
            })
            ->count();

        // Taxa de aprovação (% de pedidos pagos vs total de pedidos com tentativa de pagamento)
        $approvalRate = $totalOrdersWithPayment > 0 
            ? round(($totalTransactions / $totalOrdersWithPayment) * 100, 1)
            : 0;

        // Ticket médio
        $averageTicket = $totalTransactions > 0 
            ? $totalProcessed / $totalTransactions 
            : 0;

        // Últimas transações (10 mais recentes pagas)
        $recentTransactions = $paidOrders
            ->sortByDesc('created_at')
            ->take(10)
            ->map(function($order) {
                return [
                    'customer_name' => $order->customer ? $order->customer->name : 'Cliente não identificado',
                    'value' => $order->final_amount,
                    'method' => $this->getPaymentMethodLabel($order->payment_method),
                    'status' => $order->payment_status === 'approved' || $order->payment_status === 'paid' ? 'Aprovado' : ucfirst($order->payment_status),
                    'date' => $order->created_at->format('d/m/Y'),
                    'status_class' => in_array($order->payment_status, ['approved', 'paid']) ? 'success' : 'muted',
                ];
            })
            ->values();

        return view('dashboard.settings.mercado-pago', [
            'keys' => $keys,
            'totalProcessed' => $totalProcessed,
            'totalTransactions' => $totalTransactions,
            'approvalRate' => $approvalRate,
            'averageTicket' => $averageTicket,
            'recentTransactions' => $recentTransactions,
        ]);
    }

    /**
     * Retorna label do método de pagamento
     */
    private function getPaymentMethodLabel($method)
    {
        $labels = [
            'pix' => 'PIX',
            'credit_card' => 'Cartão de Crédito',
            'debit_card' => 'Cartão de Débito',
            'bank_transfer' => 'Transferência Bancária',
        ];

        return $labels[$method] ?? ucfirst(str_replace('_', ' ', $method));
    }

    public function mpSave(Request $r)
    {
        $data = $r->validate([
            'mercadopago_access_token' => 'required|string',
            'mercadopago_public_key' => 'required|string',
            'mercadopago_environment' => 'required|string',
            'mercadopago_webhook_url' => 'nullable|url',
        ]);

        $clientId = currentClientId();
        
        if (!$clientId) {
            return back()->with('error', 'Erro: client_id não encontrado.');
        }

        // Persistir de forma idempotente usando o Model PaymentSetting
        foreach ($data as $k => $v) {
            \App\Models\PaymentSetting::updateOrCreate(
                [
                    'client_id' => $clientId,
                    'key' => $k
                ],
                [
                    'value' => $v,
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => DB::raw('COALESCE(created_at, NOW())')
                ]
            );
        }

        \Log::info('MercadoPago settings saved', [
            'keys' => array_keys($data),
        ]);

        return back()->with('ok', 'Configurações do Mercado Pago salvas.');
    }

    /**
     * Salva métodos de pagamento habilitados/desabilitados (Mercado Pago)
     */
    public function mpMethodsSave(Request $r)
    {
        // Checkboxes: se não vierem marcados, devem virar 0
        $keys = [
            'mp_enable_credit_card',
            'mp_enable_debit_card',
            'mp_enable_pix',
            'mp_enable_boleto',
        ];

        $toSave = [];
        foreach ($keys as $k) {
            $toSave[$k] = $r->has($k) ? '1' : '0';
        }

        $clientId = currentClientId();
        foreach ($toSave as $k => $v) {
            \App\Models\PaymentSetting::updateOrCreate(
                [
                    'client_id' => $clientId,
                    'key' => $k
                ],
                [
                    'value' => $v,
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => DB::raw('COALESCE(created_at, NOW())')
                ]
            );
        }

        \Log::info('MercadoPago methods settings saved', [
            'keys' => $toSave,
        ]);

        return back()->with('ok', 'Métodos de pagamento atualizados.');
    }

    // ============================================
    // GESTÃO DE MÚLTIPLAS INSTÂNCIAS WHATSAPP
    // ============================================

    /**
     * Criar nova instância WhatsApp
     */
    public function whatsappInstanceStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'phone_number' => 'required|string|max:20|regex:/^[0-9]+$/',
        ]);

        try {
            // Obter client_id de múltiplas fontes para debug
            $requestClientId = currentClientId();
            $sessionClientId = session('client_id');
            $userClientId = auth()->check() ? auth()->user()->client_id : null;
            
            // LOG DETALHADO para identificar a origem do problema
            Log::info('WhatsappInstance::whatsappInstanceStore - DEBUG client_id', [
                'request_client_id' => $requestClientId,
                'session_client_id' => $sessionClientId,
                'user_client_id' => $userClientId,
                'request_attributes' => $request->attributes->has('client_id') ? $request->attributes->get('client_id') : 'not_set',
                'user_email' => auth()->check() ? auth()->user()->email : 'not_authenticated',
                'user_id' => auth()->check() ? auth()->user()->id : null,
            ]);
            
            // OBRIGATÓRIO: Usar client_id do usuário autenticado
            // Isso previne que usuários regulares criem instâncias para o master
            if (!auth()->check()) {
                throw new \Exception('Usuário não autenticado. Faça login novamente.');
            }
            
            $user = auth()->user();
            if (!$user->client_id) {
                throw new \Exception('Usuário não possui estabelecimento associado. Entre em contato com o suporte.');
            }
            
            // FORÇAR: Sempre usar o client_id do usuário autenticado (ignorar sessão/request)
            $clientId = $user->client_id;
            
            Log::info('WhatsappInstance::whatsappInstanceStore - Usando client_id do usuário autenticado (FORÇADO)', [
                'client_id_escolhido' => $clientId,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'request_client_id' => $requestClientId,
                'session_client_id' => $sessionClientId,
                'correcao_aplicada' => ($requestClientId != $clientId) || ($sessionClientId != $clientId),
            ]);
            
            // PROTEÇÃO: Se for master (client_id = 1), verificar se o usuário é realmente master
            if ($clientId == 1) {
                // Verificar se o usuário é realmente master/admin
                $isMaster = $user->is_master ?? false;
                if (!$isMaster) {
                    Log::error('WhatsappInstance::whatsappInstanceStore - TENTATIVA BLOQUEADA: Usuário não-master tentou criar instância para master', [
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                        'user_client_id' => $user->client_id,
                    ]);
                    throw new \Exception('Acesso negado: você não tem permissão para criar instâncias para este estabelecimento.');
                }
            }
            
            $client = \App\Models\Client::find($clientId);
            
            if (!$client) {
                throw new \Exception('Cliente não encontrado.');
            }
            
            Log::info('WhatsappInstance::whatsappInstanceStore - Cliente confirmado', [
                'client_id' => $client->id,
                'client_name' => $client->name,
            ]);
            
            // Buscar URL disponível do master (WhatsappInstanceUrl)
            $instanceUrl = \App\Models\WhatsappInstanceUrl::getNextAvailable();
            
            if (!$instanceUrl) {
                throw new \Exception('Nenhuma instância WhatsApp disponível no momento. Entre em contato com o suporte.');
            }
            
            // Usar telefone do cadastro do cliente (whatsapp_phone)
            $phoneNumber = $client->whatsapp_phone ?? $client->phone ?? null;
            
            // Atribuir URL ao cliente
            $instanceUrl->assignToClient($client);
            
            // Usar telefone da requisição ou do cadastro
            $phoneNumber = $request->phone_number ?? $phoneNumber;
            
            // Normalizar número: garantir que tenha código do país (55 para Brasil)
            $phoneNumber = $this->normalizePhoneNumber($phoneNumber);
            
            // Criar instância WhatsApp usando create() mas desabilitando temporariamente o Global Scope
            // para garantir que o client_id seja salvo corretamente
            $instance = \App\Models\WhatsappInstance::withoutGlobalScopes()->create([
                'client_id' => $clientId, // Garantir que o client_id correto seja salvo
                'name' => $request->name,
                'phone_number' => $phoneNumber,
                'api_url' => rtrim($instanceUrl->url, '/'),
                'api_token' => $instanceUrl->api_key,
                'status' => 'DISCONNECTED',
            ]);
            
            // Verificar se foi salvo corretamente consultando o banco diretamente
            $verificacao = \DB::table('whatsapp_instances')
                ->where('id', $instance->id)
                ->first(['id', 'client_id', 'name']);
            
            Log::info('WhatsappInstance criada', [
                'id' => $instance->id,
                'name' => $instance->name,
                'client_id_esperado' => $clientId,
                'client_id_no_modelo' => $instance->client_id,
                'client_id_no_banco' => $verificacao->client_id ?? 'ERROR',
                'verificacao_ok' => ($verificacao->client_id ?? null) == $clientId,
            ]);
            
            // Se o client_id foi salvo incorretamente, corrigir
            if (($verificacao->client_id ?? null) != $clientId) {
                Log::error('WhatsappInstance::whatsappInstanceStore - CORREÇÃO: client_id incorreto salvo! Corrigindo...', [
                    'instance_id' => $instance->id,
                    'client_id_esperado' => $clientId,
                    'client_id_salvo' => $verificacao->client_id ?? null,
                ]);
                
                \DB::table('whatsapp_instances')
                    ->where('id', $instance->id)
                    ->update(['client_id' => $clientId]);
                
                $instance->refresh();
            }
            
            // Vincular instância à URL
            $instanceUrl->update(['whatsapp_instance_id' => $instance->id]);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'instance' => $instance]);
            }

            return back()->with('success', 'Instância criada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao criar instância WhatsApp', ['error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }

            return back()->with('error', 'Erro ao criar instância: ' . $e->getMessage());
        }
    }

    /**
     * Exibir dados de uma instância específica (JSON)
     */
    public function whatsappInstanceShow($instance)
    {
        try {
            $inst = \App\Models\WhatsappInstance::findOrFail($instance);
            
            // Tentar buscar status atualizado do Node.js
            $statusInfo = null;
            try {
                $statusInfo = $inst->getStatus();
            } catch (\Exception $e) {
                Log::warning('Não foi possível obter status da instância', ['id' => $instance, 'error' => $e->getMessage()]);
            }
            
            $data = $inst->toArray();
            $data['status_info'] = $statusInfo;
            
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Instância não encontrada'], 404);
        }
    }

    /**
     * Atualizar instância WhatsApp
     */
    public function whatsappInstanceUpdate(Request $request, $instance)
    {
        try {
            $inst = \App\Models\WhatsappInstance::findOrFail($instance);
            
            // Validar apenas campos editáveis (nome e telefone)
            // URL e token são gerenciados pelo master e não podem ser alterados
            $data = $request->validate([
                'name' => 'sometimes|string|max:100',
                'phone_number' => 'sometimes|nullable|string|max:20|regex:/^[0-9]+$/',
                'status' => 'sometimes|string|max:50',
            ]);
            
            // Não permitir alterar api_url e api_token (são gerenciados pelo master)
            unset($data['api_url'], $data['api_key']);
            
            // Normalizar número de telefone se foi alterado
            if (isset($data['phone_number'])) {
                $data['phone_number'] = $this->normalizePhoneNumber($data['phone_number']);
            }
            
            $inst->update($data);
            
            Log::info('WhatsappInstance atualizada', ['id' => $inst->id, 'changes' => $data]);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'instance' => $inst->fresh()]);
            }

            return back()->with('success', 'Instância atualizada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar instância WhatsApp', ['error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }

            return back()->with('error', 'Erro ao atualizar instância: ' . $e->getMessage());
        }
    }

    /**
     * Deletar instância WhatsApp
     */
    public function whatsappInstanceDestroy($instance)
    {
        try {
            $inst = \App\Models\WhatsappInstance::findOrFail($instance);
            $inst->delete();

            Log::info('WhatsappInstance deletada', ['id' => $instance]);

            if (request()->expectsJson()) {
                return response()->json(['success' => true]);
            }

            return back()->with('success', 'Instância removida com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao deletar instância WhatsApp', ['error' => $e->getMessage()]);
            
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }

            return back()->with('error', 'Erro ao remover instância: ' . $e->getMessage());
        }
    }

    /**
     * Status de uma instância específica
     */
    public function whatsappInstanceStatus($instance)
    {
        try {
            $inst = \App\Models\WhatsappInstance::findOrFail($instance);
            $status = $inst->getStatus();
            
            return response()->json($status);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Conectar instância WhatsApp
     */
    public function whatsappInstanceConnect(Request $request, $instance)
    {
        try {
            $inst = \App\Models\WhatsappInstance::findOrFail($instance);
            $result = $inst->connect();
            
            if (isset($result['success']) && $result['success']) {
                Log::info('WhatsappInstance conectando', ['id' => $instance]);
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'pairingCode' => $result['pairingCode'] ?? null,
                        'qrCode' => $result['qrCode'] ?? null,
                    ]);
                }
                
                return back()->with('success', 'Conectando instância...');
            }
            
            $error = $result['error'] ?? 'Erro desconhecido';
            
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => $error], 400);
            }
            
            return back()->with('error', 'Erro: ' . $error);
        } catch (\Exception $e) {
            Log::error('Erro ao conectar instância WhatsApp', ['error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }

            return back()->with('error', 'Erro ao conectar: ' . $e->getMessage());
        }
    }

    /**
     * Desconectar instância WhatsApp
     */
    public function whatsappInstanceDisconnect(Request $request, $instance)
    {
        try {
            $inst = \App\Models\WhatsappInstance::findOrFail($instance);
            $result = $inst->disconnect();
            
            if (isset($result['success']) && $result['success']) {
                Log::info('WhatsappInstance desconectada', ['id' => $instance]);
                
                if ($request->expectsJson()) {
                    return response()->json(['success' => true]);
                }
                
                return back()->with('success', 'Instância desconectada!');
            }
            
            $error = $result['error'] ?? 'Erro desconhecido';
            
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => $error], 400);
            }
            
            return back()->with('error', 'Erro: ' . $error);
        } catch (\Exception $e) {
            Log::error('Erro ao desconectar instância WhatsApp', ['error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }

            return back()->with('error', 'Erro ao desconectar: ' . $e->getMessage());
        }
    }

    /**
     * Normaliza número de telefone para formato internacional (Brasil: 55)
     * 
     * @param string|null $phone Número de telefone (apenas dígitos)
     * @return string Número normalizado com código do país
     */
    private function normalizePhoneNumber(?string $phone): string
    {
        if (!$phone) {
            return '';
        }

        // Remover caracteres não numéricos
        $phone = preg_replace('/\D/', '', $phone);

        // Se já começa com 55 (código do Brasil), retornar como está
        if (str_starts_with($phone, '55')) {
            return $phone;
        }

        // Se tem 10 ou 11 dígitos (DDD + número), adicionar código do país
        if (strlen($phone) >= 10 && strlen($phone) <= 11) {
            return '55' . $phone;
        }

        // Se tem menos de 10 dígitos, assumir que está incompleto e adicionar 55
        if (strlen($phone) < 10) {
            return '55' . $phone;
        }

        // Caso tenha mais de 13 dígitos (55 + 11), já está normalizado
        return $phone;
    }

    public function productionSave(Request $request)
    {
        $validated = $request->validate([
            'sales_multiplier' => 'required|numeric|min:0',
            'resale_multiplier' => 'required|numeric|min:0',
            'fixed_cost' => 'nullable|numeric|min:0',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'card_fee_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $clientId = currentClientId();
        $settings = \App\Models\Setting::getSettings($clientId);
        
        $settings->update([
            'sales_multiplier' => $validated['sales_multiplier'],
            'resale_multiplier' => $validated['resale_multiplier'],
            'fixed_cost' => $validated['fixed_cost'] ?? 0,
            'tax_percentage' => $validated['tax_percentage'] ?? 0,
            'card_fee_percentage' => $validated['card_fee_percentage'] ?? 6.0,
        ]);

        return back()->with('success', 'Configurações de produção salvas com sucesso!');
    }

    /**
     * Salvar configurações gerais (idioma, moeda, informações da empresa)
     */
    public function generalSave(Request $request)
    {
        $data = $request->validate([
            'language' => 'nullable|string|max:10',
            'currency' => 'nullable|string|max:10',
            'company_name' => 'nullable|string|max:255',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:255',
        ]);

        $this->saveSettingsIntoFlexibleTable($data);

        return back()->with('success', 'Configurações gerais salvas com sucesso!');
    }

    /**
     * Salvar configurações de personalização (cores, logo, favicon)
     */
    public function personalizationSave(Request $request)
    {
        $data = $request->validate([
            'theme_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'favicon' => 'nullable|image|mimes:jpeg,png,jpg,ico|max:1024',
            'generate_favicon_from_logo' => 'nullable|boolean',
        ]);

        $clientId = currentClientId();

        // Processar upload de logo
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $data['logo'] = $logoPath;

            // Se solicitado, gerar favicon automaticamente a partir da logo
            if ($request->input('generate_favicon_from_logo')) {
                $faviconPath = $this->generateFaviconFromLogo($request->file('logo'), $clientId);
                if ($faviconPath) {
                    $data['favicon'] = $faviconPath;
                }
            }
        } else {
            unset($data['logo']);
        }

        // Processar upload de favicon (se não foi gerado automaticamente)
        if ($request->hasFile('favicon') && !$request->input('generate_favicon_from_logo')) {
            $faviconPath = $request->file('favicon')->store('favicons', 'public');
            $data['favicon'] = $faviconPath;
        } else {
            unset($data['favicon']);
        }

        unset($data['generate_favicon_from_logo']);

        $clientId = currentClientId();
        
        // Salvar na tabela payment_settings (chave-valor)
        $this->saveSettingsIntoFlexibleTable($data);
        
        // Também salvar no Setting model para uso no layout
        $settings = \App\Models\Setting::getSettings($clientId);
        
        $updateData = [];
        if (isset($data['logo']) && $data['logo']) {
            $updateData['theme_logo_url'] = asset('storage/' . $data['logo']);
            $updateData['logo_url'] = asset('storage/' . $data['logo']);
        }
        if (isset($data['favicon']) && $data['favicon']) {
            $updateData['theme_favicon_url'] = asset('storage/' . $data['favicon']);
        }
        if (isset($data['theme_color']) && $data['theme_color']) {
            $updateData['theme_primary_color'] = $data['theme_color'];
        }
        
        if (!empty($updateData)) {
            $settings->update($updateData);
        }

        // Limpar cache para garantir que as mudanças sejam refletidas
        \Illuminate\Support\Facades\Cache::forget('theme_settings');
        \Illuminate\Support\Facades\Cache::flush();
        
        // Limpar ícones PWA antigos para forçar regeneração
        if (isset($data['logo']) || isset($data['favicon'])) {
            $pwaIconsDir = storage_path('app/public/pwa-icons');
            if (file_exists($pwaIconsDir)) {
                $files = glob($pwaIconsDir . '/' . $clientId . '_*.png');
                foreach ($files as $file) {
                    @unlink($file);
                }
            }
        }

        return back()->with('success', 'Configurações de personalização salvas com sucesso!');
    }

    /**
     * Gerar favicon automaticamente a partir da logo
     */
    private function generateFaviconFromLogo($logoFile, $clientId)
    {
        try {
            $imageInfo = getimagesize($logoFile->getRealPath());
            if (!$imageInfo) {
                Log::warning('Não foi possível ler informações da imagem');
                return null;
            }

            $sourceImage = null;
            switch ($imageInfo[2]) {
                case IMAGETYPE_JPEG:
                    $sourceImage = imagecreatefromjpeg($logoFile->getRealPath());
                    break;
                case IMAGETYPE_PNG:
                    $sourceImage = imagecreatefrompng($logoFile->getRealPath());
                    break;
                case IMAGETYPE_GIF:
                    $sourceImage = imagecreatefromgif($logoFile->getRealPath());
                    break;
                default:
                    Log::warning('Formato de imagem não suportado para geração de favicon');
                    return null;
            }

            if (!$sourceImage) {
                Log::warning('Não foi possível criar recurso de imagem');
                return null;
            }

            // Criar favicon 32x32
            $favicon = imagecreatetruecolor(32, 32);
            imagealphablending($favicon, false);
            imagesavealpha($favicon, true);
            
            // Preencher com transparência
            $transparent = imagecolorallocatealpha($favicon, 0, 0, 0, 127);
            imagefill($favicon, 0, 0, $transparent);
            
            // Redimensionar mantendo proporção
            imagecopyresampled($favicon, $sourceImage, 0, 0, 0, 0, 32, 32, $imageInfo[0], $imageInfo[1]);
            
            // Salvar favicon
            $faviconPath = 'favicons/' . $clientId . '_' . time() . '.png';
            $fullPath = storage_path('app/public/' . $faviconPath);
            
            $faviconsDir = storage_path('app/public/favicons');
            if (!file_exists($faviconsDir)) {
                mkdir($faviconsDir, 0755, true);
            }
            
            imagealphablending($favicon, false);
            imagesavealpha($favicon, true);
            imagepng($favicon, $fullPath);
            imagedestroy($favicon);
            imagedestroy($sourceImage);

            Log::info('Favicon gerado com sucesso', ['path' => $faviconPath]);
            return $faviconPath;
        } catch (\Exception $e) {
            Log::error('Erro ao gerar favicon: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return null;
        }
    }

    /**
     * Exibir página de configurações de impressão
     */
    public function printing()
    {
        $clientId = currentClientId();
        
        // Buscar configurações de impressão
        $settings = (object) [
            'printer_type' => $this->getSettingValue('printer_type', 'thermal'),
            'receipt_size' => $this->getSettingValue('receipt_size', 'half-page'),
            'default_copies' => $this->getSettingValue('default_copies', 1),
            'page_orientation' => $this->getSettingValue('page_orientation', 'portrait'),
            'show_logo' => $this->getSettingValue('show_logo', true),
            'show_qrcode' => $this->getSettingValue('show_qrcode', true),
        ];
        
        return view('dashboard.settings.printing', compact('settings'));
    }

    /**
     * Salvar configurações de impressão
     */
    public function printingSave(Request $request)
    {
        $data = $request->validate([
            'printer_type' => 'required|in:thermal,regular',
            'receipt_size' => 'nullable|in:half-page,quarter-page,80mm,full-page',
            'default_copies' => 'nullable|integer|min:1|max:5',
            'page_orientation' => 'nullable|in:portrait,landscape',
            'show_logo' => 'nullable|boolean',
            'show_qrcode' => 'nullable|boolean',
        ]);
        
        // Converter checkboxes
        $data['show_logo'] = $request->has('show_logo') ? 1 : 0;
        $data['show_qrcode'] = $request->has('show_qrcode') ? 1 : 0;
        
        // Salvar usando o método existente
        $this->saveSettingsIntoFlexibleTable($data);
        
        Log::info('Configurações de impressão salvas', $data);
        
        return back()->with('success', 'Configurações de impressão salvas com sucesso!');
    }

    /**
     * Obter valor de uma configuração individual
     */
    private function getSettingValue(string $key, $default = null)
    {
        if (Schema::hasTable('payment_settings')) {
            $value = DB::table('payment_settings')->where('key', $key)->value('value');
            return $value !== null ? $value : $default;
        }
        return $default;
    }
}

