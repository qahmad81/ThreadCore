<?php

namespace App\Services\Gateway;

use App\Models\Thread;
use Illuminate\Support\Str;

class ThreadMarkdownExporter
{
    public function filename(Thread $thread): string
    {
        $base = $thread->title ? Str::slug($thread->title) : '';
        $base = filled($base) ? $base : $thread->public_id;

        return $base.'.md';
    }

    public function render(Thread $thread): string
    {
        $lines = [];

        $lines[] = '# Thread Conversation';
        $lines[] = '';
        $lines[] = '- Title: '.($thread->title ?: 'Untitled');
        $lines[] = '- Public ID: '.$thread->public_id;
        $lines[] = '';
        $lines[] = '## Messages';

        foreach ($thread->messages as $message) {
            $lines[] = '';
            $lines[] = '### '.ucfirst($message->role);
            $lines[] = $message->content;
        }

        return implode("\n", $lines)."\n";
    }
}
