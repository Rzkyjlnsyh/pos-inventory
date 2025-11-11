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

        // 🔧 FIX: Konversi format Indonesia ke format database
        $costPrice = $this->convertToFloat($row['cost_price']);
        $price = $this->convertToFloat($row['price']);

        // Validasi harga jual tidak boleh lebih kecil dari harga modal
        if ($price < $costPrice) {
            throw new \Exception("Harga jual tidak boleh lebih kecil dari harga modal. Baris: " . ($this->rowCount + 1));
        }

        // Tambah hitungan baris
        $this->rowCount++;

        return new Product([
            'sku' => $row['sku'] ?? null,
            'name' => $row['name'],
            'category_id' => $categoryId,
            'cost_price' => $costPrice,
            'price' => $price,
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
            // 🔧 FIX: Custom validation untuk format Indonesia
            'cost_price' => ['required', function ($attribute, $value, $fail) {
                $converted = $this->convertToFloat($value);
                if ($converted === false || $converted < 0) {
                    $fail('Harga modal harus berupa angka yang valid dan tidak negatif.');
                }
            }],
            'price' => ['required', function ($attribute, $value, $fail) {
                $converted = $this->convertToFloat($value);
                if ($converted === false || $converted < 0) {
                    $fail('Harga jual harus berupa angka yang valid dan tidak negatif.');
                }
            }],
            'stock_qty' => ['required', 'integer'],
            'is_active' => ['required', 'in:0,1'],
        ];
    }

    /**
     * 🔧 FIX: Konversi format Indonesia ke float
     * Contoh: 
     * - "61998,77" → 61998.77
     * - "17.000" → 17000.00
     * - "17.000,77" → 17000.77
     * - "1.234,56" → 1234.56
     */
    private function convertToFloat($value)
    {
        // Jika sudah numeric, langsung return
        if (is_numeric($value)) {
            return (float) $value;
        }

        // Jika string, bersihkan format Indonesia
        $value = trim(strval($value));
        
        // Jika kosong, return false
        if ($value === '') {
            return false;
        }

        // Hapus karakter selain angka, koma, dan titik
        $cleaned = preg_replace('/[^\d,.]/', '', $value);
        
        // Jika tidak ada digit, return false
        if (!preg_match('/\d/', $cleaned)) {
            return false;
        }

        // Handle berbagai format
        if (strpos($cleaned, ',') !== false && strpos($cleaned, '.') !== false) {
            // Format: 1.234,56 → hapus titik (thousand separator), ganti koma dengan titik (decimal separator)
            $cleaned = str_replace('.', '', $cleaned);
            $cleaned = str_replace(',', '.', $cleaned);
        } elseif (strpos($cleaned, ',') !== false && strpos($cleaned, '.') === false) {
            // Format: 1234,56 atau 61.998,77 → cek jika koma sebagai decimal separator
            $parts = explode(',', $cleaned);
            if (count($parts) === 2 && strlen($parts[1]) <= 2) {
                // Format: 1234,56 → ganti koma dengan titik
                $cleaned = str_replace(',', '.', $cleaned);
            } else {
                // Format: 61.998,77 → hapus titik, ganti koma dengan titik
                $cleaned = str_replace('.', '', $cleaned);
                $cleaned = str_replace(',', '.', $cleaned);
            }
        } elseif (strpos($cleaned, '.') !== false) {
            // Format: 17000.00 (sudah format internasional) - biarkan
            // Atau 17.000 (format Indonesia) - perlu dikonversi
            $parts = explode('.', $cleaned);
            if (count($parts) > 2) {
                // Format: 17.000 → hapus semua titik
                $cleaned = str_replace('.', '', $cleaned);
            }
            // Jika hanya 1 titik, biarkan sebagai decimal separator
        }

        // Konversi ke float
        $result = (float) $cleaned;
        
        // Validasi hasil konversi
        if ($result < 0) {
            return false;
        }

        return $result;
    }

    public function customValidationMessages()
    {
        return [
            'sku.unique' => 'SKU sudah digunakan.',
            'name.required' => 'Nama produk wajib diisi.',
            'stock_qty.required' => 'Jumlah stok wajib diisi.',
            'is_active.required' => 'Status aktif wajib diisi (0 atau 1).',
            'is_active.in' => 'Status aktif harus 0 atau 1.',
        ];
    }

    public function customValidationAttributes()
    {
        return ['category_name' => 'nama kategori'];
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }
}