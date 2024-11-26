@extends('layouts.app')

@section('content')
<div class="container-fluid bg-light py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2 font-weight-bold">My Communities</h1>
            <a href="{{ route('communities.create') }}" class="btn btn-primary">Create Community</a>
        </div>

        @if ($communities->isEmpty())
        <div class="alert alert-info">You haven't created any communities yet.</div>
        @else
        <div class="row">
            @foreach ($communities as $community)
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">{{ $community->name }}</h5>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Actions
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="{{ route('communities.show', $community->id) }}">View Community</a>
                                </div>
                            </div>
                        </div>
                        <p class="card-text text-muted">{{ $community->description }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">{{ $community->created_at }}</span>
                            <span class="badge badge-pill badge-{{ $community->privacy === 'public' ? 'success' : 'danger' }}">{{ ucfirst($community->privacy) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection