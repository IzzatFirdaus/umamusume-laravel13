# Reference + framework verification — 2026-11-09

Two passes over [`scenario-detail-hfw.html`](./scenario-detail-hfw.html): every game-mechanic
claim against `docs/UMAMUSUME_REFERENCE.md` (and the scenario guide), then every checkable rule in
`docs/framework-ui-ux/` measured in the rendered page.

**Verdict:** the HFW's facts hold except three, which are corrected in the file. The framework pass
found two real defects in the artifact (both fixed) and no accessibility failure.

---

## Pass 1 — reference corpus

| # | Claim in the HFW | Corpus says | Result |
|---|---|---|---|
| 1 | Five team-race categories: Sprint, Mile, Medium, Long, Dirt | §2.2.2 "five races (Sprint, Mile, Medium and Long on turf plus a dirt race, one per category)"; `02-unity-cup.md:151` names the five | **Holds** |
| 2 | "Aim for at least 3 circles as a margin, not a win condition" | `02-unity-cup.md:163`: "aim for at least 3 circles overall, because losing decreases your league rank" | **Holds** |
| 3 | A loss can be retried with an Alarm Clock post-2026-07-01 | §2.2.2 "a lost Unity Cup race can be retried with an Alarm Clock"; table row 858 | **Holds** |
| 4 | A team race every 6 months, Late June and Late December | §2.2.2 "resolve at the end of Late June and Late December and consume no turn of their own" | **Holds** |
| 5 | Facility ladder G/F→1, D/E→2, B/C→3, A→4, S→5 | §2.2.2 (2026-10-25 block): "F/G → 1, D/E → 2, B/C → 3, A → 4, S → 5" — same pairing the app prints | **Holds** (the pairing is the source's, not a sort bug) |
| 6 | S+ sits above S, grants a second hint not a higher facility | §2.2.2: "`S+` is the **overall** team rank… not a per-stat value"; the ladder note | **Holds** |
| 7 | Band rewards: white Lv1 +10/+10, white Lv3 +20/+20, gold Lv1 +30/+30 at 10–12, gold Lv3 +40/+40 at 13+ | §2.2.2 exclusive-skill bullet, verbatim | **Holds** |
| 8 | Payout in Senior Year, Late November | §2.2.2: "in **Late** November of Senior year, not early November as this bullet read until 2026-11-06" | **Holds** |
| 9 | Extra Energy cost on Special Training removed 2026-07-01 | `02-unity-cup.md:45`; §2.2.2 rework list | **Holds** |
| 10 | A burst on Wit grants +5 Energy recovery | `02-unity-cup.md:107`: "a Burst on the **Wit** facility additionally grants **+5 Energy recovery**"; `config/scenarios.php:426` | **Holds** |
| 11 | Caps 1200+100=1300 ×4, Wit 1200+600=1800 | §1.3.4 (Unity Cup 1300 / Wit 1800 post-rework) | **Holds** (arithmetic checked) |
| 12 | "Up to three Umamusume fielded per category" | §2.2.2: "up to three Umamusume per race"; `02-unity-cup.md:287`: 1–3 per category | **Corrected** — the HFW said "3 fielded in each", which reads as a fixed 3. Config's `field_per_category => 3` is the ceiling. |
| 13 | Team Ranking Bonus: All Attributes +30, arising from the rank | **The corpus says it is the league-placement bonus.** `docs/research/flows-career-workspace.md:232`: 2nd–5th +50, 6th–10th +30, 11th–15th +20, 16th–20th +10; this run's 8th = +30 band. The "+30 fires on each overall-rank increase" table in `SCENARIO-PUBLISHER-REFERENCES.md:1093` is a **different mechanic** (F +2 … S +5 and +1 hint). | **Corrected** — figure right, attribution wrong. The HFW now carries the placement reading. `UnityCupPanel.vue:128-131` still carries the old inference; noted, not fixed (no code change here). |
| 14 | "Two other publishers report 15/15/20/20 SP" | Not in `UMAMUSUME_REFERENCE.md`. Conflict row 57 says the two publishers differ by **edges vs criteria** (GameTora prints 4–9/7–9/10–12/13+; the Wiki prints criteria, no numbered edges), not by SP figure. The 15/15/20/20 line lives in the run report and `RACE-AND-SLICE-RESEARCH.md`, not in the reference corpus. | **Corrected** — caveat rewritten to what §2.2.2 and row 57 actually say. The app's own `UnityCupPanel.vue:370` still carries the unsourced phrasing; noted, not fixed. |
| 15 | Six Spirit Burst states named as the client's | `SpiritBurstState.php:10-12` (D-20): "Case values are this tool's identifiers, not client strings: no Global English capture names these states." | **Corrected** — panel now says these are the tool's words, not the client's. |
| 16 | "No source says how strong each opponent team is" | §2.2.2 names the Elite Team's three unlock conditions and the approximately-20-per-stat / 28-per-stat boosts — but no per-opponent strength ranking | **Holds** (the absence is narrower than the corpus, not contradicted) |
| 17 | 8 trainee goals, `Goals Achieved 1 of 8` | §1.6 goal-panel shape (one active target, status enum, footer ratio); the ladder is per-character (`ADR-0026`, S-A-P01.4) | **Holds** |

**Reference verdict:** 13 of 17 hold outright; 4 corrected (12, 13, 14, 15). Two of those four are
also wrong in the app, not just in this artifact — recorded above rather than silently matched.

---

## Pass 2 — `docs/framework-ui-ux/`

Measured in Chromium 153 on the rendered file, both themes. Rule IDs are the framework files'
own.

| Rule | Source | Measured | Status |
|---|---|---|---|
| **WCAG 1.4.3** Contrast | `wcag-2.2-accessibility-2026-10-21.md:158` | every text node, light **0 failures**, dark **0 failures** (ratio computed against the effective background, `color(srgb …)` handled) | **Pass** |
| **UX-1** 44px targets | `laws-of-ux-2026-10-21.md:164` | **11 undersized `a.link-rec` at 62×35** on first measure → after fix **0 undersized** (11 links at 95×44) | **Defect found, fixed** |
| **UI-1** three-step ladder | `uilaws` via shell.css:378 | h1 28 / h2 15 / body **+ a fourth step at 13** (`h3` inside the caps card) → after fix **h1 28 / h2 15 / body only** | **Defect found, fixed** |
| **WCAG 1.4.10** Reflow | `wcag:…` (1.4.10) | at 320px: document 320 = viewport 320, no page-level sideways scroll; wide tables scroll **inside** their card (`overflow-x:auto`) | **Pass** |
| **WCAG 2.4.1** Bypass blocks | `wcag:…` | skip link present, `main#main` present | **Pass** |
| **WCAG 1.3.1** Info & relationships | `wcag:…` | 7 tables checked: 2 lacked any `<th>` (identity, stat grades) → after fix **all 7 carry headers**, 14 row headers `scope="row"` | **Defect found, fixed** |
| **WCAG 4.1.2** Name, role, value | `wcag:…:169` | 0 links without an accessible name, 0 images without `alt` | **Pass** |
| **WCAG 1.4.1** Use of colour | `wcag:…:157` | every state pill carries a word (`Not reached`, `Missed`, `Recorded`, `Upcoming`, `Documented`, `Estimated`); the ladder's current rung carries `current` as text | **Pass** |
| **2.5.8** Target size | — | Never claimed. The 44px floor is the house rule (**UX-1**), which exceeds 2.5.8's 24px minimum. | Not claimed, correctly |
| **H1** Visibility of system status | `usability-heuristics:…` | each panel states its own state; absences name their reason | **Pass** |
| **H8** Aesthetic / minimalist | same | every element carries information; no filler tiles | **Pass** |
| **GP-2 / GP-3** Derived-figure provenance | `gerhardt-powals:118-119` | bands carry `Estimated` + publisher-conflict text; caps carry `Verified 2026-11-02`; facility level names its derivation; the +30 carries its (now corrected) attribution | **Pass** |
| **IA-6 / G-33** No scenario name in a component | `AGENTS.md` §7 | the HFW is a static document and reads no config; the panel set it draws comes from `UnityCupPanel.vue`'s registry keys, not a name branch | **Pass** |
| **D-220** Absence never a zero | `AGENTS.md` §5 | every unrecorded figure prints `N/A` with its reason; the one `N/A` that should be `11` is labelled a defect, not accepted | **Pass** |

Not measured, and why: axe-core was not run (the v2 set's `framework-audit.mjs` owns that pass for
its own 56 pages and does not know this folder); motion, timing and authentication criteria are
outside a rendered-frame check, exactly as the v2 README records for its own set.

---

## What changed in the artifact

Seven edits, all in `scenario-detail-hfw.html`:

1. `.link-rec` class — the app's own `min-h-11` on every "Record this race" (UX-1).
2. The caps card's `h3` → `.sublabel` paragraph (UI-1).
3. Row headers `scope="row"` on the identity, stat-grade and coverage tables (1.3.1).
4. `white-space:nowrap` on the due cells and the action column, so a due date reads on one line.
5. The +30 attribution corrected to the league-placement reading (reference #13).
6. The bands caveat rewritten to §2.2.2 / conflict row 57 (reference #14).
7. The coverage copy "3 fielded" → "up to three fielded" (reference #12), and the six burst states
   marked as the tool's own words (reference #15).

Three of these describe defects in the **app**, not only in this drawing, and each is now filed in
`KNOWN-ISSUES.md` rather than left in this report:

| Register row | App site | What is wrong there |
|---|---|---|
| **KI-154** | `ScenarioSectionBuilder.php:84` | Every widget but `team_rank` is hardcoded `null`, so a run with 6 + 5 bursts prints `Spirit Bursts: N/A` while the bands panel below it prints `11, inside 10–12`. |
| **KI-155** | `UnityCupPanel.vue:128-131` | The `+30` note binds the figure to the team rank; `flows-career-workspace.md:232` records it as the league-placement bonus. |
| **KI-156** | `UnityCupPanel.vue:370` | The caveat cites a `15/15/20/20` publisher figure the reference corpus does not carry; conflict row 57 records the real split as edges-versus-criteria. |

No code was changed by this pass. The HFW's own copy carries the corrected readings and, for
KI-154, names the tile a defect rather than reproducing it as an honest absence.
