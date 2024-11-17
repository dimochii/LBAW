@extends('layouts.app')

@section('content')
<div class="font-grotesk divide-y-4 divide-[black]">
  {{-- news post --}}
  <div class="px-8 py-4 flex flex-row gap-8" id="post-header">
    <div class="w-1/2 flex flex-col grow">
      <div class="flex items-center h-8">
        <a class="flex items-center" href="">
          <img src="https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png"
            class="max-w-full rounded-3xl min-w-[32px] mr-3  w-[32px]">
          <span class="text-2xl font-light underline-effect">h/hub</span>
        </a>
        <x-bi-three-dots class="ml-auto h-6 w-6" />
      </div>
      <a href="#">
        <p class="my-4 text-5xl md:text-6xl lg:text-7xl font-medium tracking-tight line-clamp-4">Rússia lança “ataque maciço”
          ao sistema eléctrico ucraniano. Polónia mobiliza caças</p>
      </a>
      
      <div id="post-actions" class="flex flex-row mt-auto text-xl gap-2 items-center">
        <div>
          <input id="upvote" type="checkbox" class="hidden peer/upvote">
          <label for="upvote" class="cursor-pointer peer-checked/upvote:text-blue-400">
            <x-ionicon-triangle-sharp class="cursor-pointer h-6 hover:text-blue-400 transition-all ease-out" />
          </label>
        </div>

        <span class="mr-2">1.2k</span>

        <div class="">
          <input id="downvote" type="checkbox" class="hidden peer/downvote">
          <!-- Default icon (unchecked) -->
          <label for="downvote" class="cursor-pointer peer-checked/downvote:text-red-400">
            <x-ionicon-triangle-sharp
              class="cursor-pointer h-6 rotate-180 hover:text-red-400 transition-all ease-out" />
          </label>
        </div>

        <x-bxs-comment class="cursor-pointer ml-4 h-6 min-w-6 hover:text-blue-400 transition-all ease-out" />
        <span>105</span>

        <div class="ml-auto hidden text-sm lg:text-base sm:block text-right">
          <span>7 hours ago by</span>
          <a href="#" class="underline-effect">@anonymous</a>
        </div>
      </div>
    </div>
    <a href="#" class="w-1/2 md:block hidden">
      <img class="object-cover aspect-[4/3] object-left w-full h-full"
        src="https://imagens.publico.pt/imagens.aspx/1955774?tp=UH&db=IMAGENS&type=JPG&share=1&o=BarraFacebook_Publico.png"
        alt="">
    </a>
  </div>

  {{--  comment editor --}}
  <div class="gap-y-2">
    <div class="flex flex-row items-center cursor-text p-8" id="thread-placeholder">
      <a class="min-w-[32px] mr-3 flex flex-col items-center w-[32px]" href="">
        <img src="https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png" class="max-w-full rounded-3xl">
      </a>
      <span class="text-xl font-light">start a thread</span>
    </div>

    <div id="thread-editor" class="px-8 py-6 hidden">
      <div name="tabs" class="flex flex-row gap-4">
        <input type="radio" name="toggle" id="markdown-write-toggle" class="hidden peer/write" checked />
        <label for="markdown-write-toggle"
          class="underline-effect cursor-pointe  peer-checked:aria-selected peer-checked/write:font-bold">
          write
        </label>

        <input type="radio" name="toggle" id="markdown-preview-toggle" class="hidden peer/preview" />
        <label for="markdown-preview-toggle"
          class="underline-effect cursor-pointer peer-checked:aria-selected peer-checked/preview:font-bold">
          preview
        </label>
      </div>

      <div class="flex flex-col h-full mt-2">
        <!-- Textarea for Markdown Input -->
        <textarea id="markdown-input" rows="6"
          class="w-full p-4 bg-inherit focus:outline-none resize-none font-mono text-sm border border-1 rounded-lg border-[#3C3D37]  hover:resize-y"></textarea>

        <!-- Markdown Preview -->
        <div id="markdown-preview"
          class="hidden p-4 break-words max-h-80 h-full font-vollkorn overflow-y-auto prose-a:text-[#4793AF]/[.80] hover:prose-a:text-[#4793AF]/[1] border border-1 rounded-lg border-[#3C3D37]">
        </div>
      </div>
      <div class="justify-end flex gap-4 mt-4">
        <button id="cancel-thread-btn"
          class="px-2 py-1 rounded-md bg-[#4793AF]/[.50] hover:bg-[#4793AF]/[.80] text-white font-bold">
          Cancel
        </button>
        <button id="submit-thread-btn"
          class="px-2 rounded-md bg-[#4793AF]/[.50] hover:bg-[#4793AF]/[.80] text-white font-bold">
          Submit
        </button>
      </div>
    </div>
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
      <div class="comment relative mb-3 min-w-72 max-w-full" id="c-1">
        <div class=" flex flex-row mt-5">
          <div class="min-w-[32px] mr-3 flex flex-col items-center w-[32px]">
            <a href="">
              <img src="https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png"
                class="max-w-full rounded-3xl">
            </a>
  
            <a href="#c-1"
              class="absolute top-[32px] bg-[#A6A6A6] hover:bg-[#4793AF] w-px hover:w-0.5 h-[calc(100%-32px)] cursor-pointer">
            </a>
          </div>
          <details open class="group/details mt-2 grow">
            {{-- comment header --}}
            <summary class="list-none">
              <div class="text-sm mb-5">
                <a href="#" class="underline-effect">@anonymous</a>
                <span>•</span>
                <span>7 hours ago</span>
                <span>•</span>
                <span class="underline-effect cursor-pointer group-open/details:before:content-['hide']">
                </span>
              </div>
            </summary>
            {{-- comment body --}}
            <article
              class="font-vollkorn max-w-full prose prose-a:text-[#4793AF]/[.80] hover:prose-a:text-[#4793AF]/[1] prose-blockquote:border-l-4 prose-blockquote:border-[#4793AF]/[.50]">
              <p>Succedere deus inplet <a href="http://estsedes.org/ubi">deum</a> infamia satam expellitur
                cadunt animoque cognoscenda quam adhibete; inmania. Legit si fratri sceptro,
                mihi <em>eu quiero despacito</em>, ripam. <em>Nares leves</em> torvo pervia pigneror
                perterrita cogente pastor licebit luctus. Iuvenis positosque indignanda ausim
                tenebant digna concubiturus imbres, Danaen, ante iuvencae licet optavit arvo!
                <em>Pompa</em> qui: eadem modulatur mores, <strong>proque</strong>, Tartessia cupidine potior saevam
                medicamine bos Prima volucres sistere?
              </p>
              <blockquote>
                <p>Cadunt animoque cognoscenda quam adhibete;
                  Anónimo, 2003
                </p>
              </blockquote>
              <p>Lorem Ipsum again</p>
            </article>
            {{-- comment buttons row --}}
            <footer class="flex gap-x-1 items-center mt-4">
              <div>
                <input id="upvote" type="checkbox" class="hidden peer/upvote">
                <!-- Default icon (unchecked) -->
                <label for="upvote" class="cursor-pointer peer-checked/upvote:text-blue-400">
                  <x-ionicon-triangle-sharp class="cursor-pointer h-4 hover:text-blue-400" />
                </label>
              </div>
  
              <span class="mr-2">1.2k</span>
  
              <div class="">
                <input id="downvote" type="checkbox" class="hidden peer/downvote">
                <!-- Default icon (unchecked) -->
                <label for="downvote" class="cursor-pointer peer-checked/downvote:text-red-400">
                  <x-ionicon-triangle-sharp class="cursor-pointer h-4 rotate-180 hover:text-red-400" />
                </label>
              </div>
  
              <x-bxs-comment class="ml-4 h-4" />
              <span>105</span>
  
              <x-eva-flag class="ml-auto h-5 hover:text-red-400 cursor-pointer" />
            </footer>
          </details>
        </div>
        {{-- replies --}}
        <div class="replies pl-96">
  
        </div>
  
      </div>
    </div>
  </div>

