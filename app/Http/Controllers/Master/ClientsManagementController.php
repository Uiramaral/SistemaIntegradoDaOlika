<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ClientsManagementController extends Controller
{
    /**
     * Lista todos os clientes/estabelecimentos
     */
    public function index(Request $request)
    {
        $query = Client::with(['subscription.plan']);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('active', $request->status === 'active');
        }

        if ($request->filled('plan')) {
            $query->whereHas('subscription', function ($q) use ($request) {
                $q->where('plan_id', $request->plan);
            });
        }

        $clients = $query->orderByDesc('created_at')->paginate(20);
        $plans = Plan::active()->get();

        return view('master.clients.index', compact('clients', 'plans'));
    }

    /**
     * Formulário de criação
     */
    public function create()
    {
        $plans = Plan::active()->get();
        return view('master.clients.form', compact('plans'));
    }

    /**
     * Salva novo cliente
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:clients,slug|regex:/^[a-z0-9-]+$/',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'plan_id' => 'required|exists:plans,id',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:6',
        ]);

        // Criar cliente
        $client = Client::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'active' => true,
        ]);

        // Criar assinatura
        $plan = Plan::find($validated['plan_id']);
        $subscription = Subscription::create([
            'client_id' => $client->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'price' => $plan->price,
            'started_at' => now(),
            'current_period_start' => now(),
            'current_period_end' => now()->addDays(30),
        ]);

        $client->update(['subscription_id' => $subscription->id]);

        // Criar usuário admin
        User::create([
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => bcrypt($validated['admin_password']),
            'client_id' => $client->id,
            'role' => 'admin',
        ]);

        return redirect()->route('master.clients.index')
            ->with('success', "Cliente {$client->name} criado com sucesso!");
    }

    /**
     * Exibe detalhes do cliente
     */
    public function show(Client $client)
    {
        $client->load([
            'subscription.plan',
            'subscription.addons',
            'subscription.invoices' => fn($q) => $q->orderByDesc('created_at')->take(10),
            'whatsappInstanceUrls',
        ]);

        $users = User::where('client_id', $client->id)->get();
        $orderStats = [
            'total' => Order::where('client_id', $client->id)->count(),
            'this_month' => Order::where('client_id', $client->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'revenue_this_month' => Order::where('client_id', $client->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('final_amount'),
        ];

        return view('master.clients.show', compact('client', 'users', 'orderStats'));
    }

    /**
     * Formulário de edição
     */
    public function edit(Client $client)
    {
        $plans = Plan::active()->get();
        return view('master.clients.form', compact('client', 'plans'));
    }

    /**
     * Atualiza cliente
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'whatsapp_phone' => 'nullable|string|max:20',
            'plan_id' => 'nullable|exists:plans,id',
            'active' => 'boolean',
            'is_master' => 'boolean',
            'mercadopago_commission_enabled' => 'boolean',
            'mercadopago_commission_amount' => 'nullable|numeric|min:0',
            'is_lifetime_free' => 'boolean',
            'lifetime_plan' => 'nullable|in:basic,ia,custom',
            'lifetime_reason' => 'nullable|string|max:255',
        ]);
        
        // Tratar checkboxes
        $validated['active'] = $request->has('active');
        $validated['is_master'] = $request->has('is_master');
        $validated['is_lifetime_free'] = $request->has('is_lifetime_free');
        
        // Comissão é SEMPRE habilitada (não é opcional)
        $validated['mercadopago_commission_enabled'] = true;
        
        // Garantir que master nunca tenha comissão habilitada
        if ($validated['is_master']) {
            $validated['mercadopago_commission_enabled'] = false;
        }
        
        // Lifetime também pode ser isento de comissão (opcional)
        // if ($validated['is_lifetime_free']) {
        //     $validated['mercadopago_commission_enabled'] = false;
        // }
        
        // Se lifetime está sendo ativado agora, registrar data
        $lifetimeGrantedAt = null;
        if ($validated['is_lifetime_free'] && !$client->is_lifetime_free) {
            $lifetimeGrantedAt = now();
        } elseif (!$validated['is_lifetime_free']) {
            // Se desativado, limpar dados lifetime
            $validated['lifetime_plan'] = null;
            $validated['lifetime_reason'] = null;
            $lifetimeGrantedAt = null;
        } else {
            // Manter data existente
            $lifetimeGrantedAt = $client->lifetime_granted_at;
        }
        
        // Atualizar cliente
        $client->update([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'whatsapp_phone' => $validated['whatsapp_phone'] ?? null,
            'active' => $validated['active'],
            'is_master' => $validated['is_master'],
            'mercadopago_commission_enabled' => $validated['mercadopago_commission_enabled'],
            'mercadopago_commission_amount' => $validated['mercadopago_commission_amount'] ?? 0.49,
            'is_lifetime_free' => $validated['is_lifetime_free'],
            'lifetime_plan' => $validated['lifetime_plan'] ?? null,
            'lifetime_reason' => $validated['lifetime_reason'] ?? null,
            'lifetime_granted_at' => $lifetimeGrantedAt,
        ]);
        
        // Atualizar plano se fornecido
        if (!empty($validated['plan_id'])) {
            $newPlan = Plan::find($validated['plan_id']);
            if ($newPlan && $client->subscription) {
                if ($client->subscription->plan_id != $newPlan->id) {
                    $client->subscription->update([
                        'plan_id' => $newPlan->id,
                        'price' => $newPlan->price,
                    ]);
                }
            } elseif ($newPlan && !$client->subscription) {
                // Criar assinatura se não existir
                $subscription = Subscription::create([
                    'client_id' => $client->id,
                    'plan_id' => $newPlan->id,
                    'status' => 'active',
                    'price' => $newPlan->price,
                    'started_at' => now(),
                    'current_period_start' => now(),
                    'current_period_end' => now()->addDays(30),
                ]);
                $client->update(['subscription_id' => $subscription->id]);
            }
        }

        return redirect()->route('master.clients.show', $client)
            ->with('success', 'Cliente atualizado com sucesso!');
    }

    /**
     * Ativa/Desativa cliente
     */
    public function toggleStatus(Client $client)
    {
        $client->update(['active' => !$client->active]);

        $status = $client->active ? 'ativado' : 'desativado';
        return back()->with('success', "Cliente {$status} com sucesso!");
    }

    /**
     * Altera o plano do cliente
     */
    public function changePlan(Request $request, Client $client)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $newPlan = Plan::find($request->plan_id);
        $subscription = $client->subscription;

        if ($subscription) {
            // Calcular diferença proporcional
            $oldPrice = $subscription->price;
            $newPrice = $newPlan->price;
            $proratedDifference = $subscription->calculateProratedPrice($newPrice - $oldPrice);

            $subscription->update([
                'plan_id' => $newPlan->id,
                'price' => $newPrice,
            ]);

            return back()->with('success', "Plano alterado para {$newPlan->name}. Diferença proporcional: R$ " . number_format($proratedDifference, 2, ',', '.'));
        }

        // Criar nova assinatura se não existir
        $subscription = Subscription::create([
            'client_id' => $client->id,
            'plan_id' => $newPlan->id,
            'status' => 'active',
            'price' => $newPlan->price,
            'started_at' => now(),
            'current_period_start' => now(),
            'current_period_end' => now()->addDays(30),
        ]);

        $client->update(['subscription_id' => $subscription->id]);

        return back()->with('success', "Plano {$newPlan->name} ativado para o cliente!");
    }

    /**
     * Renova assinatura manualmente
     */
    public function renewSubscription(Client $client)
    {
        $subscription = $client->subscription;
        
        if (!$subscription) {
            return back()->with('error', 'Cliente não possui assinatura ativa.');
        }

        $subscription->renew();

        return back()->with('success', 'Assinatura renovada por mais 30 dias!');
    }

    /**
     * Exclui cliente (CUIDADO: Ação irreversível)
     */
    public function destroy(Client $client)
    {
        // Verificar se é master (não pode excluir master)
        if ($client->is_master) {
            return back()->with('error', 'Não é possível excluir o cliente master!');
        }

        $clientName = $client->name;
        
        try {
            \DB::beginTransaction();
            
            // Excluir relacionamentos primeiro
            // 1. Usuários do cliente
            User::where('client_id', $client->id)->delete();
            
            // 2. Assinatura
            if ($client->subscription) {
                $client->subscription->delete();
            }
            
            // 3. Pedidos (opcional - comentar se quiser manter histórico)
            // Order::where('client_id', $client->id)->delete();
            
            // 4. WhatsApp instâncias
            $client->whatsappInstanceUrls()->delete();
            
            // 5. Cliente
            $client->delete();
            
            \DB::commit();
            
            return redirect()->route('master.clients.index')
                ->with('success', "Cliente {$clientName} excluído com sucesso!");
                
        } catch (\Exception $e) {
            \DB::rollBack();
            
            \Log::error('Erro ao excluir cliente', [
                'client_id' => $client->id,
                'error' => $e->getMessage(),
            ]);
            
            return back()->with('error', 'Erro ao excluir cliente. Verifique se não há dependências.');
        }
    }
}
