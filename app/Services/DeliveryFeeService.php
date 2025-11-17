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

            $globalFreeShippingMin = $this->getFreeShippingMinTotal();

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
                $distanceError = method_exists($distanceCalculator, 'getLastError')
                    ? $distanceCalculator->getLastError()
                    : null;
                $friendlyError = $this->translateDistanceError($distanceError);
                
                return [
                    'success' => false,
                    'delivery_fee' => 0.00,
                    'distance_km' => null,
                    'free' => false,
                    'custom' => false,
                    'message' => $friendlyError['message'],
                    'error_code' => $friendlyError['code'],
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

            if (!$isFreeFromConfig && $globalFreeShippingMin > 0 && $subtotal >= $globalFreeShippingMin) {
                $isFreeFromConfig = true;
                Log::info('DeliveryFeeService: Frete grátis aplicado por configuração global', [
                    'subtotal' => $subtotal,
                    'threshold' => $globalFreeShippingMin,
                ]);
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
                'global_free_shipping_min' => $globalFreeShippingMin,
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

    public function calculateDeliveryFeeByAddress(array $address, float $subtotal, ?string $customerPhone = null, ?string $customerEmail = null): array
    {
        try {
            $normalizedPhone = $customerPhone ? preg_replace('/\D/', '', $customerPhone) : null;
            $normalizedEmail = $customerEmail ? trim($customerEmail) : null;

            $globalFreeShippingMin = $this->getFreeShippingMinTotal();

            if ($normalizedPhone || $normalizedEmail) {
                $customer = $this->findCustomerByContact($normalizedPhone, $normalizedEmail);
                if ($customer && $customer->custom_delivery_fee !== null) {
                    Log::info('DeliveryFeeService: Usando taxa personalizada do cliente (endereço manual)', [
                        'customer_id' => $customer->id,
                        'custom_fee' => $customer->custom_delivery_fee,
                    ]);

                    return [
                        'success' => true,
                        'delivery_fee' => round((float)$customer->custom_delivery_fee, 2),
                        'base_delivery_fee' => round((float)$customer->custom_delivery_fee, 2),
                        'discount_percent' => 0,
                        'discount_amount' => 0,
                        'distance_km' => null,
                        'free' => false,
                        'custom' => true,
                        'resolved_zip_code' => $address['zip_code'] ?? null,
                        'message' => null,
                    ];
                }
            }

            $storeCep = $this->getStoreZipCode();
            if (!$storeCep) {
                Log::warning('DeliveryFeeService: CEP da loja não configurado (endereço manual)');
                return [
                    'success' => false,
                    'delivery_fee' => 0.00,
                    'base_delivery_fee' => 0.00,
                    'distance_km' => null,
                    'free' => false,
                    'custom' => false,
                    'message' => 'CEP da loja não configurado',
                    'error_code' => 'store_zip_not_configured',
                ];
            }

            $distanceCalculator = new \App\Services\DistanceCalculatorService();
            $distanceResult = $distanceCalculator->calculateDistanceByAddressComponents($storeCep, $address);

            if ($distanceResult === null || !isset($distanceResult['distance_km'])) {
                Log::error('DeliveryFeeService: Não foi possível calcular distância via endereço', [
                    'store_cep' => $storeCep,
                    'address' => $address,
                ]);

                $distanceError = method_exists($distanceCalculator, 'getLastError')
                    ? $distanceCalculator->getLastError()
                    : null;
                $friendlyError = $this->translateDistanceError($distanceError);

                return [
                    'success' => false,
                    'delivery_fee' => 0.00,
                    'base_delivery_fee' => 0.00,
                    'distance_km' => null,
                    'free' => false,
                    'custom' => false,
                    'message' => $friendlyError['message'],
                    'error_code' => $friendlyError['code'],
                ];
            }

            $distance = (float)$distanceResult['distance_km'];
            $resolvedZipRaw = $distanceResult['resolved_zip'] ?? ($address['zip_code'] ?? null);
            $resolvedZip = $this->normalizeZipToDistrict($resolvedZipRaw) ?? $this->normalizeZipToDistrict($address['zip_code'] ?? null);
            $resolvedZipDigits = $resolvedZip ? preg_replace('/\D/', '', $resolvedZip) : null;

            if ($resolvedZipDigits && strlen($resolvedZipDigits) === 8) {
                $resolvedZip = $resolvedZipDigits;
            } elseif (!empty($address['zip_code'])) {
                $addressZipDigits = preg_replace('/\D/', '', $address['zip_code']);
                if (strlen($addressZipDigits) === 8) {
                    $resolvedZipDigits = substr($addressZipDigits, 0, 5) . '000';
                    $resolvedZip = $resolvedZipDigits;
                }
            }

            if ($distance < 0) {
                Log::error('DeliveryFeeService: Distância inválida (negativa) via endereço', [
                    'store_cep' => $storeCep,
                    'address' => $address,
                    'distance' => $distance,
                ]);

                return [
                    'success' => false,
                    'delivery_fee' => 0.00,
                    'base_delivery_fee' => 0.00,
                    'distance_km' => null,
                    'free' => false,
                    'custom' => false,
                    'message' => 'Erro ao calcular distância.',
                ];
            }

            $baseDeliveryFee = 0.0;
            $isFreeFromConfig = false;

            $dynamicCount = DeliveryDistancePricing::active()->count();
            if ($dynamicCount > 0) {
                $calc = DeliveryDistancePricing::calculateFeeFor((float)$distance, (float)$subtotal);
                $baseDeliveryFee = (float)$calc['fee'];
                $isFreeFromConfig = (bool)$calc['free'];
                Log::info('DeliveryFeeService: Cálculo via tabela dinâmica (endereço manual)', [
                    'distance_km' => $distance,
                    'rounded_km' => $calc['km'],
                    'subtotal' => $subtotal,
                    'base_fee' => $baseDeliveryFee,
                    'free_from_config' => $isFreeFromConfig,
                ]);
            } else {
                $deliveryFeeConfig = DeliveryFee::active()->first();
                if ($deliveryFeeConfig) {
                    $freeShippingMin = $this->getFreeShippingMinTotal();
                    if ($freeShippingMin > 0 && $subtotal >= $freeShippingMin) {
                        $isFreeFromConfig = true;
                        $baseDeliveryFee = 0.0;
                    } else {
                        $baseDeliveryFee = (float)$deliveryFeeConfig->calculateFee((float)$distance, (float)$subtotal);
                    }
                }
            }

            if (!$isFreeFromConfig && $globalFreeShippingMin > 0 && $subtotal >= $globalFreeShippingMin) {
                $isFreeFromConfig = true;
                Log::info('DeliveryFeeService: Frete grátis aplicado por configuração global (endereço manual)', [
                    'subtotal' => $subtotal,
                    'threshold' => $globalFreeShippingMin,
                ]);
            }

            $discountPercent = 0;
            $discountAmount = 0.0;
            $finalDeliveryFee = $baseDeliveryFee;

            if (!$isFreeFromConfig && $baseDeliveryFee > 0) {
                if ($subtotal >= 250.0) {
                    $discountPercent = 100;
                    $discountAmount = $baseDeliveryFee;
                    $finalDeliveryFee = 0.0;
                } elseif ($subtotal >= 150.0) {
                    $discountPercent = 30;
                    $discountAmount = round($baseDeliveryFee * 0.30, 2);
                    $finalDeliveryFee = round($baseDeliveryFee - $discountAmount, 2);
                } elseif ($subtotal >= 100.0) {
                    $discountPercent = 10;
                    $discountAmount = round($baseDeliveryFee * 0.10, 2);
                    $finalDeliveryFee = round($baseDeliveryFee - $discountAmount, 2);
                }
            }

            if ($discountPercent > 0) {
                Log::info('DeliveryFeeService: Desconto progressivo aplicado (endereço manual)', [
                    'subtotal' => $subtotal,
                    'base_fee' => $baseDeliveryFee,
                    'discount_percent' => $discountPercent,
                    'discount_amount' => $discountAmount,
                    'final_fee' => $finalDeliveryFee,
                ]);
            }

            if ($isFreeFromConfig) {
                $finalDeliveryFee = 0.0;
                $discountPercent = 100;
                $discountAmount = $baseDeliveryFee;
            }

            return [
                'success' => true,
                'delivery_fee' => round($finalDeliveryFee, 2),
                'base_delivery_fee' => round($baseDeliveryFee, 2),
                'discount_percent' => $discountPercent,
                'discount_amount' => round($discountAmount, 2),
                'distance_km' => (int)round($distance, 0),
                'free' => ($finalDeliveryFee <= 0),
                'custom' => false,
                'resolved_zip_code' => $resolvedZip,
                'message' => null,
            ];
        } catch (\Exception $e) {
            Log::error('DeliveryFeeService: Erro ao calcular frete (endereço manual)', [
                'address' => $address,
                'subtotal' => $subtotal,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'delivery_fee' => 0.00,
                'base_delivery_fee' => 0.00,
                'distance_km' => null,
                'free' => false,
                'custom' => false,
                'message' => 'Erro ao calcular frete.',
                'error_code' => 'exception',
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

    private function translateDistanceError(?array $error): array
    {
        $code = $error['code'] ?? null;

        $message = match ($code) {
            'store_zip_not_configured' => 'CEP da loja não configurado. Entre em contato com o suporte.',
            'store_zip_invalid' => 'Configuração de CEP da loja inválida. Entre em contato com o suporte.',
            'store_geocode_failed' => 'Não foi possível localizar o endereço da loja. Entre em contato com o suporte.',
            'invalid_zip_format' => 'CEP inválido. Confira e tente novamente.',
            'missing_api_key' => 'Não foi possível calcular o frete automaticamente. Entre em contato com o suporte para concluir o pedido.',
            'geocode_failed', 'geocode_zero_results', 'geocode_invalid_location', 'geocode_http_error', 'geocode_exception' => 'Não conseguimos localizar o endereço para o CEP informado. Por favor, digite o endereço completo.',
            'address_geocode_http_error', 'address_geocode_failed', 'address_geocode_invalid_location', 'address_geocode_exception' => 'Não conseguimos localizar o endereço informado. Verifique os dados e tente novamente.',
            'distance_matrix_no_results', 'distance_matrix_error', 'distance_matrix_http_error', 'distance_matrix_invalid', 'distance_out_of_range', 'distance_matrix_exception' => 'Não conseguimos calcular a rota para o CEP informado. Digite o endereço completo ou revise os dados.',
            'address_distance_exception' => 'Não foi possível calcular a rota com o endereço informado. Revise os dados.',
            'approximation_unavailable', 'distance_calculation_failed' => 'Não foi possível calcular o frete com os dados informados. Preencha o endereço completo para prosseguir.',
            default => 'Não foi possível calcular o frete para o CEP informado. Informe o endereço completo ou tente novamente em instantes.',
        };

        return [
            'code' => $code,
            'message' => $message,
        ];
    }

    private function normalizeZipToDistrict(?string $zip): ?string
    {
        if (!$zip) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $zip);
        if (strlen($digits) >= 8) {
            return substr($digits, 0, 5) . '000';
        }

        if (strlen($digits) >= 5) {
            return substr($digits, 0, 5) . '000';
        }

        return null;
    }
}
