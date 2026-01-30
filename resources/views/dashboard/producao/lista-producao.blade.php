@extends('dashboard.layouts.app')

@section('page_title', 'Lista de Produção')
@section('page_subtitle', 'Acompanhamento da produção diária')

@section('content')
<div class="space-y-6">
    <div class="bg-card rounded-xl border border-border p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h3 class="font-semibold text-lg">Produção – {{ \Carbon\Carbon::parse($date)->translatedFormat('d/m/Y') }}</h3>
                <p class="text-sm text-muted-foreground">{{ $list ? $list->total_items : 0 }} itens na lista</p>
            </div>
            <div class="flex gap-2 items-center">
                @php $countPrint = $list ? $list->items->where('mark_for_print', true)->count() : 0; @endphp
                @if($list && $countPrint > 0)
                    <a href="{{ route('dashboard.producao.lista-producao.print', ['id' => $list->id]) }}" 
                       target="_blank"
                       class="relative inline-flex items-center justify-center whitespace-nowrap rounded-full text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-red-600 text-white hover:bg-red-700 h-12 w-12 p-0 shadow-lg">
                        <i data-lucide="printer" class="h-6 w-6"></i>
                        <span class="absolute -top-1 -right-1 bg-white text-red-600 rounded-full h-6 w-6 flex items-center justify-center text-xs font-bold border-2 border-red-600">
                            {{ $countPrint }}
                        </span>
                    </a>
                @endif
                @if(!$list)
                    <form action="{{ route('dashboard.producao.lista-producao.create') }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="date" name="production_date" value="{{ $date }}" class="form-input">
                        <button type="submit" class="btn-primary gap-2">
                            <i data-lucide="plus" class="h-4 w-4"></i>
                            Criar Lista
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if($list)
            <form action="{{ route('dashboard.producao.lista-producao.add-item', $list->id) }}" method="POST" class="mb-6 p-4 bg-muted/30 rounded-lg flex flex-wrap gap-2">
                @csrf
                <select name="recipe_id" required class="form-input flex-1 min-w-[200px]">
                    <option value="">Selecione uma receita...</option>
                    @foreach($recipes as $recipe)
                        <option value="{{ $recipe->id }}">{{ $recipe->name }} ({{ number_format($recipe->total_weight, 0, ',', '.') }}g)</option>
                    @endforeach
                </select>
                <input type="number" name="quantity" value="1" min="1" required class="form-input w-24" placeholder="Qtd">
                <input type="number" name="weight" step="0.01" min="0" class="form-input w-32" placeholder="Peso (g)">
                <input type="text" name="observation" class="form-input flex-1 min-w-[120px]" placeholder="Obs (opcional)">
                <button type="submit" class="btn-primary gap-2">
                    <i data-lucide="plus" class="h-4 w-4"></i>
                    Adicionar
                </button>
            </form>

            <p class="text-sm text-muted-foreground mb-3">Por padrão, todos os itens são marcados para impressão. Desmarque para excluir da fila.</p>
            <div class="space-y-2">
                @forelse($list->items as $item)
                <div class="flex items-center gap-4 p-4 border border-border rounded-lg {{ $item->is_produced ? 'bg-green-50/50' : '' }}" data-item-id="{{ $item->id }}">
                    <label class="flex-shrink-0 flex items-center gap-2 cursor-pointer" title="Marcar para impressão">
                        <input type="checkbox" 
                               class="mark-print-checkbox w-5 h-5 rounded border-2 border-border text-primary focus:ring-primary" 
                               {{ ($item->mark_for_print ?? true) ? 'checked' : '' }}
                               data-item-id="{{ $item->id }}"
                               data-url="{{ route('dashboard.producao.lista-producao.mark-print', $item->id) }}">
                        <span class="text-xs text-muted-foreground hidden sm:inline">Imprimir</span>
                    </label>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-semibold">{{ $item->recipe_name }}</h4>
                        <p class="text-sm text-muted-foreground">
                            {{ $item->quantity }}x {{ number_format($item->weight, 0, ',', '.') }}g = {{ number_format($item->quantity * $item->weight, 0, ',', '.') }}g
                        </p>
                        @if($item->observation)
                            <p class="text-sm text-amber-700/80 bg-amber-50 rounded px-2 py-1 mt-1"><strong>Obs:</strong> {{ $item->observation }}</p>
                        @endif
                        @if($item->recipe)
                            @php
                                $calculated = $item->recipe->calculateIngredientWeights($item->weight);
                                $ingredients = [];
                                foreach ($calculated as $key => $row) {
                                    $w = (float) ($row['weight'] ?? 0) * $item->quantity;
                                    if ($w <= 0) continue;
                                    
                                    if ($key === '_water') {
                                        $ingredients[] = [
                                            'name' => $row['label'] ?? 'Água (hidratação)',
                                            'weight' => $w,
                                        ];
                                        continue;
                                    }
                                    
                                    if ($key === '_levain') {
                                        $ingredients[] = [
                                            'name' => $row['label'] ?? 'Levain',
                                            'weight' => $w,
                                        ];
                                        continue;
                                    }
                                    
                                    if (strpos($key, '_') === 0) continue;
                                    
                                    $ing = $row['ingredient'] ?? null;
                                    if (!$ing) continue;
                                    
                                    $ingredients[] = [
                                        'name' => $ing->name ?? 'Ingrediente desconhecido',
                                        'weight' => $w,
                                    ];
                                }
                                usort($ingredients, function ($a, $b) {
                                    return strcmp($a['name'], $b['name']);
                                });
                            @endphp
                            @if(count($ingredients) > 0)
                                <div class="mt-2 pt-2 border-t border-border">
                                    <p class="text-xs font-semibold text-muted-foreground mb-1">Ingredientes:</p>
                                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-muted-foreground">
                                        @foreach($ingredients as $ing)
                                            <span>{{ $ing['name'] }}: <strong>{{ number_format($ing['weight'], 1, ',', '.') }}g</strong></span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                    <form action="{{ route('dashboard.producao.lista-producao.remove-item', $item->id) }}" method="POST" onsubmit="return confirm('Remover item?');" class="flex-shrink-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-outline text-destructive h-9 w-9 p-0">
                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                        </button>
                    </form>
                </div>
                @empty
                <div class="text-center py-12 text-muted-foreground">
                    <i data-lucide="list-todo" class="w-12 h-12 mx-auto mb-4 opacity-50"></i>
                    <p>Nenhum item na lista</p>
                </div>
                @endforelse
            </div>
        @else
            <div class="text-center py-12 text-muted-foreground">
                <i data-lucide="list-todo" class="w-12 h-12 mx-auto mb-4 opacity-50"></i>
                <p>Nenhuma lista criada para esta data</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.mark-print-checkbox').forEach(function(cb) {
        cb.addEventListener('change', function() {
            var url = this.dataset.url;
            var token = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
            var xhr = new XMLHttpRequest();
            xhr.open('POST', url);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.setRequestHeader('X-CSRF-TOKEN', token);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    window.location.reload();
                } else {
                    try { var r = JSON.parse(xhr.responseText || '{}'); } catch(e) { var r = {}; }
                    alert(r.message || 'Erro ao atualizar.');
                    cb.checked = !cb.checked;
                }
            };
            xhr.onerror = function() {
                alert('Erro de conexão.');
                cb.checked = !cb.checked;
            };
            xhr.send(JSON.stringify({ _token: token }));
        });
    });
});
</script>
@endpush
@endsection
