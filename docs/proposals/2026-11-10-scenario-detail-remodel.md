# `S-RS-REMODEL-B1` — the consolidation plan, and where it stops

**Slice:** `S-RS-REMODEL-B1` (Workstream A). **Mode:** consolidate (owner Ruling 2).
**Base:** `5e39a1f0` (after Workstreams C and B).

**Verdict: the plan is written, and it stops at Move 4 for a named fork. Moves 1, 2 and 3 are
priced and ready; Move 4 is not implementable without Ruling 3.**

The stop is not a drafting failure. Move 4 asks the Team Race banner's *rule text* to move into the
*identity card*, and this repository has never decided which panel is the identity card on this page
— `GRID-REVIEW.md` records that the last card is a **reference** block, not the identity, and the
page's real identity is the `h1` + lede + stepper above the grid. So Move 4 requires a panel-priority
answer ("what is this page's identity object?") that is exactly the question Ruling 3 reserves, and
A.1's own stop condition fires on it.

---

## 1. The five moves, priced against the tree as measured

Every path below was verified with `git ls-files --error-unmatch` before being named, and every
file's dirty state was measured, because a move whose file carries a peer's uncommitted work cannot
be committed by pathspec without sweeping it.

| Move | Files touched | Clean? | Mode | Ruling needed |
|---|---|---|---|---|
| **1** — Reflow regroup (team 2–4 contiguous, run-state 5) | `resources/js/components/scenario/UnityCupPanel.vue` | **clean** | reflow | **Ruling 3** (panel priority) |
| **2** — Split Unity Cup strip (status → row 1; caps → reference row) | `ScenarioPanel.vue` (+ payload plumbing) | **DIRTY** | consolidation | Ruling 2 (given) |
| **3** — Merge Spirit Bursts into Burst bands | `UnityCupPanel.vue`, `SpiritBurstRoster.vue`, `TeamPanelBuilder.php` | clean | consolidation | Ruling 2 (given) |
| **4** — Absorb Team Race banner rule text into identity card | `TeamRacePanel.vue`, `ScenarioPanel.vue`, `TeamPanelBuilder.php` | DIRTY | content move | **Ruling 3** (identity question) |
| **P4 residual** | falls out of Move 3 | — | consolidation | covered by Move 3 |

**The dirtiness matters and is not incidental.** `ScenarioPanel.vue` carries this session's
uncommitted F-01 guard split (10 insertions, 2 deletions, shown in full in §5). Moves 2 and 4 both
touch it. Committing either with `git commit -- <path>` would carry the F-01 fix into a consolidation
commit, which is the peer-sweep hazard the dispatch's commit rule exists to prevent — except here the
"peer" is this same session's earlier, separately-authorized work. The resolution is procedural, not
technical: **land F-01 as its own commit first**, then the consolidation moves have a clean file.

---

## 2. Move-by-move specification

### Move 1 — Reflow regroup

**Before** (measured at 1280 px, 12 tracks, `5e39a1f0`):

| Row | Panels | Spans |
|---|---|---|
| 1 | Team Race | 12 |
| 2 | Team · Spirit · Team Rank | 4 + 4 + 4 |
| 3 | Unity Cup progress · Team stat grades | 4 + 8 |
| 4 | Burst count bands · Spirit Burst | 8 + 4 |
| 5 | Special Training · Team race coverage | 4 + 8 |
| 6 | Team race shape | 12 |

**After:** the team block becomes contiguous. Team · Spirit · Team Rank stay row 2; Team stat grades
and Team race coverage join them as a second team row; burst bands, Spirit Burst and Special Training
form the run-state row. **This is the one move that needs Ruling 3**, because it decides that
"team" outranks "run state" on the page — and the page is titled *Scenario detail*, where a reader
could equally argue run state leads.

**Stop condition A.1 fires here too:** the dispatch says stop if the plan "requires panel-priority
ordering (Ruling 3)". Move 1 is by definition that. It is priced and stated, not implemented.

### Move 2 — Split the Unity Cup strip

The strip (`ScenarioPanel.vue`) currently renders, in one `s12`-equivalent card: the scenario label,
a `Documented` badge, two ruleset lines, the two widget tiles, the `Published stat caps` table, the
finale line and the advisor note.

**Proposed split:** status half (label, badge, ruleset lines, tiles) stays where it is; the caps
table and finale move to a reference row after the trainee block.

**Cost that the review did not price:** the caps table is not a data-independent block — it reads
`scenario.caps` and `scenario.caps_absence`, and the strip's own gate is
`careerGoalsOn || onPanels.length === 0`, widened by `5e7da543` specifically so the caps would stop
hiding behind the goals ladder (pinned by `grand-concert-panel.spec.ts:49`). **Splitting the strip
re-opens that gate question**: a caps block moved out of the strip needs its own gate, and the
narrowest correct one is "the scenario declares caps", not "the scenario composes career goals".
That is a payload change (`ScenarioPanel` would need a separate flag), not a pure template move.

