<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class OrdersController extends Controller
{
    public function index()
    {
        $orders = Order::with('customer')->latest()->paginate(20);
        return view('dash.pages.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('customer', 'items');
        return view('dash.pages.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $order->update(['status' => $request->status]);
        return redirect()->back()->with('success', 'Status do pedido atualizado!');
    }
}
