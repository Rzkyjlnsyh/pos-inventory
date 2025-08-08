<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Buat Pembelian</h2>
    </x-slot>

    <div class="py-6" x-data="purchaseForm()">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="p-6 space-y-4">
                    <form method="POST" action="{{ route('owner.purchases.store') }}">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block mb-1">Tanggal</label>
                                <input type="date" name="order_date" class="border rounded p-2 w-full text-gray-900" value="{{ date('Y-m-d') }}" />
                            </div>
                            <div>
                                <label class="block mb-1">Supplier</label>
                                <select name="supplier_id" class="border rounded p-2 w-full text-gray-900">
                                    <option value="">- Pilih -</option>
                                    @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block mb-1">Atau Nama Supplier Baru</label>
                                <input name="supplier_name" placeholder="Supplier Baru" class="border rounded p-2 w-full text-gray-900" />
                            </div>
                        </div>

                        <div class="mt-6">
                            <div class="flex items-center justify-between mb-2">
                                <div class="font-semibold">Detail Produk</div>
                                <button type="button" @click="addItem()" class="px-3 py-1 bg-indigo-600 text-white rounded">Tambah Baris</button>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead>
                                        <tr class="border-b">
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

                        <div class="pt-4">
                            <button class="bg-indigo-600 text-white px-4 py-2 rounded">Simpan (Draft)</button>
                            <a href="{{ route('owner.purchases.index') }}" class="ml-2 px-4 py-2 rounded border">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    function purchaseForm() {
        return {
            items: [ { product_id: '', product_name: '', sku: '', cost_price: '', qty: 1, discount: 0, suggestions: [] } ],
            addItem() { this.items.push({ product_id: '', product_name: '', sku: '', cost_price: '', qty: 1, discount: 0, suggestions: [] }); },
            removeItem(i) { this.items.splice(i, 1); },
            async search(idx, q) {
                if (!q || q.length < 2) { this.items[idx].suggestions = []; return; }
                try {
                    const resp = await fetch(`{{ route('owner.catalog.products.search') }}?q=${encodeURIComponent(q)}`);
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
</x-app-layout>