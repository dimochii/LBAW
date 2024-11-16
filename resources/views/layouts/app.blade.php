<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'whatsup-green': '#A6B37D',
                        'whatsup-red': '#C96868',
                        'whatsup-blue': '#7EACB5'
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full">
    <div class="min-h-screen flex flex-col">
        <!-- Top Header -->
        <header class="flex items-center justify-between bg-whatsup-green h-12">
            <!-- Logo Section -->
            <div class="flex items-center">
                <a href="{{ url('/') }}" class="px-4 text-white font-semibold text-xl">
                    whatsUP
                </a>
            </div>

            <!-- Search Section -->
            <div class="flex-1 bg-whatsup-red h-12 flex items-center px-4">
                <svg class="w-5 h-5 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input 
                    type="text" 
                    placeholder="search" 
                    class="w-full bg-transparent border-none text-white placeholder-white/80 px-3 py-2 focus:outline-none text-sm"
                >
            </div>

            <!-- Right Section -->
            <div class="bg-whatsup-blue h-12 px-4 flex items-center space-x-4">
                <button class="text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </button>
                <button class="text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </button>
                @if (Auth::check())
                    <a href="#" class="w-8 h-8 rounded-full overflow-hidden">
                        <img src="/api/placeholder/32/32" alt="Profile" class="w-full h-full object-cover">
                    </a>
                @endif
            </div>
        </header>

        <div class="flex flex-1">
            <!-- Sidebar -->
            <aside class="w-48 bg-gray-50 border-r">
                <nav class="py-4">
                    <!-- Primary Links -->
                    <div class="space-y-1">
                        <a href="{{ url('/home') }}" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
                            <span class="text-sm">home</span>
                        </a>
                        <a href="#" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
                            <span class="text-sm">global</span>
                        </a>
                        <a href="#" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
                            <span class="text-sm">recent</span>
                        </a>
                    </div>

                    <!-- Recent Section -->
                    <div class="mt-6">
                        <h3 class="px-4 text-xs font-medium text-gray-500 mb-2">recent</h3>
                        <div class="space-y-1">
                            <a href="#" class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <div class="w-2 h-2 rounded-full bg-green-500"></div>
                                <span class="text-sm">/Economics</span>
                            </a>
                            <a href="#" class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                <span class="text-sm">/Sports</span>
                            </a>
                            <a href="#" class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <div class="w-2 h-2 rounded-full bg-red-500"></div>
                                <span class="text-sm">/Portugal</span>
                            </a>
                            <a href="#" class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <div class="w-2 h-2 rounded-full bg-yellow-500"></div>
                                <span class="text-sm">/Finances</span>
                            </a>
                        </div>
                    </div>

                    <!-- Hubs Section -->
                    <div class="mt-6">
                        <h3 class="px-4 text-xs font-medium text-gray-500 mb-2">hubs</h3>
                        <div class="space-y-1">
                            <a href="#" class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <div class="w-2 h-2 rounded-full bg-green-500"></div>
                                <span class="text-sm">/Economics</span>
                            </a>
                            <a href="#" class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                <span class="text-sm">/Sports</span>
                            </a>
                        </div>
                    </div>

                    <!-- Info Section -->
                    <div class="mt-6">
                        <h3 class="px-4 text-xs font-medium text-gray-500 mb-2">info</h3>
                        <div class="space-y-1">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                about us
                            </a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                help
                            </a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                best of
                            </a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                hubs
                            </a>
                        </div>
                    </div>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 p-6 bg-white">
                <section id="content">
                    @yield('content')
                </section>
            </main>
        </div>
    </div>
</body>
</html>