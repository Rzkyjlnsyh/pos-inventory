<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inventory - Custom Pare</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet">
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <style>
    body{ font-family: 'Nunito,sans-serif'; }
    .nav-text{ position: relative; display: inline-block; }
    .nav-text::after{ content:''; position:absolute; width:0; height:2px; bottom:-2px; left:0; background-color:#e17f12; transition:width .2s; }
    .hover-link:hover .nav-text::after{ width:100%; }
    
    /* Active link effect */
    .active .nav-text::after {
      width: 100%;
    }
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

      <div class="p-4" x-data="{ tab: 'stock_in' }">


        <div class="bg-white p-6 rounded-xl shadow-lg">
          <div class="flex border-b mb-4">
            <button @click="tab='stock_in'" :class="tab==='stock_in' ? 'border-b-2 border-[#005281] text-[#005281]' : 'text-gray-500'" class="px-4 py-2 font-semibold">Stok Masuk</button>
            <button @click="tab='opname'" :class="tab==='opname' ? 'border-b-2 border-[#005281] text-[#005281]' : 'text-gray-500'" class="px-4 py-2 font-semibold">Stock Opname</button>
            <button @click="tab='movement'" :class="tab==='movement' ? 'border-b-2 border-[#005281] text-[#005281]' : 'text-gray-500'" class="px-4 py-2 font-semibold">Pergerakan Stok</button>
          </div>

          <div x-show="tab==='stock_in'">
            <iframe src="{{ route('owner.inventory.stock-ins.index') }}" class="w-full min-h-[600px] border rounded"></iframe>
          </div>

          <div x-show="tab==='opname'">
            <iframe src="{{ route('owner.inventory.stock-opnames.index') }}" class="w-full min-h-[600px] border rounded"></iframe>
          </div>

          <div x-show="tab==='movement'" class="text-gray-600">
            <!-- stock movement disini -->
            <iframe src="{{ route('owner.inventory.stock-movements.index') }}" class="w-full min-h-[600px] border rounded"></iframe>
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