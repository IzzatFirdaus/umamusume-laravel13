# Phase 8.1 triage (2026-10-25)

Sweep: `PLAYWRIGHT_PORT=8157 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/p8/browser-8157.sqlite`,
fresh `npm run build` (exit 0), 7 specs. Result: **16 failed, 16 passed, 53 did not run, 22.5 m**.
The worker crashed at `support-deck.spec.ts:113` (`code=3221225794`), so the 0 ms failures after it
(trackblazer, unity-cup, ura-panel) are harness artifacts and are not product results.
`career-build-target`, `career-legacy-deck-steps`, `rice-shower-unity-cup` and the first two
`support-deck` cases are measured.

Verdicts after the fixes: **4 component defects**, **13 stale-spec pins**, **2 harness timeouts**,
**1 strict-mode collision**. The 2.1.0 claim that all 46 are "real rendering defects, not stale
assertions" does not hold on this tree.

## Per failure

| spec:line | class | cause | fix |
|---|---|---|---|
| `career-build-target.spec.ts:24` | component | Summary section + `Calculated` badge gated on `hasAnyValue`; `SCREEN_SPEC.md:1655` requires the summary to name every absence | removed the gate (`BuildTarget.vue`) |
| `career-build-target.spec.ts:223` | component | same gate (badge hidden at 320 px) | same |
| `career-build-target.spec.ts:57` | spec | R4-24 (`17900356`) deliberately deleted the "Competitive Build" copy because the select cannot offer it | assertion now requires `Competitive Build` to have count 0 |
| `career-legacy-deck-steps.spec.ts:212` (R3-09) | spec | Preflight prints the parent count + Affinity grade, never "Grandparent A1 … Rank UG"; the rank renders on the legacy step | read-back moved to `#ancestor-rank-letter-parent_a-0` |
| `career-legacy-deck-steps.spec.ts:229` | component | Empty slot printed "Empty."; `SCREEN_SPEC.md:1790` requires "Not equipped" | `SupportSlot.vue` copy changed |
| `career-legacy-deck-steps.spec.ts:267` | spec | `#deck-pick` select + "Equip owned to …" buttons were removed by `5f2bfde4` (per-slot picker + dialog) | re-pointed to the slot's dialog + its Equip button |
| `career-legacy-deck-steps.spec.ts:306` | harness | `#deck-pick` no longer exists, so `boundingBox()` waited out the test timeout; the picker controls are inside a closed dialog | selectors re-pointed; dialog opened before measuring |
| `career-legacy-deck-steps.spec.ts:327` | spec | "Choose a card" heading moved into the dialog | heading asserted with the dialog open |
| `career-legacy-deck-steps.spec.ts:343` | spec | old `#deck-pick` / "Equip owned" flow | re-pointed to the dialog flow |
| `career-legacy-deck-steps.spec.ts:360` (deck clear) | component | `clearSlot()` existed with no bound control | `Remove` control added to `SupportSlot`, wired in `DeckSelect` and `Builder` |
| `rice-shower-unity-cup.spec.ts:58` | spec | `getByText(/Unity Cup/)` resolved to the capsule header + the finale line (strict mode) | `{ exact: true }` |
| `rice-shower-unity-cup.spec.ts:99` | spec | `DeckBuilderPage.equipCard` waited for "Replace the card in Slot N" on empty slots, which say "Choose a card for …" | page object matches either label |
| `support-deck.spec.ts:56` | component | same "Empty." copy defect | same `SupportSlot.vue` fix |
| `support-deck.spec.ts:91` | spec | 44 px sweep waited for `button:has-text("Replace")` on an empty deck | selector re-pointed to "Choose a card" |
| `support-deck.spec.ts:113` | — | worker crash (`0xC0000142`) | void; not a product result |
| `support-deck.spec.ts:156/253` | spec | "Replace the card in Slot 1" on an empty slot | re-pointed to "Choose a card for Slot 1" |
| `trackblazer-panel.spec.ts:228` | spec | the floor cells migrated to `AbsenceValue`, so an exact `N/A` match counts 3 plan cells, not 6 | split into 3 plain N/A + 3 disclosures |
| `trackblazer-panel.spec.ts:349` | component | the held-count `ProvenanceBadge` was dropped while `heldTitle` stayed behind, unused | `Calculated` badge restored beside the count |
| deck page timeouts | harness | 25 picker thumbnails queue on single-process `artisan serve`; `support-deck.spec.ts` already aborts `**/artwork/**` | same abort added to `career-legacy-deck-steps.spec.ts` and `rice-shower-unity-cup.spec.ts` |

