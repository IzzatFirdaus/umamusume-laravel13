# Scenario Field Metadata — Gap Resolution Report

**Generated:** 2026-10-15
**Task date:** 2026-10-15
**Reference sources:** `docs/UMAMUSUME_REFERENCE.md` (dated 2026-09-27), `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` (dated 2026-10-09), the four scenario guides, the four scenario plans, and `config/`
**Scope:** The 8 N/A fields on the Scenario Select, Run Detail and Training Detail surfaces, across all four `[Global]` scenarios
**Machine-readable artifact:** `docs/research-scratch-source/scenario-field-metadata-2026-10-15.json`

---

## Fields Resolved

The eight fields the surfaces ask for are `ruleset_version`, `recommended_use`, `rule_family`, `planning_focus`, `expected_gains`, `energy_cost`, `risk` and `team_rank`. The matrix is 8 fields by 4 scenarios; at the start of this pass 8 of the 32 cells were already populated and 24 were not.

| # | Field | Surface | URA Finale | Unity Cup | Trackblazer | Our Grand Concert |
| - | ----- | ------- | ---------- | --------- | ----------- | ----------------- |
| 1 | `ruleset_version` | Scenario Select | RESOLVED | RESOLVED | RESOLVED | RESOLVED |
| 2 | `recommended_use` | Scenario Select | already filled | N/A (no source) | N/A (no source) | N/A (no source) |
| 3 | `rule_family` | Scenario Select | RESOLVED | already filled | RESOLVED | RESOLVED |
| 4 | `planning_focus` | Scenario Select | N/A (no source) | already filled | N/A (no source) | N/A (no source) |
| 5 | `expected_gains` | Training Detail | RESOLVED (JP) | RESOLVED (JP + Global note) | RESOLVED (JP) | RESOLVED (JP) |
| 6 | `energy_cost` | Training Detail | RESOLVED (JP) | RESOLVED (JP + Global note) | RESOLVED (JP) | RESOLVED (JP) |
| 7 | `risk` | Training Detail | NO SOURCE for a ranked table | NO SOURCE for a ranked table | NO SOURCE for a ranked table | NO SOURCE for a ranked table |
| 8 | `team_rank` | Run Detail | not applicable | RESOLVED | not applicable | not applicable |

**Counts.** Scenario-level cells (fields 1 to 4): 16 cells, of which 5 are N/A because no source names the field and 11 carry a value. Per-training-type cells (fields 5 to 7): 5 disciplines by 4 scenarios by 3 fields = 60 cells, of which 40 carry a resolved table (fields 5 and 6) and 20 report a measured negative (field 7, the ranked risk table). The `team_rank` field is a single Unity Cup cell, resolved.

### 1. `ruleset_version` — RESOLVED (all four)

| Scenario | Value | Global change | Source |
| -------- | ----- | ------------- | ------ |
| URA Finale | `2026-07-01` | Caps 1200 → 1400 in all five stats, Happy Meek duels added, +200 per-stat cap bonus | `UMAMUSUME_REFERENCE.md` §2.2.1, `SCENARIO-PUBLISHER-REFERENCES.md` line 982, `docs/URA-Finale-plan.md` line 16, `config/scenarios.php:114,150` |
| Unity Cup | `2026-07-01` | Caps 1200 → 1300 (Wit 1800), Extreme Spirit Bursts added, Special Training Energy cost removed, Burst hints from the card's own pool | `UMAMUSUME_REFERENCE.md` §2.2.2, `docs/scenarios/02-unity-cup.md:28`, `SCENARIO-PUBLISHER-REFERENCES.md` line 568 |
| Trackblazer | `2026-03-12` | Launch version, no subsequent rework recorded | `UMAMUSUME_REFERENCE.md` §2.2.3, `SCENARIO-PUBLISHER-REFERENCES.md` line 339, `docs/Trackblazer-plan.md` line 56 |
| Our Grand Concert | `2026-07-22` | Launch version (notice 899, "As of 10:00 p.m., Jul 22, 2026 (UTC)"), no subsequent rework recorded | `UMAMUSUME_REFERENCE.md` §2.2.4, `docs/scenarios/07-grand-concert.md:37` |

