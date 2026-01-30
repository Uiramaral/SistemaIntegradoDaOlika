<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DistanceCalculatorService
{
    protected ?array $lastError = null;

    protected function setLastError(string $code, ?string $message = null, array $meta = []): void
    {
        $this->lastError = [
            'code' => $code,
            'message' => $message,
            'meta' => $meta,
        ];
    }

    public function getLastError(): ?array
    {
        return $this->lastError;
    }

    /**
     * Calcula distância usando Google Maps Distance Matrix API
     * Fallback para cálculo aproximado se API não disponível
     */
    public function calculateDistanceByCep(string $cep1, string $cep2): ?float
    {
        try {
            $this->lastError = null;

            $cep1 = preg_replace('/\D/', '', $cep1);
            $cep2 = preg_replace('/\D/', '', $cep2);
            
            if (strlen($cep1) !== 8 || strlen($cep2) !== 8) {
                $this->setLastError('invalid_zip_format', 'CEP inválido');
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
                    $this->lastError = null;
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

                if ($this->lastError === null) {
                    $this->setLastError('distance_matrix_invalid', 'A API do Google Maps não retornou uma distância válida', [
                        'cep1' => $cep1,
                        'cep2' => $cep2,
                        'distance' => $distance,
                    ]);
                }
            } else {
                Log::error('GOOGLE_MAPS_API_KEY não configurada ou vazia', [
                    'cep1' => $cep1,
                    'cep2' => $cep2,
                    'api_key_received' => $apiKey !== null,
                    'api_key_empty' => $apiKey === null || trim($apiKey) === '',
                ]);
                $this->setLastError('missing_api_key', 'Chave de API do Google Maps ausente ou inválida');
            }

            // Fallback: cálculo aproximado baseado na diferença dos CEPs
            // AVISO: Este método é muito impreciso e não deve ser usado em produção
            Log::error('Usando cálculo aproximado de distância - IMPRECISO! Configure GOOGLE_MAPS_API_KEY', [
                'cep1' => $cep1,
                'cep2' => $cep2,
            ]);
            
            $approx = $this->calculateApproximateDistance($cep1, $cep2);

            if ($approx === null && $this->lastError === null) {
                $this->setLastError('distance_calculation_failed', 'Não foi possível calcular a distância com os dados fornecidos', [
                    'cep1' => $cep1,
                    'cep2' => $cep2,
                ]);
            }

            return $approx;
            
        } catch (\Exception $e) {
            Log::error('Erro ao calcular distância entre CEPs', [
                'cep1' => $cep1 ?? null,
                'cep2' => $cep2 ?? null,
                'error' => $e->getMessage(),
            ]);
            $this->setLastError('exception', $e->getMessage(), [
                'cep1' => $cep1 ?? null,
                'cep2' => $cep2 ?? null,
            ]);
            return null;
        }
    }

    public function calculateDistanceByAddressComponents(string $storeCep, array $destination): ?array
    {
        try {
            $this->lastError = null;

            $storeCepDigits = preg_replace('/\D/', '', $storeCep);
            if (strlen($storeCepDigits) !== 8) {
                $this->setLastError('store_zip_invalid', 'CEP da loja inválido');
                return null;
            }

            $apiKey = $this->getGoogleMapsApiKey();
            if (!$apiKey || trim($apiKey) === '') {
                $this->setLastError('missing_api_key', 'Chave de API do Google Maps ausente ou inválida');
                return null;
            }

            $origin = $this->getCoordinatesFromCep($storeCepDigits, $apiKey);
            if (!$origin) {
                $this->setLastError('store_geocode_failed', 'Não foi possível localizar o endereço da loja');
                return null;
            }

            $destinationCoords = $this->getCoordinatesFromAddress($destination, $apiKey);
            if (!$destinationCoords) {
                if ($this->lastError === null) {
                    $this->setLastError('address_geocode_failed', 'Não foi possível localizar o endereço informado');
                }
                return null;
            }

            $distance = $this->calculateDistanceBetweenCoordinates($origin, $destinationCoords, $apiKey);
            if ($distance === null) {
                if ($this->lastError === null) {
                    $this->setLastError('distance_matrix_invalid', 'A API do Google Maps não retornou uma distância válida');
                }
                return null;
            }

            return [
                'distance_km' => $distance,
                'resolved_zip' => $destinationCoords['postal_code'] ?? null,
                'destination' => $destinationCoords,
            ];
        } catch (\Exception $e) {
            $this->setLastError('address_distance_exception', $e->getMessage(), [
                'store_cep' => $storeCep ?? null,
                'destination' => $destination,
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
                $this->setLastError('geocode_failed', 'Não foi possível obter coordenadas para o CEP informado', [
                    'cep1' => $cep1,
                    'cep2' => $cep2,
                    'origin_found' => (bool)$origin,
                    'destination_found' => (bool)$destination,
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
                $this->setLastError('distance_matrix_http_error', 'A API do Google Maps retornou um erro', [
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
                $this->setLastError('distance_matrix_error', 'A API do Google Maps não conseguiu calcular a rota para o CEP informado', [
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
                $this->setLastError('distance_matrix_no_results', 'Não foi possível localizar uma rota para o CEP informado', [
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
                $this->setLastError('distance_out_of_range', 'A distância calculada excede o limite permitido', [
                    'distance_km' => $distanceKm,
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
            $this->setLastError('distance_matrix_exception', $e->getMessage(), [
                'cep1' => $cep1 ?? null,
                'cep2' => $cep2 ?? null,
            ]);
            return null;
        }
    }

    private function calculateDistanceBetweenCoordinates(array $origin, array $destination, string $apiKey): ?float
    {
        try {
            $url = 'https://maps.googleapis.com/maps/api/distancematrix/json';
            $response = Http::get($url, [
                'origins' => "{$origin['lat']},{$origin['lng']}",
                'destinations' => "{$destination['lat']},{$destination['lng']}",
                'key' => $apiKey,
                'units' => 'metric',
            ]);

            if (!$response->successful()) {
                $this->setLastError('distance_matrix_http_error', 'A API do Google Maps retornou um erro', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();
            if (($data['status'] ?? null) !== 'OK') {
                $this->setLastError('distance_matrix_error', 'A API do Google Maps não conseguiu calcular a rota', [
                    'status' => $data['status'] ?? null,
                ]);
                return null;
            }

            $element = $data['rows'][0]['elements'][0] ?? null;
            if (!$element || ($element['status'] ?? null) !== 'OK') {
                $this->setLastError('distance_matrix_no_results', 'Não foi possível localizar uma rota válida', [
                    'element_status' => $element['status'] ?? 'N/A',
                ]);
                return null;
            }

            $distanceMeters = $element['distance']['value'] ?? 0;
            $distanceKm = round($distanceMeters / 1000, 2);

            if ($distanceKm > 1000) {
                $this->setLastError('distance_out_of_range', 'A distância calculada excede o limite permitido', [
                    'distance_km' => $distanceKm,
                ]);
                return null;
            }

            return $distanceKm;
        } catch (\Exception $e) {
            $this->setLastError('distance_matrix_exception', $e->getMessage(), [
                'origin' => $origin,
                'destination' => $destination,
            ]);
            return null;
        }
    }

    private function getCoordinatesFromAddress(array $address, string $apiKey): ?array
    {
        try {
            $parts = [];
            $street = trim($address['street'] ?? '');
            $number = trim($address['number'] ?? '');
            $neighborhood = trim($address['neighborhood'] ?? '');
            $city = trim($address['city'] ?? '');
            $state = trim($address['state'] ?? '');
            $zip = trim($address['zip_code'] ?? '');

            if ($street !== '') {
                $parts[] = $street . ($number !== '' ? ", {$number}" : '');
            }
            if ($neighborhood !== '') {
                $parts[] = $neighborhood;
            }
            if ($city !== '') {
                $parts[] = $city;
            }
            if ($state !== '') {
                $parts[] = $state;
            }
            if ($zip !== '') {
                $parts[] = $zip;
            }

            $parts[] = 'Brasil';
            $addressString = implode(', ', $parts);

            $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => $addressString,
                'key' => $apiKey,
            ]);

            if (!$response->successful()) {
                $this->setLastError('address_geocode_http_error', 'Erro ao consultar endereço na API do Google', [
                    'status' => $response->status(),
                ]);
                return null;
            }

            $data = $response->json();
            if (($data['status'] ?? null) !== 'OK' || empty($data['results'])) {
                $this->setLastError('address_geocode_failed', 'Não encontramos coordenadas para o endereço informado', [
                    'status' => $data['status'] ?? null,
                ]);
                return null;
            }

            $result = $data['results'][0];
            $location = $result['geometry']['location'] ?? null;
            if (!$location) {
                $this->setLastError('address_geocode_invalid_location', 'A resposta da API não continha coordenadas válidas');
                return null;
            }

            $postalCode = null;
            if (!empty($result['address_components'])) {
                foreach ($result['address_components'] as $component) {
                    if (in_array('postal_code', $component['types'], true)) {
                        $postalCode = $component['long_name'] ?? null;
                        break;
                    }
                }
            }

            return [
                'lat' => $location['lat'],
                'lng' => $location['lng'],
                'postal_code' => $postalCode,
                'formatted_address' => $result['formatted_address'] ?? null,
            ];
        } catch (\Exception $e) {
            $this->setLastError('address_geocode_exception', $e->getMessage(), [
                'address' => $address,
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
                $this->setLastError('geocode_http_error', 'Erro ao consultar endereço na API do Google', [
                    'cep' => $cep,
                    'status' => $response->status(),
                ]);
                return null;
            }

            $data = $response->json();

            if ($data['status'] !== 'OK' || empty($data['results'])) {
                $this->setLastError('geocode_zero_results', 'Não encontramos uma localização para este CEP', [
                    'cep' => $cep,
                    'status' => $data['status'] ?? null,
                ]);
                return null;
            }

            $location = $data['results'][0]['geometry']['location'] ?? null;

            if (!$location) {
                $this->setLastError('geocode_invalid_location', 'A resposta da API de geocodificação não continha coordenadas válidas', [
                    'cep' => $cep,
                ]);
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
            $this->setLastError('geocode_exception', $e->getMessage(), [
                'cep' => $cep,
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
        $this->setLastError('approximation_unavailable', 'Cálculo aproximado desabilitado. Configure a API do Google Maps para calcular o frete com precisão.', [
            'cep1' => $cep1,
            'cep2' => $cep2,
        ]);
        return null;
    }

    /**
     * Obtém a chave da API do Google Maps
     * Prioridade: Master (APIs centralizadas) → payment_settings → settings → .env
     */
    private function getGoogleMapsApiKey(): ?string
    {
        try {
            $key = \App\Models\MasterSetting::get('google_maps_api_key', '');
            if ($key && trim((string) $key) !== '') {
                return trim((string) $key);
            }
        } catch (\Exception $e) {
            Log::debug('Erro ao buscar Google Maps API key em MasterSetting', ['error' => $e->getMessage()]);
        }

        try {
            $key = DB::table('payment_settings')->where('key', 'google_maps_api_key')->value('value');
            if ($key && trim($key) !== '') {
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
