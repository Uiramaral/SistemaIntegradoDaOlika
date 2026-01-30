<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $systemName = $systemName ?? \App\Models\MasterSetting::get('system_name', 'OLIKA');
        $systemFaviconUrl = \App\Models\MasterSetting::get('system_favicon_url', '');
        $usePublicFavicons = file_exists(public_path('favicon/favicon.ico'));
    @endphp
    <title>Cadastro - {{ $systemName }}</title>
    @if($usePublicFavicons)
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon/favicon.ico') }}?v={{ time() }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon/genfavicon-32.png') }}?v={{ time() }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon/genfavicon-16.png') }}?v={{ time() }}">
    @elseif($systemFaviconUrl && $systemFaviconUrl !== '')
        <link rel="icon" type="image/png" href="{{ $systemFaviconUrl }}?v={{ time() }}">
    @endif
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
        * { font-family: 'Exo 2', sans-serif; }
        
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
        
        .step-indicator.active .step-number {
            background: linear-gradient(135deg, #f97316 0%, #ef4444 100%);
        }
        
        .step-indicator.completed .step-number {
            background: #22c55e;
        }
        
        .step-number {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        #step-2-content {
            display: none;
        }
    </style>
</head>

<body class="gradient-bg min-h-screen relative">
    <!-- Floating Background Shapes -->
    <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
    </div>

    @php
        $systemName = $systemName ?? \App\Models\MasterSetting::get('system_name', 'OLIKA');
        $systemLogoUrl = $systemLogoUrl ?? \App\Models\MasterSetting::get('system_logo_url', '');
        
        if ($systemLogoUrl && $systemLogoUrl !== '') {
            $systemLogoUrl .= (strpos($systemLogoUrl, '?') !== false ? '&' : '?') . 'v=' . time();
        }
    @endphp

    <div class="relative z-10 min-h-screen py-8 px-4">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-10">
                <a href="{{ route('landing') }}" class="inline-flex items-center gap-3 mb-6">
                    @if($systemLogoUrl && $systemLogoUrl !== '')
                        <img src="{{ $systemLogoUrl }}" alt="{{ $systemName }}" class="w-12 h-12 object-contain rounded-xl">
                    @else
                        <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-500 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-utensils text-white text-xl"></i>
                        </div>
                    @endif
                    <span class="text-2xl font-bold text-white">{{ $systemName }}</span>
                </a>
                
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white mb-4">
                    Comece a <span class="gradient-text">vender</span> hoje mesmo
                </h1>
                <p class="text-gray-400 text-lg max-w-2xl mx-auto">
                    Escolha o plano ideal para o seu negócio e comece seu teste grátis
                </p>
                
                <!-- Trial Badge -->
                <div class="inline-flex items-center gap-2 bg-green-500/20 text-green-400 px-4 py-2 rounded-full text-sm font-medium mt-4">
                    <i class="fas fa-gift"></i>
                    {{ $trialDays }} dias de teste grátis • Sem cartão de crédito
                </div>
            </div>

            <!-- Steps Indicator -->
            <div class="w-full max-w-md mx-auto mb-10">
                <div class="flex items-center justify-center gap-4">
                    <div class="step-indicator active flex flex-col items-center" id="step-1-indicator">
                        <div class="step-number">
                            <span class="step-1-number">1</span>
                            <i class="fas fa-check step-1-check hidden"></i>
                        </div>
                        <p class="text-white text-sm mt-2 font-medium">Escolha o Plano</p>
                    </div>

                    <div class="h-0.5 w-16 bg-gray-700" id="connector"></div>

                    <div class="step-indicator flex flex-col items-center" id="step-2-indicator">
                        <div class="step-number">2</div>
                        <p class="text-white text-sm mt-2 font-medium">Seus Dados</p>
                    </div>
                </div>
            </div>

            <!-- Error Messages -->
            @if(session('error'))
                <div class="max-w-4xl mx-auto mb-6 p-4 bg-red-500/20 border border-red-500/30 text-red-300 rounded-xl text-sm">
                    <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="max-w-4xl mx-auto mb-6 p-4 bg-red-500/20 border border-red-500/30 text-red-300 rounded-xl text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form -->
            <form method="POST" action="{{ route('store-signup.store') }}" class="w-full" id="signup-form">
                @csrf

                <!-- Step 1: Choose Plan -->
                <div id="step-1-content">
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-5xl mx-auto">
                        @foreach($plans as $slug => $plan)
                            <div class="plan-card rounded-2xl p-6 cursor-pointer {{ !empty($plan['featured']) ? 'plan-popular' : '' }}"
                                 onclick="selectPlan('{{ $slug }}')"
                                 data-plan="{{ $slug }}">
                                
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <h3 class="text-xl font-bold text-white">{{ $plan['name'] }}</h3>
                                        <p class="text-gray-400 text-sm mt-1">{{ $plan['description'] }}</p>
                                    </div>
                                    <div class="w-6 h-6 rounded-full border-2 border-gray-600 flex items-center justify-center plan-radio">
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
                                
                                <button type="button" 
                                        class="w-full mt-6 py-3 rounded-xl font-semibold transition-all select-button bg-white/10 text-white border border-white/20 hover:bg-white/20"
                                        onclick="selectPlan('{{ $slug }}')">
                                    Selecionar Plano
                                </button>
                            </div>
                        @endforeach
                    </div>

                    <input type="hidden" name="plan" id="selected-plan" value="{{ old('plan') }}" required>
                    
                    <!-- Login Link -->
                    <p class="text-center text-gray-400 mt-8">
                        Já tem uma conta? 
                        <a href="{{ route('dashboard.login') }}" class="text-orange-400 font-semibold hover:text-orange-300 transition-colors">
                            Fazer login
                        </a>
                    </p>
                </div>

                <!-- Step 2: Complete Registration -->
                <div id="step-2-content" class="max-w-2xl mx-auto">
                    <div class="glass-card rounded-3xl p-8 sm:p-10">
                        <h2 class="text-2xl font-bold text-white mb-2">Complete seu cadastro</h2>
                        <p class="text-gray-400 mb-8">Preencha os dados abaixo para criar sua conta</p>

                        <!-- Company Info -->
                        <div class="space-y-4 mb-8">
                            <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                                <i class="fas fa-store text-orange-400"></i>
                                Dados da Empresa
                            </h3>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    Nome da Empresa <span class="text-red-400">*</span>
                                </label>
                                <input type="text" name="company_name" value="{{ old('company_name') }}"
                                       placeholder="Ex: Padaria do João"
                                       class="input-modern w-full px-4 h-14 rounded-xl text-white focus:outline-none"
                                       required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    WhatsApp <span class="text-red-400">*</span>
                                </label>
                                <div class="flex gap-2">
                                    <input type="text" name="whatsapp_phone" id="whatsapp_phone" value="{{ old('whatsapp_phone') }}"
                                           placeholder="(11) 99999-9999"
                                           class="input-modern flex-1 px-4 h-14 rounded-xl text-white focus:outline-none"
                                           required>
                                    <button type="button" id="verify-whatsapp-btn" 
                                            class="btn-primary px-6 h-14 rounded-xl font-semibold whitespace-nowrap">
                                        Verificar
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">Você receberá um código via WhatsApp para validar</p>
                                <input type="hidden" id="whatsapp_verified" name="whatsapp_verified" value="0">
                            </div>
                        </div>

                        <!-- User Data -->
                        <div class="space-y-4 mb-8 border-t border-gray-700 pt-8">
                            <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                                <i class="fas fa-user text-orange-400"></i>
                                Seus Dados
                            </h3>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    Seu Nome <span class="text-red-400">*</span>
                                </label>
                                <input type="text" name="name" value="{{ old('name') }}"
                                       placeholder="Ex: João Silva"
                                       class="input-modern w-full px-4 h-14 rounded-xl text-white focus:outline-none"
                                       required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    E-mail <span class="text-red-400">*</span>
                                </label>
                                <input type="email" name="email" value="{{ old('email') }}"
                                       placeholder="seu@email.com"
                                       class="input-modern w-full px-4 h-14 rounded-xl text-white focus:outline-none"
                                       required>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">
                                        Senha <span class="text-red-400">*</span>
                                    </label>
                                    <input type="password" name="password"
                                           placeholder="Mínimo 6 caracteres"
                                           class="input-modern w-full px-4 h-14 rounded-xl text-white focus:outline-none"
                                           required>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">
                                        Confirmar Senha <span class="text-red-400">*</span>
                                    </label>
                                    <input type="password" name="password_confirmation"
                                           placeholder="Digite novamente"
                                           class="input-modern w-full px-4 h-14 rounded-xl text-white focus:outline-none"
                                           required>
                                </div>
                            </div>
                        </div>

                        <!-- Terms -->
                        <div class="border-t border-gray-700 pt-6 mb-6">
                            <label class="flex items-start gap-3 cursor-pointer group">
                                <input type="checkbox" name="accept_terms" value="1" required
                                       class="w-5 h-5 mt-0.5 rounded border-gray-600 bg-transparent text-orange-500 focus:ring-orange-500 focus:ring-offset-0">
                                <span class="text-sm text-gray-400 group-hover:text-gray-300 transition-colors">
                                    Eu aceito os 
                                    @if(!empty($termsOfUse))
                                        <a href="#" onclick="showTermsModal(); return false;" class="text-orange-400 hover:underline">termos de uso</a>
                                    @else
                                        <span class="text-orange-400">termos de uso</span>
                                    @endif
                                    e 
                                    @if(!empty($privacyPolicy))
                                        <a href="#" onclick="showPrivacyModal(); return false;" class="text-orange-400 hover:underline">política de privacidade</a>
                                    @else
                                        <span class="text-orange-400">política de privacidade</span>
                                    @endif
                                    <span class="text-red-400">*</span>
                                </span>
                            </label>
                        </div>

                        <!-- Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4">
                            <button type="button" onclick="backToPlans()"
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
                    </div>
                    
                    <!-- Login Link -->
                    <p class="text-center text-gray-400 mt-6">
                        Já tem uma conta? 
                        <a href="{{ route('dashboard.login') }}" class="text-orange-400 font-semibold hover:text-orange-300 transition-colors">
                            Fazer login
                        </a>
                    </p>
                </div>
            </form>
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
                const radio = card.querySelector('.plan-radio div');
                if (radio) {
                    radio.style.opacity = '0';
                }
                const btn = card.querySelector('.select-button');
                btn.className = 'w-full mt-6 py-3 rounded-xl font-semibold transition-all select-button bg-white/10 text-white border border-white/20 hover:bg-white/20';
                btn.textContent = 'Selecionar Plano';
            });

            // Add selected class to clicked card
            const selectedCard = document.querySelector(`[data-plan="${slug}"]`);
            selectedCard.classList.add('selected');
            const radio = selectedCard.querySelector('.plan-radio div');
            if (radio) {
                radio.style.opacity = '1';
            }
            const btn = selectedCard.querySelector('.select-button');
            btn.className = 'w-full mt-6 py-3 rounded-xl font-semibold transition-all select-button btn-primary';
            btn.innerHTML = '<i class="fas fa-check mr-2"></i>Plano Selecionado';

            // Go to step 2
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
            document.getElementById('connector').classList.remove('bg-gray-700');
            document.getElementById('connector').classList.add('bg-green-500');

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
            document.getElementById('connector').classList.add('bg-gray-700');
            document.getElementById('connector').classList.remove('bg-green-500');

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Pre-select plan if coming back from validation error
        if (selectedPlanSlug) {
            selectPlan(selectedPlanSlug);
        }

        // WhatsApp Verification
        document.getElementById('verify-whatsapp-btn')?.addEventListener('click', function() {
            const phoneInput = document.getElementById('whatsapp_phone');
            const phone = phoneInput.value.trim();
            
            if (!phone) {
                alert('Por favor, digite seu número de WhatsApp');
                return;
            }

            fetch('{{ route("store-signup.whatsapp.send-code") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ phone: phone })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showVerificationModal(phone);
                } else {
                    alert(data.message || 'Erro ao enviar código');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Erro ao enviar código. Tente novamente.');
            });
        });

        function showVerificationModal(phone) {
            const modal = document.createElement('div');
            modal.id = 'whatsapp-verification-modal';
            modal.className = 'fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50';
            modal.innerHTML = `
                <div class="glass-card rounded-2xl p-8 max-w-md w-full mx-4">
                    <h3 class="text-2xl font-bold text-white mb-4">Verificar WhatsApp</h3>
                    <p class="text-gray-400 mb-6">Enviamos um código de 6 dígitos para <strong class="text-white">${phone}</strong>. Digite o código abaixo:</p>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Código de Verificação</label>
                            <input type="text" id="verification-code" maxlength="6" pattern="\\d{6}" 
                                   placeholder="000000"
                                   class="input-modern w-full px-4 py-4 rounded-xl text-white text-center text-2xl tracking-widest focus:outline-none">
                        </div>
                        <div class="flex gap-3">
                            <button type="button" onclick="closeVerificationModal()" 
                                    class="btn-secondary flex-1 py-3 rounded-xl font-semibold text-white">
                                Cancelar
                            </button>
                            <button type="button" onclick="verifyCode('${phone}')" 
                                    class="btn-primary flex-1 py-3 rounded-xl font-semibold text-white">
                                Verificar
                            </button>
                        </div>
                        <button type="button" onclick="resendCode('${phone}')" 
                                class="w-full text-sm text-orange-400 hover:underline">
                            Reenviar código
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            document.getElementById('verification-code').focus();
        }

        function closeVerificationModal() {
            const modal = document.getElementById('whatsapp-verification-modal');
            if (modal) modal.remove();
        }

        function verifyCode(phone) {
            const code = document.getElementById('verification-code').value.trim();
            
            if (code.length !== 6) {
                alert('Digite o código completo de 6 dígitos');
                return;
            }

            fetch('{{ route("store-signup.whatsapp.verify-code") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ phone: phone, code: code })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('whatsapp_verified').value = '1';
                    const btn = document.getElementById('verify-whatsapp-btn');
                    btn.innerHTML = '<i class="fas fa-check mr-1"></i> Verificado';
                    btn.style.background = 'linear-gradient(135deg, #22c55e 0%, #16a34a 100%)';
                    btn.disabled = true;
                    closeVerificationModal();
                } else {
                    alert(data.message || 'Código inválido');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Erro ao verificar código. Tente novamente.');
            });
        }

        function resendCode(phone) {
            fetch('{{ route("store-signup.whatsapp.send-code") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ phone: phone })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Código reenviado com sucesso!');
                } else {
                    alert(data.message || 'Erro ao reenviar código');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Erro ao reenviar código. Tente novamente.');
            });
        }

        // Modais de Termos e Políticas
        @php
            $termsOfUseJs = addslashes($termsOfUse ?? '');
            $privacyPolicyJs = addslashes($privacyPolicy ?? '');
        @endphp
        function showTermsModal() {
            const content = `{!! $termsOfUseJs !!}`;
            showDocumentModal('Termos de Uso', content);
        }

        function showPrivacyModal() {
            const content = `{!! $privacyPolicyJs !!}`;
            showDocumentModal('Política de Privacidade', content);
        }

        function showDocumentModal(title, content) {
            const modal = document.createElement('div');
            modal.id = 'document-modal';
            modal.className = 'fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50';
            modal.innerHTML = `
                <div class="glass-card rounded-2xl p-8 max-w-3xl w-full mx-4 max-h-[90vh] overflow-hidden flex flex-col">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-2xl font-bold text-white">${title}</h3>
                        <button onclick="closeDocumentModal()" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fas fa-times text-2xl"></i>
                        </button>
                    </div>
                    <div class="overflow-y-auto flex-1 bg-white/5 rounded-xl p-6">
                        <div class="prose prose-invert max-w-none whitespace-pre-wrap text-gray-300">${content || 'Conteúdo não disponível.'}</div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button onclick="closeDocumentModal()" 
                                class="btn-primary px-6 py-2 rounded-xl font-semibold text-white">
                            Fechar
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        function closeDocumentModal() {
            const modal = document.getElementById('document-modal');
            if (modal) modal.remove();
        }

        // Validar WhatsApp antes de submeter
        document.getElementById('signup-form')?.addEventListener('submit', function(e) {
            if (document.getElementById('whatsapp_verified').value !== '1') {
                e.preventDefault();
                alert('Por favor, verifique seu número de WhatsApp antes de continuar.');
                return false;
            }
        });
    </script>
</body>

</html>
