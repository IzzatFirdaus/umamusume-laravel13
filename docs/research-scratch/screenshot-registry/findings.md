# Findings Registry — Consolidated Evidence and Observations

This document consolidates confirmed mechanics, UI behavior, and open questions from the screenshot research registry. It serves as a maintainable index for Trainer Desk 2.0 implementation, distinguishing directly observed values from inferred rules, and documenting evidence gaps.

All observations and claims are traceable to specific screenshots via their `ref` (timestamp) or `eid` (evidence ID). Where a claim is marked "inferred" or "low confidence", further verification is required.

## Confirmed Mechanics and UI Behavior

These observations are directly legible on one or more screenshots and have been promoted to evidence claims (recorded in `evidence.jsonl`). Each includes the supporting frame(s), confidence, and limitations.

### Mood System
- **Claim:** Mood Effect modal shows five tiers: GREAT/GOOD/NORMAL/BAD/AWFUL. Training result deltas: +20/+10/0/−10/−20%. Running attribute deltas: +4/+2/0/−2/−4%.
  - **Support:** `2026-07-18 001957` (observed, high confidence)
  - **Limitations:** None; the modal displays exact values.
  - **Evidence ID:** `mood-ladder-confirmed-0004`
  - **Trainer Desk 2.0 implication:** Used to calculate effective training gains and attribute changes during runs.

### Growth Rates
- **Claim:** Growth-rate panel prints `+0%` explicitly for zero entries (not blank). Values are per-run, not fixed ceiling.
  - **Support:** `2026-07-17 232345`, `2026-07-18 133731`, `2026-07-18 160438` (observed, high confidence)
  - **Limitations:** Confirmed on three frames from two different runs; denominators vary slightly, confirming a per-run ceiling.
  - **Evidence ID:** `growth-rate-confirmed-0005`
  - **Trainer Desk 2.0 implication:** Used to calculate stat gains after training; zero gains are rendered as `+0%`.

### URA Finale
- **Claim:** URA Finale training screen shows: "Finale Underway" banner, EX goal "Place 1st in URA Finale Qualifier", stat ceilings Speed 1327/Stamina 1322/Power 1404/Guts 1306/Wit 1802, Skill Points 170, Failure 10%, growth rates +20/+0/+10/+0/+0.
  - **Support:** `2026-07-15 012428`, `2026-07-15 014154` (observed, high confidence)
  - **Limitations:** None; all values are directly readable.
  - **Evidence ID:** `ura-finale-confirmed-0006`
  - **Trainer Desk 2.0 implication:** Used to display URA Finale-specific UI and goal tracking.

### Skill Point Hint Discounts
- **Claim:** Support-card Skill Point hint discount tiers are 10% / 20% / 30% OFF for Hint Lv1 / Lv2 / Lv3. All three tiers are legible on a single Skill Selection frame.
  - **Support:** `2026-07-17 234521` (Lv1 10%, Lv2 20%, Lv3 30%); corroborated by `2026-07-17 233324` (Lv1/Lv2), `2026-07-14 130856` (Lv1/Lv2), `2026-07-15 161624` (Lv3)
  - **Limitations:** The pre-discount base cost is not printed on-screen, so arithmetic cannot be independently verified for every row.
  - **Evidence ID:** `hint-discount-0009`
  - **Trainer Desk 2.0 implication:** Used to calculate effective Skill Point costs when purchasing hints on the Learn screen.

### Failure Rates
- **Claim:** The active training card shows a per-discipline static failure label (Guts 5%, Speed 3%, Stamina 1%, Power 2%). The Power-card reading "Failure 2%" is consistent with the Power discipline rate.
  - **Support:** `2026-07-18 135801` (observed, medium confidence)
  - **Limitations:** Single-frame confirmation; other disciplines not directly observed on the same frame.
  - **Evidence ID:** `failure-rate-confirmed-0008`
  - **Trainer Desk 2.0 implication:**** Used to display discipline-specific failure risks during training selection.

## Per-Frame Observations (Not Yet Evidence Claims)

These observations are directly legible on individual screenshots but have not yet been generalized into reusable evidence claims. They remain as per-frame notes in `inventory.jsonl`. Promoting them to evidence would require additional supporting frames or rule inference.

