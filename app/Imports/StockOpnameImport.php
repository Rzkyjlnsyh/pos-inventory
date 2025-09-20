<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StockOpnameImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        // Kelompokkan berdasarkan document_number
        $grouped = $rows->groupBy('document_number');

        foreach ($grouped as $documentNumber => $group) {
            // Validasi header
            $validator = Validator::make($group->toArray(), [
                '*.document_number' => [
                    'required',
                    Rule::unique('stock_opnames', 'document_number'),
                ],
                '*.date' => [
                    'required',
                    'date',
                    function ($attribute, $value, $fail) {
                        if ($value !== date('Y-m-d')) {
                            $fail('Tanggal harus hari ini.');
                        }
                    },
                ],
                '*.notes' => 'nullable|string',
                '*.sku' => 'required|exists:products,sku',
                '*.actual_qty' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                throw new \Exception('Error pada data import: ' . $validator->errors()->first());
            }

            DB::transaction(function () use ($group, $documentNumber) {
                // Buat StockOpname
                $stockOpname = StockOpname::create([
                    'document_number' => $documentNumber,
                    'date' => $group->first()['date'],
                    'notes' => $group->first()['notes'] ?? null,
                    'status' => 'draft',
                    'user_id' => auth()->id(),
                ]);

                // Simpan items
                foreach ($group as $row) {
                    $product = Product::where('sku', $row['sku'])->first();

                    $stockOpname->items()->create([
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'sku' => $product->sku,
                        'system_qty' => $product->stock_qty,
                        'actual_qty' => $row['actual_qty'],
                        'difference' => $row['actual_qty'] - $product->stock_qty,
                    ]);
                }
            });
        }
    }
}