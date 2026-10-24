# ADR-0029: Per-run measurement facts — the eight the source reads and the tree cannot store

<!-- ADR-CONTRACT
  decision: proposed — authorise eight per-run measurement facts as storage, one column or table each, with deck_slots.card_level holding the numerator only; the owner flips this to Accepted
  code: not built — no column or table for any of the eight exists: deck_slots carries support_card_id, slot_position and ownership only; turn_entries carries no facility_banner and no training_card_ids; training_runs carries no team_stat_grades and no current_spirit; there is no team_roster, no skill_levels and no roster-skill table
  superseded-by: —
  amended-by: —
  open: the eight storages are authorised but unbuilt, so every audit row stays blocked until a slice migrates it; A-0.4 stores nothing and is computed from A-0.5's column; A-0.13 and A-0.14 have no PRD anchor and are marked needs-PRD-citation; US-12's sentence "no card level, limit break or Unique Perk state is stored anywhere" needs a dated travel note if this ADR is accepted
-->

Status: **Proposed** — drafted 2026-10-24 for the owner's review. The agent drafts an ADR; the owner accepts it. Nothing here builds a column, a table, a model, or a slice.

## Context

The source document `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` reads eight facts off one mid-career run that the tree has no column or table for. Two Group A/B passes and the audit addendum of 2026-10-24 found them independently, in two rounds, and filed every one as `needs-schema`: four in the prior pass (`A-0.5`, `B-0.5`, `B-0.6`, `B-0.7`), three in the addendum (`A-0.12`, `A-0.13`, `A-0.14`), and one chained on another (`A-0.4`).

| Item | KI | The audit row's own finding |
|---|---|---|
| A-0.4 | KI-116 | "the run's reached effect values are still unrecorded — the builder's analysis continues to use catalog anchors" |
| A-0.5 | KI-117 | "The **per-run** half ('what this run's equipped card is at') is recorded open" |
| A-0.12 | KI-124 | "There is no column for the facility banner name and no column for the three support cards whose avatars the tile showed" |
| A-0.13 | KI-125 | "The 20/20 counter counts a stored roster the tool does not hold (the derived deck roster is 6, not 18)" |
| A-0.14 | KI-126 | "The `A-0.6` derived roster carries each member's name and state only — no skill list" |
| B-0.5 | KI-118 | "`TeamPanelBuilder::teamSection()` hard-codes `grade => null` for every stat" |
| B-0.6 | KI-119 | "mounts `ResourceMeter` with `value: null, max: null`" |
| B-0.7 | KI-120 | "`SkillsPlannerController` carries acquisition state only" |

`ADR-0014` declined the collection and left the per-run half open. Its `open:` field reads: "card levels and limit breaks are still unrecorded, and the run's reached effect values are still unrecorded — the builder's analysis continues to use catalog anchors". Its `UserSupportCard` row reads: "the Trainer's collection: level, breaks, perk level | no | Collection tracking is still the feature §6.9 cut". The collection gate in `ADR-0005` reads: "A new PRD story, written by the owner, that authorizes the Trainer's collection (levels, limit breaks, perk level). `ADR-0014` did not write that story; it took the reference-plus-deck half only." Those sentences are about inventory held across runs. This ADR is about what one run held.

`ADR-0004` made the aptitude catalogue authoritative, and the owner ruled at `KI-115` that per-run aptitude letters are correctly not stored; nothing here touches aptitudes and `A-0.2` is not re-opened. `ADR-0011`'s hint-level ruling, `ADR-0005`'s R37 hint-pool cut, and `ADR-0016`'s held win-probability question all stand unchanged.

The eight are one decision because the source reads them as one snapshot of one run. Splitting them would hand the owner eight documents to rule on separately, each of which would have to repeat the snapshot-versus-collection distinction that keeps `ADR-0014` standing.

## Decision

Eight facts, eight storages. Each row authorises a column or a table; none builds one, and none picks the slice shape. A migration still needs the schema bundle: a PRD citation, an `ARCHITECTURE-ESSENTIALS.md` digest line, an ADR, and a working `down()`.

