<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use App\Services\WhatsAppService;

class StoreSignupController extends Controller
{
    /**
     * Exibe a página pública de cadastro de lojista
     */
    public function show()
    {
        // Buscar TODOS os planos (ativos e inativos) ordenados
        // Landing page pública deve mostrar todos os planos disponíveis
        $plansFromDb = \App\Models\Plan::ordered()->get();
        
        // Obter configurações do master settings (fonte única de verdade)
        $commission = \App\Models\MasterSetting::get('registration_default_commission', 0.49);
        $trialDays = \App\Models\MasterSetting::getRegistrationTrialDays(); // Usar sempre master settings
        
        // Mapear planos do banco para formato esperado pela view
        $plans = [];
        foreach ($plansFromDb as $plan) {
            $features = $plan->features_list; // Usar accessor
            
            // Adicionar informação de comissão na feature "Integração Mercado Pago"
            $features = array_map(function($feature) use ($commission) {
                if (stripos($feature, 'Integração') !== false && stripos($feature, 'Mercado') !== false) {
                    return $feature . ' (taxa de R$ ' . number_format($commission, 2, ',', '.') . ' por venda)';
                }
                return $feature;
            }, $features);
            
            $plans[$plan->slug] = [
                'name' => $plan->name,
                'description' => $plan->description,
                'featured' => $plan->is_featured,
                'features' => $features,
                'price' => $plan->formatted_price,
                'price_label' => '/mês', // Forçar mensal (não há pagamento anual por enquanto)
                'trial_days' => $trialDays, // SEMPRE usar master settings
            ];
        }
        
        // Se não houver planos no banco, usar hardcoded como fallback
        if (empty($plans)) {
            $plans = [
                'basic' => [
                    'name' => 'Plano Básico',
                    'description' => 'Funcionalidades essenciais para gerenciar seu negócio',
                    'features' => [
                        'Vendas online e presencial',
                        'PDV (Ponto de Venda) completo',
                        'Cardápio digital ilimitado',
                        'Cadastro de produtos e categorias',
                        'Gestão de clientes e pedidos',
                        'Sistema de cupons de desconto',
                        'Cashback e programa de fidelidade',
                        'Relatórios e análises de vendas',
                        'Integração Mercado Pago (taxa de R$ ' . number_format($commission, 2, ',', '.') . ' por venda)',
                        'Suporte por email',
                    ],
                    'price' => 'R$ 99,90',
                    'price_label' => '/mês',
                    'trial_days' => $trialDays,
                ],
                'ia' => [
                    'name' => 'Plano WhatsApp',
                    'description' => 'Tudo do básico + integração completa com WhatsApp',
                    'featured' => true,
                    'features' => [
                        '✨ Todas as funcionalidades do Plano Básico',
                        'Integração WhatsApp para notificações',
                        'Envio automático de atualizações de pedidos',
                        'Campanhas de marketing via WhatsApp',
                        'Templates de mensagens personalizáveis',
                        'Agendamento de mensagens',
                        'Suporte a múltiplas instâncias WhatsApp',
                        'Suporte prioritário',
                    ],
                    'price' => 'R$ 149,90',
                    'price_label' => '/mês',
                    'trial_days' => $trialDays,
                ],
            ];
        }

        // Buscar configurações do master para personalização
        $systemName = \App\Models\MasterSetting::get('system_name', 'OLIKA');
        $systemLogoUrl = \App\Models\MasterSetting::get('system_logo_url', '');
        $termsOfUse = \App\Models\MasterSetting::get('terms_of_use', '');
        $privacyPolicy = \App\Models\MasterSetting::get('privacy_policy', '');
        
        return view('store-signup-v2', compact('plans', 'commission', 'trialDays', 'systemName', 'systemLogoUrl', 'termsOfUse', 'privacyPolicy'));
    }

