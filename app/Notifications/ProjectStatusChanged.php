<?php


namespace App\Notifications;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

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
        return ['database'];
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
}
