<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Stok - Bblara</title>
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
        .form-input {
            transition: all 0.3s ease;
            border: 2px solid #e2e8f0;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            width: 100%;
            background-color: #fff;
        }
        .form-input:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
            outline: none;
        }
        .form-input:hover {
            border-color: #f59e0b;
        }
        .action-button {
            transition: all 0.3s ease;
        }
        .action-button:hover {
            transform: translateY(-1px);
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex">
        <!-- Sidebar Toggle -->
        <button class="fixed text-white text-3xl top-5 left-4 p-2 rounded-md bg-gray-700 lg:hidden focus:outline-none z-50" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>
        
        <!-- Sidebar -->
        <x-navbar-owner></x-navbar-owner>
        
        <!-- Main Content -->
        <div class="flex-1 lg:w-5/6">
            <!-- Top Navigation -->
            <x-navbar-top-owner></x-navbar-top-owner>
            
            <!-- Content Area -->
            <div class="p-4 lg:p-8">
                <div class="max-w-4xl mx-auto">
                    <!-- Breadcrumb -->
                    <nav class="flex mb-8" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li class="inline-flex items-center">
                                <a href="{{ route('owner.stock.index') }}" class="hover-link">
                                    <span class="nav-text inline-flex items-center text-gray-500">
                                        <i class="bi bi-box mr-2"></i>
                                        Stok
                                    </span>
                                </a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <i class="bi bi-chevron-right text-gray-400 mx-2"></i>
                                    <span class="text-gray-700">Edit Stok</span>
                                </div>
                            </li>
                        </ol>
                    </nav>

                    <!-- Main Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <!-- Header -->
                        <div class="border-b border-gray-100 px-8 py-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h1 class="text-2xl font-semibold text-gray-800">Edit Stok</h1>
                                    <p class="mt-2 text-gray-600">Perbarui informasi stok bahan baku</p>
                                </div>
                                <span class="px-4 py-2 bg-amber-100 text-amber-700 rounded-full text-sm font-medium">
                                    ID: #{{ $stock->id }}
                                </span>
                            </div>
                        </div>

                        <!-- Form Content -->
                        <form action="{{ route('owner.stock.update', $stock) }}" method="POST" class="p-8">
                            @csrf
                            @method('PUT')

                            <div class="space-y-6">
                                <div>
                                    <label for="raw_material" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="bi bi-box-seam mr-2 text-amber-500"></i>Bahan Baku
                                    </label>
                                    <input type="text" name="raw_material" id="raw_material"
                                           class="form-input"
                                           value="{{ old('raw_material', $stock->raw_material) }}"
                                           placeholder="Masukkan nama bahan baku"
                                           required>
                                    @error('raw_material')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="qty" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="bi bi-123 mr-2 text-blue-500"></i>Kuantitas
                                    </label>
                                    <input type="number" name="qty" id="qty"
                                           class="form-input"
                                           value="{{ old('qty', $stock->qty) }}"
                                           placeholder="Masukkan jumlah stok"
                                           required>
                                    @error('qty')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="weight" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="bi bi-rulers mr-2 text-green-500"></i>Berat
                                    </label>
                                    <div class="flex space-x-4">
                                        <div class="flex-1">
                                            <input type="text" name="weight" id="weight"
                                                   class="form-input"
                                                   value="{{ old('weight', $stock->weight) }}"
                                                   placeholder="Masukkan berat"
                                                   required>
                                            @error('weight')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <select name="unit" id="unit"
                                                class="form-input w-32">
                                            <option value="gr" {{ old('unit', $stock->unit) == 'gr' ? 'selected' : '' }}>gr</option>
                                            <option value="ml" {{ old('unit', $stock->unit) == 'ml' ? 'selected' : '' }}>ml</option>
                                            <option value="l" {{ old('unit', $stock->unit) == 'l' ? 'selected' : '' }}>l</option>
                                            <option value="ons" {{ old('unit', $stock->unit) == 'ons' ? 'selected' : '' }}>ons</option>
                                            <option value="kg" {{ old('unit', $stock->unit) == 'kg' ? 'selected' : '' }}>kg</option>
                                            <option value="pcs" {{ old('unit', $stock->unit) == 'pcs' ? 'selected' : '' }}>pcs</option>
                                        </select>
                                    </div>
                                    @error('unit')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex justify-end space-x-4 mt-8 pt-6 border-t">
                                <a href="{{ route('owner.stock.index') }}" 
                                   class="action-button inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                                    <i class="bi bi-x-lg mr-2"></i>
                                    Batal
                                </a>
                                <button type="submit" 
                                        class="action-button inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-white bg-amber-500 hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                                    <i class="bi bi-check-lg mr-2"></i>
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('-translate-x-full');
        }

        // Dropdown functionality
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