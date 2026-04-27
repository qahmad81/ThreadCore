<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CustomerAccount extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'status', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->where('status', 'active')->latestOfMany();
    }

    public function threads(): HasMany
    {
        return $this->hasMany(Thread::class);
    }
}
