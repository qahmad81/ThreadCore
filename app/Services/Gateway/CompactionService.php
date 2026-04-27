<?php

namespace App\Services\Gateway;

use App\Models\Thread;

class CompactionService
{
    public function compact(Thread $thread, bool $force = false): bool
    {
        $family = $thread->familyAgent;
        $total = $thread->input_tokens + $thread->output_tokens;

        if (! $force && $total < $family->compaction_threshold_tokens) {
            return false;
        }

        $rawMessages = $thread->messages()
            ->where('is_compacted', false)
            ->where('is_memory', false)
            ->where('is_forgotten', false)
            ->oldest()
            ->get();

        if ($rawMessages->count() < 2) {
            return false;
        }

        $summary = $rawMessages
            ->take(12)
            ->map(fn ($message) => strtoupper($message->role).': '.mb_substr($message->content, 0, 300))
            ->implode("\n");

        $thread->messages()->create([
            'role' => 'system',
            'content' => "Compacted memory:\n".$summary,
            'is_compacted' => true,
            'is_memory' => true,
            'metadata' => ['generated_by' => 'local_compaction_v1'],
        ]);

        $thread->messages()
            ->whereIn('id', $rawMessages->pluck('id'))
            ->update(['is_compacted' => true]);

        $thread->forceFill(['compacted_at' => now()])->save();

        return true;
    }

    public function forget(Thread $thread, string $needle): int
    {
        if ($needle === '') {
            return 0;
        }

        return $thread->messages()
            ->where('content', 'like', '%'.$needle.'%')
            ->update(['is_forgotten' => true]);
    }
}
