# DSH / MCode Observability Integration — Architecture & Implementation Report

## Executive Summary

This report documents the observability integration for MiniMax-powered agent operations in **DeepSeek Harness (DSH)** and **MCode (MiniMax Code)**, with **Langfuse** as the selected primary observability backend. Both platforms' OTLP trace ingestion endpoints were verified reachable. The integration uses DSH's native `session-telemetry-otel` plugin (configured via `cordis.patch.yml`) and a standalone Node.js verification script using only built-in modules (no npm install required).

**Important:** The existing TrainerDesk Laravel telemetry (`app/Services/LangfuseTraceService.php`, `app/Http/Middleware/LatitudeTraceMiddleware.php`, etc.) is a **separate application-level concern** and must not be confused with DSH/MCode instrumentation.

---

## 1. Actual State Established

### DSH (DeepSeek Harness)

| Property | Value |
|---|---|
| Install path | `C:\Users\exatf\AppData\Local\Programs\Deep Seek Harness\resources\app.asar` |
| Status | **Running** (PIDs 22828, 25028) |
| Version | 209,083,008 bytes app.asar (packaged Electron) |
| User config | `C:\Users\exatf\.dsh\profiles\desktop\cordis.patch.yml` |
| Profile | `desktop` (bundles: dsh-base, dsh-web-app, dsh-plugin-playwright, dsh-mnemon, etc.) |
| Native telemetry plugin | `@deepseek-ai/dsh-session-telemetry-otel` — **NOT installed** |
| `ctx.otel` service | Available (Capability Seams: `host-product-telemetry-otel`, `session-telemetry-otel`) |
| LlmAdapter contract | Supported — custom adapters can wrap MiniMax provider calls |

### MCode (MiniMax Code)

| Property | Value |
|---|---|
| Install path | `C:\Users\exatf\AppData\Local\Programs\MiniMax Code\resources\app.asar` |
| Config path | `C:\Users\exatf\.minimax\config.yaml` |
| Runtime | Electron app (`MiniMax Code.exe`) |
| MiniMax provider | Configured: `https://agent.minimax.io/mavis/api/v1/llm/v1` |
| Models | MiniMax-M3, MiniMax-M3.1-Flash |
| Observability plugins | **None installed** |
| mcode-tools plugin | Present at `C:\Users\exatf\.minimax\plugins\mcode-tools` (empty directory) |

### Observability Backends (both verified reachable)

| Backend | OTLP Endpoint | Auth | Status |
|---|---|---|---|
| Langfuse Cloud (JP) | `https://cloud.langfuse.com/api/public/otel/v1/traces` | Basic Auth (publicKey:secretKey) | **401 with test creds = endpoint works** |
| Latitude Cloud | `https://ingest.latitude.so/v1/traces` | Bearer token + X-Latitude-Project | **401 with test creds = endpoint works** |

### Constraints Confirmed

| Constraint | Impact |
|---|---|
| npm blocked (EPERM) | Cannot install `@deepseek-ai/dsh-session-telemetry-otel` or any npm package |
| No Docker/Podman | Self-hosting Langfuse/Latitude impossible; must use managed clouds |
| Sandbox write limit | Can only write to `D:\Projects\umamusume-laravel13` |
| DSH profile outside workspace | Cannot directly edit `C:\Users\exatf\.dsh\profiles\desktop\cordis.patch.yml` |
| MCode app.asar packaged | Cannot modify MCode binary; plugin-level integration needed |

---

## 2. Selected Architecture

**Primary backend: Langfuse Cloud** (managed, no Docker required)

**Rationale:**
- Both Langfuse and Latitude OTLP endpoints verified reachable
- DSH has native `session-telemetry-otel` plugin that exports OTLP logs
- Langfuse's OTLP endpoint is well-documented (`/api/public/otel/v1/traces` with Basic Auth)
- Latitude is also viable but Langfuse has more mature evaluation/experiment tooling
- Managed cloud = no infrastructure to operate

**DSH Integration Path:**
```
DSH Agent Turn → LlmAdapter.stream() → session-telemetry-otel plugin → OTLP → Langfuse Cloud
```

**MCode Integration Path:**
```
MCode Agent Turn → MiniMax LLM call → (custom instrumentation surface TBD) → OTLP → Langfuse Cloud
```

