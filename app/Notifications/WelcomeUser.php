<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeUser extends Notification
{
    use Queueable;

    public function __construct(
        public string $plainPassword
    ) {}

    public function via($notifiable): array
    {
        return ['mail']; // no need to store this in DB
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to SitePro - Your Account is Ready')
            ->greeting('Welcome, ' . $notifiable->name . '!')
            ->line('Your SitePro account has been created. Here are your login details:')
            ->line('**Email:** ' . $notifiable->email)
            ->line('**Password:** ' . $this->plainPassword)
            ->line('For security, please change your password after your first login.')
            ->action('Login to SitePro', url(route('login')))
            ->salutation('- SitePro');
    }
}
