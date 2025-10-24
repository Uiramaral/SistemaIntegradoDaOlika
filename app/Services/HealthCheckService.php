<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;

class HealthCheckService
{
    /**
     * Verifica saúde do sistema
     */
    public function checkSystemHealth(): array
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'models' => $this->checkModels(),
            'performance' => $this->checkPerformance(),
        ];

        $overallStatus = $this->calculateOverallStatus($checks);

        return [
            'status' => $overallStatus,
            'timestamp' => now()->toISOString(),
            'checks' => $checks,
        ];
    }

    /**
     * Verifica conexão com banco de dados
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            $tableCount = DB::select("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = ?", [config('database.connections.mysql.database')])[0]->count;
            
            return [
                'status' => 'healthy',
                'message' => 'Conexão com banco de dados OK',
                'tables_count' => $tableCount,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Erro na conexão com banco: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verifica sistema de cache
     */
    private function checkCache(): array
    {
        try {
            $testKey = 'health_check_' . time();
            Cache::put($testKey, 'test', 60);
            $value = Cache::get($testKey);
            Cache::forget($testKey);
            
            if ($value === 'test') {
                return [
                    'status' => 'healthy',
                    'message' => 'Sistema de cache funcionando',
                ];
            } else {
                return [
                    'status' => 'unhealthy',
                    'message' => 'Cache não está funcionando corretamente',
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Erro no sistema de cache: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verifica sistema de arquivos
     */
    private function checkStorage(): array
    {
        try {
            $testFile = 'health_check_' . time() . '.txt';
            Storage::put($testFile, 'test');
            $content = Storage::get($testFile);
            Storage::delete($testFile);
            
            if ($content === 'test') {
                $diskSpace = disk_free_space(storage_path());
                return [
                    'status' => 'healthy',
                    'message' => 'Sistema de arquivos OK',
                    'free_space_mb' => round($diskSpace / 1024 / 1024, 2),
                ];
            } else {
                return [
                    'status' => 'unhealthy',
                    'message' => 'Sistema de arquivos com problemas',
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Erro no sistema de arquivos: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verifica modelos principais
     */
    private function checkModels(): array
    {
        try {
            $productCount = Product::count();
            $customerCount = Customer::count();
            $orderCount = Order::count();
            
            return [
                'status' => 'healthy',
                'message' => 'Modelos funcionando corretamente',
                'stats' => [
                    'products' => $productCount,
                    'customers' => $customerCount,
                    'orders' => $orderCount,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Erro nos modelos: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verifica performance básica
     */
    private function checkPerformance(): array
    {
        $startTime = microtime(true);
        
        try {
            // Teste de consulta simples
            Product::active()->count();
            
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            $status = $executionTime < 1000 ? 'healthy' : 'warning';
            
            return [
                'status' => $status,
                'message' => $status === 'healthy' ? 'Performance OK' : 'Performance lenta detectada',
                'execution_time_ms' => $executionTime,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Erro de performance: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Calcula status geral
     */
    private function calculateOverallStatus(array $checks): string
    {
        $statuses = array_column($checks, 'status');
        
        if (in_array('unhealthy', $statuses)) {
            return 'unhealthy';
        } elseif (in_array('warning', $statuses)) {
            return 'warning';
        } else {
            return 'healthy';
        }
    }

    /**
     * API: Endpoint de health check
     */
    public function getHealthCheckResponse(): array
    {
        $health = $this->checkSystemHealth();
        
        $statusCode = match($health['status']) {
            'healthy' => 200,
            'warning' => 200,
            'unhealthy' => 503,
            default => 200,
        };
        
        return [
            'data' => $health,
            'status_code' => $statusCode,
        ];
    }
}
