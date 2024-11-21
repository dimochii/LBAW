{{--
post args

image - optional
img_pos = [left, right] optional
news = Bool

--}}

<div class="p-4 hover:bg-[#3C3D37] hover:text-[#F4F2ED] transition ease-out group/wrapper h-full w-full flex flex-col">
  <header class="flex items-center">
    <a class="flex  items-center h-8" href="">
      <img src="https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png"
        class="max-w-full rounded-3xl min-w-[32px] mr-3  w-[32px]">
      <span class="text-xl font-light underline-effect-light">h/{{ $post->post->community->name ?? 'Unknown
        Community' }}</span>
    </a>
    <button class="ml-auto">
      <svg class="ml-auto h-6 w-6 fill-[#3C3D37] group-hover/wrapper:fill-[#F4F2ED]" xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 16 16">
        <path class="cls-1"
          d="M8,6.5A1.5,1.5,0,1,1,6.5,8,1.5,1.5,0,0,1,8,6.5ZM.5,8A1.5,1.5,0,1,0,2,6.5,1.5,1.5,0,0,0,.5,8Zm12,0A1.5,1.5,0,1,0,14,6.5,1.5,1.5,0,0,0,12.5,8Z" />
      </svg>
    </button>
  </header>
  <div class="grow">
    @if ($news)
    {{-- route(news.show, $post->id) --}}
    <a href="{{ '#' }}">
      <p class="my-4 text-4xl md:text-5xl lg:text-6xl font-medium tracking-tight line-clamp-4 overflow-visible">
        {{ $post->post->title ?? 'No title available' }}</p>
    </a>

    @else
    <a href="{{ route(topic.show, $post->id) ?? '#' }}">
      <p class="my-4 text-4xl md:text-5xl lg:text-6xl font-medium tracking-tight line-clamp-4">{{
        $post->post->title ?? 'No title available' }}</p>
    </a>

    @endif

  </div>

  <footer class="flex flex-row mt-auto text-lg gap-2 items-center">
    <div>
      <input id="upvote" type="checkbox" class="hidden peer/upvote">
      <label for="upvote"
        class=" peer-checked/upvote:fill-blue-400 cursor-pointer group-hover/wrapper:hover:fill-blue-400 fill-[#3C3D37] transition-all ease-out group-hover/wrapper:fill-[#F4F2ED]">
        <svg class="h-6" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path d="M21,21H3L12,3Z" />
        </svg>
      </label>
    </div>

    <span class="mr-2">
      @php
      $score = $post->upvotes_count - $post->downvotes_count;
      echo $score >= 1000 ? number_format($score / 1000, 1) . 'k' : $score;
      @endphp
    </span>

    <div class="">
      <input id="downvote" type="checkbox" class="hidden peer/downvote">
      <label for="downvote"
        class="cursor-pointer peer-checked/downvote:text-red-400  group-hover/wrapper:fill-[#F4F2ED] group-hover/wrapper:hover:fill-red-400 fill-[#3C3D37] transition-all ease-out">
        <svg class="h-6 rotate-180" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path d="M21,21H3L12,3Z" />
        </svg>
      </label>
    </div>

    <svg
      class="cursor-pointer ml-4 h-5 min-w-5 hover:fill-blue-400 transition-all ease-out fill-[#3C3D37] group-hover/wrapper:fill-[#F4F2ED] group-hover/wrapper:hover:fill-blue-400"
      viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
      <g id="Layer_2" data-name="Layer 2">
        <g id="invisible_box" data-name="invisible box">
          <rect width="48" height="48" fill="none" />
          <rect width="48" height="48" fill="none" />
        </g>
        <g id="icons_Q2" data-name="icons Q2">
          <path d="M42,4H6A2,2,0,0,0,4,6V42a2,2,0,0,0,2,2,2,2,0,0,0,1.4-.6L15.2,36H42a2,2,0,0,0,2-2V6a2,2,0,0,0-2-2Z" />
        </g>
      </g>
    </svg>

    <span>{{ $post->comments_count }}</span>

    <div class="relative ml-auto hidden text-sm lg:text-base sm:block">
      <span>
        {{ $post->post->creation_date ? $post->post->creation_date->diffForHumans() : 'Unknown date' }}
        by
      </span>
      @if (count($post->post->authors) === 1)
      <a data-name="authors" class="underline-effect-light">
        {{ $author->username ?? 'Unknown' }}
      </a>
      @else
      @include('partials.authors_dropdown')
      @endif
    </div>
  </footer>
</div>