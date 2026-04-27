<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThreadMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'thread_id',
        'role',
        'content',
        'input_tokens',
        'output_tokens',
        'is_compacted',
        'is_memory',
        'command',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
            'is_compacted' => 'boolean',
            'is_memory' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }
}