## URA panel: retired by owner ruling A (2026-10-25)

- `ura_mandatory_ladder`, `happy_meek`, `career_milestones`, `pre_final_review` existed in
  `ScenarioPanel.vue`'s registry and nowhere in `config/scenarios.php` (`panel_labels` or any
  scenario's `panels`), so the panels could never mount.
- `scenario.meek` is never sent by `ScenarioSectionBuilder`; `HappyMeekPanel` reads it.
- `UraDuelPanel.vue` was deleted by FQ-12 (`681e8b70`); the spec still tested its door, dialog and hints.
- `CareerGoalsPanel` and `UraMandatoryLadder` both render the whole `objectives` array, so mounting
  both would duplicate every group.

Ruling A retires the four registrations and narrows `ura-panel.spec.ts` to the ten cases that render
through `career_goals` today; the seven dead-surface cases are removed. `career_goals` is untouched
and still draws the URA goals ladder; `team_race_facility` stays registered behind its isolated
fixture. KI-130 carries the dated addendum. Restoring a URA surface is a separate compose pass with
PRD §6 stories, not a re-registration.

## Verification after fixes

| run | command | result |
|---|---|---|
| typecheck | `npm run typecheck` | exit 0 (three times, after each component edit) |
| build | `npm run build` | exit 0 (25.3 s, then 43.5 s after the ledger fix) |
| Build Target | `career-build-target.spec.ts` | 7/7 passed |
| Legacy + deck | `career-legacy-deck-steps.spec.ts` | 12/12 passed |
| Support deck (run-scoped) | `support-deck.spec.ts` | 10/10 passed (2.5 m) |
| Rice Shower / Unity Cup | `rice-shower-unity-cup.spec.ts` | 7/7 passed |
| Trackblazer | `trackblazer-panel.spec.ts` | 15/17, then the two repaired cases 2/2 passed |
| Stat marks (Phase 11) | `stat-marks.spec.ts` | 1/1 passed in both themes |
| Unity Cup panel | peer session owns `unity-cup-panel.spec.ts` | **not run** |
| URA panel (narrowed) | `ura-panel.spec.ts` | 10/10 passed (4.0 m) |
| UI framework lint | `npm run lint:ui` | exit 0, but `arbitrary-value count 101 exceeds baseline 0` (pre-existing tree-wide debt; the new Remove button copies its sibling's class string) |
| Lore gates | `composer lore`, `composer lore-code` | docs 517 hits / 75 exempt; code 207 hits. No hit introduced by this slice; deltas from the 2.1.0 figures (515 / 189) are tree-wide. |

## Landing record (2026-10-25)

- `2e752896` ("test(deck): measure the slot grid...") captured the five component fixes while they
  were staged, although its body says "No product file changed." The content is correct; the message
  is not, and the history was not rewritten because the commit belongs to a peer session.
- `56ce97fb` — Commit 2, the six spec/page-object re-points.
- `f2607438` — Commit 3, stat-marks, after a peer `reset` at 21:15:53 removed its first landing
  (`46151c93`); the object was intact and was re-applied.
- `3e81adf6` — Commit 4, URA retirement (ruling A).
- Commits 5 and 6 follow this document's move to `docs/audits/`.
