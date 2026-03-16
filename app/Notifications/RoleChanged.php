<?php


namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RoleChanged extends Notification
{
    use Queueable;

    public function __construct(
        public string $newRole,
        public string $assignedByName
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
            'type' => 'role_changed',
            'title' => 'Your Role Has Been Updated',
            'message' => 'Your role has been changed to ' . ucwords(str_replace('_', ' ', $this->newRole)),
            'url' => route('dashboard'),
            'meta' => [
                'new_role' => $this->newRole,
                'assigned_by' => $this->assignedByName,
            ],
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $roleLabel = ucwords(str_replace('_', ' ', $this->newRole));

        return (new MailMessage)
            ->subject('Your SitePro Role Has Been Updated')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your access role on SitePro has been updated.')
            ->line('**New Role:** ' . $roleLabel)
            ->line('**Updated by:** ' . $this->assignedByName)
            ->line('Your permissions have been updated accordingly. If you have questions, contact your administrator.')
            ->action('Go to Dashboard', url(route('dashboard')))
            ->salutation('- SitePro');
    }
}
