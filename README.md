# ThreadCore

ThreadCore is a Laravel-based microsaas for AI thread orchestration, provider routing, customer API access, and long-context memory management.

It gives teams one gateway for creating AI conversations, routing them through configurable provider/model resources, tracking usage and cost, compacting long-running context into memory, and exposing a customer-facing API key workflow.

> Built as an AI-assisted product by Ahmad Odeh with Codex as the primary implementation agent.

## What It Does

- Runs a customer-facing API gateway for AI conversations.
- Lets admins manage providers, models, family agents, CMS pages, customers, API keys, threads, and usage.
- Routes each thread through a selected family agent, default model, and optional compaction model.
- Supports OpenAI-compatible providers plus Anthropic, Google, LM Studio, vLLM, and Ollama adapters.
- Tracks AI-generated token usage and internal cost on saved messages.
- Compacts long histories through an AI model instead of resending stale raw context forever.
- Gives customers a dashboard for API keys, usage, docs, profile, password changes, and thread history.
- Uses a CMS-backed landing page and root-level public pages.

## Current Status

ThreadCore is already past the default Laravel skeleton stage. The repository includes:

- Admin authentication with a configurable admin path through `THREADCORE_ADMIN_PATH`.
- Unified resource management at `/admin/resource` for providers and models.
- Provider/model registry seed data mirrored from `docs/providers.md` and `docs/models.md`.
- Provider and model records seeded disabled by default, so operators only enable resources they actually use.
- Family agents with default model, compaction model, context limits, compaction prompt, and optional description.
- Customer accounts, plans, subscriptions, API keys, usage counters, thread views, and Markdown exports.
- Gateway endpoints under `/api/v1`.
- Command handling for `/whisper`, `/skip`, `/dayend`, and `/forget`.
- AI compaction that sends active memory plus raw un-compacted turns to the configured compaction model.
- Feature and unit tests covering the core admin, customer, gateway, resolver, pricing, and compaction behavior.

## Stack

- PHP 8.3+
- Laravel 12
- MySQL
- Blade forms
- PHPUnit
- OpenAI-compatible HTTP providers, Anthropic, Google Gemini, Ollama, LM Studio, and vLLM driver support

## Main Areas

| Area | Purpose |
| --- | --- |
| Public site | CMS-backed landing page and published public pages |
| Admin | Operational control for resources, family agents, pages, customers, keys, threads, and usage |
| Customer | Dashboard, API keys, usage, docs, profile, password, and owned threads |
| Gateway | Bearer-token API for creating threads and posting messages |
| AI layer | Provider clients, provider resolution, usage normalization, pricing, and compaction |
| Memory | Active-history building, forgotten turns, and AI-generated compacted memories |

## Provider Resources

Providers and models are database-managed resources.

The current registry covers providers such as OpenRouter, OpenAI, Anthropic, Google Gemini, DeepSeek, Mistral, Cohere, Groq, Fireworks, Perplexity, xAI, Novita, NVIDIA, Together, Qwen, Alibaba, Hugging Face, Ollama, LM Studio, vLLM, and others.

Human-readable registries live in:

- [`docs/providers.md`](docs/providers.md)
- [`docs/models.md`](docs/models.md)

Fresh installs seed these records as disabled. Enable only the provider accounts and models you actually own or want to route through.

## Gateway API

All gateway routes require a customer API key:

```http
Authorization: Bearer tc_live_your_key
```

### Create A Thread

```bash
curl -X POST http://your-app.test/api/v1/threads \
  -H "Authorization: Bearer tc_live_your_key" \
  -H "Content-Type: application/json" \
  -d '{
    "family_agent": "default",
    "content": "Start a planning conversation for a short story."
  }'
```

### Post A Message

```bash
curl -X POST http://your-app.test/api/v1/threads/{thread_id}/messages \
  -H "Authorization: Bearer tc_live_your_key" \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Continue from the previous answer."
  }'
```

### Optional Gateway Fields

Requests may include:

- `family_agent`
- `content`
- `thread_id`
- `provider`
- `model`
- `inner_provider`
- `encryption_key`

Responses include the thread ID, message ID, model response, provider/model route, token usage, context limit, and compaction status.

## Gateway Commands

| Command | Behavior |
| --- | --- |
| `/whisper` | Calls the model, then stores the turn as forgotten so it does not enter future history |
| `/skip` | Calls the model without recent/compressed memory, then stores the turn as forgotten |
| `/dayend` | Programmatically triggers compaction, stores no user/assistant turn, and returns a JSON status |
| `/forget` | Marks matching memory as forgotten and returns a programmatic acknowledgement |

## Memory And Cost Tracking

ThreadCore focuses accounting on AI calls, not raw user input rows:

- User turns are stored with zero input tokens, output tokens, and cost.
- Assistant turns and compacted memories store token usage from the provider response when available.
- Provider model pricing is stored as JSON on `provider_models`.
- Normalized usage details are kept in message metadata for later analysis.
- Automatic compaction checks active non-compacted context, not lifetime thread totals.
- Compacted raw inputs are marked so they are not resent as history.

## Installation

```bash
git clone https://github.com/qahmad81/ThreadCore.git
cd ThreadCore
composer install
npm install
npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Runtime requirements:

- PHP 8.3 or newer
- Composer
- MySQL
- Node.js and npm

## Key Environment Values

Set these in `.env` for local development:

```env
APP_URL=http://your-app.test
THREADCORE_ADMIN_PATH=admin-path
THREADCORE_ADMIN_PASSWORD=your_admin_password
THREADCORE_DEMO_CUSTOMER_EMAIL=demo@example.com
THREADCORE_DEMO_CUSTOMER_PASSWORD=your_demo_password
THREADCORE_GATEWAY_TIMEOUT_SECONDS=1200
```

Provider credentials may be stored as environment variable names or direct provider-record values depending on how the provider is configured in admin.

## Seeded Access

The seeder creates:

- An admin account using the credential values provided through environment variables
- A demo customer account using the configured demo email and password values
- A default family agent, starter plan, subscription, provider records, provider models, and landing page content

## Useful Commands

```bash
composer install
php artisan migrate --seed
php artisan route:list
php artisan test
php artisan serve
```

## Documentation

- [`docs/README.md`](docs/README.md) - human-facing documentation index
- [`docs/04-accounts.md`](docs/04-accounts.md) - accounts, admin login, demo customer, API keys, and billing notes
- [`docs/05-api-gateway.md`](docs/05-api-gateway.md) - gateway examples and command notes
- [`docs/providers.md`](docs/providers.md) - provider registry
- [`docs/models.md`](docs/models.md) - model registry and pricing JSON notes
- [`ai_doc/`](ai_doc/) - operational memory for agents working on the project

## Tests

Run the test suite:

```bash
php artisan test
```

The suite currently covers admin access, resources, family agents, customer pages, API keys, CMS pages, gateway behavior, commands, provider resolution, pricing, compaction, and thread exports.

## License

MIT License.
