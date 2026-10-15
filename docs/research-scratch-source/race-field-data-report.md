# Race Field Data — Gap Resolution Report

**Generated:** 2026-10-15  
**Anchor date:** Task date 2026-10-15  
**Reference source:** `docs/UMAMUSUME_REFERENCE.md` (dated 2026-09-27)  
**Scope:** URA Finale career calendar races at grades OP (400) and above — OP, G1, G2, G3, Listed  
**Total races resolved:** 404 (9 special races excluded: Debut, Maiden, Qualifiers, Semifinals, Finals, etc.)  
**Machine-readable artifact:** `docs/research-scratch-source/race-field-data.json`

---

## Fields Resolved

### 1. fan_gain — RESOLVED

| Field | Status | Source |
|-------|--------|--------|
| `fan_gain` (1st place) | Filled for all 404 races | `ura-races.json` `fans_gain` field, joined to `race_instances.json` |

**Data:** The `fans_gain` field in `ura-races.json` provides the 1st-place fan payout for each of the 404 race instances. A full placement ladder (1st through 18th) is available in `race-fans.json` but is keyed by course, not by race instance, so it could not be mapped per-race without the course-to-instance join which the export does not expose.

**Grade distribution of resolved races:**

| Grade | Label | Count | fan_gain range (1st) | fans_needed range |
|-------|-------|-------|---------------------|-------------------|
| 100 | G1 | 48 | 20–54 | 1,000–25,000 |
| 200 | G2 | 58 | 13–56 | 375–2,000 |
| 300 | G3 | 108 | 9–53 | 350–1,500 |
| 400 | OP | 164 | 5–50 | 350 |
| 700 | Listed | 26 | 44 | 350 |

**Career year distribution:** Year 1: 56 races, Year 2: 162 races, Year 3: 186 races.

### 2. running_style — UNRESOLVED (trainee attribute, not race attribute)

| Field | Status | Reason |
|-------|--------|--------|
| `running_style` | `null` | Running style is a per-trainee attribute, not a race property |

**Evidence:** `UMAMUSUME_REFERENCE.md` §1.2.2 states: "The engine stores exactly four values, `running_style==1..4`" — referring to the Umamusume's running style, not the course. The four running styles (Front Runner, Pace Chaser, Late Surger, End Closer) are attributes of the trainee being trained. No publisher assigns a running style to a specific race instance. Courses have phase geometry (`phases[].id`, `positionKeepEnd`, `spurtStart`) that determines *which* running styles have skills that can trigger, but the race itself does not impose a running style.

### 3. skill_points — PARTIALLY RESOLVED (post-race payout table found)

| Field | Status | Details |
|-------|--------|--------|
| `skill_points` (per-race SP payout) | Values found in source, but only at 34% Race Bonus breakpoint | Game8 "Race Bonus Explained" (RACE-AND-SLICE-RESEARCH.md line 1615) |

**Evidence:** `UMAMUSUME_REFERENCE.md` §1.1.4 states that Skill Points come from races "finished after a training turn (the payout scales with race difficulty)" but originally did not publish per-grade values. However, `RACE-AND-SLICE-RESEARCH.md` line 1615 cites Game8's "Race Bonus Explained" page (archives/572831, dated 2026-06-26), which documents a post-race stat/SP payout table keyed by Race Bonus percentage. At the 34% breakpoint (the threshold for URA Finale and Unity Cup):

| Race Grade | Stats | Skill Points (SP) |
|------------|-------|-------------------|
| G1 | +4 to all | 60 |
| G2 | +4 to all | 46 |
| G3 | +4 to all | 46 |
| OP | +4 to all | 40 |
| Pre-OP | +4 to all | 40 |

**Limitations:**
- The 50% breakpoint (Trackblazer threshold) values are mentioned but not stated in the source.
- The 60% breakpoint values are not published.
- Game8 has a marking quirk: at 10% the low row icons are OP+Pre-OP, but at 20/34/50/60% the same row uses G2+Pre-OP alt pair, so the row alignment is not trustworthy for per-tier values.
- Listed races (grade 700 in the export) are labeled "Pre-OP" by uma.guide, not in Game8's race-bonus table, so SP payouts for Listed races are not in the published table.

