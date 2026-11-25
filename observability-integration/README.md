# MCode Observability — Latitude + Langfuse (Cloud-first, no Docker)

This scaffolding instruments MCode (MiniMax Code) agent turns, skill invocations,
and tool calls so traces/spans flow to an observability backend. It supports
Latitude (agent self-healing) and Langfuse (prompt/skill lifecycle + evaluation).

**No Docker/Podman required** — both platforms have managed clouds. If you later
want to self-host, you can add Docker Compose, but it is not needed to start.

## Layout

```
observability-integration/
  .env.example              # Env var templates (copy to .env, fill real keys)
  README.md                 # This file
  latitude/init.ts          # Latitude SDK init + captureTurn + shutdown
  langfuse/init.ts          # Langfuse v5 SDK init (OTel) + captureTurn + shutdown
  instrumentation/
    agent-turn.ts           # Backend-agnostic wrapper (init/capture/shutdown)
```

## 1. Choose your primary backend

Pick **one** to avoid double ingestion (duplicate storage + cost):

| Goal | Backend | Why |
|------|---------|-----|
| Agent self-healing / signals → monitors → MCP fix dispatch | **latitude** | Built-in flaggers, signals, evaluations, monitors, MCP server + CLI |
| Prompt/skill versioning + experiments + LLM-as-a-judge | **langfuse** | Prompt management, skill management (beta), Experiments, code evaluators |
| Both truly needed | langfuse primary + latitude secondary | Only where self-healing adds unique value |

Set in `.env`:
```
MCODE_OBSERVABILITY_BACKEND=latitude   # or "langfuse"
```

## 2. Install SDKs (in MCode's actual project)

This directory has no `package.json`. Run the install in **MCode's real project root**, not here.

**Latitude:**
```bash
npm install @latitude-data/telemetry openai
```

**Langfuse v5:**
```bash
npm install @langfuse/tracing @langfuse/otel @langfuse/client \
  "@opentelemetry/sdk-node@<0.300" @opentelemetry/api
```

> `@langfuse/langfuse` does **not** exist on npm. The deprecated unscoped
> `langfuse` package is v3. Use the scoped v5 packages above.

## 3. Configure environment

Copy `.env.example` to `.env` and fill in real keys:

**Latitude Cloud** (https://app.latitude.so → Project Settings → API keys):
```
LATITUDE_API_KEY=lat_xxx
LATITUDE_PROJECT_SLUG=your-project-slug
LATITUDE_TELEMETRY_URL=https://ingest.latitude.so/v1/traces
```

**Langfuse Cloud** (https://cloud.langfuse.com → Project Settings → API keys):
```
LANGFUSE_SECRET_KEY=sk-lf-xxx
LANGFUSE_PUBLIC_KEY=pk-lf-xxx
LANGFUSE_HOST=https://cloud.langfuse.com   # or us/jp/hipaa region
```

Never commit `.env`. Keys live in environment variables only.

## 4. Wire into MCode's agent entrypoint

Call `initObservability()` once at app startup, then wrap agent turns with
`captureTurn()`:

```ts
import { initObservability, captureTurn, shutdownObservability } from "./observability-integration/instrumentation/agent-turn";

// At app entrypoint:
initObservability("latitude"); // or "langfuse"

// Around an agent turn / skill / tool call:
const result = await captureTurn(
  "minimax-completion",
  async () => {
    return await minimax.chat.completions.create({ ... });
  },
  {
    userId: "user-123",
    sessionId: "session-abc",
    tags: ["skill:summarize", "model:minimax"],
  },
);

// On app teardown / process exit:
process.on("beforeExit", async () => {
  await shutdownObservability();
});
```

## 5. Verify traces appear

- **Latitude:** Open https://app.latitude.so → your project → Traces. Look for the capture name you used.
- **Langfuse:** Open https://cloud.langfuse.com → your project → Traces. Look for the observation name.

If nothing appears, check the backend dashboard and your app logs for export errors (all export failures are logged and swallowed — they never fail the request).

## 6. Security

- No API keys are hardcoded anywhere in this scaffolding.
- `.env` is gitignored / untracked — do not commit it.
- `.env.example` contains only empty placeholders.
- If you self-host later, pin Docker image tags (`LAT_IMAGE_TAG=X.Y.Z`) instead of `latest`.

## What's intentionally deferred

- **Self-hosted Docker Compose** — not applicable on this system (no Docker/Podman). Cloud endpoints are configured by default.
- **MCode hook points** — where to call `initObservability()` inside MCode's internals is inferred, not verified against MCode source (hook surface not publicly documented).
- **SDK installation** — run `npm install` in MCode's actual project before importing these modules.
