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

<div class="p-4">
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
~<div class="p-4 ">
  <div class="w-[50%] mx-auto">
    <x-chartjs-component :chart="$comboPosts" />
  </div>
</div>

<div class="p-4">
  <h1 class="text-xl font-bold mb-2">users</h1>
  <table
    class="w-full bg-white border-2 border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30 p-6">
    <thead class="bg-gray-100">
      <tr>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Handle</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">admin</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Suspended</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delete User</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
      <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-4 py-4 whitespace-nowrap">1</td>
        <td class="px-4 py-4">
          <a class="flex items-center" href="">
            <img src="https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png"
              class="max-w-full rounded-3xl min-w-[32px] mr-3  w-[32px]">
            <span class="break-all">@anonymous</span>
          </a>
        </td>
        <td class="px-4 py-4 break-all">
          anonymous
        </td>
        <td class="px-4 py-4">
          <input checked id="red-checkbox" type="checkbox" class="w-4 h-4 accent-blue-500">
        </td>
        <td class="px-4 py-4">
          <input checked id="red-checkbox" type="checkbox" class="w-4 h-4 accent-red-500">
        </td>
        <td class="px-4 py-4">
          <button name="delete-button"
            class="px-2 py-1 rounded-md bg-red-500/[.80] hover:bg-red-500 text-white font-bold">
            delete
          </button>
        </td>
      </tr>
    </tbody>
  </table>

</div>

<div class="p-4">
  <h1 class="text-xl font-bold mb-2">hubs</h1>

  <table
    class="w-full bg-white border-2 border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30 p-6">
    <thead class="bg-gray-100">
      <tr>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">privacy</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delete</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
      <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-4 py-4 whitespace-nowrap">1</td>
        <td class="px-4 py-4">
          <a class="flex items-center" href="">
            <img src="https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png"
              class="max-w-full rounded-3xl min-w-[32px] mr-3  w-[32px]">
            <span
              class="truncate max-w-32 hover:max-w-full transition-all">h/AnimeUwUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA</span>
          </a>
        </td>
        <td class="px-4 py-4 break-all max-w-prose">
          Lorem, ipsum dolor sit amet consectetur adipisicing elit. Est alias, soluta eum dolore tempora ea minus
          commodi deleniti expedita praesentium unde, culpa officiis, iste magni nihil ex. Placeat, alias quaerat!
        </td>
        <td class="px-4 py-4">
          <span
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-full {{ 0===0 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
            @if(0 === 0)
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
          </span>
        </td>

        <td class="px-4 py-4">
          <button name="delete-button"
            class="px-2 py-1 rounded-md bg-red-500/[.80] hover:bg-red-500 text-white font-bold">
            delete
          </button>
        </td>
      </tr>
    </tbody>
  </table>
</div>

<div class="p-4">
  <h1 class="text-xl font-bold mb-2">news</h1>

  <table
    class="w-full bg-white border-2 border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30 p-6">
    <thead class="bg-gray-100">
      <tr>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">News URL</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Content</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">upvotes/downvotes
        </th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">threads</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">delete</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
      <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-4 py-4 whitespace-nowrap">1</td>
        <td class="px-4 py-4">
          <a class="prose"
            href="https://www.publico.pt/2024/10/01/politica/noticia/oe2025-montenegro-promete-proposta-irrecusavel-ps-2106112?ref=hp&cx=manchete_2_destaques_0">
            https://www.publico.pt/...
          </a>
        </td>
        <td
          class="px-4 py-4 break-all max-w-[16rem] overflow-hidden whitespace-nowrap text-ellipsis hover:overflow-auto hover:whitespace-normal hover:text-wrap hover:max-w-prose transition-all">
          Lorem, ipsum dolor sit amet consectetur adipisicing elit. Est alias, soluta eum dolore tempora ea minus
          commodi deleniti expedita praesentium unde, culpa officiis, iste magni nihil ex. Placeat, alias quaerat!
        </td>
        <td
          class="px-4 py-4 break-all max-w-[24rem] overflow-hidden whitespace-nowrap text-ellipsis hover:overflow-auto hover:whitespace-normal hover:text-wrap hover:max-w-prose transition-all">
          Lorem, ipsum dolor sit amet consectetur adipisicing elit. Est alias, soluta eum dolore tempora ea minus
          commodi deleniti expedita praesentium unde, culpa officiis, iste magni nihil ex. Placeat, alias quaerat!
        </td>
        <td class="px-4 py-4">
          <strong class="text-pastelBlue">123</strong> <strong>/</strong> <strong class="text-pastelRed">214</strong>
        </td>
        <td class="px-4 py-4">
          123
        </td>
        <td class="px-4 py-4">
          <button name="delete-button"
            class="px-2 py-1 rounded-md bg-red-500/[.80] hover:bg-red-500 text-white font-bold">
            Delete
          </button>
        </td>
      </tr>
    </tbody>
  </table>
</div>

<div class="p-4">
  <h1 class="text-xl font-bold mb-2">topics</h1>
  <table
    class="w-full bg-white border-2 border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30 p-6">
    <thead class="bg-gray-100">
      <tr>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Content</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">upvotes/downvotes
        </th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">threads</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">status</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">delete</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
      <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-4 py-4 whitespace-nowrap">1</td>

        <td
          class="px-4 py-4 break-all max-w-[16rem] overflow-hidden whitespace-nowrap text-ellipsis hover:overflow-auto hover:whitespace-normal hover:text-wrap hover:max-w-prose transition-all">
          Lorem, ipsum dolor sit amet consectetur adipisicing elit. Est alias, soluta eum dolore tempora ea minus
          commodi deleniti expedita praesentium unde, culpa officiis, iste magni nihil ex. Placeat, alias quaerat!
        </td>
        <td
          class="px-4 py-4 break-all max-w-[24rem] overflow-hidden whitespace-nowrap text-ellipsis hover:overflow-auto hover:whitespace-normal hover:text-wrap hover:max-w-prose transition-all">
          Lorem, ipsum dolor sit amet consectetur adipisicing elit. Est alias, soluta eum dolore tempora ea minus
          commodi deleniti expedita praesentium unde, culpa officiis, iste magni nihil ex. Placeat, alias quaerat!
        </td>
        <td class="px-4 py-4">
          <strong class="text-pastelBlue">123</strong> <strong>/</strong> <strong class="text-pastelRed">214</strong>
        </td>
        <td class="px-4 py-4">
          123
        </td>
        <td class="px-4 py-4">
          {{-- <span
            class="{{ 0===0 ? 'text-green-600 bg-green-100' : 'text-red-600 bg-red-100' }} text-sm border rounded-full px-3 py-1 font-bold">
            {{ 0===0 ? 'Approved' : 'Rejected' }}
          </span> --}}
          <span
            class=" bg-orange-200 text-orange-500 text-sm border rounded-full px-3 py-1 font-bold whitespace-nowrap">Waiting
            Approval</span>
        </td>
        <td class="px-4 py-4">
          <button name="delete-button"
            class="px-2 py-1 rounded-md bg-red-500/[.80] hover:bg-red-500 text-white font-bold">
            Delete
          </button>
        </td>
      </tr>
    </tbody>
  </table>
</div>

@endsection