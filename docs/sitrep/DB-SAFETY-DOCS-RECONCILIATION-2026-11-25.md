# DB-Safety Documentation Reconciliation — Check #7 (Technical Documentation Engineer)

**Date:** 2026-11-25
**Role:** Technical Documentation Engineer (owner of acceptance Check #7; source-provenance for #6; WorkBuddy markers for #2)
**HEAD at inspection:** working tree, `master` (uncommitted peer work present; nothing committed by this session)
**Scope:** read-only documentation reconciliation + provenance review. No backend/frontend code changed, no DB mutation, no build/full-suite run.

---

## Check #7 status: PARTIAL

Documentation now matches the implemented, verified behaviour. It does **not** close until the Backend (#1/#4/#5) and Frontend (#3) implementation reports are attached and the full-suite failure triage (`sitrep-2026-11-25-verification-failure.md`, 1,973 failed / 65 unstaged files) is resolved. No full Pest run was executed (doc-only scope, per constraints).

---

## Documents changed

| File | Change |
|---|---|
| `docs/sitrep/DB-SAFETY-ACCEPTANCE-2026-11-25.md` | #7 → PARTIAL with named blockers; added status vocabulary; durable 8-check record confirmed as the single tracker. |
| `docs/sitrep/DB-SAFETY-DOCS-RECONCILIATION-2026-11-25.md` | This report (Check #7 evidence + Check #6 provenance + Check #2 markers). |

AGENTS.md §13 already described the implemented boundary accurately at inspection; no edit required there beyond confirming it matches code (see classification below). No competing tracker created.

---

## Verified executable protections (application-level enforcement)

Each item below is enforced by code that runs inside the app's connection/command lifecycle, confirmed by reading the source and the existing regression tests.

1. **Fail-safe agent boundary (primary).** `AgentSessionGuard::onConnectionEstablished()` listens on `ConnectionEstablished` (wired in `DatabaseGuardServiceProvider::boot()`). A process carrying `CLAUDE_SESSION_ID` or `CODEBUDDY_SESSION_ID` that resolves the canonical `database/database.sqlite` is refused before the first statement, unless (a) isolated role + absolute non-canonical path, or (b) `UMA_OPERATOR_ACKNOWLEDGED=yes`. Regression: `tests/Feature/AgentSessionIsolationTest.php` (subprocess-probed: refusal, misleading-role, operator-ack, trainer-path, launcher isolation, inventory stability).
2. **Refused connection is never cached.** `SafeDatabaseManager::connection()` purges a connection whose establishment threw, bound as `db` in the provider `register()`. Prevents a later `connection()` reusing a refused connection without re-dispatching the event.
3. **Isolated-role write guard.** `DatabaseGuard::assertWriteSafe()` on Eloquent `creating`/`updating` (provider `boot()`); refuses writes to canonical when `UMA_DATABASE_ROLE` is an isolated role.
4. **Destructive-command refusal.** `assertCommandSafe()` on `CommandStarting` refuses `migrate:fresh`/`refresh`/`reset`/`rollback`/`db:wipe` against canonical without the operator-ack pair (interactive shell only).
5. **Boot assertion.** `assertIsolatedContext()` at boot: no config cache, absolute path, not canonical, matches `UMA_EXPECTED_DATABASE` when set.
6. **Session-private launcher.** `tools/agent-run.php` (`composer agent` / `composer agent:tinker`) forces `UMA_DATABASE_ROLE=test`, absolute session-private `DB_DATABASE` under `.scratch-uma/agent-sessions/session-<id>.sqlite`, seeds via read-only `VACUUM INTO`, pre-flights `uma:db-guard`.

## Enforcement terminology (six distinct categories)

Documentation must keep these separate; conflating them overstates protection.

1. **Explicit isolated-role enforcement.** `DatabaseGuard::assertIsolatedContext()` / `assertWriteSafe()` / `assertCommandSafe()` fire only when `UMA_DATABASE_ROLE` names an isolated role (`isolated`, `test`, `browser`, `scratch`, `e2`). Opt-in: a process that declares nothing is treated as the Trainer app.
2. **Agent-marker-based fail-safe enforcement.** `AgentSessionGuard` on `ConnectionEstablished` fires when a process carries `CLAUDE_SESSION_ID` / `CODEBUDDY_SESSION_ID` and resolves canonical — **regardless of declared role**. This protection does **not** depend exclusively on declaring `UMA_DATABASE_ROLE`; the marker alone triggers it. The only ways through are isolation (role + absolute non-canonical path) or `UMA_OPERATOR_ACKNOWLEDGED=yes`.
3. **Expected session database identity.** `UMA_EXPECTED_DATABASE` positive match — **instruction-only** until Backend supplies executable-enforcement evidence. The connection boundary does not depend on it.
4. **Owner-authorised maintenance.** `UMA_ALLOW_CANONICAL_DESTRUCTIVE` + `UMA_OPERATOR_ACKNOWLEDGED=yes` + interactive TTY — the only sanctioned path to a canonical destructive write.
5. **Supported workflow instructions.** `composer agent` / `composer agent:tinker` (`tools/agent-run.php`), Pest `:memory:`, Playwright scratch DBs — documented safe paths, not runtime guards themselves.
6. **Operating-system security boundaries.** Windows ACL identity separation — **not implemented**; app and agents share one Windows identity. Not enforceable in-app (see Remaining limitations).

`SafeDatabaseManager` documentation is **provisional** until Backend confirms the pristine Laravel framework implementation (no vendor patch) for Check #1.

## Remaining limitations (OS-level, not enforceable in-app)

- Raw `new PDO('sqlite:...')`, Python/CLI `sqlite3`, or any external tool opening the file bypass every guard. Boundary is the app connection resolver, not the filesystem.
- Windows finding (2026-11-25): all agent processes, `php artisan serve`, and the owner share one security identity (`LENOVOLEGION\extremerazr`) with inherited `Authenticated Users: Modify` on `database/`. **No universal database protection exists** while app and agents run under the same Windows identity. Stronger separation needs an owner-created low-privilege account + ACL — owner-gated, not applied. AGENTS.md §13.2 records this.

## Classification summary (what AGENTS.md §13 says vs reality)

| AGENTS.md §13 claim | Verdict |
|---|---|
| Fail-safe agent boundary primary, `ConnectionEstablished` | ACCURATE (code + test) |
| SafeDatabaseManager never caches refused connection | ACCURATE (code) |
| `UMA_EXPECTED_DATABASE` instruction-only | ACCURATE |
| Destructive-command refusal list | ACCURATE (config + code) |
| `composer agent` / `agent:tinker` session-private | ACCURATE (composer.json + tools/agent-run.php) |
| Browser suites target `.scratch-uma/sqlite/` | ACCURATE (global-setup.ts) but see Check #3 caveat |
| Canonical inventory `umamusume=135, runs=18, veterans=8` | ACCURATE (pinned in AgentSessionIsolationTest) |
| OS-level not enforceable | ACCURATE |

No discrepancy found between AGENTS.md §13 and the implemented code at inspection.

---

## Check #6 — Source-provenance findings (review only, no backend change)

- **Curated names genuine.** All 28 names in `UmamusumeFactory::$sourceNames` verified present as `"name_en"` in the committed catalog `database/seeders/data/gametora-characters.897e63ea.json`. No Faker word-salad; no invented names.
- **Global eligibility supported — release date populated and already past.** Catalog carries `release_en` (107 populated entries). Every curated name's `release_en` date is populated (a Global release date exists) **and** is already released: the latest `release_en` anywhere in the dataset is `2026-09-28`, earlier than the verification date `2026-11-25`. So each curated identity is an *already-released Global character*, not merely a character with a populated date. Factory rows default `release_status = ReleaseStatus::GlobalReleased`, consistent with the snapshot.
  - **Dataset:** `database/seeders/data/gametora-characters.897e63ea.json`. **Verification date:** 2026-11-25.
- **Fixture classification accurate.** `is_fixture` is a real column (`2026_11_24_130000_add_is_fixture_to_umamusume_table.php`, default `false`, indexed). Factory `definition()` sets `is_fixture => true`; `catalogue()` state opts back to `false`. `is_manual` is a separate column (default `false`, `manual()` state). The two are **not conflated** — migration docblock and AGENTS.md §13.4 both state `is_manual` marks the Trainer's own rows (opposite of the fixture exclusion).
- **`catalogue()` state described accurately.** AGENTS.md §13.4 "A `catalogue()` state opt-in is available" matches `UmamusumeFactory::catalogue()`.
- **Synthetic gameplay not presented as historical.** Factory rows are fabricated trainees; a synthetic career on a genuine trainee is **not** provenance-marked by the column — that distinction is carried by DB isolation (scratch vs canonical), exactly as AGENTS.md §13.4 states. No source doc presents synthetic runs as historical gameplay.
- **Provenance taxonomy preserved.** Published mechanics (`UMAMUSUME_REFERENCE.md`, tier-cited), catalog identities (GameTora export, tier B), client-observed gameplay (`[Rosy_Dreams]Rice_Shower_Unity-Cup.md` §1 frame observations), derived info, synthetic fixtures, and unknown history remain distinct. Unity Cup doc explicitly keeps catalog-sourced vs client-read skill lists separate (§ lines ~513-514) and marks single-source claims (tier B, not promoted to A without a second source).

No external fetch required: every material claim was verifiable against the committed snapshot. No unresolved source disagreement surfaced within this review's scope. Full historical game-domain audit not repeated (per constraints).

---

## Check #2 — WorkBuddy environment findings (for Fullstack)

- **Session markers present:** both `CLAUDE_SESSION_ID` and `CODEBUDDY_SESSION_ID` are set in this Docs session (values not recorded — no session/account identifiers or sensitive values logged).
- **Child-process behaviour:** a child `bash -c` inherits the session marker; `DB_DATABASE` and `UMA_DATABASE_ROLE` are unset in the agent shell (no isolation declared by default). This is the exact trigger the `AgentSessionGuard` fail-safe is built for: an agent process that declares nothing and targets canonical is refused.
- Implication for #2: the boundary's agent-marker detection fires for this session's subprocesses unless they isolate via `tools/agent-run.php` or set `UMA_OPERATOR_ACKNOWLEDGED=yes`.
- **Scope limit:** these findings describe the Docs session only. Compatibility is **not** claimed for other agent environments (Backend/Frontend/Fullstack sessions must report their own markers).

---

## Full-suite failure clarification (unresolved integration finding)

- **Reported:** a comprehensive run recorded 1,973 failed tests at HEAD `11207c42` (source: `docs/sitrep/sitrep-2026-11-25-verification-failure.md`, 8m8s, 65 unstaged modified files).
- **Verification status:** unresolved. The count is a single reported figure, **not** 1,973 independent defects. The sitrep itself states the Fullstack estimate (~868) differs and that neither figure is an established failure count.
- **Ownership:** Fullstack classifies failures (environmental vs application regression vs unresolved) and drives cross-agent remediation.
- **Not a blanket prerequisite:** resolution of every unrelated repository-wide failure is **not** an automatic prerequisite for all eight acceptance checks. A check closes on its own cited evidence; the full-suite triage is a separate integration gate owned by Fullstack.

## Outstanding discrepancies assigned to implementation agents

| Owner | Item |
|---|---|
| Backend Architect | Attach #1 (pristine vendor / lifecycle), #4 (launcher + concurrent isolation), #5 (env inheritance/sanitization) implementation reports so #7 can cite executed evidence. Confirm `SafeDatabaseManager` pristine-framework status (Check #1) to make its documentation non-provisional. Provide executable `UMA_EXPECTED_DATABASE` evidence if/when it becomes enforced. |
| Frontend Developer | Attach #3 report: confirm browser isolation is **verified by a run**, not merely by `playwright.config.ts` existence. |
| Fullstack Senior | Classify the reported 1,973-failure run; drive remediation. #7 stays PARTIAL until then. |

---

## Durable eight-check acceptance record

`docs/sitrep/DB-SAFETY-ACCEPTANCE-2026-11-25.md` — already exists, lists all eight checks with requirement / owner / evidence / status / blocker / verification date. This session updated #7 to PARTIAL and added the status vocabulary. Future sessions locate it via `docs/sitrep/INDEX.md` and `AGENTS.md` §3; criteria are not reconstructed from conversation memory. No check marked complete prematurely.