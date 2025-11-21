/**
 * Carregador de Tailwind CSS com fallback múltiplo
 * Aplica configuração customizada e marca body quando carregado
 */
(function() {
    'use strict';
    
    // Configuração do Tailwind (será aplicada quando carregar)
    window.tailwindConfig = window.tailwindConfig || {
        theme: {
            extend: {
                colors: {
                    primary: {
                        DEFAULT: '#7A5230',
                        foreground: '#fff',
                    },
                    background: 'hsl(35, 25%, 98%)',
                    foreground: 'hsl(25, 40%, 13%)',
                    card: '#fff',
                    muted: {
                        DEFAULT: 'hsl(30, 20%, 95%)',
                        foreground: 'hsl(25, 20%, 46%)',
                    },
                    accent: '#7A5230',
                    accentForeground: '#fff',
                    border: 'hsl(35, 15%, 88%)',
                },
                fontFamily: {
                    sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    serif: ['Inter', 'system-ui', 'sans-serif'],
                },
                aspectRatio: {
                    '4/3': '4 / 3',
                },
                animation: {
                    'fade-in': 'fadeIn 0.6s ease-out',
                },
                keyframes: {
                    fadeIn: {
                        '0%': { opacity: '0', transform: 'translateY(20px)' },
                        '100%': { opacity: '1', transform: 'translateY(0)' },
                    },
                },
            },
        },
    };
    
    const tailwindCDNs = [
        window.tailwindLocalPath || '/js/tailwind.min.js',
        'https://cdn.tailwindcss.com',
        'https://cdn.jsdelivr.net/npm/tailwindcss@3/dist/tailwind.min.js',
        'https://unpkg.com/tailwindcss@3/dist/tailwind.min.js'
    ];
    
    let currentIndex = 0;
    let configApplied = false;
    
    function applyConfig() {
        if (configApplied || typeof tailwind === 'undefined') return;
        try {
            tailwind.config = window.tailwindConfig;
            configApplied = true;
            markTailwindLoaded();
        } catch(e) {
            console.warn('Erro ao aplicar config do Tailwind:', e);
        }
    }
    
    function markTailwindLoaded() {
        document.body.classList.add('tailwind-loaded');
    }
    
    function loadTailwind() {
        if (currentIndex >= tailwindCDNs.length) {
            console.error('Não foi possível carregar o Tailwind CSS de nenhum CDN. Aplicando fallback básico...');
            // Fallback: aplicar estilos básicos inline para manter layout funcional
            const fallbackStyle = document.createElement('style');
            fallbackStyle.id = 'tailwind-fallback';
            fallbackStyle.textContent = `
                * { box-sizing: border-box; }
                body { margin: 0; font-family: 'Inter', system-ui, sans-serif; }
                .container { max-width: 1200px; margin: 0 auto; padding: 0 1rem; }
                .flex { display: flex; }
                .grid { display: grid; }
                .hidden { display: none; }
                .w-full { width: 100%; }
                .h-full { height: 100%; }
                .p-4 { padding: 1rem; }
                .px-4 { padding-left: 1rem; padding-right: 1rem; }
                .py-4 { padding-top: 1rem; padding-bottom: 1rem; }
                .mb-4 { margin-bottom: 1rem; }
                .text-center { text-align: center; }
                .font-bold { font-weight: 700; }
                .rounded-lg { border-radius: 0.5rem; }
                .border { border-width: 1px; border-style: solid; border-color: #e5e7eb; }
                .bg-white { background-color: #fff; }
                .text-primary { color: #7A5230; }
            `;
            document.head.appendChild(fallbackStyle);
            markTailwindLoaded();
            return;
        }
        
        const script = document.createElement('script');
        script.src = tailwindCDNs[currentIndex];
        script.onerror = function() {
            console.warn(`CDN ${currentIndex + 1} falhou, tentando próximo...`);
            currentIndex++;
            loadTailwind();
        };
        script.onload = function() {
            // Aguardar um pouco para garantir que o Tailwind está disponível
            setTimeout(function() {
                if (typeof tailwind !== 'undefined') {
                    applyConfig();
                } else {
                    // Tentar próximo CDN se o objeto não estiver disponível
                    currentIndex++;
                    loadTailwind();
                }
            }, 100);
        };
        document.head.appendChild(script);
    }
    
    // Marcar body como carregado quando Tailwind estiver pronto
    const checkTailwind = setInterval(function() {
        if (typeof tailwind !== 'undefined' && configApplied) {
            markTailwindLoaded();
            clearInterval(checkTailwind);
        }
    }, 50);
    
    // Iniciar carregamento
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadTailwind);
    } else {
        loadTailwind();
    }
})();

