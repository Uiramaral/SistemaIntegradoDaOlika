<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Referral;
use App\Models\LoyaltyTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReferralController extends Controller
{
    /**
     * Exibe o painel de indicações
     */
    public function index(Request $request)
    {
        $customer = null;
        $referrals = collect();
        $referralStats = null;

        if ($request->has('phone')) {
            $customer = Customer::where('phone', $request->phone)->first();
            
            if ($customer) {
                $referrals = $customer->referrals()
                    ->with('referred')
                    ->orderBy('created_at', 'desc')
                    ->paginate(20);

                $referralStats = [
                    'total_referrals' => $customer->referrals()->count(),
                    'active_referrals' => $customer->referrals()->active()->count(),
                    'used_referrals' => $customer->referrals()->used()->count(),
                    'total_rewards' => $customer->referrals()->used()->sum('reward_amount'),
                ];
            }
        }

        return view('referral.index', compact('customer', 'referrals', 'referralStats'));
    }

    /**
     * Cria nova indicação
     */
    public function create(Request $request)
    {
        $request->validate([
            'referrer_phone' => 'required|string',
            'referred_phone' => 'required|string',
            'referred_name' => 'required|string|max:255',
        ]);

        // Verificar se o referrer existe
        $referrer = Customer::where('phone', $request->referrer_phone)->first();
        if (!$referrer) {
            return response()->json(['error' => 'Cliente indicador não encontrado'], 404);
        }

        // Verificar se o indicado já existe
        $referred = Customer::where('phone', $request->referred_phone)->first();
        if ($referred) {
            return response()->json(['error' => 'Cliente já cadastrado'], 400);
        }

        DB::beginTransaction();
        try {
            // Criar cliente indicado
            $referred = Customer::create([
                'name' => $request->referred_name,
                'phone' => $request->referred_phone,
                'data_cadastro' => now(),
            ]);

            // Criar indicação
            $referral = Referral::createReferral(
                $referrer->id,
                $referred->id,
                30 // 30 dias para expirar
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Indicação criada com sucesso!',
                'referral_code' => $referral->code,
                'referred_customer' => $referred,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erro ao criar indicação'], 500);
        }
    }

    /**
     * Processa indicação quando o indicado faz primeira compra
     */
    public function processReferral(Order $order)
    {
        if (!$order->customer) {
            return false;
        }

        // Buscar indicação ativa para este cliente
        $referral = Referral::where('referred_id', $order->customer_id)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$referral) {
            return false;
        }

        DB::beginTransaction();
        try {
            // Marcar indicação como usada
            $referral->markAsUsed();

            // Adicionar recompensa para o indicador
            if ($referral->reward_type === 'points') {
                LoyaltyTransaction::create([
                    'customer_id' => $referral->referrer_id,
                    'order_id' => $order->id,
                    'type' => 'bonus',
                    'points' => (int) $referral->reward_amount,
                    'value' => $referral->reward_amount,
                    'description' => "Bônus por indicação - Cliente: {$order->customer->name}",
                ]);
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * API: Obter código de indicação
     */
    public function getReferralCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $customer = Customer::where('phone', $request->phone)->first();
        
        if (!$customer) {
            return response()->json(['error' => 'Cliente não encontrado'], 404);
        }

        $activeReferral = $customer->referrals()
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$activeReferral) {
            // Criar nova indicação se não existir
            $referral = Referral::createReferral($customer->id, $customer->id, 30);
            $activeReferral = $referral;
        }

        return response()->json([
            'referral_code' => $activeReferral->code,
            'expires_at' => $activeReferral->expires_at,
            'reward_amount' => $activeReferral->reward_amount,
            'reward_type' => $activeReferral->reward_type,
        ]);
    }

    /**
     * API: Validar código de indicação
     */
    public function validateReferralCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $referral = Referral::where('code', $request->code)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->with('referrer')
            ->first();

        if (!$referral) {
            return response()->json(['error' => 'Código de indicação inválido ou expirado'], 404);
        }

        return response()->json([
            'valid' => true,
            'referrer' => $referral->referrer,
            'reward_amount' => $referral->reward_amount,
            'reward_type' => $referral->reward_type,
        ]);
    }
}
