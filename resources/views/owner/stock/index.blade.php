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
        .cursor-pointer:hover {
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
            transform: translateY(-3px);
            transition: all 0.3s ease;
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
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .search-animation {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.5); }
            70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
        }
        .page-transition {
            transition: all 0.3s ease-in-out;
        }
        .fade-enter {
            opacity: 0;
            transform: translateY(10px);
        }
        .fade-enter-active {
            opacity: 1;
            transform: translateY(0);
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

                    <!-- Stock Cards Section with Pagination -->
                    <div class="space-y-6">
                        <!-- Cards Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="stocksGrid">
                            @foreach($stocks as $stock)
                            <div class="bg-white rounded-lg shadow-sm card-hover stock-card page-transition">
                                <div class="p-6">
                                    <!-- Card Header -->
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-800">{{ $stock->raw_material }}</h3>
                                            <p class="text-sm text-gray-500">ID: {{ $stock->id }}</p>
                                        </div>
                                        <div class="flex gap-2">
                                            <button onclick="showStockActions({{ $stock->id }})" 
                                                    class="text-gray-400 hover:text-gray-600">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Card Content -->
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-600">Berat per Unit:</span>
                                            <span class="font-medium">{{ $stock->weight }} {{ $stock->unit }}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-600">Kuantitas:</span>
                                            <div class="flex items-center gap-3">
                                                <form action="{{ route('owner.stock.decrement', $stock->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="p-1 hover:bg-gray-100 rounded">
                                                        <i class="bi bi-dash-circle text-red-500"></i>
                                                    </button>
                                                </form>
                                                <span class="font-medium">{{ $stock->qty }}</span>
                                                <form action="{{ route('owner.stock.increment', $stock->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="p-1 hover:bg-gray-100 rounded">
                                                        <i class="bi bi-plus-circle text-green-500"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-600">Total Berat:</span>
                                            <span class="font-medium">{{ $stock->weight * $stock->qty }} {{ $stock->unit }}</span>
                                        </div>
                                    </div>

                                    <!-- Card Actions -->
                                    <div class="mt-6 flex gap-2">
                                        <a href="{{ route('owner.stock.show', $stock) }}" 
                                           class="flex-1 inline-flex justify-center items-center px-4 py-2 text-sm text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-md transition-colors duration-200">
                                            <i class="bi bi-eye mr-2"></i>
                                            Detail
                                        </a>
                                        <a href="{{ route('owner.stock.edit', $stock) }}" 
                                           class="flex-1 inline-flex justify-center items-center px-4 py-2 text-sm text-yellow-600 bg-yellow-50 hover:bg-yellow-100 rounded-md transition-colors duration-200">
                                            <i class="bi bi-pencil mr-2"></i>
                                            Edit
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
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
                                <span class="text-sm text-gray-500">dari <span id="totalPages">{{ ceil($stocks->count() / 3) }}</span></span>
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

    <!-- Stock Actions Modal -->
    <div id="stockActionsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Aksi Stok</h3>
                    <div class="space-y-3">
                        <button onclick="viewStock()" class="w-full text-left px-4 py-2 hover:bg-gray-100 rounded-md">
                            <i class="bi bi-eye mr-2"></i> Lihat Detail
                        </button>
                        <button onclick="editStock()" class="w-full text-left px-4 py-2 hover:bg-gray-100 rounded-md">
                            <i class="bi bi-pencil mr-2"></i> Edit Stok
                        </button>
                        <button onclick="deleteStock()" class="w-full text-left px-4 py-2 hover:bg-gray-100 rounded-md text-red-600">
                            <i class="bi bi-trash mr-2"></i> Hapus Stok
                        </button>
                    </div>
                </div>
                <div class="border-t px-6 py-4">
                    <button onclick="closeStockActions()" class="w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-md">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global Variables
        let currentPage = 1;
        const itemsPerPage = 3;
        let stockCards = [];
        let currentStockId = null;
        let filteredCards = [];

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            initializePagination();
            updateButtonStates();
        });

        // Sidebar Toggle
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('-translate-x-full');
        }

        // Stock Filter
        function filterStocks() {
            const searchInput = document.getElementById('searchInput');
            const filter = searchInput.value.toLowerCase();
            
            filteredCards = Array.from(stockCards).filter(card => {
                const text = card.textContent.toLowerCase();
                return text.includes(filter);
            });

            currentPage = 1;
            updateDisplay();
            updateButtonStates();
        }

        // Pagination Functions
        function initializePagination() {
            stockCards = Array.from(document.getElementsByClassName('stock-card'));
            filteredCards = [...stockCards];
            updateDisplay();
        }

        function changePage(direction) {
            const totalPages = Math.ceil(filteredCards.length / itemsPerPage);
            
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
            
            stockCards.forEach(card => card.style.display = 'none');
            
            filteredCards.slice(start, end).forEach((card, index) => {
                card.style.display = '';
                card.style.opacity = '0';
                setTimeout(() => {
                    card.style.transition = 'opacity 0.3s ease-in-out';
                    card.style.opacity = '1';
                }, 50 * index);
            });
            
            document.getElementById('pageNumber').textContent = currentPage;
            document.getElementById('totalPages').textContent = Math.ceil(filteredCards.length / itemsPerPage);
        }

        function updateButtonStates() {
            const prevButton = document.getElementById('prevButton');
            const nextButton = document.getElementById('nextButton');
            const totalPages = Math.ceil(filteredCards.length / itemsPerPage);

            prevButton.disabled = currentPage === 1;
            nextButton.disabled = currentPage === totalPages;

            prevButton.classList.toggle('opacity-50', currentPage === 1);
            nextButton.classList.toggle('opacity-50', currentPage === totalPages);
        }

        // Stock Actions Functions
        function showStockActions(stockId) {
            currentStockId = stockId;
            document.getElementById('stockActionsModal').classList.remove('hidden');
        }

        function closeStockActions() {
            document.getElementById('stockActionsModal').classList.add('hidden');
            currentStockId = null;
        }

        function viewStock() {
            if (currentStockId) {
                window.location.href = `/owner/stock/${currentStockId}`;
            }
        }

        function editStock() {
            if (currentStockId) {
                window.location.href = `/owner/stock/${currentStockId}/edit`;
            }
        }

        function deleteStock() {
            if (currentStockId && confirm('Apakah Anda yakin ingin menghapus stok ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/owner/stock/${currentStockId}`;
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';
                
                form.appendChild(csrfToken);
                form.appendChild(methodField);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modal when clicking outside
        document.getElementById('stockActionsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeStockActions();
            }
        });
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
    <script>
        function showLowStock() {
            // Filter card stok yang kuantitasnya kurang dari 3
            filteredCards = stockCards.filter(card => {
                // Find the quantity element more precisely by looking for it within the context of "Kuantitas:"
                const qtyContainer = Array.from(card.querySelectorAll('.flex.justify-between'))
                    .find(div => div.textContent.includes('Kuantitas:'));
                if (!qtyContainer) return false;
                
                const qtyElement = qtyContainer.querySelector('.font-medium');
                if (!qtyElement) return false;
                
                const qty = parseInt(qtyElement.textContent.trim());
                return qty < 3;
            });

            // Reset pagination
            currentPage = 1;
            updateDisplay();
            updateButtonStates();
        }

        function showAllStocks() {
            // Reset filtered cards to show all stock cards
            filteredCards = [...stockCards];  // Create a new array with all stock cards
            
            // Reset to first page
            currentPage = 1;
            
            // Update the display with all cards
            updateDisplay();
            updateButtonStates();
        }
    </script>
</body>
</html>