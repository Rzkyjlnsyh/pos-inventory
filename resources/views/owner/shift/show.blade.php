<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Detail Shift - Pare Custom</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" />
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Raleway', sans-serif; }
        .nav-text {
            position: relative;
            display: inline-block;
        }
        .nav-text::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: #e17f12;
            transition: width 0.2s ease-in-out;
        }
        .hover-link:hover .nav-text::after {
            width: 100%;
        }
    </style>
</head>
<body class="bg-gray-100">
<div class="flex">
    <x-navbar-owner />
    <div class="flex-1 lg:w-5/6">
        <x-navbar-top-owner />
        <div class="p-4 lg:p-8">
            <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
                <h1 class="text-2xl font-semibold text-gray-800 mb-4">Detail Shift</h1>
                <div class="mb-4">
                    <a href="{{ route('owner.shift.history') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded shadow inline-block">
                        <i class="bi bi-arrow-left"></i> Kembali ke Riwayat
                    </a>
                    <a href="{{ route('owner.shift.export-detail', $shift) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded shadow inline-block ml-2">
                        <i class="bi bi-download"></i> Export ke Excel
                    </a>
                    <a href="{{ route('owner.shift.export-detail-pdf', $shift) }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded shadow inline-block ml-2">
                        <i class="bi bi-file-earmark-pdf"></i> Export ke PDF
                    </a>
                </div>
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-2">Informasi Shift</h2>
                    <p><strong>Pengguna:</strong> {{ $shift->user->name }}</p>
                    <p><strong>Waktu Mulai:</strong> {{ \Carbon\Carbon::parse($shift->start_time)->format('d/m/Y H:i') }}</p>
                    <p><strong>Waktu Selesai:</strong> {{ $shift->end_time ? \Carbon\Carbon::parse($shift->end_time)->format('d/m/Y H:i') : '-' }}</p>
                    <p><strong>Kas Awal:</strong> Rp {{ number_format($shift->initial_cash, 0, ',', '.') }}</p>
                    <p><strong>Total Kas:</strong> Rp {{ number_format($shift->cash_total, 0, ',', '.') }}</p>
                    <p><strong>Total Pengeluaran:</strong> Rp {{ number_format($shift->expense_total, 0, ',', '.') }}</p>
                    <p><strong>Kas Akhir:</strong> {{ $shift->final_cash ? 'Rp ' . number_format($shift->final_cash, 0, ',', '.') : '-' }}</p>
                    <p><strong>Selisih:</strong> {{ $shift->discrepancy ? 'Rp ' . number_format($shift->discrepancy, 0, ',', '.') : '-' }}</p>
                    <p><strong>Catatan:</strong> {{ $shift->notes ?? '-' }}</p>
                    <p><strong>Status:</strong> {{ ucfirst($shift->status) }}</p>
                </div>

                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-2">Penjualan</h2>
                    @if($salesOrders->isEmpty())
                        <p class="text-gray-500">Tidak ada penjualan pada shift ini.</p>
                    @else
                        <table class="w-full table-auto border-collapse">
                            <thead>
                                <tr class="bg-gray-200">
                                    <th class="px-4 py-2">ID Order</th>
                                    <th class="px-4 py-2">Pelanggan</th>
                                    <th class="px-4 py-2">Total</th>
                                    <th class="px-4 py-2">Metode Pembayaran</th>
                                    <th class="px-4 py-2">Jumlah Pembayaran</th>
                                    <th class="px-4 py-2">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($salesOrders as $order)
                                    <tr>
                                        <td class="border px-4 py-2">{{ $order->id }}</td>
                                        <td class="border px-4 py-2">{{ $order->customer->name ?? '-' }}</td>
                                        <td class="border px-4 py-2">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                                        <td class="border px-4 py-2">{{ $order->payments->first()->method ?? '-' }}</td>
                                        <td class="border px-4 py-2">Rp {{ number_format($order->payments->sum('amount'), 0, ',', '.') }}</td>
                                        <td class="border px-4 py-2">{{ ucfirst($order->status) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-2">Pengeluaran</h2>
                    @if($expenses->isEmpty())
                        <p class="text-gray-500">Tidak ada pengeluaran pada shift ini.</p>
                    @else
                        <table class="w-full table-auto border-collapse">
                            <thead>
                                <tr class="bg-gray-200">
                                    <th class="px-4 py-2">Deskripsi</th>
                                    <th class="px-4 py-2">Jumlah</th>
                                    <th class="px-4 py-2">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expenses as $expense)
                                    <tr>
                                        <td class="border px-4 py-2">{{ $expense->description }}</td>
                                        <td class="border px-4 py-2">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                                        <td class="border px-4 py-2">{{ \Carbon\Carbon::parse($expense->created_at)->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function toggleSidebar() { 
        document.getElementById('sidebar').classList.toggle('-translate-x-full'); 
    }
    function toggleDropdown(button) {
        const dropdown = button.nextElementSibling;
        const chevron = button.querySelector('.bi-chevron-down');
        dropdown.classList.toggle('max-h-0');
        dropdown.classList.toggle('max-h-40');
        chevron.classList.toggle('rotate-180');
    }
</script>
</body>
</html>
