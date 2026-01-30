{{-- Banner de Incentivo à Conexão WhatsApp --}}
<div id="whatsapp-connect-banner" 
     data-whatsapp-connected="{{ $whatsappConnected ? 'true' : 'false' }}" 
     class="hidden fixed bottom-4 left-4 right-4 md:bottom-6 md:left-6 md:right-6 lg:left-auto lg:max-w-md z-50 animate-in slide-in-from-bottom duration-500">
    <div class="bg-card rounded-xl border border-border shadow-2xl overflow-hidden">
        <!-- Header com Gradient -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3 flex-1 min-w-0">
                    <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center shrink-0">
                        <i data-lucide="message-circle" class="w-6 h-6 text-white"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="font-semibold text-white text-sm md:text-base">Conecte seu WhatsApp</h3>
                        <p class="text-xs text-white/90 mt-0.5">Automatize notificações e atendimento</p>
                    </div>
                </div>
                <button type="button" id="whatsapp-banner-dismiss" class="text-white/80 hover:text-white transition-colors shrink-0">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="p-4 space-y-3">
            <!-- Benefícios -->
            <div class="space-y-2">
                <div class="flex items-start gap-2 text-sm">
                    <i data-lucide="check-circle" class="w-4 h-4 text-green-600 mt-0.5 shrink-0"></i>
                    <span class="text-foreground">Envie recibos automaticamente</span>
                </div>
                <div class="flex items-start gap-2 text-sm">
                    <i data-lucide="check-circle" class="w-4 h-4 text-green-600 mt-0.5 shrink-0"></i>
                    <span class="text-foreground">Notifique status de pedidos</span>
                </div>
                <div class="flex items-start gap-2 text-sm">
                    <i data-lucide="check-circle" class="w-4 h-4 text-green-600 mt-0.5 shrink-0"></i>
                    <span class="text-foreground">Atendimento com IA opcional</span>
                </div>
            </div>

            <!-- Avisos Importantes -->
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 space-y-2">
                <div class="flex items-start gap-2">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600 mt-0.5 shrink-0"></i>
                    <div class="text-xs text-amber-900 space-y-1">
                        <p class="font-semibold">⚠️ Conexão Não Oficial</p>
                        <ul class="space-y-1 ml-4 list-disc">
                            <li>Não é uma API oficial do WhatsApp</li>
                            <li>Risco de suspensão por spam ou uso comercial</li>
                            <li>WhatsApp pode banir o número sem aviso</li>
                            <li>Use um número secundário (não principal)</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Recomendação -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                <div class="flex items-start gap-2">
                    <i data-lucide="info" class="w-4 h-4 text-blue-600 mt-0.5 shrink-0"></i>
                    <div class="text-xs text-blue-900">
                        <p class="font-semibold mb-1">💡 Recomendação</p>
                        <p>Use um chip/número dedicado exclusivamente para o sistema. Nunca use seu número pessoal ou comercial principal.</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-2 pt-2">
                <a href="{{ route('dashboard.settings.whatsapp') }}" 
                   class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i data-lucide="plug" class="w-4 h-4"></i>
                    Conectar Agora
                </a>
                <button type="button" id="whatsapp-banner-later"
                        class="sm:w-auto px-4 py-2.5 border border-border text-sm font-medium rounded-lg hover:bg-muted transition-colors">
                    Conectar Depois
                </button>
            </div>

            <!-- Disclaimer Final -->
            <p class="text-[10px] text-muted-foreground text-center pt-2 border-t border-border">
                Ao conectar, você concorda com os riscos e isenta o sistema de responsabilidade por suspensões ou banimentos
            </p>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    const banner = document.getElementById('whatsapp-connect-banner');
    const dismissBtn = document.getElementById('whatsapp-banner-dismiss');
    const laterBtn = document.getElementById('whatsapp-banner-later');
    const STORAGE_KEY = 'whatsapp-banner-dismissed';
    const STORAGE_EXPIRY = 'whatsapp-banner-expiry';
    
    console.log('🟢 [WhatsApp Banner] Inicializando...');
    console.log('🟢 WhatsApp Connected:', banner?.dataset?.whatsappConnected);
    console.log('🟢 LocalStorage dismissed:', localStorage.getItem(STORAGE_KEY));
    console.log('🟢 LocalStorage expiry:', localStorage.getItem(STORAGE_EXPIRY));
    console.log('🔧 Para resetar o banner, execute no console: resetWhatsAppBanner()');
    
    // Função global para resetar o banner (para testes)
    window.resetWhatsAppBanner = function() {
        localStorage.removeItem(STORAGE_KEY);
        localStorage.removeItem(STORAGE_EXPIRY);
        console.log('✅ Banner resetado! Recarregue a página para ver.');
        location.reload();
    };
    
    // Função para verificar se deve mostrar o banner
    function shouldShowBanner() {
        // Verificar se foi dispensado
        const dismissed = localStorage.getItem(STORAGE_KEY);
        if (dismissed === 'permanently') {
            console.log('❌ Banner dispensado permanentemente');
            return false;
        }
        
        // Verificar se está em período de "depois"
        const expiry = localStorage.getItem(STORAGE_EXPIRY);
        if (expiry) {
            const expiryDate = new Date(parseInt(expiry));
            const now = new Date();
            console.log('⏰ Expiry:', expiryDate, '| Now:', now);
            if (now < expiryDate) {
                console.log('❌ Ainda no período de espera');
                return false;
            } else {
                console.log('✅ Período expirou, limpando...');
                localStorage.removeItem(STORAGE_EXPIRY);
            }
        }
        
        // Verificar se WhatsApp já está conectado (via atributo data no backend)
        const isConnected = banner?.dataset?.whatsappConnected === 'true';
        if (isConnected) {
            console.log('❌ WhatsApp já conectado');
            return false;
        }
        
        console.log('✅ Banner DEVE ser mostrado!');
        return true;
    }
    
    function showBanner() {
        if (!banner) {
            console.error('❌ Banner element não encontrado');
            return;
        }
        if (!shouldShowBanner()) {
            console.log('❌ Banner não será exibido');
            return;
        }
        
        console.log('⏳ Aguardando 3 segundos para exibir...');
        setTimeout(() => {
            console.log('✅ EXIBINDO BANNER!');
            banner.classList.remove('hidden');
            if (window.lucide) window.lucide.createIcons();
        }, 3000);
    }
    
    function hideBanner(permanently = false) {
        if (!banner) return;
        console.log('🔴 Ocultando banner (permanently:', permanently, ')');
        banner.classList.add('hidden');
        
        if (permanently) {
            localStorage.setItem(STORAGE_KEY, 'permanently');
            localStorage.removeItem(STORAGE_EXPIRY);
        }
    }
    
    function postponeBanner() {
        if (!banner) return;
        console.log('⏰ Adiando banner por 7 dias');
        banner.classList.add('hidden');
        
        const expiryDate = new Date();
        expiryDate.setDate(expiryDate.getDate() + 7);
        localStorage.setItem(STORAGE_EXPIRY, expiryDate.getTime().toString());
        console.log('⏰ Nova data de expiração:', expiryDate);
    }
    
    // Event Listeners
    if (dismissBtn) {
        dismissBtn.addEventListener('click', () => hideBanner(true));
    }
    
    if (laterBtn) {
        laterBtn.addEventListener('click', () => postponeBanner());
    }
    
    // Inicializar
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', showBanner);
    } else {
        showBanner();
    }
})();
</script>
@endpush
