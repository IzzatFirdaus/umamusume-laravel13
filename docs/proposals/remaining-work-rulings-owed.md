# Remaining work: the eight rulings owed

Recorded 2026-10-24 at `499e0105`. This document presents every ruling the remaining
roadmap depends on, in dependency order, so the owner can rule in one sitting. It decides
nothing: each entry names the options, the evidence, a recommendation, and the observation
that would overturn the recommendation.

Sources of record: `AGENTS.md` §2 (precedence), `DESIGN.md`, `docs/adr/0029` (Proposed),
`SCREEN_SPEC.md` §8, `KNOWN-ISSUES.md` KI-115..KI-130, and this pass's three
investigations (§R1, §R5b, §R6 below).

A ruling is recorded as a dated note beside the KI or document it governs. No row here
waives a gate: a ruling that changes a design decision is still a schema or DESIGN.md
change with its own travel (migration, ADR, digest, PRD citation per §11).

---

## R4 — Accept ADR-0029 (per-run measurement columns)?

**Blocks.** Slices 1.1, 1.3, 1.6, 1.7, 1.8 directly; 1.2 chains on 1.1. Five code slices.

**Source.** `docs/adr/0029-per-run-measurement-columns.md` (drafted at `9c232066`,
`Status: Proposed`; contract `decision: proposed`); KIs KI-116..KI-120, KI-124..KI-126.

**Current state.** The ADR authorises eight per-run storages and builds none:
`deck_slots.card_level` (numerator only), `deck_slots.reached_effects`, `turn_entries`
banner + card avatars, `training_runs.team_member_count`, `team_roster`,
`team_roster_member_skill`, `training_runs.team_stat_grades`, `training_runs.current_spirit`,
`skill_levels`.

**Evidence.** Two rows need a separate answer before they can be migrated, and the ADR
says so itself: `A-0.13` and `A-0.14` are marked `needs-PRD-citation`
(`docs/adr/0029-per-run-measurement-columns.md:43-44`, decision D6 at `:59`, and `:88`),
because `PRD.md` has no story for a stored roster or guest skills. `A-0.5` collides with
US-12's acceptance sentence (`PRD.md:103`, restated at `:53` and `:431`) and is R7.

**Option A — accept as drafted.** Consequence: slices 1.1, 1.2, 1.3, 1.6, 1.7, 1.8 open
once their PRD citation is settled; 1.4/1.5 wait on R8. Cost: five to six slices, one
migration each, digest + PRD citation per §11.

**Option B — accept the six rows that have a PRD anchor.** Consequence: 1.1, 1.2, 1.3,
1.6, 1.7, 1.8 open; `A-0.13`/`A-0.14` stay `blocked: PRD`. Cost: same minus the two
tables, and the 20/20 counter and guest skills remain absent.

**Option C — decline.** Consequence: all eight stay `blocked: ADR`, filed as KIs. The
audit's `needs-schema` rows close as "not stored, by ruling". Cost: none; the source's
facts remain unrecordable.

**Recommendation.** A, with R7 and R8 answered in the same sitting. Those two narrow
specific rows; they do not change the ADR's shape.

**What would settle it.** An owner statement that per-run measurement facts are out of
product scope (C), or that a stored team roster is not wanted (B).

---

## R7 — US-12: narrow its acceptance sentence, or refuse A-0.5?

**Blocks.** Slice 1.1 (`A-0.5`), and through it 1.2.

**Source.** `PRD.md:103` (US-12 acceptance: a run records which six cards it was equipped
with), `PRD.md:53` and `:431` ("no collection, no levels, no limit breaks, no Unique Perk
state"), `docs/adr/0029-per-run-measurement-columns.md:41` (`A-0.5` row, cited to
`FR-C-6, US-12 (travel note)`).

**Current state.** `deck_slots` stores `support_card_id`, `slot_position` and `ownership`;
it has no level. A run can say which card, never what the card was at.

