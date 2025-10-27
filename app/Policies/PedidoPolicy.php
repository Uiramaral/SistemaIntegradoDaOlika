<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Pedido;

class PedidoPolicy
{
    public function viewAny(User $u): bool
    {
        return in_array($u->role,['admin','gestor','atendimento','producao','entrega','leitor']);
    }

    public function view(User $u, Pedido $p): bool
    {
        return $this->viewAny($u);
    }

    public function create(User $u): bool
    {
        return in_array($u->role,['admin','gestor','atendimento']);
    }

    public function update(User $u, Pedido $p): bool
    {
        return in_array($u->role,['admin','gestor','atendimento','producao']);
    }

    public function delete(User $u, Pedido $p): bool
    {
        return in_array($u->role,['admin','gestor']);
    }

    public function bulk(User $u): bool
    {
        return in_array($u->role,['admin','gestor','producao']);
    }
}
