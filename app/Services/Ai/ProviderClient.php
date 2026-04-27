<?php

namespace App\Services\Ai;

use App\Models\Provider;
use App\Models\ProviderModel;

interface ProviderClient
{
    /**
     * @param array<int, array{role: string, content: string}> $messages
     */
    public function chat(Provider $provider, ProviderModel $model, array $messages, array $options = []): AiResponse;
}
