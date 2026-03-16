<?php


namespace App\Notifications;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ExpenseApproved extends Notification
{
    use Queueable;

    public function __construct(public Expense $expense, public User $approvedBy)
    {
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'expense_approved',
            'title' => 'Expense Approved',
            'message' => "Your expense of ₦{$this->expense->amount} for \"{$this->expense->project?->name}\" was approved by {$this->approvedBy->name}",
            'url' => route('expenses.index'),
            'meta' => [
                'expense_id' => $this->expense->id,
                'amount' => $this->expense->amount,
                'project_name' => $this->expense->project?->name,
                'approved_by' => $this->approvedBy->name,
            ],
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Expense Approved - ' . $this->expense->project->name)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your expense has been approved.')
            ->line('**Description:** ' . $this->expense->description)
            ->line('**Project:** ' . $this->expense->project->name)
            ->line('**Amount:** ₦' . number_format($this->expense->amount, 2))
            ->action('View Expenses', url(route('expenses.index')))
            ->salutation('- SitePro');
    }

}
