import pathlib

d = pathlib.Path("docs/design-research/DESIGN.md")
s = d.read_text(encoding="utf-8")

# --- grade badge colour ---
old = """- the client's letters run S through G with `+`/`-` modifiers. We render the letter plus modifier (`E+`, `D+`) and never colour alone (P5)
- **the client tints the badge per grade** (observed: D blue `#6DA0D9`, E magenta, F lavender-purple, G grey-green, A orange, B pink). This system ships **one neutral indigo badge** instead, and the reason is the same as §3.6: those hues are already spoken for, and the letter carries the information. A per-grade colour scale is an open option (§11.3), not a defect."""
new = """- the client's letters run S through G with `+`/`-` modifiers. We render the letter plus modifier (`E+`, `D+`) and never colour alone (P5)
- **the badge is tinted per grade.** An earlier revision of this section shipped one neutral indigo badge and argued that the hues were already spoken for. That argument was weaker than the observation it overrode: the client tints badges consistently across the stat band, the aptitude table and the rank laurels, and a flat indigo badge throws away a scanning affordance the game spent real design effort on. Restored.

| Grade | Tint | Measured from |
|---|---|---|
| A | orange | `232345` aptitude column |
| B | pink | `232345` |
| C | green | `232345` |
| D | blue `#6DA0D9` | `202142` |
| E | purple / magenta | `202142`, `232345` |
| F, G | grey-lavender and grey-green | `202142`, `232345` |

The letter always accompanies the colour, so a Trainer who cannot separate the tints loses nothing (P5, D-12). S and S+ sit above A and take a stronger gold. Exact hexes for grades other than D are read from small badges and should be re-sampled before implementation; they are directional, not final."""
assert old in s
s = s.replace(old, new, 1)

# --- energy gauge gradient ---
old2 = """| Fill | segmented, 10 blocks, `linear-gradient(90deg, var(--sp), var(--green))` |"""
new2 = """| Fill | **segmented, sweeping the full gauge spectrum**: cyan `#57C6EC` through green `#51E39C` and lime `#87E440` into yellow-green `#B7E23A`, then amber and red toward the right end. Measured as a hue sweep 207 to 75 across the filled portion of `194819`; the montage of partially-filled bars shows the orange and red tail on fuller gauges. The colour encodes **position on the bar**, not the current level |"""
assert old2 in s
s = s.replace(old2, new2, 1)

old3 = """| Band | Range | Word | Treatment |"""
new3 = """An earlier revision described this fill as a cyan-to-lime gradient. That was a measurement artefact: the probe only covered the filled section of a partly-drained bar, so the amber and red tail was hidden behind the dark unfilled track. Corrected.

| Band | Range | Word | Treatment |"""
assert old3 in s
s = s.replace(old3, new3, 1)

# --- discipline banner activity name ---
old4 = """### 6.5 Stat band"""
new4 = """### 6.4b Discipline banner

The client's training banner is two stacked ribbons, and the lower one carries information our design had dropped: the **facility activity name**, which changes with the discipline and the level. Observed in the corpus: `Breaststroke` and `Freestyle` for Stamina at the pool, `Incline` for Guts, `Running` for Speed, `Long-Distance Swimming` for Stamina at Lv5, `Dirt` for Power.

```
Stamina Lvl 1        <- pale ribbon, the discipline and its level
Breaststroke         <- saturated ribbon, the activity the facility offers
```

A mockup showing only `Stamina Lvl 1` is incomplete. The activity line is where the game tells you *what this turn actually is*, and it is also the strongest single piece of evidence that facility layouts differ between scenarios, since the activity set is a function of the facility.

### 6.5 Stat band"""
assert old4 in s, "6.5 anchor"
s = s.replace(old4, new4 + "\n" + old4, 1)

# --- support rail + failure/growth ---
old5 = """### 6.6 Turn chip"""
new5 = """### 6.5b Support card rail, failure badge, growth rates

Three elements the client keeps on the training screen permanently and that every mockup in this package so far has omitted.

**Support card rail.** A vertical column on the right edge of the training HUD, one circular avatar per card in the deck. Each carries a small type badge for the card's discipline, a segmented **bond gauge** beneath it, an orange double chevron when friendship training is available, and a flame mark when the card sits on its own tile. It is the Trainer's read on deck state at a glance, and it belongs on the dashboard, not only on the training screen.

**Failure badge.** A blue pill directly beneath the stat band reading `Failure 0%` (`194819`). It is the client's own risk surface and it is the natural home for the §6.15 Safe / Caution / Danger bands. Note the limit: the client shows a percentage it can compute from live state; this tool has no sourced curve, so we render the band word and never a number (D-155, ADR-0001 §3).

**Growth rate row.** A row of small per-stat pills, `+ 0%`, `+ 10%`, `+ 20%`, each prefixed by the discipline glyph (`232345`). It is a property of the trainee, not of the turn, so it belongs in the run identity block or the details modal rather than the persistent header.

### 6.6 Turn chip"""
assert old5 in s
s = s.replace(old5, new5, 1)

