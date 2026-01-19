<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - OLIKA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }

        .plan-card {
            transition: all 0.3s ease;
        }

        .plan-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }

        .plan-card.selected {
            border-color: #22c55e;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);
        }

        .step-indicator {
            position: relative;
        }

        .step-indicator::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 100%;
            width: 100%;
            height: 2px;
            background: #e5e7eb;
            z-index: -1;
        }

        .step-indicator.active .step-number {
            background: #22c55e;
            color: white;
        }

        .step-indicator.completed .step-number {
            background: #22c55e;
            color: white;
        }

        .step-number {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
            color: #9ca3af;
            font-weight: 600;
            transition: all 0.3s;
        }

        #step-2-content {
            display: none;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col items-center justify-center px-4 py-12">
        <!-- Logo/Icon -->
        <div class="mb-8">
            <div class="w-16 h-16 bg-gradient-to-br from-orange-400 to-orange-600 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-box text-white text-2xl"></i>
            </div>
        </div>

        <!-- Title -->
        <h1 class="text-4xl md:text-5xl font-bold text-center mb-3">
            Comece a vender com a <span class="text-orange-500">OLIKA</span>
        </h1>
        <p class="text-gray-600 text-center mb-2 text-lg">
            Escolha o plano ideal para o seu negócio e comece hoje mesmo
        </p>
        <div class="bg-green-50 border border-green-200 rounded-lg px-4 py-2 mb-8">
            <p class="text-green-700 font-semibold text-sm">
                <i class="fas fa-gift mr-2"></i>{{ $trialDays }} dias de teste grátis em todos os planos
            </p>
        </div>

        <!-- Steps Indicator -->
        <div class="w-full max-w-4xl mb-12">
            <div class="flex items-center justify-center gap-4 relative">
                <!-- Step 1 -->
                <div class="step-indicator active flex flex-col items-center" id="step-1-indicator">
                    <div class="step-number">
                        <span class="step-1-number">1</span>
                        <i class="fas fa-check step-1-check hidden"></i>
                    </div>
                    <div class="mt-2 text-center">
                        <p class="font-semibold text-sm">Escolha o Plano</p>
                        <p class="text-xs text-gray-500">Selecione o melhor para você</p>
                    </div>
                </div>

                <!-- Connector -->
                <div class="h-0.5 w-32 bg-gray-300" id="connector"></div>

                <!-- Step 2 -->
                <div class="step-indicator flex flex-col items-center" id="step-2-indicator">
                    <div class="step-number">2</div>
                    <div class="mt-2 text-center">
                        <p class="font-semibold text-sm">Seus Dados</p>
                        <p class="text-xs text-gray-500">Complete seu cadastro</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error Messages -->
        @if(session('error'))
            <div class="w-full max-w-4xl mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="w-full max-w-4xl mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form -->
        <form method="POST" action="{{ route('store-signup.store') }}" class="w-full max-w-4xl" id="signup-form">
            @csrf

            <!-- Step 1: Choose Plan -->
            <div id="step-1-content">
                <div class="grid md:grid-cols-3 gap-6">
                    @foreach($plans as $slug => $plan)
                        <div class="plan-card relative bg-white rounded-2xl border-2 border-gray-200 p-6 cursor-pointer hover:shadow-xl transition-all"
                             onclick="selectPlan('{{ $slug }}')"
                             data-plan="{{ $slug }}">
                            
                            @if(!empty($plan['featured']))
                                <div class="absolute -top-3 left-1/2 transform -translate-x-1/2">
                                    <span class="bg-green-500 text-white px-4 py-1 rounded-full text-sm font-semibold shadow-md flex items-center gap-1">
                                        <i class="fas fa-star text-xs"></i> Mais Popular
                                    </span>
                                </div>
                            @endif

                            <!-- Plan Header -->
                            <div class="mb-4">
                                <h3 class="text-2xl font-bold text-gray-900 mb-1">{{ $plan['name'] }}</h3>
                                <p class="text-gray-600 text-sm">{{ $plan['description'] }}</p>
                            </div>

                            <!-- Price -->
                            <div class="mb-6">
                                <span class="text-4xl font-bold text-gray-900">{{ $plan['price'] }}</span>
                                <span class="text-gray-500 text-lg">{{ $plan['price_label'] }}</span>
                            </div>

                            <!-- Features -->
                            <ul class="space-y-3 mb-6">
                                @foreach($plan['features'] as $feature)
                                    <li class="flex items-start gap-2 text-sm">
                                        @if(str_starts_with($feature, '✨'))
                                            <i class="fas fa-crown text-orange-500 mt-0.5 flex-shrink-0"></i>
                                            <span class="font-semibold text-gray-900">{{ str_replace('✨ ', '', $feature) }}</span>
                                        @else
                                            <i class="fas fa-check-circle text-green-500 mt-0.5 flex-shrink-0"></i>
                                            <span class="text-gray-700">{{ $feature }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>

                            <!-- Select Button -->
                            <button type="button" 
                                    class="w-full py-3 rounded-lg font-semibold transition-all select-button"
                                    style="background: #f3f4f6; color: #6b7280;"
                                    onclick="selectPlan('{{ $slug }}')">
                                Selecionar Plano
                            </button>
                        </div>
                    @endforeach
                </div>

                <input type="hidden" name="plan" id="selected-plan" value="{{ old('plan') }}" required>
            </div>

            <!-- Step 2: Complete Registration -->
            <div id="step-2-content" class="bg-white rounded-2xl border-2 border-gray-200 p-8 shadow-lg">
                <h2 class="text-2xl font-bold mb-2 flex items-center gap-2">
                    <i class="fas fa-building text-orange-500"></i>
                    Complete seu cadastro
                </h2>
                <p class="text-gray-600 mb-6">Preencha os dados abaixo para criar sua conta</p>

                <!-- Business Data -->
                <div class="mb-6 bg-orange-50 rounded-lg p-4 border border-orange-200">
                    <h3 class="font-semibold text-orange-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-store"></i> Dados da Empresa
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Empresa *</label>
                            <input type="text" name="company_name" value="{{ old('company_name') }}"
                                   placeholder="Ex: Padaria do João"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                   required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp (opcional)</label>
                            <input type="text" name="whatsapp_phone" value="{{ old('whatsapp_phone') }}"
                                   placeholder="(11) 99999-9999"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- User Data -->
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-user"></i> Seus Dados
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Seu Nome *</label>
                            <input type="text" name="name" value="{{ old('name') }}"
                                   placeholder="João Silva"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                   required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">E-mail *</label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                   placeholder="joao@email.com"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                   required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Senha *</label>
                            <input type="password" name="password"
                                   placeholder="Mínimo 6 caracteres"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                   required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Senha *</label>
                            <input type="password" name="password_confirmation"
                                   placeholder="Digite a senha novamente"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                   required>
                        </div>
                    </div>
                </div>

                <!-- Terms -->
                <div class="mb-6">
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input type="checkbox" name="accept_terms" value="1" class="mt-1" required>
                        <span class="text-sm text-gray-700">
                            Aceito os <a href="#" class="text-orange-500 hover:underline">termos de uso</a> e 
                            <a href="#" class="text-orange-500 hover:underline">política de privacidade</a>
                        </span>
                    </label>
                </div>

                <!-- Actions -->
                <div class="flex gap-4">
                    <button type="button" onclick="backToPlans()"
                            class="px-6 py-3 border-2 border-gray-300 rounded-lg font-semibold text-gray-700 hover:bg-gray-50 transition">
                        <i class="fas fa-arrow-left mr-2"></i>Voltar
                    </button>
                    <button type="submit"
                            class="flex-1 bg-gradient-to-r from-orange-500 to-orange-600 text-white py-3 rounded-lg font-semibold hover:from-orange-600 hover:to-orange-700 transition shadow-lg">
                        <i class="fas fa-rocket mr-2"></i>Criar Conta Grátis
                    </button>
                </div>
            </div>
        </form>

        <!-- Footer -->
        <div class="mt-12 text-center text-sm text-gray-500">
            <p>Já tem uma conta? <a href="{{ route('login') }}" class="text-orange-500 hover:underline font-semibold">Faça login</a></p>
        </div>
    </div>

    <script>
        let selectedPlanSlug = '{{ old("plan") }}';

        function selectPlan(slug) {
            selectedPlanSlug = slug;
            document.getElementById('selected-plan').value = slug;

            // Remove selected class from all cards
            document.querySelectorAll('.plan-card').forEach(card => {
                card.classList.remove('selected');
                const btn = card.querySelector('.select-button');
                btn.style.background = '#f3f4f6';
                btn.style.color = '#6b7280';
                btn.textContent = 'Selecionar Plano';
            });

            // Add selected class to clicked card
            const selectedCard = document.querySelector(`[data-plan="${slug}"]`);
            selectedCard.classList.add('selected');
            const btn = selectedCard.querySelector('.select-button');
            btn.style.background = 'linear-gradient(to right, #f97316, #ea580c)';
            btn.style.color = 'white';
            btn.innerHTML = '<i class="fas fa-check mr-2"></i>Plano Selecionado';

            // Scroll to next step button
            setTimeout(() => {
                goToStep2();
            }, 500);
        }

        function goToStep2() {
            document.getElementById('step-1-content').style.display = 'none';
            document.getElementById('step-2-content').style.display = 'block';
            
            // Update indicators
            document.getElementById('step-1-indicator').classList.add('completed');
            document.getElementById('step-1-indicator').classList.remove('active');
            document.querySelector('.step-1-number').classList.add('hidden');
            document.querySelector('.step-1-check').classList.remove('hidden');
            
            document.getElementById('step-2-indicator').classList.add('active');
            document.getElementById('connector').classList.remove('bg-gray-300');
            document.getElementById('connector').classList.add('bg-green-500');

            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function backToPlans() {
            document.getElementById('step-1-content').style.display = 'block';
            document.getElementById('step-2-content').style.display = 'none';
            
            // Update indicators
            document.getElementById('step-1-indicator').classList.remove('completed');
            document.getElementById('step-1-indicator').classList.add('active');
            document.querySelector('.step-1-number').classList.remove('hidden');
            document.querySelector('.step-1-check').classList.add('hidden');
            
            document.getElementById('step-2-indicator').classList.remove('active');
            document.getElementById('connector').classList.add('bg-gray-300');
            document.getElementById('connector').classList.remove('bg-green-500');

            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Pre-select plan if coming back from validation error
        if (selectedPlanSlug) {
            selectPlan(selectedPlanSlug);
        }
    </script>
</body>
</html>
