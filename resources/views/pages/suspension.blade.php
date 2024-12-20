@extends('layouts.app')
@section('content')
<div class="min-h-screen flex flex-col items-center justify-center text-gray-400 p-6">
    <div class="text-center mb-6">
        <h1 class="text-4xl font-extrabold mb-4 text-rose-500">You're Suspended</h1>
        <p class="text-lg font-semibold text-gray-800">
            Your WhatsUp profile has been banned for activity that violates our core values.
        </p>
    </div>

    <div class="flex justify-center mb-6">
        @if($suspensions->isNotEmpty())
            @php
                $latestSuspension = $suspensions->first();
            @endphp
            <div class="text-center mb-4">
                <p class="text-base font-semibold text-gray-800">Suspension Start:
                    <span class="text-rose-400">{{ $latestSuspension->start }}</span>
                </p>
                <p class="text-base font-semibold text-gray-800">Duration:
                    <span class="text-rose-400">{{ $latestSuspension->duration ?? 'Indefinite' }}</span>
                </p>
                <p class="text-base font-semibold text-gray-800">Reason:
                    <span class="text-rose-400">{{ $latestSuspension->reason }}</span>
                </p>
            </div>
        @else
            <p class="text-gray-800">No suspension details available.</p>
        @endif
    </div>

    <div class="flex justify-center">
        <button type="button" class="flex items-center gap-3 bg-rose-700 hover:bg-rose-600 text-white px-6 py-3 font-semibold rounded-lg shadow-lg transition cursor-not-allowed" disabled>
            <svg class="animate-spin h-5 w-5 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            processing
        </button>
    </div>

    <footer class="mt-8 text-sm text-gray-500">
        To access the site, please <a href="/logout" class="text-rose-400 hover:underline">logout</a>.
    </footer>
</div>
@endsection
