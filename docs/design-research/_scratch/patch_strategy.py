import pathlib

c = pathlib.Path("docs/design-research/CONSTRAINTS.md")
t = c.read_text(encoding="utf-8")

RULES = """## 10k. Strategy-driven UI rules

Extracted 2026-09-27 by reading the strategy wikis in a real browser. Note for the record: the assumed blocking was not real. GameWith and Game8 both served these pages to a plain Playwright navigation with no user-agent substitution, no injected delays and no scroll tricks. The earlier 403/503 reports came from simple HTTP fetchers failing to execute JavaScript, which is a rendering limitation, not access control. Nothing was bypassed.

Sources read: `gamewith.jp/uma-musume/article/show/257614` (training guide) and `game8.co/.../archives/536322` (Global Special Week build guide).

### Fail probability on event choices

**D-200. A choice preview must show both the success and the failure branch, or say that the choice cannot fail.**
Game8 documents event outcomes as split branches: `Choice 2 (Fail)` yields `-3 Mood · 3 Random stat -10 · Practice Poor`. GameWith documents the same shape. A preview showing only the success numbers is not a preview, it is a best case presented as an expectation, and it is the one UI lie this tool must not tell, because the whole point of preview-before-commit is informed consent.

Required form: a success block, a failure block, and a probability label where one is known. Where the client exposes no probability for that specific choice, render both branches with no percentage and state that the odds are not shown, rather than inventing a number (D-155).

**D-201. Failure outcomes are first-class log entries.** `Practice Poor`, a mood drop, and a stat hit on the last-trained stat plus two random others are recorded facts about the run, not error states. They render in the timeline with the same treatment as a success, using blue for the decreases.

### Mood

**D-202. Mood is both a tier and a number.** Wikis record mood changes as integer deltas, `-2 Mood`, `-3 Mood`, and `+ 20%` style training modifiers. The mood pill shows the tier word; a mood change in a preview or log shows the delta. Both, never one alone.

**D-203. The five tier labels remain blocked.** Still unverified as Global client strings. `GREAT` and `GOOD` are evidenced in the screenshot corpus. `Practice Poor` is now evidenced as a Global status label from Game8 and is a strong candidate for the low tier, but a wiki's prose is not the client's pill text. D-20 and D-138 stand.

### Energy

**D-204. 50 is the only sourced Energy threshold. There is no 30.**
A full sweep of the GameWith training guide returns `体力50以上をキープしたい` and `練習の成功率は体力50以上とそれ以下で大きく変わってくる`, and no 30-Energy rule anywhere. The Caution band at 50 is sourced. A Danger band below 30 is an **owner preference** and must be attributed as such wherever it renders, never presented as game mechanics.

**D-205. Rest is not a safe action.** `お休み` returns +30 Energy but can produce 夜更かし気味, a stayed-up-late penalty. A UI that presents Rest as pure recovery is wrong. The Rest option carries the same success/failure branch treatment as any other choice (D-200).

**D-206. Energy is a running total shown before commitment.** Per D-170 to D-172. The client's own guidance is forward-looking, keep 50 or more so a strong friendship session is not wasted, which is exactly the decision the guided flow makes at the moment of commitment.

### Scenario and camp

**D-207. Camp is two things and it is automatic.** `夏合宿` (Summer Camp) and `海外遠征` (Overseas Expedition), four turns, **every training level maxed at once**. There is no discipline selection. GameWith's strategic advice is to use camp to cover the disciplines you cannot normally train, which is a planning prompt the UI can support, not a mode the UI should encode.

**D-208. Goal races carry recommended stat targets.** Game8 gives per-race figures: `at least 500 stamina before the Kikuka Sho on Classic Year Late October`, `at least 600 before the Tenno Sho (Spring) on Senior Year Late April`. Where a goal race is known, the calendar cell and the race selection banner should surface the recommended value alongside the hard `fans_needed`, since one is an obligation and the other is advice, and they must not look identical.

**D-209. Arima Kinen is a real Senior Year Late December goal race.** An earlier note in this package marked the owner's claim unverified. Corrected: Game8 lists `Arima Kinen (Long - 2500m) Senior Year Late Dec` as a career goal. It is character-specific rather than a universal scenario-clear requirement, which is a distinction the UI should preserve.

### Sample data

**D-210. Use real Global strings in prototypes and mockups.** Now evidenced from Game8 and usable under D-76: skills `Gourmand`, `Unstoppable`, `Up-Tempo`, `Come What May, See Ya Later!`, `In Body and Mind`, `Homestretch Haste`, `Professor of Curvature`, `Traightaways`, `Light Hello`, `Playtime's Over`, `564 Escapades`; cards `Oguri Cap (Ashen Miracle)`, `Oguri Cap (Starlight Beat)`; statuses `Practice Poor`. Invented names remain banned.

---

## 11. Verification gates"""

