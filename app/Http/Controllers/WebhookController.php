<?php

namespace App\Http\Controllers;

use App\Services\MercadoPagoApiService;
use App\Services\WhatsAppService;
use App\Models\Order;
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
        Log::info('Mercado Pago Webhook Received', $request->all());
        
        try {
            // Usar o novo service para processar webhook
            $result = $this->mercadoPagoService->processWebhook($request->all());
            
            if ($result['success']) {
                Log::info('Webhook processado com sucesso', $result);
                
                // Enviar notificação WhatsApp se aprovado
                if ($result['payment_status'] === 'approved') {
                    $order = Order::find($result['order_id']);
                    if ($order && $order->customer && $order->customer->phone) {
                        $message = "✅ Seu pedido #{$order->order_number} foi confirmado! Total: R$ " . number_format($order->final_amount, 2, ',', '.');
                        $this->whatsAppService->sendMessage($order->customer->phone, $message);
                    }
                }
                
                return response()->json(['status' => 'success'], 200);
            } else {
                Log::error('Erro ao processar webhook', $result);
                return response()->json(['status' => 'error', 'message' => $result['error']], 400);
            }
        } catch (\Exception $e) {
            Log::error('Exceção ao processar webhook do MercadoPago', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            
            return response()->json(['status' => 'error', 'message' => 'Erro interno'], 500);
        }
    }

    /**
     * Webhook do WhatsApp
     */
    public function whatsApp(Request $request)
    {
        try {
            Log::info('Webhook WhatsApp recebido', $request->all());

            $data = $request->all();
            
            // Processa mensagem recebida
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
}
