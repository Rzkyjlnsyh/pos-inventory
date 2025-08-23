<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Detail Pembelian - Pare Custom</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" />
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Raleway', sans-serif; }
        .nav-text { position: relative; display: inline-block; }
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
        .hover-link:hover .nav-text::after { width: 100%; }
    </style>
</head>
<body class="bg-gray-100">
<div class="flex">
    <button class="fixed text-white text-3xl top-5 left-4 p-2 rounded-md bg-gray-700 lg:hidden focus:outline-none z-50" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </button>

    <x-navbar-owner></x-navbar-owner>

    <div class="flex-1 lg:w-5/6">
        <x-navbar-top-owner></x-navbar-top-owner>

        <div class="p-4 lg:p-8">
            <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-700">Detail Pembelian</h2>
                    <a href="{{ route('owner.purchases.index') }}" class="px-4 py-2 border rounded hover:bg-gray-50 transition-colors">Kembali</a>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <div class="text-sm text-gray-500">No. Pembelian</div>
                        <div class="text-lg font-semibold">{{ $purchase->po_number }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500">Status</div>
                        <div class="text-lg font-semibold capitalize">
                            <span class="px-2 py-1 rounded text-xs 
                                @if($purchase->status === 'draft') bg-gray-100 text-gray-800
                                @elseif($purchase->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($purchase->status === 'approved') bg-blue-100 text-blue-800
                                @elseif($purchase->status === 'received') bg-green-100 text-green-800
                                @elseif($purchase->status === 'cancelled') bg-red-100 text-red-800
                                @elseif($purchase->status === 'returned') bg-purple-100 text-purple-800
                                @endif">
                                {{ $purchase->status }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    <div>
                        <div class="text-sm text-gray-500">Tanggal Pembelian</div>
                        <div>{{ \Carbon\Carbon::parse($purchase->order_date)->format('d M Y') }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Supplier</div>
                        <div>{{ $purchase->supplier?->name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Dibuat Oleh</div>
                        <div>{{ $purchase->creator->name ?? 'System' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Subtotal</div>
                        <div>Rp {{ number_format($purchase->subtotal,0,',','.') }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Diskon</div>
                        <div>Rp {{ number_format($purchase->discount_total,0,',','.') }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Grand Total</div>
                        <div class="font-semibold text-green-600">Rp {{ number_format($purchase->grand_total,0,',','.') }}</div>
                    </div>
                </div>

                <!-- Log Information -->
<div class="bg-gray-50 p-4 rounded-lg mb-6">
  <h3 class="text-lg font-semibold text-gray-800 mb-3">Log Aktivitas</h3>
  
  <div class="space-y-3">
    <!-- Waktu Pembuatan -->
    <div class="flex items-start">
      <div class="w-32 flex-shrink-0">
        <span class="text-sm font-medium text-gray-600">Dibuat Oleh:</span>
      </div>
      <div>
        <div class="text-sm text-gray-900">
          @if($purchase->creator)
            {{ $purchase->creator->name }}, 
          @else
            System, 
          @endif
          {{ \Carbon\Carbon::parse($purchase->created_at)->format('Y-m-d H:i:s') }}
        </div>
      </div>
    </div>

    <!-- Waktu Approval -->
    @if($purchase->approved_at)
    <div class="flex items-start">
      <div class="w-32 flex-shrink-0">
        <span class="text-sm font-medium text-gray-600">Disetujui Oleh:</span>
      </div>
      <div>
        <div class="text-sm text-gray-900">
          @if($purchase->approver)
            {{ $purchase->approver->name }}, 
          @else
            System, 
          @endif
          {{ \Carbon\Carbon::parse($purchase->approved_at)->format('Y-m-d H:i:s') }}
        </div>
      </div>
    </div>
    @endif

    <!-- Waktu Penerimaan -->
    @if($purchase->received_at)
    <div class="flex items-start">
      <div class="w-32 flex-shrink-0">
        <span class="text-sm font-medium text-gray-600">Diterima Oleh:</span>
      </div>
      <div>
        <div class="text-sm text-gray-900">
          @if($purchase->receiver)
            {{ $purchase->receiver->name }}, 
          @else
            System, 
          @endif
          {{ \Carbon\Carbon::parse($purchase->received_at)->format('Y-m-d H:i:s') }}
        </div>
      </div>
    </div>
    @endif

    <!-- Terakhir Diupdate -->
    <div class="flex items-start">
      <div class="w-32 flex-shrink-0">
        <span class="text-sm font-medium text-gray-600">Terakhir Update:</span>
      </div>
      <div>
        <div class="text-sm text-gray-900">
          {{ \Carbon\Carbon::parse($purchase->updated_at)->format('Y-m-d H:i:s') }}
        </div>
      </div>
    </div>
  </div>
</div>
            <div class="overflow-x-auto mb-6">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diskon</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  @foreach($purchase->items as $item)
                  <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">{{ $item->product_name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $item->sku ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">Rp {{ number_format($item->cost_price,0,',','.') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $item->qty }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">Rp {{ number_format($item->discount,0,',','.') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap font-semibold">Rp {{ number_format($item->line_total,0,',','.') }}</td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>

                <!-- File Uploads Section -->
                @if($purchase->status === 'approved' || $purchase->status === 'received' || $purchase->invoice_file || $purchase->payment_proof_file)
                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Dokumen Upload</h3>
                    <div class="space-y-3">
                        @if($purchase->invoice_file)
                        <div>
                            <span class="font-medium text-gray-700">Faktur:</span>
                            <a href="{{ asset('storage/' . $purchase->invoice_file) }}" target="_blank" class="text-blue-600 underline hover:text-blue-800">
                                Lihat File
                            </a>
                        </div>
                        @else
                        <div>
                            <span class="font-medium text-gray-700 text-gray-500">Faktur belum diupload.</span>
                        </div>
                        @endif

                        @if($purchase->payment_proof_file)
                        <div>
                            <span class="font-medium text-gray-700">Bukti Pembayaran:</span>
                            <a href="{{ asset('storage/' . $purchase->payment_proof_file) }}" target="_blank" class="text-blue-600 underline hover:text-blue-800">
                                Lihat File
                            </a>
                        </div>
                        @else
                        <div>
                            <span class="font-medium text-gray-700 text-gray-500">Bukti Pembayaran belum diupload.</span>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <div class="flex items-center space-x-2 mt-6">
                    @if($purchase->status === 'draft')
                    <form method="POST" action="{{ route('owner.purchases.submit', $purchase) }}">
                        @csrf
                        <button class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors">
                            <i class="bi bi-send mr-1"></i>Ajukan
                        </button>
                    </form>
                    @endif

                    @if($purchase->status === 'pending')
                    <button onclick="openModal('approve-modal-{{ $purchase->id }}')" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors">
                        <i class="bi bi-check-circle mr-1"></i>Approve
                    </button>

                    <div id="approve-modal-{{ $purchase->id }}" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
                        <div class="bg-white rounded-lg p-6 w-full max-w-md">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">Approve Pembelian {{ $purchase->po_number }}</h3>
                            <form action="{{ route('owner.purchases.approve', $purchase->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-4">
                                    <label for="invoice_file_{{ $purchase->id }}" class="block text-sm font-medium text-gray-700 mb-1">Upload Faktur (PDF/JPG/PNG)</label>
                                    <input type="file" id="invoice_file_{{ $purchase->id }}" name="invoice_file" accept=".pdf,.jpg,.jpeg,.png" required class="w-full border rounded p-2 text-gray-900" />
                                </div>
                                <div class="mb-4">
                                    <label for="payment_proof_file_{{ $purchase->id }}" class="block text-sm font-medium text-gray-700 mb-1">Upload Bukti Pembayaran (PDF/JPG/PNG)</label>
                                    <input type="file" id="payment_proof_file_{{ $purchase->id }}" name="payment_proof_file" accept=".pdf,.jpg,.jpeg,.png" required class="w-full border rounded p-2 text-gray-900" />
                                </div>
                                <div class="flex justify-end space-x-2">
                                    <button type="button" onclick="closeModal('approve-modal-{{ $purchase->id }}')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Batal</button>
                                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:opacity-90">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif

                    @if($purchase->status === 'approved')
                    <form method="POST" action="{{ route('owner.purchases.receive', $purchase) }}">
                        @csrf
                        <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                            <i class="bi bi-box-arrow-in-down mr-1"></i>Terima
                        </button>
                    </form>
                    @endif

                    @if($purchase->status === 'draft')
                    <form method="POST" action="{{ route('owner.purchases.cancel', $purchase) }}">
                        @csrf
                        @method('PATCH')
                        <button class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors" onclick="return confirm('Apakah Anda yakin ingin membatalkan pembelian ini?')">
                            <i class="bi bi-x-circle mr-1"></i>Batalkan
                        </button>
                    </form>
                    @endif

                    <a href="{{ route('owner.purchases.index') }}" class="px-4 py-2 border rounded hover:bg-gray-50 transition-colors">
                        <i class="bi bi-arrow-left mr-1"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleSidebar() {
        const el = document.getElementById('sidebar');
        if (!el) return;
        el.classList.toggle('-translate-x-full');
    }
    function toggleDropdown(btn) {
        const menu = btn.nextElementSibling;
        if (!menu) return;
        if (menu.style.maxHeight && menu.style.maxHeight !== '0px') {
            menu.style.maxHeight = '0px';
            btn.querySelector('i.bi-chevron-down')?.classList.remove('rotate-180');
        } else {
            menu.style.maxHeight = menu.scrollHeight + 'px';
            btn.querySelector('i.bi-chevron-down')?.classList.add('rotate-180');
        }
    }
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }
    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }
</script>
</body>
</html>
