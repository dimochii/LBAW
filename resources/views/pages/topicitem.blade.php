@extends('layouts.app')

@section('content')
<span class="hidden" id="postId">{{$topicItem->post->id}}</span>
<div class="font-grotesk divide-y-2 divide-[black]">
  @if($topicItem->post->community->privacy &&
  !($topicItem->post->community->followers->pluck('id')->contains(Auth::user()->id)) &&
  !(Auth::user()->is_admin))
  <div class="text-center py-12 bg-white rounded-xl shadow-sm">
    <a class="flex items-center"
      href="{{ route('communities.show', ['id' => $topicItem->post->community->id ?? 'unknown']) }}">

      <img src="{{ asset($topicItem->post->community->image->path ?? 'images/groupdefault.jpg') }}"
        class="size-8 rounded-full object-cover ring-2  ring-white">
      <span class="text-2xl font-light underline-effect px-2">h/{{ $topicItem->post->community->name ?? 'Unknown
        Community' }}</span>
    </a>
    <p class="text-gray-500">This post belongs to a private hub.</p>
    <p>{{Auth::user()->is_admin}} olaaaa</p>
  </div>
  @else
  {{-- news post --}}
  <div class=" flex flex-row" id="post-header">
    <div class="px-8 py-4 w-1/2 flex flex-col grow">
      <div class="flex items-center h-8 relative">
        <a class="flex items-center"
          href="{{ route('communities.show', ['id' => $topicItem->post->community->id ?? 'unknown']) }}">
          <img src="{{ asset($topicItem->post->community->image->path ?? 'images/groupdefault.jpg') }}"
            class="size-8 rounded-full object-cover ring-2  ring-white">
          <span class="text-2xl font-light underline-effect px-2">h/{{ $topicItem->post->community->name ?? 'Unknown
            Community' }}</span>
        </a>

        {{--
        <!-- Edit Button (only if the current authenticated user is an author) -->
        @auth
        <!-- Check if the authenticated user is one of the authors -->
        @can('isAuthor', $topicItem->post)
        <a href="{{ route('news.edit', ['post_id' => $topicItem->post->id]) }}" class="btn btn-warning mt-3">Edit
          Post</a>
        @endif
        @endauth --}}


        <div class="ml-auto">
          <input type="checkbox" class="peer hidden" id="{{$topicItem->post_id}}-options">
          <label for="{{$topicItem->post_id}}-options">
            <svg class="ml-auto h-4 w-4 fill-[#3C3D37] group-hover/wrapper:fill-[#F4F2ED] z-0"
              xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
              <path class="cls-1"
                d="M8,6.5A1.5,1.5,0,1,1,6.5,8,1.5,1.5,0,0,1,8,6.5ZM.5,8A1.5,1.5,0,1,0,2,6.5,1.5,1.5,0,0,0,.5,8Zm12,0A1.5,1.5,0,1,0,14,6.5,1.5,1.5,0,0,0,12.5,8Z" />
            </svg>
          </label>
          @if (Auth::check() && Auth::user()->can('isAuthor', $topicItem->post))
          @include('partials.options_dropdown', [
          "options" => ['edit post' => route('topics.edit',['post_id' => ($topicItem->post_id)])]
          ])

          </form>
          @else
          @include('partials.options_dropdown', [
          "options" => [
          'report post' => "javascript:reportTopic()"
          ]
          ])
          @include('partials.report_box', ['reported_id' =>$topicItem->post_id])
          @endif
        </div>

      </div>
      <a href="{{ $topicItem->news_url ?? '#' }}">
        <p class="my-4 text-4xl md:text-5xl lg:text-6xl font-medium tracking-tight line-clamp-4 overflow-visible">{{
          $topicItem->post->title ?? 'No title available' }}</p>
      </a>

      <div id="post-actions" class="flex flex-row mt-auto text-xl gap-2 items-center">
        <div>
          <input id="favorite-{{$topicItem->post_id}}" type="checkbox" class="hidden peer/favorite" {{ Auth::check() &&
            Auth::user()->favouritePosts->contains($topicItem->post_id) ? 'checked' : '' }}
          name="favorite"
          onchange="toggleFavorite({{ $topicItem->post_id }})">

          <label for="favorite-{{$topicItem->post_id}}"
            class="cursor-pointer peer-checked/favorite:fill-pink-500 cursor-pointer group-hover/wrapper:hover:fill-pink-500 fill-[#3C3D37] transition-all ease-out group-hover/wrapper:fill-[#F4F2ED]">
            <svg class="h-6" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path
                d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
            </svg>
          </label>
        </div>

        <div>
          <input id="{{ $topicItem->post_id }}-upvote" type="checkbox" class="hidden peer/upvote"
            {{ $topicItem->user_upvoted ? 'checked' : '' }} name="vote"
            onclick="handleVisitor('{{ route('login') }}')">
          <label for="{{ $topicItem->post_id }}-upvote"
            class="peer-checked/upvote:fill-blue-400 cursor-pointer hover:fill-blue-400 fill-[#3C3D37] transition-all ease-out">
            <svg class="h-6" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M21,21H3L12,3Z" />
            </svg>
          </label>
        </div>
        

        <span class="mr-2" id="{{ $topicItem->post_id}}-score">
          @php
          $score = $topicItem->upvotes_count - $topicItem->downvotes_count;

          echo $score >= 1000 ? number_format($score / 1000, 1) . 'k' : $score;
          @endphp
        </span>

        <div class="">
          <input id="{{ $topicItem->post_id }}-downvote" type="checkbox" class="hidden peer/downvote"
            {{ $topicItem->user_downvoted ? 'checked' : '' }} name="vote"
            onclick="handleVisitor('{{ route('login') }}')">
          <label for="{{ $topicItem->post_id }}-downvote"
            class="cursor-pointer peer-checked/downvote:fill-red-400 hover:fill-red-400 fill-[#3C3D37] transition-all ease-out">
            <svg class="h-6 rotate-180" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M21,21H3L12,3Z" />
            </svg>
          </label>
        </div>
        

        <button data-toggle="reply-form" data-target="thread">
          <svg class="cursor-pointer ml-4 h-5 min-w-5 hover:fill-blue-400 transition-all ease-out fill-[#3C3D37]"
            viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
            <g id="Layer_2" data-name="Layer 2">
              <g id="invisible_box" data-name="invisible box">
                <rect width="48" height="48" fill="none" />
                <rect width="48" height="48" fill="none" />
              </g>
              <g id="icons_Q2" data-name="icons Q2">
                <path
                  d="M42,4H6A2,2,0,0,0,4,6V42a2,2,0,0,0,2,2,2,2,0,0,0,1.4-.6L15.2,36H42a2,2,0,0,0,2-2V6a2,2,0,0,0-2-2Z" />
              </g>
            </g>
          </svg>
        </button>

        <span>{{ $topicItem->comments_count }}</span>



        <div class="ml-auto hidden text-sm lg:text-base sm:block relative transition-all transform">
          <span>
            {{ $topicItem->post->creation_date ? $topicItem->post->creation_date->diffForHumans() : 'Unknown date' }} by
          </span>
          @if (count($topicItem->post->authors) === 1)
          <a data-name="authors" class="underline-effect"
            href="{{ route('user.profile', $topicItem->post->authors[0]->id) }}">
            {{ $topicItem->post->authors[0]->username ?? 'Unknown' }}
          </a>
          </a>
          @else
          @include('partials.authors_dropdown', [
          'post' => $topicItem
          ])
          @endif
        </div>
      </div>
    </div>
  </div>
  @endif

  @isset($topicItem->post->content)
  <div id="post-content" class="py-4 px-8 flex flex-col gap-4  flex-none">
    <div id="contributors-container" class="flex items-center space-x-2">
      <div class="flex -space-x-4 rtl:space-x-reverse items-center transition-all" id="contributors-images">
        @foreach($topicItem->post->authors->take(4) as $author)
        <a href="{{ route('user.profile', $author->id) }}" class="group">
          <img src="{{ asset($author->image->path ?? '/images/default.jpg') }}" alt="{{ $author->name }}"
            class="w-10 h-10 border-2 border-white rounded-full object-cover ">
        </a>
        @endforeach
        @if($topicItem->post->authors->count() > 4)
        <button id="expand-button"
          class="flex items-center justify-center w-10 h-10 text-xs font-medium text-white bg-slate-600 border-2 border-white rounded-full object-cover hover:bg-gray-600">
          +{{ $topicItem->post->authors->count() - 4 }}
        </button>
        @endif
      </div>
    </div>


    <div data-text="markdown"
      class="break-words text-[#3C3D37] prose-headings:text-[#3C3D37] prose:text-[#3C3D37] font-vollkorn max-w-[95%] prose prose-a:text-[#4793AF]/[.80] hover:prose-a:text-[#4793AF]/[1] prose-blockquote:border-l-4 prose-blockquote:border-[#4793AF]/[.50] prose-code:bg-white/[.50] prose-code:p-1 prose-code:rounded prose-code:text-[#4793AF]">
      {{ $topicItem->post->content }}
    </div>
  </div>
  @endisset


  {{-- comment editor --}}
  <div class="gap-y-2">
    <div class="flex flex-row items-center cursor-text p-8" id="thread-placeholder">
      <a class="size-8 rounded-full object-cover " href="">
        @php
        if(Auth::check()) {$image_id = Auth::user()->image_id;}
        else { $image_id = 1;}
        @endphp
        <img src="{{ asset(Auth::user()->image->path  ?? '/images/default.jpg') }}"
          class="size-8 rounded-full object-cover ">
      </a>
      <span class="text-xl font-light ml-4">start a thread</span>
    </div>

    @include('partials.text_editor_md', [
    'id' => 'thread'
    ])
  </div>

  {{-- comments --}}
  

    {{-- comments wrapper --}}
    <div class="min-w-72">
      {{-- comment thread --}}
      @foreach ($comments as $comment)
      @if (is_null($comment->parent_comment_id))
      @include('partials.comments', ['comment' => $comment, 'margin' => '12'])
      @endif
      @endforeach
    </div>

  </div>
</div>
<script>
  const bgcolors = ['pastelYellow', 'pastelGreen', 'pastelRed', 'pastelBlue'] 
    const randomColor = bgcolors[Math.floor(Math.random() * bgcolors.length)]
    document.getElementById('post-header').classList.add(`bg-${randomColor}`)
    // document.getElementById('post-content').classList.add(`bg-${randomColor}`)
</script>


<script>
  function reportTopic() {
      const authors = @json($topicItem->post->authors->pluck('id')); 
      const form = document.getElementById('reportForm'); 
      form.reset();
      authors.forEach(authorId => {
        const input = document.createElement('input'); 
       input.type = 'hidden';
        input.name = 'reported_user_id[]'; 
        input.value = authorId; 
        form.appendChild(input); 
        });
      document.getElementById('reportForm').action = '{{ route('report') }}';
      document.getElementById('report_type').value = 'topic_report';
      document.getElementById('reported_id').value = '{{ $topicItem->post_id }}';
      document.getElementById('reportTitle').textContent = 'Report all authors';
      document.getElementById('reportModal').classList.remove('hidden');
      
    }

</script>


<script>
  function handleVisitor(loginUrl) {
    @if (!Auth::check())
      window.location.href = loginUrl;
    @endif
  }
</script>

@endsection
