import pathlib

# ---------- status lines on the absorbed ADRs ----------
a1 = pathlib.Path("docs/adr/0001-lift-no-prediction-nongoal-for-energy-guidance.md")
s = a1.read_text(encoding="utf-8")
s = s.replace(
  "Status: **proposed, awaiting owner edits to PRD.md and CLAUDE.md**",
  "Status: **accepted in part.** The Energy decision stands. §5 (schema) is superseded by `ADR-0003`, which consolidates Energy, Fans, `turn_events` and race tracking into one amendment. Owner edits to PRD.md and CLAUDE.md are still outstanding.", 1)
a1.write_text(s, encoding="utf-8")

a2 = pathlib.Path("docs/adr/0002-scenario-caps-exceed-validation-bound.md")
s = a2.read_text(encoding="utf-8")
s = s.replace(
  "Status: **proposed, blocking. Owner decision required before any run-tracking UI is implemented.**",
  "Status: **accepted. Owner promoted US-10 to P1 and approved the schema expansion via `ADR-0003`.** The 0..1200 bound defect described here remains open and must be fixed alongside that schema work.", 1)
a2.write_text(s, encoding="utf-8")
print("ADR statuses updated")

# ---------- DESIGN.md ----------
p = pathlib.Path("docs/design-research/DESIGN.md")
s = p.read_text(encoding="utf-8")

HEADER = """### 6.22 Run header, the persistent resource strip

Three values decide every turn a Trainer takes: where the run is, how much Energy is left, and how many Fans have been earned. All three stay on screen at all times, in one strip, in that reading order. Nothing in the run view scrolls them away.

```
[ 13 turn(s) left ]  Rice Shower · Trainee Umamusume · Unity Cup
                     Energy ▓▓▓▓▓░░░░  42  [Caution]   |   1,943 fans · target 3,000
```

| Element | Behaviour |
|---|---|
| Turn chip | torn-page calendar card, §6.6. First in reading order and in the DOM |
| Scenario | `label-strong`, never a subtitle. Four live Global scenarios cap differently, so naming the scenario is load-bearing, not decorative (D-160) |
| Energy | §6.15 gauge. Prominent, because it is the value that can end a run |
| Fan count | §6.19 readout, client sentence form, separated from Energy by a hairline rule |

**Energy sits adjacent to the Confirm control in the guided flow, not merely in the header.** The decision the Trainer is making is "can I afford this session", and a gauge 800px from the button does not answer it at the moment it is asked. When stored Energy is under 50 the Confirm control carries the caution state from §6.15 and the §6.16 advisory line sits directly above it. Prominence at the point of commitment is the requirement; a second copy of the number elsewhere is not.

Fans and Energy are end-of-turn totals in the schema (`ADR-0003`), so the header shows a real stored value rather than a derived sum, and a corrected middle turn cannot silently corrupt the display.

"""

CAL = """#### Lock state grammar, the three that matter

A race cell can be blocked for two unrelated reasons, and a Trainer who misreads which one has wasted a turn. They must not share a treatment.

| State | Fill | Marker | Text | Why it is distinct |
|---|---|---|---|---|
| **Mandatory Goal race** | `raised`, warm 2px outline | red `Goal` pennant, top-right corner | race name at `label-strong` | Not a lock. It is an obligation, and it reads heavier than an optional race, never dimmer |
| **Fan-locked** | `sunken`, 55% opacity | padlock glyph | `1,000 fans` shown as a **number** | The remedy is measurable. A Trainer can see the shortfall against the header total |
| **Maiden-locked** | `sunken`, 55% opacity, **dashed** outline | distinct conditional marker, not a padlock | `Win Debut or a Maiden race first` | The remedy is an event, not a quantity. A padlock would send the Trainer to check a fan total that is not the obstacle |

The dashed outline on the maiden lock is the whole difference and it is deliberate: opacity and a padlock already say "you lack a number", so the conditional gate has to break the pattern rather than join it. Both locked states keep the race name legible, because a locked race the Trainer cannot identify is not information.

Completed and entered races use the client's own pink `Scheduled` pill (`RAW-FINDINGS.md` §4.9) and are never dimmed.

"""