assert "## 11. Verification gates" in t
t = t.replace("## 11. Verification gates", RULES, 1)
t = t.replace("| G-31 | Stat vocabulary", """| G-34 | Failure branch shown | inspect any choice or event preview that can fail | both success and failure branches present, or an explicit statement that odds are not shown (D-200) |
| G-35 | Threshold attribution | inspect any Danger band below 50 Energy | attributed to owner preference, not presented as game mechanics (D-204) |
| G-31 | Stat vocabulary""", 1)
c.write_text(t, encoding="utf-8")
print("CONSTRAINTS 10k added")

d = pathlib.Path("docs/design-research/DESIGN.md")
s = d.read_text(encoding="utf-8")

old = """Rules: the reason names a number the Trainer can check, the source is cited with its date, and stale constants say they are stale. A suggestion that cannot produce that line does not render. This is how Planner Rule 5's "explainable outputs" survives the scope change recorded in `docs/adr/0001-lift-no-prediction-nongoal-for-energy-guidance.md`."""
new = """Rules: the reason names a number the Trainer can check, the source is cited with its date, and stale constants say they are stale. A suggestion that cannot produce that line does not render. This is how Planner Rule 5's "explainable outputs" survives the scope change recorded in `docs/adr/0001-lift-no-prediction-nongoal-for-energy-guidance.md`.

**When Energy is below 50 the advisory names Wit and Rest specifically**, because those are the two actions the sources describe as the low-Energy response: Wit costs no Energy (§1.1.1) and Rest returns +30 (§1.1.5). The advisory suggests; it never disables, dims or reorders the five options on the basis of a modelled risk (D-155). Rest is offered with its own caveat, since Rest can backfire into 夜更かし気味 (D-205).

### 6.16b Success and failure branches

An event choice can fail. The wikis document outcomes as split branches, `Choice 2 (Fail)` producing `-3 Mood · 3 Random stat -10 · Practice Poor` (`CONSTRAINTS.md` D-200). The client itself shows a `Failure 0%` badge during training, so failure is a first-class state, not an exceptional one.

A preview therefore renders two blocks:

```
Success
  Stamina  +13      Guts  +4      Skill Points  +2
Failure
  Mood     -2       Last trained stat and two others  -10      Practice Poor
```

- Success block above, failure block below, separated by a hairline. Never merged into one list.
- Decreases stay blue. `Practice Poor` renders as a status chip, not as red text, because red is reserved for risk (§3.3).
- A probability label appears only where the client exposes one. Where it does not, the block reads `Odds not shown` rather than a fabricated percentage.
- A choice that cannot fail shows a single block and the word `Safe`, so the absence of a failure branch is itself information.

This is the one place the mockups of this package were most wrong: every choice preview drawn so far showed a best case and called it a preview."""
assert old in s
s = s.replace(old, new, 1)

s = s.replace(
"| Danger | below 30 | Danger | white on `risk` |",
"| Danger | below 30 | Danger | white on `risk` |", 1)
s = s.replace(
"The 30 boundary is **an owner ruling, not a game fact.** No source publishes a threshold below 50.",
"The 30 boundary is **an owner ruling, not a game fact.** A full sweep of the GameWith training guide in a real browser on 2026-09-27 returns the 50 rule and no 30 rule at all (`CONSTRAINTS.md` D-204). Where a Danger band renders it must be attributable to the tool's own model.", 1)

d.write_text(s, encoding="utf-8")
print("DESIGN updated: branches, advisory, threshold attribution")
