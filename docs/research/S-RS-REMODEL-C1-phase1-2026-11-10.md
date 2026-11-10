# `S-RS-REMODEL-C1` — Phase 1 checkpoint

**Rulings applied, as received:** **W.1 = C** (plain `h2`, no capsule) · **W.2 = run-state primary** ·
**W.3 = C** (banner rule text → reference block) · **W.4 = C** (caps labelled "Published stat caps").

**Phase 1 did NOT clear. Phase 2 did not run.**

Two stop conditions fired, both in the dispatch's own list, and both are factual rather than
judgement calls. The plan addendum and the HFW redraw would each have required guessing which of two
contradictory authority documents governs the panel list — which is precisely what the dispatch forbids.

**Pre-flight:** HEAD `295331e5`; `git show 295331e5 --stat` = 1 file, the report it claimed; peer set
**0 modified / 6 untracked**, re-measured; every path this dispatch names resolves
(`UnityCupPanel.vue`, `ScenarioPanel.vue`, `ScenarioDetail.vue`, `TeamRacePanel.vue`, the plan, the
memos, the HFW) — no path correction needed.

---

## Fork 3 — Special Training classification: **clears, with a citation**

The dispatch said to stop if the plan's own category definitions do not settle it. **They do.**

`docs/proposals/2026-11-10-scenario-detail-remodel.md` (Move 1, "**After**") names it explicitly:

> "burst bands, Spirit Burst and Special Training form the run-state row."

That is the plan speaking, not an inference. The dispatch's worry — that Special Training carries a
*team* fact (per-facility levels) — does not move it: the per-facility-level fact it mentions is not in
this panel at all. `UnityCupPanel.vue:423-425` renders a note saying *"Per-facility levels and
per-member training state are not recorded here"* — an **absence statement**, and the panel's own
content is the energy-penalty removal, the Wit-burst bonus, and that absence. Facility level lives in
**Team Rank**'s ladder. So Special Training is run-state content with one team-adjacent disclaimer.

**Fork 3: resolved — run-state.**

