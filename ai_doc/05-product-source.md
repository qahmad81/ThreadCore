# Product Source

## Canonical Source
- The original seed note has been folded into the requirements documentation.
- Future project data should move into the application database and project docs.

## Primary Data Areas
- Database records for providers, families, threads, billing, and customer access.
- `docs/` for human-facing project documentation.
- `ai_doc/` for agent-facing behavior and local preferences.

## Source Policy
- Provider configuration should be database-driven instead of hardcoded in `.env`.
- OpenRouter is a starting default, not a permanent hard dependency.
- Local-only preferences belong in `ai_doc/agent_local.md`.

## Interpretation Policy
- When docs and future code conflict, update the docs to match the actual design.
- The docs should always describe the current project stage, not the future implementation wish list.
