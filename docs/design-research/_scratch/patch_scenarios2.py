import pathlib

c = pathlib.Path("docs/design-research/CONSTRAINTS.md")
t = c.read_text(encoding="utf-8")

RULES = """## 10l. Scenario mechanics extracted from the Game8 scenario guides

Read 2026-09-27 in a real browser. **Coverage is uneven and that is recorded rather than smoothed over:** the URA Finale guide (`archives/536520`) was retrieved in full. The Unity Cup guide (`archives/545572`) and the Trackblazer guide (`archives/580723`) both redirected away from their scenario pages during the session, so **neither scenario's own guide was actually read.** Anything below about Unity Cup or Trackblazer comes from the screenshot corpus or `UMAMUSUME_REFERENCE.md`, not from these two URLs. Do not cite 10l as evidence for them.

### The 1200 line is a real soft cap, not an app artefact

**D-211. Gains past 1200 are halved, and the UI must say so.**
The URA guide states plainly: *"Training gains for stats beyond 1200 are always halved."* It also names a mechanic, `Fully Charged`, that requires Power 1200 or more. So 1200 is a meaningful in-game inflection point, not just a validation constant this app happens to use.

This reframes `ADR-0002`. The defect there is real, the app rejects values a real run reaches, but the fix is not "raise the number to an arbitrary 2000". The bar should show **1200 as the soft-cap line where returns halve**, which is a fact about the game, and separately show the scenario ceiling, which is a different fact. Two markers, two meanings, both sourced.

**D-212. Caps are expressed as base plus bonus, and the client renders the bonus in gold.**
*"The URA Finale Scenario now has +200 stat caps for Speed, Stamina, Power, Guts, and Wit."* So URA's 1400 is `1200 base + 200 scenario`, not a flat 1400. The cap stack in `DESIGN.md` §6.22 is confirmed as the right model, and its rows should read as additions, not totals.

**D-213. Gold text is the client's own signal for a cap increase. Adopt it.**
*"When a stat's cap is about to be increased, the values that will be added will always be indicated in gold text."* This resolves the open wording question in D-191, which had said the distinction was real but the client's phrasing was unobserved. It is now observed: **cap increases render in gold.** So a `+4` in `gold` means the ceiling moved and the stat did not, and a `+4` in `orange` means the stat moved. The colour carries the distinction the prose was carrying, which is stronger than a wording rule. Keep the word `cap` in the text as well, so the distinction survives for anyone who cannot separate gold from orange (P5, D-12).

### Scenario structure

**D-214. Summer camp runs in both Classic and Senior years, four turns each.**
*"There are 4 turns of each Summer training and they occur during Early July until Late August of both Classic and Senior Years."* That is eight camp turns across a run, not one window. `UMAMUSUME_REFERENCE.md` §1.1.2 describes the window without saying it recurs per year, so this is a genuine refinement. The calendar must mark both windows.

**D-215. Scenario NPCs appear in training at calendar milestones, and they are the speech-bubble speakers.**
The URA guide names Director Akikawa, who appears in training after the debut and gives +30 Energy on the third camp turn, and Reporter Etsuko Otonashi, who appears from Early July. These are the characters behind the `HINT` bubble pattern in D-190, and **which NPC appears is scenario-specific**, so the advisory speaker is scenario chrome, not a fixed avatar. Note the rights position: the client's NPC artwork is not ours to ship, so the bubble carries a name and a monogram, not an illustration.

**D-216. Each scenario has a Scenario Link Character.**
*"Aoi Kiryuin is the Scenario Link Character"* for URA Finale. This is a scenario-level identity element the run header can carry, and it is a real field the schema has nowhere to store.

**D-217. Event choices are documented positionally as Top, Mid, Bottom.**
The guide writes outcomes as `Choices: Top: Stat +10 / Mid: Energy +20 / Bottom: Skill Points +20`. So the client presents a vertical stack of up to three, and `choice_index` in `ADR-0003` maps cleanly onto that. Design the choice list for one to three options, not a scrolling list of many.

### Staleness warning

**D-218. URA Finale was reworked on 2026-07-01 on Global, ahead of its planned release.**
*"Updates to the URA Finale scenario were made on July 1, 2026 in the Global version."* The cap table in `UMAMUSUME_REFERENCE.md` §1.3.4 traces to Game8 2025-11-21 and Kamigame 2024-02-19, both already flagged `⚠️ STALE`, and the URA row is now demonstrably from before a rework that changed caps. **Re-verify every cap before seeding `scenario_races` or a caps table.** The `+200` figure in D-212 is post-rework and supersedes the older table for URA.

---

## 11. Verification gates"""

assert "## 11. Verification gates" in t
t = t.replace("## 11. Verification gates", RULES, 1)
t = t.replace("| G-34 | Failure branch shown", """| G-36 | Cap-increase colour | inspect a preview or log line that raises a ceiling | gold value **and** the word `cap`; never orange alone (D-213) |
| G-37 | Soft-cap marker | inspect a stat bar above 1200 | the 1200 halved-gains line and the scenario ceiling are distinct markers (D-211) |
| G-34 | Failure branch shown""", 1)
c.write_text(t, encoding="utf-8")
print("CONSTRAINTS 10l added")

d = pathlib.Path("docs/design-research/DESIGN.md")
s = d.read_text(encoding="utf-8")

s = s.replace(
"""- **A cap increase is never phrased as a stat increase.** The client separates the two: choice previews read `Max Energy +4` against `Guts +10` (`124128`), and §1.3.4 names the effect class 「限界値アップ」, Global `Max Speed`, `Max Stamina`. A log line reads `Speed cap went up by 4`, never `Speed went up by 4`. Exact client log phrasing is unobserved, so the wording is ours; the distinction is not negotiable.""",
"""- **A cap increase is never phrased as a stat increase, and the client colours it.** *"When a stat's cap is about to be increased, the values that will be added will always be indicated in gold text"* (`CONSTRAINTS.md` D-213). So `+4` in **gold** means the ceiling moved; `+4` in **orange** means the value moved. The word `cap` stays in the text so the distinction survives for anyone who cannot separate the two hues (P5, D-12). This was an open wording question in an earlier revision and is now a sourced rule.""", 1)

s = s.replace(
"""- Cap is `1200` per the app's own validation range (`StoreTurnEntryRequest`, PRD FR-C-2). The band must never imply a higher ceiling: the game's real caps run to 2000 per scenario and some guides treat 2000 as a target (`UMAMUSUME_REFERENCE.md` §1.3.4), so an unlabelled 2000-wide bar would be a quiet lie about what the tool accepts. Show `/1200`.""",
"""- **Two markers, two meanings.** The bar carries the **1200 soft-cap line**, where *"training gains for stats beyond 1200 are always halved"* (`CONSTRAINTS.md` D-211), and separately the **scenario ceiling**, which for URA is `1200 base + 200 scenario = 1400` (D-212). They are different facts and must not collapse into one line. The denominator is the scenario ceiling; the tick at 1200 is the halving point.""", 1)

if "Two markers, two meanings" not in s:
    s = s.replace(
"""- the value is the largest thing in the band. The label is not.""",
"""- the value is the largest thing in the band. The label is not.
- **Two markers, two meanings.** The bar carries the **1200 soft-cap line**, where *"training gains for stats beyond 1200 are always halved"* (`CONSTRAINTS.md` D-211), and separately the **scenario ceiling**, expressed as base plus bonus (D-212). They are different facts and must not collapse into one line.""", 1)

d.write_text(s, encoding="utf-8")
print("DESIGN updated")
