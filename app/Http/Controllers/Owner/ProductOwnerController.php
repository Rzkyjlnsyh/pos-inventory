<?php

namespace App\Http\Controllers\Owner;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ProductOwnerController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->orderByDesc('id')->get();
        return view('owner.product.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('owner.product.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => ['nullable','string','max:100','unique:products,sku'],
            'barcode' => ['nullable','string','max:150','unique:products,barcode'],
            'name' => ['required','string','max:255'],
            'category_id' => ['nullable','exists:categories,id'],
            'cost_price' => ['required','numeric','min:0'],
            'price' => ['required','numeric','min:0'],
            'is_active' => ['sometimes','boolean'],
            'image' => ['nullable','image','mimes:jpeg,png,jpg,gif','max:10240'],
        ]);

        if ((float)$validated['price'] < (float)$validated['cost_price']) {
            return back()->withErrors(['price' => 'Harga jual tidak boleh lebih kecil dari harga beli.'])->withInput();
        }

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        } else {
            // set default image path (public/assets/logo.png as placeholder)
            $validated['image_path'] = 'assets/logo.png';
        }

        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);

        Product::create($validated);

        return redirect()->route('owner.product.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product)
    {
        return view('owner.product.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        return view('owner.product.edit', compact('product','categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku' => ['nullable','string','max:100','unique:products,sku,'.$product->id],
            'barcode' => ['nullable','string','max:150','unique:products,barcode,'.$product->id],
            'name' => ['required','string','max:255'],
            'category_id' => ['nullable','exists:categories,id'],
            'cost_price' => ['required','numeric','min:0'],
            'price' => ['required','numeric','min:0'],
            'is_active' => ['sometimes','boolean'],
            'image' => ['nullable','image','mimes:jpeg,png,jpg,gif','max:10240'],
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

        return redirect()->route('owner.product.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        $product->delete();
        return redirect()->route('owner.product.index')->with('success', 'Produk berhasil dihapus.');
    }
}