### UI Structure and Layout
- **`2026-07-14 025400`:** Predictions modal for G1 Sprints Stakes (Nakayama 1200m Sprint Right Outer) shows five stat chips with visual state indicators: double-ring (favorite), check mark (top 3), cross (outside top 3). Coach verdict: "definitely a contender".
- **`2026-07-14 121123`:** Trainer Select pre-run screen displays Team Rank B, time penalties (TP 03:52 71/100), repeat penalties (RP 1:53:24 4/5), currency (1,754,090), and detailed stat/gear breakdown for Hishi Amazon (Azure Amazon) including track suitability (A/E dirt) and distance/style suitability grids.
- **`2026-07-14 122314`:** Legacy Select + Sparks screen shows two Legacy cards (B+ and A+) with full stat blocks (125/1318 F · 87/1316 G · 111/1304 F · 94/1308 G · 93/1800 G) and a Sparks panel listing 10 star-rated skills per Legacy.
- **`2026-07-14 124209`–`124534`:** Junior Year Pre-Debut training screens show discipline highlighting (Swimming Lvl 1, Dirt Lvl 1, Guts Lvl 1, Speed Lvl 1), Energy bars with pip counters, Skill Points, Failure %, and per-turn stat gains (e.g. +2/+11/+8/+1/+5). Goal text varies ("Run in Junior Make Debut", "Earn 3000 fans").
- **`2026-07-14 130856`:** Skill Selection screen (168 SP) shows two hint discount tiers visible: LP1211-M (180 SP, Hint L1 10% OFF) and Cut and Drive! (160 SP, Hint L2 20% OFF). Confirm button present.
- **`2026-07-14 190453`:** Junior Year Pre-Debut training screen shows high Failure % (28%) with otherwise standard layout.
- **`2026-07-14 194317`:** Junior Year Late Jul training screen shows "Earn 3000 fans" goal with progress counter (1,943 to go), Energy GOOD, and Skill Points 242.
- **`2026-07-14 203400`:** Classic Year Early Jan training shows NHK Mile C entry goal met ("Place top 5 in NHK Mile C. Entry criteria met!"), Energy GREAT, and Treadmill Lv 2 highlighted.
- **`2026-07-14 223933`:** Classic Year Early Feb training shows Bunny-Hop Lv 2 highlighted, Skill Pts 30, Failure 31%, and Energy GOOD.
- **`2026-07-14 225944`:** Classic Year Early Jun training shows 1 turn to Unity Cup, Goal "Place top 3 in Mile Ch", Energy GOOD, and Squats Lv 2 highlighted.
- **`2026-07-14 235300`:** Team Selection confirmation modal shows five-circle compatibility (Sprint O / Mile O / Medium O / Long O / Dirt O) versus a D-tier opponent ("Last Stand Meal"), with coach encouragement.
- **`2026-07-15 012321`:** Team race results screen shows three detailed race rows versus a five-character opponent on Tokyo Turf, with placement per character and cumulative wins/losses.
- **`2026-07-15 024104`:** Junior Year Pre-Debut swimming training shows Stamina Lvl 1 (Eaststroke) highlighted, Skill Pts 133, Failure 13%, and stat gains Speed +9 / Guts +4 / Wit +2.
- **`2026-07-15 032215`:** Junior Year Pre-Debut power training shows Power Lvl 1 highlighted, Failure 13%, Skill Pts 153, and stat gains Speed +6 / Guts +8 / Wit +2.
- **`2026-07-15 040332`:** Shop Items popup displays 36 Shop Coins on hand and three NEW items with costs and effects: Stamina Ankle Weights (Cost 50, +6 turns), Coaching Megaphone (Cost 40, +4 turns), Speed Training Application (Cost 150, +6 turns).
- **`2026-07-15 044428`:** Final training turn shows Goal "500 Result Pts" with progress counter (After 100 pts), 7 turns left, Energy GREAT, Speed Lvl 5 (Volleyball Dive) highlighted with double-arrow, and stat gains Speed +18 / Stamina +8 / Wit +2.
- **`2026-07-15 052723`:** Skill Selection screen (278 SP) shows Resplendent Red Ace OBTAINED (purple card, inverted row, no stepper), LP1211-M Hint L2 20% OFF (160 SP), and Triumphant Pulse Hint L1 10% OFF (180 SP).
- **`2026-07-15 061006`:** G1 Arima Kinen race result shows 2nd place, 460/300 pts MAX (over quota), Nakayama Turf 2500m Long Right/Inner Firm, placement rows with exact points (1st: 100 pts, 2nd: 60 pts, 3rd: 40 pts), and coach commentary.
- **`2026-07-15 161624`:** Skill Selection screen (181 SP) shows Subdued Pace Chasers (130 SP, no discount), Soft Step Hint L3 30% OFF (112 SP), and Straight Descent (120 SP). Maximum discount tier (Lv3 30%) visible.
- **`2026-07-15 194245`:** Showdown results screen shows RACE FINISHED banner, five races on Hanjin Turf (2200m Medium, 1600m Mile, 2600m Long, 1200m Sprint, 1800m Dirt), and a 4W-1L-1D record versus a five-character opponent.
- **`2026-07-17 221551`:** Classic Year Late Jan training shows Treadmill Speed Lv.2, Failure 0%, SP 103, full stat panel (E/E/E/D/F), and Scheduled Races calendar with upcoming G1 races.
- **`2026-07-17 232251`:** Skill allocation screen shows 133 SP, three candidate skills each with Hint Lv.1 10% OFF (81/323/170 SP), and skill names (Firm Conditions / Swinging Maestro / Corner Recovery).
- **`2026-07-17 232828`:** G2 Spring Stakes 4th place race result shows Nakayama Turf 1800m Mile Right/Inner Firm, prize tiers (Gold 50000/Silver 20000/Bronze 5000), fan gain +1363 (total 23943), two item rewards, and coach feedback ("more Power needed").
- **`2026-07-17 235210`:** Team Selection pre-race confirmation shows mixed compatibility (Sprint O / Mile X / Medium △ / Long ▮ / Dirt O), Team Lucky Number 14, and coach dialogue about a close contest.

