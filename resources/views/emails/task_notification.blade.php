<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .header { background: #f4f6f9; padding: 20px; text-align: center; border-bottom: 3px solid #007bff; }
        .content { padding: 20px; }
        .footer { font-size: 12px; color: #777; text-align: center; padding: 20px; background: #f4f6f9; }
        .btn { background: #007bff; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
        .status-badge { background: #eee; padding: 5px 10px; border-radius: 4px; font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        <h2>
            @if($type === 'created') New Task Submitted
            @elseif($type === 'assigned') You have a new Assignment
            @elseif($type === 'status') Task Status Updated
            @elseif($type === 'message') New Message Received
            @endif
        </h2>
    </div>

    <div class="content">
        <p><strong>Task Reference:</strong> {{ $task->task_number }}</p>
        <p><strong>Client:</strong> {{ $task->client->business_name }}</p>
        
        @if($type === 'status')
            <p>The status of this task has been updated to: <span class="status-badge">{{ $task->status }}</span></p>
        @endif

        @if($customMessage)
            <div style="background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 15px 0;">
                <strong>Note/Message:</strong><br>
                {{ $customMessage }}
            </div>
        @endif

        <p><strong>Description:</strong> {{ Str::limit($task->description, 100) }}</p>

        <br>
        <div style="text-align: center;">
            {{-- Determine Link based on who is receiving (Logic handled in controller, generic link here) --}}
            <a href="{{ route('login') }}" class="btn">View Task Details</a>
        </div>
    </div>

    <div class="footer">
        Automated Notification System
    </div>

</body>
</html>