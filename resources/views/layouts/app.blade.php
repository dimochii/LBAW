<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <script type="text/javascript">
        // Fix for Firefox autofocus CSS bug
        // See: http://stackoverflow.com/questions/18943276/html-5-autofocus-messes-up-css-loading/18945951#18945951
    </script>
    <script type="text/javascript" src={{ url('js/app.js') }} defer></script>
</head>
<body class="h-full">
    <div class="min-h-screen bg-gray-100">
        <!-- Top Header -->
        <header class="bg-emerald-50 border-b border-emerald-100">
            <div class="flex items-center justify-between px-4 py-2">
                <div class="flex items-center space-x-2 flex-1">
                    <!-- Logo -->
                    <h1 class="text-emerald-700 font-medium m-0">
                        <a href="{{ url('/cards') }}" class="text-emerald-700 no-underline hover:text-emerald-800">
                            Thingy!
                        </a>
                    </h1>
                    <!-- Search Bar -->
                    <div class="max-w-xl flex-1 relative">
                        <input 
                            type="text" 
                            placeholder="search" 
                            class="w-full px-4 py-1.5 pl-10 rounded-md border border-gray-200 text-sm focus:outline-none focus:border-emerald-500"
                        >
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
                <!-- User Section -->
                <div class="flex items-center space-x-4">
                    @if (Auth::check())
                        <span class="text-gray-700">{{ Auth::user()->name }}</span>
                        <a href="{{ url('/logout') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-md no-underline">
                            Logout
                        </a>
                    @endif
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <div class="flex">
            <!-- Sidebar -->
            <aside class="w-48 bg-white min-h-screen shadow-lg">
                <nav class="py-4">
                    <!-- Primary Links -->
                    <div class="px-3 space-y-1">
                        <a href="{{ url('/cards') }}" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 {{ Request::is('cards') ? 'bg-gray-100' : 'hover:bg-gray-50' }}">
                            <span class="text-sm">Cards</span>
                        </a>
                        <!-- Add other main navigation items as needed -->
                    </div>

                    <!-- Categories Section -->
                    <div class="mt-6">
                        <h3 class="px-6 text-xs font-medium text-gray-500 uppercase tracking-wider">Categories</h3>
                        <div class="mt-2 px-3">
                            <a href="#" class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 rounded-lg hover:bg-gray-50">
                                <div class="w-2 h-2 rounded-full bg-green-500"></div>
                                <span>Category 1</span>
                            </a>
                            <a href="#" class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 rounded-lg hover:bg-gray-50">
                                <div class="w-2 h-2 rounded-full bg-yellow-500"></div>
                                <span>Category 2</span>
                            </a>
                        </div>
                    </div>

                    <!-- Info Section -->
                    <div class="mt-6">
                        <h3 class="px-6 text-xs font-medium text-gray-500 uppercase tracking-wider">Info</h3>
                        <div class="mt-2 px-3 space-y-1">
                            <a href="#" class="block px-3 py-2 text-sm text-gray-700 rounded-lg hover:bg-gray-50">
                                About
                            </a>
                            <a href="#" class="block px-3 py-2 text-sm text-gray-700 rounded-lg hover:bg-gray-50">
                                Help
                            </a>
                        </div>
                    </div>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 p-6">
                <section id="content">
                    @yield('content')
                </section>
            </main>
        </div>
    </div>
</body>
</html>