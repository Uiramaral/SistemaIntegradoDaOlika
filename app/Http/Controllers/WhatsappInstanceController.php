<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WhatsappInstance;
use App\Models\Customer;
use App\Services\WhatsAppRouter;
use App\Services\AIResponderService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class WhatsappInstanceController extends Controller
{
    /**
     * Ação do Dashboard para iniciar conexão
     */
    public function connect(Request $request, $id)
    {
        $instance = WhatsappInstance::findOrFail($id);
        
        try {
            $result = $instance->connect(); // Manda comando pro Node
            
            if (isset($result['success']) && $result['success']) {
                $instance->update(['status' => 'CONNECTING']);
                
                // Se for requisição AJAX, retornar JSON imediatamente
                // O JavaScript vai fazer polling para buscar o código
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Comando enviado! Aguarde o código de pareamento...'
                    ]);
                }
                
                return back()->with('success', 'Comando enviado! Verifique o código no Railway.');
            }
            
            // Se for requisição AJAX, retornar JSON
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'] ?? 'Erro desconhecido'
                ], 400);
            }
            
            return back()->with('error', 'Erro: ' . ($result['error'] ?? 'Erro desconhecido'));
        } catch (\Exception $e) {
            Log::error('WhatsappInstanceController::connect - Exceção', [
                'instance_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            // Se for requisição AJAX, retornar JSON
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Erro: ' . $e->getMessage());
        }
    }

    /**
     * Ação do Dashboard para desconectar
     */
    public function disconnect(Request $request, $id)
    {
        $instance = WhatsappInstance::findOrFail($id);
        
        try {
            $result = $instance->disconnect(); // Envia comando de reset
            
            if (isset($result['success']) && $result['success']) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Instância desconectada com sucesso!'
                    ]);
                }
                return back()->with('success', 'Instância desconectada com sucesso!');
            }
            
            // Se deu erro
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'] ?? 'Erro desconhecido'
                ], 400);
            }
            return back()->with('error', 'Erro: ' . ($result['error'] ?? 'Erro desconhecido'));
            
        } catch (\Exception $e) {
            Log::error('WhatsappInstanceController::disconnect - Exceção', [
                'instance_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Erro: ' . $e->getMessage());
        }
    }

    /**
     * Webhook Centralizado (Recebe de todas as instâncias)
     */
    public function handleWebhook(Request $request)
    {
        // Validação básica do token de segurança (Se você não tem um Middleware)
        // if ($request->header('X-Olika-Secret') !== env('WEBHOOK_SECRET')) {
        //     return response()->json(['error' => 'Acesso não autorizado ao webhook'], 401);
        // }

        try {
            $type = $request->input('type', 'messages_upsert'); // Padrão: mensagem
            $instancePhone = $request->input('instance_phone');

            if (!$instancePhone) {
                 // Fallback para lógica antiga se não vier type/instance_phone (compatibilidade)
                 // Assumindo que pode ser uma mensagem direta sem estrutura nova
                 // Mas a lógica abaixo já trata customerPhone e message
            }

            // Busca instância se tiver telefone
            $instance = null;
            if ($instancePhone) {
                $instance = WhatsappInstance::where('phone_number', $instancePhone)->first();
            }

            if ($type === 'connection_update') {
                if (!$instance) {
                     Log::warning("Webhook de conexão recebido de instância desconhecida: {$instancePhone}");
                     return response()->json(['error' => 'Instância desconhecida'], 404);
                }

                // LÓGICA 1: ATUALIZAÇÃO DE STATUS (Conectado/Desconectado)
                $status = $request->input('status');
                
                // Se o status for STANDBY, ele virá como DISCONNECTED, mas o STANDBY_ALERT
                // será tratado na próxima condição.
                // Limpa mensagem de erro se conectar com sucesso
                if ($status === 'CONNECTED') {
                    $instance->update([
                        'status' => $status,
                        'last_error_message' => null
                    ]);
                } else {
                    $instance->update(['status' => $status]); 
                }
                
                return response()->json(['ack' => true]);

            } elseif ($type === 'shutdown_alert') {
                if (!$instance) {
                     Log::warning("Webhook de alerta recebido de instância desconhecida: {$instancePhone}");
                     return response()->json(['error' => 'Instância desconhecida'], 404);
                }

                // 🚨 LÓGICA 2: ALERTA DE FALHA PERSISTENTE
                $reason = $request->input('reason', 'Falha desconhecida');

                Log::error("ALERTA CRÍTICO: Instância {$instancePhone} entrou em STANDBY por falha: {$reason}");
                
                // Traduzir mensagens de erro para português e torná-las mais amigáveis
                $errorMessages = [
                    'PERSISTENT_FAILURE' => 'Conexão instável / desconectada. Refaça o login do seu número de WhatsApp clicando em "Conectar".',
                    'TIMEOUT' => 'A conexão com o WhatsApp expirou. Verifique sua conexão com a internet e tente novamente.',
                    'CONNECTION_ERROR' => 'Erro ao conectar com o WhatsApp. Verifique se o serviço está online e tente novamente.',
                    'AUTHENTICATION_FAILED' => 'Falha na autenticação do WhatsApp. É necessário reconectar o número.',
                    'SESSION_EXPIRED' => 'A sessão do WhatsApp expirou. Clique em "Conectar" para criar uma nova sessão.',
                    'QR_CODE_EXPIRED' => 'O código QR expirou. Clique em "Conectar" para gerar um novo código.',
                ];
                
                $friendlyMessage = $errorMessages[$reason] ?? "Erro na conexão do WhatsApp: {$reason}. Clique em 'Conectar' para tentar reconectar.";
                
                $instance->update([
                    'status' => 'DISCONNECTED', // Atualiza para o estado de exibição
                    'last_error_message' => $friendlyMessage,
                    // 'phone_number' => null // Opcional: Manter o número para facilitar reconexão
                ]);
                
                return response()->json(['ack' => true]);

            } elseif ($type === 'messages_upsert' || !$type) {
                // LÓGICA 3: MENSAGEM RECEBIDA (Fluxo da IA)
                
                // Compatibilidade com payload antigo ou novo
                $gatewayPhone = $instancePhone ?? $request->input('instance_phone'); 
                $customerPhone = $request->input('phone');         // Cliente
                $message = $request->input('message');
                $aiDisabled = $request->input('ai_disabled', false);
                $messageType = $request->input('message_type', 'unknown');

                if (!$gatewayPhone || !$customerPhone) {
                    // Log::warning...
                    return response()->json(['status' => 'ok', 'message' => 'Dados incompletos']);
                }
                
                // 🚨 NOVA LÓGICA: Transferência Humana para Imagens/Vídeos
                if ($aiDisabled && in_array($messageType, ['imageMessage', 'videoMessage'])) {
                    $this->handleImageVideoTransfer($customerPhone, $messageType);
                    return response()->json(['status' => 'ok', 'message' => 'Transferência humana acionada']);
                }
                
                // Se não tiver mensagem de texto, não processar
                if (!$message) {
                    return response()->json(['status' => 'ok', 'message' => 'Mensagem sem texto']);
                }
                
                // 1. Identificar/Criar Cliente e fixar preferência (Sticky Session)
                $customer = Customer::firstOrCreate(
                    ['phone' => $customerPhone],
                    ['name' => 'Cliente', 'is_active' => true]
                );
    
                if ($customer->preferred_gateway_phone !== $gatewayPhone) {
                    $customer->update(['preferred_gateway_phone' => $gatewayPhone]);
                }
    
                // 2. Identificar qual Instância está processando
                // Se já buscamos $instance lá em cima, usamos. Se não, buscamos agora.
                if (!$instance) {
                     $instance = WhatsappInstance::where('phone_number', $gatewayPhone)->first();
                }
                
                if (!$instance) {
                    Log::warning('WhatsappInstanceController::handleWebhook - Instância não encontrada', [
                        'gateway_phone' => $gatewayPhone
                    ]);
                    return response()->json(['status' => 'ok', 'message' => 'Instância não encontrada']);
                }
    
                // 3. Definir Prompt da IA baseado na Instância
                $systemPrompt = "Você é um assistente.";
                
                if (stripos($instance->name, 'principal') !== false) {
                    $systemPrompt = "Você é o Oli, atendente da Olika Pizza. Ajude com pedidos e status. Seja caloroso.";
                } else {
                    $systemPrompt = "Você é o assistente de novidades da Olika. Fale sobre promoções e tire dúvidas.";
                }
    
                // 4. Chamar IA
                $ai = new AIResponderService();
                
                if (!$ai->isEnabled()) {
                    Log::info('WhatsappInstanceController::handleWebhook - IA desabilitada');
                    return response()->json(['status' => 'ok', 'message' => 'IA desabilitada']);
                }
    
                // Construir contexto do cliente
                $context = $ai->buildContextForPhone($customerPhone);
                
                // Chamar IA com prompt customizado baseado na instância
                $reply = $ai->reply($message, $context, $systemPrompt);
                
                if (!$reply) {
                    Log::warning('WhatsappInstanceController::handleWebhook - Resposta vazia da IA');
                    return response()->json(['status' => 'ok', 'message' => 'Resposta vazia']);
                }
    
                // 5. Responder (pelo mesmo canal que entrou)
                $result = $instance->sendMessage($customerPhone, $reply);
                
                if (isset($result['success']) && $result['success']) {
                    Log::info('WhatsappInstanceController::handleWebhook - Mensagem enviada', [
                        'instance_id' => $instance->id,
                        'customer_phone' => $customerPhone
                    ]);
                } else {
                    Log::error('WhatsappInstanceController::handleWebhook - Erro ao enviar', [
                        'instance_id' => $instance->id,
                        'customer_phone' => $customerPhone,
                        'error' => $result['error'] ?? 'Erro desconhecido'
                    ]);
                }
    
                return response()->json(['status' => 'ok']);
            }

            return response()->json(['status' => 'ignored']);
            
        } catch (\Exception $e) {
            Log::error('WhatsappInstanceController::handleWebhook - Exceção', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Trata transferência humana quando recebe imagem ou vídeo
     * Cria uma exceção temporária de 5 minutos para desabilitar IA
     * 
     * @param string $customerPhone Número do cliente (apenas dígitos)
     * @param string $messageType Tipo da mensagem ('imageMessage' ou 'videoMessage')
     */
    private function handleImageVideoTransfer(string $customerPhone, string $messageType): void
    {
        try {
            // Limpar número (apenas dígitos)
            $phoneDigits = preg_replace('/\D/', '', $customerPhone);
            
            if (empty($phoneDigits)) {
                Log::warning('WhatsappInstanceController::handleImageVideoTransfer - Número inválido', [
                    'customer_phone' => $customerPhone
                ]);
                return;
            }
            
            // Verificar se a tabela existe
            if (!DB::getSchemaBuilder()->hasTable('ai_exceptions')) {
                Log::warning('WhatsappInstanceController::handleImageVideoTransfer - Tabela ai_exceptions não existe');
                return;
            }
            
            // Determinar motivo baseado no tipo
            $reason = $messageType === 'imageMessage' ? 'image_received' : 'video_received';
            
            // Criar ou atualizar exceção com expiração de 5 minutos
            $expiresAt = now()->addMinutes(5);
            
            // Verificar se já existe exceção ativa para este número
            $existing = DB::table('ai_exceptions')
                ->where('phone', $phoneDigits)
                ->where('active', true)
                ->first();
            
            if ($existing) {
                // Atualizar exceção existente
                DB::table('ai_exceptions')
                    ->where('id', $existing->id)
                    ->update([
                        'reason' => $reason,
                        'expires_at' => $expiresAt,
                        'updated_at' => now()
                    ]);
            } else {
                // Criar nova exceção
                DB::table('ai_exceptions')->insert([
                    'phone' => $phoneDigits,
                    'reason' => $reason,
                    'active' => true,
                    'expires_at' => $expiresAt,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            Log::info('WhatsappInstanceController::handleImageVideoTransfer - Exceção criada', [
                'phone' => $phoneDigits,
                'reason' => $reason,
                'expires_at' => $expiresAt->toDateTimeString()
            ]);
            
            // Opcional: Notificar admin sobre transferência humana
            $this->notifyAdminAboutTransfer($phoneDigits, $messageType);
            
        } catch (\Exception $e) {
            Log::error('WhatsappInstanceController::handleImageVideoTransfer - Erro', [
                'error' => $e->getMessage(),
                'customer_phone' => $customerPhone,
                'message_type' => $messageType
            ]);
        }
    }

    /**
     * Notifica admin sobre transferência humana (opcional)
     * 
     * @param string $phoneDigits Número do cliente
     * @param string $messageType Tipo da mensagem
     */
    private function notifyAdminAboutTransfer(string $phoneDigits, string $messageType): void
    {
        try {
            // Buscar telefone do admin nas configurações
            $adminPhone = DB::table('whatsapp_settings')
                ->where('active', 1)
                ->value('admin_notification_phone');
            
            if (!$adminPhone) {
                return; // Sem admin configurado, não notificar
            }
            
            $messageTypeLabel = $messageType === 'imageMessage' ? 'imagem' : 'vídeo';
            $message = "📸 Transferência Humana Acionada\n\n" .
                      "Cliente: +{$phoneDigits}\n" .
                      "Motivo: Enviou {$messageTypeLabel}\n" .
                      "IA desabilitada por 5 minutos para atendimento manual.";
            
            // Enviar notificação via WhatsApp (se tiver instância ativa)
            $instance = WhatsappInstance::where('status', 'CONNECTED')->first();
            if ($instance) {
                $instance->sendMessage($adminPhone, $message);
            }
            
        } catch (\Exception $e) {
            Log::error('WhatsappInstanceController::notifyAdminAboutTransfer - Erro', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Lista todas as instâncias
     */
    public function index()
    {
        $instances = WhatsappInstance::orderBy('name')->get();
        return response()->json($instances);
    }

    /**
     * Mostra uma instância específica com status atualizado
     */
    public function show($id)
    {
        $instance = WhatsappInstance::findOrFail($id);
        
        // Buscar status atualizado do Node.js
        $status = $instance->getStatus();
        
        // Combinar dados da instância com status
        $data = $instance->toArray();
        $data['status_info'] = $status;
        
        return response()->json($data);
    }

    /**
     * Cria uma nova instância
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'api_url' => 'required|url',
            'api_token' => 'nullable|string|max:255',
        ]);

        $instance = WhatsappInstance::create($validated);
        
        return response()->json($instance, 201);
    }

    /**
     * Atualiza uma instância
     */
    public function update(Request $request, $id)
    {
        $instance = WhatsappInstance::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'api_url' => 'sometimes|url',
            'api_token' => 'nullable|string|max:255',
            'status' => 'sometimes|in:DISCONNECTED,CONNECTING,CONNECTED',
        ]);

        $instance->update($validated);
        
        return response()->json($instance);
    }
}
