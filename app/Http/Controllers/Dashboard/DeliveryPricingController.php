<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeliveryDistancePricing;

class DeliveryPricingController extends Controller
{
    public function index()
    {
        $rows = DeliveryDistancePricing::orderBy('sort_order')->orderBy('min_km')->get();
        return view('dashboard.delivery-pricing.index', compact('rows'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'min_km' => 'required|numeric|min:0',
            'max_km' => 'required|numeric|min:0|gte:min_km',
            'fee' => 'required|numeric|min:0',
            'min_amount_free' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        DeliveryDistancePricing::create($data);
        return redirect()->back()->with('success', 'Faixa criada com sucesso');
    }

    public function update(Request $request, DeliveryDistancePricing $pricing)
    {
        $data = $request->validate([
            'min_km' => 'required|numeric|min:0',
            'max_km' => 'required|numeric|min:0|gte:min_km',
            'fee' => 'required|numeric|min:0',
            'min_amount_free' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $pricing->update($data);
        return redirect()->back()->with('success', 'Faixa atualizada com sucesso');
    }

    public function destroy(DeliveryDistancePricing $pricing)
    {
        $pricing->delete();
        return redirect()->back()->with('success', 'Faixa removida');
    }

    /**
     * Simular taxa de entrega
     */
    public function simulate(Request $request)
    {
        $validated = $request->validate([
            'zipcode' => 'required|string|min:8|max:10',
            'subtotal' => 'nullable|numeric|min:0',
        ]);

        $zipcode = preg_replace('/\D/', '', $validated['zipcode']);
        $subtotal = (float)($validated['subtotal'] ?? 0.0);

        if (strlen($zipcode) !== 8) {
            return response()->json([
                'success' => false,
                'message' => 'CEP inválido. Deve conter 8 dígitos.'
            ], 400);
        }

        try {
            $deliveryFeeService = new \App\Services\DeliveryFeeService();
            $result = $deliveryFeeService->calculateDeliveryFee($zipcode, $subtotal);

            return response()->json($result);
        } catch (\Exception $e) {
            \Log::error('DeliveryPricingController: Erro ao simular taxa', [
                'error' => $e->getMessage(),
                'zipcode' => $zipcode,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao calcular taxa de entrega: ' . $e->getMessage()
            ], 500);
        }
    }
}


