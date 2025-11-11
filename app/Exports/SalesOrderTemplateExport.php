<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class SalesOrderTemplateExport implements FromArray, WithHeadings, WithTitle, WithColumnWidths
{
    public function array(): array
    {
        return [
            // Contoh 1: SO dengan 2 items (beli_jadi + cash)
            [
                'SAL2501010001',      // SO_NUMBER
                '2025-01-15',         // ORDER_DATE
                '2025-01-20',         // DEADLINE
                'Budi Santoso',       // CUSTOMER_NAME
                '081234567890',       // CUSTOMER_PHONE
                'beli_jadi',          // ORDER_TYPE
                'cash',               // PAYMENT_METHOD
                'dp',                 // PAYMENT_STATUS
                'Kaos Polo Lengan Pendek', // PRODUCT_NAME
                'POLO001',            // SKU
                75000,                // SALE_PRICE
                2,                    // QTY
                5000                  // DISCOUNT
            ],
            [
                'SAL2501010001',      // SAME SO NUMBER
                '2025-01-15',         // SAME DATE
                '2025-01-20',         // SAME DEADLINE  
                'Budi Santoso',       // SAME CUSTOMER
                '081234567890',       // SAME PHONE
                'beli_jadi',          // SAME ORDER TYPE
                'cash',               // SAME PAYMENT METHOD
                'dp',                 // SAME PAYMENT STATUS
                'Celana Chino',       // DIFFERENT PRODUCT
                'CHINO001',           // DIFFERENT SKU
                120000,               // DIFFERENT PRICE
                1,                    // DIFFERENT QTY
                0                     // DIFFERENT DISCOUNT
            ],
            // Contoh 2: SO jahit_sendiri + transfer
            [
                'SAL2501010002',
                '2025-01-16',
                '2025-01-25',
                'Siti Rahayu',
                '081298765432',
                'jahit_sendiri',
                'transfer',
                'dp',
                'Kemeja Linen Custom',
                'LINEN001',
                150000,
                1,
                10000
            ],
            // Contoh 3: SO dengan status lunas
            [
                'SAL2501010003',
                '2025-01-17',
                '2025-01-22',
                'Ahmad Wijaya',
                '081377788899',
                'beli_jadi',
                'transfer',
                'lunas',
                'Jaket Denim',
                'DENIM001',
                250000,
                1,
                0
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'SO_NUMBER',
            'ORDER_DATE',
            'DEADLINE',
            'CUSTOMER_NAME', 
            'CUSTOMER_PHONE',
            'ORDER_TYPE',
            'PAYMENT_METHOD',
            'PAYMENT_STATUS',
            'PRODUCT_NAME',
            'SKU',
            'SALE_PRICE',
            'QTY',
            'DISCOUNT'
        ];
    }

    public function title(): string
    {
        return 'Template Import Sales Order';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, // SO_NUMBER
            'B' => 12, // ORDER_DATE
            'C' => 12, // DEADLINE
            'D' => 20, // CUSTOMER_NAME
            'E' => 15, // CUSTOMER_PHONE
            'F' => 12, // ORDER_TYPE
            'G' => 12, // PAYMENT_METHOD
            'H' => 12, // PAYMENT_STATUS
            'I' => 25, // PRODUCT_NAME
            'J' => 12, // SKU
            'K' => 12, // SALE_PRICE
            'L' => 8,  // QTY
            'M' => 10, // DISCOUNT
        ];
    }
}