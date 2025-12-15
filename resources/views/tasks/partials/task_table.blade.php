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
                    // CSS Class based on PDF Legend (Background Color)
                    $rowClass = '';
                    if($task->status == 'Pending') $rowClass = 'status-pending'; // Red-ish
                    elseif($task->status == 'In Progress') $rowClass = 'status-progress'; // Blue-ish
                    elseif($task->status == 'Completed') $rowClass = 'status-completed'; // Yellow-ish
                    elseif($task->status == 'Closed') $rowClass = 'status-closed'; // Green-ish
                    
                    // Note: We don't override the whole row color for overdue anymore based on your new design,
                    // we just add the tag. But if you want the red border/text from before:
                    // if($task->isOverdue()) $rowClass .= ' status-overdue'; 
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
                    </td>
                    
                    {{-- ✅ UPDATED STATUS COLUMN --}}
                    <td>
                        <span class="font-weight-bold">{{ $task->status }}</span>
                        @if($task->isOverdue())
                            <br>
                            <span class="badge badge-danger mt-1" style="font-size: 10px;">
                                <i class="fas fa-exclamation-triangle"></i> OVERDUE
                            </span>
                        @endif
                    </td>

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