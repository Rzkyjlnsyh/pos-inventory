<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Beranda - Bblara</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" />
    {{-- Font Cdn --}}
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
      body{
        font-family: 'Raleway', sans-serif;
      }
      #progressBar {
        background-color: #005281;
        transition: width 0.4s ease-in-out;
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
    </style>
  </head>
  <body class="bg-gray-100">
    <div class="flex">
      <!-- Toggle Button for Sidebar -->
      <button
        class="fixed text-white text-3xl top-5 left-4 p-2 rounded-md bg-gray-700 lg:hidden focus:outline-none z-50"
        onclick="toggleSidebar()"
      >
        <i class="bi bi-list"></i>
      </button>

      <!-- Sidebar -->
      <x-navbar-owner></x-navbar-owner>

      <!-- Main Content -->
      <div class="flex-1 lg:w-5/6">
        {{-- Navbar Top --}}
        <x-navbar-top-owner></x-navbar-top-owner>

        <!-- Content Wrapper -->
        <div class="p-4 lg:p-8">
          <!-- Welcome Message - Improved Version -->
          <div class="bg-white p-6 rounded-xl shadow-lg mb-6 relative overflow-hidden">
            <!-- Background Pattern -->
            <div class="absolute right-0 top-0 w-32 h-32 bg-[#005281]/5 rounded-bl-full"></div>
            
            <div class="flex items-center gap-6 relative">
                <div class="flex items-center justify-center w-16 h-16 bg-[#005281]/10 rounded-full">
                    <i class="bi bi-person-circle text-4xl text-[#005281]"></i>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <h2 class="text-2xl font-semibold text-gray-700">
                            Halo {{ ucfirst(Auth::user()->name) }}!
                        </h2>
                        <span class="px-3 py-1 text-sm bg-[#005281]/10 text-[#005281] rounded-full">
                            Owner
                        </span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <i class="bi bi-clock"></i>
                        <span>{{ now()->format('l, d F Y') }}</span>
                    </div>
                    <p class="text-gray-600 text-sm">
                        Selamat datang di dashboard untuk melihat data real-time dan performa bisnis Anda.
                    </p>
                </div>
            </div>
          </div>

          <!-- Date Range Selector - Improved Version -->
          <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
            <div class="space-y-6">
                <!-- Preset Buttons with Better Layout -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Filter Periode</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
                        <button 
                            onclick="applyPreset('today')" 
                            data-preset="today"
                            class="relative px-4 py-2.5 text-sm font-medium text-white bg-[#005281] rounded-lg 
                                  hover:bg-[#003d61] focus:outline-none focus:ring-2 focus:ring-[#005281] focus:ring-offset-2 
                                  transition-all duration-200 ease-in-out shadow-sm hover:shadow group"
                        >
                            <div class="flex items-center justify-center gap-2">
                                <i class="bi bi-calendar-event"></i>
                                <span>Hari Ini</span>
                            </div>
                        </button>
                        <button 
                            onclick="applyPreset('yesterday')" 
                            data-preset="yesterday"
                            class="relative px-4 py-2.5 text-sm font-medium text-white bg-[#005281] rounded-lg 
                                  hover:bg-[#003d61] focus:outline-none focus:ring-2 focus:ring-[#005281] focus:ring-offset-2 
                                  transition-all duration-200 ease-in-out shadow-sm hover:shadow group"
                        >
                            <div class="flex items-center justify-center gap-2">
                                <i class="bi bi-calendar-minus"></i>
                                <span>Kemarin</span>
                            </div>
                        </button>
                        <button 
                            onclick="applyPreset('last7days')" 
                            data-preset="last7days"
                            class="relative px-4 py-2.5 text-sm font-medium text-white bg-[#005281] rounded-lg 
                                  hover:bg-[#003d61] focus:outline-none focus:ring-2 focus:ring-[#005281] focus:ring-offset-2 
                                  transition-all duration-200 ease-in-out shadow-sm hover:shadow group"
                        >
                            <div class="flex items-center justify-center gap-2">
                                <i class="bi bi-calendar-week"></i>
                                <span>7 Hari</span>
                            </div>
                        </button>
                        <button 
                            onclick="applyPreset('last30days')" 
                            data-preset="last30days"
                            class="relative px-4 py-2.5 text-sm font-medium text-white bg-[#005281] rounded-lg 
                                  hover:bg-[#003d61] focus:outline-none focus:ring-2 focus:ring-[#005281] focus:ring-offset-2 
                                  transition-all duration-200 ease-in-out shadow-sm hover:shadow group"
                        >
                            <div class="flex items-center justify-center gap-2">
                                <i class="bi bi-calendar3"></i>
                                <span>30 Hari</span>
                            </div>
                        </button>
                        <button 
                            onclick="applyPreset('thisMonth')" 
                            data-preset="thisMonth"
                            class="relative px-4 py-2.5 text-sm font-medium text-white bg-[#005281] rounded-lg 
                                  hover:bg-[#003d61] focus:outline-none focus:ring-2 focus:ring-[#005281] focus:ring-offset-2 
                                  transition-all duration-200 ease-in-out shadow-sm hover:shadow group"
                        >
                            <div class="flex items-center justify-center gap-2">
                                <i class="bi bi-calendar-check"></i>
                                <span>Bulan Ini</span>
                            </div>
                        </button>
                        <button 
                            onclick="applyPreset('lastMonth')" 
                            data-preset="lastMonth"
                            class="relative px-4 py-2.5 text-sm font-medium text-white bg-[#005281] rounded-lg 
                                  hover:bg-[#003d61] focus:outline-none focus:ring-2 focus:ring-[#005281] focus:ring-offset-2 
                                  transition-all duration-200 ease-in-out shadow-sm hover:shadow group"
                        >
                            <div class="flex items-center justify-center gap-2">
                                <i class="bi bi-calendar2-check"></i>
                                <span>Bulan Lalu</span>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Custom Date Range with Better Design -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Pilih Tanggal Kustom</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="relative">
                            <label class="block text-gray-700 text-sm font-medium mb-2">Tanggal Awal</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="bi bi-calendar3 text-gray-400"></i>
                                </div>
                                <input 
                                    type="date" 
                                    id="startDate" 
                                    class="block w-full pl-10 pr-4 py-2.5 text-gray-700 bg-white border border-gray-300 
                                          rounded-lg focus:outline-none focus:ring-2 focus:ring-[#005281] focus:border-[#005281]
                                          transition-colors duration-200"
                                    onchange="handleDateChange()"
                                >
                            </div>
                        </div>
                        <div class="relative">
                            <label class="block text-gray-700 text-sm font-medium mb-2">Tanggal Akhir</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="bi bi-calendar3 text-gray-400"></i>
                                </div>
                                <input 
                                    type="date" 
                                    id="endDate" 
                                    class="block w-full pl-10 pr-4 py-2.5 text-gray-700 bg-white border border-gray-300 
                                          rounded-lg focus:outline-none focus:ring-2 focus:ring-[#005281] focus:border-[#005281]
                                          transition-colors duration-200"
                                    onchange="handleDateChange()"
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
          </div>

          <!-- Stats Cards -->
          <div class="stats-cards grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            <!-- Card 1: Total Modal -->
            <div class="stats-card bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 relative overflow-hidden">
                <div class="flex justify-between items-start">
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <div class="p-2 bg-[#005281]/10 rounded-lg">
                                <i class="bi bi-cart text-2xl text-[#005281]"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-700">Total Modal</h3>
                        </div>
                        
                        <p class="text-3xl font-bold text-[#005281]" id="totalModal">
                            Rp{{ number_format($todayModal, 0, ',', '.') }}
                        </p>
                        
                        <div class="flex flex-col gap-1">
                            <p id="modalPercentage" class="text-{{ $modalPercentage >= 0 ? 'green' : 'red' }}-500 text-sm flex items-center gap-2">
                                <i class="bi {{ $modalPercentage >= 0 ? 'bi-arrow-up-circle-fill' : 'bi-arrow-down-circle-fill' }}"></i>
                                {{ number_format($modalPercentage, 1) }}%
                            </p>
                            <p class="text-gray-500 text-sm">
                                ({{ $modalDiff >= 0 ? '+' : '' }}Rp{{ number_format($modalDiff, 0, ',', '.') }})
                                <span class="text-gray-400">dibanding periode sebelumnya</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Total Pendapatan -->
            <div class="stats-card bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 relative overflow-hidden">
                <div class="flex justify-between items-start">
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <div class="p-2 bg-[#0077b6]/10 rounded-lg">
                                <i class="bi bi-bar-chart text-2xl text-[#0077b6]"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-700">Total Pendapatan</h3>
                        </div>
                        
                        <p class="text-3xl font-bold text-[#0077b6]" id="totalPendapatan">
                            Rp{{ number_format($todayPendapatan, 0, ',', '.') }}
                        </p>
                        
                        <div class="flex flex-col gap-1">
                            <p id="pendapatanPercentage" class="text-{{ $pendapatanPercentage >= 0 ? 'green' : 'red' }}-500 text-sm flex items-center gap-2">
                                <i class="bi {{ $pendapatanPercentage >= 0 ? 'bi-arrow-up-circle-fill' : 'bi-arrow-down-circle-fill' }}"></i>
                                {{ number_format($pendapatanPercentage, 1) }}%
                            </p>
                            <p class="text-gray-500 text-sm">
                                ({{ $pendapatanDiff >= 0 ? '+' : '' }}Rp{{ number_format($pendapatanDiff, 0, ',', '.') }})
                                <span class="text-gray-400">dibanding periode sebelumnya</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Total Keuntungan -->
            <div class="stats-card bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 relative overflow-hidden">
                <div class="flex justify-between items-start">
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <div class="p-2 bg-[#00b4d8]/10 rounded-lg">
                                <i class="bi bi-cash-stack text-2xl text-[#00b4d8]"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-700">Total Keuntungan</h3>
                        </div>
                        
                        <p class="text-3xl font-bold text-[#00b4d8]" id="totalKeuntungan">
                            Rp{{ number_format($todayKeuntungan, 0, ',', '.') }}
                        </p>
                        
                        <div class="flex flex-col gap-1">
                            <p id="keuntunganPercentage" class="text-{{ $keuntunganPercentage >= 0 ? 'green' : 'red' }}-500 text-sm flex items-center gap-2">
                                <i class="bi {{ $keuntunganPercentage >= 0 ? 'bi-arrow-up-circle-fill' : 'bi-arrow-down-circle-fill' }}"></i>
                                {{ number_format($keuntunganPercentage, 1) }}%
                            </p>
                            <p class="text-gray-500 text-sm">
                                ({{ $keuntunganDiff >= 0 ? '+' : '' }}Rp{{ number_format($keuntunganDiff, 0, ',', '.') }})
                                <span class="text-gray-400">dibanding periode sebelumnya</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
          </div>

          <!-- Chart Container -->
          <div class="chart-container bg-white p-4 rounded shadow mb-4">
            <canvas id="comboChart" class="w-full" style="height: 400px;"></canvas>
          </div>

          <!-- To-Do List and Progress Section -->
          <div class="flex flex-col md:flex-row gap-6 mb-6">
            <!-- Card 1: To-Do List -->
            <div class="w-full md:w-1/2 bg-white p-6 rounded-xl shadow-lg">
                <div class="flex justify-between items-center mb-6">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-[#005281]/10 rounded-lg">
                            <i class="bi bi-list-check text-xl text-[#005281]"></i>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-700">To-Do List</h2>
                    </div>
                    <button onclick="clearCompletedTasks()" 
                            class="text-sm text-[#005281] hover:text-[#003d61] transition-colors duration-200">
                        Clear Completed
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="todo-item group">
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" 
                                  id="task1" 
                                  class="form-checkbox h-5 w-5 text-[#005281] border-gray-300 rounded 
                                          focus:ring-2 focus:ring-[#005281] transition-colors duration-200" 
                                  onchange="updateProgress()">
                            <div class="ml-4 flex-1">
                                <span class="text-gray-700 font-medium group-hover:text-[#005281] transition-colors">
                                    Buka Toko
                                </span>
                                <p class="text-sm text-gray-500">Pukul 10:00 WIB</p>
                            </div>
                            <span class="px-2.5 py-0.5 text-sm bg-blue-100 text-blue-800 rounded-full">Pagi</span>
                        </label>
                    </div>

                    <div class="todo-item group">
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" 
                                  id="task2" 
                                  class="form-checkbox h-5 w-5 text-[#005281] border-gray-300 rounded 
                                          focus:ring-2 focus:ring-[#005281] transition-colors duration-200" 
                                  onchange="updateProgress()">
                            <div class="ml-4 flex-1">
                                <span class="text-gray-700 font-medium group-hover:text-[#005281] transition-colors">
                                    Persiapan
                                </span>
                                <p class="text-sm text-gray-500">Bersih-bersih & Prepare</p>
                            </div>
                            <span class="px-2.5 py-0.5 text-sm bg-yellow-100 text-yellow-800 rounded-full">Prioritas</span>
                        </label>
                    </div>

                    <div class="todo-item group">
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" 
                                  id="task3" 
                                  class="form-checkbox h-5 w-5 text-[#005281] border-gray-300 rounded 
                                          focus:ring-2 focus:ring-[#005281] transition-colors duration-200" 
                                  onchange="updateProgress()">
                            <div class="ml-4 flex-1">
                                <span class="text-gray-700 font-medium group-hover:text-[#005281] transition-colors">
                                    Istirahat
                                </span>
                                <p class="text-sm text-gray-500">Jam Makan Siang</p>
                            </div>
                            <span class="px-2.5 py-0.5 text-sm bg-green-100 text-green-800 rounded-full">Siang</span>
                        </label>
                    </div>

                    <div class="todo-item group">
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" 
                                  id="task4" 
                                  class="form-checkbox h-5 w-5 text-[#005281] border-gray-300 rounded 
                                          focus:ring-2 focus:ring-[#005281] transition-colors duration-200" 
                                  onchange="updateProgress()">
                            <div class="ml-4 flex-1">
                                <span class="text-gray-700 font-medium group-hover:text-[#005281] transition-colors">
                                    Tutup Toko
                                </span>
                                <p class="text-sm text-gray-500">Pukul 10:00 WIB</p>
                            </div>
                            <span class="px-2.5 py-0.5 text-sm bg-purple-100 text-purple-800 rounded-full">Malam</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Card 2: Progress Bar -->
            <div class="w-full md:w-1/2 bg-white p-6 rounded-xl shadow-lg">
              <div class="flex items-center gap-3 mb-6">
                  <div class="p-2 bg-[#005281]/10 rounded-lg">
                      <i class="bi bi-graph-up text-xl text-[#005281]"></i>
                  </div>
                  <h2 class="text-lg font-semibold text-gray-700">Progress Hari Ini</h2>
              </div>

              <div class="space-y-6">
                  <div class="flex justify-between items-center">
                      <div>
                          <h3 class="text-sm font-medium text-gray-700">Penyelesaian Tugas</h3>
                          <p class="text-xs text-gray-500">Total 4 tugas hari ini</p>
                      </div>
                      <span id="progressPercentage" class="text-lg font-semibold text-[#005281]">0%</span>
                  </div>

                  <div class="relative">
                      <div class="w-full bg-gray-200 rounded-full h-3">
                          <div id="progressBar" 
                              class="bg-gradient-to-r from-[#005281] to-[#0077b6] h-3 rounded-full 
                                      transition-all duration-500 ease-in-out shadow-sm" 
                              style="width: 0%">
                          </div>
                      </div>
                  </div>

                  <div class="grid grid-cols-2 gap-4">
                      <div class="text-center p-3 bg-gray-50 rounded-lg">
                          <p class="text-sm text-gray-500">Selesai</p>
                          <p id="completedTasks" class="text-xl font-semibold text-[#005281]">0</p>
                      </div>
                      <div class="text-center p-3 bg-gray-50 rounded-lg">
                          <p class="text-sm text-gray-500">Pending</p>
                          <p id="pendingTasks" class="text-xl font-semibold text-gray-700">4</p>
                      </div>
                  </div>

                  <!-- Quote of the Day Section -->
                  <div class="mt-6 p-4 bg-[#005281]/5 rounded-xl">
                      <div class="flex items-start gap-3">
                          <i class="bi bi-quote text-3xl text-[#005281]/70"></i>
                          <div class="space-y-2">
                              <p id="quoteText" class="text-gray-600 text-sm italic">
                                  "Sukses adalah hasil dari kesempurnaan, kerja keras, belajar dari pengalaman, loyalitas, dan ketekunan."
                              </p>
                              <p id="quoteAuthor" class="text-sm font-medium text-[#005281]">- Colin Powell</p>
                          </div>
                      </div>
                  </div>
              </div>
            </div>
          </div>

          <!-- Quick Navigation Cards -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
              <!-- Produk -->
              <a href="{{ route('owner.product.index') }}"
                class="group bg-white p-5 rounded-xl shadow-lg hover:shadow-xl transition flex items-center justify-between cursor-pointer border border-transparent hover:border-[#005281]">
                  <div class="flex items-center gap-4">
                      <span class="inline-flex items-center justify-center w-14 h-14 bg-blue-100 rounded-full">
                          <i class="bi bi-box-seam text-2xl text-blue-700"></i>
                      </span>
                      <div>
                          <p class="text-gray-800 font-semibold text-lg">Produk</p>
                          <span class="text-gray-500 text-sm">Kelola Produk</span>
                      </div>
                  </div>
                  <span class="ml-4 flex-shrink-0">
                      <span class="inline-flex items-center justify-center w-10 h-10 bg-blue-50 rounded-full group-hover:bg-blue-200 transition">
                          <i class="bi bi-arrow-right text-xl text-blue-700"></i>
                      </span>
                  </span>
              </a>
              <!-- Stok -->
              <a href="{{ route('owner.stock.index') }}"
                class="group bg-white p-5 rounded-xl shadow-lg hover:shadow-xl transition flex items-center justify-between cursor-pointer border border-transparent hover:border-[#005281]">
                  <div class="flex items-center gap-4">
                      <span class="inline-flex items-center justify-center w-14 h-14 bg-yellow-100 rounded-full">
                          <i class="bi bi-archive text-2xl text-yellow-700"></i>
                      </span>
                      <div>
                          <p class="text-gray-800 font-semibold text-lg">Stok</p>
                          <span class="text-gray-500 text-sm">Manajemen Stok</span>
                      </div>
                  </div>
                  <span class="ml-4 flex-shrink-0">
                      <span class="inline-flex items-center justify-center w-10 h-10 bg-yellow-50 rounded-full group-hover:bg-yellow-200 transition">
                          <i class="bi bi-arrow-right text-xl text-yellow-700"></i>
                      </span>
                  </span>
              </a>
              <!-- Laporan -->
              <a href="{{ route('owner.report.index') }}"
                class="group bg-white p-5 rounded-xl shadow-lg hover:shadow-xl transition flex items-center justify-between cursor-pointer border border-transparent hover:border-[#005281]">
                  <div class="flex items-center gap-4">
                      <span class="inline-flex items-center justify-center w-14 h-14 bg-green-100 rounded-full">
                          <i class="bi bi-graph-up text-2xl text-green-700"></i>
                      </span>
                      <div>
                          <p class="text-gray-800 font-semibold text-lg">Laporan</p>
                          <span class="text-gray-500 text-sm">Ringkasan Bisnis</span>
                      </div>
                  </div>
                  <span class="ml-4 flex-shrink-0">
                      <span class="inline-flex items-center justify-center w-10 h-10 bg-green-50 rounded-full group-hover:bg-green-200 transition">
                          <i class="bi bi-arrow-right text-xl text-green-700"></i>
                      </span>
                  </span>
              </a>
              <!-- Kasir -->
              <a href="{{ route('owner.cashier.index') }}"
                class="group bg-white p-5 rounded-xl shadow-lg hover:shadow-xl transition flex items-center justify-between cursor-pointer border border-transparent hover:border-[#005281]">
                  <div class="flex items-center gap-4">
                      <span class="inline-flex items-center justify-center w-14 h-14 bg-purple-100 rounded-full">
                          <i class="bi bi-basket3"></i>
                      </span>
                      <div>
                          <p class="text-gray-800 font-semibold text-lg">Kasir</p>
                          <span class="text-gray-500 text-sm">Transaksi Penjualan</span>
                      </div>
                  </div>
                  <span class="ml-4 flex-shrink-0">
                      <span class="inline-flex items-center justify-center w-10 h-10 bg-purple-50 rounded-full group-hover:bg-purple-200 transition">
                          <i class="bi bi-arrow-right text-xl text-purple-700"></i>
                      </span>
                  </span>
              </a>
          </div>

          <!-- Footer -->
          <footer class="text-gray-400 text-center py-4 mt-8">
            <p>Copyright &#169; 2024 Bblaratech3. All Rights Reserved.</p>
          </footer>
        </div>
      </div>
    </div>

    <!-- Scripts -->
    <script>
      const monthlyData = @json($monthlyData);
      let myChart;
      let currentPreset = 'today';

      // Chart configuration
      const chartConfig = {
        type: 'bar',
        data: {
          labels: Object.keys(monthlyData),
          datasets: [
            {
              label: 'Total Modal',
              data: Object.values(monthlyData).map(data => data.modal),
              backgroundColor: 'rgba(255, 182, 193, 0.6)',
              borderColor: 'rgba(255, 182, 193, 1)',
              borderWidth: 1
            },
            {
              label: 'Total Pendapatan',
              data: Object.values(monthlyData).map(data => data.pendapatan),
              backgroundColor: 'rgba(173, 216, 230, 0.6)',
              borderColor: 'rgba(173, 216, 230, 1)',
              borderWidth: 1
            },
            {
              label: 'Total Keuntungan',
              type: 'line',
              data: Object.values(monthlyData).map(data => data.keuntungan),
              borderColor: 'rgba(144, 238, 144, 1)',
              backgroundColor: 'rgba(144, 238, 144, 0.2)',
              borderWidth: 2,
              fill: true
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          animation: {
            duration: 750,
            easing: 'easeInOutQuart'
          },
          scales: {
            x: {
              grid: {
                display: false
              }
            },
            y: {
              beginAtZero: true,
              ticks: {
                callback: function(value) {
                  return 'Rp' + new Intl.NumberFormat('id-ID').format(value);
                }
              }
            }
          },
          plugins: {
            legend: {
              display: true,
              position: 'top'
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  return context.dataset.label + ': Rp' + new Intl.NumberFormat('id-ID').format(context.raw);
                }
              }
            }
          }
        }
      };

      // Initialize chart
      document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('comboChart').getContext('2d');
        myChart = new Chart(ctx, chartConfig);
        setDefaultDates();
      });

      // Set default dates
      function setDefaultDates() {
        const today = new Date();
        document.getElementById('endDate').value = today.toISOString().split('T')[0];
        document.getElementById('startDate').value = today.toISOString().split('T')[0];
        applyPreset('today');
      }

      // Handle date change
      function handleDateChange() {
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        
        if (startDate && endDate) {
          updateData();
          resetPresetButtons();
        }
      }

      // Apply preset
      function applyPreset(preset) {
        currentPreset = preset;
        updatePresetButtons(preset);
        updateData(preset);
      }

      // Update preset buttons
      function updatePresetButtons(activePreset) {
        document.querySelectorAll('.date-btn').forEach(btn => {
          btn.classList.remove('active');
          if (btn.dataset.preset === activePreset) {
            btn.classList.add('active');
          }
        });
      }

      // Reset preset buttons
      function resetPresetButtons() {
        document.querySelectorAll('.date-btn').forEach(btn => {
          btn.classList.remove('active');
        });
      }

      // Update data
      function updateData(preset = null) {
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;

        let url = '/api/dashboard-data';
        let params = new URLSearchParams();

        if (preset) {
          params.append('preset', preset);
        } else {
          if (!startDate || !endDate) {
            alert('Harap pilih rentang tanggal terlebih dahulu.');
            return;
          }
          params.append('start', startDate);
          params.append('end', endDate);
        }

        // Show loading state
        document.body.style.cursor = 'wait';

        fetch(`${url}?${params.toString()}`)
          .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
          })
          .then(data => {
            if (!data) throw new Error('Data kosong');
            updateCards(data);
            if (data.monthlyData) {
              updateChart(data.monthlyData);
            }
          })
          .catch(error => {
            console.error('Error updating dashboard:', error);
            alert('Terjadi kesalahan saat memperbarui data. Silakan coba lagi.');
          })
          .finally(() => {
            document.body.style.cursor = 'default';
          });
      }

      // Update cards
      function updateCards(data) {
          if (!data) return;

          const formatCurrency = (value) => 'Rp' + new Intl.NumberFormat('id-ID').format(value || 0);
          const formatPercentage = (value) => value ? value.toFixed(2) : '0.00';

          ['Modal', 'Pendapatan', 'Keuntungan'].forEach(type => {
              const total = document.querySelector(`#total${type}`);
              const percentage = document.querySelector(`#${type.toLowerCase()}Percentage`);
              const lowerType = type.toLowerCase();
              
              if (total) {
                  total.textContent = formatCurrency(data[`today${type}`]);
              }
              
              if (percentage) {
                  const percentValue = data[`${lowerType}Percentage`] || 0;
                  const diffValue = data[`${lowerType}Diff`] || 0;
                  const isPositive = percentValue >= 0;
                  
                  percentage.className = `text-${isPositive ? 'green' : 'red'}-500 text-sm mb-2`;
                  percentage.textContent = `${formatPercentage(percentValue)}% (${isPositive ? '+' : ''}${formatCurrency(diffValue)}) dibanding periode sebelumnya`;
              }
          });
      }

      // Update chart
      function updateChart(newData) {
        if (!myChart) return;

        myChart.data.labels = Object.keys(newData);
        myChart.data.datasets.forEach((dataset, index) => {
          const type = ['modal', 'pendapatan', 'keuntungan'][index];
          dataset.data = Object.values(newData).map(data => data[type]);
        });
        
        myChart.update('active');
      }

      function updatePresetButtons(activePreset) {
        document.querySelectorAll('[data-preset]').forEach(btn => {
          if (btn.dataset.preset === activePreset) {
            btn.classList.add('bg-[#003d61]');
            btn.classList.remove('bg-[#005281]');
          } else {
            btn.classList.remove('bg-[#003d61]');
            btn.classList.add('bg-[#005281]');
          }
        });
      }

      function resetPresetButtons() {
        document.querySelectorAll('[data-preset]').forEach(btn => {
          btn.classList.remove('bg-[#003d61]');
          btn.classList.add('bg-[#005281]');
        });
      }

      // Toggle sidebar for mobile
      function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('hidden');
      }
    </script>
    <script>
      // Save todo state in localStorage
      function saveTodoState() {
        const tasks = document.querySelectorAll('.form-checkbox');
        const todoState = Array.from(tasks).map(task => task.checked);
        localStorage.setItem('todoState', JSON.stringify(todoState));
      }

      // Load todo state from localStorage
      function loadTodoState() {
        const savedState = localStorage.getItem('todoState');
        if (savedState) {
          const todoState = JSON.parse(savedState);
          const tasks = document.querySelectorAll('.form-checkbox');
          tasks.forEach((task, index) => {
            task.checked = todoState[index];
          });
          updateProgress();
        }
      }

      // Update progress bar
      function updateProgress() {
        const tasks = document.querySelectorAll('.form-checkbox');
        const completedTasks = Array.from(tasks).filter(task => task.checked).length;
        const totalTasks = tasks.length;
        const progress = (completedTasks / totalTasks) * 100;
        
        const progressBar = document.getElementById('progressBar');
        progressBar.style.width = `${progress}%`;
        
        // Save state after update
        saveTodoState();
      }

      // Add to DOMContentLoaded
      document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('comboChart').getContext('2d');
        myChart = new Chart(ctx, chartConfig);
        setDefaultDates();
        loadTodoState(); // Load saved todo state
      });
    </script>
    <script>
      function toggleDropdown(button) {
      const dropdownMenus = document.querySelectorAll(".dropdown-menu");
      const dropdownArrows = document.querySelectorAll("i.bi-chevron-down");

      // Tutup semua dropdown kecuali yang dipilih
      dropdownMenus.forEach((menu) => {
        if (menu !== button.nextElementSibling) {
          menu.classList.add("max-h-0");
          menu.classList.remove("max-h-40");
        }
      });

      // Atur semua panah kecuali yang dipilih
      dropdownArrows.forEach((arrow) => {
        if (arrow !== button.querySelector("i.bi-chevron-down")) {
          arrow.classList.remove("rotate-180");
        }
      });

      // Toggle dropdown yang dipilih
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
      function updateProgress() {
          const tasks = document.querySelectorAll('.form-checkbox');
          const completedTasks = Array.from(tasks).filter(task => task.checked).length;
          const totalTasks = tasks.length;
          const progress = (completedTasks / totalTasks) * 100;
          
          const progressBar = document.getElementById('progressBar');
          const progressPercentage = document.getElementById('progressPercentage');
          const completedTasksElement = document.getElementById('completedTasks');
          const pendingTasksElement = document.getElementById('pendingTasks');
          
          progressBar.style.width = `${progress}%`;
          progressPercentage.textContent = `${Math.round(progress)}%`;
          completedTasksElement.textContent = completedTasks;
          pendingTasksElement.textContent = totalTasks - completedTasks;
          
          saveTodoState();
      }
  
      function clearCompletedTasks() {
          const tasks = document.querySelectorAll('.form-checkbox');
          tasks.forEach(task => {
              if (task.checked) {
                  task.checked = false;
              }
          });
          updateProgress();
      }
  </script>
  <script>
    const quotes = [
        {
            text: "Sukses adalah hasil dari kesempurnaan, kerja keras, belajar dari pengalaman, loyalitas, dan ketekunan.",
            author: "Colin Powell"
        },
        {
            text: "Jangan pernah menyerah. Hari ini mungkin sulit, besok mungkin lebih sulit, tapi lusa akan indah.",
            author: "Jack Ma"
        },
        {
            text: "Rahasia kesuksesan adalah melakukan hal yang biasa secara luar biasa.",
            author: "John D. Rockefeller"
        },
        {
            text: "Kesuksesan bisnis bukan tentang keberuntungan. Ini tentang melihat peluang dan mengambil tindakan.",
            author: "Richard Branson"
        },
        {
            text: "Jangan takut dengan kegagalan. Ketakutan membuat kita tidak berani mencoba.",
            author: "Walt Disney"
        },
        {
            text: "Bermimpilah seakan kau akan hidup selamanya. Hiduplah seakan kau akan mati hari ini.",
            author: "James Dean"
        },
        {
            text: "Kesuksesan adalah perjalanan, bukan tujuan. Proses adalah yang terpenting.",
            author: "Arthur Ashe"
        }
    ];

    function updateQuote() {
        const randomIndex = Math.floor(Math.random() * quotes.length);
        const quote = quotes[randomIndex];
        
        document.getElementById('quoteText').textContent = `"${quote.text}"`;
        document.getElementById('quoteAuthor').textContent = `- ${quote.author}`;
    }

    // Update quote when page loads
    document.addEventListener('DOMContentLoaded', updateQuote);

    // Update quote every 24 hours
    setInterval(updateQuote, 24 * 60 * 60 * 1000);
  </script>
  </body>
</html>