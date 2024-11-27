@extends('layouts.app')
@section('content')
<div class=" text-gray-900 py-12">
    <div class="container mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="tracking-tighter font-medium text-6xl px-4">Hubs</h1>
            <a href="{{ route('communities.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-md">Create Hub</a>
        </div>
        @if ($communities->isEmpty())
        <div class="bg-gray-200 p-4 rounded-md">
            <p class="text-gray-600">You haven't created any hubs yet.</p>
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
            @foreach ($communities as $community)
            <div class="bg-gray-200 rounded-lg shadow-md">
                <div class="p-4">
                    <div class="flex justify-between items-center mb-3">
                        <h2 class="text-lg font-medium">{{ $community->name }}</h2>
                        <a href="{{ route('communities.show', $community->id) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-600 font-medium py-2 px-4 rounded-md">View Hub</a>
                    </div>
                    <p class="text-gray-600 font-light mb-4">{{ $community->description }}</p>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">{{ $community->created_at }}</span>
                        <span class="bg-{{ $community->privacy === 'public' ? 'green' : 'red' }}-500 text-white font-medium py-1 px-2 rounded-full">{{ ucfirst($community->privacy) }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection