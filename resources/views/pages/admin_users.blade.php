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

<div class="flex flex-col md:flex-row md:divide-x-2 md:divide-y-0 divide-y-2 divide-black">
  <div class="flex flex-col w-[50%]  divide-y-2 divide-black ">
    <div class="flex flex-row p-4 h-full">
      <h1 class=" tracking-tight font-medium text-5xl">users <span
          class="text-2xl tracking-normal opacity-60">manage</span>
      </h1>
      <span class="ml-auto text-sm tracking-normal opacity-60 mt-auto">{{$startDate}} -> {{$endDate}}</span>
    </div>
    <div class="grid grid-cols-3">
      <div class="px-4 py-4 bg-pastelRed border-black border-r-2 flex flex-col">
        <div class="text-2xl text-[#F4F2ED]/[.8] mb-auto">active users</div>
        <div class="text-6xl font-bold tracking-tighter text-[#F4F2ED] mb-auto">{{ $activeUserCount }}</div>


      </div>
      <div class="px-4 py-4 bg-pastelYellow border-black border-r-2 flex flex-col">
        <div class="text-2xl text-[#3C3D37]/[.8] mb-auto">suspended users</div>
        <div class="text-6xl font-bold tracking-tighter text-[#3C3D37] mb-auto">{{ $suspendedUserCount }}</div>


      </div>
      <div class="px-4 py-4 bg-pastelGreen  flex flex-col">
        <div class="text-2xl text-[#F4F2ED]/[.8] mb-auto">total users</div>
        <div class="text-6xl font-bold tracking-tighter text-[#F4F2ED]"> {{ $users->count() }} </div>
        <div class="text-lg tracking-tight text-[#F4F2ED]/[.8] mb-auto">{{ $newUserCount }} new users </div>
      </div>

    </div>


    <div class="min-h-12 flex items-center pl-2 md:pl-4 relative border-b-2 border-black bg-pastelBlue">
      <svg class="w-5 h-5 text-[#F4F2ED]/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
      </svg>
      <input id="search-input" type="text" placeholder="search"
        class="w-full bg-transparent border-none text-[#F4F2ED] placeholder-[#F4F2ED] px-2 md:px-3 py-2 focus:outline-none ">
    </div>



  </div>

  <div class="w-[50%]">
    <div class="w-[600px] mx-auto p-4">
      <x-chartjs-component :chart="$chartUsers" />
    </div>

  </div>
</div>





<div class="">
  <table
    class="min-w-[1000px] w-full bg-white border-2 border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30 p-6">
    <thead class="bg-gray-100">
      <tr>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200"
          data-type="number">ID</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200"
          data-type="string">Handle</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200"
          data-type="string">Name</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200">
          admin</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200">
          Suspended</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200">
          Delete User</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
      @foreach($users as $user)
      <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-4 py-4 whitespace-nowrap" data-sort>{{$user->id}}</td>
        <td class="px-4 py-4">
          <a class="flex items-center" href="{{ route('user.profile', $user->id) }}">
            <img src="https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png"
              class="max-w-full rounded-3xl min-w-[32px] mr-3  w-[32px]">
            <span class="break-all" data-sort>{{ '@' . $user->username }}</span>
          </a>
        </td>
        <td class="px-4 py-4 break-all" data-sort>
          {{ $user->name }}
        </td>
        <td class="px-4 py-4">
          <input id="admin-checkbox-{{ $user->id }}" type="checkbox" class="w-4 h-4 accent-blue-500"
            @if($user->is_admin) checked @endif
          onclick="toggleAdmin({{ $user->id }}, this.checked)"
          >
        </td>
        <td class="px-4 py-4">
          <input id="suspend-checkbox-{{ $user->id }}" type="checkbox" class="w-4 h-4 accent-red-500"
            @if($user->is_suspended) checked @endif
          onclick="toggleSuspend({{ $user->id }}, this.checked)"
          >
        </td>
        <td class="px-4 py-4">
          <form action="{{ route('admin.delete', ['id' => $user->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this account?')">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="px-2 py-1 rounded-md bg-red-500/[.80] hover:bg-red-500 text-white font-bold">
                Delete
            </button>
        </form>
        
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>

@endsection