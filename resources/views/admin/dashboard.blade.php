<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            <i class="bi bi-gear mr-2"></i>{{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex items-center mb-4">
                        <i class="bi bi-person-badge-fill text-purple-500 text-2xl mr-3"></i>
                        <div>
                            <h3 class="text-lg font-semibold">Selamat Datang, {{ Auth::user()->name }}!</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Role: Admin</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg">
                            <h4 class="font-semibold text-purple-800 dark:text-purple-200">System Admin</h4>
                            <p class="text-sm text-purple-600 dark:text-purple-300 mt-1">Kelola konfigurasi sistem</p>
                        </div>
                        <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">
                            <h4 class="font-semibold text-red-800 dark:text-red-200">User Management</h4>
                            <p class="text-sm text-red-600 dark:text-red-300 mt-1">Administrasi pengguna dan permissions</p>
                        </div>
                        <div class="bg-indigo-50 dark:bg-indigo-900/20 p-4 rounded-lg">
                            <h4 class="font-semibold text-indigo-800 dark:text-indigo-200">Data Admin</h4>
                            <p class="text-sm text-indigo-600 dark:text-indigo-300 mt-1">Administrasi data master</p>
                        </div>
                    </div>
                    
                    <div class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                        <p class="text-sm text-yellow-800 dark:text-yellow-200">
                            <i class="bi bi-info-circle mr-1"></i>
                            Fitur Admin sedang dalam tahap pengembangan. Sistem role berhasil diimplementasi!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>