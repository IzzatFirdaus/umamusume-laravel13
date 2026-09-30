# Slice 5 Phase A — does a sourced `best_for` for skills exist? **No. Verdict (c): do not ship.**

Date: 2026-10-01. Read-only research. No migration, no column, no backfill, no UI, no scratch database.
Phase B is **not** authorised by this document.

## Verdict

**(c) Do not ship.** Two independent grounds, either of which is sufficient:

1. **No source carries the field.** Not GameTora's export, not Game8, not the in-repo scenario docs.
2. **No requirement cites it.** `PRD.md` contains zero occurrences of `best_for`, `best for` or `recommend`.
   Under `AGENTS.md` ("Every new table, column, or class must cite a PRD requirement. No citation, no
   merge."), the column cannot merge regardless of whether a source appeared. This is the same ground on
   which Slice 4 declined the API write, and it is the stronger of the two because it does not depend on
   the state of third-party websites.

The coverage bar in the dispatch — ">80% Global coverage" — is not close to met by any candidate: the best
number available from any source is **19.7%**, and that candidate is not a recommendation at all.

## 1. Schema check

`git grep -nE "best_for|recommended_usage" -- app database config resources routes tests docs` → **0 files**.
The names are free, so there is no existing column with a conflicting meaning to reconcile; an ADR would not
have had to note a collision.

The `skills` surface as it stands (`2026_09_29_021157_add_reference_fields_to_skills_table.php`): `export_id`,
`rarity`, `release_status`, `name_is_client`, and the provenance quartet `source_url` / `snapshot_path` /
`fetched_at` / `source_timezone` plus `is_manual`. Nothing in that set is a usage, role, target or priority
field.

**Where the term came from.** It exists once in tracked content, in an unrelated sense:
`docs/scenarios/02-unity-cup.md:45` — "Best for Sprint/Mile", about a **race distance**, not a skill. The
vocabulary is the slice's, not the project's.

## 2. GameTora key scan

Scanned twice, because the two artifacts answer different questions and only one of them is attributable.

- **Committed:** `tests/Fixtures/gametora-skills.sample.json` — 12 rows, 8 keys
  (`id`, `enname`, `jpname`, `rarity`, `condition_groups`, `name_en`, `cost`, `unreleased`). Per ADR-0011 the
  fixture is *cut from the live document* rather than hand-written, so its key names are the source's.
- **Untracked:** `database/seeders/data/skills.609afe88.json` — 1,910 rows, **30 keys**, 2,502,242 bytes.
  This body is **not on any ref**, and its `seed_file` declaration sits inside a peer's uncommitted
  `config/uma.php` diff, so the counts below are not attributable to a commit. `git show HEAD:config/uma.php`
  contains no `seed_file` key at all.

All 30 keys: `activation`, `condition_groups`, `desc_ko`, `desc_tw`, `endesc`, `enname`, `iconid`, `id`,
`jpdesc`, `jpname`, `rarity`, `tid`, `type`, `name_ko`, `name_tw`, `char`, `loc`, `unreleased`, `desc_en`,
`name_en`, `cost`, `versions`, `evo_cond`, `pre_evo`, `sup_e`, `sup_hint`, `evo`, `gene_version`, `char_e`,
`sce_e`.

**None is a usage, role, target, priority, tag or rating field.** The candidate filter was self-checked
before being trusted — 6/6 of `best_for`, `recommended_usage`, `skill_role`, `target_stats`, `notes`,
`priority` fire on the filter, and no real document key does. A zero from a filter that cannot fire is not
evidence; this one can. Probe: `.scratch-uma/skill-key-values.php` (replayable).

The keys that could have been a `best_for` under another name are not one, read by value:

| Key | Present | What it actually holds |
|---|---|---|
| `char` | 1490 (78.0%) | The character ids whose **unique** skill this is — ownership, e.g. `It's Going to Be Me → [100302]`. Availability, not suitability. |
| `sup_hint` | 379 | Support-card ids that can hint the skill — a source list. |
| `type` | 1910 | Engine type codes (`nac`, `l_2`, `f_s`, `f_c`, `str`). |
| `condition_groups` | 1910 | Activation DSL: `{"condition":"is_last_straight==1","effects":[{"type":27,"value":4500}],…}`. |
| `sce_e` | 102 | Scenario effect codes. |
| `evo_cond` / `pre_evo` / `versions` / `evo` | 672 / 672 / 821 / 346 | Upgrade chains. |
| `loc` / `unreleased` | 1308 / 1287 | Localisation payload; which locales lack the skill. |

### The one signal that is a client fact, and its coverage

`docs/UMAMUSUME_REFERENCE.md:242` states the mechanic: a skill whose condition contains `running_style==N`
**cannot fire for any other style**, so the label rewrites the usable skill list rather than a hidden curve.
That is genuine "who is this for" information, published by the client, and it is the only such signal found.

Measured over the untracked body (1,910 rows):

```
Global English subset (no 'en' in unreleased)  623  32.6%
carry any condition string                   1,910  100.0%
gated by running_style==                       646  33.8%   -> of the Global subset: 123, 19.7%
mention surface==                                0   0.0%
mention distance                               997  52.2%
  running_style==1  133 rows / ==2  320 / ==3  235 / ==4  141
```

The Global subset of 623 **matches ADR-0011's own committed figure** ("on the 623 …"), which corroborates
the reading of `unreleased` rather than leaving it as my invention. The app's predicate is
`Skill::scopeAvailableOnGlobal()` — `release_status = GlobalReleased`, and 535 of those 623 are client-named.

**19.7% is the ceiling of what could be sourced, and it is not a recommendation.** A style gate says when a
skill may fire; it does not say the skill is good. Presenting it as `best_for` would relabel an eligibility
fact as a judgement — the exact inversion this register has already caught twice, in the tier-label hold and
in the provisional `grade_banding`.

## 3. Game8

Both relevant surfaces are **Global-current**, dated **September 28, 2026**:

- **Skills tier list** (`game8.co/.../archives/536805`): columns are exactly **`Tier` / `Skill` / `Points`**.
  **No target column.** Fetched content notes that specific use cases ("dirt tracks", "late-race") appear
  *inline in prose descriptions*, not as a field. Roughly 60+ skills listed against 623 Global rows — about
  10%, and it is a ranking rather than a mapping.
- **Per-character build guide** (`archives/536303`, Daiwa Scarlet): a "### Recommended Skills" section,
  categorised as Velocity / Acceleration / Recovery / Others, presented as **bare linked skill names with no
  per-skill "best for" annotation**. The relation is character → skills, one page per trainee, so inverting it
  into skill → characters would require crawling every guide and would still be **editorial opinion**, not a
  client fact.

The precedent that settles how to treat this: the Slice 2 ruling held card tier labels because Game8's list is
"dated and scored at Max Limit Break". The same class of objection applies here — a third-party score, on
their subset, restated as this tool's fact.

## 4. Third source: the two named scenario docs

Checked, and **neither carries skill-role framing**. What they do say about skills:

- `docs/scenarios/04-trackblazer-umaguide.md` — priority language exists but points at **shop items and deck
  composition**, not at skills: "Both Must Buy; +1 has priority" (mood items, :156), "Low priority filler
  only" (stats item, :155), "Power cards that carry good skills" (:19, about *support cards*).
- `docs/scenarios/05-trackblazer-gametora.md` — skills appear only as **hint sources**: "Winning (1st place)
  vs. the rival grants one random skill hint, tied to either the race's distance or the running style you
  used" (:127), and unique-skill level-up conditions (:150-155).

The concept sweep across `docs/scenarios/` and `docs/UMAMUSUME_REFERENCE.md` returned three hits, and the two
that are not the scenario docs above are:

- `UMAMUSUME_REFERENCE.md:146` — the closest thing to a real skill-priority concept anywhere: the
  「スキルセット」 feature sorts desired skills into **three priority groups** (超優先 / 優先 / 通常). It is
  **`[JP]`** official news 2026.09.11, it is **player-authored** rather than published per skill, and the same
  line records that **no `[Global]` notice for an equivalent feature** appears in the Global news index
  checked 2026-09-27. So the game's own notion of skill priority exists, is a personal list, and is not on
  Global.
- `docs/scenarios/09-global-race-calendar.md:47` — a `G1` averseness skill quote with Game8's per-race tier;
  a race-tier citation, not a skill-role taxonomy.

## 5. What would be worth building instead, if anything is

Recorded as an option, **not** as a recommendation and not as authorisation:

The client does state eligibility for 123 of 623 Global skills. If that number is ever worth surfacing, the
correct shape is a **derived badge on the existing skill screen**, computed from `condition_groups` at read
time, saying which running styles the skill can fire in — because that is a fact the source states, it needs
no new column, and it cannot drift from the data it is derived from. It would need the style-number-to-label
mapping resolved from client strings first: `factors.json` ids 21-24 give **Front Runner / Pace Chaser /
Late Surger / End Closer**, and the labels in wider circulation ("Runner / Leader / Betweener / Chaser")
are not the shipped strings — "Betweener" has zero occurrences in client data. No count in this document is
labelled for that reason.

A `best_for` column would instead store, per skill, a judgement no source makes, at 19.7% coverage at best,
against a requirement `PRD.md` does not contain.

## 6. Fence confirmation

Honoured in full. No `config/uma.php` write, no `phpunit.xml` write, no migration, no column, no backfill,
no UI, no `migrate`, no scratch database. Nothing was committed to the schema and no `.sqlite` file was
touched by this phase — the shared development database was neither read nor written.

Reads performed: `app/`, `config/`, `database/migrations/`, `docs/scenarios/`, `docs/UMAMUSUME_REFERENCE.md`,
`docs/adr/0011`, `tests/Fixtures/`, the untracked skills JSON (read only, provenance stated above), and two
public Game8 pages for the sole purpose of establishing whether a column exists on them.

Two provenance limits carried forward rather than hidden:

1. The 1,910-row coverage counts come from an **untracked** body whose `seed_file` line is in a peer's
   uncommitted diff, so they are not attributable to a ref. The Global subset size (623) is corroborated by
   ADR-0011's committed text, which is what makes the number usable despite that.
2. The Game8 column reading is a **snapshot of a live page** on 2026-10-01. If Game8 restructures, re-check
   before relying on "no target column".

Probes, replayable: `.scratch-uma/skill-key-scan.php`, `.scratch-uma/skill-key-values.php` (includes the
filter self-check), `.scratch-uma/skill-eligibility-coverage.php`. All three live under `.scratch-uma/`,
which `.gitignore:88` excludes.

Sources:
- [Best Skills Tier List | Umamusume — Game8](https://game8.co/games/Umamusume-Pretty-Derby/archives/536805)
- [Daiwa Scarlet (Peak Blue) Build Guide — Game8](https://game8.co/games/Umamusume-Pretty-Derby/archives/536303)
- [Game8 all-races table, cited in `docs/scenarios/09-global-race-calendar.md:47`](https://game8.co/games/Umamusume-Pretty-Derby/archives/536131)
