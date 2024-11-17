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
            Create your account
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            Already have an account?
            <a href="{{ route('login') }}" class="font-medium text-whatsup-blue hover:text-whatsup-green transition-colors">
                Sign in here
            </a>
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <form method="POST" action="{{ route('register') }}" class="space-y-6">
                {{ csrf_field() }}

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        Full Name
                    </label>
                    <div class="mt-1">
                        <input id="name" name="name" type="text" required autofocus
                            value="{{ old('name') }}"
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-whatsup-blue focus:border-whatsup-blue sm:text-sm">
                    </div>
                    @if ($errors->has('name'))
                        <p class="mt-2 text-sm text-whatsup-red">
                            {{ $errors->first('name') }}
                        </p>
                    @endif
                </div>

                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">
                        Username
                    </label>
                    <div class="mt-1">
                        <input id="username" name="username" type="text" required
                            value="{{ old('username') }}"
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-whatsup-blue focus:border-whatsup-blue sm:text-sm">
                    </div>
                    @if ($errors->has('username'))
                        <p class="mt-2 text-sm text-whatsup-red">
                            {{ $errors->first('username') }}
                        </p>
                    @endif
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        Email address
                    </label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" required
                            value="{{ old('email') }}"
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-whatsup-blue focus:border-whatsup-blue sm:text-sm">
                    </div>
                    @if ($errors->has('email'))
                        <p class="mt-2 text-sm text-whatsup-red">
                            {{ $errors->first('email') }}
                        </p>
                    @endif
                </div>

                <!-- Password -->
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

                <div>
                    <label for="password-confirm" class="block text-sm font-medium text-gray-700">
                        Confirm Password
                    </label>
                    <div class="mt-1">
                        <input id="password-confirm" name="password_confirmation" type="password" required
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-whatsup-blue focus:border-whatsup-blue sm:text-sm">
                    </div>
                </div>

                <div>
                    <label for="birth_date" class="block text-sm font-medium text-gray-700">
                        Birth Date
                    </label>
                    <div class="mt-1">
                        <input id="birth_date" name="birth_date" type="date" required
                            value="{{ old('birth_date') }}"
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-whatsup-blue focus:border-whatsup-blue sm:text-sm">
                    </div>
                    @if ($errors->has('birth_date'))
                        <p class="mt-2 text-sm text-whatsup-red">
                            {{ $errors->first('birth_date') }}
                        </p>
                    @endif
                </div>

                <div>
                    <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-whatsup-green hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-whatsup-blue transition-colors">
                        Create Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection