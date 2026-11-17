# 04 — Updated delta against the 2026-11-14 register

## Register change: **NONE.**

The 2026-11-15 register stands at **four records** — BO-013 (Medium), BO-015, BO-016, BO-017 (Low). Neither Task C
nor Task D produced a new finding:

- **Task C** found the seven `--color-stat-*-ink` tokens declared, built, resolving and **unwired**. No stat-text
  colour changed; no contrast pair regressed; nothing fails that previously passed. The dispatch's own rule applies:
  the prior contrast claims stand, stated in one line in `02-stat-ink-remeasure.md`.
- **Task D** found the deck builder empty on three completed runs and `deck_slots` globally empty. That is a coverage
  hole, not a defect — the screen renders empty storage correctly. Recorded in `00-coverage.md`, not as a finding.

So `05-delta-vs-2026-11-14.md` in the 2026-11-15 package remains accurate **without amendment**. Its four new
records, its twelve prior-ID reconciliations, and its "what the doc comparison turned up" section are unchanged.

## What *did* change, and where

Not findings — rulings and framing. Three documents moved:

| Document | Change | Effect on the delta narrative |
|---|---|---|
| `2026-11-15/04-excluded-findings.md` | EX-12 split into **EX-12a** (walk convention) and **EX-12b** (dev-file state); both **owner-ruled** | The delta's "the shared dev file was not seeded" caveat is now **superseded**: the owner ruled private-copy for the method *and* seeded the dev file once. `:8000` now carries 19 runs / 8 veterans. |
| `2026-11-15/00-coverage.md` | Method-property statement promoted to a **standing header** at the top (A4); new heading **"Coverage holes surviving more than one pass"** (A3) | The delta's EX-08 note is now a first-class header, not an exclusion a reader might skip. |
| `KNOWN-ISSUES.md` | KI-163 gains **trap 3** (`errors="replace"`) | No delta effect; a host-invocation trap, not a product defect. |

## The one correction to the 2026-11-15 delta that this pass makes

The delta said, in "What could not be observed":

> **The shared dev file was not seeded.** Precondition 2's approval was never answered […] If the owner wanted the
> dev file itself populated, that instruction still stands open (EX-12).

**That instruction is now given and executed.** The dev file was backed up
(`.scratch-uma/browser-obs-followup/dev-db-pre-seed-backup.sqlite`), then seeded to **19 runs / 8 veterans**, verified
by reading the DB (not the seed log — KI-163 trap 1). Anyone walking `:8000` from now on sees populated data.

## Register-count check (the dispatch's fourth item from last pass)

Confirmed against the finished file:

- bodies defined: **4** (`BO-013`, `BO-015`, `BO-016`, `BO-017`)
- header claim: **4 records · 4 active · 0 withdrawn**
- severity rows: 1 Medium + 3 Low = 4
- reconciliation table: **12 prior IDs**, all present
- `BO-014`: cited **once**, in the accounting note that explains why the ID is unused. No phantom.

Last pass's "6 records" was a miscount, not a dropped finding. That is settled and is not re-litigated here.

## Method-property statement — still true after this pass

Vision was **re-tested**, not inherited: the endpoint returned the same `ChatCompletion` adapter error. So the count
is now **three passes** with zero visual judgements, and the header's "across two passes" wording is a floor rather
than a ceiling. The next pass should bump it to three.

**What that means for reading this package:** the stat-ink verdict above is a *measurement*, sound and reproducible.
It says nothing about whether the stat rail looks good, and this instrument cannot tell you.

## Session note — BO-002 live repro

Zero-turn wizard run created **run #35** on `:8000` at `2026-11-15 11:35:59 +0800` (scenario Unity Cup, trainee Admire Vega, no target, no parents, no deck; `StartCareerRequest`/Preflight path only — wizard per EX-12b, no seeder). Cockpit `/training-runs/35/cockpit` throws `TypeError: Cannot read properties of null (reading 'warning_message')` — BO-002 confirmed on a fresh zero-turn run.

Screenshots: `followup/screenshots/bo-002-run35-cockpit-desktop-1280x800.png`, `followup/screenshots/bo-002-run35-cockpit-mobile-375x812.png`. Console error captured verbatim. Named `BO-002 repro — zero turns, do not delete`. Recorded in `01-findings.md` under BO-002.
