<?php

namespace App\Services;

use App\Models\PaymentSetting;
use App\Services\AppSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MercadoPagoApi
{
    private string $base = 'https://api.mercadopago.com';
    private ?string $token;
    private bool $production;

    public function __construct()
    {
        // Busca do banco de dados (tabela payment_settings)
        $this->token = PaymentSetting::getMercadoPagoToken();
        $env = PaymentSetting::getMercadoPagoEnvironment();
        $this->production = $env === 'production';
        
        // Fallback para .env se não encontrar no banco
        if (empty($this->token)) {
            $this->token = config('payments.mp.access_token', env('MP_ACCESS_TOKEN')) ?: null;
        }
        
        if (empty($this->token)) {
            throw new \RuntimeException('Mercado Pago access token não configurado. Configure em Dashboard > Configurações > Mercado Pago ou MP_ACCESS_TOKEN no .env');
        }
    }

    private function client()
    {
        if (empty($this->token)) {
            throw new \RuntimeException('Mercado Pago access token não disponível.');
        }
        return Http::withToken($this->token)->acceptJson()->asJson()->timeout(25);
    }

    /** PIX direto (/v1/payments) */
    public function createPixPayment(array $order, array $payer): array
    {
        // Usar descrição fornecida ou padrão
        $description = $order['description'] ?? "Pedido #{$order['number']} - Olika";
        
        // Preparar itens incluindo desconto se houver
        $items = array_map(fn($i)=>[
            'title'=>$i['title'],'quantity'=>(int)$i['quantity'],'unit_price'=>(float)$i['unit_price']
        ], $order['items'] ?? []);

        // Adicionar desconto como item negativo se disponível
        if (isset($order['discount_amount']) && (float)$order['discount_amount'] > 0) {
            $items[] = [
                'title' => $order['coupon_code'] 
                    ? "Desconto - Cupom {$order['coupon_code']}" 
                    : 'Desconto',
                'quantity' => 1,
                'unit_price' => -((float)$order['discount_amount']), // Negativo para desconto
            ];
        }
        // Adicionar entrega como item positivo quando houver
        if (isset($order['delivery_fee']) && (float)$order['delivery_fee'] > 0) {
            $items[] = [
                'title' => 'Entrega',
                'quantity' => 1,
                'unit_price' => (float)$order['delivery_fee'],
            ];
        }
        
        $customerName = trim(($payer['first_name'] ?? '').' '.($payer['last_name'] ?? '')) ?: null;
        $orderNumber = $order['number'] ?? null;
        $finalDescription = $description;
        if ($orderNumber || $customerName) {
            $finalDescription = trim("Pedido #{$orderNumber} - ".$customerName);
        }
        
        // Em SANDBOX, forçar valor pequeno aleatório (R$ 0,01 a R$ 0,10)
        $sandboxAmount = null;
        if (!$this->production) {
            $sandboxAmount = mt_rand(1, 10) / 100; // 0.01 .. 0.10
        }

        // external_reference com prefixo configurável
        $extRef = (string)($order['number'] ?? '');
        $prefix = $this->flexSetting('order_number_prefix');
        if ($prefix && !Str::startsWith($extRef, $prefix)) {
            $extRef = $prefix.$extRef;
        }

        $payload = [
            'transaction_amount' => $sandboxAmount !== null ? $sandboxAmount : (float)$order['total'],
            'description'        => $finalDescription ?: $description,
            'payment_method_id'  => 'pix',
            'installments'       => 1,
            'binary_mode'        => true,
            'payer' => [
                'email' => $payer['email'] ?? null,
                'first_name' => $payer['first_name'] ?? null,
                'last_name'  => $payer['last_name'] ?? null,
                'identification' => [
                    'type' => $payer['doc_type'] ?? 'CPF',
                    'number' => $payer['doc_number'] ?? null,
                ],
            ],
            'additional_info' => [
                // Mercado Pago não aceita 'summary' neste objeto (causava 400)
                'items' => $items,
            ],
            'external_reference' => $extRef,
            'notification_url'   => $order['notification_url'] ?? null,
            'statement_descriptor'=> 'OLIKA',
        ];

        // Adicionar metadata sobre cupom/desconto e identificação do pedido
        $metadata = [];
        
        // Identificação do pedido (importante para webhook) - prioridade: metadata do payload, depois order['metadata']
        if (isset($order['metadata']['order_id'])) {
            $metadata['order_id'] = $order['metadata']['order_id'];
        }
        if (isset($order['metadata']['order_number'])) {
            $metadata['order_number'] = $order['metadata']['order_number'];
        }
        if (isset($order['metadata']['customer_id'])) {
            $metadata['customer_id'] = $order['metadata']['customer_id'];
        }
        // Fallback: tentar obter order_id do número do pedido (se possível)
        if (!isset($metadata['order_id']) && isset($order['number'])) {
            $metadata['order_number'] = (string)$order['number'];
        }
        
        // Cupom e desconto
        if (isset($order['coupon_code'])) {
            $metadata['coupon_code'] = $order['coupon_code'];
        }
        if (isset($order['discount_amount'])) {
            $metadata['discount_amount'] = (float)$order['discount_amount'];
            $metadata['discount_type'] = $order['discount_type'] ?? null;
        }
        if (isset($order['delivery_fee'])) {
            $metadata['delivery_fee'] = (float)$order['delivery_fee'];
        }
        
        // Sempre adicionar metadata (mesmo que vazio, para garantir que existe)
        $payload['metadata'] = $metadata;

        $res = $this->client()->post($this->base.'/v1/payments', $payload);
        if ($res->failed()) return ['ok'=>false,'status'=>$res->status(),'error'=>$res->json()];
        $d = $res->json();
        return [
            'ok'=>true,
            'id'=>$d['id'] ?? null,
            'qr_code'=>$d['point_of_interaction']['transaction_data']['qr_code'] ?? null,
            'qr_code_base64'=>$d['point_of_interaction']['transaction_data']['qr_code_base64'] ?? null,
            'raw'=>$d,
        ];
    }

    /** Link de pagamento (/checkout/preferences) — aceita crédito/débito/PIX; bloqueia boleto; parcelas 1x */
    public function createPaymentLink(array $order, array $payer = []): array
    {
        $items = array_map(fn($i)=>[
            'title'=>$i['title'],'quantity'=>(int)$i['quantity'],'unit_price'=>(float)$i['unit_price'],'currency_id'=>'BRL'
        ], $order['items'] ?? []);
        if (empty($items)) {
            $items[] = ['title'=>"Pedido #".($order['number'] ?? Str::upper(Str::random(6))).($payer['first_name']?" - {$payer['first_name']}" : ''),'quantity'=>1,'unit_price'=>(float)$order['total'],'currency_id'=>'BRL'];
        }

        // Incluir entrega como item quando houver
        if (isset($order['delivery_fee']) && (float)$order['delivery_fee'] > 0) {
            $items[] = [
                'title' => 'Entrega',
                'quantity' => 1,
                'unit_price' => (float)$order['delivery_fee'],
                'currency_id' => 'BRL',
            ];
        }

        // Em SANDBOX, força valor pequeno aleatório 0,01 a 0,10 no total dos itens
        if (!$this->production) {
            $sandboxAmount = mt_rand(1,10) / 100; // 0.01..0.10
            $items = [[
                'title' => 'SANDBOX TEST - Valor simbólico',
                'quantity' => 1,
                'unit_price' => $sandboxAmount,
                'currency_id' => 'BRL',
            ]];
        }

        // external_reference com prefixo
        $extRef = (string)($order['number'] ?? '');
        $prefix = $this->flexSetting('order_number_prefix');
        if ($prefix && !Str::startsWith($extRef, $prefix)) {
            $extRef = $prefix.$extRef;
        }

        $payload = [
            'items'=>$items,
            'payer'=>[
                'name'=>trim(($payer['first_name']??'').' '.($payer['last_name']??'')) ?: null,
                'email'=>$payer['email'] ?? null,
                'phone'=>isset($payer['phone'])?['number'=>$payer['phone']]:null,
                'identification'=>isset($payer['doc_number'])?['type'=>$payer['doc_type']??'CPF','number'=>$payer['doc_number']]:null,
            ],
            'external_reference'=>$extRef,
            'notification_url'=>$order['notification_url'] ?? null,
            'back_urls'=>[
                'success'=>$order['back_urls']['success'] ?? url('/pagamento/sucesso'),
                'failure'=>$order['back_urls']['failure'] ?? url('/pagamento/erro'),
                'pending'=>$order['back_urls']['pending'] ?? url('/pagamento/pendente'),
            ],
            'auto_return'=>'approved',
            'payment_methods'=>[
                'excluded_payment_types'=>[
                    ['id'=>'ticket'], // boleto fora
                ],
                'installments'=>1,  // trava parcelamento
            ],
            'statement_descriptor'=>'OLIKA',
            'binary_mode'=>true,
        ];

        // Adicionar informações de desconto/cupom se disponíveis
        if (isset($order['discount_amount']) && (float)$order['discount_amount'] > 0) {
            $discountItem = [
                'title' => $order['coupon_code'] 
                    ? "Desconto - Cupom {$order['coupon_code']}" 
                    : 'Desconto',
                'quantity' => 1,
                'unit_price' => -((float)$order['discount_amount']),
                'currency_id' => 'BRL',
            ];
            $payload['items'][] = $discountItem;
        }

        // Adicionar metadata com resumo/identificação
        $metadata = $order['metadata'] ?? [];
        
        // Identificação do pedido (importante para webhook)
        if (isset($order['metadata']['order_id'])) {
            $metadata['order_id'] = $order['metadata']['order_id'];
        }
        if (isset($order['metadata']['order_number'])) {
            $metadata['order_number'] = $order['metadata']['order_number'];
        }
        if (isset($order['number'])) {
            $metadata['order_number'] = (string)$order['number'];
        }
        
        $metadata['summary'] = [
            'subtotal' => array_reduce($order['items'] ?? [], fn($c,$i)=>$c + ((float)$i['unit_price']*(int)$i['quantity']), 0.0),
            'delivery_fee' => (float)($order['delivery_fee'] ?? 0),
            'discount_amount' => (float)($order['discount_amount'] ?? 0),
            'total' => (float)$order['total']
        ];
        if (isset($order['coupon_code'])) { $metadata['coupon_code'] = $order['coupon_code']; }
        if (isset($order['number'])) { $metadata['order_number'] = (string)$order['number']; }
        if (!empty($payer['first_name']) || !empty($payer['last_name'])) { $metadata['customer_name'] = trim(($payer['first_name']??'').' '.($payer['last_name']??'')); }
        $payload['metadata'] = array_merge($payload['metadata'] ?? [], $metadata);

        $res = $this->client()->post($this->base.'/checkout/preferences', $payload);
        if ($res->failed()) return ['ok'=>false,'status'=>$res->status(),'error'=>$res->json()];
        $d = $res->json();
        return ['ok'=>true,'id'=>$d['id']??null,'init_point'=>$d['init_point'] ?? ($d['sandbox_init_point'] ?? null),'raw'=>$d];
    }

    // Métodos de compatibilidade com código existente
    public function createPix(array $data)
    {
        return $this->createPixPayment($data, []);
    }

    public function createPreference(array $data)
    {
        return $this->createPaymentLink($data, []);
    }

    public function getPayment($paymentId)
    {
        $res = $this->client()->get($this->base."/v1/payments/{$paymentId}");
        return $res->json();
    }

    /** Gera link (checkout) para cartão (crédito/débito) e PIX dentro do mesmo link - Versão com objetos Order */
    public function createPaymentLinkFromOrder($order, $customer, array $items, array $opts = []): array
    {
        $payload = [
            'title'       => 'Pedido ' . $order->order_number,
            'quantity'    => 1,
            'unit_price'  => (float) $order->final_amount,
            'currency_id' => 'BRL',
        ];

        $prefix = $this->flexSetting('order_number_prefix');
        $extRef = (string)($order->order_number ?? $order->id);
        if ($prefix && !Str::startsWith($extRef, $prefix)) { $extRef = $prefix.$extRef; }

        // Preparar URLs de retorno
        $successUrl = route('pedido.payment.success', ['order' => $order->id]);
        $failureUrl = route('pedido.payment.failure', ['order' => $order->id]);
        $pendingUrl = route('pedido.payment.success', ['order' => $order->id]);
        
        // URL de notificação do webhook
        $notificationUrl = AppSettings::get('mercadopago_webhook_url', route('webhooks.mercadopago'));
        
        $body = [
            'items' => [$payload],
            'payer' => [
                'name'    => $customer->name ?? 'Cliente',
                'email'   => $customer->email ?? 'sem-email@dominio.com',
                'phone'   => [
                    'area_code' => '',
                    'number' => preg_replace('/\D/', '', $customer->phone ?? ''),
                ],
            ],
            'payment_methods' => [
                'excluded_payment_types' => array_map(fn($t)=>['id'=>$t], $opts['exclude_payment_types'] ?? ['ticket']), // boleto fora
                'installments'           => (int)($opts['installments'] ?? 1), // sem parcelas
            ],
            'back_urls' => [
                'success' => $successUrl,
                'failure' => $failureUrl,
                'pending' => $pendingUrl,
            ],
            'auto_return' => 'approved',
            'notification_url' => $notificationUrl,
            'external_reference' => $extRef,
            'metadata' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'customer_id' => $customer->id ?? null,
            ]
        ];

        $res = Http::withHeaders($this->headers())
            ->post("{$this->base}/checkout/preferences", $body);

        // Verificar se houve erro HTTP
        if ($res->failed()) {
            \Log::error('MercadoPagoApi: Erro HTTP ao criar preference', [
                'status' => $res->status(),
                'body' => $res->body(),
                'order_id' => $order->id,
            ]);
            throw new \RuntimeException('MP erro HTTP: '.$res->status().' - '.$res->body());
        }
        
        $j = $res->json();
        
        // Verificar se a resposta realmente tem os dados necessários
        // A resposta do Mercado Pago pode ter init_point ou sandbox_init_point
        $hasInitPoint = !empty($j['init_point']);
        $hasSandboxInitPoint = !empty($j['sandbox_init_point']);
        $hasId = !empty($j['id']);
        
        if (!$hasId || (!$hasInitPoint && !$hasSandboxInitPoint)) {
            \Log::error('MercadoPagoApi: Preference criada mas sem dados necessários', [
                'response' => $j,
                'order_id' => $order->id,
                'has_id' => $hasId,
                'has_init_point' => $hasInitPoint,
                'has_sandbox_init_point' => $hasSandboxInitPoint,
                'status_code' => $res->status(),
            ]);
            throw new \RuntimeException('MP erro: Resposta inválida do Mercado Pago - faltam dados necessários');
        }
        
        // Log de sucesso para debug
        \Log::info('MercadoPagoApi: Preference criada com sucesso', [
            'order_id' => $order->id,
            'preference_id' => $j['id'],
            'has_init_point' => $hasInitPoint,
            'has_sandbox_init_point' => $hasSandboxInitPoint,
        ]);

        return [
            'preference_id' => $j['id'] ?? null,
            'checkout_url'  => $j['init_point'] ?? ($j['sandbox_init_point'] ?? null),
            'raw'           => $j,
        ];
    }

    /** Gera carga PIX (qr + copia e cola) */
    public function createPixPreference($order, $customer, array $items, array $opts = []): array
    {
        $body = [
            'transaction_amount' => (float)$order->final_amount,
            'description'        => 'Pedido ' . $order->order_number,
            'payment_method_id'  => 'pix',
            'payer'              => [
                'email' => $customer->email ?? 'sem-email@dominio.com',
                'first_name' => $customer->name,
            ],
        ];

        $res = Http::withHeaders($this->headers())
            ->post("{$this->base}/v1/payments", $body);

        if (!$res->ok()) {
            throw new \RuntimeException('MP PIX erro: '.$res->body());
        }
        $j = $res->json();

        $qr = $j['point_of_interaction']['transaction_data']['qr_code_base64'] ?? null;
        $cc = $j['point_of_interaction']['transaction_data']['qr_code'] ?? null;
        $exp= $j['date_of_expiration'] ?? null;

        return [
            'preference_id' => (string)($j['id'] ?? ''),
            'checkout_url'  => null,
            'qr_base64'     => $qr,
            'copia_cola'    => $cc,
            'expires_at'    => $exp,
            'raw'           => $j,
        ];
    }

    private function headers(): array
    {
        if (empty($this->token)) {
            throw new \RuntimeException('Mercado Pago access token não disponível.');
        }
        return [
            'Authorization' => "Bearer {$this->token}",
            'Content-Type'  => 'application/json',
        ];
    }

    private function flexSetting(string $key): ?string
    {
        try{
            if (!Schema::hasTable('settings')) return null;
            $keyCol = collect(['key','name','config_key','setting_key','option','option_name'])->first(fn($c)=>Schema::hasColumn('settings',$c));
            $valCol = collect(['value','val','config_value','content','data','option_value'])->first(fn($c)=>Schema::hasColumn('settings',$c));
            if (!$keyCol || !$valCol) return null;
            $val = DB::table('settings')->where($keyCol,$key)->value($valCol);
            return $val !== null ? (string)$val : null;
        }catch(\Throwable $e){ return null; }
    }
}

