<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BackupSystemCommand extends Command
{
    protected $signature = 'olika:backup {--type=all : Tipo de backup (database, files, all)}';
    protected $description = 'Cria backup do sistema Olika';

    public function handle()
    {
        $type = $this->option('type');
        
        $this->info('Iniciando backup do sistema Olika...');
        
        try {
            switch ($type) {
                case 'database':
                    $this->backupDatabase();
                    break;
                case 'files':
                    $this->backupFiles();
                    break;
                case 'all':
                default:
                    $this->backupDatabase();
                    $this->backupFiles();
                    break;
            }
            
            $this->info('Backup concluído com sucesso!');
            
        } catch (\Exception $e) {
            $this->error('Erro durante backup: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }

    private function backupDatabase()
    {
        $this->info('Fazendo backup do banco de dados...');
        
        $filename = 'database_backup_' . Carbon::now()->format('Y_m_d_H_i_s') . '.sql';
        $path = 'backups/database/' . $filename;
        
        // Comando mysqldump (ajustar conforme configuração)
        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s %s > %s',
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password'),
            config('database.connections.mysql.host'),
            config('database.connections.mysql.database'),
            storage_path('app/' . $path)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            $this->info("Backup do banco salvo em: {$path}");
        } else {
            throw new \Exception('Erro ao fazer backup do banco de dados');
        }
    }

    private function backupFiles()
    {
        $this->info('Fazendo backup dos arquivos...');
        
        $filename = 'files_backup_' . Carbon::now()->format('Y_m_d_H_i_s') . '.zip';
        $path = 'backups/files/' . $filename;
        
        // Criar arquivo ZIP com arquivos importantes
        $zip = new \ZipArchive();
        $zipPath = storage_path('app/' . $path);
        
        if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
            // Adicionar arquivos de configuração
            $zip->addFile(base_path('.env'), '.env');
            $zip->addFile(base_path('composer.json'), 'composer.json');
            $zip->addFile(base_path('composer.lock'), 'composer.lock');
            
            // Adicionar storage/app/public
            $this->addDirectoryToZip($zip, storage_path('app/public'), 'storage');
            
            $zip->close();
            $this->info("Backup de arquivos salvo em: {$path}");
        } else {
            throw new \Exception('Erro ao criar arquivo ZIP');
        }
    }

    private function addDirectoryToZip($zip, $dir, $zipDir = '')
    {
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $filePath = $dir . '/' . $file;
                    $zipPath = $zipDir . '/' . $file;
                    
                    if (is_dir($filePath)) {
                        $zip->addEmptyDir($zipPath);
                        $this->addDirectoryToZip($zip, $filePath, $zipPath);
                    } else {
                        $zip->addFile($filePath, $zipPath);
                    }
                }
            }
        }
    }
}
