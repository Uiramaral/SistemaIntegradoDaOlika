<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlansController extends Controller
{
    /**
     * Lista todos os planos
     */
    public function index()
    {
        $plans = Plan::withCount(['subscriptions' => function ($q) {
            $q->where('status', 'active');
        }])
            ->orderBy('sort_order')
            ->get();

        return view('master.plans.index', compact('plans'));
    }

    /**
     * Formulário de criação
     */
    public function create()
    {
        return view('master.plans.form');
    }

    /**
     * Salva novo plano
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:plans,slug|regex:/^[a-z0-9-]+$/',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'trial_days' => 'nullable|integer|min:0',
            'max_products' => 'nullable|integer|min:0',
            'max_orders_per_month' => 'nullable|integer|min:0',
            'max_users' => 'nullable|integer|min:1',
            'max_whatsapp_instances' => 'nullable|integer|min:0',
            'features_text' => 'nullable|string',
        ]);

        // Gerar slug se não informado
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Garantir que slug é único
        $baseSlug = $validated['slug'];
        $counter = 1;
        while (Plan::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $baseSlug . '-' . $counter++;
        }

        // Converter features de texto para array
        $features = [];
        if (!empty($validated['features_text'])) {
            $features = array_filter(array_map('trim', explode("\n", $validated['features_text'])));
        }
        unset($validated['features_text']);

        // Handle checkboxes
        $validated['has_whatsapp'] = $request->boolean('has_whatsapp');
        $validated['has_ai'] = $request->boolean('has_ai');
        $validated['active'] = $request->boolean('active');
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['features'] = $features;
        
        // Forçar billing_cycle como 'monthly' (não há planos anuais)
        // Sempre definir antes do create para evitar erro de campo obrigatório
        $validated['billing_cycle'] = 'monthly';

        // Criar o plano
        // O Model já garante billing_cycle = 'monthly' no boot method, mas definimos aqui também
        $plan = Plan::create($validated);

        return redirect()->route('master.plans.index')
            ->with('success', "Plano {$plan->name} criado com sucesso!");
    }

    /**
     * Formulário de edição
     */
    public function edit(Plan $plan)
    {
        return view('master.plans.form', compact('plan'));
    }

    /**
     * Atualiza plano
     */
    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'trial_days' => 'nullable|integer|min:0',
            'max_products' => 'nullable|integer|min:0',
            'max_orders_per_month' => 'nullable|integer|min:0',
            'max_users' => 'nullable|integer|min:1',
            'max_whatsapp_instances' => 'nullable|integer|min:0',
            // billing_cycle removido - sempre será 'monthly'
            'features_text' => 'nullable|string',
        ]);

        // Converter features de texto para array
        $features = [];
        if (!empty($validated['features_text'])) {
            $features = array_filter(array_map('trim', explode("\n", $validated['features_text'])));
        }
        unset($validated['features_text']);

        // Handle checkboxes
        $validated['has_whatsapp'] = $request->boolean('has_whatsapp');
        $validated['has_ai'] = $request->boolean('has_ai');
        $validated['active'] = $request->boolean('active');
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['features'] = $features;
        
        // Forçar billing_cycle como 'monthly' (não há planos anuais)
        // Sempre definir antes do update para evitar erro de campo obrigatório
        $validated['billing_cycle'] = 'monthly';

        // Atualizar o plano
        // O Model já garante billing_cycle = 'monthly' no boot, mas definimos aqui também
        $plan->fill($validated);
        $plan->billing_cycle = 'monthly'; // Garantia extra
        $plan->save();

        return redirect()->route('master.plans.index')
            ->with('success', "Plano {$plan->name} atualizado com sucesso!");
    }

    /**
     * Ativa/Desativa plano
     */
    public function toggleStatus(Plan $plan)
    {
        // Corrigir: usar 'active' ao invés de 'is_active'
        $plan->update(['active' => !$plan->active]);

        $status = $plan->active ? 'ativado' : 'desativado';
        return back()->with('success', "Plano {$status} com sucesso!");
    }

    /**
     * Destaca/Remove destaque do plano
     */
    public function toggleFeatured(Plan $plan)
    {
        $plan->update(['is_featured' => !$plan->is_featured]);

        $status = $plan->is_featured ? 'destacado' : 'removido do destaque';
        return back()->with('success', "Plano {$status} com sucesso!");
    }

    /**
     * Reordena planos
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:plans,id',
            'orders.*.order' => 'required|integer|min:0',
        ]);

        foreach ($request->orders as $item) {
            Plan::where('id', $item['id'])->update(['sort_order' => $item['order']]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Exclui plano (apenas se não tiver assinaturas)
     */
    public function destroy(Plan $plan)
    {
        if ($plan->subscriptions()->exists()) {
            return back()->with('error', 'Não é possível excluir um plano que possui assinaturas.');
        }

        $name = $plan->name;
        $plan->delete();

        return redirect()->route('master.plans.index')
            ->with('success', "Plano {$name} excluído com sucesso!");
    }
}
