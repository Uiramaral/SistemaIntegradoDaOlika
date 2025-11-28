<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Customer;
use Illuminate\Http\Request;

class OrderApiController extends Controller
{
    /**
     * Lista pedidos de um cliente
     */
    public function customerOrders(Request $request)
    {
        $phone = $request->get('phone');
        
        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'Telefone é obrigatório',
            ], 400);
        }

        $customer = Customer::where('phone', $phone)->first();
        
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente não encontrado',
            ], 404);
        }

        $orders = $customer->orders()
            ->with(['items.product', 'items.product.category'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * Exibe detalhes de um pedido
     */
    public function show(Order $order)
    {
        $order->load(['customer', 'items.product', 'coupons']);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Atualiza status do pedido
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,preparing,ready,delivered,cancelled',
        ]);

        $order->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status atualizado com sucesso',
            'data' => $order,
        ]);
    }

    /**
     * Lista todos os pedidos (admin)
     */
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'items.product']);

        // Filtro por status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por data
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }
}
