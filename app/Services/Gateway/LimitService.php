<?php

namespace App\Services\Gateway;

use App\Models\CustomerAccount;
use RuntimeException;

class LimitService
{
    public function assertCanUse(CustomerAccount $account, int $estimatedTokens): void
    {
        $subscription = $account->activeSubscription;
        $plan = $subscription?->plan;

        if (! $subscription || ! $plan || $subscription->status !== 'active') {
            throw new RuntimeException('No active subscription is available for this customer.');
        }

        if ($subscription->requests_used + 1 > $plan->monthly_request_limit) {
            throw new RuntimeException('Monthly request limit reached.');
        }

        if ($subscription->tokens_used + $estimatedTokens > $plan->monthly_token_limit) {
            throw new RuntimeException('Monthly token limit reached.');
        }
    }

    public function recordUsage(CustomerAccount $account, int $tokens): void
    {
        $subscription = $account->activeSubscription;

        if ($subscription) {
            $subscription->increment('requests_used');
            $subscription->increment('tokens_used', $tokens);
        }
    }
}
