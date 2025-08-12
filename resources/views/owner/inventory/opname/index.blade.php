<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Stock Opname - Bblara</title>
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
              <h2 class="text-xl font-semibold text-gray-700">Stock Opname</h2>
            </div>
          </div>

          <div class="bg-white p-6 rounded-xl shadow-lg text-gray-600">
            Fitur Stock Opname (draft → approve) akan ditambahkan. Setelah approve, qty stok akan diperbarui sesuai hasil opname.
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