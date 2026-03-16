<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

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
        // Only email at 75% and 100% — 50% is informational
        $channels = ['database'];
        if ($this->threshold >= 75) {
            $channels[] = 'mail';
        }
        return $channels;
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

    public function toMail($notifiable): MailMessage
    {
        $emoji = $this->threshold >= 100 ? '🚨' : '⚠️';

        return (new MailMessage)
            ->subject($emoji . ' Budget Alert - ' . $this->project->name . ' at ' . $this->threshold . '%')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A project has reached a budget threshold that requires your attention.')
            ->line('**Project:** ' . $this->project->name)
            ->line('**Budget Used:** ' . $this->threshold . '%')
            ->line('**Spent:** ₦' . number_format($this->spent, 2))
            ->line('**Total Budget:** ₦' . number_format($this->budget, 2))
            ->action('View Project', url(route('projects.show', $this->project)))
            ->salutation('- SitePro');
    }

}
