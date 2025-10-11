<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Finance Dashboard - Pare Custom</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" />
</head>
<body class="bg-gray-50">
<div class="flex">
    <x-navbar-finance />
    <div class="flex-1 lg:w-5/6">
        <x-navbar-top-finance />
        <div class="p-4 lg:p-8">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-6 rounded-xl shadow-lg mb-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold mb-2">💰 Finance Dashboard</h1>
                        <p class="opacity-90">Analisis keuangan & laporan bisnis {{ now()->format('F Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm opacity-80">Update: {{ now()->format('d M Y H:i') }}</p>
                        <p class="text-sm opacity-80">Period: Monthly</p>
                    </div>
                </div>
            </div>

            <!-- CASH FLOW CARDS -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Total Pemasukan -->
                <div class="bg-white p-6 rounded-xl shadow border-l-4 border-green-500">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="text-gray-500 text-sm">Total Pemasukan</p>
                            <p class="text-2xl font-bold text-green-600">
                                Rp {{ number_format($cashFlow['current']['income'], 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-full">
                            <i class="bi bi-arrow-down-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500">
                        @if($cashFlow['previous']['income'] > 0)
                            @php $growth = (($cashFlow['current']['income'] - $cashFlow['previous']['income']) / $cashFlow['previous']['income']) * 100 @endphp
                            <span class="{{ $growth >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $growth >= 0 ? '↑' : '↓' }} {{ number_format(abs($growth), 1) }}% vs bulan lalu
                            </span>
                        @else
                            <span class="text-gray-600">Data bulan lalu tidak tersedia</span>
                        @endif
                    </p>
                </div>

                <!-- Total Pengeluaran -->
                <div class="bg-white p-6 rounded-xl shadow border-l-4 border-red-500">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="text-gray-500 text-sm">Total Pengeluaran</p>
                            <p class="text-2xl font-bold text-red-600">
                                Rp {{ number_format($cashFlow['current']['expenses'], 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="p-3 bg-red-100 rounded-full">
                            <i class="bi bi-arrow-up-circle text-red-600 text-xl"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500">Operational & expenses</p>
                </div>

                <!-- Net Profit -->
                <div class="bg-white p-6 rounded-xl shadow border-l-4 border-blue-500">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="text-gray-500 text-sm">Net Profit</p>
                            <p class="text-2xl font-bold text-blue-600">
                                Rp {{ number_format($cashFlow['current']['income'] - $cashFlow['current']['expenses'], 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full">
                            <i class="bi bi-graph-up text-blue-600 text-xl"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500">Pemasukan - Pengeluaran</p>
                </div>
            </div>

            <!-- SALES PERFORMANCE -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white p-6 rounded-xl shadow">
                    <div class="text-center">
                        <p class="text-gray-500 text-sm">Total Transaksi</p>
                        <p class="text-3xl font-bold text-purple-600">{{ $salesPerformance['totalSales'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Sales order bulan ini</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow">
                    <div class="text-center">
                        <p class="text-gray-500 text-sm">Total Revenue</p>
                        <p class="text-3xl font-bold text-green-600">
                            Rp {{ number_format($salesPerformance['totalRevenue'], 0, ',', '.') }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Nilai penjualan</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow">
                    <div class="text-center">
                        <p class="text-gray-500 text-sm">Rata-rata/Transaksi</p>
                        <p class="text-3xl font-bold text-orange-600">
                            Rp {{ number_format($salesPerformance['averageTicket'], 0, ',', '.') }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Ticket size</p>
                    </div>
                </div>
            </div>

            <!-- TWO COLUMN LAYOUT -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- OUTSTANDING PAYMENTS -->
                <div class="bg-white p-6 rounded-xl shadow">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold text-gray-800">⏳ Outstanding Payments</h2>
                        <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-sm">
                            Rp {{ number_format($totalOutstanding, 0, ',', '.') }}
                        </span>
                    </div>
                    
                    @if($outstandingPayments->count() > 0)
                    <div class="space-y-3 max-h-80 overflow-y-auto">
                        @foreach($outstandingPayments as $payment)
                        <div class="border rounded-lg p-3">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-medium text-sm">{{ $payment['so_number'] }}</p>
                                    <p class="text-xs text-gray-600">{{ $payment['customer'] }}</p>
                                </div>
                                <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded">
                                    {{ $payment['days_old'] }} hari
                                </span>
                            </div>
                            <div class="flex justify-between items-center mt-2">
                                <span class="text-sm text-gray-600">
                                    Sisa: Rp {{ number_format($payment['remaining'], 0, ',', '.') }}
                                </span>
                                <a href="{{ route('finance.sales.show', ['salesOrder' => $payment['so_number']]) }}" 
                                   class="text-blue-600 hover:text-blue-800 text-xs">
                                    Lihat
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-8 text-gray-500">
                        <i class="bi bi-check-circle text-3xl text-green-400 mb-2"></i>
                        <p>Tidak ada outstanding payments</p>
                    </div>
                    @endif
                </div>

                <!-- PAYMENT METHODS -->
                <div class="bg-white p-6 rounded-xl shadow">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">💳 Payment Methods</h2>
                    
                    <div class="space-y-4">
                        @foreach($paymentMethods as $method)
                        <div class="flex justify-between items-center">
                            <div class="flex items-center">
                                @if($method->method === 'cash')
                                    <i class="bi bi-cash-coin text-green-500 mr-2"></i>
                                @elseif($method->method === 'transfer')
                                    <i class="bi bi-bank text-blue-500 mr-2"></i>
                                @else
                                    <i class="bi bi-arrow-left-right text-purple-500 mr-2"></i>
                                @endif
                                <span class="capitalize">{{ $method->method }}</span>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold">Rp {{ number_format($method->total_amount, 0, ',', '.') }}</p>
                                <p class="text-xs text-gray-500">{{ $method->transaction_count }} transaksi</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- TOP PRODUCTS -->
            <div class="bg-white p-6 rounded-xl shadow">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">🏆 Top Products</h2>
                
                @if($topProducts->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2">Produk</th>
                                <th class="text-right py-2">Qty</th>
                                <th class="text-right py-2">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topProducts as $product)
                            <tr class="border-b">
                                <td class="py-2">{{ $product->product_name }}</td>
                                <td class="text-right py-2">{{ $product->total_qty }}</td>
                                <td class="text-right py-2 text-green-600 font-medium">
                                    Rp {{ number_format($product->total_revenue, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-8 text-gray-500">
                    <i class="bi bi-box-seam text-3xl text-gray-400 mb-2"></i>
                    <p>Belum ada data penjualan bulan ini</p>
                </div>
                @endif
            </div>

            <!-- QUICK ACTIONS -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                <a href="{{ route('finance.shift.history') }}" 
                   class="bg-white p-4 rounded-xl shadow border hover:border-blue-500 transition-colors text-center">
                    <i class="bi bi-clock-history text-blue-500 text-2xl mb-2"></i>
                    <p class="font-semibold">Shift History</p>
                    <p class="text-xs text-gray-600">Lihat riwayat shift</p>
                </a>

                <a href="{{ route('finance.sales.index') }}" 
                   class="bg-white p-4 rounded-xl shadow border hover:border-green-500 transition-colors text-center">
                    <i class="bi bi-receipt text-green-500 text-2xl mb-2"></i>
                    <p class="font-semibold">Sales Report</p>
                    <p class="text-xs text-gray-600">Laporan penjualan</p>
                </a>

                <a href="{{ route('finance.shift.export') }}" 
                   class="bg-white p-4 rounded-xl shadow border hover:border-purple-500 transition-colors text-center">
                    <i class="bi bi-file-earmark-excel text-purple-500 text-2xl mb-2"></i>
                    <p class="font-semibold">Export Data</p>
                    <p class="text-xs text-gray-600">Download Excel</p>
                </a>
            </div>
        </div>
    </div>
</div>
</body>
</html>