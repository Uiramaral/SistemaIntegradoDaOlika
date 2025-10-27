<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use Illuminate\Support\Facades\DB;

class PedidosBulkController extends Controller
{
    public function update(Request $req)
    {
        $data = $req->validate([
            'acao'         => ['required','in:status,data,status_e_data'],
            'status'       => ['nullable','in:agendado,producao,entrega,concluido,cancelado'],
            'data_entrega' => ['nullable','date'],
        ]);

        $ids = $req->input('ids');
        if (is_string($ids)) { $ids = json_decode($ids, true) ?: []; }
        
        if (empty($ids)) {
            return back()->with('error','Nenhum pedido selecionado.');
        }
        
        $acao  = $data['acao'];
        $attrs = [];

        if (in_array($acao, ['status','status_e_data']) && !empty($data['status'])) {
            $attrs['status'] = $data['status'];
        }
        if (in_array($acao, ['data','status_e_data']) && !empty($data['data_entrega'])) {
            $attrs['data_entrega'] = $data['data_entrega'];
        }
        if (empty($attrs)) {
            return back()->with('error','Nenhuma mudança selecionada.');
        }

        // Validação de que todos os IDs existem
        $exists = Pedido::whereIn('id', $ids)->count();
        if ($exists !== count($ids)) {
            return back()->with('error','Alguns pedidos não foram encontrados.');
        }
        
        DB::transaction(function() use ($ids, $attrs) {
            Pedido::whereIn('id', $ids)->update($attrs);
        });

        return back()->with('ok', 'Pedidos atualizados: '.count($ids));
    }
}
