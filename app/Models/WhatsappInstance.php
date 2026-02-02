<?php

namespace App\Models;

use App\Models\Traits\BelongsToClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappInstance extends Model
{
    use BelongsToClient;

    protected $fillable = [
        'client_id',
        'name',
        'phone_number',
        'api_url',
        'api_token',
        'status',
        'last_error_message',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Conecta a instância (Envia o número para o Node configurar)
     */
    public function connect($retry = true)
    {
        // Garantir URL limpa (sem espaços e sem barra no final)
        $url = rtrim(trim($this->api_url), '/');

        // Normalizar número de telefone antes de enviar
        $normalizedPhone = $this->normalizePhoneNumber($this->phone_number);

        Log::info("WhatsappInstance::connect - Iniciando conexão", [
            'instance_id' => $this->id,
            'target_url' => "{$url}/api/whatsapp/connect",
            'phone_original' => $this->phone_number,
            'phone_normalized' => $normalizedPhone,
        ]);

        try {
            // allow_redirects: false -> Para detectar se o servidor está redirecionando (causa comum do POST virar GET)
            $response = Http::withHeaders($this->getHeaders())
                ->withOptions(['allow_redirects' => false])
                ->timeout(30)
                ->post("{$url}/api/whatsapp/connect", [
                    'phone' => $normalizedPhone
                ]);

            // Se for redirecionamento (3xx), logar aviso
            if ($response->status() >= 300 && $response->status() < 400) {
                Log::error('WhatsappInstance::connect - Redirecionamento detectado!', [
                    'instance_id' => $this->id,
                    'status' => $response->status(),
                    'location' => $response->header('Location'),
                    'msg' => 'O servidor respondeu com redirecionamento. Isso transforma POST em GET. Verifique a URL (http vs https, www, etc).'
                ]);
                return ['success' => false, 'error' => 'Erro: URL redirecionando (verifique http/https).'];
            }

            if ($response->successful()) {
                $this->update(['status' => 'CONNECTING']);
                return $response->json();
            }

            Log::error('WhatsappInstance::connect - Erro na resposta', [
                'instance_id' => $this->id,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            // Tentar recuperar de estado travado (429 ou Conexão em andamento)
            if ($retry && ($response->status() === 429 || str_contains($response->body(), 'Conexão já em andamento'))) {
                Log::warning('WhatsappInstance::connect - Estado travado detectado. Tentando resetar e reconectar...', [
                    'instance_id' => $this->id
                ]);

                // Forçar desconexão para limpar o estado no Node.js
                $this->disconnect();

                // Aguardar 2 segundos para o Node processar o reset
                sleep(2);

                // Tentar conectar novamente (sem retry recursivo para evitar loop infinito)
                return $this->connect(false);
            }

            return ['success' => false, 'error' => 'Erro ao conectar instância: ' . $response->status()];
        } catch (\Exception $e) {
            Log::error('WhatsappInstance::connect - Exceção', [
                'instance_id' => $this->id,
                'error' => $e->getMessage()
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Desconecta a instância (Envia comando de reset para o Node)
     */
    public function disconnect()
    {
        // Garantir URL limpa (sem espaços e sem barra no final)
        $url = rtrim(trim($this->api_url), '/');

        Log::info("WhatsappInstance::disconnect - Iniciando desconexão", [
            'instance_id' => $this->id,
            'target_url' => "{$url}/api/whatsapp/restart"
        ]);

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->post("{$url}/api/whatsapp/restart"); // Chama o Botão de Pânico

            if ($response->successful()) {
                $this->update([
                    'status' => 'DISCONNECTED',
                    'last_error_message' => null // Limpa erros antigos ao desconectar intencionalmente
                ]);
                return ['success' => true, 'message' => 'Instância desconectada e resetada.'];
            }

            Log::error('WhatsappInstance::disconnect - Erro na resposta', [
                'instance_id' => $this->id,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            // Mesmo com erro no Node, marcamos como desconectado no banco para não travar a UI
            $this->update(['status' => 'DISCONNECTED']);

            return ['success' => false, 'error' => 'Erro ao desconectar no servidor: ' . $response->status()];
        } catch (\Exception $e) {
            Log::error('WhatsappInstance::disconnect - Exceção', [
                'instance_id' => $this->id,
                'error' => $e->getMessage()
            ]);

            // Fallback: Se deu erro de conexão, assume que caiu e marca desconectado
            $this->update(['status' => 'DISCONNECTED']);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Busca status da instância (incluindo código de pareamento)
     */
    public function getStatus()
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(10)
                ->get("{$this->api_url}/api/whatsapp/status");

            if ($response->successful()) {
                $status = $response->json();

                // Atualizar status no banco baseado na resposta
                if (isset($status['connected']) && $status['connected']) {
                    $this->update(['status' => 'CONNECTED']);
                } elseif (isset($status['pairingCode']) && $status['pairingCode']) {
                    $this->update(['status' => 'CONNECTING']);
                } else {
                    $this->update(['status' => 'DISCONNECTED']);
                }

                return $status;
            }

            return ['connected' => false, 'error' => 'Erro ao buscar status'];
        } catch (\Exception $e) {
            Log::error('WhatsappInstance::getStatus - Exceção', [
                'instance_id' => $this->id,
                'error' => $e->getMessage()
            ]);

            return ['connected' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Envia mensagem via esta instância
     */
    public function sendMessage($to, $message)
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->post("{$this->api_url}/api/whatsapp/send", [
                    'number' => $to,
                    'message' => $message
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('WhatsappInstance::sendMessage - Erro na resposta', [
                'instance_id' => $this->id,
                'to' => $to,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return ['success' => false, 'error' => 'Erro ao enviar mensagem'];
        } catch (\Exception $e) {
            Log::error('WhatsappInstance::sendMessage - Exceção', [
                'instance_id' => $this->id,
                'to' => $to,
                'error' => $e->getMessage()
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Retorna headers para requisições HTTP
     */
    private function getHeaders()
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];

        if ($this->api_token) {
            $headers['X-Olika-Token'] = $this->api_token;
        } elseif (env('API_SECRET')) {
            $headers['X-Olika-Token'] = env('API_SECRET');
        }

        return $headers;
    }

    /**
     * Scope para instâncias conectadas
     */
    public function scopeConnected($query)
    {
        return $query->where('status', 'CONNECTED');
    }

    /**
     * Scope para instâncias por nome
     */
    public function scopeByName($query, $name)
    {
        return $query->where('name', $name);
    }

    /**
     * Normaliza número de telefone para formato internacional (Brasil: 55)
     * 
     * @param string|null $phone Número de telefone (apenas dígitos)
     * @return string Número normalizado com código do país
     */
    private function normalizePhoneNumber(?string $phone): string
    {
        if (!$phone) {
            return '';
        }

        // Remover caracteres não numéricos
        $phone = preg_replace('/\D/', '', $phone);

        // Se já começa com 55 (código do Brasil), retornar como está
        if (str_starts_with($phone, '55')) {
            return $phone;
        }

        // Se tem 10 ou 11 dígitos (DDD + número), adicionar código do país
        if (strlen($phone) >= 10 && strlen($phone) <= 11) {
            return '55' . $phone;
        }

        // Se tem menos de 10 dígitos, assumir que está incompleto e adicionar 55
        if (strlen($phone) < 10) {
            return '55' . $phone;
        }

        // Caso tenha mais de 13 dígitos (55 + 11), já está normalizado
        return $phone;
    }
}

