<?php


namespace App\Notifications;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProjectCreated extends Notification
{
    use Queueable;

    public function __construct(public Project $project, public User $createdBy)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'project_created',
            'title' => 'New Project Created',
            'message' => "\"{$this->project->name}\" was created by {$this->createdBy->name}",
            'url' => route('projects.show', $this->project),
            'meta' => [
                'project_id' => $this->project->id,
                'project_name' => $this->project->name,
                'created_by' => $this->createdBy->name,
            ],
        ];
    }
}
