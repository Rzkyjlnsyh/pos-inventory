<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SupplierTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'name',
            'contact_name',
            'phone',
            'email',
            'address',
            'is_active',
        ];
    }

    public function array(): array
    {
        // Mengembalikan array kosong karena ini hanya template
        return [];
    }
}