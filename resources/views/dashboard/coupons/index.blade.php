@extends('dashboard.layouts.app')

@section('page_title', 'Cupons')
@section('page_subtitle', 'Gerenciamento de cupons de desconto')

@section('page_actions')
    <a href="{{ route('dashboard.coupons.create') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12h14"></path>
            <path d="M12 5v14"></path>
        </svg>
        Novo Cupom
    </a>
@endsection

@section('content')
<div class="bg-card rounded-xl border border-border animate-fade-in" 
     id="coupons-page"
     x-data="couponsLiveSearch('{{ request('search') ?? '' }}')">
    <div class="p-4 border-b border-border flex flex-col sm:flex-row gap-4 justify-between">
        <div class="relative flex-1 max-w-md">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none"></i>
            <input
                type="text"
                x-model="search"
                @input="filterCoupons()"
                placeholder="Buscar cupom..."
                class="form-input pl-10"
                autocomplete="off"
            />
        </div>
        <a href="{{ route('dashboard.coupons.create') }}" class="btn-primary gap-2 h-9 px-4">
            <i data-lucide="plus" class="h-4 w-4"></i>
            Novo Cupom
        </a>
    </div>

    <div id="coupons-grid" class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($coupons as $coupon)
            @php
                $statusClass = $coupon->is_active ? 'status-badge-completed' : 'status-badge-pending';
                $statusLabel = $coupon->is_active ? 'Ativo' : 'Inativo';
            @endphp
            <div class="coupon-card border border-border rounded-xl p-4 hover:shadow-md transition-all"
                 data-search-code="{{ mb_strtolower($coupon->code, 'UTF-8') }}">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                            <i data-lucide="ticket" class="h-5 w-5 text-primary"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold font-mono">{{ $coupon->code }}</h3>
                                <button type="button" class="inline-flex items-center justify-center h-6 w-6 rounded-md hover:bg-muted copy-coupon-btn" data-code="{{ $coupon->code }}">
                                    <i data-lucide="copy" class="h-3 w-3"></i>
                                </button>
                            </div>
                            <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Desconto</span>
                        <span class="font-semibold text-accent">{{ $coupon->formatted_value }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Usos</span>
                        <span>{{ $coupon->used_count ?? 0 }} / {{ $coupon->usage_limit ?: '∞' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Expira em</span>
                        <span>{{ $coupon->expires_at ? $coupon->expires_at->format('d/m/Y') : 'Sem validade' }}</span>
                    </div>
                </div>

                <div class="flex gap-2 mt-4 pt-4 border-t border-border">
                    <a href="{{ route('dashboard.coupons.edit', $coupon) }}" class="btn-outline flex-1 h-9 text-xs gap-1">
                        <i data-lucide="edit" class="h-4 w-4"></i>
                        Editar
                    </a>
                    <form action="{{ route('dashboard.coupons.destroy', $coupon) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este cupom?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-outline h-9 px-3 text-xs text-destructive">
                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
        @if($coupons->count() > 0)
            <div class="coupon-filter-no-results col-span-full text-center text-muted-foreground py-8"
                 x-show="search && showNoResults"
                 x-cloak
                 x-transition>
                <div class="flex flex-col items-center gap-2">
                    <i data-lucide="search-x" class="w-10 h-10 opacity-40"></i>
                    <p class="text-sm">Nenhum cupom encontrado para "<span x-text="search"></span>"</p>
                </div>
            </div>
        @else
            <div class="col-span-full text-center text-muted-foreground py-8">
                Nenhum cupom encontrado.
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush

@push('scripts')
<script>
document.addEventListener('alpine:init', function () {
    Alpine.data('couponsLiveSearch', function (initialQ) {
        return {
            search: (typeof initialQ === 'string' ? initialQ : '') || '',
            showNoResults: false,
            loading: false,
            searchTimeout: null,
            allCoupons: [],

            init: function () {
                this.saveInitialCoupons();
            },

            saveInitialCoupons: function () {
                var grid = document.getElementById('coupons-grid');
                if (!grid) return;
                var cards = grid.querySelectorAll('.coupon-card');
                this.allCoupons = Array.from(cards).map(function(card) {
                    return {
                        element: card.cloneNode(true),
                        code: card.getAttribute('data-search-code') || ''
                    };
                });
            },
            
            restoreInitialCoupons: function () {
                var grid = document.getElementById('coupons-grid');
                if (!grid) return;
                
                grid.innerHTML = '';
                this.allCoupons.forEach(function(item) {
                    if (item.element) {
                        grid.appendChild(item.element.cloneNode(true));
                    }
                });
                
                // Reativar botões de copiar
                document.querySelectorAll('.copy-coupon-btn').forEach(function(button) {
                    button.addEventListener('click', async function() {
                        var code = button.getAttribute('data-code');
                        if (!code) return;
                        try {
                            await navigator.clipboard.writeText(code);
                        } catch (error) {
                            console.error('Erro ao copiar cupom:', error);
                        }
                    });
                });
                
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            },

            filterCoupons: function () {
                var self = this;
                
                if (self.searchTimeout) {
                    clearTimeout(self.searchTimeout);
                }

                var searchTerm = self.search.trim();
                
                if (!searchTerm) {
                    self.restoreInitialCoupons();
                    self.showNoResults = false;
                    return;
                }

                self.searchTimeout = setTimeout(function() {
                    self.loading = true;
                    
                    fetch('{{ route("dashboard.coupons.index") }}?search=' + encodeURIComponent(searchTerm), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        self.loading = false;
                        if (data.coupons && data.coupons.length > 0) {
                            self.renderSearchResults(data.coupons);
                            self.showNoResults = false;
                        } else {
                            self.clearCoupons();
                            self.showNoResults = true;
                        }
                    })
                    .catch(function(error) {
                        console.error('Erro na busca:', error);
                        self.loading = false;
                        self.filterLocal();
                    });
                }, 300);
            },


            renderSearchResults: function (coupons) {
                var self = this;
                var grid = document.getElementById('coupons-grid');
                if (!grid) return;

                grid.innerHTML = '';

                coupons.forEach(function(coupon) {
                    var statusClass = coupon.is_active ? 'status-badge-completed' : 'status-badge-pending';
                    var statusLabel = coupon.is_active ? 'Ativo' : 'Inativo';
                    var editUrl = '{{ route("dashboard.coupons.edit", ":id") }}'.replace(':id', coupon.id);
                    var deleteUrl = '{{ route("dashboard.coupons.destroy", ":id") }}'.replace(':id', coupon.id);
                    var csrfToken = '{{ csrf_token() }}';
                    
                    var card = document.createElement('div');
                    card.className = 'coupon-card border border-border rounded-xl p-4 hover:shadow-md transition-all';
                    
                    card.innerHTML = 
                        '<div class="flex items-start justify-between mb-4">' +
                            '<div class="flex items-center gap-3">' +
                                '<div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">' +
                                    '<i data-lucide="ticket" class="h-5 w-5 text-primary"></i>' +
                                '</div>' +
                                '<div>' +
                                    '<div class="flex items-center gap-2">' +
                                        '<h3 class="font-bold font-mono">' + (coupon.code || '').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</h3>' +
                                        '<button type="button" class="inline-flex items-center justify-center h-6 w-6 rounded-md hover:bg-muted copy-coupon-btn" data-code="' + (coupon.code || '').replace(/"/g, '&quot;') + '">' +
                                            '<i data-lucide="copy" class="h-3 w-3"></i>' +
                                        '</button>' +
                                    '</div>' +
                                    '<span class="status-badge ' + statusClass + '">' + statusLabel + '</span>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                        '<div class="space-y-2 text-sm">' +
                            '<div class="flex justify-between">' +
                                '<span class="text-muted-foreground">Desconto</span>' +
                                '<span class="font-semibold text-accent">' + (coupon.formatted_value || '').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</span>' +
                            '</div>' +
                            '<div class="flex justify-between">' +
                                '<span class="text-muted-foreground">Usos</span>' +
                                '<span>' + (coupon.used_count || 0) + ' / ' + (coupon.usage_limit || '∞') + '</span>' +
                            '</div>' +
                            '<div class="flex justify-between">' +
                                '<span class="text-muted-foreground">Expira em</span>' +
                                '<span>' + (coupon.expires_at || 'Sem validade').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</span>' +
                            '</div>' +
                        '</div>' +
                        '<div class="flex gap-2 mt-4 pt-4 border-t border-border">' +
                            '<a href="' + editUrl + '" class="btn-outline flex-1 h-9 text-xs gap-1">' +
                                '<i data-lucide="edit" class="h-4 w-4"></i>' +
                                'Editar' +
                            '</a>' +
                            '<form action="' + deleteUrl + '" method="POST" onsubmit="return confirm(\'Tem certeza que deseja excluir este cupom?\');">' +
                                '<input type="hidden" name="_token" value="' + csrfToken + '">' +
                                '<input type="hidden" name="_method" value="DELETE">' +
                                '<button type="submit" class="btn-outline h-9 px-3 text-xs text-destructive">' +
                                    '<i data-lucide="trash-2" class="h-4 w-4"></i>' +
                                '</button>' +
                            '</form>' +
                        '</div>';
                    
                    grid.appendChild(card);
                });
                
                // Reativar botões de copiar
                document.querySelectorAll('.copy-coupon-btn').forEach(function(button) {
                    button.addEventListener('click', async function() {
                        var code = button.getAttribute('data-code');
                        if (!code) return;
                        try {
                            await navigator.clipboard.writeText(code);
                        } catch (error) {
                            console.error('Erro ao copiar cupom:', error);
                        }
                    });
                });
                
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            },

            clearCoupons: function () {
                var grid = document.getElementById('coupons-grid');
                if (grid) {
                    grid.innerHTML = '';
                }
            },

            filterLocal: function () {
                var self = this;
                var q = self.search.trim().toLowerCase();
                if (!q) {
                    self.restoreInitialCoupons();
                    self.showNoResults = false;
                    return;
                }
                
                var visible = 0;
                this.allCoupons.forEach(function(item) {
                    if (item.element) {
                        var code = item.code.toLowerCase();
                        var codeNorm = code.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                        var qNorm = q.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                        
                        if (code.includes(q) || codeNorm.includes(qNorm)) {
                            item.element.style.display = '';
                            visible++;
                        } else {
                            item.element.style.display = 'none';
                        }
                    }
                });
                
                self.showNoResults = visible === 0;
            }
        };
    });
});

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.copy-coupon-btn').forEach((button) => {
        button.addEventListener('click', async () => {
            const code = button.getAttribute('data-code');
            if (!code) return;
            try {
                await navigator.clipboard.writeText(code);
            } catch (error) {
                console.error('Erro ao copiar cupom:', error);
            }
        });
    });
    
    if (window.lucide) {
        window.lucide.createIcons();
    }
});
</script>
@endpush
@endsection
