<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Stock Opname - Custom Pare</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Nunito,sans-serif'; }
  </style>
</head>
<body class="bg-gray-100">
    <div class="flex-1">
        <div class="p-4 lg:p-8">
            <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-700">Detail Stock Opname</h2>
                    <a href="{{ route('owner.inventory.stock-opnames.index') }}" class="text-blue-600 hover:underline">
                        Kembali ke List
                    </a>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <h3 class="font-medium text-gray-700">Informasi Dokumen</h3>
                        <div class="mt-2 space-y-2">
                            <p><span class="font-medium">No. Dokumen:</span> {{ $stockOpname->document_number }}</p>
                            <p><span class="font-medium">Tanggal:</span> 
   {{ \Carbon\Carbon::parse($stockOpname->date)->format('d M Y') }}
</p>
                            <p><span class="font-medium">Status:</span> 
                                <span class="px-2 py-1 rounded-full text-xs 
                                    {{ $stockOpname->status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ ucfirst($stockOpname->status) }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-700">Informasi User</h3>
                        <div class="mt-2 space-y-2">
                        <p><span class="font-medium">Dibuat oleh:</span> {{ $stockOpname->creator_label }}</p>
                        @if($stockOpname->status === 'approved')
  <p><span class="font-medium">Disetujui oleh:</span> {{ $stockOpname->approver_label }}</p>
@endif
                        </div>
                    </div>
                </div>

                <h3 class="font-medium text-gray-700 mb-4">Daftar Produk</h3>
                <table class="min-w-full border rounded-lg">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2">Produk</th>
                            <th class="px-4 py-2">Qty Sistem</th>
                            <th class="px-4 py-2">Qty Aktual</th>
                            <th class="px-4 py-2">Selisih</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stockOpname->items as $item)
                        <tr class="border-b">
                            <td class="px-4 py-2">{{ $item->product->name }}</td>
                            <td class="px-4 py-2 text-center">{{ $item->system_qty }}</td>
                            <td class="px-4 py-2 text-center">{{ $item->actual_qty }}</td>
                            <td class="px-4 py-2 text-center {{ $item->difference < 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $item->difference }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                @if($stockOpname->notes)
                <div class="mt-6">
                    <h3 class="font-medium text-gray-700">Catatan</h3>
                    <p class="mt-2 p-3 bg-gray-50 rounded">{{ $stockOpname->notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>