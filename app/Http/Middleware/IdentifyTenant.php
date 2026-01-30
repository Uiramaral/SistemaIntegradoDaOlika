<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    /**
     * Lista de subdomínios reservados que não devem ser tratados como tenants
     */
    protected const RESERVED_SUBDOMAINS = [
        'www',
        'dashboard',
        'pedido',
        'admin',
        'api',
        'suporte',
        'mail',
        'smtp',
        'ftp',
        'webmail',
        'cpanel',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $parts = explode('.', $host);

        // Verifica se tem pelo menos 3 partes (subdominio.dominio.tld)
        if (count($parts) >= 3) {
            $slug = strtolower($parts[0]);

            // Ignora subdomínios reservados
            if (!in_array($slug, self::RESERVED_SUBDOMAINS)) {
                // Busca o tenant (usuário) pelo slug
                $tenant = User::where('slug', $slug)
                    ->where('status', 'active')
                    ->first();

                if (!$tenant) {
                    abort(404, 'Estabelecimento não encontrado. Verifique se o endereço está correto.');
                }

                // Adiciona o tenant ao request para uso posterior
                $request->merge(['tenant_id' => $tenant->id]);
                $request->attributes->set('tenant', $tenant);
                
                // Compartilha com todas as views
                \View::share('tenant', $tenant);

                // Define o client_id na sessão se o tenant tiver um
                if ($tenant->client_id) {
                    session(['client_id' => $tenant->client_id]);
                }
            }
        }

        return $next($request);
    }
}
