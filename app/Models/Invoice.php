<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'project_id', 'created_by', 'invoice_number',
        'client_name', 'client_email', 'client_address',
        'issue_date', 'due_date',
        'subtotal', 'tax_rate', 'tax_amount', 'total', 'amount_paid',
        'status', 'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'total', 'amount_paid'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $e) => "Invoice {$e}");
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable')->latest();
    }

    public function getBalanceAttribute(): float
    {
        return (float)$this->total - (float)$this->amount_paid;
    }

    public function recalculate(): void
    {
        $subtotal = $this->items()->sum(\DB::raw('quantity * unit_price'));
        $tax_amount = round($subtotal * ($this->tax_rate / 100), 2);
        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $tax_amount,
            'total' => $subtotal + $tax_amount,
        ]);
    }

    public function updateStatus(): void
    {
        if ($this->status === 'cancelled') return;

        $balance = $this->total - $this->amount_paid;

        $status = match (true) {
            $this->amount_paid <= 0 => (now()->gt($this->due_date) ? 'overdue' : 'sent'),
            $balance <= 0 => 'paid',
            $this->amount_paid > 0 && $balance > 0 => 'partially_paid',
            default => $this->status,
        };

        $this->update(['status' => $status]);
    }
}
