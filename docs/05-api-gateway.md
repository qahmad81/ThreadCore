# API Gateway

ThreadCore exposes a first gateway contract under `/api/v1`.

## Authentication
Use a customer API key as a bearer token:

```bash
Authorization: Bearer tc_live_your_key
```

API keys are hashed in the database and plain tokens are only shown once after creation.

## Create Thread

```bash
curl -X POST http://your-app.test/api/v1/threads \
  -H "Authorization: Bearer tc_live_your_key" \
  -H "Content-Type: application/json" \
  -d '{"family_agent":"default","content":"Hello ThreadCore"}'
```

## Post Message

```bash
curl -X POST http://your-app.test/api/v1/threads/{thread_id}/messages \
  -H "Authorization: Bearer tc_live_your_key" \
  -H "Content-Type: application/json" \
  -d '{"content":"Continue the conversation"}'
```

## Commands
- `/whisper` asks without storing the message or response as thread history.
- `/skip` asks without including previous memory/history.
- `/dayend` forces compaction.
- `/forget` marks matching memory as forgotten.
