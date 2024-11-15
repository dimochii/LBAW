
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>News List</h1>

    <!-- Check if there are any news items -->
    @if($news->isEmpty())
        <p>No news available.</p>
    @else
        <ul>
            @foreach($news as $newsItem)
                <li>
                    <a href="{{ url('/news/'.$newsItem->post_id) }}">
                        {{ $newsItem->post->title }}
                    </a>
                    - {{ $newsItem->news_url }}
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
