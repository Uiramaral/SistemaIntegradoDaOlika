<?php

namespace App\Services;

use App\Events\OrderStatusUpdated;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\WhatsAppService;

class OrderStatusService
{
    public function changeStatus(Order $order, string $newCode, ?string $note = null, ?int $userId = null, bool $skipHistory = false, bool $skipNotifications = false): void
    {
        // Recarregar order para garantir dados atualizados
        $order->refresh();
        
        $old = $order->status;
        
        // Mapear códigos de ENUM para códigos de order_statuses
        $enumToStatusCodeMapping = [
            'confirmed' => 'paid', // "confirmed" no ENUM mapeia para "paid" em order_statuses
            'pending' => 'pending',
            'preparing' => 'preparing',
            'ready' => 'out_for_delivery',
            'delivered' => 'delivered',
            'cancelled' => 'cancelled',
        ];
        
        // Se o código recebido é um código do ENUM, mapear para código de order_statuses
        $statusCodeForLookup = $enumToStatusCodeMapping[$newCode] ?? $newCode;
        
        // Não fazer nada se já estiver no mesmo status (comparar pelo código original)
        if ($old === $newCode) {
            // Mesmo assim, tentar enviar notificação se necessário
            // (pode ter sido atualizado externamente mas sem notificação)
        } else {
            // Atualizar apenas se for diferente
            // (O controller já atualizou, mas vamos garantir)
            if ($order->status !== $newCode) {
                $order->status = $newCode;
                $order->save();
            }
        }
        
        // Registrar cashback quando o pagamento é confirmado
        // Verificar se é confirmação de pagamento (paid ou confirmed)
        $isPaymentConfirmed = in_array($statusCodeForLookup, ['paid', 'confirmed']) || 
                             in_array($newCode, ['paid', 'confirmed']) ||
                             $order->payment_status === 'paid';
        
        if ($isPaymentConfirmed && $order->customer_id) {
            // IMPORTANTE: Recarregar o pedido do banco para garantir que temos os valores corretos de cashback
            $order->refresh();
            
            try {
                Log::info('OrderStatusService: Registrando cashback para pedido pago', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'cashback_used' => $order->cashback_used,
                    'cashback_earned' => $order->cashback_earned,
                    'customer_id' => $order->customer_id,
                ]);
                
                // Verificar se já existe transação de cashback para este pedido (evitar duplicatas)
                $existingCashback = \App\Models\CustomerCashback::where('order_id', $order->id)->first();
                if ($existingCashback) {
                    Log::warning('OrderStatusService: Cashback já registrado para este pedido, pulando', [
                        'order_id' => $order->id,
                        'existing_cashback_id' => $existingCashback->id,
                    ]);
                } else {
                    // Débito: cashback usado
                    if ($order->cashback_used > 0) {
                        $debit = \App\Models\CustomerCashback::createDebit(
                            $order->customer_id,
                            $order->id,
                            $order->cashback_used,
                            "Uso de cashback no pedido #{$order->order_number}"
                        );
                        Log::info('OrderStatusService: Débito de cashback criado', [
                            'debit_id' => $debit->id,
                            'amount' => $debit->amount,
                        ]);
                    }
                    
                    // Crédito: cashback ganho
                    if ($order->cashback_earned > 0) {
                        $credit = \App\Models\CustomerCashback::createCredit(
                            $order->customer_id,
                            $order->id,
                            $order->cashback_earned,
                            "Cashback do pedido #{$order->order_number}"
                        );
                        Log::info('OrderStatusService: Crédito de cashback criado', [
                            'credit_id' => $credit->id,
                            'amount' => $credit->amount,
                        ]);
                    } else {
                        Log::warning('OrderStatusService: Cashback ganho é zero ou nulo, não criando crédito', [
                            'order_id' => $order->id,
                            'cashback_earned' => $order->cashback_earned,
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                Log::error('OrderStatusService: Falha ao registrar cashback', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        // Histórico (só registrar se não foi solicitado para pular)
        if (!$skipHistory) {
            // Verificar se já existe histórico recente para evitar duplicação
            $recentHistory = DB::table('order_status_history')
                ->where('order_id', $order->id)
                ->where('new_status', $newCode)
                ->where('created_at', '>=', now()->subMinute())
                ->first();
            
            if (!$recentHistory) {
                // Verificar se a coluna updated_at existe antes de inserir
                $hasUpdatedAt = DB::getSchemaBuilder()->hasColumn('order_status_history', 'updated_at');
                
                $insertData = [
                    'order_id'   => $order->id,
                    'old_status' => $old,
                    'new_status' => $newCode,
                    'note'       => $note,
                    'user_id'    => $userId,
                    'created_at' => now(),
                ];
                
                // Só adicionar updated_at se a coluna existir
                if ($hasUpdatedAt) {
                    $insertData['updated_at'] = now();
                }
                
                DB::table('order_status_history')->insert($insertData);
            }
        }

        // Regras do status (notificações)
        // Buscar usando o código mapeado (statusCodeForLookup já foi calculado acima)
        $st = DB::table('order_statuses')
            ->where('code', $statusCodeForLookup)
            ->where('active', 1)
            ->first();
        
        // Se não encontrar com o mapeamento, tentar buscar pelo código original
        if (!$st) {
            $st = DB::table('order_statuses')
                ->where('code', $newCode)
                ->where('active', 1)
                ->first();
        }
        
        // Vars padrão (usar mesmo se status não for encontrado)
        $deliveryNote = trim((string)($note ?? ''));

        $vars = [
            'nome'   => optional($order->customer)->name ?? 'Cliente',
            'pedido' => (string) $order->order_number,
            'valor'  => number_format($order->final_amount ?? $order->total_amount ?? 0, 2, ',', '.'),
            'observacao' => $deliveryNote,
        ];

        // Monta mensagem (template ou fallback)
        $tplText = null;
        $shouldNotifyCustomer = false;
        $shouldNotifyAdmin = false;
        
        if ($st) {
            if ($st->whatsapp_template_id) {
                $tpl = DB::table('whatsapp_templates')
                    ->where('id', $st->whatsapp_template_id)
                    ->where('active', 1)
                    ->first();
                
                if ($tpl) {
                    $tplText = $tpl->content;
                }
            }
            
            if (!$tplText) {
                // fallback simples
                $tplText = "Status do pedido *#{$order->order_number}*: {$st->name}.";
            }
            
            $shouldNotifyCustomer = $st->notify_customer ?? false;
            $shouldNotifyAdmin = $st->notify_admin ?? false;
        } else {
            // Se status não encontrado, criar fallback baseado no código
            $statusName = ucfirst(str_replace('_', ' ', $newCode));
            $tplText = "Status do pedido *#{$order->order_number}*: {$statusName}.";
            
            // Para status "paid" ou "confirmed", sempre tentar notificar
            if (in_array($statusCodeForLookup, ['paid', 'confirmed']) || $newCode === 'confirmed') {
                $shouldNotifyCustomer = true;
            }
            
            Log::warning('OrderStatusService: Status não encontrado ou inativo, usando fallback', [
                'code' => $newCode,
                'mapped_code' => $statusCodeForLookup,
                'order_id' => $order->id,
                'available_statuses' => DB::table('order_statuses')->where('active', 1)->pluck('code')->toArray()
            ]);
        }

        // Custom mensagens para fluxos específicos
        if ($newCode === 'confirmed') {
            $order->loadMissing('items.product', 'customer');

            $itemsLines = [];
            foreach ($order->items as $item) {
                $name = $item->custom_name ?? optional($item->product)->name ?? 'Item';
                $qty = (int) ($item->quantity ?? 1);
                $total = $item->total_price ?? ($item->unit_price * $qty);
                $itemsLines[] = sprintf("%dx %s — R$ %s", $qty, $name, number_format($total, 2, ',', '.'));
            }

            $tplText = "✅ *Pedido confirmado!*\n\n"
                ."Olá, {nome}! Recebemos o pedido *#{pedido}* e já estamos separando tudo com carinho.\n\n"
                ."🧾 *Resumo do pedido:*\n"
                .implode("\n", $itemsLines)
                ."\n\n💰 Total: R$ {valor}\n\n"
                ."Assim que a entrega estiver a caminho, avisaremos por aqui!";
        } elseif ($newCode === 'ready') {
            $tplText = "🚨 *Pedido pronto para entrega!*\n\n"
                ."Olá, {nome}! O pedido *#{pedido}* já está pronto e aguardando a coleta do entregador.\n\n"
                ."Obrigado por comprar com a Olika!";
        } elseif ($newCode === 'out_for_delivery' || ($statusCodeForLookup === 'out_for_delivery' && $newCode !== 'ready')) {
            $tplText = "🚚 *Pedido a caminho!*\n\n"
                ."Olá, {nome}! O pedido *#{pedido}* saiu para entrega e está a caminho.\n";

            if (!empty($deliveryNote)) {
                $tplText .= "\n📝 Observações do entregador:\n{observacao}\n";
            }

            $tplText .= "\nAcompanhe por aqui e, se precisar, é só nos chamar!";
        } elseif ($newCode === 'delivered') {
            $tplText = "🎉 *Pedido entregue!*\n\n"
                ."Olá, {nome}! Confirmamos que o pedido *#{pedido}* foi entregue com sucesso.\n";

            if (!empty($deliveryNote)) {
                $tplText .= "\n📝 Observações da entrega:\n{observacao}\n";
            }

            $tplText .= "\nAgradecemos a preferência e esperamos que aproveite! 😋";
        }

        // Dispara WhatsApp se marcado (pular se skipNotifications estiver ativo)
        if (!$skipNotifications) {
            try {
                $wa = new WhatsAppService();
                
                // Verificar se o serviço está habilitado
                if (!$wa->isEnabled()) {
                    Log::warning('OrderStatusService: WhatsAppService desabilitado - Nenhuma instância conectada', [
                        'order_id' => $order->id,
                        'status_code' => $newCode
                    ]);
                } else {
                    // Cliente
                    if ($shouldNotifyCustomer && $order->customer && $order->customer->phone) {
                        $phoneNormalized = preg_replace('/\D/', '', $order->customer->phone);
                        // Adicionar código do país se não tiver
                        if (strlen($phoneNormalized) >= 10 && !str_starts_with($phoneNormalized, '55')) {
                            $phoneNormalized = '55' . $phoneNormalized;
                        }
                        
                        // Processar variáveis no template
                        $processedText = $tplText;
                        foreach ($vars as $key => $value) {
                            $processedText = str_replace("{{$key}}", $value, $processedText);
                        }
                        
                        // Verificar se é confirmação de pagamento - usar número padrão
                        $isPaymentConfirmation = in_array($statusCodeForLookup, ['paid', 'confirmed']) || 
                                                 $order->payment_status === 'paid';
                        
                        if ($isPaymentConfirmation) {
                            // Buscar número padrão para confirmações de pagamento
                            $defaultPhone = DB::table('whatsapp_settings')
                                ->where('active', 1)
                                ->value('default_payment_confirmation_phone');
                            
                            // Se não tiver configurado, usar o número padrão do WhatsApp
                            if (empty($defaultPhone)) {
                                $defaultPhone = DB::table('whatsapp_settings')
                                    ->where('active', 1)
                                    ->value('whatsapp_phone');
                            }
                            
                            if ($defaultPhone) {
                                // Buscar configuração do WhatsApp para obter API URL
                                $whatsappSettings = DB::table('whatsapp_settings')
                                    ->where('active', 1)
                                    ->first();
                                
                                // Buscar instância pela API URL ou pelo número
                                $defaultInstance = null;
                                if ($whatsappSettings && $whatsappSettings->api_url) {
                                    $defaultInstance = \App\Models\WhatsappInstance::where('api_url', $whatsappSettings->api_url)
                                        ->orWhere('phone_number', $defaultPhone)
                                        ->orWhere('phone_number', preg_replace('/\D/', '', $defaultPhone))
                                        ->first();
                                }
                                
                                // Se não encontrou, buscar primeira instância conectada
                                if (!$defaultInstance) {
                                    $defaultInstance = \App\Models\WhatsappInstance::where('status', 'CONNECTED')
                                        ->orderBy('id')
                                        ->first();
                                }
                                
                                if ($defaultInstance) {
                                    Log::info('OrderStatusService: Usando número padrão para confirmação de pagamento', [
                                        'order_id' => $order->id,
                                        'phone_customer' => $phoneNormalized,
                                        'default_phone' => $defaultPhone,
                                        'instance_name' => $defaultInstance->name,
                                        'instance_api_url' => $defaultInstance->api_url,
                                        'status_code' => $statusCodeForLookup
                                    ]);
                                    
                                    // Usar sendFromInstance com a instância padrão
                                    $result = $wa->sendFromInstance($defaultInstance, $phoneNormalized, $processedText);
                                } else {
                                    Log::warning('OrderStatusService: Instância padrão não encontrada, usando roteamento automático', [
                                        'order_id' => $order->id,
                                        'default_phone' => $defaultPhone,
                                    ]);
                                    // Fallback para roteamento automático
                                    $result = $wa->sendText($phoneNormalized, $processedText);
                                }
                            } else {
                                Log::warning('OrderStatusService: Número padrão não configurado, usando roteamento automático', [
                                    'order_id' => $order->id,
                                ]);
                                // Fallback para roteamento automático
                                $result = $wa->sendText($phoneNormalized, $processedText);
                            }
                        } else {
                            Log::info('OrderStatusService: Enviando WhatsApp para cliente (roteamento automático)', [
                                'order_id' => $order->id,
                                'phone' => $phoneNormalized,
                                'template' => substr($processedText, 0, 50) . '...',
                                'status_code' => $statusCodeForLookup
                            ]);
                            
                            // Usar sendText que agora faz roteamento automático
                            $result = $wa->sendText($phoneNormalized, $processedText);
                        }
                        
                        if (isset($result['success']) && $result['success']) {
                            Log::info('OrderStatusService: WhatsApp enviado com sucesso', [
                                'order_id' => $order->id,
                                'phone' => $phoneNormalized
                            ]);
                            
                            // Marcar mensagens pendentes deste pedido como enviadas
                            DB::table('whatsapp_failed_messages')
                                ->where('order_id', $order->id)
                                ->where('recipient_phone', $phoneNormalized)
                                ->where('status', 'pending')
                                ->update([
                                    'status' => 'sent',
                                    'sent_at' => now(),
                                    'updated_at' => now()
                                ]);
                        } else {
                            $errorMessage = $result['error'] ?? 'Erro desconhecido';
                            $errorType = 'api_error';
                            
                            // Determinar tipo de erro
                            if (isset($result['connection_error']) && $result['connection_error']) {
                                $errorType = 'connection';
                            } elseif (isset($result['http_status']) && $result['http_status'] >= 500) {
                                $errorType = 'server_error';
                            } elseif (isset($result['http_status']) && $result['http_status'] >= 400) {
                                $errorType = 'client_error';
                            }
                            
                            Log::warning('OrderStatusService: Falha ao enviar WhatsApp', [
                                'order_id' => $order->id,
                                'phone' => $phoneNormalized,
                                'error' => $errorMessage,
                                'error_type' => $errorType
                            ]);
                            
                            // Salvar falha no banco de dados
                            try {
                                DB::table('whatsapp_failed_messages')->insert([
                                    'order_id' => $order->id,
                                    'recipient_phone' => $phoneNormalized,
                                    'message' => $processedText,
                                    'error_message' => $errorMessage,
                                    'error_type' => $errorType,
                                    'attempt_count' => 1,
                                    'status' => 'pending',
                                    'last_attempt_at' => now(),
                                    'metadata' => json_encode([
                                        'status_code' => $statusCodeForLookup,
                                        'order_number' => $order->order_number ?? null,
                                        'customer_name' => $order->customer->name ?? null,
                                    ]),
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ]);
                            } catch (\Exception $e) {
                                Log::error('OrderStatusService: Erro ao salvar falha no banco', [
                                    'order_id' => $order->id,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }
                    }

                    // Admin (opcional)
                    if ($shouldNotifyAdmin) {
                        // Buscar número do admin do banco de dados (configuração ativa do WhatsApp)
                        $whatsappSettings = DB::table('whatsapp_settings')
                            ->where('active', 1)
                            ->first();
                        
                        $admin = $whatsappSettings->admin_notification_phone ?? null;
                        
                        // Fallback para .env se não estiver configurado no banco
                        if (!$admin) {
                            $admin = env('WHATSAPP_ADMIN_NUMBER');
                        }
                        
                        if (!$admin) {
                            Log::warning('OrderStatusService: notify_admin está ativado mas número do admin não está configurado', [
                                'order_id' => $order->id,
                                'status_code' => $statusCodeForLookup,
                                'status_name' => $st->name ?? 'N/A',
                                'hint' => 'Configure o número em Dashboard > Integrações > WhatsApp > Configurações'
                            ]);
                        } elseif ($st) {
                            $msgAdmin = "🔔 *Pedido #{$order->order_number} mudou de status: {$st->name}*\n\n";
                            $msgAdmin .= "Cliente: " . ($order->customer->name ?? 'N/A') . "\n";
                            $msgAdmin .= "Valor: R$ " . number_format($order->final_amount ?? $order->total_amount ?? 0, 2, ',', '.') . "\n";
                            if ($statusCodeForLookup === 'paid' || $newCode === 'paid') {
                                $msgAdmin .= "\n💰 *Pagamento confirmado!*";
                            }
                            
                            Log::info('OrderStatusService: Enviando notificação para admin', [
                                'order_id' => $order->id,
                                'order_number' => $order->order_number,
                                'admin_phone' => $admin,
                                'status_code' => $statusCodeForLookup
                            ]);
                            
                            // O serviço vai tentar encontrar uma instância para enviar
                            // Como o admin não é um cliente, pode usar qualquer instância, 
                            // mas o router vai tentar achar pelo telefone. 
                            // Se não achar, vai usar fallback.
                            $result = $wa->sendText($admin, $msgAdmin);
                            
                            if (isset($result['success']) && $result['success']) {
                                Log::info('OrderStatusService: Notificação de admin enviada com sucesso', [
                                    'order_id' => $order->id,
                                    'admin_phone' => $admin
                                ]);
                            } else {
                                Log::warning('OrderStatusService: Falha ao enviar notificação de admin', [
                                    'order_id' => $order->id,
                                    'admin_phone' => $admin,
                                    'error' => $result['error'] ?? 'Erro desconhecido'
                                ]);
                            }
                        } else {
                            Log::warning('OrderStatusService: notify_admin está ativado mas status não foi encontrado', [
                                'order_id' => $order->id,
                                'status_code' => $statusCodeForLookup,
                                'new_code' => $newCode
                            ]);
                        }
                    } else {
                        Log::debug('OrderStatusService: notify_admin está desativado para este status', [
                            'order_id' => $order->id,
                            'status_code' => $statusCodeForLookup,
                            'status_name' => $st->name ?? 'N/A'
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                Log::error('WhatsApp status notify error', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        } else {
            Log::info('OrderStatusService: Notificações puladas (skipNotifications=true)', [
                'order_id' => $order->id,
                'status_code' => $newCode
            ]);
        }

        if (!$skipNotifications) {
            $this->dispatchOrderEvent($order->fresh(['customer', 'items']), $newCode, $deliveryNote);
        }
    }

    /**
     * Mapeia status internos do pedido para eventos públicos consumidos pelo bot WhatsApp.
     */
    private function dispatchOrderEvent(Order $order, string $status, ?string $note = null): void
    {
        $map = [
            'pending' => 'order_created',
            'confirmed' => 'order_created',
            'preparing' => 'order_preparing',
            'ready' => 'order_ready',
            'out_for_delivery' => 'order_ready', // Mapeia para order_ready (pedido a caminho)
            'delivered' => 'order_completed',
        ];

        if (!isset($map[$status])) {
            Log::debug('Status não mapeado para evento WhatsApp', [
                'status' => $status,
                'order_id' => $order->id,
            ]);
            return;
        }

        $eventType = $map[$status];
        
        Log::info('📨 Disparando evento OrderStatusUpdated', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $status,
            'event' => $eventType,
        ]);
        
        event(new OrderStatusUpdated($order, $eventType, $note));
    }
}

