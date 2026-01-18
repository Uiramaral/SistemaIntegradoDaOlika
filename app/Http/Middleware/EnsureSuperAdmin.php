<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware EnsureSuperAdmin
 * 
 * Garante que apenas usuários da Olika Tecnologia (client_id = 1 ou NULL)
 * com role 'super_admin' possam acessar rotas protegidas do admin master.
 * 
 * Uso nas rotas:
 * Route::middleware(['auth', 'super.admin'])->group(function () {
 *     Route::get('/admin/clients', ...);
 * });
 */
class EnsureSuperAdmin
{
    /**
     * Client ID da Olika Tecnologia (admin master)
     */
    protected const OLIKA_CLIENT_ID = 1;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Usuário não autenticado
        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'unauthenticated',
                    'message' => 'Você precisa estar autenticado para acessar esta área.',
                ], 401);
            }
            return redirect()->route('login');
        }

        // Verificar se é super admin
        if (!$this->isSuperAdmin($user)) {
            \Log::warning('EnsureSuperAdmin: Acesso negado', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_client_id' => $user->client_id,
                'user_role' => $user->role ?? 'undefined',
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'forbidden',
                    'message' => 'Você não tem permissão para acessar esta área.',
                ], 403);
            }

            abort(403, 'Acesso restrito à Olika Tecnologia.');
        }

        return $next($request);
    }

    /**
     * Verifica se o usuário é super admin da Olika
     * 
     * Condições para ser super admin:
     * - role = 'super_admin' OU
     * - client_id = 1 (Olika) OU
     * - client_id = NULL (admin global)
     */
    protected function isSuperAdmin($user): bool
    {
        // Se tem o método isSuperAdmin no model, usar ele
        if (method_exists($user, 'isSuperAdmin')) {
            return $user->isSuperAdmin();
        }

        // Fallback: verificar manualmente
        // Super admin deve ter role 'super_admin' OU pertencer à Olika (client_id = 1)
        if (($user->role ?? null) === 'super_admin') {
            return true;
        }

        // Usuário da Olika (client_id = 1) ou global (client_id = NULL)
        if ($user->client_id === null || $user->client_id === self::OLIKA_CLIENT_ID) {
            return true;
        }

        return false;
    }
}