### Mechanics and Values
- **`2026-07-17 234521` (revised observation):** In addition to confirming all three hint discount tiers, this frame shows Skill Points 308, a Log panel with Rice Shower dialogue, an event card for "The Unity Cup is Almost Here!", and a Snipping Tool overlay (screenshot-copied toast) in the corner.
- **`2026-07-17 232828`:** Beyond the race result, this frame shows two item rewards accompanying the 4th place finish, and Legacy ratings for the participant (Legacy 1 B+, Legacy 2 S+/Guest).
- **`2026-07-17 221551`:** Beyond training stats, this frame shows the Scheduled Races calendar with upcoming G1 races (Spring Stakes, Tokyo Yushun, Kikuka Sho, Queen Elizabeth II Cup).

## Inferred or Low-Confidence Claims Requiring More Evidence

These are mechanic claims currently recorded as low-confidence inferences (bootstrap seeds) or claims with notable limitations. They require additional verification frames or rule confirmation.

### Bootstrap Seeds (Inferred, Low Confidence)
These originate from the registry bootstrap and await confirmation.
- **Mood tiers are five (GREAT/GOOD/NORMAL/BAD/AWFUL); training deltas +20/+10/0/−10/−20%, running deltas +4/+2/0/−2/−4%.**
  - **Support:** Inferred from notes; not yet observed on a single frame.
  - **Evidence ID:** `mood-ladder-0001`
  - **Status:** Superseded by `mood-ladder-confirmed-0004` (high confidence, observed on `2026-07-18 001957`).
- **Rice Shower growth-rate panel reads +0 Speed, +10 Stamina, +0 Power, +20 Guts, +0 Wit.**
  - **Support:** Inferred from notes on three frames; not yet observed on a single frame.
  - **Evidence ID:** `growth-rate-0002`
  - **Status:** Superseded by `growth-rate-confirmed-0005` (high confidence, observed with explicit `+0%` printing).
- **Support-card Skill Point hint discount: Lv1=10 percent, Lv2=20 percent, Lv3=30 percent.**
  - **Support:** Inferred from notes; Lv1/Lv2 observed together, Lv3 inferred.
  - **Evidence ID:** `hint-discount-0003`
  - **Status:** Superseded by `hint-discount-0009` (high confidence, all three tiers observed on `2026-07-17 234521`).

### Open Questions and Limitations
These are observations or claims with noted uncertainties that require further verification.
- **Claim:** Growth-rate values are per-run ceilings.
  - **Support:** Observed on two Rice Shower runs with differing denominators (1325/1322/1358/1308/1800 vs. 1327/1322/1404/1306/1802).
  - **Limitations:** Confirmed on only two runs; more runs would strengthen the conclusion.
  - **Open:** Verify on additional Rice Shower runs or other umamusume to confirm per-run behavior.
- **Claim:** Hint discount tiers are exactly 10%/20%/30% OFF the base Skill Point cost.
  - **Support:** Observed costs (272/128/112) align with 10%/20%/30% OFF plausible bases (e.g. 302→272, 160→128, 160→112).
  - **Limitations:** The pre-discount base cost is not printed on-screen, so the exact percentage cannot be independently verified.
  - **Open:** Find a frame that shows both the base cost and discounted cost for the same skill, or confirm via external documentation.
- **Claim:** Failure rates are static per-discipline (Guts 5%, Speed 3%, Stamina 1%, Power 2%).
  - **Support:** Power-card reading "Failure 2%" matches the Power discipline rate (2%).
  - **Limitations:** Only the Power discipline was directly observed; other disciplines not confirmed on the same frame.
  - **Open:** Observe failure-rate labels for Guts, Speed, and Stamina on training cards to confirm the static rates.

## Evidence Gaps by Scenario

The corpus contains 1,159 screenshots, but not all scenarios or UI surfaces are equally represented. These gaps are documented to guide future capture efforts rather than repeated searching of the existing corpus.

### Missing Scenarios
- **Trackblazer:** No screenshots of the Trackblazer scenario exist in the current corpus. All available URA Finale screens are from the qualifying and training phases; none show the actual Trackblazer race UI.
- **Our Grand Concert:** No screenshots of the Our Grand Concert scenario exist. All concert-related UI (rehearsal, performance, awards) is absent.
- **Derby Races (Twinkle Series, etc.):** While some derby races appear in showdown results (e.g. `2026-07-15 194245` on Hanjin Turf), dedicated derby scenario screens (entry, betting, post-race) are not clearly present in the corpus.

### Missing UI Surfaces
- **Results Detail Modals:** While race result screens are present (e.g. `2026-07-15 061006`, `2026-07-17 232828`), the detailed result modals (tapped from the result screen) are not clearly visible in the corpus.
- **Support Card Detail Modals:** Support card list screens are present, but the detail modals (tapped from a card) are not clearly visible.
- **Shop Detail Modals:** While the Shop Items popup is present (`2026-07-15 040332`), the detail modals for individual items are not visible.
- **Event Detail Modals:** While event cards appear in logs and banners (e.g. "The Unity Cup is Almost Here!" in `2026-07-17 234521`), the full event detail modals are not visible.
- **Friend Support Screens:** Friend support selection and interaction screens are not clearly visible in the corpus.

## Unresolved Citations and Missing-File Investigation

These are entries in `refs-index.jsonl` that do not resolve to a current inventory record. Each has been investigated using only authorized registry artifacts and corpus metadata; no mappings have been invented.

### `2026-07-14 185506.png`
- **Citations:** 16 (from `clusters.json`, `signatures.json`, `DESIGN-CORPUS.md`)
- **Investigation:** The frame is documented in `DESIGN-CORPUS.md` section 6 as a cluster representative (singleton, count 1) whose sole member is absent from disk and has never appeared in git history. Its signature survives in `signatures.json` (613×456, luminance 229, saturation 37).
- **Disposition:** Legitimate unresolved citation — the frame is genuinely missing from the corpus. No frame exists to inspect; the group cannot be extracted.
- **Action:** Skip this group in extraction. Record the skip in the extraction report.

### `2026-07-15 005040.png`
- **Citations:** 2 (from `accents.json`)
- **Investigation:** The frame is documented in `accents.json` under `"result-pits-modal": { "file": "Screenshot 2026-07-15 005040.png", "missing": true }`. It is explicitly marked as missing for the "result-pits-modal" color probe.
- **Disposition:** Legitimate unresolved citation — the frame is genuinely missing from the corpus. No frame exists to inspect.
- **Action:** Skip this frame in extraction. Record the skip.

### `2026-07-18 235511.png`
- **Citations:** 10 (from `KNOWN-ISSUES.md`, `DESIGN-CORPUS.md`)
- **Investigation:** This is a citation typo (KI-57). The manifest in `DESIGN-CORPUS.md` names `Screenshot 2026-07-18 235511.png`, but the file on disk is `Screenshot 2026-07-17 235511.png`. The correct frame is present in inventory and verified (state: `documented_unverified`, awaiting visual inspection).
- **Disposition:** Resolved as a typo; the correct frame `2026-07-17 235511.png` exists in inventory.
- **Action:** No action required; the registry correctly references the existing frame.

## Remaining Documented-Unverified Frames

There are 45 frames that appear in legacy notes but have not yet been visually inspected against those notes. These are recorded as `state: documented_unverified` in `inventory.jsonl`. They represent an opportunity to validate or correct historical observations.

