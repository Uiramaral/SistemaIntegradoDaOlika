<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderStatusController extends Controller
{
    public function update(Request $request, Order $order)
    {
        $order->status = $request->input('status_code');
        $order->save();

        return redirect()->back()->with('success', 'Status do pedido atualizado!');
    }
}