<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    /**
     * Exibir página de configuração de temas
     */
    public function index()
    {
        $clientSettings = Setting::getSettings();
        $themeSettings = $clientSettings->getThemeSettings();
        
        return view('dashboard.themes.index', compact('themeSettings'));
    }

    /**
     * Atualizar configurações do tema
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'theme_primary_color' => 'nullable|string|regex:/^#[0-9a-fA-F]{6}$/',
            'theme_secondary_color' => 'nullable|string|regex:/^#[0-9a-fA-F]{6}$/',
            'theme_accent_color' => 'nullable|string|regex:/^#[0-9a-fA-F]{6}$/',
            'theme_background_color' => 'nullable|string|regex:/^#[0-9a-fA-F]{6}$/',
            'theme_text_color' => 'nullable|string|regex:/^#[0-9a-fA-F]{6}$/',
            'theme_border_color' => 'nullable|string|regex:/^#[0-9a-fA-F]{6}$/',
            'theme_logo_url' => 'nullable|url|max:255',
            'theme_favicon_url' => 'nullable|url|max:255',
            'theme_brand_name' => 'nullable|string|max:100',
            'theme_font_family' => 'nullable|string|max:255',
            'theme_border_radius' => 'nullable|string|max:20',
            'theme_shadow_style' => 'nullable|string|max:255',
        ]);

        // Filtrar apenas os campos que foram enviados
        $settingsToUpdate = array_filter($validated, function($value) {
            return $value !== null;
        });

        if (!empty($settingsToUpdate)) {
            $clientSettings = Setting::getSettings();
            $clientSettings->updateThemeSettings($settingsToUpdate);
        }

        return back()->with('success', 'Configurações de tema atualizadas com sucesso!');
    }

    /**
     * Resetar para tema padrão
     */
    public function reset()
    {
        $defaultSettings = [
            'theme_primary_color' => '#f59e0b',
            'theme_secondary_color' => '#8b5cf6',
            'theme_accent_color' => '#10b981',
            'theme_background_color' => '#ffffff',
            'theme_text_color' => '#1f2937',
            'theme_border_color' => '#e5e7eb',
            'theme_logo_url' => null,
            'theme_favicon_url' => null,
            'theme_brand_name' => null,
            'theme_font_family' => "'Inter', -apple-system, BlinkMacSystemFont, sans-serif",
            'theme_border_radius' => '12px',
            'theme_shadow_style' => '0 4px 12px rgba(0,0,0,0.08)',
        ];

        $clientSettings = Setting::getSettings();
        $clientSettings->updateThemeSettings($defaultSettings);

        return back()->with('success', 'Tema resetado para configurações padrão!');
    }
}