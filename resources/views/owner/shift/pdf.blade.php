<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Riwayat Shift</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        h1 { text-align: center; }
    </style>
</head>
<body>
    <h1>Riwayat Shift</h1>
    <table>
        <thead>
            <tr>
                <th>Pengguna</th>
                <th>Waktu Mulai</th>
                <th>Waktu Selesai</th>
                <th>Kas Awal</th>
                <th>Total Kas</th>
                <th>Total Pengeluaran</th>
                <th>Kas Akhir</th>
                <th>Selisih</th>
                <th>Catatan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($shifts as $shift)
                <tr>
                    <td>{{ $shift->user->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($shift->start_time)->format('d/m/Y H:i') }}</td>
                    <td>{{ $shift->end_time ? \Carbon\Carbon::parse($shift->end_time)->format('d/m/Y H:i') : '-' }}</td>
                    <td>Rp {{ number_format($shift->initial_cash, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($shift->cash_total, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($shift->expense_total, 0, ',', '.') }}</td>
                    <td>{{ $shift->final_cash ? 'Rp ' . number_format($shift->final_cash, 0, ',', '.') : '-' }}</td>
                    <td>{{ $shift->discrepancy ? 'Rp ' . number_format($shift->discrepancy, 0, ',', '.') : '-' }}</td>
                    <td>{{ $shift->notes ?? '-' }}</td>
                    <td>{{ ucfirst($shift->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
