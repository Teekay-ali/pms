<?php

namespace App\Notifications;

use App\Models\Discussion;
use App\Models\DiscussionReply;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DiscussionReplied extends Notification
{
    use Queueable;

    public function __construct(
        public Discussion $discussion,
        public DiscussionReply $reply,
        public User $repliedBy
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type'    => 'discussion_replied',
            'title'   => 'New Reply on Your Discussion',
            'message' => "{$this->repliedBy->name} replied to \"{$this->discussion->title}\"",
            'url'     => route('discussions.show', $this->discussion),
            'meta'    => [
                'discussion_id'    => $this->discussion->id,
                'discussion_title' => $this->discussion->title,
                'replied_by'       => $this->repliedBy->name,
            ],
        ];
    }
}
