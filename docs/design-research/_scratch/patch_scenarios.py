import pathlib

# ---------- DESIGN.md ----------
p = pathlib.Path("docs/design-research/DESIGN.md")
s = p.read_text(encoding="utf-8")

SEC = """### 6.21 Scenario identity and stat caps

The tool must never read as a URA-only tracker. Four scenarios are live on Global (`UMAMUSUME_REFERENCE.md` §1.6.1): Ura Finale, Unity Cup, Trackblazer, and Brighter Together Our Grand Concert, each with different caps.

**Scenario identity is a first-class header element**, not a subtitle. The active scenario name sits in the run header at `label-strong` beside the trainee name, and the phase bars in the timeline carry it, because a Trainer running two scenarios side by side needs to know which is which at a glance.

**The cap display has to be honest about being a derived value.** §1.3.4 establishes that a run's ceiling is scenario base, plus breakthrough at +16 per ★3 inherited basic-ability factor, plus per-stat support-card 「限界値アップ」 effects, applied at three separate moments in the run. There is no single stored cap to print.

So the stat band renders the cap as a **stack, not a number**:

```
Wit  1,340 / 1,800
       base 1,800 · +0 breakthrough · deck not tracked
```

- The denominator is the scenario base, labelled as such.
- A disclosure line names what is included and what is not. Until support cards exist in the schema, the line says the deck contribution is untracked. Naming a known gap beats implying a complete figure.
- The bar fill is scaled to the **scenario base for that stat**, never to a global constant, so a Unity Cup Wit bar and a URA Wit bar of the same value are visibly different lengths. This is what makes the tool read as scenario-aware without a single extra pixel of chrome.
- The 1200 validation line is drawn as a separate marker on the bar, not as the bar's end. See `CONSTRAINTS.md` D-31 and `docs/adr/0002`.

| Scenario | Speed | Stamina | Power | Guts | Wit | Source confidence |
|---|---|---|---|---|---|---|
| Ura Finale | 1400 | 1400 | 1400 | 1400 | 1400 | two corroborating guides, both `STALE` |
| Unity Cup | 1300 | 1300 | 1300 | 1300 | 1800 | same |
| Trackblazer | 1200 | 1900 | 1200 | 1500 | 1200 | **RETIRED (pre-correction) — DO NOT COPY.** Guts and Wit are transposed in this row. Correct value: `1200 / 1900 / 1200 / 1200 / 1500`, see `UMAMUSUME_REFERENCE.md` §1.3.4 (corrected 2026-09-27) and `docs/adr/0002` amendment 2. Retained only as the record of what this script patched |
| Our Grand Concert | 1600 | 1300 | 1300 | 1500 | 1300 | same |
| Grand Masters | 1500 | 1400 | 1500 | 1300 | 1300 | `[JP-Only]`, no `[Global]` release date. Written here as single-source; since upgraded to three-source (GameWith 2023-07-17, Game8 2026-04-13, Kamigame 2024-04-08), and it reproduces `1200 + scenarios.json.stats` = [300, 200, 300, 100, 100] |
| L'Arc | 1600 | 1600 | 1500 | 1500 | 1300 | `[JP-Only]`, no `[Global]` release date. ⚠️ STALE: single-source on Kamigame 2024-02-19 only, not corroborated |

**Server and date for every row above** (CONSTRAINTS.md §"every stored game value needs a server qualifier and a date"). Ura Finale `[Global]` 2025-06-26, Unity Cup `[Global]` 2025-11-06, Trackblazer `[Global]` 2026-03-12, Our Grand Concert `[Global]` 2026-07-22 — these four trace to Game8 2025-11-21 and Kamigame 2024-02-19, both ⚠️ STALE, and are reproduced by `scenarios.json` as `1200 + stats`. Grand Masters and L'Arc are `[JP-Only]` and get no UI.

The rows above state the confidence held **at the time this script ran**; two have since moved. Presenting a single-source figure as settled fact would promote an unverified claim into UI copy, which `CONSTRAINTS.md` D-20 forbids.

**Research gap, stated rather than papered over.** Every frame in the 1,160-image corpus is one scenario: the turn chips read "Until the Unity Cup". There is no screenshot evidence for Ura Finale, Trackblazer or Our Grand Concert UI, and none for the claim that scenarios differ in facility layout. The cap table above is documentary, not visual. Scenario-specific chrome variations must be designed from the scenario data, or captured from the client, before any of them is asserted in a mockup.

"""
anchor = "\n---\n\n## 7. Motif and ornament budget"
assert anchor in s
s = s.replace(anchor, "\n" + SEC + anchor, 1)
p.write_text(s, encoding="utf-8")
print("DESIGN 6.21 added")

