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
    <a href="{{ route('admin.dashboard') }}" class="flex items-center p-3 rounded-md bg-none transition hover-link">
      <i class="bi bi-house-door-fill"></i>
      <span class="ml-3 nav-text font-semibold">Beranda</span>
    </a>

    <!-- System Configuration -->
    <a href="#" class="flex items-center p-3 rounded-md bg-none transition hover-link">
      <i class="bi bi-gear-fill"></i>
      <span class="ml-3 nav-text font-semibold">Konfigurasi Sistem</span>
    </a>

    <!-- User Management -->
    <div>
      <button
        onclick="toggleDropdown(this)"
        class="flex items-center justify-between w-full p-3 rounded-md bg-none transition focus:outline-none hover-link">
        <span class="flex items-center">
          <i class="bi bi-people-fill"></i>
          <span class="ml-3 nav-text font-semibold">User Management</span>
        </span>
        <i class="bi bi-chevron-down transition-transform"></i>
      </button>
      <div class="dropdown-menu space-y-2 overflow-hidden max-h-0 transition-all duration-300">
        <a href="#" class="block p-3 rounded-md bg-none transition mt-2 hover-link">
          <span class="nav-text font-semibold">Kelola User</span>
        </a>
        <a href="#" class="block p-3 rounded-md bg-none transition hover-link">
          <span class="nav-text font-semibold">Role & Permissions</span>
        </a>
      </div>
    </div>

    <!-- Data Master -->
    <a href="#" class="flex items-center p-3 rounded-md bg-none transition hover-link">
      <i class="bi bi-database-fill"></i>
      <span class="ml-3 nav-text font-semibold">Data Master</span>
    </a>

    <!-- Backup & Restore -->
    <a href="#" class="flex items-center p-3 rounded-md bg-none transition hover-link">
      <i class="bi bi-cloud-arrow-up"></i>
      <span class="ml-3 nav-text font-semibold">Backup & Restore</span>
    </a>

    <!-- Activity Logs -->
    <a href="#" class="flex items-center p-3 rounded-md bg-none transition hover-link">
      <i class="bi bi-list-ul"></i>
      <span class="ml-3 nav-text font-semibold">Activity Logs</span>
    </a>

    <!-- System Monitoring -->
    <a href="#" class="flex items-center p-3 rounded-md bg-none transition hover-link">
      <i class="bi bi-speedometer2"></i>
      <span class="ml-3 nav-text font-semibold">System Monitor</span>
    </a>

  </div>

  Logout
  <form action="{{ route('logout') }}" method="POST" class="flex items-center p-3 rounded-md bg-none transition hover-link mt-auto mx-4 mb-4">
    @csrf
    <button type="submit" class="flex items-center">
        <i class="bi bi-box-arrow-right"></i>
        <span class="ml-3 nav-text font-semibold">Logout</span>
    </button>
  </form>
</div>