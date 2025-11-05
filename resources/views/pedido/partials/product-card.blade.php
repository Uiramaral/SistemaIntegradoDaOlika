@php
    $minVariantPrice = $product->variants()->where('is_active', true)->min('price');
    $hasActiveVariants = $minVariantPrice !== null;
    $displayPrice = ($product->price > 0) ? (float)$product->price : ((float)$minVariantPrice ?: 0);
    $isPurchasable = $displayPrice > 0;
    
    // Obter URLs otimizadas da imagem
    // Tamanhos baseados no tipo de exibição
    $thumbnailSize = match($displayType ?? 'grid') {
        'grid' => 'thumb', // 400x400
        'list_horizontal' => 'small', // 200x200
        'list_vertical' => 'small', // 200x200
        default => 'thumb'
    };
    
    $imageUrls = $product->getOptimizedImageUrls($thumbnailSize);
    
    // Dimensões baseadas no displayType
    $imgWidth = match($displayType ?? 'grid') {
        'grid' => 400,
        'list_horizontal' => 140,
        'list_vertical' => 96,
        default => 400
    };
    $imgHeight = $imgWidth; // Quadrado
    
    $displayType = $displayType ?? 'grid';
    $loadEager = $loadEager ?? false;
    $fetchPriority = $fetchPriority ?? 'auto';
@endphp

@if($isPurchasable)
@if($displayType === 'grid')
    <div class="product-item text-card-foreground group overflow-hidden border shadow-sm hover:shadow-xl transition-all duration-300 bg-card rounded-2xl cursor-pointer" data-category-id="{{ $product->category_id ?? '0' }}" onclick="openQuickView({{ $product->id }})">
        <div class="aspect-square overflow-hidden bg-muted relative">
            <div class="absolute inset-0 bg-gradient-to-br from-gray-200 via-gray-100 to-gray-200" id="placeholder-{{ $product->id }}">
                <div class="absolute inset-0 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path>
                    </svg>
                </div>
            </div>
            <picture>
                <source srcset="{{ $imageUrls['webp'] }}" type="image/webp">
                <img 
                    src="{{ $imageUrls['jpg'] }}" 
                    alt="{{ $product->name }}" 
                    loading="{{ $loadEager ? 'eager' : 'lazy' }}"
                    decoding="async"
                    fetchpriority="{{ $fetchPriority }}"
                    width="{{ $imgWidth }}"
                    height="{{ $imgHeight }}"
                    class="h-full w-full object-cover transition-opacity duration-200 group-hover:scale-110 pointer-events-none"
                    style="opacity: {{ $loadEager ? '0.5' : '0' }};"
                    onload="this.style.opacity='1'; const placeholder = document.getElementById('placeholder-{{ $product->id }}'); if(placeholder) placeholder.style.display='none';"
                    onloadstart="if(this.style.opacity === '0') this.style.opacity='0.3';"
                    onerror="this.onerror=null; this.src='{{ asset('images/produto-placeholder.jpg') }}'; this.style.opacity='1'; const placeholder = document.getElementById('placeholder-{{ $product->id }}'); if(placeholder) placeholder.style.display='none';"
                    data-product-id="{{ $product->id }}"
                >
            </picture>
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        </div>
        <div class="p-2.5 sm:p-3 flex flex-col gap-1.5">
            <h3 class="font-medium text-xs sm:text-sm line-clamp-2 leading-tight text-gray-900">{{ $product->name }}</h3>
            <div class="flex items-center justify-between mt-auto pt-1">
                <div class="flex flex-col">
                    @if($hasActiveVariants)
                        <span class="text-xs text-muted-foreground">A partir de</span>
                    @endif
                    <span class="text-sm sm:text-base font-bold text-primary">R$ {{ number_format($displayPrice, 2, ',', '.') }}</span>
                </div>
                @if($hasActiveVariants)
                    <button onclick="event.stopPropagation(); openQuickView({{ $product->id }})" class="inline-flex items-center justify-center gap-1 whitespace-nowrap text-xs font-medium bg-primary text-white hover:bg-primary/90 rounded-lg h-8 w-8 sm:h-9 sm:w-9 shadow-sm hover:shadow transition-all duration-200 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14"></path>
                            <path d="M12 5v14"></path>
                        </svg>
                    </button>
                @else
                    <button onclick="event.stopPropagation(); addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $displayPrice }})" class="inline-flex items-center justify-center gap-1 whitespace-nowrap text-xs font-medium bg-primary text-white hover:bg-primary/90 rounded-lg h-8 w-8 sm:h-9 sm:w-9 shadow-sm hover:shadow transition-all duration-200 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14"></path>
                            <path d="M12 5v14"></path>
                        </svg>
                    </button>
                @endif
            </div>
        </div>
    </div>

