<?php


namespace App\Notifications;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ExpenseRejected extends Notification
{
    use Queueable;

    public function __construct(public Expense $expense, public User $rejectedBy)
    {
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'expense_rejected',
            'title' => 'Expense Rejected',
            'message' => "Your expense of ₦{$this->expense->amount} for \"{$this->expense->project?->name}\" was rejected by {$this->rejectedBy->name}",
            'url' => route('expenses.index'),
            'meta' => [
                'expense_id' => $this->expense->id,
                'amount' => $this->expense->amount,
                'project_name' => $this->expense->project?->name,
                'rejected_by' => $this->rejectedBy->name,
            ],
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Expense Rejected - ' . $this->expense->project->name)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Unfortunately your expense has been rejected.')
            ->line('**Description:** ' . $this->expense->description)
            ->line('**Project:** ' . $this->expense->project->name)
            ->line('**Amount:** ₦' . number_format($this->expense->amount, 2))
            ->action('View Expenses', url(route('expenses.index')))
            ->salutation('- SitePro');
    }
}
