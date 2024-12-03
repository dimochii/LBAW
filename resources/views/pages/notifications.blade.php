@extends('layouts.app')

@section('content')
<div class="p-4">
    <h1 class="text-xl font-bold mb-4 flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        Notifications
    </h1>

    <div class="space-y-6">
        <div>
            <h2 class="text-lg font-semibold mb-2 text-gray-700">Unread Notifications</h2>
            
            @if($unreadNotifications->isEmpty())
                <div class="bg-white border-2 border-black/10 rounded-lg p-4 text-center text-gray-500">
                    No unread notifications.
                </div>
            @else
                <table class="w-full bg-white border-2 border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30">
                    <tbody class="divide-y divide-gray-200">
                        @foreach($unreadNotifications as $notification)
                            @if(!($notification->postNotification && $notification->postNotification->post) &&
                                !($notification->commentNotification && $notification->commentNotification->comment->post) &&
                                !($notification->upvoteNotification && $notification->upvoteNotification->vote->postVote->post) &&
                                !($notification->followNotification && $notification->followNotification->follower))
                                @continue
                            @endif
                            
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-4">
                                    <strong class="text-sm text-gray-600">{{ $notification->notification_date }}</strong>
                                </td>
                                <td class="px-4 py-4 w-full">
                                    @if($notification->postNotification && $notification->postNotification->post)
                                        <a href="{{ route($notification->postNotification->post->news ? 'news.show' : 'topics.show', $notification->postNotification->post->id) }}"
                                           onclick="markAsRead({{ $notification->id }})" 
                                           class="hover:text-blue-600 transition-colors">
                                            New post on {{ $notification->postNotification->post->community->name }}: 
                                            <span class="font-medium">{{ $notification->postNotification->post->title }}</span>
                                        </a>
                                    @elseif($notification->commentNotification && $notification->commentNotification->comment->post)
                                        <a href="{{ route($notification->commentNotification->comment->post->news ? 'news.show' : 'topics.show', $notification->commentNotification->comment->post->id) }}"
                                           onclick="markAsRead({{ $notification->id }})"
                                           class="hover:text-blue-600 transition-colors">
                                            Comment on: 
                                            <span class="font-medium">{{ $notification->commentNotification->comment->post->title }}</span>
                                        </a>
                                    @elseif($notification->upvoteNotification && $notification->upvoteNotification->vote->postVote->post)
                                        <a href="{{ route($notification->upvoteNotification->vote->postVote->post->news ? 'news.show' : 'topics.show', $notification->upvoteNotification->vote->postVote->post->id) }}"
                                           onclick="markAsRead({{ $notification->id }})"
                                           class="flex items-center hover:text-blue-600 transition-colors">
                                            <div class="mr-3 w-8 h-8 rounded-full overflow-hidden border-2 border-gray-300">
                                                <img src="{{ $notification->upvoteNotification->vote->user->avatar ?? 'default-avatar.png' }}" 
                                                     alt="{{ $notification->upvoteNotification->vote->user->username }}"
                                                     class="w-full h-full object-cover">
                                            </div>
                                            {{ $notification->upvoteNotification->vote->user->username }} upvoted: 
                                            <span class="font-medium ml-1">{{ $notification->upvoteNotification->vote->postVote->post->title }}</span>
                                        </a>
                                    @elseif($notification->followNotification && $notification->followNotification->follower)
                                        <a href="{{ route('user.profile', $notification->followNotification->follower->id) }}"
                                           onclick="markAsRead({{ $notification->id }})"
                                           class="flex items-center hover:text-blue-600 transition-colors">
                                            <div class="mr-3 w-8 h-8 rounded-full overflow-hidden border-2 border-gray-300">
                                                <img src="{{ $notification->followNotification->follower->avatar ?? 'default-avatar.png' }}" 
                                                     alt="{{ $notification->followNotification->follower->name }}"
                                                     class="w-full h-full object-cover">
                                            </div>
                                            New follower: 
                                            <span class="font-medium ml-1">{{ $notification->followNotification->follower->name }}</span>
                                        </a>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-block w-2 h-2 bg-blue-500 rounded-full animate-pulse"></span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div>
            <h2 class="text-lg font-semibold mb-2 text-gray-700">Read Notifications</h2>
            
            @if($readNotifications->isEmpty())
                <div class="bg-white border-2 border-black/10 rounded-lg p-4 text-center text-gray-500">
                    No read notifications.
                </div>
            @else
                <table class="w-full bg-white border-2 border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30">
                    <tbody class="divide-y divide-gray-200">
                        @foreach($readNotifications as $notification)
                            @if(!($notification->postNotification && $notification->postNotification->post) &&
                                !($notification->commentNotification && $notification->commentNotification->comment->post) &&
                                !($notification->upvoteNotification && $notification->upvoteNotification->vote->postVote->post) &&
                                !($notification->followNotification && $notification->followNotification->follower))
                                @continue
                            @endif
                            
                            <tr class="hover:bg-gray-50 transition-colors text-gray-600">
                                <td class="px-4 py-4">
                                    <strong class="text-sm">{{ $notification->notification_date }}</strong>
                                </td>
                                <td class="px-4 py-4 w-full">
                                    @if($notification->postNotification && $notification->postNotification->post)
                                        <a href="{{ route($notification->postNotification->post->news ? 'news.show' : 'topics.show', $notification->postNotification->post->id) }}"
                                           class="hover:text-blue-600 transition-colors">
                                            New post on {{ $notification->postNotification->post->community->name }}: 
                                            <span class="font-medium">{{ $notification->postNotification->post->title }}</span>
                                        </a>
                                    @elseif($notification->commentNotification && $notification->commentNotification->comment->post)
                                        <a href="{{ route($notification->commentNotification->comment->post->news ? 'news.show' : 'topics.show', $notification->commentNotification->comment->post->id) }}"
                                           class="hover:text-blue-600 transition-colors">
                                            Comment on: 
                                            <span class="font-medium">{{ $notification->commentNotification->comment->post->title }}</span>
                                        </a>
                                    @elseif($notification->upvoteNotification && $notification->upvoteNotification->vote->postVote->post)
                                        <a href="{{ route($notification->upvoteNotification->vote->postVote->post->news ? 'news.show' : 'topics.show', $notification->upvoteNotification->vote->postVote->post->id) }}"
                                           class="flex items-center hover:text-blue-600 transition-colors">
                                            <div class="mr-3 w-8 h-8 rounded-full overflow-hidden border-2 border-gray-300">
                                                <img src="{{ $notification->upvoteNotification->vote->user->avatar ?? 'default-avatar.png' }}" 
                                                     alt="{{ $notification->upvoteNotification->vote->user->username }}"
                                                     class="w-full h-full object-cover">
                                            </div>
                                            {{ $notification->upvoteNotification->vote->user->username }} upvoted: 
                                            <span class="font-medium ml-1">{{ $notification->upvoteNotification->vote->postVote->post->title }}</span>
                                        </a>
                                    @elseif($notification->followNotification && $notification->followNotification->follower)
                                        <a href="{{ route('user.profile', $notification->followNotification->follower->id) }}"
                                           class="flex items-center hover:text-blue-600 transition-colors">
                                            <div class="mr-3 w-8 h-8 rounded-full overflow-hidden border-2 border-gray-300">
                                                <img src="{{ $notification->followNotification->follower->avatar ?? 'default-avatar.png' }}" 
                                                     alt="{{ $notification->followNotification->follower->name }}"
                                                     class="w-full h-full object-cover">
                                            </div>
                                            New follower: 
                                            <span class="font-medium ml-1">{{ $notification->followNotification->follower->name }}</span>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

<script>
    function markAsRead(notificationId) {
        fetch(`/notifications/${notificationId}/read`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            },
        }).then(response => {
            if (response.ok) {
                location.reload(); 
            }
        });
    }
</script>
@endsection