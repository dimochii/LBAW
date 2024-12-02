@extends('layouts.app')

@section('content')
    <h1>Your Favorites</h1>

    @if($favorites->isEmpty())
        <p>You don't have any favorite posts yet.</p>
    @else
        <ul>
            @foreach($favorites as $favorite)
                <li>
                    <a href="{{ route('news.show', $favorite->id) }}">{{ $favorite->title }}</a>

                    <!-- Form to remove a specific post from favorites -->
                    <form method="POST" action="{{ url('/unfavorites/' . $favorite->id) }}" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Remove</button>
                    </form>
                </li>
            @endforeach
        </ul>
    @endif

    <!-- Form to test adding a post to favorites -->
    <form method="POST" action="{{ url('/favorites/2') }}">
        @csrf
        <button type="submit" class="btn btn-primary">Add Post 2 to Favorites</button>
    </form>

    <!-- Form to test removing a post from favorites  -->
    <form method="POST" action="{{ url('/unfavorites/2') }}">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Remove Post 2 from Favorites</button>
    </form>
@endsection
