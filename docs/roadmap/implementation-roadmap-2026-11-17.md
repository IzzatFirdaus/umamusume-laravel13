# Trainer Desk — Implementation Roadmap (2026-11-17)

Standalone session pair. This document states **what remains and why**; the paired plan
(`docs/plan/implementation-plan-2026-11-17.md`) states **how**, with exact commands. Every completed item is
excluded with the evidence that excludes it. Every remaining slice ends in code, a spec, or a gate reading —
never in a report alone.

Measured at **HEAD `248e1cbb`** (2026-11-17). Migrations **83 ran, 0 pending**. KI census corrected 2026-11-17 at S2:
**108 index rows / 9 OPEN** (the earlier "31 body-OPEN / 27 body-CLOSED" was a single-pattern artefact).

---

## 0. Measured current state

| Dimension | Measured | Command |
| --- | --- | --- |
| Version | `3.0.0`, pre-release, **untagged** | `VERSION.md:3`; `git tag -l '3.0.*'` empty |
| Tags shipped | `0.1.0`, `2.0.1`, `2.0.2`, `2.1.0`, `2.1.1` | `VERSION.md:3` |
| PHP feature/unit test files | 257 / 13 | `ls tests/Feature/**/*.php \| wc -l` |
| Browser specs | 126 | `ls tests/browser/*.spec.ts \| wc -l` |
| Vue pages / components | 58 / 119 | `ls resources/js/pages/**/*.vue \| wc -l` |
| Migrations | 83, **0 pending** | `php artisan migrate:status --no-ansi` |
| ADRs | 30 (`0001`–`0030`) | `ls docs/adr/0*.md \| wc -l` |
| KI register | **108 index rows / 9 OPEN** (measured 2026-11-17, S2); superseded reading: 31 body-OPEN / 27 body-CLOSED | three patterns together: `^\*\*State:\*\* OPEN`, `^\*\*Status:\*\* OPEN`, `^\*\*Status: OPEN` — one pattern measures the pattern, not the file |
| Walk-9 (Unity Cup, Rice Shower) | Completed 2026-11-17: F=0, E=3, U=7, 0 blocking | `ux-walkthroughs/rice-shower-unity-cup-ux-walk-9.md` §3b–§10b |
| Capability matrix gaps | November event, finals UI, owned-skill acquisition, card levels, roster | `audits/capability-matrix/rice-shower-unity-cup-capability-matrix.md` |

**Known hazards.** `phpunit.xml` forces `DB_DATABASE=:memory:` — a green PHP suite proves nothing about
`database/database.sqlite` (currently schema-current, 0 pending). Browser suite re-seeds per retry (~23 min);
one process at a time. `npm run build` writes lowercase colour literals — grep built CSS with `-i`. HEAD moves
mid-session on this shared tree; re-derive before attributing any count.

---

## 1. Completed work — excluded, with evidence

Do not re-open any row below.

| Slice | Evidence that excludes it |
| --- | --- |
| Snapshot door (mid-career position) | Walk-9 completed pass: turn 67 stored, read back on 5 screens, timeline `Recorded as entered`; migrations 0 pending |
| Team state write path | `runs.team` form persists all 6 fields + A/A/A/S/A grades, reload-verified in walk-9 S17 |
| Deck builder 6 slots + owned/rented | Walk-9 S18: 6/6 cards by name/epithet, flags persisted; commit `6a44a750` fixed save naming |
| Skill hint-discount ladder | Planner models 10/20/30/35/40% with floored pricing stated on-screen (walk-9 S09) |
| HFW parity repair (32 divergent pages) | 2026-10-30 repair pass, spec re-baselined; `VERSION.md:9` |
| WCAG AA token pairs | `DesignTokensTest` 17 passed (480 assertions) |
| Dark risk token + focus ring (BO-011) | `--color-risk` in `resources/css/app.css:531`; 3 ratios ≥ 4.5:1 |
| QWEN.md truth | commit `3cbbd8b9`, census 691 ≤ baseline 708, parity test 2 passed |
| FR-E scope + NFR-19 | commits `9638ee4b`/`20dfeb38` record six-endpoint scope and terminal deferral |
| Session 1 P3 fixes | commit `e1253e94` changelog entry |
| Roadmap R2 classification (21 issues) | commit `255b0c30` |

---

## 2. Dependency graph of what remains

```
S1 (walk-9 FIX batch)  ──┐
S2 (KI register truth) ──┼──> S3 (browser suite truth) ──> S4 (release bar) ──> S5 (tag hand-off)
S6 (scenario walks) ─────┘         (S6 independent, feeds S4 condition 7 completeness)
S7 (owner-scope ADR items) — parallel, gates nothing until ruled
```

---

## Phase S1 — Walk-9 remediation batch (R8 FIX candidates)

**Goal.** Close the three experiential findings and the three small unrepresented-data findings from the
completed walk; the two medium ones (U-02, U-07) land as their own slices. Register:
`audits/reconciliation-rounds/rice-shower-unity-cup-round8-reconciliation.md`.

| Slice | Finding | Deliverable | Files | Acceptance |
| --- | --- | --- | --- | --- |
| S1.1 | E-01 duplicate `Skills` label | Run-rail control renamed/`aria-label`led `Skills planner` | `resources/js/pages/Career/Cockpit.vue:666` region | First-click navigation test asserts `/training-runs/{id}/skills`; no accessible-name collision on the cockpit |
| S1.2 | E-02 snapshot door unlinked | Dashboard + Careers list each carry a `Start from existing snapshot` link to `career.snapshot` | `resources/js/pages/Dashboard.vue`, `resources/js/pages/Runs/Index.vue` | Browser assertion: link href `/career/snapshot` present on both surfaces |
| S1.3 | E-03 ceiling label says `Scenario` | Settings ceiling labels read `Run ceiling (scenario floor N)` | `resources/js/pages/Career/RunSettings.vue` | Feature test asserts new label; cockpit `floor`/`cap` strip unchanged |
| S1.4 | U-03 snapshot lacks rarity/potential + full scenario label | Snapshot setup adds `Rarity` (★1–★3) and `Potential Level`; scenario picker prints full label `Unity Cup: Shine On, Team Spirit!` | `Career/Snapshot/Setup.vue`, `Review.vue`, `SnapshotController`, scenario label map | Snapshot feature test round-trips ★3/2; review lists them Known; run #36-class row carries them |
| S1.5 | U-05b goal rows lack fan criteria | Goals board prints each goal race's `FANS NEEDED` beside the state | `Cockpit` goals board component | Browser assertion: Nikkei Sho row shows `2,000`-class threshold from calendar |
| S1.6 | U-02 owned skills at snapshot | Post-create owned-skills editor on the run writing through `runs.skills.sync` (existing endpoint `routes/web.php:238`), `acquired` status default for snapshot entry | `Career/SkillsPlanner.vue` add-skill writer (exists per commit `9cc21b8f`), snapshot flow hand-off link | Feature test: snapshot → add 11 named skills → planner shows 11 recorded; browser assertion on one |
| S1.7 | U-07 card levels | Per-slot `Level` input on the deck, persisted per slot; deck analysis unchanged | `Support/Builder.vue` slot panel, deck save path, migration if no column | Feature test: set 35/30/30/50/30/35, reload, values persist; migration `down()` verified |

**Order.** S1.1–S1.5 are copy/link/small-field work (one day). S1.6–S1.7 medium. S1.4 needs a migration only if
snapshot payload columns don't exist — verify first (`training_runs` already carries rarity? check
`Rarity` cast before adding).

**Phase acceptance.** R8 rows E-01..E-03, U-02, U-03, U-05b, U-07 flip to ALREADY-FIXED in the R8 register with
command evidence. Every slice ships a test that would fail without it.

---

## Phase S2 — KI register truth: 31 open entries become verdicts *(2026-11-17 erratum: the measured denominator is **108 index rows / 9 OPEN**, not 31. The 31 was an artefact of one grep pattern; see S2 delivery at `docs/audits/reconciliation-rounds/rice-shower-unity-cup-round8-s2-register-truth.md`.)*

**Goal.** No `OPEN` entry survives without a landed fix or a recorded terminal classification.

| Slice | Work | Acceptance |
| --- | --- | --- |
| S2.1 Verify-then-bucket | Probe each remaining OPEN entry at HEAD with one command each; assign **FIX** / **TERMINAL** / **ALREADY-FIXED**. S2 closed this for 15 of the then-24 OPEN rows; **9 remain** (KI-104, 106, 114, 116, 119, 127, 162, 163, 164) |
| S2.2 FIX bucket lands | One commit per entry, smallest change; each with a failing-first test | Each entry's register status flips to CLOSED with closure commit |
| S2.3 TERMINAL bucket recorded | Owner-scope entries (e.g. KI-150 page-level sticky) get their recorded classification in the entry body | Register parse: 0 `Status: OPEN` without a disposition |

**Known measured inputs at this HEAD.** KI-137/KI-136: `full-career-journey.spec.ts` locator fix is
`details[data-region="team-state"]` (peer-owned file — coordinate). KI-160: Cockpit 88px overflow at 640px,
target `NextDecision.vue`. KI-159/KI-141: `title=`-only reasons on deck/build-target steps. KI-145: `lint:ui`
debt 282 vs baseline — pay down or enumerate.

**Phase acceptance (amended 2026-11-17).** Every index row carries a current-state verdict, and the count is derived from the index (108 rows), not from a body-pattern grep. Measured at S2 close: **9 OPEN**, each with a stated reason; zero index/body contradictions unaccounted for.
owner-ruling reference).

---

## Phase S3 — Browser suite truth

**Goal.** Zero failing specs, or a small named set with filed reasons.

