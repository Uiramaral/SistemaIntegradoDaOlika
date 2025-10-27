<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderStatusService
{
    public function changeStatus(Order $order, string $newCode, ?string $note = null, ?int $userId = null): void
    {
        $old = $order->status;
        if ($old === $newCode) {
            return;
        }

        // Atualiza pedido
        $order->status = $newCode;
        $order->save();

        // Histórico
        DB::table('order_status_history')->insert([
            'order_id'   => $order->id,
            'old_status' => $old,
            'new_status' => $newCode,
            'note'       => $note,
            'user_id'    => $userId,
            'created_at' => now(),
        ]);

        // Regras do status (notificações)
        $st = DB::table('order_statuses')->where('code', $newCode)->where('active', 1)->first();
        
        if (!$st) {
            return;
        }

        // Monta mensagem (template ou fallback)
        $tplText = null;
        if ($st->whatsapp_template_id) {
            $tpl = DB::table('whatsapp_templates')
                ->where('id', $st->whatsapp_template_id)
                ->where('active', 1)
                ->first();
            
            if ($tpl) {
                $tplText = $tpl->content;
            }
        }
        
        if (!$tplText) {
            // fallback simples
            $tplText = "Status do pedido *#{$order->order_number}*: {$st->name}.";
        }

        // Vars padrão
        $vars = [
            'nome'   => optional($order->customer)->name ?? 'Cliente',
            'pedido' => (string) $order->order_number,
            'valor'  => number_format($order->final_amount ?? $order->total_amount ?? 0, 2, ',', '.'),
        ];

        // Dispara WhatsApp se marcado
        try {
            $wa = new WhatsAppService();
            
            // Cliente
            if ($st->notify_customer && $order->customer && $order->customer->phone) {
                $wa->sendTemplate($order->customer->phone, $tplText, $vars);
            }

            // Admin (opcional: defina número no whatsapp_settings.sender_name ou em settings)
            if ($st->notify_admin) {
                // Substitua por um número de admin real (ou busque de settings)
                $admin = env('WHATSAPP_ADMIN_NUMBER', '55719987654321');
                
                if ($admin) {
                    $msgAdmin = "🔔 Pedido #{$order->order_number} mudou de status: {$st->name}";
                    $wa->sendText($admin, $msgAdmin);
                }
            }
        } catch (\Throwable $e) {
            Log::error('WhatsApp status notify error: ' . $e->getMessage());
        }
    }
}

