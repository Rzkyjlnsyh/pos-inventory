<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class DailyIncomeExport implements FromView, WithTitle, WithStyles, ShouldAutoSize
{
    protected $dailyReports;
    protected $totalData;
    protected $startDate;
    protected $endDate;

    public function __construct($dailyReports, $totalData, $startDate, $endDate)
    {
        $this->dailyReports = $dailyReports;
        $this->totalData = $totalData;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function view(): View
    {
        // Konversi nilai ke string dengan format yang diinginkan
        $this->dailyReports = $this->dailyReports->map(function ($report) {
            $report['modal'] = 'Rp ' . number_format($report['modal'], 0, ',', '.');
            $report['pendapatan'] = 'Rp ' . number_format($report['pendapatan'], 0, ',', '.');
            $report['keuntungan'] = 'Rp ' . number_format($report['keuntungan'], 0, ',', '.');
            return $report;
        });

        // Konversi nilai total
        $this->totalData['total_modal'] = 'Rp ' . number_format($this->totalData['total_modal'], 0, ',', '.');
        $this->totalData['total_pendapatan'] = 'Rp ' . number_format($this->totalData['total_pendapatan'], 0, ',', '.');
        $this->totalData['total_keuntungan'] = 'Rp ' . number_format($this->totalData['total_keuntungan'], 0, ',', '.');

        return view('owner.report.export', [
            'dailyReports' => $this->dailyReports,
            'totalData' => $this->totalData,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate
        ]);
    }

    public function title(): string
    {
        return 'Laporan Pendapatan Harian';
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->dailyReports) + 2;

        // Header styles
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
        ]);

        // Border styles
        $sheet->getStyle('A1:G' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Total row styles
        $sheet->getStyle('A' . $lastRow . ':G' . $lastRow)->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2EFDA'],
            ],
        ]);

        // Set columns to text format untuk kolom currency
        $sheet->getStyle('E2:G'.$lastRow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

        // Alignment untuk kolom currency
        $sheet->getStyle('E2:G'.$lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}