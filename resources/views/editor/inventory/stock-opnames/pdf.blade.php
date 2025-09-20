<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Stock Opname - {{ $stockOpname->document_number }}</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 12px;
            margin: 10mm;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 16px;
            margin: 0;
        }
        .header p {
            font-size: 12px;
            color: #666;
            margin: 5px 0;
        }
        .info-table, .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 5px;
            vertical-align: top;
        }
        .info-table .label {
            font-weight: bold;
            width: 150px;
        }
        .items-table th, .items-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .items-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .items-table .difference-positive {
            color: green;
        }
        .items-table .difference-negative {
            color: red;
        }
        .notes {
            margin-top: 20px;
        }
        .notes h3 {
            font-size: 14px;
            margin-bottom: 5px;
        }
        .notes p {
            background-color: #f9f9f9;
            padding: 10px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Stock Opname</h1>
        <p>Custom Pare - Retail Management</p>
        <p>{{ \Carbon\Carbon::now()->format('d M Y') }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">No. Dokumen:</td>
            <td>{{ $stockOpname->document_number }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal:</td>
            <td>{{ \Carbon\Carbon::parse($stockOpname->date)->format('d M Y') }}</td>
        </tr>
        <tr>
            <td class="label">Status:</td>
            <td>{{ ucfirst($stockOpname->status) }}</td>
        </tr>
        <tr>
            <td class="label">Dibuat oleh:</td>
            <td>
                {{ $stockOpname->creator->name ?? $stockOpname->creator->email ?? '-' }}<br>
                <small>{{ \Carbon\Carbon::parse($stockOpname->created_at)->format('d M Y H:i') }}</small>
            </td>
        </tr>
        @if($stockOpname->status === 'approved')
        <tr>
            <td class="label">Disetujui oleh:</td>
            <td>
                {{ $stockOpname->approver->name ?? $stockOpname->approver->email ?? '-' }}<br>
                <small>{{ \Carbon\Carbon::parse($stockOpname->approved_at)->format('d M Y H:i') }}</small>
            </td>
        </tr>
        @endif
    </table>

    <h3>Daftar Produk</h3>
    <table class="items-table">
        <thead>
            <tr>
                <th>Produk</th>
                <th>Qty Sistem</th>
                <th>Qty Aktual</th>
                <th>Selisih</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stockOpname->items as $item)
            <tr>
                <td>{{ $item->product->name }}</td>
                <td style="text-align: center;">{{ $item->system_qty }}</td>
                <td style="text-align: center;">{{ $item->actual_qty }}</td>
                <td style="text-align: center;" class="{{ $item->difference < 0 ? 'difference-negative' : 'difference-positive' }}">
                    {{ $item->difference }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($stockOpname->notes)
    <div class="notes">
        <h3>Catatan</h3>
        <p>{{ $stockOpname->notes }}</p>
    </div>
    @endif
</body>
</html>