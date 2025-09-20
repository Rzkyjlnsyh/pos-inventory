<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Retur - Pare Custom</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" />
</head>
<body class="bg-gray-100">
    <div class="flex">
        <x-navbar-owner></x-navbar-owner>

        <div class="flex-1 lg:w-5/6">
            <x-navbar-top-owner></x-navbar-top-owner>

            <div class="p-6">
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                    <div class="flex items-center justify-between mb-6">
                        <h1 class="text-xl font-semibold text-gray-700">Detail Retur Pembelian</h1>
                        <a href="{{ route('owner.purchase-returns.index') }}" class="text-gray-500 hover:text-gray-700">
                            <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                        </a>
                    </div>

                    <!-- Header Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h2 class="font-semibold mb-3">Informasi Retur</h2>
                            <div class="space-y-2">
                                <p><strong>No. Retur:</strong> {{ $purchaseReturn->return_number }}</p>
                                <p><strong>Tanggal Retur:</strong> {{ $purchaseReturn->return_date ? \Carbon\Carbon::parse($purchaseReturn->return_date)->format('d M Y') : '' }}</p>
                                <p><strong>Status:</strong> 
                                    <span class="capitalize px-2 py-1 rounded 
                                        @if($purchaseReturn->status === 'confirmed') bg-green-100 text-green-800
                                        @elseif($purchaseReturn->status === 'cancelled') bg-red-100 text-red-800
                                        @else bg-yellow-100 text-yellow-800 @endif">
                                        {{ $purchaseReturn->status }}
                                    </span>
                                </p>
                                <p><strong>Alasan:</strong> {{ $purchaseReturn->reason }}</p>
                                @if($purchaseReturn->notes)
                                <p><strong>Catatan:</strong> {{ $purchaseReturn->notes }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
    <h2 class="font-semibold mb-3">Informasi Pembelian</h2>
    <div class="space-y-2">
        <p><strong>No. PO:</strong> {{ $purchaseReturn->purchaseOrder->po_number }}</p>
        <p><strong>Supplier:</strong> {{ $purchaseReturn->supplier->name }}</p>
        <p><strong>Tanggal Beli:</strong> {{ $purchaseReturn->purchaseOrder->order_date ? \Carbon\Carbon::parse($purchaseReturn->purchaseOrder->order_date)->format('d M Y') : '' }}</p>
        <p><strong>Status PO:</strong> 
            <span class="capitalize px-2 py-1 rounded 
                @if($purchaseReturn->purchaseOrder->status === 'returned') bg-purple-100 text-purple-800
                @elseif($purchaseReturn->purchaseOrder->status === 'received') bg-green-100 text-green-800
                @elseif($purchaseReturn->purchaseOrder->status === 'canceled') bg-red-100 text-red-800
                @else bg-yellow-100 text-yellow-800 @endif">
                {{ $purchaseReturn->purchaseOrder->status }}
            </span>
        </p>
        <p><strong>Restock:</strong> {{ $purchaseReturn->restock ? 'Ya' : 'Tidak' }}</p>
    </div>
</div>
                    </div>

                    <!-- Items Table -->
                    <div class="mb-6">
                        <h2 class="font-semibold mb-3">Items yang Dikembalikan</h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full border-collapse border border-gray-300">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="border border-gray-300 p-2">Product</th>
                                        <th class="border border-gray-300 p-2">Qty Beli</th>
                                        <th class="border border-gray-300 p-2">Harga</th>
                                        <th class="border border-gray-300 p-2">Qty Retur</th>
                                        <th class="border border-gray-300 p-2">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchaseReturn->items as $item)
                                    <tr>
                                        <td class="border border-gray-300 p-2">
                                            {{ $item->product->name ?? $item->product_id }}
                                        </td>
                                        <td class="border border-gray-300 p-2 text-center">
                                            {{ $item->qty }}
                                        </td>
                                        <td class="border border-gray-300 p-2 text-right">
                                            Rp {{ number_format($item->price, 0) }}
                                        </td>
                                        <td class="border border-gray-300 p-2 text-center">
                                            {{ $item->qty }}
                                        </td>
                                        <td class="border border-gray-300 p-2 text-right">
                                            Rp {{ number_format($item->total, 0) }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h2 class="font-semibold mb-3">Ringkasan Retur</h2>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Subtotal</p>
                                <p class="font-semibold">Rp {{ number_format($purchaseReturn->subtotal, 0) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Pajak</p>
                                <p class="font-semibold">Rp {{ number_format($purchaseReturn->tax_amount, 0) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Discount</p>
                                <p class="font-semibold">Rp {{ number_format($purchaseReturn->discount_amount, 0) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Total</p>
                                <p class="font-semibold">Rp {{ number_format($purchaseReturn->total_amount, 0) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    @if($purchaseReturn->status === 'pending')
                    <div class="flex space-x-3">
                        <form action="{{ route('owner.purchase-returns.confirm', $purchaseReturn) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                                Konfirmasi Retur
                            </button>
                        </form>
                        <form action="{{ route('owner.purchase-returns.cancel', $purchaseReturn) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                                Batalkan Retur
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>