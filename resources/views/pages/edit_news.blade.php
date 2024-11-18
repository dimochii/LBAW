@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4 text-primary">Edit News</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('news.update', $newsItem->post->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Title -->
        <div class="form-group">
            <label for="title" class="form-label">Title</label>
            <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $newsItem->post->title) }}" required>
        </div>

        <!-- Content -->
        <div class="form-group">
            <label for="content" class="form-label">Content</label>
            <textarea name="content" id="content" class="form-control" rows="5" required>{{ old('content', $newsItem->post->content) }}</textarea>
        </div>

        <!-- URL -->
        <div class="form-group">
            <label for="news_url" class="form-label">News URL</label>
            <input type="url" name="news_url" id="news_url" class="form-control" value="{{ old('news_url', $newsItem->news_url) }}" required>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Save Changes</button>
    </form>
</div>
@endsection
