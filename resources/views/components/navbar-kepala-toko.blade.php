<style>
    body {
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
    <div class="flex-1 p-4">
        <!-- Beranda -->
        <a href="{{ route('kepala_toko.dashboard') }}" class="flex items-center p-3 rounded-md bg-none transition hover-link">
            <i class="bi bi-house-door-fill"></i>
            <span class="ml-3 nav-text font-semibold">Beranda</span>
        </a>
        <!-- Produk Dropdown -->


        <!-- Customer & Supplier -->
        <a href="{{ route('kepala_toko.contacts.index') }}" class="flex items-center p-3 rounded-md bg-none transition hover-link">
            <i class="bi bi-people-fill"></i>
            <span class="ml-3 nav-text font-semibold">Customer & Supplier</span>
        </a>

        <!-- Placeholder for other menus -->
        <a href="{{ route('kepala_toko.dashboard') }}" class="flex items-center p-3 rounded-md bg-none transition hover-link">
            <i class="bi bi-clipboard-data"></i>
            <span class="ml-3 nav-text font-semibold">Inventory</span>
        </a>

        <a href="{{ route('kepala_toko.dashboard') }}" class="flex items-center p-3 rounded-md bg-none transition hover-link">
            <i class="bi bi-cash-stack"></i>
            <span class="ml-3 nav-text font-semibold">Penjualan</span>
        </a>

        <a href="{{ route('kepala_toko.dashboard') }}" class="flex items-center p-3 rounded-md bg-none transition hover-link">
            <i class="bi bi-cart-check"></i>
            <span class="ml-3 nav-text font-semibold">Pembelian</span>
        </a>
    </div>

    <form action="{{ route('logout') }}" method="POST" class="flex items-center p-3 rounded-md bg-none transition hover-link mt-auto mx-4 mb-4">
        @csrf
        <button type="submit" class="flex items-center">
            <i class="bi bi-box-arrow-right"></i>
            <span class="ml-3 nav-text font-semibold">Logout</span>
        </button>
    </form>
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