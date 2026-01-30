<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Packaging;
use Illuminate\Http\Request;

class PackagingsController extends Controller
{
    public function index(Request $request)
    {
        $clientId = currentClientId();
        
        $query = Packaging::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->where(function($q) use ($clientId) {
                $q->where('client_id', $clientId)->orWhereNull('client_id');
            });
        
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        if ($request->has('active_only') && $request->active_only) {
            $query->where('is_active', true);
        }
        
        $packagings = $query->orderBy('name')->paginate(30)->withQueryString();
        
        return view('dashboard.producao.embalagens', compact('packagings'));
    }

    public function create()
    {
        return view('dashboard.producao.embalagens.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['client_id'] = currentClientId();
        $validated['is_active'] = $request->has('is_active') ? true : false;

        Packaging::create($validated);

        return redirect()->route('dashboard.producao.embalagens.index')
            ->with('success', 'Embalagem criada com sucesso!');
    }

    public function edit(Packaging $embalagem)
    {
        $clientId = currentClientId();
        
        $packaging = Packaging::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->where(function($q) use ($clientId) {
                $q->where('client_id', $clientId)->orWhereNull('client_id');
            })
            ->findOrFail($embalagem->id);
        
        return view('dashboard.producao.embalagens.edit', compact('packaging'));
    }

    public function update(Request $request, Packaging $embalagem)
    {
        $clientId = currentClientId();
        
        $packaging = Packaging::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->where(function($q) use ($clientId) {
                $q->where('client_id', $clientId)->orWhereNull('client_id');
            })
            ->findOrFail($embalagem->id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        $packaging->update($validated);

        return redirect()->route('dashboard.producao.embalagens.index')
            ->with('success', 'Embalagem atualizada com sucesso!');
    }

    public function destroy(Packaging $embalagem)
    {
        $clientId = currentClientId();
        
        $packaging = Packaging::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->where(function($q) use ($clientId) {
                $q->where('client_id', $clientId)->orWhereNull('client_id');
            })
            ->findOrFail($embalagem->id);

        $packaging->delete();

        return redirect()->route('dashboard.producao.embalagens.index')
            ->with('success', 'Embalagem removida com sucesso!');
    }
}
