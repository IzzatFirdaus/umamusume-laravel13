# Skill system gaps

`verified-against 3711894 (master)` · written 2026-09-29 during the skills audit pass, in response to
a brief that described three acquisition paths and a six-row hint-discount table. Every anchor below
was opened and read on that commit; the corrections an incoming draft of this file needed are in §9.

**Scope:** Global only. Nothing here may import `[JP]`-only mechanics, evolved-skill data, or JP wiki
skill tables. Where a source is JP-side it is marked and its Global status is stated.

**Do not re-derive numbers from this file.** The acquisition paths are the authority; every figure is
only as good as the anchor beside it, and §2.2/§5 record the figures this repo has already declined.

**Concurrent work on this tree (do not plan over it):** a parallel session is landing the Legacy Select
read-back right now — `app/Models/Legacy/LegacySelectionPayload.php` and
`database/migrations/2026_09_29_012615_add_legacy_selection_to_training_runs.php` are untracked, the
migration's own docblock cites D-268 and an ADR-0010 that is not yet in `docs/adr/`, and
`app/Models/TrainingRun.php` is dirty. That payload stores per-Legacy Spark kind, target and star rank
plus an affinity grade. G-SK-5 is scoped against it below, and `docs/scenarios/01`, `02`, `07` are dirty
with the same session. `docs/scenarios/03-trackblazer.md` is superseded and untouched, per its own rule
(`SOURCE-OF-TRUTH.md` §4.1).

---

## 1. Acquisition paths — eight, not three

The brief named inheritance, personal defaults and star-rank unlock. Confirmed Global paths:

| # | Path | Anchor | Status |
|---|---|---|---|
| 1 | Innate / default skills, raised by Potential Level | `UMAMUSUME_REFERENCE.md:386` names **Potential Levels** (`覚醒Lv`) as a real pre-run system, and `:395` records that which UI owns the export's `stat_bonus` row is `❌ UNVERIFIED`; `[S]` Umamusume Global news 1064 (2026-09-23) "Potential Levels and Books of Hints"; `:771` §1.6.10 | Name and existence confirmed. The "starts with 3 hints, 7 after awakening" counts are `❌ UNVERIFIED` — no anchor in the corpus carries a number |
| 2 | Inspiration / Legacy Sparks | `REFERENCE` §1.5.1–1.5.4 (`:586`–`:641`); `docs/scenarios/01-ura-finale.md:38` | Confirmed, multi-source. Three payout moments (career start, early April Classic, early April Senior); White Sparks pay a mid-run hint +1..+5 and never fire at career start; Green Sparks carry the trainee's own Unique Skill at 1–3 hint levels |
| 3 | Star-rank unlock | `REFERENCE:588` (unique-skill Spark guaranteed once the trainee stands at ★3+); §1.3.5 `:385` (★4/★5 breakthrough rows, +50 stat budget per rank past native rarity); §1.6.9 `:750`, `:762` and the terminology row `:1957` | Confirmed for the ★3 consequence. The currency route is Trainee Star Pieces (Transfer Requests, duplicates) and the Statue Exchange at 650 pieces to max a trainee — see §5.1: the phrasing "Goddess Statues raise rank" is a retired reading |
| 4 | Support card events | `REFERENCE` §1.4.4 `:465`–`:467`; §1.4.7/§1.4.8 `:550`–`:555` | Confirmed, export-backed. Per-card `hint_skills` / `hint_others`; Hint Levels (id 17) and Hint Frequency (id 18); one event pays `1 + card hint level` |
| 5 | Race results | `REFERENCE` §2.3 Trackblazer `:891-892`: beating a rival grants a hint tied to the race distance or the running style, **requiring C aptitude or better in that distance**; `03-trackblazer.md:41-43`, `04-trackblazer-umaguide.md:146`, `05-trackblazer-gametora.md:127` | Confirmed. The draw is distance/style-keyed, not "mainly passive-type" as the brief put it. Cite by line: Section 2 numbers two different headings `2.3` |
| 6 | Training-goal and epithet achievement | `04-trackblazer-umaguide.md:84-127` (route wins pay `hint +1`, e.g. Mile Straightaways, Homestretch Haste, Top Pick); `:246` Climax → Radiant Star; `05:148-155` finale-skill hint plus unique-skill level-up events; URA's Unique Skill fan gates are in `design-research/CONSTRAINTS.md` D-224 | Confirmed for Trackblazer and URA. The route→skill mappings exist in the guides and are not consolidated anywhere structured |
| 7 | Scenario-exclusive skills | §4 below | Confirmed |
| 8 | Negative skills from losses | Not in this repository. Read this pass from [umamusu.wiki Game:Skills](https://umamusu.wiki/Game:Skills) (rev. 2026-08-30, tier `[A]`) — obtained by placing 6th or lower in races or from other specific events, and removed by spending SP — and from [Game8's Global all-skills list](https://game8.co/games/Umamusume-Pretty-Derby/archives/535927) (read 2026-09-29, tier `[A]`) | Existence: two independent `[A]` domains, **single readable pass, no `[S]`/client capture**. Everything past existence is `❌ UNVERIFIED` here — see G-SK-9 |

Paths 1–7 have anchors; the work on them is consolidation. Path 8 is the only genuinely new material
this pass produced, and it is new only in the sense that the corpus has never recorded it.

---

## 2. Hint levels

### 2.1 Confirmed

- A per-skill hint level exists, it lowers the SP cost of that skill, and it is raised by four routes:
  the trainee's own starting hints (path 1), White Spark inheritance +1..+5 (path 2), support-card hint
  events paying `1 + card hint level` (path 4), and the Books of Hints item route (`REFERENCE` §1.6.10
  `:771`).
- The item route is documented at **Lv3, 必要スキルPt 30% 減少**, with 12 / 6 / 30 of 「ヒント本 /
  ヒント専門書 / 夢の煌めき」 to take an unenhanced card there (`REFERENCE:467`).
- Hint levels demonstrably go past Lv3: a card at hint level 4 pays five levels from one event, and
  Unity Cup burst hints start at Lv2 (`02-unity-cup.md:95`, `06-unity-cup-gametora.md:156`).
- A hint for an already-learned skill is spent, not banked: Unity Cup substitutes a different random
  Ignited hint when the skill is maxed or owned (`02:109`, `06:186`).

### 2.2 What is not confirmed

**The per-level discount curve is unsettled and this repo has already ruled on it.** Conflict Log
**row 16** (`REFERENCE:2002`) is the authority, verbatim in disposition: keep **10%/level and −30% at
Lv3** as the published value because it is quoted from client-adjacent `[JP]` text and matches the Lv3
ceiling; treat the umamusu.wiki **~8% per hint, max 40%** — plus its separate **Fast Learner −10%** —
as *possibly folding that effect in*; and **do not encode either as a multiplier without an in-client
check**. `REFERENCE` §1.1.4 `:144` is stricter still: "Exact per-level discount percentages: ❌
UNVERIFIED: No current source found", and §8.4 `:2093` keeps the same gap open.

Working position for this repo, following the stricter record: **a hint level may be displayed as a
level; a discount percentage may not be computed or rendered** until an in-client read settles it. The
`NN% OFF` caption shape is already pinned in `SOURCE-OF-TRUTH.md:93` for the day it becomes honest.

### 2.3 The brief's table is rejected as a unit

`Lv0–Lv5 = 0/10/20/30/35/40%` restates a curve row 16 declined to settle and appends Lv4/Lv5 values no
source in two domains carries. It is recorded here so a future pass does not re-import it from the same
brief. The 40% figure is the wiki's *ceiling*, not a fifth level's value, and row 16 already notes the
two shapes cannot both describe one quantity.

---

## 3. Skill points

Confirmed (`REFERENCE` §1.1.4 `:144`): Wit pays +4 to +5 per session against +2 for the four
energy-spending disciplines; races pay more the harder they were; training events pay; support effects
raise either the post-race package (Race Bonus, id 15) or the discount on a hinted skill. Trackblazer's
shop economy records 30 SP on a loss and coins scaling with placement (`:837`, `:889-890`).

**Not confirmed:** the brief's per-grade magnitudes (G1 +45, G2/G3 +35, OP +30). Direction is sourced,
the numbers are not, and the repo's grade-code mapping is itself only partly settled (conflict row 12:
code 700 remains `❌ UNVERIFIED`). See G-SK-12.

