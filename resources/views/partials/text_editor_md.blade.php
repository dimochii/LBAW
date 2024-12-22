{{-- comment editor --}}
<div id="{{$id}}-editor" class="px-8 py-6 hidden">
  <div class="flex flex-row gap-4">
    <input type="radio" name="toggle" id="editor-write-toggle-{{$id}}" class="hidden peer/write" checked />
    <label for="editor-write-toggle-{{$id}}"
      class="underline-effect cursor-pointer peer-checked:aria-selected peer-checked/write:font-bold">
      write
    </label>

    <input type="radio" name="toggle" id="editor-preview-toggle-{{$id}}" class="hidden peer/preview" />
    <label for="editor-preview-toggle-{{$id}}"
      class="underline-effect cursor-pointer peer-checked:aria-selected peer-checked/preview:font-bold">
      preview
    </label>
  </div>

  <div class="flex flex-col mt-2" id="{{$id}}-editors">
    <!-- Textarea for Markdown Input -->
    <textarea id="editor-{{$id}}-input"
      class="min-h-32 w-full p-4 bg-inherit focus:outline-none resize-y font-mono text-sm border border-1 rounded-lg border-[#3C3D37]"></textarea>

    <!-- Markdown Preview -->
    <div id="editor-{{$id}}-preview"
      class="min-h-32 resize-y hidden p-4 break-words max-h-80 h-full font-vollkorn overflow-y-auto prose-a:text-[#4793AF]/[.80] hover:prose-a:text-[#4793AF]/[1] border border-1 rounded-lg border-[#3C3D37] prose-blockquote:my-2 prose-code:my-4 prose-headings:my-4 prose-hr:my-4">
    </div>
  </div>
  <div class="justify-end flex gap-4 mt-4">

    @include('partials.button', [
    'name' => 'cancel-btn',
    'slot' => 'cancel'
    ])

    @include('partials.button', [
    'name' => 'submit-btn',
    'slot' => 'submit'
    ])

  </div>
</div>