<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CouponsController extends Controller
{
    public function index(Request $request)
    {
        $query = Coupon::query();

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('visibility')) {
            $query->where('visibility', $request->visibility);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Se for requisição AJAX, retornar JSON sem paginação
        if ($request->ajax() || $request->wantsJson()) {
            $allCoupons = $query->orderBy('created_at', 'desc')->get();
            return response()->json([
                'coupons' => $allCoupons->map(function($coupon) {
                    return [
                        'id' => $coupon->id,
                        'code' => $coupon->code,
                        'name' => $coupon->name ?? '',
                        'is_active' => $coupon->is_active ?? true,
                        'formatted_value' => $coupon->formatted_value ?? '',
                        'used_count' => $coupon->used_count ?? 0,
                        'usage_limit' => $coupon->usage_limit,
                        'expires_at' => $coupon->expires_at ? $coupon->expires_at->format('d/m/Y') : null,
                    ];
                })
            ]);
        }
        
        $coupons = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        
        // Estatísticas
        $stats = [
            'total' => Coupon::count(),
            'active' => Coupon::where('is_active', true)->count(),
            'public' => Coupon::where('visibility', 'public')->count(),
            'private' => Coupon::where('visibility', 'private')->count(),
        ];

        return view('dashboard.coupons.index', compact('coupons', 'stats'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get(['id', 'name', 'email']);
        return view('dashboard.coupons.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'minimum_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_limit_per_customer' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean',
            'visibility' => 'required|in:public,private,targeted',
            'target_customer_id' => 'nullable|required_if:visibility,targeted|exists:customers,id',
            'private_description' => 'nullable|string|max:500',
            'first_order_only' => 'boolean',
            'free_shipping_only' => 'boolean',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = (bool)($validated['is_active'] ?? true);
        $validated['used_count'] = 0;
        $validated['first_order_only'] = (bool)($validated['first_order_only'] ?? false);
        $validated['free_shipping_only'] = (bool)($validated['free_shipping_only'] ?? false);

        if ($validated['visibility'] !== 'targeted') {
            $validated['target_customer_id'] = null;
        }

        Coupon::create($validated);

        return redirect()->route('dashboard.coupons.index')
            ->with('success', 'Cupom criado com sucesso!');
    }

    public function edit(Coupon $coupon)
    {
        $customers = Customer::orderBy('name')->get(['id', 'name', 'email']);
        return view('dashboard.coupons.edit', compact('coupon', 'customers'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('coupons')->ignore($coupon->id)],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'minimum_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_limit_per_customer' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean',
            'visibility' => 'required|in:public,private,targeted',
            'target_customer_id' => 'nullable|required_if:visibility,targeted|exists:customers,id',
            'private_description' => 'nullable|string|max:500',
            'first_order_only' => 'boolean',
            'free_shipping_only' => 'boolean',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = (bool)($validated['is_active'] ?? $coupon->is_active);
        $validated['first_order_only'] = (bool)($validated['first_order_only'] ?? false);
        $validated['free_shipping_only'] = (bool)($validated['free_shipping_only'] ?? false);

        if ($validated['visibility'] !== 'targeted') {
            $validated['target_customer_id'] = null;
        }

        $coupon->update($validated);

        return redirect()->route('dashboard.coupons.index')
            ->with('success', 'Cupom atualizado com sucesso!');
    }

    public function destroy(Coupon $coupon)
    {
        if ($coupon->used_count > 0) {
            return redirect()->route('dashboard.coupons.index')
                ->with('error', 'Não é possível excluir cupom que já foi usado.');
        }

        $coupon->delete();

        return redirect()->route('dashboard.coupons.index')
            ->with('success', 'Cupom removido com sucesso!');
    }

    public function toggleStatus(Coupon $coupon)
    {
        $coupon->is_active = !$coupon->is_active;
        $coupon->save();

        return redirect()->route('dashboard.coupons.index')
            ->with('success', 'Status do cupom atualizado!');
    }
}
