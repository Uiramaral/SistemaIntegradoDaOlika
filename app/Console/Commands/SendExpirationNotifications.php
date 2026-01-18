<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\SubscriptionNotification;
use App\Models\MasterSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendExpirationNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'subscription:notify-expiring';

    /**
     * The console command description.
     */
    protected $description = 'Envia notificações para assinaturas que estão expirando em breve';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificando assinaturas expirando...');

        // Buscar dias de aviso das configurações
        $warningDays = [
            MasterSetting::get('expiry_warning_days_1', 7),
            MasterSetting::get('expiry_warning_days_2', 3),
            MasterSetting::get('expiry_warning_days_3', 1),
        ];

        $notificationsSent = 0;

        foreach ($warningDays as $days) {
            $targetDate = now()->addDays($days)->startOfDay();
            
            $subscriptions = Subscription::with(['client', 'plan'])
                ->where('status', 'active')
                ->whereDate('current_period_end', $targetDate)
                ->get();

            foreach ($subscriptions as $subscription) {
                // Verificar se já foi enviada notificação hoje para este período
                $existingNotification = SubscriptionNotification::where('subscription_id', $subscription->id)
                    ->where('type', 'expiring_soon')
                    ->whereDate('created_at', now()->toDateString())
                    ->exists();

                if (!$existingNotification) {
                    $this->sendExpirationNotification($subscription, $days);
                    $notificationsSent++;
                }
            }
        }

        // Verificar assinaturas vencidas
        $expiredSubscriptions = Subscription::with(['client', 'plan'])
            ->where('status', 'active')
            ->where('current_period_end', '<', now())
            ->get();

        foreach ($expiredSubscriptions as $subscription) {
            // Verificar se já foi enviada notificação de expiração hoje
            $existingNotification = SubscriptionNotification::where('subscription_id', $subscription->id)
                ->where('type', 'expired')
                ->whereDate('created_at', now()->toDateString())
                ->exists();

            if (!$existingNotification) {
                $this->sendExpiredNotification($subscription);
                $notificationsSent++;
            }
        }

        $this->info("Total de notificações enviadas: {$notificationsSent}");
        
        return Command::SUCCESS;
    }

    /**
     * Envia notificação de expiração próxima
     */
    protected function sendExpirationNotification(Subscription $subscription, int $daysRemaining): void
    {
        $client = $subscription->client;
        
        if (!$client) {
            return;
        }

        $message = "Sua assinatura do plano {$subscription->plan?->name} expira em {$daysRemaining} dia(s). Renove para continuar usando todos os recursos!";

        // Criar notificação no sistema
        SubscriptionNotification::create([
            'subscription_id' => $subscription->id,
            'type' => 'expiring_soon',
            'title' => 'Assinatura Expirando',
            'message' => $message,
            'days_until_expiry' => $daysRemaining,
        ]);

        // Enviar por WhatsApp se configurado
        if ($client->whatsapp_phone) {
            $this->sendWhatsAppNotification($client->whatsapp_phone, $message);
        }

        Log::info("Notificação de expiração enviada", [
            'client' => $client->name,
            'days_remaining' => $daysRemaining,
        ]);
    }

    /**
     * Envia notificação de assinatura expirada
     */
    protected function sendExpiredNotification(Subscription $subscription): void
    {
        $client = $subscription->client;
        
        if (!$client) {
            return;
        }

        $message = "Sua assinatura do plano {$subscription->plan?->name} expirou! Renove agora para não perder acesso aos recursos.";

        // Criar notificação no sistema
        SubscriptionNotification::create([
            'subscription_id' => $subscription->id,
            'type' => 'expired',
            'title' => 'Assinatura Expirada',
            'message' => $message,
            'days_until_expiry' => 0,
        ]);

        // Enviar por WhatsApp se configurado
        if ($client->whatsapp_phone) {
            $this->sendWhatsAppNotification($client->whatsapp_phone, $message);
        }

        // Verificar período de carência
        $gracePeriodDays = MasterSetting::get('grace_period_days', 3);
        $gracePeriodEnd = $subscription->current_period_end->addDays($gracePeriodDays);

        if (now()->isAfter($gracePeriodEnd)) {
            // Suspender assinatura
            $subscription->update(['status' => 'suspended']);
            Log::warning("Assinatura suspensa por falta de pagamento", [
                'client' => $client->name,
                'subscription_id' => $subscription->id,
            ]);
        }

        Log::info("Notificação de expiração enviada", [
            'client' => $client->name,
        ]);
    }

    /**
     * Envia mensagem via WhatsApp
     */
    protected function sendWhatsAppNotification(string $phone, string $message): void
    {
        try {
            $waService = app(\App\Services\WhatsAppService::class);
            $waService->sendMessage($phone, $message);
        } catch (\Exception $e) {
            Log::error("Erro ao enviar notificação WhatsApp: " . $e->getMessage());
        }
    }
}
