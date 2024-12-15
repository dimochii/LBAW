@extends('layouts.moderator')

@section('content')

<div class="flex flex-col md:flex-row md:divide-x-2 md:divide-y-0 divide-y-2 divide-black">
  <div class="flex flex-col w-[50%]  divide-y-2 divide-black ">
    <div class="flex flex-row p-4 h-full">
      <h1 class=" tracking-tight font-medium text-5xl">users <span
          class="text-2xl tracking-normal opacity-60">manage</span>
      </h1>
      <span class="ml-auto text-sm tracking-normal opacity-60 mt-auto">{{$startDate->toFormattedDateString()}} ->
        {{$endDate->toFormattedDateString()}}</span>
    </div>
    <div class="grid grid-cols-3">
      <div class="px-4 py-4 bg-pastelRed border-black border-r-2 flex flex-col">
        <div class="text-2xl text-[#F4F2ED]/[.8] mb-auto">pending</div>
        <div class="text-6xl font-bold tracking-tighter text-[#F4F2ED] mb-auto">{{ $pendingReportsCount }}</div>


      </div>
      <div class="px-4 py-4 bg-pastelYellow border-black border-r-2 flex flex-col">
        <div class="text-2xl text-[#3C3D37]/[.8] mb-auto">resolved</div>
        <div class="text-6xl font-bold tracking-tighter text-[#3C3D37] mb-auto"> {{$resolvedReportsCount}} </div>


      </div>
      <div class="px-4 py-4 bg-pastelGreen  flex flex-col">
        <div class="text-2xl text-[#F4F2ED]/[.8] mb-auto">reports</div>
        <div class="text-6xl font-bold tracking-tighter text-[#F4F2ED]"> {{$reports->count()}} </div>
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
      <x-chartjs-component :chart="$reportsChart" />
    </div>
  </div>

</div>
<div>

  <table
    class="w-full bg-white border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30 p-6">
    <thead class="bg-gray-100">
      <tr>
        <th
          class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200 cursor-pointer">
          ID</th>
        <th
          class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200 cursor-pointer">
          Reporter</th>
        <th
          class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200 cursor-pointer">
          Reported Type</th>
        <th
          class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200 cursor-pointer">
          Reason</th>
        <th
          class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200 cursor-pointer">
          Status</th>
        <th
          class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200 cursor-pointer">
          Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
      @foreach($reports->get() as $report)
      <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-4 py-4 whitespace-nowrap">{{ $report->id }}</td>
        <td class="px-4 py-4">
          <a class="flex items-center" href="{{ route('user.profile', $report->authenticated_user_id) }}">
            <img
              src="{{ $report->user->avatar ?? 'https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png' }}"
              class="max-w-full rounded-3xl min-w-[32px] mr-3 w-[32px]">
            <span class="break-all" data-sort>{{ '@' . $report->user->username }}</span>
          </a>
        </td>
        <td class="px-4 py-4">
          @if($report->report_type->value === 'user_report')
          <a href="{{ route('user.profile', $report->reported_id) }}" class="text-blue-500 hover:underline">
            {{ str_replace('_', ' ', $report->report_type->value) }}
          </a>
          @elseif($report->report_type->value === 'comment_report')
          <a href="{{  route('post.show', $report->reported_id) }}" class="text-blue-500 hover:underline">
            {{ str_replace('_', ' ', $report->report_type->value) }}
          </a>
          @elseif($report->report_type->value === 'item_report')
          <a href="{{ route('news.show', $report->reported_id) }}" class="text-blue-500 hover:underline">
            {{ str_replace('_', ' ', $report->report_type->value) }}
          </a>
          @elseif($report->report_type->value === 'topic_report')
          <a href="{{ route('topic.show', $report->reported_id) }}" class="text-blue-500 hover:underline">
            {{ str_replace('_', ' ', $report->report_type->value) }}
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
  
</div>


@endsection

