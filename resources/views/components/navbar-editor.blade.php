<div
  id="sidebar"
  class="fixed lg:sticky top-0 left-0 w-64 lg:w-1/6 bg-[#FCFCFC] h-screen flex flex-col transition-transform transform -translate-x-full lg:translate-x-0 text-[#000000] border-r-2 z-40 text-sm"
  >
  <!-- Sidebar Header -->
  <div class="flex items-center justify-between mb-4 p-4">
    <img src="{{ asset('assets/logo.png') }}" alt="" class="w-auto h-40 mx-auto xl:h-40 lg:h-36">
    <button
      class="lg:hidden text-gray-400 hover:text-gray-600"
      onclick="toggleSidebar()"
    >
      <i class="bi bi-x text-2xl"></i>
    </button>
  </div>

  <div class="w-full border-b-2 border-gray-300"></div>

  <!-- Menu Items -->
  <div class=" flex-1 p-4">
    <!-- Beranda -->
    <a href="{{ route('editor.dashboard') }}" class="flex items-center p-3 rounded-md bg-none transition hover-link">
      <i class="bi bi-house-door-fill"></i>
      <span class="ml-3 nav-text font-semibold">Beranda</span>
    </a>

    <!-- Catalog Management -->
    <div>
      <button
        onclick="toggleDropdown(this)"
        class="flex items-center justify-between w-full p-3 rounded-md bg-none transition focus:outline-none hover-link">
        <span class="flex items-center">
          <i class="bi bi-grid-3x3-gap"></i>
          <span class="ml-3 nav-text font-semibold">Katalog</span>
        </span>
        <i class="bi bi-chevron-down transition-transform"></i>
      </button>
      <div class="dropdown-menu space-y-2 overflow-hidden max-h-0 transition-all duration-300">
        <a href="#" class="block p-3 rounded-md bg-none transition mt-2 hover-link">
          <span class="nav-text font-semibold">Kategori</span>
        </a>
        <a href="#" class="block p-3 rounded-md bg-none transition hover-link">
          <span class="nav-text font-semibold">Produk</span>
        </a>
      </div>
    </div>

    <!-- Content Management -->
    <a href="#" class="flex items-center p-3 rounded-md bg-none transition hover-link">
      <i class="bi bi-file-text"></i>
      <span class="ml-3 nav-text font-semibold">Content Management</span>
    </a>

    <!-- Data Entry -->
    <a href="#" class="flex items-center p-3 rounded-md bg-none transition hover-link">
      <i class="bi bi-pencil-square"></i>
      <span class="ml-3 nav-text font-semibold">Data Entry</span>
    </a>

    <!-- Media Library -->
    <a href="#" class="flex items-center p-3 rounded-md bg-none transition hover-link">
      <i class="bi bi-images"></i>
      <span class="ml-3 nav-text font-semibold">Media Library</span>
    </a>

    <!-- Quality Control -->
    <a href="#" class="flex items-center p-3 rounded-md bg-none transition hover-link">
      <i class="bi bi-shield-check"></i>
      <span class="ml-3 nav-text font-semibold">Quality Control</span>
    </a>

    <!-- Publishing -->
    <a href="#" class="flex items-center p-3 rounded-md bg-none transition hover-link">
      <i class="bi bi-cloud-upload"></i>
      <span class="ml-3 nav-text font-semibold">Publishing</span>
    </a>

  </div>

  <!-- Logout -->
  <form action="{{ route('logout') }}" method="POST" class="flex items-center p-3 rounded-md bg-none transition hover-link mt-auto mx-4 mb-4">
    @csrf
    <button type="submit" class="flex items-center">
        <i class="bi bi-box-arrow-right"></i>
        <span class="ml-3 nav-text font-semibold">Logout</span>
    </button>
  </form>
</div>