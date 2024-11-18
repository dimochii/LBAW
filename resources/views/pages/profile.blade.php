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
                            <div class="font-semibold">{{ 56 }}</div>  {{-- Falta esta lógica--}}
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
            <div class="mt-6 flex flex-col sm:flex-row gap-4">
                <div class="group">
                    <a href="{{ route('user.followers', $user->id) }}" 
                    class="inline-flex items-center gap-3 px-4 py-2 bg-white border-2 border-black rounded-xl hover:bg-whatsup-green hover:text-white transition-all duration-200 shadow-md hover:shadow-lg">
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
                    class="inline-flex items-center gap-3 px-4 py-2 bg-white border-2 border-black rounded-xl hover:bg-whatsup-green hover:text-white transition-all duration-200 shadow-md hover:shadow-lg">
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
                class="underline-effect py-4 text-gray-900 hover:text-gray-700 relative group">
                    articles
                    <span class="absolute bottom-1 left-0 w-full h-0.5 bg-black transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></span>
                </a>
                <a href="{{ url('/users/' . $user->id . '/discussions') }}" 
                class="underline-effect py-4 text-gray-500 hover:text-gray-900 relative group">
                    discussions
                    <span class="absolute bottom-1 left-0 w-full h-0.5 bg-black transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></span>
                </a>
                <a href="{{ url('/users/' . $user->id . '/upvoted') }}" 
                class="underline-effect py-4 text-gray-500 hover:text-gray-900 relative group">
                    upvoted
                    <span class="absolute bottom-1 left-0 w-full h-0.5 bg-black transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></span>
                </a>
                <a href="{{ url('/users/' . $user->id . '/hubs') }}" 
                class="underline-effect py-4 text-gray-500 hover:text-gray-900 ml-auto relative group">
                    hubs
                    <span class="absolute bottom-1 left-0 w-full h-0.5 bg-black transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></span>
                </a>
            </nav>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 py-6">
        @if ($posts->count() > 0)
            @foreach ($posts as $post)
                <div class="border-b border-gray-200 pb-6 mb-6">
                    {{-- Hub/Category --}}
                    <div class="flex items-center gap-2 mb-2">
                        @if ($post->community)
                            <div class="flex items-center gap-2">
                                @php
                                    $colors = ['bg-red-500', 'bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-purple-500'];
                                    $randomColor = $colors[array_rand($colors)];
                                @endphp
                                
                                @if ($post->community->image_id)
                                    <img src="{{ asset('images/' . $post->community->image_id . '.jpg') }}"
                                        class="w-5 h-5 rounded">
                                @else
                                    <div class="w-5 h-5 rounded-full {{ $randomColor }}"></div>
                                @endif
                                <span class="text-gray-600">{{ '/' . $post->community->name }}</span>
                            </div>
                        @else
                            <div class="text-gray-500">Community Unavailable</div>
                        @endif
                    </div>

                    {{-- Rest of the post content remains the same --}}
                    {{-- Post Title --}}
                    <h2 class="text-2xl font-semibold mb-4">
                        <a href="{{ route('news.show', $post->id) }}" class="text-black hover:underline">
                            {{ $post->title }}
                        </a>
                    </h2>

                    {{-- Interaction Stats --}}
                    <div class="flex items-center gap-4 text-gray-500">
                        <div class="flex items-center gap-2">
                            <div>
                                <input id="upvote_{{ $post->id }}" type="checkbox" class="hidden peer/upvote">
                                <label for="upvote_{{ $post->id }}"
                                    class="peer-checked/upvote:fill-blue-400 cursor-pointer hover:fill-blue-400 transition-all ease-out">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M21,21H3L12,3Z" />
                                    </svg>
                                </label>
                            </div>
                            <span class="mr-2">
                                @php
                                    $likesCount = $post->likes_count ?? 0;
                                    echo $likesCount >= 1000 ? number_format($likesCount / 1000, 1) . 'k' : $likesCount;
                                @endphp
                            </span>
                            <div>
                                <input id="downvote_{{ $post->id }}" type="checkbox" class="hidden peer/downvote">
                                <label for="downvote_{{ $post->id }}"
                                    class="cursor-pointer peer-checked/downvote:fill-red-400 hover:fill-red-400 transition-all ease-out">
                                    <svg class="w-5 h-5 rotate-180" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M21,21H3L12,3Z" />
                                    </svg>
                                </label>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM7 8H5v2h2V8zm2 0h2v2H9V8zm6 0h-2v2h2V8z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ $post->comments_count ?? 0 }}</span>
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
</div>
@endsection
