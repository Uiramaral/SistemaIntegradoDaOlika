<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartNotEmpty
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $cart = Session::get('cart', []);
        
        if (empty($cart)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Carrinho vazio',
                    'message' => 'Adicione produtos ao carrinho antes de finalizar o pedido'
                ], 400);
            }
            
            return redirect()->route('menu.index')
                ->with('error', 'Seu carrinho está vazio. Adicione produtos antes de finalizar o pedido.');
        }

        return $next($request);
    }
}