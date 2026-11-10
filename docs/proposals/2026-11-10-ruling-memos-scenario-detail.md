# Ruling memos — scenario detail remodel

**Status:** decision memos. Four questions, each with measured options and their costs. **No ruling is
made here and nothing is implemented.** Every figure below was measured on this tree at `9defc83b`
(plus the Workstream C commits that follow it), and each carries the command or `file:line` that
produced it.

**Why this file exists.** The remodel plan (`docs/proposals/2026-11-10-scenario-detail-remodel.md`,
`28d9afbf`) priced four moves. One landed (Move 3). The other three stop at questions only the owner
can settle, and `Ruling 1` is a question the plan deliberately left open. These memos reduce each to a
menu with costs so the ruling is cheap to make.

**Read order if you want only the decision:** §W.1 is the narrowest (one card, one header) and affects
today's rendering; §W.2 and §W.3 are the two forks of Ruling 3 and gate the whole reflow; §W.4 is
about the caps block and does **not** gate anything today.

---

## W.1 — Ruling 1: capsule chrome on the merged Move 3 card

### What changed, and why this is now a concrete question

Move 3 merged `SpiritBurstRoster` into the `Burst count bands` card. Before the merge the roster
carried its own `CapsuleHeader title="Spirit Burst"`. The merged card therefore has **one card holding
two topics**, and something has to decide how many capsule headers it carries.

Measured on the tree:

- `CapsuleHeader.vue` is the **only place the lime `lattice-bleed` chrome is defined** (`grep -rln
  lattice-bleed resources/js/` returns that one file), while **9 components import `CapsuleHeader`** —
  so the chrome is a shared primitive whose call sites are the panels. The survey counted up to **14
  capsules on one Unity Cup board** and **zero non-scenario pages** wearing them
  (`docs/research/scenario-detail-design-language-survey-2026-11-09.md` §2–§3).
- `DESIGN.md:497` sanctions the chrome: *"Argyle lattice bleed | research §6.3 | capsule headers | The
  client's most repeated element … It identifies a header as **chrome** rather than as data."*
- `DESIGN.md:500-502` adds two limits: a capsule always carries a word, and its fill stays on the
  accessible chrome step — never the client's bright lime, where white measures 1.99:1.
- **`DESIGN.md` says nothing about a card that carries two topics.** Its inventory treats the capsule
  as a per-panel header. That silence is the whole question.

### The options

| | Option | What it looks like | Cost |
|---|---|---|---|
| **A** | **One capsule** — the bands' header survives; the roster becomes a labelled sub-block (`.sublabel`) inside the card | What landed at `64c37c4f` | **No redraw.** Status quo. Every HFW render already shows this. |
| **B** | **Two capsules** — bands capsule, then a roster capsule stacked inside the same card | Two headers in one card | **Redraw** the HFW's merged card + one code change (`bare` → two children). Costs a card with two competing headers. |
| **C** | **No capsule on this card** — plain `h2`, matching the HFW's wider plain-heading design | One `h2`, one `.sublabel` | **Redraw + broader propagation question.** The HFW already draws this; the *app* would need a change, and the moment one card drops its capsule you have answered Ruling 1 for the page in practice. |

### Evidence, and what it does and does not settle

- **A is the only option that needs no new ruling to keep**: it preserves the sanctioned chrome
  (`DESIGN.md:497`), keeps one header per card (the convention every other panel follows), and is
  already what the app renders and what the HFW draws.
- **C cannot be decided at card scope.** It answers "chrome headers → plain headings for the whole
  page", which is exactly what the HFW's annotation claims as its one visual decision and what the
  survey measured as this page's single divergence. Picking C for one card while 13 others keep
  capsules would make the page internally inconsistent — `GT-4`/`SHR-1` territory.
- **B is the only option that breaks a convention for the sake of keeping a heading.** It preserves
  both words but produces a card with two titles, which no other card on the page does.

### Recommendation flag

**Flagged: A, on measured grounds.** It is the only option that requires no redraw, matches the
chrome the design system explicitly sanctions, and keeps the one-header-per-card convention that every
other panel already follows. That is not a close call on cost.

**But the memo cannot settle Ruling 1 itself**, because the real question is larger than this card:
*does this page keep capsule chrome at all?* If the owner answers "plain headings page-wide" (option C
propagated), then A becomes wrong for this card retroactively. So this memo reduces to:

