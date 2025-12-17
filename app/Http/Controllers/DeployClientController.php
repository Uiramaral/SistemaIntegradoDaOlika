<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Client;
use Illuminate\Support\Str;

class DeployClientController extends Controller
{
    /**
     * Dispara deploy de um cliente via GitHub Actions
     */
    public function deploy(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
        ]);

        $client = Client::findOrFail($request->input('client_id'));

        // Verificar se o cliente tem plano IA
        if (!$client->hasIaPlan()) {
            return response()->json([
                'success' => false,
                'error' => 'Apenas clientes com plano IA podem ter instância Railway'
            ], 400);
        }

        // Gerar slug único se não existir
        if (!$client->slug) {
            $client->slug = Str::slug($client->name . '-' . $client->id);
            $client->save();
        }

        $githubToken = env('GITHUB_TOKEN');
        $githubRepo = env('GITHUB_REPO');

        if (!$githubToken || !$githubRepo) {
            Log::error('DeployClientController: GITHUB_TOKEN ou GITHUB_REPO não configurado');
            return response()->json([
                'success' => false,
                'error' => 'Configuração GitHub incompleta'
            ], 500);
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
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github+json',
                'Authorization' => 'Bearer ' . $githubToken,
                'X-GitHub-Api-Version' => '2022-11-28',
            ])->timeout(10)->post(
                "https://api.github.com/repos/{$githubRepo}/actions/workflows/deploy-client.yml/dispatches",
                $payload
            );

            if ($response->failed()) {
                Log::error('DeployClientController: Erro ao disparar workflow GitHub', [
                    'response' => $response->body(),
                    'status' => $response->status(),
                    'client_id' => $client->id,
                ]);

                // Atualizar status para falha
                $client->update(['deploy_status' => 'failed']);

                // Registrar log de deploy
                DB::table('deployment_logs')->insert([
                    'client_id' => $client->id,
                    'status' => 'failure',
                    'message' => 'Falha ao iniciar workflow no GitHub: ' . $response->body(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'Falha ao iniciar deploy: ' . $response->body()
                ], 500);
            }

            // Atualizar status do cliente
            $client->update(['deploy_status' => 'in_progress']);

            // Registrar log de deploy
            DB::table('deployment_logs')->insert([
                'client_id' => $client->id,
                'status' => 'queued',
                'message' => 'Deploy solicitado no GitHub Actions',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info('DeployClientController: Deploy solicitado no GitHub Actions', [
                'client_id' => $client->id,
                'client_name' => $client->name,
                'slug' => $client->slug,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Deploy iniciado com sucesso',
                'slug' => $client->slug,
                'status' => 'in_progress'
            ]);

        } catch (\Exception $e) {
            Log::error('DeployClientController: Exceção ao disparar workflow', [
                'error' => $e->getMessage(),
                'client_id' => $client->id,
            ]);

            $client->update(['deploy_status' => 'failed']);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao iniciar deploy: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Webhook para receber callback do GitHub após deploy
     */
    public function webhook(Request $request)
    {
        $clientId = $request->input('client_id');
        $status = $request->input('status');
        $branch = $request->input('branch');
        $runId = $request->input('run_id');
        $clientSlug = $request->input('client_slug');

        if (!$clientId) {
            return response()->json(['success' => false, 'error' => 'client_id é obrigatório'], 400);
        }

        $client = Client::find($clientId);
        if (!$client) {
            Log::warning('DeployClientController::webhook - Cliente não encontrado', ['client_id' => $clientId]);
            return response()->json(['success' => false, 'error' => 'Cliente não encontrado'], 404);
        }

        // Construir URL da instância
        $url = "https://{$clientSlug}.railway.app";
        if ($clientSlug && env('APP_DOMAIN')) {
            // Se tiver domínio customizado configurado
            $url = "https://{$clientSlug}." . env('APP_DOMAIN');
        }

        // Atualizar status do cliente
        $deployStatus = ($status === 'completed') ? 'completed' : 'failed';
        $client->update([
            'deploy_status' => $deployStatus,
            'instance_url' => $url,
        ]);

        // Registrar log de deploy
        DB::table('deployment_logs')->insert([
            'client_id' => $clientId,
            'github_run_id' => $runId,
            'status' => ($status === 'completed') ? 'success' : 'failure',
            'branch_name' => $branch,
            'message' => "Deploy finalizado com status: {$status}",
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info("DeployClientController::webhook - Webhook GitHub recebido", [
            'client_id' => $clientId,
            'status' => $status,
            'branch' => $branch,
            'url' => $url,
            'run_id' => $runId,
        ]);

        return response()->json(['success' => true]);
    }
}


