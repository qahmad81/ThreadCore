<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FamilyAgent extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'name',
        'description',
        'system_prompt',
        'default_provider_id',
        'default_provider_model_id',
        'max_context_tokens',
        'compaction_threshold_tokens',
        'is_enabled',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'max_context_tokens' => 'integer',
            'compaction_threshold_tokens' => 'integer',
            'is_enabled' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function defaultProvider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'default_provider_id');
    }

    public function defaultProviderModel(): BelongsTo
    {
        return $this->belongsTo(ProviderModel::class, 'default_provider_model_id');
    }

    public function threads(): HasMany
    {
        return $this->hasMany(Thread::class);
    }
}
