<?php

namespace App\Http\Controllers;

use App\Models\Pedido;

class NotifyTemplatesController extends Controller
{
    public function index()
    {
        $pedidoEx = Pedido::with('cliente')->latest()->first();
        return view('notifications.templates', compact('pedidoEx'));
    }
}
