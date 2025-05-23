{{--
post args

image - optional
img_left = true -> left, false -> right
news = Bool

--}}

<div data-post="{{$post->post_id}}"
  class="p-4 hover:bg-[#3C3D37] hover:text-[#F4F2ED] transition ease-out group/wrapper h-full w-full flex {{ isset($img_left) && $img_left ? 'flex-row' : 'flex-row-reverse' }}">
  <div class="h-full w-full flex-col flex">
    <header class="flex items-center relative">
      <a class="flex  items-center h-8" href="">
        <img src="https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png"
       
          class="max-w-full rounded-3xl min-w-[32px] mr-3  w-[32px]">
        <span class="text-xl font-light underline-effect-light">h/{{ $post->post->community->name ?? 'Unknown
          Community' }}</span>
      </a>
      <div class="inline cursor-pointer pb-4 group ml-auto z-0">
        <input type="checkbox" class="peer hidden" id="{{$post->post_id}}-options">
        <label for="{{$post->post_id}}-options">
          <svg class="ml-auto h-4 w-4 fill-[#3C3D37] group-hover/wrapper:fill-[#F4F2ED] z-0"
            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
            <path class="cls-1"
              d="M8,6.5A1.5,1.5,0,1,1,6.5,8,1.5,1.5,0,0,1,8,6.5ZM.5,8A1.5,1.5,0,1,0,2,6.5,1.5,1.5,0,0,0,.5,8Zm12,0A1.5,1.5,0,1,0,14,6.5,1.5,1.5,0,0,0,12.5,8Z" />
          </svg>
        </label>
        @if (Auth::check() && Auth::user()->can('isAuthor', $newsItem->post))
            @include('partials.options_dropdown', [
                "options" => [
                    'edit post' => route('news.edit', ['post_id' => $newsItem->post_id]),
                    // 'delete post' => route() -> incluir rota para delete
                ]
            ])
        @else
            @include('partials.options_dropdown', [
                "options" => [
                    'report post' => "javascript:reportNews()"
                ]
            ])
            @include('partials.report_box')
        @endif
      </div>


    </header>
    <div class="grow">
      @if ($news)
      <a href="{{ route('news.show',['post_id' => ($post->post->id)]) ?? '#' }}">
        <p class="my-4 text-4xl md:text-5xl lg:text-6xl font-medium tracking-tight line-clamp-4 overflow-visible">
          {{ $post->post->title ?? 'No title available' }}</p>
      </a>

      @else
      <a href="{{ route('topic.show', ['post_id' => ($post->post->id)]) ?? '#' }}">
        <p class="my-4 text-4xl md:text-5xl lg:text-6xl font-medium tracking-tight line-clamp-4">{{
          $post->post->title ?? 'No title available' }}</p>
      </a>

      @endif

    </div>

    <footer class="flex flex-row mt-auto text-lg gap-2 items-center">
      <div>
      <input id="{{ $post->post_id }}-upvote" type="checkbox" class="hidden peer/upvote" {{ $post->user_upvoted ? 'checked' : '' }} name="vote">
        <label for="{{$post->post_id}}-upvote"
          class=" peer-checked/upvote:fill-blue-400 cursor-pointer group-hover/wrapper:hover:fill-blue-400 fill-[#3C3D37] transition-all ease-out group-hover/wrapper:fill-[#F4F2ED]">
          <svg class="h-6" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M21,21H3L12,3Z" />
          </svg>
        </label>
      </div>

      <span class="mr-2" id="{{ $post->post_id}}-score">
        @php
        
        $score = $post->upvotes_count - $post->downvotes_count;
        echo $score >= 1000 ? number_format($score / 1000, 1) . 'k' : $score;
        @endphp
      </span>

      <div class="">
      <input id="{{ $post->post_id }}-upvote" type="checkbox" class="hidden peer/upvote" {{ $post->user_upvoted ? 'checked' : '' }} name="vote">
        <label for="{{$post->post_id}}-downvote"
          class="cursor-pointer peer-checked/downvote:fill-red-400  group-hover/wrapper:fill-[#F4F2ED] group-hover/wrapper:hover:fill-red-400 fill-[#3C3D37] transition-all ease-out">
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
            <path
              d="M42,4H6A2,2,0,0,0,4,6V42a2,2,0,0,0,2,2,2,2,0,0,0,1.4-.6L15.2,36H42a2,2,0,0,0,2-2V6a2,2,0,0,0-2-2Z" />
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
          {{ $post->post->authors[0]->username ?? 'Unknown' }}
        </a>
        @else
        @include('partials.authors_dropdown')
        @endif
      </div>
    </footer>
  </div>
  @if(isset($img) && isset($img_left))
  <a href="{{ $post->news_url ?? '#' }}"
    class="md:block hidden min-w-[50%] max-w-[50%] {{ $img_left ? 'ml-4' : 'mr-4' }}">
    <img class="object-cover object-left w-full h-full"
      src="https://imagens.publico.pt/imagens.aspx/1955774?tp=UH&db=IMAGENS&type=JPG&share=1&o=BarraFacebook_Publico.png"
      alt="">
  </a>

  @endif
</div>
