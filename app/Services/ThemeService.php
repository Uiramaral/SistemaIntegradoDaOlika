<?php

namespace App\Services;

use App\Models\MasterSetting;
use Illuminate\Support\Facades\Cache;

class ThemeService
{
    private const CACHE_KEY = 'theme_settings';
    private const CACHE_TTL = 3600; // 1 hora

    /**
     * Obter todas as configurações de tema
     */
    public function getThemeSettings(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $keys = [
                'theme_primary_color',
                'theme_secondary_color', 
                'theme_accent_color',
                'theme_background_color',
                'theme_text_color',
                'theme_border_color',
                'theme_logo_url',
                'theme_favicon_url',
                'theme_brand_name',
                'theme_font_family',
                'theme_border_radius',
                'theme_shadow_style'
            ];
            
            $settings = [];
            foreach ($keys as $key) {
                $settings[$key] = MasterSetting::get($key);
            }

            return $this->getDefaultSettings($settings);
        });
    }

    /**
     * Obter configuração específica do tema
     */
    public function getSetting(string $key, $default = null)
    {
        $settings = $this->getThemeSettings();
        return $settings[$key] ?? $default;
    }

    /**
     * Definir configuração do tema
     */
    public function setSetting(string $key, $value): void
    {
        MasterSetting::set($key, $value);
        
        // Limpar cache
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Definir múltiplas configurações
     */
    public function setSettings(array $settings): void
    {
        foreach ($settings as $key => $value) {
            MasterSetting::set($key, $value);
        }
        
        // Limpar cache
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Configurações padrão do tema
     */
    private function getDefaultSettings(array $storedSettings): array
    {
        $defaults = [
            // Cores primárias (padrão SweetSpot inspired)
            'theme_primary_color' => $storedSettings['theme_primary_color'] ?? '#f59e0b', // Laranja quente
            'theme_secondary_color' => $storedSettings['theme_secondary_color'] ?? '#8b5cf6', // Roxo
            'theme_accent_color' => $storedSettings['theme_accent_color'] ?? '#10b981', // Verde
            'theme_background_color' => $storedSettings['theme_background_color'] ?? '#ffffff', // Branco
            'theme_text_color' => $storedSettings['theme_text_color'] ?? '#1f2937', // Cinza escuro
            'theme_border_color' => $storedSettings['theme_border_color'] ?? '#e5e7eb', // Cinza claro
            
            // Branding
            'theme_logo_url' => $storedSettings['theme_logo_url'] ?? '/images/logo-default.png',
            'theme_favicon_url' => $storedSettings['theme_favicon_url'] ?? '/favicon.ico',
            'theme_brand_name' => $storedSettings['theme_brand_name'] ?? 'Olika PDV',
            
            // Tipografia e estilo
            'theme_font_family' => $storedSettings['theme_font_family'] ?? "'Inter', -apple-system, BlinkMacSystemFont, sans-serif",
            'theme_border_radius' => $storedSettings['theme_border_radius'] ?? '12px',
            'theme_shadow_style' => $storedSettings['theme_shadow_style'] ?? '0 4px 12px rgba(0,0,0,0.08)',
        ];

        return array_merge($defaults, $storedSettings);
    }

    /**
     * Gerar classes CSS personalizadas para o tema
     */
    public function generateCustomCSS(): string
    {
        $settings = $this->getThemeSettings();
        
        return "
            :root {
                --theme-primary: {$settings['theme_primary_color']};
                --theme-secondary: {$settings['theme_secondary_color']};
                --theme-accent: {$settings['theme_accent_color']};
                --theme-background: {$settings['theme_background_color']};
                --theme-text: {$settings['theme_text_color']};
                --theme-border: {$settings['theme_border_color']};
                --theme-font-family: {$settings['theme_font_family']};
                --theme-border-radius: {$settings['theme_border_radius']};
                --theme-shadow: {$settings['theme_shadow_style']};
            }
            
            body {
                font-family: var(--theme-font-family);
                background-color: var(--theme-background);
                color: var(--theme-text);
            }
            
            .btn-primary {
                background-color: var(--theme-primary);
                border-color: var(--theme-primary);
            }
            
            .btn-primary:hover {
                background-color: color-mix(in srgb, var(--theme-primary) 90%, black);
                border-color: color-mix(in srgb, var(--theme-primary) 90%, black);
            }
            
            .text-primary {
                color: var(--theme-primary);
            }
            
            .border-primary {
                border-color: var(--theme-primary);
            }
            
            .bg-primary {
                background-color: var(--theme-primary);
            }
        ";
    }

    /**
     * Converter cor HEX para HSL
     */
    public function hexToHsl(string $hex): string
    {
        $hex = str_replace('#', '', $hex);
        
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;
        
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $delta = $max - $min;
        
        $h = 0;
        $s = 0;
        $l = ($max + $min) / 2;
        
        if ($delta !== 0) {
            $s = $l > 0.5 ? $delta / (2 - $max - $min) : $delta / ($max + $min);
            
            switch ($max) {
                case $r:
                    $h = ($g - $b) / $delta + ($g < $b ? 6 : 0);
                    break;
                case $g:
                    $h = ($b - $r) / $delta + 2;
                    break;
                case $b:
                    $h = ($r - $g) / $delta + 4;
                    break;
            }
            
            $h /= 6;
        }
        
        $h = round($h * 360);
        $s = round($s * 100);
        $l = round($l * 100);
        
        return "{$h} {$s}% {$l}%";
    }

    /**
     * Obter paleta de cores completa para o tema
     */
    public function getColorPalette(): array
    {
        $primary = $this->getSetting('theme_primary_color');
        $secondary = $this->getSetting('theme_secondary_color');
        $accent = $this->getSetting('theme_accent_color');
        
        return [
            'primary' => [
                '50' => $this->lightenColor($primary, 40),
                '100' => $this->lightenColor($primary, 30),
                '200' => $this->lightenColor($primary, 20),
                '300' => $this->lightenColor($primary, 10),
                '400' => $this->darkenColor($primary, 5),
                '500' => $primary,
                '600' => $this->darkenColor($primary, 10),
                '700' => $this->darkenColor($primary, 20),
                '800' => $this->darkenColor($primary, 30),
                '900' => $this->darkenColor($primary, 40),
            ],
            'secondary' => [
                '50' => $this->lightenColor($secondary, 40),
                '100' => $this->lightenColor($secondary, 30),
                '200' => $this->lightenColor($secondary, 20),
                '300' => $this->lightenColor($secondary, 10),
                '400' => $this->darkenColor($secondary, 5),
                '500' => $secondary,
                '600' => $this->darkenColor($secondary, 10),
                '700' => $this->darkenColor($secondary, 20),
                '800' => $this->darkenColor($secondary, 30),
                '900' => $this->darkenColor($secondary, 40),
            ],
            'accent' => [
                '50' => $this->lightenColor($accent, 40),
                '100' => $this->lightenColor($accent, 30),
                '200' => $this->lightenColor($accent, 20),
                '300' => $this->lightenColor($accent, 10),
                '400' => $this->darkenColor($accent, 5),
                '500' => $accent,
                '600' => $this->darkenColor($accent, 10),
                '700' => $this->darkenColor($accent, 20),
                '800' => $this->darkenColor($accent, 30),
                '900' => $this->darkenColor($accent, 40),
            ]
        ];
    }

    /**
     * Clarear uma cor
     */
    private function lightenColor(string $hex, int $percent): string
    {
        $hex = str_replace('#', '', $hex);
        
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        $r = min(255, hexdec(substr($hex, 0, 2)) + (255 * $percent / 100));
        $g = min(255, hexdec(substr($hex, 2, 2)) + (255 * $percent / 100));
        $b = min(255, hexdec(substr($hex, 4, 2)) + (255 * $percent / 100));
        
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    /**
     * Escurecer uma cor
     */
    private function darkenColor(string $hex, int $percent): string
    {
        $hex = str_replace('#', '', $hex);
        
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        $r = max(0, hexdec(substr($hex, 0, 2)) - (hexdec(substr($hex, 0, 2)) * $percent / 100));
        $g = max(0, hexdec(substr($hex, 2, 2)) - (hexdec(substr($hex, 2, 2)) * $percent / 100));
        $b = max(0, hexdec(substr($hex, 4, 2)) - (hexdec(substr($hex, 4, 2)) * $percent / 100));
        
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}