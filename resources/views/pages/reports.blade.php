@extends('layouts.app')

@section('content')

<h1>Reports</h1>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Reason</th>
                <th>Report Date</th>
                <th>Status</th>
                <th>Type</th>
                <th> User</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $report)
                <tr>
                    <td>{{ $report->id }}</td>
                    <td>{{ $report->reason }}</td>
                    <td>{{ $report->report_date }}</td>
                    <td>{{ $report->is_open ? 'Open' : 'Closed' }}</td>
                    <td>{{ $report->report_type }}</td>
                    <td>{{ $report->authenticated_user_id  }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No reports found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>


<div class="mt-3">
    {{ $reports->links() }}
</div>

@endsection
