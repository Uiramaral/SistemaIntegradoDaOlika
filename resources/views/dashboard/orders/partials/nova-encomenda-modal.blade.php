{{-- Modal Nova Encomenda (Novo PDV) – Estilo Confeitaria Pro. Popup sobre popup: PIX. --}}
<div id="nova-encomenda-root" class="fixed inset-0 z-[100]" aria-hidden="true" style="display: none;"
    x-data="novaEncomendaModal()" x-show="open" x-cloak x-init="open = false"
    @open-nova-encomenda.window="if ($event.detail?.userInitiated) { customerId = null; customerIsWholesale = false; customerName = ''; customerPhone = ''; deliveryAddress = ''; deliveryNumber = ''; deliveryAddressObservation = ''; deliveryCep = ''; addressId = null; deliveryOffHours = false; deliverySlotsList = []; selectedDeliveryDate = ''; selectedDeliverySlot = ''; deliveryDate = new Date().toISOString().slice(0, 10); deliveryTime = ''; open = true; clearCurrentItem(); $nextTick(() => { fetchModalProducts(); fetchDeliverySlots(); }); }"
    @keydown.escape.window="if (currentItem.dropdownOpen) closeProductDropdown(); else if (pixQrOpen) closePixQr(); else if (pixOpen) closePix(); else close()"
    x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="ease-out duration-150" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">
    {{-- Overlay --}}
    <div class="absolute inset-0 bg-black/50"
        @click="if (pixQrOpen) closePixQr(); else if (pixOpen) closePix(); else close()"></div>

    {{-- Modal principal: Nova Encomenda --}}
    <div class="absolute inset-0 flex items-start sm:items-center justify-center p-4 sm:p-6 overflow-y-auto"
        @click.self="if (pixQrOpen) closePixQr(); else if (!pixOpen) close()">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl lg:max-w-4xl xl:max-w-5xl max-h-[90vh] flex flex-col overflow-hidden my-auto"
            @click.stop x-show="!pixOpen && !pixQrOpen" x-transition>
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-border shrink-0">
                <div>
                    <h2 class="text-lg font-semibold text-foreground">Nova Encomenda</h2>
                    <p class="text-sm text-muted-foreground mt-0.5">Registe os detalhes da encomenda (dados sensíveis
                        são criptografados)</p>
                </div>
                <button type="button"
                    class="p-2 rounded-lg text-muted-foreground hover:bg-muted hover:text-foreground transition-colors"
                    @click="close()" aria-label="Fechar">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            {{-- Body (scrollável) --}}
            <div class="flex-1 overflow-y-auto p-6 space-y-6">
                {{-- Cliente --}}
                <div class="space-y-4">
                    <div class="space-y-2" x-show="!customerId">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-foreground">Nome do Consumidor <span
                                    class="text-destructive">*</span></label>
                            <button type="button" @click="createCustomerModalOpen = true"
                                class="text-xs text-primary font-bold hover:underline mb-1">+ Novo Consumidor</button>
                        </div>
                        <div class="relative">
                            <i data-lucide="search"
                                class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground"></i>
                            <input type="text" x-model="customerName" @input.debounce.300ms="searchCustomers()"
                                placeholder="Buscar consumidor..."
                                class="input-copycat w-full pl-10 h-10 rounded-xl border border-border bg-muted/30 focus:bg-white">

                            <div x-show="customerSuggestions.length" x-cloak
                                class="absolute z-50 left-0 right-0 mt-1 bg-white rounded-xl border border-border shadow-lg max-h-48 overflow-y-auto">
                                <template x-for="c in customerSuggestions" :key="c.id">
                                    <button type="button" @click="selectCustomer(c)"
                                        class="w-full px-4 py-3 text-left text-sm hover:bg-muted/50 flex flex-col transition-colors border-b border-muted/30 last:border-0">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-foreground" x-text="c.name"></span>
                                            <span x-show="c.is_wholesale"
                                                class="flex items-center gap-1.5 text-[10px] font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-100">
                                                <i data-lucide="diamond" class="w-3 h-3 fill-blue-600"></i>
                                                <span class="hidden lg:inline">REVENDA</span>
                                            </span>
                                        </div>
                                        <span class="text-xs text-muted-foreground"
                                            x-text="c.phone || c.email || 'Sem contato'"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Card de Cliente Selecionado --}}
                    <div x-show="customerId" x-cloak
                        class="rounded-xl bg-muted/20 border border-border/50 p-4 transition-all">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-bold text-foreground" x-text="customerName"></h4>
                                    <span x-show="customerIsWholesale"
                                        class="flex items-center gap-1.5 text-[11px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-100">
                                        <i data-lucide="diamond" class="w-3.5 h-3.5 fill-blue-600"></i>
                                        <span class="hidden lg:inline">Revenda</span>
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 mt-1 text-xs text-muted-foreground">
                                    <span x-text="customerPhone || 'Sem telefone'"></span>
                                    <span x-show="customerPhone && customerEmail"
                                        class="w-1 h-1 rounded-full bg-muted-foreground/30"></span>
                                    <span x-text="customerEmail"></span>
                                </div>
                            </div>
                            <button type="button" @click="resetCustomerSelection()"
                                class="p-2 text-muted-foreground hover:text-destructive transition-colors">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Campo de Telefone (Apenas para cliente manual/não selecionado) --}}
                    <div class="space-y-2" x-show="!customerId">
                        <label class="block text-sm font-medium text-foreground">Telefone</label>
                        <div class="relative">
                            <i data-lucide="phone"
                                class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground"></i>
                            <input type="text" x-model="customerPhone" placeholder="Ex: 71 99999-9999"
                                class="input-copycat w-full pl-10 h-10 rounded-xl border border-border bg-muted/30 focus:bg-white">
                        </div>
                    </div>
                </div>

                {{-- Produtos: formulário único + resumo --}}
                <div>
                    <div class="flex flex-wrap items-center gap-3 mb-3">
                        <span class="text-sm font-medium text-foreground">Adicionar item</span>
                        <div class="flex rounded-lg overflow-hidden border border-border">
                            <button type="button" @click="sourceLista = true"
                                :class="sourceLista ? 'bg-primary text-white' : 'bg-muted/30 text-muted-foreground hover:bg-muted/50'"
                                class="px-3 py-1.5 text-xs font-medium transition-colors">Da lista</button>
                            <template x-if="allowAvulso">
                                <button type="button" @click="sourceLista = false"
                                    :class="!sourceLista ? 'bg-primary text-white' : 'bg-muted/30 text-muted-foreground hover:bg-muted/50'"
                                    class="px-3 py-1.5 text-xs font-medium transition-colors">+ Manual</button>
                            </template>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="allowAvulso" @change="saveAllowAvulso()"
                                class="rounded border-border text-primary focus:ring-primary">
                            <span class="text-xs text-muted-foreground">Permitir itens avulsos</span>
                        </label>
                    </div>

                    {{-- Formulário único: produto, variação, quantidade, observação --}}
                    <div class="rounded-xl border border-border bg-muted/10 p-4 space-y-4 mb-4">
                        <div x-show="sourceLista" class="relative" @click.outside="closeProductDropdown()">
                            <label class="block text-xs font-medium text-muted-foreground mb-1">Produto <span
                                    class="text-destructive">*</span></label>
                            <div class="relative">
                                <i data-lucide="search"
                                    class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground"></i>
                                <input type="text" x-model="currentItem.productDisplay"
                                    @input.debounce.300ms="debounceProductSearch()" @focus="loadProductsOnFocus()"
                                    placeholder="Clique para ver produtos ou digite para buscar..."
                                    class="input-copycat w-full h-10 pl-10 rounded-xl" autocomplete="off">
                            </div>
                            <div x-show="currentItem.dropdownOpen" x-cloak x-transition
                                class="absolute z-30 left-0 right-0 mt-1 bg-white rounded-xl border border-border shadow-xl overflow-hidden">
                                <div x-show="currentItem.suggestions && currentItem.suggestions.length"
                                    class="max-h-56 overflow-y-auto">
                                    <template x-for="p in currentItem.suggestions" :key="'p-' + p.id">
                                        <button type="button" @click="selectProduct(p)"
                                            class="w-full px-4 py-3 text-left text-sm hover:bg-muted/50 flex justify-between items-center gap-2 border-b border-border/50 last:border-0 transition-colors">
                                            <div class="min-w-0 flex-1">
                                                <span class="font-medium text-foreground block truncate"
                                                    x-text="p.name"></span>
                                                <span x-show="p.has_variants || (p.variants || []).length"
                                                    class="flex items-center gap-1 text-xs text-primary font-medium mt-1 bg-primary/5 px-2 py-0.5 rounded-md w-fit">
                                                    <i data-lucide="layers" class="w-3 h-3"></i>
                                                    A partir de R$ <span
                                                        x-text="formatPrice(minVariantPrice(p))"></span> · Escolher
                                                    opção
                                                </span>
                                            </div>
                                            <span x-show="!p.has_variants && !(p.variants || []).length"
                                                class="text-primary font-semibold shrink-0"
                                                x-text="'R$ ' + formatPrice(p.price)"></span>
                                        </button>
                                    </template>
                                </div>
                                <div x-show="currentItem.productsFetched && (!currentItem.suggestions || !currentItem.suggestions.length)"
                                    class="px-4 py-6 text-center text-sm text-muted-foreground">
                                    <i data-lucide="package-x" class="w-10 h-10 mx-auto mb-2 opacity-50"></i>
                                    <p class="font-medium text-foreground">Nenhum produto encontrado</p>
                                    <p class="text-xs mt-1">Digite para buscar ou verifique se há produtos cadastrados.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div x-show="!sourceLista">
                            <label class="block text-xs font-medium text-muted-foreground mb-1">Produto <span
                                    class="text-destructive">*</span></label>
                            <input type="text" x-model="currentItem.name" placeholder="Ex: Bolo de aniversário"
                                class="input-copycat w-full h-10 rounded-xl">
                        </div>
                        <div x-show="currentItem.productId" class="space-y-3">
                            <div
                                x-show="currentItem.hasVariants || (currentItem.variants && currentItem.variants.length)">
                                <label class="block text-xs font-medium text-muted-foreground mb-1">Variação <span
                                        class="text-destructive">*</span></label>
                                <p class="text-xs text-muted-foreground mb-3">Este produto possui variações, selecione
                                    uma para continuar:</p>

                                <div class="grid grid-cols-1 gap-2">
                                    <template x-for="v in (currentItem.variants || [])" :key="v.id">
                                        <button type="button" @click="selectVariant(v)" :class="String(currentItem.variantId) === String(v.id) 
                                                ? 'border-primary bg-primary/5 text-primary ring-1 ring-primary' 
                                                : 'border-border bg-white hover:bg-muted/30'"
                                            class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-xl border text-left transition-all duration-200">

                                            <div class="flex items-center gap-3">
                                                <div class="w-4 h-4 rounded-full border flex items-center justify-center transition-colors"
                                                    :class="String(currentItem.variantId) === String(v.id) ? 'border-primary' : 'border-muted-foreground/30'">
                                                    <div x-show="String(currentItem.variantId) === String(v.id)"
                                                        class="w-2 h-2 rounded-full bg-primary"></div>
                                                </div>
                                                <span class="font-medium text-sm text-foreground"
                                                    x-text="v.name"></span>
                                            </div>

                                            <span class="text-sm font-bold text-primary shrink-0"
                                                x-text="'R$ ' + formatPrice(v.price)"></span>
                                        </button>
                                    </template>
                                </div>

                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-3">
                            <div class="col-span-3 sm:col-span-2">
                                <label class="block text-xs font-medium text-muted-foreground mb-1">Qtd.</label>
                                <input type="number" min="1" x-model.number="currentItem.quantity"
                                    class="input-copycat w-full h-10 rounded-xl text-center">
                            </div>
                            <div class="col-span-4 sm:col-span-3">
                                <label class="block text-xs font-medium text-muted-foreground mb-1">Preço <span
                                        class="text-destructive">*</span></label>
                                <input type="text" x-model="currentItem.price" placeholder="0,00"
                                    class="input-copycat w-full h-10 rounded-xl font-medium">
                            </div>
                            <div class="col-span-5 sm:col-span-7">
                                <label class="block text-xs font-medium text-muted-foreground mb-1">Observação</label>
                                <input type="text" x-model="currentItem.observation" placeholder="Ex: Sem glúten..."
                                    class="input-copycat w-full h-10 rounded-xl">
                            </div>
                        </div>
                        <button type="button" @click="addCurrentToResumo()" class="btn-copycat-primary w-full gap-2">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            Adicionar ao pedido
                        </button>
                    </div>

                    {{-- Resumo do pedido --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-foreground">Resumo do pedido</span>
                            <span x-show="items.length" class="text-xs text-muted-foreground"
                                x-text="items.length + ' item(ns)'"></span>
                        </div>
                        <div x-show="!items.length"
                            class="rounded-xl border border-dashed border-border bg-muted/10 p-6 text-center text-sm text-muted-foreground">
                            Nenhum item adicionado. Use o formulário acima para adicionar.
                        </div>
                        <ul x-show="items.length"
                            class="divide-y divide-border rounded-xl border border-border bg-white overflow-y-auto max-h-64">
                            <template x-for="(it, idx) in items" :key="it.itemId || idx">
                                <li
                                    class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-muted/20 transition-colors">
                                    <div class="min-w-0 flex-1">
                                        <span class="font-medium text-foreground block truncate"
                                            x-text="it.name + (it.variantName ? ' · ' + it.variantName : '')"></span>
                                        <span class="text-xs text-muted-foreground"
                                            x-text="(it.quantity || 1) + ' × R$ ' + formatPrice(it.price) + (it.observation ? ' · ' + it.observation : '')"></span>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <span class="font-semibold text-primary"
                                            x-text="'R$ ' + formatPrice((parseFloat(String(it.price || '0').replace(',', '.')) || 0) * (parseInt(it.quantity, 10) || 1))"></span>
                                        <button type="button" @click="removeItem(idx)"
                                            class="p-1.5 rounded-lg text-destructive hover:bg-destructive/10 transition-colors shrink-0"
                                            aria-label="Remover">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

                {{-- Entrega --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <i data-lucide="truck" class="w-5 h-5 text-muted-foreground"></i>
                            <span class="text-sm font-semibold text-foreground">Entrega</span>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer shrink-0">
                            <input type="checkbox" x-model="deliveryOffHours"
                                class="rounded border-border text-primary focus:ring-primary">
                            <span class="text-xs text-muted-foreground">Entrega fora de horário</span>
                        </label>
                    </div>
                    <template x-if="!deliveryOffHours && deliverySlotsList.length">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-muted-foreground mb-1">Data</label>
                                <select x-model="selectedDeliveryDate" @change="selectedDeliverySlot = ''"
                                    class="input-copycat w-full h-10 rounded-xl">
                                    <option value="">Selecione o dia</option>
                                    <template x-for="d in deliverySlotsList" :key="d.date">
                                        <option :value="d.date" x-text="d.label + ' (' + (d.day_name || '') + ')'">
                                        </option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-muted-foreground mb-1">Horário</label>
                                <select x-model="selectedDeliverySlot" class="input-copycat w-full h-10 rounded-xl">
                                    <option value="">Selecione o horário</option>
                                    <template
                                        x-for="s in (deliverySlotsList.find(x => x.date === selectedDeliveryDate) || {}).slots || []"
                                        :key="s.value">
                                        <option :value="s.value" x-text="s.label"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </template>
                    <template x-if="deliveryOffHours || !deliverySlotsList.length">
                        <div class="space-y-4" x-show="deliveryOffHours || !deliverySlotsList.length">
                            <div>
                                <label class="block text-xs font-medium text-muted-foreground mb-1">Data e Hora da
                                    Entrega</label>
                                <input type="datetime-local" x-model="selectedDeliveryAt"
                                    class="input-copycat w-full h-10 rounded-xl">
                                <p class="text-[10px] text-muted-foreground mt-1">Selecione qualquer dia e horário para
                                    agendar esta entrega especial.</p>
                            </div>
                        </div>
                    </template>
                    {{-- Endereço reorganizado --}}
                    <div class="space-y-3">
                        <label class="block text-xs font-medium text-muted-foreground mb-1">Endereço de Entrega</label>

                        {{-- Linha 1: CEP, Número, Taxa --}}
                        <div class="grid grid-cols-12 gap-3">
                            <div class="col-span-5 sm:col-span-4">
                                <label class="block text-[10px] font-medium text-muted-foreground mb-1">CEP</label>
                                <input type="text" x-model="deliveryCep" placeholder="00000-000"
                                    class="input-copycat w-full h-10 rounded-xl">
                            </div>
                            <div class="col-span-3 sm:col-span-3">
                                <label class="block text-[10px] font-medium text-muted-foreground mb-1">Número</label>
                                <input type="text" x-model="deliveryNumber" placeholder="Nº"
                                    class="input-copycat w-full h-10 rounded-xl">
                            </div>
                            <div class="col-span-4 sm:col-span-5">
                                <label class="block text-[10px] font-medium text-muted-foreground mb-1">Taxa</label>
                                <input type="text" x-model="deliveryFee" placeholder="0,00"
                                    class="input-copycat w-full h-10 rounded-xl text-right">
                            </div>
                        </div>

                        {{-- Linha 2: Endereço (Logradouro) --}}
                        <div>
                            <label class="block text-[10px] font-medium text-muted-foreground mb-1">Endereço</label>
                            <input type="text" x-model="deliveryAddress" placeholder="Rua, Avenida, etc."
                                class="input-copycat w-full h-10 rounded-xl">
                        </div>

                        {{-- Linha 3: Observação do Endereço --}}
                        <div>
                            <label class="block text-[10px] font-medium text-muted-foreground mb-1">Observação do
                                endereço</label>
                            <input type="text" x-model="deliveryAddressObservation"
                                placeholder="Ponto de referência, complemento, etc."
                                class="input-copycat w-full h-10 rounded-xl">
                        </div>
                    </div>
                </div>

                {{-- Resumo + Pagamento --}}
                <div class="bg-muted/20 rounded-xl p-4 border border-border space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-muted-foreground">Subtotal</span>
                        <span class="font-medium text-foreground" x-text="'R$ ' + formatPrice(subtotal)"></span>
                    </div>
                    <div class="flex justify-between text-sm font-semibold">
                        <span class="text-foreground">Total</span>
                        <span class="text-primary" x-text="'R$ ' + formatPrice(total)"></span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-2">
                        <button type="button" @click="openPix('qr')" :disabled="submitting"
                            class="flex flex-col items-center justify-center gap-2 py-4 rounded-xl border-2 border-border hover:border-primary hover:bg-primary/5 transition-colors">
                            <i data-lucide="qr-code" class="w-6 h-6 sm:w-8 sm:h-8 text-muted-foreground"></i>
                            <span class="text-xs sm:text-sm font-medium text-foreground">PIX</span>
                        </button>
                        <button type="button" @click="submitOrderWithLink()" :disabled="submitting"
                            class="flex flex-col items-center justify-center gap-2 py-4 rounded-xl border-2 border-border hover:border-primary hover:bg-primary/5 transition-colors">
                            <i data-lucide="link" class="w-6 h-6 sm:w-8 sm:h-8 text-muted-foreground"></i>
                            <span class="text-xs sm:text-sm font-medium text-foreground text-center">Link
                                Pagamento</span>
                        </button>
                        <button type="button" @click="openMoneyModal()" :disabled="submitting"
                            class="flex flex-col items-center justify-center gap-2 py-4 rounded-xl border-2 border-border hover:border-emerald-500 hover:bg-emerald-50 transition-colors">
                            <i data-lucide="banknote" class="w-6 h-6 sm:w-8 sm:h-8 text-emerald-600"></i>
                            <span class="text-xs sm:text-sm font-medium text-foreground">Dinheiro</span>
                        </button>
                        <button type="button" @click="submitOrderFiado()" :disabled="submitting"
                            class="flex flex-col items-center justify-center gap-2 py-4 rounded-xl border-2 border-amber-300 hover:border-amber-500 hover:bg-amber-50 transition-colors">
                            <i data-lucide="receipt" class="w-6 h-6 sm:w-8 sm:h-8 text-amber-600"></i>
                            <span class="text-xs sm:text-sm font-medium text-foreground">Fiado / Outros</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 border-t border-border flex justify-end gap-2 shrink-0">
                <button type="button" @click="close()" class="btn-copycat-outline">Cancelar</button>
                <button type="button" @click="submitOrder(false)" class="btn-copycat-primary gap-2"
                    :disabled="submitting">
                    <i data-lucide="check" class="w-4 h-4" x-show="!submitting"></i>
                    <span x-show="!submitting">Registar encomenda</span>
                    <span x-show="submitting">A registar…</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Modal: Como deseja processar o PIX? (QR na tela ou enviar por WhatsApp) --}}
    <div class="absolute inset-0 flex items-center justify-center p-4 z-[102] bg-black/50" x-show="pixOpen" x-cloak
        x-transition @click.self="closePix()">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden" @click.stop>
            <div class="flex items-center justify-between px-6 py-4 border-b border-border">
                <div class="flex items-center gap-2">
                    <i data-lucide="qr-code" class="w-5 h-5 text-muted-foreground"></i>
                    <h3 class="text-lg font-semibold text-foreground">Pagamento via PIX</h3>
                </div>
                <button type="button" @click="closePix()" class="p-2 rounded-lg text-muted-foreground hover:bg-muted">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <p class="text-sm text-muted-foreground">Como deseja processar o PIX?</p>
                <div class="space-y-2">
                    <label class="flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-colors"
                        :class="pixOption === 'qr' ? 'border-primary bg-primary/5' : 'border-border hover:bg-muted/30'">
                        <input type="radio" x-model="pixOption" value="qr" class="sr-only">
                        <i data-lucide="qr-code" class="w-6 h-6 text-muted-foreground"></i>
                        <div>
                            <span class="font-medium text-foreground block">Gerar QR Code em tela</span>
                            <span class="text-xs text-muted-foreground">Cliente escaneia o QR Code na tela</span>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-colors"
                        :class="pixOption === 'whatsapp' ? 'border-primary bg-primary/5' : 'border-border hover:bg-muted/30'">
                        <input type="radio" x-model="pixOption" value="whatsapp" class="sr-only">
                        <i data-lucide="send" class="w-6 h-6 text-muted-foreground"></i>
                        <div>
                            <span class="font-medium text-foreground block">Enviar cobrança PIX</span>
                            <span class="text-xs text-muted-foreground">Envia cobrança via WhatsApp</span>
                        </div>
                    </label>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-border flex justify-end gap-2">
                <button type="button" @click="closePix()" class="btn-copycat-outline gap-2">
                    <i data-lucide="x" class="w-4 h-4"></i> Cancelar
                </button>
                <button type="button" @click="confirmPixAndSubmit()" class="btn-copycat-primary gap-2"
                    :disabled="submitting">
                    <i data-lucide="qr-code" class="w-4 h-4" x-show="!submitting && pixOption === 'qr'"></i>
                    <i data-lucide="send" class="w-4 h-4" x-show="!submitting && pixOption === 'whatsapp'"></i>
                    <span x-show="!submitting"
                        x-text="pixOption === 'whatsapp' ? 'Enviar cobrança' : 'Gerar QR Code'"></span>
                    <span x-show="submitting">A processar…</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Modal: QR Code PIX (após submit com PIX) --}}
    <div class="absolute inset-0 flex items-center justify-center p-4 z-[102] bg-black/50" x-show="pixQrOpen" x-cloak
        x-transition>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-border">
                <h3 class="text-lg font-semibold text-foreground">Pagamento via PIX</h3>
                <button type="button" @click="closePixQr()" class="p-2 rounded-lg text-muted-foreground hover:bg-muted">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="p-6 space-y-4" x-show="pixQrData && (pixQrData.qr_code_base64 || pixQrData.copy_paste)">
                <p class="text-center text-sm font-medium"
                    x-text="'Valor: R$ ' + (pixQrData ? formatPrice(pixQrData.amount || 0) : '0,00')"></p>
                <p class="text-center text-xs text-muted-foreground">Escaneie o QR Code ou copie o código PIX</p>
                <div class="flex justify-center" x-show="pixQrData && pixQrData.qr_code_base64">
                    <img :src="'data:image/png;base64,' + (pixQrData && pixQrData.qr_code_base64 ? pixQrData.qr_code_base64 : '')"
                        alt="QR Code PIX" class="w-64 h-64 rounded-xl border border-border">
                </div>
                <div class="space-y-2" x-show="pixQrData && pixQrData.copy_paste">
                    <label class="block text-xs font-medium text-muted-foreground">Código PIX (copiar e colar):</label>
                    <div class="flex gap-2">
                        <input type="text" :value="pixQrData && pixQrData.copy_paste ? pixQrData.copy_paste : ''"
                            readonly class="input-copycat flex-1 font-mono text-sm" id="pix-copy-paste-nova">
                        <button type="button" @click="copyPixCode()" data-pix-copy-btn
                            class="btn-copycat-outline shrink-0">Copiar</button>
                    </div>
                </div>
                <div class="py-2 px-4 rounded-lg bg-muted/60 text-center">
                    <p class="text-sm font-medium text-muted-foreground">Aguardando confirmação do pagamento...</p>
                </div>
            </div>
            <div
                class="px-6 py-4 border-t border-border flex flex-col-reverse sm:flex-row sm:flex-wrap sm:justify-end gap-2">
                <button type="button" @click="closePixQr()"
                    class="btn-copycat-outline gap-2 w-full sm:w-auto order-1 sm:order-1 sm:mr-auto">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Voltar
                </button>
                <button type="button" @click="sendPixWhatsAppFromQr()"
                    :disabled="sendingPixWhatsApp || pixQrSent || !pixQrOrderId"
                    class="btn-copycat-outline gap-2 w-full sm:w-auto order-2 sm:order-2">
                    <i data-lucide="send" class="w-4 h-4" x-show="!sendingPixWhatsApp && !pixQrSent"></i>
                    <span x-show="sendingPixWhatsApp">A enviar…</span>
                    <span x-show="!sendingPixWhatsApp && pixQrSent">Enviado</span>
                    <span x-show="!sendingPixWhatsApp && !pixQrSent">Enviar cobrança pelo WhatsApp</span>
                </button>
                <button type="button" @click="confirmPixPaymentFromQr()"
                    :disabled="confirmingPixPayment || !pixQrOrderId"
                    class="btn-copycat-primary gap-2 w-full sm:w-auto order-3 sm:order-3">
                    <i data-lucide="check-circle" class="w-4 h-4" x-show="!confirmingPixPayment"></i>
                    <span x-show="confirmingPixPayment">A confirmar…</span>
                    <span x-show="!confirmingPixPayment">Confirmar Pagamento</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Modal: Pagamento em Dinheiro --}}
    <div class="absolute inset-0 flex items-center justify-center p-4 z-[102] bg-black/50" x-show="moneyModalOpen"
        x-cloak x-transition @click.self="closeMoneyModal()">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden" @click.stop>
            <div class="flex items-center justify-between px-6 py-4 border-b border-border">
                <h3 class="text-lg font-semibold text-foreground">Pagamento em Dinheiro</h3>
                <button type="button" @click="closeMoneyModal()"
                    class="p-2 rounded-lg text-muted-foreground hover:bg-muted">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div class="text-center space-y-1">
                    <p class="text-sm text-muted-foreground">Total do pedido</p>
                    <p class="text-3xl font-bold text-foreground" x-text="'R$ ' + formatPrice(total)"></p>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-foreground">Valor Recebido</label>
                    <div class="relative">
                        <span
                            class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground font-medium">R$</span>
                        <input type="number" step="0.01" x-model="moneyAmountReceived" @input="calculateChange()"
                            placeholder="0.00"
                            class="input-copycat w-full pl-10 h-12 rounded-xl text-lg font-semibold border-border bg-muted/10 focus:bg-white">
                    </div>
                </div>

                <div class="bg-muted/30 rounded-xl p-4 border border-border/50 text-center transition-colors"
                    :class="moneyChange < 0 ? 'bg-destructive/10 border-destructive/30' : 'bg-emerald-50/50 border-emerald-100'">
                    <p class="text-xs font-medium uppercase tracking-wide"
                        :class="moneyChange < 0 ? 'text-destructive' : 'text-emerald-600'">Troco</p>
                    <p class="text-2xl font-bold" :class="moneyChange < 0 ? 'text-destructive' : 'text-emerald-700'"
                        x-text="moneyChange < 0 ? 'Faltam R$ ' + formatPrice(Math.abs(moneyChange)) : 'R$ ' + formatPrice(moneyChange)">
                    </p>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-border flex justify-end gap-2">
                <button type="button" @click="closeMoneyModal()" class="btn-copycat-outline">Cancelar</button>
                <button type="button" @click="submitOrderMoney()" class="btn-copycat-primary gap-2"
                    :disabled="submitting || moneyChange < 0 || !moneyAmountReceived">
                    <i data-lucide="check" class="w-4 h-4" x-show="!submitting"></i>
                    <span x-show="!submitting">Confirmar Recebimento</span>
                    <span x-show="submitting">Processando...</span>
                </button>
            </div>
        </div>
    </div>
    {{-- Modal: Novo Consumidor Rápido --}}
    <div x-show="createCustomerModalOpen" x-cloak
        class="fixed inset-0 z-[150] flex items-center justify-center bg-black/50 backdrop-blur-sm"
        @click.self="createCustomerModalOpen = false">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-sm mx-4 p-6 space-y-4" @click.stop>
            <h3 class="text-lg font-bold text-foreground">Novo Consumidor</h3>

            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium mb-1">Nome *</label>
                    <input type="text" x-model="newCustomerName" x-ref="newCustomerNameInput"
                        class="w-full rounded-lg border-input bg-background px-3 py-2 text-sm"
                        placeholder="Nome do consumidor" @keydown.enter="submitCreateCustomer()">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Telefone</label>
                    <input type="text" x-model="newCustomerPhone"
                        class="w-full rounded-lg border-input bg-background px-3 py-2 text-sm"
                        placeholder="Ex: 71 99999-9999" @keydown.enter="submitCreateCustomer()">
                </div>
            </div>

            <div class="flex gap-2 pt-2">
                <button type="button" @click="createCustomerModalOpen = false"
                    class="flex-1 px-4 py-2 rounded-lg border border-input hover:bg-muted text-sm font-medium">
                    Cancelar
                </button>
                <button type="button" @click="submitCreateCustomer()" :disabled="creatingCustomer"
                    class="flex-1 px-4 py-2 rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 text-sm font-medium flex items-center justify-center gap-2">
                    <span x-show="!creatingCustomer">Salvar</span>
                    <i x-show="creatingCustomer" data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>



