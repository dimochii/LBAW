@extends('layouts.admin')

@section('content')
{{-- <div class="flex-1 bg-pastelRed h-12 flex items-center pl-2 md:pl-4 relative">
  <svg class="w-5 h-5 text-[#F4F2ED]/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
  </svg>
  <input id="search-input" type="text" placeholder="search"
    class="w-full bg-transparent border-none text-[#F4F2ED] placeholder-[#F4F2ED] px-2 md:px-3 py-2 focus:outline-none ">
</div> --}}

{{-- <div class="p-4">
  <div class="w-[50%] mx-auto">
    <x-chartjs-component :chart="$chartHubs" />
  </div>
</div>

<div class="p-4 ">
  <div class="w-[50%] mx-auto">
    <x-chartjs-component :chart="$chartUsers" />
  </div>
</div>

<div class="p-4 ">
  <div class="w-[50%] mx-auto">
    <x-chartjs-component :chart="$postsPDay" />
  </div>
</div>
<div class="p-4 ">
  <div class="w-[50%] mx-auto">
    <x-chartjs-component :chart="$comboPosts" />
  </div>
</div>
<div class="p-4 ">
  <div class="w-[50%] mx-auto">
    <x-chartjs-component :chart="$chartReports" />
  </div>
</div> --}}

<div class="flex flex-row p-4">
  <h1 class=" tracking-tight font-medium text-5xl">posts <span class="text-2xl tracking-normal opacity-60">manage</span>
  </h1>
  <span class="ml-auto text-sm tracking-normal opacity-60 mt-auto">{{$startDate->toFormattedDateString()}} ->
    {{$endDate->toFormattedDateString()}}</span>
</div>
<div class="grid grid-cols-4 border-b-2 border-black ">
  <div class="p-4 col-span-2 border-b-2 border-black border-r-2 flex items-center justify-center">
    <div class="w-[90%]">
      <x-chartjs-component :chart="$chartUsers" />
    </div>
  </div>
  <div class="p-4 col-span-2 border-b-2 border-black flex items-center justify-center">
    <div class="w-[90%]">
      <x-chartjs-component :chart="$comboPosts" />
    </div>
  </div>

  {{--
  'newPosts',
  'activeHubCount',
  'activeUserCount',
  'pendingReports',
  'userCount',
  'hubCount',
  'postsCount'
  --}}

  <div class="px-4 py-4  border-black border-r-2 flex flex-col">
    <div class="text-2xl text-[#3C3D37]/[.8] mb-auto ">new posts</div>
    <div class="text-6xl font-bold tracking-tighter text-[#3C3D37] mb-auto">{{ $newPosts }}</div>
    <div class="text-lg tracking-tight text-[#3C3D37]/[.8] mb-auto">{{ $postsCount }} total posts </div>
  </div>
  <div class="px-4 py-4  border-black border-r-2 flex flex-col">
    <div class="text-2xl text-[#3C3D37]/[.8] mb-auto">active users</div>
    <div class="text-6xl font-bold tracking-tighter text-[#3C3D37] mb-auto">{{ $activeUserCount }}</div>
    <div class="text-lg tracking-tight text-[#3C3D37]/[.8] mb-auto">{{ $userCount }} total users </div>
  </div>
  <div class="px-4 py-4  border-black border-r-2 flex flex-col">
    <div class="text-2xl text-[#3C3D37]/[.8] mb-auto">active hubs</div>
    <div class="text-6xl font-bold tracking-tighter text-[#3C3D37] mb-auto"> {{ $activeHubCount }} </div>
    <div class="text-lg tracking-tight text-[#3C3D37]/[.8] mb-auto">{{ $hubCount }} total hubs </div>

  </div>
  <div class="px-4 py-4   flex flex-col">
    <div class="text-2xl text-[#3C3D37]/[.8] mb-auto">pending reports</div>
    <div class="text-6xl font-bold tracking-tighter text-[#3C3D37] mb-auto"> {{ $pendingReports }} </div>
  </div>

  <div class="flex flex-col *:grow col-span-2 border-black border-r-2 border-t-2">
    <header
      class="pl-4 pr-2 py-4 w-full font-bold text-4xl bg-pastelBlue flex flex-row border-b-2 border-black max-h-20 min-h-20">
      top users <a href=" {{ route('admin.users')}} "
        class="ml-auto mt-auto text-sm text-[#3C3D37]/[.6] font-light">manage</a>
    </header>
    @foreach ($topUsers as $user)
    <a class="flex items-center gap-4 p-2 pl-8 group hover:bg-[#3C3D37] hover:text-[#F4F2ED] transition-colors"
      href="{{ route('user.profile', $user->id) }}">
      <img src="{{ asset('images/user' . $user->image_id . '.jpg') }}" class="rounded-full h-12 w-12">
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
      <img src="{{ asset('images/hub' . $hub->image_id . '.jpg') }}" class="rounded-full h-12 w-12">
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
      <img src="{{ asset('images/user' . $user->user->image_id . '.jpg') }}" class="rounded-full h-12 w-12">
      <div class="break-all flex flex-col" data-sort>
        <span class="font-medium text-xl">{{ $user->user->name }}</span>
        <span class="text-[#3C3D37]/[.6] group-hover:text-[#F4F2ED]/[.6]">{{ '@' . $user->user->username }}</span>
      </div>
      <span class="ml-auto mr-8">{{$user->report_count}} reports</span>
    </a>
    @endforeach
  </div>