**Option A — dated travel note on US-12.** The sentence's "stored anywhere" narrows to
"account-level collection", and the per-run snapshot is authorised by ADR-0029. Consequence:
1.1 stores `card_level` (numerator only, per ADR-0029 D1) and 1.2 stores reached effects.
Cost: one PRD note plus the ADR.

**Option B — refuse A-0.5.** US-12 stands as written; ADR-0014's collection ban is read as
covering the per-run copy too. Consequence: 1.1 and 1.2 close as declined; the deck analysis
keeps catalog maxima for every run. Cost: none.

**Option C — store the level without citing US-12.** Consequence: §11 is violated, because
every schema change carries a PRD citation. Not viable as written; listed so the refusal is
explicit.

**Recommendation.** A. The per-run reading ("what this run's equipped card is at") is what
the audit asked for, and it creates no collection entity that ADR-0014 declined.

**What would settle it.** An owner reading of US-12 as deliberate about the run, not the
account; or an ADR-0014 erratum extending the collection veto to per-run copies.

---

## R8 — A-0.13 / A-0.14: write the PRD story, or refuse?

**Blocks.** Slices 1.4 (stored roster, 20/20 counter) and 1.5 (guest skills).

**Source.** `docs/adr/0029-per-run-measurement-columns.md:43-44,59,88`; `PRD.md` §6
non-goals; KI-125, KI-126.

**Current state.** No scenario data stores a team roster; the 20/20 counter in the client
counts a roster the tool does not hold (`TeamPanelBuilder` derives six deck cards, not
eighteen members). Guest skill lists are neither stored nor derivable.

**Option A — one PRD story for the team roster.** Covers both rows: the roster and its
per-member skills. Consequence: 1.4 and 1.5 open; the counter and the roster editor ship.
Cost: one PRD story, two migrations, two ADR rows already drafted.

**Option B — refuse both.** The counter keeps its named absence and roster skills stay
unrecorded. Consequence: KIs close as declined. Cost: none.

**Option C — roster only, no skills.** Consequence: 1.4 ships, 1.5 stays blocked. Cost:
one PRD story, one migration; guest skills remain absent.

**Recommendation.** A if KI-130's dead-registration ruling composes the team panels; B
otherwise. A roster editor with no composed surface is storage without a screen.

**What would settle it.** The KI-130 composition ruling (compose or retire the five
registrations). It is the same surface.

---

## R3 — The Scenario-region focusable ruling: amend, restore, or retire?

**Blocks.** Slice 2.6; closes the last two reds in `tests/browser/unity-cup-panel.spec.ts`
(`:134`, `:229`).