---

## 4. Scenario-exclusive skills

### 4.1 Unity Cup

Authority: `docs/scenarios/02-unity-cup.md:199-241` and `06-unity-cup-gametora.md:151-188`, `:223-235`,
`:257-258`, `:300-320`. `REFERENCE` §2.2 `:849`–`:862` is the summary, and §2.2's own rework note `:1005`
dates the Global rework to **2026-07-01**.

- **Burst hint ladder** (`02:205-208`, `06:232-235`): 4-6 → white hint Lv1 +10 stat +10 SP · 7-9 → white
  Lv3 +20/+20 · 10-12 → gold Lv1 +30/+30 · 13+ → gold Lv3 +40/+40. `SCENARIO-DIFFERENCES.md:150` records
  the correction that matters: the denominator is now **bursts plus Extreme bursts combined**, and that
  same row flags the older "13+ → Burning Spirit X Lv.3 + 20 SP" reading as superseded.
- **"Ignited Spirit"** facility-specific hints (`02:109`, `06:185-186`) start at Lv1 and scale with the
  card's Hint Level bonus. **"Burning"** as a rarity label appears only inside the superseded row above
  and in the incoming write-ups — no active scenario file names it. Treat the pair as `❌ UNVERIFIED`
  client copy before putting either word in a screen (D-20).
