<?php


namespace App\Notifications;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ExpenseApproved extends Notification
{
    use Queueable;

    public function __construct(public Expense $expense, public User $approvedBy)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
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
}
