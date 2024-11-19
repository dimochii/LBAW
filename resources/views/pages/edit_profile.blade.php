@extends('layouts.app')

@section('content')
<script src="{{ asset('js/user.js') }}" defer></script>
<div class="font-grotesk min-h-screen ">
    {{-- Header Section --}}
    <div class="border-b-2 border-black transition-all duration-300 hover:bg-whatsup-blue hover:text-[#F4F2ED]">
        <div class="max-w-7xl mx-auto p-8">
            <h1 class="text-5xl md:text-6xl lg:text-7xl font-medium tracking-tight">Edit Profile</h1>
            <p class="text-xl font-light mt-4">Update your personal information and profile settings</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-8 py-12">
        {{-- Success Message --}}
        @if (session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-8 rounded-none animate-fade-in">
                <div class="flex items-center">
                    <svg class="h-6 w-6 text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <p class="ml-3 text-lg font-light text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-8 rounded-none">
                <div class="flex items-center">
                    <svg class="h-6 w-6 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <div class="ml-3">
                        <ul class="list-disc list-inside text-lg font-light text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('user.update', $user->id) }}" method="POST" enctype="multipart/form-data" 
              class="grid grid-cols-1 md:grid-cols-2 gap-12">
            @csrf

            {{-- Left Column --}}
            <div class="space-y-8">
                {{-- Profile Image Section --}}
                <div class="border-2 border-black p-8 group transition-all duration-300 hover:bg-gray-50">
                    <h3 class="text-2xl font-medium mb-6">Profile Image</h3>
                    <div class="flex items-center space-x-8">
                        {{-- Current Image Preview --}}
                        <div class="ring-2 ring-black rounded-full p-1 transition-transform duration-300 group-hover:scale-105">
                            @if ($user->image_id)
                                <img src="{{ asset('images/' . $user->image_id . '.jpg') }}" 
                                     alt="Current Profile" 
                                     class="h-24 w-24 rounded-full object-cover">
                            @else
                                <div class="h-24 w-24 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-3xl font-light text-gray-500">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                            @endif
                        </div>
                        
                        {{-- Image Upload --}}
                        <div class="flex-1">
                            <label class="cursor-pointer block">
                                <input type="file" class="hidden" id="image" name="image" 
                                       accept="image/*" onchange="updateImagePreview(event)">
                                <div class="border-2 border-dashed border-gray-300 rounded-none p-6 text-center transition-colors duration-300 hover:border-black">
                                    <svg class="mx-auto h-10 w-10 text-gray-400 transition-colors duration-300 group-hover:text-gray-600" 
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="mt-2 text-lg font-light text-gray-600">Click to upload new image</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Basic Information --}}
                <div class="border-2 border-black p-8 space-y-6 transition-all duration-300 hover:bg-gray-50">
                    <h3 class="text-2xl font-medium">Basic Information</h3>
                    
                    {{-- Name --}}
                    <div>
                        <label for="name" class="block text-lg font-light">Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                               class="mt-2 block w-full border-2 border-gray-200 p-4 font-light text-lg transition-colors duration-300 focus:border-black focus:ring-0">
                    </div>

                    {{-- Username --}}
                    <div>
                        <label for="username" class="block text-lg font-light">Username</label>
                        <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}"
                               class="mt-2 block w-full border-2 border-gray-200 p-4 font-light text-lg transition-colors duration-300 focus:border-black focus:ring-0">
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-lg font-light">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                               class="mt-2 block w-full border-2 border-gray-200 p-4 font-light text-lg transition-colors duration-300 focus:border-black focus:ring-0">
                    </div>
                </div>
            </div>

            {{-- Right Column --}}
            <div class="space-y-8">
                {{-- Additional Information --}}
                <div class="border-2 border-black p-8 space-y-6 transition-all duration-300 hover:bg-gray-50">
                    <h3 class="text-2xl font-medium">Additional Information</h3>

                    {{-- Birth Date --}}
                    <div>
                        <label for="birth_date" class="block text-lg font-light">Birth Date</label>
                        <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date', $user->birth_date) }}"
                               class="mt-2 block w-full border-2 border-gray-200 p-4 font-light text-lg transition-colors duration-300 focus:border-black focus:ring-0">
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="block text-lg font-light">Description</label>
                        <textarea id="description" name="description" rows="4"
                                  class="mt-2 block w-full border-2 border-gray-200 p-4 font-light text-lg transition-colors duration-300 focus:border-black focus:ring-0 resize-none">{{ old('description', $user->description) }}</textarea>
                    </div>
                </div>

                {{-- Password Section --}}
                <div class="border-2 border-black p-8 space-y-6 transition-all duration-300 hover:bg-gray-50">
                    <h3 class="text-2xl font-medium">Change Password</h3>
                    
                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-lg font-light">New Password</label>
                        <input type="password" id="password" name="password"
                               class="mt-2 block w-full border-2 border-gray-200 p-4 font-light text-lg transition-colors duration-300 focus:border-black focus:ring-0"
                               placeholder="Leave blank to keep current">
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <label for="password_confirmation" class="block text-lg font-light">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="mt-2 block w-full border-2 border-gray-200 p-4 font-light text-lg transition-colors duration-300 focus:border-black focus:ring-0">
                    </div>
                </div>
            </div>

            {{-- Submit Button --}}
            <div class="md:col-span-2 flex justify-end">
                <button type="submit" 
                        onclick="event.preventDefault(); submitAndRedirect(this.form, '{{ route('user.profile', ['id' => Auth::user()->getKey()]) }}')"
                        class="group inline-flex items-center gap-4 px-8 py-4 border-2 border-black text-xl font-medium transition-all duration-300 hover:bg-black hover:text-white">
                    <span>Save Changes</span>
                    <svg xmlns="http://www.w3.org/2000/svg" 
                         class="h-6 w-6 transform transition-transform duration-300 group-hover:translate-x-2" 
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
@endsection