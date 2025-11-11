<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Import Sales Order - Pare Custom</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
            <!-- Page Title -->
            <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-800">Import Sales Order</h1>
                        <p class="text-sm text-gray-500 mt-1">Upload file Excel untuk import data sales order</p>
                    </div>
                    <a href="{{ route('admin.sales.index') }}" 
                       class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>

            <!-- Import Form -->
            <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Form Upload -->
                    <div>
                        <h2 class="text-lg font-semibold mb-4">Upload File Excel</h2>
                        <form action="{{ route('admin.sales.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih File Excel</label>
                                <input type="file" name="file" accept=".xlsx,.xls" required
                                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                <p class="text-xs text-gray-500 mt-1">Format: .xlsx, .xls (Max: 2MB)</p>
                            </div>
                            <button type="submit" 
                                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded shadow flex items-center">
                                <i class="bi bi-upload mr-2"></i> Import Data
                            </button>
                        </form>
                    </div>

                    <!-- Instructions -->
                    <div>
                        <h2 class="text-lg font-semibold mb-4">Petunjuk Import</h2>
                        <div class="space-y-3 text-sm">
                            <div class="flex items-start">
                                <i class="bi bi-download text-blue-600 mr-2 mt-0.5"></i>
                                <span><strong>Download template</strong> terlebih dahulu untuk format yang benar</span>
                            </div>
                            <div class="flex items-start">
                                <i class="bi bi-table text-green-600 mr-2 mt-0.5"></i>
                                <span><strong>Kolom wajib:</strong> ORDER_DATE, CUSTOMER_NAME, ORDER_TYPE, PAYMENT_METHOD, PRODUCT_NAME, SALE_PRICE, QTY</span>
                            </div>
                            <div class="flex items-start">
                                <i class="bi bi-info-circle text-yellow-600 mr-2 mt-0.5"></i>
                                <span><strong>ORDER_TYPE:</strong> jahit_sendiri atau beli_jadi</span>
                            </div>
                            <div class="flex items-start">
                                <i class="bi bi-currency-dollar text-purple-600 mr-2 mt-0.5"></i>
                                <span><strong>PAYMENT_METHOD:</strong> cash, transfer, atau split</span>
                            </div>
                            <div class="flex items-start">
                                <i class="bi bi-check-circle text-red-600 mr-2 mt-0.5"></i>
                                <span><strong>Pastikan shift aktif</strong> sebelum melakukan import</span>
                            </div>
                        </div>

                        <div class="mt-6">
                            <a href="{{ route('admin.sales.download-template') }}" 
                               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow flex items-center justify-center text-sm">
                                <i class="bi bi-file-earmark-spreadsheet mr-2"></i> Download Template Import
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Error Display -->
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <h4 class="font-bold">Terjadi kesalahan:</h4>
                    <ul class="list-disc list-inside mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($errors->has('import_errors'))
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-6">
                    <h4 class="font-bold">Error Import:</h4>
                    <ul class="list-disc list-inside mt-2 max-h-60 overflow-y-auto">
                        @foreach ($errors->get('import_errors') as $errorArray)
                            @foreach ($errorArray as $error)
                                <li class="text-sm">{{ $error }}</li>
                            @endforeach
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Sample Data -->
<!-- GANTI bagian Sample Data dengan ini: -->
<div class="bg-white p-6 rounded-xl shadow-lg">
    <h2 class="text-lg font-semibold mb-4">Contoh Format Data (1 SO bisa multiple items)</h2>
    <div class="overflow-x-auto">
        <table class="w-full table-auto border-collapse text-sm">
            <thead>
                <tr class="bg-gray-50">
                    <th class="border px-3 py-2 text-left">SO_NUMBER</th>
                    <th class="border px-3 py-2 text-left">ORDER_DATE</th>
                    <th class="border px-3 py-2 text-left">CUSTOMER_NAME</th>
                    <th class="border px-3 py-2 text-left">ORDER_TYPE</th>
                    <th class="border px-3 py-2 text-left">PAYMENT_METHOD</th>
                    <th class="border px-3 py-2 text-left">PRODUCT_NAME</th>
                    <th class="border px-3 py-2 text-left">SALE_PRICE</th>
                    <th class="border px-3 py-2 text-left">QTY</th>
                </tr>
            </thead>
            <tbody>
                <!-- SO 1 - Item 1 -->
                <tr class="bg-blue-50">
                    <td class="border px-3 py-2 font-semibold">SAL2501010001</td>
                    <td class="border px-3 py-2">2025-01-15</td>
                    <td class="border px-3 py-2">Budi Santoso</td>
                    <td class="border px-3 py-2">beli_jadi</td>
                    <td class="border px-3 py-2">cash</td>
                    <td class="border px-3 py-2">Kaos Polo Lengan Pendek</td>
                    <td class="border px-3 py-2">75000</td>
                    <td class="border px-3 py-2">2</td>
                </tr>
                <!-- SO 1 - Item 2 -->
                <tr class="bg-blue-50">
                    <td class="border px-3 py-2 font-semibold">SAL2501010001</td>
                    <td class="border px-3 py-2">2025-01-15</td>
                    <td class="border px-3 py-2">Budi Santoso</td>
                    <td class="border px-3 py-2">beli_jadi</td>
                    <td class="border px-3 py-2">cash</td>
                    <td class="border px-3 py-2">Celana Chino</td>
                    <td class="border px-3 py-2">120000</td>
                    <td class="border px-3 py-2">1</td>
                </tr>
                <!-- SO 2 - Item 1 -->
                <tr class="bg-green-50">
                    <td class="border px-3 py-2 font-semibold">SAL2501010002</td>
                    <td class="border px-3 py-2">2025-01-16</td>
                    <td class="border px-3 py-2">Siti Rahayu</td>
                    <td class="border px-3 py-2">jahit_sendiri</td>
                    <td class="border px-3 py-2">transfer</td>
                    <td class="border px-3 py-2">Kemeja Linen</td>
                    <td class="border px-3 py-2">150000</td>
                    <td class="border px-3 py-2">1</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="mt-4 text-sm text-gray-600">
        <p><strong>Catatan:</strong></p>
        <ul class="list-disc list-inside mt-2">
            <li>SO dengan <strong>nomor yang sama</strong> akan dianggap sebagai 1 Sales Order dengan multiple items</li>
            <li>Data customer, tanggal, dan payment method diambil dari <strong>baris pertama</strong> setiap SO</li>
            <li>Pastikan SO_NUMBER, ORDER_DATE, CUSTOMER_NAME konsisten untuk SO yang sama</li>
        </ul>
    </div>
</div>
        </div>
    </div>
</div>
</body>
</html>