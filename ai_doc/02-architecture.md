# Architecture

## High-Level Layers
- Public application layer
  Handles the CMS-backed landing page, customer accounts, subscriptions, keys, and basic site content.
- Admin layer
  Manages providers, families, models, CMS pages, and operational settings. The URL prefix is configurable through `THREADCORE_ADMIN_PATH`.
- API gateway layer
  Opens threads, posts messages, resolves defaults, and returns model responses.
- Agent orchestration layer
  Prepares system prompts, memory state, token accounting, and compaction.

## Message Flow
1. A client opens a new thread or posts to an existing thread.
2. API key middleware resolves the customer account and active access key.
3. The gateway resolves the family, provider, model, and any optional inner provider.
4. The system appends the standard request data and relevant conversation history.
5. Token usage is estimated and checked against the internal subscription limits.
6. If needed, the thread is compacted by collecting the latest non-compacted memory plus all eligible raw non-compacted messages in full, sending them to the resolved compaction provider/model with the family compaction prompt, storing the AI-generated compressed response as a new memory item, and marking only the older inputs so they are not resent. Automatic compaction is gated by the active non-compacted context size, not the thread's cumulative lifetime token counters.
7. The model response is returned with token metadata and the request is logged.

## Command Semantics
- `/whisper` and `/skip` still call the model, but their stored turns are marked forgotten so they stay out of future history.
- `/forget` removes matching memory, stores the command as forgotten, and returns a programmatic acknowledgement.
- `/dayend` is a programmatic trigger: it stores no turn and returns a JSON status response, and when it is sent to the create-thread endpoint it short-circuits before a thread row is created while still counting the gateway request. On existing threads it may still trigger internal compaction even before the hard limit is reached, and successful compaction usage is counted separately.
- Forced `/dayend` compaction can use `provider` and `model` request overrides; automatic threshold compaction uses the family compaction settings.

## Compaction Settings
- Each family agent selects a default model and a compaction model in admin, and the provider is derived from the chosen model. The model pickers only show enabled models from enabled providers, while the underlying provider columns stay in sync for runtime routing even though they are not edited separately in the form.
- Each family agent stores a compaction prompt; empty values resolve to `Compacted memory`.
- Compaction sends the latest non-compacted memory together with the complete uncompressed raw message bundle to the resolved provider/model and stores the returned compressed text as the newest memory item.
- If the compaction provider returns empty content, the compaction run is rejected instead of persisting a raw-transcript fallback summary.
- Compacted memory messages record the resolved compaction provider/model metadata for diagnostics, and forced `/dayend` logs should reflect the actual compaction route when one is used.
- Forced `/dayend` compaction failures are returned as structured gateway errors instead of raw 500s.
- Thread message accounting now includes a `cost` column. User turns stay at zero tokens/cost, and AI-generated turns or compacted memories copy cost from provider response metadata when available, otherwise they persist zero. The normalized usage breakdown is kept in message metadata so cache-hit, cache-miss, and reasoning token fields remain available for later analysis. Automatic compaction compares the current active non-compacted context against the family threshold, so a thread can compact again later without being permanently forced over threshold by its historical token totals.

## Provider Strategy
- OpenRouter is the primary starting point for cloud AI.
- Local providers such as Ollama should remain supported.
- Additional providers are introduced through database records, not hardcoded routing.
- Provider adapters normalize provider responses into content, token usage, finish reason, and raw metadata, and OpenRouter-compatible routes should raise explicit credential errors when the configured credential from either env or the provider record is missing or rejected.
- Provider model pricing is stored as JSON on each `provider_models` row, using usage field names such as `prompt_tokens`, `completion_tokens`, `prompt_cache_hit_tokens`, `prompt_cache_miss_tokens`, and `reasoning_tokens` so internal cost calculation stays provider-agnostic. The cost calculator treats prompt cache and reasoning buckets as non-overlapping parts of the bill so totals are not counted twice.

## Design Direction
- Keep the architecture fork-friendly.
- Make provider selection and thread behavior configurable without editing the core flow.
- Preserve a clear separation between human-facing docs and agent-facing docs.
- Do not hardcode the admin URL path in user-facing assumptions; the default is `admin`, but local deployments may change it.
