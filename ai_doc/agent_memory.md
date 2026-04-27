# Agent Memory

## Purpose
This file is the fast operational memory for any new agent opening ThreadCore in a fresh conversation.

## What This Project Is
ThreadCore is a Laravel-based microsaas for managing AI threads, families, providers, customer access, subscriptions, and a gateway for other applications.

## Current Phase
Laravel v1 microsaas implementation and hardening.

## What Is Already Implemented
- The original seed note has been folded into the requirements.
- The docs and agent-facing documentation folders have been established.
- Laravel 12 has been scaffolded into the repository.
- Basic session login/logout exists.
- Provider, provider model, family agent, thread, and thread message tables exist.
- OpenRouter and Ollama records are seeded from local bootstrap environment values.
- `/` renders a CMS-backed landing page from the `site_pages` table.
- The homepage was recently tightened to use a more consistent typography scale, a smaller hero headline, a lighter capability strip, and a minimal footer line instead of a heavy lower band.
- Published CMS pages other than `landing` are available as root-level slugs such as `/about`.
- The admin URL prefix is configurable through `THREADCORE_ADMIN_PATH`; it currently remains `admin`.
- Admin routes require both `auth` and an `admin` middleware check.
- `/admin/providers` shows seeded provider records for authenticated users.
- Admin CRUD exists for CMS pages, providers, provider models, and family agents.
- Family agent admin forms now include an optional description field.
- Admin threads now have a list view action and a conversation detail page that shows thread metadata and saved messages.
- Customer dashboard, API key management, usage, profile/password management, and gateway docs exist. The dashboard includes onboarding for first API key creation, usage progress, recent API keys, and recent gateway requests. The customer docs page now includes an active workspace summary plus thread creation and thread reply examples.
- The customer docs page now shows active family agents above the create-thread example, with name, code, description, default route, and context length.
- The customer docs page now also lists the supported gateway commands: `/whisper`, `/skip`, `/dayend`, and `/forget`.
- Customer threads now have their own list and detail views, and the detail route is scoped to the signed-in customer's account.
- Customer threads now paginate instead of truncating at 100, and the docs workspace summary uses full count queries instead of the latest five relations.
- Customer login redirects to `/customer/dashboard`; admin login redirects to the configured admin area.
- Customer accounts, internal plans/subscriptions, API keys, gateway logs, and request usage counters exist.
- Gateway endpoints exist at `/api/v1/threads` and `/api/v1/threads/{public_id}/messages`.
- Gateway API key auth, provider resolution, OpenRouter/Ollama adapters, token estimation, compaction, and command handling exist.
- Gateway/provider requests default to a 20 minute timeout via `THREADCORE_GATEWAY_TIMEOUT_SECONDS=1200`.
- Feature and unit tests cover the landing page, CMS updates, configurable admin path, auth, role-aware login redirects, admin guard, customer profile/password updates, customer API keys, gateway thread/message flow, commands, and resolver precedence.
- Tests also cover public CMS slugs, unpublished page 404s, reserved CMS slugs, and catch-all route regressions.

## Current Verified State
- The project now has runnable Laravel code.
- The app layer is intended to be English-only.
- Developer-specific language and tone are configured separately from the shared project contract.
- MySQL migrations and seeders ran successfully against the local `threadcore` database.
- `artisan test` passes.
- HTTP smoke checks passed for the public landing page and demo customer dashboard.

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
- Implemented the first Laravel build slice: skeleton, auth, provider schema, provider seeding, admin provider verification view, and tests.
- Added the configurable admin path and simple CMS-backed landing page.
- Refined the homepage visual hierarchy to remove the bulky lower footer-like band, reduce the hero headline size, and make typography feel more coherent.
- Locked the admin area behind an `is_admin` check and made the admin bootstrap password explicit.
- Implemented the remaining v1 plan: CMS/admin/customer UI, internal billing records, API keys, gateway endpoints, live provider adapters, request logs, command handling, and tests.

## What Still Needs To Be Done
- Manually verify live OpenRouter and Ollama calls when the user wants to spend provider credits or has Ollama running.
- Replace token estimation with a more exact tokenizer.
- Add richer admin thread/log detail pages.
- Add production deployment, monitoring, and external billing when needed.

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
