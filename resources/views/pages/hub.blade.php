@extends('layouts.app')

@section('content')

@php
$activeTab = request()->query('tab', 'news'); // Default to 'News'
@endphp

<div class="">
  <!-- Hub Header -->
  @if($community)
  <div class="border-b-2 border-black max-w-7xl mx-auto px-4 sm:px-6 py-6 lg:px-8">
    <div class="flex gap-6 justify-between">
      <img src="{{ asset( $community->image->path ?? 'images/groupdefault.jpg') }}"
        onerror="this.onerror=null;this.src='https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png';"
        alt="Community Image"
        class="rounded-full ring-2 ring-black h-36 w-36 sm:h-24 sm:w-24 md:h-32 md:w-32 lg:h-40 lg:w-40 object-cover">
      <div>
        <div class="flex items-center gap-2">
          <!-- Community Name -->
          <h1 class="tracking-tighter font-medium text-6xl">h/{{ $community->name }}</h1>

          <!-- Form for Privacy Toggle -->

          <form action="{{ route('communities.update.privacy', $community) }}" method="POST"
            class="inline-flex items-center gap-2">
            @csrf
            @method('POST')

            <!-- Dropdown for Privacy Selection -->
            @if (Auth::check())
            @if ($community->moderators->pluck('id')->contains(Auth::user()->id) || Auth::user()->is_admin)
            <div data-route="{{$community->id}}"
              class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-full {{ $community->privacy ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
              @if($community->privacy)
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
            </div>
            @else
            <span
              class="px-4 inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-full {{ $community->privacy ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
              @if($community->privacy)
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              </svg>
              private
              @else
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                <path d="M7 11h10"></path>
              </svg>
              public
              @endif
            </span>
            @endif
            @endif
          </form>
        </div>

        <p class="text-gray-600 mt-2 text-sm">{{ $community->description }}</p>


        <!-- Sort by and + post button -->
        <div class="flex items-center gap-4 mt-6">
          @auth
          @if($is_following)
          {{-- Estado: Seguindo --}}
          <form action="{{ route('communities.leave', $community->id) }}" method="POST" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit"
              class="inline-flex items-center justify-center px-6 py-2  font-medium hover:text-[#F4F2ED] rounded-lg border-2 border-black hover:border-pastelRed hover:bg-pastelRed transition-colors">
              unfollow -
            </button>
          </form>
          @elseif($community->privacy && $community->followRequests->where('authenticated_user_id',
          Auth::user()->id)->where('request_status', 'pending')->count() > 0)
          <button
            class="inline-flex items-center justify-center px-3.5 py-2.5 font-medium text-gray-600 border-2 border-black rounded-lg bg-[#F4F2ED] cursor-not-allowed"
            disabled>
            request Pending
          </button>
          @else
          <form id="followForm" action="{{ route('communities.join', $community->id) }}" method="POST" class="inline">
            @csrf
            <button type="submit"
              class="inline-flex items-center justify-center px-6 py-2 font-medium text-[#F4F2ED] rounded-lg bg-black hover:bg-pastelBlue transition-colors">
              follow +
            </button>
          </form>
          @endif
          @else
          <a href="{{ route('login') }}"
            class="inline-flex items-center justify-center px-6 py-2 font-medium text-[#F4F2ED] rounded-lg bg-black hover:bg-pastelBlue transition-colors">
            follow +
          </a>
          @endauth
          @auth
          @if($is_following)
          <a href="{{ route('post.create') }}"
            class="relative inline-flex items-center justify-center px-6 py-2 overflow-hidden font-medium text-white transition duration-300 ease-out border-2 border-black rounded-lg shadow-md group bg-black text-[#F4F2ED] hover:opacity-80">
            <span
              class="absolute inset-0 flex items-center justify-center w-full h-full text-white duration-300 -translate-x-full bg-pastelGreen group-hover:translate-x-0 ease">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3">
                </path>
              </svg>
            </span>
            <span
              class="absolute flex items-center text-base font-semibold justify-center w-full h-full text-white transition-all duration-300 transform group-hover:translate-x-full ease">
              post +
            </span>
            <span class="relative text-base font-semibold invisible">post +</span>
          </a>


          @endif
          @endauth
        </div>
      </div>
      <div class="grid grid-cols-2 mt-auto tracking-tight text-lg text-left items-center">
        <span class="text-2xl font-medium text-right mr-2">{{ number_format($followers_count ?? 0, 0) }}</span>
        <a href="{{ route('community.followers', $community->id) }}" class="underline-effect font-light ">followers</a>

        <span class="text-2xl text-right mr-2">{{ number_format($posts_count ?? 0, 0) }}</span>
        <span class="font-light ">posts</span>
      </div>
    </div>
  </div>

  {{-- @include('partials.news_topic_nav', ['url' => '/hub/' . $community->id]) --}}

  <nav class="border-b-2 border-black w-full font-light text-xl tracking-tighter px-6 flex flex-wrap gap-4 md:gap-8">
    <a href="{{ url('/hub/' . $community->id . '/?tab=news&sort=' . $sortOption) }}"
      class="py-4 relative group {{ $activeTab === 'news' ? 'text-gray-900 border-b-2 border-black' : 'text-gray-500 hover:text-gray-700' }}">
      news
    </a>
    <a href="{{ url('/hub/' . $community->id . '/?tab=topics&sort=' . $sortOption) }}"
      class="py-4 relative group {{ $activeTab === 'topics' ? 'text-gray-900 border-b-2 border-black' : 'text-gray-500 hover:text-gray-700' }}">
      topics
    </a>
    @if ($community->moderators->pluck('id')->contains(Auth::user()->id) || (Auth::user()->is_admin))
    <a href="{{ route('moderation.overview', $community->id) }}"
      class="py-4 relative group {{ $activeTab === 'moderation' ? 'text-gray-900 border-b-2 border-black' : 'text-gray-500 hover:text-gray-700' }}">
      moderation
    </a>
    @endif
    <form action="{{ url('/hub/' . $community->id) }}" method="GET" class="ml-auto flex items-center gap-2">
      <input type="hidden" name="tab" value="{{ $activeTab }}">
      <select name="sort" class="bg-transparent  focus:outline-none *:text-sm" onchange="this.form.submit()">
        <option value="newest" {{ $sortOption==='newest' ? 'selected' : '' }}>recent</option>
        <option value="top" {{ $sortOption==='top' ? 'selected' : '' }}>top</option>
        <option value="trending" {{ $sortOption==='trending' ? 'selected' : '' }}>trending</option>
      </select>
    </form>
  </nav>


  <!-- Posts Section -->
  @if($community->privacy && !($community->followers->pluck('id')->contains(Auth::user()->id)) &&
  !(Auth::user()->is_admin))
  <div class="text-center py-12 bg-white rounded-xl shadow-sm">
    <p class="text-gray-500">This hub is private.</p>
  </div>
  @else
  <div>
    <!-- Posts Grid -->
    <div class="divide-y-2 border-b-2 border-black divide-black">
      @if ($activeTab === 'news' && $newsPosts->count() > 0)
      @foreach ($newsPosts as $post)
      @include('partials.post', [
      'news' => 'true',
      'item' => $post,
      'post' => $post->news,
      ])
      @endforeach
      @elseif ($activeTab === 'topics' && $topicPosts->count() > 0)
      @foreach ($topicPosts as $post)
      @if ($community->moderators->pluck('id')->contains(Auth::user()->id) || Auth::user()->is_admin ||
      $post->topic->status->value === 'accepted')
      @include('partials.post', ['news' => false, 'post' => $post->topic, 'img' => false, 'item' => $post])
      @endif
      @endforeach
      @else
      <div class="text-center py-12 bg-white rounded-xl shadow-sm">
        <p class="text-gray-500">No posts available in this hub yet.</p>
        @auth
        @if($is_following)
        <a href="{{ route('post.create', ['community_id' => $community->id]) }}" class="mt-4 inline-block px-11 py-3 bg-pastelBlue text-white text-sm font-medium rounded-full 
                hover:bg-blue-600 transition-colors duration-200 border-2 border-black">
          Create the first post
        </a>
        @endif
        @endauth
      </div>
      @endif
    </div>


    <!-- Pagination -->
    @if(method_exists($community->posts, 'hasPages') && $community->posts->hasPages())
    <div class="py-6">
      {{ $community->posts->links() }}
    </div>
    @endif
  </div>
  @endif



  @else
  <div class="py-12 text-center">
    <p class="text-gray-500 text-xl">Hub not found</p>
    <a href="{{ route('home') }}"
      class="mt-4 inline-block px-6 py-2 bg-blue-500 text-white text-sm font-medium rounded-full hover:bg-blue-600 transition-colors duration-200">
      Return Home
    </a>
  </div>
  @endif
</div>

@endsection