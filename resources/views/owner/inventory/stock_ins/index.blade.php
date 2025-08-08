<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Stok Masuk</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" class="flex items-center space-x-2 mb-4">
                        <input type="text" name="q" value="{{ $q }}" placeholder="Cari No Stok Masuk" class="border rounded p-2 text-gray-900" />
                        <button class="bg-gray-200 px-3 py-2 rounded">Filter</button>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="px-3 py-2">No. Stok Masuk</th>
                                    <th class="px-3 py-2">Tanggal</th>
                                    <th class="px-3 py-2">Supplier</th>
                                    <th class="px-3 py-2">No. Pembelian</th>
                                    <th class="px-3 py-2">Jumlah Item</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stockIns as $s)
                                <tr class="border-b">
                                    <td class="px-3 py-2">{{ $s->stock_in_number }}</td>
                                    <td class="px-3 py-2">{{ \Carbon\Carbon::parse($s->received_date)->format('d M Y') }}</td>
                                    <td class="px-3 py-2">{{ $s->supplier?->name }}</td>
                                    <td class="px-3 py-2">{{ $s->purchaseOrder?->po_number }}</td>
                                    <td class="px-3 py-2">{{ $s->items->sum('qty') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">{{ $stockIns->withQueryString()->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>