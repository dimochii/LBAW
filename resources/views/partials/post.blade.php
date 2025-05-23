{{--
post args

image - optional
img_left = true -> left, false -> right
news = Bool

--}}

<div data-post="{{$post->post_id}}"
  class="p-4  hover:bg-[#3C3D37] hover:text-[#F4F2ED] transition ease-out group/wrapper h-full w-full flex {{ isset($img_left) && $img_left ? 'flex-row' : 'flex-row-reverse' }}">
  <div class="h-full w-full flex-col flex gap-4">
    <header class="flex items-center relative">
      <a class="flex items-center h-8"
        href="{{ route('communities.show', ['id' => $post->post->community->id ?? 'unknown']) }}">
        <img
          src="{{ $post->post->community->image_id ? asset($post->post->community->image->path) : 'images/groupdefault.jpg' }}"
          onerror="this.onerror=null;this.src='https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png';"
          class="size-8 rounded-full object-cover">

        <span class="text-xl font-light underline-effect-light px-2">h/{{ $post->post->community->name ?? 'Unknown
          Community'
          }}</span>
      </a>
      <span class="ml-2 text-xs font-semibold px-2 py-1 rounded-md 
    {{ $news ? 'bg-pastelBlue text-[#F4F2ED]' : 'bg-pastelGreen text-[#F4F2ED]' }} transition ease-out">
        {{ $news ? 'News' : 'Topic' }}
      </span>
      <div class="inline cursor-pointer pb-4 group ml-auto z-0">
        <input type="checkbox" class="peer hidden" id="{{$post->post_id}}-options">
        <label for="{{$post->post_id}}-options">
          <svg class="ml-auto h-4 w-4 fill-[#3C3D37] group-hover/wrapper:fill-[#F4F2ED] z-0"
            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
            <path class="cls-1"
              d="M8,6.5A1.5,1.5,0,1,1,6.5,8,1.5,1.5,0,0,1,8,6.5ZM.5,8A1.5,1.5,0,1,0,2,6.5,1.5,1.5,0,0,0,.5,8Zm12,0A1.5,1.5,0,1,0,14,6.5,1.5,1.5,0,0,0,12.5,8Z" />
          </svg>
        </label>
        @if (Auth::check() && Auth::user()->can('isAuthor', $post->post))
        @if ($news)
        @include('partials.options_dropdown', [
        "options" => [
        'edit post' => route('news.edit', ['post_id' => $post->post_id]),
        // 'delete post' => route() -> incluir rota para delete
        ]
        ])
        @else
        @include('partials.options_dropdown', [
        "options" => [
        'edit post' => route('topics.edit', ['post_id' => $post->post_id])
        ]
        ])
        @endif
        @else
        @include('partials.options_dropdown', [
        "options" => [
        'Report post' => "javascript:reportNews()"
        ]
        ])
        @include('partials.report_box', ['reported_id' => $post->post_id])
        @endif

      </div>
    </header>

    <div class="grow">

      @if ($news)

      <a href="{{ route('news.show',['post_id' => ($post->post->id)]) ?? '#' }}"
        class="inline my-4 text-4xl md:text-5xl lg:text-6xl font-medium tracking-tight line-clamp-4 overflow-visible">
        {{ $post->post->title ?? 'No title available' }}</a>

      @if ($post->news_url)
      {{-- <a href="{{$post->news_url}}"
        class="inline ml-2 text-sm lg:text-base text-[#3C3D37]/[.7] group-hover/wrapper:text-gray-300 underline-effect-light"
        data-content="news-url">{{$post->news_url}}</a> --}}
      @endif
      @else
      <a href="{{ route('topic.show',['post_id' => ($post->post->id)]) ?? '#' }}">
        <p class="text-4xl md:text-5xl lg:text-6xl font-medium tracking-tight line-clamp-4 overflow-visible">{{
          $post->post->title ?? 'No title available' }}</p>
      </a>

      @endif

    </div>

    {{-- action="{{ route('news.upvote', ['post_id' => $post->post->id]) }}"
    @if($post->user_upvoted) fill-green-500 @else fill-[#3C3D37] group-hover:fill-blue-400 @endif"
    --}}

    <footer class="flex flex-row mt-auto text-lg gap-2 items-center">
      <div>
        <input id="favorite-{{$post->post_id}}" type="checkbox" class="hidden peer/favorite" {{ Auth::check() &&
          Auth::user()->favouritePosts->contains($post->post_id) ? 'checked' : '' }}
        name="favorite"
        onchange="toggleFavorite({{ $post->post_id }})">

        <label for="favorite-{{$post->post_id}}"
          class="cursor-pointer peer-checked/favorite:fill-pink-500 cursor-pointer group-hover/wrapper:hover:fill-pink-500 fill-[#3C3D37] transition-all ease-out group-hover/wrapper:fill-[#F4F2ED]">
          <svg class="h-6" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
          </svg>
        </label>
      </div>
      @php
      if(Auth::check()){
      $userVote = $item->userVote(Auth::user()->id);}
      else
      $userVote = false;
      @endphp
      <div>
        <input id="{{$post->post_id}}-upvote" type="checkbox" class="hidden peer/upvote" {{($userVote?->upvote ?? false)
        ?
        'checked' : '' }} name="vote">
        <label for="{{$post->post_id}}-upvote"
          class=" peer-checked/upvote:fill-blue-400 cursor-pointer group-hover/wrapper:hover:fill-blue-400 fill-[#3C3D37] transition-all ease-out group-hover/wrapper:fill-[#F4F2ED]">
          <svg class="h-6" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M21,21H3L12,3Z" />
          </svg>
        </label>
      </div>

      <span class="mr-2" id="{{ $post->post_id}}-score">
        @php
        $score = $item->getUpvoteCountAttribute() - $item->getDownvoteCountAttribute();
        echo $score >= 1000 ? number_format($score / 1000, 1) . 'k' : $score;
        @endphp
      </span>

      <div class="">
        <input id="{{$post->post_id}}-downvote" type="checkbox" class="hidden peer/downvote" {{ ($userVote ?
          !$userVote->upvote : false) ?
        'checked' : '' }} name="vote">
        <label for="{{$post->post_id}}-downvote"
          class="cursor-pointer peer-checked/downvote:fill-red-400  group-hover/wrapper:fill-[#F4F2ED] group-hover/wrapper:hover:fill-red-400 fill-[#3C3D37] transition-all ease-out">
          <svg class="h-6 rotate-180" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M21,21H3L12,3Z" />
          </svg>
        </label>
      </div>

      @if ($news)

      <a href="{{ route('news.show',['post_id' => ($post->post->id)]) ?? '#' }}" >
        <svg
          class="cursor-pointer ml-4 h-5 min-w-5 hover:fill-blue-400 transition-all ease-out fill-[#3C3D37] group-hover/wrapper:fill-[#F4F2ED] group-hover/wrapper:hover:fill-blue-400"
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
      </a>

      @else
      <a href="{{ route('topic.show',['post_id' => ($post->post->id)]) ?? '#' }}" >
        <svg
          class="cursor-pointer ml-4 h-5 min-w-5 hover:fill-blue-400 transition-all ease-out fill-[#3C3D37] group-hover/wrapper:fill-[#F4F2ED] group-hover/wrapper:hover:fill-blue-400"
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
      </a>
      @endif


      <span>{{ count($post->post->comments) }}</span>




      <div class="relative ml-auto hidden text-sm lg:text-base sm:block">
        <span>
          {{ $post->post->creation_date ? $post->post->creation_date->diffForHumans() : 'Unknown date' }}
          by
        </span>
        @if (count($post->post->authors) === 1)
        <a data-name="authors" class="underline-effect-light"
          href="{{ route('user.profile', $post->post->authors[0]->id) }}">
          {{ $post->post->authors[0]->username ?? 'Unknown' }}
        </a>
        @else
        @include('partials.authors_dropdown')
        @endif
      </div>
    </footer>
  </div>
  @if(isset($img) && isset($img_left) && !is_null($post->image_url))
  <a href="{{ $post->news_url ?? '#' }}"
    class="md:block hidden min-w-[40%] max-w-[40%] {{ $img_left ? 'ml-4' : 'mr-4' }}">
    <img class="object-cover object-left w-full h-full" src="{{ $post->image_url }}" alt="">
  </a>
  @endif
</div>

<script>
  
function reportNews() {
  const authors = @json($post->post->authors->pluck('id')); 
  const form = document.getElementById('reportForm'); 
  form.reset();
  authors.forEach(authorId => {
    const input = document.createElement('input'); 
    input.type = 'hidden';
    input.name = 'reported_user_id[]'; 
    input.value = authorId; 
    form.appendChild(input); 
    });
  document.getElementById('reportForm').action = '{{ route('report')  }}';
  document.getElementById('report_type').value = 'item_report';
  document.getElementById('reported_id').value = '{{ $post->post_id }}';
  document.getElementById('reportTitle').textContent = 'Report all authors';
  document.getElementById('reportModal').classList.remove('hidden');
}

function reportTopic() {
  const authors = @json($post->post->authors->pluck('id')); 
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
  document.getElementById('reported_id').value = '{{ $post->post_id }}';
  document.getElementById('reportTitle').textContent = 'Report all authors';
  document.getElementById('reportModal').classList.remove('hidden');
  
}

</script>