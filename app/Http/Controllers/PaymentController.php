<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\AppSettings;
use App\Services\MercadoPagoApi;

class PaymentController extends Controller
{
    public function createPix(Request $r)
    {
        $order = Order::findOrFail(session('order_id'));

        $order->payment_method = 'pix';
        $order->save();

        $mp = new MercadoPagoApi();

        $payload = [
            "transaction_amount" => (float)$order->total_amount,
            "description" => "Pedido #{$order->order_number}",
            "payment_method_id" => "pix",
            "notification_url"  => AppSettings::get('mercadopago_webhook_url', route('webhook.mercadopago')),
            "payer" => [
                "email" => optional($order->customer)->email ?: "noemail@dummy.com",
                "first_name" => optional($order->customer)->name ?: "Cliente"
            ],
            "metadata" => ["order_id" => $order->id, "order_number" => $order->order_number],
        ];

        $res = $mp->createPix($payload);

        $qrBase64   = data_get($res, 'point_of_interaction.transaction_data.qr_code_base64');
        $copiaCola  = data_get($res, 'point_of_interaction.transaction_data.qr_code');
        $paymentId  = (string) data_get($res, 'id');
        $status     = (string) data_get($res, 'status');
        $expiresAt  = data_get($res, 'date_of_expiration');

        $order->payment_id        = $paymentId;
        $order->payment_status    = $status;
        $order->pix_qr_base64     = $qrBase64;
        $order->pix_copy_paste    = $copiaCola;
        $order->pix_expires_at     = $expiresAt;
        $order->payment_raw_response = json_encode($res);
        $order->save();

        return response()->json([
            'ok' => true,
            'qr_base64' => $qrBase64,
            'copia_cola' => $copiaCola
        ]);
    }

    public function createMpPreference(Request $r)
    {
        $order = Order::findOrFail(session('order_id'));

        $order->payment_method = 'mercadopago';
        $order->save();

        $items = $order->items->map(function($i) {
            return [
                "title" => $i->product_name,
                "quantity" => (int)$i->qty,
                "currency_id" => "BRL",
                "unit_price" => (float)$i->price,
            ];
        })->values()->all();

        $mp = new MercadoPagoApi();

        $res = $mp->createPreference([
            "items" => $items,
            "metadata" => ["order_id" => $order->id, "order_number" => $order->order_number],
            "notification_url" => AppSettings::get('mercadopago_webhook_url', route('webhook.mercadopago')),
            "back_urls" => [
                "success" => route('checkout.success', $order),
                "pending" => route('checkout.success', $order),
                "failure" => route('checkout.success', $order),
            ],
            "auto_return" => "approved",
        ]);

        $order->preference_id = data_get($res, 'id');
        $order->payment_link  = data_get($res, 'init_point');
        $order->payment_raw_response = json_encode($res);
        $order->save();

        return response()->json(['ok' => true, 'init_point' => $order->payment_link]);
    }
}
