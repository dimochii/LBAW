@extends('layouts.admin')

@section('content')

<div class="p-4">
  <h1 class="text-xl font-bold mb-2">Users</h1>
  <table class="w-full bg-white border-2 border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30 p-6">
    <thead class="bg-gray-100">
      <tr>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Handle</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admin</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Suspended</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delete User</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
      @foreach($users as $user)
      <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-4 py-4 whitespace-nowrap">{{ $user->id }}</td>
        <td class="px-4 py-4">
          <a class="flex items-center" href="#">
            <img src="{{ $user->avatar ?? 'https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png' }}" 
                 class="max-w-full rounded-3xl min-w-[32px] mr-3 w-[32px]">
            <span class="break-all">{{ '@' . $user->username }}</span>
          </a>
        </td>
        <td class="px-4 py-4 break-all">{{ $user->name }}</td>
        <td class="px-4 py-4">
          <input type="checkbox" {{ $user->is_admin ? 'checked' : '' }} class="w-4 h-4 accent-blue-500" disabled>
        </td>
        <td class="px-4 py-4">
          <input type="checkbox" {{ $user->is_suspended ? 'checked' : '' }} class="w-4 h-4 accent-red-500" disabled>
        </td>
        <td class="px-4 py-4">
          <form method="POST" action="{{ route('user.destroy', $user->id) }}">
            @csrf
            @method('DELETE')
            <button name="delete-button" class="px-2 py-1 rounded-md bg-red-500/[.80] hover:bg-red-500 text-white font-bold">
              Delete
            </button>
          </form>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>

<div class="p-4">
  <h1 class="text-xl font-bold mb-2">Hubs</h1>
  <table class="w-full bg-white border-2 border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30 p-6">
    <thead class="bg-gray-100">
      <tr>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Privacy</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delete</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
      @foreach($hubs as $hub)
      <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-4 py-4 whitespace-nowrap">{{ $hub->id }}</td>
        <td class="px-4 py-4">{{ $hub->name }}</td>
        <td class="px-4 py-4">{{ $hub->description }}</td>
        <td class="px-4 py-4">
          <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-full 
                        {{ $hub->is_private ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
            {{ $hub->is_private ? 'Private' : 'Public' }}
          </span>
        </td>
        <td class="px-4 py-4">
          <form method="POST" action="{{ route('communities.destroy', $hub->id) }}">
            @csrf
            @method('DELETE')
            <button name="delete-button" class="px-2 py-1 rounded-md bg-red-500/[.80] hover:bg-red-500 text-white font-bold">
              Delete
            </button>
          </form>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>

<div class="p-4">
  <h1 class="text-xl font-bold mb-2">News</h1>
  <table class="w-full bg-white border-2 border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30 p-6">
    <thead class="bg-gray-100">
      <tr>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">News URL</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Content</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Upvotes/Downvotes</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Threads</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delete</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
      @foreach($news as $item)
      <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-4 py-4">{{ $item->post_id }}</td>
        <td class="px-4 py-4"><a href="{{ $item->url }}">{{ $item->news_url }}</a></td>
        <td class="px-4 py-4">{{ $item->post->title }}</td>
        <td class="px-4 py-4">{{ $item->post->content }}</td>
        <td class="px-4 py-4">{{ $item->post->upvote_count }} / {{ $item->post->downvote_count }}</td>
        <td class="px-4 py-4">{{ $item->post->comments->count() }}</td>
        <td class="px-4 py-4">
          <form method="POST" action="{{ route('news.show', ['post_id' => $item->post_id]) }}">
            @csrf
            @method('DELETE')
            <button name="delete-button" class="px-2 py-1 rounded-md bg-red-500/[.80] hover:bg-red-500 text-white font-bold">
              Delete
            </button>
          </form>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>

<div class="p-4">
  <h1 class="text-xl font-bold mb-2">Topics</h1>
  <table class="w-full bg-white border-2 border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30 p-6">
    <thead class="bg-gray-100">
      <tr>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Content</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Upvotes/Downvotes</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Threads</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delete</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
      @foreach($topics as $topic)
      <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-4 py-4">{{ $topic->post_id }}</td>
        <td class="px-4 py-4">{{ $topic->post->title }}</td>
        <td class="px-4 py-4">{{ $topic->post->content }}</td>
        <td class="px-4 py-4">{{ $topic->post->upvote_count }} / {{ $topic->post->downvote_count }}</td>
        <td class="px-4 py-4">{{ $topic->post->comments->count() }}</td>
        <td class="px-4 py-4">
          <form method="POST" action="{{ route('topic.show', ['post_id' => $item->post_id]) }}">
            @csrf
            @method('DELETE')
            <button name="delete-button" class="px-2 py-1 rounded-md bg-red-500/[.80] hover:bg-red-500 text-white font-bold">
              Delete
            </button>
          </form>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>

@endsection
