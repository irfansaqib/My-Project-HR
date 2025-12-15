<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAlert extends Notification
{
    use Queueable;

    public $task;
    public $type; // 'assigned', 'message', 'status_change', etc.
    public $content;

    public function __construct($task, $type, $content = '')
    {
        $this->task = $task;
        $this->type = $type;
        $this->content = $content;
    }

    // ✅ Send via both Database (Bell Icon) and Email
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    // ✅ Design the Email
    public function toMail($notifiable)
    {
        $url = route('tasks.show', $this->task->id);

        return (new MailMessage)
                    ->subject('Task Update: ' . $this->task->task_number)
                    ->line('Update on Task: ' . $this->task->task_number)
                    ->line($this->content)
                    ->action('View Task', $url);
    }

    // ✅ Design the Database Alert (For Bell Icon)
    public function toArray($notifiable)
    {
        return [
            'task_id' => $this->task->id,
            'task_number' => $this->task->task_number,
            'type' => $this->type,
            'message' => $this->content,
            'url' => route('tasks.show', $this->task->id),
            'created_by' => auth()->user()->name ?? 'System',
        ];
    }
}