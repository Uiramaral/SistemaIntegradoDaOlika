@extends('dashboard.layouts.app')

@section('page_title', 'Cupons')
@section('page_subtitle', 'Gerenciamento de cupons de desconto')

@section('content')
    <div x-data="couponsPage()" id="coupons-page" class="bg-card rounded-xl border border-border overflow-hidden">

        {{-- Header: Search + New Button --}}
        <div class="p-4 border-b border-border">
            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <div class="relative flex-1 min-w-0">
                    <i data-lucide="search"
                        class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none"></i>
                    <input type="text" x-model="search" placeholder="Buscar cupom..."
                        class="form-input pl-10 h-10 bg-muted/30 border-transparent focus:bg-white transition-all text-sm rounded-lg w-full"
                        autocomplete="off">
                </div>
                <button type="button" @click="openModal('create')"
                    class="inline-flex items-center justify-center gap-2 h-10 px-4 rounded-lg bg-primary hover:bg-primary/90 text-white font-semibold text-sm shadow-sm transition-colors shrink-0">
                    <i data-lucide="plus" class="h-4 w-4"></i>
                    <span>Novo Cupom</span>
                </button>
            </div>
        </div>

        {{-- Coupons Grid --}}
        <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($coupons as $coupon)
                @php
                    $couponJson = [
                        'id' => $coupon->id,
                        'code' => $coupon->code,
                        'name' => $coupon->name ?? '',
                        'description' => $coupon->description ?? '',
                        'type' => $coupon->type,
                        'value' => (float) $coupon->value,
                        'minimum_amount' => (float) ($coupon->minimum_amount ?? 0),
                        'usage_limit' => $coupon->usage_limit,
                        'starts_at' => $coupon->starts_at ? $coupon->starts_at->format('Y-m-d') : '',
                        'expires_at' => $coupon->expires_at ? $coupon->expires_at->format('Y-m-d') : '',
                        'is_active' => (bool) $coupon->is_active,
                        'visibility' => $coupon->visibility ?? 'public',
                        'first_order_only' => (bool) ($coupon->first_order_only ?? false),
                        'update_url' => route('dashboard.coupons.update', $coupon->id),
                    ];
                    $searchCode = mb_strtolower($coupon->code, 'UTF-8');
                @endphp
                <div class="coupon-card bg-white border border-border rounded-xl p-4 hover:shadow-md transition-all overflow-hidden"
                    data-search-code="{{ $searchCode }}" x-show="matchesCard($el)">

                    {{-- Header --}}
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                                <i data-lucide="ticket" class="h-5 w-5 text-primary"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold font-mono text-sm truncate">{{ $coupon->code }}</h3>
                                    <button type="button"
                                        class="inline-flex items-center justify-center h-6 w-6 rounded-md hover:bg-muted shrink-0"
                                        @click="copyCode('{{ $coupon->code }}')" title="Copiar código">
                                        <i data-lucide="copy" class="h-3 w-3"></i>
                                    </button>
                                </div>
                                <span
                                    class="status-badge {{ $coupon->is_active ? 'status-badge-completed' : 'status-badge-pending' }}">
                                    {{ $coupon->is_active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Details --}}
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Desconto</span>
                            <span class="font-semibold text-primary">{{ $coupon->formatted_value }}</span>
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

                    {{-- Actions --}}
                    <div class="flex gap-2 mt-4 pt-3 border-t border-border">
                        <button type="button" @click="openModal('edit', {{ json_encode($couponJson) }})"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 h-9 px-3 rounded-lg border border-border bg-white text-foreground text-xs font-medium hover:bg-muted/30 transition-colors">
                            <i data-lucide="edit" class="h-3.5 w-3.5"></i>
                            Editar
                        </button>
                        <form action="{{ route('dashboard.coupons.destroy', $coupon) }}" method="POST"
                            onsubmit="return confirm('Tem certeza que deseja excluir este cupom?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="inline-flex items-center justify-center h-9 w-9 rounded-lg border border-border bg-white text-destructive hover:bg-destructive/10 transition-colors">
                                <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center text-muted-foreground py-12">
                    <div class="flex flex-col items-center gap-3">
                        <i data-lucide="ticket" class="w-12 h-12 opacity-20"></i>
                        <p class="text-sm">Nenhum cupom cadastrado</p>
                        <button @click="openModal('create')"
                            class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-lg bg-primary hover:bg-primary/90 text-white font-semibold text-sm shadow-sm transition-colors">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            Criar primeiro cupom
                        </button>
                    </div>
                </div>
            @endforelse

            {{-- No Results --}}
            <div class="col-span-full text-center text-muted-foreground py-8" x-show="search && showNoResults" x-cloak>
                <div class="flex flex-col items-center gap-2">
                    <i data-lucide="search-x" class="w-10 h-10 opacity-40"></i>
                    <p class="text-sm">Nenhum cupom encontrado para "<span x-text="search"></span>"</p>
                </div>
            </div>
        </div>

        {{-- Pagination --}}
        @if($coupons->hasPages())
            <div class="px-4 py-3 border-t border-border bg-muted/20">
                {{ $coupons->links() }}
            </div>
        @endif

        {{-- MODAL --}}
        <div class="fixed inset-0 z-[100] overflow-y-auto" x-show="isModalOpen" x-cloak
            x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="fixed inset-0 bg-black/50" @click="closeModal()"></div>

            <div class="flex min-h-full items-start sm:items-center justify-center p-4">
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden my-auto" @click.stop
                    x-show="isModalOpen" x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95">

                    <form :action="formAction" method="POST">
                        @csrf
                        <input type="hidden" name="_method" :value="isEditMode ? 'PUT' : 'POST'">

                        {{-- Header --}}
                        <div class="flex items-center justify-between px-6 py-4 border-b border-border">
                            <div>
                                <h2 class="text-lg font-semibold text-foreground"
                                    x-text="isEditMode ? 'Editar Cupom' : 'Novo Cupom'"></h2>
                                <p class="text-sm text-muted-foreground mt-0.5">Configure os detalhes do cupom de desconto.
                                </p>
                            </div>
                            <button type="button" @click="closeModal()"
                                class="p-2 rounded-lg text-muted-foreground hover:bg-muted hover:text-foreground transition-colors">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        {{-- Body --}}
                        <div class="px-6 py-5 space-y-4 max-h-[60vh] overflow-y-auto">

                            {{-- Code --}}
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-1">Código do Cupom <span
                                        class="text-destructive">*</span></label>
                                <input type="text" name="code" x-model="formData.code" required
                                    class="w-full h-10 px-3 rounded-xl border border-border bg-white focus:border-primary focus:ring-1 focus:ring-primary text-sm font-mono uppercase"
                                    placeholder="DESCONTO10">
                                <p class="text-xs text-muted-foreground mt-1">Código que o cliente digitará no checkout.</p>
                            </div>

                            {{-- Name --}}
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-1">Nome do Cupom <span
                                        class="text-destructive">*</span></label>
                                <input type="text" name="name" x-model="formData.name" required
                                    class="w-full h-10 px-3 rounded-xl border border-border bg-white focus:border-primary focus:ring-1 focus:ring-primary text-sm"
                                    placeholder="Desconto de Natal">
                            </div>

                            {{-- Type & Value --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-1">Tipo <span
                                            class="text-destructive">*</span></label>
                                    <select name="type" x-model="formData.type" required
                                        class="w-full h-10 px-3 rounded-xl border border-border bg-white focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                                        <option value="percentage">Porcentagem (%)</option>
                                        <option value="fixed">Valor Fixo (R$)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-1">Valor <span
                                            class="text-destructive">*</span></label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground text-sm"
                                            x-text="formData.type === 'fixed' ? 'R$' : '%'"></span>
                                        <input type="number" name="value" step="0.01" min="0" x-model="formData.value"
                                            required
                                            class="w-full h-10 pl-10 pr-3 rounded-xl border border-border bg-white focus:border-primary focus:ring-1 focus:ring-primary text-sm"
                                            placeholder="10.00">
                                    </div>
                                </div>
                            </div>

                            {{-- Minimum Amount --}}
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-1">Valor Mínimo do Pedido</label>
                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground text-sm">R$</span>
                                    <input type="number" name="minimum_amount" step="0.01" min="0"
                                        x-model="formData.minimum_amount"
                                        class="w-full h-10 pl-10 pr-3 rounded-xl border border-border bg-white focus:border-primary focus:ring-1 focus:ring-primary text-sm"
                                        placeholder="0.00">
                                </div>
                                <p class="text-xs text-muted-foreground mt-1">Deixe em branco para não ter mínimo.</p>
                            </div>

                            {{-- Usage Limit --}}
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-1">Limite de Usos</label>
                                <input type="number" name="usage_limit" min="1" x-model="formData.usage_limit"
                                    class="w-full h-10 px-3 rounded-xl border border-border bg-white focus:border-primary focus:ring-1 focus:ring-primary text-sm"
                                    placeholder="Ilimitado">
                                <p class="text-xs text-muted-foreground mt-1">Deixe em branco para uso ilimitado.</p>
                            </div>

                            {{-- Dates --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-1">Data de Início</label>
                                    <input type="date" name="starts_at" x-model="formData.starts_at"
                                        class="w-full h-10 px-3 rounded-xl border border-border bg-white focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-1">Data de Expiração</label>
                                    <input type="date" name="expires_at" x-model="formData.expires_at"
                                        class="w-full h-10 px-3 rounded-xl border border-border bg-white focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                                </div>
                            </div>

                            {{-- Visibility --}}
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-1">Visibilidade <span
                                        class="text-destructive">*</span></label>
                                <select name="visibility" x-model="formData.visibility" required
                                    class="w-full h-10 px-3 rounded-xl border border-border bg-white focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                                    <option value="public">Público</option>
                                    <option value="private">Privado</option>
                                </select>
                            </div>

                            {{-- Active Toggle --}}
                            <label
                                class="flex items-center justify-between bg-muted/20 p-4 rounded-xl border border-border/50 cursor-pointer">
                                <div>
                                    <span class="font-medium text-foreground block">Cupom Ativo</span>
                                    <span class="text-xs text-muted-foreground">Define se este cupom está disponível para
                                        uso.</span>
                                </div>
                                <div class="relative">
                                    <input type="checkbox" x-model="formData.is_active" class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary">
                                    </div>
                                </div>
                                <input type="hidden" name="is_active" :value="formData.is_active ? 1 : 0">
                            </label>

                            {{-- First Order Only --}}
                            <label
                                class="flex items-center justify-between bg-muted/20 p-4 rounded-xl border border-border/50 cursor-pointer">
                                <div>
                                    <span class="font-medium text-foreground block">Apenas Primeiro Pedido</span>
                                    <span class="text-xs text-muted-foreground">Cupom válido apenas para o primeiro pedido
                                        do cliente.</span>
                                </div>
                                <div class="relative">
                                    <input type="checkbox" x-model="formData.first_order_only" class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary">
                                    </div>
                                </div>
                                <input type="hidden" name="first_order_only" :value="formData.first_order_only ? 1 : 0">
                            </label>
                        </div>

                        {{-- Footer --}}
                        <div
                            class="px-6 py-4 border-t border-border flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                            <button type="button" @click="closeModal()"
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-border bg-white text-foreground font-medium hover:bg-muted/30 transition-colors text-sm">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-primary hover:bg-primary/90 text-white font-semibold shadow-sm transition-colors text-sm">
                                <span x-text="isEditMode ? 'Salvar Alterações' : 'Criar Cupom'"></span>
                            </button>
                        </div>
                    </form>
                </div>
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
                Alpine.data('couponsPage', function () {
                    return {
                        search: '',
                        showNoResults: false,
                        isModalOpen: false,
                        isEditMode: false,
                        formAction: '',

                        formData: {
                            id: null,
                            code: '',
                            name: '',
                            description: '',
                            type: 'percentage',
                            value: '',
                            minimum_amount: '',
                            usage_limit: '',
                            starts_at: '',
                            expires_at: '',
                            is_active: true,
                            visibility: 'public',
                            first_order_only: false
                        },

                        init() {
                            this.$watch('search', () => this.updateNoResults());
                        },

                        updateNoResults() {
                            this.$nextTick(() => {
                                const cards = document.querySelectorAll('#coupons-page .coupon-card');
                                let visible = 0;
                                cards.forEach(el => { if (this.matchesCard(el)) visible++; });
                                this.showNoResults = this.search.trim() !== '' && visible === 0;
                            });
                        },

                        matchesCard(el) {
                            const q = this.search.trim().toLowerCase();
                            if (!q) return true;
                            const code = (el.getAttribute('data-search-code') || '').toLowerCase();
                            const codeNorm = code.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                            const qNorm = q.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                            return code.includes(q) || codeNorm.includes(qNorm);
                        },

                        openModal(mode, data = null) {
                            this.isModalOpen = true;
                            this.isEditMode = mode === 'edit';
                            if (mode === 'create') {
                                this.formAction = "{{ route('dashboard.coupons.store') }}";
                                this.resetForm();
                            } else {
                                this.formAction = data.update_url;
                                this.loadFormData(data);
                            }
                            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
                        },

                        closeModal() {
                            this.isModalOpen = false;
                        },

                        resetForm() {
                            this.formData = {
                                id: null,
                                code: '',
                                name: '',
                                description: '',
                                type: 'percentage',
                                value: '',
                                minimum_amount: '',
                                usage_limit: '',
                                starts_at: '',
                                expires_at: '',
                                is_active: true,
                                visibility: 'public',
                                first_order_only: false
                            };
                        },

                        loadFormData(data) {
                            this.formData = {
                                id: data.id,
                                code: data.code || '',
                                name: data.name || '',
                                description: data.description || '',
                                type: data.type || 'percentage',
                                value: data.value || '',
                                minimum_amount: data.minimum_amount || '',
                                usage_limit: data.usage_limit || '',
                                starts_at: data.starts_at || '',
                                expires_at: data.expires_at || '',
                                is_active: data.is_active ?? true,
                                visibility: data.visibility || 'public',
                                first_order_only: data.first_order_only ?? false
                            };
                        },

                        copyCode(code) {
                            navigator.clipboard.writeText(code).then(() => {
                                // Toast feedback could go here
                            }).catch(err => console.error('Erro ao copiar:', err));
                        }
                    };
                });
            });
        </script>
    @endpush
@endsection