<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return view('dash.pages.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('dash.pages.categories.create');
    }

    public function store(Request $request)
    {
        Category::create($request->all());
        return redirect()->route('dashboard.categories.index')->with('success', 'Categoria criada com sucesso!');
    }

    public function edit(Category $category)
    {
        return view('dash.pages.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $category->update($request->all());
        return redirect()->route('dashboard.categories')->with('success', 'Categoria atualizada com sucesso!');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('dashboard.categories')->with('success', 'Categoria removida com sucesso!');
    }

    public function toggleStatus($id)
    {
        $category = Category::findOrFail($id);
        $category->active = !$category->active;
        $category->save();

        return redirect()->route('dashboard.categories')->with('success', 'Status da categoria atualizado!');
    }
}