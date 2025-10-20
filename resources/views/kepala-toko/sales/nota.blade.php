<!DOCTYPE html>
<html>
<head>
    <title>Nota {{ $salesOrder->so_number }}</title>
    <style>
        @media print {
            .page-break { display: block; page-break-before: always; }
        }
        #invoice-POS {
            box-shadow: 0 0 1in -0.25in rgba(0, 0, 0, 0.5);
            padding: 2mm;
            margin: 0 auto;
            width: 58mm; /* ubah sesuai ukuran kertas printer thermal, bisa 44mm atau 80mm */
            background: #FFF;
            font-family: Arial, sans-serif; 
        }
        #invoice-POS h1 {
            font-size: 1.2em;
            text-align: center;
        }
        #invoice-POS p, #invoice-POS td, #invoice-POS th {
            font-size: 0.7em;
        }
        #invoice-POS table {
            width: 100%;
            border-collapse: collapse;
        }
        #invoice-POS .tabletitle {
            background: #EEE;
            font-size: 0.6em;
        }
        #invoice-POS .itemtext {
            font-size: 0.6em;
        }
        #invoice-POS th, #invoice-POS td {
            padding: 4px;
            border-bottom: 1px solid #EEE;
            text-align: left;
        }
        #invoice-POS #legalcopy {
            margin-top: 5mm;
            text-align: center;
            font-size: 0.6em;
        }
    </style>
</head>
<body>
    <div id="invoice-POS">
        <h1>Pare Custom</h1>
        <p style="text-align:center;">Nota Penjualan</p>
        <p>SO Number: {{ $salesOrder->so_number }}</p>
        <p>Tanggal: {{ \Carbon\Carbon::parse($salesOrder->order_date)->format('d/m/Y') }}</p>
        
        <p><strong>Customer:</strong> {{ $salesOrder->customer->name ?? 'Guest' }}</p>
        @if(isset($payment) && $payment->creator)
            <p><strong>Kasir:</strong> {{ $payment->creator->name }}</p>
        @endif

        <table>
            <tr class="tabletitle">
                <th>Produk</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
            @foreach ($salesOrder->items as $item)
                <tr>
                    <td class="itemtext">{{ $item->product_name }}</td>
                    <td>{{ $item->qty }}</td>
                    <td>Rp {{ number_format($item->sale_price, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->line_total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="tabletitle">
                <td colspan="3"><b>Total</b></td>
                <td><b>Rp {{ number_format($salesOrder->grand_total, 0, ',', '.') }}</b></td>
            </tr>
        </table>

        <p><strong>Pembayaran:</strong> {{ ucfirst($payment->method) }}</p>
        @if($payment->method === 'split')
            <p>Cash: Rp {{ number_format($payment->cash_amount, 0, ',', '.') }}</p>
            <p>Transfer: Rp {{ number_format($payment->transfer_amount, 0, ',', '.') }}</p>
        @endif
        <p>Jumlah: Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
        <p>Status: {{ ucfirst($payment->status) }}</p>

        <div id="legalcopy">
            <p><strong>Terima kasih atas pembelian Anda!</strong><br/>
            Barang yang sudah dibeli tidak dapat dikembalikan.</p>
        </div>
    </div>

    @if(isset($autoPrint) && $autoPrint)
        <script>
            window.onload = function() {
                window.print();
                window.onafterprint = function() {
                    window.location.href = "{{ route('kepala-toko.sales.show', $salesOrder) }}";
                };
            };
        </script>
    @endif
</body>
</html>
