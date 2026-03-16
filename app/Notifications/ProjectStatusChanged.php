<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ProjectStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public Project $project,
        public string  $oldStatus,
        public User    $changedBy
    )
    {
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'project_status_changed',
            'title' => 'Project Status Updated',
            'message' => "\"{$this->project->name}\" status changed from {$this->oldStatus} to {$this->project->status}",
            'url' => route('projects.show', $this->project),
            'meta' => [
                'project_id' => $this->project->id,
                'project_name' => $this->project->name,
                'old_status' => $this->oldStatus,
                'new_status' => $this->project->status,
                'changed_by' => $this->changedBy->name,
            ],
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Project Status Updated - ' . $this->project->name)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A project status has been updated.')
            ->line('**Project:** ' . $this->project->name)
            ->line('**Previous Status:** ' . ucfirst($this->oldStatus))
            ->line('**New Status:** ' . ucfirst($this->project->status))
            ->action('View Project', url(route('projects.show', $this->project)))
            ->salutation('- SitePro');
    }
}
