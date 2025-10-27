<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;

class EntregasController extends Controller
{
    public function index(Request $req)
    {
        $dia      = $req->date('dia', now());
        $janela   = $req->string('janela');
        $status   = $req->string('status');
        $bairro   = $req->string('bairro');
        $q        = $req->string('q');

        $query = Pedido::query()
            ->with(['cliente'])
            ->withCount('itens')
            ->whereDate('data_entrega', $dia->toDateString());

        if ($status->isNotEmpty()) {
            match ($status->toString()) {
                'entrega'   => $query->where('status','entrega'),
                'concluido' => $query->where('status','concluido'),
                'pendente'  => $query->whereIn('status',['agendado','producao']),
                'atrasado'  => $query->whereIn('status',['agendado','producao','entrega'])
                                      ->where('data_entrega','<', now()),
                'rota'      => $query->where('status','entrega')->where('em_rota',true),
                default     => null,
            };
        }

        if ($janela->isNotEmpty()) {
            $query->whereTime('data_entrega', match ($janela->toString()) {
                'manha' => ['>=','06:00:00', '<=','11:59:59'],
                'tarde' => ['>=','12:00:00', '<=','17:59:59'],
                'noite' => ['>=','18:00:00', '<=','22:59:59'],
                default => ['>=','00:00:00','<=','23:59:59']
            });
        }

        if ($bairro->isNotEmpty()) {
            $query->where('bairro','like',"%{$bairro}%");
        }

        if ($q->isNotEmpty()) {
            $needle = $q->toString();
            $query->where(function($qb) use ($needle){
                $qb->where('id', $needle)
                   ->orWhereHas('cliente', fn($c)=>$c->where('nome','like',"%{$needle}%"));
            });
        }

        $entregas = $query->orderBy('data_entrega')->paginate(50);

        $baseDay = fn($statuses)=> Pedido::whereDate('data_entrega', $dia->toDateString())
            ->when($statuses, fn($q,$st)=>$q->whereIn('status',$st))
            ->count();

        $stats = [
            'a_entregar' => $baseDay(['agendado','producao','entrega']),
            'em_rota'    => Pedido::whereDate('data_entrega', $dia->toDateString())->where('status','entrega')->when(true, fn($q)=>$q->where('em_rota',true))->count(),
            'entregues'  => $baseDay(['concluido']),
            'atrasados'  => Pedido::whereDate('data_entrega', $dia->toDateString())
                               ->whereIn('status',["agendado","producao","entrega"]) 
                               ->where('data_entrega','<', now())->count(),
        ];

        return view('entregas.index', compact('entregas','stats','dia'));
    }

    public function show(Pedido $entrega)
    {
        $entrega->load(['cliente','itens.produto']);
        return view('entregas.show', compact('entrega'));
    }

    public function edit(Pedido $entrega)
    {
        return view('entregas.edit', compact('entrega'));
    }

    public function update(Request $req, Pedido $entrega)
    {
        $data = $req->validate([
            'status' => ['required','in:agendado,producao,entrega,concluido,cancelado'],
            'data_entrega' => ['nullable','date'],
            'taxa_entrega' => ['nullable','numeric','min:0'],
            'pagamento_na_entrega' => ['nullable','boolean'],
            'em_rota' => ['nullable','boolean'],
        ]);

        $entrega->update($data);
        return redirect()->route('entregas.show',$entrega)->with('ok','Entrega atualizada.');
    }
}
