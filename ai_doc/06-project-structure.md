# Project Structure

## Root
- `AGENTS.md`
  Root entry contract for agents working in this workspace.
- `ai_doc/`
  Agent-facing project documentation and local developer preferences.
- `docs/`
  Human-facing project documentation and durable planning notes.
- `.ignore`
  Seeding ignore list for procedural and development-state files.

## Agent Docs
- `ai_doc/00-overview.md`
  Project goal and current scope.
- `ai_doc/01-requirements.md`
  Required stack and functional requirements.
- `ai_doc/02-architecture.md`
  High-level architecture and conversation flow.
- `ai_doc/03-roadmap.md`
  Milestones and current work.
- `ai_doc/05-product-source.md`
  Source-of-truth policy and data ownership.
- `ai_doc/06-project-structure.md`
  This file. Repository structure and responsibilities.
- `ai_doc/07-system-environment.md`
  Runtime assumptions and integration notes.
- `ai_doc/agent_memory.md`
  Fast operational memory for new sessions.
- `ai_doc/agent_local.md`
  Local developer preferences and communication settings.
- `ai_doc/cli-reference.json`
  Placeholder CLI reference for future commands.
- `ai_doc/current-status.md`
  Current project state and resume point for agents.
- `ai_doc/known-operational-issues.md`
  Durable notes for recurring issues and fixes.
- `ai_doc/plans/`
  Folder for execution plans, milestones, and strategy details.
- `ai_doc/plans/build-web-app-plan.md`
  Concrete phased plan for building the Laravel microsaas from the current documentation base.

## Human Docs
- `docs/04-accounts.md`
  Account and subscription notes.
- `docs/README.md`
  Human-facing introduction to the docs folder.

## Future Application Structure
- `app/`
- `routes/`
- `database/`
- `resources/`
- `config/`
- `tests/`

## Structural Notes
- Keep the docs tree aligned with the actual build state.
- Keep local-only agent preferences outside the shared agent contract.
- Keep procedural state in `ai_doc/` and keep project definition in `docs/`.
