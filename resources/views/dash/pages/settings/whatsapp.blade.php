@extends('dash.layouts.app')

@section('title', 'WhatsApp - OLIKA Painel')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
    <div>
        <h1 class="text-3xl font-bold tracking-tight">Integração WhatsApp</h1>
        <p class="text-muted-foreground">Configure mensagens automáticas via WhatsApp</p>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-6 pt-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Mensagens Enviadas</p>
                        <p class="text-2xl font-bold">1.234</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send h-8 w-8 text-primary">
                        <path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"></path>
                        <path d="m21.854 2.147-10.94 10.939"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-6 pt-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Taxa de Leitura</p>
                        <p class="text-2xl font-bold">94%</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-check h-8 w-8 text-success">
                        <path d="M18 6 7 17l-5-5"></path>
                        <path d="m22 10-7.5 7.5L13 16"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-6 pt-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Clientes Ativos</p>
                        <p class="text-2xl font-bold">342</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users h-8 w-8 text-primary">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div dir="ltr" data-orientation="horizontal" class="space-y-4">
        <div role="tablist" aria-orientation="horizontal" class="h-10 items-center justify-center rounded-md bg-muted p-1 text-muted-foreground grid w-full grid-cols-3">
            <button type="button" role="tab" data-tab="settings" class="tab-button inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 active">Configurações</button>
            <button type="button" role="tab" data-tab="templates" class="tab-button inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">Templates</button>
            <button type="button" role="tab" data-tab="history" class="tab-button inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">Histórico</button>
        </div>

        <div data-tab-content="settings" class="tab-content mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-col space-y-1.5 p-6">
                    <h3 class="text-2xl font-semibold leading-none tracking-tight">Configurações da Integração</h3>
                    <p class="text-sm text-muted-foreground">Configure a conexão com WhatsApp Business API</p>
                </div>
                <div class="p-6 pt-0 space-y-6">
                    <div class="flex items-center justify-between p-4 border rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-success/10 rounded-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-circle h-6 w-6 text-success">
                                    <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold">Status da Conexão</p>
                                <p class="text-sm text-muted-foreground">WhatsApp Business conectado</p>
                            </div>
                        </div>
                        <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 bg-success text-success-foreground">Conectado</div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="phone">Número do WhatsApp Business</label>
                        <input type="tel" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" id="phone" placeholder="+55 11 99999-9999" value="+55 11 98765-4321">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="api-key">API Key</label>
                        <input type="password" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" id="api-key" placeholder="••••••••••••••••" value="sk_test_123456789">
                    </div>
                    <div class="space-y-4">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Notificações Automáticas</label>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium">Pedido Confirmado</p>
                                    <p class="text-sm text-muted-foreground">Enviar quando o pedido for confirmado</p>
                                </div>
                                <button type="button" role="switch" aria-checked="true" data-state="checked" value="on" class="peer inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors data-[state=checked]:bg-primary data-[state=unchecked]:bg-input focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background disabled:cursor-not-allowed disabled:opacity-50">
                                    <span data-state="checked" class="pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg ring-0 transition-transform data-[state=checked]:translate-x-5 data-[state=unchecked]:translate-x-0"></span>
                                </button>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium">Pedido em Preparo</p>
                                    <p class="text-sm text-muted-foreground">Enviar quando o pedido estiver sendo preparado</p>
                                </div>
                                <button type="button" role="switch" aria-checked="true" data-state="checked" value="on" class="peer inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors data-[state=checked]:bg-primary data-[state=unchecked]:bg-input focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background disabled:cursor-not-allowed disabled:opacity-50">
                                    <span data-state="checked" class="pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg ring-0 transition-transform data-[state=checked]:translate-x-5 data-[state=unchecked]:translate-x-0"></span>
                                </button>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium">Saiu para Entrega</p>
                                    <p class="text-sm text-muted-foreground">Enviar quando o pedido sair para entrega</p>
                                </div>
                                <button type="button" role="switch" aria-checked="true" data-state="checked" value="on" class="peer inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors data-[state=checked]:bg-primary data-[state=unchecked]:bg-input focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background disabled:cursor-not-allowed disabled:opacity-50">
                                    <span data-state="checked" class="pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg ring-0 transition-transform data-[state=checked]:translate-x-5 data-[state=unchecked]:translate-x-0"></span>
                                </button>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium">Pedido Entregue</p>
                                    <p class="text-sm text-muted-foreground">Enviar quando o pedido for entregue</p>
                                </div>
                                <button type="button" role="switch" aria-checked="false" data-state="unchecked" value="on" class="peer inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors data-[state=checked]:bg-primary data-[state=unchecked]:bg-input focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background disabled:cursor-not-allowed disabled:opacity-50">
                                    <span data-state="unchecked" class="pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg ring-0 transition-transform data-[state=checked]:translate-x-5 data-[state=unchecked]:translate-x-0"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 flex-1">Salvar Configurações</button>
                        <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">Testar Envio</button>
                    </div>
                </div>
            </div>
        </div>

        <div data-tab-content="templates" class="tab-content mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 hidden">
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-col space-y-1.5 p-6">
                    <h3 class="text-2xl font-semibold leading-none tracking-tight">Templates de Mensagem</h3>
                    <p class="text-sm text-muted-foreground">Personalize as mensagens automáticas</p>
                </div>
                <div class="p-6 pt-0 space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="template-type">Tipo de Template</label>
                        <select class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" id="template-type">
                            <option>Pedido Confirmado</option>
                            <option>Pedido em Preparo</option>
                            <option>Saiu para Entrega</option>
                            <option>Pedido Entregue</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="message-template">Mensagem</label>
                        <textarea class="flex min-h-[120px] w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" id="message-template" placeholder="Digite sua mensagem aqui...">Olá @{{nome}}! 👋 Seu pedido #@{{numero}} foi confirmado com sucesso! Total: @{{valor}} Previsão de entrega: @{{tempo}} Obrigado por escolher nosso restaurante! 🍕</textarea>
                        <p class="text-sm text-muted-foreground">Use variáveis: @{{nome}}, @{{numero}}, @{{valor}}, @{{tempo}}</p>
                    </div>
                    <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 w-full">Salvar Template</button>
                </div>
            </div>
        </div>

        <div data-tab-content="history" class="tab-content mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 hidden">
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-col space-y-1.5 p-6">
                    <h3 class="text-2xl font-semibold leading-none tracking-tight">Histórico de Mensagens</h3>
                    <p class="text-sm text-muted-foreground">Últimas mensagens enviadas</p>
                </div>
                <div class="p-6 pt-0">
                    <div class="space-y-4">
                        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                            <div class="p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <p class="font-semibold">João Silva</p>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-check h-4 w-4 text-muted-foreground">
                                                <path d="M18 6 7 17l-5-5"></path>
                                                <path d="m22 10-7.5 7.5L13 16"></path>
                                            </svg>
                                        </div>
                                        <p class="text-sm text-muted-foreground mb-2">Pedido confirmado! #123</p>
                                        <p class="text-xs text-muted-foreground">Hoje, 14:32</p>
                                    </div>
                                    <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 text-foreground">Enviado</div>
                                </div>
                            </div>
                        </div>
                        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                            <div class="p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <p class="font-semibold">Maria Santos</p>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-check h-4 w-4 text-primary">
                                                <path d="M18 6 7 17l-5-5"></path>
                                                <path d="m22 10-7.5 7.5L13 16"></path>
                                            </svg>
                                        </div>
                                        <p class="text-sm text-muted-foreground mb-2">Seu pedido está em preparo</p>
                                        <p class="text-xs text-muted-foreground">Hoje, 13:15</p>
                                    </div>
                                    <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 text-foreground">Lido</div>
                                </div>
                            </div>
                        </div>
                        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                            <div class="p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <p class="font-semibold">Pedro Costa</p>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-check h-4 w-4 text-muted-foreground">
                                                <path d="M18 6 7 17l-5-5"></path>
                                                <path d="m22 10-7.5 7.5L13 16"></path>
                                            </svg>
                                        </div>
                                        <p class="text-sm text-muted-foreground mb-2">Pedido saiu para entrega</p>
                                        <p class="text-xs text-muted-foreground">Hoje, 12:08</p>
                                    </div>
                                    <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 text-foreground">Enviado</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
