<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LoyaltyProgram;
use App\Models\LoyaltyTransaction;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LoyaltyController extends Controller
{
    public function index()
    {
        // Buscar ou criar programa de fidelidade
        $loyaltyProgram = LoyaltyProgram::first();
        if (!$loyaltyProgram) {
            $loyaltyProgram = LoyaltyProgram::create([
                'name' => 'Programa de Fidelidade Olika',
                'description' => 'Programa de pontos de fidelidade',
                'points_per_real' => 10,
                'real_per_point' => 0.10,
                'minimum_points_to_redeem' => 100,
                'points_expiry_days' => 365,
                'is_active' => true,
                'start_date' => now(),
            ]);
        }

        // Calcular estatísticas
        $totalPointsEarned = LoyaltyTransaction::where('type', 'earned')->sum('points') ?? 0;
        $totalPointsRedeemed = LoyaltyTransaction::where('type', 'redeemed')->sum('points') ?? 0;
        $totalPointsAvailable = max(0, $totalPointsEarned - $totalPointsRedeemed);
        
        // Calcular clientes com pontos ativos
        $activeCustomers = Customer::whereHas('loyaltyTransactions', function($q) {
            $q->where('type', 'earned')->where('is_active', true);
        })->get()->filter(function($customer) {
            return $customer->available_points > 0;
        })->count();

        // Taxa de resgate (pontos resgatados / pontos emitidos * 100)
        $redemptionRate = $totalPointsEarned > 0 
            ? round(($totalPointsRedeemed / $totalPointsEarned) * 100, 1) 
            : 0;

        // Buscar últimas transações
        $recentTransactions = LoyaltyTransaction::with(['customer', 'order'])
            ->latest()
            ->limit(20)
            ->get();

        // Buscar top clientes por pontos disponíveis
        $topCustomers = Customer::withSum(['loyaltyTransactions as total_earned' => function($q) {
            $q->where('type', 'earned')->where('is_active', true);
        }], 'points')
        ->withSum(['loyaltyTransactions as total_redeemed' => function($q) {
            $q->where('type', 'redeemed');
        }], 'points')
        ->havingRaw('COALESCE(total_earned, 0) > 0')
        ->orderByRaw('COALESCE(total_earned, 0) - COALESCE(total_redeemed, 0) DESC')
        ->limit(10)
        ->get()
        ->map(function($customer) {
            $customer->available_points = ($customer->total_earned ?? 0) - ($customer->total_redeemed ?? 0);
            return $customer;
        });

        // Buscar configurações do programa
        $settings = $this->getLoyaltySettings($loyaltyProgram);

        return view('dashboard.loyalty.index', compact(
            'loyaltyProgram',
            'totalPointsEarned',
            'totalPointsRedeemed',
            'totalPointsAvailable',
            'activeCustomers',
            'redemptionRate',
            'recentTransactions',
            'topCustomers',
            'settings'
        ));
    }

    /**
     * Buscar configurações de fidelidade
     */
    private function getLoyaltySettings($loyaltyProgram): array
    {
        return [
            'enabled' => $loyaltyProgram->is_active ?? true,
            'points_per_real' => (float)($loyaltyProgram->points_per_real ?? 10.0),
            'real_per_point' => (float)($loyaltyProgram->real_per_point ?? 0.10),
            'minimum_points' => (int)($loyaltyProgram->minimum_points_to_redeem ?? 100),
            'expiry_days' => (int)($loyaltyProgram->points_expiry_days ?? 365),
            'min_order_value' => (float)(DB::table('payment_settings')->where('key', 'loyalty_min_order')->value('value') ?? 0),
        ];
    }

    /**
     * Salvar configurações do programa de fidelidade
     */
    public function saveSettings(Request $request)
    {
        $validated = $request->validate([
            'loyalty_enabled' => 'nullable|boolean',
            'points_per_real' => 'required|numeric|min:0.01',
            'real_per_point' => 'required|numeric|min:0.001',
            'minimum_points' => 'required|integer|min:1',
            'expiry_days' => 'nullable|integer|min:1',
            'min_order_value' => 'nullable|numeric|min:0',
        ]);

        $loyaltyProgram = LoyaltyProgram::first();
        if (!$loyaltyProgram) {
            $loyaltyProgram = LoyaltyProgram::create([
                'name' => 'Programa de Fidelidade Olika',
                'description' => 'Programa de pontos de fidelidade',
                'start_date' => now(),
            ]);
        }

        $loyaltyProgram->update([
            'is_active' => $request->has('loyalty_enabled') && $request->loyalty_enabled,
            'points_per_real' => $validated['points_per_real'],
            'real_per_point' => $validated['real_per_point'],
            'minimum_points_to_redeem' => $validated['minimum_points'],
            'points_expiry_days' => $validated['expiry_days'] ?? null,
        ]);

        // Salvar valor mínimo do pedido em payment_settings
        if (Schema::hasTable('payment_settings')) {
            DB::table('payment_settings')->updateOrInsert(
                ['key' => 'loyalty_min_order'],
                [
                    'value' => (string)($validated['min_order_value'] ?? 0),
                    'updated_at' => now(),
                    'created_at' => DB::raw('COALESCE(created_at, NOW())')
                ]
            );
        }

        return redirect()->route('dashboard.loyalty.index')
            ->with('success', 'Configurações do programa de fidelidade salvas com sucesso!');
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        return view('dashboard.loyalty.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'points' => 'required|integer|min:1',
            'type' => 'required|in:earned,redeemed,bonus,adjustment',
            'description' => 'nullable|string|max:255',
        ]);

        LoyaltyTransaction::create([
            'customer_id' => $validated['customer_id'],
            'order_id' => null,
            'type' => $validated['type'],
            'points' => abs((int)$validated['points']),
            'value' => null,
            'description' => $validated['description'] ?? 'Ajuste manual de pontos',
            'is_active' => $validated['type'] === 'earned' || $validated['type'] === 'bonus',
            'expires_at' => $validated['type'] === 'earned' || $validated['type'] === 'bonus'
                ? (LoyaltyProgram::first()?->points_expiry_days ? now()->addDays(LoyaltyProgram::first()->points_expiry_days) : null)
                : null,
        ]);

        return redirect()->route('dashboard.loyalty.index')
            ->with('success', 'Transação de fidelidade criada com sucesso!');
    }

    public function edit(LoyaltyTransaction $loyalty)
    {
        $loyalty->load(['customer', 'order']);
        return view('dashboard.loyalty.edit', compact('loyalty'));
    }

    public function update(Request $request, LoyaltyTransaction $loyalty)
    {
        $validated = $request->validate([
            'points' => 'required|integer|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        $loyalty->update([
            'points' => abs((int)$validated['points']),
            'description' => $validated['description'] ?? $loyalty->description,
        ]);

        return redirect()->route('dashboard.loyalty.index')
            ->with('success', 'Transação de fidelidade atualizada com sucesso!');
    }

    public function destroy(LoyaltyTransaction $loyalty)
    {
        $loyalty->delete();
        return redirect()->route('dashboard.loyalty.index')
            ->with('success', 'Transação de fidelidade excluída com sucesso!');
    }
}
