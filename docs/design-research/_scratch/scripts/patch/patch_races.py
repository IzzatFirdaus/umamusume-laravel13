import pathlib

p = pathlib.Path("docs/design-research/DESIGN.md")
s = p.read_text(encoding="utf-8")

CAL = """### 6.19 Fan readout

The client states fan progress as a sentence with the shortfall in it, not as a bare number: `Earn 3000 fans` above `Progress 1,943 fan(s) to go` (`Screenshot 2026-07-14 194819.png`). That is the shape to copy, because it answers "how far am I" without the Trainer doing subtraction against a target they have to remember.

- Persistent in the run header beside the Energy gauge, because fans gate entries and a Trainer checks it before deciding a turn.
- Current total at `numeral-md`, tabular, `ink-strong`.
- Target and shortfall as one `body` line, the shortfall in `up` orange, since fan gain is the reward axis of this tool and orange already means increase.
- No progress bar. The client does not draw one, and a bar implies a linear race to a fixed ceiling when the real calendar has several thresholds stacked.

### 6.20 Race calendar

Measured directly from the client's Scheduled Races panel (`Screenshot 2026-07-17 230755.png`, `235229.png`), which is already a 24-cell half-month grid under a three-segment Junior / Classic / Senior year tab bar. The design formalises that component rather than replacing it.

Cell states, and the count is five because there are genuinely five things a Trainer needs to distinguish:

| State | Treatment | Basis |
|---|---|---|
| Empty half-month | `sunken` fill, muted plus glyph | measured: disabled cell `#D0D1D0` |
| Optional race, enterable | `raised` fill, green plus, race artwork thumbnail | measured: available cell |
| **Mandatory Goal race** | red `Goal` pennant on the cell corner, warm outline | measured: the client's own `Goal` flag |
| Scheduled, already entered | pink `Scheduled` pill over the thumbnail | measured: the client's own `Scheduled` flag |
| **Locked by fans** | dimmed, padlock plus the exact `fans_needed` figure | derived; see the note below |
| **Locked by maiden gate** | dimmed, a distinct marker and the words `Win Debut or a Maiden race first` | derived from §1.2.6 client copy |
| Current turn | pale yellow fill with a warm outline | measured: current cell |

Two rules that are easy to get wrong:

**A fan lock must show the number, not just a lock.** `fans_needed` bottoms out at 350 for grades 300, 400 and 700 alike, so a lock icon alone cannot tell a Trainer which race they are short on or by how much. Show the figure.

**The maiden gate is not a fan problem.** The client states: "If you don't win the Debut, you're gonna have to win any Maiden Race before participating in any standard races" (`UMAMUSUME_REFERENCE.md` §1.2.6). Dimming it the same way as a fan lock would send a Trainer to check a fan total that is not the obstacle. It gets its own marker and its own sentence.

Tier badges carry the labels Pre-OP, OP, G3, G2, G1, but a badge rendered from stored grade data may only assert G1 and OP with confidence. Codes 200, 300 and 700 are `❌ UNVERIFIED` against those three labels in §1.2.6, so until the mapping is settled the UI shows the tier **as recorded on the race row**, not as derived from a grade code.

---

### 8.4 Race selection, F5

A fifth flow, added because the race calendar is a decision point and not just a display. It appears **only on a turn where the calendar holds an entry**, and it reuses the §8.6 event-panel shape rather than introducing a new component: same banner buttons, same arrow-caps, same preview-before-commit.

Each race option shows: race name, tier badge, `fans_needed` against the current fan total, whether it is mandatory, and the Energy cost. The preview lists the fan gain and the placement-dependent outcomes. **Skip is a first-class option**, rendered as a banner like any other, because "I am choosing not to run this" is a decision the Trainer makes deliberately and it should not be the absence of a click.

The panel must not rank races, project a placing, or estimate a win. `PRD.md` §6.11 still forbids race prediction and this flow does not change that; the Energy and `TurnEvent` decisions in ADR-0001 were scoped to training, explicitly leaving race outcomes untouched. Eligibility is arithmetic over stored values and is fine. Outcome is not, and does not appear.

"""

anchor = "\n---\n\n## 9. Motion"
assert anchor in s
s = s.replace(anchor, "\n" + CAL + anchor, 1)

# four flows -> five
s = s.replace(
"A turn is not always \"pick a training\". Four things can happen, and the UI must present each as itself rather than as another form variant.",
"A turn is not always \"pick a training\". Five things can happen, and the UI must present each as itself rather than as another form variant.", 1)
s = s.replace(
"| **F4 Scenario event** |",
"| **F4 Scenario event** |", 1)
s = s.replace(
"**Random world events are not modelled as a fifth flow.**",
"**Random world events are not modelled as a separate flow.**", 1)

p.write_text(s, encoding="utf-8")
print("DESIGN race sections added")


c = pathlib.Path("docs/design-research/CONSTRAINTS.md")
t = c.read_text(encoding="utf-8")

RULES = """## 10f. Race calendar and fan gating

**D-150. The race calendar is the client's component, not a new one.** 24 half-month cells under a Junior / Classic / Senior tab bar, as measured in `Screenshot 2026-07-17 230755.png`. Seven cell states are defined in DESIGN.md §6.20 and a cell must always be in exactly one of them.

**D-151. A fan lock shows the number.** `fans_needed` appears on the cell, not just a padlock. Grades 300, 400 and 700 all bottom out at 350 fans, so a lock alone cannot identify which race is blocked or by how much (`UMAMUSUME_REFERENCE.md` §1.2.6).

**D-152. The maiden gate is a distinct state from a fan lock.** The client requires a Debut or Maiden win before standard races are open, which is a conditional lock unrelated to fan count. Rendering it with the fan treatment sends the Trainer to check the wrong number.

**D-153. Tier badges display what is recorded, never what is inferred from a grade code.** Only grade 100 = G1 and grade 400 = OP are client-confirmed. Codes 200, 300 and 700 are `❌ UNVERIFIED` against G2, G3 and Pre-OP, so a UI that maps them is asserting a fact the repo does not have.

**D-154. Race selection is F5 and reuses the event panel.** It appears only on a turn where the calendar holds an entry. Skip is a first-class option with its own banner, never the absence of a click.

**D-155. No race outcome prediction, ranking, or win estimate.** `PRD.md` §6.11 is unmodified on this point. ADR-0001 lifted the non-goal for Energy guidance only and explicitly left race outcomes out of scope. Eligibility arithmetic is permitted; placing projection is not.

**D-156. Fan count is persistent in the run header**, using the client's own sentence form, target plus shortfall (`194819`). No progress bar: the calendar has several stacked thresholds and one bar implies one ceiling.

**D-157. Unverified race claims are attributed, not asserted.** "Up to 2 or 3 races per turn" and the Senior-year Arima Kinen requirement have no source in this repo. If they appear in a design artifact they are labelled as owner-supplied, per D-20.

**D-158. Race and fan data need a PRD requirement before any migration.** `turn_entries` has no `fans` column and there is no `race_goals` or calendar table. `PRD.md` US-10 is P2 and deferred. Building this UI in Phase 1 means promoting US-10 to P1 and writing an FR for it, which is an owner and Architect decision, not an implementation detail. See ADR-0001.

---

## 11. Verification gates"""

assert "## 11. Verification gates" in t
t = t.replace("## 11. Verification gates", RULES, 1)

t = t.replace("| G-17a | Event overlay", """| G-16a | Fan lock legibility | inspect a locked race cell | the `fans_needed` figure is shown, not a padlock alone (D-151) |
| G-16b | Lock kinds | inspect a maiden-gated race and a fan-gated race | they are visually distinct (D-152) |
| G-16c | Tier inference | grep for grade-code to tier-label mapping | none beyond G1 and OP (D-153) |
| G-16d | Race outcome projection | inspect the race selection panel | eligibility only, no placing or win estimate (D-155) |
| G-17a | Event overlay""", 1)

c.write_text(t, encoding="utf-8")
print("CONSTRAINTS race rules added")
