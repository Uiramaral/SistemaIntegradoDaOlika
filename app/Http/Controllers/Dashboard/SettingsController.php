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
        $row = DB::table('whatsapp_settings')->where('active', 1)->first();
        
        // Buscar templates
        $templates = DB::table('whatsapp_templates')
            ->orderBy('slug')
            ->get();
        
        // Buscar status e suas notificações
        $statuses = DB::table('order_statuses')
            ->leftJoin('whatsapp_templates', 'order_statuses.whatsapp_template_id', '=', 'whatsapp_templates.id')
            ->select(
                'order_statuses.*',
                'whatsapp_templates.slug as template_slug'
            )
            ->orderBy('order_statuses.created_at')
            ->get();
        
        // Estatísticas básicas
        $stats = [
            'total_templates' => $templates->count(),
            'active_templates' => $templates->where('active', true)->count(),
            'total_statuses' => $statuses->count(),
            'statuses_with_notifications' => $statuses->where('notify_customer', true)->count(),
        ];

        return view('dashboard.settings.whatsapp', compact('row', 'templates', 'statuses', 'stats'));
    }

    public function whatsappSave(Request $r)
    {
        $data = $r->validate([
            'instance_name' => 'required|string|max:100',
            'api_url' => 'required|url|max:255',
            'api_key' => 'required|string|max:255',
            'sender_name' => 'nullable|string|max:100',
        ]);

        $row = DB::table('whatsapp_settings')->where('active', 1)->first();

        if ($row) {
            DB::table('whatsapp_settings')->where('id', $row->id)->update($data + ['updated_at' => now()]);
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
     * Atualiza notificações automáticas dos status
     */
    public function whatsappNotificationsSave(Request $r)
    {
        $notifications = $r->input('notifications', []);
        
        foreach ($notifications as $statusId => $settings) {
            DB::table('order_statuses')
                ->where('id', $statusId)
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
}

