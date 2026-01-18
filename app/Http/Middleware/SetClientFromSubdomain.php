<?php

namespace App\Http\Middleware;

use App\Models\Client;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware SetClientFromSubdomain
 * 
 * Identifica o tenant (cliente) atual a partir do subdomínio da requisição.
 * Define o client_id no contexto para que os models filtrem automaticamente.
 * 
 * Exemplo:
 * - churrasquinhodoze.menuonline.com.br → client_id do "churrasquinhodoze"
 * - olika.menuonline.com.br → client_id do "olika"
 * 
 * Para ambiente local/desenvolvimento, usa o config('olika.default_client_id')
 */
class SetClientFromSubdomain
{
    /**
     * Domínios base que indicam o sistema principal (não são subdomínios de clientes)
     */
    protected array $baseDomains = [
        'menuonline.com.br',
        'olika.com.br',
        'localhost',
        '127.0.0.1',
    ];

    /**
     * Subdomínios reservados que não são clientes
     */
    protected array $reservedSubdomains = [
        'www',
        'api',
        'admin',
        'app',
        'dashboard',
        'devdashboard',
        'panel',
        'painel',
        'staging',
        'dev',
        'test',
        'homolog',
        'sandbox',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $client = null;
        $host = $request->getHost();

        // =====================================================================
        // PRIORIDADE 1: Sessão (setado no login/registro)
        // Se já existe client_id na sessão, usar esse (usuário já identificado)
        // =====================================================================
        if (session()->has('client_id')) {
            $sessionClientId = session('client_id');
            $client = Client::find($sessionClientId);
            
            if ($client) {
                \Log::debug('SetClientFromSubdomain: Cliente identificado pela sessão', [
                    'session_client_id' => $sessionClientId,
                    'client_id' => $client->id,
                    'client_name' => $client->name,
                ]);
            }
        }

        // =====================================================================
        // PRIORIDADE 2: Usuário autenticado (fallback se sessão não tem)
        // Se o usuário está logado mas sessão não tem, usar o client_id DELE
        // =====================================================================
        if (!$client) {
            $user = auth()->user();
            if ($user && $user->client_id) {
                $client = Client::find($user->client_id);
                
                if ($client) {
                    // Setar na sessão para próximos requests
                    session(['client_id' => $client->id]);
                    
                    \Log::debug('SetClientFromSubdomain: Cliente identificado pelo usuário logado', [
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                        'client_id' => $client->id,
                        'client_name' => $client->name,
                    ]);
                }
            }
        }

        // =====================================================================
        // PRIORIDADE 3: Subdomínio (para cardápio público)
        // =====================================================================
        if (!$client) {
            $subdomain = $this->extractSubdomain($host);
            
            if ($subdomain && !in_array($subdomain, $this->reservedSubdomains)) {
                $client = Client::where('slug', $subdomain)
                               ->where('active', true)
                               ->first();
                
                if ($client) {
                    \Log::debug('SetClientFromSubdomain: Cliente identificado pelo subdomínio', [
                        'subdomain' => $subdomain,
                        'client_id' => $client->id,
                        'client_name' => $client->name,
                    ]);
                }
            }
        }

        // =====================================================================
        // PRIORIDADE 4: Header X-Client-Id (para API)
        // =====================================================================
        if (!$client && $request->hasHeader('X-Client-Id')) {
            $clientId = (int) $request->header('X-Client-Id');
            $client = Client::where('id', $clientId)
                           ->where('active', true)
                           ->first();
            
            if ($client) {
                \Log::debug('SetClientFromSubdomain: Cliente identificado pelo header', [
                    'header_client_id' => $clientId,
                    'client_id' => $client->id,
                ]);
            }
        }

        // =====================================================================
        // PRIORIDADE 5: Parâmetro de query (para testes/debug)
        // =====================================================================
        if (!$client && $request->has('_client_id')) {
            $clientId = (int) $request->get('_client_id');
            $client = Client::where('id', $clientId)
                           ->where('active', true)
                           ->first();
        }

        // =====================================================================
        // PRIORIDADE 6: Cliente padrão (para ambiente local/admin)
        // =====================================================================
        if (!$client && $this->isLocalOrAdmin($host)) {
            $defaultClientId = config('olika.default_client_id');
            if ($defaultClientId) {
                $client = Client::find($defaultClientId);
                
                \Log::debug('SetClientFromSubdomain: Usando cliente padrão', [
                    'default_client_id' => $defaultClientId,
                    'client_name' => $client?->name,
                ]);
            }
        }

        // 6. Se encontrou cliente, definir no contexto
        if ($client) {
            // Setar no request para o ClientScope acessar
            $request->attributes->set('client_id', $client->id);
            $request->attributes->set('client', $client);
            
            // NOTA: Não sobrescrever a sessão aqui!
            // A sessão deve ser setada apenas no login/registro
            // para evitar que o usuário seja redirecionado para outro cliente
            
            // Verificar se o cliente pode operar (não expirado, ativo)
            if (!$client->canOperate()) {
                \Log::warning('SetClientFromSubdomain: Cliente não pode operar', [
                    'client_id' => $client->id,
                    'active' => $client->active,
                    'is_trial' => $client->is_trial,
                    'trial_ends_at' => $client->trial_ends_at,
                ]);

                // Para API, retornar erro JSON
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'client_suspended',
                        'message' => 'Este estabelecimento está temporariamente indisponível.',
                    ], 403);
                }

