<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_account_id',
        'name',
        'prefix',
        'token_hash',
        'last_used_at',
        'revoked_at',
        'scopes',
    ];

    protected function casts(): array
    {
        return [
            'last_used_at' => 'datetime',
            'revoked_at' => 'datetime',
            'scopes' => 'array',
        ];
    }

    public static function makePlainToken(): string
    {
        return 'tc_live_'.Str::random(48);
    }

    public static function hashToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    public function customerAccount(): BelongsTo
    {
        return $this->belongsTo(CustomerAccount::class);
    }

    public function isActive(): bool
    {
        return $this->revoked_at === null && $this->customerAccount?->status === 'active';
    }
}
