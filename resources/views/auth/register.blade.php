@extends('layouts.auth')

@section('content')
<div class="min-h-screen bg-bg-fill flex">
    <!-- Left Panel -->
    <div class="hidden lg:flex lg:w-1/2 bg-whatsup-green/10">
        <div class="w-full p-16 flex flex-col justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-whatsup-green rounded-lg flex items-center justify-center">
                    <span class="text-white text-xl font-bold">W</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">WhatsUp</h1>
            </div>
            
            <div class="space-y-6">
                <h2 class="text-4xl font-bold text-gray-800">Join our creative community</h2>
                <p class="text-lg text-gray-600">Start your journey with WhatsUp today and connect with creators worldwide.</p>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div class="h-32 bg-whatsup-green/20 rounded-lg"></div>
                <div class="h-32 bg-whatsup-blue/20 rounded-lg"></div>
                <div class="h-32 bg-whatsup-green/30 rounded-lg"></div>
            </div>
        </div>
    </div>

    <!-- Register Form -->
    <div class="w-full lg:w-1/2 flex flex-col">
        <div class="p-6">
            <a href="{{ url()->previous() }}" class="text-gray-600 hover:text-gray-900 flex items-center space-x-2">
                <span>← Back</span>
            </a>
        </div>

        <div class="flex-grow flex flex-col justify-center px-8 sm:px-16 lg:px-24 py-8">
            <div class="w-full max-w-md mx-auto space-y-8">
                <div class="text-center space-y-2">
                    <h2 class="text-3xl font-bold text-gray-900">Create Account</h2>
                    <p class="text-gray-600">
                        Already have an account?
                        <a href="{{ route('login') }}" class="text-whatsup-blue hover:text-whatsup-green transition-colors">
                            Sign in here
                        </a>
                    </p>
                </div>

                <div class="bg-white shadow-lg rounded-lg">
                    <div class="p-6 space-y-6">
                        <form method="POST" action="{{ route('register') }}" class="space-y-6">
                            {{ csrf_field() }}

                            <!-- Name and Username row -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label for="name" class="block text-sm font-medium text-gray-700">
                                        Full Name
                                    </label>
                                    <input id="name" name="name" type="text" required autofocus
                                        value="{{ old('name') }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-whatsup-blue focus:border-whatsup-blue text-sm"
                                        placeholder="John Doe">
                                    @if ($errors->has('name'))
                                        <p class="mt-2 text-sm text-whatsup-red">
                                            {{ $errors->first('name') }}
                                        </p>
                                    @endif
                                </div>

                                <div class="space-y-2">
                                    <label for="username" class="block text-sm font-medium text-gray-700">
                                        Username
                                    </label>
                                    <input id="username" name="username" type="text" required
                                        value="{{ old('username') }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-whatsup-blue focus:border-whatsup-blue text-sm"
                                        placeholder="johndoe">
                                    @if ($errors->has('username'))
                                        <p class="mt-2 text-sm text-whatsup-red">
                                            {{ $errors->first('username') }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label for="email" class="block text-sm font-medium text-gray-700">
                                    Email address
                                </label>
                                <input id="email" name="email" type="email" required
                                    value="{{ old('email') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-whatsup-blue focus:border-whatsup-blue text-sm"
                                    placeholder="you@example.com">
                                @if ($errors->has('email'))
                                    <p class="mt-2 text-sm text-whatsup-red">
                                        {{ $errors->first('email') }}
                                    </p>
                                @endif
                            </div>

                            <!-- Password fields row -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label for="password" class="block text-sm font-medium text-gray-700">
                                        Password
                                    </label>
                                    <input id="password" name="password" type="password" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-whatsup-blue focus:border-whatsup-blue text-sm"
                                        placeholder="••••••••">
                                    @if ($errors->has('password'))
                                        <p class="mt-2 text-sm text-whatsup-red">
                                            {{ $errors->first('password') }}
                                        </p>
                                    @endif
                                </div>

                                <div class="space-y-2">
                                    <label for="password-confirm" class="block text-sm font-medium text-gray-700">
                                        Confirm Password
                                    </label>
                                    <input id="password-confirm" name="password_confirmation" type="password" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-whatsup-blue focus:border-whatsup-blue text-sm"
                                        placeholder="••••••••">
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label for="birth_date" class="block text-sm font-medium text-gray-700">
                                    Birth Date
                                </label>
                                <input id="birth_date" name="birth_date" type="date" required
                                    value="{{ old('birth_date') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-whatsup-blue focus:border-whatsup-blue text-sm">
                                @if ($errors->has('birth_date'))
                                    <p class="mt-2 text-sm text-whatsup-red">
                                        {{ $errors->first('birth_date') }}
                                    </p>
                                @endif
                            </div>

                            <button type="submit"
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-whatsup-green hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-whatsup-blue transition-colors">
                                Create Account
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection