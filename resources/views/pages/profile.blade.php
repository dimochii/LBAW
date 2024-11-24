@extends('layouts.app')

@section('content')
<div class="min-h-screen">
  {{-- Main Profile Section with Black Border Bottom --}}
  <div class="border-b-2 border-black">
    <div class="max-w-7xl mx-auto p-6 flex flex-col md:flex-row items-start gap-6">
      {{-- Profile Image --}}
      <div class="flex-shrink-0">
        @if ($user->image_id)
        <div class="ring-2 ring-black rounded-full p-1">
          <img src="{{ asset('images/' . $user->image_id . '.jpg') }}" alt="Profile Image"
            class="h-24 w-24 rounded-full object-cover">
        </div>
        @else
        <div class="ring-2 ring-black rounded-full p-1">
          <div class="h-24 w-24 rounded-full bg-gray-200 flex items-center justify-center">
            <span class="text-gray-500 text-2xl">{{ substr($user->name, 0, 1) }}</span>
          </div>
        </div>
        @endif
      </div>

      {{-- Profile Info --}}
      <div class="flex-1">
        <div class="flex flex-col md:flex-row justify-between items-start">
          <div>
            <h1 class="text-3xl font-medium text-gray-900">{{ $user->name }}</h1>
            <p class="text-gray-600">{{ '@' . $user->username }}</p>
            <p class="mt-1 text-gray-600">{{ $user->email }}</p>
          </div>

          {{-- Stats and Edit --}}
          <div class="flex flex-col items-end">
            @if (Auth::check() && Auth::user()->id === $user->id)
            <div class="mb-4">
              <a href="{{ route('user.edit', $user->id) }}" class="btn btn-warning">Edit Profile</a>
            </div>
            @endif

            {{-- Stats on the right --}}
            <div class="flex items-start gap-4 text-sm text-gray-600 mt-4 md:mt-0">
              <div class="text-right">
                <div class="font-semibold">{{ 15 }}</div> {{-- Falta esta lógica--}}
                <div>articles</div>
              </div>
              <div class="text-right">
                <div class="font-semibold">{{ 56 }}</div> {{-- Falta esta lógica--}}
                <div>readers</div>
              </div>
              <div class="text-right">
                <div class="font-semibold">{{ $user->reputation }}</div>
                <div>reputation</div>
              </div>
            </div>
          </div>
        </div>

        <p class="mt-4 text-gray-700 max-w-2xl">
          {{ $user->description ?? 'No description provided.' }}
        </p>

        {{-- Followers/Following buttons --}}
        <div class="mt-6 flex flex-col sm:flex-row gap-4 justify-end">
          <div class="group">
            <a href="{{ route('user.followers', $user->id) }}"
              class="group inline-flex items-center gap-4 px-8 py-4  text-xl font-medium transition-all duration-300 hover:bg-[#3C3D37] hover:text-white">
              <div class="flex flex-col items-start">
                <span class="text-sm font-medium">Followers</span>
                <span class="text-2xl font-bold">{{ $followers->count() ?? 0 }}</span>
              </div>
              <svg xmlns="http://www.w3.org/2000/svg"
                class="h-6 w-6 transform transition-transform duration-300 group-hover:translate-x-2"
                viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                  d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                  clip-rule="evenodd" />
              </svg>
            </a>
          </div>

          <div class="group">
            <a href="{{ route('user.following', $user->id) }}"
              class="group inline-flex items-center gap-4 px-8 py-4  text-xl font-medium transition-all duration-300 hover:bg-[#3C3D37] hover:text-white">
              <div class="flex flex-col items-start">
                <span class="text-sm font-medium">Following</span>
                <span class="text-2xl font-bold">{{ $following->count() ?? 0 }}</span>
              </div>
              <svg xmlns="http://www.w3.org/2000/svg"
                class="h-6 w-6 transform transition-transform duration-300 group-hover:translate-x-2"
                viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                  d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                  clip-rule="evenodd" />
              </svg>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Navigation Tabs with Black Border --}}
  <div class="border-b-2 border-black w-full">
    <div class="w-full">
      <nav class="max-w-7xl mx-auto px-6 flex flex-wrap gap-4 md:gap-8">
        <a href="{{ url('/users/' . $user->id . '/articles') }}"
          class="underline-effect py-4 text-gray-900 hover:text-gray-700 relative group">
          articles
          <span
            class="absolute bottom-1 left-0 w-full h-0.5 bg-black transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></span>
        </a>
        <a href="{{ url('/users/' . $user->id . '/discussions') }}"
          class="underline-effect py-4 text-gray-500 hover:text-gray-900 relative group">
          discussions
          <span
            class="absolute bottom-1 left-0 w-full h-0.5 bg-black transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></span>
        </a>
        <a href="{{ url('/users/' . $user->id . '/upvoted') }}"
          class="underline-effect py-4 text-gray-500 hover:text-gray-900 relative group">
          upvoted
          <span
            class="absolute bottom-1 left-0 w-full h-0.5 bg-black transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></span>
        </a>
        <a href="{{ url('/users/' . $user->id . '/hubs') }}"
          class="underline-effect py-4 text-gray-500 hover:text-gray-900 ml-auto relative group">
          hubs
          <span
            class="absolute bottom-1 left-0 w-full h-0.5 bg-black transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></span>
        </a>
      </nav>
    </div>
  </div>

  <div class="divide-y-2 border-b-2 border-black">
    @if ($posts->count() > 0)
    @foreach ($posts as $post)
    @include('partials.post', [
    'news' => 'true',
    'post' => $post,
    ])
    @endforeach
    @else
    <p class="text-gray-500">This user has not authored any posts yet.</p>
    @endif
  </div>
</div>
@endsection