**Caution that governs this field.** `config/scenarios.php:375-376` states "no source defines a Global ruleset version, so no version number is invented." The value recorded here is the **scenario's Global (re)launch date**, not a client-stated version string. It is the dated instant a source pins the scenario's current numbering to, and the artifact labels it as a date for that reason. The UI's own `Ruleset version` row (`ScenarioSelect.vue:207-215`) reads a **different** field (`app.ruleset`) that is `null` by ruling; the two must not be merged into one label. Do not present the dates above as a ruleset version the client prints.

### 2. `recommended_use` — 1 RESOLVED, 3 N/A

| Scenario | Status | Value or reason |
| -------- | ------ | --------------- |
| URA Finale | RESOLVED | "Its guide names it the baseline for learning training, mood, energy and race timing before moving to scenarios with extra systems." (`config/scenarios.php:147`, `docs/scenarios/01-ura-finale.md:14`) |
| Unity Cup | N/A | No repo master names a recommended use. Searched: `docs/scenarios/`, `docs/Unity-Cup-plan.md` §2, `docs/UMAMUSUME_REFERENCE.md`. (`config/scenarios.php:388`) |
| Trackblazer | N/A | No repo master names a recommended use. Searched: `docs/scenarios/`, `docs/Trackblazer-plan.md` §2, `docs/UMAMUSUME_REFERENCE.md`. (`config/scenarios.php:405`) |
| Our Grand Concert | N/A | No repo master names a recommended use. Searched: `docs/scenarios/`, `docs/proposals/Our-Grand-Concert-plan.md`, `docs/UMAMUSUME_REFERENCE.md`. (`config/scenarios.php:728`) |

**Why the three stay N/A.** The guides describe each scenario's mechanics and its strategy, but none states a "who is this for" sentence the way the URA Finale guide does. The Trackblazer Game8 page prints an F2P-friendly observation (`SCENARIO-PUBLISHER-REFERENCES.md` line 1285) and the Grand Concert pages print PvP-build observations (line 2183-2185), but an observation about a build audience is not a recommended-use line, and lifting one into that field would be the invented fact `AGENTS.md` §5 forbids. They remain named absences with the search recorded.

### 3. `rule_family` — RESOLVED (all four)

Our own label for the mechanism that advances facility level, read straight from each entry's `facility_level_source` in `config/scenarios.php`. No publisher uses the term "rule family"; the field is a taxonomy over an already-sourced config fact.

| Scenario | Value | What it means | Source |
| -------- | ----- | ------------- | ------ |
| URA Finale | `repetition` | Every 4 uses of one discipline raises its facility by 1 level, max Lv5 | `config/scenarios.php:163`, `SCENARIO-PUBLISHER-REFERENCES.md` §4.7 line 1981 |
| Unity Cup | `team_rank` | Facility level is set by the team's stat rank, not by repetition | `config/scenarios.php:284`, `docs/scenarios/02-unity-cup.md:123`, `SCENARIO-PUBLISHER-REFERENCES.md` line 1093 |
| Trackblazer | `repetition_plus_purchases` | 4 uses per level, and Training Application items (150 coins) buy +1 level on top | `config/scenarios.php:421`, `SCENARIO-PUBLISHER-REFERENCES.md` line 509-510 |
| Our Grand Concert | `repetition` | Same as URA: 4 uses per level, max Lv5 | `config/scenarios.php:771`, `SCENARIO-PUBLISHER-REFERENCES.md` line 2509 |

### 4. `planning_focus` — 1 RESOLVED, 3 N/A

| Scenario | Status | Value or reason |
| -------- | ------ | --------------- |
| URA Finale | N/A | No repo master names a planning focus. Searched: `docs/URA-Finale-plan.md`, `docs/scenarios/01-ura-finale.md`, `docs/UMAMUSUME_REFERENCE.md`. |
| Unity Cup | RESOLVED | "Wit carries the scenario's own cap bonus; team rank and Spirit Burst counts decide the exclusive-skill payout." (`config/scenarios.php:390`) |
| Trackblazer | N/A | No planning focus is named. The plan's central question is "Race, train, or rest this turn, and does it keep me alive at the next Grade Point checkpoint?" (`docs/Trackblazer-plan.md` line 23), but no source calls it a planning focus. |
| Our Grand Concert | N/A | No planning focus is named. No publisher uses the term; the scenario's loop is described as "train → lessons → songs → lives" (`docs/scenarios/07-grand-concert.md:58-95`). |

### 5. `expected_gains` — RESOLVED per discipline (JP values, Global caveat)

