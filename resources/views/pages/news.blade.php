@extends('layouts.app')

@section('content')
<div class="container">
    <h1>News List</h1>

    <!-- Display success message if exists -->
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <!-- Button to go to the post creation page -->
    <div style="margin-bottom: 20px;">
        <a href="{{ route('post.create') }}" class="btn btn-primary">Create New Post</a>
    </div>
    

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
