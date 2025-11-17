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

            @if(!empty($statusMessage ?? null))
            <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-700">
                {{ $statusMessage }}
            </div>
            @endif

            @if(!empty($otpError ?? null))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                {{ $otpError }}
            </div>
            @endif

            @if(!empty($otpSent ?? false))
            <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg text-blue-700">
                Enviamos um código para o seu WhatsApp. Informe abaixo para acessar seus pedidos.
            </div>
            @endif

            <form method="POST" action="{{ route('customer.orders.request-token') }}" class="space-y-4" id="login-form">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Telefone ou E-mail</label>
                    <input type="text" id="identifier" name="phone" placeholder="(11) 99999-9999" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none"
                           value="{{ $phoneValue ?? old('phone') }}"
                           required>
                </div>

                <button type="submit" 
                        class="w-full bg-primary text-primary-foreground py-3 rounded-lg font-medium hover:bg-primary/90 transition-colors">
                    Receber código por WhatsApp
                </button>
            </form>

            @php
                $showOtpForm = ($needsOtp ?? false) || ($otpSent ?? false);
            @endphp

            @if($showOtpForm)
            <form method="GET" action="{{ route('customer.orders.index') }}" class="space-y-4 mt-6">
                <input type="hidden" name="phone" value="{{ $phoneValue ?? old('phone') }}">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Código de 6 dígitos</label>
                    <input type="text" name="otp" maxlength="6" minlength="6" pattern="\d{6}" placeholder="000000"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none"
                           required>
                </div>

                <button type="submit"
                        class="w-full bg-emerald-600 text-white py-3 rounded-lg font-medium hover:bg-emerald-500 transition-colors">
                    Confirmar código
                </button>
            </form>
            @endif

            <p class="mt-6 text-center text-sm text-gray-500">
                Digite seu telefone e confirme o código enviado para visualizar seus pedidos.
            </p>
        </div>
    </div>
    
    <script>
        // Enviar apenas telefone (sem email) e salvar cookie
        document.getElementById('login-form').addEventListener('submit', function(e) {
            const identifier = document.getElementById('identifier');
            if (!identifier) return;
            const phoneNormalized = identifier.value.replace(/\D/g, '');
            identifier.value = phoneNormalized;
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

