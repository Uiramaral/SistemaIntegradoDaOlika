@extends('dashboard.layouts.app')

@section('page_title', 'Mensagens WhatsApp Falhadas')
@section('page_subtitle', 'Gerencie e reenvie mensagens que falharam no envio')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Mensagens Falhadas</h1>
            <p class="text-sm text-muted-foreground">Mensagens que não foram enviadas com sucesso</p>
        </div>
    </div>

    @if(!empty($missingTable) && $missingTable)
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-amber-800">
            <div class="font-semibold mb-1">Conexão instável / desconectada</div>
            <p class="text-sm">
                Não foi possível listar mensagens falhadas porque a tabela de logs ainda não existe nesta instância.
                Refaça o login do seu número de WhatsApp clicando em <strong>Conectar</strong> na aba WhatsApp.
            </p>
        </div>
    @endif

    @if($failedMessages->count() > 0)
        <div class="rounded-lg border bg-card">
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($failedMessages as $message)
                        <div class="border rounded-lg p-4 hover:bg-muted/50 transition-colors" data-message-id="{{ $message->id }}">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 space-y-2">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center rounded-full bg-destructive/10 text-destructive px-2 py-1 text-xs font-medium">
                                            Falhou
                                        </span>
                                        @if($message->order_number)
                                            <span class="text-sm font-medium">Pedido: {{ $message->order_number }}</span>
                                        @endif
                                        @if($message->customer_name)
                                            <span class="text-sm text-muted-foreground">Cliente: {{ $message->customer_name }}</span>
                                        @endif
                                    </div>
                                    
                                    <div class="text-sm">
                                        <p class="font-medium">Telefone: {{ $message->recipient_phone }}</p>
                                        <p class="text-muted-foreground mt-1">{{ Str::limit($message->message, 100) }}</p>
                                    </div>
                                    
                                    <div class="flex items-center gap-4 text-xs text-muted-foreground">
                                        <span>Tentativas: {{ $message->attempt_count }}</span>
                                        <span>Última tentativa: {{ \Carbon\Carbon::parse($message->last_attempt_at)->format('d/m/Y H:i') }}</span>
                                        @if($message->error_type)
                                            <span class="capitalize">Tipo: {{ str_replace('_', ' ', $message->error_type) }}</span>
                                        @endif
                                    </div>
                                    
                                    @if($message->error_message)
                                        <div class="mt-2 p-2 bg-destructive/10 rounded text-xs text-destructive">
                                            <strong>Erro:</strong> {{ $message->error_message }}
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <button 
                                        onclick="retryMessage({{ $message->id }})" 
                                        class="inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-9 px-4"
                                        data-message-id="{{ $message->id }}"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw">
                                            <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                                            <path d="M21 3v5h-5"></path>
                                            <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                                            <path d="8 16H3v5"></path>
                                        </svg>
                                        Reenviar
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-6">
                    {{ $failedMessages->links() }}
                </div>
            </div>
        </div>
    @else
        <div class="rounded-lg border bg-card p-12 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-circle-2 mx-auto text-green-500 mb-4">
                <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path>
                <path d="m9 12 2 2 4-4"></path>
            </svg>
            <h3 class="text-lg font-semibold mb-2">Nenhuma mensagem falhada</h3>
            <p class="text-sm text-muted-foreground">Todas as mensagens foram enviadas com sucesso!</p>
        </div>
    @endif
</div>

<script>
async function retryMessage(messageId) {
    const button = document.querySelector(`[data-message-id="${messageId}"]`);
    const originalText = button.innerHTML;
    
    // Desabilitar botão e mostrar loading
    button.disabled = true;
    button.innerHTML = '<svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"></path></svg> Reenviando...';
    
    try {
        const response = await fetch(`/dashboard/whatsapp/failed-messages/${messageId}/retry`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Mostrar mensagem de sucesso
            showNotification('✅ Mensagem reenviada com sucesso!', 'success');
            
            // Remover item da lista após 1 segundo
            setTimeout(() => {
                const messageElement = document.querySelector(`[data-message-id="${messageId}"]`).closest('.border');
                if (messageElement) {
                    messageElement.style.transition = 'opacity 0.3s';
                    messageElement.style.opacity = '0';
                    setTimeout(() => {
                        messageElement.remove();
                        // Recarregar página se não houver mais mensagens
                        if (document.querySelectorAll('.border.rounded-lg.p-4').length === 0) {
                            window.location.reload();
                        }
                    }, 300);
                }
            }, 1000);
        } else {
            showNotification('❌ Erro ao reenviar: ' + (data.error || 'Erro desconhecido'), 'error');
            button.disabled = false;
            button.innerHTML = originalText;
        }
    } catch (error) {
        showNotification('❌ Erro ao processar reenvio: ' + error.message, 'error');
        button.disabled = false;
        button.innerHTML = originalText;
    }
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 rounded-lg border px-4 py-3 shadow-lg ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.transition = 'opacity 0.3s';
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}
</script>
@endsection

