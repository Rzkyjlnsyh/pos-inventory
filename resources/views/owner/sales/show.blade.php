<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Detail Sales Order - Pare Custom</title>
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
            @php
                $activeShift = \App\Models\Shift::where('user_id', \Illuminate\Support\Facades\Auth::id())->whereNull('end_time')->first();
            @endphp

            <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-800">Detail Sales Order</h1>
                        <p class="text-sm text-gray-500 mt-1">SO Number: {{ $salesOrder->so_number }}</p>
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('owner.sales.index') }}"
                           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                        @if($salesOrder->isEditable() && $activeShift)
                            <a href="{{ route('owner.sales.edit', $salesOrder) }}"
                               class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded shadow">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                        @endif
                        @if($salesOrder->status === 'pending' && $salesOrder->approved_by === null && $activeShift)
                            <form action="{{ route('owner.sales.approve', $salesOrder) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
                                    <i class="bi bi-check-circle"></i> Approve
                                </button>
                            </form>
                        @endif
                        @if($salesOrder->status === 'pending' && $salesOrder->approved_by !== null && $salesOrder->paid_total >= $salesOrder->grand_total * 0.5 && $activeShift)
                            <form action="{{ route('owner.sales.startProcess', $salesOrder) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow">
                                    <i class="bi bi-play-circle"></i> Mulai Proses
                                </button>
                            </form>
                        @endif
                        @if($salesOrder->order_type === 'jahit_sendiri' && $salesOrder->status === 'request_kain' && $activeShift)
                            <form action="{{ route('owner.sales.processJahit', $salesOrder) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow">
                                    <i class="bi bi-scissors"></i> Proses Jahit
                                </button>
                            </form>
                        @endif
                        @if($salesOrder->order_type === 'jahit_sendiri' && $salesOrder->status === 'proses_jahit' && $activeShift)
                            <form action="{{ route('owner.sales.markAsJadi', $salesOrder) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow">
                                    <i class="bi bi-check-circle"></i> Tandai Jadi
                                </button>
                            </form>
                        @endif
                        @if(($salesOrder->order_type === 'jahit_sendiri' && $salesOrder->status === 'jadi') || ($salesOrder->order_type === 'beli_jadi' && $salesOrder->status === 'di proses') && $activeShift)
                            <form action="{{ route('owner.sales.markAsDiterimaToko', $salesOrder) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow">
                                    <i class="bi bi-shop"></i> Diterima Toko
                                </button>
                            </form>
                        @endif
                        @if($salesOrder->status === 'diterima_toko' && $activeShift)
                            <form action="{{ route('owner.sales.complete', $salesOrder) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow">
                                    <i class="bi bi-check2-all"></i> Selesaikan
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            @if(!$activeShift)
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-6">
                    Shift belum dimulai. Anda tidak bisa menambah pembayaran atau melakukan aksi lain. Silakan mulai shift terlebih dahulu di <a href="{{ route('owner.shift.dashboard') }}" class="underline">Dashboard Shift</a>.
                </div>
            @endif

            @if($salesOrder->status === 'pending')
                @php
                    $insufficientStock = false;
                    $stockMessages = [];
                @endphp
                @foreach($salesOrder->items as $item)
                    @if($item->product_id)
                        @php
                            $product = \App\Models\Product::find($item->product_id);
                            if ($product && $product->stock_qty < $item->qty) {
                                $insufficientStock = true;
                                $stockMessages[] = 'Stok ' . $product->name . ' tidak cukup. Tersedia: ' . $product->stock_qty . ', Dibutuhkan: ' . $item->qty;
                            }
                        @endphp
                    @endif
                @endforeach
                @if($insufficientStock)
                    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-6">
                        <h4 class="font-bold">Peringatan Stok!</h4>
                        <ul class="list-disc list-inside">
                            @foreach($stockMessages as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                        <p class="mt-2">Stok akan tetap diproses meski negatif saat mulai proses.</p>
                    </div>
                @endif
            @endif

            @if(in_array($salesOrder->status, ['pending', 'request_kain', 'proses_jahit', 'jadi', 'di proses', 'diterima_toko']))
                <div class="bg-gray-100 p-4 rounded-xl mb-6">
                    <h4 class="font-semibold text-gray-700">Debug Status Proses</h4>
                    <p class="text-sm text-gray-600">
                        Approved: {{ $salesOrder->approved_by ? 'Ya (User ID: ' . $salesOrder->approved_by . ')' : 'Belum' }}<br>
                        Total Dibayar: Rp {{ number_format($salesOrder->paid_total, 0, ',', '.') }}<br>
                        Minimal 50% Grand Total: Rp {{ number_format($salesOrder->grand_total * 0.5, 0, ',', '.') }}<br>
                        @if(in_array($salesOrder->payment_method, ['transfer', 'split']))
                            Bukti Pembayaran: {{ $salesOrder->payments()->whereNull('proof_path')->count() == 0 ? 'Semua pembayaran memiliki bukti' : 'Ada pembayaran tanpa bukti' }}<br>
                        @endif
                        @if($salesOrder->status === 'pending' && $salesOrder->approved_by && $salesOrder->paid_total >= $salesOrder->grand_total * 0.5 && ($salesOrder->payment_method === 'cash' || $salesOrder->payments()->whereNull('proof_path')->count() == 0))
                            <span class="text-green-600">Tombol Mulai Proses harusnya muncul.</span>
                        @elseif($salesOrder->status === 'pending')
                            <span class="text-red-600">Tombol Mulai Proses tidak muncul karena: 
                                {{ !$salesOrder->approved_by ? 'Belum di-approve. ' : '' }}
                                {{ $salesOrder->paid_total < $salesOrder->grand_total * 0.5 ? 'Pembayaran kurang dari 50%. ' : '' }}
                                @if(in_array($salesOrder->payment_method, ['transfer', 'split']) && $salesOrder->payments()->whereNull('proof_path')->count() > 0)
                                    Ada pembayaran tanpa bukti.
                                @endif
                            </span>
                        @elseif($salesOrder->order_type === 'jahit_sendiri' && $salesOrder->status === 'request_kain')
                            <span class="text-green-600">Tombol Proses Jahit harusnya muncul.</span>
                        @elseif($salesOrder->order_type === 'jahit_sendiri' && $salesOrder->status === 'proses_jahit')
                            <span class="text-green-600">Tombol Tandai Jadi harusnya muncul.</span>
                        @elseif(($salesOrder->order_type === 'jahit_sendiri' && $salesOrder->status === 'jadi') || ($salesOrder->order_type === 'beli_jadi' && $salesOrder->status === 'di proses'))
                            <span class="text-green-600">Tombol Diterima Toko harusnya muncul.</span>
                        @elseif($salesOrder->status === 'diterima_toko' && $salesOrder->remaining_amount == 0)
                            <span class="text-green-600">Tombol Selesaikan harusnya muncul.</span>
                        @elseif($salesOrder->status === 'diterima_toko')
                            <span class="text-red-600">Tombol Selesaikan tidak muncul karena pembayaran belum lunas.</span>
                        @endif
                    </p>
                </div>
            @endif

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

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <h2 class="text-lg font-semibold mb-4 text-gray-800">Informasi Order</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between"><span class="text-gray-600">SO Number:</span><span class="font-mono font-semibold">{{ $salesOrder->so_number }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Tipe Order:</span><span class="capitalize">{{ str_replace('_', ' ', $salesOrder->order_type) }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Tanggal Order:</span><span>{{ \Carbon\Carbon::parse($salesOrder->order_date)->format('d/m/Y') }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Customer:</span><span>{{ $salesOrder->customer->name ?? 'Guest' }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Dibuat Oleh:</span><span>{{ $salesOrder->creator->name }}</span></div>
                        @if($salesOrder->approved_by)
                            <div class="flex justify-between"><span class="text-gray-600">Disetujui Oleh:</span><span>{{ $salesOrder->approver->name }}</span></div>
                        @endif
                        @if($salesOrder->approved_at)
                            <div class="flex justify-between"><span class="text-gray-600">Tanggal Approve:</span><span>{{ \Carbon\Carbon::parse($salesOrder->approved_at)->format('d/m/Y H:i') }}</span></div>
                        @endif
                        @if($salesOrder->completed_at)
                            <div class="flex justify-between"><span class="text-gray-600">Tanggal Selesai:</span><span>{{ \Carbon\Carbon::parse($salesOrder->completed_at)->format('d/m/Y H:i') }}</span></div>
                        @endif
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <h2 class="text-lg font-semibold mb-4 text-gray-800">Informasi Pembayaran & Status</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between"><span class="text-gray-600">Status Order:</span><span class="px-2 py-1 rounded-full text-xs font-medium @if($salesOrder->status === 'selesai') bg-green-100 text-green-600 @elseif(in_array($salesOrder->status, ['request_kain', 'proses_jahit', 'jadi', 'di proses', 'diterima_toko'])) bg-yellow-100 text-yellow-600 @else bg-blue-100 text-blue-600 @endif">{{ ucfirst(str_replace('_', ' ', $salesOrder->status)) }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Metode Pembayaran:</span><span class="capitalize">{{ $salesOrder->payment_method }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Status Pembayaran:</span><span class="px-2 py-1 rounded-full text-xs font-medium @if($salesOrder->payment_status === 'lunas') bg-green-100 text-green-600 @else bg-yellow-100 text-yellow-600 @endif">{{ ucfirst($salesOrder->payment_status) }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Subtotal:</span><span>Rp {{ number_format($salesOrder->subtotal, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Diskon:</span><span>Rp {{ number_format($salesOrder->discount_total, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Grand Total:</span><span class="text-lg font-bold text-blue-600">Rp {{ number_format($salesOrder->grand_total, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Total Dibayar:</span><span class="text-green-600 font-medium">Rp {{ number_format($salesOrder->paid_total, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Sisa:</span><span class="@if($salesOrder->remaining_amount > 0) text-red-600 @else text-green-600 @endif font-medium">Rp {{ number_format($salesOrder->remaining_amount, 0, ',', '.') }}</span></div>
                    </div>
                </div>
            </div>

            @if($salesOrder->status !== 'selesai' && $activeShift)
                <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
                    <h2 class="text-lg font-semibold mb-4 text-gray-800">Tambah Pembayaran</h2>
                    <form action="{{ route('owner.sales.addPayment', $salesOrder) }}" method="POST" enctype="multipart/form-data" id="paymentForm">
                        @csrf
                        <div class="grid md:grid-cols-2 gap-4 mb-4">
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
                                <label for="payment_amount" class="block font-medium mb-1">Jumlah Pembayaran</label>
                                <input type="number" name="payment_amount" id="payment_amount" min="0" step="0.01"
                                       required class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300"
                                       value="{{ old('payment_amount', $salesOrder->remaining_amount) }}">
                                @error('payment_amount')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div id="split-payment-fields" class="hidden col-span-2">
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
                            <div>
                                <label for="paid_at" class="block font-medium mb-1">Tanggal Pembayaran</label>
                                <input type="datetime-local" name="paid_at" id="paid_at"
                                       value="{{ old('paid_at', now()->format('Y-m-d\TH:i')) }}" required
                                       class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">
                                @error('paid_at')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="reference" class="block font-medium mb-1">No. Referensi (opsional)</label>
                                <input type="text" name="reference" id="reference" value="{{ old('reference') }}"
                                       class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">
                                @error('reference')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="proof_path" class="block font-medium mb-1">Bukti Pembayaran (opsional)</label>
                                <input type="file" name="proof_path" id="proof_path" accept=".jpg,.jpeg,.png,.pdf"
                                       class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">
                                @error('proof_path')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="note" class="block font-medium mb-1">Catatan (opsional)</label>
                            <textarea name="note" id="note" rows="2"
                                      class="border rounded px-3 py-2 w-full focus:ring focus:ring-blue-300">{{ old('note') }}</textarea>
                            @error('note')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded shadow">
                            <i class="bi bi-check-lg"></i> Simpan Pembayaran
                        </button>
                    </form>
                </div>
            @endif

            <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
                <h2 class="text-lg font-semibold mb-4 text-gray-800">Daftar Pembayaran</h2>
                <div class="overflow-x-auto">
                    <table class="w-full table-auto border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-left text-sm font-semibold text-gray-600">
                                <th class="px-4 py-2 border">Jumlah</th>
                                <th class="px-4 py-2 border">Detail Pembayaran</th>
                                <th class="px-4 py-2 border">Tanggal</th>
                                <th class="px-4 py-2 border">Referensi</th>
                                <th class="px-4 py-2 border">Bukti</th>
                                <th class="px-4 py-2 border">Catatan</th>
                                <th class="px-4 py-2 border text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($salesOrder->payments as $payment)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-2 border">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                    <td class="px-4 py-2 border">
                                        @if($payment->method === 'split')
                                            Cash: Rp {{ number_format($payment->cash_amount, 0, ',', '.') }} <br>
                                            Transfer: Rp {{ number_format($payment->transfer_amount, 0, ',', '.') }}
                                        @else
                                            {{ ucfirst($payment->method) }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 border">{{ \Carbon\Carbon::parse($payment->paid_at)->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-2 border">{{ $payment->reference ?? '-' }}</td>
                                    <td class="px-4 py-2 border">
                                        @if($payment->proof_path)
                                            <a href="{{ Storage::url($payment->proof_path) }}" target="_blank">Lihat Bukti</a>
                                        @else -
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 border">{{ $payment->note ?? '-' }}</td>
                                    <td class="px-4 py-2 border text-center">
                                        <div class="flex justify-center gap-2">
                                            <a href="{{ route('owner.sales.printNotaDirect', $payment) }}" class="text-green-600 hover:underline">
                                                <i class="bi bi-printer"></i> Print Langsung
                                            </a>
                                            <a href="{{ route('owner.sales.printNota', $payment) }}" class="text-blue-600 hover:underline">
                                                <i class="bi bi-download"></i> Download PDF
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-gray-500 px-4 py-4">Belum ada pembayaran</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h2 class="text-lg font-semibold mb-4 text-gray-800">Item Order</h2>
                <div class="overflow-x-auto">
                    <table class="w-full table-auto border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-left text-sm font-semibold text-gray-600">
                                <th class="px-4 py-2 border">Produk</th>
                                <th class="px-4 py-2 border">SKU</th>
                                <th class="px-4 py-2 border text-right">Harga</th>
                                <th class="px-4 py-2 border text-center">Qty</th>
                                <th class="px-4 py-2 border text-right">Diskon</th>
                                <th class="px-4 py-2 border text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salesOrder->items as $item)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-2 border">{{ $item->product_name }}
                                        @if($item->product_id)<br><small class="text-gray-500">ID: {{ $item->product_id }}</small>@endif</td>
                                    <td class="px-4 py-2 border">{{ $item->sku ?? '-' }}</td>
                                    <td class="px-4 py-2 border text-right">Rp {{ number_format($item->sale_price, 0, ',', '.') }}</td>
                                    <td class="px-4 py-2 border text-center">{{ $item->qty }}</td>
                                    <td class="px-4 py-2 border text-right">Rp {{ number_format($item->discount, 0, ',', '.') }}</td>
                                    <td class="px-4 py-2 border text-right font-semibold">Rp {{ number_format($item->line_total, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="5" class="px-4 py-2 border text-right font-semibold">Subtotal:</td>
                                <td class="px-4 py-2 border text-right font-semibold">Rp {{ number_format($salesOrder->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td colspan="5" class="px-4 py-2 border text-right font-semibold">Total Diskon:</td>
                                <td class="px-4 py-2 border text-right font-semibold text-red-600">- Rp {{ number_format($salesOrder->discount_total, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td colspan="5" class="px-4 py-2 border text-right font-semibold">Grand Total:</td>
                                <td class="px-4 py-2 border text-right font-semibold text-blue-600">Rp {{ number_format($salesOrder->grand_total, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg mt-6">
                <h2 class="text-lg font-semibold mb-4 text-gray-800">Informasi Sistem</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-600">Dibuat pada:</span><span>{{ $salesOrder->created_at->format('d/m/Y H:i:s') }}</span></div>
                    <div><span class="text-gray-600">Terakhir diupdate:</span><span>{{ $salesOrder->updated_at->format('d/m/Y H:i:s') }}</span></div>
                    @if($salesOrder->approved_at)
                        <div><span class="text-gray-600">Disetujui pada:</span><span>{{ \Carbon\Carbon::parse($salesOrder->approved_at)->format('d/m/Y H:i') }}</span></div>
                    @endif
                    @if($salesOrder->completed_at)
                        <div><span class="text-gray-600">Diselesaikan pada:</span><span>{{ \Carbon\Carbon::parse($salesOrder->completed_at)->format('d/m/Y H:i') }}</span></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleSidebar() { 
        document.getElementById('sidebar').classList.toggle('-translate-x-full'); 
    }
    function toggleDropdown(button) {
        const dropdown = button.nextElementSibling;
        const chevron = button.querySelector('.bi-chevron-down');
        dropdown.classList.toggle('max-h-0');
        dropdown.classList.toggle('max-h-40');
        chevron.classList.toggle('rotate-180');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const paymentMethod = '{{ $salesOrder->payment_method }}';
        const proofInput = document.getElementById('proof_path');
        const form = document.getElementById('paymentForm');

        // Initial setup for split fields
        const splitFields = document.getElementById('split-payment-fields');
        splitFields.classList.toggle('hidden', paymentMethod !== 'split');

        // Change event for payment_method
        document.getElementById('payment_method').addEventListener('change', function () {
            splitFields.classList.toggle('hidden', this.value !== 'split');
            if (this.value !== 'split') {
                document.getElementById('cash_amount').value = 0;
                document.getElementById('transfer_amount').value = 0;
                document.getElementById('proof_path').value = '';
            }
            updatePaymentAmount();
            updateProofRequired(this.value);
        });

        function updatePaymentAmount() {
            const method = document.getElementById('payment_method').value;
            const cash = parseFloat(document.getElementById('cash_amount')?.value || 0);
            const transfer = parseFloat(document.getElementById('transfer_amount')?.value || 0);
            const total = method === 'split' ? cash + transfer : parseFloat(document.getElementById('payment_amount').value) || 0;
            document.getElementById('payment_amount').value = total.toFixed(2);
        }

        function updateProofRequired(method) {
            if (method === 'transfer' || method === 'split') {
                proofInput.required = true;
            } else {
                proofInput.required = false;
            }
        }

        // Initial proof required
        updateProofRequired(paymentMethod);

        document.getElementById('cash_amount').addEventListener('input', updatePaymentAmount);
        document.getElementById('transfer_amount').addEventListener('input', updatePaymentAmount);

        form.addEventListener('submit', function (e) {
            const method = document.getElementById('payment_method').value;
            if ((method === 'transfer' || method === 'split') && !proofInput.files.length) {
                e.preventDefault();
                alert('Harap unggah bukti pembayaran untuk metode ' + method);
            }
        });
    });
</script>
</body>
</html>