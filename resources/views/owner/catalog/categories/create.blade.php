<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Tambah Kategori</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('owner.catalog.category.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block mb-1">Nama</label>
                            <input name="name" required class="border rounded p-2 w-full text-gray-900" />
                        </div>
                        <div>
                            <label class="block mb-1">Parent</label>
                            <select name="parent_id" class="border rounded p-2 w-full text-gray-900">
                                <option value="">-</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1">Deskripsi</label>
                            <textarea name="description" class="border rounded p-2 w-full text-gray-900"></textarea>
                        </div>
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" name="is_active" value="1" checked />
                            <span>Aktif</span>
                        </div>
                        <div class="pt-4">
                            <button class="bg-indigo-600 text-white px-4 py-2 rounded">Simpan</button>
                            <a href="{{ route('owner.category.index') }}" class="ml-2 px-4 py-2 rounded border">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>