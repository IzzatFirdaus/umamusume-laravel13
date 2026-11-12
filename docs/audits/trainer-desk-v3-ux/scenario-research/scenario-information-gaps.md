# Scenario information gaps

**Phase 0 deliverable 4 of 6.** What the research could not settle, what it settled against the repository's own
record, and the rulings only the owner can give.

This document is not a defect register. A gap here is a place where the **app is right to print an absence**.
A conflict here is a place where the **app prints one thing and a better source says another** — those are
reference-pass findings waiting for a walk to reach them, and three of them need an owner ruling before any walk
can file them as anything.

---

## 1. Gaps by scenario, ranked by what they block

### 1.1 URA Finale

| Gap | What it blocks | Highest tier available | Upgrade path |
|---|---|---|---|
| The Akikawa-bond half of the April fan gate | The gate cannot be **evaluated**, only stated. No column stores a bond reading | B (the requirement), C (the association) | A bond column, or an entered reading on a turn |
| Per-duel stat gain as a function of the dueled stat | The duel panel's `stat_gain_values` is `null` and must stay `null` | C | A capture of a duel at a known duel level |
| Wins required to reach Happy Meek's MAX | A single figure cannot be stated: Game8 says six, a community guide says five, and the repository's config encodes **6** | C | A capture, or an owner ruling to print both |
| Mood's training multiplier | Any mood figure the app prints is invented | none found | A client capture of the training screen at each mood tier |
| A trainee's starting stat block | Every test career starts at `N/A` | none found | A capture of a fresh career's first turn |
| Facility leveling at 4 uses/level | Currently tier C, one guide | C | A second independent source, or a client capture |

### 1.2 Unity Cup

Thinnest research need: the scenario has eight walks and a committed seeder. The gaps that matter are the ones
the **seeded reference run** cannot answer.

| Gap | What it blocks | Highest tier available |
|---|---|---|
| The finals' per-team-name gold skill mapping | The app does not model it and no test needs it | B |
| Whether the 2026-07-01 rework changed Unity Cup's caps | `config/scenarios.php` records `ruleset_version` as the rework date with caps 1300 ×4 / Wit 1800; no source read this pass confirms the *caps* as reworked | B |
| Spirit Burst's exact per-burst payout | The app counts bursts, never prices them, which is correct | B |

### 1.3 Trackblazer

| Gap | What it blocks | Highest tier available | Upgrade path |
|---|---|---|---|
| The yearly-target **dirt-leaning** and **limited-turf-range** tracks have no trainee selector | The app prints only the `standard` track because no column says which trainee belongs where | C for the variant figures, none for the selector | A per-trainee aptitude rule, which `umamusume.aptitude_turf` / `aptitude_dirt` already holds — see §3.4 |
| The year-end **bond** figures disagree (19/31/51 vs 15/30/50) | The gate prints both and compares neither, which is the honest shape | C, two publishers | A third source, or an owner ruling to pick one |
| The Pro Shop's rotation, refresh-on-win and stock duration | The app models none of the three and states their absence | B | Payload shapes, which is an architecture decision |
| Race Fatigue: whether "3 races" counts the race about to run | The app prints the row its label names and settles nothing | C | A capture |
| Race Fatigue: which year's Late December closes it | Same | C | A capture |
| A coin **balance** | The Pro Shop's whole economy is unrepresentable as a holding | none — the app totals spend only | An entered balance on a turn, or a payload |

### 1.4 Our Grand Concert

The largest gap set before this pass. Five client frames closed more of it than every prior guide combined.

