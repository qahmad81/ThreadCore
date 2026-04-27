<?php

namespace App\Services\Gateway;

class TokenEstimator
{
    public function estimate(string $content): int
    {
        return max(1, (int) ceil(mb_strlen($content) / 4));
    }
}
