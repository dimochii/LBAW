@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ $user->name }}'s Followers</h1>

    @if ($followers->isEmpty())
        <p>No followers found.</p>
    @else
        <ul class="list-group">
            @foreach ($followers as $follower)
                <li class="list-group-item">
                    <a href="{{ route('user.profile', $follower->id) }}">{{ $follower->name }} ({{ $follower->username }})</a>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
