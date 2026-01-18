<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SettingsController extends Controller
{
    public function index()
    {
        // Buscar configurações do WhatsApp
        $whatsappSettings = DB::table('whatsapp_settings')->where('active', 1)->first();
        
        // Buscar configurações de pagamento
        $paymentSettings = DB::table('payment_settings')->pluck('value', 'key');
        
        // Configurações gerais da loja
        $storeSettings = [
            'store_name' => 'Olika Cozinha Artesanal',
            'store_email' => 'olikacozinhaartesanal@gmail.com',
            'store_phone' => '(71) 987019420',
            'store_cnpj' => '50.910.565/0001-84',
            'service_fee' => 0,
            'min_order' => 0,
        ];
        
        // Chaves de API + loja (free shipping) salvas em settings
        // Nota: cashback_percent removido - agora gerenciado na aba dedicada de Cashback
        $apiSettings = $this->getSettingsFromFlexibleTable([
            'openai_api_key','openai_model','google_maps_api_key',
            'free_shipping_min_total',
            'order_number_prefix','next_order_number',
            // BotConversa
            'botconversa_webhook_url','botconversa_paid_webhook_url','botconversa_token',
            // Agendamento/Entrega
            'delivery_slot_capacity','advance_order_days','default_cutoff_time',
        ]);
        
        // Limpar valores inválidos (emails no campo de URL) e usar .env como fallback
        foreach (['botconversa_webhook_url', 'botconversa_paid_webhook_url'] as $key) {
            $value = $apiSettings[$key] ?? '';
            // Se for um email (contém @ mas não é uma URL válida), limpar e usar .env
            if (!empty($value) && strpos($value, '@') !== false && !filter_var($value, FILTER_VALIDATE_URL)) {
                Log::info("SettingsController: Limpando valor inválido (email) do campo {$key}, usando .env");
                $apiSettings[$key] = '';
                // Usar .env como fallback se o valor do banco for inválido
                $envKey = strtoupper($key);
                $envValue = env($envKey) ?: config("services.botconversa." . str_replace('botconversa_', '', $key));
                if ($envValue && filter_var($envValue, FILTER_VALIDATE_URL)) {
                    $apiSettings[$key] = $envValue;
                    Log::info("SettingsController: Usando valor do .env para {$key}");
                }
            }
        }
        // Fallback para .env/config quando não houver no banco
        $envDefaults = [
            'openai_api_key' => config('services.openai.key', env('OPENAI_API_KEY')),
            'openai_model' => config('services.openai.model', env('OPENAI_MODEL', 'gpt-5-nano')),
            'google_maps_api_key' => (config('services.google.maps_key') ?? env('GOOGLE_MAPS_API_KEY')),
            'free_shipping_min_total' => env('FREE_SHIPPING_MIN_TOTAL'),
            // cashback_percent removido - agora gerenciado na aba dedicada de Cashback
            // BotConversa do .env
            'botconversa_webhook_url' => config('services.botconversa.webhook_url'),
            'botconversa_paid_webhook_url' => config('services.botconversa.paid_webhook'),
            'botconversa_token' => config('services.botconversa.token'),
            // Defaults para entrega
            'delivery_slot_capacity' => 2,
            'advance_order_days' => 2,
        ];
        foreach ($envDefaults as $k => $v) {
            // Para BotConversa URLs, só usar .env se o valor do banco estiver vazio ou inválido
            if (in_array($k, ['botconversa_webhook_url', 'botconversa_paid_webhook_url'])) {
                $dbValue = $apiSettings[$k] ?? '';
                // Se o valor do banco é vazio ou inválido (email), usar .env
                if (empty($dbValue) || (strpos($dbValue, '@') !== false && !filter_var($dbValue, FILTER_VALIDATE_URL))) {
                    if ($v !== null && $v !== '') { 
                        $apiSettings[$k] = $v;
                    }
                }
            } else {
                // Para outros campos, comportamento normal
                if (!isset($apiSettings[$k]) || $apiSettings[$k] === null || $apiSettings[$k] === '') {
                    if ($v !== null && $v !== '') { $apiSettings[$k] = $v; }
                }
            }
        }

        return view('dashboard.settings.index', compact('whatsappSettings', 'paymentSettings', 'storeSettings','apiSettings'));
    }

    public function apisSave(Request $r)
    {
        // Log dos dados recebidos ANTES da validação
        Log::info('SettingsController: Dados recebidos do formulário', [
            'all_input' => $r->all(),
            'botconversa_webhook_url_raw' => $r->input('botconversa_webhook_url'),
            'botconversa_paid_webhook_url_raw' => $r->input('botconversa_paid_webhook_url'),
            'botconversa_token_raw' => $r->has('botconversa_token') ? 'present' : 'absent',
        ]);
        
        // Validação adicional: se o campo paid_webhook_url contém um email, rejeitar
        $paidWebhookUrl = $r->input('botconversa_paid_webhook_url');
        if (!empty($paidWebhookUrl) && strpos($paidWebhookUrl, '@') !== false && !filter_var($paidWebhookUrl, FILTER_VALIDATE_URL)) {
            return back()->withErrors([
                'botconversa_paid_webhook_url' => 'Este campo deve ser uma URL válida, não um email.'
            ])->withInput();
        }

        $data = $r->validate([
            'openai_api_key' => 'nullable|string',
            'openai_model' => 'nullable|string',
            'google_maps_api_key' => 'nullable|string',
            'free_shipping_min_total' => 'nullable|numeric|min:0',
            // cashback_percent removido da validação - agora gerenciado na aba dedicada de Cashback
            'order_number_prefix' => 'nullable|string|max:12',
            'next_order_number' => 'nullable|integer|min:1',
            // BotConversa - validar URL apenas se não estiver vazio (removido FILTER_VALIDATE_URL muito restritivo)
            'botconversa_webhook_url' => 'nullable|string|max:500',
            'botconversa_paid_webhook_url' => 'nullable|string|max:500',
            'botconversa_token' => 'nullable|string|max:255',
            // Entrega
            'delivery_slot_capacity' => 'nullable|integer|min:1|max:20',
            'advance_order_days' => 'nullable|integer|min:0|max:30',
            'default_cutoff_time' => 'nullable|date_format:H:i',
        ]);

        // Log dos dados APÓS validação
        Log::info('SettingsController: Dados validados', [
            'botconversa_webhook_url' => $data['botconversa_webhook_url'] ?? 'NOT_SET',
            'botconversa_paid_webhook_url' => $data['botconversa_paid_webhook_url'] ?? 'NOT_SET',
            'botconversa_token' => isset($data['botconversa_token']) ? 'PRESENT' : 'NOT_SET',
            'all_keys' => array_keys($data),
        ]);

        $this->saveSettingsIntoFlexibleTable($data);

        // Verificar se foi salvo corretamente
        $saved = $this->getSettingsFromFlexibleTable(['botconversa_paid_webhook_url']);
        Log::info('SettingsController: Verificação após salvamento', [
            'botconversa_paid_webhook_url_saved' => $saved['botconversa_paid_webhook_url'] ?? 'NOT_FOUND',
        ]);

        Log::info('SettingsController: Configurações salvas com sucesso');

        return back()->with('success', 'Configurações salvas com sucesso.');
    }

    /**
     * Lê múltiplas chaves da tabela payment_settings (tabela chave-valor)
     */
    private function getSettingsFromFlexibleTable(array $keys): array
    {
        // Usar payment_settings como tabela principal para configurações flexíveis
        if (Schema::hasTable('payment_settings')) {
            return DB::table('payment_settings')->whereIn('key', $keys)->pluck('value','key')->toArray();
        }
        
        // Fallback: tentar settings se tiver estrutura chave-valor (improvável)
        if (Schema::hasTable('settings')) {
            $keyCol = collect(['key','name','config_key','setting_key','option','option_name'])
                ->first(fn($c)=>Schema::hasColumn('settings',$c));
            $valCol = collect(['value','val','config_value','content','data','option_value'])
                ->first(fn($c)=>Schema::hasColumn('settings',$c));
            if ($keyCol && $valCol) {
                return DB::table('settings')->whereIn($keyCol, $keys)->pluck($valCol, $keyCol)->toArray();
            }
        }
        return [];
    }

    /**
     * Salva chaves/valores na tabela payment_settings (tabela chave-valor)
     */
    private function saveSettingsIntoFlexibleTable(array $data): void
    {
        Log::info('SettingsController: saveSettingsIntoFlexibleTable - Iniciando salvamento', [
            'keys' => array_keys($data),
            'payment_settings_exists' => Schema::hasTable('payment_settings'),
        ]);

        // Usar payment_settings como tabela principal para configurações flexíveis
        if (Schema::hasTable('payment_settings')) {
            foreach ($data as $k => $v) {
                // Validar URLs - se for um email, limpar o valor
                if (in_array($k, ['botconversa_webhook_url', 'botconversa_paid_webhook_url']) && !empty($v)) {
                    // Se contém @ mas não é uma URL válida, limpar
                    if (strpos($v, '@') !== false && !filter_var($v, FILTER_VALIDATE_URL)) {
                        Log::warning("SettingsController: Valor inválido detectado (email) no campo {$k}, limpando...", ['value' => substr($v, 0, 50)]);
                        $v = '';
                    }
                }
                
                // Manter valor como está (não converter string vazia para null)
                $value = $v;
                
                // Salvar em payment_settings
                $result = DB::table('payment_settings')->updateOrInsert(
                    ['key' => $k],
                    [
                        'value' => $value, 
                        'updated_at' => now(), 
                        'created_at' => DB::raw('COALESCE(created_at, NOW())')
                    ]
                );
                
                // Verificar se foi salvo corretamente
                $saved = DB::table('payment_settings')->where('key', $k)->first();
                $savedValue = $saved ? ($saved->value ?? 'NULL') : 'NOT_FOUND';
                
                Log::info('SettingsController: Salvo em payment_settings', [
                    'key' => $k, 
                    'value_preview' => is_string($value) ? substr($value, 0, 100) : (is_null($value) ? 'null' : gettype($value)),
                    'updateOrInsert_result' => $result,
                    'saved_value_preview' => is_string($savedValue) ? substr($savedValue, 0, 100) : $savedValue,
                    'saved_exists' => $saved ? true : false,
                ]);
            }
            return;
        }
        
        // Fallback: tentar settings se tiver estrutura chave-valor (improvável)
        if (Schema::hasTable('settings')) {
            $keyCol = collect(['key','name','config_key','setting_key','option','option_name'])
                ->first(fn($c)=>Schema::hasColumn('settings',$c));
            $valCol = collect(['value','val','config_value','content','data','option_value'])
                ->first(fn($c)=>Schema::hasColumn('settings',$c));
            if ($keyCol && $valCol) {
                $hasCreated = Schema::hasColumn('settings','created_at');
                $hasUpdated = Schema::hasColumn('settings','updated_at');
                foreach ($data as $k => $v) {
                    $value = $v;
                    $update = [$valCol => $value];
                    if ($hasUpdated) { $update['updated_at'] = now(); }
                    if ($hasCreated) { $update['created_at'] = DB::raw('COALESCE(created_at, NOW())'); }
                    DB::table('settings')->updateOrInsert([$keyCol => $k], $update);
                    Log::info('SettingsController: Salvo em settings (fallback)', ['key' => $k]);
                }
                return;
            }
        }
        
        Log::error('SettingsController: Nenhuma tabela adequada encontrada para salvar configurações');
    }
    public function whatsapp()
    {
        $clientId = currentClientId();
        
        // Buscar instâncias WhatsApp do cliente (nova estrutura multi-instância)
        $instances = DB::table('whatsapp_instances')
            ->where('client_id', $clientId)
            ->orderBy('name')
            ->get();
        
        // Buscar URLs disponíveis para criar novas instâncias
        $availableUrls = DB::table('whatsapp_instance_urls')
            ->where('status', 'available')
            ->where('health_status', '!=', 'unhealthy')
            ->orderBy('name')
            ->get();
        
        // Manter compatibilidade com código legado (whatsapp_settings)
        $row = DB::table('whatsapp_settings')->where('active', 1)->first();
        
        // Buscar dados de notificação da tabela settings
        $settings = DB::table('settings')
            ->where(function($query) use ($clientId) {
                $query->where('client_id', $clientId)
                      ->orWhereNull('client_id');
            })
            ->first();
        
        // Buscar templates
        $templates = DB::table('whatsapp_templates')
            ->where(function($query) use ($clientId) {
                $query->where('client_id', $clientId)
                      ->orWhereNull('client_id');
            })
            ->orderBy('slug')
            ->get();
        
        // Buscar status e suas notificações
        $statuses = DB::table('order_statuses')
            ->leftJoin('whatsapp_templates', 'order_statuses.whatsapp_template_id', '=', 'whatsapp_templates.id')
            ->select(
                'order_statuses.*',
                'whatsapp_templates.slug as template_slug'
            )
            ->where(function($query) use ($clientId) {
                $query->where('order_statuses.client_id', $clientId)
                      ->orWhereNull('order_statuses.client_id');
            })
            ->orderBy('order_statuses.created_at')
            ->get();
        
        // Estatísticas básicas
        $stats = [
            'total_templates' => $templates->count(),
            'active_templates' => $templates->where('active', true)->count(),
            'total_statuses' => $statuses->count(),
            'statuses_with_notifications' => $statuses->where('notify_customer', true)->count(),
            'total_instances' => $instances->count(),
            'connected_instances' => $instances->where('status', 'CONNECTED')->count(),
        ];
        
        // Verificar status de conexão de cada instância
        foreach ($instances as $instance) {
            try {
                $info = $this->checkInstanceStatus($instance);
                $instance->live_status = $info['connected'] ? 'connected' : 'disconnected';
                $instance->live_phone = $info['phone'] ?? $instance->phone_number;
            } catch (\Exception $e) {
                $instance->live_status = 'unknown';
                $instance->live_phone = $instance->phone_number;
            }
        }
        
        // Status de conexão legado (para compatibilidade)
        $connectionStatus = 'unknown';
        $connectionPhone = null;
        
        if ($row && !empty($row->api_url) && !empty($row->instance_name)) {
            try {
                $connectionStatus = $this->checkWhatsappConnection($row) ? 'connected' : 'disconnected';
                $instanceInfo = $this->getWhatsappInstanceInfo($row);
                $connectionPhone = $instanceInfo['phone'] ?? null;
            } catch (\Exception $e) {
                Log::warning('Erro ao verificar status WhatsApp: ' . $e->getMessage());
                $connectionStatus = 'disconnected';
            }
        } else {
            $connectionStatus = 'disconnected';
        }

        return view('dashboard.settings.whatsapp', compact(
            'row', 'settings', 'templates', 'statuses', 'stats', 
            'connectionStatus', 'connectionPhone', 'instances', 'availableUrls'
        ));
    }
    
    /**
     * Verifica status de uma instância específica
     */
    protected function checkInstanceStatus($instance): array
    {
        if (empty($instance->api_url)) {
            return ['connected' => false, 'phone' => null];
        }
        
        try {
            $url = rtrim($instance->api_url, '/') . '/api/whatsapp/status';
            $client = new \GuzzleHttp\Client(['timeout' => 8, 'http_errors' => false]);
            $response = $client->get($url, [
                'headers' => [
                    'X-API-Token' => $instance->api_token ?? '',
                ]
            ]);
            
            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody()->getContents(), true);
                return [
                    'connected' => $data['connected'] ?? $data['isConnected'] ?? false,
                    'phone' => $data['currentPhone'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            Log::debug('Erro ao verificar status da instância: ' . $e->getMessage());
        }
        
        return ['connected' => false, 'phone' => null];
    }

    public function whatsappSave(Request $r)
    {
        $data = $r->validate([
            'instance_name' => 'required|string|max:100',
            'api_url' => 'required|url|max:255',
            'api_key' => 'nullable|string|max:255', // Opcional - será gerado automaticamente
            'sender_name' => 'nullable|string|max:100',
        ]);

        // Gerar API Key automaticamente se não fornecida
        if (empty($data['api_key']) || str_starts_with($data['api_key'], 'AUTO_GENERATED_')) {
            $data['api_key'] = 'sk_' . bin2hex(random_bytes(32)); // Gera token seguro de 64 caracteres
        }

        $row = DB::table('whatsapp_settings')->where('active', 1)->first();

        if ($row) {
            // Não atualizar updated_at se a coluna não existir
            $updateData = $data;
            try {
                $updateData['updated_at'] = now();
                DB::table('whatsapp_settings')->where('id', $row->id)->update($updateData);
            } catch (\Exception $e) {
                // Se falhar (coluna updated_at não existe), tentar sem ela
                unset($updateData['updated_at']);
                DB::table('whatsapp_settings')->where('id', $row->id)->update($updateData);
            }
        } else {
            DB::table('whatsapp_settings')->insert($data + [
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'Configurações do WhatsApp salvas com sucesso!');
    }
    
    /**
     * Salva configurações de notificação (número do admin)
     */
    public function whatsappAdminNotificationSave(Request $r)
    {
        $data = $r->validate([
            'notificacao_whatsapp' => 'nullable|string|max:20',
            'notificacao_whatsapp_confirmacao' => 'nullable|string|max:20',
        ]);
        
        // Limpar máscara do telefone e garantir formato internacional
        $notificacao = preg_replace('/\D/', '', $data['notificacao_whatsapp'] ?? '');
        $confirmacao = preg_replace('/\D/', '', $data['notificacao_whatsapp_confirmacao'] ?? '');
        
        // Adicionar 55 se não tiver código do país
        if (!empty($notificacao) && strlen($notificacao) <= 11) {
            $notificacao = '55' . $notificacao;
        }
        if (!empty($confirmacao) && strlen($confirmacao) <= 11) {
            $confirmacao = '55' . $confirmacao;
        }
        
        $clientId = currentClientId();

        // Salvar na tabela settings com filtro de client
        DB::table('settings')
            ->where(function($query) use ($clientId) {
                $query->where('client_id', $clientId)
                      ->orWhereNull('client_id');
            })
            ->limit(1)
            ->update([
                'notificacao_whatsapp' => !empty($notificacao) ? $notificacao : null,
                'notificacao_whatsapp_confirmacao' => !empty($confirmacao) ? $confirmacao : null,
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Número de notificação salvo com sucesso!');
    }
    
    /**
     * Atualiza notificações automáticas dos status
     */
    public function whatsappNotificationsSave(Request $r)
    {
        $clientId = currentClientId();
        $notifications = $r->input('notifications', []);
        
        // Primeiro, zerar todas as notificações para os status do cliente
        DB::table('order_statuses')
            ->where(function($query) use ($clientId) {
                $query->where('client_id', $clientId)
                      ->orWhereNull('client_id');
            })
            ->update([
                'notify_customer' => 0,
                'notify_admin' => 0,
                'updated_at' => now(),
            ]);
        
        // Depois, marcar apenas os que foram selecionados
        foreach ($notifications as $statusId => $settings) {
            DB::table('order_statuses')
                ->where('id', $statusId)
                ->where(function($query) use ($clientId) {
                    $query->where('client_id', $clientId)
                          ->orWhereNull('client_id');
                })
                ->update([
                    'notify_customer' => isset($settings['customer']) ? 1 : 0,
                    'notify_admin' => isset($settings['admin']) ? 1 : 0,
                    'updated_at' => now(),
                ]);
        }

        return back()->with('success', 'Configurações de notificações salvas com sucesso!');
    }

    public function waConnect()
    {
        try{
            $wa = new \App\Services\WhatsAppService();
            $res = $wa->connectInstance();
            
            if(!$res){ 
                return response()->json(['ok'=>false,'msg'=>'Falha ao conectar. Veja os logs.']); 
            }
            
            // Normalize possíveis campos
            $qrBase64 = $res['base64'] ?? $res['qrCode'] ?? null; // algumas builds usam 'base64', outras 'qrCode'
            $pair     = $res['pairingCode'] ?? $res['code'] ?? null;
            
            return response()->json([
                'ok' => true,
                'pairing_code' => $pair,
                'qr_base64' => $qrBase64,  // pode vir 'data:image/png;base64,...' ou só o base64 puro
                'raw' => $res
            ]);
            
        } catch(\Throwable $e){
            \Log::error('waConnect error: '.$e->getMessage());
            return response()->json(['ok'=>false,'msg'=>'Erro interno ao conectar']);
        }
    }

    public function waHealth()
    {
        try{
            $row = \DB::table('whatsapp_settings')->where('active',1)->first();
            if(!$row) return response()->json(['ok'=>false,'msg'=>'Sem configuração.']);

            $url = rtrim($row->api_url,'/')."/instance/health/{$row->instance_name}";
            $ch  = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER=>true,
                CURLOPT_HTTPHEADER => ["apikey: {$row->api_key}"],
                CURLOPT_TIMEOUT => 15
            ]);
            
            $resp = curl_exec($ch);
            if($resp === false){ return response()->json(['ok'=>false,'msg'=>curl_error($ch)]); }
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
            if($code >= 300) return response()->json(['ok'=>false,'msg'=>"HTTP {$code}",'raw'=>$resp]);
            
            $j = json_decode($resp,true);
            return response()->json(['ok'=>true,'data'=>$j]);
            
        }catch(\Throwable $e){
            \Log::error('waHealth error: '.$e->getMessage());
            return response()->json(['ok'=>false,'msg'=>'erro interno']);
        }
    }

    public function mp()
    {
        // Usar array simples para evitar problemas de acesso na view
        $keys = DB::table('payment_settings')->pluck('value', 'key')->toArray();

        // Buscar dados reais de pedidos pagos
        // Pedidos pagos através do Mercado Pago (status approved ou paid)
        $paidOrders = \App\Models\Order::with('customer')
            ->whereIn('payment_status', ['approved', 'paid'])
            ->get();

        // Total processado (soma de final_amount de pedidos pagos)
        $totalProcessed = $paidOrders->sum('final_amount');

        // Quantidade de transações
        $totalTransactions = $paidOrders->count();

        // Total de pedidos com pagamento (incluindo pendentes, pagos, falhados)
        $totalOrdersWithPayment = \App\Models\Order::where(function($query) {
                $query->whereNotNull('payment_id')
                      ->orWhereNotNull('payment_status');
            })
            ->count();

        // Taxa de aprovação (% de pedidos pagos vs total de pedidos com tentativa de pagamento)
        $approvalRate = $totalOrdersWithPayment > 0 
            ? round(($totalTransactions / $totalOrdersWithPayment) * 100, 1)
            : 0;

        // Ticket médio
        $averageTicket = $totalTransactions > 0 
            ? $totalProcessed / $totalTransactions 
            : 0;

        // Últimas transações (10 mais recentes pagas)
        $recentTransactions = $paidOrders
            ->sortByDesc('created_at')
            ->take(10)
            ->map(function($order) {
                return [
                    'customer_name' => $order->customer ? $order->customer->name : 'Cliente não identificado',
                    'value' => $order->final_amount,
                    'method' => $this->getPaymentMethodLabel($order->payment_method),
                    'status' => $order->payment_status === 'approved' || $order->payment_status === 'paid' ? 'Aprovado' : ucfirst($order->payment_status),
                    'date' => $order->created_at->format('d/m/Y'),
                    'status_class' => in_array($order->payment_status, ['approved', 'paid']) ? 'success' : 'muted',
                ];
            })
            ->values();

        return view('dashboard.settings.mercado-pago', [
            'keys' => $keys,
            'totalProcessed' => $totalProcessed,
            'totalTransactions' => $totalTransactions,
            'approvalRate' => $approvalRate,
            'averageTicket' => $averageTicket,
            'recentTransactions' => $recentTransactions,
        ]);
    }

    /**
     * Retorna label do método de pagamento
     */
    private function getPaymentMethodLabel($method)
    {
        $labels = [
            'pix' => 'PIX',
            'credit_card' => 'Cartão de Crédito',
            'debit_card' => 'Cartão de Débito',
            'bank_transfer' => 'Transferência Bancária',
        ];

        return $labels[$method] ?? ucfirst(str_replace('_', ' ', $method));
    }

    public function mpSave(Request $r)
    {
        $data = $r->validate([
            'mercadopago_access_token' => 'required|string',
            'mercadopago_public_key' => 'required|string',
            'mercadopago_environment' => 'required|string',
            'mercadopago_webhook_url' => 'nullable|url',
        ]);

        // Persistir de forma idempotente
        foreach ($data as $k => $v) {
            DB::table('payment_settings')->updateOrInsert(
                ['key' => $k],
                ['value' => $v, 'updated_at' => now(), 'created_at' => DB::raw('COALESCE(created_at, NOW())')]
            );
        }

        \Log::info('MercadoPago settings saved', [
            'keys' => array_keys($data),
        ]);

        return back()->with('ok', 'Configurações do Mercado Pago salvas.');
    }

    /**
     * Salva métodos de pagamento habilitados/desabilitados (Mercado Pago)
     */
    public function mpMethodsSave(Request $r)
    {
        // Checkboxes: se não vierem marcados, devem virar 0
        $keys = [
            'mp_enable_credit_card',
            'mp_enable_debit_card',
            'mp_enable_pix',
            'mp_enable_boleto',
        ];

        $toSave = [];
        foreach ($keys as $k) {
            $toSave[$k] = $r->has($k) ? '1' : '0';
        }

        foreach ($toSave as $k => $v) {
            DB::table('payment_settings')->updateOrInsert(
                ['key' => $k],
                ['value' => $v, 'updated_at' => now(), 'created_at' => DB::raw('COALESCE(created_at, NOW())')]
            );
        }

        \Log::info('MercadoPago methods settings saved', [
            'keys' => $toSave,
        ]);

        return back()->with('ok', 'Métodos de pagamento atualizados.');
    }
    
    /**
     * API: Retorna dados do cliente para o Node.js WhatsApp Gateway
     * Usado pelo sistema de notificações
     */
    public function getClientData($id)
    {
        // Buscar cliente da tabela clients
        $client = DB::table('clients')->find($id);
        
        if (!$client) {
            return response()->json([
                'error' => 'Client not found',
                'message' => 'Cliente não encontrado'
            ], 404);
        }
        
        // Buscar configurações da tabela settings
        $settings = DB::table('settings')->first();
        
        // Verificar se o cliente tem assinatura ativa com WhatsApp
        $subscription = null;
        $hasWhatsapp = false;
        $hasIA = false;
        
        if ($client->subscription_id) {
            $subscription = DB::table('subscriptions')
                ->where('id', $client->subscription_id)
                ->where('status', 'active')
                ->first();
                
            if ($subscription) {
                // Verificar features do plano
                $plan = DB::table('plans')->find($subscription->plan_id);
                if ($plan) {
                    $features = json_decode($plan->features ?? '[]', true);
                    $hasWhatsapp = in_array('whatsapp', $features) || in_array('whatsapp_notifications', $features);
                    $hasIA = in_array('ia', $features) || in_array('ai_assistant', $features);
                }
            }
        }
        
        // Retornar dados formatados para o Node.js
        return response()->json([
            'id' => (int) $id,
            'name' => $client->name ?? ($settings->business_name ?? 'Olika'),
            'slug' => $client->slug ?? null,
            'plan' => $client->plan ?? 'basic',
            'active' => (bool) ($client->active ?? true),
            'is_active' => (bool) ($client->active ?? true),
            'has_whatsapp' => $hasWhatsapp || !empty($client->whatsapp_phone),
            'has_ia' => $hasIA,
            'notificacao_whatsapp' => $client->notificacao_whatsapp ?? ($settings->notificacao_whatsapp ?? null),
            'notificacao_whatsapp_confirmacao' => $settings->notificacao_whatsapp_confirmacao ?? null,
            'whatsapp_phone' => $client->whatsapp_phone ?? null,
            'api_url' => $settings->whatsapp_api_url ?? null,
            'sender_name' => 'Olika Bot',
        ]);
    }
    
    /**
     * API: Verifica status da conexão WhatsApp (AJAX)
     */
    public function whatsappStatus()
    {
        $row = DB::table('whatsapp_settings')->where('active', 1)->first();
        
        if (!$row || empty($row->api_url) || empty($row->instance_name)) {
            return response()->json([
                'connected' => false,
                'message' => 'Instância não configurada'
            ]);
        }
        
        try {
            $connected = $this->checkWhatsappConnection($row);
            $info = $connected ? $this->getWhatsappInstanceInfo($row) : [];
            
            return response()->json([
                'connected' => $connected,
                'phone' => $info['phone'] ?? null,
                'name' => $info['name'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::warning('Erro ao verificar status WhatsApp: ' . $e->getMessage());
            return response()->json([
                'connected' => false,
                'message' => 'Erro ao verificar status'
            ]);
        }
    }
    
    /**
     * Desconecta instância WhatsApp
     */
    public function whatsappDisconnect()
    {
        $row = DB::table('whatsapp_settings')->where('active', 1)->first();
        
        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'Instância não encontrada'
            ]);
        }
        
        try {
            // Usar endpoint do projeto Node.js customizado
            $url = rtrim($row->api_url, '/') . "/api/whatsapp/restart";
            
            $client = new \GuzzleHttp\Client(['timeout' => 15, 'http_errors' => false]);
            $response = $client->post($url, [
                'headers' => [
                    'X-API-Token' => $row->api_key ?? '',
                    'Content-Type' => 'application/json'
                ]
            ]);
            
            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents(), true);
            
            if ($statusCode >= 200 && $statusCode < 300) {
                Log::info('WhatsApp instância reiniciada: ' . $row->instance_name);
                
                return response()->json([
                    'success' => true,
                    'message' => $body['message'] ?? 'Instância reiniciada com sucesso'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $body['error'] ?? 'Erro ao reiniciar instância'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao desconectar WhatsApp: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao desconectar: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Conecta/reconecta instância WhatsApp - gera código de pareamento
     */
    public function whatsappConnect()
    {
        $row = DB::table('whatsapp_settings')->where('active', 1)->first();
        
        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'Configure as credenciais do WhatsApp primeiro'
            ]);
        }
        
        if (empty($row->api_url)) {
            return response()->json([
                'success' => false,
                'message' => 'URL da API é obrigatória'
            ]);
        }
        
        // Buscar número do WhatsApp configurado
        $settings = DB::table('settings')->first();
        $whatsappPhone = $settings->notificacao_whatsapp ?? $row->whatsapp_phone ?? null;
        
        if (empty($whatsappPhone)) {
            return response()->json([
                'success' => false,
                'message' => 'Número do WhatsApp não configurado. Configure em Configurações da Loja.'
            ]);
        }
        
        try {
            // Usar endpoint do projeto Node.js customizado
            $url = rtrim($row->api_url, '/') . "/api/whatsapp/connect";
            
            $client = new \GuzzleHttp\Client(['timeout' => 30, 'http_errors' => false]);
            
            $response = $client->post($url, [
                'headers' => [
                    'X-API-Token' => $row->api_key ?? '',
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'phone' => preg_replace('/\D/', '', $whatsappPhone) // Apenas números
                ]
            ]);
            
            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents(), true);
            
            if ($statusCode >= 200 && $statusCode < 300) {
                Log::info('WhatsApp connect iniciado', $body ?? []);
                
                return response()->json([
                    'success' => true,
                    'message' => $body['message'] ?? 'Conexão iniciada. Verifique os logs do Railway para o código de pareamento.',
                    'raw' => $body
                ]);
            } else {
                Log::warning('WhatsApp connect falhou: HTTP ' . $statusCode, $body ?? []);
                return response()->json([
                    'success' => false,
                    'message' => $body['error'] ?? $body['message'] ?? 'Erro ao conectar instância'
                ]);
            }
            
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            Log::error('WhatsApp connect timeout: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Timeout ao conectar. Verifique se a URL da API está correta e o serviço está online.'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao conectar WhatsApp: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao conectar: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Verifica conexão com instância WhatsApp
     */
    protected function checkWhatsappConnection($row): bool
    {
        if (!$row || empty($row->api_url)) {
            return false;
        }
        
        try {
            // Usar endpoint do projeto Node.js customizado
            $url = rtrim($row->api_url, '/') . "/api/whatsapp/status";
            
            $client = new \GuzzleHttp\Client(['timeout' => 10, 'http_errors' => false]);
            $response = $client->get($url, [
                'headers' => [
                    'X-API-Token' => $row->api_key ?? '',
                ]
            ]);
            
            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody()->getContents(), true);
                // Verifica se está conectado
                return ($data['connected'] ?? $data['isConnected'] ?? false) === true;
            }
        } catch (\Exception $e) {
            Log::debug('Erro ao verificar conexão WhatsApp: ' . $e->getMessage());
        }
        
        return false;
    }
    
    /**
     * Busca informações da instância WhatsApp
     */
    protected function getWhatsappInstanceInfo($row): array
    {
        if (!$row || empty($row->api_url)) {
            return [];
        }
        
        try {
            // Usar endpoint do projeto Node.js customizado
            $url = rtrim($row->api_url, '/') . "/api/whatsapp/status";
            
            $client = new \GuzzleHttp\Client(['timeout' => 10, 'http_errors' => false]);
            $response = $client->get($url, [
                'headers' => [
                    'X-API-Token' => $row->api_key ?? '',
                ]
            ]);
            
            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody()->getContents(), true);
                
                return [
                    'phone' => $data['currentPhone'] ?? null,
                    'name' => $data['profileName'] ?? $row->instance_name ?? null,
                    'connected' => $data['connected'] ?? $data['isConnected'] ?? false,
                    'pairingCode' => $data['pairingCode'] ?? null,
                    'message' => $data['message'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            Log::debug('Erro ao buscar info WhatsApp: ' . $e->getMessage());
        }
        
        return [];
    }
    
    /**
     * Cria nova instância WhatsApp para o cliente
     */
    public function whatsappInstanceStore(Request $request)
    {
        $clientId = currentClientId();
        
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'phone_number' => 'required|string|max:20',
        ]);
        
        // Buscar automaticamente a primeira URL disponível
        $url = DB::table('whatsapp_instance_urls')
            ->where('status', 'available')
            ->where('health_status', '!=', 'unhealthy')
            ->orderBy('id')
            ->first();
        
        if (!$url) {
            return back()->with('error', 'Nenhuma instância disponível no momento. Entre em contato com o suporte.');
        }
        
        // Buscar API Token global
        $apiToken = $this->getGlobalApiToken();
        
        // Limpar telefone e garantir formato internacional
        $phone = preg_replace('/\D/', '', $data['phone_number'] ?? '');
        if (!empty($phone) && strlen($phone) <= 11) {
            $phone = '55' . $phone;
        }
        
        // Criar instância
        $instanceId = DB::table('whatsapp_instances')->insertGetId([
            'client_id' => $clientId,
            'name' => $data['name'],
            'phone_number' => $phone ?: null,
            'api_url' => $url->url,
            'instance_url_id' => $url->id,
            'api_token' => $apiToken,
            'status' => 'DISCONNECTED',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Marcar URL como atribuída
        DB::table('whatsapp_instance_urls')
            ->where('id', $url->id)
            ->update([
                'status' => 'assigned',
                'client_id' => $clientId,
                'whatsapp_instance_id' => $instanceId,
                'current_connections' => DB::raw('current_connections + 1'),
                'updated_at' => now(),
            ]);
        
        return back()->with('success', 'Instância WhatsApp criada com sucesso! Clique em "Conectar" para vincular seu número.');
    }
    
    /**
     * Remove instância WhatsApp do cliente
     */
    public function whatsappInstanceDestroy($instanceId)
    {
        $clientId = currentClientId();
        
        $instance = DB::table('whatsapp_instances')
            ->where('id', $instanceId)
            ->where('client_id', $clientId)
            ->first();
        
        if (!$instance) {
            return response()->json(['success' => false, 'message' => 'Instância não encontrada']);
        }
        
        // Liberar URL
        if ($instance->instance_url_id) {
            DB::table('whatsapp_instance_urls')
                ->where('id', $instance->instance_url_id)
                ->update([
                    'status' => 'available',
                    'client_id' => null,
                    'whatsapp_instance_id' => null,
                    'current_connections' => DB::raw('GREATEST(current_connections - 1, 0)'),
                    'updated_at' => now(),
                ]);
        }
        
        // Remover instância
        DB::table('whatsapp_instances')->where('id', $instanceId)->delete();
        
        return response()->json(['success' => true, 'message' => 'Instância removida com sucesso']);
    }
    
    /**
     * Verifica status de uma instância específica (AJAX)
     */
    public function whatsappInstanceStatus($instanceId)
    {
        $clientId = currentClientId();
        
        $instance = DB::table('whatsapp_instances')
            ->where('id', $instanceId)
            ->where('client_id', $clientId)
            ->first();
        
        if (!$instance) {
            return response()->json(['connected' => false, 'message' => 'Instância não encontrada']);
        }
        
        $status = $this->checkInstanceStatus($instance);
        
        return response()->json([
            'connected' => $status['connected'],
            'phone' => $status['phone'] ?? $instance->phone_number,
            'status' => $status['connected'] ? 'CONNECTED' : 'DISCONNECTED',
        ]);
    }
    
    /**
     * Conecta instância WhatsApp (gera código de pareamento)
     */
    public function whatsappInstanceConnect(Request $request, $instanceId)
    {
        $clientId = currentClientId();
        
        $instance = DB::table('whatsapp_instances')
            ->where('id', $instanceId)
            ->where('client_id', $clientId)
            ->first();
        
        if (!$instance) {
            return response()->json(['success' => false, 'message' => 'Instância não encontrada']);
        }
        
        if (empty($instance->api_url)) {
            return response()->json(['success' => false, 'message' => 'URL da API não configurada']);
        }
        
        // Buscar telefone (sempre usar o do request, se fornecido)
        $phone = $request->input('phone');
        if (empty($phone)) {
            $phone = $instance->phone_number;
        }
        $phone = preg_replace('/\D/', '', $phone ?? '');
        
        if (empty($phone)) {
            return response()->json(['success' => false, 'message' => 'Número de telefone é obrigatório']);
        }
        
        // Garantir formato internacional
        if (!str_starts_with($phone, '55') && strlen($phone) <= 11) {
            $phone = '55' . $phone;
        }
        
        try {
            $baseUrl = rtrim($instance->api_url, '/');
            $client = new \GuzzleHttp\Client(['timeout' => 10, 'connect_timeout' => 5, 'http_errors' => false]);
            
            // 1. Chamar /connect para iniciar
            $connectResponse = $client->post($baseUrl . '/api/whatsapp/connect', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-API-Token' => $instance->api_token ?? '',
                ],
                'json' => ['phone' => $phone]
            ]);
            
            $connectBody = json_decode($connectResponse->getBody()->getContents(), true);
            Log::info('WhatsApp Connect Initial Response', ['body' => $connectBody]);
            
            if ($connectResponse->getStatusCode() !== 200 || !($connectBody['success'] ?? false)) {
                return response()->json([
                    'success' => false,
                    'message' => $connectBody['error'] ?? $connectBody['message'] ?? 'Erro ao iniciar conexão'
                ]);
            }
            
            // 2. Atualizar telefone no banco
            DB::table('whatsapp_instances')
                ->where('id', $instanceId)
                ->update([
                    'phone_number' => $phone,
                    'status' => 'CONNECTING',
                    'updated_at' => now(),
                ]);
            
            // 3. Aguardar e fazer polling do código (até 3 tentativas com 3s de intervalo)
            $pairingCode = null;
            for ($i = 0; $i < 4; $i++) {
                sleep(3); // Esperar 3 segundos
                
                try {
                    $statusResponse = $client->get($baseUrl . '/api/whatsapp/status', [
                        'headers' => [
                            'X-API-Token' => $instance->api_token ?? '',
                        ]
                    ]);
                    
                    $statusBody = json_decode($statusResponse->getBody()->getContents(), true);
                    Log::info('WhatsApp Status Polling #' . ($i + 1), ['body' => $statusBody]);
                    
                    if (!empty($statusBody['pairingCode'])) {
                        $pairingCode = $statusBody['pairingCode'];
                        break;
                    }
                    
                    // Se já conectou, não precisa de código
                    if ($statusBody['connected'] ?? $statusBody['isConnected'] ?? false) {
                        return response()->json([
                            'success' => true,
                            'message' => 'WhatsApp já conectado!',
                            'pairing_code' => null,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Polling attempt #' . ($i + 1) . ' failed: ' . $e->getMessage());
                }
            }
            
            if ($pairingCode) {
                return response()->json([
                    'success' => true,
                    'message' => 'Código gerado com sucesso!',
                    'pairing_code' => $pairingCode,
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Conexão iniciada! Verifique o status em alguns segundos.',
                    'pairing_code' => null,
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Erro ao conectar instância WhatsApp: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro de conexão. Tente novamente.']);
        }
    }
    
    /**
     * Desconecta instância WhatsApp
     */
    public function whatsappInstanceDisconnect($instanceId)
    {
        $clientId = currentClientId();
        
        $instance = DB::table('whatsapp_instances')
            ->where('id', $instanceId)
            ->where('client_id', $clientId)
            ->first();
        
        if (!$instance) {
            return response()->json(['success' => false, 'message' => 'Instância não encontrada']);
        }
        
        try {
            $url = rtrim($instance->api_url, '/') . '/api/whatsapp/restart';
            $client = new \GuzzleHttp\Client(['timeout' => 15, 'http_errors' => false]);
            $response = $client->post($url, [
                'headers' => [
                    'X-API-Token' => $instance->api_token ?? '',
                ]
            ]);
            
            // Atualizar status local
            DB::table('whatsapp_instances')
                ->where('id', $instanceId)
                ->update([
                    'status' => 'DISCONNECTED',
                    'updated_at' => now(),
                ]);
            
            return response()->json(['success' => true, 'message' => 'Instância desconectada']);
        } catch (\Exception $e) {
            Log::error('Erro ao desconectar instância: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Busca API Token global do sistema
     */
    protected function getGlobalApiToken(): ?string
    {
        // Tentar buscar de whatsapp_settings
        $wsToken = DB::table('whatsapp_settings')
            ->where('active', 1)
            ->value('api_key');
        
        if (!empty($wsToken)) {
            return $wsToken;
        }
        
        // Fallback: payment_settings.webhook_token
        $token = DB::table('payment_settings')
            ->where('key', 'webhook_token')
            ->value('value');
        
        return $token ?: null;
    }
}

