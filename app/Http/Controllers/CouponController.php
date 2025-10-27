<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CouponController extends Controller
{
    /**
     * Lista cupons públicos
     */
    public function index()
    {
        $coupons = Coupon::public()
            ->active()
            ->valid()
            ->available()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('coupons.index', compact('coupons'));
    }

    /**
     * Valida cupom (API para PDV)
     */
    public function validateCoupon(Request $request)
    {
        $code = strtoupper(trim($request->get('code') ?? ''));
        
        if (empty($code)) {
            return response()->json(['valid' => false, 'message' => 'Código do cupom é obrigatório.'], 400);
        }

        $coupon = Coupon::where('code', $code)
            ->where('active', true)
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
            })
            ->first();

        if (!$coupon) {
            return response()->json(['valid' => false, 'message' => 'Cupom inválido ou expirado.'], 404);
        }

        // Valida limite de uso
        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return response()->json(['valid' => false, 'message' => 'Cupom esgotado.'], 400);
        }

        // Valida data de início
        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            return response()->json(['valid' => false, 'message' => 'Cupom ainda não está ativo.'], 400);
        }

        return response()->json([
            'valid' => true,
            'type'  => $coupon->type,   // 'percentage' ou 'fixed'
            'value' => $coupon->value,
            'message' => 'Cupom aplicado com sucesso!'
        ]);
    }

    /**
     * API: Lista cupons visíveis para um cliente
     */
    public function getVisibleCoupons(Request $request)
    {
        $request->validate([
            'customer_id' => 'nullable|integer|exists:customers,id',
        ]);

        $customerId = $request->customer_id;
        
        $coupons = Coupon::visibleFor($customerId)
            ->active()
            ->valid()
            ->available()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($coupon) {
                return [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'name' => $coupon->name,
                    'description' => $coupon->description,
                    'type' => $coupon->type,
                    'value' => $coupon->value,
                    'formatted_value' => $coupon->formatted_value,
                    'minimum_amount' => $coupon->minimum_amount,
                    'visibility' => $coupon->visibility,
                    'expires_at' => $coupon->expires_at,
                ];
            });

        return response()->json([
            'success' => true,
            'coupons' => $coupons,
        ]);
    }

    /**
     * API: Criar cupom
     */
    public function create(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'minimum_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_limit_per_customer' => 'nullable|integer|min:1',
            'visibility' => 'required|in:public,private,targeted',
            'target_customer_id' => 'nullable|integer|exists:customers,id|required_if:visibility,targeted',
            'private_description' => 'nullable|string|max:500',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
        ]);

        try {
            $coupon = Coupon::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Cupom criado com sucesso!',
                'coupon' => $coupon,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar cupom: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Atualizar cupom
     */
    public function update(Request $request, Coupon $coupon)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'minimum_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_limit_per_customer' => 'nullable|integer|min:1',
            'visibility' => 'required|in:public,private,targeted',
            'target_customer_id' => 'nullable|integer|exists:customers,id|required_if:visibility,targeted',
            'private_description' => 'nullable|string|max:500',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'is_active' => 'boolean',
        ]);

        try {
            $coupon->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Cupom atualizado com sucesso!',
                'coupon' => $coupon->fresh(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar cupom: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Deletar cupom
     */
    public function delete(Coupon $coupon)
    {
        try {
            // Verificar se o cupom foi usado
            if ($coupon->orderCoupons()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível deletar cupom que já foi usado',
                ], 400);
            }

            $coupon->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cupom deletado com sucesso!',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar cupom: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Estatísticas de cupons
     */
    public function getStats()
    {
        $stats = [
            'total_coupons' => Coupon::count(),
            'active_coupons' => Coupon::active()->count(),
            'public_coupons' => Coupon::public()->active()->count(),
            'private_coupons' => Coupon::private()->active()->count(),
            'targeted_coupons' => Coupon::targeted()->active()->count(),
            'expired_coupons' => Coupon::where('expires_at', '<', now())->count(),
            'used_coupons' => Coupon::where('used_count', '>', 0)->count(),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * API: Lista cupons para admin
     */
    public function adminIndex(Request $request)
    {
        $query = Coupon::query();

        // Filtros
        if ($request->has('visibility')) {
            $query->where('visibility', $request->visibility);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $coupons = $query->with('targetCustomer')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'coupons' => $coupons,
        ]);
    }
}
