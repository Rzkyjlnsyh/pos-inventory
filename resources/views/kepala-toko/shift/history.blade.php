<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Monitor Shift - Kepala Toko</title>
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
                <h1 class="text-2xl font-semibold text-gray-800 mb-4">🔍 Monitor Semua Shift</h1>
                
                <!-- Action Buttons -->
                <div class="flex flex-wrap gap-4 mb-6">
                    <a href="{{ route('kepala-toko.shift.dashboard') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded shadow inline-flex items-center">
                        <i class="bi bi-arrow-left mr-2"></i> Kembali
                    </a>
                    <a href="{{ route('kepala-toko.shift.export') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded shadow inline-flex items-center">
                        <i class="bi bi-file-earmark-excel mr-2"></i> Export Semua
                    </a>
                </div>

                <!-- Shift Table -->
                <div class="overflow-x-auto">
                    <table class="w-full table-auto border-collapse">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="px-4 py-3">Kasir</th>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Kas Awal</th>
                                <th class="px-4 py-3">Kas Masuk</th>
                                <th class="px-4 py-3">Pengeluaran</th>
                                <th class="px-4 py-3">Kas Akhir</th>
                                <th class="px-4 py-3">Selisih</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($shifts as $shift)
                                <tr class="hover:bg-gray-50 border-b">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-2">
                                                <i class="bi bi-person text-blue-600"></i>
                                            </div>
                                            {{ $shift->user->name }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">{{ $shift->start_time->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-right">Rp {{ number_format($shift->initial_cash, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right">Rp {{ number_format($shift->cash_total, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right">Rp {{ number_format($shift->expense_total, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right">
                                        @if($shift->final_cash)
                                            Rp {{ number_format($shift->final_cash, 0, ',', '.') }}
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        @if($shift->discrepancy)
                                            <span class="{{ $shift->discrepancy < 0 ? 'text-red-600 font-semibold' : 'text-green-600' }}">
                                                Rp {{ number_format($shift->discrepancy, 0, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded-full text-xs 
                                            {{ $shift->status == 'open' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                            {{ ucfirst($shift->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('kepala-toko.shift.show', $shift) }}" 
                                               class="text-blue-600 hover:text-blue-800" title="Lihat Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('kepala-toko.shift.export-detail', $shift) }}" 
                                               class="text-green-600 hover:text-green-800" title="Export Excel">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $shifts->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>