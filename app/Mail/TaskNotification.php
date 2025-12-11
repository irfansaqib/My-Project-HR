<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Task;

class TaskNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $task;
    public $type; // 'created', 'assigned', 'status', 'message'
    public $customMessage;

    public function __construct(Task $task, $type, $customMessage = null)
    {
        $this->task = $task;
        $this->type = $type;
        $this->customMessage = $customMessage;
    }

    public function build()
    {
        $subject = 'Task Update: ' . $this->task->task_number;

        if ($this->type === 'created') $subject = 'New Task Request: ' . $this->task->task_number;
        if ($this->type === 'assigned') $subject = 'New Assignment: ' . $this->task->task_number;
        if ($this->type === 'status') $subject = 'Status Update: ' . $this->task->task_number;
        if ($this->type === 'message') $subject = 'New Message on Task: ' . $this->task->task_number;

        return $this->subject($subject)
                    ->view('emails.task_notification');
    }
}