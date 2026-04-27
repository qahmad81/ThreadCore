<?php

namespace App\Services\Gateway;

use App\Models\FamilyAgent;
use App\Models\Thread;

class HistoryBuilder
{
    /**
     * @return array<int, array{role: string, content: string}>
     */
    public function build(FamilyAgent $familyAgent, Thread $thread, bool $skipMemory = false): array
    {
        $messages = [];

        if ($familyAgent->system_prompt) {
            $messages[] = ['role' => 'system', 'content' => $familyAgent->system_prompt];
        }

        if ($skipMemory) {
            return $messages;
        }

        foreach ($thread->messages()->oldest()->get() as $message) {
            if ($message->is_forgotten || ($message->is_compacted && ! $message->is_memory)) {
                continue;
            }

            $messages[] = [
                'role' => $message->role === 'assistant' ? 'assistant' : 'user',
                'content' => $message->content,
            ];
        }

        return $messages;
    }
}
