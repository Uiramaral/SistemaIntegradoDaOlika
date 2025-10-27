<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cupom;

class CuponsController extends Controller
{
    public function index(Request $req)
    {
        $q = $req->string('q')->toString();
        $status = $req->string('status');
        $periodo = $req->string('periodo');

        $query = Cupom::query();

        if ($q) {
            $query->where(function ($qb) use ($q) {
                $qb->where('codigo', 'like', "%{$q}%")
                   ->orWhere('descricao', 'like', "%{$q}%");
            });
        }

        if ($status->isNotEmpty()) {
            $now = now();
            match ($status->toString()) {
                'ativo'    => $query->where('ativo', true),
                'inativo'  => $query->where('ativo', false),
                'validos'  => $query->where('ativo', true)
                                     ->where(function($q){
                                         $q->whereNull('validade_inicio')->orWhere('validade_inicio','<=', now());
                                     })
                                     ->where(function($q){
                                         $q->whereNull('validade_fim')->orWhere('validade_fim','>=', now());
                                     }),
                'expirados'=> $query->whereNotNull('validade_fim')->where('validade_fim','<', $now),
                'futuros'  => $query->whereNotNull('validade_inicio')->where('validade_inicio','>', $now),
                default    => null,
            };
        }

        if ($periodo->isNotEmpty()) {
            match ($periodo->toString()) {
                'hoje'   => $query->whereDate('updated_at', now()->toDateString()),
                'semana' => $query->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()]),
                'mes'    => $query->whereBetween('updated_at', [now()->startOfMonth(), now()->endOfMonth()]),
                default  => null,
            };
        }

        $cupons = $query->latest()->paginate(20);

        $stats = [
            'total'        => Cupom::count(),
            'ativos'       => Cupom::where('ativo', true)->count(),
            'validos_hoje' => Cupom::where('ativo', true)
                ->where(function($q){ $q->whereNull('validade_inicio')->orWhere('validade_inicio','<=', now()); })
                ->where(function($q){ $q->whereNull('validade_fim')->orWhere('validade_fim','>=', now()); })
                ->count(),
        ];

        return view('cupons.index', compact('cupons','stats'));
    }

    public function create()
    {
        return view('cupons.create');
    }

    public function store(Request $req)
    {
        $data = $this->validateData($req);
        $data['ativo'] = (bool)($data['ativo'] ?? true);
        $data['tipo']  = $data['tipo'] ?? 'percent';
        $cupom = Cupom::create($data);
        return redirect()->route('cupons.show', $cupom)->with('ok','Cupom criado.');
    }

    public function show(Cupom $cupom)
    {
        return view('cupons.show', compact('cupom'));
    }

    public function edit(Cupom $cupom)
    {
        return view('cupons.edit', compact('cupom'));
    }

    public function update(Request $req, Cupom $cupom)
    {
        $data = $this->validateData($req);
        $data['ativo'] = (bool)($data['ativo'] ?? $cupom->ativo);
        $cupom->update($data);
        return redirect()->route('cupons.show', $cupom)->with('ok','Cupom atualizado.');
    }

    public function destroy(Cupom $cupom)
    {
        $cupom->delete();
        return redirect()->route('cupons.index')->with('ok','Cupom removido.');
    }

    private function validateData(Request $req): array
    {
        return $req->validate([
            'codigo'          => ['required','string','max:60'],
            'descricao'       => ['nullable','string','max:180'],
            'tipo'            => ['nullable','in:percent,valor'],
            'valor'           => ['required','numeric','min:0'],
            'minimo_pedido'   => ['nullable','numeric','min:0'],
            'uso_maximo'      => ['nullable','integer','min:1'],
            'validade_inicio' => ['nullable','date'],
            'validade_fim'    => ['nullable','date','after_or_equal:validade_inicio'],
            'ativo'           => ['nullable','boolean'],
        ]);
    }
}
