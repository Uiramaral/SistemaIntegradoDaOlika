@extends('layouts.admin')

@section('title', 'Instâncias WhatsApp')
@section('page_title', 'Instâncias WhatsApp')
@section('page_subtitle', 'Gerencie as URLs de instâncias WhatsApp no Railway')

@section('page_actions')
    <div class="flex items-center gap-2">
        <form action="{{ route('master.whatsapp-urls.health-check-all') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-secondary text-secondary-foreground rounded-md hover:bg-secondary/80 transition">
                <i data-lucide="activity" class="h-4 w-4"></i>
                Verificar Todas
            </button>
        </form>
        <a href="{{ route('master.whatsapp-urls.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition">
            <i data-lucide="plus" class="h-4 w-4"></i>
            Nova Instância
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-lg border border-success/30 bg-success/10 p-4 text-success">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="rounded-lg border border-destructive/30 bg-destructive/10 p-4 text-destructive">
            {{ session('error') }}
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="rounded-lg border border-border bg-card p-4 shadow-sm">
            <p class="text-sm text-muted-foreground">Total de Instâncias</p>
            <p class="text-2xl font-bold text-foreground">{{ $instances->count() }}</p>
        </div>
        <div class="rounded-lg border border-border bg-card p-4 shadow-sm">
            <p class="text-sm text-muted-foreground">Disponíveis</p>
            <p class="text-2xl font-bold text-success">{{ $instances->where('status', 'available')->count() }}</p>
        </div>
        <div class="rounded-lg border border-border bg-card p-4 shadow-sm">
            <p class="text-sm text-muted-foreground">Em Uso</p>
            <p class="text-2xl font-bold text-primary">{{ $instances->where('status', 'in_use')->count() }}</p>
        </div>
        <div class="rounded-lg border border-border bg-card p-4 shadow-sm">
            <p class="text-sm text-muted-foreground">Offline/Manutenção</p>
            <p class="text-2xl font-bold text-warning">{{ $instances->whereIn('status', ['offline', 'maintenance'])->count() }}</p>
        </div>
    </div>

    {{-- Tabela de Instâncias --}}
    <div class="rounded-lg border border-border bg-card shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-muted/50">
                    <tr>
                        <th class="text-left px-4 py-3 text-sm font-medium text-muted-foreground">Instância</th>
                        <th class="text-left px-4 py-3 text-sm font-medium text-muted-foreground">URL</th>
                        <th class="text-left px-4 py-3 text-sm font-medium text-muted-foreground">Status</th>
                        <th class="text-left px-4 py-3 text-sm font-medium text-muted-foreground">Cliente</th>
                        <th class="text-left px-4 py-3 text-sm font-medium text-muted-foreground">Último Check</th>
                        <th class="text-right px-4 py-3 text-sm font-medium text-muted-foreground">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($instances as $instance)
                        <tr class="hover:bg-muted/30 transition">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center
                                        {{ $instance->status === 'available' ? 'bg-success/10' : '' }}
                                        {{ $instance->status === 'in_use' ? 'bg-primary/10' : '' }}
                                        {{ $instance->status === 'offline' ? 'bg-destructive/10' : '' }}
                                        {{ $instance->status === 'maintenance' ? 'bg-warning/10' : '' }}">
                                        <i data-lucide="message-circle" class="h-5 w-5
                                            {{ $instance->status === 'available' ? 'text-success' : '' }}
                                            {{ $instance->status === 'in_use' ? 'text-primary' : '' }}
                                            {{ $instance->status === 'offline' ? 'text-destructive' : '' }}
                                            {{ $instance->status === 'maintenance' ? 'text-warning' : '' }}"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-foreground">{{ $instance->name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ $instance->description ?? 'Sem descrição' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <code class="text-xs bg-muted px-2 py-1 rounded break-all max-w-xs inline-block">
                                    {{ Str::limit($instance->url, 40) }}
                                </code>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $statusColors = [
                                        'available' => 'bg-success/10 text-success',
                                        'in_use' => 'bg-primary/10 text-primary',
                                        'offline' => 'bg-destructive/10 text-destructive',
                                        'maintenance' => 'bg-warning/10 text-warning',
                                    ];
                                    $statusLabels = [
                                        'available' => 'Disponível',
                                        'in_use' => 'Em Uso',
                                        'offline' => 'Offline',
                                        'maintenance' => 'Manutenção',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $statusColors[$instance->status] ?? 'bg-muted text-muted-foreground' }}">
                                    {{ $statusLabels[$instance->status] ?? $instance->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($instance->client)
                                    <a href="{{ route('master.clients.show', $instance->client) }}" class="text-sm text-primary hover:underline">
                                        {{ $instance->client->name }}
                                    </a>
                                @else
                                    <span class="text-sm text-muted-foreground">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($instance->last_health_check)
                                    <p class="text-sm text-foreground">{{ $instance->last_health_check->format('d/m/Y H:i') }}</p>
                                    <p class="text-xs {{ $instance->is_healthy ? 'text-success' : 'text-destructive' }}">
                                        {{ $instance->is_healthy ? 'Saudável' : 'Com problemas' }}
                                    </p>
                                @else
                                    <span class="text-sm text-muted-foreground">Nunca verificado</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" onclick="doHealthCheck({{ $instance->id }}, '{{ $instance->name }}')" 
                                            class="p-2 rounded-md hover:bg-muted transition" title="Verificar saúde" id="health-btn-{{ $instance->id }}">
                                        <i data-lucide="activity" class="h-4 w-4 text-muted-foreground" id="health-icon-{{ $instance->id }}"></i>
                                    </button>
                                    
                                    @if($instance->status === 'available')
                                        <button type="button" 
                                                onclick="openAssignModal({{ $instance->id }}, '{{ $instance->name }}')"
                                                class="p-2 rounded-md hover:bg-muted transition" title="Atribuir a cliente">
                                            <i data-lucide="link" class="h-4 w-4 text-primary"></i>
                                        </button>
                                    @elseif($instance->status === 'in_use')
                                        <form action="{{ route('master.whatsapp-urls.release', $instance) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Tem certeza que deseja liberar esta instância?')">
                                            @csrf
                                            <button type="submit" class="p-2 rounded-md hover:bg-muted transition" title="Liberar instância">
                                                <i data-lucide="unlink" class="h-4 w-4 text-warning"></i>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <form action="{{ route('master.whatsapp-urls.maintenance', $instance) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="p-2 rounded-md hover:bg-muted transition" 
                                                title="{{ $instance->status === 'maintenance' ? 'Tirar de manutenção' : 'Colocar em manutenção' }}">
                                            <i data-lucide="wrench" class="h-4 w-4 {{ $instance->status === 'maintenance' ? 'text-warning' : 'text-muted-foreground' }}"></i>
                                        </button>
                                    </form>
                                    
                                    <a href="{{ route('master.whatsapp-urls.edit', $instance) }}" 
                                       class="p-2 rounded-md hover:bg-muted transition" title="Editar">
                                        <i data-lucide="edit" class="h-4 w-4 text-muted-foreground"></i>
                                    </a>
                                    
                                    @if(!$instance->client_id)
                                        <form action="{{ route('master.whatsapp-urls.destroy', $instance) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Tem certeza que deseja excluir esta instância?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 rounded-md hover:bg-destructive/10 transition" title="Excluir">
                                                <i data-lucide="trash-2" class="h-4 w-4 text-destructive"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">
                                <i data-lucide="message-circle" class="h-12 w-12 mx-auto mb-3 opacity-50"></i>
                                <p>Nenhuma instância cadastrada</p>
                                <a href="{{ route('master.whatsapp-urls.create') }}" class="text-primary hover:underline mt-2 inline-block">
                                    Adicionar primeira instância
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal para Atribuir Cliente --}}
<div id="assignModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
    <div class="bg-card rounded-lg border border-border shadow-lg w-full max-w-md mx-4">
        <div class="p-6 border-b border-border">
            <h3 class="text-lg font-semibold text-foreground">Atribuir Instância</h3>
            <p class="text-sm text-muted-foreground" id="assignModalInstanceName"></p>
        </div>
        <form id="assignForm" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label for="client_id" class="block text-sm font-medium text-foreground mb-1">Selecionar Cliente</label>
                <select name="client_id" id="client_id" required
                        class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">Selecione um cliente...</option>
                    @foreach($clients ?? [] as $client)
                        <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->slug }})</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="flex-1 px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition">
                    Atribuir
                </button>
                <button type="button" onclick="closeAssignModal()" class="px-4 py-2 bg-secondary text-secondary-foreground rounded-md hover:bg-secondary/80 transition">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal de Resultado Health Check --}}
