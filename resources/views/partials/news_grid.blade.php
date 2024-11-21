{{-- 
  divs are in order of prio -> descendent
--}}

<div class="flex flex-col xl:grid grid-cols-3 border-b-2 border-black divide-y-2 divide-black">
  <div class="col-span-3 col-start-1 row-start-2 xl:border-t-2 border-black">
    @include('partials.post', ['news' => true, 'post' => $posts[1]])
  </div>
  <div class="col-span-2 col-start-1 row-start-1 xl:border-r-2 border-black">
    @include('partials.post', ['news' => true, 'post' => $posts[2]])
  </div>
  <div class="col-start-3 row-start-1">
    @include('partials.post', ['news' => true, 'post' => $posts[3]])
  </div>
  <div class="row-start-3 xl:border-r-2 border-black">
    @include('partials.post', ['news' => true, 'post' => $posts[4]])
  </div>
  <div class="row-start-3 xl:border-r-2 border-black">
    @include('partials.post', ['news' => true, 'post' => $posts[5]])
  </div>
  <div class="row-start-3">
    @include('partials.post', ['news' => true, 'post' => $posts[6]])
  </div>
</div>