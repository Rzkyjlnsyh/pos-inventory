<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Stok - Bblara</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Base Styles */
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

        /* Card Styles */
        .stat-card {
            transition: all 0.3s ease;
            background-color: white;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            padding: 1.5rem;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }

        /* Button Styles */
        .action-button {
            transition: all 0.2s ease;
        }
        .action-button:hover {
            transform: translateY(-1px);
        }

        /* Badge Styles */
        .stock-badge {
            background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
        }

        /* Animation */
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
        }
        .pulse-animation {
            animation: pulse 2s infinite;
        }

        /* Icon Container Styles */
        .icon-container {
            padding: 0.75rem;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .icon-container i {
            font-size: 1.25rem;
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
                                <a href="{{ route('owner.stock.index') }}" class="inline-flex items-center text-gray-500">
                                    <i class="bi bi-house-door mr-2"></i>
                                    <span class="nav-text">Stok</span>
                                </a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <i class="bi bi-chevron-right text-gray-400 mx-2"></i>
                                    <span class="text-gray-700">Detail Stok</span>
                                </div>
                            </li>
                        </ol>
                    </nav>

                    <!-- Main Content Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <!-- Header -->
                        <div class="border-b border-gray-100 px-8 py-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h1 class="text-2xl font-semibold text-gray-800">Detail Stok</h1>
                                    <p class="mt-2 text-gray-600">Informasi lengkap mengenai stok bahan baku</p>
                                </div>
                                <span class="stock-badge px-4 py-2 rounded-full text-white text-sm font-medium">
                                    ID: #{{ $stock->id }}
                                </span>
                            </div>
                        </div>

                        <!-- Stock Information -->
                        <div class="p-8">
                            <!-- Stats Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                                <!-- Bahan Baku Card -->
                                <div class="stat-card">
                                    <div class="flex items-center">
                                        <div class="icon-container bg-blue-100">
                                            <i class="bi bi-box text-blue-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-500">Bahan Baku</p>
                                            <h3 class="text-lg font-semibold text-gray-800">{{ $stock->raw_material }}</h3>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quantity Card -->
                                <div class="stat-card">
                                    <div class="flex items-center">
                                        <div class="icon-container bg-green-100">
                                            <i class="bi bi-layers text-green-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-500">Kuantitas</p>
                                            <h3 class="text-lg font-semibold text-gray-800">{{ $stock->qty }} unit</h3>
                                        </div>
                                    </div>
                                </div>

                                <!-- Weight Card -->
                                <div class="stat-card">
                                    <div class="flex items-center">
                                        <div class="icon-container bg-purple-100">
                                            <i class="bi bi-speedometer2 text-purple-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-500">Berat per Unit</p>
                                            <h3 class="text-lg font-semibold text-gray-800">{{ $stock->weight }} {{ $stock->unit }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="bg-blue-50 rounded-lg p-6 mb-8">
                                <h3 class="text-sm font-medium text-blue-800 mb-3">Informasi Total</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="flex items-center">
                                        <i class="bi bi-calculator text-blue-600 mr-2"></i>
                                        <span class="text-blue-600">Total Berat: 
                                            <strong>{{ $stock->weight * $stock->qty }} {{ $stock->unit }}</strong>
                                        </span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="bi bi-clock-history text-blue-600 mr-2"></i>
                                        <span class="text-blue-600">Terakhir Diperbarui: 
                                            <strong>{{ $stock->updated_at->format('d M Y, H:i') }}</strong>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="flex flex-col md:flex-row gap-4 items-center justify-between border-t pt-6">
                                <!-- Increment/Decrement Actions -->
                                <div class="flex items-center gap-2">
                                    <form action="{{ route('owner.stock.decrement', $stock->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="action-button inline-flex items-center px-4 py-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-300">
                                            <i class="bi bi-dash-circle mr-2"></i>
                                            Kurangi
                                        </button>
                                    </form>
                                    <form action="{{ route('owner.stock.increment', $stock->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="action-button inline-flex items-center px-4 py-2 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-300">
                                            <i class="bi bi-plus-circle mr-2"></i>
                                            Tambah
                                        </button>
                                    </form>
                                </div>
                                
                                <!-- Navigation Actions -->
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('owner.stock.index') }}" 
                                       class="action-button inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200">
                                        <i class="bi bi-arrow-left mr-2"></i>
                                        Kembali
                                    </a>
                                    <a href="{{ route('owner.stock.edit', $stock) }}" 
                                       class="action-button inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-300">
                                        <i class="bi bi-pencil mr-2"></i>
                                        Edit
                                    </a>
                                    <form action="{{ route('owner.stock.destroy', $stock) }}" 
                                          method="POST" 
                                          class="inline" 
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus stok ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="action-button inline-flex items-center px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                                            <i class="bi bi-trash mr-2"></i>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Sidebar Toggle Function
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('-translate-x-full');
        }

        // Add hover animation to stat cards
        document.querySelectorAll('.stat-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.classList.add('pulse-animation');
            });
            card.addEventListener('mouseleave', function() {
                this.classList.remove('pulse-animation');
            });
        });

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