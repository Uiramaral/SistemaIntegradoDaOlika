<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderDeliveryFee;
use App\Models\DeliveryFee;

class OrderDeliveryFeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Este seeder é opcional, pois as taxas são criadas automaticamente
        // quando os pedidos são processados
        
        // Exemplo de como criar taxas de entrega para pedidos existentes
        $orders = Order::where('delivery_type', 'delivery')
            ->whereDoesntHave('orderDeliveryFee')
            ->get();

        $deliveryFee = DeliveryFee::active()->first();

        foreach ($orders as $order) {
            OrderDeliveryFee::create([
                'order_id' => $order->id,
                'delivery_fee_id' => $deliveryFee?->id,
                'calculated_fee' => $order->delivery_fee ?? 0,
                'final_fee' => $order->delivery_fee ?? 0,
                'distance_km' => null,
                'order_value' => $order->total_amount,
                'is_free_delivery' => ($order->delivery_fee ?? 0) == 0,
                'is_manual_adjustment' => false,
                'adjustment_reason' => 'Migração de dados existentes',
                'adjusted_by' => 'system',
            ]);
        }
    }
}
