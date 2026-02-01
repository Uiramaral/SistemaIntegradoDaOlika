@extends('dashboard.layouts.app')

@section('page_title', 'Lista de Compras')
@section('page_subtitle', 'Ingredientes necessários para produção')

@section('content')
    <div class="bg-card rounded-xl border border-border overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-border">
            <h3 class="font-semibold text-lg flex items-center gap-2">
                <i data-lucide="calendar" class="w-5 h-5 text-primary"></i>
                {{ \Carbon\Carbon::parse($date)->translatedFormat('d/m/Y') }}
            </h3>
        </div>

        @if(count($shoppingList) > 0)
            {{-- Desktop Table View --}}
            <div class="hidden md:block">
                <table class="w-full text-sm">
                    <thead class="bg-muted/50 border-b border-border">
                        <tr>
                            <th class="text-left font-semibold uppercase py-3 px-6">Ingrediente</th>
                            <th class="text-left font-semibold uppercase py-3 px-6">Estoque Atual</th>
                            <th class="text-left font-semibold uppercase py-3 px-6">Necessário</th>
                            <th class="text-left font-semibold uppercase py-3 px-6">Falta</th>
                            <th class="text-right font-semibold uppercase py-3 px-6">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($shoppingList as $item)
                            @php
                                $needed = $item['needed'];
                                $current = $item['current_stock'];
                                $missing = max(0, $needed - $current);
                                $isEnough = $current >= $needed;
                            @endphp
                            <tr class="hover:bg-muted/30 transition-colors">
                                <td class="py-4 px-6 font-medium text-foreground">
                                    {{ $item['ingredient']->name }}
                                </td>
                                <td class="py-4 px-6 text-muted-foreground">
                                    {{ number_format($current, 2, ',', '.') }} {{ $item['ingredient']->unit ?? 'g' }}
                                </td>
                                <td class="py-4 px-6 font-semibold">
                                    {{ number_format($needed, 2, ',', '.') }} {{ $item['ingredient']->unit ?? 'g' }}
                                </td>
                                <td class="py-4 px-6">
                                    @if($missing > 0)
                                        <span class="text-red-600 font-bold bg-red-50 px-2 py-1 rounded">
                                            -{{ number_format($missing, 2, ',', '.') }} {{ $item['ingredient']->unit ?? 'g' }}
                                        </span>
                                    @else
                                        <span class="text-green-600 font-medium">OK</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <span class="status-badge {{ $isEnough ? 'status-badge-completed' : 'status-badge-pending' }}">
                                        {{ $isEnough ? 'OK' : 'Comprar' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile Cards View --}}
            <div class="md:hidden divide-y divide-border">
                @foreach($shoppingList as $item)
                    @php
                        $needed = $item['needed'];
                        $current = $item['current_stock'];
                        $missing = max(0, $needed - $current);
                        $isEnough = $current >= $needed;
                    @endphp
                    <div class="p-4 space-y-3">
                        <div class="flex justify-between items-start">
                            <h4 class="font-semibold text-foreground">{{ $item['ingredient']->name }}</h4>
                            <span class="status-badge {{ $isEnough ? 'status-badge-completed' : 'status-badge-pending' }}">
                                {{ $isEnough ? 'OK' : 'Comprar' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div class="bg-muted/30 p-2 rounded">
                                <p class="text-[10px] uppercase text-muted-foreground font-semibold">Estoque</p>
                                <p class="font-medium">{{ number_format($current, 2, ',', '.') }}</p>
                            </div>
                            <div class="bg-muted/30 p-2 rounded">
                                <p class="text-[10px] uppercase text-muted-foreground font-semibold">Necessário</p>
                                <p class="font-medium">{{ number_format($needed, 2, ',', '.') }}</p>
                            </div>
                        </div>

                        @if($missing > 0)
                            <div class="flex items-center gap-2 text-red-600 bg-red-50 p-2 rounded text-sm font-medium">
                                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                                Faltam {{ number_format($missing, 2, ',', '.') }} {{ $item['ingredient']->unit ?? 'g' }} para produção
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-16 text-muted-foreground">
                <div class="bg-muted/30 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="check-circle" class="w-8 h-8 opacity-50 text-green-600"></i>
                </div>
                <h3 class="font-semibold text-foreground text-lg">Tudo Certo!</h3>
                <p>Nenhum ingrediente faltando para a produção desta data.</p>
            </div>
        @endif
    </div>
@endsection