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
                    @if($notification->postNotification && $notification->postNotification->post)
                        {{-- Notification for Post --}}
                        <a href="{{ route($notification->postNotification->post->news ? 'news.show' : 'topics.show', $notification->postNotification->post->id) }}">
                            {{ $notification->postNotification->post->title }}
                        </a>
                    @elseif($notification->commentNotification && $notification->commentNotification->comment->post)
                        {{-- Notification for Comment --}}
                        <a href="{{ route($notification->commentNotification->comment->post->news ? 'news.show' : 'topics.show', $notification->commentNotification->comment->post->id) }}">
                            Comment on: {{ $notification->commentNotification->comment->post->title }}
                        </a>
                    @elseif($notification->upvoteNotification && $notification->upvoteNotification->vote->postVote->post)
                        {{-- Notification for Vote --}}
                        <a href="{{ route($notification->upvoteNotification->vote->postVote->post->news ? 'news.show' : 'topics.show', $notification->upvoteNotification->vote->postVote->post->id) }}">
                            Your vote on: {{ $notification->upvoteNotification->vote->postVote->post->title }}
                        </a>
                    @elseif($notification->followNotification && $notification->followNotification->follower)
                        {{-- Notification for Follow --}}
                        <a href="{{ route('user.profile', $notification->followNotification->follower->id) }}">
                            New follower: {{ $notification->followNotification->follower->name }}
                        </a>
                    @else
                        {{-- Fallback for undefined notification types --}}
                        <p>Notification type not recognized.</p>
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
                    @if($notification->postNotification && $notification->postNotification->post)
                        <a href="{{ route($notification->postNotification->post->news ? 'news.show' : 'topics.show', $notification->postNotification->post->id) }}">
                            {{ $notification->postNotification->post->title }}
                        </a>
                    @elseif($notification->commentNotification && $notification->commentNotification->comment->post)
                        <a href="{{ route($notification->commentNotification->comment->post->news ? 'news.show' : 'topics.show', $notification->commentNotification->comment->post->id) }}">
                            Comment on: {{ $notification->commentNotification->comment->post->title }}
                        </a>
                    @elseif($notification->upvoteNotification && $notification->upvoteNotification->vote->postVote && $notification->upvoteNotification->vote->postVote->post)
                        <a href="{{ route($notification->upvoteNotification->vote->postVote->post->news ? 'news.show' : 'topics.show', $notification->upvoteNotification->vote->postVote->post->id) }}">
                            Your vote on: {{ $notification->upvoteNotification->vote->postVote->post->title }}
                        </a>
                    @elseif($notification->followNotification && $notification->followNotification->follower)
                        <a href="{{ route('user.profile', $notification->followNotification->follower->id) }}">
                            New follower: {{ $notification->followNotification->follower->name }}
                        </a>
                    @else
                        <p>Notification type not recognized.</p>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
