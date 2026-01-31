@extends('pedido.layout')

@section('title', 'Olika - Pães Artesanais | Cardápio Digital')

@section('content')
@php
    $routeName = \Illuminate\Support\Facades\Request::route() ? \Illuminate\Support\Facades\Request::route()->getName() : '';
    $isStore = str_starts_with($routeName, 'store.');
    
    $rMenu = $isStore ? 'store.menu' : 'pedido.menu';
    $rCart = $isStore ? 'store.cart' : 'pedido.cart';
    $rIndex = $isStore ? 'store.index' : 'pedido.index';

    $cartCount = session('cart_count', 0);
    $cart = session('cart', []);
    if (empty($cartCount) && !empty($cart)) {
        $cartCount = array_sum(array_column($cart, 'qty'));
    }
    
    // Determinar categoria ativa
    $activeCategoryId = null; // "Todos" por padrão
    $categoryTitle = 'Todos';
    $productsCount = isset($products) ? $products->count() : 0;
    
    // Preload das primeiras 20 imagens (above the fold e próximas) usando URLs otimizadas
    $preloadCount = 0;
    $preloadLimit = 20;
@endphp

<!-- Preload das primeiras imagens para carregamento mais rápido -->
@if(isset($categories) && $categories->count() > 0)
    @foreach($categories as $category)
        @if(isset($category->products) && $category->products->count() > 0 && $preloadCount < $preloadLimit)
            @php
                $displayType = $category->display_type ?? 'grid';
                $thumbnailSize = match($displayType) {
                    'grid' => 'thumb',
                    'list_horizontal' => 'small',
                    'list_vertical' => 'small',
                    default => 'thumb'
                };
            @endphp
            @foreach($category->products->take($preloadLimit - $preloadCount) as $product)
                @php
                    $imageUrls = $product->getOptimizedImageUrls($thumbnailSize);
                    $img = $imageUrls['webp'] ?? $imageUrls['jpg'] ?? asset('images/produto-placeholder.jpg');
                    $preloadCount++;
                @endphp
                <link rel="preload" as="image" href="{{ $img }}" fetchpriority="{{ $preloadCount <= 12 ? 'high' : 'auto' }}" type="{{ strpos($img, '.webp') !== false ? 'image/webp' : 'image/jpeg' }}">
            @endforeach
        @endif
        @break($preloadCount >= $preloadLimit)
    @endforeach
@endif

