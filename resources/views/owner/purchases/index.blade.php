<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Daftar Pembelian</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <form method="GET" class="flex items-center space-x-2">
                            <input type="text" name="q" value="{{ $q }}" placeholder="Cari No Pembelian/Supplier" class="border rounded p-2 text-gray-900" />
                            <select name="status" class="border rounded p-2 text-gray-900">
                                <option value="">Semua Status</option>
                                @foreach(['draft','pending','approved','received'] as $st)
                                <option value="{{ $st }}" @selected($status==$st)>{{ ucfirst($st) }}</option>
                                @endforeach
                            </select>
                            <button class="bg-gray-200 px-3 py-2 rounded">Filter</button>
                        </form>
                        <a href="{{ route('owner.purchases.create') }}" class="bg-indigo-600 text-white px-3 py-2 rounded">Buat Pembelian</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="px-3 py-2">No. Pembelian</th>
                                    <th class="px-3 py-2">Tanggal</th>
                                    <th class="px-3 py-2">Supplier</th>
                                    <th class="px-3 py-2">Jumlah</th>
                                    <th class="px-3 py-2">Status</th>
                                    <th class="px-3 py-2">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchases as $p)
                                <tr class="border-b">
                                    <td class="px-3 py-2">
                                        <a class="text-indigo-600" href="{{ route('owner.purchases.show', $p) }}">{{ $p->po_number }}</a>
                                    </td>
                                    <td class="px-3 py-2">{{ \Carbon\Carbon::parse($p->order_date)->format('d M Y') }}</td>
                                    <td class="px-3 py-2">{{ $p->supplier?->name }}</td>
                                    <td class="px-3 py-2">Rp {{ number_format($p->grand_total,0,',','.') }}</td>
                                    <td class="px-3 py-2">{{ ucfirst($p->status) }}</td>
                                    <td class="px-3 py-2 space-x-2">
                                        @if($p->status==='draft')
                                        <form method="POST" action="{{ route('owner.purchases.submit', $p) }}" class="inline">
                                            @csrf
                                            <button class="px-2 py-1 text-xs bg-gray-200 rounded">Ajukan</button>
                                        </form>
                                        @endif
                                        @if($p->status==='pending')
                                        <form method="POST" action="{{ route('owner.purchases.approve', $p) }}" class="inline">
                                            @csrf
                                            <button class="px-2 py-1 text-xs bg-green-600 text-white rounded">Approve</button>
                                        </form>
                                        @endif
                                        @if(in_array($p->status,['pending','approved']))
                                        <form method="POST" action="{{ route('owner.purchases.receive', $p) }}" class="inline">
                                            @csrf
                                            <button class="px-2 py-1 text-xs bg-indigo-600 text-white rounded">Terima</button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">{{ $purchases->withQueryString()->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>