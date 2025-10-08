<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Retur Pembelian - Pare Custom</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" />
</head>
<body class="bg-gray-100">
    <div class="flex">
        <x-navbar-admin></x-navbar-admin>

        <div class="flex-1 lg:w-5/6">
            <x-navbar-top-admin></x-navbar-top-admin>

            <div class="p-6">
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                    <div class="flex items-center justify-between mb-6">
                        <h1 class="text-xl font-semibold text-gray-700">Buat Retur Pembelian</h1>
                        <a href="{{ route('admin.purchases.index') }}" class="text-gray-500 hover:text-gray-700">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Informasi Pembelian -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h2 class="font-semibold mb-3">Informasi Pembelian</h2>
                            <div class="space-y-2">
                                <p><strong>No. PO:</strong> {{ $purchase->po_number }}</p>
                                <p><strong>Supplier:</strong> {{ $purchase->supplier->name }}</p>
                                <p><strong>Tanggal:</strong> {{ $purchase->order_date ? \Carbon\Carbon::parse($purchase->order_date)->format('d M Y') : '' }}</p>
                                <p><strong>Total:</strong> Rp {{ number_format($purchase->grand_total, 0) }}</p>
                            </div>
                        </div>

                        <!-- Form Retur -->
                        <div class="bg-white p-4 rounded-lg">
                            <form action="{{ route('admin.purchase-returns.store', $purchase) }}" method="POST">
                                @csrf
                                
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Retur</label>
                                    <input type="date" name="return_date" value="{{ old('return_date', now()->format('Y-m-d')) }}" 
                                           class="w-full border rounded-md p-2" required>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Retur</label>
                                    <textarea name="reason" rows="3" class="w-full border rounded-md p-2" 
                                              placeholder="Masukkan alasan retur..." required>{{ old('reason') }}</textarea>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                                    <textarea name="notes" rows="2" class="w-full border rounded-md p-2" 
                                              placeholder="Catatan tambahan...">{{ old('notes') }}</textarea>
                                </div>

{{-- Tampilkan info --}}
<div class="mb-4 bg-blue-50 p-3 rounded">
    <p class="text-sm text-blue-800">
        <i class="bi bi-info-circle"></i> 
        Retur pembelian akan otomatis mengurangi stok produk
    </p>
</div>
                                <hr class="my-4">

                                <h3 class="font-semibold mb-3">Items yang Dikembalikan</h3>
                                
                                <div class="space-y-3">
                                    @foreach($purchase->items as $item)
                                    <div class="border rounded-md p-3">
                                        <div class="flex justify-between items-start mb-2">
                                            <div>
                                                <p class="font-medium">{{ $item->product_name }}</p>
                                                <p class="text-sm text-gray-600">SKU: {{ $item->sku }}</p>
                                            </div>
                                            <p class="text-sm">Qty: {{ $item->qty }}</p>
                                        </div>
                                        
                                        <div class="flex items-center space-x-3">
    <div>
        <label class="block text-sm text-gray-700 mb-1">Qty Retur</label>
        <input type="number" name="items[{{ $item->product_id }}][qty]" 
               min="0" max="{{ $item->qty }}" 
               class="w-20 border rounded-md p-1" value="0">
    </div>
    
    <div>
        <label class="flex items-center mt-5">
            <input type="checkbox" name="items[{{ $item->product_id }}][restock]" 
                   value="1" class="mr-1" checked>
            <span class="text-xs text-gray-600">Kurangi Stok</span>
        </label>
    </div>
</div>
                                        
                                        <input type="hidden" name="items[{{ $item->product_id }}][product_id]" value="{{ $item->product_id }}">
                                    </div>
                                    @endforeach
                                </div>

                                <div class="mt-6">
                                    <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700">
                                        Buat Retur
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>