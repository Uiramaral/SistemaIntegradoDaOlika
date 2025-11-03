<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerCashback;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CashbackController extends Controller
{
    public function index()
    {
        // Calcular totais de cashback
        $totalCredits = CustomerCashback::where('type', 'credit')->sum('amount') ?? 0;
        $totalDebits = CustomerCashback::where('type', 'debit')->sum('amount') ?? 0;
        $totalAvailable = max(0, (float)$totalCredits - (float)$totalDebits);
        
        // Calcular quantos clientes têm saldo de cashback
        $activeCustomers = Customer::whereHas('cashbackTransactions', function($q) {
            // Clientes com pelo menos uma transação
        })->get()->filter(function($customer) {
            return CustomerCashback::getBalance($customer->id) > 0;
        })->count();
        
        // Buscar últimas transações
        $recentTransactions = CustomerCashback::with(['customer', 'order'])
            ->latest()
            ->limit(20)
            ->get();
        
        // Buscar configurações do cashback
        $cashbackSettings = $this->getCashbackSettings();
        
        return view('dashboard.cashback.index', compact(
            'totalCredits',
            'totalDebits', 
            'totalAvailable',
            'activeCustomers',
            'recentTransactions',
            'cashbackSettings'
        ));
    }

    /**
     * Buscar configurações de cashback
     */
    private function getCashbackSettings(): array
    {
        $keys = [
            'cashback_enabled',
            'cashback_percentage',
            'cashback_min_purchase',
            'cashback_max_amount',
            'cashback_expiry_days'
        ];
        
        $settings = [];
        if (Schema::hasTable('payment_settings')) {
            $results = DB::table('payment_settings')
                ->whereIn('key', $keys)
                ->pluck('value', 'key')
                ->toArray();
            
            $settings = [
                'enabled' => isset($results['cashback_enabled']) && $results['cashback_enabled'] === '1',
                'percentage' => (float)($results['cashback_percentage'] ?? 5.0),
                'min_purchase' => (float)($results['cashback_min_purchase'] ?? 30.0),
                'max_amount' => (float)($results['cashback_max_amount'] ?? 50.0),
                'expiry_days' => (int)($results['cashback_expiry_days'] ?? 90),
            ];
        } else {
            // Valores padrão se a tabela não existir
            $settings = [
                'enabled' => true,
                'percentage' => 5.0,
                'min_purchase' => 30.0,
                'max_amount' => 50.0,
                'expiry_days' => 90,
            ];
        }
        
        return $settings;
    }

    /**
     * Salvar configurações de cashback
     */
    public function saveSettings(Request $request)
    {
        $validated = $request->validate([
            'cashback_enabled' => 'nullable|boolean',
            'cashback_percentage' => 'required|numeric|min:0|max:100',
            'cashback_min_purchase' => 'required|numeric|min:0',
            'cashback_max_amount' => 'nullable|numeric|min:0',
            'cashback_expiry_days' => 'required|integer|min:1',
        ]);

        $settingsToSave = [
            'cashback_enabled' => $request->has('cashback_enabled') && $request->cashback_enabled ? '1' : '0',
            'cashback_percentage' => (string)$validated['cashback_percentage'],
            'cashback_min_purchase' => (string)$validated['cashback_min_purchase'],
            'cashback_max_amount' => $validated['cashback_max_amount'] ? (string)$validated['cashback_max_amount'] : '0',
            'cashback_expiry_days' => (string)$validated['cashback_expiry_days'],
        ];

        if (Schema::hasTable('payment_settings')) {
            foreach ($settingsToSave as $key => $value) {
                DB::table('payment_settings')->updateOrInsert(
                    ['key' => $key],
                    [
                        'value' => $value,
                        'updated_at' => now(),
                        'created_at' => DB::raw('COALESCE(created_at, NOW())')
                    ]
                );
            }
        }

        return redirect()->route('dashboard.cashback.index')
            ->with('success', 'Configurações do cashback salvas com sucesso!');
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        return view('dashboard.cashback.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:credit,debit',
            'description' => 'nullable|string|max:255',
        ]);

        CustomerCashback::create([
            'customer_id' => $validated['customer_id'],
            'order_id' => null,
            'amount' => abs((float)$validated['amount']),
            'type' => $validated['type'],
            'description' => $validated['description'] ?? 'Ajuste manual de cashback',
        ]);

        return redirect()->route('dashboard.cashback.index')
            ->with('success', 'Transação de cashback criada com sucesso!');
    }

    public function edit(CustomerCashback $cashback)
    {
        $cashback->load(['customer', 'order']);
        return view('dashboard.cashback.edit', compact('cashback'));
    }

    public function update(Request $request, CustomerCashback $cashback)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        $cashback->update([
            'amount' => abs((float)$validated['amount']),
            'description' => $validated['description'] ?? $cashback->description,
        ]);

        return redirect()->route('dashboard.cashback.index')
            ->with('success', 'Transação de cashback atualizada com sucesso!');
    }

    public function destroy(CustomerCashback $cashback)
    {
        $cashback->delete();
        return redirect()->route('dashboard.cashback.index')
            ->with('success', 'Transação de cashback excluída com sucesso!');
    }
}