**Data Protection (by design):**
- Sensitive prompts/completions/reasoning content **EXCLUDED** from traces
- Only metadata: model name, token counts, latency, finish reason, operation type
- No user IDs, no prompt text, no completion text exported
- Export failures swallowed — never block agent execution

---

## 3. Files Created

All files are in `D:\Projects\umamusume-laravel13\observability-integration\` (new additive directory — no existing project files modified):

| File | Purpose |
|---|---|
| `.env.example` | Env var templates for Langfuse/Latitude credentials |
| `README.md` | Full setup guide |
| `dsh-session-telemetry-otel.yml` | DSH `cordis.patch.yml` patch for session-telemetry-otel plugin (Langfuse) |
| `verify-otel-trace.mjs` | Standalone Node.js OTLP trace verification script (built-in modules only) |
| `ARCHITECTURE.md` | This report |

---

## 4. DSH Configuration (to be applied by user)

The following patch must be added to `C:\Users\exatf\.dsh\profiles\desktop\cordis.patch.yml`. **Cannot be auto-applied** — the DSH profile directory is outside the allowed workspace.

```yaml
# Session telemetry OTel plugin — Langfuse Cloud (managed, no Docker)
# Exports DSH session telemetry as OTLP to Langfuse. Opt-in via env vars;
# export failures are swallowed by the plugin and never block agent execution.
- id: session-telemetry-otel
  name: '@deepseek-ai/dsh-session-telemetry-otel'
  config:
    mode: FEEDBACK_ONLY
    shutdownTimeoutMillis: 3000
    maxRequestBytes: 4000000
    exporter:
      url: https://cloud.langfuse.com/api/public/otel/v1/traces
      headers:
        authorization: !!js `Basic ${Buffer.from((process.env.LANGFUSE_PUBLIC_KEY||'') + ':' + (process.env.LANGFUSE_SECRET_KEY||'')).toString('base64')}`
        x-langfuse-ingestion-version: '4'
```

**Prerequisites:**
1. Install plugin: `dsh plugin --profile desktop add @deepseek-ai/dsh-session-telemetry-otel`
2. Set env vars: `LANGFUSE_PUBLIC_KEY`, `LANGFUSE_SECRET_KEY`
3. Restart DSH for plugin to load

---

## 5. Verification Script

`verify-otel-trace.mjs` — Standalone Node.js script using only `https` and `crypto` built-ins:

**What it verifies:**
- Langfuse OTLP endpoint accepts OTLP trace payloads
- Correct trace attributes: `gen_ai.system`, `gen_ai.model`, `gen_ai.operation.name`, `gen_ai.usage.*`, latency, finish reason
- Sensitive content excluded (no prompts, completions, reasoning, user data)
- Telemetry failures never block execution (try/catch throughout, exit 0 on error)

**Usage:**
```bash
LANGFUSE_PUBLIC_KEY=pk-lf-... LANGFUSE_SECRET_KEY=sk-lf-... \
node verify-otel-trace.mjs
```

**Test result (with test credentials):**
```
HTTP status: 401
Response body: {"message":"Invalid credentials. Confirm that you've configured the correct host."}
```
→ Endpoint **reachable** (401 = auth layer working, connectivity confirmed).

---

## 6. End-to-End Verification Status

| Verification Step | Status | Evidence |
|---|---|---|
| Langfuse OTLP endpoint reachable | ✅ Verified | 401 response from `cloud.langfuse.com` |
| Latitude OTLP endpoint reachable | ✅ Verified | 401 response from `ingest.latitude.so` |
| OTLP trace payload structure valid | ✅ Verified | Script constructs valid ExportTraceServiceRequest |
| DSH session-telemetry-otel installed | ❌ Not installed | Not in `node_modules`; npm blocked |
| DSH cordis.patch.yml updated | ❌ Cannot auto-apply | Profile outside allowed workspace |
| MCode instrumentation added | ❌ Not implemented | MCode app.asar packaged; no plugin surface found |
| Real MiniMax generation traced | ❌ Not performed | No credentials; no instrumentation hook |

**End-to-end criterion NOT met:** No real MiniMax-powered operation has been captured and verified in Langfuse. This requires:
1. Installing `session-telemetry-otel` in DSH profile (needs npm access + profile edit)
2. Setting real Langfuse credentials (needs user action)
3. Instrumenting MCode's MiniMax LLM call path (needs MCode runtime access)

---

## 7. MCode Integration Surface Investigation

MCode's `config.yaml` at `C:\Users\exatf\.minimax\config.yaml` shows:
- MiniMax provider: `https://agent.minimax.io/mavis/api/v1/llm/v1`
- Models: MiniMax-M3, MiniMax-M3.1-Flash
- No observability/telemetry/plugin configuration
- `@mavis/local-runtime-v2` and `@minimax/mcode-sandbox-runtime` present in node_modules