<!-- Produtos separados por categoria -->
@if(isset($categories) && $categories->count() > 0)
    @foreach($categories as $category)
            @if(isset($category->products) && $category->products->count() > 0)
            @php
                $displayType = $category->display_type ?? 'grid';
                $categoryId = is_string($category->id) ? $category->id : $category->id;
                // Marcar primeiras imagens como eager (até 12 imagens visíveis)
                static $totalProductIndex = 0;
            @endphp
            
            <div class="mb-12 category-section" data-category-id="{{ $categoryId }}">
                @php
                    // Contador global de produtos para determinar quais imagens carregar eager
                    if (!isset($globalProductIndex)) {
                        $globalProductIndex = 0;
                    }
                @endphp
                @if($displayType === 'grid')
                    <!-- Título e contagem de produtos (estilo do site de referência) -->
                    <div class="mb-6">
                        <h2 class="text-3xl font-serif font-bold text-foreground">{{ $category->name }}</h2>
                        <p class="text-muted-foreground mt-1">{{ $category->products->count() }} {{ $category->products->count() === 1 ? 'produto' : 'produtos' }}</p>
                    </div>
                    <!-- Grid (Grade) - Layout pixel-perfect do site de referência -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 animate-fade-in">
                        @foreach($category->products as $product)
                            @php
                                $shouldLoadEager = $globalProductIndex < 12;
                                $fetchPriority = $globalProductIndex < 6 ? 'high' : ($globalProductIndex < 12 ? 'auto' : 'low');
                                $globalProductIndex++;
                            @endphp
                            @include('pedido.partials.product-card', [
                                'product' => $product, 
                                'displayType' => 'grid',
                                'loadEager' => $shouldLoadEager,
                                'fetchPriority' => $fetchPriority
                            ])
                        @endforeach
                    </div>
                
                @elseif($displayType === 'list_horizontal')
                    <!-- Lista Horizontal (Rolagem) -->
                    <div class="horizontal-scroll-container overflow-x-auto scrollbar-hide pb-4 -mx-4 px-4" 
                         data-category-id="{{ $categoryId }}" 
                         id="horizontal-scroll-{{ $categoryId }}">
                        <div class="flex gap-4 horizontal-scroll-content" style="width: max-content;">
                            @foreach($category->products as $product)
                                @php
                                    $shouldLoadEager = $globalProductIndex < 12;
                                    $fetchPriority = $globalProductIndex < 6 ? 'high' : ($globalProductIndex < 12 ? 'auto' : 'low');
                                    $globalProductIndex++;
                                @endphp
                                @include('pedido.partials.product-card', [
                                    'product' => $product, 
                                    'displayType' => 'list_horizontal',
                                    'loadEager' => $shouldLoadEager,
                                    'fetchPriority' => $fetchPriority
                                ])
                            @endforeach
                        </div>
                    </div>
                
                @elseif($displayType === 'list_vertical')
                    <!-- Lista Vertical -->
                    <div class="space-y-4">
                        @foreach($category->products as $product)
                            @php
                                $shouldLoadEager = $globalProductIndex < 12;
                                $fetchPriority = $globalProductIndex < 6 ? 'high' : ($globalProductIndex < 12 ? 'auto' : 'low');
                                $globalProductIndex++;
                            @endphp
                            @include('pedido.partials.product-card', [
                                'product' => $product, 
                                'displayType' => 'list_vertical',
                                'loadEager' => $shouldLoadEager,
                                'fetchPriority' => $fetchPriority
                            ])
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    @endforeach
@endif

