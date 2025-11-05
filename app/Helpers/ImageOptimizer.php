<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class ImageOptimizer
{
    /**
     * Gera versões WebP e thumbnail da imagem
     * 
     * @param string $originalPath Caminho da imagem original (relativo ao storage/public)
     * @param array $sizes Tamanhos de thumbnail a gerar ['thumb' => [width, height], ...]
     * @return array ['webp' => path, 'thumbnail' => path]
     */
    public static function optimize($originalPath, $sizes = ['thumb' => [400, 400], 'small' => [200, 200]])
    {
        $disk = Storage::disk('public');
        
        // Verificar se a imagem original existe
        if (!$disk->exists($originalPath)) {
            return null;
        }
        
        $originalFullPath = $disk->path($originalPath);
        $pathInfo = pathinfo($originalPath);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        $extension = strtolower($pathInfo['extension'] ?? 'jpg');
        
        // Verificar se é uma imagem válida
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            return null;
        }
        
        $results = [];
        
        // Carregar imagem original
        $image = null;
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $image = @imagecreatefromjpeg($originalFullPath);
                break;
            case 'png':
                $image = @imagecreatefrompng($originalFullPath);
                break;
            case 'gif':
                $image = @imagecreatefromgif($originalFullPath);
                break;
            case 'webp':
                $image = @imagecreatefromwebp($originalFullPath);
                break;
        }
        
        if (!$image) {
            return null;
        }
        
        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);
        
        // Gerar WebP
        $webpPath = $directory . '/' . $filename . '.webp';
        $webpFullPath = $disk->path($webpPath);
        
        // Criar diretório se não existir
        $webpDir = dirname($webpFullPath);
        if (!is_dir($webpDir)) {
            mkdir($webpDir, 0755, true);
        }
        
        // Converter para WebP (qualidade 85%)
        if (function_exists('imagewebp')) {
            // Preservar transparência para PNG
            if ($extension === 'png') {
                imagealphablending($image, false);
                imagesavealpha($image, true);
            }
            
            if (imagewebp($image, $webpFullPath, 85)) {
                $results['webp'] = $webpPath;
            }
        }
        
        // Gerar thumbnails
        foreach ($sizes as $sizeName => $dimensions) {
            list($thumbWidth, $thumbHeight) = $dimensions;
            
            // Calcular dimensões mantendo proporção
            $ratio = min($thumbWidth / $originalWidth, $thumbHeight / $originalHeight);
            $newWidth = (int)($originalWidth * $ratio);
            $newHeight = (int)($originalHeight * $ratio);
            
            // Criar thumbnail
            $thumb = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preservar transparência
            if ($extension === 'png' || $extension === 'webp') {
                imagealphablending($thumb, false);
                imagesavealpha($thumb, true);
                $transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
                imagefill($thumb, 0, 0, $transparent);
            }
            
            // Redimensionar
            imagecopyresampled(
                $thumb, $image,
                0, 0, 0, 0,
                $newWidth, $newHeight,
                $originalWidth, $originalHeight
            );
            
            // Salvar thumbnail WebP
            $thumbWebpPath = $directory . '/thumbs/' . $filename . '-' . $sizeName . '.webp';
            $thumbWebpFullPath = $disk->path($thumbWebpPath);
            
            $thumbDir = dirname($thumbWebpFullPath);
            if (!is_dir($thumbDir)) {
                mkdir($thumbDir, 0755, true);
            }
            
            if (function_exists('imagewebp')) {
                if (imagewebp($thumb, $thumbWebpFullPath, 85)) {
                    $results['thumbnails'][$sizeName]['webp'] = $thumbWebpPath;
                }
            }
            
            // Salvar thumbnail JPG (fallback)
            $thumbJpgPath = $directory . '/thumbs/' . $filename . '-' . $sizeName . '.jpg';
            $thumbJpgFullPath = $disk->path($thumbJpgPath);
            
            if (imagejpeg($thumb, $thumbJpgFullPath, 85)) {
                $results['thumbnails'][$sizeName]['jpg'] = $thumbJpgPath;
            }
            
            imagedestroy($thumb);
        }
        
        imagedestroy($image);
        
        return $results;
    }
    
    /**
     * Limpa arquivos otimizados relacionados a uma imagem
     */
    public static function cleanup($originalPath)
    {
        $disk = Storage::disk('public');
        $pathInfo = pathinfo($originalPath);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        
        // Deletar WebP
        $webpPath = $directory . '/' . $filename . '.webp';
        if ($disk->exists($webpPath)) {
            $disk->delete($webpPath);
        }
        
        // Deletar thumbnails
        $thumbDir = $directory . '/thumbs';
        if ($disk->exists($thumbDir)) {
            $files = $disk->files($thumbDir);
            foreach ($files as $file) {
                if (strpos(basename($file), $filename) === 0) {
                    $disk->delete($file);
                }
            }
        }
    }
    
    /**
     * Obtém URL otimizada da imagem (WebP se disponível, senão original)
     */
    public static function getOptimizedUrl($originalPath, $size = 'thumb')
    {
        if (!$originalPath) {
            return asset('images/produto-placeholder.jpg');
        }
        
        $disk = Storage::disk('public');
        $pathInfo = pathinfo($originalPath);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        
        // Tentar WebP thumbnail primeiro
        if ($size) {
            $webpThumb = $directory . '/thumbs/' . $filename . '-' . $size . '.webp';
            if ($disk->exists($webpThumb)) {
                return asset('storage/' . $webpThumb);
            }
            
            $jpgThumb = $directory . '/thumbs/' . $filename . '-' . $size . '.jpg';
            if ($disk->exists($jpgThumb)) {
                return asset('storage/' . $jpgThumb);
            }
        }
        
        // Tentar WebP original
        $webpPath = $directory . '/' . $filename . '.webp';
        if ($disk->exists($webpPath)) {
            return asset('storage/' . $webpPath);
        }
        
        // Fallback para original
        return asset('storage/' . $originalPath);
    }
}

