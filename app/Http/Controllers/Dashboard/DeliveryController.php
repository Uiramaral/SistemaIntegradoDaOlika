<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $view = $request->get('view', 'lista'); // 'lista' ou 'calendario'
        $tab = $request->get('tab', 'pendentes'); // 'pendentes' ou 'entregues'

        $query = Order::with(['customer', 'address', 'items.product']);

        // Filtrar por tab
        if ($tab === 'pendentes') {
            $query->whereIn('status', ['confirmed', 'preparing', 'ready', 'out_for_delivery']);
        } else {
            $query->where('status', 'delivered')
                ->whereDate('updated_at', today());
        }

        // Ordenar
        if ($tab === 'pendentes') {
            $query->orderByRaw('scheduled_delivery_at IS NULL, scheduled_delivery_at ASC')
                ->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('updated_at', 'desc');
        }

        $orders = $query->paginate(20)->withQueryString();

        // Contadores para os cards
        $pendingCount = Order::whereIn('status', ['confirmed', 'preparing', 'ready', 'out_for_delivery'])
            ->count();

        $inTransitCount = Order::where('status', 'out_for_delivery')
            ->count();

        $deliveredTodayCount = Order::where('status', 'delivered')
            ->whereDate('updated_at', today())
            ->count();

        return view('dashboard.deliveries.index', compact(
            'orders',
            'view',
            'tab',
            'pendingCount',
            'inTransitCount',
            'deliveredTodayCount'
        ));
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


