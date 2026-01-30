<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationsController extends Controller
{
    public function index()
    {
        $clientId = currentClientId();
        
        // Buscar configurações de notificações
        $notificationSettings = $this->getNotificationSettings($clientId);
        
        return view('dashboard.notifications.index', compact('notificationSettings'));
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'notify_new_order' => 'nullable|boolean',
            'notify_order_status_change' => 'nullable|boolean',
            'notify_payment_received' => 'nullable|boolean',
            'notify_delivery_reminder' => 'nullable|boolean',
            'delivery_reminder_hours' => 'nullable|integer|min:1|max:168',
        ]);

        $clientId = currentClientId();

        // Converter checkboxes para boolean
        $settings = [
            'notify_new_order' => $request->has('notify_new_order') ? '1' : '0',
            'notify_order_status_change' => $request->has('notify_order_status_change') ? '1' : '0',
            'notify_payment_received' => $request->has('notify_payment_received') ? '1' : '0',
            'notify_delivery_reminder' => $request->has('notify_delivery_reminder') ? '1' : '0',
            'delivery_reminder_hours' => $data['delivery_reminder_hours'] ?? 24,
        ];

        // Salvar usando PaymentSetting (tabela flexível)
        foreach ($settings as $key => $value) {
            \App\Models\PaymentSetting::updateOrCreate(
                [
                    'client_id' => $clientId,
                    'key' => $key
                ],
                [
                    'value' => $value,
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => DB::raw('COALESCE(created_at, NOW())')
                ]
            );
        }

        return back()->with('success', 'Configurações de notificações salvas com sucesso!');
    }

    private function getNotificationSettings($clientId)
    {
        $keys = [
            'notify_new_order',
            'notify_order_status_change',
            'notify_payment_received',
            'notify_delivery_reminder',
            'delivery_reminder_hours',
        ];

        $settings = \App\Models\PaymentSetting::where('client_id', $clientId)
            ->whereIn('key', $keys)
            ->pluck('value', 'key')
            ->toArray();

        // Converter valores string para boolean onde necessário
        foreach (['notify_new_order', 'notify_order_status_change', 'notify_payment_received', 'notify_delivery_reminder'] as $key) {
            if (isset($settings[$key])) {
                $settings[$key] = $settings[$key] === '1' || $settings[$key] === 1 || $settings[$key] === true;
            } else {
                $settings[$key] = true; // Padrão: ativado
            }
        }

        // Valor padrão para horas
        if (!isset($settings['delivery_reminder_hours'])) {
            $settings['delivery_reminder_hours'] = 24;
        } else {
            $settings['delivery_reminder_hours'] = (int) $settings['delivery_reminder_hours'];
        }

        return $settings;
    }
}
