<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Lojista - OLIKA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-orange-50 to-orange-100 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                🍞 Comece a vender com a OLIKA
            </h1>
            <p class="text-xl text-gray-600">
                Escolha o plano ideal para o seu negócio e comece hoje mesmo
            </p>
            <p class="text-lg text-orange-600 font-semibold mt-2">
                ✨ 14 dias de teste grátis em todos os planos
            </p>
        </div>

        @if(session('error'))
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 text-red-900 px-4 py-3">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 text-red-900 px-4 py-3">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Planos -->
        <div class="grid md:grid-cols-2 gap-8 mb-12">
            @foreach($plans as $planKey => $plan)
                <div class="bg-white rounded-xl shadow-lg p-8 border-2 {{ isset($plan['featured']) && $plan['featured'] ? 'border-orange-400 ring-4 ring-orange-100' : 'border-gray-200' }} hover:border-orange-400 transition-all relative">
                    @if(isset($plan['featured']) && $plan['featured'])
                        <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                            <span class="bg-orange-500 text-white px-4 py-1 rounded-full text-sm font-semibold shadow-lg">🌟 Mais Popular</span>
                        </div>
                    @endif
                    
                    <div class="mb-4">
                        <h3 class="text-2xl font-bold text-gray-900">{{ $plan['name'] }}</h3>
                        <p class="text-gray-600 text-sm mt-1">{{ $plan['description'] }}</p>
                    </div>
                    
                    <div class="mb-6">
                        <span class="text-4xl font-bold text-gray-900">{{ $plan['price'] }}</span>
                        <span class="text-gray-500 text-lg">{{ $plan['price_label'] }}</span>
                    </div>

                    <div class="mb-6">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                            <p class="text-sm text-green-800 font-semibold">
                                <i class="fas fa-gift mr-2"></i>
                                {{ $plan['trial_days'] }} dias de teste grátis
                            </p>
                        </div>
                    </div>

                    <ul class="space-y-3 mb-8 min-h-[300px]">
                        @foreach($plan['features'] as $feature)
                            <li class="flex items-start gap-3">
                                @if(str_starts_with($feature, '✨'))
                                    {{-- Feature especial (inclui tudo do anterior) --}}
                                    <i class="fas fa-crown text-orange-500 mt-1 flex-shrink-0"></i>
                                    <span class="text-gray-900 font-semibold">{{ str_replace('✨ ', '', $feature) }}</span>
                                @else
                                    <i class="fas fa-check-circle text-green-500 mt-1 flex-shrink-0"></i>
                                    <span class="text-gray-700">{{ $feature }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>

                    <button 
                        onclick="selectPlan('{{ $planKey }}')" 
                        class="w-full {{ isset($plan['featured']) && $plan['featured'] ? 'bg-orange-500 hover:bg-orange-600 ring-2 ring-orange-300' : 'bg-gray-700 hover:bg-gray-800' }} text-white font-semibold py-3 px-6 rounded-lg transition-all transform hover:scale-105"
                        data-plan="{{ $planKey }}"
                    >
                        <i class="fas fa-rocket mr-2"></i>
                        Escolher {{ $plan['name'] }}
                    </button>
                </div>
            @endforeach
        </div>

        <!-- Formulário de Cadastro -->
        <div id="signup-form" class="hidden bg-white rounded-xl shadow-lg p-8 max-w-2xl mx-auto">
            <h2 class="text-3xl font-bold text-gray-900 mb-6 text-center">Complete seu cadastro</h2>
            
            <form action="{{ route('store-signup.store') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="plan" id="selected-plan" required>

                <!-- Dados da Empresa -->
                <div class="border-b pb-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Dados da Empresa</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nome da Empresa <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="company_name" 
                                name="company_name" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                placeholder="Ex: Padaria do João"
                            >
                        </div>
                    </div>
                </div>

                <!-- Dados Pessoais -->
                <div class="border-b pb-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Seus Dados</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Seu Nome <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                placeholder="Ex: João Silva"
                            >
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                E-mail <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                placeholder="seu@email.com"
                            >
                        </div>

                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                    Senha <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    required
                                    minlength="6"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                    placeholder="Mínimo 6 caracteres"
                                >
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                    Confirmar Senha <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="password" 
                                    id="password_confirmation" 
                                    name="password_confirmation" 
                                    required
                                    minlength="6"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                    placeholder="Digite novamente"
                                >
                            </div>
                        </div>

                        <div>
                            <label for="whatsapp_phone" class="block text-sm font-medium text-gray-700 mb-2">
                                WhatsApp (Opcional)
                            </label>
                            <input 
                                type="text" 
                                id="whatsapp_phone" 
                                name="whatsapp_phone"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                placeholder="(11) 99999-9999"
                            >
                            <p class="text-xs text-gray-500 mt-1">Para receber notificações importantes</p>
                        </div>
                    </div>
                </div>

                <!-- Termos -->
                <div class="pb-6">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input 
                            type="checkbox" 
                            name="accept_terms" 
                            value="1" 
                            required
                            class="mt-1 w-5 h-5 text-orange-500 border-gray-300 rounded focus:ring-orange-500"
                        >
                        <span class="text-sm text-gray-700">
                            Eu aceito os <a href="#" class="text-orange-500 hover:underline">termos de uso</a> e <a href="#" class="text-orange-500 hover:underline">política de privacidade</a> <span class="text-red-500">*</span>
                        </span>
                    </label>
                </div>

                <!-- Botões -->
                <div class="flex gap-4">
                    <button 
                        type="button"
                        onclick="cancelSignup()"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-6 rounded-lg transition-colors"
                    >
                        Voltar
                    </button>
                    <button 
                        type="submit"
                        class="flex-1 bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3 px-6 rounded-lg transition-colors"
                    >
                        <i class="fas fa-rocket mr-2"></i>
                        Criar Conta e Começar Teste Grátis
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function selectPlan(planKey) {
            // Preencher campo hidden do plano
            document.getElementById('selected-plan').value = planKey;
            
            // Mostrar formulário
            document.getElementById('signup-form').classList.remove('hidden');
            
            // Scroll suave até o formulário
            document.getElementById('signup-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function cancelSignup() {
            // Esconder formulário
            document.getElementById('signup-form').classList.add('hidden');
            
            // Limpar seleção
            document.getElementById('selected-plan').value = '';
            
            // Scroll até o topo
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Preencher formulário se houver erros de validação
        @if(old('plan'))
            selectPlan('{{ old('plan') }}');
        @endif
    </script>
</body>
</html>

