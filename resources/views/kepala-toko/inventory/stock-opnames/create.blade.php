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
    <x-navbar-kepala-toko></x-navbar-kepala-toko>
  <div class="flex-1">
    <x-navbar-top-kepala-toko></x-navbar-top-kepala-toko>
    <div class="p-4 lg:p-8">
      <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-semibold text-gray-700">Tambah Stock Opname</h2>
        </div>
      </div>

      <div class="bg-white p-6 rounded-xl shadow-lg">
        <form action="{{ route('kepala-toko.inventory.stock-opnames.store') }}" method="POST" x-data="opnameForm()">
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
                  <div>
                    <label class="block text-gray-700 mb-2">Produk</label>
                    <select :name="`items[${index}][product_id]`" class="w-full border rounded p-2 product-select"
                      x-model="item.product_id" @change="getQtySystem(index)" required>
                      <option value="">-- Pilih Produk --</option>
                      @foreach($products as $product)
                        <option value="{{ $product->id }}" data-stock="{{ $product->stock_qty }}">
                          {{ $product->name }} (Stok: {{ $product->stock_qty }})
                        </option>
                      @endforeach
                    </select>
                    <input type="hidden" :name="`items[${index}][product_name]`" :value="getProductName(item.product_id)">
                    <input type="hidden" :name="`items[${index}][sku]`" :value="getProductSku(item.product_id)">
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
            <a href="{{ route('kepala-toko.inventory.stock-opnames.index') }}"
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
        products: @json($products),
        items: [{ 
          product_id: '', 
          system_qty: 0,
          actual_qty: 0 
        }],
        addItem() {
          this.items.push({ 
            product_id: '', 
            system_qty: 0,
            actual_qty: 0 
          });
        },
        removeItem(index) {
          this.items.splice(index, 1);
        },
        getProductName(productId) {
          if (!productId) return '';
          const product = this.products.find(p => p.id == productId);
          return product ? product.name : '';
        },
        getProductSku(productId) {
          if (!productId) return '';
          const product = this.products.find(p => p.id == productId);
          return product ? (product.sku || '') : '';
        },
        async getQtySystem(index) {
          const productId = this.items[index].product_id;
          if (!productId) return;
          
          const selectedOption = document.querySelector(`select[name="items[${index}][product_id]"] option:checked`);
          if (selectedOption) {
            const qtySystem = selectedOption.getAttribute('data-stock') || 0;
            this.items[index].system_qty = qtySystem;
          }
        }
      }
    }
  </script>
</body>
</html>