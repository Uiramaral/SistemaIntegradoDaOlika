@extends('dashboard.layouts.app')

@section('page_title', 'Embalagens')
@section('page_subtitle', 'Gerencie as embalagens utilizadas nos seus produtos e receitas')

@section('page_actions')
    {{-- Actions handled inside the main content area for better mobile layout --}}
@endsection

@section('content')
    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Enforce mobile consistency exactly like Ingredients/Products */
        @media (max-width: 640px) {
            #packagings-grid>.packaging-card {
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
            }

            #packagings-page {
                overflow-x: hidden !important;
                width: 100% !important;
                max-width: 100vw !important;
            }
        }
    </style>

    <div x-data="packagingManager('{{ request('q') ?? '' }}')"
        class="bg-card rounded-xl border border-border animate-fade-in w-full overflow-x-hidden" id="packagings-page">

        {{-- Header: Search & Actions --}}
        <div class="p-4 border-b border-border flex flex-col sm:flex-row gap-4 justify-between w-full">
            <div class="relative flex-1 w-full max-w-full sm:max-w-md">
                <i data-lucide="search"
                    class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none"></i>
                <form action="{{ route('dashboard.producao.embalagens.index') }}" method="GET">
                    <input type="text" name="q" x-model="search" @input.debounce.300ms="updateSearch()"
                        placeholder="Buscar embalagem..." class="form-input pl-10 h-10 w-full" autocomplete="off">
                </form>
            </div>

            <button @click="openCreateModal()"
                class="btn-primary gap-2 h-10 px-4 rounded-lg shadow-sm w-full sm:w-auto shrink-0 font-bold inline-flex items-center justify-center">
                <i data-lucide="plus" class="h-4 w-4 text-white"></i>
                <span>Nova Embalagem</span>
            </button>
        </div>

        {{-- Packagings Grid --}}
        <div id="packagings-grid" class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 w-full">
            @forelse($packagings as $packaging)
                <div class="packaging-card searchable-item border border-border rounded-xl p-4 hover:shadow-md transition-all cursor-pointer group flex flex-col justify-between bg-white w-full min-w-0"
                    data-search-name="{{ mb_strtolower($packaging->name, 'UTF-8') }}"
                    data-search-description="{{ mb_strtolower($packaging->description ?? '', 'UTF-8') }}" x-show="matches($el)"
                    @click='openEditModal({{ json_encode($packaging) }})'>

                    {{-- Header: Icon, Name, Status --}}
                    <div class="flex items-start justify-between mb-3 gap-3">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div
                                class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center overflow-hidden shrink-0 border border-blue-100">
                                <i data-lucide="package" class="w-5 h-5 text-blue-500"></i>
                            </div>

                            <div class="min-w-0 flex-1">
                                <h3 class="font-semibold text-foreground text-sm sm:text-base truncate leading-tight"
                                    title="{{ $packaging->name }}">{{ $packaging->name }}</h3>
                                <p
                                    class="text-[11px] text-muted-foreground mt-0.5 truncate uppercase tracking-wider font-medium">
                                    {{ $packaging->description ?: 'Sem descrição' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1 shrink-0">
                            <span
                                class="status-badge {{ $packaging->is_active ? 'status-badge-completed' : 'status-badge-pending' }} text-[10px] py-0 px-2">
                                {{ $packaging->is_active ? 'Ativa' : 'Inativa' }}
                            </span>
                        </div>
                    </div>

                    {{-- Footer: Cost and Actions --}}
                    <div class="pt-3 border-t border-border mt-auto flex items-center justify-between">
                        <div class="min-w-0">
                            <p class="text-[9px] text-muted-foreground uppercase tracking-widest font-bold truncate">CUSTO
                                UNITÁRIO</p>
                            <p class="text-xs font-bold text-primary mt-0.5 truncate">R$
                                {{ number_format($packaging->cost, 2, ',', '.') }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <button @click.stop='openEditModal({{ json_encode($packaging) }})' class="btn-outline h-8 w-8 p-0"
                                title="Editar">
                                <i data-lucide="edit" class="h-4 w-4"></i>
                            </button>
                            <form action="{{ route('dashboard.producao.embalagens.destroy', $packaging) }}" method="POST"
                                class="inline" onsubmit="return confirm('Tem certeza que deseja excluir esta embalagem?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" @click.stop
                                    class="btn-outline text-destructive hover:bg-destructive/10 h-8 w-8 p-0" title="Excluir">
                                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center text-muted-foreground">
                    <div class="bg-muted/30 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="package-search" class="w-8 h-8 opacity-50"></i>
                    </div>
                    <h3 class="text-lg font-medium text-foreground mb-1">Nenhuma embalagem encontrada</h3>
                    <p class="mb-4">Comece adicionando uma nova embalagem para seus produtos.</p>
                </div>
            @endforelse
        </div>

        {{-- No Results State --}}
        <div class="text-center text-muted-foreground py-12" x-show="search && showNoResults" x-cloak x-transition>
            <div class="flex flex-col items-center gap-2">
                <i data-lucide="search-x" class="w-10 h-10 opacity-40"></i>
                <p class="text-sm">Nenhuma embalagem encontrada para "<span x-text="search"></span>"</p>
            </div>
        </div>

        {{-- Pagination --}}
        @if($packagings->hasPages())
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-border bg-muted/20">
                <div
                    class="flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-4 font-medium text-xs text-muted-foreground">
                    <p>Mostrando <span class="text-foreground">{{ $packagings->count() }}</span> de <span
                            class="text-foreground">{{ $packagings->total() }}</span> embalagens</p>
                    {{ $packagings->links() }}
                </div>
            </div>
        @endif

        {{-- Unified Packaging Modal (Create/Edit) --}}
        <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-cloak>

            <div class="bg-card w-full max-w-lg rounded-xl border border-border shadow-xl overflow-hidden flex flex-col max-h-[85dvh]"
                @click.away="showModal = false" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4">

                <form
                    :action="modalMode === 'edit' ? '{{ url('/dashboard/producao/embalagens') }}/' + formData.id : '{{ route('dashboard.producao.embalagens.store') }}'"
                    method="POST" class="contents">
                    @csrf
                    <template x-if="modalMode === 'edit'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="p-4 sm:p-6 border-b border-border flex justify-between items-center bg-muted/20 shrink-0">
                        <h3 class="font-bold text-lg" x-text="modalMode === 'edit' ? 'Editar Embalagem' : 'Nova Embalagem'">
                        </h3>
                        <button type="button" @click="showModal = false"
                            class="text-muted-foreground hover:text-foreground">
                            <i data-lucide="x" class="h-5 w-5"></i>
                        </button>
                    </div>

                    <div class="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1 min-h-0">
                        <div>
                            <label class="block text-sm font-semibold mb-1.5">Nome da Embalagem <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="name" x-model="formData.name" required class="form-input w-full"
                                placeholder="Ex: Caixa de 4 brigadeiros, Saco Kraft P...">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-1.5">Custo Unitário (R$) <span
                                    class="text-red-500">*</span></label>
                            <div class="relative">
                                <span
                                    class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground text-sm font-medium">R$</span>
                                <input type="number" name="cost" x-model="formData.cost" step="0.01" min="0" required
                                    class="form-input w-full pl-10" placeholder="0,00">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-1.5 text-balance">Descrição / Detalhes
                                (Opcional)</label>
                            <textarea name="description" x-model="formData.description" rows="3"
                                class="form-input resize-none py-3"
                                placeholder="Medidas, material, fornecedor..."></textarea>
                        </div>

                        <div class="flex items-center gap-6 pt-2">
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input type="checkbox" name="is_active" value="1" x-model="formData.is_active"
                                        :checked="formData.is_active"
                                        class="form-checkbox h-5 w-5 rounded border-border text-primary focus:ring-primary">
                                </div>
                                <span class="text-sm font-medium group-hover:text-primary transition-colors">Embalagem
                                    Ativa</span>
                            </label>
                        </div>
                    </div>

                    <div class="p-4 border-t border-border bg-muted/20 flex justify-end gap-3 shrink-0">
                        <button type="button" @click="showModal = false" class="btn-ghost">Cancelar</button>
                        <button type="submit" class="btn-primary px-8"
                            x-text="modalMode === 'edit' ? 'Salvar Alterações' : 'Criar Embalagem'"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('packagingManager', (initialQ) => ({
                    search: (typeof initialQ === 'string' ? initialQ : '') || '',
                    showNoResults: false,
                    showModal: false,
                    modalMode: 'create', // 'create' or 'edit'
                    formData: {
                        id: null,
                        name: '',
                        cost: '',
                        description: '',
                        is_active: true
                    },

                    openCreateModal() {
                        this.modalMode = 'create';
                        this.formData = {
                            id: null,
                            name: '',
                            cost: '',
                            description: '',
                            is_active: true
                        };
                        this.showModal = true;
                    },

                    openEditModal(packaging) {
                        this.modalMode = 'edit';
                        this.formData = {
                            id: packaging.id,
                            name: packaging.name,
                            cost: packaging.cost,
                            description: packaging.description || '',
                            is_active: !!packaging.is_active
                        };
                        this.showModal = true;
                    },

                    updateSearch() {
                        const q = this.search.trim().toLowerCase();
                        const qNorm = q.normalize('NFD').replace(/[\u0300-\u036f]/g, '');

                        let visibleCount = 0;

                        document.querySelectorAll('.searchable-item').forEach(el => {
                            const name = (el.getAttribute('data-search-name') || '').toLowerCase();
                            const description = (el.getAttribute('data-search-description') || '').toLowerCase();
                            const nameNorm = name.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                            const descriptionNorm = description.normalize('NFD').replace(/[\u0300-\u036f]/g, '');

                            let matches = true;
                            if (q) {
                                matches = name.includes(q) || nameNorm.includes(qNorm) ||
                                    description.includes(q) || descriptionNorm.includes(qNorm);
                            }

                            if (matches) visibleCount++;
                        });

                        this.showNoResults = q !== '' && visibleCount === 0;
                    },

                    matches(el) {
                        const q = this.search.trim().toLowerCase();
                        if (!q) return true;

                        const name = (el.getAttribute('data-search-name') || '').toLowerCase();
                        const description = (el.getAttribute('data-search-description') || '').toLowerCase();
                        const nameNorm = name.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                        const descriptionNorm = description.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                        const qNorm = q.normalize('NFD').replace(/[\u0300-\u036f]/g, '');

                        if (name.includes(q) || nameNorm.includes(qNorm)) return true;
                        if (description.includes(q) || descriptionNorm.includes(qNorm)) return true;

                        return false;
                    }
                }));
            });
        </script>
    @endpush
@endsection