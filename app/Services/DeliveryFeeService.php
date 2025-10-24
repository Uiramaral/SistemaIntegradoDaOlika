<?php

namespace App\Services;

use App\Models\DeliveryFee;
use App\Models\Order;
use App\Models\OrderDeliveryFee;
use Illuminate\Support\Facades\DB;

class DeliveryFeeService
{
    /**
     * Calcula taxa de entrega para um pedido
     */
    public function calculateDeliveryFee(Order $order, float $distance = null): float
    {
        // Se for retirada, não há taxa
        if ($order->delivery_type === 'pickup') {
            return 0.00;
        }

        // Buscar taxa de entrega ativa
        $deliveryFee = DeliveryFee::active()->first();
        
        if (!$deliveryFee) {
            return 0.00;
        }

        // Verificar se a entrega é gratuita
        if ($deliveryFee->isFreeDelivery($order->total_amount)) {
            return 0.00;
        }

        // Verificar valor mínimo do pedido
        if ($order->total_amount < $deliveryFee->minimum_order_value) {
            return $deliveryFee->base_fee;
        }

        // Calcular taxa baseada na distância
        $distance = $distance ?? 0;
        $calculatedFee = $deliveryFee->calculateFee($distance, $order->total_amount);

        return $calculatedFee;
    }

    /**
     * Salva taxa de entrega no banco
     */
    public function saveDeliveryFee(Order $order, float $distance = null, string $reason = null): OrderDeliveryFee
    {
        $calculatedFee = $this->calculateDeliveryFee($order, $distance);
        
        $deliveryFee = DeliveryFee::active()->first();

        return OrderDeliveryFee::create([
            'order_id' => $order->id,
            'delivery_fee_id' => $deliveryFee?->id,
            'calculated_fee' => $calculatedFee,
            'final_fee' => $calculatedFee,
            'distance_km' => $distance,
            'order_value' => $order->total_amount,
            'is_free_delivery' => $calculatedFee == 0,
            'is_manual_adjustment' => false,
            'adjustment_reason' => $reason,
            'adjusted_by' => 'system',
        ]);
    }

    /**
     * Ajusta taxa de entrega manualmente
     */
    public function adjustDeliveryFee(Order $order, float $newFee, string $reason = null, string $adjustedBy = 'admin'): bool
    {
        $orderDeliveryFee = $order->orderDeliveryFee;
        
        if (!$orderDeliveryFee) {
            // Criar taxa se não existir
            $orderDeliveryFee = $this->saveDeliveryFee($order);
        }

        $orderDeliveryFee->applyManualAdjustment($newFee, $reason, $adjustedBy);

        // Atualizar valor final do pedido
        $this->updateOrderFinalAmount($order);

        return true;
    }

    /**
     * Reverte taxa para valor calculado automaticamente
     */
    public function revertToCalculated(Order $order): bool
    {
        $orderDeliveryFee = $order->orderDeliveryFee;
        
        if (!$orderDeliveryFee) {
            return false;
        }

        $orderDeliveryFee->revertToCalculated();

        // Atualizar valor final do pedido
        $this->updateOrderFinalAmount($order);

        return true;
    }

    /**
     * Aplica desconto na taxa de entrega
     */
    public function applyDiscount(Order $order, float $discountAmount, string $reason = null): bool
    {
        $orderDeliveryFee = $order->orderDeliveryFee;
        
        if (!$orderDeliveryFee) {
            return false;
        }

        $newFee = max(0, $orderDeliveryFee->final_fee - $discountAmount);
        
        $orderDeliveryFee->applyManualAdjustment(
            $newFee, 
            $reason ?? "Desconto de R$ " . number_format($discountAmount, 2, ',', '.'),
            'admin'
        );

        // Atualizar valor final do pedido
        $this->updateOrderFinalAmount($order);

        return true;
    }

    /**
     * Define entrega gratuita
     */
    public function setFreeDelivery(Order $order, string $reason = null): bool
    {
        $orderDeliveryFee = $order->orderDeliveryFee;
        
        if (!$orderDeliveryFee) {
            $orderDeliveryFee = $this->saveDeliveryFee($order);
        }

        $orderDeliveryFee->applyManualAdjustment(
            0.00, 
            $reason ?? 'Entrega gratuita',
            'admin'
        );

        $orderDeliveryFee->update(['is_free_delivery' => true]);

        // Atualizar valor final do pedido
        $this->updateOrderFinalAmount($order);

        return true;
    }

    /**
     * Atualiza valor final do pedido
     */
    private function updateOrderFinalAmount(Order $order): void
    {
        $orderDeliveryFee = $order->orderDeliveryFee;
        $deliveryFee = $orderDeliveryFee ? $orderDeliveryFee->final_fee : 0;
        
        $finalAmount = $order->total_amount + $deliveryFee - $order->discount_amount;
        
        $order->update([
            'delivery_fee' => $deliveryFee,
            'final_amount' => $finalAmount,
        ]);
    }

    /**
     * Obtém histórico de ajustes de taxa
     */
    public function getAdjustmentHistory(Order $order): array
    {
        $orderDeliveryFee = $order->orderDeliveryFee;
        
        if (!$orderDeliveryFee) {
            return [];
        }

        return [
            'calculated_fee' => $orderDeliveryFee->calculated_fee,
            'final_fee' => $orderDeliveryFee->final_fee,
            'difference' => $orderDeliveryFee->difference,
            'is_manual_adjustment' => $orderDeliveryFee->is_manual_adjustment,
            'adjustment_reason' => $orderDeliveryFee->adjustment_reason,
            'adjusted_by' => $orderDeliveryFee->adjusted_by,
            'created_at' => $orderDeliveryFee->created_at,
            'updated_at' => $orderDeliveryFee->updated_at,
        ];
    }

    /**
     * Calcula estatísticas de taxas de entrega
     */
    public function getDeliveryFeeStats(int $days = 30): array
    {
        $startDate = now()->subDays($days);
        
        $stats = OrderDeliveryFee::whereHas('order', function ($query) use ($startDate) {
            $query->where('created_at', '>=', $startDate);
        })->selectRaw('
            COUNT(*) as total_orders,
            AVG(calculated_fee) as avg_calculated_fee,
            AVG(final_fee) as avg_final_fee,
            SUM(calculated_fee) as total_calculated_fee,
            SUM(final_fee) as total_final_fee,
            SUM(CASE WHEN is_manual_adjustment = 1 THEN 1 ELSE 0 END) as manual_adjustments,
            SUM(CASE WHEN is_free_delivery = 1 THEN 1 ELSE 0 END) as free_deliveries
        ')->first();

        return [
            'total_orders' => $stats->total_orders ?? 0,
            'avg_calculated_fee' => $stats->avg_calculated_fee ?? 0,
            'avg_final_fee' => $stats->avg_final_fee ?? 0,
            'total_calculated_fee' => $stats->total_calculated_fee ?? 0,
            'total_final_fee' => $stats->total_final_fee ?? 0,
            'manual_adjustments' => $stats->manual_adjustments ?? 0,
            'free_deliveries' => $stats->free_deliveries ?? 0,
            'adjustment_rate' => $stats->total_orders > 0 
                ? round(($stats->manual_adjustments / $stats->total_orders) * 100, 2) 
                : 0,
        ];
    }
}