s = s.replace("### 6.21 Scenario identity and stat caps", HEADER + "### 6.21 Scenario identity and stat caps", 1)
anchor = "Two rules that are easy to get wrong:"
assert anchor in s
s = s.replace(anchor, CAL + "\n" + anchor, 1)

# strengthen 8.9 with the conditional rule
s = s.replace(
"Each race option shows: race name, tier badge, `fans_needed` against the current fan total, whether it is mandatory, and the Energy cost.",
"""The step is **conditional**. It renders only on a turn where `scenario_races` holds an entry for that slot; on every other turn the flow goes straight to training or rest. A race step on an empty slot is noise that trains the Trainer to ignore it.

Each race option shows: race name, tier badge, `fans_needed` against the current fan total, whether it is mandatory, and the Energy cost. A fan-locked race is visible but disabled with its number shown, never hidden, because a race the Trainer did not know existed cannot be planned for.""", 1)

p.write_text(s, encoding="utf-8")
print("DESIGN updated")

# ---------- CONSTRAINTS.md ----------
c = pathlib.Path("docs/design-research/CONSTRAINTS.md")
t = c.read_text(encoding="utf-8")

RULES = """## 10h. Persistent resources and the conditional race step

Owner decisions of 2026-09-27, recorded in `docs/adr/0003`. These are requirements, not options.

**D-170. Turn, scenario, Energy and Fan count are persistent in every run view.** None of the four scrolls away. A Trainer mid-turn needs all four to decide the next action, and the schema now stores Energy and Fans as end-of-turn totals so the header shows a real value rather than a derived sum (`ADR-0003`).

**D-171. Energy is prominent at the point of commitment.** In the guided flow the gauge and the caution state sit adjacent to the Confirm control, not only in the page header. A gauge 800px from the button does not answer "can I afford this" at the moment it is asked.

**D-172. Energy and Fans store absolute end-of-turn values, not deltas.** This matches the existing convention for the five stats, which is why an injury is already representable as 302 then 294. A delta series cannot answer a start-of-turn value without a summation that every prior row must survive intact. Deltas stay a presentation-layer subtraction.

**D-173. The three race lock states are visually distinct, and the maiden lock breaks the padlock pattern.** Mandatory Goal races read heavier, never dimmer. Fan locks show the numeric `fans_needed`. The maiden gate uses a dashed outline and its own sentence, because its remedy is an event rather than a quantity. Sharing one treatment between the two locks is a review failure.

**D-174. Locked races stay visible and named.** A fan-locked race renders disabled with its number, never hidden. A race the Trainer did not know existed cannot be planned for.

**D-175. The race selection step is conditional on the calendar holding an entry for that slot.** On turns with no race the flow goes straight to training or rest. A race step on an empty slot is noise that trains the Trainer to ignore the step when it matters.

**D-176. `Skipped` is a stored status, not an absent row.** `race_entries.status` carries `NotOffered`, `Skipped`, `Entered`, `Completed`. A nullable-absent row cannot distinguish "not offered" from "declined", and that distinction is exactly what the calendar renders.

**D-177. Screen D is a generic multi-step scenario event.** No scenario mechanic is hardcoded into the UI. The Summer Camp "choose 2 disciplines" instruction was withdrawn by the owner on 2026-09-27; `UMAMUSUME_REFERENCE.md` §1.1.2 stands.

**D-178. US-10 is P1.** Race calendar and fan gating are Phase 1 scope. Any artifact that treats them as deferred is out of date.

---

## 11. Verification gates"""

assert "## 11. Verification gates" in t
t = t.replace("## 11. Verification gates", RULES, 1)
t = t.replace("| G-15a | Scenario legibility", """| G-28 | Lock-state distinctness | render a fan-locked cell beside a maiden-locked cell | distinguishable without reading the text (D-173) |
| G-29 | Conditional race step | advance a turn whose calendar slot is empty | no race step appears (D-175) |
| G-30 | Energy at commitment | measure the distance from the gauge to the Confirm control in the guided flow | gauge is adjacent, not header-only (D-171) |
| G-15a | Scenario legibility""", 1)
c.write_text(t, encoding="utf-8")
print("CONSTRAINTS updated")
