<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Closing Summary - Shift #{{ $shift->id }}</title>
    <style>
        body { 
            font-family: 'DejaVu Sans', Arial, sans-serif; 
            font-size: 12px; 
            margin: 0;
            padding: 15px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .header p {
            margin: 3px 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        .section h2 {
            background-color: #f8f9fa;
            padding: 6px;
            margin: 0 0 8px 0;
            border-left: 4px solid #007bff;
            font-size: 14px;
        }
        .positive { color: #28a745; }
        .negative { color: #dc3545; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary-box {
            background-color: #f8f9fa;
            padding: 12px;
            border-radius: 5px;
            border: 1px solid #ddd;
            margin: 10px 0;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }
        .discrepancy {
            font-weight: bold;
            padding: 8px;
            border-radius: 5px;
            text-align: center;
            margin: 10px 0;
        }
        .discrepancy.positive { background-color: #d4edda; color: #155724; }
        .discrepancy.negative { background-color: #f8d7da; color: #721c24; }
        .discrepancy.neutral { background-color: #e2e3e5; color: #383d41; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CLOSING SUMMARY SHIFT</h1>
        <p>Pare Custom - {{ date('d/m/Y H:i') }}</p>
        <p>Shift #{{ $shift->id }} | Dicetak otomatis saat tutup shift</p>
    </div>

    <!-- Informasi Shift -->
    <div class="section">
        <h2>📊 INFORMASI SHIFT</h2>
        <table>
            <tr>
                <th width="30%">Kasir</th>
                <td>{{ $shift->user->name }}</td>
            </tr>
            <tr>
                <th>Waktu Mulai</th>
                <td>{{ $shift->start_time->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <th>Waktu Selesai</th>
                <td>{{ $shift->end_time->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <th>Durasi</th>
                <td>{{ $shift->start_time->diff($shift->end_time)->format('%h jam %i menit') }}</td>
            </tr>
        </table>
    </div>

    <!-- Summary Kas -->
    <div class="summary-box">
        <h3 style="margin: 0 0 8px 0; color: #333;">🧮 RINGKASAN KAS</h3>
        <table style="border: none; background: none;">
            <tr>
                <td width="40%">Kas Awal</td>
                <td width="10%" class="text-right">Rp</td>
                <td width="20%" class="text-right">{{ number_format($shift->initial_cash, 0, ',', '.') }}</td>
                <td width="30%"></td>
            </tr>
            <tr>
                <td>+ Kas dari Penjualan</td>
                <td class="text-right">Rp</td>
                <td class="text-right positive">+ {{ number_format($shift->cash_total - $shift->income_total, 0, ',', '.') }}</td>
                <td></td>
            </tr>
            <tr>
                <td>+ Pemasukan Manual</td>
                <td class="text-right">Rp</td>
                <td class="text-right positive">+ {{ number_format($shift->income_total, 0, ',', '.') }}</td>
                <td></td>
            </tr>
            <tr>
                <td>- Pengeluaran</td>
                <td class="text-right">Rp</td>
                <td class="text-right negative">- {{ number_format($shift->expense_total, 0, ',', '.') }}</td>
                <td></td>
            </tr>
            <tr style="border-top: 2px solid #333;">
                <td><strong>Total Diharapkan</strong></td>
                <td class="text-right"><strong>Rp</strong></td>
                <td class="text-right"><strong>{{ number_format($shift->initial_cash + $shift->cash_total - $shift->expense_total, 0, ',', '.') }}</strong></td>
                <td></td>
            </tr>
            <tr>
                <td>Kas Aktual (Fisik)</td>
                <td class="text-right">Rp</td>
                <td class="text-right">{{ number_format($shift->final_cash, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </table>

        <!-- Selisih -->
        <div class="discrepancy {{ $shift->discrepancy > 0 ? 'positive' : ($shift->discrepancy < 0 ? 'negative' : 'neutral') }}">
            <strong>SELISIH: Rp {{ number_format(abs($shift->discrepancy), 0, ',', '.') }}</strong>
            <br>
            {{ $shift->discrepancy > 0 ? 'LEBIH' : ($shift->discrepancy < 0 ? 'KURANG' : 'PAS') }}
        </div>
    </div>

    <!-- Statistik -->
    <div class="section">
        <h2>📈 STATISTIK</h2>
        <table>
            <tr>
                <th>Total Penjualan</th>
                <td>{{ $salesOrders->count() }} transaksi</td>
                <td class="text-right">Rp {{ number_format($salesOrders->sum('total'), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Pemasukan Manual</th>
                <td>{{ $incomes->count() }} item</td>
                <td class="text-right positive">Rp {{ number_format($incomes->sum('amount'), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Pengeluaran</th>
                <td>{{ $expenses->count() }} item</td>
                <td class="text-right negative">Rp {{ number_format($expenses->sum('amount'), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Rata-rata Transaksi</th>
                <td colspan="2" class="text-right">
                    Rp {{ number_format($salesOrders->avg('total') ?? 0, 0, ',', '.') }}
                </td>
            </tr>
        </table>
    </div>

    @if($shift->notes)
    <div class="section">
        <h2>📝 CATATAN</h2>
        <div style="padding: 10px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #ffc107;">
            {{ $shift->notes }}
        </div>
    </div>
    @endif

    <!-- Tanda Tangan -->
    <div style="margin-top: 30px;">
        <table style="border: none;">
            <tr>
                <td width="50%" style="border: none; text-align: center;">
                    <div style="border-bottom: 1px solid #000; width: 200px; margin: 0 auto; padding-bottom: 5px;"></div>
                    <p style="margin-top: 5px; font-size: 11px;">Kasir</p>
                </td>
                <td width="50%" style="border: none; text-align: center;">
                    <div style="border-bottom: 1px solid #000; width: 200px; margin: 0 auto; padding-bottom: 5px;"></div>
                    <p style="margin-top: 5px; font-size: 11px;">Supervisor</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Dicetak otomatis pada: {{ date('d/m/Y H:i:s') }}</p>
        <p>© {{ date('Y') }} Pare Custom - Sistem Manajemen Shift</p>
    </div>
</body>
</html>