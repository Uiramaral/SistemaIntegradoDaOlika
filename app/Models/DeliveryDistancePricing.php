<?php

namespace App\Models;

use App\Models\Traits\BelongsToClient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryDistancePricing extends Model
{
    use HasFactory, BelongsToClient;

    protected $table = 'delivery_distance_pricing';

    protected $fillable = [
        'client_id',
        'min_km',
        'max_km',
        'fee',
        'min_amount_free',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'min_km' => 'decimal:2',
        'max_km' => 'decimal:2',
        'fee' => 'decimal:2',
        'min_amount_free' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($q)
    {
        return $q->where(function($query) {
            $query->where('is_active', 1)
                  ->orWhere('is_active', true);
        });
    }

    /**
     * Arredonda distância para o km mais próximo (0,5 para cima)
     */
    public static function roundKm(float $distanceKm): int
    {
        return (int) floor($distanceKm + 0.5);
    }

    /**
     * Retorna a faixa aplicável para um dado km inteiro
     * Prioriza faixas que contêm exatamente o km, depois a mais próxima maior
     */
    public static function findForKm(int $km): ?self
    {
        // 1. Tentar encontrar faixa que contém exatamente o km (min_km <= km <= max_km)
        $exact = self::active()
            ->where('min_km', '<=', $km)
            ->where('max_km', '>=', $km)
            ->orderBy('sort_order')
            ->orderBy('min_km')
            ->first();
        
        if ($exact) {
            return $exact;
        }
        
        // 2. Se não encontrou, buscar faixa onde min_km >= km (faixa mais próxima acima)
        $next = self::active()
            ->where('min_km', '>=', $km)
            ->orderBy('min_km')
            ->orderBy('sort_order')
            ->first();
        
        if ($next) {
            return $next;
        }
        
        // 3. Se não encontrou acima, buscar a faixa máxima disponível (última faixa)
        $max = self::active()
            ->orderByDesc('max_km')
            ->orderByDesc('sort_order')
            ->first();
        
        if ($max) {
            return $max;
        }
        
        // 4. Fallback: buscar qualquer faixa ativa (última tentativa)
        return self::active()
            ->orderByDesc('min_km')
            ->orderByDesc('sort_order')
            ->first();
    }

    /**
     * Calcula a taxa de entrega com base nas faixas dinâmicas
     */
    public static function calculateFeeFor(float $rawDistanceKm, float $subtotal): array
    {
        $km = self::roundKm($rawDistanceKm);

        // Buscar faixa aplicável
        $range = self::findForKm($km);
        
        if (!$range) {
            \Log::warning('DeliveryDistancePricing: Nenhuma faixa ativa encontrada no banco', [
                'km' => $km,
                'raw_distance' => $rawDistanceKm,
                'active_count' => self::active()->count(),
            ]);
            return [
                'fee' => 0.0,
                'km' => $km,
                'free' => false,
            ];
        }

        // Verificar se há frete grátis por valor mínimo
        if (!is_null($range->min_amount_free) && $subtotal >= (float)$range->min_amount_free) {
            \Log::info('DeliveryDistancePricing: Frete grátis aplicado por valor mínimo', [
                'km' => $km,
                'subtotal' => $subtotal,
                'min_amount_free' => $range->min_amount_free,
                'range_id' => $range->id,
            ]);
            return [ 'fee' => 0.0, 'km' => $km, 'free' => true ];
        }

        // Calcular taxa
        $fee = (float)$range->fee;
        
        // Se for "Taxa por km", calcular baseado na distância
        // Detecta taxa por km quando: diferença entre max_km e min_km >= 50
        // OU quando o fee é menor ou igual a 2.00 E a faixa é grande (indicando taxa por km)
        $kmRange = (float)$range->max_km - (float)$range->min_km;
        $isPerKm = ($kmRange >= 50) || ((float)$range->fee <= 2.00 && $kmRange > 10);
        
        if ($isPerKm) {
            // Taxa por km: fee * km total
            // Exemplo: 1.75 por km, se calcular 20km, = 20 * 1.75 = 35.00
            $fee = (float)$range->fee * $km;
        }
        // Caso contrário, usa o valor fixo do campo 'fee' (já atribuído acima)

        \Log::info('DeliveryDistancePricing: Taxa calculada', [
            'km' => $km,
            'raw_distance' => $rawDistanceKm,
            'range_id' => $range->id,
            'range_min' => $range->min_km,
            'range_max' => $range->max_km,
            'range_fee' => $range->fee,
            'calculated_fee' => $fee,
            'subtotal' => $subtotal,
        ]);

        return [
            'fee' => round($fee, 2),
            'km' => $km,
            'free' => false,
        ];
    }
}


