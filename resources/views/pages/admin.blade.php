@extends('layouts.admin')

@section('content')

<head>
<script defer src="{{ asset('js/app.js') }}"></script>
</head>
{{-- <div class="flex-1 bg-pastelRed h-12 flex items-center pl-2 md:pl-4 relative">
  <svg class="w-5 h-5 text-[#F4F2ED]/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
  </svg>
  <input id="search-input" type="text" placeholder="search"
    class="w-full bg-transparent border-none text-[#F4F2ED] placeholder-[#F4F2ED] px-2 md:px-3 py-2 focus:outline-none ">
</div> --}}

<div class="p-4">
  <div class="w-[50%] mx-auto">
    <x-chartjs-component :chart="$chartHubs" />
  </div>
</div>

<div class="p-4 ">
  <div class="w-[50%] mx-auto">
    <x-chartjs-component :chart="$chartUsers" />
  </div>
</div>

<div class="p-4 ">
  <div class="w-[50%] mx-auto">
    <x-chartjs-component :chart="$postsPDay" />
  </div>
</div>
<div class="p-4 ">
  <div class="w-[50%] mx-auto">
    <x-chartjs-component :chart="$comboPosts" />
  </div>
</div>
<div class="p-4 ">
  <div class="w-[50%] mx-auto">
    <x-chartjs-component :chart="$chartReports" />
  </div>
</div>

<div class="p-4 ">
  <h1 class="text-xl font-bold mb-2">users</h1>
  <table
    class="min-w-[1000px] w-full bg-white border-2 border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30 p-6">
    <thead class="bg-gray-100">
      <tr>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Handle</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">admin</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Suspended</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delete User</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
    @foreach($users as $user)
      <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-4 py-4 whitespace-nowrap">{{$user->id}}</td>
        <td class="px-4 py-4">
          <a class="flex items-center" href="{{ route('user.profile', $user->id) }}">
            <img src="https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png"
              class="max-w-full rounded-3xl min-w-[32px] mr-3  w-[32px]">
            <span class="break-all">{{ '@' . $user->username }}</span>
          </a>
        </td>
        <td class="px-4 py-4 break-all">
        {{ $user->name }}
        </td>
        <td class="px-4 py-4">
          <input
              id="admin-checkbox-{{ $user->id }}"
              type="checkbox"
              class="w-4 h-4 accent-blue-500"
              @if($user->is_admin) checked @endif
              onclick="toggleAdmin({{ $user->id }}, this.checked)"
          >
      </td>
        <td class="px-4 py-4">
          <input
              id="suspend-checkbox-{{ $user->id }}"
              type="checkbox"
              class="w-4 h-4 accent-red-500"
              @if($user->is_suspended) checked @endif
              onclick="toggleSuspend({{ $user->id }}, this.checked)"
          >
      </td>
        <td class="px-4 py-4">
          <button name="delete-button"
            class="px-2 py-1 rounded-md bg-red-500/[.80] hover:bg-red-500 text-white font-bold">
            delete
          </button>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

</div>

