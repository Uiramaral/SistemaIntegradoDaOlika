<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Lista de slugs proibidos (subdomínios reservados)
     */
    protected const PROHIBITED_SLUGS = [
        'www',
        'dashboard',
        'pedido',
        'admin',
        'api',
        'suporte',
        'mail',
        'smtp',
        'ftp',
        'webmail',
        'cpanel',
        'teste',
        'test',
        'dev',
        'staging',
        'beta',
        'alpha',
        'demo',
        'app',
        'blog',
        'forum',
        'help',
        'status',
        'sistema',
        'painel',
    ];

    /**
     * Exibe a página de perfil do usuário
     */
    public function index()
    {
        $user = Auth::user();
        return view('profile.index', compact('user'));
    }

    /**
     * Atualiza o slug (URL personalizada) do usuário
     */
    /**
     * Atualiza o slug (URL personalizada) do estabelecimento (Client)
     */
    public function updateSlug(Request $request)
    {
        $user = Auth::user();
        $client = $user->client;

        if (!$client) {
            return back()->with('error', 'Usuário não está vinculado a um estabelecimento.');
        }

        // Validação
        $validated = $request->validate([
            'slug' => [
                'required',
                'alpha_dash', // Apenas letras, números, hifens e underscores
                'min:3',
                'max:30',
                'lowercase', // Força minúsculas
                Rule::unique('clients', 'slug')->ignore($client->id), // Único na tabela clients, ignorando o próprio ID
                function ($attribute, $value, $fail) {
                    // Valida se não é um slug proibido
                    if (in_array(strtolower($value), self::PROHIBITED_SLUGS)) {
                        $fail('Este nome de URL é reservado pelo sistema e não pode ser usado.');
                    }

                    // Valida se não começa com número
                    if (is_numeric($value[0])) {
                        $fail('O nome da URL não pode começar com número.');
                    }

                    // Valida se não tem apenas números
                    if (is_numeric($value)) {
                        $fail('O nome da URL não pode ser formado apenas por números.');
                    }
                },
            ],
        ], [
            'slug.required' => 'O nome da URL é obrigatório.',
            'slug.alpha_dash' => 'O nome da URL pode conter apenas letras, números e hifens.',
            'slug.min' => 'O nome da URL deve ter no mínimo 3 caracteres.',
            'slug.max' => 'O nome da URL deve ter no máximo 30 caracteres.',
            'slug.lowercase' => 'O nome da URL deve conter apenas letras minúsculas.',
            'slug.unique' => 'Este nome de URL já está sendo usado por outro estabelecimento.',
        ]);

        // Normaliza o slug (força minúsculas)
        $slug = strtolower($validated['slug']);

        // Salva o slug antigo para mensagem
        $oldSlug = $client->slug;

        // Atualiza o cliente (loja)
        $client->update(['slug' => $slug]);

        // Monta a URL completa
        $newUrl = $slug . '.cozinhapro.app.br';

        // Mensagem de sucesso
        $message = 'Sua URL foi atualizada com sucesso! ';
        $message .= 'Seu novo link é: <strong>' . $newUrl . '</strong>';

        if ($oldSlug && $oldSlug !== $slug) {
            $message .= '<br><small class="text-warning">⚠️ Atenção: Ao mudar sua URL, links antigos enviados no WhatsApp de clientes não funcionarão mais.</small>';
        }

        return back()->with('success', $message);
    }

    /**
     * Atualiza as informações gerais do perfil
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            // Adicionar validação opcional para o nome da loja se vier no request
            'store_name' => 'nullable|string|max:255',
        ]);

        // Atualiza usuário
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        // Se tiver nome da loja e usuário tem client, atualiza também
        if ($request->has('store_name') && $user->client) {
            $user->client->update(['name' => $request->input('store_name')]);
        }

        return back()->with('success', 'Perfil atualizado com sucesso!');
    }

    /**
     * Verifica se um slug está disponível (AJAX)
     */
    public function checkSlugAvailability(Request $request)
    {
        $slug = strtolower($request->input('slug'));
        $user = Auth::user();

        // Se usuário não tem client, não pode verificar disponibilidade de slug de loja
        if (!$user->client) {
            return response()->json([
                'available' => false,
                'message' => 'Usuário sem vínculo com estabelecimento.'
            ]);
        }

        $clientId = $user->client->id;

        // Verifica se é proibido
        if (in_array($slug, self::PROHIBITED_SLUGS)) {
            return response()->json([
                'available' => false,
                'message' => 'Este nome é reservado pelo sistema.'
            ]);
        }

        // Verifica se já existe na tabela CLIENTS
        $exists = \App\Models\Client::where('slug', $slug)
            ->where('id', '!=', $clientId)
            ->exists();

        if ($exists) {
            return response()->json([
                'available' => false,
                'message' => 'Este nome já está sendo usado.'
            ]);
        }

        return response()->json([
            'available' => true,
            'message' => 'Nome disponível!',
            'url' => $slug . '.cozinhapro.app.br'
        ]);
    }
}
