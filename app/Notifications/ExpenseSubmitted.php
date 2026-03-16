<?php


namespace App\Notifications;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ExpenseSubmitted extends Notification
{
    use Queueable;

    public function __construct(public Expense $expense, public User $submittedBy)
    {
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'expense_submitted',
            'title' => 'Expense Awaiting Approval',
            'message' => "{$this->submittedBy->name} submitted an expense of ₦{$this->expense->amount} for \"{$this->expense->project?->name}\"",
            'url' => route('expenses.index'),
            'meta' => [
                'expense_id' => $this->expense->id,
                'amount' => $this->expense->amount,
                'project_name' => $this->expense->project?->name,
                'submitted_by' => $this->submittedBy->name,
            ],
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Expense Pending Approval - ' . $this->expense->project->name)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new expense has been submitted and requires your approval.')
            ->line('**Description:** ' . $this->expense->description)
            ->line('**Project:** ' . $this->expense->project->name)
            ->line('**Amount:** ₦' . number_format($this->expense->amount, 2))
            ->line('**Submitted by:** ' . $this->expense->creator?->name)
            ->action('Review Expense', url(route('expenses.index')))
            ->salutation('- SitePro');
    }
}
