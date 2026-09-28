# Grand Masters — `[JP-Only]` reference note

**Server:** `[JP]` (reference only)
**Status:** Active
**Last Verified:** 2026-09-27 (metadata pass)
**Superseded By:** none. Per `AGENTS.md` and the owner's Global-scope ruling this file must not be
imported into app data, config, or UI copy until a Global release date exists.


> ## ⚠️ Read this first: what this file is and is not
>
> **`グランドマスターズ -継ぐ者達へ-` has never been released on the Global English server.** As of the
> 2026-09-27 anchor it is `[JP-Only]`: the scenario data carries a Japanese start date of
> **2023-02-24** and **no Global date**, and Global's own scenario guide lists it under "Upcoming"
> with only an **unofficial ~November 2026 estimate**. Korean (2024-06-14) and Traditional Chinese
> (2024-06-26) builds shipped it first; Global did not follow.
>
> Consequences that bind anyone using this file:
>
> 1. **There is no `[Global]` client string for anything below.** Every English term in this file is
>    a third-party or fan rendering, not client text. The Japanese strings in 「」 brackets are the
>    citable ones. This is the reverse of the rule the rest of the repository follows, where the
>    Global client label is the authority.
> 2. **Do not import this file into Global-facing data or copy.** See
>    `docs/UMAMUSUME_REFERENCE.md` §2.4 conflict 6, which this file is the reason remains open.
>    `PRD.md` §6 and the Global-only audience ruling both stand against it.
> 3. **This is reference material for a possible future phase**, written so that the phase does not
>    have to re-derive the mechanics or repeat the terminology errors.
> 4. It is **not** one of the four scenarios `docs/design-research/SCENARIO-DIFFERENCES.md` compares,
>    and no scenario-composition rule (D-220 family) should be widened for it yet.

## Lore framing, for the Guardian

The three names below are, outside the game, the historical foundation lines of a racing breed. **In
the game they are not a breed's ancestors.** The scenario premise is that the **Satono Group**
develops a VR product, 「メガドリームサポーター」, and the three are **support AIs that carry goddess
names**; 「三女神」 ("three goddesses") is Cygames' own wording for them.

- Write them as **the three goddesses** or by name. Never as bloodlines, foundation stock, or any
  breeding framing. <!-- lore-ignore-line class=1 cite=C-4 -->
- **Do not import the English wiki's reward title "Trail of Hooves"**, nor any equine vocabulary <!-- lore-ignore-line class=1 cite=C-4 -->
  attached to this scenario in fan translation. Where a fan rendering contains an equine noun, the <!-- lore-ignore-line class=1 cite=C-4 -->
  Japanese string is the one to quote.
- Note one JP-side spelling variance so a future pass does not "fix" it wrongly: some press renders
  the first goddess **ゴドルフィンアラビアン** rather than ゴドルフィンバルブ.

## The resource chain

「知識の欠片」 → 「知識の結晶」 → 「女神の叡智」, commonly glossed in fan English as Knowledge Fragments →
Knowledge Crystals → Goddess's Wisdom.

⚠️ **Terminology collision to keep straight.** The word "Wisdom" in this file translates 「叡智」, the
third resource tier. It is **not** the stat — 賢さ is **Wit** on `[Global]`, and `CONSTRAINTS.md` D-20
bans the wiki's "Wisdom" label for it. A grep for "Wisdom" that lands here is a false positive for that
rule, and no copy in this file may be reused as a stat label.

| Step | Official rule | Numbers |
|---|---|---|
| Fragments | 「知識の欠片を**2つ**集めると…『知識の結晶』」 | 2 fragments = 1 crystal |
| Fragments → Wisdom | 「知識の欠片を**8つ**集めると…『女神の叡智』」 | 8 fragments = 1 Wisdom, i.e. **8:1** overall |
| Cap | 「知識の欠片は**8個を越えて**集めることはできません」 | Never above **8** fragments held |

The tracker's grid reproduces the same arithmetic: the second row fills at fragment counts
**2 / 4 / 6 / 8** (four crystals) and the third at **4 / 8** (two), so each tier is exactly ×2 the
tier below it. ⚠️ **There is no third "Merged Crystals" tier** — the third row is still 「知識の結晶」.
And ⚠️ 「女神の叡智」 is a **one-time, one-turn effect**, not a spendable currency, so describing it as
"currency" is wrong.

