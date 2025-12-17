<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StoreSignupController extends Controller
{
    /**
     * Exibe a página pública de cadastro de lojista
     */
    public function show()
    {
        // Definir planos disponíveis
        $plans = [
            'basic' => [
                'name' => 'Plano Básico',
                'description' => 'Funcionalidades essenciais para gerenciar seu negócio',
                'features' => [
                    'Vendas online e presencial',
                    'PDV (Ponto de Venda)',
                    'Cadastro de produtos e categorias',
                    'Gestão de clientes',
                    'Sistema de cupons',
                    'Cashback e fidelidade',
                    'Relatórios e análises',
                    'Integração com MercadoPago',
                ],
                'price' => 'R$ 99,90/mês',
                'trial_days' => 14,
            ],
            'ia' => [
                'name' => 'Plano WhatsApp',
                'description' => 'Tudo do básico + integração completa com WhatsApp',
                'features' => [
                    'Todas as funcionalidades do Plano Básico',
                    'Integração WhatsApp para notificações',
                    'Envio automático de atualizações de pedidos',
                    'Campanhas de marketing via WhatsApp',
                    'Templates de mensagens personalizáveis',
                    'Agendamento de mensagens',
                    'Suporte a múltiplas instâncias',
                ],
                'price' => 'R$ 149,90/mês',
                'trial_days' => 14,
            ],
        ];

        return view('store-signup', compact('plans'));
    }

    /**
     * Processa o cadastro de novo lojista
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'plan' => 'required|in:basic,ia',
            'whatsapp_phone' => 'nullable|string|max:20',
            'accept_terms' => 'required|accepted',
        ], [
            'company_name.required' => 'O nome da empresa é obrigatório.',
            'name.required' => 'O seu nome é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Digite um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter pelo menos 6 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.',
            'plan.required' => 'O plano é obrigatório.',
            'plan.in' => 'O plano deve ser básico ou WhatsApp.',
            'accept_terms.required' => 'Você deve aceitar os termos de uso.',
            'accept_terms.accepted' => 'Você deve aceitar os termos de uso.',
        ]);

        DB::beginTransaction();
        
        try {
            // Gerar slug único baseado no nome da empresa
            $baseSlug = Str::slug($validated['company_name']);
            $slug = $baseSlug;
            $counter = 1;
            
            while (Client::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            // Definir período de teste (14 dias)
            $trialDays = 14;
            $trialStartedAt = now();
            $trialEndsAt = $trialStartedAt->copy()->addDays($trialDays);

            // Criar cliente SaaS com período de teste
            $client = Client::create([
                'name' => $validated['company_name'],
                'slug' => $slug,
                'plan' => $validated['plan'],
                'whatsapp_phone' => $validated['whatsapp_phone'] ?? null,
                'active' => true, // Ativo durante o período de teste
                'deploy_status' => 'pending',
                'is_trial' => true,
                'trial_started_at' => $trialStartedAt,
                'trial_ends_at' => $trialEndsAt,
            ]);

            // Criar usuário associado ao cliente
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'client_id' => $client->id,
                'email_verified_at' => now(), // Auto-verificar para novos cadastros
            ]);

            Log::info('Novo lojista cadastrado via página pública', [
                'client_id' => $client->id,
                'client_name' => $client->name,
                'user_id' => $user->id,
                'plan' => $client->plan,
                'trial_ends_at' => $trialEndsAt->format('Y-m-d H:i:s'),
            ]);

            DB::commit();

            // Fazer login automático do usuário
            auth()->login($user);

            // Redirecionar para o dashboard com mensagem de sucesso
            return redirect()->route('dashboard.index')
                ->with('success', "Cadastro realizado com sucesso! Você tem {$trialDays} dias de teste gratuito. Seu período de teste termina em " . $trialEndsAt->format('d/m/Y') . ".");

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erro ao cadastrar novo lojista', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao realizar cadastro. Por favor, tente novamente.');
        }
    }
}

