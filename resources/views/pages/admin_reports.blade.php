@extends('layouts.admin')

@section('content')

<div class="flex flex-col md:flex-row md:divide-x-2 md:divide-y-0 divide-y-2 divide-black">
  <div class="flex flex-col w-[50%] divide-y-2 divide-black">
    <div class="flex flex-row p-4 h-full">
      <h1 class="tracking-tight font-medium text-5xl">reports <span
          class="text-2xl tracking-normal opacity-60">manage</span></h1>
      <span class="ml-auto text-sm tracking-normal opacity-60 mt-auto">{{$startDate}} -> {{$endDate}}</span>
    </div>
    <div class="grid grid-cols-3">
      <div class="px-4 py-4 bg-pastelRed border-black border-r-2 flex flex-col">
        <div class="text-2xl text-[#F4F2ED]/[.8] mb-auto">pending reports</div>
        <div class="text-6xl font-bold tracking-tighter text-[#F4F2ED] mb-auto">{{$pendingReports}}</div>
      </div>
      <div class="px-4 py-4 bg-pastelYellow border-black border-r-2 flex flex-col">
        <div class="text-2xl text-[#3C3D37]/[.8] mb-auto">resolved reports</div>
        <div class="text-6xl font-bold tracking-tighter text-[#3C3D37] mb-auto">{{$resolvedReports}}</div>
      </div>
      <div class="px-4 py-4 bg-pastelGreen flex flex-col">
        <div class="text-2xl text-[#F4F2ED]/[.8] mb-auto">total reports</div>
        <div class="text-6xl font-bold tracking-tighter text-[#F4F2ED]">{{$totalReports}}</div>
        <div class="text-lg tracking-tight text-[#F4F2ED]/[.8] mb-auto">{{$newReports}} new reports</div>
      </div>
    </div>
    <div class="min-h-12 flex items-center pl-2 md:pl-4 relative border-b-2 border-black bg-pastelBlue peer">
      <svg class="w-5 h-5 text-[#F4F2ED]/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
      </svg>
      <input id="search-input" type="text" placeholder="search"
        class="w-full bg-transparent border-none text-[#F4F2ED] placeholder-[#F4F2ED] px-2 md:px-3 py-2 focus:outline-none">
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
      <select id="reports-type-filter"
        class="bg-transparent px-4 focus:outline-none py-2 border-r-2 border-black appearance-none">
        <option value="" class="font-light">Type</option>
        <option value="News" class="font-bold">News</option>
        <option value="Topic" class="font-bold">Topic</option>
        <option value="Comment" class="font-bold">Comment</option>
        <option value="User" class="font-bold">User</option>
      </select>
      
      <select id="reports-status-filter"
        class="bg-transparent px-4 focus:outline-none py-2 border-r-2 border-black appearance-none">
        <option value="" class="font-light">Status</option>
        <option value="Open" class="font-bold">Open</option>
        <option value="Closed" class="font-bold">Closed</option>
      </select>

    </div>
  </div>

  <div class="w-[50%]">
    <div class="w-[600px] mx-auto p-4">
      <x-chartjs-component :chart="$chartReports" />
    </div>
  </div>
</div>

<div class="">
  @if($reports->isEmpty())
  <div class="bg-white p-8 rounded-lg shadow-md text-center border border-gray-200">
    <p class="text-gray-600 text-xl font-light">No reports found.</p>
  </div>
  @else
  <table
    class="w-full bg-white border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30 p-6">
    <thead class="bg-gray-100">
      <tr>
        <th
          class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200 cursor-pointer"
          data-type="number">ID</th>
        <th
          class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200 cursor-pointer"
          data-type="string">Reporter</th>
        <th
          class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200 cursor-pointer">
          Reported Type</th>
        <th
          class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200 cursor-pointer">
          Reason</th>
        <th
          class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200 cursor-pointer"
          data-type="string">Status</th>
        <th
          class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200 cursor-pointer">
          Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
      @foreach($reports as $report)
      <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-4 py-4 whitespace-nowrap">{{ $report->id }}</td>
        <td class="px-4 py-4">
          <a class="flex items-center" href="{{ route('user.profile', $report->authenticated_user_id) }}">
            <img src="{{ asset( $report->user->image->path ?? 'images/groupdefault.jpg') }}"
              onerror="this.onerror=null;this.src='https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png';"
              class="max-w-full rounded-full size-9 mr-3 object-cover">
            <span class="break-all" data-sort>{{ '@' . $report->user->username }}</span>
          </a>
        </td>
        <td class="px-4 py-4 capitalize">
          @if($report->report_type->value === 'user_report')
          <a href="{{ route('user.profile', $report->reported_id) }}" class="text-blue-500 hover:underline">
            User
          </a>
          @elseif($report->report_type->value === 'comment_report')
          <a href="{{  route('post.show', $report->reported_id) }}" class="text-blue-500 hover:underline">
            Comment
          </a>
          @elseif($report->report_type->value === 'item_report')
          <a href="{{ route('news.show', $report->reported_id) }}" class="text-blue-500 hover:underline">
            Post
          </a>
          @elseif($report->report_type->value === 'topic_report')
          <a href="{{ route('topic.show', $report->reported_id) }}" class="text-blue-500 hover:underline">
            Topic
          </a>
          @else
          {{ str_replace('_', ' ', $report->report_type->value) }}
          @endif
        </td>

        <td class="px-4 py-4">{{ $report->reason }}</td>
        <td class="px-4 py-4">
          <span
            class="{{ $report->is_open ? 'text-green-600 bg-green-100' : 'text-red-600 bg-red-100' }} text-sm border rounded-full px-3 py-1 font-bold">
            {{ $report->is_open ? 'Open' : 'Closed' }}
          </span>
        </td>
        <td class="px-4 py-4">
          @if($report->is_open)
          <button class="px-2 py-1 rounded-md bg-green-500/[.80] hover:bg-green-500 text-white font-bold"
            onclick="openResolveModal({{ $report->id }})">
            resolve
          </button>

          @include('partials.report_resolve', ['report' => $report])
          @endif
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @endif
</div>


@endsection