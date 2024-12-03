@extends('layouts.app')

@section('content')
<div class="font-grotesk divide-y-2 divide-[black]">
  {{-- topic post --}}
  <div class="flex flex-row" id="post-header">
    <div class="px-8 py-4 w-1/2 flex flex-col grow">
      <div class="flex items-center h-8 relative">
        <a class="flex items-center" href="">
          <img src="https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png"
            class="max-w-full rounded-3xl min-w-[32px] mr-3 w-[32px]">
          <span class="text-2xl font-light underline-effect">h/{{ $topicItem->post->community->name ?? 'Unknown Community' }}</span>
        </a>

        <div class="ml-auto">
          <input type="checkbox" class="peer hidden" id="{{$topicItem->post_id}}-options">
          <label for="{{$topicItem->post_id}}-options">
            <svg class="ml-auto h-4 w-4 fill-[#3C3D37] group-hover/wrapper:fill-[#F4F2ED] z-0"
              xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
              <path class="cls-1"
                d="M8,6.5A1.5,1.5,0,1,1,6.5,8,1.5,1.5,0,0,1,8,6.5ZM.5,8A1.5,1.5,0,1,0,2,6.5,1.5,1.5,0,0,0,.5,8Zm12,0A1.5,1.5,0,1,0,14,6.5,1.5,1.5,0,0,0,12.5,8Z" />
            </svg>
          </label>
          @if (Auth::check() && $topicItem->post->authors->contains(Auth::user()->id))
          @include('partials.options_dropdown', [
          "options" => ['edit post' => route('topics.edit',['post_id' => ($topicItem->post_id)])]
          ])
          @endif
        </div>

      </div>
      <a href="{{ $topicItem->topic_url ?? '#' }}">
        <p class="my-4 text-4xl md:text-5xl lg:text-6xl font-medium tracking-tight line-clamp-4 overflow-visible">{{ $topicItem->post->title ?? 'No title available' }}</p>
      </a>

      <div id="post-actions" class="flex flex-row mt-auto text-xl gap-2 items-center">
        <div>
          <input id="{{$topicItem->post_id}}-upvote" type="checkbox" class="hidden peer/upvote" {{ $topicItem->user_upvoted ? 'checked' : '' }} name="vote">
          <label for="{{$topicItem->post_id}}-upvote" class="peer-checked/upvote:fill-blue-400 cursor-pointer hover:fill-blue-400 fill-[#3C3D37] transition-all ease-out ">
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
          <input id="{{$topicItem->post_id}}-downvote" type="checkbox" class="hidden peer/downvote" {{ $topicItem->user_downvoted ? 'checked' : '' }} name="vote">
          <label for="{{$topicItem->post_id}}-downvote" class="cursor-pointer peer-checked/downvote:fill-red-400 hover:fill-red-400 fill-[#3C3D37] transition-all ease-out">
            <svg class="h-6 rotate-180" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M21,21H3L12,3Z" />
            </svg>
          </label>
        </div>

        <svg class="cursor-pointer ml-4 h-5 min-w-5 hover:fill-blue-400 transition-all ease-out fill-[#3C3D37]"
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

        <span>{{ $topicItem->comments_count }}</span>

        <div class="ml-auto hidden text-sm lg:text-base sm:block relative transition-all transform">
          <span>
            {{ $topicItem->post->creation_date ? $topicItem->post->creation_date->diffForHumans() : 'Unknown date' }} by
          </span>
          @if (count($topicItem->post->authors) === 1)
          <a data-name="authors" class="underline-effect">
            {{ $author->username ?? 'Unknown' }}
          </a>
          @else
          @include('partials.authors_dropdown', [
          'post' => $topicItem
          ])
          @endif
        </div>
      </div>
    </div>
    <a href="{{ $topicItem->topic_url ?? '#' }}" class="w-1/2 md:block hidden">
      <img class=" object-cover object-left w-full h-full"
        src="https://imagens.publico.pt/imagens.aspx/1955774?tp=UH&db=IMAGENS&type=JPG&share=1&o=BarraFacebook_Publico.png"
        alt="">
    </a>
  </div>

  @isset($topicItem->post->content)
  <div id="post-content" class="py-4 px-8 flex flex-col gap-4 flex-none">
    <div class="grid cursor-pointer group mr-auto gap-4 items-center">
      @foreach($topicItem->post->authors as $index => $author)
      <a href="{{ route('user.profile', $author->id) }}"
        class="transition-all transform col-start-1 row-start-1 ml-[{{ $index * 14 }}px] group-hover:ml-[{{$index * 36}}px]">
        <img src="https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png"
          class="max-w-full rounded-3xl min-w-[32px] w-[32px]">
      </a>
      @endforeach
      <div class="col-start-2 row-start-1">
        contributors • {{$topicItem->post->creation_date ? $topicItem->post->creation_date->diffForHumans() : 'Unknown date'}}
      </div>
    </div>

    <div data-text="markdown"
      class="break-words font-vollkorn max-w-[95%] prose prose-a:text-[#4793AF]/[.80] hover:prose-a:text-[#4793AF]/[1] prose-blockquote:border-l-4 prose-blockquote:border-[#4793AF]/[.50] prose-code:bg-white/[.50] prose-code:p-1 prose-code:rounded prose-code:text-[#4793AF]">
      {{ $topicItem->post->content }}
    </div>
  </div>
  @endisset

  {{-- comment editor --}}
  <div class="gap-y-2">
    <div class="flex flex-row items-center cursor-text p-8" id="thread-placeholder">
      <a class="min-w-[32px] mr-3 flex flex-col items-center w-[32px]" href="">
        <img src="https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png" class="max-w-full rounded-3xl w-[32px]">
      </a>
      <div class="relative w-full group">
        <form class="text-left gap-y-1" id="comment-form">
          <div class="flex justify-between gap-x-4">
            <div class="flex flex-col w-full">
              <textarea id="comment-body" placeholder="Add a comment..." rows="3"
                class="peer placeholder:text-[#c8c8c8] border border-[#c8c8c8] focus:outline-none focus:border-[#4793AF] w-full p-4 resize-none rounded-md font-light text-xl"></textarea>
              <p id="comment-error-message" class="hidden text-[#F44336]"></p>
            </div>
            <button type="submit" id="submit" class="ml-auto self-start mt-2 bg-[#4793AF] text-white py-2 px-4 rounded-md">
              Submit
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- comment section --}}
  <div id="comments" class="w-full mt-12 pb-8 px-8">
    <div id="comments-sort-toggle" class="ml-auto flex items-center gap-2 text-lg text-[#595959]">
      <span class="text-base">Sort by</span>
      <button id="sort-newest" class="cursor-pointer">Newest</button> |
      <button id="sort-oldest" class="cursor-pointer">Oldest</button>
    </div>


  </div>
</div>
@endsection
