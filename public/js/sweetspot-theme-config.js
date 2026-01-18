/*
 * SweetSpot Theme Configuration
 * Sistema de configuração para temas personalizáveis
 */

class SweetSpotThemeConfig {
    constructor() {
        this.defaultConfig = {
            // Colors
            primaryColor: '#f59e0b',
            secondaryColor: '#8b5cf6',
            accentColor: '#10b981',
            backgroundColor: '#ffffff',
            textColor: '#1f2937',
            borderColor: '#e5e7eb',
            
            // Branding
            brandName: 'SweetSpot Bakery',
            logoText: 'BK',
            logoUrl: null,
            
            // Typography
            fontFamily: "'Inter', -apple-system, BlinkMacSystemFont, sans-serif",
            borderRadius: '12px',
            
            // Layout
            sidebarWidth: '280px',
            cartPanelWidth: '380px',
            
            // Animation
            animationSpeed: '0.2s',
            enableAnimations: true,
            
            // Responsive breakpoints
            breakpoints: {
                mobile: 480,
                tablet: 768,
                desktop: 1024,
                large: 1400
            }
        };
        
        this.currentConfig = { ...this.defaultConfig };
        this.loadConfig();
    }
    
    loadConfig() {
        // Load from localStorage or server
        const savedConfig = localStorage.getItem('sweetspot-theme-config');
        if (savedConfig) {
            try {
                const parsed = JSON.parse(savedConfig);
                this.currentConfig = { ...this.defaultConfig, ...parsed };
            } catch (e) {
                console.warn('Failed to load theme config:', e);
            }
        }
    }
    
    saveConfig() {
        localStorage.setItem('sweetspot-theme-config', JSON.stringify(this.currentConfig));
    }
    
    getConfig(key = null) {
        if (key) {
            return this.currentConfig[key];
        }
        return { ...this.currentConfig };
    }
    
    setConfig(key, value) {
        this.currentConfig[key] = value;
        this.saveConfig();
        this.applyConfig();
    }
    
    setMultipleConfig(config) {
        Object.assign(this.currentConfig, config);
        this.saveConfig();
        this.applyConfig();
    }
    
    resetConfig() {
        this.currentConfig = { ...this.defaultConfig };
        this.saveConfig();
        this.applyConfig();
    }
    
    applyConfig() {
        this.updateCSSVariables();
        this.updateBrandElements();
        this.updateTypography();
    }
    
    updateCSSVariables() {
        const root = document.documentElement;
        
        // Color variables
        root.style.setProperty('--ss-primary', this.currentConfig.primaryColor);
        root.style.setProperty('--ss-secondary', this.currentConfig.secondaryColor);
        root.style.setProperty('--ss-accent', this.currentConfig.accentColor);
        root.style.setProperty('--ss-bg-primary', this.currentConfig.backgroundColor);
        root.style.setProperty('--ss-text-primary', this.currentConfig.textColor);
        root.style.setProperty('--ss-border', this.currentConfig.borderColor);
        
        // Typography
        root.style.setProperty('--ss-font-family', this.currentConfig.fontFamily);
        root.style.setProperty('--ss-radius', this.currentConfig.borderRadius);
        
        // Generate color variations
        this.generateColorVariations();
    }
    
    generateColorVariations() {
        const root = document.documentElement;
        
        // Primary color variations
        const primaryHsl = this.hexToHsl(this.currentConfig.primaryColor);
        root.style.setProperty('--ss-primary-dark', `hsl(${primaryHsl.h}, ${primaryHsl.s}%, ${Math.max(0, primaryHsl.l - 15)}%)`);
        root.style.setProperty('--ss-primary-light', `hsl(${primaryHsl.h}, ${primaryHsl.s}%, ${Math.min(100, primaryHsl.l + 15)}%)`);
        
        // Secondary color variations
        const secondaryHsl = this.hexToHsl(this.currentConfig.secondaryColor);
        root.style.setProperty('--ss-secondary-dark', `hsl(${secondaryHsl.h}, ${secondaryHsl.s}%, ${Math.max(0, secondaryHsl.l - 15)}%)`);
        root.style.setProperty('--ss-secondary-light', `hsl(${secondaryHsl.h}, ${secondaryHsl.s}%, ${Math.min(100, secondaryHsl.l + 15)}%)`);
        
        // Accent color variations
        const accentHsl = this.hexToHsl(this.currentConfig.accentColor);
        root.style.setProperty('--ss-accent-dark', `hsl(${accentHsl.h}, ${accentHsl.s}%, ${Math.max(0, accentHsl.l - 15)}%)`);
        root.style.setProperty('--ss-accent-light', `hsl(${accentHsl.h}, ${accentHsl.s}%, ${Math.min(100, accentHsl.l + 15)}%)`);
    }
    
