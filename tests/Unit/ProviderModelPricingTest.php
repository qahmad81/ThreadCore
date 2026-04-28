<?php

namespace Tests\Unit;

use App\Models\ProviderModel;
use PHPUnit\Framework\TestCase;

class ProviderModelPricingTest extends TestCase
{
    public function test_cost_for_usage_avoids_double_charging_prompt_and_reasoning_breakdowns(): void
    {
        $model = new ProviderModel();
        $model->pricing = [
            'prompt_tokens' => 0.000001,
            'prompt_cache_hit_tokens' => 0.00000025,
            'prompt_cache_miss_tokens' => 0.000001,
            'completion_tokens' => 0.000002,
            'reasoning_tokens' => 0.000003,
        ];

        $cost = $model->costForUsage([
            'prompt_tokens' => 825,
            'completion_tokens' => 1024,
            'prompt_cache_hit_tokens' => 100,
            'prompt_cache_miss_tokens' => 725,
            'reasoning_tokens' => 842,
            'has_prompt_cache_breakdown' => true,
            'has_reasoning_breakdown' => true,
        ]);

        $this->assertSame('0.003640', $cost);
    }

    public function test_cost_for_usage_uses_plain_prompt_pricing_when_cache_breakdown_is_absent(): void
    {
        $model = new ProviderModel();
        $model->pricing = [
            'prompt_tokens' => 0.000001,
            'prompt_cache_hit_tokens' => 0.00000025,
            'prompt_cache_miss_tokens' => 0.000001,
            'completion_tokens' => 0.000002,
        ];

        $cost = $model->costForUsage([
            'prompt_tokens' => 100,
            'completion_tokens' => 50,
            'prompt_cache_hit_tokens' => 0,
            'prompt_cache_miss_tokens' => 0,
            'reasoning_tokens' => 0,
            'has_prompt_cache_breakdown' => false,
            'has_reasoning_breakdown' => false,
        ]);

        $this->assertSame('0.000200', $cost);
    }

    public function test_cost_for_usage_defaults_to_zero_without_pricing(): void
    {
        $model = new ProviderModel();

        $this->assertSame(
            '0.000000',
            $model->costForUsage([
                'prompt_tokens' => 100,
                'completion_tokens' => 50,
                'prompt_cache_hit_tokens' => 0,
                'prompt_cache_miss_tokens' => 0,
                'reasoning_tokens' => 0,
                'has_prompt_cache_breakdown' => false,
                'has_reasoning_breakdown' => false,
            ]),
        );
    }
}
