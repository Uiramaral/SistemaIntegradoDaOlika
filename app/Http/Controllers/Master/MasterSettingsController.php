<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterSetting;
use Illuminate\Http\Request;

class MasterSettingsController extends Controller
{
    /**
     * Exibe configurações do Master
     */
    public function index()
    {
        $settings = [
            'whatsapp_instance_price' => MasterSetting::get('whatsapp_instance_price', 15.00),
            'ai_message_price' => MasterSetting::get('ai_message_price', 5.00),
            'default_trial_days' => MasterSetting::get('default_trial_days', 7),
            'billing_cycle_days' => MasterSetting::get('billing_cycle_days', 30),
            'expiry_warning_days_1' => MasterSetting::get('expiry_warning_days_1', 7),
            'expiry_warning_days_2' => MasterSetting::get('expiry_warning_days_2', 3),
            'expiry_warning_days_3' => MasterSetting::get('expiry_warning_days_3', 1),
            'grace_period_days' => MasterSetting::get('grace_period_days', 3),
            'support_email' => MasterSetting::get('support_email', ''),
            'billing_email' => MasterSetting::get('billing_email', ''),
            'support_whatsapp' => MasterSetting::get('support_whatsapp', ''),
            // APIs & Integrações (gestão única no Master; usadas por todo o sistema)
            'gemini_api_key' => MasterSetting::get('gemini_api_key', ''),
            'openai_api_key' => MasterSetting::get('openai_api_key', ''),
            'openai_model' => MasterSetting::get('openai_model', 'gpt-4o-mini'),
            'google_maps_api_key' => MasterSetting::get('google_maps_api_key', ''),
            // Personalização do Sistema (apenas Master)
            'system_logo_url' => MasterSetting::get('system_logo_url', ''),
            'system_favicon_url' => MasterSetting::get('system_favicon_url', ''),
            'system_name' => MasterSetting::get('system_name', 'OLIKA'),
            'system_welcome_message' => MasterSetting::get('system_welcome_message', 'Bem-vindo ao OLIKA'),
            // Termos e Políticas
            'terms_of_use' => MasterSetting::get('terms_of_use', ''),
            'privacy_policy' => MasterSetting::get('privacy_policy', ''),
        ];

        return view('master.settings.index', compact('settings'));
    }

    /**
     * Salva configurações
     */
    public function update(Request $request)
    {
        // Get all fillable settings from the request
        $settingsKeys = [
            'whatsapp_instance_price',
            'ai_message_price',
            'default_trial_days',
            'billing_cycle_days',
            'expiry_warning_days_1',
            'expiry_warning_days_2',
            'expiry_warning_days_3',
            'grace_period_days',
            'support_email',
            'billing_email',
            'support_whatsapp',
            // ⚡ NOVO: Configurações de Cadastro
            'registration_trial_days',
            'registration_default_commission',
            'registration_commission_enabled',
            'registration_default_plan',
            'registration_require_approval',
            'registration_notify_master',
            'registration_master_email',
            // APIs & Integrações (Gemini, OpenAI, Google Maps)
            'gemini_api_key',
            'openai_api_key',
            'openai_model',
            'google_maps_api_key',
            // Personalização do Sistema (apenas Master)
            'system_logo_url',
            'system_favicon_url',
            'system_name',
            'system_welcome_message',
            // Termos e Políticas
            'terms_of_use',
            'privacy_policy',
        ];
        
        // Processar upload de logo primeiro (se houver)
        if ($request->hasFile('system_logo')) {
            $logoPath = $request->file('system_logo')->store('system-logos', 'public');
            $logoUrl = asset('storage/' . $logoPath);
            MasterSetting::set('system_logo_url', $logoUrl);
            
            // Se solicitado, gerar favicon automaticamente a partir da logo
            if ($request->input('generate_favicon_from_logo')) {
                $faviconPath = $this->generateFaviconFromLogo($request->file('system_logo'));
                if ($faviconPath) {
                    MasterSetting::set('system_favicon_url', asset('storage/' . $faviconPath));
                }
            }
        }
        
        // Processar upload de favicon (se não foi gerado automaticamente)
        if ($request->hasFile('system_favicon') && !$request->input('generate_favicon_from_logo')) {
            $faviconPath = $request->file('system_favicon')->store('system-favicons', 'public');
            $faviconUrl = asset('storage/' . $faviconPath);
            MasterSetting::set('system_favicon_url', $faviconUrl);
        }
        
        foreach ($settingsKeys as $key) {
            // Pular system_logo_url se já foi processado via upload
            if ($key === 'system_logo_url' && $request->hasFile('system_logo')) {
                continue;
            }
            
            if ($request->has($key)) {
                $value = $request->input($key);
                
                // Tratar checkboxes (se não vier no request, é false)
                if (in_array($key, ['registration_commission_enabled', 'registration_require_approval', 'registration_notify_master'])) {
                    $value = $request->has($key) ? 1 : 0;
                }
                
                MasterSetting::set($key, $value);
            }
        }
        
        // Limpar cache após atualizar
        \Illuminate\Support\Facades\Cache::flush();

        return back()->with('success', 'Configurações salvas com sucesso!');
    }

