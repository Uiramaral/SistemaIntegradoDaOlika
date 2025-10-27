<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Consignacao;
use App\Models\ConsignacaoItem;
use App\Models\Cliente;
use App\Models\Produto;

class ConsignacoesController extends Controller
{
    public function index(Request $req)
    {
        $status  = $req->string('status');
        $periodo = $req->string('periodo','mes');
        $q       = $req->string('q');

        $query = Consignacao::query()->with('parceiro')->withCount('itens');

        match ($periodo->toString()) {
            'hoje'   => $query->whereDate('data_envio', now()->toDateString()),
            'semana' => $query->whereBetween('data_envio', [now()->startOfWeek(), now()->endOfWeek()]),
            'mes'    => $query->whereBetween('data_envio', [now()->startOfMonth(), now()->endOfMonth()]),
            default  => null,
        };

        if ($status->isNotEmpty()) { $query->where('status',$status); }

        if ($q->isNotEmpty()) {
            $term = $q->toString();
            $query->where(function($qb) use ($term){
                $qb->where('id',$term)
                   ->orWhereHas('parceiro', fn($c)=>$c->where('nome','like',"%{$term}%"));
            });
        }

        $consignacoes = $query->latest('data_envio')->paginate(20);

        $stats = [
            'abertas'    => Consignacao::where('status','aberta')->count(),
            'liquidadas' => Consignacao::where('status','liquidada')->count(),
            'canceladas' => Consignacao::where('status','cancelada')->count(),
        ];

        return view('consignacoes.index', compact('consignacoes','stats'));
    }

    public function create()
    {
        $parceiros = Cliente::orderBy('nome')->get(['id','nome']);
        $produtos  = Produto::where('ativo',true)->orderBy('nome')->get(['id','nome','preco']);
        return view('consignacoes.create', compact('parceiros','produtos'));
    }

    public function store(Request $req)
    {
        $data = $this->validated($req);

        DB::transaction(function() use ($data, &$c) {
            $c = Consignacao::create([
                'parceiro_id'     => $data['parceiro_id'],
                'status'          => 'aberta',
                'data_envio'      => $data['data_envio'] ?? now()->toDateString(),
                'data_retorno'    => $data['data_retorno'] ?? null,
                'comissao_percent'=> $data['comissao_percent'] ?? 0,
                'observacoes'     => $data['observacoes'] ?? null,
                'total_enviado'   => 0,
                'total_vendido'   => 0,
                'total_devolvido' => 0,
                'valor_comissao'  => 0,
                'valor_liquido'   => 0,
            ]);

            $totEnv = 0; $totVend = 0; $totDev = 0;
            foreach(($data['itens'] ?? []) as $item){
                if(empty($item['produto_id']) || empty($item['qtd_enviada'])) continue;
                $preco = $item['preco_unit'] ?? Produto::find($item['produto_id'])->preco ?? 0;
                $qEnv  = (int)$item['qtd_enviada'];
                $qVend = (int)($item['qtd_vendida'] ?? 0);
                $qDev  = (int)($item['qtd_devolvida'] ?? 0);
                $subEnv = $preco * $qEnv;
                $subVend= $preco * $qVend;
                ConsignacaoItem::create([
                    'consignacao_id'  => $c->id,
                    'produto_id'      => $item['produto_id'],
                    'qtd_enviada'     => $qEnv,
                    'qtd_vendida'     => $qVend,
                    'qtd_devolvida'   => $qDev,
                    'preco_unit'      => $preco,
                    'subtotal_enviado'=> $subEnv,
                    'subtotal_vendido'=> $subVend,
                ]);
                $totEnv += $subEnv; $totVend += $subVend; $totDev += ($preco * $qDev);
            }
            $comissao = ($data['comissao_percent'] ?? 0) * $totVend / 100;
            $liquido  = max(0, $totVend - $comissao);

            $c->update([
                'total_enviado'   => $totEnv,
                'total_vendido'   => $totVend,
                'total_devolvido' => $totDev,
                'valor_comissao'  => $comissao,
                'valor_liquido'   => $liquido,
            ]);
        });

        return redirect()->route('consignacoes.show',$c)->with('ok','Consignação criada.');
    }

