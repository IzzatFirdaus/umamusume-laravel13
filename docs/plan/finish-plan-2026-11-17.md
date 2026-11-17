# Session 4 finish plan — P7.3 hold package (confirmed findings)

Repo: D:/Projects/umamusume-laravel13 | branch master | HEAD be8893a25879acad04935a3aeb47c5c38ed84517
Dirty: 108 D / 50 M / 49 ?? (peer deletions dominate: jev-inspect/ subproject, docs/audits/). Nothing staged, committed, or pushed this session.
Roadmap: docs/roadmap/implementation-roadmap-2026-11-16.md, phases P4–P7.
Owner decisions delegated to this assistant after research; nothing is staged/committed/pushed unless the owner names the pathspec.
Constraints honored: no git add -A / commit -a / checkout HEAD -- . / restore . in shared worktree; no test deletion or skip; no new dependency; no schema change; no CI change; no Docker; no feature absent from PRD.md. make absent → vendor/bin/phpstan.

## Decision: HOLD package — do NOT cut v3.0.0 tag

Package is HOLD, not tag-ready. Conditions 2 (browser), 4 (BO-004) and 6 (QWEN) are open.
Owner's decision: "record the wait," not "cut the tag."

---

## Gating owner decision — release content onto tag path (prerequisite to any tag)

`git rev-list --left-right --count origin/master...HEAD` → `7 15`: local master **diverged** (7 behind, 15 ahead of origin/master).
Merge base: ae26b79987677ca13a0a29751a568164764a67cc.

