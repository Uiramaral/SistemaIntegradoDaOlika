<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Client;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;

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
        Log::info('IdentifyTenant: handle direct start - host: ' . $request->getHost());
        $host = $request->getHost();
        $baseDomains = config('olika.base_domains', ['menuolika.com.br', 'cozinhapro.app.br', 'gastroflow.online']);
        $defaultClientId = config('olika.default_client_id', 1);

        $matchedBase = null;
        foreach ($baseDomains as $bd) {
            if (str_ends_with($host, $bd)) {
                $matchedBase = $bd;
                break;
            }
        }

        if (!$matchedBase || !str_contains($host, '.')) {
            // Fallback total para o cliente padrÃ£o se nÃ£o for um domÃnio conhecido com subdomÃnio
            $tenant = Client::find($defaultClientId);
            $baseDomain = $matchedBase ?: 'menuolika.com.br';
        } else {
            $baseDomain = $matchedBase;
            $parts = explode('.', str_replace($baseDomain, '', $host));
            $slug = trim($parts[0] ?? '', '.');

            // Se o host for exatamente o domÃnio base (sem subdomÃnio), ou subdomÃnio reservado
            if (empty($slug) || in_array($slug, ['dashboard', 'www', 'admin', 'api', 'painel', 'cozinha', 'pedido']) || $host === $baseDomain) {
                // Para o dashboard central e domÃnios base, tentamos identificar via autenticaÃ§Ã£o ou sessÃ£o
                $tenant = null;

                // 1. Tenta pegar do usuário logado (se houver)
                if (auth()->check()) {
                    $user = auth()->user();
                    if (isset($user->client_id) && $user->client_id) {
                        $tenant = Client::find($user->client_id);
                    }
                }

                // 2. Tenta pegar da sessão (se houver)
                if (!$tenant && session()->has('client_id')) {
                    $tenant = Client::find(session('client_id'));
                }

                // Se ainda for null, é um acesso anônimo ao domínio reservado (ex: tela de login)
            } else {
                // Tenta buscar pelo slug (ex: loja1.menuolika...)
                $tenant = Client::where('slug', $slug)->active()->first();

                // Fallback para o padrão se não encontrar (garante que nada quebre)
                if (!$tenant) {
                    $tenant = Client::find($defaultClientId);
                }
            }
        }

        if ($tenant) {
            $request->merge(['tenant_id' => $tenant->id]);
            $request->attributes->set('tenant', $tenant);
            $request->attributes->set('client', $tenant);
            $request->attributes->set('client_id', $tenant->id);
            $request->merge(['_client_id' => $tenant->id]);

            View::share('tenant', $tenant);

            // Parâmetros padrão para rotas
            \Illuminate\Support\Facades\URL::defaults([
                'slug' => $tenant->slug ?? 'pedido',
                'tenant_domain' => $baseDomain,
            ]);

        } else {
            Log::warning('IdentifyTenant: Tenant not found for host ' . $host);
        }

        // Remover parâmetros de rota de domínio para não quebrar a injeção de dependência nos controllers
        // Fazemos isso mesmo sem tenant, se a rota existir, para garantir segurança na assinatura dos métodos
        if ($request->route()) {
            $request->route()->forgetParameter('slug');
            $request->route()->forgetParameter('tenant_domain');
            $request->route()->forgetParameter('dashboard_domain');
        }

        return $next($request);
    }
}
