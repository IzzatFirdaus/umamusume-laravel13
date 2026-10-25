# Group A + Group B — consolidated implementation plan

**Date.** 2026-10-25. **Supersedes as the work order:** `docs/proposals/group-ab-plan.md` (kept as the
record, not edited). **Verified against:** HEAD `92cdfec6`.

## 1. Sources

| Document | Role |
|---|---|
| `docs/audits/group-ab-audit-2026-10-24.md` | 19 classified items + the 2026-10-24 addendum (`A-0.12`..`A-0.14`) |
| `docs/proposals/group-ab-plan.md` | original work order, owner rulings, classification deltas |
| `docs/audits/group-ab-pass-2026-10-24.md` | what actually landed, with SHAs and gate output |
| `docs/proposals/group-ab-rulings-owed.md` | the two-option write-ups for every blocked item |
| `docs/adr/0029-per-run-measurement-columns.md` | **`Status: Proposed`** — authorises the eight per-run storages, builds none |
| `docs/proposals/remaining-work-roadmap.md` | the 2.1.1→2.2.0 roadmap; Phase 1 here maps onto its Phase 1 |
| `docs/proposals/remaining-work-rulings-owed.md` | `R1`..`R8`, the rulings the roadmap depends on |

Item states at this HEAD: **landed 7** (`A-0.1`, `A-0.6`, `A-0.8`, `A-0.10`, `A-0.11`, `B-0.2`, `B-0.4`) ·
**closed, does not reproduce 2** (`B-0.1`, `B-0.3`) · **ruled out 2** (`A-0.2`, `B-0.8`) ·
**pre-existing landed 3** (`A-0.3`, `A-0.7`, `A-0.9`) · **blocked on ADR acceptance 8** (`A-0.4`, `A-0.5`,
`A-0.12`, `A-0.13`, `A-0.14`, `B-0.5`, `B-0.6`, `B-0.7`).

Every "landed" row below was re-verified in the tree at this HEAD: the controller sections, the config
key, the feature test files, and the browser cases all exist and were read, not assumed.

## 2. Phase 1 — Audit and planning (completed)

Commit `18d6f6f1` landed the audit, the plan, and the rulings-owed document. 19 items classified
(11 Group A, 8 Group B). No source file touched. Owner rulings of 2026-10-24 then moved six rows; the
audit document is the record and was **not** edited — the deltas live in the plan's own delta table.

Addendum at `04c16a22` (commits `525ab4de`, `9fe4a451`, `45fee9ae`) added `A-0.12`..`A-0.14` as
`needs-schema` and recorded a fourth candidate as not reproducing (the green-factor unique-skill spark is
writable through the typed `Skill` select, `LegacySelectController.php:127-131`).

**Done. No action.**

## 3. Phase 2 — Openable Group A slices (completed)

All five landed. SHA, file, and pinning test re-verified.

| Slice | Item | SHA | What landed | Pinned by |
|---|---|---|---|---|
| 2.1 | `A-0.10` Top Star 240,000 threshold | `f748a378` | `fanSuffix` renders `class · gap · nextThreshold` (`CareerStatePanel.vue:88-93`) | `career-cockpit.spec.ts:699` "names the fan ladder threshold beside the gap" |
| 2.2 | `A-0.1` Cap composition read-out | `92a3d773` | `capCompositionSection()` (`CockpitController.php:443`) + `capComposition` prop (`:192`); panel names the §1.2 non-reconciliation | `CockpitCapCompositionTest`; `career-cockpit.spec.ts:715` |
| 2.3 | `A-0.8` Purchasable pool with prices | `5f359aae` | `purchasableSection()` (`SkillsPlannerController.php:94`) + prop (`:47`); priced, unacquired, sorted, with sum and SP shortfall, capped at the 100 cheapest | `CareerSkillsPlannerTest`; `career-skills-planner.spec.ts:285` |
| 2.4 | `A-0.6` Derived roster read-out | `d6c30475` | `rosterSection()` (`CockpitController.php:511`) + `roster` prop (`:194`); six deck cards, no stored roster | `CockpitRosterTest`; `career-cockpit.spec.ts:728` |
| 2.5 | `A-0.11` Mood Effect percentages | `d8aaeb27` | `config('uma.mood_effect')` = training 20 / running 4 (`config/uma.php:497-507`), one sourced figure, no per-tier table | `CockpitMoodEffectTest`; `career-cockpit.spec.ts:739` |

