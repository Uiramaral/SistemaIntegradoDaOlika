<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FiadoController extends Controller
{
    public function balance(Request $r)
    {
        $id = (int) $r->query('customer_id');
        if(!$id) return response()->json(['saldo'=>0]);

        // Busca saldo em aberto na tabela customer_debts
        $saldo = (float) DB::table('customer_debts')
            ->where('customer_id', $id)
            ->where('status', 'open')
            ->selectRaw("
              COALESCE(SUM(CASE WHEN type='debit'  THEN amount ELSE 0 END),0) -
              COALESCE(SUM(CASE WHEN type='credit' THEN amount ELSE 0 END),0) AS s
            ")
            ->value('s');

        return response()->json(['saldo' => $saldo]);
    }
}
