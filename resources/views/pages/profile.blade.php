@extends('layouts.app')

@section('content')
<div class="min-h-screen">
  <div class="flex flex-row gap-8 p-8 border-b-2 border-black items-center relative min-w-32">
    <img src="https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png" alt="Profile Image"
      class="rounded-full ring-2 ring-black h-32 w-32">
    <div class="h-full flex flex-col gap-4">
      <div class="flex-col flex">
        <div class=" tracking-tighter font-medium text-6xl ">{{ $user->name }}</div>
        <div>{{ '@' . $user->username }}</div>
      </div>
      <p class="font-light">
        {{ $user->description }}
      </p>
    </div>
    <div class="ml-auto">
      <div class="mt-6 flex flex-col sm:flex-row gap-4">
        <div class="group">
          <a href="{{ route('user.followers', $user->id) }}"
            class="group inline-flex items-center gap-4 px-8 py-4  text-xl font-medium transition-all duration-300 hover:bg-[#3C3D37] hover:text-white">
            <div class="flex flex-col items-start">
              <span class="text-sm font-medium">Followers</span>
              <span class="text-2xl font-bold">{{ $followers->count() ?? 0 }}</span>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg"
              class="h-6 w-6 transform transition-transform duration-300 group-hover:translate-x-2 text-[#F4F2ED]"
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
              class="h-6 w-6 transform transition-transform duration-300 group-hover:translate-x-2 text-[#F4F2ED]"
              viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                clip-rule="evenodd" />
            </svg>
          </a>
        </div>
      </div>


      <a href="{{ route('user.edit', $user->id) }}" class="font-light tracking-tighter text-xl absolute top-4 right-8 underline-effect cursor-pointer">
        edit profile
      </a>
    </div>

  </div>

  {{-- Navigation Tabs with Black Border --}}
  <div class="border-b-2 border-black w-full font-light text-xl tracking-tighter">
    <div class="w-full">
      <nav class="max-w-7xl mx-auto px-6 flex flex-wrap gap-4 md:gap-8">
        <a href="{{ url('/users/' . $user->id . '/articles') }}"
          class=" py-4 text-gray-900 hover:text-gray-700 relative group">
          articles
        </a>
        <a href="{{ url('/users/' . $user->id . '/discussions') }}"
          class="py-4 text-gray-500 hover:text-gray-900 relative group">
          discussions
        </a>
        <a href="{{ url('/users/' . $user->id . '/upvoted') }}"
          class="py-4 text-gray-500 hover:text-gray-900 relative group">
          upvoted
        </a>
      </nav>
    </div>
  </div>

  <div class="divide-y-2 divide-black border-b-2 border-black">
    @if ($posts->count() > 0)
    @foreach ($posts as $item)
    @include('partials.post', [
    'news' => 'true',
    'post' => $item->news,
    ])
    @endforeach
    @else
    <p class="text-gray-500">This user has not authored any posts yet.</p>
    @endif
  </div>
</div>
@endsection