# Project Structure

## Root
- `AGENTS.md`
  Root entry contract for agents working in this workspace.
- `app/`
  Laravel application code, including models and HTTP controllers.
- `ai_doc/`
  Agent-facing project documentation and local developer preferences.
- `bootstrap/`
  Laravel bootstrap and cache files.
- `config/`
  Laravel configuration, including `threadcore.php` for provider bootstrap settings.
- `database/`
  Migrations, factories, and seeders.
- `docs/`
  Human-facing project documentation and durable planning notes.
- `public/`
  Laravel public entrypoint and public assets.
- `resources/`
  Blade views and frontend resources.
- `routes/`
  Laravel route definitions.
- `storage/`
  Laravel runtime storage.
- `tests/`
  PHPUnit feature and unit tests.
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

## Implemented Application Structure
- `app/Http/Controllers/Auth/SessionController.php`
  Basic local session login/logout.
- `app/Http/Controllers/Admin/ProviderController.php`
  Provider CRUD.
- `app/Http/Controllers/Admin/ProviderModelController.php`
  Provider model CRUD.
- `app/Http/Controllers/Admin/FamilyAgentController.php`
  Family-agent CRUD.
- `app/Http/Controllers/Admin/ApiKeyController.php`
  Admin read-only API key visibility.
- `app/Http/Controllers/Admin/ThreadController.php`
  Admin thread list and conversation detail view.
- `app/Http/Controllers/Admin/SitePageController.php`
  CMS page CRUD.
- `app/Http/Controllers/Api/GatewayThreadController.php`
  Gateway endpoints for creating threads and posting messages.
- `app/Http/Controllers/Customer/`
  Customer dashboard, API keys, usage, profile/password management, and docs.
- `app/Http/Controllers/LandingPageController.php`
  Public landing page renderer backed by `site_pages`.
- `app/Http/Controllers/SitePageController.php`
  Public renderer for published CMS pages at root-level slugs.
- `app/Models/Provider.php`
  Provider records such as OpenRouter and Ollama.
- `app/Models/ProviderModel.php`
  Models available through each provider.
- `app/Models/FamilyAgent.php`
  Family-agent configuration, defaults, and compaction provider/model/prompt settings.
- `app/Models/Thread.php`
  Thread metadata and token counters.
- `app/Models/ThreadMessage.php`
  Thread messages, command markers, and compaction flags.
- `app/Models/SitePage.php`
  CMS page records used for the public landing page and future site pages.
- `app/Models/CustomerAccount.php`, `Plan.php`, `Subscription.php`, `ApiKey.php`
  Internal billing, customer access, and API key records.
- `app/Models/GatewayRequestLog.php`
  Gateway request and provider response metadata.
- `app/Services/Ai/`
  Provider clients and normalized AI responses.
- `app/Services/Gateway/`
  Provider resolution, history building, command parsing, compaction, limits, and token estimation.
- `app/Services/Gateway/ThreadMarkdownExporter.php`
  Shared Markdown rendering for admin and customer thread exports.
- `database/seeders/DatabaseSeeder.php`
  Seeds local admin user, demo customer, plan/subscription, providers, provider models, the default family agent, and the initial landing page.

## Structural Notes
- Keep the docs tree aligned with the actual build state.
- Keep local-only agent preferences outside the shared agent contract.
- Keep procedural state in `ai_doc/` and keep project definition in `docs/`.
