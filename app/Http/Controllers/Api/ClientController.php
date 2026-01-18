<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Models\Client;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * ClientController - Gerenciamento de Clientes/Tenants
 * 
 * Endpoints para:
 * - Verificar disponibilidade de slug
 * - Criar novo cliente (cadastro)
 * - Listar clientes (admin)
 * - Atualizar dados do cliente
 */
class ClientController extends Controller
{
    /**
     * Verificar disponibilidade de slug
     * 
     * GET /api/clients/check-slug?slug=minha-loja
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function checkSlug(Request $request): JsonResponse
    {
        $request->validate([
            'slug' => 'required|string|min:3|max:30',
        ]);

        $result = Client::checkSlugAvailability($request->slug);

        return response()->json($result);
    }

    /**
     * Cadastrar novo cliente (público - para onboarding)
     * 
     * POST /api/clients/register
     */
    public function register(StoreClientRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Criar o cliente
            $client = Client::create([
                'name' => $request->name,
                'slug' => $request->slug, // Será normalizado no model
                'plan' => $request->plan ?? 'basic',
                'active' => true,
                'is_trial' => true,
                'trial_started_at' => now(),
                'trial_ends_at' => now()->addDays(14),
            ]);

            // Criar usuário admin do cliente
            $user = User::create([
                'name' => $request->admin_name ?? $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'client_id' => $client->id,
                'role' => 'admin',
            ]);

            // Criar configurações padrão
            Setting::create([
                'client_id' => $client->id,
                'store_name' => $request->name,
                'store_phone' => $request->phone ?? null,
                // Configurações padrão serão aplicadas no model
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cadastro realizado com sucesso!',
                'client' => [
                    'id' => $client->id,
                    'name' => $client->name,
                    'slug' => $client->slug,
                    'menu_url' => $client->menu_url,
                    'trial_ends_at' => $client->trial_ends_at->format('d/m/Y'),
                ],
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                ],
            ], 201);

        } catch (\InvalidArgumentException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('ClientController@register: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar cadastro. Tente novamente.',
            ], 500);
        }
    }

    /**
     * Listar todos os clientes (apenas super admin)
     * 
     * GET /api/admin/clients
     */
    public function index(Request $request): JsonResponse
    {
        $query = Client::query();

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('plan')) {
            $query->where('plan', $request->plan);
        }

        if ($request->filled('active')) {
            $query->where('active', $request->boolean('active'));
        }

        if ($request->filled('is_trial')) {
            $query->where('is_trial', $request->boolean('is_trial'));
        }

        // Ordenação
        $orderBy = $request->get('order_by', 'created_at');
        $orderDir = $request->get('order_dir', 'desc');
        $query->orderBy($orderBy, $orderDir);

        // Paginação
        $clients = $query->paginate($request->get('per_page', 20));

        return response()->json($clients);
    }

    /**
     * Detalhes de um cliente (apenas super admin)
     * 
     * GET /api/admin/clients/{id}
     */
    public function show(int $id): JsonResponse
    {
        $client = Client::with(['customers', 'orders', 'products'])
            ->withCount(['customers', 'orders', 'products'])
            ->findOrFail($id);

        return response()->json([
            'client' => $client,
            'stats' => [
                'customers_count' => $client->customers_count,
                'orders_count' => $client->orders_count,
                'products_count' => $client->products_count,
            ],
        ]);
    }

    /**
     * Atualizar cliente (apenas super admin)
     * 
     * PUT /api/admin/clients/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $client = Client::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'plan' => 'sometimes|in:basic,ia',
            'active' => 'sometimes|boolean',
            'is_trial' => 'sometimes|boolean',
            'trial_ends_at' => 'sometimes|date',
            'whatsapp_phone' => 'sometimes|nullable|string|max:20',
            // Nota: slug NÃO pode ser alterado
        ]);

        $client->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cliente atualizado com sucesso.',
            'client' => $client->fresh(),
        ]);
    }

    /**
     * Suspender cliente (apenas super admin)
     * 
     * POST /api/admin/clients/{id}/suspend
     */
    public function suspend(int $id): JsonResponse
    {
        $client = Client::findOrFail($id);
        
        // Não permitir suspender a Olika
        if ($id === 1) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível suspender a Olika Tecnologia.',
            ], 403);
        }

        $client->update(['active' => false]);

        \Log::info("Cliente suspenso: {$client->name} (ID: {$id})");

        return response()->json([
            'success' => true,
            'message' => 'Cliente suspenso com sucesso.',
        ]);
    }

    /**
     * Reativar cliente (apenas super admin)
     * 
     * POST /api/admin/clients/{id}/activate
     */
    public function activate(int $id): JsonResponse
    {
        $client = Client::findOrFail($id);

        $client->update(['active' => true]);

        \Log::info("Cliente reativado: {$client->name} (ID: {$id})");

        return response()->json([
            'success' => true,
            'message' => 'Cliente reativado com sucesso.',
        ]);
    }

    /**
     * Estatísticas gerais (apenas super admin)
     * 
     * GET /api/admin/clients/stats
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'total_clients' => Client::count(),
            'active_clients' => Client::where('active', true)->count(),
            'trial_clients' => Client::where('is_trial', true)->count(),
            'trial_expired' => Client::trialExpired()->count(),
            'by_plan' => [
                'basic' => Client::where('plan', 'basic')->count(),
                'ia' => Client::where('plan', 'ia')->count(),
            ],
        ]);
    }
}
