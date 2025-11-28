<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Services\BotConversaService;
use Illuminate\Support\Facades\Log;

class SendUnpaidOrderReminders extends Command
{
    protected $signature = 'orders:remind-unpaid {--minutes=30}';
    protected $description = 'Envia lembretes de WhatsApp para pedidos não pagos há mais de N minutos';

    public function handle()
    {
        $minutes = (int) $this->option('minutes');
        $bot = new BotConversaService();
        if (!$bot->isConfigured()) {
            $this->warn('BotConversa não configurado.');
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
                $text = $bot->buildUnpaidReminder($order->loadMissing('items.product','customer'));
                $ok = $bot->send([
                    'type'=>'order_unpaid_reminder',
                    'order_id'=>$order->id,
                    'order_number'=>$order->order_number,
                    'message'=>$text,
                ]);
                if ($ok) {
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
