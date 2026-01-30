<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $systemName = \App\Models\MasterSetting::get('system_name', 'OLIKA');
        $systemLogoUrl = \App\Models\MasterSetting::get('system_logo_url', '');
        $systemFaviconUrl = \App\Models\MasterSetting::get('system_favicon_url', '');
        $systemDescription = \App\Models\MasterSetting::get('system_description', 'Sistema Completo para Delivery e Gestão de Pedidos');
        $usePublicFavicons = file_exists(public_path('favicon/favicon.ico'));

        if ($systemLogoUrl && $systemLogoUrl !== '') {
            $systemLogoUrl .= (strpos($systemLogoUrl, '?') !== false ? '&' : '?') . 'v=' . time();
        }
    @endphp
    <title>{{ $systemName }} - {{ $systemDescription }}</title>
    <meta name="description"
        content="Transforme seu negócio com o sistema de delivery mais completo. Cardápio digital, gestão de pedidos, finanças e muito mais. Teste grátis por 14 dias!">
    <meta name="keywords"
        content="sistema delivery, cardápio digital, gestão pedidos, restaurante, lanchonete, pizzaria">
    <meta property="og:title" content="{{ $systemName }} - Sistema Completo para Delivery">
    <meta property="og:description" content="Cardápio digital, gestão de pedidos, finanças integradas. Teste grátis!">
    <meta property="og:type" content="website">

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
                    },
                    colors: {
                        'brand': {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            200: '#fed7aa',
                            300: '#fdba74',
                            400: '#fb923c',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c',
                            800: '#9a3412',
                            900: '#7c2d12',
                        },
                        'accent': {
                            500: '#ef4444',
                            600: '#dc2626',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        * {
            font-family: 'Exo 2', sans-serif;
        }

        .gradient-hero {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        }

        .gradient-text {
            background: linear-gradient(135deg, #f97316 0%, #ef4444 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .card-glow {
            box-shadow: 0 0 40px rgba(249, 115, 22, 0.15);
        }

        .card-glow:hover {
            box-shadow: 0 0 60px rgba(249, 115, 22, 0.25);
            transform: translateY(-4px);
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
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .feature-icon {
            background: linear-gradient(135deg, #f97316 0%, #ef4444 100%);
        }

        .floating {
            animation: floating 3s ease-in-out infinite;
        }

        @keyframes floating {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .pulse-dot {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .pricing-popular {
            border: 2px solid #f97316;
            position: relative;
        }

        .pricing-popular::before {
            content: '⭐ MAIS POPULAR';
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #f97316 0%, #ef4444 100%);
            color: white;
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
    </style>
</head>

<body class="bg-slate-900 text-white overflow-x-hidden">

    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 glass-card">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    @if($systemLogoUrl && $systemLogoUrl !== '')
                        <img src="{{ $systemLogoUrl }}" alt="{{ $systemName }}" class="w-10 h-10 object-contain rounded-xl">
                    @else
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-500 to-red-500 flex items-center justify-center">
                            <i class="fas fa-utensils text-white text-lg"></i>
                        </div>
                    @endif
                    <span class="text-xl font-bold">{{ $systemName }}</span>
                </div>
                <div class="hidden md:flex items-center gap-8">
                    <a href="#features" class="text-gray-300 hover:text-white transition-colors">Recursos</a>
                    <a href="#pricing" class="text-gray-300 hover:text-white transition-colors">Planos</a>
                    <a href="#faq" class="text-gray-300 hover:text-white transition-colors">FAQ</a>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('dashboard.login') }}"
                        class="text-gray-300 hover:text-white transition-colors">Entrar</a>
                    <a href="{{ route('store-signup.show') }}"
                        class="btn-primary px-5 py-2 rounded-lg font-semibold text-sm">
                        Começar Grátis
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="gradient-hero min-h-screen flex items-center pt-16 relative overflow-hidden">
        <!-- Background decorations -->
        <div class="absolute top-20 left-10 w-72 h-72 bg-orange-500/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-red-500/10 rounded-full blur-3xl"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="text-center lg:text-left">
                    <div
                        class="inline-flex items-center gap-2 bg-orange-500/20 text-orange-400 px-4 py-2 rounded-full text-sm font-medium mb-6">
                        <span class="pulse-dot w-2 h-2 bg-orange-400 rounded-full"></span>
                        14 dias de teste grátis
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-tight mb-6">
                        Seu negócio de
                        <span class="gradient-text">delivery</span>
                        no próximo nível
                    </h1>

                    <p class="text-lg sm:text-xl text-gray-300 mb-8 max-w-xl">
                        Cardápio digital profissional, gestão completa de pedidos, finanças integradas e muito mais.
                        Tudo em uma única plataforma.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="{{ route('store-signup.show') }}"
                            class="btn-primary px-8 py-4 rounded-xl font-bold text-lg inline-flex items-center justify-center gap-2">
                            <i class="fas fa-rocket"></i>
                            Começar Agora - É Grátis
                        </a>
                        <a href="#features"
                            class="btn-secondary px-8 py-4 rounded-xl font-semibold text-lg inline-flex items-center justify-center gap-2">
                            <i class="fas fa-info-circle"></i>
                            Saiba Mais
                        </a>
                    </div>

                    <div class="flex items-center gap-8 mt-10 justify-center lg:justify-start">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-orange-400">
                                <i class="fas fa-check-circle text-lg"></i>
                            </div>
                            <div class="text-sm text-gray-400">Sem Mensalidade*</div>
                        </div>
                        <div class="w-px h-10 bg-gray-700"></div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-orange-400">
                                <i class="fas fa-check-circle text-lg"></i>
                            </div>
                            <div class="text-sm text-gray-400">Sem Taxa por Pedido</div>
                        </div>
                        <div class="w-px h-10 bg-gray-700"></div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-orange-400">
                                <i class="fas fa-check-circle text-lg"></i>
                            </div>
                            <div class="text-sm text-gray-400">100% Seu</div>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div class="floating">
                        <div class="bg-slate-800 rounded-3xl p-6 shadow-2xl card-glow">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            </div>
                            <div class="bg-slate-900 rounded-2xl p-4">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-sm text-gray-400">Dashboard</span>
                                    <span class="text-xs bg-green-500/20 text-green-400 px-2 py-1 rounded-full">Ao
                                        vivo</span>
                                </div>
                                <div class="grid grid-cols-2 gap-3 mb-4">
                                    <div class="bg-slate-800 rounded-xl p-3">
                                        <div class="text-xs text-gray-400 mb-1">Vendas Hoje</div>
                                        <div class="text-xl font-bold text-green-400">R$ 2.450</div>
                                    </div>
                                    <div class="bg-slate-800 rounded-xl p-3">
                                        <div class="text-xs text-gray-400 mb-1">Pedidos</div>
                                        <div class="text-xl font-bold text-orange-400">47</div>
                                    </div>
                                </div>
                                <div class="bg-slate-800 rounded-xl p-3">
                                    <div class="text-xs text-gray-400 mb-2">Últimos Pedidos</div>
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-300">#1234 - João</span>
                                            <span class="text-green-400">Entregue</span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-300">#1235 - Maria</span>
                                            <span class="text-yellow-400">Em preparo</span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-300">#1236 - Pedro</span>
                                            <span class="text-blue-400">Novo</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold mb-4">
                    Tudo que você precisa em <span class="gradient-text">um só lugar</span>
                </h2>
                <p class="text-gray-400 text-lg max-w-2xl mx-auto">
                    Recursos poderosos para transformar a gestão do seu negócio
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-slate-900 rounded-2xl p-6 card-glow transition-all duration-300">
                    <div class="feature-icon w-12 h-12 rounded-xl flex items-center justify-center mb-4">
                        <i class="fas fa-mobile-alt text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Cardápio Digital</h3>
                    <p class="text-gray-400">
                        Cardápio online bonito e responsivo. Seus clientes fazem pedidos direto pelo celular, sem
                        precisar de app.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-slate-900 rounded-2xl p-6 card-glow transition-all duration-300">
                    <div class="feature-icon w-12 h-12 rounded-xl flex items-center justify-center mb-4">
                        <i class="fas fa-clipboard-list text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Gestão de Pedidos</h3>
                    <p class="text-gray-400">
                        Painel completo para gerenciar pedidos em tempo real. Status, impressão, notificações por
                        WhatsApp.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-slate-900 rounded-2xl p-6 card-glow transition-all duration-300">
                    <div class="feature-icon w-12 h-12 rounded-xl flex items-center justify-center mb-4">
                        <i class="fas fa-chart-line text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Finanças Integradas</h3>
                    <p class="text-gray-400">
                        Receitas, despesas, relatórios detalhados. Tenha controle total das finanças do seu negócio.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-slate-900 rounded-2xl p-6 card-glow transition-all duration-300">
                    <div class="feature-icon w-12 h-12 rounded-xl flex items-center justify-center mb-4">
                        <i class="fab fa-whatsapp text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">WhatsApp Integrado</h3>
                    <p class="text-gray-400">
                        Notificações automáticas para clientes. Confirmação, preparo, saiu para entrega, tudo via
                        WhatsApp.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="bg-slate-900 rounded-2xl p-6 card-glow transition-all duration-300">
                    <div class="feature-icon w-12 h-12 rounded-xl flex items-center justify-center mb-4">
                        <i class="fas fa-users text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Gestão de Clientes</h3>
                    <p class="text-gray-400">
                        Histórico completo, cashback, programa de fidelidade. Fidelize seus clientes e aumente as
                        vendas.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="bg-slate-900 rounded-2xl p-6 card-glow transition-all duration-300">
                    <div class="feature-icon w-12 h-12 rounded-xl flex items-center justify-center mb-4">
                        <i class="fas fa-cash-register text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">PDV Integrado</h3>
                    <p class="text-gray-400">
                        Venda no balcão com o mesmo sistema. Registre vendas presenciais e tenha tudo centralizado.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-20 bg-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold mb-4">
                    Simples e <span class="gradient-text">transparente</span>
                </h2>
                <p class="text-gray-400 text-lg max-w-2xl mx-auto">
                    Pagamento único, sem taxas por pedido, sem surpresas
                </p>
            </div>

            <div class="max-w-lg mx-auto">
                <!-- Single Plan -->
                <div class="bg-slate-800 rounded-2xl p-8 pricing-popular">
                    <div class="text-center mb-6">
                        <h3 class="text-2xl font-bold mb-2">Plano Completo</h3>
                        <p class="text-gray-400">Tudo que você precisa para seu negócio</p>
                    </div>
                    <div class="text-center mb-8">
                        <div class="text-5xl font-bold mb-2">Pagamento Único</div>
                        <p class="text-orange-400 font-semibold">Sem mensalidade • 100% seu</p>
                    </div>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center gap-3 text-gray-300">
                            <i class="fas fa-check text-green-400 flex-shrink-0"></i>
                            Cardápio digital personalizado
                        </li>
                        <li class="flex items-center gap-3 text-gray-300">
                            <i class="fas fa-check text-green-400 flex-shrink-0"></i>
                            Gestão completa de pedidos
                        </li>
                        <li class="flex items-center gap-3 text-gray-300">
                            <i class="fas fa-check text-green-400 flex-shrink-0"></i>
                            Módulo financeiro integrado
                        </li>
                        <li class="flex items-center gap-3 text-gray-300">
                            <i class="fas fa-check text-green-400 flex-shrink-0"></i>
                            WhatsApp com notificações automáticas
                        </li>
                        <li class="flex items-center gap-3 text-gray-300">
                            <i class="fas fa-check text-green-400 flex-shrink-0"></i>
                            Gestão de clientes e fidelidade
                        </li>
                        <li class="flex items-center gap-3 text-gray-300">
                            <i class="fas fa-check text-green-400 flex-shrink-0"></i>
                            PDV para vendas presenciais
                        </li>
                        <li class="flex items-center gap-3 text-gray-300">
                            <i class="fas fa-check text-green-400 flex-shrink-0"></i>
                            Relatórios e dashboards
                        </li>
                        <li class="flex items-center gap-3 text-gray-300">
                            <i class="fas fa-check text-green-400 flex-shrink-0"></i>
                            Suporte por WhatsApp
                        </li>
                    </ul>
                    <a href="{{ route('store-signup.show') }}"
                        class="btn-primary block w-full text-center py-4 rounded-xl font-bold text-lg">
                        <i class="fas fa-rocket mr-2"></i>
                        Começar Agora
                    </a>
                    <p class="text-center text-gray-500 text-sm mt-4">
                        <i class="fas fa-shield-alt mr-1"></i>
                        Teste grátis por 14 dias • Sem cartão de crédito
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-20 bg-slate-800">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold mb-4">
                    Perguntas <span class="gradient-text">Frequentes</span>
                </h2>
            </div>

            <div class="space-y-4">
                <div class="bg-slate-900 rounded-xl p-6">
                    <h3 class="text-lg font-semibold mb-2 flex items-center gap-3">
                        <i class="fas fa-question-circle text-orange-400"></i>
                        Preciso instalar algum aplicativo?
                    </h3>
                    <p class="text-gray-400 pl-9">
                        Não! O {{ $systemName }} funciona 100% na web. Você acessa pelo navegador do celular ou
                        computador. Seus
                        clientes também fazem pedidos pelo navegador, sem precisar baixar nada.
                    </p>
                </div>

                <div class="bg-slate-900 rounded-xl p-6">
                    <h3 class="text-lg font-semibold mb-2 flex items-center gap-3">
                        <i class="fas fa-question-circle text-orange-400"></i>
                        Como funciona o período de teste?
                    </h3>
                    <p class="text-gray-400 pl-9">
                        Você tem 14 dias para testar todas as funcionalidades gratuitamente. Não pedimos cartão de
                        crédito para começar. Após o período, você escolhe se quer continuar.
                    </p>
                </div>

                <div class="bg-slate-900 rounded-xl p-6">
                    <h3 class="text-lg font-semibold mb-2 flex items-center gap-3">
                        <i class="fas fa-question-circle text-orange-400"></i>
                        Vocês cobram taxa por pedido?
                    </h3>
                    <p class="text-gray-400 pl-9">
                        Não! Você paga apenas uma vez e usa para sempre. Não cobramos nenhuma porcentagem sobre suas
                        vendas. O
                        dinheiro é 100% seu.
                    </p>
                </div>

                <div class="bg-slate-900 rounded-xl p-6">
                    <h3 class="text-lg font-semibold mb-2 flex items-center gap-3">
                        <i class="fas fa-question-circle text-orange-400"></i>
                        E se eu precisar de ajuda?
                    </h3>
                    <p class="text-gray-400 pl-9">
                        Oferecemos suporte direto pelo WhatsApp. Nosso time está pronto para ajudar você a configurar e
                        usar o sistema da melhor forma.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 gradient-hero relative overflow-hidden">
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-orange-500/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-red-500/10 rounded-full blur-3xl"></div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-6">
                Pronto para transformar seu <span class="gradient-text">negócio</span>?
            </h2>
            <p class="text-xl text-gray-300 mb-8 max-w-2xl mx-auto">
                Comece agora mesmo e veja como o {{ $systemName }} pode facilitar a gestão do seu delivery.
            </p>
            <a href="{{ route('store-signup.show') }}"
                class="btn-primary px-10 py-4 rounded-xl font-bold text-lg inline-flex items-center justify-center gap-2">
                <i class="fas fa-rocket"></i>
                Começar Meu Teste Grátis
            </a>
            <p class="text-gray-400 text-sm mt-4">
                Sem necessidade de cartão de crédito • Configure em 5 minutos
            </p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-900 border-t border-slate-800 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        @if($systemLogoUrl && $systemLogoUrl !== '')
                            <img src="{{ $systemLogoUrl }}" alt="{{ $systemName }}"
                                class="w-10 h-10 object-contain rounded-xl">
                        @else
                            <div
                                class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-500 to-red-500 flex items-center justify-center">
                                <i class="fas fa-utensils text-white text-lg"></i>
                            </div>
                        @endif
                        <span class="text-xl font-bold">{{ $systemName }}</span>
                    </div>
                    <p class="text-gray-400 text-sm">
                        Sistema completo para gestão de delivery e pedidos.
                    </p>
                </div>

                <div>
                    <h4 class="font-semibold mb-4">Produto</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="#features" class="hover:text-white transition-colors">Recursos</a></li>
                        <li><a href="#pricing" class="hover:text-white transition-colors">Preços</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-semibold mb-4">Suporte</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="#faq" class="hover:text-white transition-colors">FAQ</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white transition-colors">Área do
                                Cliente</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-semibold mb-4">Acesse</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="{{ route('login') }}" class="hover:text-white transition-colors">Entrar</a></li>
                        <li><a href="{{ route('store-signup.show') }}" class="hover:text-white transition-colors">Criar
                                Conta</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-slate-800 pt-8 flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-gray-400 text-sm">
                    © {{ date('Y') }} {{ $systemName }}. Todos os direitos reservados.
                </p>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>

</body>

</html>