The Training Detail card prints `Expected gains` as `N/A` today (`TrainingCard.vue:236-240`). The values exist and are published, per discipline and per training level, in the GameWith 257432 tables consolidated at `SCENARIO-PUBLISHER-REFERENCES.md` §4.4.

**Level-1 primary/secondary stat gain, all four scenarios:**

| Discipline | URA Finale | Unity Cup (Aoharu) | Trackblazer (Climax) | Grand Concert (Grand Live) |
| ---------- | ---------- | ------------------ | -------------------- | -------------------------- |
| Speed | +10 / +5 Power | +8 / +4 Power | +8 / +4 Power | +8 / +4 Power |
| Stamina | +9 / +4 Guts | +8 / +6 Guts | +7 / +3 Guts | +8 / +6 Guts |
| Power | +8 / +5 Stamina | +9 / +4 Stamina | +6 / +4 Stamina | +9 / +4 Stamina |
| Guts | +8 / +4 Speed +4 Power | +6 / +3 Speed +3 Power | +6 / +3 Speed +3 Power | +7 / +2 Speed +2 Power |
| Wit | +9 / +2 Speed, +4 SP | +6 / +2 Speed, +5 SP | +6 / +2 Speed, +3 SP | +6 / +2 Speed, +5 SP |

The per-level progression for every discipline and every scenario is in the JSON artifact under `training_tables`.

**Unity Cup's second gain path — team training.** The scenario's Spirit Burst loop adds a second table, published per flame count by GameTora (`docs/scenarios/02-unity-cup.md:87-106`): 2 flames = 2 primary / 1 secondary / 3 SP; 5 flames = 10 / 5 / 7 SP. Guts and Wit route the secondary differently. Scenario-linked cards add +5 all stats and +3 SP on a normal Burst.

**Our Grand Concert's second gain path — Performance.** Training also pays the scenario's resource, in five types (Dance, Passion, Vocals, Visuals, Composure). GameTora supplies 200 each at start and +50 per successful concert (`docs/scenarios/07-grand-concert.md:64-70`). Friendship training pays two types where ordinary training pays one.

**Server caveat, stated plainly.** The per-level tables are `[JP]`, from GameWith 257432, keyed to the JP scenario names URA / Aoharu / Climax / Grand Live / Grand Masters. `SCENARIO-PUBLISHER-REFERENCES.md` line 34 states "no JP figure here may be quoted as a Global expectation." No source in this repository publishes a Global-specific per-level training table, so these remain the only published figures and are tagged `[JP]` in the artifact. For Unity Cup the one documented Global divergence is the SP payout: Global pays +2 SP on the four Energy disciplines where JP pays +4 (`docs/scenarios/02-unity-cup.md:255`).

### 6. `energy_cost` — RESOLVED per discipline (JP values, Global caveat)

The Training Detail card prints energy cost from the advisor's declared constant, `config('advisor.constants.session_cost')`, which is the range `[17, 28]` sourced from a **stale** (2023-02-25) GameWith figure (`config/advisor.php:39-45`). The GameWith 257432 tables give the per-discipline, per-level costs the range was flattened from.

**Level-1 energy cost per discipline (positive = refund):**

| Discipline | URA Finale | Unity Cup (Aoharu) | Trackblazer (Climax) | Grand Concert (Grand Live) |
| ---------- | ---------- | ------------------ | -------------------- | -------------------------- |
| Speed | −21 | −19 | −19 | −19 |
| Stamina | −19 | −17 | −17 | −20 |
| Power | −20 | −18 | −18 | −20 |
| Guts | −22 | −20 | −20 | −20 |
| Wit | +5 | +5 | +5 | +5 |

At Lv5 the four Energy disciplines cost more: URA Speed −27, Unity Cup −25, Trackblazer −25, Grand Concert −25 (full grid in the artifact). **Wit refunds a flat +5 Energy at every level in every scenario**, which is the `wit_cost` constant's basis (recorded as `0` in config, because the advisor reasons about cost, not refund).

**A caution on the config range.** `[17, 28]` spans roughly the cheapest and dearest single-discipline single-level figures across the tables, which is why it is declared a range and never a point (`config/advisor.php:18-21`). The per-discipline values above are tighter than the range but are `[JP]`-sourced and, for URA Finale and Unity Cup, predate the 2026-07-01 Global rework. The rework did not alter base training energy costs per any source here, but the `2023-02-25` staleness of the published range is a live caveat the surfaces already carry.

