@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-12 min-h-screen">
    <div class="max-w-6xl mx-auto">
        <h1 class="tracking-tight font-medium text-5xl mb-8 text-gray-900">
            Best of Topics and News
        </h1>

        @php
            $voteColors = [
                'bg-yellow-300', 'bg-green-400', 'bg-teal-400', 
                'bg-blue-400', 'bg-indigo-400', 'bg-violet-500',
                'bg-pink-400', 'bg-red-400', 'bg-gray-400'
            ];
        @endphp

        {{-- Top Topics Section --}}
        <section class="mb-16">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                Top 10 Topics
                <span class="ml-4 flex-grow h-[4px] bg-gray-300"></span>
            </h2>

            @if ($topTopics->isEmpty())
                <div class="text-center py-6 bg-gray-100 rounded-lg">
                    <p class="text-gray-500">No topics found.</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach ($topTopics as $index => $topic)
                        <div data-post="{{ $topic->post->post_id }}"
                            class="p-4 hover:bg-[#3C3D37] hover:text-[#F4F2ED] transition ease-out group/wrapper h-full w-full flex flex-row border border-2 border-black">
                            <div class="h-full w-full flex-col flex gap-4">
                                <header class="flex items-center relative">
                                    <a class="flex items-center h-8"
                                        href="{{ route('communities.show', ['id' => $topic->post->community->id ?? 'unknown']) }}">
                                        <img src="{{ asset('images/hub' . $topic->post->community->image_id . '.jpg') }}" alt="Community Image"
                                            class="max-w-full rounded-3xl min-w-[32px] mr-3 w-[32px]">

                                        <span class="text-xl font-light underline-effect-light">
                                            h/{{ $topic->post->community->name ?? 'Unknown Community' }}
                                        </span>
                                    </a>
                                </header>

                                <div class="grow">
                                    <a href="{{ route('topic.show', ['post_id' => ($topic->post->id)]) ?? '#' }}">
                                        <p class="text-4xl md:text-5xl lg:text-6xl font-medium tracking-tight line-clamp-4">
                                            {{ $topic->post->title ?? 'No title available' }}
                                        </p>
                                    </a>
                                </div>

                                {{-- Votos e Destaques --}}
                                <div class="mt-4">
                                    <span class="{{ $voteColors[$index % count($voteColors)] }} text-white text-sm px-2 py-1 rounded-md">
                                        {{ $topic->votes_count }} votes
                                    </span>
                                </div>

                                <footer class="flex flex-row mt-auto text-lg gap-2 items-center">
                        

                                    {{-- Additional Info --}}
                                    <span class="ml-auto text-sm text-gray-500 group-hover/wrapper:text-gray-300">
                                        Reviewed on: {{ $topic->review_date ? $topic->review_date->format('F d, Y') : 'Pending Review' }}
                                    </span>
                                </footer>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Top News Section --}}
        <section>
            <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                Top 10 News
                <span class="ml-4 flex-grow h-[4px] bg-gray-300"></span>
            </h2>
            @if ($topNews->isEmpty())
                <div class="text-center py-6 bg-gray-100 rounded-lg">
                    <p class="text-gray-500">No news found.</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach ($topNews as $index => $news)
                        <div data-post="{{ $news->post->post_id }}"
                            class="p-4 hover:bg-[#3C3D37] hover:text-[#F4F2ED] transition ease-out group/wrapper h-full w-full flex flex-row border border-2 border-black">
                            <div class="h-full w-full flex-col flex gap-4">
                                <header class="flex items-center relative">
                                    <a class="flex items-center h-8"
                                        href="{{ route('communities.show', ['id' => $news->post->community->id ?? 'unknown']) }}">
                                        <img src="{{ asset('images/hub' . $news->post->community->image_id . '.jpg') }}" alt="Community Image"
                                            class="max-w-full rounded-3xl min-w-[32px] mr-3 w-[32px]">

                                        <span class="text-xl font-light underline-effect-light">
                                            h/{{ $news->post->community->name ?? 'Unknown Community' }}
                                        </span>
                                    </a>
                                </header>

                                <div class="grow">
                                    <a href="{{ route('news.show', ['post_id' => ($news->post->id)]) ?? '#' }}"
                                        class="inline my-4 text-4xl md:text-5xl lg:text-6xl font-medium tracking-tight line-clamp-4 overflow-visible">
                                        {{ $news->post->title ?? 'No title available' }}
                                    </a>

                                    @if ($news->news_url)
                                        <a href="{{ $news->news_url }}"
                                            class="inline ml-2 text-sm lg:text-base text-gray-500 group-hover/wrapper:text-gray-300 underline-effect-light"
                                            data-content="news-url">{{ $news->news_url }}</a>
                                    @endif
                                </div>

                                {{-- Votos e Destaques --}}
                                <div class="mt-4">
                                    <span class="{{ $voteColors[$index % count($voteColors)] }} text-white text-sm px-2 py-1 rounded-md">
                                        {{ $news->votes_count }} votes
                                    </span>
                                </div>

                                <footer class="flex flex-row mt-auto text-lg gap-2 items-center">

                                    {{-- Additional Info --}}
                                    <span class="ml-auto text-sm text-gray-500 group-hover/wrapper:text-gray-300">
                                        Posted on: {{ $news->post->creation_date->format('F d, Y') }}
                                    </span>
                                </footer>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
