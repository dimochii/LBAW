<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ config('app.name', 'Laravel') }}</title>
  <script defer src="{{ asset('js/app.js') }}"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:ital,wght@0,100..900;1,100..900&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Vollkorn:ital,wght@0,400..900;1,400..900&display=swap"
    rel="stylesheet">

  <link rel="apple-touch-icon" sizes="180x180" href="{{asset('images/apple-touch-icon.png')}}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{asset('images/favicon-32x32.png')}}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{asset('images/favicon-16x16.png')}}">

  {{--
  <link rel="stylesheet" href={{ asset('css/app.css') }}> --}}
  @vite('resources/css/app.css')
</head>

<body class="bg-[#F4F2ED] text-[#3C3D37] font-grotesk ">
  <div class="min-h-screen flex flex-col">
    <!-- Top Header -->
    <header
      class="flex items-center justify-between h-12 border-b-2 border-black fixed top-0 left-0 right-0 z-50 bg-[#F4F2ED]">
      <!-- Logo Section -->
      <div class="w-32 md:w-48 bg-pastelGreen h-full flex items-center border-r-2 border-black">
        <a href="{{ url('/') }}" class="m-auto text-[#F4F2ED] font-bold text-4xl tracking-tight">
          <span class="block md:hidden">w.UP</span>
          <span class="hidden md:block">whatsUP</span>
        </a>
      </div>
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
      </div>


      <!-- Right Section -->
      <div class="bg-pastelBlue h-full w-32 md:w-64 flex items-center border-l-2 border-black justify-evenly">
        @if (Auth::check())
        <a href="{{ route('post.create') }}">
          <svg class="w-5 h-5 fill-[#3C3D37] hover:fill-[#3C3D37]/80 hover:rotate-180 transition-all"
            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 455 455" xml:space="preserve">
            <polygon
              points="455,212.5 242.5,212.5 242.5,0 212.5,0 212.5,212.5 0,212.5 0,242.5 212.5,242.5 212.5,455 242.5,455 242.5,242.5 455,242.5" />
          </svg>
        </a>

        <a href="{{ route('notifications.show', Auth::user()->id) }}"
          class="text-[#3C3D37] hover:text-[#3C3D37] transition-colors hidden md:block relative">
          @php
          $unreadCount = Auth::user()->notifications()->where('is_read', false)->count();
          @endphp

          @if($unreadCount > 0)
          <div class="absolute -top-2 -right-2">
            <div class="absolute w-5 h-5 bg-pastelRed rounded-full animate-ping opacity-75"></div>

            <div
              class="relative bg-pastelRed text-white text-[10px] font-bold rounded-full w-5 h-5 flex items-center justify-center px-1">
              {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </div>
          </div>
          @endif

          <svg class="w-6 h-6 hover:fill-pastelYellow fill-transparent transition-colors" stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
        </a>

        <div class="relative">
          <a href="#" class="relative fill-transparent text-[#3C3D37] hover:text-[#3C3D37]/80 transition-colors"
            id="profileIcon">
            <img src="{{ asset(Auth::user()->image->path ?? 'images/default.jpg') }}"
                  class="size-7 rounded-full object-cover mr-2" />
          </a>
          <div id="dropdownMenu"
            class="absolute right-0 mt-4 bg-white border border-gray-200 w-48 rounded-lg shadow-lg hidden z-50">
            <a href="{{ route('user.profile', Auth::user()->id) }}"
              class="block py-2 text-gray-700 hover:bg-gray-100 border-b border-gray-200">
              <div class="flex items-center justify-around px-4 py-3">
                <img src="{{ asset(Auth::user()->image->path ?? 'images/default.jpg') }}"
                  class="size-10 rounded-full object-cover mr-2" />
                <div class="flex flex-col text-left">
                  <div class="font-medium text-gray-900">{{ Auth::user()->name }}</div>
                  <div class="text-gray-500 text-sm">View profile</div>
                </div>
              </div>
            </a>
            @if(Auth::user()->is_admin)
            <a href="{{ route('admin.overview', Auth::user()->id) }}"
              class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
              <svg viewBox="0 0 24 24" class="stroke-gray-700 w-4 h-4 mr-2 inline-block"
                xmlns="http://www.w3.org/2000/svg" fill="none">
                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                <g id="SVGRepo_iconCarrier">
                  <g id="Interface / Settings">
                    <g id="Vector">
                      <path
                        d="M20.3499 8.92293L19.9837 8.7192C19.9269 8.68756 19.8989 8.67169 19.8714 8.65524C19.5983 8.49165 19.3682 8.26564 19.2002 7.99523C19.1833 7.96802 19.1674 7.93949 19.1348 7.8831C19.1023 7.82677 19.0858 7.79823 19.0706 7.76998C18.92 7.48866 18.8385 7.17515 18.8336 6.85606C18.8331 6.82398 18.8332 6.79121 18.8343 6.72604L18.8415 6.30078C18.8529 5.62025 18.8587 5.27894 18.763 4.97262C18.6781 4.70053 18.536 4.44993 18.3462 4.23725C18.1317 3.99685 17.8347 3.82534 17.2402 3.48276L16.7464 3.1982C16.1536 2.85658 15.8571 2.68571 15.5423 2.62057C15.2639 2.56294 14.9765 2.56561 14.6991 2.62789C14.3859 2.69819 14.0931 2.87351 13.5079 3.22396L13.5045 3.22555L13.1507 3.43741C13.0948 3.47091 13.0665 3.48779 13.0384 3.50338C12.7601 3.6581 12.4495 3.74365 12.1312 3.75387C12.0992 3.7549 12.0665 3.7549 12.0013 3.7549C11.9365 3.7549 11.9024 3.7549 11.8704 3.75387C11.5515 3.74361 11.2402 3.65759 10.9615 3.50224C10.9334 3.48658 10.9056 3.46956 10.8496 3.4359L10.4935 3.22213C9.90422 2.86836 9.60915 2.69121 9.29427 2.62057C9.0157 2.55807 8.72737 2.55634 8.44791 2.61471C8.13236 2.68062 7.83577 2.85276 7.24258 3.19703L7.23994 3.1982L6.75228 3.48124L6.74688 3.48454C6.15904 3.82572 5.86441 3.99672 5.6517 4.23614C5.46294 4.4486 5.32185 4.69881 5.2374 4.97018C5.14194 5.27691 5.14703 5.61896 5.15853 6.3027L5.16568 6.72736C5.16676 6.79166 5.16864 6.82362 5.16817 6.85525C5.16343 7.17499 5.08086 7.48914 4.92974 7.77096C4.9148 7.79883 4.8987 7.8267 4.86654 7.88237C4.83436 7.93809 4.81877 7.96579 4.80209 7.99268C4.63336 8.26452 4.40214 8.49186 4.12733 8.65572C4.10015 8.67193 4.0715 8.68752 4.01521 8.71871L3.65365 8.91908C3.05208 9.25245 2.75137 9.41928 2.53256 9.65669C2.33898 9.86672 2.19275 10.1158 2.10349 10.3872C2.00259 10.6939 2.00267 11.0378 2.00424 11.7255L2.00551 12.2877C2.00706 12.9708 2.00919 13.3122 2.11032 13.6168C2.19979 13.8863 2.34495 14.134 2.53744 14.3427C2.75502 14.5787 3.05274 14.7445 3.64974 15.0766L4.00808 15.276C4.06907 15.3099 4.09976 15.3266 4.12917 15.3444C4.40148 15.5083 4.63089 15.735 4.79818 16.0053C4.81625 16.0345 4.8336 16.0648 4.8683 16.1255C4.90256 16.1853 4.92009 16.2152 4.93594 16.2452C5.08261 16.5229 5.16114 16.8315 5.16649 17.1455C5.16707 17.1794 5.16658 17.2137 5.16541 17.2827L5.15853 17.6902C5.14695 18.3763 5.1419 18.7197 5.23792 19.0273C5.32287 19.2994 5.46484 19.55 5.65463 19.7627C5.86915 20.0031 6.16655 20.1745 6.76107 20.5171L7.25478 20.8015C7.84763 21.1432 8.14395 21.3138 8.45869 21.379C8.73714 21.4366 9.02464 21.4344 9.30209 21.3721C9.61567 21.3017 9.90948 21.1258 10.4964 20.7743L10.8502 20.5625C10.9062 20.5289 10.9346 20.5121 10.9626 20.4965C11.2409 20.3418 11.5512 20.2558 11.8695 20.2456C11.9015 20.2446 11.9342 20.2446 11.9994 20.2446C12.0648 20.2446 12.0974 20.2446 12.1295 20.2456C12.4484 20.2559 12.7607 20.3422 13.0394 20.4975C13.0639 20.5112 13.0885 20.526 13.1316 20.5519L13.5078 20.7777C14.0971 21.1315 14.3916 21.3081 14.7065 21.3788C14.985 21.4413 15.2736 21.4438 15.5531 21.3855C15.8685 21.3196 16.1657 21.1471 16.7586 20.803L17.2536 20.5157C17.8418 20.1743 18.1367 20.0031 18.3495 19.7636C18.5383 19.5512 18.6796 19.3011 18.764 19.0297C18.8588 18.7252 18.8531 18.3858 18.8417 17.7119L18.8343 17.2724C18.8332 17.2081 18.8331 17.1761 18.8336 17.1445C18.8383 16.8247 18.9195 16.5104 19.0706 16.2286C19.0856 16.2007 19.1018 16.1726 19.1338 16.1171C19.166 16.0615 19.1827 16.0337 19.1994 16.0068C19.3681 15.7349 19.5995 15.5074 19.8744 15.3435C19.9012 15.3275 19.9289 15.3122 19.9838 15.2818L19.9857 15.2809L20.3472 15.0805C20.9488 14.7472 21.2501 14.5801 21.4689 14.3427C21.6625 14.1327 21.8085 13.8839 21.8978 13.6126C21.9981 13.3077 21.9973 12.9658 21.9958 12.2861L21.9945 11.7119C21.9929 11.0287 21.9921 10.6874 21.891 10.3828C21.8015 10.1133 21.6555 9.86561 21.463 9.65685C21.2457 9.42111 20.9475 9.25526 20.3517 8.92378L20.3499 8.92293Z"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                      <path
                        d="M8.00033 12C8.00033 14.2091 9.79119 16 12.0003 16C14.2095 16 16.0003 14.2091 16.0003 12C16.0003 9.79082 14.2095 7.99996 12.0003 7.99996C9.79119 7.99996 8.00033 9.79082 8.00033 12Z"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    </g>
                  </g>
                </g>
              </svg>
              Admin Options
            </a>
            @endif
            @if(Auth::user()->moderatedCommunities()->exists())
            {{-- <a href="{{ route('user.moderator', Auth::user()->id) }}"
              class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
              <svg class="w-4 h-4 mr-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
              </svg>
              Hub Options
            </a> --}}
            @endif
            <a href="{{ route('logout') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
              <svg class="w-4 h-4 mr-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              Logout
            </a>
          </div>
        </div>
        @else
        <a href="{{ route('login') }}" class="h-full w-full  flex items-center justify-center text-lg">
          Login
        </a>
        @endif
      </div>
    </header>

    <div class="flex pt-12 ml-0 md:ml-48">
      <!-- Mobile Menu Button -->
      <button id="mobile-menu-button"
        class="md:hidden fixed bottom-4 right-4 z-50 bg-pastelBlue text-[#F4F2ED] p-3 rounded-full shadow-lg transition-transform duration-300">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
        </svg>
      </button>

      <!-- Left Sidebar -->
      <aside id="left-sidebar"
        class="[&::-webkit-scrollbar]:hidden text-[#3C3D37] text-lg fixed inset-y-0 left-0 w-48 bg-[#F4F2ED] border-r-2 border-black z-40 overflow-y-auto max-h-screen transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
        <nav class="divide-y-2 divide-black">
          <!-- Primary Links -->
          <div class="pt-14 pb-2">
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

          @php
          $colors = ['green-500', 'blue-500', 'red-500', 'yellow-500'];
          $colorIndex = 0;
          @endphp

          <div class="py-4">
            <h3 class="px-4 font-light text-gray-600 mb-1">recent</h3>
            <div class="space-y-1 *:transition-colors *:pl-4">
              @foreach ($recentHubs as $recent)
              <a href="/hub/{{ $recent['id'] }}"
                class="flex items-center space-x-2 px-4 py-2 hover:bg-[#3C3D37] hover:text-[#F4F2ED]">
                {{-- <div class="w-2 h-2 rounded-full bg-{{ $colors[$colorIndex] }}"></div> --}}
                <img src="{{asset($recent['image'])}}" alt="{{$recent['name']}} image"
                  class=" h-8 w-8 rounded-full object-cover">
                <span class="break-all text-sm">h/{{ $recent['name'] }}</span>
              </a>
              @php
              $colorIndex = ($colorIndex + 1) % count($colors);
              @endphp
              @endforeach
            </div>
          </div>

          <div class="pt-4">
            <h3 class="px-4 font-light text-gray-600 mb-1">hubs</h3>

            <div class="space-y-1 *:transition-colors *:pl-4">
              @foreach ($userHubs as $hubs)
              <a href="/hub/{{ $hubs['id'] }}"
                class="flex items-center space-x-2 px-4 py-2 hover:bg-[#3C3D37] hover:text-[#F4F2ED]">
                {{-- <div class="w-2 h-2 rounded-full bg-{{ $colors[$colorIndex] }}"></div> --}}
                <img src="{{asset($hubs['image'])}}" alt="{{$hubs['name']}} image"
                  class=" h-8 w-8 rounded-full object-cover">
                <span class="break-all text-sm">h/{{ $hubs['name'] }}</span>
              </a>
              @php
              $colorIndex = ($colorIndex + 1) % count($colors);
              @endphp
              @endforeach
            </div>
            <a href="{{ url('/hubs/create') }}"
              class="flex items-center space-x-2 px-4 py-2 hover:bg-[#3C3D37] hover:text-[#F4F2ED] ">
              <span class="">+ create hub</span>
            </a>
          </div>
          <!-- Info Section -->
          <div class="py-4">
            <h3 class="px-4 font-light text-gray-600 mb-1">info</h3>
            <div class="*:*:underline-effect *:px-4 *:py-1 flex flex-col text-xl ml-2">
              <a href="{{ url('/about-us') }}" class="">
                <span class="">about us</span>
              </a>
              <a href="{{ url('/bestof') }}" class="">
                <span class="">best of</span>
              </a>
              <a href="{{ url('/all-hubs') }}" class="">
                <span class="">hubs</span>
              </a>
            </div>
          </div>
        </nav>
      </aside>


      <!-- Main Content -->
      <main class="flex-1">
        {{-- bug das blade templates, sem este include, z-index dos elementos fica partido, este elemento não tem efeito
        na pagina e resolve problema --}}
        @include('partials.report_box', ['reported_id' => 1])

        <section id="content">
          @yield('content')
        </section>
      </main>

      <!-- Right Sidebar -->
      @if (Request::is('hub/*') && isset($community))
      <aside id="right-sidebar"
        class="fixed inset-y-0 right-0 transform translate-x-full md:translate-x-0 md:static md:w-64 bg-[#F4F2ED] border-l-2 border-black transition-transform duration-200 ease-in-out overflow-y-auto min-h-screen">
        <!-- Hubs Section -->
        <div class="border-b-2 border-black">
          <a class="p-4 flex flex-col hover:bg-[#3C3D37] hover:text-[#F4F2ED] transition-all group"
            href="{{route('communities.show', $community->id)}}">
            <img src="{{ asset($community->image->path ?? 'images/groupdefault.jpg') }}" alt="hub image"
              class="rounded-full h-20 w-20 mx-auto ring-2 group-hover:ring-[#F4F2ED] ring-[#3C3D37] object-cover">
            <h1 class="mt-4 mb-1 font-medium tracking-tight text-xl text-center">h/{{$community->name}}</h1>
            <p class="text-sm font-light break-all tracking-tight text-center">{{ $community->description }}</p>
            <div class="flex items-center justify-center text-sm font-light break-all tracking-tight text-center">
              @if($community->privacy)
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500 mr-2" viewBox="0 0 24 24"
                fill="currentColor">
                <path d="M12 12c2.21 0 4-1.79 4-4V5c0-2.21-1.79-4-4-4S8 2.79 8 5v3c0 2.21 1.79 4 4 4z"></path>
                <path fill-rule="evenodd"
                  d="M4 9c-1.1 0-2 .9-2 2v9c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2v-9c0-1.1-.9-2-2-2H4zm2 3v6h12v-6H6z"
                  clip-rule="evenodd"></path>
              </svg>
              private
              @else
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 24 24"
                fill="currentColor">
                <path
                  d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z">
                </path>
                <path d="M11 14h2v2h-2zm0-8h2v6h-2z"></path>
              </svg>
              public
              @endif
            </div>
            <div class="flex flex-row justify-center gap-8 mt-4">
              <div class="flex items-center">
                <span class="font-medium text-lg">{{ number_format($followers_count ?? 0, 0) }}</span>
                <span class="ml-1 text-sm ">followers</span>
              </div>
              <div class="flex items-center">
                <span class="font-medium text-lg">{{ number_format($posts_count ?? 0, 0) }}</span>
                <span class="ml-1 text-sm t">posts</span>
              </div>
            </div>

            <div class="flex justify-center mt-6">
              @auth
              @if($is_following)
              {{-- Estado: Seguindo --}}
              <form action="{{ route('communities.leave', $community->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit"
                  class="px-3.5 py-2.5 text-sm font-medium rounded-lg bg-[#F4F2ED] text-black border-2 border-black">
                  unfollow -
                </button>
              </form>
              @elseif($community->privacy && $community->followRequests->where('authenticated_user_id',
              Auth::user()->id)->where('request_status', 'pending')->count() > 0)
              <button
                class="px-3.5 py-2.5 text-sm font-medium rounded-lg bg-[#F4F2ED] text-gray-600 border-2 border-black cursor-not-allowed"
                disabled>
                request Pending
              </button>
              @else
              <form id="followForm" action="{{ route('communities.join', $community->id) }}" method="POST">
                @csrf
                <button type="submit"
                  class="px-3.5 py-2.5 text-sm font-medium rounded-lg bg-black text-[#F4F2ED] border-2 border-black">
                  follow +
                </button>
              </form>
              @endif
              @else
              <a href="{{ route('login') }}"
                class="px-3.5 py-2.5 text-sm font-medium rounded-lg bg-black text-[#F4F2ED] border-2 border-black">
                follow +
              </a>
              @endauth
            </div>
          </a>
        </div>

        <!-- Moderators Section -->
        <div class="p-4">
          <h3 class="text-sm font-medium text-gray-500 mb-3">moderators</h3>
          <div class="space-y-3">
            @php
            $moderators = $community->moderators ?? [];
            @endphp
            @foreach ($moderators as $moderator)
            <a href="{{ route('user.profile', $moderator->id) }}">
              <div class="flex items-center space-x-2 py-2">
                <div class="w-12 h-12 rounded-full bg-gray-200 overflow-hidden">
                  <img
                    src="{{ asset($moderator->image->path ?? 'https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png') }}"
                    alt="{{ $moderator->username }}" class="w-full h-full object-cover">
                </div>
                <div>
                  <p class="text-sm font-medium">{{ $moderator['name'] }}</p>
                  <p class="text-xs text-gray-500"><span>
                      @
                    </span>{{ $moderator['username'] }}</p>
                </div>
              </div>
            </a>
            @endforeach
          </div>
        </div>

      </aside>

      @endif
    </div>
  </div>
</body>

</html>