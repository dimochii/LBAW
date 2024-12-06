@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="text-center my-4">Best of Topics and News</h1>

    {{-- Top Topics Section --}}
    <div class="my-5">
        <h2 class="mb-3">Top 10 Topics</h2>
        @if ($topTopics->isEmpty())
            <p>No topics found.</p>
        @else
            <ul class="list-group">
                @foreach ($topTopics as $topic)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">{{ $topic->post->title }}</h5>
                            <small class="text-muted">Reviewed on: {{ $topic->review_date ?? 'Pending Review' }}</small>
                        </div>
                        <span class="badge bg-primary rounded-pill">{{ $topic->votes_count }} votes</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Top News Section --}}
    <div class="my-5">
        <h2 class="mb-3">Top 10 News</h2>
        @if ($topNews->isEmpty())
            <p>No news found.</p>
        @else
            <ul class="list-group">
                @foreach ($topNews as $news)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1"><a href="{{ $news->news_url }}" target="_blank">{{ $news->post->title }}</a></h5>
                            <small class="text-muted">Posted on: {{ $news->post->creation_date->format('F d, Y') }}</small>
                        </div>
                        <span class="badge bg-primary rounded-pill">{{ $news->votes_count }} votes</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