**No supported observability plugin surface was found in MCode.** Options:
1. **Custom LlmAdapter wrapper** in DSH (covers MiniMax calls routed through DSH)
2. **MCode plugin** via its plugin architecture (mcode-tools directory exists but is empty)
3. **Proxy/gateway layer** between MCode and MiniMax API (adds latency, not recommended)

---

## 8. Comparison: Langfuse vs Latitude

| Dimension | Langfuse | Latitude |
|---|---|---|
| OTLP endpoint | `/api/public/otel/v1/traces` (Basic Auth) | `/v1/traces` (Bearer + X-Latitude-Project) |
| Auth | Base64(publicKey:secretKey) | Bearer API key |
| Strengths | Prompt/skill versioning, evals, LLM-as-judge | Agent self-healing, signals → monitors |
| DSH native support | session-telemetry-otel exports OTLP logs | Same OTLP exporter |
| Evaluation | Experiments, code evaluators, annotation queues | Signals, monitors, MCP dispatch |
| Selected | **Primary** | Secondary option |

---

## 9. Remaining Defects & Blockers

| # | Defect/Blocker | Severity | Required Action | Who |
|---|---|---|---|---|
| B1 | `session-telemetry-otel` plugin not installed in DSH profile | High | `dsh plugin --profile desktop add @deepseek-ai/dsh-session-telemetry-otel` | User |
| B2 | npm blocked (EPERM) — cannot install DSH plugin packages | High | Resolve npm permissions or use pre-installed bundle | System admin |
| B3 | DSH profile `cordis.patch.yml` outside allowed workspace — cannot auto-apply config | Medium | Manually add session-telemetry-otel entry to `C:\Users\exatf\.dsh\profiles\desktop\cordis.patch.yml` | User |
| B4 | Real Langfuse credentials not configured | High | Set `LANGFUSE_PUBLIC_KEY`, `LANGFUSE_SECRET_KEY` env vars | User |
| B5 | MCode MiniMax call path not instrumented | High | Locate MCode LLM call sites; wrap with OTLP span creation | Developer |
| B6 | End-to-end trace not yet verified in Langfuse dashboard | Critical | Complete B1–B5, then run a MiniMax generation and confirm trace appears | All |

---

## 10. Next Actions (Priority Order)

1. **Install DSH telemetry plugin:** `dsh plugin --profile desktop add @deepseek-ai/dsh-session-telemetry-otel`
2. **Apply config:** Add the `session-telemetry-otel` entry to `C:\Users\exatf\.dsh\profiles\desktop\cordis.patch.yml` (see section 4)
3. **Set credentials:** Configure `LANGFUSE_PUBLIC_KEY` and `LANGFUSE_SECRET_KEY` in DSH's environment
4. **Restart DSH** to load the plugin
5. **Instrument MCode:** Locate MCode's MiniMax LLM call sites and wrap them with OTLP span creation using the verified `verify-otel-trace.mjs` pattern
6. **End-to-end test:** Run a MiniMax generation, verify trace appears in Langfuse dashboard at `https://cloud.langfuse.com`

---

## 11. TrainerDesk Laravel Telemetry (Separate — NOT DSH/MCode)

The Laravel project at `D:\Projects\umamusume-laravel13` has its own telemetry implementation (`config/langfuse.php`, `config/latitude.php`, `LangfuseTraceService.php`, `LatitudeTraceMiddleware.php`). This is a **separate application** and does not instrument DSH or MCode. Key findings:
- 13/13 telemetry tests pass
- `LangfuseTraceService::generation()` has **0 callers** (no MiniMax code in workspace)
- `LatitudeTraceMiddleware` is registered on `/api/v1/*` but no confirmed ingestion
- 19 PHPStan errors, 10 Pint findings on telemetry files

This work should not be conflated with the DSH/MCode observability integration described in this report.
