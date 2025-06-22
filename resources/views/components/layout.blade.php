<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>send.lee.io | {{ $section }}</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .drag-drop-zone {
            transition: background-color 0.3s;
        }

        .drag-drop-zone.dragover {
            background-color: #e0f2fe;
        }
    </style>
</head>

<body class="flex flex-col min-h-screen bg-gray-100">
    <!-- Fixed Navigation Bar -->
    <nav class="bg-white shadow-md w-full fixed top-0 left-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <a href="/">
                        <h1 class="text-xl font-bold text-gray-800">File Upload</h1>
                    </a>
                </div>
                <!-- Burger menu for mobile view -->
                <div class="flex md:hidden">
                    <button id="menu-toggle" class="text-gray-500 focus:outline-none focus:text-gray-800">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16m-7 6h7" />
                        </svg>
                    </button>
                </div>
                <!-- Menu items for desktop view -->
                <div class="hidden md:flex space-x-4">
                    <a href="/sessions" class="text-gray-700 hover:text-blue-500">Upload Sessions</a>
                    <a href="/logout" class="text-gray-700 hover:text-blue-500">Logout</a>
                </div>
            </div>
        </div>
        <!-- Dropdown menu for mobile view -->
        <div id="mobile-menu" class="md:hidden hidden">
            <a href="/sessions" class="block px-4 py-2 text-gray-700 hover:bg-gray-200 w-full text-left">Upload
                Sessions</a>
            <a href="/logout" class="block px-4 py-2 text-gray-700 hover:bg-gray-200 w-full text-left">Logout</a>
        </div>
    </nav>
    {{ $slot }}
    <script>
        const menuToggle = document.getElementById("menu-toggle");
        const mobileMenu = document.getElementById("mobile-menu");
        // Toggle mobile menu visibility
        menuToggle.addEventListener("click", () => {
            mobileMenu.classList.toggle("hidden");
        });
    </script>
</body>

</html>