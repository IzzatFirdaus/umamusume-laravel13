# Scenario detail HFW — 2026-11-09

One page: [`scenario-detail-hfw.html`](./scenario-detail-hfw.html). Open it in a browser (static, no
server needed; it links the v2 set's `assets/shell.css` by relative path, so keep the folder beside
`frontend-v2-wireframes/`).

## What it is

The updated Scenario detail screen (`SCR-CAR-029`, `/training-runs/{run}/scenario`) drawn in the
app's majority visual language, answering the design-language survey
(`docs/research/scenario-detail-design-language-survey-2026-11-09.md`). It is a design artifact:
no component, token, test or copy string changed to produce it.

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

## Panel set, order and cards — unchanged from the component

**Rendered/current as `42d91678`; Move 3 (Spirit Bursts → Burst bands) reflected.** Ten team panels in
`UnityCupPanel.vue`'s own DOM order and spans (Team Race s12 → Team s4 → Spirit s4 → Team Rank s4 →
Unity Cup progress s4 → Team stat grades s8 → Burst count bands + Spirit Burst roster **s12** →
Special Training s4 → Team race coverage s8 → Team race shape s12), then Trainee goals and Mandatory
races as **two** cards (`CareerGoalsPanel.vue:97` renders one `<section>` per group), then the
baseline strip.

Two dated corrections to that list, both measured in a browser at 1280 px on a 977 px grid:

- **`6493326a`** — Team stat grades moved `s4` → `s8`, closing a 341 px hole (8/12 tracks → 12/12)
  without moving a single panel.
- **Move 3** (`28d9afbf`, owner Ruling 2) — the Spirit Burst roster merged into the Burst count bands
  card and that card widened `s8` → `s12`, because its `s4` sibling no longer exists. Panel count
  **11 → 10**; the 236 px of dead column the review measured under the shorter card is gone, and the
  worst remaining hole anywhere on the page is row 2's pre-existing 23 px.

The roster keeps its own words inside the merged card, under a rule, as a labelled sub-block; its
`CapsuleHeader` becomes a `.sublabel` paragraph, so the card carries one header rather than two. In
the app that is `SpiritBurstRoster`'s new `bare` prop.

## The one real defect this drawing surfaced — now fixed

The strip's **Spirit Bursts** tile read `N/A` while the bands panel placed the same run in `10–12`
from 6 + 5 = 11. `ScenarioSectionBuilder.php:84` hardcoded every widget except `team_rank` to null,
so a run that recorded both tallies was told it had none.

**Filed as KI-154 and fixed at `5e39a1f0`.** The tile now reads **11**, the combined normal + Extreme
total the bands panel already owns, and stays absent when only one tally is recorded. Research and
the full call-site sweep: `docs/research/ki-154-scenario-widget-sweep-2026-11-10.md` — the sweep
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
