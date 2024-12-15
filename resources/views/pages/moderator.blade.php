@extends('layouts.admin')

@section('content')

<div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Moderator Dashboard</h1>

    <!-- Dropdown for Community Selection -->
    <form method="GET" action="{{ route('user.moderator') }}" class="mb-6">
        <label for="community-select" class="block mb-2 font-medium text-gray-700">Select a Community:</label>
        <select name="hub_id" id="community-select" class="w-full p-2 border border-gray-300 rounded-lg">
            @foreach ($moderated_hubs as $hub)
                <option value="{{ $hub->id }}" {{ request('hub_id') == $hub->id ? 'selected' : '' }}>
                    {{ $hub->name }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
            View Community
        </button>
    </form>

    @if ($selected_hub)
        <!-- Users Table -->
        <div class="mb-8">
            <h2 class="text-xl font-bold mb-4">Moderators and Followers in {{ $selected_hub->name }}</h2>
            <table class="w-full bg-white border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Handle</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Moderators -->
                    @foreach ($selected_hub->moderators as $moderator)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-4">Moderator</td>
                            <td class="px-4 py-4">{{ '@' . $moderator->username }}</td>
                            <td class="px-4 py-4">{{ $moderator->name }}</td>
                        </tr>
                    @endforeach
                    <!-- Followers -->
                    @foreach ($selected_hub->followers as $follower)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-4">Follower</td>
                            <td class="px-4 py-4">{{ '@' . $follower->username }}</td>
                            <td class="px-4 py-4">{{ $follower->name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Posts Table -->
        <div class="mb-8">
            <h2 class="text-xl font-bold mb-4">Posts in {{ $selected_hub->name }}</h2>
            <table class="w-full bg-white border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Content</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Upvotes</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Downvotes</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Score</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($selected_hub->posts as $post)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-4">{{ $post->id }}</td>
                            <td class="px-4 py-4">{{ $post->title }}</td>
                            <td class="px-4 py-4">{{ Str::limit($post->content, 50) }}</td>
                            <td class="px-4 py-4">{{ $post->upvote_count }}</td>
                            <td class="px-4 py-4">{{ $post->downvote_count }}</td>
                            <td class="px-4 py-4">{{ $post->score }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-gray-600">Select a community to view its details.</p>
    @endif
</div>

@endsection


<script>
function toggleModerator(userId, communityId, isChecked) {
    const action = isChecked ? 'make_moderator' : 'remove_moderator';
    const confirmationMessage = isChecked
        ? 'Are you sure you want to grant this user moderator privileges in this community?'
        : 'Are you sure you want to revoke this user\'s moderator privileges in this community?';

    if (confirm(confirmationMessage)) {
        fetch(`/hub/${communityId}/${action}/${userId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({})
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to update moderator status.');
            }
            return response.json();
        })
        .then(data => {
            alert(data.message);
        })
        .catch(error => {
            alert(error.message);
            document.getElementById(`moderator-checkbox-${userId}`).checked = !isChecked;
        });
    } else {
        document.getElementById(`moderator-checkbox-${userId}`).checked = !isChecked;
    }
}

function removeFollower(userId, communityId) {
    const confirmationMessage = 'Are you sure you want to remove this user as a follower from the community?';

    if (confirm(confirmationMessage)) {
        fetch(`/community/${communityId}/remove_follower/${userId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to remove follower.');
            }
            return response.json();
        })
        .then(data => {
            alert(data.message);

            const followerRow = document.getElementById(`follower-row-${userId}`);
            if (followerRow) {
                followerRow.remove();
            }
        })
        .catch(error => {
            alert(error.message);
        });
    }
}
</script>
