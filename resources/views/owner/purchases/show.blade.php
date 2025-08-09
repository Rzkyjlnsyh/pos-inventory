<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Detail Pembelian</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <div class="text-sm text-gray-500">No. Pembelian</div>
                            <div class="text-lg font-semibold">{{ $purchase->po_number }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-500">Status</div>
                            <div class="text-lg font-semibold capitalize">{{ $purchase->status }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <div class="text-sm text-gray-500">Tanggal</div>
                            <div>{{ \Carbon\Carbon::parse($purchase->order_date)->format('d M Y') }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Supplier</div>
                            <div>{{ $purchase->supplier?->name }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Subtotal</div>
                            <div>Rp {{ number_format($purchase->subtotal,0,',','.') }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Diskon</div>
                            <div>Rp {{ number_format($purchase->discount_total,0,',','.') }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Grand Total</div>
                            <div>Rp {{ number_format($purchase->grand_total,0,',','.') }}</div>
                        </div>
                    </div>

                    <div class="overflow-x-auto mb-6">
                        <table class="min-w-full text-left text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="px-3 py-2">Produk</th>
                                    <th class="px-3 py-2">SKU</th>
                                    <th class="px-3 py-2">Harga</th>
                                    <th class="px-3 py-2">Qty</th>
                                    <th class="px-3 py-2">Diskon</th>
                                    <th class="px-3 py-2">Line Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->items as $it)
                                <tr class="border-b">
                                    <td class="px-3 py-2">{{ $it->product_name }}</td>
                                    <td class="px-3 py-2">{{ $it->sku }}</td>
                                    <td class="px-3 py-2">Rp {{ number_format($it->cost_price,0,',','.') }}</td>
                                    <td class="px-3 py-2">{{ $it->qty }}</td>
                                    <td class="px-3 py-2">Rp {{ number_format($it->discount,0,',','.') }}</td>
                                    <td class="px-3 py-2">Rp {{ number_format($it->line_total,0,',','.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center space-x-2">
                        @if($purchase->status==='draft')
                        <form method="POST" action="{{ route('owner.purchases.submit', $purchase) }}">
                            @csrf
                            <button class="px-3 py-2 bg-gray-200 rounded">Ajukan</button>
                        </form>
                        @endif
                        @if($purchase->status==='pending')
                        <form method="POST" action="{{ route('owner.purchases.approve', $purchase) }}">
                            @csrf
                            <button class="px-3 py-2 bg-green-600 text-white rounded">Approve</button>
                        </form>
                        @endif
                        @if(in_array($purchase->status,['pending','approved']))
                        <form method="POST" action="{{ route('owner.purchases.receive', $purchase) }}">
                            @csrf
                            <button class="px-3 py-2 bg-indigo-600 text-white rounded">Terima</button>
                        </form>
                        @endif
                        <a href="{{ route('owner.purchases.index') }}" class="px-3 py-2 border rounded">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>