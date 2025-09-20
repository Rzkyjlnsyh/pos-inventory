<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Riwayat Shift - {{ date('d/m/Y') }}</title>
    <style>
        body { 
            font-family: 'DejaVu Sans', Arial, sans-serif; 
            font-size: 10px; 
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            page-break-inside: auto;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .positive {
            color: #28a745;
        }
        .negative {
            color: #dc3545;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .summary h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN RIWAYAT SHIFT</h1>
        <p>Pare Custom - {{ date('d/m/Y H:i') }}</p>
        <p>Dicetak oleh: {{ Auth::user()->name }}</p>
    </div>

    @if($shifts->isEmpty())
        <p style="text-align: center; color: #666; font-style: italic;">
            Tidak ada data shift
        </p>
    @else
        <table>
            <thead>
                <tr>
                    <th width="12%">Kasir</th>
                    <th width="8%">Tanggal</th>
                    <th width="6%">Mulai</th>
                    <th width="6%">Selesai</th>
                    <th width="8%">Kas Awal</th>
                    <th width="8%">Kas Penjualan</th>
                    <th width="8%">Pemasukan Manual</th>
                    <th width="8%">Total Kas</th>
                    <th width="8%">Pengeluaran</th>
                    <th width="8%">Kas Diharapkan</th>
                    <th width="8%">Kas Aktual</th>
                    <th width="8%">Selisih</th>
                    <th width="6%">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($shifts as $shift)
                    @php
                        $kasPenjualan = $shift->cash_total - $shift->income_total;
                        $kasDiharapkan = $shift->initial_cash + $shift->cash_total - $shift->expense_total;
                    @endphp
                    <tr>
                        <td>{{ $shift->user->name }}</td>
                        <td class="text-center">{{ $shift->start_time->format('d/m/Y') }}</td>
                        <td class="text-center">{{ $shift->start_time->format('H:i') }}</td>
                        <td class="text-center">{{ $shift->end_time ? $shift->end_time->format('H:i') : '-' }}</td>
                        <td class="text-right">Rp {{ number_format($shift->initial_cash, 0, ',', '.') }}</td>
                        <td class="text-right positive">Rp {{ number_format($kasPenjualan, 0, ',', '.') }}</td>
                        <td class="text-right positive">Rp {{ number_format($shift->income_total, 0, ',', '.') }}</td>
                        <td class="text-right positive">Rp {{ number_format($shift->cash_total, 0, ',', '.') }}</td>
                        <td class="text-right negative">Rp {{ number_format($shift->expense_total, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($kasDiharapkan, 0, ',', '.') }}</td>
                        <td class="text-right">
                            @if($shift->final_cash)
                                Rp {{ number_format($shift->final_cash, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-right {{ $shift->discrepancy < 0 ? 'negative' : 'positive' }}">
                            @if($shift->discrepancy)
                                Rp {{ number_format(abs($shift->discrepancy), 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">
                            <span style="display: inline-block; padding: 2px 6px; border-radius: 10px; font-size: 9px; 
                                {{ $shift->status == 'open' ? 'background-color: #fff3cd; color: #856404;' : 
                                   'background-color: #d4edda; color: #155724;' }}">
                                {{ $shift->status == 'open' ? 'Aktif' : 'Selesai' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary Section -->
        <div class="summary">
            <h3>📊 Ringkasan Total</h3>
            <table style="border: none; background: none;">
                <tr>
                    <td width="30%">Total Kas Awal:</td>
                    <td class="text-right" width="20%"><strong>Rp {{ number_format($shifts->sum('initial_cash'), 0, ',', '.') }}</strong></td>
                    
                    <td width="30%">Total Shift:</td>
                    <td class="text-right" width="20%"><strong>{{ $shifts->count() }}</strong></td>
                </tr>
                <tr>
                    <td>Total Kas dari Penjualan:</td>
                    <td class="text-right positive"><strong>Rp {{ number_format($shifts->sum('cash_total') - $shifts->sum('income_total'), 0, ',', '.') }}</strong></td>
                    
                    <td>Shift Aktif:</td>
                    <td class="text-right"><strong>{{ $shifts->where('status', 'open')->count() }}</strong></td>
                </tr>
                <tr>
                    <td>Total Pemasukan Manual:</td>
                    <td class="text-right positive"><strong>Rp {{ number_format($shifts->sum('income_total'), 0, ',', '.') }}</strong></td>
                    
                    <td>Shift Selesai:</td>
                    <td class="text-right"><strong>{{ $shifts->where('status', 'closed')->count() }}</strong></td>
                </tr>
                <tr>
                    <td>Total Kas Masuk:</td>
                    <td class="text-right positive"><strong>Rp {{ number_format($shifts->sum('cash_total'), 0, ',', '.') }}</strong></td>
                    
                    <td>Total Pengeluaran:</td>
                    <td class="text-right negative"><strong>Rp {{ number_format($shifts->sum('expense_total'), 0, ',', '.') }}</strong></td>
                </tr>
                <tr>
                    <td>Rata-rata Kas per Shift:</td>
                    <td class="text-right"><strong>Rp {{ number_format($shifts->avg('cash_total'), 0, ',', '.') }}</strong></td>
                    
                    <td>Total Selisih:</td>
                    <td class="text-right {{ $shifts->sum('discrepancy') < 0 ? 'negative' : 'positive' }}">
                        <strong>Rp {{ number_format(abs($shifts->sum('discrepancy')), 0, ',', '.') }}</strong>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Notes Section -->
        @if($shifts->where('notes')->isNotEmpty())
        <div class="summary" style="margin-top: 15px;">
            <h3>📝 Catatan Penting</h3>
            @foreach($shifts->where('notes') as $shift)
                @if($shift->notes)
                <div style="margin-bottom: 10px; padding: 8px; background: white; border-radius: 4px; border-left: 4px solid #007bff;">
                    <strong>{{ $shift->user->name }} ({{ $shift->start_time->format('d/m/Y') }}):</strong><br>
                    {{ $shift->notes }}
                </div>
                @endif
            @endforeach
        </div>
        @endif
    @endif

    <div class="footer">
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }} | Halaman 1 of 1</p>
        <p>© {{ date('Y') }} Pare Custom - Sistem Manajemen Shift</p>
    </div>
</body>
</html>