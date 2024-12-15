@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <header class="border-b-2 border-black pb-6 mb-8">
            <h1 class="text-4xl font-grotesk text-neutral-800">{{ $user->name }}'s Following</h1>
        </header>

        @if ($following->isEmpty())
            <p class="text-black text-sm font-mono">No connections yet.</p>
        @else
            <div class="grid gap-2  border-black">
                @foreach ($following as $followedUser)
                    <a href="{{ route('user.profile', $followedUser->id) }}" 
                       class="flex items-center border-2 border-black hover:bg-[#3C3D37] hover:text-white text-black p-4 transition-all">
                        <div class="w-12 h-12 bg-neutral-200 border border-black overflow-hidden flex-shrink-0 rounded-full">
                            <img src="{{ asset($followedUser->image->path ?? '/images/default.jpg') }}" 
                                 alt="{{ $followedUser->name }}" 
                                 class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1 pl-4">
                            <h2 class="font-bold text-lg">{{ $followedUser->name }}</h2>
                            <p class="text-sm">{{ $followedUser->username }}</p>
                        </div>
                        <div class="ml-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="square" stroke-linejoin="miter" stroke-width="3" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endsection
