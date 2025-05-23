@extends('layouts.app')

@section('content')
<div class="min-h-screen font-grotesk">
  <div class="max-w-4xl mx-auto px-8 py-12">
    <div class="mb-16 group">
      <h1 class="text-6xl font-medium tracking-tight mb-4 transition-all duration-500 group-hover:tracking-widest">
        Create Post
      </h1>
      <div class="h-1 w-24 bg-black transition-all duration-500 ease-out group-hover:w-full"></div>
    </div>

    <form method="POST" action="{{ route('post.store') }}" class="space-y-12">
      @csrf

      <fieldset class="border p-6 space-y-4 rounded-lg">
        <legend class="text-3xl font-medium">Post Information</legend>

        <div class="relative">
          <label for="title" class="absolute left-0 -top-6 text-2xl font-medium text-black/60 
                                  transition-all duration-300 peer-placeholder-shown:text-3xl 
                                  peer-placeholder-shown:top-2 peer-focus:-top-6 peer-focus:text-2xl">
            Title
          </label>
          <input type="text" id="title" name="title" class="peer w-full text-4xl font-medium bg-transparent border-b-2 border-black/10 
                                  focus:border-black focus:outline-none pb-2 pt-2 placeholder-transparent
                                  transition-all duration-300 mt-4" placeholder="Enter title" required>
        </div>
        <div class="py-2"></div>
        <div class="relative">
          <label for="community" class="absolute left-0 -top-6 text-2xl font-medium text-black/60
                                  transition-all duration-300 peer-placeholder-shown:text-3xl
                                  peer-placeholder-shown:top-2 peer-focus:-top-6 peer-focus:text-2xl">
            Select Community <span class="text-red-500">*</span>
          </label>
          <div class="relative group py-4">
            <select name="community_id" id="community" class="peer w-full text-xl font-medium bg-transparent border-b-2 border-black/10
                                       focus:border-black focus:outline-none pb-2 pt-2 placeholder-transparent
                                       transition-all duration-300" required>
              <option value="" disabled selected>Select a Community</option>
              @foreach(Auth::user()->communities as $community)
              <option value="{{ $community->id }}">/{{ $community->name }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div
          class="space-y-6 border-2 border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30 p-6">
          <label class="block text-2xl font-medium">Post Type</label>
          <div class="flex gap-8">
            <label class="relative group flex items-center gap-3 cursor-pointer">
              <input type="radio" name="type" value="news" class="peer hidden" id="postTypeNews" checked>
              <div class="w-6 h-6 border-2 rounded-full relative transition-colors duration-300
                                group-hover:border-green-500 peer-checked:border-green-500">
                <div class="absolute inset-1 rounded-full bg-green-500 transform scale-0 
                                            peer-checked:scale-100 transition-transform duration-300"></div>
              </div>
              <span class="text-xl transition-colors duration-300 peer-checked:text-green-500">News</span>
            </label>

            <!-- Topic Radio -->
            <label class="relative group flex items-center gap-3 cursor-pointer">
              <input type="radio" name="type" value="topic" class="peer hidden" id="postTypeTopic">
              <div class="w-6 h-6 border-2 rounded-full relative transition-colors duration-300
                                group-hover:border-blue-500 peer-checked:border-blue-500">
                <div class="absolute inset-1 rounded-full bg-blue-500 transform scale-0 
                                            peer-checked:scale-100 transition-transform duration-300"></div>
              </div>
              <span class="text-xl transition-colors duration-300 peer-checked:text-blue-500">Topic</span>
            </label>
          </div>
        </div>

        <div id="newsUrlContainer" class="space-y-4 transform transition-all duration-500 origin-top">
          <div class="flex items-center gap-2">
            <label for="news_url" class="block text-2xl font-medium">News URL</label>
            <div class="relative inline-block">
              <svg
                class="help-trigger w-5 h-5 text-gray-500 hover:text-gray-700 cursor-help transition-colors duration-200"
                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
              </svg>
              <div
                class="help-tooltip absolute z-50 w-72 p-2 bg-gray-900 text-white text-sm rounded-lg -left-24 bottom-full mb-2">
                <div class="relative">
                  <p>Leave the title field empty to automatically use the news article's title.</p>
                  <p class="mt-1">The post will automatically fetch and use the first image from the news article.</p>
                  <div class="absolute h-2 w-2 bg-gray-900 rotate-45 -bottom-1 left-1/2 transform -translate-x-1/2">
                  </div>
                </div>
              </div>
            </div>
          </div>
          <input type="url" id="news_url" name="news_url" class="w-full text-lg border-b-2 border-black/10 focus:border-black 
                                  focus:outline-none py-2 px-2 transition-colors duration-300" placeholder="https://">
        </div>

        <div>
          <div class="flex items-center mb-2">
            <label for="authors" class="block text-2xl font-medium mr-2">Additional Authors</label>
            <div class="relative inline-block">
              <svg
                class="help-trigger w-5 h-5 text-gray-500 hover:text-gray-700 cursor-help transition-colors duration-200"
                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
              </svg>
              <div
                class="help-tooltip absolute z-50 w-64 p-2 bg-gray-900 text-white text-sm rounded-lg -left-24 bottom-full mb-2">
                <div class="relative">
                  Hold Shift key to select multiple consecutive authors, or Ctrl key (Cmd on Mac) to select individual
                  authors
                  <div class="absolute h-2 w-2 bg-gray-900 rotate-45 -bottom-1 left-1/2 transform -translate-x-1/2">
                  </div>
                </div>
              </div>
            </div>
          </div>
          <select name="authors[]" id="authors" multiple
            class="w-full rounded text-l border-b-2 border-black/10 focus:border-black focus:outline-none pb-2 transition-all duration-300">
            @foreach(Auth::user()->follows->sortBy('name') as $potentialAuthor)
            <option class="hover:bg-sky-400" value="{{ $potentialAuthor->id }}"
              data-image="{{ $potentialAuthor->profile_photo_url }}">
              {{ $potentialAuthor->name }} ({{ $potentialAuthor->username }})
            </option>
            @endforeach
          </select>
          <p class="text-sm text-gray-600 mt-1">Select users you follow to be additional authors</p>
        </div>

        <div id="selected-authors" class="flex flex-wrap gap-2 mt-4">
          <!-- Chips dynamically added here -->
        </div>

        <div class="space-y-4">
          <div class="flex items-center mb-2">
            <label class="block text-2xl font-medium">Content<span class="text-red-500">*</span></label>
            <div class="px-2 relative inline-block">
              <svg
                class="help-trigger w-5 h-5 text-gray-500 hover:text-gray-700 cursor-help transition-colors duration-200"
                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                  <div class="absolute h-2 w-2 bg-gray-900 rotate-45 -bottom-1 left-1/2 transform -translate-x-1/2">
                  </div>
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
              <textarea name="content" placeholder="Write your post content here..." required id="editor-create-input"
                class="min-h-32 w-full p-4 bg-inherit focus:outline-none resize-y font-mono text-sm border border-1 rounded-lg border-[#3C3D37]"></textarea>

              <!-- Markdown Preview -->
              <div id="editor-create-preview"
                class="min-h-32 resize-y hidden p-4 break-words max-h-80 h-full font-vollkorn overflow-y-auto prose-a:text-[#4793AF]/[.80] hover:prose-a:text-[#4793AF]/[1] border border-1 rounded-lg border-[#3C3D37] prose-blockquote:my-2 prose-code:my-4 prose-headings:my-4 prose-hr:my-4">
              </div>
            </div>
          </div>

          {{-- <div
            class="border-2 border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30">
            <div class="flex gap-4 px-6 py-3 border-b border-black/10">
              <input type="radio" name="editor-toggle" id="write-toggle" class="hidden peer/write" checked>
              <label for="write-toggle" class="underline-effect cursor-pointer peer-checked/write:font-bold">
                write
              </label>

              <input type="radio" name="editor-toggle" id="preview-toggle" class="hidden peer/preview">
              <label for="preview-toggle" class="underline-effect cursor-pointer peer-checked/preview:font-bold">
                preview
              </label>
            </div>

            <!-- Editor Content -->
            <div class="relative">
              <textarea id="editor-content" name="content" class="w-full h-64 p-6 text-lg font-vollkorn focus:outline-none resize-none
                                             peer-checked/preview:hidden" placeholder="Write your post content here..."
                required></textarea>
              <div id="preview-content" class="hidden w-full h-64 p-6 text-lg font-vollkorn overflow-y-auto
                                        prose prose-a:text-[#4793AF]/[.80] hover:prose-a:text-[#4793AF]/[1] 
                                        prose-blockquote:border-l-4 prose-blockquote:border-[#4793AF]/[.50] 
                                        prose-code:bg-white/[.50] prose-code:p-1 prose-code:rounded 
                                        prose-code:text-[#4793AF]">
              </div>
            </div>
          </div> --}}
        </div>
      </fieldset>

      <div class="flex justify-end gap-4">
        <a href="/home"
          class="group relative overflow-hidden inline-flex items-center gap-4 px-8 py-4 bg-gray-300 text-black text-xl font-medium transition-transform duration-300 hover:-translate-y-1">
          <span class="relative z-10">Cancel</span>
        </a>
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
            class="absolute inset-0 bg-pastelGreen transform translate-y-full transition-transform duration-300 group-hover:translate-y-0">
          </div>
        </button>
      </div>
    </form>
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
</style>

@endsection