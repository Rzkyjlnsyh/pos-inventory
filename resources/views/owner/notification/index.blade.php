<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Notifications - Bblara</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" />
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet">
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
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex">
        <!-- Toggle Button for Sidebar -->
        <button class="fixed text-white text-3xl top-5 left-4 p-2 rounded-md bg-gray-700 lg:hidden focus:outline-none z-50" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>

        <!-- Sidebar -->
        <x-navbar-owner></x-navbar-owner>

        <!-- Main Content -->
        <div class="flex-1 lg:w-5/6">
            {{-- Navbar Top --}}
            <x-navbar-top-owner></x-navbar-top-owner>

            <!-- Content Wrapper -->
            <div class="p-4 lg:px-8 py-0">
                <div class="p-6 bg-gray-100 min-h-screen">
                    <div class="max-w-7xl mx-auto">
                        <div class="max-w-4xl mx-auto py-8">
                            <div class="bg-white shadow-md rounded-lg p-4">
                                <div class="flex justify-between items-center mb-4">
                                    <h2 class="text-xl font-semibold">Notifications</h2>
                                    <button id="clear-all-btn" class="bg-red-500 text-white px-4 py-2 rounded">Clear All</button>
                                </div>
                                @if($notifications->isEmpty())
                                    <p>No notifications found.</p>
                                @else
                                    <div id="notifications-container">
                                        @include('owner.notification.partials.notifications', ['notifications' => $notifications])
                                    </div>
                                @endif
                                @if ($notifications->hasMorePages())
                                    <div class="text-center mt-4">
                                        <button id="load-more-btn" 
                                                class="bg-gray-200 text-gray-700 px-4 py-2 rounded" 
                                                data-next-page="{{ $notifications->nextPageUrl() }}">
                                            Load more
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Global variable to prevent multiple simultaneous loads
        let loading = false;

        // Sidebar toggle function
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('hidden');
        }

        // Delete modal toggle function
        function toggleDeleteModal() {
            const modal = document.getElementById('deleteModal');
            if (modal.classList.contains('hidden')) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } else {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }

        // Clear all notifications function
        document.getElementById('clear-all-btn')?.addEventListener('click', function () {
            if (confirm('Are you sure you want to clear all notifications?')) {
                fetch('/owner/notifications/clear-all', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to clear notifications.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to clear notifications.');
                });
            }
        });

        // Updated Load More function
        function loadMore() {
            // Prevent multiple simultaneous loads
            if (loading) return;
            
            const loadMoreBtn = document.getElementById('load-more-btn');
            // Get the next page URL from the button's data attribute
            const nextPageUrl = loadMoreBtn.getAttribute('data-next-page') || loadMoreBtn.dataset.nextPage;
            
            if (!nextPageUrl) {
                loadMoreBtn.remove();
                return;
            }
            
            // Set loading state
            loading = true;
            loadMoreBtn.textContent = 'Loading...';
            
            // Make the fetch request
            fetch(nextPageUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                const notificationsContainer = document.getElementById('notifications-container');
                notificationsContainer.insertAdjacentHTML('beforeend', data.notifications);
                
                if (data.hasMorePages && data.nextPageUrl) {
                    loadMoreBtn.setAttribute('data-next-page', data.nextPageUrl);
                } else {
                    loadMoreBtn.remove();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to load more notifications.');
            })
            .finally(() => {
                loading = false;
                if (loadMoreBtn && document.contains(loadMoreBtn)) {
                    loadMoreBtn.textContent = 'Load more';
                }
            });
        }

        // Attach event listener to load more button
        document.getElementById('load-more-btn')?.addEventListener('click', loadMore);

        // Dropdown toggle function
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