<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DistanceCalculatorService
{
    /**
     * Calcula distância usando Google Maps Distance Matrix API
     * Fallback para cálculo aproximado se API não disponível
     */
    public function calculateDistanceByCep(string $cep1, string $cep2): ?float
    {
        try {
            $cep1 = preg_replace('/\D/', '', $cep1);
            $cep2 = preg_replace('/\D/', '', $cep2);
            
            if (strlen($cep1) !== 8 || strlen($cep2) !== 8) {
                return null;
            }

            // Tentar usar Google Maps API primeiro
            $apiKey = $this->getGoogleMapsApiKey();
            
            if ($apiKey && trim($apiKey) !== '') {
                Log::info('Tentando calcular distância com Google Maps API', [
                    'cep1' => $cep1,
                    'cep2' => $cep2,
                    'api_key_length' => strlen($apiKey),
                    'api_key_prefix' => substr($apiKey, 0, 10) . '...',
                ]);
                
                $distance = $this->calculateWithGoogleMaps($cep1, $cep2, $apiKey);
                // Aceitar distância >= 0 (0 significa mesmo local, o que é válido)
                if ($distance !== null && $distance >= 0) {
                    Log::info('Distância calculada com sucesso via Google Maps', [
                        'distance_km' => $distance,
                        'cep1' => $cep1,
                        'cep2' => $cep2,
                        'same_location' => $distance == 0,
                    ]);
                    return $distance;
                }
                
                Log::warning('Google Maps API não retornou distância válida', [
                    'cep1' => $cep1,
                    'cep2' => $cep2,
                    'distance_returned' => $distance,
                ]);
            } else {
                Log::error('GOOGLE_MAPS_API_KEY não configurada ou vazia', [
                    'cep1' => $cep1,
                    'cep2' => $cep2,
                    'api_key_received' => $apiKey !== null,
                    'api_key_empty' => $apiKey === null || trim($apiKey) === '',
                ]);
            }

            // Fallback: cálculo aproximado baseado na diferença dos CEPs
            // AVISO: Este método é muito impreciso e não deve ser usado em produção
            Log::error('Usando cálculo aproximado de distância - IMPRECISO! Configure GOOGLE_MAPS_API_KEY', [
                'cep1' => $cep1,
                'cep2' => $cep2,
            ]);
            
            return $this->calculateApproximateDistance($cep1, $cep2);
            
        } catch (\Exception $e) {
            Log::error('Erro ao calcular distância entre CEPs', [
                'cep1' => $cep1 ?? null,
                'cep2' => $cep2 ?? null,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Calcula distância usando Google Maps Distance Matrix API
     */
    private function calculateWithGoogleMaps(string $cep1, string $cep2, string $apiKey): ?float
    {
        try {
            // Primeiro, obter coordenadas dos CEPs usando Geocoding API
            $origin = $this->getCoordinatesFromCep($cep1, $apiKey);
            $destination = $this->getCoordinatesFromCep($cep2, $apiKey);

            if (!$origin || !$destination) {
                Log::warning('Não foi possível obter coordenadas dos CEPs', [
                    'cep1' => $cep1,
                    'cep2' => $cep2,
                ]);
                return null;
            }

            // Chamar Distance Matrix API
            $url = 'https://maps.googleapis.com/maps/api/distancematrix/json';
            $response = Http::get($url, [
                'origins' => "{$origin['lat']},{$origin['lng']}",
                'destinations' => "{$destination['lat']},{$destination['lng']}",
                'key' => $apiKey,
                'units' => 'metric',
            ]);

            if (!$response->successful()) {
                Log::warning('Erro na resposta da Google Maps API', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();

            if ($data['status'] !== 'OK') {
                Log::warning('Google Maps API retornou status inválido', [
                    'status' => $data['status'],
                    'cep1' => $cep1,
                    'cep2' => $cep2,
                ]);
                return null;
            }

            $element = $data['rows'][0]['elements'][0] ?? null;

            if (!$element || $element['status'] !== 'OK') {
                Log::warning('Elemento da matriz de distância não OK', [
                    'element_status' => $element['status'] ?? 'N/A',
                ]);
                return null;
            }

            // Converter metros para quilômetros
            $distanceMeters = $element['distance']['value'] ?? 0;
            $distanceKm = round($distanceMeters / 1000, 2);

            Log::info('Distância calculada via Google Maps', [
                'cep1' => $cep1,
                'cep2' => $cep2,
                'distance_meters' => $distanceMeters,
                'distance_km' => $distanceKm,
                'element_status' => $element['status'] ?? 'N/A',
            ]);

            // Distância 0 é válida (mesmo CEP = mesma localização)
            // Distância até 1000km é considerada válida
            if ($distanceKm > 1000) {
                Log::warning('Distância calculada parece inválida (muito alta)', [
                    'distance_km' => $distanceKm,
                    'cep1' => $cep1,
                    'cep2' => $cep2,
                ]);
                return null; // Retornar null apenas se for claramente inválido (> 1000km)
            }

            // Se distância for 0, é porque são o mesmo CEP (localização idêntica)
            // Isso é válido e deve retornar 0 km
            if ($distanceKm == 0) {
                Log::info('Distância 0km - mesmo CEP de origem e destino (frete grátis)', [
                    'cep1' => $cep1,
                    'cep2' => $cep2,
                ]);
            }

            return $distanceKm;

        } catch (\Exception $e) {
            Log::error('Erro ao calcular distância com Google Maps', [
                'cep1' => $cep1 ?? null,
                'cep2' => $cep2 ?? null,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Obtém coordenadas de um CEP usando Google Maps Geocoding API
     */
    private function getCoordinatesFromCep(string $cep, string $apiKey): ?array
    {
        try {
            $url = 'https://maps.googleapis.com/maps/api/geocode/json';
            $response = Http::get($url, [
                'address' => $cep . ', Brasil',
                'key' => $apiKey,
            ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();

            if ($data['status'] !== 'OK' || empty($data['results'])) {
                return null;
            }

            $location = $data['results'][0]['geometry']['location'] ?? null;

            if (!$location) {
                return null;
            }

            return [
                'lat' => $location['lat'],
                'lng' => $location['lng'],
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao obter coordenadas do CEP', [
                'cep' => $cep,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Calcula distância aproximada entre dois CEPs (fallback)
     * Este método é apenas um fallback básico - NÃO É PRECISO
     * IMPORTANTE: Use Google Maps API sempre que possível
     * 
     * Este método NÃO deve ser usado em produção sem Google Maps API configurada
     */
    private function calculateApproximateDistance(string $cep1, string $cep2): ?float
    {
        // IMPORTANTE: Este método é muito impreciso
        // A diferença numérica entre CEPs não reflete distância real
        // Por exemplo: CEPs próximos podem ter diferença grande numericamente
        
        // Retornar null para forçar uso da Google Maps API
        // Se a API não estiver configurada, o sistema deve alertar o administrador
        Log::error('Cálculo aproximado de distância não é confiável. Configure GOOGLE_MAPS_API_KEY', [
            'cep1' => $cep1,
            'cep2' => $cep2,
        ]);
        
        // Retornar null ao invés de um valor falso
        // Isso forçará o sistema a retornar erro ou usar uma configuração padrão
        return null;
    }

    /**
     * Obtém a chave da API do Google Maps
     */
    private function getGoogleMapsApiKey(): ?string
    {
        // Tentar buscar de payment_settings
        try {
            $key = DB::table('payment_settings')->where('key', 'google_maps_api_key')->value('value');
            if ($key && trim($key) !== '') {
                Log::info('Google Maps API Key encontrada em payment_settings');
                return trim($key);
            }
        } catch (\Exception $e) {
            Log::debug('Erro ao buscar API key em payment_settings', ['error' => $e->getMessage()]);
        }

        // Tentar buscar de settings (se tiver estrutura chave-valor)
        try {
            if (DB::getSchemaBuilder()->hasTable('settings')) {
                // Tentar diferentes nomes de colunas (name é mais comum que key)
                $keyCol = collect(['name', 'key', 'config_key'])->first(function($c) {
                    return DB::getSchemaBuilder()->hasColumn('settings', $c);
                });
                
                if ($keyCol) {
                    $valCol = collect(['value', 'val', 'config_value'])->first(function($c) {
                        return DB::getSchemaBuilder()->hasColumn('settings', $c);
                    });
                    
                    if ($valCol) {
                        $key = DB::table('settings')->where($keyCol, 'google_maps_api_key')->value($valCol);
                        if ($key && trim($key) !== '') {
                            Log::info('Google Maps API Key encontrada em settings');
                            return trim($key);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::debug('Erro ao buscar API key em settings', ['error' => $e->getMessage()]);
        }

        // Fallback para .env - ler diretamente do arquivo se necessário
        $envKey = env('GOOGLE_MAPS_API_KEY');
        
        // Se env() não retornar, tentar ler diretamente do arquivo .env
        if (!$envKey || trim($envKey) === '') {
            try {
                $envPath = base_path('.env');
                if (file_exists($envPath)) {
                    $envContent = file_get_contents($envPath);
                    if (preg_match('/^GOOGLE_MAPS_API_KEY\s*=\s*([^\s]+)/m', $envContent, $matches)) {
                        $envKey = trim($matches[1]);
                        Log::info('Google Maps API Key lida diretamente do arquivo .env');
                    }
                }
            } catch (\Exception $e) {
                Log::debug('Erro ao ler .env diretamente', ['error' => $e->getMessage()]);
            }
        }
        
        if ($envKey && trim($envKey) !== '') {
            Log::info('Google Maps API Key encontrada em .env', [
                'key_length' => strlen(trim($envKey)),
            ]);
            return trim($envKey);
        }

        // Debug: listar todas as variáveis de ambiente relacionadas
        $allEnvKeys = [];
        foreach ($_ENV as $key => $value) {
            if (stripos($key, 'GOOGLE') !== false || stripos($key, 'MAPS') !== false) {
                $allEnvKeys[$key] = substr($value, 0, 10) . '...';
            }
        }

        Log::warning('Google Maps API Key não encontrada em nenhum local', [
            'checked_payment_settings' => true,
            'checked_settings' => true,
            'checked_env' => true,
            'env_key_exists' => env('GOOGLE_MAPS_API_KEY') !== null,
            'env_key_length' => $envKey ? strlen($envKey) : 0,
            'related_env_keys' => array_keys($allEnvKeys),
            'note' => 'Execute "php artisan config:clear" se alterou o .env recentemente',
        ]);

        return null;
    }
}
