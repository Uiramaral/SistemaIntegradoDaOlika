@extends('dashboard.layouts.app')

@section('page_title', 'Clientes')
@section('page_subtitle', 'Gerenciamento de clientes')

@section('page_actions')
    {{-- Botões removidos conforme solicitado --}}
@endsection

@section('content')
    <div class="bg-card rounded-xl border border-border animate-fade-in overflow-hidden max-w-full" id="customers-page"
        x-data="{ ...customersLiveSearch({{ json_encode(request('q') ?? '') }}), newCustomerModalOpen: false, submitting: false }">
        <!-- Card Header: Busca, Filtros e Botão -->
        <div class="p-4 sm:p-6 border-b border-border">
            <form id="customers-filter-form" method="GET" action="{{ route('dashboard.customers.index') }}"
                class="space-y-4">
                
                {{-- Linha Superior: Busca e Ações Principais --}}
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <i data-lucide="search"
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none"></i>
                        <input type="text" name="q" x-model="search"
                            @input.debounce.500ms="$event.target.form && $event.target.form.submit()"
                            placeholder="Buscar por nome ou telefone..."
                            class="form-input pl-10 h-10 bg-muted/30 border-transparent focus:bg-white transition-all text-sm rounded-lg w-full"
                            autocomplete="off">
                    </div>
                    
                    <button type="button" @click="newCustomerModalOpen = true"
                        class="btn-primary gap-2 h-10 px-4 rounded-lg shadow-sm whitespace-nowrap inline-flex items-center justify-center">
                        <i data-lucide="plus" class="h-4 w-4 text-white"></i>
                        <span class="font-bold text-white text-sm">Novo Consumidor</span>
                    </button>
                </div>

                {{-- Barra de Filtros: Horizontal no Desktop, Grid no Mobile --}}
                <div class="flex flex-wrap lg:flex-nowrap items-center gap-2">
                    <div class="flex items-center gap-2 text-muted-foreground mr-1 hidden lg:flex shrink-0">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        <span class="text-[10px] font-bold uppercase tracking-wider">Filtros</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 lg:flex items-center gap-2 w-full lg:w-auto">
                        {{-- Filtro Compras --}}
                        <select name="compras" onchange="this.form.submit()"
                            class="h-9 rounded-lg border border-input bg-muted/20 text-xs px-2 focus:ring-2 focus:ring-primary/20 focus:border-primary w-full lg:w-32">
                            <option value="" {{ trim((string) (request('compras') ?? '')) === '' ? 'selected' : '' }}>Compras (Todas)</option>
                            <option value="com" {{ trim((string) (request('compras') ?? '')) === 'com' ? 'selected' : '' }}>Com compras</option>
                            <option value="sem" {{ trim((string) (request('compras') ?? '')) === 'sem' ? 'selected' : '' }}>Sem compras</option>
                        </select>

                        {{-- Filtro Fiado --}}
                        <select name="fiado" onchange="this.form.submit()"
                            class="h-9 rounded-lg border border-input bg-muted/20 text-xs px-2 focus:ring-2 focus:ring-primary/20 focus:border-primary w-full lg:w-28">
                            <option value="" {{ trim((string) (request('fiado') ?? '')) === '' ? 'selected' : '' }}>Fiado (Todos)</option>
                            <option value="com" {{ trim((string) (request('fiado') ?? '')) === 'com' ? 'selected' : '' }}>Com fiado</option>
                            <option value="sem" {{ trim((string) (request('fiado') ?? '')) === 'sem' ? 'selected' : '' }}>Sem fiado</option>
                        </select>

                        {{-- Filtro Tipo --}}
                        <select name="revenda" onchange="this.form.submit()"
                            class="h-9 rounded-lg border border-input bg-muted/20 text-xs px-2 focus:ring-2 focus:ring-primary/20 focus:border-primary w-full lg:w-28">
                            <option value="" {{ trim((string) (request('revenda') ?? '')) === '' ? 'selected' : '' }}>Tipo (Todos)</option>
                            <option value="1" {{ trim((string) (request('revenda') ?? '')) === '1' ? 'selected' : '' }}>Revenda</option>
                            <option value="0" {{ trim((string) (request('revenda') ?? '')) === '0' ? 'selected' : '' }}>Consumidor</option>
                        </select>

                        {{-- Ordenação --}}
                        <select name="ordenar" onchange="this.form.submit()"
                            class="h-9 rounded-lg border border-input bg-muted/20 text-xs px-2 focus:ring-2 focus:ring-primary/20 focus:border-primary w-full lg:w-32">
                            <option value="nome" {{ (request('ordenar') ?? 'nome') === 'nome' ? 'selected' : '' }}>Nome A–Z</option>
                            <option value="ultimo" {{ (request('ordenar') ?? 'nome') === 'ultimo' ? 'selected' : '' }}>Último pedido</option>
                            <option value="gasto" {{ (request('ordenar') ?? 'nome') === 'gasto' ? 'selected' : '' }}>Total gasto</option>
                            <option value="pedidos" {{ (request('ordenar') ?? 'nome') === 'pedidos' ? 'selected' : '' }}>Mais pedidos</option>
                        </select>
                    </div>

                    {{-- Botão Limpar --}}
                    <a href="{{ route('dashboard.customers.index') }}"
                        class="h-9 px-3 rounded-lg text-xs font-medium gap-2 bg-muted/30 border border-input hover:bg-muted inline-flex items-center justify-center shrink-0 ml-auto lg:ml-1"
                        title="Limpar filtros">
                        <i data-lucide="eraser" class="w-3.5 h-3.5"></i>
                        <span>Limpar</span>
                    </a>
                </div>
            </form>
        </div>

        <!-- Customers Grid -->
        <div class="p-4 sm:p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
                @forelse($customers as $customer)
                    @php
                        $name = $customer->name ?? 'Sem nome';
                        // Formatar nome: apenas primeiro e último nome
                        $nameParts = explode(' ', trim($name));
                        if (count($nameParts) > 1) {
                            $displayName = $nameParts[0] . ' ' . end($nameParts);
                        } else {
                            $displayName = $name;
                        }



                        $ordersCount = $customer->orders_count ?? $customer->total_orders ?? 0;
                        $totalSpent = $customer->total_spent ?? 0;
                        $lastOrderAt = $customer->last_order_at ? \Carbon\Carbon::parse($customer->last_order_at)->format('d/m/Y') : '—';
                        $searchName = mb_strtolower($name, 'UTF-8');
                        $searchPhone = preg_replace('/\D/', '', $customer->phone ?? '');
                    @endphp
                    <div class="customer-card bg-white border border-border rounded-xl p-3 sm:p-4 hover:shadow-md transition-all"
                        data-search-name="{{ $searchName }}" data-search-phone="{{ $searchPhone }}" x-show="matchesCard($el)">
                        <!-- Header: Avatar, Name, Orders, Actions -->
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center gap-2 min-w-0 flex-1">

                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('dashboard.customers.show', $customer->id) }}" class="block group">
                                        <h3
                                            class="font-semibold text-foreground text-sm group-hover:text-primary transition-colors truncate">
                                            {{ $displayName }}
                                        </h3>
                                        <p class="text-xs text-muted-foreground mt-0.5">{{ $ordersCount }} pedidos</p>
                                    </a>
                                </div>
                            </div>
                            <div class="flex items-center gap-0.5 flex-shrink-0">
                                <a href="{{ route('dashboard.customers.edit', $customer->id) }}"
                                    class="inline-flex items-center justify-center h-7 w-7 rounded-md hover:bg-muted transition-colors text-muted-foreground hover:text-foreground"
                                    title="Editar">
                                    <i data-lucide="edit" class="h-3.5 w-3.5"></i>
                                </a>
                                <form action="{{ route('dashboard.customers.destroy', $customer->id) }}" method="POST"
                                    class="inline" onsubmit="return confirm('Tem certeza que deseja excluir este cliente?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center justify-center h-7 w-7 rounded-md hover:bg-destructive/10 transition-colors text-muted-foreground hover:text-destructive"
                                        title="Excluir">
                                        <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Contact: só telefone — desktop (lg+) -->
                        <div class="mb-2 hidden lg:block">
                            @if($customer->phone)
                                <div class="flex items-center gap-1.5 text-xs text-muted-foreground min-w-0">
                                    <i data-lucide="phone" class="w-3.5 h-3.5 flex-shrink-0"></i>
                                    <span class="truncate">{{ $customer->phone }}</span>
                                </div>
                            @else
                                <span class="text-xs text-muted-foreground/60">Sem contato</span>
                            @endif
                        </div>

                        <!-- Footer: Total Gasto, Telefone, Último Pedido -->
                        <div class="pt-2 border-t border-border">
                            <!-- Desktop: Total | Último em linha (telefone na seção Contact acima) -->
                            <div class="hidden lg:flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] text-muted-foreground uppercase tracking-wide">Total gasto</p>
                                    <p class="text-sm font-bold text-primary mt-0.5">R$
                                        {{ number_format($totalSpent, 2, ',', '.') }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] text-muted-foreground uppercase tracking-wide">Último pedido</p>
                                    <p class="text-xs font-medium text-foreground mt-0.5">{{ $lastOrderAt }}</p>
                                </div>
                            </div>
                            <!-- Mobile/tablet: Gasto · Telefone · Último na MESMA LINHA (menos altura) -->
                            <div class="flex items-stretch gap-2 lg:hidden">
                                <div class="min-w-0 flex-1 overflow-hidden">
                                    <p class="text-xs text-muted-foreground/80 uppercase tracking-wide truncate">Gasto</p>
                                    <p class="text-sm font-bold text-primary truncate mt-0.5">R$
                                        {{ number_format($totalSpent, 2, ',', '.') }}
                                    </p>
                                </div>
                                <span class="text-muted-foreground/40 self-center shrink-0 text-sm">·</span>
                                <div class="min-w-0 flex-1 overflow-hidden">
                                    <p class="text-xs text-muted-foreground/80 uppercase tracking-wide truncate">Tel</p>
                                    @if($customer->phone)
                                        <a href="tel:{{ preg_replace('/\D/', '', $customer->phone) }}"
                                            class="text-sm font-medium text-foreground hover:text-primary truncate block mt-0.5">{{ $customer->phone }}</a>
                                    @else
                                        <span class="text-sm text-muted-foreground/60 mt-0.5 block">—</span>
                                    @endif
                                </div>
                                <span class="text-muted-foreground/40 self-center shrink-0 text-sm">·</span>
                                <div class="min-w-0 flex-1 overflow-hidden text-right">
                                    <p class="text-xs text-muted-foreground/80 uppercase tracking-wide truncate">Último</p>
                                    <p class="text-sm font-medium text-foreground truncate mt-0.5">{{ $lastOrderAt }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center text-muted-foreground py-12">
                        <div class="flex flex-col items-center gap-2">
                            <i data-lucide="inbox" class="w-12 h-12 opacity-20"></i>
                            <p class="text-sm">Nenhum cliente cadastrado</p>
                        </div>
                    </div>
                @endforelse
                @if($customers->count() > 0)
                    <div class="customer-filter-no-results col-span-full text-center text-muted-foreground py-8"
                        x-show="search && showNoResults" x-cloak x-transition>
                        <div class="flex flex-col items-center gap-2">
                            <i data-lucide="search-x" class="w-10 h-10 opacity-40"></i>
                            <p class="text-sm">Nenhum cliente nesta página para "<span x-text="search"></span>"</p>
                            <p class="text-xs">Use <strong>Filtrar</strong> para buscar em todos ou <strong>Limpar</strong> para
                                refazer.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Pagination -->
        <!-- Pagination -->
        @if(isset($customers) && method_exists($customers, 'links') && $customers->hasPages())
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-border bg-muted/20">
                {{ $customers->withQueryString()->links() }}
            </div>
        @endif

        {{-- Modal de Novo Consumidor --}}
        <div x-show="newCustomerModalOpen" x-cloak
            class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm"
            @click.self="newCustomerModalOpen = false" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">

            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl mx-4 overflow-hidden max-h-[90vh] flex flex-col"
                x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4">

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-border bg-muted/10 shrink-0">
                    <div>
                        <h3 class="text-lg font-bold text-foreground">Novo Consumidor</h3>
                        <p class="text-sm text-muted-foreground">Preencha os dados do novo consumidor</p>
                    </div>
                    <button type="button" @click="newCustomerModalOpen = false"
                        class="p-2 rounded-lg text-muted-foreground hover:bg-muted hover:text-foreground transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                {{-- Body (Scrollable) --}}
                <div class="p-6 overflow-y-auto">
                    <form action="{{ route('dashboard.customers.store') }}" method="POST" id="new-customer-form"
                        @submit="submitting = true">
                        @csrf
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <label for="name" class="text-sm font-medium leading-none">Nome <span
                                        class="text-destructive">*</span></label>
                                <input type="text" id="name" name="name" required
                                    class="input-copycat w-full h-10 rounded-lg px-3 border border-input focus:ring-2 focus:ring-primary/20 transition-all font-medium">
                            </div>

                            <div class="space-y-2">
                                <label for="email" class="text-sm font-medium leading-none">E-mail</label>
                                <input type="email" id="email" name="email"
                                    class="input-copycat w-full h-10 rounded-lg px-3 border border-input focus:ring-2 focus:ring-primary/20 transition-all">
                            </div>

                            <div class="space-y-2">
                                <label for="phone" class="text-sm font-medium leading-none">Telefone <span
                                        class="text-destructive">*</span></label>
                                <div class="relative">
                                    <i data-lucide="phone"
                                        class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground"></i>
                                    <input type="text" id="phone" name="phone" required
                                        class="input-copycat w-full pl-10 h-10 rounded-lg border border-input focus:ring-2 focus:ring-primary/20 transition-all font-medium"
                                        placeholder="(00) 00000-0000">
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label for="cpf" class="text-sm font-medium leading-none">CPF</label>
                                <div class="relative">
                                    <i data-lucide="credit-card"
                                        class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground"></i>
                                    <input type="text" id="cpf" name="cpf"
                                        class="input-copycat w-full pl-10 h-10 rounded-lg border border-input focus:ring-2 focus:ring-primary/20 transition-all"
                                        placeholder="000.000.000-00">
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label for="birth_date" class="text-sm font-medium leading-none">Data de Nascimento</label>
                                <input type="date" id="birth_date" name="birth_date"
                                    class="input-copycat w-full h-10 rounded-lg px-3 border border-input focus:ring-2 focus:ring-primary/20 transition-all">
                            </div>

                            <div class="space-y-2 flex items-end pb-2">
                                <label
                                    class="flex items-center gap-2 cursor-pointer p-2 border border-input rounded-lg hover:bg-muted/30 w-full transition-colors">
                                    <input type="checkbox" name="is_wholesale" value="1"
                                        class="rounded border-gray-300 text-primary focus:ring-primary h-4 w-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium">Consumidor Revenda / Parceiro</span>
                                        <span class="text-xs text-muted-foreground">Acesso a preços especiais</span>
                                    </div>
                                </label>
                            </div>

                            <div class="space-y-2 md:col-span-2 pt-2 border-t border-dashed">
                                <label for="cashback_balance"
                                    class="text-sm font-medium leading-none flex items-center gap-2">
                                    <i data-lucide="coins" class="w-4 h-4 text-yellow-500"></i>
                                    Saldo Inicial de Cashback (R$)
                                </label>
                                <input type="number" id="cashback_balance" name="cashback_balance" step="0.01" min="0"
                                    value="0.00"
                                    class="input-copycat w-full h-10 rounded-lg px-3 border border-input focus:ring-2 focus:ring-primary/20 transition-all max-w-[200px]">
                                <p class="text-xs text-muted-foreground">Valor será creditado automaticamente.</p>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 border-t border-border bg-muted/10 flex justify-end gap-2 shrink-0">
                    <button type="button" @click="newCustomerModalOpen = false"
                        class="btn-outline h-10 px-4 rounded-lg">Cancelar</button>
                    <button type="submit" form="new-customer-form" class="btn-primary h-10 px-4 rounded-lg gap-2"
                        :disabled="submitting">
                        <i x-show="!submitting" data-lucide="check" class="w-4 h-4"></i>
                        <i x-show="submitting" data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                        <span x-show="!submitting">Criar Consumidor</span>
                        <span x-show="submitting">Salvando...</span>
                    </button>
                </div>
            </div>
        </div>



        @push('styles')
            <style>
                [x-cloak] {
                    display: none !important
                }
            </style>
        @endpush

        @push('scripts')
            <script>
                document.addEventListener('alpine:init', function () {
                    Alpine.data('customersLiveSearch', function (initialQ) {
                        return {
                            search: (typeof initialQ === 'string' ? initialQ : '') || '',
                            showNoResults: false,
                            newCustomerModalOpen: false,
                            submitting: false,

                            init: function () {
                                var self = this;
                                function updateNoResults() {
                                    self.$nextTick(function () {
                                        var root = document.getElementById('customers-page');
                                        var cards = root ? root.querySelectorAll('.customer-card') : [];
                                        var visible = 0;
                                        cards.forEach(function (el) {
                                            if (self.matchesCard(el)) visible++;
                                        });
                                        self.showNoResults = self.search.trim() !== '' && visible === 0;
                                    });
                                }
                                this.$watch('search', updateNoResults);
                                updateNoResults();
                            },

                            matchesCard: function (el) {
                                var q = this.search.trim().toLowerCase();
                                if (!q) return true;
                                var qDigits = q.replace(/\D/g, '');
                                var name = (el.getAttribute('data-search-name') || '').toLowerCase();
                                var phone = (el.getAttribute('data-search-phone') || '').replace(/\D/g, '');
                                var nameNorm = name.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                                var qNorm = q.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                                if (name.includes(q) || nameNorm.includes(qNorm)) return true;
                                if (qDigits.length >= 2 && phone.indexOf(qDigits) !== -1) return true;
                                return false;
                            }
                        };
                    });
                });
            </script>
        @endpush
@endsection