| Item | KI | Storage | Type | Semantics | PRD anchor |
|---|---|---|---|---|---|
| A-0.4 | KI-116 | none — computed from `deck_slots.card_level` and `support_effects` | derived read-out | computed, never stored | FR-C-6, US-12 |
| A-0.5 | KI-117 | `deck_slots.card_level` | integer, nullable | **numerator only** | FR-C-6, US-12 (travel note) |
| A-0.12 | KI-124 | `turn_entries.facility_banner`, `turn_entries.training_card_ids` | string; list or join table | entered | FR-C-2, US-3 (travel note) |
| A-0.13 | KI-125 | `team_roster` | table | counted roster | needs-PRD-citation |
| A-0.14 | KI-126 | `team_roster_member_skill` | table | entered | needs-PRD-citation |
| B-0.5 | KI-118 | `training_runs.team_stat_grades` | json or enum | entered | FR-C-1 (travel note) |
| B-0.6 | KI-119 | `training_runs.current_spirit` | integer, nullable | entered | FR-C-1 (travel note) |
| B-0.7 | KI-120 | `skill_levels` | table | entered | FR-C-3, US-4 (travel note) |

**D1 — A-0.5 stores the numerator only.** `deck_slots.card_level` holds the level the card was at in this run, as the client showed it at record time. It is not the cap, not the limit-break count, not the perk level. Those three are collection state, and the collection stays declined by `ADR-0014`. A slice that wants the cap returns for a new ruling.

**D2 — A-0.4 is a derivation, not a column.** The reached effect values are computed from `deck_slots.card_level` and the catalog anchors, with floor-interpolation between them, exactly as `ADR-0005` correction 4 already ruled. No per-level effect table is authorised.

**D3 — B-0.6 stores the reading, not the band.** `training_runs.current_spirit` holds the integer the client showed. It carries no maximum, because no source states one; the gauge's maximum stays null and the panel keeps saying so. No readiness band is derived.

**D4 — B-0.7 stores the level and nothing else.** `skill_levels` is run × skill → level integer. No percentage, no hint-pool import, no rarity re-derivation: `ADR-0011`'s "level only, no percentage" is the copy rule, and the R37 cut in `ADR-0005` stands.

**D5 — A-0.12 stores two entered facts.** `facility_banner` is the banner string the tile showed; `training_card_ids` is the set of cards whose avatars the tile showed. Both are entered readings of a turn, in the grain of `failure_rate` and `preview_gains`.

**D6 — A-0.13 and A-0.14 need a PRD anchor.** `team_roster` and `team_roster_member_skill` have no PRD home: `FR-C` has no roster object. The owner either adds the citation or refuses the two items.

**D7 — The counter reads N of 20 as the client shows it.** The `A-0.6` derived six-card roster is unchanged; the counted roster is stored. The 20 is a client display figure, so the counter prints the stored count against 20 and names the absence when nothing is stored.

## Consequences

### 1. What this authorises

- **A-0.4** — a slice may read the effect values a run's cards had reached, computed from the stored level.
- **A-0.5** — a slice may store one card level per deck slot, as the client showed it at record time.
- **A-0.12** — a slice may store the training tile's banner and the cards whose avatars the tile showed.
- **A-0.13** — a slice may store a team roster and print the members counter the client shows.
- **A-0.14** — a slice may store the skills a roster member carries.
- **B-0.5** — a slice may store the five team stat grades, so the panel stops printing `N/A`.
- **B-0.6** — a slice may store the Current Spirit reading, so the gauge has a value.
- **B-0.7** — a slice may store the level a skill reached in a run.

The four rows filed `blocked: ADR` and the three filed `needs-schema` become openable for slicing once this ADR is Accepted, and each slice still carries its own schema bundle.

### 2. What this does not authorise

