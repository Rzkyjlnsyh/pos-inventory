<div class="flex justify-between items-center p-4 bg-white shadow sticky top-0 z-50">
    <!-- Bagian Kiri: Dashboard Label -->
    <div class="flex items-center">
        <i class="bi bi-person-badge text-gray-500 mr-2"></i>
        <span class="text-gray-700 font-semibold">Kepala Toko Dashboard - Custom Pare</span>
    </div>

    <!-- Bagian Kanan: Profile dan Notifikasi -->
    <div class="flex items-center space-x-4">
        <!-- Notifikasi -->
        <a href="#" id="notification-bell" class="relative text-gray-500 text-xl cursor-pointer">
            <i class="bi bi-bell-fill"></i>
            <span id="notification-count" class="absolute top-0 right-0 rounded-full bg-red-500 text-white px-1 text-xs" style="display: none;"></span>
        </a>

        <!-- Profil Pengguna -->
        <div class="flex items-center">
            <span class="text-gray-700 mr-2">Hai, {{ ucfirst(auth()->user()->name) . (' - ') . ucfirst(auth()->user()->usertype) }}</span>

            <a href="#" class="flex items-center hover:opacity-80 transition-opacity">
                @php
                    $avatarPath = auth()->user()->avatar 
                        ? asset('storage/avatars/' . auth()->user()->avatar) 
                        : asset('storage/avatars/dummy.jpeg');
                @endphp
                <img src="{{ $avatarPath }}" alt="Profile" class="w-10 h-10 rounded-full border-2 border-gray-300 object-cover">
            </a>
        </div>
    </div>
</div>