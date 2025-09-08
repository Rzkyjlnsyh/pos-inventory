<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use League\Csv\Reader;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CategoryAdminController extends Controller implements FromArray, WithHeadings
{
    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $counter = 1;
    
        // cek apakah slug sudah ada di DB
        while (
            Category::where('slug', $slug)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $original . '-' . $counter++;
        }
    
        return $slug;
    }
    public function index(Request $request): View
    {
        $q = $request->get('q');
        $perPage = $request->get('per_page', 10);

        $categories = Category::withCount('products')
            ->when($q, fn($query) => $query->where('name', 'like', "%$q%"))
            ->orderBy('name')
            ->paginate($perPage);

        return view('admin.categories.index', compact('categories', 'q'));
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['slug'] = $this->generateUniqueSlug($validated['name']);
        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);

        Category::create($validated);

        return redirect()->route('admin.category.index')->with('success', 'Category created');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['slug'] = $this->generateUniqueSlug($validated['name'], $category->id);

        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        $category->update($validated);

        return redirect()->route('admin.category.index')->with('success', 'Category updated');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();
        return redirect()->route('admin.category.index')->with('success', 'Category deleted');
    }

    public function importForm(): View
    {
        return view('admin.categories.import');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        $file = $request->file('file');
        $csv = Reader::createFromPath($file->getPathname(), 'r');
        $csv->setHeaderOffset(0);

        $requiredColumns = ['name', 'description', 'is_active'];
        $header = $csv->getHeader();

        if (count(array_intersect($requiredColumns, $header)) !== count($requiredColumns)) {
            return redirect()->back()->withErrors(['file' => 'File CSV harus memiliki kolom: name, description, is_active.']);
        }

        foreach ($csv->getRecords() as $record) {
            $validated = validator($record, [
                'name' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'is_active' => ['required', 'in:0,1'],
            ])->validate();

            $validated['slug'] = $this->generateUniqueSlug($validated['name']);

            $validated['is_active'] = (bool) $validated['is_active'];
            Category::create($validated);
        }

        return redirect()->route('admin.category.index')->with('success', 'Kategori berhasil diimport');
    }

    public function headings(): array
    {
        return ['name', 'description', 'is_active'];
    }

    public function array(): array
    {
        return [
            ['Kaos Polos', 'Kategori untuk kaos polos', 1],
            ['Aksesoris', 'Kategori untuk aksesoris', 1],
        ];
    }

    public function downloadTemplate()
    {
        return Excel::download($this, 'category_template.xlsx');
    }


}