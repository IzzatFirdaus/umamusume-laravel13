# 01 — Findings (seeded re-audit)
**Run-14 reference note (2026-11-15 dev DB reseed):** Run 14 references throughout this package refer to the pre-correction dev DB row (card 33 Winning Ticket, `deck_slots` empty). On 2026-11-15 the corrected `RosyDreamsRiceShowerSeeder` was applied to the shared dev DB under a one-time EX-12a override (`browser-observation-2026-11-15-followup/07-ex12a-override.md`), superseding run 14 with **run 34** (card 31 `[Rosy Dreams]`, six deck slots including Super Creek Rented, two turn events at turn 67). Screenshots remain valid evidence of the state they capture; live URLs should use run 34.

**4 records this pass · 4 active · 0 withdrawn.** 1 Medium, 3 Low — plus the reconciliation of all 12 prior IDs
(below), which is the substance of this pass. The counts below are of the **new** records only; a prior ID's delta
status is recorded in the reconciliation table, not double-counted here.

| Severity | New records this pass |
|---|---|
| Critical | 0 |
| High | 0 |
| Medium | 1 (BO-013) |
| Low | 3 (BO-015, BO-016, BO-017) |
| Informational | 0 |

**Accounting note.** Two *candidate* defects were raised and dismissed during the pass (the six-card deck rendering
empty; BO-016's countdown being straightforwardly wrong). Neither was ever written as a BO body, so neither is a
withdrawn record — they are recorded in `## Withdrawn this pass` below as observations. A record means a body that
existed. IDs are non-contiguous because **BO-014 was reserved for the deck candidate and left unused**, so the hole
is visible rather than papered over by renumbering a row a reader may already have cited.

## Reconciliation against the 2026-11-14 register — every prior ID

| Prior ID | Title | Status now | Evidence |
|---|---|---|---|
| BO-001 | trainee-profile slug 404 | **carried** | `/career/setup/trainee/rice-shower` → `That page does not exist` (404) on the seeded server; the id form `/career/setup/trainee/33` still resolves. Unchanged by data. |
| BO-002 | Cockpit `warning_message` TypeError | **re-verified on empty state** | Run #13 on `:8000` throws `TypeError: Cannot read properties of null (reading 'warning_message')` at `Cockpit--iqvu67i.js:1:32866` — identical line to the prior pass. **On the populated run #14 (`:8156`) the Cockpit renders 17 rows with ZERO console errors.** The defect is triggered by the turn-less state and hidden, not fixed, by seeding. |

**Session note — live repro: run #35, created 2026-11-15 11:35:59 +0800.** Zero-turn wizard run (scenario Unity Cup, trainee Admire Vega, no target, no parents, no deck, no acknowledgement of extra detail — all steps skipped with defaults) created run 35 on `:8000`. Cockpit at `/training-runs/35/cockpit` throws `TypeError: Cannot read properties of null (reading 'warning_message')` — BO-002 confirmed on a fresh zero-turn run. Screenshots: `followup/screenshots/bo-002-run35-cockpit-desktop-1280x800.png`, `followup/screenshots/bo-002-run35-cockpit-mobile-375x812.png`. Console error captured verbatim above. **Name: `BO-002 repro — zero turns, do not delete`.** No seeder used; wizard path only (`StartCareerRequest`/Preflight per EX-12b).
| BO-003 | factory row `Aut Neque` in the catalogue | **carried** | `/umamusume` still states `72 of 72 trainees`; the row was not re-opened this pass (it is data, unchanged by seeding). Not re-screenshotted — see note below. |
| BO-004 | dark `To do` pill 4.15:1 | **not re-checked** | `dark` theme was not re-entered; the prior measurement stands unrefuted. One line, as the dispatch allows. |
| BO-005 | turn-edit door unreachable (no turns) | **does not reproduce** — new precondition | On the seeded run the door exists: the Timeline prints `Edit turn 67`. Prior precondition was "0 turns"; the finding was a data-state artifact, correctly recorded as such at the time. |
| BO-006 | "5 steps need attention" wrong | **carried** | Fresh draft on the seeded server still prints `5 steps need attention` with five `Missing / Not entered` rows — here the count is *correct*. **The filled-draft half could not be re-walked** (see below): the populated server's wizard draft is per-session and I walked it fresh. The state-(b) contradiction is neither confirmed nor refuted. |
| BO-007 | `floor` vs `cap` | **escalated — worse than filed** | **BO-013.** On the populated Cockpit the same component prints `607 / 1,325 cap`; on the empty run it prints `N/A / 1,300 floor` for the same stat. See BO-013. |
| BO-008 | Dashboard counts vs catalogue scope | **carried** | Dashboard `8 veterans` / catalogue `72 of 72 trainees`; the 133-trainee tile of the prior pass still has no reconcilable counterpart. Cards and skills still reconcile. |
| BO-009 | `N/A :` bare colon | **carried** | `/training-runs` → `N/A : no turn has been logged on this run yet.` Exactly one occurrence, unchanged. Now also visible on `/legacy` and `/training-runs/14/deck` (`N/A : No ownership has been recorded for this slot yet.`) — the class is broader than the one cell originally filed. |
| BO-010 | 3 unmarked required selects | **carried** | `/career/setup/target` unchanged. |
| BO-011 | 4 focus-ring colours on `/review/10` | **not re-checked** | Styling is data-independent; the prior measurement stands. |
| BO-012 | no compare affordance on `/support-cards` | **carried** | `/support-cards` still has no compare control. `/veterans/compare` works and now has 8 candidates. |

The three prior *withdrawn* candidates were not revisited: duplicate nav labels (probe error), 1,910-vs-623 skills
(reconciles exactly), and the 281px panel (renders nothing). Nothing in the seeded data changes any of the three.

**One deliberate non-re-screenshot:** BO-003 is a data row. Seeding does not touch the catalogue, so its evidence is
unchanged; re-capturing the same page would add a file and no information.

---

## Medium

## BO-013 — The stat-ceiling caption is state-dependent: `cap` when populated, `floor` when unrecorded

- **Severity:** Medium (**escalated from Low** — the prior pass filed BO-007 as a wording inconsistency; it is a
  conditional that prints the wrong noun exactly when the trainer has no data)
- **Screen / route:** `/training-runs/{run}/cockpit`, the stat rail
- **Viewport:** both
- **Theme:** light
- **Preconditions:** two runs — one populated (#14, turn 67) and one turn-less (#13).
- **Steps to reproduce:** open both Cockpits and read the same five rows.
- **Expected:** one noun for one number, in both states. The section is already headed **`Caps`**, and the scenario
  detail calls the same figures "Published stat caps".
- **Actual:**
  - Populated — `Speed 607 / 1,325 cap`, `Stamina 577 / 1,322 cap`, `Power 549 / 1,368 cap`, `Guts 736 / 1,308 cap`,
    `Wit 397 / 1,800 cap`.
  - Turn-less — `Speed N/A / 1,300 floor`, … `Wit N/A / 1,800 floor`.
  The caption flips noun with the presence of a recorded stat. A trainer reading the empty run is told 1,300 is a
  **minimum**; it is a maximum. The two reading surfaces disagree with each other *and* with their own section
  heading. This is not a cosmetic label: it inverts the meaning of the number precisely in the state where the
  trainer has nothing else to anchor on.
- **Evidence:** `training-runs-14-cockpit-1440x900.png` (cap), `training-runs-13-cockpit-1440x900.png` (floor)
- **Can it be fixed in code?** yes — one caption string in the stat-rail component, branching on whether a value is
  recorded. The arithmetic is right in both states and `ScenarioCaps` already owns it; only the noun is wrong.
- **Confidence:** **Observed.** Both states read directly, same component, same five rows.

## Low

## BO-015 — The team's league placement prints with no placement bonus anywhere

- **Severity:** Low
- **Screen / route:** `/training-runs/14/cockpit` (Scenario section) and `/training-runs/14/scenario`
- **Viewport:** both
- **Theme:** light
- **Preconditions:** run #14, Unity Cup, `team_league_placement = 8`.
- **Steps to reproduce:** open the Cockpit, read the Scenario panel; open the Scenario detail, read the team block.
- **Expected:** the reference doc for this exact career records `League **8th**` alongside
  `Team Ranking Bonus: All Attributes **+ 30**`. Where a run states a placement, a trainer would expect the bonus
  that placement pays, or a sentence saying it is not stored.
- **Actual:** the Cockpit prints `Team Blue Bloom · Dreaming Big · League 8 · Bursts 11 (inside 10-12)` and the
  Scenario detail prints `Team Name Blue Bloom / Motto Dreaming Big / League placement 8 / Preseason rounds won 4`.
  **The word "bonus" appears zero times on the Cockpit**, and no `+30` or `+ 30` anywhere on either screen. The
  placement is recorded and rendered; the thing it pays is absent with no disclosure.
  The prior pass could only infer this from config; here it is observed on a run that *has* the placement.
- **Evidence:** `training-runs-14-cockpit-1440x900.png`, `training-runs-14-scenario-1440x900.png`
- **Can it be fixed in code?** **partially** — rendering the bonus needs the bonus table, which is a data/model
  question (does the app store the ladder?). What is code-fixable *today* is the disclosure: a sentence saying the
  placement's bonus is not stored. Treat the data half as EX-14.
- **Confidence:** **Observed** for the absence on both rendered screens. The doc's `+30` is a read of the reference,
  not a claim about what the app should compute.

## BO-016 — "Spirit Burst Countdown" states a target the run has already met

- **Severity:** Low
- **Screen / route:** `/training-runs/14/cockpit`
- **Viewport:** both
- **Theme:** light
- **Preconditions:** run #14, `Spirit Bursts 11`, band `10-12`.
- **Steps to reproduce:** open the Cockpit Scenario panel and read the countdown sentence.
- **Expected:** a countdown states the remaining gap, and reads zero/absent when the band is met.
- **Actual:** the same panel prints `Bursts 11 (inside 10-12)` and directly beneath it
  `Need 2 more bursts to the Spirit Burst payout (Gold Lv3, Senior November). This counts normal plus Extreme
  bursts; a normal-only reading would need 1…`. Eleven inside a 10–12 band with "need 2 more" is arithmetically
  contradictory on its face: 11 + 2 = 13 is outside the stated band, and 11 is already above the band's floor.
  Either the countdown targets a different threshold than the band prints, or it is counting from a different
  figure. **The screen contradicts itself in two adjacent lines.**
- **Evidence:** `training-runs-14-cockpit-1440x900.png`
- **Can it be fixed in code?** yes, once the intended target is settled — this is the one finding where the fix
  depends on which number is right, so it needs a one-line ruling (EX-15) before code.
- **Confidence:** **Observed** for the contradiction. **Uncertain** for which figure is wrong — I can see the two
  sentences disagree, not which one to trust. Deliberately not filed as a code slice.

## BO-017 — Legend and counter disagree: `11` Bursts printed, `6` in the progress row

- **Severity:** Low
- **Screen / route:** `/training-runs/14/cockpit` vs `/training-runs/14/scenario`
- **Viewport:** both
- **Theme:** light
- **Preconditions:** run #14.
- **Steps to reproduce:** read `Bursts 11` on the Cockpit; read `Unity Cup progress … Spirit Bursts 6` on the
  Scenario detail.
- **Expected:** one run, one burst count.
- **Actual:** Cockpit says `Bursts 11 (inside 10-12)`; Scenario detail says
  `Unity Trainings 55 · Spirit Bursts 6`. The Cockpit sentence explains it counts "normal plus Extreme" — so 11 and
  6 are plausibly `6 normal + 5 extreme` — but the Scenario detail's row labels neither, and prints the plain label
  `Spirit Bursts` with the smaller number. Two screens, same run, same noun, different figures, no scope word on
  the second.
- **Evidence:** `training-runs-14-cockpit-1440x900.png`, `training-runs-14-scenario-1440x900.png`
- **Can it be fixed in code?** yes — label the Scenario row's figure with its scope (normal-only), or print both.
- **Confidence:** **Observed** for both figures. **Inferred** that 11 = 6 normal + 5 extreme: the Cockpit says it
  counts both and the numbers add, but no screen states the split.

---

## Withdrawn this pass

| Candidate | Why withdrawn |
|---|---|
| "The seeded run's six-card deck renders as six empty slots" | **The seeder never imports a deck.** Its own docblock: "The six-card deck (§1.4) is not imported. `ImportHistoricalRun` writes no deck slots, the same omission the URA seed records in its own docblock." The screen correctly renders what is stored (0 slots). Not a defect — and the roster/deck gap the dispatch asked about is a **seeder omission**, not a rendering bug. Recorded as EX-16. |
| "BO-016's countdown is simply wrong" | **Held as Uncertain, not asserted.** I can prove the two adjacent sentences conflict; I cannot prove which is intended. Filed with that limit stated rather than upgraded. |
