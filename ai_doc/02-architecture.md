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
2. The gateway resolves the family, provider, model, and any optional inner provider.
3. The system appends the standard request data and relevant conversation history.
4. Token usage is calculated against the family capacity.
5. If needed, the thread is compacted and marked so compressed items are not resent.
6. The model response is returned with token metadata.

## Command Semantics
- `/dayend` triggers compaction even before the hard limit is reached.
- `/whisper` asks a question without storing it.
- `/skip` asks a question without passing compressed or recent memory.
- `/forget` removes matching memory after compaction.

## Provider Strategy
- OpenRouter is the primary starting point for cloud AI.
- Local providers such as Ollama should remain supported.
- Additional providers are introduced through database records, not hardcoded routing.

## Design Direction
- Keep the architecture fork-friendly.
- Make provider selection and thread behavior configurable without editing the core flow.
- Preserve a clear separation between human-facing docs and agent-facing docs.
- Do not hardcode the admin URL path in user-facing assumptions; the default is `admin`, but local deployments may change it.