                // Para web, redirecionar para página de erro
                return response()->view('errors.client-suspended', [
                    'client' => $client,
                ], 403);
            }
        } else {
            // Se não encontrou cliente e não é ambiente permitido
            if (!$this->isLocalOrAdmin($host) && !$this->isPublicRoute($request)) {
                \Log::warning('SetClientFromSubdomain: Cliente não encontrado', [
                    'host' => $host,
                    'subdomain' => $subdomain ?? null,
                ]);

                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'client_not_found',
                        'message' => 'Estabelecimento não encontrado.',
                    ], 404);
                }

                return response()->view('errors.client-not-found', [], 404);
            }
        }

        // Compartilhar cliente com todas as views
        if ($client) {
            view()->share('currentClient', $client);
        }

        return $next($request);
    }

    /**
     * Extrai o subdomínio do host
     */
    protected function extractSubdomain(string $host): ?string
    {
        // Remover porta se existir
        $host = preg_replace('/:\d+$/', '', $host);

        // Verificar se é um domínio base
        foreach ($this->baseDomains as $baseDomain) {
            if ($host === $baseDomain) {
                return null;
            }

            // Se o host termina com .baseDomain, extrair subdomínio
            if (str_ends_with($host, '.' . $baseDomain)) {
                $subdomain = str_replace('.' . $baseDomain, '', $host);
                
                // Se ainda contiver pontos, pegar apenas o primeiro nível
                if (str_contains($subdomain, '.')) {
                    $subdomain = explode('.', $subdomain)[0];
                }

                return $subdomain;
            }
        }

        // Para domínios personalizados (ex: cliente.com.br)
        // Buscar pelo domínio completo na tabela de clients
        return null;
    }

    /**
     * Verifica se é ambiente local ou rota de admin
     */
    protected function isLocalOrAdmin(string $host): bool
    {
        // Ambiente local
        if (in_array($host, ['localhost', '127.0.0.1']) || str_contains($host, 'localhost')) {
            return true;
        }

        // Subdomínios de admin (inclui variações como devdashboard, admindashboard, etc)
        $adminPatterns = ['admin', 'dashboard', 'panel', 'painel', 'staging', 'dev', 'homolog'];
        foreach ($adminPatterns as $pattern) {
            if (str_contains($host, $pattern)) {
                return true;
            }
        }

        // Domínio principal sem subdomínio
        foreach ($this->baseDomains as $baseDomain) {
            if ($host === $baseDomain || $host === 'www.' . $baseDomain) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica se é rota pública que não precisa de client
     */
    protected function isPublicRoute(Request $request): bool
    {
        $publicPaths = [
            'login',
            'register',
            'password',
            'logout',
            'sanctum',
            'webhook',
            'health',
        ];

        $path = $request->path();
        
        foreach ($publicPaths as $publicPath) {
            if (str_starts_with($path, $publicPath)) {
                return true;
            }
        }

        return false;
    }
}
