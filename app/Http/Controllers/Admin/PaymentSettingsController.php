<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use Illuminate\Http\Request;

class PaymentSettingsController extends Controller
{
    /**
     * Exibe configurações de pagamento
     */
    public function index()
    {
        $settings = PaymentSetting::all()->keyBy('key');
        
        return view('admin.payment-settings', compact('settings'));
    }

    /**
     * Atualiza configurações
     */
    public function update(Request $request)
    {
        $request->validate([
            'mercadopago_access_token' => 'required|string',
            'mercadopago_public_key' => 'required|string',
            'mercadopago_environment' => 'required|in:sandbox,production',
            'test_mode_enabled' => 'boolean',
            'pix_expiration_minutes' => 'required|integer|min:1|max:1440',
        ]);

        try {
            // Atualizar configurações
            PaymentSetting::setValue('mercadopago_access_token', $request->mercadopago_access_token, 'Token de acesso do MercadoPago');
            PaymentSetting::setValue('mercadopago_public_key', $request->mercadopago_public_key, 'Chave pública do MercadoPago');
            PaymentSetting::setValue('mercadopago_environment', $request->mercadopago_environment, 'Ambiente do MercadoPago');
            PaymentSetting::setValue('pix_expiration_minutes', $request->pix_expiration_minutes, 'Tempo de expiração do PIX');
            
            // Atualizar modo de teste
            PaymentSetting::setTestMode($request->boolean('test_mode_enabled'));

            return response()->json([
                'success' => true,
                'message' => 'Configurações atualizadas com sucesso!',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar configurações: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Ativar/desativar modo de teste
     */
    public function toggleTestMode(Request $request)
    {
        $request->validate([
            'enabled' => 'required|boolean',
        ]);

        try {
            PaymentSetting::setTestMode($request->enabled);

            return response()->json([
                'success' => true,
                'message' => $request->enabled 
                    ? 'Modo de teste ativado! Valores serão entre 1-10 centavos.' 
                    : 'Modo de teste desativado! Valores normais serão aplicados.',
                'test_mode_enabled' => $request->enabled,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao alterar modo de teste: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Obter configurações atuais
     */
    public function getSettings()
    {
        $settings = PaymentSetting::all()->mapWithKeys(function ($setting) {
            return [$setting->key => $setting->value];
        });

        return response()->json([
            'success' => true,
            'settings' => $settings,
        ]);
    }

    /**
     * API: Testar conexão com MercadoPago
     */
    public function testConnection()
    {
        try {
            $accessToken = PaymentSetting::getMercadoPagoToken();
            
            if (empty($accessToken)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token de acesso não configurado',
                ], 400);
            }

            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])->get('https://api.mercadopago.com/v1/payment_methods');

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Conexão com MercadoPago estabelecida com sucesso!',
                    'environment' => PaymentSetting::getMercadoPagoEnvironment(),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Erro na conexão: ' . $response->body(),
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao testar conexão: ' . $e->getMessage(),
            ], 500);
        }
    }
}
