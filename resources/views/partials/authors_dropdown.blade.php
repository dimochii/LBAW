<div class="inline cursor-pointer pb-4 group italic"> contributors
  <div class="transition-all absolute right-0 top-6 invisible group-hover:visible opacity-0 group-hover:opacity-100 bg-[#F4F2ED] text-[#3C3D37] border border-[#3C3D37] rounded-md shadow-lg p-2">
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