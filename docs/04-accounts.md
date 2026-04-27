# Accounts

## Account Scope
ThreadCore includes customer-facing account management as part of the microsaas model.

## Local Login Details
- Admin login:
  - Email: `admin@threadcore.local`
  - Password: value from `THREADCORE_ADMIN_PASSWORD` in `.env`
- Demo customer login:
  - Email: `customer@threadcore.local`
  - Password: value from `THREADCORE_DEMO_CUSTOMER_PASSWORD` in `.env`
  - Local fallback: `password` when `THREADCORE_DEMO_CUSTOMER_PASSWORD` is not set

## Planned Account Features
- Customer sign-up and authentication
- Subscription management
- Payment status tracking
- API key management
- Usage visibility for thread and provider activity

## Admin Account Features
- Provider administration
- Family-agent administration
- Model default management
- Access and operational oversight

## Current Implementation
- A local admin user is seeded by `DatabaseSeeder`.
- A demo customer account and internal Starter plan/subscription are seeded by `DatabaseSeeder`.
- Basic session login/logout is implemented.
- The admin URL prefix is configurable through `THREADCORE_ADMIN_PATH` and currently remains `admin`.
- Admin access requires both login and the `is_admin` flag.
- Admin CRUD exists for CMS pages, providers, provider models, and family agents.
- The public `/` route renders a simple CMS-backed landing page from the `site_pages` table.
- Customer dashboard, API key management, usage, profile, password change, and gateway docs exist under `/customer`.
- The customer docs page now includes an active workspace summary plus two request samples: creating a thread and replying to an existing thread.
- The customer docs page now shows active family agents above the create-thread example, including agent name, code, description, default route, and context length.
- The customer docs page now also lists the supported gateway commands: `/whisper`, `/skip`, `/dayend`, and `/forget`.
- Customer threads now have a dedicated `/customer/threads` list and `/customer/threads/{publicId}` detail view that are scoped to the signed-in customer only.
- Customer docs workspace totals use full count queries for API keys and threads, while the threads list itself now paginates instead of truncating at 100.
- Customer login redirects to `/customer/dashboard`; admin login redirects to the configured admin area.
- The customer dashboard now shows subscription status, plan limits, usage progress, recent API keys, recent gateway requests, and a first API key onboarding form when no key exists.
- API keys are stored as hashes and the plain token is shown only once after creation.
- Internal billing v1 tracks plan limits and subscription usage counters. Stripe is not integrated.
- Gateway requests authenticate with `Authorization: Bearer tc_live_xxx`.
- Gateway/provider calls use a 20 minute timeout controlled by `THREADCORE_GATEWAY_TIMEOUT_SECONDS` and defaulting to `1200`.

## Notes
- Account and billing records should be part of the application, not part of the docs.
- Provider records should support future CRUD management through the admin layer.
