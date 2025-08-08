<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Kategori</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" class="mb-4">
                        <input type="text" name="q" value="{{ $q }}" placeholder="Cari kategori" class="border rounded p-2 text-gray-900" />
                        <button class="bg-gray-200 px-3 py-2 rounded">Cari</button>
                        <a href="{{ route('owner.category.create') }}" class="bg-indigo-600 text-white px-3 py-2 rounded ml-2">Tambah</a>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="px-3 py-2">Nama</th>
                                    <th class="px-3 py-2">Aktif</th>
                                    <th class="px-3 py-2">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $category)
                                <tr class="border-b">
                                    <td class="px-3 py-2">{{ $category->name }}</td>
                                    <td class="px-3 py-2">{{ $category->is_active ? 'Ya' : 'Tidak' }}</td>
                                    <td class="px-3 py-2">
                                        <a href="{{ route('owner.category.edit', $category) }}" class="text-indigo-600">Edit</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">{{ $categories->withQueryString()->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>