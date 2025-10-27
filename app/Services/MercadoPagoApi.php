<?php

namespace App\Services;

class MercadoPagoApi 
{
    private string $accessToken;

    public function __construct(?string $accessToken = null)
    {
        $this->accessToken = $accessToken ?: AppSettings::get('mercadopago_access_token');
    }

    private function http($method, $url, $payload = null)
    {
        $ch = curl_init($url);
        $headers = ["Authorization: Bearer {$this->accessToken}"];
        
        if ($payload !== null) {
            $headers[] = "Content-Type: application/json";
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER    => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT       => 30
        ]);
        
        $resp = curl_exec($ch);
        
        if ($resp === false) {
            throw new \Exception(curl_error($ch));
        }
        
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($code >= 300) {
            throw new \Exception("MP HTTP {$code}: {$resp}");
        }
        
        return json_decode($resp, true);
    }

    public function createPix(array $data)
    {
        return $this->http('POST', 'https://api.mercadopago.com/v1/payments', $data);
    }

    public function createPreference(array $data)
    {
        return $this->http('POST', 'https://api.mercadopago.com/checkout/preferences', $data);
    }

    public function getPayment($paymentId)
    {
        return $this->http('GET', "https://api.mercadopago.com/v1/payments/{$paymentId}");
    }
}

