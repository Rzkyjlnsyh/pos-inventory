<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->get('q');
        $categoryId = $request->get('category_id');

        $products = Product::with('category')
            ->when($q, fn($query) => $query->where('name', 'like', "%$q%"))
            ->when($categoryId, fn($query) => $query->where('category_id', $categoryId))
            ->orderByDesc('id')
            ->paginate(15);

        $categories = Category::orderBy('name')->get();

        return view('owner.catalog.products.index', compact('products','categories','q','categoryId'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        return view('owner.catalog.products.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => ['nullable','string','max:100','unique:products,sku'],
            'name' => ['required','string','max:255'],
            'category_id' => ['nullable','exists:categories,id'],
            'cost_price' => ['required','numeric','min:0'],
            'price' => ['required','numeric','min:0'],
            'image' => ['nullable','image','mimes:jpeg,png,jpg,gif','max:10240'],
            'is_active' => ['sometimes','boolean'],
        ]);

        if ((float)$validated['price'] < (float)$validated['cost_price']) {
            return back()->withErrors(['price' => 'Harga jual tidak boleh lebih kecil dari harga beli.'])->withInput();
        }

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);

        Product::create($validated);

        return redirect()->route('owner.catalog.products.index')->with('success', 'Product created');
    }

    public function edit(Product $product): View
    {
        $categories = Category::orderBy('name')->get();
        return view('owner.catalog.products.edit', compact('product','categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => ['nullable','string','max:100','unique:products,sku,'.$product->id],
            'name' => ['required','string','max:255'],
            'category_id' => ['nullable','exists:categories,id'],
            'cost_price' => ['required','numeric','min:0'],
            'price' => ['required','numeric','min:0'],
            'image' => ['nullable','image','mimes:jpeg,png,jpg,gif','max:10240'],
            'is_active' => ['sometimes','boolean'],
        ]);

        if ((float)$validated['price'] < (float)$validated['cost_price']) {
            return back()->withErrors(['price' => 'Harga jual tidak boleh lebih kecil dari harga beli.'])->withInput();
        }

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        $product->update($validated);

        return redirect()->route('owner.catalog.products.index')->with('success', 'Product updated');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        $product->delete();
        return redirect()->route('owner.catalog.products.index')->with('success', 'Product deleted');
    }

    public function search(Request $request)
    {
        $q = $request->get('q');
        $products = Product::query()
            ->where('is_active', true)
            ->when($q, function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('name', 'like', "%$q%")
                       ->orWhere('sku', 'like', "%$q%");
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id','name','sku','cost_price','price']);
        return response()->json($products);
    }
}