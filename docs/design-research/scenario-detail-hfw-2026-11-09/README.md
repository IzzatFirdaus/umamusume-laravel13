# Scenario detail HFW — 2026-11-09

**Redrawn per W.1=C, W.2=run-state-primary, W.3=C, W.4=C; supersedes the earlier "one visual decision" annotation; current as of `45ed5534`.**

One page: [`scenario-detail-hfw.html`](./scenario-detail-hfw.html). Open it in a browser (static, no
server needed; it links the v2 set's `assets/shell.css` by relative path, so keep the folder beside
`frontend-v2-wireframes/`).

## What it is

The updated Scenario detail screen (`SCR-CAR-029`, `/training-runs/{run}/scenario`) drawn in the
app's majority visual language, answering the design-language survey
(section `## scenario-detail-design-language-survey-2026-11-09.md` of
`docs/research-scratch/AUDIT-AND-VERIFICATION.md`, section `## scenario-detail-design-language-survey-2026-11-09.md`). It is a design artifact:
no component, token, test or copy string changed to produce it.

**On 2026-11-10 four owner rulings superseded the "one visual decision" framing this file used to
carry.** The page is no longer a proposal with a single change in it; it is the consequence of four
rulings, and the annotation's first item says so. The rulings live in
section `## 2026-11-10-scenario-detail-remodel.md` of `docs/research-scratch/PROCESS-PLANS.md` ("Addendum — 2026-11-10"), which also records
the classification basis the order is derived from.

## What is real in it

- **Data:** run 7's live payload — the seeded canonical walk (`RosyDreamsRiceShowerSeeder`:
  Rice Shower · Unity Cup · turn 67, Blue Bloom / Dreaming Big / league 8 / 4 preseason wins,
  55 Unity Trainings, 6 + 5 bursts, rank S → facility 5, band 10–12, Goals Achieved 1 of 8).
  Nothing invented; the N/A cells are absences in the data too.
- **Tokens:** the v2 set's `shell.css` (the app's `app.css` values), linked across folders.
- **Structure:** every panel the updated page composes, in the app's reading order, at the
  12-track bento spans `UnityCupPanel.vue:183` declares.

## The one decision it draws

The survey measured the lime `CapsuleHeader` as the page's single divergence from the app's
majority (one call site, zero other pages). This wireframe applies the survey's re-skin reading:
chrome headers → the plain card headings the other 21 pages use. It is the drawing a ruling on the
survey's §5 would act on, not the ruling itself.

**Verified, not asserted:** fourteen panel `<h2>` elements; `class="…lattice-bleed"` and
`class="…bg-chrome"` each appear zero times. The remaining pills are data (status badges, ladder
rungs, provenance marks), never heading chrome.

## Panel order — the owner's 2026-11-10 ruling, not the component's DOM order

**Superseded heading:** this section was "Panel set, order and cards — unchanged from the component".
It is no longer unchanged. W.2 = run-state primary reorders the page, and the order below is the
addendum's classification applied, not a transcription of `UnityCupPanel.vue`.

| Position | Panel | Category | Span |
|---|---|---|---|
| — | Team Race banner | header, unclassified | s12 |
| 1 | Unity Cup progress | run-state | s4 |
| 2 | Burst count bands (+ Spirit Burst roster) | run-state | s12 |
| 3 | Special Training | run-state | s4 |
| 4 | Trainee goals | run-state | s12 |
| 5 | Mandatory races | run-state | s12 |
| 6 | Team race coverage | run-state | s8 |
| 7 | Team race shape | run-state | s12 |
| 8 | Team | team | s4 |
| 9 | Spirit | team | s4 |
| 10 | Team Rank | team | s4 |
| 11 | Team stat grades | team | s8 |
| 12 | Unity Cup reference strip | reference | full width |

**Why this differs from the component.** `UnityCupPanel.vue` draws positions 1–3 and 6–11; both goals
cards are `CareerGoalsPanel`'s. The app renders one block per registered flag, so positions 1–7 cannot
be contiguous in the shipped page without splitting a component — recorded as an open consequence in
the addendum (§A.7), not resolved by this redraw.

**Chrome:** zero capsules. W.1 = C applied page-wide, which ratifies what this file already drew.

**Banner:** W.3 = C moved the rule line ("at least 3 circles as a margin…") to the reference strip
(position 12). The banner keeps its title and the run's recorded-absence state.

**Caps:** W.4 = C — already labelled "Published stat caps" in the reference strip, one table, no move
needed.

Two earlier dated corrections still hold and are not undone by the reorder: `6493326a` widened Team
stat grades `s4` → `s8` (closing a 341 px hole), and Move 3 merged the Spirit Burst roster into the
bands card (panel count 11 → 10).

## The one real defect this drawing surfaced — now fixed

The strip's **Spirit Bursts** tile read `N/A` while the bands panel placed the same run in `10–12`
from 6 + 5 = 11. `ScenarioSectionBuilder.php:84` hardcoded every widget except `team_rank` to null,
so a run that recorded both tallies was told it had none.

**Filed as KI-154 and fixed at `5e39a1f0`.** The tile now reads **11**, the combined normal + Extreme
total the bands panel already owns, and stays absent when only one tally is recorded. Research and
the full call-site sweep: section `## ki-154-scenario-widget-sweep-2026-11-10.md` of
`docs/research-scratch/SLICE-RECORDS.md` — the sweep
found six of the seven widget keys were `null`, and that only this one had a recorded reading behind
it. Not fixed by this artifact: the Vue-side bare `N/A` at `UnityCupPanel.vue:324`, and the `+30` /
`15/15/20/20` copy corrections, which are separate register rows.

## Checks

`node ../frontend-v2-wireframes/link-check.mjs` — every internal link and image resolves.

**Reference and framework verification: [`VERIFICATION.md`](./VERIFICATION.md).** Two passes:
17 game-mechanic claims checked against `docs/UMAMUSUME_REFERENCE.md` (13 hold, 4 corrected), and
every checkable rule in `docs/framework-ui-ux/` measured in the rendered page (contrast 0 failures
in both themes, 44px floor 0 undersized, three-step type ladder, 320px reflow, skip link, table
headers, no unnamed control). The pass found two real defects in this artifact — 11 under-sized
record links and a fourth type step — both fixed.

Not run here, and why: `framework-check.mjs` and `framework-audit.mjs` carry per-page registries
for the 49 v2 pages and do not know this folder; adding this page to them is a v2-set edit, which
is a separate task from drawing the HFW. The rules those scripts own were measured directly
instead, and the numbers are in `VERIFICATION.md` rather than asserted here.
