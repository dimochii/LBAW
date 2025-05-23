@extends('layouts.admin')

@section('content')

<div class="flex flex-row p-4">
  <h1 class="font-medium text-5xl tracking-tighter">whatsUP <span class="text-2xl tracking-normal opacity-60">manage</span>
  </h1>
  <span class="ml-auto text-sm tracking-normal opacity-60 mt-auto">{{$startDate->toFormattedDateString()}} ->
    {{$endDate->toFormattedDateString()}}</span>
</div>
<div class="grid grid-cols-4 border-b-2 border-black ">
  <div class="p-4 col-span-2 border-b-2 border-black border-r-2 flex items-center justify-center">
    <div class="w-[90%] mx-auto">
      <x-chartjs-component :chart="$chartUsers" />
    </div>
  </div>
  <div class="p-4 col-span-2 border-b-2 border-black flex items-center justify-center">
    <div class="w-[90%] mx-auto">
      <x-chartjs-component :chart="$comboPosts" />
    </div>
  </div>

  <div class="px-4 py-4  border-black border-r-2 flex flex-col hover:bg-[#3C3D37] hover:text-[#F4F2ED] transition-all text-[#3C3D37]">
    <div class="text-2xl opacity-80 mb-auto ">new posts</div>
    <div class="text-6xl font-bold tracking-tighter  mb-auto">{{ $newPosts }}</div>
    <div class="text-lg opacity-80 tracking-tight mb-auto">{{ $postsCount }} total posts </div>
  </div>
  <div class="px-4 py-4  border-black border-r-2 flex flex-col hover:bg-[#3C3D37] hover:text-[#F4F2ED] transition-all text-[#3C3D37]">
    <div class="text-2xl opacity-80 mb-auto">active users</div>
    <div class="text-6xl font-bold tracking-tighter mb-auto">{{ $activeUserCount }}</div>
    <div class="text-lg tracking-tight opacity-80 mb-auto">{{ $userCount }} total users </div>
  </div>
  <div class="px-4 py-4  border-black border-r-2 flex flex-col hover:bg-[#3C3D37] hover:text-[#F4F2ED] transition-all text-[#3C3D37]">
    <div class="text-2xl opacity-80 mb-auto">active hubs</div>
    <div class="text-6xl font-bold tracking-tighter mb-auto"> {{ $activeHubCount }} </div>
    <div class="text-lg tracking-tight opacity-80 mb-auto">{{ $hubCount }} total hubs </div>

  </div>
  <div class="px-4 py-4   flex flex-col hover:bg-[#3C3D37] hover:text-[#F4F2ED] transition-all text-[#3C3D37]">
    <div class="text-2xl opacity-80 mb-auto">pending reports</div>
    <div class="text-6xl font-bold tracking-tighter mb-auto"> {{ $pendingReports }} </div>
  </div>

  <div class="flex flex-col *:grow col-span-2 border-black border-r-2 border-t-2">
    <header
      class="pl-4 pr-2 py-4 w-full font-bold text-4xl bg-pastelBlue flex flex-row border-b-2 border-black max-h-20 min-h-20">
      top users <a href=" {{ route('admin.users')}} "
        class="ml-auto mt-auto text-sm text-[#3C3D37]/[.8] font-light">manage</a>
    </header>
    @foreach ($topUsers as $user)
    <a class="flex items-center gap-4 p-2 pl-8 group hover:bg-[#3C3D37] hover:text-[#F4F2ED] transition-colors"
      href="{{ route('user.profile', $user->id) }}">
      <img src="{{ asset(isset($user->image->path) ? $user->image->path : 'images/default.jpg') }}" class="rounded-full h-12 w-12">
      <div class="break-all flex flex-col" data-sort>
        <span class="font-medium text-xl">{{ $user->name }}</span>
        <span class="text-[#3C3D37]/[.6] group-hover:text-[#F4F2ED]/[.6]">{{ '@' . $user->username }}</span>
      </div>
    </a>
    @endforeach
  </div>

  <div class="flex flex-col *:grow col-span-2 border-t-2 border-black">
    <header
      class="pl-4 pr-2 py-4 w-full font-bold text-4xl bg-pastelRed flex flex-row border-b-2 border-black max-h-20 min-h-20">
      top hubs <a href=" {{ route('admin.hubs')}} "
        class="ml-auto mt-auto text-sm text-[#3C3D37]/[.8] font-light">manage</a>
    </header>
    @foreach ($topHubs as $hub)

    <a class="flex items-center gap-4 p-2 pl-8 group hover:bg-[#3C3D37] hover:text-[#F4F2ED] transition-colors"
      href="{{ route('communities.show', $hub->id) }}">
      <img src="{{ asset($hub->image->path ?? 'images/groupdefault.jpg') }}" class="rounded-full h-12 w-12">
      <div class="break-all flex flex-col" data-sort>
        <span class="font-medium text-xl">{{ 'h/' . $hub->name }}</span>
        <span class="text-[#3C3D37]/[.6] group-hover:text-[#F4F2ED]/[.6]">{{ $hub->description }}</span>
      </div>
    </a>
    @endforeach
  </div>

  <div class="p-4  border-t-2 border-black border-r-2 flex flex-col gap-4 col-span-2">
    <span class="text-2xl text-[#3C3D37]/[.8]">suspended users</span>
    <div class="w-[50%] mx-auto">
      <x-chartjs-component :chart="$pieSuspended" />
    </div>
    
  </div>

  <div class="col-span-2 border-t-2 border-black">
    <header class="pl-4 pr-2 py-4 w-full font-bold text-4xl bg-pastelGreen flex flex-row border-b-2 border-black ">
      most reported users <a href=" {{ route('admin.reports')}} "
        class="ml-auto mt-auto text-sm text-[#3C3D37]/[.8] font-light">manage</a>
    </header>
    @foreach ($mostReportedUsers as $user)    
    <a class="flex items-center gap-4 p-2 pl-8 group hover:bg-[#3C3D37] hover:text-[#F4F2ED] transition-colors"
      href="{{ route('user.profile', $user->user->id) }}">
      <img src="{{ asset($user->user->image->path ?? 'images/default.jpg') }}" class="rounded-full h-12 w-12">
      <div class="break-all flex flex-col" data-sort>
        <span class="font-medium text-xl">{{ $user->user->name }}</span>
        <span class="text-[#3C3D37]/[.6] group-hover:text-[#F4F2ED]/[.6]">{{ '@' . $user->user->username }}</span>
      </div>
      <span class="ml-auto mr-8">{{$user->report_count}} reports</span>
    </a>
    @endforeach
  </div>

</div>

@endsection
