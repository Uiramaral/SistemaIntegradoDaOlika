<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido {{ $order->order_number }} - OLIKA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary: 24 95% 53%;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b sticky top-0 z-10">
        <div class="max-w-4xl mx-auto px-4 py-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('customer.orders.index', ['phone' => request('phone')]) }}" 
                   class="text-gray-600 hover:text-orange-600">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-orange-600">Pedido {{ $order->order_number }}</h1>
                    <p class="text-xs text-gray-600">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </header>

    <!-- Conteúdo -->
    <main class="max-w-4xl mx-auto px-4 py-6 pb-24">
        @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
            {{ session('success') }}
        </div>
        @endif

        <!-- Status do Pedido -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-4">
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
                    'pending' => 'bg-yellow-100 text-yellow-800',
                ];
                $statusLabel = $statusLabels[$order->status] ?? ucfirst($order->status);
                $statusColor = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800';
            @endphp
            <div class="flex items-center justify-between">
                <span class="px-4 py-2 rounded-full text-sm font-semibold {{ $statusColor }}">
                    {{ $statusLabel }}
                </span>
                @if($order->status === 'delivered' && !$rating)
                <button onclick="openRatingModal('{{ $order->order_number }}')" 
                        class="px-4 py-2 text-sm bg-pink-500 text-white rounded-lg hover:bg-pink-600 transition-colors flex items-center gap-2">
                    <i class="fas fa-star"></i>
                    Avaliar pedido
                </button>
                @elseif($rating)
                <span class="px-4 py-2 text-sm bg-gray-100 text-gray-600 rounded-lg flex items-center gap-2">
                    <i class="fas fa-star text-yellow-500"></i>
                    Avaliado ({{ $rating->rating }} estrelas)
                </span>
                @endif
            </div>
        </div>

        <!-- Itens do Pedido -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-4">
            <h2 class="text-lg font-semibold mb-4">Itens do Pedido</h2>
            <div class="space-y-3">
                @foreach($order->items as $item)
                <div class="flex justify-between pb-3 border-b last:border-0">
                    <div>
                        <p class="font-medium">
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
                        </p>
                        @if($item->special_instructions)
                        <p class="text-xs text-gray-500 mt-1">Obs: {{ $item->special_instructions }}</p>
                        @endif
                        <p class="text-xs text-gray-500">R$ {{ number_format($item->unit_price, 2, ',', '.') }} cada</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold">R$ {{ number_format($item->total_price, 2, ',', '.') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Resumo Financeiro -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-4">
            <h2 class="text-lg font-semibold mb-4">Resumo</h2>
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Subtotal:</span>
                    <span>R$ {{ number_format($order->total_amount ?? 0, 2, ',', '.') }}</span>
                </div>
                @if($order->delivery_fee > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Taxa de Entrega:</span>
                    <span>R$ {{ number_format($order->delivery_fee, 2, ',', '.') }}</span>
                </div>
                @endif
                @if($order->discount_amount > 0)
                <div class="flex justify-between text-sm text-green-600">
                    <span>
                        Desconto
                        @if($order->coupon_code)
                            ({{ $order->coupon_code }})
                        @endif
                        :
                    </span>
                    <span>- R$ {{ number_format($order->discount_amount, 2, ',', '.') }}</span>
                </div>
                @endif
                <div class="flex justify-between text-lg font-bold pt-2 border-t">
                    <span>Total:</span>
                    <span class="text-orange-600">R$ {{ number_format($order->final_amount ?? $order->total_amount ?? 0, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Informações Adicionais -->
        @if($order->delivery_address || $order->notes || $order->delivery_instructions)
        <div class="bg-white rounded-lg shadow-md p-6 mb-4">
            <h2 class="text-lg font-semibold mb-4">Informações Adicionais</h2>
            @if($order->delivery_address)
            <div class="mb-3">
                <p class="text-sm font-medium text-gray-700 mb-1">Endereço de Entrega:</p>
                <p class="text-sm text-gray-600">{{ $order->delivery_address }}</p>
            </div>
            @endif
            @if($order->notes)
            <div class="mb-3">
                <p class="text-sm font-medium text-gray-700 mb-1">Observações:</p>
                <p class="text-sm text-gray-600">{{ $order->notes }}</p>
            </div>
            @endif
            @if($order->delivery_instructions)
            <div>
                <p class="text-sm font-medium text-gray-700 mb-1">Instruções de Entrega:</p>
                <p class="text-sm text-gray-600">{{ $order->delivery_instructions }}</p>
            </div>
            @endif
        </div>
        @endif

        <!-- Histórico -->
        @if($statusHistory && $statusHistory->count() > 0)
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold mb-4">Histórico</h2>
            <div class="space-y-3">
                @foreach($statusHistory as $history)
                <div class="flex items-start gap-3">
                    <div class="w-2 h-2 bg-orange-600 rounded-full mt-2"></div>
                    <div>
                        <p class="text-sm font-medium">{{ \Carbon\Carbon::parse($history->created_at)->format('d/m/Y H:i') }}</p>
                        <p class="text-xs text-gray-600">
                            @if($history->old_status)
                                Status alterado de <strong>{{ ucfirst($history->old_status) }}</strong> para <strong>{{ ucfirst($history->new_status) }}</strong>
                            @else
                                Status definido como <strong>{{ ucfirst($history->new_status) }}</strong>
                            @endif
                            @if($history->note)
                                - {{ $history->note }}
                            @endif
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
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

            <form method="POST" action="{{ route('customer.orders.rate', $order->order_number) }}" class="space-y-4">
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
                              placeholder="Conte-nos sua experiência com este pedido...">{{ $rating->comment ?? '' }}</textarea>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeRatingModal()" 
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors">
                        {{ $rating ? 'Atualizar Avaliação' : 'Enviar Avaliação' }}
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        @if($rating)
        let currentRating = {{ $rating->rating }};
        @else
        let currentRating = 0;
        @endif

        function openRatingModal(orderNumber) {
            document.getElementById('rating-modal').classList.remove('hidden');
            @if($rating)
            setRating({{ $rating->rating }});
            @endif
        }

        function closeRatingModal() {
            document.getElementById('rating-modal').classList.add('hidden');
            @if(!$rating)
            resetStars();
            @endif
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

        document.getElementById('rating-modal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeRatingModal();
            }
        });

        @if($rating)
        // Inicializar estrelas com a avaliação existente
        setRating({{ $rating->rating }});
        @endif
    </script>
</body>
</html>

