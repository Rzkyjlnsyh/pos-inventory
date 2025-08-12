<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            <i class="bi bi-pencil-square mr-2"></i>{{ __('Editor Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex items-center mb-4">
                        <i class="bi bi-person-badge-fill text-orange-500 text-2xl mr-3"></i>
                        <div>
                            <h3 class="text-lg font-semibold">Selamat Datang, {{ Auth::user()->name }}!</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Role: Editor</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-lg">
                            <h4 class="font-semibold text-orange-800 dark:text-orange-200">Content Editor</h4>
                            <p class="text-sm text-orange-600 dark:text-orange-300 mt-1">Edit data dan konten sistem</p>
                        </div>
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg">
                            <h4 class="font-semibold text-yellow-800 dark:text-yellow-200">Data Management</h4>
                            <p class="text-sm text-yellow-600 dark:text-yellow-300 mt-1">Kelola data produk dan katalog</p>
                        </div>
                        <div class="bg-teal-50 dark:bg-teal-900/20 p-4 rounded-lg">
                            <h4 class="font-semibold text-teal-800 dark:text-teal-200">Content Review</h4>
                            <p class="text-sm text-teal-600 dark:text-teal-300 mt-1">Review dan validasi konten</p>
                        </div>
                    </div>
                    
                    <div class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                        <p class="text-sm text-yellow-800 dark:text-yellow-200">
                            <i class="bi bi-info-circle mr-1"></i>
                            Fitur Editor sedang dalam tahap pengembangan. Sistem role berhasil diimplementasi!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>