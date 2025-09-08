<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard Shift - Pare Custom</title>
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
                <h1 class="text-2xl font-semibold text-gray-800 mb-4">Dashboard Shift</h1>
                <a href="{{ route('owner.shift.history') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded shadow mb-4 inline-block">
                    <i class="bi bi-clock-history"></i> Lihat Riwayat Shift
                </a>
                @if($shift)
                    <p class="text-sm text-gray-500">Shift dimulai pada {{ \Carbon\Carbon::parse($shift->start_time)->format('d/m/Y H:i') }} oleh {{ Auth::user()->name }}</p>
                    <p class="text-sm text-gray-500">Kas Awal: Rp {{ number_format($shift->initial_cash, 0, ',', '.') }}</p>
                @else
                    <p class="text-sm text-red-500">Shift belum dimulai. Silakan mulai shift untuk mengoperasikan penjualan.</p>
                @endif

                <div class="grid md:grid-cols-2 gap-6 mt-6">
                    <!-- Kolom Kiri - Kas Masuk -->
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h2 class="text-lg font-semibold mb-4 text-green-800">Kas Masuk</h2>
                        <table class="w-full table-auto">
                            <tbody>
                                <tr class="border-b">
                                    <td class="px-4 py-2 font-medium">Cash Lunas</td>
                                    <td class="px-4 py-2 text-right">Rp {{ number_format($cashLunas, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="px-4 py-2 font-medium">Cash DP</td>
                                    <td class="px-4 py-2 text-right">Rp {{ number_format($cashDp, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="px-4 py-2 font-medium">Cash Pelunasan</td>
                                    <td class="px-4 py-2 text-right">Rp {{ number_format($cashPelunasan, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="px-4 py-2 font-medium">Transfer Lunas</td>
                                    <td class="px-4 py-2 text-right">Rp {{ number_format($transferLunas, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="px-4 py-2 font-medium">Transfer DP</td>
                                    <td class="px-4 py-2 text-right">Rp {{ number_format($transferDp, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="px-4 py-2 font-medium">Transfer Pelunasan</td>
                                    <td class="px-4 py-2 text-right">Rp {{ number_format($transferPelunasan, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="bg-green-100 font-semibold">
                                    <td class="px-4 py-2">Total Kas Masuk</td>
                                    <td class="px-4 py-2 text-right">Rp {{ number_format($cashLunas + $cashDp + $cashPelunasan + $transferLunas + $transferDp + $transferPelunasan, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Kolom Kanan - Kas Keluar & Summary -->
                    <div class="bg-red-50 p-4 rounded-lg">
                        <h2 class="text-lg font-semibold mb-4 text-red-800">Kas Keluar & Summary</h2>
                        <table class="w-full table-auto">
                            <tbody>
                                <tr class="border-b">
                                    <td class="px-4 py-2 font-medium">Pengeluaran</td>
                                    <td class="px-4 py-2 text-right">Rp {{ number_format($pengeluaran, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="px-4 py-2 font-medium">Awal Laci</td>
                                    <td class="px-4 py-2 text-right">Rp {{ number_format($awalLaci, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="px-4 py-2 font-medium">Tunai di Laci</td>
                                    <td class="px-4 py-2 text-right">Rp {{ number_format($tunaiDiLaci, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="bg-blue-100 font-semibold">
                                    <td class="px-4 py-2">Total Diharapkan</td>
                                    <td class="px-4 py-2 text-right">Rp {{ number_format($totalDiharapkan, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6">
                    @if($shift)
                        <form action="{{ route('owner.shift.end') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label for="final_cash" class="block font-medium mb-1">Kas Aktual di Laci</label>
                                <input type="number" name="final_cash" id="final_cash" required class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">
                            </div>
                            <div class="mb-4">
                                <label for="notes" class="block font-medium mb-1">Catatan (Opsional)</label>
                                <textarea name="notes" id="notes" class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300"></textarea>
                            </div>
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded shadow">
                                <i class="bi bi-check-circle"></i> Akhiri Shift
                            </button>
                        </form>
                    @else
                        <form action="{{ route('owner.shift.start') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label for="initial_cash" class="block font-medium mb-1">Kas Awal di Laci</label>
                                <input type="number" name="initial_cash" id="initial_cash" required class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">
                            </div>
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded shadow">
                                <i class="bi bi-play-circle"></i> Mulai Shift
                            </button>
                        </form>
                    @endif
                </div>

                <div class="mt-6">
                    <h2 class="text-lg font-semibold mb-4 text-gray-800">Input Pengeluaran (Opsional)</h2>
                    <form action="{{ route('owner.shift.expense') }}" method="POST">
                        @csrf
                        <div class="grid md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="expense_amount" class="block font-medium mb-1">Jumlah Pengeluaran</label>
                                <input type="number" name="expense_amount" id="expense_amount" min="0" step="0.01" required class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">
                                @error('expense_amount')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="expense_description" class="block font-medium mb-1">Deskripsi</label>
                                <input type="text" name="expense_description" id="expense_description" required class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">
                                @error('expense_description')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded shadow">
                            <i class="bi bi-arrow-down-circle"></i> Simpan Pengeluaran
                        </button>
                    </form>
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