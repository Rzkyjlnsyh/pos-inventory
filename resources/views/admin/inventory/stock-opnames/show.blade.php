<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detail Stock Opname - Custom Pare</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet">
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <style>
    body { font-family: 'Raleway', sans-serif; }
    .nav-text { position: relative; display: inline-block; }
    .nav-text::after { content: ''; position: absolute; width: 0; height: 2px; bottom: -2px; left: 0; background-color: #e17f12; transition: width .2s; }
    .hover-link:hover .nav-text::after { width: 100%; }
    [x-cloak] { display: none !important; }
  </style>
</head>
<body class="bg-gray-100">
  <div class="flex">

    <x-navbar-admin></x-navbar-admin>

    <div class="flex-1 lg:w-5/6">
      <x-navbar-top-admin></x-navbar-top-admin>

      <div class="p-4 lg:p-8">
        <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
          <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-700">Detail Stock Opname</h2>
            <div class="flex space-x-2">
              @if($stockOpname->status === 'approved')
                <a href="{{ route('admin.inventory.stock-opnames.pdf', $stockOpname->id) }}"
                   class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                  <i class="bi bi-file-earmark-pdf mr-2"></i> Export PDF
                </a>
              @endif
              <a href="{{ route('admin.inventory.stock-opnames.index') }}"
                 class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow">
                <i class="bi bi-arrow-left"></i> Kembali
              </a>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-lg">
          @if($stockOpname->status === 'draft')
            <!-- Form Edit untuk Draft dengan Search System -->
            <form action="{{ route('admin.inventory.stock-opnames.update', $stockOpname->id) }}" method="POST" id="opnameForm">
              @csrf
              @method('PUT')

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                  <label class="block text-gray-700 mb-2">No. Dokumen</label>
                  <input type="text" name="document_number" class="w-full border rounded p-2 bg-gray-100"
                    value="{{ $stockOpname->document_number }}" readonly>
                </div>
                <div>
                  <label class="block text-gray-700 mb-2">Tanggal</label>
                  <input type="text" class="w-full border rounded p-2 bg-gray-100" value="{{ date('Y-m-d') }}" disabled>
                  <input type="hidden" name="date" value="{{ date('Y-m-d') }}">
                </div>
              </div>

              <h3 class="text-lg font-semibold mb-4">Daftar Produk</h3>

              <div id="product-items">
                <!-- Items akan diisi oleh JavaScript -->
              </div>

              <button type="button" id="add-item-btn"
                class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 mb-4">
                <i class="bi bi-plus-circle"></i> Tambah Produk
              </button>

              <div class="mb-4">
                <label class="block text-gray-700 mb-2">Catatan</label>
                <textarea name="notes" class="w-full border rounded p-2" rows="3">{{ $stockOpname->notes ?? '' }}</textarea>
              </div>

              <div class="flex justify-end space-x-4 pt-6 border-t">
                <a href="{{ route('admin.inventory.stock-opnames.index') }}"
                  class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                  <i class="bi bi-arrow-left mr-2"></i>
                  Kembali
                </a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                  <i class="bi bi-check-lg mr-2"></i>
                  Simpan
                </button>
              </div>
            </form>

            <!-- Template untuk item produk -->
            <template id="product-item-template">
              <div class="border rounded-lg p-4 mb-4 bg-gray-50 product-item">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div class="relative">
                    <label class="block text-gray-700 mb-2">Produk</label>
                    <div class="relative">
                      <input 
                        type="text" 
                        class="product-search w-full border rounded p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Cari produk (nama, SKU, barcode)..."
                        required>
                      <input type="hidden" class="product-id" name="items[INDEX][product_id]">
                      <input type="hidden" class="product-name" name="items[INDEX][product_name]">
                      <input type="hidden" class="product-sku" name="items[INDEX][sku]">
                      
                      <!-- Search Results Dropdown -->
                      <div class="search-results absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                        <!-- Results akan diisi oleh JavaScript -->
                      </div>
                    </div>
                  </div>
                  <div>
                    <label class="block text-gray-700 mb-2">Qty Sistem</label>
                    <input type="number" class="system-qty w-full border rounded p-2 bg-gray-100" name="items[INDEX][system_qty]" readonly>
                  </div>
                  <div>
                    <label class="block text-gray-700 mb-2">Qty Aktual</label>
                    <input type="number" class="actual-qty w-full border rounded p-2" name="items[INDEX][actual_qty]" required>
                  </div>
                </div>
                <div class="text-right mt-2 remove-item-container">
                  <button type="button" class="remove-item text-red-500 hover:underline">
                    <i class="bi bi-trash"></i> Hapus
                  </button>
                </div>
              </div>
            </template>

            <script>
              // Data items dari PHP
              const initialItems = {!! json_encode(
    $stockOpname->items->map(function($item) {
        return [
            'product_id' => $item->product_id,
            'product_name' => $item->product_name,
            'sku' => $item->sku,
            'system_qty' => $item->system_qty,
            'actual_qty' => $item->actual_qty
        ];
    })
, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};

              // Search timeouts storage
              const searchTimeouts = {};

              document.addEventListener('DOMContentLoaded', function() {
                  const productItemsContainer = document.getElementById('product-items');
                  const template = document.getElementById('product-item-template');
                  const addItemBtn = document.getElementById('add-item-btn');

                  // Initialize dengan data existing
                  initialItems.forEach((item, index) => {
                      addProductItem(item, index);
                  });

                  // Event listener untuk tambah item
                  addItemBtn.addEventListener('click', function() {
                      const newIndex = document.querySelectorAll('.product-item').length;
                      addProductItem({}, newIndex);
                  });

                  // Event delegation untuk remove item
                  productItemsContainer.addEventListener('click', function(e) {
                      if (e.target.closest('.remove-item')) {
                          const item = e.target.closest('.product-item');
                          item.remove();
                          reindexItems();
                      }
                  });

                  // Event delegation untuk search
                  productItemsContainer.addEventListener('input', function(e) {
                      if (e.target.classList.contains('product-search')) {
                          const index = Array.from(document.querySelectorAll('.product-search')).indexOf(e.target);
                          searchProducts(e.target.value, index);
                      }
                  });

                  // Event delegation untuk select product
                  productItemsContainer.addEventListener('click', function(e) {
                      if (e.target.closest('.search-result-item')) {
                          const resultItem = e.target.closest('.search-result-item');
                          const product = JSON.parse(resultItem.dataset.product);
                          const searchInput = resultItem.closest('.product-item').querySelector('.product-search');
                          const index = Array.from(document.querySelectorAll('.product-search')).indexOf(searchInput);
                          
                          selectProduct(product, index);
                      }
                  });

                  // Hide search results when clicking outside
                  document.addEventListener('click', function(e) {
                      if (!e.target.closest('.product-search') && !e.target.closest('.search-results')) {
                          document.querySelectorAll('.search-results').forEach(el => {
                              el.classList.add('hidden');
                          });
                      }
                  });
              });

              function addProductItem(itemData = {}, index) {
                  const template = document.getElementById('product-item-template');
                  const clone = template.content.cloneNode(true);
                  const productItem = clone.querySelector('.product-item');
                  
                  // Update semua input dengan index yang benar
                  productItem.querySelectorAll('[name]').forEach(input => {
                      input.name = input.name.replace('INDEX', index);
                  });

                  // Pre-fill data jika ada
                  if (itemData.product_id) {
                      productItem.querySelector('.product-id').value = itemData.product_id;
                      productItem.querySelector('.product-name').value = itemData.product_name;
                      productItem.querySelector('.product-sku').value = itemData.sku;
                      productItem.querySelector('.product-search').value = itemData.product_name;
                      productItem.querySelector('.system-qty').value = itemData.system_qty;
                      productItem.querySelector('.actual-qty').value = itemData.actual_qty;
                      
                      // Style untuk produk yang sudah dipilih
                      productItem.querySelector('.product-search').classList.add('bg-green-50', 'border-green-300');
                  }

                  // Sembunyikan remove button jika hanya 1 item
                  if (document.querySelectorAll('.product-item').length === 0 && index === 0) {
                      productItem.querySelector('.remove-item-container').classList.add('hidden');
                  } else {
                      productItem.querySelector('.remove-item-container').classList.remove('hidden');
                  }

                  document.getElementById('product-items').appendChild(productItem);
              }

              function searchProducts(query, index) {
                  if (!query || query.length < 2) {
                      hideSearchResults(index);
                      return;
                  }

                  // Clear previous timeout
                  if (searchTimeouts[index]) {
                      clearTimeout(searchTimeouts[index]);
                  }

                  // Debounce search
                  searchTimeouts[index] = setTimeout(() => {
                      fetch(`{{ route('admin.inventory.stock-opnames.search-products') }}?q=${encodeURIComponent(query)}`)
                          .then(response => response.json())
                          .then(data => {
                              showSearchResults(data, index);
                          })
                          .catch(error => {
                              console.error('Search error:', error);
                              hideSearchResults(index);
                          });
                  }, 300);
              }

              function showSearchResults(products, index) {
                  const searchInputs = document.querySelectorAll('.product-search');
                  const searchInput = searchInputs[index];
                  const resultsContainer = searchInput.closest('.product-item').querySelector('.search-results');
                  
                  resultsContainer.innerHTML = '';

                  if (products.length === 0) {
                      resultsContainer.innerHTML = '<div class="p-3 text-gray-500 text-sm">Produk tidak ditemukan</div>';
                  } else {
                      products.forEach(product => {
                          const resultItem = document.createElement('div');
                          resultItem.className = 'search-result-item p-3 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0';
                          resultItem.dataset.product = JSON.stringify(product);
                          resultItem.innerHTML = `
                              <div class="font-medium text-gray-800">${product.name}</div>
                              <div class="text-sm text-gray-500">
                                  SKU: ${product.sku} | Stok: ${product.stock_qty}
                              </div>
                          `;
                          resultsContainer.appendChild(resultItem);
                      });
                  }
                  
                  resultsContainer.classList.remove('hidden');
              }

              function hideSearchResults(index) {
                  const searchInputs = document.querySelectorAll('.product-search');
                  const searchInput = searchInputs[index];
                  const resultsContainer = searchInput.closest('.product-item').querySelector('.search-results');
                  resultsContainer.classList.add('hidden');
              }

              function selectProduct(product, index) {
                  const searchInputs = document.querySelectorAll('.product-search');
                  const searchInput = searchInputs[index];
                  const productItem = searchInput.closest('.product-item');
                  
                  productItem.querySelector('.product-id').value = product.id;
                  productItem.querySelector('.product-name').value = product.name;
                  productItem.querySelector('.product-sku').value = product.sku;
                  productItem.querySelector('.system-qty').value = product.stock_qty;
                  productItem.querySelector('.product-search').value = product.name;
                  
                  // Style untuk produk yang dipilih
                  searchInput.classList.add('bg-green-50', 'border-green-300');
                  
                  // Hide results
                  hideSearchResults(index);
              }

              function reindexItems() {
                  const items = document.querySelectorAll('.product-item');
                  items.forEach((item, newIndex) => {
                      item.querySelectorAll('[name]').forEach(input => {
                          const oldName = input.name;
                          const newName = oldName.replace(/items\[\d+\]/, `items[${newIndex}]`);
                          input.name = newName;
                      });

                      // Update remove button visibility
                      if (items.length === 1) {
                          item.querySelector('.remove-item-container').classList.add('hidden');
                      } else {
                          item.querySelector('.remove-item-container').classList.remove('hidden');
                      }
                  });
              }
            </script>
          @else
            <!-- Tampilan Read-only untuk Approved (SAMA seperti sebelumnya) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
              <div>
                <h3 class="font-medium text-gray-700">Informasi Dokumen</h3>
                <div class="mt-2 space-y-2">
                  <p><span class="font-medium">No. Dokumen:</span> {{ $stockOpname->document_number }}</p>
                  <p><span class="font-medium">Tanggal:</span> 
                    {{ \Carbon\Carbon::parse($stockOpname->date)->format('d M Y') }}</p>
                  <p><span class="font-medium">Status:</span> 
                    <span class="px-2 py-1 rounded-full text-xs 
                      {{ $stockOpname->status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                      {{ ucfirst($stockOpname->status) }}
                    </span>
                  </p>
                </div>
              </div>
              <div>
                <h3 class="font-medium text-gray-700">Informasi User</h3>
                <div class="mt-2 space-y-2">
                  <p>
                    <span class="font-medium">Dibuat oleh:</span> 
                    {{ $stockOpname->creator->name ?? $stockOpname->creator->email ?? '-' }}<br>
                    <small class="text-gray-500 text-sm">
                      {{ \Carbon\Carbon::parse($stockOpname->created_at)->format('d M Y H:i') }}
                    </small>
                  </p>
                  @if($stockOpname->status === 'approved')
                    <p>
                      <span class="font-medium">Disetujui oleh:</span> 
                      {{ $stockOpname->approver->name ?? $stockOpname->approver->email ?? '-' }}<br>
                      <small class="text-gray-500 text-sm">
                        {{ \Carbon\Carbon::parse($stockOpname->approved_at)->format('d M Y H:i') }}
                      </small>
                    </p>
                  @endif
                </div>
              </div>
            </div>

            <h3 class="font-medium text-gray-700 mb-4">Daftar Produk</h3>
            <table class="min-w-full border rounded-lg">
              <thead class="bg-gray-100">
                <tr>
                  <th class="px-4 py-2">Produk</th>
                  <th class="px-4 py-2">Qty Sistem</th>
                  <th class="px-4 py-2">Qty Aktual</th>
                  <th class="px-4 py-2">Selisih</th>
                </tr>
              </thead>
              <tbody>
                @foreach($stockOpname->items as $item)
                <tr class="border-b">
                  <td class="px-4 py-2">{{ $item->product->name }}</td>
                  <td class="px-4 py-2 text-center">{{ $item->system_qty }}</td>
                  <td class="px-4 py-2 text-center">{{ $item->actual_qty }}</td>
                  <td class="px-4 py-2 text-center {{ $item->difference < 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ $item->difference }}
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>

            @if($stockOpname->notes)
            <div class="mt-6">
              <h3 class="font-medium text-gray-700">Catatan</h3>
              <p class="mt-2 p-3 bg-gray-50 rounded">{{ $stockOpname->notes }}</p>
            </div>
            @endif
          @endif
        </div>
      </div>
    </div>
  </div>

  <script>
    function toggleSidebar(){
      const el = document.getElementById('sidebar');
      if(!el) return; el.classList.toggle('-translate-x-full');
    }
    function toggleDropdown(btn){
      const menu = btn.nextElementSibling;
      if(!menu) return;
      if(menu.style.maxHeight && menu.style.maxHeight !== '0px'){
        menu.style.maxHeight = '0px';
        btn.querySelector('i.bi-chevron-down')?.classList.remove('rotate-180');
      } else {
        menu.style.maxHeight = menu.scrollHeight + 'px';
        btn.querySelector('i.bi-chevron-down')?.classList.add('rotate-180');
      }
    }
  </script>
</body>
</html>