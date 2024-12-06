@extends('layouts.app')

@section('content')
<div>
    <!-- Hub Header -->
    @if($community)
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 lg:px-8">
        <div class="flex items-start gap-6">
            <img src="{{ asset('images/hub' . $community->image_id . '.jpg') }}" alt="Profile Image"
                 class="rounded-full ring-2 ring-black h-32 w-32">
            <div>
            <div class="flex items-center gap-2">
    <!-- Community Name -->
    <h1 class="tracking-tighter font-medium text-6xl">/{{ $community->name }}</h1>

    <!-- Form for Privacy Toggle -->
    <form action="{{ route('communities.update.privacy', $community) }}" method="POST" class="inline-flex items-center gap-2">
        @csrf
        @method('POST')

        <!-- Current Privacy Badge -->
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-full {{ $community->privacy ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
            @if($community->privacy)
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                private
            @else
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    <path d="M7 11h10"></path>
                </svg>
                public
            @endif
        </span>

        <!-- Dropdown for Privacy Selection -->
        <select name="privacy" id="privacy-dropdown" onchange="this.form.submit()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium border rounded-full {{ $community->privacy === 'private' ? 'border-red-300' : 'border-green-300' }} focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500">
            <option value="public" {{ $community->privacy === 'public' ? 'selected' : '' }}>public</option>
            <option value="private" {{ $community->privacy === 'private' ? 'selected' : '' }}>private</option>
        </select>
    </form>
</div>

                <p class="text-gray-600 mt-2 text-sm">{{ $community->description }}</p>
                <div class="flex items-center gap-4 mt-3 text-sm text-gray-500">
                    <div class="flex items-center gap-2">
                        <span>{{ number_format($followers_count ?? 0, 0) }}</span>
                        <span>Readers</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span>{{ number_format($posts_count ?? 0, 0) }}</span>
                        <span>Posts</span>
                    </div>
                </div>

                <!-- Sort by and + post button -->
                <div class="flex items-center gap-4 mt-6">
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-600">sort by</span>
                        <select name="sort" class="bg-transparent text-sm text-gray-900 font-medium focus:outline-none">
                            <option value="newest">Newest</option>
                            <option value="top">Top</option>
                            <option value="trending">Trending</option>
                        </select>
                    </div>
                    @auth
                    @if($is_following)
                    <a href="{{ route('post.create') }}"
                       class="px-4 text-gray-600 text-sm font-medium underline-effect">
                        + post
                    </a>
                    @endif
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <!-- Posts Section -->
    <div>
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
                @if($is_following)
                <a href="{{ route('post.create', ['community_id' => $community->id]) }}" 
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
        <h2 class="text-lg font-semibold mb-4">Moderators</h2>
        <div class="space-y-3">
            @foreach($community->moderators as $moderator)
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-gray-100 overflow-hidden">
                    <img src="{{ $moderator->avatar ?? 'https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png' }}" 
                         alt="{{ $moderator->username }}"
                         class="w-full h-full object-cover">
                </div>
                <span class="text-sm text-gray-700">{{ $moderator['username'] }}</span> 
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
