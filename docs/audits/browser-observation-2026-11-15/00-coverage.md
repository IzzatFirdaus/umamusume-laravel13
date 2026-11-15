# 00 — Coverage (seeded re-audit)

> **Method property (carried forward from 2026-11-14 and 2026-11-15).** This instrument detects copy,
> state-dependent labels, count contradictions, computed contrast, geometry, console errors and empty states. It has
> never, across two passes, detected a density, hierarchy, or "reads well" defect. Vision was unavailable on both
> passes. A clean colour or geometry section from this audit does not mean the UI is fine; it means the class of
> defect this instrument can see was not present. Read the register as a measurement audit, not a visual one, until a
> vision-available pass is run.
>
> **Dated supersession 2026-11-15 (owner ruling, follow-up pass).** The vision endpoint is unrecoverable, so this
> register is **permanently measurement-only**: "Vision unavailable. Audit measurement-only. Density and visual
> hierarchy unverified." A "reads well" judgement is out of scope for this instrument from here on, not a pending
> verification step. Where a later pass needs to assert a display row exists and carries the right text, DOM text
> extraction and computed geometry are the evidence class of record.


Observation surface: **Playwright MCP `@playwright/mcp@0.0.38`** (server was down at the start of the prior pass;
it respawned and was live for this one) plus the **direct Playwright API 1.63.0 on Node v26.12.1** for the
screenshot/measure harness — no `*.spec.ts`, no assertion suite. Clean headless Chromium throughout; the owner's
Chrome (Dark Reader) was not used for palette work.
Viewport mechanism: real window resize (`setViewportSize`) at 1440x900 and 390x844. No device emulation.

Walk date: 2026-11-15. HEAD: `d8238c87` (unchanged from the prior pass). Theme: light (default).
**Vision model: STILL UNAVAILABLE.** The auxiliary vision endpoint failed with the same `ChatCompletion` adapter
error on every attempt this pass (one probe at the start, one mid-pass). **This register therefore again contains
zero visual judgements** — no "reads as raised", no "density is oppressive", no aesthetic rulings. Every finding is
a measured or rendered-text observation. See `05-delta-vs-2026-11-14.md` §"What could not be observed".

## Preconditions

| # | Precondition | Result |
|---|---|---|
| 1 | Quiet tree | **PASS with a caveat.** Peer `playwright test` gone; `test-results/` frozen at 23.9 min; the Dev DB and the peer worktree sampled twice 45 s apart and were **stable**. The peer worktree still exists (HEAD `2bfa29d4`, 10 dirty paths) and one file in it (`.github/workflows/nativephp-windows.yml`) was touched 8.7 min before the walk and did **not** move during it. It cannot affect the rendered app: it is a separate worktree and the walk reads the main tree's `public/build`. Recorded, not blocking. |
| 2 | Seeding approved by the owner | **NOT EXPLICITLY GRANTED — and therefore not exercised on the shared file.** The approval question timed out unanswered. I did not seed `database/database.sqlite`. Instead I took a **consistent snapshot** of it (SQLite backup API, read-only source) and seeded the **copy**, served on a **session-private port `:8156`**. This is strictly safer than the approval requested: the shared dev file is byte-identical to how I found it, and no peer work could be collided with (KI-73/KI-80 convention). |
| 3 | Observation surface up | **PASS.** MCP live; direct API used for captures. Versions recorded above. |
| 4 | Vision available or explicitly unavailable | **UNAVAILABLE — explicitly.** Re-tested, not inherited: one probe at pass start, one mid-pass, both `ChatCompletion` adapter errors. |
| 5 | Baseline recorded by observation | **PASS.** Read from rendered pages, never queried — see below. |

## Seeding — exact commands, and the trap that was avoided

Seeded into `.scratch-uma/browser-obs-seeded/seeded.sqlite` (copy of the dev file), via `DB_DATABASE` in the
**process environment** (not as a shell prefix — see the trap below).

