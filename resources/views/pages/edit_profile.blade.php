@extends('layouts.app')

@section('content')
<div class="min-h-screen">
    {{-- Header Section with Black Border Bottom --}}
    <div class="border-b-2 border-black">
        <div class="max-w-7xl mx-auto p-6">
            <h1 class="text-3xl font-medium text-gray-900">Edit Profile</h1>
            <p class="text-gray-600 mt-2">Update your personal information and profile settings</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 py-8">
        {{-- Success Message --}}
        @if (session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-lg animate-fade-in">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <ul class="list-disc list-inside text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('user.update', $user->id) }}" method="POST" enctype="multipart/form-data" 
              class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @csrf

            {{-- Left Column --}}
            <div class="space-y-6">
                {{-- Profile Image Section --}}
                <div class="border-2 border-black rounded-xl p-6 group hover:shadow-lg transition-all duration-200">
                    <label class="block text-sm font-medium text-gray-700 mb-4">Profile Image</label>
                    <div class="flex items-center space-x-6">
                        {{-- Current Image Preview --}}
                        <div class="ring-2 ring-black rounded-full p-1">
                            @if ($user->image_id)
                                <img src="{{ asset('images/' . $user->image_id . '.jpg') }}" 
                                     alt="Current Profile" 
                                     class="h-20 w-20 rounded-full object-cover">
                            @else
                                <div class="h-20 w-20 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-gray-500 text-2xl">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                            @endif
                        </div>
                        
                        {{-- Image Upload --}}
                        <div class="flex-1">
                            <label class="cursor-pointer group relative block">
                                <input type="file" class="hidden" id="image" name="image" 
                                       accept="image/*" onchange="updateImagePreview(event)">
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-black transition-colors duration-200">
                                    <svg class="mx-auto h-8 w-8 text-gray-400 group-hover:text-gray-500" 
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="mt-1 text-sm text-gray-500">Click to upload new image</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Basic Information --}}
                <div class="border-2 border-black rounded-xl p-6 space-y-6 group hover:shadow-lg transition-all duration-200">
                    <h3 class="text-lg font-medium text-gray-900">Basic Information</h3>
                    
                    {{-- Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                               class="mt-1 block w-full rounded-lg border-2 border-gray-200 px-4 py-2 focus:border-black focus:ring-0 transition-colors duration-200">
                    </div>

                    {{-- Username --}}
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}"
                               class="mt-1 block w-full rounded-lg border-2 border-gray-200 px-4 py-2 focus:border-black focus:ring-0 transition-colors duration-200">
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                               class="mt-1 block w-full rounded-lg border-2 border-gray-200 px-4 py-2 focus:border-black focus:ring-0 transition-colors duration-200">
                    </div>
                </div>
            </div>

            {{-- Right Column --}}
            <div class="space-y-6">
                {{-- Additional Information --}}
                <div class="border-2 border-black rounded-xl p-6 space-y-6 group hover:shadow-lg transition-all duration-200">
                    <h3 class="text-lg font-medium text-gray-900">Additional Information</h3>

                    {{-- Birth Date --}}
                    <div>
                        <label for="birth_date" class="block text-sm font-medium text-gray-700">Birth Date</label>
                        <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date', $user->birth_date) }}"
                               class="mt-1 block w-full rounded-lg border-2 border-gray-200 px-4 py-2 focus:border-black focus:ring-0 transition-colors duration-200">
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="description" name="description" rows="4"
                                  class="mt-1 block w-full rounded-lg border-2 border-gray-200 px-4 py-2 focus:border-black focus:ring-0 transition-colors duration-200">{{ old('description', $user->description) }}</textarea>
                    </div>
                </div>

                {{-- Password Section --}}
                <div class="border-2 border-black rounded-xl p-6 space-y-6 group hover:shadow-lg transition-all duration-200">
                    <h3 class="text-lg font-medium text-gray-900">Change Password</h3>
                    
                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                        <input type="password" id="password" name="password"
                               class="mt-1 block w-full rounded-lg border-2 border-gray-200 px-4 py-2 focus:border-black focus:ring-0 transition-colors duration-200"
                               placeholder="Leave blank to keep current">
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="mt-1 block w-full rounded-lg border-2 border-gray-200 px-4 py-2 focus:border-black focus:ring-0 transition-colors duration-200">
                    </div>
                </div>
            </div>

            {{-- Submit Button --}}
            <div class="md:col-span-2 flex justify-end">
                <button type="submit" 
                        class="group inline-flex items-center gap-3 px-6 py-3 bg-white border-2 border-black rounded-xl hover:bg-whatsup-green hover:text-white transition-all duration-200 shadow-md hover:shadow-lg">
                    <span class="text-lg font-medium">Save Changes</span>
                    <svg xmlns="http://www.w3.org/2000/svg" 
                         class="h-5 w-5 group-hover:translate-x-1 transition-transform duration-200" 
                         viewBox="0 0 20 20" 
                         fill="currentColor">
                        <path fill-rule="evenodd" 
                              d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" 
                              clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/user.js') }}" defer ></script>
@endpush
@endsection