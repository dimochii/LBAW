@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Hub Header -->
    @if($community)
    <div class="py-8">
        <div class="flex items-center gap-4">
            <div class="h-16 w-16 rounded-lg bg-gray-200 overflow-hidden">
                <img src="{{ isset($community->image_id) ? '/api/images/' . $community->image_id : '/images/default-hub.png' }}"
                     alt="{{ $community->name }}"
                     class="w-full h-full object-cover">
            </div>
            <div class="flex-grow">
                <h1 class="text-4xl font-medium">{{ $community->name }}</h1>
                <p class="text-gray-600 mt-1">{{ $community->description }}</p>
                <div class="flex items-center gap-2 mt-2">
                    <span class="text-sm {{ $community->privacy === 'private' ? 'text-red-500' : 'text-green-500' }}">
                        {{ ucfirst($community->privacy) }}
                    </span>
                    <span class="text-sm text-gray-500">
                        Created {{ optional($community->creation_date)->format('M d, Y') }}
                    </span>
                </div>
            </div>
            
            @auth
                @if($community->privacy === 'public')
                    @if(!$isFollowing)
                    <form action="{{ route('communities.join', $community->id) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors duration-200">
                            Join Hub
                        </button>
                    </form>
                    @else
                    <form action="{{ route('communities.leave', $community->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors duration-200">
                            Leave Hub
                        </button>
                    </form>
                    @endif
                @endif
            @endauth
        </div>
    </div>

    <!-- Posts Section -->
    <div class="mt-8">
        <!-- Sort Controls -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex gap-4">
                <select name="sort" class="bg-white border border-gray-300 rounded-lg px-3 py-2">
                    <option value="newest">Newest</option>
                    <option value="top">Top</option>
                    <option value="trending">Trending</option>
                </select>
            </div>
            @auth
            @if($isFollowing)
            <a href="{{ route('posts.create', ['community_id' => $community->id]) }}" 
               class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors duration-200">
                Create Post
            </a>
            @endif
            @endauth
        </div>

        <!-- Posts Grid -->
        <div class="space-y-4">
            @if ($community->posts->count() > 0)
                @foreach ($community->posts as $post)
                    @include('partials.post', [
                        'news' => 'true',
                        'post' => $post->news,
                    ])
                @endforeach
            @else
            <div class="text-center py-12">
                <p class="text-gray-500 text-lg">No posts available in this hub yet.</p>
                @auth
                @if($isFollowing)
                <a href="{{ route('posts.create', ['community_id' => $community->id]) }}" 
                   class="mt-4 inline-block px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors duration-200">
                    Create the first post
                </a>
                @endif
                @endauth
            </div>
            @endif
        </div>

        <!-- Pagination -->
        @if(method_exists($community->posts, 'hasPages') && $community->posts->hasPages())
        <div class="py-8">
            {{ $community->posts->links() }}
        </div>
        @endif
    </div>

    <!-- Moderators Section -->
    <div class="mt-8 bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-medium mb-4">Moderators</h2>
        <div class="space-y-2">
            @foreach($moderators as $moderator)
            <div class="flex items-center gap-2">
                <span class="text-gray-700">{{ $moderator['username'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    @else
    <div class="py-12 text-center">
        <p class="text-gray-500 text-xl">Hub not found</p>
        <a href="{{ route('home') }}" 
           class="mt-4 inline-block px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors duration-200">
            Return Home
        </a>
    </div>
    @endif
</div>
@endsection