### Colour is the axis, not volume

Each fragment is one of three colours, one per goddess, and the colour decides both which goddess's
knowledge rises and what the payoff is:

| Colour | JP | Goddess | Domain |
|---|---|---|---|
| Red | 赤 | ダーレーアラビアン | 太陽 (sun) |
| Blue | 青 | ゴドルフィンバルブ | 大海 (sea) |
| Yellow | 黄 | バイアリーターク | 大地 (earth) |

- Conversion tier follows colour **majority**: 「欠片→結晶→叡智の変化は、**欠片の色の多さ**で決定」.
- **Lock rule:** 「**1個目と5個目が同じ色**である場合は、その色で確定」 — if the 1st and 5th fragments
  share a colour, the outcome is fixed to it regardless of what follows.
- **Fragments grant stats themselves**, so colour should match the training spread already being
  used, not just the goddess being leveled.

### How fragments are earned

- Any action can drop one; **friendship training guarantees 2**; a **目標レース** (objective race)
  guarantees 2.
- The scenario support card 「祖にして導く者」 post-training events let the player **choose the
  colour**, up to **3 per turn**.

## Per-goddess knowledge: 「女神の知識」, cap **Lv5**

**All three share** a 「トレーニング効果アップ」 ladder that the English wiki table omits entirely:
**+5 / +8 / +11 / +13 / +15%** at Lv1→5.

| Goddess | Signature effect, Lv1 → Lv5 | Second effect |
|---|---|---|
| **ダーレーアラビアン** | 「体力消費ダウン」 **10 / 15 / 18 / 20 / 23%** | — |
| **ゴドルフィンバルブ** | 「ヒント発生率アップ」 **20 / 25 / 30 / 33 / 35%** | 「トレーニング後イベント発生率アップ」**+35** |
| **バイアリーターク** | 「サポートイベントのパラメーター上昇量」 **10 / 15 / 20 / 23 / 25%** | 「サポート連続イベント率アップ」 **20 / 40 / 60 / 80 / 90%** |

⚠️ **Known source defect:** the English wiki assigns these differently (energy reduction → Byerley
Turk, event effect → Darley Arabian, chain chance → Godolphin Barb, hint rate omitted). **Two
independent Japanese guides agree with each other and against it.** Use the JP mapping above; the
disagreement is logged as conflict row 46 in `docs/UMAMUSUME_REFERENCE.md` §7.

## The one-turn activations, 「習得後1ターンだけ」

Official phrasing is "**for one turn only after acquiring**". The three are not equivalent in kind,
and two are commonly overstated in English summaries:

- **ダーレーアラビアン:** 「やる気**+4**・体力**+50**・すべてのトレーニング効果が**トレーニングLv5を超える**・
  レースで得られるステータス上昇 (**レースボーナス +35%**)」. ⚠️ **There is no "Level 6 facility"** —
  facilities still cap at Lv5 (1.1.2), and the effect is that gains *exceed* the Lv5 value. The
  +35% race bonus is routinely dropped from English summaries.
- **ゴドルフィンバルブ:** 「**友人/グループタイプのサポカ**のトレーニング後イベントとヒントイベントの**発生率と効果が上昇**」.
  ⚠️ This is **not** "a hint from every card on the tile". It raises the **rate and magnitude** of
  post-training and hint events for **Pal and Group** type cards specifically.
- **バイアリーターク:** 「サポートカードのウマ娘が**すべてのトレーニングで友情トレーニングを発生させる**」.
  The **facility-type requirement is bypassed** — every support card triggers friendship training on
  any facility. ⚠️ **Whether the bond-gauge ≥ 80 requirement is also bypassed is not stated by any
  source**; English summaries commonly assert both bypasses. Carry the bond bypass as ❌.

## Calendar, gates, and caps

- **December races each year:** 「グロウアップレース」 (Junior), 「WBC」 (Classic), 「SWBC」 (Senior), and
  the final 「グランドマスターズ」, in which 「**三女神全員が出走**」 — all three run in it — with trophies.