</div>

<script>
  const bgcolors = ['pastelYellow', 'pastelGreen', 'pastelRed', 'pastelBlue'];
  const randomColor = bgcolors[Math.floor(Math.random() * bgcolors.length)]

  document.getElementById('post-header').classList.add(`bg-${randomColor}`)

  // Get references to the radio buttons
  const writeToggle = document.getElementById('markdown-write-toggle');
  const previewToggle = document.getElementById('markdown-preview-toggle');
  const markdownInput = document.getElementById('markdown-input');
  const markdownPreview = document.getElementById('markdown-preview');

  // When the "write" radio is checked
  writeToggle.addEventListener('change', () => {
    // Show the textarea and hide the preview
    markdownInput.classList.remove('hidden');
    markdownPreview.classList.add('hidden');
  });

  // When the "preview" radio is checked
  previewToggle.addEventListener('change', () => {
    // Show the preview and hide the textarea
    markdownInput.classList.add('hidden');
    markdownPreview.classList.remove('hidden');
  });
  // Function to convert markdown to HTML
  function markdownToHTML(markdown) {
  const headingClasses = {
      1: "m-0 text-4xl font-bold",
      2: "m-0 text-3xl font-bold",
      3: "m-0 text-2xl font-bold",
      4: "m-0 text-xl font-bold",
      5: "m-0 text-lg font-bold",
      6: "m-0 text-base font-bold",
  };

  return markdown
      .replace(/^(#{1,6})\s*(.+)$/gm, (_, hashes, content) =>
          `<h${hashes.length} class="${headingClasses[hashes.length]}">${content}</h${hashes.length}>`
      ) // Headings
      .replace(/^>\s*(.+)$/gm, 
          `<blockquote class="prose-blockquote border-l-4 border-[#4793AF]/[.50] pl-4 italic text-gray-700">$1</blockquote>`
      )
      .replace(/-{3,}/g, '<hr class="my-4 border-[#4793AF]/[.50]"/>')
      .replace (/\[([^\[]+)\]\(([^\)]+)\)/g, '<a href=\'\$2\'>\$1</a>')
      .replace(/\*\*(.+?)\*\*/g, '<strong class="font-semibold">$1</strong>') // Bold
      .replace(/\*(.+?)\*/g, '<em class="italic">$1</em>') // Italics
      .replace(/`(.*?)`/g, '<code class="bg-gray-200 p-1 rounded text-[#4793AF]">$1</code>') // Inline Code
      .replace()
      .replace(/^(?!<(h[1-6]|blockquote|hr)[^>]*>).+/gm, '$&<br>'); // Add <br> for plain text lines
      
  }

  document.getElementById("markdown-input").addEventListener("keydown", (e) => {
  if (e.key === "Tab") {
      e.preventDefault(); // Prevent default tab behavior (focus shift)
      
      const textarea = e.target;
      const start = textarea.selectionStart;
      const end = textarea.selectionEnd;

      // Insert tab character
      textarea.value = textarea.value.substring(0, start) + "  " + textarea.value.substring(end);

      // Move the cursor after the inserted tab
      textarea.selectionStart = textarea.selectionEnd = start + 1;
  }
});

  // Set up live preview
  document.getElementById('markdown-input').addEventListener('input', (e) => {
      const markdownText = e.target.value;
      const preview = document.getElementById('markdown-preview');
      preview.innerHTML = markdownToHTML(markdownText);
  });

    // Get references to the elements
  const threadPlaceholder = document.getElementById('thread-placeholder');
  const threadEditor = document.getElementById('thread-editor');
  const cancelThreadBtn = document.getElementById('cancel-thread-btn');
  const textarea = document.getElementById("markdown-input")
  const preview = document.getElementById('markdown-preview')

  threadPlaceholder.addEventListener('click', function () {
    threadPlaceholder.classList.add('hidden')
    threadEditor.classList.remove('hidden') 
  });

  cancelThreadBtn.addEventListener('click', function () {
    threadEditor.classList.add('hidden')
    threadPlaceholder.classList.remove('hidden')
    textarea.value = ''
    preview.innerHTML = ''
  });
</script>

@endsection