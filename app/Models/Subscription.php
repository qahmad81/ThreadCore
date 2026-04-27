<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_account_id',
        'plan_id',
        'status',
        'current_period_starts_at',
        'current_period_ends_at',
        'requests_used',
        'tokens_used',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'current_period_starts_at' => 'datetime',
            'current_period_ends_at' => 'datetime',
            'requests_used' => 'integer',
            'tokens_used' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function customerAccount(): BelongsTo
    {
        return $this->belongsTo(CustomerAccount::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
