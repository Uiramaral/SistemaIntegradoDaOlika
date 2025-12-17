<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Client;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class DeployClientCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'olika:deploy {client_id : ID do cliente para fazer deploy}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispara deploy manual para um cliente via GitHub Actions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $clientId = $this->argument('client_id');
        
        $client = Client::find($clientId);
        
        if (!$client) {
            $this->error("❌ Cliente #{$clientId} não encontrado!");
            return 1;
        }
        
        $this->info("🚀 Iniciando deploy para cliente: {$client->name} (#{$client->id})");
        
        // Verificar plano
        if (!$client->hasIaPlan()) {
            $this->error("❌ Cliente não tem plano IA. Apenas clientes com plano IA podem ter instância Railway.");
            return 1;
        }
        
        // Gerar slug se não existir
        if (!$client->slug) {
            $client->slug = Str::slug($client->name . '-' . $client->id);
            $client->save();
            $this->info("✅ Slug gerado: {$client->slug}");
        }
        
        $githubToken = env('GITHUB_TOKEN');
        $githubRepo = env('GITHUB_REPO');
        
        if (!$githubToken || !$githubRepo) {
            $this->error("❌ GITHUB_TOKEN ou GITHUB_REPO não configurado no .env");
            return 1;
        }
        
        $payload = [
            'ref' => 'main',
            'inputs' => [
                'client_id' => (string)$client->id,
                'client_name' => $client->name,
                'client_slug' => $client->slug,
                'environment' => 'production'
            ]
        ];
        
        try {
            $this->info("📡 Disparando workflow no GitHub Actions...");
            
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github+json',
                'Authorization' => 'Bearer ' . $githubToken,
                'X-GitHub-Api-Version' => '2022-11-28',
            ])->timeout(10)->post(
                "https://api.github.com/repos/{$githubRepo}/actions/workflows/deploy-client.yml/dispatches",
                $payload
            );
            
            if ($response->failed()) {
                $this->error("❌ Falha ao iniciar deploy: " . $response->body());
                Log::error('DeployClientCommand: Erro ao disparar workflow', [
                    'client_id' => $client->id,
                    'response' => $response->body(),
                ]);
                
                $client->update(['deploy_status' => 'failed']);
                return 1;
            }
            
            $client->update(['deploy_status' => 'in_progress']);
            
            $this->info("✅ Deploy iniciado com sucesso!");
            $this->info("📋 Status: in_progress");
            $this->info("🔗 Slug: {$client->slug}");
            $this->line("");
            $this->comment("💡 Acompanhe o progresso no GitHub Actions:");
            $this->comment("   https://github.com/{$githubRepo}/actions");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Erro ao iniciar deploy: " . $e->getMessage());
            Log::error('DeployClientCommand: Exceção', [
                'client_id' => $client->id,
                'error' => $e->getMessage(),
            ]);
            
            $client->update(['deploy_status' => 'failed']);
            return 1;
        }
    }
}