| Slice | Work | Acceptance |
| --- | --- | --- |
| S3.1 Triage gate | For each failing spec: grep the asserted literal against `resources/js`. Absent → stale (rewrite or delete, keeping counter-specs like `career-preflight.spec.ts` untouched); present → isolated re-run | Per-spec verdict table |
| S3.2 Isolated re-runs | One process, session-private port + DB (`PLAYWRIGHT_PORT=81xx PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/browser-81xx.sqlite`, `--workers=1`) | Each re-run classified: regression / contention / fixed-between / stale |
| S3.3 `full-career-journey.spec.ts` red | Fix the two-`details` locator (`details[data-region="team-state"]`), **coordinate — peer-owned file**; this single red blocks the 3.0.0 tag per `VERSION.md:9` | Spec passes isolated |

**Phase acceptance.** Full browser sweep green on the session-private harness, or ≤3 named failures each with a
filed KI row and an owner-facing reason.

---

## Phase S4 — Release bar (`v3.0.0` exit)

Seven conditions, each a command:

| # | Condition | Command | State at `248e1cbb` |
| --- | --- | --- | --- |
| 1 | PHP suite green, skips explained | `php vendor/bin/pest --compact` | pending re-run at this HEAD (2 known skips, both `markTestSkipped`) |
| 2 | Browser suite green | session-private full sweep | **blocked by S3** |
| 3 | Pint/PHPStan/typecheck/build clean | `vendor/bin/pint --test --format agent <paths>`; `vendor/bin/phpstan analyse --no-progress --memory-limit=1G`; `npm run typecheck`; `npm run build` | pending |
| 4 | WCAG AA tokens | `php vendor/bin/pest --filter=DesignTokensTest` | **green** (17 passed) |
| 5 | Clean clone renders; offline placeholder | `composer setup` on scratch, load a card detail with mirror empty | pending |
| 6 | QWEN.md contradicts nothing | manual read | **green** at `3cbbd8b9`; re-check if docs moved |
| 7 | BO/KI ledger names every terminal state | register parse | **blocked by S2** |

**Phase acceptance.** All seven conditions hold with quoted output; VERSION.md rows updated by whoever runs them.

---

## Phase S5 — Version truth and tag hand-off

| Slice | Work | Acceptance |
| --- | --- | --- |
| S5.1 | `VERSION.md`/`CHANGELOG.md` agree on `3.0.0` untagged pre-release; Release-commit row says who gates it | Doc rows consistent |
| S5.2 | Present Phase C evidence to the owner | **The owner cuts the tag. The agent does not.** |

---

## Phase S6 — Scenario walks (independent; feeds condition 7 completeness)

| Slice | Scenario | Standard |
| --- | --- | --- |
| S6.1 | **Trackblazer** | Manual UI walk, one line per observable event: what happened / intent / expected vs observed / evidence. Screenshot without a value caption is not evidence; unclear → `unverified` + what is needed |
| S6.2 | **Our Grand Concert**, then **URA Finale** as control | Same standard; URA must not regress |

Defects found go to `KNOWN-ISSUES.md` as readings. **Walks and code changes never share a turn.** Host-limited
QA (screen reader, physical mobile, Safari) recorded once, never forced.

---

## Phase S7 — Owner-scope decisions (parallel; gate nothing until ruled)

| Item | Question for the owner | Where it lands |
| --- | --- | --- |
| U-01 per-ancestor sparks | Schema: per-node spark rows vs single-selection terminal? Needs ADR either way | R8 register U-01 |
| U-06 rounds-as-rows | `preseason_rounds` table vs count-is-truth ruling | R8 register U-06 |
| U-04 roster + overall Team Rank | New sub-model vs turn-event-only path | R8 register U-04 |
| November event / finals UI | In-product or documented-absent (matrix rows say Not implemented) | capability matrix + PRD §6 boundary check — no simulation, no race-outcome prediction |

---

## 3. Roadmap laws

1. No slice closes on assertion — command output in the hand-off is the evidence.
2. A recorded owner ruling is terminal; reversing needs a new written ruling.
3. The baseline is never raised to pass (`DocCitationParityTest.php:26`); the fix is making docs true.
4. `career-preflight.spec.ts` is the record of the Phase E2 cut — the specs contradicting it are the stale ones.
5. Never `pint --dirty` on a shared worktree; one Pest process at a time.
6. Stage explicit paths; peer files are not your slice.
7. Stale evidence is not evidence — re-derive HEAD before attributing any count.

## 4. Progression guarantee

S1 closes 7 of 10 R8 rows in code → S2 reconciles the register (108 rows, 9 OPEN) → S3 unblocks condition 2 → S4 runs seven gates →
S5 hands the owner a taggable tree → S6 makes the release claim rest on walked screens → S7 turns the three
structural gaps into rulings instead of open questions. Every slice changes code, a spec, or a gate result.
