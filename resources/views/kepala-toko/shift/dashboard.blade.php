<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard Shift - Pare Custom</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" />
</head>
<body class="bg-gray-100">
<div class="flex">

    <x-navbar-kepala-toko />
    
    <div class="flex-1 lg:w-5/6">
        
        <x-navbar-top-kepala-toko /> 
        
        <div class="p-4 lg:p-8">
            <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
                <h1 class="text-2xl font-semibold text-gray-800 mb-4">Dashboard Shift</h1>

                <!-- Notifikasi Shift Aktif -->
@php
    $activeShift = \App\Models\Shift::whereNull('end_time')->first();
@endphp

@if($activeShift && !$shift)
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
        <div class="flex items-center">
            <i class="bi bi-exclamation-triangle mr-2"></i>
            <strong>Shift sedang berjalan!</strong>
        </div>
        <p class="mt-1 text-sm">
            Shift aktif sedang berjalan oleh <strong>{{ $activeShift->user->name }}</strong> 
            sejak {{ $activeShift->start_time->format('H:i') }}.
            Tunggu hingga shift selesai untuk mulai shift baru.
        </p>
    </div>
@endif

@if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
        <p>{{ session('error') }}</p>
    </div>
@endif
                
                <div class="flex flex-wrap gap-4 mb-4">
                    <a href="{{ route('kepala-toko.shift.history') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded shadow inline-flex items-center">
                        <i class="bi bi-clock-history mr-2"></i> Lihat Riwayat
                    </a>
                </div>

                @if($shift)
                    <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                        <p class="text-sm text-green-800">
                            <strong>Shift Aktif</strong> - Dimulai pada {{ \Carbon\Carbon::parse($shift->start_time)->format('d/m/Y H:i') }}
                        </p>
                        <p class="text-sm text-green-800">Kas Awal: Rp {{ number_format($shift->initial_cash, 0, ',', '.') }}</p>
                    </div>
                @else
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                        <p class="text-sm text-red-800">
                            <i class="bi bi-exclamation-triangle"></i> Shift belum dimulai. 
                            Silakan mulai shift untuk mengoperasikan penjualan.
                        </p>
                    </div>
                @endif

                <!-- Grid Stats -->
                <div class="grid md:grid-cols-2 gap-6 mt-6">
                    <!-- Kolom Kiri - Kas Masuk -->
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h2 class="text-lg font-semibold mb-4 text-green-800">💰 Kas Masuk</h2>
                        
                        <table class="w-full table-auto text-sm">
                            <tbody>
                                <tr class="border-b">
                                    <td class="px-3 py-1">Cash Lunas</td>
                                    <td class="px-3 py-1 text-right">Rp {{ number_format($cashLunas, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="px-3 py-1">Cash DP</td>
                                    <td class="px-3 py-1 text-right">Rp {{ number_format($cashDp, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="px-3 py-1">Cash Pelunasan</td>
                                    <td class="px-3 py-1 text-right">Rp {{ number_format($cashPelunasan, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="px-3 py-1">Transfer Lunas</td>
                                    <td class="px-3 py-1 text-right">Rp {{ number_format($transferLunas, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="px-3 py-1">Transfer DP</td>
                                    <td class="px-3 py-1 text-right">Rp {{ number_format($transferDp, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="px-3 py-1">Transfer Pelunasan</td>
                                    <td class="px-3 py-1 text-right">Rp {{ number_format($transferPelunasan, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="px-3 py-1 font-medium">Pemasukan Manual</td>
                                    <td class="px-3 py-1 text-right text-blue-600">Rp {{ number_format($pemasukanManual, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="bg-green-100 font-semibold">
                                    <td class="px-3 py-1">Total Kas Masuk</td>
                                    <td class="px-3 py-1 text-right">Rp {{ number_format($cashLunas + $cashDp + $cashPelunasan + $transferLunas + $transferDp + $transferPelunasan + $pemasukanManual, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Kolom Kanan - Kas Keluar & Summary -->
                    <div class="bg-red-50 p-4 rounded-lg">
                        <h2 class="text-lg font-semibold mb-4 text-red-800">💸 Kas Keluar & Summary</h2>
                        <table class="w-full table-auto text-sm">
                            <tbody>
                                <tr class="border-b">
                                    <td class="px-3 py-2 font-medium">Pengeluaran</td>
                                    <td class="px-3 py-2 text-right">Rp {{ number_format($pengeluaran, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="px-3 py-2 font-medium">Awal Laci</td>
                                    <td class="px-3 py-2 text-right">Rp {{ number_format($awalLaci, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="border-b bg-blue-50">
                                    <td class="px-3 py-2 font-semibold">Tunai di Laci</td>
                                    <td class="px-3 py-2 text-right font-semibold">Rp {{ number_format($tunaiDiLaci, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="bg-blue-100 font-semibold">
                                    <td class="px-3 py-2">Total Diharapkan</td>
                                    <td class="px-3 py-2 text-right">Rp {{ number_format($totalDiharapkan, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Form Input Pemasukan -->
                @if($shift)
                <div class="mt-6 bg-yellow-50 p-4 rounded-lg">
                    <h2 class="text-lg font-semibold mb-3 text-yellow-800">➕ Input Pemasukan Manual</h2>
                    <form action="{{ route('kepala-toko.shift.income') }}" method="POST">
                        <!-- UNTUK KEPALA TOKO: action="{{ route('kepalatoko.shift.income') }}" -->
                        @csrf
                        <div class="grid md:grid-cols-3 gap-4 mb-3">
                            <div>
                                <label for="income_amount" class="block font-medium mb-1">Jumlah Pemasukan *</label>
                                <input type="number" name="income_amount" id="income_amount" min="0" step="0.01" required 
                                       class="border rounded px-3 py-2 w-full focus:ring focus:ring-yellow-300">
                            </div>
                            <div>
                                <label for="income_description" class="block font-medium mb-1">Keterangan *</label>
                                <input type="text" name="income_description" id="income_description" required 
                                       class="border rounded px-3 py-2 w-full focus:ring focus:ring-yellow-300" 
                                       placeholder="Contoh: Setoran modal, dll">
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-2 rounded shadow w-full">
                                    <i class="bi bi-plus-circle"></i> Tambah Pemasukan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                @endif

                <!-- Form Input Pengeluaran -->
                @if($shift)
                <div class="mt-4 bg-red-50 p-4 rounded-lg">
                    <h2 class="text-lg font-semibold mb-3 text-red-800">➖ Input Pengeluaran</h2>
                    <form action="{{ route('kepala-toko.shift.expense') }}" method="POST">
                        <!-- UNTUK KEPALA TOKO: action="{{ route('kepalatoko.shift.expense') }}" -->
                        @csrf
                        <div class="grid md:grid-cols-3 gap-4 mb-3">
                            <div>
                                <label for="expense_amount" class="block font-medium mb-1">Jumlah Pengeluaran *</label>
                                <input type="number" name="expense_amount" id="expense_amount" min="0" step="0.01" required 
                                       class="border rounded px-3 py-2 w-full focus:ring focus:ring-red-300">
                            </div>
                            <div>
                                <label for="expense_description" class="block font-medium mb-1">Keterangan *</label>
                                <input type="text" name="expense_description" id="expense_description" required 
                                       class="border rounded px-3 py-2 w-full focus:ring focus:ring-red-300" 
                                       placeholder="Contoh: Beli plastik, dll">
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded shadow w-full">
                                    <i class="bi bi-dash-circle"></i> Tambah Pengeluaran
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                @endif

                <!-- Form Tutup Shift -->
                <div class="mt-6">
                    @if($shift)
                        <form action="{{ route('kepala-toko.shift.end') }}" method="POST">
                            <!-- UNTUK KEPALA TOKO: action="{{ route('kepalatoko.shift.end') }}" -->
                            @csrf
                            <div class="grid md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="final_cash" class="block font-medium mb-1">Kas Aktual di Laci *</label>
                                    <input type="number" name="final_cash" id="final_cash" required 
                                           class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300"
                                           placeholder="Jumlah uang fisik di laci">
                                </div>
                                <div>
                                    <label for="notes" class="block font-medium mb-1">Catatan Penutupan</label>
                                    <textarea name="notes" id="notes" rows="2"
                                              class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300"
                                              placeholder="Catatan khusus shift ini"></textarea>
                                </div>
                            </div>
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded shadow">
                                <i class="bi bi-lock-fill"></i> Tutup Shift
                            </button>
                        </form>
                    @else
                        <form action="{{ route('kepala-toko.shift.start') }}" method="POST">
                            @csrf
                            <div class="max-w-md">
                                <label for="initial_cash" class="block font-medium mb-1">Kas Awal di Laci *</label>
                                <input type="number" name="initial_cash" id="initial_cash" required 
                                       class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300"
                                       placeholder="Jumlah uang awal di laci">
                            </div>
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded shadow mt-3">
                                <i class="bi bi-play-fill"></i> Mulai Shift Baru
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>