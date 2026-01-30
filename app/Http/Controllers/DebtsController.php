<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\FinancialTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

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
                'order_id'=>$d->order_id ?? null,
                'amount'=>$d->amount,
                'type'=>'credit',
                'status'=>'settled',
                'description'=>'Baixa de fiado ref. #'.$d->id,
                'created_at'=>now(),'updated_at'=>now(),
            ]);

            // Marcar pedido como pago e registrar receita quando fiado é quitado
            if ($d->order_id) {
                $order = Order::find($d->order_id);
                if ($order) {
                    // Marcar pedido como pago se ainda não estiver pago
                    if ($order->payment_status !== 'paid') {
                        $order->payment_status = 'paid';
                        $order->save();
                        // O Observer vai criar a receita automaticamente quando payment_status muda para 'paid'
                        Log::info('DebtsController (legacy): Pedido marcado como pago ao quitar fiado', [
                            'debt_id' => $d->id,
                            'order_id' => $order->id,
                        ]);
                    } else {
                        // Se já estava pago, verificar se a receita existe e criar se não existir
                        if (Schema::hasTable('financial_transactions')) {
                            // Garantir que temos um client_id válido
                            $clientId = $order->client_id ?? currentClientId();
                            
                            // Validar que client_id é um inteiro válido (não null, não 0, não string vazia)
                            $clientIdValid = false;
                            if ($clientId && is_numeric($clientId)) {
                                $clientIdInt = (int) $clientId;
                                if ($clientIdInt > 0) {
                                    $clientId = $clientIdInt;
                                    $clientIdValid = true;
                                }
                            }
                            
                            if (!$clientIdValid) {
                                Log::warning('DebtsController (legacy): Não foi possível determinar client_id válido para criar receita', [
                                    'debt_id' => $d->id,
                                    'order_id' => $order->id,
                                    'order_client_id' => $order->client_id,
                                    'order_client_id_type' => gettype($order->client_id),
                                    'current_client_id' => currentClientId(),
                                    'current_client_id_type' => gettype(currentClientId()),
                                    'client_id_raw' => $clientId,
                                ]);
                            } else {
                                
                                $alreadyExists = FinancialTransaction::withoutGlobalScopes()
                                    ->where('order_id', $order->id)
                                    ->where('type', 'revenue')
                                    ->exists();
                                
                                if (!$alreadyExists) {
                                    $amount = (float) ($order->final_amount ?? $order->total_amount ?? $d->amount);
                                    
                                    // Verificação final: garantir que client_id é válido antes de criar
                                    if ($amount > 0 && $clientId && is_int($clientId) && $clientId > 0) {
                                        try {
                                            // Criar transação garantindo que client_id seja definido explicitamente
                                            $transaction = new FinancialTransaction();
                                            $transaction->client_id = $clientId;
                                            $transaction->type = 'revenue';
                                            $transaction->amount = $amount;
                                            $transaction->description = 'Pedido ' . ($order->order_number ?? '#' . $order->id) . ' - Pagamento de fiado';
                                            $transaction->transaction_date = now()->format('Y-m-d');
                                            $transaction->category = 'Pedidos';
                                            $transaction->order_id = $order->id;
                                            $transaction->save();
                                            
                                            Log::info('DebtsController (legacy): Receita financeira criada ao quitar fiado (pedido já estava pago)', [
                                                'debt_id' => $d->id,
                                                'order_id' => $order->id,
                                                'amount' => $amount,
                                                'client_id' => $clientId,
                                            ]);
                                        } catch (\Throwable $e) {
                                            Log::error('Erro ao criar receita financeira ao quitar fiado (legacy)', [
                                                'debt_id' => $d->id,
                                                'order_id' => $order->id,
                                                'client_id' => $clientId,
                                                'amount' => $amount,
                                                'error' => $e->getMessage(),
                                                'trace' => $e->getTraceAsString(),
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        });

        return response()->json(['ok'=>true]);
    }
}
