@extends('layouts.app')

@section('content')
<div class="min-h-screen">
  <div class="flex flex-row gap-8 p-8 border-b-2 border-black items-center relative min-w-32">
  <img src="{{ asset('images/user' . $user->image_id . '.jpg') }}" alt="Profile Image" class="rounded-full ring-2 ring-black h-32 w-32">
    <div class="h-full flex flex-col gap-4 flex-grow">
      <div class="flex-col flex">
        <div class="tracking-tighter font-medium text-6xl">{{ $user->name }}</div>
        <div>{{ '@' . $user->username }}</div>
      </div>
      <p class="font-light">
        {{ $user->description }}
      </p>
    </div>
    <div class="flex flex-col items-end space-y-4">
      {{-- Followers and Following on the Same Line --}}
      <div class="flex space-x-4 text-sm">
        <a href="{{ route('user.followers', $user->id) }}" 
           class="text-gray-700 hover:text-blue-600 transition-colors duration-300 text-xl underline-effect">
          <span class="font-semibold ">{{ $followers->count() ?? 0 }}</span> Followers
        </a>
        <a href="{{ route('user.following', $user->id) }}" 
           class="text-gray-700 hover:text-blue-600 transition-colors duration-300 text-xl underline-effect">
          <span class="font-semibold">{{ $following->count() ?? 0 }}</span> Following
        </a>
      </div>

      @if (Auth::check() && Auth::user()->id !== $user->id)
          @if ($isFollowing)
              <form action="{{ route('user.follow', $user->id) }}" method="POST" class="w-full">
                  @csrf
                  <button type="submit" class="w-full px-6 py-2 bg-gray-500 text-white rounded-full 
                  hover:bg-gray-600 transition-all duration-300 font-semibold">
                      Following
                  </button>
              </form>
          @else
              <form action="{{ route('user.follow', $user->id) }}" method="POST" class="w-full">
                  @csrf
                  <button type="submit" class="w-full px-6 py-2 bg-pastelBlue text-white rounded-full 
                  hover:bg-green-700 transition-all duration-300 font-semibold">
                      Follow
                  </button>
              </form>
          @endif
      @endif

      {{-- Edit Profile Button --}}
      @if (Auth::check() && Auth::user()->can('editProfile', $user))
      <a href="{{ route('user.edit', $user->id) }}" class="text-gray-600 hover:text-blue-600 transition-colors duration-300 text-sm underline">
        Edit Profile
      </a>
      @endif
    </div>
  </div>

  {{-- Navigation Tabs with Black Border --}}
  <div class="border-b-2 border-black w-full font-light text-xl tracking-tighter">
    <div class="w-full">
      @php
        $activeTab = request()->query('tab', 'articles'); // Default to 'articles'
      @endphp
      <nav class="max-w-7xl mx-auto px-6 flex flex-wrap gap-4 md:gap-8">
        <a href="{{ url('/users/' . $user->id . '/profile?tab=articles') }}"
           class="py-4 relative group {{ $activeTab === 'articles' ? 'text-gray-900 border-b-2 border-black' : 'text-gray-500 hover:text-gray-700' }}">
          articles
        </a>
        <a href="{{ url('/users/' . $user->id . '/profile?tab=discussions') }}"
           class="py-4 relative group {{ $activeTab === 'discussions' ? 'text-gray-900 border-b-2 border-black' : 'text-gray-500 hover:text-gray-700' }}">
          discussions
        </a>
        <a href="{{ url('/users/' . $user->id . '/profile?tab=upvoted') }}"
           class="py-4 relative group {{ $activeTab === 'upvoted' ? 'text-gray-900 border-b-2 border-black' : 'text-gray-500 hover:text-gray-700' }}">
          upvoted
        </a>
        <!-- Add Favorites Tab -->
        <a href="{{ url('/users/' . $user->id . '/profile?tab=favorites') }}"
           class="py-4 relative group {{ $activeTab === 'favorites' ? 'text-gray-900 border-b-2 border-black' : 'text-gray-500 hover:text-gray-700' }}">
          favorites
        </a>
      </nav>
    </div>
  </div>

  <div class="divide-y-2 divide-black border-b-2 border-black">
    @if ($activeTab === 'articles')
      @if ($authored_news->count() > 0)
        @foreach ($authored_news as $item)
          @include('partials.post', [
            'news' => true,
            'post' => $item->news,
          ])
        @endforeach
      @else
        <p class="text-gray-500">This user has not authored any posts yet.</p>
      @endif
    @elseif ($activeTab === 'discussions')
      @if ($authored_topics->count() > 0)
        @foreach ($authored_topics as $item)
          @include('partials.post', [
            'news' => false,
            'post' => $item->topic,
          ])
        @endforeach
      @else
        <p class="text-gray-500">This user has not participated in any discussions yet.</p>
      @endif
    @elseif ($activeTab === 'upvoted')
      @if ($voted_news->count() > 0)
        @foreach ($voted_news as $item)
          @include('partials.post', [
            'news' => true,
            'post' => $item->news,
          ])
        @endforeach
      @elseif ($voted_topics->count() > 0)
        @foreach ($voted_topics as $item)
          @include('partials.post', [
            'news' => false,
            'post' => $item->topic,
          ])
        @endforeach
      @else
        <p class="text-gray-500">This user has not upvoted any posts yet.</p>
      @endif
      @elseif ($activeTab === 'favorites')
        @if ($favourite_news->count() > 0)
        @foreach ($favourite_news as $item)
          @include('partials.post', [
            'news' => true,
            'post' => $item->news,
          ])
        @endforeach
        @elseif ($favourite_topics->count() > 0)   
        @foreach ($favourite_topics as $item)    
          @include('partials.post', [
            'news' => false,
            'post' => $item->topic,
          ])
        @endforeach
    @else
        <p class="text-gray-500">This user has no favorite posts yet.</p>
    @endif
@endif

  </div>


  <form method="POST" action="{{ url('/deletemyaccount') }}">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger">Delete My Account</button>
  </form>

  @endsection
</div>


