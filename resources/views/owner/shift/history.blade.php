<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Riwayat Shift - Pare Custom</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" />
</head>
<body class="bg-gray-100">
<div class="flex">
    <x-navbar-owner />
    <div class="flex-1 lg:w-5/6">
        <x-navbar-top-owner />
        <div class="p-4 lg:p-8">
            <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
                <h1 class="text-2xl font-semibold text-gray-800 mb-4">Riwayat Shift</h1>
                
                <div class="flex flex-wrap gap-4 mb-6">
                    <a href="{{ route('owner.shift.dashboard') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded shadow inline-flex items-center">
                        <i class="bi bi-arrow-left mr-2"></i> Kembali
                    </a>
                    <a href="{{ route('owner.shift.export') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded shadow inline-flex items-center">
                        <i class="bi bi-file-earmark-excel mr-2"></i> Export Excel
                    </a>
                    <a href="{{ route('owner.shift.export-pdf') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded shadow inline-flex items-center">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </a>
                </div>

                <div class="overflow-x-auto">
    <table class="w-full table-auto border-collapse">
        <thead class="bg-gray-200">
            <tr>
                <th class="px-4 py-3">Kasir</th>
                <th class="px-4 py-3">Tanggal</th>
                <th class="px-4 py-3 text-right">Kas Awal</th>
                <th class="px-4 py-3 text-right">Cash Lunas</th>
                <th class="px-4 py-3 text-right">Cash DP</th>
                <th class="px-4 py-3 text-right">Cash Pelunasan</th>
                <th class="px-4 py-3 text-right">Transfer</th>
                <th class="px-4 py-3 text-right">Pemasukan Manual</th>
                <th class="px-4 py-3 text-right">Pengeluaran</th>
                <th class="px-4 py-3 text-right">Kas Akhir</th>
                <th class="px-4 py-3 text-right">Selisih</th>
                <th class="px-4 py-3">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($shifts as $shift)
                @php
                    // Ambil pembayaran untuk shift ini
                    $payments = \App\Models\Payment::where('created_by', $shift->user_id)
                        ->where('created_at', '>=', $shift->start_time)
                        ->where('created_at', '<=', $shift->end_time ?? now())
                        ->with('salesOrder')
                        ->get();
                    $cashLunas = 0;
                    $cashDp = 0;
                    $cashPelunasan = 0;
                    $transferTotal = 0;
                    foreach ($payments as $payment) {
                        $so = $payment->salesOrder;
                        $isLunasSekaliBayar = ($payment->category === 'pelunasan' && $so->payments->count() === 1);
                        if ($payment->method === 'cash') {
                            if ($isLunasSekaliBayar) {
                                $cashLunas += $payment->amount;
                            } elseif ($payment->category === 'dp') {
                                $cashDp += $payment->amount;
                            } else {
                                $cashPelunasan += $payment->amount;
                            }
                        } elseif ($payment->method === 'transfer') {
                            $transferTotal += $payment->amount;
                        } elseif ($payment->method === 'split') {
                            if ($isLunasSekaliBayar) {
                                $cashLunas += $payment->cash_amount;
                                $transferTotal += $payment->transfer_amount;
                            } elseif ($payment->category === 'dp') {
                                $cashDp += $payment->cash_amount;
                                $transferTotal += $payment->transfer_amount;
                            } else {
                                $cashPelunasan += $payment->cash_amount;
                                $transferTotal += $payment->transfer_amount;
                            }
                        }
                    }
                @endphp
                <tr class="hover:bg-gray-50 border-b cursor-pointer" onclick="window.location='{{ route('owner.shift.show', $shift) }}'">
                    <td class="px-4 py-3">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-2">
                                <i class="bi bi-person text-blue-600"></i>
                            </div>
                            {{ $shift->user->name }}
                        </div>
                    </td>
                    <td class="px-4 py-3">{{ $shift->start_time->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-right">Rp {{ number_format($shift->initial_cash, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-green-600">Rp {{ number_format($cashLunas, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-green-600">Rp {{ number_format($cashDp, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-green-600">Rp {{ number_format($cashPelunasan, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-blue-600">Rp {{ number_format($transferTotal, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-blue-600">Rp {{ number_format($shift->income_total, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-red-600">Rp {{ number_format($shift->expense_total, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right">
                        @if($shift->final_cash)
                            <span class="font-medium">Rp {{ number_format($shift->final_cash, 0, ',', '.') }}</span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        @if($shift->discrepancy)
                            <span class="{{ $shift->discrepancy < 0 ? 'text-red-600 font-semibold' : 'text-green-600' }}">
                                Rp {{ number_format($shift->discrepancy, 0, ',', '.') }}
                            </span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-3 py-1 rounded-full text-xs font-medium
                            {{ $shift->status == 'open' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                            {{ $shift->status == 'open' ? 'Aktif' : ($shift->discrepancy < 0 ? 'Selesai (Kurang)' : ($shift->discrepancy > 0 ? 'Selesai (Lebih)' : 'Selesai')) }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

                <div class="mt-6">
                    {{ $shifts->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@if (session('pdf_download'))
<script>
    window.onload = function() {
        const pdfPath = "{{ session('pdf_download') }}";
        const link = document.createElement('a');
        link.href = "{{ url('storage') }}/" + pdfPath;
        link.download = pdfPath.split('/').pop();
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };
</script>
@endif
</body>
</html>