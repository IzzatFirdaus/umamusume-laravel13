# 02 — Cross-cutting (seeded re-audit)

Same shape as the prior pass. Each class is re-stated as **holding** or **not holding** on populated data.

## Colour and contrast

| Observation | Status |
|---|---|
| **Light-mode contrast held.** No failing pair surfaced on any populated screen; the prior pass's 246-pair sweep with 0 failures is not contradicted by anything here. | holds (not re-swept in full; no new failing text observed) |
| **Cards read as one system.** Every populated screen carried the same bordered/rounded surface family over the `rgb(244,244,255)` page. No populated screen introduced a second card style. | holds |
| **Dark mode** was not re-entered. | not re-checked |

## Typography and hierarchy

| Observation | Status |
|---|---|
| **One `h1` per route** across all 57 visits, including every populated screen. | holds |
| **Heading scale unchanged** (h1 30/600, h2 16/800) on populated pages; a populated Cockpit's 17-row table did not disturb it. | holds |
| **Numbers stay tabular** in the populated stat rail (`607 / 1,325 cap`) and the result screen's stat tables. | holds — and it is what makes BO-013's noun flip legible: same type, different word |

## Spacing and layout

| Observation | Status |
|---|---|
| **Zero horizontal overflow** on all 57 visits at both viewports, including the 17-row Cockpit, the 14-row Races table and the 62-row Scenarios table. Populating the screens did **not** introduce sideways scroll. | holds — stronger than the prior pass could claim |
| **Wide tables still scroll inside their container** at 390px. | holds |
| **The populated Timeline fits** — 6 entries, `Previous / Next` pagination, no overflow at 390. | holds |

## Interaction

| Observation | Status |
|---|---|
| **Loading/empty/error states are distinct and named.** The populated screens add a fourth state the empty walk could not see: a *partially* recorded run (energy `N/A` with `title="This turn recorded no energy reading."` while SP is `173`). Absence is still disclosed per-cell, not globalised. | holds — and this is the strongest evidence in the pass that the `N/A` + `title` pattern works at scale |
| **Inline validation still field-local** on the wizard. | holds |
| **Disabled-until-ready** on the wizard primaries. | holds |
| **A result screen for an Active run** correctly refuses rather than rendering empty: `This career is still running, so there is no result to read yet. The result screen reads a run whose status is Completed; until then the Cockpit is where the next turn is decided.` | holds |

## Copy and terminology

| Observation | Status |
|---|---|
| **Trainer vocabulary throughout**, no developer vocabulary on any populated screen (the densest surfaces — Cockpit, Scenario detail, Result — were checked). | holds |
| **No apologetic copy.** Where a figure is absent the screen states the omission as fact, e.g. `Team Rank — Rank not recorded: the league letter is read off the client, and this run has none entered yet.` | holds |
| **`N/A` + `title` disclosure works** on Timeline, Training, Skills planner, Scenario detail and Result. | holds |
| **`N/A :` bare colon** on `/training-runs`, `/legacy`, and now `/training-runs/14/deck`. | **does not hold** — BO-009, and the class is broader than filed |
| **The same fact printed twice with different words** (`cap` vs `floor`, same component, two states). | **does not hold** — BO-013 |

## Domain fidelity — exercised for the first time

This is the class the prior pass could only name. With a populated Unity Cup career the app can be set against the
client frames.

| Doc (client) | App (rendered) | Verdict |
|---|---|---|
| Live caps `1325 / 1322 / 1368 / 1308 / 1800` over a `1300` floor (§1.2) | `607 / 1,325 cap`, `577 / 1,322 cap`, `549 / 1,368 cap`, `736 / 1,308 cap`, `397 / 1,800 cap` | **exact match** on all five caps |
| Live stat values 607 / 577 / 549 / 736 / 397 (§1.2) | identical, all five | **exact match** |
| `Team Blue Bloom`, motto `Dreaming Big`, league **8th**, Team Rank **S**, `Team Ranking Bonus: + 30` (§1.8) | `Team Blue Bloom · Dreaming Big · League 8`; Team Rank reads `Rank not recorded` on the Scenario detail | **name/motto/placement match**; **rank not recorded**; **bonus absent** → BO-015 |
| `Spirit Bursts` — six blue sparks across the six-ancestor tree (§1.5) | Cockpit: `No blue spark is recorded in this run's Legacy.`; Inheritance: `PARENT A Not set / PARENT B Not set` | **not represented** — the seeder imports no legacy selection, so the app has nothing to show. Absence is correctly disclosed, not faked. (EX-16) |
| 20-member roster (§1.9) | Deck renders `Not equipped` ×6; the app stores no members | **divergent by design** — seeder writes no deck; Informational. The app's deck model is six cards and does not claim to hold a roster. |
| `Team Ranking Bonus: All Attributes + 30` at 8th (§1.8) | placement rendered, bonus nowhere | **BO-015**, the one clean domain-fidelity defect of the pass |

**Where the app matches the doc, it matches exactly** — all five caps and all five live stat values. That is worth
stating plainly: the arithmetic layer is correct and it is the *labels and the unrepresented systems* that diverge.

## States

| Observation | Status |
|---|---|
| **Empty states guide** (5 surfaces, prior pass). `Veterans` with 8 rows still keeps its filters and `Find parents for this build` door. | holds |
| **A run's own scenario drives its panels.** Run #14 (Unity Cup) renders Team/League/Bursts; the eight URA runs render race-calendar panels instead. No cross-scenario leakage observed on populated data. | holds — first time testable |
| **Two adjacent sentences can contradict** (BO-016: `11 inside 10-12` next to `need 2 more`). | does not hold |
