<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustomerTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'name',
            'phone',
            'email',
            'address',
            'notes',
            'is_active',
        ];
    }

    public function array(): array
    {
        // Mengembalikan array kosong karena ini hanya template
        return [];
    }
}