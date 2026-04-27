<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Thread extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'customer_account_id',
        'api_key_id',
        'family_agent_id',
        'provider_id',
        'provider_model_id',
        'title',
        'input_tokens',
        'output_tokens',
        'max_context_tokens',
        'compacted_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
            'max_context_tokens' => 'integer',
            'compacted_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function familyAgent(): BelongsTo
    {
        return $this->belongsTo(FamilyAgent::class);
    }

    public function customerAccount(): BelongsTo
    {
        return $this->belongsTo(CustomerAccount::class);
    }

    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function providerModel(): BelongsTo
    {
        return $this->belongsTo(ProviderModel::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ThreadMessage::class);
    }
}
