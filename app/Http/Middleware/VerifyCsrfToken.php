<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'cart/*',
        'cart/add',
        'cart/update',
        'cart/remove',
        'cart/clear',
        'webhooks/*',
        'api/*',
        'api/botconversa/*',
        'dashboard/customers/debts/*/settle',
        'customers/debts/*/settle',
        'assistente-ia/ask',
        'assistente-ia/test',
        'stripe/*',
        'webhook/*',
        'bot-conversa/*',
        // Exceptions for PDV to avoid 419 on session mismatch
        'pdv/*',
        'dashboard/pdv/*',
        'api/pdv/*',
        // Exceptions for Order Actions (due to same session domain issue)
        'orders/*',
        'dashboard/orders/*',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Illuminate\Session\TokenMismatchException
     */
    public function handle($request, \Closure $next)
    {
        try {
            return parent::handle($request, $next);
        } catch (\Illuminate\Session\TokenMismatchException $e) {
            \Log::error('CSRF Token Mismatch', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'session_token' => $request->session()->token(),
                'header_token' => $request->header('X-CSRF-TOKEN'),
                'input_token' => $request->input('_token'),
                'cookies' => $request->cookies->all(),
                'session_config' => config('session'),
            ]);
            throw $e;
        }
    }
}
