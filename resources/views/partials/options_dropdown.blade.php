<div class="inline cursor-pointer pb-4 group ml-auto z-0">
  <input type="checkbox" class="peer hidden" id="{{$post_id}}-options">
  <label for="{{$post_id}}-options">
    <svg class="ml-auto h-4 w-4 fill-[#3C3D37] group-hover/wrapper:fill-[#F4F2ED] z-0" xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 16 16">
      <path class="cls-1"
        d="M8,6.5A1.5,1.5,0,1,1,6.5,8,1.5,1.5,0,0,1,8,6.5ZM.5,8A1.5,1.5,0,1,0,2,6.5,1.5,1.5,0,0,0,.5,8Zm12,0A1.5,1.5,0,1,0,14,6.5,1.5,1.5,0,0,0,12.5,8Z" />
    </svg>
  </label>

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
</div>