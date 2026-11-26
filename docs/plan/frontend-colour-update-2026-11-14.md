# Frontend colour-update plan
<!-- PLAN-CONTRACT
plan: frontend-colour-update-2026-11-14
status: landed
slices: —
superseded-by: —
amended-by: —
open: —
-->

2026-11-14. Authored from the grey-parity audit (`docs/audits/grey-parity/`, sign-off in `06`,
verdict "audit stands with erratum") plus fresh tree measurements at `d8238c87`. Plan only —
no token, test, or source change is made or authorized by this document; every slice below is a
proposed, owner-ruled item.

## State at authoring

- HEAD `d8238c87`; audit deliverables intact; six spot-checked figures 6/6 MATCH (06, table).
- Guards intact: surface-step floor 1.05 (`DesignTokensTest.php:903`), boundary 3.0 (`:894-899`),
  G-18 three-way thresholds (`:752-754`). No new hardcoded grey hex anywhere outside `app.css`.
- `page #F4F4FF` / `panel #FBFBFD` live (`app.css:136-137`); ink `#322A2F`/`#000000` unchanged
  (`:142-143`); `bc102252` still the last commit on the light block.
- One **new finding beyond the audit** (Phase A, this session): the `/veterans` "Offer for
  Transfer" button uses `bg-warning`/`text-on-warning` — **a token that does not exist**
  (`app.css` has never declared `--color-warning`; built CSS carries zero `warning` contexts;
  repo law at `AUDIT-AND-VERIFICATION.md:10152` forbids inventing one). The button renders with
  a UA-default grey fill and inherited ink. Affects `VeteranRow.vue:109` and
  `resources/js/pages/Veterans/Show.vue:121`.

## Candidate inventory (B1)

| # | Candidate | Source | Scope | Kind | Owner ruling? |
|---|---|---|---|---|---|
| C1 | Ink family re-theme (`ink`, `ink-strong`, `ink-muted`, `ink-faint`) | Audit Change 3 / GP-10 remainder | All three theme blocks (light moves; dark/eva01 measured safe) | Re-theme, not a parity fix | **Y** — audit declined it; owner may now want it |
| C1a | HFW brown ink set: `#6A5641` / `#482720` / `#6A6A75` / `#988F87` | computed here | light block | the concrete option under C1 | folded into C1 |
| C1b | Dark + eva01 ink ramps (`:490-493`, `:627-630`) | measured | dark, eva01 | blast radius of C1, predicted safe unchanged | folded into C1 |
| C1c | HFW muted `#6A6A75` on sunken = 4.57 vs the 4.5 floor | computed here | light muted tier | C1's binding constraint | folded into C1 |
| C2 | Dead `bg-warning`/`text-on-warning` utilities (token never existed) | Phase A finding | 2 components, 1 screen family | Defect repair (not parity, not polish) | **Y** — owner picks the replacement pair |
| — | GP-08 HFW wizard sidebar | 06 erratum 1 (refuted) | n/a | no such variant exists | out of scope |
| — | Semantic family values (status/mood/pick/goal/brand/risk) | audit scope note | n/a | separate pass | out of scope |
| — | DESIGN.md 1.05 floor / DesignTokensTest guards | brief B4 | n/a | untouchable | n/a — slices satisfy them |

No other candidates: the audit's findings (GP-02..07, GP-10) are verification results, not colour
work; Phase A surfaced no new hex and no further drift.

## Blast radius per candidate (B2)

### C1 — ink family: three themes, whole app

| Dimension | Measured |
|---|---|
| Theme blocks | light `app.css:142-152` (`#322A2F`/`#000000`/`#625D7D`/`#8C8A9A`); dark `:490-493` (`#FCFCFC`/`#FFFFFF`/`#A0A0A0`/`#605F5F`); eva01 `:627-630` (`#F0E6FF`/`#FFFFFF`/`#A695C4`/`#65508E`) |
| Consumers | `text-ink*` utilities in 164 files (`text-ink` 164, `text-ink-strong` 130, `text-ink-muted` 146, `text-ink-faint` 1); no raw `var(--color-ink*)` in `resources/js` |
| G-18 pairs naming an ink token | 15 of 41 (exact list at `DesignTokensTest.php:700-715`) + 9 grade-badge `ink-strong` pairs + `ink-strong` on brand-accent-soft |
| app.css comment figures to re-measure | muted-tier 4.5 rows; faint non-text note `:145-151`; rule pair `:153-168` (rule itself unchanged by an ink move) |
| Tests that go red if unsatisfied | resolved-token (95-pin), G-18 41 pairs + 9 grades, surface-step, boundary — all floor-based **genuine guards**: satisfy, never update (audit Phase 3; brief B4) |
| Predicted light ratios — ink `#6A5641` | panel 6.73 / raised 6.95 / sunken 5.95 / page 6.37; on the 7 tint/green-tint contract surfaces 5.77-6.31 — all >= 4.5 PASS |
| Predicted light ratios — ink-strong `#482720` | raised 13.24; 9 light grade fills 9.19-11.03 (worst grade-e 9.19); brand-accent-soft 11.32 — PASS |
| Predicted light ratios — muted `#6A6A75` | panel 5.17 / raised 5.34 / sunken **4.57** / page 4.89 — passes, but the sunken margin +0.07 is the plan's binding constraint |
| Predicted light ratios — faint `#988F87` | white 3.18 / sunken 2.72 — non-text token, `app.css:145-151` note unchanged |
| Predicted dark + eva01 | dark ink on dark panel 14.09, dark muted on dark panel 7.55, eva01 ink on eva01 page 12.10, eva01 muted on eva01 panel 7.16 — **no dark/eva01 change needed** (per-theme ramps) |
| Rollback | revert the light-block token commits in reverse order; guards re-run |

### C2 — dead warning utilities: 2 files, no token exists

| Dimension | Measured |
|---|---|
| Dead classes | `bg-warning`, `border-warning`, `text-on-warning`, `hover:bg-warning/90` — none resolvable; Tailwind emits no rule for an undeclared theme key |
| Consumers | exactly two: `VeteranRow.vue:109`, `Veterans/Show.vue:121` |
| Tests | none pin the button's classes (`git grep -E 'bg-warning|Offer for Transfer' -- tests` = none); the slice adds the assertion |
| Option A — `risk` pair (`#C62828` fill, `#FFFFFF` ink) | boundary risk-on-panel 5.74:1 >= 3 PASS; white on risk 5.62:1 >= 4.5 PASS (`app.css:194` records the same figure); matches A&V:10152's law and DESIGN.md's framing of the incomplete-record state |
| Option B — neutral (`raised` fill `#FFFFFF` + `border-rule` boundary) | fill-vs-card step 1.023:1 (the fill reads by its 1px boundary, not its fill — the same pattern as the screen's other neutral actions); boundary rule-on-panel 3.36:1 >= 3 PASS |
| Rollback | class swap in 2 files; no token moves; no guard moves |

## Proposed slices, ordered (B3)

### Slice 1 — C2 dead-utility repair (small, no token movement)

- **What:** replace the dead classes with one resolvable pair at `VeteranRow.vue:109` and
  `Veterans/Show.vue:121`. The pair is the owner's call (Option A or B above); the plan does not
  choose it.
- **Why first:** 2-file diff, zero token/guard movement, unblocks the veterans screen's only
  unstyled control; every later slice stays independent of it.
- **Tests affected:** none existing; add one assertion pinning the chosen classes (the repo's
  test-per-behavior rule; none exists today — a named gap). No genuine guard is touched.
- **Verification:** targeted pest; `vendor/bin/pint --test --format agent` on the two files;
  `npm run typecheck`; recapture `veterans-1440x900-app.png` into a new supersession dir.
- **Rollback:** revert the 2-file swap.
- **Owner ruling: Y** (pair choice).

### Slice 2 — C1 ink re-theme (largest; ruling required)

- **What:** move the light ink ramp to C1a's four HFW values. Dark/eva01 blocks untouched
  (measured safe above). Presented as a ruling, not a fait accompli (brief B4): if declined, the
  audit's decline stands and nothing happens.
- **Order inside the slice:** `ink-muted` -> `ink` -> `ink-strong`, one commit per token. The
  muted-on-sunken pair (4.57, the thinnest predicted margin) is satisfied from the first commit
  onward, and the later ink/strong moves cannot break it (their worst predicted pair is 5.95) —
  so every intermediate commit clears every floor.
- **Tests affected:** the 41 G-18 pairs, 9 grades, surface-step and boundary all stay as
  floor-based genuine guards — satisfied by measurement, never edited; the 95-token resolved pin
  is count-stable. No pinned-value update exists to make honestly or otherwise.
- **Verification per commit:** re-measure the 15 ink-naming pairs from source hex; re-measure the
  faint note and rule pair; **run the browser contrast gate** (brief B3 makes it mandatory for any
  ink change; it has not been executed across this whole arc); recapture all four desktop pairs
  into the supersession dir (originals untouched); full `php artisan test --compact` at slice end.
- **Rollback:** revert the token commits in reverse order; re-run the gates.
- **Owner ruling: Y.**

### Slice 3 — documentation precondition (flagged separately, brief B4)

- GP-01's trigger-doc erratum is doc hygiene, not colour work: append a dated erratum to
  `GREY-SCALE-APP-VS-HFW.md` naming `bc102252` and labelling its tables historical. The 06 errata
  (GP-08 refuted; 03's line-cite drift) ride along in the same pass. Run this before any slice.
- **Errata folded in (owner correction pass, 2026-11-14, item 4) — and status: DONE for two of
  them, same day.** The `DESIGN.md:198` HLF-path erratum and the dark-provenance clarification
  were appended to `DESIGN.md`'s 2026-10-24 second-ruling block during stat Slice A (measured:
  current dark tokens match the mockups synthesis `app.css:475-482` value-for-value, 7/7, not the
  HLF dark column), and the stale `StatBand.vue` entry in `AGENTS.md` §7 was corrected with a dated
  erratum + §20 changelog row. Remaining in this pass: the `GREY-SCALE-APP-VS-HFW.md` trigger-doc
  erratum and the 06 errata repoint.
- **Owner decisions recorded (2026-11-14):** the semantic-colours pass is now OPEN (it previously
  was excluded — the stat-colour plan is its first slice); Slice D (dark "Neon Glow") is PARKED per
  the owner's view (the game has no dark UI — memo 09 tested this at N=1159 — so only the HLF
  reference could justify it, and it contradicts `DESIGN.md`'s flat-tint rationale); execution
  order: doc hygiene + C2 repair first, then stat Slice A (landed 2026-11-14) — both plans' test
  count pins (95→102, 41→48) are now reconciled in one tree state by that order.

## What this plan does NOT cover

- Semantic family values (C2 only *uses* the existing `risk` token; it moves no value).
- GP-08 — refuted in 06 erratum 1; no work exists.
- Any weakening of `DESIGN.md`'s floors or `DesignTokensTest`'s guards — the plan satisfies them.
- Dark/eva01 ink values — measured safe; no change proposed.
- Execution of any slice — everything here is proposed; the owner rules.

## Preconditions

1. Slice 3's doc-hygiene erratum pass (GP-01 + the 06 errata).
2. Owner rulings: C2's replacement pair; C1's take-or-leave on the HFW ink set.
3. The Phase A baseline stands: `06-final-signoff.md` + `screenshots/phaseA-2026-11-14/`.

## Coverage limitations

- The browser contrast gate has never been executed across this arc (audit limitation carried
  forward); Slice 2 makes running it a hard gate at execution time.
- Predicted ratios are arithmetic from declared hex; rendered pixels not sampled (the audit's
  token-level scope note, same basis).
- GP-09's classification is superseded by this session's evidence: not a warm-grey polish item
  but a dead-utility defect from a never-existing token (mechanism confirmed: no token, no built
  rule, two call sites, repo law against inventing the role).
- C1's dark/eva01 safety numbers are arithmetic predictions, not browser-measured; Slice 2's
  mandatory gate run closes exactly that gap at execution time.
