<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\BotConversaService;

class OrderStatusService
{
    public function changeStatus(Order $order, string $newCode, ?string $note = null, ?int $userId = null, bool $skipHistory = false): void
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

        // Histórico (só registrar se não foi solicitado para pular)
        if (!$skipHistory) {
            // Verificar se já existe histórico recente para evitar duplicação
            $recentHistory = DB::table('order_status_history')
                ->where('order_id', $order->id)
                ->where('new_status', $newCode)
                ->where('created_at', '>=', now()->subMinute())
                ->first();
            
            if (!$recentHistory) {
                DB::table('order_status_history')->insert([
                    'order_id'   => $order->id,
                    'old_status' => $old,
                    'new_status' => $newCode,
                    'note'       => $note,
                    'user_id'    => $userId,
                    'created_at' => now(),
                ]);
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
        $vars = [
            'nome'   => optional($order->customer)->name ?? 'Cliente',
            'pedido' => (string) $order->order_number,
            'valor'  => number_format($order->final_amount ?? $order->total_amount ?? 0, 2, ',', '.'),
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

        // Dispara WhatsApp se marcado
        try {
            $wa = new WhatsAppService();
            
            // Verificar se o serviço está habilitado
            if (!$wa->isEnabled()) {
                Log::warning('OrderStatusService: WhatsAppService desabilitado - configurações não encontradas ou incompletas', [
                    'order_id' => $order->id,
                    'status_code' => $newCode
                ]);
                // Não retornar: seguir para BotConversa abaixo
            } else {
                // Cliente
                if ($shouldNotifyCustomer && $order->customer && $order->customer->phone) {
                    $phoneNormalized = preg_replace('/\D/', '', $order->customer->phone);
                    // Adicionar código do país se não tiver
                    if (strlen($phoneNormalized) === 11 && !str_starts_with($phoneNormalized, '55')) {
                        $phoneNormalized = '55' . $phoneNormalized;
                    }
                    
                    Log::info('OrderStatusService: Enviando WhatsApp para cliente', [
                        'order_id' => $order->id,
                        'phone' => $phoneNormalized,
                        'template' => substr($tplText, 0, 50) . '...',
                        'status_code' => $statusCodeForLookup
                    ]);
                    
                    $result = $wa->sendTemplate($phoneNormalized, $tplText, $vars);
                    
                    if ($result) {
                        Log::info('OrderStatusService: WhatsApp enviado com sucesso', [
                            'order_id' => $order->id,
                            'phone' => $phoneNormalized
                        ]);
                    } else {
                        Log::warning('OrderStatusService: Falha ao enviar WhatsApp', [
                            'order_id' => $order->id,
                            'phone' => $phoneNormalized
                        ]);
                    }
                } else {
                    Log::info('OrderStatusService: WhatsApp não enviado para cliente', [
                        'order_id' => $order->id,
                        'should_notify_customer' => $shouldNotifyCustomer,
                        'has_customer' => $order->customer !== null,
                        'has_phone' => $order->customer && $order->customer->phone
                    ]);
                }

                // Admin (opcional: defina número no whatsapp_settings.sender_name ou em settings)
                if ($shouldNotifyAdmin) {
                    // Substitua por um número de admin real (ou busque de settings)
                    $admin = env('WHATSAPP_ADMIN_NUMBER', '55719987654321');
                    
                    if ($admin && $st) {
                        $msgAdmin = "🔔 Pedido #{$order->order_number} mudou de status: {$st->name}";
                        $wa->sendText($admin, $msgAdmin);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('WhatsApp status notify error', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        // Enviar também via BotConversa (webhook) para todos os status
        try {
            $botConversa = new BotConversaService();
            
            if (!$botConversa->isConfigured()) {
                Log::debug('OrderStatusService: BotConversa não configurado, pulando webhook', [
                    'order_id' => $order->id,
                    'status' => $newCode
                ]);
            } else {
                // Se for status "paid" ou "confirmed", usar o método específico para pedidos pagos
                if (in_array($statusCodeForLookup, ['paid', 'confirmed']) || $newCode === 'confirmed') {
                    // Carregar relacionamentos necessários
                    $order->loadMissing('items.product', 'customer', 'address');
                    
                    Log::info('OrderStatusService: Enviando pedido pago para BotConversa', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $newCode
                    ]);
                    
                    $botConversa->sendPaidOrderJson($order);
                } else {
                    // Para outros status, enviar payload genérico
                    $phone = $botConversa->normalizePhoneBR(optional($order->customer)->phone);
                    
                    if ($phone && $order->customer) {
                        // Substituir variáveis no template
                        $message = $tplText;
                        foreach ($vars as $key => $value) {
                            $message = str_replace('{' . $key . '}', $value, $message);
                        }
                        
                        $payload = [
                            'type' => 'order_status_change',
                            'order_id' => $order->id,
                            'order_number' => (string) $order->order_number,
                            'status_code' => $newCode,
                            'status_name' => $st->name ?? $newCode,
                            'phone' => $phone,
                            'message' => $message,
                            'customer_name' => optional($order->customer)->name,
                            'final_amount' => (float)($order->final_amount ?? $order->total_amount ?? 0),
                        ];
                        
                        Log::info('OrderStatusService: Enviando mudança de status para BotConversa', [
                            'order_id' => $order->id,
                            'status' => $newCode,
                            'phone' => $phone
                        ]);
                        
                        $botConversa->send($payload);
                    } else {
                        Log::debug('OrderStatusService: Cliente sem telefone, pulando BotConversa', [
                            'order_id' => $order->id,
                            'has_customer' => $order->customer !== null,
                            'has_phone' => $order->customer && $order->customer->phone
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('OrderStatusService: Erro ao enviar para BotConversa', [
                'order_id' => $order->id,
                'status' => $newCode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Não bloquear o fluxo se o BotConversa falhar
        }
    }
}

