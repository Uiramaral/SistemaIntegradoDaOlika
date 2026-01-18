<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Client;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Setting;

class RegisterController extends Controller
{
    /**
     * Exibe o formulário de registro de estabelecimento
     */
    public function showForm()
    {
        // Buscar planos ativos
        $plans = [];
        try {
            if (\Schema::hasTable('plans')) {
                $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
            }
        } catch (\Exception $e) {
            // Tabela plans ainda não existe
        }
        
        return view('auth.register', compact('plans'));
    }

    /**
     * Processa o registro de novo estabelecimento
     */
    public function register(Request $request)
    {
        // Validação
        $validator = Validator::make($request->all(), [
            'business_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'slug' => 'required|string|max:50|regex:/^[a-z0-9-]+$/|unique:clients,slug',
            'admin_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'terms' => 'required|accepted',
        ], [
            'business_name.required' => 'O nome do estabelecimento é obrigatório.',
            'phone.required' => 'O telefone é obrigatório.',
            'slug.required' => 'O slug (URL) é obrigatório.',
            'slug.regex' => 'O slug deve conter apenas letras minúsculas, números e hífens.',
            'slug.unique' => 'Este slug já está em uso. Escolha outro.',
            'admin_name.required' => 'O nome do administrador é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Digite um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter pelo menos 6 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.',
            'terms.required' => 'Você precisa aceitar os termos de uso.',
            'terms.accepted' => 'Você precisa aceitar os termos de uso.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // 1. Criar o cliente (estabelecimento)
            $client = Client::create([
                'name' => $request->business_name,
                'slug' => strtolower($request->slug),
                'phone' => $request->phone,
                'active' => true,
                'is_trial' => true,
                'trial_started_at' => now(),
                'trial_ends_at' => now()->addDays(7),
            ]);

            // 2. Buscar o plano selecionado
            $plan = null;
            if ($request->filled('plan_id')) {
                $plan = Plan::find($request->plan_id);
            } elseif ($request->filled('plan_slug')) {
                $plan = Plan::where('slug', $request->plan_slug)->first();
            }
            
            // Plano padrão se não encontrou
            if (!$plan) {
                $plan = Plan::where('slug', 'basico')->first();
            }

            // 3. Criar assinatura (se tiver plano)
            if ($plan) {
                $subscription = Subscription::create([
                    'client_id' => $client->id,
                    'plan_id' => $plan->id,
                    'status' => 'active',
                    'price' => $plan->price,
                    'started_at' => now(),
                    'current_period_start' => now(),
                    'current_period_end' => now()->addDays(7), // Período trial
                    'trial_ends_at' => now()->addDays(7),
                ]);

                $client->update(['subscription_id' => $subscription->id]);
            }

            // 4. Criar usuário admin
            $user = User::create([
                'name' => $request->admin_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'client_id' => $client->id,
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);

            // 5. Criar configurações padrão
            Setting::create([
                'client_id' => $client->id,
                'store_name' => $request->business_name,
                'store_phone' => $request->phone,
            ]);

            DB::commit();

            // Logar o usuário automaticamente
            auth()->login($user);
            
            // IMPORTANTE: Setar o client_id na sessão para o novo cliente!
            // Isso garante que o usuário veja seu próprio estabelecimento, não a Olika
            session(['client_id' => $client->id]);
            
            // Também setar no request para middlewares subsequentes
            if (request()) {
                request()->attributes->set('client_id', $client->id);
                request()->attributes->set('client', $client);
            }

            return redirect()->route('dashboard.index')
                ->with('success', 'Bem-vindo ao ' . $client->name . '! Seu estabelecimento foi criado com sucesso. Você tem 7 dias de teste grátis!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erro ao registrar estabelecimento', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao criar estabelecimento. Tente novamente.');
        }
    }
}
