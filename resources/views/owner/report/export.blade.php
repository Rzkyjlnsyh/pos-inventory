<table>
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Transaksi Tunai</th>
            <th>Transaksi QRIS</th>
            <th>Total Transaksi</th>
            <th>Modal (Rp)</th>
            <th>Pendapatan (Rp)</th>
            <th>Keuntungan (Rp)</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($dailyReports as $report)
        <tr>
            <td>{{ $report['tanggal'] }}</td>
            <td>{{ $report['transaksi_tunai_count'] }}</td>
            <td>{{ $report['transaksi_qris_count'] }}</td>
            <td>{{ $report['total_transaksi'] }}</td>
            <td>{{ $report['modal'] }}</td>
            <td>{{ $report['pendapatan'] }}</td>
            <td>{{ $report['keuntungan'] }}</td>
        </tr>
        @endforeach
        <tr>
            <td><strong>TOTAL</strong></td>
            <td><strong>{{ $totalData['total_transaksi_tunai'] }}</strong></td>
            <td><strong>{{ $totalData['total_transaksi_qris'] }}</strong></td>
            <td><strong>{{ $totalData['total_transaksi'] }}</strong></td>
            <td><strong>{{ $totalData['total_modal'] }}</strong></td>
            <td><strong>{{ $totalData['total_pendapatan'] }}</strong></td>
            <td><strong>{{ $totalData['total_keuntungan'] }}</strong></td>
        </tr>
    </tbody>
</table>
<table>
    <tr>
        <td colspan="7">Laporan Pendapatan: {{ $startDate }} hingga {{ $endDate }}</td>
    </tr>
</table>