<!-- Product Modal Container -->
<div id="productModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/60 backdrop-blur-[2px] transition-opacity opacity-0" id="productModalBackdrop" onclick="closeProductModal()"></div>
    
    <!-- Modal Panel Container -->
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 pointer-events-none">
        <!-- Modal Panel (Panel itself listens to clicks, so propagation stop is needed if clicking inside shouldn't close) -->
        <!-- But the panel content is loaded via AJAX. We set pointer-events-auto on the panel. -->
        <div id="productModalPanel" class="w-full max-w-4xl bg-transparent transition-all transform scale-95 opacity-0 pointer-events-auto flex items-center justify-center">
            <div id="productModalBody" class="w-full shadow-2xl rounded-xl overflow-hidden bg-card">
                <!-- Content will be injected here -->
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Otimização de carregamento de imagens com Intersection Observer
document.addEventListener('DOMContentLoaded', function() {
    // Forçar carregamento imediato das imagens eager (marcadas no PHP)
    const eagerImages = document.querySelectorAll('img[loading="eager"]');
    eagerImages.forEach((img) => {
        // Se já está carregada, mostrar imediatamente
        if (img.complete && img.naturalWidth > 0) {
            img.style.opacity = '1';
            const productId = img.getAttribute('data-product-id');
            const placeholder = document.getElementById('placeholder-' + productId) || 
                              document.getElementById('placeholder-h-' + productId) || 
                              document.getElementById('placeholder-v-' + productId);
            if (placeholder) {
                placeholder.style.display = 'none';
            }
        } else {
            // Preload agressivo para imagens eager
            const tempImg = new Image();
            tempImg.onload = function() {
                if (img.src) {
                    img.style.opacity = '1';
                    const productId = img.getAttribute('data-product-id');
                    const placeholder = document.getElementById('placeholder-' + productId) || 
                                      document.getElementById('placeholder-h-' + productId) || 
                                      document.getElementById('placeholder-v-' + productId);
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                }
            };
            // Usar source do picture se disponível
            const picture = img.closest('picture');
            if (picture) {
                const source = picture.querySelector('source[type="image/webp"]');
                if (source && source.srcset) {
                    tempImg.src = source.srcset;
                } else {
                    tempImg.src = img.src;
                }
            } else {
                tempImg.src = img.src;
            }
        }
    });
    
    // Intersection Observer para imagens lazy restantes
    const lazyImages = document.querySelectorAll('img[loading="lazy"]');
    
    if (lazyImages.length > 0 && 'IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    
                    // Pré-carregar imagem se ainda não estiver carregada
                    if (!img.complete && img.src) {
                        const tempImg = new Image();
                        tempImg.onload = function() {
                            img.style.opacity = '1';
                            const productId = img.getAttribute('data-product-id');
                            const placeholder = document.getElementById('placeholder-' + productId) || 
                                              document.getElementById('placeholder-h-' + productId) || 
                                              document.getElementById('placeholder-v-' + productId);
                            if (placeholder) {
                                placeholder.style.display = 'none';
                            }
                        };
                        tempImg.onerror = function() {
                            // Mesmo em erro, mostrar placeholder removido
                            img.style.opacity = '1';
                        };
                        tempImg.src = img.src;
                    } else if (img.complete) {
                        // Se já carregou, mostrar imediatamente
                        img.style.opacity = '1';
                        const productId = img.getAttribute('data-product-id');
                        const placeholder = document.getElementById('placeholder-' + productId) || 
                                          document.getElementById('placeholder-h-' + productId) || 
                                          document.getElementById('placeholder-v-' + productId);
                        if (placeholder) {
                            placeholder.style.display = 'none';
                        }
                    }
                    
                    // Parar de observar esta imagem
                    observer.unobserve(img);
                }
            });
        }, {
            // Carregar quando estiver a 800px da viewport (carregar bem antes de aparecer)
            rootMargin: '800px 0px',
            threshold: 0.01
        });
        
        lazyImages.forEach(img => {
            if (img.loading === 'lazy') {
                const rect = img.getBoundingClientRect();
                // Se já está muito próxima, não observar (já será carregada)
                if (rect.top > viewportHeight * 2) {
                    imageObserver.observe(img);
                }
            }
        });
    }
    
    // Prefetch agressivo das próximas imagens ao fazer scroll
    let lastScrollTop = 0;
    let scrollTimeout;
    const prefetchedUrls = new Set();
    
    function prefetchUpcomingImages() {
        const viewportHeight = window.innerHeight;
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const scrollDirection = scrollTop > lastScrollTop ? 'down' : 'up';
        lastScrollTop = scrollTop;
        
        // Prefetch imagens próximas ao scroll (até 20 imagens)
        const upcomingImages = document.querySelectorAll('img[loading="lazy"]');
        let prefetchCount = 0;
        const maxPrefetch = 20;
        
        upcomingImages.forEach((img) => {
            if (prefetchCount >= maxPrefetch) return;
            
            if (img.src && !prefetchedUrls.has(img.src)) {
                const rect = img.getBoundingClientRect();
                const distance = Math.abs(rect.top - (scrollTop + viewportHeight));
                
                // Prefetch se estiver dentro de 2x viewport height
                if (distance < viewportHeight * 2) {
                    // Criar link prefetch
                    const link = document.createElement('link');
                    link.rel = 'prefetch';
                    link.as = 'image';
                    link.href = img.src;
                    
                    // Verificar se já existe
                    if (!document.querySelector(`link[href="${img.src}"]`)) {
                        document.head.appendChild(link);
                        prefetchedUrls.add(img.src);
                        prefetchCount++;
                    }
                }
            }
        });
    }
    
    window.addEventListener('scroll', function() {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(prefetchUpcomingImages, 50); // Reduzido para 50ms
    }, { passive: true });
    
    // Prefetch inicial após 100ms
    setTimeout(prefetchUpcomingImages, 100);
});

