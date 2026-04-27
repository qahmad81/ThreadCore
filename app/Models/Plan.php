<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'monthly_request_limit', 'monthly_token_limit', 'is_active', 'metadata'];

    protected function casts(): array
    {
        return [
            'monthly_request_limit' => 'integer',
            'monthly_token_limit' => 'integer',
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
