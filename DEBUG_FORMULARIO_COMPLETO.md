# CÓDIGO COMPLETO DO FORMULÁRIO DE ADICIONAR ITEM

## 1. FORMULÁRIO HTML (resources/views/dash/pages/orders/show.blade.php)

```html
<!-- Modal para Adicionar Item -->
<div id="add-item-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-card rounded-lg shadow-lg w-full max-w-md mx-4 border">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Adicionar Item ao Pedido</h3>
                <button type="button" onclick="document.getElementById('add-item-modal').classList.add('hidden')" class="text-muted-foreground hover:text-foreground">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x">
                        <path d="M18 6 6 18"></path>
                        <path d="M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form action="{{ route('dashboard.orders.addItem', $order->id) }}" method="POST">
                @csrf
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Produto *</label>
                        <select id="product-select" name="product_id" required class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            <option value="">Selecione um produto</option>
                            <option value="loose_item">Item Avulso</option>
                            @foreach($availableProducts as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                    {{ $product->name }} - R$ {{ number_format($product->price, 2, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Campos para Item Avulso (ocultos por padrão) -->
                    <div id="loose-item-fields" class="hidden space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Nome do Item *</label>
                            <input type="text" id="loose-item-name" name="custom_name" maxlength="255" placeholder="Ex: Molho de pimenta" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Valor *</label>
                            <input type="number" id="loose-item-price" name="unit_price" step="0.01" min="0.01" placeholder="0.00" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Quantidade *</label>
                            <input type="number" id="loose-item-quantity" name="quantity" min="1" value="1" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Descrição (opcional)</label>
                            <textarea id="loose-item-description" name="special_instructions" rows="2" maxlength="500" placeholder="Ex: Molho artesanal, picante" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"></textarea>
                        </div>
                    </div>

                    <!-- Campos para Produto Normal (visíveis por padrão) -->
                    <div id="normal-item-fields">
                        <div>
                            <label class="block text-sm font-medium mb-2">Quantidade *</label>
                            <input type="number" name="quantity" id="quantity" min="1" value="1" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Preço Unitário (opcional)</label>
                            <input type="number" name="unit_price" id="unit_price" step="0.01" min="0" placeholder="Deixe em branco para usar o preço padrão" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Nome Personalizado (opcional)</label>
                            <input type="text" name="custom_name" maxlength="255" placeholder="Ex: Focaccia Especial" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Observações Especiais (opcional)</label>
                            <textarea name="special_instructions" id="special_instructions" rows="2" maxlength="500" placeholder="Ex: Com pouco sal, sem azeitona" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="document.getElementById('add-item-modal').classList.add('hidden')" class="flex-1 inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4">
                        Adicionar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
```

## 2. ROTA (routes/web.php)

```php
Route::post('/{order}/items', [\App\Http\Controllers\Dashboard\OrdersController::class, 'addItem'])->name('addItem');
```

## 3. CONTROLLER (app/Http/Controllers/Dashboard/OrdersController.php)

[Mostrar código completo do método addItem]

## 4. JAVASCRIPT (parte do show.blade.php)

[Mostrar código JavaScript completo]
