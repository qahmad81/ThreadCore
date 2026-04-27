<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SitePage extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'headline',
        'summary',
        'blocks',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'blocks' => 'array',
            'is_published' => 'boolean',
        ];
    }
}
