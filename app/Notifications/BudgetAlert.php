<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BudgetAlert extends Notification
{
    use Queueable;

    public function __construct(
        public Project $project,
        public int $threshold,
        public float $spent,
        public float $budget
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $pct = $this->threshold;
        return [
            'type'    => 'budget_alert',
            'title'   => "Budget Alert - {$pct}% Used",
            'message' => "\"{$this->project->name}\" has used {$pct}% of its budget (\${$this->spent} of \${$this->budget})",
            'url'     => route('projects.show', $this->project),
            'meta'    => [
                'project_id'   => $this->project->id,
                'project_name' => $this->project->name,
                'threshold'    => $pct,
                'spent'        => $this->spent,
                'budget'       => $this->budget,
            ],
        ];
    }
}
