<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\DeliveryFeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryFeeController extends Controller
{
    protected $deliveryFeeService;

    public function __construct(DeliveryFeeService $deliveryFeeService)
    {
        $this->deliveryFeeService = $deliveryFeeService;
    }

    /**
     * Ajusta taxa de entrega de um pedido
     */
    public function adjustFee(Request $request, Order $order)
    {
        $request->validate([
            'new_fee' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:500',
            'adjusted_by' => 'nullable|string|max:100',
        ]);

        try {
            $this->deliveryFeeService->adjustDeliveryFee(
                $order,
                $request->new_fee,
                $request->reason,
                $request->adjusted_by ?? 'admin'
            );

            return response()->json([
                'success' => true,
                'message' => 'Taxa de entrega ajustada com sucesso!',
                'new_fee' => $request->new_fee,
                'formatted_fee' => 'R$ ' . number_format($request->new_fee, 2, ',', '.'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao ajustar taxa: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Aplica desconto na taxa de entrega
     */
    public function applyDiscount(Request $request, Order $order)
    {
        $request->validate([
            'discount_amount' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->deliveryFeeService->applyDiscount(
                $order,
                $request->discount_amount,
                $request->reason
            );

            $orderDeliveryFee = $order->fresh()->orderDeliveryFee;

            return response()->json([
                'success' => true,
                'message' => 'Desconto aplicado com sucesso!',
                'new_fee' => $orderDeliveryFee->final_fee,
                'formatted_fee' => $orderDeliveryFee->formatted_final_fee,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao aplicar desconto: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Define entrega gratuita
     */
    public function setFreeDelivery(Request $request, Order $order)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->deliveryFeeService->setFreeDelivery(
                $order,
                $request->reason
            );

            return response()->json([
                'success' => true,
                'message' => 'Entrega definida como gratuita!',
                'new_fee' => 0.00,
                'formatted_fee' => 'R$ 0,00',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao definir entrega gratuita: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reverte para taxa calculada automaticamente
     */
    public function revertToCalculated(Order $order)
    {
        try {
            $this->deliveryFeeService->revertToCalculated($order);

            $orderDeliveryFee = $order->fresh()->orderDeliveryFee;

            return response()->json([
                'success' => true,
                'message' => 'Taxa revertida para valor calculado automaticamente!',
                'calculated_fee' => $orderDeliveryFee->calculated_fee,
                'final_fee' => $orderDeliveryFee->final_fee,
                'formatted_fee' => $orderDeliveryFee->formatted_final_fee,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao reverter taxa: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtém histórico de ajustes
     */
    public function getAdjustmentHistory(Order $order)
    {
        $history = $this->deliveryFeeService->getAdjustmentHistory($order);

        return response()->json([
            'success' => true,
            'history' => $history,
        ]);
    }

    /**
     * Obtém estatísticas de taxas de entrega
     */
    public function getStats(Request $request)
    {
        $days = $request->get('days', 30);
        $stats = $this->deliveryFeeService->getDeliveryFeeStats($days);

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'period_days' => $days,
        ]);
    }

    /**
     * Calcula taxa de entrega para um pedido
     */
    public function calculateFee(Request $request, Order $order)
    {
        $request->validate([
            'distance' => 'nullable|numeric|min:0',
        ]);

        $distance = $request->get('distance');
        $calculatedFee = $this->deliveryFeeService->calculateDeliveryFee($order, $distance);

        return response()->json([
            'success' => true,
            'calculated_fee' => $calculatedFee,
            'formatted_fee' => 'R$ ' . number_format($calculatedFee, 2, ',', '.'),
            'distance' => $distance,
        ]);
    }

    /**
     * Lista pedidos com ajustes de taxa
     */
    public function getOrdersWithAdjustments(Request $request)
    {
        $query = Order::with(['orderDeliveryFee', 'customer'])
            ->whereHas('orderDeliveryFee', function ($q) {
                $q->where('is_manual_adjustment', true);
            });

        // Filtros
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'orders' => $orders,
        ]);
    }
}
