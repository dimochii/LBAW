@extends('layouts.admin')

@section('content')

<head>
  <script defer src="{{ asset('js/app.js') }}"></script>
</head>
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
      <h1 class=" tracking-tight font-medium text-5xl">posts <span
          class="text-2xl tracking-normal opacity-60">manage</span>
      </h1>
      <span class="ml-auto text-sm tracking-normal opacity-60 mt-auto">{{$startDate}} -> {{$endDate}}</span>
    </div>
    <div class="grid grid-cols-3">
      <div class="px-4 py-4 bg-pastelRed border-black border-r-2 flex flex-col">
        <div class="text-2xl text-[#F4F2ED]/[.8] mb-auto">news</div>
        <div class="text-6xl font-bold tracking-tighter text-[#F4F2ED] mb-auto">{{ $newsCount }}</div>
        <div class="text-lg tracking-tight text-[#F4F2ED]/[.8] mb-auto">{{ $newNewsCount }} new news </div>


      </div>
      <div class="px-4 py-4 bg-pastelYellow border-black border-r-2 flex flex-col">
        <div class="text-2xl text-[#3C3D37]/[.8] mb-auto">topics</div>
        <div class="text-6xl font-bold tracking-tighter text-[#3C3D37] mb-auto">{{ $topicsCount }}</div>
        <div class="text-lg tracking-tight text-[#3C3D37]/[.8] mb-auto">{{ $newTopicsCount }} new topics </div>


      </div>
      <div class="px-4 py-4 bg-pastelGreen  flex flex-col">
        <div class="text-2xl text-[#F4F2ED]/[.8] mb-auto">news/topics ratio</div>
        <div class="text-6xl font-bold tracking-tighter text-[#F4F2ED] mb-auto"> {{ round($newsCount / $topicsCount, 2)
          }} </div>
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
      <x-chartjs-component :chart="$comboPosts" />
    </div>

  </div>
</div>

<div class="border-b-2 border-black w-full font-light text-xl tracking-tighter">
  <div class="w-full">
    @php
    $activeTab = request()->query('tab', 'news');
    @endphp
    <nav class="max-w-7xl mx-auto px-6 flex flex-wrap gap-8 md:gap-8">
      <a href="{{ url('/admin/posts?tab=news') }}"
        class="py-4 {{ $activeTab === 'news' ? 'text-gray-900 border-b-2 border-black' : 'text-gray-500 hover:text-gray-700' }}">
        news
      </a>
      <a href="{{ url('/admin/posts?tab=topics') }}"
        class="py-4 {{ $activeTab === 'topics' ? 'text-gray-900 border-b-2 border-black' : 'text-gray-500 hover:text-gray-700' }}">
        topics
      </a>
    </nav>
  </div>
</div>

@if ($activeTab == 'news')
<div class="">
  <table
    class="w-full bg-white border-2 border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30 p-6">
    <thead class="bg-gray-100">
      <tr>
        <th
          class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200 cursor-pointer"
          data-type="number">ID</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200">
          News URL</th>
        <th
          class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200 cursor-pointer"
          data-type="string">Title</th>
        <th
          class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200 cursor-pointer"
          data-type="string">Content</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200">
          upvotes/downvotes</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200">
          threads</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200">
          delete</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
      @foreach($data as $item)
      <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-4 py-4 whitespace-nowrap">{{ $item->post_id }}</td>
        <td class="px-4 py-4">
          <a class="prose" href="{{ $item->news_url }}">
            {{ Str::limit($item->news_url, 30) }}
          </a>
        </td>
        <td
          class="px-4 py-4 break-all max-w-[16rem] overflow-hidden whitespace-nowrap text-ellipsis hover:overflow-auto hover:whitespace-normal hover:text-wrap hover:max-w-prose transition-all">
          <a class="flex items-center" href="{{ route('news.show',['post_id' => $item->post->id]) }}">
            {{ $item->post->title }}
        </td>
        <td
          class="px-4 py-4 break-all max-w-[24rem] overflow-hidden whitespace-nowrap text-ellipsis hover:overflow-auto hover:whitespace-normal hover:text-wrap hover:max-w-prose transition-all">
          {{ $item->post->content }}
        </td>
        <td class="px-4 py-4">
          <strong class="text-pastelBlue">{{ $item->post->upvote_count }}</strong> <strong>/</strong> <strong
            class="text-pastelRed">{{ $item->post->downvote_count }}</strong>
        </td>
        <td class="px-4 py-4">
          {{ $item->post->comments->count() }}
        </td>
        <td class="px-4 py-4">
          <button name="delete-button"
            class="px-2 py-1 rounded-md bg-red-500/[.80] hover:bg-red-500 text-white font-bold">
            Delete
          </button>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
