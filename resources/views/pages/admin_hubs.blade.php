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
    <div class="flex flex-row items-end p-4 h-full">
      <h1 class=" tracking-tight font-medium text-5xl">hubs <span
          class="text-2xl tracking-normal opacity-60">manage</span>
      </h1>
      <span class="ml-auto text-sm tracking-normal opacity-60">{{$startDate}} -> {{$endDate}}</span>
    </div>
    <div class="flex flex-row">
      <div class="px-4 py-4 bg-pastelRed border-black border-r-2 flex flex-col">
        <div class="text-2xl text-[#F4F2ED]/[.8] mb-auto">total hubs</div>
        <div class="text-6xl font-bold tracking-tighter text-[#F4F2ED]">{{$totalHubs}}00</div>
        <div class="text-lg tracking-tight text-[#F4F2ED]/[.8] mb-auto">{{$newHubs}} new hubs </div>

        @if ($comparisonHubs !== 0)
        <div class="before:content-['■_']">{{$comparisonHubs}} hubs.</div>
        @endif
      </div>
      <div class="flex flex-col grow ">
        <div class="h-12 flex items-center pl-2 md:pl-4 relative border-b-2 border-black bg-pastelBlue">
          <svg class="w-5 h-5 text-[#F4F2ED]/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input id="search-input" type="text" placeholder="search"
            class="w-full bg-transparent border-none text-[#F4F2ED] placeholder-[#F4F2ED] px-2 md:px-3 py-2 focus:outline-none ">
        </div>
        <div class="grow bg-white flex items-center justify-center"> nao sei o que por aqui</div>
      </div>
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
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200">
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
            <img src="https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png"
              class="max-w-full rounded-3xl min-w-[32px] mr-3  w-[32px]">
            <span class="truncate max-w-32 hover:max-w-full transition-all" data-sort>{{ $hub->name }}</span>
          </a>
        </td>
        <td class="px-4 py-4 break-all max-w-prose" data-sort>
          {{ $hub->description }}
        </td>
        <td class="px-4 py-4">
          <span
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-full {{ $hub->is_private ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
            @if($hub->is_private)
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