The 15 commits local master has that origin/master lacks are docs regens +
`fix(career)` slug + `test(review)` pager + `fix(a11y)` button label — none are S1/S18
release content. **S1 + S18 fixes sit uncommitted in the working tree.** No tag can
contain them until they are committed AND landed on the tag path (worktree from
origin/master + PR pattern, proven by PR #25).

**Owner must decide:** commit S1+S18 onto origin/master before any tag. Until then,
tagging be8893a2 would publish 15 unverified peer commits.

---

## Condition status (re-derived at be8893a2, not inherited from stale d417a9a5 census)

| Cond | Item | Status | Evidence |
|------|------|--------|----------|
| 1 | Full suite | CLOSED (provenance) | `vendor/bin/pest --compact` → **2455 passed / 37540 assertions, exit 0, Duration 370.98s** at be8893a2 + current dirty tree. Quiet box: killed 10 orphaned chrome-headless-shell (children of killed Playwright run PID 37431); php.exe left untouched (can't distinguish peer's artisan serve — no wmic/PPID on this box). Re-run on quiet box before tag (Level 3). |
| 2 | Browser suite | RESOLVED 2026-11-17 (stale-spec blocker cleared; full re-run still owed before tag) | Stale spec identified by measurement: `_screens-indep-training.spec.ts` is gone from disk and was never tracked (its claims died with the file); the live contradiction is `career-preflight-indep.spec.ts`, tracked + clean, red since `ae16ec88` (Phase E2 cut the Career-mode switch; peer commit `4b09b1b6` filed it "known pre-existing red"). Rewritten this session to pin the Phase-E2 absence from the SS1.1.7 walk (steps 1, 2, 5): `Career mode`/`Independent Training`/`Training policy` count 0 + `Create the run` visible. Run session-private (`PLAYWRIGHT_PORT=8311`, scratch DB `.scratch-uma/s4/browser-8311.sqlite`, lock acquired/released): **1 passed (52.1s)**; `npm run typecheck` exit 0. Full `npm run test:browser` sweep still owed before any tag (one Playwright process at a time, serialise with `.scratch-uma/<session>/playwright.lock`). |
| 3 | Static analysis | CLOSED | Pint: only pre-existing `tests/Feature/SnapshotDoorLinkTest.php` single_quote fail (empty diff vs HEAD, outside slice — do not touch; repo-wide pint at tag time would fail, trivial fix queued for release commit). `npm run typecheck` exit 0; `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` → [OK] No errors; `npm run build` exit 0 manifest fresh. |
| 4 | BO-004 / contrast | CLOSED 2026-11-17 (already landed in tree) | The fix is committed: `c3b45049` "fix(a11y): seed the focus-ring colour at rest and lift the dark risk token (BO-011)" moved dark `--color-risk` `#FF4400` → `#FF6F3B` in `resources/css/app.css` (:531), clearing WCAG AA 4.5:1 as text on every surface it is set on (raised 3.66→4.57, panel 4.16→5.19, page 7.19 — measured figures recorded in DESIGN.md's erratum, whose +15-line doc half sits uncommitted as peer work, do not adopt). Enforcement: `tests/Feature/DesignTokensTest.php` "keeps --color-risk legible as text on every surface it is set on" (committed, clean). Earlier "4.15:1 OPEN" figure in this row was stale — superseded by the commit provenance above. |
| 5 | Artwork mirror | CLOSED | Clean clone `.scratch-uma/s4/clean-clone`: composer install 81 pkgs exit 0, npm install exit 0, npm run build exit 0 manifest fresh, migrate+seed exit 0, `php artisan uma:fetch-art --dry-run` exit 0 — card_portrait 263/263, support_thumb 559/559, 0 unresolved. `ArtworkSlot.vue` renders nothing for null url (DESIGN.md §4.7). |
| 6 | QWEN four-claim check | CLOSED 2026-11-17 (tracked; claims verified; one falsified block corrected by erratum) | Ruling: track QWEN.md as an onboarding doc. Claims verified by read/measure: CI exists (3 workflows, `.github/workflows/`) and runs no quality gates (only `npm run build` in `nativephp-windows.yml:92`, `release.yml:70`) — both true; image uploads cut (0 upload routes in `routes/api.php`, 6 GET-only endpoints) — true; artwork mirror manual — true; ADR range 0001-0030 — true; counts (62 pages / 119 components / 125 browser specs / 80 migrations) within drift of stated figures. Docker contradiction already ruled at `README.md:30`: `compose.yaml` is stock Laravel Sail (MySQL/Redis), **not** the supported DB path; Sail is dev-only in `composer.json` (`require-dev`), and QWEN's "Deliberately Absent: Redis, MySQL/PostgreSQL" describes the app's database truth, not the unused compose file — no contradiction against product truth. Falsified block corrected: "Measured gate status (2026-11-16, HEAD 69488c1f)" claims `npm run typecheck` exit 1 and phpstan exit 1; both measured **exit 0** at `be8893a2` this session. Fixed by dated erratum appended below that block in `QWEN.md` (original rows preserved verbatim per erratum law). `python tools/md_fence_check.py QWEN.md` → 0 invalid fences. |
| 7 | BO findings ledger | CLOSED | SLICE-RECORDS.md BO ledger: all BO findings carry terminal states; BO-003/005/012/014 do not exist (0 matches); BO-011/BO-013 fixes committed; BO-004 open under P5.5 with state recorded. |

## Per-entry verdicts — 9 live OPEN KNOWN-ISSUES entries (index/body cross-checked at be8893a2)

| KI | Verdict | Basis |
|----|---------|-------|
| KI-104 | STILL OPEN | UnityCupEditor.vue nested `<details>` at :78/:80/:168 + Cockpit.vue:757-772 wrapper; fix uncommitted at HEAD. ADR-0027 still Proposed → owner signature pending. Do NOT commit peer fix. Shared with OWNER_RULING bucket. |
| KI-106 | STILL OPEN | Citation ratchet: 270 dead citations (585 GONE vs 708 DOC_CITATION_BASELINE). Not re-measured in S2. Re-measure DocCitationParityTest vs DOC_CITATION_BASELINE before acting. |
| KI-114 | STILL OPEN | Two browser files still drive removed deck picker; 11 red cases. Session-private Playwright pair unmeasured — run before verdict. |
| KI-116 | STILL OPEN | Reached per-card effect values unrecorded (Group A / A-0.4). Needs `SupportCardEffects::forLevel()` — absent; only `atCap()` at SupportCardEffects.php:59. Code-only slice possible now that A-0.5 landed (df624cca). |
| KI-119 | OPEN — blocked on ADR | B-0.6 run-level `current_spirit` column superseded by KI-133 (per-member gauge shape). No column, digest, or writer yet. Needs ADR ruling. |
| KI-127 | OPEN | TeamRaceFacilityPanel bare `<div>`, no `.card`/`CapsuleHeader`, under `team_race_facility` flag. Fix family with KI-127 dead registration + P4.5 dead panels — rule together so dispositions don't contradict. |
| KI-162 | OPEN — peer-owned | Dashboard/Races full-suite-only failures; uncommitted peer refactor (position_label); causality not proven by revert → no slice code change on peer files. |
| KI-163 | OPEN | Four duplicate GoalRow/GoalGroup interfaces kept in agreement by hand. Refactor debt; decision needed. |
| KI-164 | OPEN | SnapshotReviewTest asserts `fields.N` by position. Test-shape debt; decision needed. |
| KI-165 | CLOSED 2026-11-17 (this session) | Fan ladder filled from sourced thresholds — see the KNOWN-ISSUES.md closure note for provenance, the PHP_INT_MAX gap leak caught, and the divergence from plan-defects slice 3.2 recorded. |
| KI-166 | OPEN — peer pair owns | Race Decision 59px overflow at 320px; peer's probe (2026-11-17) refuted the `.card:has(> table.t)` candidate; diagnosis window narrowed, cause unknown. Session 4 does not touch it (pair separation). Not in the original 9 (filed after the finish plan was drafted). |

**Flagged, not in the 9:** KI-136/137 — HEAD=OPEN vs working-tree=TERMINAL (uncommitted peer work); do not adopt; needs evidence before retiring. KI-127 + P4.5 dead panels = one decision family.

**Census:** current measured OPEN 121 / CLOSED 208 / FIXED-IN-TREE 9 / TERMINAL 26 at be8893a2. Stale d417a9a5 census (114 entries / 53 body-open) NOT averaged; both recorded with definitions and measurement refs.

---

## Next order (prioritized)

*Updated 2026-11-17 after the delegated decisions executed — items 1-3 done this session, rulings moved to "done" entries above.*

1. **DONE this session (delegated rulings executed):** Cond 2 stale-spec blocker cleared (spec rewritten to the Phase-E2 absence, 1 passed session-private); Cond 4 closed (BO-004 already landed, `c3b45049`); Cond 6 closed (QWEN tracked, erratum appended); KI-165 closed (ladder filled, tests green, register closed).
2. **Owner:** release-content decision — commit S1+S18 onto origin/master (tag-path prerequisite). Name the pathspecs; nothing staged/committed/pushed without owner naming paths.
3. **Before tag:** full `npm run test:browser` sweep on the session-private harness (spec now green; the rest of the suite unre-derived at this HEAD) + full Pest re-run on a quiet box (Level 3).
4. **Still owner rulings (not delegated):** ADR-0027 (KI-104), dead-panel+KI-127 branch, `/api/v1/skills` branch, NFR-19 build-or-defer.
5. Present package as **hold** pending only the tag-path prerequisite and the two pre-tag re-runs.

---

## Verification commands (expected outputs at HEAD be8893a2)

```
grep -oE '^KI-[0-9]+ OPEN' KNOWN-ISSUES.md | sort -u | wc -l   → 9
vendor/bin/pest --compact                                         → 2455 passed / 37540 assertions, exit 0 (quiet box)
vendor/bin/phpstan analyse --no-progress --memory-limit=1G        → [OK] No errors
npm run typecheck                                                 → exit 0
npm run build                                                     → exit 0, manifest fresh
php tools/lore.php                                                → exit 0 (advisory; quote hit count + delta)
php artisan uma:fetch-art --dry-run   (DB_DATABASE=scratch sqlite) → card_portrait 263/263, support_thumb 559/559, 0 unresolved
npm run test:browser                                              → session-private harness; blocked until cond 2 stale spec resolved by owner
```

Browser harness: `PLAYWRIGHT_PORT=8191 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/browser-8191.sqlite npm run test:browser`. For a manual serve pass: `SESSION_DRIVER=file CACHE_STORE=file php artisan serve` (never `array` driver — breaks CSRF).

---

## Owner actions required (name the paths/commands)

1. **Commit paths** for P4 `KNOWN-ISSUES.md` register edits (reconciliation block + closures + KI-104 decision request). Explicit pathspecs only; no `git add -A`.
2. **Stale spec ruling: DONE this session** — `_screens-indep-training.spec.ts` was never tracked and is gone from disk (nothing to rule on); `career-preflight-indep.spec.ts` rewritten to the Phase-E2 absence contract, 1 passed session-private. Full browser sweep still owed before tag.
3. **ADR-0027 ruling on KI-104** (implement / amend / defer).
4. **Dead-panel branch** for P4.5 (compose into scenario + spec vs delete + ruling).
5. **Quiet window / Session 5 end** for P6 walks (or keep gated).
6. **Tag cut** for `v3.0.0` (P7.3) or recorded wait — owner's act only; Session 4 cannot tag or push.
7. **P5 rulings: partially DONE this session** — QWEN.md tracked (verified claims, one erratum); BO-004 closed (already landed). Remaining: `/api/v1/skills` branch, NFR-19 build-or-defer.

---

## Explicitly excluded (no work, no tooling)

- Cond 5 and cond 7 re-verification (complete above).
- New dependencies, CI workflows, Docker/devcontainer, image-upload endpoint, schema migrations, ADR changes, features absent from PRD.md.
- Test deletion/skip, `pint --dirty`, `git checkout HEAD -- .` / `git restore .` in shared worktree, adoption/restoration/deletion of peer uncommitted work.
- Full-suite re-run before tag is noted but not executed here (contended-box risk; Level 3 checkpoint).
