<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Consignacao;

class ConsignacaoPolicy
{
    public function viewAny(User $u): bool
    {
        return in_array($u->role,['admin','gestor','leitor']);
    }

    public function view(User $u, Consignacao $c): bool
    {
        return $this->viewAny($u);
    }

    public function create(User $u): bool
    {
        return in_array($u->role,['admin','gestor']);
    }

    public function update(User $u, Consignacao $c): bool
    {
        return in_array($u->role,['admin','gestor']);
    }

    public function delete(User $u, Consignacao $c): bool
    {
        return $u->role==='admin';
    }
}
