<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Nota Pembayaran - {{ $salesOrder->so_number }}</title>
<style>
body { font-family: Arial, sans-serif; font-size: 12pt; }
.container { max-width: 800px; margin: 0 auto; }
.header { text-align: center; margin-bottom: 20px; }
.table { width: 100%; border-collapse: collapse; }
.table th, .table td { border: 1px solid #000; padding: 8px; }
.table th { background-color: #f2f2f2; }
</style>
</head>
<body>
<div class="container">
<div class="header">
<h1>Nota Pembayaran</h1>
<h2>Pare Custom</h2>
<p>SO Number: {{ $salesOrder->so_number }}</p>
<p>Tanggal: {{ \Carbon\Carbon::parse($payment->paid_at)->format('d/m/Y H:i') }}</p>
</div>

<h2>Informasi Order</h2>
<p>Customer: {{ $salesOrder->customer->name ?? 'Guest' }}</p>
<p>Grand Total: Rp {{ number_format($salesOrder->grand_total, 0, ',', '.') }}</p>
<p>Jumlah Bayar: Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
<p>Sisa: Rp {{ number_format($salesOrder->remaining_amount, 0, ',', '.') }}</p>
<p>Catatan: {{ $payment->note ?? '-' }}</p>

<h2>Item Order</h2>
<table class="table">
<thead>
<tr>
<th>Produk</th>
<th>Harga</th>
<th>Qty</th>
<th>Subtotal</th>
</tr>
</thead>
<tbody>
@foreach($salesOrder->items as $item)
<tr>
<td>{{ $item->product_name }}</td>
<td>Rp {{ number_format($item->sale_price, 0, ',', '.') }}</td>
<td>{{ $item->qty }}</td>
<td>Rp {{ number_format($item->line_total, 0, ',', '.') }}</td>
</tr>
@endforeach
</tbody>
</table>

<p class="text-center mt-4">Terima kasih telah berbelanja!</p>
</div>
</body>
</html>