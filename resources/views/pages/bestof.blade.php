@extends('layouts.app')
@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-3xl font-bold text-center my-6">Best of Topics and News</h1>

    {{-- Top Topics Section --}}
    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Top 10 Topics</h2>
        @if ($topTopics->isEmpty())
            <p class="text-gray-500">No topics found.</p>
        @else
            <div class="space-y-4">
                @foreach ($topTopics as $index => $topic)
                    <div class="bg-white border rounded-lg p-4 flex items-center justify-between hover:bg-gray-50 transition">
                        <div class="flex-grow pr-4">
                            <div class="flex items-center space-x-3">
                                <span class="text-xl font-bold text-gray-400">{{ $index + 1 }}</span>
                                <h3 class="text-lg font-medium text-gray-800">{{ $topic->post->title }}</h3>
                            </div>
                            <div class="mt-2 text-sm text-gray-500">
                                Reviewed on: {{ $topic->review_date ? $topic->review_date->format('F d, Y') : 'Pending Review' }}
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">
                                {{ $topic->votes_count }} votes
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Top News Section --}}
    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Top 10 News</h2>
        @if ($topNews->isEmpty())
            <p class="text-gray-500">No news found.</p>
        @else
            <div class="space-y-4">
                @foreach ($topNews as $index => $news)
                    <div class="bg-white border rounded-lg p-4 flex items-center justify-between hover:bg-gray-50 transition">
                        <div class="flex-grow pr-4">
                            <div class="flex items-center space-x-3">
                                <span class="text-xl font-bold text-gray-400">{{ $index + 1 }}</span>
                                <a href="{{ $news->news_url }}" target="_blank" class="text-lg font-medium text-blue-600 hover:underline">
                                    {{ $news->post->title }}
                                </a>
                            </div>
                            <div class="mt-2 text-sm text-gray-500">
                                Posted on: {{ $news->post->creation_date->format('F d, Y') }}
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">
                                {{ $news->votes_count }} votes
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection