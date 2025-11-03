<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\DeliveryDistancePricing;
use App\Models\DeliveryFee;
use App\Models\Customer;

class DeliveryFeeService
{
    /**
     * Buscar CEP da loja (store_zip_code)
     * Centralizado para evitar duplicação
     */
    public function getStoreZipCode(): ?string
    {
        // Tentar buscar de payment_settings
        $cep = DB::table('payment_settings')->where('key', 'store_zip_code')->value('value');
        
        if ($cep) {
            return preg_replace('/\D/', '', $cep);
        }

        // Tentar buscar de settings (se tiver estrutura chave-valor)
        if (DB::getSchemaBuilder()->hasTable('settings')) {
            $keyCol = collect(['name', 'key', 'config_key'])->first(function($c) {
                return DB::getSchemaBuilder()->hasColumn('settings', $c);
            });
            
            if ($keyCol) {
                $valCol = collect(['value', 'val', 'config_value'])->first(function($c) {
                    return DB::getSchemaBuilder()->hasColumn('settings', $c);
                });
                
                if ($valCol) {
                    $cep = DB::table('settings')->where($keyCol, 'store_zip_code')->value($valCol);
                    if ($cep) {
                        return preg_replace('/\D/', '', $cep);
                    }
                }
            }

            // Tentar buscar de settings como coluna direta (business_cep)
            if (DB::getSchemaBuilder()->hasColumn('settings', 'business_cep')) {
                $cep = DB::table('settings')->value('business_cep');
                if ($cep) {
                    return preg_replace('/\D/', '', $cep);
                }
            }

            // Tentar buscar de settings como coluna store_zip_code
            if (DB::getSchemaBuilder()->hasColumn('settings', 'store_zip_code')) {
                $cep = DB::table('settings')->value('store_zip_code');
                if ($cep) {
                    return preg_replace('/\D/', '', $cep);
                }
            }
        }

        // Fallback .env
        $envCep = env('STORE_ZIP_CODE');
        if ($envCep) {
            return preg_replace('/\D/', '', $envCep);
        }

        return null;
    }

