<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DeliveryController extends Controller
{
    public function index()
    {
        $orders = Order::with(['customer', 'address', 'items.product'])
            ->whereIn('status', ['confirmed', 'preparing', 'ready', 'out_for_delivery'])
            ->whereNotNull('scheduled_delivery_at')
            ->orderBy('scheduled_delivery_at')
            ->get();

        return view('dashboard.deliveries.index', compact('orders'));
    }

    public function updateStatus(Request $request, Order $order, OrderStatusService $statusService)
    {
        $validated = $request->validate([
            'status' => 'required|in:out_for_delivery,delivered',
            'note' => 'nullable|string|max:500',
        ]);

        $statusService->changeStatus(
            $order,
            $validated['status'],
            $validated['note'] ?? null,
            $request->user()->id ?? null
        );

        return back()->with('success', 'Status atualizado e notificações disparadas (quando configuradas).');
    }
}


