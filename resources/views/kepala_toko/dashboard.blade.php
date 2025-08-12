<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            <i class="bi bi-person-badge mr-2"></i>{{ __('Kepala Toko Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex items-center mb-4">
                        <i class="bi bi-person-badge-fill text-green-500 text-2xl mr-3"></i>
                        <div>
                            <h3 class="text-lg font-semibold">Selamat Datang, {{ Auth::user()->name }}!</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Role: Kepala Toko</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                            <h4 class="font-semibold text-green-800 dark:text-green-200">Supervisory</h4>
                            <p class="text-sm text-green-600 dark:text-green-300 mt-1">Supervisi operasional harian toko</p>
                        </div>
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                            <h4 class="font-semibold text-blue-800 dark:text-blue-200">Approval</h4>
                            <p class="text-sm text-blue-600 dark:text-blue-300 mt-1">Approve stock opname dan transaksi</p>
                        </div>
                        <div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-lg">
                            <h4 class="font-semibold text-orange-800 dark:text-orange-200">Reporting</h4>
                            <p class="text-sm text-orange-600 dark:text-orange-300 mt-1">Akses laporan operasional</p>
                        </div>
                    </div>
                    
                    <div class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                        <p class="text-sm text-yellow-800 dark:text-yellow-200">
                            <i class="bi bi-info-circle mr-1"></i>
                            Fitur Kepala Toko sedang dalam tahap pengembangan. Sistem role berhasil diimplementasi!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>