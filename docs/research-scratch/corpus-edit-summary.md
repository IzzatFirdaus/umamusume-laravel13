# Corpus edit summary, Phase C

Companion to the diff of `docs/research-scratch/DESIGN-CORPUS.md`, not a substitute for it. One entry per edit: what changed, the old text, the new text, what authorized it, and the note or frame the value came from.

| | |
|---|---|
| Date | 2026-10-03 |
| Target file | `docs/research-scratch/DESIGN-CORPUS.md` |
| Sections written | `## SCREENSHOT-MANIFEST.md` only |
| Sections authorized and then blocked | the mood-pill colour entries, see edit D |
| SHA before | `d9e5c681283f6e345a3eeb3169a4630b25a6e598b9ab7d17b2638b910f489c3b` |
| SHA after edits A to C | `79f380119665b4cded92951fc2fc0f2d0319a48e6276efd858da2abb4115556b` |
| SHA after edits E and F | `fb9bb295e500438262d4d8526d7ef4451ad687e81857112ebc512deb011a0136` |
| Owner rulings applied | Ruling 1(a), Ruling 2(a) blocked, Ruling 3(a), Ruling 4(a) plus its item 9 on re-authorization, Ruling 4(c) run separately |

No line of the `### Screen-type coverage` table was edited or renumbered. No `### Scenario coverage` row was edited. No line outside `## SCREENSHOT-MANIFEST.md` was touched.

---

## Edit A. Preamble and standing note, inserted after the section's intro block

**Authorized by:** Ruling 1(a) for the preamble, synthesis section 5 item 11 for the standing note.

**Old text** (the insertion point, quoted unchanged):

```
Triage of `docs/game-screenshots/` for scenario and screen-type coverage. Generated 2026-09-27.
Method and per-screen visual detail live in `RAW-FINDINGS.md`; this file is the coverage index.

### How this was produced
```

**New text:** the same two lines, then two new subsections, then `### How this was produced`.

`### Preamble: what the 44 representatives are` states four things: the 44 full-desktop figures are cluster representatives from `clusters.json` and not 44 distinct screens; at least one cluster mixes screen types, named as the cluster whose `rep` is `2026-07-18 001811.png` with 58 members, which contains `2026-07-17 233324`, a Learn screen, while its representative is a Training HUD; the mechanism, that the rolling window chains frames sharing HUD chrome; and the instruction that future expansions select by frame rather than by representative. It cites `research-scratch/screenshot-notes/PHASE-B-REPORT.md` item 13 for the derivation and item 2 for the counts.

`### Standing note: the corpus spans two trains` names `2026-07-14 025344` as the Curren Chan frame, all other noted full-desktop frames as `[Rosy Dreams] Rice Shower`, and states that any stat-curve, cap, or growth-rate read from the section is conditional on excluding `025344`. It records that the exclusion has not been performed.

**Evidence cited:** report items 2.2, 13.2 and 10.2; notes `2026-07-18 001811`, `2026-07-17 233324` (extras), `2026-07-14 025344`.

---

## Edit B. Verification note, inserted under the `### Corpus shape` table

**Authorized by:** Ruling 4(a) applying synthesis section 5 item 12.

**Old text:** the table's last row `| Median frame luminance | 193 / 255 |` followed directly by the paragraph beginning `Filenames carry no semantic information`.

**New text:** between them, one paragraph headed `**Verified against a second measurement, 2026-10-03.**` It names the four figures re-derived (1,160 total, 751 distinct, 173 full desktop at 44 representatives, largest cluster 58 at `2026-07-18 001811.png`), says they were re-derived from `signatures.json`, from `clusters.json`, and from a direct PNG IHDR measurement, that all agree, and that the 173 was confirmed twice over. It cites `PHASE-B-REPORT.md` item 2. It closes by limiting the claim: the verification says the arithmetic is right and does not change what the 44 are, which Edit A's preamble qualifies.

**No table row was edited.** The verification is a note beneath the table rather than a change to a cell, so the existing figures stay verbatim.

**Evidence cited:** report items 2.1 and 10.3.

---

## Edit C. `### Additions from the Phase B note set`, appended to the section

