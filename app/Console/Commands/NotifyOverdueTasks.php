<?php


namespace App\Console\Commands;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskOverdue;
use Illuminate\Console\Command;

class NotifyOverdueTasks extends Command
{
    protected $signature = 'tasks:notify-overdue';
    protected $description = 'Send notifications for overdue tasks';

    public function handle(): void
    {
        $overdueTasks = Task::with(['project', 'assignee'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', today())
            ->whereNotIn('status', ['completed'])
            ->get();

        foreach ($overdueTasks as $task) {
            $recipients = collect();

            if ($task->assignee) {
                $recipients->push($task->assignee);
            }

            if ($task->project?->project_manager_id) {
                $manager = User::find($task->project->project_manager_id);
                if ($manager && $manager->id !== $task->assigned_to) {
                    $recipients->push($manager);
                }
            }

            foreach ($recipients as $user) {
                // Avoid duplicate notifications — only notify once per day per task
                $alreadyNotified = $user->notifications()
                    ->whereDate('created_at', today())
                    ->where('type', TaskOverdue::class)
                    ->whereJsonContains('data->meta->task_id', $task->id)
                    ->exists();

                if (!$alreadyNotified) {
                    $user->notify(new TaskOverdue($task));
                }
            }
        }

        $this->info("Processed {$overdueTasks->count()} overdue tasks.");
    }
}
