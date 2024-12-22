@extends('layouts.app')

@section('content')
<div class="p-4">
    <h1 class="tracking-tighter font-medium text-6xl py-2 mb-4 flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mr-2 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        Notifications
    </h1>

    <!-- Navigation Bar -->
    <div class="border-b-2 border-black w-full font-light text-xl tracking-tighter mb-4">
        <nav class="max-w-7xl mx-auto px-6 flex flex-wrap gap-4 md:gap-8">
            @php
                $activeTab = request()->query('tab', 'all'); // Default to 'all'
            @endphp
            <a href="{{ url('/notifications?tab=all') }}"
               class="py-4 relative group {{ $activeTab === 'all' ? 'text-gray-900 border-b-2 border-black' : 'text-gray-500 hover:text-gray-700' }}">
              all
            </a>
            <a href="{{ url('/notifications?tab=unread') }}"
               class="py-4 relative group {{ $activeTab === 'unread' ? 'text-gray-900 border-b-2 border-black' : 'text-gray-500 hover:text-gray-700' }}">
              unread
            </a>
            <a href="{{ url('/notifications?tab=read') }}"
               class="py-4 relative group {{ $activeTab === 'read' ? 'text-gray-900 border-b-2 border-black' : 'text-gray-500 hover:text-gray-700' }}">
              read
            </a>
        </nav>
    </div>

    <!-- Notifications Content -->
    <div class="space-y-6">
        @if($activeTab === 'unread' || $activeTab === 'all')
        <div>
            <h2 class="text-lg font-semibold mb-2 text-gray-700">Unread Notifications</h2>
            
            @if($unreadNotifications->isEmpty())
                <div class="bg-white border-2 border-black/10 rounded-lg p-4 text-center text-gray-500">
                    No unread notifications.
                </div>
            @else
                <table class="w-full bg-white border-2 border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30">
                    <tbody class="divide-y divide-gray-200">
                        @foreach($unreadNotifications as $notification)
                            @include('partials.notification', ['notification' => $notification])
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        @endif

        @if($activeTab === 'read' || $activeTab === 'all')
        <div>
            <h2 class="text-lg font-semibold mb-2 text-gray-700">Read Notifications</h2>
            
            @if($readNotifications->isEmpty())
                <div class="bg-white border-2 border-black/10 rounded-lg p-4 text-center text-gray-500">
                    No read notifications.
                </div>
            @else
                <table class="w-full bg-white border-2 border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30">
                    <tbody class="divide-y divide-gray-200">
                        @foreach($readNotifications as $notification)
                            @include('partials.notification', ['notification' => $notification])
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        @endif
    </div>
</div>


@endsection
