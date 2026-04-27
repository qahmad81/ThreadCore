<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GatewayRequestLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_account_id',
        'api_key_id',
        'thread_id',
        'provider_id',
        'provider_model_id',
        'status',
        'input_tokens',
        'output_tokens',
        'request_payload',
        'response_metadata',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
            'request_payload' => 'array',
            'response_metadata' => 'array',
        ];
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }
}