</div>

{{-- <div>
  <div class="flex flex-col *:p-2">
    <header class="font-bold text-4xl bg-pastelRed text-[#F4F2ED]">top hubs</header>
    @foreach ($topHubs as $hub)
    <div class="grid grid-cols-2 items-center">
      <a class="flex items-center gap-2" href="{{ route('communities.show', $hub->id) }}">
        <img src="{{ asset('images/hub' . $hub->image_id . '.jpg') }}" class="rounded-full h-10 w-10">
        <span class="break-all" data-sort>{{ 'h/' . $hub->name }}</span>
      </a>
      <div>
        {{ $user->followers_count }}
      </div>
    </div>
    @endforeach
  </div>
</div> --}}

@endsection

<script>
  function toggleSuspend(userId, isChecked) {
        const action = isChecked ? 'suspend' : 'unsuspend';
        const confirmationMessage = isChecked
            ? 'Are you sure you want to suspend this user?'
            : 'Are you sure you want to unsuspend this user?';

        if (confirm(confirmationMessage)) {
            fetch(`/users/${userId}/${action}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({})
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to update user status.');
                }
                return response.json();
            })
            .then(data => {
                alert(data.message);
            })
            .catch(error => {
                alert(error.message);
                // Revert checkbox state if the request fails
                document.getElementById(`suspend-checkbox-${userId}`).checked = !isChecked;
            });
        } else {
            // Revert checkbox state if the user cancels the action
            document.getElementById(`suspend-checkbox-${userId}`).checked = !isChecked;
        }
    }

    function toggleAdmin(userId, isChecked) {
        const action = isChecked ? 'make_admin' : 'remove_admin';
        const confirmationMessage = isChecked
            ? 'Are you sure you want to grant this user admin privileges?'
            : 'Are you sure you want to revoke this user\'s admin privileges?';

        if (confirm(confirmationMessage)) {
            fetch(`/users/${userId}/${action}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({})
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to update admin status.');
                }
                return response.json();
            })
            .then(data => {
                alert(data.message);
            })
            .catch(error => {
                alert(error.message);
                // Revert checkbox state if the request fails
                document.getElementById(`admin-checkbox-${userId}`).checked = !isChecked;
            });
        } else {
            // Revert checkbox state if the user cancels the action
            document.getElementById(`admin-checkbox-${userId}`).checked = !isChecked;
        }
    }
</script>