<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'driver',
        'base_url',
        'api_key_env',
        'is_enabled',
        'is_default',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'is_default' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function models(): HasMany
    {
        return $this->hasMany(ProviderModel::class);
    }
}