- **Fan gates** (`[JP]`): Senior early February **60,000 / 50,000**; early April **70,000 / 60,000**
  *and* 「理事長の絆ゲージが緑以上」; late December **120,000 / 80,000**. Note the second number differs
  from Unity Cup's (50,000 vs 40,000 at the February gate), so the dirt-leaning column is
  scenario-specific and must not be copied between scenarios.
- **Stat caps:** 1500 / 1400 / 1500 / 1300 / 1300 (Speed / Stamina / Power / Guts / Wit). This is now
  **three-source corroborated** and reproduces `1200 + scenarios.json.stats` =
  `[300, 200, 300, 100, 100]` exactly, so it doubles as a check on the cap formula §2.1 of the
  reference guide derives. ⚠️ **Staleness, stated rather than smoothed:** all three agreeing guides are
  `⚠️ STALE` against the 2026-09-27 anchor — 1,168, 902 and 167 days — so this is three old sources
  that never disagreed, not three current ones. The only fresh check is the tier-B export, and under
  the conflict-resolution protocol a B-tier dataset needs A- or S-tier confirmation before a claim
  becomes app data. Because the scenario has never shipped on `[Global]`, no `[Global]`-side source
  can supply that confirmation; **do not promote these five numbers to Global-facing data on the
  strength of this row.**
- **Scenario skills:** 「太陽の叡智」「大海の叡智」「大地の叡智」 — available at that goddess's
  「知識」**Lv4 or higher**, and chosen **the turn before** the final; 「陽の加護」「海の加護」「地の加護」;
  「良バ場の鬼」 with **hint level +1** once **all three** goddesses reach Lv3; and 「全身全霊」.

## What is still not established

- ❌ A full, single-published per-level table for the ゴドルフィン and バイアリー ladders: the Lv5
  columns come from one guide and matching figures from a second, not from one authoritative grid.
- ❌ Whether バイアリーターク's friendship training bypasses the **bond gauge** as well as the type
  requirement.
- ❌ Any Global release date, localization terms, or whether the Global rework pattern (the
  2026-07-01 rebalance) would be folded in at launch.
- ❌ How the scenario behaves under `[Global]`'s halved-gains-above-1200 rule, which is a Global
  mechanics layer and does not exist on the JP side this file describes.

## Sources

[Umamusume JP official scenario page](https://umamusume.jp/contents/game/scenario/grandmasters/)
(live 2026-09-28, tier S) ·
[GameWith 388788](https://gamewith.jp/uma-musume/article/show/388788) 2023-07-17, ⚠️ **STALE** (1,168 days against the 2026-09-27 anchor; tier A) ·
[Game8 510269](https://game8.jp/umamusume/510269) 2026-04-13, ⚠️ **STALE** (167 days; tier A) ·
[Game8 375145](https://game8.jp/umamusume/375145) 2025-11-20, ⚠️ **STALE** (311 days) ·
[Game8 518990](https://game8.jp/umamusume/518990) 2026-09-10 (tier A, fresh) ·
[Kamigame scenario guide](https://kamigame.jp/umamusume/page/251718874821586120.html) 2024-04-08, ⚠️ **STALE** (902 days) ·
[Kamigame 「叡智」](https://kamigame.jp/umamusume/page/251958908782950284.html) and
[「欠片」](https://kamigame.jp/umamusume/page/251963810783478794.html) 2025-06-07 ·
[umamusume.wikiru.jp](https://umamusume.wikiru.jp/) 2023-07-25 (tier B, community wiki) ·
[4Gamer](https://www.4gamer.net/games/414/G041434/20230215083/) and
[Impress](https://game.watch.impress.co.jp/docs/kikaku/1478459.html) 2023-02-23 (launch preview) ·
[umamusu.wiki Game:Grand Masters](https://umamusu.wiki/Game:Grand_Masters) 2026-07-28 — **used here
only as the counter-example**; its English bonus table is the defective row ·
[game8.co Global scenario guide 536350](https://game8.co/games/Umamusume-Pretty-Derby/archives/536350)
2026-07-24, for the "not on Global" status · GameTora `scenarios.json` (fetched 2026-09-27) for the
dates, the absence of `start_en`, and the `stats` array.

Compiled 2026-09-27 from a verification pass on a client-supplied mechanics brief. Nothing in this
file was inferred from another repository document.
