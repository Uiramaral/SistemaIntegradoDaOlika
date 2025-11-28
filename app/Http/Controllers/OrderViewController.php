<?php

namespace App\Http\Controllers;

use App\Models\Order;

class OrderViewController extends Controller
{
    public function show($id)
    {
        $order = Order::with(['customer','items.product'])->findOrFail($id);
        return view('dashboard/orders/show', compact('order'));
    }
}
