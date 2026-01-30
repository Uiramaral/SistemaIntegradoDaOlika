@extends('dashboard.layouts.app')

@section('page_title', 'Clientes')
@section('page_subtitle', 'Gerenciamento de clientes')

@section('page_actions')
    {{-- Botões removidos conforme solicitado --}}
@endsection

@section('content')
<div class="bg-card rounded-xl border border-border animate-fade-in overflow-hidden max-w-full" id="customers-page"
     x-data="customersLiveSearch({{ json_encode(request('q') ?? '') }})">
    <!-- Card Header: Busca, Filtros e Botão -->
    <div class="p-4 sm:p-6 border-b border-border">
        <form id="customers-filter-form" method="GET" action="{{ route('dashboard.customers.index') }}" class="flex flex-col lg:flex-row lg:items-center gap-3 lg:gap-3">
            {{-- Busca: ao digitar envia ao servidor após 500ms; filtros enviam ao mudar --}}
            <div class="relative w-full lg:flex-1 lg:min-w-[200px] lg:max-w-sm order-1">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none"></i>
                <input type="text"
                       name="q"
                       x-model="search"
                       @input.debounce.500ms="$event.target.form && $event.target.form.submit()"
                       placeholder="Buscar por nome ou telefone..."
                       class="form-input pl-10 h-10 bg-muted/30 border-transparent focus:bg-white transition-all text-sm rounded-lg w-full"
                       autocomplete="off">
            </div>
            {{-- Filtros: ao mudar já envia; Limpar + Filtrar --}}
            <div class="grid grid-cols-2 lg:flex lg:flex-nowrap items-center gap-2 lg:shrink-0 order-2 w-full lg:w-auto">
                <select name="fiado" onchange="this.form.submit()" class="h-10 rounded-lg border border-input bg-muted/30 text-sm px-3 focus:ring-2 focus:ring-primary/20 focus:border-primary w-full lg:w-[130px] shrink-0" title="Filtrar por fiado">
                    <option value="" {{ trim((string)(request('fiado') ?? '')) === '' ? 'selected' : '' }}>Todos (fiado)</option>
                    <option value="com" {{ trim((string)(request('fiado') ?? '')) === 'com' ? 'selected' : '' }}>Com fiado</option>
                    <option value="sem" {{ trim((string)(request('fiado') ?? '')) === 'sem' ? 'selected' : '' }}>Sem fiado</option>
                </select>
                <select name="revenda" onchange="this.form.submit()" class="h-10 rounded-lg border border-input bg-muted/30 text-sm px-3 focus:ring-2 focus:ring-primary/20 focus:border-primary w-full lg:w-[130px] shrink-0" title="Filtrar por tipo">
                    <option value="" {{ trim((string)(request('revenda') ?? '')) === '' ? 'selected' : '' }}>Todos (tipo)</option>
                    <option value="1" {{ trim((string)(request('revenda') ?? '')) === '1' ? 'selected' : '' }}>Revenda</option>
                    <option value="0" {{ trim((string)(request('revenda') ?? '')) === '0' ? 'selected' : '' }}>Consumidor</option>
                </select>
                <select name="ordenar" onchange="this.form.submit()" class="h-10 rounded-lg border border-input bg-muted/30 text-sm px-3 focus:ring-2 focus:ring-primary/20 focus:border-primary col-span-2 lg:col-span-1 w-full lg:w-[140px] shrink-0">
                    <option value="nome" {{ (request('ordenar') ?? 'nome') === 'nome' ? 'selected' : '' }}>Nome A–Z</option>
                    <option value="ultimo" {{ (request('ordenar') ?? 'nome') === 'ultimo' ? 'selected' : '' }}>Último pedido</option>
                    <option value="gasto" {{ (request('ordenar') ?? 'nome') === 'gasto' ? 'selected' : '' }}>Total gasto</option>
                    <option value="pedidos" {{ (request('ordenar') ?? 'nome') === 'pedidos' ? 'selected' : '' }}>Mais pedidos</option>
                </select>
                <div class="col-span-2 lg:col-span-1 flex flex-row gap-2">
                    <a href="{{ route('dashboard.customers.index') }}" class="btn-outline h-10 px-3 rounded-lg text-sm font-medium gap-1.5 shrink-0 flex-1 lg:flex-initial justify-center inline-flex items-center min-w-0" title="Limpar filtros e busca">
                        <i data-lucide="eraser" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate">Limpar</span>
                    </a>
                    <button type="submit" class="btn-outline h-10 px-3 rounded-lg text-sm font-medium gap-1.5 shrink-0 flex-1 lg:flex-initial">
                        <i data-lucide="filter" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate">Filtrar</span>
                    </button>
                </div>
            </div>
            <a href="{{ route('dashboard.customers.create') }}" class="btn-primary gap-2 h-10 px-4 rounded-lg shadow-sm shrink-0 w-full lg:w-auto lg:ml-auto justify-center order-3">
                <i data-lucide="plus" class="h-4 w-4 text-white"></i>
                <span class="font-bold text-white text-sm">Novo Cliente</span>
            </a>
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
                     data-search-name="{{ $searchName }}"
                     data-search-phone="{{ $searchPhone }}"
                     x-show="matchesCard($el)">
                    <!-- Header: Avatar, Name, Orders, Actions -->
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex items-center gap-2 min-w-0 flex-1">

                            <div class="min-w-0 flex-1">
                                <a href="{{ route('dashboard.customers.show', $customer->id) }}" class="block group">
                                    <h3 class="font-semibold text-foreground text-sm group-hover:text-primary transition-colors truncate">
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
                            <form action="{{ route('dashboard.customers.destroy', $customer->id) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir este cliente?');">
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
                                <p class="text-sm font-bold text-primary mt-0.5">R$ {{ number_format($totalSpent, 2, ',', '.') }}</p>
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
                                <p class="text-sm font-bold text-primary truncate mt-0.5">R$ {{ number_format($totalSpent, 2, ',', '.') }}</p>
                            </div>
                            <span class="text-muted-foreground/40 self-center shrink-0 text-sm">·</span>
                            <div class="min-w-0 flex-1 overflow-hidden">
                                <p class="text-xs text-muted-foreground/80 uppercase tracking-wide truncate">Tel</p>
                                @if($customer->phone)
                                    <a href="tel:{{ preg_replace('/\D/', '', $customer->phone) }}" class="text-sm font-medium text-foreground hover:text-primary truncate block mt-0.5">{{ $customer->phone }}</a>
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
                     x-show="search && showNoResults"
                     x-cloak
                     x-transition>
                    <div class="flex flex-col items-center gap-2">
                        <i data-lucide="search-x" class="w-10 h-10 opacity-40"></i>
                        <p class="text-sm">Nenhum cliente nesta página para "<span x-text="search"></span>"</p>
                        <p class="text-xs">Use <strong>Filtrar</strong> para buscar em todos ou <strong>Limpar</strong> para refazer.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Pagination -->
    @if(isset($customers) && method_exists($customers, 'links') && $customers->hasPages())
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-border bg-muted/20">
            <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-center gap-3 sm:gap-4">
                <p class="text-xs text-muted-foreground font-medium order-2 sm:order-1 lg:order-1 text-center sm:text-left">
                    Mostrando <span class="font-bold text-foreground">{{ $customers->firstItem() ?? $customers->count() }}</span> de <span class="font-bold text-foreground">{{ $customers->total() }}</span> clientes
                </p>
                <div class="flex items-center gap-2 order-1 sm:order-2 lg:order-2">
                    @if($customers->onFirstPage())
                        <button class="px-3 sm:px-4 py-2 rounded-lg border border-border bg-white text-xs font-semibold text-muted-foreground disabled:opacity-40 disabled:cursor-not-allowed transition-all inline-flex items-center" disabled>
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            <span class="ml-1 hidden sm:inline">Anterior</span>
                        </button>
                    @else
                        <a href="{{ $customers->appends(request()->query())->previousPageUrl() }}" class="px-3 sm:px-4 py-2 rounded-lg border border-border bg-white text-xs font-semibold text-foreground hover:bg-muted hover:border-primary/30 transition-all inline-flex items-center">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            <span class="ml-1 hidden sm:inline">Anterior</span>
                        </a>
                    @endif
                    
                    @if($customers->hasMorePages())
                        <a href="{{ $customers->appends(request()->query())->nextPageUrl() }}" class="px-3 sm:px-4 py-2 rounded-lg border border-border bg-white text-xs font-semibold text-foreground hover:bg-muted hover:border-primary/30 transition-all inline-flex items-center">
                            <span class="mr-1 hidden sm:inline">Próximo</span>
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </a>
                    @else
                        <button class="px-3 sm:px-4 py-2 rounded-lg border border-border bg-white text-xs font-semibold text-muted-foreground disabled:opacity-40 disabled:cursor-not-allowed transition-all inline-flex items-center" disabled>
                            <span class="mr-1 hidden sm:inline">Próximo</span>
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @elseif(isset($customers))
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-border bg-muted/20">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-4">
                <p class="text-xs text-muted-foreground font-medium text-center sm:text-left">
                    Mostrando <span class="font-bold text-foreground">{{ $customers->count() }}</span> de <span class="font-bold text-foreground">{{ $customers->total() ?? $customers->count() }}</span> clientes
                </p>
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush

@push('scripts')
<script>
document.addEventListener('alpine:init', function () {
  Alpine.data('customersLiveSearch', function (initialQ) {
    return {
      search: (typeof initialQ === 'string' ? initialQ : '') || '',
      showNoResults: false,

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
