import pathlib

c = pathlib.Path("docs/design-research/CONSTRAINTS.md")
t = c.read_text(encoding="utf-8")

RULES = """## 10i. Stat vocabulary and mockup verification

**D-179. The stat name set is closed. Six labels exist and no others.**
`Speed`, `Stamina`, `Power`, `Guts`, `Wit`, and `Skill Points`. Anything else is a defect, not a synonym. Banned specifically, because a generator produced them: **Strength** for Power, **Wisdom** for Wit, and **Luck**, **Skills**, **Endurance** and **Agility** as invented stat columns. The dumbbell glyph is always Power; the open-book glyph is always Wit.

**D-180. Skill Points is not a graded column.** It carries no grade badge and no `/cap` denominator, because it is a currency, not one of the five abilities. A grade badge on a Skill Points column is a spec violation even though the client's band renders the column beside the other five.

**D-181. Mandatory status is carried visually, never by an explanatory sentence.** A goal race announces itself with a `Goal` pennant, a heavier warm outline and greater height. A banner reading "mandatory goal races cannot be skipped" is redundant noise and is banned; the treatment is the message.

**D-182. When a mandatory race is open, Skip renders as secondary and unavailable.** Reduced opacity, dashed outline, circle-slash glyph, muted label. It stays visible so the Trainer understands why it is not available, and it must never read as an equal peer of the race options.

**D-183. Fan eligibility shows both numbers.** `620 / 1,000`, current against required, never a bare requirement or a bare lock. The shortfall is the decision-relevant figure and a Trainer should not have to subtract it mentally.

**D-184. Mockup images are not a verified surface.** The gate in `docs/design-research/_scratch/gate.py` scans HTML and markdown; it cannot read text out of a PNG. Controlled vocabulary in a generated image is therefore unverified by construction, and two rounds of regeneration were needed to converge. **The HTML prototypes are the authoritative surface for label correctness.** Where a mockup and a prototype disagree, the prototype wins and the mockup is re-rendered or discarded.

---

## 11. Verification gates"""

assert "## 11. Verification gates" in t
t = t.replace("## 11. Verification gates", RULES, 1)
t = t.replace("| G-28 | Lock-state distinctness", """| G-31 | Stat vocabulary | gate, terminology pass over HTML and markdown | only the six permitted labels appear (D-179) |
| G-32 | Run length | gate, regex pass for a turn denominator | no total asserted anywhere (D-136) |
| G-33 | Mockup labels | read the rendered image text by eye | six labels correct; the gate cannot do this (D-184) |
| G-28 | Lock-state distinctness""", 1)
c.write_text(t, encoding="utf-8")
print("CONSTRAINTS 10i added")

d = pathlib.Path("docs/design-research/DESIGN.md")
s = d.read_text(encoding="utf-8")
s = s.replace(
"- the value is the largest thing in the band. The label is not.",
"""- the value is the largest thing in the band. The label is not.
- **Skill Points carries no grade badge and no `/cap`.** It is a currency, not a sixth ability, and the client separates it by header colour rather than by grading it. A badge on that column is a spec violation (`CONSTRAINTS.md` D-180).
- **The six labels are a closed set.** Speed, Stamina, Power, Guts, Wit, Skill Points. Never Strength, Wisdom, Luck or Skills (D-179).""", 1)

s = s.replace(
"| **Mandatory Goal race** | `raised`, warm 2px outline | red `Goal` pennant, top-right corner | race name at `label-strong` | Not a lock. It is an obligation, and it reads heavier than an optional race, never dimmer |",
"| **Mandatory Goal race** | `raised`, warm 3px outline, taller than its neighbours | red `Goal` pennant, top-right corner | race name at `label-strong` | Not a lock. It is an obligation and it reads heavier than an optional race, never dimmer. **No explanatory sentence.** The pennant, the outline and the height carry it (D-181) |", 1)

s = s.replace(
"| Fan count | §6.19 readout, client sentence form, separated from Energy by a hairline rule |",
"""| Fan count | §6.19 readout, client sentence form, separated from Energy by a hairline rule |
| Fan eligibility | Always both numbers, `620 / 1,000`, current against required. The shortfall is the decision-relevant figure and must not require mental subtraction (D-183) |""", 1)

s = s.replace(
"A fan-locked race is visible but disabled with its number shown, never hidden, because a race the Trainer did not know existed cannot be planned for.",
"""A fan-locked race is visible but disabled with its number shown as current against required, `620 / 1,000`, never hidden, because a race the Trainer did not know existed cannot be planned for.

**When a goal race is open, Skip renders as secondary and unavailable**: reduced opacity, dashed outline, circle-slash glyph, muted label, and a short note naming the reason. It stays visible so the Trainer understands why, and it must never read as an equal peer of the race options (D-182).""", 1)
d.write_text(s, encoding="utf-8")
print("DESIGN updated")
