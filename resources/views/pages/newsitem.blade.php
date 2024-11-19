@extends('layouts.app')

@section('content')
<div class="font-grotesk divide-y-2 divide-[black]">
  {{-- news post --}}
  <div class=" flex flex-row" id="post-header">
    <div class="px-8 py-4 w-1/2 flex flex-col grow">
      <div class="flex items-center h-8">
        <a class="flex items-center" href="">
          <img src="https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png"
            class="max-w-full rounded-3xl min-w-[32px] mr-3  w-[32px]">
          <span class="text-2xl font-light underline-effect">h/{{ $newsItem->post->community->name ?? 'Unknown
            Community' }}</span>
        </a>
        <!-- Edit Button (only if the current authenticated user is an author) -->
        @auth
        <!-- Check if the authenticated user is one of the authors -->
        @if ($newsItem->post->authors->contains('id', Auth::user()->id))
        <a href="{{ route('news.edit', ['post_id' => $newsItem->post->id]) }}" class="btn btn-warning mt-3">Edit
          Post</a>
        @endif
        @endauth

        <svg class="ml-auto h-6 w-6 fill-[#3C3D37] cursor-pointer" xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 16 16">
          <path class="cls-1"
            d="M8,6.5A1.5,1.5,0,1,1,6.5,8,1.5,1.5,0,0,1,8,6.5ZM.5,8A1.5,1.5,0,1,0,2,6.5,1.5,1.5,0,0,0,.5,8Zm12,0A1.5,1.5,0,1,0,14,6.5,1.5,1.5,0,0,0,12.5,8Z" />
        </svg>
      </div>
      <a href="{{ $newsItem->news_url ?? 'No URL available' }}">
        <p class="my-4 text-4xl md:text-5xl lg:text-6xl font-medium tracking-tight line-clamp-4 overflow-visible">{{
          $newsItem->post->title ?? 'No title available' }}</p>
      </a>


      <div id="post-actions" class="flex flex-row mt-auto text-xl gap-2 items-center">
        <div>
          <input id="upvote" type="checkbox" class="hidden peer/upvote">
          <label for="upvote"
            class=" peer-checked/upvote:fill-blue-400 cursor-pointer hover:fill-blue-400 fill-[#3C3D37] transition-all ease-out">
            <svg class="h-7" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M21,21H3L12,3Z" />
            </svg>
          </label>
        </div>

        <span class="mr-2">
          @php
          $score = $newsItem->upvotes_count - $newsItem->downvotes_count;
          echo $score >= 1000 ? number_format($score / 1000, 1) . 'k' : $score;
          @endphp
        </span>

        <div class="">
          <input id="downvote" type="checkbox" class="hidden peer/downvote">
          <label for="downvote"
            class="cursor-pointer peer-checked/downvote:text-red-400 hover:fill-red-400 fill-[#3C3D37] transition-all ease-out">
            <svg class="h-7 rotate-180" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M21,21H3L12,3Z" />
            </svg>
          </label>
        </div>

        <svg class="cursor-pointer ml-4 h-6 min-w-6 hover:fill-blue-400 transition-all ease-out fill-[#3C3D37]"
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

        <span>{{ $newsItem->comments_count }}</span>

        <div class="ml-auto hidden text-sm lg:text-base sm:block text-right">
          <span>{{ $newsItem->post->creation_date ? $newsItem->post->creation_date->diffForHumans() : 'Unknown date' }}
            by</span>
          @foreach ($newsItem->post->authors as $author)
          <a href="{{ route('user.profile', $author->id) }}" class="flex items-center gap-4">
            <!-- Display Author Image -->
            {{-- <img src="{{ $author->image_id ?? '/images/default-profile.png' }}" alt="Author Image"
              class="w-8 h-8 rounded-full object-cover"> --}}
            <!-- Display Author Username -->
            <span class="underline-effect p-4">{{ $author->username ?? 'Unknown'
              }}</span>
          </a>
          @endforeach

        </div>
      </div>
    </div>
    <a href="#" class="w-1/2 md:block hidden">
      <img class=" object-cover  object-left w-full h-full"
        src="https://imagens.publico.pt/imagens.aspx/1955774?tp=UH&db=IMAGENS&type=JPG&share=1&o=BarraFacebook_Publico.png"
        alt="">
    </a>
  </div>

  @isset($newsItem->post->content)
  <div id="post-content" class="py-4 px-8 flex flex-col gap-4">
    {{-- <div>
      <a class="flex items-center" href="">
        <img src="https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png"
          class="max-w-full rounded-3xl min-w-[32px] mr-3  w-[32px]">
        <span class="underline-effect">@anonymous</span>
      </a>
    </div> --}}
    <div data-text="markdown"
      class="break-words font-vollkorn max-w-[95%] prose prose-a:text-[#4793AF]/[.80] hover:prose-a:text-[#4793AF]/[1] prose-blockquote:border-l-4 prose-blockquote:border-[#4793AF]/[.50] prose-code:bg-white/[.50] prose-code:p-1 prose-code:rounded prose-code:text-[#4793AF]">
      {{ $newsItem->post->content }}
    </div>
  </div>
  @endisset


  {{-- comment editor --}}
  <div class="gap-y-2">
    <div class="flex flex-row items-center cursor-text p-8" id="thread-placeholder">
      <a class="min-w-[32px] mr-3 flex flex-col items-center w-[32px]" href="">
        <img src="https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png" class="max-w-full rounded-3xl">
      </a>
      <span class="text-xl font-light">start a thread</span>
    </div>

    @include('partials.text_editor_md', [
    'id' => 'thread'
    ])

    {{-- <div id="thread-editor" class="px-8 py-6 hidden">
      <div class="flex flex-row gap-4">
        <input type="radio" name="toggle" id="editor-write-toggle" class="hidden peer/write" checked />
        <label for="editor-write-toggle"
          class="underline-effect cursor-pointe  peer-checked:aria-selected peer-checked/write:font-bold">
          write
        </label>

        <input type="radio" name="toggle" id="editor-preview-toggle" class="hidden peer/preview" />
        <label for="editor-preview-toggle"
          class="underline-effect cursor-pointer peer-checked:aria-selected peer-checked/preview:font-bold">
          preview
        </label>
      </div>

      <div class="flex flex-col h-full mt-2">
        <!-- Textarea for Markdown Input -->
        <textarea id="editor-thread-input" rows="6"
          class=" w-full p-4 bg-inherit focus:outline-none resize-none font-mono text-sm border border-1 rounded-lg border-[#3C3D37]  hover:resize-y"></textarea>

        <!-- Markdown Preview -->
        <div id="editor-thread-preview"
          class="hidden p-4 break-words max-h-80 h-full font-vollkorn overflow-y-auto prose-a:text-[#4793AF]/[.80] hover:prose-a:text-[#4793AF]/[1] border border-1 rounded-lg border-[#3C3D37]">
        </div>
      </div>
      <div class="justify-end flex gap-4 mt-4">
        @include('partials.button', [
        'id' => 'cancel-thread-btn',
        'slot' => 'Cancel'
        ])
        @include('partials.button', [
        'id' => 'submit-thread-btn',
        'slot' => 'Submit'
        ])
      </div>
    </div> --}}
  </div>

  {{-- comments --}}
  <div class="flex flex-col px-12 py-4 font-grotesk">

    <div class="relative">
      <div>
        <button type="button" class="flex ml-auto font-light justify-center gap-x-1.5 underline-effect" id="menu-button"
          aria-expanded="true" aria-haspopup="true">
          sort by
        </button>
      </div>
      <div
        class="absolute right-0 z-10 mt-2 w-32 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black/5 focus:outline-none transform transition"
        role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
        <div class="py-1" role="none">
          <a href="#"
            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 hover:outline-none"
            role="menuitem" tabindex="-1" id="menu-item-0">
            recent
          </a>
        </div>
        <div class="py-1" role="none">
          <a href="#"
            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 hover:outline-none"
            role="menuitem" tabindex="-1" id="menu-item-5">
            top
          </a>
        </div>
      </div>
    </div>

    {{-- comments wrapper --}}
    <div class="w-11/12 min-w-72">
      {{-- comment thread --}}
      @foreach ($comments as $comment)
        @if (is_null($comment->parent_comment_id))
        @include('partials.comments', ['comment' => $comment])
        @endif
      @endforeach
      {{-- @include('partials.text_editor_md', [
      'id' => 'reply-1'
      ]) --}}
    </div>

  </div>

  <script>
    // Function to convert markdown to HTML
    function markdownToHTML(markdown) {
  const headingClasses = {
      1: "m-0 text-4xl font-bold",
      2: "m-0 text-3xl font-bold",
      3: "m-0 text-2xl font-bold",
      4: "m-0 text-xl font-bold",
      5: "m-0 text-lg font-bold",
      6: "m-0 text-base font-bold",
  } 

  return markdown
      .replace(/^(#{1,6})\s*(.+)$/gm, (_, hashes, content) =>
          `<h${hashes.length} class="${headingClasses[hashes.length]}">${content}</h${hashes.length}>`
      ) // Headings
      .replace(/^>\s*(.+)$/gm, 
          `<blockquote class="prose-blockquote border-l-4 border-[#4793AF]/[.50] pl-4 italic text-gray-700">$1</blockquote>`
      ) // Blockquotes
      .replace(/-{3,}/g, '<hr class="my-4 border-[#4793AF]/[.50]"/>')
      .replace (/\[([^\[]+)\]\(([^\)]+)\)/g, '<a href=\'\$2\'>\$1</a>')
      .replace(/\*\*(.+?)\*\*/g, '<strong class="font-semibold">$1</strong>') // Bold
      .replace(/\*(.+?)\*/g, '<em class="italic">$1</em>') // Italics
      .replace(/`(.*?)`/g, '<code class="bg-gray-200 p-1 rounded text-[#4793AF]">$1</code>') // Inline Code
      .replace()
      .replace(/^(?!<(h[1-6]|blockquote|hr)[^>]*>).+/gm, '$&<br>')  // Add <br> for plain text lines

  }
  
  const bgcolors = ['pastelYellow', 'pastelGreen', 'pastelRed', 'pastelBlue'] 
  const randomColor = bgcolors[Math.floor(Math.random() * bgcolors.length)]

  document.getElementById('post-header').classList.add(`bg-${randomColor}`)
  // document.getElementById('post-content').classList.add(`bg-${randomColor}`)


  const threadPlaceholder = document.getElementById('thread-placeholder') 
  const threadEditor = document.getElementById('thread-editor') 
  
  threadPlaceholder.addEventListener('click', function () {
    threadPlaceholder.classList.add('hidden')
    threadEditor.classList.remove('hidden') 
  }) 

  const replyBtns = document.querySelectorAll("[data-toggle='reply-form']") 

  replyBtns.forEach(btn => {
    btn.addEventListener('click', event => {
      const targetId = btn.getAttribute('data-target') 
      const targetElement = document.getElementById(targetId) 
      console.log(targetElement)
      if (targetElement.classList.contains('hidden')) {
        targetElement.classList.add("block")
        targetElement.classList.remove("hidden")
      } else 
      {
        targetElement.classList.remove("block")
        targetElement.classList.add("hidden")
      }
    }) 
  }) 

  function setupEditor(id) {
    const editor = document.getElementById(`${id}-editor`)
    const textarea = document.getElementById(`editor-${id}-input`) 
    const preview = document.getElementById(`editor-${id}-preview`) 
    
    // Tab key behavior for indentation in textarea
    textarea.addEventListener("keydown", (e) => {
      if (e.key === "Tab") {
        e.preventDefault()  // Prevent default tab behavior (focus shift)

        const start = textarea.selectionStart 
        const end = textarea.selectionEnd 

        // Insert tab character
        textarea.value = textarea.value.substring(0, start) + "  " + textarea.value.substring(end) 

        // Move the cursor after the inserted tab
        textarea.selectionStart = textarea.selectionEnd = start + 2 
      }
    }) 

    // Live preview: update the HTML preview on input change
    textarea.addEventListener('input', (e) => {
      const markdownText = e.target.value 
      preview.innerHTML = markdownToHTML(markdownText, id) 
    }) 

    // Toggle editor visibility based on radio button selection
    document.getElementById(`editor-write-toggle-${id}`).addEventListener('change', () => {
      textarea.classList.remove('hidden') 
      preview.classList.add('hidden') 
    }) 

    document.getElementById(`editor-preview-toggle-${id}`).addEventListener('change', () => {
      textarea.classList.add('hidden') 
      preview.classList.remove('hidden') 
    }) 

    editor.querySelector('[name="cancel-btn"]').addEventListener('click', () => {
      editor.classList.add('hidden')

      if (id === 'thread') {
        document.getElementById('thread-placeholder').classList.remove('hidden')
      }

      preview.innerHTML = ''
      textarea.value = ''
    })
  }

  // Initialize editor for each specific ID
  const editorIds = document.querySelectorAll('[id$="-editor"]')

  editorIds.forEach(editor => {
    const id = editor.id.replace('-editor', '')  // Extract the unique id (e.g., "comment-1")
    setupEditor(id)  // Setup the editor for this specific instance
  }) 
  </script>

  @endsection