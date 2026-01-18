<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureNatalThemeActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar se o tema natal está ativo
        if (!$this->isTemaNatalAtivo()) {
            // Redirecionar para o cardápio normal
            return redirect()->route('pedido.index');
        }

        return $next($request);
    }

    /**
     * Verifica se o tema natal está ativo
     * Cria o registro se não existir
     */
    private function isTemaNatalAtivo(): bool
    {
        try {
            $value = DB::table('payment_settings')
                ->where('key', 'tema_natal_ativo')
                ->value('value');
            
            // Se não existir o registro, criar com valor '0' (desativado)
            if ($value === null) {
                DB::table('payment_settings')->updateOrInsert(
                    ['key' => 'tema_natal_ativo'],
                    [
                        'value' => '0',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
                return false;
            }
            
            return $value === '1' || $value === 1 || $value === true;
        } catch (\Exception $e) {
            // Se houver erro (tabela não existe, etc), retornar false
            Log::warning('Erro ao verificar tema natal ativo no middleware: ' . $e->getMessage());
            return false;
        }
    }
}

