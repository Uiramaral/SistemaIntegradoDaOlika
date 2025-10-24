<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\Customer;

class LogService
{
    /**
     * Log de atividades do sistema
     */
    public static function logActivity(string $action, array $data = [], string $level = 'info')
    {
        $logData = [
            'action' => $action,
            'timestamp' => now()->toISOString(),
            'data' => $data,
        ];

        Log::channel('daily')->{$level}('Activity Log', $logData);
    }

    /**
     * Log de pedidos
     */
    public static function logOrder(Order $order, string $action, array $additionalData = [])
    {
        self::logActivity("order.{$action}", array_merge([
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'customer_id' => $order->customer_id,
            'status' => $order->status,
            'total_amount' => $order->total_amount,
        ], $additionalData));
    }

    /**
     * Log de cupons
     */
    public static function logCoupon(string $action, array $data)
    {
        self::logActivity("coupon.{$action}", $data);
    }

    /**
     * Log de pagamentos
     */
    public static function logPayment(Order $order, string $action, array $data = [])
    {
        self::logActivity("payment.{$action}", array_merge([
            'order_id' => $order->id,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
        ], $data));
    }

    /**
     * Log de erros críticos
     */
    public static function logError(string $context, \Exception $exception, array $additionalData = [])
    {
        Log::error("Critical Error in {$context}", array_merge([
            'error' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ], $additionalData));
    }
}
