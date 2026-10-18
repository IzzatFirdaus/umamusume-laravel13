# Open Questions

This file is `docs/OPEN-QUESTIONS.md`, the open-question register for `PRD.md`. It was extracted from
`PRD.md` §7 on 2026-10-18, when `PRD.md` was restructured to state current requirements only, and moved from
the repository root into `docs/` the same day because `AGENTS.md` §3 forbids a new markdown file in the root.
`PRD.md` §7 keeps a one-line-per-question pointer here. Answered-in-part narration lives in `CHANGELOG.md`,
not in this table. No owner is named that `PRD.md` did not name: where the source says "the owner" or "the
Trainer", this file says the same and does not invent an individual.

## Register

| ID   | Question                                                                                                                                     | Status                                                                     | Owner                                        | Resolved in                                                                                     |
| ---- | -------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------- | -------------------------------------------- | ----------------------------------------------------------------------------------------------- |
| OQ-1 | What is the product called?                                                                                                                  | **CLOSED 2026-09-27**: **Trainer Desk**.                                     | The owner, by decision 2026-09-27            | `DESIGN.md`; `PRD.md` §1                                                                          |
| OQ-2 | Which sources feed the cross-reference engine, on what legality and refresh policy?                                                           | **PARTIALLY RESOLVED 2026-09-28**: first source approved.                    | The Trainer picks each added source          | `config/uma.php` (`gametora-characters`, entry dated 2026-09-27)                                  |
| OQ-3 | Should `uma:fetch` run on the Laravel scheduler by default, or only manually?                                                                  | **OPEN**: default is manual, until rate-limit behaviour of chosen sources is observed. | — (no ruling recorded)                       | — still open                                                                                      |
| OQ-4 | Do aptitude grades and stat caps enter the product as engine-owned facts?                                                                     | **CLOSED 2026-09-27**: yes, as engine-owned facts with provenance.           | The owner                                    | `ADR-0004`; `PRD.md` FR-A-5                                                                       |
| OQ-5 | Should the skill screen surface a skill's running-style eligibility ("this skill can only fire for style X"), and is that a feature at all?    | **OPEN 2026-10-01**, verdict recorded: do not ship a skill recommendation.  | The owner (a product question, not a defect) | Nothing yet; the only defensible shape is recorded for the day it is answered yes.                 |
| OQ-6 | Which artwork families does the mirror display, and on which screens?                                                                         | **OPEN 2026-10-05**, four of five surfaces answered.                        | The owner placed the four answered slots     | `ADR-0021` (layer and mechanism); per-screen answers go to `SCREEN_SPEC.md` §7 item 16; behaviour is `DESIGN.md` §4.7 |

## What each open question still holds

### OQ-2, remainder

Answered: one source is approved and configured. Remaining: robots/live availability verification,
refresh/schedule policy, additional source selection, parser maturity, and the promotion policy for data
types beyond characters. The standing rule from the question's original wording is not in doubt and stays a
requirement: each addition is a legal/robots.txt review plus one parser class, and the Trainer picks.

### OQ-3, remainder

Whether `uma:fetch` runs on the Laravel scheduler by default or only manually. It is manual today. The
observation the answer waits on is the rate-limit behaviour of the chosen sources.

### OQ-5, what is answered and what remains

Sourced mechanic: `docs/UMAMUSUME_REFERENCE.md` §1.2.2 states that a skill whose condition contains
`running_style==N` cannot fire for any other style, so the label rewrites the usable skill list rather than a
hidden curve. Phase A measured it on **123 of the 623 Global skill rows (19.7%)**, with `condition_groups`
carrying a condition string on 100% of rows.

Answered, in the negative: this is **not** `best_for` and must not be built as such. No source carries a
recommendation (GameTora's 30 keys include no usage, role, target or priority field; Game8's list is
`Tier / Skill / Points` with use cases inline in prose) and no requirement cites one. The 2026-10-01 record
stated this as "`PRD.md` having zero occurrences of `best_for`, "best for" or `recommend`". Measured 2026-10-18
against the file as it stood before this restructure, `best_for` and "best for" occur nowhere except as the
terms named in that sentence, while `recommend` occurs three times, in the US-14 / FR-F / §6.11 lines
`ADR-0020` authorized on 2026-10-04 — after the measurement. The load-bearing half of the claim therefore
stands: no requirement asks for a *skill* recommendation, and the Trainer Advisor recommends next-turn
training actions, not skills. Relabelling eligibility would repeat the inversion the card-tier-label hold and
the provisional `grade_banding` marker both refused.