Note on 2.4: the audit classified `A-0.6` `needs-schema`; the owner's ruling ("derived read-out, no
column") is what made it openable. The roster editor stays declined — `A-0.13` and `A-0.14` are the
residue that ruling left behind.

**Done. No action.**

## 4. Phase 3 — Group B slices (completed / closed)

| Slice | Item | SHA | Outcome |
|---|---|---|---|
| 3.1 | `B-0.2` Cleared-goal recording | `f20ea1db` | Race-drawer "Record completed race" writes `Completed` + `placement`; existing `objectiveMet()` derivation picks it up. No new column. `CockpitGoalFromCompletedRaceTest`; `career-race-decision.spec.ts:186` |
| 3.2 | `B-0.4` Scheduled-race state | `7466cf4d` | "Scheduled, not yet run." line on an un-entered calendar race card. No enum change, no new state. `career-race-decision.spec.ts:178` |
| 3.3 | `B-0.1` Scenario mandatory races | — | **Closed, does not reproduce.** `docs/scenarios/09-global-race-calendar.md:105,632-633` — the three URA Finals rounds are shared across scenarios and only the Final forks (Unity Cup's is Aoharu). The ruled filter would have deleted legitimate shared obligations. `KI-122` |
| 3.4 | `B-0.3` Pending-turn + date | — | **Closed, does not reproduce.** `Snapshot/Setup.vue:165-186` already renders Year/Month/Phase; `CreateSnapshotRun:58-79` writes `career_position`. `KI-123` |

Both closures are dated notes in `KNOWN-ISSUES.md`, not silent drops.

**Done. No action.**

## 5. Phase 4 — Schema-blocked slices (blocked on ADR acceptance)

Five items. All five now sit under **one** ADR: `docs/adr/0029-per-run-measurement-columns.md`, drafted
`9c232066`, **`Status: Proposed`**. The agent drafts; the owner accepts. Nothing in this phase opens until
that status flips.

| Slice | Item | KI | Storage authorised by ADR-0029 | Extra ruling |
|---|---|---|---|---|
| 4.1 | `A-0.5` Card client levels | `KI-117` | `deck_slots.card_level` — integer, nullable, **numerator only** | `R4` + `R7` (US-12 travel note) |
| 4.2 | `A-0.4` Reached effect values | `KI-116` | **none** — computed from `deck_slots.card_level` × catalog anchors (D2) | `R4`, and only after 4.1 |
| 4.3 | `B-0.5` Team stat grades | `KI-118` | `training_runs.team_stat_grades` — json or enum | `R4` (+ FR-C-1 travel note) |
| 4.4 | `B-0.6` Current Spirit gauge | `KI-119` | `training_runs.current_spirit` — integer, nullable; reading only, no max (D3) | `R4` (+ FR-C-1 travel note) |
| 4.5 | `B-0.7` Skill levels | `KI-120` | `skill_levels` — run × skill → level integer (D4) | `R4` (+ FR-C-3 travel note) |

**Gating rulings.**
- **`R4`** — accept ADR-0029. Blocks all five.
- **`R7`** — `US-12` reads "no card level, limit break or Unique Perk state is stored anywhere". A per-run
  level *is* a card level stored somewhere, so 4.1 needs a dated travel note narrowing that sentence to the
  collection reading, or the two requirements contradict each other on the page.
- `R4` and `R7` are written up in `docs/proposals/remaining-work-rulings-owed.md`.

**Per-slice schema bundle (§11, non-negotiable):** PRD citation · `ARCHITECTURE-ESSENTIALS.md` digest line ·
the ADR · a working `down()`. One migration, model/request edits, one Vue surface, one feature case, one
browser case where the value is user-entered. `migrate:status` clean on the dev database afterwards.

**Test per slice** (from the roadmap, kept): `DeckSlotCardLevelTest`, `DeckAnalysisReachedEffectsTest`,
`CockpitTeamPanelTest` case, `CockpitUnityCupCountersTest`, `SkillsPlannerLevelsTest`.

**Phase close:** close `KI-116`..`KI-120` with a dated note naming each commit; `VERSION.md` to `2.2.0`
(MINOR — new schema and surfaces).

## 6. Phase 5 — Addendum slices (blocked on ADR + PRD citation)

| Slice | Item | KI | Storage | Extra ruling |
|---|---|---|---|---|
| 5.1 | `A-0.12` Training-tile banner + card avatars | `KI-124` | `turn_entries.facility_banner` (string) and `turn_entries.training_card_ids` (list or join table) | `R4` (+ FR-C-2 travel note) |
| 5.2 | `A-0.13` 20/20 members counter | `KI-125` | `team_roster` table | `R4` + **`R8`** — no PRD anchor |
| 5.3 | `A-0.14` Guest skill lists | `KI-126` | `team_roster_member_skill` table | `R4` + **`R8`** — no PRD anchor |

**`R8`** is the hard one. `PRD.md` `FR-C` has no roster object at all, so ADR-0029 marks both roster items
`needs-PRD-citation` (D6). The owner either writes the PRD story or refuses the two items; nothing is built
in the meantime. The recommendation on file is "write it if `KI-130` composes" — the five dead scenario
registrations ruling and the roster story are settled together.

`A-0.13` counts a stored roster the tool does not hold; the landed `A-0.6` roster is six deck cards, not
eighteen. That is why 5.2 cannot ride 2.4.

Tests: `TurnEntryTileMetadataTest`, a `CockpitTeamPanelTest` case, `CockpitRosterTest`.

## 7. Phase 6 — Ruled out and pre-existing landed (no action)

| Item | Disposition |
|---|---|
| `A-0.2` Run-stored aptitudes | **Ruled out** — catalog-only, D8 verified the catalog reading correct. `KI-115` |
| `B-0.8` Borrowed-card owner | **Ruled out** — a loopback, single-Trainer tool has no second account identity. `KI-121` |
| `A-0.3` Owned-skill inventory | Landed `fbd37bdd` |
| `A-0.7` +30 ranking bonus | Landed `6a74f857` |
| `A-0.9` Burst band position | Landed `5eba0d01` |

The three pre-existing landings are recorded with their SHAs and nothing is re-litigated.

## 8. Verification gates

**Phase 2 gate (already run, quoted in the pass report).** `npm run typecheck` → exit 0.
`php artisan test --compact` on the six touched/nearby feature files → 77 passed (1002 assertions).
`npm run build` → built. Browser: two cases failed on first run (a `·` encoding assertion; one host-load
timeout, not an assertion), both re-ran focused and passed. Pint `--test` passed on every changed PHP file.

**Phase 3 gate.** `php artisan test --compact` on `CockpitGoalFromCompletedRaceTest`,
`CareerRaceDecisionTest`, `CockpitObjectivesTest`, `CockpitTeamPanelTest`, `CockpitRosterTest` →
39 passed (662 assertions). Typecheck exit 0. Build built. Both browser cases passed.

**Phase 4/5 gates (not yet runnable — no slice open).** When they open:
`vendor/bin/pint --test --format agent <explicit paths>` (never `--dirty`, KI-75) ·
`vendor/bin/phpstan analyse` scoped to the changed paths · `npm run typecheck` · `npm run build` ·
focused Pest per slice · focused browser specs per slice · `php artisan migrate:status` clean.

**Register gate.** `grep -cE "^### KI-" KNOWN-ISSUES.md` → **71** at this HEAD (55 at plan time; 64 after
Phase 4's nine entries; 71 after the addendum's three and the capsule pass's four). Phase 4 close adds no
new entries — it closes `KI-116`..`KI-120` — so the count is expected to hold at 71.

**Close-out.** `CHANGELOG.md` entry at the release cut; pass report under `docs/audits/`; tag at the
changelog commit. **Owner pushes and moves tags; no agent does either.**

## 9. Corrections this consolidation makes

Three places where the inputs disagree or the original plan misstated a fact. Each is reported, not
silently resolved.

1. **`A-0.4` storage shape — real conflict, unresolved.** ADR-0029 **D2** states A-0.4 stores *nothing* and
   is computed from `deck_slots.card_level` and the catalog anchors, and its decision table gives storage
   "none — computed from `deck_slots.card_level` and `support_effects`". `remaining-work-roadmap.md` slice
   1.2 instead lists `deck_slots.reached_effects` (JSON) with `DeckAnalysisReachedEffectsTest`. These are
   different designs: a derived read-out versus a stored blob. The ADR is the higher authority (accepted
   ADR > proposal), so §5 above follows D2 — but the roadmap row is wrong and should be corrected when `R4`
   is ruled. **Owner input needed** if the intended shape is the stored column.
2. **Addendum column names.** The original plan says `facility_banner` / `training_card_ids`; the roadmap
   says `tile_banner` / `tile_card_avatars`. ADR-0029 D5 and its table use `facility_banner` /
   `training_card_ids`, so §6 follows the ADR. Same for `A-0.13`: the roadmap offers
   `training_runs.team_member_count*` *or* `team_roster`; ADR-0029 rules `team_roster`, so that is the shape.
3. **Browser spec filenames.** The original plan names `career-cockpit-forms.spec.ts` for the Phase 2 cases.
   They actually live in `career-cockpit.spec.ts` at lines 699, 715, 728, 739. `career-cockpit-forms.spec.ts`
   exists and is a different file. The Phase 2 gate must run `career-cockpit.spec.ts`.

Also corrected: the original plan's Phase 4 gate command was malformed (`grep -cE KI-" KNOWN-ISSUES.md`);
the working form is `grep -cE "^### KI-" KNOWN-ISSUES.md`.

## 10. What this plan does not open

- **S2b (`R4-03`)** and **S11 (`R4-13`)** — separate slices on the roadmap as 2.4 and 2.5, blocked on `R1`
  and `R2`. Not in scope here.
- **The collection** — `ADR-0014` declines `user_support_cards` (level, breaks, perk). Only the per-run
  reading is authorised, and only as the numerator. A slice wanting the cap or the limit-break count
  returns for a new ruling.
- **Aptitudes, hint pools, win probability** — `ADR-0004`, `ADR-0005` R37, `ADR-0016` all stand unchanged.
- **`KI-127`..`KI-130`** — the scenario-panel, delete-placement, capsule-uniqueness, and dead-registration
  items belong to the roadmap's Phases 2–4, not to Group A/B.

## 11. Definition of done

- **Phase 1** — done; audit, plan, and rulings-owed exist.
- **Phase 2** — done; five commits, each with a feature test and a browser case.
- **Phase 3** — done; two landed, two closed by dated note.
- **Phase 4** — every one of the five is either landed with its schema bundle and a green focused gate, or
  still filed open against `KI-116`..`KI-120`. No item is closed without a commit SHA.
- **Phase 5** — same, plus the PRD citation that `R8` must supply first.
- **Phase 6** — nothing to do; the dispositions are already recorded.

## Appendix — item → KI → phase index

| Item | Group | State | KI | Phase |
|---|---|---|---|---|
| A-0.1 | A | landed `92a3d773` | — | 2 |
| A-0.2 | A | ruled out | `KI-115` | 6 |
| A-0.3 | A | landed `fbd37bdd` | — | 6 |
| A-0.4 | A | blocked: ADR | `KI-116` | 4 |
| A-0.5 | A | blocked: ADR | `KI-117` | 4 |
| A-0.6 | A | landed `d6c30475` | — | 2 |
| A-0.7 | A | landed `6a74f857` | — | 6 |
| A-0.8 | A | landed `5f359aae` | — | 2 |
| A-0.9 | A | landed `5eba0d01` | — | 6 |
| A-0.10 | A | landed `f748a378` | — | 2 |
| A-0.11 | A | landed `d8aaeb27` | — | 2 |
| A-0.12 | A | blocked: ADR | `KI-124` | 5 |
| A-0.13 | A | blocked: ADR + PRD | `KI-125` | 5 |
| A-0.14 | A | blocked: ADR + PRD | `KI-126` | 5 |
| B-0.1 | B | does not reproduce | `KI-122` | 3 |
| B-0.2 | B | landed `f20ea1db` | — | 3 |
| B-0.3 | B | does not reproduce | `KI-123` | 3 |
| B-0.4 | B | landed `7466cf4d` | — | 3 |
| B-0.5 | B | blocked: ADR | `KI-118` | 4 |
| B-0.6 | B | blocked: ADR | `KI-119` | 4 |
| B-0.7 | B | blocked: ADR | `KI-120` | 4 |
| B-0.8 | B | ruled out | `KI-121` | 6 |

**Ledger.** 2026-10-25 · consolidated at HEAD `92cdfec6`. Absorbs the audit, the original plan, the pass
report, the rulings-owed document, ADR-0029's `Proposed` decision table, and the remaining-work roadmap.
No source file, test, migration, config, or ADR was edited; this document is the only file written.

## Dated addition - 2026-10-25 - Phase 1 residuals are not blocked, and were already landed

Section 5's framing ("all five sit under one ADR") is true of the **schema** set and was read as if it covered
all remaining work. It does not. Two distinct sets exist, and only the second is blocked:

| Set | Blocked on | Items |
|---|---|---|
| Phase 1 residuals | Nothing | none outstanding - see below |
| Schema work | ADR-0029 acceptance (R4) + R7 + R8 | A-0.4, A-0.5, A-0.12, A-0.13, A-0.14, B-0.5, B-0.6, B-0.7 |

**Phase 1 residuals were proposed as six open slices and verified as already landed.** Each proposed file
path matched an existing R4 Phase 1 commit exactly, so nothing was re-implemented:

| Proposed slice | Found at | Result |
|---|---|---|
| S1-residual, run id in cockpit header (R4-01) | `9f16ebde` | landed |
| S5, deck builder career nav (R4-29) | `c6d12986` | landed |
| S7a, deck picker ownership copy + filter in place (R4-20, R4-21) | `8e5a9ddc`, `4d048fb0` | landed |
| S7b, trainee picker forward control + query preservation (R4-22, R4-23) | `cdb1a136`, `3cdc466c` | landed |
| S7c, facility levels above cards (R4-26) | `3972460a` | landed |
| S8-residual, completed-race write path (R4-07) | `f20ea1db` | landed - this is Phase 3's B-0.2 |

Two items remain genuinely open, and neither is a Phase 1 residual:

- **S11 / `R4-13`, team-rank owner copy.** No commit carries `R4-13`; the copy line is still unlanded and is
  gated on ruling **R2** (copy-only vs copy plus shortcut), per the remaining-work roadmap slice 2.5.
- **`R4-31`, S2b priorities on re-save.** No code commit. The roadmap records it as evidence-only, closing
  with **R1**'s slice 2.4 rather than as its own fix. Not openable without that ruling.

Section 10's exclusion of S2b and S11 stands. What changes is the reason: they are excluded because they are
gated on R1 and R2, **not** because ADR-0029 blocks them.

**ADR-0029 patched at 2026-10-25** in line with the three rulings: A-0.4's prerequisite corrected (the anchor
vector is stored and already read by `SupportCardEffects`; what is missing is a level-parameterised reader,
not a table), the ADR's names confirmed against the roadmap's stale alternates, and the spec-file and
register-gate corrections recorded. The ADR remains `Proposed`; R4, R7 and R8 are still outstanding.

**2026-10-25 — R8 ruled B, A-0.13/A-0.14 declined.** The owner's ruling on KI-130 (`3e81adf6`, retire
the four URA-only registrations) sets the R8 conditional to **B**: with no composed team-panel surface,
a roster editor and guest skill lists have nowhere to live. A-0.13 (`KI-125`) and A-0.14 (`KI-126`)
remain permanently blocked. The `US-16 (proposed)` draft that would have authorised them under branch A
is recorded here as the historical alternative, not a work item:

> As a Trainer, I record the team a Unity Cup run fielded and what each member was worth…

*[Gap recorded 2026-10-25: only this opening sentence survives in this tree. The full D.2 US-16 draft was
not found in tracked files, any ref (`git log -S`), either worktree
(`.kilo/worktrees/almondine-plywood`, `.kilo/worktrees/cherry-pet`), or `.scratch-uma`. Paste the full
draft here if it is recovered; this fragment is the record that it existed and what it was for.]*

To un-decline: the ruling would need to reverse KI-130 back to compose, and a roster editor would need a
composed `team_roster` panel registration and PRD §6 story. That is a new pass, not a reopen of this
one.

**2026-10-25 (later) — superseded.** R4 landed at `f3ca8813` (ADR-0029 accepted). R7 landed at `06d9515e` (US-12 narrowed). R8 landed at `c734a280` (A-0.13/A-0.14 declined, KI-130 retire). None are outstanding.