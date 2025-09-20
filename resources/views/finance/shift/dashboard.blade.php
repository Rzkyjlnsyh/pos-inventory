<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard Keuangan - Finance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" />
</head>
<body class="bg-gray-100">
<div class="flex">
    <x-navbar-finance />
    <div class="flex-1 lg:w-5/6">
        <x-navbar-top-finance />
        <div class="p-4 lg:p-8">
            <!-- Welcome Header -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-6 rounded-xl shadow-lg mb-6">
                <h1 class="text-2xl font-bold mb-2">💰 Dashboard Keuangan</h1>
                <p class="opacity-90">Monitoring dan reporting data shift kasir</p>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white p-6 rounded-xl shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total Kas Bulan Ini</p>
                            <p class="text-2xl font-bold text-green-600">Rp {{ number_format($totalCash, 0, ',', '.') }}</p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-full">
                            <i class="bi bi-cash-coin text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Pengeluaran Bulan Ini</p>
                            <p class="text-2xl font-bold text-red-600">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</p>
                        </div>
                        <div class="p-3 bg-red-100 rounded-full">
                            <i class="bi bi-arrow-up-circle text-red-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Shift Tertutup</p>
                            <p class="text-2xl font-bold text-blue-600">
                                {{ \App\Models\Shift::where('status', 'closed')->whereMonth('created_at', now()->month)->count() }}
                            </p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full">
                            <i class="bi bi-check-circle text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Shift Aktif</p>
                            <p class="text-2xl font-bold text-yellow-600">
                                {{ \App\Models\Shift::where('status', 'open')->count() }}
                            </p>
                        </div>
                        <div class="p-3 bg-yellow-100 rounded-full">
                            <i class="bi bi-clock-history text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-xl shadow">
                    <div class="flex items-center mb-4">
                        <div class="p-3 bg-blue-100 rounded-full mr-4">
                            <i class="bi bi-table text-blue-600 text-xl"></i>
                        </div>
                        <h2 class="text-xl font-semibold">Data Shift</h2>
                    </div>
                    <p class="text-gray-600 mb-4">Lihat dan eksport data semua shift kasir</p>
                    <a href="{{ route('finance.shift.history') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded inline-flex items-center">
                        <i class="bi bi-eye mr-2"></i> Lihat Data
                    </a>
                </div>

                <div class="bg-white p-6 rounded-xl shadow">
                    <div class="flex items-center mb-4">
                        <div class="p-3 bg-green-100 rounded-full mr-4">
                            <i class="bi bi-file-earmark-excel text-green-600 text-xl"></i>
                        </div>
                        <h2 class="text-xl font-semibold">Export Laporan</h2>
                    </div>
                    <p class="text-gray-600 mb-4">Download data shift dalam format Excel</p>
                    <a href="{{ route('finance.shift.export') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded inline-flex items-center">
                        <i class="bi bi-download mr-2"></i> Export Excel
                    </a>
                </div>
            </div>

            <!-- Quick Report -->
            <div class="bg-white p-6 rounded-xl shadow mt-6">
                <h2 class="text-xl font-semibold mb-4">📈 Quick Report</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="border rounded-lg p-4">
                        <p class="text-sm text-gray-500">Rata-rata Kas per Shift</p>
                        <p class="text-lg font-semibold">
                            Rp {{ number_format(\App\Models\Shift::where('status', 'closed')->avg('cash_total') ?? 0, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="border rounded-lg p-4">
                        <p class="text-sm text-gray-500">Total Shift Bulan Ini</p>
                        <p class="text-lg font-semibold">
                            {{ \App\Models\Shift::whereMonth('created_at', now()->month)->count() }}
                        </p>
                    </div>
                    <div class="border rounded-lg p-4">
                        <p class="text-sm text-gray-500">Selisih Terbesar</p>
                        <p class="text-lg font-semibold text-red-600">
                            Rp {{ number_format(abs(\App\Models\Shift::where('status', 'closed')->min('discrepancy') ?? 0), 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>