Remains open, if the question is ever answered yes:

- The style **number-to-label** mapping must be resolved from client strings first. The four tracked aptitude
  columns `aptitude_front_runner` / `aptitude_pace_chaser` / `aptitude_late_surger` / `aptitude_end_closer`
  (`database/migrations/2026_09_27_090000_add_aptitude_grades_to_umamusume_table.php:19`, carried into
  `App\Models\Umamusume`'s `#[Fillable]`) name Front Runner / Pace Chaser / Late Surger / End Closer, and the
  style numbers those labels sit under were read from the untracked `factors.json` ids 21-24 rather than from
  anything tracked here. The four names in wider circulation ("Runner / Leader / Betweener / Chaser") are not
  the shipped strings, and "Betweener" has zero occurrences in client data.
- The only defensible shape is **derived at read time from `condition_groups`, no new column, no backfill**, so
  it cannot drift from the data it reads.
- A badge on 19.7% of skills must read as absence-of-data on the rest, the same rule the mood and `condition`
  fields already follow.

Nothing is authorised by this question.

### OQ-6, what is answered and what remains

`ADR-0021` authorizes the layer and fixes its mechanism: fetch by id from an allowlisted asset host into a
gitignored local directory, path derived at render time, nothing stored in the database. It does not pick the
families or place the slots, and this question is that remainder.

Answered and built (2026-10-05): `resources/js/components/ArtworkSlot.vue` renders a frame on the catalog index
(trainee header and each costume-form row), the trainee detail Identity section, the support-card index and the
support-card detail header. Mechanism, placement, geometry and click action are fixed; the frames stream over a
loopback route with nothing in the database. The "does a slot ever carry information" half is answered in the
negative, by choice: every slot is purely visual, `alt=""` wherever the row already prints the name, and no slot
carries a rarity marker or any other fact. Until an answer exists for a surface, the text-only row is what
renders, and that row, not a placeholder, is the fallback for a file that was never mirrored or has gone missing
upstream (`DESIGN.md` §4.7).

Remains open:

- The **pre-run pick screen** cannot host a frame as built: its trainee picker is a native `<select>` plus a
  client-rendered combobox listbox, and neither has a row to put an image in. This is blocked by an HTML
  content model, not by want of authorization.
- **Skill icons** need a `skills.iconid` migration before they are reachable at all (`ADR-0021` Verification);
  `skills` stores no icon column.
- Inside the settled part, unresolved: the catalog index resolves a trainee's portrait from her top-rarity form
  while the detail page resolves it from the active or first form, so a multi-form trainee can show two
  different portraits across the two screens. That is a placement decision for this document's owner rather
  than either screen assuming.

Three technical unknowns named in `ADR-0021` so a build does not guess at them: the four official profile poses
per trainee have no resolvable path yet, `gacha/char/thumb` coverage is partial and is therefore not a base, and
whether the Global client's own CDN host matches the JP one is unverified. A future slot is a Vue component and a
controller prop, not a Blade partial. Nothing is authorized here beyond what `ADR-0021` already grants, and
`PRD.md` §6.13 stays cut.

## Closed outcomes

- **OQ-1** (CLOSED 2026-09-27): the product name is **Trainer Desk**, owner decision recorded in `DESIGN.md`.
  `APP_NAME` in `.env` should be set to match at the next config change; that instruction has no recorded
  execution.
- **OQ-4** (CLOSED 2026-09-27 by the owner): aptitude grades and stat caps enter as engine-owned facts with
  provenance. The closure's scope is exactly what `ADR-0004` implements: the ten aptitude letters (FR-A-5) and
  the per-scenario stat caps (FR-A-5, `scenarios` table), both arriving through `uma:fetch` against a declared
  source. Growth rates and base stats stay out: nothing in `PRD.md` requires them yet, and `ADR-0004` leaves them
  unimplemented rather than seeding them by hand.
