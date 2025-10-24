<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class CleanupSystemCommand extends Command
{
    protected $signature = 'olika:cleanup {--days=30 : Dias para manter logs}';
    protected $description = 'Limpa arquivos temporários e logs antigos do sistema Olika';

    public function handle()
    {
        $days = $this->option('days');
        $this->info("Iniciando limpeza do sistema (mantendo últimos {$days} dias)...");
        
        try {
            $this->cleanupLogs($days);
            $this->cleanupCache();
            $this->cleanupTempFiles();
            $this->cleanupBackups($days);
            
            $this->info('Limpeza concluída com sucesso!');
            
        } catch (\Exception $e) {
            $this->error('Erro durante limpeza: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }

    private function cleanupLogs(int $days)
    {
        $this->info('Limpando logs antigos...');
        
        $logPath = storage_path('logs');
        $cutoffDate = Carbon::now()->subDays($days);
        
        if (is_dir($logPath)) {
            $files = glob($logPath . '/*.log');
            $deletedCount = 0;
            
            foreach ($files as $file) {
                if (filemtime($file) < $cutoffDate->timestamp) {
                    unlink($file);
                    $deletedCount++;
                }
            }
            
            $this->info("Removidos {$deletedCount} arquivos de log antigos.");
        }
    }

    private function cleanupCache()
    {
        $this->info('Limpando cache...');
        
        Cache::flush();
        $this->info('Cache limpo com sucesso.');
    }

    private function cleanupTempFiles()
    {
        $this->info('Limpando arquivos temporários...');
        
        $tempPaths = [
            storage_path('app/temp'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
        ];
        
        $deletedCount = 0;
        
        foreach ($tempPaths as $path) {
            if (is_dir($path)) {
                $files = glob($path . '/*');
                foreach ($files as $file) {
                    if (is_file($file) && filemtime($file) < time() - 3600) { // 1 hora
                        unlink($file);
                        $deletedCount++;
                    }
                }
            }
        }
        
        $this->info("Removidos {$deletedCount} arquivos temporários.");
    }

    private function cleanupBackups(int $days)
    {
        $this->info('Limpando backups antigos...');
        
        $backupPath = storage_path('app/backups');
        $cutoffDate = Carbon::now()->subDays($days);
        $deletedCount = 0;
        
        if (is_dir($backupPath)) {
            $this->cleanupDirectory($backupPath, $cutoffDate, $deletedCount);
        }
        
        $this->info("Removidos {$deletedCount} arquivos de backup antigos.");
    }

    private function cleanupDirectory(string $dir, Carbon $cutoffDate, int &$deletedCount)
    {
        $files = glob($dir . '/*');
        
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->cleanupDirectory($file, $cutoffDate, $deletedCount);
                
                // Remove diretório vazio
                if (count(glob($file . '/*')) === 0) {
                    rmdir($file);
                }
            } elseif (is_file($file) && filemtime($file) < $cutoffDate->timestamp) {
                unlink($file);
                $deletedCount++;
            }
        }
    }
}
