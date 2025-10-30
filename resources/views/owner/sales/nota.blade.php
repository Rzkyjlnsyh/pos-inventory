<!DOCTYPE html>
<html>
<head>
    <title>Nota {{ $salesOrder->so_number }}</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            width: 58mm;
            margin: 0;
            padding: 5px;
            font-size: 12px;
            line-height: 1.3;
        }
        .center { text-align: center; }
        .right { text-align: right; }
        .divider { border-top: 1px dashed #000; margin: 5px 0; }
        .bold { font-weight: bold; }
        @media print {
            body { margin: 0; padding: 5px; }
        }
    </style>
</head>
<body>
    <div class="center bold">PARE CUSTOM</div>
    <div class="center">NOTA PEMBAYARAN</div>
    <div class="divider"></div>
    <div>SO: {{ $salesOrder->so_number }}</div>
    <div>Tgl: {{ \Carbon\Carbon::parse($salesOrder->order_date)->format('d/m/Y') }}</div>
    <div>Cust: {{ $salesOrder->customer->name ?? 'Umum' }}</div>
    @if(isset($payment) && $payment->creator)
        <div>Kasir: {{ $payment->creator->name }}</div>
    @endif
    <div class="divider"></div>
    @foreach ($salesOrder->items as $item)
        <div>{{ str_pad(substr($item->product_name, 0, 20), 20) }} {{ str_pad($item->qty, 2, ' ', STR_PAD_LEFT) }}x{{ str_pad(number_format($item->sale_price, 0, ',', '.'), 10, ' ', STR_PAD_LEFT) }}</div>
    @endforeach
    <div class="divider"></div>
    <div class="right">TOTAL: {{ str_pad('Rp ' . number_format($salesOrder->grand_total, 0, ',', '.'), 24, ' ', STR_PAD_LEFT) }}</div>
    <div class="right">BAYAR: {{ str_pad('Rp ' . number_format($payment->amount, 0, ',', '.'), 24, ' ', STR_PAD_LEFT) }}</div>
    <div class="right">SISA: {{ str_pad('Rp ' . number_format($salesOrder->remaining_amount, 0, ',', '.'), 24, ' ', STR_PAD_LEFT) }}</div>
    <div class="divider"></div>
    <div class="center">Metode: {{ ucfirst($payment->method) }}</div>
    @if($payment->method === 'split')
        <div class="center">Cash: {{ number_format($payment->cash_amount, 0, ',', '.') }}</div>
        <div class="center">Transfer: {{ number_format($payment->transfer_amount, 0, ',', '.') }}</div>
    @endif
    <div class="divider"></div>
    <div class="center">Terima kasih!</div>
    <div class="center">*** {{ now()->format('d/m/Y H:i') }} ***</div>
    @if(isset($autoPrint) && $autoPrint)
        <script>
            window.onload = function() {
                window.print();
                window.onafterprint = function() {
                    window.location.href = "{{ route('owner.sales.show', $salesOrder) }}";
                };
            };
        </script>
    @endif
</body>
</html>