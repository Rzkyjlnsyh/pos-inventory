<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Stok - Bblara</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Raleway', sans-serif;
            background-color: #f8fafc;
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
        a:hover .nav-text::after {
            width: 100%;
        }
        .search-animation {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.5); }
            70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
        }
    </style>
</head>
<body>
    <div class="flex">
        <!-- Sidebar Toggle Button -->
        <button class="fixed text-white text-3xl top-5 left-4 p-2 rounded-md bg-gray-700 lg:hidden focus:outline-none z-50" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>

        <!-- Sidebar Component -->
        <x-navbar-owner></x-navbar-owner>

        <!-- Main Content -->
        <div class="flex-1 lg:w-5/6">
            <!-- Top Navigation -->
            <x-navbar-top-owner></x-navbar-top-owner>

            <!-- Main Content Area -->
            <div class="p-4 lg:p-8">
                <div class="max-w-7xl mx-auto">
                    <!-- Header Section -->
                    <div class="flex flex-col md:flex-row justify-between items-center mb-8">
                        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Manajemen Stok</h1>
                        <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto">
                            <!-- Search Bar -->
                            <div class="relative flex-grow md:flex-grow-0">
                                <input type="text" 
                                       placeholder="Cari bahan baku..." 
                                       class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                                       id="searchInput"
                                       onkeyup="filterStocks()">
                                <i class="bi bi-search absolute right-3 top-2.5 text-gray-400"></i>
                            </div>
                            <!-- Add Stock Button -->
                            <a href="{{ route('owner.stock.create') }}" 
                               class="inline-flex items-center justify-center px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors duration-200">
                                <i class="bi bi-plus-lg mr-2"></i>
                                Tambah Stok
                            </a>
                        </div>
                    </div>

                    <!-- Alert Messages -->
                    @if(session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r-lg" role="alert">
                        <div class="flex">
                            <i class="bi bi-check-circle text-xl mr-2"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                    @endif

                    <!-- Stats Overview -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <!-- Total Items Stats -->
                        <div class="bg-white rounded-lg shadow p-6 cursor-pointer" onclick="showAllStocks()">
                            <div class="flex items-center">
                                <div class="p-3 bg-blue-100 rounded-full">
                                    <i class="bi bi-box text-blue-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-gray-500 text-sm">Total Jenis Bahan</h3>
                                    <p class="text-2xl font-semibold">{{ $stocks->count() }}</p>
                                </div>
                            </div>
                        </div>
                        <!-- Total Stock Stats -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="p-3 bg-green-100 rounded-full">
                                    <i class="bi bi-archive text-green-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-gray-500 text-sm">Total Stok Tersedia</h3>
                                    <p class="text-2xl font-semibold">{{ $stocks->sum('qty') }}</p>
                                </div>
                            </div>
                        </div>
                        <!-- Low Stock Warning -->
                        <div class="bg-white rounded-lg shadow p-6 cursor-pointer" onclick="showLowStock()">
                            <div class="flex items-center">
                                <div class="p-3 bg-yellow-100 rounded-full">
                                    <i class="bi bi-exclamation-triangle text-yellow-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-gray-500 text-sm">Stok Menipis</h3>
                                    <p class="text-2xl font-semibold">{{ $stocks->where('qty', '<', 3)->count() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stock List Section with Pagination -->
                    <div class="space-y-6">
                        <!-- Table/List View -->
                        <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200" id="stocksTable">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Bahan Baku</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Berat/Unit</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kuantitas</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Berat</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="stocksTbody" class="divide-y divide-gray-200">
                                    @foreach($stocks as $stock)
                                    <tr class="stock-row hover:bg-gray-50 transition-colors page-transition">
                                        <td class="px-4 py-2 font-medium text-gray-800">
                                            {{ $stock->raw_material }}
                                        </td>
                                        <td class="px-4 py-2 text-gray-500 text-sm">
                                            {{ $stock->id }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ $stock->weight }} {{ $stock->unit }}
                                        </td>
                                        <td class="px-4 py-2">
                                            <div class="flex items-center gap-2">
                                                <form action="{{ route('owner.stock.decrement', $stock->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="p-1 hover:bg-gray-100 rounded" title="Kurangi">
                                                        <i class="bi bi-dash-circle text-red-500"></i>
                                                    </button>
                                                </form>
                                                <span class="font-medium qty-value">{{ $stock->qty }}</span>
                                                <form action="{{ route('owner.stock.increment', $stock->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="p-1 hover:bg-gray-100 rounded" title="Tambah">
                                                        <i class="bi bi-plus-circle text-green-500"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ $stock->weight * $stock->qty }} {{ $stock->unit }}
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <div class="flex justify-center gap-2">
                                                <a href="{{ route('owner.stock.show', $stock) }}" 
                                                   class="inline-flex items-center px-2 py-1 text-blue-600 hover:text-blue-800 rounded transition-colors duration-200 text-sm" title="Lihat Detail">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('owner.stock.edit', $stock) }}" 
                                                   class="inline-flex items-center px-2 py-1 text-yellow-600 hover:text-yellow-800 rounded transition-colors duration-200 text-sm" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button onclick="confirmDelete({{ $stock->id }})" 
                                                   class="inline-flex items-center px-2 py-1 text-red-600 hover:text-red-800 rounded transition-colors duration-200 text-sm" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Controls -->
                        <div class="flex justify-center items-center mt-8 space-x-4">
                            <button onclick="changePage('prev')" 
                                    id="prevButton"
                                    class="flex items-center justify-center w-10 h-10 rounded-full bg-white border border-gray-300 hover:bg-gray-50 transition-colors duration-200">
                                <i class="bi bi-chevron-left text-gray-600"></i>
                            </button>
                            
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-medium text-gray-700">Halaman <span id="pageNumber">1</span></span>
                                <span class="text-sm text-gray-500">dari <span id="totalPages">{{ ceil($stocks->count() / 10) }}</span></span>
                            </div>

                            <button onclick="changePage('next')" 
                                    id="nextButton"
                                    class="flex items-center justify-center w-10 h-10 rounded-full bg-white border border-gray-300 hover:bg-gray-50 transition-colors duration-200">
                                <i class="bi bi-chevron-right text-gray-600"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Delete Form for JS -->
    <form id="deleteForm" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>

    <script>
        // Pagination Parameters
        let currentPage = 1;
        const itemsPerPage = 10; // More natural for list/table
        let stockRows = [];
        let filteredRows = [];
        let currentDeleteId = null;

        document.addEventListener('DOMContentLoaded', function() {
            stockRows = Array.from(document.getElementsByClassName('stock-row'));
            filteredRows = [...stockRows];
            updateDisplay();
            updateButtonStates();
        });

        function filterStocks() {
            const searchInput = document.getElementById('searchInput');
            const filter = searchInput.value.toLowerCase();

            filteredRows = stockRows.filter(row => {
                return row.textContent.toLowerCase().includes(filter);
            });

            currentPage = 1;
            updateDisplay();
            updateButtonStates();
        }

        function changePage(direction) {
            const totalPages = Math.ceil(filteredRows.length / itemsPerPage);
            
            if (direction === 'next' && currentPage < totalPages) {
                currentPage++;
            } else if (direction === 'prev' && currentPage > 1) {
                currentPage--;
            }
            
            updateDisplay();
            updateButtonStates();
        }

        function updateDisplay() {
            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;

            stockRows.forEach(row => row.style.display = 'none');
            filteredRows.slice(start, end).forEach(row => {
                row.style.display = '';
            });

            document.getElementById('pageNumber').textContent = currentPage;
            document.getElementById('totalPages').textContent = Math.max(1, Math.ceil(filteredRows.length / itemsPerPage));
        }

        function updateButtonStates() {
            const prevButton = document.getElementById('prevButton');
            const nextButton = document.getElementById('nextButton');
            const totalPages = Math.ceil(filteredRows.length / itemsPerPage);

            prevButton.disabled = currentPage === 1;
            nextButton.disabled = currentPage === totalPages || totalPages === 0;

            prevButton.classList.toggle('opacity-50', currentPage === 1);
            nextButton.classList.toggle('opacity-50', currentPage === totalPages || totalPages === 0);
        }

        function showLowStock() {
            filteredRows = stockRows.filter(row => {
                const qtyElem = row.querySelector('.qty-value');
                if (!qtyElem) return false;
                const qty = parseInt(qtyElem.textContent.trim());
                return qty < 3;
            });
            currentPage = 1;
            updateDisplay();
            updateButtonStates();
        }

        function showAllStocks() {
            filteredRows = [...stockRows];
            currentPage = 1;
            updateDisplay();
            updateButtonStates();
        }

        // Confirm delete
        function confirmDelete(stockId) {
            if (confirm('Apakah Anda yakin ingin menghapus stok ini?')) {
                const form = document.getElementById('deleteForm');
                form.action = `/owner/stock/${stockId}`;
                form.submit();
            }
        }
    </script>
    <script>
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