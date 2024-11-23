<div
  class="transition-all absolute right-0 top-6 visible peer-checked:visible opacity-0 peer-checked:opacity-100 bg-[#F4F2ED] text-[#3C3D37] border border-[#3C3D37] rounded-md shadow-lg py-1 min-w-28">
  <ul class="">
    @foreach ($options as $option)
    <li class="py-1 px-4 hover:bg-black/[.10] ">
      <a href="#" class="">
        {{ $option }}
      </a>
    </li>
    @endforeach
  </ul>
</div>