<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Events\PedidoStatusChanged;

class PedidosNotifyController extends Controller
{
    public function send(Pedido $pedido)
    {
        event(new PedidoStatusChanged($pedido, $pedido->status, $pedido->status));
        return back()->with('ok','Notificações reenviadas.');
    }
}