<div id="healthCheckModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
    <div class="bg-card rounded-lg border border-border shadow-lg w-full max-w-md mx-4">
        <div class="p-6 border-b border-border flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-foreground">Health Check</h3>
                <p class="text-sm text-muted-foreground" id="healthCheckInstanceName"></p>
            </div>
            <button type="button" onclick="closeHealthCheckModal()" class="p-2 rounded-md hover:bg-muted transition">
                <i data-lucide="x" class="h-5 w-5 text-muted-foreground"></i>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <div id="healthCheckLoading" class="flex items-center justify-center py-8">
                <i data-lucide="loader-2" class="h-8 w-8 animate-spin text-primary"></i>
                <span class="ml-2 text-muted-foreground">Verificando...</span>
            </div>
            <div id="healthCheckResult" class="hidden">
                <div id="healthCheckSuccess" class="hidden p-4 rounded-lg bg-success/10 border border-success/30">
                    <div class="flex items-center gap-3">
                        <i data-lucide="check-circle" class="h-8 w-8 text-success"></i>
                        <div>
                            <p class="font-semibold text-success">Instância Saudável</p>
                            <p class="text-sm text-muted-foreground">O serviço está respondendo normalmente.</p>
                        </div>
                    </div>
                </div>
                <div id="healthCheckError" class="hidden p-4 rounded-lg bg-destructive/10 border border-destructive/30">
                    <div class="flex items-center gap-3">
                        <i data-lucide="x-circle" class="h-8 w-8 text-destructive"></i>
                        <div>
                            <p class="font-semibold text-destructive">Instância com Problemas</p>
                            <p class="text-sm text-muted-foreground">O serviço não está respondendo ou está offline.</p>
                        </div>
                    </div>
                </div>
                <div class="mt-4 text-sm text-muted-foreground">
                    <p><strong>URL:</strong> <span id="healthCheckUrl"></span></p>
                    <p><strong>Verificado em:</strong> <span id="healthCheckTime"></span></p>
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-border flex justify-end">
            <button type="button" onclick="closeHealthCheckModal()" class="px-4 py-2 bg-secondary text-secondary-foreground rounded-md hover:bg-secondary/80 transition">
                Fechar
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openAssignModal(instanceId, instanceName) {
    document.getElementById('assignModalInstanceName').textContent = instanceName;
    document.getElementById('assignForm').action = '/master/whatsapp-urls/' + instanceId + '/assign';
    document.getElementById('assignModal').classList.remove('hidden');
    document.getElementById('assignModal').classList.add('flex');
}

