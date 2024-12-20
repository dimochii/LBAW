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
          <img src="{{ asset( $user->image->path ?? 'images/groupdefault.jpg') }}"
        onerror="this.onerror=null;this.src='https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png';"
              class="max-w-full rounded-full size-9 mr-3 object-cover">
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
        <td>
            @if($user->is_suspended)
                <button type="button" class="unsuspend-btn font-bold px-2 py-1 rounded-md bg-green-500/[.80] hover:bg-green-500 text-white" data-user-id="{{$user->id}}" onclick="unsuspendUser({{ $user->id }})">
                    Unsuspend
                </button>
            @else
                <button type="button" class="suspend-btn px-2 py-1 rounded-md bg-red-500/[.80] hover:bg-red-500 text-white font-bold" data-user-id="{{$user->id}}" onclick="openSuspendModal({{ $user->id }})">
                    Suspend
                </button>
            @endif
        </td>


        <div id="suspend-modal" class="hidden fixed inset-0 flex justify-center items-center bg-gray-500 bg-opacity-50">
          <div class="bg-white p-6 rounded shadow-lg w-96">
              <h3 class="text-xl font-bold mb-4">Suspend User</h3>
              <form id="suspend-form" action="" method="POST">
                  @csrf
                  <input type="hidden" name="authenticated_user_id" id="authenticated_user_id">

                  <div class="mb-4">
                      <label for="reason" class="block font-medium text-gray-700 mb-1">Reason</label>
                      <input type="text" name="reason" id="reason" class="w-full border-gray-300 rounded p-2 border-2" required>
                  </div>

                  <div class="mb-6">
                      <label for="duration" class="block font-medium text-gray-700 mb-1">Duration (in days)</label>
                      <input type="number" name="duration" id="duration" class="w-full border-gray-300 rounded p-2 border-2" required>
                  </div>

                  <div class="flex justify-end gap-4">
                      <button type="button" class="px-2 py-1 rounded-md bg-gray-300 hover:bg-gray-400/[.80] text-white font-bold" onclick="closeSuspendModal()">Cancel</button>
                      <button type="submit" class="px-2 py-1 rounded-md bg-red-500/[.80] hover:bg-red-500 text-white font-bold">Suspend</button>
                  </div>
              </form>
          </div>
      </div>

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
{{-- 
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
</script> --}}