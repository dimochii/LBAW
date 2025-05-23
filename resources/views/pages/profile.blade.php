@extends('layouts.app')

@section('content')
<div class="min-h-screen">
  <div class="flex flex-row p-8 border-b-2 border-black  relative min-w-32">
    <img src="{{ asset(isset($user->image->path) ? $user->image->path : 'images/default.jpg') }}" alt="Profile Image"
      class="mr-8 rounded-full ring-2 ring-black h-36 w-36 sm:h-24 sm:w-24 md:h-32 md:w-32 lg:h-40 lg:w-40 object-cover">
    <div class="h-full flex flex-col gap-2 grow">
      <div class="flex-col flex">
        <div class="tracking-tighter font-medium text-6xl mb-1">{{ $user->name }}</div>
        <div>{{ '@' . $user->username }}</div>
      </div>
      <p class="font-light">
        {{ $user->description }}
        Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptatibus, fugiat ad aliquid dolorem quas reiciendis molestias accusantium quam neque architecto sed nesciunt vel nemo maxime corrupti, maiores fugit laudantium soluta!
      </p>
      <div class="mt-4">
        @if (Auth::check() && Auth::user()->id !== $user->id && $user->id !== 1)
        @if ($isFollowing)
        <form action="{{ route('user.follow', $user->id) }}" method="POST" class="w-full">
          @csrf
          <button type="submit" class="inline-flex items-center justify-center px-6 py-2  font-medium hover:text-[#F4F2ED] rounded-lg border-2 border-black hover:border-pastelRed hover:bg-pastelRed transition-colors">
            unfollow -
          </button>
        </form>
        @else
        <form action="{{ route('user.follow', $user->id) }}" method="POST" class="w-full">
          @csrf
          <button type="submit" class="inline-flex items-center justify-center px-6 py-2 font-medium text-[#F4F2ED] rounded-lg bg-black hover:bg-pastelBlue transition-colors">
            follow +
          </button>
        </form>
        @endif
        @endif
      </div>
      

    </div>

    @if ($user->id != 1)
    <div class="flex flex-col w-full items-end">
      @if (Auth::check() && Auth::user()->can('editProfile', $user) && $user->id !== 1)
      <a href="{{ route('user.edit', $user->id) }}" class="font-light underline-effect text-lg">
        edit profile
      </a>
      @endif

      @if (!Auth::user()->can('editProfile', $user) )
      <button onclick=reportProfile()>
        <svg class="ml-auto h-4 w-4 fill-[#3C3D37] group-hover/wrapper:fill-[#F4F2ED] z-0"
          xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
          <path class="cls-1"
            d="M8,6.5A1.5,1.5,0,1,1,6.5,8,1.5,1.5,0,0,1,8,6.5ZM.5,8A1.5,1.5,0,1,0,2,6.5,1.5,1.5,0,0,0,.5,8Zm12,0A1.5,1.5,0,1,0,14,6.5,1.5,1.5,0,0,0,12.5,8Z" />
        </svg>
      </button>
      @endif
      @include('partials.report_box',['reported_id' =>$user->id] )
      @endif

      {{-- Followers and Following on the Same Line --}}
      <div class="grid grid-cols-2 mt-auto tracking-tight text-lg text-left items-center">
        <span class="text-2xl font-medium text-right mr-2">{{ $reputation ?? 0 }}</span>
        <span class="font-light ">reputation</span>

        <span class="text-2xl font-medium text-right mr-2">{{ $followers->count() ?? 0 }}</span>
        <a href="{{ route('user.followers', $user->id) }}" class="underline-effect font-light ">followers</a>

        <span class="text-2xl text-right mr-2">{{ $following->count() ?? 0 }}</span>
        <a href="{{ route('user.following', $user->id) }}" class="underline-effect font-light ">following</a>
      </div>

      

      {{-- Edit Profile Button --}}

    </div>
  </div>

  @if ($user->id !== 1)
  {{-- Navigation Tabs with Black Border --}}
  <div class="border-b-2 border-black w-full font-light text-xl tracking-tighter">
    <div class="w-full">
      @php
      $activeTab = request()->query('tab', 'news'); // Default to 'news'
      @endphp
      <nav class="max-w-7xl mx-auto px-6 flex flex-wrap gap-4 md:gap-8">
        <a href="{{ url('/users/' . $user->id . '/profile?tab=news') }}"
          class="py-4 relative group {{ $activeTab === 'news' ? 'text-gray-900 border-b-2 border-black' : 'text-gray-500 hover:text-gray-700' }}">
          news
        </a>
        <a href="{{ url('/users/' . $user->id . '/profile?tab=topics') }}"
          class="py-4 relative group {{ $activeTab === 'topics' ? 'text-gray-900 border-b-2 border-black' : 'text-gray-500 hover:text-gray-700' }}">
          topics
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
        <a href="{{ url('/users/' . $user->id . '/profile?tab=hubs') }}"
          class="py-4 relative group {{ $activeTab === 'hubs' ? 'text-gray-900 border-b-2 border-black' : 'text-gray-500 hover:text-gray-700' }}">
          hubs
        </a>
        <a href="{{ url('/users/' . $user->id . '/profile?tab=pending') }}"
          class="py-4 relative group {{ $activeTab === 'pending' ? 'text-gray-900 border-b-2 border-black' : 'text-gray-500 hover:text-gray-700' }}">
          pending
        </a>
      </nav>
    </div>
  </div>

  <div class="divide-y-2 divide-black border-b-2 border-black">
    @if ($activeTab === 'news')
    @if ($authored_news->count() > 0)
    @foreach ($authored_news as $item)
    @include('partials.post', [
    'news' => true,
    'item' =>$item,
    'post' => $item->news,
    ])
    @endforeach
    @else
    <p class="text-gray-500">This user has not authored any posts yet.</p>
    @endif
    @elseif ($activeTab === 'topics')
    @if ($authored_topics->count() > 0)
    @foreach ($authored_topics as $item)
    @if ($item->topic->status->value === 'accepted')
    @include('partials.post', [
    'news' => false,
    'item' =>$item,
    'post' => $item->topic,
    ])
    @endif
    @endforeach
    @else
    <p class="text-gray-500">This user has not participated in any topics yet.</p>
    @endif
    @elseif ($activeTab === 'pending')
    @if ($authored_topics->count() > 0)
    @foreach ($authored_topics as $item)
    @if ($item->topic->status->value === 'pending')
    @include('partials.post', [
    'news' => false,
    'item' =>$item,
    'post' => $item->topic,
    ])
    @endif
    @endforeach
    @else
    <p class="text-gray-500">This user has not participated in any topics yet.</p>
    @endif
    @elseif ($activeTab === 'upvoted')
    @if ($voted_news->count() > 0)
    @foreach ($voted_news as $item)
    @include('partials.post', [
    'news' => true,
    'item' =>$item,
    'post' => $item->news,
    ])
    @endforeach
    @elseif ($voted_topics->count() > 0)
    @foreach ($voted_topics as $item)
    @include('partials.post', [
    'news' => false,
    'item' =>$item,
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
    'item' =>$item,
    'post' => $item->news
    ])
    @endforeach
    @elseif ($favourite_topics->count() > 0)
    @foreach ($favourite_topics as $item)
    @include('partials.post', [
    'news' => false,
    'item' =>$item,
    'post' => $item->topic,
    ])
    @endforeach
    @else
    <p class="text-gray-500">This user has no favorite posts yet.</p>
    @endif
    @elseif ($activeTab === 'hubs')
    @if ($user->communities->count() > 0)
    <ul class="divide-y divide-black divide-4">
      @foreach ($user->communities as $community)
      <li
        class="py-4 flex items-center justify-between hover:bg-[#3C3D37] hover:text-[#F4F2ED] transition ease-out group/wrapper">
        <a href="{{ route('communities.show', $community->id) }}"
          class="text-lg font-medium  transition-colors duration-300">
          <div class="px-4 flex items-center space-x-4">
            <img src="{{ asset($community->image->path) }}"
              onerror="this.onerror=null;this.src='https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png';"
              alt="{{ $community->name }}"
              class="rounded-full object-cover size-20 grayscale hover:grayscale-0 transition-all duration-300 ease-in-out">

            <div class="flex-1 break-words">
              <h2 class="font-medium break-all">h/{{ $community->name }}</h2>
              <p class="text-sm  whitespace-normal">
                {{ $community->description }}
              </p>
            </div>
          </div>
        </a>
        @if ($community->moderators->pluck('id')->contains($user->id))
        <span class="px-3 py-1 text-sm font-semibold text-white bg-pastelGreen object-cover rounded-full">
          Moderator
        </span>
        @endif
      </li>
      @endforeach
    </ul>

    @endif
    @endif

  </div>
  @endif


</div>
<script>
  function reportProfile() {
    document.getElementById('reportForm').action = '{{ route('report') }}';
      document.getElementById('report_type').value = 'user_report';
      document.getElementById('reported_id').value = '{{ $user->id }}';
      document.getElementById('reportTitle').textContent = 'Report {{ $user->username }}\'s profile  ';
      document.getElementById('reportModal').classList.remove('hidden');
  }
</script>
@endsection