**Verdict: priced, ready, needs F-01 landed first.**

### Move 3 — Merge Spirit Burst into Burst bands

**This is the move the KI-154 fix was sequenced ahead of, and the sequencing was right.** Before
`5e39a1f0`, stacking the two panels would have placed `N/A` next to `11`; now both read 11 and the
merged card says one consistent thing.

**Before:** two cards — `Burst count bands` (`s8`) and `Spirit Burst` (`s4`, a
`SpiritBurstRoster` child).

**After:** one card carrying the bands table (with `10–12 (this run)` marked) and the roster's six
state rows beneath it. The `Estimated` badge stays on the bands half only, because it marks that
figure's provenance, not the roster's.

**P4 residual:** the review measured 236 px of slack under Spirit Burst beside the 402 px bands card.
Merging closes it by construction — there is no second card left to leave a gap under.

**Verdict: priced, ready. Highest value per unit of risk of the four.** No ruling fork.

### Move 4 — Absorb the Team Race banner's rule text — **STOP**

`TeamRacePanel.vue:27` prints *"Aim for at least {{ guidance }} circles"* as the team-race banner.
The move is to lift that sentence into an identity card at the top of the page.

**The fork:** where the sentence goes depends on what the page's identity object is, and the tree has
two defensible answers:

- **The `h1` + lede + stepper** above the grid is the page identity (this is what `GRID-REVIEW.md`
  §P2 measured, and it is why that review's "identity inversion" premise was judged wrong).
- **The last card** — the scenario reference block — is what the review *called* the identity panel,
  incorrectly.

So Move 4's destination does not exist yet as a named object. Creating one means deciding that the
scenario reference block outranks the run state, which is Move 1's question again in another form.

**Additional cost:** the banner is a *live rule read from config* (`circles_guidance`), rendered by
a panel that also lists the run's recorded races with at-or-above-margin verdicts. Lifting the
sentence separates the rule from the rows that apply it, which is a `CLOSURE`-class readability loss
(the rule and its instances stop travelling together) unless the rows move too — and moving the rows
is not a content move, it is a panel merge.

**Verdict: not implementable under Ruling 2 alone. A.1's stop condition fires. Owner rules; A.2
does not run for this move.**

---

## 3. The capsule question, in miniature (A.1 requires a stated treatment)

Move 3 merges two panels that today both carry a `CapsuleHeader`. The plan's choice, stated so it is
a plan decision and not a hidden ruling:

**Preserve the surviving panel's own header treatment unchanged; the merged card carries one header,
the bands', and the roster becomes a labelled sub-block inside it.** Rationale: the alternative
(two headers in one card) produces a card with two competing titles, and the third alternative
(dropping both) would silently answer Ruling 1 for the whole page, which the plan may not do.

This choice overrides Ruling 1 **for the merged case only** — it says nothing about the other panels,
which keep whatever chrome they have until Ruling 1 lands.

---

## 4. Content corrections — named here, implemented separately

The dispatch requires these as discrete items, not bundled into consolidation commits.

| Item | Site | Defect | Register |
|---|---|---|---|
| `+30` attribution | `UnityCupPanel.vue:128-131` | Binds the figure to team rank; corpus says league placement | **KI-155** |
| `15/15/20/20` caveat | `UnityCupPanel.vue:370` | Names a publisher figure the corpus does not carry | **KI-156** |
| Bare `N/A` | `UnityCupPanel.vue:324` | Absence not through the shared component | **KI-157** |

All three are filed and confirmed absent from `HEAD`'s history as fixes. **None is implemented by
this dispatch.** They are content-correctness fixes in a file consolidation also touches, which is
precisely why they must be separate commits: a reviewer reading "merge two panels" should not have
to also review three copy corrections.

## 5. The uncommitted F-01 work this plan depends on

`ScenarioPanel.vue` (DIRTY, 10+/2−) carries the guard split that stops the "composes no panels"
sentence printing over a populated page:

```diff
-                v-if="props.scenario.panel_absence === null"
+                v-if="props.scenario.panel_absence === null && onPanels.length === 0"
```

It is a landed-and-verified fix from the earlier slice (17/17 browser specs green, F-01 verified on
runs 3 and 7), but it is not yet committed. **Moves 2 and 4 both touch this file.** Landing it first
is a precondition of either, and it is this session's own work, not a peer's.

---

## 6. What A.2 can run if the owner clears it

| Move | Runnable now | Blocker |
|---|---|---|
| 3 (merge Spirit Bursts into bands) | **yes**, after F-01 lands | none — no ruling fork, highest value |
| 2 (split the strip) | yes, after F-01 lands | needs its own caps gate decided (stated above) |
| 1 (reflow regroup) | **no** | Ruling 3 panel priority |
| 4 (absorb the banner) | **no** | Ruling 3 identity question |

**Recommendation: run Move 3 only.** It is the move with a measured defect behind it (P4's 236 px
slack), no ruling fork, and a file that is clean. Moves 1, 2 and 4 each need an answer only the owner
can give.
