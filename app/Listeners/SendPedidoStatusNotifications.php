<?php

namespace App\Listeners;

use App\Events\PedidoStatusChanged;
use App\Mail\PedidoStatusMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendPedidoStatusNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(PedidoStatusChanged $event): void
    {
        $p = $event->pedido->loadMissing('cliente');
        $cliente = $p->cliente;

        if (!$cliente) return;

        // EMAIL
        if (config('notifications.email_enabled') && !empty($cliente->email)) {
            Mail::to($cliente->email)->queue(new PedidoStatusMail($p, $event->oldStatus, $event->newStatus));
        }

        // WHATSAPP via webhook simples
        if (config('notifications.wa_enabled') && !empty($cliente->telefone)) {
            $url = config('notifications.wa_webhook_url');
            if ($url) {
                $payload = [
                    'to' => $cliente->telefone,
                    'sender' => config('notifications.wa_sender'),
                    'message' => view('notifications.whatsapp.status', [
                        'pedido' => $p,
                        'old' => $event->oldStatus,
                        'new' => $event->newStatus,
                    ])->render(),
                ];
                $req = Http::withHeaders([
                    'Authorization' => 'Bearer '.config('notifications.wa_token'),
                    'Content-Type'  => 'application/json',
                ])->post($url, $payload);
                
                if ($req->failed()) { 
                    Log::warning('WA notify failed', ['pedido'=>$p->id, 'resp'=>$req->body()]); 
                }
            }
        }
    }
}
