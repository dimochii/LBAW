<div class="inline cursor-pointer group italic underline-effect-light"> contributors
  <div class="transition-all absolute right-0 bottom-6 invisible group-hover:visible hover:visible opacity-0 hover:opacity-100 group-hover:opacity-100 bg-[#F4F2ED] text-[#3C3D37] border border-[#3C3D37] rounded-md shadow-lg py-4 px-2 min-w-28">
    <ul>
      @foreach ($post->post->authors as $author)
      <li class="mb-1">
        <a href="{{ route('user.profile', $author->id) }}" class="underline-effect">
          {{ '@' . $author->username }}
        </a>
      </li>
      @endforeach
    </ul>
  </div>
</div>