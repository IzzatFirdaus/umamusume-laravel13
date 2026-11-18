# ADR-0035: A run records a hint level per pooled skill, and the ladder prices it

<!-- ADR-CONTRACT
  decision: proposed — run_skills.hint_level stores the hint level a run holds for a pooled skill (1..5 or Lv Max), and the displayed cost is floor(base * (1 - discount)) read from one config ladder
  code: built — migration 2026_11_03_155641_add_hint_level_to_run_skills_table (with down()); RunSkill cast; StoreRunSkillRequest rule; App\Services\HintDiscount; config('uma.skills.hint_discount'); tests/Feature/SkillHintLevelTest.php, Career/BuildTargetHintLevelTest.php
  superseded-by: —
  amended-by: —
  open: the Skills detail page still prints "No table stores a hint level" and "No run stores a hint level"; the copy is stale against this column and no gate catches shipped copy that contradicts a shipped schema
-->

Status: **Proposed.** Drafted 2026-11-18 from shipped code; the column landed at `dcc562b6` (2026-11-03) as part of
a five-slice skills pass with no decision record. **Nothing here binds until the owner records it as Accepted.**

## Context

`ADR-0011` ruled "no hint levels, no discounts" on 2026-09-29, on the belief that the ladder did not exist. Its
2026-10-27 dated amendment corrects that: `UMAMUSUME_REFERENCE.md` §1.1.4 reads the discount ladder off the
`[Global]` Learn screen, so a code path that skipped it was wrong. `config('uma.skills.hint_discount')` and
`App\Services\HintDiscount` now carry the ladder.

That left a gap the config could not close. The ladder prices a skill **at a level**, and nothing stored which
level a run held, so every price was the base price and the planner could not rank a hinted skill against an
un-hinted one.

Two different quantities were also being called a level. A hint level is a support-card-derived discount tier
(1 to 5, then `Lv Max`). A unique-skill level is how far the skill itself has advanced (1 to 6). `ADR-0029` D4 put
the second in `skill_levels`; this decision puts the first in `run_skills`.

## Decision

**`run_skills.hint_level` stores the discount tier a run holds for a pooled skill.** Nullable, because "no hint
recorded" is a real state and must price at base rather than at an assumed tier.

- **The ladder has one home.** `config('uma.skills.hint_discount')` holds the five percentages and the date they
  were measured; `HintDiscount` is the only reader, so the detail page's table, the planner's budget and the build
  target's cost estimate cannot disagree about the arithmetic.
- **The price is integer arithmetic.** `intdiv($base * (100 - $discount), 100)`. The float form drifts: measured at
  180 SP and Lv3, `180 * (1 - 0.3)` evaluates to `125.99999999999999` and floors to 125 where the client prints
  126. `HintDiscount`'s docblock carries that measurement.
- **An unrecognised level leaves the base untouched.** `discountPct()` returns null outside the ladder and `price()`
  returns the base, so a malformed value cannot invent a discount.
- **The two level kinds stay apart.** `StoreRunSkillRequest` accepts `hint_level` 1..5 and `current_level` 1..6 as
  separate fields with separate homes, which is `ADR-0029` D4's boundary made enforceable.
- **The planner prices at the recorded level, not only for priorities.** Any pooled skill with a recorded hint
  prices at its adjusted cost, so a heavily hinted 130 SP skill sorts ahead of an un-hinted 105 SP one.

## What this does not license

- **No hint-pool import.** The support cards' hint pools stay cut (`ADR-0005`, R37). This stores what a Trainer
  entered for a skill the run already holds; it does not import which skills a card offers hints for.
- **No percentage on a skill's own card as a property.** `ADR-0011`'s copy rule stands: a hint belongs to the run
  that holds it, so it renders per run and never as a catalogue attribute.
- **No seventh hint tier.** The ladder is the five the client prints; a sixth value is a new measurement, not a
  guess.

## Links

- `ADR-0029` (D4 keeps the unique-skill level in `skill_levels`; that is a separate quantity from this hint level)
- `ADR-0005` (R37, the support-card hint-pool cut this leaves standing), `ADR-0011` (its 2026-10-27 dated note
  corrected the "no discounts" half this column implements)
- `PRD.md` FR-C-3, FR-D-1, US-4; `UMAMUSUME_REFERENCE.md` §1.1.4
- `config/uma.php` (`skills.hint_discount`, `skills.hint_discount_measured_at`); `app/Services/HintDiscount.php`
- `StoreRunSkillRequest`; `app/Models/RunSkill.php`; `Career/SkillsPlannerController`,
  `Career/BuildTargetController`, `SkillController`
- `tests/Feature/SkillHintLevelTest.php`, `tests/Feature/Career/BuildTargetHintLevelTest.php`,
  `tests/browser/career-skills-planner.spec.ts`

## Ledger

- 2026-11-18 · **drafted** — drafted after the fact. Two of the file's own surfaces contradict it in shipped copy
  (`resources/js/pages/Skills/Show.vue` still says no table stores a hint level, twice); that is recorded in `open`
  rather than fixed here, because it is a copy correction in a different owner's file.