| Gap | State | Note |
|---|---|---|
| The five type names and the resource's name | **CLOSED** | `Performance Points`; `Dance` `Passion` `Vocals` `Visuals` `Composure` |
| The lesson screen's wording | **CLOSED** | `Lessons`, `Select a technique or song to learn.`, `Performance Point Cost`, `Learn` / `Learnable!` / `Locked`, `Not enough performance points`, and the four card group headings |
| Song titles in English | **CLOSED, all 23** | Three from the frames, all 23 from A14; the sources register §5A lists them |
| The two bonus-layer names | **CLOSED** | mastery layer prints as a plain effect; the second layer prints as `Specialty Priority` / `Friendship Training Effectiveness` / `Support Chain Event Frequency` |
| The gauge | **PARTIALLY CLOSED** | `Hype Level`, state word `Mild Hype`. The full ladder and `Special Hype!` remain unmeasured |
| The reserve/schedule button's label | **OPEN, narrowed** | A14 gives the refusal (`Not enough performance points`) and the card states `Learn` / `Learnable!` / `Locked`; the scheduled state's own button is still unmeasured |
| Per-lesson cost curves | **OPEN** | Three costs were read off one run state; that is an observation, not a price list |
| Hype value per song | **OPEN** | No source quantifies it |
| Whether a lesson ends the turn | **RESOLVED, and negative** | The tutorial says lessons take no turn; several may be taken in one turn |
| A Performance **balance** | **OPEN** | No starting value published; the app stores observations and prints no total |

---

## 2. Conflicts between sources, recorded rather than silently resolved

Each row names both readings and the tier of each. Where the app already commits to one, the row says so.

| # | Topic | Reading A | Reading B | What the app does | Ruling owed? |
|---|---|---|---|---|---|
| 1 | Trackblazer's point currency | `Result Points` — Cygames' Global launch page (A) and Game8 (C) | `Grade Points` — uma.guide (C), umamusu.wiki's section heading (B) while its own body says Result Points | `Grade Points`, in config keys, code, and ~50 tests | **YES** — §3.1 |
| 2 | Grand Concert's second bonus type | `Speciality Rate Up` — `docs/scenarios/07-grand-concert.md`, from GameTora (B) | `Specialty Priority` — the Global client's song card (A) | Neither is printed: no surface renders the bonus names | Not for the app; a doc erratum for the guide |
| 3 | Happy Meek's wins to MAX | Six — Game8 (C) | Five — a community guide (C) | `escalation_wins => 6` | **YES** — either print both or cite one |
| 4 | Trackblazer's year-end bond | 19 / 31 / 51 — uma.guide (C) | 15 / 30 / 50 — GameWith (C) | Prints **both**, compares against neither | No; the printed shape is already honest |
| 5 | The fifth Performance type | `Composure` — the Global client (A), notice 905 (A) | `Mental` — GameTora (B7) | `Composure`, in `App\Enums\PerformanceType` | No; the client wins and the app already follows it |
| 6 | The finals' fan requirement for a URA career | 240,000 fans for the "Going to the URA Finale!" event (C) | No fan requirement for the finale itself — the finals ladder is gated on **winning** each race (B) | Nothing; the app models neither | No |
| 7 | Grand Concert's finale | The Grand Concert itself, Senior late December (A/B) | The three URA Finale races close every career (B/C) | `finale_label => 'The Grand Concert'` on two surfaces, `label => 'Our Grand Concert'` on a third | **YES** — §3.2 |
| 8 | Trackblazer's scenario-link | `Scenario Links: N/A` (B) | uma.guide's deck advice names link-type cards generically | `scenario_links => []` | No |
| 9 | The preflight Happy Meek row's figures | "+1 cap tier" — the shipped copy | "+4 points" — the client (A, cited in this pass's wiki read) | The shipped copy prints "Win pays 30 SP and +1 cap tier" | **YES** — §3.3 |

---

## 3. Rulings owed, with the evidence each needs

These are the only items in this document that need the owner. Each is written so a ruling can be given in one
line.

### 3.1 `Result Points` vs `Grade Points` — a vocabulary ruling

**The question.** The app's user-facing vocabulary for Trackblazer's point currency is `Grade Points`. The
publisher's own Global page says `Result Points` [A11]. Which word does the desktop surface print?

**Why it matters more than usual.** `Grade Points` appears in:

- `config/scenarios.php` — `grade_objectives`, `grade_point_by_grade`, `grade_points_surplus_carries_over`, and
  the four `shop_worth` strings do not use it
- `app/Models/TrainingRun.php`, `app/Models/RaceEntry.php`, `app/Services/Career/TrackblazerPanelBuilder.php`
- roughly fifty test files whose names carry `GradePoint`
- every rendered label the Trackblazer panel prints

**Why the app did not simply follow the publisher.** The rename is not cosmetic: `grade_objectives` is also the
name of a **panel flag** in the composition matrix, and renaming a flag is a config-surface change with a
migration path. The two decisions are separable and the owner may want them separate.

