<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento PIX - Pedido {{ $order->order_number }} - OLIKA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary: hsl(24.6 95% 53.1%);
            --primary-foreground: hsl(0 0% 100%);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">OLIKA</h1>
            <p class="text-gray-600">Pagamento via PIX</p>
        </div>

        <!-- Card Principal -->
        <div class="bg-white rounded-lg shadow-lg p-6 md:p-8 mb-6">
            <!-- Informações do Pedido -->
            <div class="mb-6 pb-6 border-b">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Pedido {{ $order->order_number }}</h2>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Cliente:</span>
                        <span class="font-medium">{{ $order->customer->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total:</span>
                        <span class="text-xl font-bold text-orange-600">R$ {{ number_format($order->final_amount, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- QR Code -->
            @if($order->pix_qr_base64)
                <div class="mb-6 text-center">
                    <p class="text-sm text-gray-600 mb-4">Escaneie o QR Code com o app do seu banco</p>
                    <div class="inline-block p-4 bg-white rounded-lg border-2 border-gray-200">
                        <img src="data:image/png;base64,{{ $order->pix_qr_base64 }}" alt="QR Code PIX" class="w-64 h-64 mx-auto">
                    </div>
                </div>
            @endif

            <!-- Código PIX Copia e Cola -->
            @if($order->pix_copy_paste)
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Ou copie o código PIX:
                    </label>
                    <div class="flex gap-2">
                        <input 
                            type="text" 
                            id="pix-code" 
                            value="{{ $order->pix_copy_paste }}" 
                            readonly
                            class="flex-1 px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-orange-500"
                        >
                        <button 
                            onclick="copyPixCode()" 
                            id="copy-btn"
                            class="px-6 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors font-medium whitespace-nowrap"
                        >
                            Copiar
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-2" id="copy-feedback"></p>
                </div>
            @endif

            <!-- Prazo de Validade -->
            @if($order->pix_expires_at)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-yellow-600">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-yellow-900">Prazo de Validade</p>
                            <p class="text-sm text-yellow-700">
                                Este código PIX expira em: <strong>{{ $order->pix_expires_at->format('d/m/Y \à\s H:i') }}</strong>
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Status do Pagamento -->
            @if($order->payment_status === 'paid')
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                    <p class="text-green-900 font-medium">✓ Pagamento confirmado!</p>
                </div>
            @elseif($order->payment_status === 'pending')
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                    <p class="text-blue-900 font-medium">Aguardando pagamento...</p>
                    <p class="text-sm text-blue-700 mt-1">Após o pagamento, você receberá a confirmação</p>
                </div>
            @endif
        </div>

        <!-- Instruções -->
        <div class="bg-white rounded-lg shadow p-4 text-sm text-gray-600">
            <h3 class="font-semibold text-gray-900 mb-2">Como pagar:</h3>
            <ol class="list-decimal list-inside space-y-1">
                <li>Abra o app do seu banco</li>
                <li>Escaneie o QR Code ou copie o código PIX</li>
                <li>Confirme o pagamento</li>
                <li>Aguarde a confirmação</li>
            </ol>
        </div>
    </div>

    <script>
        function copyPixCode() {
            const pixCode = document.getElementById('pix-code');
            const copyBtn = document.getElementById('copy-btn');
            const feedback = document.getElementById('copy-feedback');
            
            pixCode.select();
            pixCode.setSelectionRange(0, 99999); // Para mobile
            
            try {
                document.execCommand('copy');
                copyBtn.textContent = 'Copiado!';
                copyBtn.classList.remove('bg-orange-600', 'hover:bg-orange-700');
                copyBtn.classList.add('bg-green-600');
                feedback.textContent = 'Código PIX copiado para a área de transferência!';
                feedback.classList.remove('text-gray-500');
                feedback.classList.add('text-green-600');
                
                setTimeout(() => {
                    copyBtn.textContent = 'Copiar';
                    copyBtn.classList.remove('bg-green-600');
                    copyBtn.classList.add('bg-orange-600', 'hover:bg-orange-700');
                    feedback.textContent = '';
                    feedback.classList.remove('text-green-600');
                    feedback.classList.add('text-gray-500');
                }, 2000);
            } catch (err) {
                feedback.textContent = 'Erro ao copiar. Tente selecionar e copiar manualmente.';
                feedback.classList.add('text-red-600');
            }
        }

        // Auto-refresh se pagamento pendente
        @if($order->payment_status === 'pending')
            setTimeout(function() {
                window.location.reload();
            }, 10000); // Atualiza a cada 10 segundos
        @endif
    </script>
</body>
</html>
