<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->get('q');
        $categories = Category::query()
            ->when($q, fn($query) => $query->where('name', 'like', "%$q%"))
            ->orderBy('name')
            ->paginate(15);

        return view('owner.catalog.categories.index', compact('categories', 'q'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        return view('owner.catalog.categories.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required','string','max:255'],
            'parent_id' => ['nullable','exists:categories,id'],
            'description' => ['nullable','string'],
            'is_active' => ['sometimes','boolean'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);

        Category::create($validated);

        return redirect()->route('owner.catalog.category.index')->with('success', 'Category created');
    }

    public function edit(Category $category): View
    {
        $categories = Category::where('id', '!=', $category->id)->orderBy('name')->get();
        return view('owner.catalog.categories.edit', compact('category', 'categories'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required','string','max:255'],
            'parent_id' => ['nullable','exists:categories,id'],
            'description' => ['nullable','string'],
            'is_active' => ['sometimes','boolean'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        $category->update($validated);

        return redirect()->route('owner.catalog.category.index')->with('success', 'Category updated');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();
        return redirect()->route('owner.catalog.category.index')->with('success', 'Category deleted');
    }
}