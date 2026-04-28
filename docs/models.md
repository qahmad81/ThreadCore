# Model Registry

This is a working companion to [providers.md](./providers.md). It keeps the external providers at the top level and groups a small, practical set of representative models under each one.

Notes:
- Pricing JSON must use the same canonical usage keys that ThreadCore stores in the model adapter output. If a provider exposes a different raw shape, the adapter should normalize it first, then the pricing keys should follow that normalized shape.
- If a provider exposes a single `cache_tokens` bucket, use `cache_tokens` consistently. If it exposes split cache buckets, normalize them before billing rather than mixing raw and canonical names in the same record.
- Where the provider documents cache or reasoning buckets, those fields are preserved after normalization.
- For local/self-hosted providers, pricing is set to zero because the provider does not publish a first-party API bill.
- Some providers expose very large catalogs. In those cases, this file keeps the most useful models or a representative first-pass catalog entry and can be expanded later.

## OpenRouter

OpenRouter’s public models catalog is sorted from most-used to least-used in its API docs, so this section keeps a first-pass working list of the models that are easiest to route against and most likely to matter in ThreadCore. Refresh the prices from the OpenRouter `/api/v1/models` payload when you need production-accurate billing.

| Model name | Model code | Role | Context window | Pricing JSON |
| --- | --- | --- | --- | --- |
| Anthropic Claude Haiku Latest | `~anthropic/claude-haiku-latest` | `summarize` | `200000` | `{"input_tokens":1.0,"output_tokens":5.0,"cache_tokens":0.1,"reasoning_tokens":0.0}` |
| OpenAI GPT Mini Latest | `~openai/gpt-mini-latest` | `worker` | `400000` | `{"input_tokens":0.75,"output_tokens":4.5,"cache_tokens":0.075,"reasoning_tokens":0.0}` |
| Google Gemini Pro Latest | `~google/gemini-pro-latest` | `reasoning` | `1048576` | `{"input_tokens":2.0,"output_tokens":12.0,"cache_tokens":0.2,"reasoning_tokens":12.0}` |
| MoonshotAI Kimi Latest | `~moonshotai/kimi-latest` | `worker` | `256000` | `{"input_tokens":0.7448,"output_tokens":4.655,"cache_tokens":0.1463,"reasoning_tokens":0.0}` |
| Google Gemini Flash Latest | `~google/gemini-flash-latest` | `worker` | `1048576` | `{"input_tokens":0.5,"output_tokens":3.0,"cache_tokens":0.05,"reasoning_tokens":3.0}` |
| Anthropic Claude Sonnet Latest | `~anthropic/claude-sonnet-latest` | `reasoning` | `1000000` | `{"input_tokens":3.0,"output_tokens":15.0,"cache_tokens":0.3,"reasoning_tokens":15.0}` |
| OpenAI GPT Latest | `~openai/gpt-latest` | `reasoning` | `1050000` | `{"input_tokens":5.0,"output_tokens":30.0,"cache_tokens":0.5,"reasoning_tokens":30.0}` |
| OpenAI GPT-5.5 Pro | `openai/gpt-5.5-pro` | `reasoning` | `400000` | `{"input_tokens":1.75,"output_tokens":14.0,"cache_tokens":0.175,"reasoning_tokens":14.0}` |
| OpenAI GPT-5.2 | `openai/gpt-5.2` | `reasoning` | `400000` | `{"input_tokens":1.75,"output_tokens":14.0,"cache_tokens":0.175,"reasoning_tokens":14.0}` |
| DeepSeek Chat v3.1 | `deepseek/deepseek-chat-v3.1` | `worker` | `128000` | `{"input_tokens":0.28,"output_tokens":0.42,"cache_tokens":0.028,"reasoning_tokens":0.0}` |
| DeepSeek Reasoner v3.1 | `deepseek/deepseek-reasoner-v3.1` | `reasoning` | `128000` | `{"input_tokens":0.28,"output_tokens":0.42,"cache_tokens":0.028,"reasoning_tokens":0.42}` |
| Qwen 3.5 Plus | `qwen/qwen3.5-plus-20260420` | `planner` | `1000000` | `{"input_tokens":0.4,"output_tokens":2.4,"cache_tokens":0.04,"reasoning_tokens":0.0}` |
| Qwen 3.6 Flash | `qwen/qwen3.6-flash` | `worker` | `1000000` | `{"input_tokens":0.25,"output_tokens":1.5,"cache_tokens":0.025,"reasoning_tokens":0.0}` |
| Qwen 3.6 35B A3B | `qwen/qwen3.6-35b-a3b-20260415` | `reasoning` | `262144` | `{"input_tokens":0.1612,"output_tokens":0.96525,"cache_tokens":0.01612,"reasoning_tokens":0.0}` |
| Qwen 3.6 Max Preview | `qwen/qwen3.6-max-preview-20260420` | `reasoning` | `262144` | `{"input_tokens":1.04,"output_tokens":6.24,"cache_tokens":0.104,"reasoning_tokens":0.0}` |
| Qwen 3.6 27B | `qwen/qwen3.6-27b-20260422` | `worker` | `256000` | `{"input_tokens":0.325,"output_tokens":3.25,"cache_tokens":0.0325,"reasoning_tokens":0.0}` |
| Meta Llama 3.3 70B Instruct | `meta-llama/Llama-3.3-70B-Instruct-Turbo` | `worker` | `131072` | `{"input_tokens":0.88,"output_tokens":0.88,"cache_tokens":0.0,"reasoning_tokens":0.0}` |
| Meta Llama 3.1 8B Instruct | `meta-llama/Llama-3.1-8B-Instruct-Turbo` | `worker` | `131072` | `{"input_tokens":0.05,"output_tokens":0.08,"cache_tokens":0.0,"reasoning_tokens":0.0}` |
| Mistral Large 3 | `mistralai/mistral-large-3` | `reasoning` | `131072` | `{"input_tokens":2.0,"output_tokens":6.0,"cache_tokens":0.0,"reasoning_tokens":6.0}` |
| xAI Grok 4.20 | `x-ai/grok-4.20` | `reasoning` | `2000000` | `{"input_tokens":0.0,"output_tokens":0.0,"cache_tokens":0.0,"reasoning_tokens":0.0}` |
| Claude Opus 4.1 | `anthropic/claude-opus-4-1` | `reasoning` | `200000` | `{"input_tokens":15.0,"output_tokens":75.0,"cache_tokens":1.5,"reasoning_tokens":75.0}` |

