<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Stock Opname - Custom Pare</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet">
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <style>
    body {
      font-family: 'Raleway', sans-serif;
    }
  </style>
</head>

<body class="bg-gray-100">
  <div class="flex">
    <x-navbar-admin></x-navbar-admin>
  <div class="flex-1">
    <x-navbar-top-admin></x-navbar-top-admin>
    <div class="p-4 lg:p-8">
      <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-semibold text-gray-700">Tambah Stock Opname</h2>
        </div>
      </div>

      <div class="bg-white p-6 rounded-xl shadow-lg">
        <form action="{{ route('admin.inventory.stock-opnames.store') }}" method="POST" x-data="opnameForm()">
          @csrf

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <label class="block text-gray-700 mb-2">No. Dokumen</label>
              <input type="text" name="document_number" class="w-full border rounded p-2 bg-gray-100"
                value="{{ $autoNumber }}" readonly>
            </div>
            <div>
              <label class="block text-gray-700 mb-2">Tanggal</label>
              <input type="text" class="w-full border rounded p-2 bg-gray-100" value="{{ date('Y-m-d') }}" disabled>
              <input type="hidden" name="date" value="{{ date('Y-m-d') }}">
            </div>
          </div>

          <h3 class="text-lg font-semibold mb-4">Daftar Produk</h3>

          <div id="product-items">
            <template x-for="(item, index) in items" :key="index">
              <div class="border rounded-lg p-4 mb-4 bg-gray-50">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div class="relative">
                    <label class="block text-gray-700 mb-2">Produk</label>
                    <div class="relative">
                      <input 
                        type="text" 
                        x-model="item.searchQuery"
                        @input="searchProducts(index)"
                        @focus="item.showResults = true"
                        @click.away="item.showResults = false"
                        :placeholder="item.product_name || 'Cari produk (nama, SKU, barcode)...'"
                        class="w-full border rounded p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="item.product_id ? 'bg-green-50 border-green-300' : ''"
                        required>
                      <input type="hidden" :name="`items[${index}][product_id]`" x-model="item.product_id">
                      <input type="hidden" :name="`items[${index}][product_name]`" x-model="item.product_name">
                      <input type="hidden" :name="`items[${index}][sku]`" x-model="item.sku">
                      
                      <!-- Search Results Dropdown -->
                      <div x-show="item.showResults && item.searchResults && item.searchResults.length > 0" 
                           x-cloak
                           class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                        <template x-for="product in item.searchResults" :key="product.id">
                          <div @click="selectProduct(index, product)"
                               class="p-3 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0">
                            <div class="font-medium text-gray-800" x-text="product.name"></div>
                            <div class="text-sm text-gray-500">
                              SKU: <span x-text="product.sku"></span> | 
                              Stok: <span x-text="product.stock_qty"></span>
                            </div>
                          </div>
                        </template>
                      </div>
                      
                      <!-- No Results -->
                      <div x-show="item.showResults && item.searchQuery && item.searchQuery.length >= 2 && item.searchResults && item.searchResults.length === 0" 
                           x-cloak
                           class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg p-3 text-gray-500 text-sm">
                            Produk tidak ditemukan
                          </div>
                    </div>
                  </div>
                  <div>
                    <label class="block text-gray-700 mb-2">Qty Sistem</label>
                    <input type="number" :name="`items[${index}][system_qty]`"
                      class="w-full border rounded p-2 bg-gray-100 qty-system" x-model="item.system_qty" readonly>
                  </div>
                  <div>
                    <label class="block text-gray-700 mb-2">Qty Aktual</label>
                    <input type="number" :name="`items[${index}][actual_qty]`"
                      class="w-full border rounded p-2" x-model="item.actual_qty" required>
                  </div>
                </div>
                <div class="text-right mt-2" x-show="items.length > 1">
                  <button type="button" class="text-red-500 hover:underline" @click="removeItem(index)">
                    <i class="bi bi-trash"></i> Hapus
                  </button>
                </div>
              </div>
            </template>
          </div>

          <button type="button" @click="addItem"
            class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 mb-4">
            <i class="bi bi-plus-circle"></i> Tambah Produk
          </button>

          <div class="mb-4">
            <label class="block text-gray-700 mb-2">Catatan</label>
            <textarea name="notes" class="w-full border rounded p-2" rows="3"></textarea>
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
      </div>
    </div>
  </div>
  </div>

  <script>
    function opnameForm() {
      return {
        searchTimeouts: {},
        items: [{ 
          product_id: '', 
          product_name: '',
          sku: '',
          system_qty: 0,
          actual_qty: 0,
          searchQuery: '',
          showResults: false,
          searchResults: []
        }],
        addItem() {
          this.items.push({ 
            product_id: '', 
            product_name: '',
            sku: '',
            system_qty: 0,
            actual_qty: 0,
            searchQuery: '',
            showResults: false,
            searchResults: []
          });
        },
        removeItem(index) {
          this.items.splice(index, 1);
          // Clear timeout jika ada
          if (this.searchTimeouts[index]) {
            clearTimeout(this.searchTimeouts[index]);
            delete this.searchTimeouts[index];
          }
        },
        searchProducts(index) {
          const item = this.items[index];
          const query = item.searchQuery;
          
          if (!query || query.length < 2) {
            item.searchResults = [];
            return;
          }

          // Clear previous timeout
          if (this.searchTimeouts[index]) {
            clearTimeout(this.searchTimeouts[index]);
          }

          // Debounce search
          this.searchTimeouts[index] = setTimeout(() => {
            fetch(`{{ route('admin.inventory.stock-opnames.search-products') }}?q=${encodeURIComponent(query)}`)
              .then(response => response.json())
              .then(data => {
                item.searchResults = data;
                item.showResults = true;
              })
              .catch(error => {
                console.error('Search error:', error);
                item.searchResults = [];
              });
          }, 300);
        },
        selectProduct(index, product) {
          const item = this.items[index];
          item.product_id = product.id;
          item.product_name = product.name;
          item.sku = product.sku || '';
          item.system_qty = product.stock_qty;
          item.searchQuery = product.name;
          item.showResults = false;
          item.searchResults = [];
        }
      }
    }
  </script>
  
  <style>
    [x-cloak] { display: none !important; }
  </style>
</body>
</html>