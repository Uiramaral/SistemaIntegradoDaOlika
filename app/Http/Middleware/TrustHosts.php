<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

class TrustHosts extends Middleware
{
    public function hosts()
    {
        return [
            'menuolika\.com\.br',
            'pedido\.menuolika\.com\.br',
            'dashboard\.menuolika\.com\.br',
            // se usar também gerenciamento.* deixe aqui:
            'gerenciamento\.menuolika\.com\.br',
        ];
    }
}
