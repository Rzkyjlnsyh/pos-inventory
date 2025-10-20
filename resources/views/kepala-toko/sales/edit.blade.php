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
    <x-navbar-kepala-toko />
    <div class="flex-1 lg:w-5/6">
        <x-navbar-top-kepala-toko />
        <div class="p-4 lg:p-8">
            <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
                <h1 class="text-2xl font-semibold text-gray-800 mb-4">Edit Sales Order</h1>
                @if (session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                        {{ session('error') }}
                    </div>
                @endif
                <form action="{{ route('kepala-toko.sales.update', $salesOrder) }}" method="POST" id="soForm" enctype="multipart/form-data">
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
    <label for="deadline" class="block font-medium mb-1">Deadline/Target Selesai (Opsional)</label>
    <input type="date" name="deadline" id="deadline" 
           value="{{ old('deadline', $salesOrder->deadline ? \Carbon\Carbon::parse($salesOrder->deadline)->format('Y-m-d') : '') }}" 
           class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">
    @error('deadline')
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
    let grandTotal = {{ $salesOrder->grand_total }};

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