**Two readings, for a one-line ruling:**

| Option | Consequence |
|---|---|
| **A — print the publisher's word, keep the keys** | Every rendered label says `Result Points`; the config keys, the code and the tests keep `grade`. Cheapest, and it puts the tier-A word in front of the Trainer |
| **B — leave the vocabulary as it is** | One reference-pass finding stays open, and the next walk files it again |

**Recommendation: A.** The rule that decides it is already in `AGENTS.md` §13 — print the Global client vocabulary
only — and the client's own launch page is the client writing it down.

### 3.2 Which surface owns the Grand Concert finale's name

**The question.** Two surfaces print `The Grand Concert` (from `finale_label`) and one prints
`Our Grand Concert` (from `label`, via `FinaleReader::forRun()`). Is one of them wrong, or do the two names answer
two questions?

**Measured 2026-11-12, from the tree:**

| Surface | Reader | Prints |
|---|---|---|
| Cockpit obligation rail | `CockpitController::obligationLabel()` → `finale_label` | `The Grand Concert` |
| Race planner | `RacePlannerController::raceRow()` → `finale_label` | `The Grand Concert` |
| Scenario detail / result | `FinaleReader::forRun()` → `label` | `Our Grand Concert` |

**The reading that makes both right.** `finale_label` is the name of an **event**; `label` is the name of a
**scenario**. A row that says "which race is this" wants the event, and a block that says "which scenario's finale
did you run" could want either. That is a design question, not a bug, and this document does not settle it.

**What is definitely wrong.** If one screen shows the finale **twice** under two names, that is one fact printed
twice with two labels and is a finding regardless of the ruling. The walk should check whether any screen does.

### 3.3 The preflight duel copy states a cap tier where the client states points

**The question.** `resources/js/pages/Career/Preflight.vue:812-813` prints:

> `Can appear on any stat-training turn. Win pays 30 SP and +1 cap tier; lose pays 15 SP.`

**Two problems, both measured:**

1. **The cap figure is the wrong shape.** umamusu.wiki and the client both say a win raises the dueled stat's
   **cap by 4 points** [B1], not by "a tier". The config's own `duel.win.gains` says `stat gains, and one tier on
   the cap of the stat dueled`, so the page is faithfully printing the config, and the config is the stale layer.
2. **The SP figures are right** and both sources agree: 30 on a win, 15 on a loss [B1][C1].

**Ruling needed:** whether the fix is a config edit (`duel.win.gains`) or a page edit, and whether the cap figure
should be `+4` (the client's number, sourced) or the current qualitative phrase.

**Not a Phase 0 edit.** It is recorded here because a URA walk will reach that screen and the copy will read
wrong against two sources, and the walk should cite this row rather than open a fresh finding.

### 3.4 Which trainee belongs on Trackblazer's variant point tracks

**The question.** Trackblazer publishes three yearly-target tracks: `standard` (60/300/300), `dirt_leaning`
(30/200/300) and `limited_turf_range` (60/200/300) [C6][B2]. The app prints **only `standard`**, and its config
comment says why: *"no column says which trainee belongs on the dirt-leaning or limited-turf-range one"* (KI-15).

**That premise is measurable, and it is testable now.** `umamusume` carries `aptitude_turf` and `aptitude_dirt`
columns, and the semantic is exactly the one the two variant tracks are named for:

| Track | The condition the sources describe | The columns that hold it |
|---|---|---|
| `dirt_leaning` | High dirt, low turf (Haru Urara is the named example) | `aptitude_dirt` high **and** `aptitude_turf` low |
| `limited_turf_range` | Turf, but poor outside short distances (Curren Chan is the named example) | `aptitude_turf` high **and** `aptitude_sprint` the only strong distance |

**Measured 2026-11-12, and the split is clean:**

```text
Haru Urara    turf G  dirt A  sprint A  mile B  medium G  long G   → dirt_leaning
Curren Chan   turf A  dirt F  sprint A  mile D  medium G  long G   → limited_turf_range
```

Both named examples land unambiguously on their own track under the rule the sources describe, using only
columns the catalogue already carries. Re-derive:

```bash
python - <<'PY'
import sqlite3
c=sqlite3.connect('database/database.sqlite')
for r in c.execute("""select name,aptitude_turf,aptitude_dirt,aptitude_sprint,aptitude_mile,aptitude_medium,aptitude_long
  from umamusume where name in ('Haru Urara','Curren Chan')"""):
    print(r)
