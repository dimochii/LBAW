<tr class="hover:bg-gray-50 transition-colors">
    <td class="px-4 py-4">
        <strong class="text-sm text-gray-600">{{ $notification->notification_date }}</strong>
    </td>
    <td class="px-4 py-4 w-full">
        <!-- Render notification content based on type -->
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
        @if($notification->read)
            <span class="inline-block w-2 h-2 bg-green-500 rounded-full"></span> <!-- Read status -->
        @else
            <span class="inline-block w-2 h-2 bg-blue-500 rounded-full animate-pulse"></span> <!-- Unread status -->
        @endif
    </td>
</tr>
