@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-white">
    {{-- Main Profile Section with Black Border Bottom --}}
    <div class="border-b-2 border-black">
        <div class="max-w-7xl mx-auto p-6 flex flex-col md:flex-row items-start gap-6">
            {{-- Profile Image --}}
            <div class="flex-shrink-0">
                @if ($user->image_id)
                    <div class="ring-2 ring-black rounded-full p-1">
                        <img src="{{ asset('images/' . $user->image_id . '.jpg') }}"
                            alt="Profile Image"
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
                    
                    {{-- Stats on the right --}}
                    <div class="flex items-start gap-4 text-sm text-gray-600 mt-4 md:mt-0">
                        <div class="text-right">
                            <div class="font-semibold">{{ 15 }}</div> {{-- Falta esta lógica--}}
                            <div>articles</div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold">{{ 56 }}</div>  {{-- Falta esta lógica--}}
                            <div>readers</div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold">{{ $user->reputation }}</div>
                            <div>reputation</div>
                        </div>
                    </div>
                </div>

                <p class="mt-4 text-gray-700 max-w-2xl">
                    {{ $user->description ?? 'No description provided.' }}
                </p>

                {{-- Followers/Following buttons --}}
                <div class="mt-6 flex flex-col sm:flex-row gap-4">
                    <div class="group">
                        <a href="{{ route('user.followers', $user->id) }}" 
                        class="inline-flex items-center gap-3 px-4 py-2 bg-white border-2 border-black rounded-lg hover:bg-black hover:text-white transition-colors duration-200">
                            <div class="flex flex-col items-start">
                                <span class="text-sm font-medium">Followers</span>
                                <span class="text-2xl font-bold">{{ $followers->count() }}</span>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" 
                                class="h-5 w-5 group-hover:translate-x-1 transition-transform duration-200" 
                                viewBox="0 0 20 20" 
                                fill="currentColor">
                                <path fill-rule="evenodd" 
                                    d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" 
                                    clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>

                    <div class="group">
                        <a href="{{ route('user.following', $user->id) }}" 
                        class="inline-flex items-center gap-3 px-4 py-2 bg-white border-2 border-black rounded-lg hover:bg-black hover:text-white transition-colors duration-200">
                            <div class="flex flex-col items-start">
                                <span class="text-sm font-medium">Following</span>
                                <span class="text-2xl font-bold">{{ $following->count() }}</span>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" 
                                class="h-5 w-5 group-hover:translate-x-1 transition-transform duration-200" 
                                viewBox="0 0 20 20" 
                                fill="currentColor">
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
                   class="py-4 text-gray-900 hover:text-gray-700 border-b-2 border-black">
                    articles
                </a>
                <a href="{{ url('/users/' . $user->id . '/discussions') }}" 
                   class="py-4 text-gray-500 hover:text-gray-900">
                    discussions
                </a>
                <a href="{{ url('/users/' . $user->id . '/upvoted') }}" 
                   class="py-4 text-gray-500 hover:text-gray-900">
                    upvoted
                </a>
                <a href="{{ url('/users/' . $user->id . '/hubs') }}" 
                   class="py-4 text-gray-500 hover:text-gray-900 ml-auto">
                    hubs
                </a>
            </nav>
        </div>
    </div>

    {{-- Content Area matching mockup --}}
    <div class="max-w-7xl mx-auto px-6 py-6">
    @if ($posts->count() > 0)
        @foreach ($posts as $post)
            <div class="border-b border-gray-200 pb-6 mb-6">
                {{-- Hub/Category --}}
                <div class="flex items-center gap-2 mb-2">
                    @if ($post->community)
                        <div class="flex items-center gap-2">
                            @if ($post->community->image_id)
                                <img src="{{ asset('images/' . $post->community->image_id . '.jpg') }}" 
                                    alt="{{ $post->community->name }}" 
                                    class="w-5 h-5 rounded">
                            @else
                                <div class="w-5 h-5 rounded-full bg-gray-300"></div>
                            @endif
                            <span class="text-gray-600">{{ '/' . $post->community->name }}</span>
                        </div>
                    @else
                        <div class="text-gray-500">Community Unavailable</div>
                    @endif
                </div>

                {{-- Post Title --}}
                <h2 class="text-2xl font-semibold mb-4">
                    <a href="{{ route('news.show', $post->id) }}" class="text-blue-500 hover:underline">
                        {{ $post->title }}
                    </a>
                </h2>

                {{-- Interaction Stats --}}
                <div class="flex items-center gap-4 text-gray-500">
                    <div class="flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z" />
                        </svg>
                        <span>{{ $post->likes_count }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM7 8H5v2h2V8zm2 0h2v2H9V8zm6 0h-2v2h2V8z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ $post->comments_count }}</span>
                    </div>
                    <span class="text-gray-400">{{ $post->creation_date->format('F j, Y') ?? 'Unknown date' }}</span>
                </div>
            </div>
        @endforeach

        {{-- Pagination Links --}}
        <div class="mt-6">
            {{ $posts->links() }}
        </div>
    @else
        <p class="text-gray-500">This user has not authored any posts yet.</p>
    @endif
    </div>
