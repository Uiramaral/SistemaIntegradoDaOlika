<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class WhatsAppFailedMessagesController extends Controller
{
    public function index()
    {
        // Se a tabela não existir (instâncias antigas), não quebrar a página
        if (!Schema::hasTable('whatsapp_failed_messages')) {
            $failedMessages = collect(); // lista vazia
            $missingTable = true;
            return view('dashboard.whatsapp-failed-messages.index', compact('failedMessages', 'missingTable'));
        }

        $failedMessages = DB::table('whatsapp_failed_messages')
            ->leftJoin('orders', 'whatsapp_failed_messages.order_id', '=', 'orders.id')
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->select(
                'whatsapp_failed_messages.*',
                'orders.order_number',
                'orders.id as order_id',
                'customers.name as customer_name'
            )
            ->where('whatsapp_failed_messages.status', 'pending')
            ->orderBy('whatsapp_failed_messages.created_at', 'desc')
            ->paginate(20);

        $missingTable = false;

        return view('dashboard.whatsapp-failed-messages.index', compact('failedMessages', 'missingTable'));
    }

    public function retry($id)
    {
        $failedMessage = DB::table('whatsapp_failed_messages')->find($id);
        
        if (!$failedMessage) {
            return response()->json([
                'success' => false,
                'error' => 'Mensagem não encontrada'
            ], 404);
        }

        if ($failedMessage->status !== 'pending') {
            return response()->json([
                'success' => false,
                'error' => 'Esta mensagem já foi processada'
            ], 400);
        }

        try {
            // Atualizar status para retrying
            DB::table('whatsapp_failed_messages')
                ->where('id', $id)
                ->update([
                    'status' => 'retrying',
                    'attempt_count' => $failedMessage->attempt_count + 1,
                    'last_attempt_at' => now(),
                    'updated_at' => now()
                ]);

            // Tentar reenviar
            $wa = new WhatsAppService();
            $result = $wa->sendText($failedMessage->recipient_phone, $failedMessage->message);

            if (isset($result['success']) && $result['success']) {
                // Sucesso - marcar como enviada
                DB::table('whatsapp_failed_messages')
                    ->where('id', $id)
                    ->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                        'updated_at' => now()
                    ]);

                Log::info('WhatsAppFailedMessagesController: Mensagem reenviada com sucesso', [
                    'failed_message_id' => $id,
                    'order_id' => $failedMessage->order_id,
                    'phone' => $failedMessage->recipient_phone
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Mensagem reenviada com sucesso!'
                ]);
            } else {
                // Falha novamente - voltar para pending
                DB::table('whatsapp_failed_messages')
                    ->where('id', $id)
                    ->update([
                        'status' => 'pending',
                        'error_message' => $result['error'] ?? 'Erro desconhecido',
                        'updated_at' => now()
                    ]);

                Log::warning('WhatsAppFailedMessagesController: Falha ao reenviar mensagem', [
                    'failed_message_id' => $id,
                    'order_id' => $failedMessage->order_id,
                    'phone' => $failedMessage->recipient_phone,
                    'error' => $result['error'] ?? 'Erro desconhecido'
                ]);

                return response()->json([
                    'success' => false,
                    'error' => $result['error'] ?? 'Erro ao reenviar mensagem'
                ], 500);
            }
        } catch (\Exception $e) {
            // Erro na tentativa - voltar para pending
            DB::table('whatsapp_failed_messages')
                ->where('id', $id)
                ->update([
                    'status' => 'pending',
                    'error_message' => $e->getMessage(),
                    'updated_at' => now()
                ]);

            Log::error('WhatsAppFailedMessagesController: Exceção ao reenviar mensagem', [
                'failed_message_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao processar reenvio: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPendingCount()
    {
        $count = DB::table('whatsapp_failed_messages')
            ->where('status', 'pending')
            ->count();

        return response()->json(['count' => $count]);
    }
}

