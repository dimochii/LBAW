@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ $user->name }}'s Profile</h1>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Username: {{ $user->username }}</h5>
            <p class="card-text"><strong>Email:</strong> {{ $user->email }}</p>
            <p class="card-text"><strong>Birth Date:</strong> {{ $user->birth_date }}</p>
            <p class="card-text"><strong>Description:</strong> {{ $user->description ?? 'No description provided.' }}</p>
            
            @if ($user->image_id)
                <p class="card-text">
                    <strong>Profile Image:</strong>
                </p>
                <img src="{{ asset('images/' . $user->image_id . '.jpg') }}" alt="Profile Image" style="max-width: 200px;">
            @else
                <p class="card-text"><strong>Profile Image:</strong> Not uploaded</p>
            @endif

            <div class="mt-3">
                <a href="{{ url('/users/' . $user->id . '/posts') }}" class="btn btn-primary">View Authored Posts</a>
                <a href="{{ url('/users/' . $user->id . '/communities') }}" class="btn btn-secondary">View Followed Communities</a>
            </div>
        </div>
    </div>
</div>
@endsection