### 7. `risk` — NO SOURCE for a ranked discipline-level table

The Training Detail card prints `Risk` as `N/A` with a reason, and marks `Wit` specifically as `RiskNotMeasured` (`TrainingCard.vue:277-286`, `TrainerAdvisor::RISK_NOT_MEASURED`). A ranked, per-discipline risk table is what would fill this field, and **no publisher states one**.

What the sources do publish is the **failure outcome set**, which is global across every scenario that uses the standard training loop (`SCENARIO-PUBLISHER-REFERENCES.md` §1.5, GameWith 257432). Two event variants exist, with per-character wording but identical numbers:

| Dimension | Range |
| --------- | ----- |
| Primary stat loss | −5 to −10 |
| Secondary stat loss | 0, or −10 to two random stats |
| Energy delta | 0, or +10 |
| Motivation delta | −1 tier |

**Directional energy-cost correspondence exists, but no ranked risk table does.** GameWith and Game8 both observe that a cheaper session can be attempted more often, but neither ranks disciplines by failure risk. The bounded statement the artifact carries is: the failure outcome set is known and scenario-independent; a per-discipline risk ordering is not published and must not be invented.

**Scenario-specific risk notes that are sourced:**

- **Unity Cup** — an Extreme Spirit Burst sets the failure chance to 0% on the facility holding the active Burst (`docs/scenarios/02-unity-cup.md:113-119`).
- **Trackblazer** — Race Fatigue: 3 races in a row, higher chance at 4, can lower Mood and apply Skin Outbreak; 4 in a row can decrease stats (`SCENARIO-PUBLISHER-REFERENCES.md` line 1226).

### 8. `team_rank` — RESOLVED (Unity Cup only)

The Run Detail team block shows five stat grades, all `N/A` today (audit line 398-399). The rank-to-facility mapping is published and is the same quantity the `team_rank` field names.

| Team Stat Rank | Facility Level |
| -------------- | -------------- |
| G – F | 1 |
| E – D | 2 |
| C – B | 3 |
| A | 4 |
| S | 5 |
| S+ | 5 (second "It's On!" hint; no further level) |

Sources: `docs/scenarios/02-unity-cup.md:125-131`, `SCENARIO-PUBLISHER-REFERENCES.md` line 1093, `docs/Unity-Cup-plan.md` line 202. Tagged `[Global]`. The post-2026-07-01 rework added S+ above the previous S ceiling; older guidance treating S as the top of the ladder is incomplete.

Not applicable to the other three scenarios: URA Finale (`facility_level_source` `repetition`), Trackblazer (`repetition_plus_purchases`) and Our Grand Concert (`repetition`) carry no team layer.

---

## Sources Consulted

| Source | Path | Scope used |
| ------ | ---- | ---------- |
| Reference corpus | `docs/UMAMUSUME_REFERENCE.md` | §1.1 training/rest/energy, §1.5 failure, §2.2.1-2.2.4 scenario mechanics, §2.8 Grand Concert, §7 conflict log |
| Publisher extraction | `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` | §1.5 failure outcomes, §4.2-4.7 training tables and level progression, Trackblazer and Unity Cup sections, Game8 scenario extraction |
| URA Finale guide | `docs/scenarios/01-ura-finale.md` | baseline framing, facility repetition, recommended-use line |
| Unity Cup guide | `docs/scenarios/02-unity-cup.md` | team rank ladder, Spirit Burst tables, post-rework deltas, SP payout note |
| Grand Concert guide | `docs/scenarios/07-grand-concert.md` | Performance loop, launch date, level-4 facility rule |
| Scenario plans | `docs/URA-Finale-plan.md`, `docs/Unity-Cup-plan.md`, `docs/Trackblazer-plan.md`, `docs/proposals/Our-Grand-Concert-plan.md` | central question per scenario, facility-level sourcing, planning-focus candidates |
| Config matrix | `config/scenarios.php`, `config/advisor.php` | `facility_level_source`, existing metadata, advisor constants and their staleness |
| Surfaces | `resources/js/pages/Career/ScenarioSelect.vue`, `resources/js/components/career/TrainingCard.vue` | which fields the cards print and how an absent one is named |
| Advisor engine | `app/Services/Advisor/TrainerAdvisor.php`, `AdvisorOption.php` | `RiskNotMeasured`, energy-after ranges |

