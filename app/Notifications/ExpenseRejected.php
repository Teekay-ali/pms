<?php


namespace App\Notifications;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ExpenseRejected extends Notification
{
    use Queueable;

    public function __construct(public Expense $expense, public User $rejectedBy)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'expense_rejected',
            'title' => 'Expense Rejected',
            'message' => "Your expense of \${$this->expense->amount} for \"{$this->expense->project?->name}\" was rejected by {$this->rejectedBy->name}",
            'url' => route('expenses.index'),
            'meta' => [
                'expense_id' => $this->expense->id,
                'amount' => $this->expense->amount,
                'project_name' => $this->expense->project?->name,
                'rejected_by' => $this->rejectedBy->name,
            ],
        ];
    }
}
