@extends('layouts.app')

@section('content')
<div class="flex justify-center items-center min-h-screen bg-gray-100">
    <div class="bg-white p-8 rounded-lg shadow-lg max-w-sm w-full">
        <h2 class="text-2xl font-semibold text-center text-gray-800 mb-6">Login</h2>

        <form method="POST" action="{{ route('login') }}">
            {{ csrf_field() }}

            <!-- E-mail Field -->
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">E-mail</label>
                <input 
                    id="email" 
                    type="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    required 
                    autofocus 
                    class="w-full px-4 py-2 mt-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @if ($errors->has('email'))
                    <span class="text-red-500 text-sm">
                        {{ $errors->first('email') }}
                    </span>
                @endif
            </div>

            <!-- Password Field -->
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input 
                    id="password" 
                    type="password" 
                    name="password" 
                    required 
                    class="w-full px-4 py-2 mt-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @if ($errors->has('password'))
                    <span class="text-red-500 text-sm">
                        {{ $errors->first('password') }}
                    </span>
                @endif
            </div>

            <!-- Remember Me -->
            <div class="flex items-center mb-6">
                <input 
                    id="remember" 
                    type="checkbox" 
                    name="remember" 
                    {{ old('remember') ? 'checked' : '' }} 
                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <label for="remember" class="ml-2 text-sm text-gray-600">Remember Me</label>
            </div>

            <!-- Login Button -->
            <button 
                type="submit" 
                class="w-full py-2 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Login
            </button>

            <!-- Register Link -->
            <div class="mt-4 text-center">
                <a href="{{ route('register') }}" class="text-blue-600 hover:underline text-sm">Create an account</a>
            </div>

            <!-- Success message -->
            @if (session('success'))
                <p class="text-green-500 text-center mt-4">{{ session('success') }}</p>
            @endif
        </form>
    </div>
</div>
@endsection
