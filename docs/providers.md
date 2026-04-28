# Provider Registry

This page lists external provider records ThreadCore can manage in the `providers` table.

## Fields

| Name | Slug | Driver | Base URL |
| --- | --- | --- | --- |
| OpenRouter | `openrouter` | `openai` | `https://openrouter.ai/api/v1` |
| OpenAI | `openai` | `openai` | `https://api.openai.com/v1` |
| Anthropic | `anthropic` | `anthropic` | `https://api.anthropic.com/v1` |
| Google Gemini | `google` | `google` | `https://generativelanguage.googleapis.com/v1beta` |
| Alibaba | `alibaba` | `openai` | `https://dashscope.aliyuncs.com/compatible-mode/v1` |
| Arcee | `arcee` | `openai` | `https://api.arcee.ai/api/v1` |
| Cerebras | `cerebras` | `openai` | `https://api.cerebras.ai/v1` |
| Chutes | `chutes` | `openai` | `https://api.chutes.ai/v1` |
| Cohere | `cohere` | `openai` | `https://api.cohere.ai/compatibility/v1` |
| DeepSeek | `deepseek` | `openai` | `https://api.deepseek.com` |
| Fireworks | `fireworks` | `openai` | `https://api.fireworks.ai/inference/v1` |
| Groq | `groq` | `openai` | `https://api.groq.com/openai/v1` |
| Hugging Face | `huggingface` | `openai` | `https://api-inference.huggingface.co/v1` |
| Kilo Gateway | `kilocode` | `openai` | `https://api.kilo.ai/api/gateway/` |
| LM Studio | `lmstudio` | `lmstudio` | `http://localhost:1234/v1` |
| Mistral | `mistral` | `openai` | `https://api.mistral.ai/v1` |
| MiniMax | `minimax` | `anthropic` | `https://api.minimax.io/v1` |
| Moonshot | `moonshot` | `openai` | `https://api.moonshot.ai/v1` |
| NVIDIA | `nvidia` | `openai` | `https://integrate.api.nvidia.com/v1` |
| Novita | `novita` | `openai` | `https://api.novita.ai/openai` |
| Ollama | `ollama` | `ollama` | `http://localhost:11434/v1` |
| Perplexity | `perplexity` | `openai` | `https://api.perplexity.ai/v1` |
| Qwen | `qwen` | `openai` | `https://dashscope.aliyuncs.com/compatible-mode/v1` |
| Together | `together` | `openai` | `https://api.together.xyz/v1` |
| Venice | `venice` | `openai` | `https://api.venice.ai/api/v1` |
| Vercel AI Gateway | `vercel-ai-gateway` | `openai` | `https://ai-gateway.vercel.sh/v1` |
| vLLM | `vllm` | `vllm` | `http://localhost:8000/v1` |
| xAI | `xai` | `openai` | `https://api.x.ai/v1` |
| Xiaomi | `xiaomi` | `anthropic` | `https://api.xiaomi.com/v1` |
| Z.AI | `zai` | `openai` | `https://api.z.ai/v1` |

## Notes

- `base_url` is the key operational field for each provider.
- `driver` is the current adapter label in ThreadCore and may be corrected later.
- `OpenRouter` now uses the shared `openai` driver internally; `openrouter` remains a legacy alias for existing rows.
- OpenAI-compatible providers can keep the `openai` driver even if their hosted API and brand are different.
- Local providers such as `lmstudio`, `ollama`, and `vllm` are listed separately because they usually need custom local endpoints, even though they still speak an OpenAI-style wire format.
