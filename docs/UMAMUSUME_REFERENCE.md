# Umamusume: Pretty Derby: Source-Cited Reference Guide

Compiled 2026-09-27. This is a dated snapshot, not a subscription.

## What this document is

A self-contained reference for Trainers of *Umamusume: Pretty Derby* by Cygames, written so that a player with no prior knowledge can act on it, and so that it can serve as upstream data for a companion tool. Every mechanic, name, date and rate in here is either cited to a source you can open or marked as unverified. Where two sources disagreed, the disagreement is recorded rather than smoothed over: see the Source Conflict Log at the end, which carried 42 rows when this sentence was written and **46** as of 2026-09-27 — counts in prose go stale silently, so re-derive it from the table rather than quoting this line.

**Structure, as of 2026-09-27 — eight sections, not four.** An incoming agent operating manual reproduced a four-section map of this file that has not been true since the scenario work landed; it was a mirror of this preamble's own stale line 9, which said "Four sections" (§ D-285 in `docs/design-research/CONSTRAINTS.md`: a write-up citing this repository is not a second source, and here it was not even that — it was a copy of an out-of-date sentence here). The real contents:

| # | Section | What it holds |
|---|---|---|
| 1 | Core Game Mechanics | 1.1 Training, 1.2 Race, 1.3 Stats, 1.4 Support Cards, 1.5 Inheritance, 1.6 Additional Systems and Game Modes (through 1.6.10 Consumables) |
| 2 | **Scenario Strategies and Mechanics `[Global]`** | The four Global scenarios, per-scenario mechanics, the shared-heuristics ledger, and the numbered conflicts |
| 3 | Character Roster | Debut forms, alternate costume cards, how the tables were built, cross-checks |
| 4 | Live Operations & Events | Current and announced events, both servers |
| 5 | Gacha & Banner Schedule | Banners, rates, banner history |
| 6 | Server Terminology Map | One concept per row, `[JP]` against `[Global]` client wording |
| 7 | Source Attribution and Conflict Log | **46 rows** as of 2026-09-27 (re-derive the count from the table before quoting it; rows 43-46 were added by the mechanics-brief pass) |
| 8 | Self-Audit Report | Per-section confidence, weakest coverage, known gaps, blockers |

The scenario section is the one an ingestion pipeline is most likely to miss and least able to do without: it carries the Global-only rulings, the cap derivation, and the import filter that `docs/scenarios/07` and `08` depend on.

## Two servers, and why that is the first thing to understand

Umamusume runs on separate servers with different content timelines. This document never merges them.

| Tag | Meaning |
|---|---|
| `[JP]` | Japan server, Japanese, the original release. Launched 2021-02-24. At its 5.5th anniversary as of this snapshot. |
| `[Global]` | Worldwide English server, operated by Cygames. Official site `https://umamusume.com/`. First `[Global]` release dates in the roster data begin 2025-06-26, which matches the Cygames English-version availability notice indexed at [cygames.co.jp/en/news/id-24452](https://www.cygames.co.jp/en/news/id-24452), dated 2025-06-26 in the search index. That page returned 403 to this client, so its body was not read here: treat the date as corroborated rather than quoted. |
| `[Both]` | The same content exists on both servers, with the same mechanics. |
| `[JP-Only]` | Live on the Japan server, not yet available on `[Global]`. |

The `[JP]` server runs months ahead of `[Global]`. A `[JP]` banner date is not a `[Global]` banner date, and a `[JP]`-derived projection of future `[Global]` content is not a confirmation. Anything resting on a projection rather than an announcement sits in a labeled `[SPECULATION]` subsection.

The two servers also use different English for identical mechanics: `[Global]` calls the gacha "Scouts" and the friend-type support card "Pal", while `[JP]` uses ガチャ, ピックアップ and 友人. Section 6 maps this vocabulary. Without that map the two servers' data cannot be lined up at all.

## Lore and terminology policy

The Umamusume are a humanoid race. They are never described with equine vocabulary in this document; the banned pattern list is the one this repository owns in `CONSTRAINTS.md` C-4, and this file adds no new term to it. Source material in Japanese, and much community wiki text, uses that vocabulary about the real-world athletes the characters take their names from. It is deliberately not carried across. The real-world careers, birth years, prize earnings and lineage records of those athletes are outside this document's scope; where the underlying data export carries such a field, it is not published here.

Three things a `make lore` run will report here and the reason none is a violation, recorded rather than left for the next person to rediscover:

1. This policy paragraph and the audit at 5.5 name the patterns in order to forbid them, which is what `docs/PRE-MORTEM.md` already does for legacy evidence under the C-4 exemption.
2. Ordinary English and real unit names contain banned substrings: "desired" and the Umamusume named `Red Desire` both contain one of the four-letter patterns, as does "damaged" and the Italian support-card epithet "La dama perfetta". These are substrings, not vocabulary choices.
3. Four em dashes sit inside verbatim skill names copied from the game's own data, for example `Victory belongs to me—Strelitzia! ☆`. Editing a proper noun to satisfy a style rule would corrupt the data it documents.

The grep proposes; the Lore Guardian decides. Japanese katakana names and quoted official notices are source data and are never a lore violation in themselves.

## How to read the flags

| Flag | Meaning |
|---|---|
| `⚠️ STALE: [source] dated [date]` | The source is older than 90 days. The claim may still hold; check it before relying on it. |
| `❌ UNVERIFIED: No current source found. Last known: ...` | Nothing citable supports this. It is not a gap to be filled in by guessing. |
| `[SPECULATION]` | A labeled prediction, from a derived schedule or community forecast. Not an announcement. |
| `[RUMOR]`, `[DATAMINE]` | Unconfirmed or extracted-from-client content. |

Sections 4 and 5 are the fast-decaying parts of this document. Each of their subsections ends with a "how to re-check" pointer naming the exact official page and the exact wiki page that would confirm or overturn it. A month from now, trust the mechanics sections and re-verify the live-ops sections.

## Source registry

Verified during compilation on 2026-09-27. Tiers: S official Cygames, A major community wiki with an editorial process, B database or tool site, C community post requiring corroboration, D rumor.

| Source | URL | Server | Verified state on 2026-09-27 | Tier |
|---|---|---|---|---|
| Umamusume JP official portal, news | https://umamusume.jp/news/?t=game | `[JP]` | Live, rendered in a browser. Newest item 2026.09.26 12:00 | S |
| JP official news article pages | https://umamusume.jp/news/detail?id=3453 | `[JP]` | Live per article id. JS-rendered, plain fetch returns an empty shell | S |
| Umamusume Global official site, news | https://umamusume.com/news/ | `[Global]` | Live, rendered in a browser. Newest item 2026-09-24 22:00 UTC | S |
| Global official character index | https://umamusume.com/characters/ | `[Global]` | Live | S |
| GameTora data export | https://gametora.com/data/manifests/umamusume.json | `[Both]` | Live. Datasets fetched 2026-09-27, page text dated 2026-09-24 | B |
| Kamigame JP wiki | https://kamigame.jp/umamusume/ | `[JP]` | Live. Gacha page 最終更新日 2026-09-22 12:21 | A |
| GameWith JP wiki | https://gamewith.jp/uma-musume/article/show/257332 | `[JP]` | Live. Article dates range 2021 to 2026-09-26, flagged individually | A |
| Game8 EN | https://game8.co/games/Umamusume-Pretty-Derby/archives/537125 | `[Global]` | Live, updated 2026-09-17. States its schedule is derived from JP and adapted | A |
| Game8 JP | https://game8.jp/umamusume/372572 | `[JP]` | Live, pages dated 2025-11-21 to 2026-09-24 | A |
| Umamusume Wiki (MediaWiki) | https://umamusu.wiki/Game:Mechanics | `[Both]` | Live. Game:Mechanics last edited 2026-07-04, Game:Career_Mode 2026-09-08 | A |
| r/UmaMusume banner megathreads | https://www.reddit.com/r/UmaMusume/comments/1w44bo5/en_gacha_banner_megathread_september_2026/ | `[Global]` | Corroboration only, never a sole source | C |

### Corrections to the source list this document was commissioned against

1. `gamewith.jp/umamusume` returns 404. The JP GameWith section lives at `gamewith.jp/uma-musume/`.
2. `umamusume.kamigame.jp` is not the Kamigame path. The live path is `kamigame.jp/umamusume/`.
3. `umamusume.wiki` did not resolve from this machine. The wiki that answered is `umamusu.wiki`. Its `List_of_Characters` page was last revised 2026-02-17, over 90 days old, and it is a franchise list mixing anime-only characters, so it is not the game roster source.
4. `umamusume-db.com` did not resolve at all (no DNS answer). `prydwen.gg/umamusume/` returned 403 to this client and is therefore unused, as is Cygames' corporate news section for the same reason. The brief's "Databases" category is effectively GameTora plus the wikis.
5. Official sites are JS-rendered SPAs. Any tool that fetches them without executing JavaScript sees an empty news list and will conclude there is no data. Every official citation in this document was read through a rendering browser.

## Data export and how to reproduce the tables

Section 3's two tables are generated from one machine-readable export, not typed by hand: [GameTora's data manifest](https://gametora.com/data/manifests/umamusume.json) lists dataset keys with cache-busting hashes, and each key resolves to `https://gametora.com/data/umamusume/<key>.<hash>.json`. The files used are `characters`, `character-cards`, `skills`, `support-cards`, `support_effects`, `races`, `racetracks`, `scenarios`, `ura-races`, `items`, `factors`, the `events/*` and `en/events/*` sets, the `gacha/*` and `en/gacha/*` sets, and `en/foresight/*`. Hashes rotate, so resolve each URL through the manifest rather than hardcoding one.

Wherever a citation in this document reads "GameTora data export, `file.json`", it points at one of those dataset URLs, fetched 2026-09-27.

Aptitude arrays in that export hold ten letters in this order: Turf, Dirt, Sprint, Mile, Medium, Long, then the four strategies. That order was confirmed cell by cell against two independent sources for three diagnostic units before any table was generated. See 2.4.

## Section 1: Core Game Mechanics

### 1.1 Training System

#### 1.1.1 The five training types

A training turn picks one of five disciplines. Four of them spend Energy (体力) for a main stat, a smaller secondary stat, and Skill Points; the Wit discipline spends no Energy and pays out more Skill Points instead [Game8 (2026-09-10)](https://game8.jp/umamusume/372572). Verbatim from that guide: 「ウマ娘のトレーニングは5種類あります。基本は体力を消費して基礎能力やスキルPtが上昇しますが、「賢さ」のトレーニングは体力を消費せず、他のトレーニングよりもスキルPtが多く貰えます」 [Game8 (2026-09-10)](https://game8.jp/umamusume/372572).

| Training (JP / [Global] label) | Main gain | Secondary gain(s) | Skill Pt per session | Energy | Source |
|---|---|---|---|---|---|
| スピード / Speed | Speed +10 to +14 | Power +5 to +7 | +2 | spent | [GameWith base-value table, ⚠️ STALE: dated 2023-02-25](https://gamewith.jp/uma-musume/article/show/257432) |
| スタミナ / Stamina | Stamina +9 to +13 | Guts +4 to +6 | +2 | spent | [GameWith, ⚠️ STALE: 2023-02-25](https://gamewith.jp/uma-musume/article/show/257432) |
| パワー / Power | Power +8 to +12 | Stamina +5 to +7 | +2 | spent | [GameWith, ⚠️ STALE: 2023-02-25](https://gamewith.jp/uma-musume/article/show/257432) |
| 根性 / Guts | Guts +8 to +12 | Speed +4 to +5, Power +4 to +5 | +2 | spent | [GameWith, ⚠️ STALE: 2023-02-25](https://gamewith.jp/uma-musume/article/show/257432) |
| 賢さ / Wit | Wit +9 to +13 | Speed +2 to +4 | +4 to +5 | none, recovers a small amount | [GameWith, ⚠️ STALE: 2023-02-25](https://gamewith.jp/uma-musume/article/show/257432) |

The gain ranges above are the Level 1 to Level 5 spread for one discipline. Secondary-gain routing (Speed feeds Power, Stamina feeds Guts, Power feeds Stamina, Guts feeds Speed and Power, Wit feeds Speed) is confirmed independently by [Game8 (2026-09-10)](https://game8.jp/umamusume/372572) and by [Kamigame, ⚠️ STALE: 2021-10-15](https://kamigame.jp/umamusume/page/146276970408242410.html), so the mapping is `[Both]` in effect even though the numbers were measured on `[JP]`.

#### 1.1.2 Training level and the gain formula

Each discipline levels up after you use it four times, rising to a maximum of Lv5; a higher level costs more Energy and returns a higher gain. During the summer camp window (first half of July to second half of August, four turns) every discipline is set to Lv5 at once [Kamigame, ⚠️ STALE: 2021-10-15](https://kamigame.jp/umamusume/page/146276970408242410.html). [GameWith, ⚠️ STALE: 2023-02-25](https://gamewith.jp/uma-musume/article/show/257432) records the same four-use rule for URA and Climax, and notes that the Unity Cup (アオハル杯) scenario instead ties level to team condition.

Session gain, as published by [Game8 (2025-11-21, ⚠️ STALE)](https://game8.jp/umamusume/454202):

```
gain = (training base value + bonus value)
     × growth-rate modifier (成長率)
     × mood term (1 + mood bonus × Mood Effect / 100)
     × Training Effectiveness (トレーニング効果アップ)
     × Friendship Bonus (友情ボーナス)
     × participant bonus (+5% per additional trainer on the tile)
```

Growth rate (成長率) is the per-stat correction that makes one Umamusume cheaper to train in Speed and another cheaper in Wit: 「成長率は「育成モード」時における、トレーニング効果にかかる補正値です」 [Game8 (2026-09-24)](https://game8.jp/umamusume/372949). The `[Global]` client displays the same figures as percentages on a build sheet, for example "STA: 20%" and "WIT: 10%" [Game8.co (2026-09-25)](https://game8.co/games/Umamusume-Pretty-Derby/archives/536322).

#### 1.1.3 Friendship (rainbow) training and the bond gauge

Friendship training fires when two conditions hold at once: the support card sits on the training tile that matches its own type (得意練習), and its bond gauge (絆ゲージ) has reached the orange stage, which the guides write as gauge value 80 or higher [Game8 (2025-11-21, ⚠️ STALE)](https://game8.jp/umamusume/454202). Verbatim: 「絆ゲージをオレンジ（80）以上にする」 and 「サポートカードのタイプと一致するトレーニングを行うと友情トレーニングが発生します」 [Game8 (2025-11-21, ⚠️ STALE)](https://game8.jp/umamusume/454202). [Kamigame (2022-12-19, ⚠️ STALE)](https://kamigame.jp/umamusume/page/149493615532457367.html) gives the same pair of conditions and adds the pace: the gauge rises about +5 per joint training, so a card with no head start needs roughly the full run to reach orange. [GameWith (2021-02-26, ⚠️ STALE)](https://gamewith.jp/uma-musume/article/show/257606) confirms the orange-plus rule and the type-match rule. The client's own effect copy settles the vocabulary: 「Increases stats gained from friendship (rainbow) training」 [GameTora data export, `support_effects.json` id 1](https://gametora.com/data/umamusume/support_effects.ca447e53.json), so "friendship training" and "rainbow training" name the same event.

Two practical notes for a new player:

1. Support effects move the gauge without you waiting for it. 「初期絆ゲージアップ」 raises the value the card begins the run with [GameTora data export, `support_effects.json` id 14](https://gametora.com/data/umamusume/support_effects.ca447e53.json). The `[Global]` string for that effect is **Initial Friendship Gauge** (`name_en`); "Starting Bond Up" is the export's `name_en_eon` field, an alternate rendering rather than the client label. An earlier revision of this line cited it as `([Global]: Starting Bond Up)`, and that mislabel is where "Bond" entered the terminology gate — see 1.4.3 for the gauge's own wording and `docs/SOURCE-OF-TRUTH.md` §3.
2. The bonus multiplies rather than adds. [Kamigame (2022-12-19, ⚠️ STALE)](https://kamigame.jp/umamusume/page/149493615532457367.html) publishes the shape 基準値 × (1 + 友情ボナA/100) × (1 + 友情ボナB/100), and [Game8 (2025-11-21, ⚠️ STALE)](https://game8.jp/umamusume/454202) states 「友情ボーナスは、キャラごとの値が乗算されるため、複数人いる場合の数値の伸びが非常に大きいです」, with a worked example of 1.25 × 1.3 = 1.625. Stacking two or more rainbow cards on one tile is therefore the single largest multiplier available in a training turn.

Wit friendship training is the exception that changes tempo: it returns Energy instead of taking it. The dedicated support effect 「賢さ友情回復量アップ」 ([Global]: Wit Friendship Recovery) exists only to scale that refund [GameTora data export, `support_effects.json` id 31](https://gametora.com/data/umamusume/support_effects.ca447e53.json), and [Game8 (2025-11-21, ⚠️ STALE)](https://game8.jp/umamusume/454202) states 「賢さの友情トレーニングで多くの体力を回復できます」.

#### 1.1.4 Skill point acquisition

Skill Points (スキルPt) come from four places: training sessions (Wit pays +4 to +5 per session against +2 for the other four, per [GameWith, ⚠️ STALE: 2023-02-25](https://gamewith.jp/uma-musume/article/show/257432)), races finished after a training turn (the payout scales with race difficulty: 「レース難易度が高いほど多くのスキルポイントが貰える」 [Kamigame (2024-04-19, ⚠️ STALE)](https://kamigame.jp/umamusume/page/146441726343540792.html)), training events, and support-card effects that lift either the payout or the discount on a hinted skill. The Race Bonus effect raises the post-race package: 「Increases stat and skill point gains after finishing a race」 [GameTora data export, `support_effects.json` id 15](https://gametora.com/data/umamusume/support_effects.ca447e53.json). Hint level cuts what a skill costs rather than what you earn: 「Lvが高い状態でヒントを貰え、スキル習得に必要なポイントが少なくなる」 [Kamigame (2024-04-19, ⚠️ STALE)](https://kamigame.jp/umamusume/page/146441726343540792.html). Exact per-level discount percentages: ❌ UNVERIFIED: No current source found. Last known: hint level reduces cost, percentage unquantified.

Spending on `[JP]` got a bulk tool on 2026-09-11: the 「スキルセット」 feature lets you sort desired skills into three priority groups (超優先 / 優先 / 通常), share a set by ID, and allocate Skill Points to the whole set in one action [Umamusume JP Official News, 2026.09.11](https://umamusume.jp/news/detail?id=3437). No `[Global]` notice for an equivalent feature appears in the Global news index checked on 2026-09-27 [Umamusume Global Official News index](https://umamusume.com/news/).

#### 1.1.5 Training failure risk and mitigation

Failure probability scales inversely with Energy: 「体力が低いほどトレーニングで失敗する確率が高くなります」 [Game8 (2026-09-10)](https://game8.jp/umamusume/372572). A failure resolves into one of three penalties, in that guide's words 「体力低下・やる気低下・ケガ（能力低下）のいずれかのペナルティが発生します」 [Game8 (2026-09-10)](https://game8.jp/umamusume/372572): Energy loss, mood loss, or an injury that lowers a stat. [GameWith, ⚠️ STALE: 2023-02-25](https://gamewith.jp/uma-musume/article/show/257432) records the older measured range for the stat hit at −5 to −10, plus a random negative label.

Mitigations, from cheapest to most structural:

| Mitigation | How it works | Numbers on record | Source |
|---|---|---|---|
| Rest (お休み) | Free-turn action that refills Energy | +30 Energy per standard rest | [GameWith (2026-09-25)](https://gamewith.jp/uma-musume/article/show/257614) |
| Hold an Energy floor | Risk curve is not linear; it bends around the middle of the gauge | success rate 「大きく変わってくる」 above vs below 50 Energy | [GameWith (2026-09-25)](https://gamewith.jp/uma-musume/article/show/257614) |
| Pick Wit when low | Wit costs no Energy, so it is the low-risk filler turn | Energy cost 0 | [Game8 (2026-09-10)](https://game8.jp/umamusume/372572) |
| Failure Protection (export id 27) | Support-card effect that shrinks the roll on a shared tile | multiplicative reduction (calc: mult) | [GameTora data export, `support_effects.json` id 27](https://gametora.com/data/umamusume/support_effects.ca447e53.json) |
| Energy Cost Reduction effect | Lowers the spend of each session, delaying the danger zone | multiplicative reduction | [GameTora data export, `support_effects.json` id 28](https://gametora.com/data/umamusume/support_effects.ca447e53.json) |
| Mood management | Outings (お出かけ) lift mood, which is a gain multiplier, not a safety switch | see 1.1.6 | [GameWith (2026-09-25)](https://gamewith.jp/uma-musume/article/show/257614) |
| Strategic skip | Decline the turn and rest instead of pushing a red-zone session | no published threshold beyond the 50 Energy guide | [Game8 (2026-09-10)](https://game8.jp/umamusume/372572) |

Whether Wit training can fail at all is not settled across sources; see the Source Conflict Log (row 2).

#### 1.1.6 Motivation (mood) and Energy thresholds

Mood (やる気, `[Global]`: Mood) has five states and modifies both training yield and the attributes the Umamusume carries into a race. The table below is the `[Global]` client's own Mood Effect panel, which prints the tier strings, both effect columns, and marks the tier the unit is currently on [User-Supplied / client Mood Effect panel capture, 2026-09-27]:

| `[Global]` client string | State (JP) | Wiki gloss | Training effect | Pre-race attributes |
|---|---|---|---|---|
| `GREAT` | 絶好調 | peak condition | +20% | +4% |
| `GOOD` | 好調 | good | +10% | +2% |
| `NORMAL` | 普通 | normal | 0% | 0% |
| `BAD` | 不調 | poor | −10% | −2% |
| `AWFUL` | 絶不調 | worst | −20% | −4% |

The training column agrees with the `[JP]` guides, which state the ±20% headline [GameWith (2026-09-25)](https://gamewith.jp/uma-musume/article/show/257614) (「やる気が高いとトレーニングで得られる効果が大きくなり、レース前のステータスも上昇する」, with a 20% peak bonus quoted) and [Game8 (2026-09-10)](https://game8.jp/umamusume/372572). The pre-race column does not: [Kamigame, ⚠️ STALE: 2021-10-15](https://kamigame.jp/umamusume/page/146276970408242410.html) and Game8 print ±10% with the second tier at ±5%, and the client panel prints ±4% and ±2%. That settles the conflict recorded under "New conflict worth logging: the mood race effect" in Section 2 in GameWith's favour and against the two glosses this table previously carried. The five tier strings and the ten percentages are the client's; the two column headers and the "Wiki gloss" column are this document's wording, because the panel's own header labels were not transcribed with the capture.

Exact `[Global]` client strings for the five states: **confirmed** by the client's own panel, as `GREAT` / `GOOD` / `NORMAL` / `BAD` / `AWFUL`. An earlier revision of this section marked them `❌ UNVERIFIED` and used the JP glosses as the English column; the peak and good strings had already been read off mood pills in the screenshot corpus (`docs/design-research/RAW-FINDINGS.md` §4.3), and the panel capture supplies the remaining three plus the arrow that accompanies each tier (up, up, neutral, down, down). Two files already in this repository point the same way as the panel, which is worth recording because neither was read for this purpose: `docs/scenarios/01-ura-finale.md` line 46 states "+20% training stat gains, +4% race performance" at Great mood, and `docs/scenarios/04-trackblazer-umaguide.md` line 183 reports mood affecting stats by "2% per level", which is the panel's second tier. Neither is the client and both are third-hand, so they corroborate rather than confirm.

Energy thresholds worth acting on, all `[JP]`-measured: sessions cost roughly 17 to 28 Energy at the levels where you use them [GameWith, ⚠️ STALE: 2023-02-25](https://gamewith.jp/uma-musume/article/show/257432); rest returns +30 [GameWith (2026-09-25)](https://gamewith.jp/uma-musume/article/show/257614); certain event chains raise the maximum by +12 [GameWith (2026-09-25)](https://gamewith.jp/uma-musume/article/show/257614); and 50 Energy is the practical line where failure risk changes character [GameWith (2026-09-25)](https://gamewith.jp/uma-musume/article/show/257614).

#### 1.1.7 自主トレ育成: the `[JP]` idle training mode

「自主トレ育成」 is an idle mode: you commit a lineup, close the app if you want, and the run plays itself out over a fixed 50 minutes [Game8 (2026-06-30)](https://game8.jp/umamusume/794628) (「育成開始から50分後に自動で育成が進行する放置育成機能」). It is entered from its own tab on the pre-training confirmation screen [Game8 (2026-06-30)](https://game8.jp/umamusume/794628), where you set a focus policy from three options (バランス重視 / スタミナ重視 / スプリント重視), an optional custom race rotation that defaults to the scenario's target races, and up to 10 priority skill hints [Game8 (2026-06-30)](https://game8.jp/umamusume/794628). [Kamigame (2026-07-20)](https://kamigame.jp/umamusume/page/429951462450165905.html) adds the practical profile: it is a fan, jewel, trainer medal, 優勝レイ and factor farm, roughly 1.3 million fans per completed run, and it recommends the balanced policy because the alternatives skew stats and cost win rate.

Its limits matter for expectations. Stat growth modifiers are ignored, so an idle run never approaches a manual ceiling [Game8 (2026-06-30)](https://game8.jp/umamusume/794628). Race results are decided from aptitude and how many turns in a row you entered, with win rate falling after three consecutive entries [Kamigame (2026-07-20)](https://kamigame.jp/umamusume/page/429951462450165905.html). Wins in this mode award no trophies and unlock no Live Theater or Gallery entries [Game8 (2026-06-30)](https://game8.jp/umamusume/794628). You cannot take manual control partway through [Kamigame (2026-07-20)](https://kamigame.jp/umamusume/page/429951462450165905.html).

Official confirmation of the mode and its rough edges comes from two `[JP]` notices. The first, dated 2026.06.29 15:34 with a 2026.06.30 12:20 addendum, reports that starting a 自主トレ育成 run with one specific support card produced 「エラーコード：102」 at completion, with a failure window of 6/29 12:00 to 6/29 15:31 and compensation of one Toughness 30 per deleted run [Umamusume JP Official News, 2026.06.29](https://umamusume.jp/news/detail?id=3322). The second, dated 2026.09.25 17:03, reports that certain 育成イベント inside 自主トレ育成 could not grant their rewards correctly between 2026/6/29 12:00 and 2026/9/25 15:34, with Toughness 30 ×4 sent as compensation; the affected events named in the notice include Seiun Sky's 「釣果アリ」 and the `[Reines Plätschern]` Eishin Flash events 「最適なスケジュール」 and 「彼の都の思い出は」 [Umamusume JP Official News, 2026.09.25](https://umamusume.jp/news/detail?id=3476). Both compensations are the same item, and its client text is narrow: 「使用するとTPが30回復する」 / "Restores 30 TP", obtained from limited missions, limited events and promo codes [GameTora data export, `items.json` id 32](https://gametora.com/data/umamusume/items.e9746f0c.json). What TP governs: ❌ UNVERIFIED: no current source defines it, and the export only records that paid Carats refill "TP or RP" alongside it [GameTora data export, `items.json` id 43](https://gametora.com/data/umamusume/items.e9746f0c.json). Do not read TP as the training Energy bar used in 1.1.6.

Daily limits: [Kamigame (2026-07-20)](https://kamigame.jp/umamusume/page/429951462450165905.html) states 20 runs per day, counting 自主トレ育成 toward the pre-existing cap on free jewels from training race rewards; [Game8 (2026-06-30)](https://game8.jp/umamusume/794628) documents no cap on the mode itself. See Source Conflict Log row 1.

#### 1.1.8 Server differences in training

The training core (five disciplines, level curve, mood multipliers, friendship trigger, failure penalties) is reported without server divergence by every source found, and each figure above carries the server it was measured on. Real differences are in features and vocabulary:

* 自主トレ育成, 育成プランシート and スキルセット are `[JP]` features announced in the 5.5th anniversary cycle [Umamusume JP Official News, 2026.09.11](https://umamusume.jp/news/detail?id=3437); the `[Global]` index carries no equivalent item [Umamusume Global Official News index](https://umamusume.com/news/).
* Roster depth behind the same rules, from the GameTora data export: 268 trainable Umamusume cards on `[JP]` (releases 2021-02-24 through 2026-09-18) against 105 carrying a Global release date (latest 2026-09-24); 559 support cards on `[JP]` against 251 with Global dates [GameTora data export, `character-cards.json` and `support-cards.json`](https://gametora.com/data/umamusume/character-cards.679f7c2e.json).
* Vocabulary on `[Global]` is Cygames' own: "Scouts" for the gacha, "Transfer Requests", and "Veteran Umamusume" for a finished trainee [Umamusume Global Official News, 2026-09-19](https://umamusume.com/news/1050), versus ガチャ / ピックアップ, セレクト and 殿堂入り on `[JP]` [Umamusume JP Official News, 2026.09.25](https://umamusume.jp/news/detail?id=3476).

**Sources:** Game8 `[JP]` training and stat guides (2026-09-10, 2026-09-24, tier A); Game8 friendship and 自主トレ pages (2025-11-21, 2026-06-30, tier A); GameWith training base values, training loop, friendship and outing pages (2026-09-25, 2023-02-25, 2021-02-26, tier A); Kamigame training types, friendship, skill points, 自主トレ (2026-07-20, 2022-12-19, 2021-10-15, 2024-04-19, tier A); Game8.co Global build guide (2026-09-25, tier A); Umamusume JP official news ids 3322, 3437, 3476 (tier S); Umamusume Global official news index and item 1050 (tier S); data export `support_effects.json`, `items.json`, `character-cards.json`, `support-cards.json` (GameTora-derived, tier B).

### 1.2 Race Mechanics

### 1.2.1 Surfaces and distance bands `[Both]`

Two surfaces exist: `ground_type` / `terrain` 1 and 2. The client's own text pins them: the event skill 「ダートレースへの想い」 (Dirt Race Enthusiast) requires `ground_type==2` and reads "Increase performance in medium-distance dirt races", and 「マイルレースへの想い」 (Mile Race Enthusiast) requires `ground_type==1` and reads "Increase performance in Mile Turf races" [GameTora data export, `skills.json` ids 1200051 and 1200031](https://gametora.com/data/umamusume/skills.f4a1e02d.json). [Global] official copy shows the same pair in a race-condition line: "Turf / 2,200m (Medium) / Right-Handed / Outer / Autumn / Sunny / Firm" [Umamusume Global Official News, 2026.09.19](https://umamusume.com/news/1050), and `[JP]` shows 「芝 1800m（マイル） 左 秋 晴 良 昼」 for the current Champions Meeting round [Umamusume JP Official News, 2026.09.18](https://umamusume.jp/news/detail?id=3453). Across the 322 race rows: 230 turf, 92 dirt [GameTora data export, `races.json`](https://gametora.com/data/umamusume/races.55dde7c9.json).

Four distance bands, decoded from `distance_type==1..4` against the client skill names 短距離 / マイル / 中距離 / 長距離 and their [Global] description tags "(Sprint)", "(Mile)", "(Medium)", "(Long)" (e.g. `skills.json` ids 200961, 201031, 201101, 201171). Metre spans are the course lengths in the export, not a wiki's prose range.

| Band | `course.distance` (equivalent to the `distance_type` condition key in `skills.json`) | Course metres in export | Distances actually raced | [JP] label | [Global] label | Race rows |
|---|---|---|---|---|---|---|
| Sprint | 1 | 1000, 1150, 1200 m | 1000, 1200 m | 短距離 | Sprint | 50 |
| Mile | 2 | 1300 to 1800 m | 1400, 1500, 1600, 1700, 1800 m | マイル | Mile | 171 |
| Medium | 3 | 1900 to 2400 m | 1900, 2000, 2100, 2200, 2400 m | 中距離 | Medium | 84 |
| Long | 4 | 2500 to 3600 m | 2500, 2600, 3000, 3200, 3400, 3600 m | 長距離 | Long | 17 |

Boundaries are confirmed by the [Global] official line "(Medium)" at 2,200 m and the `[JP]` line 「（マイル）」 at 1800 m. The 1400 m boundary is contested: see Conflict Log row 7.

Two facts the bands control. First, field size: 18 runners on 152 rows, 16 on 134, 14 on 23, and the remaining 13 rows split across 15, 20, 9, 17, 12 and 5 runners [GameTora data export, `races.json`](https://gametora.com/data/umamusume/races.55dde7c9.json); `[JP]` Champions Meeting rounds run 9 runners per race ("9人出走のレース") [Umamusume JP Official News, 2026.09.18](https://umamusume.jp/news/detail?id=3453). Second, distance roundness: 「根幹距離◎」 (Standard Distance ◎) requires `is_basis_distance==1` and the client defines the flag itself, "Increase performance over standard distances (multiples of 400m)", with 「非根幹距離」 covering non-multiples [GameTora data export, `skills.json` ids 200131 and 200141](https://gametora.com/data/umamusume/skills.f4a1e02d.json). 180 of the 322 race rows are multiples of 400 m, 142 are not.

**Sources:** data export `races.json`, `racetracks.json`, `skills.json` (tier B); [Umamusume Global Official News 1050, 2026.09.19](https://umamusume.com/news/1050) (tier S); [Umamusume JP Official News id 3453, 2026.09.18](https://umamusume.jp/news/detail?id=3453) (tier S); [Umamusume Wiki Career Mode, last edited 8 September 2026](https://umamusu.wiki/Game:Career_Mode) (tier A).

### 1.2.2 The four running strategies `[Both]`, labels server-specific

The engine stores exactly four values, `running_style==1..4`. The `[Global]` labels are not literal translations of the `[JP]` terms, and the export carries both a client label (`name_en`) and a separate literal rendering (`enname`), which is why the two must never be quoted as one set.

| `running_style` | [JP] term | [Global] client label | Literal EN rendering in export | Skills gated to it | Where the toolkit sits |
|---|---|---|---|---|---|
| 1 | 逃げ | Front Runner | Runner (逃げのコツ◎ = "Runner's Tricks ◎") | 107 | phase 0 and phase 1, lead-holding |
| 2 | 先行 | Pace Chaser | Leader (先行のコツ◎ = "Leader's Tricks ◎") | 220 | mid-race, largest pool |
| 3 | 差し | Late Surger | Betweener (差しのコツ◎ = "Betweener's Tricks ◎") | 166 | late-race move, order_rate gated |
| 4 | 追込 | End Closer | Chaser (追込のコツ◎ = "Chaser's Tricks ◎") | 115 | last spurt, 18 of the 121 skills whose condition text pins `running_style==4` |

Counts and label pairs are from the export, skills whose condition set pins exactly one `running_style` value [GameTora data export, `skills.json`](https://gametora.com/data/umamusume/skills.f4a1e02d.json). The [Global] label set is confirmed independently by the guide pages: Front Runner, Pace Chaser, Late Surger, End Closer [game8.co Running Style Differences, ⚠️ STALE: dated 2025-10-05](https://game8.co/games/Umamusume-Pretty-Derby/archives/543935), and by the per-style pages refreshed inside the recency window: [game8.co Front Runner Guide (2026.08.24)](https://game8.co/games/Umamusume-Pretty-Derby/archives/544485), [game8.co Pace Chaser Guide (2026.09.01)](https://game8.co/games/Umamusume-Pretty-Derby/archives/544592), [game8.co Late Surger Guide (2026.09.01)](https://game8.co/games/Umamusume-Pretty-Derby/archives/544747), [game8.co End Closer Guide (2026.08.24)](https://game8.co/games/Umamusume-Pretty-Derby/archives/544879).

`[JP]` phase behaviour, verbatim: 「逃げはスタートからゴールまで先頭を走る」; 先行 「最後の直線でスパートをかけ1位を狙っていく」; 差し 「最終コーナー辺りから追い上げるタイプだ」; 追込 「最後の直線から加速して先頭集団を抜いていく」 [Kamigame running styles, ⚠️ STALE: dated 2024-04-10](https://kamigame.jp/umamusume/page/144371082126720308.html).

What the label actually changes, mechanically:

1. Skill eligibility. A skill whose condition contains `running_style==N` cannot fire for any other style, so the label rewrites the usable skill list, not a hidden speed curve. The 先行のコツ family reads "Increase ability to get into a good position. (Pace Chaser)" in the client's own copy, which is the position mechanism the label gates [GameTora data export, `skills.json` ids 201521 to 201552](https://gametora.com/data/umamusume/skills.f4a1e02d.json).
2. Terrain interaction. Uphill skills are Front Runner flavored and downhill skills are End Closer flavored: 「勢い任せ」 (Moxie) is `running_style==1&slope==1&accumulatetime>=10`, "Slightly reduce fatigue on an uphill. (Front Runner)", and 「下校後のスペシャリスト」 (Go-Home Specialist) is `running_style==4&slope==2&accumulatetime>=10`, "Reduce fatigue on a downhill. (End Closer)". The two states are mutually exclusive, so the style label decides which of the two terrain toolkits a Umamusume can bring.
3. Position vocabulary. The client maps `order_rate` to plain text per skill copy: 50 or lower as "positioned toward the front", 40 to 80 or 50 to 80 as "midpack", 50 or higher as "toward the back", and the engine converts the percentage to an absolute placing, "Order rate condition is converted to order condition, rounding to the nearest" [Umamusume Wiki Mechanics, last edited 4 July 2026](https://umamusu.wiki/Game:Mechanics).

「大逃げ」 is not a fifth label: the export gates it behind `running_style==1`, [Global] name "Runaway", while [game8.co](https://game8.co/games/Umamusume-Pretty-Derby/archives/543935) and [Umamusume Wiki Career Mode, last edited 8 September 2026](https://umamusu.wiki/Game:Career_Mode) list "Runaway" beside the four styles. See Conflict Log row 9.

**Sources:** data export `skills.json` (tier B); [Umamusume Wiki Mechanics (2026.07.04)](https://umamusu.wiki/Game:Mechanics) (tier A); [Kamigame (2024.04.10, stale)](https://kamigame.jp/umamusume/page/144371082126720308.html) (tier A); game8.co style guides [543935 (2025.10.05, stale)](https://game8.co/games/Umamusume-Pretty-Derby/archives/543935), [544485 (2026.08.24)](https://game8.co/games/Umamusume-Pretty-Derby/archives/544485), [544592 (2026.09.01)](https://game8.co/games/Umamusume-Pretty-Derby/archives/544592), [544747 (2026.09.01)](https://game8.co/games/Umamusume-Pretty-Derby/archives/544747), [544879 (2026.08.24)](https://game8.co/games/Umamusume-Pretty-Derby/archives/544879).

### 1.2.3 How a race resolves: four phases `[Both]`

Every one of the 138 course definitions in the export carries exactly four phases, `phases[].id` 0 to 3, split at fixed fractions of the race: 16.67%, 66.67%, 83.33%, 100% (57 courses land on exactly those three fractions, and all 138 are within 0.1% of them). The same model is documented as 24 sections: "Opening Leg (0): Section 1 to 4. Middle Leg (1): Section 5 to 16. Final Leg (2): Section 17 to 20. Last Spurt (3): Section 21 to 24" [Umamusume Wiki Mechanics, last edited 4 July 2026](https://umamusu.wiki/Game:Mechanics), and 4/24, 16/24 and 20/24 are exactly the export's three boundaries.

| Phase | `phase` id | Sections | Share of race | What is decided | Export anchor |
|---|---|---|---|---|---|
| Opening Leg | 0 | 1 to 4 | first 1/6 | break from the gate and the early lead contest | 24 skill records gate on `phase==0`, incl. `blocked_front_continuetime` |
| Middle Leg | 1 | 5 to 16 | 1/6 to 2/3 | position holding, then the position battle | `positionKeepEnd` inside phase 1 in all 138 courses |
| Final Leg | 2 | 17 to 20 | 2/3 to 5/6 | the move (仕掛け) begins | `spurtStart` inside the course's own phase 2 window in all 138 courses; only 95 of 138 also sit at or above the mathematical two-thirds point |
| Last Spurt | 3 | 21 to 24 | last 1/6 | who holds on | `is_lastspurt==1` on 119 skill records |

Opening: the break is modeled as lost time, not as a stat. 「集中力」 (Focus) is "Slightly decrease time lost to slow starts" and 「ゲート難」 (Gatekept) is "Moderately increase time lost to slow starts", both `always==1`; a separate flag `is_badstart` gates skills such as 「抜かりなし」 (All Set), whose copy reads "If the skill user began the race without a late start, greatly recover endurance in the early part of mid-race".

Holding a position: `positionKeepEnd` sits between 41.64% and 41.70% of race length in all 138 courses, which is the section-10 boundary, and the wiki states the rule for that window: "Position keeping affects target speed between section 1 to 10". After it, position is defended move by move, and the engine exposes the contest as counters that skills read: `compete_fight_count` (position battles fought), `is_overtake`, `change_order_onetime`, `blocked_front_continuetime`, `blocked_side_continuetime`, and lane state `is_move_lane==1|2` with `lane_type==0` for the inside [GameTora data export, `skills.json`](https://gametora.com/data/umamusume/skills.f4a1e02d.json). "When in normal mode, a check to enter non-normal modes is performed every 2 seconds" [Umamusume Wiki Mechanics, last edited 4 July 2026](https://umamusu.wiki/Game:Mechanics).

Final approach: `spurtStart` never lands in the Last Spurt itself; it is set inside Final Leg, and its `location` names the trigger geometry: 53 of 138 courses start it in the final corner, 35 in a corner with no other tag, 21 on a straight with no other tag, and 37 tag it as uphill or downhill and 8 more carry a `final_straight` tag; the groups overlap, and between them they cover all 138 courses. This is why the style guides describe 差し as starting the move at the final corner.

Closing: endurance decides who holds. "Stamina is converted to HP at the start of a race", and "Last spurt calculation estimates the stamina usage up to 60m before the goal line", with the spurt released when "the trainee has enough HP to run the remaining distance" [Umamusume Wiki Mechanics, last edited 4 July 2026](https://umamusu.wiki/Game:Mechanics). The skill set mirrors that gate: 119 skill records (159 condition occurrences) carry `is_lastspurt==1`, and `hp_per` (remaining endurance as a percentage) appears in conditions such as `hp_per>=30`.

**Sources:** data export `racetracks.json`, `skills.json` (tier B); [Umamusume Wiki Mechanics, last edited 4 July 2026](https://umamusu.wiki/Game:Mechanics) (tier A); [Kamigame running styles, ⚠️ STALE: 2024.04.10](https://kamigame.jp/umamusume/page/144371082126720308.html) (tier A); [game8.co Late Surger Guide (2026.09.01)](https://game8.co/games/Umamusume-Pretty-Derby/archives/544747) (tier A).

### 1.2.4 Course factors `[Both]`

Corners. Courses carry 0 to 8 corner segments, exactly one course (id 10301, distance code 1) being a straight with no corner segments at all, and the count scales with the band: exactly 2 segments on 47 of 50 Sprint race rows, 2 to 4 on Mile, 2 to 6 on Medium, 4 to 8 on Long. Corner arc lengths run 100 to 500 m, and the final corner (segment `number` 4) is 100 to 350 m. Skill triggers read the specific corner through `corner_random==1..4` (for example 「円弧のマエストロ」 / Swinging Maestro, "Recover endurance on a corner with efficient turning", cost 170 SP) and 「型破り」 (Break the Mold) fires on "a third corner just before the final corner", `corner_random==3`. All corners enter as candidates: "all corners in the race start as candidate corners" [Umamusume Wiki Mechanics, last edited 4 July 2026](https://umamusu.wiki/Game:Mechanics). A separate course flag gates 「小回り◎」 (Sharp Turns ◎): `is_tight_track==1`, "Increase performance on tracks with sharp turns".

Straights. 1 to 5 per course, lengths 141 to 1000 m. The closing straight is always the stand-side straight (`frontType` 1 in all 138 courses), the opposite side is `frontType` 2, confirmed by 「Do Ya Breakin!」 (Break It Down!), which fires on `straight_front_type==2` and reads "If it's the backstretch, also slightly increase acceleration". `frontType` 3 appears once: ❌ UNVERIFIED: No current source found. Last known: one straight segment in the export carries value 3.

Elevation. 85 of 138 courses carry slope bands; slope magnitudes in the export are ±10000, ±15000, ±20000 and the longest single band spans 800 m. The engine's state is `slope` 0 flat, 1 uphill, 2 downhill, decoded from client copy ("Reduce fatigue on an uphill" on `slope==1`; "Reduce fatigue on a downhill" on `slope==2`). Effect on the outcome: "loses target speed equal to SlopePer∗200/PowerStat", so Power buys back uphill cost, and the downhill branch is luck-based rather than fixed, "Declining routes offer a probability-based state dependent on cognitive attributes" (Wit) [Umamusume Wiki Mechanics, last edited 4 July 2026](https://umamusu.wiki/Game:Mechanics). The unit of `SlopePer` and the export's 10000-scale: ❌ UNVERIFIED: No current source found. Last known: values ±10000, ±15000, ±20000 on 226 slope bands.

Surface change mid-race. 10 of 138 courses define `terrainChanges`, and 30 race rows sit on them: the segment list switches surface at a metre mark (for example turf from 0 m, dirt from 60 m on course 10310).

Track condition as a course factor is covered in 1.2.5. Venue-gated skills exist too: `track_id==10005` and `track_id==10006` appear in conditions, and the [Global] copy of one such skill grants its bonus effect only when the race is run at the venue that `track_id==10006` names, so a course id is a mechanical input, not scenery [GameTora data export, `skills.json` id 110081](https://gametora.com/data/umamusume/skills.f4a1e02d.json).

**Sources:** data export `racetracks.json`, `races.json`, `skills.json` (tier B); [Umamusume Wiki Mechanics, last edited 4 July 2026](https://umamusu.wiki/Game:Mechanics) (tier A); [GameWith course data index, ⚠️ STALE: dated 2026-03-05](https://gamewith.jp/uma-musume/article/show/287842) (tier A, 32 catalogued layouts with straight, corner and gradient intervals).

### 1.2.5 Weather and ground state `[Both]`

Weather codes decode off the client's own skill names: `weather==1` 「晴れの日」 Sunny Days, 2 「曇りの日」 Cloudy Days, 3 「雨の日」 Rainy Days, 4 「雪の日」 Snowy Days [GameTora data export, `skills.json` ids 200211, 200221, 200231, 200241](https://gametora.com/data/umamusume/skills.f4a1e02d.json). Ground (footing) codes decode off the same source, and the client enumerates the whole set in one line: 「道悪◎」 (Wet Conditions ◎) covers `ground_condition` 2, 3 and 4 and its copy reads "Increase performance on good, soft, and heavy ground", while 「良バ場◎」 (Firm Conditions ◎) is `ground_condition==1` [GameTora data export, `skills.json` ids 200151 and 200161](https://gametora.com/data/umamusume/skills.f4a1e02d.json).

| Code | [JP] term | [Global] label | Speed modifier, Turf | Speed modifier, Dirt |
|---|---|---|---|---|
| 1 | 良 | Firm | 0 | 0 |
| 2 | 稍重 | Good | 0 | 0 |
| 3 | 重 | Soft | 0 | 0 |
| 4 | 不良 | Heavy | -50 | -50 |

Modifier row quoted as "Firm Good Soft Heavy / Turf 0 0 0 -50 / Dirt 0 0 0 -50" [Umamusume Wiki Mechanics, last edited 4 July 2026](https://umamusu.wiki/Game:Mechanics). Stamina side of footing: ❌ UNVERIFIED: No current source found. Last known: the wiki table only lists a Speed row.

Weather and footing are linked, one directionally. `[JP]` guidance states that on a rainy day the footing 「必ず重か不良になる」 (always becomes 重 or 不良) [Kamigame rain and heavy footing, ⚠️ STALE: dated 2023-06-06](https://kamigame.jp/umamusume/page/182514818417216449.html), and the event data agrees: all 5 `[JP]` and both `[Global]` Champions Meeting rounds with `weather==3` carry `condition` 3 or 4, never 1 or 2 [GameTora data export, `events__champions-meeting.json`, `en/events/champions-meeting`](https://gametora.com/data/umamusume/events/champions-meeting.ccf98e59.json). The reverse does not hold: `condition==3` also occurs under `weather==2`, and the next `[JP]` Champions Meeting round (ending 2026-10) is `weather==1`, `condition==4` (不良) [GameTora data export, `events__champions-meeting.json` id 49](https://gametora.com/data/umamusume/events/champions-meeting.ccf98e59.json). Snow (`weather==4`) occurs only under `season==4` (冬) in all 4 sampled rounds across both servers.

Skills gated to a specific combination, from the export's condition strings, all `[Both]`:

| Skill ([JP] / [Global]) | Trigger combination | Cost, SP |
|---|---|---|
| 泥遊び◎ / Muddy ◎ (202342) | `ground_type==2` and `ground_condition` 3 or 4, dirt plus soft or heavy | 110 |
| 泥んこマイスター / Maestro of the Mud (202341) | same pair, plus a Speed and Power bonus | 130 |
| ターフの主人公 (107701111) | `ground_condition==1` and `ground_type==1`, turf plus firm | no cost field, rarity 6 |
| ナイター◎ / Night Races ◎ (202231) | `time==4`, night meeting | 90 |
| 交流重賞◎ / Collaborative Graded Races ◎ (202251) | `is_dirtgrade==1` | 90 |

The [Global] Champions Meeting edition announced 2026.09.19 disables exactly the three course-factor lines, "The following skills will not activate during this Scorpio Cup: Night Races ◎/○/×, Sharp Turns ◎/○/×, Collaborative Graded Races ◎/○/×" [Umamusume Global Official News, 2026.09.19](https://umamusume.com/news/1050), which confirms all three flags are live mechanics. Time of day: 13 of 322 race rows carry `time==4` (4 on grade 100, 5 on grade 200, 4 on grade 300). Values 2 (299 rows) and 3 (10 rows): ❌ UNVERIFIED: No current source found. Last known: the `[JP]` official condition line ends with 「昼」, which is the state the export's dominant value 2 would represent. Skill effect magnitudes are stored as raw integers (600000 for a ◎ bonus, 400000 for ○, -400000 for ×): ❌ UNVERIFIED: No current source found. Last known: that triple in `skills.json` effect payloads.

**Sources:** data export `skills.json`, `races.json`, `events__champions-meeting.json`, `en/events/champions-meeting` (tier B); [Umamusume Wiki Mechanics, last edited 4 July 2026](https://umamusu.wiki/Game:Mechanics) (tier A); [Kamigame (2023.06.06, stale)](https://kamigame.jp/umamusume/page/182514818417216449.html) (tier A); [Kamigame Firm Conditions skill page (2026.09.24)](https://kamigame.jp/umamusume/page/147176378167461059.html) (tier A, confirms 「良」 footing naming and the 90 SP cost for 良バ場○); [Umamusume Global Official News 1050 (2026.09.19)](https://umamusume.com/news/1050) (tier S).

### 1.2.6 Grade tiers and entry gates `[Both]`

The export stores five grade codes, 100 (55 rows), 200 (46), 300 (76), 400 (119), 700 (26). `grade==100` is client-confirmed as the top tier: 「GⅠ苦手」 is named "G1 Averseness" and its copy reads "Moderately decrease performance in G1 or otherwise important races" [GameTora data export, `skills.json` id 200311](https://gametora.com/data/umamusume/skills.f4a1e02d.json). The tier label set is documented as "Pre-OP, OP, G3, G2, and G1" [Umamusume Wiki Career Mode, last edited 8 September 2026](https://umamusu.wiki/Game:Career_Mode), and `[JP]` official copy uses 「グレードリーグ」 for the top-tier league [Umamusume JP Official News, 2026.09.18](https://umamusume.jp/news/detail?id=3453). Three rows in the export whose in-game names contain 「オープン」 all carry grade 400, which pins 400 as the Open tier by the client's own naming. Codes 200, 300 and 700 have no cited label map: ❌ UNVERIFIED: No current source found. Last known: the descending fan requirement across the codes matches the tier order G1 to OP, and 24 of the 26 grade-700 rows carry a 賞 suffix instead of ステークス, which fits but does not prove a Pre-OP reading.

Entry is gated twice, by fan count and by race goals.

| Grade code | Race rows in the URA schedule | `fans_needed` range | `fans_gain` range |
|---|---|---|---|
| 100 (G1) | 48 | 1,000 to 25,000 | 20 to 54 |
| 200 | 58 | 375 to 2,000 | 13 to 56 |
| 300 | 108 | 350 to 1,500 | 9 to 53 |
| 400 (OP) | 164 | 350 | 5 to 50 |
| 700 | 26 | 350 | 44 |

Ranges are per-row values in the export's URA race schedule joined to the grade on each race [GameTora data export, `ura-races.json` `fans_needed` and `fans_gain`, joined to `races.json` by `instance` and `id`](https://gametora.com/data/umamusume/ura-races.c12e8867.json); the mechanism behind the column is documented: "To participate in a race, the trainee needs to have accumulated the required number of fans; races with higher ratings generally require a larger number of fans" [Umamusume Wiki Career Mode, last edited 8 September 2026](https://umamusu.wiki/Game:Career_Mode). The monotone drop from 1,000 to 350 across the codes is what the tier order predicts, which is the evidence for the 100 to 400 mapping.

Race goals gate the chain, and the client states it: "The Debut Race is mandatory for every character"; "If you don't win the Debut, you're gonna have to win any Maiden Race before participating in any standard races"; "You can't participate in any races listed here until you win either Debut or any of the Maiden Races" [GameTora data export, `ura-races.json` description blocks for ids `debut` and `maiden`](https://gametora.com/data/umamusume/ura-races.c12e8867.json). Campaign objectives are "getting a minimum placing in a specific race or acquiring a set amount of fans before a set date", and a missed mandatory placing can be retried "by using an Alarm Clock" [Umamusume Wiki Career Mode, last edited 8 September 2026](https://umamusu.wiki/Game:Career_Mode).

Competitive entry is graded by career rank, and the two servers set different ceilings on the lower league:

* `[Global]` Open League: "Only Veteran Umamusume with a Career Rank of A+ or below can enter" and "Veteran Umamusume with a Career Rank of S or above cannot participate in the Open League"; Graded League has no rank limit [Umamusume Global Official News, 2026.09.19](https://umamusume.com/news/1050).
* `[JP]` Open League: 「育成ランク[UC]まで出走登録可能」 with 「育成ランク[UC1]以上はオープンリーグへ出走登録できません」; グレードリーグ has no limit [Umamusume JP Official News, 2026.09.18](https://umamusume.jp/news/detail?id=3453).

Server coverage, from the export: 13 of 322 race rows are flagged `unreleased_servers: ['en']` (10 on grade 100, 3 on grade 200), so those are `[JP]`-only as of the 2026-09-27 dump; 17 rows are flagged `did_not_exist: 'pre_nar'` and 6 `pre_2_5th_anni`, meaning they were added or reshaped by those `[JP]` updates. `[JP]` Champions Meeting rounds have been named by distance band (SPRINT, MILE, CLASSIC, LONG, DIRT) since round 25 in June 2023, while `[Global]` still uses the zodiac names (Scorpio Cup as of 2026.09.19) [GameTora data export, `events__champions-meeting.json`, `en/events/champions-meeting`](https://gametora.com/data/umamusume/events/champions-meeting.ccf98e59.json).

**Sources:** data export `races.json`, `ura-races.json`, `skills.json`, `events__champions-meeting.json`, `en/events/champions-meeting` (tier B); [Umamusume Wiki Career Mode, last edited 8 September 2026](https://umamusu.wiki/Game:Career_Mode) (tier A); [Umamusume Global Official News 1050 (2026.09.19)](https://umamusume.com/news/1050) (tier S); [Umamusume JP Official News id 3453 (2026.09.18)](https://umamusume.jp/news/detail?id=3453) (tier S).

### 1.3 Stat System

#### 1.3.1 What each stat governs

All five statements below are quoted from [Game8 (2026-09-24)](https://game8.jp/umamusume/372949), which is the freshest mechanic-level source found `[JP]`.

| Stat (JP / EN) | Governs | Documented behaviour gate |
|---|---|---|
| スピード / Speed | Highest velocity reached in the closing stage of the race; the stat most directly tied to the result | Above 2000 the 「全開スパート」 (full spurt) behaviour can activate |
| スタミナ / Stamina | The endurance pool drained across the race; the longer the course, the more of the race it carries | Above 1200 the 「スタミナ勝負」 (endurance duel) behaviour becomes available |
| パワー / Power | Acceleration and course-taking, including how easily the Umamusume escapes a blocked lane; also speed during repositioning and acceleration out of a full spurt | Above 1200 「脚をためる」 (saving her legs) becomes available |
| 根性 / Guts | Tenacity once endurance is spent in the last spurt; stronger in late stretch duels, and for Front Runners also in the early battle for position | no published numeric gate |
| 賢さ / Wit | Resistance to 掛かり (running hot and burning endurance), skill activation rate, and activation of 「位置取り調整」, 「リード確保」 and 「持久力温存」 | Above 1200, the speed-up and forward-push portions of unique, evolved and rare skills are strengthened |

#### 1.3.2 Speed and the race phases

A race is simulated in phases, and the skill layer keys on phase flags rather than on stat comparisons: the local skill export carries conditions such as `phase>=2`, `phase_random`, `is_lastspurt==1` and `is_last_straight==1` across its 1,910 entries, and the matching `[Global]` skill text reads "on the final straight" [GameTora data export, `skills.json`](https://gametora.com/data/umamusume/skills.f4a1e02d.json). That structure is why Speed is described as a closing-stage stat rather than an all-day one: it sets the ceiling she can hold in the final phase, while Power decides how fast she reaches it and Stamina decides whether she still has endurance left to spend when she gets there [Game8 (2026-09-24)](https://game8.jp/umamusume/372949). The 2000 Speed mark is therefore both a cap question and a behaviour question: it is the point where the full-spurt behaviour switches on, and it is also the ceiling recorded in the scenario data (1.3.4).

#### 1.3.3 Stamina and race length

Distance bands are fixed and published: Sprint is 1400 m and under, Mile 1401 to 1800 m, Medium 1801 to 2400 m, Long 2401 m and up [Game8 (2026-09-24)](https://game8.jp/umamusume/372949). `[Global]` official race notices use the same four labels in their condition line, for example "Kyoto / Turf / 2,200m (Medium) / Right-Handed / Outer / Autumn / Sunny / Firm" [Umamusume Global Official News, 2026-09-19](https://umamusume.com/news/1050).

No source publishes a metres-per-Stamina conversion. Neither of the two guides that address the question gives one: 「距離が長いほど必要なスタミナが増える」, stamina need rises with distance, but no fixed table is offered [GameWith (2026-09-02)](https://gamewith.jp/uma-musume/article/show/257605), and Game8 expresses the same requirement as a threshold stat value instead of a distance credit [Game8 (2026-09-24)](https://game8.jp/umamusume/372949). ❌ UNVERIFIED: No current source found for a Stamina-to-metres conversion factor. Last known: 2400 m is treated as the distance at which endurance duels begin to decide long races, i.e. Stamina above the 1200 gate is what the distance tests [GameWith (2026-09-26)](https://gamewith.jp/uma-musume/article/show/575798). Practical rule from the same guide: recovery skills substitute for Stamina points on soft footing.

#### 1.3.4 Caps and soft caps

| Cap layer | Value | Applies to | Source |
|---|---|---|---|
| Baseline per-stat cap | 1200 | every stat before breakthroughs. **Two different past-1200 effects are recorded here and they are not the same claim.** (a) *Training axis:* gains past 1200 are halved — `[Global]` Game8 EN pages, "always halved" (see 1.3.4 note below and `docs/adr/0002` amendment 1; the exact reduction is prose-only, no dataset field carries it). (b) *Race axis:* points earned past 1200 exist but have a reduced effect on the Umamusume during the race — the two JP guides cited here. A UI sentence must name which axis it is quoting. | [Game8 (2025-11-21, ⚠️ STALE)](https://game8.jp/umamusume/475668), [Kamigame (2024-02-19, ⚠️ STALE)](https://kamigame.jp/umamusume/page/225422194069532204.html) — race axis; training axis from `[Global]` Game8 EN, dated 2026-07-07 / 2026-08-25 in the rework note |
| Scenario caps, Speed / Stamina / Power / Guts / Wit | URA 1400 / 1400 / 1400 / 1400 / 1400; Unity Cup 1300 / 1300 / 1300 / 1300 / 1800; Climax (Global: Trackblazer) 1200 / 1900 / 1200 / 1200 / 1500; Our Grand Concert 1600 / 1300 / 1300 / 1500 / 1300 | the training run you are in | [Game8 (2025-11-21, ⚠️ STALE)](https://game8.jp/umamusume/475668) for the first four scenarios; [Kamigame (2024-02-19, ⚠️ STALE)](https://kamigame.jp/umamusume/page/225422194069532204.html) for all five. **Climax row corrected 2026-09-27:** this cell previously read `1200 / 1900 / 1200 / 1500 / 1200`, with Guts and Wit transposed; `scenarios.json` and 1.6's Trackblazer row both give `… / 1200 / 1500`. The diagnosis is in `docs/design-research/SCENARIO-DIFFERENCES.md` ("The trap that produced the wrong Trackblazer row"). Any artifact that copied the pre-correction figure is reproducing this row, not an independent source |
| Extended scenario caps | Grand Masters 1500 / 1400 / 1500 / 1300 / 1300 (`[JP-Only]`, and it reproduces `1200 + scenarios.json.stats` = [300, 200, 300, 100, 100]); L'Arc 1600 / 1600 / 1500 / 1500 / 1300 | **Grand Masters now three-source corroborated** (GameWith 2023-07-17, Game8 2026-04-13, Kamigame 2024-04-08) — upgraded from this row's previous "single-source". ⚠️ **All three are `⚠️ STALE`**, by 1,168, 167 and 902 days against the anchor, so what agrees here is three old guides, not three current ones; the only fresh leg is the tier-B export, and the scenario has no `[Global]`-side source at all, so this stays a `[JP-Only]` value and is not promotable to Global-facing data. **L'Arc stays single-source**, on Kamigame alone | [Kamigame (2024-02-19, ⚠️ STALE)](https://kamigame.jp/umamusume/page/225422194069532204.html); [GameWith (2023-07-17, ⚠️ STALE)](https://gamewith.jp/uma-musume/article/show/388788); [Game8 (2026-04-13, ⚠️ STALE)](https://game8.jp/umamusume/510269) |
| Breakthrough (上限突破) | +16 to that stat's cap per ★3 basic-ability factor inherited; the cap applies at 育成開始時, Classic and Senior inheritance moments. A unique-skill factor also raises caps, in proportion to the donor's growth rates | inheritance tools, `[JP]` | [Game8 (2025-11-21, ⚠️ STALE)](https://game8.jp/umamusume/475668) |
| Support-card cap effects | Per-stat 「限界値アップ」 ([Global]: Max Speed, Max Stamina, and so on) raise the cap the run starts with | deck building, `[Both]` wording | [GameTora data export, `support_effects.json` ids 20 to 24](https://gametora.com/data/umamusume/support_effects.ca447e53.json) |
| Recorded hard ceiling | 2000 per stat, with a sixth unlabeled column at 9999, identical for all five scenarios in the export | `[Both]` | [GameTora data export, `scenarios.json`](https://gametora.com/data/umamusume/scenarios.61b7c51c.json) |

The 2000 ceiling is not just a display limit: it coincides with the Speed behaviour gate in 1.3.1, which is why current `[JP]` guides treat "cross 2000 in Speed" as a build objective rather than a rounding artifact [Game8 (2026-09-24)](https://game8.jp/umamusume/372949). A guide target above 2000 does appear in the wild; see Source Conflict Log row 3.

#### 1.3.5 Where a number comes from: base, growth, support bonus, star breakthrough

These are four different things and they do not stack the same way.

* Base stats are what the trainable Umamusume starts the run with. In the GameTora data export of 268 cards, the five base values always sum to 450 for a three-star card (246 cards), 425 for two-star (13 cards) and 400 for one-star (9 cards). Observed single-stat maxima at base: Speed 118 (Calstone Light O), Stamina 124 (Rice Shower), Power 113 (Kawakami Princess), Guts 113 (Mejiro McQueen), Wit 105 (Neo Universe).
* Growth is the run-time multiplier on training gains (1.1.2), and it also distributes the fixed star budget unevenly, which is why two cards with the same base total peak in different stats.
* Support-card stat bonuses are additive deck effects: per-type 「ボーナス」 that raise gains on shared tiles, 「初期○アップ」 that raise the starting value, and 「限界値アップ」 that raise the ceiling [GameTora data export, `support_effects.json` ids 3 to 7, 9 to 13, 20 to 24](https://gametora.com/data/umamusume/support_effects.ca447e53.json).
* Star breakthrough is the biggest single structural fact in the export: the ★4 row always sums to exactly 500 and the ★5 row to exactly 550, for all 268 cards. Every rank past the card's native rarity adds a flat 50 points, redistributed by profile; per-stat deltas from ★4 to ★5 run +4 to +14 in Speed, +6 to +14 in Stamina and +7 to +13 in Power, Guts and Wit.
* **There is no system named "Trainer Abilities" on either client**, and no pre-run passive-buff system by that name was found in any source read for this document. The phrase reaches the repo from an incoming write-up (`docs/UMAMUSUME PRETTY DERBY — COMPREHENSIVE UX DELIVERABLES.md:1154`, "STEP 5: Trainer Abilities (if applicable)"), which is triaged rather than merged and cites this file as its own source — per `docs/design-research/CONSTRAINTS.md` D-285 that is a mirror, not corroboration. What actually exists before a run starts is the stack this section and 1.4 enumerate, and a planner should model these five, not a sixth invented one: **base stats** for the card; **`stat_bonus`** (owner still unstated, see below); **Inherited Blue and Pink Sparks** at run start (+5 / +12 / +21 per stat, and aptitude grade shifts, 1.5.1-1.5.3); **initial-value support effects** (`初期○アップ`, `[Global]` Initial Speed and friends, ids 9 to 13; Initial Friendship Gauge id 14; Specialty Priority id 19); and **cap-setting effects** (`限界値アップ`, `[Global]` Max Speed and friends, ids 20 to 24). Two neighbouring things are often mistaken for it: **Potential Levels** (`覚醒Lv`, 1.3.5) and the `[JP]` **Training Pass** reward track (1.6.7), neither of which is a per-run buff. See also 1.6.10 for what a run can consume.

| Row in the export | Total across the five stats | Per-stat maximum observed |
|---|---|---|
| base_stats | 450 (★3), 425 (★2), 400 (★1) | 118 / 124 / 113 / 113 / 105 |
| four_star_stats | 500, every card | 131 / 138 / 125 / 125 / 116 |
| five_star_stats | 550, every card | 145 / 152 / 138 / 138 / 127 |
| stat_bonus | 30, every card, in 5-point steps across one to three stats | 30 / 30 / 30 / 20 / 30 |

Column order is GameTora's: Speed, Stamina, Power, Guts, Wit [GameTora data export, `character-cards.json`](https://gametora.com/data/umamusume/character-cards.679f7c2e.json). The export carries no in-game label for `stat_bonus`; its fixed 30-point size and 5-point granularity separate it from the +50-per-rank growth rows, and the `[Global]` client string "Potential Level" (JP 覚醒Lv) is the most plausible owner but is not confirmed. ❌ UNVERIFIED: no current source states which UI system grants the export's `stat_bonus` row.

#### 1.3.6 Stats during a run versus final totals

During the run the panel shows live values against the caps in 1.3.4, and each discipline previews the gain it will pay before you commit [Game8 (2026-09-10)](https://game8.jp/umamusume/372572). Everything in that panel is still mutable: mood rescales the next session, camp resets levels, and support effects only apply while the deck is set.

The totals that persist are the ones on the finished Umamusume. `[Global]` official copy calls those units Veteran Umamusume and requires three of them registered as a team to enter a Champions Meeting [Umamusume Global Official News, 2026-09-19](https://umamusume.com/news/1050); `[JP]` uses 殿堂入り for the same graduation [Umamusume JP Official News, 2026.09.11](https://umamusume.jp/news/detail?id=3437). Those final totals are what factor research then reads: the JP event 「アグネスタキオンの究極因子研究」 upgrades a graduated or inheritance-only Umamusume's basic-ability, aptitude and unique factors to ★3 with a 究極因子研究レポート, and explicitly cannot push a factor past ★4 [Umamusume JP Official News, 2026.09.11](https://umamusume.jp/news/detail?id=3437). The loop a new player should expect: train to a final total, bank the factor grade, inherit the +16-per-★3 cap raise, then train past the previous ceiling.

#### 1.3.7 Current competitive stat targets

| Server | Target set | Speed | Stamina | Power | Guts | Wit | Source |
|---|---|---|---|---|---|---|---|
| `[JP]` | Champions Meeting guide, entry level | 2000 | 1600, with one gold recovery skill | 1300 | 1000 | 1400 | [GameWith (2026-09-26)](https://gamewith.jp/uma-musume/article/show/575798) |
| `[JP]` | Champions Meeting guide, advanced | 2100 | 1800, with one gold recovery skill | 1600 | 1200 | 1750 | [GameWith (2026-09-26)](https://gamewith.jp/uma-musume/article/show/575798) |
| `[Global]` | Published sample build target (Special Week) | 1500+ | 800 | 1000 | 500 | 1000 | [Game8.co (2026-09-25)](https://game8.co/games/Umamusume-Pretty-Derby/archives/536322) |

The `[JP]` guide states its reasoning as behaviour gates rather than preference: Speed must reach 2000 to switch on the full spurt, and 2400 m is a distance where 「スタミナ勝負が発動する」, with poor footing (不良) making instant recovery essential [GameWith (2026-09-26)](https://gamewith.jp/uma-musume/article/show/575798). The `[Global]` numbers are lower, and no source explains the gap; the roster and scenario lag visible in the export (1.1.8) is the context to read them in, not a stated cause.

Distance-level priorities, `[JP]`, from the same guide family: Sprint races are Speed-first with no endurance worry, Mile races stay Speed-first with Stamina as insurance from event choices, Medium races need Speed plus the Stamina to finish, and Long races require all five at a high level, making them the hardest to build for [Game8 (2026-09-24)](https://game8.jp/umamusume/372949).

**Sources:** Game8 `[JP]` stat-effects guide (2026-09-24, tier A) and scenario cap guide (2025-11-21, tier A, stale); Kamigame cap table (2024-02-19, tier A, stale); GameWith stat-effects guide (2026-09-02, tier A), stat targets (2026-09-26, tier A) and training base values (2023-02-25, tier A, stale); Game8.co Global build guide (2026-09-25, tier A); Umamusume Global official news item 1050 (tier S); Umamusume JP official news ids 3437 and 3476 (tier S); data export `scenarios.json`, `skills.json`, `support_effects.json`, `character-cards.json` (GameTora-derived, tier B).

### 1.4 Support Card System

Tier key: `[S]` = Cygames official site, `[A]` = maintained strategy wiki, `[B]` = GameTora export pulled 2026-09-27 into `GameTora data export` ([manifest](https://gametora.com/data/manifests/umamusume.json)). Every decoded `[B]` number is paired with an `[A]` page.

#### 1.4.1 Types

A support card is a training companion assigned before a career run. The client sorts cards into seven types, and Group is a type in its own right rather than a flag on another type: the export stores it in the same `type` field as the five stat types, the JP filter offers グループ beside the other six, and both English wikis list it as a seventh heading.

| Type (export key) | `[JP]` | `[Global]` | `[JP]` R / SR / SSR | `[Global]` SSR | Basis |
|---|---|---|---|---|---|
| speed | スピード | Speed | 34 / 19 / 72 | 28 | `[B]` + `[A]` |
| stamina | スタミナ | Stamina | 25 / 17 / 55 | 22 | `[B]` + `[A]` |
| power | パワー | Power | 21 / 21 / 57 | 21 | `[B]` + `[A]` |
| guts | 根性 | Guts | 30 / 22 / 59 | 20 | `[B]` + `[A]` |
| intelligence | 賢さ | Wit | 25 / 21 / 53 | 18 | `[B]` + `[A]` |
| friend | 友人 | Pal | 11 / 1 / 11 | 4 | `[B]` + `[A]` |
| group | グループ | Group | 0 / 0 / 5 | 2 | `[B]` + `[A]` |

Counts are records in the export: `[JP]` covers releases from 2021-02-24 on, `[Global]` counts cards carrying a `release_en` date. `[Global]` holds 115 SSR cards against `[JP]` 312, so the two catalogues are not interchangeable.

Pal (友人) cards differ in effect, not just flavor. None of the 11 `[JP]` SSR friend cards carries the Friendship Bonus effect (id 1) while all 296 non-friend, non-group SSR cards do, every friend card carries Event Effectiveness (id 26), and most carry Failure Protection (id 27, 21 of 23) and Event Recovery (id 25, 20 of 23) `[Both]`. Guides read it the same way: a Pal card earns its slot through its event chain (energy return, mood, failure protection) rather than through boosted stat training, and the type is where the NPC staff cards sit, such as the academy chairwoman and the reporter.

Group (グループ) cards are SSR-only on both servers. Five exist `[JP]`: チーム＜シリウス＞ (Team Sirius, 2022-03-18), 玉座に集いし者たち (The Throne's Assemblage, `[Global]` name Heirs to the Throne, 2022-07-20), 祖にして導く者 (Ancestors & Guides, 2023-03-20), 刻み続ける者たち (Carvers of History, 2023-12-28), 伝説の体現者 (Embodiment of Legends, 2025-02-24); two reached `[Global]`, Team Sirius on 2026-03-26 and Heirs to the Throne on 2026-06-25. All five carry the full friendship kit (id 1) plus Wit Friendship Recovery (id 31) and the event-value effects, and the export's group event table records six character ids behind one card (entry 30081), that is, several Umamusume acting as a single support card. No source in this pass states a separate facility rule for the type, so the difference is claimed only at the effect profile.

**Sources:** `[B]` `support-cards.json`, `support_effects.json`, `training_events__group.json` (2026-09-27); `[A]` Game8 card list, 2026-09-23, labels Speed/Stamina/Power/Guts/Wit/Pal/Group: https://game8.co/games/Umamusume-Pretty-Derby/archives/535928; `[A]` GameWith filter, 2026-09-14, JP types including グループ: https://gamewith.jp/uma-musume/article/show/293366; `[A]` GameWith group ratings, 2026-09-14: https://gamewith.jp/uma-musume/article/show/352669; ⚠️ STALE: Umamusume Wiki support list dated 2025-07-09, Group only under SSR: https://umamusu.wiki/Game:List_of_Support_Cards; ⚠️ STALE: Kamigame bond gauge dated 2022-12-19, Pal holds the NPC staff cards: https://kamigame.jp/umamusume/page/147315202046567206.html; ⚠️ STALE: GameWith deck guide dated 2024-02-16, Pal value sits in events: https://gamewith.jp/uma-musume/article/show/257619; `[A]` Kamigame deck build, 2026-09-17: https://kamigame.jp/umamusume/page/428924736643289151.html

#### 1.4.2 Rarity and limit breaking

Rarity is R, SR, SSR on `[Both]` (export `rarity` 1/2/3, confirmed by the Game8 list).

`[Global]` keeps the original upgrade model. A card takes four breaks by consuming copies of itself, so a fully broken card is five copies, and two identical copies cannot sit in one deck, which is why duplicates are spent instead of equipped. English guides say Limit Break and score at "Max Limit Break (MLB/4LB)"; official `[Global]` tutorial material names the item path "Support Cards and Uncap Crystals", matching the export entries Rainbow Uncap Crystal and Gold Uncap Crystal, described as "Uncaps an SSR Support Card" and "Uncaps an SR Support Card" `[Global]`.

`[Global]` level caps per break are **confirmed from the client** as of 2026-09-27, closing the gap this line carried. The deck editor draws four diamonds on every card slot and fills one per completed break, and the level readout next to them is `30 + 5 × breaks` for SSR: in `Screenshot 2026-07-15 155016.png` three cards show ◇◇◇◇ at `Lvl 30`, two show ◆◇◇◇ at `Lvl 35`, and one shows ◆◆◆◆ at `Lvl 50`. That matches the 2021 guide's +5-per-break rule and its ceiling set of SSR 50 / SR 45 / R 40, and it is corroborated independently by the export: the effect-value ladder in 1.4.7 stops at level 50 for `rarity` 3, at 45 for `rarity` 2 and at 40 for `rarity` 1, which is only possible if those are the three rarities' caps. The unbroken bases are therefore SSR 30, SR 25, R 20.

`[JP]` replaced that model at the 2025-10-07 maintenance: card levels and the Support Pt currency were removed, Support Pt was refunded as money, 上限解放 was renamed 性能解放, and a duplicate now applies the release stage automatically. Effect values at a given stage equal what the old fully levelled card produced, the level-40 gate on a card's unique bonus is gone, and the team arena bonus scales off rarity plus release stage instead of levels. Current `[JP]` guides still score 無凸 (no break) to 完凸 (four breaks) without quoting levels, which confirms the four-stage ceiling survived the change.

**Sources:** `[S]` Umamusume Global news, 2026-09-23 22:00 UTC, episodes include "Support Cards and Uncap Crystals": https://umamusume.com/news/1064/; `[B]` `items.json`; `[A]` Game8 tier list, 2026-09-23, MLB/4LB wording: https://game8.co/games/Umamusume-Pretty-Derby/archives/536715; ⚠️ STALE: Kamigame uncap guide dated 2021-10-13, four breaks, five copies, same-card deck ban, level figures: https://kamigame.jp/umamusume/page/146591481853953911.html; ⚠️ STALE: Famitsu dated 2025-08-29 quoting the official JP notice: https://www.famitsu.com/article/202508/51020; ⚠️ STALE: 4Gamer dated 2025-10-07, implementation day: https://www.4gamer.net/games/414/G041434/20251007025/; ⚠️ STALE: Dengeki Online dated 2025-08-28: https://dengekionline.com/article/202508/50965; `[A]` GameWith ranking, 2026-09-26, 0凸 to 4凸 baselines: https://gamewith.jp/uma-musume/article/show/258925; `[A]` Kamigame ranking, 2026-09-18, 無凸 and 完凸 scoring: https://kamigame.jp/umamusume/page/147029748571208590.html

#### 1.4.3 Friendship gauge and friendship training

The gauge runs 0 to 100, 絆ゲージ `[JP]` and "friendship gauge" `[Global]`. It fills in marked segments and turns orange at the fourth mark, 80. Crossing 80 is the switch: from there the card can trigger 友情トレーニング, friendship training, when it appears at the facility of its own type `[Both]`. Training value resolves as (facility base + bonus) × growth rate × mood × friendship bonus × participant bonus, and the participant term is +5% per participating card `[Both]`. Two qualifying cards multiply rather than add, so a +25% and a +30% bonus give 1.25 × 1.30 = 1.625 `[JP]`.

Gauge head start is a card effect, Initial Friendship Gauge (id 14), on 289 of 296 `[JP]` SSR stat cards, and Specialty Priority (id 19, 276 of 296) raises how often the card appears at its preferred facility `[Both]`. Wit friendship training returns energy, effect id 31 (Wit Friendship Recovery) `[Both]`.

**Sources:** ⚠️ STALE: Game8 Global friendship training dated 2025-08-07, gauge name, 80% orange threshold, +5% per support: https://game8.co/games/Umamusume-Pretty-Derby/archives/542672; ⚠️ STALE: Game8 JP friendship training dated 2025-11-21, fourth mark at 80 turns the gauge orange, formula and 1.625 example: https://game8.jp/umamusume/454202; ⚠️ STALE: Kamigame bond gauge dated 2022-12-19, maximum of 100: https://kamigame.jp/umamusume/page/147315202046567206.html; `[B]` `support_effects.json`, `support-cards.json` (ids, localized names, coverage counts)

#### 1.4.4 Card events and skill hints

Each card owns an event chain: the export carries 135 per-character chains, 11 Pal chains, and 5 group chains, plus a `hints` block per card holding the hinted skill pool (`hint_skills`) and the hint modifiers (`hint_others`) `[B]`. Two effects drive hint output, Hint Levels (id 17, on 253 of 296 `[JP]` SSR stat cards) and Hint Frequency (id 18, on 254 of 296) `[Both]`.

Hints accumulate into a per-skill hint level, and each level cuts that skill's point cost by 10%: the `[JP]` guide states that raising hint level lowers required skill points and caps the item route at "最大Lv3（必要スキルPtが30%減少）", that is, 30% at level 3. The `[JP]` items are ヒント本, ヒント専門書, 夢の煌めき, needed 12 / 6 / 30 to take an unenhanced card to Lv3, and the official `[Global]` tutorial names the same category "Books of Hints" `[Both]`. A card's own hint level multiplies what one event pays out: the gain is 1 + hint level, so a card at hint level 4 jumps five levels at once `[JP]`. Inherited white factors push a skill's hint level anywhere from +1 to +5, so a hinted skill can already look discounted before the run's events resolve `[Global]`.

Yellow and orange are gauge states in the sources consulted, not hint states: the gauge reads yellow while filling and flips to orange at 80, the friendship training trigger, in both clients `[Both]`.

❌ UNVERIFIED: a yellow versus orange coloring on the hint indicator itself. No source in this pass states one; last known description is the discount figure shown next to a skill.

**Sources:** `[A]` Game8 JP hint enhancement, 2026-09-10, cost cut and item counts: https://game8.jp/umamusume/442505; `[S]` Umamusume Global news, 2026-09-23, "Potential Levels and Books of Hints": https://umamusume.com/news/1064/; ⚠️ STALE: GameWith effect formulas dated 2022-05-18, payout of 1 + hint level: https://gamewith.jp/uma-musume/article/show/274990; ⚠️ STALE: Umamusume Wiki Inspiration dated 2025-11-24, white sparks give hint level +1 to +5: https://umamusu.wiki/Game:Inspiration; `[B]` `support-cards.json`, `training_events__char.json`, `training_events__friend.json`, `training_events__group.json`

#### 1.4.5 Deck building basics

A deck holds six support card slots, the shape every published template fills `[Both]`, and two copies of the same card cannot be equipped together, so duplicates go into breaking `[Both]`.

Spread matters because a facility hosts only cards of its own type and friendship training needs the gauge past 80 on the cards that appear there. Guides recommend two to three cards of one type and no more, so one facility is not crowded while the others stay empty, and they call a fully distinct six-type deck weak for skill points ("完全ハイランダーはスキルPtを稼ぎづらく微妙" `[JP]`). Current scenario decks work off a type floor instead: four different types counted with the Pal slot unlocks that scenario's bonus training ("合計4タイプ以上編成するとクラシック級以降の「試食会」で分身効果が発動し、強力な友情練習を踏みやすくなる" `[JP]`).

Repeating Pal or Group cards is the one duplication guides rule out outright: extra copies over-supply energy recovery and make card event progress harder to advance ("複数編成すると回復が過剰になってしまったり、イベント進行度を進めるのが難しくなるので基本的には非推奨" `[JP]`).

❌ UNVERIFIED: "friendship radius" as a named deck-building term. No source uses it. The concept it points at is the gauge threshold and the per-participant bonus described in 1.4.3.

**Sources:** `[A]` Kamigame deck build, 2026-09-17, six-slot templates, four-type rule, hybrid warning: https://kamigame.jp/umamusume/page/428924736643289151.html; ⚠️ STALE: GameWith deck guide dated 2024-02-16, six positions, 2 to 3 cards per type, Pal and Group stacking warning: https://gamewith.jp/uma-musume/article/show/257619; ⚠️ STALE: Kamigame uncap guide dated 2021-10-13, identical copies cannot share a deck: https://kamigame.jp/umamusume/page/146591481853953911.html

#### 1.4.6 Current top-tier support cards

The lists stay separate because `[Global]` content trails `[JP]`: `[Global]` had reached only the 2022-era `[JP]` wave by 2026-09-24, and none of the newest `[JP]` versions below exist there `[B]`. Fine Motion is the only overlapping name.

`[Global]` SS rank, from the Game8 tier list dated 2026-09-23, scored at Max Limit Break, each card checked against the export for its `[Global]` release date:
- Agnes Tachyon [Q≠0] Speed, live 2026-07-22; Kitasan Black [Fire at My Heels] Speed, 2025-07-16; Maruzensky [Sentimental Flare ♪] Speed, 2026-07-02
- Super Creek [Piece of Mind] Stamina, 2025-06-26; Ines Fujin [Watch My Star Fly!] Guts, 2025-06-26; Fine Motion [Wave of Gratitude] Wit, 2025-06-26; Light Hello [From the Ground Up] Pal, 2026-07-22
- Newest `[Global]` drops in the export: Eishin Flash (Speed) and a Narita Top Road SR on 2026-09-24, then Oguri Cap (Wit), Mejiro Ardan (Speed), Yaeno Muteki (Guts) on 2026-09-07; no group card newer than 2026-06-25

`[JP]` SS tier, from the GameWith ranking updated 2026-09-26 21:49 JST, with each newest SSR version and its `[JP]` release date from the export:
- Tokai Teio Speed 2025-10-29; Air Groove Speed 2025-12-26; Forever Young Wit 2026-02-24; Tap Dance City Speed 2026-04-30; Sakura Chiyono O Stamina 2026-05-11; Satono Diamond Speed 2026-05-29
- Tazuna Hayakawa Pal 2026-06-29; Meisho Doto Stamina 2026-06-29; Nice Nature Wit 2026-07-10; Fuji Kiseki Guts 2025-09-29; Mr. C.B. Speed 2026-08-24; Efforia Speed 2026-08-24; Matikanetannhauser Speed 2026-09-11

The second `[JP]` source, Kamigame's ranking of 2026-09-18, agrees at the top of the newest wave: it calls the Efforia SSR Speed card the strongest practice and hint card of 2026 and the Mr. C.B. SSR Speed card the best late-charger card, both 9.5/10 at 完凸, and treats the newest Tazuna Hayakawa Pal card as mandatory for the current scenario's factor farming. Calendar context `[JP]`: the 5.5th anniversary pickup gacha and the newest trainable Umamusume were announced 2026-09-18 12:00 `[S]`.

**Sources:** `[A]` Game8 tier list, 2026-09-23: https://game8.co/games/Umamusume-Pretty-Derby/archives/536715; `[A]` GameWith ranking, 2026-09-26: https://gamewith.jp/uma-musume/article/show/258925; `[A]` Kamigame ranking, 2026-09-18: https://kamigame.jp/umamusume/page/147029748571208590.html; `[A]` Kamigame factor farming, 2026-08-25, mandatory Pal slot per scenario: https://kamigame.jp/umamusume/page/147455032642539573.html; `[S]` Umamusume JP news list, items of 2026-09-18 and 2026-09-26: https://umamusume.jp/news/?t=game; `[B]` `support-cards.json` (titles, `release`, `release_en`)

#### 1.4.7 Effect values: the level ladder, how it interpolates, and the Unique Perk

The export's `support-cards.json` stores each card's effects as `[[effect_id, v1 … v11], …]`. The eleven value slots are **card levels 1, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50**, and `-1` means the client holds no entry for the effect at that level rather than that the effect is zero. `[Global]`, decoded `[B]` and confirmed against the client's own numbers below.

The decode came from the GameTora support-card page (`https://gametora.com/umamusume/supports/<url_name>`; note the plural, the singular paths 404), which carries a level selector with −5 / −1 / +1 / +5 controls and prints "Unlocked at level N" against effects a card does not yet have. Reading Tokai Teio `[Dream Big!]` (`support_id` 30003) at three selector positions against its export row gives the rule:

| Effect (id) | export anchors | level 30 shown | level 35 shown | level 40 shown |
|---|---|---|---|---|
| Friendship Bonus (1) | 30→15, 45→20 | 15% | 16% | 18% |
| Mood Effect (2) | 30→40, 50→60 | 40% | 45% | 50% |
| Initial Friendship Gauge (14) | 30→20, 45→25 | 20 | 21 | 23 |
| Hint Frequency (18) | 30→30, 45→40 | 30% | 33% | 36% |
| Specialty Priority (19) | 30→20, 45→35 | 20 | 25 | 30 |
| Power Bonus (5) | 35→1 | not unlocked | 1 | 1 |
| Race Bonus (15) / Fan Bonus (16) | 45→5 / 45→10 | not unlocked | not unlocked | not unlocked |

Every intermediate figure is the **floor of a straight line drawn between the two bracketing anchors**: at level 35, Friendship Bonus is `15 + (20−15)·(35−30)/(45−30) = 16.67 → 16`, Mood Effect is `40 + 20·5/20 = 45`, Initial Friendship Gauge is `20 + 5·5/15 = 21.67 → 21`. All five changing effects land on the displayed integer at levels 35 and 40, and the two `Unlocked at level 45` effects are still absent at 40, so the rule is not a lucky fit on one row. `[Global]`

Two consequences worth stating plainly. First, a card's **effect count grows with its level**, which is the mechanism behind the 2021 note's "one more obtainable support effect per release": the release stage raises the level cap, and the higher cap exposes effects that were locked below it. Second, the tool can therefore reproduce any `[Global]` effect value from the export alone, provided it stores the anchors and applies the floor rule; rounding is truncation, not nearest, and getting that wrong shifts every intermediate value by one.

**The Unique Perk is a second, independent axis and the export does not carry its numbers.** The client's card detail panel has a heading `Unique Perk`, names it after the card's bracket title (`Dream Big!`, `Piece of Mind`, `Even the Littlest Bud`), gives it its own level badge, and lists exactly two effect names under it — for `[Even the Littlest Bud]` Nishino Flower, "Mood Effect and Initial Friendship Gauge"; for `[Piece of Mind]` Super Creek, "Friendship Bonus and Specialty Priority". Its level is **not** the card's level: across the six captured panels a card at `Lvl 35` appears with perk `Lvl 30` and another at `Lvl 35` with perk `Lvl 40`, and a fully broken card at `Lvl 50 / 50` still shows perk `Lvl 30`. `[Global]`

The export's `effects` array covers the card's levelled effects, not the perk's: `[Dream Big!]` Tokai Teio prints "Friendship Bonus and Initial Speed" under its perk while its `effects` rows contain no Initial Speed (id 9) at all. So the perk's magnitude at a given perk level is **not derivable from anything in this repository**, and what raises the perk level is likewise unstated by every source consulted here. ❌ UNVERIFIED: the `[Global]` Unique Perk value table and the currency or action that levels a perk. Last known: none; the six client panels give the two effect names per card and the level number, and nothing else.

The same panel prints `Lvl N / MAX` beside a bar and the text `0 SP to next level`, so SP is the card-experience currency and a card at its cap reads zero. The GameTora page shows two figures at once (37,785 and 75,570 at level 35, 56,935 and 113,870 at level 40, each pair exactly 1 : 2), which is consistent with a next-step and a total-to-cap reading. ❌ UNVERIFIED: which of the two is which.

**Scenario Link is derived, not stored per card.** The deck editor badges a card `Scenario Link` when the Umamusume who holds it is on the running scenario's linked list, and that is exactly what the data says: Unity Cup's `scenario_linked_characters` holds Taiki Shuttle, Rice Shower, Haru Urara, Matikanefukukitaru and Riko Kashimoto, and in `Screenshot 2026-07-15 155016.png` a six-card Unity Cup deck whose only member of that list is Haru Urara `[Urara's Day Off!]` (`char_id` 1052) badges that card and no other. Per-scenario list lengths are Ura Finale 1, Unity Cup 5, Trackblazer 0, Our Grand Concert 5, Grandmasters Legacies 1, and then 13, 6, 6, 5, 6, 6, 6, 6, 7 across the remaining nine scenarios `[Both]`. A schema that stores a `is_scenario_link` flag per card is therefore wrong: the flag is a join between `char_id` and the scenario, and it changes when the scenario does. `[B]` + client frame

**Support card type glyphs, fixed by the client and distinct from the stat-band icons.** The deck editor's legend row carries seven chips, and six cards in the frame identify five of them by pairing the chip with the export's `type` value: `[Dream Big!]`, `[Even the Littlest Bud]` and `[Double Carrot Punch!]` are all `speed` and all wear the blue boot; `[Urara's Day Off!]` and `[Just Keep Going]` are both `guts` and both wear the pink flame; `[Piece of Mind]` is `stamina` and wears the red heart. The remaining chips in the same row are the brown flexed arm (`power`), the dark-green graduation cap (`intelligence`, which the client calls Wit) and two figure marks — a single olive figure (`friend`) and a pair of green figures (`group`). `[Global]`

Note the collision this creates with the training HUD: the heart is Stamina's type chip here, and the client's own HUD uses a heart for Energy in other contexts, which is why `docs/design-research/CONSTRAINTS.md` pins one glyph to one meaning. The chip fill colours are read from the frame's legend row and are not point-probed; treat them as indicative until they are measured.

**Sources:** `[B]` `support-cards.json` (559 records, `effects` anchor vectors, `rarity`, `type`, `char_id`), `support_effects.json` (35 effects with `[Global]` `name_en`, `desc_en`, `calc`, `symbol`), `scenarios.json` (`scenario_linked_characters`), all pulled 2026-09-27; `[B]` GameTora support card page https://gametora.com/umamusume/supports/30003-tokai-teio read at selector levels 30, 35 and 40 on 2026-09-27; `[S]` client frames `docs/game-screenshots/Screenshot 2026-07-15 155016.png` (deck editor, six cards with break diamonds, level readouts, Scenario Link badge and the type legend) and the six card detail panels captured the same minute (`155215`, `155222`, `155239`, `155311`, `155324`, `155337`); ⚠️ STALE: Kamigame uncap guide dated 2021-10-13 for the +5-per-break rule and the extra-effect-per-release claim: https://kamigame.jp/umamusume/page/146591481853953911.html



#### 1.4.8 Where each effect lands in a run

The dictionary carries 35 effect records, and each one states in the export whether it multiplies or adds
(`calc`) and what its unit is (`symbol`). That is the client's own metadata rather than this document's
reading, so the grouping below is a re-shelving of published fields, not an interpretation. `[Global]` `[B]`

| When it pays | Effects (id) | `calc` | Coverage on the 312 `[JP]` SSR records |
|---|---|---|---|
| At career start | Initial Speed (9), Initial Stamina (10), Initial Power (11), Initial Guts (12), Initial Wit (13), Initial Friendship Gauge (14) | flat | 65 / 40 / 62 / 49 / 18 / **305** |
| On every shared training session, as a multiplier | Friendship Bonus (1), Mood Effect (2), Training Effectiveness (8) | 1 is `mult`; 2 and 8 flat percent | 301 / 218 / 195 |
| On a specific discipline's gain, added | Speed Bonus (3), Stamina Bonus (4), Power Bonus (5), Guts Bonus (6), Wit Bonus (7), Skill Point Bonus (30) | flat | 78 / 48 / 69 / 56 / 35 / 126 |
| On whether the card shows up on its own tile | Specialty Priority (19) | `add` | 276 |
| On race days | Race Bonus (15), Fan Bonus (16) | flat percent | 250 / 250 |
| On this card's own events | Event Recovery (25), Event Effectiveness (26) | flat percent | 15 / 16 |
| On failure and Energy spend | Failure Protection (27) `mult`, Energy Cost Reduction (28) `mult`, Wit Friendship Recovery (31) | 27 and 28 multiply | 10 / 11 / 58 |
| On hint output | Hint Levels (17) as a `level`, Hint Frequency (18) as percent | flat | 253 / 254 |
| Named, and carried by **no card at all** | Max Speed (20), Max Stamina (21), Max Power (22), Max Guts (23), Max Wit (24), Minigame Effectiveness (29), Hint Quantity Bonus (33), id 41, id 9991 | — | 0 |

Three things in that table are worth more than the rest.

**Only four effects declare `calc`, and they are the four that combine multiplicatively** — Friendship
Bonus (1), Failure Protection (27), Energy Cost Reduction (28) and Specialty Priority (19, `add`).
Everything else is a flat percentage or a flat amount. This matters because §1.4.3's training formula
multiplies its terms, and an engine that guessed "percent means multiply" would compute wrong gains on
every card carrying a stat bonus. `[B]`

**The five "Max <stat>" effects exist in the dictionary and are carried by zero of the 559 cards.** They
are the game's own vocabulary for raising a stat ceiling, and no shipped support card uses them, which is
the strongest evidence in this document that a scenario's cap is not a card effect on `[Global]`. It also
means a UI that offers a "cap raised by support cards" line would be displaying a feature the catalogue
does not contain. `[B]`

**Id 32 has no name in any language and appears on 14 SSR cards.** Id 9991's `name_en` is the sentence
"Increases Stats from Hints" rather than a label, and ids 33 and 41 have neither name nor coverage. A
deck view that renders effect names from the dictionary will therefore print a blank cell for those 14
cards, and must show a visible `[Unverified]` marker rather than inventing a label (D-20). `[B]`

**Sources:** `[B]` `support_effects.json` (35 records: `id`, `name_en`, `desc_en`, `calc`, `symbol`) and
`support-cards.json` (coverage counted across all 559 records and the 312 `rarity` 3 records, pulled
2026-09-27); the training formula the multipliers feed into is §1.4.3.


### 1.5 Inheritance System

`[Global]` localizes the system as Inspiration, where the ancestors picked for a run are legacies and the inheritable traits are Sparks, while `[JP]` uses 継承 and 因子. Each is described with its own terms below.

#### 1.5.1 How inheritance resolves at the end of a run

At career end the finished Umamusume gains factors whose kinds and numbers follow her final stats, aptitudes, and learned skills; the player does not pick a fixed list `[Both]`. A unique-skill factor is guaranteed once the trainee herself stands at three stars or higher, and using a finished Umamusume as an ancestor neither consumes nor removes her `[Both]`.

Inheritance pays out at three fixed moments: career start, early April of the classic year, and early April of the senior year `[Both]`. Career start is deterministic for stat and aptitude factors; the two mid-run events are probabilistic, and a rare golden inheritance event hands over more than a normal one `[JP]`. White factors never fire at career start, only mid-run `[JP]`. The export stores factor records rather than payout rules, so every number below comes from a wiki and is paired with a second one.

**Sources:** ⚠️ STALE: Kamigame factor mechanics dated 2025-06-05, three timings, grant at career end, golden event, white factor limit: https://kamigame.jp/umamusume/page/154134787475434233.html; ⚠️ STALE: Kamigame double circle guide dated 2025-06-05, ancestors are not consumed: https://kamigame.jp/umamusume/page/144421049306500950.html; ⚠️ STALE: Umamusume Wiki Inspiration dated 2025-11-24, Global terms, event timings, three-star condition: https://umamusu.wiki/Game:Inspiration; `[A]` Game8 Legacy and Sparks guide, 2026-08-24: https://game8.co/games/Umamusume-Pretty-Derby/archives/536822; `[B]` `factors.json`

#### 1.5.2 Factor categories

`[JP]` names four colors plus the scenario kind, `[Global]` renders them as Blue, Pink, Green, and White Sparks. The 2021-era framing that green means skills and white means unique no longer matches either client: skill hints are white on `[Global]`, and unique-skill inheritance is green on both servers.

| Category | `[JP]` | `[Global]` | Effect | Export records | Basis |
|---|---|---|---|---|---|
| Stat | 青因子 | Blue Sparks | One stat up +5, +12, or +21 at career start for 1, 2, or 3 stars | 5 | `[A]`+`[A]`+`[B]` |
| Aptitude | 赤因子 | Pink Sparks | Track, distance, strategy: 1 star = +1 grade, 4 = +2, 7 = +3, 10 to 18 = +4 | 10 | `[A]`+`[A]`+`[B]` |
| Unique skill | 緑因子 / 固有因子 | Green Sparks | Carries one character's own unique skill as 1 to 3 hint levels | 268 | `[A]`+`[A]`+`[B]` |
| Skill | 白因子（スキル因子） | White Sparks, skill | Mid-run hint of level +1 to +5, or a small stat top-up if already learned | 452 | `[A]`+`[A]`+`[B]` |
| Competition | 白因子（レース因子） | White Sparks, competition | From top-grade (G1) wins; 3, 6, or 9 stat per event, sometimes a hint | 37 | `[A]`+`[A]`+`[B]` |
| Scenario | シナリオ因子 | Scenario Sparks | From clearing a scenario's final conditions; about 10 to 30 per stat, over 200 when stacked | 34 | `[A]`+`[A]`+`[B]` |

The ten aptitude records are the two surfaces, four distances, and four running styles that every trainable Umamusume carries `[B]` + `[A]`. The other 68 records in the export are older aptitude and stat items plus the carnival bonus, which no page in this pass explains; they are counted, not described.

**Sources:** ⚠️ STALE: Kamigame factor mechanics dated 2025-06-05, JP color names, star thresholds, scenario values: https://kamigame.jp/umamusume/page/154134787475434233.html; ⚠️ STALE: Umamusume Wiki Inspiration dated 2025-11-24, Global Spark names and payout table: https://umamusu.wiki/Game:Inspiration; `[A]` Game8 guide, 2026-08-24, four color families on Global: https://game8.co/games/Umamusume-Pretty-Derby/archives/536822; `[B]` `factors.json` (categories blue, pink, skill, race, scenario, other with the counts above)

#### 1.5.3 Star ratings and the ceilings parents face

Factors carry 1 to 3 stars and three is the ceiling; more stars mean larger payouts and better trigger odds `[Both]`. The count is rolled from the run's results, never chosen, and the published `[Global]` odds for a stat factor follow the final value of that stat:

| Final stat value | 1 star | 2 stars | 3 stars |
|---|---|---|---|
| Below 600 | about 90% | about 10% | 0% |
| 600 to 1100 | about 50% | about 45% | about 6% |
| Above 1100 | about 20% | about 70% | about 10% |

The same thresholds are the `[JP]` planning rule: guides tell farmers to finish an ancestor with the target stat above 600 and, for a real shot at three stars, above 1100, so a three-star stat factor is unreachable below 600 in that stat `[Both]`. Read the table as probabilities, not as an award schedule: **crossing 1100 buys a roughly 1-in-10 roll at three stars, not a three-star factor**, and below 600 the three-star branch is 0% rather than unlikely. Any planner output that states "Speed 1100 → ★★★" as a deterministic mapping is wrong on this row, and the run's own ★ count is rolled, never chosen.

Aptitude factors roll off the grade instead, from 10% at G or F to 100% at A, with S graded parents rolling extra guaranteed inheritance attempts rather than a chance above 100% `[Global]` + `[JP]`. The ceilings a parent faces: inheritance cannot lift an aptitude above A before the run starts, cannot lift it more than four grades, and cannot take an aptitude starting at F or below all the way to A `[Both]`. Mid-run events can push A to S, and a mid-run jump is worth one grade whether the factor is one star or three, with stars affecting only the trigger chance `[JP]`.

`[JP]` shrinks the roll during the recurring Agnes Tachyon factor research event: the 25th edition ran 2026-09-11 to 2026-09-18 as the anniversary 究極因子研究 form and distributed items that retro-fit generic factors and set aptitude, stat, and unique factors to three stars `[S]` + `[A]`.

**Sources:** ⚠️ STALE: Umamusume Wiki Inspiration dated 2025-11-24, star odds, grade odds, ceilings: https://umamusu.wiki/Game:Inspiration; `[A]` Game8 guide, 2026-08-24, three-star ceiling and stat-value scaling: https://game8.co/games/Umamusume-Pretty-Derby/archives/536822; `[A]` Kamigame factor farming, 2026-08-25, 600 and 1100 stat targets: https://kamigame.jp/umamusume/page/147455032642539573.html; `[A]` Kamigame factor research, 2026-09-16, edition schedule and 究極因子研究 items: https://kamigame.jp/umamusume/page/251735276060200383.html; `[S]` Umamusume JP news list, item of 2026-09-18 12:00 「特別イベント「アグネスタキオンの究極因子研究」終了！」 https://umamusume.jp/news/?t=game; ⚠️ STALE: Kamigame factor mechanics dated 2025-06-05, mid-run single-grade rule: https://kamigame.jp/umamusume/page/154134787475434233.html

#### 1.5.4 Ancestor circle, slots, and repeats

Two ancestors are chosen when a run starts and each brings two ancestors of its own, so the diagram holds six Umamusume: two parents plus four grandparents `[Both]`, shown together on one screen `[Global]`. The two parents must be different Umamusume and neither may be the trainee herself `[JP]`.

Duplicates deeper in the diagram are allowed but cost compatibility: if the trainee appears among her own grandparents, that link contributes a compatibility value of 0, which makes the top grade much harder to reach, and the same zeroing applies to other repeated ancestors `[Both]`. The recursion limit stated in English is that a character may appear anywhere in the ancestry diagram as long as she is not her own direct ancestor `[Global]`.

❌ UNVERIFIED: how many factor slots a finished Umamusume displays. No source states a maximum; the sourced rule is that the count varies with final stats, aptitudes, and skills.

One parent may be a friend's finished Umamusume, rented from the friend list `[Both]`. `[JP]` boards let trainers search for a given three-star factor or a fully broken card before exchanging codes, and guides recommend renting for scenario-limited skills rather than rebuilding a Legacy stock around them `[JP]`.

Compatibility, 相性 `[JP]` and Affinity `[Global]`, grades each link △, ○, or ◎, and a better grade raises both the chance that a factor fires and the size of the payout: ◎ is worth more stat gain, more aptitude progress, and better skill inheritance `[JP]`. The grade comes from shared relationships and similar aptitudes, from the grandparents' side as well as the parents' side, and from ancestors winning the same top-grade competitions `[Both]`.

**Sources:** ⚠️ STALE: Umamusume Wiki Inspiration dated 2025-11-24, two legacies plus sub-legacies, Global terms: https://umamusu.wiki/Game:Inspiration; ⚠️ STALE: Kamigame factor mechanics dated 2025-06-05, six members in the inheritance diagram: https://kamigame.jp/umamusume/page/154134787475434233.html; ⚠️ STALE: Kamigame double circle guide dated 2025-06-05, two-parent pick, no duplicates, friend rental, compatibility grades: https://kamigame.jp/umamusume/page/144421049306500950.html; `[A]` GameWith factor farming, 2026-09-26, two ancestors mandatory and repeated ancestor zeroes compatibility: https://gamewith.jp/uma-musume/article/show/258843; `[A]` Game8 guide, 2026-08-24, rental rule and no self-ancestor rule: https://game8.co/games/Umamusume-Pretty-Derby/archives/536822; `[A]` Kamigame factor farming, 2026-08-25, factor search boards: https://kamigame.jp/umamusume/page/147455032642539573.html

#### 1.5.5 Chain strategy for building a Legacy stock

The loop is 因子周回 `[JP]`, factor farming: dedicated careers whose only output is strong factors, needed because the number of aptitude and white factors is decided by chance `[Both]`. The order the `[JP]` guides prescribe:

1. Build the grandparent tier first, four grandparents holding the target aptitude at three stars, then promote that stock a generation further, because reusable grandparents feed many later parents.
2. Spend factor research rewards on grandparents rather than parents: a research-built parent specializes in one competition rotation, while a finished grandparent transfers to every parent made afterwards.
3. Rank aptitude factors by what they move: distance aptitude drives late top speed, running-style aptitude drives positioning, surface aptitude drives acceleration; unique factors are a small cost cut plus a trigger-rate bump, and stat factors lost value as scenarios became more generous.
4. Keep white factors in the stock, since they only fire mid-run and cannot be front-loaded at career start.
5. Stack the same factor across the diagram instead of rerolling it: a Kamigame 50-run trial reported the child carrying the target scenario factor 11 times with a partial ancestor setup and 15 times when all six diagram members carried it.

The 2021-era four-member rotation, cycling the same four Umamusume through parent and child slots to hold ◎ permanently, is documented as no longer worth the effort once the roster grew and compatibility rules were understood, and current practice replaces it with the grandparent stock above `[JP]`.

**Sources:** `[A]` Kamigame factor farming, 2026-08-25, definition and grandparent-first order: https://kamigame.jp/umamusume/page/147455032642539573.html; `[A]` Kamigame factor research, 2026-09-16, reward spending and factor priority: https://kamigame.jp/umamusume/page/251735276060200383.html; `[A]` GameWith factor farming, 2026-09-26: https://gamewith.jp/uma-musume/article/show/258843; ⚠️ STALE: Kamigame factor mechanics dated 2025-06-05, 11 of 50 and 15 of 50 trial: https://kamigame.jp/umamusume/page/154134787475434233.html; ⚠️ STALE: Kamigame factor loop guide dated 2025-06-05, text itself dated 2023, rotation called obsolete: https://kamigame.jp/umamusume/page/147871627357506165.html

### 1.6 Additional Systems and Game Modes

### 1.6.0 Mode availability matrix

Mode names are `[JP]` client wording first, `[Global]` client wording second.

| Mode | `[JP]` state on 2026-09-27 | `[Global]` state on 2026-09-27 | Entry gate | Main payout | Evidence |
|---|---|---|---|---|---|
| Training scenarios (育成シナリオ) | Live: いらっしゃい！トレセン軒！ since 2026-06-29, 14 total selectable | Live: Brighter Together Our Grand Concert since 2026-07-22, 4 total selectable | Story/Career start screen | A Veteran Umamusume plus Factor and title rewards | `scenarios.json`; [JP news index](https://umamusume.jp/news/?t=game) |
| Champions Meeting (チャンピオンズミーティング / "Champions Meeting") | MILE ran 09-18 12:00 to 09-24 11:59; CLASSIC league selection open since 09-26, race 09-29 to 10-05 | Scorpio Cup ran 09-19 22:00 to 09-25 21:59 UTC; next edition unannounced at the anchor | Team Rank E2, three Veteran Umamusume | Carats, Scout Tickets, league and placement titles | [JP 3453](https://umamusume.jp/news/detail?id=3453), [JP 3463](https://umamusume.jp/news/detail?id=3463), [Global 1050](https://umamusume.com/news/1050/) |
| Masters Challenge (マスターズチャレンジ) | Live: 9th edition 07-30 12:00 to 10-26 11:59 | ❌ UNVERIFIED, no Global notice in the Global index | None stated, open during the window | 300 Carats per first clear per level, Crystal Shards, titles | [JP 3363](https://umamusume.jp/news/detail?id=3363), [GameWith 2026-09-26](https://gamewith.jp/uma-musume/article/show/431722), [Kamigame 2026-08-01](https://kamigame.jp/umamusume/page/296436114980404472.html) |
| Team Trials (チーム競技場) | Live, permanent, daily mission | Live, permanent, daily mission "[Daily 4] Play Team Trials" | Unlocks early in the tutorial, 1 RP per entry per game8.co | Carats and Friend Points at class endpoints, Winning Rewards | [game8.co 2026-09-23](https://game8.co/games/Umamusume-Pretty-Derby/archives/536831), [Kamigame 2026-09-18](https://kamigame.jp/umamusume/page/144381813119318601.html), `en/missions/daily` |
| Racing Carnival (レーシングカーニバル) | No edition after 2024-10-20 in the `[JP]` mission export or on the guide page | Recurring; export records editions 2026-02-05 to 02-15 and 2026-04-16 to 04-23 | Event window only | Carnival Pts, spent in the event shop on Uncap Crystals and tickets | `missions/racingcarnival-daily`, `en/missions/racingcarnival-*`; [Game8 2026-04-14](https://game8.jp/umamusume/421662) |
| Legend Race (レジェンドレース) | Monthly, ~6-day windows; newest export record スワンステークス 09-06 12:00 to 09-12 04:59 JST | Monthly; newest export record 2026-09-03 22:00 to 09-09 14:59 UTC (2 stages, 2000m, name field empty) | Legend Race Ticket, granted at the 05:00 server reset during an edition | Character Pieces, Carats, Monies, Support Points | `events__legend-race.json`, `en/events/legend-race`; [GameWith 2026-09-23](https://gamewith.jp/uma-musume/article/show/261413) |
| Daily Legend Race (デイリーレジェンドレース) | Live, permanent rotation | Live, permanent (Global mission text "Run in 21 Legend Races") | Daily Legend Race Ticket, one per daily reset | Same family as Legend Race, smaller | `items.json` id 168, `en/missions/legendrace-limited` |
| Training Pass (トレーニングパス) | Live: aggregation period 09-24 12:00 to 10-26 11:59 | ❌ UNVERIFIED, no Global notice | Menu, then トレーニングパス | Free track plus a once-per-period Premium Pass worth 350 paid Carats | [JP 3455](https://umamusume.jp/news/detail?id=3455) |
| 自主トレ育成 ("Independent Training") | Live since the 2026-06-29 update | ❌ UNVERIFIED, client string exists, no Global notice | Own tab on the pre-training confirmation screen | Fans, Carats, Club Points, winner's sashes, Factors | [Game8 2026-06-30](https://game8.jp/umamusume/794628), [Kamigame 2026-07-20](https://kamigame.jp/umamusume/page/429951462450165905.html), [JP 3476](https://umamusume.jp/news/detail?id=3476) |
| Transfer Requests (Global wording) | Not documented in the sources read for this section | Live: first window 2026-09-24 22:00 to 2026-09-28 21:59 UTC | Request list refreshed every two days | Trainee Star Pieces, Monies, Support Points | [Global 1058](https://umamusume.com/news/1058/), [game8.co 2025-10-05, ⚠️ STALE](https://game8.co/games/Umamusume-Pretty-Derby/archives/554781) |
| Aim for the Stars! Dream Team (目指せ！最強チーム) | Limited event, Scout Race sub-mode | Limited event, editions recorded in Global mission keys | Scout Race Ticket | Scout Points, used to recruit team members | `items.json` ids 1001 and 2001, `en/missions/teamscout-*` |
| League of Heroes (リーグ オブ ヒーローズ) | Race mode behind item 3001; out of scope for 1.6 | Global client text exists ("a League of Heroes race") | League Ticket | Not researched here | `items.json` id 3001 |

#### 1.6.1 Training scenarios

A Career run picks one scenario, and the scenario decides the target-race calendar, the scenario-exclusive mechanics, and the stat caps. The live list is the export list, not a remembered one: `scenarios.json` carries 14 rows with per-server start epochs.

| Order | `[JP]` title | `[Global]` title (client or export label) | `[JP]` live since | `[Global]` live since | Tag |
|---|---|---|---|---|---|
| 1 | 新設！URAファイナルズ | Ura Finale (full: The Beginning: URA Finale) | 2021-02-24 | 2025-06-26 | `[Both]` |
| 2 | アオハル杯 | Unity Cup (full: Unity Cup: Shine On, Team Spirit!) | 2021-08-30 | 2025-11-06 | `[Both]` |
| 3 | Make a new track!! クライマックス開幕 | export label Trackblazer; Global mission text says Twinkle Star Climax | 2022-02-24 | 2026-03-12 | `[Both]` |
| 4 | つなげ、照らせ、ひかれ。私たちのグランドライブ | Brighter Together Our Grand Concert | 2022-08-24 | 2026-07-22 | `[Both]` |
| 5 to 14 | グランドマスターズ, L'Arc, U.A.F., 大豊食祭, メカウマ娘, The Twinkle Legends, 無人島, ゆこま温泉郷, Beyond Dreams, トレセン軒 | no Global release date in the export (`start_en` absent) | 2023-02-24 to 2026-06-29 | none | `[JP-Only]` |

So the current scenario is トレセン軒 on `[JP]` (order 14, `start_ja` 2026-06-29) and Brighter Together Our Grand Concert on `[Global]` (order 4, `start_en` 2026-07-22). Two independent confirmations for the `[JP]` date: the 自主トレ育成 bug window in the official notice opens 2026/6/29 12:00, the same update boundary ([Umamusume JP Official News, 2026.09.25](https://umamusume.jp/news/detail?id=3476)), and Kamigame carries a scenario guide titled 新シナリオ「トレセン軒」の攻略と立ち回り dated 2026-09-11 ([Kamigame](https://kamigame.jp/umamusume/page/427218893275192287.html)). The English titles for orders 5 to 14 in the export are GameTora labels, not Global client names, because those scenarios never shipped on Global; do not treat them as official `[Global]` wording.

**Sources:** data export `scenarios` from the data export (tier B, fetched 2026-09-27), `en/missions/trainerexam-limited` (Global client text), [Umamusume JP Official News, 2026.09.25](https://umamusume.jp/news/detail?id=3476), [Kamigame 2026-09-11](https://kamigame.jp/umamusume/page/427218893275192287.html).

#### 1.6.2 Champions Meeting: the MILE and CLASSIC question resolved

Both servers run the same three-round skeleton, and the two September 2026 notices read side by side show where they diverge.

What the labels are. `[JP]` editions are named by a category tag drawn from a fixed set of five: SPRINT, MILE, CLASSIC, LONG, DIRT. The tag is not the event's sponsor name and not the client's distance class. Evidence chain: (1) the `[JP]` export `events__champions-meeting.json` has 49 dated rows, rows 1 to 24 (2021-05 to 2023-04) carry zodiac cup names such as タウラス杯 / Taurus Cup and ジェミニ杯 / Gemini Cup, and from row 25 (2023-06-13) the name field becomes a tag, with tag and `resource_id` locked 1:1 (13 SPRINT, 14 MILE, 15 CLASSIC, 16 LONG, 17 DIRT) across all 25 later rows; (2) the official notices read 「レースイベント『チャンピオンズミーティング MILE』」 and 「レースイベント『チャンピオンズミーティング CLASSIC』」; (3) each notice also prints the target race with the client's own distance class, and they disagree in the useful direction: the CLASSIC edition is ロンシャン 芝 2400m（中距離） and the MILE edition is 東京 芝 1800m（マイル）. CLASSIC therefore means the middle-distance "classic" band (2000 to 2400m in the export rows) inside Champions Meeting, not a distance class the client displays.

`[Global]` never adopted the tag scheme: all 19 rows of `en/events/champions-meeting` use the zodiac cups (Taurus Cup through Scorpio Cup, `resource_id` 1 to 12 only, no 13 to 17), and the official Global headline is "The race event Champions Meeting: Scorpio Cup is here!" ([Umamusume Global Official News, 2026-09-19](https://umamusume.com/news/1050/)). The Global zodiac series is the retired `[JP]` series, race setups included: Global's Scorpio Cup (2026-09-19) is Kyoto / Turf / 2,200m / Right-Handed / Outer / Autumn, and `[JP]`'s own Scorpio Cup row of 2022-11-13 is distance 2200, ground 1, turn 1, season 3, same track id. A pipeline that joins the two servers on event name will silently miss every post-2023 `[JP]` edition; join on `resource_id` and the window instead.

League selection (`参加リーグ選択期間` / "League Selection Period"). Two leagues, chosen before the race and locked once Round 1 opens: `[JP]` 「グレードリーグ」 and 「オープンリーグ」, `[Global]` "Graded League" and "Open League". Matching happens only inside a league, rewards differ by league, and choosing one pays a participation reward (Toughness 30 ×3 and Alarm Clock ×3 on both servers). The eligibility rule is not equivalent across servers: `[JP]` Open League accepts up to 育成ランク [UC] and blocks [UC1] and above, while `[Global]` Open League accepts Career Rank A+ or below and blocks S or above ([Umamusume JP Official News, 2026.09.26](https://umamusume.jp/news/detail?id=3463), [Umamusume Global Official News, 2026-09-19](https://umamusume.com/news/1050/)). `[JP]` has extended the rank ladder past S, so the same bracket means a different ceiling.

Qualifying window and race setup, CLASSIC (next `[JP]` edition): selection 09-26 12:00 to 10-03 11:59; event 09-29 12:00 to 10-05 11:59; Round 1 09-29 to 10-01, Round 2 10-01 to 10-03, final registration 10-03 12:00 to 23:59, matching 10-04 0:00 to 11:59, race 10-04 12:00 to 10-05 11:59. Rounds 1 and 2 are the preliminaries; Round 2 and the final split into グループ A and B (`[Global]` "Group A/B"). Each entry is five races, three trainers matched per race, nine runners per race, up to four entries per day. Three or more wins sends you to Group A of the next round; fewer drops you to Group B; zero wins in Round 2 eliminates you. The final round is one race between three trainers. Entry costs an Entry Ticket or 30 Carats on both servers, but the free-grant mechanic differs (conflict log row 3).

Rewards. Per-entry rewards in the preliminaries scale with wins; the final pays by league, group and placement, including event-exclusive titles that gain a star when re-earned. `[JP]` titles are named after the tag: MILEプラチナ / ゴールド / シルバー / ブロンズ and CLASSICプラチナ etc. `[Global]` titles are named after the cup: "Scorpio Cup Platinum / Gold / Silver / Bronze". The CLASSIC edition also carries a special rule, 「特殊ルール：デバフなし」, which switches off a published list of 50 skills plus 5 継承スキル while leaving unique and evolved skills active even when they contain debuff effects; the MILE notice lists no special rule, and the Global Scorpio Cup instead switched off Night Races, Sharp Turns and Collaborative Graded Races in three tiers each. `[JP]` daily event missions during Rounds 1 and 2 pay 栄誉のメダリオン (Honor Medallion, export `items.json` id 268); Global pays "<Cup> Entry Tickets", two per day.

**Sources:** [Umamusume JP Official News, 2026.09.26 (CLASSIC)](https://umamusume.jp/news/detail?id=3463), [Umamusume JP Official News, 2026.09.18 (MILE)](https://umamusume.jp/news/detail?id=3453), [Umamusume Global Official News, 2026.09.19 (Scorpio Cup)](https://umamusume.com/news/1050/), [Umamusume Global Official News index](https://umamusume.com/news/), data export datasets `events__champions-meeting.json`, `en/events/champions-meeting`, `missions/championsmeeting`, `en/missions/championsmeeting`, `items.json` ids 198 and 268 (tier B).

#### 1.6.3 Masters Challenge `[JP]`

A high-difficulty limited-time race event against preset strong opponent Umamusume, described by GameWith as 「非常に強力な対戦ウマ娘が出走するレースに挑戦する高難度イベントで、勝利することでジュエルや虹の結晶片などの豪華報酬を獲得できる」 ([GameWith, 2026-09-26](https://gamewith.jp/uma-musume/article/show/431722)). The 9th edition runs 07-30 12:00 to 10-26 11:59 and covers one course per distance category: スワンS (短距離), 毎日王冠 (マイル), 皐月賞 (中距離), 有馬記念 (長距離), 帝王賞 (ダート) ([Kamigame, 2026-08-01](https://kamigame.jp/umamusume/page/296436114980404472.html)). Difficulty tiers Lv1 to Lv3 pay 300 Carats each on first clear, Lv1 adds a Gold Crystal Shard, Lv2 a Rainbow Crystal Shard, Lv3 an exclusive title; Kamigame totals a Lv2 clear on all five courses at 3000 Carats plus five Rainbow Crystal Shards (the shard the shop exchanges for a Rainbow Uncap Crystal). Each registered Veteran Umamusume may run at most three times, win or lose, which is the anti-grind cap; fielding the same Umamusume as a rival thins the field. Access needs no unlock, only the open window. The official notice of 2026.09.26 confirms the current limited race closes 10-26 11:59, that the payout is Carats and 結晶片, and that expired limited races are scheduled for later addition to アーカイブス (Archives) with rewards and completion state carried over, no date fixed yet ([Umamusume JP Official News, 2026.09.26](https://umamusume.jp/news/detail?id=3363)). Kamigame's Archives section is the index of editions 1 to 8.

**Sources:** [Umamusume JP Official News, 2026.09.26](https://umamusume.jp/news/detail?id=3363), [GameWith, 2026-09-26](https://gamewith.jp/uma-musume/article/show/431722), [Kamigame, 2026-08-01](https://kamigame.jp/umamusume/page/296436114980404472.html), `items.json` ids 149 and 150 (Global names "Rainbow Crystal Shard" / "Gold Crystal Shard").

#### 1.6.4 Team Trials (`[Both]`), and why "Team Stadium" is the wrong key

There is no mode named Team Stadium on either server. The monthly-style team race mode is `[JP]` チーム競技場, and the `[Global]` client calls it Team Trials: the Global daily mission text is "[Daily 4] Play Team Trials", and the `[JP]` daily row is 「【デイリーミッション④】 チーム競技場をプレイしよう」 (`en/missions/daily` and `missions/daily`). Kamigame's guide describes it as a three-Umamusume team format raced across five categories, 短距離・マイル・中距離・長距離・ダート, with score from finishes, gate position and opponent strength, and a weekly class placement (クラス6 to クラス1) that promotes, holds or demotes the team; class endpoints pay Carats and Friend Points, e.g. holding クラス6 pays 250 Carats and 5000 Friend Points ([Kamigame, 2026-09-18](https://kamigame.jp/umamusume/page/144381813119318601.html)). game8.co matches it from the Global build: "You set up a team of Veteran Umamusume characters and compete against other players in five races", one entry costs 1 RP, roster slots grow from one to three with division tier, and the mode resets weekly on Monday 15:00 UTC with classification closing on a six-day cycle ([game8.co, 2026-09-23](https://game8.co/games/Umamusume-Pretty-Derby/archives/536831)). Two ladders exist and are easy to conflate: the long-running チームランク / "Team Rank" (E, E2, ... S), which gates Champions Meeting at E2, and the weekly class/division placement. Team Trials is also where the weather, gate and mood consumables are spent, which the client item text states (「チーム競技場などで使用できる」, Global names Pleasing Parfait, Sunshine Doll, Rainfall Doll, Inner Post Raffle Ball, Outer Post Raffle Ball). A third thing, `[JP]` 目指せ！最強チーム with its スカウトレース, is a limited team-building event whose Global client text is "Aim for the Stars! Dream Team Scout Race" (`items.json` ids 1001, 2001); the export key `missions/teamscout-*` belongs to that event, not to Team Trials.

**Sources:** [game8.co, 2026-09-23](https://game8.co/games/Umamusume-Pretty-Derby/archives/536831), [Kamigame, 2026-09-18](https://kamigame.jp/umamusume/page/144381813119318601.html), data export datasets `missions/daily`, `en/missions/daily`, `items.json` ids 116 to 120, 1001, 2001 (tier B).

#### 1.6.5 Racing Carnival `[Global]` live, `[JP]` dormant

「期間限定のレースで決められたウマ娘と戦うイベントです」: a recurring limited event where you enter one Umamusume against a designated opponent Umamusume on one announced course, repeatedly, and farm Carnival Pts ([Game8, 2026-04-14](https://game8.jp/umamusume/421662)). That guide records 1 RP per attempt with no daily entry cap, a fixed course for the run (Kyoto mile in the edition documented), HARD races filling a gauge that opens CHALLENGE RACES at Very Hard and Extreme, a retry stat bonus stacking up to +150% on consecutive losses, and a ban on fielding the designated rival yourself. Its shop prices listed are Rainbow Crystal Shards at 30000 Pt and Gold at 15000 Pt with Scout and Support tickets at 6000 Pt, and 100000 Pt is the baseline completion target. The client item text agrees on the loop: 「レーシングカーニバルのレースに出走すると入手できる。ショップで様々なものと交換できる」 for カーニバルPt, Global name "Carnival Pts" (`items.json` id 159). A top-five finish also grants a temporary Racing Carnival Factor, whose level raises the Carnival Bonus skill rank up to Lv3, and the factor disappears when the event ends; the `[Global]` client mission text for the same mechanic is "Limited-Time: Receive the Lvl 1 Carnival Bonus Spark", so on Global the Factor is called a Spark. Server status is the notable part: the `[JP]` mission export has no Carnival window after 2024-10-19 20:00 to 10-20 02:59 UTC (10-20 11:59 JST), and the Game8 page says the most recent documented run ended 2024-10-20, while the Global export carries 2026 editions 02-05 to 02-15 and 04-16 to 04-23. Treat Racing Carnival as running on `[Global]` now and dormant on `[JP]`; no edition is verified live on 2026-09-27 on either server.

**Sources:** [Game8, 2026-04-14](https://game8.jp/umamusume/421662), data export datasets `missions/racingcarnival-daily`, `missions/racingcarnival-limited`, `en/missions/racingcarnival-daily`, `en/missions/racingcarnival-limited`, `items.json` id 159, `factors.json` ids 40001 to 40004 (tier B).

#### 1.6.6 Legend Race and Daily Legend Race `[Both]`

「レジェンドレースは特定のキャラと対戦するイベント」: a monthly window, about six days, against fixed legendary opponent line-ups on an announced course, with the recommended routine of three entries every day because entry pays even without a win ([GameWith, 2026-09-23](https://gamewith.jp/uma-musume/article/show/261413)). First clear pays character pieces, Carats and Monies; every entry pays pieces; wins roll pieces, Monies or Support Points; event missions add pieces and high-tier Crystal Shards; and the placement trophy needs a first. The `[JP]` export matches: newest record スワンステークス, 2026-09-06 12:00 to 09-12 04:59 JST, two stages, each stage carrying its own boss stat array and its own opponent card id. `[Global]` runs the same mode with two to four stages per edition, the newest export record being 2026-09-03 22:00 to 09-09 14:59 UTC over 2000m with an empty name field, while the older Global rows do carry names with a difficulty suffix, e.g. "Japan Cup (Hard)", "Sprinters Stakes (Hard)". Entry is ticket-gated: 「レジェンドレースの挑戦に必要なチケット」 with obtain text "Automatically obtained at daily reset (5 AM Server time) during legend race events" (`items.json` id 97, Global name "Legend Race Ticket"), and the permanent デイリーレジェンドレース variant has its own ticket at id 168, refreshed at every daily reset on both servers. `[Global]` mission text confirms the live loop: "Limited-Time: Run in 6 Legend Races" through 2026-09-03.

**Sources:** [GameWith, 2026-09-23](https://gamewith.jp/uma-musume/article/show/261413), data export datasets `events__legend-race.json` (49+ rows with stage-level boss stats), `en/events/legend-race` (15 rows), `missions/legendrace-daily`, `en/missions/legendrace-limited`, `items.json` ids 97 and 168 (tier B).

#### 1.6.7 Training Pass `[JP]`

A periodic reward track fed by training activity, reached from 「[メニュー]内の[トレーニングパス]」. The aggregation period (集計期間) rolled over at 09-24 12:00 and now closes 10-26 11:59, the same boundary as the Masters Challenge window. Progress is measured in training-achievement Pt, rewards are claimed directly on the pass screen, and unclaimed rewards are mailed to Presents when the period ends. A paid tier, プレミアムパス, may be bought once per period and grants 350 paid Carats plus the premium-only reward track; it can be bought from the pass footer, from the Carats "+" on the home screen, or from メニュー > ジュエル/その他購入, and it is blocked in the final day's 05:00 to 11:59 window. Premium rewards unlock retroactively for the period's passes already completed. Pass reward contents and the Pt thresholds are stated to vary per period ([Umamusume JP Official News, 2026.09.24](https://umamusume.jp/news/detail?id=3455)). Single-domain evidence: no second source for the mode was found in this session, so the reward table itself is `❌ UNVERIFIED: no current source found. Last known: the notice above names only 有償ジュエル350個 for the premium purchase.` Global status is also `❌ UNVERIFIED: no Global notice in the umamusume.com news index. Last known: none.`

**Sources:** [Umamusume JP Official News, 2026.09.24](https://umamusume.jp/news/detail?id=3455), [Umamusume Global Official News index](https://umamusume.com/news/).

#### 1.6.8 自主トレ育成: what the mode actually does

`[JP]` 自主トレ育成 is an idle training feature, not a manual mode and not a separate race mode: 「自主トレ育成は、育成開始から50分後に自動で育成が完了前まで進行する放置育成機能です」, and the app may be closed while the timer runs ([Game8, 2026-06-30](https://game8.jp/umamusume/794628)). It is entered from its own tab on the pre-training confirmation screen, where the player fixes three things before walking away: a training policy (バランス重視 / スタミナ重視 / スプリント重視), a race rotation (custom micro-route or target events only), and up to ten priority skill hints that steer which event skills the run takes ([Kamigame, 2026-07-20](https://kamigame.jp/umamusume/page/429951462450165905.html)). It is a resource farm rather than a top-grade builder: Kamigame profiles it as the recommended way to grind fans, Carats, Club Points (トレーナーメダル), winner's sashes and Factors, roughly 1.3 million fans per finished run, and warns that the Stamina and Sprint policies skew stats and cost win rate. Game8 lists the ceilings: a race win inside the mode awards no trophy and unlocks no Winning Live or Gallery content, and skills that evolve off rest counts do not advance. Kamigame records 20 runs per day, counted against the pre-existing daily cap on free Carats from training race wins; Game8 documents no cap on the mode itself (conflict log row 8). Live since the 2026-06-29 update on `[JP]`; on `[Global]` the client string exists (nickname 394: 「自主トレウマ娘」 with `desc_jp` 「自主トレ育成で育成を完了する」, Global text "Independent Learner" / "Complete a Career with Independent Training") but no Global notice was found, so Global availability is `❌ UNVERIFIED: no Global release notice found. Last known: Global client string "Independent Training" in local nicknames.json.` Note that 育成プランシート and スキルセット are `[JP]`-side vocabulary from the same cycle and have no Global equivalents in the strings read here.

Official status evidence: `[JP]` reported on 2026.09.25 17:03 that in 自主トレ育成 「下記の育成イベントで報酬が正しく獲得できない場合がある」 for a named set of training events (Seiun Sky's 「釣果アリ」, the [Glorious Coat] Winning Ticket card's 「チケゾーチャンネル」, the [Reines Plätschern] Eishin Flash pair 「最適なスケジュール」 and 「彼の都の思い出は」, and the [パステルマリン・ロコドル] Hokko Tarumae pair), that the defect window was 2026/6/29 12:00 to 2026/9/25 15:34, that a fix shipped, and that compensation was Toughness 30 ×4 ([Umamusume JP Official News, 2026.09.25](https://umamusume.jp/news/detail?id=3476)). The bug's scope is itself proof of what the mode does: it plays training events, and their rewards are the payload.

**Sources:** [Game8, 2026-06-30](https://game8.jp/umamusume/794628), [Kamigame, 2026-07-20](https://kamigame.jp/umamusume/page/429951462450165905.html), [Umamusume JP Official News, 2026.09.25](https://umamusume.jp/news/detail?id=3476), `nicknames.json` id 394 (tier B).

#### 1.6.9 Transfer Requests `[Global]`

"Transfer Requests allow trainers to receive rewards by transferring Veteran Umamusume that meet the recipient trainer's desired conditions" ([Umamusume Global Official News, 2026-09-24](https://umamusume.com/news/1058/)). The first window ran 2026-09-24 22:00 to 2026-09-28 21:59 UTC, previewed one day earlier by "Transfer Requests coming soon!" ([Umamusume Global Official News index](https://umamusume.com/news/)). Mechanics, from the same notice: each request belongs to a named trainer and carries conditions on the Veteran Umamusume's abilities and achievements, a rank that scales the payout, its own transfer availability period, and a remaining-transfer counter that decrements once per transfer and locks the request at zero; the request list refreshes every two days at 22:00 UTC; a transferred Veteran Umamusume disappears and cannot be recovered. Rewards are Trainee Umamusume Star Pieces (matched to the transferred Umamusume, and not the alternate-outfit versions), Monies, and Support Points, added straight to totals. Help location: Transfer Requests under Umamusume ([Umamusume Global Official News, 2026-09-24](https://umamusume.com/news/1058/)).

Second source, [game8.co Transfer Request guide, ⚠️ STALE: dated 2025-10-05](https://game8.co/games/Umamusume-Pretty-Derby/archives/554781): "Transfer Request allows you to transfer your Veteran Umamusume for increased rewards", "Requirements normally include Stat or Aptitude requirements, specific Race wins, or specific Epithets", and expired assignments are replaced on a timer. It documents three concurrent requests, single-completion high-tier assignments, and rewards including Star Shards, Dream Glimmer and Hint Books. The two sources agree on the loop, the rank scaling, the per-request limit and the deletion consequence; they disagree on the reward label and on how new the mode is (conflict log rows 6 and 7). The official notice governs the live window. `[JP]` status: not researched for this section, `❌ UNVERIFIED: no JP-side source read. Last known: the JP export carries mission keys specialtransfer/specialtransfer_event with zero rows and an empty schedule object, which is not evidence of a live JP mode.`

**Sources:** [Umamusume Global Official News, 2026-09-24](https://umamusume.com/news/1058/), [Umamusume Global Official News index](https://umamusume.com/news/), [game8.co, ⚠️ STALE 2025-10-05](https://game8.co/games/Umamusume-Pretty-Derby/archives/554781), `data/manifest.json` keys `missions/specialtransfer/*` (tier B).

#### 1.6.10 Consumables, and where each is actually spent `[Both]`

Spendable items are not support effects, and the two are conflated constantly in third-party guides. Three questions sort every item here: *does it act before a run, inside a career, or on a race entry?* and *which mode consumes it?*

**Two widely-circulated items do not do what guides claim.** ❌ **No item on either client guarantees training success.** The real zero-failure levers are: the Trackblazer shop's **Good-Luck Charm** (40 coins, failure rate 0% for 1 turn, 2.3), **Extreme Spirit Burst's** 0% on its own facility (2.2), and the support effect **Failure Protection** (id 27, a multiplicative reduction, 1.1.5). And the JP item family 「虹の蹄鉄 / 金の蹄鉄 / 銀の蹄鉄」 (`items.json` ids 48, 49, 50) is a **shop currency**, not a training buff: `[Global]` client text renders it **Rainbow / Gold / Silver Cleat**, one duplicate SSR support card converts to **10 Rainbow Cleats** via Storage → Support Cards, and they are spent in the **Cleat Exchange** on Scout Tickets (×2), the "SR+ Guaranteed Make Debut" ticket (×2), Dream Glimmer and Winner's Sashes. ⚠️ Third-party English pages render that JP noun with an equine word — the same string `DESIGN.md` lists among forbidden visual motifs. It is not client text; never key on it, in data or in copy. Do not confuse the **Cleat** currency with Trackblazer's **Artisan / Master Cleat Hammer** shop items, which are Race Bonus purchases (2.3).

⚠️ Likewise, a **"Goddess Statue"** (`[JP]` 「女神像」) exists on `[Global]` but is **pure currency**: exchanged for Trainee Star Pieces in the **Statue Exchange** shop on an escalating **×1 → ×5** rate, **650 Star Pieces** to max a trainee, with the client stating statues "cannot be used to unlock an unscouted Trainee". **It grants no buff**, so it is not a scenario modifier of any kind.

| Item (`[Global]` client name) | Effect, as the client puts it | Where it is spent |
|---|---|---|
| **Alarm Clock** | "Lets you try again on a Career goal race" | **Career only.** Retries a missed mandatory placing (1.2.6), and a lost Unity Cup **Team Race** since the 2026-07-01 rework (2.2). Also paid as a league-selection participation reward (1.6.2) |
| **Toughness 30** | "Restores 30 TP" | Compensation currency (1.1.7, 1.6.3). ❌ **What TP governs is unverified**, and it is *not* the training Energy bar — do not model it as energy |
| **Pleasing Parfait** | "Raises a runner's mood to Great" | **Team Trials and Daily Races** (1.6.4) |
| **Sunshine Doll** / **Rainfall Doll** | Set weather to **Sunny** and footing to **Firm** / to **Rain** and **Heavy** | Team Trials and Daily Races. ⚠️ Note the interaction with 1.2.5: choosing Rain via the doll makes Soft-or-Heavy footing near-mandatory, so the two effects are one decision, not two |
| **Inner Post Raffle Ball** / **Outer Post Raffle Ball** | Draw an **inner bracket (1-3)** / an **outer (6-8)** gate | Team Trials and Daily Races. Gate *position* is separate from the slow-start penalty mechanics in 1.2.3 |
| **Books of Hints** — Book of Hints, Rare Book of Hints, Textbook of Hints, Rare Textbook of Hints | Raise a skill's **hint level** | **Before** a run, on the deck/build screen. Costs on an unenhanced card to Lv3: 12 / 6 / 30 of 「ヒント本 / ヒント専門書 / 夢の煌めき」 (Dream Glimmer), 1.4.4 |
| **Rainbow / Gold Uncap Crystal** | "Uncaps an SSR / SR Support Card" | Card leveling, `[Global]` only — `[JP]` replaced that model on 2025-10-07 (1.4.2) |
| **Rainbow / Gold Crystal Shard** | Exchange material | Bought with Carnival Pts in the Racing Carnival shop (1.6.5) |

⚠️ **Spend-site error to avoid:** the mood, weather and gate items above are **Team Trials** (`[JP]` チーム競技場) and **Daily Races** consumables. Guides routinely place them in **Champions Meeting**, which publishes no such item path (1.6.2, 1.6.4). ❌ **`"Special Katsu Curry"` appears in no Global item list found**; treat it as not-Global rather than as a hidden Energy item.

**Sources:** [game8.co Global item list, 2026-07-14](https://game8.co/games/Umamusume-Pretty-Derby/archives/538152); [game8.co consumables list, 2026-07-13](https://game8.co/games/Umamusume-Pretty-Derby/archives/543018); [game8.co Cleat exchange, 2026-03-12](https://game8.co/games/Umamusume-Pretty-Derby/archives/543930); [game8.co Statue Exchange, 2026-03-12](https://game8.co/games/Umamusume-Pretty-Derby/archives/542870); [Game8 JP item list, 2026-09-15](https://game8.jp/umamusume/418448); [Game8 JP Cleat shop, 2025-11-20](https://game8.jp/umamusume/419913); data export `items.json` ids 48 to 50, 97, 116 to 120, 144 to 150, 159, 168, 195, 268 with `[Global]` `name_en` / `desc_en` (tier B, fetched 2026-09-27).

## Section 2: Scenario Strategies and Mechanics, `[Global]`

Freshness anchor 2026-09-27. Scope is `[Global]` only, per instruction. The ten `[JP-Only]` scenarios
and the current `[JP]` scenario トレセン軒 are excluded; a separate `[JP]` capture of トレセン軒 was
taken and parked in this directory, unused here.

### 2.1. The Global scenario surface

`[Global]` runs four of the fourteen scenarios that exist in the client's scenario data. The gap
between the servers is ten scenarios, which is why scenario advice copied from `[JP]` sources is
usually not actionable here.

| # | Scenario (`[Global]` EN) | JP name | `[Global]` start (UTC) | JP start (JST) | Stat cap Sp / St / Pw / Gu / Wi |
|---|---|---|---|---|---|
| 1 | URA Finale | 新設！URAファイナルズ | 2025-06-26 | 2021-02-24 | 1400 / 1400 / 1400 / 1400 / 1400 |
| 2 | Unity Cup | アオハル杯～輝け、チームの絆～ | 2025-11-06 | 2021-08-30 | 1300 / 1300 / 1300 / 1300 / 1800 |
| 3 | Trackblazer | Make a new track!!～クライマックス開幕～ | 2026-03-12 | 2022-02-24 | 1200 / 1900 / 1200 / 1200 / 1500 |
| 4 | Grand Live (Grand Concert) | つなげ、照らせ、ひかれ。私たちのグランドライブ | 2026-07-22 | 2022-08-24 | 1600 / 1300 / 1300 / 1500 / 1300 [User-Supplied / Confirmed via Famitsu & GameWith extraction, 2026-09-27] |

Dates and the cap formula come from the GameTora data export
([scenarios dataset](https://gametora.com/data/umamusume/scenarios.61b7c51c.json)), fetched
2026-09-27.

### How the caps were derived, and why they can be trusted

The export stores a `stats` array per scenario. It is not a starting-stat allocation: it is the
per-stat cap bonus over a base cap of 1200. Three independent readings confirm it, each matching the
arithmetic exactly:

| Scenario | Export `stats` | 1200 + bonus | Published by an independent source |
|---|---|---|---|
| URA Finale | [200, 200, 200, 200, 200] | 1400 across all five | Stated as 1400 across all five stats |
| Unity Cup | [100, 100, 100, 100, 600] | 1300 x4, Wit 1800 | `docs/scenarios/02-unity-cup.md:170` states exactly 1300 / 1800 |
| Grand Masters | [300, 200, 300, 100, 100] | 1500 / 1400 / 1500 / 1300 / 1300 | `[JP]` only, listed here as a formula check, not as Global data |

The same arithmetic also explains a contested figure in `docs/UMAMUSUME_REFERENCE.md`: a 2100 Speed
target is Beyond Dreams' Speed cap (1200 + 900), and 2000 is the separate database `hard_caps`
ceiling for the twelve older scenarios, which rises to 2500 for the newest two. Both figures were
real; they measured different things.

Two caveats, stated rather than smoothed over. The negative bonus in one `[JP]` scenario's stamina
slot, giving a 1000 cap below the base, is the only such case found and is not yet confirmed by a
second source; it is out of Global scope anyway. And Grand Live's caps were a derivation from data until [User-Supplied / Confirmed via Famitsu & GameWith extraction, 2026-09-27] confirmed them figure for figure, independently of the export.

### 2.2. Per-scenario mechanics `[Global]`

Three of the four are documented in `docs/scenarios/01-ura-finale.md`, `02-unity-cup.md` and
`03-trackblazer.md` (verified present and lore-gate clean on 2026-09-27, 367 lines total). This
section summarizes what those files establish and records the mechanics claims that came from the
Game8 scenario pages, with the source noted per claim.

### 2.1 URA Finale `[Global]`

- **Facility progression by repetition.** Training facility level rises from using the same discipline
  repeatedly, rather than from a team rank or a parallel meter. This is the shared behavior that
  Trackblazer also uses.
- **The scenario's own system is the duel with Happy Meek.** She can appear on any stat training.
  Winning a duel grants stat gains, raises the applicable stat cap, gives a racing-spirit hint and
  30 Skill Points; losing still grants minor stats and 15 Skill Points. Six duel wins escalate her to
  a powered-up state, and beating that state at the finals grants the `Past My Limits` skill, 200 or
  more Skill Points, and a chance at an enhanced spirit spark, which carries a stat bonus and a skill
  hint into inheritance.
- **Deck shape.** Speed 2 or 3 plus flex; 4x Speed 2x Power for Sprint and Mile, Speed 2 plus
  Friend or Guts for Medium and Long. Sourced from `docs/scenarios/01`.
- **Fan and skill-point gates.** Senior-year Valentine 60,000 / 40,000, Fan Fest 70,000 / 60,000
  requiring a 3-bar friendship with Director Akikawa, Holiday Season 120,000 / 80,000, all from
  `docs/scenarios/01`.
- **Beginner targets.** Speed around 800 or an A rank to win the URA finale; stamina by distance
  400 Sprint, 500 Mile, 600 Medium, 700 Long.

### 2.2 Unity Cup `[Global]`

- **Team ranks drive facilities.** Team stat rank raises Training Facility levels, which is the
  structural difference from URA's repetition model, and it is why the scenario's training level can
  track team condition instead of four uses.
- **Extreme Spirit Bursts.** Purple-tier spirit bursts: large stat boosts, an `Ignited Spirit` skill,
  and a 0% failure rate on that facility for the turn. They can appear on a support that already had a
  regular burst, and an unused one can disappear and return later.
- **Alarm Clock retries.** A lost team race can be redone, which lowers the cost of a bad race day.
- **Exclusive skill payout.** Completing several spirit bursts awards an exclusive skill in early
  November of Senior year, chosen by the team's highest stat rank.
- **Cap shape.** Wit is the outlier at 1800 against 1300 elsewhere, which is the mechanical reason a
  Wit-forward deck is unusually strong here.

### 2.3 Trackblazer `[Global]`

- **Open career, no race goals, no scenario link.** The export agrees: this scenario has zero linked
  characters, the only one of the fourteen with none, and the GameTora extraction states the same thing
  in prose ("no Scenario Link mechanic attached to it"). What replaces career goals is **four shared
  objectives**, and the term for its currency is **Grade Points** — not `Result Pts`, which is Unity Cup's
  counter (2.2). An earlier revision of this bullet called the objective "Result Points"; that was a
  terminology slip across two scenario currencies, corrected here on 2026-09-27.
- **The four objectives, and their thresholds.** Late June of Junior Year: run the Debut race. End of
  Junior: 60 Grade Points. End of Classic: 300. End of Senior: 300. The thresholds have **two tracks by
  aptitude**, not one: a high-dirt or low-turf character takes 30 / 200 / 300, and a turf character whose
  range is narrow takes 60 / 200 / 300 — the guides name Haru Urara and Curren Chan as the respective
  cases. **Surplus points do not carry over**: each period starts from zero, so over-shooting one deadline
  cannot fund the next. Two independent extractions agree on all of the above
  (`docs/scenarios/04-trackblazer-umaguide.md:31-44`, `docs/scenarios/05-trackblazer-gametora.md:12-21`).
- **Missing a threshold ends the career.** This is a hard fail state, not a penalty: the run terminates to
  the standard career-end screen, and the player chooses between retiring to the results (banking Sparks)
  or spending an **Alarm Clock** to retry from a checkpoint. `[Global]`. Sourced: owner-supplied from
  in-game observation, 2026-09-27, and corroborated by the item's own client text — Alarm Clock
  (`items.json` id 95, 目覚まし時計) reads "Lets you try again on a Career goal race" and this document
  already records it retrying a missed mandatory placing (1.2.6) and a lost Team Race (2.2). A missed
  Grade Point deadline is the same category of failure, and the item is the game's answer to it.
  ❌ Partially unverified: **where** the retry resumes. "The start of that semester" is the owner's
  recollection and no source here states a resume point; the tracker must not encode one.
  The consequence matters to the schema for one reason: a `GradeDeadline` row has to be able to be
  *missed*, which a log of what happened cannot express (see `ADR-0003` item 5).
- **Pro Shop and Shop Coins.** Coins scale with race placement, 100 for first and less below, and buy
  training items. Losing reduces income, so race consistency has a direct economic penalty.
- **Rivals.** A race may carry a rival; beating one grants a skill hint tied to the race distance or
  the running style, requiring C aptitude or better in that distance.
- **No secret events.** Character secret events do not fire here, which is the mechanical reason a
  character cannot unlock an extra strategy option in this scenario.
- **Corrections to two circulating claims.** The caps are not the standard set: stamina reaches 1900
  and wit 1500. And facility leveling here follows the URA repetition model, which the guide states
  and the export neither contradicts nor confirms.

### 2.4 Grand Live / Grand Concert `[Global]`

Live on `[Global]` since 2026-07-22 and the newest scenario available there, so it is the one this
draft covers thinnest. `❌ UNVERIFIED: mechanics not extracted.` What is confirmed is the existence
and dates, and the guide that should supply the rest: [Game8 Grand Live (Grand Concert) Scenario
Guide](https://game8.co/games/Umamusume-Pretty-Derby/archives/607337), page dated 2026-07-26, plus
[Game8 Fully Charged Explained](https://game8.co/games/Umamusume-Pretty-Derby/archives/607687) dated
2026-07-02, whose title indicates the scenario's named resource mechanic. Neither was read in this
session: two rendering attempts were navigated away by a concurrent browser session before the
content could be captured. **Status changed by owner decision on 2026-09-27: extraction is suspended,
not merely unfinished**, so 2.4 stays open deliberately rather than being filled by inference. The
boundary and the matrix consequences are written up in `docs/scenarios/07-grand-concert.md`, which
records the sourced facts (titles, both server dates, **dataset order 4**, caps 1600 / 1300 / 1300 /
1500 / 1300 over the 1200 base, hard ceiling 2000, 5 linked characters, **0 frames** in
`SCREENSHOT-MANIFEST.md:39`) and marks every mechanic `❌ UNVERIFIED`. ⚠️ Two rules from that file are
load-bearing for this section: the 「Fully Charged」 / Power-1200 line above is **a name and a number
from a citation, not an extracted mechanic**, and per `CONSTRAINTS.md` D-165 and D-241 the scenario
must render as baseline widgets plus its known caps. Re-opening 2.4 requires a primary source — one of
the two Game8 pages read end to end, a `[Global]` notice, or a client capture — not a third guide
paraphrasing the first two.

### 2.3. Shared training heuristics `[Both]`

These are the rules that carry across scenarios. The evidence base is a JP guide, so the mechanics
are tagged `[Both]` where the system is common to both servers, and every number keeps its source
date.

Source: [Kamigame 育成のコツと立ち回り｜全シナリオの共通知識を解説！](https://kamigame.jp/umamusume/page/114672877806026759.html),
`⚠️ STALE: 最終更新日 2025-03-07`, read 2026-09-26 through a rendering browser.

| Rule as published | English mechanical reading | Threshold |
|---|---|---|
| 絆ゲージが8割以上になると友情練習が可能になる | Friendship training unlocks once the bond gauge reaches 80 percent | 80 |
| 体力5割未満か強い練習が無い時はお休み | Rest when energy falls below half, or when no strong training option exists | 50 percent of energy |
| やる気は「絶好調」を維持する | Keep motivation at the top tier; it raises both stat and skill-point gains and in-race performance | top tier |
| 夏合宿中は友情練習4回を狙う、直前に回復しておく | Aim for four friendship sessions during summer camp and enter it near full energy | 4 turns |
| バッドコンディションになったら保健室 | Treat a bad condition at the infirmary | condition |
| 「練習ベタ」はトレーニング失敗率が2%増えるだけで影響は小さい | The practice-unskilled condition adds only 2 percent to failure rate, so skipping treatment can be correct | +2 percent |
| 目標外のレース出走は控える | Off-objective races pay fewer stats than a training turn, so skip them unless fan counts demand them | rule |
| 育成開始時に適性をAにする（赤因子で上げる） | Raise turf, distance and strategy aptitude to A before starting via red factors; distance matters most | A |

The 80 percent bond line is a real gate, since friendship training cannot fire below it. The 50
percent rest line is advice rather than a game threshold, see section 2.6, so a planner should encode
the bond value and leave the rest value to the Trainer. The +2 percent figure for practice-unskilled
is the one that decides whether an infirmary turn pays for itself.

The page is 204 days old against the anchor. The threshold values have not been re-confirmed on a
current page in this session, so treat them as provisional, and re-check before encoding
them.

### 2.4. Source ledger and conflicts

| Source | Tier | Date | Used for |
|---|---|---|---|
| [GameTora scenarios dataset](https://gametora.com/data/umamusume/scenarios.61b7c51c.json) | B | fetched 2026-09-27 | scenario list, both server dates, `stats` and `hard_caps`, linked-character counts |
| `docs/scenarios/01-ura-finale.md` | A | read 2026-09-27 | URA mechanics, deck shapes, fan gates, strategy targets |
| `docs/scenarios/02-unity-cup.md` | A | read 2026-09-27 | Unity Cup mechanics and the 1300 / 1800 cap line |
| `docs/scenarios/03-trackblazer.md` | A | read 2026-09-27 | Trackblazer no-goals and trophy notes |
| [Game8 scenario guides 536520, 545572, 580723](https://game8.co/games/Umamusume-Pretty-Derby/archives/536520) | A | per-page dates not captured | duel, Extreme Spirit Burst and Shop Coin specifics |
| [Kamigame shared training guide](https://kamigame.jp/umamusume/page/114672877806026759.html) | A | 2025-03-07, stale | shared thresholds and the failure-rate figure |

### Conflicts and open items

1. **Trackblazer caps.** The circulating note that standard caps apply is refuted; [User-Supplied / Confirmed via Famitsu & GameWith extraction, 2026-09-27] gives Stamina 1900 and Wit
   1500. Resolution: follow the data, since it is a per-scenario published value rather than an
   inference. [User-Supplied / Confirmed via Famitsu & GameWith extraction, 2026-09-27] now prints the identical five numbers, so the figures are HIGH; the unresolved half is which
   server they apply on, not what they are.
2. **`hard_caps` versus scenario caps.** 2000 or 2500 in the export, 1200 to 2150 in practice.
   Resolved: different quantities, database ceiling versus in-run display cap. Confidence HIGH.
3. **URA stat-cap wording.** The chat summary says duels raise the cap, and URA's own guide file
   prints no cap at all, so whether 1400 is the starting cap or the post-duel ceiling is unresolved.
   Confidence LOW. Verify against the Game8 page text, not the summary.
4. **Grand Live mechanics.** Unextracted. Highest-value remaining gap for a Global audience, since
   it is the newest scenario `[Global]` actually has.
5. **Game8 page dates.** Not captured for 536520, 545572 and 580723, so their recency cannot be
   graded. A re-read must record them.
6. **Server leakage risk in `docs/scenarios/`.** This row formerly flagged only `02-unity-cup.md`,
   which carried a "future updates" section that an importer had to filter. **Resolved on
   2026-09-27:** that section was rewritten into a dated delta table, so every row in it is now live
   `[Global]` state except the one explicitly marked `[JP]`-side (the larger per-facility Skill Point
   payouts). Two files now need the opposite treatment, and they are the reason the rule is still
   open rather than closed: **`08-grand-masters-jp-only.md` is `[JP-Only]` end to end** — no Global
   release, therefore no Global client string, and every English name in it is a third-party
   rendering — and `07-grand-concert.md` carries rows marked `❌ UNVERIFIED` that must not be seeded
   as facts. An importer for scenario mechanics must read a file's **server tag and its `❌` markers**,
   not its directory, and `AGENTS.md`'s provenance rule bars storing any row without a source. The
   related design-side rule is D-285: an incoming write-up that cites this file is a mirror, not a
   second source.

### Build note

Sections 1 through 3 are complete for the four Global scenarios except 2.4. Closing 2.4 and the
conflict-3 question needs two Game8 renders and a browser session no other agent is driving; the concurrent agent that
was driving the shared session is the reason they are not done.

### 2.5. Resolutions from the full Game8 read

Source: `research-scratch/scrape-game8-scenarios.md`, 477 lines, extracted through a browser with
`location.href` asserted before each read. Page dates:
[536520 URA Finale](https://game8.co/games/Umamusume-Pretty-Derby/archives/536520) 2026-07-06,
[545572 Unity Cup](https://game8.co/games/Umamusume-Pretty-Derby/archives/545572) 2026-07-07,
[580723 Trackblazer](https://game8.co/games/Umamusume-Pretty-Derby/archives/580723) 2026-08-25.

### The July 1, 2026 Global rework

The pages date a change to `[Global]` rather than describing a static rule set: stat caps were raised
on 2026-07-01, and the purple spirit burst was added the same day. The base cap is 1200 per stat,
gains beyond it are printed as "always halved", and raised values show in gold text. A separate layer
printed as "Stat Cap increase" sits on top of the scenario figure.

That closes open item 3: URA's 1400 is the post-rework scenario cap, not a post-duel ceiling, and the
duel sits in the increase layer. It also confirms section 2.1's derivation twice more, 1400 across all
five for URA and 1300 with 1800 Wit for Unity Cup, exactly as 1200 + `scenarios.json.stats` predicts.

### Closed and still open

| Item | Status after the read |
|---|---|
| Conflict 3, URA cap wording | CLOSED, dated rework of 2026-07-01 |
| Conflict 5, missing page dates | CLOSED, all three captured above |
| Conflict 1, Trackblazer caps | CLOSED on the five figures. [User-Supplied / Confirmed via Famitsu & GameWith extraction, 2026-09-27] prints 1200 / 1900 / 1200 / 1200 / 1500, the same values the export predicts, so the numbers are HIGH. Open only on the narrower claim that the raised caps are live on `[Global]` today |
| Conflict 4, Grand Live mechanics | OPEN, narrowed. The pages reference a `Fully Charged` mechanic requiring Power at 1200 or more, and Game8 has a dedicated page dated 2026-07-02, so the resource to model is named even though its rules are still unextracted |
| Section 3 mood bonus | CORROBORATED. Game8 prints 20 percent training stat increase at Great mood tagged `[Both]`, independently matching the JP guide and `docs/scenarios/01`. The Global mood label list is still unconfirmed |

### Two naming decisions an importer must make

1. **Enhanced or Extreme.** The URA page calls it "Enhanced Spirit Bursts"; the Unity Cup page calls it
   "Extreme Spirit Bursts (ESB)" and is the page for the scenario owning the mechanic. Use **Extreme
   Spirit Burst** and treat the URA wording as stale second-hand reference.
2. **Scope of the 0 percent failure rate.** The URA page says it applies "whenever it appears on any
   training"; the Unity Cup page scopes it to the facility holding the active burst. The owning page's
   narrower reading is the safe one for a failure model, since the wide reading would suppress failure
   on tiles where no burst sits.

### Numbers worth carrying forward

- Skill-point yield by scenario: Unity Cup 2,000 to 2,500 or more against Trackblazer averaging 2,800.
- Reachable stat lines under the raised caps are described as "at most or close to 1300" in the
  Unity-versus-Trackblazer comparison, a waypoint rather than a ceiling.
- `Fully Charged` pays off at Power 1200 or more, printed on both cap discussions.

### 2.6. Corrections to 2.2 and 2.3 from the heuristics extraction

Source: `research-scratch/scrape-training-heuristics.md`, 764 lines, 30 ledger sources, 29 tables,
12 disagreement rows. All pages rendered in a real browser, with tab-hidden panels read from the DOM.

### The 50 percent rest line is not a mechanical threshold

Section 3 lists it as a threshold, and it needs downgrading. The source string 体力5割未満か強い練習が
無い時はお休み is Kamigame's own advice about when to rest, tagged by the extractor as an editorial
heuristic. **No tier-A page publishes a failure probability table by energy level**, so there is no
published inflection at half energy to encode. What the sources do support is direction only: failure
chance rises as energy falls.

A run planner should treat 50 percent as a house rule a Trainer may adopt, not as a game constant.

### What the failure model actually is

| Rule as printed | English mechanical reading |
|---|---|
| 失敗率×失敗率ダウン＋コンディション補正＝失敗率 | Final failure rate equals the base rate multiplied by a failure-rate-down multiplier, plus a condition correction |
| 練習上手◯: トレーニング失敗率が−2%される | Practice Skilled reduces failure rate by 2 percentage points |
| 練習ベタはトレーニング失敗率が2%増えるだけ | Practice Unskilled adds 2 percentage points, small enough that leaving it untreated can be correct |
| 失敗率が高いほど失敗した時のペナルティも大きくなってしまう | Penalty magnitude scales with the failure rate itself, so a risky turn is worse on average than its probability alone suggests |

The multiplier-then-additive-order matters if the app models failure: the two effects do not commute,
and a flat subtraction applied before the multiplier gives a different number than the printed formula.

### Conflict 1 stays open, with one supporting signal

GameWith's per-scenario training tables were recovered for URA, アオハル, クライマックス,
グランドライブ and グランドマスターズ, but they print **per-level training gain deltas, not stat
caps**, so Trackblazer's 1200 / 1900 / 1200 / 1200 / 1500 still has no second source and remains a
derivation at LOW-MEDIUM.

The tables do carry one indirect corroboration of its direction: クライマックス shows a fixed **+5**
in the stamina column at every one of its five facility levels, the only scenario in that set with a
constant stamina bonus per level. That is consistent with a scenario built to push stamina high,
which is what a 1900 stamina cap would imply, without confirming the number.

### New conflict worth logging: the mood race effect

| Publisher | Figure printed |
|---|---|
| GameWith | mood race effect of plus or minus 4 percent, with the second tier at 2 percent |
| Game8 and Kamigame | plus or minus 10 percent, with the second tier at 5 percent |

These cannot both describe the same quantity. Note that `docs/scenarios/01-ura-finale.md` carries
"+20% training stat gains, +4% race performance" at Great mood, which matches the GameWith camp,
while Game8 independently states the 20 percent training figure that all three agree on. The
disagreement is confined to the race-side number, so the training-side 20 percent is safe and the
race-side percentage is not settled. Sample sizes behind the rest-probability figures also differ by
an order of magnitude in the extraction, 3,833 versus 100, which is the right way to weigh a
disagreement of that kind.

### Still unverified after 30 sources

Failure probability by energy level, theアイシング action, 疲労 and ケガ as separate counters, the
numeric ids for failure-rate effects, and a standalone Wit efficiency guide. That is the boundary of
what tier-A publishing supports on this topic, and a fourth source of the same kind is unlikely to
move it.

### 2.7. Item 1 closed: Trackblazer caps confirmed, with a server caveat

Two more sources arrived after section 2.6, both under `docs/scenarios/`:
`05-trackblazer-gametora.md` (210 lines, from the GameTora Trackblazer page, attributions to Gertas,
robflop and Burgh) and `06-unity-cup-gametora.md` (last updated 2026-07-20), plus
`04-trackblazer-umaguide.md` (260 lines, a community strategy take from uma.guide).

### The numbers are confirmed

05 prints a cap table of **Speed 1200, Stamina 1900, Power 1200, Guts 1200, Wisdom 1500**, which is
exactly what `1200 + scenarios.json.stats` predicted. Two independent publishers now agree with the
derivation, so open item 1 closes on the arithmetic and the earlier note that Trackblazer runs
"standard base caps" is refuted. Stamina 1900 and Wit 1500 are real.

### Where it still needs one more look, and why that is not the same doubt

05 labels the higher figures as applying on non-Global servers and says Global is **expected** to
launch Trackblazer at 1200 across the board. That is a hedge about launch state, not a measurement,
and it is dated against two firmer facts established elsewhere in this draft:

1. Trackblazer reached `[Global]` on 2026-03-12, which is **before** the 2026-07-01 Global mechanics
   update.
2. That update is now triple-confirmed, by the Game8 URA page, the Game8 Unity Cup page, and 06's own
   header line stating it is written against Unity Cup **after** its July 1, 2026 Global update, which
   corresponds to JP's 2023-01-20 revision.
3. URA's Global cap is printed as 1400 **after** that rework, and Unity Cup's as 1300 plus 1800 Wit,
   both matching the export. So for those two scenarios the export's `stats` field describes current
   `[Global]` state, not JP-only state.

Read together: 05's "1200 across the board" was very likely correct on 2026-03-12 and was overtaken by
the 2026-07-01 update, the same way Unity Cup was. The export, fetched 2026-09-27, is on the far side
of that update. So the working conclusion is **1900 and 1500 are current on `[Global]`**, held at HIGH for the five figures themselves, now [User-Supplied / Confirmed via Famitsu & GameWith extraction, 2026-09-27], and the narrower claim, that the raised caps are live on [Global] today, stays open. One
look at the live client's scenario description or the patch notes settles it, and this section should
be amended rather than re-argued when someone checks.

### Other content worth carrying

- The Trackblazer scenario factor is printed as **"Climax Scenario"** and grants Stamina and Guts
  bonuses on successful inheritance, which is the mechanic a `[Global]` Legacy planner cares about
  when choosing this scenario for farming. ("bloodline" appeared here until 2026-09-27; it is a
  banned framing term under `docs/design-research/CONSTRAINTS.md` §3.1, and the Global client word
  for this screen is Legacy.)
- 04 supplies a turn-economy framing worth keeping: races dominate the turn budget here, Grade Points
  replace career goals, and epithet routes gate shop and stat rewards, with the Goddess epithet
  requiring the Lady plus Victoria Mile, Hanshin Juvenile Fillies and both Queen Elizabeth II Cups for
  plus 15 to two random stats.
- 04 also records a deck archetype that can fully cap Guts where Speed-plus-Wit decks typically
  cannot, which is a use for the 1200 Guts ceiling rather than a contradiction of it.

### One lore item for the Guardian, in a file the repo grep will not catch

`04-trackblazer-umaguide.md:82` heads a route section **"Tiara Route (Fillies-focused)"**. The `make lore` list under
`CONSTRAINTS.md` C-4 does not include this word, so it passes the grep and still reads as animal
framing of the characters, which the broader rule in `AGENTS.md`
forbids. Two nearby uses differ and should be judged separately: line 87's "Hanshin Juvenile Fillies"
is a verbatim race name, which is source data, whereas line 82 is the document's own label for a route
and is better written as a classic-age or Tiara-track route. Also note that two other apparent hits in
these files, on "stacking" and similar, are the substring inside unrelated words and are not findings.

### Lore rulings and their disposition, 2026-09-27

Four items went to the Guardian during the mechanics pass that produced 1.6.10 and the Section 6 rows. The first three are rulings rather than grep hits, and each is disposed of here instead of only logged. The heading above is retained as the record of what was found; it is the file's single quoted use of the retired descriptor, and it is withdrawn below.

1. **The Tiara-route heading.** **Disposition: fixed.** `04-trackblazer-umaguide.md` now reads **"Tiara Route (the Oka Sho / Japanese Oaks / Shuka Sho line)"** — named by its three races, which is both unambiguous and free of character framing. The race name on the following line stays verbatim as source data, and the route's mechanics are untouched. ⚠️ Gate lesson worth keeping: `make lore` does not carry the plural form and `lore-code` matches only the singular inside app directories, so **no gate in this repository can see a heading like that**; the read, not the grep, is the control. C-4's pattern list is a floor, and this was a hole in it.
2. **「蹄鉄」 is "Cleat" in `[Global]` client text, and the fan equine rendering is not adoptable.** Disposition: 1.6.10 documents the item as the **Cleat currency** with its exchange rates, the Section 6 row carries the ban, and the collision with Trackblazer's **Artisan / Master Cleat Hammer** race items is logged so nobody later "corrects" one into the other. `DESIGN.md` already forbids the object as a visual motif, so this is one rule shared by lore and design rather than two. ⚠️ Note what survives on purpose: the `[Global]` client itself names five distance/surface items **"Racing Shoes"** — client text, kept, and equipment vocabulary rather than character framing.
3. **The `[JP-Only]` Grand Masters goddesses.** Out of game these are the historical foundation lines of a racing breed; **in game the premise is a Satono Group VR product and three support AIs carrying goddess names** (「三女神」 is Cygames' own wording). Disposition: `docs/scenarios/08-grand-masters-jp-only.md` states the premise in those terms and names the wiki's English reward title "Trail of Hooves" and any breeding framing as **do-not-import** rather than translating them. The scenario has no Global release, so nothing here can reach Global-facing copy today; the live risk is the import path in conflict 6.

4. **One case that IS a finding and is deliberately not deleted.** Section 3 row 105, Air Messiah, carries the unique skill 「辿る血脈、芽吹く未来」 with the English title **"Traced Bloodline, Budding Future"** — a verbatim skill name holding a term from `CONSTRAINTS.md` D-20's banned list, invisible to C-4's grep. Ruling: **keep as source data, gate on export.** The row is `[JP-Only]` ("unit unreleased on `[Global]`"), and per the export-label warning in §1.6.1 an English string on a unit with no `start_en` is a third-party label rather than `[Global]` client text — so as a display string it fails P6 as well as C-4. The name stays in the roster table because deleting a skill from a catalog row would be data loss rather than a lore fix; what is barred is **promoting this rendering into UI copy**, which no Global-facing surface can require while the unit is unreleased there. If the unit ships on `[Global]`, substitute the client's own title and retire this note. ⚠️ Escalated to the Lore Guardian as a naming ruling, not resolved unilaterally — and the general lesson is that **verbatim names are a third category** between "our copy" and "a stray noun": the grep can neither clear them nor be trusted to catch them.

Two substring classes were checked and are **not** findings, recorded so a future gate run does not re-open them: the `[Global]` card bracket **`[Fille Éclair]`** in Section 3 is a French word inside a proper card name, not the equine noun; and the short-word members of C-4's list appear in `docs/` only **inside longer words** — the equine-noun lookalike family already inventoried in §8.8, plus the compound and adjective senses — where C-4's context rule clears them. This paragraph therefore adds no new hit for `make lore` to report, which matters because §8.8's audit line states a specific hit count. `docs/PRE-MORTEM.md` stays exempt as elimination evidence. Ordinary verb senses of "pairing" — pairing a glyph with a type value in §1.4.7, pairing a letter with a column in §3.4 — are likewise not animal framing.

## Section 3: Character Roster

Counts read from the GameTora data export on 2026-09-27 (`characters.json`, `character-cards.json`).

| Measure | Value |
|---|---|
| Distinct trainable Umamusume, any server | 135 |
| Released on `[Both]` | 68 |
| `[JP-Only]` | 67 |
| Trainable cards total (debut forms and alternate costumes) | 268 |
| Cards with a `[Global]` release date | 105 |
| Cards with a `[JP]` release date | 268 |
| Units with 1 / 2 / 3 cards | 33 / 71 / 31 |

Server status is asserted per card, not per unit. 61 alternate-costume cards belong to a unit that is
already live on `[Global]` while the card itself has never shipped there, so those rows read `[JP-Only]` next to an
English name that is official for the unit. 0 debut rows disagree between the unit flag and the card date.

| card_id | JP name | char_id |
|---|---|---|
| 100802 | ウオッカ | 1008 |
| 100902 | ダイワスカーレット | 1009 |
| 101602 | ナリタブライアン | 1016 |
| 106702 | サトノダイヤモンド | 1067 |
| 106802 | キタサンブラック | 1068 |
| 102702 | メジロライアン | 1027 |
| 103102 | アイネスフウジン | 1031 |
| 106902 | サクラチヨノオー | 1069 |
| 107102 | メジロアルダン | 1071 |
| 104102 | サクラバクシンオー | 1041 |
| 106202 | マチカネタンホイザ | 1062 |
| 101202 | ヒシアマゾン | 1012 |
| 105102 | ニシノフラワー | 1051 |
| 104802 | トーセンジョーダン | 1048 |
| 105302 | バンブーメモリー | 1053 |
| 100202 | サイレンススズカ | 1002 |
| 103202 | アグネスタキオン | 1032 |
| 100703 | ゴールドシップ | 1007 |
| 106703 | サトノダイヤモンド | 1067 |
| 103602 | エアシャカール | 1036 |
| 100303 | トウカイテイオー | 1003 |
| 103902 | カワカミプリンセス | 1039 |
| 106402 | メジロパーマー | 1064 |
| 107402 | メジロブライト | 1074 |
| 106803 | キタサンブラック | 1068 |
| 106003 | ナイスネイチャ | 1060 |
| 102502 | マンハッタンカフェ | 1025 |
| 102902 | ユキノビジン | 1029 |
| 103503 | ウイニングチケット | 1035 |
| 104503 | スーパークリーク | 1045 |
| 107202 | ヤエノムテキ | 1072 |
| 103203 | アグネスタキオン | 1032 |
| 104402 | スイープトウショウ | 1044 |
| 106103 | キングヘイロー | 1061 |
| 103003 | ライスシャワー | 1030 |
| 103703 | エイシンフラッシュ | 1037 |
| 102403 | マヤノトップガン | 1024 |
| 104202 | シーキングザパール | 1042 |
| 102303 | ビワハヤヒデ | 1023 |
| 105003 | ナリタタイシン | 1050 |
| 103302 | アドマイヤベガ | 1033 |
| 101103 | グラスワンダー | 1011 |
| 100403 | マルゼンスキー | 1004 |
| 107802 | ヤマニンゼファー | 1078 |
| 108702 | アストンマーチャン | 1087 |
| 104603 | スマートファルコン | 1046 |
| 109802 | コパノリッキー | 1098 |
| 104003 | ゴールドシチー | 1040 |
| 103103 | アイネスフウジン | 1031 |
| 102802 | ヒシアケボノ | 1028 |
| 104803 | トーセンジョーダン | 1048 |
| 110002 | ワンダーアキュート | 1100 |
| 104902 | ナカヤマフェスタ | 1049 |
| 100603 | オグリキャップ | 1006 |
| 101003 | タイキシャトル | 1010 |
| 104103 | サクラバクシンオー | 1041 |
| 100503 | フジキセキ | 1005 |
| 102503 | マンハッタンカフェ | 1025 |
| 102203 | ファインモーション | 1022 |
| 102003 | セイウンスカイ | 1020 |
| 107403 | メジロブライト | 1074 |

### 3.1 Playable Umamusume, debut form

Sorted by `[JP]` debut date, then by database id. Aptitude letters follow the export's element order,
confirmed cell by cell against two independent sources for three diagnostic units (see the cross-check note in 3.3).
Strategy labels are the client strings from the export (`factors.json` pink ids 21 to 24, where `name_en` equals
`name_en_gl`, corroborated by Global nickname text such as "Win 6 races as a Front Runner"). A shorter set,
`Runner` / `Leader` / `Betweener` / `Chaser`, circulates in community guides and in the brief that commissioned this
table; it appears nowhere in the client text, so 1.2.2 and the Source Conflict Log rule against it. See 1.7.

| # | JP Name | Romanized Name | Global EN Name | Server Status | Aptitude (Track) | Aptitude (Distance) | Aptitude (Strategy) | Unique Skill Name | Notable Traits | Source |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | スペシャルウィーク | Special Week | Special Week | [Both] | Turf A / Dirt G | Sprint F / Mile C / Medium A / Long A | Front Runner G / Pace Chaser A / Late Surger A / End Closer C | Shooting Star (シューティングスター) | SSR · [Special Dreamer] · debut [JP] 2021-02-24 · debut [Global] 2025-06-26 · `stat_bonus` Stamina+20, Wit+10 (meaning unconfirmed, see 1.3.5) · 2 alternate costume cards · ★5 ceiling 120 | [GameTora card 100101](https://gametora.com/umamusume/characters/100101-special-week) |
| 2 | サイレンススズカ | Silence Suzuka | Silence Suzuka | [Both] | Turf A / Dirt G | Sprint D / Mile A / Medium A / Long E | Front Runner A / Pace Chaser C / Late Surger E / End Closer G | The View from the Lead Is Mine! (先頭の景色は譲らない…！) | SSR · [Innocent Silence] · debut [JP] 2021-02-24 · debut [Global] 2025-06-26 · `stat_bonus` Speed+20, Guts+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 124 | [GameTora card 100201](https://gametora.com/umamusume/characters/100201-silence-suzuka) |
| 3 | トウカイテイオー | Tokai Teio | Tokai Teio | [Both] | Turf A / Dirt G | Sprint F / Mile E / Medium A / Long B | Front Runner D / Pace Chaser A / Late Surger C / End Closer E | Sky-High Teio Step (究極テイオーステップ) | SSR · [Peak Joy] · debut [JP] 2021-02-24 · debut [Global] 2025-06-26 · `stat_bonus` Speed+20, Stamina+10 (meaning unconfirmed, see 1.3.5) · 2 alternate costume cards · ★5 ceiling 118 | [GameTora card 100301](https://gametora.com/umamusume/characters/100301-tokai-teio) |
| 4 | マルゼンスキー | Maruzensky | Maruzensky | [Both] | Turf A / Dirt D | Sprint B / Mile A / Medium B / Long C | Front Runner A / Pace Chaser E / Late Surger G / End Closer G | Red Shift/LP1211-M (紅焔ギア/LP1211-M) | SSR · [Formula R] · debut [JP] 2021-02-24 · debut [Global] 2025-06-26 · `stat_bonus` Speed+10, Wit+20 (meaning unconfirmed, see 1.3.5) · 2 alternate costume cards · ★5 ceiling 122 | [GameTora card 100401](https://gametora.com/umamusume/characters/100401-maruzensky) |
| 5 | オグリキャップ | Oguri Cap | Oguri Cap | [Both] | Turf A / Dirt B | Sprint E / Mile A / Medium A / Long B | Front Runner F / Pace Chaser A / Late Surger A / End Closer D | Triumphant Pulse (勝利の鼓動) | SSR · [Starlight Beat] · debut [JP] 2021-02-24 · debut [Global] 2025-06-26 · `stat_bonus` Speed+20, Power+10 (meaning unconfirmed, see 1.3.5) · 2 alternate costume cards · ★5 ceiling 130 | [GameTora card 100601](https://gametora.com/umamusume/characters/100601-oguri-cap) |
| 6 | ゴールドシップ | Gold Ship | Gold Ship | [Both] | Turf A / Dirt G | Sprint G / Mile C / Medium A / Long A | Front Runner G / Pace Chaser B / Late Surger B / End Closer A | Warning Shot! (波乱注意砲！); Anchors Aweigh! (不沈艦、抜錨ォッ！) | SR · [Red Strife] · debut [JP] 2021-02-24 · debut [Global] 2025-06-26 · `stat_bonus` Stamina+20, Power+10 (meaning unconfirmed, see 1.3.5) · 2 alternate costume cards · ★5 ceiling 129 | [GameTora card 100701](https://gametora.com/umamusume/characters/100701-gold-ship) |
| 7 | ウオッカ | Vodka | Vodka | [Both] | Turf A / Dirt G | Sprint F / Mile A / Medium A / Long F | Front Runner C / Pace Chaser B / Late Surger A / End Closer F | Xceleration (アクセルX); Cut and Drive! (カッティング×DRIVE！) | SR · [Wild Top Gear] · debut [JP] 2021-02-24 · debut [Global] 2025-06-26 · `stat_bonus` Speed+10, Power+20 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 136 | [GameTora card 100801](https://gametora.com/umamusume/characters/100801-vodka) |
| 8 | ダイワスカーレット | Daiwa Scarlet | Daiwa Scarlet | [Both] | Turf A / Dirt G | Sprint F / Mile A / Medium A / Long B | Front Runner A / Pace Chaser A / Late Surger E / End Closer G | Red Ace (レッドエース); Resplendent Red Ace (ブリリアント・レッドエース) | SR · [Peak Blue] · debut [JP] 2021-02-24 · debut [Global] 2025-06-26 · `stat_bonus` Speed+10, Guts+20 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 122 | [GameTora card 100901](https://gametora.com/umamusume/characters/100901-daiwa-scarlet) |
| 9 | タイキシャトル | Taiki Shuttle | Taiki Shuttle | [Both] | Turf A / Dirt B | Sprint A / Mile A / Medium E / Long G | Front Runner C / Pace Chaser A / Late Surger E / End Closer G | Shooting for Victory! (ヴィクトリーショット！) | SSR · [Wild Frontier] · debut [JP] 2021-02-24 · debut [Global] 2025-06-26 · `stat_bonus` Speed+20, Wit+10 (meaning unconfirmed, see 1.3.5) · 2 alternate costume cards · ★5 ceiling 119 | [GameTora card 101001](https://gametora.com/umamusume/characters/101001-taiki-shuttle) |
| 10 | グラスワンダー | Grass Wonder | Grass Wonder | [Both] | Turf A / Dirt G | Sprint G / Mile A / Medium B / Long A | Front Runner F / Pace Chaser A / Late Surger A / End Closer F | Focused Mind (精神一到); Where There's a Will, There's a Way (精神一到何事か成らざらん) | SR · [Stone-Piercing Blue] · debut [JP] 2021-02-24 · debut [Global] 2025-06-26 · `stat_bonus` Speed+20, Power+10 (meaning unconfirmed, see 1.3.5) · 2 alternate costume cards · ★5 ceiling 129 | [GameTora card 101101](https://gametora.com/umamusume/characters/101101-grass-wonder) |
| 11 | メジロマックイーン | Mejiro McQueen | Mejiro McQueen | [Both] | Turf A / Dirt E | Sprint G / Mile F / Medium A / Long A | Front Runner B / Pace Chaser A / Late Surger D / End Closer F | The Duty of Dignity Calls (貴顕の使命を果たすべく) | SSR · [Frontline Elegance] · debut [JP] 2021-02-24 · debut [Global] 2025-06-26 · `stat_bonus` Stamina+20, Wit+10 (meaning unconfirmed, see 1.3.5) · 2 alternate costume cards · ★5 ceiling 136 | [GameTora card 101301](https://gametora.com/umamusume/characters/101301-mejiro-mcqueen) |
| 12 | エルコンドルパサー | El Condor Pasa | El Condor Pasa | [Both] | Turf A / Dirt B | Sprint F / Mile A / Medium A / Long B | Front Runner E / Pace Chaser A / Late Surger A / End Closer C | Corazón ☆ Ardiente (熱血☆アミーゴ); Victoria por plancha ☆ (プランチャ☆ガナドール) | SR · [El☆Número 1] · debut [JP] 2021-02-24 · debut [Global] 2025-06-26 · `stat_bonus` Speed+20, Wit+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 121 | [GameTora card 101401](https://gametora.com/umamusume/characters/101401-el-condor-pasa) |
| 13 | シンボリルドルフ | Symboli Rudolf | Symboli Rudolf | [Both] | Turf A / Dirt G | Sprint E / Mile C / Medium A / Long A | Front Runner B / Pace Chaser A / Late Surger A / End Closer C | Behold Thine Emperor's Divine Might (汝、皇帝の神威を見よ) | SSR · [Emperor's Path] · debut [JP] 2021-02-24 · debut [Global] 2025-06-26 · `stat_bonus` Stamina+20, Guts+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 118 | [GameTora card 101701](https://gametora.com/umamusume/characters/101701-symboli-rudolf) |
| 14 | エアグルーヴ | Air Groove | Air Groove | [Both] | Turf A / Dirt G | Sprint C / Mile B / Medium A / Long E | Front Runner D / Pace Chaser A / Late Surger A / End Closer G | Empress's Pride (エンプレス・プライド); Blazing Pride (ブレイズ・オブ・プライド) | SR · [Empress Road] · debut [JP] 2021-02-24 · debut [Global] 2025-06-26 · `stat_bonus` Speed+10, Power+20 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 117 | [GameTora card 101801](https://gametora.com/umamusume/characters/101801-air-groove) |
| 15 | マヤノトップガン | Mayano Top Gun | Mayano Top Gun | [Both] | Turf A / Dirt E | Sprint D / Mile D / Medium A / Long A | Front Runner A / Pace Chaser A / Late Surger B / End Closer B | 1st Place Kiss☆ (勝利のキッス☆); Flashy☆Landing (ひらめき☆ランディング) | SR · [Scramble☆Zone] · debut [JP] 2021-02-24 · debut [Global] 2025-06-26 · `stat_bonus` Stamina+20, Guts+10 (meaning unconfirmed, see 1.3.5) · 2 alternate costume cards · ★5 ceiling 130 | [GameTora card 102401](https://gametora.com/umamusume/characters/102401-mayano-top-gun) |
| 16 | メジロライアン | Mejiro Ryan | Mejiro Ryan | [Both] | Turf A / Dirt G | Sprint E / Mile C / Medium A / Long B | Front Runner F / Pace Chaser A / Late Surger A / End Closer F | Feel the Burn! (燃えろ筋肉！); Let's Pump Some Iron! (レッツ・アナボリック！) | R · [Down the Line] · debut [JP] 2021-02-24 · debut [Global] 2025-06-26 · `stat_bonus` Power+20, Wit+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 130 | [GameTora card 102701](https://gametora.com/umamusume/characters/102701-mejiro-ryan) |
| 17 | ライスシャワー | Rice Shower | Rice Shower | [Both] | Turf A / Dirt G | Sprint E / Mile C / Medium A / Long A | Front Runner B / Pace Chaser A / Late Surger C / End Closer G | Blue Rose Closer (ブルーローズチェイサー) | SSR · [Rosy Dreams] · debut [JP] 2021-02-24 · debut [Global] 2025-06-26 · `stat_bonus` Stamina+10, Guts+20 (meaning unconfirmed, see 1.3.5) · 2 alternate costume cards · ★5 ceiling 143 | [GameTora card 103001](https://gametora.com/umamusume/characters/103001-rice-shower) |
| 18 | アグネスタキオン | Agnes Tachyon | Agnes Tachyon | [Both] | Turf A / Dirt G | Sprint G / Mile D / Medium A / Long B | Front Runner E / Pace Chaser A / Late Surger B / End Closer F | Introduction to Physiology (introduction：My body); U=ma2 (U=ma2) | R · [tach-nology] · debut [JP] 2021-02-24 · debut [Global] 2025-06-26 · `stat_bonus` Speed+20, Guts+10 (meaning unconfirmed, see 1.3.5) · 2 alternate costume cards · ★5 ceiling 122 | [GameTora card 103201](https://gametora.com/umamusume/characters/103201-agnes-tachyon) |
| 19 | ウイニングチケット | Winning Ticket | Winning Ticket | [Both] | Turf A / Dirt G | Sprint G / Mile F / Medium A / Long B | Front Runner G / Pace Chaser B / Late Surger A / End Closer G | V Is for Victory! (全力Vサインッ！); Our Ticket to Win! (勝利のチケットを、君にッ！) | R · [Get to Winning!] · debut [JP] 2021-02-24 · debut [Global] 2025-06-26 · `stat_bonus` Stamina+10, Power+20 (meaning unconfirmed, see 1.3.5) · 2 alternate costume cards · ★5 ceiling 125 | [GameTora card 103501](https://gametora.com/umamusume/characters/103501-winning-ticket) |
| 20 | サクラバクシンオー | Sakura Bakushin O | Sakura Bakushin O | [Both] | Turf A / Dirt G | Sprint A / Mile B / Medium G / Long G | Front Runner A / Pace Chaser A / Late Surger F / End Closer G | Class Rep + Speed = Bakushin (学級委員長+速さ＝バクシン); Genius x Bakushin = Victory (優等生×バクシン＝大勝利ッ) | R · [Blossom in Learning] · debut [JP] 2021-02-24 · debut [Global] 2025-06-26 · `stat_bonus` Speed+20, Wit+10 (meaning unconfirmed, see 1.3.5) · 2 alternate costume cards · ★5 ceiling 128 | [GameTora card 104101](https://gametora.com/umamusume/characters/104101-sakura-bakushin-o) |
| 21 | スーパークリーク | Super Creek | Super Creek | [Both] | Turf A / Dirt G | Sprint G / Mile G / Medium A / Long A | Front Runner D / Pace Chaser A / Late Surger B / End Closer G | Clear Heart (クリアハート); Pure Heart (ピュリティオブハート) | SR · [Murmuring Stream] · debut [JP] 2021-02-24 · debut [Global] 2025-06-26 · `stat_bonus` Stamina+10, Wit+20 (meaning unconfirmed, see 1.3.5) · 2 alternate costume cards · ★5 ceiling 129 | [GameTora card 104501](https://gametora.com/umamusume/characters/104501-super-creek) |
| 22 | ハルウララ | Haru Urara | Haru Urara | [Both] | Turf G / Dirt A | Sprint A / Mile B / Medium G / Long G | Front Runner G / Pace Chaser G / Late Surger A / End Closer B | Super-Duper Stoked (ワクワクよーいドン); Super-Duper Climax (ワクワククライマックス) | R · [Bestest Prize ♪] · debut [JP] 2021-02-24 · debut [Global] 2025-06-26 · `stat_bonus` Power+10, Guts+20 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 123 | [GameTora card 105201](https://gametora.com/umamusume/characters/105201-haru-urara) |
| 23 | マチカネフクキタル | Matikanefukukitaru | Matikanefukukitaru | [Both] | Turf A / Dirt F | Sprint F / Mile C / Medium A / Long A | Front Runner G / Pace Chaser B / Late Surger A / End Closer F | Luck Be with Me! (来てください来てください！); I See Victory in My Future! (来ます来てます来させます！) | R · [Rising☆Fortune] · debut [JP] 2021-02-24 · debut [Global] 2025-06-26 · `stat_bonus` Stamina+20, Power+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 121 | [GameTora card 105601](https://gametora.com/umamusume/characters/105601-matikanefukukitaru) |
| 24 | ナイスネイチャ | Nice Nature | Nice Nature | [Both] | Turf A / Dirt G | Sprint G / Mile C / Medium A / Long A | Front Runner F / Pace Chaser B / Late Surger A / End Closer D | I Can Win Sometimes, Right? (アタシもたまには、ね？); Just a Little Farther! (きっとその先へ…！) | R · [Poinsettia Ribbon] · debut [JP] 2021-02-24 · debut [Global] 2025-06-26 · `stat_bonus` Power+20, Wit+10 (meaning unconfirmed, see 1.3.5) · 2 alternate costume cards · ★5 ceiling 123 | [GameTora card 106001](https://gametora.com/umamusume/characters/106001-nice-nature) |
| 25 | キングヘイロー | King Halo | King Halo | [Both] | Turf A / Dirt G | Sprint A / Mile B / Medium B / Long C | Front Runner G / Pace Chaser B / Late Surger A / End Closer D | Call Me King (Call me KING); Prideful King (Pride of KING) | R · [King of Emeralds] · debut [JP] 2021-02-24 · debut [Global] 2025-06-26 · `stat_bonus` Power+20, Guts+10 (meaning unconfirmed, see 1.3.5) · 2 alternate costume cards · ★5 ceiling 127 | [GameTora card 106101](https://gametora.com/umamusume/characters/106101-king-halo) |
| 26 | テイエムオペラオー | TM Opera O | TM Opera O | [Both] | Turf A / Dirt E | Sprint G / Mile E / Medium A / Long A | Front Runner C / Pace Chaser A / Late Surger A / End Closer G | This Dance Is for Vittoria! (ヴィットーリアに捧ぐ舞踏) | SSR · [O Sole Suo!] · debut [JP] 2021-03-02 · debut [Global] 2025-06-26 · `stat_bonus` Stamina+20, Wit+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 132 | [GameTora card 101501](https://gametora.com/umamusume/characters/101501-tm-opera-o) |
| 27 | ミホノブルボン | Mihono Bourbon | Mihono Bourbon | [Both] | Turf A / Dirt G | Sprint C / Mile B / Medium A / Long B | Front Runner A / Pace Chaser E / Late Surger G / End Closer G | G00 1st. F∞; (G00 1st.F∞;) | SSR · [MB-19890425] · debut [JP] 2021-03-09 · debut [Global] 2025-07-02 · `stat_bonus` Stamina+20, Power+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 124 | [GameTora card 102601](https://gametora.com/umamusume/characters/102601-mihono-bourbon) |
| 28 | ビワハヤヒデ | Biwa Hayahide | Biwa Hayahide | [Both] | Turf A / Dirt F | Sprint F / Mile C / Medium A / Long A | Front Runner E / Pace Chaser A / Late Surger B / End Closer E | ∴win Q.E.D. (∴win Q.E.D.) | SSR · [pf. Winning Equation...] · debut [JP] 2021-03-18 · debut [Global] 2025-07-10 · `stat_bonus` Guts+10, Wit+20 (meaning unconfirmed, see 1.3.5) · 2 alternate costume cards · ★5 ceiling 117 | [GameTora card 102301](https://gametora.com/umamusume/characters/102301-biwa-hayahide) |
| 29 | カレンチャン | Curren Chan | Curren Chan | [Both] | Turf A / Dirt F | Sprint A / Mile D / Medium G / Long G | Front Runner B / Pace Chaser A / Late Surger E / End Closer G | #LookatCurren (#LookatCurren) | SSR · [Fille Éclair] · debut [JP] 2021-04-15 · debut [Global] 2025-07-27 · `stat_bonus` Speed+10, Power+20 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 130 | [GameTora card 103801](https://gametora.com/umamusume/characters/103801-curren-chan) |
| 30 | ナリタタイシン | Narita Taishin | Narita Taishin | [Both] | Turf A / Dirt G | Sprint F / Mile D / Medium A / Long A | Front Runner G / Pace Chaser F / Late Surger B / End Closer A | Nemesis (Nemesis) | SSR · [Nevertheless] · debut [JP] 2021-04-26 · debut [Global] 2025-08-03 · `stat_bonus` Speed+10, Guts+20 (meaning unconfirmed, see 1.3.5) · 2 alternate costume cards · ★5 ceiling 121 | [GameTora card 105001](https://gametora.com/umamusume/characters/105001-narita-taishin) |
| 31 | スマートファルコン | Smart Falcon | Smart Falcon | [Both] | Turf E / Dirt A | Sprint B / Mile A / Medium A / Long E | Front Runner A / Pace Chaser D / Late Surger G / End Closer G | SPARKLY☆STARDOM (キラキラ☆STARDOM) | SSR · [LOVE☆4EVER] · debut [JP] 2021-05-06 · debut [Global] 2025-08-11 · `stat_bonus` Speed+20, Power+10 (meaning unconfirmed, see 1.3.5) · 2 alternate costume cards · ★5 ceiling 122 | [GameTora card 104601](https://gametora.com/umamusume/characters/104601-smart-falcon) |
| 32 | ナリタブライアン | Narita Brian | Narita Brian | [Both] | Turf A / Dirt G | Sprint F / Mile B / Medium A / Long A | Front Runner G / Pace Chaser A / Late Surger A / End Closer D | Shadow Break (Shadow Break) | SSR · [Maverick] · debut [JP] 2021-05-17 · debut [Global] 2025-08-20 · `stat_bonus` Speed+10, Stamina+20 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 122 | [GameTora card 101601](https://gametora.com/umamusume/characters/101601-narita-brian) |
| 33 | セイウンスカイ | Seiun Sky | Seiun Sky | [Both] | Turf A / Dirt G | Sprint G / Mile C / Medium A / Long A | Front Runner A / Pace Chaser B / Late Surger D / End Closer E | Angling and Scheming (アングリング×スキーミング) | SSR · [Reeling in the Big One] · debut [JP] 2021-06-10 · debut [Global] 2025-09-07 · `stat_bonus` Stamina+10, Wit+20 (meaning unconfirmed, see 1.3.5) · 2 alternate costume cards · ★5 ceiling 120 | [GameTora card 102001](https://gametora.com/umamusume/characters/102001-seiun-sky) |
| 34 | ヒシアマゾン | Hishi Amazon | Hishi Amazon | [Both] | Turf A / Dirt E | Sprint D / Mile A / Medium A / Long B | Front Runner G / Pace Chaser B / Late Surger C / End Closer A | You and Me! One-on-One! (タイマン！デッドヒート！) | SSR · [Azure Amazon] · debut [JP] 2021-06-21 · debut [Global] 2025-09-17 · `stat_bonus` Power+20, Guts+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 129 | [GameTora card 101201](https://gametora.com/umamusume/characters/101201-hishi-amazon) |
| 35 | フジキセキ | Fuji Kiseki | Fuji Kiseki | [Both] | Turf A / Dirt F | Sprint B / Mile A / Medium B / Long E | Front Runner C / Pace Chaser A / Late Surger C / End Closer G | Lights of Vaudeville (煌星のヴォードヴィル) | SSR · [Shooting Star Revue] · debut [JP] 2021-07-12 · debut [Global] 2025-10-02 · `stat_bonus` Power+20, Wit+10 (meaning unconfirmed, see 1.3.5) · 2 alternate costume cards · ★5 ceiling 118 | [GameTora card 100501](https://gametora.com/umamusume/characters/100501-fuji-kiseki) |
| 36 | ゴールドシチー | Gold City | Gold City | [Both] | Turf A / Dirt D | Sprint F / Mile A / Medium B / Long B | Front Runner F / Pace Chaser A / Late Surger A / End Closer F | KEEP IT REAL. (KEEP IT REAL.) | SSR · [Authentic / 1928] · debut [JP] 2021-07-20 · debut [Global] 2025-10-07 · `stat_bonus` Power+10, Guts+20 (meaning unconfirmed, see 1.3.5) · 2 alternate costume cards · ★5 ceiling 114 | [GameTora card 104001](https://gametora.com/umamusume/characters/104001-gold-city) |
| 37 | メイショウドトウ | Meisho Doto | Meisho Doto | [Both] | Turf A / Dirt E | Sprint G / Mile F / Medium A / Long A | Front Runner F / Pace Chaser A / Late Surger B / End Closer E | I Never Goof Up! (I Never Goof Up!) | SSR · [Turbulent Blue] · debut [JP] 2021-08-11 · debut [Global] 2025-10-21 · `stat_bonus` Stamina+20, Guts+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 118 | [GameTora card 105801](https://gametora.com/umamusume/characters/105801-meisho-doto) |
| 38 | エイシンフラッシュ | Eishin Flash | Eishin Flash | [Both] | Turf A / Dirt G | Sprint G / Mile F / Medium A / Long A | Front Runner G / Pace Chaser B / Late Surger A / End Closer C | Schwarzes Schwert (Schwarzes Schwert) | SSR · [Meisterschaft] · debut [JP] 2021-08-20 · debut [Global] 2025-10-30 · `stat_bonus` Power+10, Wit+20 (meaning unconfirmed, see 1.3.5) · 2 alternate costume cards · ★5 ceiling 122 | [GameTora card 103701](https://gametora.com/umamusume/characters/103701-eishin-flash) |
| 39 | ヒシアケボノ | Hishi Akebono | Hishi Akebono | [Both] | Turf A / Dirt F | Sprint A / Mile B / Medium F / Long G | Front Runner B / Pace Chaser A / Late Surger C / End Closer G | YUMMY☆SPEED! (I'M☆FULL☆SPEED!!) | SSR · [Buono ☆ Alla Moda] · debut [JP] 2021-09-10 · debut [Global] 2025-11-11 · `stat_bonus` Power+20, Guts+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 132 | [GameTora card 102801](https://gametora.com/umamusume/characters/102801-hishi-akebono) |
| 40 | アグネスデジタル | Agnes Digital | Agnes Digital | [Both] | Turf A / Dirt A | Sprint F / Mile A / Medium A / Long G | Front Runner G / Pace Chaser A / Late Surger A / End Closer B | OMG! (ﾟ∀ﾟ)  The Final Sprint! ☆ (尊み☆ﾗｽﾄｽﾊﾟ—(ﾟ∀ﾟ)—ﾄ!) | SSR · [Full-Color Fangirling] · debut [JP] 2021-09-20 · debut [Global] 2025-11-19 · `stat_bonus` Speed+8, Stamina+8, Power+7, Wit+7 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 122 | [GameTora card 101901](https://gametora.com/umamusume/characters/101901-agnes-digital) |
| 41 | カワカミプリンセス | Kawakami Princess | Kawakami Princess | [Both] | Turf A / Dirt G | Sprint D / Mile B / Medium A / Long F | Front Runner G / Pace Chaser C / Late Surger A / End Closer D | A Princess Must Seize Victory! (姫たるもの、勝利をこの手に) | SSR · [Princess of Pink] · debut [JP] 2021-10-11 · debut [Global] 2025-12-01 · `stat_bonus` Power+10, Guts+20 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 131 | [GameTora card 103901](https://gametora.com/umamusume/characters/103901-kawakami-princess) |
| 42 | マンハッタンカフェ | Manhattan Cafe | Manhattan Cafe | [Both] | Turf A / Dirt G | Sprint G / Mile F / Medium B / Long A | Front Runner G / Pace Chaser C / Late Surger A / End Closer C | Chasing After You (アナタヲ・オイカケテ) | SSR · [Creeping Shadow] · debut [JP] 2021-10-20 · debut [Global] 2025-12-08 · `stat_bonus` Stamina+30 (meaning unconfirmed, see 1.3.5) · 2 alternate costume cards · ★5 ceiling 120 | [GameTora card 102501](https://gametora.com/umamusume/characters/102501-manhattan-cafe) |
| 43 | トーセンジョーダン | Tosen Jordan | Tosen Jordan | [Both] | Turf A / Dirt G | Sprint G / Mile F / Medium A / Long B | Front Runner C / Pace Chaser A / Late Surger B / End Closer G | Pop & Polish (YEAH☆VIVID TIME!) | SSR · [Jokester ☆ Vibes] · debut [JP] 2021-11-08 · debut [Global] 2025-12-18 · `stat_bonus` Speed+10, Power+10, Guts+10 (meaning unconfirmed, see 1.3.5) · 2 alternate costume cards · ★5 ceiling 123 | [GameTora card 104801](https://gametora.com/umamusume/characters/104801-tosen-jordan) |
| 44 | メジロドーベル | Mejiro Dober | Mejiro Dober | [Both] | Turf A / Dirt G | Sprint E / Mile A / Medium A / Long F | Front Runner C / Pace Chaser B / Late Surger A / End Closer G | Moving Past, and Beyond (彼方、その先へ…) | SSR · [Off the Line] · debut [JP] 2021-11-19 · debut [Global] 2025-12-28 · `stat_bonus` Speed+10, Wit+20 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 119 | [GameTora card 105901](https://gametora.com/umamusume/characters/105901-mejiro-dober) |
| 45 | ファインモーション | Fine Motion | Fine Motion | [Both] | Turf A / Dirt G | Sprint F / Mile A / Medium A / Long C | Front Runner D / Pace Chaser A / Late Surger E / End Closer C | Fairy Tale (Fairy tale) | SSR · [Noble Seamair] · debut [JP] 2021-12-14 · debut [Global] 2026-01-15 · `stat_bonus` Power+15, Wit+15 (meaning unconfirmed, see 1.3.5) · 2 alternate costume cards · ★5 ceiling 117 | [GameTora card 102201](https://gametora.com/umamusume/characters/102201-fine-motion) |
| 46 | タマモクロス | Tamamo Cross | Tamamo Cross | [Both] | Turf A / Dirt F | Sprint G / Mile E / Medium A / Long A | Front Runner G / Pace Chaser A / Late Surger A / End Closer A | White Lightning Comin' Through! (白い稲妻、見せたるで！) | SSR · [Fast as Lightning] · debut [JP] 2021-12-22 · debut [Global] 2026-01-22 · `stat_bonus` Stamina+20, Power+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 129 | [GameTora card 102101](https://gametora.com/umamusume/characters/102101-tamamo-cross) |
| 47 | サクラチヨノオー | Sakura Chiyono O | Sakura Chiyono O | [Both] | Turf A / Dirt G | Sprint E / Mile A / Medium A / Long E | Front Runner B / Pace Chaser A / Late Surger F / End Closer G | Ambition to Surpass the Sakura (憧れは桜を越える！) | SSR · [Strength in Full Bloom] · debut [JP] 2022-01-20 · debut [Global] 2026-02-11 · `stat_bonus` Speed+10, Guts+10, Wit+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 117 | [GameTora card 106901](https://gametora.com/umamusume/characters/106901-sakura-chiyono-o) |
| 48 | メジロアルダン | Mejiro Ardan | Mejiro Ardan | [Both] | Turf A / Dirt F | Sprint E / Mile B / Medium A / Long D | Front Runner C / Pace Chaser A / Late Surger D / End Closer G | A Lifelong Dream, A Moment's Flight (一期の夢、刹那の飛翔) | SSR · [Crystalline] · debut [JP] 2022-02-08 · debut [Global] 2026-02-25 · `stat_bonus` Speed+10, Wit+20 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 121 | [GameTora card 107101](https://gametora.com/umamusume/characters/107101-mejiro-ardan) |
| 49 | アドマイヤベガ | Admire Vega | Admire Vega | [Both] | Turf A / Dirt G | Sprint F / Mile C / Medium A / Long C | Front Runner G / Pace Chaser G / Late Surger B / End Closer A | Shooting Star of Dioskouroi (ディオスクロイの流星) | SSR · [Starry Nocturne] · debut [JP] 2022-02-16 · debut [Global] 2026-03-05 · `stat_bonus` Speed+10, Power+20 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 132 | [GameTora card 103301](https://gametora.com/umamusume/characters/103301-admire-vega) |
| 50 | マチカネタンホイザ | Matikanetannhauser | Matikanetannhauser | [Both] | Turf A / Dirt G | Sprint G / Mile D / Medium A / Long A | Front Runner F / Pace Chaser A / Late Surger A / End Closer E | Ready, Go! (レディー、どんっ！); Go, Go, Mun! (どんっ、パッ、むんっ) | SR · [Clippety-Tippety-Clop] · debut [JP] 2022-02-24 · debut [Global] 2026-03-12 · `stat_bonus` Stamina+20, Guts+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 117 | [GameTora card 106201](https://gametora.com/umamusume/characters/106201-matikanetannhauser) |
| 51 | キタサンブラック | Kitasan Black | Kitasan Black | [Both] | Turf A / Dirt G | Sprint E / Mile C / Medium A / Long A | Front Runner A / Pace Chaser B / Late Surger C / End Closer G | Victory Cheer! (勝ち鬨ワッショイ！) | SSR · [Gilded Shrine to Glory] · debut [JP] 2022-02-24 · debut [Global] 2026-03-12 · `stat_bonus` Speed+20, Stamina+10 (meaning unconfirmed, see 1.3.5) · 2 alternate costume cards · ★5 ceiling 122 | [GameTora card 106801](https://gametora.com/umamusume/characters/106801-kitasan-black) |
| 52 | サトノダイヤモンド | Satono Diamond | Satono Diamond | [Both] | Turf A / Dirt G | Sprint G / Mile C / Medium A / Long A | Front Runner G / Pace Chaser B / Late Surger A / End Closer D | Eternal Encompassing Shine (晦冥を照らせ永遠の耀き) | SSR · [Natural Brilliance] · debut [JP] 2022-03-07 · debut [Global] 2026-03-22 · `stat_bonus` Stamina+15, Wit+15 (meaning unconfirmed, see 1.3.5) · 2 alternate costume cards · ★5 ceiling 117 | [GameTora card 106701](https://gametora.com/umamusume/characters/106701-satono-diamond) |
| 53 | メジロブライト | Mejiro Bright | Mejiro Bright | [Both] | Turf A / Dirt G | Sprint F / Mile C / Medium A / Long A | Front Runner G / Pace Chaser D / Late Surger A / End Closer A | Lovely Spring Breeze (麗しき花信風) | SSR · [Brunissage Line] · debut [JP] 2022-03-18 · debut [Global] 2026-03-26 · `stat_bonus` Stamina+14, Guts+8, Wit+8 (meaning unconfirmed, see 1.3.5) · 2 alternate costume cards · ★5 ceiling 130 | [GameTora card 107401](https://gametora.com/umamusume/characters/107401-mejiro-bright) |
| 54 | ニシノフラワー | Nishino Flower | Nishino Flower | [Both] | Turf A / Dirt F | Sprint A / Mile A / Medium E / Long G | Front Runner F / Pace Chaser A / Late Surger A / End Closer G | Budding Blossom (つぼみ、ほころぶ時) | SSR · [Layered Petals] · debut [JP] 2022-04-11 · debut [Global] 2026-04-12 · `stat_bonus` Speed+15, Power+15 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 117 | [GameTora card 105101](https://gametora.com/umamusume/characters/105101-nishino-flower) |
| 55 | ヤエノムテキ | Yaeno Muteki | Yaeno Muteki | [Both] | Turf A / Dirt E | Sprint G / Mile B / Medium A / Long E | Front Runner F / Pace Chaser A / Late Surger A / End Closer G | Peerless Dance of Flowering Flames (烈火繚乱、無敵之舞) | SSR · [Blazed Head, Covered Fists] · debut [JP] 2022-04-19 · debut [Global] 2026-04-20 · `stat_bonus` Power+20, Guts+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 119 | [GameTora card 107201](https://gametora.com/umamusume/characters/107201-yaeno-muteki) |
| 56 | アイネスフウジン | Ines Fujin | Ines Fujin | [Both] | Turf A / Dirt G | Sprint G / Mile A / Medium A / Long C | Front Runner A / Pace Chaser C / Late Surger G / End Closer G | All Charged! It's Go Time! (チャージ完了！全速前進！) | SSR · [Always Electrifying] · debut [JP] 2022-05-10 · debut [Global] 2026-04-30 · `stat_bonus` Speed+15, Guts+15 (meaning unconfirmed, see 1.3.5) · 2 alternate costume cards · ★5 ceiling 122 | [GameTora card 103101](https://gametora.com/umamusume/characters/103101-ines-fujin) |
| 57 | メジロパーマー | Mejiro Palmer | Mejiro Palmer | [Both] | Turf A / Dirt G | Sprint G / Mile F / Medium A / Long A | Front Runner A / Pace Chaser E / Late Surger F / End Closer G | Keep Pushing Ahead (ぶっちぎりロード) | SSR · [Line Breakthrough] · debut [JP] 2022-05-20 · debut [Global] 2026-05-10 · `stat_bonus` Speed+10, Stamina+10, Guts+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 126 | [GameTora card 106401](https://gametora.com/umamusume/characters/106401-mejiro-palmer) |
| 58 | イナリワン | Inari One | Inari One | [Both] | Turf A / Dirt A | Sprint F / Mile B / Medium A / Long A | Front Runner G / Pace Chaser B / Late Surger B / End Closer A | Now We're Cruisin'! (快走かな、快走かな！) | SSR · [Edomurasaki] · debut [JP] 2022-06-10 · debut [Global] 2026-05-28 · `stat_bonus` Stamina+10, Power+20 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 133 | [GameTora card 103401](https://gametora.com/umamusume/characters/103401-inari-one) |
| 59 | スイープトウショウ | Sweep Tosho | Sweep Tosho | [Both] | Turf A / Dirt G | Sprint E / Mile A / Medium A / Long D | Front Runner G / Pace Chaser G / Late Surger A / End Closer A | Victory belongs to me—Strelitzia! ☆ (いただき☆ストレリチア！) | SSR · [Platanus Witch] · debut [JP] 2022-06-20 · debut [Global] 2026-06-04 · `stat_bonus` Speed+10, Power+20 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 135 | [GameTora card 104401](https://gametora.com/umamusume/characters/104401-sweep-tosho) |
| 60 | エアシャカール | Air Shakur | Air Shakur | [Both] | Turf A / Dirt G | Sprint G / Mile E / Medium A / Long A | Front Runner G / Pace Chaser C / Late Surger A / End Closer A | trigger:BEAT (trigger:BEAT) | SSR · [unsigned] · debut [JP] 2022-07-11 · debut [Global] 2026-06-18 · `stat_bonus` Wit+30 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 126 | [GameTora card 103601](https://gametora.com/umamusume/characters/103601-air-shakur) |
| 61 | バンブーメモリー | Bamboo Memory | Bamboo Memory | [Both] | Turf A / Dirt D | Sprint A / Mile A / Medium C / Long G | Front Runner G / Pace Chaser E / Late Surger A / End Closer C | Red-Hot Discipline! (熱血！！風紀アタック) | SSR · [Iron Ambition] · debut [JP] 2022-08-10 · debut [Global] 2026-07-07 · `stat_bonus` Speed+10, Power+10, Guts+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 127 | [GameTora card 105301](https://gametora.com/umamusume/characters/105301-bamboo-memory) |
| 62 | コパノリッキー | Copano Rickey | Copano Rickey | [Both] | Turf F / Dirt A | Sprint C / Mile A / Medium A / Long G | Front Runner A / Pace Chaser A / Late Surger C / End Closer G | Luck Runs My Way (理運開かりて翔る) | SSR · [Eightfold☆Fortune] · debut [JP] 2022-08-19 · debut [Global] 2026-07-16 · `stat_bonus` Power+10, Wit+20 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 114 | [GameTora card 109801](https://gametora.com/umamusume/characters/109801-copano-rickey) |
| 63 | ユキノビジン | Yukino Bijin | Yukino Bijin | [Both] | Turf A / Dirt B | Sprint D / Mile A / Medium A / Long E | Front Runner C / Pace Chaser A / Late Surger F / End Closer G | Snow Bright, Snow Flight (ゆきあかり、おいかけて) | SSR · [Darl'n Snowflake] · debut [JP] 2022-09-12 · debut [Global] 2026-08-05 · `stat_bonus` Speed+10, Guts+20 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 117 | [GameTora card 102901](https://gametora.com/umamusume/characters/102901-yukino-bijin) |
| 64 | シーキングザパール | Seeking the Pearl | Seeking the Pearl | [Both] | Turf A / Dirt F | Sprint A / Mile A / Medium E / Long G | Front Runner C / Pace Chaser A / Late Surger A / End Closer B | I'm Possible! (『I'm possible』) | SSR · [Rocket☆Star] · debut [JP] 2022-09-20 · debut [Global] 2026-08-12 · `stat_bonus` Speed+10, Wit+20 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 127 | [GameTora card 104201](https://gametora.com/umamusume/characters/104201-seeking-the-pearl) |
| 65 | アストンマーチャン | Aston Machan | Aston Machan | [Both] | Turf A / Dirt G | Sprint A / Mile B / Medium G / Long G | Front Runner A / Pace Chaser A / Late Surger G / End Closer G | Silent Letter (Silent letter) | SSR · [Flare] · debut [JP] 2022-10-11 · debut [Global] 2026-08-25 · `stat_bonus` Speed+20, Guts+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 121 | [GameTora card 108701](https://gametora.com/umamusume/characters/108701-aston-machan) |
| 66 | ヤマニンゼファー | Yamanin Zephyr | Yamanin Zephyr | [Both] | Turf A / Dirt D | Sprint B / Mile A / Medium A / Long G | Front Runner E / Pace Chaser A / Late Surger C / End Closer G | Sunny Breeze (風光る) | SSR · [Fluttertail Spirit] · debut [JP] 2022-10-19 · debut [Global] 2026-09-01 · `stat_bonus` Speed+10, Guts+10, Wit+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 119 | [GameTora card 107801](https://gametora.com/umamusume/characters/107801-yamanin-zephyr) |
| 67 | ナカヤマフェスタ | Nakayama Festa | Nakayama Festa | [Both] | Turf A / Dirt G | Sprint G / Mile C / Medium A / Long B | Front Runner G / Pace Chaser A / Late Surger A / End Closer D | Laugh at the Odds (剣ヶ峰より、狂気に嗤え) | SSR · [Desperate Measures] · debut [JP] 2022-11-09 · debut [Global] 2026-09-15 · `stat_bonus` Speed+10, Stamina+10, Power+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 121 | [GameTora card 104901](https://gametora.com/umamusume/characters/104901-nakayama-festa) |
| 68 | ワンダーアキュート | Wonder Acute | Wonder Acute | [Both] | Turf G / Dirt A | Sprint D / Mile A / Medium A / Long E | Front Runner C / Pace Chaser A / Late Surger C / End Closer E | Never Say Never (Never Say Never) | SSR · [Butterfly Sting] · debut [JP] 2022-11-17 · debut [Global] 2026-09-24 · `stat_bonus` Guts+15, Wit+15 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 123 | [GameTora card 110001](https://gametora.com/umamusume/characters/110001-wonder-acute) |
| 69 | ゼンノロブロイ | Zenno Rob Roy | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile E / Medium A / Long A | Front Runner G / Pace Chaser A / Late Surger A / End Closer E | Raise My Soul's Blade! (掲げよ、己が魂の剣を！) | SSR · [Heroic Author] · debut [JP] 2022-12-12 · this card has no [Global] release · `stat_bonus` Stamina+10, Wit+20 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 122 | [GameTora card 104701](https://gametora.com/umamusume/characters/104701-zenno-rob-roy) |
| 70 | ホッコータルマエ | Hokko Tarumae | N/A (unit unreleased on [Global]) | [JP-Only] | Turf G / Dirt A | Sprint F / Mile A / Medium A / Long E | Front Runner B / Pace Chaser A / Late Surger G / End Closer G | Shine On, Tomakomai! ☆ (かがやけ☆とまこまい) | SSR · [Starry Lightship] · debut [JP] 2023-01-10 · this card has no [Global] release · `stat_bonus` Speed+10, Stamina+10, Wit+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 119 | [GameTora card 109901](https://gametora.com/umamusume/characters/109901-hokko-tarumae) |
| 71 | ダイタクヘリオス | Daitaku Helios | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint B / Mile A / Medium B / Long E | Front Runner A / Pace Chaser A / Late Surger G / End Closer G | Hands in the Air Like Ya Don't Care! (アゲてアゲてぷちょへんざ！) | SSR · [Fun☆Fun☆Party Night] · debut [JP] 2023-01-20 · this card has no [Global] release · `stat_bonus` Speed+15, Power+15 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 122 | [GameTora card 106501](https://gametora.com/umamusume/characters/106501-daitaku-helios) |
| 72 | シンコウウインディ | Shinko Windy | N/A (unit unreleased on [Global]) | [JP-Only] | Turf F / Dirt A | Sprint C / Mile A / Medium B / Long G | Front Runner G / Pace Chaser A / Late Surger B / End Closer F | Ding Dong, Boo! (Ding Dong Boo) | SSR · [Wicked Punk] · debut [JP] 2023-02-13 · this card has no [Global] release · `stat_bonus` Speed+10, Power+10, Guts+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 131 | [GameTora card 104301](https://gametora.com/umamusume/characters/104301-shinko-windy) |
| 73 | ミスターシービー | Mr. C.B. | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile B / Medium A / Long A | Front Runner G / Pace Chaser E / Late Surger A / End Closer A | Lyrical Journey (叙情、旅路の果てに) | SSR · [Clear Bliss] · debut [JP] 2023-02-24 · this card has no [Global] release · `stat_bonus` Speed+10, Stamina+10, Wit+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 123 | [GameTora card 105701](https://gametora.com/umamusume/characters/105701-mr-cb) |
| 74 | ツインターボ | Twin Turbo | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt F | Sprint G / Mile A / Medium A / Long E | Front Runner A / Pace Chaser G / Late Surger G / End Closer G | Engines LIT! (エンジン点火！); Turbo BLAST! (エンジン全開！大噴射！) | R · [Turbo Engine! Full Throttle!] · debut [JP] 2023-02-24 · this card has no [Global] release · `stat_bonus` Speed+30 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 137 | [GameTora card 106601](https://gametora.com/umamusume/characters/106601-twin-turbo) |
| 75 | ダイイチルビー | Daiichi Ruby | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint A / Mile A / Medium C / Long G | Front Runner E / Pace Chaser B / Late Surger A / End Closer A | Ever Supreme (至上であれ) | SSR · [Opulent Gem] · debut [JP] 2023-03-10 · this card has no [Global] release · `stat_bonus` Power+20, Wit+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 132 | [GameTora card 108501](https://gametora.com/umamusume/characters/108501-daiichi-ruby) |
| 76 | シンボリクリスエス | Symboli Kris S | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile E / Medium A / Long A | Front Runner G / Pace Chaser A / Late Surger A / End Closer D | Mission: Triumph (Mission: Triumph) | SSR · [Onyx Soldier] · debut [JP] 2023-03-20 · this card has no [Global] release · `stat_bonus` Stamina+15, Power+15 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 125 | [GameTora card 108301](https://gametora.com/umamusume/characters/108301-symboli-kris-s) |
| 77 | サクラローレル | Sakura Laurel | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt E | Sprint G / Mile C / Medium A / Long A | Front Runner G / Pace Chaser B / Late Surger A / End Closer B | World in Bloom (花開き、世界) | SSR · [Saisir le rêve] · debut [JP] 2023-04-10 · this card has no [Global] release · `stat_bonus` Stamina+20, Power+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 123 | [GameTora card 107601](https://gametora.com/umamusume/characters/107601-sakura-laurel) |
| 78 | ネオユニヴァース | Neo Universe | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint F / Mile B / Medium A / Long B | Front Runner F / Pace Chaser A / Late Surger A / End Closer C | Ad Astra (アド・アストラ) | SSR · [Universe-Naut] · debut [JP] 2023-04-19 · this card has no [Global] release · `stat_bonus` Wit+30 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 127 | [GameTora card 110501](https://gametora.com/umamusume/characters/110501-neo-universe) |
| 79 | ヒシミラクル | Hishi Miracle | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile G / Medium A / Long A | Front Runner G / Pace Chaser C / Late Surger A / End Closer B | Bang! Miracle Shot ☆ (Bang☆ミラクるわせ！) | SSR · [Miracle Maker] · debut [JP] 2023-05-10 · this card has no [Global] release · `stat_bonus` Speed+7, Stamina+8, Power+7, Guts+8 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 116 | [GameTora card 110601](https://gametora.com/umamusume/characters/110601-hishi-miracle) |
| 80 | タニノギムレット | Tanino Gimlet | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt F | Sprint F / Mile A / Medium A / Long F | Front Runner G / Pace Chaser D / Late Surger A / End Closer A | Sublimated Thunder (霹靂のアウフヘーベン) | SSR · [Mantle of Keravnos] · debut [JP] 2023-05-19 · this card has no [Global] release · `stat_bonus` Power+30 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 125 | [GameTora card 108401](https://gametora.com/umamusume/characters/108401-tanino-gimlet) |
| 81 | マーベラスサンデー | Marvelous Sunday | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt F | Sprint G / Mile C / Medium A / Long B | Front Runner G / Pace Chaser A / Late Surger A / End Closer C | Magical☆Marvelous★World (万彩☆マーベラス★世界) | SSR · [★Super∞Marvel∞Tastic☆] · debut [JP] 2023-06-19 · this card has no [Global] release · `stat_bonus` Power+15, Wit+15 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 121 | [GameTora card 105501](https://gametora.com/umamusume/characters/105501-marvelous-sunday) |
| 82 | カツラギエース | Katsuragi Ace | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint E / Mile B / Medium A / Long B | Front Runner A / Pace Chaser A / Late Surger E / End Closer G | Sunrise Banner—Katsuragi Ace! (暁の御旗『葛城栄主』！) | SSR · [Dragon Rising] · debut [JP] 2023-07-10 · this card has no [Global] release · `stat_bonus` Speed+10, Power+10, Guts+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 121 | [GameTora card 110401](https://gametora.com/umamusume/characters/110401-katsuragi-ace) |
| 83 | シリウスシンボリ | Sirius Symboli | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile B / Medium A / Long C | Front Runner E / Pace Chaser A / Late Surger C / End Closer E | Seirios (セイリオス) | SSR · [Féroce] · debut [JP] 2023-07-21 · this card has no [Global] release · `stat_bonus` Power+10, Wit+20 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 120 | [GameTora card 107001](https://gametora.com/umamusume/characters/107001-sirius-symboli) |
| 84 | ナリタトップロード | Narita Top Road | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile C / Medium A / Long A | Front Runner F / Pace Chaser A / Late Surger B / End Closer D | Road to Glory (Road to Glory) | SSR · The Proud Road · debut [JP] 2023-08-24 · this card has no [Global] release · `stat_bonus` Speed+20, Stamina+10 (meaning unconfirmed, see 1.3.5) · 2 alternate costume cards · ★5 ceiling 124 | [GameTora card 107701](https://gametora.com/umamusume/characters/107701-narita-top-road) |
| 85 | ケイエスミラクル | K.S.Miracle | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint A / Mile B / Medium G / Long G | Front Runner E / Pace Chaser A / Late Surger B / End Closer C | Blue Ray of Happiness (幸せの青い光) | SSR · Prism · debut [JP] 2023-09-20 · this card has no [Global] release · `stat_bonus` Speed+15, Guts+15 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 125 | [GameTora card 109301](https://gametora.com/umamusume/characters/109301-ksmiracle) |
| 86 | メジロラモーヌ | Mejiro Ramonu | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt F | Sprint B / Mile A / Medium A / Long E | Front Runner G / Pace Chaser A / Late Surger A / End Closer F | Melt in Love's Embrace (愛と熔けよただ熔けよ) | SSR · Onyx Line · debut [JP] 2023-10-19 · this card has no [Global] release · `stat_bonus` Speed+15, Wit+15 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 119 | [GameTora card 108601](https://gametora.com/umamusume/characters/108601-mejiro-ramonu) |
| 87 | タップダンスシチー | Tap Dance City | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile E / Medium A / Long A | Front Runner A / Pace Chaser B / Late Surger F / End Closer G | Billions of Stars (Billions of stars) | SSR · GLITTER! · debut [JP] 2023-11-20 · this card has no [Global] release · `stat_bonus` Speed+10, Power+10, Guts+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 122 | [GameTora card 110701](https://gametora.com/umamusume/characters/110701-tap-dance-city) |
| 88 | サトノクラウン | Satono Crown | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile B / Medium A / Long E | Front Runner G / Pace Chaser B / Late Surger A / End Closer D | Reversal Illusion (Reversal Illusion) | SSR · Emerald Journey · debut [JP] 2023-12-11 · this card has no [Global] release · `stat_bonus` Power+15, Guts+15 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 129 | [GameTora card 108801](https://gametora.com/umamusume/characters/108801-satono-crown) |
| 89 | シュヴァルグラン | Cheval Grand | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile G / Medium A / Long A | Front Runner G / Pace Chaser A / Late Surger B / End Closer F | Celeste Oath (Celeste Oath) | SSR · Grand Itinéraire · debut [JP] 2023-12-20 · this card has no [Global] release · `stat_bonus` Stamina+10, Guts+10, Wit+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 127 | [GameTora card 108901](https://gametora.com/umamusume/characters/108901-cheval-grand) |
| 90 | ヴィブロス | Vivlos | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint E / Mile A / Medium A / Long G | Front Runner G / Pace Chaser D / Late Surger A / End Closer C | Deluxe☆Fountain (デラックス☆ファウンテン) | SSR · Voyage Étincelant · debut [JP] 2024-01-19 · this card has no [Global] release · `stat_bonus` Speed+10, Power+10, Wit+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 123 | [GameTora card 109101](https://gametora.com/umamusume/characters/109101-vivlos) |
| 91 | ビコーペガサス | Biko Pegasus | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt E | Sprint A / Mile B / Medium G / Long G | Front Runner G / Pace Chaser E / Late Surger A / End Closer B | Pegasus Full Power! (ペガサスフルパワー！); Blasting Gale Pegasus Dash! (疾風爆走ペガサスダッシュ！) | SR · Gale Pegasus Type Zero · debut [JP] 2024-02-14 · this card has no [Global] release · `stat_bonus` Speed+10, Power+10, Guts+10 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 136 | [GameTora card 105401](https://gametora.com/umamusume/characters/105401-biko-pegasus) |
| 92 | イクノディクタス | Ikuno Dictus | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint D / Mile A / Medium A / Long D | Front Runner D / Pace Chaser A / Late Surger A / End Closer D | Strike and Strengthen (打ち、鍛えて); Forged From a Hundred Trials (百錬成鋼) | SR · Mantle of Steel · debut [JP] 2024-02-24 · this card has no [Global] release · `stat_bonus` Guts+20, Wit+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 130 | [GameTora card 106301](https://gametora.com/umamusume/characters/106301-ikuno-dictus) |
| 93 | ドゥラメンテ | Duramente | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile A / Medium A / Long C | Front Runner G / Pace Chaser C / Late Surger A / End Closer A | Scarlet Ascension of the Rakshasa (羅刹、赤翼にて天上へ至らん) | SSR · Red in Black · debut [JP] 2024-02-24 · this card has no [Global] release · `stat_bonus` Speed+20, Power+10 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 118 | [GameTora card 110801](https://gametora.com/umamusume/characters/110801-duramente) |
| 94 | トランセンド | Transcend | N/A (unit unreleased on [Global]) | [JP-Only] | Turf F / Dirt A | Sprint G / Mile A / Medium A / Long G | Front Runner A / Pace Chaser B / Late Surger F / End Closer G | Info: Acquired (Info: Acquired) | SSR · ZOKU-ZOKU GIZMO · debut [JP] 2024-03-12 · this card has no [Global] release · `stat_bonus` Speed+10, Power+10, Wit+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 121 | [GameTora card 108001](https://gametora.com/umamusume/characters/108001-transcend) |
| 95 | ラインクラフト | Rhein Kraft | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint A / Mile A / Medium B / Long G | Front Runner E / Pace Chaser A / Late Surger C / End Closer G | Connecting Dreams With the Future (繋ぐ・繋がる×夢・未来) | SSR · Dream Successor · debut [JP] 2024-03-21 · this card has no [Global] release · `stat_bonus` Power+15, Guts+15 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 114 | [GameTora card 110901](https://gametora.com/umamusume/characters/110901-rhein-kraft) |
| 96 | サウンズオブアース | Sounds of Earth | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile F / Medium A / Long A | Front Runner G / Pace Chaser B / Late Surger A / End Closer E | Vivace Volare (ヴィヴァーチェ・ヴォラーレ) | SSR · Ritmo Della Terra · debut [JP] 2024-04-19 · this card has no [Global] release · `stat_bonus` Stamina+10, Power+10, Guts+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 116 | [GameTora card 110201](https://gametora.com/umamusume/characters/110201-sounds-of-earth) |
| 97 | ノースフライト | North Flight | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint C / Mile A / Medium B / Long G | Front Runner D / Pace Chaser A / Late Surger D / End Closer C | Shining Runway (Shining Runway) | SSR · Looking Fly! · debut [JP] 2024-05-20 · this card has no [Global] release · `stat_bonus` Speed+20, Wit+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 119 | [GameTora card 108201](https://gametora.com/umamusume/characters/108201-north-flight) |
| 98 | ジャングルポケット | Jungle Pocket | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile C / Medium A / Long B | Front Runner G / Pace Chaser D / Late Surger A / End Closer B | Faith in the Feral (Faith in the Feral) | SSR · Ruler's Battle Cry · debut [JP] 2024-06-13 · this card has no [Global] release · `stat_bonus` Speed+10, Stamina+10, Power+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 133 | [GameTora card 109401](https://gametora.com/umamusume/characters/109401-jungle-pocket) |
| 99 | ドリームジャーニー | Dream Journey | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint F / Mile C / Medium A / Long A | Front Runner G / Pace Chaser G / Late Surger A / End Closer A | «Bon Voyage» (『それでは、よき旅を』) | SSR · Dreamland Memento · debut [JP] 2024-06-26 · this card has no [Global] release · `stat_bonus` Stamina+10, Power+10, Wit+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 125 | [GameTora card 111901](https://gametora.com/umamusume/characters/111901-dream-journey) |
| 100 | カルストンライトオ | Calstone Light O | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint A / Mile D / Medium G / Long G | Front Runner A / Pace Chaser C / Late Surger G / End Closer G | Path of Singular Focus (無二無三なる一条の路) | SSR · One True Path · debut [JP] 2024-07-19 · this card has no [Global] release · `stat_bonus` Speed+15, Power+15 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 145 | [GameTora card 112001](https://gametora.com/umamusume/characters/112001-calstone-light-o) |
| 101 | ジェンティルドンナ | Gentildonna | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile A / Medium A / Long A | Front Runner E / Pace Chaser A / Late Surger A / End Closer D | Rose Conquest (烈華の洗礼) | SSR · Regina dei Fiori · debut [JP] 2024-08-24 · this card has no [Global] release · `stat_bonus` Speed+10, Stamina+10, Power+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 121 | [GameTora card 111601](https://gametora.com/umamusume/characters/111601-gentildonna) |
| 102 | シーザリオ | Cesario | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile A / Medium A / Long F | Front Runner G / Pace Chaser A / Late Surger A / End Closer C | Guiding Sea (Guiding Sea) | SSR · Future Weaver · debut [JP] 2024-09-10 · this card has no [Global] release · `stat_bonus` Speed+10, Power+10, Wit+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 120 | [GameTora card 111001](https://gametora.com/umamusume/characters/111001-cesario) |
| 103 | デュランダル | Durandal | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint A / Mile A / Medium F / Long G | Front Runner G / Pace Chaser G / Late Surger C / End Closer A | Lame de Vent (Lame de vent) | SSR · Chevalier Fidèle · debut [JP] 2024-09-20 · this card has no [Global] release · `stat_bonus` Speed+20, Power+10 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 128 | [GameTora card 112101](https://gametora.com/umamusume/characters/112101-durandal) |
| 104 | バブルガムフェロー | Bubble Gum Fellow | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile A / Medium A / Long G | Front Runner E / Pace Chaser A / Late Surger B / End Closer G | Growing Dreams, Pioneer's Path (ふくらむ夢、先駆の途) | SSR · POPPING! · debut [JP] 2024-10-11 · this card has no [Global] release · `stat_bonus` Speed+20, Guts+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 122 | [GameTora card 112401](https://gametora.com/umamusume/characters/112401-bubble-gum-fellow) |
| 105 | エアメサイア | Air Messiah | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint C / Mile B / Medium A / Long G | Front Runner G / Pace Chaser B / Late Surger A / End Closer E | Traced Bloodline, Budding Future (辿る血脈、芽吹く未来) | SSR · Inherited Hope · debut [JP] 2024-11-18 · this card has no [Global] release · `stat_bonus` Power+15, Wit+15 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 112 | [GameTora card 111101](https://gametora.com/umamusume/characters/111101-air-messiah) |
| 106 | ウインバリアシオン | Win Variation | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile E / Medium A / Long A | Front Runner G / Pace Chaser E / Late Surger A / End Closer A | Allegro of Valor (鋭気のアレグロ) | SSR · Dramatic Tutu · debut [JP] 2024-12-10 · this card has no [Global] release · `stat_bonus` Speed+10, Power+10, Guts+10 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 122 | [GameTora card 111701](https://gametora.com/umamusume/characters/111701-win-variation) |
| 107 | フリオーソ | Furioso | N/A (unit unreleased on [Global]) | [JP-Only] | Turf G / Dirt A | Sprint F / Mile A / Medium A / Long F | Front Runner A / Pace Chaser A / Late Surger E / End Closer G | Funabashi Above All! (『船橋最強！』) | SSR · Triumphant Return of the Auspicious Star · debut [JP] 2025-01-20 · this card has no [Global] release · `stat_bonus` Speed+20, Guts+10 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 121 | [GameTora card 107901](https://gametora.com/umamusume/characters/107901-furioso) |
| 108 | ツルマルツヨシ | Tsurumaru Tsuyoshi | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt F | Sprint F / Mile D / Medium A / Long C | Front Runner G / Pace Chaser A / Late Surger A / End Closer E | Steel Your Heart, Tsuyoshi! (心、強し！); Flames of Unyielding Resolve (燃え盛るは絶対の意志) | SR · Dream Bold, Stand Tall, Tsuyoshi! · debut [JP] 2025-02-14 · this card has no [Global] release · `stat_bonus` Power+20, Guts+10 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 126 | [GameTora card 107301](https://gametora.com/umamusume/characters/107301-tsurumaru-tsuyoshi) |
| 109 | オルフェーヴル | Orfevre | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt D | Sprint G / Mile C / Medium A / Long A | Front Runner G / Pace Chaser F / Late Surger A / End Closer A | None Shall Object My Rule (我が覇道、阻むものなし) | SSR · Total Dominion · debut [JP] 2025-02-24 · this card has no [Global] release · `stat_bonus` Speed+10, Stamina+10, Power+10 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 123 | [GameTora card 111501](https://gametora.com/umamusume/characters/111501-orfevre) |
| 110 | グランアレグリア | Gran Alegria | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint A / Mile A / Medium C / Long G | Front Runner F / Pace Chaser A / Late Surger A / End Closer B | ¡Qué alegría! (¡Qué alegría!) | SSR · sMile My Way! · debut [JP] 2025-03-11 · this card has no [Global] release · `stat_bonus` Power+10, Guts+20 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 124 | [GameTora card 113101](https://gametora.com/umamusume/characters/113101-gran-alegria) |
| 111 | ノーリーズン | No Reason | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile B / Medium A / Long C | Front Runner G / Pace Chaser D / Late Surger A / End Closer F | Deceive Thy Foe and Be Certain of Victory (知宵欺敵、百戦不殆) | SSR · Swift Crimson Armor · debut [JP] 2025-03-21 · this card has no [Global] release · `stat_bonus` Stamina+20, Wit+10 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 124 | [GameTora card 109601](https://gametora.com/umamusume/characters/109601-no-reason) |
| 112 | フェノーメノ | Fenomeno | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile G / Medium A / Long A | Front Runner C / Pace Chaser A / Late Surger E / End Closer G | Target Acquired! Serving Justice! (対象捕捉！正義遂行！) | SSR · Black Flame of Righteousness · debut [JP] 2025-04-21 · this card has no [Global] release · `stat_bonus` Stamina+10, Guts+20 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 142 | [GameTora card 112701](https://gametora.com/umamusume/characters/112701-fenomeno) |
| 113 | ヴィルシーナ | Verxina | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint D / Mile A / Medium A / Long G | Front Runner A / Pace Chaser A / Late Surger E / End Closer G | Queen's Rebirth (Queen's Rebirth) | SSR · Le Beau Sommet · debut [JP] 2025-05-12 · this card has no [Global] release · `stat_bonus` Power+15, Guts+15 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 122 | [GameTora card 109001](https://gametora.com/umamusume/characters/109001-verxina) |
| 114 | ラヴズオンリーユー | Loves Only You | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile C / Medium A / Long F | Front Runner G / Pace Chaser A / Late Surger A / End Closer G | Circulating Love♡ (Circulating Love♡) | SSR · 9927 Wishes · debut [JP] 2025-05-21 · this card has no [Global] release · `stat_bonus` Speed+10, Stamina+10, Guts+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 126 | [GameTora card 113201](https://gametora.com/umamusume/characters/113201-loves-only-you) |
| 115 | クロノジェネシス | Chrono Genesis | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile B / Medium A / Long A | Front Runner G / Pace Chaser A / Late Surger C / End Closer E | Weaving History (Weaving History) | SSR · Prismatic Curator · debut [JP] 2025-06-13 · this card has no [Global] release · `stat_bonus` Speed+10, Stamina+10, Guts+10 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 119 | [GameTora card 113301](https://gametora.com/umamusume/characters/113301-chrono-genesis) |
| 116 | フサイチパンドラ | Fusaichi Pandora | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt E | Sprint G / Mile B / Medium A / Long G | Front Runner C / Pace Chaser A / Late Surger B / End Closer F | Hop Step Gotcha ♡ (ホップステップ・ゲッチュ♡) | SSR · Assorted Cuteness♡ · debut [JP] 2025-07-22 · this card has no [Global] release · `stat_bonus` Stamina+10, Guts+10, Wit+10 (meaning unconfirmed, see 1.3.5) · 1 alternate costume card · ★5 ceiling 116 | [GameTora card 111301](https://gametora.com/umamusume/characters/111301-fusaichi-pandora) |
| 117 | スティルインラブ | Still in Love | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint C / Mile A / Medium A / Long G | Front Runner G / Pace Chaser A / Late Surger A / End Closer F | Scarlet Lily's Elation (スカーレットリリィの高揚) | SSR · Scarlet Vow Raiment · debut [JP] 2025-08-24 · this card has no [Global] release · `stat_bonus` Stamina+10, Power+10, Guts+10 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 119 | [GameTora card 109701](https://gametora.com/umamusume/characters/109701-still-in-love) |
| 118 | エスポワールシチー | Espoir City | N/A (unit unreleased on [Global]) | [JP-Only] | Turf E / Dirt A | Sprint A / Mile A / Medium B / Long G | Front Runner A / Pace Chaser A / Late Surger F / End Closer G | Punkish Bite (Punkish Bite) | SSR · Red Devil Gear · debut [JP] 2025-09-09 · this card has no [Global] release · `stat_bonus` Power+10, Guts+20 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 116 | [GameTora card 108101](https://gametora.com/umamusume/characters/108101-espoir-city) |
| 119 | ビリーヴ | Believe | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint A / Mile D / Medium G / Long G | Front Runner D / Pace Chaser A / Late Surger D / End Closer G | Because I Believe (念い、信ずればこそ) | SSR · Purity · debut [JP] 2025-09-19 · this card has no [Global] release · `stat_bonus` Power+10, Guts+10, Wit+10 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 126 | [GameTora card 109501](https://gametora.com/umamusume/characters/109501-believe) |
| 120 | ダンツフレーム | Dantsu Flame | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt F | Sprint E / Mile B / Medium A / Long D | Front Runner G / Pace Chaser A / Late Surger B / End Closer D | My Flame Lights the Way (照らすのはわたしの焔) | SSR · Center ◎ Spotlight · debut [JP] 2025-10-18 · this card has no [Global] release · `stat_bonus` Stamina+10, Guts+20 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 116 | [GameTora card 109201](https://gametora.com/umamusume/characters/109201-dantsu-flame) |
| 121 | ブエナビスタ | Buena Vista | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt F | Sprint G / Mile A / Medium A / Long C | Front Runner G / Pace Chaser B / Late Surger A / End Closer A | To Our Vista (To Our Vista) | SSR · Heroína Inocente · debut [JP] 2025-11-19 · this card has no [Global] release · `stat_bonus` Speed+10, Power+10, Wit+10 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 122 | [GameTora card 111401](https://gametora.com/umamusume/characters/111401-buena-vista) |
| 122 | ステイゴールド | Stay Gold | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile G / Medium A / Long A | Front Runner G / Pace Chaser B / Late Surger A / End Closer C | In Search of Gold (黄金を訪ねて) | SSR · Sunlit Outsider · debut [JP] 2025-12-21 · this card has no [Global] release · `stat_bonus` Stamina+10, Guts+20 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 115 | [GameTora card 113501](https://gametora.com/umamusume/characters/113501-stay-gold) |
| 123 | キセキ | Kiseki | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile C / Medium A / Long A | Front Runner A / Pace Chaser B / Late Surger A / End Closer E | Imagination × Creation = ∞ (想像×創造＝∞) | SSR · Miracle Author · debut [JP] 2026-01-19 · this card has no [Global] release · `stat_bonus` Speed+10, Guts+10, Wit+10 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 119 | [GameTora card 113701](https://gametora.com/umamusume/characters/113701-kiseki) |
| 124 | ロイスアンドロイス | Royce and Royce | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile B / Medium A / Long E | Front Runner E / Pace Chaser A / Late Surger A / End Closer G | Showcase Time! (アピール宣言！); Absolute Strongest ☆ Showcase Time! (絶対最強☆アピール宣言！) | SR · Inspiring Genius · debut [JP] 2026-02-14 · this card has no [Global] release · `stat_bonus` Power+10, Wit+20 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 121 | [GameTora card 110301](https://gametora.com/umamusume/characters/110301-royce-and-royce) |
| 125 | アーモンドアイ | Almond Eye | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile A / Medium A / Long F | Front Runner G / Pace Chaser A / Late Surger A / End Closer D | Peerless Heroine (Peerless Heroine) | SSR · The Changer · debut [JP] 2026-02-24 · this card has no [Global] release · `stat_bonus` Speed+10, Stamina+10, Power+10 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 127 | [GameTora card 112901](https://gametora.com/umamusume/characters/112901-almond-eye) |
| 126 | ヴィクトワールピサ | Victoire Pisa | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt A | Sprint G / Mile B / Medium A / Long B | Front Runner G / Pace Chaser A / Late Surger A / End Closer E | Ring-a-Link (Ring-a-Link) | SSR · Lueur Angélique · debut [JP] 2026-03-19 · this card has no [Global] release · `stat_bonus` Stamina+10, Guts+10, Wit+10 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 120 | [GameTora card 114301](https://gametora.com/umamusume/characters/114301-victoire-pisa) |
| 127 | ラッキーライラック | Lucky Lilac | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile A / Medium A / Long C | Front Runner F / Pace Chaser A / Late Surger A / End Closer G | Blooming Star (咲う花形) | SSR · Standard of Unyielding Resolve · debut [JP] 2026-04-10 · this card has no [Global] release · `stat_bonus` Power+10, Guts+10, Wit+10 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 115 | [GameTora card 113001](https://gametora.com/umamusume/characters/113001-lucky-lilac) |
| 128 | アドマイヤグルーヴ | Admire Groove | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile B / Medium A / Long G | Front Runner G / Pace Chaser B / Late Surger A / End Closer C | Distant Far North (悠遠のファーノース) | SSR · Glacial Queen · debut [JP] 2026-04-20 · this card has no [Global] release · `stat_bonus` Speed+10, Guts+10, Wit+10 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 118 | [GameTora card 111801](https://gametora.com/umamusume/characters/111801-admire-groove) |
| 129 | デアリングハート | Daring Heart | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt D | Sprint B / Mile A / Medium E / Long G | Front Runner D / Pace Chaser A / Late Surger F / End Closer G | With All My Heart (With All My Heart) | SSR · Daring Darling! · debut [JP] 2026-05-11 · this card has no [Global] release · `stat_bonus` Guts+20, Wit+10 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 119 | [GameTora card 111201](https://gametora.com/umamusume/characters/111201-daring-heart) |
| 130 | レッドディザイア | Red Desire | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt B | Sprint G / Mile A / Medium A / Long F | Front Runner G / Pace Chaser B / Late Surger A / End Closer F | Innocent Prayer: Holy Light (無垢の祈り・ホーリーライト) | SSR · Divine Raiment · debut [JP] 2026-05-20 · this card has no [Global] release · `stat_bonus` Speed+10, Guts+10, Wit+10 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 117 | [GameTora card 113601](https://gametora.com/umamusume/characters/113601-red-desire) |
| 131 | カレンブーケドール | Curren Bouquetd'or | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile B / Medium A / Long B | Front Runner E / Pace Chaser A / Late Surger B / End Closer G | Flora That Will Bloom for You (いずれあなたに咲くフローラ) | SSR · Couture in Bloom · debut [JP] 2026-06-15 · this card has no [Global] release · `stat_bonus` Stamina+10, Guts+10, Wit+10 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 118 | [GameTora card 113401](https://gametora.com/umamusume/characters/113401-curren-bouquetdor) |
| 132 | ルーラーシップ | Rulership | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile B / Medium A / Long B | Front Runner G / Pace Chaser A / Late Surger A / End Closer A | Unmatched Tactics (冠絶タクティクス) | SSR · Monochrome GM · debut [JP] 2026-07-21 · this card has no [Global] release · `stat_bonus` Stamina+10, Power+10, Wit+10 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 119 | [GameTora card 114501](https://gametora.com/umamusume/characters/114501-rulership) |
| 133 | エピファネイア | Epiphaneia | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile E / Medium A / Long A | Front Runner D / Pace Chaser A / Late Surger B / End Closer G | Shine! Burst! Supernova! (光れ！弾けろ！超新星爆発！) | SSR · Fate's Chosen Star · debut [JP] 2026-08-24 · this card has no [Global] release · `stat_bonus` Stamina+10, Power+10, Wit+10 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 115 | [GameTora card 114101](https://gametora.com/umamusume/characters/114101-epiphaneia) |
| 134 | ファレノプシス | Phalaenopsis | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt F | Sprint D / Mile A / Medium A / Long F | Front Runner G / Pace Chaser A / Late Surger A / End Closer C | Bloom Fiercely (烈々と咲く) | SSR · untitled · debut [JP] 2026-09-11 · this card has no [Global] release · `stat_bonus` Stamina+10, Power+10, Guts+10 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 120 | [GameTora card 114901](https://gametora.com/umamusume/characters/114901-phalaenopsis) |
| 135 | ローズキングダム | Rose Kingdom | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile A / Medium A / Long C | Front Runner G / Pace Chaser A / Late Surger B / End Closer G | Épanouissement by Royal Decree (エパヌイスマンの大号令) | SSR · Éclat de Roseraie · debut [JP] 2026-09-18 · this card has no [Global] release · `stat_bonus` Power+10, Guts+10, Wit+10 (meaning unconfirmed, see 1.3.5) · 0 alternate costume cards · ★5 ceiling 115 | [GameTora card 114401](https://gametora.com/umamusume/characters/114401-rose-kingdom) |

### 3.2 Alternate costume cards

Same eleven columns, one row per costume card that is not the unit's debut form. Aptitudes, unique skills
and stat ceilings can differ from the debut form, which is why they are not folded into 2.1.

| # | JP Name | Romanized Name | Global EN Name | Server Status | Aptitude (Track) | Aptitude (Distance) | Aptitude (Strategy) | Unique Skill Name | Notable Traits | Source |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | トウカイテイオー | Tokai Teio | Tokai Teio | [Both] | Turf A / Dirt G | Sprint F / Mile E / Medium A / Long B | Front Runner D / Pace Chaser A / Late Surger C / End Closer E | Certain Victory (絶対は、ボクだ) | SSR · [Beyond the Horizon] · debut [JP] 2021-03-30 · debut [Global] 2025-07-16 · `stat_bonus` Speed+10, Stamina+10, Guts+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 125 | [GameTora card 100302](https://gametora.com/umamusume/characters/100302-tokai-teio) |
| 2 | メジロマックイーン | Mejiro McQueen | Mejiro McQueen | [Both] | Turf A / Dirt E | Sprint G / Mile F / Medium A / Long A | Front Runner B / Pace Chaser A / Late Surger D / End Closer F | Legacy of the Strong (最強の名を懸けて) | SSR · [End of the Skies] · debut [JP] 2021-03-30 · debut [Global] 2025-07-16 · `stat_bonus` Stamina+10, Power+10, Wit+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 134 | [GameTora card 101302](https://gametora.com/umamusume/characters/101302-mejiro-mcqueen) |
| 3 | エアグルーヴ | Air Groove | Air Groove | [Both] | Turf A / Dirt G | Sprint C / Mile B / Medium A / Long E | Front Runner D / Pace Chaser A / Late Surger A / End Closer G | Eternal Moments (薫風、永遠なる瞬間を) | SSR · [Quercus Civilis] · debut [JP] 2021-05-28 · debut [Global] 2025-08-28 · `stat_bonus` Speed+10, Power+10, Guts+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 117 | [GameTora card 101802](https://gametora.com/umamusume/characters/101802-air-groove) |
| 4 | マヤノトップガン | Mayano Top Gun | Mayano Top Gun | [Both] | Turf A / Dirt E | Sprint D / Mile D / Medium A / Long A | Front Runner A / Pace Chaser A / Late Surger B / End Closer B | Flowery☆Maneuver (フラワリー☆マニューバ) | SSR · [Sunlight Bouquet] · debut [JP] 2021-05-28 · debut [Global] 2025-08-28 · `stat_bonus` Speed+10, Stamina+10, Wit+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 123 | [GameTora card 102402](https://gametora.com/umamusume/characters/102402-mayano-top-gun) |
| 5 | グラスワンダー | Grass Wonder | Grass Wonder | [Both] | Turf A / Dirt G | Sprint G / Mile A / Medium B / Long A | Front Runner F / Pace Chaser A / Late Surger A / End Closer F | Superior Heal (ゲインヒール・スペリアー) | SSR · [Saintly Jade Cleric] · debut [JP] 2021-06-29 · debut [Global] 2025-09-21 · `stat_bonus` Stamina+15, Wit+15 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 123 | [GameTora card 101102](https://gametora.com/umamusume/characters/101102-grass-wonder) |
| 6 | エルコンドルパサー | El Condor Pasa | El Condor Pasa | [Both] | Turf A / Dirt B | Sprint F / Mile A / Medium A / Long B | Front Runner E / Pace Chaser A / Late Surger A / End Closer C | Condor's Fury (コンドル猛撃波) | SSR · [Kukulkan Warrior] · debut [JP] 2021-06-29 · debut [Global] 2025-09-21 · `stat_bonus` Speed+15, Guts+15 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 122 | [GameTora card 101402](https://gametora.com/umamusume/characters/101402-el-condor-pasa) |
| 7 | スペシャルウィーク | Special Week | Special Week | [Both] | Turf A / Dirt G | Sprint F / Mile C / Medium A / Long A | Front Runner G / Pace Chaser A / Late Surger A / End Closer C | Dazzl'n ♪ Diver (わやかわ♪マリンダイヴ) | SSR · [Hopp'n♪Happy Heart] · debut [JP] 2021-07-29 · debut [Global] 2025-10-14 · `stat_bonus` Stamina+10, Power+10, Guts+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 125 | [GameTora card 100102](https://gametora.com/umamusume/characters/100102-special-week) |
| 8 | マルゼンスキー | Maruzensky | Maruzensky | [Both] | Turf A / Dirt D | Sprint B / Mile A / Medium B / Long C | Front Runner A / Pace Chaser E / Late Surger G / End Closer G | A Kiss for Courage (グッときて♪Chu) | SSR · [Hot☆Summer Night] · debut [JP] 2021-07-29 · debut [Global] 2025-10-14 · `stat_bonus` Speed+15, Wit+15 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 122 | [GameTora card 100402](https://gametora.com/umamusume/characters/100402-maruzensky) |
| 9 | マチカネフクキタル | Matikanefukukitaru | Matikanefukukitaru | [Both] | Turf A / Dirt F | Sprint F / Mile C / Medium A / Long A | Front Runner G / Pace Chaser B / Late Surger A / End Closer F | Bountiful Harvest (禾スナハチ登ル) | SSR · [Lucky Tidings] · debut [JP] 2021-08-30 · debut [Global] 2025-11-06 · `stat_bonus` Stamina+10, Guts+10, Wit+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 122 | [GameTora card 105602](https://gametora.com/umamusume/characters/105602-matikanefukukitaru) |
| 10 | ライスシャワー | Rice Shower | Rice Shower | [Both] | Turf A / Dirt G | Sprint E / Mile C / Medium A / Long A | Front Runner B / Pace Chaser A / Late Surger C / End Closer G | Every Rose Has Its Fangs (Drain for rose) | SSR · [Vampire Makeover!] · debut [JP] 2021-09-29 · debut [Global] 2025-11-24 · `stat_bonus` Stamina+15, Power+15 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 148 | [GameTora card 103002](https://gametora.com/umamusume/characters/103002-rice-shower) |
| 11 | スーパークリーク | Super Creek | Super Creek | [Both] | Turf A / Dirt G | Sprint G / Mile G / Medium A / Long A | Front Runner D / Pace Chaser A / Late Surger B / End Closer G | Give Mummy a Hug ♡ (ぐるぐるマミートリック♡) | SSR · [Chiffon-Wrapped Mummy] · debut [JP] 2021-09-29 · debut [Global] 2025-11-24 · `stat_bonus` Speed+14, Stamina+8, Guts+8 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 129 | [GameTora card 104502](https://gametora.com/umamusume/characters/104502-super-creek) |
| 12 | シンボリルドルフ | Symboli Rudolf | Symboli Rudolf | [Both] | Turf A / Dirt G | Sprint E / Mile C / Medium A / Long A | Front Runner B / Pace Chaser A / Late Surger A / End Closer C | Arrows Whistle, Shadows Disperse (翳り退く、さざめきの矢) | SSR · [Archer by Moonlight] · debut [JP] 2021-10-28 · debut [Global] 2025-12-14 · `stat_bonus` Speed+8, Stamina+14, Wit+8 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 122 | [GameTora card 101702](https://gametora.com/umamusume/characters/101702-symboli-rudolf) |
| 13 | ゴールドシチー | Gold City | Gold City | [Both] | Turf A / Dirt D | Sprint F / Mile A / Medium B / Long B | Front Runner F / Pace Chaser A / Late Surger A / End Closer F | Dancing in the Leaves (GET DOWN) | SSR · [Autumn Cosmos] · debut [JP] 2021-10-28 · debut [Global] 2025-12-14 · `stat_bonus` Speed+8, Power+8, Wit+14 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 120 | [GameTora card 104002](https://gametora.com/umamusume/characters/104002-gold-city) |
| 14 | オグリキャップ | Oguri Cap | Oguri Cap | [Both] | Turf A / Dirt B | Sprint E / Mile A / Medium A / Long B | Front Runner F / Pace Chaser A / Late Surger A / End Closer D | Festive Miracle (聖夜のミラクルラン！) | SSR · [Ashen Miracle] · debut [JP] 2021-11-29 · debut [Global] 2026-01-05 · `stat_bonus` Speed+15, Stamina+15 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 136 | [GameTora card 100602](https://gametora.com/umamusume/characters/100602-oguri-cap) |
| 15 | ビワハヤヒデ | Biwa Hayahide | Biwa Hayahide | [Both] | Turf A / Dirt F | Sprint F / Mile C / Medium A / Long A | Front Runner E / Pace Chaser A / Late Surger B / End Closer E | Presents from X (Presents from X) | SSR · [Rouge Caroler] · debut [JP] 2021-11-29 · debut [Global] 2026-01-05 · `stat_bonus` Stamina+12, Power+12, Wit+6 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 121 | [GameTora card 102302](https://gametora.com/umamusume/characters/102302-biwa-hayahide) |
| 16 | テイエムオペラオー | TM Opera O | TM Opera O | [Both] | Turf A / Dirt E | Sprint G / Mile E / Medium A / Long A | Front Runner C / Pace Chaser A / Late Surger A / End Closer G | Barcarole of Blessings (恵福バルカローレ) | SSR · [New Year, Same Radiance!] · debut [JP] 2021-12-31 · debut [Global] 2026-01-29 · `stat_bonus` Speed+14, Stamina+8, Wit+8 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 124 | [GameTora card 101502](https://gametora.com/umamusume/characters/101502-tm-opera-o) |
| 17 | ハルウララ | Haru Urara | Haru Urara | [Both] | Turf G / Dirt A | Sprint A / Mile A / Medium G / Long G | Front Runner G / Pace Chaser G / Late Surger A / End Closer B | 114th Time's the Charm (113転び114起き) | SSR · [New Year ♪ New Urara!] · debut [JP] 2021-12-31 · debut [Global] 2026-01-29 · `stat_bonus` Power+20, Guts+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 130 | [GameTora card 105202](https://gametora.com/umamusume/characters/105202-haru-urara) |
| 18 | ミホノブルボン | Mihono Bourbon | Mihono Bourbon | [Both] | Turf A / Dirt G | Sprint C / Mile B / Medium A / Long B | Front Runner A / Pace Chaser E / Late Surger G / End Closer G | Operation Cacao (オペレーション・Cacao) | SSR · [CODE: ICING] · debut [JP] 2022-01-28 · debut [Global] 2026-02-18 · `stat_bonus` Speed+10, Stamina+10, Power+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 123 | [GameTora card 102602](https://gametora.com/umamusume/characters/102602-mihono-bourbon) |
| 19 | エイシンフラッシュ | Eishin Flash | Eishin Flash | [Both] | Turf A / Dirt G | Sprint G / Mile F / Medium A / Long A | Front Runner G / Pace Chaser B / Late Surger A / End Closer C | Guten Appetit ♪ (Guten Appetit♪) | SSR · [Precise Chocolatier] · debut [JP] 2022-01-28 · debut [Global] 2026-02-18 · `stat_bonus` Stamina+8, Power+8, Wit+14 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 125 | [GameTora card 103702](https://gametora.com/umamusume/characters/103702-eishin-flash) |
| 20 | フジキセキ | Fuji Kiseki | Fuji Kiseki | [Both] | Turf A / Dirt F | Sprint B / Mile A / Medium B / Long E | Front Runner C / Pace Chaser A / Late Surger C / End Closer G | Ravissant (Ravissant) | SSR · [Succès Étoilé] · debut [JP] 2022-03-29 · debut [Global] 2026-04-05 · `stat_bonus` Speed+8, Power+14, Wit+8 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 124 | [GameTora card 100502](https://gametora.com/umamusume/characters/100502-fuji-kiseki) |
| 21 | セイウンスカイ | Seiun Sky | Seiun Sky | [Both] | Turf A / Dirt G | Sprint G / Mile C / Medium A / Long A | Front Runner A / Pace Chaser B / Late Surger D / End Closer E | Break It Down! (Do Ya Breakin!) | SSR · [Soirée des Chatons] · debut [JP] 2022-03-29 · debut [Global] 2026-04-05 · `stat_bonus` Speed+8, Power+8, Guts+14 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 123 | [GameTora card 102002](https://gametora.com/umamusume/characters/102002-seiun-sky) |
| 22 | ナイスネイチャ | Nice Nature | Nice Nature | [Both] | Turf A / Dirt G | Sprint G / Mile C / Medium A / Long A | Front Runner F / Pace Chaser B / Late Surger A / End Closer D | Go☆Go☆Goal! (Go☆Go☆for it!) | SSR · [Run & Win] · debut [JP] 2022-04-28 · debut [Global] 2026-04-26 · `stat_bonus` Stamina+10, Power+10, Wit+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 120 | [GameTora card 106002](https://gametora.com/umamusume/characters/106002-nice-nature) |
| 23 | キングヘイロー | King Halo | King Halo | [Both] | Turf A / Dirt G | Sprint A / Mile B / Medium B / Long C | Front Runner G / Pace Chaser B / Late Surger A / End Closer D | Louder! Tracen Cheer! (轟！トレセン応援団！！) | SSR · [Cheerleader in Noble White] · debut [JP] 2022-04-28 · debut [Global] 2026-04-26 · `stat_bonus` Speed+10, Power+10, Guts+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 134 | [GameTora card 106102](https://gametora.com/umamusume/characters/106102-king-halo) |
| 24 | ファインモーション | Fine Motion | Fine Motion | [Both] | Turf A / Dirt G | Sprint F / Mile A / Medium A / Long C | Front Runner D / Pace Chaser A / Late Surger E / End Closer C | Best Day Ever (Best day ever) | SSR · [Titania] · debut [JP] 2022-05-30 · debut [Global] 2026-05-18 · `stat_bonus` Guts+10, Wit+20 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 121 | [GameTora card 102202](https://gametora.com/umamusume/characters/102202-fine-motion) |
| 25 | カレンチャン | Curren Chan | Curren Chan | [Both] | Turf A / Dirt F | Sprint A / Mile D / Medium G / Long G | Front Runner B / Pace Chaser A / Late Surger E / End Closer G | One True Color (One True Color) | SSR · [Ma Chérie of the New Moon] · debut [JP] 2022-05-30 · debut [Global] 2026-05-18 · `stat_bonus` Speed+10, Power+10, Wit+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 133 | [GameTora card 103802](https://gametora.com/umamusume/characters/103802-curren-chan) |
| 26 | タイキシャトル | Taiki Shuttle | Taiki Shuttle | [Both] | Turf A / Dirt B | Sprint A / Mile A / Medium E / Long G | Front Runner C / Pace Chaser A / Late Surger E / End Closer G | Joyful Voyage! (Joyful Voyage!) | SSR · [Bubblegum☆Memories] · debut [JP] 2022-06-30 · debut [Global] 2026-06-11 · `stat_bonus` Power+30 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 130 | [GameTora card 101002](https://gametora.com/umamusume/characters/101002-taiki-shuttle) |
| 27 | メジロドーベル | Mejiro Dober | Mejiro Dober | [Both] | Turf A / Dirt G | Sprint E / Mile A / Medium A / Long F | Front Runner C / Pace Chaser B / Late Surger A / End Closer G | Wherever This Wonder Leads (ときめきが呼ぶほうへ) | SSR · [Sapphire Sojourn] · debut [JP] 2022-06-30 · debut [Global] 2026-06-11 · `stat_bonus` Speed+20, Wit+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 122 | [GameTora card 105902](https://gametora.com/umamusume/characters/105902-mejiro-dober) |
| 28 | スペシャルウィーク | Special Week | Special Week | [Both] | Turf A / Dirt G | Sprint F / Mile C / Medium A / Long A | Front Runner G / Pace Chaser A / Late Surger A / End Closer C | Dreams Donned with Pride! (威風堂々、夢錦！) | SSR · [Ruler of Japan] · debut [JP] 2022-07-20 · debut [Global] 2026-06-25 · `stat_bonus` Speed+10, Stamina+10, Wit+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 118 | [GameTora card 100103](https://gametora.com/umamusume/characters/100103-special-week) |
| 29 | ゴールドシップ | Gold Ship | Gold Ship | [Both] | Turf A / Dirt G | Sprint G / Mile C / Medium A / Long A | Front Runner G / Pace Chaser B / Late Surger B / End Closer A | 564 Escapades (Adventure of 564) | SSR · [RUN! RUIN! LAUNCHER!] · debut [JP] 2022-07-29 · debut [Global] 2026-07-02 · `stat_bonus` Power+20, Wit+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 137 | [GameTora card 100702](https://gametora.com/umamusume/characters/100702-gold-ship) |
| 30 | メジロマックイーン | Mejiro McQueen | Mejiro McQueen | [Both] | Turf A / Dirt E | Sprint G / Mile F / Medium A / Long A | Front Runner B / Pace Chaser A / Late Surger D / End Closer F | Your Smile Sparkles as the Waves (きらめくは海、まばゆきは君) | SSR · [Fair Lady of the Waves] · debut [JP] 2022-07-29 · debut [Global] 2026-07-02 · `stat_bonus` Speed+8, Stamina+8, Wit+14 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 138 | [GameTora card 101303](https://gametora.com/umamusume/characters/101303-mejiro-mcqueen) |
| 31 | スマートファルコン | Smart Falcon | Smart Falcon | [Both] | Turf E / Dirt A | Sprint B / Mile A / Medium A / Long E | Front Runner A / Pace Chaser D / Late Surger G / End Closer G | α-star* (α-star*) | SSR · [Twilight Triumph] · debut [JP] 2022-08-24 · debut [Global] 2026-07-22 · `stat_bonus` Speed+20, Guts+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 128 | [GameTora card 104602](https://gametora.com/umamusume/characters/104602-smart-falcon) |
| 32 | ウイニングチケット | Winning Ticket | Winning Ticket | [Both] | Turf A / Dirt G | Sprint G / Mile F / Medium A / Long B | Front Runner G / Pace Chaser B / Late Surger A / End Closer G | Ticket to Your Dreams! (夢の先へ、届け！) | SSR · [Dream Deliverer] · debut [JP] 2022-08-29 · debut [Global] 2026-07-27 · `stat_bonus` Speed+8, Power+14, Guts+8 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 133 | [GameTora card 103502](https://gametora.com/umamusume/characters/103502-winning-ticket) |
| 33 | ナリタタイシン | Narita Taishin | Narita Taishin | [Both] | Turf A / Dirt G | Sprint F / Mile D / Medium A / Long A | Front Runner G / Pace Chaser F / Late Surger B / End Closer A | Hephaestus (Hephaistos) | SSR · [Difference Engineer] · debut [JP] 2022-08-29 · debut [Global] 2026-07-27 · `stat_bonus` Stamina+8, Guts+8, Wit+14 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 124 | [GameTora card 105002](https://gametora.com/umamusume/characters/105002-narita-taishin) |
| 34 | アグネスデジタル | Agnes Digital | Agnes Digital | [Both] | Turf A / Dirt A | Sprint F / Mile A / Medium A / Long G | Front Runner G / Pace Chaser A / Late Surger A / End Closer B | THE MOE AAAA Thanks for My Life (萌到讓我活過來了！) | SSR · [Fanatic♡Jiangshi] · debut [JP] 2022-09-29 · debut [Global] 2026-08-18 · `stat_bonus` Speed+7, Stamina+7, Power+8, Guts+8 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 129 | [GameTora card 101902](https://gametora.com/umamusume/characters/101902-agnes-digital) |
| 35 | メイショウドトウ | Meisho Doto | Meisho Doto | [Both] | Turf A / Dirt E | Sprint G / Mile F / Medium A / Long A | Front Runner F / Pace Chaser A / Late Surger B / End Closer E | Spooky, Scary, Happy (Spooky-Scary-Happy) | SSR · [Dot-o'-Lantern] · debut [JP] 2022-09-29 · debut [Global] 2026-08-18 · `stat_bonus` Power+15, Wit+15 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 126 | [GameTora card 105802](https://gametora.com/umamusume/characters/105802-meisho-doto) |
| 36 | タマモクロス | Tamamo Cross | Tamamo Cross | [Both] | Turf A / Dirt F | Sprint G / Mile E / Medium A / Long A | Front Runner G / Pace Chaser A / Late Surger A / End Closer A | Lightning Flare (火神鳴) | SSR · [Raging Thunder] · debut [JP] 2022-10-28 · debut [Global] 2026-09-07 · `stat_bonus` Speed+14, Stamina+8, Guts+8 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 115 | [GameTora card 102102](https://gametora.com/umamusume/characters/102102-tamamo-cross) |
| 37 | イナリワン | Inari One | Inari One | [Both] | Turf A / Dirt A | Sprint F / Mile B / Medium A / Long A | Front Runner G / Pace Chaser B / Late Surger B / End Closer A | Firelight (灯穂) | SSR · [Golden Dream] · debut [JP] 2022-10-28 · debut [Global] 2026-09-07 · `stat_bonus` Speed+14, Power+8, Wit+8 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 120 | [GameTora card 103402](https://gametora.com/umamusume/characters/103402-inari-one) |
| 38 | ウオッカ | Vodka | Vodka (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint F / Mile A / Medium A / Long E | Front Runner C / Pace Chaser B / Late Surger A / End Closer F | Into High Gear! (Into High Gear!) | SSR · [Fiery Aqua Vitae] · debut [JP] 2022-11-28 · this card has no [Global] release · `stat_bonus` Speed+20, Guts+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 122 | [GameTora card 100802](https://gametora.com/umamusume/characters/100802-vodka) |
| 39 | ダイワスカーレット | Daiwa Scarlet | Daiwa Scarlet (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint F / Mile B / Medium A / Long A | Front Runner A / Pace Chaser A / Late Surger E / End Closer G | Queen's Lumination (Queen's Lumination) | SSR · [Nuit Étoilée de Scarlet] · debut [JP] 2022-11-28 · this card has no [Global] release · `stat_bonus` Speed+20, Wit+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 122 | [GameTora card 100902](https://gametora.com/umamusume/characters/100902-daiwa-scarlet) |
| 40 | ナリタブライアン | Narita Brian | Narita Brian (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint F / Mile B / Medium A / Long A | Front Runner G / Pace Chaser A / Late Surger A / End Closer D | Free From the Ashes (灰色の臨界点) | SSR · [Ravenous Wolf] · debut [JP] 2022-12-20 · this card has no [Global] release · `stat_bonus` Speed+10, Stamina+10, Guts+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 120 | [GameTora card 101602](https://gametora.com/umamusume/characters/101602-narita-brian) |
| 41 | サトノダイヤモンド | Satono Diamond | Satono Diamond (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile C / Medium A / Long A | Front Runner G / Pace Chaser B / Late Surger A / End Closer D | Rain Cloud Bolt (玄雲散らす、黄金甲矢) | SSR · [Jade's Prosperity] · debut [JP] 2022-12-29 · this card has no [Global] release · `stat_bonus` Speed+15, Stamina+15 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 118 | [GameTora card 106702](https://gametora.com/umamusume/characters/106702-satono-diamond) |
| 42 | キタサンブラック | Kitasan Black | Kitasan Black (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint E / Mile C / Medium A / Long A | Front Runner A / Pace Chaser B / Late Surger C / End Closer G | Bring on the Banquet! (あっぱれ大盤振る舞い！) | SSR · [Crane's Ambition] · debut [JP] 2022-12-29 · this card has no [Global] release · `stat_bonus` Speed+10, Power+10, Wit+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 122 | [GameTora card 106802](https://gametora.com/umamusume/characters/106802-kitasan-black) |
| 43 | メジロライアン | Mejiro Ryan | Mejiro Ryan (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint E / Mile C / Medium A / Long B | Front Runner F / Pace Chaser A / Late Surger A / End Closer F | A Warm Cup for You (あなたに捧げるフリーポア) | SSR · [Marguerite Latte] · debut [JP] 2023-01-30 · this card has no [Global] release · `stat_bonus` Speed+20, Wit+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 125 | [GameTora card 102702](https://gametora.com/umamusume/characters/102702-mejiro-ryan) |
| 44 | アイネスフウジン | Ines Fujin | Ines Fujin (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile A / Medium A / Long C | Front Runner A / Pace Chaser C / Late Surger G / End Closer G | Fresh☆Parlor (フレッシュ☆パーラー) | SSR · [Melt Your Heart] · debut [JP] 2023-01-30 · this card has no [Global] release · `stat_bonus` Speed+14, Guts+8, Wit+8 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 125 | [GameTora card 103102](https://gametora.com/umamusume/characters/103102-ines-fujin) |
| 45 | サクラチヨノオー | Sakura Chiyono O | Sakura Chiyono O (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint E / Mile A / Medium A / Long E | Front Runner B / Pace Chaser A / Late Surger F / End Closer G | Watch! Me! BLOOM! (咲け咲け！私！) | SSR · [Fleur Enneigée] · debut [JP] 2023-03-29 · this card has no [Global] release · `stat_bonus` Speed+8, Power+14, Wit+8 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 121 | [GameTora card 106902](https://gametora.com/umamusume/characters/106902-sakura-chiyono-o) |
| 46 | メジロアルダン | Mejiro Ardan | Mejiro Ardan (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt F | Sprint E / Mile B / Medium A / Long D | Front Runner C / Pace Chaser A / Late Surger D / End Closer G | Danser le Présent (Danser le présent) | SSR · [Neige Émeraude] · debut [JP] 2023-03-29 · this card has no [Global] release · `stat_bonus` Speed+8, Stamina+8, Wit+14 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 121 | [GameTora card 107102](https://gametora.com/umamusume/characters/107102-mejiro-ardan) |
| 47 | サクラバクシンオー | Sakura Bakushin O | Sakura Bakushin O (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint A / Mile B / Medium G / Long G | Front Runner A / Pace Chaser A / Late Surger F / End Closer G | Cherry☆Scramble (CHERRY☆スクランブル) | SSR · [Red Hot☆Leader] · debut [JP] 2023-04-28 · this card has no [Global] release · `stat_bonus` Speed+14, Power+8, Wit+8 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 131 | [GameTora card 104102](https://gametora.com/umamusume/characters/104102-sakura-bakushin-o) |
| 48 | マチカネタンホイザ | Matikanetannhauser | Matikanetannhauser (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile D / Medium A / Long A | Front Runner F / Pace Chaser A / Late Surger A / End Closer E | Tumbly Power Drive! (ごろりん！？パワードライブ) | SSR · [Blue Turbulence] · debut [JP] 2023-04-28 · this card has no [Global] release · `stat_bonus` Speed+10, Power+20 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 126 | [GameTora card 106202](https://gametora.com/umamusume/characters/106202-matikanetannhauser) |
| 49 | ヒシアマゾン | Hishi Amazon | Hishi Amazon (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt E | Sprint D / Mile A / Medium A / Long B | Front Runner G / Pace Chaser B / Late Surger C / End Closer A | First Bite of the Feast! (大盛り！ファーストバイト！) | SSR · [Hungry Veil] · debut [JP] 2023-05-29 · this card has no [Global] release · `stat_bonus` Speed+10, Power+10, Wit+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 121 | [GameTora card 101202](https://gametora.com/umamusume/characters/101202-hishi-amazon) |
| 50 | ニシノフラワー | Nishino Flower | Nishino Flower (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt F | Sprint A / Mile A / Medium E / Long G | Front Runner F / Pace Chaser A / Late Surger A / End Closer G | Flowering Dreams (Flowering Dreams) | SSR · [Sweet Juneberry] · debut [JP] 2023-05-29 · this card has no [Global] release · `stat_bonus` Speed+10, Wit+20 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 120 | [GameTora card 105102](https://gametora.com/umamusume/characters/105102-nishino-flower) |
| 51 | トーセンジョーダン | Tosen Jordan | Tosen Jordan (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile F / Medium A / Long B | Front Runner C / Pace Chaser A / Late Surger A / End Closer G | Us Girlies Keep Winnin'! ♪ (GALmem.ふぉーえば♪) | SSR · [Aurore☆Vacances] · debut [JP] 2023-06-29 · this card has no [Global] release · `stat_bonus` Speed+8, Stamina+8, Power+14 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 121 | [GameTora card 104802](https://gametora.com/umamusume/characters/104802-tosen-jordan) |
| 52 | バンブーメモリー | Bamboo Memory | Bamboo Memory (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt D | Sprint A / Mile A / Medium C / Long G | Front Runner G / Pace Chaser E / Late Surger A / End Closer C | Scorching Summer Tech! (奥義・常夏バーニング！！) | SSR · [Ultra☆Marine] · debut [JP] 2023-06-29 · this card has no [Global] release · `stat_bonus` Speed+14, Power+8, Guts+8 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 134 | [GameTora card 105302](https://gametora.com/umamusume/characters/105302-bamboo-memory) |
| 53 | サイレンススズカ | Silence Suzuka | Silence Suzuka (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint D / Mile A / Medium A / Long E | Front Runner A / Pace Chaser C / Late Surger E / End Closer G | Ahead of the Horizon (水平線のその先へ) | SSR · [Emerald Tidings] · debut [JP] 2023-07-31 · this card has no [Global] release · `stat_bonus` Speed+15, Stamina+15 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 123 | [GameTora card 100202](https://gametora.com/umamusume/characters/100202-silence-suzuka) |
| 54 | アグネスタキオン | Agnes Tachyon | Agnes Tachyon (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile D / Medium A / Long B | Front Runner E / Pace Chaser A / Late Surger B / End Closer F | Summer Halation (夏空ハレーション) | SSR · [Lunatic Lab] · debut [JP] 2023-07-31 · this card has no [Global] release · `stat_bonus` Speed+8, Power+8, Wit+14 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 122 | [GameTora card 103202](https://gametora.com/umamusume/characters/103202-agnes-tachyon) |
| 55 | ゴールドシップ | Gold Ship | Gold Ship (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile C / Medium A / Long A | Front Runner G / Pace Chaser B / Late Surger B / End Closer A | Vive la GOLD (Vive la GOLD) | SSR · La Mode 564 · debut [JP] 2023-08-31 · this card has no [Global] release · `stat_bonus` Speed+8, Stamina+14, Power+8 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 123 | [GameTora card 100703](https://gametora.com/umamusume/characters/100703-gold-ship) |
| 56 | サトノダイヤモンド | Satono Diamond | Satono Diamond (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile C / Medium A / Long A | Front Runner G / Pace Chaser B / Late Surger A / End Closer D | Ponte de Diamant (ポンテ・デ・ディアマン) | SSR · Chevalier Bleu · debut [JP] 2023-09-11 · this card has no [Global] release · `stat_bonus` Speed+8, Stamina+14, Guts+8 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 121 | [GameTora card 106703](https://gametora.com/umamusume/characters/106703-satono-diamond) |
| 57 | エアシャカール | Air Shakur | Air Shakur (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile E / Medium A / Long A | Front Runner G / Pace Chaser C / Late Surger A / End Closer A | ...found you. (...found you.) | SSR · Belphegor's Prime · debut [JP] 2023-09-29 · this card has no [Global] release · `stat_bonus` Stamina+14, Power+8, Wit+8 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 126 | [GameTora card 103602](https://gametora.com/umamusume/characters/103602-air-shakur) |
| 58 | シンボリクリスエス | Symboli Kris S | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile E / Medium A / Long A | Front Runner G / Pace Chaser A / Late Surger A / End Closer D | Immortal Work (Immortal Work) | SSR · Jetblack Automaton · debut [JP] 2023-09-29 · this card has no [Global] release · `stat_bonus` Speed+15, Power+15 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 125 | [GameTora card 108302](https://gametora.com/umamusume/characters/108302-symboli-kris-s) |
| 59 | トウカイテイオー | Tokai Teio | Tokai Teio (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint F / Mile E / Medium A / Long A | Front Runner D / Pace Chaser A / Late Surger C / End Closer E | Merrymaking Song and Dance (歌舞歓楽や、ああをかし) | SSR · Dream Butterfly of Purple Clouds · debut [JP] 2023-10-30 · this card has no [Global] release · `stat_bonus` Speed+10, Stamina+10, Wit+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 128 | [GameTora card 100303](https://gametora.com/umamusume/characters/100303-tokai-teio) |
| 60 | カワカミプリンセス | Kawakami Princess | Kawakami Princess (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint D / Mile B / Medium A / Long F | Front Runner G / Pace Chaser C / Late Surger A / End Closer D | Invigorating Strength (快なる剛力) | SSR · Suikan Beauty · debut [JP] 2023-10-30 · this card has no [Global] release · `stat_bonus` Stamina+8, Power+8, Guts+14 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 138 | [GameTora card 103902](https://gametora.com/umamusume/characters/103902-kawakami-princess) |
| 61 | メジロパーマー | Mejiro Palmer | Mejiro Palmer (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile F / Medium A / Long A | Front Runner A / Pace Chaser E / Late Surger F / End Closer G | Jingle All the Way (jingle all the way) | SSR · Warm-Hearted Reindeer · debut [JP] 2023-11-30 · this card has no [Global] release · `stat_bonus` Speed+10, Stamina+20 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 126 | [GameTora card 106402](https://gametora.com/umamusume/characters/106402-mejiro-palmer) |
| 62 | メジロブライト | Mejiro Bright | Mejiro Bright (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint F / Mile C / Medium A / Long A | Front Runner G / Pace Chaser D / Late Surger A / End Closer A | Illuminate You (Illuminate you) | SSR · Starry Snow Lolita · debut [JP] 2023-11-30 · this card has no [Global] release · `stat_bonus` Stamina+20, Wit+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 144 | [GameTora card 107402](https://gametora.com/umamusume/characters/107402-mejiro-bright) |
| 63 | キタサンブラック | Kitasan Black | Kitasan Black (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint E / Mile C / Medium A / Long A | Front Runner A / Pace Chaser B / Late Surger C / End Closer G | Becoming Everyone's Joy! (ミンナノアタシへ！) | SSR · Final Bloom · debut [JP] 2023-12-28 · this card has no [Global] release · `stat_bonus` Speed+10, Guts+20 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 128 | [GameTora card 106803](https://gametora.com/umamusume/characters/106803-kitasan-black) |
| 64 | ナイスネイチャ | Nice Nature | Nice Nature (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile C / Medium A / Long A | Front Runner F / Pace Chaser B / Late Surger A / End Closer D | Springy Festivities (もちっと・ハレハレ) | SSR · Converging Wishes · debut [JP] 2024-01-09 · this card has no [Global] release · `stat_bonus` Speed+14, Power+8, Wit+8 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 122 | [GameTora card 106003](https://gametora.com/umamusume/characters/106003-nice-nature) |
| 65 | マンハッタンカフェ | Manhattan Cafe | Manhattan Cafe (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile F / Medium B / Long A | Front Runner G / Pace Chaser C / Late Surger A / End Closer A | Purrfect Hospitality (心からのおもてにゃし) | SSR · Willow-Green Evening · debut [JP] 2024-01-31 · this card has no [Global] release · `stat_bonus` Stamina+20, Power+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 121 | [GameTora card 102502](https://gametora.com/umamusume/characters/102502-manhattan-cafe) |
| 66 | ユキノビジン | Yukino Bijin | Yukino Bijin (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt A | Sprint D / Mile A / Medium A / Long E | Front Runner C / Pace Chaser A / Late Surger F / End Closer G | Have Your Fill! (いっぱいおあげんしぇ！) | SSR · Snowy Girl of Tea and Cake · debut [JP] 2024-01-31 · this card has no [Global] release · `stat_bonus` Speed+8, Power+8, Guts+14 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 117 | [GameTora card 102902](https://gametora.com/umamusume/characters/102902-yukino-bijin) |
| 67 | ダイタクヘリオス | Daitaku Helios | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint B / Mile A / Medium B / Long E | Front Runner A / Pace Chaser A / Late Surger G / End Closer G | It's Mashup Time! (ノッてけ、マッシュアップ！) | SSR · Joyful Jamboree! · debut [JP] 2024-03-29 · this card has no [Global] release · `stat_bonus` Speed+8, Power+14, Guts+8 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 122 | [GameTora card 106502](https://gametora.com/umamusume/characters/106502-daitaku-helios) |
| 68 | ダイイチルビー | Daiichi Ruby | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint A / Mile A / Medium C / Long G | Front Runner E / Pace Chaser B / Late Surger A / End Closer A | Sapphire Flame (蒼炎) | SSR · Flowing Blue · debut [JP] 2024-03-29 · this card has no [Global] release · `stat_bonus` Speed+6, Power+12, Wit+12 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 122 | [GameTora card 108502](https://gametora.com/umamusume/characters/108502-daiichi-ruby) |
| 69 | ウイニングチケット | Winning Ticket | Winning Ticket (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile F / Medium A / Long B | Front Runner G / Pace Chaser B / Late Surger A / End Closer G | GO! Full-send (GO! Full-send) | SSR · Glorious Coat · debut [JP] 2024-04-09 · this card has no [Global] release · `stat_bonus` Speed+10, Stamina+10, Power+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 130 | [GameTora card 103503](https://gametora.com/umamusume/characters/103503-winning-ticket) |
| 70 | スーパークリーク | Super Creek | Super Creek (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile G / Medium A / Long A | Front Runner D / Pace Chaser A / Late Surger B / End Closer G | Ninja Art: Seal of the Smiling Heart (忍法・ほほえみ心結の印) | SSR · Flowery Shadow Amongst the Gentle Rain · debut [JP] 2024-04-30 · this card has no [Global] release · `stat_bonus` Speed+10, Stamina+13, Wit+7 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 135 | [GameTora card 104503](https://gametora.com/umamusume/characters/104503-super-creek) |
| 71 | ヤエノムテキ | Yaeno Muteki | Yaeno Muteki (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt E | Sprint G / Mile B / Medium A / Long E | Front Runner F / Pace Chaser A / Late Surger A / End Closer G | Decisive Blade of Blazing Fire (剛勇果断、烈火之刀) | SSR · Black General, Zen · debut [JP] 2024-04-30 · this card has no [Global] release · `stat_bonus` Stamina+6, Power+12, Guts+12 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 119 | [GameTora card 107202](https://gametora.com/umamusume/characters/107202-yaeno-muteki) |
| 72 | アグネスタキオン | Agnes Tachyon | Agnes Tachyon (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile D / Medium A / Long B | Front Runner E / Pace Chaser A / Late Surger B / End Closer F | Tachyon Potential (超光速微粒子の可能性) | SSR · Σ Experiment · debut [JP] 2024-05-10 · this card has no [Global] release · `stat_bonus` Speed+14, Power+8, Wit+8 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 122 | [GameTora card 103203](https://gametora.com/umamusume/characters/103203-agnes-tachyon) |
| 73 | スイープトウショウ | Sweep Tosho | Sweep Tosho (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint E / Mile A / Medium A / Long D | Front Runner G / Pace Chaser G / Late Surger A / End Closer A | Special Materialization (とっておきmaterialize) | SSR · Realize・Rune · debut [JP] 2024-05-30 · this card has no [Global] release · `stat_bonus` Speed+10, Power+10, Guts+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 125 | [GameTora card 104402](https://gametora.com/umamusume/characters/104402-sweep-tosho) |
| 74 | キングヘイロー | King Halo | King Halo (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint A / Mile A / Medium B / Long C | Front Runner G / Pace Chaser B / Late Surger A / End Closer D | The Winding Road to Ideals (理想へのwinding road) | SSR · Evergreen Identity · debut [JP] 2024-05-30 · this card has no [Global] release · `stat_bonus` Power+10, Guts+10, Wit+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 134 | [GameTora card 106103](https://gametora.com/umamusume/characters/106103-king-halo) |
| 75 | ライスシャワー | Rice Shower | Rice Shower (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint E / Mile C / Medium A / Long A | Front Runner B / Pace Chaser A / Late Surger C / End Closer G | Clear Sky Cooking♪ (あおぞらクッキング♪) | SSR · Yummy Dreamy Fairy · debut [JP] 2024-07-09 · this card has no [Global] release · `stat_bonus` Stamina+20, Guts+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 152 | [GameTora card 103003](https://gametora.com/umamusume/characters/103003-rice-shower) |
| 76 | エイシンフラッシュ | Eishin Flash | Eishin Flash (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile F / Medium A / Long A | Front Runner G / Pace Chaser B / Late Surger A / End Closer C | Geschenk of the Salty Sea Breeze (潮風のGeschenk) | SSR · Reines Plätschern · debut [JP] 2024-07-29 · this card has no [Global] release · `stat_bonus` Stamina+10, Guts+10, Wit+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 120 | [GameTora card 103703](https://gametora.com/umamusume/characters/103703-eishin-flash) |
| 77 | ホッコータルマエ | Hokko Tarumae | N/A (unit unreleased on [Global]) | [JP-Only] | Turf G / Dirt A | Sprint F / Mile A / Medium A / Long E | Front Runner B / Pace Chaser A / Late Surger G / End Closer G | Popping☆Clams (とびだせ☆ポッピングシェル) | SSR · Pastel Marine Locodol · debut [JP] 2024-07-29 · this card has no [Global] release · `stat_bonus` Speed+15, Stamina+10, Wit+5 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 119 | [GameTora card 109902](https://gametora.com/umamusume/characters/109902-hokko-tarumae) |
| 78 | ゼンノロブロイ | Zenno Rob Roy | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile E / Medium A / Long A | Front Runner G / Pace Chaser A / Late Surger A / End Closer E | Close Encounters of the Literary Kind (未知との遭遇、即ち物語) | SSR · Inlaid Stories · debut [JP] 2024-08-30 · this card has no [Global] release · `stat_bonus` Speed+10, Power+10, Wit+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 114 | [GameTora card 104702](https://gametora.com/umamusume/characters/104702-zenno-rob-roy) |
| 79 | ネオユニヴァース | Neo Universe | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint F / Mile B / Medium A / Long B | Front Runner F / Pace Chaser A / Late Surger A / End Closer C | Encounter With U (Encounter with U) | SSR · Like “ZEER” · debut [JP] 2024-08-30 · this card has no [Global] release · `stat_bonus` Power+7, Guts+10, Wit+13 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 127 | [GameTora card 110502](https://gametora.com/umamusume/characters/110502-neo-universe) |
| 80 | マヤノトップガン | Mayano Top Gun | Mayano Top Gun (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt E | Sprint D / Mile D / Medium A / Long A | Front Runner A / Pace Chaser A / Late Surger B / End Closer B | HOP STEP♪LOCK ON! (HOP STEP♪LOCK ON!) | SSR · Rocking☆MewMeow · debut [JP] 2024-09-30 · this card has no [Global] release · `stat_bonus` Speed+8, Stamina+15, Wit+7 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 138 | [GameTora card 102403](https://gametora.com/umamusume/characters/102403-mayano-top-gun) |
| 81 | シーキングザパール | Seeking the Pearl | Seeking the Pearl (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt F | Sprint A / Mile A / Medium E / Long G | Front Runner C / Pace Chaser A / Late Surger A / End Closer B | Oh!bento-magic☆ (Oh!bento-magic☆) | SSR · Be♪Witched · debut [JP] 2024-09-30 · this card has no [Global] release · `stat_bonus` Speed+10, Power+13, Wit+7 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 119 | [GameTora card 104202](https://gametora.com/umamusume/characters/104202-seeking-the-pearl) |
| 82 | ビワハヤヒデ | Biwa Hayahide | Biwa Hayahide (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt F | Sprint F / Mile C / Medium A / Long A | Front Runner E / Pace Chaser A / Late Surger B / End Closer E | Accumulative Victory (勝利ヘ至ル累積) | SSR · Engineered Victory · debut [JP] 2024-10-29 · this card has no [Global] release · `stat_bonus` Stamina+10, Power+10, Guts+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 128 | [GameTora card 102303](https://gametora.com/umamusume/characters/102303-biwa-hayahide) |
| 83 | ナリタタイシン | Narita Taishin | Narita Taishin (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint F / Mile D / Medium A / Long A | Front Runner G / Pace Chaser F / Late Surger B / End Closer A | Overdrive Speed (Overdrive Speed) | SSR · Stray Light Override · debut [JP] 2024-11-08 · this card has no [Global] release · `stat_bonus` Power+20, Wit+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 119 | [GameTora card 105003](https://gametora.com/umamusume/characters/105003-narita-taishin) |
| 84 | アドマイヤベガ | Admire Vega | Admire Vega (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint F / Mile C / Medium A / Long C | Front Runner G / Pace Chaser G / Late Surger B / End Closer A | Crystal Mist (Crystal Mist) | SSR · Glacialis Vega · debut [JP] 2024-11-28 · this card has no [Global] release · `stat_bonus` Speed+10, Power+20 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 124 | [GameTora card 103302](https://gametora.com/umamusume/characters/103302-admire-vega) |
| 85 | ナリタトップロード | Narita Top Road | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile C / Medium A / Long A | Front Runner F / Pace Chaser A / Late Surger B / End Closer D | Joy to the World (Joy to the World) | SSR · Celestial Road · debut [JP] 2024-11-28 · this card has no [Global] release · `stat_bonus` Stamina+10, Power+20 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 117 | [GameTora card 107702](https://gametora.com/umamusume/characters/107702-narita-top-road) |
| 86 | グラスワンダー | Grass Wonder | Grass Wonder (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile B / Medium A / Long A | Front Runner F / Pace Chaser A / Late Surger A / End Closer F | Naginata Maiden's Dance (演舞・撫子大薙刀) | SSR · Honor of the Azure Flame · debut [JP] 2024-12-19 · this card has no [Global] release · `stat_bonus` Guts+20, Wit+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 122 | [GameTora card 101103](https://gametora.com/umamusume/characters/101103-grass-wonder) |
| 87 | ミスターシービー | Mr. C.B. | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile B / Medium A / Long A | Front Runner G / Pace Chaser E / Late Surger A / End Closer A | Radiant Stride (爛然闊歩) | SSR · Dazzling Kabuki Flower · debut [JP] 2024-12-27 · this card has no [Global] release · `stat_bonus` Speed+20, Power+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 120 | [GameTora card 105702](https://gametora.com/umamusume/characters/105702-mr-cb) |
| 88 | カツラギエース | Katsuragi Ace | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint E / Mile B / Medium A / Long B | Front Runner A / Pace Chaser A / Late Surger E / End Closer G | Determined Stroke (決意一筆) | SSR · Pen Name: Ink Dragon · debut [JP] 2024-12-27 · this card has no [Global] release · `stat_bonus` Speed+5, Power+10, Guts+15 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 124 | [GameTora card 110402](https://gametora.com/umamusume/characters/110402-katsuragi-ace) |
| 89 | マルゼンスキー | Maruzensky | Maruzensky (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt D | Sprint B / Mile A / Medium B / Long C | Front Runner A / Pace Chaser E / Late Surger G / End Closer G | Miraculously Tubular Oracle (霊験灼然チョベリグ神託) | SSR · Auspicious Maiden of Divine Speed · debut [JP] 2025-01-10 · this card has no [Global] release · `stat_bonus` Speed+15, Power+15 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 123 | [GameTora card 100403](https://gametora.com/umamusume/characters/100403-maruzensky) |
| 90 | ヤマニンゼファー | Yamanin Zephyr | Yamanin Zephyr (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt D | Sprint B / Mile A / Medium A / Long G | Front Runner E / Pace Chaser A / Late Surger C / End Closer G | Breezy Treat (Breezy Treat) | SSR · Sugary Wind · debut [JP] 2025-01-31 · this card has no [Global] release · `stat_bonus` Power+15, Guts+15 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 124 | [GameTora card 107802](https://gametora.com/umamusume/characters/107802-yamanin-zephyr) |
| 91 | アストンマーチャン | Aston Machan | Aston Machan (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint A / Mile B / Medium G / Long G | Front Runner A / Pace Chaser A / Late Surger G / End Closer G | Fluffy Fuzzy Hour (ふわもこアワー) | SSR · Everlasting Sweet Treat · debut [JP] 2025-01-31 · this card has no [Global] release · `stat_bonus` Speed+10, Power+10, Guts+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 135 | [GameTora card 108702](https://gametora.com/umamusume/characters/108702-aston-machan) |
| 92 | タニノギムレット | Tanino Gimlet | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt F | Sprint F / Mile A / Medium A / Long F | Front Runner G / Pace Chaser D / Late Surger A / End Closer A | Terpsichore of the Abyss (深淵のテルプシコラー) | SSR · With a Twist · debut [JP] 2025-03-31 · this card has no [Global] release · `stat_bonus` Speed+10, Power+10, Guts+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 126 | [GameTora card 108402](https://gametora.com/umamusume/characters/108402-tanino-gimlet) |
| 93 | タップダンスシチー | Tap Dance City | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile E / Medium A / Long A | Front Runner A / Pace Chaser B / Late Surger F / End Closer G | Romantic Horizon (Romantic Horizon) | SSR · Tap! Tap! Tap! · debut [JP] 2025-03-31 · this card has no [Global] release · `stat_bonus` Speed+10, Guts+20 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 125 | [GameTora card 110702](https://gametora.com/umamusume/characters/110702-tap-dance-city) |
| 94 | シリウスシンボリ | Sirius Symboli | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile B / Medium A / Long C | Front Runner E / Pace Chaser A / Late Surger A / End Closer E | Driven by Light, the Heavenly Wolf Hunts (駆るは光、狩るは星々) | SSR · Louve Stellaire · debut [JP] 2025-04-10 · this card has no [Global] release · `stat_bonus` Power+10, Guts+20 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 126 | [GameTora card 107002](https://gametora.com/umamusume/characters/107002-sirius-symboli) |
| 95 | スマートファルコン | Smart Falcon | Smart Falcon (unit live on [Global], this card is not) | [JP-Only] | Turf E / Dirt A | Sprint B / Mile A / Medium A / Long E | Front Runner A / Pace Chaser D / Late Surger G / End Closer G | Forward March! (Forward March!) | SSR · Luminous ☆ Twirler · debut [JP] 2025-04-30 · this card has no [Global] release · `stat_bonus` Stamina+10, Power+10, Guts+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 123 | [GameTora card 104603](https://gametora.com/umamusume/characters/104603-smart-falcon) |
| 96 | コパノリッキー | Copano Rickey | Copano Rickey (unit live on [Global], this card is not) | [JP-Only] | Turf F / Dirt A | Sprint C / Mile A / Medium A / Long G | Front Runner A / Pace Chaser A / Late Surger C / End Closer G | Parade of the Five Heavenly Beasts (五獣挙りて彩光奏づ) | SSR · Dazzling ☆ Lucky Wear · debut [JP] 2025-04-30 · this card has no [Global] release · `stat_bonus` Stamina+10, Power+5, Guts+10, Wit+5 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 115 | [GameTora card 109802](https://gametora.com/umamusume/characters/109802-copano-rickey) |
| 97 | メジロラモーヌ | Mejiro Ramonu | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt F | Sprint B / Mile A / Medium A / Long E | Front Runner G / Pace Chaser A / Late Surger A / End Closer F | Everlasting Knot (解けぬ結い目) | SSR · Untouchable Eden · debut [JP] 2025-05-30 · this card has no [Global] release · `stat_bonus` Power+15, Guts+5, Wit+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 121 | [GameTora card 108602](https://gametora.com/umamusume/characters/108602-mejiro-ramonu) |
| 98 | シーザリオ | Cesario | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile A / Medium A / Long F | Front Runner G / Pace Chaser A / Late Surger A / End Closer C | Perennial Babbles (時かけるせせらぎ) | SSR · Twinbell Queen · debut [JP] 2025-05-30 · this card has no [Global] release · `stat_bonus` Power+10, Guts+20 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 122 | [GameTora card 111002](https://gametora.com/umamusume/characters/111002-cesario) |
| 99 | ゴールドシチー | Gold City | Gold City (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt D | Sprint F / Mile B / Medium A / Long A | Front Runner F / Pace Chaser A / Late Surger A / End Closer F | Silent Sunset Gold (Silent Sunset Gold) | SSR · Boho Flare · debut [JP] 2025-06-27 · this card has no [Global] release · `stat_bonus` Stamina+10, Guts+15, Wit+5 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 125 | [GameTora card 104003](https://gametora.com/umamusume/characters/104003-gold-city) |
| 100 | アイネスフウジン | Ines Fujin | Ines Fujin (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile A / Medium A / Long C | Front Runner A / Pace Chaser C / Late Surger G / End Closer G | Full-Throttle Cyclone! (全力全開！サイクロン) | SSR · Hello Hello Island · debut [JP] 2025-07-11 · this card has no [Global] release · `stat_bonus` Speed+12, Power+10, Guts+8 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 126 | [GameTora card 103103](https://gametora.com/umamusume/characters/103103-ines-fujin) |
| 101 | ラインクラフト | Rhein Kraft | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint B / Mile A / Medium A / Long G | Front Runner E / Pace Chaser A / Late Surger C / End Closer G | Shine on Forever (ずっとずっと輝いて) | SSR · Eternal Fairytale · debut [JP] 2025-07-22 · this card has no [Global] release · `stat_bonus` Speed+5, Power+15, Wit+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 120 | [GameTora card 110902](https://gametora.com/umamusume/characters/110902-rhein-kraft) |
| 102 | サトノクラウン | Satono Crown | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile B / Medium A / Long E | Front Runner G / Pace Chaser B / Late Surger A / End Closer D | Summer Thunder Cascade! (夏雷カスケード！) | SSR · Sunny Island Floral Beauty · debut [JP] 2025-07-31 · this card has no [Global] release · `stat_bonus` Stamina+10, Power+6, Guts+14 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 120 | [GameTora card 108802](https://gametora.com/umamusume/characters/108802-satono-crown) |
| 103 | シュヴァルグラン | Cheval Grand | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile G / Medium A / Long A | Front Runner G / Pace Chaser A / Late Surger B / End Closer F | Tidebreaker (Tidebreaker) | SSR · Summer Lull Navy Drop · debut [JP] 2025-07-31 · this card has no [Global] release · `stat_bonus` Stamina+10, Guts+20 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 131 | [GameTora card 108902](https://gametora.com/umamusume/characters/108902-cheval-grand) |
| 104 | ヴィブロス | Vivlos | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint E / Mile A / Medium A / Long G | Front Runner G / Pace Chaser D / Late Surger A / End Closer C | Je t'aime ☆ Vacances (ジュ・テーム☆バカンス！) | SSR · Éclat d'été · debut [JP] 2025-08-14 · this card has no [Global] release · `stat_bonus` Speed+10, Guts+10, Wit+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 120 | [GameTora card 109102](https://gametora.com/umamusume/characters/109102-vivlos) |
| 105 | ケイエスミラクル | K.S.Miracle | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint A / Mile B / Medium G / Long G | Front Runner E / Pace Chaser A / Late Surger B / End Closer C | Miracle, Hopes, Melody (きせき・おもい・かなで) | SSR · Andante in Autumn Hues · debut [JP] 2025-08-29 · this card has no [Global] release · `stat_bonus` Speed+15, Power+15 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 128 | [GameTora card 109302](https://gametora.com/umamusume/characters/109302-ksmiracle) |
| 106 | ヒシミラクル | Hishi Miracle | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile G / Medium A / Long A | Front Runner G / Pace Chaser C / Late Surger A / End Closer B | A Small Miracle for You♪ (小さな奇跡、フォーユー♪) | SSR · Happy Little Notes · debut [JP] 2025-08-29 · this card has no [Global] release · `stat_bonus` Stamina+14, Power+8, Guts+8 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 125 | [GameTora card 110602](https://gametora.com/umamusume/characters/110602-hishi-miracle) |
| 107 | ヒシアケボノ | Hishi Akebono | Hishi Akebono (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt F | Sprint A / Mile B / Medium F / Long G | Front Runner B / Pace Chaser A / Late Surger C / End Closer G | Sugar Parade: Start! (シュガーパレード・始動！) | SSR · Magic Night Garland · debut [JP] 2025-09-29 · this card has no [Global] release · `stat_bonus` Power+30 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 138 | [GameTora card 102802](https://gametora.com/umamusume/characters/102802-hishi-akebono) |
| 108 | マーベラスサンデー | Marvelous Sunday | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt F | Sprint G / Mile C / Medium A / Long B | Front Runner G / Pace Chaser A / Late Surger A / End Closer C | Bravo ☆ Wonder Doll! (喝采☆ワンダードール！) | SSR · Trick ☆ Ribonetta · debut [JP] 2025-09-29 · this card has no [Global] release · `stat_bonus` Stamina+16, Power+7, Guts+7 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 117 | [GameTora card 105502](https://gametora.com/umamusume/characters/105502-marvelous-sunday) |
| 109 | トーセンジョーダン | Tosen Jordan | Tosen Jordan (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile F / Medium A / Long B | Front Runner C / Pace Chaser A / Late Surger A / End Closer G | Sick Moves ☆ Juggle Beat (ヤバ技☆ジャグルビート) | SSR · Highkey ☆ Hallowed · debut [JP] 2025-10-10 · this card has no [Global] release · `stat_bonus` Stamina+10, Power+10, Guts+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 118 | [GameTora card 104803](https://gametora.com/umamusume/characters/104803-tosen-jordan) |
| 110 | トランセンド | Transcend | N/A (unit unreleased on [Global]) | [JP-Only] | Turf F / Dirt A | Sprint G / Mile A / Medium A / Long G | Front Runner A / Pace Chaser B / Late Surger F / End Closer G | Geothermal Overdrive (地熱解放オーヴァードライブ) | SSR · Twilit Neo-Kagura · debut [JP] 2025-10-29 · this card has no [Global] release · `stat_bonus` Power+15, Wit+15 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 117 | [GameTora card 108002](https://gametora.com/umamusume/characters/108002-transcend) |
| 111 | ワンダーアキュート | Wonder Acute | Wonder Acute (unit live on [Global], this card is not) | [JP-Only] | Turf G / Dirt A | Sprint D / Mile A / Medium A / Long E | Front Runner C / Pace Chaser A / Late Surger B / End Closer E | Bathkeeper's Heart of Zen (湯守の和心) | SSR · Soft Light・Hot Spring Scent · debut [JP] 2025-11-10 · this card has no [Global] release · `stat_bonus` Stamina+10, Power+10, Guts+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 119 | [GameTora card 110002](https://gametora.com/umamusume/characters/110002-wonder-acute) |
| 112 | ナカヤマフェスタ | Nakayama Festa | Nakayama Festa (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile C / Medium A / Long B | Front Runner G / Pace Chaser A / Late Surger A / End Closer D | Wager on the Holy Night Stars (運否、聖夜の星に賭ける) | SSR · Festive Play · debut [JP] 2025-11-28 · this card has no [Global] release · `stat_bonus` Speed+10, Power+10, Guts+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 121 | [GameTora card 104902](https://gametora.com/umamusume/characters/104902-nakayama-festa) |
| 113 | ドリームジャーニー | Dream Journey | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint F / Mile C / Medium A / Long A | Front Runner G / Pace Chaser G / Late Surger A / End Closer A | Dreaming of the Silver World (夢寐に見る銀世界) | SSR · Snow-White Dream Road · debut [JP] 2025-11-28 · this card has no [Global] release · `stat_bonus` Speed+20, Stamina+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 114 | [GameTora card 111902](https://gametora.com/umamusume/characters/111902-dream-journey) |
| 114 | オグリキャップ | Oguri Cap | Oguri Cap (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt B | Sprint E / Mile A / Medium B / Long A | Front Runner F / Pace Chaser A / Late Surger A / End Closer D | Ashen Trail: Cinderella Gray (灰の光跡：シンデレラグレイ) | SSR · Cinderella Gray · debut [JP] 2025-12-11 · this card has no [Global] release · `stat_bonus` Speed+8, Power+8, Guts+14 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 124 | [GameTora card 100603](https://gametora.com/umamusume/characters/100603-oguri-cap) |
| 115 | イクノディクタス | Ikuno Dictus | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint D / Mile A / Medium A / Long D | Front Runner D / Pace Chaser A / Late Surger A / End Closer D | Spinning Top of Longevity (千歳、廻りて) | SSR · Strolling an Orderly Path · debut [JP] 2025-12-26 · this card has no [Global] release · `stat_bonus` Stamina+10, Power+10, Guts+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 120 | [GameTora card 106302](https://gametora.com/umamusume/characters/106302-ikuno-dictus) |
| 116 | サクラローレル | Sakura Laurel | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt E | Sprint G / Mile C / Medium A / Long A | Front Runner G / Pace Chaser B / Late Surger A / End Closer B | Spring Light: Bon Appétit (春光・Bon appétit) | SSR · Nouvelle Pousse · debut [JP] 2025-12-26 · this card has no [Global] release · `stat_bonus` Stamina+10, Guts+20 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 127 | [GameTora card 107602](https://gametora.com/umamusume/characters/107602-sakura-laurel) |
| 117 | バブルガムフェロー | Bubble Gum Fellow | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile A / Medium A / Long G | Front Runner E / Pace Chaser A / Late Surger B / End Closer G | Clear the Haze, Seize the Morning (暁霞を拓き、朝を掴む) | SSR · Dawn Light · debut [JP] 2026-01-08 · this card has no [Global] release · `stat_bonus` Power+10, Guts+20 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 119 | [GameTora card 112402](https://gametora.com/umamusume/characters/112402-bubble-gum-fellow) |
| 118 | タイキシャトル | Taiki Shuttle | Taiki Shuttle (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt B | Sprint A / Mile A / Medium E / Long G | Front Runner C / Pace Chaser A / Late Surger E / End Closer G | Have S'more Love! (Have S'more Love!) | SSR · Baa Baa Patisserie · debut [JP] 2026-01-30 · this card has no [Global] release · `stat_bonus` Power+10, Guts+20 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 127 | [GameTora card 101003](https://gametora.com/umamusume/characters/101003-taiki-shuttle) |
| 119 | サウンズオブアース | Sounds of Earth | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile F / Medium A / Long A | Front Runner G / Pace Chaser A / Late Surger A / End Closer E | Che Piacevole! (ケ・ピアチェーヴォレ！) | SSR · Sonata alla Menta · debut [JP] 2026-01-30 · this card has no [Global] release · `stat_bonus` Stamina+14, Power+8, Guts+8 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 117 | [GameTora card 110202](https://gametora.com/umamusume/characters/110202-sounds-of-earth) |
| 120 | ラヴズオンリーユー | Loves Only You | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile C / Medium A / Long F | Front Runner G / Pace Chaser A / Late Surger A / End Closer G | Watch Us Flourish (Watch Us Flourish) | SSR · Love-Linked Aster · debut [JP] 2026-03-11 · this card has no [Global] release · `stat_bonus` Stamina+10, Guts+10, Wit+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 117 | [GameTora card 113202](https://gametora.com/umamusume/characters/113202-loves-only-you) |
| 121 | サクラバクシンオー | Sakura Bakushin O | Sakura Bakushin O (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint A / Mile B / Medium G / Long G | Front Runner A / Pace Chaser A / Late Surger F / End Closer G | Sakura Storm Spring Burst (桜吹雪くスプリントバースト) | SSR · Spring-Thunder Speedster · debut [JP] 2026-03-30 · this card has no [Global] release · `stat_bonus` Speed+10, Power+15, Wit+5 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 125 | [GameTora card 104103](https://gametora.com/umamusume/characters/104103-sakura-bakushin-o) |
| 122 | ノースフライト | North Flight | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint C / Mile A / Medium B / Long G | Front Runner D / Pace Chaser A / Late Surger D / End Closer A | Sparkling Arrow Line (煌めき華やぐアローライン) | SSR · Pale-Blue Flash · debut [JP] 2026-03-30 · this card has no [Global] release · `stat_bonus` Speed+15, Wit+15 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 119 | [GameTora card 108202](https://gametora.com/umamusume/characters/108202-north-flight) |
| 123 | フジキセキ | Fuji Kiseki | Fuji Kiseki (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt F | Sprint B / Mile A / Medium A / Long E | Front Runner C / Pace Chaser A / Late Surger C / End Closer G | Gloire à toi! (Gloire à toi!) | SSR · Blanche Étoile · debut [JP] 2026-04-30 · this card has no [Global] release · `stat_bonus` Stamina+10, Guts+13, Wit+7 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 121 | [GameTora card 100503](https://gametora.com/umamusume/characters/100503-fuji-kiseki) |
| 124 | ジャングルポケット | Jungle Pocket | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile C / Medium A / Long B | Front Runner G / Pace Chaser D / Late Surger A / End Closer B | Twilight Blood Pact (黄昏の血盟) | SSR · Vermilion Head · debut [JP] 2026-04-30 · this card has no [Global] release · `stat_bonus` Stamina+7, Power+10, Guts+13 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 119 | [GameTora card 109402](https://gametora.com/umamusume/characters/109402-jungle-pocket) |
| 125 | マンハッタンカフェ | Manhattan Cafe | Manhattan Cafe (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile F / Medium B / Long A | Front Runner G / Pace Chaser C / Late Surger A / End Closer C | In Times You're Wandering the Night (迷える時も、幽かなる時も) | SSR · Ethereal Rose · debut [JP] 2026-05-29 · this card has no [Global] release · `stat_bonus` Stamina+5, Power+10, Guts+5, Wit+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 117 | [GameTora card 102503](https://gametora.com/umamusume/characters/102503-manhattan-cafe) |
| 126 | ジェンティルドンナ | Gentildonna | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile A / Medium A / Long A | Front Runner E / Pace Chaser A / Late Surger A / End Closer D | Unwithering Cattleya (不凋なるCattleya) | SSR · La dama perfetta · debut [JP] 2026-05-29 · this card has no [Global] release · `stat_bonus` Power+10, Guts+10, Wit+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 126 | [GameTora card 111602](https://gametora.com/umamusume/characters/111602-gentildonna) |
| 127 | ナリタトップロード | Narita Top Road | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile C / Medium A / Long A | Front Runner F / Pace Chaser A / Late Surger B / End Closer D | One Bowl, Full Power Delivery! (一杯全力、お届けします！) | SSR · Bountiful Dragon Broth Saga · debut [JP] 2026-06-29 · this card has no [Global] release · `stat_bonus` Stamina+5, Power+10, Wit+15 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 130 | [GameTora card 107703](https://gametora.com/umamusume/characters/107703-narita-top-road) |
| 128 | ファインモーション | Fine Motion | Fine Motion (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint F / Mile A / Medium A / Long C | Front Runner D / Pace Chaser A / Late Surger E / End Closer C | Graceful Dash: The Way of Ramen (麗走一直！ラーメン道) | SSR · Radiant Noodle Splendor Saga · debut [JP] 2026-07-10 · this card has no [Global] release · `stat_bonus` Power+10, Guts+10, Wit+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 117 | [GameTora card 102203](https://gametora.com/umamusume/characters/102203-fine-motion) |
| 129 | セイウンスカイ | Seiun Sky | Seiun Sky (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile C / Medium A / Long A | Front Runner A / Pace Chaser B / Late Surger D / End Closer E | Let the Sea Breeze Carry Me (海風にまったり身を任せ) | SSR · Shoreline Whimsy in Orange · debut [JP] 2026-07-30 · this card has no [Global] release · `stat_bonus` Power+5, Guts+10, Wit+15 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 118 | [GameTora card 102003](https://gametora.com/umamusume/characters/102003-seiun-sky) |
| 130 | メジロブライト | Mejiro Bright | Mejiro Bright (unit live on [Global], this card is not) | [JP-Only] | Turf A / Dirt G | Sprint F / Mile C / Medium A / Long A | Front Runner G / Pace Chaser D / Late Surger A / End Closer A | Evening Calm Retreat Memory (夕凪のリトリート・メモリー) | SSR · Seaside Elegance in Chiffon · debut [JP] 2026-07-30 · this card has no [Global] release · `stat_bonus` Speed+5, Stamina+10, Guts+5, Wit+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 118 | [GameTora card 107403](https://gametora.com/umamusume/characters/107403-mejiro-bright) |
| 131 | フサイチパンドラ | Fusaichi Pandora | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt E | Sprint G / Mile B / Medium A / Long G | Front Runner C / Pace Chaser A / Late Surger B / End Closer F | Beachside Venus Time♡ (渚のヴィーナスタイム♡) | SSR · Snatchin' Hearts ♡ · debut [JP] 2026-08-14 · this card has no [Global] release · `stat_bonus` Power+14, Guts+8, Wit+8 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 121 | [GameTora card 111302](https://gametora.com/umamusume/characters/111302-fusaichi-pandora) |
| 132 | シンコウウインディ | Shinko Windy | N/A (unit unreleased on [Global]) | [JP-Only] | Turf F / Dirt A | Sprint C / Mile A / Medium B / Long G | Front Runner G / Pace Chaser A / Late Surger B / End Closer F | The Scariest Prank Ever♪ (最恐！いたずら計画なのだ♪) | SSR · Chomp-Chomp ☆ Scamp · debut [JP] 2026-08-31 · this card has no [Global] release · `stat_bonus` Speed+8, Power+8, Guts+14 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 120 | [GameTora card 104302](https://gametora.com/umamusume/characters/104302-shinko-windy) |
| 133 | フェノーメノ | Fenomeno | N/A (unit unreleased on [Global]) | [JP-Only] | Turf A / Dirt G | Sprint G / Mile G / Medium A / Long A | Front Runner C / Pace Chaser A / Late Surger E / End Closer G | Ironclad Guardian (剛心鉄壁ガーディアン) | SSR · Violet Flame of Fortitude · debut [JP] 2026-08-31 · this card has no [Global] release · `stat_bonus` Speed+10, Stamina+10, Guts+10 (meaning unconfirmed, see 1.3.5) · alternate costume card · ★5 ceiling 133 | [GameTora card 112702](https://gametora.com/umamusume/characters/112702-fenomeno) |

### 3.3 How these tables were built, and what that means for trust

- Every cell in 3.1 and 2.2 comes from one machine-readable export of the GameTora database (tier B), fetched 2026-09-27: `character-cards.json` for aptitudes, rarity, epithet, unique skill ids and per-server dates; `characters.json` for the `[Global]` availability flag; `skills.json` for skill names. No row was typed by hand, which is the point: a 268-row table entered by hand is where invented aptitude letters would hide.
- The ten-element aptitude array order was treated as unverified until proven. It was decoded against the rendered GameTora unit pages and against two independent A-tier wikis for three diagnostic units chosen to be awkward rather than easy, 30 of 30 cells agreeing. Order: Turf, Dirt, Sprint, Mile, Medium, Long, then the four strategies. GameTora's own unit page prints `Short` where the `[Global]` client prints `Sprint`, and `Medium` for the same band, so the letters are the reliable part, not the labels.
- Naming discipline: the Global EN Name column holds an official localized name only where the *unit* has shipped on `[Global]`. GameTora publishes English renderings for `[JP-Only]` units too, and those are tier B translations rather than announced localizations, so an unshipped unit's row reads `N/A (unit unreleased on [Global])`. Where the unit is live but this card is not, the name stays and the cell says so in those words, because the row's `[JP-Only]` status describes the card. Romanized Name carries the same rendering.
- Level discipline, and a defect that was found and fixed: an earlier build of this table decided Server Status from the unit flag while listing costume cards, which tagged 61 cards that never shipped on `[Global]` as `[Both]`, and then certified zero disagreements because the check was run at that same wrong level. Status is now decided per card, which yields 68 `[Both]` of 135 debut rows in 3.1 and 37 `[Both]` of 133 variant rows in 2.2. 0 debut rows disagree between the unit flag and the card date, which is why the two levels are stated separately rather than collapsed into one token.

### 3.4 Independent cross-check of the roster data

Independent verification of the GameTora-derived aptitude array order and the server-availability claims. Freshness anchor 2026-09-27. Verification only; no roster table is authored here.

#### Task 1 Verdict

CONFIRMED. The claimed order survives: index 0 Turf, 1 Dirt, 2 Sprint, 3 Mile, 4 Medium, 5 Long, 6 Front, 7 Pace, 8 Late, 9 End.

No correction to the order. One wording note, non-blocking: the rendered GameTora unit page prints index 2 as "Short" and index 4 as "Medium", while the GameTora filter row and game8.co print "Sprint" and "Med". Same slots, same letters, different label text. The order in the claim is the order the pages print.

Decisive evidence: on the rendered GameTora unit page each letter is an image adjacent to its own label in the accessibility tree (`img "A"` next to `Turf`, `img "G"` next to `Dirt`, and so on), so the pairing is not inferred from array position. The Japanese order printed by kamigame.jp, 芝 / ダート / 短距離 / マイル / 中距離 / 長距離 / 逃げ / 先行 / 差し / 追込, is the same sequence slot for slot, so the strategy block (Front, Pace, Late, End) is confirmed against a source that does not share GameTora's data pipeline.

Selected units are diagnostic by design: two carry Dirt A against Turf E or Turf G, and one differs across all four strategy slots, so any permutation of the last three or first two elements would show as a mismatch.

#### Task 1 Comparison Table

| Unit | Server tag | GameTora rendered unit page (tier B) | Independent site (tier A) | Export array | Agreement |
|---|---|---|---|---|---|
| 100101 Special Week / スペシャルウィーク | [Both] | Turf A, Dirt G, Short F, Mile C, Medium A, Long A, Front G, Pace A, Late A, End C | game8.co "Special Week (Ruler of Japan)": Turf A, Dirt G, Sprint F, Mile C, Med A, Long A, Front G, Pace A, Late A, End C | ["A","G","F","C","A","A","G","A","A","C"] | 10 of 10 |
| 104601 Smart Falcon / スマートファルコン, Dirt A against Turf E | [Both] | Turf E, Dirt A, Short B, Mile A, Medium A, Long E, Front A, Pace D, Late G, End G | kamigame.jp: 芝 E, ダート A, 短距離 B, マイル A, 中距離 A, 長距離 E, 逃げ A, 先行 D, 差し G, 追込 G | ["E","A","B","A","A","E","A","D","G","G"] | 10 of 10 |
| 110001 Wonder Acute / ワンダーアキュート, [Global] release_en 2026-09-24, Dirt A against Turf G | [Global] for the 2026 release date, [Both] for the aptitude read | Turf G, Dirt A, Short D, Mile A, Medium A, Long E, Front C, Pace A, Late C, End E | kamigame.jp: 芝 G, ダート A, 短距離 D, マイル A, 中距離 A, 長距離 E, 逃げ C, 先行 A, 差し C, 追込 E | ["G","A","D","A","A","E","C","A","C","E"] | 10 of 10 |

Units tested: 3. All 3 agreed, 30 of 30 cells. Two independent sources per unit, and the two independent sites used are different properties (game8.co English, kamigame.jp Japanese).

#### Task 2 Server Availability Table

Export figures read from `GameTora data export` ([manifest](https://gametora.com/data/manifests/umamusume.json)): `GameTora data export` ([manifest](https://gametora.com/data/manifests/umamusume.json)): 268 card rows, 163 character rows.

| Claim | Independent support found | Status |
|---|---|---|
| 135 trainable Umamusume on [JP] | Export is internally consistent: 135 distinct `char_id` carry `playable` true, of 163 character rows. No external per-character tally located. | Could not confirm |
| 68 of those playable on [Global] | Export is internally consistent: 68 distinct `char_id` carry `playable_en` true, and exactly 68 distinct `char_id` own a card with a `release_en` value, with zero characters flagged `playable_en` but lacking one. The game8.co global trainee list rendered 104 entries; that is a per-card-variant tally and it lines up with the 105 export cards carrying `release_en`, not with 68. | Count 68 could not be confirmed by an independent tally; the card-level figure was corroborated at 104 against 105 |
| [Global] first opened in 2025 | Export `release_en` minimum is 2025-06-26 across 105 cards. Cygames' official English news item "Umamusume: Pretty Derby English Version Now Available" is indexed with the date 2025-06-26; a direct fetch of that page returned HTTP 403. | SUPPORTED at 2025-06-26 by the export minimum across 105 cards; the official page was not readable (HTTP 403), so this is one readable source plus an indexed title rather than a quoted announcement, with the official-page date taken from the search index rather than the page body |
| Per-card `release` for [JP] and `release_en` for [Global] | Export ranges: `release` 2021-02-24 to 2026-09-18, `release_en` 2025-06-26 to 2026-09-24. 105 of 268 cards carry `release_en`. The [JP] start of the range matches the [JP] launch and the [Global] start matches the confirmed 2025 opening. | Field structure and range endpoints consistent; individual dates not checked against announcements |

Counts I can support from an independent source: the [Global] opening window, 2025, date 2025-06-26. Counts I cannot support: 135 [JP] trainable and 68 [Global] playable.

#### Unresolved

- 135 trainable on [JP]: no source outside the export gives a per-character tally. Neither official news index was navigated in this session, so the [JP] roster size stands unverified.
- 68 playable on [Global]: the same gap. Every external list located is organized by card variant, so it cannot confirm or refute a distinct-character count.
- Official pages not read: `https://umamusume.com/news/` and `https://umamusume.jp/news/?t=game` were not rendered in this session, and `https://www.cygames.co.jp/en/news/id-24452` returned HTTP 403. The 2025-06-26 [Global] opening therefore rests on the export plus a search-index date rather than official page text.
- Variant identity for 100101: game8.co's "Special Week (Ruler of Japan)" was matched to card 100101 because the GameTora page for 100101 lists that epithet, not because either site publishes a shared card id. All ten letters agree, which is strong but not an id-level match.
- Release dates for individual 2026 [Global] cards, including 110001 at release_en 2026-09-24, were not verified against any announcement.
- Slots 2 and 4 label text ("Short"/"Sprint", "Medium"/"Med") differs between GameTora's unit page and its filter row. Document the wording you adopt and keep the index positions as confirmed.
- The export files carry no snapshot timestamp, so drift between the export and the live GameTora pages as of 2026-09-27 cannot be measured. Every card released on [JP] after the 2026-09-18 latest `release` value, and on [Global] after 2026-09-24, would be missing from the 268 rows, which is a plausible source of the count gaps above rather than evidence of a wrong count.
- Confirmation is limited to three units out of 268 rows. It settles the element order, which is order-invariant across rows, and it does not certify any individual letter beyond those three cards.

**Sources:**
- https://gametora.com/umamusume/characters/100101-special-week (tier B, rendered with playwright; label and letter pairs read from the accessibility tree)
- https://gametora.com/umamusume/characters/104601-smart-falcon (tier B, rendered with playwright)
- https://gametora.com/umamusume/characters/110001-wonder-acute (tier B, rendered with playwright)
- https://gametora.com/umamusume/characters (tier B, filter labels behind the claimed order)
- https://game8.co/games/Umamusume-Pretty-Derby/archives/603281 (tier A, English, Special Week aptitudes)
- https://game8.co/games/Umamusume-Pretty-Derby/archives/535926 (tier A, English, global trainee list, per-variant tally)
- https://kamigame.jp/umamusume/page/158169296764198340.html (tier A, Japanese, Smart Falcon aptitudes)
- https://kamigame.jp/umamusume/page/237444350693387352.html (tier A, Japanese, Wonder Acute aptitudes)
- https://www.cygames.co.jp/en/news/id-24452 (official English news item, HTTP 403 on direct fetch)
- https://umamusume.com/news/ (official [Global] news index, not navigated this session)
- https://umamusume.jp/news/?t=game (official [JP] news index, not navigated this session)
- [characters dataset](https://gametora.com/data/umamusume/characters.731ee674.json) and [character-cards dataset](https://gametora.com/data/umamusume/character-cards.679f7c2e.json) (tier B data export under test)

### 2.5 Whole-roster cross-check against a second domain

The three-unit aptitude spot check in 3.4 proves the column decoding. It does not prove the roster is the right *set* of characters. That question needs a whole-list comparison, so the `[JP]` trainable-character index was extracted independently from a different publisher and diffed name by name against the export.

Source: [Kamigame 育成キャラ一覧, page stamped 最終更新日 2026-09-18 13:21](https://kamigame.jp/umamusume/page/110667391372886023.html) (tier A), read as server-rendered HTML, 268 card rows. Kamigame marks costume variants with a parenthetical suffix on the unit name, so those suffixes were stripped to reach unit level, which is the level 2.1 is written at.

| Measure | GameTora data export (tier B) | Kamigame (tier A) | Agreement |
|---|---|---|---|
| Trainable costume-card rows | 268 | 268 | exact |
| Distinct trainable Umamusume | 135 | 135 | exact |
| Names present on one side only | 0 | 0 | set equality holds |

Two naming anomalies surfaced and resolved rather than ignored: two Kamigame rows carry the identical unit name `マルゼンスキー` because the variant marker sits only in the card title, and one variant `ゼンノロブロイ（お月見）` exists solely as a parenthetical. Both collapse correctly at unit level and neither changes the count.

What this test does **not** upgrade, stated plainly: Kamigame's index has no `[Global]` availability column and no implementation dates, contributing zero Global evidence across its 268 rows. So the 68 units flagged playable on `[Global]`, and every `debut [Global]` date in 3.1 and 2.2, still rest on tier B data plus the official-announcement checks in 3.4 and in Sections 4 and 5. A third candidate domain, [Game8's English Trainee List](https://game8.co/games/Umamusume-Pretty-Derby/archives/535926), was examined for this test and rejected as a roster-set control because it publishes neither katakana names nor implementation dates; it remains a legitimate source for English naming, which is how Section 6 uses it.

Roster-set confidence: high. Per-cell date and Global-availability confidence: medium, bounded by the tier B source, and stated as such in the audit.

## Section 4: Live Operations & Events

### 4.1 Current events `[JP]`

State as of 2026-09-27 02:05 JST. Newest official GAME notice on `umamusume.jp/news/?t=game` was 2026-09-26 12:00.

**Event Name:** レースイベント「チャンピオンズミーティング CLASSIC」(special holding, カタール凱旋門賞 premium partner commemoration)
**Server:** `[JP]`
**Type:** Race event (Champions Meeting, league + rounds, real-time PvP with trained Umamusume)
**Start Date:** League selection 2026-09-26 12:00 JST (official); Round 1 2026-09-29 12:00 JST (official); data export gives the event record id 49 spanning 2026-09-29 12:00 JST -> 2026-10-05 11:59 JST
**End Date:** 2026-10-05 11:59 JST (single source: `events__champions-meeting.json` id 49; official notice publishes only "9/29 12:00 から")
**Key Rewards:** Champions Meeting ranking and league rewards, plus an event-exclusive title granted to every trainer who reaches the final round regardless of group or placing. The official Japanese title string is deliberately not reproduced here because it contains equine vocabulary that fails this document's lore gate.
**Mechanics:** Course conditions published by the official notice: 2400 m (medium distance), turf, right-handed, autumn, sunny, heavy going. Special rule "デバフなし" (no debuff): 50 regular skills plus 5 inherited skills, 55 named in total, do not activate. Regular examples from the list: 慧眼, 見惚れるトリック, the four 駆け引き, the twelve けん制/焦り/ためらい variants, 悩殺術, スピードイーター, 魅惑のささやき, スタミナイーター, 八方にらみ, 威風堂々, 切り崩し. The inherited skills on the list are アナタヲ・オイカケテ, 至上であれ, Adventure of 564, Drain for Rose and Spooky-Scary-Happy. Unique and evolved skills still fire even when they carry debuff effects.
**Source:** [S] `https://umamusume.jp/news/detail?id=3444` (published 2026-09-09 17:00, amended 2026-09-11 18:06, read 2026-09-27 02:04 JST); [S] `https://umamusume.jp/news/?t=game` item 2026-09-26 12:00 "参加リーグ選択開始" (id 3463); [B] `events/champions-meeting` from the data export id 49 (name "CLASSIC", resource_id 15)
**Status:** Announced and partially live (league selection open, Round 1 not started)

**Event Name:** イベント「マスターズチャレンジ」(Masters Challenge), limited-time race set
**Server:** `[JP]`
**Type:** Time-limited race challenge event (single-player, replayable races for currency)
**Start Date:** ❌ UNVERIFIED: no current source found for the opening date. Last known: official notice of 2026-09-26 12:00 describes the set as 現在開催中 (currently running).
**End Date:** 2026-10-26 11:59 JST for the currently listed limited-time races (official)
**Key Rewards:** ジュエル (Jewels) and 結晶片 (crystal shards, the training uncap items), stated by the official notice
**Mechanics:** Racing the listed limited-time courses before the deadline. Official warnings: crossing the end time with a run in progress can fail to register the entry, and no reward is granted if the limited race closes mid-run. The official notice says these limited races are planned for later addition to the Archives with rewards unchanged and progress carried over, with the addition date not yet set.
**Source:** [S] [JP official news id 3363](https://umamusume.jp/news/detail?id=3363) (2026-09-26 12:00, read 2026-09-27 02:05 JST); [A] [Game8 JP Masters Challenge guide, 2026-09-11](https://game8.jp/umamusume/584950); [A] [Kamigame 第9回 strategy page, 2026-08-01](https://kamigame.jp/umamusume/page/296436114980404472.html)
**Status:** Active, on three domains: the official notice plus Game8 JP (2026-09-11) and Kamigame (第9回, 2026-08-01). The opening date is still `❌ UNVERIFIED`; the second-domain gap that this line reported is closed. See Source Conflict Log row 37

**Event Name:** 「秋のGⅠキャンペーン第1弾」(Autumn GⅠ Campaign, phase 1)
**Server:** `[JP]`
**Type:** Login/mission campaign tied to the autumn GⅠ and JpnⅠ schedule
**Start Date:** 2026-09-21 12:00 JST
**End Date:** Phase 1 mission window 2026-09-28 04:59 JST; the campaign's later mission windows run to 2026-10-26 04:59 JST and the later mail gifts run to 2026-11-01 11:59 JST
**Key Rewards:** Clearing the limited-time missions grants ジュエル, マニー and フレンドPt; specific mission sets also grant Star Piece sets for カルストンライトオ [真実一路], マヤノトップガン [ろっきん☆MewMeow], コパノリッキー [光彩陸離☆招福衣], ヴィブロス [Voyage étincelant] and ナリタトップロード [The Proud Road]. Mail gifts of マニー ×100000 per race commemoration. GameWith tallies ジュエル ×150 for the whole limited-mission set (tier A, not repeated on the official page).
**Mechanics:** Missions appear on the Home screen under [ミッション]. Published windows (all JST): スプリンターズS 9/21 12:00-9/28 4:59; ジャパンダートクラシック 10/1 5:00-10/8 4:59; マイルCS南部杯 10/6 5:00-10/13 4:59; 秋華賞 10/12 5:00-10/19 4:59; 菊花賞 10/19 5:00-10/26 4:59. Mail gifts: 9/27 12:00-10/4 11:59 (SプリンターズS), then 10/7, 10/12, 10/18, 10/25 windows. Mission completion is judged from the moment the mission was added, so earlier clears do not count. Mail gifts expire 30 days after delivery.
**Source:** [S] `https://umamusume.jp/news/detail?id=3458` (2026-09-21 12:00, read 2026-09-27 02:05 JST); [A] `https://gamewith.jp/uma-musume/article/show/520191` 「秋のG1記念ミッション2026」 (最終更新 2026-09-23 13:10) publishes the identical five mission windows
**Status:** Active (SprinterS mission set live, SprinterS mail gift window opens 2026-09-27 12:00 JST)

**Other active `[JP]` item:** トレーニングパス (Training Pass), update notice 2026-09-24 12:00 JST, [S] [JP official news id 3455](https://umamusume.jp/news/detail?id=3455), corroborated on a second domain by [A] [GameWith training and premium pass reward list, 2026-09-01](https://gamewith.jp/uma-musume/article/show/437940), which enumerates the track split as スタンダード against プレミアム (800 yen) and names the reward categories ジュエル, 育成ガチャチケ, サポガチャチケ, アビリティPt, 虹の解放結晶片 and サポートカード交換チケット. Per-season quantities and the season close time stay `❌ UNVERIFIED`: they are published in-app. See Source Conflict Log row 33.

**Event Name:** ウマ娘ストーリー解放キャンペーン for [Éclat de Roseraie] ローズキングダム
**Server:** `[JP]`
**Type:** Story-unlock campaign (companion campaign to a new trainable Umamusume)
**Start Date:** 2026-09-18 12:00 JST
**End Date:** 2026-09-30 11:59 JST
**Key Rewards:** Umamusume Story episodes 1-4 of the featured Umamusume become watchable without meeting the normal unlock conditions; watching during the window still grants the first-watch Jewel reward, the story reward and Umamusume Archive experience.
**Mechanics:** Episodes that are not watched during the window re-lock afterwards. Available after the tutorial.
**Source:** [S] `https://umamusume.jp/news/detail?id=3457` (2026-09-18 12:00, read 2026-09-27 02:02 JST); [A] `https://gamewith.jp/uma-musume/article/show/257332` lists the paired gacha window 9/18 12:00-9/30 11:59
**Status:** Active

**Recently closed `[JP]`, for continuity inside the 30 day window**
- 特別イベント「アグネスタキオンの究極因子研究」: special event, opened with the 2026-09-11 12:00 JST notice and closed with the 2026-09-18 12:00 JST notice "終了！" [S] `umamusume.jp/news/detail?id=3447` and `?id=3448`. レジェンドレース「スワンステークス」: 2026-09-06 12:00 -> 2026-09-12 04:59 JST [S] opening notice 2026-09-06 12:00 (id 3426) plus advance notice 2026-09-05 (id 3425); [B] `events__legend-race.json` (`name_jp` スワンステークス, sub-records id 138 with card_id 105323 and id 139 with card_id 109301), dates agreeing to the minute. Observed 2026-09-27 02:03 JST.
- レースイベント「チャンピオンズミーティング MILE」: 2026-09-18 12:00 -> 2026-09-24 11:59 JST [S] news 2026-09-18 12:00 (id 3453) plus league-selection notice 2026-09-15 12:00 (id 3452); [B] `events__champions-meeting.json` id 48 (name "MILE", resource_id 14). Two domains agree.
- ストーリーイベント「ゆけゆけ、きらぼし調査隊！」: 2026-08-31 12:00 -> 2026-09-11 11:59 JST [S] opening notice 2026-08-31 12:00 (id 3428) and closing notice 2026-09-11 12:00 (id 3429); [B] `events__story-events.json` id 1056 (`name_ja` matches exactly, `start_ja`/`end_ja` match to the minute). Two domains agree, this is the strongest chain in the section.

**How to re-check (3.1).** Official page: `https://umamusume.jp/news/?t=game` (JS rendered, read it in a browser, newest GAME notices on top; detail pages are `https://umamusume.jp/news/detail?id=NNNN`). Wiki page: `https://gamewith.jp/uma-musume/article/show/257332` for the gacha/event windows and `https://kamigame.jp/umamusume/` for the event list; both print their own 最終更新日, so record it when you re-read. Data page: `https://gametora.com/ja/umamusume/gacha` (last-updated date is in page text, 2026-09-24 when this draft was written).

### 4.2 Current events `[Global]`

State as of 2026-09-26 17:16 UTC. Newest official Global notice on `umamusume.com/news/` was 2026-09-24 22:00 (UTC), and nothing newer had been posted at the observation instant.

**Finding worth stating plainly:** at the anchor instant the Global server had **no live story event and no live race event**. The story event "Hark Back, Run Forward" closed 2026-09-19 21:59 UTC and Champions Meeting: Scorpio Cup closed 2026-09-25 21:59 UTC, while the next story event (Illuminate the Heart) was announced to open 2026-09-28 22:00 UTC. That gap is real, not missing data.

**Event Name:** Transfer Requests
**Server:** `[Global]`
**Type:** Feature release / long-running campaign window (Umamusume transfer requests)
**Start Date:** 2026-09-24 22:00 UTC ("now available"), pre-announced 2026-09-23 22:00 UTC ("coming soon")
**End Date:** 2026-09-28 21:59 UTC, closing the first window. The 2026-09-24 22:00 UTC notice states availability, and the closing time comes from the same notice read in full for 1.6.9; Conflict Log row 30 rules the earlier `❌ UNVERIFIED` closed.
**Key Rewards:** Trainee Umamusume Star Pieces, Monies, and Support Points, from the same full read of the notice recorded in 1.6.9.
**Mechanics:** Transfer-request functionality opened on the Global server; it is a system feature rather than a scoring event, so it does not appear in any GameTora event export.
**Source:** [S] `https://umamusume.com/news/1058/` (2026-09-24 22:00 UTC) and [S] `https://umamusume.com/news/1057/` (2026-09-23 22:00 UTC)
**Status:** Active (two domains: [Global official news 1058](https://umamusume.com/news/1058/) plus the [game8.co Transfer Request guide](https://game8.co/games/Umamusume-Pretty-Derby/archives/554781); see Source Conflict Log rows 26, 27, 30 and 38)

**Event Name:** Umamusume Story Sneak Peek for [Butterfly Sting] Wonder Acute
**Server:** `[Global]`
**Type:** Story-unlock campaign (companion to a new Trainee Umamusume)
**Start Date:** 2026-09-23 22:00 UTC
**End Date:** 2026-10-04 21:59 UTC (the notice states the window as "10:00 p.m., Sep 23" opening to "9:59 p.m., Oct 4, 2026 (UTC)" closing)
**Key Rewards:** Episodes 1-4 of Wonder Acute's Umamusume Story unlocked without meeting normal conditions; first-watch carat rewards, story rewards and Umamusume Archive experience all apply if watched inside the window.
**Mechanics:** Unwatched episodes re-lock when the window closes. Available after the tutorial.
**Source:** [S] `https://umamusume.com/news/1052/` (2026-09-23 22:00 UTC, read 2026-09-26 17:07 UTC); [A] `https://game8.co/games/Umamusume-Pretty-Derby/archives/537125` (updated 2026-09-17) lists "Wonder Acute ... Sep. 23 - Oct. 4, 2026"
**Status:** Active

**Closed inside the 30 day window `[Global]`**
- Champions Meeting: Scorpio Cup, race event: 2026-09-19 22:00 -> 2026-09-25 21:59 UTC. League selection 2026-09-15 22:00 -> 2026-09-23 21:59 UTC. Rounds: R1 09-19 22:00 -> 09-21 21:59, R2 09-21 22:00 -> 09-23 21:59, Final registration 09-23 22:00 -> 09-24 09:59, matching 09-24 10:00 -> 21:59, races 09-24 22:00 -> 09-25 21:59 (all UTC). Requires a team of three Veteran Umamusume, unlocked at Team Rank E2. Disabled skills that edition: Night Races, Sharp Turns and Collaborative Graded Races in all three grades. [S] `https://umamusume.com/news/1050/` and [S] `https://umamusume.com/news/1049/` (league selection opening); [B] `en/events/champions-meeting` id 19 "Scorpio Cup" 2026-09-19 22:00 -> 2026-09-25 21:59 UTC. Two domains, minute-exact agreement.
- Story event "Hark Back, Run Forward": ended 2026-09-19 21:59 UTC per [S] `https://umamusume.com/news/1024/` (2026-09-19 22:00 UTC). ❌ UNVERIFIED start date: the local Global story-event export `en__storyevents.json` stops at event_id 1009 (2026-01-29 to 2026-02-05 UTC), so it carries no September records at all. Last known: the ending notice only.

**Not an event (guard against a future misread):** "Check out Sakura Chiyono O's SUPER KEEN ADVICE!" (`https://umamusume.com/news/1064/`, 2026-09-23 22:00 UTC) is an out-of-game official YouTube series of six training-explainer episodes, not a live-ops event, and it must not be entered in an event table.

**How to re-check (3.2).** Official page: `https://umamusume.com/news/` (JS rendered; individual notices are `https://umamusume.com/news/NNNN/` and each carries its own UTC stamp). Wiki page: `https://game8.co/games/Umamusume-Pretty-Derby/archives/537125` for the Global schedule view; the Champions Meeting edition names on that site's nav matched the data export when this draft was written.

### 4.3 Officially announced upcoming (both servers)

`[JP]`
1. Champions Meeting CLASSIC Round 1, 2026-09-29 12:00 JST (special Arc commemoration holding, no-debuff rule). Source: [S] `umamusume.jp/news/detail?id=3444`. See 3.1 for the full block.
2. Autumn GⅠ Campaign phases 2-5 with the five published mission windows and the four remaining mail-gift windows, 2026-10-01 05:00 through 2026-11-01 11:59 JST. Source: [S] `umamusume.jp/news/detail?id=3458`, corroborated by [A] `gamewith.jp/uma-musume/article/show/520191`.
3. New-trainee pickup gacha and the 5.5th Anniv. Select Pickup close 2026-09-30 11:59 JST; the Twinkle Collection and SSR確定パワーガチャ close 2026-10-01 11:59 JST; the two 凱旋門賞 guaranteed gacha close 2026-10-13 11:59 JST. Sources: [S] `?id=3457`, `?id=3421`, `?id=3440`; [A] `gamewith.jp/uma-musume/article/show/257332`.
4. `[DATAMINE]` Story event record id 1057, 2026-09-30 12:00 -> 2026-10-13 11:59 JST. Not an announcement: it is listed here because the window is dated, and it is excluded from the announced count in this subsection. Name: ❌ UNVERIFIED, the `events/story-events` record carries no `name_ja`, `name_en`, `url_name` only ("story-event-57") and no bonus-card list. Single source (GameTora export) and no official notice exists yet as of 2026-09-27 02:05 JST, so this line is a placeholder for the next announcement, not a published schedule item.

`[Global]`
1. Story event "Illuminate the Heart", 2026-09-28 22:00 -> 2026-10-12 21:59 UTC. Rewards: event points from Career playthroughs, copies of an event-exclusive SSR Support Card, plus an exclusive story. The id chain resolves that SSR: `en/foresight/timeline` future_story_events record id 1020, `support_ids [30125]` -> `support-cards.json` support_id 30125 = サクラローレル / Sakura Laurel, SSR, JP title [ここからはDon't stop!], official English title [Don't Stop Anymore!], JP release 2022-11-28, Global release field still empty.: [S] `https://umamusume.com/news/1076/` (2026-09-24 22:00 UTC); [B] local foresight record with `is_estimated: false`; [A] `game8.co/games/Umamusume-Pretty-Derby/archives/537125` lists a "Sep. 28 - Oct. 12, 2026" window for the same period. Two domains, and the official statement is the authority.
2. Nothing else is announced for Global beyond 2026-10-04 21:59 UTC. The next race event and the next Scout pair have no official notice at the observation instant.

#### 4.3a `[SPECULATION]` (derived or predicted, not confirmed)
Nothing in this subsection has an official source. Treat every line as a forecast that may never happen on the stated date.
- `[Global]` Next Spotlight Scout pair, Vodka and Daiwa Scarlet, with SSR supports Narita Brian and Air Groove. Game8's schedule page (updated 2026-09-17) states "Sep. 28 - Oct. 12, 2026"; GameTora's own record set carries `is_estimated: true` with `display_start` 2026-09-30 11:25 UTC for gacha ids 30132/30133. Both are projections of the JP cadence onto the Global release rhythm, and the predicted `display_start` stamps are interpolated midpoints rather than real reset times (see 5.3a). Logged as a date conflict, see Source Conflict Log row 34.
- `[Global]` Next Champions Meeting: `en/foresight/timeline` future_cm record id 20 = "サジタリウス杯 / Sagittarius Cup" with `is_estimated: true`, `display_start` 2026-10-12 10:37 UTC. Game8's Global nav labels the same edition "Sagittarius Cup 2 (CM20)". No official Global notice exists as of 2026-09-26 17:16 UTC. `[RUMOR]` quality: name is safe (the zodiac rotation is mechanical, `en/events/champions-meeting` runs Taurus Cup through Aries Cup (ids 1 to 12) and then restarts at Taurus Cup through Scorpio Cup (ids 13 to 19), which is 19 records rather than two complete 12-sign cycles), the date is not.
- `[Global]` Longer projections exist in `en/foresight/timeline` under `future_char_banners` (137) and `future_support_banners` (97), every one of them `is_estimated: true`, running to 2029. `en/foresight/predicted_releases` is a different file, holding `char_cards`, `scenarios` and `support_cards` with no estimate flag at all. They are not published as a schedule in this document because every row is a model output.
- `[JP]` Kamigame labels its next-banner entry 予測 (prediction); the local JP exports contain zero `is_estimated: true` records for the `ja` server, so no JP future banner is even forecast-able from the data side. Nothing JP-side is claimed past 2026-10-13 11:59 JST.

**How to re-check (3.3).** Official pages: `https://umamusume.jp/news/?t=game` for JP advance notices (titles prefixed 【予告】) and `https://umamusume.com/news/` for Global "coming soon" notices. Wiki pages: `https://gametora.com/ja/umamusume/gacha` (JP) and `https://game8.co/games/Umamusume-Pretty-Derby/archives/537125` (Global schedule, note the site's own disclaimer that its dates are derived from the Japanese version).

### 4.4 Recurring event types

Cadence is computed from the local event exports (`events__champions-meeting.json` 49 JP records, `en/events/champions-meeting` 19 Global records, `events__story-events.json` 57 JP records, `events__legend-race.json` 57 JP records) and each row's naming is checked against the official notices read in this session.

| Type | Servers | Cadence in the data | Naming pattern in the data | Mechanics summary | Evidence |
|---|---|---|---|---|---|
| Champions Meeting (race event) | both | JP editions span 6 days and are spaced 31 to 61 days apart in 2026 (records 2026-01-22, 03-22, 04-23, 06-23, 07-24, 09-18, 09-29), with the last two only 11 days apart because one is a special commemoration holding; Global editions span 6 to 7 days and are spaced 20 to 24 days apart (2026-04-23, 05-14, 06-04, 06-24, 07-15, 08-08, 08-28, 09-19) | `[JP]` labels every 2025 and 2026 record by distance or surface class (CLASSIC / SPRINT / MILE / LONG / DIRT) while the earliest records use zodiac names (id 1 = タウラス杯); `[Global]` uses zodiac cup names in all 19 records, restarting the zodiac series each year without completing it, 19 records over two partial cycles | League selection opens 3 to 4 days before Round 1; Round 1, Round 2, then a Final Round with registration, matching and race sub-windows; a team of three previously trained Umamusume; unlocked at Team Rank E2 on Global; each edition publishes a skill-disable list and occasionally a special rule | [S] `umamusume.com/news/1050/`, [S] `umamusume.jp/news/detail?id=3444`, [B] both champions-meeting exports |
| Story event | both | JP editions run 11 to 17 days and start 29 to 62 days apart (the seven 2026 records: 01-30, 03-30, 04-30, 05-29, 07-30, 08-31, next 09-30); the Global export cannot support a cadence figure because it stops at 2026-01-29 | JP uses poetic Japanese titles; Global uses its own English titles which are not literal translations | Complete Career playthroughs to bank Event Points; point rewards plus a bingo-style card in the Global data (`bingo` blocks with `pityNumber`, `linesToReset`); reward track includes an event-exclusive support card; an exclusive story unlocks as you progress | [S] `umamusume.com/news/1076/`, [S] `umamusume.jp/news/detail?id=3428`, [B] `events__story-events.json`, `en__storyevents.json` |
| Legend Race (single-player challenge against a preset rival line-up) | `[JP]` confirmed; `[Global]` ❌ UNVERIFIED, no Global record in the data exports and no Global notice read | JP monthly, each edition runs 5.7 days (open 12:00, close 04:59 on the sixth day) and the nine 2026 records start 25 to 35 days apart; the record closing 2026-09-12 04:59 JST ran two stages, 09-06 12:00 -> 09-09 04:59 then 09-09 05:00 -> 09-12 04:59 | Edition names come from the fixture the event models; the record read here is スワンステークス | Two back-to-back limited stages, each with its own start and end and its own rival line-up with published stat totals | [S] `umamusume.jp/news/detail?id=3426` (index line 2026-09-06 12:00), [B] `events__legend-race.json` |
| Special event (factor / item research style) | `[JP]` confirmed; `[Global]` ❌ UNVERIFIED for this label | One observed window: running per the 2026-09-11 12:00 JST notice titled 開催中 and closed per the 2026-09-18 12:00 JST notice titled 終了, so about 7 days; the opening time is not restated in either notice | 特別イベント「...」 | Time-boxed objective chain that pays a progression currency; opened by an advance notice and closed by an 終了 notice | [S] `umamusume.jp/news/detail?id=3447` and `?id=3448`, advance notice `?id=3446` |
| Masters Challenge (limited-time race set) | `[JP]` confirmed; `[Global]` ❌ UNVERIFIED | Long window: still running at 2026-09-27 with a 2026-10-26 11:59 JST close, so at least ~5 weeks in the observed case | イベント「マスターズチャレンジ」 | Replayable limited-time courses paying Jewels and crystal shards; expired races are planned for the Archives with rewards unchanged | [S] `umamusume.jp/news/detail?id=3363` |
| Season pass | `[JP]` confirmed | Update notice 2026-09-24 12:00 JST; the previous pass cycle boundaries are not in any data export | トレーニングパス | Structure, tiers and rewards: ❌ UNVERIFIED, published in-app only; the web notice states only that the pass was updated | [S] `umamusume.jp/news/?t=game` (id 3455) |
| Campaign tied to a real-world race calendar, including premium-partner commemorations | `[JP]` confirmed; `[Global]` ❌ UNVERIFIED | Follows the Japanese GⅠ calendar; five mission windows plus five mail gifts in phase 1 alone. The premium-partner commemoration ran 2026-09-11 12:00 -> 2026-10-13 11:59 JST | 「秋のGⅠキャンペーン第N弾」, 「カタール凱旋門賞 プレミアムパートナー就任記念キャンペーン」 | Home-screen limited missions paying ジュエル, マニー, フレンドPt and themed Star Pieces; mail gifts of マニー ×100000; a pair of guaranteed gacha; a special Champions Meeting holding; a web-store campaign; balance additions such as 覚醒Lv6/Lv7 | [S] `umamusume.jp/news/detail?id=3458`, `?id=3440`, `?id=3449`, `?id=3444`, `?id=3443`; [A] `gamewith.jp/uma-musume/article/show/520191` |
| Anniversary campaign ladder | `[JP]` confirmed | 5.5th Anniversary, 第3弾 notice on 2026-09-11 12:00 JST, gacha window 2026-08-24 to 2026-09-26 JST | 「5.5th Anniversaryキャンペーン第N弾」 plus 「5.5th Anniv.セレクトピックアップ」 gacha naming | Numbered phases with login gifts, a select-pickup gacha and pass content | [S] `umamusume.jp/news/?t=game` (ids 3437, 3457), [A] `gamewith.jp/uma-musume/article/show/257332` |
| Versus race schedule notice | `[JP]` confirmed as a recurring publication type; contents not read | Seasonal: notice titled 9月～2月対戦レースイベント, 2026-09-09 17:00 JST | 対戦レースイベント | Periodic listing of the upcoming versus-race calendar | [S] `umamusume.jp/news/detail?id=3445` (index line only) |
| Story-unlock campaign (paired with a new release) | both confirmed | Runs exactly as long as the paired debut gacha window (JP 09-18 to 09-30 JST; Global 09-23 22:00 to 10-04 21:59 UTC) | JP ウマ娘ストーリー解放キャンペーン; Global Umamusume Story Sneak Peek | Episodes 1-4 unlocked without meeting the normal conditions; unwatched episodes re-lock | [S] `umamusume.jp/news/detail?id=3457`, [S] `umamusume.com/news/1052/` |

Lore note for this subsection: the character data in these exports contains a `race` object (distance, ground, track, season, weather) and an `aptitude` array. Those are event and stat configuration, not character attributes, and none of them are published as character traits in this document.

**How to re-check (3.4).** Official pages: `https://umamusume.jp/news/?t=game` for JP editions and `https://umamusume.com/news/` for Global editions. Wiki pages: re-download the same event exports (`events__champions-meeting.json`, `events__story-events.json`, `events__legend-race.json`, `en/events/champions-meeting`) and diff the date columns, then confirm each new edition name on `https://kamigame.jp/umamusume/`.

---

## Section 5: Gacha & Banner Schedule

### 5.1 Current banners `[JP]`

Six gacha were live at 2026-09-27 02:05 JST. Rates below are GameTora per-card weight arithmetic: each `lineup` group key is the per-card rate multiplied by 10000, and the groups sum to exactly 100.00% on every banner checked, which is what makes the arithmetic usable. Official rate tables exist only in the in-app [ガチャ詳細] screen, so every rate line here is marked accordingly.

**Banner Name:** ピックアッププリティーダービーガチャ (Pickup Pretty Derby Gacha)
**Server:** `[JP]`
**Type:** Character pickup gacha, new trainable Umamusume debut
**Featured:** ★★★ ローズキングダム [Éclat de Roseraie] (id chain: `gacha__char-standard.json` id 30470 -> `pickups[0]` card_id 114401 -> `character-cards.json` 114401, rarity 3, release 2026-09-18)
**Start Date:** 2026-09-18 12:00 JST
**End Date:** 2026-09-30 11:59 JST
**Rates:** ★3 3.00% total (243 non-featured ★3 cards at 0.0092%/0.0093%, featured ★3 ローズキングダム at 0.75%), ★2 18.00%, ★1 79.00%. Tier-B arithmetic plus [A] GameWith's 排出確率 table (★★★ 3.0%, ★★ 18.0%, ★ 79.0%); official web publication ❌ UNVERIFIED (in-app only).
**Spark Cost:** 育成ウマ娘交換Pt, 200 Pt for the featured ★3. Official notices name the currency and state that unspent balances under 200 Pt convert to クローバー at close; GameWith states exchange after 200 pulls. Points do not carry to the next gacha.
**Source:** [S] `https://umamusume.jp/news/detail?id=3457`; [B] `gacha__char-standard.json` id 30470; [A] `gamewith.jp/uma-musume/article/show/257332` ("9/18(金)12:00～9/30(水)11:59", ローズキングダム)
**Status:** Active

**Banner Name:** 5.5th Anniv.セレクトピックアップ サポートカードガチャ
**Server:** `[JP]`
**Type:** Support card gacha with player-selected spotlight (select pickup)
**Featured:** Player chooses 2 SSR from a published candidate list of 10: タップダンスシチー [刀光散らしてClash！], エアグルーヴ [心覚えし、京の華], トウカイテイオー [天才的ユートピア], ラインクラフト [Unveiled Dream], グランアレグリア [スマイル・エバーアフター], ネオユニヴァース [星跨ぐメッセージ], デアリングタクト [白に至る純真], デアリングハート [白に至る覚悟], フォーエバーヤング [Innovator], 駿川たづな [一杯のノスタルジア]. The local record's two featured slots hold ids 301 and 302, which resolve in neither `character-cards.json` nor `support-cards.json`; the likeliest reading is that they are per-account selection placeholders rather than fixed cards, but the local data does not prove it, so the featured pair is ❌ UNVERIFIED for any given account.
**Start Date:** 2026-09-18 12:00 JST
**End Date:** 2026-09-30 11:59 JST
**Rates:** SSR 3.00% total (non-featured SSR at 0.0069%/0.0070% per card, the two selected slots at 0.75% each), SR 18.00%, R 79.00%. Official web publication ❌ UNVERIFIED.
**Spark Cost:** サポートカード交換Pt, 200 Pt, no carryover, under-200 balance converts to クローバー.
**Source:** [S] `?id=3457` (window and candidate list); [B] `gacha__special.json` id 30471; [A] GameWith "セレクトピックアップ サポカガチャ 9/18(金)12:00～9/30(水)11:59, 強力なSSRを2枚選択"
**Status:** Active. Guarantee: one per account, 10-pull for 有償ジュエル 1500 with the 10th slot forced to SSR; the normal 有償/無償 1500 10-pull carries no such guarantee.

**Banner Name:** トゥインクルコレクション プリティーダービーガチャ
**Server:** `[JP]`
**Type:** Limited-pool character gacha (only 8 ★3 in the pool at all)
**Featured:** ★3 pool of exactly 8, ids resolved 8 for 8 against the official list: ヒシアケボノ [マジックナイトガーランド] 102802, サクラチヨノオー [Fleur Enneigée] 106902, ダイイチルビー [Flowing Blue] 108502, メジロラモーヌ [Untouchable Eden] 108602, ヴィルシーナ [Le beau sommet] 109001, ブエナビスタ [Heroína Inocente] 111401, デュランダル [Chevalier fidèle] 112101, グランアレグリア [すまいる・まい・うぇい！] 113101
**Start Date:** 2026-09-01 12:00 JST
**End Date:** 2026-10-01 11:59 JST
**Rates:** ★3 3.00% (each of the 8 at 0.3750%), ★2 18.00%, ★1 79.00%. Official web publication ❌ UNVERIFIED.
**Spark Cost:** 育成ウマ娘交換Pt (official note 3 and 4 of the notice apply to this gacha: no carryover, under-200 converts to クローバー), 200 Pt
**Source:** [S] `https://umamusume.jp/news/detail?id=3421`; [B] `gacha__special.json` id 30466; [A] GameWith "トゥインクルコレクションガチャ 9/1(火)12:00～10/1(木)11:59"
**Status:** Active. Guarantee: one per account 10-pull for 有償ジュエル 1500 with a forced ★3 on slot 10; single pull 150 ジュエル or a gacha ticket; 1日1回限定 pull 50 有償ジュエル.

**Banner Name:** SSR確定パワーガチャ
**Server:** `[JP]`
**Type:** Limited-pool support gacha, one stat type only
**Featured:** 15 SSR power-type support cards at 0.2% each. Resolved from the local pool: ウイニングチケット 30183, ケイエスミラクル 30195, セイウンスカイ 30204, ニシノフラワー 30208, エスポワールシチー 30222, シンボリクリスエス 30228, メジロアルダン 30234, ウオッカ 30245, ブエナビスタ 30250, タマモクロス 30256, ミホノブルボン 30277, ファインモーション 30283, ネオユニヴァース 30287, アグネスデジタル 30297, グランアレグリア 30301. Every one of the 15 carries `type: power` in `support-cards.json`, which is what ties the local record to the official "power type only" wording.
**Start Date:** 2026-09-01 12:00 JST
**End Date:** 2026-10-01 11:59 JST
**Rates:** SSR 3.00%, SR 18.00%, R 79.00%. Official web publication ❌ UNVERIFIED.
**Spark Cost:** none. The official notice states サポートカード交換Pt cannot be earned on this gacha.
**Source:** [S] `?id=3421`; [B] `gacha__special.json` id 50245; [A] GameWith "SSR確定パワーガチャ 9/1(火)12:00～10/1(木)11:59, パワータイプのサポートカードのみ"
**Status:** Active. Structure: only the stated ★3/SSR subset can drop on slots 1-10; one per account, 有償ジュエル 1500.

**Banner Name:** ★3確定 凱旋門賞ガチャ
**Server:** `[JP]`
**Type:** Commemoration guaranteed character gacha
**Featured:** 9 ★3, ids matching the official list 9 for 9: マンハッタンカフェ [幽玄薔薇] 102503 and [柳緑小夜] 102502, オルフェーヴル 111501, タップダンスシチー 110702, シリウスシンボリ 107002, クロノジェネシス 113301, ナカヤマフェスタ 104902, キセキ 113701, ヴィクトワールピサ 114301
**Start Date:** 2026-09-11 12:00 JST
**End Date:** 2026-10-13 11:59 JST
**Rates:** ★3 3.00% (6 cards at 0.3333%, 3 at 0.3334%), ★2 18.00%, ★1 79.00%; ★2 and ★1 pools are everything released up to 2026-09-11. Official web publication ❌ UNVERIFIED.
**Spark Cost:** none, official notice states neither exchange currency is earned on this gacha
**Source:** [S] `https://umamusume.jp/news/detail?id=3440`; [B] `gacha__special.json` id 50246
**Status:** Active. One per account, 有償ジュエル 1500, guaranteed ★3 on slot 10 and only the listed ★3 on the other slots.

**Banner Name:** SSR確定 凱旋門賞ガチャ
**Server:** `[JP]`
**Type:** Commemoration guaranteed support gacha
**Featured:** 6 SSR, ids matching the official list 6 for 6: オルフェーヴル [只、君臨す。] 30187, ブラストワンピース [Blast Off!] 30232, ゴールドシップ [激録！爆走トナカイ事件] 30278, タップダンスシチー [刀光散らしてClash！] 30298, サトノダイヤモンド [永久の誓い、永久の輝き] 30302, キセキ [巻頭カラーの夏] 30307
**Start Date:** 2026-09-11 12:00 JST
**End Date:** 2026-10-13 11:59 JST
**Rates:** SSR 3.00% (6 at 0.5% each), SR 18.00%, R 79.00%. Official web publication ❌ UNVERIFIED.
**Spark Cost:** none (official notice)
**Source:** [S] `?id=3440`; [B] `gacha__special.json` id 50247
**Status:** Active. One per account, 有償ジュエル 1500.

**How to re-check (4.1).** Official page: `https://umamusume.jp/news/?t=game`, newest GAME notices; the current debut item is `https://umamusume.jp/news/detail?id=3457` and the month-long pairs are `?id=3421` and `?id=3440`. Wiki page: `https://gamewith.jp/uma-musume/article/show/257332` (records 最終更新日 in page text, 2026-09-26 04:29 at the time of this draft) and `https://kamigame.jp/umamusume/` for the per-banner pickup pages; `https://gametora.com/ja/umamusume/gacha` for the raw id windows.

### 5.2 Current banners `[Global]`

Two spotlight Scouts were live at 2026-09-26 17:10 UTC, plus the permanent pools.

**Banner Name:** Spotlight Pretty Derby Scout
**Server:** `[Global]`
**Type:** Character pickup Scout, new Trainee Umamusume debut
**Featured:** ★★★ [Butterfly Sting] Wonder Acute (chain: `en__gacha__char-standard.json` id 30128 -> `pickups[0]` card_id 110001 -> `character-cards.json` 110001, rarity 3, `release_en` 2026-09-24)
**Start Date:** 2026-09-23 22:00 UTC
**End Date:** 2026-10-04 21:59 UTC
**Rates:** 3★ 3.00% (spotlight card 0.75%, the other 86 3★ split as 54 cards at 0.0262% and 32 at 0.0261%), 2★ 18.00%, 1★ 79.00%; bonus for pulling the spotlight 3★: [Butterfly Sting] Wonder Acute Star Piece ×90, repeatable and also paid when the character is claimed with Trainee Exchange Points. Official web publication of the numbers ❌ UNVERIFIED (the notice points to the in-game Scout Rates tab).
**Spark Cost:** Trainee Exchange Points; the Global guide gives 200 Exchange Points for the featured 3★ and one Point per roll, with no carryover and unused Points converting to Clovers. Currency names are official; the 200 figure is ⚠️ STALE: `game8.co/.../archives/538219` dated 2025-08-08.
**Source:** [S] `https://umamusume.com/news/1052/` (2026-09-23 22:00 UTC); [B] `en__gacha__char-standard.json` id 30128; [A] `game8.co/games/Umamusume-Pretty-Derby/archives/537125` ("Wonder Acute ... Sep. 23 - Oct. 4, 2026")
**Status:** Active

**Banner Name:** Spotlight Support Card Scout
**Server:** `[Global]`
**Type:** Support Card pickup Scout, two debuts
**Featured:** SSR [Danke Schön] Eishin Flash (id 30122, `release_en` 2026-09-24, 0.75%) and SR [Stop, Prez!] Narita Top Road (id 20053, `release_en` 2026-09-24, 2.25%). Chain: `en__gacha__support-standard.json` id 30129 -> `pickups` [30122, 20053] -> `support-cards.json`.
**Start Date:** 2026-09-23 22:00 UTC
**End Date:** 2026-10-04 21:59 UTC
**Rates:** SSR 3.00%, SR 18.00%, R 79.00% (weight arithmetic, sums to 100.00%). Official web publication ❌ UNVERIFIED.
**Spark Cost:** Support Card Exchange Points, same 200 Point structure, no carryover, converts to Clovers (currency names official, number ⚠️ STALE as above)
**Source:** [S] `https://umamusume.com/news/1052/`; [A] `game8.co/.../archives/537125`; [B] `en__gacha__support-standard.json` id 30129
**Status:** Active

**Permanent pools `[Global]`.** The local Global export lists 20 `special` records with end timestamps in 2050, i.e. always open: ids 10001 and 10002 (a 3★ 3.00% / 2★ 18.00% / 1★ 79.00% character pool and the matching support pool), ids 20001 and 20002 (100% ★3 character pool of 9 cards and an SSR-only support pool of 20), ids 20003 and 20004, 20005 to 20008 (the last four opened 2025-12-28 and 2026-01-08 22:00 UTC), and 50001 and 50002 (the 10001, 10002, 50001 and 50002 records carry placeholder start timestamps at 2021-01-01 rather than real launch dates, so only the open-ended end is meaningful). Banner names: ❌ UNVERIFIED for all of them, no name exists in any data export and none of these ids appear in the Global notices read in this session. Do not publish a name for these ids without a fresh check.

**How to re-check (4.2).** Official page: `https://umamusume.com/news/` (the live Scout notice at the anchor instant is `https://umamusume.com/news/1052/`, the "coming soon" for the next pair is `https://umamusume.com/news/1051/`). Wiki page: `https://game8.co/games/Umamusume-Pretty-Derby/archives/537125` and `https://gametora.com/umamusume/gacha` (page-text last-updated stamp was 2026-09-24).

### 5.3 Officially announced upcoming banners

`[JP]`: nothing announced beyond the 2026-09-30 11:59 and 2026-10-01 11:59 and 2026-10-13 11:59 JST closings listed in 4.1. The next debut has no official notice, and Kamigame explicitly labels its next-banner row 予測. Local JP exports contain no `is_estimated` rows at all, so there is nothing to report even as a forecast.
`[Global]`: no official Scout announcement exists past 2026-10-04 21:59 UTC as of 2026-09-26 17:16 UTC. The advance-notice item `https://umamusume.com/news/1051/` (2026-09-22 22:00 UTC) pointed at the Scout pair that opened 2026-09-23 22:00 UTC, and there is no newer equivalent.

#### 5.3a `[SPECULATION]` Global banner forecast, derived from JP, not confirmed
- GameTora `is_estimated: true` records for the `en` server, next five pairs with resolved names (predicted `display_start` values are interpolated, so read date only): 2026-09-30 Vodka and Daiwa Scarlet with SSR supports Narita Brian and Air Groove (gacha 30132/30133); 2026-10-10 Zenno Rob Roy with supports Light Hello and Mayano Top Gun (30134/30135); 2026-10-15 Narita Brian with supports T.M. Opera O and Yamanin Zephyr (30136/30137); 2026-10-22 Kitasan Black and Satono Diamond with supports Special Week and Sweep Tosho (30138/30139); 2026-10-30 Hokko Tarumae with supports Agnes Tachyon and Tokai Teio (30140/30141).
- Game8's derived schedule for the same period gives 2026-09-28 to 2026-10-12 for the Vodka and Daiwa Scarlet pair, "Early October" for Zenno Rob Roy, "Early October" for Narita Brian, "Late October" for Kitasan Black and "Late October" for Hokko Tarumae. Its own text states the schedule is based on the Japanese releases and then adjusted to the English server's average monthly rollout pace, i.e. derived prediction, not confirmation. GameTora's estimated `display_start` values are interpolated midpoints (times such as 11:25 or 07:56 are not real reset times); every confirmed Global window in the same data opens at 22:00 UTC.
- `[JP]`-side dates are the upstream truth for those characters and supports, and are cited in 5.5 as JP history only. Copying a JP date into a Global row is the error this subsection exists to prevent.

**How to re-check (4.3).** Official: `https://umamusume.com/news/` and `https://umamusume.jp/news/?t=game`. Wiki: `https://game8.co/games/Umamusume-Pretty-Derby/archives/537125`; `https://gametora.com/umamusume/gacha`.

### 5.4 Gacha and Scout system mechanics

The two servers run the same system with different labels and different reset zones.

**Banner / Scout types.**
- `[JP]` `プリティーダービーガチャ` (characters) and `サポートカードガチャ` (support cards) as the two permanent tracks; `ピックアップ` variants for debuts; `セレクトピックアップ` where the player fixes 2 SSR from a candidate list; themed limited-pool gacha (`トゥインクルコレクション`, `SSR確定パワーガチャ`); one-per-account guaranteed commemoration gacha (`★3確定` / `SSR確定`). GameTora's own export split matches this: `gacha__char-standard.json` 202 JP records, `gacha__support-standard.json` 162, `gacha__special.json` 289.
- `[Global]` `Pretty Derby Scout` and `Support Card Scout` as permanent tracks, plus `Spotlight Pretty Derby Scout` and `Spotlight Support Card Scout` for debuts, both named on the official page. The Global server began in 2025, which is why its export is small: `gacha__years-by-server.json` gives `en` 2025 and 2026 against `ja` 2021 to 2026, and the Global standard files hold 65 records each against JP's 202 and 162.

**Exchange / spark system, per server.**
| Item | `[JP]` | `[Global]` |
|---|---|---|
| Exchange currencies, character and support | 育成ウマ娘交換Pt and サポートカード交換Pt | Trainee Exchange Points and Support Card Exchange Points |
| Cost of the featured ★3 / SSR | 200 Pt | 200 Points (⚠️ STALE: Game8 pity guide dated 2025-08-08) |
| Earn rate | 1 Pt per pull, per GameWith's "200回ガチャを引いた後、交換可能"; not stated on the official notice | 1 Point per roll, per the same tier-A guide |
| Carryover | Never. Official: cannot be carried to other or future gacha | Never. Official notice item 1 |
| Expired balance | Auto-converted to クローバー at close, official | Auto-converted to Clovers, official notice item 2 |
| Premium currency | ジュエル (single 150, 10-pull 1500, daily limited single 50 有償) | carats (name official; per-pull cost ❌ UNVERIFIED, no official web page found) |

**Advertised rates.** Standard structure on both servers, computed from GameTora weights and summing to 100.00% on every banner sampled: ★3/SSR 3.00%, ★2/SR 18.00%, ★1/R 79.00%, with each featured ★3 or SSR at 0.75% and a featured SR support at 2.25%. Newly released R support cards are given a temporary high weight inside the same pool (observed: 2 new R cards at 2.5% each in support gacha 30463, one new R card at 37.5% in 30465). Neither server publishes these numbers on the web notices read in this session; the official pages route the player to the in-app [ガチャ詳細] / Scout Rates tab, so every rate in this document is tier-B arithmetic plus tier-A corroboration and is marked as such. GameWith's JP table states ★★★ 3.0%, ★★ 18.0%, ★ 79.0% independently; the Global tier-A guide states 3% / 18% and a contradictory 97% for ★/R (see Source Conflict Log row 35).

**Guarantee structure.** No ceiling on the ★3/SSR rate by pull count is documented in any source used here; the documented ceilings are these. (1) The exchange path at 200 Pt. (2) A forced ★3 or SSR on slot 10 of the once-per-account paid 10-pull inside JP select-pickup, themed and commemoration gacha, official (`?id=3457`, `?id=3421`, `?id=3440`); the Global equivalent is ❌ UNVERIFIED. (3) The 10th-pull guarantee applies only to `10回引く!(有償)`, and the official JP notice is explicit that the normal paid 10-pull does not carry it.

**Free pulls and campaign Scouts.** `[JP]` cheapest entry is the 1日1回限定 pull at 50 有償ジュエル (official). Tickets exist for both tracks (育成ウマ娘ガチャチケット, サポートカードガチャチケット, official). `[Global]` an official notice titled "Up to 100 free scouts! Daily Free 10x Scout has begun!" exists at `https://umamusume.com/news/901` (found via search, title only, body not read in this session, ⚠️ STALE: date not confirmed, launch-era 2025 is likely). Any login-bonus Scout on Global therefore needs a fresh read of that page before it is published.

**How to re-check (4.4).** Official pages: `https://umamusume.jp/news/detail?id=3457` (exchange Pt, no-carryover, Clover conversion, guaranteed 10th pull, currency names) and `https://umamusume.com/news/1052/` (Global equivalents in English). Wiki pages: `https://gamewith.jp/uma-musume/article/show/257332` (JP rates and the 200-pull exchange statement) and `https://game8.co/games/Umamusume-Pretty-Derby/archives/538219` (Global exchange and Clover mechanics, record its last-updated stamp because the copy on hand predates the anchor by 13 months).

### 5.5 Banner history, 2026-08-28 to 2026-09-27

Every row is a GameTora record whose id chain resolved to a name, cross-checked against at least one other domain where the check exists. `[JP]` timestamps are JST, `[Global]` timestamps are UTC. "2nd source" names the corroborating domain.

#### `[JP]` (12 resolved records overlapping the window, plus 1 unresolved)
| Gacha id | Type | Window (JST) | Featured (resolved via id chain) | 2nd source | Status |
|---|---|---|---|---|---|
| 30462 char | pickup | 2026-08-24 12:00 -> 2026-09-26 11:59 | エピファネイア (114101, JP release 2026-08-24) | [A] GameWith window 8/24-9/26; the matching official notice sits on page 5 or later of the JP index, which was not read in this session | Ended 09-26 |
| 30463 support | 5.5th Anniv. support | 2026-08-24 12:00 -> 2026-09-26 11:59 | エフフォーリア (30311) and ミスターシービー (30312), plus new R cards エフフォーリア (10146) / ルーラーシップ (10147) at 2.5% each | [A] GameWith "5.5thAnnivサポートカードガチャ" same window | Ended 09-26 |
| 30464 char | pickup | 2026-08-31 12:00 -> 2026-09-11 11:59 | シンコウウインディ (104302) and フェノーメノ (112702) | [S] news index 2026-08-31 12:00 "育成ウマ娘&サポートカード新登場" (id 3419); date is local-only | Ended 09-11 |
| 30465 support | pickup | 2026-08-31 12:00 -> 2026-09-11 11:59 | ハルウララ (30314) and ビコーペガサス (30315), plus new R サムソンビッグ (10148) at 37.5% | same official index line; end date local-only | Ended 09-11 |
| 30466 char | Twinkle Collection | 2026-09-01 12:00 -> 2026-10-01 11:59 | 8 ★3, all resolved, see 5.1 | [S] `?id=3421`, [A] GameWith | Active |
| 50245 support | SSR確定パワー | 2026-09-01 12:00 -> 2026-10-01 11:59 | 15 power SSR, all resolved | [S] `?id=3421`, [A] GameWith | Active |
| 30468 char | pickup | 2026-09-11 12:00 -> 2026-09-18 11:59 | ファレノプシス (114901, JP release 2026-09-11) | [S] news index 2026-09-11 12:00 (id 3439) and 2026-09-10 予告 (id 3438); end date local-only | Ended 09-18 |
| 30469 support | pickup | 2026-09-11 12:00 -> 2026-09-18 11:59 | SSR マチカネタンホイザ (30317) and SR ゼンノロブロイ (20101) | same official line; end date local-only | Ended 09-18 |
| 50246 char | ★3確定 凱旋門賞 | 2026-09-11 12:00 -> 2026-10-13 11:59 | 9 ★3, all resolved | [S] `?id=3440` | Active |
| 50247 support | SSR確定 凱旋門賞 | 2026-09-11 12:00 -> 2026-10-13 11:59 | 6 SSR, all resolved | [S] `?id=3440` | Active |
| 30470 char | pickup | 2026-09-18 12:00 -> 2026-09-30 11:59 | ローズキングダム (114401) | [S] `?id=3457`, [A] GameWith, [A] Kamigame (lead's read: to 2026-09-30 12:00) | Active |
| 30471 support | 5.5th Anniv. select pickup | 2026-09-18 12:00 -> 2026-09-30 11:59 | player-selected pair, local ids 301/302 ❌ UNVERIFIED by design; 10-candidate list is official | [S] `?id=3457`, [A] GameWith | Active |
| ❌ no id resolved | support | 2026-08-24 12:00 -> end date not captured | "SSRセレクトステップアップガチャ", 10 player-chosen SSR released by 2026-07-31 | [A] GameWith only; no matching record in any data export | ❌ UNVERIFIED |

#### `[Global]` (10 records overlapping the window, UTC)
| Gacha id | Type | Window (UTC) | Featured (resolved via id chain) | 2nd source | Status |
|---|---|---|---|---|---|
| 30120 char | Spotlight | 2026-08-25 22:00 -> 2026-09-02 21:59 | Aston Machan (108701, `release_en` 2026-08-25) | ❌ single source (GameTora + same-vendor release field) | Ended 09-02 |
| 30121 support | Spotlight | 2026-08-25 22:00 -> 2026-09-02 21:59 | Fine Motion (30010) and Maruzensky (30107) | ❌ single source | Ended 09-02 |
| 30122 char | Spotlight | 2026-09-01 22:00 -> 2026-09-10 21:59 | Yamanin Zephyr [Fluttertail Spirit] (107801) | [A] Game8 "Yamanin Zephyr ... Sep. 1 - 10" | Ended 09-10 |
| 30123 support | Spotlight | 2026-09-01 22:00 -> 2026-09-10 21:59 | SSR Symboli Kris S (30118) and SR Tsurumaru Tsuyoshi (20052) | [A] Game8 same row names both supports | Ended 09-10 |
| 30124 char | Spotlight | 2026-09-07 22:00 -> 2026-09-19 21:59 | Tamamo Cross (102102) and Inari One (103402) | [A] Game8 "Tamamo Cross and Inari One ... Sep. 7 - 19" | Ended 09-19 |
| 30125 support | Spotlight | 2026-09-07 22:00 -> 2026-09-19 21:59 | Oguri Cap (30146) and Yaeno Muteki (30120) | [A] Game8 same row | Ended 09-19 |
| 30126 char | Spotlight | 2026-09-15 22:00 -> 2026-09-23 21:59 | Nakayama Festa [Dramatic Turnabout] (104901) | [S] `umamusume.com/news/1040/` 2026-09-15 22:00 UTC; [A] Game8 "Nakayama Festa ... Sep. 15 - 23" | Ended 09-23 |
| 30127 support | Spotlight | 2026-09-15 22:00 -> 2026-09-23 21:59 | Super Creek (30016) and Mr. C.B. (30097), both reruns | [S] `news/1040/`, [A] Game8 same row | Ended 09-23 |
| 30128 char | Spotlight | 2026-09-23 22:00 -> 2026-10-04 21:59 | Wonder Acute [Butterfly Sting] (110001) | [S] `news/1052/` (window quoted in UTC), [A] Game8 | Active |
| 30129 support | Spotlight | 2026-09-23 22:00 -> 2026-10-04 21:59 | SSR Eishin Flash (30122) and SR Narita Top Road (20053) | [S] `news/1052/`, [A] Game8 | Active |

Cadence read from the table: Global ran 5 character Scout pairs and 5 support Scout pairs in 31 days (about one pair every 6 to 8 days, windows overlapping), while JP ran 4 debut pickup pairs plus 4 themed or guaranteed special gacha in the same span. The Global count is consistent with the Game8 schedule page's own description of adjusting Japanese release timing to the English server's average monthly rollout pace.

**How to re-check (4.5).** Official: JP `https://umamusume.jp/news/?t=game` paging back to `?t=game&p=4` covers 2026-08-30 (each pickup debut has a 予告 notice the day before and an 開催 notice on the day); Global `https://umamusume.com/news/` pages back through the 2026-09-15 22:00 UTC items. Wiki: `https://gametora.com/umamusume/gacha` and `https://gametora.com/ja/umamusume/gacha` for the raw per-banner windows, plus `https://gamewith.jp/uma-musume/article/show/257332` for JP and `https://game8.co/games/Umamusume-Pretty-Derby/archives/537125` for Global. Rebuild the tables from the exports (`gacha/char_banners`, `gacha/support_banners`); the `is_estimated` flag lives only in the `en/foresight/timeline` records, so keep it there rather than looking for it in the banner files: every `true` row is a prediction and must not be pasted into a history table.

---

## Section 6: Server Terminology Map

One concept per row, `[JP]` client wording against `[Global]` client wording. The Global column is client text: `name_en_gl` / `desc_en_gl` in `items.json`, `nicknames.json` and `factors.json`, the `originalText` of the `en/*` mission datasets, or the official Global notices. The last column flags whether the two servers share the concept or only share a row.

| Concept | `[JP]` wording | `[Global]` official English | Equivalent? | Evidence |
|---|---|---|---|---|
| Gacha system | ガチャ (育成ウマ娘ガチャ, サポートカードガチャ) | Scouts: "Pretty Derby Scout", "Support Card Scout", "Spotlight Pretty Derby / Spotlight Support Card Scouts" | Same concept, different word | [Global index](https://umamusume.com/news/), `items.json` ids 41, 111 |
| Featured / pickup tag | ピックアップ, セレクト | "Spotlight", "Select" | Naming only | [Global index](https://umamusume.com/news/), [JP index](https://umamusume.jp/news/?t=game) |
| Pull currency | ジュエル | Carats | Same concept | `items.json` id 43: 「虹色に輝く…ニンジン型の宝石」 vs "A rainbow-colored jewel in the shape of a carrot" |
| Per-pull exchange currency | 育成ウマ娘交換Pt / サポートカード交換Pt | Trainee Exchange Points / Support Card Exchange Points | Same concept, different wording | [JP official news id 3457](https://umamusume.jp/news/detail?id=3457) and the Global Scout notice; neither string appears in `items.json`, whose ids 1001 and 110 are the Dream Team event currency and the old support-card level currency |
| Card max-limit item and verb | 虹の解放結晶, 金の解放結晶, 虹の結晶片, 金の結晶片; JP slot term 限界突破 | "Rainbow Uncap Crystal", "Gold Uncap Crystal", "Rainbow Crystal Shard", "Gold Crystal Shard"; verb "Uncaps an SSR Support Card" | Same concept; Global verb is Uncap | `items.json` ids 144, 145, 149, 150 |
| Card shop amulet | 虹の蹄鉄 / 金の蹄鉄 / 銀の蹄鉄 (fan English exports render this name with an equine noun) | "Rainbow Cleat", "Gold Cleat", "Silver Cleat" | Same concept; **do not key on the fan English string**. These are a **shop currency**, not a training buff: 1 duplicate SSR card → 10 Rainbow Cleats, spent in "Cleat Exchange". See 1.6.10 | `items.json` ids 48, 49, 50; [game8.co 2026-03-12](https://game8.co/games/Umamusume-Pretty-Derby/archives/543930) |
| Trainee exchange statue | 女神像 | "Goddess Statue" | Same concept; **currency only, no buff**. Exchanged for Trainee Star Pieces in the "Statue Exchange" shop on an escalating ×1→×5 rate; 650 pieces maxes a trainee, and statues "cannot be used to unlock an unscouted Trainee" | [game8.co 2026-03-12](https://game8.co/games/Umamusume-Pretty-Derby/archives/542870) |
| Trackblazer race item | JP string ❌ not verified in this pass | "Artisan Cleat Hammer" (25 coins), "Master Cleat Hammer" (40 coins) | **Name-collision hazard:** a *Hammer* is a one-turn Race Bonus purchase in the Trackblazer shop, not the Cleat currency above | `docs/scenarios/05-trackblazer-gametora.md:114-115` |
| Support card rarity | R / SR / SSR plus ★1 to ★3 | R, SR, SSR plus "3★", "SR+", "SSR" | Same | `items.json` ids 113 to 115, 130; `support-cards.json` rarity 1/2/3 |
| Support card types | スピード, スタミナ, パワー, 根性, 賢さ, 友人, グループ | Speed, Stamina, Power, Guts, Wit, Pal, Group | 友人 to "Pal" is a label change; the JP export holds 23 friend-type rows and game8.co's Pal page lists the subset live on Global (Light Hello, Sasami Anshinzawa, Riko Kashimoto, Tazuna Hayakawa, Aoi Kiryuin, Wallflower, Corner Recovery) | `support-cards.json` type keys and `release_en`, [game8.co 2026-08-12](https://game8.co/games/Umamusume-Pretty-Derby/archives/537276) |
| Inheritance factor | 因子 (因子研究レポート, 因子強化) | Spark ("Spark Research Report", "Can be used for Spark Enhancement", "Carnival Bonus Spark") | Same concept, different word | `items.json` id 195, `en/missions/racingcarnival-limited` |
| Character with a finished Career | 殿堂入りウマ娘 | Veteran Umamusume | Same concept | [Global 1050](https://umamusume.com/news/1050/), [Global 1058](https://umamusume.com/news/1058/) |
| Character being trained | 育成ウマ娘 | Trainee Umamusume (ticket text: "Trainee Scout Ticket") | Same concept | `items.json` id 41 |
| ウマ娘 inside titles | ウマ娘 | "Umamusume" in body text, "Runner" in titles (レイニーウマ娘 → "Rainy Runner") | Naming only | `nicknames.json` id 1 |
| Race event / limited race mode | レースイベント | "race event" | Same concept | [Global 1050](https://umamusume.com/news/1050/) |
| Champions Meeting edition name | category tag: SPRINT / MILE / CLASSIC / LONG / DIRT (post-2023-06) | zodiac cup: "Champions Meeting: Scorpio Cup" | NOT equivalent: `[Global]` runs the series `[JP]` retired in 2023; join on `resource_id` | `events__champions-meeting.json`, `en/events/champions-meeting`, [JP 3463](https://umamusume.jp/news/detail?id=3463) |
| CM brackets | グレードリーグ / オープンリーグ; A・Bグループ; チームランク E2; 育成ランク | Graded League / Open League; Group A/B; Team Rank E2; Career Rank | Concept split: Open League caps at [UC] on `[JP]` and at A+ on `[Global]` | [JP 3463](https://umamusume.jp/news/detail?id=3463), [Global 1050](https://umamusume.com/news/1050/) |
| Four distances | 短距離 / マイル / 中距離 / 長距離 | Sprint / Mile / Medium / Long | Same | `factors.json` ids 31 to 34 `name_en_gl`, `items.json` ids 1, 4, 7, 10, Global race line "2,200m (Medium)" |
| Fifth race category | ダート (a surface, not a distance), 芝 | Dirt, Turf | Same concept; Team Trials and Champions Meeting list Dirt beside the four distances as one of five categories | `factors.json` ids 11, 12; `items.json` ids 1, 4, 7, 10, 13 (five "Racing Shoes", the Dirt pair included) |
| Four running strategies | 作戦: 逃げ / 先行 / 差し / 追込 | Front Runner / Pace Chaser / Late Surger / End Closer | Same concept; one wiki labels 追込 "Runner" (conflict row 25) | `factors.json` ids 21 to 24 `name_en_gl`, `nicknames.json` Global text "as a Front Runner or Pace Chaser" |
| Five stats | スピード / スタミナ / パワー / 根性 / 賢さ | Speed / Stamina / Power / Guts / Wit | Same; export key for 賢さ is `intelligence` | `factors.json` ids 1 to 5 `name_en_gl`, [game8.co 2026-09-25](https://game8.co/games/Umamusume-Pretty-Derby/archives/536352) |
| Friendship training | 友情トレーニング / 友情ボーナス | Friendship Training / Friendship Bonus | Same | `en/missions/limited`, `support_effects.json` id 1 (`*_eon` field) |
| Team race mode | チーム競技場, ウイニング報酬, チーム編成 | Team Trials, Winning Reward, team setup | Same concept; "Team Stadium" is neither server's word | `missions/daily`, `en/missions/daily`, [game8.co 2026-09-23](https://game8.co/games/Umamusume-Pretty-Derby/archives/536831) |
| Self-directed idle training | 自主トレ育成 | "Independent Training" (client string present, release unconfirmed) | NOT equivalent until Global release is confirmed | `nicknames.json` id 394 `name_en_gl` |
| Team-building limited event | 目指せ！最強チーム, スカウトレース | "Aim for the Stars! Dream Team", "Scout Race" | Same concept; do not confuse スカウトレース with Team Trials | `items.json` ids 1001, 2001 |
| Trophy / rank currency | 栄誉のメダリオン | not present in the Global strings read here | `[JP-Only]` for now | `items.json` id 268, [JP 3463](https://umamusume.jp/news/detail?id=3463) |
| Monies and club currency | マニー / トレーナーメダル / ルーレットコイン | Monies / Club Points / Prize Coin | Same | `items.json` ids 59, 134, 45 |

**Sources:** data export datasets `items.json`, `factors.json`, `nicknames.json`, `support-cards.json`, `events__champions-meeting.json`, `en/events/champions-meeting`, `missions/*` and `en/missions/*` (tier B, fetched 2026-09-27); [Umamusume Global Official News index and items 1050, 1058](https://umamusume.com/news/); [Umamusume JP Official News index and items 3363, 3453, 3463, 3476](https://umamusume.jp/news/?t=game); [game8.co Global guides, 2026-08-12, 2026-09-23, 2026-09-25](https://game8.co/games/Umamusume-Pretty-Derby/archives/536831).

## Section 7: Source Attribution and Conflict Log

Consolidated from the five research drafts. One row per disagreement between sources that survived review.

| # | From | Data Point | Source A | Source B | Conflict | Resolution | Confidence |
|---|---|---|---|---|---|---|---|
| 1 | 1.1/1.3 | Daily limit on 自主トレ育成 runs | [Kamigame (2026-07-20)](https://kamigame.jp/umamusume/page/429951462450165905.html): 20 runs/day, sharing the 2025 cap on free jewels from training race rewards | [Game8 (2026-06-30)](https://game8.jp/umamusume/794628): no daily cap documented for the mode | One documents a cap on the mode, the other documents none on the mode itself | Recorded as a cap on jewel payout from training race rewards rather than on 自主トレ育成 entries; plan on the jewel limit binding, not the run count | Medium |
| 2 | 1.1/1.3 | Can 賢さ / Wit training fail? | [GameWith (2023-02-25, ⚠️ STALE)](https://gamewith.jp/uma-musume/article/show/257432): failure listed for the four energy-spending disciplines, with a separate Wit failure branch recorded | [Game8 (2026-09-10)](https://game8.jp/umamusume/372572): Wit consumes no Energy, so the driver of failure risk is absent | Ambiguous whether Wit is exempt or merely very low risk | Text says Wit is the low-risk filler turn, not a risk-free one; treat failure as possible but rare, until a current measurement exists | Low |
| 3 | 1.1/1.3 | Highest achievable Speed in a current `[JP]` run | [GameWith (2026-09-26)](https://gamewith.jp/uma-musume/article/show/575798): advanced target 2100 | [GameTora data export, `scenarios.json`](https://gametora.com/data/umamusume/scenarios.61b7c51c.json): hard cap 2000 per stat; [Game8 (2026-09-24)](https://game8.jp/umamusume/372949) puts the full-spurt gate just past 2000 | A published target exceeds the recorded ceiling | **Reconciled 2026-09-27, not a contradiction.** The two figures measure different things: 2100 is Beyond Dreams' Speed **scenario cap** (`1200 + 900` from `scenarios.json.stats`), and 2000 is the `hard_caps` database ceiling of the twelve older scenarios, which is 2500 on Beyond Dreams itself. A 2100 target therefore sits comfortably under its own ceiling, and §2.6 carries the arithmetic. Residual, and it is a small one: the GameWith page's own scenario context was not captured, so if its 2100 was meant against an older scenario then the ceiling has moved since the export. Do not build to 2100 on `[Global]`, where no scenario cap exceeds 1900 | Low |
| 4 | 1.1/1.3 | Display name of the fifth stat | game8.co Global guide and GameTora export: Wit | Local client effect strings alternate "Wisdom Bonus" and "Intelligence Limit Up", and the support-card type key is `intelligence` ([GameTora data export, `support_effects.json` ids 7 and 24, plus `support-cards.json`](https://gametora.com/data/umamusume/support_effects.ca447e53.json)) | Two labels for the same stat inside Global assets | Player-facing text uses Wit; the internal key and one localization pass use intelligence/Wisdom. Noted so future exports are not read as two stats | High |
| 5 | 1.1/1.3 | Scenario caps for Grand Masters and L'Arc | [Kamigame (2024-02-19, ⚠️ STALE)](https://kamigame.jp/umamusume/page/225422194069532204.html) | [Game8 (2025-11-21, ⚠️ STALE)](https://game8.jp/umamusume/475668) does not list them | Single-source rows in the cap table | Kept with an explicit single-source label; both sources are already stale-flagged, so re-verify before using them in a build planner | Medium |
| 6 | 1.1/1.3 | Bond gauge scale for friendship training | [Game8 (2025-11-21, ⚠️ STALE)](https://game8.jp/umamusume/454202): four visible segments, orange at value 80 | [Kamigame (2022-12-19, ⚠️ STALE)](https://kamigame.jp/umamusume/page/149493615532457367.html): threshold value 80, +5 per joint training | Segment count is described, the underlying 0 to 100 scale is implied | Both agree on the number that matters (80) and on the type-match requirement; the visible segment count is reported as four per Game8 without an independent second count | Medium |
| 7 | 1.2 | Band for 1400 m courses | data export `racetracks.json`, all 1400 m courses carry `distance` code 2, and `skills.json` gates "(Mile)"-tagged skills on `distance_type==2` | [Umamusume Wiki Career Mode, 8 September 2026](https://umamusu.wiki/Game:Career_Mode): Short is "1000m 1200m 1400m" | Engine code says Mile, wiki says Short | Table shows the engine code; treat the Short reading as a naming convention. Verify in-game before using 1400 m rows in a Sprint or Mile planner | Medium |
| 8 | 1.2 | Band for the `[JP]` SPRINT-named round | data export `events__champions-meeting.json` id 29 is named SPRINT at 1400 m | same export, course table gives that 1400 m layout `distance` code 2 (Mile) | One round is marketed as Sprint while the engine files it as Mile | Event naming is not the engine band; both kept, and the row is cited as evidence for the label gap in row 1 | High |
| 9 | 1.2 | Number of running styles | data export, `running_style` takes only 1 to 4, and 「大逃げ」 (Runaway) is gated behind `running_style==1` | [game8.co 543935 (2025.10.05, stale)](https://game8.co/games/Umamusume-Pretty-Derby/archives/543935) and [Umamusume Wiki Career Mode (8 September 2026)](https://umamusu.wiki/Game:Career_Mode) list Runaway alongside the four | Wiki counts a fifth style | Four labels plus a Front Runner state. Table lists four; Runaway is documented as a skill | High |
| 10 | 1.2 | Open League rank ceiling | [Umamusume Global Official News 1050 (2026.09.19)](https://umamusume.com/news/1050): A+ or below, S or above excluded | [Umamusume JP Official News 3453 (2026.09.18)](https://umamusume.jp/news/detail?id=3453): 「育成ランク[UC]まで」, UC1 and above excluded | Different ceilings on the two servers | Not merged: each kept under its own server tag in 1.2.6 | High |
| 11 | 1.2 | Footing label for code 4 | data export `skills.json` 200161 `desc_en`: "good, soft, and heavy ground" for `ground_condition` 2, 3, 4, and [Umamusume Wiki Mechanics (4 July 2026)](https://umamusu.wiki/Game:Mechanics): Heavy | [Kamigame rain page (2023.06.06, stale)](https://kamigame.jp/umamusume/page/182514818417216449.html) renders the four as good, slightly heavy, heavy, bad | Two English renderings of the same `[JP]` set, 良/稍重/重/不良 | [Global] labels taken from client copy (Firm, Good, Soft, Heavy); `[JP]` terms kept separately. The stale page is treated as `[JP]` prose only | High |
| 12 | 1.2 | Tier label for grade code 700 | [Umamusume Wiki Career Mode (8 September 2026)](https://umamusu.wiki/Game:Career_Mode) lists five tiers: Pre-OP, OP, G3, G2, G1 | data export has codes 100, 200, 300, 400, 700 with no label field | Tier count matches, code map does not exist in either source | Code 100 and 400 pinned from client copy; 700 recorded as ❌ UNVERIFIED in 1.2.6 rather than guessed | Medium |
| 13 | 1.4/1.5 | `[JP]` card levels and Support Pt | [Famitsu](https://www.famitsu.com/article/202508/51020) 2025-08-29 and [4Gamer](https://www.4gamer.net/games/414/G041434/20251007025/) 2025-10-07: removed on 2025-10-07 | [GameWith](https://gamewith.jp/uma-musume/article/show/293366) 2026-09-14 nav still links level and Support Pt guides | Site navigation still advertises the deleted grind | Removed on `[JP]`, three outlets cite the official notice; kept as the live model on `[Global]`, where official material names Uncap Crystals | High |
| 14 | 1.4/1.5 | Level gain per break on `[Global]` | [Kamigame](https://kamigame.jp/umamusume/page/146591481853953911.html) 2021-10-13: +5 per break, four breaks, plus a "up to five releases" line in the same paragraph | No second source | The page contradicts its own break count and is five years old | Only the four-break and five-copy facts kept, confirmed by Game8 MLB/4LB; level figures flagged ❌ UNVERIFIED | Low |
| 15 | 1.4/1.5 | Yellow and orange stages | [Game8 JP](https://game8.jp/umamusume/454202) 2025-11-21 and [Game8 Global](https://game8.co/games/Umamusume-Pretty-Derby/archives/542672) 2025-08-07: gauge turns orange at 80 | No source colors the hint indicator | The brief expected the colors to mark hint stages | Color change reported where sourced, on the gauge; hint coloring flagged ❌ UNVERIFIED | Medium |
| 16 | 1.4/1.5 | 10% cost cut per hint level | [Game8 JP](https://game8.jp/umamusume/442505) 2026-09-10: 30% at Lv3, quoted from client-adjacent text | [GameWith](https://gamewith.jp/uma-musume/article/show/274990) 2022-05-18 covers payout, not the discount; [umamusu.wiki Game:Skills, rev. 2026-08-30](https://umamusu.wiki/Game:Skills) prints **~8% per hint, max 40%**, plus a separate **Fast Learner −10%** | A second domain now disagrees rather than merely being silent, and the two figures cannot both describe one quantity — 40% is not reachable at 10%/level capped at Lv3 | Keep 10%/level and −30% at Lv3 as the published value because it is quoted from JP client text and matches the Lv3 ceiling; treat the wiki's 8%/40% as possibly folding in Fast Learner. Do not encode either as a multiplier without an in-client check | Medium |
| 17 | 1.4/1.5 | `[Global]` top-tier ranking | [Game8](https://game8.co/games/Umamusume-Pretty-Derby/archives/536715) 2026-09-23 ranks seven SS cards | `[B]` `support-cards.json` confirms each exists on `[Global]`, ranks nothing | No second ranking source inside the approved list | Tier claims kept with the Game8 date plus the availability cross-check; ranking stays one-source | Medium |
| 18 | 1.4/1.5 | Top-grade starts per farming run | [Kamigame factor loop](https://kamigame.jp/umamusume/page/147871627357506165.html) 2025-06-05: 15 or more | [Kamigame double circle](https://kamigame.jp/umamusume/page/144421049306500950.html) 2025-06-05: 20 or more | Same site, two target counts | Number dropped; only the rule that shared wins raise compatibility is used | Low |
| 19 | 1.4/1.5 | Factor slots on a finished Umamusume | [Kamigame](https://kamigame.jp/umamusume/page/154134787475434233.html) 2025-06-05: count follows stats, aptitudes, skills | [Umamusume Wiki](https://umamusu.wiki/Game:Inspiration) 2025-11-24: same, no maximum | Neither states a slot number | Written as ❌ UNVERIFIED with the qualitative rule kept | Low |
| 20 | 1.4/1.5 | Group card behavior | `[B]` `support-cards.json` and `training_events__group.json`: distinct type, friendship plus event kit, six-member event chain | [GameWith](https://gamewith.jp/uma-musume/article/show/352669) 2026-09-14: type and five cards confirmed, no rule stated | Type agreed, special behavior undocumented | Only the effect profile and the multi-member event chain asserted | Medium |
| 21 | 1.6/1.7 | What MILE and CLASSIC name on `[JP]` | JP headlines read as distance-class naming (my earlier inference) | `events__champions-meeting.json` shows 24 zodiac cup rows then tag rows; tag maps 1:1 to `resource_id` 13 to 17; CLASSIC edition's own race line says 2400m（中距離） | Headlines do not mean the client distance class | Tags are Champions Meeting category labels, CLASSIC being the 2000 to 2400m band; Global never took them, it runs the zodiac series JP retired | High |
| 22 | 1.6/1.7 | Open League eligibility | [JP 3463](https://umamusume.jp/news/detail?id=3463): 育成ランク[UC] and below, [UC1] blocked | [Global 1050](https://umamusume.com/news/1050/): Career Rank A+ and below, S and above blocked | Same bracket name, different ceiling | Keep both rules per server, do not merge; the JP rank ladder extends past S | High |
| 23 | 1.6/1.7 | CM entry economy | [JP 3453](https://umamusume.jp/news/detail?id=3453) and [JP 3463](https://umamusume.jp/news/detail?id=3463): every entry costs 1 Entry Ticket or 30 Carats, 3 free tickets per day from the event top, 4 entries per day | [Global 1050](https://umamusume.com/news/1050/): first daily entry free, later entries cost a "<Cup> Entry Ticket" or 30 carats, 4 per day, 2 tickets per day from missions | Free-entry model and ticket naming differ | Model per server; Global tickets are edition-scoped ("Scorpio Cup Entry Ticket"), JP uses one generic Entry Ticket | High |
| 24 | 1.6/1.7 | CM special rules | [JP 3463](https://umamusume.jp/news/detail?id=3463) applies デバフなし over ~50 named skills; [JP 3453] applies none | [Global 1050](https://umamusume.com/news/1050/) blocks Night Races, Sharp Turns, Collaborative Graded Races | Rule sets are edition-specific, not shared | Record the rule per edition; never assume a Global CM mirrors the JP one | High |
| 25 | 1.6/1.7 | Label for 追込 | `factors.json` id 24 `name_en_gl`: "End Closer" | [game8.co, 2026-09-25](https://game8.co/games/Umamusume-Pretty-Derby/archives/536352) lists the fourth tactic as "Runner" | Two English labels for one strategy | Use the client string "End Closer"; treat "Runner" as wiki-side wording, re-check against the Global build before mapping aptitude columns | Medium |
| 26 | 1.6/1.7 | Transfer Request rewards | [Global 1058](https://umamusume.com/news/1058/): Star Pieces, Monies, Support Points | [game8.co, ⚠️ STALE 2025-10-05](https://game8.co/games/Umamusume-Pretty-Derby/archives/554781): Star Shards, Dream Glimmer, Hint Books, currency | Item sets differ | Official window notice wins; the wiki list is a broader shop-level view of the same system and is stale | Medium |
| 27 | 1.6/1.7 | Whether Transfer Requests are new on 2026-09-24 | [Global index](https://umamusume.com/news/): "coming soon!" 2026-09-23 then "now available!" 2026-09-24 | game8.co guide on the same mechanic dated 2025-10-05 | A guide predates the "new" notice | Read 2026-09-24 as a scheduled event window, not first release; the system existed on Global earlier or the guide is backported | Medium |
| 28 | 1.6/1.7 | Daily cap on 自主トレ育成 | [Kamigame, 2026-07-20](https://kamigame.jp/umamusume/page/429951462450165905.html): 20 runs per day against the free-Carat cap | [Game8, 2026-06-30](https://game8.jp/umamusume/794628): no cap documented | Cap on the mode or on the Carat payout | Model it as a cap on Carats from training race wins, which binds the run count; matches the sibling section 1.1.7 log | Medium |
| 29 | 1.6/1.7 | Racing Carnival currency of `[JP]` editions | `missions/racingcarnival-*`: no window after 2024-10-20; [Game8, 2026-04-14](https://game8.jp/umamusume/421662) last run ended 2024-10-20 | `en/missions/racingcarnival-*`: Global editions 2026-02 and 2026-04 | JP looks dormant while Global is running | Tag the mode `[Global]` live / `[JP]` dormant; absence of newer JP rows is not proof of retirement, re-check before building a JP Carnival calendar | Medium |
| 30 | 1.6/1.7 | Global Transfer Requests notice content | the live-ops research pass, its row 5: same URL reported with no closing time and no reward table | This session's browser read of [Global 1058](https://umamusume.com/news/1058/): both present, 09-28 21:59 UTC close plus a three-item reward list | Two reads of one page | The full render is the record; the live-ops section's `❌ UNVERIFIED` on those two fields can be closed | High |
| 31 | 1.6/1.7 | `[Global]` name of the third scenario | `scenarios.json` `name_en`: "Trackblazer: Start of the Climax" | `en/missions/trainerexam-limited` `originalText`: "Twinkle Star Climax"; `factors.json` 30003 `name_en_gl`: "TS Climax Scenario" | Export label vs client campaign label | Do not assert one Global scenario name; keep the client strings "Twinkle Star Climax" and the export label separate, `❌ UNVERIFIED` for the official title | Low |
| 32 | 1.6/1.7 | Support card rarity ceiling | `support-cards.json`: rarity values 1, 2, 3 only | [game8.co Global tier filter](https://game8.co/games/Umamusume-Pretty-Derby/archives/537276) lists R, SR, SSR, UR | A fourth rarity with no client backing | Treat UR as a wiki label, not a client rarity; enum R/SR/SSR | Medium |
| 33 | 1.6/1.7 | Training Pass reward values and Global status | [JP 3455](https://umamusume.jp/news/detail?id=3455) only | no second domain found this session | Single-source mode | Keep the notice for the period, premium price and entry path; leave the reward table and any Global release as `❌ UNVERIFIED` | Medium |
| 34 | 3/4 | Next Global Scout pair start | GameTora `en` record gacha 30132, `is_estimated: true`, `display_start` 2026-09-30 11:25 UTC | Game8 schedule page updated 2026-09-17, "Sep. 28 - Oct. 12, 2026" | Two-day gap, and both are projections | Neither published as fact; both shown in 5.3a as `[SPECULATION]`. Trusted slightly more on the Game8 date because it also matches the story event's confirmed 2026-09-28 22:00 UTC opening, which is the usual pairing | Low, by design |
| 35 | 3/4 | Global ★1/R rate | GameTora weights for `en` 30128 sum to ★1 79.00%, ★2 18.00%, ★3 3.00% (total 100.00%) | `game8.co/.../archives/538219` (dated 2025-08-08) lists ★★★ 3%, ★★ 18%, ★ 97% | Game8's three figures sum to 118%, so at least one is a pity-row value printed as a base rate | Publish 79.00% for ★1/R. Rationale: the weight arithmetic closes at exactly 100.00% on all 24 Global records sampled for this draft and on the JP records sampled, and 79% matches the independent GameWith JP table. Game8 copy also predates the anchor by 13 months | High |
| 36 | 3/4 | Global 200 Exchange Point cost | `game8.co/.../archives/538219` states 200 Points for the featured unit, 1 per roll | `umamusume.com/news/1052/` names the currencies and the no-carryover and Clover rules but gives no number | Not a contradiction, a coverage gap: the number has no S-tier source | Cost published as tier-A with a `⚠️ STALE` flag; currency names and rules published as official | Medium |
| 37 | 3/4 | JP Masters Challenge, Training Pass and 対戦レースイベント items | `umamusume.jp/news/?t=game` and detail 3363 | no second domain found in the pages read this session | Single-domain evidence for three active or recurring items | Logged, kept in 4.1 and 3.4 with the gap stated, no dates invented. The GameWith event page and the Kamigame event list are the two named re-check targets | Medium (official source is authoritative for its own dates; only breadth is missing) |
| 38 | 3/4 | Global Transfer Requests close date and rewards | `umamusume.com/news/1058/` (2026-09-24 22:00 UTC) | no second domain | The notice carries no closing time and no reward table on the web | Published with `❌ UNVERIFIED` on both fields rather than inferring from JP | High confidence in the gap being real |
| 39 | 3/4 | JP SSRセレクトステップアップガチャ | `gamewith.jp/uma-musume/article/show/257332` names it with start 8/24 and a blank end date | no matching record in `gacha__char-standard.json`, `gacha__support-standard.json` or `gacha__special.json` | Either the export is missing the record or GameWith's label refers to one of the ids already listed | Published as a single-source row with `❌ UNVERIFIED` dates and the possibility stated. Not merged into 4.1 | Low |
| 40 | 3/4 | Global story event history | `en__storyevents.json` stops at event_id 1009 (2026-02-05 UTC) | `umamusume.com/news/1024/` and `/news/1076/` cover the March to October 2026 editions | The data export is 7 months behind, so it cannot corroborate any September Global event | Rely on the official notices plus `en/foresight/timeline` (`is_estimated: false` for event 1020); flagged so nobody treats the export as complete | High |
| 41 | 3/4 | JP next story event (record 1057) | `events__story-events.json` gives 2026-09-30 12:00 -> 2026-10-13 11:59 JST with no name | no official notice as of 2026-09-27 02:05 JST | Name and rewards missing, dates single-sourced | Held in 4.3 as a placeholder line, not counted as an announced event | Medium on dates, none on name |
| 42 | 3/4 | Global Scout pair of 2026-08-25 (Aston Machan, Fine Motion, Maruzensky) | GameTora `en` records 30120 and 30121 with the resolved pickups | Game8's schedule page (updated 2026-09-17) starts its list at Sep. 1, and the Global news index only exposed items from 2026-09-15 back | No second domain covers that pair; the `release_en` fields that agree with it come from the same vendor, so they are not independent | Published in 5.5 flagged `❌ single source` rather than dropped, since the record is internally consistent with the neighbouring pairs' 8-day cadence | Medium, breadth gap only |
| 43 | 1.6.10 | An item that guarantees training success | An incoming write-up (`COMPREHENSIVE UX DELIVERABLES.md`) and the client brief for this pass both assert a **guaranteed-training-success consumable named with the equine-noun rendering of 「蹄鉄」**, and "Goddess Statues" as permanent scenario buffs | `[Global]` and `[JP]` item lists read in 1.6.10: 「蹄鉄」 is the **Cleat** **currency** and 「女神像」 the **Statue Exchange** currency, and neither carries a buff effect string | The fan rendering of one item name was read as a different, powerful item; the second was read as a modifier | **Both retired.** Deliberately not re-typed here: naming the fan rendering would add a `C-4` substring hit to tracked text purely to describe a mistake, and the ban reads cleanly without it. The only documented zero-failure levers are Trackblazer's Good-Luck Charm (40 coins, 1 turn), an Extreme Spirit Burst's 0% on its own facility, and support effect Failure Protection (id 27) | High |
| 44 | 1.6.4 | A "cover" mechanic in Team Trials scoring | The same brief: scoring rests on "total team stats, skill coverage across all 5 distances, and specific 'cover' mechanics" | [game8.co 2026-09-23](https://game8.co/games/Umamusume-Pretty-Derby/archives/536831), GameTora's Team Trials scoring tool (2026-09-24) and uma.guide publish position points, per-skill points (rare 1,200 / common 500), target-time and margin bonuses, and state no cover requirement beyond fielding entrants | "cover" is not a mode term; the nearest real quantities are **skill count** and the five-category field | Model the published component table. Do not add a cover flag. "Team Stadium" is likewise neither server's word for the mode (1.6.4) | Medium |
| 45 | 1.2.2/1.3.1 | Whether evolved skills exist on `[Global]` | [umamusu.wiki Game:Skills, rev. 2026-08-30](https://umamusu.wiki/Game:Skills): 「進化スキル」 evolves **gold only** (覚醒 skills at ★3 and ★5) on 1-3 per-trainee conditions, chosen at career completion, "currently exclusive to the Japanese version" | [game8.co Global skill tier list 2026-09-23](https://game8.co/games/Umamusume-Pretty-Derby/archives/536805) carries **no** Evolved category; no official Global notice found as of 2026-09-27 | Global availability is asserted negative by two independent angles but never stated by Cygames | Treat as `[JP-Only]` until a Global notice exists. 1.3.1's Wit-1200 gate already names evolved skills, so the gate text is shared and the *system* is server-split — do not let a reader infer Global evolved skills from that row | Medium |
| 46 | 2.x | `[JP-Only]` Grand Masters goddess bonuses | [umamusu.wiki Game:Grand Masters, rev. 2026-07-28](https://umamusu.wiki/Game:Grand_Masters) EN table: energy reduction → Byerley Turk, event effect → Darley Arabian, chain chance → Godolphin Barb, hint rate omitted | [GameWith (2023-07-17)](https://gamewith.jp/uma-musume/article/show/388788) and [Game8 (2026-04-13)](https://game8.jp/umamusume/510269) agree with each other and with Kamigame: ダーレー = 「体力消費ダウン」, ゴドルフィン = 「ヒント発生率アップ」 + post-training event rate, バイアリー = support-event magnitude + 「連続イベント率」, and **all three share** a 「トレーニング効果アップ」 ladder the wiki row drops | The EN wiki row shifts the mapping across goddesses and omits the shared effect | Follow the JP pair; recorded in `docs/scenarios/08-grand-masters-jp-only.md`. Scenario is `[JP-Only]`, so no Global-facing table or copy depends on this — but the file is in the scenario directory, so conflict 6's import filter now has two files to exclude, not one | High |

## Section 8: Self-Audit Report

Measured against the compiled file on 2026-09-27. Every number here came from a command over the finished document, not from a recollection of writing it.

### 8.1 The review round this document went through

The body was put in front of a fresh-context adversarial reviewer that had not written it, with instructions to try to break it rather than to summarize it. It returned 24 findings: 3 blocking, 11 major, 10 minor, and its verdict was "do not ship". It read all 1,357 lines and re-derived the roster rows, support-card counts and course figures from the data export.

Outcome, per finding class:

| Class | Result |
|---|---|
| 3 blocking | All three were correct and are fixed. Two of them were defects in this document's own generator, not in a researcher's prose. |
| 11 major | 10 accepted and patched; 1 (finding 18) refuted by recompute, and the line was rewritten so the ambiguity cannot recur. |
| 10 minor | 9 patched, 1 (heading em dashes) already handled by the build step. |

The two blocking generator defects, stated plainly because they are the kind of error a reader cannot see:

1. **Server status was asserted at the wrong level.** Section 3.2 lists costume cards, and status was taken from the unit flag. That tagged 61 cards which never shipped on `[Global]` as `[Both]`, inside rows whose own text said the card had no `[Global]` release. Worse, the consistency check that was supposed to catch it tested the same wrong level and reported zero disagreements. Status is now decided per card: 2.1 splits 68 `[Both]` against 67 `[JP-Only]`, and 2.2 splits 37 against 96, with zero rows contradicting themselves.
2. **The strategy labels were invented.** The table captioned `Runner` / `Leader` / `Betweener` / `Chaser` as the `[Global]` client labels. Those four words came from the brief that commissioned this document, not from the game. The client strings in the export are `Front Runner` / `Pace Chaser` / `Late Surger` / `End Closer` (`factors.json` pink ids 21 to 24, where `name_en` equals `name_en_gl`), corroborated by Global objective text such as "Win 6 races as a Front Runner". `Betweener` appears zero times in that client text. The tables now use the client strings and name the community set as the thing that is not the client's.

The refuted finding is worth recording, because a review is not automatically right: finding 18 claimed `spurtStart` sits in phase 2 for only 95 of 138 courses. Recomputed against the export, all 138 place it inside their own `phases` array's phase 2; 95 is the count that also clear the mathematical two-thirds point. The line now states both numbers with their criteria, since the disagreement came from the sentence not saying which it meant.

Pointer drift was the third blocking item, and it was mechanical: consolidating five per-draft conflict logs into one renumbered the rows underneath in-text references to them. Ten pointers were rewritten against the consolidated numbering, and one row inside the log was reworded to stop citing a draft-local number.

### 8.2 Confidence per section

| Section | Confidence | Why that rating |
|---|---|---|
| 1.1 Training System | MEDIUM | Mechanics and gating corroborated across three wikis plus official notices. Per-session gain ranges rest on a GameWith base-value table dated 2023-02-25 and carry `⚠️ STALE`. Two sub-claims sit at LOW: whether Wit training can fail, and the current `[JP]` Speed ceiling. |
| 1.2 Race Mechanics | MEDIUM-HIGH | Numeric codes decoded from client copy plus a wiki, and every course figure in this section was re-derived from the export twice, once by the reviewer and once here. Several magnitudes stay `❌ UNVERIFIED` and some grade-tier labels are unresolved. |
| 1.3 Stat System | MEDIUM | The sum structure (`★4` rows total 500, `★5` total 550, across all 268 cards) is arithmetic over the whole dataset, the strongest evidence in the document. The Speed ceiling is contested 2100 against 2000. |
| 1.4 Support Cards | MEDIUM | Type and rarity distributions from the full export; top-tier lists dated from two publishers per server. `[Global]` per-break level gain and the hint-stage colors are `❌ UNVERIFIED`. |
| 1.5 Inheritance | MEDIUM | Factor categories confirmed against official notice id 3457 and two wikis. Factor slot count on a finished Umamusume and per-run farming starts are LOW. |
| 1.6 Game Modes | MEDIUM | Existence and timing are S-tier official. Whether Masters Challenge, Training Pass and 自主トレ育成 exist on `[Global]` is genuinely unknown rather than merely uncited. |
| 1.7 Terminology Map | HIGH | Built from official page copy on both servers plus client strings. The 追込 label and the third scenario's Global name are the two soft cells. |
| 2 Character Roster | HIGH on the set, the labels and the letters; MEDIUM on dates | 135 units and 268 cards reproduced exactly by an independent A-tier publisher with zero name differences; aptitude order verified 30 of 30 cells against two publishers; labels now taken from client strings. `[Global]` dates still rest on tier B for breadth. |
| 3 Live Operations | HIGH for items tagged active; MEDIUM overall | Every active item on both servers was read from the official feed through a rendering browser, and the three items that had rested on one domain now carry two or three. Gaps remain at the level of one unnamed JP story event. |
| 4 Gacha and Banners | MEDIUM | Windows and lineups resolve through an id chain and are cross-checked. Rates are tier-B arithmetic because neither server publishes them on the web; that is stated where the rates appear, not in a footnote. |

Conflict log disposition: **46 rows** as of 2026-09-27 — this sentence said 42 rows / 13 HIGH / 21 MEDIUM / 8 LOW, and the four added by the mechanics-brief pass changed the shape of the tally rather than just its size. Counted from the table rather than derived: **15 High, 19 Medium, 7 Low** on the strict single-word label, with **five rows carrying compound ratings** ("Medium, breadth gap only", "Low, by design", "Medium on dates, none on name", "High confidence in the gap being real", and one similar), which is why 15+19+7 does not equal 46. Nothing rated LOW is presented elsewhere as settled. ⚠️ This line is the second count in this file that went stale by being written as prose instead of derived from the table; both now say how to re-derive them.

### 8.3 Weakest source coverage

1. **Section 3's per-cell `[Global]` dates.** The 105 dated cards and the 68-unit availability count come from tier B. Kamigame's independent index (2.5) reproduces the roster set exactly but publishes no dates and no Global column, so it cannot test them. This is the document's most load-bearing single-publisher dependency.
2. **Section 5's rate table.** Neither server publishes pull rates on the web; both route the player to an in-app tab, so no amount of searching lifts this above tier B plus tier A.
3. **Section 1.1's training arithmetic.** The gain ranges are three years old against a 2026 client.
4. **Grand Masters and L'Arc scenario caps** appear in one source each (conflict row 5).

### 8.4 Known gaps

Gaps found and closed during the review round: Transfer Requests' end date and reward list (the live-ops section had left them `❌ UNVERIFIED` while the game-modes section had already resolved them from a full read of the same notice, and conflict row 30's ruling had never been applied); Masters Challenge and Training Pass second domains (now Game8 JP and Kamigame, and GameWith's reward-list page respectively); the terminology map's currency row, which had cited two `items.json` records that are the Dream Team event currency and the retired support-card level currency rather than the gacha exchange currencies.

Closed after publication, on 2026-09-27: the `[Global]` mood tier strings in 1.1.6, which were `❌ UNVERIFIED` and are now confirmed from the client's own Mood Effect panel, along with the race-side percentage that Section 2 left as an open conflict. This is the one place in the document where a claim was settled by the client's UI rather than by a publisher, and it is recorded here rather than quietly, because the two wiki camps it contradicts are still live sources elsewhere in these sections.

Gaps that remain, each naming what was tried:

- Whether 賢さ / Wit training can fail at all; sources exist on both sides.
- ~~The current `[JP]` Speed ceiling: 2100 or 2000.~~ Closed 2026-09-27: they are different quantities, a scenario cap and a database ceiling, and the arithmetic reconciles them. See conflict row 3 and §2.6.
- The exact cost reduction per hint level.
- The number of factor slots on a finished Umamusume, and the per-run count of top-grade farming starts.
- Whether Masters Challenge, Training Pass and 自主トレ育成 reached `[Global]`.
- Per-season Training Pass quantities and the season close time, published in-app.
- One `[JP]` story event (record 1057) whose name never resolved through the id chain, and the 「SSRセレクトステップアップガチャ」, which has no record in the export at all.
- The per-pull price on `[Global]`: the currency name is official, the cost is not.
- The `[Global]` launch date 2025-06-26, which is supported by the export minimum plus an indexed official title, that page having returned HTTP 403 to this client. It is recorded as SUPPORTED, single readable source, not as confirmed.

### 8.5 Verification evidence

| Gate | How it was run | Result |
|---|---|---|
| Independent adversarial review | Fresh-context reviewer over the whole body, re-deriving figures from the export | 24 findings, 3 blocking, all addressed or refuted with evidence; verdict moved from "do not ship" |
| Roster recomputation | All 268 rows compared cell by cell against the export, twice by separate parties | Full agreement on names, ten aptitude letters, rarity, both server dates, ★5 ceiling, variant count and `stat_bonus` |
| Roster set equality | Diff against the Kamigame `[JP]` index | 135 against 135 units, 268 against 268 cards, 0 names unique to either side |
| Aptitude order | Three diagnostic units, two publishers each | 30 of 30 cells agree |
| Server-status consistency | Row-level assertion over the finished tables | 0 rows tagged `[Both]` while carrying no `[Global]` release date, after 61 such rows were found and fixed |
| Lore sweep, word boundaries | `gates.py` and `final_measure.py` over the finished document | 23 hits, all of them the document naming the ban list at itself: 10 in the lore policy statement and 13 in this audit's enumeration. 0 hits anywhere else |
| Lore sweep, substrings | same | 30 hits, every one inside a longer word or a proper noun: "desired" in ordinary prose, the Umamusume named Red Desire and its URL slug, "damaged", and the Italian epithet "La dama perfetta" |
| Independent adversarial review note on lore | reviewer's own sweep | 0 confirmed violations; it also recorded two places where this document declined to reproduce an equine string from the export and printed the official wording instead |
| Dead citations | `checklinks.py`, 12 workers, every distinct link target | 0 non-200 across every link in the document |
| Machine-local paths | Absolute-path sweep | 0 references to the build machine's working directory, so the document stands alone |
| Empty table cells and placeholders | `final_measure.py` | 0 empty cells. The only occurrences of TODO, TBD and "data pending" in the file are in this audit's statement that they are forbidden |
| Hedging and AI filler | `final_measure.py` | 0 in the body. Every hit for the forbidden phrases sits in this audit's own list of terms the document forbids |
| Em dash | `final_measure.py` | 5 occurrences, 0 of them prose: 4 inside verbatim skill names in the roster tables, 1 inside the lore policy statement quoting such a name. The five section headings that carried one were rewritten with colons at build time |
| Server tagging | `final_measure.py` | 451 `[JP]`, 588 `[Global]`, 156 `[Both]`, 171 `[JP-Only]` markers |
| Table integrity | Column-count scan over every table | 29 tables, 0 column mismatches, 0 missing separators, 0 truncated rows; one nine-cell-against-eight-column defect was found in the merge step and fixed before this scan |
| Date arithmetic | Window comparison for every active and upcoming item | 0 items whose end precedes its start; 0 "active" items already closed; 0 "upcoming" items already open |
| Future-dated roster rows | Date filter over the export | 0 cards dated after 2026-09-27 on either server |

### 8.6 Deviations from the commissioned method, stated plainly

1. **`browse` and `scrape` were not loaded as written.** Those gstack skills drive a local daemon. Live reading went through a playwright MCP browser and direct HTTP instead, which is the same capability by another route. Consequence worth naming: no per-source robots or rate-limit note was generated, because nothing was crawled; each source was fetched singly or in tens.
2. **No `CONSTRAINTS.md` was written by this task, and none existed when the bar was set.** The
   constraint skill's default artifact is that file; this brief authorized exactly one output
   document, so the measurable bar went into the Phase 0 Scope Contract instead. `CONSTRAINTS.md` did
   appear during this session, at 01:02 on 2026-09-27, written by another process working on the app
   while this document was being compiled. It was not available to the Phase 0 gate, and its C-1 to
   C-8 dimensions are code gates that this document cannot fail: it ships no PHP, no migration and no
   UI. C-4 does reach it, and 8.8 covers what a `make lore` run will see.
3. **Three sources named in the brief do not exist as given.** `gamewith.jp/umamusume` and `umamusume.kamigame.jp` are wrong paths, `umamusume.wiki` does not resolve, `umamusume-db.com` has no DNS answer, and Prydwen and Cygames' corporate news both return 403 to this client. The registry section records each with the check that produced the verdict.
4. **Official sites are JS-rendered SPAs.** A plain HTTP fetch of `umamusume.jp/news/` or `umamusume.com/news/` returns an empty list, and the same is true of GameTora's HTML pages. Any "no data found" conclusion reached without executing JavaScript on these hosts is an artifact of the tool, and several citations here were rescued by re-reading through a browser.
5. **`source.md` in this repo is not a source registry.** It is a Laravel project-bootstrap prompt. Phase 1 here was built from live probing.
6. **Two researchers had to be re-dispatched.** The agent assigned Sections 1.2 and 1.6 reported intent and wrote nothing at all. Its scope was split in two and re-run. The standing lesson, applied to every later handoff: an agent's summary is not a deliverable, and each reported file was verified with `wc -l` before being trusted.
7. **A rebuild regression, caught by the review.** Rerunning the merge after a fix regenerated the body from the drafts and silently reverted a source-label normalization that had been applied to the merged file. The relabel is now part of the patch pass rather than a manual post-edit.
8. **Concurrent repo edits.** `AGENTS.md` and `PRD.md` were modified by another process during this session, expanding the consolidation from three legacy apps to four and adding a role. Neither was touched here. `PRD.md` §6 lists "no event/banner calendar" and "no support-card database" as Phase 1 non-goals for the app; Sections 1.4, 3 and 4 of this document are reference research upstream of that scope and should not be read as a Phase 1 build spec.
9. **`stat_bonus` is published without a meaning.** The roster row labels it "meaning unconfirmed" and points at 1.3.5, which records that no source states which system grants it. An earlier build called it a "card growth bonus", which 1.3.5 refuses to assert.
10. **Scratch artifacts.** The generator, data export, drafts and review inputs live in a working directory beside this file and are not part of the deliverable. Nothing in this document cites them: every citation resolves to a public URL.

### 8.7 Blockers

No hard blockers. Two soft ones, with what was done:

```
BLOCKER: umamusume-db.com does not resolve and prydwen.gg/umamusume returns 403 to this client.
Impact: the brief's Databases category reduces to GameTora alone for Sections 3 and 5.
Mitigation: Section 3 gained a whole-roster set diff against Kamigame (2.5) and a cell-level diff
against game8.co and Kamigame (2.4); Section 5 cross-checked every active item against an official
page plus one wiki, and the three items that had rested on one domain were lifted to two or three.
```

```
BLOCKER: neither server publishes gacha pull rates on a web page; both route to an in-app tab.
Impact: Section 5.4 rates cannot reach S-tier sourcing at any amount of effort.
Mitigation: rates computed from the export's weights, shown summing to 100.00% on every banner
sampled, corroborated by two tier-A guides, labeled tier B at the point of use. The Global guide's
contradictory 79 against 97 percent for the common tier is logged rather than resolved in silence.
```

### 8.8 What a lore-gate run will report on this file

`CONSTRAINTS.md` C-4 greps tracked text for the banned patterns, and this document will produce hits
of three kinds, none of them a violation:

| Kind | Where | Why it is not a violation |
|---|---|---|
| Naming the ban in order to forbid it | the lore policy section, and 5.5 above | Same purpose as the `docs/PRE-MORTEM.md` lines already exempted under C-4 as elimination evidence |
| Banned substrings inside ordinary words and real names | "desired" in skill-planning prose, the Umamusume named `Red Desire` and its URL slug, "damaged", the support-card epithet "La dama perfetta" | A substring is not a vocabulary choice, and the names are the data |
| Four em dashes inside verbatim skill names | three roster table rows | Quoted product strings. Rewriting them to satisfy a style rule would falsify the reference |

The document is currently untracked, so `make lore` will not see it until it is committed. When it
is, the C-4 adjudication belongs to the Lore Guardian, and the three rows above are the reasoning
already on file.

### 8.9 What a reader should do with this document

Trust Sections 1 and 3 for months. Re-verify Sections 4 and 5 against the official feeds named in each "How to re-check" line before acting on a date, because those windows were live at the anchor instant and the next `[JP]` banner swap is due 2026-09-30 12:00 JST. Treat every `❌ UNVERIFIED` as an open question with a paper trail, not as a blank to fill in elsewhere.