// Auto-scroll para listas horizontais - VERSÃO MELHORADA COM LOOP CONTÍNUO
document.addEventListener('DOMContentLoaded', function() {
    const horizontalScrolls = document.querySelectorAll('.horizontal-scroll-container');
    
    horizontalScrolls.forEach(container => {
        const content = container.querySelector('.horizontal-scroll-content');
        if (!content) return;
        
        // Verificar se há conteúdo suficiente para scrollar
        const originalWidth = content.scrollWidth;
        const hasOverflow = originalWidth > container.clientWidth;
        if (!hasOverflow) return;
        
        // Identificar se é a categoria "Novidades" (loop contínuo ida e volta)
        const categoryId = container.getAttribute('data-category-id');
        const parentCategoryId = container.closest('[data-category-id]')?.getAttribute('data-category-id');
        const isNovidades = categoryId === 'novidades' || parentCategoryId === 'novidades';
        
        // Clonar conteúdo para criar loop infinito (sempre necessário)
        const clonedContent = content.cloneNode(true);
        content.appendChild(clonedContent);
        
        // Calcular dimensões após clonagem
        const itemCount = Math.floor(content.children.length / 2); // Dividido por 2 porque clonamos
        const totalWidth = originalWidth;
        const halfWidth = totalWidth;
        
        // Variáveis de controle
        let position = 0;
        let direction = 1; // 1 = esquerda, -1 = direita (para Novidades: vai e volta)
        let isPaused = false;
        let isDragging = false;
        let dragStartX = 0;
        let dragOffset = 0;
        let resumeTimeout = null;
        let rafId = null;
        const speed = isNovidades ? 0.6 : 0.8; // Mais lento para Novidades
        
        // Configurar container e content para usar transform
        container.style.overflow = 'hidden';
        container.style.position = 'relative';
        container.style.cursor = 'grab';
        content.style.display = 'flex';
        content.style.willChange = 'transform';
        content.style.transition = 'none';
        
        // Função de animação com transform (GPU-accelerated)
        function animate() {
            if (isPaused || isDragging) {
                rafId = requestAnimationFrame(animate);
                return;
            }
            
            // Atualizar posição
            position += direction * speed;
            
            // Lógica de loop baseada no tipo
            if (isNovidades) {
                // Para Novidades: vai e volta (ping-pong)
                if (position >= halfWidth - 100) {
                    direction = -1; // Inverter direção (voltar)
                } else if (position <= 0) {
                    direction = 1; // Inverter direção (ir)
                }
            } else {
                // Para outras categorias: loop contínuo unidirecional
                if (position >= halfWidth) {
                    position = 0; // Reset invisível ao início
                }
            }
            
            // Aplicar transform
            content.style.transform = `translateX(-${position}px)`;
            rafId = requestAnimationFrame(animate);
        }
        
        // Pausar animação
        function pause() {
            isPaused = true;
            if (resumeTimeout) {
                clearTimeout(resumeTimeout);
                resumeTimeout = null;
            }
        }
        
        // Retomar animação
        function scheduleResume() {
            if (resumeTimeout) clearTimeout(resumeTimeout);
            resumeTimeout = setTimeout(() => {
                isPaused = false;
                if (!rafId) {
                    rafId = requestAnimationFrame(animate);
                }
            }, isDragging ? 1500 : 800);
        }
        
        // Eventos de drag/toque otimizados
        let lastTouchX = 0;
        let lastMoveTime = 0;
        
        function handleStart(e) {
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            isDragging = true;
            dragStartX = clientX;
            lastTouchX = clientX;
            lastMoveTime = Date.now();
            pause();
            content.style.transition = 'none';
            container.style.cursor = 'grabbing';
        }
        
        function handleMove(e) {
            if (!isDragging) return;
            
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const deltaX = dragStartX - clientX;
            dragOffset = deltaX;
            
            // Aplicar transform durante drag
            const newPosition = Math.max(0, Math.min(position + deltaX, halfWidth));
            content.style.transform = `translateX(-${newPosition}px)`;
            
            lastTouchX = clientX;
            lastMoveTime = Date.now();
        }
        
        function handleEnd(e) {
            if (!isDragging) return;
            
            // Calcular nova posição baseada no drag
            const finalDelta = dragStartX - (e.changedTouches ? e.changedTouches[0].clientX : e.clientX);
            position = Math.max(0, Math.min(position + finalDelta, halfWidth));
            
            // Reset para dentro dos limites se necessário
            if (position >= halfWidth) {
                position = 0;
            } else if (position < 0) {
                position = 0;
            }
            
            // Resetar estado
            isDragging = false;
            dragOffset = 0;
            content.style.transition = '';
            container.style.cursor = '';
            
            scheduleResume();
        }
        
        // Adicionar event listeners (touch e mouse)
        container.addEventListener('touchstart', handleStart, { passive: true });
        container.addEventListener('touchmove', handleMove, { passive: true });
        container.addEventListener('touchend', handleEnd, { passive: true });
        container.addEventListener('touchcancel', handleEnd, { passive: true });
        
        container.addEventListener('mousedown', handleStart);
        container.addEventListener('mousemove', handleMove);
        container.addEventListener('mouseup', handleEnd);
        container.addEventListener('mouseleave', handleEnd);
        
        // Pausar ao sair da viewport (Intersection Observer)
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    if (!isPaused && !isDragging && !rafId) {
                        rafId = requestAnimationFrame(animate);
                    }
                } else {
                    if (rafId) {
                        cancelAnimationFrame(rafId);
                        rafId = null;
                    }
                }
            });
        }, { threshold: 0.1 });
        
        observer.observe(container);
        
        // Iniciar animação
        rafId = requestAnimationFrame(animate);
    });
});

