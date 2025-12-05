@extends('dashboard.layouts.app')

@section('page_title', 'Integração Mercado Pago')
@section('page_subtitle', 'Receba pagamentos online de forma segura e fácil')

@section('content')
<div class="space-y-6">

    <div role="alert" class="relative w-full rounded-lg border p-4 [&>svg~*]:pl-7 [&>svg+div]:translate-y-[-3px] [&>svg]:absolute [&>svg]:left-4 [&>svg]:top-4 [&>svg]:text-foreground bg-background text-foreground">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-alert h-4 w-4">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" x2="12" y1="8" y2="12"></line>
            <line x1="12" x2="12.01" y1="16" y2="16"></line>
        </svg>
        <h5 class="mb-1 font-medium leading-none tracking-tight">Status da Integração</h5>
        <div class="text-sm [&_p]:leading-relaxed">Configure suas credenciais do Mercado Pago para começar a receber pagamentos online.</div>
    </div>

    <x-stat-grid :items="[
        ['label' => 'Total Processado', 'value' => 'R$ ' . number_format($totalProcessed ?? 0, 2, ',', '.'), 'icon' => 'dollar-sign'],
        ['label' => 'Transações', 'value' => number_format($totalTransactions ?? 0, 0, ',', '.'), 'icon' => 'credit-card'],
        ['label' => 'Taxa de Aprovação', 'value' => number_format($approvalRate ?? 0, 1, ',', '.') . '%', 'icon' => 'trending-up'],
        ['label' => 'Ticket Médio', 'value' => 'R$ ' . number_format($averageTicket ?? 0, 2, ',', '.'), 'icon' => 'bar-chart'],
    ]" />

    <div dir="ltr" data-orientation="horizontal" class="space-y-4">
        <x-tab-bar type="buttons" :tabs="[
            ['id' => 'settings', 'label' => 'Configurações', 'data-tab' => 'settings'],
            ['id' => 'methods', 'label' => 'Métodos de Pagamento', 'data-tab' => 'methods'],
            ['id' => 'transactions', 'label' => 'Transações', 'data-tab' => 'transactions'],
        ]" active="settings" />

        <div data-tab-content="settings" class="tab-content mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-col space-y-1.5 p-6">
                    <h3 class="text-lg font-semibold leading-none tracking-tight">Configurações da Integração</h3>
                    <p class="text-sm text-muted-foreground">Configure suas credenciais do Mercado Pago</p>
                </div>
                <form action="{{ route('dashboard.settings.mp.save') }}" method="POST" class="p-6 pt-0 space-y-6">
                    @csrf
                    <div class="flex items-center justify-between p-4 border rounded-lg bg-muted/30">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-credit-card h-5 w-5 text-primary">
                                    <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                                    <line x1="2" x2="22" y1="10" y2="10"></line>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold">Status da Conexão</p>
                                <p class="text-xs text-muted-foreground">Configure suas credenciais</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold bg-red-100 text-red-800 border-red-300">Não Conectado</span>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="public-key">Public Key</label>
                        <input name="mercadopago_public_key" value="{{ $keys['mercadopago_public_key'] ?? '' }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" id="public-key" placeholder="APP_USR-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx">
                        <p class="text-sm text-muted-foreground">Encontre suas credenciais no painel do Mercado Pago</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="access-token">Access Token</label>
                        <input name="mercadopago_access_token" value="{{ $keys['mercadopago_access_token'] ?? '' }}" type="password" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" id="access-token" placeholder="APP_USR-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx">
                    </div>
                    <div class="space-y-4">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Configurações de Pagamento</label>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium">Modo de Produção</p>
                                    <p class="text-sm text-muted-foreground">Processar pagamentos reais</p>
                                </div>
                                <select name="mercadopago_environment" id="mp-environment" class="flex h-10 w-44 rounded-md border border-input bg-background px-3 py-2 text-sm">
                                    <option value="sandbox" {{ ($keys['mercadopago_environment'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                                    <option value="production" {{ ($keys['mercadopago_environment'] ?? '') === 'production' ? 'selected' : '' }}>Produção</option>
                                </select>
                            </div>
                            <!-- Removidos: Salvar Cartões e Parcelamento -->
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="webhook-url">Webhook URL (opcional)</label>
                        <input name="mercadopago_webhook_url" value="{{ $keys['mercadopago_webhook_url'] ?? '' }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" id="webhook-url" placeholder="https://dashboard.menuolika.com.br/webhook/mercadopago">
                    </div>
                    <!-- Removido: Máximo de parcelas -->
                    <div class="flex gap-3">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 flex-1">Salvar</button>
                    </div>
                </form>
            </div>
        </div>

        <div data-tab-content="methods" class="tab-content mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 hidden">
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-col space-y-1.5 p-6">
                    <h3 class="text-2xl font-semibold leading-none tracking-tight">Métodos de Pagamento</h3>
                    <p class="text-sm text-muted-foreground">Configure os métodos de pagamento disponíveis</p>
                </div>
                <form action="{{ route('dashboard.settings.mp.methods.save') }}" method="POST" class="p-6 pt-0 space-y-4">
                    @csrf
                    <div class="rounded-lg bg-card text-card-foreground shadow-sm border-2">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-credit-card h-6 w-6 text-primary">
                                            <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                                            <line x1="2" x2="22" y1="10" y2="10"></line>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold">Cartão de Crédito</p>
                                        <p class="text-sm text-muted-foreground">Visa, Mastercard, Elo, Amex</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="mp_enable_credit_card" value="1" {{ ($keys['mp_enable_credit_card'] ?? '1') === '1' ? 'checked' : '' }} class="sr-only" onchange="this.nextElementSibling.classList.toggle('bg-primary', this.checked); this.nextElementSibling.classList.toggle('bg-input', !this.checked); this.nextElementSibling.nextElementSibling.classList.toggle('translate-x-5', this.checked);">
                                    <div class="w-11 h-6 {{ ($keys['mp_enable_credit_card'] ?? '1') === '1' ? 'bg-primary' : 'bg-input' }} rounded-full transition-colors"></div>
                                    <div class="absolute left-[2px] top-[2px] bg-background rounded-full h-5 w-5 transition-transform {{ ($keys['mp_enable_credit_card'] ?? '1') === '1' ? 'translate-x-5' : '' }}"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg bg-card text-card-foreground shadow-sm border-2">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-success/10 rounded-lg flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-dollar-sign h-6 w-6 text-success">
                                            <line x1="12" x2="12" y1="2" y2="22"></line>
                                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold">PIX</p>
                                        <p class="text-sm text-muted-foreground">Pagamento instantâneo</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="mp_enable_pix" value="1" {{ ($keys['mp_enable_pix'] ?? '1') === '1' ? 'checked' : '' }} class="sr-only" onchange="this.nextElementSibling.classList.toggle('bg-primary', this.checked); this.nextElementSibling.classList.toggle('bg-input', !this.checked); this.nextElementSibling.nextElementSibling.classList.toggle('translate-x-5', this.checked);">
                                    <div class="w-11 h-6 {{ ($keys['mp_enable_pix'] ?? '1') === '1' ? 'bg-primary' : 'bg-input' }} rounded-full transition-colors"></div>
                                    <div class="absolute left-[2px] top-[2px] bg-background rounded-full h-5 w-5 transition-transform {{ ($keys['mp_enable_pix'] ?? '1') === '1' ? 'translate-x-5' : '' }}"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg bg-card text-card-foreground shadow-sm border-2">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-warning/10 rounded-lg flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-credit-card h-6 w-6 text-warning">
                                            <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                                            <line x1="2" x2="22" y1="10" y2="10"></line>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold">Cartão de Débito</p>
                                        <p class="text-sm text-muted-foreground">Débito online</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="mp_enable_debit_card" value="1" {{ ($keys['mp_enable_debit_card'] ?? '0') === '1' ? 'checked' : '' }} class="sr-only" onchange="this.nextElementSibling.classList.toggle('bg-primary', this.checked); this.nextElementSibling.classList.toggle('bg-input', !this.checked); this.nextElementSibling.nextElementSibling.classList.toggle('translate-x-5', this.checked);">
                                    <div class="w-11 h-6 {{ ($keys['mp_enable_debit_card'] ?? '0') === '1' ? 'bg-primary' : 'bg-input' }} rounded-full transition-colors"></div>
                                    <div class="absolute left-[2px] top-[2px] bg-background rounded-full h-5 w-5 transition-transform {{ ($keys['mp_enable_debit_card'] ?? '0') === '1' ? 'translate-x-5' : '' }}"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg bg-card text-card-foreground shadow-sm border-2">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-muted rounded-lg flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-dollar-sign h-6 w-6 text-muted-foreground">
                                            <line x1="12" x2="12" y1="2" y2="22"></line>
                                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold">Boleto Bancário</p>
                                        <p class="text-sm text-muted-foreground">Vencimento em 3 dias úteis</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="mp_enable_boleto" value="1" {{ ($keys['mp_enable_boleto'] ?? '0') === '1' ? 'checked' : '' }} class="sr-only" onchange="this.nextElementSibling.classList.toggle('bg-primary', this.checked); this.nextElementSibling.classList.toggle('bg-input', !this.checked); this.nextElementSibling.nextElementSibling.classList.toggle('translate-x-5', this.checked);">
                                    <div class="w-11 h-6 {{ ($keys['mp_enable_boleto'] ?? '0') === '1' ? 'bg-primary' : 'bg-input' }} rounded-full transition-colors"></div>
                                    <div class="absolute left-[2px] top-[2px] bg-background rounded-full h-5 w-5 transition-transform {{ ($keys['mp_enable_boleto'] ?? '0') === '1' ? 'translate-x-5' : '' }}"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 w-full">Salvar métodos</button>
                    </div>
                </form>
            </div>
        </div>

        <div data-tab-content="transactions" class="tab-content mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 hidden">
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-col space-y-1.5 p-6">
                    <h3 class="text-2xl font-semibold leading-none tracking-tight">Últimas Transações</h3>
                    <p class="text-sm text-muted-foreground">Histórico de pagamentos processados</p>
                </div>
                <div class="p-6 pt-0">
                    <div class="overflow-x-auto">
                        <div class="relative w-full overflow-auto">
                            <table class="w-full caption-bottom text-sm">
                                <thead class="[&_tr]:border-b">
                                    <tr class="border-b transition-colors data-[state=selected]:bg-muted hover:bg-muted/50">
                                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">Cliente</th>
                                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">Valor</th>
                                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">Método</th>
                                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">Status</th>
                                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">Data</th>
                                    </tr>
                                </thead>
                                <tbody class="[&_tr:last-child]:border-0">
                                    @forelse($recentTransactions ?? [] as $transaction)
                                    <tr class="border-b transition-colors data-[state=selected]:bg-muted hover:bg-muted/50">
                                        <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 font-medium">{{ $transaction['customer_name'] }}</td>
                                        <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 font-semibold">R$ {{ number_format($transaction['value'], 2, ',', '.') }}</td>
                                        <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0">{{ $transaction['method'] }}</td>
                                        <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0">
                                            @if($transaction['status_class'] === 'success')
                                            <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent bg-success text-success-foreground hover:bg-success/80">{{ $transaction['status'] }}</div>
                                            @else
                                            <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent bg-muted text-muted-foreground">{{ $transaction['status'] }}</div>
                                            @endif
                                        </td>
                                        <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 text-muted-foreground">{{ $transaction['date'] }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="p-4 text-center text-muted-foreground">Nenhuma transação encontrada</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
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
    const envSelect = document.getElementById('mp-environment');
    
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

    if (envSelect) {
        envSelect.addEventListener('change', function(e) {
            if (this.value === 'sandbox') {
                const ok = confirm('Ativar SANDBOX? Os próximos pagamentos serão criados com valor aleatório entre R$ 0,01 e R$ 0,10 para testes.');
                if (!ok) {
                    // revert
                    this.value = 'production';
                }
            }
        });
    }
});
</script>
@endpush
@endsection
