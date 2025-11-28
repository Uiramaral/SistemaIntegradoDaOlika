<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Endereco;
use App\Models\Cliente;

class EnderecosController extends Controller
{
    public function index(Request $req)
    {
        $q        = $req->string('q');
        $cidade   = $req->string('cidade');
        $bairro   = $req->string('bairro');
        $cliente  = $req->integer('cliente_id');

        $query = Endereco::query()->with('cliente');

        if ($cliente) { $query->where('cliente_id', $cliente); }
        if ($cidade->isNotEmpty()) { $query->where('cidade','like',"%{$cidade}%"); }
        if ($bairro->isNotEmpty()) { $query->where('bairro','like',"%{$bairro}%"); }
        if ($q->isNotEmpty()) {
            $needle = $q->toString();
            $query->where(function($qb) use ($needle){
                $qb->where('nome_destinatario','like',"%{$needle}%")
                   ->orWhere('endereco','like',"%{$needle}%")
                   ->orWhere('cep','like',"%{$needle}%")
                   ->orWhereHas('cliente', fn($c)=>$c->where('nome','like',"%{$needle}%"));
            });
        }

        $enderecos = $query->latest()->paginate(30);

        $stats = [
            'total' => Endereco::count(),
            'padroes' => Endereco::where('padrao', true)->count(),
            'clientes_com_endereco' => Endereco::distinct('cliente_id')->count('cliente_id'),
        ];

        return view('enderecos.index', compact('enderecos','stats'));
    }

    public function create(Request $req)
    {
        $cliente = null;
        if ($req->has('cliente_id')) { $cliente = Cliente::find($req->integer('cliente_id')); }
        return view('enderecos.create', compact('cliente'));
    }

    public function store(Request $req)
    {
        $data = $this->validated($req);
        $endereco = Endereco::create($data);

        if ($endereco->padrao) {
            Endereco::where('cliente_id',$endereco->cliente_id)
                ->where('id','<>',$endereco->id)
                ->update(['padrao'=>false]);
        }

        return redirect()->route('enderecos.show',$endereco)->with('ok','Endereço criado.');
    }

    public function show(Endereco $endereco)
    {
        $endereco->load('cliente');
        return view('enderecos.show', compact('endereco'));
    }

    public function edit(Endereco $endereco)
    {
        $endereco->load('cliente');
        return view('enderecos.edit', compact('endereco'));
    }

    public function update(Request $req, Endereco $endereco)
    {
        $data = $this->validated($req, $endereco->id);
        $endereco->update($data);

        if ($endereco->padrao) {
            Endereco::where('cliente_id',$endereco->cliente_id)
                ->where('id','<>',$endereco->id)
                ->update(['padrao'=>false]);
        }

        return redirect()->route('enderecos.show',$endereco)->with('ok','Endereço atualizado.');
    }

    public function destroy(Endereco $endereco)
    {
        $endereco->delete();
        return redirect()->route('enderecos.index')->with('ok','Endereço removido.');
    }

    private function validated(Request $req, $id = null): array
    {
        return $req->validate([
            'cliente_id'        => ['required','exists:clientes,id'],
            'nome_destinatario' => ['required','string','max:120'],
            'telefone'          => ['nullable','string','max:30'],
            'endereco'          => ['required','string','max:180'],
            'numero'            => ['nullable','string','max:20'],
            'complemento'       => ['nullable','string','max:120'],
            'bairro'            => ['required','string','max:120'],
            'cidade'            => ['required','string','max:120'],
            'uf'                => ['nullable','string','size:2'],
            'cep'               => ['nullable','string','max:15'],
            'referencia'        => ['nullable','string','max:180'],
            'tax_zone'          => ['nullable','string','max:60'],
            'distancia_km'      => ['nullable','numeric','min:0'],
            'taxa_base'         => ['nullable','numeric','min:0'],
            'padrao'            => ['nullable','boolean'],
        ]);
    }
}
