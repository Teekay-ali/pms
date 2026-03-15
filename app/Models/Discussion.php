<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Discussion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'project_id', 'category_id',
        'title', 'body', 'is_pinned', 'is_locked',
        'views', 'replies_count', 'last_activity_at',
    ];

    protected $casts = [
        'is_pinned'          => 'boolean',
        'is_locked'          => 'boolean',
        'last_activity_at'   => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DiscussionCategory::class, 'category_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(DiscussionReply::class)->latest();
    }

    public function reactions(): MorphMany
    {
        return $this->morphMany(DiscussionReaction::class, 'reactable');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable')->latest();
    }
}
