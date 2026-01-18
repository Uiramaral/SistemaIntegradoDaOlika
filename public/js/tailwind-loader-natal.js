/**
 * Carregador de Tailwind CSS para tema Natal
 * Configuração específica com cores de Natal
 */
(function() {
    'use strict';
    
    // Configuração do Tailwind para tema Natal
    window.tailwindConfig = {
        theme: {
            extend: {
                colors: {
                    primary: {
                        DEFAULT: '#C41E3A',
                        foreground: '#fff',
                    },
                    natal: {
                        red: '#C41E3A',
                        green: '#228B22',
                        gold: '#FFD700',
                        cream: '#FFF8DC',
                    },
                    background: '#FFF8DC',
                    foreground: '#1a1a1a',
                    card: '#fff',
                    muted: {
                        DEFAULT: '#f5f5f5',
                        foreground: '#666',
                    },
                    accent: '#C41E3A',
                    accentForeground: '#fff',
                    border: '#e0e0e0',
                },
                fontFamily: {
                    sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
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
            console.error('Não foi possível carregar o Tailwind CSS de nenhum CDN.');
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
            setTimeout(function() {
                if (typeof tailwind !== 'undefined') {
                    applyConfig();
                } else {
                    currentIndex++;
                    loadTailwind();
                }
            }, 100);
        };
        document.head.appendChild(script);
    }
    
    const checkTailwind = setInterval(function() {
        if (typeof tailwind !== 'undefined' && configApplied) {
            markTailwindLoaded();
            clearInterval(checkTailwind);
        }
    }, 50);
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadTailwind);
    } else {
        loadTailwind();
    }
})();

