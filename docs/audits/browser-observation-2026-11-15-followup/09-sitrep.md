# 09 — Sitrep: Phase 2 / 3 / 5 dispatch

**As of** HEAD `c29e4f32` · branch `master`, ahead of `origin/master` by 4 · working tree carries 34 modified/untracked
paths from other workstreams (none staged by this dispatch). This file is untracked, in the untracked audit directory.

## Status in one line

Phase 2 (R6-48), Phase 3 (slices 3.1–3.4) and Slice 5.1 are **committed and green on their targeted specs**. Slices
5.2, 5.3 and 5.9 are **committed**. Slices 5.4–5.8 and 5.10 are **open**. The run-34/35 DOM evidence pass is
**blocked** on a server relaunch (a relative `DB_DATABASE` is refused by `DatabaseGuard`).

## Gates

| Gate | Ruling | Recorded in |
|---|---|---|
| R6-48 | Column approved — Team Rank is a status readout, not a performance record | commit `2e454119`; `08-r6-48-review-prep.md` |
| BO-016 | 11 combined (6 spirit + 5 extreme); "2 more" is the Gold Lv3 target | commit `2e454119`; copy in `fc5aa9d3` / `abfd1e5f` |
| Vision | Unavailable; audit is measurement-only | commit `2e454119`; `00-coverage.md` header |

## Committed slices (all ancestors of HEAD)

| Slice | Commit | Subject |
|---|---|---|
| 2.1 | `a4d5d673` | `team_rank` column on `training_runs` |
| 2.2 | `9a2ecccc` | seeder writes `team_rank = S` |
| 2.3 | `c296511d` | Cockpit renders the rank with its stars qualifier |
| 3.1 | `66cc90e1` | Unity Trainings count row |
| 3.2 | `02997418` | five per-stat team grades |
| 3.3 | `27800fa8` | fan ladder class + Top Star gap |
| 3.4 | `3ee31da8` | league-placement `+30` bonus |
| 5.1 | `fc5aa9d3`, `abfd1e5f` | BO-016: both burst tiers named in the countdown |
| 5.2 | `c29e4f32` | R6-16 trainee-step copy, with a spec that pins the new sentence |
| 5.3 | `a23fd59a` | R7-43/45/46/54 copy pass; carries the R6-22 heading |
| 5.9 | `62f43094` | KI-142 pager word form |
| — | `5869d1a0` | BO-001, BO-008 (veteran half), BO-009 (2 sites), BO-010, BO-013, BO-017 |
| — | `1392e30f` | BO-013 floor/cap restore — reverts `5869d1a0`'s re-flatten |
| — | `2ae2de54` | BO-017 burst split beside the Cockpit aggregate |

## Verification run this session

| Suite | Port | Result |
|---|---|---|
| `unity-cup-panel.spec.ts` + `scenario-panel.spec.ts` | 8181 | **27/27 pass** (2.4 min) — rank gauge, five grades, roster empty states, axe A+AA, 320 px reflow |
| `career-cockpit.spec.ts --grep "names the stat denominator"` | 8601 | **1/1 pass** (47.6 s) — BO-013 floor/cap; DB identity check printed `targets canonical: no` |

**Red — server relaunch refused.** `php artisan serve --port=8188` exited 1 at boot with
`App\Exceptions\DatabaseSafetyViolation`: `DB_DATABASE=.scratch-uma/bo-2026-11-15/browser-8188.sqlite` is a relative
path (`DatabaseGuard.php:260`). The Playwright harness resolves scratch paths itself; a manual `serve` does not.
Relaunch with the absolute form in both variables:

```
DB_DATABASE=D:/Projects/umamusume-laravel13/.scratch-uma/bo-2026-11-15/browser-8188.sqlite
UMA_EXPECTED_DATABASE=D:/Projects/umamusume-laravel13/.scratch-uma/bo-2026-11-15/browser-8188.sqlite
```

## Open slices

### 5.4 — R6-53: notes reader

`training_runs.notes` is written from three screens and read by none (R6-53). Plan: pass `notes` through the
`CockpitController` payload and render a `<details>` panel titled `Notes`, capped at three lines. No commit yet.

### 5.5 — R6-24: support-card scope and pagination

The Dashboard prints `Support cards 559`; the deck picker filters to 251 Global-released and returns `->get()` with no
pagination (`DeckSelectController.php:284`). Plan: annotate `559 catalogued (251 Global)`; paginate the picker at 25.
No commit yet.

### 5.6 — BO-009: residual colons

Seven `N/A<span class="sr-only">: …` sites remain:

- `resources/js/pages/Runs/Index.vue:259` (scenario cell)
- `resources/js/pages/Legacy/Index.vue:95`, `:99`
- `resources/js/pages/Veterans/Index.vue:482`
- `resources/js/pages/Database/ShopItems.vue:206`
- `resources/js/pages/Database/Sources.vue:201`, `:219`

Two further sites were changed by `5869d1a0` to carry **no separator at all** — the position cell in `Runs/Index.vue`
and the ownership cell in `SupportSlot.vue` — so the accessible text now runs together: `N/Ano turn has been logged on
this run yet.` The house pattern (`AbsenceValue.vue`) is `N/A<span class="sr-only">, {{ reason }}</span>`, a comma.

**Decision needed:** normalize all nine sites to the comma form (recommended — it is the component's own documented
accessible name), or drop the sr-only sentence wherever a `title` already carries it.

### 5.7 — BO-010: required marker

`5869d1a0` added the `required` attribute to Purpose / Surface / Style (`BuildTarget.vue:407, :419, :431`). The
attribute is not a visible marker, and the audit asked for one: a marker or a one-line note above the three selects.

**Decision needed:** a visible `*` on the three labels, or the one-line note the numeric block already models
("The fields below start empty rather than at zero.").

### 5.8 — BO-006: Preflight count and run-together

- **Count.** `Preflight.vue:404-428` now derives the sentence from the server warning list, with the rationale in a
  docblock; the empty-draft case reads `5`. The filled-draft contradiction still needs a re-walk before closing.
- **`cardFix this`.** `Preflight.vue:748-760` separates the `Fix this` link from the row summary with `ml-2` only — a
  margin, not a separator. Same class as R7-54, which was fixed one line above with explicit parens.

### 5.10 — BO-008: trainees KPI scope

`5869d1a0` covered the veteran half only. The Dashboard still prints `TRAINEES CATALOGUED 133` against a catalogue
that prints `72 of 72`. Plan: `133 recorded · 72 on [Global]`.

## Tree hygiene

The 34 modified/untracked paths belong to other workstreams (`app.css`, `BaseInput.vue`, `ProvenanceMark.vue`,
`spa.ts`, audit documents, …). Nothing from this dispatch is staged. Every commit listed above is pathspec-clean.

## Next, in order

1. Relaunch `:8188` with the absolute scratch path; capture run-34/35 DOM evidence — rank `S (five stars)` vs
   `not recorded`, the four Phase-3 facts, the BO-016 copy.
2. 5.6 separator decision, then one commit across the nine sites.
3. 5.7 marker decision, then one commit.
4. 5.8 `Fix this` separator, plus the filled-draft re-walk.
5. 5.4 notes reader → 5.5 → 5.10, one commit each.
6. `vue-tsc`, build, full Feature + browser batch, final report.
