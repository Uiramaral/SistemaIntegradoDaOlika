<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Produto;
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
        $pedidosBase = Pedido::query()
            ->when($status->isNotEmpty(), fn($q)=>$q->where('status',$status))
            ->whereBetween('created_at', $between);

        // 2.1 — KPIs
        $kpIs = [
            'qtd_pedidos' => (clone $pedidosBase)->count(),
            'faturamento' => (clone $pedidosBase)->sum('total'),
            'ticket_medio'=> (function() use ($pedidosBase){
                $qtd = (clone $pedidosBase)->count();
                return $qtd ? (clone $pedidosBase)->sum('total') / $qtd : 0;
            })(),
        ];

        // 2.2 — Série diária (faturamento por dia)
        $serieDiaria = (clone $pedidosBase)
            ->select(DB::raw('DATE(created_at) as dia'), DB::raw('SUM(total) as total'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('dia')
            ->get();

        // 2.3 — Top produtos no período
        $topProdutos = PedidoItem::query()
            ->join('pedidos','pedido_items.pedido_id','=','pedidos.id')
            ->join('produtos','pedido_items.produto_id','=','produtos.id')
            ->whereBetween('pedidos.created_at', $between)
            ->when($status->isNotEmpty(), fn($q)=>$q->where('pedidos.status',$status))
            ->select('produtos.id','produtos.nome',
                DB::raw('SUM(pedido_items.quantidade) as qtd'),
                DB::raw('SUM(pedido_items.subtotal) as receita'))
            ->groupBy('produtos.id','produtos.nome')
            ->orderByDesc('receita')
            ->limit(10)
            ->get();

        // 2.4 — Cupons usados
        $cuponsUsados = (clone $pedidosBase)
            ->whereNotNull('cupom_codigo')
            ->select('cupom_codigo', DB::raw('COUNT(*) as qtd'), DB::raw('SUM(total) as receita'))
            ->groupBy('cupom_codigo')
            ->orderByDesc('qtd')
            ->get();

        // 2.5 — Geografia (bairro) - ajustado para buscar do cliente
        $porBairro = (clone $pedidosBase)
            ->join('clientes','pedidos.cliente_id','=','clientes.id')
            ->select('clientes.bairro', DB::raw('COUNT(*) as qtd'), DB::raw('SUM(pedidos.total) as receita'))
            ->groupBy('clientes.bairro')
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

        $query = Pedido::query()
            ->with(['cliente:id,nome'])
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
                        optional($p->cliente)->nome,
                        $p->status,
                        optional($p->created_at)->format('Y-m-d H:i:s'),
                        optional($p->data_entrega)->format('Y-m-d H:i:s'),
                        number_format($p->total,2,'.',''),
                        $p->cupom_codigo ?? '',
                    ]);
                }
            });
            fclose($out);
        }, 'pedidos.csv', ['Content-Type' => 'text/csv']);
    }
}
