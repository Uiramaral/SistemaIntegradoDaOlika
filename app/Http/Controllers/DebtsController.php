<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DebtsController extends Controller
{
    public function index($customerId)
    {
        $debts = DB::table('customer_debts')
            ->where('customer_id',$customerId)
            ->orderByDesc('created_at')->get();

        $customer = DB::table('customers')->find($customerId);

        // saldo total histórico
        $saldo = (float) DB::table('customer_debts')
            ->where('customer_id',$customerId)
            ->selectRaw("SUM(CASE WHEN type='debit' THEN amount ELSE 0 END) - SUM(CASE WHEN type='credit' THEN amount ELSE 0 END) as s")
            ->value('s');

        // saldo EM ABERTO (apenas lançamentos status='open')
        $saldoAberto = (float) DB::table('customer_debts')
            ->where('customer_id',$customerId)
            ->where('status','open')
            ->selectRaw("
              COALESCE(SUM(CASE WHEN type='debit'  THEN amount ELSE 0 END),0) -
              COALESCE(SUM(CASE WHEN type='credit' THEN amount ELSE 0 END),0) AS s
            ")
            ->value('s');

        return view('dashboard/customers/fiados', compact('debts','customer','saldo','saldoAberto'));
    }

    public function balance(Request $r)
    {
        $customerId = (int) $r->get('customer_id');
        if(!$customerId) return response()->json(['ok'=>false,'message'=>'customer_id obrigatório'], 422);

        // Saldo em aberto = (débitos abertos) - (créditos abertos)
        $saldo = (float) DB::table('customer_debts')
            ->where('customer_id', $customerId)
            ->where('status', 'open')
            ->selectRaw("
              COALESCE(SUM(CASE WHEN type='debit'  THEN amount ELSE 0 END),0) -
              COALESCE(SUM(CASE WHEN type='credit' THEN amount ELSE 0 END),0) AS s
            ")
            ->value('s');

        return response()->json(['ok'=>true,'saldo'=>$saldo]);
    }

    public function settle($debtId, Request $r)
    {
        $d = DB::table('customer_debts')->find($debtId);
        if(!$d || $d->status !== 'open' || $d->type !== 'debit'){
            return response()->json(['ok'=>false,'message'=>'Lançamento inválido'], 422);
        }

        DB::transaction(function() use ($d){
            DB::table('customer_debts')->where('id',$d->id)->update([
                'status'=>'settled','updated_at'=>now()
            ]);

            DB::table('customer_debts')->insert([
                'customer_id'=>$d->customer_id,
                'order_id'=>null,
                'amount'=>$d->amount,
                'type'=>'credit',
                'status'=>'settled',
                'description'=>'Baixa de fiado ref. #'.$d->id,
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        });

        return response()->json(['ok'=>true]);
    }
}
