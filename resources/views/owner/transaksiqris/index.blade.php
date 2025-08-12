<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Transaksi Qris - Pare Custom</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" />
    <!-- Font CDN -->
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;500;600;700&display=swap" rel="stylesheet">
    
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

        .bi-arrow-down-up {
            transition: all 0.2s ease-in-out;
        }

        .bi-arrow-up, .bi-arrow-down {
            color: #e17f12;
        }

        th[onclick] {
            transition: all 0.2s ease-in-out;
        }

        th[onclick]:hover {
            background-color: #fff8f3;
        }

        tbody tr:not(.bg-gray-50):hover {
            background-color: #fff8f3;
            transition: background-color 0.2s ease-in-out;
        }

        .pagination-button {
            @apply px-3 py-1 border rounded-lg transition-all duration-200;
        }

        .pagination-button.active {
            @apply bg-orange-500 text-white border-orange-500;
        }

        .pagination-button:not(.active) {
            @apply hover:bg-orange-50 text-gray-700;
        }

        tbody tr {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Custom Scrollbar */
        .overflow-x-auto::-webkit-scrollbar {
            height: 8px;
        }

        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #e17f12;
            border-radius: 4px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #d67610;
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
            <!-- Navbar Top -->
            <x-navbar-top-owner></x-navbar-top-owner>

            <!-- Content Wrapper -->
            <div class="p-4 lg:p-8">
                <div class="p-6 bg-gray-100 min-h-screen">
                    <div class="max-w-7xl mx-auto">
                        <!-- Header Section -->
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-3 gap-4">
                            <h1 class="text-3xl font-bold text-gray-800">Daftar Transaksi Qris</h1>
                            <div class="text-sm text-gray-600 bg-white p-3 rounded-lg shadow-sm">
                                <p class="mb-1">Current User: <span class="font-semibold text-orange-500">{{ Auth::user()->name ?? 'rhelzz' }}</span></p>
                                <p>Date: <span id="currentDateTime" class="font-semibold text-orange-500">2025-04-07 00:50:40</span></p>
                            </div>
                        </div>

                        <!-- User Statistics Cards Section -->
                        <div class="mb-8">
                          <div id="userStatsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                              <!-- Cards will be dynamically populated here -->
                          </div>
                        </div>

                        <!-- Success Alert -->
                        @if(session('success'))
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6" role="alert">
                            <div class="flex items-center">
                                <i class="bi bi-check-circle-fill mr-2"></i>
                                <span>{{ session('success') }}</span>
                            </div>
                        </div>
                        @endif

                        <!-- Main Card -->
                        <div class="bg-white rounded-xl shadow-md overflow-hidden">
                            <!-- Table Controls -->
                            <div class="p-6 border-b border-gray-100">
                              <div class="flex flex-col gap-4">
                                  <!-- Search & Filter Row -->
                                  <div class="flex flex-col md:flex-row gap-4">
                                      <!-- Search Box - Full width -->
                                      <div class="flex-grow">
                                          <label for="searchInput" class="text-sm font-medium text-gray-600 mb-2 block">
                                              <i class="bi bi-search mr-1"></i>Cari Transaksi
                                          </label>
                                          <div class="relative">
                                              <input
                                                  type="text"
                                                  id="searchInput"
                                                  class="w-full pl-10 pr-4 py-2.5 border rounded-lg focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200"
                                                  placeholder="Cari berdasarkan ID atau metode pembayaran..."
                                              >
                                              <div class="absolute left-3 top-3 text-gray-400">
                                                  <i class="bi bi-search"></i>
                                              </div>
                                          </div>
                                      </div>

                                      <!-- User Filter -->
                                      <div class="md:w-72">
                                          <label for="userFilter" class="text-sm font-medium text-gray-600 mb-2 block">
                                              <i class="bi bi-person mr-1"></i>Filter Pengguna
                                          </label>
                                          <select id="userFilter" 
                                                  class="w-full px-3 py-2.5 border rounded-lg focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200 bg-white">
                                              <option value="">Semua Pengguna</option>
                                              <!-- Users will be populated dynamically -->
                                          </select>
                                      </div>

                                      <!-- Entries Per Page -->
                                      <div class="md:w-48">
                                          <label for="entriesPerPage" class="text-sm font-medium text-gray-600 mb-2 block">
                                              <i class="bi bi-table mr-1"></i>Tampilkan Entri
                                          </label>
                                          <select id="entriesPerPage" 
                                                  class="w-full px-3 py-2.5 border rounded-lg focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200 bg-white">
                                              <option value="5" selected>5 entri</option>
                                              <option value="10">10 entri</option>
                                              <option value="25">25 entri</option>
                                              <option value="50">50 entri</option>
                                          </select>
                                      </div>
                                  </div>
                              </div>
                            </div>

                            <!-- Table Section -->
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Pengguna</th>
                                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Subtotal</th>
                                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Modal</th>
                                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Metode Pembayaran</th>
                                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer group" onclick="handleSort()">
                                                <div class="flex items-center space-x-2">
                                                    <span>Waktu Transaksi</span>
                                                    <span id="sort-icon">
                                                        <i class="bi bi-arrow-down-up"></i>
                                                    </span>
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($transactions as $transaction)
                                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $transaction->id }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $transaction->name_user }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp {{ number_format($transaction->total_cost_price, 0, ',', '.') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <span class="px-3 py-1 rounded-full text-xs font-medium {{ $transaction->payment_method == 'cash' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                                    {{ ucfirst($transaction->payment_method) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $transaction->timestamp }}</td>
                                        </tr>
                                        @endforeach
                                        <!-- Total Row -->
                                        <tr class="bg-gray-50 font-semibold">
                                            <td class="px-6 py-4 whitespace-nowrap"></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-900">Total</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-orange-600">Rp {{ number_format($transactions->sum('subtotal'), 0, ',', '.') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-orange-600">Rp {{ number_format($transactions->sum('total_cost_price'), 0, ',', '.') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap"></td>
                                            <td class="px-6 py-4 whitespace-nowrap"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="px-6 py-4 border-t border-gray-200">
                                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                                    <div class="text-sm text-gray-700">
                                        Menampilkan 
                                        <span class="font-semibold text-orange-500" id="startEntry">1</span> 
                                        sampai 
                                        <span class="font-semibold text-orange-500" id="endEntry">10</span> 
                                        dari 
                                        <span class="font-semibold text-orange-500" id="totalEntries">0</span> 
                                        entri
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button id="prevPage" class="px-4 py-2 border rounded-lg hover:bg-orange-50 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white transition-colors duration-200 flex items-center">
                                            <i class="bi bi-chevron-left mr-1"></i> Sebelumnya
                                        </button>
                                        <div id="pageNumbers" class="flex space-x-1">
                                            <!-- Page numbers will be inserted here -->
                                        </div>
                                        <button id="nextPage" class="px-4 py-2 border rounded-lg hover:bg-orange-50 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white transition-colors duration-200 flex items-center">
                                            Selanjutnya <i class="bi bi-chevron-right ml-1"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Dropdown Toggle Function
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
        // Table state and configuration
        const tableState = {
            currentPage: 1,
            entriesPerPage: 5,
            isAscending: true,
            filteredData: [],
            allRows: []
        };

        // Utility functions
        function formatDateTime(date) {
            return date.getFullYear() + '-' + 
                String(date.getMonth() + 1).padStart(2, '0') + '-' + 
                String(date.getDate()).padStart(2, '0') + ' ' + 
                String(date.getHours()).padStart(2, '0') + ':' + 
                String(date.getMinutes()).padStart(2, '0') + ':' + 
                String(date.getSeconds()).padStart(2, '0');
        }

        function formatCurrency(amount) {
            return amount.toLocaleString('id-ID');
        }

        // DOM Updates
        function updateDateTime() {
            document.getElementById('currentDateTime').textContent = formatDateTime(new Date());
        }

        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('-translate-x-full');
        }

        // Table Operations
        function handleSort() {
            tableState.isAscending = !tableState.isAscending;
            const sortIcon = document.getElementById('sort-icon');
            
            sortIcon.innerHTML = tableState.isAscending ? 
                '<i class="bi bi-arrow-up text-orange-500"></i>' : 
                '<i class="bi bi-arrow-down text-orange-500"></i>';
            
            sortTable();
            updateTable();
        }

        function sortTable() {
            tableState.filteredData.sort((a, b) => {
                const timeA = new Date(a.cells[5].textContent.trim());
                const timeB = new Date(b.cells[5].textContent.trim());
                const idA = parseInt(a.cells[0].textContent.trim());
                const idB = parseInt(b.cells[0].textContent.trim());
                
                if (timeA.getTime() !== timeB.getTime()) {
                    return tableState.isAscending ? timeA - timeB : timeB - timeA;
                }
                return tableState.isAscending ? idA - idB : idB - idA;
            });
        }

        function handleSearch(e) {
            const searchTerm = e.target.value.toLowerCase();
            tableState.filteredData = tableState.allRows.filter(row => 
                Array.from(row.cells).some(cell => 
                    cell.textContent.toLowerCase().includes(searchTerm)
                )
            );
            tableState.currentPage = 1;
            updateTable();
        }

        function handleEntriesChange(e) {
            tableState.entriesPerPage = parseInt(e.target.value);
            tableState.currentPage = 1;
            updateTable();
        }

        function handlePageChange(newPage) {
            const maxPage = Math.ceil(tableState.filteredData.length / tableState.entriesPerPage);
            if (newPage >= 1 && newPage <= maxPage) {
                tableState.currentPage = newPage;
                updateTable();
            }
        }

        function updateTable() {
            const tbody = document.querySelector('tbody');
            const totalRow = tbody.querySelector('.bg-gray-50');
            const startIndex = (tableState.currentPage - 1) * tableState.entriesPerPage;
            const endIndex = startIndex + tableState.entriesPerPage;
            const paginatedData = tableState.filteredData.slice(startIndex, endIndex);
            
            // Clear and update rows
            Array.from(tbody.querySelectorAll('tr:not(.bg-gray-50)')).forEach(row => row.remove());
            paginatedData.forEach(row => tbody.insertBefore(row.cloneNode(true), totalRow));
            
            updatePaginationInfo();
            updatePaginationControls();
            updateTotalRow();
        }

        function updatePaginationInfo() {
            const startEntry = tableState.filteredData.length === 0 ? 0 : 
                (tableState.currentPage - 1) * tableState.entriesPerPage + 1;
            const endEntry = Math.min(tableState.currentPage * tableState.entriesPerPage, 
                tableState.filteredData.length);
            
            document.getElementById('startEntry').textContent = startEntry;
            document.getElementById('endEntry').textContent = endEntry;
            document.getElementById('totalEntries').textContent = tableState.filteredData.length;
        }

        function updatePaginationControls() {
            const pageNumbers = document.getElementById('pageNumbers');
            const maxPage = Math.ceil(tableState.filteredData.length / tableState.entriesPerPage);
            
            pageNumbers.innerHTML = '';
            
            for (let i = 1; i <= maxPage; i++) {
                const button = document.createElement('button');
                button.textContent = i;
                button.classList.add('pagination-button');
                
                if (i === tableState.currentPage) {
                    button.classList.add('active');
                }
                
                button.addEventListener('click', () => handlePageChange(i));
                pageNumbers.appendChild(button);
            }
            
            document.getElementById('prevPage').disabled = tableState.currentPage === 1;
            document.getElementById('nextPage').disabled = tableState.currentPage === maxPage;
        }

        function updateTotalRow() {
            const tbody = document.querySelector('tbody');
            const visibleRows = Array.from(tbody.querySelectorAll('tr:not(.bg-gray-50)'));
            const totalRow = tbody.querySelector('.bg-gray-50');
            
            if (visibleRows.length > 0) {
                const subtotalSum = visibleRows.reduce((sum, row) => {
                    const subtotalText = row.cells[2].textContent.replace('Rp ', '').replace(/\./g, '');
                    return sum + parseInt(subtotalText);
                }, 0);
                
                const modalSum = visibleRows.reduce((sum, row) => {
                    const modalText = row.cells[3].textContent.replace('Rp ', '').replace(/\./g, '');
                    return sum + parseInt(modalText);
                }, 0);
                
                totalRow.cells[2].textContent = `Rp ${formatCurrency(subtotalSum)}`;
                totalRow.cells[3].textContent = `Rp ${formatCurrency(modalSum)}`;
            }
        }

        function getUniqueUsers(rows) {
            const users = new Set();
            rows.forEach(row => {
                const userName = row.cells[1].textContent.trim();
                users.add(userName);
            });
            return Array.from(users).sort();
        }

        function populateUserFilter() {
            const userFilter = document.getElementById('userFilter');
            const users = getUniqueUsers(tableState.allRows);
            
            // Clear existing options except the first one
            userFilter.innerHTML = '<option value="">Semua Pengguna</option>';
            
            // Add user options
            users.forEach(user => {
                const option = document.createElement('option');
                option.value = user;
                option.textContent = user;
                userFilter.appendChild(option);
            });
        }

        function handleUserFilter(e) {
            const selectedUser = e.target.value;
            
            if (selectedUser === "") {
                // If "Semua Pengguna" is selected, show all rows
                tableState.filteredData = tableState.allRows.filter(row => {
                    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
                    return Array.from(row.cells).some(cell => 
                        cell.textContent.toLowerCase().includes(searchTerm)
                    );
                });
            } else {
                // Filter by selected user and search term
                const searchTerm = document.getElementById('searchInput').value.toLowerCase();
                tableState.filteredData = tableState.allRows.filter(row => {
                    const userName = row.cells[1].textContent.trim();
                    const matchesUser = userName === selectedUser;
                    const matchesSearch = Array.from(row.cells).some(cell => 
                        cell.textContent.toLowerCase().includes(searchTerm)
                    );
                    return matchesUser && matchesSearch;
                });
            }
            
            tableState.currentPage = 1;
            updateTable();
        }

        // Modify the handleSearch function to include user filter
        function handleSearch(e) {
            const searchTerm = e.target.value.toLowerCase();
            const selectedUser = document.getElementById('userFilter').value;
            
            tableState.filteredData = tableState.allRows.filter(row => {
                const userName = row.cells[1].textContent.trim();
                const matchesUser = selectedUser === "" || userName === selectedUser;
                const matchesSearch = Array.from(row.cells).some(cell => 
                    cell.textContent.toLowerCase().includes(searchTerm)
                );
                return matchesUser && matchesSearch;
            });
            
            tableState.currentPage = 1;
            updateTable();
        }

        // Initialization
        document.addEventListener('DOMContentLoaded', function() {
            const tbody = document.querySelector('tbody');
            tableState.allRows = Array.from(tbody.querySelectorAll('tr')).filter(row => 
                !row.classList.contains('bg-gray-50')
            );
            tableState.filteredData = tableState.allRows;
            
            // Initialize components
            updateDateTime();
            setInterval(updateDateTime, 1000);
            
            // Populate user filter
            populateUserFilter();
            
            // Event listeners
            document.getElementById('searchInput').addEventListener('input', handleSearch);
            document.getElementById('userFilter').addEventListener('change', handleUserFilter);
            document.getElementById('entriesPerPage').addEventListener('change', handleEntriesChange);
            document.getElementById('prevPage').addEventListener('click', () => 
                handlePageChange(tableState.currentPage - 1)
            );
            document.getElementById('nextPage').addEventListener('click', () => 
                handlePageChange(tableState.currentPage + 1)
            );
            
            // Initial table setup
            updateTable();
        });
    </script>
    <script>
        const userStats = {
            data: new Map(),
            currentPage: 0,
            cardsPerPage: 3,
            
            initialize() {
                this.currentPage = 0;
                this.calculateStats();
                this.renderCards();
                this.attachEventListeners();
            },

            calculateStats() {
                this.data.clear();
                
                // Initialize total statistics
                this.data.set('All Transactions', {
                    transactions: 0,
                    totalSubtotal: 0,
                    color: 'bg-orange-100 border-orange-500',
                    isTotal: true
                });
                
                tableState.allRows.forEach(row => {
                    const userName = row.cells[1].textContent.trim();
                    const subtotal = parseInt(row.cells[2].textContent.replace('Rp ', '').replace(/\./g, ''));
                    
                    if (!this.data.has(userName)) {
                        this.data.set(userName, {
                            transactions: 0,
                            totalSubtotal: 0,
                            color: this.getRandomColor(),
                            isTotal: false
                        });
                    }
                    
                    const userData = this.data.get(userName);
                    userData.transactions++;
                    userData.totalSubtotal += subtotal;
                    
                    const totalStats = this.data.get('All Transactions');
                    totalStats.transactions++;
                    totalStats.totalSubtotal += subtotal;
                });
            },

            getRandomColor() {
                const colors = [
                    'bg-blue-100 border-blue-500',
                    'bg-green-100 border-green-500',
                    'bg-purple-100 border-purple-500',
                    'bg-pink-100 border-pink-500',
                    'bg-indigo-100 border-indigo-500'
                ];
                return colors[Math.floor(Math.random() * colors.length)];
            },

            renderCards() {
                const container = document.getElementById('userStatsContainer');
                container.innerHTML = '';

                // Get selected user from filter
                const selectedUser = document.getElementById('userFilter').value;

                // Convert Map to Array for sorting
                const statsArray = Array.from(this.data.entries());
                
                // Sort so that "All Transactions" is first, then sort others alphabetically
                statsArray.sort((a, b) => {
                    if (a[0] === 'All Transactions') return -1;
                    if (b[0] === 'All Transactions') return 1;
                    return a[0].localeCompare(b[0]);
                });

                // Calculate start and end index for current page
                const startIndex = this.currentPage * this.cardsPerPage;
                const endIndex = Math.min(startIndex + this.cardsPerPage, statsArray.length);
                const currentPageStats = statsArray.slice(startIndex, endIndex);

                // Create wrapper for cards
                const cardsWrapper = document.createElement('div');
                cardsWrapper.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 col-span-full';

                currentPageStats.forEach(([userName, stats]) => {
                    const card = document.createElement('div');
                    const isTotal = stats.isTotal;
                    
                    // Add active/inactive state styling
                    const isActive = selectedUser === '' || userName === selectedUser;
                    
                    card.className = `user-stat-card ${stats.color} p-6 rounded-xl border-l-4 shadow-sm 
                                    transition-all duration-300 transform cursor-pointer
                                    ${isActive ? 'hover:shadow-md hover:-translate-y-1' : 'opacity-50 hover:opacity-75'}`;

                    card.innerHTML = `
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-2 flex items-center">
                                    ${isTotal ? '<i class="bi bi-graph-up-arrow mr-2"></i>' : '<i class="bi bi-person-circle mr-2"></i>'}
                                    ${userName}
                                </h3>
                                <div class="space-y-2">
                                    <p class="text-sm text-gray-600">
                                        <i class="bi bi-receipt mr-2"></i>
                                        <span class="font-medium">${stats.transactions}</span> Transaksi
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        <i class="bi bi-cash-coin mr-2"></i>
                                        <span class="font-medium">Rp ${stats.totalSubtotal.toLocaleString('id-ID')}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="text-4xl opacity-20">
                                ${isTotal ? '<i class="bi bi-bar-chart-fill"></i>' : '<i class="bi bi-person-circle"></i>'}
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="bi bi-graph-up mr-2"></i>
                                Rata-rata: Rp ${Math.round(stats.totalSubtotal / stats.transactions).toLocaleString('id-ID')}
                            </div>
                        </div>
                    `;

                    // Add click event handlers
                    if (isTotal) {
                        card.addEventListener('click', () => {
                            const userFilter = document.getElementById('userFilter');
                            userFilter.value = '';
                            userFilter.dispatchEvent(new Event('change'));
                        });
                    } else {
                        card.addEventListener('click', () => {
                            const userFilter = document.getElementById('userFilter');
                            userFilter.value = userName === selectedUser ? '' : userName;
                            userFilter.dispatchEvent(new Event('change'));
                        });
                    }

                    cardsWrapper.appendChild(card);
                });

                container.appendChild(cardsWrapper);

                // Add dot navigation below the cards
                const navigationDiv = document.createElement('div');
                navigationDiv.className = 'col-span-full flex items-center justify-center mt-6 space-x-3'; // Added margin-top (mt-6)

                // Calculate total pages
                const totalPages = Math.ceil(statsArray.length / this.cardsPerPage);

                // Create dots for each page
                for (let i = 0; i < totalPages; i++) {
                    const dotWrapper = document.createElement('div');
                    dotWrapper.className = 'relative group';

                    const dot = document.createElement('button');
                    dot.className = `w-2.5 h-2.5 rounded-full transition-all duration-200 ${
                        i === this.currentPage 
                            ? 'bg-orange-500 w-4' // Active dot
                            : 'bg-gray-300 hover:bg-gray-400' // Inactive dot
                    }`;

                    // Add tooltip showing page number
                    const tooltip = document.createElement('div');
                    tooltip.className = 'absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200';
                    tooltip.textContent = `Page ${i + 1}`;

                    dot.addEventListener('click', () => {
                        this.currentPage = i;
                        this.renderCards();
                    });

                    dotWrapper.appendChild(tooltip);
                    dotWrapper.appendChild(dot);
                    navigationDiv.appendChild(dotWrapper);
                }

                container.appendChild(navigationDiv);
            },

            attachEventListeners() {
                // Update stats when table data changes
                document.getElementById('searchInput').addEventListener('input', () => {
                    this.calculateStats();
                    this.renderCards();
                });

                document.getElementById('userFilter').addEventListener('change', () => {
                    this.calculateStats();
                    this.renderCards();
                });
            }
        };

        // Initialize user stats when document is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // After table initialization
            setTimeout(() => {
                userStats.initialize();
            }, 100);
        });
    </script>
</body>
</html>