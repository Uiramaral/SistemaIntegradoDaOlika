<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $apiUrl;
    protected $apiKey;

    public function __construct()
    {
        $settings = Setting::getSettings();
        
        $this->apiUrl = $settings->whatsapp_api_url;
        $this->apiKey = $settings->whatsapp_api_key;
    }

    /**
     * Envia mensagem de confirmação de pedido
     */
    public function sendOrderConfirmation(Order $order)
    {
        try {
            $message = $this->buildOrderConfirmationMessage($order);
            
            return $this->sendMessage($order->customer->phone, $message);

        } catch (\Exception $e) {
            Log::error('Erro ao enviar confirmação WhatsApp: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envia mensagem de pedido pronto
     */
    public function sendOrderReady(Order $order)
    {
        try {
            $message = $this->buildOrderReadyMessage($order);
            
            return $this->sendMessage($order->customer->phone, $message);

        } catch (\Exception $e) {
            Log::error('Erro ao enviar pedido pronto WhatsApp: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envia mensagem de pedido entregue
     */
    public function sendOrderDelivered(Order $order)
    {
        try {
            $message = $this->buildOrderDeliveredMessage($order);
            
            return $this->sendMessage($order->customer->phone, $message);

        } catch (\Exception $e) {
            Log::error('Erro ao enviar pedido entregue WhatsApp: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envia mensagem de pedido cancelado
     */
    public function sendOrderCancelled(Order $order)
    {
        try {
            $message = $this->buildOrderCancelledMessage($order);
            
            return $this->sendMessage($order->customer->phone, $message);

        } catch (\Exception $e) {
            Log::error('Erro ao enviar pedido cancelado WhatsApp: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envia mensagem personalizada
     */
    public function sendMessage(string $phone, string $message)
    {
        try {
            if (!$this->apiUrl || !$this->apiKey) {
                Log::warning('WhatsApp API não configurada');
                return false;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/send-message', [
                'phone' => $this->formatPhone($phone),
                'message' => $message,
            ]);

            if ($response->successful()) {
                Log::info('Mensagem WhatsApp enviada com sucesso', [
                    'phone' => $phone,
                    'message' => $message,
                ]);
                return true;
            }

            Log::error('Erro ao enviar mensagem WhatsApp: ' . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error('Erro ao enviar mensagem WhatsApp: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Constrói mensagem de confirmação
     */
    private function buildOrderConfirmationMessage(Order $order): string
    {
        $items = $order->items->map(function ($item) {
            return "• {$item->product->name} x{$item->quantity} - R$ " . number_format($item->total_price, 2, ',', '.');
        })->join("\n");

        return "🍕 *Olika - Pedido Confirmado*\n\n" .
               "Olá {$order->customer->name}!\n\n" .
               "Seu pedido *#{$order->order_number}* foi confirmado e está sendo preparado.\n\n" .
               "*Itens do pedido:*\n{$items}\n\n" .
               "*Total:* R$ " . number_format($order->final_amount, 2, ',', '.') . "\n" .
               "*Tipo de entrega:* {$order->delivery_type_label}\n\n" .
               "Tempo estimado: 30-45 minutos\n\n" .
               "Obrigado pela preferência! 🙏";
    }

    /**
     * Constrói mensagem de pedido pronto
     */
    private function buildOrderReadyMessage(Order $order): string
    {
        return "✅ *Pedido Pronto!*\n\n" .
               "Olá {$order->customer->name}!\n\n" .
               "Seu pedido *#{$order->order_number}* está pronto!\n\n" .
               ($order->delivery_type === 'pickup' 
                   ? "Pode vir buscar no estabelecimento.\n\n"
                   : "Aguarde a entrega em breve.\n\n") .
               "Obrigado pela preferência! 🙏";
    }

    /**
     * Constrói mensagem de pedido entregue
     */
    private function buildOrderDeliveredMessage(Order $order): string
    {
        return "🎉 *Pedido Entregue!*\n\n" .
               "Olá {$order->customer->name}!\n\n" .
               "Seu pedido *#{$order->order_number}* foi entregue com sucesso!\n\n" .
               "Esperamos que tenha gostado da experiência.\n\n" .
               "Até a próxima! 👋";
    }

    /**
     * Constrói mensagem de pedido cancelado
     */
    private function buildOrderCancelledMessage(Order $order): string
    {
        return "❌ *Pedido Cancelado*\n\n" .
               "Olá {$order->customer->name}!\n\n" .
               "Seu pedido *#{$order->order_number}* foi cancelado.\n\n" .
               "Entre em contato conosco se precisar de ajuda.\n\n" .
               "Telefone: (71) 98701-9420";
    }

    /**
     * Formata número de telefone
     */
    private function formatPhone(string $phone): string
    {
        // Remove caracteres não numéricos
        $phone = preg_replace('/\D/', '', $phone);
        
        // Adiciona código do país se necessário
        if (strlen($phone) === 11 && substr($phone, 0, 2) === '71') {
            $phone = '55' . $phone;
        }
        
        return $phone;
    }
}
