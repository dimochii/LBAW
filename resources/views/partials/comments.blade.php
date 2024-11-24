<div class="relative mb-3 min-w-72 max-w-full" id="c-{{ $comment->id }}" data-id="{{ $comment->id }}" data-parent-id="{{ is_null($comment->parent_comment_id) ? 'null' : $comment->parent_comment_id}}">
  <div class="flex flex-row mt-5">
    <div class="min-w-[32px] mr-3 flex flex-col items-center w-[32px]">
      <a href="">
        <img src="https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png" class="max-w-full rounded-3xl">
      </a>
      <a href="#c-{{ $comment->id }}"
        class="absolute top-[32px] bg-[#A6A6A6] hover:bg-[#4793AF] w-px hover:w-0.5 h-[calc(100%-32px)] cursor-pointer"></a>
    </div>
    <details open class="group/details-{{ $comment ->id }} mt-2 grow">
      {{-- Comment Header --}}
      <summary class="list-none">
        <div class="text-sm mb-5">
          <a href="#" class="underline-effect">{{ $comment->user->username }}</a>
          <span>•</span>
          <span>{{ $comment->creation_date ? $comment->creation_date->diffForHumans() : '?' }}</span>
          <span>•</span>
          <span class="underline-effect cursor-pointer group-open/details-{{ $comment ->id }}:before:content-['hide']">
          </span>

        </div>
      </summary>

      {{-- Comment Body --}}
      <article data-text="markdown"
        class="font-vollkorn max-w-full prose prose-a:text-[#4793AF]/[.80] hover:prose-a:text-[#4793AF]/[1] ml-1 prose-blockquote:my-2 prose-code:my-4 prose-headings:my-4 prose-hr:my-4">
        {{ $comment->content }}
      </article>

      {{-- Comment Buttons --}}
      <footer class="flex gap-x-1 items-center mt-4">
        {{-- Upvote --}}
        <div>
          <input id="upvote-{{ $comment->id }}" type="checkbox" class="hidden peer/upvote">
          <label for="upvote-{{ $comment->id }}"
            class="peer-checked/upvote:fill-blue-400 cursor-pointer hover:fill-blue-400 transition-all ease-out">
            <svg class="w-5 h-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M21,21H3L12,3Z" />
            </svg>
          </label>
        </div>

        {{-- Score --}}
        <span class="mr-2">
          @php
          $score = $comment->upvotesCount->count() - $comment->downvotesCount->count();
          echo $score >= 1000 ? number_format($score / 1000, 1) . 'k' : $score;
          @endphp
        </span>

        {{-- Downvote --}}
        <div>
          <input id="downvote-{{ $comment->id }}" type="checkbox" class="hidden peer/downvote">
          <label for="downvote-{{ $comment->id }}"
            class="cursor-pointer peer-checked/downvote:fill-red-400 hover:fill-red-400 transition-all ease-out">
            <svg class="w-5 h-5 rotate-180" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M21,21H3L12,3Z" />
            </svg>
          </label>
        </div>

        {{-- Reply Button --}}
        <button data-toggle="reply-form" data-target="{{ $comment->id }}-editor">
          <svg class="ml-2 h-[18px] w-[18px] hover:fill-blue-400 transition-all ease-out" viewBox="0 0 48 48"
            xmlns="http://www.w3.org/2000/svg">
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
        <span>{{ $comment->children->count() }}</span>
      </footer>

      @include('partials.text_editor_md', [
      'id' => $comment->id
      ])

      {{-- Nested Replies --}}
      <div class="ml-{{$margin}}" name="replies">
        @foreach ($comment->children as $childComment)
        @include('partials.comments', ['comment' => $childComment, 'margin' => ($margin / 2) + 1])
        @endforeach
      </div>
    </details>
  </div>
</div>