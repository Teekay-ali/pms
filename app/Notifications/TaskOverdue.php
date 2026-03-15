<?php


namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskOverdue extends Notification
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
            'type' => 'task_overdue',
            'title' => 'Task Overdue',
            'message' => "Task \"{$this->task->name}\" is overdue (due {$this->task->due_date->toFormattedDateString()})",
            'url' => route('tasks.index'),
            'meta' => [
                'task_id' => $this->task->id,
                'task_name' => $this->task->name,
                'project_name' => $this->task->project?->name,
                'due_date' => $this->task->due_date->toDateString(),
            ],
        ];
    }
}
