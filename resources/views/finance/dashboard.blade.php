<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Finance Dashboard - Pare Custom</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" />
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Raleway', sans-serif; }
    </style>
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
                        <p class="opacity-90">Laporan Keuangan & Income Statement</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm opacity-80">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Date Range Filter -->
            <div class="bg-white p-4 rounded-xl shadow mb-6">
                <form method="GET" class="flex flex-col md:flex-row gap-4 items-end">
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                            <input type="date" name="start_date" value="{{ $startDate }}" 
                                   class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                            <input type="date" name="end_date" value="{{ $endDate }}" 
                                   class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    <div class="flex gap-2 flex-wrap">
                        <button type="submit" 
                                class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition-colors font-medium">
                            Terapkan Filter
                        </button>
                    </div>
                </form>
                
                <!-- Quick Date Buttons -->
                <div class="flex gap-2 mt-3">
                    <a href="?start_date={{ now()->format('Y-m-d') }}&end_date={{ now()->format('Y-m-d') }}" 
                       class="bg-blue-100 text-blue-700 px-3 py-1 rounded-lg text-sm hover:bg-blue-200 transition-colors">
                        Hari Ini
                    </a>
                    <a href="?start_date={{ now()->subDays(6)->format('Y-m-d') }}&end_date={{ now()->format('Y-m-d') }}" 
                       class="bg-green-100 text-green-700 px-3 py-1 rounded-lg text-sm hover:bg-green-200 transition-colors">
                        7 Hari
                    </a>
                    <a href="?start_date={{ now()->startOfMonth()->format('Y-m-d') }}&end_date={{ now()->endOfMonth()->format('Y-m-d') }}" 
                       class="bg-purple-100 text-purple-700 px-3 py-1 rounded-lg text-sm hover:bg-purple-200 transition-colors">
                        Bulan Ini
                    </a>
                </div>
            </div>

            <!-- INCOME STATEMENT -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
                <!-- OMSET -->
                <div class="bg-white p-6 rounded-xl shadow border-l-4 border-green-500">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">OMSET</p>
                            <p class="text-2xl font-bold text-green-600">
                                Rp {{ number_format($omset, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-full">
                            <i class="bi bi-arrow-down-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="space-y-1 text-xs text-gray-600">
                        <div class="flex justify-between">
                            <span>Penjualan:</span>
                            <span>Rp {{ number_format($totalSales, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Pemasukan Manual:</span>
                            <span>Rp {{ number_format($manualIncome, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    @if($omsetGrowth != 0)
                    <p class="text-xs mt-2 {{ $omsetGrowth >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        <i class="bi bi-{{ $omsetGrowth >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                        {{ number_format(abs($omsetGrowth), 1) }}% vs bulan lalu
                    </p>
                    @endif
                </div>

                <!-- HPP -->
                <div class="bg-white p-6 rounded-xl shadow border-l-4 border-red-500">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">HPP</p>
                            <p class="text-2xl font-bold text-red-600">
                                Rp {{ number_format($hpp, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="p-3 bg-red-100 rounded-full">
                            <i class="bi bi-box-seam text-red-600 text-xl"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-600">Cost of Goods Sold</p>
                    <p class="text-xs text-gray-500 mt-1">Total pembelian barang</p>
                </div>

                <!-- OPERASIONAL -->
                <div class="bg-white p-6 rounded-xl shadow border-l-4 border-orange-500">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">OPERASIONAL</p>
                            <p class="text-2xl font-bold text-orange-600">
                                Rp {{ number_format($operasional, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="p-3 bg-orange-100 rounded-full">
                            <i class="bi bi-tools text-orange-600 text-xl"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-600">Operating Expenses</p>
                    <p class="text-xs text-gray-500 mt-1">Pengeluaran operasional</p>
                </div>

                    <!-- CASH TRANSFER (CARD BARU) -->
    <div class="bg-white p-6 rounded-xl shadow border-l-4 border-purple-500">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-gray-500 text-sm font-medium">TRANSFER TUNAI</p>
                <p class="text-2xl font-bold text-purple-600">
                    @php
                        $totalCashTransfer = \App\Models\CashTransfer::whereBetween('created_at', [$start, $end])->sum('amount') ?? 0;
                    @endphp
                    Rp {{ number_format($totalCashTransfer, 0, ',', '.') }}
                </p>
            </div>
            <div class="p-3 bg-purple-100 rounded-full">
                <i class="bi bi-arrow-left-right text-purple-600 text-xl"></i>
            </div>
        </div>
        <p class="text-xs text-gray-600">Setor/Tukar Tunai</p>
        <p class="text-xs text-purple-500 mt-1">Tidak mempengaruhi profit</p>
    </div>

                <!-- PROFIT -->
                <div class="bg-white p-6 rounded-xl shadow border-l-4 border-blue-500">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">PROFIT</p>
                            <p class="text-2xl font-bold text-blue-600">
                                Rp {{ number_format($profit, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full">
                            <i class="bi bi-graph-up text-blue-600 text-xl"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-600">Net Profit</p>
                    <p class="text-xs {{ $profit >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1 font-medium">
                        {{ $profit >= 0 ? 'Laba' : 'Rugi' }}
                    </p>
                </div>
            </div>

            <!-- BREAKDOWN STATUS PEMBAYARAN -->
<div class="bg-white p-6 rounded-xl shadow mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">📊 Breakdown Status Pembayaran</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <!-- TOTAL ORDERS -->
        <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-blue-600 text-sm font-medium">Total Orders</p>
                    <p class="text-2xl font-bold text-blue-800">{{ $salesBreakdown->total_orders ?? 0 }}</p>
                </div>
                <i class="bi bi-receipt text-blue-500 text-xl"></i>
            </div>
            <p class="text-xs text-blue-600 mt-2">Exclude Draft</p>
        </div>

        <!-- LUNAS -->
        <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-green-600 text-sm font-medium">Lunas</p>
                    <p class="text-2xl font-bold text-green-800">{{ $salesBreakdown->lunas_count ?? 0 }}</p>
                    <p class="text-sm text-green-700">Rp {{ number_format($salesBreakdown->lunas_amount ?? 0, 0, ',', '.') }}</p>
                </div>
                <i class="bi bi-check-circle text-green-500 text-xl"></i>
            </div>
            <div class="w-full bg-green-200 rounded-full h-2 mt-2">
                <div class="bg-green-600 h-2 rounded-full" style="width: {{ $salesBreakdown->total_orders > 0 ? round(($salesBreakdown->lunas_count / $salesBreakdown->total_orders) * 100) : 0 }}%"></div>
            </div>
            <p class="text-xs text-green-600 mt-1">
                {{ $salesBreakdown->total_orders > 0 ? round(($salesBreakdown->lunas_count / $salesBreakdown->total_orders) * 100) : 0 }}% dari total
            </p>
        </div>

        <!-- DP -->
        <div class="bg-yellow-50 p-4 rounded-lg border-l-4 border-yellow-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-yellow-600 text-sm font-medium">DP</p>
                    <p class="text-2xl font-bold text-yellow-800">{{ $salesBreakdown->dp_count ?? 0 }}</p>
                    <p class="text-sm text-yellow-700">Rp {{ number_format($salesBreakdown->dp_amount ?? 0, 0, ',', '.') }}</p>
                </div>
                <i class="bi bi-currency-dollar text-yellow-500 text-xl"></i>
            </div>
            <div class="w-full bg-yellow-200 rounded-full h-2 mt-2">
                <div class="bg-yellow-600 h-2 rounded-full" style="width: {{ $salesBreakdown->total_orders > 0 ? round(($salesBreakdown->dp_count / $salesBreakdown->total_orders) * 100) : 0 }}%"></div>
            </div>
            <p class="text-xs text-yellow-600 mt-1">
                {{ $salesBreakdown->total_orders > 0 ? round(($salesBreakdown->dp_count / $salesBreakdown->total_orders) * 100) : 0 }}% dari total
            </p>
        </div>

        <!-- BELUM BAYAR -->
        <div class="bg-red-50 p-4 rounded-lg border-l-4 border-red-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-red-600 text-sm font-medium">Belum Bayar</p>
                    <p class="text-2xl font-bold text-red-800">{{ $salesBreakdown->belum_bayar_count ?? 0 }}</p>
                    <p class="text-sm text-red-700">Rp {{ number_format($salesBreakdown->belum_bayar_amount ?? 0, 0, ',', '.') }}</p>
                </div>
                <i class="bi bi-clock text-red-500 text-xl"></i>
            </div>
            <div class="w-full bg-red-200 rounded-full h-2 mt-2">
                <div class="bg-red-600 h-2 rounded-full" style="width: {{ $salesBreakdown->total_orders > 0 ? round(($salesBreakdown->belum_bayar_count / $salesBreakdown->total_orders) * 100) : 0 }}%"></div>
            </div>
            <p class="text-xs text-red-600 mt-1">
                {{ $salesBreakdown->total_orders > 0 ? round(($salesBreakdown->belum_bayar_count / $salesBreakdown->total_orders) * 100) : 0 }}% dari total
            </p>
        </div>
    </div>

    <!-- PELUNASAN (BAYAR BERTAHAP) -->
    @if($pelunasanData && $pelunasanData->count > 0)
    <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-purple-800 flex items-center">
                    <i class="bi bi-arrow-repeat mr-2"></i> Pembayaran Pelunasan
                </h3>
                <p class="text-sm text-purple-600">
                    {{ $pelunasanData->count }} transaksi pelunasan • 
                    Rp {{ number_format($pelunasanData->amount, 0, ',', '.') }}
                </p>
            </div>
            <span class="bg-purple-200 text-purple-800 px-3 py-1 rounded-full text-sm font-medium">
                Bayar Bertahap
            </span>
        </div>
    </div>
    @endif
</div>

<!-- THREE COLUMN LAYOUT -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- PAYMENT METHODS BREAKDOWN -->
    <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">💳 Metode Pembayaran</h2>
        
        <div class="space-y-4">
            @foreach($salesByPaymentMethod as $method)
            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center">
                    @if($method->method === 'cash')
                        <i class="bi bi-cash-coin text-green-500 mr-3 text-lg"></i>
                    @elseif($method->method === 'transfer')
                        <i class="bi bi-bank text-blue-500 mr-3 text-lg"></i>
                    @else
                        <i class="bi bi-arrow-left-right text-purple-500 mr-3 text-lg"></i>
                    @endif
                    <div>
                        <p class="font-medium capitalize">{{ $method->method }}</p>
                        <p class="text-xs text-gray-500">{{ $method->transaction_count }} transaksi</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="font-semibold text-gray-800">
                        Rp {{ number_format($method->total_amount, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500">
                        {{ $omset > 0 ? number_format(($method->total_amount / $omset) * 100, 1) : 0 }}%
                    </p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

<!-- RECENT TRANSACTIONS -->
<div class="bg-white p-6 rounded-xl shadow">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">📋 Transaksi Terbaru</h2>
    
    @if($recentSales->count() > 0)
    <div class="space-y-3">
        @foreach($recentSales as $sale)
        <div class="flex justify-between items-center p-3 border rounded-lg hover:bg-gray-50">
            <div class="flex-1">
                <div class="flex justify-between items-start mb-1">
                    <div>
                        <p class="font-medium text-sm">{{ $sale->so_number }}</p>
                        <p class="text-xs text-gray-600">{{ $sale->customer->name ?? 'Guest' }}</p>
                    </div>
                    <div class="text-right">
                        <!-- STATUS PEMBAYARAN -->
                        @if($sale->payment_status === 'lunas')
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium">
                                <i class="bi bi-check-circle mr-1"></i>Lunas
                            </span>
                        @elseif($sale->payment_status === 'dp')
                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-medium">
                                <i class="bi bi-currency-dollar mr-1"></i>DP
                            </span>
                        @else
                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-medium">
                                <i class="bi bi-clock mr-1"></i>Belum Bayar
                            </span>
                        @endif
                    </div>
                </div>
                
                <!-- DETAIL PEMBAYARAN -->
                <div class="grid grid-cols-3 gap-2 text-xs text-gray-600 mt-2">
                    <div>
                        <span class="font-medium">Total:</span>
                        <br>Rp {{ number_format($sale->grand_total, 0, ',', '.') }}
                    </div>
                    <div>
                        <span class="font-medium">Dibayar:</span>
                        <br>Rp {{ number_format($sale->paid_total, 0, ',', '.') }}
                    </div>
                    <div class="{{ $sale->remaining_amount > 0 ? 'text-red-600' : 'text-green-600' }}">
                        <span class="font-medium">Sisa:</span>
                        <br>Rp {{ number_format($sale->remaining_amount, 0, ',', '.') }}
                    </div>
                </div>
                
                <!-- STATUS ORDER -->
                <div class="mt-2">
                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">
                        {{ ucfirst(str_replace('_', ' ', $sale->status)) }}
                    </span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-8 text-gray-500">
        <i class="bi bi-receipt text-3xl text-gray-400 mb-2"></i>
        <p>Tidak ada transaksi</p>
        <p class="text-sm">(Exclude draft orders)</p>
    </div>
    @endif
</div>

    <!-- PRODUK TERLARIS -->
    <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">🏆 Produk Terlaris</h2>
        
        @if($bestSellingProducts->count() > 0)
        <div class="space-y-4">
            @foreach($bestSellingProducts as $product)
            <div class="flex justify-between items-center p-3 bg-gradient-to-r from-yellow-50 to-orange-50 rounded-lg border border-yellow-200">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center mr-3">
                        <span class="text-white text-xs font-bold">#{{ $loop->iteration }}</span>
                    </div>
                    <div>
                        <p class="font-medium text-sm">{{ $product->product_name }}</p>
                        <p class="text-xs text-gray-500">{{ $product->product_sku }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500">
    {{ number_format($product->total_terjual) }} pcs terjual
</p>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-8 text-gray-500">
            <i class="bi bi-box text-3xl text-gray-400 mb-2"></i>
            <p>Belum ada penjualan produk</p>
        </div>
        @endif
    </div>
</div>

            <!-- QUICK ACTIONS -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('finance.shift.history') }}" 
                   class="bg-white p-4 rounded-xl shadow border hover:border-blue-500 transition-colors text-center group">
                    <i class="bi bi-clock-history text-blue-500 text-2xl mb-2 group-hover:scale-110 transition-transform"></i>
                    <p class="font-semibold">Shift History</p>
                    <p class="text-xs text-gray-600">Lihat riwayat shift</p>
                </a>

                <a href="{{ route('finance.purchases.index') }}" 
                   class="bg-white p-4 rounded-xl shadow border hover:border-green-500 transition-colors text-center group">
                    <i class="bi bi-cart-check text-green-500 text-2xl mb-2 group-hover:scale-110 transition-transform"></i>
                    <p class="font-semibold">Data Pembelian</p>
                    <p class="text-xs text-gray-600">Lihat purchase orders</p>
                </a>

                <a href="{{ route('finance.shift.export') }}" 
                   class="bg-white p-4 rounded-xl shadow border hover:border-purple-500 transition-colors text-center group">
                    <i class="bi bi-file-earmark-excel text-purple-500 text-2xl mb-2 group-hover:scale-110 transition-transform"></i>
                    <p class="font-semibold">Export Laporan</p>
                    <p class="text-xs text-gray-600">Download Excel</p>
                </a>
            </div>
        </div>
    </div>
</div>
</body>
</html>