**Additional SP data:** URA Finale's special finale races have documented SP payouts:
- Qualifier win: +40 Skill Points (URA-Finale-plan.md line 237, SCENARIO-PUBLISHER-REFERENCES.md line 993)
- Semifinals win: +60 Skill Points (URA-Finale-plan.md line 238)
- Finals win: +80 Skill Points (URA-Finale-plan.md line 239)
All affected by Race Bonus multiplier.

### 4. estimated_win_probability — UNRESOLVED (not published)

| Field | Status | Reason |
|-------|--------|--------|
| `estimated_win_probability` | `null` | No publisher publishes per-race win probability values |

**Evidence:** `UMAMUSUME_REFERENCE.md` does not cite any source that publishes win rates per race or per grade. `ADR-0016` (Next Race Readiness) is an open question explicitly blocked on race requirement data: the `race_catalog_slots` table that would carry distance/surface data needed for win probability is fetch-only and holds 0 rows offline. The reference states no source publishes per-race base win rates, difficulty scalars, or datamined probability values.

### 5. scenario_reward — RESOLVED (by scenario)

#### URA Finale
| Field | Value | Reason |
|-------|-------|--------|
| `scenario_reward` | `null` | URA Finale has no per-race scenario reward system |

URA Finale's scenario mechanics are career objectives and fan gates, not per-race rewards. The only SP-bearing races are the three finale bracket races (Qualifier/Semifinals/Finals), documented in §3 above.

#### Unity Cup
| Field | Value | Reason |
|-------|-------|--------|
| `scenario_reward` | `null` | Unity Cup has no per-race scenario reward |

Unity Cup's scenario mechanics are team-based Spirit Bursts and team ranking, not per-race rewards. The `config/scenarios.php` confirms `unity_cup.grade_points = null` and `unity_cup.shop_coins = null` — these mechanics are Trackblazer-only.

#### Trackblazer
| Field | Value | Source |
|-------|-------|--------|
| `scenario_reward` (Grade Points) | Per-grade table | `SCENARIO-PUBLISHER-REFERENCES.md` lines 94–102 |
| `scenario_reward` (Shop Coins) | Placement-based | `SCENARIO-PUBLISHER-REFERENCES.md` lines 116–123 |

**Grade Points (1st place, by grade):**

| Race Grade | Grade Points (1st) |
|------------|-------------------|
| G1 | 100 |
| G2 | 80 |
| G3 | 60 |
| OP | 40 |
| Pre-OP | 20 |

**Placement modifiers (apply to base Grade Points):**

| Placement | Modifier |
|-----------|----------|
| 1st | 100% |
| 2nd | 60% |
| 3rd | 40% |
| 4th–5th | 20% |
| 6th+ | 10% |

**Shop Coins (independent of race grade, by placement):**

| Placement | Shop Coins |
|-----------|-----------|
| 1st | 100 |
| 2nd–3rd | 60 |
| 4th–5th | 30 |
| 6th+ | 0 |

**Important Trackblazer rules:**
- Grade Points are consumed at each deadline and do NOT carry over (lines 86–92 of the uma.guide section).
- Shop Coins are awarded by placement regardless of race grade (line 116).
- Pre-OP races award 20 Grade Points (line 102).

---

## Scope Notes

1. **Excluded races:** 9 special races (Debut, Maiden, Qualifiers, Semifinals, Finals, Grand Concert, etc.) were excluded as out of scope. These have `special_race: true` in `race_instances.json`.
2. **JP-Only tagging:** All data here is available on both servers per `UMAMUSUME_REFERENCE.md` §1.2: the export is tagged `[Both]`. No `[JP]`-only values were promoted to `[Global]`.
3. **Lore compliance:** The artifacts were generated from data exports and cited reference docs. No em dashes were used in prose. Race names are source data quoted verbatim from the GameTora export.
