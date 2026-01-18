<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PlansController extends Controller
{
    /**
     * Exibe a página de módulos/planos
     */
    public function index()
    {
        // Buscar cliente atual do usuário logado
        $user = Auth::user();
        $currentClient = null;
        
        if ($user) {
            // Tentar buscar pelo client_id do usuário
            if ($user->client_id) {
                $currentClient = Client::find($user->client_id);
            }
            
            // Se não encontrou pelo client_id, tentar buscar pelo primeiro cliente ativo
            // (para casos onde o usuário ainda não está vinculado a um cliente)
            if (!$currentClient) {
                $currentClient = Client::where('active', true)->first();
            }
        }

        // Definir planos disponíveis
        $plans = [
            'basic' => [
                'name' => 'Plano Básico',
                'description' => 'Funcionalidades essenciais para gerenciar seu negócio',
                'features' => [
                    'Vendas online e presencial',
                    'PDV (Ponto de Venda)',
                    'Cadastro de produtos e categorias',
                    'Gestão de clientes',
                    'Sistema de cupons',
                    'Cashback e fidelidade',
                    'Relatórios e análises',
                    'Integração com MercadoPago',
                ],
                'price' => 'R$ 99,90/mês',
            ],
            'ia' => [
                'name' => 'Plano WhatsApp',
                'description' => 'Tudo do básico + integração completa com WhatsApp',
                'features' => [
                    'Todas as funcionalidades do Plano Básico',
                    'Integração WhatsApp para notificações',
                    'Envio automático de atualizações de pedidos',
                    'Campanhas de marketing via WhatsApp',
                    'Templates de mensagens personalizáveis',
                    'Agendamento de mensagens',
                    'Suporte a múltiplas instâncias',
                ],
                'price' => 'R$ 149,90/mês',
            ],
        ];

        return view('dashboard.plans.index', compact('currentClient', 'plans'));
    }

    /**
     * Atualiza o plano do cliente (apenas visualização - não processa pagamento)
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || !$user->client_id) {
            return redirect()->back()
                ->with('error', 'Usuário não vinculado a um cliente.');
        }

        $validated = $request->validate([
            'plan' => 'required|in:basic,ia',
        ]);

        try {
            $client = Client::findOrFail($user->client_id);
            $client->plan = $validated['plan'];
            $client->save();

            Log::info('Plano atualizado', [
                'client_id' => $client->id,
                'new_plan' => $validated['plan'],
                'user_id' => $user->id,
            ]);

            return redirect()->route('dashboard.plans.index')
                ->with('success', 'Plano atualizado com sucesso! As alterações serão aplicadas no próximo ciclo de cobrança.');

        } catch (\Exception $e) {
            Log::error('Erro ao atualizar plano', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return redirect()->back()
                ->with('error', 'Erro ao atualizar plano. Por favor, tente novamente.');
        }
    }
}

