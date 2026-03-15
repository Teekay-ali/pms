<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class DailyLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'logged_by',
        'date',
        'weather',
        'temperature',
        'weather_icon',
        'work_performed',
        'workers_on_site',
        'equipment_used',
        'delays_issues',
    ];

    protected $casts = [
        'date'        => 'date',
        'temperature' => 'decimal:1',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function logger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable')->latest();
    }
}