# --- unique skill gradient ---
old6 = """- `is_unique` skills get a sparkle mark, a `Unique` text tag at `micro`, and an `indigo-50` chip fill. The client sets unique skills apart with a pink-to-lavender gradient on the chip (`232345`); the indigo tint is the in-system equivalent. `gold` is deliberately not used here, because gold means *selected* and one row cannot mean both (§3.3)."""
new6 = """- `is_unique` skills get a sparkle mark, a `Unique` text tag at `micro`, and a **pink-to-blue gradient chip fill**, matching the client directly (`232345`, where `Blue Rose Closer` carries a pink-to-lavender gradient against flat lavender-grey on the ordinary rows). An earlier revision used a flat `indigo-50` on the grounds that pink and blue were spoken for; that lost the single most legible affordance on the panel, since the gradient is what makes a unique skill findable without reading. The hues here are **material fills, not semantic colours**, and they do not conflict with §3.3 for the same reason a card background does not. `gold` still means *selected* and is still not used here."""
assert old6 in s
s = s.replace(old6, new6, 1)

# --- advisory as NPC speech bubble ---
old7 = """### 6.16 Advisory and recommendation row

A suggestion is allowed to appear, but it must carry its arithmetic."""
new7 = """### 6.16 Advisory and recommendation row

**The advisory is an NPC speech bubble, not a banner.** The client delivers coaching through a circular avatar of a green-clad staff member beside a rounded white bubble (`025519`, `038100`, `038751`, `058814`, `194319`), with a small green `HINT` badge. That is the pattern to adopt, because it converts a warning from an interruption into a piece of dialogue, which is how the game frames every piece of advice it gives.

Adopting it costs almost nothing and buys the single largest immersion win available in this design: the advisory stops looking like validation copy and starts looking like the Trainer's assistant talking to them.

A suggestion is allowed to appear, but it must carry its arithmetic."""
assert old7 in s
s = s.replace(old7, new7, 1)

# --- cap vs stat phrasing ---
old8 = """- Delta colours are the client's: gains orange, losses blue, never green and never red."""
new8 = """- Delta colours are the client's: gains orange, losses blue, never green and never red.
- **A cap increase is never phrased as a stat increase.** The client distinguishes raising a value from raising its ceiling: choice previews read `Max Energy +4` against `Guts +10` (`124128`), and §1.3.4 names the effect class 「限界値アップ」, Global `Max Speed`, `Max Stamina`. So a log line reads `Speed cap went up by 4`, never `Speed went up by 4`. The two are different facts and conflating them corrupts the run history. The exact client log phrasing is not yet observed, so the wording is ours; the distinction is not negotiable."""
if old8 in s:
    s = s.replace(old8, new8, 1)
else:
    s = s.replace("""- **Delta colours are the client's**: gains orange, losses blue, never green and never red.""",
                  """- **Delta colours are the client's**: gains orange, losses blue, never green and never red.
- **A cap increase is never phrased as a stat increase.** The client separates raising a value from raising its ceiling: choice previews read `Max Energy +4` against `Guts +10` (`124128`), and §1.3.4 names the effect class 「限界値アップ」, Global `Max Speed`, `Max Stamina`. A log line reads `Speed cap went up by 4`, never `Speed went up by 4`. Exact client log phrasing is unobserved, so the wording is ours; the distinction is not negotiable.""", 1)

# --- scenario evidence correction ---
s = s.replace(
"""**Research gap, stated rather than papered over.** Every frame in the 1,160-image corpus is one scenario: the turn chips read "Until the Unity Cup". There is no screenshot evidence for Ura Finale, Trackblazer or Our Grand Concert UI, and none for the claim that scenarios differ in facility layout. The cap table above is documentary, not visual.""",
"""**Scenario coverage, corrected.** An earlier revision of this file claimed the whole corpus was one scenario. That was an overreach from a ten-frame sample and is withdrawn. A 96-frame stratified montage of the turn-chip region shows the corpus is **predominantly Unity Cup** with **confirmed Ura Finale material** (`012428` carries a `Place 1st in URA Finals` goal and a `FINISHED` state). **No Trackblazer or Our Grand Concert evidence was found**, so a three-way scenario comparison is not possible from this corpus.

Unity Cup also turns out to carry scenario-specific resources the design had not accounted for: a `Result Pts` counter and a `TEAM RANK B 11/16` badge (`121214`, `024535`, `031816`). The facility activity names in §6.4b are the strongest evidence that scenarios differ in facility layout, since the activity set is a function of the facility. The cap table above remains documentary, not visual.""", 1)

d.write_text(s, encoding="utf-8")
print("DESIGN updated: grades, energy, banner, rail, advisory, cap phrasing, scenario")
