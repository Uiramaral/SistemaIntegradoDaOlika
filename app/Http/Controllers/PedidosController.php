<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Cupom;
use Illuminate\Support\Facades\DB;

class PedidosController extends Controller
{
    public function index(Request $req)
    {
        $periodo = $req->string('periodo','hoje');
        $status  = $req->string('status');
        $q       = $req->string('q');

        $query = Pedido::query()->with(['cliente'])->withCount('itens');

        match ($periodo->toString()) {
            'semana' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'mes'    => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]),
            'all'    => null,
            default  => $query->whereDate('created_at', now()->toDateString()),
        };

        if ($status->isNotEmpty()) { $query->where('status', $status); }

        if ($q->isNotEmpty()) {
            $term = $q->toString();
            $query->where(function($qb) use ($term) {
                $qb->where('id',$term)
                   ->orWhereHas('cliente', fn($c)=>$c->where('nome','like',"%{$term}%"));
            });
        }

        $pedidos = $query->latest()->paginate(20);

        $stats = [
            'total_hoje' => Pedido::whereDate('created_at', now()->toDateString())->count(),
            'em_producao' => Pedido::where('status','producao')->count(),
            'em_entrega' => Pedido::where('status','entrega')->count(),
            'concluidos' => Pedido::where('status','concluido')->count(),
        ];

        return view('pedidos.index', compact('pedidos','stats'));
    }

    public function create()
    {
        $clientes = Cliente::orderBy('nome')->get(['id','nome']);
        $produtos = Produto::where('ativo',true)->orderBy('nome')->get(['id','nome','preco']);
        return view('pedidos.create', compact('clientes','produtos'));
    }

    public function store(Request $req)
    {
        $data = $this->validatePedido($req);

        DB::transaction(function() use ($data, &$pedido) {
            $pedido = Pedido::create([
                'cliente_id'   => $data['cliente_id'],
                'status'       => $data['status'] ?? 'agendado',
                'data_entrega' => $data['data_entrega'] ?? null,
                'taxa_entrega' => $data['taxa_entrega'] ?? 0,
                'observacoes'  => $data['observacoes'] ?? null,
                'total'        => 0,
            ]);

            $totalItens = 0;
            foreach(($data['itens'] ?? []) as $item){
                if(empty($item['produto_id']) || empty($item['quantidade'])) continue;
                $preco = $item['preco_unit'] ?? Produto::find($item['produto_id'])->preco ?? 0;
                $sub = $preco * (int)$item['quantidade'];
                PedidoItem::create([
                    'pedido_id'   => $pedido->id,
                    'produto_id'  => $item['produto_id'],
                    'quantidade'  => (int)$item['quantidade'],
                    'preco_unit'  => $preco,
                    'subtotal'    => $sub,
                ]);
                $totalItens += $sub;
            }

            // Calcular desconto e cupom
            $taxa = $data['taxa_entrega'] ?? 0;
            $desc = $data['desconto'] ?? 0;
            $cupomV = 0;

            if (!empty($data['cupom_codigo'])) {
                $cupom = Cupom::where('codigo',$data['cupom_codigo'])->first();
                if ($cupom && $cupom->ativo) {
                    $now = now();
                    $inicioOK = !$cupom->validade_inicio || $cupom->validade_inicio <= $now;
                    $fimOK    = !$cupom->validade_fim || $cupom->validade_fim >= $now;
                    if ($inicioOK && $fimOK && (!$cupom->minimo_pedido || $totalItens >= $cupom->minimo_pedido)) {
                        $cupomV = $cupom->tipo === 'percent' ? ($totalItens * ($cupom->valor/100)) : $cupom->valor;
                    }
                }
            }

            $pedido->update(['total' => max(0, $totalItens + $taxa - $desc - $cupomV)]);
        });

        return redirect()->route('pedidos.show',$pedido)->with('ok','Pedido criado.');
    }

    public function show(Pedido $pedido)
    {
        $pedido->load(['cliente','itens.produto']);

        $timeline = [
            ['label' => 'Criado', 'at' => $pedido->created_at],
            ['label' => 'Produção', 'at' => $pedido->status==='producao' || $pedido->status==='entrega' || $pedido->status==='concluido' ? $pedido->updated_at : null],
            ['label' => 'Em entrega', 'at' => $pedido->status==='entrega' || $pedido->status==='concluido' ? $pedido->updated_at : null],
            ['label' => 'Concluído', 'at' => $pedido->status==='concluido' ? $pedido->updated_at : null],
        ];

        return view('pedidos.show', compact('pedido','timeline'));
    }

    public function edit(Pedido $pedido)
    {
        $pedido->load(['itens','itens.produto']);
        $clientes = Cliente::orderBy('nome')->get(['id','nome']);
        $produtos = Produto::where('ativo',true)->orderBy('nome')->get(['id','nome','preco']);
        return view('pedidos.edit', compact('pedido','clientes','produtos'));
    }

    public function update(Request $req, Pedido $pedido)
    {
        $data = $this->validatePedido($req, $pedido->id);

        DB::transaction(function() use ($data, $pedido) {
            $pedido->update([
                'cliente_id'   => $data['cliente_id'],
                'status'       => $data['status'],
                'data_entrega' => $data['data_entrega'] ?? null,
                'taxa_entrega' => $data['taxa_entrega'] ?? 0,
                'observacoes'  => $data['observacoes'] ?? null,
            ]);

            if(isset($data['itens'])){
                $pedido->itens()->delete();
                $totalItens = 0;
                foreach($data['itens'] as $item){
                    if(empty($item['produto_id']) || empty($item['quantidade'])) continue;
                    $preco = $item['preco_unit'] ?? Produto::find($item['produto_id'])->preco ?? 0;
                    $sub = $preco * (int)$item['quantidade'];
                    PedidoItem::create([
                        'pedido_id'   => $pedido->id,
                        'produto_id'  => $item['produto_id'],
                        'quantidade'  => (int)$item['quantidade'],
                        'preco_unit'  => $preco,
                        'subtotal'    => $sub,
                    ]);
                    $totalItens += $sub;
                }

                // Calcular desconto e cupom
                $taxa = $data['taxa_entrega'] ?? 0;
                $desc = $data['desconto'] ?? 0;
                $cupomV = 0;

                if (!empty($data['cupom_codigo'])) {
                    $cupom = Cupom::where('codigo',$data['cupom_codigo'])->first();
                    if ($cupom && $cupom->ativo) {
                        $now = now();
                        $inicioOK = !$cupom->validade_inicio || $cupom->validade_inicio <= $now;
                        $fimOK    = !$cupom->validade_fim || $cupom->validade_fim >= $now;
                        if ($inicioOK && $fimOK && (!$cupom->minimo_pedido || $totalItens >= $cupom->minimo_pedido)) {
                            $cupomV = $cupom->tipo === 'percent' ? ($totalItens * ($cupom->valor/100)) : $cupom->valor;
                        }
                    }
                }

                $pedido->update(['total' => max(0, $totalItens + $taxa - $desc - $cupomV)]);
            }
        });

        return redirect()->route('pedidos.show',$pedido)->with('ok','Pedido atualizado.');
    }

    public function destroy(Pedido $pedido)
    {
        $pedido->delete();
        return redirect()->route('pedidos.index')->with('ok','Pedido removido.');
    }

    private function validatePedido(Request $req, $id = null): array
    {
        return $req->validate([
            'cliente_id'   => ['required','exists:clientes,id'],
            'status'       => ['required','in:agendado,producao,entrega,concluido,cancelado'],
            'data_entrega' => ['nullable','date'],
            'taxa_entrega' => ['nullable','numeric','min:0'],
            'desconto'     => ['nullable','numeric','min:0'],
            'cupom_codigo' => ['nullable','string','max:60'],
            'observacoes'  => ['nullable','string','max:2000'],
            'itens'        => ['nullable','array'],
            'itens.*.produto_id' => ['nullable','exists:produtos,id'],
            'itens.*.quantidade' => ['nullable','integer','min:1'],
            'itens.*.preco_unit' => ['nullable','numeric','min:0'],
        ]);
    }
}
