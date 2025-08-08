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
                                            <th class="px-2 py-2">Nama Produk</th>
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
                                                    <input class="border rounded p-2 text-gray-900" :name="`items[${idx}][product_name]`" x-model="it.product_name" required />
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
            items: [ { product_name: '', sku: '', cost_price: '', qty: 1, discount: 0 } ],
            addItem() { this.items.push({ product_name: '', sku: '', cost_price: '', qty: 1, discount: 0 }); },
            removeItem(i) { this.items.splice(i, 1); }
        };
    }
    </script>
</x-app-layout>