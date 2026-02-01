@php
    $img = $product->image_url;
    if (!$img && $product->cover_image) {
        $img = asset('storage/' . $product->cover_image);
    } elseif (!$img && $product->images && $product->images->count() > 0) {
        $img = asset('storage/' . $product->images->first()->path);
    }
    $img = $img ?? asset('images/produto-placeholder.jpg');

    $variantsActive = $product->variants()->where('is_active', true)->orderBy('sort_order')->get();
    $initialPrice = ($variantsActive->count() > 0) ? (float) optional($variantsActive->first())->price : (float) $product->price;
@endphp

<div
    class="bg-card w-full max-w-5xl rounded-xl overflow-hidden shadow-xl flex flex-col md:flex-row max-h-[90dvh] md:max-h-[85vh] w-[95%] sm:w-full">
    <!-- Image Section -->
    <div class="w-full md:w-5/12 h-48 md:h-auto relative bg-muted shrink-0">
        <button onclick="closeProductModal()"
            class="absolute top-4 right-4 z-20 md:hidden p-2 bg-black/20 hover:bg-black/40 text-white rounded-full backdrop-blur-sm transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 6 6 18" />
                <path d="m6 6 12 12" />
            </svg>
        </button>

        <img src="{{ $img }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent md:hidden"></div>
    </div>

    <!-- Content Section -->
    <div class="w-full md:w-7/12 flex flex-col flex-1 min-h-0 bg-card">
        <!-- Header (Sticky on scroll) -->
        <div class="p-6 pb-2 shrink-0 relative">
            <button onclick="closeProductModal()"
                class="absolute top-4 right-4 hidden md:flex p-2 hover:bg-muted text-muted-foreground hover:text-foreground rounded-full transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6 6 18" />
                    <path d="m6 6 12 12" />
                </svg>
            </button>

            @if($product->category)
                <div class="text-sm text-muted-foreground mb-1">{{ $product->category->name }}</div>
            @endif
            <h2 class="text-2xl font-bold font-serif text-foreground leading-tight mb-2">{{ $product->name }}</h2>
            <p class="text-xl font-bold text-primary" id="modalPrice">R$ {{ number_format($initialPrice, 2, ',', '.') }}
            </p>
        </div>

        <!-- Scrollable Content -->
        <div class="p-6 pt-2 flex-1 overflow-y-auto custom-scrollbar">
            @if($product->description || ($product->ingredients ?? null))
                <div class="mb-6 space-y-4 text-sm text-muted-foreground">
                    @if($product->description)
                        <p>{{ $product->description }}</p>
                    @endif
                    @if($product->ingredients ?? null)
                        <div>
                            <span class="font-semibold text-foreground">Ingredientes:</span>
                            {{ $product->ingredients }}
                        </div>
                    @endif
                </div>
            @endif

            <form id="addToCartForm" onsubmit="event.preventDefault(); submitModalCart();">
                <!-- Variants -->
                @if($variantsActive->count() > 0)
                    <div class="mb-6">
                        <div class="flex items-center gap-2 mb-3">
                            <label class="block text-sm font-medium text-foreground">Escolha uma opção</label>
                            <span
                                class="inline-flex items-center rounded-full bg-primary/10 text-primary text-[10px] font-bold px-2 py-0.5 uppercase tracking-wide">Obrigatório</span>
                        </div>
                        <div class="space-y-2">
                            @foreach($variantsActive as $index => $v)
                                <label
                                    class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-accent/50 transition-all {{ $index === 0 ? 'border-primary ring-1 ring-primary bg-primary/5' : 'border-border' }} variant-option">
                                    <input type="radio" name="modal_variant_id" value="{{ $v->id }}"
                                        data-price="{{ (float) $v->price }}" class="sr-only" {{ $index === 0 ? 'checked' : '' }}
                                        onchange="updateModalPrice(this)" required>
                                    <div
                                        class="flex items-center justify-center w-5 h-5 rounded-full border border-primary text-primary opacity-0 scale-50 transition-all check-circle">
                                        <div class="w-2.5 h-2.5 rounded-full bg-current"></div>
                                    </div>
                                    <span class="flex-1 text-sm font-medium text-foreground">{{ $v->name }}</span>
                                    <span class="text-sm font-semibold text-primary">R$
                                        {{ number_format((float) $v->price, 2, ',', '.') }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Allergens -->
                @if($product->allergens)
                    <div
                        class="mb-6 p-3 bg-red-50 dark:bg-red-900/10 rounded-lg border border-red-100 dark:border-red-900/20">
                        <h3 class="font-semibold text-xs text-red-800 dark:text-red-400 mb-1 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="12" x2="12" y1="8" y2="12" />
                                <line x1="12" x2="12.01" y1="16" y2="16" />
                            </svg>
                            Alérgenos
                        </h3>
                        <p class="text-xs text-red-600 dark:text-red-300">{{ $product->allergens }}</p>
                    </div>
                @endif

                <!-- Observation -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-foreground mb-2">Observações</label>
                    <textarea id="modalObservation"
                        class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        placeholder="Ex: Tirar cebola, ponto da carne, etc..."></textarea>
                </div>
            </form>
        </div>

        <!-- Footer (Actions) -->
        <div class="p-6 pb-10 mb-1 border-t bg-muted/20 shrink-0">
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center border rounded-md bg-background">
                        <button type="button" onclick="changeModalQty(-1)"
                            class="p-3 hover:bg-accent hover:text-accent-foreground disabled:opacity-50 transition-colors rounded-l-md">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M5 12h14" />
                            </svg>
                        </button>
                        <span class="w-12 text-center font-semibold text-lg" id="modalQty">1</span>
                        <button type="button" onclick="changeModalQty(1)"
                            class="p-3 hover:bg-accent hover:text-accent-foreground transition-colors rounded-r-md">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M5 12h14" />
                                <path d="M12 5v14" />
                            </svg>
                        </button>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-muted-foreground block">Total</span>
                        <span class="text-xl font-bold text-primary" id="modalTotalPrice">R$
                            {{ number_format($initialPrice, 2, ',', '.') }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 w-full">
                    <button onclick="submitModalCart(false)"
                        class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-primary text-primary hover:bg-primary/5 h-12 w-full">
                        Adicionar +
                    </button>
                    <button onclick="submitModalCart(true)"
                        class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-12 w-full shadow-lg shadow-primary/20">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14" />
                            <path d="M12 5v14" />
                        </svg>
                        Pedir agora
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom Style for Variant Selection */
    .variant-option input:checked~.check-circle {
        opacity: 1;
        transform: scale(1);
    }

    .check-circle {
        margin-right: -0.5rem;
        /* Ajuste visual */
    }

    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: rgba(156, 163, 175, 0.5);
        border-radius: 20px;
    }
</style>