<div class="card hover:shadow-xl transition-all duration-300 hover:-translate-y-1 group">
    @if($product->image_url)
    <div class="relative overflow-hidden rounded-t-lg">
        <img src="{{ $product->image_url }}" 
             alt="{{ $product->name }}" 
             class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
        @if($product->is_featured)
        <div class="absolute top-2 left-2">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 shadow-sm">
                <i class="fas fa-star mr-1"></i>
                Destaque
            </span>
        </div>
        @endif
    </div>
    @else
    <div class="w-full h-48 bg-gradient-to-br from-gray-100 to-gray-200 rounded-t-lg flex items-center justify-center">
        <i class="fas fa-image text-4xl text-gray-400"></i>
    </div>
    @endif
    
    <div class="p-6 space-y-4">
        <div>
            <h3 class="font-bold text-lg text-gray-900 line-clamp-2 group-hover:text-orange-600 transition-colors">
                {{ $product->name }}
            </h3>
            @if($product->description)
            <p class="text-gray-600 text-sm line-clamp-2 mt-2">
                {{ $product->description }}
            </p>
            @endif
        </div>
        
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <span class="text-2xl font-bold text-orange-600">
                    R$ {{ number_format($product->price, 2, ',', '.') }}
                </span>
                @if($product->preparation_time)
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    <i class="fas fa-clock mr-1"></i>
                    {{ $product->preparation_time }}min
                </span>
                @endif
            </div>
        </div>
        
        <div class="flex items-center space-x-2">
            <button onclick="addToCart({{ $product->id }}, 1, {{ $product->price }})" 
                    class="flex-1 bg-orange-600 text-white px-4 py-3 rounded-lg hover:bg-orange-700 transition-all duration-200 hover:scale-105 active:scale-95 font-medium">
                <i class="fas fa-plus mr-2"></i>
                Adicionar
            </button>
            
            <a href="{{ route('menu.product', $product) }}" 
               class="bg-gray-100 text-gray-700 px-4 py-3 rounded-lg hover:bg-gray-200 transition-all duration-200 hover:scale-105">
                <i class="fas fa-eye"></i>
            </a>
        </div>
        
        @if($product->allergens)
        <div class="pt-2 border-t border-gray-100">
            <div class="flex flex-wrap gap-1">
                @foreach(json_decode($product->allergens, true) ?? [$product->allergens] as $allergen)
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    {{ $allergen }}
                </span>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