// Popular sidebar com categorias
document.addEventListener('DOMContentLoaded', function() {
    const categoriesList = document.getElementById('categoriesList');
    const categoryTitle = document.getElementById('categoryTitle');
    const productsCount = document.getElementById('productsCount');
    
    // Verificar se categoriesList existe antes de continuar
    if (!categoriesList) return;
    
    @if(isset($categories) && $categories->count() > 0)
    // Determinar categoria ativa
    const currentPath = window.location.pathname;
    const isCategoryPage = currentPath.includes('/categoria/');
    const activeCategoryId = isCategoryPage ? parseInt(currentPath.split('/').pop()) : null;
    
    // Adicionar botão "Todos"
    const todosBtn = document.createElement('button');
    todosBtn.className = activeCategoryId === null ? 
        'inline-flex items-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 px-4 py-2 w-full justify-start gap-3 h-11 bg-primary text-primary-foreground hover:bg-primary/90' :
        'inline-flex items-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 px-4 py-2 w-full justify-start gap-3 h-11 hover:bg-accent hover:text-accent-foreground';
    todosBtn.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
            <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"></path>
            <path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
        </svg>
        <span>Todos</span>
    `;
    todosBtn.addEventListener('click', () => {
        window.location.href = '{{ route($rIndex) }}';
    });
    categoriesList.appendChild(todosBtn);
    
         // Adicionar categorias
     @foreach($categories as $cat)
         @php
             $catId = is_string($cat->id) ? $cat->id : $cat->id;
             $isNovidades = is_string($cat->id) && $cat->id === 'novidades';
         @endphp
         @if(!$isNovidades)
             // Categorias normais podem ser clicadas para navegar
             const catBtn{{ str_replace(['-', ' '], '_', $catId) }} = document.createElement('button');
             catBtn{{ str_replace(['-', ' '], '_', $catId) }}.className = activeCategoryId === {{ is_numeric($catId) ? $catId : "'{$catId}'" }} ?
                 'inline-flex items-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 px-4 py-2 w-full justify-start gap-3 h-11 bg-primary text-primary-foreground hover:bg-primary/90' :
                 'inline-flex items-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 px-4 py-2 w-full justify-start gap-3 h-11 hover:bg-accent hover:text-accent-foreground';
             catBtn{{ str_replace(['-', ' '], '_', $catId) }}.innerHTML = `
                 <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                     <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path>
                     <path d="M12 22V12"></path>
                     <path d="m3.3 7 7.703 4.734a2 2 0 0 0 1.994 0L20.7 7"></path>
                     <path d="m7.5 4.27 9 5.15"></path>
                 </svg>
                 <span>{{ $cat->name }}</span>
             `;
             catBtn{{ str_replace(['-', ' '], '_', $catId) }}.addEventListener('click', () => {
                 window.location.href = '{{ route($rMenu . '.category', is_numeric($catId) ? $catId : 0) }}';
             });
             categoriesList.appendChild(catBtn{{ str_replace(['-', ' '], '_', $catId) }});
         @else
             // Categoria "Novidades" dinâmica - apenas exibir, não navegar
             const catNovidadesBtn = document.createElement('div');
             catNovidadesBtn.className = 'inline-flex items-center whitespace-nowrap rounded-md text-sm font-medium px-4 py-2 w-full justify-start gap-3 h-11 text-muted-foreground cursor-default';
             catNovidadesBtn.innerHTML = `
                 <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                     <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path>
                     <path d="M12 22V12"></path>
                     <path d="m3.3 7 7.703 4.734a2 2 0 0 0 1.994 0L20.7 7"></path>
                     <path d="m7.5 4.27 9 5.15"></path>
                 </svg>
                 <span>{{ $cat->name }}</span>
             `;
             categoriesList.appendChild(catNovidadesBtn);
         @endif
     @endforeach
    @endif
    
    // Atualizar título e contagem
    if (categoryTitle) {
        categoryTitle.textContent = '{{ $categoryTitle }}';
    }
    if (productsCount) {
        productsCount.textContent = '{{ $productsCount }} produtos disponíveis';
    }
    
    
    // Busca
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => {
                    window.location.href = '{{ route($rMenu . '.search') }}?q=' + encodeURIComponent(query);
                }, 500);
            }
        });
    }
    
    // Filtro de categorias (botões acima da busca) - apenas no index
    @if(request()->routeIs($rIndex))
    const categoryFilterBtns = document.querySelectorAll('.category-filter-btn');
    categoryFilterBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            // Se for link (não botão), deixar o comportamento padrão
            if (this.tagName === 'A') {
                return; // Permitir navegação normal
            }
            
            e.preventDefault();
            const categoryId = this.getAttribute('data-category-id');
            
            // Atualizar estado visual dos botões
            categoryFilterBtns.forEach(b => {
                if (b.tagName === 'BUTTON') {
                    b.classList.remove('bg-primary', 'text-primary-foreground');
                    b.classList.add('bg-muted', 'text-foreground');
                }
            });
            if (this.tagName === 'BUTTON') {
                this.classList.remove('bg-muted', 'text-foreground');
                this.classList.add('bg-primary', 'text-primary-foreground');
            }
            
            // Filtrar produtos
            filterProductsByCategory(categoryId);
        });
    });
    @endif
    
    // Função para filtrar produtos por categoria
    function filterProductsByCategory(categoryId) {
        const gridProducts = document.querySelectorAll('#productsGrid .product-item');
        let visibleCount = 0;
        
        // Filtrar no grid
        gridProducts.forEach(product => {
            const productCategoryId = product.getAttribute('data-category-id');
            if (categoryId === 'all' || productCategoryId === categoryId || (categoryId === 'all' && productCategoryId === '0')) {
                product.style.display = '';
                visibleCount++;
            } else {
                product.style.display = 'none';
            }
        });
        
        // Mostrar mensagem se não houver produtos
        const gridContainer = document.getElementById('productsGrid');
        const isEmpty = visibleCount === 0;
        
        // Remover mensagem anterior se existir
        const existingMessage = document.getElementById('noProductsMessage');
        if (existingMessage) {
            existingMessage.remove();
        }
        
        if (isEmpty && gridContainer) {
            const message = document.createElement('div');
            message.id = 'noProductsMessage';
            message.className = 'text-center py-12 col-span-3';
            message.innerHTML = '<p class="text-muted-foreground">Nenhum produto encontrado nesta categoria.</p>';
            gridContainer.appendChild(message);
        }
    }
});

// Funções de carrinho (otimizada para resposta rápida)
function addToCart(productId, productName, price, buttonElement = null) {
    // Feedback visual imediato
    if (buttonElement) {
        const originalHTML = buttonElement.innerHTML;
        buttonElement.disabled = true;
        buttonElement.classList.add('opacity-50', 'cursor-wait');
        buttonElement.innerHTML = '<svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
        
        // Restaurar após timeout de segurança
        setTimeout(() => {
            if (buttonElement.disabled) {
                buttonElement.disabled = false;
                buttonElement.classList.remove('opacity-50', 'cursor-wait');
                buttonElement.innerHTML = originalHTML;
            }
        }, 3000);
    }
    
    // Atualizar badge otimisticamente (antes da resposta)
    const currentBadge = document.querySelector('nav a[href*="cart"] .absolute, #cartBadgeHeader, a[href*="cart"] .absolute');
    if (currentBadge) {
        const currentCount = parseInt(currentBadge.textContent) || 0;
        currentBadge.textContent = currentCount + 1;
        currentBadge.style.display = 'flex';
    }
    
    // Fazer requisição de forma não-bloqueante
    fetch('{{ route($rCart . '.add') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            product_id: productId,
            qty: 1,
            price: price
        })
    })
    .then(response => response.json())
    .then(data => {
        // Restaurar botão
        if (buttonElement) {
            buttonElement.disabled = false;
            buttonElement.classList.remove('opacity-50', 'cursor-wait');
            buttonElement.innerHTML = buttonElement.innerHTML.replace(/<svg class="animate-spin[^>]*>.*?<\/svg>/s, '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>');
        }
        
        if (data.success || data.ok) {
            // Atualizar badge com valor real do servidor
            if (typeof window.updateCartBadge === 'function') {
                window.updateCartBadge(data.cart_count || 0);
            }
            
            // Mostrar notificação rápida
            if (typeof showNotification === 'function') {
                showNotification('Produto adicionado!', productName, price);
            }
            
            // Recarregar drawer APENAS se estiver aberto (evita requisição desnecessária)
            // Otimização: não bloquear a resposta, fazer de forma completamente assíncrona
            const cartDrawer = document.getElementById('cartDrawer');
            if (cartDrawer && typeof loadCartIntoDrawer === 'function') {
                const isOpen = !cartDrawer.classList.contains('translate-x-full');
                if (isOpen) {
                    // Carregar de forma totalmente assíncrona, sem bloquear a resposta
                    requestIdleCallback ? requestIdleCallback(() => loadCartIntoDrawer()) : setTimeout(() => loadCartIntoDrawer(), 300);
                }
            }
        } else {
            // Reverter badge otimístico em caso de erro
            if (currentBadge) {
                const currentCount = parseInt(currentBadge.textContent) || 1;
                currentBadge.textContent = Math.max(0, currentCount - 1);
            }
            alert(data.message || 'Erro ao adicionar produto ao carrinho');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        // Restaurar botão em caso de erro
        if (buttonElement) {
            buttonElement.disabled = false;
            buttonElement.classList.remove('opacity-50', 'cursor-wait');
        }
        // Reverter badge otimístico
        if (currentBadge) {
            const currentCount = parseInt(currentBadge.textContent) || 1;
            currentBadge.textContent = Math.max(0, currentCount - 1);
        }
        alert('Erro ao adicionar produto ao carrinho');
    });
}

/* Product Modal Functions */
function openProductModal(productId) {
    window.activeModalProductId = productId;
    
    const modal = document.getElementById('productModal');
    const backdrop = document.getElementById('productModalBackdrop');
    const panel = document.getElementById('productModalPanel');
    const body = document.getElementById('productModalBody');

    // Show modal
    modal.classList.remove('hidden');
    // Animate in
    setTimeout(() => {
        backdrop.classList.remove('opacity-0');
        panel.classList.remove('scale-95', 'opacity-0');
        panel.classList.add('scale-100', 'opacity-100');
    }, 10);

    // Loading state
    body.innerHTML = '<div class="h-64 flex items-center justify-center"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div></div>';

    // Fetch Content
    const url = '{{ route($rMenu . ".product.modal", ":id") }}'.replace(':id', productId);
    
    fetch(url)
        .then(res => res.text())
        .then(html => {
            body.innerHTML = html;
            
            // Trigger price update if variants exist (init visual state)
            const checked = body.querySelector('input[name="modal_variant_id"]:checked');
            if (checked) {
                 updateModalPrice(checked);
            }
            // Initialize quantity display
            const qtyEl = document.getElementById('modalQty');
            if (qtyEl) qtyEl.textContent = '1';
        })
        .catch(err => {
            console.error(err);
            body.innerHTML = '<div class="p-6 text-center text-destructive">Erro ao carregar produto. Tente novamente.</div>';
        });
}

function closeProductModal() {
    const modal = document.getElementById('productModal');
    const backdrop = document.getElementById('productModalBackdrop');
    const panel = document.getElementById('productModalPanel');

    backdrop.classList.add('opacity-0');
    panel.classList.remove('scale-100', 'opacity-100');
    panel.classList.add('scale-95', 'opacity-0');

    setTimeout(() => {
        modal.classList.add('hidden');
        document.getElementById('productModalBody').innerHTML = ''; 
    }, 300);
}

function changeModalQty(delta) {
    const qtyEl = document.getElementById('modalQty');
    if (!qtyEl) return;
    
    let qty = parseInt(qtyEl.textContent) || 1;
    qty = Math.max(1, qty + delta);
    qtyEl.textContent = qty;
    
    refreshModalTotals();
}

function updateModalPrice(input) {
    refreshModalTotals();
}

function refreshModalTotals() {
    const qtyEl = document.getElementById('modalQty');
    if (!qtyEl) return;
    const qty = parseInt(qtyEl.textContent) || 1;
    
    const checked = document.querySelector('input[name="modal_variant_id"]:checked');
    let price = 0;
    
    if (checked) {
        price = parseFloat(checked.getAttribute('data-price') || 0);
        
        // Update unit price display
        const unitEl = document.getElementById('modalPrice');
        if (unitEl) unitEl.textContent = 'R$ ' + price.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    } else {
        // Fallback: parse from modalPrice text or use zero if fails
        const unitEl = document.getElementById('modalPrice');
        if (unitEl) {
             const text = unitEl.textContent.replace('R$', '').trim().replace(/\./g, '').replace(',', '.');
             price = parseFloat(text) || 0;
        }
    }
    
    const total = price * qty;
    const totalEl = document.getElementById('modalTotalPrice');
    if (totalEl) totalEl.textContent = 'R$ ' + total.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function submitModalCart() {
    const qtyEl = document.getElementById('modalQty');
    const qty = parseInt(qtyEl.textContent) || 1;
    
    const checked = document.querySelector('input[name="modal_variant_id"]:checked');
    let variantId = null;
    let price = 0;
    
    // Check if variants are present
    const hasVariants = document.querySelector('input[name="modal_variant_id"]');

    if (hasVariants) {
        if (!checked) {
            showNotification('Selecione uma opção.', 'error');
            return;
        }
        variantId = checked.value;
        price = parseFloat(checked.getAttribute('data-price') || 0);
    } else {
         const unitEl = document.getElementById('modalPrice');
         if(unitEl) {
             const text = unitEl.textContent.replace('R$', '').trim().replace(/\./g, '').replace(',', '.');
             price = parseFloat(text) || 0;
         }
    }

    const observation = document.getElementById('modalObservation')?.value || '';
    
    if (!window.activeModalProductId) {
         console.error("No active product ID");
         return;
    }

    const btn = document.querySelector('#productModalBody button[onclick="submitModalCart()"]');
    const originalText = btn ? btn.innerHTML : '';
    if(btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="animate-spin inline-block mr-2">⟳</span> Adicionando...';
    }

    fetch('{{ route($rCart . ".add") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
             product_id: window.activeModalProductId,
             variant_id: variantId,
             qty: qty,
             price: price, 
             special_instructions: observation
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok || data.success) {
            closeProductModal();
            if (typeof window.updateCartBadge === 'function') {
                 try { window.updateCartBadge(data.cart_count); } catch(e){}
            } else {
                 const badge = document.querySelector('a[href*="cart"] .absolute');
                 if(badge) badge.textContent = data.cart_count;
            }
             showNotification('Adicionado ao pedido!', 'success');
             
             // Open cart drawer if exists
             const cartDrawer = document.getElementById('cartDrawer');
             if (cartDrawer && typeof loadCartIntoDrawer === 'function') {
                const isOpen = !cartDrawer.classList.contains('translate-x-full');
                if (isOpen) loadCartIntoDrawer();
             }
        } else {
             showNotification(data.message || 'Erro ao adicionar.', 'error');
        }
    })
    .catch(e => {
        console.error(e);
        showNotification('Erro ao processar pedido.', 'error');
    })
    .finally(() => {
        if(btn) {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });
}
</script>
@endpush
