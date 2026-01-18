<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionNotification;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Exibe página de plano e assinatura do cliente
     */
    public function index()
    {
        $client = currentClient();
        
        if (!$client) {
            return redirect()->route('dashboard.index')
                ->with('error', 'Cliente não encontrado.');
        }

        $subscription = $client->subscription;
        $subscription?->load(['plan', 'addons', 'invoices' => fn($q) => $q->orderByDesc('created_at')->take(5)]);

        // Todos os planos disponíveis
        $plans = Plan::active()->get();

        // Notificações não lidas
        $notifications = [];
        if ($subscription) {
            $notifications = SubscriptionNotification::where('subscription_id', $subscription->id)
                ->inApp()
                ->unread()
                ->orderByDesc('created_at')
                ->get();
        }

        return view('dashboard.subscription.index', compact(
            'client',
            'subscription',
            'plans',
            'notifications'
        ));
    }

    /**
     * Solicita upgrade de plano
     */
    public function requestUpgrade(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $client = currentClient();
        $subscription = $client?->subscription;
        $newPlan = Plan::find($request->plan_id);

        if (!$subscription) {
            return back()->with('error', 'Você não possui uma assinatura ativa.');
        }

        if ($subscription->plan_id === $newPlan->id) {
            return back()->with('info', 'Você já está neste plano.');
        }

        // Calcular diferença proporcional
        $priceDifference = $newPlan->price - $subscription->price;
        $proratedPrice = $subscription->calculateProratedPrice(abs($priceDifference));

        // Para este exemplo, apenas atualizamos direto
        // Em produção, você integraria com gateway de pagamento
        $subscription->update([
            'plan_id' => $newPlan->id,
            'price' => $newPlan->price,
        ]);

        $message = $priceDifference > 0 
            ? "Plano atualizado para {$newPlan->name}! Valor proporcional adicional: R$ " . number_format($proratedPrice, 2, ',', '.')
            : "Plano alterado para {$newPlan->name}!";

        return back()->with('success', $message);
    }

    /**
     * Solicita renovação
     */
    public function renew()
    {
        $client = currentClient();
        $subscription = $client?->subscription;

        if (!$subscription) {
            return back()->with('error', 'Você não possui uma assinatura ativa.');
        }

        // Em produção, integraria com gateway de pagamento
        // Por enquanto, apenas renova
        $subscription->renew();

        return back()->with('success', 'Assinatura renovada com sucesso por mais 30 dias!');
    }

    /**
     * Marca notificação como lida
     */
    public function markNotificationRead(SubscriptionNotification $notification)
    {
        $notification->markAsRead();
        return response()->json(['success' => true]);
    }

    /**
     * Marca todas as notificações como lidas
     */
    public function markAllNotificationsRead()
    {
        $client = currentClient();
        $subscription = $client?->subscription;

        if ($subscription) {
            SubscriptionNotification::where('subscription_id', $subscription->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        return back()->with('success', 'Todas as notificações marcadas como lidas.');
    }

    /**
     * Exibe histórico de faturas
     */
    public function invoices()
    {
        $client = currentClient();
        $subscription = $client?->subscription;

        $invoices = $subscription 
            ? $subscription->invoices()->orderByDesc('created_at')->paginate(20)
            : collect();

        return view('dashboard.subscription.invoices', compact('subscription', 'invoices'));
    }
}
