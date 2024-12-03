@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Your Notifications</h1>

    <h2>Unread Notifications</h2>
    @if($unreadNotifications->isEmpty())
        <p>No unread notifications.</p>
    @else
        <ul>
            @foreach($unreadNotifications as $notification)
                <li>
                    <strong>{{ $notification->notification_date }}</strong>: 
                    @if($notification->postNotification)
                        New post in a community you follow: 
                            {{ $notification->postNotification->post->title }}
                        </a>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif

    <h2>Read Notifications</h2>
    @if($readNotifications->isEmpty())
        <p>No read notifications.</p>
    @else
        <ul>
            @foreach($readNotifications as $notification)
                <li>
                    <strong>{{ $notification->notification_date }}</strong>: 
                    @if($notification->postNotification)
                        New post in a community you follow: 
                        <a href="{{ route('posts.show', $notification->postNotification->post->id) }}">
                            {{ $notification->postNotification->post->title }}
                        </a>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
