@extends('layouts.app')

@section('content')
<div class="divide-y-2 divide-black border-b-2 border-black">
  <!-- Display success message if exists -->
  @if(session('success'))
  <div class="alert alert-success">
    {{ session('success') }}
  </div>
  @endif

 

  @php
  $activeTab = request()->query('tab', 'News'); // Default to 'News'
  @endphp

  @include('partials.news_topic_nav', ['url' => '/home/'])
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