<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\AnalyticsEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class FinancasController extends Controller
{
    /**
     * Dashboard principal de finanças (unificado com relatórios)
     */
    public function index(Request $request)
    {
        // Determinar período
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : now()->startOfMonth();
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : now()->endOfMonth();

        $clientId = currentClientId();

        // ===============================
        // DADOS FINANCEIROS
        // ===============================
        $baseQuery = FinancialTransaction::query()
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->whereBetween('transaction_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where(function ($q) {
                $q->whereNull('order_id')
                    ->orWhereHas('order', function ($sq) {
                        $sq->whereNotIn('status', ['cancelled', 'canceled', 'trash'])
                            ->whereIn('payment_status', ['paid', 'approved']);
                    });
            });

        $receitas = (clone $baseQuery)->where('type', 'revenue')->sum('amount');
        $despesas = (clone $baseQuery)->where('type', 'expense')->sum('amount');
        $lucro = $receitas - $despesas;

        // Lista de transações (últimas 10)
        $transactions = (clone $baseQuery)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Gráfico de receitas x despesas
        $chartData = $this->getChartData($clientId, 'mes', $startDate, $endDate);

        // ===============================
        // DADOS DE RELATÓRIOS/PEDIDOS
        // ===============================

        // Total de pedidos pagos no período
        $ordersQuery = Order::query()
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->whereNotIn('status', ['cancelled', 'canceled', 'trash'])
            ->whereIn('payment_status', ['paid', 'approved'])
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->copy()->endOfDay()]);

        $totalOrders = (clone $ordersQuery)->count();
        $totalAmount = $receitas; // Receita já calculada acima
        $averageTicket = $totalOrders > 0 ? $totalAmount / $totalOrders : 0;

        // Novos clientes no período
        $newCustomers = Customer::query()
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Produtos vendidos
        $orderIds = (clone $ordersQuery)->pluck('id')->toArray();
        $productsSold = OrderItem::whereIn('order_id', $orderIds)->sum('quantity') ?? 0;

        // Pedidos por status (todos do período, não só pagos)
        $statusSummary = Order::query()
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->whereBetween('created_at', [$startDate, $endDate->copy()->endOfDay()])
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // Gráfico de pedidos por dia
        $ordersChartData = $this->getOrdersChartData($clientId, $startDate, $endDate);

        // ===============================
        // MÉTRICAS DE ANALYTICS
        // ===============================
        $pageViews = 0;
        $addToCartEvents = 0;
        $checkoutStarted = 0;
        $purchases = 0;
        $conversionRate = 0;
        $cartAbandonment = 0;
        $checkoutCompletionRate = 0;

        if (Schema::hasTable('analytics_events')) {
            $analyticsQuery = AnalyticsEvent::query()
                ->when($clientId, fn($q) => $q->where('client_id', $clientId))
                ->whereBetween('created_at', [$startDate, $endDate->copy()->endOfDay()]);

            $pageViews = (clone $analyticsQuery)->where('event_type', 'page_view')->count();
            $addToCartEvents = (clone $analyticsQuery)->where('event_type', 'add_to_cart')->count();
            $checkoutStarted = (clone $analyticsQuery)->where('event_type', 'checkout_started')->count();
            $purchases = (clone $analyticsQuery)->where('event_type', 'purchase')->count();

            $conversionRate = $pageViews > 0 ? ($purchases / $pageViews) * 100 : 0;
            $cartAbandonment = $addToCartEvents > 0 ? (($addToCartEvents - $checkoutStarted) / $addToCartEvents) * 100 : 0;
            $checkoutCompletionRate = $checkoutStarted > 0 ? ($purchases / $checkoutStarted) * 100 : 0;
        }

        // Categorias para modal de lançamento
        $categorias = FinancialTransaction::query()
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        return view('dashboard.financas.index', compact(
            'startDate',
            'endDate',
            // Financeiro
            'receitas',
            'despesas',
            'lucro',
            'transactions',
            'chartData',
            'categorias',
            // Pedidos
            'totalOrders',
            'totalAmount',
            'averageTicket',
            'newCustomers',
            'productsSold',
            'statusSummary',
            'ordersChartData',
            // Analytics
            'pageViews',
            'addToCartEvents',
            'checkoutStarted',
            'purchases',
            'conversionRate',
            'cartAbandonment',
            'checkoutCompletionRate'
        ));
    }

    /**
     * API: Dados para atualização dinâmica de cards
     */
    public function apiResumo(Request $request)
    {
        $periodo = $request->get('periodo', 'mes');
        $dataInicio = $request->get('data_inicio');
        $dataFim = $request->get('data_fim');

        [$start, $end] = $this->resolvePeriodo($periodo, $dataInicio, $dataFim);

        $clientId = currentClientId();

        $baseQuery = FinancialTransaction::query()
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->whereBetween('transaction_date', [$start, $end])
            ->where(function ($q) {
                $q->whereNull('order_id')
                    ->orWhereHas('order', function ($sq) {
                        $sq->whereNotIn('status', ['cancelled', 'canceled', 'trash'])
                            ->whereIn('payment_status', ['paid', 'approved']);
                    });
            });

        return response()->json([
            'receitas' => (float) (clone $baseQuery)->where('type', 'revenue')->sum('amount'),
            'despesas' => (float) (clone $baseQuery)->where('type', 'expense')->sum('amount'),
            'countReceitas' => (clone $baseQuery)->where('type', 'revenue')->count(),
            'countDespesas' => (clone $baseQuery)->where('type', 'expense')->count(),
        ]);
    }

    /**
     * API: Dados para gráficos
     */
    public function apiChartData(Request $request)
    {
        $periodo = $request->get('periodo', 'mes');
        $dataInicio = $request->get('data_inicio');
        $dataFim = $request->get('data_fim');

        [$start, $end] = $this->resolvePeriodo($periodo, $dataInicio, $dataFim);

        $clientId = currentClientId();

        return response()->json($this->getChartData($clientId, $periodo, $start, $end));
    }

    /**
     * Gerar dados para gráfico
     */
    private function getChartData($clientId, string $periodo, Carbon $start, Carbon $end): array
    {
        $labels = [];
        $receitas = [];
        $despesas = [];

        // Determinar agrupamento baseado no período
        if ($periodo === 'hoje') {
            // Por hora (últimas 24h)
            for ($i = 0; $i < 24; $i++) {
                $hora = Carbon::today()->addHours($i);
                $labels[] = $hora->format('H:00');

                $r = FinancialTransaction::query()
                    ->when($clientId, fn($q) => $q->where('client_id', $clientId))
                    ->where('type', 'revenue')
                    ->whereDate('transaction_date', Carbon::today())
                    ->sum('amount');

                $receitas[] = $i === now()->hour ? (float) $r : 0;
                $despesas[] = 0;
            }
        } elseif (in_array($periodo, ['semana', 'mes'])) {
            // Por dia
            $current = $start->copy();
            while ($current <= $end) {
                $labels[] = $current->format('d/m');

                $dayQuery = FinancialTransaction::query()
                    ->when($clientId, fn($q) => $q->where('client_id', $clientId))
                    ->whereDate('transaction_date', $current)
                    ->where(function ($q) {
                        $q->whereNull('order_id')
                            ->orWhereHas('order', fn($sq) => $sq->whereNotIn('status', ['cancelled', 'canceled', 'trash']));
                    });

                $receitas[] = (float) (clone $dayQuery)->where('type', 'revenue')->sum('amount');
                $despesas[] = (float) (clone $dayQuery)->where('type', 'expense')->sum('amount');

                $current->addDay();
            }
        } else {
            // Por mês (ano ou personalizado longo)
            $current = $start->copy()->startOfMonth();
            while ($current <= $end) {
                $labels[] = $current->translatedFormat('M/y');

                $monthQuery = FinancialTransaction::query()
                    ->when($clientId, fn($q) => $q->where('client_id', $clientId))
                    ->whereYear('transaction_date', $current->year)
                    ->whereMonth('transaction_date', $current->month)
                    ->where(function ($q) {
                        $q->whereNull('order_id')
                            ->orWhereHas('order', fn($sq) => $sq->whereNotIn('status', ['cancelled', 'canceled', 'trash']));
                    });

                $receitas[] = (float) (clone $monthQuery)->where('type', 'revenue')->sum('amount');
                $despesas[] = (float) (clone $monthQuery)->where('type', 'expense')->sum('amount');

                $current->addMonth();
            }
        }

        return [
            'labels' => $labels,
            'receitas' => $receitas,
            'despesas' => $despesas,
        ];
    }

    /**
     * Relatório de vendas
     */
    public function relatorioVendas(Request $request)
    {
        $periodo = $request->get('periodo', 'mes');
        $dataInicio = $request->get('data_inicio');
        $dataFim = $request->get('data_fim');
        $metodoPagamento = $request->get('metodo_pagamento');

        [$start, $end] = $this->resolvePeriodo($periodo, $dataInicio, $dataFim);

        $clientId = currentClientId();

        // Query de pedidos (não de transações)
        $query = Order::query()
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->whereNotIn('status', ['cancelled', 'canceled', 'trash'])
            ->whereIn('payment_status', ['paid', 'approved'])
            ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
            ->when($metodoPagamento, fn($q) => $q->where('payment_method', $metodoPagamento))
            ->orderBy('created_at', 'desc');

        $vendas = $query->paginate(20)->withQueryString();

        // Totais
        $totalVendas = (clone $query)->sum('final_amount');
        $quantidadeVendas = (clone $query)->count();

        // Métodos de pagamento disponíveis
        $metodosPagamento = Order::query()
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->whereNotNull('payment_method')
            ->distinct()
            ->pluck('payment_method');

        return view('dashboard.financas.relatorio-vendas', compact(
            'vendas',
            'totalVendas',
            'quantidadeVendas',
            'periodo',
            'start',
            'end',
            'metodoPagamento',
            'metodosPagamento'
        ));
    }

    /**
     * Salvar nova transação (receita ou despesa)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:revenue,expense',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
            'transaction_date' => 'required|date',
            'category' => 'nullable|string|max:64',
        ], [
            'type.required' => 'Informe se é receita ou despesa.',
            'amount.required' => 'Informe o valor.',
            'amount.min' => 'O valor deve ser maior que zero.',
            'transaction_date.required' => 'Informe a data.',
        ]);

        $validated['client_id'] = currentClientId();

        FinancialTransaction::create($validated);

        $redirect = $request->get('_redirect');
        $url = $redirect && is_string($redirect) ? $redirect : route('dashboard.financas.index');

        $msg = $validated['type'] === 'revenue' ? 'Receita registrada com sucesso.' : 'Despesa registrada com sucesso.';
        return redirect($url)->with('success', $msg);
    }

    /**
     * Atualizar transação existente
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'type' => 'required|in:revenue,expense',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
            'transaction_date' => 'required|date',
            'category' => 'nullable|string|max:64',
        ]);

        $clientId = currentClientId();
        $transaction = FinancialTransaction::query()
            ->where('id', $id)
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->firstOrFail();

        // Não permitir editar transações vinculadas a pedidos
        if ($transaction->order_id) {
            return back()->with('error', 'Transações de pedidos não podem ser editadas manualmente.');
        }

        $transaction->update($validated);

        $redirect = $request->get('_redirect');
        $url = $redirect && is_string($redirect) ? $redirect : route('dashboard.financas.index');
        return redirect($url)->with('success', 'Lançamento atualizado com sucesso.');
    }

    /**
     * Excluir transação
     */
    public function destroy(int $id)
    {
        $clientId = currentClientId();
        $transaction = FinancialTransaction::query()
            ->where('id', $id)
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->firstOrFail();

        $transaction->delete();

        $redirect = request()->get('_redirect');
        $url = $redirect && is_string($redirect) ? $redirect : route('dashboard.financas.index');
        return redirect($url)->with('success', 'Lançamento excluído.');
    }

    /**
     * Resolver período para datas de início e fim
     */
    private function resolvePeriodo(string $periodo, ?string $dataInicio = null, ?string $dataFim = null): array
    {
        $now = Carbon::now();

        switch ($periodo) {
            case 'hoje':
                return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];

            case 'semana':
                return [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()];

            case 'mes':
                return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];

            case 'ano':
                return [$now->copy()->startOfYear(), $now->copy()->endOfYear()];

            case 'personalizado':
                $start = $dataInicio ? Carbon::parse($dataInicio) : $now->copy()->subDays(30);
                $end = $dataFim ? Carbon::parse($dataFim) : $now->copy();
                return [$start->startOfDay(), $end->endOfDay()];

            default:
                return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
        }
    }

    /**
     * Gerar dados para gráfico de pedidos por dia
     */
    private function getOrdersChartData($clientId, Carbon $startDate, Carbon $endDate): array
    {
        $labels = [];
        $data = [];

        $current = $startDate->copy();
        while ($current <= $endDate) {
            $labels[] = $current->format('d/m');

            $count = Order::query()
                ->when($clientId, fn($q) => $q->where('client_id', $clientId))
                ->whereDate('created_at', $current)
                ->whereNotIn('status', ['cancelled', 'canceled', 'trash'])
                ->whereIn('payment_status', ['paid', 'approved'])
                ->count();

            $data[] = $count;
            $current->addDay();
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
}
