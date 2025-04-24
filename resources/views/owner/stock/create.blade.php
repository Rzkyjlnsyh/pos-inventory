<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Stok - Bblara</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Raleway', sans-serif;
            background-color: #f8fafc;
        }
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
        a:hover .nav-text::after {
            width: 100%;
        }
        .form-input {
            transition: all 0.3s ease;
        }
        .form-input:focus {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .input-group:hover label {
            color: #2563eb;
        }
        .custom-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236B7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }
        .floating-label {
            transform: translateY(-50%) scale(0.85);
            background-color: white;
            padding: 0 0.25rem;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex">
        <button class="fixed text-white text-3xl top-5 left-4 p-2 rounded-md bg-gray-700 lg:hidden focus:outline-none z-50" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>
        <x-navbar-owner></x-navbar-owner>
        
        <div class="flex-1 lg:w-5/6">
            <x-navbar-top-owner></x-navbar-top-owner>
            
            <div class="p-4 lg:p-8">
                <div class="max-w-4xl mx-auto">
                    <!-- Breadcrumb -->
                    <nav class="flex mb-8" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li class="inline-flex items-center">
                                <a href="{{ route('owner.stock.index') }}" class="inline-flex items-center text-gray-500 hover:text-blue-600">
                                    <i class="bi bi-house-door mr-2"></i>
                                    Stok
                                </a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <i class="bi bi-chevron-right text-gray-400 mx-2"></i>
                                    <span class="text-gray-700">Tambah Stok Baru</span>
                                </div>
                            </li>
                        </ol>
                    </nav>

                    <!-- Main Form Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <!-- Form Header -->
                        <div class="border-b border-gray-100 px-8 py-6">
                            <h1 class="text-2xl font-semibold text-gray-800">Tambah Stok Baru</h1>
                            <p class="mt-2 text-gray-600">Isi formulir di bawah untuk menambahkan stok bahan baku baru</p>
                        </div>

                        <!-- Form Content -->
                        <div class="p-8">
                            <form action="{{ route('owner.stock.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                                @csrf
                                
                                <!-- Bahan Baku Input -->
                                <div class="input-group relative">
                                    <label for="raw_material" class="absolute -top-2 left-2 z-10 px-1 text-xs font-medium text-gray-500 floating-label">
                                        Nama Bahan Baku
                                    </label>
                                    <input type="text" 
                                           name="raw_material" 
                                           id="raw_material" 
                                           class="form-input block w-full px-4 py-3 text-gray-700 border border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200" 
                                           value="{{ old('raw_material') }}"
                                           placeholder="Masukkan nama bahan baku"
                                           required>
                                    @error('raw_material')
                                        <p class="mt-2 text-sm text-red-600">
                                            <i class="bi bi-exclamation-circle mr-1"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Quantity and Weight Group -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Quantity Input -->
                                    <div class="input-group relative">
                                        <label for="qty" class="absolute -top-2 left-2 z-10 px-1 text-xs font-medium text-gray-500 floating-label">
                                            Kuantitas
                                        </label>
                                        <input type="number" 
                                               name="qty" 
                                               id="qty" 
                                               class="form-input block w-full px-4 py-3 text-gray-700 border border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200" 
                                               value="{{ old('qty') }}"
                                               min="1"
                                               placeholder="Masukkan jumlah"
                                               required>
                                        @error('qty')
                                            <p class="mt-2 text-sm text-red-600">
                                                <i class="bi bi-exclamation-circle mr-1"></i>
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>

                                    <!-- Weight Input Group -->
                                    <div class="space-y-2">
                                        <div class="flex gap-3">
                                            <div class="input-group relative flex-1">
                                                <label for="weight" class="absolute -top-2 left-2 z-10 px-1 text-xs font-medium text-gray-500 floating-label">
                                                    Berat
                                                </label>
                                                <input type="number" 
                                                       name="weight" 
                                                       id="weight" 
                                                       class="form-input block w-full px-4 py-3 text-gray-700 border border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200" 
                                                       value="{{ old('weight') }}"
                                                       min="0.1"
                                                       step="0.1"
                                                       placeholder="Masukkan berat"
                                                       required>
                                            </div>
                                            <div class="input-group relative w-32">
                                                <label for="unit" class="absolute -top-2 left-2 z-10 px-1 text-xs font-medium text-gray-500 floating-label">
                                                    Satuan
                                                </label>
                                                <select name="unit" 
                                                        id="unit" 
                                                        class="form-input custom-select block w-full px-4 py-3 text-gray-700 border border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                                                    <option value="gr">gram (gr)</option>
                                                    <option value="kg">kilogram (kg)</option>
                                                    <option value="ml">mililiter (ml)</option>
                                                    <option value="l">liter (l)</option>
                                                    <option value="ons">ons</option>
                                                </select>
                                            </div>
                                        </div>
                                        @error('weight')
                                            <p class="text-sm text-red-600">
                                                <i class="bi bi-exclamation-circle mr-1"></i>
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Preview Section -->
                                <div class="bg-blue-50 rounded-lg p-4 mt-6">
                                    <h3 class="text-sm font-medium text-blue-800 mb-2">Preview Informasi Stok</h3>
                                    <div class="text-blue-600" id="previewInfo">
                                        Menambahkan <span id="previewQty">0</span> unit 
                                        <span id="previewMaterial">bahan baku</span> dengan berat 
                                        <span id="previewWeight">0</span> <span id="previewUnit">gr</span> per unit
                                    </div>
                                </div>

                                <!-- Form Actions -->
                                <div class="flex items-center justify-end space-x-4 pt-6 border-t">
                                    <button type="button" 
                                            onclick="window.history.back()" 
                                            class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 transition-all duration-200">
                                        <i class="bi bi-arrow-left mr-2"></i>
                                        Kembali
                                    </button>
                                    <button type="submit" 
                                            class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 transition-all duration-200">
                                        <i class="bi bi-check-lg mr-2"></i>
                                        Simpan Stok
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Live Preview Updates
        const rawMaterialInput = document.getElementById('raw_material');
        const qtyInput = document.getElementById('qty');
        const weightInput = document.getElementById('weight');
        const unitSelect = document.getElementById('unit');
        
        const previewMaterial = document.getElementById('previewMaterial');
        const previewQty = document.getElementById('previewQty');
        const previewWeight = document.getElementById('previewWeight');
        const previewUnit = document.getElementById('previewUnit');

        function updatePreview() {
            previewMaterial.textContent = rawMaterialInput.value || 'bahan baku';
            previewQty.textContent = qtyInput.value || '0';
            previewWeight.textContent = weightInput.value || '0';
            previewUnit.textContent = unitSelect.value;
        }

        // Event listeners for live preview
        rawMaterialInput.addEventListener('input', updatePreview);
        qtyInput.addEventListener('input', updatePreview);
        weightInput.addEventListener('input', updatePreview);
        unitSelect.addEventListener('change', updatePreview);

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const required = ['raw_material', 'qty', 'weight'];
            let isValid = true;

            required.forEach(field => {
                const input = document.getElementById(field);
                if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add('border-red-500');
                } else {
                    input.classList.remove('border-red-500');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang diperlukan');
            }
        });

        // Sidebar toggle
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('-translate-x-full');
        }
    </script>
    <script>
        function toggleDropdown(button) {
            const dropdownMenus = document.querySelectorAll(".dropdown-menu");
            const dropdownArrows = document.querySelectorAll("i.bi-chevron-down");

            dropdownMenus.forEach((menu) => {
                if (menu !== button.nextElementSibling) {
                    menu.classList.add("max-h-0");
                    menu.classList.remove("max-h-40");
                }
            });

            dropdownArrows.forEach((arrow) => {
                if (arrow !== button.querySelector("i.bi-chevron-down")) {
                    arrow.classList.remove("rotate-180");
                }
            });

            const dropdownMenu = button.nextElementSibling;
            const dropdownArrow = button.querySelector("i.bi-chevron-down");

            if (dropdownMenu.classList.contains("max-h-0")) {
                dropdownMenu.classList.remove("max-h-0");
                dropdownMenu.classList.add("max-h-40");
                dropdownArrow.classList.add("rotate-180");
            } else {
                dropdownMenu.classList.add("max-h-0");
                dropdownMenu.classList.remove("max-h-40");
                dropdownArrow.classList.remove("rotate-180");
            }
        }
    </script>
</body>
</html>