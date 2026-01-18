<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\CustomerCashback;

class CustomersController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('q', '');
        
        $query = DB::table('customers');
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        $customers = $query->select('customers.*', 
                DB::raw('(SELECT COUNT(*) FROM orders WHERE orders.customer_id = customers.id AND orders.payment_status IN ("paid", "approved")) as total_orders'),
                DB::raw('(SELECT COALESCE(SUM(final_amount), 0) FROM orders WHERE orders.customer_id = customers.id AND payment_status IN ("paid", "approved")) as total_spent'),
                DB::raw('(SELECT COALESCE(SUM(CASE WHEN type="debit" THEN amount ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN type="credit" THEN amount ELSE 0 END), 0) FROM customer_debts WHERE customer_debts.customer_id = customers.id AND status = "open") as total_debts')
            )
            ->orderByDesc('id')
            ->paginate(30);

        return view('dashboard.customers.index', compact('customers', 'search'));
    }

    public function create()
    {
        return view('dashboard.customers.create');
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'cpf' => 'nullable|string|max:14',
            'birth_date' => 'nullable|date',
            'cashback_balance' => 'nullable|numeric|min:0',
            'is_wholesale' => 'nullable|boolean',
        ]);

        $cashbackBalance = $r->input('cashback_balance', 0);
        
        $data['created_at'] = now();
        $data['updated_at'] = now();
        $data['is_wholesale'] = $r->has('is_wholesale') ? 1 : 0;
        unset($data['cashback_balance']); // Remover do array de dados do cliente

        $customerId = DB::table('customers')->insertGetId($data);

        // Criar transação de cashback inicial se o valor foi informado
        if ($cashbackBalance > 0) {
            \App\Models\CustomerCashback::create([
                'customer_id' => $customerId,
                'order_id' => null,
                'amount' => (float)$cashbackBalance,
                'type' => 'credit',
                'description' => 'Saldo inicial de cashback',
            ]);
        }

        return redirect()->route('dashboard.customers.index')->with('success', 'Cliente criado com sucesso!');
    }

    public function show($id)
    {
        $customer = \App\Models\Customer::find($id);
        if (!$customer) {
            return redirect()->route('dashboard.customers.index')->with('error', 'Cliente não encontrado');
        }

        $orders = DB::table('orders')
            ->where('customer_id', $id)
            ->orderByDesc('id')
            ->paginate(10);

        $openDebts = \App\Models\CustomerDebt::with('order')
            ->where('customer_id', $id)
            ->where('status', 'open')
            ->orderByDesc('created_at')
            ->get();

        $debtHistory = \App\Models\CustomerDebt::with('order')
            ->where('customer_id', $id)
            ->where('status', '!=', 'open')
            ->where('type', 'debit') // Mostrar apenas débitos originais, não os créditos de baixa
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        // Calcular estatísticas de pedidos
        $totalOrders = DB::table('orders')
            ->where('customer_id', $id)
            ->count();
        
        $totalOrdersValue = DB::table('orders')
            ->where('customer_id', $id)
            ->sum('final_amount');
        
        $averageOrderValue = $totalOrders > 0 ? ($totalOrdersValue / $totalOrders) : 0;

        return view('dashboard.customers.show', compact('customer', 'orders', 'openDebts', 'debtHistory', 'totalOrders', 'totalOrdersValue', 'averageOrderValue'));
    }

    public function edit($id)
    {
        $customer = DB::table('customers')->find($id);
        if (!$customer) {
            return redirect()->route('dashboard.customers.index')->with('error', 'Cliente não encontrado');
        }

        return view('dashboard.customers.edit', compact('customer'));
    }

    public function update(Request $r, $id)
    {
        $customer = DB::table('customers')->find($id);
        if (!$customer) {
            return redirect()->route('dashboard.customers.index')->with('error', 'Cliente não encontrado');
        }

        $data = $r->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'cpf' => 'nullable|string|max:14',
            'birth_date' => 'nullable|date',
            'cashback_balance' => 'nullable|numeric|min:0',
            'is_wholesale' => 'nullable|boolean',
        ]);

        $targetCashbackBalance = (float)($r->input('cashback_balance', 0) ?? 0);
        
        $data['updated_at'] = now();
        $data['is_wholesale'] = $r->has('is_wholesale') ? 1 : 0;
        unset($data['cashback_balance']); // Remover do array de dados do cliente
        
        DB::table('customers')->where('id', $id)->update($data);

        // Ajustar cashback se o valor foi informado e é diferente do saldo atual
        $currentBalance = \App\Models\CustomerCashback::getBalance($id);
        $difference = $targetCashbackBalance - $currentBalance;
        
        if (abs($difference) > 0.01) { // Tolerância de 1 centavo para diferenças de arredondamento
            if ($difference > 0) {
                // Adicionar cashback
                \App\Models\CustomerCashback::create([
                    'customer_id' => $id,
                    'order_id' => null,
                    'amount' => $difference,
                    'type' => 'credit',
                    'description' => 'Ajuste manual de cashback',
                ]);
            } else {
                // Remover cashback (débito)
                \App\Models\CustomerCashback::create([
                    'customer_id' => $id,
                    'order_id' => null,
                    'amount' => abs($difference),
                    'type' => 'debit',
                    'description' => 'Ajuste manual de cashback',
                ]);
            }
        }

        return redirect()->route('dashboard.customers.index')->with('success', 'Cliente atualizado com sucesso!');
    }

    public function destroy($id)
    {
        DB::table('customers')->where('id', $id)->delete();
        return redirect()->route('dashboard.customers.index')->with('success', 'Cliente excluído com sucesso!');
    }

    /**
     * Atualizar estatísticas de todos os clientes
     */
    public function updateStats(Request $request)
    {
        $customerId = $request->input('customer_id');
        $dryRun = $request->has('dry_run');

        try {
            $query = \App\Models\Customer::query();
            
            if ($customerId) {
                $query->where('id', $customerId);
                $customer = $query->first();
                if (!$customer) {
                    return redirect()->route('dashboard.customers.index')
                        ->with('error', "Cliente com ID {$customerId} não encontrado.");
                }
                $customers = collect([$customer]);
            } else {
                $customers = $query->get();
            }

            $updated = 0;
            $errors = 0;
            $summary = [];

            foreach ($customers as $customer) {
                try {
                    // Buscar pedidos pagos do cliente
                    $paidOrders = $customer->orders()
                        ->whereIn('payment_status', ['approved', 'paid'])
                        ->get();

                    $totalOrders = $paidOrders->count();
                    $totalSpent = $paidOrders->sum('final_amount');

                    // Buscar último pedido pago
                    $lastOrder = $customer->orders()
                        ->whereIn('payment_status', ['approved', 'paid'])
                        ->orderBy('created_at', 'desc')
                        ->first();

                    // Calcular saldo de cashback
                    $loyaltyBalance = \App\Models\CustomerCashback::getBalance($customer->id);

                    // Preparar dados para atualização
                    $oldStats = [
                        'total_orders' => $customer->total_orders ?? 0,
                        'total_spent' => $customer->total_spent ?? 0,
                        'last_order_at' => $customer->last_order_at,
                        'loyalty_balance' => $customer->loyalty_balance ?? 0,
                    ];

                    $newStats = [
                        'total_orders' => $totalOrders,
                        'total_spent' => $totalSpent,
                        'last_order_at' => $lastOrder ? $lastOrder->created_at : null,
                        'loyalty_balance' => $loyaltyBalance,
                    ];

                    // Verificar se há mudanças
                    $hasChanges = false;
                    foreach ($oldStats as $key => $oldValue) {
                        if ($oldValue != $newStats[$key]) {
                            $hasChanges = true;
                            break;
                        }
                    }

                    if ($hasChanges) {
                        if (!$dryRun) {
                            // Atualizar no banco
                            $customer->total_orders = $newStats['total_orders'];
                            $customer->total_spent = $newStats['total_spent'];
                            $customer->last_order_at = $newStats['last_order_at'];
                            $customer->loyalty_balance = $newStats['loyalty_balance'];
                            $customer->save();
                        }

                        $updated++;
                        
                        $summary[] = [
                            'id' => $customer->id,
                            'name' => $customer->name,
                            'old' => $oldStats,
                            'new' => $newStats,
                        ];
                    }
                } catch (\Exception $e) {
                    $errors++;
                    \Log::error('Erro ao atualizar estatísticas do cliente', [
                        'customer_id' => $customer->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $message = $dryRun 
                ? "Modo de teste: {$updated} cliente(s) teriam estatísticas atualizadas."
                : "✅ {$updated} cliente(s) atualizado(s) com sucesso!";

            if ($errors > 0) {
                $message .= " ⚠️ {$errors} erro(s) encontrado(s).";
            }

            return redirect()->route('dashboard.customers.index')
                ->with('success', $message)
                ->with('update_stats_summary', $summary);

        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar estatísticas dos clientes', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('dashboard.customers.index')
                ->with('error', 'Erro ao atualizar estatísticas: ' . $e->getMessage());
        }
    }

    /**
     * Atualizar apenas o cashback do cliente
     */
    public function updateCashback(Request $request, $id)
    {
        $customer = \App\Models\Customer::find($id);
        if (!$customer) {
            return redirect()->route('dashboard.customers.show', $id)
                ->with('error', 'Cliente não encontrado');
        }

        $request->validate([
            'cashback_balance' => 'required|numeric|min:0',
        ]);

        $targetCashbackBalance = (float)$request->input('cashback_balance', 0);
        $currentBalance = \App\Models\CustomerCashback::getBalance($id);
        $difference = $targetCashbackBalance - $currentBalance;

        if (abs($difference) > 0.01) { // Tolerância de 1 centavo
            if ($difference > 0) {
                // Adicionar cashback
                \App\Models\CustomerCashback::create([
                    'customer_id' => $id,
                    'order_id' => null,
                    'amount' => $difference,
                    'type' => 'credit',
                    'description' => 'Ajuste manual de cashback',
                ]);
            } else {
                // Remover cashback (débito)
                \App\Models\CustomerCashback::create([
                    'customer_id' => $id,
                    'order_id' => null,
                    'amount' => abs($difference),
                    'type' => 'debit',
                    'description' => 'Ajuste manual de cashback',
                ]);
            }
        }

        return redirect()->route('dashboard.customers.show', $id)
            ->with('success', 'Saldo de cashback atualizado com sucesso!');
    }

    /**
     * Ajustar saldo devedor do cliente
     */
    public function adjustDebtBalance(Request $request, $id)
    {
        $customer = \App\Models\Customer::find($id);
        if (!$customer) {
            return redirect()->route('dashboard.customers.show', $id)
                ->with('error', 'Cliente não encontrado');
        }

        $request->validate([
            'new_balance' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Calcular saldo atual
            $oldBalance = \App\Models\CustomerDebt::getBalance($id);
            $newBalance = (float)$request->input('new_balance', 0);
            $adjustmentAmount = $newBalance - $oldBalance;

            // Se não houver diferença significativa, não fazer nada
            if (abs($adjustmentAmount) < 0.01) {
                return redirect()->route('dashboard.customers.show', $id)
                    ->with('info', 'O saldo informado é igual ao saldo atual. Nenhum ajuste foi necessário.');
            }

            // Criar registro de ajuste no histórico
            \App\Models\CustomerDebtAdjustment::create([
                'customer_id' => $id,
                'old_balance' => $oldBalance,
                'new_balance' => $newBalance,
                'adjustment_amount' => $adjustmentAmount,
                'reason' => $request->input('reason', 'Ajuste manual de saldo devedor'),
                'created_by' => auth()->id(),
            ]);

            // Criar débito ou crédito para ajustar o saldo
            if ($adjustmentAmount > 0) {
                // Aumentar saldo (criar débito)
                \App\Models\CustomerDebt::create([
                    'customer_id' => $id,
                    'order_id' => null,
                    'amount' => $adjustmentAmount,
                    'type' => 'debit',
                    'status' => 'open',
                    'description' => $request->input('reason', 'Ajuste manual de saldo devedor') . ' (Ajuste: +R$ ' . number_format($adjustmentAmount, 2, ',', '.') . ')',
                ]);
            } else {
                // Diminuir saldo (criar crédito)
                \App\Models\CustomerDebt::create([
                    'customer_id' => $id,
                    'order_id' => null,
                    'amount' => abs($adjustmentAmount),
                    'type' => 'credit',
                    'status' => 'open',
                    'description' => $request->input('reason', 'Ajuste manual de saldo devedor') . ' (Ajuste: -R$ ' . number_format(abs($adjustmentAmount), 2, ',', '.') . ')',
                ]);
            }

            DB::commit();

            \Log::info('Saldo devedor ajustado', [
                'customer_id' => $id,
                'old_balance' => $oldBalance,
                'new_balance' => $newBalance,
                'adjustment_amount' => $adjustmentAmount,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('dashboard.customers.show', $id)
                ->with('success', 'Saldo devedor ajustado com sucesso! De R$ ' . number_format($oldBalance, 2, ',', '.') . ' para R$ ' . number_format($newBalance, 2, ',', '.') . '.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erro ao ajustar saldo devedor', [
                'customer_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('dashboard.customers.show', $id)
                ->with('error', 'Erro ao ajustar saldo devedor: ' . $e->getMessage());
        }
    }
}

