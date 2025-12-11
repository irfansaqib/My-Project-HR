@extends('layouts.admin')
@section('title', 'Recurring Task Profiles')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="m-0 font-weight-bold text-info"><i class="fas fa-sync-alt mr-2"></i> Recurring Automation Rules</h5>
        <a href="{{ route('recurring-tasks.create') }}" class="btn btn-info btn-sm shadow-sm">
            <i class="fas fa-plus-circle mr-1"></i> New Recurring Rule
        </a>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 text-nowrap">
                <thead class="thead-light">
                    <tr>
                        <th>Client</th>
                        <th>Service / Category</th>
                        <th>Assigned To</th>
                        <th>Frequency Rule</th>
                        <th>Last Run</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($profiles as $profile)
                    <tr>
                        <td>
                            <strong>{{ $profile->client->business_name }}</strong><br>
                            <small class="text-muted">{{ $profile->client->contact_person }}</small>
                        </td>
                        <td>
                            {{ $profile->category->name }}
                            <br>
                            <small class="text-muted">{{ $profile->description }}</small>
                        </td>
                        <td>
                            @if($profile->assignedEmployee)
                                <span class="badge badge-light border">{{ $profile->assignedEmployee->name }}</span>
                            @else
                                <span class="text-danger small">Unassigned</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-info">{{ $profile->frequency }}</span>
                            <small class="d-block mt-1 text-muted">
                                @if($profile->frequency == 'Daily')
                                    {{ \Carbon\Carbon::parse($profile->start_time)->format('h:i A') }}
                                @elseif($profile->frequency == 'Weekly')
                                    Every {{ $profile->day_of_week }}
                                @elseif($profile->frequency == 'Monthly')
                                    {{ $profile->month_start_day }}<sup>th</sup> to {{ $profile->month_end_day }}<sup>th</sup>
                                @elseif($profile->frequency == 'Annually')
                                    {{ $profile->annual_start_date ? $profile->annual_start_date->format('d-M') : 'N/A' }}
                                @endif
                            </small>
                        </td>
                        <td>
                            @if($profile->last_run_at)
                                {{ $profile->last_run_at->format('d M, Y') }}
                            @else
                                <span class="text-muted small">Never</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{ $profile->status == 'Active' ? 'success' : 'secondary' }}">
                                {{ $profile->status }}
                            </span>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('recurring-tasks.edit', $profile->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('recurring-tasks.destroy', $profile->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure? This will stop future task generation.');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-robot fa-3x mb-3 text-secondary"></i><br>
                            No recurring rules found. <a href="{{ route('recurring-tasks.create') }}">Create your first automation</a>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        {{ $profiles->links() }}
    </div>
</div>
@endsection