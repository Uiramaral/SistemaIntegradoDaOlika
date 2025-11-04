<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Services\OpenAIService;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    // Helpers centralizados -----------------------------

    private function getCartFromSession(): array
    {
        // Estrutura: ["productId:variantId" => ['qty'=>int,'price'=>float,'product_id'=>int,'variant_id'=>int]]
        return session('cart', []);
    }

    private function saveCartToSession(array $cart): void
    {
        session(['cart' => $cart]);
    }

    private function flexSetting(string $key): ?string
    {
        // tenta tabela settings com colunas flexíveis
        if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
            $keyCol = collect(['key','name','config_key','setting_key','option','option_name'])
                ->first(fn($c)=>\Illuminate\Support\Facades\Schema::hasColumn('settings',$c));
            $valCol = collect(['value','val','config_value','content','data','option_value'])
                ->first(fn($c)=>\Illuminate\Support\Facades\Schema::hasColumn('settings',$c));
            if ($keyCol && $valCol) {
                $val = DB::table('settings')->where($keyCol, $key)->value($valCol);
                if ($val !== null) return (string)$val;
            }
        }
        // fallback: payment_settings (chave/valor)
        if (\Illuminate\Support\Facades\Schema::hasTable('payment_settings')) {
            $val = DB::table('payment_settings')->where('key',$key)->value('value');
            if ($val !== null) return (string)$val;
        }
        // fallback .env
        return env(strtoupper($key));
    }


    public function cartSummary(array $cart): array
    {
        $count = 0;
        $total = 0.0;
        $items = [];

        // Buscar produtos do banco para enriquecer os dados
        // Coleta IDs de produtos e variações
        $productIds = [];
        $variantIds = [];
        foreach ($cart as $key => $row) {
            $pid = (int)($row['product_id'] ?? 0);
            $vid = (int)($row['variant_id'] ?? 0);
            if ($pid) { $productIds[] = $pid; }
            if ($vid) { $variantIds[] = $vid; }
        }
        $products = \App\Models\Product::whereIn('id', array_unique($productIds))
            ->with(['images'])
            ->get()
            ->keyBy('id');
        $variants = empty($variantIds) ? collect() : \App\Models\ProductVariant::whereIn('id', array_unique($variantIds))->get()->keyBy('id');

        foreach ($cart as $key => $row) {
            $productId = (int)($row['product_id'] ?? 0);
            $variantId = (int)($row['variant_id'] ?? 0);
            $qty       = (int)($row['qty'] ?? 0);
            $price     = (float)($row['price'] ?? 0);

            $product = $products->get($productId);
            $variant = $variantId ? $variants->get($variantId) : null;
            // Fallback de preço: prioriza preço da variação; senão preço do produto
            if ($price <= 0) {
                if ($variant) {
                    $price = (float)$variant->price;
                } elseif ($product) {
                    $price = (float)$product->price;
                }
            }

            $count += $qty;
            $lineTotal = $qty * $price;
            $total += $lineTotal;

            // Mesma lógica de imagem do catálogo
            $imageUrl = null;
            if ($product) {
                $img = $product->image_url;
                if (!$img && $product->cover_image) {
                    $img = asset('storage/' . $product->cover_image);
                } elseif (!$img && $product->images && $product->images->count() > 0) {
                    $img = asset('storage/' . $product->images->first()->path);
                }
                $imageUrl = $img;
            }

            $items[] = [
                'product_id' => (int)$productId,
                'variant_id' => $variantId ?: null,
                'qty'        => $qty,
                'price'      => round($price, 2),
                'subtotal'   => round($lineTotal, 2),
                'name'       => $product ? $product->name : "Produto #{$productId}",
                'variant'    => $variant ? $variant->name : null,
                'image_url'  => $imageUrl,
                'special_instructions' => $row['special_instructions'] ?? null,
            ];
        }

        return [$count, round($total, 2), $items];
    }

    private function jsonCart(array $extra = [])
    {
        [$count, $total, $items] = $this->cartSummary($this->getCartFromSession());

        // Configurações: frete grátis e cashback
        $freeShippingMin = (float)str_replace(',', '.', (string)($this->flexSetting('free_shipping_min_total') ?? 0));
        // Buscar cashback_percentage de payment_settings (aba dedicada de cashback)
        $cashbackPercent = 5.0; // padrão
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('payment_settings')) {
                $val = \Illuminate\Support\Facades\DB::table('payment_settings')->where('key', 'cashback_percentage')->value('value');
                if ($val !== null && $val !== '') {
                    $cashbackPercent = (float)$val;
                }
            }
        } catch (\Exception $e) {
            // Mantém padrão se houver erro
        }
        $cashbackAmount  = round($total * max(0, $cashbackPercent) / 100, 2);
        $remainingFree   = $freeShippingMin > 0 ? max(0, $freeShippingMin - $total) : 0;
        $progress        = $freeShippingMin > 0 ? min(100, round(($total / $freeShippingMin) * 100, 2)) : 0;

        return response()->json(array_merge([
            'success'    => true,
            'cart_count' => $count,
            'total'      => $total,
            'items'      => $items,
            'free_shipping_min_total' => round($freeShippingMin, 2),
            'free_shipping_remaining' => round($remainingFree, 2),
            'free_shipping_progress'  => $progress,
            'cashback_percent'        => round($cashbackPercent, 2),
            'cashback_amount'         => $cashbackAmount,
        ], $extra));
    }

    // Endpoints de LEITURA ------------------------------

    public function count(Request $request)
    {
        // Otimização: calcular count diretamente da sessão sem queries pesadas
        $cart = $this->getCartFromSession();
        $count = 0;
        foreach ($cart as $item) {
            $count += (int)($item['qty'] ?? 0);
        }
        return response()->json(['success' => true, 'count' => $count]);
    }

    public function items(Request $request)
    {
        return $this->jsonCart();
    }

    /**
     * Sugestões de upsell com IA
     */
    public function aiSuggestions(Request $request)
    {
        [$count, $total, $items] = $this->cartSummary($this->getCartFromSession());
        if ($count === 0) return response()->json(['success'=>true,'suggestions'=>[]]);

        $ai = new OpenAIService();
        if (!$ai->isConfigured()) return response()->json(['success'=>true,'suggestions'=>[]]);

        // Catálogo resumido (até 30 itens ativos) sem itens já no carrinho, incluindo descrição
        $inCartIds = array_map(fn($it)=>(int)$it['product_id'], $items);
        $catalog = Product::where('is_active', true)
            ->where('show_in_catalog', true)
            ->whereNotIn('id', $inCartIds)
            ->orderBy('sort_order')->orderBy('name')
            ->limit(30)->get(['id','name','price','description'])
            ->map(fn($p)=>[
                'id'=>$p->id,
                'name'=>$p->name,
                'price'=>(float)$p->price,
                'description'=>(string)($p->description ?? '')
            ])->all();

        // Histórico básico: últimos 5 pedidos do cliente
        $past = [];
        $customerId = (int)($request->user()->id ?? 0);
        $email = trim((string)$request->query('email', '')) ?: trim((string)$request->input('email', ''));
        $phone = preg_replace('/\D+/', '', (string)($request->query('phone', $request->input('phone', ''))));

        $q = DB::table('orders as o')
            ->join('order_items as i','i.order_id','=','o.id')
            ->leftJoin('products as p','p.id','=','i.product_id')
            ->orderByDesc('o.id')
            ->limit(100);

        $q->where(function($w) use ($customerId, $email, $phone) {
            if ($customerId > 0) {
                $w->orWhere('o.customer_id', $customerId);
            }
            if ($email !== '' && \Illuminate\Support\Facades\Schema::hasColumn('orders','customer_email')) {
                $w->orWhere('o.customer_email', $email);
            }
            if ($phone !== '' && \Illuminate\Support\Facades\Schema::hasColumn('orders','customer_phone')) {
                // normaliza dígitos na query
                $w->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(o.customer_phone,'(',''),')',''),'-',''),' ','') = ?", [$phone]);
            }
        });

        $rows = $q->get(['o.id as order_id','p.name as name','i.quantity as qty']);
        if ($rows->count() > 0) {
            $past = $rows->groupBy('order_id')
                ->map(function($rs){
                    return ['items'=>array_map(function($r){return ['name'=>$r->name,'qty'=>$r->qty];}, $rs->all())];
                })->values()->take(5)->all();
        }

        $suggestions = $ai->generateUpsellSuggestions($items, $catalog, $past);

        // Enriquecer sugestões com dados do produto (nome, preço, descrição)
        $ids = collect($suggestions)->pluck('product_id')->filter()->unique()->values();
        $productsById = \App\Models\Product::query()
            ->whereIn('id', $ids)
            ->active()->available()->purchasable()
            ->get(['id','name','price','description'])
            ->keyBy('id');

        $enriched = [];
        foreach ($suggestions as $sug) {
            $pid = (int)($sug['product_id'] ?? 0);
            $p   = $productsById->get($pid);
            if (!$p) { continue; }
            $enriched[] = [
                'product_id' => $pid,
                'name' => $p->name,
                'price' => (float)$p->price,
                'description' => (string)($p->description ?? ''),
                'reason' => (string)($sug['reason'] ?? ''),
                'pitch'  => (string)($sug['pitch'] ?? ''),
            ];
        }

        return response()->json(['success'=>true,'suggestions'=>$enriched]);
    }

    // Endpoints de ESCRITA ------------------------------

    public function add(Request $request)
    {
        $productId = (int) $request->input('product_id');
        $variantId = (int) $request->input('variant_id', 0);
        $qty       = max(1, (int)$request->input('qty', 1));
        $price     = (float) $request->input('price', 0);
        $specialInstructions = $request->input('special_instructions', '');

        // Se o preço não veio, tenta calcular com base em variação/produto (apenas se necessário)
        // Otimização: só fazer query se realmente precisar do preço
        if ($price <= 0) {
            // Usar query otimizada para buscar apenas o que precisa
            if ($variantId) {
                $price = (float) \App\Models\ProductVariant::where('id', $variantId)
                    ->where('is_active', true)
                    ->value('price') ?? 0;
            }
            
            if ($price <= 0) {
                $price = (float) \App\Models\Product::where('id', $productId)
                    ->where('is_active', true)
                    ->value('price') ?? 0;
            }
        }

        if ($price <= 0) {
            $msg = 'Este produto está sem preço disponível no momento. Escolha uma variação com preço ou tente outro item.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success'=>false,'ok'=>false,'message'=>$msg], 400);
            }
            return redirect()->back()->with('error', $msg);
        }

        $cart = $this->getCartFromSession();
        // Incluir observação na chave se houver, para permitir itens com mesma variação mas observações diferentes
        $key = $productId.':'.($variantId ?: 0);
        // Se houver observação, adicionar um hash simples na chave para diferenciar itens
        if (!empty($specialInstructions)) {
            $obsHash = substr(md5($specialInstructions), 0, 6);
            $key = $productId.':'.($variantId ?: 0).':obs:'.$obsHash;
        }

        if (!isset($cart[$key])) {
            $cart[$key] = [
                'product_id' => $productId,
                'variant_id' => $variantId ?: null,
                'qty' => 0,
                'price' => $price,
                'special_instructions' => !empty($specialInstructions) ? trim($specialInstructions) : null,
            ];
        }

        $cart[$key]['qty'] += $qty;
        if ($price > 0) { $cart[$key]['price'] = $price; }
        // Atualizar observações se fornecida
        if (!empty($specialInstructions)) {
            $cart[$key]['special_instructions'] = trim($specialInstructions);
        }

        $this->saveCartToSession($cart);

        // Calcular total de itens para badge (otimizado - sem query adicional)
        $totalItems = 0;
        foreach ($cart as $item) {
            $totalItems += $item['qty'];
        }

        // Rastrear evento de adição ao carrinho
        try {
            \App\Models\AnalyticsEvent::trackAddToCart($productId, [
                'variant_id' => $variantId,
                'quantity' => $qty,
                'price' => $price,
                'total_items' => $totalItems,
            ]);
        } catch (\Exception $e) {
            // Não bloquear se falhar o tracking
            \Log::warning('Erro ao rastrear adição ao carrinho', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);
        }

        $payload = [
            'ok' => true,
            'success' => true,
            'message' => 'Item adicionado ao carrinho',
            'cart_count' => $totalItems,
        ];

        // Persistir count em sessão para a badge no refresh
        session(['cart_count' => $totalItems]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($payload);
        }

        return redirect()->back()->with('success', $payload['message']);
    }

    public function update(Request $request)
    {
        $productId = (int) $request->input('product_id');
        $variantId = (int) $request->input('variant_id', 0);
        $qty       = max(0, (int)$request->input('qty', 0));
        $specialInstructions = $request->input('special_instructions', ''); // Captura observação se fornecida

        $cart = $this->getCartFromSession();

        // Construir a chave correta (pode incluir hash de observação)
        $key = $productId.':'.($variantId ?: 0);
        if (!empty($specialInstructions)) {
            $obsHash = substr(md5(trim($specialInstructions)), 0, 6);
            $key = $productId.':'.($variantId ?: 0).':obs:'.$obsHash;
        }
        
        // Se a chave exata não existir, tentar encontrar qualquer chave que comece com productId:variantId
        // (isso cobre o caso onde o item tem observação mas não estamos passando ela no request)
        if (!isset($cart[$key])) {
            $baseKey = $productId.':'.($variantId ?: 0);
            // Procurar por chaves que começam com baseKey (pode ter :obs:hash)
            foreach ($cart as $cartKey => $cartItem) {
                if (strpos($cartKey, $baseKey . ':') === 0 || $cartKey === $baseKey) {
                    $key = $cartKey;
                    break;
                }
            }
        }

        // Se quantidade for zero, remover o item
        if ($qty === 0) {
            unset($cart[$key]);
        } else {
            // Se o item não existe, criar com os dados fornecidos
            if (!isset($cart[$key])) {
                $cart[$key] = [
                    'product_id' => $productId,
                    'variant_id' => $variantId ?: null,
                    'qty' => 0,
                    'price' => (float)$request->input('price', 0),
                    'special_instructions' => !empty($specialInstructions) ? trim($specialInstructions) : null,
                ];
            }
            // Atualizar a quantidade
            $cart[$key]['qty'] = $qty;
            // Atualizar observações se fornecida
            if (!empty($specialInstructions)) {
                $cart[$key]['special_instructions'] = trim($specialInstructions);
            }
        }

        $this->saveCartToSession($cart);

        return $this->jsonCart(['message' => 'Carrinho atualizado']);
    }

    public function remove(Request $request)
    {
        $productId = (int) $request->input('product_id');
        $variantId = (int) $request->input('variant_id', 0);
        $specialInstructions = $request->input('special_instructions', ''); // Captura observação se fornecida

        $cart = $this->getCartFromSession();

        // Construir a chave correta (pode incluir hash de observação)
        $key = $productId.':'.($variantId ?: 0);
        if (!empty($specialInstructions)) {
            $obsHash = substr(md5(trim($specialInstructions)), 0, 6);
            $key = $productId.':'.($variantId ?: 0).':obs:'.$obsHash;
        }
        
        // Se a chave exata não existir, tentar encontrar qualquer chave que comece com productId:variantId
        // (isso cobre o caso onde o item tem observação mas não estamos passando ela no request)
        if (!isset($cart[$key])) {
            $baseKey = $productId.':'.($variantId ?: 0);
            // Procurar por chaves que começam com baseKey (pode ter :obs:hash)
            foreach ($cart as $cartKey => $cartItem) {
                if (strpos($cartKey, $baseKey . ':') === 0 || $cartKey === $baseKey) {
                    $key = $cartKey;
                    break;
                }
            }
        }
        
        unset($cart[$key]);

        $this->saveCartToSession($cart);

        return $this->jsonCart(['message' => 'Item removido']);
    }

    public function clear(Request $request)
    {
        $this->saveCartToSession([]);
        return $this->jsonCart(['message' => 'Carrinho limpo']);
    }

    // Página HTML --------------------------------------

    public function show(Request $request)
    {
        // Renderiza a view SEM depender de $cart cru na sessão.
        // A própria view pode consumir /cart/items via JS para hidratar.
        return view('pedido.cart');
    }

    // Compatibilidade com rotas antigas
    public function index(Request $request)
    {
        // compat com rotas antigas: /cart -> index
        return $this->show($request);
    }

    /**
     * Calcula taxa de entrega baseada no CEP
     * Usa DeliveryFeeService centralizado
     */
    public function calculateDeliveryFee(Request $request)
    {
        $zipcode = preg_replace('/\D/', '', $request->input('zipcode', ''));
        $customerPhone = preg_replace('/\D/', '', (string)$request->input('customer_phone', ''));
        $customerEmail = trim((string)$request->input('customer_email', ''));
        
        // Buscar carrinho para calcular subtotal
        $cart = $this->getCartFromSession();
        [, $subtotal] = $this->cartSummary($cart);

        // Usar serviço centralizado
        $deliveryFeeService = new \App\Services\DeliveryFeeService();
        $result = $deliveryFeeService->calculateDeliveryFee(
            $zipcode,
            (float)$subtotal,
            $customerPhone ?: null,
            $customerEmail ?: null
        );

        // Formatar resposta
        $response = [
            'success' => $result['success'],
            'delivery_fee' => $result['delivery_fee'],
            'base_delivery_fee' => $result['base_delivery_fee'] ?? $result['delivery_fee'],
            'discount_percent' => $result['discount_percent'] ?? 0,
            'discount_amount' => $result['discount_amount'] ?? 0,
            'distance_km' => $result['distance_km'],
            'free_shipping_applied' => $result['free'] ?? false,
            'subtotal' => round($subtotal, 2),
            'total' => round($subtotal + $result['delivery_fee'], 2),
            'custom' => $result['custom'] ?? false,
        ];

        if (!$result['success'] && $result['message']) {
            $response['message'] = $result['message'];
        }

        return response()->json($response, $result['success'] ? 200 : 400);
    }
}
