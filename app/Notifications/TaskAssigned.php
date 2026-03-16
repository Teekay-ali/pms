<?php


namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TaskAssigned extends Notification
{
    use Queueable;

    public function __construct(public Task $task)
    {
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
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

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Task Assigned - ' . $this->task->name)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('You have been assigned a new task.')
            ->line('**Task:** ' . $this->task->name)
            ->line('**Project:** ' . ($this->task->project?->name ?? '—'))
            ->line('**Priority:** ' . ucfirst($this->task->priority))
            ->action('View Tasks', url(route('tasks.index')))
            ->salutation('- SitePro');
    }
}
