<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Customer;
use App\Services\WhatsAppService;
use App\Services\LogService;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    protected $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Envia notificação de pedido confirmado
     */
    public function sendOrderConfirmation(Order $order)
    {
        try {
            // WhatsApp
            $this->whatsAppService->sendOrderConfirmation($order);
            
            // Email (se disponível)
            if ($order->customer->email) {
                $this->sendOrderConfirmationEmail($order);
            }

            LogService::logOrder($order, 'notification_sent', [
                'type' => 'confirmation',
                'channels' => ['whatsapp', 'email'],
            ]);

            return true;

        } catch (\Exception $e) {
            LogService::logError('Order Confirmation Notification', $e, [
                'order_id' => $order->id,
            ]);
            return false;
        }
    }

    /**
     * Envia notificação de pedido pronto
     */
    public function sendOrderReady(Order $order)
    {
        try {
            $this->whatsAppService->sendOrderReady($order);
            
            LogService::logOrder($order, 'notification_sent', [
                'type' => 'ready',
            ]);

            return true;

        } catch (\Exception $e) {
            LogService::logError('Order Ready Notification', $e, [
                'order_id' => $order->id,
            ]);
            return false;
        }
    }

    /**
     * Envia notificação de pedido entregue
     */
    public function sendOrderDelivered(Order $order)
    {
        try {
            $this->whatsAppService->sendOrderDelivered($order);
            
            LogService::logOrder($order, 'notification_sent', [
                'type' => 'delivered',
            ]);

            return true;

        } catch (\Exception $e) {
            LogService::logError('Order Delivered Notification', $e, [
                'order_id' => $order->id,
            ]);
            return false;
        }
    }

    /**
     * Envia notificação de cupom disponível
     */
    public function sendCouponAvailable(Customer $customer, $coupon)
    {
        try {
            $message = "🎟️ *Novo Cupom Disponível!*\n\n" .
                      "Código: *{$coupon->code}*\n" .
                      "Desconto: {$coupon->formatted_value}\n" .
                      "Válido até: " . ($coupon->expires_at ? $coupon->expires_at->format('d/m/Y') : 'Sem prazo') . "\n\n" .
                      "Acesse: pedido.menuolika.com.br";

            $this->whatsAppService->sendMessage($customer->phone, $message);

            LogService::logActivity('coupon_notification_sent', [
                'customer_id' => $customer->id,
                'coupon_code' => $coupon->code,
            ]);

            return true;

        } catch (\Exception $e) {
            LogService::logError('Coupon Notification', $e, [
                'customer_id' => $customer->id,
                'coupon_id' => $coupon->id,
            ]);
            return false;
        }
    }

    /**
     * Envia email de confirmação
     */
    private function sendOrderConfirmationEmail(Order $order)
    {
        try {
            Mail::send('emails.order-confirmation', [
                'order' => $order,
                'customer' => $order->customer,
            ], function ($message) use ($order) {
                $message->to($order->customer->email, $order->customer->name)
                        ->subject("Pedido #{$order->order_number} Confirmado - Olika");
            });

        } catch (\Exception $e) {
            LogService::logError('Email Notification', $e, [
                'order_id' => $order->id,
                'email' => $order->customer->email,
            ]);
        }
    }

    /**
     * Envia notificação de promoção
     */
    public function sendPromotionNotification(Customer $customer, string $title, string $message)
    {
        try {
            $fullMessage = "🎉 *{$title}*\n\n{$message}\n\nAcesse: pedido.menuolika.com.br";
            
            $this->whatsAppService->sendMessage($customer->phone, $fullMessage);

            LogService::logActivity('promotion_sent', [
                'customer_id' => $customer->id,
                'title' => $title,
            ]);

            return true;

        } catch (\Exception $e) {
            LogService::logError('Promotion Notification', $e, [
                'customer_id' => $customer->id,
            ]);
            return false;
        }
    }
}
