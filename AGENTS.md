# Agent Memory Entry
Start here when opening the ThreadCore project in a fresh conversation.

## Required Read Order
- [ai_doc/agent_local.md]
- [ai_doc/cli-reference.json]
- [ai_doc/known-operational-issues.md]
- [ai_doc/current-status.md]
- [ai_doc/00-overview.md]
- [ai_doc/01-requirements.md]
- [ai_doc/02-architecture.md]
- [ai_doc/03-roadmap.md]
- [ai_doc/05-product-source.md]
- [ai_doc/06-project-structure.md]
- [ai_doc/07-system-environment.md]
- [ai_doc/plans/]

## Fast Facts
- Project name: `ThreadCore`
- Project type: Laravel-based microsaas for AI thread and agent orchestration
- Primary app language: English
- Developer communication language: configured in `ai_doc/agent_local.md`
- Human docs folder: `docs/`
- Agent docs folder: `ai_doc/`

## Shared Agent Contract
ThreadCore is a Laravel-based microsaas for managing AI thread conversations, agent families, provider routing, customer accounts, payments, subscriptions, and an API gateway that other applications can call.

## Core Product Shape
- Public layer: customer accounts, billing, subscriptions, API key management, and a simple site/page management surface.
- Admin layer: provider CRUD, family-agent configuration, model defaults, token/capacity settings, and operational control.
- Gateway layer: create threads, post messages, resolve providers/models, and return responses with token accounting metadata.
- AI layer: support OpenRouter by default, while remaining compatible with local or external providers such as Ollama or any provider added to the database.
## Documentation Contract
Treat `docs/` and `ai_doc/` as operational memory, not optional notes.

After every meaningful change, review and update the relevant documents in the same work session:
- Update [docs/04-accounts.md] when billing, subscriptions, API keys, or tenant-account rules change.
- Update [docs/README.md] when the docs folder layout or purpose changes.
- Update [ai_doc/agent_memory.md] when operating assumptions, implementation status, routing policy, or current progress changes.
- Update [ai_doc/agent_local.md] when local developer preferences or communication settings change.
- Update [ai_doc/cli-reference.json] whenever a CLI command is added, removed, renamed, or its usage materially changes.
- Update [ai_doc/known-operational-issues.md] when a runtime failure repeats or receives a durable fix/workaround.
- Update [ai_doc/current-status.md] when the project state, implemented phase, or recommended resume point changes.
- Update [ai_doc/00-overview.md] when the project scope or current phase changes.
- Update [ai_doc/01-requirements.md] when runtime, stack, provider, or deployment assumptions change.
- Update [ai_doc/02-architecture.md] when pipeline stages, gateway semantics, or authority rules change.
- Update [ai_doc/03-roadmap.md] when completed work or next milestones change.
- Update [ai_doc/05-product-source.md] when the source-of-truth policy or import assumptions change.
- Update [ai_doc/06-project-structure.md] when folders, entrypoints, or file responsibilities change.
- Update [ai_doc/07-system-environment.md] when runtime commands, ports, providers, or environment assumptions change.
- Update files under [ai_doc/plans] when execution plans, milestones, or strategy details materially change.

## Mandatory End-Of-Task Review
Before ending any meaningful coding task in this repository, the agent must:
- Re-read [ai_doc/agent_memory.md], [ai_doc/06-project-structure.md], and [ai_doc/07-system-environment.md]
- Update any document that is now stale because of new code, generated outputs, commands, models, or workflow changes
- Update [ai_doc/current-status.md] when the project state, implemented phase, or recommended resume point changes
- Prefer documenting the exact current state over leaving TODO-style ambiguity for later agents

## Working Rule
- Do not finish a substantial implementation, refactor, or pipeline behavior change without checking whether `docs/` and `ai_doc/` must be updated.
- If code and docs disagree, fix the docs in the same session unless the user explicitly asks not to.
- Keep app-facing language in English, and keep developer language configuration in `ai_doc/agent_local.md`.

## Bootstrap Rule
If a file listed under `ai_doc/` is missing, create it from the current project documentation and specialize it for the current workspace before continuing.
