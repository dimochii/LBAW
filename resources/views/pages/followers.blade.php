@extends('layouts.app')
   
@section('content')
    <div class="max-w-4xl mx-auto px-4 py-8">
        <header class="border-b border-neutral-600 pb-6 mb-8">
            <h1 class="text-2xl font-grotesk text-neutral-800">{{ $user->name }}'s Followers</h1>
        </header>
       
        @if ($followers->isEmpty())
            <p class="text-neutral-600 text-base">No followers yet.</p>
        @else
            <div class="divide-y divide-neutral-300">
                @foreach ($followers as $follower)
                    <a href="{{ route('user.profile', $follower->id) }}"
                       class="block py-4 group transition-colors duration-200 hover:bg-[#3C3D37] hover:text-white">
                        <div class="flex items-center px-4 space-x-4">
                            <div class="w-10 h-10 rounded-full overflow-hidden bg-neutral-300">
                                @if($follower->avatar)
                                    <img src="{{ $follower->avatar }}" alt="{{ $follower->name }}" 
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-neutral-700">
                                        {{ substr($follower->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <h2 class="text-neutral-800 text-base font-light group-hover:text-white">
                                    {{ $follower->name }}
                                </h2>
                                <p class="text-xs text-neutral-600 group-hover:text-neutral-200">
                                    {{ $follower->username }}
                                </p>
                            </div>
                            <div class="text-neutral-500 group-hover:text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endsection
