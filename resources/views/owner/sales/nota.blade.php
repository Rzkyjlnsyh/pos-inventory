<!DOCTYPE html>
<html>
<head>
    <title>Nota {{ $salesOrder->so_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .details { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
        .payment-details { margin-bottom: 20px; }
        .footer { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Pare Custom - Nota Penjualan</h1>
        <p>SO Number: {{ $salesOrder->so_number }}</p>
        <p>Tanggal: {{ \Carbon\Carbon::parse($salesOrder->order_date)->format('d/m/Y') }}</p>
    </div>

    <div class="details">
        <h3>Detail Customer</h3>
        <p>Nama: {{ $salesOrder->customer->name ?? 'Guest' }}</p>
    </div>
    @if(isset($payment) && $payment->creator)
<div class="flex justify-between text-sm">
    <span> Kasir :</span>
    <span>{{ $payment->creator->name }}</span>
</div>
@endif

    <h3>Item Order</h3>
    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Diskon</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($salesOrder->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->qty }}</td>
                    <td>Rp {{ number_format($item->sale_price, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->discount, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->line_total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4">Grand Total</td>
                <td>Rp {{ number_format($salesOrder->grand_total, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="payment-details">
        <h3>Detail Pembayaran</h3>
        <p>Metode: {{ ucfirst($payment->method) }}</p>
        @if($payment->method === 'split')
            <p>Pembayaran Cash: Rp {{ number_format($payment->cash_amount, 0, ',', '.') }}</p>
            <p>Pembayaran Transfer: Rp {{ number_format($payment->transfer_amount, 0, ',', '.') }}</p>
        @endif
        <p>Jumlah: Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
        <p>Status: {{ ucfirst($payment->status) }}</p>
        <p>Tanggal: {{ \Carbon\Carbon::parse($payment->paid_at)->format('d/m/Y H:i') }}</p>
    </div>

    <div class="footer">
        <p>Terima kasih atas pembelian Anda!</p>
    </div>

    @if(isset($autoPrint) && $autoPrint)
        <script>
            window.onload = function() {
                window.print();
                // Redirect kembali ke show setelah print dialog ditutup
                window.onafterprint = function() {
                    window.location.href = "{{ route('owner.sales.show', $salesOrder) }}";
                };
            };
        </script>
    @endif
</body>
</html>