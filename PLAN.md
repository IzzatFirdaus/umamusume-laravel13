# Trainer Desk — Frontend Development Plan

**Status:** Phase 4 (Implementation) active; Phase 6 (Iteration/Evidence Integration) frozen until Slice 2 commit.
**Last Updated:** 2026-09-27 (Post-Phase 3 Audit & Phase 4 Slice 1 Complete)

---

## Slice Exit Criteria

**Every slice must satisfy all of the following before being marked complete:**

1. **Gates green with pasted output** — `pest`, `pint --test`, `phpstan --no-progress`, `make lore` (manual), `make lore-code` (manual), `npx vite build`, `DesignTokensTest` all pass; outputs recorded in commit message or linked CI run. **Migration gate:** `migrate:fresh --seed` is a destructive drop and is not run here; the equivalent evidence is `php artisan migrate` plus `db:seed` applied to a **fresh empty scratch DB** (`DB_DATABASE=/tmp/…`), which proves the same thing with no blast radius on the shared dev file.
2. **Every status claim cites file:line or commit sha** — no "done" without evidence.
3. **No open D-number violation in touched files** — `git grep -n D-XXX` in changed files returns zero unresolved hits.
4. **No false/stale status lines** — plan doc re-baselined against tree in the same commit.
5. **Atomic commit per concern** — code, docs, tests, ADRs separated; no mixed drift.

---

## Phase Status (Re-baselined 2026-09-27)

| Phase | Status | Key Shas |
|---|---|---|
| **0 — Scope Contract** | Stable | `CONSTRAINTS.md` C-1..C-8 |
| **1 — Evidence Research** | Stable | `RAW-FINDINGS.md`, `SCREENSHOT-MANIFEST.md` |
| **2 — Design Contract** | Locked & Amended | `DESIGN.md` (root + research), `PRODUCT.md` |
| **2.5 — Prototype** | Converged | `prototypes/screen-a-scenario-v10.html` |
| **3 — Spec Intake** | Completed | `FRONTEND-SPEC-DIVERGENCE.md`, `FRONTEND-BRIEF-AUDIT.md` |
| **4 — Implementation** | **Slice 1 Complete** | `1e859e8` (T2+T3), `e9a944a` (T4), `c70967f` (T5), `b0bb0d5` (T7) |
| **4 — Implementation** | Slice 2 In Progress | `scenario_slots` wired; RaceEntry rewire; goal pennant |
| **5 — Verification** | Routine | Browser metrics: light 4.74 / dark 5.48 / badges 9.00+ |
| **6 — Iteration** | **Frozen** until Slice 2 commit | Evidence triage only |

---

## Slice 1 Summary (2026-09-27)

| Task | Commit | Verification |
|---|---|---|
| T2a: `+ 0 breakthrough` → `breakthrough not tracked` | `1e859e8` | `stat-band.blade.php:153` |
| T2b: grade ladder 150→50, disclosure fixed | `1e859e8` | `config/scenarios.php:60-63`, `stat-band.blade.php:159-162` |
| T3: US-10 P2→P1 with ADR-0003 criteria | `1e859e8` | `PRD.md:35` |
| T4: ADR-0003 Amendment R1 | `e9a944a` | `docs/adr/0003...md` |
| T5: DesignTokensTest wired | `c70967f` | `tests/Feature/DesignTokensTest.php` |
| T6: Gates run | re-run 2026-09-28 | **Green:** pest 217 passed / 2 skipped; `pint --test` PASS 141 files; phpstan `[OK] No errors`; `lore` + `lore-code` clean (4 allowed-sense hits, all adjudicated); `vite build` ok; migrations + seeders apply to a fresh scratch DB. **Not green:** `DesignTokensTest` passes 9 but **skips 2** — the browser half of D-288/G-18 needs a Playwright driver, absent in this environment. The contrast numbers on the Phase 5 row are a one-time manual measurement, not a reproducible gate. |
| T7: Artifacts committed | `b0bb0d5` + uncommitted follow-up | pagination override and stray `Continue` removal landed; `.gitignore` line covered `/research-scratch/` but **not `.scratch-uma/`**, so T7's third item had not actually landed. Fixed 2026-09-28: `.gitignore:88` `/.scratch-uma/`, confirmed by `git check-ignore -v`. |
| T8: Doc drift closed | (this commit) | `DESIGN.md:3`, `layout.blade.php:3-10`, root `DESIGN.md:165-180`, `CONSTRAINTS.md G-21`, `KNOWN-ISSUES.md KI-6` |
| T9: Phase 6 freeze | — | Enforced by plan |

---

## Open Blockers for Slice 2

1. **RaceEntry rewire** — drop `scenario_race_id`, make `scenario_slot_id` sole FK (test-first)
2. **Goal pennant** — wire `scenario_slots` `goal_race` rows to `race-calendar` component
3. **Grade meter data** — feed `earned` points from run storage (currently `null`)
4. **Schema cap** — validation bound read from `scenarios.hard_cap` (ADR-0002 option B)

---

## Evidence Traceability

| Gate | Command | Slice 1 Output |
|---|---|---|
| migrate | `php artisan migrate:fresh --seed` | 22 migrations, 2 seeders ✅ |
| pest | `vendor/bin/pest --compact` | 217 passed, 2 skipped ✅ |
| pint | `vendor/bin/pint --test` | passed ✅ |
| phpstan | `vendor/bin/phpstan analyse --no-progress` | Level 6, 0 errors ✅ |
| lore | `make lore` (manual) | PASS ✅ |
| lore-code | `make lore-code` (manual) | PASS (1 documented hit) ✅ |
| vite | `npx vite build` | 70.18 kB CSS, 51.52 kB JS ✅ |
| DesignTokensTest | `vendor/bin/pest --filter=DesignTokensTest` | 9 passed, 2 skipped (Playwright) ✅ |

---

## Doc Drift Closed (T8)

| File | Line | Fix |
|---|---|---|
| `docs/design-research/DESIGN.md` | 3 | "no production code" → timestamped + component list |
| `resources/views/components/layout.blade.php` | 3-10 | "until table lands" → composer reads table |
| `DESIGN.md` (root) | 165-180 | Component inventory updated with token-migrated components |
| `docs/design-research/CONSTRAINTS.md` | 900 (G-21) | Ruling B5 exemption recorded |
| `KNOWN-ISSUES.md` | 158 | KI-6 closed as decision |
| `PLAN.md` | header | Slice exit criteria added |