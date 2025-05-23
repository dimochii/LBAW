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
                <h2 class="text-4xl font-bold text-gray-800">Reset your password</h2>
                <p class="text-lg text-gray-600">We'll send you an email to reset your password.</p>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div class="h-32 bg-pastelGreen/20 rounded-lg"></div>
                <div class="h-32 bg-pastelBlue/20 rounded-lg"></div>
                <div class="h-32 bg-pastelGreen/30 rounded-lg"></div>
            </div>
        </div>
    </div>

    <!-- Recover Password Form -->
    <div class="w-full lg:w-1/2 flex flex-col">
        <div class="p-6">
            <a href="{{ url()->previous() }}" class="text-gray-600 hover:text-gray-900 flex items-center space-x-2">
                <span>← Back</span>
            </a>
        </div>

        <div class="flex-grow flex flex-col justify-center px-8 sm:px-16 lg:px-24">
            <div class="w-full max-w-md mx-auto space-y-8">
                <div class="text-center space-y-2">
                    <h2 class="text-3xl font-bold text-gray-900">Recover your password</h2>
                    <p class="text-gray-600">
                        Enter your email and we'll send you instructions to reset your password.
                    </p>
                </div>

                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="bg-white shadow-lg rounded-lg">
                    <div class="p-6 space-y-6">
                        <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                            @csrf

                            <div class="space-y-2">
                                <label for="email" class="block text-sm font-medium text-gray-700">
                                    Your email
                                </label>
                                <input id="email" name="email" type="email" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-pastelBlue focus:border-pastelBlue text-sm"
                                    placeholder="Email">
                            </div>

                            <button type="submit"
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-pastelGreen hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pastelBlue transition-colors">
                                Send Password Reset Link
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
