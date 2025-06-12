<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perbandingan Transaksi - Bblara</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .chart-container {
            position: relative;
            height: 300px;
            margin: auto;
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
            
            <div class="p-6 lg:p-8">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-3xl font-bold text-gray-800">Perbandingan Transaksi</h1>
                    <div class="flex space-x-2">
                        <a href="{{ route('owner.report.daily-income') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            <i class="bi bi-cash-coin me-2"></i>Laporan Pendapatan
                        </a>
                        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="bi bi-download me-2"></i>Export
                        </button>
                    </div>
                </div>

                <!-- Payment Method Comparison Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Chart Card -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h2 class="text-xl font-semibold mb-4">Grafik Perbandingan Pembayaran</h2>
                        <div class="chart-container">
                            <canvas id="transactionChart"></canvas>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 gap-6">
                        <!-- QRIS Stats -->
                        <div class="stats-card bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl shadow-lg p-6">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-white text-lg">Total QRIS</p>
                                    <h3 class="text-white text-3xl font-bold">{{ $qrisCount }}</h3>
                                    <p class="text-purple-100 mt-2">Transaksi</p>
                                </div>
                                <div class="bg-white/20 p-4 rounded-lg">
                                    <i class="bi bi-qr-code text-4xl text-white"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Cash Stats -->
                        <div class="stats-card bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg p-6">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-white text-lg">Total Tunai</p>
                                    <h3 class="text-white text-3xl font-bold">{{ $tunaiCount }}</h3>
                                    <p class="text-blue-100 mt-2">Transaksi</p>
                                </div>
                                <div class="bg-white/20 p-4 rounded-lg">
                                    <i class="bi bi-cash text-4xl text-white"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Summary Card -->
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <h3 class="text-xl font-semibold mb-4">Kesimpulan</h3>
                            <div class="flex items-center">
                                <i class="bi bi-trophy-fill text-yellow-500 text-3xl mr-4"></i>
                                <p class="text-lg">
                                    @if(isset($totalTransactions) && $totalTransactions > 0)
                                        @if($qrisCount > $tunaiCount)
                                            <span class="font-semibold text-purple-600">QRIS</span> adalah metode pembayaran terpopuler dengan {{ $qrisCount }} transaksi
                                        @elseif($tunaiCount > $qrisCount)
                                            <span class="font-semibold text-blue-600">Tunai</span> adalah metode pembayaran terpopuler dengan {{ $tunaiCount }} transaksi
                                        @else
                                            QRIS dan Tunai memiliki jumlah yang sama yaitu {{ $qrisCount }} transaksi
                                        @endif
                                    @else
                                        Belum ada data transaksi untuk dianalisis
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Best Seller Section -->
                <div class="mt-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Menu Best Seller</h2>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Best Seller Chart -->
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <h3 class="text-xl font-semibold mb-4">Top 5 Menu Terlaris</h3>
                            <div class="chart-container">
                                @if(count($bestSellersWithPercentage) > 0)
                                    <canvas id="bestSellerChart"></canvas>
                                @else
                                    <div class="flex flex-col items-center justify-center h-full">
                                        <i class="bi bi-info-circle text-orange-400 text-4xl mb-3"></i>
                                        <p class="text-gray-500">Belum ada data penjualan menu</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Best Seller Cards -->
                        <div class="grid grid-cols-1 gap-4">
                            @if(count($bestSellersWithPercentage) > 0)
                                @foreach($bestSellersWithPercentage as $menu => $data)
                                <div class="stats-card bg-white rounded-xl shadow-lg p-6">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div class="bg-gradient-to-r from-orange-400 to-orange-500 p-3 rounded-lg">
                                                <i class="bi bi-cup-hot text-2xl text-white"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-lg font-semibold">{{ $menu }}</h4>
                                                <p class="text-gray-600">Terjual {{ $data['count'] }} kali</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-2xl font-bold text-orange-500">{{ $data['percentage'] }}%</span>
                                            <div class="w-24 bg-gray-200 rounded-full h-2 mt-2">
                                                <div class="bg-orange-500 rounded-full h-2" style="width: {{ $data['percentage'] }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div class="stats-card bg-white rounded-xl shadow-lg p-6 text-center">
                                    <div class="p-4">
                                        <i class="bi bi-info-circle text-orange-400 text-4xl"></i>
                                        <p class="text-gray-600 mt-2">Belum ada data penjualan menu</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Hourly Analysis Section -->
                <div class="mt-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Analisis Waktu Transaksi</h2>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Hourly Transaction Chart -->
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <h3 class="text-xl font-semibold mb-4">Grafik Transaksi per Jam</h3>
                            <div class="chart-container">
                                <canvas id="hourlyChart"></canvas>
                            </div>
                        </div>

                        <!-- Peak Hours Analysis -->
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <h3 class="text-xl font-semibold mb-4">Jam Tersibuk</h3>
                            <div class="space-y-4">
                                @if(!empty($peakHours))
                                    @foreach($peakHours as $hour => $count)
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-lg font-semibold text-gray-800">
                                                    {{ sprintf('%02d:00', $hour) }} - {{ sprintf('%02d:00', $hour + 1) }}
                                                </p>
                                                <p class="text-gray-600">{{ $count }} Transaksi</p>
                                            </div>
                                            <div class="bg-blue-100 text-blue-800 p-3 rounded-full">
                                                <i class="bi bi-clock text-2xl"></i>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-blue-600 rounded-full h-2" 
                                                    style="width: {{ max($peakHours) > 0 ? ($count / max($peakHours) * 100) : 0 }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                                        <p class="text-gray-600">Tidak ada data transaksi untuk dianalisis</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employee Performance Section -->
                <div class="mt-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Performa Karyawan</h2>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Employee Performance Chart -->
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <h3 class="text-xl font-semibold mb-4">Grafik Performa Karyawan</h3>
                            <div class="chart-container">
                                @if(isset($topEmployees) && $topEmployees->count() > 0)
                                    <canvas id="employeeChart"></canvas>
                                @else
                                    <div class="flex flex-col items-center justify-center h-full">
                                        <i class="bi bi-people text-blue-400 text-4xl mb-3"></i>
                                        <p class="text-gray-500">Belum ada data karyawan untuk dianalisis</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Top Employees List -->
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <h3 class="text-xl font-semibold mb-4">Top 5 Karyawan</h3>
                            <div class="space-y-4">
                                @if(isset($topEmployees) && $topEmployees->count() > 0)
                                    @foreach($topEmployees as $employee)
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-3">
                                                <div class="bg-gradient-to-r from-purple-500 to-blue-500 text-white h-10 w-10 rounded-full flex items-center justify-center font-bold">
                                                    {{ strtoupper(substr($employee['name'], 0, 1)) }}
                                                </div>
                                                <div>
                                                    <p class="text-lg font-semibold text-gray-800">{{ $employee['name'] }}</p>
                                                    <p class="text-sm text-gray-600">
                                                        QRIS: {{ $employee['qris_count'] }} | Tunai: {{ $employee['tunai_count'] }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-2xl font-bold text-blue-600">{{ $employee['total_count'] }}</p>
                                                <p class="text-sm text-gray-600">Transaksi</p>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                                <span>Kontribusi</span>
                                                <span>{{ $employee['percentage'] }}%</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-gradient-to-r from-purple-500 to-blue-500 rounded-full h-2" 
                                                    style="width: {{ $employee['percentage'] }}%">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                                        <p class="text-gray-600">Belum ada data karyawan untuk dianalisis</p>
                                    </div>
                                @endif
                            </div>
                        </div>
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

        // Payment Methods Chart
        const ctx = document.getElementById('transactionChart').getContext('2d');
        const transactionChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['QRIS', 'Tunai'],
                datasets: [{
                    label: 'Jumlah Transaksi',
                    data: [{{ $qrisCount }}, {{ $tunaiCount }}],
                    backgroundColor: [
                        'rgba(147, 51, 234, 0.8)',  // Purple for QRIS
                        'rgba(59, 130, 246, 0.8)'   // Blue for Cash
                    ],
                    borderColor: [
                        'rgba(147, 51, 234, 1)',
                        'rgba(59, 130, 246, 1)'
                    ],
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: {
                                family: 'Raleway'
                            }
                        },
                        grid: {
                            display: true,
                            drawBorder: false
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                family: 'Raleway',
                                weight: 'bold'
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            family: 'Raleway',
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            family: 'Raleway',
                            size: 13
                        },
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.dataset.label + ': ' + tooltipItem.raw + ' Transaksi';
                            }
                        }
                    }
                }
            }
        });

        @if(count($bestSellersWithPercentage) > 0)
        // Best Seller Chart
        const bestSellerCtx = document.getElementById('bestSellerChart').getContext('2d');
        const bestSellerChart = new Chart(bestSellerCtx, {
            type: 'doughnut',
            data: {
                labels: [@foreach($bestSellersWithPercentage as $menu => $data) '{{ $menu }}', @endforeach],
                datasets: [{
                    data: [@foreach($bestSellersWithPercentage as $data) {{ $data['count'] }}, @endforeach],
                    backgroundColor: [
                        'rgba(225, 127, 18, 0.8)',
                        'rgba(234, 88, 12, 0.8)',
                        'rgba(249, 115, 22, 0.8)',
                        'rgba(251, 146, 60, 0.8)',
                        'rgba(253, 186, 116, 0.8)'
                    ],
                    borderColor: [
                        'rgba(225, 127, 18, 1)',
                        'rgba(234, 88, 12, 1)',
                        'rgba(249, 115, 22, 1)',
                        'rgba(251, 146, 60, 1)',
                        'rgba(253, 186, 116, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            font: {
                                family: 'Raleway',
                                size: 12
                            },
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            family: 'Raleway',
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            family: 'Raleway',
                            size: 13
                        },
                        callbacks: {
                            label: function(tooltipItem) {
                                const dataset = tooltipItem.dataset;
                                const total = dataset.data.reduce((acc, data) => acc + data, 0);
                                const value = dataset.data[tooltipItem.dataIndex];
                                const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                return `${tooltipItem.label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
        @endif

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
    <script>
        // Hourly Transaction Chart
        const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
        const hourlyChart = new Chart(hourlyCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($formattedHourlyData['labels']) !!},
                datasets: [{
                    label: 'QRIS',
                    data: {!! json_encode($formattedHourlyData['qris']) !!},
                    borderColor: 'rgba(147, 51, 234, 1)',
                    backgroundColor: 'rgba(147, 51, 234, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2
                }, {
                    label: 'Tunai',
                    data: {!! json_encode($formattedHourlyData['tunai']) !!},
                    borderColor: 'rgba(59, 130, 246, 1)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: {
                                family: 'Raleway'
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                family: 'Raleway'
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                family: 'Raleway',
                                size: 12
                            },
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            family: 'Raleway',
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            family: 'Raleway',
                            size: 13
                        }
                    }
                }
            }
        });
    </script>
    @if(isset($topEmployees) && $topEmployees->count() > 0)
    <script>
        // Employee Performance Chart
        const employeeCtx = document.getElementById('employeeChart').getContext('2d');
        const employeeChart = new Chart(employeeCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($topEmployees->pluck('name')->toArray()) !!},
                datasets: [{
                    label: 'QRIS',
                    data: {!! json_encode($topEmployees->pluck('qris_count')->toArray()) !!},
                    backgroundColor: 'rgba(147, 51, 234, 0.8)',
                    borderColor: 'rgba(147, 51, 234, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Tunai',
                    data: {!! json_encode($topEmployees->pluck('tunai_count')->toArray()) !!},
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        stacked: true,
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                family: 'Raleway'
                            }
                        }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: {
                                family: 'Raleway'
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                family: 'Raleway',
                                size: 12
                            },
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            family: 'Raleway',
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            family: 'Raleway',
                            size: 13
                        },
                        callbacks: {
                            afterBody: function(tooltipItems) {
                                const index = tooltipItems[0].dataIndex;
                                const totalTransactions = {!! json_encode($topEmployees->pluck('total_count')->toArray()) !!}[index];
                                const percentage = {!! json_encode($topEmployees->pluck('percentage')->toArray()) !!}[index];
                                return `Total: ${totalTransactions} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    </script>
    @endif
</body>
</html>