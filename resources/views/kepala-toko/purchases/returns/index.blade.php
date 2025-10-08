<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Retur Pembelian - Pare Custom</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" />
    <style>
      body{ font-family:'Raleway',sans-serif; }
      .nav-text{ position:relative; display:inline-block; }
      .nav-text::after{ content:''; position:absolute; width:0; height:2px; bottom:-2px; left:0; background-color:#e17f12; transition:width .2s; }
      .hover-link:hover .nav-text::after{ width:100%; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex">
        <x-navbar-kepala-toko></x-navbar-kepala-toko>

        <div class="flex-1 lg:w-5/6">
            <x-navbar-top-kepala-toko></x-navbar-top-kepala-toko>

            <div class="p-4 lg:p-8">
          <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
            <div class="flex items-center justify-between">
              <h2 class="text-xl font-semibold text-gray-700">Purchase Return</h2>
              <a href="{{ route('kepala-toko.purchases.create') }}" class="bg-[#005281] text-white px-4 py-2 rounded-md hover:opacity-90">Buat Pembelian</a>
            </div>
          </div>

            <div>
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                    <div class="flex items-center justify-between mb-6">
                        <h1 class="text-xl font-semibold text-gray-700">Daftar Retur Pembelian</h1>
                        <a href="{{ route('kepala-toko.purchases.index') }}" class="text-gray-500 hover:text-gray-700">
                            <i class="bi bi-arrow-left"></i> Kembali ke Pembelian
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="p-3 text-left">No. Retur</th>
                                    <th class="p-3 text-left">No. Pembelian</th>
                                    <th class="p-3 text-left">Tanggal Retur</th>
                                    <th class="p-3 text-left">Supplier</th>
                                    <th class="p-3 text-right">Total</th>
                                    <th class="p-3 text-left">Status</th>
                                    <th class="p-3 text-left">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($returns as $retur) <!-- GANTI $return MENJADI $retur -->
                                <tr class="border-b">
                                    <td class="p-3">{{ $retur->return_number }}</td>
                                    <td class="p-3">{{ $retur->purchaseOrder->po_number }}</td>
                                    <td class="p-3">{{ $retur->return_date ? \Carbon\Carbon::parse($retur->return_date)->format('d M Y') : '' }}</td>
                                    <td class="p-3">{{ $retur->supplier->name }}</td>
                                    <td class="p-3 text-right">Rp {{ number_format($retur->total_amount, 0) }}</td>
                                    <td class="p-3">
                                        <span class="capitalize px-2 py-1 rounded 
                                            @if($retur->status === 'confirmed') bg-green-100 text-green-800
                                            @elseif($retur->status === 'cancelled') bg-red-100 text-red-800
                                            @else bg-yellow-100 text-yellow-800 @endif">
                                            {{ $retur->status }}
                                        </span>
                                    </td>
                                    <td class="p-3">
                                        <a href="{{ route('kepala-toko.purchase-returns.show', $retur) }}" 
                                           class="text-blue-600 hover:text-blue-800">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $returns->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
      function toggleSidebar(){ const el=document.getElementById('sidebar'); if(!el) return; el.classList.toggle('-translate-x-full'); }
      function toggleDropdown(btn){ const menu=btn.nextElementSibling; if(!menu) return; if(menu.style.maxHeight&&menu.style.maxHeight!=='0px'){ menu.style.maxHeight='0px'; btn.querySelector('i.bi-chevron-down')?.classList.remove('rotate-180'); } else { menu.style.maxHeight=menu.scrollHeight+'px'; btn.querySelector('i.bi-chevron-down')?.classList.add('rotate-180'); } }

    </script>
</body>
</html>