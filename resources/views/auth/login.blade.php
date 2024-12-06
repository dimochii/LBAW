@extends('layouts.auth')

@section('content')
<div class="min-h-screen bg-bg-fill flex">
    <!-- Left Panel -->
    <div class="hidden lg:flex lg:w-1/2 bg-pastelGreen/10">
        <div class="w-full p-16 flex flex-col justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-pastelGreen rounded-lg flex items-center justify-center">
                    <span class="text-white text-xl font-bold">W</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">WhatsUp</h1>
            </div>
            
            <div class="space-y-6">
                <h2 class="text-4xl font-bold text-gray-800">Welcome to your creative community</h2>
                <p class="text-lg text-gray-600">Connect, share, and grow with like-minded individuals.</p>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div class="h-32 bg-pastelGreen/20 rounded-lg"></div>
                <div class="h-32 bg-pastelBlue/20 rounded-lg"></div>
                <div class="h-32 bg-pastelGreen/30 rounded-lg"></div>
            </div>
        </div>
    </div>

    <!-- Login Form -->
    <div class="w-full lg:w-1/2 flex flex-col">
        <div class="p-6">
            <a href="{{ url()->previous() }}" class="text-gray-600 hover:text-gray-900 flex items-center space-x-2">
                <span>← Back</span>
            </a>
        </div>

        <div class="flex-grow flex flex-col justify-center px-8 sm:px-16 lg:px-24">
            <div class="w-full max-w-md mx-auto space-y-8">
                <div class="text-center space-y-2">
                    <h2 class="text-3xl font-bold text-gray-900">Sign in</h2>
                    <p class="text-gray-600">
                        Don't have an account?
                        <a href="{{ route('register') }}" class="text-pastelBlue hover:text-pastelGreen transition-colors">
                            Create one here
                        </a>
                    </p>
                </div>

                <div class="bg-white shadow-lg rounded-lg">
                    <div class="p-6 space-y-6">
                        <form method="POST" action="{{ route('login') }}" class="space-y-6">
                            {{ csrf_field() }}

                            <div class="space-y-2">
                                <label for="email" class="block text-sm font-medium text-gray-700">
                                    Email address
                                </label>
                                <input id="email" name="email" type="email" required autofocus
                                    value="{{ old('email') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-pastelBlue focus:border-pastelBlue text-sm"
                                    placeholder="you@example.com">
                                @if ($errors->has('email'))
                                    <p class="mt-2 text-sm text-pastelRed">
                                        {{ $errors->first('email') }}
                                    </p>
                                @endif
                            </div>

                            <div class="space-y-2">
                                <label for="password" class="block text-sm font-medium text-gray-700">
                                    Password
                                </label>
                                <input id="password" name="password" type="password" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-pastelBlue focus:border-pastelBlue text-sm"
                                    placeholder="Enter your password">
                                @if ($errors->has('password'))
                                    <p class="mt-2 text-sm text-pastelRed">
                                        {{ $errors->first('password') }}
                                    </p>
                                @endif
                            </div>
                            <a href="{{ route('google-auth') }}" class="w-full flex items-center justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pastelBlue">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c1/Google_%22G%22_logo.svg/1200px-Google_%22G%22_logo.svg.png" alt="Google logo" class="h-5 mr-2">
                                <span>Continue with Google</span>
                            </a>
                            <p>
                                <a href="{{ url('/forgot-password') }}" class="text-pastelBlue text-xs font-medium hover:text-pastelGreen transition-colors">
                                    Forgot password?
                                </a>
                            </p>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <input id="remember" name="remember" type="checkbox"
                                        {{ old('remember') ? 'checked' : '' }}
                                        class="h-4 w-4 text-pastelGreen focus:ring-pastelBlue border-gray-300 rounded">
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

                            <button type="submit"
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-pastelGreen hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pastelBlue transition-colors">
                                Sign in
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection