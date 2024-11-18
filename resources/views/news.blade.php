@extends('layouts.app')

@section('content')
<div class="flex flex-col p-12 font-grotesk bg-gray-50">

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
          <footer class="flex gap-x-1 items-center">
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
@endsection