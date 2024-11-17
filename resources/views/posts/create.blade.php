@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create a New Post</h1>

    <form method="POST" action="{{ route('post.store') }}">
        @csrf

        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}" required>
            @if ($errors->has('title'))
                <small class="text-danger">{{ $errors->first('title') }}</small>
            @endif
        </div>

        <div class="form-group">
            <label for="content">Content</label>
            <textarea class="form-control" id="content" name="content" rows="5" required>{{ old('content') }}</textarea>
            @if ($errors->has('content'))
                <small class="text-danger">{{ $errors->first('content') }}</small>
            @endif
        </div>

        <div class="form-group">
            <label for="type">Post Type</label>
            <select class="form-control" id="type" name="type" required>
                <option value="news" {{ old('type') == 'news' ? 'selected' : '' }}>News</option>
                <option value="topic" {{ old('type') == 'topic' ? 'selected' : '' }}>Topic</option>
            </select>
            @if ($errors->has('type'))
                <small class="text-danger">{{ $errors->first('type') }}</small>
            @endif
        </div>

        <div class="form-group" id="news-url-group" style="display: none;">
            <label for="news_url">News URL</label>
            <input type="url" class="form-control" id="news_url" name="news_url" value="{{ old('news_url') }}">
            @if ($errors->has('news_url'))
                <small class="text-danger">{{ $errors->first('news_url') }}</small>
            @endif
        </div>

        <button type="submit" class="btn btn-success">Create Post</button>
    </form>
</div>

<script>
    // Show or hide the "news_url" field based on the selected type
    document.getElementById('type').addEventListener('change', function () {
        const newsUrlGroup = document.getElementById('news-url-group');
        if (this.value === 'news') {
            newsUrlGroup.style.display = 'block';
        } else {
            newsUrlGroup.style.display = 'none';
        }
    });

    // Initialize the form
    document.getElementById('type').dispatchEvent(new Event('change'));
</script>
@endsection
