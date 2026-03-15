<?php


namespace App\Notifications;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ExpenseSubmitted extends Notification
{
    use Queueable;

    public function __construct(public Expense $expense, public User $submittedBy)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
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
}
