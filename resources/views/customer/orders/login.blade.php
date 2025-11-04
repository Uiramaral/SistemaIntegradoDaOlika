<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Pedidos - OLIKA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#7A5230', // Marrom Olika
                            foreground: '#fff',
                        },
                    },
                },
            },
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-primary mb-2">OLIKA</h1>
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
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none"
                           required>
                </div>

                <button type="submit" 
                        class="w-full bg-primary text-primary-foreground py-3 rounded-lg font-medium hover:bg-primary/90 transition-colors">
                    Acessar Meus Pedidos
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-500">
                Digite seu telefone para visualizar seus pedidos
            </p>
        </div>
    </div>
    
    <script>
        // Enviar apenas telefone (sem email) e salvar cookie
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
            
            // Salvar telefone no cookie (30 dias)
            document.cookie = `customer_phone=${phoneNormalized}; path=/; max-age=${60 * 60 * 24 * 30}`;
        });
        
        // Tentar preencher telefone do cookie se existir
        document.addEventListener('DOMContentLoaded', function() {
            const cookies = document.cookie.split(';');
            for (let cookie of cookies) {
                const [name, value] = cookie.trim().split('=');
                if (name === 'customer_phone' && value) {
                    document.getElementById('identifier').value = value;
                    break;
                }
            }
        });
    </script>
</body>
</html>

