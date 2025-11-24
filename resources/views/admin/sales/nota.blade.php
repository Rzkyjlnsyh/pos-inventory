<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nota {{ $salesOrder->so_number }}</title>
    <style>
        body { font-family: 'Courier New', monospace; margin: 0; padding: 10px; font-size: 10px; line-height: 1.3; }
        .header { text-align: center; margin-bottom: 10px; }
        .header h2 { margin: 0; font-size: 14px; font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 8px 0; }
        .info { margin: 5px 0; font-size: 9px; }
        .items { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9px; }
        .items th, .items td { padding: 3px 2px; text-align: left; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .small { font-size: 8px; }
        .qr { text-align: center; margin: 15px 0 5px; }
        .qr img { width: 110px; height: 110px; }
        .footer { text-align: center; margin-top: 10px; font-size: 8px; color: #555; }
        @media print {
            body { margin: 0; padding: 5px; }
            .qr img { image-rendering: pixelated; }
        }
    </style>
</head>
<body>

<div class="header">
    <h2>PARECUSTOM</h2>
    <p class="small">Jl. Pb. Sudirman No.37, Perdana, Pare, Kec. Pare, Kabupaten Kediri, Jawa Timur 64211</p>
</div>

<div class="divider"></div>

<div class="info">
    <div><strong>SO:</strong> {{ $salesOrder->so_number }}</div>
    <div><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($salesOrder->order_date)->format('d/m/Y H:i') }}</div>
    <div><strong>Kasir:</strong> {{ $payment->creator->name ?? 'System' }}</div>
    <div><strong>Customer:</strong> {{ $salesOrder->customer?->name ?? 'Umum' }}</div>
</div>

<div class="divider"></div>

<table class="items">
    <thead>
        <tr>
            <th>Item</th>
            <th class="text-right">Qty</th>
            <th class="text-right">Harga</th>
            <th class="text-right">Disc</th>
            <th class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($salesOrder->items as $item)
        <tr>
            <td>{{ $item->product_name }}@if($item->sku)<br><span class="small">{{ $item->sku }}</span>@endif</td>
            <td class="text-right">{{ $item->qty }}</td>
            <td class="text-right">Rp {{ number_format($item->sale_price, 0, ',', '.') }}</td>
            <td class="text-right">Rp {{ number_format($item->discount, 0, ',', '.') }}</td>
            <td class="text-right bold">Rp {{ number_format($item->line_total, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="divider"></div>

<div class="info text-right">
    <div>Subtotal: <strong>Rp {{ number_format($salesOrder->subtotal, 0, ',', '.') }}</strong></div>
    <div>Diskon: <strong>Rp {{ number_format($salesOrder->discount_total, 0, ',', '.') }}</strong></div>
    <div class="bold" style="font-size: 11px;">Grand Total: Rp {{ number_format($salesOrder->grand_total, 0, ',', '.') }}</div>
</div>

<div class="divider"></div>

<div class="info">
    <div><strong>Pembayaran:</strong></div>
    <div>Tanggal: {{ \Carbon\Carbon::parse($payment->paid_at)->format('d/m/Y H:i') }}</div>
    <div>Metode: <strong>{{ ucfirst($payment->method) }}</strong></div>
    @if($payment->method === 'split')
    <div>Cash: Rp {{ number_format($payment->cash_amount, 0, ',', '.') }}</div>
    <div>Transfer: Rp {{ number_format($payment->transfer_amount, 0, ',', '.') }}</div>
    @endif
    <div>Jumlah: <strong>Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong></div>
    @if($payment->reference_number)
    <div>Ref: {{ $payment->reference_number }}</div>
    @endif
    @if($payment->note)
    <div class="small">Catatan: {{ $payment->note }}</div>
    @endif
</div>

<div class="divider"></div>

<div class="qr">
    <p class="small bold">Scan untuk beri ulasan:</p>
    <img src="{{ qr_code(env('GOOGLE_REVIEW_URL')) }}" alt="QR Review">
</div>

<div class="footer">
    <p>Terima kasih atas kunjungan Anda!</p>
    <p>Follow @parecustom di Instagram</p>
    <p>© {{ date('Y') }} PareCustom</p>
</div>

</body>
</html>