- **The collection stays declined.** `ADR-0014`'s `UserSupportCard` row stands: no `user_support_cards`, no level, no limit break, no Unique Perk state. Only the per-run level is in scope, and only as the numerator. `ADR-0014` is referenced here; it is not changed.
- **Card limit-break counts stay unrecorded**, and `max_level` stays derived, per `ADR-0005` correction 5.
- **Aptitudes stay catalogue-authoritative** (`ADR-0004`), and `KI-115`'s ruling stands.
- **Hint pools stay cut** (the R37 ruling in `ADR-0005`); hint levels stay "level only, no percentage" (`ADR-0011`).
- **Win probability stays withheld** (`ADR-0016`); `PRD.md` §6.11 is unchanged.
- **No per-level effect table** (`ADR-0005` correction 4).
- **No code, migration, model, or slice** is opened by this document.

Two items carry no PRD anchor and are marked **`needs-PRD-citation`** here: **A-0.13** (the stored roster) and **A-0.14** (a roster member's skills). The owner either adds the PRD citation or refuses the item; nothing is built in the meantime. Four further items take a dated travel note on the requirement they extend: **B-0.5** and **B-0.6** on `FR-C-1`, **A-0.12** on `FR-C-2`, **B-0.7** on `FR-C-3`, following the `ADR-0025` precedent. One item needs its requirement text re-read before the note is written: **A-0.5** sits under `US-12`, whose acceptance sentence reads "no card level, limit break or Unique Perk state is stored anywhere (identity, not collection)" — a per-run level is a card level stored somewhere, so `US-12` must travel with a dated note that narrows that sentence to the collection reading, or the two requirements contradict each other on the page.

### 3. What happens if the owner declines

The three addendum items (`A-0.12`, `A-0.13`, `A-0.14`) and the four prior `blocked: ADR` items (`A-0.5`, `B-0.5`, `B-0.6`, `B-0.7`) stay blocked, `A-0.4` stays chained on `A-0.5`, all eight stay filed under `KI-116`, `KI-117`, `KI-118`, `KI-119`, `KI-120`, `KI-124`, `KI-125` and `KI-126`, and no code slice opens. That is a ruling, not an oversight.

## Alternatives considered

1. **Split into per-surface ADRs** — one for the deck, one for the team, one for the skills, one for the roster. Rejected: the owner would review eight one-page documents where one two-page document covers the same decision, and the source document reads these as one snapshot rather than eight independent facts.
2. **Decline the whole set** and record each item as a "will not add". Not recommended, because the source document reads them and the walk audits verified each one; recorded so the owner has an explicit decline-all path, whose consequence is the subsection above.
3. **Store the whole snapshot as one JSON column on `training_runs`.** Rejected: queryability (a grade, a level and a banner are read by different surfaces and filtered differently), one owner per fact (a shared blob has no owner when two surfaces want different parts of it), and no schema-shape drift (a blob's shape changes with every new fact; a column does not).

## Links

- Audit rows: `docs/audits/group-ab-audit-2026-10-24.md` — `A-0.4`, `A-0.5`, `B-0.5`, `B-0.6`, `B-0.7`, and the 2026-10-24 addendum's `A-0.12`, `A-0.13`, `A-0.14`
- Defect register: `KNOWN-ISSUES.md` — `KI-116` through `KI-126`; `KI-115` for the aptitude ruling
- Source: `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` §1.3, §1.4, §1.7, §1.8, §1.9
- PRD: `PRD.md` — `US-3`, `US-4`, `US-12`; `FR-C-1`, `FR-C-2`, `FR-C-3`, `FR-C-6`; §6.9, §6.11
- Standing rulings referenced here and left unchanged: `ADR-0004`, `ADR-0005`, `ADR-0011`, `ADR-0014`, `ADR-0016`, `ADR-0025`, `ADR-0027`

## Ledger

- 2026-10-24 · **drafted** — `Proposed` by the Group A/B audit reconciliation pass. Authorises the eight per-run measurement facts as storage and builds none of them. The collection decline, the catalogue-aptitude rule, the R37 hint-pool cut, the held win-probability question and the §6.11 hold are all left standing; none of the ADRs this document references was edited, and no PRD requirement was re-typed.
