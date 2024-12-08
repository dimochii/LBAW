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

<div class="p-4 ">
  <div class="w-[50%] mx-auto">
    <x-chartjs-component :chart="$chartUsers" />
  </div>
</div>

<div class="p-4 ">
  <h1 class="text-xl font-bold mb-2">users</h1>
  <table
    class="min-w-[1000px] w-full bg-white border-2 border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30 p-6">
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
    @foreach($users as $user)
      <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-4 py-4 whitespace-nowrap">{{$user->id}}</td>
        <td class="px-4 py-4">
          <a class="flex items-center" href="{{ route('user.profile', $user->id) }}">
            <img src="https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png"
              class="max-w-full rounded-3xl min-w-[32px] mr-3  w-[32px]">
            <span class="break-all">{{ '@' . $user->username }}</span>
          </a>
        </td>
        <td class="px-4 py-4 break-all">
        {{ $user->name }}
        </td>
        <td class="px-4 py-4">
          <input
              id="admin-checkbox-{{ $user->id }}"
              type="checkbox"
              class="w-4 h-4 accent-blue-500"
              @if($user->is_admin) checked @endif
              onclick="toggleAdmin({{ $user->id }}, this.checked)"
          >
      </td>
        <td class="px-4 py-4">
          <input
              id="suspend-checkbox-{{ $user->id }}"
              type="checkbox"
              class="w-4 h-4 accent-red-500"
              @if($user->is_suspended) checked @endif
              onclick="toggleSuspend({{ $user->id }}, this.checked)"
          >
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
