<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StockOpnameTemplateExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return collect([
            [
                'document_number' => 'SO' . date('Ymd') . '001',
                'date' => date('Y-m-d'),
                'notes' => 'Contoh catatan stock opname',
                'sku' => 'SKU001',
                'actual_qty' => 100,
            ],
            [
                'document_number' => 'SO' . date('Ymd') . '001',
                'date' => date('Y-m-d'),
                'notes' => '',
                'sku' => 'SKU002',
                'actual_qty' => 50,
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'Document Number',
            'Date (YYYY-MM-DD)',
            'Notes (Optional)',
            'SKU',
            'Actual Qty',
        ];
    }
}