<?php

namespace App\Exports;

use App\Models\SalesOrder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class SalesOrderExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithColumnWidths
{
    protected $salesOrders;

    public function __construct($salesOrders)
    {
        $this->salesOrders = $salesOrders;
    }

    public function collection()
    {
        return $this->salesOrders;
    }

    public function map($salesOrder): array
    {
        $rows = [];
        
        foreach ($salesOrder->items as $index => $item) {
            $rows[] = [
                $salesOrder->so_number,
                $salesOrder->order_date->format('Y-m-d'),
                $salesOrder->deadline ? $salesOrder->deadline->format('Y-m-d') : '',
                $salesOrder->customer ? $salesOrder->customer->name : 'Umum',
                $salesOrder->customer ? $salesOrder->customer->phone : '',
                $salesOrder->order_type,
                $salesOrder->payment_method,
                $salesOrder->payment_status,
                $item->product_name,
                $item->sku ?? '',
                $item->sale_price,
                $item->qty,
                $item->discount,
                $salesOrder->status,
                $salesOrder->grand_total,
                $salesOrder->paid_total,
                $salesOrder->remaining_amount,
                $salesOrder->created_at->format('Y-m-d H:i:s'),
                $salesOrder->creator->name ?? 'System'
            ];
        }

        return $rows;
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
            'DISCOUNT',
            'STATUS',
            'GRAND_TOTAL',
            'TOTAL_DIBAYAR',
            'SISA',
            'CREATED_AT',
            'CREATED_BY'
        ];
    }

    public function title(): string
    {
        return 'Sales Orders';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, 'B' => 12, 'C' => 12, 'D' => 20, 'E' => 15,
            'F' => 12, 'G' => 15, 'H' => 15, 'I' => 25, 'J' => 12,
            'K' => 12, 'L' => 8, 'M' => 10, 'N' => 12, 'O' => 12,
            'P' => 12, 'Q' => 12, 'R' => 18, 'S' => 15
        ];
    }
}