function closeAssignModal() {
    document.getElementById('assignModal').classList.add('hidden');
    document.getElementById('assignModal').classList.remove('flex');
}

document.getElementById('assignModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeAssignModal();
    }
});

// Health Check functions
function doHealthCheck(instanceId, instanceName) {
    // Mostrar modal
    document.getElementById('healthCheckInstanceName').textContent = instanceName;
    document.getElementById('healthCheckLoading').classList.remove('hidden');
    document.getElementById('healthCheckResult').classList.add('hidden');
    document.getElementById('healthCheckSuccess').classList.add('hidden');
    document.getElementById('healthCheckError').classList.add('hidden');
    
    document.getElementById('healthCheckModal').classList.remove('hidden');
    document.getElementById('healthCheckModal').classList.add('flex');
    
    // Reinicializar ícones
    if (typeof lucide !== 'undefined') lucide.createIcons();
    
    // Fazer requisição AJAX
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    fetch('/master/whatsapp-urls/' + instanceId + '/health-check', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        // Esconder loading, mostrar resultado
        document.getElementById('healthCheckLoading').classList.add('hidden');
        document.getElementById('healthCheckResult').classList.remove('hidden');
        
        if (data.healthy) {
            document.getElementById('healthCheckSuccess').classList.remove('hidden');
        } else {
            document.getElementById('healthCheckError').classList.remove('hidden');
        }
        
        document.getElementById('healthCheckUrl').textContent = data.url || '-';
        document.getElementById('healthCheckTime').textContent = data.last_check || new Date().toLocaleString('pt-BR');
        
        // Reinicializar ícones
        if (typeof lucide !== 'undefined') lucide.createIcons();
    })
    .catch(error => {
        console.error('Erro:', error);
        document.getElementById('healthCheckLoading').classList.add('hidden');
        document.getElementById('healthCheckResult').classList.remove('hidden');
        document.getElementById('healthCheckError').classList.remove('hidden');
        document.getElementById('healthCheckUrl').textContent = '-';
        document.getElementById('healthCheckTime').textContent = new Date().toLocaleString('pt-BR');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
}

function closeHealthCheckModal() {
    document.getElementById('healthCheckModal').classList.add('hidden');
    document.getElementById('healthCheckModal').classList.remove('flex');
    // Recarregar para atualizar status na lista
    window.location.reload();
}

document.getElementById('healthCheckModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeHealthCheckModal();
    }
});
</script>
@endpush
@endsection
