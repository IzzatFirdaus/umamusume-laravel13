# Slice 6 — documentation currency pass: PRD and its cross-references

Date: 2026-10-01. Read the PRD, fixed exactly two lines elsewhere (KI-48), filed everything else.
No `PRD.md` content change was made by this pass. No migration, no schema, no code, no scratch database.
`config/uma.php` and `phpunit.xml` were not read as inputs and not written.

## 1. Method note

**Scanned.** `PRD.md` (191 lines) in full: every `§` reference, every `FR-*`/`US-*` token, every counted
claim, every backticked token. Each outbound path was resolved against the tree; each external `§` target was
opened and read. `AGENTS.md` and `CONSTRAINTS.md` were read for the two rules recent decisions cited.
`docs/PRE-MORTEM.md`, `docs/UMAMUSUME_REFERENCE.md`, `docs/adr/0003`, `docs/adr/0008`,
`docs/adr/0015` and the Slice 5 Phase A record were opened only to resolve a citation that pointed at them.

**Tooling.** `git grep` with a canary per pattern, plus three replayable probes under gitignored
`.scratch-uma/`: `prd-outbound-citations.php` (path resolution), and for the requirement-ID map the loop
recorded in §2. No test was run, because nothing executable changed; the gate results are in §4.

**Excluded, and why.** Product decisions: where the PRD and shipped behaviour disagree, this pass records the
disagreement and does not resolve it. `ARCHITECTURE.md` and `ARCHITECTURE-ESSENTIALS.md` were touched **only**
for KI-48's two lines — a listing gap found in them is reported in §5, not fixed. `docs/deprecated/**` was
not opened for editing, only `docs/deprecated/REVIEW-2026-09-30.md` §7 was used as the shape precedent for
the citation scan.

**Two discipline notes, because both changed an answer mid-pass.**

1. **A first regex silently matched zero `FR-*` ids.** The pattern was `FR-[A-Z]?[0-9]+`, which cannot match
   `FR-A-5` — there is a hyphen between the letter and the digit. The zero was a blind filter, not an
   absence. Re-run with `FR-[A-Z](-[0-9]+)?` plus a canary (`does FR-C-1 appear in PRD.md?`) before any
   number in this document is trusted. The canary is what surfaced §2.1's central finding.
2. **A first path classifier reported seven broken citations; four were not citations at all.** `/api/v1` is a
   URL prefix, `Asia/Tokyo` a timezone value, `maatwebsite/excel` a composer package, `.json` prose. A scanner
   that treats "backticked and contains a slash" as a file reference manufactures broken links, so the script
   was rewritten to enumerate evidence and leave classification to the reader. N was 22; that was the right
   trade.

## 2. PRD internal consistency findings

Each line is `file:line` plus the evidence, per instruction. **Filed, not fixed.**

- **P-1 — the PRD does not use the `FR-` prefix that every citation to it uses.**
  `PRD.md:41,118,126,142,147` define sub-requirements as `- A-1:`, `- B-4:`, `- C-1:`, `- D-2:`, `- E-1:`
  under `### FR-A:` … `### FR-E:` headings. `git grep -c "FR-C-1" -- PRD.md` returns **0**. Meanwhile
  **26 distinct `FR-X-N` ids** are cited outside the PRD (~250 occurrences; `FR-B-4` alone 43). Mapping each
  through the `FR-` strip rule: **25 resolve to a real `- X-N:` item, 1 does not** (see P-2). So the drift is
  systematic, resolvable by a human, and ungreppable inside the PRD. `US-*` ids are literal (`| US-1 |` at
  `PRD.md:26`), so the two families are inconsistent with each other. Origin is `AGENTS.md:25` — see §4.
  *Severity: notation drift, not a broken promise. Fix is one line in AGENTS.md or a prefix added to the PRD
  list markers; either way it is the next slice's, and it must be one of the two, not both.*