    /**
     * Processa o cadastro de novo lojista
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255', // Remover unique - email pode repetir entre estabelecimentos
            'password' => 'required|string|min:6|confirmed',
            'plan' => 'required|string', // Aceitar qualquer slug do banco
            'whatsapp_phone' => 'required|string|max:20',
            'accept_terms' => 'required|accepted',
        ], [
            'company_name.required' => 'O nome da empresa é obrigatório.',
            'name.required' => 'O seu nome é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Digite um e-mail válido.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter pelo menos 6 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.',
            'plan.required' => 'O plano é obrigatório.',
            'whatsapp_phone.required' => 'O WhatsApp é obrigatório.',
            'accept_terms.required' => 'Você deve aceitar os termos de uso.',
            'accept_terms.accepted' => 'Você deve aceitar os termos de uso.',
        ]);

        // Verificar se o WhatsApp foi verificado
        $phone = $this->normalizePhone($validated['whatsapp_phone'] ?? null);
        $verifiedPhone = Session::get('whatsapp_verified_phone');
        
        if (!$phone || $phone !== $verifiedPhone) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['whatsapp_phone' => 'Por favor, verifique seu número de WhatsApp antes de continuar.']);
        }

        // Verificar se o número de WhatsApp já está cadastrado em outro estabelecimento
        // Buscar todos os telefones cadastrados e normalizar para comparar
        $existingPhones = Client::whereNotNull('whatsapp_phone')
            ->where('whatsapp_phone', '!=', '')
            ->pluck('whatsapp_phone')
            ->map(function($existingPhone) {
                return $this->normalizePhone($existingPhone);
            })
            ->filter()
            ->unique()
            ->toArray();
        
        if (in_array($phone, $existingPhones)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['whatsapp_phone' => 'Este número de WhatsApp já está cadastrado em outro estabelecimento. Use um número diferente.']);
        }

        // Validar se o plano existe no banco e está ativo
        $planSlug = $validated['plan'];
        $planModel = \App\Models\Plan::where('slug', $planSlug)->first();
        
        if (!$planModel) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['plan' => 'Plano selecionado não encontrado.']);
        }

        if (!$planModel->active) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['plan' => 'O plano selecionado não está disponível para novos cadastros.']);
        }

        // Mapear plano para o formato esperado pelo campo 'plan' do Client
        // Se o plano tem WhatsApp (has_whatsapp = 1) -> 'ia'
        // Caso contrário -> 'basic'
        // Isso permite que qualquer plano (mesmo "Básico" editado) seja reconhecido corretamente
        $clientPlan = $planModel->has_whatsapp ? 'ia' : 'basic';

        DB::beginTransaction();
        
        try {
            // Verificar se email já existe (ANTES de criar cliente)
            if (User::where('email', $validated['email'])->exists()) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['email' => 'Este e-mail já está cadastrado. Use outro e-mail ou faça login.']);
            }
            
            // Gerar slug único baseado no nome da empresa
            $baseSlug = Str::slug($validated['company_name']);
            $slug = $baseSlug;
            $counter = 1;
            
            while (Client::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            // ⚡ Obter configurações do painel master
            $trialDays = \App\Models\MasterSetting::getRegistrationTrialDays();
            $defaultCommission = \App\Models\MasterSetting::getRegistrationDefaultCommission();
            $commissionEnabled = \App\Models\MasterSetting::isRegistrationCommissionEnabled();
            $requireApproval = \App\Models\MasterSetting::isRegistrationApprovalRequired();
            
            $trialStartedAt = now();
            $trialEndsAt = $trialStartedAt->copy()->addDays($trialDays);

            // Criar cliente SaaS com período de teste
            $client = Client::create([
                'name' => $validated['company_name'],
                'slug' => $slug,
                'email' => $validated['email'], // Salvar email do estabelecimento
                'plan' => $clientPlan, // Usar o plano mapeado
                'whatsapp_phone' => $phone,
                'active' => !$requireApproval, // ⚡ Se exige aprovação, começa inativo
                'deploy_status' => 'pending',
                'is_trial' => true,
                'trial_started_at' => $trialStartedAt,
                'trial_ends_at' => $trialEndsAt,
                // 💳 COMISSÃO MERCADO PAGO (configurada no master)
                'mercadopago_commission_enabled' => $commissionEnabled,
                'mercadopago_commission_amount' => $defaultCommission,
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

            // Limpar sessão de verificação
            Session::forget('whatsapp_verified_phone');

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

    /**
     * Envia código de verificação via WhatsApp
     */
    public function sendWhatsAppVerification(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'min:10'],
        ]);

        $phone = $this->normalizePhone($data['phone'] ?? null);
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'Informe um telefone válido com DDD (ex: (11) 99999-9999).'
            ], 400);
        }

        // Throttle: 60 segundos entre requisições
        $throttleKey = 'whatsapp_verify_throttle:' . $phone;
        if (Cache::has($throttleKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Aguarde alguns segundos antes de solicitar um novo código.'
            ], 429);
        }

        $code = (string) random_int(100000, 999999);
        $expiresAt = Carbon::now()->addMinutes(5);
        
        $payload = [
            'hash' => hash('sha256', $code),
            'attempts' => 0,
            'expires_at' => $expiresAt->timestamp,
            'ip' => $ip,
            'user_agent_hash' => hash('sha256', $userAgent ?? ''),
            'created_at' => Carbon::now()->timestamp,
        ];

        Cache::put('whatsapp_verify_code:' . $phone, $payload, $expiresAt);
        Cache::put($throttleKey, true, Carbon::now()->addSeconds(60));

        try {
            $whatsApp = new WhatsAppService();
            if ($whatsApp->isEnabled()) {
                $phoneNormalized = preg_replace('/\D/', '', $phone);
                if (strlen($phoneNormalized) >= 10 && !str_starts_with($phoneNormalized, '55')) {
                    $phoneNormalized = '55' . $phoneNormalized;
                }
                
                $message = "Seu código de verificação para cadastro na Olika é {$code}. Ele expira em 5 minutos.";
                $whatsApp->sendText($phoneNormalized, $message);
                
                Log::info('Código de verificação WhatsApp enviado para cadastro', [
                    'phone' => $phone,
                    'phone_normalized' => $phoneNormalized,
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Código enviado com sucesso!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível enviar o código agora. Tente novamente em instantes.'
                ], 500);
            }
        } catch (\Throwable $e) {
            Log::error('Erro ao enviar código de verificação WhatsApp', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível enviar o código. Tente novamente em instantes.'
            ], 500);
        }
    }

    /**
     * Verifica código de WhatsApp
     */
    public function verifyWhatsAppCode(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $phone = $this->normalizePhone($data['phone'] ?? null);
        $code = $data['code'];

        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'Telefone inválido.'
            ], 400);
        }

        $cacheKey = 'whatsapp_verify_code:' . $phone;
        $payload = Cache::get($cacheKey);

        if (!$payload) {
            return response()->json([
                'success' => false,
                'message' => 'Código expirado ou inválido. Solicite um novo código.'
            ], 400);
        }

        if (Carbon::now()->timestamp > $payload['expires_at']) {
            Cache::forget($cacheKey);
            return response()->json([
                'success' => false,
                'message' => 'Código expirado. Solicite um novo código.'
            ], 400);
        }

        $codeHash = hash('sha256', $code);
        if (!hash_equals($payload['hash'], $codeHash)) {
            $payload['attempts']++;
            Cache::put($cacheKey, $payload, Carbon::createFromTimestamp($payload['expires_at']));
            
            if ($payload['attempts'] >= 3) {
                Cache::forget($cacheKey);
                return response()->json([
                    'success' => false,
                    'message' => 'Muitas tentativas incorretas. Solicite um novo código.'
                ], 400);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Código inválido. Tente novamente.'
            ], 400);
        }

        // Código válido - marcar telefone como verificado na sessão
        Session::put('whatsapp_verified_phone', $phone);
        Cache::forget($cacheKey);

        return response()->json([
            'success' => true,
            'message' => 'Telefone verificado com sucesso!'
        ]);
    }

    /**
     * Normaliza e valida telefone brasileiro
     */
    private function normalizePhone(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value);
        
        if (strlen($digits) < 10 || strlen($digits) > 11) {
            return null;
        }
        
        if (strlen($digits) === 11 && $digits[0] === '0') {
            $digits = substr($digits, 1);
        }
        
        $ddd = substr($digits, 0, 2);
        if (!preg_match('/^[1-9][1-9]$/', $ddd)) {
            return null;
        }
        
        return $digits;
    }
}

