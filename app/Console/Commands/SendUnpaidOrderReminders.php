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
        $whatsappService = new WhatsAppService();
        if (!$whatsappService->isEnabled()) {
            $this->warn('WhatsApp não configurado.');
            return Command::SUCCESS;
        }

        $cutoff = now()->subMinutes($minutes);
        $orders = Order::query()
            ->where(function($q){ $q->whereNull('payment_status')->orWhere('payment_status','!=','paid'); })
            ->whereNull('notified_unpaid_at')
            ->where('created_at','<=',$cutoff)
            ->with(['customer', 'items.product'])
            ->limit(100)
            ->get();

        foreach ($orders as $order) {
            try {
                if (!$order->customer || !$order->customer->phone) {
                    continue;
                }
                
                $text = $this->buildUnpaidReminder($order);
                $result = $whatsappService->sendText($order->customer->phone, $text);
                
                if (isset($result['success']) && $result['success']) {
                    $order->notified_unpaid_at = now();
                    $order->save();
                    $this->info("Lembrete enviado para pedido #{$order->order_number}");
                } else {
                    Log::warning('Falha ao enviar lembrete de não pagamento', [
                        'order_id' => $order->id,
                        'error' => $result['error'] ?? 'Erro desconhecido'
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('Falha ao enviar lembrete de não pagamento', ['order_id'=>$order->id,'err'=>$e->getMessage()]);
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Construir mensagem de lembrete de pedido não pago
     */
    private function buildUnpaidReminder(Order $order): string
    {
        $lines = [];
        $lines[] = '👋 Olá! Seu pedido #'.$order->order_number.' ainda aguarda pagamento.';
        $final = (float)($order->final_amount ?? $order->total_amount ?? 0);
        $lines[] = 'Total: R$ '.number_format($final,2,',','.');
        $lines[] = '';
        $lines[] = 'Pague agora:';
        $lines[] = url(route('pedido.payment.checkout', $order, false));
        $lines[] = 'PIX: '.url(route('pedido.payment.pix', $order, false));
        return implode("\n", $lines);
    }
}