**Source.** `e5768c98` commit body (2026-10-11 ruling: "no disclosure and no focus stop
anywhere in the Scenario region"; lifting needs "the owner's written overrule");
`DESIGN.md:768-777` (§5(a), amended 2026-10-17, owner instruction of the same day);
`2b82f043` (implemented §5(a) at `resources/js/components/scenario/ScenarioPanel.vue:243`);
`tests/Feature/UnityCupSurfacesTest.php:284` (executable form scans only the two Unity Cup
panel files).

**Current state.** The Scenario region renders 13 focusable summaries and one `<details>`,
all from `AbsenceValue` at `ScenarioPanel.vue:243` and the scenario panels that compose it.
The spec still asserts zero for the whole region; `UnityCupSurfacesTest` is green because
it never scans the wrapper.

**Option A — amend the ruling.** Add one sentence to `DESIGN.md` §5(a): the disclosure
channel is permitted in the Scenario region. Consequence: spec narrows to the two panels in
the same commit. Cost: one DESIGN.md amendment (owner), one spec line each, no component
change.

**Option B — restore the 2026-10-11 ruling.** `AbsenceValue` gains a static mode and every
in-region caller migrates (`ScenarioPanel.vue:243`, `TrackblazerRoutePlanner.vue:185`,
`UraPreFinalReview.vue:106,136,161`). Consequence: `AbsenceChannelTest`'s ratchet and the
2026-10-17 amendment both need a dated exception. Cost: one new prop, five call sites, one
DESIGN.md note, one test's expectations.

**Option C — retire the ruling.** Spec narrows to the panels; a dated note says the
region-wide guard is retired. Consequence: same end state as A without the DESIGN.md
sentence. Cost: one spec change, one note; leaves §5(a)'s scope unstated for this region.

**Recommendation.** A. It is the only option that makes the written rule and the tree agree
without deleting either.

**What would settle it.** An owner statement that the 2026-10-17 amendment was meant only
for new surfaces, which makes B mandatory.

---

## R1 — S2b payload shape: does a ceilings-only save store a target?

**Blocks.** Slice 2.4; also covers R4-31's symptom.

**Source.** `app/Http/Requests/StoreBuildTargetRequest.php:71-82` (all-or-nothing
`$presence`), `:160-170` (`isEmptyTarget()`), `:186-217` (`payload()`),
`resources/js/pages/Career/BuildTarget.vue:126,175`.

**Evidence (this pass, code-proven).** `isEmptyTarget()` checks only `purpose`, `distance`,
`surface`, `style` and the five target values; ceilings and `skill_priorities` are ignored.
A save carrying ceilings plus priorities therefore validates (ceilings have their own
rules) and returns `payload() === null`: the priorities are never written. The form echoes
whatever the stored target holds (`BuildTarget.vue:126`) and always posts the key
(`:175`), and the request requires it present (`:81`). So the observed loss is **silent**,
not a 422: the first save stores no target, and the next save posts an empty list. R4-31
and R4-03 are one mechanism.

**Option A — one partial payload (S2b B1).** `payload()` returns a partial object when
ceilings or priorities were entered; Preflight renders `Partial` instead of `Missing`; the
cockpit renders what is present. Consequence: 2.4 opens and R4-31 closes with it. Cost:
three controllers/services, three feature cases, one browser case.

**Option B — two draft keys.** `build_target` stays all-or-nothing; `caps_and_priorities`
becomes independent. Consequence: same user outcome, two shapes to read. Cost: every
downstream consumer learns which key carries what.

**Option C — copy-only refusal.** `Save target` refuses without saying why; Preflight's
`Missing` is correct. Consequence: no code change; entered ceilings still discarded on run
creation. Cost: none, and the loss remains.

**Recommendation.** A.

**What would settle it.** An owner statement that a target without a purpose is genuinely
not a target (C), which would make the loss correct rather than a defect.

---

## R2 — S11: one copy sentence naming where Team Rank is recorded?

**Blocks.** Slice 2.5. One sentence.

**Source.** R4-13 (`docs/audits/rice-shower-unity-cup-round4-reconciliation.md`);
`resources/js/components/career/UnityCupEditor.vue` help text; `TrainingRun` records rank
through a turn's team-race event.

**Current state.** The editor offers no rank input and does not say where a rank comes
from, so a Trainer who reads the client's rank has no path from this panel.

**Option A — copy-only.** One sentence: "Team Rank is recorded through a turn's team-race
event, not through this editor." Consequence: 2.5 ships with one assertion. Cost: one line,
one test.

**Option B — copy plus a shortcut.** A link to the Events screen or the turn form, with the
rank field pre-scoped. Consequence: a navigation change and a new control. Cost: one link,
one browser case, and a decision about which surface owns rank entry.

**Option C — leave it.** The KI stays open.

**Recommendation.** A. The rank already has an owner; the defect is that the editor is
silent about it.

**What would settle it.** An owner preference for entering rank from the editor itself,
which would make B insufficient and require a real input.

---

## R5b — `.card + .card`: fix the spec measurement, exempt grids, or accept?

**Blocks.** Slice 3.1 (one pre-existing red in `career-deck-select.spec.ts`).

**Source.** `resources/css/app.css:678-681` (`.card + .card { margin-top: 14px }`, ported
from `shell.css`); `tests/browser/career-deck-select.spec.ts:23-36` (counts children whose
`getBoundingClientRect().top` equals the first child's).

**Evidence (this pass).** The deck grid computes three columns at desktop. The margin rule
applies to every sibling `.card` inside the grid, so children 2..n sit 14px lower than the
first even though they share a row; the spec's equal-top test then reports one per row.
The rule is a stacked-card house rule, not a grid rule.

**Option A — fix the spec.** Count row membership by left offset or by the computed
`gridTemplateColumns` tracks. Consequence: 3.1 closes with a spec-only diff. Cost: one
assertion.

**Option B — exempt grids from the rule.** Scope the margin to non-grid parents. Consequence:
the visual gap between stacked cards is unchanged and grids no longer carry a phantom
margin. Cost: a CSS selector change; the stylesheet is a design surface, so this is Tier C
and needs the same ruling.

**Option C — accept as a KI.** Record the assertion as a known red.

**Recommendation.** A. The rule is intentional and documented as a stacked-card rule; the
measurement is what is wrong.

**What would settle it.** A visual complaint about the extra 14px inside grids, which would
make B the honest fix.

---

## R6 — Inertia DevTools: disable for the browser role, or mitigate?

**Blocks.** Slice 3.3.

**Source.** `vendor/inertiajs/inertia-laravel/config/inertia.php:170-184`;
`vendor/inertiajs/inertia-laravel/src/DevTools/DevTools.php:16-25`;
`storage/inertia-devtools/` (exists).

**Evidence (this pass).** The provider ships inside **`inertiajs/inertia-laravel` 3.5.1**
(auto-discovered via `extra.laravel.providers`), not a separate package. `config/inertia.php`
is **not published**, but the package config merges, and the gate is
`inertia.devtools.enabled`, read from **`INERTIA_DEVTOOLS_ENABLED`**; when unset it falls
back to `app()->environment('local')`. Storage path and TTL are config keys
(`inertia.devtools.path`, `ttl`, `prune_interval`).

**Option A — env-gated disable.** Set `INERTIA_DEVTOOLS_ENABLED=false` in the browser
harness environment. Consequence: 3.3 closes with no code. Cost: one env line in the
harness (and `.env.example` documentation).

**Option B — publish the config and set the flag.** `php artisan vendor:publish` plus
`devtools.enabled => false` for the harness. Consequence: same behaviour, a tracked config.
Cost: one config file; more surface than A.

**Option C — mitigate on recurrence.** Keep recording and delete `storage/inertia-devtools`
when it grows. Consequence: the `/up` probe can still time out during a long browser run.
Cost: an operational note only.

**Recommendation.** A. The package already reads an env var; nothing needs publishing.

**What would settle it.** A need to inspect recorded entries while debugging the browser
role, which would make B (config with an override) preferable to a hard env switch.

---

## Ruling record

| # | Question | Recommendation | Recorded? |
|---|---|---|---|
| R4 | Accept ADR-0029? | A: accept as drafted | no |
| R7 | Narrow US-12 or refuse A-0.5? | A: dated travel note | no |
| R8 | PRD story for the roster, or refuse? | A if KI-130 composes; else B | no |
| R3 | Amend, restore, or retire the region rule? | A: amend §5(a) | no |
| R1 | One partial payload, two keys, or refuse? | A: one partial payload | no |
| R2 | Copy-only, copy plus shortcut, or leave? | A: copy-only | no |
| R5b | Fix the spec, exempt grids, or accept? | A: fix the spec | no |
| R6 | Disable by env, publish config, or mitigate? | A: env gate | no |

Each ruling is recorded as a dated note beside its governing KI or document; this table is
then updated with the date, not rewritten.
