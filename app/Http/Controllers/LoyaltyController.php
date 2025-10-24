<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\LoyaltyProgram;
use App\Models\LoyaltyTransaction;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoyaltyController extends Controller
{
    /**
     * Exibe o painel de fidelidade do cliente
     */
    public function index(Request $request)
    {
        $customer = null;
        $loyaltyProgram = LoyaltyProgram::active()->first();
        
        if ($request->has('phone')) {
            $customer = Customer::where('phone', $request->phone)->first();
        }

        if (!$customer) {
            return view('loyalty.index', compact('loyaltyProgram'));
        }

        $transactions = $customer->loyaltyTransactions()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $totalPoints = $customer->loyaltyTransactions()
            ->where('type', 'earned')
            ->where('is_active', true)
            ->sum('points');

        $redeemedPoints = $customer->loyaltyTransactions()
            ->where('type', 'redeemed')
            ->sum('points');

        $availablePoints = $totalPoints - $redeemedPoints;

        return view('loyalty.index', compact(
            'customer',
            'loyaltyProgram',
            'transactions',
            'totalPoints',
            'redeemedPoints',
            'availablePoints'
        ));
    }

    /**
     * Adiciona pontos por compra
     */
    public function addPoints(Order $order)
    {
        $loyaltyProgram = LoyaltyProgram::active()->first();
        
        if (!$loyaltyProgram || !$order->customer) {
            return false;
        }

        $points = $loyaltyProgram->calculatePoints($order->total_amount);
        
        if ($points > 0) {
            LoyaltyTransaction::create([
                'customer_id' => $order->customer_id,
                'order_id' => $order->id,
                'type' => 'earned',
                'points' => $points,
                'value' => $order->total_amount,
                'description' => "Pontos ganhos pela compra #{$order->order_number}",
                'expires_at' => $loyaltyProgram->points_expiry_days 
                    ? now()->addDays($loyaltyProgram->points_expiry_days) 
                    : null,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Resgata pontos por desconto
     */
    public function redeemPoints(Request $request)
    {
        $request->validate([
            'customer_phone' => 'required|string',
            'points' => 'required|integer|min:100',
        ]);

        $customer = Customer::where('phone', $request->customer_phone)->first();
        
        if (!$customer) {
            return response()->json(['error' => 'Cliente não encontrado'], 404);
        }

        $loyaltyProgram = LoyaltyProgram::active()->first();
        
        if (!$loyaltyProgram) {
            return response()->json(['error' => 'Programa de fidelidade não ativo'], 400);
        }

        if ($request->points < $loyaltyProgram->minimum_points_to_redeem) {
            return response()->json([
                'error' => "Mínimo de {$loyaltyProgram->minimum_points_to_redeem} pontos para resgate"
            ], 400);
        }

        $availablePoints = $customer->loyaltyTransactions()
            ->where('type', 'earned')
            ->where('is_active', true)
            ->sum('points') - 
            $customer->loyaltyTransactions()
            ->where('type', 'redeemed')
            ->sum('points');

        if ($request->points > $availablePoints) {
            return response()->json([
                'error' => "Pontos insuficientes. Disponível: {$availablePoints} pontos"
            ], 400);
        }

        $discountValue = $loyaltyProgram->calculateValue($request->points);

        DB::beginTransaction();
        try {
            // Criar transação de resgate
            LoyaltyTransaction::create([
                'customer_id' => $customer->id,
                'type' => 'redeemed',
                'points' => $request->points,
                'value' => $discountValue,
                'description' => "Resgate de {$request->points} pontos por R$ {$discountValue}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Resgate realizado! Desconto de R$ " . number_format($discountValue, 2, ',', '.'),
                'discount_value' => $discountValue,
                'points_used' => $request->points,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erro ao processar resgate'], 500);
        }
    }

    /**
     * API: Obter pontos do cliente
     */
    public function getCustomerPoints(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $customer = Customer::where('phone', $request->phone)->first();
        
        if (!$customer) {
            return response()->json(['error' => 'Cliente não encontrado'], 404);
        }

        $totalPoints = $customer->loyaltyTransactions()
            ->where('type', 'earned')
            ->where('is_active', true)
            ->sum('points');

        $redeemedPoints = $customer->loyaltyTransactions()
            ->where('type', 'redeemed')
            ->sum('points');

        $availablePoints = $totalPoints - $redeemedPoints;

        return response()->json([
            'customer' => $customer,
            'total_points' => $totalPoints,
            'redeemed_points' => $redeemedPoints,
            'available_points' => $availablePoints,
        ]);
    }
}
