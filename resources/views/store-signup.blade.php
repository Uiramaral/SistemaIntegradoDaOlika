<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Lojista - OLIKA</title>
    <meta name="description"
        content="Crie sua conta grátis e comece a vender com o sistema de delivery mais completo do mercado.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'display': ['Exo 2', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        * {
            font-family: 'Exo 2', sans-serif;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        }

        .gradient-text {
            background: linear-gradient(135deg, #f97316 0%, #ef4444 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .input-modern {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .input-modern:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: #f97316;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.2);
        }

        .input-modern::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .btn-primary {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(249, 115, 22, 0.4);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .plan-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .plan-card:hover {
            border-color: rgba(249, 115, 22, 0.5);
            transform: translateY(-4px);
        }

        .plan-card.selected {
            border-color: #f97316;
            background: rgba(249, 115, 22, 0.1);
            box-shadow: 0 0 30px rgba(249, 115, 22, 0.2);
        }

        .plan-popular {
            position: relative;
        }

        .plan-popular::before {
            content: '⭐ MAIS POPULAR';
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #f97316 0%, #ef4444 100%);
            color: white;
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .floating-shapes {
            position: fixed;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
            z-index: 0;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.4;
        }

        .shape-1 {
            width: 500px;
            height: 500px;
            background: rgba(249, 115, 22, 0.3);
            top: -150px;
            right: -150px;
        }

        .shape-2 {
            width: 400px;
            height: 400px;
            background: rgba(239, 68, 68, 0.3);
            bottom: -100px;
            left: -100px;
        }

        .step-indicator {
            transition: all 0.3s ease;
        }

        .step-indicator.active {
            background: linear-gradient(135deg, #f97316 0%, #ef4444 100%);
        }

        .step-indicator.completed {
            background: #22c55e;
        }

        .checkbox-custom:checked {
            background: linear-gradient(135deg, #f97316 0%, #ef4444 100%);
            border-color: #f97316;
        }
    </style>
</head>

<body class="gradient-bg min-h-screen relative">
    <!-- Floating Background Shapes -->
    <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
    </div>

    <div class="relative z-10 min-h-screen py-8 px-4">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-10">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-3 mb-6">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-500 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-utensils text-white text-xl"></i>
                    </div>
                    <span class="text-2xl font-bold text-white">OLIKA</span>
                </a>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white mb-4">
                    Comece a <span class="gradient-text">vender</span> hoje mesmo
                </h1>
                <p class="text-gray-400 text-lg max-w-2xl mx-auto">
                    Escolha o plano ideal para o seu negócio e comece seu teste grátis de 14 dias
                </p>

                <!-- Trial Badge -->
                <div
                    class="inline-flex items-center gap-2 bg-green-500/20 text-green-400 px-4 py-2 rounded-full text-sm font-medium mt-4">
                    <i class="fas fa-gift"></i>
                    14 dias de teste grátis • Sem cartão de crédito
                </div>
            </div>

            @if(session('error'))
                <div
                    class="max-w-2xl mx-auto mb-6 p-4 bg-red-500/20 border border-red-500/30 text-red-300 rounded-xl text-sm">
                    <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div
                    class="max-w-2xl mx-auto mb-6 p-4 bg-red-500/20 border border-red-500/30 text-red-300 rounded-xl text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Plans Grid -->
            <div id="plans-section" class="grid md:grid-cols-2 gap-6 max-w-4xl mx-auto mb-10">
                @foreach($plans as $planKey => $plan)
                    <div class="plan-card rounded-2xl p-6 cursor-pointer {{ isset($plan['featured']) && $plan['featured'] ? 'plan-popular' : '' }}"
                        onclick="selectPlan('{{ $planKey }}')" data-plan="{{ $planKey }}">

                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-xl font-bold text-white">{{ $plan['name'] }}</h3>
                                <p class="text-gray-400 text-sm mt-1">{{ $plan['description'] }}</p>
                            </div>
                            <div
                                class="w-6 h-6 rounded-full border-2 border-gray-600 flex items-center justify-center plan-radio">
                                <div class="w-3 h-3 rounded-full bg-orange-500 opacity-0 transition-opacity"></div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <span class="text-3xl font-bold text-white">{{ $plan['price'] }}</span>
                            <span class="text-gray-400">{{ $plan['price_label'] }}</span>
                        </div>

                        <div class="bg-green-500/10 border border-green-500/20 rounded-xl p-3 mb-6">
                            <p class="text-sm text-green-400 font-medium">
                                <i class="fas fa-gift mr-2"></i>
                                {{ $plan['trial_days'] }} dias de teste grátis
                            </p>
                        </div>

                        <ul class="space-y-3">
                            @foreach($plan['features'] as $feature)
                                <li class="flex items-start gap-3">
                                    @if(str_starts_with($feature, '✨'))
                                        <i class="fas fa-crown text-orange-400 mt-0.5 flex-shrink-0"></i>
                                        <span class="text-white font-medium">{{ str_replace('✨ ', '', $feature) }}</span>
                                    @else
                                        <i class="fas fa-check text-green-400 mt-0.5 flex-shrink-0"></i>
                                        <span class="text-gray-300">{{ $feature }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            <!-- Registration Form -->
            <div id="signup-form" class="hidden max-w-2xl mx-auto">
                <div class="glass-card rounded-3xl p-8 sm:p-10">
                    <!-- Step Indicators -->
                    <div class="flex items-center justify-center gap-3 mb-8">
                        <div class="step-indicator w-3 h-3 rounded-full bg-orange-500"></div>
                        <div class="w-12 h-0.5 bg-gray-700"></div>
                        <div class="step-indicator w-3 h-3 rounded-full bg-gray-700" id="step-2-indicator"></div>
                    </div>

                    <h2 class="text-2xl font-bold text-white text-center mb-2">Complete seu cadastro</h2>
                    <p class="text-gray-400 text-center mb-8">Preencha os dados abaixo para criar sua conta</p>

                    <form action="{{ route('store-signup.store') }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="plan" id="selected-plan" required>

                        <!-- Company Info -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                                <i class="fas fa-store text-orange-400"></i>
                                Dados da Empresa
                            </h3>
                            <div>
                                <label for="company_name" class="block text-sm font-medium text-gray-300 mb-2">
                                    Nome da Empresa <span class="text-red-400">*</span>
                                </label>
                                <input type="text" id="company_name" name="company_name" required
                                    value="{{ old('company_name') }}"
                                    class="input-modern w-full px-4 h-14 rounded-xl text-white focus:outline-none"
                                    placeholder="Ex: Padaria do João">
                            </div>
                        </div>

                        <div class="border-t border-gray-700 pt-6 space-y-4">
                            <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                                <i class="fas fa-user text-orange-400"></i>
                                Seus Dados
                            </h3>

                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-300 mb-2">
                                    Seu Nome <span class="text-red-400">*</span>
                                </label>
                                <input type="text" id="name" name="name" required value="{{ old('name') }}"
                                    class="input-modern w-full px-4 h-14 rounded-xl text-white focus:outline-none"
                                    placeholder="Ex: João Silva">
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
                                    E-mail <span class="text-red-400">*</span>
                                </label>
                                <input type="email" id="email" name="email" required value="{{ old('email') }}"
                                    class="input-modern w-full px-4 h-14 rounded-xl text-white focus:outline-none"
                                    placeholder="seu@email.com">
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                                        Senha <span class="text-red-400">*</span>
                                    </label>
                                    <input type="password" id="password" name="password" required minlength="6"
                                        class="input-modern w-full px-4 h-14 rounded-xl text-white focus:outline-none"
                                        placeholder="Mínimo 6 caracteres">
                                </div>

                                <div>
                                    <label for="password_confirmation"
                                        class="block text-sm font-medium text-gray-300 mb-2">
                                        Confirmar Senha <span class="text-red-400">*</span>
                                    </label>
                                    <input type="password" id="password_confirmation" name="password_confirmation"
                                        required minlength="6"
                                        class="input-modern w-full px-4 h-14 rounded-xl text-white focus:outline-none"
                                        placeholder="Digite novamente">
                                </div>
                            </div>

                            <div>
                                <label for="whatsapp_phone" class="block text-sm font-medium text-gray-300 mb-2">
                                    WhatsApp <span class="text-gray-500">(Opcional)</span>
                                </label>
                                <input type="text" id="whatsapp_phone" name="whatsapp_phone"
                                    value="{{ old('whatsapp_phone') }}"
                                    class="input-modern w-full px-4 h-14 rounded-xl text-white focus:outline-none"
                                    placeholder="(11) 99999-9999">
                                <p class="text-xs text-gray-500 mt-2">Para receber notificações importantes</p>
                            </div>
                        </div>

                        <!-- Terms -->
                        <div class="border-t border-gray-700 pt-6">
                            <label class="flex items-start gap-3 cursor-pointer group">
                                <input type="checkbox" name="accept_terms" value="1" required
                                    class="checkbox-custom w-5 h-5 mt-0.5 rounded border-gray-600 bg-transparent focus:ring-orange-500 focus:ring-offset-0">
                                <span class="text-sm text-gray-400 group-hover:text-gray-300 transition-colors">
                                    Eu aceito os <a href="#" class="text-orange-400 hover:underline">termos de uso</a> e
                                    <a href="#" class="text-orange-400 hover:underline">política de privacidade</a>
                                    <span class="text-red-400">*</span>
                                </span>
                            </label>
                        </div>

                        <!-- Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4 pt-4">
                            <button type="button" onclick="cancelSignup()"
                                class="btn-secondary flex-1 h-14 rounded-xl text-white font-semibold flex items-center justify-center gap-2">
                                <i class="fas fa-arrow-left"></i>
                                Voltar
                            </button>
                            <button type="submit"
                                class="btn-primary flex-1 h-14 rounded-xl text-white font-semibold flex items-center justify-center gap-2">
                                <i class="fas fa-rocket"></i>
                                Criar Conta Grátis
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Already have account -->
                <p class="text-center text-gray-400 mt-6">
                    Já tem uma conta?
                    <a href="{{ route('login') }}"
                        class="text-orange-400 font-semibold hover:text-orange-300 transition-colors">
                        Fazer login
                    </a>
                </p>
            </div>

            <!-- Back to Login when plans visible -->
            <div id="login-link" class="text-center mt-8">
                <p class="text-gray-400">
                    Já tem uma conta?
                    <a href="{{ route('login') }}"
                        class="text-orange-400 font-semibold hover:text-orange-300 transition-colors">
                        Fazer login
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script>
        let selectedPlanKey = null;

        function selectPlan(planKey) {
            selectedPlanKey = planKey;
            document.getElementById('selected-plan').value = planKey;

            // Update visual selection
            document.querySelectorAll('.plan-card').forEach(card => {
                const isSelected = card.dataset.plan === planKey;
                card.classList.toggle('selected', isSelected);
                const radio = card.querySelector('.plan-radio div');
                if (radio) {
                    radio.style.opacity = isSelected ? '1' : '0';
                }
            });

            // Show form
            document.getElementById('signup-form').classList.remove('hidden');
            document.getElementById('login-link').classList.add('hidden');

            // Update step indicator
            document.getElementById('step-2-indicator').classList.add('active');

            // Scroll to form
            setTimeout(() => {
                document.getElementById('signup-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        }

        function cancelSignup() {
            document.getElementById('signup-form').classList.add('hidden');
            document.getElementById('login-link').classList.remove('hidden');
            document.getElementById('selected-plan').value = '';
            document.getElementById('step-2-indicator').classList.remove('active');

            // Clear selection
            document.querySelectorAll('.plan-card').forEach(card => {
                card.classList.remove('selected');
                const radio = card.querySelector('.plan-radio div');
                if (radio) {
                    radio.style.opacity = '0';
                }
            });

            selectedPlanKey = null;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Restore form if validation errors
        @if(old('plan'))
            document.addEventListener('DOMContentLoaded', function () {
                selectPlan('{{ old('plan') }}');
            });
        @endif
    </script>
</body>

</html>