<?php

namespace App\Exports;

use App\Models\Shift;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ShiftHistoryExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Shift::with('user')->get()->map(function ($shift) {
            return [
                'User' => $shift->user->name,
                'Start Time' => \Carbon\Carbon::parse($shift->start_time)->format('d/m/Y H:i'),
                'End Time' => $shift->end_time ? \Carbon\Carbon::parse($shift->end_time)->format('d/m/Y H:i') : '-',
                'Initial Cash' => 'Rp ' . number_format((float) $shift->initial_cash, 0, ',', '.'),
                'Cash Total' => 'Rp ' . number_format((float) $shift->cash_total, 0, ',', '.'),
                'Expense Total' => 'Rp ' . number_format((float) $shift->expense_total, 0, ',', '.'),
                'Final Cash' => $shift->final_cash !== null ? 'Rp ' . number_format((float) $shift->final_cash, 0, ',', '.') : '-',
                'Discrepancy' => $shift->discrepancy !== null ? 'Rp ' . number_format((float) $shift->discrepancy, 0, ',', '.') : '-',
                'Notes' => $shift->notes ?? '-',
                'Status' => ucfirst($shift->status),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'User',
            'Start Time',
            'End Time',
            'Initial Cash',
            'Cash Total',
            'Expense Total',
            'Final Cash',
            'Discrepancy',
            'Notes',
            'Status',
        ];
    }
}