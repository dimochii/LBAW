@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Hub Header -->
    @if($community)
    <div class="py-6">
        <div class="flex items-center gap-6">
             <img src="https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png" alt="Profile Image"
            class="rounded-full ring-2 ring-black h-32 w-32">
            <div class="flex-grow">
                <div class="flex items-center gap-2">
                    <h1 class=" tracking-tighter font-medium text-6xl">/{{ $community->name }}</h1>
                    <span class="text-sm px-3 py-1 rounded-full {{ $community->privacy === 'private' ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600' }}">
                        {{ ucfirst($community->privacy) }}
                    </span>
                </div>
                <p class="text-gray-600 mt-2 text-sm">{{ $community->description }}</p>
                <div class="flex items-center gap-4 mt-3 text-sm text-gray-500">
                    <div class="flex items-center gap-2">
                        <span>{{ number_format($community->followers_count ?? 0, 0) }}k</span>
                        <span>Readers</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span>{{ number_format($community->posts_count ?? 0, 0) }}k</span>
                        <span>Something</span>
                    </div>
                </div>
            </div>
            
            @auth
                @if($community->privacy === 'public')
                    @if(!$isFollowing)
                    <form action="{{ route('communities.join', $community->id) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-500 text-white text-sm font-medium rounded-full hover:bg-blue-600 transition-colors duration-200">
                            follow +
                        </button>
                    </form>
                    @else
                    <form action="{{ route('communities.leave', $community->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="px-6 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-full hover:bg-gray-200 transition-colors duration-200">
                            following
                        </button>
                    </form>
                    @endif
                @endif
            @endauth
        </div>
    </div>

    <!-- Posts Section -->
    <div>
        <!-- Sort Controls -->
        <div class="flex justify-between items-center mb-6 font-light">
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-600">sort by</span>
                <select name="sort" class="bg-transparent text-sm text-gray-900 font-medium focus:outline-none">
                    <option value="newest">Newest</option>
                    <option value="top">Top</option>
                    <option value="trending">Trending</option>
                </select>
            </div>
            @auth
            @if($isFollowing)
            <a href="{{ route('posts.create', ['community_id' => $community->id]) }}" 
               class="px-6 py-2 bg-blue-500 text-white text-sm font-medium rounded-full hover:bg-blue-600 transition-colors duration-200">
                post +
            </a>
            @endif
            @endauth
        </div>

        <!-- Posts Grid -->
        <div class="divide-y-2 border-b-2 border-black">
            @if ($community->posts->count() > 0)
                @foreach ($community->posts as $post)
                    @include('partials.post_hub', [
                        'news' => 'true',
                        'post' => $post->news,
                    ])
                @endforeach
            @else
            <div class="text-center py-12 bg-white rounded-xl shadow-sm">
                <p class="text-gray-500">No posts available in this hub yet.</p>
                @auth
                @if($isFollowing)
                <a href="{{ route('posts.create', ['community_id' => $community->id]) }}" 
                   class="mt-4 inline-block px-6 py-2 bg-blue-500 text-white text-sm font-medium rounded-full hover:bg-blue-600 transition-colors duration-200">
                    Create the first post
                </a>
                @endif
                @endauth
            </div>
            @endif
        </div>

        <!-- Pagination -->
        @if(method_exists($community->posts, 'hasPages') && $community->posts->hasPages())
        <div class="py-6">
            {{ $community->posts->links() }}
        </div>
        @endif
    </div>

    <!-- Moderators Section -->
    <div class="mt-8 bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold mb-4">moderators</h2>
        <div class="space-y-3">
            @foreach($moderators as $moderator)
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-gray-100 overflow-hidden">
                    <img src="{{ $moderator['avatar'] ?? '/images/default-avatar.png' }}" 
                         alt="{{ $moderator['username'] }}"
                         class="w-full h-full object-cover">
                </div>
                <span class="text-sm text-gray-700">@{{ $moderator['username'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    @else
    <div class="py-12 text-center">
        <p class="text-gray-500 text-xl">Hub not found</p>
        <a href="{{ route('home') }}" 
           class="mt-4 inline-block px-6 py-2 bg-blue-500 text-white text-sm font-medium rounded-full hover:bg-blue-600 transition-colors duration-200">
            Return Home
        </a>
    </div>
    @endif
</div>
@endsection