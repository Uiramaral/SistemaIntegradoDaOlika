<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    public function index(Request $req)
    {
        $periodo = $req->string('periodo','mes'); // hoje|semana|mes|custom
        $status  = $req->string('status'); // opcional
        $ini     = $req->date('ini');
        $fim     = $req->date('fim');

        $between = match ($periodo->toString()) {
            'hoje'   => [now()->startOfDay(), now()->endOfDay()],
            'semana' => [now()->startOfWeek(), now()->endOfWeek()],
            'mes'    => [now()->startOfMonth(), now()->endOfMonth()],
            'custom' => [$ini?->startOfDay() ?? now()->subDays(7), $fim?->endOfDay() ?? now()->endOfDay()],
            default  => [now()->startOfMonth(), now()->endOfMonth()],
        };

        // Base de pedidos no período
        $pedidosBase = Order::query()
            ->when($status->isNotEmpty(), fn($q)=>$q->where('status',$status))
            ->whereBetween('created_at', $between);

        // 2.1 — KPIs
        $kpIs = [
            'qtd_pedidos' => (clone $pedidosBase)->count(),
            'faturamento' => (clone $pedidosBase)->sum('final_amount'),
            'ticket_medio'=> (function() use ($pedidosBase){
                $qtd = (clone $pedidosBase)->count();
                return $qtd ? (clone $pedidosBase)->sum('final_amount') / $qtd : 0;
            })(),
        ];

        // 2.2 — Série diária (faturamento por dia)
        $serieDiaria = (clone $pedidosBase)
            ->select(DB::raw('DATE(created_at) as dia'), DB::raw('SUM(final_amount) as total'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('dia')
            ->get();

        // 2.3 — Top produtos no período
        $topProdutos = OrderItem::query()
            ->join('orders','order_items.order_id','=','orders.id')
            ->join('products','order_items.product_id','=','products.id')
            ->whereBetween('orders.created_at', $between)
            ->when($status->isNotEmpty(), fn($q)=>$q->where('orders.status',$status))
            ->select('products.id','products.name',
                DB::raw('SUM(order_items.quantity) as qtd'),
                DB::raw('SUM(order_items.total_price) as receita'))
            ->groupBy('products.id','products.name')
            ->orderByDesc('receita')
            ->limit(10)
            ->get();

        // 2.4 — Cupons usados
        $cuponsUsados = (clone $pedidosBase)
            ->whereNotNull('coupon_code')
            ->select('coupon_code', DB::raw('COUNT(*) as qtd'), DB::raw('SUM(final_amount) as receita'))
            ->groupBy('coupon_code')
            ->orderByDesc('qtd')
            ->get();

        // 2.5 — Geografia (bairro) - ajustado para buscar do cliente
        $porBairro = Order::query()
            ->join('customers','orders.customer_id','=','customers.id')
            ->whereBetween('orders.created_at', $between)
            ->when($status->isNotEmpty(), fn($q)=>$q->where('orders.status',$status))
            ->select('customers.neighborhood', DB::raw('COUNT(*) as qtd'), DB::raw('SUM(orders.final_amount) as receita'))
            ->groupBy('customers.neighborhood')
            ->orderByDesc('qtd')
            ->limit(12)
            ->get();

        return view('relatorios.index', [
            'kpIs' => $kpIs,
            'serieDiaria' => $serieDiaria,
            'topProdutos' => $topProdutos,
            'cuponsUsados' => $cuponsUsados,
            'porBairro' => $porBairro,
            'periodo' => $periodo,
            'ini' => $between[0],
            'fim' => $between[1],
            'status' => $status,
        ]);
    }

    public function export(Request $req): StreamedResponse
    {
        // Exporta CSV com pedidos do período
        $periodo = $req->string('periodo','mes');
        $status  = $req->string('status');
        $ini     = $req->date('ini');
        $fim     = $req->date('fim');

        $between = match ($periodo->toString()) {
            'hoje'   => [now()->startOfDay(), now()->endOfDay()],
            'semana' => [now()->startOfWeek(), now()->endOfWeek()],
            'mes'    => [now()->startOfMonth(), now()->endOfMonth()],
            'custom' => [$ini?->startOfDay() ?? now()->subDays(7), $fim?->endOfDay() ?? now()->endOfDay()],
            default  => [now()->startOfMonth(), now()->endOfMonth()],
        };

        $query = Order::query()
            ->with(['customer:id,name'])
            ->when($status->isNotEmpty(), fn($q)=>$q->where('status',$status))
            ->whereBetween('created_at',$between)
            ->orderBy('created_at');

        return response()->streamDownload(function() use ($query){
            $out = fopen('php://output','w');
            fputcsv($out, ['id','cliente','status','data','entrega','total','cupom']);
            $query->chunk(500, function($rows) use ($out){
                foreach($rows as $p){
                    fputcsv($out, [
                        $p->id,
                        optional($p->customer)->name,
                        $p->status,
                        optional($p->created_at)->format('Y-m-d H:i:s'),
                        optional($p->scheduled_delivery_at)->format('Y-m-d H:i:s'),
                        number_format($p->final_amount,2,'.',''),
                        $p->coupon_code ?? '',
                    ]);
                }
            });
            fclose($out);
        }, 'pedidos.csv', ['Content-Type' => 'text/csv']);
    }
}
