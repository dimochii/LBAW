@extends('layouts.auth')

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="flex justify-center">
            <div class="w-16 h-16 bg-whatsup-green rounded-lg flex items-center justify-center">
                <span class="text-white text-2xl font-bold">W</span>
            </div>
        </div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
            Welcome back
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            Don't have an account?
            <a href="{{ route('register') }}" class="font-medium text-whatsup-blue hover:text-whatsup-green transition-colors">
                Create one here
            </a>
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                {{ csrf_field() }}

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        Email address
                    </label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" required autofocus
                            value="{{ old('email') }}"
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-whatsup-blue focus:border-whatsup-blue sm:text-sm">
                    </div>
                    @if ($errors->has('email'))
                        <p class="mt-2 text-sm text-whatsup-red">
                            {{ $errors->first('email') }}
                        </p>
                    @endif
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        Password
                    </label>
                    <div class="mt-1">
                        <input id="password" name="password" type="password" required
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-whatsup-blue focus:border-whatsup-blue sm:text-sm">
                    </div>
                    @if ($errors->has('password'))
                        <p class="mt-2 text-sm text-whatsup-red">
                            {{ $errors->first('password') }}
                        </p>
                    @endif
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox"
                            {{ old('remember') ? 'checked' : '' }}
                            class="h-4 w-4 text-whatsup-green focus:ring-whatsup-blue border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-700">
                            Remember me
                        </label>
                    </div>
                </div>

                @if (session('success'))
                    <div class="rounded-md bg-green-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800">
                                    {{ session('success') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <div>
                    <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-whatsup-green hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-whatsup-blue transition-colors">
                        Sign in
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection