# Unity Cup (Aoharu Hai) — Scenario Guide (Global EN Server)

**Server:** `[Global]`
**Status:** Superseded (pre-2026-07-01 mechanic numbers; strategy prose remains useful)
**Last Verified:** 2026-09-27 (metadata pass)
**Superseded By:** `06-unity-cup-gametora.md` for all mechanics numbers


> ## ⚠️ Currency of this file — read before using any number in it
>
> **`[Global]` reworked Unity Cup on 2026-07-01** (the same rework that raised stat caps and added
> purple Spirit Bursts; it corresponds to `[JP]`'s 2023-01-20 revision). This file's strategy and
> structure are still sound, but several of its mechanic numbers predate that patch, and
> `docs/scenarios/06-unity-cup-gametora.md` (GameTora, last updated **2026-07-20**, written against
> the post-rework Global build) **supersedes it wherever the two disagree**.
>
> Where this file states a figure that 06 also states, **06 is the source of truth**; the figures
> below have been corrected to the post-rework values and the full tables deliberately live in one
> place only. Do not re-paste 06's tables here — that duplication is what let this file drift.

## Overview

**Unity Cup: Shine On, Team Spirit!** (known as *Aoharu Hai* in the Japanese version) is the **second permanent scenario** on Global, released November 6, 2025 (UTC) during the Half Anniversary. Like URA Finale, it remains permanently selectable afterward — new scenarios don't replace old ones.

The defining difference from URA Finale: Unity Cup replaces the "train solo, occasionally see scripted events" loop with a **team-based mechanic**. You are managing not just your trainee's stats, but the collective stats/ranks of a roster of teammates who train alongside you, and periodically you fight **Team Races** where your whole team's combined performance matters, not just your trainee's individual race.

This makes Unity Cup meaningfully more mechanically dense than URA Finale — most of the extra "elaboration" the game requires you to learn lives in its Team mechanics below.

### Global Balance Adjustments (Important for EN Players)
On **November 11, 2025 (5 days after Global launch)**, a large balance patch landed early on Global — changes that JP only received months later at their 1st Anniversary. This includes a Guts stat rework, new race mechanics, better bad-condition management, and generally buffed skills/events. **If you're reading older JP-based guides or watching JP gameplay footage, expect the Global version of Unity Cup to feel noticeably different** from pre-patch JP experiences — this isn't a translation error, it's a genuine mechanical divergence introduced specifically for Global.

## Best Support Deck Setups

Unlike URA Finale's fairly rigid "4-Speed" template, Unity Cup supports **several viable deck archetypes** because of its Wit-energy and Pal-card mechanics:

### 1. Speed/Wit Deck
- Comfortable, low-maintenance setup. Wit training is the one discipline that **costs no Energy and returns some**, and a Wit-facility Spirit Burst adds **+5 Energy recovery** on top.
- ⚠️ **Post-rework note:** the extra Energy cost on Special Training was **removed** on 2026-07-01, so a Wit card is no longer the *necessity* it was for energy survival. It is now a comfort and SP-tempo pick, and 06 states plainly that Wit-heavy decks are "less uniquely necessary than before the patch".
- Best for Sprint/Mile; Medium is achievable with good Stamina rolls and recovery skills.

### 2. Front Runner Groundwork Variant
- A Speed/Wit variant that swaps some Wit copies for a card that grants **Groundwork**, a skill that specifically benefits Front Runners. Good if your trainee's running style is confirmed Front Runner.

### 3. Double Pal Deck
- Built around **Pal cards** (Riko Kashimoto, Tazuna Hayakawa) instead of Wit cards for energy/mood comfort — their event effects scale with limit breaks.
- Gives efficient early-career mood buildup (mood-up on first training with a Pal).
- ⚠️ **Post-rework note:** with Special Training's Energy penalty gone, this deck's advantage is **event-chain value and Mood**, not energy rescue. Note 06's team-composition rule: **Pal-type cards are excluded from the team roster**, so a Pal buys you no Spirit Bursts at all.
- Don't need to over-invest in Stamina; good for Sprint/Mile.

### 4. Speed/Power Deck
- Higher ceiling but "swingier" — you can end up over-training Power relative to Speed if training options don't cooperate.
- Best suited to Power-hungry running styles (Late Surger/End Closer) or short distances needing burst Power.

### 5. Speed/Stamina Deck
- Built to guarantee enough Stamina for Long-distance runs, typically anchored by Riko Kashimoto (Pal) + a dedicated Stamina card (e.g. Super Creek).
- More comfortable than a pure non-Wit, non-Pal deck thanks to Riko's energy management.

**Notable individual cards:**
- **Riko Kashimoto** is the strongest overall pick — her events give strong Stamina/Guts and her Pal effect eases energy management specifically around Unity Training/Spirit Bursts.
- **Rice Shower (Power SSR)** stands out for longer-distance builds; she can carry the Swinging Maestro skill plus her own scenario-link bonus (Cooldown).

## Core Mechanic: Special (Unity) Training & Spirit Bursts

This is the mechanical heart of Unity Cup and the biggest departure from URA Finale. **All values below are the post-2026-07-01 `[Global]` values; the full per-facility tables live in `06-unity-cup-gametora.md`.**

### How a teammate gets charged

- While training, teammates marked with a **white flame icon** are "chargeable" — training on their facility raises their **Spirit Burst gauge**.
- **Any** team member can appear in Special Training here, not just support cards and story characters as in URA Finale.
- Training with a flagged teammate raises her gauge **and** grants stat gains across all categories, with an extra bonus matching the facility's specialty (Speed training → bonus Speed **and** Power).
- ⚠️ **The rule the pre-rework version of this file missed: your trainee only gains bonus stats and SP when at least 2 flames are present on the tile at once.** A single flame benefits that teammate only — it is not a trainee gain.
- **Scenario-linked support cards add +1 to every stat gained from Special Training** when they are part of that instance — but Special Training gains and Spirit Burst gains are **counted separately** for that +1, so a stat that comes *only* from a Burst is not boosted by it. Worked example (from 06): Yukino Bijin + Kitasan Black on Guts = +2 Guts; add scenario-linked Haru Urara → **+3 Guts, +2 Speed, +2 Power, +2 SP**.
- ⚠️ Only **actual support cards** carry a bond gauge and can do Friendship (rainbow) Training. Story and random teammates cannot, though they still fully participate in Special Training and Bursts.

### Trainee gains by flame count (current patch, Speed/Stamina/Power facilities)

| # of flames | Primary | Secondary | Skill Points |
|---|---|---|---|
| 2 | 2 | 0 | 1 |
| 3 | 4 | 1 | 3 |
| 4 | 6 | 3 | 5 |
| **5** | **10** | **5** | **7** |

The Guts and Wit facilities route the secondary differently (Guts at 5 flames = 10 Guts / 3 Speed / 3 Power / 7 SP; Wit at 5 = 6 Wit / 2 Speed / 6 SP). Because of the shape of this curve, **prioritizing facilities where multiple teammates are gathered beats 1-on-1 training** on both raw gain and Burst charge rate. See 06 for every row.

### Spirit Burst

- Triggers on the **next** Special Training with a teammate once her gauge is full: **(1)** a large stat boost **to the teammate**, **(2)** a moderate boost **to your trainee**, **(3)** a **skill hint**.
- ⚠️ **Hints are no longer random** (post-rework): they are drawn from **that support card's own hint pool** — an R-card pool for non-deck teammates, falling back to a random A-rank-aptitude hint if the pool is exhausted. **Hint level = the card's hint bonus + 2, plus +1 for scenario-linked cards**, so a Burst hint never starts below Lv2.
- **Each character gets one normal Spirit Burst per career**, **plus one Extreme Burst after it** (below). Once both are spent she does not recharge for that run.
- You are **not** forced to fire a Burst the instant it is ready — hold it until she sits on the facility you actually want boosted. This matters more now that her Extreme Burst is the *following* training, so which facility you build toward decides where the bigger payoff lands.
- **All Bursts are purely additive** — no multiplier for firing several together, just flat stacking.
- ⚠️ **Energy:** the additional Energy cost on Special Training was **removed** in the rework. A Burst on the **Wit** facility additionally grants **+5 Energy recovery**. (Pre-rework text said Bursts *raised* Energy cost outside Wit — that is no longer true.)
- **Scenario-linked support cards raise the Burst values themselves**, not just the +1 above: a Speed Burst goes from **15 primary / 7 secondary / 5 SP** to **20 / 10 / 10 SP**. Real, but not worth building a whole deck around alone.
- ⚠️ Skill hint icons are largely hidden by the Unity Training icon overlay on facilities — check every facility manually, since a facility can be hiding a skill hint underneath its Unity Training indicator.

### Extreme Spirit Burst (added on 2026-07-01)

Available on a teammate's **next** Unity Training *after* her normal Burst — a second, stronger Burst per teammate per career. It can also appear on a support that already burst, and **an unused one can disappear and return later**. It does everything a normal Burst does, **plus**:

- sets **that training's failure chance to 0%**,
- **raises the teammate's stat caps**, and
- grants a hint for the matching facility-specific **"Ignited Spirit"** skill, starting at **Lv1** and scaling with the card's Hint Level bonus; if that hint is already maxed or owned you get a **different random Ignited skill** hint instead.

Values: **20 / 10 with 15 SP** normally, **25 / 15 with 20 SP** scenario-linked (06 has the per-facility grid, including the Wit row's 15 rather than 25 primary).

⚠️ **Scope ruling on the 0% failure:** the URA page says the exemption applies "whenever it appears on any training", but the **Unity Cup page — the one that owns the mechanic — scopes it to the facility holding the active Burst**. Use the narrow reading; the wide one would wrongly suppress failure on tiles with no Burst on them.

## Core Mechanic: Team Rank & Facility Levels

Unlike URA Finale (where facility level comes from repeating the *same stat* 4 times), Unity Cup's **facility level is tied to your team's overall stat rank** for that stat:

| Team Stat Rank | Facility Level |
|---|---|
| G–F | 1 |
| E–D | 2 |
| C–B | 3 |
| A | 4 |
| S | 5 |

This means facility level in Unity Cup is a **team investment problem**, not a personal-repetition problem — you must raise your whole team's collective stat in that category, not just grind your own trainee.

Raising your **overall Team Power/Rank** (aggregate across all stats) also grants direct stat bonuses on every rank-up, from +2 all stats at F/E rank up to +5 all stats at A/S rank, plus a Hint Level for the "It's On!" skill.

⚠️ **The rank ladder now extends past S.** The rework added **Team Rank S+** above the previous S ceiling, and it is the second "It's On!" hint source (see that section). Older guidance that treats S as the top of the ladder is incomplete.

## Core Mechanic: Team Races

| Round | Timing |
|---|---|
| Round 1 | After Junior Year, Late Dec |
| Round 2 | After Classic Year, Late June |
| Round 3 | After Classic Year, Late Dec |
| Round 4 | After Senior Year, Late June |
| Finals | After Senior Year, Late Dec |

- Each Team Race is a **set of 5 races**, one per category (Sprint, Mile, Medium, Long, Dirt) — and you field a **sub-team of 3 per category**, structurally the same shape as the Team Trials PvP mode. ⚠️ Earlier drafts of this file said "5-person sub-team"; 3 is the real slot count (and 06 states it as "just like Team Trials").
- You race **NPC teams, not other players**: the menu offers **3 opponent teams from strongest to weakest**, and beating a stronger one raises your **league rank** further. Before you commit, the game shows a circle-based win-odds estimate per category — **aim for at least 3 circles overall**, because **losing decreases your league rank**.
- ⚠️ **New since the rework: a lost Team Race can be retried with an Alarm Clock item.** This was unavailable at scenario launch, so older guidance treats a bad race day as permanent. It is not.
- Completing all 5 races in a round grants an **all-stat increase**, scaled by performance — winning matters, this isn't just a participation flag. Each round also ends with **new random teammates joining** the roster.
- A countdown to the next Team Race (every 6 months) is shown under the standard objective timer.
- **Do not try to maximize regular (non-team, non-goal) races** during Unity Cup — every turn spent racing is a turn not spent building Spirit Bursts/team rank, which is the mode's real growth engine. Only race outside of team races / trainee goals when it directly serves those goals.
- Your trainee's own individual **race goals still apply on their normal schedule** (same as URA Finale), including the final URA Finale-style races — failing those enough times still ends your run early, exactly like URA Finale. Unity Cup adds Team Races on top; it does not remove the underlying career race-goal structure. Structurally, 06's framing is the clean one: Unity Cup is **"URA Finals plus a team layer"** — same base schedule, same objective shape, same final 3 races.

### Beating Team Zenith (Finals)
Team Zenith is the hardest scripted opponent, encountered in the finals **after the 4th Team Race**, under Riko Kashimoto with original characters **Little Cocon** and **Bitter Glasse**. Your overall **Team Rank** buffs your attributes against them, which can let a below-top team win — ⚠️ the pre-rework line here read "up to +50 all stats at Rank 5+", a number keyed to `[JP]`'s numeric rank ladder rather than `[Global]`'s letter ladder (F/G → S → **S+**); treat the "+50" as a `[JP]`-side figure and the letter ladder as the live Global one. Recommended approach for reaching the top ranks in the lead-up races:
- Win the **top-tier race option** every time except once.
- Pick the **middle option exactly once** — commonly recommended for the very first race, since it's the safest slot to "spend" your one non-top pick.
- If you're winning the top option consistently and reach the 4th race, it's safe to take the middle option there instead without losing much — this also avoids risking a loss against the harder alternative opponents (Turf Queens / The Apex).
- Beating Zenith **while at Rank S** is what makes Little Cocon and Bitter Glasse appear as **opponents in the URA Finale-style final race** later in the run. Winning the Zenith battle is also what actually grants your team name's skill — simply picking the name isn't enough.

### Elite ("Powerhouse") Teams — unlocked by the rework
⚠️ What this file previously listed as a *future* update is **live on `[Global]`**. An Elite Team can appear during the **4th** Team Race if **all three** hold:

1. your **league rank is ≥ 10**,
2. your **Team Rank is ≥ A**, and
3. you have triggered **at least one Extreme Spirit Burst**.

They are flagged with a **pink/purple background** and named after **Greek deities** — not to be confused with the `[JP-Only]` Grand Masters three-goddess system. **Winning** one pays extra stat bonuses and unlocks a **strengthened Team Zenith** in the finals; beating *that* grants extra stats, makes the **Unity Cup Scenario Spark easier to obtain**, and unlocks **"+" versions of the Unity Cup scenario skill Sparks**, marked by **blue flames instead of red** on the pre-race screen. So the practical planning consequence: **carry an Extreme Burst into the 4th Team Race** rather than spending all three conditions earlier or later.

## Team Composition Rules

- Team members are your trainee, your 6 Support Cards, plus other characters who join at random through the career.
- **A minimum of 1 racer is required per distance; up to 3 racers per distance.**
- Slot teammates into the distance where their aptitude is **A rank** whenever possible — mismatched aptitude drags down both individual and team performance.
- Fill teams with the **highest-rank members available**, since team stat rank (not raw trainee stats alone) drives your facility levels.

## Team Name Selection & Scenario-Linked Skills

Around **Junior Year, Late September**, you'll choose a Team Name, determined by which scenario-linked character(s) you're running (in your Support deck or as trainee):

| Team Name | Linked Character(s) | Reward Skill (on beating Team Zenith in Finals) |
|---|---|---|
| Happy Hoppers | Taiki Shuttle | Mile Maven |
| Sunny Runners | Matikane Fukukitaru | Clairvoyance |
| Carrot Pudding | Haru Urara | Indomitable |
| Blue Bloom | Rice Shower | Cooldown |
| Team Carrot (default) | None of the above | No Stopping Me! |

Winning the Team Zenith battle in the finals is what actually grants the associated skill — simply picking the team name isn't enough. Strongest picks generally: **Mile Maven** (great for Mile races broadly), **No Stopping Me!** (good general Pace/Late/End skill), **Cooldown** (solid Long-distance recovery skill).

### Random Scenario-Link Bonus Events
Beyond team name selection, you may randomly encounter events with these characters, giving bigger bonuses if they're scenario-linked (i.e. present in your deck/trainee):

| Character | Normal Bonus | Scenario-Link Bonus |
|---|---|---|
| Taiki Shuttle | +10 Speed | +20 Speed, +10 Skill Pts |
| Rice Shower | +10 Stamina | +20 Stamina, +10 Skill Pts |
| Haru Urara | +10 Guts | +20 Guts, +10 Skill Pts |
| Matikanefukukitaru | +10 Wit | +20 Wit, +10 Skill Pts |
| Riko Kashimoto | +10 Wit, +10 Energy | +20 Wit, +10 Skill Pts, +15 Energy, +1 Mood |

## Exclusive Skills from Spirit Burst Count

⚠️ **Corrected to post-rework values.** The count that matters is **Spirit Bursts + Extreme Spirit Bursts combined**, not normal Bursts alone, and the reward is graded **white → gold** rather than by two skill names.

| Total Bursts (normal + Extreme) | Reward |
|---|---|
| 4–6 | **White** hint Lv1, +10 matching stat, +10 SP |
| 7–9 | **White** hint Lv3, +20 matching stat, +20 SP |
| 10–12 | **Gold** hint Lv1, +30 matching stat, +30 SP |
| **13+** | **Gold** hint Lv3, +40 matching stat, +40 SP |

- **Where each rarity comes from:** the **white**-rarity versions of the scenario skills arrive from triggering **Extreme Spirit Bursts**; the **gold**-rarity versions (plus some additional white hints) arrive from the scripted **"Team Zenith Declares War"** event in **Senior Year, late November**. ⚠️ This file previously dated the payout to "early November" and named the tiers "Burning Spirit X" / "Ignited Spirit X"; 06's white/gold framing supersedes that, and "Ignited Spirit" is specifically the skill the **Extreme Burst** hints.
- The stat variant (SPD/STA/PWR/GUTS/WIT) is set by your team's **highest overall stat rank**, ties broken randomly among the tied stats, and each variant **scales off the team's total in that stat**, not your trainee's alone — three Power-heavy Aces produce a far stronger Power variant than a mixed-investment team.
- You can check your running Burst count anytime via the **Team Info** button next to your Team Rank display.

The strategy ranking below is **editorial guidance from the community guide, not a client or GameTora figure** — 06 publishes no such order:

| Skill Variant | Effect | Rough Priority |
|---|---|---|
| PWR | Late-race acceleration boost, scales with team Power | Best overall pick |
| GUTS | Late-race velocity + acceleration, scales with team Guts | 2nd |
| SPD | Mid-race velocity boost, scales with team Speed | 3rd |
| STA | Mid-race endurance recovery, scales with team Stamina | Situational (recovery needs) |
| WIT | Early-race navigation improvement | Lowest priority |

## "It's On!" Skill (Reaching Rank S and S+)

Reaching **overall Team Rank S** auto-triggers a scripted event granting a hint for the **It's On!** skill family (increases velocity when passing another Umamusume mid-race):
- **Lv1** normally.
- **Lv3** instead if you are training a scenario story character (e.g. Haru Urara) — that is the "+3 with a scenario-linked trainee" line this file already carried.
- ✅ **Now live:** reaching **Team Rank S+** grants **another** hint for the same skill, which is how you **max the hint level** — S+ specifically, with a scenario story character in the run.

## Shipped by the 2026-07-01 rework (previously listed as "future")

⚠️ Every item this section used to file under "Known Future Updates (Not Yet on Global)" **arrived on `[Global]` on 2026-07-01**. It is retained here as a delta list, not as a pending roadmap — and, per the import warning in `docs/UMAMUSUME_REFERENCE.md` §2.4 conflict 6, this is the section an importer previously had to filter. That reason is gone now that the state is current.

| Feature | Status on `[Global]` |
|---|---|
| **Stat cap increase** — Speed/Stamina/Power/Guts **1300**, Wit **1800** | **Live.** Matches `1200 + scenarios.json.stats` exactly |
| **Extreme (purple) Spirit Bursts** — bigger gains, extra hints, **0% training failure** | **Live**, scoped to the facility holding the active Burst |
| **Stronger opposing teams** (league rank ≥10 + Team Rank ≥A + ≥1 Extreme Burst) → strengthened Team Zenith, extra stats, easier scenario Spark, "+" scenario skill Sparks | **Live**, as **Elite / "Powerhouse" Teams** (above) |
| **Spirit Burst hints drawn from the triggering card's own hint pool** instead of randomly | **Live** |
| **Team Rank S+** and the second "It's On!" hint | **Live** |
| **Alarm Clock retry on a lost Team Race** | **Live** |
| Additional Energy cost on Special Training | **Removed** by the same patch |
| `[JP]`'s larger per-facility **Skill Point** payouts (e.g. Speed Lv1 = +4 SP) | ❌ **Not on `[Global]` yet** — Global still pays +2 SP on the four Energy disciplines. Do not assume `[JP]` SP values |

Still `[JP]`-side only, with no `[Global]` release notice found: **Unity Cup's `scenario_linked_characters` roster is 5** (Taiki Shuttle, Rice Shower, Haru Urara, Matikanefukukitaru, Riko Kashimoto) — treat any larger `[JP]` link list from a later scenario as out of scope here. See `docs/UMAMUSUME_REFERENCE.md` §1.4.7 for how Scenario Link is derived rather than stored.

## Best Strategy Summary for Unity Cup

1. **Pick your archetype for stat ceiling and event value, not for energy survival.** Since the rework deleted Special Training's Energy penalty, Speed/Wit is now a comfort-and-SP pick rather than a requirement; Double Pal buys Mood and event chains (and fields no teammates, being excluded from the roster); Speed/Power or Speed/Stamina chase a specific distance ceiling.
2. **Prioritize Special Training over solo/regular racing** for most of the run — team rank sets facility level, so unteamed solo training is comparatively inefficient. **Get 2+ flames on the tile**: one flame gives your trainee nothing.
3. **Bank Spirit Bursts strategically** — don't trigger the instant they're ready; wait until the teammate is on the facility you most need boosted. Her **Extreme** Burst then lands on the training right after, so the facility you build toward decides both payoffs.
4. **Chase the combined Burst count, not just Bursts**: 13+ normal + Extreme gives the gold, Lv3 scenario skill (+40 stat, +40 SP).
5. **Build to Team Rank S+, not just S**, for the second "It's On!" hint — most efficiently with a scenario story character in the run.
6. **Carry an Extreme Spirit Burst into the 4th Team Race** with league rank ≥10 and Team Rank ≥A to trigger the Elite Team path; the strengthened Zenith behind it is the route to the "+" scenario skill Sparks.
7. **Slot teammates by A-rank aptitude per category**, 1–3 per category, prioritizing your strongest-ranked members — and remember teammate stats rise **only** through Special Training, and **not** from the underlying card's level or limit break.
8. **For the Team Zenith finals fight, win every top-tier race option except one middle pick** (ideally the first race) to maximize rank going in; use an **Alarm Clock** on a Team Race loss rather than absorbing the league-rank hit.
9. Remember your **trainee's individual race goals are unchanged from URA Finale** and still run in parallel — Unity Cup adds a layer on top, it doesn't remove the underlying goal-race structure.
10. **Two dating rules for outside sources.** The 2025-11-11 `[Global]`-exclusive early patch (Guts rework, race mechanic changes, buffed skills/events) is **not** in pre-November-2025 `[JP]` material; and the **2026-07-01** rework is **not** in anything dated before 2023-01-20 on `[JP]`. Assume older guides — and older screenshots and community spreadsheets — carry **pre-rework** Burst values and the old Energy cost.
