<?php

namespace App\Exports;

use App\Models\Shift;
use App\Models\SalesOrder;
use App\Models\Expense;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ShiftDetailExport implements FromCollection, WithHeadings, WithTitle
{
    protected $shift;

    public function __construct(Shift $shift)
    {
        $this->shift = $shift;
    }

    public function collection()
    {
        $data = [];

        // Informasi Shift
        $data[] = ['Shift Information', '', '', '', ''];
        $data[] = ['User', $this->shift->user->name, '', '', ''];
        $data[] = ['Start Time', \Carbon\Carbon::parse($this->shift->start_time)->format('d/m/Y H:i'), '', '', ''];
        $data[] = ['End Time', $this->shift->end_time ? \Carbon\Carbon::parse($this->shift->end_time)->format('d/m/Y H:i') : '-', '', '', ''];
        $data[] = ['Initial Cash', 'Rp ' . number_format((float) $this->shift->initial_cash, 0, ',', '.'), '', '', ''];
        $data[] = ['Cash Total', 'Rp ' . number_format((float) $this->shift->cash_total, 0, ',', '.'), '', '', ''];
        $data[] = ['Expense Total', 'Rp ' . number_format((float) $this->shift->expense_total, 0, ',', '.'), '', '', ''];
        $data[] = ['Final Cash', $this->shift->final_cash !== null ? 'Rp ' . number_format((float) $this->shift->final_cash, 0, ',', '.') : '-', '', '', ''];
        $data[] = ['Discrepancy', $this->shift->discrepancy !== null ? 'Rp ' . number_format((float) $this->shift->discrepancy, 0, ',', '.') : '-', '', '', ''];
        
        $data[] = ['Notes', $this->shift->notes ?? '-', '', '', ''];
        $data[] = ['Status', ucfirst($this->shift->status), '', '', ''];
        $data[] = ['', '', '', '', ''];

        // Data Penjualan
        $data[] = ['Sales Orders', '', '', '', ''];
        $salesOrders = SalesOrder::where('created_by', $this->shift->user_id)
            ->whereBetween('created_at', [$this->shift->start_time, $this->shift->end_time ?? now()])
            ->with('payments')
            ->get();

        if ($salesOrders->isEmpty()) {
            $data[] = ['No sales orders in this shift.', '', '', '', ''];
        } else {
            $data[] = ['Order ID', 'Customer', 'Total', 'Payment Method', 'Payment Amount', 'Status'];
            foreach ($salesOrders as $order) {
                $data[] = [
                    $order->id,
                    $order->customer->name ?? '-',
                    'Rp ' . number_format($order->total, 0, ',', '.'),
                    $order->payments->first()->method ?? '-',
                    'Rp ' . number_format($order->payments->sum('amount'), 0, ',', '.'),
                    ucfirst($order->status),
                ];
            }
        }

        $data[] = ['', '', '', '', ''];

        // Data Pengeluaran
        $data[] = ['Expenses', '', '', '', ''];
        $expenses = Expense::where('shift_id', $this->shift->id)->get();

        if ($expenses->isEmpty()) {
            $data[] = ['No expenses in this shift.', '', '', '', ''];
        } else {
            $data[] = ['Description', 'Amount', 'Date', '', ''];
            foreach ($expenses as $expense) {
                $data[] = [
                    $expense->description,
'Rp ' . number_format((float) $expense->amount, 0, ',', '.'),

                    \Carbon\Carbon::parse($expense->created_at)->format('d/m/Y H:i'),
                    '',
                    '',
                ];
            }
        }

        return collect($data);
    }

    public function headings(): array
    {
        return ['Column 1', 'Column 2', 'Column 3', 'Column 4', 'Column 5'];
    }

    public function title(): string
    {
        return 'Shift Detail ' . $this->shift->id;
    }
}