- **"It's On!"** (`02:224-229`, `06:257-258`): Team Rank S grants the hint at Lv1, **Lv3 for a scenario
  *story* character** (the guide's example is Haru Urara) — not for any scenario-*linked* card; the
  linked list is a different thing (`REFERENCE:530`: Taiki Shuttle, Rice Shower, Haru Urara,
  Matikanefukukitaru, Riko Kashimoto). S+ grants a second hint, which is how the level is maxed, and the
  ladder's own UI consequence is already built into `x-team-rank-gauge` (`:42`, `:63`).
- **Team Zenith victory skills** — the brief names `Cooldown` (Rice Shower) and `Indomitable` (Haru
  Urara) as post-finals rewards. **No file in this repository contains "Team Zenith", "Cooldown" or
  "Indomitable"** (grep across `docs/` and root returns only the incoming UX write-up's own line 1594).
  Recorded as an incoming claim, not a repo fact. Do not encode the names or the effect class until a
  scenario file carries them.

### 4.2 Trackblazer

`04-trackblazer-umaguide.md:246` (Climax → Radiant Star), `05-trackblazer-gametora.md:148-155`
(finale-skill hint; unique-skill level-up events pay a hint when the trainee is selected), `05:188-190`
(the scenario Spark). `04:1170` in the incoming flows doc concedes the Radiant Star mechanics were never
extracted, so the name is all this repo has.

### 4.3 Other scenarios

`07-grand-concert.md` is a Known-Gap Stub and `08-grand-masters-jp-only.md` is `[JP-Only]`; per
`SOURCE-OF-TRUTH.md` §4.1 the latter must not be imported into app data, config, or UI copy. Neither may
be used to fill a skill surface.

---

## 5. What the brief got wrong

| Brief claim | Disposition |
|---|---|
| Three acquisition paths | Eight (§1) |
| Hint table 10/20/30/35/40% | Rejected as a unit; conflict row 16 already declined the curve, and Lv4/Lv5 carry no source in any domain |
| "A blue mark on the icon means the skill cannot be inherited" | `❌ UNVERIFIED`. No repo doc and neither wiki read this pass states it. Do not encode a flag for it (G-SK-5) |
| Skill Points G1 +45 / G2+G3 +35 / OP +30 | Direction confirmed, magnitudes `❌ UNVERIFIED` (§3) |
| "3 hints → 7 via Potential Level" | Potential Levels confirmed by name; the counts are `❌ UNVERIFIED` (§1 path 1) |
| "Scenario-linked trainee such as Taiki Shuttle gets +3 hint levels on It's On!" | The Lv3 case is a scenario **story** character, not any linked card (§4.1) |
| "Evolution skills — confirm Global status; the Global site announced additional ones" | Already settled at conflict **row 45**: two independent negative readings, no `[S]` notice, the wiki itself says the system is JP-exclusive. Treat as `[JP-Only]` (G-SK-10) |

### 5.1 Claims in this brief that the conflict log already retired

Recorded because a re-import of a retired claim is the failure this repo has been catching all slice:

- **"Goddess Statues" as permanent scenario buffs / as what raises star rank** — retired by conflict
  **row 43**, with the deliberate instruction not to re-type the retired literal in tracked text. The
  corpus position (`REFERENCE:762`, `:1957`) is that 「女神像」/Goddess Statue is **currency only**,
  exchanged for Trainee Star Pieces in the Statue Exchange on an escalating ×1→×5 rate, and grants no
  buff. §1 path 3 is written from that reading. Row 43 also retired a guaranteed-training-success
  consumable in the same family; the only documented zero-failure levers are Trackblazer's Good-Luck
  Charm, an Extreme Spirit Burst on its own facility, and Failure Protection (id 27).
- **"Skill Set" bulk planning** — `[JP]`-only, excluded by `MECHANICS-TRANSLATION-TRIAGE.md` §3.
- **The 1200-Power evolution gate** — triage §7.2 `:182` shows it is Grand Live's *Fully Charged* gate
  misfiled as an evolution condition, alongside a 340 SP figure that belongs to Swinging Maestro.
- **Mood pre-race percentages** — the incoming docs' +10/+5 are contradicted by the client's own Mood
  Effect panel (+4/+2/0/−2/−4), triage §7.2 `:172`.

Triage §4 `:88` already ruled the ~10% hint discount unsourced. None of these four is reopened here.

---

## 6. Gap entries

### G-SK-1 — Skill master columns are PRD-shaped; the *data* is absent

- **Measured:** `skills` = `id, name, name_ja, match_key, sp_cost, type, is_unique, timestamps`, 10 rows,
  `sp_cost` and `type` NULL across all ten, `is_unique` false across all ten, `run_skills` empty.
- **Correction to the incoming draft of this file:** those six columns are exactly `PRD.md:63` FR-D-1
  ("English name, Japanese name (nullable until cross-referenced), match key, SP cost (nullable), type
  string (nullable), `is_unique` flag"). Nothing is missing from the shape. **`rarity` is not a PRD
  field**, so adding it is a scope change for the owner (AGENTS.md escalation 2), not a gap to fill.
- **What is missing:** the values. Cost, type and uniqueness arrive with the import (G-SK-2), and
  rarity — if authorized — arrives with the same export field.
- **Read this with G-SK-18** if the derived `type` is ever refactored: the sign of an effect value is
  part of the rule, not a detail, and a code-only reading prints `Passive` beside an averseness.
- **Owner:** Architect (any column beyond FR-D-1), Data Engineer (values).

### G-SK-2 — No skills import

- **Measured:** `app/Services/DataPipeline/Parsers/` holds `GametoraCharacterParser`,
  `GametoraScenarioParser`, `GametoraRaceCatalogParser`; `config/uma.php:44-93` configures exactly **two**
  sources, `gametora-characters` and `gametora-race-catalog`. So a scenario parser exists without a
  configured scenario source, and skills have neither.
- **Why not built:** outside this pass, and every cost/type/rarity UI item downstream is blocked on it.
- **What is needed:** a `GametoraSkillsParser` behind its own `SkillSourceParser` contract — master
  already carries `Contracts/SourceParser.php`, `ScenarioSourceParser.php` and
  `RaceCatalogSourceParser.php`, and the card branch adds `CharacterCardSourceParser.php`, so one
  contract per source is the existing pattern, not a new one — a `config('uma.sources')` entry for
  `https://gametora.com/data/umamusume/skills.f4a1e02d.json` — a **different document** from the two
  approved ones, so the owner-gate argument written at `config/uma.php:69-77` has to be re-made rather
  than inherited — plus the promote path, since a live fetch cannot create catalog rows
  (`CrossReferenceMatcher` returns `None`, `PRD.md` FR-B-3). Cross-check rarities against Game8.
  **The export's skill row count is not recorded in this repository**; `PRD.md:73` NFR-3 budgets roughly
  2,000 for the performance gate, which is a budget, not a count.
- **Owner:** Data Engineer, with Architect sign-off and the owner's fetch approval on record.

### G-SK-3 — No hint-level tracking

- **Measured:** `run_skills` carries `status` and `turn_acquired` only. No `hint_level`, no `skill_hints`
  table, in any of the 29 migrations on this tree.
- **Why not built:** the payout consequence is unsettled (G-SK-4), and a stored level whose discount
  cannot be computed would invite exactly the multiplier row 16 forbids.
- **What is needed:** `run_skills.hint_level` plus a stated ceiling once one is sourced; per-hint
  provenance wants its own row, not a column, if more than the latest level matters.
- **Owner:** Architect, gated on G-SK-4.

### G-SK-4 — Hint-level → cost curve

- **What is missing:** the curve. Conflict row 16 holds the published value and forbids encoding it
  without an in-client check; §1.1.4 and §8.4 keep the gap open.
- **What is needed:** a client read of the skill-cost calculator against a known base cost, or a fresh
  `[S]`/`[A]` source dated against the current Global build. Until then: level yes, percentage no.
- **Owner:** Design system with the Data Engineer; the copy ruling in §7 is the owner's.

### G-SK-5 — Inheritance: provenance is landing, the skill join is not

- **In flight (uncommitted):** `training_runs.legacy_selection` (typed JSON, nullable) records per
  Legacy the chosen Umamusume, rank, Guest flag, its own two ancestors, a Spark list with **kind, target
  and star rank**, and an affinity grade. That is the half the incoming draft called missing.
- **Still missing:** no join from a Green/White Spark **target** to a row in `skills`, so a Spark carrying
  a Unique Skill names a target string rather than a catalog skill; no inheritable/not-inheritable flag
  (and the blue-mark rule that would justify one is `❌ UNVERIFIED`, §5); no source attribution for which
  Spark produced a run's hint.
- **Why not built here:** the payload is another session's surface, and the flag's only proposed evidence
  has no source.
- **What is needed:** coordinate with the Legacy owner on whether Spark target strings resolve through
  `match_key` at read time or through a stored `skill_id` at write time; then a second-domain source for
  the inheritable rule.
- **Owner:** Planner Domain Specialist with the Architect, after the Legacy work lands.

### G-SK-6 — Star rank and the card layer are on a branch, not on master

- **Correction to the incoming draft:** the card layer is **built**, just not here. Branch
  `feat/catalog-roster-and-trainee-selector` (worktree `D:/Projects/umamusume-laravel13-catalog-roster`,
  tip `cf5fc75`) carries ADR-0008, `character_cards`, `training_runs.character_card_id` and a
  `GametoraCharacterCardParser` (commits `c02600e`, `98d9a03`, `cd26d8d`, `db8603c`). Master has none of
  it, and `docs/adr/` on master stops at 0007 plus 0009 — **there is no ADR-0008 file on this ref**.
- **Measured on master:** `umamusume` = `id, slug, name, name_ja, match_key, release_status,
  jp_debut_date, global_debut_date, is_manual, timestamps` plus the aptitude columns from
  `2026_09_27_090000`. No star rank, no Potential Level, no card rows.
- **Consequence:** nothing on master can show what ★3 unlocks for a named trainee, because master cannot
  state any trainee's star rank at all.
- **What is needed:** a merge decision on the card branch before any star-rank UI is planned; then the
  rank lives on that layer, not on `umamusume`.
- **Owner:** Architect with the card-branch session.

### G-SK-7 — Support card hint pools: **closed, not open**

- **Correction to the incoming draft:** `ADR-0005` is not "Proposed". Status line 3: **DECLINED for
  Phase 1 (owner ruling R37, 2026-09-28)**, "the question is closed rather than parked", `PRD.md` §6 item
  9 stands, the entity shapes are retained as later-phase reference only, and **"no slice may cite this
  ADR as permission."**
- **Reopen trigger, as written:** a new PRD story by the owner authorizing support-card entities and
  saying which domain it wants (reference data, or the Trainer's collection and deck).
- **Consequence for the brief's Step 4:** the requested "support card hint pool" panel is not a gap to
  fill; it is a cut system, and the corpus can describe `hint_skills` per card (`REFERENCE:465`) without
  the app storing a card.
- **Owner:** none until the owner writes the story.

### G-SK-8 — Race → hint mapping

- **What is missing:** no table or config maps a race to the hint it can pay. The rule is documented
  (distance or style, C aptitude or better, `REFERENCE:891-892`) and the route rewards are enumerated in
  prose (`04:84-127`), including named ones like `Mile Straightaways`, `Homestretch Haste` and `Top Pick`
  — two of which are already in `SkillSeeder`'s ten names.
- **Why not built:** consolidation, not research, and it only earns its keep once a race entry is worth
  attributing a hint to.
- **What is needed:** a scenario-scoped mapping with provenance (`race_catalog_slots` is the grain),
  read from the existing guides rather than invented per row.
- **Owner:** Planner Domain Specialist.

### G-SK-9 — Negative skills

- **What is missing:** everything. No column, no corpus section, no scenario doc, no UI.
- **Confirmed this pass:** existence, from placing 6th or lower or from specific events; removable by
  spending SP. Two `[A]` domains agree.
- **`❌ UNVERIFIED`, do not encode:** the full list, per-skill removal cost, the claimed rule that holding
  a negative skill locks its positive counterpart (incoming, single-source, untested here), and the label
  itself — neither source names a client English string for the category, and "purple" is a colour read,
  not a captured label. `DESIGN.md` §3 reserves hues as semantic; a fifth skill colour is a token decision
  with a contrast pass behind it (`DesignTokensTest` pins the token count).
- **What is needed:** a client capture of the negative-skill row plus one `[A]` list page, then a column
  on the master (`is_negative`) and a removal cost, before any UI.
- **Owner:** Data Engineer with the Design system.

### G-SK-10 — Evolved skills

- **Status:** settled at conflict **row 45** — the wiki's own text says the system is currently exclusive
  to the Japanese version, Game8's Global skill pages carry no Evolved category, and no `[S]` notice
  exists. `REFERENCE:353` and `:706` name evolved skills inside `[JP]` gate text, so shared gate copy is
  **not** evidence of a Global system; row 45 says so explicitly.
- **What is needed:** a Global notice, or the gap stays closed. If it ever opens, it is its own slice.
- **Owner:** owner on the Global release question; Data Engineer to re-check.

### G-SK-11 — Scenario-exclusive skills have data and no surface

- **Measured:** `x-spirit-burst-roster` renders six teammate burst **states** from `turn_events` payloads
  and `x-team-rank-gauge` renders the ladder with the S+ second-hint note. Neither names a skill, and no
  panel reads the burst reward ladder at a given team rank.
- **Why not built:** a UI decision, not a data import — the values already exist in `02`/`06` and the
  composition matrix gates the panels.
- **What is needed:** one row per the §4.1 ladder keyed to the reported team rank, an "It's On!" row for
  S/S+, and the Trackblazer Climax name from `04:246`. Do **not** render the Zenith names (G §4.1).
  Labels verbatim from a scenario file only, per D-20.
- **Owner:** Frontend with the Planner Domain Specialist.

### G-SK-12 — Per-grade SP figures

- **What is needed:** a source for the magnitudes, or the run keeps SP as an entered number with no
  per-grade breakdown. Direction is confirmed; the grade codes themselves are only partly settled
  (conflict row 12).
- **Owner:** Data Engineer.

---

## 7. What is genuinely open

Reduced from two questions to the decisions that are actually unmade:

1. **Does this pass land the skills import (G-SK-2)?** Every cost, type and rarity display in a Step-4
   pass is blocked behind it, and it needs its own owner fetch approval because it is a new document.
2. **Is a `rarity` column authorized?** `skills` already matches FR-D-1 exactly, so this is a PRD change,
   not a schema tidy-up.
3. **Hint-level copy (§2.2 and G-SK-4):** ship level-without-percentage (the safe reading of row 16),
   or ship 30%-at-Lv3 as a single-source caption carrying an explicit `[Unverified]` marker. A copy
   ruling, and the owner's.

Support-card entities (G-SK-7) and evolved skills (G-SK-10) are **not** open — both are ruled. No Step-4
code work on skills begins until (1) is answered.

**All three were answered the same day, and §8 records what landed:** import and UI in this pass;
`rarity` authorized as a PRD amendment and stored as the source's code with no label; level with no
percentage anywhere.

**Still open after Screen D landed the same day** — these are the decisions nobody has made, not work
somebody skipped:

1. **Hint level** (G-SK-3, G-SK-4): a `run_skills.hint_level` column and conflict row 16's curve. Screen D
   renders no hint anything, by ruling and by scope.
2. **The fixture path exclusion** (G-SK-17): gate tooling, owner's pen, two facts recorded there.
3. **Icon and description** (G-SK-19, G-SK-20): both are §6.11 elements with no source this repository
   holds. A description column specifically needs a client-string source first, not a re-read of `desc_en`.
4. **A Japanese search key** (G-SK-22): a new column, so a PRD amendment first.
5. **FR-D-2's second word.** The PRD asks for "skill search/autocomplete **for the run UI**" while §8.4 makes
   Screen D a surface of its own. The surface landed, and the run screen links to it, so a Trainer can narrow
   623 rows before choosing. Whether the run screen *also* gets in-place autocomplete is unsettled; a native
   `<datalist>` filled server-side would do it with no JS dependency, which is the one constraint here that
   no option may break.
6. **The D-63 and D-65 amendment asks** (G-SK-23), including whether `skills` should have aliases at all.

---

## 8. Landed 2026-09-29 (same pass, after the owner's three rulings)

The owner chose the widest option on scope ("import + UI in this pass"), ruled `rarity` in as a PRD
amendment, ruled hint copy to **level only, no percentage**, chose to store every row with
`release_status` + `name_is_client`, and chose correction over substitution for the seeder names.

**Shipped:** `config/uma.php` gains `gametora-skills` (manifest-resolved, pinned URL kept as the
documented fallback); `Contracts/SkillSourceParser`; `Parsers/GametoraSkillsParser`;
`Actions/StoreSkills`; a `PipelineRunner` branch so skills never reach the review queue; the
`skills` reference-columns migration; `Skill::availableOnGlobal()`; the seeder name correction with the
D-210 addendum; and the run screen's filter plus `✦ Unique` / SP cost marks and the hint-level absence
said out loud. `ADR-0011` carries the measurements; `KI-23`/`KI-24` carry the two defects found on the way.

**Everything after `acab4d8` on this branch is the Screen D pass**, and its own commits are three: the
rendered-output absence check that G-SK-17 leans on (`83086b0`, `SkillsFetchTest::skillsFixtureGlobalRenderings()`)
and the two register corrections (`c3bdda3`, `acab4d8`) already sit before that mark, so `git log acab4d8..HEAD`
is the range rather than a list that goes stale. What the range carries: the fixture's three added rows, the
`GET /skills` surface, G-SK-19 to G-SK-23, and **KI-26**. Listing the SHAs here instead of a range would mean
a file that cites commits it cannot contain.

**Measured on the first live import** (`uma:fetch gametora-skills`): 1,901 created, 9 adopted, 0 to
review, 1,910 stored — **623** rows are `[Global]` and client-named, and those 623 are exactly what
`availableOnGlobal()` returns. `is_unique` 294, cost present on 906, derived type Speed 913 / Passive 120 /
Recovery 82 / null 795. A second run reports "unchanged since last snapshot", so the snapshot-hash
idempotence covers this source too.

**Gaps this created, which are not the same as the ones above:**

- **G-SK-13 — the run screen's picker now lists 623 options.** That is the honest consequence of importing
  real data: the select was designed when the table held ten names. `FR-D-2` and `DESIGN.md` §8.4 (Screen D)
  are the designed answer and remain unbuilt; a `select` of this size is the reason they exist.
- **G-SK-14 — `rarity` and `type` are stored and not rendered.** `rarity` has no label to render (six codes,
  three client rarities). `type` has a derived word but it is this tool's reading of effect codes, and
  `Debuff` is unevidenced, so the panel footnotes the derivation instead of printing a taxonomy.
- **G-SK-15 — `/api/v1` and the CSV/JSON export are unchanged.** `TrainingRunResource` still emits
  `id / name / status / turnAcquired`. Widening a published shape is its own decision, not a side effect
  of the import.
- **G-SK-16 — neither English description field in the skills document is client text, and the gate proves
  it twice over.** `SOURCE-OF-TRUTH.md` §3 pins the stat as Stamina, the second stat as Wit and the gauge
  word as Mood. Run the gate's own code-mode patterns over the document (measured 2026-09-29 while auditing
  Screen D, because `DESIGN.md` §6.11 puts a description in the skill row):
  `desc_en` matches **189** times, 172 of them `endurance`; `endesc` matches **72** times, 52 `wisdom` and
  16 `motivation`. Two fields, two independent vocabulary tells, and both are the §3 banned-synonym set —
  so neither is the client's prose, whichever one reads more like game copy. `desc_en` sits on 985 rows
  (exactly the `name_en` population, and all 623 `[Global]` rows); `endesc` sits on all 1,910. They differ
  on **every** row carrying both — 0 identical of 985.
  The consequence is a rule, not a preference: **descriptions are evidence and never copy.** The parser's
  category table reads them to decide what an effect code does (that is how code 9 is known to be a
  recovery) and quotes none of them into a label, and no description column is authorized by this register.
  By the same logic `name_en` is *not* suspect in the way the descriptions are: its hits are verbatim
  client skill names — `Paddock Fright`, `Wisdom of the Sun`, `Master of the Sands` — which C-4 gates on
  the display path rather than in the data, while `endurance` and `wisdom` in a description are the field
  itself using the wrong word for a stat.
- **G-SK-17 — the fixture was cut to the keys the parser reads, and one verbatim hit remains.** It was
  drafted as nine whole document records including their description keys, which put four banned-term hits
  into `tests/Fixtures/` — and that draft **never reached a commit**: the file has one commit, `aa5b05c`, at
  4,472 bytes. So the 22 KB to 4.5 KB cut and the `lore-code` 15-hits-down-to-8 are measurements of a
  working tree, not of shipped history, and no reader should go looking for the wide version in `git log`.
  They are also measurements the register cannot re-derive: the description strings are gone, so which four
  hits they were is not recoverable — what is recoverable is G-SK-16's counts over the live document (189 in
  `desc_en`, 72 in `endesc`), which explain why cutting those keys moved the number at all. Those fields are
  not read by anything, so they went: `id, name_en, enname, jpname, rarity, cost, unreleased,
  condition_groups` is the whole of it now, with the skills pair still passing (145 assertions as of
  `83086b0`). The survivor is `"enname": "Sand Expert"` —
  a value the parser genuinely reads (it is the name fallback), which `GametoraSkillsParserTest` pins out
  of parser output and `SkillsFetchTest` now pins out of the **rendered run screen**, so removing it would
  be editing the data to quiet a grep. The class is the one `R62` already ruled on for
  `database/seeders/data/**`, and code mode filters no marker, so any remedy is a **path** exclusion in
  `tools/lore.php` **and** the Makefile with a docblock naming the class — gate tooling, with
  `LoreGateParityTest` holding the two in step, and still the owner's call. Not touched here.
  **Two facts the owner needs before ruling, both measured after the review asked for them.** A path
  exclusion must name `tests/Fixtures/gametora-skills.sample.json` and nothing wider; `tests/**` would
  re-open the exact class `KI-23` came from, a filter that quietly stops seeing the thing it guards. And
  the provenance clause cannot sit in the fixture itself — JSON has no comment slot, which is why the
  capture note (source, manifest hash `609afe88`, pulled 2026-09-29, cut verbatim) reads in
  `GametoraSkillsParserTest.php`'s docblock instead. If the owner would rather have provenance and
  exclusion in one place, the alternative is a captured-source folder, e.g.
  `tests/Fixtures/gametora/`, holding the document beside a sidecar note, with that **directory** excluded
  in both gate surfaces. Either way the assertion that carries the weight is the one in `SkillsFetchTest`:
  eight derived renderings forbidden over decoded page text, with the row's own client string required
  present so the absence cannot pass on an extraction that saw nothing.
- **G-SK-18 — the category derivation reads the sign, not only the code. Do not "simplify" this away.**
  Effect code 1 is an axis, not a meaning: `Right-Handed ◎` reads `+600000` on it and `G1 Averseness`
  reads `−400000`. A derivation that switched on the code alone would print `Passive` beside a skill whose
  client text says *decrease performance in G1 races*, and the mistake is invisible in review because the
  code table still reads correctly for every positive row. `GametoraSkillsParser::category()` therefore
  returns null unless every effect is a positive integer on a mapped code, and
  `GametoraSkillsParserTest` pins the averseness row to null specifically. Measured consequence: 53 of
  the 623 `[Global]` rows lose a label to this rule. The same discipline is what found `KI-23` — a check
  that cannot fail is worse than no check, and here the check that would have failed was only missing a
  negative number in a fixture. **If a future slice refactors the derivation, keep the sign test or
  delete the derived `type` column; a taxonomy that quietly absorbs negatives is the failure this
  repository has been catching all session.**
- **G-SK-19 — §6.11's skill icon has no source in this repository, so Screen D renders none.** The document
  states `iconid` on **all 1,910 rows**, and nothing stores it: no column, no asset path, and no committed
  manifest that names where an icon is served from. Landing it is two decisions before it is any code — a
  PRD amendment for the column (FR-D-1's set is name, name_ja, match key, sp_cost, type, `is_unique`, plus
  `ADR-0011` §3's rarity and provenance and FR-D-3's availability; an icon is outside all of that), and an
  owner ruling on whether image assets are fetched at all, which `CONSTRAINTS.md` C-4 scope notes have kept
  out of Phase 1. Screen D's rows carry the name, the Japanese name, the cost, the derived type and the
  `is_unique` mark, which is the whole of what `D-30` permits and the whole of what the data holds.
- **G-SK-20 — §6.11's description has no client string behind it, and the vocabulary is the proof.** See
  G-SK-16 for the measurement: `desc_en` fails the terminology table 189 times (172 of them `endurance`) and
  `endesc` fails it 72 times (52 `wisdom`, 16 `motivation`), so neither field is `[Global]` prose, and the
  one that *looks* most like game copy is precisely the one that contradicts `SOURCE-OF-TRUTH.md` §3.
  **The rule this entry exists to keep:** no description column may be added until a client-string source for
  descriptions exists — an in-client capture, not a re-read of the export. A future pass should not reach for
  `desc_en` because it reads better than `endesc`; that is D-20's failure mode with a flattering example.
- **G-SK-21 — two `[Global]` skills share a client name, and the Unique badge falls on the one no card
  names.** Measured on the imported document: 623 rows, **621 distinct client names**. `Indomitable` is
  export **200471** (class code 2, learnable, 170 SP, derived `Recovery`) and export **300141** (class code
  5, no SP cost, derived `Speed`) — and 300141 is one of the four rows `ADR-0011` §5 names as beyond the
  card join. `Carnival Bonus` is the other collision (1000011, 1001012, both class code 1, neither priced).
  So a Trainer searching `Indomitable` gets two rows, and the code rule badges exactly one of them.
  **Ruled 2026-09-29 (owner, option b of three):** ship the code rule, because it is what a one-document
  parser can defend, and put the limit on the badge itself — `title="Marked from the source's skill class
  code, not from a card's own skill list."` No count in the copy: 290-of-294 is a fact about one snapshot
  and belongs here and in the ADR, not in UI text that will outlive it. The rejected alternatives were to
  badge nothing (silently dropping the client's own signal) or to read the card document on this screen
  (option c: a second source in the read path for four rows).
  Both rows render, and they are distinguishable without a rarity word because cost and derived type differ.
  `SkillSearchScreenTest` pins the collision through the producer path, with both rows in the fixture
  verbatim, so it cannot be quietly "fixed" by a future dedupe.
- **G-SK-22 — a Japanese query cannot reach a skill, so D-63's "which field matched" has one answer today.**
  `StoreSkills.php:68` writes `match_key` from `$record['name']`, which is the client English string, and
  `NameNormalizer` does not transliterate. Measured against the live document: of 200 sampled `[Global]`
  `jpname` values, **1** finds a row through `match_key LIKE` — and that one is export 200311-style noise, a
  row whose `jpname` is the Latin string `U=ma2`. `DESIGN.md` §8.4 and D-62 both say a Japanese query may
  return an English row; for skills it returns nothing. D-63's matched-field report therefore has exactly one
  field to report. Fixing it means a normalized key derived from `name_ja` (or a second searchable key
  column), which is a new column, which is a PRD amendment first. **What was not done instead:** matching on
  the `name_ja` display string directly, which the root Banned Patterns D-62 cites forbid, and which would
  have made the screen look right while breaking the rule that keeps case- and width-folded search honest.
- **G-SK-23 — D-65's review-queue offer does not map to skills, and Screen D says so rather than linking to
  an empty place.** D-65's no-results state offers the review-queue path (US-5) because a missing skill is a
  data gap, not a user error — that half is true and is kept. The queue half is not reachable: `StoreSkills`
  writes straight past `match_candidates` by design, and `SkillsFetchTest` asserts the queue holds zero
  skills after a full import. So the screen states the gap in the fetched data and names
  `php artisan uma:fetch gametora-skills` when the table is empty outright, and links to `/review` nowhere.
  **Two amendment asks, both the owner's pen, because `CONSTRAINTS.md` is not this agent's file to widen:**
  D-65's queue clause scoped to cross-referenced entities, and D-63's "skill name, Japanese name, or alias"
  reduced to what `skills` can back — the table has no alias column and no alias relation (contrast
  `umamusume`, whose `aliases` `CatalogController.php:47` searches), so "alias" is a field that does not
  exist on this entity. Either the clause gains a "where the backing exists" scope or skills gain aliases;
  neither is decided here.

**Landed with Screen D, 2026-09-29 (the same owner pass, decisions 1 to 5).** `GET /skills` is the second
server-driven filter surface and the answer to G-SK-13's 623-option select: the run screen now links to it
and the shell's nav carries it, so the long list has a way out. Facets are **search, type (with
`Unspecified` for the 288 rows the sign rule withheld) and unique**; `rarity` and cost-present are columns,
not controls, because a filter over a code no Trainer can read is D-64's ornament. The param is `search`,
matching `/umamusume`, and the query is escaped before the `LIKE` — the older surface is not, which is
**KI-26**, filed and left there deliberately.

**G-SK-1 through G-SK-2, read with the above:** G-SK-2 (no skills import) is closed by this pass.
G-SK-1 stays as written because it was the *correction* that mattered — the columns were already PRD-shaped,
and what arrived is data, plus one authorized column. G-SK-3, G-SK-4 (hint level and its curve) are
untouched by the import: the document states no per-run hint level, and row 16 still forbids the multiplier.
G-SK-9 gains a data path (`effect code 21`, 60 rows, all negative-valued) and still has no cost, no list,
and no client label. G-SK-10 is now machine-confirmed rather than wiki-only: **672 evolved rows, all of them
`unreleased` containing `en`, none on `[Global]`** — and all 270 disagreements against the card document's
`release_en` are exactly those rows. One count above needs its arithmetic stated beside it, not in a
footnote: **`is_unique` 294** is the code rule (rarity 3/4/5) while the card join reaches **290**, and
`ADR-0011` §5 names the four rows in between rather than leaving two counts to look like a typo.

## 9. Cross-references

- `docs/UMAMUSUME_REFERENCE.md` — the mechanics authority. Skill-adjacent: §1.1.4 `:142`, §1.2 gating
  `:208`–`:312`, §1.3.5 `:378`, §1.4.4 `:463`, §1.4.7 `:550`, §1.5.1–1.5.4 `:582`, §1.6.9–1.6.10 `:748`,
  §2.2 `:849`, §2.3 `:863`, Conflict Log rows 12, 16, 43, 44, 45, 46, §8.4 `:2083`.
- `docs/design-research/MECHANICS-TRANSLATION-TRIAGE.md` — rulings this file must not reopen: §1
  terminology, §3 out-of-scope, §4 `:88`, §7.2 `:172`/`:182`.
- `docs/design-research/CONSTRAINTS.md` — D-20 (no unverified strings in UI copy), D-30 (render only what
  exists), D-44 (planned vs actual in one view), D-62..D-65 (skill search), D-192, D-210 (the ten names),
  D-223, D-224, D-229, D-253, G-16, G-49.
- `docs/design-research/DESIGN.md` §6.11 (skill row), §8.4 (Screen D), §3 (sparkle = `is_unique` only).
- `docs/design-research/RACE-CALENDAR-GAPS.md` — the sibling register this file follows.
- `docs/SOURCE-OF-TRUTH.md` §3 (Spark / Inspiration / Legacies; `Hint Lvl N, NN% OFF`), §4.1 (supersedence
  and the JP-only import filter), §5 (tier ladder: a `[B]` dataset needs `[A]`/`[S]` confirmation).
- `PRD.md` §4 C-3 `:58`, FR-D `:62-64`, NFR-3 `:73`, §6 item 9 `:91`.
- `docs/adr/0005` (support cards declined, R37), `0009` (committed client export precedent, Option A),
  and the unmerged ADR-0008 on the card branch.
- `KNOWN-ISSUES.md` — KI-5 (a fabricated skill name, fixed) stands; no KI is filed here. These are gaps,
  not defects.

---

## 10. Corrections applied to the incoming draft of this file

The draft arrived with anchors asserted rather than opened. Each of these was measured before the edit:

| Draft said | Ground truth |
|---|---|
| Team Zenith victory skills `Cooldown` / `Indomitable`, anchored to `06` | Neither name, nor "Team Zenith", appears in any repo file; kept as an incoming claim (§4.1) |
| "Balance Adjustments (Nov. 11, 2025)" at `06:300-320` | That range is the pre/post Spirit Burst rework diff; the Global rework dates from **2026-07-01** (`REFERENCE:1005`), and no file carries a "Balance Adjustments" list |
| `character_cards` "authorized but not built (no migration)" | Built on `feat/catalog-roster-and-trainee-selector` with ADR-0008; absent from master, where `docs/adr/` has no 0008 |
| ADR-0005 "Proposed, not Accepted", question open | DECLINED for Phase 1 (R37, 2026-09-28); question closed; reopen needs an owner-written PRD story; no slice may cite it as permission |
| "the export's ~1,910 skills" | No row count exists in the repo; `PRD.md:73`'s ~2,000 is a performance budget |
| Rarity anchored to §1.1.4 | That section is SP acquisition; the three-rarity reading comes from Game8's Global list, and FR-D-1 has no rarity field |
| 30%-at-Lv3 is "the only one to encode" | Row 16 forbids encoding **either** figure as a multiplier without an in-client check |
| "§2.2 (URA fan gates)" | URA is §2.1; §2.2 is Unity Cup, and Section 2 numbers two headings 2.1/2.2/2.3 twice — cite by line |
| Inheritance hint resolution wholly missing | `training_runs.legacy_selection` is landing now with Spark kind/target/star rank and affinity; only the skill join and the inheritable rule remain (G-SK-5) |
| Negative-skill base-lock rule stated as a "confirmed rule" | Single-source incoming claim; marked `❌ UNVERIFIED` (G-SK-9) |
| Hint levels "raised by three sources", four listed | Rewritten as four |
| "Star Pieces from duplicates or Goddess Statues raise star rank" | Carried the buff framing conflict row 43 retired; rewritten from `:762`/`:1957` as currency only (§1 path 3, §5.1) |
