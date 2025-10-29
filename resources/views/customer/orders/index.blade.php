<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seus Pedidos - OLIKA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: 24 95% 53%;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-4xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-orange-600">OLIKA</h1>
                    <p class="text-sm text-gray-600">Olá, {{ $customer->name }}</p>
                </div>
                <a href="{{ route('customer.orders.index', ['phone' => request('phone')]) }}" 
                   class="text-gray-600 hover:text-orange-600">
                    <i class="fas fa-home"></i>
                </a>
            </div>
        </div>
    </header>

    <!-- Conteúdo -->
    <main class="max-w-4xl mx-auto px-4 py-6">
        <h2 class="text-2xl font-bold mb-6">Seus Pedidos</h2>

        @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
            {{ session('success') }}
        </div>
        @endif

        @forelse($orders as $order)
            @php
                $statusLabels = [
                    'pending' => 'Pendente',
                    'confirmed' => 'Confirmado',
                    'preparing' => 'Em Preparo',
                    'ready' => 'Pronto',
                    'delivered' => 'Entregue',
                    'cancelled' => 'Cancelado',
                ];
                $statusColors = [
                    'delivered' => 'bg-green-100 text-green-800',
                    'cancelled' => 'bg-red-100 text-red-800',
                ];
                $statusLabel = $statusLabels[$order->status] ?? ucfirst($order->status);
                $statusColor = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800';
                $isDelivered = $order->status === 'delivered';
            @endphp

            <div class="bg-white rounded-lg shadow-md p-6 mb-4">
                <!-- Cabeçalho do Pedido -->
                <div class="flex justify-between items-start mb-4 pb-4 border-b">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">N. pedido: <strong>{{ $order->order_number }}</strong></p>
                        @if($order->created_at)
                        <p class="text-xs text-gray-500">Horário do pedido {{ $order->created_at->format('d/m/Y H:i:s') }}</p>
                        @endif
                    </div>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $statusColor }}">
                        {{ $statusLabel }}
                    </span>
                </div>

                <!-- Resumo de Itens -->
                <div class="mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Resumo</h3>
                    <div class="space-y-2">
                        @foreach($order->items as $item)
                        <div class="flex justify-between text-sm">
                            <span>
                                {{ $item->quantity }}x 
                                @if(!$item->product_id && $item->custom_name)
                                    Item Avulso - {{ $item->custom_name }}
                                @elseif($item->custom_name)
                                    {{ $item->custom_name }}
                                @elseif($item->product)
                                    {{ $item->product->name }}
                                @else
                                    Produto
                                @endif
                            </span>
                            <span class="font-medium">R$ {{ number_format($item->total_price, 2, ',', '.') }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Descontos e Valores -->
                <div class="mb-4 space-y-1">
                    @if($order->coupon_code && $order->discount_amount > 0)
                    <div class="flex justify-between text-sm text-green-600">
                        <span>Cupom utilizado: <strong>{{ $order->coupon_code }}</strong></span>
                        <span>- R$ {{ number_format($order->discount_amount, 2, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($order->delivery_fee > 0)
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Taxa de Entrega:</span>
                        <span>R$ {{ number_format($order->delivery_fee, 2, ',', '.') }}</span>
                    </div>
                    @endif
                </div>

                <!-- Pagamento -->
                <div class="mb-4 pb-4 border-b">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">
                            Pagamentos:
                            <span class="font-medium">
                                {{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'Não informado')) }}
                            </span>
                        </span>
                        <span class="font-medium">R$ {{ number_format($order->final_amount ?? $order->total_amount ?? 0, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-base font-bold mt-2">
                        <span>Total:</span>
                        <span>R$ {{ number_format($order->final_amount ?? $order->total_amount ?? 0, 2, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="flex justify-end gap-2">
                    <a href="{{ route('customer.orders.show', ['order' => $order->order_number, 'phone' => request('phone')]) }}" 
                       class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Ver Detalhes
                    </a>
                    @if($isDelivered && !isset($ratings[$order->id]))
                    <button onclick="openRatingModal({{ $order->id }}, '{{ $order->order_number }}')" 
                            class="px-4 py-2 text-sm bg-pink-500 text-white rounded-lg hover:bg-pink-600 transition-colors flex items-center gap-2">
                        <i class="fas fa-star"></i>
                        Avaliar pedido
                    </button>
                    @elseif($isDelivered && isset($ratings[$order->id]))
                    <span class="px-4 py-2 text-sm bg-gray-100 text-gray-600 rounded-lg flex items-center gap-2">
                        <i class="fas fa-star text-yellow-500"></i>
                        Avaliado ({{ $ratings[$order->id] }} estrelas)
                    </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <i class="fas fa-shopping-bag text-6xl text-gray-300 mb-4"></i>
                <p class="text-xl font-semibold text-gray-700 mb-2">Nenhum pedido encontrado</p>
                <p class="text-gray-500">Você ainda não realizou nenhum pedido.</p>
            </div>
        @endforelse

        <!-- Paginação -->
        @if(method_exists($orders, 'links'))
        <div class="mt-6">
            {{ $orders->appends(request()->query())->links() }}
        </div>
        @endif
    </main>

    <!-- Modal de Avaliação -->
    <div id="rating-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Avaliar Pedido</h3>
                <button onclick="closeRatingModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="rating-form" method="POST" action="" class="space-y-4">
                @csrf
                <input type="hidden" name="phone" value="{{ request('phone') }}">
                <input type="hidden" name="email" value="{{ request('email') }}">
                
                <div>
                    <label class="block text-sm font-medium mb-2">Sua avaliação *</label>
                    <div class="flex gap-2" id="stars-container">
                        @for($i = 1; $i <= 5; $i++)
                        <button type="button" onclick="setRating({{ $i }})" class="star-button text-3xl text-gray-300 hover:text-yellow-400 transition-colors" data-rating="{{ $i }}">
                            <i class="fas fa-star"></i>
                        </button>
                        @endfor
                    </div>
                    <input type="hidden" name="rating" id="rating-value" required>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Comentário (opcional)</label>
                    <textarea name="comment" rows="4" maxlength="1000" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none"
                              placeholder="Conte-nos sua experiência com este pedido..."></textarea>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeRatingModal()" 
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors">
                        Enviar Avaliação
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t shadow-lg">
        <div class="max-w-4xl mx-auto px-4 py-3 flex justify-around">
            <a href="#" class="flex flex-col items-center text-gray-400">
                <i class="fas fa-bars text-xl mb-1"></i>
                <span class="text-xs">menu</span>
            </a>
            <a href="{{ route('customer.orders.index', ['phone' => request('phone')]) }}" 
               class="flex flex-col items-center text-orange-600">
                <i class="fas fa-shopping-bag text-xl mb-1"></i>
                <span class="text-xs">pedidos</span>
            </a>
        </div>
    </nav>

    <script>
        let currentRating = 0;
        let currentOrderId = null;

        function openRatingModal(orderId, orderNumber) {
            currentOrderId = orderId;
            document.getElementById('rating-form').action = "{{ route('customer.orders.rate', ':order') }}".replace(':order', orderNumber);
            document.getElementById('rating-modal').classList.remove('hidden');
            resetStars();
        }

        function closeRatingModal() {
            document.getElementById('rating-modal').classList.add('hidden');
            resetStars();
            document.getElementById('rating-value').value = '';
        }

        function setRating(rating) {
            currentRating = rating;
            document.getElementById('rating-value').value = rating;
            
            const stars = document.querySelectorAll('.star-button');
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.querySelector('i').classList.remove('text-gray-300');
                    star.querySelector('i').classList.add('text-yellow-400');
                } else {
                    star.querySelector('i').classList.remove('text-yellow-400');
                    star.querySelector('i').classList.add('text-gray-300');
                }
            });
        }

        function resetStars() {
            currentRating = 0;
            const stars = document.querySelectorAll('.star-button');
            stars.forEach(star => {
                star.querySelector('i').classList.remove('text-yellow-400');
                star.querySelector('i').classList.add('text-gray-300');
            });
        }

        // Fechar modal ao clicar fora
        document.getElementById('rating-modal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeRatingModal();
            }
        });
    </script>
</body>
</html>

