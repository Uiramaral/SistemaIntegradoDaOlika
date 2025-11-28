<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Cupom;

class CupomPolicy
{
    public function viewAny(User $u): bool
    {
        return in_array($u->role,['admin','gestor','leitor']);
    }

    public function view(User $u, Cupom $c): bool
    {
        return $this->viewAny($u);
    }

    public function create(User $u): bool
    {
        return in_array($u->role,['admin','gestor']);
    }

    public function update(User $u, Cupom $c): bool
    {
        return in_array($u->role,['admin','gestor']);
    }

    public function delete(User $u, Cupom $c): bool
    {
        return $u->role==='admin';
    }
}