Source: [OpenRouter Models API](https://openrouter.ai/docs/guides/overview/models), [OpenRouter API models](https://openrouter.ai/api/v1/models)

## OpenAI

| Model name | Model code | Role | Context window | Pricing JSON |
| --- | --- | --- | --- | --- |
| GPT-5.2 | `gpt-5.2` | `reasoning` | `400000` | `{"input_tokens":1.75,"output_tokens":14.0,"prompt_cache_hit_tokens":0.175,"prompt_cache_miss_tokens":1.75,"reasoning_tokens":14.0}` |
| GPT-5.2-Codex | `gpt-5.2-codex` | `planner` | `400000` | `{"input_tokens":1.75,"output_tokens":14.0,"prompt_cache_hit_tokens":0.175,"prompt_cache_miss_tokens":1.75,"reasoning_tokens":14.0}` |
| GPT-4.1 | `gpt-4.1` | `worker` | `1047576` | `{"input_tokens":2.0,"output_tokens":8.0,"prompt_cache_hit_tokens":0.5,"prompt_cache_miss_tokens":2.0,"reasoning_tokens":0.0}` |
| GPT-4o | `gpt-4o` | `worker` | `128000` | `{"input_tokens":2.5,"output_tokens":10.0,"prompt_cache_hit_tokens":1.25,"prompt_cache_miss_tokens":2.5,"reasoning_tokens":0.0}` |
| text-embedding-3-small | `text-embedding-3-small` | `embedding` | `8192` | `{"input_tokens":0.02,"output_tokens":0.0,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.02,"reasoning_tokens":0.0}` |

Source: [OpenAI model pages](https://platform.openai.com/docs/models/gpt-5.2), [GPT-4.1](https://platform.openai.com/docs/models/gpt-4.1), [GPT-4o](https://platform.openai.com/docs/models/gpt-4o), [text-embedding-3-small](https://platform.openai.com/docs/models/text-embedding-3-small), [pricing](https://platform.openai.com/docs/pricing)

## Anthropic

| Model name | Model code | Role | Context window | Pricing JSON |
| --- | --- | --- | --- | --- |
| Claude Opus 4.1 | `claude-opus-4-1` | `reasoning` | `200000` | `{"input_tokens":15.0,"output_tokens":75.0,"prompt_cache_hit_tokens":1.5,"prompt_cache_miss_tokens":15.0,"reasoning_tokens":75.0}` |
| Claude Sonnet 4 | `claude-sonnet-4` | `worker` | `200000` | `{"input_tokens":3.0,"output_tokens":15.0,"prompt_cache_hit_tokens":0.3,"prompt_cache_miss_tokens":3.0,"reasoning_tokens":15.0}` |
| Claude Haiku 3.5 | `claude-haiku-3-5` | `summarize` | `200000` | `{"input_tokens":0.8,"output_tokens":4.0,"prompt_cache_hit_tokens":0.08,"prompt_cache_miss_tokens":0.8,"reasoning_tokens":4.0}` |

Source: [Anthropic pricing](https://docs.anthropic.com/en/docs/about-claude/pricing), [Anthropic models overview](https://docs.anthropic.com/en/docs/models-overview)

## Google Gemini

| Model name | Model code | Role | Context window | Pricing JSON |
| --- | --- | --- | --- | --- |
| Gemini 2.5 Pro | `gemini-2.5-pro` | `reasoning` | `1000000` | `{"input_tokens":0.7,"output_tokens":2.8,"prompt_cache_hit_tokens":0.025,"prompt_cache_miss_tokens":0.7,"reasoning_tokens":2.8}` |
| Gemini 2.0 Flash | `gemini-2.0-flash` | `worker` | `1000000` | `{"input_tokens":0.1,"output_tokens":0.4,"prompt_cache_hit_tokens":0.025,"prompt_cache_miss_tokens":0.1,"reasoning_tokens":0.4}` |
| Gemini 2.0 Flash-Lite | `gemini-2.0-flash-lite` | `summarize` | `1000000` | `{"input_tokens":0.075,"output_tokens":0.3,"prompt_cache_hit_tokens":0.025,"prompt_cache_miss_tokens":0.075,"reasoning_tokens":0.3}` |

Source: [Gemini pricing](https://ai.google.dev/gemini-api/docs/pricing), [Gemini models](https://ai.google.dev/gemini-api/docs/models/gemini), [long context](https://ai.google.dev/gemini-api/docs/long-context)

## DeepSeek

| Model name | Model code | Role | Context window | Pricing JSON |
| --- | --- | --- | --- | --- |
| DeepSeek Chat | `deepseek-chat` | `worker` | `128000` | `{"input_tokens":0.28,"output_tokens":0.42,"prompt_cache_hit_tokens":0.028,"prompt_cache_miss_tokens":0.28,"reasoning_tokens":0.0}` |
| DeepSeek Reasoner | `deepseek-reasoner` | `reasoning` | `128000` | `{"input_tokens":0.28,"output_tokens":0.42,"prompt_cache_hit_tokens":0.028,"prompt_cache_miss_tokens":0.28,"reasoning_tokens":0.42}` |

Source: [DeepSeek pricing](https://api-docs.deepseek.com/quick_start/pricing/)

## Mistral

| Model name | Model code | Role | Context window | Pricing JSON |
| --- | --- | --- | --- | --- |
| Mistral Large 3 | `mistral-large-2512` | `reasoning` | `131072` | `{"input_tokens":2.0,"output_tokens":6.0,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":2.0,"reasoning_tokens":6.0}` |
| Mistral Medium 3.1 | `mistral-medium-2508` | `planner` | `131072` | `{"input_tokens":0.8,"output_tokens":2.4,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.8,"reasoning_tokens":2.4}` |
| Mistral Small 4 | `mistral-small-2603` | `worker` | `256000` | `{"input_tokens":0.15,"output_tokens":0.6,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.15,"reasoning_tokens":0.6}` |
| Codestral | `codestral-2501` | `planner` | `32768` | `{"input_tokens":0.25,"output_tokens":0.8,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.25,"reasoning_tokens":0.8}` |

Source: [Mistral models overview](https://docs.mistral.ai/getting-started/models/models_overview), [Mistral Small 4](https://docs.mistral.ai/models/model-cards/mistral-small-4-0-26-03), [known limitations](https://docs.mistral.ai/resources/known-limitations)

## Cohere

| Model name | Model code | Role | Context window | Pricing JSON |
| --- | --- | --- | --- | --- |
| Command A | `command-a-03-2025` | `planner` | `256000` | `{"input_tokens":0.0,"output_tokens":0.0,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.0,"reasoning_tokens":0.0}` |
| Command R+ 08-2024 | `command-r-plus-08-2024` | `web-search` | `128000` | `{"input_tokens":2.5,"output_tokens":10.0,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":2.5,"reasoning_tokens":0.0}` |

Source: [Cohere models](https://docs.cohere.com/v2/docs/models), [Command R+](https://docs.cohere.com/v2/docs/command-r-plus)

## Groq

| Model name | Model code | Role | Context window | Pricing JSON |
| --- | --- | --- | --- | --- |
| Llama 3.1 8B Instant | `llama-3.1-8b-instant` | `worker` | `131072` | `{"input_tokens":0.05,"output_tokens":0.08,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.05,"reasoning_tokens":0.0}` |
| Llama 3.3 70B Versatile | `llama-3.3-70b-versatile` | `reasoning` | `131072` | `{"input_tokens":0.59,"output_tokens":0.79,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.59,"reasoning_tokens":0.79}` |
| GPT OSS 120B | `openai/gpt-oss-120b` | `reasoning` | `131072` | `{"input_tokens":0.15,"output_tokens":0.60,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.15,"reasoning_tokens":0.60}` |
| GPT OSS 20B | `openai/gpt-oss-20b` | `worker` | `131072` | `{"input_tokens":0.075,"output_tokens":0.30,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.075,"reasoning_tokens":0.30}` |

Source: [Groq models](https://console.groq.com/docs/models), [Groq reasoning](https://console.groq.com/docs/reasoning), [Whisper](https://console.groq.com/docs/model/whisper-large-v3)

## Fireworks

| Model name | Model code | Role | Context window | Pricing JSON |
| --- | --- | --- | --- | --- |
| Llama 3.1 70B Instruct | `llama-v3p1-70b-instruct` | `worker` | `131072` | `{"input_tokens":0.0,"output_tokens":0.0,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.0,"reasoning_tokens":0.0}` |
| DeepSeek V3.1 | `deepseek-v3.1` | `reasoning` | `131072` | `{"input_tokens":0.0,"output_tokens":0.0,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.0,"reasoning_tokens":0.0}` |
| Kimi K2 0905 | `kimi-k2-0905` | `planner` | `131072` | `{"input_tokens":0.0,"output_tokens":0.0,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.0,"reasoning_tokens":0.0}` |

Source: [Fireworks model overview](https://docs.fireworks.ai/models/overview), [recommended open models](https://docs.fireworks.ai/guides)

## Together

| Model name | Model code | Role | Context window | Pricing JSON |
| --- | --- | --- | --- | --- |
| MiniMax M2.7 | `MiniMaxAI/MiniMax-M2.7` | `reasoning` | `202752` | `{"input_tokens":0.30,"output_tokens":1.20,"prompt_cache_hit_tokens":0.06,"prompt_cache_miss_tokens":0.30,"reasoning_tokens":1.20}` |
| MiniMax M2.5 | `MiniMaxAI/MiniMax-M2.5` | `reasoning` | `228700` | `{"input_tokens":0.30,"output_tokens":1.20,"prompt_cache_hit_tokens":0.06,"prompt_cache_miss_tokens":0.30,"reasoning_tokens":1.20}` |
| Qwen3.5 397B A17B | `Qwen/Qwen3.5-397B-A17B` | `planner` | `262144` | `{"input_tokens":0.60,"output_tokens":3.60,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.60,"reasoning_tokens":3.60}` |
| Llama 3.3 70B Instruct Turbo | `meta-llama/Llama-3.3-70B-Instruct-Turbo` | `worker` | `131072` | `{"input_tokens":0.88,"output_tokens":0.88,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.88,"reasoning_tokens":0.0}` |

Source: [Together serverless models](https://docs.together.ai/docs/serverless-models), [Together DeepSeek FAQ](https://docs.together.ai/docs/deepseek-faqs), [Together models](https://docs.together.ai/reference/models-5)

## Perplexity

| Model name | Model code | Role | Context window | Pricing JSON |
| --- | --- | --- | --- | --- |
| Sonar | `perplexity/sonar` | `web-search` | `200000` | `{"input_tokens":0.25,"output_tokens":2.50,"prompt_cache_hit_tokens":0.0625,"prompt_cache_miss_tokens":0.25,"reasoning_tokens":0.0}` |
| Sonar Pro | `perplexity/sonar-pro` | `web-search` | `200000` | `{"input_tokens":3.0,"output_tokens":15.0,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":3.0,"reasoning_tokens":15.0}` |
| Claude Opus 4.6 | `anthropic/claude-opus-4-6` | `reasoning` | `200000` | `{"input_tokens":5.0,"output_tokens":25.0,"prompt_cache_hit_tokens":0.50,"prompt_cache_miss_tokens":5.0,"reasoning_tokens":25.0}` |

Source: [Perplexity models](https://docs.perplexity.ai/docs/agent-api/models), [Sonar Pro](https://docs.perplexity.ai/docs/sonar/models/sonar-pro)

## xAI

| Model name | Model code | Role | Context window | Pricing JSON |
| --- | --- | --- | --- | --- |
| Grok 4.20 | `grok-4.20` | `reasoning` | `2000000` | `{"input_tokens":0.0,"output_tokens":0.0,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.0,"reasoning_tokens":0.0}` |
| Grok 4.20 Reasoning | `grok-4.20-reasoning` | `reasoning` | `2000000` | `{"input_tokens":0.0,"output_tokens":0.0,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.0,"reasoning_tokens":0.0}` |

Source: [xAI models and pricing](https://docs.x.ai/developers/models?cluster=us-west-1), [xAI overview](https://docs.x.ai/docs)

## NVIDIA

| Model name | Model code | Role | Context window | Pricing JSON |
| --- | --- | --- | --- | --- |
| Nemotron 3 Super 120B A12B | `nemotron-3-super-120b-a12b` | `planner` | `1000000` | `{"input_tokens":0.0,"output_tokens":0.0,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.0,"reasoning_tokens":0.0}` |
| Qwen3.5 122B A10B | `qwen3.5-122b-a10b` | `reasoning` | `262144` | `{"input_tokens":0.0,"output_tokens":0.0,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.0,"reasoning_tokens":0.0}` |
| Minimax M2.7 | `minimax-m2.7` | `worker` | `202752` | `{"input_tokens":0.0,"output_tokens":0.0,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.0,"reasoning_tokens":0.0}` |
| Ising Calibration 1 35B A3B | `ising-calibration-1-35b-a3b` | `worker` | `131072` | `{"input_tokens":0.0,"output_tokens":0.0,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.0,"reasoning_tokens":0.0}` |

Source: [NVIDIA models catalog](https://build.nvidia.com/models), [NVIDIA NIM API reference](https://docs.nvidia.com/nim/large-language-models/2.0.3/reference/api-reference.html)

## Alibaba / Qwen

| Model name | Model code | Role | Context window | Pricing JSON |
| --- | --- | --- | --- | --- |
| Qwen3.5 122B A10B | `qwen3.5-122b-a10b` | `reasoning` | `262144` | `{"input_tokens":0.0,"output_tokens":0.0,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.0,"reasoning_tokens":0.0}` |
| Qwen3.5 397B A17B | `qwen3.5-397b-a17b` | `reasoning` | `262144` | `{"input_tokens":0.0,"output_tokens":0.0,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.0,"reasoning_tokens":0.0}` |

Source: [NVIDIA catalog entries for Qwen](https://build.nvidia.com/models) and Qwen-compatible hosted providers in `providers.md`

## Local / self-hosted providers

| Provider | Representative model | Role | Context window | Pricing JSON |
| --- | --- | --- | --- | --- |
| LM Studio | `openai/gpt-oss-20b` | `worker` | `128000` | `{"input_tokens":0.0,"output_tokens":0.0,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.0,"reasoning_tokens":0.0}` |
| Ollama | `gemma3` | `worker` | `32768` | `{"input_tokens":0.0,"output_tokens":0.0,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.0,"reasoning_tokens":0.0}` |
| vLLM | `meta-llama/Llama-3.1-8B-Instruct` | `worker` | `131072` | `{"input_tokens":0.0,"output_tokens":0.0,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.0,"reasoning_tokens":0.0}` |

Source: [LM Studio docs](https://lmstudio.ai/docs/api), [Ollama docs](https://docs.ollama.com/api), [vLLM OpenAI-compatible server](https://docs.vllm.ai/en/v0.7.0/serving/openai_compatible_server.html)

## Catalog-driven providers

These providers are listed in [providers.md](./providers.md) but do not expose a single stable first-party public model table in the pages reviewed here. Keep them in the registry and refresh the model list from the provider console or dashboard when you need live production accuracy.

| Provider | Suggested role | Context window | Pricing JSON |
| --- | --- | --- | --- |
| Arcee | `worker` | `null` | `{"input_tokens":0.0,"output_tokens":0.0,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.0,"reasoning_tokens":0.0}` |
| Cerebras | `worker` | `null` | `{"input_tokens":0.0,"output_tokens":0.0,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.0,"reasoning_tokens":0.0}` |
| Chutes | `worker` | `null` | `{"input_tokens":0.0,"output_tokens":0.0,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.0,"reasoning_tokens":0.0}` |
| Hugging Face | `worker` | `null` | `{"input_tokens":0.0,"output_tokens":0.0,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.0,"reasoning_tokens":0.0}` |
| MiniMax | `reasoning` | `228700` | `{"input_tokens":0.30,"output_tokens":1.20,"prompt_cache_hit_tokens":0.06,"prompt_cache_miss_tokens":0.30,"reasoning_tokens":1.20}` |
| Novita | `worker` | `null` | `{"input_tokens":0.0,"output_tokens":0.0,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.0,"reasoning_tokens":0.0}` |
| Venice | `worker` | `null` | `{"input_tokens":0.0,"output_tokens":0.0,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.0,"reasoning_tokens":0.0}` |
| Vercel AI Gateway | `worker` | `null` | `{"input_tokens":0.0,"output_tokens":0.0,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.0,"reasoning_tokens":0.0}` |
| Xiaomi | `worker` | `null` | `{"input_tokens":0.0,"output_tokens":0.0,"prompt_cache_hit_tokens":0.0,"prompt_cache_miss_tokens":0.0,"reasoning_tokens":0.0}` |

## Next pass

If we want this registry to become a live operational source, the next step is to expand each provider section with:

- stable model aliases used in the admin UI
- exact provider-specific pricing deltas where the official docs expose them
- supported capabilities such as embeddings, vision, search, or reasoning
- any provider-specific notes about cache or reasoning tokens
