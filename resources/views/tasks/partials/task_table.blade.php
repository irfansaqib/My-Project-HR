<div class="table-responsive">
    <table class="table table-bordered table-hover mb-0 text-nowrap">
        <thead class="thead-light">
            <tr>
                <th>Task #</th>
                <th>Client</th>
                <th>Category</th>
                <th>Description</th>
                <th>Assigned Date</th>
                <th>Due Date</th>
                <th>Status</th>
                <th class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tasks as $task)
                @php
                    // Determine CSS Class based on PDF Legend
                    $rowClass = '';
                    if($task->status == 'Pending') $rowClass = 'status-pending';
                    elseif($task->status == 'In Progress') $rowClass = 'status-progress';
                    elseif($task->status == 'Completed') $rowClass = 'status-completed';
                    elseif($task->status == 'Closed') $rowClass = 'status-closed';
                    
                    if($task->isOverdue()) $rowClass .= ' status-overdue';
                @endphp

                <tr class="{{ $rowClass }}">
                    <td class="font-weight-bold">{{ $task->task_number }}</td>
                    <td>{{ $task->client->business_name }}</td>
                    <td>
                        <small>{{ $task->category->parent->name ?? '' }}</small><br>
                        <strong>{{ $task->category->name }}</strong>
                    </td>
                    <td>{{ \Illuminate\Support\Str::limit($task->description, 40) }}</td>
                    <td>{{ $task->created_at->format('d-M-y') }}</td>
                    <td>
                        {{ $task->due_date ? $task->due_date->format('d-M-y') : 'N/A' }}
                        @if($task->isOverdue()) <i class="fas fa-exclamation-circle text-danger ml-1" title="Overdue"></i> @endif
                    </td>
                    <td class="font-weight-bold">{{ $task->status }}</td>
                    <td class="text-center bg-white">
                        <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>