<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPageViews
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Rastrear apenas páginas do pedido (customer-facing)
        if ($request->is('pedido/*') && !$request->is('dashboard/*') && !$request->is('api/*')) {
            try {
                // Rastrear apenas GET requests (não POST, PUT, etc)
                if ($request->isMethod('GET')) {
                    \App\Models\AnalyticsEvent::trackPageView($request, $request->path());
                }
            } catch (\Exception $e) {
                // Não bloquear a requisição se falhar o tracking
                \Log::warning('Erro ao rastrear visualização de página', [
                    'path' => $request->path(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $response;
    }
}

