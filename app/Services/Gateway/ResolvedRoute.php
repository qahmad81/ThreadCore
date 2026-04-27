<?php

namespace App\Services\Gateway;

use App\Models\Provider;
use App\Models\ProviderModel;

class ResolvedRoute
{
    public function __construct(
        public readonly Provider $provider,
        public readonly ProviderModel $model,
    ) {
    }
}
