<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'name',
        'model_key',
        'role',
        'context_window',
        'is_enabled',
        'is_default',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'context_window' => 'integer',
            'is_enabled' => 'boolean',
            'is_default' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
