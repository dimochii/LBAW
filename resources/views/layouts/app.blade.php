<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ config('app.name', 'Laravel') }}</title>
  <script src="{{ asset('js/layout.js') }}"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:ital,wght@0,100..900;1,100..900&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Vollkorn:ital,wght@0,400..900;1,400..900&display=swap"
    rel="stylesheet">
  {{--
  <link rel="stylesheet" href={{ asset('css/app.css') }}>
  @vite('resources/css/app.css') --}}
  {{-- @vite('resources/css/app.css') --}}
  <link rel="stylesheet" href="{{ asset('css/build.css' )}}">

</head>

<body class="bg-[#F4F2ED] text-[#3C3D37] font-grotesk">
  <div class="min-h-screen flex flex-col">
    <!-- Top Header -->
    <header class="flex items-center justify-between h-12 border-b-2 border-black">
      <!-- Logo Section -->
      <div class="w-32 md:w-48 bg-pastelGreen h-full flex items-center border-r-2 border-black">
        <a href="{{ url('/') }}" class="m-auto text-[#F4F2ED] font-bold text-4xl tracking-tight">
          <span class="block md:hidden">w.UP</span>
          <span class="hidden md:block">whatsUP</span>
        </a>
      </div>

      <!-- Hamburger Button -->
      <button id="mobile-menu-button"
        class="md:hidden fixed bottom-4 right-4 z-50 bg-pastelBlue text-[#F4F2ED] p-3 rounded-full shadow-lg"
        onclick="toggleMobileMenu()">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
        </svg>
      </button>
      <!-- Search Section -->
      <div class="flex-1 bg-pastelRed h-full flex items-center pl-2 md:pl-4 relative">
        <svg class="w-5 h-5 text-[#F4F2ED]/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input id="search-input" type="text" placeholder="search"
          class="w-full bg-transparent border-none text-[#F4F2ED] placeholder-[#F4F2ED] px-2 md:px-3 py-2 focus:outline-none ">

        <!-- Enhanced Search Results Dropdown -->
        <div id="search-results"
          class="absolute left-2 md:left-4 right-2 md:right-4 top-full mt-2 bg-[#F4F2ED] backdrop-blur-sm rounded-xl shadow-2xl border-2 border-black overflow-hidden transform opacity-0 scale-95 transition-all duration-200 ease-out pointer-events-none z-50 max-w-full md:max-w-[calc(100%-16rem)]">
          <!-- Communities Section -->
          <div class="border-b border-gray-200 bg-gradient-to-r from-red-50 to-blue-50">
            <div class="p-4">
              <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                </svg>
                Communities
              </h3>
              <div
                class="space-y-2 max-h-48 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-transparent">
                <div class="p-3 rounded-lg hover:bg-gray-50 transition-colors duration-200 cursor-pointer">
                  <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-green-500"></div>
                    <span class="text-sm text-gray-700"></span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Posts Section -->
          <div class="border-b border-gray-200 bg-gradient-to-r from-blue-50 to-green-50">
            <div class="p-4">
              <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                Posts
              </h3>
              <div
                class="space-y-2 max-h-48 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-transparent">
                <!-- Example post result item -->
                <div class="p-3 rounded-lg hover:bg-gray-50 transition-colors duration-200 cursor-pointer">
                  <span class="text-sm text-gray-700"></span>
                </div>
              </div>
            </div>
          </div>

          <!-- Users Section -->
          <div class="bg-gradient-to-r from-green-50 to-red-50">
            <div class="p-4">
              <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Users
              </h3>
              <div
                class="space-y-2 max-h-48 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-transparent">
                <div class="p-3 rounded-lg hover:bg-gray-50 transition-colors duration-200 cursor-pointer">
                  <div class="flex items-center gap-3">
                    <div class="w-6 h-6 rounded-full bg-blue-500"></div>
                    <span class="text-sm text-gray-700"></span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>


        <!-- Right Section -->
        <div class="bg-pastelBlue h-full w-32 md:w-64 flex items-center border-l-2 border-black justify-evenly">
          @if (Auth::check())
          <a href="{{ route('post.create') }}">
            <svg class="w-5 h-5 fill-[#3C3D37] hover:fill-[#3C3D37]/80 hover:rotate-180 transition-all "
              xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 455 455"
              xml:space="preserve">
              <polygon points="455,212.5 242.5,212.5 242.5,0 212.5,0 212.5,212.5 0,212.5 0,242.5 212.5,242.5 212.5,455 242.5,455 242.5,242.5 
	455,242.5 " />
            </svg>
          </a>

          <a href="{{ route('notifications') }}"
            class="text-[#3C3D37] hover:text-[#3C3D37] transition-colors hidden md:block relative">
            <div class="rounded-lg bg-pastelRed animate-ping w-2 h-2 absolute top-0 right-0"></div>
            <div class="rounded-lg bg-pastelRed w-2 h-2 absolute top-0 right-0"></div>
            <svg class="w-6 h-6 hover:fill-pastelYellow fill-transparent transition-colors" stroke="currentColor"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
          </a>

          <div class="relative group">
            <a href="{{ route('user.profile',  Auth::user()->id) }}"
              class="relative fill-transparent text-[#3C3D37] hover:text-[#3C3D37]/80 transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </a>
            <div
              class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200">
              <!-- Changed to direct link instead of form -->
              <a href="{{ route('logout') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                Logout
              </a>
            </div>
          </div>
        </div>
        @else
        <a href="{{ route('login') }}"
          class="px-2 md:px-4 py-1.5 text-sm font-medium text-[#F4F2ED] bg-black/20 hover:bg-black/30 rounded-full transition-colors duration-200 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          <span class="hidden md:inline">Login</span>
        </a>
        @endif
      </div>

    </header>

    <div class="flex flex-1">
      <!-- Mobile Menu Button -->
      <button id="mobile-menu-button"
        class="md:hidden fixed bottom-4 right-4 z-50 bg-pastelBlue text-[#F4F2ED] p-3 rounded-full shadow-lg"
        onclick="toggleMobileMenu()">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
        </svg>
      </button>

      <!-- Left Sidebar -->
      <aside id="left-sidebar"
        class="text-[#3C3D37] text-lg fixed inset-y-0 left-0 transform -translate-x-full md:translate-x-0 md:static md:w-48 flex-shrink-0 bg-[#F4F2ED] border-r-2 border-black transition-transform duration-200 ease-in-out z-40 overflow-y-auto">
        <nav class="divide-y-2 divide-black">
          <!-- Primary Links -->
          <div class="py-4">
            <div class="*:*:underline-effect *:px-4 *:py-1 flex flex-col text-xl ml-2">
              <a href="{{ url('/home') }}" class=" mr-auto">
                <span class="">home</span>
              </a>
              <a href="{{ url('/global') }}" class="">
                <span class="">global</span>
              </a>
              <a href="{{ url('/recent') }}" class="">
                <span class="">recent</span>
              </a>
            </div>
          </div>

          <!-- Recent Section -->
          <div class="py-4">
            <h3 class="px-4 font-light text-gray-600 mb-1">recent</h3>
            <div class="space-y-1 *:transition-colors *:pl-6">
              <a href="#" class="flex items-center space-x-2 px-4 py-2 hover:bg-[#3C3D37] hover:text-[#F4F2ED] ">
                <div class="w-2 h-2 rounded-full bg-green-500"></div>
                <span class="">/Economics</span>
              </a>
              <a href="#" class="flex items-center space-x-2 px-4 py-2 hover:bg-[#3C3D37] hover:text-[#F4F2ED] ">
                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                <span class="">/Sports</span>
              </a>
              <a href="#" class="flex items-center space-x-2 px-4 py-2 hover:bg-[#3C3D37] hover:text-[#F4F2ED]">
                <div class="w-2 h-2 rounded-full bg-red-500"></div>
                <span class="">/Portugal</span>
              </a>
              <a href="#" class="flex items-center space-x-2 px-4 py-2 hover:bg-[#3C3D37] hover:text-[#F4F2ED]">
                <div class="w-2 h-2 rounded-full bg-yellow-500"></div>
                <span class="">/Finances</span>
              </a>
            </div>
          </div>

          <!-- Hubs Section -->
          <div class="py-4">
            <h3 class="px-4 font-light text-gray-600 mb-1">hubs</h3>
            <div class="space-y-1 *:transition-colors *:pl-6">
              <a href="#" class="flex items-center space-x-2 px-4 py-2 hover:bg-[#3C3D37] hover:text-[#F4F2ED]">
                <div class="w-2 h-2 rounded-full bg-green-500"></div>
                <span class="">/Economics</span>
              </a>
              <a href="#" class="flex items-center space-x-2 px-4 py-2  hover:bg-[#3C3D37] hover:text-[#F4F2ED]">
                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                <span class="">/Sports</span>
              </a>
            </div>
          </div>

          <!-- Info Section -->
          <div class="py-4">
            <h3 class="px-4 font-light text-gray-600 mb-1">info</h3>
            <div class="*:*:underline-effect *:px-4 *:py-1 flex flex-col text-xl ml-2">
              <a href="{{ url('/home') }}" class="">
                <span class="">about us</span>
              </a>
              <a href="#" class="">
                <span class="">best of</span>
              </a>
              <a href="#" class="">
                <span class="">hubs</span>
              </a>
              <a href="#" class="">
                <span class="">help</span>
              </a>
            </div>
          </div>
        </nav>
      </aside>

      <!-- Main Content -->
      <main class="flex-1">
        <section id="content">
          @yield('content')
        </section>
      </main>

      <!-- Right Sidebar -->
      @if (Request::is('hub/*') || Request::is('news/*'))
      <aside id="right-sidebar"
        class="fixed inset-y-0 right-0 transform translate-x-full md:translate-x-0 md:static md:w-64 flex-shrink-0 bg-[#F4F2ED] border-l-2 border-black transition-transform duration-200 ease-in-out z-40">
        <!-- Hubs Section -->
        <div class="p-4 border-b-2 border-black">
          <div class="flex flex-wrap items-start gap-3">
            <div class="w-12 h-12 bg-green-500 rounded-full flex-shrink-0"></div>
            <div class="flex-1 break-words">
              <h2 class="font-medium break-all">/Economics</h2>
              <p class="text-sm text-gray-600 whitespace-normal">
                Discussing economic trends, policies, and finance. Get involved in economic discussions.
              </p>
              <div class="mt-2 flex flex-wrap gap-x-4 gap-y-2 text-sm text-gray-600">
                <div class="flex items-center shrink-0">
                  <span>123k</span>
                  <span class="ml-1">Reading</span>
                </div>
                <div class="flex items-center shrink-0">
                  <span>123k</span>
                  <span class="ml-1">Following</span>
                </div>
              </div>
              <button class="mt-2 px-4 py-1 text-sm bg-black text-[#F4F2ED] rounded-full hover:bg-black/80">
                follow +
              </button>
            </div>
          </div>
        </div>


        <!-- Moderators Section -->
        <div class="p-4">
          <h3 class="text-sm font-medium text-gray-500 mb-3">moderators</h3>
          <div class="space-y-3">
            <div class="flex items-center space-x-2">
              <div class="w-8 h-8 bg-blue-500 rounded-full flex-shrink-0"></div>
              <div>
                <p class="text-sm font-medium">@admin</p>
                <p class="text-xs text-gray-500">Administrator</p>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <div class="w-8 h-8 bg-green-500 rounded-full flex-shrink-0"></div>
              <div>
                <p class="text-sm font-medium">@friends</p>
                <p class="text-xs text-gray-500">Moderator</p>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <div class="w-8 h-8 bg-yellow-500 rounded-full flex-shrink-0"></div>
              <div>
                <p class="text-sm font-medium">@walkPro123</p>
                <p class="text-xs text-gray-500">Moderator</p>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <div class="w-8 h-8 bg-red-500 rounded-full flex-shrink-0"></div>
              <div>
                <p class="text-sm font-medium">@anonymous</p>
                <p class="text-xs text-gray-500">Moderator</p>
              </div>
            </div>
          </div>
        </div>
      </aside>
      @endif
    </div>
  </div>
  <div id="mobile-menu-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden md:hidden"
    onclick="toggleLeftSidebar()">
  </div>
</body>

</html>