**Authorized by:** Ruling 4(a), applying synthesis section 5 items 2 through 8 and 10. Placed as a new subsection at the end of `## SCREENSHOT-MANIFEST.md`, after `### Not present in the corpus`, so that no existing row is edited.

Eight entries. Each carries the value, the source note id, and the frame that note describes. The five that carry a stat value also carry the hazard sentence inline, in the wording Ruling 4(a) specifies.

| Entry | Value applied | Source note or notes | Run-specific, hazard attached |
|---|---|---|---|
| 1 | Mood effects +20/+10/0/-10/-20 training and +4/+2/0/-2/-4 attributes while running | `2026-07-18 001957` | yes |
| 2 | "Mood -1", "Previously trained attribute -5", "Previously trained attribute -10", "Become Practice Poor", "Become Practice Perfect ○" | `2026-07-17 235902` | yes |
| 3 | Hint Lv 1 is 10 percent, Lv 2 is 20, Lv 3 is 30 | `2026-07-17 234521` | yes |
| 4 | Failure 5 percent Guts, 3 Speed, 1 Stamina, 2 Power on one turn, Wit not captured | `2026-07-18 135801`, corroborated by `135751` and `135756` | yes |
| 5 | Rail holds 6, 3, 2 and 6 cards for those four disciplines | `2026-07-18 001823`, `135751`, `135756`, `135801` | no, counts not values |
| 6 | Banner tint renders blue in `230755` where it renders green in `194819` and `202142`, so the per-trainee reading of the "Training HUD, blue theme" row is unsupported | `2026-07-17 230755`, `2026-07-14 194819`, `2026-07-14 202142` | no |
| 7 | Growth Rate +0 Speed, +10 Stamina, +0 Power, +20 Guts, +0 Wit | `2026-07-17 232345`, `2026-07-18 133731`, `2026-07-18 160438` | yes |
| 8 | The Learn left surface is unchanged behind both "Log" and "Scheduled Races" | `2026-07-17 234453`, `234521`, `233332`, `233339`, `233344` | no |

**Pre-application check.** Each value was confirmed present in the note it is attributed to before being written. Items 1 through 4 and 7 were checked by string match against their notes. Item 1's percentages and item 7's five signed values are in the notes verbatim; item 3's three hint-level pairs are in `234521` verbatim; item 4's four percentages with their discipline names are in `135801` verbatim along with the card-count series.

---

## Edit D. Not applied. The mood-pill hexes

**Authorized by:** Ruling 2(a), dispatched as Step 1.3.

**Intended change:** replace `--color-mood-normal: #A0978E`, `--color-mood-bad: #D48556` and `--color-mood-awful: #D47E9E` with measured values, and rewrite the rationale that says the derived tiers sit 1.0 and 3.3 degrees of hue from their positive partners.

**Not applied, for two independent reasons.**

1. **The source carries no hexes.** Step 1.3 requires "hexes quoted from the `001957` note". That note contains zero hex tokens. It records colour *names*: GREAT deep pink, GOOD burnt orange, NORMAL gold, BAD blue, AWFUL purple. Supplying six-digit values would be fabrication, which the corpus's own no-fabricated-claims rule and the dispatch's "do not infer" instruction both forbid.
2. **The corpus has already excluded this frame as a colour source.** The comment block above the palette states that the probe on this panel is not usable because "it is a legend panel, and sampling its row bands averages the pill against the panel field", and it names the open item as the three lower pill colours captured **from the HUD**. Frame `001957` is that legend panel, not the HUD. The dispatch's premise that this note settles the provisional hexes is therefore wrong on the corpus's own recorded method.

**Consequence not incurred.** Had the hexes been changed, the `on-mood` contrast rows for `mood-bad` and `mood-awful` at 6.23 and the `mood-normal` row at 6.25 would have become false, and the AA verdicts printed beside them would have needed recomputation. That cascade is a second reason not to make a partial edit.

**What does stand, and is already recorded:** the qualitative reading that BAD is blue and AWFUL is purple, which is a hue-family observation and does not depend on pixel sampling. It is carried in `PHASE-B-REPORT.md` item 10 and in `phase-c-synthesis.md` section 5 item 1, and it is not in the corpus.

