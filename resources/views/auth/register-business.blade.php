@extends('layouts.auth')

@section('title', 'Cadastrar Estabelecimento - Olika')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-8 py-6 text-center">
            <h1 class="text-2xl font-bold text-white mb-1">Cadastre seu Estabelecimento</h1>
            <p class="text-orange-100">Comece a receber pedidos online em minutos</p>
        </div>

        <!-- Formulário -->
        <form method="POST" action="{{ route('register') }}" class="p-8">
            @csrf
            
            <!-- Mensagens de erro -->
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Dados do Estabelecimento -->
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <span class="w-8 h-8 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center text-sm font-bold mr-3">1</span>
                    Dados do Estabelecimento
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Estabelecimento *</label>
                        <input type="text" name="business_name" value="{{ old('business_name') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                               placeholder="Ex: Padaria do João">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telefone/WhatsApp *</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                               placeholder="(11) 99999-9999">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Slug (URL única) *</label>
                        <div class="flex">
                            <span class="inline-flex items-center px-3 text-sm text-gray-500 bg-gray-100 border border-r-0 border-gray-300 rounded-l-lg">
                                pedido.menuolika.com.br/
                            </span>
                            <input type="text" name="slug" value="{{ old('slug') }}" required
                                   class="flex-1 px-4 py-2 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                   placeholder="meu-negocio"
                                   pattern="[a-z0-9-]+"
                                   title="Apenas letras minúsculas, números e hífens">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Apenas letras minúsculas, números e hífens</p>
                    </div>
                </div>
            </div>

            <!-- Dados do Administrador -->
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <span class="w-8 h-8 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center text-sm font-bold mr-3">2</span>
                    Dados do Administrador
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo *</label>
                        <input type="text" name="admin_name" value="{{ old('admin_name') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                               placeholder="Seu nome completo">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">E-mail *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                               placeholder="seu@email.com">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Senha *</label>
                        <input type="password" name="password" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                               placeholder="Mínimo 6 caracteres">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Senha *</label>
                        <input type="password" name="password_confirmation" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                               placeholder="Repita a senha">
                    </div>
                </div>
            </div>

            <!-- Seleção de Plano -->
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <span class="w-8 h-8 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center text-sm font-bold mr-3">3</span>
                    Escolha seu Plano
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @forelse($plans ?? [] as $plan)
                        <label class="relative cursor-pointer">
                            <input type="radio" name="plan_id" value="{{ $plan->id }}" 
                                   class="sr-only peer" {{ $loop->first ? 'checked' : '' }}>
                            <div class="border-2 rounded-xl p-4 transition-all peer-checked:border-orange-500 peer-checked:bg-orange-50 hover:border-orange-300">
                                @if($plan->is_featured)
                                    <span class="absolute -top-2 left-1/2 -translate-x-1/2 bg-orange-500 text-white text-xs px-2 py-0.5 rounded-full">
                                        Popular
                                    </span>
                                @endif
                                <h3 class="font-bold text-gray-800 text-center">{{ $plan->name }}</h3>
                                <p class="text-2xl font-bold text-orange-600 text-center my-2">
                                    R$ {{ number_format($plan->price, 2, ',', '.') }}
                                    <span class="text-sm text-gray-500 font-normal">/mês</span>
                                </p>
                                <ul class="text-xs text-gray-600 space-y-1">
                                    @foreach(json_decode($plan->features ?? '[]') as $feature)
                                        <li class="flex items-start">
                                            <svg class="w-4 h-4 text-green-500 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            {{ $feature }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </label>
                    @empty
                        <!-- Planos padrão se não houver no banco -->
                        <label class="relative cursor-pointer">
                            <input type="radio" name="plan_slug" value="basico" class="sr-only peer" checked>
                            <div class="border-2 rounded-xl p-4 transition-all peer-checked:border-orange-500 peer-checked:bg-orange-50 hover:border-orange-300">
                                <h3 class="font-bold text-gray-800 text-center">Básico</h3>
                                <p class="text-2xl font-bold text-orange-600 text-center my-2">
                                    R$ 49,90<span class="text-sm text-gray-500 font-normal">/mês</span>
                                </p>
                                <p class="text-xs text-gray-500 text-center">Cardápio digital + Gestão de pedidos</p>
                            </div>
                        </label>
                        
                        <label class="relative cursor-pointer">
                            <input type="radio" name="plan_slug" value="whatsapp" class="sr-only peer">
                            <div class="border-2 rounded-xl p-4 transition-all peer-checked:border-orange-500 peer-checked:bg-orange-50 hover:border-orange-300">
                                <span class="absolute -top-2 left-1/2 -translate-x-1/2 bg-orange-500 text-white text-xs px-2 py-0.5 rounded-full">Popular</span>
                                <h3 class="font-bold text-gray-800 text-center">WhatsApp</h3>
                                <p class="text-2xl font-bold text-orange-600 text-center my-2">
                                    R$ 99,90<span class="text-sm text-gray-500 font-normal">/mês</span>
                                </p>
                                <p class="text-xs text-gray-500 text-center">Básico + Notificações WhatsApp</p>
                            </div>
                        </label>
                        
                        <label class="relative cursor-pointer">
                            <input type="radio" name="plan_slug" value="whatsapp-ia" class="sr-only peer">
                            <div class="border-2 rounded-xl p-4 transition-all peer-checked:border-orange-500 peer-checked:bg-orange-50 hover:border-orange-300">
                                <h3 class="font-bold text-gray-800 text-center">WhatsApp + I.A.</h3>
                                <p class="text-2xl font-bold text-orange-600 text-center my-2">
                                    R$ 199,90<span class="text-sm text-gray-500 font-normal">/mês</span>
                                </p>
                                <p class="text-xs text-gray-500 text-center">WhatsApp + Atendimento com I.A.</p>
                            </div>
                        </label>
                    @endforelse
                </div>
                
                <p class="text-center text-sm text-gray-500 mt-4">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    7 dias grátis para testar! Cancele quando quiser.
                </p>
            </div>

            <!-- Termos -->
            <div class="mb-6">
                <label class="flex items-start">
                    <input type="checkbox" name="terms" required class="mt-1 mr-3 rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                    <span class="text-sm text-gray-600">
                        Li e concordo com os 
                        <a href="#" class="text-orange-600 hover:underline">Termos de Uso</a> e 
                        <a href="#" class="text-orange-600 hover:underline">Política de Privacidade</a>
                    </span>
                </label>
            </div>

            <!-- Botão de Submit -->
            <button type="submit" 
                    class="w-full py-3 px-6 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-lg transition-colors flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                Criar Meu Estabelecimento
            </button>
        </form>

        <!-- Footer -->
        <div class="bg-gray-50 px-8 py-4 text-center border-t">
            <p class="text-sm text-gray-600">
                Já tem uma conta? 
                <a href="{{ route('login') }}" class="text-orange-600 hover:underline font-medium">Fazer Login</a>
            </p>
        </div>
    </div>
</div>
@endsection
