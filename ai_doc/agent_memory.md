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
- Family agent admin forms now include an optional description field plus compaction provider/model/prompt settings.
- Admin threads now have a list view action and a conversation detail page that shows thread metadata and saved messages.
- Admin and customer thread detail pages now include a Markdown export action for the full conversation.
- Thread Markdown exports now use a simple plain-text-friendly structure without per-message metadata or fenced code blocks.
- Customer dashboard, API key management, usage, profile/password management, and gateway docs exist. The dashboard includes onboarding for first API key creation, usage progress, recent API keys, and recent gateway requests. The customer docs page now includes an active workspace summary plus thread creation and thread reply examples.
- The customer docs page now shows active family agents above the create-thread example, with name, code, description, default route, and context length.
- The customer docs page now also lists the supported gateway commands: `/whisper`, `/skip`, `/dayend`, and `/forget`.
- Gateway commands now have distinct handling: `/whisper` and `/skip` call the model but are stored as forgotten turns, `/forget` is handled programmatically and stored as forgotten, and `/dayend` stores no turn but now returns a JSON status response; on the create-thread endpoint it short-circuits before thread creation while still counting the request and writing a gateway log, and on existing threads it only counts usage when compaction actually runs.
- Customer threads now have their own list and detail views, and the detail route is scoped to the signed-in customer's account.
- Customer and admin thread detail views can export the current conversation as a `.md` download.
- Thread export Markdown is intentionally minimal: title, public ID, and the message bodies only.
- Customer threads now paginate instead of truncating at 100, and the docs workspace summary uses full count queries instead of the latest five relations.
- Customer login redirects to `/customer/dashboard`; admin login redirects to the configured admin area.
- Customer accounts, internal plans/subscriptions, API keys, gateway logs, and request usage counters exist.
- Gateway endpoints exist at `/api/v1/threads` and `/api/v1/threads/{public_id}/messages`.
- Gateway API key auth, provider resolution, OpenRouter/Ollama adapters, token estimation, compaction, and command handling exist.
- OpenRouter-compatible providers can now authenticate from either an env-backed provider field value or a direct token stored in the provider record itself.
- Compaction now sends the latest non-compacted memory plus every eligible raw non-compacted message in full to the resolved compaction provider/model, stores the AI-generated compressed response as the new memory message, marks only the older memory/raw inputs compacted, and leaves the newest memory item non-compacted for the next cycle.
- Compaction now rejects empty provider summaries instead of falling back to a raw-transcript memory item, and forced `/dayend` compaction failures return a structured gateway error instead of a raw 500.
- Compaction writes are now wrapped in a database transaction so provider failures or later persistence errors do not leave partial compacted state behind, successful compaction runs are counted against usage, and forced dayend logs record the resolved compaction provider/model when one is used.
- Gateway/provider requests default to a 20 minute timeout via `THREADCORE_GATEWAY_TIMEOUT_SECONDS=1200`.
- OpenRouter-compatible providers now fail fast with clearer credential errors if the configured credential source is empty or the remote provider rejects that key, and failed first-turn requests no longer leave empty threads behind.
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
- Added configurable family-agent compaction provider/model/prompt settings, removed local message count/content truncation from compaction, and routed compaction through the selected AI provider/model.

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