    /**
     * Configurações individuais via AJAX
     */
    public function updateSingle(Request $request)
    {
        $request->validate([
            'key' => 'required|string|max:100',
            'value' => 'required',
            'type' => 'nullable|in:string,integer,decimal,boolean,json',
        ]);

        MasterSetting::set(
            $request->key, 
            $request->value, 
            $request->type
        );

        return response()->json([
            'success' => true,
            'message' => 'Configuração atualizada com sucesso!',
        ]);
    }

    /**
     * Gerar favicon automaticamente a partir da logo
     */
    private function generateFaviconFromLogo($logoFile)
    {
        try {
            $imageInfo = getimagesize($logoFile->getRealPath());
            if (!$imageInfo) {
                \Illuminate\Support\Facades\Log::warning('Não foi possível ler informações da imagem');
                return null;
            }

            $sourceImage = null;
            switch ($imageInfo[2]) {
                case IMAGETYPE_JPEG:
                    $sourceImage = imagecreatefromjpeg($logoFile->getRealPath());
                    break;
                case IMAGETYPE_PNG:
                    $sourceImage = imagecreatefrompng($logoFile->getRealPath());
                    break;
                case IMAGETYPE_GIF:
                    $sourceImage = imagecreatefromgif($logoFile->getRealPath());
                    break;
                default:
                    \Illuminate\Support\Facades\Log::warning('Formato de imagem não suportado para geração de favicon');
                    return null;
            }

            if (!$sourceImage) {
                \Illuminate\Support\Facades\Log::warning('Não foi possível criar recurso de imagem');
                return null;
            }

            // Criar favicon 32x32
            $favicon = imagecreatetruecolor(32, 32);
            imagealphablending($favicon, false);
            imagesavealpha($favicon, true);

            // Preencher com transparência
            $transparent = imagecolorallocatealpha($favicon, 0, 0, 0, 127);
            imagefill($favicon, 0, 0, $transparent);

            // Redimensionar mantendo proporção
            imagecopyresampled($favicon, $sourceImage, 0, 0, 0, 0, 32, 32, $imageInfo[0], $imageInfo[1]);

            // Salvar favicon
            $faviconPath = 'system-favicons/' . time() . '.png';
            $faviconsDir = storage_path('app/public/system-favicons');
            if (!file_exists($faviconsDir)) {
                mkdir($faviconsDir, 0755, true);
            }
            imagealphablending($favicon, false);
            imagesavealpha($favicon, true);
            imagepng($favicon, storage_path('app/public/' . $faviconPath));
            imagedestroy($favicon);
            imagedestroy($sourceImage);

            \Illuminate\Support\Facades\Log::info('Favicon gerado com sucesso', ['path' => $faviconPath]);
            return $faviconPath;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao gerar favicon: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return null;
        }
    }
}
