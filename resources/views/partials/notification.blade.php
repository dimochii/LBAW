<tr class="hover:bg-gray-50 transition-colors">
    <td class="px-4 py-4">
        <strong class="text-sm text-gray-600">{{ $notification->notification_date }}</strong>
    </td>
    <td class="px-4 py-4 w-full">
        @if($notification->postNotification && $notification->postNotification->post)
            <a href="{{ route($notification->postNotification->post->news ? 'news.show' : 'topics.show', $notification->postNotification->post->id) }}"
               onclick="markAsRead({{ $notification->id }})"
               class="flex items-center hover:text-blue-600 transition-colors">
                <!-- Newspaper Icon for Post -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18V3H3v1zm0 5h18v-1H3v1zm0 5h18v-1H3v1zm0 5h18v-1H3v1z" />
                </svg>
                New post on {{ $notification->postNotification->post->community->name }}: 
                <span class="font-medium">{{ $notification->postNotification->post->title }}</span>
            </a> 		
        @elseif($notification->commentNotification && $notification->commentNotification->comment->post)
            <a href="{{ route($notification->commentNotification->comment->post->news ? 'news.show' : 'topics.show', $notification->commentNotification->comment->post->id) }}"
               onclick="markAsRead({{ $notification->id }})"
               class="flex items-center hover:text-blue-600 transition-colors">
                <!-- Comment Balloon Icon -->
                <svg
                    class="cursor-pointer ml-4 h-5 min-w-5 hover:fill-blue-400 transition-all ease-out fill-[#3C3D37] group-hover/wrapper:fill-[#F4F2ED] group-hover/wrapper:hover:fill-blue-400 px-4"
                    viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                    <g id="icons_Q2" data-name="icons Q2">
                        <path
                        d="M42,4H6A2,2,0,0,0,4,6V42a2,2,0,0,0,2,2,2,2,0,0,0,1.4-.6L15.2,36H42a2,2,0,0,0,2-2V6a2,2,0,0,0-2-2Z" />
                    </g>
                </svg>
                Comment on: 
                <span class="font-medium">{{ $notification->commentNotification->comment->post->title }}</span>
            </a>
        @elseif($notification->upvoteNotification && $notification->upvoteNotification->vote->postVote->post)
            <a href="{{ route($notification->upvoteNotification->vote->postVote->post->news ? 'news.show' : 'topics.show', $notification->upvoteNotification->vote->postVote->post->id) }}"
               onclick="markAsRead({{ $notification->id }})"
               class="flex items-center hover:text-blue-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9l6 6 6-6" />
                </svg>
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
            <span class="inline-block w-2 h-2 bg-green-500 rounded-full"></span> 
        @else
            <span class="inline-block w-2 h-2 bg-blue-500 rounded-full animate-pulse"></span> 
        @endif
    </td>
</tr>
