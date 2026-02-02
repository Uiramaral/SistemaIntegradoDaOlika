/**
 * OLIKA Dashboard Theme Manager
 * Gerenciamento de temas Dark/Light com persistência
 */

(function () {
    'use strict';

    const THEME_KEY = 'olika-theme';
    const THEMES = {
        LIGHT: 'light',
        DARK: 'dark'
    };

    /**
     * Obter tema salvo ou preferência do sistema
     */
    function getSavedTheme() {
        // Primeiro, verificar localStorage
        const savedTheme = localStorage.getItem(THEME_KEY);
        if (savedTheme && Object.values(THEMES).includes(savedTheme)) {
            return savedTheme;
        }

        // Se não houver tema salvo, verificar preferência do sistema
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return THEMES.DARK;
        }

        // Padrão: light
        return THEMES.LIGHT;
    }

    /**
     * Forçar aplicação de tema removendo estilos inline conflitantes
     */
    function forceThemeStyles(theme) {
        if (theme !== THEMES.DARK) return;

        // Seletores de elementos que costumam ter estilos inline
        const elements = document.querySelectorAll('[style*="background"], [style*="color"], [style*="border"]');

        elements.forEach(el => {
            // Não modificar sidebar, header ou elementos já corrigidos
            if (el.closest('#sidebar') || el.closest('header') || el.dataset.themeFixed) return;

            const style = el.getAttribute('style');
            if (!style) return;

            // Remover backgrounds brancos/claros
            if (style.includes('#fff') ||
                style.includes('#ffffff') ||
                style.includes('rgb(255, 255, 255)') ||
                style.includes('#f9fafb') ||
                style.includes('#f3f4f6')) {

                el.style.removeProperty('background');
                el.style.removeProperty('background-color');
                el.dataset.themeFixed = 'true';
            }

            // Remover cores de texto escuras
            if (style.includes('#111827') ||
                style.includes('#1f2937') ||
                style.includes('#374151') ||
                style.includes('rgb(17, 24, 39)')) {

                el.style.removeProperty('color');
                el.dataset.themeFixed = 'true';
            }

            // Remover bordas cinzas claras
            if (style.includes('#e5e7eb') ||
                style.includes('#d1d5db') ||
                style.includes('rgb(229, 231, 235)')) {

                el.style.removeProperty('border');
                el.style.removeProperty('border-color');
                el.dataset.themeFixed = 'true';
            }
        });

        // Forçar refresh de lucide icons (às vezes não renderizam corretamente)
        if (window.lucide) {
            setTimeout(() => lucide.createIcons(), 100);
        }
    }

    /**
     * Aplicar tema ao documento
     */
    function applyTheme(theme, animate = false) {
        const html = document.documentElement;

        if (animate) {
            html.classList.add('theme-transitioning');
            setTimeout(() => {
                html.classList.remove('theme-transitioning');
            }, 400);
        }

        html.setAttribute('data-theme', theme);
        localStorage.setItem(THEME_KEY, theme);

        // Atualizar meta theme-color para PWA
        updateMetaThemeColor(theme);

        // Forçar estilos após DOM renderizar
        requestAnimationFrame(() => {
            forceThemeStyles(theme);
        });

        // Disparar evento customizado
        window.dispatchEvent(new CustomEvent('themechange', {
            detail: { theme: theme }
        }));

        console.log('[ThemeManager] Tema aplicado:', theme);
    }

    /**
     * Atualizar meta theme-color
     */
    function updateMetaThemeColor(theme) {
        const metaTheme = document.querySelector('meta[name="theme-color"]');
        if (metaTheme) {
            metaTheme.setAttribute('content', theme === THEMES.DARK ? '#1a1a2e' : '#f9fafb');
        }
    }

    /**
     * Alternar entre temas
     */
    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme') || THEMES.LIGHT;
        const newTheme = currentTheme === THEMES.DARK ? THEMES.LIGHT : THEMES.DARK;
        applyTheme(newTheme, true);
        return newTheme;
    }

    /**
     * Inicializar sistema de temas
     */
    function init() {
        // Aplicar tema imediatamente (sem animação)
        const theme = getSavedTheme();
        applyTheme(theme, false);

        // Escutar mudanças na preferência do sistema
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                // Só mudar automaticamente se não houver preferência manual salva
                if (!localStorage.getItem(THEME_KEY)) {
                    applyTheme(e.matches ? THEMES.DARK : THEMES.LIGHT, true);
                }
            });
        }

        // Configurar listeners para botões de toggle
        document.addEventListener('DOMContentLoaded', () => {
            setupToggleButtons();
            // Re-aplicar forceThemeStyles após DOM completo
            forceThemeStyles(getSavedTheme());
        });

        console.log('[ThemeManager] Inicializado com tema:', theme);
    }

    /**
     * Configurar botões de toggle
     */
    function setupToggleButtons() {
        const toggleButtons = document.querySelectorAll('[data-theme-toggle]');

        toggleButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                toggleTheme();
            });
        });
    }

    // API pública
    window.ThemeManager = {
        get: getSavedTheme,
        set: applyTheme,
        toggle: toggleTheme,
        forceStyles: forceThemeStyles,
        THEMES: THEMES
    };

    // Inicializar assim que o script carregar
    init();

})();

