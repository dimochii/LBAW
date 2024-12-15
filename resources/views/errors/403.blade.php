@extends('layouts.app')
@section('content')
<div class="min-h-screen flex items-center justify-center text-gray-800">
    <div class="bg-white border-2 border-black shadow-2xl p-16 max-w-3xl text-center relative animate-pulse">
        <div class="flex items-center justify-center mb-8">
            <h1 class="tracking-tighter font-medium text-6xl animate-bounce">4</h1>
            <h1 class="tracking-tighter font-medium text-6xl animate-bounce">0</h1>
            <h1 class="tracking-tighter font-medium text-6xl animate-bounce">3</h1>
        </div>
        <h1 class="tracking-tighter font-medium text-4xl animate-bounce">Forbidden</h1>
        <p class="text-2xl font-medium mb-8 animate-pulse">
        You don’t have permission to access this page. Please return to the home page.
        </p>
        <a href="{{ url('/') }}" class="inline-block bg-pastelRed border-2 border-black text-white text-2xl font-bold py-4 px-8 rounded-full shadow-lg transition duration-300 animate-bounce">
            Return to Home Page
        </a>
    </div>
</div>
@endsection