@elseif($displayType === 'list_horizontal')
    <div class="text-card-foreground group overflow-hidden border shadow-sm hover:shadow-xl transition-all duration-300 bg-card rounded-2xl flex flex-col cursor-pointer" style="min-width: 140px; max-width: 140px; flex-shrink: 0;" onclick="openQuickView({{ $product->id }})">
        <div class="aspect-square overflow-hidden bg-muted relative">
            <div class="absolute inset-0 bg-gradient-to-br from-gray-200 via-gray-100 to-gray-200" id="placeholder-h-{{ $product->id }}">
                <div class="absolute inset-0 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path>
                    </svg>
                </div>
            </div>
            <picture>
                <source srcset="{{ $imageUrls['webp'] }}" type="image/webp">
                <img 
                    src="{{ $imageUrls['jpg'] }}" 
                    alt="{{ $product->name }}" 
                    loading="{{ $loadEager ? 'eager' : 'lazy' }}"
                    decoding="async"
                    fetchpriority="{{ $fetchPriority }}"
                    width="{{ $imgWidth }}"
                    height="{{ $imgHeight }}"
                    class="h-full w-full object-cover transition-opacity duration-200 group-hover:scale-110 pointer-events-none"
                    style="opacity: {{ $loadEager ? '0.5' : '0' }};"
                    onload="this.style.opacity='1'; const placeholder = document.getElementById('placeholder-h-{{ $product->id }}'); if(placeholder) placeholder.style.display='none';"
                    onloadstart="if(this.style.opacity === '0') this.style.opacity='0.3';"
                    onerror="this.onerror=null; this.src='{{ asset('images/produto-placeholder.jpg') }}'; this.style.opacity='1'; const placeholder = document.getElementById('placeholder-h-{{ $product->id }}'); if(placeholder) placeholder.style.display='none';"
                    data-product-id="h-{{ $product->id }}"
                >
            </picture>
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        </div>
        <div class="p-2.5 flex flex-col gap-1.5">
            <h3 class="font-medium text-xs line-clamp-2 leading-tight text-gray-900">{{ $product->name }}</h3>
            <div class="flex items-center justify-between mt-auto pt-1">
                <div class="flex flex-col">
                    @if($hasActiveVariants)
                        <span class="text-[10px] text-muted-foreground">A partir de</span>
                    @endif
                    <span class="text-xs font-bold text-primary">R$ {{ number_format($displayPrice, 2, ',', '.') }}</span>
                </div>
                @if($hasActiveVariants)
                    <button onclick="event.stopPropagation(); openQuickView({{ $product->id }})" class="inline-flex items-center justify-center gap-1 whitespace-nowrap text-xs font-medium bg-primary text-white hover:bg-primary/90 rounded-lg h-7 w-7 shadow-sm hover:shadow transition-all duration-200 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14"></path>
                            <path d="M12 5v14"></path>
                        </svg>
                    </button>
                @else
                    <button onclick="event.stopPropagation(); addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $displayPrice }})" class="inline-flex items-center justify-center gap-1 whitespace-nowrap text-xs font-medium bg-primary text-white hover:bg-primary/90 rounded-lg h-7 w-7 shadow-sm hover:shadow transition-all duration-200 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14"></path>
                            <path d="M12 5v14"></path>
                        </svg>
                    </button>
                @endif
            </div>
        </div>
    </div>

