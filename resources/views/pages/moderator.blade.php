@extends('layouts.moderator')

@section('content')

<div class="flex flex-col md:flex-row md:divide-x-2 md:divide-y-0 divide-y-2 divide-black">
  <div class="flex flex-col w-[50%]  divide-y-2 divide-black ">
    <div class="flex flex-row p-4 h-full">
      <h1 class=" tracking-tight font-medium text-5xl">users <span
          class="text-2xl tracking-normal opacity-60">manage</span>
      </h1>
      <span class="ml-auto text-sm tracking-normal opacity-60 mt-auto">{{$startDate->toFormattedDateString()}} -> {{$endDate->toFormattedDateString()}}</span>
    </div>
    <div class="grid grid-cols-3">
      <div class="px-4 py-4 bg-pastelRed border-black border-r-2 flex flex-col">
        <div class="text-2xl text-[#F4F2ED]/[.8] mb-auto">active users</div>
        <div class="text-6xl font-bold tracking-tighter text-[#F4F2ED] mb-auto">{{$activeUserCount}}</div>


      </div>
      <div class="px-4 py-4 bg-pastelYellow border-black border-r-2 flex flex-col">
        <div class="text-2xl text-[#3C3D37]/[.8] mb-auto">moderators</div>
        <div class="text-6xl font-bold tracking-tighter text-[#3C3D37] mb-auto"> {{$moderatorCount}} </div>


      </div>
      <div class="px-4 py-4 bg-pastelGreen  flex flex-col">
        <div class="text-2xl text-[#F4F2ED]/[.8] mb-auto">followers</div>
        <div class="text-6xl font-bold tracking-tighter text-[#F4F2ED]"> {{$followers->count()}} </div>
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
      <x-chartjs-component :chart="$activeUserChart" />
    </div>
  </div>

</div>
<div>
  <table
    class="min-w-[1000px] w-full bg-white border-2 border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30 p-6">
    <thead class="bg-gray-100">
      <tr>
        <th class="cursor-pointer px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200"
          data-type="number">ID</th>
        <th class="cursor-pointer px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200"
          data-type="string">Handle</th>
        <th class="cursor-pointer px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200"
          data-type="string">Name</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200">
          moderator</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200">
          Remove follow</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
      @foreach($followers->get() as $user)
      <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-4 py-4 whitespace-nowrap" data-sort>{{$user->id}}</td>
        <td class="px-4 py-4">
          <a class="flex items-center" href="{{ route('user.profile', $user->id) }}">
            <img src="{{ asset($user->user->image->path ?? 'images/default.jpg') }}"
              class="max-w-full rounded-3xl min-w-[32px] mr-3  w-[32px]">
            <span class="break-all" data-sort>{{ '@' . $user->username }}</span>
          </a>
        </td>
        <td class="px-4 py-4 break-all" data-sort>
          {{ $user->name }}
        </td>
        <td class="px-4 py-4">
          <input id="admin-checkbox-{{ $user->id }}" type="checkbox" class="w-4 h-4 accent-blue-500"
            @if($hub->moderators->pluck('id')->contains($user->id)) checked @endif
          onclick="toggleModerator({{ $user->id }},  {{$hub->id}} , this.checked)"
          >
        </td>
        
        <td class="px-4 py-4">
    <form action="{{ route('community.remove_follower',['user_id' => $user->id, 'community_id' => $id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to make this user unfollow the community?')">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="px-2 py-1 rounded-md bg-red-500/[.80] hover:bg-red-500 text-white font-bold">
                    Unfollow
            </button>
        </form>
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

    // Initialize directions array after the table headers are loaded
    const directions = Array.from(headers).map(function (header) {
        return '';
    });

    const transform = function (index, content) {
        const type = headers[index].getAttribute('data-type');
        switch (type) {
            case 'number':
                return parseFloat(content);
            case 'string':
            default:
                return content;
        }
    };

    function updateHeaderText(index, direction) {
        headers.forEach(function (header) {
              header.textContent = header.textContent.replace(/ (ASC|DESC)$/, '');
        });

        const header = headers[index];

        if (direction === 'asc') {
            header.textContent = header.textContent.replace(/ (ASC|DESC)$/, '') + ' ASC';
        } else {
            header.textContent = header.textContent.replace(/ (ASC|DESC)$/, '') + ' DESC';
        }
    }

    function sortColumn(index) {
        // Set the direction for sorting
        const direction = directions[index] || 'asc';

        // Set the multiplier based on the sorting direction
        const multiplier = direction === 'asc' ? 1 : -1;

        // Convert the NodeList to an array so we can sort it
        const newRows = Array.from(rows);

        newRows.sort(function (rowA, rowB) {
            const cellA = rowA.querySelectorAll('td')[index].innerHTML;
            const cellB = rowB.querySelectorAll('td')[index].innerHTML;

            const a = transform(index, cellA);
            const b = transform(index, cellB);

            if (a > b) return 1 * multiplier;
            if (a < b) return -1 * multiplier;
            return 0;
        });

        // Remove all current rows from the table
        [].forEach.call(rows, function (row) {
            tableBody.removeChild(row);
        });

        // Reverse the sorting direction for the next click
        directions[index] = direction === 'asc' ? 'desc' : 'asc';

        // Append the sorted rows back to the table
        newRows.forEach(function (newRow) {
            tableBody.appendChild(newRow);
        });

        updateHeaderText(index, directions[index]);

    }

    function filterTable(query) {
        const queryLower = query.toLowerCase();

        rows.forEach(function (row) {
            let rowVisible = false;

            row.querySelectorAll('td').forEach(function (cell, index) {
                const dataType = headers[index].getAttribute('data-type');
                if (dataType) {
                    const cellText = cell.textContent.toLowerCase();
                    if (cellText.includes(queryLower)) {
                        rowVisible = true; // If any cell matches, show the row
                    }
                }
            });

            // Show or hide the row based on whether it matched the query
            row.style.display = rowVisible ? '' : 'none';
        });
    }

    // Assign click listeners to column headers for sorting
    headers.forEach(function (header, index) {
        if (header.hasAttribute('data-type')) {
            header.addEventListener('click', function () {
                sortColumn(index);
            });
        }
    })

    searchInput.addEventListener('input', function () {
        filterTable(searchInput.value);
    });
  })




function toggleModerator(user_id, community_id, isChecked) {
    console.log('User ID:', user_id);
    console.log('Hub ID:', community_id);
    const action = isChecked ? 'make_moderator' : 'remove_moderator';
    const confirmationMessage = isChecked
        ? 'Are you sure you want to grant this user moderator privileges in this community?'
        : 'Are you sure you want to revoke this user\'s moderator privileges in this community?';

    if (confirm(confirmationMessage)) {
        fetch(`/users/${user_id}/${community_id}/${action}`, {
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
        fetch(`/hub/${communityId}/remove_follower/${userId}`, {
            method: 'POST',
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
