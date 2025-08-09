<div class="flex justify-between items-center p-4 bg-white shadow">
    <!-- Bagian Kiri: Dashboard Label -->
    <div class="flex items-center">
        <i class="fas fa-chart-bar text-gray-500 mr-2"></i>
        <span class="text-gray-700 font-semibold">Custom Pare - Beriman</span>
    </div>

    <!-- Bagian Kanan: Profile dan Notifikasi -->
    <div class="flex items-center space-x-4">
        <!-- Notifikasi -->
        <a href="{{ route('karyawan.notification.index') }}" id="notification-bell" class="relative text-gray-500 text-xl cursor-pointer">
            <i class="bi bi-bell-fill"></i>
            <span id="notification-count" class="absolute top-0 right-0 rounded-full bg-red-500 text-white px-1 text-xs" style="display: none;"></span>
        </a>

        <!-- Profil Pengguna -->
        <div class="flex items-center">
            <span class="text-gray-700 mr-2">Hai, {{ ucfirst(auth()->user()->name) . (' - ') . ucfirst(auth()->user()->usertype) }}</span>

            <a href="{{ route('karyawan.profile.index') }}" class="flex items-center hover:opacity-80 transition-opacity">
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function updateNotificationCount() {
            fetch('/karyawan/notifications/unread-count')
                .then(response => response.json())
                .then(data => {
                    const notificationCountElement = document.getElementById('notification-count');
                    if (data.count > 0) {
                        notificationCountElement.textContent = data.count;
                        notificationCountElement.style.display = 'block';
                    } else {
                        notificationCountElement.style.display = 'none';
                    }
                });
        }

        updateNotificationCount();

        document.getElementById('notification-bell').addEventListener('click', function (e) {
            e.preventDefault();
            fetch('/karyawan/notifications/mark-as-read', { 
                method: 'POST', 
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                }
            })
            .then(response => response.json())
            .then(() => {
                updateNotificationCount();
                window.location.href = '{{ route('karyawan.notification.index') }}';
            });
        });
    });
</script>