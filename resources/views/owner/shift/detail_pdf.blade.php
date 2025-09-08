<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Shift Detail - {{ $shift->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        h1, h2 { text-align: center; }
        .section { margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>Shift Detail - {{ $shift->id }}</h1>
    <div class="section">
        <h2>Informasi Shift</h2>
        <p><strong>User:</strong> {{ $shift->user->name }}</p>
        <p><strong>Start Time:</strong> {{ \Carbon\Carbon::parse($shift->start_time)->format('d/m/Y H:i') }}</p>
        <p><strong>End Time:</strong> {{ $shift->end_time ? \Carbon\Carbon::parse($shift->end_time)->format('d/m/Y H:i') : '-' }}</p>
        <p><strong>Initial Cash:</strong> Rp {{ number_format($shift->initial_cash, 0, ',', '.') }}</p>
        <p><strong>Cash Total:</strong> Rp {{ number_format($shift->cash_total, 0, ',', '.') }}</p>
        <p><strong>Expense Total:</strong> Rp {{ number_format($shift->expense_total, 0, ',', '.') }}</p>
        <p><strong>Final Cash:</strong> {{ $shift->final_cash ? 'Rp ' . number_format($shift->final_cash, 0, ',', '.') : '-' }}</p>
        <p><strong>Discrepancy:</strong> {{ $shift->discrepancy ? 'Rp ' . number_format($shift->discrepancy, 0, ',', '.') : '-' }}</p>
        <p><strong>Notes:</strong> {{ $shift->notes ?? '-' }}</p>
        <p><strong>Status:</strong> {{ ucfirst($shift->status) }}</p>
    </div>

    <div class="section">
        <h2>Penjualan</h2>
        @if($salesOrders->isEmpty())
            <p>Tidak ada penjualan pada shift ini.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Payment Method</th>
                        <th>Payment Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salesOrders as $order)
                        <tr>
                            <td>{{ $order->id }}</td>
                            <td>{{ $order->customer->name ?? '-' }}</td>
                            <td>Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                            <td>{{ $order->payments->first()->method ?? '-' }}</td>
                            <td>Rp {{ number_format($order->payments->sum('amount'), 0, ',', '.') }}</td>
                            <td>{{ ucfirst($order->status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="section">
        <h2>Pengeluaran</h2>
        @if($expenses->isEmpty())
            <p>Tidak ada pengeluaran pada shift ini.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expenses as $expense)
                        <tr>
                            <td>{{ $expense->description }}</td>
                            <td>Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                            <td>{{ \Carbon\Carbon::parse($expense->created_at)->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</body>
</html>