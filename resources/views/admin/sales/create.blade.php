<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Buat Sales Order - Pare Custom</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" />
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Raleway', sans-serif; }
        .nav-text {
            position: relative;
            display: inline-block;
        }
        .nav-text::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: #e17f12;
            transition: width 0.2s ease-in-out;
        }
        .hover-link:hover .nav-text::after {
            width: 100%;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            text-align: center;
        }
    </style>
</head>
<body class="bg-gray-100">
<div class="flex">
    <x-navbar-admin />
    <div class="flex-1 lg:w-5/6">
        <x-navbar-top-admin />
        <div class="p-4 lg:p-8">
            <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
                <h1 class="text-2xl font-semibold text-gray-800 mb-4">Buat Sales Order</h1>
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if (session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif
                <form action="{{ route('admin.sales.store') }}" method="POST" id="soForm" enctype="multipart/form-data">
                    @csrf
                    <div class="grid md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="order_type" class="block font-medium mb-1">Tipe Order</label>
                            <div class="flex items-center space-x-4">
                                <label><input type="radio" name="order_type" value="jahit_sendiri" checked class="mr-2">Jahit Sendiri</label>
                                <label><input type="radio" name="order_type" value="beli_jadi" class="mr-2">Langsung Beli Jadi</label>
                            </div>
                            @error('order_type')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="order_date" class="block font-medium mb-1">Tanggal Order</label>
                            <input type="datetime-local" name="order_date" id="order_date" value="{{ old('order_date', now()->format('Y-m-d\TH:i')) }}" required class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">
                            @error('order_date')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="customer_id" class="block font-medium mb-1">Customer</label>
                            <select name="customer_id" id="customer_id" class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">
                                <option value="">Guest</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="payment_method" class="block font-medium mb-1">Metode Pembayaran</label>
                            <select name="payment_method" id="payment_method" required class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="transfer" {{ old('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                                <option value="split" {{ old('payment_method') == 'split' ? 'selected' : '' }}>Split</option>
                            </select>
                            @error('payment_method')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="payment_status" class="block font-medium mb-1">Status Pembayaran</label>
                            <select name="payment_status" id="payment_status" class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">
                                <option value="dp" {{ old('payment_status') == 'dp' ? 'selected' : '' }}>DP</option>
                                <option value="lunas" {{ old('payment_status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                            </select>
                            @error('payment_status')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-6">
    <h2 class="text-lg font-semibold mb-4 text-gray-800">Pembayaran (Opsional)</h2>
    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label for="payment_amount" class="block font-medium mb-1">Jumlah Pembayaran (Total)</label>
            <input type="number" name="payment_amount" id="payment_amount" class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300" step="0.01" min="0" value="{{ old('payment_amount') }}">
            <p id="dp-info" class="text-sm text-gray-600 mt-1"></p>
            @error('payment_amount')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div id="split-payment-fields" class="hidden md:col-span-2">
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label for="cash_amount" class="block font-medium mb-1">Jumlah Cash</label>
                    <input type="number" name="cash_amount" id="cash_amount" class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300" step="0.01" min="0" value="{{ old('cash_amount') }}">
                    @error('cash_amount')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="transfer_amount" class="block font-medium mb-1">Jumlah Transfer</label>
                    <input type="number" name="transfer_amount" id="transfer_amount" class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300" step="0.01" min="0" value="{{ old('transfer_amount') }}">
                    @error('transfer_amount')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
        <div id="proof-field" class="mt-4 hidden md:col-span-2">
            <label for="proof_path" class="block font-medium mb-1">Bukti Transfer (jpg, png, pdf, opsional)</label>
            <input type="file" name="proof_path" id="proof_path" accept=".jpg,.jpeg,.png,.pdf" class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">
            <p class="text-sm text-gray-600 mt-1">Opsional untuk metode transfer atau split</p>
            @error('proof_path')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="paid_at" class="block font-medium mb-1">Tanggal Pembayaran</label>
            <input type="datetime-local" name="paid_at" id="paid_at" value="{{ old('paid_at', now()->format('Y-m-d\TH:i')) }}" class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">
            @error('paid_at')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

                    <div class="mb-6">
                        <h2 class="text-lg font-semibold mb-4 text-gray-800">Item Order</h2>
                        <div id="items-container" class="space-y-4">
                            <div class="item-row grid md:grid-cols-5 gap-4">
                                <div>
                                    <label class="block font-medium mb-1">Produk</label>
                                    <select name="items[0][product_id]" class="product-select border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">
                                        <option value="">Pilih Produk</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}"
                                                    data-name="{{ $product->name }}"
                                                    data-sku="{{ $product->sku }}"
                                                    data-price="{{ $product->price }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="items[0][product_name]" class="product-name">
                                    @error('items.0.product_name')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block font-medium mb-1">SKU</label>
                                    <input type="text" name="items[0][sku]" class="sku border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300" readonly>
                                    @error('items.0.sku')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block font-medium mb-1">Harga</label>
                                    <input type="number" name="items[0][sale_price]" class="sale-price border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300" step="0.01" required>
                                    @error('items.0.sale_price')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block font-medium mb-1">Qty</label>
                                    <input type="number" name="items[0][qty]" class="qty border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300" min="1" required>
                                    @error('items.0.qty')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block font-medium mb-1">Diskon</label>
                                    <input type="number" name="items[0][discount]" class="discount border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300" min="0" step="0.01" value="0">
                                    @error('items.0.discount')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <button type="button" id="add-item" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow mt-4">
                            <i class="bi bi-plus-circle"></i> Tambah Item
                        </button>
                    </div>

                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded shadow">
                        <i class="bi bi-save"></i> Simpan Sales Order
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let itemIndex = 1;
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

            row.querySelector('.product-name').value = productName;
            row.querySelector('.sku').value = sku;
            row.querySelector('.sale-price').value = price > 0 ? price.toFixed(2) : '';

            if (price <= 0) {
                alert('Harga produk tidak valid atau kosong. Harap periksa data produk di database.');
            }

            updateGrandTotal();
        }

        if (e.target.classList.contains('qty') || e.target.classList.contains('sale-price') || e.target.classList.contains('discount')) {
            updateGrandTotal();
        }
    });

    document.getElementById('items-container').addEventListener('click', function (e) {
        if (e.target.closest('.remove-item')) {
            const row = e.target.closest('.item-row');
            if (document.querySelectorAll('.item-row').length > 1) {
                row.remove();
                updateGrandTotal();
            } else {
                alert('Minimal satu item harus ada.');
            }
        }
    });

    let grandTotal = 0;

    function updateGrandTotal() {
        const items = document.querySelectorAll('.item-row');
        let subtotal = 0;
        let discountTotal = 0;

        items.forEach(item => {
            const price = parseFloat(item.querySelector('.sale-price').value) || 0;
            const qty = parseInt(item.querySelector('.qty').value) || 0;
            const discount = parseFloat(item.querySelector('.discount').value) || 0;
            const itemSubtotal = price * qty;
            const itemDiscount = discount * qty;
            subtotal += itemSubtotal;
            discountTotal += itemDiscount;
        });

        grandTotal = subtotal - discountTotal;
        const dpInfo = document.getElementById('dp-info');
        dpInfo.textContent = grandTotal > 0 ? `DP minimal 50%: Rp ${Math.ceil(grandTotal * 0.5).toLocaleString('id-ID')}` : '';
    }

    document.getElementById('payment_method').addEventListener('change', function () {
    const splitFields = document.getElementById('split-payment-fields');
    const proofField = document.getElementById('proof-field');
    splitFields.classList.toggle('hidden', this.value !== 'split');
    proofField.classList.toggle('hidden', !(this.value === 'transfer' || this.value === 'split')); // Munculkan proof_field untuk transfer juga
    if (this.value !== 'split') {
        document.getElementById('cash_amount').value = '';
        document.getElementById('transfer_amount').value = '';
    }
    if (this.value !== 'transfer' && this.value !== 'split') {
        document.getElementById('proof_path').value = '';
    }
    updatePaymentAmount();
    updateProofRequired(this.value); // Tambah ini untuk set required
});

function updatePaymentAmount() {
    const method = document.getElementById('payment_method').value;
    const cash = parseFloat(document.getElementById('cash_amount')?.value || 0);
    const transfer = parseFloat(document.getElementById('transfer_amount')?.value || 0);
    const total = method === 'split' ? cash + transfer : parseFloat(document.getElementById('payment_amount').value) || 0;
    document.getElementById('payment_amount').value = total.toFixed(2);
    updatePaymentStatus();
}
function updateProofRequired(method) {
    const proofInput = document.getElementById('proof_path');
    proofInput.required = (method === 'transfer' || method === 'split'); // Set required untuk transfer/split
}

    function updatePaymentStatus() {
        const paymentAmount = parseFloat(document.getElementById('payment_amount').value) || 0;
        const paymentStatusSelect = document.getElementById('payment_status');
        if (paymentAmount >= grandTotal && grandTotal > 0) {
            paymentStatusSelect.value = 'lunas';
        } else {
            paymentStatusSelect.value = 'dp';
        }
    }

    document.getElementById('payment_amount').addEventListener('input', updatePaymentStatus);
    document.getElementById('cash_amount')?.addEventListener('input', updatePaymentAmount);
    document.getElementById('transfer_amount')?.addEventListener('input', updatePaymentAmount);

    document.getElementById('soForm').addEventListener('submit', function (e) {
    console.log('Form submitted, validating...');
    const salePriceInputs = document.querySelectorAll('.sale-price');
    for (let input of salePriceInputs) {
        if (!input.value || parseFloat(input.value) <= 0) {
            e.preventDefault();
            alert('Harga produk tidak boleh kosong atau nol. Silakan pilih produk yang valid.');
            console.log('Validation failed: Invalid sale price');
            return;
        }
    }

    const paymentMethod = document.getElementById('payment_method').value;
    const paymentStatus = document.getElementById('payment_status').value;
    let paymentAmount = 0;

    if (paymentMethod === 'split') {
        const cashAmount = parseFloat(document.getElementById('cash_amount').value) || 0;
        const transferAmount = parseFloat(document.getElementById('transfer_amount').value) || 0;
        paymentAmount = cashAmount + transferAmount;
        if (transferAmount > 0 && !document.getElementById('proof_path').files[0]) {
            e.preventDefault();
            alert('Bukti pembayaran wajib untuk transfer di metode split.');
            console.log('Validation failed: Missing proof for split transfer');
            return;
        }
    } else if (paymentMethod === 'transfer') {
        paymentAmount = parseFloat(document.getElementById('payment_amount').value) || 0;
        if (paymentAmount > 0 && !document.getElementById('proof_path').files[0]) {
            e.preventDefault();
            alert('Bukti pembayaran wajib untuk metode transfer.');
            console.log('Validation failed: Missing proof for transfer');
            return;
        }
    } else {
        paymentAmount = parseFloat(document.getElementById('payment_amount').value) || 0;
    }

    if (paymentAmount > 0) {
        if (paymentStatus === 'dp' && paymentAmount < grandTotal * 0.5) {
            e.preventDefault();
            alert(`Jumlah pembayaran kurang dari DP minimal 50%: Rp ${(grandTotal * 0.5).toLocaleString('id-ID')}`);
            console.log('Validation failed: Payment amount below 50% DP');
            return;
        }
        if (paymentAmount > grandTotal) {
            e.preventDefault();
            alert(`Jumlah pembayaran melebihi grand total: Rp ${grandTotal.toLocaleString('id-ID')}`);
            console.log('Validation failed: Payment amount exceeds grand total');
            return;
        }
    }
    console.log('Validation passed, submitting form...');
});

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('-translate-x-full');
    }

    function toggleDropdown(button) {
        const dropdownMenu = button.nextElementSibling;
        const chevronIcon = button.querySelector('.bi-chevron-down');
        dropdownMenu.classList.toggle('max-h-0');
        dropdownMenu.classList.toggle('max-h-40');
        chevronIcon.classList.toggle('rotate-180');
    }

    updateGrandTotal();    let itemIndex = {{ count(old('items', [])) }};
    let grandTotal = 0;

    function updateGrandTotal() {
        const items = document.querySelectorAll('.item-row');
        let subtotal = 0;
        items.forEach(row => {
            const qty = parseInt(row.querySelector('.qty').value) || 0;
            const salePrice = parseFloat(row.querySelector('.sale-price').value) || 0;
            const discount = parseFloat(row.querySelector('.discount').value) || 0;
            subtotal += (salePrice * qty) - (discount * qty);
        });
        grandTotal = subtotal;
        document.getElementById('grand_total').value = grandTotal.toFixed(2);
        updatePaymentStatus();
    }

    function updatePaymentAmount() {
        const paymentMethod = document.getElementById('payment_method').value;
        const paymentAmountInput = document.getElementById('payment_amount');
        let paymentAmount = parseFloat(paymentAmountInput.value) || 0;
        if (paymentMethod === 'split') {
            const cashAmount = parseFloat(document.getElementById('cash_amount').value) || 0;
            const transferAmount = parseFloat(document.getElementById('transfer_amount').value) || 0;
            paymentAmount = cashAmount + transferAmount;
            paymentAmountInput.value = paymentAmount.toFixed(2);
        }
        updatePaymentStatus();
    }

    function updatePaymentStatus() {
        const paymentAmount = parseFloat(document.getElementById('payment_amount').value) || 0;
        const paymentStatus = document.getElementById('payment_status').value;
        const dpInfo = document.getElementById('dp-info');
        if (paymentStatus === 'dp' && paymentAmount > 0) {
            const minDp = grandTotal * 0.5;
            dpInfo.textContent = `Minimal DP 50%: Rp ${minDp.toLocaleString('id-ID')}`;
        } else {
            dpInfo.textContent = '';
        }
    }

    function updateProofRequired(method) {
        const proofField = document.getElementById('proof-field');
        proofField.classList.toggle('hidden', !(method === 'transfer' || method === 'split'));
    }

    document.getElementById('add-item').addEventListener('click', function () {
        const itemContainer = document.getElementById('items-container');
        const newItem = document.createElement('div');
        newItem.classList.add('item-row', 'grid', 'md:grid-cols-5', 'gap-4', 'mb-4');
        newItem.innerHTML = `
            <div>
                <label class="block font-medium mb-1">Produk</label>
                <select name="items[${itemIndex}][product_id]" class="product-select border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">
                    <option value="">Pilih Produk</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-sku="{{ $product->sku }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block font-medium mb-1">Nama Produk</label>
                <input type="text" name="items[${itemIndex}][product_name]" class="product-name border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300" required>
            </div>
            <div>
                <label class="block font-medium mb-1">SKU</label>
                <input type="text" name="items[${itemIndex}][sku]" class="sku border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">
            </div>
            <div>
                <label class="block font-medium mb-1">Harga</label>
                <input type="number" name="items[${itemIndex}][sale_price]" class="sale-price border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300" step="0.01" min="0" required>
            </div>
            <div>
                <label class="block font-medium mb-1">Qty</label>
                <input type="number" name="items[${itemIndex}][qty]" class="qty border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300" min="1" required>
            </div>
            <div>
                <label class="block font-medium mb-1">Diskon (opsional)</label>
                <input type="number" name="items[${itemIndex}][discount]" class="discount border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300" step="0.01" min="0">
            </div>
            <div>
                <button type="button" class="remove-item text-red-500 hover:text-red-700 mt-6">Hapus</button>
            </div>
        `;
        itemContainer.appendChild(newItem);
        itemIndex++;
        updateGrandTotal();
    });

    document.getElementById('items-container').addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-item')) {
            e.target.closest('.item-row').remove();
            updateGrandTotal();
        }
    });

    document.getElementById('items-container').addEventListener('change', function (e) {
        if (e.target.classList.contains('product-select')) {
            const row = e.target.closest('.item-row');
            const selectedOption = e.target.options[e.target.selectedIndex];
            const price = selectedOption.getAttribute('data-price') || 0;
            const sku = selectedOption.getAttribute('data-sku') || '';
            const productName = selectedOption.text || '';
            row.querySelector('.sale-price').value = parseFloat(price).toFixed(2);
            row.querySelector('.sku').value = sku;
            row.querySelector('.product-name').value = productName;
            updateGrandTotal();
        }
        if (e.target.classList.contains('qty') || e.target.classList.contains('sale-price') || e.target.classList.contains('discount')) {
            updateGrandTotal();
        }
    });

    document.getElementById('payment_method').addEventListener('change', function () {
        const splitFields = document.getElementById('split-payment-fields');
        const proofField = document.getElementById('proof-field');
        splitFields.classList.toggle('hidden', this.value !== 'split');
        proofField.classList.toggle('hidden', !(this.value === 'transfer' || this.value === 'split'));
        if (this.value !== 'split') {
            document.getElementById('cash_amount').value = '';
            document.getElementById('transfer_amount').value = '';
        }
        if (this.value !== 'transfer' && this.value !== 'split') {
            document.getElementById('proof_path').value = '';
        }
        updatePaymentAmount();
        updateProofRequired(this.value);
    });

    document.getElementById('payment_amount').addEventListener('input', updatePaymentStatus);
    document.getElementById('payment_status').addEventListener('change', updatePaymentStatus);
    document.getElementById('cash_amount').addEventListener('input', updatePaymentAmount);
    document.getElementById('transfer_amount').addEventListener('input', updatePaymentAmount);

    document.getElementById('soForm').addEventListener('submit', function (e) {
        console.log('Form submitted, validating...');
        const salePriceInputs = document.querySelectorAll('.sale-price');
        for (let input of salePriceInputs) {
            if (!input.value || parseFloat(input.value) <= 0) {
                e.preventDefault();
                alert('Harga produk tidak boleh kosong atau nol. Silakan pilih produk yang valid.');
                console.log('Validation failed: Invalid sale price');
                return;
            }
        }

        const paymentMethod = document.getElementById('payment_method').value;
        const paymentStatus = document.getElementById('payment_status').value;
        const paymentAmountInput = document.getElementById('payment_amount');
        let paymentAmount = parseFloat(paymentAmountInput ? paymentAmountInput.value : 0) || 0;

        console.log('Payment amount:', paymentAmount, 'Method:', paymentMethod);

        if (paymentAmount > 0) {
            if (paymentMethod === 'split') {
                const cashAmount = parseFloat(document.getElementById('cash_amount').value) || 0;
                const transferAmount = parseFloat(document.getElementById('transfer_amount').value) || 0;
                if (paymentAmount != cashAmount + transferAmount) {
                    e.preventDefault();
                    alert('Jumlah total harus sama dengan cash + transfer.');
                    console.log('Validation failed: Split amount mismatch');
                    return;
                }
            }

            if (paymentStatus === 'dp' && paymentAmount < grandTotal * 0.5) {
                e.preventDefault();
                alert(`Jumlah pembayaran kurang dari DP minimal 50%: Rp ${(grandTotal * 0.5).toLocaleString('id-ID')}`);
                console.log('Validation failed: Payment amount below 50% DP');
                return;
            }
            if (paymentAmount > grandTotal) {
                e.preventDefault();
                alert(`Jumlah pembayaran melebihi grand total: Rp ${grandTotal.toLocaleString('id-ID')}`);
                console.log('Validation failed: Payment amount exceeds grand total');
                return;
            }
        } else {
            console.log('No payment amount, skipping payment validation');
        }
        console.log('Validation passed, submitting form...');
    });

    updateGrandTotal();
    document.getElementById('payment_method').dispatchEvent(new Event('change'));
</script>
</body>
</html>