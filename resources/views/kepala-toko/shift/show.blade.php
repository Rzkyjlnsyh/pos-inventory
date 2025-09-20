<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Detail Shift - Kepala Toko</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" />
</head>
<body class="bg-gray-100">
<div class="flex">
    <x-navbar-kepala-toko />
    <div class="flex-1 lg:w-5/6">
        <x-navbar-top-kepala-toko />
        <div class="p-4 lg:p-8">
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h1 class="text-2xl font-semibold text-gray-800 mb-4">Detail Shift #{{ $shift->id }}</h1>
                
                <div class="flex flex-wrap gap-4 mb-6">
                    <a href="{{ route('kepala-toko.shift.history') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded shadow inline-flex items-center">
                        <i class="bi bi-arrow-left mr-2"></i> Kembali
                    </a>
                    <a href="{{ route('kepala-toko.shift.export-detail', $shift) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded shadow inline-flex items-center">
                        <i class="bi bi-file-earmark-excel mr-2"></i> Export Excel
                    </a>
                </div>

                <!-- Informasi Shift -->
                <div class="bg-gray-50 p-6 rounded-lg mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">📊 Informasi Shift</h2>
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Kasir</p>
                            <p class="font-medium">{{ $shift->user->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Waktu Mulai</p>
                            <p class="font-medium">{{ $shift->start_time->format('d/m/Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Waktu Selesai</p>
                            <p class="font-medium">{{ $shift->end_time ? $shift->end_time->format('d/m/Y H:i') : 'Masih Aktif' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Kas Awal</p>
                            <p class="font-medium">Rp {{ number_format($shift->initial_cash, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total Kas</p>
                            <p class="font-medium text-green-600">Rp {{ number_format($shift->cash_total, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Pemasukan Manual</p>
                            <p class="font-medium text-blue-600">Rp {{ number_format($shift->income_total, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Pengeluaran</p>
                            <p class="font-medium text-red-600">Rp {{ number_format($shift->expense_total, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Kas Akhir</p>
                            <p class="font-medium">{{ $shift->final_cash ? 'Rp ' . number_format($shift->final_cash, 0, ',', '.') : '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Selisih</p>
                            <p class="font-medium {{ $shift->discrepancy < 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $shift->discrepancy ? 'Rp ' . number_format($shift->discrepancy, 0, ',', '.') : '-' }}
                            </p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-600">Catatan</p>
                            <p class="font-medium">{{ $shift->notes ?? 'Tidak ada catatan' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status</p>
                            <span class="px-3 py-1 rounded-full text-xs font-medium
                                {{ $shift->status == 'open' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                {{ $shift->status == 'open' ? 'Aktif' : 'Selesai' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Pemasukan Manual -->
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">💰 Pemasukan Manual</h2>
                    @if($incomes->isEmpty())
                        <p class="text-gray-500 bg-gray-50 p-4 rounded-lg">Tidak ada pemasukan manual pada shift ini.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full table-auto border-collapse">
                                <thead>
                                    <tr class="bg-blue-100">
                                        <th class="px-4 py-2 text-left">Keterangan</th>
                                        <th class="px-4 py-2 text-right">Jumlah</th>
                                        <th class="px-4 py-2 text-left">Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($incomes as $income)
                                        <tr class="border-b hover:bg-blue-50">
                                            <td class="px-4 py-2">{{ $income->description }}</td>
                                            <td class="px-4 py-2 text-right text-green-600 font-medium">
                                                Rp {{ number_format($income->amount, 0, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-600">
                                                {{ $income->created_at->format('d/m/Y H:i') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="bg-blue-50 font-semibold">
                                        <td class="px-4 py-2">Total Pemasukan Manual</td>
                                        <td class="px-4 py-2 text-right text-green-600">
                                            Rp {{ number_format($incomes->sum('amount'), 0, ',', '.') }}
                                        </td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- Penjualan -->
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">🛒 Penjualan</h2>
                    @if($salesOrders->isEmpty())
                        <p class="text-gray-500 bg-gray-50 p-4 rounded-lg">Tidak ada penjualan pada shift ini.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full table-auto border-collapse">
                                <thead>
                                    <tr class="bg-green-100">
                                        <th class="px-4 py-2">Order ID</th>
                                        <th class="px-4 py-2">Pelanggan</th>
                                        <th class="px-4 py-2 text-right">Total</th>
                                        <th class="px-4 py-2 text-right">Dibayar</th>
                                        <th class="px-4 py-2">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($salesOrders as $order)
                                        <tr class="border-b hover:bg-green-50">
                                            <td class="px-4 py-2">#{{ $order->id }}</td>
                                            <td class="px-4 py-2">{{ $order->customer->name ?? 'Umum' }}</td>
                                            <td class="px-4 py-2 text-right">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                                            <td class="px-4 py-2 text-right">Rp {{ number_format($order->payments->sum('amount'), 0, ',', '.') }}</td>
                                            <td class="px-4 py-2">
                                                <span class="px-2 py-1 rounded text-xs
                                                    {{ $order->status == 'completed' ? 'bg-green-100 text-green-800' : 
                                                       ($order->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                                    {{ $order->status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- Pengeluaran -->
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">💸 Pengeluaran</h2>
                    @if($expenses->isEmpty())
                        <p class="text-gray-500 bg-gray-50 p-4 rounded-lg">Tidak ada pengeluaran pada shift ini.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full table-auto border-collapse">
                                <thead>
                                    <tr class="bg-red-100">
                                        <th class="px-4 py-2 text-left">Keterangan</th>
                                        <th class="px-4 py-2 text-right">Jumlah</th>
                                        <th class="px-4 py-2 text-left">Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expenses as $expense)
                                        <tr class="border-b hover:bg-red-50">
                                            <td class="px-4 py-2">{{ $expense->description }}</td>
                                            <td class="px-4 py-2 text-right text-red-600 font-medium">
                                                Rp {{ number_format($expense->amount, 0, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-600">
                                                {{ $expense->created_at->format('d/m/Y H:i') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="bg-red-50 font-semibold">
                                        <td class="px-4 py-2">Total Pengeluaran</td>
                                        <td class="px-4 py-2 text-right text-red-600">
                                            Rp {{ number_format($expenses->sum('amount'), 0, ',', '.') }}
                                        </td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>