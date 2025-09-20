<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tambah Kategori - Pare Custom</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Bootstrap Icons CDN -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css"
    />
    {{-- Font Cdn --}}
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet">
    <style>
      body{
        font-family: 'Raleway', sans-serif;
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
      <x-navbar-editor></x-navbar-editor>

      <!-- Main Content -->
      <div class="flex-1 lg:w-5/6">
        {{-- Navbar Top --}}
        <x-navbar-top-editor></x-navbar-top-editor>

        <!-- Content Wrapper -->
        <div class="p-4 lg:p-8">
          <div class="p-6 bg-gray-100 min-h-screen">
              <div class="max-w-3xl mx-auto">
                  <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-gray-800">Tambah Kategori</h1>
                    <a href="{{ route('editor.category.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                        <i class="bi bi-arrow-left mr-1"></i>Kembali
                    </a>
                  </div>
          
                  <div class="bg-white rounded-lg shadow overflow-hidden">
                      <div class="p-6">
                        <form method="POST" action="{{ route('editor.category.store') }}" class="space-y-6">
                            @csrf
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Kategori *</label>
                                <input name="name" required 
                                       class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200"
                                       placeholder="Masukkan nama kategori">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                                <textarea name="description" rows="3"
                                          class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200"
                                          placeholder="Masukkan deskripsi kategori"></textarea>
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                <input type="checkbox" name="is_active" value="1" checked 
                                       class="w-4 h-4 text-orange-500 border-gray-300 rounded focus:ring-orange-500">
                                <span class="text-sm text-gray-700">Aktif</span>
                            </div>
                            
                            <div class="pt-6 border-t border-gray-200">
                                <button type="submit" 
                                        class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2.5 rounded-lg font-medium transition-all duration-200">
                                    <i class="bi bi-check-circle mr-2"></i>Simpan Kategori
                                </button>
                                <a href="{{ route('editor.category.index') }}" 
                                   class="ml-3 px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-all duration-200">
                                    Batal
                                </a>
                            </div>
                        </form>
                      </div>
                  </div>
              </div>
          </div>
        </div>
      </div>

      <!-- Scripts -->
      <script>
        function toggleSidebar() {
          const sidebar = document.querySelector('#sidebar');
          sidebar.classList.toggle('-translate-x-full');
        }
      </script>
    </body>
</html>