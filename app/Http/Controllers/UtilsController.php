<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class UtilsController extends Controller
{
    public function cep(Request $req)
    {
        $cep = preg_replace('/\D/', '', (string)$req->get('cep'));
        if (strlen($cep) !== 8) {
            return response()->json(['ok'=>false, 'message'=>'CEP inválido'], 422);
        }

        try {
            $res = Http::timeout(6)->get("https://viacep.com.br/ws/{$cep}/json/");
            if (!$res->ok() || $res->json('erro')) {
                return response()->json(['ok'=>false, 'message'=>'CEP não encontrado']);
            }
            $j = $res->json();
            return response()->json([
                'ok' => true,
                'address' => [
                    'street'   => $j['logradouro'] ?? '',
                    'district' => $j['bairro']     ?? '',
                    'city'     => $j['localidade'] ?? '',
                    'state'    => $j['uf']         ?? '',
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['ok'=>false, 'message'=>'Erro ao consultar CEP'], 500);
        }
    }
}
