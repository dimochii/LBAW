@section('content')


<div class="flex flex-row p-4">
  <h1 class=" tracking-tight font-medium text-5xl">posts <span class="text-2xl tracking-normal opacity-60">manage</span>
  </h1>
  <span class="ml-auto text-sm tracking-normal opacity-60 mt-auto">{{$startDate->toFormattedDateString()}} ->
    {{$endDate->toFormattedDateString()}}</span>
</div>

<div>

</div>


@endsection
