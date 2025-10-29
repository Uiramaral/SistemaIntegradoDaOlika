<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MercadoPagoApi
{
    private string $base = 'https://api.mercadopago.com';
    private string $token;
    private bool $production;

    public function __construct()
    {
        // lê de settings() ou .env; se tiver tabela settings/payment_settings use um Config repository
        $env = config('payments.mp.environment', env('MP_ENV', 'production'));
        $this->production = $env === 'production';
        $this->token = config('payments.mp.access_token', env('MP_ACCESS_TOKEN'));
    }

    private function client()
    {
        return Http::withToken($this->token)->acceptJson()->asJson()->timeout(25);
    }

    /** PIX direto (/v1/payments) */
    public function createPixPayment(array $order, array $payer): array
    {
        // Usar descrição fornecida ou padrão
        $description = $order['description'] ?? "Pedido #{$order['number']} - Olika";
        
        $payload = [
            'transaction_amount' => (float)$order['total'],
            'description'        => $description,
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
                'items' => array_map(fn($i)=>[
                    'title'=>$i['title'],'quantity'=>(int)$i['quantity'],'unit_price'=>(float)$i['unit_price']
                ], $order['items'] ?? []),
            ],
            'external_reference' => (string)$order['number'],
            'notification_url'   => $order['notification_url'] ?? null,
            'statement_descriptor'=> 'OLIKA',
        ];

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
            $items[] = ['title'=>"Pedido #".($order['number'] ?? Str::upper(Str::random(6))),'quantity'=>1,'unit_price'=>(float)$order['total'],'currency_id'=>'BRL'];
        }

        $payload = [
            'items'=>$items,
            'payer'=>[
                'name'=>trim(($payer['first_name']??'').' '.($payer['last_name']??'')) ?: null,
                'email'=>$payer['email'] ?? null,
                'phone'=>isset($payer['phone'])?['number'=>$payer['phone']]:null,
                'identification'=>isset($payer['doc_number'])?['type'=>$payer['doc_type']??'CPF','number'=>$payer['doc_number']]:null,
            ],
            'external_reference'=>(string)$order['number'],
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

    /** Gera link (checkout) para cartão (crédito/débito) e PIX dentro do mesmo link */
    public function createPaymentLink($order, $customer, array $items, array $opts = []): array
    {
        $payload = [
            'title'       => 'Pedido ' . $order->order_number,
            'quantity'    => 1,
            'unit_price'  => (float) $order->final_amount,
            'currency_id' => 'BRL',
        ];

        $body = [
            'items' => [$payload],
            'payer' => [
                'name'    => $customer->name,
                'email'   => $customer->email ?? 'sem-email@dominio.com',
                'phone'   => ['area_code' => '', 'number' => $customer->phone],
            ],
            'payment_methods' => [
                'excluded_payment_types' => array_map(fn($t)=>['id'=>$t], $opts['exclude_payment_types'] ?? ['ticket']), // boleto fora
                'installments'           => (int)($opts['installments'] ?? 1), // sem parcelas
            ],
            'metadata' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]
        ];

        $res = Http::withHeaders($this->headers())
            ->post("{$this->base}/checkout/preferences", $body);

        if (!$res->ok()) {
            throw new \RuntimeException('MP erro: '.$res->body());
        }
        $j = $res->json();

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
        return [
            'Authorization' => "Bearer {$this->token}",
            'Content-Type'  => 'application/json',
        ];
    }
}

