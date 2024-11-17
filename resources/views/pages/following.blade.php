@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ $user->name }}'s Following</h1>

    @if ($following->isEmpty())
        <p>Not following anyone yet.</p>
    @else
        <ul class="list-group">
            @foreach ($following as $followedUser)
                <li class="list-group-item">
                    <a href="{{ route('user.profile', $followedUser->id) }}">{{ $followedUser->name }} ({{ $followedUser->username }})</a>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
