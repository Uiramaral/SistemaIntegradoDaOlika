<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;

class AIResponderService
{
    private bool $enabled;
    private ?string $apiKey;
    private string $model;
    private string $system;

    public function __construct()
    {
        $row = DB::table('whatsapp_settings')->where('active',1)->first();
        $this->enabled = (bool)($row->ai_enabled ?? 0);
        $this->apiKey  = $row->openai_api_key ?? null;
        $this->model   = $row->openai_model ?: 'gpt-4o-mini';
        $this->system  = $row->ai_system_prompt ?: 'Você é o atendente virtual da Olika.';
    }

    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->apiKey);
    }

    /** Monta contexto leve do cliente/pedidos para a IA */
    public function buildContextForPhone(string $digits): array
    {
        // cliente + último pedido (se houver)
        $cust = DB::table('customers')->where('phone','like',"%{$digits}%")->orderByDesc('id')->first();
        $order = null;
        if($cust){
            $order = DB::table('orders')->where('customer_id',$cust->id)->orderByDesc('id')->first();
        }
        return [$cust, $order];
    }

    /** Chama OpenAI API — retorna texto final da IA */
    public function reply(string $userText, array $context = []): ?string
    {
        if(!$this->isEnabled()) return null;

        $messages = [
            ["role"=>"system", "content"=>$this->system],
            ["role"=>"user",   "content"=>$userText]
        ];

        // injeta contexto simples (não exponha dados sensíveis)
        if(!empty($context)){
            [$cust, $order] = $context;
            $ctx = [];
            if($cust){
                $ctx[] = "Cliente: {$cust->name} (tel: {$cust->phone})";
            }
            if($order){
                $ctx[] = "Último pedido: #{$order->number} total R$ ".number_format($order->total,2,',','.')." status {$order->status}";
            }
            if($ctx){
                $messages[] = ["role"=>"system","content"=>"Contexto interno: ".implode(" | ", $ctx)];
            }
        }

        // chamada HTTP simples cURL
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        $payload = [
            "model"   => $this->model,
            "messages"   => $messages,
            "max_tokens" => 400,
            "temperature" => 0.7
        ];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER=> true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->apiKey}",
                "Content-Type: application/json"
            ],
            CURLOPT_TIMEOUT => 20
        ]);

        $resp = curl_exec($ch);
        if($resp === false){ \Log::error('OpenAI cURL: '.curl_error($ch)); return null; }
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
        if($code >= 300){ \Log::error("OpenAI HTTP {$code}: ".$resp); return null; }

        $j = json_decode($resp,true);
        $text = $j['choices'][0]['message']['content'] ?? null;
        return $text ? trim($text) : null;
    }
}
