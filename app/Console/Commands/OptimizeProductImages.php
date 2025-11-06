<?php

namespace App\Console\Commands;

use App\Helpers\ImageOptimizer;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class OptimizeProductImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:optimize-images {--force : Forçar re-otimização mesmo se já existir}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otimiza todas as imagens de produtos, gerando versões WebP e thumbnails';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🖼️  Iniciando otimização de imagens de produtos...');
        
        $force = $this->option('force');
        $disk = Storage::disk('public');
        
        $processed = 0;
        $skipped = 0;
        $errors = 0;
        
        // Processar imagens de capa
        $products = Product::whereNotNull('cover_image')
            ->where('cover_image', '!=', '')
            ->get();
        
        $this->info("📦 Encontrados {$products->count()} produtos com imagem de capa");
        
        foreach ($products as $product) {
            $path = $product->cover_image;
            
            if (!$disk->exists($path)) {
                $this->warn("⚠️  Imagem não encontrada: {$path}");
                $errors++;
                continue;
            }
            
            // Verificar se já existe WebP (se não forçar)
            if (!$force) {
                $pathInfo = pathinfo($path);
                $webpPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.webp';
                
                if ($disk->exists($webpPath)) {
                    $this->line("⏭️  Já otimizada: {$path}");
                    $skipped++;
                    continue;
                }
            }
            
            try {
                ImageOptimizer::optimize($path);
                $this->line("✅ Otimizada: {$path}");
                $processed++;
            } catch (\Exception $e) {
                $this->error("❌ Erro ao otimizar {$path}: {$e->getMessage()}");
                $errors++;
            }
        }
        
        // Processar imagens da galeria
        $images = ProductImage::all();
        $this->info("📸 Encontradas {$images->count()} imagens na galeria");
        
        foreach ($images as $image) {
            $path = $image->path;
            
            if (!$disk->exists($path)) {
                $this->warn("⚠️  Imagem não encontrada: {$path}");
                $errors++;
                continue;
            }
            
            // Verificar se já existe WebP (se não forçar)
            if (!$force) {
                $pathInfo = pathinfo($path);
                $webpPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.webp';
                
                if ($disk->exists($webpPath)) {
                    $this->line("⏭️  Já otimizada: {$path}");
                    $skipped++;
                    continue;
                }
            }
            
            try {
                ImageOptimizer::optimize($path);
                $this->line("✅ Otimizada: {$path}");
                $processed++;
            } catch (\Exception $e) {
                $this->error("❌ Erro ao otimizar {$path}: {$e->getMessage()}");
                $errors++;
            }
        }
        
        $this->info("\n📊 Resumo:");
        $this->info("   ✅ Processadas: {$processed}");
        $this->info("   ⏭️  Puladas: {$skipped}");
        $this->info("   ❌ Erros: {$errors}");
        
        if ($processed > 0) {
            $this->info("\n🎉 Otimização concluída!");
        }
        
        return Command::SUCCESS;
    }
}