The scheme the owner ruled from, `docs/proposals/2026-11-10-ruling-memos-scenario-detail.md` §W.2,
calls the same panel run-state twice: in the "What each choice produces" table ("some run-state panels
(burst bands, Spirit Burst, Special Training) promote above team panels") and in the measured-facts
bullet. So plan and memo agree on this panel.

---

## STOP 1 — the two authority documents contradict each other on the panel classification

This is the blocker. The dispatch's pre-flight step 9 requires classifying every panel before writing
§1.1, and the two documents that would supply the classification **disagree on four of them**.

| Panel | Plan `28d9afbf` Move 1 places it | Memo §W.2 (the doc the owner ruled from) places it | Contradiction |
|---|---|---|---|
| **Spirit** | team ("Team · Spirit · Team Rank stay row 2") | team (same list) | agree |
| **Unity Cup progress** | *not placed at all* | **team** — "the team panels with `v-if` data gates (Team, Spirit, Unity Cup progress, Special Training)" | **plan silent, memo says team; and the plan's own "After" omits it** |
| **Special Training** | run-state ("form the run-state row") | **team** — listed among "the team panels … with `v-if` data gates" | **direct contradiction** |
| **Team race coverage** | team ("join them as a second team row") | **run-state** — "The run-state panels (bands, coverage, shape)" | **direct contradiction** |
| **Team race shape** | *not placed at all* | run-state ("bands, coverage, shape") | plan silent |
| **Burst count bands** | run-state | run-state | agree |
| **Team stat grades** | team | not classified | — |
| **Team Race** | top, unclassified | unclassified | — |

The same three panels are classified **team** in one document and **run-state** in the other, and the
plan leaves two panels unplaced entirely. Since W.2 = run-state-primary is the ruling that *drives*
the reorder, and the reorder's whole content is "which panels are run-state", this is not a detail:
picking either document decides the new order and produces a different page.

**The memo's own recommendation flag says why this matters.** It states plainly: *"So the corpus does
not settle it. There is no document that says 'this page leads with team state' or 'leads with run
state'."* The owner then ruled run-state-primary — a legitimate ruling — but the classification table
the ruling would act on was never settled, and the two drafts of it disagree.

**I cannot resolve this without guessing, and the dispatch says not to.** Resolving it would mean
choosing which of two owner-visible documents is wrong about four panels.

## STOP 2 — the plan's "After" order is stale: it lists a panel Move 3 deleted

Independent of the contradiction above, the plan's Move 1 "After" enumerates **eleven** panels
including "**Spirit Burst**" as a member of the run-state row. Move 3 (`64c37c4f`) merged that panel
into Burst count bands. Measured:

```
grep -c 'CapsuleHeader title="Spirit Burst"' UnityCupPanel.vue  → 0
grep -c 'CapsuleHeader title='                UnityCupPanel.vue  → 6
```

The page has **10 cards**, not 11, and there is no Spirit Burst panel to place in a run-state row.
So even a fully-resolved classification could not be transcribed from the plan's order as written: it
names a panel that does not exist and omits two that do (`Unity Cup progress`, `Team race shape`).

This is a second, separate reason §1.1 cannot be written without guessing — not a restatement of
STOP 1. STOP 1 is "the documents disagree"; STOP 2 is "the plan's list is stale against the tree".

---

## Forks 1, 2, 4 — status, and why they were not reached

The dispatch lists four forks. Two are hard stops and two are agent decisions, but **all four sit
downstream of the panel order**, so none could be finalised:

| Fork | Kind | Status |
|---|---|---|
| **1** — capsule propagation (card-only vs page-wide) | **stop** | Not reached. But one measured fact bears on it: **the HFW already draws plain `h2` everywhere** — all four `CapsuleHeader`/`lattice-bleed`/`bg-chrome` string matches in it are prose (lines 31, 344, 345, 347), not markup, and it carries 14 `<h2>` and 0 capsule elements. So on the *wireframe* side, W.1 = C is already satisfied page-wide; the open question is only whether the **app** follows at card scope or page scope. |
| **2** — banner fate (title-only vs removed) | **stop** | Not reached. Measured input for whoever rules: `TeamRacePanel.vue:26-30` renders the rule as `<p>` text directly under a `CapsuleHeader title="Team Race"` (`:24`), and the same component renders the run's rows with per-row "at or above the margin" verdicts (`:47`) computed against that `guidance`. Removing the banner removes the only place those verdicts are explained. |
| **3** — Special Training classification | resolved | **Run-state**, cited above. |
| **4** — reference-block structure | agent decision | Not reached — it is a property of the new order. |

## What was produced, and what was not

| Deliverable | Status | Why |
|---|---|---|
| Plan addendum (§1.1) | **NOT written** | Its own content is the panel classification table and the new order — the two things STOP 1 and STOP 2 block. An addendum recording four rulings is useful; an addendum recording a *guessed* order would be a durable defect in the document the implementation is built from. |
| HFW redraw (§1.2) | **NOT redrawn** | Same reason: the reorder is its first instruction. Note the HFW already satisfies W.1 = C's visual requirement page-wide, so the redraw's remaining content is the reorder, the banner move and the caps label — all downstream of the order. |
| Phase 1 commits | **none** | The dispatch says to commit 1.1 + 1.2 "regardless of forks — addendum and redraw are useful artifacts even if Phase 2 blocked." True for an addendum that states the rulings; **not** true for one that states a guessed panel order, which is the part the next dispatch would implement from. Committing the rulings alone is possible and is offered below as the cheapest unblock. |
| Phase 2 | **NOT run** | Conditional on Phase 1 clearing. |

## The cheapest unblock, for the owner

Everything else is ready. Three answers clear the whole dispatch:

1. **Which document governs the classification — the plan or memo §W.2?** They disagree on Unity Cup
   progress, Special Training, and Team race coverage; the plan also omits Unity Cup progress and Team
   race shape entirely. Naming one as authoritative settles the order.
2. **Special Training** — already resolved as run-state by both documents' agreement on *that* panel;
   no answer needed unless the owner disagrees.
3. **Fork 1 scope** — card-only or page-wide capsule removal, now that the HFW is known to be
   plain-`h2` page-wide already; and **Fork 2** — banner keeps its title or disappears.

With (1) answered, §1.1 writes itself from the corrected 10-panel list; Fork 4 follows; Phase 2's four
slices are already committed-shaped in the dispatch.

## Gates

No code, config, test, route or migration changed in this dispatch, so the suite, PHPStan, lint,
typecheck and browser specs are **unchanged and not re-run** — running them would measure the tree at
`295331e5`, which the previous dispatch already reported as `2305 passed / 2 failed`. Nothing in this
dispatch can have moved that figure. Markdown gates: no markdown written except this checkpoint
(`0 invalid closing fence(s)`).
