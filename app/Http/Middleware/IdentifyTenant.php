<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Client;
use Illuminate\Support\Facades\View;

class IdentifyTenant
{
    /**
     * Lista de subdomínios reservados que não devem ser tratados como tenants
     */
    protected const RESERVED_SUBDOMAINS = [
        'www',
        'dashboard',
        // 'pedido', // Permitido como slug de cliente
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
                // Busca o cliente pelo slug (usando tabela clients)
                // O tenant agora é a entidade Client, não mais User
                $tenant = Client::where('slug', $slug)
                    ->active() // Scope do model Client
                    ->first();

                if (!$tenant) {
                    abort(404, 'Estabelecimento não encontrado. Verifique se o endereço está correto.');
                }

                // Adiciona o tenant ao request para uso posterior
                $request->merge(['tenant_id' => $tenant->id]);
                $request->attributes->set('tenant', $tenant);
                $request->attributes->set('client', $tenant); // Compatibilidade com helper
                $request->attributes->set('client_id', $tenant->id); // Compatibilidade com helper

                // Compartilha com todas as views
                View::share('tenant', $tenant);

                // Injeta _client_id na requisição (para Trait, se usar input)
                $request->merge(['_client_id' => $tenant->id]);

                // Define parâmetros padrão para rotas (slug e tenant_domain)
                // Isso corrige erro "Missing required parameters" ao usar route()
                $domainParts = explode('.', $host);
                array_shift($domainParts); // Remove o slug
                $tenantDomain = implode('.', $domainParts);

                \Illuminate\Support\Facades\URL::defaults([
                    'slug' => $slug,
                    'tenant_domain' => $tenantDomain,
                ]);
            }
        }

        return $next($request);
    }
}
