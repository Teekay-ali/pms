<?php


namespace App\Notifications;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AddedToProject extends Notification
{
    use Queueable;

    public function __construct(public Project $project, public User $addedBy)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'added_to_project',
            'title' => 'Added to Project',
            'message' => "You were added to project \"{$this->project->name}\" by {$this->addedBy->name}",
            'url' => route('projects.show', $this->project),
            'meta' => [
                'project_id' => $this->project->id,
                'project_name' => $this->project->name,
                'added_by' => $this->addedBy->name,
            ],
        ];
    }
}
