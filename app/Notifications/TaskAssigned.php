<?php


namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskAssigned extends Notification
{
    use Queueable;

    public function __construct(public Task $task)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'task_assigned',
            'title' => 'Task Assigned',
            'message' => "You have been assigned to \"{$this->task->name}\"",
            'url' => route('tasks.index'),
            'meta' => [
                'task_id' => $this->task->id,
                'task_name' => $this->task->name,
                'project_name' => $this->task->project?->name,
                'priority' => $this->task->priority,
            ],
        ];
    }
}