    updateBrandElements() {
        // Update brand name
        const brandTextElements = document.querySelectorAll('.sweetspot-brand-text');
        brandTextElements.forEach(el => {
            el.textContent = this.currentConfig.brandName;
        });
        
        // Update logo
        const logoElements = document.querySelectorAll('.sweetspot-logo');
        logoElements.forEach(el => {
            if (this.currentConfig.logoUrl) {
                el.innerHTML = `<img src="${this.currentConfig.logoUrl}" alt="Logo" style="width: 100%; height: 100%; object-fit: contain;">`;
            } else {
                el.textContent = this.currentConfig.logoText;
            }
        });
    }
    
    updateTypography() {
        document.body.style.fontFamily = this.currentConfig.fontFamily;
    }
    
    hexToHsl(hex) {
        // Remove # if present
        hex = hex.replace('#', '');
        
        // Convert 3-digit hex to 6-digit
        if (hex.length === 3) {
            hex = hex.split('').map(char => char + char).join('');
        }
        
        // Convert hex to RGB
        const r = parseInt(hex.substr(0, 2), 16) / 255;
        const g = parseInt(hex.substr(2, 2), 16) / 255;
        const b = parseInt(hex.substr(4, 2), 16) / 255;
        
        // Find min and max values
        const max = Math.max(r, g, b);
        const min = Math.min(r, g, b);
        let h, s, l = (max + min) / 2;
        
        if (max === min) {
            h = s = 0; // achromatic
        } else {
            const d = max - min;
            s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
            
            switch (max) {
                case r: h = (g - b) / d + (g < b ? 6 : 0); break;
                case g: h = (b - r) / d + 2; break;
                case b: h = (r - g) / d + 4; break;
            }
            h /= 6;
        }
        
        return {
            h: Math.round(h * 360),
            s: Math.round(s * 100),
            l: Math.round(l * 100)
        };
    }
    
    // Preset themes
    applyPreset(themeName) {
        const presets = {
            'bakery': {
                primaryColor: '#f59e0b',
                secondaryColor: '#8b5cf6',
                accentColor: '#10b981',
                brandName: 'SweetSpot Bakery',
                logoText: 'BK'
            },
            'coffee-shop': {
                primaryColor: '#92400e',
                secondaryColor: '#7c2d12',
                accentColor: '#f97316',
                brandName: 'Coffee House',
                logoText: 'CH'
            },
            'pastry': {
                primaryColor: '#ec4899',
                secondaryColor: '#db2777',
                accentColor: '#f472b6',
                brandName: 'Pastry Shop',
                logoText: 'PS'
            },
            'healthy': {
                primaryColor: '#059669',
                secondaryColor: '#047857',
                accentColor: '#10b981',
                brandName: 'Healthy Bites',
                logoText: 'HB'
            }
        };
        
        if (presets[themeName]) {
            this.setMultipleConfig(presets[themeName]);
        }
    }
    
    // Export/import configuration
    exportConfig() {
        return JSON.stringify(this.currentConfig, null, 2);
    }
    
    importConfig(configString) {
        try {
            const config = JSON.parse(configString);
            this.setMultipleConfig(config);
            return true;
        } catch (e) {
            console.error('Invalid configuration:', e);
            return false;
        }
    }
}

// Initialize theme configuration
document.addEventListener('DOMContentLoaded', function() {
    window.sweetspotTheme = new SweetSpotThemeConfig();
    
    // Apply configuration
    window.sweetspotTheme.applyConfig();
});

// Export for global use
window.SweetSpotThemeConfig = SweetSpotThemeConfig;