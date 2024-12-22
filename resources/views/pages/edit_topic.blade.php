@extends('layouts.app')

@section('content')
<div class="min-h-screen font-grotesk">
  <div class="max-w-4xl mx-auto px-8 py-12">
    <div class="mb-16 group">
      <h1 class="text-6xl font-medium tracking-tight mb-4 transition-all duration-500 group-hover:tracking-widest">
        Edit Topic
      </h1>
      <div class="h-1 w-24 bg-black transition-all duration-500 ease-out group-hover:w-full"></div>
    </div>

    @if (session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-8" role="alert">
      {{ session('success') }}
    </div>
    @endif

    <form method="POST" action="{{ route('topics.update', $topicItem->post->id) }}"
      data-post-id="{{ $topicItem->post->id }}" data-post-type="topic" class="space-y-12" id="update-post-form">
      @csrf
      @method('POST')

      <fieldset>
        <legend class="sr-only">Topic Details</legend>
        <div class="relative">
          <label for="title"
          class="block mb-2 text-2xl font-medium transition-all duration-300 peer-placeholder-shown:text-3xl peer-placeholder-shown:top-2 peer-focus:-top-6 peer-focus:text-2xl">Title</label>
          <input type="text" id="title" name="title" value="{{ old('title', $topicItem->post->title) }}" class="peer w-full text-4xl font-medium bg-transparent border-b-2 border-black/10 
                                  focus:border-black focus:outline-none pb-2 pt-2 placeholder-transparent
                                  transition-all duration-300" placeholder="Enter title" required>
        </div>
      </fieldset>

      <fieldset class="space-y-2">
        <legend class="sr-only">Authors</legend>
        <label class="block text-2xl font-medium text-black/60">Authors</label>
        <div
          class="border-2 border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30">
          <ul class="authors-list divide-y divide-gray-200">
            @foreach($topicItem->post->authors as $author)
            <li class="flex items-center justify-between px-6 py-4 hover:bg-black/5 transition-colors duration-300">
              <span class="text-lg font-medium text-black/70">
                {{ $author->name }}
              </span>
              @if($topicItem->post->authors->count() > 1)
              <button type="button" data-author-id="{{ $author->id }}"
                class="remove-author-btn px-2 py-1 rounded-md bg-red-500/[.80] hover:bg-red-500 text-white font-bold">
                remove
              </button>
              @endif
            </li>
            @endforeach
          </ul>
        </div>
      </fieldset>

      <fieldset class="">
        <legend class="sr-only">Content</legend>

        <div class="flex items-center mb-2">
          <label class="block text-2xl font-medium">Content</label>
          <div class="px-2 relative inline-block">
            <svg
              class="help-trigger w-5 h-5 text-gray-500 hover:text-gray-700 cursor-help transition-colors duration-200"
              xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
              <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            <div
              class="help-tooltip absolute z-50 w-64 p-2 bg-gray-900 text-white text-sm rounded-lg -left-24 bottom-full mb-2">
              <div class="relative">
                Markdown formatting is supported. Use preview mode to see how your content will look.
                <ul class="mt-1 list-disc list-inside">
                  <li># for headers</li>
                  <li>** for bold</li>
                  <li>* for italic</li>
                </ul>
                <div class="absolute h-2 w-2 bg-gray-900 rotate-45 -bottom-1 left-1/2 transform -translate-x-1/2"></div>
              </div>
            </div>
          </div>
        </div>

        <div id="create-editor" class="">
          <div class="flex flex-row gap-4">
            <input type="radio" name="toggle" id="editor-write-toggle-create" class="hidden peer/write" checked />
            <label for="editor-write-toggle-create"
              class="underline-effect cursor-pointer peer-checked:aria-selected peer-checked/write:font-bold">
              write
            </label>

            <input type="radio" name="toggle" id="editor-preview-toggle-create" class="hidden peer/preview" />
            <label for="editor-preview-toggle-create"
              class="underline-effect cursor-pointer peer-checked:aria-selected peer-checked/preview:font-bold">
              preview
            </label>
          </div>
          
          <div class="flex flex-col mt-2" id="create-editors">
            <!-- Textarea for Markdown Input -->
            <textarea name="content" required id="editor-create-input"
              class="min-h-32 w-full p-4 bg-inherit focus:outline-none resize-y font-mono text-sm border border-1 rounded-lg border-[#3C3D37]">{{ old('content',$topicItem->post->content) }}</textarea>

            <!-- Markdown Preview -->
            <div data-text="markdown" id="editor-create-preview"
              class="min-h-32 resize-y hidden p-4 break-words max-h-80 h-full font-vollkorn overflow-y-auto prose-a:text-[#4793AF]/[.80] hover:prose-a:text-[#4793AF]/[1] border border-1 rounded-lg border-[#3C3D37] prose-blockquote:my-2 prose-code:my-4 prose-headings:my-4 prose-hr:my-4">
              {{ old('content',$topicItem->post->content) }}
            </div>
          </div>
        </div>
      </fieldset>

      <div class="flex justify-end">
        <button type="submit"
          class="group relative overflow-hidden inline-flex items-center gap-4 px-8 py-4 bg-black text-white text-xl font-medium transition-transform duration-300 hover:-translate-y-1">
          <span class="relative z-10">Save Changes</span>
          <svg xmlns="http://www.w3.org/2000/svg"
            class="relative z-10 h-6 w-6 transform transition-transform duration-300 group-hover:translate-x-2"
            viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd"
              d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
              clip-rule="evenodd" />
          </svg>
          <div
            class="absolute inset-0 bg-whatsup-green transform translate-y-full transition-transform duration-300 group-hover:translate-y-0">
          </div>
        </button>
      </div>
    </form>

    <div class="flex justify-end mt-4">
      <form id="delete-post-form" action="{{ route('post.delete', ['id' => $topicItem->post_id]) }}" method="POST"
        style="display: inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit"
        class="remove-author-btn px-8 py-4 bg-red-500/[.80] hover:bg-red-500 text-white text-xl font-medium delete-button"
          data-post-id="{{ $topicItem->post_id }}">
          Delete Post
          
        </button>
      </form>
    </div>

  </div>
</div>
<style>
  .help-tooltip {
    visibility: hidden;
    opacity: 0;
    transition: opacity 0.3s;
  }

  .help-trigger:hover+.help-tooltip,
  .help-tooltip:hover {
    visibility: visible;
    opacity: 1;
  }

  .underline-effect {
    position: relative;
  }

  .underline-effect::after {
    content: '';
    position: absolute;
    width: 100%;
    height: 2px;
    bottom: -4px;
    left: 0;
    background-color: currentColor;
    transform: scaleX(0);
    transform-origin: center;
    transition: transform 0.3s ease-out;
  }

  .underline-effect:hover::after,
  input:checked+.underline-effect::after {
    transform: scaleX(1);
  }
</style>

@endsection