**What the owner needs to decide:** whether a HUD capture of the mood pill is worth obtaining, which is the only route the corpus itself accepts, or whether the arrow rule D-259 keeps its current justification on the strength of the hue-family reading alone.

---

## Edit E. Standing note widened to the whole `2026-07-14` session

**Authorized by:** the owner's follow-up of 2026-10-03, first item.

**Old text:**

```
Added 2026-10-03 from the Phase B pass. `2026-07-14 025344` shows the trainee "Curren Chan". Every other full-desktop frame noted by that pass shows "[Rosy Dreams] Rice Shower".

**Any stat-curve, cap, or growth-rate read from this section is conditional on excluding `2026-07-14 025344`.** The exclusion has not been performed; it is recorded as a hazard to be carried until a pass can resolve which run each figure belongs to.
```

**New text:** a three-bullet breakdown of the `2026-07-14` session, then the exclusion conditioned on the whole session rather than one frame, then a sentence recording that the earlier narrower form left two stat-bearing frames in scope.

**Why the old text was wrong.** Its second sentence claimed every other noted full-desktop frame shows Rice Shower. The classification pass contradicts that: `194819` and `202142` are also `2026-07-14`, both carry a full stat band with denominators, and both are `unattributed`, so their notes name no trainee. Excluding one `2026-07-14` frame while leaving two stat-bearing `2026-07-14` frames in scope made the hazard statement incomplete in exactly the direction that matters, since the two portraits are training HUDs and the thing the note guards against is a stat read.

**Evidence cited:** `research-scratch/trainee-classification.csv` rows `2026-07-14 025344`, `2026-07-14 194819`, `2026-07-14 202142`. Verified count: the note set holds exactly three frames from `2026-07-14`, and the only other dates present are `2026-07-17` and `2026-07-18`.

---

## Edit F. Entry 9 added to `### Additions from the Phase B note set`

**Authorized by:** the owner's follow-up of 2026-10-03, second item, re-authorizing synthesis section 5 item 9 under the existing Ruling 4(a).

**Old text:** the subsection ended at entry 8, the Learn surface pairing.

**New text:** entry 9 added in the same pattern, in the owner's drafted wording with the frame ids written in full and the source-session claim made precise:

> **Observed stat denominators.** 1304 to 1358 across the four body stats and 1800 for Wit, from `2026-07-17 232345`, `2026-07-18 133731`, `2026-07-18 160438`, `2026-07-17 234444` and `2026-07-18 133312`. The minimum 1304 is in `232345` and the maximum 1358 in `133731` and `160438`. The values are run-specific; the standing note above applies. Any cap statement in this corpus should be checked against these before reuse. All five source frames are from the `2026-07-17` and `2026-07-18` sessions and so already satisfy that note's exclusion; the read is a Rice Shower read, and `2026-07-14 025344`, `194819` and `202142` contribute nothing to it.

**One deviation from the draft, to state a fact rather than a form.** The standard hazard sentence used on entries 1 to 4 and 7 asserts that `025344` is excluded from the read. For entry 9 that is true but trivial, because none of the five sources is a `2026-07-14` frame at all. The entry says so plainly and names the three excluded frames, which is checkable, in place of a boilerplate that would have implied a near miss.

**Range verified against the five notes before applying.** The denominators present are 1304, 1307, 1308, 1316, 1318, 1319, 1321, 1322, 1325, 1330, 1334 and 1358 across the body stats, and 1800 for Wit in all five. The minimum and maximum are attributed to the specific notes that carry them.

---

## Items considered and not applied

| Synthesis section 5 item | Disposition | Reason |
|---|---|---|
| 1, mood pill measured colours | Not applied | See Edit D. Blocked on two independent grounds and now an owner decision, not a pending edit |
| 9, stat denominators | **Applied as Edit F** | Was withheld when its range read 1318 to 1358. Corrected to 1304 to 1358 in the synthesis and re-authorized by the owner's follow-up |
| 11, two-trains hazard | Applied as Edit A, widened by Edit E | The first application named one frame; the classification pass showed two more stat-bearing frames from the same session |
| 12, corpus-shape verification | Applied | Edit B |
