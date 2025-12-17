<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SaasClientsController extends Controller
{
    /**
     * Exibe o formulário de cadastro de novo cliente SaaS
     */
    public function create()
    {
        return view('dashboard.saas-clients.create');
    }

    /**
     * Processa o cadastro de novo cliente SaaS (apenas para master)
     */
    public function store(Request $request)
    {
        // Verificar se é master
        if (!auth()->check() || !auth()->user()->isMaster()) {
            abort(403, 'Acesso negado. Apenas o gestor geral pode acessar esta página.');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'plan' => 'required|in:basic,ia',
            'whatsapp_phone' => 'nullable|string|max:20',
        ], [
            'name.required' => 'O nome da empresa é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Digite um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter pelo menos 6 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.',
            'plan.required' => 'O plano é obrigatório.',
            'plan.in' => 'O plano deve ser básico ou WhatsApp.',
        ]);

        DB::beginTransaction();
        
        try {
            // Gerar slug único baseado no nome
            $baseSlug = Str::slug($validated['name']);
            $slug = $baseSlug;
            $counter = 1;
            
            while (Client::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            // Criar cliente SaaS
            $client = Client::create([
                'name' => $validated['name'],
                'slug' => $slug,
                'plan' => $validated['plan'],
                'whatsapp_phone' => $validated['whatsapp_phone'] ?? null,
                'active' => true,
                'deploy_status' => 'pending',
            ]);

            // Criar usuário associado ao cliente
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'client_id' => $client->id,
                'email_verified_at' => now(), // Auto-verificar para clientes SaaS
            ]);

            Log::info('Cliente SaaS criado com sucesso', [
                'client_id' => $client->id,
                'client_name' => $client->name,
                'user_id' => $user->id,
                'plan' => $client->plan,
            ]);

            DB::commit();

            return redirect()->route('dashboard.saas-clients.index')
                ->with('success', 'Cliente SaaS cadastrado com sucesso! O token de API foi gerado automaticamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erro ao cadastrar cliente SaaS', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao cadastrar cliente. Por favor, tente novamente.');
        }
    }

    /**
     * Lista todos os clientes SaaS (apenas para master)
     */
    public function index()
    {
        // Verificar se é master
        if (!auth()->check() || !auth()->user()->isMaster()) {
            abort(403, 'Acesso negado. Apenas o gestor geral pode acessar esta página.');
        }

        $clients = Client::with(['users', 'activeApiToken'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('dashboard.saas-clients.index', compact('clients'));
    }

    /**
     * Exibe detalhes de um cliente SaaS (apenas para master)
     */
    public function show(Client $saasClient)
    {
        // Verificar se é master
        if (!auth()->check() || !auth()->user()->isMaster()) {
            abort(403, 'Acesso negado. Apenas o gestor geral pode acessar esta página.');
        }

        $saasClient->load(['users', 'apiTokens', 'instance']);
        
        return view('dashboard.saas-clients.show', compact('saasClient'));
    }
}