- **P-2 — orphan requirement: `FR-C-7` is cited and was never created.**
  `docs/adr/0003-consolidated-phase1-schema-expansion.md:104`: "add FR-C-6 for Energy and Fans, **FR-C-7 for
  event and race logging**, and amend §6.11". `PRD.md` FR-C ends at `C-6`. The tables that requirement would
  cover **are shipped** (`turn_events`, `scenario_races`, `race_entries`, all migrated on 2026-09-27), so this
  is not an unbuilt feature — it is a written requirement that never landed in the document while its schema
  did.

- **P-3 — `FR-C-6` means two different things.** The same ADR line proposes `FR-C-6` **for Energy and Fans**.
  `PRD.md:132` uses `C-6` for **`DeckSlot`**, added later under `ADR-0014` (owner authorization 2026-09-30).
  The slot was claimed by a different requirement after the plan that reserved it. A reader following
  `FR-C-6` out of ADR-0003 reaches the deck.

- **P-4 — three shipped columns have no PRD requirement.** `turn_entries` carries `energy`, `mood`, `fans`
  (verified in the creating migration), the guided form collects them, and `export()` emits them. `PRD.md:128`
  (C-2) lists "five stat integers, SP integer, optional condition string" and stops there. Across the whole
  PRD, the words energy / fans / mood appear in **one place only: `PRD.md:184`, my own OQ-5 aside**. This is
  the concrete cost of P-2 and P-3: the requirement that would have cited them was planned as `FR-C-6`, never
  written, and its number was then reused. Under `AGENTS.md:25` ("every new … column must cite a PRD
  requirement"), these three are uncited columns on a shipped table. *This is a PRD-vs-shipped disagreement,
  so per scope it is filed and not fixed; it is also the single most consequential item in this pass.*

- **P-5 — `ADR-0015`'s supersession reached the PRD but not the architecture docs.** `PRD.md:28` and `:128`
  both carry the supersession note; `ARCHITECTURE-ESSENTIALS.md:36` and `ARCHITECTURE.md:158` did not. That is
  **KI-48**, and it is the one finding this slice was told to fix — done in §3.

- **P-6 — one citation inside the PRD points at an untracked file, and it is mine.** `PRD.md:184` (OQ-5) says
  the style labels come from "`factors.json` ids 21–24". `git ls-files | grep -i factors` returns **nothing**;
  the file exists only under ignored scratch dirs. The *claim* survives independently on tracked content —
  `database/migrations` defines exactly four running-style aptitude columns, `aptitude_front_runner`,
  `aptitude_pace_chaser`, `aptitude_late_surger`, `aptitude_end_closer` — but the *pointer* is not durable.
  Filed for the next PRD pass: retarget the citation to the aptitude columns. Not edited here, because the
  fence says the PRD is read, not rewritten.

- **P-7: the four running-style counts at `UMAMUSUME_REFERENCE.md:231-234` do not state their denominator.**
  The column "Skills gated to it" reads 107, 220, 166 and 115. Measured over the whole export body
  `database/seeders/data/skills.609afe88.json`, which holds 1,910 records, counting skills whose condition set
  pins exactly one `running_style` value gives 107, 220, 166, 115. Those four numbers reproduce, so the table
  is not wrong. Measured over the 623 records that carry no `unreleased` key, the same definition gives 26, 35,
  33, 27. The caption at `:236` names the predicate, "skills whose condition set pins exactly one
  `running_style` value", and cites the export, but never names the set the count runs over, so a reader cannot
  tell which of the two it is. Every consumer downstream shows the smaller list: `app/Enums/ReleaseStatus.php`
  renders Global states only, and `docs/design-research/skill-facts-2026-10-01.md` covers exactly the 623. So
  the reference's numbers would sit beside a list a third their size with nothing on the page saying so. Fix is
  one clause stating the denominator. Do not change the figures; they are correct for what they count. This is
  a Docs Writer item and not a KI, because no shipped behaviour reads those four cells. Filed here beside P-6
  rather than in the register, since it is the same class of citation-hygiene defect and P-6 already anchors the
  pair. Measurement: parse `database/seeders/data/skills.609afe88.json`, collect the distinct
  `running_style==N` values from `condition_groups[].condition` and `.precondition` per record, and count
  records whose set has exactly one member, once over all 1,910 records and once over the 623 with no
  `unreleased` key. The script that ran it is `skill-facts-2026-10-01.md`'s generator, and that document
  reports both figures: the per-label Global counts in its coverage table and the whole-export counts in the
  reconciliation paragraph beneath it.

**Checked and clean — recording these because a currency pass that names nothing checked is unauditable.**

- **All 10 `§` references resolve.** Internal: `§3`, `§5`, `§6` (`PRD.md:35–37`), `§6.9` (`:37,90`), `§6.11`
  (`:35,75,110`) — `§6` is a numbered 1–13 list, so `.9` and `.11` resolve by ordinal to items 9 and 11.
  External: `docs/PRE-MORTEM.md` §4.1 exists at its line 67; Phase A doc §5 exists at its line 136;
  `docs/UMAMUSUME_REFERENCE.md` §1.2.2 spans 225–249 and does carry the eligibility mechanic at 242.
- **No counted claim is stale.** "Ten aptitude letters" (`OQ-4`) is exactly the 10 `aptitude_*` columns in the
  migrations. "Slot position one to six" (`C-6`) matches `DeckSlot::POSITIONS`. "CSV/JSON only" matches
  `export()`'s two formats. `E-1` states no endpoint count, so there is nothing to rot.
- **Supersession notes are present where the ADRs landed.** §6 item 3 carries `ADR-0010` narrowing; item 9
  carries `ADR-0014`'s half-lift and the still-cut collection; `C-2` carries `ADR-0015`; `C-6` carries
  `ADR-0014`; `D-1`/`D-3` carry `ADR-0011`. §6 item 11 is correctly untouched by `ADR-0016`, which explicitly
  declines to amend it.
- **No requirement ID appears twice inside the PRD with two meanings.** The duplication the scan found
  (`FR-A-5` twice at `:183`, `US-7` at `:32` and `:36`, `US-1`/`US-3` in §3 and §8) is same-id-same-meaning
  reuse. The collision is *across* documents: P-3.

## 3. PRD outbound citation resolution

22 backticked candidates, classified by a reader after the scanner printed evidence.

| Class | Count | Items |
|---|---|---|
| Resolves from repo root | 10 | `AGENTS.md:59`, `tests/Feature/CatalogDetailPageTest.php:82`, `docs/adr/0012…:87`, `CONSTRAINTS.md:143,166`, `DESIGN.md:180`, `config/uma.php:181`, `KNOWN-ISSUES.md:184`, `docs/UMAMUSUME_REFERENCE.md:184`, `docs/design-research/slice-5-phase-a-best-for-2026-10-01.md:184` |
| Loose but findable | 3 | `components/layout.blade.php:36` → `resources/views/components/…`; `2026_09_29_182820_create_umamusume_profiles_table.php:84` → `database/migrations/…`; `factors.json:184` → **not tracked** (see P-6) |
| Not citations at all | 8 | `GET /umamusume…:26,31`, `.json:31`, `/api/v1:34,148`, `Asia/Tokyo:124`, `maatwebsite/excel:168`, `Tier / Skill / Points:184` |

**Broken: 0 of 22. Stale: 1 (`factors.json`, P-6). Resolvable: 21.** Two of the three loose paths omit a
directory prefix, which is style rather than rot — a reader finds them — but neither is greppable from the
PRD as written.

**Line-anchored citations inside the PRD: none.** `PRD.md` cites documents and sections, never `file:line`,
so the past-EOF class of rot cannot occur here. That is worth knowing: the PRD's citation surface is
section-level, and section-level anchors survive refactors that would break a line number.

## 4. KI-48 fix

Two edits, both forward-append errata following `ADR-0008`'s dated-erratum pattern: the historical sentence
stands verbatim and the correction is appended with its date. Both cite `ADR-0015` and `8bda7db`
(verified: `git log -1 8bda7db` → "feat(bounds): each stat is capped by its own scenario, not a flat 1200",
2026-09-30 22:16:14 +0800 — and `git log --diff-filter=A` on the ADR file returns the same sha, so the brief's
attribution is correct).

**`ARCHITECTURE-ESSENTIALS.md:36`**

Before, ending the line:

> `… uniq(run, turn); stats validated 0..1200, turn >= 1 [rev 0.2 — repo #4]`

After, same line with the erratum appended (nothing above it altered):

> `… uniq(run, turn); stats validated 0..1200, turn >= 1 [rev 0.2 — repo #4]. **Bound corrected 2026-10-01 by a dated erratum; the `0..1200` above stands as the state as written.** ADR-0015 (owner decision 5, 2026-09-30, landed in 8bda7db) replaced the flat bound with the run's **own per-stat scenario ceiling** — base_cap (1200) plus that scenario's cap_bonus for that stat, clamped to the engine hard_cap (2000) — read through App\Services\ScenarioCaps … So a stat is accepted to 1400 on URA Finale, to 1800 on Unity Cup Wit, to 1900 on Trackblazer Stamina and to 1600 on Our Grand Concert Speed; **1200 is the ceiling only where the run names no scenario**, which is the one case this sentence still states correctly … PRD.md carried this supersession from the start (:28, :128); the two architecture documents did not, and this is one of the two carriers recorded as **KI-48**.`

**`ARCHITECTURE.md:158`** — the listing uses `--` comment lines, so the erratum is five further `--` lines
under the untouched original:

Before:

> `  -- stats validated 0..1200, turn >= 1 (StoreTurnEntryRequest) [rev 0.2 — repo #4]`

After: the same line verbatim, then `-- BOUND CORRECTED 2026-10-01, dated erratum; the line above stands as
the state as written.` and the `ADR-0015` / `8bda7db` / `ScenarioCaps` explanation with the same four ceilings,
the same no-scenario exception, and the KI-48 pointer.

Both per-stat numbers were read from `config/scenarios.php` `cap_bonus` and checked against
`ADR-0015`'s table, not typed from memory: URA 200 across all five; Unity Cup 100/100/100/100/600; Trackblazer
0/700/0/0/300; Grand Concert 400/100/100/300/100, over `base_cap` 1200, clamped at `hard_cap` 2000.

**Gate results for this change.** Documents only, no executable code, so no test was added. Run for
confirmation of no breakage: `php artisan test --compact tests/Feature/DocSchemaDriftTest.php` (that guard
reads `ARCHITECTURE.md`, `ARCHITECTURE-ESSENTIALS.md`, `docs/adr/0008…` and `CONSTRAINTS.md`, i.e. exactly the
files edited here) — green. Lore grep over the added lines: no banned-pattern hits.

## 5. AGENTS.md and CONSTRAINTS.md — informational results

**`AGENTS.md:25`** — "Every new table, column, or class must cite a PRD requirement **(FR-x / US-x)**. No
citation, no merge." The rule as written is still how it is being applied, and both recent refusals used it
correctly: Slice 4 declined `POST /api/v1/training-runs/import` because `E-1` scopes the API to reads, and
Phase A declined `best_for` because no requirement mentions it. The application is sound; the **notation is the
origin of P-1**. The rule tells an agent to write `FR-C-1`, and `FR-C-1` is a string that appears nowhere in
the PRD it is citing — so every correct citation produced by following the rule is a string that cannot be
grepped back to its target. Suggested wording, **not applied**: after "(FR-x / US-x)", note that the PRD's own
markers are `- C-1:` under `### FR-C:`, so a citation `FR-C-1` resolves by stripping the prefix. That is a
one-clause edit to a governance rule and belongs to the owner, not to this pass.

One more consequence worth naming: the rule says *column*, and P-4 is three shipped columns with no
requirement. The rule was enforced on new work in this period while older columns went uncited, so the gate is
prospective rather than retrospective. If it is meant to reach back, that is a decision; nothing here assumes
it.

**`CONSTRAINTS.md:9` (C-1)** — "All Pest tests pass; every behavior change ships a test", command
`php artisan test --compact`. **No finding.** C-1's command is exactly what has been run for every gate in
this period, and the "ships a test" clause is what put 11 tests behind the import. `C-2` (`vendor/bin/phpstan
analyse --no-progress`) and `C-3` (`pint --dirty` then `pint --test`) likewise match the commands actually
used. One adjacent caveat, already on the record and not a C-1 wording defect: whether the suite touches a
file-based database is decided by an **uncommitted** `phpunit.xml` change, so C-1 is satisfiable today in a
way a fresh clone is not. That is the escalation row's problem, not the gate's text.

## 6. Not-slice-6 items surfaced

Named, not fixed. Each has a different remit.

- **N-1 — three uncited shipped columns (`turn_entries.energy`/`mood`/`fans`), and the `FR-C-6` collision and
  `FR-C-7` orphan behind them.** (P-2, P-3, P-4.) Remits to the **human owner and the Architect**: closing
  this means writing a PRD requirement, which is a product statement, not a documentation fix. Recommended as
  one item, because the three findings are one story — a planned requirement that never landed, whose number
  was then reused.
- **N-2 — the architecture listings omit `energy`, `mood`, `fans` as columns.** `ARCHITECTURE.md:154–156` and
  `ARCHITECTURE-ESSENTIALS.md:36` both list `turn_entries` fields without them, which is the same
  under-reporting as N-1 at the schema layer. **Deliberately not fixed**: the fence limits those files to
  KI-48's two lines, and this is not a consequence of `ADR-0015`. Remits to the Docs Writer with N-1.
- **N-3 — `factors.json` is cited from the PRD but is not tracked.** (P-6.) **Remits to the Docs Writer, not
  the Data Engineer — owner's ruling 2026-10-01, correcting this entry's first draft.** The fix is a citation
  change, not a data change. Two paths were weighed: commit the body under `database/seeders/data/` with a
  `seed_file` entry, which is the peer's in-flight pattern and would collide with `config/uma.php`'s current
  state; or retarget `PRD.md:184` to the tracked aptitude columns — `aptitude_front_runner`,
  `aptitude_pace_chaser`, `aptitude_late_surger`, `aptitude_end_closer` — which carry the same four styles on
  tracked content. **The second is smaller, needs no data decision, and is the one to take.**
- **N-4 — the `FR-` prefix notation drift.** (P-1.) Remits to whoever owns `AGENTS.md:25`. One clause fixes
  it; doing it in the PRD instead means re-prefixing ~30 list markers, which is a larger and worse change.
- **N-5 — the product question surfaced while filing OQ-5, preserved here so it is not lost with the
  withdrawn draft.** *Does the tool advise, or only record?* The OQ-6 draft was withdrawn as unasked-for
  scope; if the owner wants it stated, the wording is recoverable from `b7f105e`'s neighbourhood. Filed here
  so the question is not lost with the draft.

## 7. Fence confirmation

`config/uma.php` — untouched, still dirty with the peer's work; not read as an input to any conclusion except
where a `cap_bonus` value was verified, which is a read of a file this pass does not edit. `phpunit.xml` —
untouched. No migration, no schema, no column, no backfill, no scratch database, no `.sqlite` read or
written. **No `PRD.md` content change**: the PRD was read, and every finding that needs it is filed in §2 and
§6. `ARCHITECTURE.md` and `ARCHITECTURE-ESSENTIALS.md` changed only at KI-48's two lines, both as appended
errata with the historical sentence left verbatim. The three re-verification counts were not run; the config
gate holds.

Prose note for the record, since `antislop-copywriting` R-02 forbids em dashes outright while this repo's
documentation uses them throughout: house style was kept for consistency with the other ~40 documents in this
tree, and the one `--` that crept into the `ARCHITECTURE.md` erratum was removed because inside a block whose
comment marker *is* `--` it reads as a nested comment. Flagged rather than silently resolved.
