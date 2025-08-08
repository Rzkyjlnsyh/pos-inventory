<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Edit Kategori</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('owner.catalog.category.update', $category) }}" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block mb-1">Nama</label>
                            <input name="name" required class="border rounded p-2 w-full text-gray-900" value="{{ old('name', $category->name) }}" />
                        </div>
                        <div>
                            <label class="block mb-1">Parent</label>
                            <select name="parent_id" class="border rounded p-2 w-full text-gray-900">
                                <option value="">-</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('parent_id', $category->parent_id)==$cat->id)>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1">Deskripsi</label>
                            <textarea name="description" class="border rounded p-2 w-full text-gray-900">{{ old('description', $category->description) }}</textarea>
                        </div>
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active)) />
                            <span>Aktif</span>
                        </div>
                        <div class="pt-4">
                            <button class="bg-indigo-600 text-white px-4 py-2 rounded">Update</button>
                            <a href="{{ route('owner.category.index') }}" class="ml-2 px-4 py-2 rounded border">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>