---

## Source Ledger

Every value in the artifact carries one of four server tags:

- **`Global`** — sourced from a `[Global]` page, a Cygames notice, or committed Global config. Safe to present as Global fact.
- **`JP`** — sourced from a JP-side publisher (GameWith, Game8 JP, Kamigame). Not to be quoted as a Global expectation per `SCENARIO-PUBLISHER-REFERENCES.md` line 34.
- **`JP + Global(rework)`** — a JP base table plus a documented Global rework delta, recorded separately rather than merged.
- **`no source`** — established by a recorded search that found nothing.

All four `ruleset_version` values, all four `rule_family` values, the `team_rank` mapping and the URA Finale `recommended_use` are `Global`. The per-level `expected_gains` and `energy_cost` tables are `JP` (Unity Cup additionally carries a Global SP-payout delta). The failure-outcome set is `JP`. The three absent `recommended_use` and `planning_focus` cells are recorded searches.

---

## Conflicts and Unresolved Items

1. **JP training tables vs a Global presentation.** No source publishes a Global per-level training table. The artifact records the `[JP]` values with an explicit `global_note`; promoting them to Global without a capture would be the error line 34 of the publisher extraction warns against.
2. **`ruleset_version` vs `app.ruleset`.** Two different facts share a surface label area. The field here is the scenario's Global (re)launch date; the UI's `Ruleset version` row reads `app.ruleset`, which is `null` by ruling (`HandleInertiaRequests`). They must not be collapsed into one value.
3. **`planning_focus` for three scenarios.** The plans each open with a "central question" that reads like a planning focus, but no source uses the term. They are recorded as candidates in the reasons and left `N/A`.
4. **Ranked `risk` table.** The measured negative. The failure outcome set is published and scenario-independent; a discipline-level risk ordering is not.
5. **Stale `session_cost` range.** `config/advisor.php` already flags `[17, 28]` as `stale` (2023-02-25). The per-discipline tables are tighter but no fresher, so neither resolves the staleness; a newer capture would.

---

## Cross-Server Notes

- **URA Finale** and **Unity Cup** share the 2026-07-01 Global rework, which corresponds to JP's 2023-01-20 revision for Unity Cup (`SCENARIO-PUBLISHER-REFERENCES.md` line 568). Both reworks changed caps and added mechanics but not the base per-discipline training values.
- **Unity Cup SP payout** is the one documented Global-vs-JP divergence in the training tables: Global pays +2 SP on the four Energy disciplines where JP pays +4 (`docs/scenarios/02-unity-cup.md:255`).
- **Trackblazer** launched 2026-03-12 with the standard 1200 base caps on Global (`SCENARIO-PUBLISHER-REFERENCES.md` line 526); its higher JP caps are not a Global expectation.
- **Our Grand Concert** launched 2026-07-22; its Performance values come from GameTora and the resource noun from Cygames' own notice 905.

---

## Repository Next Steps

These are recorded for the owner; nothing here is a change to make autonomously.

1. **The two governed fields need an owner ruling, not a config edit.** `app.ruleset` is `null` by ruling and `session_cost` is a declared stale range by ruling. Populating `ruleset_version` from the launch dates, or narrowing `session_cost` to the per-discipline tables, crosses a ruling the artifact does not have authority to move.
2. **Training Detail's `Expected gains` and `Energy cost`.** Both now have sourced per-discipline values at every level. The value that would fill `Expected gains` is known and is `[JP]`; presenting it needs the surface to carry the server tag, the way the audit already shows energy cost carrying its constant's read date.
3. **`Risk`.** No ranked table exists. The bounded statement that can be shipped is the failure outcome set plus the two scenario-specific notes (Unity Cup ESB 0%, Trackblazer Race Fatigue). Whether to ship that bounded form is an owner decision.
4. **`team_rank`.** The mapping is sourced and `[Global]`. The Run Detail block that prints all-`N/A` grades is the surface it would fill.

---

## Lore Gate

`php tools/lore.php docs` and `composer lore` were run against `docs/research-scratch-source/` on 2026-10-15. Output recorded in the session hand-off. Every hit was classified against the four allowed classes in `AGENTS.md` §5 (naming the list to forbid it, substrings inside ordinary words, verbatim quoted source data, a gate's own pattern source). No new hit was introduced by these two files.
