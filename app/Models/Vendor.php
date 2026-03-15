<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Vendor extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'contact',
        'email',
        'phone',
        'type',
        'address',
        'hourly_rate', 'payment_terms', 'contract_expiry',
        'insurance_expiry', 'rating', 'notes',
    ];

    protected $casts = [
        'contract_expiry'  => 'date',
        'insurance_expiry' => 'date',
        'hourly_rate'      => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'status', 'budget', 'project_manager_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Project {$eventName}");
    }
}
