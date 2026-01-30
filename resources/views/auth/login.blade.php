<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $systemName = \App\Models\MasterSetting::get('system_name', 'OLIKA');
        $systemFaviconUrl = \App\Models\MasterSetting::get('system_favicon_url', '');
        $usePublicFavicons = file_exists(public_path('favicon/favicon.ico'));
    @endphp
    <title>Login - {{ $systemName }}</title>
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
        
        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }
        
        .shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.5;
        }
        
        .shape-1 {
            width: 400px;
            height: 400px;
            background: rgba(249, 115, 22, 0.3);
            top: -100px;
            right: -100px;
            animation: float1 8s ease-in-out infinite;
        }
        
        .shape-2 {
            width: 300px;
            height: 300px;
            background: rgba(239, 68, 68, 0.3);
            bottom: -50px;
            left: -50px;
            animation: float2 10s ease-in-out infinite;
        }
        
        .shape-3 {
            width: 200px;
            height: 200px;
            background: rgba(59, 130, 246, 0.2);
            top: 50%;
            left: 30%;
            animation: float3 12s ease-in-out infinite;
        }
        
        @keyframes float1 {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(-30px, 30px) rotate(10deg); }
        }
        
        @keyframes float2 {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(30px, -30px) rotate(-10deg); }
        }
        
        @keyframes float3 {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(-20px, 20px); }
        }
        
        .feature-item {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s ease forwards;
        }
        
        .feature-item:nth-child(1) { animation-delay: 0.2s; }
        .feature-item:nth-child(2) { animation-delay: 0.4s; }
        .feature-item:nth-child(3) { animation-delay: 0.6s; }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="gradient-bg min-h-screen relative overflow-hidden">
    <!-- Floating Background Shapes -->
    <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    @php
        $systemName = \App\Models\MasterSetting::get('system_name', 'OLIKA');
        $systemLogoUrl = \App\Models\MasterSetting::get('system_logo_url', '');
        $welcomeMessage = \App\Models\MasterSetting::get('system_welcome_message', 'Bem-vindo ao ' . $systemName);
        
        if ($systemLogoUrl && $systemLogoUrl !== '') {
            $systemLogoUrl .= (strpos($systemLogoUrl, '?') !== false ? '&' : '?') . 'v=' . time();
        }
    @endphp

    <div class="min-h-screen flex relative z-10">
        <!-- Left Side - Branding/Features (Hidden on mobile) -->
        <div class="hidden lg:flex lg:w-1/2 flex-col justify-center p-12 xl:p-20">
            <div class="max-w-lg">
                <!-- Logo -->
                <div class="flex items-center gap-4 mb-12">
                    @if($systemLogoUrl && $systemLogoUrl !== '')
                        <img src="{{ $systemLogoUrl }}" alt="{{ $systemName }}" class="w-14 h-14 object-contain rounded-xl">
                    @else
                        <div class="w-14 h-14 bg-gradient-to-br from-orange-500 to-red-500 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-utensils text-white text-2xl"></i>
                        </div>
                    @endif
                    <span class="text-2xl font-bold text-white">{{ $systemName }}</span>
                </div>
                
                <!-- Headline -->
                <h1 class="text-4xl xl:text-5xl font-extrabold text-white leading-tight mb-6">
                    Gerencie seus <span class="gradient-text">pedidos</span> de forma simples
                </h1>
                
                <p class="text-gray-400 text-lg mb-10">
                    Acesse seu painel para acompanhar vendas, gerenciar pedidos e controlar suas finanças em tempo real.
                </p>
                
                <!-- Features -->
                <div class="space-y-6">
                    <div class="feature-item flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-orange-500/20 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-chart-line text-orange-400 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold">Dashboard em tempo real</h3>
                            <p class="text-gray-400 text-sm">Acompanhe vendas e pedidos ao vivo</p>
                        </div>
                    </div>
                    
                    <div class="feature-item flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-green-500/20 flex items-center justify-center flex-shrink-0">
                            <i class="fab fa-whatsapp text-green-400 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold">Notificações automáticas</h3>
                            <p class="text-gray-400 text-sm">WhatsApp integrado para seus clientes</p>
                        </div>
                    </div>
                    
                    <div class="feature-item flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-mobile-alt text-blue-400 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold">100% Responsivo</h3>
                            <p class="text-gray-400 text-sm">Acesse de qualquer dispositivo</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Side - Login Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12">
            <div class="w-full max-w-md">
                <!-- Mobile Logo -->
                <div class="lg:hidden text-center mb-8">
                    @if($systemLogoUrl && $systemLogoUrl !== '')
                        <img src="{{ $systemLogoUrl }}" alt="{{ $systemName }}" class="w-16 h-16 object-contain mx-auto rounded-xl mb-4">
                    @else
                        <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-red-500 rounded-xl flex items-center justify-center shadow-lg mx-auto mb-4">
                            <i class="fas fa-utensils text-white text-2xl"></i>
                        </div>
                    @endif
                    <h1 class="text-2xl font-bold text-white">{{ $systemName }}</h1>
                </div>
                
                <!-- Login Card -->
                <div class="glass-card rounded-3xl p-8 sm:p-10">
                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-bold text-white mb-2">{{ $welcomeMessage }}</h2>
                        <p class="text-gray-400">Faça login para continuar</p>
                    </div>
                    
                    @if(session('error'))
                        <div class="mb-6 p-4 bg-red-500/20 border border-red-500/30 text-red-300 rounded-xl text-sm">
                            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                        </div>
                    @endif
                    @if(session('success'))
                        <div class="mb-6 p-4 bg-green-500/20 border border-green-500/30 text-green-300 rounded-xl text-sm">
                            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                        </div>
                    @endif
                    @if(isset($errors) && $errors->any())
                        <div class="mb-6 p-4 bg-red-500/20 border border-red-500/30 text-red-300 rounded-xl text-sm">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('auth.login') }}" class="space-y-6">
                        @csrf
                        <div class="space-y-2">
                            <label for="email" class="block text-sm font-medium text-gray-300">Email</label>
                            <div class="relative">
                                <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input id="email" name="email" type="email" required autofocus value="{{ old('email') }}"
                                       placeholder="seu@email.com"
                                       class="input-modern w-full pl-12 pr-4 h-14 rounded-xl text-white focus:outline-none">
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <label for="password" class="block text-sm font-medium text-gray-300">Senha</label>
                            <div class="relative">
                                <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input id="password" name="password" type="password" required
                                       placeholder="••••••••"
                                       class="input-modern w-full pl-12 pr-14 h-14 rounded-xl text-white focus:outline-none">
                                <button type="button" tabindex="-1" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-200 transition-colors" id="toggle-password" aria-label="Mostrar/ocultar senha">
                                    <i class="fas fa-eye" id="eye-icon"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-600 bg-transparent text-orange-500 focus:ring-orange-500 focus:ring-offset-0">
                                <span class="text-sm text-gray-300">Lembrar-me</span>
                            </label>
                            <a href="#" class="text-sm text-orange-400 hover:text-orange-300 transition-colors">Esqueceu a senha?</a>
                        </div>
                        
                        <button type="submit" class="btn-primary w-full h-14 rounded-xl text-white font-semibold text-lg flex items-center justify-center gap-2">
                            <i class="fas fa-sign-in-alt"></i>
                            Entrar
                        </button>
                    </form>
                    
                    <div class="mt-8 text-center">
                        <p class="text-gray-400">
                            Não tem conta? 
                            <a href="{{ route('store-signup.show') }}" class="text-orange-400 font-semibold hover:text-orange-300 transition-colors">
                                Criar conta grátis
                            </a>
                        </p>
                    </div>
                </div>
                
                <!-- Footer -->
                <p class="text-center text-gray-500 text-sm mt-8">
                    © {{ date('Y') }} {{ $systemName }}. Todos os direitos reservados.
                </p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var pw = document.getElementById('password');
            var btn = document.getElementById('toggle-password');
            var icon = document.getElementById('eye-icon');
            if (btn && pw && icon) {
                btn.addEventListener('click', function() {
                    var show = pw.type === 'password';
                    pw.type = show ? 'text' : 'password';
                    icon.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
                });
            }
        });
    </script>
</body>
</html>