@push('scripts')
    <script>
        // Definir componente Alpine.js para o modal Nova Encomenda
        document.addEventListener('alpine:init', () => {
            Alpine.data('novaEncomendaModal', () => ({
                // Estado
                open: false,
                submitting: false,
                pixOpen: false,
                pixOption: 'qr',
                pixQrOpen: false,
                pixQrData: null,
                pixQrOrderId: null,
                pixQrSent: false,
                sendingPixWhatsApp: false,
                confirmingPixPayment: false,

                // Novo Cliente Rápido
                createCustomerModalOpen: false,
                creatingCustomer: false,
                newCustomerName: '',
                newCustomerPhone: '',

                // Cliente
                customerId: null,
                customerName: '',
                customerPhone: '',
                customerEmail: '',
                customerSuggestions: [],
                customerIsWholesale: false,

                // Dinheiro
                moneyModalOpen: false,
                moneyAmountReceived: '',
                moneyChange: 0,

                // Entrega
                deliveryAddress: '',
                deliveryNumber: '',
                deliveryAddressObservation: '',
                deliveryNeighborhood: '',
                deliveryCep: '',
                deliveryInstructions: '',
                deliveryFee: '',
                deliveryType: 'delivery',
                discountAmount: 0,
                paymentMethod: 'fiado',
                create_as_paid: false,
                addressId: null,

                // Produtos
                sourceLista: true,
                allowAvulso: false,
                items: [],
                currentItem: {
                    productId: null,
                    productDisplay: '',
                    name: '',
                    price: '',
                    quantity: 1,
                    observation: '',
                    variantId: null,
                    variantName: '',
                    hasVariants: false,
                    variants: [],
                    dropdownOpen: false,
                    suggestions: [],
                    productsFetched: false
                },
                deliveryOffHours: false,
                deliverySlotsList: [],
                selectedDeliveryDate: '',
                selectedDeliverySlot: '',
                selectedDeliveryAt: '',
                deliveryDate: new Date().toISOString().slice(0, 10),
                deliveryTime: '',

                // Métodos principais
                close() {
                    this.open = false;
                    this.resetForm();
                },

                resetForm() {
                    this.customerId = null;
                    this.customerName = '';
                    this.customerPhone = '';
                    this.items = [];
                    this.clearCurrentItem();
                },

                clearCurrentItem() {
                    this.currentItem = {
                        productId: null,
                        productDisplay: '',
                        name: '',
                        price: '',
                        quantity: 1,
                        observation: '',
                        variantId: null,
                        variantName: '',
                        hasVariants: false,
                        variants: [],
                        dropdownOpen: false,
                        suggestions: [],
                        productsFetched: false
                    };
                },

                // Buscar clientes
                async searchCustomers() {
                    if (!this.customerName || this.customerName.length < 2) {
                        this.customerSuggestions = [];
                        return;
                    }

                    try {
                        const response = await fetch(`{{ route('dashboard.pdv.customers.search') }}?q=${encodeURIComponent(this.customerName)}`);
                        const data = await response.json();
                        this.customerSuggestions = data.customers || [];
                    } catch (error) {
                        console.error('Erro ao buscar clientes:', error);
                        this.customerSuggestions = [];
                    }
                },

                selectCustomer(customer) {
                    this.customerId = customer.id;
                    this.customerName = customer.name;
                    this.customerPhone = customer.phone || '';
                    this.customerEmail = customer.email || '';
                    this.customerIsWholesale = customer.is_wholesale || false;

                    // Preencher endereço se existir
                    if (customer.address || customer.zip_code) {
                        this.deliveryAddress = customer.address || '';
                        this.deliveryNumber = customer.number || '';
                        this.deliveryAddressObservation = customer.complement || customer.address_reference || ''; // Tenta pegar complemento ou referência
                        this.deliveryNeighborhood = customer.neighborhood || '';
                        this.deliveryCep = customer.zip_code || '';
                        this.addressId = customer.address_id || null;
                        // Se preencheu o CEP, disparar cálculo de taxa
                        if (this.deliveryCep) {
                            this.calculateDeliveryFee();
                        }
                    }

                    if (this.customerId) {
                        this.fetchModalProducts(); // Recarrega produtos para aplicar preços de revenda se necessário
                    }
                    this.customerSuggestions = [];
                    setTimeout(() => { if (window.lucide) window.lucide.createIcons(); }, 50);
                },

                resetCustomerSelection() {
                    this.customerId = null;
                    this.customerName = '';
                    this.customerPhone = '';
                    this.customerEmail = '';
                    this.customerIsWholesale = false;
                    this.addressId = null;
                    this.deliveryAddress = '';
                    this.deliveryNumber = '';
                    this.deliveryAddressObservation = '';
                    this.deliveryNeighborhood = '';
                    this.deliveryCep = '';
                    this.customerSuggestions = [];
                    setTimeout(() => { if (window.lucide) window.lucide.createIcons(); }, 50);
                },

                // Buscar produtos
                async fetchModalProducts(query = '') {
                    try {
                        let url = '{{ route("dashboard.pdv.products.search") }}';
                        const params = new URLSearchParams();

                        if (query) params.append('q', query);
                        if (this.customerId) params.append('customer_id', this.customerId);

                        if (params.toString()) {
                            url += '?' + params.toString();
                        }

                        const response = await fetch(url);
                        const data = await response.json();
                        this.currentItem.suggestions = data.products || [];
                        this.currentItem.productsFetched = true;
                    } catch (error) {
                        console.error('Erro ao buscar produtos:', error);
                    }
                },

                async loadProductsOnFocus() {
                    // Sempre recarrega se houver cliente para garantir preços corretos (revenda)
                    if (!this.currentItem.productsFetched || this.customerId) {
                        await this.fetchModalProducts(this.currentItem.productDisplay);
                    }
                    this.currentItem.dropdownOpen = true;
                },

                debounceProductSearch() {
                    this.currentItem.dropdownOpen = true;
                    // Debounce manual
                    if (this.productSearchTimer) clearTimeout(this.productSearchTimer);
                    this.productSearchTimer = setTimeout(() => {
                        this.fetchModalProducts(this.currentItem.productDisplay);
                    }, 300);
                },

                selectProduct(product) {
                    console.log('Produto Selecionado:', product);
                    console.log('Variantes:', product.variants);

                    this.currentItem.productId = product.id;
                    this.currentItem.productDisplay = product.name;
                    this.currentItem.name = product.name;
                    this.currentItem.price = product.price ? parseFloat(product.price).toFixed(2).replace('.', ',') : '';

                    // Verificação robusta
                    const hasVars = (product.has_variants === true || product.has_variants === 1) ||
                        (Array.isArray(product.variants) && product.variants.length > 0);

                    this.currentItem.hasVariants = hasVars;
                    this.currentItem.variants = product.variants || [];
                    this.currentItem.variantId = null;
                    this.currentItem.variantName = '';
                    this.currentItem.dropdownOpen = false;

                    // Se tiver variações, limpa o preço para forçar a escolha da variação
                    if (this.currentItem.hasVariants) {
                        console.log('Tem variantes. Limpando preço para forçar seleção.');
                        this.currentItem.price = '';
                    } else {
                        console.log('Não tem variantes (ou não detectado).');
                    }
                },

                selectVariant(v) {
                    this.currentItem.variantId = v.id;
                    this.currentItem.variantName = v.name;
                    this.currentItem.price = v.price ? parseFloat(v.price).toFixed(2).replace('.', ',') : '';
                },

                closeProductDropdown() {
                    this.currentItem.dropdownOpen = false;
                },

                minVariantPrice(product) {
                    if (!product.variants || product.variants.length === 0) {
                        return product.price || 0;
                    }
                    return Math.min(...product.variants.map(v => parseFloat(v.price) || 0));
                },

                // Adicionar item ao resumo
                addCurrentToResumo() {
                    if (this.sourceLista && !this.currentItem.productId) {
                        alert('Por favor, selecione um produto da lista');
                        return;
                    }
                    if (!this.sourceLista && !this.currentItem.name) {
                        alert('Por favor, digite o nome do produto');
                        return;
                    }
                    if (this.currentItem.hasVariants && !this.currentItem.variantId) {
                        alert('Este produto possui variações. Por favor, selecione uma antes de adicionar.');
                        return;
                    }
                    if (!this.currentItem.price || this.currentItem.price === '0,00') {
                        alert('Por favor, informe o preço do produto');
                        return;
                    }

                    // Preparar nome final (Produto + Variante)
                    let finalName = this.currentItem.name;
                    if (this.currentItem.variantName) {
                        finalName += ' (' + this.currentItem.variantName + ')';
                    }

                    this.items.push({
                        itemId: Date.now(),
                        productId: this.currentItem.productId,
                        variantId: this.currentItem.variantId,
                        name: this.currentItem.name,
                        variantName: this.currentItem.variantName,
                        price: this.currentItem.price,
                        quantity: this.currentItem.quantity,
                        observation: this.currentItem.observation
                    });

                    this.clearCurrentItem();
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                },

                // Buscar slots de entrega
                async fetchDeliverySlots() {
                    try {
                        const response = await fetch('{{ route("dashboard.orders.deliverySlots") }}');
                        const data = await response.json();
                        this.deliverySlotsList = data.dates || [];
                        // Se não houver slots automáticos, ativar modo manual/fora de horário por padrão
                        if (this.deliverySlotsList.length === 0) {
                            this.deliveryOffHours = true;
                        }
                    } catch (error) {
                        console.error('Erro ao buscar slots:', error);
                        this.deliveryOffHours = true;
                    }
                },

                // Calcular taxa de entrega
                async calculateDeliveryFee() {
                    const cep = String(this.deliveryCep || '').replace(/\D/g, '');
                    if (cep.length < 8) return;

                    try {
                        const response = await fetch('{{ route("dashboard.pdv.calculateDeliveryFee") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                cep: cep,
                                subtotal: this.subtotal,
                                customer_id: this.customerId
                            })
                        });

                        const data = await response.json();
                        if (data.success) {
                            this.deliveryFee = parseFloat(data.delivery_fee.replace(',', '.')).toFixed(2).replace('.', ',');

                            // Preencher endereço se retornado
                            if (data.address) {
                                // Se o campo de endereço estiver vazio ou não modificado manualmente, preenche
                                // Assumindo que o usuario quer o endereço do CEP
                                let fullAddress = data.address.street;
                                if (fullAddress) {
                                    this.deliveryAddress = fullAddress; // Logradouro
                                }

                                if (data.address.neighborhood) {
                                    this.deliveryNeighborhood = data.address.neighborhood;
                                }

                                // Se houver campos de cidade/estado no form (não vi no view_file anterior, mas se tiver, preencheria)
                                // this.deliveryCity = data.address.city;
                                // this.deliveryState = data.address.state;
                            }
                        } else {
                            // Se falhar (ex: CEP invalido), pode zerar ou avisar
                            console.warn(data.message);
                        }
                    } catch (error) {
                        console.error('Erro ao calcular taxa:', error);
                    }
                },

                // Cálculos
                get subtotal() {
                    return this.items.reduce((sum, item) => {
                        const price = parseFloat(String(item.price || '0').replace(',', '.')) || 0;
                        const quantity = parseInt(item.quantity, 10) || 1;
                        return sum + (price * quantity);
                    }, 0);
                },

                get total() {
                    const fee = parseFloat(String(this.deliveryFee || '0').replace(',', '.')) || 0;
                    return this.subtotal + fee;
                },

                formatPrice(value) {
                    return parseFloat(value || 0).toFixed(2).replace('.', ',');
                },

                // Pagamento
                openPix(option) {
                    if (!this.customerName || this.items.length === 0) {
                        alert('Preencha cliente e adicione itens ao pedido');
                        return;
                    }
                    this.pixOption = option;
                    this.pixOpen = true;
                },

                closePix() {
                    this.pixOpen = false;
                },

                async confirmPixAndSubmit() {
                    await this.submitOrder(this.pixOption === 'qr' ? 'pix_qr' : 'pix_whatsapp');
                },

                closePixQr() {
                    this.pixQrOpen = false;
                    window.location.reload();
                },

                copyPixCode() {
                    const input = document.getElementById('pix-copy-paste-nova');
                    if (input) {
                        input.select();
                        document.execCommand('copy');
                        alert('Código PIX copiado!');
                    }
                },

                async sendPixWhatsAppFromQr() {
                    if (!this.pixQrOrderId) return;

                    this.sendingPixWhatsApp = true;
                    try {
                        const response = await fetch(`/pdv/send-pix-whatsapp/${this.pixQrOrderId}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });

                        const data = await response.json();
                        if (data.success) {
                            this.pixQrSent = true;
                            alert('Cobrança enviada com sucesso!');
                        } else {
                            alert('Erro ao enviar cobrança');
                        }
                    } catch (error) {
                        console.error('Erro:', error);
                        alert('Erro ao enviar cobrança');
                    } finally {
                        this.sendingPixWhatsApp = false;
                    }
                },

                async confirmPixPaymentFromQr() {
                    if (!this.pixQrOrderId) return;

                    this.confirmingPixPayment = true;
                    try {
                        const response = await fetch('{{ route("dashboard.pdv.confirmPaymentSilent", ["order" => ":id"]) }}'.replace(':id', this.pixQrOrderId), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });

                        const data = await response.json();
                        if (data.success) {
                            alert('Pagamento confirmado!');
                            window.location.reload();
                        } else {
                            alert('Erro ao confirmar pagamento');
                        }
                    } catch (error) {
                        console.error('Erro:', error);
                        alert('Erro ao confirmar pagamento');
                    } finally {
                        this.confirmingPixPayment = false;
                    }
                },

                // Formatar dados para o backend
                getFormattedData() {
                    let scheduledAt = null;
                    if (this.deliveryOffHours) {
                        if (this.selectedDeliveryAt) {
                            scheduledAt = this.selectedDeliveryAt.replace('T', ' ') + ':00';
                        }
                    } else if (this.selectedDeliverySlot) {
                        scheduledAt = this.selectedDeliverySlot + ':00';
                    }

                    let finalAddress = this.deliveryAddress;
                    if (this.deliveryNumber) {
                        finalAddress += ', ' + this.deliveryNumber;
                    }

                    let finalInstructions = this.deliveryInstructions || '';
                    if (this.deliveryAddressObservation) {
                        finalInstructions = finalInstructions ? (finalInstructions + ' | ' + this.deliveryAddressObservation) : this.deliveryAddressObservation;
                    }

                    return {
                        customer_id: this.customerId,
                        items: this.items.map(item => ({
                            product_id: item.productId,
                            variant_id: item.variantId,
                            name: item.name,
                            price: parseFloat(String(item.price || '0').replace(',', '.')),
                            quantity: item.quantity,
                            notes: item.observation
                        })),
                        payment_method: this.paymentMethod,
                        delivery_type: this.deliveryType,
                        address_id: this.deliveryType === 'delivery' ? this.addressId : null,
                        scheduled_delivery_at: scheduledAt,
                        delivery_instructions: finalInstructions,
                        delivery_fee: parseFloat(String(this.deliveryFee || '0').replace(',', '.')),
                        discount_amount: parseFloat(String(this.discountAmount || '0').replace(',', '.')),
                        // Fallbacks para campos que o backend possa esperar com nomes diferentes
                        customer_name: this.customerName,
                        customer_phone: this.customerPhone,
                        delivery_address: finalAddress,
                        delivery_neighborhood: this.deliveryNeighborhood,
                        delivery_cep: this.deliveryCep,
                        create_as_paid: this.create_as_paid
                    };
                },

                // Submeter pedido
                async submitOrder(paymentType = false) {
                    if (!this.customerName || this.items.length === 0) {
                        alert('Preencha cliente e adicione itens ao pedido');
                        return;
                    }

                    this.submitting = true;

                    const orderData = this.getFormattedData();
                    if (paymentType) {
                        // Tratar tipos de pagamento PIX especiais
                        if (paymentType === 'pix_qr') {
                            orderData.payment_method = 'pix';
                            orderData.pix_option = 'display_qr';
                        } else if (paymentType === 'pix_whatsapp') {
                            orderData.payment_method = 'pix';
                            orderData.pix_option = 'send_whatsapp';
                        } else {
                            orderData.payment_method = paymentType;
                        }
                        orderData.send_payment_link = (paymentType !== 'fiado' && !this.create_as_paid);
                    }


                    try {
                        const response = await fetch('{{ route("dashboard.pdv.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(orderData)
                        });

                        if (!response.ok) {
                            const errorText = await response.text();
                            console.error('Erro do servidor:', errorText);
                            throw new Error('Erro ao salvar pedido no servidor (Status: ' + response.status + ').');
                        }

                        const data = await response.json();

                        if (data.success) {
                            if (paymentType === 'pix_qr' && data.order_id) {
                                try {
                                    const pixURL = `/pdv/pix-qr/${data.order_id}`;
                                    const pixResponse = await fetch(pixURL);
                                    if (!pixResponse.ok) throw new Error('Falha ao obter QR Code');

                                    const pixData = await pixResponse.json();
                                    this.pixQrData = pixData;
                                    this.pixQrOrderId = data.order_id;
                                    this.pixQrOpen = true;
                                    setTimeout(() => { if (window.lucide) window.lucide.createIcons(); }, 100);
                                } catch (pixErr) {
                                    console.error('Erro PIX:', pixErr);
                                    alert('Pedido criado! Mas houve um erro ao gerar o QR Code. Você pode vê-lo nos detalhes do pedido.');
                                    window.location.reload();
                                }
                            } else {
                                alert(data.message || 'Pedido registrado com sucesso!');
                                window.location.reload();
                            }
                        } else {
                            alert('Erro: ' + (data.message || 'Falha ao processar pedido'));
                        }
                    } catch (error) {
                        console.error('Erro ao criar pedido:', error);
                        alert('Erro ao criar pedido');
                    } finally {
                        this.submitting = false;
                    }
                },

                async submitOrderWithLink() {
                    await this.submitOrder('credit_card');
                },

                async submitOrderFiado() {
                    await this.submitOrder('fiado');
                },

                openMoneyModal() {
                    if (!this.customerName || this.items.length === 0) {
                        alert('Preencha cliente e adicione itens ao pedido');
                        return;
                    }
                    this.moneyModalOpen = true;
                    // Reset
                    this.moneyAmountReceived = '';
                    this.moneyChange = -this.total;

                    setTimeout(() => {
                        if (window.lucide) window.lucide.createIcons();
                        // Focar no input talvez?
                    }, 100);
                },

                closeMoneyModal() {
                    this.moneyModalOpen = false;
                    this.moneyAmountReceived = '';
                    this.moneyChange = 0;
                },

                calculateChange() {
                    const total = this.total;
                    const received = parseFloat(this.moneyAmountReceived.replace('.', '').replace(',', '.') || 0);
                    this.moneyChange = received - total;
                },

                async submitOrderMoney() {
                    this.create_as_paid = true;
                    // Injeta o valor do troco na observação se desejar, ou apenas salva.
                    // Como não tenho campo de troco, posso adicionar na observação do primeiro item ou geral? 
                    // O pedido não tem obs geral exposta no model, mas tem delivery_instructions.
                    // Melhor não mexer onde não devo. Apenas marco como pago.

                    await this.submitOrder('money');
                    this.closeMoneyModal();
                },

                saveAllowAvulso() {
                    localStorage.setItem('allowAvulso', this.allowAvulso);
                },

                // Função de criar cliente
                async submitCreateCustomer() {
                    if (!this.newCustomerName.trim()) {
                        alert('Nome é obrigatório');
                        return;
                    }

                    this.creatingCustomer = true;

                    try {
                        const response = await fetch('{{ route("dashboard.customers.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                name: this.newCustomerName,
                                phone: this.newCustomerPhone
                            })
                        });

                        const data = await response.json();

                        if (data.success && data.customer) {
                            // Selecionar o cliente criado
                            this.selectCustomer(data.customer);

                            // Limpar e fechar
                            this.newCustomerName = '';
                            this.newCustomerPhone = '';
                            this.createCustomerModalOpen = false;

                            // Feedback
                            // alert('Cliente cadastrado com sucesso!');
                        } else {
                            alert('Erro ao cadastrar cliente: ' + (data.message || 'Erro desconhecido'));
                        }
                    } catch (error) {
                        console.error(error);
                        alert('Erro ao cadastrar cliente');
                    } finally {
                        this.creatingCustomer = false;
                    }
                },

                init() {
                    this.allowAvulso = localStorage.getItem('allowAvulso') === 'true';

                    // Watchers para recalcular taxa de entrega
                    this.$watch('deliveryCep', (value) => {
                        if (value && value.replace(/\D/g, '').length === 8) {
                            this.calculateDeliveryFee();
                        }
                    });

                    this.$watch('subtotal', (value) => {
                        if (this.deliveryCep && this.deliveryCep.replace(/\D/g, '').length === 8) {
                            this.calculateDeliveryFee();
                        }
                    });
                }
            }));
        });
    </script>
@endpush