@else
<div class="">
  <table
    class="w-full bg-white border-2 border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30 p-6">
    <thead class="bg-gray-100">
      <tr>
        <th
          class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200 cursor-pointer"
          data-type="number">ID</th>
        <th
          class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200 cursor-pointer"
          data-type="string">Title</th>
        <th
          class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200 cursor-pointer"
          data-type="string">Content</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200">
          upvotes/downvotes</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200">
          threads</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200">
          status</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-200">
          delete</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
      @foreach($data as $topic)
      <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-4 py-4 whitespace-nowrap">{{ $topic->post_id }}</td>

        <td
          class="px-4 py-4 break-all max-w-[16rem] overflow-hidden whitespace-nowrap text-ellipsis hover:overflow-auto hover:whitespace-normal hover:text-wrap hover:max-w-prose transition-all">
          <a class="flex items-center" href="{{ route('topic.show',['post_id' => $topic->post->id]) }}">
            {{ $topic->post->title }}
        </td>
        <td
          class="px-4 py-4 break-all max-w-[24rem] overflow-hidden whitespace-nowrap text-ellipsis hover:overflow-auto hover:whitespace-normal hover:text-wrap hover:max-w-prose transition-all">
          {{ $topic->post->content }}
        </td>
        <td class="px-4 py-4">
          <strong class="text-pastelBlue">{{ $topic->post->upvote_count }}</strong> <strong>/</strong> <strong
            class="text-pastelRed">{{ $topic->post->downvote_count }}</strong>
        </td>
        <td class="px-4 py-4">
          {{ $topic->post->comments->count() }}
        </td>
        <td class="px-4 py-4 relative">
          {{-- <span
            class="{{ $topic->status ? 'text-green-600 bg-green-100' : 'text-red-600 bg-red-100' }} text-sm border rounded-full px-3 py-1 font-bold">
            {{ $topic->status ? 'Approved' : 'Rejected' }}
          </span> --}}
          <input type="checkbox" id="status-{{$topic->post->id}}" class="hidden peer">
          <label for="status-{{$topic->post->id}}">
            @if ($topic->status->value === 'pending')
            <span
              class="cursor-pointer bg-orange-200 text-orange-500 text-sm border rounded-full px-3 py-1 font-bold whitespace-nowrap">
              Waiting Approval
            </span>
            @elseif ($topic->status->value === 'accepted')
            <span class="text-green-600 bg-green-100 text-sm border rounded-full px-3 py-1 font-bold">
              Approved
            </span>
            @elseif ($topic->status->value === 'rejected')
            <span class="text-red-600 bg-red-100 text-sm border rounded-full px-3 py-1 font-bold">
              Rejected
            </span>
            @else
            <span class="text-gray-600 bg-gray-100 text-sm border rounded-full px-3 py-1 font-bold">
              Unknown Status
            </span>
            @endif
          </label>
          @if ($topic->status->value === 'pending')
          <div
            class="z-50 transition-all absolute left-5 top-12 invisible peer-checked:visible opacity-0 peer-checked:opacity-100 bg-[#F4F2ED] text-[#3C3D37] border border-[#3C3D37] rounded shadow-lg min-w-28">
            <div class="ml-2 py-1  font-light text-gray-400">status</div>
            <ul class=" text-sm *:rounded flex flex-col *:cursor-pointer">
              <li id="accept-{{$topic->post->id}}" class="py-1 px-4 hover:bg-green-200 hover:text-green-500">
                Approve
              </li>
              <li id="reject-{{$topic->post->id}}" class="py-1 px-4 hover:bg-red-200 hover:text-red-500 ">
                Reject
              </li>
            </ul>
          </div>
          @endif
        </td>
        <td class="px-4 py-4">
          <button name="delete-button"
            class="px-2 py-1 rounded-md bg-red-500/[.80] hover:bg-red-500 text-white font-bold">
            Delete
          </button>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endif

@endsection

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search-input');
    const table = document.querySelector('table');
    const tableBody = table.querySelector('tbody');
    const rows = tableBody.querySelectorAll('tr');
    const headers = table.querySelectorAll('th');
    const accept = document.querySelectorAll('li[id^="accept-"]')
    const reject = document.querySelectorAll('li[id^="reject-"]')

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
                        rowVisible = true;
                    }
                }
            });

            row.style.display = rowVisible ? '' : 'none';
        });
    }

    /* 
     ($topic->status->value === 'accepted')
            <span class="text-green-600 bg-green-100 text-sm border rounded-full px-3 py-1 font-bold">
              Approved
            </span>
            ($topic->status->value === 'rejected')
            <span class="text-red-600 bg-red-100 text-sm border rounded-full px-3 py-1 font-bold">
              Rejected
            </span>
    */
    
    function acceptTopic(id) {
      fetch(`/topic/${id}/accept`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
      })
      .then(response => response.json()) 
      .then(data => {
        if (data.status === 'ok') {
          console.log('Topic accepted successfully');
          const label = document.querySelector(`label[for="status-${id}"`)
          const input = document.getElementById(`status-${id}`)
          input.checked = false
          label.innerHTML = 
          `
          <span class="text-green-600 bg-green-100 text-sm border rounded-full px-3 py-1 font-bold">
              Approved
          </span>
          `
        } else {
          console.error('Failed to accept the topic');
        }
      })
      .catch(error => {
        console.error('Error:', error);
      });
    }

    function rejectTopic(id) {
      fetch(`/topic/${id}/reject`, {
        method: 'POST', 
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 
        }      
      })
      .then(response => response.json()) 
      .then(data => {
        if (data.status === 'ok') {
          console.log('Topic rejected successfully');
          const label = document.querySelector(`label[for="status-${id}"`)
          const input = document.getElementById(`status-${id}`)
          input.checked = false
          label.innerHTML = 
          `
          <span class="text-red-600 bg-red-100 text-sm border rounded-full px-3 py-1 font-bold">
              Rejected
          </span>
          `
          
        } else {
          console.error('Failed to reject the topic')
        }
      })
      .catch(error => {
        console.error('Error:', error)
      });
    }

    accept.forEach((ele,  index) => {      
      const parsedId = ele.id.split('-')[1]
      ele.addEventListener('click', () => {
        acceptTopic(parsedId);
      });
    })

    reject.forEach((ele,  index) => {      
      const parsedId = ele.id.split('-')[1]
      ele.addEventListener('click', () => {
        rejectTopic(parsedId);
      });
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