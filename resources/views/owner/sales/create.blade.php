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
</style>
</head>
<body class="bg-gray-100">
<div class="flex">
<x-navbar-owner />
<div class="flex-1 lg:w-5/6">
<x-navbar-top-owner />
<div class="p-4 lg:p-8">
<div class="bg-white p-6 rounded-xl shadow-lg mb-6 flex justify-between items-center">
<div>
<h1 class="text-2xl font-semibold text-gray-800">Buat Sales Order Baru</h1>
<p class="text-sm text-gray-500 mt-1">Lengkapi detail sales order berikut.</p>
</div>
<a href="{{ route('owner.sales.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow">
<i class="bi bi-arrow-left"></i> Kembali
</a>
</div>

@if ($errors->any())
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
<h4 class="font-bold">Terjadi kesalahan:</h4>
<ul class="list-disc list-inside">
@foreach ($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul>
</div>
@endif

<div class="bg-white p-6 rounded-xl shadow-lg">
<form method="POST" action="{{ route('owner.sales.store') }}" id="salesForm">
@csrf

<div class="grid md:grid-cols-2 gap-4">
<div>
<label for="order_date" class="block font-medium mb-1">Tanggal Order</label>
<input type="date" name="order_date" id="order_date" value="{{ old('order_date', now()->toDateString()) }}" required
class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300" />
@error('order_date')
<p class="text-red-500 text-sm mt-1">{{ $message }}</p>
@enderror
</div>
<div>
<label for="customer_id" class="block font-medium mb-1">Customer (opsional)</label>
<select name="customer_id" id="customer_id" class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">
<option value="">-- Pilih Customer --</option>
@foreach($customers as $customer)
<option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>
{{ $customer->name }}
</option>
@endforeach
</select>
@error('customer_id')
<p class="text-red-500 text-sm mt-1">{{ $message }}</p>
@enderror
</div>
<div>
<label for="payment_method" class="block font-medium mb-1">Metode Pembayaran</label>
<select name="payment_method" id="payment_method" required
class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">
<option value="cash" @selected(old('payment_method') == 'cash')>Cash</option>
<option value="transfer" @selected(old('payment_method') == 'transfer')>Transfer</option>
<option value="split" @selected(old('payment_method') == 'split')>Split</option>
</select>
@error('payment_method')
<p class="text-red-500 text-sm mt-1">{{ $message }}</p>
@enderror
</div>
<div>
<label for="payment_status" class="block font-medium mb-1">Status Pembayaran</label>
<select name="payment_status" id="payment_status" required
class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">
<option value="dp" @selected(old('payment_status') == 'dp')>DP</option>
<option value="lunas" @selected(old('payment_status') == 'lunas')>Lunas</option>
</select>
@error('payment_status')
<p class="text-red-500 text-sm mt-1">{{ $message }}</p>
@enderror
</div>
</div>

<div class="mt-6">
<h3 class="text-lg font-semibold mb-2">Item Order</h3>
<div class="overflow-x-auto">
<table class="w-full table-auto border border-gray-300 rounded mb-4">
<thead class="bg-gray-100 text-sm text-gray-700">
<tr>
<th class="border px-2 py-1">Produk</th>
<th class="border px-2 py-1">Harga Jual</th>
<th class="border px-2 py-1">Qty</th>
<th class="border px-2 py-1">Diskon</th>
<th class="border px-2 py-1">Aksi</th>
</tr>
</thead>
<tbody id="order-items-body">
<tr class="item-row">
<td class="border p-1">
<select name="items[0][product_id]" class="product-select border rounded px-2 py-1 w-full" required>
<option value="">-- Pilih Produk --</option>
@foreach($products as $product)
<option value="{{ $product->id }}" data-price="{{ $product->price }}"
data-name="{{ $product->name }}" data-sku="{{ $product->sku }}">
[{{ $product->sku }}] {{ $product->name }}
</option>
@endforeach
</select>
<input type="hidden" name="items[0][product_name]" class="product-name">
<input type="hidden" name="items[0][sku]" class="product-sku">
@error('items.0.product_id')
<p class="text-red-500 text-sm mt-1">{{ $message }}</p>
@enderror
</td>
<td class="border p-1">
<input type="number" name="items[0][sale_price]" class="sale-price border rounded px-2 py-1 w-full" min="0" step="100" required />
@error('items.0.sale_price')
<p class="text-red-500 text-sm mt-1">{{ $message }}</p>
@enderror
</td>
<td class="border p-1">
<input type="number" name="items[0][qty]" class="qty border rounded px-2 py-1 w-full" min="1" value="1" required />
@error('items.0.qty')
<p class="text-red-500 text-sm mt-1">{{ $message }}</p>
@enderror
</td>
<td class="border p-1">
<input type="number" name="items[0][discount]" class="discount border rounded px-2 py-1 w-full" min="0" step="100" value="0" />
@error('items.0.discount')
<p class="text-red-500 text-sm mt-1">{{ $message }}</p>
@enderror
</td>
<td class="border p-1 text-center">
<button type="button" class="remove-item text-red-600 font-bold text-lg">×</button>
</td>
</tr>
</tbody>
</table>
</div>
<button type="button" id="add-item" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded shadow mb-6">
<i class="bi bi-plus-circle"></i> Tambah Item
</button>
<div id="total-preview" class="mt-4 p-4 bg-gray-50 rounded-lg">
<p class="text-sm text-gray-600">Subtotal: <span id="subtotal">Rp 0</span></p>
<p class="text-sm text-gray-600">Total Diskon: <span id="discountTotal">Rp 0</span></p>
<p class="text-sm text-gray-600 font-bold">Grand Total: <span id="grandTotal">Rp 0</span></p>
</div>
</div>

<div class="flex justify-end mt-6">
<button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded shadow">
<i class="bi bi-check-lg"></i> Simpan
</button>
</div>
</form>
</div>
</div>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
let itemIndex = 1;

function calculateTotals() {
let subtotal = 0;
let discountTotal = 0;
document.querySelectorAll('.item-row').forEach(row => {
const salePrice = parseFloat(row.querySelector('.sale-price').value) || 0;
const qty = parseInt(row.querySelector('.qty').value) || 0;
const discount = parseFloat(row.querySelector('.discount').value) || 0;
subtotal += salePrice * qty;
discountTotal += discount * qty;
});
const grandTotal = subtotal - discountTotal;
document.getElementById('subtotal').textContent = 'Rp ' + numberFormat(subtotal);
document.getElementById('discountTotal').textContent = 'Rp ' + numberFormat(discountTotal);
document.getElementById('grandTotal').textContent = 'Rp ' + numberFormat(grandTotal);
}

function numberFormat(number) {
return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

document.getElementById('add-item').addEventListener('click', function () {
const tbody = document.getElementById('order-items-body');
const newRow = tbody.rows[0].cloneNode(true);
const index = itemIndex;

newRow.querySelectorAll('select, input').forEach(el => {
const name = el.getAttribute('name').replace(/\[\d+\]/, `[${index}]`);
el.setAttribute('name', name);
if (el.tagName === 'SELECT') el.selectedIndex = 0;
else if (el.classList.contains('qty')) el.value = '1';
else if (el.classList.contains('discount')) el.value = '0';
else if (el.classList.contains('sale-price')) el.value = '';
else if (el.type === 'hidden') el.value = '';
});

tbody.appendChild(newRow);
itemIndex++;
calculateTotals();
});

document.getElementById('order-items-body').addEventListener('click', function (e) {
if (e.target.classList.contains('remove-item')) {
const row = e.target.closest('tr');
if (tbody.rows.length > 1) row.remove();
else alert('Minimal harus ada satu item.');
calculateTotals();
}
});

document.getElementById('order-items-body').addEventListener('change', function (e) {
if (e.target.classList.contains('product-select')) {
const selectedOption = e.target.options[e.target.selectedIndex];
const row = e.target.closest('tr');
if (selectedOption.value) {
row.querySelector('.sale-price').value = selectedOption.getAttribute('data-price');
row.querySelector('.product-name').value = selectedOption.getAttribute('data-name');
row.querySelector('.product-sku').value = selectedOption.getAttribute('data-sku');
} else {
row.querySelector('.sale-price').value = '';
row.querySelector('.product-name').value = '';
row.querySelector('.product-sku').value = '';
}
calculateTotals();
}
if (e.target.classList.contains('sale-price') || e.target.classList.contains('qty') || e.target.classList.contains('discount')) {
calculateTotals();
}
});

document.getElementById('salesForm').addEventListener('submit', function (e) {
let isValid = true;
const itemRows = document.querySelectorAll('.item-row');
itemRows.forEach((row, index) => {
const productSelect = row.querySelector('.product-select');
const salePrice = row.querySelector('.sale-price');
const qty = row.querySelector('.qty');
if (!productSelect.value) {
alert(`Item ${index + 1}: Harap pilih produk`);
isValid = false;
}
if (!salePrice.value || salePrice.value <= 0) {
alert(`Item ${index + 1}: Harga jual harus lebih dari 0`);
isValid = false;
}
if (!qty.value || qty.value <= 0) {
alert(`Item ${index + 1}: Quantity harus lebih dari 0`);
isValid = false;
}
});
if (!isValid) e.preventDefault();
});

// Hitung awal
calculateTotals();
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
</script>