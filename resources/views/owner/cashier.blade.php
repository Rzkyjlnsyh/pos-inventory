<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Kasir - Bblara</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Add this after the other font imports -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" />

    <!-- Font Cdn -->
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- jQuery (diperlukan untuk Toastr) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <style>
        body {
            font-family: 'Raleway', sans-serif;
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

        /* Add this to your existing style tag */
        .receipt-section {
            position: fixed;
            right: 0;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            width: 16.666667%;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem; /* Slightly larger than the original */
        }

        /* Update specific elements within the receipt */
        .receipt-section h1 {
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
        }

        .receipt-section #item-list {
            font-weight: 500;
            font-size: 0.95rem;
        }

        .receipt-section #item-list .font-semibold {
            font-weight: 600;
            font-size: 1rem;
        }

        .receipt-section #subtotal {
            font-weight: 700;
            font-size: 1.1rem;
        }

        /* Adjust main content to account for fixed receipt section */
        .main-content {
            margin-right: 16.666667%; /* Same as receipt section width */
        }

        /* Add these styles to your existing style tag */
        .filter-btn {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            color: #6B7280;
            background-color: #F3F4F6;
            border-radius: 0.375rem;
            transition: all 0.2s ease;
            white-space: nowrap;
            border: 1px solid transparent;
        }

        .filter-btn:hover {
            background-color: #E5E7EB;
            color: #374151;
        }

        .filter-btn.active {
            background-color: #ffffff;
            color: #e17f12;
            border-color: #e17f12;
            font-weight: 500;
        }

        #filterContainer {
            -ms-overflow-style: none;
            scrollbar-width: none;
            padding: 0.25rem 0;
        }

        #filterContainer::-webkit-scrollbar {
            display: none;
        }

        #scrollLeft, #scrollRight {
            opacity: 0.8;
            transition: all 0.2s ease;
        }

        #scrollLeft:hover, #scrollRight:hover {
            opacity: 1;
        }

        #filterMenu {
            will-change: transform;
            gap: 0.5rem;
        }

        /* Add smooth shadow indication for scroll */
        .scroll-shadow-left::before,
        .scroll-shadow-right::after {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            width: 20px;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }

        .scroll-shadow-left::before {
            left: 0;
            background: linear-gradient(to right, rgba(255,255,255,0.9), rgba(255,255,255,0));
        }

        .scroll-shadow-right::after {
            right: 0;
            background: linear-gradient(to left, rgba(255,255,255,0.9), rgba(255,255,255,0));
        }

        /* Add these styles to your existing style section */
        .edit-modal {
            max-height: 80vh;
            overflow-y: auto;
        }

        .edit-modal::-webkit-scrollbar {
            width: 6px;
        }

        .edit-modal::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .edit-modal::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .edit-modal::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Updated styles */
        .card-view {
            display: grid !important;
            grid-template-columns: repeat(5, 1fr) !important; /* Changed to 5 columns */
            gap: 1rem;
            padding: 1rem;
        }
        .product-card {
            display: flex;
            flex-direction: column;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
            background-color: white;
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-card-image {
            position: relative;
            width: 100%;
            padding-top: 100%; /* Ini akan membuat rasio 1:1 */
            overflow: hidden;
        }

        .product-card-image img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .product-card-details {
            padding: 1rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .product-card-title {
            font-weight: bold;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            color: #005281;
        }

        .product-card-price {
            color: #e17f12;
            font-weight: bold;
            font-size: 1rem;
            margin-bottom: 1rem;
        }

        .product-card-action {
            margin-top: auto;
        }

        /* Updated media queries */
        @media (max-width: 1600px) {
            .card-view {
                grid-template-columns: repeat(4, 1fr) !important;
            }
        }

        @media (max-width: 1280px) {
            .card-view {
                grid-template-columns: repeat(3, 1fr) !important;
            }
        }

        @media (max-width: 1024px) {
            .card-view {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }

        @media (max-width: 640px) {
            .card-view {
                grid-template-columns: repeat(1, 1fr) !important;
            }
        }
    
        @media print {
            @page {
                margin: 0;
                size: 72mm 100%;
            }

            .receipt-section,
            .receipt-section * {
                visibility: visible;
                width: 72mm !important;
                margin: 0 auto !important;
                padding: 2mm !important;
                /* Use the same font family as web version */
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
                /* Increase base font size */
                font-size: 14pt !important;
                line-height: 1.4 !important;
            }

            /* Header styling */
            .receipt-section h1 {
                font-size: 36pt !important;
                font-weight: 700 !important;
                margin-bottom: 4mm !important;
                text-align: center !important;
                font-family: 'Inter', sans-serif !important;
            }

            /* Date and cashier info */
            #current-date,
            #cashier-name {
                font-size: 24pt !important;
                font-weight: 500 !important;
                margin-bottom: 2mm !important;
                font-family: 'Inter', sans-serif !important;
            }

            /* Items list styling */
            #item-list {
                margin: 4mm 0 !important;
                padding: 2mm 0 !important;
            }

            #item-list .font-semibold {
                font-size: 24pt !important;
                font-weight: 600 !important;
                margin-bottom: 2mm !important;
                font-family: 'Inter', sans-serif !important;
            }

            #item-list ul {
                margin-left: 3mm !important;
                font-size: 22pt !important;
            }

            #item-list li {
                margin-bottom: 1.5mm !important;
                line-height: 1.4 !important;
                font-weight: 500 !important;
                font-family: 'Inter', sans-serif !important;
            }

            /* Subtotal section */
            #subtotal,
            .text-lg {
                font-size: 24pt !important;
                font-weight: 700 !important;
                margin: 3mm 0 !important;
                font-family: 'Inter', sans-serif !important;
            }

            /* Ensure proper text contrast */
            * {
                color: black !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

    <div class="flex relative">

        <!-- Navbar Owner -->
        <x-navbar-owner></x-navbar-owner>

        <!-- Main Content -->
        <section class="w-4/6 bg-white shadow rounded main-content xl:w-4/6 lg:w-[62.5%]">
            <x-navbar-top-owner></x-navbar-top-owner>

            <!-- Search Bar -->
            <div class="p-4">
                <div class="relative">
                    <input 
                        type="text" 
                        id="searchInput" 
                        class="w-full px-4 py-2 pl-10 pr-8 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#005281] focus:border-transparent"
                        placeholder="Search product name..."
                    >
                    <div class="absolute top-2.5 left-3 text-gray-400">
                        <i class="bi bi-search"></i>
                    </div>
                    <button 
                        id="clearSearch" 
                        class="absolute top-2.5 right-3 text-gray-400 hover:text-gray-600 hidden"
                        onclick="clearSearchInput()"
                    >
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <!-- Add this right after the search bar section (around line 282) -->
            <div class="px-4 pt-1 pb-2 flex justify-between items-center">
                <div class="flex items-center">
                    <span class="mr-2 text-gray-700 font-medium">View Mode:</span>
                    <label class="inline-flex items-center cursor-pointer">
                        <span class="mr-2 text-sm text-gray-700">Detailed</span>
                        <div class="relative">
                            <input type="checkbox" id="view-toggle" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-2 peer-focus:ring-blue-300 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#e17f12]"></div>
                        </div>
                        <span class="ml-2 text-sm text-gray-700">Quick Cards</span>
                    </label>
                </div>
            </div>

            <!-- Filter Menu Section -->
            <div class="px-4 pt-1 pb-2">
                <div class="relative">
                    <div class="flex items-center">
                        <!-- Left Arrow -->
                        <button id="scrollLeft" class="hidden p-2 text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        
                        <!-- Filter Container -->
                        <div id="filterContainer" class="flex-1 overflow-hidden">
                            <div id="filterMenu" class="flex gap-2 transition-all duration-300 ease-in-out">
                                <button class="filter-btn active" data-category="all">
                                    All Menu
                                </button>
                                <!-- Dynamic filter buttons will be added here -->
                            </div>
                        </div>
                        
                        <!-- Right Arrow -->
                        <button id="scrollRight" class="hidden p-2 text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div id="products-container">
                <!-- Main content section -->
                @foreach ($products as $row)
                <form class="p-6" method="POST" action="#" onsubmit="addToOrder(event)" data-product-name="{{ $row->name }}" data-product-price="{{ $row->price }}" data-cost-price="{{ $row->cost_price }}" data-product-id="{{ $row->id }}">
                        <div class="w-full p-6 bg-white rounded shadow-lg">
                            <header class="mb-4">
                                <h2 class="font-bold text-xl text-green-700">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}. {{ $row->name }} (Rp {{ number_format($row->price, 0, ',', '.') }})</h2>
                            </header>
                        
                            <!-- Kontainer untuk gambar dan kontrol -->
                            <div class="flex gap-6">
                                <!-- Gambar -->
                                <figure class="w-1/3">
                                    <img src="{{ Storage::url($row->image_path) }}" alt="Macchiato Milk Tea" class="w-56 h-56 object-cover object-center">
                                </figure>
                        
                                <!-- Kontrol -->
                                <div class="w-2/3 grid grid-cols-2 gap-4">
                                    <!-- Amount -->
                                    <div class="p-4 bg-white rounded shadow">
                                        <label class="block font-medium text-gray-700 mb-2">Amount:</label>
                                        <div class="flex items-center justify-center space-x-2">
                                            <button type="button" class="px-3 py-1 bg-[#e17f12] rounded-full w-12 h-12 shadow text-white font-bold lg:h-10 lg:w-10 lg:text-sm" onclick="decrementAmount()">-</button>
                                            <input type="text" name="amount" value="1" class="w-10 text-center border rounded h-12">
                                            <button type="button" class="px-3 py-1 bg-[#e17f12] rounded-full w-12 h-12 shadow text-white lg:h-10 lg:w-10 lg:text-sm" onclick="incrementAmount()">+</button>
                                        </div>
                                    </div>
                        
                                    <!-- Size -->
                                    <div class="p-4 bg-white rounded shadow">
                                        <label class="block font-medium text-gray-700 mb-2">Size:</label>
                                        <div class="flex items-center justify-center space-x-4">
                                            <div class="relative">
                                                <input type="radio" name="size_{{ $row->id }}" id="size-m-{{ $row->id }}" value="M" class="sr-only peer" onchange="updatePrice()" checked>
                                                <label for="size-m-{{ $row->id }}" class="flex items-center justify-center px-3 py-1 border rounded-full w-12 h-12 text-center cursor-pointer peer-checked:bg-[#e17f12] peer-checked:text-white hover:bg-gray-50 lg:h-10 lg:w-10 lg:text-sm">M</label>
                                            </div>
                                            <div class="relative">
                                                <input type="radio" name="size_{{ $row->id }}" id="size-l-{{ $row->id }}" value="L" class="sr-only peer" onchange="updatePrice()">
                                                <label for="size-l-{{ $row->id }}" class="flex items-center justify-center px-3 py-1 border rounded-full w-12 h-12 text-center cursor-pointer peer-checked:bg-[#e17f12] peer-checked:text-white hover:bg-gray-50 lg:h-10 lg:w-10 lg:text-sm">L</label>
                                            </div>
                                        </div>
                                    </div>
                        
                                    <!-- Sugar -->
                                    <div class="p-4 bg-white rounded shadow">
                                        <label class="block font-medium text-gray-700 mb-2">Sugar:</label>
                                        <div class="flex items-center justify-center space-x-4">
                                            <div class="relative">
                                                <input type="radio" name="sugar_{{ $row->id }}" id="sugar-25-{{ $row->id }}" value="25" class="sr-only peer" onchange="updatePrice()">
                                                <label for="sugar-25-{{ $row->id }}" class="flex items-center justify-center px-3 py-1 border rounded-full w-12 h-12 text-center cursor-pointer peer-checked:bg-[#e17f12] peer-checked:text-white hover:bg-gray-50 lg:h-10 lg:w-10 lg:text-sm">25%</label>
                                            </div>
                                            <div class="relative">
                                                <input type="radio" name="sugar_{{ $row->id }}" id="sugar-50-{{ $row->id }}" value="50" class="sr-only peer" onchange="updatePrice()" checked>
                                                <label for="sugar-50-{{ $row->id }}" class="flex items-center justify-center px-3 py-1 border rounded-full w-12 h-12 text-center cursor-pointer peer-checked:bg-[#e17f12] peer-checked:text-white hover:bg-gray-50 lg:h-10 lg:w-10 lg:text-sm">50%</label>
                                            </div>
                                            <div class="relative">
                                                <input type="radio" name="sugar_{{ $row->id }}" id="sugar-75-{{ $row->id }}" value="75" class="sr-only peer" onchange="updatePrice()">
                                                <label for="sugar-75-{{ $row->id }}" class="flex items-center justify-center px-3 py-1 border rounded-full w-12 h-12 text-center cursor-pointer peer-checked:bg-[#e17f12] peer-checked:text-white hover:bg-gray-50 lg:h-10 lg:w-10 lg:text-sm">75%</label>
                                            </div>
                                        </div>
                                    </div>
                        
                                    <!-- Ice -->
                                    <div class="p-4 bg-white rounded shadow">
                                        <label class="block font-medium text-gray-700 mb-2">Ice:</label>
                                        <div class="flex items-center justify-center space-x-4">
                                            <div class="relative">
                                                <input type="radio" name="ice_{{ $row->id }}" id="ice-25-{{ $row->id }}" value="25" class="sr-only peer" onchange="updatePrice()">
                                                <label for="ice-25-{{ $row->id }}" class="flex items-center justify-center px-3 py-1 border rounded-full w-12 h-12 text-center cursor-pointer peer-checked:bg-[#e17f12] peer-checked:text-white hover:bg-gray-50 lg:h-10 lg:w-10 lg:text-sm">25%</label>
                                            </div>
                                            <div class="relative">
                                                <input type="radio" name="ice_{{ $row->id }}" id="ice-50-{{ $row->id }}" value="50" class="sr-only peer" onchange="updatePrice()" checked>
                                                <label for="ice-50-{{ $row->id }}" class="flex items-center justify-center px-3 py-1 border rounded-full w-12 h-12 text-center cursor-pointer peer-checked:bg-[#e17f12] peer-checked:text-white hover:bg-gray-50 lg:h-10 lg:w-10 lg:text-sm">50%</label>
                                            </div>
                                            <div class="relative">
                                                <input type="radio" name="ice_{{ $row->id }}" id="ice-75-{{ $row->id }}" value="75" class="sr-only peer" onchange="updatePrice()">
                                                <label for="ice-75-{{ $row->id }}" class="flex items-center justify-center px-3 py-1 border rounded-full w-12 h-12 text-center cursor-pointer peer-checked:bg-[#e17f12] peer-checked:text-white hover:bg-gray-50 lg:h-10 lg:w-10 lg:text-sm">75%</label>
                                            </div>
                                        </div>
                                    </div>
                        
                                    <!-- Add Total Price and Topping Section -->
                                    <div class="col-span-2 grid grid-cols-2 gap-4 mt-4">
                                        <!-- Topping Section -->
                                        <div class="p-4 bg-white rounded shadow">
                                            <label class="block font-medium text-gray-700 mb-2">Customized :</label>
                                            <div class="flex flex-col space-y-2">
                                                <div class="relative">
                                                    <input type="radio" name="topping_{{ $row->id }}" id="topping-none-{{ $row->id }}" value="No Topping" class="sr-only peer" onchange="updatePrice()" checked>
                                                    <label for="topping-none-{{ $row->id }}" 
                                                        class="block w-full px-4 py-2 border rounded cursor-pointer peer-checked:bg-[#e17f12] peer-checked:text-white hover:bg-gray-50 text-center lg:text-sm">
                                                        No Topping
                                                    </label>
                                                </div>
                                                <div class="relative">
                                                    <input type="radio" name="topping_{{ $row->id }}" id="topping-oat-{{ $row->id }}" value="Susu Oat" class="sr-only peer" onchange="updatePrice()">
                                                    <label for="topping-oat-{{ $row->id }}" 
                                                        class="block w-full px-4 py-2 border rounded cursor-pointer peer-checked:bg-[#e17f12] peer-checked:text-white hover:bg-gray-50 text-center lg:text-sm">
                                                        Susu Oat +(5K)
                                                    </label>
                                                </div>
                                                <div class="relative">
                                                    <input type="radio" name="topping_{{ $row->id }}" id="topping-espresso-{{ $row->id }}" value="Espresso" class="sr-only peer" onchange="updatePrice()">
                                                    <label for="topping-espresso-{{ $row->id }}" 
                                                        class="block w-full px-4 py-2 border rounded cursor-pointer peer-checked:bg-[#e17f12] peer-checked:text-white hover:bg-gray-50 text-center lg:text-sm">
                                                        Espresso +(4K)
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Total Price and Add to Order Section -->
                                        <div class="p-4 bg-white rounded shadow flex flex-col w-auto h-40">
                                            <div class="text-center">
                                                <label class="block font-medium text-gray-700 mb-2">Total Price:</label>
                                                <span id="total-price-{{ $row->id }}" class="text-2xl font-bold text-[#e17f12] block mb-3 lg:text-base"></span>
                                            </div>
                                            <button type="submit" class="w-full px-6 py-2 bg-[#005281] text-white rounded hover:bg-[#004371] transition-colors lg:text-sm">
                                                Add to Order
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                @endforeach
            </div>

            <!-- Add after the original products container, before the </section> tag (around line 437) -->
            <div id="card-view-container" style="display:none;">
                <div class="grid grid-cols-5 gap-4 p-4 xl:grid-cols-4 lg:grid-cols-3 md:grid-cols-2 sm:grid-cols-1">
                    @foreach ($products as $row)
                    <div class="product-card">
                        <div class="product-card-image">
                            <img src="{{ Storage::url($row->image_path) }}" alt="{{ $row->name }}">
                        </div>
                        <div class="product-card-details">
                            <h3 class="product-card-title">{{ $row->name }}</h3>
                            <div class="product-card-price">Rp {{ number_format($row->price, 0, ',', '.') }}</div>
                            
                            <!-- Quick order amount control -->
                            <div class="flex items-center justify-center my-3">
                                <button type="button" class="px-3 py-1 bg-gray-200 rounded-l" onclick="quickDecrementAmount(this)">-</button>
                                <span class="px-3 py-1 bg-white border-t border-b card-amount">1</span>
                                <button type="button" class="px-3 py-1 bg-gray-200 rounded-r" onclick="quickIncrementAmount(this)">+</button>
                            </div>
                            
                            <div class="product-card-action">
                                <button type="button" class="w-full px-4 py-2 bg-[#e17f12] text-white rounded hover:bg-[#d36f02] transition-colors" 
                                        onclick="quickAddToOrder('{{ $row->id }}', '{{ $row->name }}', {{ $row->price }}, {{ $row->cost_price }}, this)">
                                    Quick Order
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </section>

        <!-- Receipt Section -->
        <section class="w-1/6 bg-white p-6 shadow rounded font-mono text-sm receipt-section xl:w-1/6 lg:w-[20.83%]">
            <div class="text-center mb-4">
                <h1 class="text-xl font-bold">Pare Custom</h1>
                <p class="text-xs text-gray-500" id="current-date"></p>
                <p class="text-xs text-gray-500" id="cashier-name"></p>
            </div>

            <!-- Item List -->
            <div class="space-y-4 max-h-[500px] overflow-y-auto" id="item-list">
                <!-- Items will be dynamically added here -->
            </div>

            <hr class="border-dashed border-t-2 my-4">

            <!-- SubTotal -->
            <div class="flex justify-between text-lg font-bold text-green-600">
                <span class="lg:text-xs">Subtotal</span>
                <span id="subtotal" class="lg:text-xs">Rp. 0</span>
            </div>

            <hr class="border-dashed border-t-2 my-4">

            <!-- Hidden form for transaction -->
            <form id="transactionForm" method="POST" action="{{ route('owner.transaksitunai.store') }}" style="display: none;">
                @csrf
                <input type="hidden" name="subtotal" id="transaction_subtotal">
                <input type="hidden" name="total_cost_price" id="transaction_total_cost_price">
                <input type="hidden" name="name_user" id="transaction_name_user" value="{{ ucfirst(Auth::user()->name) }}">
                <input type="hidden" name="payment_method" id="transaction_payment_method" value="tunai">
                <input type="hidden" name="timestamp" id="transaction_timestamp">
            </form>

            <!-- Hidden form for best seller tracking -->
            <form id="bestSellerForm" method="POST" action="{{ route('owner.menu-best-sellers.store') }}" style="display: none;">
                @csrf
                <input type="hidden" name="product_ordered" id="best_seller_products">
            </form>

            <!-- Add this after the existing transactionForm -->
            <form id="transactionQRISForm" method="POST" action="{{ route('owner.transaksiqris.store') }}" style="display: none;">
                @csrf
                <input type="hidden" name="subtotal" id="transaction_qris_subtotal">
                <input type="hidden" name="total_cost_price" id="transaction_qris_total_cost_price">
                <input type="hidden" name="name_user" id="transaction_qris_name_user" value="{{ ucfirst(Auth::user()->name) }}">
                <input type="hidden" name="payment_method" id="transaction_qris_payment_method" value="qris">
                <input type="hidden" name="timestamp" id="transaction_qris_timestamp">
            </form>

            <!-- Buttons -->
            <div class="text-center space-y-2">
                <div class="text-center space-y-2 button-group">
                    <button onclick="processPayment()" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700 transition-colors">Pay</button>
                    <button onclick="processQRISPayment()" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition-colors">Pay with QRIS</button>
                    <button onclick="printReceipt()" class="w-full border border-gray-300 py-2 rounded hover:bg-gray-50 transition-colors">Print</button>
                    <button onclick="clearOrder()" class="w-full border border-red-300 text-red-600 py-2 rounded hover:bg-red-50 transition-colors">Clear Order</button>
                </div>
            </div>
        </section>

    </div>

    <script>
        function toggleDropdown(button) {
          const dropdownMenus = document.querySelectorAll(".dropdown-menu");
          const dropdownArrows = document.querySelectorAll("i.bi-chevron-down");
      
          // Close all dropdowns except the selected one
          dropdownMenus.forEach((menu) => {
            if (menu !== button.nextElementSibling) {
              menu.classList.add("max-h-0");
              menu.classList.remove("max-h-40");
            }
          });
      
          // Reset all arrows except the selected one
          dropdownArrows.forEach((arrow) => {
            if (arrow !== button.querySelector("i.bi-chevron-down")) {
              arrow.classList.remove("rotate-180");
            }
          });
      
          // Toggle the selected dropdown
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
    <script>
        // Set current date and cashier name when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const currentDate = new Date();
            document.getElementById('current-date').textContent = currentDate.toLocaleString('en-US', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
            document.getElementById('cashier-name').textContent = '{{ ucfirst(Auth::user()->name) }}';
            
            // Initialize price for all forms
            document.querySelectorAll('form[data-product-price]').forEach(form => {
                updatePrice.call(form);
            });

            // Add event listeners for manual input on amount fields
            document.querySelectorAll('input[name="amount"]').forEach(input => {
                input.addEventListener('input', function() {
                    // Ensure the value is a positive integer
                    let value = parseInt(this.value);
                    if (isNaN(value) || value < 1) {
                        value = 1;
                        this.value = 1;
                    }
                    
                    const form = this.closest('form');
                    updatePrice(form);
                });
            });
        });

        // Add event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Add event listeners for radio inputs
            document.querySelectorAll('form[data-product-id]').forEach(form => {
                const productId = form.getAttribute('data-product-id');
                
                // Add listeners for all radio inputs (size, sugar, ice, and topping)
                const radioGroups = ['size', 'sugar', 'ice', 'topping'];
                
                radioGroups.forEach(group => {
                    form.querySelectorAll(`input[name="${group}_${productId}"]`).forEach(input => {
                        input.addEventListener('change', function() {
                            updatePrice(form);
                        });
                    });
                });

                // Also add listener for amount changes
                form.querySelector('input[name="amount"]').addEventListener('input', function() {
                    updatePrice(form);
                });
            });
        });

        // Function to toggle dropdown
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

        // Function to increment amount
        function incrementAmount() {
            const button = event.target;
            const form = button.closest('form');
            let amountField = form.querySelector('input[name="amount"]');
            amountField.value = parseInt(amountField.value) + 1;
            updatePrice.call(form);
        }

        // Function to decrement amount
        function decrementAmount() {
            const button = event.target;
            const form = button.closest('form');
            let amountField = form.querySelector('input[name="amount"]');
            if (parseInt(amountField.value) > 1) {
                amountField.value = parseInt(amountField.value) - 1;
                updatePrice.call(form);
            }
        }

        // Function to format Rupiah
        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(number).replace('IDR', 'Rp').trim();
        }

        // Function to update price
        function updatePrice(form) {
            const formToUpdate = form || this.closest('form');
            if (!formToUpdate) return;

            const basePrice = parseInt(formToUpdate.getAttribute('data-product-price'));
            const amount = parseInt(formToUpdate.querySelector('input[name="amount"]').value);
            const productId = formToUpdate.getAttribute('data-product-id');
            
            // Use the product ID to find the correct radio buttons
            const size = formToUpdate.querySelector(`input[name="size_${productId}"]:checked`);
            const topping = formToUpdate.querySelector(`input[name="topping_${productId}"]:checked`);

            let itemPrice = basePrice;
            let customizations = [];

            if (size && size.value === 'L') {
                itemPrice += 3000;
                customizations.push('Size L (+3K)');
            }

            if (topping) {
                if (topping.value === 'Susu Oat') {
                    itemPrice += 5000;
                    customizations.push('Susu Oat (+5K)');
                } else if (topping.value === 'Espresso') {
                    itemPrice += 4000;
                    customizations.push('Espresso (+4K)');
                }
            }

            const totalPrice = itemPrice * amount;

            // Update to use product-specific total price element
            const totalPriceElement = formToUpdate.querySelector(`#total-price-${productId}`);
            if (totalPriceElement) {
                totalPriceElement.textContent = formatRupiah(totalPrice);
            }
            
            formToUpdate.setAttribute('data-base-price', basePrice);
            formToUpdate.setAttribute('data-item-price', itemPrice);
            formToUpdate.setAttribute('data-customizations', JSON.stringify(customizations));
        }

        // Function to add order
        function addToOrder(event) {
            event.preventDefault();

            const form = event.target;
            const productId = form.getAttribute('data-product-id');
            const productName = form.getAttribute('data-product-name');
            const basePrice = parseInt(form.getAttribute('data-base-price'));
            const itemPrice = parseInt(form.getAttribute('data-item-price'));
            const customizations = JSON.parse(form.getAttribute('data-customizations') || '[]');

            const size = form.querySelector(`input[name="size_${productId}"]:checked`);
            const sugar = form.querySelector(`input[name="sugar_${productId}"]:checked`);
            const ice = form.querySelector(`input[name="ice_${productId}"]:checked`);
            const topping = form.querySelector(`input[name="topping_${productId}"]:checked`);

            if (!size || !sugar || !ice || !topping) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Options',
                    text: 'Please select all options before adding to order',
                    confirmButtonColor: '#e17f12'
                });
                return;
            }

            const amount = parseInt(form.querySelector('input[name="amount"]').value);
            const totalPrice = itemPrice * amount;

            const item = document.createElement('div');
            item.className = 'flex flex-col border-b pb-2 mb-3';
            item.innerHTML = `
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="flex justify-between">
                            <span class="font-semibold lg:text-xs">${productName} x${amount}</span>
                        </div>
                        <ul class="ml-2 text-gray-600 text-xs mt-1">
                            <li>Size: ${size.value}</li>
                            <li>Sugar: ${sugar.value}%</li>
                            <li>Ice: ${ice.value}%</li>
                            <li>Topping: ${topping.value}</li>
                            <li>Base Price: ${formatRupiah(basePrice)}</li>
                            ${customizations.length > 0 ? 
                                `<li class="text-xs text-gray-500">Add-ons: ${customizations.join(', ')}</li>` : 
                                ''}
                            <li class="font-semibold lg:text-xs">Total: ${formatRupiah(totalPrice)}</li>
                        </ul>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" onclick="editItem(this)" 
                            class="text-blue-500 hover:text-blue-700 text-xs"
                            data-product-id="${productId}"
                            data-amount="${amount}"
                            data-size="${size.value}"
                            data-sugar="${sugar.value}"
                            data-ice="${ice.value}"
                            data-topping="${topping.value}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" onclick="removeItem(this)" class="text-red-500 hover:text-red-700 text-xs">×</button>
                    </div>
                </div>
            `;

            const itemList = document.getElementById('item-list');
            itemList.appendChild(item);

            updateSubtotal();

            // Reset form with correct IDs
            form.querySelector('input[name="amount"]').value = 1;
            form.querySelector(`#size-m-${productId}`).checked = true;
            form.querySelector(`#sugar-50-${productId}`).checked = true;
            form.querySelector(`#ice-50-${productId}`).checked = true;
            form.querySelector(`#topping-none-${productId}`).checked = true;
            
            // Update price after resetting
            updatePrice(form);

            // Show success message
            toastr.success('Item added to order successfully!', 'Success');
        }

        function editItem(button) {
            const item = button.closest('.flex.flex-col');
            const productId = button.getAttribute('data-product-id');
            const productForm = document.querySelector(`form[data-product-id="${productId}"]`);
            
            if (!productForm) {
                toastr.error('Product form not found!', 'Error');
                return;
            }

            // Get the current values from the receipt item
            const amount = button.getAttribute('data-amount');
            const size = button.getAttribute('data-size');
            const sugar = button.getAttribute('data-sugar');
            const ice = button.getAttribute('data-ice');
            const topping = button.getAttribute('data-topping');

            // Create a SweetAlert2 modal for editing
            Swal.fire({
                title: 'Edit Order',
                html: `
                    <div class="space-y-4">
                        <div class="flex flex-col">
                            <label class="text-sm font-medium text-gray-700 mb-1">Amount:</label>
                            <div class="flex items-center justify-center space-x-2">
                                <button type="button" class="px-3 py-1 bg-[#e17f12] rounded-full text-white" 
                                    onclick="this.nextElementSibling.stepDown()">-</button>
                                <input type="number" id="edit-amount" value="${amount}" min="1" 
                                    class="w-20 text-center border rounded">
                                <button type="button" class="px-3 py-1 bg-[#e17f12] rounded-full text-white" 
                                    onclick="this.previousElementSibling.stepUp()">+</button>
                            </div>
                        </div>
                        <div class="flex flex-col">
                            <label class="text-sm font-medium text-gray-700 mb-1">Size:</label>
                            <div class="flex justify-center space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="edit-size" value="M" ${size === 'M' ? 'checked' : ''} 
                                        class="text-[#e17f12]">
                                    <span class="ml-2">M</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="edit-size" value="L" ${size === 'L' ? 'checked' : ''} 
                                        class="text-[#e17f12]">
                                    <span class="ml-2">L</span>
                                </label>
                            </div>
                        </div>
                        <div class="flex flex-col">
                            <label class="text-sm font-medium text-gray-700 mb-1">Sugar Level:</label>
                            <div class="flex justify-center space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="edit-sugar" value="25" ${sugar === '25' ? 'checked' : ''} 
                                        class="text-[#e17f12]">
                                    <span class="ml-2">25%</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="edit-sugar" value="50" ${sugar === '50' ? 'checked' : ''} 
                                        class="text-[#e17f12]">
                                    <span class="ml-2">50%</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="edit-sugar" value="75" ${sugar === '75' ? 'checked' : ''} 
                                        class="text-[#e17f12]">
                                    <span class="ml-2">75%</span>
                                </label>
                            </div>
                        </div>
                        <div class="flex flex-col">
                            <label class="text-sm font-medium text-gray-700 mb-1">Ice Level:</label>
                            <div class="flex justify-center space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="edit-ice" value="25" ${ice === '25' ? 'checked' : ''} 
                                        class="text-[#e17f12]">
                                    <span class="ml-2">25%</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="edit-ice" value="50" ${ice === '50' ? 'checked' : ''} 
                                        class="text-[#e17f12]">
                                    <span class="ml-2">50%</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="edit-ice" value="75" ${ice === '75' ? 'checked' : ''} 
                                        class="text-[#e17f12]">
                                    <span class="ml-2">75%</span>
                                </label>
                            </div>
                        </div>
                        <div class="flex flex-col">
                            <label class="text-sm font-medium text-gray-700 mb-1">Topping:</label>
                            <div class="flex flex-col space-y-2">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="edit-topping" value="No Topping" 
                                        ${topping === 'No Topping' ? 'checked' : ''} class="text-[#e17f12]">
                                    <span class="ml-2">No Topping</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="edit-topping" value="Susu Oat" 
                                        ${topping === 'Susu Oat' ? 'checked' : ''} class="text-[#e17f12]">
                                    <span class="ml-2">Susu Oat (+5K)</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="edit-topping" value="Espresso" 
                                        ${topping === 'Espresso' ? 'checked' : ''} class="text-[#e17f12]">
                                    <span class="ml-2">Espresso (+4K)</span>
                                </label>
                            </div>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Update',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#e17f12',
                cancelButtonColor: '#d33',
                preConfirm: () => {
                    return {
                        amount: document.getElementById('edit-amount').value,
                        size: document.querySelector('input[name="edit-size"]:checked').value,
                        sugar: document.querySelector('input[name="edit-sugar"]:checked').value,
                        ice: document.querySelector('input[name="edit-ice"]:checked').value,
                        topping: document.querySelector('input[name="edit-topping"]:checked').value
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Update the form with new values
                    const newValues = result.value;
                    
                    // Set the amount in the original form
                    productForm.querySelector('input[name="amount"]').value = newValues.amount;
                    
                    // Set size
                    productForm.querySelector(`input[name="size_${productId}"][value="${newValues.size}"]`).checked = true;
                    
                    // Set sugar
                    productForm.querySelector(`input[name="sugar_${productId}"][value="${newValues.sugar}"]`).checked = true;
                    
                    // Set ice
                    productForm.querySelector(`input[name="ice_${productId}"][value="${newValues.ice}"]`).checked = true;
                    
                    // Set topping
                    productForm.querySelector(`input[name="topping_${productId}"][value="${newValues.topping}"]`).checked = true;
                    
                    // Update the price
                    updatePrice(productForm);
                    
                    // Remove the old item
                    item.remove();
                    
                    // Add the updated item
                    const event = new Event('submit');
                    productForm.dispatchEvent(event);
                    
                    // Show success message
                    toastr.success('Order updated successfully!', 'Success');
                    
                    // Update the subtotal
                    updateSubtotal();
                }
            });
        }

        // Function to remove item
        function removeItem(button) {
            const item = button.closest('.flex.flex-col');
            item.remove();
            updateSubtotal();
        }

        // Function to update subtotal
        function updateSubtotal() {
            const itemList = document.getElementById('item-list');
            const prices = itemList.querySelectorAll('.text-xs.mt-1 li:last-child');
            let subtotal = 0;

            prices.forEach(price => {
                const priceText = price.textContent;
                const priceNumber = parseInt(priceText.split(': ')[1].replace(/[^\d]/g, ''));
                subtotal += priceNumber;
            });

            document.getElementById('subtotal').textContent = formatRupiah(subtotal);
        }

            // Function to process best seller data
            async function processBestSellerData() {
                const itemList = document.getElementById('item-list');
                const items = itemList.querySelectorAll('.flex.flex-col');
                let orderedProducts = [];
                
                items.forEach(item => {
                    const productNameElement = item.querySelector('.font-semibold');
                    if (productNameElement) {
                        const fullText = productNameElement.textContent;
                        const productName = fullText.split(' x')[0];
                        const quantity = parseInt(fullText.split('x')[1]);
                        
                        // Add the product name to the array as many times as it was ordered
                        for (let i = 0; i < quantity; i++) {
                            orderedProducts.push(productName);
                        }
                    }
                });

                // Join the products with commas
                const productsString = orderedProducts.join(', ');
                
                // Set the value in the hidden form
                document.getElementById('best_seller_products').value = productsString;
            }

            // Updated processPayment function
            async function processPayment() {
                const subtotalElement = document.getElementById('subtotal');
                const subtotalText = subtotalElement.textContent;
                
                if (subtotalText === 'Rp 0') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Empty Order',
                        text: 'Please add items to your order before proceeding to payment',
                        confirmButtonColor: '#e17f12'
                    });
                    return;
                }

                // Calculate total cost price and set up forms
                let totalCostPrice = 0;
                const itemList = document.getElementById('item-list');
                const items = itemList.querySelectorAll('.flex.flex-col');
                
                items.forEach(item => {
                    const productName = item.querySelector('.font-semibold').textContent.split(' x')[0];
                    const quantity = parseInt(item.querySelector('.font-semibold').textContent.split('x')[1]);
                    const form = document.querySelector(`form[data-product-name="${productName}"]`);
                    const baseCostPrice = parseFloat(form.getAttribute('data-cost-price'));
                    
                    let itemCostPrice = baseCostPrice;
                    const size = item.querySelector('li:nth-child(1)').textContent.split(': ')[1];
                    const topping = item.querySelector('li:nth-child(4)').textContent.split(': ')[1];

                    if (size === 'L') {
                        itemCostPrice += 1500;
                    }
                    if (topping === 'Susu Oat') {
                        itemCostPrice += 2500;
                    } else if (topping === 'Espresso') {
                        itemCostPrice += 2000;
                    }

                    totalCostPrice += (itemCostPrice * quantity);
                });

                const now = new Date();
                const timestamp = now.getFullYear() + '-' + 
                                String(now.getMonth() + 1).padStart(2, '0') + '-' + 
                                String(now.getDate()).padStart(2, '0') + ' ' + 
                                String(now.getHours()).padStart(2, '0') + ':' + 
                                String(now.getMinutes()).padStart(2, '0') + ':' + 
                                String(now.getSeconds()).padStart(2, '0');

                // Show confirmation dialog
                const { isConfirmed } = await Swal.fire({
                    title: 'Confirm Payment',
                    text: `Confirm payment for ${subtotalText}?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#e17f12',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, process payment',
                    cancelButtonText: 'Cancel'
                });

                if (isConfirmed) {
                    // Prepare both forms
                    const subtotal = parseFloat(subtotalText.replace(/[^\d]/g, ''));
                    document.getElementById('transaction_subtotal').value = subtotal;
                    document.getElementById('transaction_total_cost_price').value = totalCostPrice.toFixed(2);
                    document.getElementById('transaction_timestamp').value = timestamp;
                    document.getElementById('transaction_payment_method').value = 'tunai';

                    // Prepare best seller data
                    await processBestSellerData();

                    // Show loading state
                    Swal.fire({
                        title: 'Processing Payment',
                        text: 'Please wait...',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        willOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {
                        // Create form data for both forms
                        const transactionFormData = new FormData(document.getElementById('transactionForm'));
                        const bestSellerFormData = new FormData(document.getElementById('bestSellerForm'));

                        // Submit both forms using fetch
                        const responses = await Promise.all([
                            fetch(document.getElementById('transactionForm').action, {
                                method: 'POST',
                                body: transactionFormData
                            }),
                            fetch(document.getElementById('bestSellerForm').action, {
                                method: 'POST',
                                body: bestSellerFormData
                            })
                        ]);

                        // Check if both submissions were successful
                        const allSuccessful = responses.every(response => response.ok);

                        if (allSuccessful) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Payment Successful',
                                text: 'Your transaction has been processed successfully!',
                                confirmButtonColor: '#e17f12'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.reload();
                                }
                            });
                        } else {
                            throw new Error('One or more submissions failed');
                        }
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Payment Failed',
                            text: 'There was an error processing your payment. Please try again.',
                            confirmButtonColor: '#e17f12'
                        });
                    }
                }
            }

            async function processQRISPayment() {
                const subtotalElement = document.getElementById('subtotal');
                const subtotalText = subtotalElement.textContent;
                
                if (subtotalText === 'Rp 0') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Empty Order',
                        text: 'Please add items to your order before proceeding to payment',
                        confirmButtonColor: '#e17f12'
                    });
                    return;
                }

                // Show QRIS code first
                const { isConfirmed: scanConfirmed } = await Swal.fire({
                    title: 'Scan QRIS Code',
                    html: `
                        <div class="flex flex-col items-center">
                            <img src="{{ asset('assets/qris-bblara.png') }}" 
                                alt="QRIS Code" 
                                class="w-64 h-64 object-contain mb-4">
                            <p class="text-sm text-gray-600">Please scan the QRIS code above to make your payment</p>
                            <p class="font-bold mt-2">Total: ${subtotalText}</p>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'I have completed the payment',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#e17f12',
                    cancelButtonColor: '#d33',
                    width: 'auto'
                });

                if (!scanConfirmed) {
                    return;
                }

                // Calculate total cost price
                let totalCostPrice = 0;
                const itemList = document.getElementById('item-list');
                const items = itemList.querySelectorAll('.flex.flex-col');
                
                items.forEach(item => {
                    const productName = item.querySelector('.font-semibold').textContent.split(' x')[0];
                    const quantity = parseInt(item.querySelector('.font-semibold').textContent.split('x')[1]);
                    const form = document.querySelector(`form[data-product-name="${productName}"]`);
                    const baseCostPrice = parseFloat(form.getAttribute('data-cost-price'));
                    
                    let itemCostPrice = baseCostPrice;
                    const size = item.querySelector('li:nth-child(1)').textContent.split(': ')[1];
                    const topping = item.querySelector('li:nth-child(4)').textContent.split(': ')[1];

                    if (size === 'L') {
                        itemCostPrice += 1500;
                    }
                    if (topping === 'Susu Oat') {
                        itemCostPrice += 2500;
                    } else if (topping === 'Espresso') {
                        itemCostPrice += 2000;
                    }

                    totalCostPrice += (itemCostPrice * quantity);
                });

                const now = new Date();
                const timestamp = now.getFullYear() + '-' + 
                                String(now.getMonth() + 1).padStart(2, '0') + '-' + 
                                String(now.getDate()).padStart(2, '0') + ' ' + 
                                String(now.getHours()).padStart(2, '0') + ':' + 
                                String(now.getMinutes()).padStart(2, '0') + ':' + 
                                String(now.getSeconds()).padStart(2, '0');

                // Show final confirmation dialog
                const { isConfirmed } = await Swal.fire({
                    title: 'Confirm QRIS Payment',
                    text: `Confirm that you have completed the QRIS payment for ${subtotalText}?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#e17f12',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, payment completed',
                    cancelButtonText: 'Cancel'
                });

                if (isConfirmed) {
                    // Prepare both forms
                    const subtotal = parseFloat(subtotalText.replace(/[^\d]/g, ''));
                    document.getElementById('transaction_qris_subtotal').value = subtotal;
                    document.getElementById('transaction_qris_total_cost_price').value = totalCostPrice.toFixed(2);
                    document.getElementById('transaction_qris_timestamp').value = timestamp;

                    // Prepare best seller data
                    await processBestSellerData();

                    // Show loading state
                    Swal.fire({
                        title: 'Processing QRIS Payment',
                        text: 'Please wait...',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        willOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {
                        // Create form data for both forms
                        const qrisFormData = new FormData(document.getElementById('transactionQRISForm'));
                        const bestSellerFormData = new FormData(document.getElementById('bestSellerForm'));

                        // Submit both forms using fetch
                        const responses = await Promise.all([
                            fetch(document.getElementById('transactionQRISForm').action, {
                                method: 'POST',
                                body: qrisFormData
                            }),
                            fetch(document.getElementById('bestSellerForm').action, {
                                method: 'POST',
                                body: bestSellerFormData
                            })
                        ]);

                        // Check if both submissions were successful
                        const allSuccessful = responses.every(response => response.ok);

                        if (allSuccessful) {
                            Swal.fire({
                                icon: 'success',
                                title: 'QRIS Payment Successful',
                                text: 'Your QRIS transaction has been processed successfully!',
                                confirmButtonColor: '#e17f12'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.reload();
                                }
                            });
                        } else {
                            throw new Error('One or more submissions failed');
                        }
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'QRIS Payment Failed',
                            text: 'There was an error processing your QRIS payment. Please try again.',
                            confirmButtonColor: '#e17f12'
                        });
                    }
                }
            }

        // Function to print receipt
        function printReceipt() {
        // Create a new hidden iframe
        const iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        document.body.appendChild(iframe);
        
        // Get the receipt content
        const receiptSection = document.querySelector('.receipt-section');
        const content = receiptSection.cloneNode(true);
        
        // Remove any edit/remove buttons from the clone
        content.querySelectorAll('.button-group, button').forEach(el => el.remove());
        
        // Write the content to the iframe
        iframe.contentDocument.write(`
            <html>
                <head>
                    <style>
                        @page {
                            margin: 0;
                            size: 58mm auto;
                        }
                        body {
                            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
                            width: 58mm;
                            margin: 0;
                            padding: 3mm;
                        }
                        * {
                            color: black !important;
                            background: none !important;
                        }
                        hr {
                            border: none;
                            border-top: 1px dashed black;
                            margin: 2mm 0;
                        }
                        h1 {
                            font-size: 16px;
                            text-align: center;
                            margin-bottom: 3mm;
                        }
                        #current-date, #cashier-name {
                            font-size: 12px;
                            text-align: center;
                        }
                        ul {
                            margin: 1mm 0;
                            padding-left: 2mm;
                            font-size: 12px;
                        }
                    </style>
                </head>
                <body>
                    ${content.innerHTML}
                </body>
            </html>
        `);
        
        // Print the iframe
        iframe.contentWindow.print();
        
        // Remove the iframe after printing
        setTimeout(() => {
            document.body.removeChild(iframe);
        }, 1000);
    }

        // Function to clear order
        function clearOrder() {
            const itemList = document.getElementById('item-list');
            if (itemList.children.length === 0) {
                alert('Order is already empty');
                return;
            }
            if (confirm('Are you sure you want to clear the entire order?')) {
                itemList.innerHTML = '';
                updateSubtotal();
            }
        }

        // Add event listeners
        document.querySelectorAll('input[name="size"], input[name="topping"]').forEach(input => {
            input.addEventListener('change', function() {
                updatePrice.call(this.closest('form'));
            });
        });

        // Search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const clearButton = document.getElementById('clearSearch');
            const productForms = document.querySelectorAll('form[data-product-name]');

            function filterProducts(searchTerm) {
                searchTerm = searchTerm.toLowerCase().trim();
                
                productForms.forEach(form => {
                    const productName = form.getAttribute('data-product-name').toLowerCase();
                    const productCard = form.closest('.p-6');
                    
                    if (productName.includes(searchTerm)) {
                        productCard.style.display = '';
                        // Highlight matching text if there's a search term
                        const titleElement = form.querySelector('h2');
                        if (searchTerm !== '') {
                            const regex = new RegExp(`(${searchTerm})`, 'gi');
                            const originalText = titleElement.textContent;
                            titleElement.innerHTML = originalText.replace(
                                regex, 
                                '<span class="bg-yellow-200">$1</span>'
                            );
                        } else {
                            // Remove highlighting if search is cleared
                            titleElement.innerHTML = titleElement.textContent;
                        }
                    } else {
                        productCard.style.display = 'none';
                    }
                });
            }

            function clearSearchInput() {
                searchInput.value = '';
                clearButton.classList.add('hidden');
                filterProducts('');
                searchInput.focus();
            }

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value;
                filterProducts(searchTerm);
                
                // Toggle clear button visibility
                if (searchTerm) {
                    clearButton.classList.remove('hidden');
                } else {
                    clearButton.classList.add('hidden');
                }
            });

            // Clear search when pressing Escape key
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    clearSearchInput();
                }
            });

            // Make clearSearchInput function globally available
            window.clearSearchInput = clearSearchInput;
        });
    </script>
    <script>
        // Konfigurasi default Toastr
        document.addEventListener('DOMContentLoaded', function() {
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": false,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };
        });

        // Function to clear order with Toastr
        function clearOrder() {
            const itemList = document.getElementById('item-list');
            if (itemList.children.length === 0) {
                toastr.warning('Order is already empty!', 'Warning');
                return;
            }

            // Show confirmation using SweetAlert2
            Swal.fire({
                title: 'Clear Order?',
                text: 'Are you sure you want to clear the entire order?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e17f12',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, clear it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    itemList.innerHTML = '';
                    updateSubtotal();
                    
                    // Show success message with Toastr
                    toastr.success('Order has been cleared successfully!', 'Success');
                }
            });
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get unique product names/categories from the forms
            const products = Array.from(document.querySelectorAll('form[data-product-name]')).map(form => {
                return form.getAttribute('data-product-name');
            });

            // Get unique names and sort them alphabetically
            const uniqueProducts = [...new Set(products)].sort();

            // Get the filter menu container
            const filterMenu = document.getElementById('filterMenu');
            
            // Add filter buttons for each unique product
            uniqueProducts.forEach(product => {
                const button = document.createElement('button');
                button.className = 'filter-btn';
                button.setAttribute('data-category', product);
                button.textContent = product;
                filterMenu.appendChild(button);
            });

            // Scroll functionality
            const scrollLeftBtn = document.getElementById('scrollLeft');
            const scrollRightBtn = document.getElementById('scrollRight');
            const filterContainer = document.getElementById('filterContainer');

            let scrollPosition = 0;
            const scrollAmount = 200;

            function updateScrollButtons() {
                const canScrollLeft = scrollPosition > 0;
                const canScrollRight = scrollPosition < filterMenu.scrollWidth - filterContainer.clientWidth;

                scrollLeftBtn.style.display = canScrollLeft ? 'block' : 'none';
                scrollRightBtn.style.display = canScrollRight ? 'block' : 'none';

                // Update scroll shadows
                filterContainer.classList.toggle('scroll-shadow-left', canScrollLeft);
                filterContainer.classList.toggle('scroll-shadow-right', canScrollRight);
            }

            function smoothScroll(target) {
                const start = scrollPosition;
                const change = target - start;
                const duration = 300;
                let startTime = null;

                function animation(currentTime) {
                    if (startTime === null) startTime = currentTime;
                    const timeElapsed = currentTime - startTime;
                    const progress = Math.min(timeElapsed / duration, 1);

                    // Easing function
                    const easeProgress = 0.5 * (1 - Math.cos(Math.PI * progress));
                    
                    scrollPosition = start + (change * easeProgress);
                    filterMenu.style.transform = `translateX(-${scrollPosition}px)`;

                    if (timeElapsed < duration) {
                        requestAnimationFrame(animation);
                    } else {
                        updateScrollButtons();
                    }
                }

                requestAnimationFrame(animation);
            }

            scrollLeftBtn.addEventListener('click', () => {
                const target = Math.max(scrollPosition - scrollAmount, 0);
                smoothScroll(target);
            });

            scrollRightBtn.addEventListener('click', () => {
                const maxScroll = filterMenu.scrollWidth - filterContainer.clientWidth;
                const target = Math.min(scrollPosition + scrollAmount, maxScroll);
                smoothScroll(target);
            });

            // Filter functionality
            const filterButtons = document.querySelectorAll('.filter-btn');
            filterButtons.forEach(button => {
                button.addEventListener('click', () => {
                    // Remove active class from all buttons
                    filterButtons.forEach(btn => btn.classList.remove('active'));

                    // Add active class to clicked button
                    button.classList.add('active');

                    const category = button.getAttribute('data-category');
                    const productForms = document.querySelectorAll('form[data-product-name]');

                    productForms.forEach(form => {
                        const productName = form.getAttribute('data-product-name');
                        const productCard = form.closest('.p-6');

                        if (category === 'all' || productName === category) {
                            productCard.style.display = '';
                        } else {
                            productCard.style.display = 'none';
                        }
                    });
                });
            });

            // Initial setup
            updateScrollButtons();

            // Update on window resize
            let resizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    updateScrollButtons();
                }, 100);
            });

            // Set initial active state
            filterButtons[0].classList.add('active');
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const viewToggle = document.getElementById('view-toggle');
            const productsContainer = document.getElementById('products-container');
            const cardViewContainer = document.getElementById('card-view-container');
            
            // Load saved view mode state
            const savedViewMode = localStorage.getItem('viewMode');
            if (savedViewMode === 'card') {
                viewToggle.checked = true;
                productsContainer.style.display = 'none';
                cardViewContainer.style.display = 'block';
            }
            
            // Add event listener for toggle changes
            viewToggle.addEventListener('change', function() {
                if (this.checked) {
                    // Card view
                    productsContainer.style.display = 'none';
                    cardViewContainer.style.display = 'block';
                    localStorage.setItem('viewMode', 'card');
                } else {
                    // Detailed view
                    productsContainer.style.display = 'block';
                    cardViewContainer.style.display = 'none';
                    localStorage.setItem('viewMode', 'detailed');
                }
            });
        });
        
        // Function to increment amount in card view
        function quickIncrementAmount(button) {
            const amountSpan = button.previousElementSibling;
            let amount = parseInt(amountSpan.textContent);
            amountSpan.textContent = amount + 1;
        }
        
        // Function to decrement amount in card view
        function quickDecrementAmount(button) {
            const amountSpan = button.nextElementSibling;
            let amount = parseInt(amountSpan.textContent);
            if (amount > 1) {
                amountSpan.textContent = amount - 1;
            }
        }
        
        // Function to quickly add items to order with default values
        function quickAddToOrder(productId, productName, basePrice, costPrice, button) {
            // Get the amount from the card
            const cardElement = button.closest('.product-card');
            const amount = parseInt(cardElement.querySelector('.card-amount').textContent);
            
            // Create an item with default values (Medium size, 50% sugar, 50% ice, No Topping)
            const item = document.createElement('div');
            item.className = 'flex flex-col border-b pb-2 mb-3';
            item.innerHTML = `
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="flex justify-between">
                            <span class="font-semibold lg:text-xs">${productName} x${amount}</span>
                        </div>
                        <ul class="ml-2 text-gray-600 text-xs mt-1">
                            <li>Size: M</li>
                            <li>Sugar: 50%</li>
                            <li>Ice: 50%</li>
                            <li>Topping: No Topping</li>
                            <li>Base Price: ${formatRupiah(basePrice)}</li>
                            <li class="font-semibold lg:text-xs">Total: ${formatRupiah(basePrice * amount)}</li>
                        </ul>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" onclick="removeItem(this)" class="text-red-500 hover:text-red-700 text-xs">×</button>
                    </div>
                </div>
            `;
            
            // Add to order
            const itemList = document.getElementById('item-list');
            itemList.appendChild(item);
            
            // Reset the amount to 1
            cardElement.querySelector('.card-amount').textContent = '1';
            
            // Update subtotal
            updateSubtotal();
            
            // Show success message
            toastr.success('Item added to order successfully!', 'Success');
        }
    </script>

</body>
</html>