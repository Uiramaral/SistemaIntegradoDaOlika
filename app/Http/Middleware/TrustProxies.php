<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * Confia em todos os proxies (ajuste para IPs específicos se preferir).
     */
    protected $proxies = '*';

    /**
     * Cabeçalhos padrão para proxies modernos.
     */
    protected $headers = Request::HEADER_X_FORWARDED_FOR
                       | Request::HEADER_X_FORWARDED_HOST
                       | Request::HEADER_X_FORWARDED_PORT
                       | Request::HEADER_X_FORWARDED_PROTO
                       | Request::HEADER_X_FORWARDED_AWS_ELB;
}
