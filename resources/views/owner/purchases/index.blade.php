<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Daftar Pembelian - Pare Custom</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" />
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet">
    <style>
      body{ font-family:'Raleway',sans-serif; }
      .nav-text{ position:relative; display:inline-block; }
      .nav-text::after{ content:''; position:absolute; width:0; height:2px; bottom:-2px; left:0; background-color:#e17f12; transition:width .2s; }
      .hover-link:hover .nav-text::after{ width:100%; }
    </style>
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
              <h2 class="text-xl font-semibold text-gray-700">Daftar Pembelian</h2>
              <a href="{{ route('owner.purchases.create') }}" class="bg-[#005281] text-white px-4 py-2 rounded-md hover:opacity-90">Buat Pembelian</a>
            </div>
          </div>

          <div class="bg-white p-6 rounded-xl shadow-lg">
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center space-x-2">
                <a href="{{ route('owner.purchases.index', ['group' => 'todo']) }}" class="px-3 py-2 rounded {{ ($group ?? '')==='todo' ? 'bg-[#005281] text-white' : 'bg-gray-100 text-gray-700' }}">Butuh Diproses</a>
                <a href="{{ route('owner.purchases.index', ['group' => 'processed']) }}" class="px-3 py-2 rounded {{ ($group ?? '')==='processed' ? 'bg-[#005281] text-white' : 'bg-gray-100 text-gray-700' }}">Telah Diproses</a>
                <a href="{{ route('owner.purchases.index', ['group' => 'returned']) }}" class="px-3 py-2 rounded {{ ($group ?? '')==='returned' ? 'bg-[#005281] text-white' : 'bg-gray-100 text-gray-700' }}">Retur</a>
                <a href="{{ route('owner.purchases.index', ['group' => 'cancelled']) }}" class="px-3 py-2 rounded {{ ($group ?? '')==='cancelled' ? 'bg-[#005281] text-white' : 'bg-gray-100 text-gray-700' }}">Dibatalkan</a>
              </div>
              <form method="GET" class="flex items-center space-x-2">
                <input type="hidden" name="group" value="{{ $group }}" />
                <input type="text" name="q" value="{{ $q }}" placeholder="Cari No/Supplier" class="border rounded p-2 text-gray-900" />
                <select name="status" class="border rounded p-2 text-gray-900">
                  <option value="">Semua Status</option>
                  @foreach(['draft','pending','approved','received'] as $st)
                  <option value="{{ $st }}" @selected($status==$st)>{{ ucfirst($st) }}</option>
                  @endforeach
                </select>
                <button class="bg-gray-100 px-3 py-2 rounded">Filter</button>
              </form>
            </div>

            <div class="overflow-x-auto">
              <table class="min-w-full text-left text-sm">
                <thead>
                  <tr class="border-b text-gray-600">
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
                      <a class="text-[#005281]" href="{{ route('owner.purchases.show', $p) }}">{{ $p->po_number }}</a>
                    </td>
                    <td class="px-3 py-2">{{ \Carbon\Carbon::parse($p->order_date)->format('d M Y') }}</td>
                    <td class="px-3 py-2">{{ $p->supplier?->name }}</td>
                    <td class="px-3 py-2">Rp {{ number_format($p->grand_total,0,',','.') }}</td>
                    <td class="px-3 py-2 capitalize">{{ $p->status }}</td>
                    <td class="px-3 py-2 space-x-2">
                      @if($p->status==='draft')
                      <form method="POST" action="{{ route('owner.purchases.submit', $p) }}" class="inline">@csrf<button class="px-2 py-1 text-xs bg-gray-100 rounded">Ajukan</button></form>
                      @endif
                      @if($p->status==='pending')
                      <form method="POST" action="{{ route('owner.purchases.approve', $p) }}" class="inline">@csrf<button class="px-2 py-1 text-xs bg-green-600 text-white rounded">Approve</button></form>
                      @endif
                      @if(in_array($p->status,['pending','approved']))
                      <form method="POST" action="{{ route('owner.purchases.receive', $p) }}" class="inline">@csrf<button class="px-2 py-1 text-xs bg-[#005281] text-white rounded">Terima</button></form>
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

    <script>
      function toggleSidebar(){ const el=document.getElementById('sidebar'); if(!el) return; el.classList.toggle('-translate-x-full'); }
      function toggleDropdown(btn){ const menu=btn.nextElementSibling; if(!menu) return; if(menu.style.maxHeight&&menu.style.maxHeight!=='0px'){ menu.style.maxHeight='0px'; btn.querySelector('i.bi-chevron-down')?.classList.remove('rotate-180'); } else { menu.style.maxHeight=menu.scrollHeight+'px'; btn.querySelector('i.bi-chevron-down')?.classList.add('rotate-180'); } }
    </script>
  </body>
</html>