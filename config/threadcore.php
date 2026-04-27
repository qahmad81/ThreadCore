<?php

return [
    'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY'),
        'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
        'main_model' => env('OPENROUTER_MAIN_MODEL', 'openai/gpt-5.4'),
        'worker_model' => env('OPENROUTER_WORKER_MODEL', 'z-ai/glm-4.7-flash'),
    ],

    'ollama' => [
        'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
        'fast_model' => env('OLLAMA_FAST_MODEL', 'gemma3:1b'),
        'balanced_model' => env('OLLAMA_BALANCED_MODEL', 'gemma3:4b'),
        'reasoning_model' => env('OLLAMA_REASONING_MODEL', 'qwen3.5:9b'),
    ],

    'admin' => [
        'path' => trim(env('THREADCORE_ADMIN_PATH', 'admin'), '/'),
        'email' => env('THREADCORE_ADMIN_EMAIL', 'admin@threadcore.local'),
        'password' => env('THREADCORE_ADMIN_PASSWORD'),
    ],

    'demo_customer' => [
        'email' => env('THREADCORE_DEMO_CUSTOMER_EMAIL', 'customer@threadcore.local'),
        'password' => env('THREADCORE_DEMO_CUSTOMER_PASSWORD', 'password'),
    ],

    'gateway' => [
        'timeout_seconds' => (int) env('THREADCORE_GATEWAY_TIMEOUT_SECONDS', 1200),
    ],
];