    /**
     * Buscar cliente por telefone ou email
     */
    private function findCustomerByContact(?string $phone, ?string $email): ?Customer
    {
        if (!$phone && !$email) {
            return null;
        }

        try {
            $query = Customer::query();
            
            if ($email) {
                $query->where('email', $email);
            }
            
            if ($phone && strlen($phone) >= 10) {
                if ($email) {
                    $query->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(phone,'(',''),')',''),'-',''),' ','') = ?", [$phone]);
                } else {
                    $query->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(phone,'(',''),')',''),'-',''),' ','') = ?", [$phone]);
                }
            }

            return $query->first();
        } catch (\Exception $e) {
            Log::debug('DeliveryFeeService: Erro ao buscar cliente', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Calcular frete completo - método centralizado
     * 
     * @param string $destinationZipcode CEP de destino (apenas números)
     * @param float $subtotal Subtotal do carrinho
     * @param string|null $customerPhone Telefone do cliente (opcional)
     * @param string|null $customerEmail Email do cliente (opcional)
     * @return array ['success' => bool, 'delivery_fee' => float, 'distance_km' => int|null, 'free' => bool, 'custom' => bool, 'message' => string|null]
     */
    public function calculateDeliveryFee(
        string $destinationZipcode,
        float $subtotal = 0.0,
        ?string $customerPhone = null,
        ?string $customerEmail = null
    ): array {
        try {
            // Validar CEP
            $zipcode = preg_replace('/\D/', '', $destinationZipcode);
            if (strlen($zipcode) !== 8) {
                return [
                    'success' => false,
                    'delivery_fee' => 0.00,
                    'distance_km' => null,
                    'free' => false,
                    'custom' => false,
                    'message' => 'CEP inválido',
                ];
            }

            // Normalizar contato do cliente
            $normalizedPhone = $customerPhone ? preg_replace('/\D/', '', $customerPhone) : null;
            $normalizedEmail = $customerEmail ? trim($customerEmail) : null;

            // 1. Prioridade: taxa personalizada do cliente
            if ($normalizedPhone || $normalizedEmail) {
                $customer = $this->findCustomerByContact($normalizedPhone, $normalizedEmail);
                
                if ($customer && $customer->custom_delivery_fee !== null) {
                    Log::info('DeliveryFeeService: Usando taxa personalizada do cliente', [
                        'customer_id' => $customer->id,
                        'custom_fee' => $customer->custom_delivery_fee,
                    ]);
                    
                    return [
                        'success' => true,
                        'delivery_fee' => round((float)$customer->custom_delivery_fee, 2),
                        'distance_km' => null,
                        'free' => false,
                        'custom' => true,
                        'message' => null,
                    ];
                }
            }

            // 2. Buscar CEP da loja
            $storeCep = $this->getStoreZipCode();
            
            if (!$storeCep) {
                Log::warning('DeliveryFeeService: CEP da loja não configurado');
                return [
                    'success' => false,
                    'delivery_fee' => 0.00,
                    'distance_km' => null,
                    'free' => false,
                    'custom' => false,
                    'message' => 'CEP da loja não configurado',
                ];
            }

            // 3. Calcular distância
            $distanceCalculator = new \App\Services\DistanceCalculatorService();
            $distance = $distanceCalculator->calculateDistanceByCep($storeCep, $zipcode);

            // Distância null é erro, mas 0 é válido (mesmo CEP = frete grátis)
            if ($distance === null) {
                Log::error('DeliveryFeeService: Não foi possível calcular distância', [
                    'store_cep' => $storeCep,
                    'destination_zipcode' => $zipcode,
                ]);
                
                return [
                    'success' => false,
                    'delivery_fee' => 0.00,
                    'distance_km' => null,
                    'free' => false,
                    'custom' => false,
                    'message' => 'Não foi possível calcular a distância. Verifique se a chave GOOGLE_MAPS_API_KEY está configurada.',
                ];
            }
            
            // Distância 0 significa mesmo local (frete grátis)
            if ($distance < 0) {
                Log::error('DeliveryFeeService: Distância inválida (negativa)', [
                    'store_cep' => $storeCep,
                    'destination_zipcode' => $zipcode,
                    'distance' => $distance,
                ]);
                
                return [
                    'success' => false,
                    'delivery_fee' => 0.00,
                    'distance_km' => null,
                    'free' => false,
                    'custom' => false,
                    'message' => 'Erro ao calcular distância.',
                ];
            }

            // 4. Calcular frete baseado na distância
            $baseDeliveryFee = 0.0;
            $isFreeFromConfig = false;
            
            // 4.1. Tabela dinâmica por distância (prioridade)
            $dynamicCount = DeliveryDistancePricing::active()->count();
            if ($dynamicCount > 0) {
                $calc = DeliveryDistancePricing::calculateFeeFor((float)$distance, (float)$subtotal);
                $baseDeliveryFee = (float)$calc['fee'];
                $isFreeFromConfig = (bool)$calc['free'];
                
                Log::info('DeliveryFeeService: Cálculo via tabela dinâmica', [
                    'distance_km' => $distance,
                    'rounded_km' => $calc['km'],
                    'subtotal' => $subtotal,
                    'base_fee' => $baseDeliveryFee,
                    'free_from_config' => $isFreeFromConfig,
                ]);
            } else {
                // 4.2. Fallback: configuração antiga DeliveryFee
                $deliveryFeeConfig = DeliveryFee::active()->first();
                if ($deliveryFeeConfig) {
                    // Verificar frete grátis global (antigo - será substituído pelo desconto progressivo)
                    $freeShippingMin = $this->getFreeShippingMinTotal();
                    if ($freeShippingMin > 0 && $subtotal >= $freeShippingMin) {
                        $isFreeFromConfig = true;
                        $baseDeliveryFee = 0.0;
                    } else {
                        $baseDeliveryFee = (float)$deliveryFeeConfig->calculateFee((float)$distance, (float)$subtotal);
                    }
                }
            }
            
            // 5. Aplicar desconto progressivo baseado no valor do carrinho
            // (somente se não for frete grátis já configurado)
            $discountPercent = 0;
            $discountAmount = 0.0;
            $finalDeliveryFee = $baseDeliveryFee;
            
            if (!$isFreeFromConfig && $baseDeliveryFee > 0) {
                // Aplicar desconto progressivo baseado no subtotal
                if ($subtotal >= 250.0) {
                    // R$ 250 ou mais: frete grátis (100% de desconto)
                    $discountPercent = 100;
                    $discountAmount = $baseDeliveryFee;
                    $finalDeliveryFee = 0.0;
                } elseif ($subtotal >= 150.0) {
                    // R$ 150 ou mais: 30% de desconto no frete
                    $discountPercent = 30;
                    $discountAmount = round($baseDeliveryFee * 0.30, 2);
                    $finalDeliveryFee = round($baseDeliveryFee - $discountAmount, 2);
                } elseif ($subtotal >= 100.0) {
                    // R$ 100 ou mais: 10% de desconto no frete
                    $discountPercent = 10;
                    $discountAmount = round($baseDeliveryFee * 0.10, 2);
                    $finalDeliveryFee = round($baseDeliveryFee - $discountAmount, 2);
                }
                // Abaixo de R$ 100: frete comum (sem desconto)
            } elseif ($isFreeFromConfig) {
                // Se já é frete grátis da configuração, manter
                $finalDeliveryFee = 0.0;
                $discountPercent = 100;
                $discountAmount = $baseDeliveryFee;
            }
            
            Log::info('DeliveryFeeService: Desconto progressivo aplicado', [
                'subtotal' => $subtotal,
                'base_delivery_fee' => $baseDeliveryFee,
                'discount_percent' => $discountPercent,
                'discount_amount' => $discountAmount,
                'final_delivery_fee' => $finalDeliveryFee,
                'is_free_from_config' => $isFreeFromConfig,
            ]);
            
            return [
                'success' => true,
                'delivery_fee' => round($finalDeliveryFee, 2),
                'base_delivery_fee' => round($baseDeliveryFee, 2),
                'discount_percent' => $discountPercent,
                'discount_amount' => round($discountAmount, 2),
                'distance_km' => (int)round($distance, 0),
                'free' => ($finalDeliveryFee <= 0),
                'custom' => false,
                'message' => null,
            ];
        } catch (\Exception $e) {
            Log::error('DeliveryFeeService: Erro ao calcular frete', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'zipcode' => $destinationZipcode ?? null,
            ]);
            
            return [
                'success' => false,
                'delivery_fee' => 0.00,
                'distance_km' => null,
                'free' => false,
                'custom' => false,
                'message' => 'Erro ao calcular frete: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Buscar valor mínimo para frete grátis
     */
    private function getFreeShippingMinTotal(): float
    {
        // Tentar settings flexível
        if (DB::getSchemaBuilder()->hasTable('settings')) {
            $keyCol = collect(['key','name','config_key','setting_key','option','option_name'])
                ->first(fn($c)=>DB::getSchemaBuilder()->hasColumn('settings',$c));
            $valCol = collect(['value','val','config_value','content','data','option_value'])
                ->first(fn($c)=>DB::getSchemaBuilder()->hasColumn('settings',$c));
            if ($keyCol && $valCol) {
                $val = DB::table('settings')->where($keyCol, 'free_shipping_min_total')->value($valCol);
                if ($val !== null) {
                    return (float)str_replace(',', '.', (string)$val);
                }
            }
        }
        
        // Fallback: payment_settings
        if (DB::getSchemaBuilder()->hasTable('payment_settings')) {
            $val = DB::table('payment_settings')->where('key', 'free_shipping_min_total')->value('value');
            if ($val !== null) {
                return (float)str_replace(',', '.', (string)$val);
            }
        }
        
        // Fallback .env
        $envVal = env('FREE_SHIPPING_MIN_TOTAL');
        if ($envVal !== null) {
            return (float)str_replace(',', '.', (string)$envVal);
        }

        return 0.0;
    }
}
