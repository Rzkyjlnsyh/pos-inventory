<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Edit Produk</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('owner.catalog.products.update', $product) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block mb-1">SKU</label>
                            <input name="sku" class="border rounded p-2 w-full text-gray-900" value="{{ old('sku', $product->sku) }}" />
                        </div>
                        <div>
                            <label class="block mb-1">Nama</label>
                            <input name="name" required class="border rounded p-2 w-full text-gray-900" value="{{ old('name', $product->name) }}" />
                        </div>
                        <div>
                            <label class="block mb-1">Kategori</label>
                            <select name="category_id" class="border rounded p-2 w-full text-gray-900">
                                <option value="">-</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id)==$cat->id)>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-1">Harga Beli</label>
                                <input name="cost_price" type="number" step="0.01" required class="border rounded p-2 w-full text-gray-900" value="{{ old('cost_price', $product->cost_price) }}" />
                            </div>
                            <div>
                                <label class="block mb-1">Harga Jual</label>
                                <input name="price" type="number" step="0.01" required class="border rounded p-2 w-full text-gray-900" value="{{ old('price', $product->price) }}" />
                            </div>
                        </div>
                        <div>
                            <label class="block mb-1">Gambar</label>
                            <input type="file" name="image" accept="image/*" class="border rounded p-2 w-full text-gray-900" />
                            @if($product->image_path)
                            <div class="mt-2 text-sm">Gambar saat ini: <a href="{{ Storage::disk('public')->url($product->image_path) }}" target="_blank" class="text-indigo-600">Lihat</a></div>
                            @endif
                        </div>
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active)) />
                            <span>Aktif</span>
                        </div>
                        <div class="pt-4">
                            <button class="bg-indigo-600 text-white px-4 py-2 rounded">Update</button>
                            <a href="{{ route('owner.catalog.products.index') }}" class="ml-2 px-4 py-2 rounded border">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>