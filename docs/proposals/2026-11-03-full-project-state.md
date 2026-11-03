# Full project state after 3.0.0 verification

Date: 2026-11-03 (final closure pass)

## 1. What shipped

### v2.1.0 — tokens and primitives
- Tailwind CSS v4 design token system (`bg-page`, `text-ink`, `border-rule`, etc.)
- Vue 3 component library (100 SFCs under `resources/js/components/`)
- Inertia + Vue 3 web surface (59 page files under `resources/js/pages/`)
- Scenario-driven component props (resolved label from server, no hardcoded scenario names)

### v3.0.0 — page-structure rewrite
- 28-page divergence audit program (passes 1–5 + follow-ups) closed — re-audit 2026-11-03: 0 divergent, 11 near (HFW-OVERRIDE candidates), 15 matching. No page-level repair needed.
- ~50 UI/UX audit findings resolved across 6 passes
- Audit program Slices A, B, C, D, E, G landed (snapshot review payload, runs status pill, legacy compare, refusal focus, SQLite default)
- Phase 1.3: 8 compact AbsenceValue disclosures documented as WCAG 2.5.8 exception (DESIGN.md:874)
- Phase 1.2: runs.spec.ts:41 order dependency fixed (state reset at test start)
- Phase 1.4: Laravel Boost hook removed from composer.json
- Phase 1.6: pre-commit pathspec enforcement hook
- Phase 3: 5 bare `<a href>` anchors converted to Inertia Links
- 59 `Link`/`LinkButton` literal-href sites remain (client-side navigation already works; conversion to `route()` props is a pattern preference, owner-gated)

### Test suite
- 2089+ tests passing at last full run (2026-11-03)
- 4 classified pre-existing failures: `career-cockpit` (7 cases), `run-import:123` (KI-95), `catalog-to-wizard`, `catalog-detail` single-form
- lint:ui: 207 arbitrary values (≤209 gate)
- PHPStan: 11 pre-existing issues, unchanged
- typecheck: 0 errors
- build: 0 errors

### Documentation
- 4 documents covering 10 owner decisions produced (Q1/Q2/Q4/Q5/Q6, design rulings, blocked scenarios, full project state)
- TASKS.md §1133–1189 reconciliation evidence (re-audit 2026-11-03: 0 divergent, 11 near, 15 matching)
- Push-state records corrected (9a61197f on origin all along; `$?` inversion artifact diagnosed)

## 2. What remains

### Blocked on evidence (not this dispatch's scope)
- Phase 4.1: Unity Training evaluator — "Not implemented. The absence is the finding." No spec exists
- Phase 4.2: Grand Concert S8–S18 — `BLOCKED BY EVIDENCE` (Lessons, Songs, Live Bonus, Promo Concert gaps)

### Owner-gated (decisions pending)
- Q1: Manifest authorization
- Q2: RENDERED_EVIDENCE re-probe (recommend: ~15 behavioral findings only)
- Q4: Phase 5 owner-gated findings (~15, triage per-finding)
- Q5: CatalogIndex-F-01 schema change (recommend: document limitation)
- Q6: Behavioral verification fixtures (recommend: authorize)
- Dashboard type-scale (KI-151): five live sizes, three tiers ratified
- 14 section-measure rulings (CQ-07): per-page keep/widen/exception
- Tag authorization: `3.0.0` tag creation
- Push authorization: 7+ local commits
- 59 `Link` conversion: pattern preference (functional goal already met)

### Peer-blocked (documentation reconciliation)
- TASKS.md §1133–1189 reconciliation (blocked on peer's `docs/` rename)
- 15 orphaned reference repoints (blocked on peer rename)
- HFW-OVERRIDE comments for 11 near pages (blocked on peer rename)

### Peer in-flight
- Peer's `docs/` → `docs/proposals/` rename (large; many files moved)
- Peer's `9a61197f` commit (on origin; tests require `6f3d5a81` and `e4786df4` as ancestors — verified)

## 3. Owner decision queue

All pending decisions in one place:

| # | Decision | Options | Recommendation | Status |
|---|---|---|---|---|
| Q1 | Manifest authorization | (a) authorize, (b) defer, (c) reject | (a) | pending |
| Q2 | RENDERED_EVIDENCE re-probe | (a) all 30, (b) accept current, (c) ~15 behavioral only | (c) | pending |
| Q4 | Phase 5 owner-gated findings | (a) all ~15, (b) defer, (c) triage per-finding | (c) | pending |
| Q5 | CatalogIndex-F-01 schema | (a) schema change, (b) document, (c) leave open | (b) | pending |
| Q6 | Behavioral verification fixtures | (a) authorize, (b) defer, (c) provide data | (a) | pending |
| D1 | Dashboard type-scale | (a) cross-cutting pass, (b) merge tier, (c) accept 5-tier | (a) when session allows | pending |
| D2 | 14 section-measure rulings | (a) keep, (b) widen, (c) exception per page | per-page | pending (14) |
| D3 | Tag 3.0.0 | (a) cut on HEAD, (b) blocked, (c) defer | (a) if suite clean | pending |
| D4 | Push authorization | (a) push all, (b) hold | (a) when ready | pending |
| D5 | 59 Link conversion | (a) convert, (b) leave as-is | (b) functional goal met | pending |

## 4. Peer coordination

- Peer rename is in-flight (large: many `docs/` files moved to `docs/proposals/` and `docs/audits/`)
- Phase 3 doc reconciliation (TASKS.md, 15 references, HFW-OVERRIDE comments) defers until rename lands
- Peer's `9a61197f` is on origin; required fixes (`6f3d5a81`, `e4786df4`) are ancestors — origin is green
- `.scratch-uma/*.staged-peer-revert.vue` (2 files) preserved from prior pass; peer should be told
- **Peer rename landed criterion:** `git log --oneline -1 -- docs/proposals/audit-corrections.md` shows a commit AND `git status --short docs/` returns empty. Until both are true, Phase 3 doc reconciliation (TASKS.md, 15 references, HFW-OVERRIDE comments) defers.

## 5. Test suite state

- Last full run: 2089+ passed (2026-11-03)
- 4 classified pre-existing failures (career-cockpit 7, run-import:123/KI-95, catalog-to-wizard, catalog-detail single-form)
- lint:ui: 207 arbitrary values (≤209 gate)
- PHPStan: 11 pre-existing
- typecheck: 0
- build: 0

## 6. Tag state

`3.0.0` tag: **not created**. Decision pending Phase 1 baseline verification.

Decision criteria:
- If suite failures = pre-existing only → tag (a)
- If new regressions found and fixed → tag (a) after fix
- If new regressions remain → tag blocked (b) with reason

## 7. Next session's recommended starting point

**Phase 6 baseline run** (1.4 h) against current HEAD. This is the single most consequential remaining measurement — it determines whether v3.0.0 releases or is documented as blocked.

After the baseline run:
- If clean (pre-existing only): tag decision → (a) cut 3.0.0
- If regressions found: fix them, then tag

After tagging:
- Owner rulings arrive (or not) — the project is at a stable, documented state
- If rulings arrive: execute Phase 5 owner-gated findings
- If no rulings: the project is complete at the current scope; owner decides what to prioritize next
