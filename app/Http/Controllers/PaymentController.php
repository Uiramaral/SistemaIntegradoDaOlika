<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentSetting;
use App\Services\MercadoPagoApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $mercadoPagoService;

    public function __construct(MercadoPagoApiService $mercadoPagoService)
    {
        $this->mercadoPagoService = $mercadoPagoService;
    }

    /**
     * Exibe tela de pagamento PIX
     */
    public function pixPayment(Order $order)
    {
        // Verificar se o pedido já tem pagamento PIX
        if ($order->payment_id && $order->payment_status === 'pending') {
            return view('payment.pix', compact('order'));
        }

        // Criar nova cobrança PIX
        $pixResult = $this->mercadoPagoService->createPixPayment($order);

        if (!$pixResult['success']) {
            return redirect()->route('checkout.index')
                ->with('error', 'Erro ao gerar cobrança PIX: ' . ($pixResult['error'] ?? 'Erro desconhecido'));
        }

        // Atualizar dados do pedido com informações do PIX
        $order->update([
            'pix_qr_code_base64' => $pixResult['pix_qr_code_base64'] ?? null,
            'pix_copy_paste' => $pixResult['pix_copy_paste'] ?? null,
            'pix_expires_at' => $pixResult['expires_at'] ?? null,
        ]);

        return view('payment.pix', compact('order'));
    }

    /**
     * Exibe tela de pagamento com cartão
     */
    public function cardPayment(Order $order)
    {
        // Verificar se o pedido já tem preferência
        if ($order->preference_id) {
            return view('payment.card', compact('order'));
        }

        // Criar nova preferência de pagamento
        $preferenceResult = $this->mercadoPagoService->createPaymentPreference($order);

        if (!$preferenceResult['success']) {
            return redirect()->route('checkout.index')
                ->with('error', 'Erro ao gerar preferência de pagamento: ' . ($preferenceResult['error'] ?? 'Erro desconhecido'));
        }

        return view('payment.card', compact('order'));
    }

    /**
     * API: Criar cobrança PIX
     */
    public function createPix(Request $request, Order $order)
    {
        $pixResult = $this->mercadoPagoService->createPixPayment($order);

        if ($pixResult['success']) {
            return response()->json([
                'success' => true,
                'payment_id' => $pixResult['payment_id'],
                'pix_qr_code' => $pixResult['pix_qr_code'],
                'pix_qr_code_base64' => $pixResult['pix_qr_code_base64'],
                'pix_copy_paste' => $pixResult['pix_copy_paste'],
                'expires_at' => $pixResult['expires_at'],
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $pixResult['error'] ?? 'Erro ao criar cobrança PIX',
        ], 400);
    }

    /**
     * API: Criar preferência de pagamento
     */
    public function createPreference(Request $request, Order $order)
    {
        $preferenceResult = $this->mercadoPagoService->createPaymentPreference($order);

        if ($preferenceResult['success']) {
            return response()->json([
                'success' => true,
                'preference_id' => $preferenceResult['preference_id'],
                'payment_link' => $preferenceResult['payment_link'],
                'sandbox_init_point' => $preferenceResult['sandbox_init_point'],
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $preferenceResult['error'] ?? 'Erro ao criar preferência',
        ], 400);
    }

    /**
     * API: Consultar status do pagamento
     */
    public function getPaymentStatus(Request $request, Order $order)
    {
        if (!$order->payment_id) {
            return response()->json([
                'success' => false,
                'error' => 'Pagamento não encontrado',
            ], 404);
        }

        $statusResult = $this->mercadoPagoService->getPaymentStatus($order->payment_id);

        if ($statusResult['success']) {
            return response()->json([
                'success' => true,
                'payment' => $statusResult['payment'],
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $statusResult['error'] ?? 'Erro ao consultar pagamento',
        ], 400);
    }

    /**
     * API: Obter configurações públicas
     */
    public function getPublicConfig()
    {
        $config = $this->mercadoPagoService->getPublicConfig();
        
        return response()->json([
            'success' => true,
            'config' => $config,
        ]);
    }

    /**
     * Página de sucesso do pagamento
     */
    public function success(Order $order)
    {
        return view('payment.success', compact('order'));
    }

    /**
     * Página de falha do pagamento
     */
    public function failure(Order $order)
    {
        return view('payment.failure', compact('order'));
    }

    /**
     * Página de pagamento pendente
     */
    public function pending(Order $order)
    {
        return view('payment.pending', compact('order'));
    }
}