The 45 frames are distributed across legacy note files as follows:
- `00-race-entry-screenshots.md`: 0 remaining (all 10 matched)
- `01-training-screenshots.md`: 0 remaining (all 14 matched)
- `02-career-progression-screenshots.md`: 0 remaining (all 9 matched)
- `03-skill-selection-screenshots.md`: 0 remaining (all 5 matched)
- `04-character-detail-screenshots.md`: 0 remaining (all 3 matched)
- `05-menu-screenshots.md`: 0 remaining (all 3 matched)
- `06-other-screenshots.md`: 0 remaining (all 5 matched)
- *Note: The above counts show zero remaining because all legacy notes were reconciled in the initial inventory pass; any `documented_unverified` frames arise only when a note reference could not be resolved to a frame. However, the `notes-index.jsonl` shows 52 reconciled references (50 exact, 2 probable, 0 unresolved) and zero unresolved note references. Therefore, the 45 `documented_unverified` frames in `inventory.jsonl` represent frames that are named in notes but for which the visual inspection has not yet been performed to confirm or refute the note.*

The next batch should prioritize these frames to validate the historical record. A frame remains `documented_unverified` until its associated note is visually confirmed (advancing to `verified`) or found to be inaccurate/illegible (remaining `documented_unverified` or advancing to `partial` with an open question).

## Recommended Next Review Batch (B003)

Per the B002 manifest's `next` field and the current registry state, the recommended next batch is:

### Selection Rationale
1. **Cluster 725 representatives** — 58-frame cluster headed by `2026-07-18 001811`. One representative (`2026-07-18 001811`) already landed in B002; the remaining 56 members are still `not_reviewed`.
2. **Clusters 707/708 representatives** — 14-frame clusters headed by `2026-07-17 230755` and `2026-07-17 230902`. One representative from each cluster already landed in B002; the remaining 26 frames (13 per cluster) are still `not_reviewed`.
3. **Wholly unverified large clusters** — Entire clusters with no members yet reviewed:
   - Cluster 80 (38 frames, rep `2026-07-14 202142`)
   - Cluster 66 (23 frames, rep `2026-07-14 194819`)
   - Cluster 64 (17 frames, rep `2026-07-14 194531`)
4. **Documented-unverified frames** — The 45 frames named in legacy notes that have not yet been visually inspected.
5. **Additional no-note, full-desktop frames** — To ensure coverage of fresh UI not yet represented in verified observations.

### Selection Priorities (for a batch of 20–30 frames)
1. One additional representative from cluster 725 (avoiding known perceptual over-collapse risk; prefer a frame distant from the B002 representative in the cluster’s temporal or dHash spread).
2. One additional representative from clusters 707 and 708 each.
3. One representative from each of clusters 80, 66, and 64 to begin verification of these large groups.
4. Up to 15 frames from the `documented_unverified` set to validate historical notes.
5. Any remaining slots filled by no-note, full-desktop (1920×1080) frames that are not near-duplicates of already-reviewed UI.

### Command to Build the Batch
The batch can be constructed using the registry’s queue tool, which prioritizes:
1. Frames with no note at all (highest priority — fresh UI).
2. Frames with unresolved note references (none currently exist).
3. Partial frames (one exists: `2026-07-17 234521`, now advanced to `verified`).
4. Skipped frames (none).
5. Duplicate candidates (none).
6. Generic note-only frames (low-evidence notes).
7. Documented-but-unverified frames (the 45 legacy-note-named frames).
8. Not-reviewed frames (the residual `not_reviewed` set).

A suitable selection is:
```
python tools/screenshots.py queue --state not_reviewed --limit 20
```
Then manually curate the output to respect the priorities above, or run:
```
python tools/screenshots.py queue --limit 30
```
and verify that the selection includes cluster representatives, documented-unverified frames, and fresh UI.

### Expected Output
For each frame in B003, the review should record:
- Screen type and game context (training, skill selection, results, etc.).
- Visible controls, UI structure, and readable numerical values.
- Any previously undocumented mechanics or layout patterns.
- Notes on illegibility or uncertainty where applicable.
- State transition to `verified`, `partial`, or another appropriate state with a recorded reason.

All mutating operations (`review`) must be executed sequentially to prevent inventory corruption, as demonstrated by the concurrency regression test.

## Validation After B003

After completing B003, run:
- `python tools/screenshots.py validate` → 0 fails, 0 warns
- `python tools/screenshots.py selftest` → all 14 assertions held
- `python tools/screenshots_concurrency_test.py` → all phases (sequential/threads/processes) PASS
- Ensure no files outside `docs/research-scratch/screenshot-registry/` were modified.

The registry will then be ready for the subsequent batch or for use by Trainer Desk 2.0 implementation tasks.