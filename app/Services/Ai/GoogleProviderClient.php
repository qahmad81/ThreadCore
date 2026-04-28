<?php

namespace App\Services\Ai;

class GoogleProviderClient extends OpenAiProviderClient
{
    protected function chatPath(): string
    {
        return '/openai/chat/completions';
    }
}
