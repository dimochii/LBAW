@extends('layouts.admin')

@section('content')

<div class="flex flex-col md:flex-row md:divide-x-2 md:divide-y-0 divide-y-2 divide-black">
  <div class="flex flex-col w-[50%]  divide-y-2 divide-black ">
    <div class="flex flex-row p-4 h-full">
      <h1 class=" tracking-tight font-medium text-5xl">hubs <span
          class="text-2xl tracking-normal opacity-60">manage</span>
      </h1>
      <span class="ml-auto text-sm tracking-normal opacity-60 mt-auto">{{$startDate}} -> {{$endDate}}</span>
    </div>
    <div class="grid grid-cols-3">
      <div class="px-4 py-4 bg-pastelRed border-black border-r-2 flex flex-col">
        <div class="text-2xl text-[#F4F2ED]/[.8] mb-auto">active hubs</div>
        <div class="text-6xl font-bold tracking-tighter text-[#F4F2ED] mb-auto">{{$activeHubs}}</div>


      </div>
      <div class="px-4 py-4 bg-pastelYellow border-black border-r-2 flex flex-col">
        <div class="text-2xl text-[#3C3D37]/[.8] mb-auto">moderators</div>
        <div class="text-6xl font-bold tracking-tighter text-[#3C3D37] mb-auto">{{$totalMods}}</div>


      </div>
      <div class="px-4 py-4 bg-pastelGreen  flex flex-col">
        <div class="text-2xl text-[#F4F2ED]/[.8] mb-auto">total hubs</div>
        <div class="text-6xl font-bold tracking-tighter text-[#F4F2ED]">{{$totalHubs}}</div>
        <div class="text-lg tracking-tight text-[#F4F2ED]/[.8] mb-auto">{{$newHubs}} new hubs </div>

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
      <select id="privacy-filter"
        class="bg-transparent px-4 focus:outline-none py-2 border-r-2 border-black appearance-none">
        <option value="" class="font-light">Privacy</option>
        <option value="Public" class="font-bold">Public</option>
        <option value="Private" class="font-bold">Private</option>
      </select>
    </div>




  </div>

  <div class="w-[50%]">
    <div class="w-[600px] mx-auto p-4">
      <x-chartjs-component :chart="$chartHubs" />
    </div>

  </div>
</div>

<div class="">
  <table
    class="w-full bg-white border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30 p-6">
    <thead class="bg-gray-100">
      <tr>
        <th
          class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200 cursor-pointer"
          data-type="number">ID</th>
        <th
          class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200 cursor-pointer"
          data-type="string">Name</th>
        <th
          class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200 cursor-pointer"
          data-type="string">Description</th>
        <th
          class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200 cursor-pointer"
          data-type="number">Readers</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200"
          data-type="string">
          privacy</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200">
          Delete</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
      @foreach($hubs as $hub)
      <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-4 py-4 whitespace-nowrap" data-sort>{{ $hub->id }}</td>
        <td class="px-4 py-4">
          <a class="flex items-center" href="{{ route('communities.show', $hub->id) }}">
            <img src="{{ asset( $hub->image->path ?? 'images/groupdefault.jpg') }}"
              onerror="this.onerror=null;this.src='https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png';"
              class="max-w-full rounded-full size-9 mr-3 object-cover">
            <span class="truncate max-w-32 hover:max-w-full transition-all" data-sort>{{ $hub->name }}</span>
          </a>
        </td>
        <td class="px-4 py-4 break-all max-w-prose" data-sort>
          {{ $hub->description }}
        </td>
        <td class="px-4 py-4 whitespace-nowrap" data-sort>{{ $hub->followers->count() }}</td>

        <td class="px-4 py-4">

          <div data-route="{{$hub->id}}"
            class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-full {{ $hub->privacy ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
            @if($hub->privacy)
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            Private
            @else
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              <path d="M7 11h10"></path>
            </svg>
            Public
            @endif
          </div>
        </td>

        <td class="px-4 py-4">
          <form action="{{ route('admin.community.delete', ['id' => $hub->id]) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit"
              class="px-2 py-1 rounded-md bg-red-500/[.80] hover:bg-red-500 text-white font-bold delete-button-hub"
              data-community-id="{{ $hub->id }}">
              Delete
            </button>
          </form>
        </td>


      </tr>
      @endforeach
    </tbody>
  </table>
</div>

<div id="error-toast"
  class="hidden fixed top-4 left-1/2 transform -translate-x-1/2 bg-red-500 text-white p-4 rounded-md shadow-lg z-50">
  <p id="toast-message" class="text-lg font-bold"></p>
</div>

<div id="success-toast"
  class="hidden fixed top-4 left-1/2 transform -translate-x-1/2 bg-green-500 text-white p-4 rounded-md shadow-lg z-50">
  <p id="toast-message-success" class="text-lg font-bold"></p>
</div>


@endsection