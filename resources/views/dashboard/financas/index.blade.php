@extends('dashboard.layouts.app')

@section('page_title', 'Finanças')
@section('page_subtitle', 'Analise o desempenho financeiro do seu negócio')

@section('page_actions')
    <form method="GET" action="{{ route('dashboard.financas.index') }}" class="flex items-center gap-2">
        <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}"
            class="flex h-10 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
        <span class="text-muted-foreground">até</span>
        <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}"
            class="flex h-10 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
        <button type="submit"
            class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-calendar h-4 w-4">
                <path d="M8 2v4" />
                <path d="M16 2v4" />
                <rect width="18" height="18" x="3" y="4" rx="2" />
                <path d="M3 10h18" />
            </svg>
            Filtrar
        </button>
    </form>
@endsection

@section('content')
    <div class="space-y-6">
        <!-- Cards de Métricas Financeiras -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="p-6 flex items-center justify-between pb-2">
                    <h3 class="text-sm font-medium text-muted-foreground">Receitas</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-trending-up h-4 w-4 text-green-500">
                        <polyline points="22 7 13.5 15.5 8.5 10.5 2 17" />
                        <polyline points="16 7 22 7 22 13" />
                    </svg>
                </div>
                <div class="p-6 pt-0">
                    <div class="text-2xl font-bold text-green-600">R$ {{ number_format($receitas ?? 0, 2, ',', '.') }}</div>
                </div>
            </div>

            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="p-6 flex items-center justify-between pb-2">
                    <h3 class="text-sm font-medium text-muted-foreground">Despesas</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-trending-down h-4 w-4 text-red-500">
                        <polyline points="22 17 13.5 8.5 8.5 13.5 2 7" />
                        <polyline points="16 17 22 17 22 11" />
                    </svg>
                </div>
                <div class="p-6 pt-0">
                    <div class="text-2xl font-bold text-red-600">R$ {{ number_format($despesas ?? 0, 2, ',', '.') }}</div>
                </div>
            </div>

            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="p-6 flex items-center justify-between pb-2">
                    <h3 class="text-sm font-medium text-muted-foreground">Lucro</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-wallet h-4 w-4 text-muted-foreground">
                        <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4" />
                        <path d="M3 5v14a2 2 0 0 0 2 2h16v-5" />
                        <path d="M18 12a2 2 0 0 0 0 4h4v-4Z" />
                    </svg>
                </div>
                <div class="p-6 pt-0">
                    <div class="text-2xl font-bold {{ ($lucro ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">R$
                        {{ number_format($lucro ?? 0, 2, ',', '.') }}
                    </div>
                </div>
            </div>

            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="p-6 flex items-center justify-between pb-2">
                    <h3 class="text-sm font-medium text-muted-foreground">Ticket Médio</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-receipt h-4 w-4 text-muted-foreground">
                        <path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z" />
                        <path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8" />
                        <path d="M12 17.5v-11" />
                    </svg>
                </div>
                <div class="p-6 pt-0">
                    <div class="text-2xl font-bold">R$ {{ number_format($averageTicket ?? 0, 2, ',', '.') }}</div>
                    <p class="text-xs text-muted-foreground">{{ $totalOrders ?? 0 }} pedidos no período</p>
                </div>
            </div>
        </div>

        <!-- Gráfico de Receitas x Despesas -->
        @if(isset($chartData) && !empty($chartData['labels']))
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-col space-y-1.5 p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-semibold tracking-tight text-lg">Receitas x Despesas</h3>
                            <p class="text-sm text-muted-foreground">Evolução financeira no período</p>
                        </div>
                    </div>
                </div>
                <div class="p-6 pt-0">
                    <canvas id="financasChart" height="100"></canvas>
                </div>
            </div>
        @endif

        <!-- Cards de Métricas de Pedidos -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="p-6 flex items-center justify-between pb-2">
                    <h3 class="text-sm font-medium text-muted-foreground">Total de Pedidos</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-shopping-bag h-4 w-4 text-muted-foreground">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
                        <path d="M3 6h18" />
                        <path d="M16 10a4 4 0 0 1-8 0" />
                    </svg>
                </div>
                <div class="p-6 pt-0">
                    <div class="text-2xl font-bold">{{ $totalOrders ?? 0 }}</div>
                </div>
            </div>

            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="p-6 flex items-center justify-between pb-2">
                    <h3 class="text-sm font-medium text-muted-foreground">Novos Clientes</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-users h-4 w-4 text-muted-foreground">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                </div>
                <div class="p-6 pt-0">
                    <div class="text-2xl font-bold">{{ $newCustomers ?? 0 }}</div>
                </div>
            </div>

            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="p-6 flex items-center justify-between pb-2">
                    <h3 class="text-sm font-medium text-muted-foreground">Produtos Vendidos</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-package h-4 w-4 text-muted-foreground">
                        <path
                            d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z" />
                        <path d="M12 22V12" />
                        <path d="m3.3 7 7.703 4.734a2 2 0 0 0 1.994 0L20.7 7" />
                        <path d="m7.5 4.27 9 5.15" />
                    </svg>
                </div>
                <div class="p-6 pt-0">
                    <div class="text-2xl font-bold">{{ number_format($productsSold ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>

            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="p-6 flex items-center justify-between pb-2">
                    <h3 class="text-sm font-medium text-muted-foreground">Taxa de Conversão</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-percent h-4 w-4 text-muted-foreground">
                        <line x1="19" x2="5" y1="5" y2="19" />
                        <circle cx="6.5" cy="6.5" r="2.5" />
                        <circle cx="17.5" cy="17.5" r="2.5" />
                    </svg>
                </div>
                <div class="p-6 pt-0">
                    <div class="text-2xl font-bold">{{ number_format($conversionRate ?? 0, 2, ',', '.') }}%</div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Pedidos por Dia -->
        @if(isset($ordersChartData) && !empty($ordersChartData['labels']))
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-col space-y-1.5 p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-semibold tracking-tight text-lg">Gráfico de Pedidos</h3>
                            <p class="text-sm text-muted-foreground">Pedidos por dia no período selecionado</p>
                        </div>
                    </div>
                </div>
                <div class="p-6 pt-0">
                    <canvas id="ordersChart" height="100"></canvas>
                </div>
            </div>
        @endif

        <div class="grid gap-4 md:grid-cols-2">
            <!-- Pedidos por Status -->
            @if(isset($statusSummary) && $statusSummary->count() > 0)
                <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                    <div class="flex flex-col space-y-1.5 p-6">
                        <h3 class="font-semibold tracking-tight text-lg">Pedidos por Status</h3>
                        <p class="text-sm text-muted-foreground">Distribuição dos pedidos no período</p>
                    </div>
                    <div class="p-6 pt-0">
                        <div class="space-y-2">
                            @foreach($statusSummary as $status => $count)
                                <div class="flex items-center justify-between p-2 border rounded">
                                    <span class="capitalize">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                                    <span class="font-semibold">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Últimas Transações -->
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-col space-y-1.5 p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-semibold tracking-tight text-lg">Últimas Transações</h3>
                            <p class="text-sm text-muted-foreground">Movimentações financeiras recentes</p>
                        </div>
                        <button onclick="document.getElementById('addTransactionModal').showModal()"
                            class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3 gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-plus h-4 w-4">
                                <path d="M5 12h14" />
                                <path d="M12 5v14" />
                            </svg>
                            Adicionar
                        </button>
                    </div>
                </div>
                <div class="p-6 pt-0">
                    @if(isset($transactions) && $transactions->count() > 0)
                            <div class="space-y-2">
                                @foreach($transactions as $t)
                                    @if($t->order_id)
                                        <a href="{{ route('dashboard.orders.show', $t->order_id) }}"
                                            class="flex items-center justify-between p-2 border rounded hover:bg-accent transition-colors cursor-pointer">
                                    @else
                                            <div class="flex items-center justify-between p-2 border rounded">
                                        @endif
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="w-2 h-2 rounded-full {{ $t->type === 'revenue' ? 'bg-green-500' : 'bg-red-500' }}">
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium">
                                                        {{ $t->description ?: ($t->type === 'revenue' ? 'Receita' : 'Despesa') }}
                                                    </p>
                                                    <p class="text-xs text-muted-foreground">
                                                        {{ $t->transaction_date?->format('d/m/Y') }}
                                                    </p>
                                                </div>
                                            </div>
                                            <span
                                                class="font-semibold {{ $t->type === 'revenue' ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $t->type === 'revenue' ? '+' : '-' }}R$ {{ number_format($t->amount, 2, ',', '.') }}
                                            </span>
                                            @if($t->order_id)
                                                </a>
                                            @else
                                        </div>
                                    @endif
                                @endforeach
                        </div>
                    @else
                    <p class="text-sm text-muted-foreground text-center py-4">Nenhuma transação no período.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Resumo do Período -->
    <div class="grid gap-4 md:grid-cols-2">
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-semibold tracking-tight text-lg">Relatório de Vendas</h3>
                        <p class="text-sm text-muted-foreground">Análise completa das vendas do período</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-trending-up h-5 w-5 text-primary">
                        <polyline points="22 7 13.5 15.5 8.5 10.5 2 17" />
                        <polyline points="16 7 22 7 22 13" />
                    </svg>
                </div>
            </div>
            <div class="p-6 pt-0">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-muted-foreground">{{ $startDate->format('d/m/Y') }} até
                        {{ $endDate->format('d/m/Y') }}</span>
                    <a href="{{ route('dashboard.financas.relatorio-vendas', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}"
                        class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3 gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-external-link h-4 w-4">
                            <path d="M15 3h6v6" />
                            <path d="M10 14 21 3" />
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                        </svg>
                        Ver Detalhes
                    </a>
                </div>
            </div>
        </div>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-semibold tracking-tight text-lg">Resumo do Período</h3>
                        <p class="text-sm text-muted-foreground">Estatísticas gerais de vendas</p>
                    </div>
                </div>
            </div>
            <div class="p-6 pt-0">
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-muted-foreground">Ticket Médio:</span>
                        <span class="font-semibold">R$ {{ number_format($averageTicket ?? 0, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-muted-foreground">Período:</span>
                        <span class="text-sm">{{ $startDate->format('d/m/Y') }} até
                            {{ $endDate->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Modal Adicionar Transação -->
    <dialog id="addTransactionModal"
        class="rounded-lg border bg-background p-0 w-full max-w-md shadow-lg backdrop:bg-black/50">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-lg">Adicionar Lançamento</h3>
                <button onclick="document.getElementById('addTransactionModal').close()"
                    class="text-muted-foreground hover:text-foreground">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="h-5 w-5">
                        <path d="M18 6 6 18" />
                        <path d="m6 6 12 12" />
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('dashboard.financas.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="_redirect" value="{{ request()->fullUrl() }}">

                <div>
                    <label class="text-sm font-medium">Tipo</label>
                    <select name="type" required
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        <option value="revenue">Receita</option>
                        <option value="expense">Despesa</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium">Valor</label>
                    <input type="number" name="amount" step="0.01" min="0.01" required
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        placeholder="0,00">
                </div>

                <div>
                    <label class="text-sm font-medium">Descrição</label>
                    <input type="text" name="description"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        placeholder="Descrição do lançamento">
                </div>

                <div>
                    <label class="text-sm font-medium">Data</label>
                    <input type="date" name="transaction_date" value="{{ now()->format('Y-m-d') }}" required
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                </div>

                <div>
                    <label class="text-sm font-medium">Categoria</label>
                    <input type="text" name="category" list="categoriasList"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        placeholder="Ex: Fornecedores, Aluguel">
                    <datalist id="categoriasList">
                        @foreach($categorias ?? [] as $cat)
                            <option value="{{ $cat }}">
                        @endforeach
                        <option value="Fornecedores">
                        <option value="Aluguel">
                        <option value="Energia">
                        <option value="Água">
                        <option value="Internet">
                        <option value="Marketing">
                        <option value="Manutenção">
                    </datalist>
                </div>

                <div class="flex gap-2 justify-end pt-2">
                    <button type="button" onclick="document.getElementById('addTransactionModal').close()"
                        class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Gráfico de Receitas x Despesas
                const financasCtx = document.getElementById('financasChart');
                if (financasCtx) {
                    new Chart(financasCtx, {
                        type: 'bar',
                        data: {
                            labels: @json($chartData['labels'] ?? []),
                            datasets: [
                                {
                                    label: 'Receitas',
                                    data: @json($chartData['receitas'] ?? []),
                                    backgroundColor: 'rgba(34, 197, 94, 0.7)',
                                    borderColor: 'rgb(34, 197, 94)',
                                    borderWidth: 1
                                },
                                {
                                    label: 'Despesas',
                                    data: @json($chartData['despesas'] ?? []),
                                    backgroundColor: 'rgba(239, 68, 68, 0.7)',
                                    borderColor: 'rgb(239, 68, 68)',
                                    borderWidth: 1
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    position: 'top'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function (value) {
                                            return 'R$ ' + value.toLocaleString('pt-BR');
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                // Gráfico de Pedidos
                const ordersCtx = document.getElementById('ordersChart');
                if (ordersCtx) {
                    new Chart(ordersCtx, {
                        type: 'line',
                        data: {
                            labels: @json($ordersChartData['labels'] ?? []),
                            datasets: [{
                                label: 'Pedidos',
                                data: @json($ordersChartData['data'] ?? []),
                                borderColor: 'rgb(122, 82, 48)',
                                backgroundColor: 'rgba(122, 82, 48, 0.1)',
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection