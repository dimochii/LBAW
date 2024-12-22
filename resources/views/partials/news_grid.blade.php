{{--
divs are in order of prio -> descendent
--}}

@php
$colors = ['bg-pastelGreen',
'bg-pastelYellow',
'bg-pastelGreen',
'bg-pastelRed',
'bg-pastelBlue'];

$randomKey = array_rand($colors);
@endphp

<div class="flex flex-col xl:grid grid-cols-3 border-b-2 border-black divide-y-2 divide-black xl:divide-y-0">
  <div class="col-span-3 col-start-1 row-start-2 xl:border-y-2 border-black {{ $colors[$randomKey] }}">
    @include('partials.post', ['news' => true, 'post' => $posts[0]->news, 'img' => true, 'img_left' => false, 'item' =>
    $posts[0]])
  </div>
  <div class="col-span-2 col-start-1 row-start-1 xl:border-r-2 border-black">
    @include('partials.post', ['news' => true, 'post' => $posts[1]->news, 'img' => true, 'img_left' => true, 'item' =>
    $posts[1]])
  </div>
  <div class="col-start-3 row-start-1">
    @include('partials.post', ['news' => true, 'post' => $posts[2]->news, 'item' => $posts[2]])
  </div>
  <div class="row-start-3 xl:border-r-2 border-black">
    @include('partials.post', ['news' => true, 'post' => $posts[3]->news, 'item' => $posts[3]])
  </div>
  <div class="row-start-3 xl:border-r-2 border-black">
    @include('partials.post', ['news' => true, 'post' => $posts[4]->news, 'item' => $posts[4]])
  </div>
  <div class="row-start-3">
    @include('partials.post', ['news' => true, 'post' => $posts[5]->news, 'item' => $posts[5]])
  </div>
</div>


