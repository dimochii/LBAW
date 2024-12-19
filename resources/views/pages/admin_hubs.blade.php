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
        <form action="{{ route('admin.community.delete', ['id' => $hub->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this community?')">
          @csrf
          @method('DELETE')
          <button type="submit" class="px-2 py-1 rounded-md bg-red-500/[.80] hover:bg-red-500 text-white font-bold delete-button" data-community-id="{{ $hub->id }}">
            Delete
        </button>
        </form>
      </td>


      </tr>
      @endforeach
    </tbody>
  </table>
</div>

<div id="error-toast" class="hidden fixed top-4 left-1/2 transform -translate-x-1/2 bg-red-500 text-white p-4 rounded-md shadow-lg z-50">
  <p id="toast-message" class="text-lg font-bold"></p>
</div>

<div id="success-toast" class="hidden fixed top-4 left-1/2 transform -translate-x-1/2 bg-green-500 text-white p-4 rounded-md shadow-lg z-50">
  <p id="toast-message-success" class="text-lg font-bold"></p>
</div>


@endsection

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search-input');
    const table = document.querySelector('table');
    const tableBody = table.querySelector('tbody');
    const rows = tableBody.querySelectorAll('tr');
    const headers = table.querySelectorAll('th');
    const privacies = tableBody.querySelectorAll('[data-route]')

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
        const direction = directions[index] || 'asc';
        const multiplier = direction === 'asc' ? 1 : -1;
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

        [].forEach.call(rows, function (row) {
            tableBody.removeChild(row);
        });

        directions[index] = direction === 'asc' ? 'desc' : 'asc';

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
                        rowVisible = true; 
                    }
                }
            });

            row.style.display = rowVisible ? '' : 'none';
        });
    }

    function updatePrivacy(element) {
      const routeID = element.getAttribute('data-route')
      
      fetch(`/hub/${routeID}/privacy`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
        }
        })
      .then(response => {
          if (!response.ok) {
              throw new Error(`HTTP error. Status: ${response.status}`)
          }
          return response.json()
      })
      .then(data => {
        if (data.success) {
            if (data.privacy == 'Private')
            {
              element.innerHTML = `
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              </svg>
              Private`
              element.classList =  'cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-full bg-red-100 text-red-700'
            } else if (data.privacy == 'Public')
            {
              element.innerHTML = `
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              <path d="M7 11h10"></path>
              </svg>
              Public
              `
              element.classList = 'cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-full bg-green-100 text-green-700'
            }
        }  
      })
      .catch(error => {
          console.error('Error updating privacy', error)
      });

    }

    privacies.forEach(function (element) {
      element.addEventListener('click', function () {
        updatePrivacy(element)
      })
    })

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

  document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-button');
    
    deleteButtons.forEach(button => {
        button.closest('form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to delete this community?')) {
                try {
                    const response = await fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: JSON.stringify({
                            _method: 'DELETE'
                        })
                    });
                    
                    const result = await response.json();
                    
                    const notification = document.createElement('div');
                    notification.className = 'fixed left-1/2 top-4 -translate-x-1/2 w-96 p-4 rounded shadow-lg';
                    
                    if (response.ok) {
                        notification.style.backgroundColor = '#c5e6a6';  
                        notification.style.border = '2px solid #34a853';
                    } else {
                        notification.style.backgroundColor = '#ed6a5a';  
                        notification.style.border = '2px solid #a30000';
                    }
                    
                    const icon = document.createElement('span');
                    icon.className = 'inline-block mr-2';
                    icon.innerHTML = response.ok 
                        ? '✓'  
                        : '✕'; 
                    icon.style.color = response.ok ? '#34a853' : '#a30000';
                    
                    const messageText = document.createElement('span');
                    messageText.textContent = response.ok 
                        ? 'Community successfully deleted!'
                        : 'Failed to delete community. It may contain posts or you may not have permission.';
                    messageText.style.color = '#333333';
                    
                    notification.appendChild(icon);
                    notification.appendChild(messageText);
                    
                    document.body.appendChild(notification);
                    
                    setTimeout(() => {
                        notification.remove();
                        if (response.ok) {
                            window.location.href = '/admin/hubs';
                        }
                    }, 3000);
                    
                } catch (error) {
                    console.error('Error:', error);

                    const errorNotification = document.createElement('div');
                    errorNotification.className = 'fixed left-1/2 top-4 -translate-x-1/2 w-96 p-4 rounded shadow-lg';
                    errorNotification.style.backgroundColor = '#ffebee';
                    errorNotification.style.border = '1px solid #a30000';
                    
                    const errorIcon = document.createElement('span');
                    errorIcon.className = 'inline-block mr-2';
                    errorIcon.innerHTML = '✕';
                    errorIcon.style.color = '#a30000';
                    
                    const errorText = document.createElement('span');
                    errorText.textContent = 'An error occurred while processing your request.';
                    errorText.style.color = '#333333';
                    
                    errorNotification.appendChild(errorIcon);
                    errorNotification.appendChild(errorText);
                    document.body.appendChild(errorNotification);
                    
                    setTimeout(() => {
                        errorNotification.remove();
                    }, 3000);
                }
            }
        });
    });
});
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
                document.getElementById(`admin-checkbox-${userId}`).checked = !isChecked;
            });
        } else {
            document.getElementById(`admin-checkbox-${userId}`).checked = !isChecked;
        }
    }

   


</script>