# Our Grand Concert — Known-Gap Stub

**Server:** `[Global]`
**Status:** Known-Gap Stub
**Last Verified:** 2026-09-27 (metadata pass)
**Superseded By:** none (filling this gap requires sourced mechanics, see the stub's boundary note)


> **This file is a boundary marker, not a guide.** It records what is sourced about the fourth
> `[Global]` scenario and, more importantly, the exact edge of what is not. It exists so that no
> later pass reads the repository as complete at three scenarios, and so nobody fills this gap by
> inference. Per `docs/design-research/CONSTRAINTS.md` **D-165**, no scenario-specific chrome may be
> invented from assumption, and per **D-241**, an undescribed scenario must render as baseline plus
> known caps — never as a guess.
>
> **Mechanical extraction for this scenario is suspended by owner decision (2026-09-27).** No
> extraction should be re-attempted until a primary source enters the corpus: a GameTora or Game8
> scenario page read to completion, a `[Global]` notice, or a client capture. An earlier attempt at
> the two pages named below was navigated away by a concurrent browser session and produced nothing.
>
> **Pointer, not an extraction (2026-09-28).** [`09-global-race-calendar.md`](09-global-race-calendar.md)
> documents the shared `[Global]` career race schedule and records the one race-level row this
> scenario has in the export: `URA Finals Final (Grand Live)`, tid `Md`, marked
> `did_not_exist: pre_gl`. That is a dataset row, not a mechanic. The suspension above stands
> unchanged, and nothing in 09 infers a Grand Concert goal list, finale structure or resource.

## Sourced facts

Everything in this table is quoted from material already in the repository or from the scenario
dataset. Nothing below is inferred from the Japanese release, from another scenario, or from the
scenario's name.

| Fact | Value | Source |
|---|---|---|
| `[JP]` title | つなげ、照らせ、ひかれ。私たちのグランドライブ | `scenarios.json` order 4 |
| `[Global]` title | **Brighter Together Our Grand Concert** (also written *Our Grand Concert*; export label "Grand Live (Grand Concert)") | `scenarios.json`; `docs/UMAMUSUME_REFERENCE.md` §1.6.1, §2.1 |
| `[Global]` live since | **2026-07-22** | `scenarios.json` `start_en`; mode matrix "4 total selectable" |
| `[JP]` live since | **2022-08-24** | `scenarios.json` `start_ja` |
| Scenario order | **4** of 14 — Trackblazer is 3 | `scenarios.json`; §1.6.1 and §2.1 both index it as 4 |
| Stat caps (Sp / St / Pw / Gu / Wi) | **1600 / 1300 / 1300 / 1500 / 1300** | §2.1, and §1.3.4; confirmed figure-for-figure by an independent Famitsu and GameWith extraction on 2026-09-27 |
| Cap derivation check | `1200 + stats` → `[400, 100, 100, 300, 100]` | the §2.1 formula, which reproduces every other scenario exactly |
| Hard ceiling | **2000** per stat | `scenarios.json` `hard_caps` |
| Notable about the caps | **Speed 1600 is the highest Speed ceiling on `[Global]`**, above URA Finale's 1400 | `docs/design-research/SCENARIO-DIFFERENCES.md` §"Live Global stat caps" |
| Scenario Link | **5 linked characters** in `scenario_linked_characters` | §1.4.7 (list lengths: URA 1, Unity Cup 5, Trackblazer 0, Grand Concert 5) |
| Scenario Link, per character | ❌ **the five identities are not recorded anywhere in this repository** | — see the gap list |
| Visual evidence in the capture corpus | **0 frames.** `docs/design-research/SCREENSHOT-MANIFEST.md:39` records "Our Grand Concert | None found | Absent" | manifest line 39, and line 80 lists its chrome among what was never captured |

⚠️ **Naming hazard for any importer.** Two labels circulate for this scenario: the export label
"Grand Live (Grand Concert)" and `[Global]` client wording "Brighter Together Our Grand Concert".
The JP title's own key noun is グランドライブ ("Grand Drive"). **Join on the dataset order (4) or the
`start_en` date, never on the name** — the same failure mode §6 documents for Champions Meeting
editions.

## ❌ UNVERIFIED — the gap, stated as a boundary

Not present in `docs/scenarios/`, not in the reference guide, not in the capture corpus. Each of these
is **unknown**, not "absent from the scenario":

- **Mid-scenario mechanics, currency, and any special training or event system.** Whether the scenario
  has one at all is unknown. One named mechanic is *referenced* but never read: §2.4 conflict 4
  records that the Game8 pages mention a **「Fully Charged」** resource printed alongside a **Power
  ≥ 1200** threshold. That is a title and a number from a secondary note, **not** an extracted rule —
  do not treat it as a mechanic, a currency, or a chip.
- **Facility leveling rule.** Unknown. Only three of the fourteen scenarios have a documented rule
  (repetition, team rank, repetition plus purchase), and this is not one of them.
- **Finale structure.** Unknown. Do not assume the URA-style elimination progression or Trackblazer's
  points league.
- **NPC presence, friendship bars, and Unique Skill gating.** Unknown. URA gates on Director Akikawa's
  friendship, Unity Cup removes her from the scenario entirely, and Trackblazer gates on fans **and**
  bond — so the answer is genuinely scenario-specific and cannot be carried over.
- **Objective structure.** Unknown whether it is goal-race-based, Grade-Point-based, or neither.
- **Scenario Spark name and the stats it boosts.** Unknown. Three of fourteen are documented (URA
  Speed/Stamina, Unity Cup Power/Wit, Trackblazer Stamina/Guts); this is not one of them.
- **Deck archetypes and any strategy guidance.** None exists. This file deliberately offers none.

## Matrix mapping (§10n) — what the dashboard must do today

The scenario already occupies a full column in the **Scenario Configuration Matrix**
(`docs/design-research/CONSTRAINTS.md` §10n). This stub does not add rows to it; it records that the
column's values are the acceptance case for D-241:

| Widget | Our Grand Concert |
|---|---|
| Turn chip, trainee + scenario identity, Energy gauge, Mood tier, stat band, timeline | **present** |
| Resource strip | **must degrade to baseline** — turn, trainee, scenario, Energy, Fans |
| Race Calendar (goal races) | **unknown → absent until sourced** |
| Grade Point meter with deadline | **absent** |
| Team Rank gauge | **absent** |
| Spirit Burst roster | **absent** |
| Shop, epithets, Rival marker, Race Fatigue chip | **absent** (each is a Trackblazer property, not a default) |
| Stat cap stack | **present**, with the scenario's own per-stat denominators: 1600 / 1300 / 1300 / 1500 / 1300 over the 1200 base |
| Fan readout | **present**, baseline behaviour only; the event-gate thresholds for this scenario are ❌ unknown, so no "next gate" value may be shown |

Two acceptance consequences, stated so a reviewer can check them without re-reading the design file:

1. **Selecting this scenario must not error and must not show a placeholder for a mechanic nobody has
   read.** Baseline widgets plus the known caps render the whole screen. D-240 makes the scenario set
   open for exactly this reason.
2. **A cap is a sourced fact; a widget is not.** Shipping the 1600 Speed denominator is correct.
   Shipping a "Fully Charged" chip because the phrase appears in a citation is the D-165 violation
   this file exists to prevent.

## What would close this file

In priority order, each of these is a single fetch away and none has been done:

1. Read `[Global]` scenario pages end to end:
   [Game8.co Grand Live (Grand Concert) Scenario Guide](https://game8.co/games/Umamusume-Pretty-Derby/archives/607337)
   (page dated 2026-07-26) and
   [Game8.co "Fully Charged" Explained](https://game8.co/games/Umamusume-Pretty-Derby/archives/607687)
   (dated 2026-07-02). Both are believed to exist; **neither has been read in this repository**, so
   neither is cited for a single fact above.
2. Capture the live client: scenario select screen, a mid-career training turn, and the resource
   strip — the manifest's "0 frames" row is the cheapest thing here to fix.
3. Then update §2.4 and conflict 4 of `docs/UMAMUSUME_REFERENCE.md`, and replace this stub's ❌ list
   with sourced rows rather than deleting the boundary language.

Compiled 2026-09-27. No fact in this file was inferred; every unverified item is marked as such.
