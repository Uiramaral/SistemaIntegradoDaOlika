<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Acesso Negado</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        olika: {
                            orange: '#ea580c',
                            brown: '#78350f',
                            'brown-light': '#92400e',
                            cream: '#fef3c7',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-amber-50 to-orange-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-lg w-full">
        <!-- Card principal -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-amber-200">
            <!-- Header com ícone - Marrom para Laranja -->
            <div class="bg-gradient-to-r from-amber-800 via-amber-700 to-orange-600 px-8 py-10 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 rounded-full mb-4 backdrop-blur-sm">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <h1 class="text-white text-6xl font-bold mb-2">403</h1>
                <p class="text-amber-100 text-lg">Acesso Restrito</p>
            </div>
            
            <!-- Conteúdo -->
            <div class="px-8 py-8 text-center">
                <h2 class="text-2xl font-bold text-amber-900 mb-3">
                    Área Exclusiva
                </h2>
                <p class="text-gray-600 mb-6">
                    Esta página é exclusiva para administradores da <strong class="text-orange-600">Olika Tecnologia</strong>. 
                    Se você acredita que deveria ter acesso, entre em contato com o suporte.
                </p>
                
                <!-- Dicas -->
                <div class="bg-amber-50 border border-amber-300 rounded-lg p-4 mb-6 text-left">
                    <h3 class="font-semibold text-amber-900 mb-2 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        O que você pode fazer:
                    </h3>
                    <ul class="text-sm text-amber-800 space-y-1">
                        <li>• Verificar se você está logado com a conta correta</li>
                        <li>• Acessar o dashboard principal do seu estabelecimento</li>
                        <li>• Entrar em contato com o suporte se precisar de ajuda</li>
                    </ul>
                </div>
                
                <!-- Botões de ação -->
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ url('/') }}" 
                       class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-amber-700 to-orange-600 text-white font-semibold rounded-lg hover:from-amber-800 hover:to-orange-700 transition-all shadow-md">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Ir para o Início
                    </a>
                    <a href="javascript:history.back()" 
                       class="inline-flex items-center justify-center px-6 py-3 bg-amber-100 text-amber-900 font-semibold rounded-lg hover:bg-amber-200 transition-colors border border-amber-300">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Voltar
                    </a>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="bg-amber-50 px-8 py-4 text-center border-t border-amber-200">
                <p class="text-sm text-amber-800">
                    Precisa de ajuda? 
                    <a href="mailto:suporte@olika.com.br" class="text-orange-600 hover:underline font-medium">
                        suporte@olika.com.br
                    </a>
                </p>
            </div>
        </div>
        
        <!-- Logo -->
        <div class="text-center mt-6">
            <span class="text-2xl font-bold bg-gradient-to-r from-amber-800 to-orange-600 bg-clip-text text-transparent">OLIKA</span>
            <p class="text-sm text-amber-700 mt-1">Sistema de Gestão de Pedidos</p>
        </div>
    </div>
</body>
</html>
