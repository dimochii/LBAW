@extends('layouts.app')

@section('content')
<div class="divide-y-2 divide-black border-b-2 border-black">
  <!-- Display success message if exists -->
  {{-- @if(session('success'))
  <div class="alert alert-success">
    {{ session('success') }}
  </div>
  @endif --}}

  <!-- Button to go to the post creation page -->
  {{-- <div style="margin-bottom: 20px;">
    <a href="{{ route('post.create') }}" class="btn btn-primary">Create New Post</a>
  </div> --}}

  <!-- Check if there are any news items -->

  @php
  $activeTab = request()->query('tab', 'News'); // Default to 'News'
  @endphp

  @include('partials.news_topic_nav', ['url' => '/recent/'])
  @if ($activeTab === 'News')
  @if($news->isEmpty())
  <p>No news available.</p>
  @else
  @foreach($news as $post)
  @include('partials.post', [
  'news' => 'true',
  'post' => $post->news,
  'item' => $post,
  ])
  @endforeach
</div>
@endif
@elseif ($activeTab === 'Topics')
@if($topics->isEmpty())
<p>No topics available.</p>
@else
@foreach($topics as $post)
@include('partials.post', ['news' => false, 'post' => $post->topic, 'img' => false, 'item' => $post])
@endforeach
</div>
@endif
@endif

</div>
@endsection