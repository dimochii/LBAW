<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ config('app.name', 'Laravel') }}</title>
  <script src="{{ asset('js/layout.js') }}"></script>
  @vite('resources/css/app.css')
</head>

<body class="bg-bg-fill antialiased">
  <div id="app">
    <main>
      @yield('content')
    </main>
  </div>

  @if(Session::has('error'))
  <div class="fixed bottom-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert">
    <span class="block sm:inline">{{ Session::get('error') }}</span>
  </div>
  @endif

  @if(Session::has('status'))
  <div class="fixed bottom-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded"
    role="alert">
    <span class="block sm:inline">{{ Session::get('status') }}</span>
  </div>
  @endif
</body>

</html>