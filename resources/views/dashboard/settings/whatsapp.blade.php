@extends('dashboard.layouts.app')

@section('title', 'WhatsApp - OLIKA Dashboard')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
    <div>
        <h1 class="text-3xl font-bold tracking-tight">Integração WhatsApp</h1>
        <p class="text-muted-foreground">Configure mensagens automáticas via WhatsApp</p>
    </div>

    @if(session('success'))
        <div class="rounded-lg border bg-green-50 text-green-900 px-4 py-3">{{ session('success') }}</div>
    @endif

    @if(session('ok'))
        <div class="rounded-lg border bg-green-50 text-green-900 px-4 py-3">{{ session('ok') }}</div>
    @endif

    @if($errors->any())
        <div class="rounded-lg border bg-red-50 text-red-900 px-4 py-3">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-6 pt-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Templates Ativos</p>
                        <p class="text-2xl font-bold">{{ $stats['active_templates'] ?? 0 }}</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text h-8 w-8 text-primary">
                        <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" x2="8" y1="13" y2="13"></line>
                        <line x1="16" x2="8" y1="17" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                </div>
            </div>
        </div>
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-6 pt-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Total de Templates</p>
                        <p class="text-2xl font-bold">{{ $stats['total_templates'] ?? 0 }}</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-folder h-8 w-8 text-primary">
                        <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-6 pt-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Status Configurados</p>
                        <p class="text-2xl font-bold">{{ $stats['total_statuses'] ?? 0 }}</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-list-checks h-8 w-8 text-primary">
                        <path d="m3 17 2 2 4-4"></path>
                        <path d="M3 7l2 2 4-4"></path>
                        <path d="M13 6h8"></path>
                        <path d="M13 12h8"></path>
                        <path d="M13 18h8"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-6 pt-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Notificações Ativas</p>
                        <p class="text-2xl font-bold">{{ $stats['statuses_with_notifications'] ?? 0 }}</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bell h-8 w-8 text-primary">
                        <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div dir="ltr" data-orientation="horizontal" class="space-y-4">
        <div role="tablist" aria-orientation="horizontal" class="h-10 items-center justify-center rounded-md bg-muted p-1 text-muted-foreground grid w-full grid-cols-3">
            <button type="button" role="tab" data-tab="settings" class="tab-button inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 active">Configurações</button>
            <button type="button" role="tab" data-tab="templates" class="tab-button inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">Templates</button>
            <button type="button" role="tab" data-tab="notifications" class="tab-button inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">Notificações</button>
        </div>

        <div data-tab-content="settings" class="tab-content mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-col space-y-1.5 p-6">
                    <h3 class="text-2xl font-semibold leading-none tracking-tight">Configurações da Integração</h3>
                    <p class="text-sm text-muted-foreground">Configure a conexão com WhatsApp Business API</p>
                </div>
                <form action="{{ route('dashboard.settings.whatsapp.save') }}" method="POST" class="p-6 pt-0 space-y-6">
                    @csrf
                    <div class="flex items-center justify-between p-4 border rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 {{ $row ? 'bg-green-100' : 'bg-gray-100' }} rounded-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-circle h-6 w-6 {{ $row ? 'text-green-600' : 'text-gray-400' }}">
                                    <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold">Status da Conexão</p>
                                <p class="text-sm text-muted-foreground">{{ $row ? 'WhatsApp Business configurado' : 'Configure as credenciais abaixo' }}</p>
                            </div>
                        </div>
                        <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 {{ $row ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">{{ $row ? 'Configurado' : 'Não Configurado' }}</div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none" for="instance_name">Nome da Instância</label>
                        <input type="text" name="instance_name" id="instance_name" value="{{ old('instance_name', $row->instance_name ?? '') }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" placeholder="ex.: olika-prod" required>
                        <p class="text-xs text-muted-foreground">Nome da instância do WhatsApp Business</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none" for="api_url">URL da API</label>
                        <input type="url" name="api_url" id="api_url" value="{{ old('api_url', $row->api_url ?? '') }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" placeholder="https://api.whatsapp.com" required>
                        <p class="text-xs text-muted-foreground">URL base da API do WhatsApp Business</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none" for="api_key">API Key</label>
                        <input type="password" name="api_key" id="api_key" value="{{ old('api_key', $row->api_key ?? '') }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" placeholder="••••••••••••••••" required>
                        <p class="text-xs text-muted-foreground">Chave de API para autenticação</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none" for="sender_name">Nome do Remetente</label>
                        <input type="text" name="sender_name" id="sender_name" value="{{ old('sender_name', optional($row)->sender_name ?? 'Olika Bot') }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" placeholder="Olika Bot">
                        <p class="text-xs text-muted-foreground">Nome que aparecerá nas mensagens enviadas</p>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 flex-1">Salvar Configurações</button>
                    </div>
                </form>
            </div>
        </div>

        <div data-tab-content="templates" class="tab-content mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 hidden">
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-col space-y-1.5 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-semibold leading-none tracking-tight">Templates de Mensagem</h3>
                            <p class="text-sm text-muted-foreground">Gerencie templates utilizados nos status dos pedidos</p>
                        </div>
                        <a href="{{ route('dashboard.status-templates.index') }}" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4">Gerenciar Templates</a>
                    </div>
                </div>
                <div class="p-6 pt-0">
                    <div class="space-y-3">
                        @forelse($templates as $template)
                            <div class="rounded-lg border p-4 hover:bg-muted/50">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-semibold">{{ $template->slug }}</span>
                                            @if($template->active)
                                                <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-semibold bg-success text-success-foreground">Ativo</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-semibold bg-muted text-muted-foreground">Inativo</span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-muted-foreground whitespace-pre-wrap">{{ Str::limit($template->content, 150) }}</p>
                                        <p class="text-xs text-muted-foreground mt-2">Usado por: 
                                            @php
                                                $usedBy = $statuses->where('whatsapp_template_id', $template->id)->pluck('name')->join(', ') ?: 'Nenhum status';
                                            @endphp
                                            {{ $usedBy }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-lg border p-8 text-center text-muted-foreground">
                                <p>Nenhum template cadastrado ainda.</p>
                                <a href="{{ route('dashboard.status-templates.index') }}" class="text-primary hover:underline mt-2 inline-block">Criar primeiro template</a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div data-tab-content="notifications" class="tab-content mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 hidden">
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-col space-y-1.5 p-6">
                    <h3 class="text-2xl font-semibold leading-none tracking-tight">Notificações Automáticas</h3>
                    <p class="text-sm text-muted-foreground">Configure quais status devem enviar notificações automáticas</p>
                </div>
                <form action="{{ route('dashboard.settings.whatsapp.notifications.save') }}" method="POST" class="p-6 pt-0 space-y-4">
                    @csrf
                    @forelse($statuses as $status)
                        <div class="rounded-lg border p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold">{{ $status->name }}</p>
                                    <p class="text-sm text-muted-foreground">Código: {{ $status->code }}</p>
                                    @if($status->template_slug)
                                        <p class="text-xs text-muted-foreground mt-1">Template: <span class="font-medium">{{ $status->template_slug }}</span></p>
                                    @else
                                        <p class="text-xs text-amber-600 mt-1">⚠️ Nenhum template associado</p>
                                    @endif
                                </div>
                            </div>
                            <div class="grid md:grid-cols-2 gap-4">
                                <label class="flex items-center justify-between cursor-pointer">
                                    <div>
                                        <span class="font-medium text-sm">Notificar Cliente</span>
                                        <p class="text-xs text-muted-foreground">Enviar mensagem para o cliente quando o pedido mudar para este status</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="notifications[{{ $status->id }}][customer]" value="1" {{ $status->notify_customer ? 'checked' : '' }} class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                    </label>
                                </label>
                                <label class="flex items-center justify-between cursor-pointer">
                                    <div>
                                        <span class="font-medium text-sm">Notificar Admin</span>
                                        <p class="text-xs text-muted-foreground">Enviar mensagem para o administrador</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="notifications[{{ $status->id }}][admin]" value="1" {{ $status->notify_admin ? 'checked' : '' }} class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                    </label>
                                </label>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-lg border p-8 text-center text-muted-foreground">
                            <p>Nenhum status cadastrado ainda.</p>
                            <a href="{{ route('dashboard.status-templates.index') }}" class="text-primary hover:underline mt-2 inline-block">Gerenciar status</a>
                        </div>
                    @endforelse
                    
                    @if($statuses->count() > 0)
                        <button type="submit" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 w-full">Salvar Configurações de Notificações</button>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.tab-button.active {
    background-color: hsl(var(--background));
    color: hsl(var(--foreground));
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remove active state from all tabs
            tabs.forEach(t => {
                t.classList.remove('active');
            });
            
            // Hide all tab contents
            tabContents.forEach(content => {
                content.classList.add('hidden');
            });
            
            // Show selected tab content
            const selectedContent = document.querySelector(`[data-tab-content="${targetTab}"]`);
            if (selectedContent) {
                selectedContent.classList.remove('hidden');
            }
            
            // Add active state to clicked tab
            this.classList.add('active');
        });
    });
});
</script>
@endpush
@endsection
