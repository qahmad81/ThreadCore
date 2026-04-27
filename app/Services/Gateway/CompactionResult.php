<?php

namespace App\Services\Gateway;

final readonly class CompactionResult
{
    public function __construct(
        public bool $triggered = false,
        public int $inputTokens = 0,
        public int $outputTokens = 0,
        public ?int $providerId = null,
        public ?int $providerModelId = null,
        public ?string $providerSlug = null,
        public ?string $modelKey = null,
    ) {
    }
}
