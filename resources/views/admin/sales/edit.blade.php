<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Sales Order - Pare Custom</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.10/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" />
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Raleway', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">
<div class="flex">
    <x-navbar-admin />
    <div class="flex-1 lg:w-5/6">
        <x-navbar-top-admin />
        <div class="p-4 lg:p-8">
            <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
                <h1 class="text-2xl font-semibold text-gray-800 mb-4">Edit Sales Order</h1>
                @if (session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                        {{ session('error') }}
                    </div>
                @endif
                <form action="{{ route('admin.sales.update', $salesOrder) }}" method="POST" id="soForm" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Hidden status (jika edit draft, tetap draft) -->
                    <input type="hidden" name="status" value="{{ $salesOrder->status }}">

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
                            <input type="datetime-local" name="order_date" id="order_date" 
                                   value="{{ old('order_date', $salesOrder->order_date->format('Y-m-d\TH:i')) }}"
                                   required class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">
                            @error('order_date')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="deadline" class="block font-medium mb-1">Deadline/Target Selesai (Opsional)</label>
                            <input type="date" name="deadline" id="deadline" 
                                   value="{{ old('deadline', $salesOrder->deadline ? $salesOrder->deadline->format('Y-m-d') : '') }}" 
                                   class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">
                            @error('deadline')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="relative">
                            <label for="customer_search" class="block font-medium mb-1">Customer (Opsional)</label>
                            <input type="text" 
                                   id="customer_search" 
                                   class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300" 
                                   placeholder="Ketik nama customer atau biarkan kosong..."
                                   autocomplete="off"
                                   value="{{ old('customer_name', $salesOrder->customer ? $salesOrder->customer->name : '') }}">
                            <input type="hidden" name="customer_id" id="customer_id" value="{{ old('customer_id', $salesOrder->customer_id) }}">
                            <input type="hidden" name="customer_name" id="customer_name" value="{{ old('customer_name', $salesOrder->customer ? $salesOrder->customer->name : '') }}">
                            <input type="hidden" name="customer_phone" id="customer_phone" value="{{ old('customer_phone', $salesOrder->customer ? $salesOrder->customer->phone : '') }}">
                            <div id="customer_dropdown" class="hidden absolute z-10 w-full mt-1 bg-white border rounded shadow-lg max-h-60 overflow-y-auto">
                                @foreach($customers as $customer)
                                    <div class="p-2 hover:bg-gray-100 cursor-pointer customer-option"
                                         data-id="{{ $customer->id }}"
                                         data-name="{{ $customer->name }}"
                                         data-phone="{{ $customer->phone ?? '' }}">
                                        {{ $customer->name }} @if($customer->phone)({{ $customer->phone }})@endif
                                    </div>
                                @endforeach
                            </div>
                            <div id="selected_customer" class="mt-2 p-2 bg-blue-50 rounded {{ $salesOrder->customer ? '' : 'hidden' }}">
                                <span id="customer_display_name" class="font-medium">{{ $salesOrder->customer ? $salesOrder->customer->name : '' }}</span>
                                <span id="customer_display_phone" class="text-sm text-gray-600 ml-2">{{ $salesOrder->customer && $salesOrder->customer->phone ? '(' . $salesOrder->customer->phone . ')' : '' }}</span>
                                <button type="button" id="clear_customer" class="text-red-600 ml-2">✕</button>
                            </div>
                            <div id="new_customer_fields" class="hidden mt-2">
                                <div class="grid md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block font-medium mb-1">Nama Customer Baru *</label>
                                        <input type="text" id="new_customer_name" class="border rounded px-3 py-2 w-full" placeholder="Nama customer baru">
                                    </div>
                                    <div>
                                        <label class="block font-medium mb-1">Nomor Telepon</label>
                                        <input type="text" id="new_customer_phone" class="border rounded px-3 py-2 w-full" placeholder="Contoh: 08123456789">
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">* Customer baru akan otomatis dibuat</p>
                            </div>
                        </div>
                        <div>
                            <label for="payment_method" class="block font-medium mb-1">Metode Pembayaran</label>
                            <select name="payment_method" id="payment_method" {{ $salesOrder->status === 'draft' ? '' : 'required' }} class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">
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
                            <select name="payment_status" id="payment_status" {{ $salesOrder->status === 'draft' ? '' : 'required' }} class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">
                                <option value="dp" {{ old('payment_status', $salesOrder->payment_status) == 'dp' ? 'selected' : '' }}>DP</option>
                                <option value="lunas" {{ old('payment_status', $salesOrder->payment_status) == 'lunas' ? 'selected' : '' }}>Lunas</option>
                            </select>
                            @error('payment_status')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Checkbox + Supplier -->
                    <div class="mt-4">
                        <label class="flex items-center">
                            <input type="hidden" name="add_to_purchase" value="0">
                            <input type="checkbox" name="add_to_purchase" id="add_to_purchase" value="1"
                            class="mr-2" {{ $salesOrder->add_to_purchase ? 'checked' : '' }}>
                            <span>Masukkan ke Pembelian (Pre-order)</span>
                        </label>
                    </div>
                    <div id="supplier-section" class="hidden mt-4">
                        <label for="supplier_search" class="block font-medium mb-1">Supplier untuk Pembelian *</label>
                        <input type="text" id="supplier_search"
                               class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300"
                               placeholder="Ketik nama supplier..."
                               autocomplete="off"
                               value="{{ old('supplier_name', $salesOrder->add_to_purchase ? 'Pre-order Customer' : '') }}">
                        <input type="hidden" name="supplier_id" id="supplier_id" value="">
                        <input type="hidden" name="supplier_name" id="supplier_name" value="{{ old('supplier_name', $salesOrder->add_to_purchase ? 'Pre-order Customer' : '') }}">
                        <div id="selected_supplier" class="mt-2 p-2 bg-blue-50 rounded hidden">
                            <span id="supplier_display_name" class="font-medium">Pre-order Customer</span>
                            <button type="button" id="clear_supplier" class="text-red-600 ml-2">✕</button>
                        </div>
                    </div>

                    <!-- Pembayaran -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold mb-4 text-gray-800">Pembayaran (Opsional)</h2>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label for="payment_amount" class="block font-medium mb-1">Jumlah Pembayaran (Total)</label>
                                <input type="number" name="payment_amount" id="payment_amount" class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300" step="0.01" min="0" value="{{ old('payment_amount', $salesOrder->paid_total) }}">
                                <p id="dp-info" class="text-sm text-gray-600 mt-1"></p>
                                @error('payment_amount')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div id="split-payment-fields" class="hidden md:col-span-2">
                                <div class="grid md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="cash_amount" class="block font-medium mb-1">Jumlah Cash</label>
                                        <input type="number" name="cash_amount" id="cash_amount" class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300" step="0.01" min="0" value="{{ old('cash_amount', $salesOrder->payments->first()->cash_amount ?? 0) }}">
                                        @error('cash_amount')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="transfer_amount" class="block font-medium mb-1">Jumlah Transfer</label>
                                        <input type="number" name="transfer_amount" id="transfer_amount" class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300" step="0.01" min="0" value="{{ old('transfer_amount', $salesOrder->payments->first()->transfer_amount ?? 0) }}">
                                        @error('transfer_amount')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div id="proof-and-ref" class="mt-4 hidden md:col-span-2">
                                <div class="grid md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="proof_path" class="block font-medium mb-1">Bukti Transfer (jpg, png, pdf, opsional)</label>
                                        <input type="file" name="proof_path" id="proof_path" accept=".jpg,.jpeg,.png,.pdf" class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">
                                        <p class="text-sm text-gray-600 mt-1">Upload bukti transfer (opsional)</p>
                                        @error('proof_path')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="reference_number" class="block font-medium mb-1">No Referensi Transfer (Opsional)</label>
                                        <input type="text" name="reference_number" id="reference_number" 
                                               class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300" 
                                               placeholder="Contoh: TRF123456789" 
                                               value="{{ old('reference_number', $salesOrder->payments->first()->reference_number ?? '') }}">
                                        <p class="text-sm text-gray-600 mt-1">No referensi bank atau keterangan</p>
                                        @error('reference_number')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600 mt-2">⚠️ Untuk transfer/split, wajib mengisi salah satu: Bukti Transfer atau No Referensi</p>
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
                            @foreach($salesOrder->items as $index => $item)
                            <div class="item-row grid md:grid-cols-3 gap-4">
    <div class="relative md:col-span-2">
        <label class="block font-medium mb-1">Produk</label>
        <input type="text"
            class="product-search border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300"
            placeholder="Ketik nama produk..." autocomplete="off">
        <input type="hidden" name="items[0][product_id]" class="product-id">
        <input type="hidden" name="items[0][product_name]" class="product-name">
        <input type="hidden" name="items[0][sku]" class="sku">
        <div class="product-results hidden absolute z-20 w-full mt-1 bg-white border rounded shadow-lg max-h-60 overflow-y-auto"></div>
    </div>
    <div>
        <label class="block font-medium mb-1">Harga</label>
        <input type="number" name="items[0][sale_price]"
            class="sale-price border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300"
            step="0.01" required>
        @error('items.0.sale_price')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="block font-medium mb-1">Qty</label>
        <input type="number" name="items[0][qty]"
            class="qty border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300"
            min="1" required>
        @error('items.0.qty')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>
                            @endforeach
                        </div>
                        <button type="button" id="add-item" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow mt-4">
                            <i class="bi bi-plus-circle"></i> Tambah Item
                        </button>
                    </div>

                    <!-- TAMBAH BAGIAN INI: Summary dengan Discount Total -->
<div class="bg-gray-50 p-4 rounded-lg mb-6">
    <h2 class="text-lg font-semibold mb-4 text-gray-800">Ringkasan Order</h2>
    <div class="grid md:grid-cols-4 gap-4">
        <div>
            <label class="block font-medium mb-1">Subtotal</label>
            <div id="display-subtotal" class="text-lg font-semibold text-gray-800">Rp {{ number_format($salesOrder->subtotal, 0, ',', '.') }}</div>
        </div>
        <div>
            <label for="discount_total" class="block font-medium mb-1">Diskon Total</label>
            <input type="number" name="discount_total" id="discount_total" 
                   class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300"
                   min="0" step="0.01" value="{{ old('discount_total', $salesOrder->discount_total) }}"
                   placeholder="0">
            @error('discount_total')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block font-medium mb-1">Grand Total</label>
            <div id="display-grand-total" class="text-lg font-bold text-blue-600">Rp {{ number_format($salesOrder->grand_total, 0, ',', '.') }}</div>
            <input type="hidden" name="grand_total" id="grand_total" value="{{ $salesOrder->grand_total }}">
        </div>
    </div>
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
// === JAVASCRIPT LENGKAP: CUSTOMER, SUPPLIER, PRODUCT SEARCH, DLL ===
document.addEventListener('DOMContentLoaded', function () {
    let itemIndex = {{ count($salesOrder->items) }};
    const itemsContainer = document.getElementById('items-container');
    const soForm = document.getElementById('soForm');

    // Hidden grand total
    if (!document.getElementById('grand_total')) {
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'grand_total';
        hidden.id = 'grand_total';
        hidden.value = '0';
        soForm.appendChild(hidden);
    }

    let grandTotal = 0;

    // === PRODUCT SEARCH ===
    function initializeProductSearchForEdit(row, index) {
        const productSearch = row.querySelector('.product-search');
        const productResults = row.querySelector('.product-results');
        const productIdInput = row.querySelector('.product-id');
        const productNameInput = row.querySelector('.product-name');
        const skuInput = row.querySelector('.sku');
        const priceInput = row.querySelector('.sale-price');

        if (!productSearch) return;

        productSearch.addEventListener('input', function() {
            const searchTerm = this.value.trim();
            if (searchTerm.length < 2) {
                productResults.classList.add('hidden');
                return;
            }
            fetch(`/admin/products/search?q=${encodeURIComponent(searchTerm)}`)
                .then(response => response.json())
                .then(products => {
                    productResults.innerHTML = '';
                    if (products.length === 0) {
                        productResults.innerHTML = '<div class="p-2 text-gray-500">Produk tidak ditemukan</div>';
                        productResults.classList.remove('hidden');
                        return;
                    }
                    products.forEach(product => {
                        const div = document.createElement('div');
                        div.className = 'p-2 hover:bg-gray-100 cursor-pointer product-option border-b';
                        div.innerHTML = `
                            <div class="font-medium">${product.name}</div>
                            <div class="text-sm text-gray-600">
                                SKU: ${product.sku} | Stok: ${product.stock_qty} | Harga: Rp ${parseFloat(product.price).toLocaleString('id-ID')}
                            </div>
                        `;
                        div.setAttribute('data-id', product.id);
                        div.setAttribute('data-name', product.name);
                        div.setAttribute('data-sku', product.sku);
                        div.setAttribute('data-price', product.price);
                        div.addEventListener('click', function() {
                            productIdInput.value = product.id;
                            productNameInput.value = product.name;
                            productSearch.value = product.name;
                            if (skuInput) skuInput.value = product.sku;
                            if (priceInput) priceInput.value = parseFloat(product.price).toFixed(2);
                            productResults.classList.add('hidden');
                            updateGrandTotal();
                        });
                        productResults.appendChild(div);
                    });
                    productResults.classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error searching products:', error);
                    productResults.innerHTML = '<div class="p-2 text-red-500">Error loading products</div>';
                    productResults.classList.remove('hidden');
                });
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.relative')) {
                productResults.classList.add('hidden');
            }
        });

        productSearch.addEventListener('focus', function() {
            if (this.value.trim().length >= 2) {
                this.dispatchEvent(new Event('input'));
            }
        });
    }

    // Inisialisasi untuk semua row yang sudah ada
    document.querySelectorAll('.item-row').forEach((row, index) => {
        initializeProductSearchForEdit(row, index);
    });

    // === FUNGSI UMUM ===
    function updateGrandTotal() {
        const rows = document.querySelectorAll('.item-row');
        let subtotal = 0;
        rows.forEach(row => {
            const price = parseFloat(row.querySelector('.sale-price').value) || 0;
            const qty = parseInt(row.querySelector('.qty').value) || 0;
            const discount = parseFloat(row.querySelector('.discount').value) || 0;
            subtotal += (price * qty) - (discount * qty);
        });
        grandTotal = subtotal;
        document.getElementById('grand_total').value = grandTotal.toFixed(2);
        updatePaymentStatus();
        updatePaymentAmount();
    }

    function updatePaymentStatus() {
        const paymentAmount = parseFloat(document.getElementById('payment_amount').value) || 0;
        const paymentStatus = document.getElementById('payment_status');
        const dpInfo = document.getElementById('dp-info');

        if (grandTotal > 0 && paymentAmount >= grandTotal) {
            paymentStatus.value = 'lunas';
        } else if (paymentAmount > 0) {
            paymentStatus.value = 'dp';
        }

        if (paymentStatus.value === 'dp' && paymentAmount > 0) {
            const minDp = Math.ceil(grandTotal * 0.5);
            dpInfo.textContent = `Minimal DP 50%: Rp ${minDp.toLocaleString('id-ID')}`;
        } else {
            dpInfo.textContent = '';
        }
    }

    function updatePaymentAmount() {
        const method = document.getElementById('payment_method').value;
        const cash = parseFloat(document.getElementById('cash_amount')?.value) || 0;
        const transfer = parseFloat(document.getElementById('transfer_amount')?.value) || 0;
        const paymentAmount = document.getElementById('payment_amount');

        if (method === 'split') {
            paymentAmount.value = (cash + transfer).toFixed(2);
            document.getElementById('split-payment-fields').classList.remove('hidden');
        } else {
            if (method === 'cash' || method === 'transfer') {
                paymentAmount.value = grandTotal.toFixed(2);
            }
            document.getElementById('split-payment-fields').classList.add('hidden');
        }
        updateProofRequired(method);
        updatePaymentStatus();
    }

    function updateProofRequired(method) {
        const proofField = document.getElementById('proof-and-ref');
        if (method === 'transfer' || method === 'split') {
            proofField.classList.remove('hidden');
        } else {
            proofField.classList.add('hidden');
        }
    }

    // === TAMBAH ITEM ===
    document.getElementById('add-item').addEventListener('click', function () {
        const newRow = document.createElement('div');
        newRow.className = 'item-row grid md:grid-cols-5 gap-4 mt-4';
        newRow.innerHTML = `
    <div class="relative md:col-span-2">
        <label class="block font-medium mb-1">Produk</label>
        <input type="text" 
               class="product-search border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300" 
               placeholder="Ketik nama produk..."
               autocomplete="off">
        <input type="hidden" name="items[${itemIndex}][product_id]" class="product-id">
        <input type="hidden" name="items[${itemIndex}][product_name]" class="product-name">
        <input type="hidden" name="items[${itemIndex}][sku]" class="sku"> <!-- SKU jadi hidden -->
        <div class="product-results hidden absolute z-20 w-full mt-1 bg-white border rounded shadow-lg max-h-60 overflow-y-auto"></div>
    </div>
    <div><label class="block font-medium mb-1">Harga</label>
        <input type="number" name="items[${itemIndex}][sale_price]" class="sale-price border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300" step="0.01" required></div>
    <div><label class="block font-medium mb-1">Qty</label>
        <input type="number" name="items[${itemIndex}][qty]" class="qty border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300" min="1" value="1" required></div>
    <div><label class="block font-medium mb-1">Diskon</label>
        <input type="number" name="items[${itemIndex}][discount]" class="discount border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300" min="0" step="0.01" value="0">
        <button type="button" class="remove-item text-red-600 hover:text-red-800 mt-2"><i class="bi bi-trash"></i></button>
    </div>
`;
        itemsContainer.appendChild(newRow);
        setTimeout(() => {
            initializeProductSearchForEdit(newRow, itemIndex);
        }, 100);
        itemIndex++;
        updateGrandTotal();
    });

    // Hapus item
    itemsContainer.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-item');
        if (btn) {
            const rows = document.querySelectorAll('.item-row');
            if (rows.length > 1) {
                btn.closest('.item-row').remove();
                updateGrandTotal();
            } else {
                alert('Minimal satu item harus ada.');
            }
        }
    });

    // Event input harga/qty/diskon
    itemsContainer.addEventListener('input', function (e) {
        const target = e.target;
        if (target.classList.contains('qty') || target.classList.contains('sale-price') || target.classList.contains('discount')) {
            updateGrandTotal();
        }
    });

    // === CUSTOMER SEARCH & NEW CUSTOMER ===
    const customerSearch = document.getElementById('customer_search');
    const customerDropdown = document.getElementById('customer_dropdown');
    const customerIdInput = document.getElementById('customer_id');
    const customerNameInput = document.getElementById('customer_name');
    const customerPhoneInput = document.getElementById('customer_phone');
    const selectedCustomerDiv = document.getElementById('selected_customer');
    const newCustomerFields = document.getElementById('new_customer_fields');
    const newCustomerName = document.getElementById('new_customer_name');
    const newCustomerPhone = document.getElementById('new_customer_phone');

    customerSearch.addEventListener('click', () => customerDropdown.classList.remove('hidden'));
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.relative')) {
            customerDropdown.classList.add('hidden');
        }
    });

    customerDropdown.addEventListener('click', function(e) {
        const option = e.target.closest('.customer-option');
        if (option) {
            customerIdInput.value = option.dataset.id;
            customerNameInput.value = option.dataset.name;
            customerPhoneInput.value = option.dataset.phone || '';
            document.getElementById('customer_display_name').textContent = option.dataset.name;
            document.getElementById('customer_display_phone').textContent = option.dataset.phone ? `(${option.dataset.phone})` : '';
            selectedCustomerDiv.classList.remove('hidden');
            newCustomerFields.classList.add('hidden');
            customerDropdown.classList.add('hidden');
            customerSearch.value = '';
        }
    });

    document.getElementById('clear_customer').addEventListener('click', function() {
        customerIdInput.value = '';
        customerNameInput.value = '';
        customerPhoneInput.value = '';
        selectedCustomerDiv.classList.add('hidden');
        newCustomerFields.classList.add('hidden');
        customerSearch.value = '';
        newCustomerName.value = '';
        newCustomerPhone.value = '';
    });

    customerSearch.addEventListener('input', function() {
        const term = this.value.trim();
        if (term.length > 0) {
            const exists = Array.from(customerDropdown.querySelectorAll('.customer-option'))
                .some(opt => opt.dataset.name.toLowerCase() === term.toLowerCase());
            if (!exists) {
                newCustomerName.value = term;
                customerNameInput.value = term;
                newCustomerFields.classList.remove('hidden');
            } else {
                newCustomerFields.classList.add('hidden');
            }
        } else {
            newCustomerFields.classList.add('hidden');
        }
    });

    newCustomerName.addEventListener('input', () => customerNameInput.value = newCustomerName.value);
    newCustomerPhone.addEventListener('input', () => customerPhoneInput.value = newCustomerPhone.value);

    // === SUPPLIER SEARCH ===
    const addToPurchase = document.getElementById('add_to_purchase');
    const supplierSection = document.getElementById('supplier-section');
    const supplierSearch = document.getElementById('supplier_search');
    const supplierIdInput = document.getElementById('supplier_id');
    const supplierNameInput = document.getElementById('supplier_name');
    const selectedSupplierDiv = document.getElementById('selected_supplier');

    addToPurchase.addEventListener('change', function() {
        supplierSection.classList.toggle('hidden', !this.checked);
        if (this.checked && !supplierIdInput.value) {
            supplierNameInput.value = 'Pre-order Customer';
            document.getElementById('supplier_display_name').textContent = 'Pre-order Customer';
            selectedSupplierDiv.classList.remove('hidden');
        }
    });

    supplierSearch.addEventListener('input', function() {
        const term = this.value.trim();
        if (term) {
            supplierNameInput.value = term;
            supplierIdInput.value = '';
        }
    });

    document.getElementById('clear_supplier').addEventListener('click', function() {
        supplierIdInput.value = '';
        supplierNameInput.value = 'Pre-order Customer';
        document.getElementById('supplier_display_name').textContent = 'Pre-order Customer';
        supplierSearch.value = '';
        selectedSupplierDiv.classList.add('hidden');
    });

    // === PAYMENT METHOD & SPLIT ===
    const paymentMethod = document.getElementById('payment_method');
    const cashAmount = document.getElementById('cash_amount');
    const transferAmount = document.getElementById('transfer_amount');
    const paymentAmount = document.getElementById('payment_amount');
    const paymentStatus = document.getElementById('payment_status');

    paymentMethod.addEventListener('change', updatePaymentAmount);
    if (cashAmount) cashAmount.addEventListener('input', updatePaymentAmount);
    if (transferAmount) transferAmount.addEventListener('input', updatePaymentAmount);
    paymentAmount.addEventListener('input', updatePaymentStatus);
    paymentStatus.addEventListener('change', updatePaymentStatus);

    // === SUBMIT VALIDATION ===
    soForm.addEventListener('submit', function(e) {
        const prices = document.querySelectorAll('.sale-price');
        for (let p of prices) {
            if (!p.value || parseFloat(p.value) <= 0) {
                e.preventDefault();
                alert('Harga produk tidak boleh kosong atau nol.');
                return;
            }
        }

        const method = paymentMethod.value;
        const amount = parseFloat(paymentAmount.value) || 0;

        if ((method === 'transfer' || method === 'split') && amount > 0) {
            const proof = document.getElementById('proof_path');
            const reference = document.getElementById('reference_number');
            const hasProof = proof?.files?.[0];
            const hasReference = reference?.value.trim();

            if (!hasProof && !hasReference) {
                e.preventDefault();
                alert('Untuk transfer/split, wajib upload bukti atau isi no referensi.');
                return;
            }
        }

        if (amount > 0 && paymentStatus.value === 'dp' && amount < (grandTotal * 0.5)) {
            e.preventDefault();
            alert(`DP minimal 50%: Rp ${(grandTotal * 0.5).toLocaleString('id-ID')}`);
            return;
        }

        if (amount > grandTotal) {
            e.preventDefault();
            alert(`Jumlah pembayaran melebihi total: Rp ${grandTotal.toLocaleString('id-ID')}`);
            return;
        }
    });

    // === INISIALISASI AWAL ===
    updateGrandTotal();
    if (paymentMethod) paymentMethod.dispatchEvent(new Event('change'));
// Initialize supplier section based on add_to_purchase value
if ({{ $salesOrder->add_to_purchase ? 'true' : 'false' }}) {
    supplierSection.classList.remove('hidden');
    document.getElementById('supplier_display_name').textContent = 'Pre-order Customer';
    selectedSupplierDiv.classList.remove('hidden');
}
});
</script>
</body>
</html>