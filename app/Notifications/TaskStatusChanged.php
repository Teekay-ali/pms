<?php


namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public Task   $task,
        public string $oldStatus,
        public User   $changedBy
    )
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'task_status_changed',
            'title' => 'Task Status Updated',
            'message' => "\"{$this->task->name}\" status changed from {$this->oldStatus} to {$this->task->status}",
            'url' => route('tasks.index'),
            'meta' => [
                'task_id' => $this->task->id,
                'task_name' => $this->task->name,
                'project_name' => $this->task->project?->name,
                'old_status' => $this->oldStatus,
                'new_status' => $this->task->status,
                'changed_by' => $this->changedBy->name,
            ],
        ];
    }
}
