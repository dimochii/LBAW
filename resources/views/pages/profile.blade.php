@extends('layouts.app')

@section('content')
<div class="min-h-screen">
  <div class="flex flex-row gap-8 p-8 border-b-2 border-black items-center relative min-w-32">
  <img src="{{ asset($user->image->path ?? '/images/default.jpg') }}" alt="Profile Image" class="rounded-full ring-2 ring-black h-32 w-32">
    <div class="h-full flex flex-col gap-4 flex-grow">
      <div class="flex-col flex">
        <div class="tracking-tighter font-medium text-6xl">{{ $user->name }}</div>
        <div>{{ '@' . $user->username }}</div>
        <div class="flex items-center mt-2">
        <div class="flex items-center mt-2">
    <span class="font-bold text-lg">Reputation:</span>
    <div class="ml-4 flex items-center">
        {{-- Reputation Value with Dynamic Badge --}}
        <span class="text-xl font-semibold px-4 py-2 rounded-full shadow-lg 
            {{ $reputation >= 1000 ? 'bg-pastelYellow text-black' : 
               ($reputation >= 500 ? 'bg-pastelBlue text-black' : 
               ($reputation >= 0 ? 'bg-gray-200 text-black' : 
               'bg-pastelRed text-white')) }}" 
               id="reputation-badge">
            {{ $reputation }}
        </span>
        
        {{-- Reputation Title with Icons --}}
        <div class="ml-3 flex items-center">
            <span class="text-sm font-bold italic text-gray-600">
                {{ $reputation >= 1000 ? 'Legend' : 
                   ($reputation >= 500 ? 'Influencer' : 
                   ($reputation >= 100 ? 'Contributor' : 
                   ($reputation >= 0 ? 'Lurker' : 
                   'Outcast')))}}
            </span>
            
            {{-- Icons Based on Reputation --}}
            <span class="ml-2 text-lg" id="reputation-icon">
                @if ($reputation >= 1000)
                    <i class="fas fa-star"></i> {{-- Star Icon for Legend --}}
                @elseif ($reputation >= 500)
                    <i class="fas fa-trophy"></i> {{-- Trophy Icon for Influencer --}}
                @elseif ($reputation >= 100)
                    <i class="fas fa-thumbs-up"></i> {{-- Thumbs-up for Contributor --}}
                @elseif ($reputation >= 0)
                    <i class="fas fa-eye-slash"></i> {{-- Eye-slash for Lurker --}}
                @else
                    <i class="fas fa-skull-crossbones"></i> {{-- Skull Icon for Outcast --}}
                @endif
            </span>
        </div>
    </div>
</div>
</div>
    
      </div>
      <p class="font-light">
        {{ $user->description }}
      </p>
    </div>
    <div class="flex flex-col items-end space-y-4">
    <button onclick=reportProfile()>
    Report 
    </button>
    @include('partials.report_box',['reported_id' =>$user->id] )


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
          @include('partials.post', [
            'news' => false,
            'item' =>$item, 
            'post' => $item->topic,
          ])
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
        <ul class="divide-y divide-gray-300">
            @foreach ($user->communities as $community)
                <li class="py-4 flex items-center justify-between">
                    <a href="{{ route('communities.show', $community->id) }}" 
                       class="text-lg font-medium text-blue-600 hover:text-blue-800 transition-colors duration-300">
                        {{ $community->name }}
                    </a>
                    @if ($community->moderators->pluck('id')->contains($user->id))
                        <span class="px-3 py-1 text-sm font-semibold text-white bg-pastelGreen rounded-full">
                            Moderator
                        </span>
                    @endif
                </li>
            @endforeach
        </ul>

    @endif
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


