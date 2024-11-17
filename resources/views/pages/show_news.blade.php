@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <div class="d-flex flex-wrap">
                <!-- Loop through all authors -->
                @foreach ($newsItem->post->authors as $author)
                    <div class="mr-3 mb-2 d-flex align-items-center">
                        <!-- Display User Image -->
                        <img src="{{ $author->image_id ?? '/images/default-profile.png' }}" alt="User Image" class="rounded-circle" width="40" height="40">
                        <div class="ml-2">
                            <!-- Display Username -->
                            <strong>{{ $author->username ?? 'Unknown' }}</strong>
                        </div>
                    </div>
                @endforeach

                <!-- Published date -->
                <small class="text-muted"> | Published on {{ $newsItem->post->creation_date->format('F j, Y') ?? 'Unknown date' }}</small>
            </div>
        </div>

        <div class="card-body">
            <!-- Title Section -->
            <h1 class="mb-3 text-primary">{{ $newsItem->post->title ?? 'No title available' }}</h1>

            <!-- Description Section -->
            <h5 class="card-title text-secondary">Description</h5>
            <p class="card-text">{{ $newsItem->post->content ?? 'No description available' }}</p>

            <!-- News URL Section -->
            <h5 class="card-title text-secondary">News URL</h5>
            <p class="card-text">
                <a href="{{ $newsItem->news_url ?? '#' }}" target="_blank" class="text-primary">{{ $newsItem->news_url ?? 'No URL available' }}</a>
            </p>

            <!-- Edit Button (only if the current authenticated user is an author) -->
            @auth
                <!-- Check if the authenticated user is one of the authors -->
                @if ($newsItem->post->authors->contains('id', Auth::user()->id))
                    <a href="{{ route('post.create') }}" class="btn btn-warning mt-3">Edit Post</a>
                @endif
            @endauth
        </div>
    </div>
</div>
@endsection