# ---------- CONSTRAINTS.md ----------
c = pathlib.Path("docs/design-research/CONSTRAINTS.md")
t = c.read_text(encoding="utf-8")

OLD_D31 = """**D-31. Stat bounds are 0..1200 and the UI must say so.** `StoreTurnEntryRequest` owns the range (`CLAUDE.md` Planner Rule 6). A stat bar, sparkline, or input must not imply a wider ceiling. The game's real caps reach 2000 per scenario and guides treat 2000 as a target (`UMAMUSUME_REFERENCE.md` §1.3.4), so a 2000-wide bar in this tool would misrepresent what the app accepts. Show `/1200`."""
NEW_D31 = """**D-31. The 0..1200 validation bound is a defect, and the UI must stop hiding it.** `PRD.md` FR-C-2 and `CLAUDE.md` Planner Rule 6 fix stat input at 0..1200, but every live scenario caps higher: Ura Finale 1400 across the board, Unity Cup Wit 1800, Trackblazer Stamina 1900, Our Grand Concert Speed 1600 (`UMAMUSUME_REFERENCE.md` §1.3.4). **A real run cannot be recorded.** P0 US-3 therefore fails on legitimate gameplay data, and this is a product blocker, not a display question.

An earlier revision of this rule told the UI to "show `/1200`" and never imply a wider ceiling. That was wrong: it dressed a schema limitation up as a design decision and would have made the tool look internally consistent while being unable to hold the thing it exists to track. Withdrawn.

Current requirements, pending `docs/adr/0002`:
- The stat bar scales to the **scenario base cap for that stat**, per D-160, not to a global constant.
- The 1200 validation limit renders as a visible marker on the bar, labelled as the tool's current bound, so the gap between what the game allows and what this app accepts is explicit rather than silent.
- A value above 1200 must not be rejected with a message implying the game caps at 1200. Until the bound is raised, the error names the limitation as the tool's own."""
assert OLD_D31 in t
t = t.replace(OLD_D31, NEW_D31, 1)

RULES = """## 10g. Scenario awareness

**D-160. The active scenario is a header element, never a subtitle.** Four scenarios are live on Global and they cap differently. A run view that does not name its scenario at header weight reads as a single-scenario tracker.

**D-161. Stat bars scale to that scenario's per-stat base cap, never to a global constant.** A Unity Cup Wit bar and a Ura Finale Wit bar showing the same value must be visibly different lengths. This is the cheapest honest signal of scenario awareness.

**D-162. A cap is displayed as a stack, not a number.** A run's true ceiling is scenario base plus breakthrough at +16 per ★3 inherited factor plus per-stat support-card 限界値アップ effects (`UMAMUSUME_REFERENCE.md` §1.3.4), applied at three separate moments. Where a component is not tracked in the schema, the disclosure line says so. An unqualified "Cap: 1800" is a false claim of completeness.

**D-163. Single-source cap data carries its confidence.** Grand Masters and L'Arc caps are recorded in §1.3.4 as "single-source, not corroborated". They may appear only with that stated. The four primary scenario cap sets come from sources flagged `STALE` and need re-verification before implementation.

**D-164. Scenario must become resolvable before any of this is built.** `training_runs.scenario` is a nullable free-text string. Free text cannot select a cap set. Either an enum or a `scenarios` reference table is required, and cap data needs provenance rows like any other engine-owned fact. See `docs/adr/0002`.

**D-165. No scenario-specific chrome may be invented from assumption.** The entire screenshot corpus is one scenario, Unity Cup. There is no visual evidence for the UI of the other three, nor for differing facility layouts. Scenario-specific interface treatment is either captured from the client or designed from scenario data, and a mockup must label which.

---

## 11. Verification gates"""

assert "## 11. Verification gates" in t
t = t.replace("## 11. Verification gates", RULES, 1)
t = t.replace("| G-16a | Fan lock legibility", """| G-15a | Scenario legibility | open a run in each of the four Global scenarios | the header names the scenario and the bar scale visibly changes (D-160, D-161) |
| G-15b | Cap completeness | inspect any displayed cap | the stack shows what is included and what is untracked (D-162) |
| G-15c | Bound honesty | enter a stat of 1350 | the rejection names the tool's limitation, not a game ceiling (D-31) |
| G-16a | Fan lock legibility""", 1)
c.write_text(t, encoding="utf-8")
print("CONSTRAINTS D-31 corrected, 10g added")
