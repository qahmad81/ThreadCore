# Agent Memory

## Purpose
This file is the fast operational memory for any new agent opening ThreadCore in a fresh conversation.

## What This Project Is
ThreadCore is a Laravel-based microsaas for managing AI threads, families, providers, customer access, subscriptions, and a gateway for other applications.

## Current Phase
Documentation seeding and architecture definition.

## What Is Already Implemented
- The original seed note has been folded into the requirements.
- The docs and agent-facing documentation folders have been established.

## Current Verified State
- The project is still documentation-first.
- The app layer is intended to be English-only.
- Developer-specific language and tone are configured separately from the shared project contract.

## Authority And Resolution Rules
- `AGENTS.md` defines the shared project contract.
- `ai_doc/agent_local.md` defines local developer preferences.
- The docs should always reflect the current intended design rather than a stale assumption.

## Current Routing Assumptions
- OpenRouter is the default cloud provider direction.
- Ollama remains the local provider path.
- Future providers should be managed through database-backed configuration.

## Important Files To Know First
- `AGENTS.md`
- `ai_doc/agent_local.md`
- `ai_doc/00-overview.md`
- `ai_doc/01-requirements.md`
- `ai_doc/02-architecture.md`
- `ai_doc/03-roadmap.md`
- `ai_doc/05-product-source.md`
- `ai_doc/06-project-structure.md`
- `ai_doc/07-system-environment.md`
- `ai_doc/current-status.md`

## What Was Done Recently
- Created the documentation base for the project.
- Removed the original seed file after moving its content into requirements.
- Added `ai_doc/plans/build-web-app-plan.md` as the concrete phased plan for building the Laravel web app from the documentation base.

## What Still Needs To Be Done
- Scaffold Laravel.
- Configure the local app URL and MySQL database.
- Implement the first schema slice for providers, provider models, family agents, threads, and messages.
- Seed OpenRouter and Ollama provider records from local bootstrap environment values.
- Define the API gateway contracts in more detail.

## Read Order For New Agents
- `AGENTS.md`
- `ai_doc/agent_local.md`
- `ai_doc/agent_memory.md`
- `docs/04-accounts.md`
- `docs/README.md`
- `ai_doc/00-overview.md`
- `ai_doc/01-requirements.md`
- `ai_doc/02-architecture.md`
- `ai_doc/03-roadmap.md`
- `ai_doc/05-product-source.md`
- `ai_doc/06-project-structure.md`
- `ai_doc/07-system-environment.md`
- `ai_doc/current-status.md`

## Documentation Rules
- Update this file after any meaningful change in implementation status, routing policy, or current operating assumptions
- Keep this file focused on facts that help a new agent resume work quickly
- If this file disagrees with code or generated outputs, update this file in the same session
