<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard - Pare Custom</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" />
    {{-- Font Cdn --}}
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet">
    <style>
      body{
        font-family: 'Raleway', sans-serif;
      }
      .nav-text {
        position: relative;
        display: inline-block;
      }
      .nav-text::after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: -2px;
        left: 0;
        background-color: #e17f12;
        transition: width 0.2s ease-in-out;
      }
      .hover-link:hover .nav-text::after {
        width: 100%;
      }
    </style>
  </head>
  <body class="bg-gray-100">
    <div class="flex">
      <!-- Toggle Button for Sidebar -->
      <button
        class="fixed text-white text-3xl top-5 left-4 p-2 rounded-md bg-gray-700 lg:hidden focus:outline-none z-50"
        onclick="toggleSidebar()"
      >
        <i class="bi bi-list"></i>
      </button>

      <!-- Sidebar -->
      <x-navbar-admin></x-navbar-admin>

      <!-- Main Content -->
      <div class="flex-1 lg:w-5/6">
        {{-- Navbar Top --}}
        <x-navbar-top-admin></x-navbar-top-admin>

        <!-- Content Wrapper -->
        <div class="p-4 lg:p-8">
          <!-- Welcome Message -->
          <div class="bg-white p-6 rounded-xl shadow-lg mb-6 relative overflow-hidden">
            <!-- Background Pattern -->
            <div class="absolute right-0 top-0 w-32 h-32 bg-[#005281]/5 rounded-bl-full"></div>
            
            <div class="flex items-center gap-6 relative">
                <div class="flex items-center justify-center w-16 h-16 bg-[#005281]/10 rounded-full">
                    <i class="bi bi-gear text-4xl text-[#005281]"></i>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <h2 class="text-2xl font-semibold text-gray-700">
                            Halo {{ ucfirst(Auth::user()->name) }}!
                        </h2>
                        <span class="px-3 py-1 text-sm bg-[#005281]/10 text-[#005281] rounded-full">
                            Admin
                        </span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <i class="bi bi-clock"></i>
                        <span>{{ now()->format('l, d F Y') }}</span>
                    </div>
                    <p class="text-gray-600 text-sm">
                        Selamat datang di dashboard Admin untuk manajemen sistem dan konfigurasi.
                    </p>
                </div>
            </div>
          </div>

          <!-- Admin Features Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white p-6 rounded-xl shadow-lg">
              <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-12 h-12 bg-purple-100 rounded-lg">
                  <i class="bi bi-gear-fill text-2xl text-purple-600"></i>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-700">System Config</h3>
                  <p class="text-sm text-gray-500">Konfigurasi sistem</p>
                </div>
              </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg">
              <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-12 h-12 bg-red-100 rounded-lg">
                  <i class="bi bi-people-fill text-2xl text-red-600"></i>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-700">User Management</h3>
                  <p class="text-sm text-gray-500">Kelola pengguna</p>
                </div>
              </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg">
              <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-12 h-12 bg-indigo-100 rounded-lg">
                  <i class="bi bi-database-fill text-2xl text-indigo-600"></i>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-700">Data Master</h3>
                  <p class="text-sm text-gray-500">Administrasi data</p>
                </div>
              </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg">
              <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-12 h-12 bg-cyan-100 rounded-lg">
                  <i class="bi bi-speedometer2 text-2xl text-cyan-600"></i>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-700">Monitor System</h3>
                  <p class="text-sm text-gray-500">System performance</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Development Notice -->
          <div class="bg-gradient-to-r from-purple-50 to-indigo-50 border border-purple-200 p-6 rounded-xl shadow-lg">
            <div class="flex items-center gap-3">
              <div class="flex items-center justify-center w-10 h-10 bg-purple-100 rounded-full">
                <i class="bi bi-info-circle text-purple-600"></i>
              </div>
              <div>
                <h3 class="font-semibold text-purple-800">Dashboard dalam Pengembangan</h3>
                <p class="text-sm text-purple-700 mt-1">
                  Fitur Admin sedang dalam tahap pengembangan. Sistem role berhasil diimplementasi!
                </p>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <script>
      function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('-translate-x-full');
      }

      function toggleDropdown(button) {
        const dropdownMenu = button.nextElementSibling;
        const chevronIcon = button.querySelector('.bi-chevron-down');
        
        dropdownMenu.classList.toggle('max-h-0');
        dropdownMenu.classList.toggle('max-h-40');
        chevronIcon.classList.toggle('rotate-180');
      }
    </script>
  </body>
</html>