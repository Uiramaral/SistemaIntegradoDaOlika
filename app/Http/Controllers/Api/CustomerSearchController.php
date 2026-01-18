<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerSearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->query('q', ''));
        if ($q === '') return response()->json([]);

        $rows = Customer::query()
            ->where(function($w) use ($q){
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('phone', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            })
            ->limit(10)
            ->get(['id','name','phone','email']);

        // Buscar endereço principal de cada cliente
        $out = $rows->map(function($c){
            $endereco = null;
            $addr = \DB::table('addresses')
                ->where('customer_id', $c->id)
                ->where('is_primary', 1)
                ->first();
            
            if($addr) {
                $endereco = [
                    'rua' => $addr->street,
                    'numero' => $addr->number,
                    'cep' => $addr->zip_code,
                    'complemento' => $addr->complement,
                    'bairro' => $addr->neighborhood,
                    'cidade' => $addr->city,
                    'uf' => $addr->state,
                ];
            }

            return [
                'id'       => $c->id,
                'nome'     => $c->name,
                'telefone' => $c->phone,
                'email'    => $c->email,
                'endereco' => $endereco,
            ];
        });

        return response()->json($out);
    }

    /**
     * Retorna o contexto do cliente para injeção no prompt da IA
     * 
     * Endpoint: POST /api/customer-context
     * Body: { "phone": "5571987019420" }
     * Headers: X-API-Token: {token}
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getContext(Request $request)
    {
        // Autenticação por token
        $token = $request->header('X-API-Token');
        $validToken = env('API_SECRET') ?? env('WEBHOOK_TOKEN') ?? env('WH_API_TOKEN');
        
        if (empty($validToken)) {
            Log::error('CustomerSearchController::getContext: Token de validação não configurado no .env');
            return response()->json([
                'error' => 'Server configuration error'
            ], 500);
        }
        
        if ($token !== $validToken) {
            Log::warning('CustomerSearchController::getContext: Tentativa de acesso não autorizado', [
                'ip' => $request->ip(),
                'token_recebido' => $token ? '***' . substr($token, -4) : 'não fornecido'
            ]);
            return response()->json([
                'error' => 'Unauthorized'
            ], 403);
        }
        
        // Obter número de telefone do corpo da requisição
        $phone = $request->input('phone');
        
        if (empty($phone)) {
            return response()->json([
                'error' => 'Phone not provided'
            ], 400);
        }
        
        // Limpar número (apenas dígitos)
        $phoneDigits = preg_replace('/\D/', '', $phone);
        
        try {
            // Buscar cliente pelo telefone
            $customer = DB::table('customers')
                ->where('phone', 'like', "%{$phoneDigits}%")
                ->orderByDesc('id')
                ->first();
            
            $context = [
                'name' => null,
                'has_customer' => false,
                'last_order' => null,
                'last_order_status' => null,
                'last_order_total' => null,
                'total_orders' => 0,
                'loyalty_points' => null,
            ];
            
            if ($customer) {
                $context['name'] = $customer->name ?? 'Cliente';
                $context['has_customer'] = true;
                
                // Buscar último pedido do cliente
                $lastOrder = DB::table('orders')
                    ->where('customer_id', $customer->id)
                    ->orderByDesc('id')
                    ->first();
                
                if ($lastOrder) {
                    $context['last_order'] = $lastOrder->order_number ?? $lastOrder->id;
                    $context['last_order_status'] = $lastOrder->status ?? null;
                    $context['last_order_total'] = $lastOrder->total ?? null;
                }
                
                // Contar total de pedidos
                $totalOrders = DB::table('orders')
                    ->where('customer_id', $customer->id)
                    ->count();
                $context['total_orders'] = $totalOrders;
                
                // Buscar pontos de fidelidade (se existir a tabela)
                if (DB::getSchemaBuilder()->hasTable('loyalty_transactions')) {
                    $loyaltyBalance = DB::table('loyalty_transactions')
                        ->where('customer_id', $customer->id)
                        ->selectRaw('SUM(points) as balance')
                        ->first();
                    
                    $context['loyalty_points'] = $loyaltyBalance->balance ?? 0;
                }
            }
            
            return response()->json($context);
            
        } catch (\Exception $e) {
            Log::error('CustomerSearchController::getContext: Erro ao buscar contexto do cliente', [
                'error' => $e->getMessage(),
                'phone' => $phoneDigits
            ]);
            
            return response()->json([
                'error' => 'Internal error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
