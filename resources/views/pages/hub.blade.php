@extends('layouts.app')

@section('content')
<div class="">
  <!-- Hub Header -->
  @if($community)
  <div class="border-b-2 border-black max-w-7xl mx-auto px-4 sm:px-6 py-6 lg:px-8">
    <div class="flex items-start gap-6">
      <img src="{{ asset( $community->image->path ?? 'images/groupdefault.jpg') }}"
        onerror="this.onerror=null;this.src='https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png';"
        alt="Community Image" class="rounded-full ring-2 ring-black h-36 w-36 sm:h-24 sm:w-24 md:h-32 md:w-32 lg:h-40 lg:w-40 object-cover">
      <div>
        <div class="flex items-center gap-2">
          <!-- Community Name -->
          <h1 class="tracking-tighter font-medium text-6xl">h/{{ $community->name }}</h1>

          <!-- Form for Privacy Toggle -->

          <form action="{{ route('communities.update.privacy', $community) }}" method="POST" class="inline-flex items-center gap-2">
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
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-full {{ $community->privacy ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                    @if($community->privacy)
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        private
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
        <div class="flex items-center gap-4 mt-3 text-sm text-gray-500">
          <a href="{{ route('community.followers', $community->id) }}" class="flex items-center gap-2">
            <div class="flex items-center gap-2">
              <span>{{ number_format($followers_count ?? 0, 0) }}</span>
              <span>Followers</span>
            </div </a>
            <div class="flex items-center gap-2">
              <span>{{ number_format($posts_count ?? 0, 0) }}</span>
              <span>Posts</span>
            </div>
        </div>

        <!-- Sort by and + post button -->
        <div class="flex items-center gap-4 mt-6">
          <div class="flex items-center gap-2">
            <span class="text-sm text-gray-600">sort by</span>
            <select name="sort" class="bg-transparent text-sm text-gray-900 font-medium focus:outline-none">
              <option value="newest">Newest</option>
              <option value="top">Top</option>
              <option value="trending">Trending</option>
            </select>
          </div>
          @auth
          @if($is_following)
          <a href="{{ route('post.create') }}" class="px-4 text-gray-600 text-sm font-medium underline-effect">
            + post
          </a>
          @endif
          @endauth
        </div>
      </div>
    </div>
  </div>

  @php
  $activeTab = request()->query('tab', 'News'); // Default to 'News'
  @endphp
  {{-- @include('partials.news_topic_nav', ['url' => '/hub/' . $community->id]) --}}

  <nav class="border-b-2 border-black w-full font-light text-xl tracking-tighter px-6 flex flex-wrap gap-4 md:gap-8">
    <a href="{{ url('/hub/' . $community->id . '/?tab=news') }}"
      class="py-4 relative group {{ $activeTab === 'news' ? 'text-gray-900 border-b-2 border-black' : 'text-gray-500 hover:text-gray-700' }}">
      news
    </a>
    <a href="{{ url('/hub/' . $community->id . '/?tab=topics') }}"
      class="py-4 relative group {{ $activeTab === 'topics' ? 'text-gray-900 border-b-2 border-black' : 'text-gray-500 hover:text-gray-700' }}">
      topics
    </a>

    <a href="{{ route('moderation.overview', $community->id)  }}"
      class="py-4 relative group {{ $activeTab === 'moderation' ? 'text-gray-900 border-b-2 border-black' : 'text-gray-500 hover:text-gray-700' }}">
      moderation
    </a>
  </nav>

  <!-- Posts Section -->
  <div>
    <!-- Posts Grid -->
    <div class="divide-y-2 border-b-2 border-black divide-black">
      @if ($activeTab === 'news')
      @if ($newsPosts->count() > 0)
      @foreach ($newsPosts as $post)
      @include('partials.post', [
      'news' => 'true',
      'item' => $post,
      'post' => $post->news,
      ])
      @endforeach
      @endif


      @elseif ($activeTab === 'topics')
      @if ($topicPosts->count() > 0)
      @foreach ($topicPosts as $post)
      @include('partials.post', ['news' => false, 'post' => $post->topic, 'img' => false, 'item' => $post])
      @endforeach
      @endif      

      @else
      <div class="text-center py-12 bg-white rounded-xl shadow-sm">
        <p class="text-gray-500">No posts available in this hub yet.</p>
        @auth
        @if($is_following)
        <a href="{{ route('post.create', ['community_id' => $community->id]) }}"
          class="mt-4 inline-block px-11 py-3 bg-pastelBlue text-white text-sm font-medium rounded-full 
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
<script>

document.addEventListener('DOMContentLoaded', function () {
    const privacies = document.querySelectorAll('[data-route]'); 

    function updatePrivacy(element) {
        const routeID = element.getAttribute('data-route');

        fetch(`/hub/${routeID}/privacy`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error. Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                if (data.privacy === 'Private') {
                    element.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    Private`;
                    element.classList = 'cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-full bg-red-100 text-red-700';
                } else if (data.privacy === 'Public') {
                    element.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    <path d="M7 11h10"></path>
                    </svg>
                    Public`;
                    element.classList = 'cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-full bg-green-100 text-green-700';
                }
            }
        })
        .catch(error => {
            console.error('Error updating privacy', error);
        });
    }

    privacies.forEach(function (element) {
        element.addEventListener('click', function () {
            updatePrivacy(element); 
        });
    });
});

    
</script>
@endsection
