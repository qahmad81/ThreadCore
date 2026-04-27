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
- Customer login redirects to `/customer/dashboard`; admin login redirects to the configured admin area.
- The customer dashboard now shows subscription status, plan limits, usage progress, recent API keys, recent gateway requests, and a first API key onboarding form when no key exists.
- API keys are stored as hashes and the plain token is shown only once after creation.
- Internal billing v1 tracks plan limits and subscription usage counters. Stripe is not integrated.
- Gateway requests authenticate with `Authorization: Bearer tc_live_xxx`.

## Notes
- Account and billing records should be part of the application, not part of the docs.
- Provider records should support future CRUD management through the admin layer.
