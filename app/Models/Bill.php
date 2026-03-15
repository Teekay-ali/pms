<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Bill extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'project_id', 'vendor_id', 'created_by', 'approved_by',
        'bill_number', 'reference', 'description',
        'amount', 'amount_paid', 'issue_date', 'due_date',
        'status', 'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'amount', 'amount_paid'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $e) => "Bill {$e}");
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable')->latest();
    }

    public function getBalanceAttribute(): float
    {
        return (float)$this->amount - (float)$this->amount_paid;
    }

    public function updateStatus(): void
    {
        if (in_array($this->status, ['cancelled', 'draft'])) return;

        $balance = $this->amount - $this->amount_paid;

        $status = match (true) {
            $this->amount_paid <= 0 => (now()->gt($this->due_date) ? 'overdue' : 'pending'),
            $balance <= 0 => 'paid',
            $this->amount_paid > 0 && $balance > 0 => 'partially_paid',
            default => $this->status,
        };

        $this->update(['status' => $status]);
    }
}
