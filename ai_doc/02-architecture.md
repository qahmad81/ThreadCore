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
6. If needed, the thread is compacted by collecting the latest non-compacted memory plus all eligible raw non-compacted messages in full, sending them to the resolved compaction provider/model with the family compaction prompt, storing the AI-generated compressed response as a new memory item, and marking only the older inputs so they are not resent.
7. The model response is returned with token metadata and the request is logged.

## Command Semantics
- `/whisper` and `/skip` still call the model, but their stored turns are marked forgotten so they stay out of future history.
- `/forget` removes matching memory, stores the command as forgotten, and returns a programmatic acknowledgement.
- `/dayend` is a programmatic trigger: it stores no turn and returns a JSON status response, and when it is sent to the create-thread endpoint it short-circuits before a thread row is created while still counting the gateway request. On existing threads it may still trigger internal compaction even before the hard limit is reached, and successful compaction usage is counted separately.
- Forced `/dayend` compaction can use `provider` and `model` request overrides; automatic threshold compaction uses the family compaction settings.

## Compaction Settings
- Each family agent can leave compaction provider/model blank to inherit the default provider/model, or override either value in admin.
- Each family agent stores a compaction prompt; empty values resolve to `Compacted memory`.
- Compaction sends the latest non-compacted memory together with the complete uncompressed raw message bundle to the resolved provider/model and stores the returned compressed text as the newest memory item.
- If the compaction provider returns empty content, the compaction run is rejected instead of persisting a raw-transcript fallback summary.
- Compacted memory messages record the resolved compaction provider/model metadata for diagnostics, and forced `/dayend` logs should reflect the actual compaction route when one is used.
- Forced `/dayend` compaction failures are returned as structured gateway errors instead of raw 500s.

## Provider Strategy
- OpenRouter is the primary starting point for cloud AI.
- Local providers such as Ollama should remain supported.
- Additional providers are introduced through database records, not hardcoded routing.
- Provider adapters normalize provider responses into content, token usage, finish reason, and raw metadata, and OpenRouter-compatible routes should raise explicit credential errors when the configured credential from either env or the provider record is missing or rejected.

## Design Direction
- Keep the architecture fork-friendly.
- Make provider selection and thread behavior configurable without editing the core flow.
- Preserve a clear separation between human-facing docs and agent-facing docs.
- Do not hardcode the admin URL path in user-facing assumptions; the default is `admin`, but local deployments may change it.
