<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ config('app.name', 'Laravel') }}</title>
  <script defer src="{{ asset('js/layout.js') }}"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:ital,wght@0,100..900;1,100..900&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Vollkorn:ital,wght@0,400..900;1,400..900&display=swap"
    rel="stylesheet">

  <link rel="stylesheet" href={{ asset('css/app.css') }}>
  @vite('resources/css/app.css')
  {{--
  <link rel="stylesheet" href="{{ asset('css/build.css' )}}"> --}}

</head>


<body class="bg-[#F4F2ED] text-[#3C3D37] font-grotesk">
  <div class="flex flex-row divide-x-2 divide-black h-screen relative">
    <aside class="w-32 md:w-48 divide-y-2 divide-black flex flex-col fixed top-0 h-screen">
      {{-- title --}}
      <div class="w-32 md:w-48 h-12 bg-pastelGreen flex items-center">
        <a href="{{ url('/') }}" class="m-auto text-[#F4F2ED] font-bold text-4xl tracking-tigh">
          <span class="block md:hidden">w.UP</span>
          <span class="hidden md:block">whatsUP</span>
        </a>
      </div>
      {{-- left bar --}}
      <div class="text-lg mb-auto divide-y-2 divide-black">
        <div class="p-4">
          <h3 class="font-light text-gray-500 mb-2">admin</h3>
          <a href="{{route('admin.overview')}}" class="underline-effect ml-2">overview</a>
        </div>
        <div>
          <h3 class="px-4 font-light text-gray-500 mb-2 mt-4 ">manage</h3>
          <div class="*:*:underline-effect *:px-4 gap-2 flex flex-col text-xl *:ml-2 pb-4">
            <a href="{{route('admin.users')}}">
              <span>users</span>
            </a>
            <a href="{{route('admin.hubs')}}">
              <span>hubs</span>
            </a>
            <a href="{{route('admin.posts')}}">
              <span>posts</span>
            </a>
          </div>
        </div>

      </div>
      {{-- footer --}}

      <footer class="bg-pastelBlue flex items-center justify-evenly py-2">
        <a href="" class="flex flex-row items-center gap-2">
          <svg class="fill-black h-3 w-3" viewBox="0 0 52 52" xmlns="http://www.w3.org/2000/svg">
            <g data-name="Group 132" id="Group_132">
              <path
                d="M38,52a2,2,0,0,1-1.41-.59l-24-24a2,2,0,0,1,0-2.82l24-24a2,2,0,0,1,2.82,0,2,2,0,0,1,0,2.82L16.83,26,39.41,48.59A2,2,0,0,1,38,52Z" />
            </g>
          </svg>
          <span class="underline-effect">back</span>
        </a>
        {{-- <a href="{{ route('notifications') }}"
          class="text-[#3C3D37] hover:text-[#3C3D37] transition-colors hidden md:block relative">
          <div class="rounded-lg bg-pastelRed animate-ping w-2 h-2 absolute top-0 right-0"></div>
          <div class="rounded-lg bg-pastelRed w-2 h-2 absolute top-0 right-0"></div>
          <svg class="w-6 h-6 hover:fill-pastelYellow fill-transparent transition-colors" stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
        </a> --}}

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
      </footer>
    </aside>

    <!-- Content Area -->
    <main class="ml-32 md:ml-48 w-full overflow-y-auto ">
      <div class="divide-y-2 divide-black w-full">
        @yield('content')
      </div>
    </main>
  </div>


</body>