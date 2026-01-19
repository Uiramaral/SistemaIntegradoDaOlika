<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterSetting;
use Illuminate\Http\Request;

class MasterSettingsController extends Controller
{
    /**
     * Exibe configurações do Master
     */
    public function index()
    {
        $settings = [
            'whatsapp_instance_price' => MasterSetting::get('whatsapp_instance_price', 15.00),
            'ai_message_price' => MasterSetting::get('ai_message_price', 5.00),
            'default_trial_days' => MasterSetting::get('default_trial_days', 7),
            'billing_cycle_days' => MasterSetting::get('billing_cycle_days', 30),
            'expiry_warning_days_1' => MasterSetting::get('expiry_warning_days_1', 7),
            'expiry_warning_days_2' => MasterSetting::get('expiry_warning_days_2', 3),
            'expiry_warning_days_3' => MasterSetting::get('expiry_warning_days_3', 1),
            'grace_period_days' => MasterSetting::get('grace_period_days', 3),
            'support_email' => MasterSetting::get('support_email', ''),
            'billing_email' => MasterSetting::get('billing_email', ''),
            'support_whatsapp' => MasterSetting::get('support_whatsapp', ''),
            // Tokens de IA (compartilhados entre todos os estabelecimentos)
            'gemini_api_key' => MasterSetting::get('gemini_api_key', ''),
            'openai_api_key' => MasterSetting::get('openai_api_key', ''),
        ];

        return view('master.settings.index', compact('settings'));
    }

    /**
     * Salva configurações
     */
    public function update(Request $request)
    {
        // Get all fillable settings from the request
        $settingsKeys = [
            'whatsapp_instance_price',
            'ai_message_price',
            'default_trial_days',
            'billing_cycle_days',
            'expiry_warning_days_1',
            'expiry_warning_days_2',
            'expiry_warning_days_3',
            'grace_period_days',
            'support_email',
            'billing_email',
            'support_whatsapp',
            // ⚡ NOVO: Configurações de Cadastro
            'registration_trial_days',
            'registration_default_commission',
            'registration_commission_enabled',
            'registration_default_plan',
            'registration_require_approval',
            'registration_notify_master',
            'registration_master_email',
            // Tokens de IA
            'gemini_api_key',
            'openai_api_key',
        ];
        
        foreach ($settingsKeys as $key) {
            if ($request->has($key)) {
                $value = $request->input($key);
                
                // Tratar checkboxes (se não vier no request, é false)
                if (in_array($key, ['registration_commission_enabled', 'registration_require_approval', 'registration_notify_master'])) {
                    $value = $request->has($key) ? 1 : 0;
                }
                
                MasterSetting::set($key, $value);
            }
        }

        return back()->with('success', 'Configurações salvas com sucesso!');
    }

    /**
     * Configurações individuais via AJAX
     */
    public function updateSingle(Request $request)
    {
        $request->validate([
            'key' => 'required|string|max:100',
            'value' => 'required',
            'type' => 'nullable|in:string,integer,decimal,boolean,json',
        ]);

        MasterSetting::set(
            $request->key, 
            $request->value, 
            $request->type
        );

        return response()->json([
            'success' => true,
            'message' => 'Configuração atualizada com sucesso!',
        ]);
    }
}
