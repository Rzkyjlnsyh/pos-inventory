<?php

namespace App\Http\Controllers\Owner;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use League\Csv\Reader;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductOwnerController extends Controller implements FromArray, WithHeadings
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

        return view('owner.product.index', compact('products', 'categories', 'q', 'categoryId'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        return view('owner.product.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => ['nullable', 'string', 'max:100', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:10240'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if ((float)$validated['price'] < (float)$validated['cost_price']) {
            return back()->withErrors(['price' => 'Harga jual tidak boleh lebih kecil dari harga beli.'])->withInput();
        }

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);

        Product::create($validated);

        return redirect()->route('owner.product.index')->with('success', 'Product created');
    }

    public function show(Product $product): View
    {
        $categories = Category::orderBy('name')->get();
        return view('owner.product.show', compact('product', 'categories'));
    }

    public function edit(Product $product): View
    {
        $categories = Category::orderBy('name')->get();
        return view('owner.product.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => ['nullable', 'string', 'max:100', 'unique:products,sku,' . $product->id],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:10240'],
            'is_active' => ['sometimes', 'boolean'],
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

        return redirect()->route('owner.product.index')->with('success', 'Product updated');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        $product->delete();
        return redirect()->route('owner.product.index')->with('success', 'Product deleted');
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
            ->get(['id', 'name', 'sku', 'cost_price', 'price']);
        return response()->json($products);
    }

    public function importForm(): View
    {
        return view('owner.product.import');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        $file = $request->file('file');
        $csv = Reader::createFromPath($file->getPathname(), 'r');
        $csv->setHeaderOffset(0);

        $requiredColumns = ['sku', 'name', 'category_id', 'cost_price', 'price', 'stock_qty', 'is_active'];
        $header = $csv->getHeader();

        if (count(array_intersect($requiredColumns, $header)) !== count($requiredColumns)) {
            return redirect()->back()->withErrors(['file' => 'File CSV harus memiliki kolom: sku, name, category_id, cost_price, price, stock_qty, is_active.']);
        }

        foreach ($csv->getRecords() as $record) {
            $validated = validator($record, [
                'sku' => ['nullable', 'string', 'max:100', 'unique:products,sku'],
                'name' => ['required', 'string', 'max:255'],
                'category_id' => ['nullable', 'exists:categories,id'],
                'cost_price' => ['required', 'numeric', 'min:0'],
                'price' => ['required', 'numeric', 'min:0'],
                'stock_qty' => ['required', 'integer', 'min:0'],
                'is_active' => ['required', 'in:0,1'],
            ])->validate();

            if ((float)$validated['price'] < (float)$validated['cost_price']) {
                continue; // Skip jika harga jual lebih kecil dari harga beli
            }

            // Ubah string kosong di category_id menjadi NULL
            if (empty($validated['category_id'])) {
                $validated['category_id'] = null;
            }

            $validated['is_active'] = (bool) $validated['is_active'];
            Product::create($validated);
        }

        return redirect()->route('owner.product.index')->with('success', 'Produk berhasil diimport');
    }

    public function headings(): array
    {
        return ['sku', 'name', 'category_id', 'cost_price', 'price', 'stock_qty', 'is_active'];
    }

    public function array(): array
    {
        return [
            ['SKU001', 'Kaos Polos Putih', '', 100000, 150000, 50, 1],
            ['SKU002', 'Kaos Polos Hitam', 1, 120000, 180000, 30, 1],
        ];
    }

    public function downloadTemplate()
    {
        return Excel::download($this, 'product_template.xlsx');
    }
}