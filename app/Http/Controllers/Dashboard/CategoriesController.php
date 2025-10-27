<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoriesController extends Controller
{
    public function index()
    {
        $cats = DB::table('categories')->orderBy('name')->paginate(30);
        return view('dashboard.categories', compact('cats'));
    }

    public function create()
    {
        return view('dashboard.categories_form', ['category' => null]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        // Campos opcionais (podem não existir no banco)
        $optional = ['slug', 'image', 'display_order'];
        foreach($optional as $field) {
            if($r->has($field)) $data[$field] = $r->get($field);
        }

        $data['is_active'] = (int)($data['is_active'] ?? 1);
        $data['created_at'] = now();
        $data['updated_at'] = now();

        // Remove campos vazios
        $data = array_filter($data, fn($v) => $v !== '' && $v !== null);

        DB::table('categories')->insert($data);

        return redirect()->route('dashboard.categories')->with('ok', 'Categoria criada!');
    }

    public function edit($id)
    {
        $category = DB::table('categories')->find($id);
        if (!$category) {
            return redirect()->route('dashboard.categories')->with('error', 'Categoria não encontrada');
        }

        return view('dashboard.categories_form', ['category' => $category]);
    }

    public function update(Request $r, $id)
    {
        $category = DB::table('categories')->find($id);
        if (!$category) {
            return redirect()->route('dashboard.categories')->with('error', 'Categoria não encontrada');
        }

        $data = $r->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        // Campos opcionais
        $optional = ['slug', 'image', 'display_order'];
        foreach($optional as $field) {
            if($r->has($field)) $data[$field] = $r->get($field);
        }

        $data['updated_at'] = now();
        
        // Remove vazios
        $data = array_filter($data, fn($v) => $v !== '' && $v !== null);
        
        DB::table('categories')->where('id', $id)->update($data);

        return redirect()->route('dashboard.categories')->with('ok', 'Categoria atualizada!');
    }

    public function destroy($id)
    {
        DB::table('categories')->where('id', $id)->delete();
        return redirect()->route('dashboard.categories')->with('ok', 'Categoria excluída!');
    }

    public function toggleStatus($id)
    {
        $category = DB::table('categories')->find($id);
        if (!$category) {
            return back()->with('error', 'Categoria não encontrada');
        }

        DB::table('categories')->where('id', $id)->update([
            'is_active' => (int)!$category->is_active,
            'updated_at' => now(),
        ]);

        return back()->with('ok', 'Status atualizado!');
    }
}