> **A now (free, status quo). The page-wide capsule question is the one worth ruling, and it is not
> gated by this card.**

---

## W.2 — Ruling 3, fork 1: is the page's primary the team state, or the run state?

### Where "primary" would come from — the evidence

This is the crux, and the tree is quieter than expected. Measured:

| Source | What it says | Does it name a primary? |
|---|---|---|
| `ScenarioDetail.vue:58` | `PageHeader eyebrow="Scenario" title="Scenario detail"` | **No.** The title names the *scenario*, neither subject. |
| `ScenarioDetail.vue:40-41` | lede: *"The **boards** `{scenario_label}` composes for this run. Every panel reads what the run recorded and names what it has not."* | **No, and pointedly so.** It says "boards", plural — the page's subject is *the set of panels*, not one of them. |
| `SCREEN_SPEC.md:2781` | the strip's four states | **No.** Describes the strip, not panel priority. |
| Panel order in `UnityCupPanel.vue` | Team Race → Team → Spirit → Team Rank → progress → grades → bands → Special Training → coverage → shape | **Implicitly team-first**, but the file's own comment says the order is "the owner's order", i.e. a past choice, not a derivation. |

**So the corpus does not settle it.** There is no document that says "this page leads with team state"
or "leads with run state". The plan's Move 1 assumes team-primary without citing a source — that
assumption is stated here rather than inherited.

### What each choice produces

| | Team-primary | Run-state-primary |
|---|---|---|
| **Consequence for Move 1** | Regroup **within the current order**: team panels contiguous in rows 2–4, run-state 5, trainee 6–7. Panel *sequence* barely moves — only gaps close. | **Reorder**: some run-state panels (burst bands, Spirit Burst, Special Training) promote above team panels. The page's reading order changes. |
| **Risk** | Low, mechanical. The order is already team-first, so this mostly closes holes. | Higher: a real re-order, and one the current HFW would not depict. |
| **What it would claim** | "This page is about the run's team; the scenario facts are reference." | "This page is about the run's progress; the team is one board among several." |
| **Evidence for** | The existing order already does this. `GRID-REVIEW.md` measured the team block as 6 of 11 panels. | The page is titled *Scenario detail* and reached from the scenario door; a reader arriving here may want progress, not roster. |

### Measured facts that bear on it

- The team block is **6 of the 11 panels** (`GRID-REVIEW.md` §P1) — a majority by count.
- The team panels are the ones with `v-if` data gates (Team, Spirit, Unity Cup progress, Special
  Training): on a sparse run **only 7 panels render at all**, and the team block shrinks to 3.
- The run-state panels (bands, coverage, shape) render unconditionally where the scenario composes
  them, so a run-state-first order would be stable across sparse and full runs, while a team-first
  order changes shape depending on what the run recorded.

### Recommendation flag

**Owner's call; the evidence supports both.** The count favours team-primary, the data-gate behaviour
favours run-state-primary for stability, and no document names either. What the memo can say with
confidence: **team-primary is the cheap option** (regroup only, no reordering), and the existing order
already encodes it.

---

## W.3 — Ruling 3, fork 2: where does the Team Race banner's rule text go?

### The premise correction this memo rests on

`GRID-REVIEW.md` §P2 measured that the last card is a **scenario reference block**, not the page's
identity, and that the page's real identity is the `h1` + lede + stepper **above the grid**
(`ScenarioDetail.vue:58`, `PageHeader`). So Move 4's phrase "absorb into the identity card" has no
existing destination — that is the fork.

`TeamRacePanel.vue:27` renders the sentence: `<span class="font-bold text-ink-strong">Aim for at least
{{ guidance }} circles </span>`, where `guidance` is the config's `circles_guidance`. The same
component renders the run's recorded races below it with per-row at/above-margin verdicts
(`TeamRacePanel.vue:46-47`).

### The options

| | Option | What happens | Downstream cost |
|---|---|---|---|
| **A** | **New card** above the grid (below the lede) carrying the rule text | A real new panel — currently zero of these exist | Most expensive: new component, new payload field, new test, and **Move 1's reflow pricing must account for one more card**. Also separates the rule from the rows that apply it. |
| **B** | **Keep the banner as-is**; don't absorb | Nothing changes; Move 4 does not exist | Zero. Move 4 is deleted rather than deferred. |
| **C** | **Reference strip** (currently the last card) gains the rule text | The strip's role widens from "scenario reference" to "scenario reference + team-race rule" | Moderate: one template move, and the strip already carries scenario-wide facts, so a team-race rule is not out of place there. But it puts the rule far from the rows it governs. |

