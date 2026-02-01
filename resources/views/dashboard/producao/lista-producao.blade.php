@extends('dashboard.layouts.app')

@section('page_title', 'Lista de Produção')
@section('page_subtitle', 'Acompanhamento da produção diária')

@section('content')
    <div class="space-y-6">
        <div class="bg-card rounded-xl border border-border overflow-hidden">
            {{-- Header --}}
            <div
                class="p-4 sm:p-6 border-b border-border flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h3 class="font-semibold text-lg flex items-center gap-2">
                        <i data-lucide="calendar" class="w-5 h-5 text-primary"></i>
                        Produção – {{ \Carbon\Carbon::parse($date)->translatedFormat('d/m/Y') }}
                    </h3>
                    <p class="text-sm text-muted-foreground mt-1">{{ $list ? $list->total_items : 0 }} itens na lista</p>
                </div>

                <div class="flex gap-2 items-center w-full sm:w-auto">
                    @php $countPrint = $list ? $list->items->where('mark_for_print', true)->count() : 0; @endphp
                    @if($list && $countPrint > 0)
                        <a href="{{ route('dashboard.producao.lista-producao.print', ['id' => $list->id]) }}" target="_blank"
                            class="btn-primary items-center justify-center h-10 w-full sm:w-auto sm:px-4 gap-2 text-white bg-red-600 hover:bg-red-700">
                            <i data-lucide="printer" class="h-4 w-4"></i>
                            Imprimir Selecionados ({{ $countPrint }})
                        </a>
                    @endif

                    @if(!$list)
                        <form action="{{ route('dashboard.producao.lista-producao.create') }}" method="POST"
                            class="flex gap-2 w-full sm:w-auto">
                            @csrf
                            <input type="date" name="production_date" value="{{ $date }}" class="form-input flex-1">
                            <button type="submit" class="btn-primary gap-2">
                                <i data-lucide="plus" class="h-4 w-4"></i>
                                Criar Lista
                            </button>
                        </form>
                    @endif
                </div>
            </div>

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
                            @forelse($list->items as $item)
                                <tr class="hover:bg-muted/30 transition-colors {{ $item->is_produced ? 'bg-green-50/30' : '' }}">
                                    <td class="py-4 px-4 text-center">
                                        <input type="checkbox"
                                            class="mark-print-checkbox w-5 h-5 rounded border-border text-primary focus:ring-primary cursor-pointer"
                                            {{ ($item->mark_for_print ?? true) ? 'checked' : '' }}
                                            data-url="{{ route('dashboard.producao.lista-producao.mark-print', $item->id) }}">
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
                                                // Simple logic to display ingredients inline
                                                $calculated = $item->recipe->calculateIngredientWeights($item->weight);
                                                $displayIngredients = [];
                                                foreach ($calculated as $key => $row) {
                                                    $w = (float) ($row['weight'] ?? 0) * $item->quantity;
                                                    if ($w <= 0)
                                                        continue;
                                                    if (strpos($key, '_') === 0 && !in_array($key, ['_water', '_levain']))
                                                        continue;

                                                    $name = $row['label'] ?? ($row['ingredient']->name ?? 'N/A');
                                                    if ($key === '_water')
                                                        $name = 'Água';
                                                    if ($key === '_levain')
                                                        $name = 'Levain';

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
                                        <form action="{{ route('dashboard.producao.lista-producao.remove-item', $item->id) }}"
                                            method="POST" onsubmit="return confirm('Remover item?');">
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
                    @forelse($list->items as $item)
                        <div class="p-4 space-y-3 {{ $item->is_produced ? 'bg-green-50/30' : '' }}">
                            <div class="flex justify-between items-start">
                                <div class="flex items-start gap-3">
                                    <input type="checkbox"
                                        class="mark-print-checkbox mt-1 w-5 h-5 rounded border-border text-primary focus:ring-primary"
                                        {{ ($item->mark_for_print ?? true) ? 'checked' : '' }}
                                        data-url="{{ route('dashboard.producao.lista-producao.mark-print', $item->id) }}">
                                    <div>
                                        <h4 class="font-semibold text-foreground">{{ $item->recipe_name }}</h4>
                                        <p class="text-sm text-muted-foreground">
                                            {{ $item->quantity }}x {{ number_format($item->weight, 0, ',', '.') }}g =
                                            <strong>{{ number_format($item->quantity * $item->weight, 0, ',', '.') }}g</strong>
                                        </p>
                                    </div>
                                </div>

                                <form action="{{ route('dashboard.producao.lista-producao.remove-item', $item->id) }}" method="POST"
                                    onsubmit="return confirm('Remover item?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon btn-ghost h-8 w-8 text-destructive -mr-2">
                                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                                    </button>
                                </form>
                            </div>

                            @if($item->observation)
                                <div class="flex items-center gap-2 px-3 py-2 bg-amber-50 text-amber-800 rounded-lg text-sm">
                                    <i data-lucide="sticky-note" class="w-4 h-4 shrink-0"></i>
                                    {{ $item->observation }}
                                </div>
                            @endif

                            @if($item->recipe)
                                @php
                                    // Same logic for mobile
                                    $calculated = $item->recipe->calculateIngredientWeights($item->weight);
                                    $displayIngredients = [];
                                    foreach ($calculated as $key => $row) {
                                        $w = (float) ($row['weight'] ?? 0) * $item->quantity;
                                        if ($w <= 0)
                                            continue;
                                        if (strpos($key, '_') === 0 && !in_array($key, ['_water', '_levain']))
                                            continue;

                                        $name = $row['label'] ?? ($row['ingredient']->name ?? 'N/A');
                                        if ($key === '_water')
                                            $name = 'Água';
                                        if ($key === '_levain')
                                            $name = 'Levain';

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
                    <h3 class="text-lg font-medium text-foreground mb-2">Nenhuma lista para hoje</h3>
                    <p>Use o formulário acima para criar uma lista de produção para esta data.</p>
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
        </script>
    @endpush
@endsection