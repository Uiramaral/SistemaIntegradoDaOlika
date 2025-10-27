<?php

namespace App\Events;

use App\Models\Pedido;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class PedidoStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Pedido $pedido, public string $oldStatus, public string $newStatus) {}
}