### The measured cost the review did not price

The banner is a **live rule read from config**, and the panel renders the run's rows against it with
an at-or-above-margin verdict per row. Lifting the sentence away from those rows:
- splits one rule from its instances, which is a closure/readability loss (`CLOSURE`, `GP-4`);
- leaves the rows' verdicts annotated by a rule printed somewhere else.

**Option A does this to the worst degree** (rule at the top of the page, rows in a card below); **C
less so** (both near the bottom); **B not at all**.

### Recommendation flag

**Owner's call.** No option is free except B, and B means Move 4 is dropped, not deferred — which is a
legitimate answer, not a non-answer. The one measured caution: **A costs more than the plan priced**,
because it creates a panel and then re-prices Move 1 around it.

---

## W.4 — Move 2's caps-block gate

### First, the stop condition: does the plan conflate two tables? — **no**

The dispatch asked this explicitly. Measured:

- `ScenarioPanel.vue` carries **one** caps table: `CapsuleHeader title="Published stat caps"`
  (`:300`), an `sr-only` `h3` (`:301`), and the table with `<caption class="sr-only">Published stat
  caps for this scenario</caption>` (`:316`).
- The HFW matches: **one** table, at `scenario-detail-hfw.html:322-...`, preceded by a `.sublabel`
  paragraph (`:319`).
- `grep -c "Published stat caps"` returns 2 in each — **heading plus caption, not two tables.**

So there is no conflation, and this memo proceeds.

### The question

Move 2 splits the strip: status half stays, caps table moves. The plan says the caps block needs its
own gate, because the strip's current gate is `careerGoalsOn || onPanels.length === 0` — widened by
`5e7da543` precisely so the caps would stop hiding behind the goals ladder (pinned by
`grand-concert-panel.spec.ts:59`, which asserts the table by its caption, and by
`scenario-panel.spec.ts:216`). Move the caps out and that gate stops applying to it.

### The options

| | Option | Consequence |
|---|---|---|
| **a** | **Caps stay in the current reference strip** — Move 2 becomes status-only | Mechanical, no gate question at all. The strip keeps the widened `careerGoalsOn ||` gate for the caps. But then Move 2 is smaller than the plan described: "split the strip" becomes "lift the status half". |
| **b** | **Caps move to a new row** — which row depends on W.2's answer | Needs Ruling 3's primary first, so it **cannot be priced until W.2 is ruled.** Adds a card; Move 1's reflow must account for it. |
| **c** | **Caps keep the wide gate but render as their own card in place** | Narrowest real change: the caps become a sibling card at the strip's current position, still gated `careerGoalsOn || onPanels.length === 0`, so the `5e7da543` rescue and both caps pins
(`grand-concert-panel.spec.ts:59`, `scenario-panel.spec.ts:216`) still hold. No new row, no new gate. |

### Recommendation flag

**Flagged: (c), on measured grounds** — it is the only option that moves the caps without either
re-deriving the gate that `5e7da543` established or waiting on Ruling 3. (a) is cheaper still but leaves
Move 2 mostly undone; (b) is blocked on W.2.

**What this memo cannot say:** whether splitting the strip is worth doing at all. The plan priced it as
consolidation under Ruling 2, but the measured benefit is thinner than Move 3's — Move 3 closed a
measured 236 px hole; the strip split closes no measured defect. That is stated here so the owner can
decline Move 2 on grounds of low value rather than only high cost.

---

## What these memos do not settle

| Question | Status |
|---|---|
| **Ruling 1** — capsule chrome for the page | **Open.** W.1 reduces the card case to A (free) and says the page-wide question is the real one. |
| **Ruling 3 fork 1** — team-primary or run-state-primary | **Open.** W.2, owner's call, evidence both ways. |
| **Ruling 3 fork 2** — where the banner rule goes | **Open.** W.3, owner's call, A costs more than priced. |
| **Move 2's gate** | **Reducible.** W.4 flags (c) as the only option needing no new gate and no ruling. |

Nothing in this file is a plan, a slice, or a dispatch. Each option's acceptance check is named in the
plan (`28d9afbf`) where one exists; where an option would create new work, the memo says so rather than
writing the acceptance for it.
