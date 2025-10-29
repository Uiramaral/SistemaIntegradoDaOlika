<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Pedidos - OLIKA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary: 24 95% 53%;
            --primary-foreground: 0 0% 100%;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-orange-600 mb-2">OLIKA</h1>
                <p class="text-gray-600">Acesse seus pedidos</p>
            </div>

            @if(isset($error))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                {{ $error }}
            </div>
            @endif

            <form method="GET" action="{{ route('customer.orders.index') }}" class="space-y-4" id="login-form">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Telefone ou E-mail</label>
                    <input type="text" id="identifier" name="identifier" placeholder="(11) 99999-9999 ou email@exemplo.com" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none"
                           required>
                </div>

                <button type="submit" 
                        class="w-full bg-orange-600 text-white py-3 rounded-lg font-medium hover:bg-orange-700 transition-colors">
                    Acessar Meus Pedidos
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-500">
                Digite seu telefone para visualizar seus pedidos
            </p>
        </div>
    </div>
    
    <script>
        // Enviar apenas telefone (sem email)
        document.getElementById('login-form').addEventListener('submit', function(e) {
            const identifier = document.getElementById('identifier').value.trim();
            
            // Normalizar telefone (remover caracteres não numéricos)
            const phoneNormalized = identifier.replace(/\D/g, '');
            
            // Sempre enviar como telefone
            const phoneInput = document.createElement('input');
            phoneInput.type = 'hidden';
            phoneInput.name = 'phone';
            phoneInput.value = phoneNormalized;
            this.appendChild(phoneInput);
        });
    </script>
</body>
</html>

