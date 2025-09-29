<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Illuminate\Validation\Rule;

class ProductImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    // Tambahkan variabel untuk menghitung baris yang berhasil diimport
    private $rowCount = 0;

    public function model(array $row)
    {
        // Handle kategori: Cari atau buat baru
        $categoryId = null;
        if (!empty($row['category_name'])) {
            $category = Category::where('name', $row['category_name'])->first();
            if (!$category) {
                $slug = Category::generateUniqueSlug($row['category_name']);
                $category = Category::create([
                    'name' => $row['category_name'],
                    'slug' => $slug,
                    'description' => '',
                    'is_active' => true,
                ]);
            }
            $categoryId = $category->id;
        }

        // Tambah hitungan baris
        $this->rowCount++;

        return new Product([
            'sku' => $row['sku'] ?? null,
            'name' => $row['name'],
            'category_id' => $categoryId,
            'cost_price' => $row['cost_price'],
            'price' => $row['price'],
            'stock_qty' => $row['stock_qty'],
            'is_active' => (bool) $row['is_active'],
        ]);
    }

    public function rules(): array
    {
        return [
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')],
            'name' => ['required', 'string', 'max:255'],
            'category_name' => ['nullable', 'string', 'max:255'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock_qty' => ['required', 'integer'], // ❌ hapus min:0
            'is_active' => ['required', 'in:0,1'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            // Tambah pesan custom jika perlu, misalnya:
            'sku.unique' => 'SKU sudah digunakan.',
            'name.required' => 'Nama produk wajib diisi.',
            'cost_price.required' => 'Harga modal wajib diisi.',
            'price.required' => 'Harga jual wajib diisi.',
            'stock_qty.required' => 'Jumlah stok wajib diisi.',
            'is_active.required' => 'Status aktif wajib diisi (0 atau 1).',
        ];
    }

    public function customValidationAttributes()
    {
        return ['category_name' => 'nama kategori'];
    }

    // Method untuk mendapatkan jumlah baris yang berhasil diimport
    public function getRowCount(): int
    {
        return $this->rowCount;
    }
}