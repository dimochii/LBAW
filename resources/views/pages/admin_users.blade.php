@extends('layouts.admin')

@section('content')

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


    <div class="min-h-12 flex items-center pl-2 md:pl-4 relative border-b-2 border-black bg-pastelBlue peer">
      <svg class="w-5 h-5 text-[#F4F2ED]/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
      </svg>
      <input id="search-input" type="text" placeholder="search"
        class="w-full bg-transparent border-none text-[#F4F2ED] placeholder-[#F4F2ED] px-2 md:px-3 py-2 focus:outline-none ">
      <input type="checkbox" name="filters" id="filters" class="hidden">

      <label for="filters" class="cursor-pointer">
        <svg class="w-5 h-5 mr-4 fill-[#F4F2ED]/80" xmlns="http://www.w3.org/2000/svg">
          <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
          <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
          <g id="SVGRepo_iconCarrier">
            <path d="M0 3H16V1H0V3Z"></path>
            <path d="M2 7H14V5H2V7Z"></path>
            <path d="M4 11H12V9H4V11Z"></path>
            <path d="M10 15H6V13H10V15Z"></path>
          </g>
        </svg>
      </label>
    </div>
    <div id="filters-section" class=" peer-has-[:checked]:flex hidden font-normal items-center">
      <select id="suspension-filter" class="bg-transparent px-4 focus:outline-none py-2 border-r-2 border-black appearance-none">
        <option value="" class="font-light">Suspension Status</option>
        <option value="Suspend" class="font-bold">Active</option>
        <option value="Unsuspend" class="font-bold">Suspended</option>
      </select>
      <select id="admin-filter" class="bg-transparent px-4 focus:outline-none py-2 border-r-2 border-black appearance-none">
        <option value="" class="font-light">Admin Status</option>
        <option value="admin" class="font-bold">Admin</option>
        <option value="normal" class="font-bold">Normal</option>
      </select>

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
            <img src="{{ asset( $user->image->path ?? 'images/groupdefault.jpg') }}"
              onerror="this.onerror=null;this.src='https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png';"
              class="max-w-full rounded-full size-9 mr-3 object-cover">
            <span class="break-all" data-sort>{{ '@' . $user->username }}</span>
          </a>
        </td>
        <td class="px-4 py-4 break-all" data-sort>
          {{ $user->name }}
        </td>
        <td class="px-4 py-4" data-admin>
          <input id="admin-checkbox-{{ $user->id }}" type="checkbox" class="w-4 h-4 accent-blue-500"
            @if($user->is_admin) checked @endif
          onclick="toggleAdmin({{ $user->id }}, this.checked)"
          >
        </td>
        <td data-suspension>
          @if($user->is_suspended)
          <button type="button"
            class="unsuspend-btn font-bold px-2 py-1 rounded-md bg-green-500/[.80] hover:bg-green-500 text-white"
            data-user-id="{{$user->id}}" onclick="unsuspendUser({{ $user->id }})">
            Unsuspend
          </button>
          <button type="button"
            class="suspend-btn hidden px-2 py-1 rounded-md bg-red-500/[.80] hover:bg-red-500 text-white font-bold"
            data-user-id="{{$user->id}}" onclick="openSuspendModal({{ $user->id }})">
            Suspend
          </button>
          @else
          <button type="button"
            class="suspend-btn font-bold px-2 py-1 rounded-md bg-red-500/[.80] hover:bg-red-500 text-white"
            data-user-id="{{$user->id}}" onclick="openSuspendModal({{ $user->id }})">
            Suspend
          </button>
          <button type="button"
            class="unsuspend-btn hidden px-2 py-1 rounded-md bg-green-500/[.80] hover:bg-green-500 text-white font-bold"
            data-user-id="{{$user->id}}" onclick="unsuspendUser({{ $user->id }})">
            Unsuspend
          </button>
          @endif
        </td>



        <div id="suspend-modal" class="hidden fixed inset-0 flex justify-center items-center bg-gray-500 bg-opacity-50">
          <div class="bg-white p-6 rounded shadow-lg w-96">
            <h3 class="text-xl font-bold mb-4">Suspend User</h3>
            <form id="suspend-form">
              @csrf
              <input type="hidden" name="authenticated_user_id" id="authenticated_user_id">

              <div class="mb-4">
                <label for="reason" class="block font-medium text-gray-700 mb-1">Reason</label>
                <input type="text" name="reason" id="reason" class="w-full border-gray-300 rounded p-2 border-2"
                  required>
              </div>

              <div class="mb-6">
                <label for="duration" class="block font-medium text-gray-700 mb-1">Duration (in days)</label>
                <input type="number" name="duration" id="duration" class="w-full border-gray-300 rounded p-2 border-2"
                  required>
              </div>

              <div class="flex justify-end gap-4">
                <button type="button"
                  class="px-2 py-1 rounded-md bg-gray-300 hover:bg-gray-400/[.80] text-white font-bold"
                  onclick="closeSuspendModal()">Cancel</button>
                <button type="submit"
                  class="px-2 py-1 rounded-md bg-red-500/[.80] hover:bg-red-500 text-white font-bold">Suspend</button>
              </div>
            </form>
          </div>
        </div>

</div>

<td class="px-4 py-4">
  <form action="{{ route('admin.delete', ['id' => $user->id]) }}" method="POST"
    onsubmit="return confirm('Are you sure you want to delete this account?')">
    @csrf
    @method('DELETE')
    <button type="submit" class="px-2 py-1 rounded-md bg-red-500/[.80] hover:bg-red-500 text-white font-bold">
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
