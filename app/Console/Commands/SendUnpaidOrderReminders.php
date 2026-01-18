<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class SendUnpaidOrderReminders extends Command
{
    protected $signature = 'orders:remind-unpaid {--minutes=30}';
    protected $description = 'Envia lembretes de WhatsApp para pedidos não pagos há mais de N minutos';

    public function handle()
    {
        $minutes = (int) $this->option('minutes');
        $whatsApp = new WhatsAppService();
        
        if (!$whatsApp->isEnabled()) {
            $this->warn('WhatsApp não configurado.');
            return Command::SUCCESS;
        }

        $cutoff = now()->subMinutes($minutes);
        $orders = Order::query()
            ->where(function($q){ $q->whereNull('payment_status')->orWhere('payment_status','!=','paid'); })
            ->whereNull('notified_unpaid_at')
            ->where('created_at','<=',$cutoff)
            ->limit(100)
            ->get();

        foreach ($orders as $order) {
            try {
                $order->loadMissing('items.product', 'customer');
                
                // Verificar se o cliente tem telefone
                if (!$order->customer || !$order->customer->phone) {
                    continue;
                }
                
                // Construir mensagem de lembrete
                $customerName = $order->customer->name ?? 'Cliente';
                $orderNumber = $order->order_number;
                $total = number_format($order->final_amount ?? $order->total_amount ?? 0, 2, ',', '.');
                
                $text = "⏰ *Lembrete de Pagamento*\n\n";
                $text .= "Olá, {$customerName}!\n\n";
                $text .= "Notamos que o pedido *#{$orderNumber}* ainda não foi pago.\n\n";
                $text .= "*Total: R$ {$total}*\n\n";
                
                if ($order->payment_link) {
                    $text .= "Para finalizar o pagamento, acesse:\n";
                    $text .= $order->payment_link . "\n\n";
                }
                
                $text .= "Qualquer dúvida, estamos à disposição! 😊";
                
                $result = $whatsApp->sendText($order->customer->phone, $text);
                
                if ($result) {
                    $order->notified_unpaid_at = now();
                    $order->save();
                    $this->info("Lembrete enviado para pedido #{$order->order_number}");
                }
            } catch (\Throwable $e) {
                Log::warning('Falha ao enviar lembrete de não pagamento', ['order_id'=>$order->id,'err'=>$e->getMessage()]);
            }
        }

        return Command::SUCCESS;
    }
}