<div class="p-4">
  <h1 class="text-xl font-bold mb-2">hubs</h1>

  <table
    class="w-full bg-white border-2 border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30 p-6">
    <thead class="bg-gray-100">
      <tr>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">privacy</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delete</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
    @foreach($hubs as $hub)
      <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-4 py-4 whitespace-nowrap">{{ $hub->id }}</td>
        <td class="px-4 py-4">
          <a class="flex items-center" href="{{ route('communities.show', $hub->id) }}">
            <img src="https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png"
              class="max-w-full rounded-3xl min-w-[32px] mr-3  w-[32px]">
            <span
              class="truncate max-w-32 hover:max-w-full transition-all">{{ $hub->name }}</span>
          </a>
        </td>
        <td class="px-4 py-4 break-all max-w-prose">
          {{ $hub->description }}
        </td>
        <td class="px-4 py-4">
          <span
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-full {{ $hub->is_private ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
            @if($hub->is_private)
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            Private
            @else
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              <path d="M7 11h10"></path>
            </svg>
            Public
            @endif
          </span>
        </td>

        <td class="px-4 py-4">
          <button name="delete-button"
            class="px-2 py-1 rounded-md bg-red-500/[.80] hover:bg-red-500 text-white font-bold">
            delete
          </button>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>

<div class="p-4">
  <h1 class="text-xl font-bold mb-2">news</h1>

  <table
    class="w-full bg-white border-2 border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30 p-6">
    <thead class="bg-gray-100">
      <tr>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">News URL</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Content</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">upvotes/downvotes
        </th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">threads</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">delete</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
    @foreach($news as $item)
      <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-4 py-4 whitespace-nowrap">{{ $item->post_id }}</td>
        <td class="px-4 py-4">
          <a class="prose"
            href="{{ $item->url }}">{{ $item->news_url }}
          </a>
        </td>
        <td
          class="px-4 py-4 break-all max-w-[16rem] overflow-hidden whitespace-nowrap text-ellipsis hover:overflow-auto hover:whitespace-normal hover:text-wrap hover:max-w-prose transition-all">
          <a class="flex items-center" href="{{ route('news.show',['post_id' => $item->post->id]) }}">
          {{ $item->post->title }}
        </td>
        <td
          class="px-4 py-4 break-all max-w-[24rem] overflow-hidden whitespace-nowrap text-ellipsis hover:overflow-auto hover:whitespace-normal hover:text-wrap hover:max-w-prose transition-all">
          {{ $item->post->content }}
        </td>
        <td class="px-4 py-4">
          <strong class="text-pastelBlue">{{ $item->post->upvote_count }}</strong> <strong>/</strong> <strong class="text-pastelRed">{{ $item->post->downvote_count }}</strong>
        </td>
        <td class="px-4 py-4">
        {{ $item->post->comments->count() }}
        </td>
        <td class="px-4 py-4">
          <button name="delete-button"
            class="px-2 py-1 rounded-md bg-red-500/[.80] hover:bg-red-500 text-white font-bold">
            Delete
          </button>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>

<div class="p-4">
  <h1 class="text-xl font-bold mb-2">topics</h1>
  <table
    class="w-full bg-white border-2 border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30 p-6">
    <thead class="bg-gray-100">
      <tr>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Content</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">upvotes/downvotes
        </th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">threads</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">status</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">delete</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
    @foreach($topics as $topic)
      <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-4 py-4 whitespace-nowrap">{{ $topic->post_id }}</td>

        <td
          class="px-4 py-4 break-all max-w-[16rem] overflow-hidden whitespace-nowrap text-ellipsis hover:overflow-auto hover:whitespace-normal hover:text-wrap hover:max-w-prose transition-all">
          <a class="flex items-center" href="{{ route('topic.show',['post_id' => $topic->post->id]) }}">
          {{ $topic->post->title }}
        </td>
        <td
          class="px-4 py-4 break-all max-w-[24rem] overflow-hidden whitespace-nowrap text-ellipsis hover:overflow-auto hover:whitespace-normal hover:text-wrap hover:max-w-prose transition-all">
          {{ $topic->post->content }}
        </td>
        <td class="px-4 py-4">
          <strong class="text-pastelBlue">{{ $topic->post->upvote_count }}</strong> <strong>/</strong> <strong class="text-pastelRed">{{ $topic->post->downvote_count }}</strong>
        </td>
        <td class="px-4 py-4">
        {{ $topic->post->comments->count() }}
        </td>
        <td class="px-4 py-4">
          {{-- <span
            class="{{ $topic->status ? 'text-green-600 bg-green-100' : 'text-red-600 bg-red-100' }} text-sm border rounded-full px-3 py-1 font-bold">
            {{ $topic->status ? 'Approved' : 'Rejected' }}
          </span> --}}
          <span
            class=" bg-orange-200 text-orange-500 text-sm border rounded-full px-3 py-1 font-bold whitespace-nowrap">Waiting
            Approval</span>
        </td>
        <td class="px-4 py-4">
          <button name="delete-button"
            class="px-2 py-1 rounded-md bg-red-500/[.80] hover:bg-red-500 text-white font-bold">
            Delete
          </button>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>

@endsection

<script>
    function toggleSuspend(userId, isChecked) {
        const action = isChecked ? 'suspend' : 'unsuspend';
        const confirmationMessage = isChecked
            ? 'Are you sure you want to suspend this user?'
            : 'Are you sure you want to unsuspend this user?';

        if (confirm(confirmationMessage)) {
            fetch(`/users/${userId}/${action}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({})
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to update user status.');
                }
                return response.json();
            })
            .then(data => {
                alert(data.message);
            })
            .catch(error => {
                alert(error.message);
                // Revert checkbox state if the request fails
                document.getElementById(`suspend-checkbox-${userId}`).checked = !isChecked;
            });
        } else {
            // Revert checkbox state if the user cancels the action
            document.getElementById(`suspend-checkbox-${userId}`).checked = !isChecked;
        }
    }

    function toggleAdmin(userId, isChecked) {
        const action = isChecked ? 'make_admin' : 'remove_admin';
        const confirmationMessage = isChecked
            ? 'Are you sure you want to grant this user admin privileges?'
            : 'Are you sure you want to revoke this user\'s admin privileges?';

        if (confirm(confirmationMessage)) {
            fetch(`/users/${userId}/${action}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({})
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to update admin status.');
                }
                return response.json();
            })
            .then(data => {
                alert(data.message);
            })
            .catch(error => {
                alert(error.message);
                // Revert checkbox state if the request fails
                document.getElementById(`admin-checkbox-${userId}`).checked = !isChecked;
            });
        } else {
            // Revert checkbox state if the user cancels the action
            document.getElementById(`admin-checkbox-${userId}`).checked = !isChecked;
        }
    }
</script>
