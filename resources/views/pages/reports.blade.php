@extends('layouts.app')
@section('content')
<div class="text-gray-900 bg-[#F5F5F0] min-h-screen">
    <div class="container mx-auto px-4">
        <h1 class="text-5xl text-gray-800 tracking-tighter font-medium py-4">Reports</h1>

        <div class="table-responsive">
            @if($reports->isEmpty())
                <div class="bg-white p-8 rounded-lg shadow-md text-center border border-gray-200">
                    <p class="text-gray-600 text-xl font-light">No reports found.</p>
                </div>
            @else
                <table class="w-full bg-white border border-gray-200 rounded-lg overflow-hidden">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Report Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($reports as $report)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-4 whitespace-nowrap">{{ $report->id }}</td>
                            <td class="px-4 py-4">{{ $report->reason }}</td>
                            <td class="px-4 py-4">{{ $report->report_date }}</td>
                            <td class="px-4 py-4">
                                <span class="{{ $report->is_open ? 'text-green-600 bg-green-100' : 'text-red-600 bg-red-100' }} text-sm border rounded-full px-3 py-1 font-bold">
                                    {{ $report->is_open ? 'Open' : 'Closed' }}
                                </span>
                            </td>
                            <td class="px-4 py-4">{{ str_replace('_', ' ', $report->report_type->value) }}</td>
                            <td class="px-4 py-4">{{ $report->authenticated_user_id }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="mt-6 py-6 flex justify-center">
            {{ $reports->appends(request()->query())->links('pagination::custom-pagination') }}
        </div>
    </div>
</div>
@endsection