<script>
  document.addEventListener('DOMContentLoaded', function () {
const searchInput = document.getElementById('search-input');
const table = document.querySelector('table');
const tableBody = table.querySelector('tbody');
const rows = tableBody.querySelectorAll('tr');
const headers = table.querySelectorAll('th');

const directions = Array.from(headers).map(() => '');

const transform = function (index, content) {
const type = headers[index].getAttribute('data-type');
switch (type) {
  case 'number': return parseFloat(content);
  case 'string':
  default: return content;
}
};

function updateHeaderText(index, direction) {
headers.forEach(header => {
  header.textContent = header.textContent.replace(/ (ASC|DESC)$/, '');
});

const header = headers[index];
header.textContent += direction === 'asc' ? ' ASC' : ' DESC';
}

function sortColumn(index) {
const direction = directions[index] || 'asc';
const multiplier = direction === 'asc' ? 1 : -1;

const newRows = Array.from(rows);
newRows.sort((rowA, rowB) => {
  const cellA = rowA.querySelectorAll('td')[index].innerHTML;
  const cellB = rowB.querySelectorAll('td')[index].innerHTML;

  const a = transform(index, cellA);
  const b = transform(index, cellB);

  return (a > b ? 1 : a < b ? -1 : 0) * multiplier;
});

rows.forEach(row => tableBody.removeChild(row));
directions[index] = direction === 'asc' ? 'desc' : 'asc';
newRows.forEach(newRow => tableBody.appendChild(newRow));

updateHeaderText(index, directions[index]);
}

function filterTable(query) {
const queryLower = query.toLowerCase();
rows.forEach(row => {
  const rowVisible = Array.from(row.querySelectorAll('td')).some(cell => {
    return cell.textContent.toLowerCase().includes(queryLower);
  });
  row.style.display = rowVisible ? '' : 'none';
});
}

headers.forEach((header, index) => {
if (header.hasAttribute('data-type')) {
  header.addEventListener('click', () => sortColumn(index));
}
});

searchInput.addEventListener('input', () => filterTable(searchInput.value));
});

function openResolveModal(reportId) {
const modal = document.getElementById('resolveModal');
if (modal) {
  modal.classList.remove('hidden');
  const form = modal.querySelector('#resolveForm');
  form.action = `/report/${reportId}/resolve`; 
  document.getElementById('report_id').value = reportId;
} else {
  console.error(`Modal not found!`);
}
}

function closeResolveModal() {
const modal = document.getElementById('resolveModal');
if (modal) {
  modal.classList.add('hidden');
}
}


function toggleModerator(userId, communityId, isChecked) {
    const action = isChecked ? 'make_moderator' : 'remove_moderator';
    const confirmationMessage = isChecked
        ? 'Are you sure you want to grant this user moderator privileges in this community?'
        : 'Are you sure you want to revoke this user\'s moderator privileges in this community?';

    if (confirm(confirmationMessage)) {
        fetch(`/hub/${communityId}/${action}/${userId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({})
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to update moderator status.');
            }
            return response.json();
        })
        .then(data => {
            alert(data.message);
        })
        .catch(error => {
            alert(error.message);
            document.getElementById(`moderator-checkbox-${userId}`).checked = !isChecked;
        });
    } else {
        document.getElementById(`moderator-checkbox-${userId}`).checked = !isChecked;
    }
}

function removeFollower(userId, communityId) {
    const confirmationMessage = 'Are you sure you want to remove this user as a follower from the community?';

    if (confirm(confirmationMessage)) {
        fetch(`/community/${communityId}/remove_follower/${userId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to remove follower.');
            }
            return response.json();
        })
        .then(data => {
            alert(data.message);

            const followerRow = document.getElementById(`follower-row-${userId}`);
            if (followerRow) {
                followerRow.remove();
            }
        })
        .catch(error => {
            alert(error.message);
        });
    }
}


</script>