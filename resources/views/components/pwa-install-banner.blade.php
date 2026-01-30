{{-- Banner de sugestão de instalação PWA (pós-login). Mostra se app não instalado e beforeinstallprompt disponível. --}}
<div id="pwa-install-banner"
     class="fixed bottom-4 left-4 right-4 md:left-auto md:right-4 md:w-80 z-50 hidden animate-copycat-fade-up card-copycat p-4 shadow-2xl">
    <div class="flex items-start gap-3">
        <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center shrink-0">
            <i data-lucide="smartphone" class="w-5 h-5 text-primary"></i>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between mb-1">
                <h3 class="font-semibold text-foreground">Instalar App</h3>
                <button type="button" id="pwa-banner-dismiss" class="text-muted-foreground hover:text-foreground transition-colors p-1 rounded">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <p class="text-sm text-muted-foreground mb-3">
                Tenha acesso rápido ao painel direto da sua tela inicial!
            </p>
            <div class="flex gap-2">
                <button type="button" id="pwa-install-btn"
                        class="flex-1 flex items-center justify-center gap-1 h-9 bg-primary hover:bg-primary/90 text-primary-foreground text-sm font-medium rounded-lg transition-colors">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Instalar</span> Agora
                </button>
                <button type="button" id="pwa-banner-dismiss-btn"
                        class="px-4 h-9 border border-border text-sm font-medium rounded-lg hover:bg-muted transition-colors">
                    Depois
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const banner = document.getElementById('pwa-install-banner');
    const installBtn = document.getElementById('pwa-install-btn');
    const dismissBtn = document.getElementById('pwa-banner-dismiss');
    const dismissBtn2 = document.getElementById('pwa-banner-dismiss-btn');
    const STORAGE_KEY = 'pwa-banner-dismissed';

    let deferredPrompt = null;

    function showBanner() {
        if (!banner) return;
        if (localStorage.getItem(STORAGE_KEY)) return;
        if (window.matchMedia('(display-mode: standalone)').matches) return;
        banner.classList.remove('hidden');
        if (window.lucide) window.lucide.createIcons();
    }

    function hideBanner() {
        if (banner) banner.classList.add('hidden');
        localStorage.setItem(STORAGE_KEY, 'true');
    }

    window.addEventListener('beforeinstallprompt', function(e) {
        e.preventDefault();
        deferredPrompt = e;
        showBanner();
    });

    if (installBtn) {
        installBtn.addEventListener('click', function() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then(function(r) {
                    if (r.outcome === 'accepted') hideBanner();
                    deferredPrompt = null;
                });
            }
        });
    }
    if (dismissBtn) dismissBtn.addEventListener('click', hideBanner);
    if (dismissBtn2) dismissBtn2.addEventListener('click', hideBanner);
})();
</script>
