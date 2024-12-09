@extends('layouts.app')

@section('content')
<div class="font-grotesk divide-y-2 divide-[black]">
  {{-- news post --}}
  <div class=" flex flex-row" id="post-header">
    <div class="px-8 py-4 w-1/2 flex flex-col grow">
      <div class="flex items-center h-8 relative">
      <a class="flex items-center" href="{{ route('communities.show', ['id' => $topicItem->post->community->id ?? 'unknown']) }}">
        <img src="{{ asset('images/hub' . $topicItem->post->community->image_id . '.jpg') }}" 
        class="max-w-full rounded-3xl min-w-[32px] mr-3 w-[32px]">
        <span class="text-2xl font-light underline-effect">h/{{ $topicItem->post->community->name ?? 'Unknown Community' }}</span>
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
            <input 
                id="favorite-{{$topicItem->post_id}}" 
                type="checkbox" 
                class="hidden peer/favorite" 
                {{ Auth::check() && Auth::user()->favouritePosts->contains($topicItem->post_id) ? 'checked' : '' }} 
                name="favorite" 
                onchange="toggleFavorite({{ $topicItem->post_id }})">

            <label for="favorite-{{$topicItem->post_id}}" 
                class="cursor-pointer peer-checked/favorite:fill-pink-500 cursor-pointer group-hover/wrapper:hover:fill-pink-500 fill-[#3C3D37] transition-all ease-out group-hover/wrapper:fill-[#F4F2ED]">
                <svg class="h-6" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                </svg>
            </label>
        </div>

        <div>
          <input id="{{$topicItem->post_id}}-upvote" type="checkbox" class="hidden peer/upvote" {{
            $topicItem->user_upvoted ?
          'checked' : '' }} name="vote">
          <label for="{{$topicItem->post_id}}-upvote"
            class=" peer-checked/upvote:fill-blue-400 cursor-pointer hover:fill-blue-400 fill-[#3C3D37] transition-all ease-out ">
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
          <input id="{{$topicItem->post_id}}-downvote" type="checkbox" class="hidden peer/downvote" {{
            $topicItem->user_downvoted
          ?
          'checked' : '' }} name="vote">
          <label for="{{$topicItem->post_id}}-downvote"
            class="cursor-pointer peer-checked/downvote:fill-red-400   hover:fill-red-400 fill-[#3C3D37] transition-all ease-out">
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
              <path
                d="M42,4H6A2,2,0,0,0,4,6V42a2,2,0,0,0,2,2,2,2,0,0,0,1.4-.6L15.2,36H42a2,2,0,0,0,2-2V6a2,2,0,0,0-2-2Z" />
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
  </div>

  @isset($topicItem->post->content)
  <div id="post-content" class="py-4 px-8 flex flex-col gap-4  flex-none">
    {{-- <div>
      <a class="flex items-center" href="">
        <img src="https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png"
          class="max-w-full rounded-3xl min-w-[32px] mr-3  w-[32px]">
        <span class="underline-effect">@anonymous</span>
      </a>
    </div> --}}
    {{-- ml-[{{ $index * 18 }}px] --}}

    <div class="grid cursor-pointer group mr-auto gap-4 items-center">
      @foreach($topicItem->post->authors as $index => $author)
      <a href="{{ route('user.profile', $author->id) }}"
        class="transition-all transform col-start-1 row-start-1 ml-[{{ $index * 14 }}px] group-hover:ml-[{{$index * 36}}px]">
        <img src="{{ asset('images/user' . $author->image_id . '.jpg') }}" 
          class="max-w-full rounded-3xl min-w-[32px] w-[32px]">
      </a>
      @endforeach
      <div class="col-start-2 row-start-1">
        contributors • {{$topicItem->post->creation_date ? $topicItem->post->creation_date->diffForHumans() : 'Unknown
        date'}}
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
      <a class="size-8 rounded-full " href="">
        @php 
          if(Auth::check()) {$image_id = Auth::user()->image_id;}
          else { $image_id = 1;}
        @endphp
        <img src="{{ asset('images/user' . Auth::user()->image_id . '.jpg') }}" class="size-8 rounded-full ">
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

    <div class="relative ml-auto">
      <input type="checkbox" class="peer hidden" id="sort-options">
      <label for="sort-options" class="flex font-light justify-center gap-x-1.5">
        sort by
      </label>

    </div>

    {{-- comments wrapper --}}
    <div class="w-11/12 min-w-72">
      {{-- comment thread --}}
      @foreach ($comments as $comment)
      @if (is_null($comment->parent_comment_id))
      @include('partials.comments', ['comment' => $comment, 'margin' => '12'])
      @endif
      @endforeach
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
        .replace(/^(?!<(h[1-6]|blockquote|hr)[^>]*>).+/gm, '$&<br>')
        .replace(/(<br>\s*){2,}/g, '<br>') // Add <br> for plain text lines
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

    const markdownText = document.querySelectorAll('[data-text="markdown"]')
    markdownText.forEach(element => {
      element.innerHTML = markdownToHTML(element.textContent)
    })

  </script>

  <script>
    const voteButtons = document.querySelectorAll("input[type='checkbox']");

  voteButtons.forEach((button) => {
    button.addEventListener("change", async function () {
      const postId = this.id.split("-")[0]; // Extract the post_id from the input's ID
      const voteType = this.id.includes("upvote") ? "upvote" : "downvote";
      const isChecked = this.checked;

      try {
        // Make an asynchronous request to update the vote
        const response = await fetch(`/news/${postId}/voteupdate`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
          },
          body: JSON.stringify({
            vote_type: voteType,
          }),
        });

        if (response.ok) {
          const data = await response.json();
          console.log(data);
          console.log(data.vote === voteType);

          // Update the score of the specific post
          const scoreElement = document.getElementById(`${postId}-score`);
          if (scoreElement) {
            let newScore = data.newScore;

            // If the score already contains a "k", don't modify it
            if (!scoreElement.textContent.includes("k")) {
              // If the current score doesn't have a "k", add the new score to it
              let currentScore = parseInt(scoreElement.textContent.replace(/[^\d.-]/g, '')); // Get the current numerical score
              newScore = currentScore + newScore; // Add the new score to the existing score
              scoreElement.textContent = newScore >= 1000 ? `${(newScore / 1000).toFixed(1)}k` : newScore;
            }
          }

          if (data.vote === voteType) {          
            this.checked = true;
          } else {
            this.checked = false;
          }
        } else {
          console.error("Failed to update the vote:", await response.text());
        }
      } catch (error) {
        console.error("Error while updating the vote:", error);
      }
    });
  });

  const postId = {{ $topicItem->post_id }};

  // reply comments submit
  const nodes = document.querySelectorAll('div[data-parent-id]');

  nodes.forEach(node => {
    const id = node.getAttribute('data-id');
    const parentId = node.getAttribute('data-parent-id');
    const commentContent = document.getElementById(`editor-${id}-input`);
    const commentHtml = document.getElementById(`editor-${id}-preview`)

    const submitBtn = document.getElementById(`${id}-editor`).querySelector("[name='submit-btn']");

    // Make the event handler async
    submitBtn.addEventListener('click', async () => {  // Use async here
      // Prepare the data to send in the body of the request
      const data = {
        content: commentContent.value,
        parent_comment_id: id,  // If parentId is "null", set it to null
      };

      try {
        // Perform the AJAX call using fetch
        const response = await fetch(`/news/${postId}/comment`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), // Add CSRF token if needed
          },
          body: JSON.stringify(data), // Send the data in JSON format
        });

        if (response.ok) {
          const result = await response.json();
          console.log('Comment successfully posted:', result);
          
          // Assuming the response contains the ID of the newly created comment
          const commentId = result.comment.id;  // Adjust based on actual response structure
          
          // Reload the page and append the new comment's ID to the URL
          window.location.href = `${window.location.href.split('#')[0]}#c-${commentId}`;
          location.reload(true)
          
        } else {
          console.error('Failed to post comment:', await response.text());
        }
      } catch (error) {
        console.error('Error while posting comment:', error);
      }
    })
  })

  const submitNewComment = threadEditor.querySelector('[name="submit-btn"]')
  const newCommentContent = document.getElementById('editor-thread-input')

  submitNewComment.addEventListener('click', async () => {
      const data = {
        content: newCommentContent.value,
        parent_comment_id: null,  
      };

      try {
        const response = await fetch(`/news/${postId}/comment`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), // Add CSRF token if needed
          },
          body: JSON.stringify(data), // Send the data in JSON format
        });

        if (response.ok) {
          const result = await response.json();
          console.log('Comment successfully posted:', result);
          
          const commentId = result.comment.id;
          
          window.location.href = `${window.location.href.split('#')[0]}#c-${commentId}`;
          location.reload(true)
          
        } else {
          console.error('Failed to post comment:', await response.text());
        }
      } catch (error) {
        console.error('Error while posting comment:', error);
      }
  })


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

  @endsection