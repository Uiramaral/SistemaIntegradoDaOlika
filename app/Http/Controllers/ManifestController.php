<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ManifestController extends Controller
{
    /**
     * Gera o manifest.json dinamicamente com cores e ícones personalizados
     */
    public function index(): JsonResponse
    {
        $clientId = currentClientId();
        
        // Buscar configurações de tema
        $settings = \App\Models\Setting::getSettings($clientId);
        $themeSettings = $settings->getThemeSettings();
        
        // Buscar personalização de payment_settings
        $personalizationSettings = \App\Models\PaymentSetting::where('client_id', $clientId)
            ->whereIn('key', ['logo', 'favicon', 'theme_color'])
            ->pluck('value', 'key')
            ->toArray();
        
        // Cor do tema
        $themeColor = $personalizationSettings['theme_color'] ?? $themeSettings['theme_primary_color'] ?? '#f59e0b';
        
        // Nome da marca
        $brandName = $themeSettings['theme_brand_name'] ?? 'OLIKA';
        
        // Verificar se há favicons em public/favicon/ (prioridade máxima)
        $usePublicFavicons = file_exists(public_path('favicon/favicon.ico'));
        
        // Buscar favicon personalizado das configurações (fallback)
        $faviconUrl = null;
        if (!$usePublicFavicons) {
            if (isset($personalizationSettings['favicon']) && $personalizationSettings['favicon']) {
                $faviconPath = storage_path('app/public/' . $personalizationSettings['favicon']);
                if (file_exists($faviconPath)) {
                    $faviconUrl = asset('storage/' . $personalizationSettings['favicon']);
                }
            }
            
            // Se não tiver favicon personalizado, usar logo como fallback
            if (!$faviconUrl) {
                if (isset($personalizationSettings['logo']) && $personalizationSettings['logo']) {
                    $logoPath = storage_path('app/public/' . $personalizationSettings['logo']);
                    if (file_exists($logoPath)) {
                        $faviconUrl = asset('storage/' . $personalizationSettings['logo']);
                    }
                } else {
                    $faviconUrl = $themeSettings['theme_logo_url'] ?? null;
                }
            }
            
            // Se ainda não tiver, usar ícone padrão
            if (!$faviconUrl) {
                $faviconUrl = asset('pwa-192x192.svg');
            }
        }
        
        // Gerar ícones PNG para PWA nos tamanhos corretos
        $icons = [];
        
        // Prioridade 1: Verificar se há favicons em public/favicon/ (gerados pelo genfavicon)
        $faviconDir = public_path('favicon');
        if (file_exists($faviconDir)) {
            $favicon512 = $faviconDir . '/genfavicon-512.png';
            
            // Tentar encontrar o melhor ícone para 192x192
            // Ordem de preferência: 256 > 180 > 128
            $favicon192 = null;
            $sizesToTry = [256, 180, 128];
            foreach ($sizesToTry as $size) {
                $testPath = $faviconDir . '/genfavicon-' . $size . '.png';
                if (file_exists($testPath)) {
                    $favicon192 = $testPath;
                    break;
                }
            }
            
            // Se não encontrou nenhum, usar o 512 e redimensionar
            if (!$favicon192 && file_exists($favicon512)) {
                $favicon192 = $favicon512; // Será usado como fallback
            }
            
            if (file_exists($favicon512)) {
                $icons = [];
                
                // Ícone 192x192 (usar o mais próximo disponível ou gerar)
                if ($favicon192 && file_exists($favicon192)) {
                    // Se for o 512, precisamos gerar um 192x192
                    if ($favicon192 === $favicon512) {
                        $generated192 = $this->generateIconFromSource($favicon512, 192, $clientId);
                        if ($generated192) {
                            $icons[] = [
                                'src' => $generated192,
                                'sizes' => '192x192',
                                'type' => 'image/png',
                            ];
                        } else {
                            // Fallback: usar o 512 mesmo
                            $icons[] = [
                                'src' => asset('favicon/genfavicon-512.png') . '?v=' . filemtime($favicon512),
                                'sizes' => '192x192',
                                'type' => 'image/png',
                            ];
                        }
                    } else {
                        // Usar o ícone disponível mais próximo (256, 180 ou 128)
                        $icons[] = [
                            'src' => asset('favicon/' . basename($favicon192)) . '?v=' . filemtime($favicon192),
                            'sizes' => '192x192',
                            'type' => 'image/png',
                        ];
                    }
                } elseif (file_exists($favicon512)) {
                    // Se não encontrou nenhum para 192, gerar a partir do 512
                    $generated192 = $this->generateIconFromSource($favicon512, 192, $clientId);
                    if ($generated192) {
                        $icons[] = [
                            'src' => $generated192,
                            'sizes' => '192x192',
                            'type' => 'image/png',
                        ];
                    }
                }
                
                // Ícone 512x512
                $icons[] = [
                    'src' => asset('favicon/genfavicon-512.png') . '?v=' . filemtime($favicon512),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                ];
                
                // Versão maskable
                $icons[] = [
                    'src' => asset('favicon/genfavicon-512.png') . '?v=' . filemtime($favicon512),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable',
                ];
            }
        }
        
        // Prioridade 2: Se não encontrou favicons em public/favicon/, usar favicon/logo personalizado
        if (empty($icons)) {
            // Buscar favicon personalizado das configurações
            $faviconUrl = null;
            if (isset($personalizationSettings['favicon']) && $personalizationSettings['favicon']) {
                $faviconPath = storage_path('app/public/' . $personalizationSettings['favicon']);
                if (file_exists($faviconPath)) {
                    $faviconUrl = asset('storage/' . $personalizationSettings['favicon']);
                }
            }
            
            // Se não tiver favicon personalizado, usar logo como fallback
            if (!$faviconUrl) {
                if (isset($personalizationSettings['logo']) && $personalizationSettings['logo']) {
                    $logoPath = storage_path('app/public/' . $personalizationSettings['logo']);
                    if (file_exists($logoPath)) {
                        $faviconUrl = asset('storage/' . $personalizationSettings['logo']);
                    }
                } else {
                    $faviconUrl = $themeSettings['theme_logo_url'] ?? null;
                }
            }
            
            if ($faviconUrl && $faviconUrl !== asset('pwa-192x192.svg')) {
                // Encontrar o arquivo fonte
                $sourcePath = null;
                if (isset($personalizationSettings['favicon']) && $personalizationSettings['favicon']) {
                    $sourcePath = storage_path('app/public/' . $personalizationSettings['favicon']);
                } elseif (isset($personalizationSettings['logo']) && $personalizationSettings['logo']) {
                    $sourcePath = storage_path('app/public/' . $personalizationSettings['logo']);
                }
                
                if ($sourcePath && file_exists($sourcePath)) {
                    // Gerar ícones PNG nos tamanhos corretos
                    $pwaIcons = $this->generatePwaIcons($sourcePath, $clientId);
                    
                    if (!empty($pwaIcons)) {
                        $icons = $pwaIcons;
                    }
                }
            }
        }
        
        // Prioridade 3: Se não gerou ícones personalizados, usar padrão
        if (empty($icons)) {
            // Verificar se existem os PNGs diretos do CorelDRAW
            $png192 = public_path('pwa-192x192_Images/pwa-192x192_ImgID1.png');
            $png512 = public_path('pwa-512x512_Images/pwa-512x512_ImgID1.png');
            
            if (file_exists($png192) && file_exists($png512)) {
                // Usar os PNGs exportados do CorelDRAW (funciona em PWA)
                // Adicionar timestamp para forçar reload
                $v192 = filemtime($png192);
                $v512 = filemtime($png512);
                
                $icons = [
                    [
                        'src' => asset('pwa-192x192_Images/pwa-192x192_ImgID1.png') . '?v=' . $v192,
                        'sizes' => '192x192',
                        'type' => 'image/png',
                    ],
                    [
                        'src' => asset('pwa-512x512_Images/pwa-512x512_ImgID1.png') . '?v=' . $v512,
                        'sizes' => '512x512',
                        'type' => 'image/png',
                    ],
                    [
                        'src' => asset('pwa-512x512_Images/pwa-512x512_ImgID1.png') . '?v=' . $v512,
                        'sizes' => '512x512',
                        'type' => 'image/png',
                        'purpose' => 'any maskable',
                    ],
                ];
            } else {
                // Fallback para SVGs (funcionam no navegador mas não em PWA instalado)
                $icons = [
                    [
                        'src' => asset('pwa-192x192.svg'),
                        'sizes' => '192x192',
                        'type' => 'image/svg+xml',
                    ],
                    [
                        'src' => asset('pwa-512x512.svg'),
                        'sizes' => '512x512',
                        'type' => 'image/svg+xml',
                    ],
                    [
                        'src' => asset('pwa-512x512.svg'),
                        'sizes' => '512x512',
                        'type' => 'image/svg+xml',
                        'purpose' => 'any maskable',
                    ],
                ];
            }
        }
        
        $manifest = [
            'name' => $brandName . ' - Sistema de Gestão',
            'short_name' => $brandName,
            'description' => 'Sistema de gestão profissional',
            'theme_color' => $themeColor,
            'background_color' => '#ffffff',
            'display' => 'standalone',
            'orientation' => 'portrait',
            'scope' => '/',
            'start_url' => '/',
            'icons' => $icons,
        ];
        
        // Headers para evitar cache do manifest
        return response()->json($manifest)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
    
    /**
     * Gera ícones PNG para PWA nos tamanhos 192x192 e 512x512
     */
    private function generatePwaIcons($sourcePath, $clientId): array
    {
        try {
            $imageInfo = @getimagesize($sourcePath);
            if (!$imageInfo) {
                \Illuminate\Support\Facades\Log::warning('ManifestController: Não foi possível ler informações da imagem', ['path' => $sourcePath]);
                return [];
            }
            
            // Carregar imagem fonte
            $sourceImage = null;
            switch ($imageInfo[2]) {
                case IMAGETYPE_JPEG:
                    $sourceImage = @imagecreatefromjpeg($sourcePath);
                    break;
                case IMAGETYPE_PNG:
                    // Suprimir avisos do libpng sobre perfil iCCP incorreto (é apenas um aviso, não um erro)
                    $sourceImage = @imagecreatefrompng($sourcePath);
                    break;
                case IMAGETYPE_GIF:
                    $sourceImage = @imagecreatefromgif($sourcePath);
                    break;
                case IMAGETYPE_WEBP:
                    if (function_exists('imagecreatefromwebp')) {
                        $sourceImage = @imagecreatefromwebp($sourcePath);
                    }
                    break;
                default:
                    \Illuminate\Support\Facades\Log::warning('ManifestController: Formato de imagem não suportado', ['type' => $imageInfo[2]]);
                    return [];
            }
            
            if (!$sourceImage) {
                \Illuminate\Support\Facades\Log::warning('ManifestController: Não foi possível criar recurso de imagem');
                return [];
            }
            
            $icons = [];
            $sizes = [192, 512];
            $pwaIconsDir = storage_path('app/public/pwa-icons');
            
            if (!file_exists($pwaIconsDir)) {
                mkdir($pwaIconsDir, 0755, true);
            }
            
            foreach ($sizes as $size) {
                $iconPath = "pwa-icons/{$clientId}_{$size}x{$size}.png";
                $fullPath = storage_path('app/public/' . $iconPath);
                
                // Verificar se o ícone já existe e é recente (menos de 1 hora)
                $regenerate = true;
                if (file_exists($fullPath)) {
                    $fileTime = filemtime($fullPath);
                    $regenerate = (time() - $fileTime) > 3600; // 1 hora
                }
                
                if ($regenerate) {
                    // Criar ícone no tamanho especificado
                    $icon = imagecreatetruecolor($size, $size);
                    imagealphablending($icon, false);
                    imagesavealpha($icon, true);
                    
                    // Preencher com transparência
                    $transparent = imagecolorallocatealpha($icon, 0, 0, 0, 127);
                    imagefill($icon, 0, 0, $transparent);
                    
                    // Redimensionar mantendo proporção (centralizado)
                    $sourceWidth = $imageInfo[0];
                    $sourceHeight = $imageInfo[1];
                    
                    // Calcular dimensões mantendo proporção
                    $ratio = min($size / $sourceWidth, $size / $sourceHeight);
                    $newWidth = (int)($sourceWidth * $ratio);
                    $newHeight = (int)($sourceHeight * $ratio);
                    
                    // Centralizar
                    $x = (int)(($size - $newWidth) / 2);
                    $y = (int)(($size - $newHeight) / 2);
                    
                    imagecopyresampled($icon, $sourceImage, $x, $y, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);
                    
                    imagealphablending($icon, false);
                    imagesavealpha($icon, true);
                    
                    if (imagepng($icon, $fullPath, 9)) {
                        \Illuminate\Support\Facades\Log::info("ManifestController: Ícone PWA gerado", [
                            'size' => $size,
                            'path' => $iconPath
                        ]);
                    }
                    
                    imagedestroy($icon);
                }
                
                // Adicionar ao array de ícones (mesmo que já exista)
                if (file_exists($fullPath)) {
                    $icons[] = [
                        'src' => asset('storage/' . $iconPath) . '?v=' . filemtime($fullPath),
                        'sizes' => "{$size}x{$size}",
                        'type' => 'image/png',
                    ];
                }
            }
            
            // Adicionar versão maskable do 512x512
            if (isset($icons[1])) {
                $icons[] = [
                    'src' => $icons[1]['src'],
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable',
                ];
            }
            
            imagedestroy($sourceImage);
            
            \Illuminate\Support\Facades\Log::info('ManifestController: Ícones PWA gerados com sucesso', [
                'client_id' => $clientId,
                'icons_count' => count($icons)
            ]);
            
            return $icons;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('ManifestController: Erro ao gerar ícones PWA: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }
    
    /**
     * Gera um ícone PNG de tamanho específico a partir de uma imagem fonte
     */
    private function generateIconFromSource($sourcePath, $targetSize, $clientId): ?string
    {
        try {
            $imageInfo = @getimagesize($sourcePath);
            if (!$imageInfo) {
                return null;
            }
            
            // Carregar imagem fonte
            $sourceImage = null;
            switch ($imageInfo[2]) {
                case IMAGETYPE_JPEG:
                    $sourceImage = @imagecreatefromjpeg($sourcePath);
                    break;
                case IMAGETYPE_PNG:
                    // Suprimir avisos do libpng sobre perfil iCCP incorreto
                    $sourceImage = @imagecreatefrompng($sourcePath);
                    break;
                case IMAGETYPE_GIF:
                    $sourceImage = @imagecreatefromgif($sourcePath);
                    break;
                case IMAGETYPE_WEBP:
                    if (function_exists('imagecreatefromwebp')) {
                        $sourceImage = @imagecreatefromwebp($sourcePath);
                    }
                    break;
                default:
                    return null;
            }
            
            if (!$sourceImage) {
                return null;
            }
            
            // Criar ícone no tamanho especificado
            $icon = imagecreatetruecolor($targetSize, $targetSize);
            imagealphablending($icon, false);
            imagesavealpha($icon, true);
            
            // Preencher com transparência
            $transparent = imagecolorallocatealpha($icon, 0, 0, 0, 127);
            imagefill($icon, 0, 0, $transparent);
            
            // Redimensionar mantendo proporção (centralizado)
            $sourceWidth = $imageInfo[0];
            $sourceHeight = $imageInfo[1];
            
            $ratio = min($targetSize / $sourceWidth, $targetSize / $sourceHeight);
            $newWidth = (int)($sourceWidth * $ratio);
            $newHeight = (int)($sourceHeight * $ratio);
            
            $x = (int)(($targetSize - $newWidth) / 2);
            $y = (int)(($targetSize - $newHeight) / 2);
            
            imagecopyresampled($icon, $sourceImage, $x, $y, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);
            
            // Salvar ícone
            $pwaIconsDir = storage_path('app/public/pwa-icons');
            if (!file_exists($pwaIconsDir)) {
                mkdir($pwaIconsDir, 0755, true);
            }
            
            $iconPath = "pwa-icons/{$clientId}_{$targetSize}x{$targetSize}.png";
            $fullPath = storage_path('app/public/' . $iconPath);
            
            imagealphablending($icon, false);
            imagesavealpha($icon, true);
            
            if (imagepng($icon, $fullPath, 9)) {
                imagedestroy($icon);
                imagedestroy($sourceImage);
                return asset('storage/' . $iconPath) . '?v=' . time();
            }
            
            imagedestroy($icon);
            imagedestroy($sourceImage);
            return null;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('ManifestController: Erro ao gerar ícone: ' . $e->getMessage());
            return null;
        }
    }
}
