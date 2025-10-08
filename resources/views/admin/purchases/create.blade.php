<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Buat Pembelian - Custom Pare</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" />
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100" x-data="purchaseForm()">
    <div class="flex">
        <!-- Toggle Button for Sidebar -->
        <button class="fixed text-white text-3xl top-5 left-4 p-2 rounded-md bg-gray-700 lg:hidden focus:outline-none z-50" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>

        <!-- Sidebar -->
        <x-navbar-admin></x-navbar-admin>

        <!-- Main Content -->
        <div class="flex-1 lg:w-5/6">
            <!-- Top Navbar -->
            <x-navbar-top-admin></x-navbar-top-admin>

            <!-- Content Wrapper -->
            <div class="p-4 lg:p-8">
                <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-700">Buat Pembelian</h2>
                        <a href="{{ route('admin.purchases.index') }}" class="px-4 py-2 border rounded">Kembali</a>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <form method="POST" action="{{ route('admin.purchases.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            <div>
                                <label class="block mb-1 text-sm text-gray-600">Tanggal</label>
                                <input type="date" name="order_date" class="border rounded p-2 w-full text-gray-900" value="{{ date('Y-m-d') }}" />
                            </div>
                            
                            <!-- Field baru untuk tipe pembelian -->
                            <div>
                                <label class="block mb-1 text-sm text-gray-600">Tipe Pembelian <span class="text-red-500">*</span></label>
                                <select name="purchase_type" x-model="purchaseType" class="border rounded p-2 w-full text-gray-900" required>
                                    <option value="">- Pilih Tipe -</option>
                                    <option value="kain">Pembelian Kain</option>
                                    <option value="produk_jadi">Pembelian Produk Jadi</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block mb-1 text-sm text-gray-600">Supplier</label>
                                <select name="supplier_id" class="border rounded p-2 w-full text-gray-900">
                                    <option value="">- Pilih -</option>
                                    @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block mb-1 text-sm text-gray-600">Atau Nama Supplier Baru</label>
                                <input name="supplier_name" placeholder="Supplier Baru" class="border rounded p-2 w-full text-gray-900" />
                            </div>
                        </div>

                        <!-- Info panel berdasarkan tipe pembelian -->
                        <div x-show="purchaseType" class="mb-6 p-4 rounded-lg" :class="purchaseType === 'kain' ? 'bg-blue-50 border border-blue-200' : 'bg-green-50 border border-green-200'">
                            <div class="flex items-start space-x-2">
                                <i class="bi bi-info-circle text-lg" :class="purchaseType === 'kain' ? 'text-blue-600' : 'text-green-600'"></i>
                                <div>
                                    <h4 class="font-semibold" :class="purchaseType === 'kain' ? 'text-blue-800' : 'text-green-800'" x-text="purchaseType === 'kain' ? 'Alur Pembelian Kain:' : 'Alur Pembelian Produk Jadi:'"></h4>
                                    <div class="text-sm mt-1" :class="purchaseType === 'kain' ? 'text-blue-700' : 'text-green-700'">
                                        <span x-show="purchaseType === 'kain'">
                                            Draft → Pending → Approved → Payment → Kain Diterima → Printing → Jahit → Selesai
                                        </span>
                                        <span x-show="purchaseType === 'produk_jadi'">
                                            Draft → Pending → Approved → Payment → Selesai
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <div class="flex items-center justify-between mb-2">
                                <div class="font-semibold text-gray-700">Detail Produk</div>
                                <button type="button" @click="addItem()" class="px-3 py-1 bg-[#005281] text-white rounded">Tambah Baris</button>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead>
                                        <tr class="border-b text-gray-600">
                                            <th class="px-2 py-2">Cari Produk</th>
                                            <th class="px-2 py-2">SKU</th>
                                            <th class="px-2 py-2">Harga Beli</th>
                                            <th class="px-2 py-2">Qty</th>
                                            <th class="px-2 py-2">Diskon</th>
                                            <th class="px-2 py-2">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(it, idx) in items" :key="idx">
                                            <tr class="border-b">
                                                <td class="px-2 py-2">
                                                    <div class="space-y-1">
                                                        <input class="border rounded p-2 text-gray-900 w-64" :name="`items[${idx}][product_name]`" x-model="it.product_name" placeholder="Ketik nama/SKU..." @input.debounce.300ms="search(idx, it.product_name)" required />
                                                        <input type="hidden" :name="`items[${idx}][product_id]`" :value="it.product_id">
                                                        <div class="bg-white border rounded shadow max-h-40 overflow-auto" x-show="it.suggestions && it.suggestions.length">
                                                            <template x-for="p in it.suggestions">
                                                                <div class="px-2 py-1 cursor-pointer hover:bg-gray-100" @click="selectProduct(idx, p)">
                                                                    <span x-text="p.name"></span>
                                                                    <span class="text-xs text-gray-500" x-text="p.sku ? '('+p.sku+')' : ''"></span>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-2 py-2">
                                                    <input class="border rounded p-2 text-gray-900" :name="`items[${idx}][sku]`" x-model="it.sku" />
                                                </td>
                                                <td class="px-2 py-2">
                                                    <input type="number" step="0.01" class="border rounded p-2 text-gray-900 w-28" :name="`items[${idx}][cost_price]`" x-model="it.cost_price" required />
                                                </td>
                                                <td class="px-2 py-2">
                                                    <input type="number" class="border rounded p-2 text-gray-900 w-20" :name="`items[${idx}][qty]`" x-model="it.qty" required />
                                                </td>
                                                <td class="px-2 py-2">
                                                    <input type="number" step="0.01" class="border rounded p-2 text-gray-900 w-28" :name="`items[${idx}][discount]`" x-model="it.discount" />
                                                </td>
                                                <td class="px-2 py-2">
                                                    <button type="button" @click="removeItem(idx)" class="px-2 py-1 bg-red-600 text-white rounded text-xs">Hapus</button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="pt-4 flex justify-end gap-2">
                            <button class="bg-[#005281] text-white px-4 py-2 rounded">Simpan (Draft)</button>
                            <a href="{{ route('admin.purchases.index') }}" class="px-4 py-2 rounded border">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar(){ const el=document.getElementById('sidebar'); if(!el) return; el.classList.toggle('-translate-x-full'); }
        function toggleDropdown(btn){ const menu=btn.nextElementSibling; if(!menu) return; if(menu.style.maxHeight&&menu.style.maxHeight!=='0px'){ menu.style.maxHeight='0px'; btn.querySelector('i.bi-chevron-down')?.classList.remove('rotate-180'); } else { menu.style.maxHeight=menu.scrollHeight+'px'; btn.querySelector('i.bi-chevron-down')?.classList.add('rotate-180'); } }

        function purchaseForm() {
            return {
                purchaseType: '',
                items: [ { product_id: '', product_name: '', sku: '', cost_price: '', qty: 1, discount: 0, suggestions: [] } ],
                addItem() { this.items.push({ product_id: '', product_name: '', sku: '', cost_price: '', qty: 1, discount: 0, suggestions: [] }); },
                removeItem(i) { this.items.splice(i, 1); },
                async search(idx, q) {
                    if (!q || q.length < 2) { this.items[idx].suggestions = []; return; }
                    try {
                        const resp = await fetch(`/admin/catalog/products/search?q=${encodeURIComponent(q)}`);
                        const data = await resp.json();
                        this.items[idx].suggestions = data;
                    } catch (e) { this.items[idx].suggestions = []; }
                },
                selectProduct(idx, p) {
                    const it = this.items[idx];
                    it.product_id = p.id;
                    it.product_name = p.name;
                    it.sku = p.sku;
                    it.cost_price = p.cost_price;
                    it.suggestions = [];
                }
            };
        }
    </script>
</body>
</html>