    public function show(Consignacao $consignaco)
    {
        $consignaco->load(['parceiro','itens.produto']);
        return view('consignacoes.show', ['c' => $consignaco]);
    }

    public function edit(Consignacao $consignaco)
    {
        $consignaco->load(['itens']);
        $parceiros = Cliente::orderBy('nome')->get(['id','nome']);
        $produtos  = Produto::where('ativo',true)->orderBy('nome')->get(['id','nome','preco']);
        return view('consignacoes.edit', ['c'=>$consignaco,'parceiros'=>$parceiros,'produtos'=>$produtos]);
    }

    public function update(Request $req, Consignacao $consignaco)
    {
        $data = $this->validated($req, $consignaco->id);

        DB::transaction(function() use ($data, $consignaco){
            $consignaco->update([
                'parceiro_id'     => $data['parceiro_id'],
                'status'          => $data['status'] ?? $consignaco->status,
                'data_envio'      => $data['data_envio'] ?? $consignaco->data_envio,
                'data_retorno'    => $data['data_retorno'] ?? $consignaco->data_retorno,
                'comissao_percent'=> $data['comissao_percent'] ?? $consignaco->comissao_percent,
                'observacoes'     => $data['observacoes'] ?? $consignaco->observacoes,
            ]);

            if(isset($data['itens'])){
                $consignaco->itens()->delete();
                $totEnv = 0; $totVend = 0; $totDev = 0;
                foreach($data['itens'] as $item){
                    if(empty($item['produto_id']) || empty($item['qtd_enviada'])) continue;
                    $preco = $item['preco_unit'] ?? Produto::find($item['produto_id'])->preco ?? 0;
                    $qEnv  = (int)$item['qtd_enviada'];
                    $qVend = (int)($item['qtd_vendida'] ?? 0);
                    $qDev  = (int)($item['qtd_devolvida'] ?? 0);
                    $subEnv = $preco * $qEnv;
                    $subVend= $preco * $qVend;
                    ConsignacaoItem::create([
                        'consignacao_id'  => $consignaco->id,
                        'produto_id'      => $item['produto_id'],
                        'qtd_enviada'     => $qEnv,
                        'qtd_vendida'     => $qVend,
                        'qtd_devolvida'   => $qDev,
                        'preco_unit'      => $preco,
                        'subtotal_enviado'=> $subEnv,
                        'subtotal_vendido'=> $subVend,
                    ]);
                    $totEnv += $subEnv; $totVend += $subVend; $totDev += ($preco * $qDev);
                }
                $comissao = ($data['comissao_percent'] ?? $consignaco->comissao_percent) * $totVend / 100;
                $liquido  = max(0, $totVend - $comissao);
                $consignaco->update([
                    'total_enviado'   => $totEnv,
                    'total_vendido'   => $totVend,
                    'total_devolvido' => $totDev,
                    'valor_comissao'  => $comissao,
                    'valor_liquido'   => $liquido,
                ]);
            }
        });

        return redirect()->route('consignacoes.show',$consignaco)->with('ok','Consignação atualizada.');
    }

    public function destroy(Consignacao $consignaco)
    {
        $consignaco->delete();
        return redirect()->route('consignacoes.index')->with('ok','Consignação removida.');
    }

    private function validated(Request $req, $id=null): array
    {
        return $req->validate([
            'parceiro_id'     => ['required','exists:clientes,id'],
            'status'          => ['nullable','in:aberta,liquidada,cancelada'],
            'data_envio'      => ['nullable','date'],
            'data_retorno'    => ['nullable','date','after_or_equal:data_envio'],
            'comissao_percent'=> ['nullable','numeric','min:0','max:100'],
            'observacoes'     => ['nullable','string','max:2000'],
            'itens'           => ['nullable','array'],
            'itens.*.produto_id'    => ['nullable','exists:produtos,id'],
            'itens.*.qtd_enviada'   => ['nullable','integer','min:1'],
            'itens.*.qtd_vendida'   => ['nullable','integer','min:0'],
            'itens.*.qtd_devolvida' => ['nullable','integer','min:0'],
            'itens.*.preco_unit'    => ['nullable','numeric','min:0'],
        ]);
    }
}
