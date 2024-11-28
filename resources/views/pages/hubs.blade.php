@extends('layouts.app')
@section('content')
<div class="text-gray-900 bg-[#F5F5F0] min-h-screen">
    <div class="container mx-auto px-4">
        <h1 class="text-5xl text-gray-800 tracking-tighter font-medium py-4">Hubs</h1>

        <div class="mb-4 flex justify-end">
            <form method="GET" action="{{ route('communities.index') }}" class="flex space-x-4">
                <select name="sort_by" 
                        class="border border-pastelBlue rounded-lg bg-white px-4 py-2 text-gray-800 focus:outline-none focus:ring-2 focus:ring-pastelBlue transition appearance-none hover:bg-pastelBlue hover:text-white"
                        onchange="this.form.submit()">
                    <option value="name" {{ $sortBy === 'name' ? 'selected' : '' }}>name</option>
                    <option value="followers_count" {{ $sortBy === 'followers_count' ? 'selected' : '' }}>followers</option>
                </select>
                <select name="order" 
                        class="border border-pastelBlue rounded-lg bg-white px-4 py-2 text-gray-800 focus:outline-none focus:ring-2 focus:ring-pastelBlue transition appearance-none hover:bg-pastelBlue hover:text-white"
                        onchange="this.form.submit()">
                    <option value="asc" {{ $order === 'asc' ? 'selected' : '' }}>ascending</option>
                    <option value="desc" {{ $order === 'desc' ? 'selected' : '' }}>descending</option>
                </select>
            </form>
        </div>

        @if ($communities->isEmpty())
            <div class="bg-white p-8 rounded-lg shadow-md text-center border border-gray-200">
                <p class="text-gray-600 text-xl font-light">You haven't created any hubs yet.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($communities as $community)
                    <div class="bg-white border border-pastelGreen rounded-lg overflow-hidden transform transition-all duration-300 hover:shadow-xl">
                        <div class="relative">
                            <div class="h-48 bg-[#E6E6DC] flex items-center justify-center relative overflow-hidden">
                                <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml,%3Csvg%20xmlns=%22http://www.w3.org/2000/svg%22%20viewBox=%220%200%2080%2080%22%20width=%2280%22%20height=%2280%3E%3Crect%20width=%2280%22%20height=%2280%22%20fill=%22%23f0f0f0%22/%3E%3Cpath%20d=%22M0%200L80%2080ZM80%200L0%2080Z%22%20stroke-width=%221%22%20stroke=%22%23cccccc%22/%3E%3C/svg%3E')]"></div>
                                <span class="text-4xl tracking-tighter text-gray-700 opacity-70 z-10">{{ strtoupper(substr($community->name, 0, 1)) }}</span>
                            </div>
                            
                            <div class="absolute top-4 right-4 bg-pastelGreen text-[#F5F5F0] rounded-full px-3 py-1 text-sm font-medium shadow-md">
                                {{ $community->followers_count }} followers
                            </div>
                        </div>

                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h2 class="text-xl text-gray-800 mb-1">{{ $community->name }}</h2>
                                    @if ($community->privacy)
                                        <span class="text-sm border font-bold border-red-600 text-pastelRed bg-red-100 rounded-full px-3 py-1 font-light">
                                            private
                                        </span>
                                    @else
                                        <span class="text-sm border font-bold border-green-600 text-pastelGreen bg-green-100 rounded-full px-3 py-1 font-light">
                                            public
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <p class="text-gray-700 mb-4 font-light line-clamp-3">{{ $community->description }}</p>

                            <div class="flex justify-between items-center border-t border-gray-200 pt-4">
                                <span class="text-sm text-gray-500 font-light">
                                    created {{ $community->creation_date->diffForHumans() }}
                                </span>
                                <a href="{{ route('communities.show', $community->id) }}" class="text-pastelBlue hover:text-sky-600 font-medium transition-colors">
                                    view hub
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-6 py-6 flex justify-center">
                {{ $communities->appends(request()->query())->links('pagination::tailwind') }}
            </div>
        @endif
    </div>
</div>
@endsection
