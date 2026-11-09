# Grid review — measured against the app and against this HFW

**Status:** review, not plan. No artifact change, no commit. Answers the four-question chain's
fourth question.

**Method.** The review asked for the bento read to be checked against raw HTML before anything
became a plan item, because its own packing arithmetic came from a screenshot. Done, and pushed one
step further: the **real app** at `/training-runs/7/scenario` was measured at 1280 px alongside the
HFW, so the arrangement can be attributed to the code rather than to the drawing.

Measured grid: **977 px wide, 12 tracks, both files**. The container query resolves at this width,
so the `@5xl` spans are the ones in force.

---

## The measured arrangement (app and HFW, identical)

| Row | Panels, left → right | Spans | Tracks used | Hole |
|---|---|---|---|---|
| 1 | Team Race | 12 | 977 / 977 | none |
| 2 | Team · Spirit · Team Rank | 4 + 4 + 4 | 954 / 977 | 23 px |
| 3 | Unity Cup progress · Team stat grades | 4 + 4 | **636 / 977** | **341 px** |
| 4 | Burst count bands · Spirit Burst | 8 + 4 | 965 / 977 | 12 px |
| 5 | Special Training · Team race coverage | 4 + 8 | 965 / 977 | 12 px |
| 6 | Team race shape | 12 | 977 / 977 | none |

Row heights, and the slack they leave: row 2 — Team 174, Spirit 244, Team Rank 289 (115 px of
empty column under Team); row 4 — Burst bands 402 against Spirit Burst 166 (**236 px** of slack).

**This is `UnityCupPanel.vue`'s own span list**, in its own DOM order:
`[12, 4, 4, 4, 4, 4, 8, 4, 4, 8, 12]`. Sum 68 tracks over 6 rows. The HFW reproduces it exactly
(same spans, same order), so every arrangement finding below is a finding about **the app**.

---

## The review's four problems, adjudicated

| | Review's claim | Measured | Verdict |
|---|---|---|---|
| **P1** | Semantic interleaving: rows 3 and 5 each pair a run-state panel with a team panel | Row 3 = Unity Cup progress (run) + Team stat grades (team). Row 5 = Special Training (team/run) + Team race coverage (team). The team block is 6 panels split across rows 2, 3, 4, 5, 6 | **Holds.** The strongest of the four. |
| **P2** | "Identity inversion" — the Unity Cup panel (namesake) is second-to-last | The Unity Cup strip is last, after Trainee goals and Mandatory races. But the review's premise — that the strip is the page's identity panel — is wrong: it carries the scenario label, badge, ruleset lines, the two widget tiles, the caps table, the finale line and the advisor note. It is the **scenario reference block**, and reference trailing the run-state is a defensible order. The page's identity is in the `h1` + lede + stepper above the grid already. | **Partly holds, and the reason given is wrong.** The strip being last is real; calling it the identity panel is not. |
| **P3** | Zigzag: three consecutive rows alternate wide-left / wide-right | Row 3 is 4+4 (**no wide side at all** — it is the 341 px hole). Row 4 is 8+4, row 5 is 4+8. So the alternation is **two** rows, not three, and only because row 4 and row 5 are mirror images. | **Holds in shape, wrong in count.** The zigzag is real between rows 4 and 5; row 3 has no wide side to alternate. |
| **P4** | Whitespace hole under Spirit Bursts | Under Spirit Burst (h=166) beside Burst bands (h=402): **236 px**. The **bigger** hole is row 3's **341 px of unused tracks** — a gap the review did not name, and the one that actually breaks the grid. | **Holds, and there is a worse one it missed.** |

**One panel the review names does not exist.** Its row-2 list is "Team · Sparks · Team Rank".
There is no Sparks panel: `grep -c Sparks` returns 0 in the HFW, 0 in `UnityCupPanel.vue`, 0 in
`ScenarioDetail.vue`. Row 2 is **Team · Spirit · Team Rank**. The screenshot's third tile was read as
"Sparks"; the app's panel is `Spirit` (the Current Spirit meter with its two absence notices).

---

## The proposed reflow, priced

The review's target arrangement (identity row 1, team contiguous 2–4, run-state 5, trainee 6–7,
caps 8) does fix P1 and P4's named case. Two facts it needs:

1. **P4 is not fully closable by reflow.** The review says so itself and is right: a 300 px stack
   beside a 375 px bands table leaves slack. Only merging Spirit Burst into the bands panel closes
   it, and that is consolidation.
2. **Row 3's 341 px hole is closable without any owner ruling.** It is a span arithmetic defect:
   `4 + 4` in a 12-track grid. `4 + 4 + 4` or `4 + 8` closes it. That is a one-line change to
   `UnityCupPanel.vue`'s wrapper classes, not a reflow, and it does not move a single panel.

That distinction matters for the decision count below: **closing the 341 px hole is a defect fix,
not a design decision.** Moving panels between rows is the decision.

---

## D1 — the decision count, adjudicated

The review is right that the annotation's "one visual decision" would become false if a bento
reflow lands, and right that this is the class of error the arc keeps catching. But the count
depends on what is being counted, and the two must not be lumped:

| Change | Kind | Owner ruling? |
|---|---|---|
| Chrome headers → plain `h2` | **Visual decision** (landed, annotated) | no — already the artifact's claim |
| Close row 3's 341 px hole (span arithmetic) | **Defect fix** | no |
| Move panels between rows / regroup by subject | **Layout decision** | yes |
| Split the Unity Cup strip (status → row 1, caps → row 8) | **Consolidation** | yes — Ruling 2 |
| Merge Spirit Burst into Burst bands | **Consolidation** | yes — Ruling 2 |
| Absorb the Team Race banner's rule text into an identity card | **Content move** | yes — Ruling 2 |

So the honest statement is not "the decision count becomes 2". It is: **one visual decision is
landed and annotated; one defect is unfixed; and four further moves would each need a ruling, of
which two are consolidation the review already flagged.**

## D2 — the identity split, adjudicated

The review's own interim — *leave the Unity Cup strip whole at row 1, split waits for Ruling 2* — is
the correct move and needs no further argument. Two additions:

- The strip is **not** the identity panel (see P2), so promoting it to row 1 is a **reference-order**
  choice, not a restoration of the page's identity.
- Moving it whole costs nothing and closes no hole, because it is a full-width `s12` in a page whose
  other rows are 4/8 mixes. Placing a `s12` first is exactly what row 1 already is for Team Race, so
  the two would swap, and the 341 px hole in row 3 would survive untouched. **The reflow does not
  fix the grid's real defect; the span fix does.**

---

## What this adds to the chain

The fourth question the review posed resolves as: **yes, a reflow would expand the artifact's
decision count — but by four rulings, not one; and the largest measured defect needs no ruling at
all and is not a reflow.** The three research questions and the fifth-finding display stand
unchanged.

Also standing, and separate: **KI-154** (the `Spirit Bursts` widget hardcoded absent) is why the
`Spirit` and bands panels disagree in the render. A reflow that stacks them side by side makes that
disagreement adjacent and more obvious, which is an argument for fixing KI-154 before, not after.
