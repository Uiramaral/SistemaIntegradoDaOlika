<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Order;

class WhatsAppService
{
    private string $baseUrl;
    private string $apiKey;
    private string $instance;
    private string $senderName;

    public function __construct()
    {
        $row = DB::table('whatsapp_settings')->where('active',1)->first();
        if(!$row) throw new \Exception('Configuração do WhatsApp não encontrada');
        
        $this->baseUrl  = rtrim($row->api_url, '/');              // ex.: http://127.0.0.1:8080
        $this->apiKey   = trim($row->api_key);                    // AUTHENTICATION_API_KEY
        $this->instance = trim($row->instance_name);              // ex.: olika_main
        $this->senderName = $row->sender_name ?: 'Olika Bot';
        
        if(!$this->baseUrl || !$this->apiKey || !$this->instance){
            throw new \Exception('Defina api_url, api_key e instance_name em whatsapp_settings.');
        }
    }

    private function header(): array
    {
        return ["Content-Type: application/json","apikey: {$this->apiKey}"];
    }

    private function post(string $path, array $payload)
    {
        $url = "{$this->baseUrl}{$path}/{$this->instance}";
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
            CURLOPT_HTTPHEADER => $this->header(),
            CURLOPT_TIMEOUT => 20
        ]);
        
        $resp = curl_exec($ch);
        if($resp === false){
            Log::error('EvolutionAPI cURL: '.curl_error($ch));
            return false;
        }
        
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if($code >= 300){
            Log::error("EvolutionAPI HTTP {$code}: ".$resp);
            return false;
        }
        
        return json_decode($resp, true);
    }

    /** Envia texto simples */
    public function sendText(string $phone, string $text)
    {
        $number = preg_replace('/\D/','',$phone); // 55DDDNXXXXXXXX
        
        return $this->post('/message/sendText', [
            "number" => $number,
            "text"   => $text
        ]);
    }

    /** Template com placeholders {chave} */
    public function sendTemplate(string $phone, string $template, array $vars = [])
    {
        $msg = $template;
        foreach($vars as $k=>$v) $msg = str_replace('{'.$k.'}', $v, $msg);
        return $this->sendText($phone, $msg);
    }

    /** Mídia/arquivo por URL (image/document/audio/video) */
    public function sendMediaByUrl(string $phone, string $url, string $mediaType = 'image', ?string $fileName = null, ?string $caption = null)
    {
        $number = preg_replace('/\D/','',$phone);
        
        return $this->post('/message/sendMedia', [
            "number" => $number,
            "options" => [ "delay"=>0, "presence"=>"composing" ],
            "mediaMessage" => [
                "mediaType" => $mediaType,        // image|document|audio|video|sticker
                "fileName"  => $fileName ?: basename(parse_url($url, PHP_URL_PATH)) ?: 'file',
                "caption"   => $caption ?: '',
                "media"     => $url               // URL pública
            ]
        ]);
    }

    /** Conectar instância - Evolution API: GET /instance/connect/{instance} */
    public function connectInstance()
    {
        $url = "{$this->baseUrl}/instance/connect/{$this->instance}";
        
        $ch  = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ["apikey: {$this->apiKey}"],
            CURLOPT_TIMEOUT => 20
        ]);
        
        $resp = curl_exec($ch);
        if($resp === false){ 
            Log::error('EvolutionAPI connect cURL: '.curl_error($ch)); 
            return false; 
        }
        
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if($code >= 300){ 
            Log::error("EvolutionAPI connect HTTP {$code}: ".$resp); 
            return false; 
        }
        
        // a API costuma devolver campos como: code, pairingCode, qrCode, base64, etc (dependendo da versão)
        return json_decode($resp, true);
    }

    /** Health da instância */
    public function getInstanceHealth()
    {
        $url = "{$this->baseUrl}/instance/health/{$this->instance}";
        
        $ch  = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ["apikey: {$this->apiKey}"],
            CURLOPT_TIMEOUT => 15
        ]);
        
        $resp = curl_exec($ch);
        if($resp === false) return false;
        
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if($code >= 300) return false;
        
        return json_decode($resp, true);
    }

    // Métodos específicos para o fluxo de pedidos
    public function sendPaymentConfirmed(Order $order)
    {
        $msgCliente = "✅ *Pagamento confirmado!*\n\n"
                    ."Olá, {$order->customer->name}!\n"
                    ."Seu pedido *#{$order->number}* foi confirmado com sucesso.\n\n"
                    ."📦 Em breve entraremos em contato para entrega.\n\n"
                    ."Atenciosamente,\nEquipe Olika Cozinha Artesanal 🥖";
        return $this->sendText($order->customer->phone, $msgCliente);
    }

    public function notifyAdmin(string $orderNumber, string $customerName, float $total, string $paymentMethod)
    {
        $adminNumber = env('WHATSAPP_ADMIN_NUMBER', '55SEUNUMEROADMIN');
        if ($adminNumber) {
            $msgAdmin = "💰 Pedido *#{$orderNumber}* pago com sucesso.\n"
                       ."Cliente: {$customerName}\n"
                       ."Total: R$ ".number_format($total,2,',','.')
                       ."\nForma: ".strtoupper($paymentMethod);
            return $this->sendText($adminNumber, $msgAdmin);
        }
        return false;
    }
}