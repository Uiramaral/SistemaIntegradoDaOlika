@extends('dashboard.layouts.app')

@section('page_title', 'Lista de Produção')
@section('page_subtitle', 'Acompanhamento da produção diária')

@section('content')
    <style>
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
    <div class="space-y-6">
        <div class="bg-card rounded-xl border border-border overflow-hidden">
            {{-- Header com Seletor de Datas Estilo Carrossel --}}
            <div class="px-4 pt-6 pb-2 border-b border-border bg-card">
                <div class="flex items-center justify-between mb-6 px-2">
                    <a href="{{ route('dashboard.producao.lista-producao.index', ['date' => \Carbon\Carbon::parse($date)->subDays(1)->format('Y-m-d')]) }}"
                        class="flex items-center gap-2 px-4 py-2 rounded-xl border border-border bg-muted/30 text-xs font-bold hover:bg-muted transition-all">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        Anterior
                    </a>

                    <div class="text-center">
                        <h3 class="text-base font-black text-foreground tracking-tight">
                            {{ \Carbon\Carbon::parse($date)->locale('pt_BR')->translatedFormat('F Y') }}
                        </h3>
                    </div>

                    <a href="{{ route('dashboard.producao.lista-producao.index', ['date' => \Carbon\Carbon::parse($date)->addDays(1)->format('Y-m-d')]) }}"
                        class="flex items-center gap-2 px-4 py-2 rounded-xl border border-border bg-muted/30 text-xs font-bold hover:bg-muted transition-all">
                        Próximo
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </a>
                </div>

                <div class="flex gap-4 overflow-x-auto pt-3 pb-4 scrollbar-hide px-2 -mx-2 snap-x">
                    @foreach($availableDates as $d)
                                <a href="{{ route('dashboard.producao.lista-producao.index', ['date' => $d['date']]) }}" class="relative flex-shrink-0 w-[84px] snap-center p-3 rounded-2xl border transition-all duration-300 flex flex-col items-center justify-center gap-1
                                                                                                                                           {{ $d['is_selected']
                        ? 'bg-white border-primary border-2 shadow-lg ring-4 ring-primary/5 scale-105 z-10'
                        : 'bg-card border-border hover:border-primary/30 opacity-70 hover:opacity-100' }}">

                                    <span
                                        class="text-[10px] font-black uppercase tracking-widest {{ $d['is_selected'] ? 'text-primary' : 'text-muted-foreground' }}">
                                        {{ $d['month'] }}
                                    </span>

                                    <span class="text-2xl font-black tracking-tighter text-foreground">
                                        {{ $d['day'] }}
                                    </span>

                                    <span
                                        class="text-[10px] font-bold capitalize {{ $d['is_selected'] ? 'text-primary' : 'text-muted-foreground' }}">
                                        {{ $d['weekday'] }}
                                    </span>

                                    @if($d['count'] > 0)
                                        <div
                                            class="absolute -top-1.5 -right-1.5 h-6 w-6 bg-primary text-white text-[10px] font-black rounded-full flex items-center justify-center border-2 border-white shadow-md ring-2 ring-primary/10">
                                            {{ $d['count'] }}
                                        </div>
                                    @endif
                                </a>
                    @endforeach
                </div>
            </div>

            {{-- Barra de Ações Rápida --}}
            @if($list)
                <div class="px-4 py-3 bg-muted/10 border-b border-border flex justify-between items-center sm:px-6">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground">
                        <span class="text-foreground font-black">{{ $list->total_items }}</span> itens agendados
                    </p>

                    @php $countPrint = $items->where('mark_for_print', true)->count(); @endphp
                    @if($countPrint > 0)
                        <a href="{{ route('dashboard.producao.lista-producao.print', ['id' => $list->id]) }}" target="_blank"
                            class="flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-xl text-xs font-black hover:bg-red-700 transition-all shadow-md hover:shadow-lg active:scale-95">
                            <i data-lucide="printer" class="w-4 h-4"></i>
                            IMPRIMIR SELECIONADOS ({{ $countPrint }})
                        </a>
                    @endif
                </div>
            @endif

            @if($list)
                {{-- Add Item Form --}}
                <div class="p-4 sm:p-6 bg-muted/30 border-b border-border">
                    <form action="{{ route('dashboard.producao.lista-producao.add-item', $list->id) }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                            <div class="sm:col-span-4">
                                <label
                                    class="block text-xs font-semibold uppercase text-muted-foreground mb-1.5">Receita</label>
                                <select name="recipe_id" required class="form-input w-full">
                                    <option value="">Selecione uma receita...</option>
                                    @foreach($recipes as $recipe)
                                        <option value="{{ $recipe->id }}">{{ $recipe->name }}
                                            ({{ number_format($recipe->total_weight, 0, ',', '.') }}g)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold uppercase text-muted-foreground mb-1.5">Qtd</label>
                                <input type="number" name="quantity" value="1" min="1" required class="form-input w-full"
                                    placeholder="Qtd">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold uppercase text-muted-foreground mb-1.5">Peso
                                    (g)</label>
                                <input type="number" name="weight" step="0.01" min="0" class="form-input w-full"
                                    placeholder="Padrão">
                            </div>
                            <div class="sm:col-span-3">
                                <label
                                    class="block text-xs font-semibold uppercase text-muted-foreground mb-1.5">Observação</label>
                                <input type="text" name="observation" class="form-input w-full" placeholder="Opcional">
                            </div>
                            <div class="sm:col-span-1">
                                <button type="submit" class="btn-primary w-full justify-center h-10">
                                    <i data-lucide="plus" class="h-5 w-5"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div
                    class="p-4 sm:px-6 py-2 bg-blue-50/50 text-blue-800 text-xs border-b border-border flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4"></i>
                    Por padrão, todos os itens são marcados para impressão. Desmarque a caixa de seleção para excluir da fila de
                    impressão.
                </div>

                {{-- Desktop Table --}}
                <div class="hidden lg:block">
                    <table class="w-full text-sm">
                        <thead class="bg-muted/50 border-b border-border">
                            <tr>
                                <th class="w-12 text-center py-3 px-4">
                                    <i data-lucide="printer" class="w-4 h-4 mx-auto text-muted-foreground"></i>
                                </th>
                                <th class="text-left font-semibold uppercase py-3 px-4">Produto / Receita</th>
                                <th class="text-left font-semibold uppercase py-3 px-4">Quantidade</th>
                                <th class="text-left font-semibold uppercase py-3 px-4">Ingredientes</th>
                                <th class="text-right font-semibold uppercase py-3 px-4 w-20">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @forelse($items as $item)
                                <tr class="hover:bg-muted/30 transition-colors {{ $item->is_produced ? 'bg-green-50/30' : '' }}">
                                    <td class="py-4 px-4 text-center">
                                        <input type="checkbox"
                                            class="mark-print-checkbox w-5 h-5 rounded border-border text-primary focus:ring-primary cursor-pointer"
                                            {{ ($item->mark_for_print ?? true) ? 'checked' : '' }}
                                            data-url="{{ route('dashboard.producao.lista-producao.mark-print', $item->ids) }}">
                                    </td>
                                    <td class="py-4 px-4 align-top">
                                        <p class="font-semibold text-foreground">{{ $item->recipe_name }}</p>
                                        @if($item->observation)
                                            <div
                                                class="mt-1 inline-flex items-center gap-1.5 px-2 py-1 bg-amber-50 text-amber-700 rounded text-xs">
                                                <i data-lucide="sticky-note" class="w-3 h-3"></i>
                                                {{ $item->observation }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 align-top text-muted-foreground">
                                        {{ $item->quantity }}x {{ number_format($item->weight, 0, ',', '.') }}g
                                        <div class="font-semibold text-foreground mt-0.5">
                                            = {{ number_format($item->quantity * $item->weight, 0, ',', '.') }}g
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 align-top">
                                        @if($item->recipe)
                                            @php
                                                $calculated = $item->recipe->calculateIngredientWeights($item->weight);
                                                $displayIngredients = [];
                                                foreach ($calculated as $key => $row) {
                                                    $w = (float) ($row['weight'] ?? 0) * $item->quantity;
                                                    if ($w <= 0)
                                                        continue;

                                                    // O nome vem do label (água/levain) ou do ingrediente
                                                    $name = $row['label'] ?? ($row['ingredient']->name ?? 'N/A');
                                                    $displayIngredients[] = "{$name}: " . number_format($w, 1, ',', '.') . "g";
                                                }
                                            @endphp
                                            <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs text-muted-foreground">
                                                @foreach($displayIngredients as $ing)
                                                    <span
                                                        class="bg-muted/50 px-1.5 py-0.5 rounded border border-border/50">{{ $ing }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 align-top text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <form
                                                action="{{ route('dashboard.producao.lista-producao.mark-produced', $item->ids) }}"
                                                method="POST">
                                                @csrf
                                                <button type="submit"
                                                    class="btn-icon {{ $item->is_produced ? 'bg-green-100 text-green-700' : 'btn-ghost text-muted-foreground hover:text-green-600' }} h-8 w-8">
                                                    <i data-lucide="{{ $item->is_produced ? 'check-circle' : 'circle' }}"
                                                        class="h-4 w-4"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('dashboard.producao.lista-producao.remove-item', $item->ids) }}"
                                                method="POST" onsubmit="return confirm('Remover lote?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="btn-icon btn-ghost h-8 w-8 text-destructive hover:bg-destructive/10">
                                                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                                                </button>
                                            </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-16 text-center text-muted-foreground">
                                        <i data-lucide="clipboard-list" class="w-12 h-12 mx-auto mb-4 opacity-50"></i>
                                        <p>Nenhum item adicionado à produção.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Cards --}}
                <div class="lg:hidden divide-y divide-border">
                    @forelse($items as $item)
                        <div class="p-4 space-y-3 {{ $item->is_produced ? 'bg-green-50/10' : '' }}">
                            <div class="flex justify-between items-start">
                                <div class="flex items-start gap-3">
                                    <input type="checkbox"
                                        class="mark-print-checkbox mt-1 w-5 h-5 rounded border-border text-primary focus:ring-primary"
                                        {{ ($item->mark_for_print ?? true) ? 'checked' : '' }}
                                        data-url="{{ route('dashboard.producao.lista-producao.mark-print', $item->ids) }}">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            @if($item->is_produced)
                                                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                            @endif
                                            <h4 class="font-bold text-foreground">{{ $item->recipe_name }}</h4>
                                        </div>
                                        <p class="text-[10px] text-muted-foreground mt-0.5">
                                            {{ $item->quantity }}x {{ number_format($item->weight, 0, ',', '.') }}g =
                                            <strong
                                                class="text-foreground">{{ number_format($item->total_weight / 1000, 2, ',', '.') }}kg</strong>
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1">
                                    <form action="{{ route('dashboard.producao.lista-producao.mark-produced', $item->ids) }}"
                                        method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="btn-icon {{ $item->is_produced ? 'bg-green-100 text-green-700' : 'btn-ghost text-muted-foreground' }} h-8 w-8">
                                            <i data-lucide="{{ $item->is_produced ? 'check-circle' : 'circle' }}"
                                                class="h-4 w-4"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('dashboard.producao.lista-producao.remove-item', $item->ids) }}"
                                        method="POST" onsubmit="return confirm('Remover lote?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon btn-ghost h-8 w-8 text-destructive -mr-2">
                                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            @if($item->observation)
                                <div class="flex items-center gap-2 px-3 py-2 bg-amber-50 text-amber-800 rounded-lg text-sm">
                                    <i data-lucide="sticky-note" class="w-4 h-4 shrink-0"></i>
                                    {{ $item->observation }}
                                </div>
                            @endif

                            @if($item->recipe)
                                @php
                                    $calculated = $item->recipe->calculateIngredientWeights($item->weight);
                                    $displayIngredients = [];
                                    foreach ($calculated as $key => $row) {
                                        $w = (float) ($row['weight'] ?? 0) * $item->quantity;
                                        if ($w <= 0)
                                            continue;

                                        $name = $row['label'] ?? ($row['ingredient']->name ?? 'N/A');
                                        $displayIngredients[] = "{$name}: " . number_format($w, 1, ',', '.') . "g";
                                    }
                                @endphp
                                <div class="bg-muted/30 p-3 rounded-lg border border-border/50">
                                    <p class="text-xs font-semibold uppercase text-muted-foreground mb-2">Ingredientes</p>
                                    <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs text-muted-foreground">
                                        @foreach($displayIngredients as $ing)
                                            <span>{{ $ing }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="py-16 text-center text-muted-foreground">
                            <i data-lucide="clipboard-list" class="w-12 h-12 mx-auto mb-4 opacity-50"></i>
                            <p>Nenhum item adicionado à produção.</p>
                        </div>
                    @endforelse
                </div>

            @else
                <div class="p-12 text-center text-muted-foreground">
                    <i data-lucide="calendar-off" class="w-16 h-16 mx-auto mb-4 opacity-30"></i>
                    <h3 class="text-lg font-medium text-foreground mb-2">Nenhuma lista para esta data</h3>
                    <p class="mb-6">Você pode criar uma nova lista de produção para este dia agora mesmo.</p>

                    <form action="{{ route('dashboard.producao.lista-producao.create') }}" method="POST" class="inline-block">
                        @csrf
                        <input type="hidden" name="production_date" value="{{ $date }}">
                        <button type="submit" class="btn-primary gap-2">
                            <i data-lucide="plus" class="h-4 w-4"></i>
                            Criar Lista de Produção
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Checkbox logic using Fetch API for cleaner code
                document.querySelectorAll('.mark-print-checkbox').forEach(function (cb) {
                    cb.addEventListener('change', async function () {
                        const url = this.dataset.url;
                        const token = document.querySelector('meta[name="csrf-token"]').content;

                        try {
                            const response = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': token,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ _token: token })
                            });

                            if (response.ok) {
                                // Optional: Show toast or just silence implies success
                                window.location.reload(); // Reload to sync state if needed or just keep it
                            } else {
                                const data = await response.json();
                                alert(data.message || 'Erro ao atualizar.');
                                this.checked = !this.checked; // Revert
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('Erro de conexão.');
                            this.checked = !this.checked; // Revert
                        }
                    });
                });
            });

            // Auto-scroll para a data selecionada
            document.addEventListener('DOMContentLoaded', function () {
                const activeDate = document.querySelector('.bg-white.border-primary');
                if (activeDate) {
                    activeDate.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                }
            });
        </script>
    @endpush
@endsection