<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Detail Pembelian - Bblara</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" />
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet">
  </head>
  <body class="bg-gray-100">
    <div class="flex">
      <button class="fixed text-white text-3xl top-5 left-4 p-2 rounded-md bg-gray-700 lg:hidden focus:outline-none z-50" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
      </button>

      <x-navbar-owner></x-navbar-owner>

      <div class="flex-1 lg:w-5/6">
        <x-navbar-top-owner></x-navbar-top-owner>

        <div class="p-4 lg:p-8">
          <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
            <div class="flex items-center justify-between">
              <h2 class="text-xl font-semibold text-gray-700">Detail Pembelian</h2>
              <a href="{{ route('owner.purchases.index') }}" class="px-4 py-2 border rounded">Kembali</a>
            </div>
          </div>

          <div class="bg-white p-6 rounded-xl shadow-lg">
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
                  <tr class="border-b text-gray-600">
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
              <form method="POST" action="{{ route('owner.purchases.submit', $purchase) }}">@csrf<button class="px-3 py-2 bg-gray-100 rounded">Ajukan</button></form>
              @endif
              @if($purchase->status==='pending')
              <form method="POST" action="{{ route('owner.purchases.approve', $purchase) }}">@csrf<button class="px-3 py-2 bg-green-600 text-white rounded">Approve</button></form>
              @endif
              @if(in_array($purchase->status,['pending','approved']))
              <form method="POST" action="{{ route('owner.purchases.receive', $purchase) }}">@csrf<button class="px-3 py-2 bg-[#005281] text-white rounded">Terima</button></form>
              @endif
              <a href="{{ route('owner.purchases.index') }}" class="px-3 py-2 border rounded">Kembali</a>
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