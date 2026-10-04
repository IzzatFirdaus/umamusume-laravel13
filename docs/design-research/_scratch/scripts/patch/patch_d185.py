import pathlib

c = pathlib.Path("docs/design-research/CONSTRAINTS.md")
t = c.read_text(encoding="utf-8")

RULES = """## 10j. Corrections taken from the screenshot corpus

Recorded because each of these was visible in the client and my earlier specs got it wrong anyway. A rule that names its own predecessor is easier not to repeat.

**D-185. Grade badges are tinted per grade.** A orange, B pink, C green, D blue, E purple, F and G grey. An earlier revision shipped one flat indigo badge on the argument that the hues were semantically spoken for; that overrode a direct observation recorded in `RAW-FINDINGS.md` §8. The letter always accompanies the colour, so nothing depends on hue discrimination (P5, D-12).

**D-186. The Energy fill sweeps the full gauge spectrum.** Cyan through green and lime into yellow, amber and red toward the right end. Colour encodes **position on the track**, not the current level. An earlier spec said "cyan to lime", which was a measurement artefact: the probe only covered the filled portion of a partly-drained bar, so the amber and red tail sat behind the dark unfilled track and went unmeasured.

**D-187. The discipline banner carries the facility activity name.** Two stacked ribbons: the pale one names the discipline and level, the saturated one names the activity the facility offers (`Breaststroke`, `Freestyle`, `Incline`, `Running`, `Long-Distance Swimming`, `Dirt`). A mockup showing only `Stamina Lvl 1` is incomplete, and the activity set is the best evidence in the corpus that scenarios differ in facility layout.

**D-188. The support card rail belongs on the dashboard.** Circular avatar, discipline type badge, segmented bond gauge, orange double chevron when friendship training is available, flame mark when the card sits on its own tile. Documented in `RAW-FINDINGS.md` §3.6 and then omitted from every mockup in this package.

**D-189. The failure indicator is a blue pill beneath the stat band.** The client shows a computed percentage because it holds live state. This tool renders the §6.15 band word and **never** a number, because no sourced curve exists (D-155, `ADR-0001` §3).

**D-190. Advisories are NPC speech bubbles, not banners.** A circular staff avatar beside a rounded white bubble with a small green `HINT` badge, which is how the client frames every piece of coaching it gives. This is the single largest immersion gain available and costs nothing, because the alternative is a validation banner that reads as software complaining.

**D-191. A cap increase is never phrased as a stat increase.** The client separates the two: `Max Energy +4` against `Guts +10` in choice previews, and the effect class 「限界値アップ」 rendered Global as `Max Speed`, `Max Stamina`. So a log line reads `Speed cap went up by 4`, never `Speed went up by 4`. Conflating them corrupts run history. Exact client log phrasing is unobserved, so the wording is ours and the distinction is not negotiable.

**D-192. Unique skill chips use a pink-to-blue gradient fill.** An earlier revision flattened this to `indigo-50` on the theory that pink and blue were reserved; those hues are semantic only when used as *meaning*, and a chip background carries no meaning beyond identity. The gradient is the most legible affordance on the skill panel.

**D-193. Scenario claims must name their evidence.** The corpus is predominantly Unity Cup with confirmed Ura Finale material and **no Trackblazer or Our Grand Concert frames**. A `Result Pts` counter and a `TEAM RANK` badge are observed Unity Cup chrome. Any design asserting a third scenario's UI is describing something this repository has not seen.

---

## 11. Verification gates"""

assert "## 11. Verification gates" in t
t = t.replace("## 11. Verification gates", RULES, 1)
c.write_text(t, encoding="utf-8")
print("CONSTRAINTS 10j added")