PY
```

**Ruling needed:** whether the app may derive the track from aptitudes, or whether that is a prediction the
product's non-goals forbid. It is not a prediction — it selects which published requirement applies — but the
standing rule that the tool records rather than infers makes it worth one explicit decision.

**Until 3.1–3.4 are ruled, they are recorded gaps, not defects.** A walk that reaches any of them should cite
this section and file nothing new.

---

## 4. What the three new test cases need that does not exist

Stated so the audit's Phase 1 does not discover it as a surprise.

| Need | Scenario | Exists? | Cheapest honest substitute |
|---|---|---|---|
| A Performance balance to record | Grand Concert | No column, no payload | Record the **delta**, which the app does support, and print no total |
| A song count to record | Grand Concert | No | Nothing. Record the absence as a passing state |
| A lesson purchase to record | Grand Concert | No payload shape | Nothing |
| A concert result to record | Grand Concert | No | Nothing |
| A coin balance to record | Trackblazer | No column | Record spend only, which is what `shopSpendTotal()` does |
| A GP-period selector on the run | Trackblazer | **Yes** — `training_runs.current_objective_index` | Use it; it is the one scenario-state column that exists |
| A duel outcome to record | URA Finale | No | The cockpit prints the rules as an unrecorded reading, which is the correct shape |
| A bond reading | URA Finale, Trackblazer | No | Prints both published figures and compares neither |

**The pattern these share:** every one is a **balance**, and every balance needs a starting value no source
publishes. That is one architectural condition with eight instances, not eight independent holes, and it is worth
naming as such in the audit's cross-cutting chapter rather than eight times in the per-screen chapters.

---

## 5. Sources that were looked for and do not exist

Recorded so a later pass does not repeat the search.

| Sought | Result |
|---|---|
| A Global client frame of the **Hype ladder** at each state | Not found. One state (`Mild Hype`) was captured. `Special Hype!` is described in text only |
| A Global client frame of a **scheduled/reserved lesson** | Not found |
| A Global frame of the **Pro Shop** screen | Found for Trackblazer? No — the frames located are Grand Concert's |
| A Global frame of a **Happy Meek duel prompt** | Not found in English. The Japanese frames show the three challenges with `〇 △ ✕` odds |
| The Global **Help** text for Trackblazer | Not found as a page. The launch page states the systems and the wiki states the tables |
| Official notice 899 and 905 bodies | The notice API returns `response_code: 500` for `pr_info_detail` on both ids; the index route serves the same rows for newer notices but neither 899 nor 905 is in the current index window |
| A second published source for the **Trackblazer variant point tracks** | Not found. uma.guide is the only publisher of the 30/200/300 and 60/200/300 rows |
| A published **cost curve** for Grand Concert lessons | Not found. Only the total of all 21 lesson songs is published |

---

## 6. Summary in one table

| Scenario | Verified mechanics rows | Open gaps | Conflicts with the repo | Rulings owed |
|---|---|---|---|---|
| URA Finale | 23 | 6 | 0 | 0 |
| Unity Cup | — (not researched this pass; a seeded reference run exists) | 3 | 0 | 0 |
| Trackblazer | 24 | 6 | 3 (points vocabulary, bond figures, variant tracks) | 2 (§3.1, §3.4) |
| Our Grand Concert | 24 | 4 after closures | 2 (bonus name, finale label) | 1 (§3.2) |
| **Cross-scenario** | — | 8 (the balance family, §4) | 1 (duel cap copy, §3.3) | 1 (§3.3) |

Re-derive the two problem counts with:

```bash
# the conflict table in §2 currently holds 9 rows
grep -cE '^\| [0-9]+ \|' docs/audits/trainer-desk-v3-ux/scenario-research/scenario-information-gaps.md
# the rulings in §3 currently number 4
grep -cE '^### 3\.[0-9]' docs/audits/trainer-desk-v3-ux/scenario-research/scenario-information-gaps.md
```
