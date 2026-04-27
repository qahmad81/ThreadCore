<?php

namespace App\Services\Ai;

class AiResponse
{
    public function __construct(
        public readonly string $content,
        public readonly int $inputTokens,
        public readonly int $outputTokens,
        public readonly ?string $finishReason = null,
        public readonly array $metadata = [],
    ) {
    }
}
