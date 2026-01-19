<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\WhatsappInstanceUrl;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WhatsappInstanceUrlsController extends Controller
{
    /**
     * Lista todas as URLs de instâncias
     */
    public function index(Request $request)
    {
        $query = WhatsappInstanceUrl::with('client');

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('health')) {
            $query->where('health_status', $request->health);
        }

        $instances = $query->orderBy('name')->get();

        // Fetch clients that don't have WhatsApp instances yet
        $clients = Client::whereDoesntHave('whatsappInstanceUrls')
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return view('master.whatsapp-urls.index', compact('instances', 'clients'));
    }

    /**
     * Formulário de criação
     */
    public function create()
    {
        return view('master.whatsapp-urls.form');
    }

    /**
     * Extrai nome da instância a partir da URL
     */
    protected function extractNameFromUrl(string $url): string
    {
        // Ex: https://olika-whatsapp-01.up.railway.app -> olika-whatsapp-01
        $parsed = parse_url($url);
        $host = $parsed['host'] ?? '';
        
        // Remover sufixos comuns
        $name = preg_replace('/\.(up\.railway\.app|railway\.app|herokuapp\.com|render\.com)$/i', '', $host);
        
        // Se ainda for um domínio completo, pegar só a primeira parte
        if (strpos($name, '.') !== false) {
            $parts = explode('.', $name);
            $name = $parts[0];
        }
        
        return $name ?: 'whatsapp-' . uniqid();
    }

    /**
     * Busca o token de API único do sistema
     */
    protected function getSystemApiToken(): ?string
    {
        // Buscar de whatsapp_settings (token global)
        $whatsappSettings = DB::table('whatsapp_settings')->where('active', 1)->first();
        if ($whatsappSettings && !empty($whatsappSettings->api_key)) {
            return $whatsappSettings->api_key;
        }
        
        // Fallback: buscar de payment_settings
        $token = DB::table('payment_settings')->where('key', 'webhook_token')->value('value');
        if ($token) {
            return $token;
        }
        
        return null;
    }

    /**
     * Salva nova(s) URL(s) de instância(s)
     * Aceita uma URL ou múltiplas URLs separadas por quebra de linha
     */
    public function store(Request $request)
    {
        // Aceitar tanto 'url' (edição) quanto 'urls' (criação múltipla)
        $validated = $request->validate([
            'url' => 'nullable|url',
            'urls' => 'nullable|string',
            'railway_service_id' => 'nullable|string|max:255',
            'railway_project_id' => 'nullable|string|max:255',
            'max_connections' => 'nullable|integer|min:1|max:100',
            'notes' => 'nullable|string',
        ]);

        // Se veio 'url' (edição), converter para array
        if (!empty($validated['url'])) {
            $urlsText = $validated['url'];
        } elseif (!empty($validated['urls'])) {
            $urlsText = $validated['urls'];
        } else {
            return back()->withErrors(['urls' => 'É necessário informar pelo menos uma URL.'])->withInput();
        }

        // Processar múltiplas URLs (separadas por quebra de linha, vírgula ou espaço)
        $urlsText = trim($urlsText);
        
        // Separar por quebra de linha, vírgula ou espaço
        $urls = preg_split('/[\r\n,]+/', $urlsText);
        $urls = array_map('trim', $urls);
        $urls = array_filter($urls, function($url) {
            return !empty($url);
        });

        // Corrigir e validar URLs
        $validUrls = [];
        $invalidUrls = [];
        
        foreach ($urls as $url) {
            $url = trim($url);
            if (empty($url)) {
                continue;
            }
            
            // Remover espaços e caracteres especiais no início/fim
            $url = trim($url, " \t\n\r\0\x0B/");
            
            // Se não tem protocolo, adicionar https://
            if (!preg_match('/^https?:\/\//i', $url)) {
                $url = 'https://' . $url;
            }
            
            // Garantir que termina sem barra
            $url = rtrim($url, '/');
            
            // Validar URL
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $validUrls[] = $url;
            } else {
                $invalidUrls[] = $url;
            }
        }

        if (empty($validUrls)) {
            $errorMessage = 'Nenhuma URL válida encontrada.';
            if (!empty($invalidUrls)) {
                $errorMessage .= ' URLs inválidas: ' . implode(', ', array_slice($invalidUrls, 0, 3));
            }
            return back()->withErrors(['urls' => $errorMessage])->withInput();
        }
        
        // Usar URLs válidas
        $urls = $validUrls;

        // Buscar API Key do sistema
        $apiKey = $this->getSystemApiToken();

        $created = [];
        $errors = [];
        $skipped = [];

        foreach ($urls as $url) {
            $url = rtrim($url, '/');
            
            // Verificar se já existe
            if (WhatsappInstanceUrl::where('url', $url)->exists()) {
                $skipped[] = $url;
                continue;
            }

            try {
                // Extrair nome da URL automaticamente
                $name = $this->extractNameFromUrl($url);

                $instanceUrl = WhatsappInstanceUrl::create([
                    'name' => $name,
                    'url' => $url,
                    'api_key' => $apiKey,
                    'railway_service_id' => $validated['railway_service_id'] ?? null,
                    'railway_project_id' => $validated['railway_project_id'] ?? null,
                    'max_connections' => $validated['max_connections'] ?? 5,
                    'status' => 'available',
                    'health_status' => 'unknown',
                ]);

                // Verificar saúde da instância (em background, não bloquear)
                try {
                    $this->checkHealth($instanceUrl);
                } catch (\Exception $e) {
                    // Log mas não falhar
                    \Log::warning("Erro ao verificar saúde da instância {$instanceUrl->id}: " . $e->getMessage());
                }

                $created[] = $instanceUrl->name;
            } catch (\Exception $e) {
                $errors[] = "Erro ao criar {$url}: " . $e->getMessage();
                \Log::error("Erro ao criar instância WhatsApp", [
                    'url' => $url,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Montar mensagem de retorno
        $messages = [];
        
        if (!empty($created)) {
            $count = count($created);
            $messages[] = "{$count} instância(s) criada(s) com sucesso: " . implode(', ', $created);
        }
        
        if (!empty($skipped)) {
            $count = count($skipped);
            $messages[] = "{$count} URL(s) ignorada(s) (já existem): " . implode(', ', array_slice($skipped, 0, 3)) . (count($skipped) > 3 ? '...' : '');
        }
        
        if (!empty($errors)) {
            $messages[] = "Erros: " . implode('; ', array_slice($errors, 0, 3)) . (count($errors) > 3 ? '...' : '');
        }

        $messageType = !empty($errors) ? 'warning' : 'success';
        
        return redirect()->route('master.whatsapp-urls.index')
            ->with($messageType, implode(' | ', $messages));
    }

    /**
     * Formulário de edição
     */
    public function edit(WhatsappInstanceUrl $whatsappUrl)
    {
        return view('master.whatsapp-urls.form', ['instance' => $whatsappUrl]);
    }

    /**
     * Atualiza URL
     */
    public function update(Request $request, WhatsappInstanceUrl $whatsappUrl)
    {
        $validated = $request->validate([
            'url' => "required|url|unique:whatsapp_instance_urls,url,{$whatsappUrl->id}",
            'railway_service_id' => 'nullable|string|max:255',
            'railway_project_id' => 'nullable|string|max:255',
            'max_connections' => 'integer|min:1|max:100',
            'status' => 'in:available,assigned,maintenance,offline',
            'notes' => 'nullable|string',
        ]);

        // Extrair nome da URL se a URL mudou
        $name = $whatsappUrl->name;
        if ($validated['url'] !== $whatsappUrl->url) {
            $name = $this->extractNameFromUrl($validated['url']);
        }

        $whatsappUrl->update([
            'name' => $name,
            'url' => rtrim($validated['url'], '/'),
            'railway_service_id' => $validated['railway_service_id'] ?? null,
            'railway_project_id' => $validated['railway_project_id'] ?? null,
            'max_connections' => $validated['max_connections'] ?? 5,
            'status' => $validated['status'] ?? $whatsappUrl->status,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('master.whatsapp-urls.index')
            ->with('success', "URL {$whatsappUrl->name} atualizada com sucesso!");
    }

    /**
     * Verifica saúde de uma instância (AJAX)
     */
    public function healthCheck(Request $request, WhatsappInstanceUrl $whatsappUrl)
    {
        $result = $this->checkHealth($whatsappUrl);
        
        // Se for requisição AJAX, retornar JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'healthy' => $result,
                'status' => $result ? 'healthy' : 'unhealthy',
                'message' => $result ? 'Instância saudável' : 'Instância com problemas',
                'name' => $whatsappUrl->name,
                'url' => $whatsappUrl->url,
                'last_check' => now()->format('d/m/Y H:i:s'),
            ]);
        }

        return back()->with('success', "Health check realizado para {$whatsappUrl->name}.");
    }

    /**
     * Verifica saúde de todas as instâncias
     */
    public function healthCheckAll()
    {
        $instances = WhatsappInstanceUrl::all();
        $results = ['healthy' => 0, 'unhealthy' => 0];

        foreach ($instances as $instance) {
            $isHealthy = $this->checkHealth($instance);
            $results[$isHealthy ? 'healthy' : 'unhealthy']++;
        }

        return back()->with('success', "Health check concluído: {$results['healthy']} saudáveis, {$results['unhealthy']} com problemas.");
    }

    /**
     * Executa health check na instância
     * Usa o endpoint root (/) do projeto Node.js customizado
     */
    protected function checkHealth(WhatsappInstanceUrl $instance): bool
    {
        try {
            $client = new \GuzzleHttp\Client(['timeout' => 10, 'http_errors' => false]);
            
            // Usar endpoint root que retorna status do Node.js customizado
            $response = $client->get($instance->url . '/');

            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody()->getContents(), true);
                
                // Verificar se está rodando (pode ou não estar conectado ao WhatsApp)
                if (isset($data['status']) && $data['status'] === 'running') {
                    $instance->updateHealthStatus('healthy');
                    return true;
                }
            }
        } catch (\Exception $e) {
            \Log::warning("Health check falhou para {$instance->name}: " . $e->getMessage());
        }

        $instance->updateHealthStatus('unhealthy');
        return false;
    }

    /**
     * Atribui instância a um cliente
     */
    public function assign(Request $request, WhatsappInstanceUrl $whatsappUrl)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
        ]);

        if (!$whatsappUrl->isAvailable()) {
            return back()->with('error', 'Esta instância não está disponível.');
        }

        $client = Client::find($request->client_id);
        $whatsappUrl->assignToClient($client);

        return back()->with('success', "Instância atribuída ao cliente {$client->name}.");
    }

    /**
     * Libera instância
     */
    public function release(WhatsappInstanceUrl $whatsappUrl)
    {
        $whatsappUrl->release();
        return back()->with('success', 'Instância liberada com sucesso.');
    }

    /**
     * Coloca em manutenção
     */
    public function maintenance(WhatsappInstanceUrl $whatsappUrl)
    {
        $whatsappUrl->update(['status' => 'maintenance']);
        return back()->with('success', 'Instância marcada para manutenção.');
    }

    /**
     * Exclui URL
     */
    public function destroy(WhatsappInstanceUrl $whatsappUrl)
    {
        if ($whatsappUrl->isAssigned()) {
            return back()->with('error', 'Não é possível excluir uma instância em uso.');
        }

        $name = $whatsappUrl->name;
        $whatsappUrl->delete();

        return redirect()->route('master.whatsapp-urls.index')
            ->with('success', "URL {$name} excluída com sucesso!");
    }
}