@elseif($displayType === 'list_vertical')
    <div class="text-card-foreground group overflow-hidden border shadow-sm hover:shadow-xl transition-all duration-300 bg-card rounded-2xl flex flex-row gap-4 cursor-pointer" onclick="openQuickView({{ $product->id }})">
        <div class="w-24 h-24 flex-shrink-0 overflow-hidden bg-muted relative rounded-lg">
            <div class="absolute inset-0 bg-gradient-to-br from-gray-200 via-gray-100 to-gray-200" id="placeholder-v-{{ $product->id }}">
                <div class="absolute inset-0 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path>
                    </svg>
                </div>
            </div>
            <picture>
                <source srcset="{{ $imageUrls['webp'] }}" type="image/webp">
                <img 
                    src="{{ $imageUrls['jpg'] }}" 
                    alt="{{ $product->name }}" 
                    loading="{{ $loadEager ? 'eager' : 'lazy' }}"
                    decoding="async"
                    fetchpriority="{{ $fetchPriority }}"
                    width="{{ $imgWidth }}"
                    height="{{ $imgHeight }}"
                    class="h-full w-full object-cover transition-opacity duration-200 group-hover:scale-110 pointer-events-none"
                    style="opacity: {{ $loadEager ? '0.5' : '0' }};"
                    onload="this.style.opacity='1'; const placeholder = document.getElementById('placeholder-v-{{ $product->id }}'); if(placeholder) placeholder.style.display='none';"
                    onloadstart="if(this.style.opacity === '0') this.style.opacity='0.3';"
                    onerror="this.onerror=null; this.src='{{ asset('images/produto-placeholder.jpg') }}'; this.style.opacity='1'; const placeholder = document.getElementById('placeholder-v-{{ $product->id }}'); if(placeholder) placeholder.style.display='none';"
                    data-product-id="v-{{ $product->id }}"
                >
            </picture>
        </div>
        <div class="flex-1 flex flex-col justify-between py-3">
            <div>
                <h3 class="font-medium text-sm sm:text-base leading-tight text-gray-900 mb-1">{{ $product->name }}</h3>
                @if($product->description)
                    <p class="text-xs text-muted-foreground line-clamp-2">{{ $product->description }}</p>
                @endif
            </div>
            <div class="flex items-center justify-between mt-auto">
                <div class="flex flex-col">
                    @if($hasActiveVariants)
                        <span class="text-xs text-muted-foreground">A partir de</span>
                    @endif
                    <span class="text-base font-bold text-primary">R$ {{ number_format($displayPrice, 2, ',', '.') }}</span>
                </div>
                @if($hasActiveVariants)
                    <button onclick="event.stopPropagation(); openQuickView({{ $product->id }})" class="inline-flex items-center justify-center gap-1 whitespace-nowrap text-xs font-medium bg-primary text-white hover:bg-primary/90 rounded-lg h-9 w-9 shadow-sm hover:shadow transition-all duration-200 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14"></path>
                            <path d="M12 5v14"></path>
                        </svg>
                    </button>
                @else
                    <button onclick="event.stopPropagation(); addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $displayPrice }})" class="inline-flex items-center justify-center gap-1 whitespace-nowrap text-xs font-medium bg-primary text-white hover:bg-primary/90 rounded-lg h-9 w-9 shadow-sm hover:shadow transition-all duration-200 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14"></path>
                            <path d="M12 5v14"></path>
                        </svg>
                    </button>
                @endif
            </div>
                 </div>
     </div>
@endif
@endif
