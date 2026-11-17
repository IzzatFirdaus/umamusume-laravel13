# 03 — Code-change plan (seeded re-audit)

Prior slices are listed by their 2026-11-14 number with a current status. New work is appended.

## Prior slices — current status

| Prior slice | Finding | Status now |
|---|---|---|
| **1** Guard the Cockpit null panel read | BO-002 | **still required, unchanged.** Re-verified on the empty state this pass; the populated run does not error, so the guard is what separates the two. Highest-traffic defect (first paint of every new career). |
| **2** Derive the Preflight attention count | BO-006 | **survives, partially re-verified.** The empty-draft case now reads correctly (`5` for five `Missing` rows); the filled-draft contradiction could not be re-walked. Do not close on this pass. |
| **3** One noun for the stat ceiling | BO-007 | **superseded by BO-013** — the fix is no longer a static rename but a state-aware caption. See new slice 3′. |
| **4** Fix the trainee-profile route parameter | BO-001 | **still required**, unchanged (reproduced on the seeded server). |
| **5** Remove the bare colon from inline absence cells | BO-009 | **still required, scope widened** — now three surfaces (`/training-runs`, `/legacy`, run deck). |
| **6** Mark the 3 required selects on Build target | BO-010 | **still required**, unchanged. |
| **7** Scope-annotate the Dashboard KPIs | BO-008 | **still required**, unchanged. |
| **8** One focus-ring token | BO-011 | **not re-checked**; presumed still required (data-independent). |
| **9** Dark `To do` non-text contrast | BO-004 | **not re-checked**; presumed unchanged. |

## New slices

### Slice 3′ — Make the stat caption state-aware (replaces slice 3)

- **Affects:** Cockpit `/training-runs/{run}/cockpit`
- **Findings:** BO-013 (was BO-007)
- **Code surface:** the stat-rail caption in the Cockpit page component — one string, emitted per row.
- **Acceptance:** the same run prints `cap` whether or not the stat has a recorded value; the word on screen matches
  the section heading `Caps` and the scenario detail's `Published stat caps` in both states.
- **Why Medium:** it prints the wrong noun exactly when the trainer has no data to anchor on.

### Slice 10 — Disclose the missing league-placement bonus

- **Affects:** Cockpit (Scenario panel) and Scenario detail (team block)
- **Findings:** BO-015
- **Code surface:** the team panel's caption on both screens — a sentence stating the placement's bonus is not
  stored, matching the pattern already used for `Rank not recorded` and the blue-spark reconciliation note.
- **Acceptance:** a run with `league_placement = 8` either prints the bonus or says plainly that it is not stored.
  Rendering the actual ladder is **not** in this slice (EX-14).

### Slice 11 — Label the burst figures with their scope

- **Affects:** Cockpit vs Scenario detail
- **Findings:** BO-017 (and, once settled, BO-016)
- **Code surface:** the Scenario detail's `Spirit Bursts` progress row — add the scope (`normal only`) or print both
  counts, so the `11` and the `6` are visibly two figures of different kinds.
- **Acceptance:** a reader can tell why the two screens print different numbers for the same noun.
- **Blocked on:** EX-15 for BO-016's contradictory countdown — do not fix the countdown until someone rules which
  number is the target. Slice 11's labelling can ship independently.

## Order

1. Slice 1 (BO-002) — first paint of every career, still the highest-traffic defect.
2. Slice 3′ (BO-013) — Medium, one string, inverts a number's meaning.
3. Slice 4 (BO-001), Slice 5 (BO-009, widened), Slice 6 (BO-010), Slice 7 (BO-008) — unchanged, small.
4. Slice 10 (BO-015), Slice 11 (BO-017) — disclosure polish.
5. Slices 8, 9 — not re-checked this pass; re-verify before scheduling.

Nothing here needs a schema change, an ADR, or a dependency. Slice 11 is partially blocked on a one-line ruling.
