<?php

namespace App\Services\Gateway;

class CommandParser
{
    public function parse(string $content): ?string
    {
        $first = strtolower(strtok(trim($content), " \n\t") ?: '');

        return in_array($first, ['/dayend', '/whisper', '/skip', '/forget'], true) ? ltrim($first, '/') : null;
    }

    public function withoutCommand(string $content): string
    {
        if (! $this->parse($content)) {
            return $content;
        }

        return trim((string) preg_replace('/^\/[a-z]+/i', '', trim($content), 1));
    }
}
