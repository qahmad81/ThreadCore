<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('providers') || ! Schema::hasTable('provider_models')) {
            return;
        }

        $now = now();

        foreach ($this->providers() as $provider) {
            DB::table('providers')->updateOrInsert(
                ['slug' => $provider['slug']],
                [
                    'name' => $provider['name'],
                    'driver' => $provider['driver'],
                    'base_url' => $provider['base_url'],
                    'api_key_env' => $provider['api_key_env'],
                    'is_enabled' => false,
                    'is_default' => $provider['is_default'],
                    'metadata' => json_encode($provider['metadata'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }

        $providerIds = DB::table('providers')->pluck('id', 'slug');

        foreach ($this->models() as $row) {
            $providerId = $providerIds[$row['provider_slug']] ?? null;

            if (! $providerId) {
                continue;
            }

            DB::table('provider_models')->updateOrInsert(
                [
                    'provider_id' => $providerId,
                    'model_key' => $row['model_key'],
                ],
                [
                    'name' => $row['name'],
                    'role' => $row['role'],
                    'context_window' => $row['context_window'],
                    'is_enabled' => false,
                    'is_default' => $row['is_default'],
                    'metadata' => json_encode($row['metadata'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    'pricing' => json_encode($row['pricing'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('providers')) {
            DB::table('providers')->whereIn('slug', array_column($this->providers(), 'slug'))->delete();
        }
    }

    private function providers(): array
    {
        return [
            ['name' => 'OpenRouter', 'slug' => 'openrouter', 'driver' => 'openai', 'base_url' => 'https://openrouter.ai/api/v1', 'api_key_env' => 'OPENROUTER_API_KEY', 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/providers.md', 'legacy_driver_alias' => 'openrouter']],
            ['name' => 'OpenAI', 'slug' => 'openai', 'driver' => 'openai', 'base_url' => 'https://api.openai.com/v1', 'api_key_env' => 'OPENAI_API_KEY', 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/providers.md']],
            ['name' => 'Anthropic', 'slug' => 'anthropic', 'driver' => 'anthropic', 'base_url' => 'https://api.anthropic.com/v1', 'api_key_env' => 'ANTHROPIC_API_KEY', 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/providers.md']],
            ['name' => 'Google Gemini', 'slug' => 'google', 'driver' => 'google', 'base_url' => 'https://generativelanguage.googleapis.com/v1beta', 'api_key_env' => 'GOOGLE_API_KEY', 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/providers.md']],
            ['name' => 'Alibaba', 'slug' => 'alibaba', 'driver' => 'openai', 'base_url' => 'https://dashscope.aliyuncs.com/compatible-mode/v1', 'api_key_env' => 'ALIYUN_API_KEY', 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/providers.md']],
            ['name' => 'Arcee', 'slug' => 'arcee', 'driver' => 'openai', 'base_url' => 'https://api.arcee.ai/api/v1', 'api_key_env' => 'ARCEE_API_KEY', 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/providers.md']],
            ['name' => 'Cerebras', 'slug' => 'cerebras', 'driver' => 'openai', 'base_url' => 'https://api.cerebras.ai/v1', 'api_key_env' => 'CEREBRAS_API_KEY', 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/providers.md']],
            ['name' => 'Chutes', 'slug' => 'chutes', 'driver' => 'openai', 'base_url' => 'https://api.chutes.ai/v1', 'api_key_env' => 'CHUTES_API_KEY', 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/providers.md']],
            ['name' => 'Cohere', 'slug' => 'cohere', 'driver' => 'openai', 'base_url' => 'https://api.cohere.ai/compatibility/v1', 'api_key_env' => 'COHERE_API_KEY', 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/providers.md']],
            ['name' => 'DeepSeek', 'slug' => 'deepseek', 'driver' => 'openai', 'base_url' => 'https://api.deepseek.com', 'api_key_env' => 'DEEPSEEK_API_KEY', 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/providers.md']],
            ['name' => 'Fireworks', 'slug' => 'fireworks', 'driver' => 'openai', 'base_url' => 'https://api.fireworks.ai/inference/v1', 'api_key_env' => 'FIREWORKS_API_KEY', 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/providers.md']],
            ['name' => 'Groq', 'slug' => 'groq', 'driver' => 'openai', 'base_url' => 'https://api.groq.com/openai/v1', 'api_key_env' => 'GROQ_API_KEY', 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/providers.md']],
            ['name' => 'Hugging Face', 'slug' => 'huggingface', 'driver' => 'openai', 'base_url' => 'https://api-inference.huggingface.co/v1', 'api_key_env' => 'HUGGINGFACE_API_KEY', 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/providers.md']],
            ['name' => 'Kilo Gateway', 'slug' => 'kilocode', 'driver' => 'openai', 'base_url' => 'https://api.kilo.ai/api/gateway/', 'api_key_env' => 'KILO_API_KEY', 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/providers.md']],
            ['name' => 'LM Studio', 'slug' => 'lmstudio', 'driver' => 'lmstudio', 'base_url' => 'http://localhost:1234/v1', 'api_key_env' => null, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/providers.md', 'local' => true]],
            ['name' => 'Mistral', 'slug' => 'mistral', 'driver' => 'openai', 'base_url' => 'https://api.mistral.ai/v1', 'api_key_env' => 'MISTRAL_API_KEY', 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/providers.md']],
            ['name' => 'MiniMax', 'slug' => 'minimax', 'driver' => 'anthropic', 'base_url' => 'https://api.minimax.io/v1', 'api_key_env' => 'MINIMAX_API_KEY', 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/providers.md']],
            ['name' => 'Moonshot', 'slug' => 'moonshot', 'driver' => 'openai', 'base_url' => 'https://api.moonshot.ai/v1', 'api_key_env' => 'MOONSHOT_API_KEY', 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/providers.md']],
            ['name' => 'NVIDIA', 'slug' => 'nvidia', 'driver' => 'openai', 'base_url' => 'https://integrate.api.nvidia.com/v1', 'api_key_env' => 'NVIDIA_API_KEY', 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/providers.md']],
            ['name' => 'Novita', 'slug' => 'novita', 'driver' => 'openai', 'base_url' => 'https://api.novita.ai/openai', 'api_key_env' => 'NOVITA_API_KEY', 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/providers.md']],
            ['name' => 'Ollama', 'slug' => 'ollama', 'driver' => 'ollama', 'base_url' => 'http://localhost:11434/v1', 'api_key_env' => null, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/providers.md', 'local' => true]],
            ['name' => 'Perplexity', 'slug' => 'perplexity', 'driver' => 'openai', 'base_url' => 'https://api.perplexity.ai/v1', 'api_key_env' => 'PERPLEXITY_API_KEY', 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/providers.md']],
            ['name' => 'Qwen', 'slug' => 'qwen', 'driver' => 'openai', 'base_url' => 'https://dashscope.aliyuncs.com/compatible-mode/v1', 'api_key_env' => 'QWEN_API_KEY', 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/providers.md']],
            ['name' => 'Together', 'slug' => 'together', 'driver' => 'openai', 'base_url' => 'https://api.together.xyz/v1', 'api_key_env' => 'TOGETHER_API_KEY', 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/providers.md']],
            ['name' => 'Venice', 'slug' => 'venice', 'driver' => 'openai', 'base_url' => 'https://api.venice.ai/api/v1', 'api_key_env' => 'VENICE_API_KEY', 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/providers.md']],
            ['name' => 'Vercel AI Gateway', 'slug' => 'vercel-ai-gateway', 'driver' => 'openai', 'base_url' => 'https://ai-gateway.vercel.sh/v1', 'api_key_env' => 'VERCEL_AI_GATEWAY_API_KEY', 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/providers.md']],
            ['name' => 'vLLM', 'slug' => 'vllm', 'driver' => 'vllm', 'base_url' => 'http://localhost:8000/v1', 'api_key_env' => null, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/providers.md', 'local' => true]],
            ['name' => 'xAI', 'slug' => 'xai', 'driver' => 'openai', 'base_url' => 'https://api.x.ai/v1', 'api_key_env' => 'XAI_API_KEY', 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/providers.md']],
            ['name' => 'Xiaomi', 'slug' => 'xiaomi', 'driver' => 'anthropic', 'base_url' => 'https://api.xiaomi.com/v1', 'api_key_env' => 'XIAOMI_API_KEY', 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/providers.md']],
            ['name' => 'Z.AI', 'slug' => 'zai', 'driver' => 'openai', 'base_url' => 'https://api.z.ai/v1', 'api_key_env' => 'ZAI_API_KEY', 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/providers.md']],
        ];
    }

    private function models(): array
    {
        return [
            ['provider_slug' => 'openrouter', 'name' => 'Anthropic Claude Haiku Latest', 'model_key' => '~anthropic/claude-haiku-latest', 'role' => 'summarize', 'context_window' => 200000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md', 'family' => 'openrouter'], 'pricing' => ['input_tokens' => 1.0, 'output_tokens' => 5.0, 'cache_tokens' => 0.1, 'reasoning_tokens' => 0.0]],
            ['provider_slug' => 'openrouter', 'name' => 'OpenAI GPT Mini Latest', 'model_key' => '~openai/gpt-mini-latest', 'role' => 'worker', 'context_window' => 400000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md', 'family' => 'openrouter'], 'pricing' => ['input_tokens' => 0.75, 'output_tokens' => 4.5, 'cache_tokens' => 0.075, 'reasoning_tokens' => 0.0]],
            ['provider_slug' => 'openrouter', 'name' => 'Google Gemini Pro Latest', 'model_key' => '~google/gemini-pro-latest', 'role' => 'reasoning', 'context_window' => 1048576, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md', 'family' => 'openrouter'], 'pricing' => ['input_tokens' => 2.0, 'output_tokens' => 12.0, 'cache_tokens' => 0.2, 'reasoning_tokens' => 12.0]],
            ['provider_slug' => 'openrouter', 'name' => 'MoonshotAI Kimi Latest', 'model_key' => '~moonshotai/kimi-latest', 'role' => 'worker', 'context_window' => 256000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md', 'family' => 'openrouter'], 'pricing' => ['input_tokens' => 0.7448, 'output_tokens' => 4.655, 'cache_tokens' => 0.1463, 'reasoning_tokens' => 0.0]],
            ['provider_slug' => 'openrouter', 'name' => 'Google Gemini Flash Latest', 'model_key' => '~google/gemini-flash-latest', 'role' => 'worker', 'context_window' => 1048576, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md', 'family' => 'openrouter'], 'pricing' => ['input_tokens' => 0.5, 'output_tokens' => 3.0, 'cache_tokens' => 0.05, 'reasoning_tokens' => 3.0]],
            ['provider_slug' => 'openrouter', 'name' => 'Anthropic Claude Sonnet Latest', 'model_key' => '~anthropic/claude-sonnet-latest', 'role' => 'reasoning', 'context_window' => 1000000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md', 'family' => 'openrouter'], 'pricing' => ['input_tokens' => 3.0, 'output_tokens' => 15.0, 'cache_tokens' => 0.3, 'reasoning_tokens' => 15.0]],
            ['provider_slug' => 'openrouter', 'name' => 'OpenAI GPT Latest', 'model_key' => '~openai/gpt-latest', 'role' => 'reasoning', 'context_window' => 1050000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md', 'family' => 'openrouter'], 'pricing' => ['input_tokens' => 5.0, 'output_tokens' => 30.0, 'cache_tokens' => 0.5, 'reasoning_tokens' => 30.0]],
            ['provider_slug' => 'openrouter', 'name' => 'OpenAI GPT-5.5 Pro', 'model_key' => 'openai/gpt-5.5-pro', 'role' => 'reasoning', 'context_window' => 400000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md', 'family' => 'openrouter'], 'pricing' => ['input_tokens' => 1.75, 'output_tokens' => 14.0, 'cache_tokens' => 0.175, 'reasoning_tokens' => 14.0]],
            ['provider_slug' => 'openrouter', 'name' => 'OpenAI GPT-5.2', 'model_key' => 'openai/gpt-5.2', 'role' => 'reasoning', 'context_window' => 400000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md', 'family' => 'openrouter'], 'pricing' => ['input_tokens' => 1.75, 'output_tokens' => 14.0, 'cache_tokens' => 0.175, 'reasoning_tokens' => 14.0]],
            ['provider_slug' => 'openrouter', 'name' => 'DeepSeek Chat v3.1', 'model_key' => 'deepseek/deepseek-chat-v3.1', 'role' => 'worker', 'context_window' => 128000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md', 'family' => 'openrouter'], 'pricing' => ['input_tokens' => 0.28, 'output_tokens' => 0.42, 'cache_tokens' => 0.028, 'reasoning_tokens' => 0.0]],
            ['provider_slug' => 'openrouter', 'name' => 'DeepSeek Reasoner v3.1', 'model_key' => 'deepseek/deepseek-reasoner-v3.1', 'role' => 'reasoning', 'context_window' => 128000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md', 'family' => 'openrouter'], 'pricing' => ['input_tokens' => 0.28, 'output_tokens' => 0.42, 'cache_tokens' => 0.028, 'reasoning_tokens' => 0.42]],
            ['provider_slug' => 'openrouter', 'name' => 'Qwen 3.5 Plus', 'model_key' => 'qwen/qwen3.5-plus-20260420', 'role' => 'planner', 'context_window' => 1000000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md', 'family' => 'openrouter'], 'pricing' => ['input_tokens' => 0.4, 'output_tokens' => 2.4, 'cache_tokens' => 0.04, 'reasoning_tokens' => 0.0]],
            ['provider_slug' => 'openrouter', 'name' => 'Qwen 3.6 Flash', 'model_key' => 'qwen/qwen3.6-flash', 'role' => 'worker', 'context_window' => 1000000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md', 'family' => 'openrouter'], 'pricing' => ['input_tokens' => 0.25, 'output_tokens' => 1.5, 'cache_tokens' => 0.025, 'reasoning_tokens' => 0.0]],
            ['provider_slug' => 'openrouter', 'name' => 'Qwen 3.6 35B A3B', 'model_key' => 'qwen/qwen3.6-35b-a3b-20260415', 'role' => 'reasoning', 'context_window' => 262144, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md', 'family' => 'openrouter'], 'pricing' => ['input_tokens' => 0.1612, 'output_tokens' => 0.96525, 'cache_tokens' => 0.01612, 'reasoning_tokens' => 0.0]],
            ['provider_slug' => 'openrouter', 'name' => 'Qwen 3.6 Max Preview', 'model_key' => 'qwen/qwen3.6-max-preview-20260420', 'role' => 'reasoning', 'context_window' => 262144, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md', 'family' => 'openrouter'], 'pricing' => ['input_tokens' => 1.04, 'output_tokens' => 6.24, 'cache_tokens' => 0.104, 'reasoning_tokens' => 0.0]],
            ['provider_slug' => 'openrouter', 'name' => 'Qwen 3.6 27B', 'model_key' => 'qwen/qwen3.6-27b-20260422', 'role' => 'worker', 'context_window' => 256000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md', 'family' => 'openrouter'], 'pricing' => ['input_tokens' => 0.325, 'output_tokens' => 3.25, 'cache_tokens' => 0.0325, 'reasoning_tokens' => 0.0]],
            ['provider_slug' => 'openrouter', 'name' => 'Meta Llama 3.3 70B Instruct', 'model_key' => 'meta-llama/Llama-3.3-70B-Instruct-Turbo', 'role' => 'worker', 'context_window' => 131072, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md', 'family' => 'openrouter'], 'pricing' => ['input_tokens' => 0.88, 'output_tokens' => 0.88, 'cache_tokens' => 0.0, 'reasoning_tokens' => 0.0]],
            ['provider_slug' => 'openrouter', 'name' => 'Meta Llama 3.1 8B Instruct', 'model_key' => 'meta-llama/Llama-3.1-8B-Instruct-Turbo', 'role' => 'worker', 'context_window' => 131072, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md', 'family' => 'openrouter'], 'pricing' => ['input_tokens' => 0.05, 'output_tokens' => 0.08, 'cache_tokens' => 0.0, 'reasoning_tokens' => 0.0]],
            ['provider_slug' => 'openrouter', 'name' => 'Mistral Large 3', 'model_key' => 'mistralai/mistral-large-3', 'role' => 'reasoning', 'context_window' => 131072, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md', 'family' => 'openrouter'], 'pricing' => ['input_tokens' => 2.0, 'output_tokens' => 6.0, 'cache_tokens' => 0.0, 'reasoning_tokens' => 6.0]],
            ['provider_slug' => 'openrouter', 'name' => 'xAI Grok 4.20', 'model_key' => 'x-ai/grok-4.20', 'role' => 'reasoning', 'context_window' => 2000000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md', 'family' => 'openrouter'], 'pricing' => ['input_tokens' => 0.0, 'output_tokens' => 0.0, 'cache_tokens' => 0.0, 'reasoning_tokens' => 0.0]],
            ['provider_slug' => 'openrouter', 'name' => 'Claude Opus 4.1', 'model_key' => 'anthropic/claude-opus-4-1', 'role' => 'reasoning', 'context_window' => 200000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md', 'family' => 'openrouter'], 'pricing' => ['input_tokens' => 15.0, 'output_tokens' => 75.0, 'cache_tokens' => 1.5, 'reasoning_tokens' => 75.0]],

            ['provider_slug' => 'openai', 'name' => 'GPT-5.2', 'model_key' => 'gpt-5.2', 'role' => 'reasoning', 'context_window' => 400000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 1.75, 'output_tokens' => 14.0, 'prompt_cache_hit_tokens' => 0.175, 'prompt_cache_miss_tokens' => 1.75, 'reasoning_tokens' => 14.0]],
            ['provider_slug' => 'openai', 'name' => 'GPT-5.2-Codex', 'model_key' => 'gpt-5.2-codex', 'role' => 'planner', 'context_window' => 400000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 1.75, 'output_tokens' => 14.0, 'prompt_cache_hit_tokens' => 0.175, 'prompt_cache_miss_tokens' => 1.75, 'reasoning_tokens' => 14.0]],
            ['provider_slug' => 'openai', 'name' => 'GPT-4.1', 'model_key' => 'gpt-4.1', 'role' => 'worker', 'context_window' => 1047576, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 2.0, 'output_tokens' => 8.0, 'prompt_cache_hit_tokens' => 0.5, 'prompt_cache_miss_tokens' => 2.0, 'reasoning_tokens' => 0.0]],
            ['provider_slug' => 'openai', 'name' => 'GPT-4o', 'model_key' => 'gpt-4o', 'role' => 'worker', 'context_window' => 128000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 2.5, 'output_tokens' => 10.0, 'prompt_cache_hit_tokens' => 1.25, 'prompt_cache_miss_tokens' => 2.5, 'reasoning_tokens' => 0.0]],
            ['provider_slug' => 'openai', 'name' => 'text-embedding-3-small', 'model_key' => 'text-embedding-3-small', 'role' => 'embedding', 'context_window' => 8192, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 0.02, 'output_tokens' => 0.0, 'prompt_cache_hit_tokens' => 0.0, 'prompt_cache_miss_tokens' => 0.02, 'reasoning_tokens' => 0.0]],

            ['provider_slug' => 'anthropic', 'name' => 'Claude Opus 4.1', 'model_key' => 'claude-opus-4-1', 'role' => 'reasoning', 'context_window' => 200000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 15.0, 'output_tokens' => 75.0, 'prompt_cache_hit_tokens' => 1.5, 'prompt_cache_miss_tokens' => 15.0, 'reasoning_tokens' => 75.0]],
            ['provider_slug' => 'anthropic', 'name' => 'Claude Sonnet 4', 'model_key' => 'claude-sonnet-4', 'role' => 'worker', 'context_window' => 200000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 3.0, 'output_tokens' => 15.0, 'prompt_cache_hit_tokens' => 0.3, 'prompt_cache_miss_tokens' => 3.0, 'reasoning_tokens' => 15.0]],
            ['provider_slug' => 'anthropic', 'name' => 'Claude Haiku 3.5', 'model_key' => 'claude-haiku-3-5', 'role' => 'summarize', 'context_window' => 200000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 0.8, 'output_tokens' => 4.0, 'prompt_cache_hit_tokens' => 0.08, 'prompt_cache_miss_tokens' => 0.8, 'reasoning_tokens' => 4.0]],

            ['provider_slug' => 'google', 'name' => 'Gemini 2.5 Pro', 'model_key' => 'gemini-2.5-pro', 'role' => 'reasoning', 'context_window' => 1000000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 0.7, 'output_tokens' => 2.8, 'prompt_cache_hit_tokens' => 0.025, 'prompt_cache_miss_tokens' => 0.7, 'reasoning_tokens' => 2.8]],
            ['provider_slug' => 'google', 'name' => 'Gemini 2.0 Flash', 'model_key' => 'gemini-2.0-flash', 'role' => 'worker', 'context_window' => 1000000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 0.1, 'output_tokens' => 0.4, 'prompt_cache_hit_tokens' => 0.025, 'prompt_cache_miss_tokens' => 0.1, 'reasoning_tokens' => 0.4]],
            ['provider_slug' => 'google', 'name' => 'Gemini 2.0 Flash-Lite', 'model_key' => 'gemini-2.0-flash-lite', 'role' => 'summarize', 'context_window' => 1000000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 0.075, 'output_tokens' => 0.3, 'prompt_cache_hit_tokens' => 0.025, 'prompt_cache_miss_tokens' => 0.075, 'reasoning_tokens' => 0.3]],

            ['provider_slug' => 'deepseek', 'name' => 'DeepSeek Chat', 'model_key' => 'deepseek-chat', 'role' => 'worker', 'context_window' => 128000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 0.28, 'output_tokens' => 0.42, 'prompt_cache_hit_tokens' => 0.028, 'prompt_cache_miss_tokens' => 0.28, 'reasoning_tokens' => 0.0]],
            ['provider_slug' => 'deepseek', 'name' => 'DeepSeek Reasoner', 'model_key' => 'deepseek-reasoner', 'role' => 'reasoning', 'context_window' => 128000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 0.28, 'output_tokens' => 0.42, 'prompt_cache_hit_tokens' => 0.028, 'prompt_cache_miss_tokens' => 0.28, 'reasoning_tokens' => 0.42]],

            ['provider_slug' => 'mistral', 'name' => 'Mistral Large 3', 'model_key' => 'mistral-large-2512', 'role' => 'reasoning', 'context_window' => 131072, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 2.0, 'output_tokens' => 6.0, 'prompt_cache_hit_tokens' => 0.0, 'prompt_cache_miss_tokens' => 2.0, 'reasoning_tokens' => 6.0]],
            ['provider_slug' => 'mistral', 'name' => 'Mistral Medium 3.1', 'model_key' => 'mistral-medium-2508', 'role' => 'planner', 'context_window' => 131072, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 0.8, 'output_tokens' => 2.4, 'prompt_cache_hit_tokens' => 0.0, 'prompt_cache_miss_tokens' => 0.8, 'reasoning_tokens' => 2.4]],
            ['provider_slug' => 'mistral', 'name' => 'Mistral Small 4', 'model_key' => 'mistral-small-2603', 'role' => 'worker', 'context_window' => 256000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 0.15, 'output_tokens' => 0.6, 'prompt_cache_hit_tokens' => 0.0, 'prompt_cache_miss_tokens' => 0.15, 'reasoning_tokens' => 0.6]],
            ['provider_slug' => 'mistral', 'name' => 'Codestral', 'model_key' => 'codestral-2501', 'role' => 'planner', 'context_window' => 32768, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 0.25, 'output_tokens' => 0.8, 'prompt_cache_hit_tokens' => 0.0, 'prompt_cache_miss_tokens' => 0.25, 'reasoning_tokens' => 0.8]],

            ['provider_slug' => 'cohere', 'name' => 'Command A', 'model_key' => 'command-a-03-2025', 'role' => 'planner', 'context_window' => 256000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 0.0, 'output_tokens' => 0.0, 'prompt_cache_hit_tokens' => 0.0, 'prompt_cache_miss_tokens' => 0.0, 'reasoning_tokens' => 0.0]],
            ['provider_slug' => 'cohere', 'name' => 'Command R+ 08-2024', 'model_key' => 'command-r-plus-08-2024', 'role' => 'web-search', 'context_window' => 128000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 2.5, 'output_tokens' => 10.0, 'prompt_cache_hit_tokens' => 0.0, 'prompt_cache_miss_tokens' => 2.5, 'reasoning_tokens' => 0.0]],

            ['provider_slug' => 'groq', 'name' => 'Llama 3.1 8B Instant', 'model_key' => 'llama-3.1-8b-instant', 'role' => 'worker', 'context_window' => 131072, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 0.05, 'output_tokens' => 0.08, 'prompt_cache_hit_tokens' => 0.0, 'prompt_cache_miss_tokens' => 0.05, 'reasoning_tokens' => 0.0]],
            ['provider_slug' => 'groq', 'name' => 'Llama 3.3 70B Versatile', 'model_key' => 'llama-3.3-70b-versatile', 'role' => 'reasoning', 'context_window' => 131072, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 0.59, 'output_tokens' => 0.79, 'prompt_cache_hit_tokens' => 0.0, 'prompt_cache_miss_tokens' => 0.59, 'reasoning_tokens' => 0.79]],
            ['provider_slug' => 'groq', 'name' => 'GPT OSS 120B', 'model_key' => 'openai/gpt-oss-120b', 'role' => 'reasoning', 'context_window' => 131072, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 0.15, 'output_tokens' => 0.6, 'prompt_cache_hit_tokens' => 0.0, 'prompt_cache_miss_tokens' => 0.15, 'reasoning_tokens' => 0.6]],
            ['provider_slug' => 'groq', 'name' => 'GPT OSS 20B', 'model_key' => 'openai/gpt-oss-20b', 'role' => 'worker', 'context_window' => 131072, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 0.075, 'output_tokens' => 0.3, 'prompt_cache_hit_tokens' => 0.0, 'prompt_cache_miss_tokens' => 0.075, 'reasoning_tokens' => 0.3]],

            ['provider_slug' => 'fireworks', 'name' => 'Llama 3.1 70B Instruct', 'model_key' => 'llama-v3p1-70b-instruct', 'role' => 'worker', 'context_window' => 131072, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 0.0, 'output_tokens' => 0.0, 'prompt_cache_hit_tokens' => 0.0, 'prompt_cache_miss_tokens' => 0.0, 'reasoning_tokens' => 0.0]],
            ['provider_slug' => 'fireworks', 'name' => 'DeepSeek V3.1', 'model_key' => 'deepseek-v3.1', 'role' => 'reasoning', 'context_window' => 131072, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 0.0, 'output_tokens' => 0.0, 'prompt_cache_hit_tokens' => 0.0, 'prompt_cache_miss_tokens' => 0.0, 'reasoning_tokens' => 0.0]],
            ['provider_slug' => 'fireworks', 'name' => 'Kimi K2 0905', 'model_key' => 'kimi-k2-0905', 'role' => 'planner', 'context_window' => 131072, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 0.0, 'output_tokens' => 0.0, 'prompt_cache_hit_tokens' => 0.0, 'prompt_cache_miss_tokens' => 0.0, 'reasoning_tokens' => 0.0]],

            ['provider_slug' => 'together', 'name' => 'MiniMax M2.7', 'model_key' => 'MiniMaxAI/MiniMax-M2.7', 'role' => 'reasoning', 'context_window' => 202752, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 0.3, 'output_tokens' => 1.2, 'prompt_cache_hit_tokens' => 0.06, 'prompt_cache_miss_tokens' => 0.3, 'reasoning_tokens' => 1.2]],
            ['provider_slug' => 'together', 'name' => 'MiniMax M2.5', 'model_key' => 'MiniMaxAI/MiniMax-M2.5', 'role' => 'reasoning', 'context_window' => 228700, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 0.3, 'output_tokens' => 1.2, 'prompt_cache_hit_tokens' => 0.06, 'prompt_cache_miss_tokens' => 0.3, 'reasoning_tokens' => 1.2]],
            ['provider_slug' => 'together', 'name' => 'Qwen3.5 397B A17B', 'model_key' => 'Qwen/Qwen3.5-397B-A17B', 'role' => 'planner', 'context_window' => 262144, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 0.6, 'output_tokens' => 3.6, 'prompt_cache_hit_tokens' => 0.0, 'prompt_cache_miss_tokens' => 0.6, 'reasoning_tokens' => 3.6]],
            ['provider_slug' => 'together', 'name' => 'Llama 3.3 70B Instruct Turbo', 'model_key' => 'meta-llama/Llama-3.3-70B-Instruct-Turbo', 'role' => 'worker', 'context_window' => 131072, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 0.88, 'output_tokens' => 0.88, 'prompt_cache_hit_tokens' => 0.0, 'prompt_cache_miss_tokens' => 0.88, 'reasoning_tokens' => 0.0]],

            ['provider_slug' => 'perplexity', 'name' => 'Sonar', 'model_key' => 'perplexity/sonar', 'role' => 'web-search', 'context_window' => 200000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 0.25, 'output_tokens' => 2.5, 'prompt_cache_hit_tokens' => 0.0625, 'prompt_cache_miss_tokens' => 0.25, 'reasoning_tokens' => 0.0]],
            ['provider_slug' => 'perplexity', 'name' => 'Sonar Pro', 'model_key' => 'perplexity/sonar-pro', 'role' => 'web-search', 'context_window' => 200000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 3.0, 'output_tokens' => 15.0, 'prompt_cache_hit_tokens' => 0.0, 'prompt_cache_miss_tokens' => 3.0, 'reasoning_tokens' => 15.0]],
            ['provider_slug' => 'perplexity', 'name' => 'Claude Opus 4.6', 'model_key' => 'anthropic/claude-opus-4-6', 'role' => 'reasoning', 'context_window' => 200000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 5.0, 'output_tokens' => 25.0, 'prompt_cache_hit_tokens' => 0.5, 'prompt_cache_miss_tokens' => 5.0, 'reasoning_tokens' => 25.0]],

            ['provider_slug' => 'nvidia', 'name' => 'Nemotron 3 Super 120B A12B', 'model_key' => 'nemotron-3-super-120b-a12b', 'role' => 'planner', 'context_window' => 1000000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 0.0, 'output_tokens' => 0.0, 'prompt_cache_hit_tokens' => 0.0, 'prompt_cache_miss_tokens' => 0.0, 'reasoning_tokens' => 0.0]],
            ['provider_slug' => 'nvidia', 'name' => 'Qwen3.5 122B A10B', 'model_key' => 'qwen3.5-122b-a10b', 'role' => 'reasoning', 'context_window' => 262144, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 0.0, 'output_tokens' => 0.0, 'prompt_cache_hit_tokens' => 0.0, 'prompt_cache_miss_tokens' => 0.0, 'reasoning_tokens' => 0.0]],
            ['provider_slug' => 'nvidia', 'name' => 'Minimax M2.7', 'model_key' => 'minimax-m2.7', 'role' => 'worker', 'context_window' => 202752, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 0.0, 'output_tokens' => 0.0, 'prompt_cache_hit_tokens' => 0.0, 'prompt_cache_miss_tokens' => 0.0, 'reasoning_tokens' => 0.0]],
            ['provider_slug' => 'nvidia', 'name' => 'Ising Calibration 1 35B A3B', 'model_key' => 'ising-calibration-1-35b-a3b', 'role' => 'worker', 'context_window' => 131072, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md'], 'pricing' => ['input_tokens' => 0.0, 'output_tokens' => 0.0, 'prompt_cache_hit_tokens' => 0.0, 'prompt_cache_miss_tokens' => 0.0, 'reasoning_tokens' => 0.0]],

            ['provider_slug' => 'openai', 'name' => 'LM Studio / GPT OSS 20B', 'model_key' => 'openai/gpt-oss-20b', 'role' => 'worker', 'context_window' => 128000, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md', 'local_reference' => 'lmstudio'], 'pricing' => ['input_tokens' => 0.0, 'output_tokens' => 0.0, 'prompt_cache_hit_tokens' => 0.0, 'prompt_cache_miss_tokens' => 0.0, 'reasoning_tokens' => 0.0]],
            ['provider_slug' => 'ollama', 'name' => 'Gemma 3', 'model_key' => 'gemma3', 'role' => 'worker', 'context_window' => 32768, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md', 'local_reference' => 'ollama'], 'pricing' => ['input_tokens' => 0.0, 'output_tokens' => 0.0, 'prompt_cache_hit_tokens' => 0.0, 'prompt_cache_miss_tokens' => 0.0, 'reasoning_tokens' => 0.0]],
            ['provider_slug' => 'vllm', 'name' => 'Meta Llama 3.1 8B Instruct', 'model_key' => 'meta-llama/Llama-3.1-8B-Instruct', 'role' => 'worker', 'context_window' => 131072, 'is_enabled' => false, 'is_default' => false, 'metadata' => ['source' => 'docs/models.md', 'local_reference' => 'vllm'], 'pricing' => ['input_tokens' => 0.0, 'output_tokens' => 0.0, 'prompt_cache_hit_tokens' => 0.0, 'prompt_cache_miss_tokens' => 0.0, 'reasoning_tokens' => 0.0]],
        ];
    }
};
