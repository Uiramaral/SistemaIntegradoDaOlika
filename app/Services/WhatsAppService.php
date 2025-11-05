<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Order;

class WhatsAppService
{
    private bool $enabled = false;
    private string $baseUrl = '';
    private string $apiKey = '';
    private string $instance = '';
    private string $senderName = 'Olika Bot';

    public function __construct()
    {
        try {
            // Tentar buscar com active=1 primeiro, depois sem filtro
            $row = DB::table('whatsapp_settings')
                ->where(function($q) {
                    $q->where('active', 1)
                      ->orWhereNull('active'); // Aceitar registros sem campo active também
                })
                ->first();
            
            // Se não encontrou com active, buscar qualquer registro
            if (!$row) {
                $row = DB::table('whatsapp_settings')->first();
            }
            
            if(!$row){
                // Log apenas em debug - não é um erro, é um estado esperado quando não configurado
                Log::debug('WhatsAppService: configuração não encontrada (whatsapp_settings). Serviço desativado.');
                return; // mantém $enabled=false
            }
            
            $this->baseUrl  = rtrim((string) ($row->api_url ?? ''), '/');
            $this->apiKey   = trim((string) ($row->api_key ?? ''));
            $this->instance = trim((string) ($row->instance_name ?? ''));
            $this->senderName = ($row->sender_name ?? null) ?: 'Olika Bot';
            
            if($this->baseUrl && $this->apiKey && $this->instance){
                $this->enabled = true;
                Log::info('WhatsAppService: Configuração carregada com sucesso', [
                    'instance' => $this->instance,
                    'base_url' => $this->baseUrl
                ]);
            } else {
                // Log detalhado do que está faltando
                Log::warning('WhatsAppService: Configuração incompleta. Serviço desativado.', [
                    'has_api_url' => !empty($this->baseUrl),
                    'has_api_key' => !empty($this->apiKey),
                    'has_instance' => !empty($this->instance),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('WhatsAppService init error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->enabled = false;
        }
    }

    /**
     * Verifica se o serviço está habilitado e configurado
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    private function header(): array
    {
        return ["Content-Type: application/json","apikey: {$this->apiKey}"];
    }

    private function post(string $path, array $payload)
    {
        if (!$this->enabled) { return false; }
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
        if (!$this->enabled) { return false; }
        $number = preg_replace('/\D/','',$phone); // 55DDDNXXXXXXXX
        return $this->post('/message/sendText', [
            "number" => $number,
            "text"   => $text
        ]);
    }

    /** Template com placeholders {chave} */
    public function sendTemplate(string $phone, string $template, array $vars = [])
    {
        if (!$this->enabled) { return false; }
        $msg = $template;
        foreach($vars as $k=>$v) $msg = str_replace('{'.$k.'}', $v, $msg);
        return $this->sendText($phone, $msg);
    }

    /** Mídia/arquivo por URL (image/document/audio/video) */
    public function sendMediaByUrl(string $phone, string $url, string $mediaType = 'image', ?string $fileName = null, ?string $caption = null)
    {
        if (!$this->enabled) { return false; }
        $number = preg_replace('/\D/','',$phone);
        return $this->post('/message/sendMedia', [
            "number" => $number,
            "options" => [ "delay"=>0, "presence"=>"composing" ],
            "mediaMessage" => [
                "mediaType" => $mediaType,
                "fileName"  => $fileName ?: basename(parse_url($url, PHP_URL_PATH)) ?: 'file',
                "caption"   => $caption ?: '',
                "media"     => $url
            ]
        ]);
    }

    /** Conectar instância */
    public function connectInstance()
    {
        if (!$this->enabled) { return false; }
        $url = "{$this->baseUrl}/instance/connect/{$this->instance}";
        $ch  = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ["apikey: {$this->apiKey}"],
            CURLOPT_TIMEOUT => 20
        ]);
        $resp = curl_exec($ch);
        if($resp === false){ Log::error('EvolutionAPI connect cURL: '.curl_error($ch)); return false; }
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if($code >= 300){ Log::error("EvolutionAPI connect HTTP {$code}: ".$resp); return false; }
        return json_decode($resp, true);
    }

    /** Health da instância */
    public function getInstanceHealth()
    {
        if (!$this->enabled) { return false; }
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
        if (!$this->enabled) { return false; }
        $msgCliente = "✅ *Pagamento confirmado!*\n\n"
                    ."Olá, {$order->customer->name}!\n"
                    ."Seu pedido *#{$order->number}* foi confirmado com sucesso.\n\n"
                    ."📦 Em breve entraremos em contato para entrega.\n\n"
                    ."Atenciosamente,\nEquipe Olika Cozinha Artesanal 🥖";
        return $this->sendText($order->customer->phone, $msgCliente);
    }

    public function notifyAdmin(string $orderNumber, string $customerName, float $total, string $paymentMethod)
    {
        if (!$this->enabled) { return false; }
        $adminNumber = env('WHATSAPP_ADMIN_NUMBER', '');
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