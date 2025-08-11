<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tabel Produk - Bblara</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Bootstrap Icons CDN -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css"
    />
    {{-- Font Cdn --}}
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet">
    <style>
      body{
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

      /* Tambahkan di dalam tag style yang sudah ada */
      .pagination-button {
          @apply px-3 py-1 border rounded-lg transition-all duration-200;
      }

      .pagination-button.active {
          @apply bg-orange-500 text-white border-orange-500;
      }

      .pagination-button:not(.active) {
          @apply hover:bg-orange-50 text-gray-700;
      }

      /* Animation untuk row tabel */
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
          <div class="p-6 bg-gray-100 min-h-screen">
              <div class="max-w-7xl mx-auto">
                  <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-gray-800">Daftar Produk</h1>
                      <a href="{{ route('owner.product.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                          Tambah Produk
                      </a>
                  </div>
          
                  @if(session('success'))
                      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                          <span class="block sm:inline">{{ session('success') }}</span>
                      </div>
                  @endif
          
                  <div class="bg-white rounded-lg shadow overflow-hidden">
                      <div class="p-6 border-b border-gray-100">
                        <div class="flex flex-col md:flex-row gap-4">
                            <!-- Search Box -->
                            <div class="flex-grow">
                                <label for="searchInput" class="text-sm font-medium text-gray-600 mb-2 block">
                                    <i class="bi bi-search mr-1"></i>Cari Produk
                                </label>
                                <div class="relative">
                                    <input
                                        type="text"
                                        id="searchInput"
                                        class="w-full pl-10 pr-4 py-2.5 border rounded-lg focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200"
                                        placeholder="Cari berdasarkan nama produk..."
                                    >
                                    <div class="absolute left-3 top-3 text-gray-400">
                                        <i class="bi bi-search"></i>
                                    </div>
                                </div>
                            </div>
                    
                            <!-- Entries Per Page -->
                            <div class="md:w-48">
                                <label for="entriesPerPage" class="text-sm font-medium text-gray-600 mb-2 block">
                                    <i class="bi bi-table mr-1"></i>Tampilkan Entri
                                </label>
                                <select id="entriesPerPage" 
                                        class="w-full px-3 py-2.5 border rounded-lg focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200 bg-white">
                                    <option value="5">5 entri</option>
                                    <option value="10" selected>10 entri</option>
                                    <option value="25">25 entri</option>
                                    <option value="50">50 entri</option>
                                </select>
                            </div>
                        </div>
                      </div>
                      <div class="overflow-x-auto">
                          <table class="min-w-full divide-y divide-gray-200">
                              <thead class="bg-gray-50">
                                  <tr>
                                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gambar</th>
                                                                             <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                       <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                       <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barcode</th>
                                       <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                       <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Modal</th>
                                       <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Jual</th>
                                       <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Stok</th>
                                       <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                       <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                  </tr>
                              </thead>
                              <tbody class="bg-white divide-y divide-gray-200">
                                  @foreach($products as $product)
                                      <tr>
                                          <td class="px-6 py-4 whitespace-nowrap">
                                                                                             <img src="{{ Str::startsWith($product->image_path, 'http') ? $product->image_path : (Str::startsWith($product->image_path, 'assets/') ? asset($product->image_path) : Storage::url($product->image_path)) }}" alt="{{ $product->name }}"  
                                                  class="h-16 w-16 object-cover rounded">
                                          </td>
                                          <td class="px-6 py-4 whitespace-nowrap">{{ $product->name }}</td>
                                          <td class="px-6 py-4 whitespace-nowrap">{{ $product->sku }}</td>
                                                                                     <td class="px-6 py-4 whitespace-nowrap">{{ $product->barcode }}</td>
                                           <td class="px-6 py-4 whitespace-nowrap">{{ $product->category?->name }}</td>
                                           <td class="px-6 py-4 whitespace-nowrap">Rp {{ number_format($product->cost_price, 0, ',', '.') }}</td>
                                           <td class="px-6 py-4 whitespace-nowrap">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                                           <td class="px-6 py-4 whitespace-nowrap">{{ $product->stock_qty }}</td>
                                           <td class="px-6 py-4 whitespace-nowrap">{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                                          <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex space-x-2">
                                                {{-- Button Lihat --}}
                                                <a href="{{ route('owner.product.show', $product) }}" 
                                                   class="inline-flex items-center px-3 py-1.5 bg-blue-500 text-white text-sm font-medium rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                    <i class="bi bi-eye mr-1"></i>
                                                    Analisis
                                                </a>
                                        
                                                {{-- Button Edit --}}
                                                <a href="{{ route('owner.product.edit', $product) }}" 
                                                   class="inline-flex items-center px-3 py-1.5 bg-yellow-500 text-white text-sm font-medium rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                                    <i class="bi bi-pencil mr-1"></i>
                                                    Edit
                                                </a>
                                        
                                                {{-- Button Hapus --}}
                                                <form action="{{ route('owner.product.destroy', $product) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')"
                                                            class="inline-flex items-center px-3 py-1.5 bg-red-500 text-white text-sm font-medium rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                        <i class="bi bi-trash mr-1"></i>
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                      </tr>
                                  @endforeach
                              </tbody>
                          </table>

                          <div class="px-6 py-4 border-t border-gray-200">
                            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                                <div class="text-sm text-gray-700">
                                    Menampilkan 
                                    <span class="font-semibold text-orange-500" id="startEntry">1</span> 
                                    sampai 
                                    <span class="font-semibold text-orange-500" id="endEntry">10</span> 
                                    dari 
                                    <span class="font-semibold text-orange-500" id="totalEntries">0</span> 
                                    produk
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
        // Add toggleSidebar function
        function toggleSidebar() {
          const sidebar = document.querySelector('.sidebar');
          sidebar.classList.toggle('-translate-x-full');
        }
        
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
        // State untuk tabel dan pagination
        const tableState = {
            currentPage: 1,
            entriesPerPage: 10,
            allRows: [],
            filteredData: []
        };

        // Fungsi inisialisasi tabel
        function initializeTable() {
            const tbody = document.querySelector('tbody');
            tableState.allRows = Array.from(tbody.querySelectorAll('tr'));
            tableState.filteredData = tableState.allRows;
            
            // Set total entries
            document.getElementById('totalEntries').textContent = tableState.filteredData.length;
            
            updateTable();
        }

        // Fungsi update tabel
        function updateTable() {
            const tbody = document.querySelector('tbody');
            const startIndex = (tableState.currentPage - 1) * tableState.entriesPerPage;
            const endIndex = startIndex + tableState.entriesPerPage;
            const paginatedData = tableState.filteredData.slice(startIndex, endIndex);
            
            // Simpan total row sebelum mengosongkan tbody
            const totalRow = tbody.querySelector('tr:last-child');
            
            // Clear existing rows kecuali total row
            tbody.innerHTML = '';
            
            // Add paginated rows
            paginatedData.forEach(row => {
                tbody.appendChild(row.cloneNode(true));
            });
            
            updatePaginationControls();
            updatePaginationInfo();
        }

        // Fungsi update controls pagination
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

        // Fungsi update info pagination
        function updatePaginationInfo() {
            const startEntry = tableState.filteredData.length === 0 ? 0 : 
                (tableState.currentPage - 1) * tableState.entriesPerPage + 1;
            const endEntry = Math.min(tableState.currentPage * tableState.entriesPerPage, 
                tableState.filteredData.length);
            
            document.getElementById('startEntry').textContent = startEntry;
            document.getElementById('endEntry').textContent = endEntry;
        }

        // Fungsi handle perubahan halaman
        function handlePageChange(newPage) {
            const maxPage = Math.ceil(tableState.filteredData.length / tableState.entriesPerPage);
            if (newPage >= 1 && newPage <= maxPage) {
                tableState.currentPage = newPage;
                updateTable();
            }
        }

        // Initialize saat dokumen dimuat
        document.addEventListener('DOMContentLoaded', function() {
            initializeTable();
            
            // Event listeners untuk tombol prev/next
            document.getElementById('prevPage').addEventListener('click', () => {
                handlePageChange(tableState.currentPage - 1);
            });
            
            document.getElementById('nextPage').addEventListener('click', () => {
                handlePageChange(tableState.currentPage + 1);
            });
        });

        // Fungsi handle search
        function handleSearch(e) {
            const searchTerm = e.target.value.toLowerCase();
            tableState.filteredData = tableState.allRows.filter(row => {
                const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                return name.includes(searchTerm);
            });
            
            tableState.currentPage = 1;
            updateTable();
        }

        // Fungsi handle perubahan jumlah entri
        function handleEntriesChange(e) {
            tableState.entriesPerPage = parseInt(e.target.value);
            tableState.currentPage = 1;
            updateTable();
        }

        document.addEventListener('DOMContentLoaded', function() {
          initializeTable();
          
          // Event listeners untuk tombol prev/next
          document.getElementById('prevPage').addEventListener('click', () => {
              handlePageChange(tableState.currentPage - 1);
          });
          
          document.getElementById('nextPage').addEventListener('click', () => {
              handlePageChange(tableState.currentPage + 1);
          });

          // Event listeners untuk search dan entries per page
          document.getElementById('searchInput').addEventListener('input', handleSearch);
          document.getElementById('entriesPerPage').addEventListener('change', handleEntriesChange);
      });
      </script>
    </body>
</html>