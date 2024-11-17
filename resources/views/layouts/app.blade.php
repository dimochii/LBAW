<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name', 'Laravel') }}</title>

  <!-- Styles -->
  <script type="text/javascript">
    // Fix for Firefox autofocus CSS bug
            // See: http://stackoverflow.com/questions/18943276/html-5-autofocus-messes-up-css-loading/18945951#18945951
  </script>
  <script type="text/javascript" src={{ url('js/app.js') }} defer>
  </script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:ital,wght@0,100..900;1,100..900&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Vollkorn:ital,wght@0,400..900;1,400..900&display=swap"
    rel="stylesheet">
  @vite('resources/css/app.css')

</head>

<body class="bg-[#F4F2ED] text-[#3C3D37]">
  <main>
    <header>
      <h1><a class="font-inter font-bold text-9xl tracking-tighter" href="{{ url('/cards') }}">WhatsUp</a></h1>
      @if (Auth::check())
      <a href="{{ url('/logout') }}"> Logout </a> <span>{{ Auth::user()->name }}</span>
      @endif
    </header>
    <section id="content">
      @yield('content')
    </section>
  </main>
</body>

</html>