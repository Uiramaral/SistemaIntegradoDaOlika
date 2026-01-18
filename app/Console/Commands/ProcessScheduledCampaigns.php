<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WhatsappCampaign;
use App\Http\Controllers\WhatsappCampaignController;
use Illuminate\Support\Facades\Log;

class ProcessScheduledCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaigns:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processa campanhas WhatsApp agendadas que devem ser iniciadas agora';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();
        
        // Buscar campanhas agendadas que devem ser iniciadas
        $campaigns = WhatsappCampaign::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', $now)
            ->get();

        if ($campaigns->isEmpty()) {
            $this->info('Nenhuma campanha agendada para processar.');
            return Command::SUCCESS;
        }

        $this->info("Processando {$campaigns->count()} campanha(s) agendada(s)...");

        foreach ($campaigns as $campaign) {
            try {
                $this->info("Iniciando campanha: {$campaign->name} (ID: {$campaign->id})");
                
                // Usar o método dispatchCampaign do controller
                $controller = new WhatsappCampaignController();
                $controller->dispatchCampaign($campaign);
                
                $this->info("✅ Campanha '{$campaign->name}' iniciada com sucesso.");
                
                Log::info('Campanha agendada processada', [
                    'campaign_id' => $campaign->id,
                    'campaign_name' => $campaign->name,
                    'scheduled_at' => $campaign->scheduled_at,
                ]);
            } catch (\Exception $e) {
                $this->error("❌ Erro ao processar campanha '{$campaign->name}': " . $e->getMessage());
                
                Log::error('Erro ao processar campanha agendada', [
                    'campaign_id' => $campaign->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return Command::SUCCESS;
    }
}

