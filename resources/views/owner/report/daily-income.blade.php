<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pendapatan Harian - Bblara</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Raleway', sans-serif;
        }
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
        .stats-card {
            transition: transform 0.2s ease-in-out;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex">
        <button class="fixed text-white text-3xl top-5 left-4 p-2 rounded-md bg-gray-700 lg:hidden focus:outline-none z-50" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>
        <x-navbar-owner></x-navbar-owner>
        <div class="flex-1 lg:w-5/6">
            <x-navbar-top-owner></x-navbar-top-owner>
            
            <div class="p-4 lg:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h1 class="text-2xl lg:text-2xl font-bold text-gray-800">Laporan Pendapatan Harian</h1>
                    <div class="flex space-x-2">
                        <a href="{{ route('owner.report.index') }}" class="px-3 py-1.5 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors text-sm">
                            <i class="bi bi-arrow-left me-1"></i>Kembali
                        </a>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-4 mb-4">
                    <h2 class="text-lg font-semibold mb-3">Pilih Rentang Tanggal</h2>
                    <form action="{{ route('owner.report.daily-income') }}" method="GET" class="flex flex-col lg:flex-row items-end space-y-3 lg:space-y-0 lg:space-x-3">
                        <div class="w-full lg:w-1/3">
                            <label class="block text-gray-700 text-xs font-bold mb-1">Dari Tanggal</label>
                            <input type="date" name="startDate" value="{{ $startDate }}" class="shadow appearance-none border rounded w-full py-1.5 px-2 text-sm text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        <div class="w-full lg:w-1/3">
                            <label class="block text-gray-700 text-xs font-bold mb-1">Sampai Tanggal</label>
                            <input type="date" name="endDate" value="{{ $endDate }}" class="shadow appearance-none border rounded w-full py-1.5 px-2 text-sm text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        <div class="w-full lg:w-1/3 flex space-x-2">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-1.5 px-3 rounded text-sm focus:outline-none focus:shadow-outline flex items-center">
                                <i class="bi bi-search me-1"></i> Tampilkan
                            </button>
                            <a href="{{ route('owner.report.export-excel') }}?startDate={{ $startDate }}&endDate={{ $endDate }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-1.5 px-3 rounded text-sm focus:outline-none focus:shadow-outline flex items-center">
                                <i class="bi bi-file-earmark-excel me-1"></i> Export
                            </a>
                        </div>
                    </form>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
                    <div class="stats-card bg-gradient-to-r from-red-500 to-red-600 rounded-xl shadow-lg p-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-white text-sm">Total Modal</p>
                                <h3 class="text-white text-xl font-bold">Rp {{ number_format($totalData['total_modal'], 0, ',', '.') }}</h3>
                                <p class="text-red-100 mt-1 text-xs">{{ $startDate }} - {{ $endDate }}</p>
                            </div>
                            <div class="bg-white/20 p-3 rounded-lg">
                                <i class="bi bi-cash-stack text-2xl text-white"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stats-card bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg p-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-white text-sm">Total Pendapatan</p>
                                <h3 class="text-white text-xl font-bold">Rp {{ number_format($totalData['total_pendapatan'], 0, ',', '.') }}</h3>
                                <p class="text-blue-100 mt-1 text-xs">{{ $startDate }} - {{ $endDate }}</p>
                            </div>
                            <div class="bg-white/20 p-3 rounded-lg">
                                <i class="bi bi-wallet2 text-2xl text-white"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stats-card bg-gradient-to-r from-green-500 to-green-600 rounded-xl shadow-lg p-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-white text-sm">Total Keuntungan</p>
                                <h3 class="text-white text-xl font-bold">Rp {{ number_format($totalData['total_keuntungan'], 0, ',', '.') }}</h3>
                                <p class="text-green-100 mt-1 text-xs">{{ $startDate }} - {{ $endDate }}</p>
                            </div>
                            <div class="bg-white/20 p-3 rounded-lg">
                                <i class="bi bi-graph-up-arrow text-2xl text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
                    <div class="stats-card bg-white rounded-xl shadow-lg p-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-600 text-sm">Transaksi Tunai</p>
                                <h3 class="text-gray-800 text-xl font-bold">{{ $totalData['total_transaksi_tunai'] }}</h3>
                                <p class="text-gray-500 mt-1 text-xs">transaksi</p>
                            </div>
                            <div class="bg-blue-100 p-3 rounded-lg text-blue-600">
                                <i class="bi bi-cash text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stats-card bg-white rounded-xl shadow-lg p-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-600 text-sm">Transaksi QRIS</p>
                                <h3 class="text-gray-800 text-xl font-bold">{{ $totalData['total_transaksi_qris'] }}</h3>
                                <p class="text-gray-500 mt-1 text-xs">transaksi</p>
                            </div>
                            <div class="bg-purple-100 p-3 rounded-lg text-purple-600">
                                <i class="bi bi-qr-code text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stats-card bg-white rounded-xl shadow-lg p-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-600 text-sm">Total Transaksi</p>
                                <h3 class="text-gray-800 text-xl font-bold">{{ $totalData['total_transaksi'] }}</h3>
                                <p class="text-gray-500 mt-1 text-xs">transaksi</p>
                            </div>
                            <div class="bg-orange-100 p-3 rounded-lg text-orange-600">
                                <i class="bi bi-cart-check text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-4">
                    <h2 class="text-lg font-semibold mb-3">Rincian Pendapatan Harian</h2>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-2 px-3 border-b text-left">Tanggal</th>
                                    <th class="py-2 px-3 border-b text-right">Trans. Tunai</th>
                                    <th class="py-2 px-3 border-b text-right">Trans. QRIS</th>
                                    <th class="py-2 px-3 border-b text-right">Total Trans.</th>
                                    <th class="py-2 px-3 border-b text-right">Modal (Rp)</th>
                                    <th class="py-2 px-3 border-b text-right">Pendapatan (Rp)</th>
                                    <th class="py-2 px-3 border-b text-right">Keuntungan (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dailyReports as $report)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-2 px-3 border-b">{{ $report['tanggal'] }}</td>
                                    <td class="py-2 px-3 border-b text-right">{{ $report['transaksi_tunai_count'] }}</td>
                                    <td class="py-2 px-3 border-b text-right">{{ $report['transaksi_qris_count'] }}</td>
                                    <td class="py-2 px-3 border-b text-right">{{ $report['total_transaksi'] }}</td>
                                    <td class="py-2 px-3 border-b text-right">{{ number_format($report['modal'], 0, ',', '.') }}</td>
                                    <td class="py-2 px-3 border-b text-right">{{ number_format($report['pendapatan'], 0, ',', '.') }}</td>
                                    <td class="py-2 px-3 border-b text-right">{{ number_format($report['keuntungan'], 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-100 font-semibold">
                                <tr>
                                    <td class="py-2 px-3 border-b">TOTAL</td>
                                    <td class="py-2 px-3 border-b text-right">{{ $totalData['total_transaksi_tunai'] }}</td>
                                    <td class="py-2 px-3 border-b text-right">{{ $totalData['total_transaksi_qris'] }}</td>
                                    <td class="py-2 px-3 border-b text-right">{{ $totalData['total_transaksi'] }}</td>
                                    <td class="py-2 px-3 border-b text-right">{{ number_format($totalData['total_modal'], 0, ',', '.') }}</td>
                                    <td class="py-2 px-3 border-b text-right">{{ number_format($totalData['total_pendapatan'], 0, ',', '.') }}</td>
                                    <td class="py-2 px-3 border-b text-right">{{ number_format($totalData['total_keuntungan'], 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('x-navbar-owner');
            sidebar.classList.toggle('hidden');
        }

        function toggleDropdown(button) {
            const dropdownMenus = document.querySelectorAll(".dropdown-menu");
            const dropdownArrows = document.querySelectorAll("i.bi-chevron-down");

            dropdownMenus.forEach((menu) => {
                if (menu !== button.nextElementSibling) {
                    menu.classList.add("max-h-0");
                    menu.classList.remove("max-h-40");
                }
            });

            dropdownArrows.forEach((arrow) => {
                if (arrow !== button.querySelector("i.bi-chevron-down")) {
                    arrow.classList.remove("rotate-180");
                }
            });

            const dropdownMenu = button.nextElementSibling;
            const dropdownArrow = button.querySelector("i.bi-chevron-down");

            if (dropdownMenu.classList.contains("max-h-0")) {
                dropdownMenu.classList.remove("max-h-0");
                dropdownMenu.classList.add("max-h-40");
                dropdownArrow.classList.add("rotate-180");
            } else {
                dropdownMenu.classList.add("max-h-0");
                dropdownMenu.classList.remove("max-h-40");
                dropdownArrow.classList.remove("rotate-180");
            }
        }
    </script>
</body>
</html>