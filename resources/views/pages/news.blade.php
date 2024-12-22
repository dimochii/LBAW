@extends('layouts.app')

@section('content')
<div class="">
  <!-- Display success message if exists -->
  {{-- @if(session('success'))
  <div class="alert alert-success">
    {{ session('success') }}
  </div>
  @endif --}}
  <div class="divide-y-2 divide-black border-b-2 border-black">
    @php
    $activeTab = request()->query('tab', 'News'); // Default to 'News'
    @endphp

    @include('partials.news_topic_nav', ['url' => '/news/'])

    @if ($activeTab === 'News')
    @if($news->isEmpty())
    <p>No news available.</p>
    @else
    @include('partials.news_grid', ['posts' => $news->take(6)])
    <div class="divide-y-2 divide-black border-b-2 border-black">
      @foreach($news->slice(6) as $post)
      @include('partials.post', [
      'news' => 'true',
      'post' => $post->news,
      'item' => $post
      ])
      @endforeach
    </div>
    @endif
    @elseif ($activeTab === 'Topics')
    @if($topics->isEmpty())
    <p>No topics available.</p>
    @else
    <div class="divide-y-2 divide-black border-b-2 border-black">
      @foreach($topics as $topic)
      @include('partials.post', ['news' => false, 'post' => $topic->topic, 'img' => false, 'item' => $topic])
      @endforeach
    </div>
    @endif

    @endif
  </div>
</div>



@endsection