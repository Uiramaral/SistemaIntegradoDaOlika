<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

class TrustHosts extends Middleware
{
    public function hosts()
    {
        $hosts = [
            'menuolika\.com\.br',
            'pedido\.menuolika\.com\.br',
            'dashboard\.menuolika\.com\.br',
            // se usar também gerenciamento.* deixe aqui:
            'gerenciamento\.menuolika\.com\.br',
        ];

        // Sempre permitir subdomínios de desenvolvimento (mesmo em produção, para testes)
        $hosts = array_merge($hosts, [
            'devpedido\.menuolika\.com\.br',
            'devdashboard\.menuolika\.com\.br',
        ]);

        if (config('app.env') !== 'production') {
            $hosts = array_merge($hosts, [
                'localhost',
                '127\.0\.0\.1',
            ]);
        }

        return $hosts;
    }
}
