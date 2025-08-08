<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Produk</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" class="mb-4 flex items-center space-x-2">
                        <input type="text" name="q" value="{{ $q }}" placeholder="Cari produk" class="border rounded p-2 text-gray-900" />
                        <select name="category_id" class="border rounded p-2 text-gray-900">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected($categoryId==$cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <button class="bg-gray-200 px-3 py-2 rounded">Filter</button>
                        <a href="{{ route('owner.catalog.products.create') }}" class="bg-indigo-600 text-white px-3 py-2 rounded">Tambah</a>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="px-3 py-2">SKU</th>
                                    <th class="px-3 py-2">Nama</th>
                                    <th class="px-3 py-2">Kategori</th>
                                    <th class="px-3 py-2">Harga Beli</th>
                                    <th class="px-3 py-2">Harga Jual</th>
                                    <th class="px-3 py-2">Aktif</th>
                                    <th class="px-3 py-2">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $p)
                                <tr class="border-b">
                                    <td class="px-3 py-2">{{ $p->sku }}</td>
                                    <td class="px-3 py-2">{{ $p->name }}</td>
                                    <td class="px-3 py-2">{{ $p->category?->name }}</td>
                                    <td class="px-3 py-2">Rp {{ number_format($p->cost_price,0,',','.') }}</td>
                                    <td class="px-3 py-2">Rp {{ number_format($p->price,0,',','.') }}</td>
                                    <td class="px-3 py-2">{{ $p->is_active ? 'Ya' : 'Tidak' }}</td>
                                    <td class="px-3 py-2">
                                        <a href="{{ route('owner.catalog.products.edit', $p) }}" class="text-indigo-600">Edit</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">{{ $products->withQueryString()->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>