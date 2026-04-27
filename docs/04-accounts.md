# Accounts

## Account Scope
ThreadCore includes customer-facing account management as part of the microsaas model.

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
- Basic session login/logout is implemented.
- The admin URL prefix is configurable through `THREADCORE_ADMIN_PATH` and currently remains `admin`.
- Admin access requires both login and the `is_admin` flag.
- The first authenticated admin screen is `/admin/providers`, which verifies seeded provider and model records.
- The public `/` route renders a simple CMS-backed landing page from the `site_pages` table.
- Customer accounts, subscriptions, API keys, and billing records are still planned and not implemented yet.

## Notes
- Account and billing records should be part of the application, not part of the docs.
- Provider records should support future CRUD management through the admin layer.
