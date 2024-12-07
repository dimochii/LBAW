{{-- 
  divs are in order of prio -> descendent
--}}

<div class="flex flex-col xl:grid grid-cols-3 border-b-2 border-black divide-y-2 divide-black xl:divide-y-0">
  <div class="col-span-3 col-start-1 row-start-2 xl:border-y-2 border-black">
    @include('partials.post', ['news' => false, 'post' => $posts[0]->topic, 'img' => true, 'img_left' => false, 'item' => $posts[0]])
  </div>
  <div class="col-span-2 col-start-1 row-start-1 xl:border-r-2 border-black">
    @include('partials.post', ['news' => false, 'post' => $posts[1]->topic, 'img' => true, 'img_left' => true, 'item' => $posts[1]])
  </div>
  <div class="col-start-3 row-start-1">
    @include('partials.post', ['news' => false, 'post' => $posts[2]->topic, 'item' => $posts[2]])
  </div>
  <div class="row-start-3 xl:border-r-2 border-black">
    @include('partials.post', ['news' => false, 'post' => $posts[3]->topic, 'item' => $posts[3]])
  </div>
  <div class="row-start-3 xl:border-r-2 border-black">
    @include('partials.post', ['news' => false, 'post' => $posts[4]->topic, 'item' => $posts[4]])
  </div>
  <div class="row-start-3">
    @include('partials.post', ['news' => false, 'post' => $posts[5]->topic, 'item' => $posts[5]])
  </div>
</div>