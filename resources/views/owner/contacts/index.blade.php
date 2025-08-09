<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Customer & Supplier - Bblara</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" />
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
      body{ font-family: 'Raleway', sans-serif; }
      .nav-text{ position: relative; display: inline-block; }
      .nav-text::after{ content:''; position:absolute; width:0; height:2px; bottom:-2px; left:0; background-color:#e17f12; transition:width .2s; }
      .hover-link:hover .nav-text::after{ width:100%; }
    </style>
  </head>
  <body class="bg-gray-100">
    <div class="flex">
      <!-- Toggle Button for Sidebar -->
      <button class="fixed text-white text-3xl top-5 left-4 p-2 rounded-md bg-gray-700 lg:hidden focus:outline-none z-50" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
      </button>

      <!-- Sidebar -->
      <x-navbar-owner></x-navbar-owner>

      <!-- Main Content -->
      <div class="flex-1 lg:w-5/6">
        <!-- Top Navbar -->
        <x-navbar-top-owner></x-navbar-top-owner>

        <!-- Content Wrapper -->
        <div class="p-4 lg:p-8">
          <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
            <div class="flex items-center justify-between">
              <h2 class="text-xl font-semibold text-gray-700">Customer & Supplier</h2>
            </div>
          </div>

          <div class="bg-white p-6 rounded-xl shadow-lg" x-data="{ tab: 'customers' }">
            <!-- Tabs -->
            <div class="flex border-b mb-4">
              <button @click="tab='customers'" :class="tab==='customers' ? 'border-b-2 border-[#005281] text-[#005281]' : 'text-gray-500'" class="px-4 py-2 font-semibold">Customers</button>
              <button @click="tab='suppliers'" :class="tab==='suppliers' ? 'border-b-2 border-[#005281] text-[#005281]' : 'text-gray-500'" class="px-4 py-2 font-semibold">Suppliers</button>
            </div>

            <!-- Customers -->
            <div x-show="tab==='customers'">
              <form method="POST" action="{{ route('owner.contacts.customers.store') }}" class="space-y-4 mb-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <input name="name" required placeholder="Customer name" class="border rounded p-2 text-gray-900 w-full" />
                  <input name="phone" placeholder="Phone" class="border rounded p-2 text-gray-900 w-full" />
                  <input name="email" type="email" placeholder="Email" class="border rounded p-2 text-gray-900 w-full" />
                  <input name="address" placeholder="Address" class="border rounded p-2 text-gray-900 w-full md:col-span-3" />
                  <input name="notes" placeholder="Notes" class="border rounded p-2 text-gray-900 w-full md:col-span-3" />
                </div>
                <button type="submit" class="bg-[#005281] text-white px-4 py-2 rounded-md hover:opacity-90">Tambah Customer</button>
              </form>

              <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                  <thead>
                    <tr class="border-b text-gray-600">
                      <th class="px-3 py-2">Nama</th>
                      <th class="px-3 py-2">Telepon</th>
                      <th class="px-3 py-2">Email</th>
                      <th class="px-3 py-2">Alamat</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($customers as $c)
                    <tr class="border-b">
                      <td class="px-3 py-2">{{ $c->name }}</td>
                      <td class="px-3 py-2">{{ $c->phone }}</td>
                      <td class="px-3 py-2">{{ $c->email }}</td>
                      <td class="px-3 py-2">{{ $c->address }}</td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>

              <div class="mt-4">{{ $customers->withQueryString()->onEachSide(1)->links() }}</div>
            </div>

            <!-- Suppliers -->
            <div x-show="tab==='suppliers'">
              <form method="POST" action="{{ route('owner.contacts.suppliers.store') }}" class="space-y-4 mb-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <input name="name" required placeholder="Supplier name" class="border rounded p-2 text-gray-900 w-full" />
                  <input name="contact_name" placeholder="Contact person" class="border rounded p-2 text-gray-900 w-full" />
                  <input name="phone" placeholder="Phone" class="border rounded p-2 text-gray-900 w-full" />
                  <input name="email" type="email" placeholder="Email" class="border rounded p-2 text-gray-900 w-full" />
                  <input name="address" placeholder="Address" class="border rounded p-2 text-gray-900 w-full md:col-span-2" />
                </div>
                <button type="submit" class="bg-[#005281] text-white px-4 py-2 rounded-md hover:opacity-90">Tambah Supplier</button>
              </form>

              <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                  <thead>
                    <tr class="border-b text-gray-600">
                      <th class="px-3 py-2">Nama</th>
                      <th class="px-3 py-2">Kontak</th>
                      <th class="px-3 py-2">Telepon</th>
                      <th class="px-3 py-2">Email</th>
                      <th class="px-3 py-2">Alamat</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($suppliers as $s)
                    <tr class="border-b">
                      <td class="px-3 py-2">{{ $s->name }}</td>
                      <td class="px-3 py-2">{{ $s->contact_name }}</td>
                      <td class="px-3 py-2">{{ $s->phone }}</td>
                      <td class="px-3 py-2">{{ $s->email }}</td>
                      <td class="px-3 py-2">{{ $s->address }}</td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>

              <div class="mt-4">{{ $suppliers->withQueryString()->onEachSide(1)->links() }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script>
      function toggleSidebar(){
        const el = document.getElementById('sidebar');
        if(!el) return; el.classList.toggle('-translate-x-full');
      }
      function toggleDropdown(btn){
        const menu = btn.nextElementSibling;
        if(!menu) return;
        if(menu.style.maxHeight && menu.style.maxHeight !== '0px'){
          menu.style.maxHeight = '0px';
          btn.querySelector('i.bi-chevron-down')?.classList.remove('rotate-180');
        } else {
          menu.style.maxHeight = menu.scrollHeight + 'px';
          btn.querySelector('i.bi-chevron-down')?.classList.add('rotate-180');
        }
      }
    </script>
  </body>
</html>