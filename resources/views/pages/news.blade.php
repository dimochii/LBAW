@extends('layouts.app')

@section('content')
<div class="container">
  <!-- Display success message if exists -->
  @if(session('success'))
  <div class="alert alert-success">
    {{ session('success') }}
  </div>
  @endif

  <!-- Button to go to the post creation page -->
  {{-- <div style="margin-bottom: 20px;">
    <a href="{{ route('post.create') }}" class="btn btn-primary">Create New Post</a>
  </div> --}}

  <!-- Check if there are any news items -->
  @if($news->isEmpty())
  <p>No news available.</p>
  @else
  @include('partials.news_grid', ['posts' => $news])
  <div class="divide-y-2 divide-black">
    @foreach($news as $newsItem)
    {{-- <li class="list-group-item d-flex justify-content-between align-items-center">
      <div>
        <a href="{{ url('/news/'.$newsItem->post_id) }}">
          <strong>{{ $newsItem->post->title }}</strong>
        </a>
        <p class="mb-0 text-muted">{{ $newsItem->news_url }}</p>
      </div>
      <div class="d-flex align-items-center">
        <!-- Upvote Button -->
        <form action="{{ route('news.upvote', $newsItem->post_id) }}" method="POST" class="mr-2">
          @csrf
          <button type="submit" class="btn btn-success btn-sm">
            ↑ Upvote ({{ $newsItem->upvotes_count }})
          </button>
        </form>

        <!-- Downvote Button -->
        <form action="{{ route('news.downvote', $newsItem->post_id) }}" method="POST">
          @csrf
          <button type="submit" class="btn btn-danger btn-sm">
            ↓ Downvote ({{ $newsItem->downvotes_count }})
          </button>
        </form>
      </div>
    </li> --}}

    @include('partials.post', [
      'news' => 'true',
      'post' => $newsItem,
    ])
    @endforeach
  </div>
  @endif
</div>
@endsection