```
php artisan db:seed --class="Database\Seeders\RosyDreamsRiceShowerSeeder" --no-interaction
php artisan db:seed --class="Database\Seeders\TrackerVeteransSeeder"           --no-interaction
php artisan db:seed --class="Database\Seeders\UraFinaleRunsSeeder"            --no-interaction
```

Served with: `php -S 127.0.0.1:8156 -t <abs>/public <abs>/vendor/laravel/framework/.../server.php` with cwd=`public`
and `DB_DATABASE` set. **Two traps, both hit and both fixed:**

1. **`DB_DATABASE=… php artisan …` in this host's bash does not set the variable.** Bash parsed it as a command and
   PHP fell back to the dev file — the seeders then ran against a DB that already had the runs' `import_source`
   labels or was the wrong file, and reported `DONE` while inserting **nothing**. Passing the env through the
   process (Python `env=`) is the working form. An owner following the documented shell form would silently get a
   half-seeded DB and a green log. **This is worth a `KNOWN-ISSUES` entry of its own.** (EX-13)
2. **`php -S … -t public <abs router>` must run with cwd=`public`.** Run from the repo root, the router's
   `require index.php` resolved to `<repo>/index.php`, which does not exist → every request 500s with
   `Failed opening required 'D:\Projects\umamusume-laravel13/index.php'`.

**The documented remedy the dispatch warned about was confirmed:** `DatabaseSeeder` calls none of the three run
seeders, so `migrate:fresh --seed` produces 0 careers. The `--class=` form is load-bearing.

## Observed baseline (rendered pages, not queries)

| Surface | Observed | Source |
|---|---|---|
| Dashboard careers | `19 careers` reachable; tile reads `8 veterans` | `/`, `/training-runs` |
| `/training-runs` | **19 rows**, `19 careers · page 1 of 1`; statuses Active ×11 / Completed ×8 | rendered table |
| `/veterans` | **8 veterans** (`8 veteran` in the count line; 12 `<tr>` because rows carry nested cells) | rendered |
| `/review` | `304 pending` on the Dashboard; 29 rows on page 1 | rendered |
| `/support-cards` | `559 catalogued` / `251 cards on [Global]` | rendered |
| `/umamusume` | `72 of 72 trainees` | rendered |
| Skills | `623 on Global` + `1,287 Japan only` = `1,910` | rendered |

