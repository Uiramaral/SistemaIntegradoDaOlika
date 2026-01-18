<?php

namespace App\Http\Controllers;

use App\Services\MercadoPagoApiService;
use App\Services\WhatsAppService;
use App\Services\MercadoPagoApi;
use App\Services\OrderStatusService;
use App\Models\Order;
use App\Models\CouponUsage;
use App\Models\WebhookLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected $mercadoPagoService;
    protected $whatsAppService;

    public function __construct(MercadoPagoApiService $mercadoPagoService, WhatsAppService $whatsAppService)
    {
        $this->mercadoPagoService = $mercadoPagoService;
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Webhook do MercadoPago
     */
    public function mercadoPago(Request $request)
    {
        $requestId = $request->header('x-request-id');
        $signature = $request->header('x-signature');
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $eventType = $request->get('type') ?? $request->get('action') ?? 'unknown';
        $payload = $request->all();
        
        $isValidSignature = $this->isValidMercadoPagoSignature($request);
        
        // Registrar log do webhook
        $webhookLog = WebhookLog::create([
            'provider' => 'mercadopago',
            'event_type' => $eventType,
            'status' => 'pending',
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'request_id' => $requestId,
            'signature_valid' => $isValidSignature,
            'payload' => $payload,
        ]);
        
        if (!$isValidSignature) {
            $webhookLog->update([
                'status' => 'rejected',
                'error_message' => 'Assinatura inválida',
            ]);
            
            Log::warning('Mercado Pago Webhook rejeitado - assinatura inválida', [
                'webhook_log_id' => $webhookLog->id,
                'request_id' => $requestId,
                'signature' => $signature,
                'ip' => $ip,
                'user_agent' => $userAgent,
            ]);
            
            return response()->json(['status' => 'error', 'message' => 'Assinatura inválida'], 401);
        }

        Log::info('Mercado Pago Webhook Received', [
            'webhook_log_id' => $webhookLog->id,
            'type' => $eventType,
            'action' => $request->get('action'),
            'data_id' => $request->get('data')['id'] ?? null,
            'request_id' => $requestId,
            'ip' => $ip,
        ]);
        
        try {
            // Usar o novo service para processar webhook
            $result = $this->mercadoPagoService->processWebhook($payload);
            
            if ($result['success']) {
                $webhookLog->update([
                    'status' => 'success',
                    'response' => $result,
                    'processed_at' => now(),
                ]);
                
                Log::info('Webhook processado com sucesso', [
                    'webhook_log_id' => $webhookLog->id,
                    'order_id' => $result['order_id'] ?? null,
                    'payment_status' => $result['payment_status'] ?? null,
                    'order_status' => $result['order_status'] ?? null,
                ]);
                
                return response()->json(['status' => 'success'], 200);
            } else {
                $webhookLog->update([
                    'status' => 'error',
                    'error_message' => $result['error'] ?? 'Erro desconhecido',
                    'response' => $result,
                    'processed_at' => now(),
                ]);
                
                Log::error('Erro ao processar webhook', [
                    'webhook_log_id' => $webhookLog->id,
                    'error' => $result,
                ]);
                
                return response()->json(['status' => 'error', 'message' => $result['error'] ?? 'Erro desconhecido'], 400);
            }
        } catch (\Exception $e) {
            $webhookLog->update([
                'status' => 'error',
                'error_message' => $e->getMessage(),
                'processed_at' => now(),
            ]);
            
            Log::error('Exceção ao processar webhook do MercadoPago', [
                'webhook_log_id' => $webhookLog->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_keys' => array_keys($payload),
            ]);
            
            return response()->json(['status' => 'error', 'message' => 'Erro interno'], 500);
        }
    }

    /**
     * Webhook do WhatsApp
     * Suporta tanto o formato antigo quanto o novo formato multi-instâncias
     */
    public function whatsApp(Request $request)
    {
        try {
            Log::info('Webhook WhatsApp recebido', $request->all());

            $data = $request->all();
            
            // Se tiver instance_phone, usa o novo sistema multi-instâncias
            if (isset($data['instance_phone'])) {
                $instanceController = new \App\Http\Controllers\WhatsappInstanceController();
                return $instanceController->handleWebhook($request);
            }
            
            // Processa mensagem recebida (formato antigo)
            $this->processWhatsAppMessage($data);

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Erro no webhook WhatsApp: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Processa mensagem do WhatsApp
     */
    private function processWhatsAppMessage(array $data)
    {
        $phone = $data['phone'] ?? null;
        $message = $data['message'] ?? null;

        if (!$phone || !$message) {
            return;
        }

        // Busca cliente pelo telefone
        $customer = \App\Models\Customer::where('phone', $phone)->first();
        
        if (!$customer) {
            // Cliente não encontrado, envia mensagem de boas-vindas
            $this->whatsAppService->sendMessage($phone, $this->getWelcomeMessage());
            return;
        }

        // Processa comando baseado na mensagem
        $message = strtolower(trim($message));

        switch ($message) {
            case 'cardapio':
            case 'cardápio':
                $this->sendMenuMessage($phone);
                break;
                
            case 'pedidos':
            case 'meus pedidos':
                $this->sendCustomerOrders($phone, $customer);
                break;
                
            case 'ajuda':
            case 'help':
                $this->sendHelpMessage($phone);
                break;
                
            default:
                $this->sendDefaultMessage($phone);
                break;
        }
    }

    /**
     * Envia mensagem de boas-vindas
     */
    private function getWelcomeMessage(): string
    {
        return "👋 *Bem-vindo ao Olika!*\n\n" .
               "Digite uma das opções:\n" .
               "• *cardápio* - Ver nosso cardápio\n" .
               "• *pedidos* - Ver seus pedidos\n" .
               "• *ajuda* - Mais informações\n\n" .
               "Ou acesse nosso site: pedido.menuolika.com.br";
    }

    /**
     * Envia cardápio
     */
    private function sendMenuMessage(string $phone)
    {
        $message = "🍕 *Cardápio Olika*\n\n" .
                  "Acesse nosso cardápio completo:\n" .
                  "🌐 pedido.menuolika.com.br\n\n" .
                  "Ou digite *ajuda* para mais opções.";

        $this->whatsAppService->sendMessage($phone, $message);
    }

    /**
     * Envia pedidos do cliente
     */
    private function sendCustomerOrders(string $phone, \App\Models\Customer $customer)
    {
        $orders = $customer->orders()
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        if ($orders->isEmpty()) {
            $message = "Você ainda não fez nenhum pedido.\n\n" .
                      "Acesse: pedido.menuolika.com.br";
        } else {
            $message = "📋 *Seus Pedidos Recentes*\n\n";
            
            foreach ($orders as $order) {
                $message .= "• #{$order->order_number} - {$order->status_label}\n";
                $message .= "  R$ " . number_format($order->final_amount, 2, ',', '.') . "\n";
                $message .= "  " . $order->created_at->format('d/m/Y H:i') . "\n\n";
            }
            
            $message .= "Acesse: pedido.menuolika.com.br";
        }

        $this->whatsAppService->sendMessage($phone, $message);
    }

    /**
     * Envia mensagem de ajuda
     */
    private function sendHelpMessage(string $phone)
    {
        $message = "ℹ️ *Ajuda - Olika*\n\n" .
                  "Comandos disponíveis:\n" .
                  "• *cardápio* - Ver cardápio\n" .
                  "• *pedidos* - Ver seus pedidos\n" .
                  "• *ajuda* - Esta mensagem\n\n" .
                  "Para fazer pedidos, acesse:\n" .
                  "🌐 pedido.menuolika.com.br\n\n" .
                  "Telefone: (71) 98701-9420";

        $this->whatsAppService->sendMessage($phone, $message);
    }

    /**
     * Envia mensagem padrão
     */
    private function sendDefaultMessage(string $phone)
    {
        $message = "Desculpe, não entendi sua mensagem.\n\n" .
                  "Digite *ajuda* para ver as opções disponíveis.";

        $this->whatsAppService->sendMessage($phone, $message);
    }

    /**
     * Webhook Mercado Pago simplificado (novo fluxo)
     */
    public function mercadoPagoSimple(Request $r)
    {
        $data = $r->all();
        $topic = $r->get('type') ?? $r->get('topic');
        
        // Usar a mesma função de extração que suporta todos os formatos (PIX e Link)
        $paymentId = MercadoPagoApiService::extractPaymentId($data);

        if ($topic === 'payment' && $paymentId) {
            $mp = new MercadoPagoApi();
            $payment = $mp->getPayment($paymentId);

            $orderId = data_get($payment, 'metadata.order_id');
            $status  = data_get($payment, 'status');

            if ($orderId && ($order = Order::find($orderId))) {
                // idempotência
                // Mapear status do MercadoPago para valores válidos do ENUM
                $mappedStatus = \App\Services\MercadoPagoApiService::mapPaymentStatus($status);
                if ($order->payment_status !== $mappedStatus) {
                    $order->payment_status = $mappedStatus;
                    $order->payment_id     = (string) data_get($payment, 'id');
                    $order->payment_raw_response = json_encode($payment);
                    
                    if ($status === 'approved') {
                        // Usa OrderStatusService para centralizar regras + WhatsApp
                        app(OrderStatusService::class)
                            ->changeStatus($order, 'paid', 'Pagamento aprovado (webhook MP)');

                        // registra uso do cupom (se houver)
                        if ($order->coupon_code && $order->customer_id) {
                            $coupon = \App\Models\Coupon::where('code', $order->coupon_code)->first();
                            if ($coupon) {
                                CouponUsage::firstOrCreate([
                                    'coupon_id'   => $coupon->id,
                                    'customer_id' => $order->customer_id,
                                    'order_id'    => $order->id,
                                    'used_at'     => now(),
                                ]);
                            }
                        }
                    }
                    $order->save();
                }
            }
        }

        return response()->json(['ok' => true]);
    }

    protected function isValidMercadoPagoSignature(Request $request): bool
    {
        $secret = config('services.mercadopago.webhook_secret');

        if (empty($secret)) {
            return true;
        }

        $signatureHeader = $request->header('x-signature');
        $requestId = $request->header('x-request-id');

        if (!$signatureHeader || !$requestId) {
            return false;
        }

        $parts = [];
        foreach (explode(',', $signatureHeader) as $segment) {
            [$key, $value] = array_pad(explode('=', trim($segment), 2), 2, null);
            if ($key && $value) {
                $parts[$key] = trim($value);
            }
        }

        $timestamp = $parts['ts'] ?? null;
        $signature = $parts['v1'] ?? null;

        if (!$timestamp || !$signature) {
            // fallback para formato sha256=hash
            if (str_starts_with($signatureHeader, 'sha256=')) {
                $signature = substr($signatureHeader, 7);
                $timestamp = (string) ($request->header('x-signature-timestamp') ?? '');
            } else {
                return false;
            }
        }

        $payload = "{$requestId}:{$timestamp}:" . $request->getContent();
        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signature);
    }
}
