 @if(
        $notification->postNotification && $notification->postNotification->post ||
        $notification->commentNotification && $notification->commentNotification->comment->post ||
        $notification->upvoteNotification && $notification->upvoteNotification->vote->postVote->post ||
        $notification->followNotification && $notification->followNotification->follower
    )
    <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-4 py-4">
            <strong class="text-sm text-gray-600">{{ $notification->notification_date }}</strong>
        </td>
        <td class="px-4 py-4 w-full">
            @if($notification->postNotification && $notification->postNotification->post)
                <a href="{{ route($notification->postNotification->post->news ? 'news.show' : 'topics.show', $notification->postNotification->post->id) }}"
                   onclick="markAsRead({{ $notification->id }})"
                   class="flex items-center hover:text-blue-600 transition-colors">

                   <!-- Community Avatar -->
                   <div class="mr-3 w-8 h-8 rounded-full overflow-hidden border-2 border-gray-300">
                        <img src="{{ asset($notification->postNotification->post->community->image->path ?? 'images/groupdefault.jpg' )}}"
                             alt="{{ $notification->postNotification->post->community->name }}"
                             class="w-full h-full object-cover">
                    </div>

                    <!-- Post Title and Community Name -->
                    New post on {{ $notification->postNotification->post->community->name }}: 
                    <span class="font-medium ml-1">{{ $notification->postNotification->post->title }}</span>
                </a> 
            @elseif($notification->commentNotification && $notification->commentNotification->comment->post)
                <a href="{{ route($notification->commentNotification->comment->post->news ? 'news.show' : 'topics.show', $notification->commentNotification->comment->post->id) }}"
                   onclick="markAsRead({{ $notification->id }})"
                   class="flex items-center hover:text-blue-600 transition-colors">
                   <div class="mr-3 w-8 h-8 rounded-full overflow-hidden border-2 border-gray-300">
                        <img src="{{ asset($notification->commentNotification->comment->user->image->path ?? '/images/default.jpg')}}" 
                             alt="{{ $notification->commentNotification->comment->user->username }}"
                             class="w-full h-full object-cover">
                    </div> 
                    <!-- Ainda nao testei este-->
                    <div>
                        <span class="font-medium">{{ $notification->commentNotification->comment->user->username }}</span> commented on: 
                        <span class="font-medium">{{ $notification->commentNotification->comment->post->title }}</span>
                        <br>
                        <span class="text-gray-500 text-sm">{{ Str::limit($notification->commentNotification->comment->body, 50, '...') }}</span>
                    </div>
                </a>
            @elseif($notification->upvoteNotification && $notification->upvoteNotification->vote->postVote->post)
                <a href="{{ route($notification->upvoteNotification->vote->postVote->post->news ? 'news.show' : 'topics.show', $notification->upvoteNotification->vote->postVote->post->id) }}"
                   onclick="markAsRead({{ $notification->id }})"
                   class="flex items-center hover:text-blue-600 transition-colors">
                    <div class="mr-3 w-8 h-8 rounded-full overflow-hidden border-2 border-gray-300">
                        <img src="{{ asset($notification->upvoteNotification->vote->user->image->path ?? '/images/default.jpg')}}"                             alt="{{ $notification->upvoteNotification->vote->user->username }}"
                             class="w-full h-full object-cover">
                    </div>
                    {{ $notification->upvoteNotification->vote->user->username }} upvoted: 
                    <span class="font-medium ml-1">{{ $notification->upvoteNotification->vote->postVote->post->title }}</span>
                </a>
                <p></p>
            @elseif($notification->followNotification && $notification->followNotification->follower)
                <a href="{{ route('user.profile', $notification->followNotification->follower->id) }}"
                   onclick="markAsRead({{ $notification->id }})"
                   class="flex items-center hover:text-blue-600 transition-colors">
                    <div class="mr-3 w-8 h-8 rounded-full overflow-hidden border-2 border-gray-300">
                        <img src="{{ asset($notification->followNotification->follower->image->path ?? '/images/default.jpg')}}"
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
    @endif