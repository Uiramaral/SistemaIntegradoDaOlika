<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerSearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->query('q', ''));
        if ($q === '') return response()->json([]);

        $rows = Customer::query()
            ->where(function($w) use ($q){
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('phone', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            })
            ->limit(10)
            ->get(['id','name','phone','email']);

        // Buscar endereço principal de cada cliente
        $out = $rows->map(function($c){
            $endereco = null;
            $addr = \DB::table('addresses')
                ->where('customer_id', $c->id)
                ->where('is_primary', 1)
                ->first();
            
            if($addr) {
                $endereco = [
                    'rua' => $addr->street,
                    'numero' => $addr->number,
                    'cep' => $addr->zip_code,
                    'complemento' => $addr->complement,
                    'bairro' => $addr->neighborhood,
                    'cidade' => $addr->city,
                    'uf' => $addr->state,
                ];
            }

            return [
                'id'       => $c->id,
                'nome'     => $c->name,
                'telefone' => $c->phone,
                'email'    => $c->email,
                'endereco' => $endereco,
            ];
        });

        return response()->json($out);
    }
}
