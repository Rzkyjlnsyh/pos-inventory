<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Riwayat Shift - Pare Custom</title>
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
    </style>
</head>
<body class="bg-gray-100">
<div class="flex">
    <x-navbar-owner />
    <div class="flex-1 lg:w-5/6">
        <x-navbar-top-owner />
        <div class="p-4 lg:p-8">
            <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
                <h1 class="text-2xl font-semibold text-gray-800 mb-4">Riwayat Shift</h1>
                <div class="mb-4">
                <a href="{{ route('owner.shift.dashboard') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded shadow inline-block">
                        <i class="bi bi-arrow-left"></i> Dashboard Shift
                    </a>
                    <a href="{{ route('owner.shift.export') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded shadow inline-block">
                        <i class="bi bi-download"></i> Export ke Excel
                    </a>
                    <a href="{{ route('owner.shift.export-pdf') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded shadow inline-block ml-2">
                        <i class="bi bi-file-earmark-pdf"></i> Export ke PDF
                    </a>
                </div>

                <!-- Tambahan: Pembungkus scroll horizontal -->
                <div class="overflow-x-auto w-full">
                    <table class="min-w-[1000px] w-full table-auto border-collapse">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="px-4 py-2">Pengguna</th>
                                <th class="px-4 py-2">Waktu Mulai</th>
                                <th class="px-4 py-2">Waktu Selesai</th>
                                <th class="px-4 py-2">Kas Awal</th>
                                <th class="px-4 py-2">Total Kas</th>
                                <th class="px-4 py-2">Total Pengeluaran</th>
                                <th class="px-4 py-2">Kas Akhir</th>
                                <th class="px-4 py-2">Selisih</th>
                                <th class="px-4 py-2">Catatan</th>
                                <th class="px-4 py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($shifts as $shift)
                                <tr class="hover:bg-gray-100 cursor-pointer" onclick="window.location='{{ route('owner.shift.show', $shift) }}'">
                                    <td class="border px-4 py-2">{{ $shift->user->name }}</td>
                                    <td class="border px-4 py-2">{{ \Carbon\Carbon::parse($shift->start_time)->format('d/m/Y H:i') }}</td>
                                    <td class="border px-4 py-2">{{ $shift->end_time ? \Carbon\Carbon::parse($shift->end_time)->format('d/m/Y H:i') : '-' }}</td>
                                    <td class="border px-4 py-2">Rp {{ number_format($shift->initial_cash, 0, ',', '.') }}</td>
                                    <td class="border px-4 py-2">Rp {{ number_format($shift->cash_total, 0, ',', '.') }}</td>
                                    <td class="border px-4 py-2">Rp {{ number_format($shift->expense_total, 0, ',', '.') }}</td>
                                    <td class="border px-4 py-2">{{ $shift->final_cash ? 'Rp ' . number_format($shift->final_cash, 0, ',', '.') : '-' }}</td>
                                    <td class="border px-4 py-2">{{ $shift->discrepancy ? 'Rp ' . number_format($shift->discrepancy, 0, ',', '.') : '-' }}</td>
                                    <td class="border px-4 py-2">{{ $shift->notes ?? '-' }}</td>
                                    <td class="border px-4 py-2">{{ ucfirst($shift->status) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $shifts->links() }}
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
</script>
</body>
</html>