This matches the seeded set exactly: 1 pre-existing run (#13) + 1 Rosy Dreams + 17 tracker runs = 19;
8 Completed (runs 15–22) and 8 Veterans. **Nothing was half-seeded.**

## Both-states record — the point of this pass

| Screen | Seeded populated career (run #14, turn 67) | Fresh zero-turn run (run #13) | Verdict |
|---|---|---|---|
| Cockpit | walked — `Career: Rice Shower`, 17 rows, **no console error** | walked — `Career: Admire Vega`, **BO-002 TypeError reproduced** | split: BO-002 is empty-state-only |
| Timeline | walked — 6 entries, `Edit turn 67` door present | walked — empty state, no door | split: populated has the door |
| Training / Races / Skills / Events / Inheritance / Deck | walked, all populated | walked (empty branches) | both |
| Result | walked on **run #15** (Completed, `Career result: Maruzensky`, 10 rows) **and** on run #14 (Active → "still running") | run #13 Active → "still running" | both states of Result |
| Scenario detail | walked — caps, bursts, team, ladder populated | walked (empty) | both |
| Deck | walked — all six slots "Not equipped"; the seeder imports no deck, so this is correct rendering of empty storage (EX-16, withdrawn candidate) | walked — same | both |
| `/veterans` | 8 rows, sort/filter present | 0 rows, empty state | both |
| `/veterans/{id}` | walked — `/veterans/8` (Vodka) and `/veterans/5` (Tokai Teio), 5 `h2` sections each | unreachable (0 veterans) | new coverage |
| `/veterans/compare` | walked (empty-selection state; 8 candidates available) | unreachable | new coverage |
| `/legacy` | 7 rows, one per run with a selection | empty | both |
| `/legacy/{run}` | walked — `/legacy/14` | walked | both |
| `/legacy/compare` | walked | walked | both |

## Screens walked

**57 route-visits across 49 distinct screens, at both viewports — 108 PNGs.** Every screen on the prior coverage
list was walked, plus the new populated surfaces. Verdicts are in `01-findings.md`; every screen not named there
was clean.

Newly reachable (prior pass marked these blocked): Result-with-data, `/veterans/{id}`, `/veterans/compare`,
`/legacy/{run}` populated, populated Cockpit/Timeline/Scenario detail.

Still not walked, and why:

| Miss | Reason |
|---|---|
| `eva01` theme | Out of budget again; `dark` was measured in the prior pass and no finding survived it. Not re-measured here. |
| CSV import preview | Producing a file is a data write, not an observation. Unchanged. |
| Driving run #14 to completion | The dispatch forbids it and the run is `Active` by design. |
| Support-card compare panel | Still no compare affordance on `/support-cards` (BO-012 stands). |
| A Grand Concert / Trackblazer populated career | No seeder produces one; the eight URA runs and one Unity Cup run are the only scenarios available. |
| Peer worktree's own app | A separate worktree; walking it would not describe this tree's build. |

## Tree-state note, added after the walk (dated, not retro-fitted)

The walk finished at **01:04:54**. Between then and the package being written, a **peer session edited
`resources/css/app.css` (01:19:56, +18/−1: seven new `--color-stat-*-ink` tokens, "stat-colour Slice A") and
`tests/Feature/DesignTokensTest.php`, and rewrote `public/build/manifest.json` (01:26:56)**.

This is the KI-70 condition for the second time in one day, and it is recorded rather than smoothed over:

- **The findings are unaffected.** All four new records are markup/copy/arithmetic: BO-013 is a caption *string*,
  BO-015 an absent *word*, BO-016 a sentence contradicting its own band, BO-017 two *counts*. None is colour- or
  layout-dependent, so a palette change cannot create or erase any of them.
- **The colour and geometry claims in `02-cross-cutting.md` describe the build as it stood during the walk**, which
  did **not** contain the new stat-ink tokens. They are true of what was rendered, and they are **not** a statement
  about the palette now on disk. A re-walk would measure different stat text colours.
- **The manifest rewrite landed 22 minutes after my last capture**, and the rebuilt CSS does not yet contain the new
  token value (`#166EAF` appears in none of the three built CSS files), so even the post-walk build has not caught up
  with the peer's edit. Nothing in this package was captured against it.
- **No conflict is created:** the peer owns `app.css` and `DesignTokensTest.php`; this package touches neither. The
  dev DB is untouched (run #13 only, 0 veterans), my seeded copy lives under gitignored `.scratch-uma/`, and the
  only tracked file I edited is `KNOWN-ISSUES.md` (KI-163 appended at EOF, additions-only).

## Coverage holes surviving more than one pass

### The deck builder has never been walked populated

Named here rather than folded into EX-16, because a seeder omission and a coverage hole are different things and
only one of them is the audit's problem.

- **2026-11-14:** the deck builder appeared on the coverage list; that pass had no run with a deck.
- **2026-11-15:** same screen, same result. Run #14 was the only populated Unity Cup career and the seeder imports no
  deck for it (EX-16).
- **2026-11-15 follow-up:** closed out against a *completed* URA run, testing the second door the dispatch named.
  `/training-runs/15/deck`, `/16/deck` and `/22/deck` all render six `Not equipped` slots; `deck_slots` holds zero
  rows for every run, so no seeder in the set imports a deck. **The hole cannot be closed by walking.** It stays
  open until a seeder gains a deck import or the deck builder is exercised by hand off-screen.
- **Consequence:** every claim about the populated deck builder - six cards in place, the friend slot carrying a
  rentable card, ownership badges, the effect summary - is **unverified by observation across three passes**. Do not
  read the deck screen's empty-state copy as evidence about its populated state.
