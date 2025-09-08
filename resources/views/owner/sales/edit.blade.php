<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Sales Order - Pare Custom</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" />
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Raleway', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">
<div class="flex">
    <x-navbar-owner />
    <div class="flex-1 lg:w-5/6">
        <x-navbar-top-owner />
        <div class="p-4 lg:p-8">
            <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
                <h1 class="text-2xl font-semibold text-gray-800 mb-4">Edit Sales Order</h1>
                @if (session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                        {{ session('error') }}
                    </div>
                @endif
                <form action="{{ route('owner.sales.update', $salesOrder) }}" method="POST" id="soForm">
                    @csrf
                    @method('PUT')
                    <div class="grid md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="order_type" class="block font-medium mb-1">Tipe Order</label>
                            <div class="flex items-center space-x-4">
                                <label><input type="radio" name="order_type" value="jahit_sendiri" {{ $salesOrder->order_type == 'jahit_sendiri' ? 'checked' : '' }} class="mr-2">Jahit Sendiri</label>
                                <label><input type="radio" name="order_type" value="beli_jadi" {{ $salesOrder->order_type == 'beli_jadi' ? 'checked' : '' }} class="mr-2">Langsung Beli Jadi</label>
                            </div>
                            @error('order_type')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="order_date" class="block font-medium mb-1">Tanggal Order</label>
<input type="date" name="order_date" id="order_date" 
    value="{{ old('order_date', \Carbon\Carbon::parse($salesOrder->order_date)->format('Y-m-d')) }}"
                                   required class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">
                            @error('order_date')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="customer_id" class="block font-medium mb-1">Customer</label>
                            <select name="customer_id" id="customer_id" class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">
                                <option value="">Guest</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id', $salesOrder->customer_id) == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="payment_method" class="block font-medium mb-1">Metode Pembayaran</label>
                            <select name="payment_method" id="payment_method" required class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">
                                <option value="cash" {{ old('payment_method', $salesOrder->payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="transfer" {{ old('payment_method', $salesOrder->payment_method) == 'transfer' ? 'selected' : '' }}>Transfer</option>
                                <option value="split" {{ old('payment_method', $salesOrder->payment_method) == 'split' ? 'selected' : '' }}>Split</option>
                            </select>
                            @error('payment_method')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="payment_status" class="block font-medium mb-1">Status Pembayaran</label>
                            <select name="payment_status" id="payment_status" required class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">
                                <option value="dp" {{ old('payment_status', $salesOrder->payment_status) == 'dp' ? 'selected' : '' }}>DP</option>
                                <option value="lunas" {{ old('payment_status', $salesOrder->payment_status) == 'lunas' ? 'selected' : '' }}>Lunas</option>
                            </select>
                            @error('payment_status')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-6">
                        <h2 class="text-lg font-semibold mb-4 text-gray-800">Item Order</h2>
                        <div id="items-container" class="space-y-4">
                            @foreach($salesOrder->items as $index => $item)
                                <div class="item-row grid md:grid-cols-5 gap-4">
                                    <div>
                                        <label class="block font-medium mb-1">Produk</label>
                                        <select name="items[{{ $index }}][product_id]" class="product-select border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">
                                            <option value="">Pilih Produk</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}"
                                                        data-name="{{ $product->name }}"
                                                        data-sku="{{ $product->sku }}"
                                                        data-price="{{ $product->price }}"
                                                        {{ $item->product_id == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="items[{{ $index }}][product_name]" class="product-name" value="{{ $item->product_name }}">
                                        @error("items.$index.product_name")
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block font-medium mb-1">SKU</label>
                                        <input type="text" name="items[{{ $index }}][sku]" class="sku border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300" value="{{ $item->sku }}" readonly>
                                        @error("items.$index.sku")
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block font-medium mb-1">Harga</label>
                                        <input type="number" name="items[{{ $index }}][sale_price]" class="sale-price border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300" step="0.01" required value="{{ $item->sale_price }}">
                                        @error("items.$index.sale_price")
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block font-medium mb-1">Qty</label>
                                        <input type="number" name="items[{{ $index }}][qty]" class="qty border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300" min="1" required value="{{ $item->qty }}">
                                        @error("items.$index.qty")
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block font-medium mb-1">Diskon</label>
                                        <input type="number" name="items[{{ $index }}][discount]" class="discount border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300" min="0" step="0.01" value="{{ $item->discount ?? 0 }}">
                                        @error("items.$index.discount")
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                        @if($index > 0)
                                            <button type="button" class="remove-item text-red-600 hover:text-red-800 mt-2"><i class="bi bi-trash"></i></button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" id="add-item" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow mt-4">
                            <i class="bi bi-plus-circle"></i> Tambah Item
                        </button>
                    </div>

                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded shadow">
                        <i class="bi bi-save"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let itemIndex = {{ count($salesOrder->items) }};
    document.getElementById('add-item').addEventListener('click', function () {
        const container = document.getElementById('items-container');
        const newRow = document.createElement('div');
        newRow.className = 'item-row grid md:grid-cols-5 gap-4 mt-4';
        newRow.innerHTML = `
            <div>
                <label class="block font-medium mb-1">Produk</label>
                <select name="items[${itemIndex}][product_id]" class="product-select border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">
                    <option value="">Pilih Produk</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}"
                                data-name="{{ $product->name }}"
                                data-sku="{{ $product->sku }}"
                                data-price="{{ $product->price }}">{{ $product->name }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="items[${itemIndex}][product_name]" class="product-name">
            </div>
            <div>
                <label class="block font-medium mb-1">SKU</label>
                <input type="text" name="items[${itemIndex}][sku]" class="sku border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300" readonly>
            </div>
            <div>
                <label class="block font-medium mb-1">Harga</label>
                <input type="number" name="items[${itemIndex}][sale_price]" class="sale-price border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300" step="0.01" required>
            </div>
            <div>
                <label class="block font-medium mb-1">Qty</label>
                <input type="number" name="items[${itemIndex}][qty]" class="qty border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300" min="1" required>
            </div>
            <div>
                <label class="block font-medium mb-1">Diskon</label>
                <input type="number" name="items[${itemIndex}][discount]" class="discount border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300" min="0" step="0.01" value="0">
                <button type="button" class="remove-item text-red-600 hover:text-red-800 mt-2"><i class="bi bi-trash"></i></button>
            </div>
        `;
        container.appendChild(newRow);
        itemIndex++;
    });

    document.getElementById('items-container').addEventListener('change', function (e) {
        if (e.target.classList.contains('product-select')) {
            const row = e.target.closest('.item-row');
            const select = e.target;
            const selectedOption = select.options[select.selectedIndex];
            const productName = selectedOption ? selectedOption.dataset.name || '' : '';
            const sku = selectedOption ? selectedOption.dataset.sku || '' : '';
            const price = selectedOption && selectedOption.dataset.price ? parseFloat(selectedOption.dataset.price) : 0;

            const productNameInput = row.querySelector('.product-name');
            const skuInput = row.querySelector('.sku');
            const salePriceInput = row.querySelector('.sale-price');

            productNameInput.value = productName;
            skuInput.value = sku;
            salePriceInput.value = price > 0 ? price.toFixed(2) : '';

            console.log('Product selected:', {
                productId: select.value,
                productName: productName,
                sku: sku,
                price: price
            });

            // Validasi harga
            if (price <= 0) {
                alert('Harga produk tidak valid atau kosong. Harap periksa data produk di database.');
            }
        }
    });

    document.getElementById('items-container').addEventListener('click', function (e) {
        if (e.target.closest('.remove-item')) {
            const row = e.target.closest('.item-row');
            if (document.querySelectorAll('.item-row').length > 1) {
                row.remove();
            } else {
                alert('Minimal satu item harus ada.');
            }
        }
    });

    document.getElementById('soForm').addEventListener('submit', function (e) {
        const salePriceInputs = document.querySelectorAll('.sale-price');
        for (let input of salePriceInputs) {
            if (!input.value || parseFloat(input.value) <= 0) {
                e.preventDefault();
                alert('Harga produk tidak boleh kosong atau nol. Silakan pilih produk yang valid.');
                return;
            }
        }
    });
</script>
</body>
</html>