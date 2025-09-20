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
            $kasPenjualan = $shift->cash_total - $shift->income_total;
            
            return [
                'Kasir' => $shift->user->name,
                'Tanggal' => $shift->start_time->format('d/m/Y'),
                'Waktu Mulai' => $shift->start_time->format('H:i'),
                'Waktu Selesai' => $shift->end_time ? $shift->end_time->format('H:i') : '-',
                'Kas Awal' => $shift->initial_cash,
                'Kas dari Penjualan' => $kasPenjualan,
                'Pemasukan Manual' => $shift->income_total,
                'Total Kas Masuk' => $shift->cash_total,
                'Total Pengeluaran' => $shift->expense_total,
                'Kas Diharapkan' => $shift->initial_cash + $shift->cash_total - $shift->expense_total,
                'Kas Aktual' => $shift->final_cash ?? 0,
                'Selisih' => $shift->discrepancy ?? 0,
                'Status' => ucfirst($shift->status),
                'Catatan' => $shift->notes ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Kasir',
            'Tanggal',
            'Waktu Mulai',
            'Waktu Selesai',
            'Kas Awal (Rp)',
            'Kas dari Penjualan (Rp)',
            'Pemasukan Manual (Rp)',
            'Total Kas Masuk (Rp)',
            'Total Pengeluaran (Rp)',
            'Kas Diharapkan (Rp)',
            'Kas Aktual (Rp)',
            'Selisih (Rp)',
            'Status',
            'Catatan',
        ];
    }
}