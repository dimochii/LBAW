 @if(
        $notification->requestNotification && $notification->requestNotification->request ||
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
        @if($notification->requestNotification && $notification->requestNotification->request)
            <div class="flex items-center hover:text-blue-600 transition-colors">
                <div class="mr-3 w-8 h-8 rounded-full overflow-hidden border-2 border-gray-300 object-cover">
                    <img src="{{ asset($notification->requestNotification->request->community->image->path ?? 'images/groupdefault.jpg') }}"
                        alt="{{ $notification->requestNotification->request->community->name }}"
                        class="w-full h-full object-cover">
                </div>
                <div>
                    New join request on {{ $notification->requestNotification->request->community->name }}:
                    <span class="font-medium ml-1">{{ $notification->requestNotification->request->user->name }}</span>
                </div>
                
                @if($notification->requestNotification->request->request_status === 'pending')
                    <div class="ml-auto flex gap-3">
                        <button
                            class="border-2 border-black px-4 py-2 bg-emerald-400 text-white rounded-lg font-medium shadow-sm hover:bg-emerald-500 hover:shadow-md transform hover:-translate-y-0.5 transition-all duration-200 focus:ring-2 focus:ring-emerald-300 focus:outline-none"
                            onclick="handleFollowRequest('{{ route('community.acceptRequest', $notification->requestNotification->request->id) }}', {{ $notification->id }}, 'accepted')">
                            accept
                        </button>
                        <button
                            class="border-2 border-black px-4 py-2 bg-rose-400 text-white rounded-lg font-medium shadow-sm hover:bg-rose-500 hover:shadow-md transform hover:-translate-y-0.5 transition-all duration-200 focus:ring-2 focus:ring-rose-300 focus:outline-none"
                            onclick="handleFollowRequest('{{ route('community.rejectRequest', $notification->requestNotification->request->id) }}', {{ $notification->id }}, 'rejected')">
                            reject
                        </button>
                    </div>
                @else
                    <div class="ml-auto text-gray-500 italic">
                        Request {{ $notification->requestNotification->request->request_status }}
                    </div>
                @endif
            </div>
            @elseif($notification->postNotification && $notification->postNotification->post)
                <a href="{{ route($notification->postNotification->post->news ? 'news.show' : 'topic.show', $notification->postNotification->post->id) }}"
                   onclick="markAsRead({{ $notification->id }})"
                   class="flex items-center hover:text-blue-600 transition-colors">

                   <!-- Community Avatar -->
                   <div class="mr-3 w-8 h-8 rounded-full overflow-hidden border-2 border-gray-300 object-cover">
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
                   <div class="mr-3 w-8 h-8 rounded-full overflow-hidden border-2 border-gray-300 object-cover">
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
                    <div class="mr-3 w-8 h-8 rounded-full overflow-hidden border-2 border-gray-300 object-cover">
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
                    <div class="mr-3 w-8 h-8 rounded-full overflow-hidden border-2 border-gray-300 object-cover">
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

<script>
    function handleFollowRequest(url, notificationId, action) {
        if (!confirm(`Are you sure you want to ${action} this follow request?`)) {
            return;
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
        })
        .then(response => {
            if (response.ok) {
                document.querySelector(`[data-notification-id="${notificationId}"]`).remove();
                alert(`Follow request ${action}ed successfully.`);
            } else {
                return response.json().then(error => {
                    throw new Error(error.message || 'Failed to process the request.');
                });
            }
        })
        .catch(error => {
            alert(`Error: ${error.message}`);
        });
        }
</script>