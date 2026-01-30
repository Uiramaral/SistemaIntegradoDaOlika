@extends('dashboard.layouts.app')

@section('page_title', 'Notificações')
@section('page_subtitle', 'Gere os lembretes de entrega das suas encomendas')

@push('styles')
<style>
    .notification-card {
        @apply bg-white rounded-lg border border-gray-200 p-6 shadow-sm;
    }
    .status-badge {
        @apply px-3 py-1 rounded-full text-xs font-semibold;
    }
    .status-badge.active {
        @apply bg-green-100 text-green-800;
    }
    .status-badge.inactive {
        @apply bg-gray-100 text-gray-600;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-lg border bg-green-50 text-green-900 px-4 py-3">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="rounded-lg border bg-red-50 text-red-900 px-4 py-3">{{ session('error') }}</div>
    @endif

    @php
        // Verificar se há WhatsApp conectado
        $clientId = currentClientId();
        $whatsappConnected = false;
        $whatsappPhone = null;
        
        try {
            $connectedInstance = \App\Models\WhatsappInstance::where('client_id', $clientId)
                ->where('status', 'CONNECTED')
                ->first();
            
            if ($connectedInstance) {
                $whatsappConnected = true;
                $whatsappPhone = $connectedInstance->phone_number;
            }
        } catch (\Exception $e) {
            // Se a tabela não existir, considerar como não conectado
            $whatsappConnected = false;
        }
    @endphp

    <!-- Status das Notificações -->
    @if($whatsappConnected)
        <div class="notification-card bg-green-50 border-green-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-green-500 flex items-center justify-center">
                        <i data-lucide="check" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-foreground">Notificações ativadas</h3>
                        <p class="text-sm text-muted-foreground">Vais receber lembretes das tuas entregas</p>
                    </div>
                </div>
                <span class="status-badge active">WhatsApp Ativo</span>
            </div>
            @if($whatsappPhone)
                <div class="mt-4 p-3 bg-white rounded-lg">
                    <p class="text-sm text-muted-foreground">Número conectado</p>
                    <p class="font-semibold text-foreground">{{ $whatsappPhone }}</p>
                </div>
            @endif
        </div>
    @else
        <div class="notification-card bg-yellow-50 border-yellow-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-yellow-500 flex items-center justify-center">
                        <i data-lucide="alert-circle" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-foreground">WhatsApp não conectado</h3>
                        <p class="text-sm text-muted-foreground">Conecte o WhatsApp para ativar as notificações</p>
                    </div>
                </div>
                <a href="{{ route('dashboard.settings.whatsapp') }}" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium bg-orange-500 text-white hover:bg-orange-600 h-10 px-4">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    Conectar WhatsApp
                </a>
            </div>
        </div>
    @endif

    @if($whatsappConnected)
        <!-- Configurações de Notificações -->
        <div class="notification-card">
            <div class="flex flex-col space-y-1.5 mb-6">
                <h3 class="text-xl font-semibold leading-none tracking-tight">Configurações de Notificações</h3>
                <p class="text-sm text-muted-foreground">Configure quando enviar notificações via WhatsApp</p>
            </div>
            
            <form action="{{ route('dashboard.notifications.save') }}" method="POST" class="space-y-4">
                @csrf
                
                <!-- Notificações de Pedidos -->
                <div class="space-y-4">
                    <h4 class="font-semibold text-foreground">Notificações de Pedidos</h4>
                    
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="notify_new_order" value="1" {{ ($notificationSettings['notify_new_order'] ?? true) ? 'checked' : '' }} class="rounded border-gray-300">
                            <div>
                                <span class="font-medium text-foreground">Novo pedido recebido</span>
                                <p class="text-xs text-muted-foreground">Enviar notificação quando um novo pedido for criado</p>
                            </div>
                        </label>
                        
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="notify_order_status_change" value="1" {{ ($notificationSettings['notify_order_status_change'] ?? true) ? 'checked' : '' }} class="rounded border-gray-300">
                            <div>
                                <span class="font-medium text-foreground">Mudança de status do pedido</span>
                                <p class="text-xs text-muted-foreground">Enviar notificação quando o status do pedido mudar</p>
                            </div>
                        </label>
                        
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="notify_payment_received" value="1" {{ ($notificationSettings['notify_payment_received'] ?? true) ? 'checked' : '' }} class="rounded border-gray-300">
                            <div>
                                <span class="font-medium text-foreground">Pagamento recebido</span>
                                <p class="text-xs text-muted-foreground">Enviar notificação quando um pagamento for confirmado</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Lembretes de Entrega -->
                <div class="space-y-4 pt-4 border-t">
                    <h4 class="font-semibold text-foreground">Lembretes de Entrega</h4>
                    
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="notify_delivery_reminder" value="1" {{ ($notificationSettings['notify_delivery_reminder'] ?? true) ? 'checked' : '' }} class="rounded border-gray-300">
                            <div>
                                <span class="font-medium text-foreground">Lembrete de entrega</span>
                                <p class="text-xs text-muted-foreground">Enviar lembrete antes da data de entrega</p>
                            </div>
                        </label>
                        
                        <div class="pl-7 space-y-2">
                            <label class="text-sm font-medium text-foreground">Horas antes da entrega</label>
                            <input type="number" name="delivery_reminder_hours" value="{{ $notificationSettings['delivery_reminder_hours'] ?? 24 }}" min="1" max="168" class="flex h-10 w-32 rounded-md border border-input bg-background px-3 py-2 text-sm">
                            <p class="text-xs text-muted-foreground">Enviar lembrete X horas antes da data de entrega agendada</p>
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Salvar Configurações
                    </button>
                </div>
            </form>
        </div>

        <!-- Lembretes de Entrega Ativos -->
        <div class="notification-card">
            <div class="flex flex-col space-y-1.5 mb-6">
                <h3 class="text-xl font-semibold leading-none tracking-tight">Lembretes de Entrega Ativos</h3>
                <p class="text-sm text-muted-foreground">Lembretes programados para as tuas encomendas</p>
            </div>
            
            <div class="text-center py-12">
                <i data-lucide="clock" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                <p class="text-gray-500 text-lg">Nenhum lembrete ativo</p>
                <p class="text-sm text-gray-400 mt-2">Adiciona lembretes ao criar ou editar encomendas</p>
            </div>
        </div>

        <!-- Como Funcionam os Lembretes -->
        <div class="notification-card bg-blue-50 border-blue-200">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="info" class="w-5 h-5 text-white"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-foreground mb-3">Como funcionam os lembretes?</h3>
                    <ul class="space-y-2 text-sm text-muted-foreground">
                        <li class="flex items-start gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0"></i>
                            <span>Os lembretes são enviados via WhatsApp automaticamente</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0"></i>
                            <span>Ao criar uma encomenda, ativa o lembrete na seção 'Lembrete de Produção'</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0"></i>
                            <span>Recebes uma notificação WhatsApp na data e hora configuradas</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0"></i>
                            <span>As notificações funcionam mesmo com o app fechado</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0"></i>
                            <span>Os lembretes ficam sincronizados com as tuas encomendas</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.lucide) {
        lucide.createIcons();
    }
});
</script>
@endpush
@endsection
