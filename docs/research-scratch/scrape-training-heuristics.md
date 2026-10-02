# Umamusume: Pretty Derby [JP] Training Mechanics: Raw Extraction

Scope: mechanical rules extracted from JP tier-A strategy wikis (GameWith, Game8 JP, Kamigame) using a live
rendering browser session (Playwright MCP). All values are transcribed exactly as printed on the page.

Method note, because it affected reliability: a concurrent agent in this workspace was driving the same single
browser tab, and it re-targeted the tab between my `browser_navigate` and `browser_evaluate` calls for a large share
of attempts. Plain navigate-then-read worked only intermittently. What worked reliably was issuing
`browser_navigate` and `browser_evaluate` in the same turn, with the evaluate polling `location.hostname` until my own
navigation landed, then reading the target article through the live page's own same-origin `fetch` (credentials
omitted) and rendering the parsed document into the live DOM so `innerText`/table layout resolved normally. Hidden
scenario-tab panels on GameWith 257432 were read directly out of the DOM rather than by clicking each tab, which is
how the per-scenario tables in section 4 were recovered. No page in this ledger was skipped or filled from memory.

Server tagging: every rule below is `[JP]`. Global confirmation was not sought for any item, so nothing here
is tagged `[Global]` or `[Both]`. Treat the whole file as JP-wiki-sourced.

Reading convention used throughout: `JP string` = verbatim text as printed; `EN` = my mechanical translation.

---

## 1. Failure mechanics

### 1.1 Can training fail at all, and per-discipline

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| 賢さ以外のトレーニングを行うと体力を消費する | Only non-Wit training spends energy | GameWith 257432 | [JP] |
| 賢さのトレーニングには体力消費がない上、わずかに体力を回復できる。他のトレーニングと比較すると失敗率の上がり方が緩い | Wit training spends no energy and refunds a little; its failure-rate ramp is gentler than the other four | GameWith 257432 | [JP] |
| 体力が低いとトレーニング失敗率が高くなる | Lower energy raises failure probability | GameWith 257432 / Game8 372572 / Kamigame 164094542528651333 | [JP] |
| 賢さ失敗では発生しない / 賢さで失敗時はステータスが上がらず体力だけ回復する | Wit training CAN fail, but on failure no failure event fires: no stat gain, and energy still recovers | GameWith 257432 | [JP] |
| 友情トレーニングでも失敗はする | Rainbow/friendship training can also fail | Kamigame 149493615532457367 | [JP] |
| トレーニングに失敗すると、能力は上昇せず、体力低下・やる気低下・ケガ（能力低下）のいずれかのペナルティが発生します | On failure: no stat gain, plus exactly one of energy loss, motivation loss, injury (stat loss) | Game8 372572 | [JP] |
| 失敗率が高いほど失敗した時のペナルティも大きくなってしまう | Penalty magnitude scales with the failure rate itself | Game8 372572 | [JP] |

### 1.2 Failure-rate formula (published as a calculation, not a lookup table)

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| 失敗率×失敗率ダウン+コンディション補正=失敗率 | Failure rate = (base failure rate x failure-rate-down multiplier) + condition correction | GameWith 274990 | [JP] |
| 失敗率ダウンは、トレーニング失敗率に対して乗算と思われる。また、練習上手◯などコンディション補正による効果は最終的に加算されるため、練習ベタや小さなほころびの効果を0にすることはできない | Failure-rate-down is multiplicative; condition corrections are added last, so bad-condition penalties cannot be zeroed out | GameWith 274990 | [JP] |
| 体力が少ない状態でトレーニングを行うことで、トレーニングに失敗する場合がある | Low energy is the trigger condition | GameWith 257432 | [JP] |
| 失敗のデメリットが大きい | (qualitative) failure carries heavy downside | GameWith 257432 | [JP] |

Note on the multiplicative wording: GameWith prints `失敗率×失敗率ダウン+コンディション補正=失敗率`. Read literally the
factor is `1 - ダウン率`, since the same page describes 失敗率ダウン as reducing the rate. The page does not disambiguate.

### 1.3 Published probability table by energy level

❌ UNVERIFIED: No energy-vs-failure-rate probability table is printed on any page rendered here. Searched/checked:
GameWith 257432 (トレーニングの効果と失敗イベント), GameWith 274990 (サポート効果と計算式), GameWith 293379
(失敗率ダウン持ちサポートカード一覧), GameWith 257614 (育成の攻略とコツ), Game8 372572 (トレーニング効果とおすすめ),
Game8 454202 (友情トレーニング), Kamigame 164094542528651333 (体力の使い道と回復方法), Kamigame 146276970408242410
(トレーニングの効果と優先度). All of them state the relationship only qualitatively.

The nearest thing to a numeric threshold published by these wikis is an operator heuristic, not a game table:

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| 練習の成功率は体力50以上とそれ以下で大きく変わってくる。…体力はなるべく50以上をキープしよう | Practical cutoff used by the editors: success rate differs markedly at 50 energy vs below 50 | GameWith 257614 | [JP] (editorial heuristic, not a printed game table) |
| 体力5割未満か強い練習が無い時はお休み | Rest when energy is below 50% | Kamigame 114672877806026759 | [JP] (editorial heuristic) |
| 体力が半分以下になった場合、お休みを選ぶのがおすすめ | Same, below half | Kamigame 147307607101602682 | [JP] (editorial heuristic) |

Energy is printed as a 0..100 scale on GameWith (recovery amounts 30/50/70 and costs -19..-28 per training are all
on that scale) and the wikis consistently treat 100 as the cap. See section 4.4 for the printed per-training energy costs.

### 1.4 The three failure outcomes and their numeric ranges

GameWith 257432 prints the failure outcome set as two named event variants, each with two choices. `直前のトレーニング
に応じたステータス` = the stat matching the training just attempted.

Event `お大事に！` (Take care! / the "get well" failure event):

| JP string as printed | EN mechanical translation | Tag |
|---|---|---|
| 上選択肢: やる気ダウン / 直前のトレーニングに応じたステータス-5 / ランダムで『練習下手』になる | Top choice: motivation down 1 tier; the training's primary stat -5; random chance to gain 練習下手 (Practice Clumsy) | [JP] |
| 下選択肢: やる気ダウン / 直前のトレーニングに応じたステータス-10 / ランダムで『練習下手』になる / 『練習上手◯』になる | Bottom choice: motivation down 1 tier; primary stat -10; random chance of 練習下手, or gain 練習上手◯ (Practice Skilled ◯) | [JP] |

Event `無茶は厳禁！` (Don't overdo it! / the "no reckless training" failure event):

| JP string as printed | EN mechanical translation | Tag |
|---|---|---|
| 上選択肢: 体力+10 / やる気ダウン / 直前のトレーニングに応じたステータス-10 / 5種ステータスからランダムに2種を-10 / ランダムで『練習ベタ』になる | Top choice: energy +10; motivation down 1 tier; primary stat -10; two of the five stats chosen at random -10 each; random chance to gain 練習ベタ (Practice Untalented) | [JP] |
| 下選択肢: やる気ダウン / 直前のトレーニングに応じたステータス-10 / 5種ステータスからランダムに2種を-10 / 『練習ベタ』になる / 体力+10 / 『練習上手◯』になる | Bottom choice: motivation down 1 tier; primary stat -10; two random stats -10 each; gain 練習ベタ, or energy +10 and 練習上手◯ | [JP] |

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| トレーニング失敗時は2種類の失敗イベントが発生する。ウマ娘ごとに選択肢の文言が違うが内容は同じ | Exactly 2 failure-event variants exist; wording differs per character but the numbers do not | GameWith 257432 | [JP] |
| ケガ（能力低下） | Injury is expressed as a stat drop, not a separate counter | Game8 372572 | [JP] |

Numeric range summary across both event variants: primary stat `-5` to `-10`; secondary random stat loss `0` or
`-10 x 2`; energy `0` or `+10`; motivation `-1 tier`.

### 1.5 What reduces the failure chance

Support effect `失敗率ダウン` (failure-rate-down), printed per support card and per limit-break level by GameWith 293379.
Header note on that page: `固有ボーナスとの合計値を記載` = values shown already include the card's own unique bonus.

| JP card name (EN) | Lv30 無凸 | Lv35 1凸 | Lv40 2凸 | Lv45 3凸 | Lv50 4凸 | Tag |
|---|---|---|---|---|---|---|
| マチカネフクキタル (Machikane Tannhauser) | 10% | 10% | 10% | 10% | 10% | [JP] |
| 駿川たづな (Tazuna Hayakawa) | 30% | 32% | 35% | 37% | 40% | [JP] |
| 樫本理子 (Riko Kashimoto) | 25% | 26% | 28% | 30% | 30% | [JP] |
| ライトハロー (Light Hello) | 20% | 22% | 25% | 27% | 30% | [JP] |
| 佐岳メイ (Mei Satake) | 15% | 16% | 18% | 20% | 20% | [JP] |
| 都留岐涼花 (Ryoka Tsurugi) | 20% | 22% | 25% | 27% | 30% | [JP] |
| 秋川理事長 (Chairman Akikawa) | 15% | 16% | 18% | 20% | 20% | [JP] |
| タッカーブライン (Tucker Brine) | 15% | 16% | 18% | 20% | 20% | [JP] |
| 保科健子 (Kenko Hoshina) | 10% | 11% | 13% | 15% | 15% | [JP] |
| カジノドライヴ (Casino Drive) | 10% | 11% | 13% | 15% | 15% | [JP] |
| 駿川たづな (Tazuna Hayakawa, 2nd entry) | 10% | 11% | 13% | 15% | 15% | [JP] |
| 桐生院葵 (Aoi Kirisouin) | 30% | 31% | 32% | 33% | 35% | [JP] |
| 駿川たづな (Tazuna Hayakawa, 3rd entry) | 15% | 16% | 17% | 18% | 20% | [JP] |
| 桐生院葵 (Aoi Kirisouin, 2nd entry) | 15% | 16% | 17% | 18% | 20% | [JP] |
| 樫本理子 (Riko Kashimoto, 2nd entry) | 10% | 11% | 13% | 15% | 15% | [JP] |
| ライトハロー (Light Hello, 2nd entry) | 15% | 16% | 17% | 18% | 20% | [JP] |
| 佐岳メイ (Mei Satake, 2nd entry) | 15% | 16% | 17% | 18% | 20% | [JP] |
| 都留岐涼花 (Ryoka Tsurugi, 2nd entry) | 15% | 16% | 17% | 18% | 20% | [JP] |
| 秋川理事長 (Chairman Akikawa, 2nd entry) | 5% | 6% | 8% | 10% | 10% | [JP] |
| タッカーブライン (Tucker Brine, 2nd entry) | 5% | 6% | 8% | 10% | 10% | [JP] |
| 保科健子 (Kenko Hoshina, 2nd entry) | 5% | 6% | 8% | 10% | 10% | [JP] |
| カジノドライヴ (Casino Drive, 2nd entry) | 5% | 6% | 8% | 10% | 10% | [JP] |

(The page does not print the rarity/type next to each row in the plain-text rendering; duplicate names are separate
cards and are kept as separate rows rather than merged.)

Condition-based failure-rate modifiers (Game8 374294, `トレーニング失敗率に影響`):

| JP string as printed | EN mechanical translation | Tag |
|---|---|---|
| 練習上手◯: トレーニング失敗率が−2%される | Practice Skilled ◯: failure rate -2 percentage points | [JP] |
| 練習ベタ: トレーニング失敗率が+2%される | Practice Untalented: failure rate +2 percentage points | [JP] |
| 練習上手◎: トレーニング失敗率が−4%される (ナリタタイシン固有コンディション) | Practice Skilled ◯◯: failure rate -4 points, Narita Taishin exclusive condition | [JP] |
| 小さなほころび: トレーニング失敗率が+5%される (スーパークリーク固有) | Small Fray: failure rate +5 points, Super Creek exclusive condition | [JP] |
| 大輪の輝き: トレーニング失敗率が−5%される (スーパークリーク固有) | Grand Bloom: failure rate -5 points, Super Creek exclusive condition | [JP] |
| 「練習ベタ」と「練習上手◯」は、お互いに上書きしあう | Practice Untalented and Practice Skilled ◯ overwrite each other | [JP] |
| 「練習ベタ」に関しては体力に関係なく失敗率が発生する | Practice Untalented raises the failure rate independently of energy | [JP] |
| 小さなほころび: クラシック2月後半に育成イベントで取得 / クラシック10月後半の育成イベント以外では解消できない | Small Fray is granted in Classic-class late February and only clears at the Classic late-October event | [JP] |
| 大輪の輝き: クラシック10月後半に育成イベントで取得 / 獲得と同時に「小さなほころび」が治る | Grand Bloom is granted Classic late October and clears Small Fray at the same time | [JP] |
| 練習上手◎: シニア3月前半に「練習ベタ」を取得していなければ取得 | Practice Skilled ◯◯ is granted Senior-class early March only if you do not hold Practice Untalented | [JP] |

Other failure-reducing systems printed:

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| 夏合宿は試食会の失敗率軽減100％を活かして練習を4連続で踏む | The new scenario's 試食会 (tasting session) grants 100% failure-rate reduction during summer camp | GameWith scenario article 257614 / Kamigame scenario page | [JP] |

❌ UNVERIFIED: skills that reduce the training failure rate. The task named `スタートコスパ◎` and `排気量アップ`-style
training skills. I read GameWith 257432, 274990, 293379, 257614, Game8 372572/374294, and Kamigame training pages. None
of them lists any race/skill that changes the training failure rate. Every failure-rate lever printed on these pages is
either a support effect (`失敗率ダウン`), a condition (練習上手◯/◎, 練習ベタ, 小さなほころび, 大輪の輝き), or a scenario
gimmick. Do not model race skills as failure-rate modifiers.

### 1.6 Items that affect failure / energy (searched)

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| やる気UPスイーツ: ウマ娘のやる気を絶好調にする | Motivation Up Sweets: sets motivation straight to Peak Condition | GameWith 258369 (アイテム一覧) | [JP] |
| 目覚まし時計: 育成の目標レースでコンテニューができる | Alarm Clock: continue after a target race | GameWith 258369 | [JP] |
| にんじんBBQセット: サポート全員の絆ゲージ+5【使用タイミング】育成序盤に使用 | Carrot BBQ Set: all support bond +5, use early (Climax-scenario item) | Kamigame 149493615532457367 | [JP] |
| リセットホイッスル: 練習メンバーを再配置【使用タイミング】合宿時に使用 | Reset Whistle: re-seats training participants, use during camp | Kamigame 149493615532457367 | [JP] |
| メガホン / アンクル: 練習効率を高める【使用タイミング】合宿時に使用 / 複数人の友情トレーニング発生時に使用 | Megaphone / Ankle: raise training efficiency, use during camp and when multiple rainbow sessions fire | Kamigame 149493615532457367 | [JP] |

❌ UNVERIFIED: `アイシング` (icing). I searched GameWith's アイテム一覧 (258369, updated 2026-09-16), the rest/health
articles, and GameWith/Game8/Kamigame link indexes for `ケア` and `アイシング`. No page rendered here prints an アイシング
command or item, so there is no printed recovery value for it. Do not add it.

---

## 2. Rest and outing

### 2.1 `お休み` (Rest), energy restored

GameWith 257617 (`お休みの確率と効果｜発生イベント`, 最終更新 2021年7月5日10:15), n = 3833 trials:

| JP string as printed | 確率 (probability) | 回数 (count) | EN mechanical translation | Tag |
|---|---|---|---|---|
| 70回復 | 25.4% | 975/3833 | Rest outcome: energy +70 | [JP] |
| 50回復 | 58.1% | 2226/3833 | Rest outcome: energy +50 | [JP] |
| 30回復 | 12.8% | 492/3833 | Rest outcome: energy +30 | [JP] |
| 30回復+夜ふかし | 3.7% | 140/3833 | Rest outcome: energy +30 and gain 夜ふかし気味 (Late-Night Feeling) | [JP] |

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| お休みを選択すると1ターン消費し、ウマ娘の体力を50回復する | Rest costs 1 turn and restores 50 energy (the modal case) | GameWith 257617 | [JP] |
| 大成功した場合は70回復できるが、失敗した場合はデメリットもある | Big-success roll gives 70; the failure roll carries a downside | GameWith 257617 | [JP] |
| 確率的には50>70>30>夜ふかしとなっており | Outcome frequency order: 50 > 70 > 30 > late-night | GameWith 257617 | [JP] |
| お休みの失敗イベントは回復量が30に減少し、やる気ダウンや「夜ふかし気味」になる | Rest failure event: recovery drops to 30, motivation down, and 夜ふかし気味 may be gained | GameWith 257617 | [JP] |
| お休みでは体力+30のパターンで稀に『夜更かし気味』になってしまいます | The +30 rest outcome is where the rare late-night condition comes from | GameWith 257614 | [JP] |
| 夏合宿中の休憩はお出かけとセットになっており、体力が40回復するだけでなくやる気アップの効果もある | During summer camp the break is bundled with an outing: energy +40 AND motivation up | GameWith 257617 | [JP] |
| 合宿期間は、お出かけのコマンドが選択できなくなる。その分、お休みのコマンドで回復+やる気アップができる | During camp the Outing command is unavailable, so Rest supplies recovery plus motivation | GameWith 257538 | [JP] |

Kamigame 147307607101602682 (`お休みのおすすめタイミングと寝不足イベントの発生確率`, 最終更新日 2022-12-19 10:22),
n = 100 trials. Same event names, different printed event names and split:

| JP string as printed | 確率（回数） | EN mechanical translation | Tag |
|---|---|---|---|
| 休息はバッチリ！ 体力+70 | 12％（12/100回） | Event "Rest is perfect!", energy +70 | [JP] |
| リフレッシュ完了 体力+50 | 66％（66/100回） | Event "Refresh complete", energy +50 | [JP] |
| 寝不足で…… 体力+30 確率で「夜ふかし気味」獲得 | 22％（22/100回） | Event "Under-slept...", energy +30, chance of late-night | [JP] |
| 寝不足で…… 体力+30「夜ふかし気味」なし | 17％（17/100回） | Under-slept branch with no condition | [JP] |
| 寝不足で…… 体力+30「夜ふかし気味」獲得 | 5％（5/100回） | Under-slept branch that grants the condition | [JP] |

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| 寝不足イベントが発生する確率は約22％ | The under-slept event fires ~22% of rests | Kamigame 147307607101602682 | [JP] |
| お休みで夜ふかし気味を獲得する確率は約5％ | The late-night condition itself lands ~5% of rests | Kamigame 147307607101602682 | [JP] |
| 1ターンで最大70回復可能 | Rest caps at +70 in one turn | Kamigame 147307607101602682 | [JP] |
| 寝不足イベント発生時は、体力は30しか回復しない | With the under-slept event only 30 is restored | Kamigame 147307607101602682 | [JP] |

Kamigame 164094542528651333 (`体力の使い道と回復方法`, 最終更新日 2021-10-15 17:35):

| JP string as printed | EN mechanical translation | Tag |
|---|---|---|
| お休みの回復量は+30、+50、+70のいずれかである。また、確率で寝不足になる可能性がある | Rest returns one of +30 / +50 / +70, with a chance of under-slept | [JP] |

### 2.2 `夜ふかし気味` (Late-Night Feeling) and its cure

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| 夜ふかし気味: ランダムで体力−10、やる気−1 | While held: random energy -10 and motivation -1 | Game8 374294 | [JP] |
| 夜ふかし気味で……: 選択肢なし・体力-10・稀にやる気-1 | Its recurring event, no choice: energy -10, rarely motivation -1 | Game8 417620 | [JP] |
| イベントは体力減少のみの場合と体力に加えやる気も下がる2パターンあります | Two patterns: energy-only drop, and energy plus motivation drop | Game8 417620 | [JP] |
| 寝不足で…… 【パターン1】・体力+30【パターン2】・体力+30・やる気−1・「夜ふかし気味」を取得 | Rest under-slept event has 2 printed patterns | Game8 417620 | [JP] |
| 保健室は体力20回復に加えてバッドコンディションを治す効果がある。ただし成功率があり、失敗することも | Nurse's office: energy +20 plus an attempt to clear one bad condition; the attempt can fail | GameWith 286339 | [JP] |
| 練習ベタ(計119回): 成功84%(100回) 失敗16%(19回) / 夜ふかし(計111回): 成功84.68%(94回) 失敗15.32%(17回) | Observed cure rate 84% (100/119) and 84.68% (94/111) | GameWith 286339 | [JP] |
| バッドコンディションは85%で治り、15%で失敗すると推測が立てられる | Editors' inferred rule: 85% cure, 15% fail | GameWith 286339 | [JP] (an estimate from their own sampling, stated as 推測) |
| 練習ベタ(計100回): 1回 86%(86回) / 2回 10%(10回) / 3回 3%(3回) / 4回 1%(1回) | Tries-to-cure distribution for Practice Untalented | GameWith 286339 | [JP] |
| 夜ふかし(計94回): 1回 87.23%(82回) / 2回 8.51%(8回) / 3回 3.19%(3回) / 4回 1.06%(1回) | Tries-to-cure distribution for late-night | GameWith 286339 | [JP] |
| 確率はバッドコンディション共通である可能性が高い | The cure probability is likely shared across all bad conditions | GameWith 286339 | [JP] |
| 複数のバッドコンディションがある場合にも1つしか治せず効率が悪いため | The office clears only one condition per visit | Kamigame 164086749461487685 | [JP] |
| 夏合宿中は保健室が使えない / クラシック・シニア級における7〜8月は夏合宿があり、保健室が使えません | The nurse's office is unavailable during summer camp (July-early September, Classic and Senior years) | Game8 417620 | [JP] |
| 「駿川たづな」の2・4段階目のお出かけイベントには一部の悪いコンディションを確定で治す効果 | Tazuna Hayakawa outing stages 2 and 4 cure certain bad conditions with certainty | Game8 417620 / GameWith 286339 | [JP] |
| 夏合宿中にお休みをすると、バッドコンディションを全て解消できる | Resting during summer camp clears every bad condition | Kamigame 164086749461487685 | [JP] |
| 神社でお祈り！イベントが発生し、バッドコンディションが治る。ただし治らない場合もあるので確実性はない | The shrine sub-roll at an outing can cure a bad condition, non-deterministically | GameWith 263413 | [JP] |

### 2.3 `お出かけ` (Outing): mood deltas and energy

GameWith 263413 (`お出かけの効果･確率とおすすめタイミング`, 最終更新 2023年3月6日14:21):

| JP string as printed | EN mechanical translation | Tag |
|---|---|---|
| お出かけを選択すると1段階以上のやる気アップとランダムで体力回復が発生。効果は選べないものの、確実にやる気が1段階は上がる | Outing: motivation up by at least 1 tier, guaranteed; energy recovery is random | [JP] |
| カラオケはやる気が2段階上昇するお出かけイベント | Karaoke raises motivation 2 tiers | [JP] |
| お散歩では体力+10とやる気が1段階上昇する | Walk: energy +10, motivation +1 tier | [JP] |
| 大吉: 体力+30、やる気アップ | Shrine, Great Blessing: energy +30, motivation up | [JP] |
| 中吉: 体力+20、やる気アップ | Shrine, Middle Blessing: energy +20, motivation up | [JP] |
| 小吉: 体力+10、やる気アップ | Shrine, Small Blessing: energy +10, motivation up | [JP] |
| クレーンゲームはクラシック級以降でおでかけした際に、1度だけランダムで発生する | Crane game: one random occurrence from Classic class onward, grants skill hints | [JP] |

GameWith 263413 outing-type frequency table (n = 119):

| JP string as printed | 回数 | 確率 | EN mechanical translation | Tag |
|---|---|---|---|---|
| カラオケ | 43/119 | 36.13% | Karaoke | [JP] |
| お散歩 | 35/119 | 29.41% | Walk | [JP] |
| 神社合計 | 41/119 | 34.45% | Shrine, all fortunes | [JP] |
| 神社-小吉 | 23/119 | 19.33% | Shrine, Small Blessing | [JP] |
| 神社-中吉 | 14/119 | 11.76% | Shrine, Middle Blessing | [JP] |
| 神社-大吉 | 4/119 | 3.36% | Shrine, Great Blessing | [JP] |

Kamigame 147307607101602682 outing-vs-rest summary:

| JP string as printed | EN mechanical translation | Tag |
|---|---|---|
| お出かけの特徴:・やる気が1〜2段階UPする・体力が0〜30回復する・確率でバッドコンディションを解消できる・友人キャラとのお出かけでは体力を大きく回復できる | Outing: motivation +1 to +2 tiers, energy +0 to +30, probabilistic condition cure; a Friend-type outing recovers far more | [JP] |
| お休みの特徴:・体力が30〜70回復する・稀にバッドコンディション「夜ふかし気味」を獲得する | Rest: energy +30 to +70, rarely the late-night condition | [JP] |

Kamigame 146407792159257608 (`お出かけ発生条件と連続イベント一覧`, 最終更新日 2024-04-14 13:50): the Friend-card outing
chains, printed per stage. These are the exact per-turn deltas the guide states.

Light Hello (`ライトハロー`) chain:

| Stage | JP string as printed | EN mechanical translation | Tag |
|---|---|---|---|
| 1回目 | 体力の最大値+4・体力+27・やる気+1・ライトハローの絆ゲージ+5 | Max energy +4; energy +27; motivation +1; bond with Light Hello +5 | [JP] |
| 2回目 | 体力+27・やる気+1・根性+11・ライトハローの絆ゲージ+5 | Energy +27; motivation +1; Guts +11; bond +5 | [JP] |
| 3回目 選択肢【恥ずかしくないですよ】 | 体力+70・やる気+1・ライトハローの絆ゲージ+5 | Energy +70; motivation +1; bond +5 | [JP] |
| 3回目 選択肢【わかりました！】 | やる気+1・スピード+16・根性+16・ライトハローの絆ゲージ+5 | Motivation +1; Speed +16; Guts +16; bond +5 | [JP] |
| 4回目 | 体力+30・やる気+1・根性+11・ライトハローの絆ゲージ+5 | Energy +30; motivation +1; Guts +11; bond +5 | [JP] |
| 5回目【大成功】 | やる気+1・体力+30・スピード+10・根性+10・絆ゲージ+5・レアスキル「お先に失礼っ！」のヒントLv+3 | Motivation +1; energy +30; Speed +10; Guts +10; bond +5; rare-skill hint level +3 | [JP] |
| 5回目【成功】 | やる気+1・体力+20・スピード+5・根性+5・絆ゲージ+5・レアスキル「お先に失礼っ！」のヒントLv+1 | Motivation +1; energy +20; Speed +5; Guts +5; bond +5; rare-skill hint level +1 | [JP] |

Throne-assembled (`玉座に集いし者たち`) chain, per character variant:

| JP string as printed | EN mechanical translation | Tag |
|---|---|---|
| シンボリルドルフ: 体力+12・賢さ+34・「闘争心」のヒントLv+1・絆ゲージ+5 | Energy +12; Wit +34; hint level +1; bond +5 | [JP] |
| トウカイテイオー: 体力+12・やる気+1・スピード+23・「ポジションセンス」のヒントLv+1・絆ゲージ+5 | Energy +12; motivation +1; Speed +23; hint level +1; bond +5 | [JP] |
| ツルマルツヨシ: 体力+36・スキルPt+17・「フルスロットル」のヒントLv+1・絆ゲージ+5 | Energy +36; skill points +17; hint level +1; bond +5 | [JP] |

Team Sirius (`チームシリウス`) chain, printed variants:

| JP string as printed | EN mechanical translation | Tag |
|---|---|---|
| マックイーン: 体力の最大値+4・スタミナ+12・根性+12・賢さ+12・「あやしげな作戦」のヒントLv+2・絆ゲージ+5 | Max energy +4; Stamina +12; Guts +12; Wit +12; hint level +2; bond +5 | [JP] |
| ライスシャワー: 体力+32・スキルPt+18・「徹底マーク◯」のヒントLv+2・絆ゲージ+5 | Energy +32; skill points +18; hint level +2; bond +5 | [JP] |

Friend-card outing unlock, printed condition:

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| 絆ゲージを3まで上げると、お出かけイベントが発生する | The Friend outing chain unlocks at bond gauge 3 bars | Kamigame 164094542528651333 | [JP] |
| 友人/グループタイプの絆ゲージが3本目に到達して緑色になっているとお出かけ開始イベントが発生しやすい | The start event is likelier once the bar reaches the 3rd (green) segment | GameWith 257614 | [JP] |
| 友人/グループタイプのサポカは最低でも1度は一緒にトレーニングをしていないとお出かけ開始イベントが発生しない | The Friend/Group card must join at least one training before the outing chain can start | GameWith 257614 | [JP] |

### 2.4 `疲労` and `ケガ` as separate systems

❌ UNVERIFIED: no separate `疲労` (fatigue) counter and no separate `ケガ` (injury) counter with numeric ranges is
printed on any page rendered here. GameWith and Kamigame model energy as the single `体力` gauge; Game8 372572 collapses
injury into the failure outcome as `ケガ（能力低下）`, i.e. an stat drop rather than a timed injury state. If the app needs
a fatigue or injury column, it is not sourced from these three wikis.

---

## 3. Motivation / mood (`やる気`), the five tiers

Tier names, JP as printed on all three sites, in order: `絶好調` / `好調` / `普通` / `不調` / `絶不調`.
EN: Peak Condition / Good Condition / Normal / Poor Condition / Worst Condition.
(Note GameWith and Kamigame print `普通`; Game8 372572 prints `普通` in the tier table and `やる気が「普通」状態` in prose.)

Two mutually inconsistent sets of multipliers are printed across these wikis. Both are recorded.

Set A, GameWith 257538 (`やる気の影響と上げ方`, 最終更新 2021年9月22日17:37):

| JP string as printed | EN mechanical translation | Tag |
|---|---|---|
| 絶好調: トレーニングの効果が20%上昇 / レース中、基礎能力が4%上昇 | Peak: training effect x1.20; in-race base stats +4% | [JP] |
| 好調: トレーニングの効果が10%上昇 / レース中、基礎能力が2%上昇 | Good: training effect x1.10; in-race base stats +2% | [JP] |
| 普通: 補正なし | Normal: no correction | [JP] |
| 不調: トレーニングの効果が10%減少 / レース中、基礎能力が2%減少 | Poor: training effect -10%; in-race base stats -2% | [JP] |
| 絶不調: トレーニングの効果が20%減少 / レース中、基礎能力が4%減少 | Worst: training effect -20%; in-race base stats -4% | [JP] |

Set B, Game8 372572 and Kamigame 146417173592559219 + 146276970408242410 (identical text on both pages):

| JP string as printed | EN mechanical translation | Tag |
|---|---|---|
| 【絶好調】トレーニングの効果が20％上昇する / レース中、基礎能力が10％上昇する | Peak: training effect +20%; in-race base stats +10% | [JP] |
| 【好調】トレーニングの効果が10％上昇する / レース中、基礎能力が5％上昇する | Good: training effect +10%; in-race base stats +5% | [JP] |
| 【普通】トレーニングの効果に増減無し / レース中、基礎能力の増減無し | Normal: no change on either | [JP] |
| 【不調】トレーニングの効果が10％減少する / レース中、基礎能力が2％減少する | Poor: training effect -10%; in-race base stats -2% | [JP] |
| 【絶不調】トレーニングの効果が20％減少する / レース中、基礎能力が5％減少する | Worst: training effect -20%; in-race base stats -5% | [JP] |

The training-effect column agrees across all three sites (20/10/0/-10/-20). Only the in-race base-stat column
disagrees; see `## Disagreements`.

GameWith also prints the mood factor as used in the training formula, on 274990
(`やる気効果アップ`, 最終更新 2022年5月18日12:04). This is the signed delta form, and it is asymmetric:

| JP string as printed | 育成ウマ娘のやる気による変化 | EN mechanical translation | Tag |
|---|---|---|---|
| 絶好調 | 1.2 | Peak factor 1.2 | [JP] |
| 好調 | 1.1 | Good factor 1.1 | [JP] |
| 普通 | 0 | Normal factor 0 | [JP] |
| 不調 | -0.9 | Poor factor -0.9 (i.e. 0.9, a 10% cut) | [JP] |
| 絶不調 | -0.8 | Worst factor -0.8 (i.e. 0.8, a 20% cut) | [JP] |

| JP string as printed | EN mechanical translation | Tag |
|---|---|---|
| やる気効果アップ 計算式: 育成ウマ娘のやる気×(1+やる気効果A+B)=やる気効果量 | Motivation-effect-up multiplies the tier delta: tier factor x (1 + summed support bonus) | [JP] |
| やる気効果が高いほど、絶好調の時の上昇量も上がるが、絶不調の時の減少量も大きくなる。基準値はそれぞれ1と思われる | A higher motivation-effect bonus amplifies both the peak gain and the worst-case loss; baseline is 1 | [JP] |
| (training formula) (基準値+ステアップボーナス)×成長率×(1+調子×やる気効果)×トレーニング効果アップ×友情ボーナス×参加人数補正 | Full stat-gain chain: (base + stat bonus) x growth rate x (1 + mood x motivation effect) x training-effect-up x friendship bonus x participant count | [JP] |
| トレーニングが成功すると、スキルPtが獲得できる | Skill points only land on a successful training | Kamigame 146276970408242410 | [JP] |
| 育成ウマ娘のやる気が低下すると、ステータスだけではなく、獲得スキルPtも減少します | Motivation drops cut skill points too, not just stats | GameWith 274990 | [JP] |

What triggers a tier change:

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| やる気が低いときはお出かけコマンドを選ぶことでやる気を上げられる | The Outing command raises a tier | GameWith 257538 | [JP] |
| 合宿期間は…お休みのコマンドで回復+やる気アップができる | During camp, Rest raises a tier | GameWith 257538 | [JP] |
| ターンが経過したタイミングでシナリオイベントやキャライベントが発生する。イベントでは…やる気に関係するものも存在する | Turn-advancing events can move the tier | GameWith 257538 | [JP] |
| ▲やる気上昇。すでに絶好調の場合は変動なし。 | A motivation-up event at Peak does nothing | GameWith 257538 | [JP] |
| 育成開始直後はやる気が普通状態から始まる | A run starts at Normal | GameWith 263413 | [JP] |
| 確率でカラオケを引ければいきなり絶好調に | Karaoke can jump a fresh run from Normal to Peak | GameWith 263413 | [JP] |
| 駿川たづなを編成している場合は、お出かけ後やる気1段階が上がった状態で次にトレーニングで選択すると絶好調に | With Tazuna in the deck, one outing then one choice lands Peak | GameWith 263413 | [JP] |
| ランダムで体力とやる気が下がる (夜ふかし気味) | Late-Night Feeling pushes motivation down at random | Game8 374294 | [JP] |
| 幸運体質: 育成中の「やる気」ダウンを一度だけ防ぐ | Lucky Constitution blocks one motivation drop | Game8 374294 | [JP] |
| ポジティブ思考: 悪いコンディションを1度だけ防ぐ | Positive Thinking blocks one bad condition | Game8 374294 | [JP] |
| 片頭痛: やる気が上がらなくなる | Migraine forbids motivation increases | Game8 374294 | [JP] |
| 肌あれ: ランダムでやる気-1 | Rough Skin drops motivation by 1 tier at random | Game8 374294 | [JP] |
| やる気UPスイーツ: ウマ娘のやる気を絶好調にする | Motivation Up Sweets forces Peak | GameWith 258369 | [JP] |

---

## 4. The Wit loop (`賢さ`)

### 4.1 Energy behaviour: Wit refunds rather than spends

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| 賢さ以外のトレーニングを行うと体力を消費する | Every non-Wit discipline spends energy | GameWith 257432 | [JP] |
| スピード/スタミナ/パワー/根性の4つのトレーニングは、行うたびに体力を消費する | Speed/Stamina/Power/Guts each cost energy per use | GameWith 257432 | [JP] |
| 賢さのトレーニングには体力消費がない上、わずかに体力を回復できる | Wit costs nothing and adds a little energy | GameWith 257432 | [JP] |
| 賢さ: ・賢さ(大up)・スピード(小up)・スキルpt(大up)・体力回復(微量) | Wit raises Wit (large), Speed (small), skill points (large), and recovers a trace of energy | GameWith 257432 | [JP] |
| 賢さ【上昇】・体力・スピード・賢さ・スキルPt / 【下降】なし | Wit training lists energy as a stat that RISES; no decrease column | Game8 372572 | [JP] |
| 基本は体力を消費して基礎能力やスキルPtが上昇しますが、「賢さ」のトレーニングは体力を消費せず、他のトレーニングよりもスキルPtが多く貰えます | Wit is the only discipline with no energy cost and a higher skill-point payout | Game8 372572 | [JP] |
| 賢さトレーニングをする…僅かだが体力を回復できる | Kamigame states the same | Kamigame 164094542528651333 | [JP] |
| 賢さ練習では体力回復ができるので体力があまり減っていないなら賢さ練習で回復しよう | Editor rule: substitute Wit training for Rest when energy is only mildly low | GameWith 257614 | [JP] |

❌ UNVERIFIED: no dedicated Wit training-efficiency guide exists on the three wikis rendered here. The Kamigame page
linked as 賢さ (page/146301691988282803) is a support-card ranking with card scores only and no training mechanics; I
rendered it and confirmed that. GameWith's Wit mechanics live inside トレーニングの効果と失敗イベント (257432) and
サポート効果と計算式 (274990); Game8's live inside トレーニング効果とおすすめ (372572) and 友情トレーニング (454202).
There is no standalone 賢さ 効率 guide to cite, so nothing in section 4 is drawn from one.

### 4.2 Printed per-session Wit values, per scenario (GameWith 257432, 最終更新 2023年2月25日17:08)

These were hidden behind a scenario tab control (`w-toggle-switch`, tabs `URA / アオハル / クライマックス / グランドライブ /
グランドマスターズ`). The tab is not visible in a plain text scrape; I read every panel out of the DOM directly.
`体力` here is the energy delta, positive = refund.

| Scenario | Lv | スピード (Speed) | 賢さ (Wit) | 体力 (energy) | スキルpt (skill points) | Tag |
|---|---|---|---|---|---|---|
| URA | 1 | +2 | +9 | **+5** | +4 | [JP] |
| URA | 2 | +2 | +10 | **+5** | +4 | [JP] |
| URA | 3 | +2 | +11 | **+5** | +4 | [JP] |
| URA | 4 | +3 | +12 | **+5** | +4 | [JP] |
| URA | 5 | +4 | +13 | **+5** | +4 | [JP] |
| アオハル (Aoharu) | 1 | +2 | +6 | **+5** | +5 | [JP] |
| アオハル | 2 | +2 | +7 | **+5** | +5 | [JP] |
| アオハル | 3 | +2 | +8 | **+5** | +5 | [JP] |
| アオハル | 4 | +3 | +9 | **+5** | +5 | [JP] |
| アオハル | 5 | +4 | +10 | **+5** | +5 | [JP] |
| クライマックス (Climax) | 1 | +2 | +6 | **+5** | +3 | [JP] |
| クライマックス | 2 | +2 | +7 | **+5** | +3 | [JP] |
| クライマックス | 3 | +2 | +8 | **+5** | +3 | [JP] |
| クライマックス | 4 | +3 | +9 | **+5** | +3 | [JP] |
| クライマックス | 5 | +4 | +10 | **+5** | +3 | [JP] |
| グランドライブ (Grand Live) | 1 | +2 | +6 | **+5** | +5 | [JP] |
| グランドライブ | 2 | +2 | +7 | **+5** | +5 | [JP] |
| グランドライブ | 3 | +2 | +8 | **+5** | +5 | [JP] |
| グランドライブ | 4 | +3 | +9 | **+5** | +5 | [JP] |
| グランドライブ | 5 | +4 | +10 | **+5** | +5 | [JP] |
| グランドマスターズ (Grand Masters) | 1 | +2 | +8 | **+5** | +5 | [JP] |
| グランドマスターズ | 2 | +2 | +9 | **+5** | +5 | [JP] |
| グランドマスターズ | 3 | +2 | +10 | **+5** | +5 | [JP] |
| グランドマスターズ | 4 | +3 | +11 | **+5** | +5 | [JP] |
| グランドマスターズ | 5 | +4 | +12 | **+5** | +5 | [JP] |

The energy refund is a flat `+5` at every training level in every scenario printed. It does not scale with level.

### 4.3 Wit skill-point payout versus the other disciplines

Same page, same tab control. Skill-point base per discipline:

| Scenario | スピード | スタミナ | パワー | 根性 | 賢さ | Tag |
|---|---|---|---|---|---|---|
| URA | +2 | +2 | +2 | +2 | +4 | [JP] |
| アオハル | +4 | +4 | +4 | +4 | +5 | [JP] |
| クライマックス | +2 | +2 | +2 | +2 | +3 | [JP] |
| グランドライブ | +4 | +4 | +4 | +4 | +5 | [JP] |
| グランドマスターズ | +5 | +5 | +5 | +5 | +5 | [JP] |

Cross-check printed by GameWith 274990: `スキルPtボーナス…基準値は賢さトレーニングが4、それ以外が2となる` = base skill
points are 4 for Wit and 2 for everything else (the URA figures), additive bonuses on top.

In Grand Masters the payout column collapses to 5 across the board; Wit's advantage there is the energy refund, not
the skill points.

### 4.4 Full printed base values, all disciplines, all scenarios (GameWith 257432)

Energy costs are printed as negative numbers. These are the only per-discipline energy costs any page here prints, and
they are what a failure-rate model would need as the spending half of the loop.

URA:

| Training | Lv | primary | secondary | energy |
|---|---|---|---|---|
| スピード (Speed) | 1 | スピード +10 | パワー +5 | -21 |
| スピード | 2 | +11 | +5 | -22 |
| スピード | 3 | +12 | +5 | -23 |
| スピード | 4 | +13 | +6 | -25 |
| スピード | 5 | +14 | +7 | -27 |
| スタミナ (Stamina) | 1 | スタミナ +9 | 根性 +4 | -19 |
| スタミナ | 2 | +10 | +4 | -20 |
| スタミナ | 3 | +11 | +4 | -21 |
| スタミナ | 4 | +12 | +5 | -23 |
| スタミナ | 5 | +13 | +6 | -25 |
| パワー (Power) | 1 | パワー +8 | スタミナ +5 | -20 |
| パワー | 2 | +9 | +5 | -21 |
| パワー | 3 | +10 | +5 | -22 |
| パワー | 4 | +11 | +6 | -24 |
| パワー | 5 | +12 | +7 | -26 |
| 根性 (Guts) | 1 | 根性 +8 | スピード +4, パワー +4 | -22 |
| 根性 | 2 | +9 | +4, +4 | -23 |
| 根性 | 3 | +10 | +4, +4 | -24 |
| 根性 | 4 | +11 | +5, +4 | -26 |
| 根性 | 5 | +12 | +5, +5 | -28 |
| 賢さ (Wit) | 1..5 | 賢さ +9..+13 | スピード +2..+4 | **+5** |

アオハル (Aoharu):

| Training | Lv 1..5 primary | secondary | energy |
|---|---|---|---|
| スピード | +8, +9, +10, +11, +12 | パワー +4, +4, +4, +5, +6 | -19, -20, -21, -23, -25 |
| スタミナ | +8, +9, +10, +11, +12 | 根性 +6, +6, +6, +7, +8 | -17, -18, -19, -21, -23 |
| パワー | +9, +10, +11, +12, +13 | スタミナ +4, +4, +4, +5, +5 | -18, -19, -20, -22, -24 |
| 根性 | +6, +7, +8, +9, +10 | スピード +3,+3,+3,+4,+4 / パワー +3,+3,+3,+3,+4 | -20, -21, -22, -24, -26 |
| 賢さ | +6, +7, +8, +9, +10 | スピード +2, +2, +2, +3, +4 | **+5** flat |

クライマックス (Climax):

| Training | Lv 1..5 primary | secondary | energy |
|---|---|---|---|
| スピード | +8, +9, +10, +11, +12 | パワー +4, +4, +4, +5, +6 | -19, -20, -21, -23, -25 |
| スタミナ | +7, +8, +9, +10, +11 | 根性 +3, +3, +3, +4, +5 | -17, -18, -19, -21, -23 |
| パワー | +6, +7, +8, +9, +10 | スタミナ +4, +4, +4, +5, +6 | -18, -19, -20, -22, -24 |
| 根性 | +6, +7, +8, +9, +10 | スピード +3,+3,+3,+4,+4 / パワー +3,+3,+3,+3,+4 | -20, -21, -22, -24, -26 |
| 賢さ | +6, +7, +8, +9, +10 | スピード +2, +2, +2, +3, +4 | **+5** flat |

グランドライブ (Grand Live):

| Training | Lv 1..5 primary | secondary | energy |
|---|---|---|---|
| スピード | +8, +9, +10, +11, +12 | パワー +4, +4, +4, +5, +6 | -19, -20, -21, -23, -25 |
| スタミナ | +8, +9, +10, +11, +12 | 根性 +6, +6, +6, +7, +8 | -20, -21, -22, -24, -26 |
| パワー | +9, +10, +11, +12, +13 | スタミナ +4, +4, +4, +5, +6 | -20, -21, -22, -24, -26 |
| 根性 | +7, +8, +9, +10, +11 | スピード +2,+2,+2,+3,+3 / パワー +2,+2,+2,+2,+3 | -20, -21, -22, -24, -26 |
| 賢さ | +6, +7, +8, +9, +10 | スピード +2, +2, +2, +3, +4 | **+5** flat |

グランドマスターズ (Grand Masters):

| Training | Lv 1..5 primary | secondary | energy |
|---|---|---|---|
| スピード | +10, +11, +12, +13, +14 | パワー +3, +3, +3, +4, +5 | -19, -20, -21, -23, -25 |
| スタミナ | +8, +9, +10, +11, +12 | 根性 +6, +6, +6, +7, +8 | -20, -21, -22, -24, -26 |
| パワー | +9, +10, +11, +12, +13 | スタミナ +4, +4, +4, +5, +6 | -20, -21, -22, -24, -26 |
| 根性 | +9, +10, +11, +12, +13 | スピード +2,+2,+2,+3,+3 / パワー +3,+3,+3,+3,+4 | -20, -21, -22, -24, -26 |
| 賢さ | +8, +9, +10, +11, +12 | スピード +2, +2, +2, +3, +4 | **+5** flat |

Scenario-label mapping: the tab control's `input[type=radio]` ids are `tsRadio1-1`..`tsRadio1-5` with labels
`URA / アオハル / クライマックス / グランドライブ / グランドマスターズ`, and I resolved each label to its panel by
matching `data-toggle-name` plus `data-toggle-value` on a second pass. The mapping used above is confirmed by that join,
not assumed from DOM order.

### 4.5 Wit loop supporting rules

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| 同じトレーニングを4回行うと、トレーニングLvが1つ上がる。最大5Lvまで上がり、体力消費が増える代わりに能力の上昇率が上がる | Each discipline levels after 4 uses, cap Lv5; higher level costs more energy and yields more | Kamigame 146276970408242410 | [JP] |
| URAやクライマックスでは各種のトレーニングを4回行うと、アオハル杯ではチームステータスによってトレーニングLvを上げられる | Level-up rule differs by scenario | GameWith 257432 | [JP] |
| 毎年1回、7月前半〜8月後半の4ターンの間は、5種類全てのトレーニングレベルが最大のLv5になる | Summer camp: all five disciplines are Lv5 for 4 turns (July-early to August-late) | Kamigame 146276970408242410 | [JP] |
| 賢さトレーニングは、体力を回復しつつ絆ゲージを上げることができます | Wit training builds bond gauge while refunding energy | Game8 454202 | [JP] |
| 賢さ1200を超えていると、固有スキル、進化スキル、レアスキルの速度を上げる効果と前に出る効果が上昇する | Above Wit 1200, unique/evolved/rare skill speed and front-running effects scale further | Game8 372949 | [JP] |
| 賢さ: 掛かり状態のなりにくさ・レース運びの上手さ・スキルの発動率 | Wit governs over-exertion resistance, route handling, and skill activation rate | Game8 372572 / 372949 | [JP] |

Other printed stat thresholds from Game8 372949 (`ステータスの意味と影響する要素`, 最終更新日 2026.09.24 12:46):

| JP string as printed | EN mechanical translation | Tag |
|---|---|---|
| スピードが2000を超えるとレース終盤に全開スパート効果が発動 | Speed above 2000 unlocks the full-throttle spurt effect | [JP] |
| スタミナが1200を超えていると、スタミナ勝負効果が使用 | Stamina above 1200 unlocks the stamina-duel effect | [JP] |
| パワーは…1200を超えていると脚をためる効果が使用 | Power above 1200 unlocks the leg-holding effect | [JP] |
| 短距離: 1400m以下 / マイル: 1401m~1800m / 中距離: 1801m~2400m / 長距離: 2401m以上 | Distance bands | [JP] |

---

### 4.6 Wit rainbow energy refund, per card (the escalation term in the Wit loop)

GameWith 293360 (`賢さ友情回復量アップ持ちサポートカード一覧`, 最終更新 2026年9月14日10:10). These are the `A+B` bonus
values that GameWith 274990 adds onto the printed flat base of `5` (`基準回復値の5`). `-` is the value the page prints
where the effect has not unlocked at that limit break.

Value ladder `3 / 3 / 4 / 4 / 5` across Lv30(無凸), Lv35(1凸), Lv40(2凸), Lv45(3凸), Lv50(4凸), shared by:
ファインモーション, エアシャカール, ユキノビジン, セイウンスカイ, ナイスネイチャ, ミホノブルボン, カレンチャン,
ナリタタイシン, ニシノフラワー, サトノダイヤモンド, シリウスシンボリ, ミスターシービー, ライスシャワー,
マチカネタンホイザ, ナカヤマフェスタ, トウカイテイオー, オグリキャップ, エアグルーヴ, スイープトウショウ, ヒシアケボノ,
メジロラモーヌ, テイエムオペラオー, ダイタクヘリオス, マンハッタンカフェ, メジロマックイーン, ノースフライト,
ネオユニヴァース, コパノリッキー, タイキシャトル, シンボリクリスエス, シーザリオ, ダイワスカーレット,
アグネスデジタル, シンボリルドルフ, イクノディクタス, デアリングタクト, デアリングハート, エアメサイア,
ウインバリアシオン, クロノジェネシス, デュランダル, サイレンススズカ, フォーエバーヤング, フサイチパンドラ.

| JP card / rarity group | Lv30 | Lv35 | Lv40 | Lv45 | Lv50 | Tag |
|---|---|---|---|---|---|---|
| Standard SSR group (list above) | 3 | 3 | 4 | 4 | 5 | [JP] |
| フジキセキ / ダイワスカーレット / アグネスタキオン / マーベラスサンデー / マチカネフクキタル / メジロドーベル / メジロアルダン / アイネスフウジン / スイープトウショウ / ゴールドシチー / セイウンスカイ / マルゼンスキー / タイキシャトル / タニノギムレット / ダンツフレーム / サクラローレル / ラッキーライラック / ビコーペガサス / ヒシアマゾン | 3 | 3 | 3 | 3 | 4 | [JP] |
| シンボリルドルフ / ダイワスカーレット / ファインモーション / アグネスタキオン / エアシャカール / マーベラスサンデー / マチカネフクキタル (lower group) | 2 | 2 | 2 | 2 | 3 | [JP] |
| ビワハヤヒデ | 2 | 2 | 2 | 2 | 3 | [JP] |
| メジロドーベル (R) | - | - | - | 1 | 5 | [JP] |
| ナイスネイチャ (R) | - | 1 | 3 | 3 | 3 | [JP] |
| レッドディザイア | - | - | - | - | 5 | [JP] |

Effective Wit rainbow refund on a flat-base-5 rule: `5 + bonus`. A 4-凸 standard Wit card therefore returns `5 + 5 = 10`
energy, versus `5` printed in the base-value tables when no rainbow fires.

### 4.7 Training level progression, which drives the Wit curve

GameWith 257618 (`トレーニングレベルの上げ方と必要回数`, 最終更新 2024年2月26日19:36):

| JP string as printed | EN mechanical translation | Tag |
|---|---|---|
| トレーニングにはレベルが1~5まで存在し…基本的にどのトレーニングも初期値は1から始まる | Level range 1..5, every discipline starts at 1 | [JP] |
| URAファイナルズ / クライマックス / グランドライブ: 4回刻みで上昇する | Those three scenarios: +1 level every 4 selections | [JP] |
| 合宿中のトレーニングはカウントされない | Camp selections do not count toward level-up | [JP] |
| レベル1 初期 / レベル2 4回 / レベル3 8回 / レベル4 12回 / レベル5 16回 | Cumulative selections to reach each level: 0, 4, 8, 12, 16 | [JP] |
| アオハル杯: レベル1 評価G~F / レベル2 評価E~D / レベル3 評価C~B / レベル4 評価A / レベル5 評価S | Aoharu maps team rating to level: G-F=1, E-D=2, C-B=3, A=4, S=5 | [JP] |
| アオハル杯: トレーニング回数では上がらない | Aoharu levels never advance by repetition | [JP] |
| プロジェクトL'Arc: 期待度が20,60,100で全トレがLv1上昇 / 40,80 は上昇なし | L'Arc: expectation 20, 60 and 100 raise every facility by 1; 40 and 80 raise nothing | [JP] |
| UAFシナリオではトレーニングLvの概念は存在しない | UAF has no training level at all, only the competing-event level | [JP] |



## 5. Friendship / rainbow training trigger (`友情トレーニング` / `虹トレーニング`)

### 5.1 The bond gauge threshold

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| サポートキャラの絆ゲージをオレンジ色以上にすることで、友情トレーニングが発生するようになる | Rainbow training unlocks once bond reaches orange | GameWith 257432 | [JP] |
| 絆ゲージがオレンジ以上でサポートカードとタイプが一致しているトレーニングを行うと、友情トレーニングが発生する | Orange plus matching specialty type fires it | GameWith 257607 | [JP] |
| 条件1：絆ゲージがオレンジ色である / 絆ゲージをオレンジ（4メモリ）以上にする | Condition 1: bond at orange, i.e. 4 memory segments | Game8 372572 | [JP] |
| 絆ゲージをオレンジ（80）以上にする / 4メモリ目の「80」まで行くとゲージがオレンジ色に変わり | The orange mark is the numeric value 80, the 4th segment | Game8 454202 | [JP] |
| 友情トレーニングを発生させるには、サポートの絆ゲージを80まで上げる必要がある。絆ゲージ80は色だと緑の次であるオレンジ、ゲージの区切りだと4ゲージ目 | Threshold is 80; green is the 3rd, orange the 4th | Kamigame 149493615532457367 | [JP] |
| 絆ゲージは初期値0、最大値100となる。初期値が増えることで80以上で発生する友情トレーニングまでに必要な絆ゲージが減る | Bond gauge runs 0 to 100; rainbow threshold is 80 | GameWith 274990 | [JP] |
| 絆ゲージが8割以上になると友情練習が可能になる | Rainbow at 80% of the gauge | Kamigame 114672877806026759 | [JP] |

Consensus numeric threshold: `80` on a `0..100` bond gauge, 4th segment, colour orange (`オレンジ`).
Second condition, printed identically on all three sites: the support card's specialty type (`得意タイプ`) must match
the training facility chosen.

### 5.2 Bond gauge gain per action

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| ウマ娘のいるトレーニングに参加: +7 / ∟「！」マークのついたウマ娘: +5 | Joining the training a card is at: +7; if that card carries a `!` mark: +5 instead | Game8 454202 | [JP] |
| サポートカードイベント: +5〜+10(+5がほとんど) | Support card events: +5 to +10, mostly +5 | Game8 454202 | [JP] |
| にんじんBBQセット(クライマックスシナリオ限定): 全サポート+5 | Carrot BBQ Set (Climax only): every support +5 | Game8 454202 | [JP] |
| サポートの絆ゲージはトレーニングをすると5上がり | Training gives +5 bond | Kamigame 149493615532457367 | [JP] |
| 「！」の付いたウマ娘は、絆ゲージが更に+5されます(2人以上いる時はランダムで1人) | A `!` card gives a further +5, one random card only if several qualify | Game8 454202 | [JP] |
| 愛嬌◯: サポートの絆ゲージ上昇量が+2される | Condition 愛嬌◯ (Charming ◯): bond gain +2 | Game8 374294 / Kamigame 149493615532457367 | [JP] |
| 注目株: 記者と理事長の絆ゲージ上昇量が+2される | Rising Star: reporter and chairman bond gain +2 | Game8 374294 | [JP] |

Note: Game8 prints +7 per training and Kamigame prints +5. Both are recorded under `## Disagreements`.

### 5.3 Rainbow-training multipliers and the published calculation

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| (トレーニングの基礎値+ボーナス値)×成長率ボーナス×やる気(1+やる気ボーナス×やる気効果アップ)/100×トレーニング効果アップ×友情ボーナス×参加人数ボーナス(1人につき5%) | Full rainbow training chain, with the participant bonus printed as 5% per person | Game8 454202 | [JP] |
| 友情ボーナスの計算式: 1.25×1.3=1.625 | Worked example: a +25% card times a +30% card equals x1.625 | Game8 454202 | [JP] |
| キタサンブラック 友情ボーナス+25% / ナリタトップロード 友情ボーナス+30% | Kitasan Black +25% friendship bonus; Narita Top Road +30% | Game8 454202 | [JP] |
| 友情ボーナスは、キャラごとの値が乗算される | Per-card friendship bonuses multiply, they do not add | Game8 454202 | [JP] |
| 計算式: 基準値×(1+友情ボナA/100)×(1+友情ボナB/100) | Same rule restated with the percentage divided by 100 | Kamigame 149493615532457367 | [JP] |
| 友情トレーニング自体にボーナス値はありません。存在はしませんが、もし仮に友情ボーナスが無いカードと友情トレーニングをした場合、通常のトレーニング全く同じ数値の上昇値となります | Rainbow-ness itself carries no bonus; only the card's 友情ボーナス value does. A zero-bonus card gives a plain-training result | GameWith 274990 | [JP] |
| およそ1.5倍ほど多くもらえる | Rainbow training yields roughly 1.5x | Game8 372572 | [JP] (editorial approximation, not a formula) |
| サポート効果（友情トレーニングの発生していない参加者）: トレーニング効果アップ / やる気効果アップ / ステータスボーナス | Non-rainbow participants still contribute training-effect-up, motivation-effect-up and stat bonus to the calculation | Game8 454202 | [JP] |
| 虹色でない参加者も一部の効果が乗る / 参加キャラのタイプが一致していない場合、友情ボーナスは計算に適応されません | Type-mismatched participants contribute no friendship bonus | Game8 454202 | [JP] |
| サポートキャラクターの絆ゲージを…友情トレーニングが発生するようになる。友情トレーニングが発生することで、通常のトレーニングより多くステータスを上げられる | Rainbow gives more stat than a normal session | GameWith 257432 / 257606 | [JP] |

### 5.4 Named support-effect identifiers cited

These wikis name effects, they do not publish numeric effect ids. The names as printed, from GameWith 274990's
25-effect table (`全25種類のサポート効果が存在`):

| JP effect name | EN mechanical translation | Printed definition | Tag |
|---|---|---|---|
| 友情ボーナス | Friendship Bonus | Boost from a rainbow session firing | [JP] |
| 失敗率ダウン | Failure Rate Down | Failure rate when training together is reduced | [JP] |
| 体力消費ダウン | Energy Cost Down | Energy spent when training together is reduced | [JP] |
| トレーニング効果アップ | Training Effect Up | Stat gain when training together is increased | [JP] |
| やる気効果アップ | Motivation Effect Up | The mood correction's magnitude is amplified | [JP] |
| ステータスボーナス | Stat Bonus | Added to the base value before multipliers, per stat | [JP] |
| スキルPtボーナス | Skill Pt Bonus | Skill point gain when training together is increased | [JP] |
| 賢さ友情回復量アップ | Wit Rainbow Recovery Up | Energy refunded by a Wit rainbow session is increased | [JP] |
| 得意率アップ | Specialty Rate Up | Appearance rate of the card's specialty facility | [JP] |
| 初期絆ゲージ | Initial Bond Gauge | Bond gauge at run start | [JP] |
| ヒントLvアップ / ヒント発生率アップ | Hint Level Up / Hint Occurrence Rate Up | Hint level and hint-event probability | [JP] |
| レースボーナス / ファン数ボーナス | Race Bonus / Fan Count Bonus | Post-race stat, skill-point and fan rewards | [JP] |
| イベント回復量アップ / イベント効果アップ | Event Recovery Up / Event Effect Up | Energy and stat gains from that card's events | [JP] |
| 初期ステータスアップ | Initial Stat Up | Fixed addition to start-of-run stats, unaffected by growth rate | [JP] |

| JP string as printed | EN mechanical translation | Tag |
|---|---|---|
| 固有ボーナスとサポート効果に同じ効果がある時は重複する | A card's unique bonus stacks with its own support effect of the same kind | [JP] |
| (計算式) 基準消費量-(基準消費量×体力消費ダウン)=体力消費量 / 消費量に対して乗算で計算されて端数は切り上げ | Energy cost down multiplies the cost, remainder rounds UP | [JP] |
| (計算式) 基準回復値+賢さ友情回復量A+B=体力回復量 / 全ての賢さ友情回復量を加算した数値+基準回復値の5。ただし友情トレーニングが発生していないと効果がない | Wit rainbow recovery = flat base 5 plus summed bonus; requires the rainbow to fire | [JP] |
| (計算式) 上昇値×(1+トレーニング効果A×B) / トレーニング効果は全て加算と思われる | Training effect up stacks additively | [JP] |
| (計算式) 基準値+(スキルPtボーナスA+B)=スキルPt上昇量 | Skill point bonus stacks additively on the base | [JP] |
| (計算式) 基準値(約18%)×得意率=得意トレーニング出現率 | Specialty rate: base ~18% per facility times the specialty-rate value | [JP] |
| 全く出現しない確率が約10％だったため、トレーニング出現率が90%、各トレーニング毎は約18%と仮定 | Modelled appearance rates: 90% overall, ~18% per facility | [JP] (GameWith's own verification estimate) |
| (計算式) 基準値×ヒント発生率アップ / ヒント発生率基準値は約6％前後と思われる | Hint-rate base ~6%, multiplied by the hint-rate-up value | [JP] (estimate) |

❌ UNVERIFIED: numeric effect ids. No page rendered here prints an integer effect id, enum value, or database key.
They publish effect names in Japanese plus percentage values only.

---

## Appendix: printed values not covered above

| JP string as printed | EN mechanical translation | Source | Tag |
|---|---|---|---|
| 追加の自主トレ 選択肢①: 対応するトレーニングの能力+5 / 体力-5 ; 選択肢②: 体力+5 | Extra self-training event: option 1 gives that discipline's stat +5 and costs 5 energy; option 2 gives +5 energy | GameWith 257614 | [JP] |
| あんし〜ん笹針師 秘孔を狙う(成功): 5種ステータス+20 / (失敗): やる気ダウン・全ステータス-15・夜ふかし気味になる | Acupuncturist event option 1: all five stats +20 on success; motivation down, all stats -15, late-night on failure | GameWith 257614 | [JP] |
| (成功): コーナー回復◯取得・直線回復取得 / (失敗): 体力-20・やる気ダウン | Option 2: two corner/straight recovery skills on success; energy -20 and motivation down on failure | GameWith 257614 | [JP] |
| (成功): 体力の最大値+12・体力+40 / (失敗): 体力-20・やる気ダウン・練習ベタになる | Option 3: max energy +12 and energy +40 on success; energy -20, motivation down, Practice Untalented on failure | GameWith 257614 | [JP] |
| (成功): 体力+20・やる気アップ・愛嬌○になる / (失敗): 体力-10・やる気ダウン | Option 4: energy +20, motivation up, Charming ◯ on success; energy -10 and motivation down on failure | GameWith 257614 | [JP] |
| 不安なのでやめておく: 体力+10 | Option 5 (decline): energy +10 | GameWith 257614 | [JP] |
| 1日10回までしかレンタルできない | Rented ancestor: 10 borrows per day | GameWith 257614 | [JP] |
| レンタル回数が増える…ウマプランの購入でも増やすことができます | Campaigns and the Trainer Plan subscription raise the daily borrow cap | GameWith 257614 | [JP] |
| 夏合宿は7月前半から8月後半までの4ターン続く | Summer camp is 4 turns | GameWith 257614 | [JP] |
| 太り気味: トレーニングでスピードが上がらなくなる / スピード上昇量が0 | Weight Gain: training Speed gain becomes exactly 0; event stat gains are unaffected | Game8 374294 | [JP] |
| なまけ癖: 確率でトレーニングが無効になる / ウマ娘がトレーニングに来ない | Lazy Habit: the session is voided at random | Game8 374294 | [JP] |
| まだまだ準備中: レース出走後に確率で体力-5 (メイショウドトウ固有) | Still Preparing, Meisho Doto exclusive condition: energy -5 after a race, at random | Game8 374294 | [JP] |
| 切れ者: スキル獲得に必要なSPが全て10%軽減 | Clever: all skill costs reduced 10% | Game8 374294 | [JP] |
| ファンとの約束: 指定レース勝利で能力アップ (スマートファルコン固有) | Promise to Fans: nominated-race win grants a stat bump | Game8 374294 | [JP] |
| リフレッシュの心得 (グッドコンディション一覧に掲載) | Listed as a good condition; no numeric effect printed on the pages read | Game8 417620 | [JP] |

---

## Source Ledger

| URL | 最終更新日 | what it contributed |
|---|---|---|
| https://gamewith.jp/uma-musume/article/show/257614 | 2026年9月25日02:39 | Rest +30 late-night case, energy-50 operator threshold, Wit-as-recovery rule, camp 4 turns, 10/day ancestor borrows, self-training and acupuncturist event numbers, Friend outing unlock, rainbow-session camp failure note |
| https://gamewith.jp/uma-musume/article/show/257432 | 2023年2月25日17:08 | Core failure rules, both failure events with exact deltas, Wit cannot fire a failure event, full per-scenario per-level base-value tables behind the scenario tab control, discipline-to-stat map |
| https://gamewith.jp/uma-musume/article/show/257617 | 2021年7月5日10:15 | Rest outcome probabilities with raw counts (n=3833), camp rest +40 with motivation, nurse's-office cure of late-night |
| https://gamewith.jp/uma-musume/article/show/257538 | 2021年9月22日17:37 | Five mood tiers with training +20/-20 and race +4/-4, camp Rest gives recovery plus motivation, events can't exceed Peak |
| https://gamewith.jp/uma-musume/article/show/257606 | 2021年2月26日20:44 | Rainbow definition, orange bond precondition, specialty-type match requirement |
| https://gamewith.jp/uma-musume/article/show/257607 | 2021年2月26日20:54 | Bond gauge raised by training and by events; orange gate drives rainbow and some events |
| https://gamewith.jp/uma-musume/article/show/263413 | 2023年3月6日14:21 | Outing outcome table with counts (n=119), Karaoke 2 tiers, Walk +10 energy, shrine fortunes +10/+20/+30, crane game, run starts at Normal |
| https://gamewith.jp/uma-musume/article/show/274990 | 2022年5月18日12:04 | Failure-rate formula, mood factor table 1.2/1.1/0/-0.9/-0.8, bond gauge 0..100 with rainbow at 80, Wit rainbow base recovery 5, skill-point bases 4 Wit / 2 other, all 25 support effect names, specialty ~18%, hint ~6% |
| https://gamewith.jp/uma-musume/article/show/286339 | 2021年7月2日18:21 | Nurse's office energy +20, cure rate 84%/84.68% with raw counts, 15%-85% inferred rule, tries-to-cure distribution, camp blocks the office |
| https://gamewith.jp/uma-musume/article/show/293379 | 2026年9月14日10:10 | Failure-rate-down support card table by limit break, 22 rows, plus the unique-bonus inclusion note |
| https://gamewith.jp/uma-musume/article/show/257618 | 2024年2月26日19:36 | Level curve 0/4/8/12/16 selections, camp sessions excluded from the count, Aoharu rating-to-level map, L'Arc expectation 20/60/100, UAF has no training level |
| https://gamewith.jp/uma-musume/article/show/293360 | 2026年9月14日10:10 | Wit rainbow energy-refund bonus per support card by limit break, the 3/3/4/4/5, 3/3/3/3/4 and 2/2/2/2/3 ladders and the unlocked-later rows |
| https://gamewith.jp/uma-musume/article/show/258369 | 2026年9月16日16:03 | Consumable item list, used to confirm アイシング is absent and to source Motivation Up Sweets and the Alarm Clock |
| https://gamewith.jp/uma-musume/article/show/317671 | 2026年9月26日21:34 | Scenario stat caps per scenario, scenario release dates, used only to confirm scenario naming |
| https://game8.jp/umamusume/372572 | 2026.09.10 13:35 | Failure outcome trio (energy/motivation/injury), penalty scales with failure rate, mood table with race 10%/5% variant, Wit no-cost statement, rainbow ~1.5x, 4 trainings per level, orange = 4 segments |
| https://game8.jp/umamusume/454202 | 2025.11.21 01:50 | Bond threshold printed as 80, bond gain +7 (or +5 with `!`), event gain +5..+10, BBQ set +5, full training formula with 5% participant bonus, friendship bonus multiply example 1.25x1.3=1.625 |
| https://game8.jp/umamusume/372949 | 2026.09.24 12:46 | Wit/Speed/Stamina/Power stat gates 2000 and 1200, distance bands 1400/1800/2400 |
| https://game8.jp/umamusume/374294 | 2025.11.20 22:11 | Full condition table with failure-rate deltas (-2/+2/-4/+5/-5), bond gain +2 conditions, mood-block and stat-block conditions, overwrite rule |
| https://game8.jp/umamusume/417620 | 2025.11.20 22:11 | Late-Night Feeling per-turn deltas, rest event patterns, office blocked in camp, Tazuna outing stages 2 and 4 cure certain |
| https://game8.jp/umamusume/372297 | 2026.09.25 19:43 | New scenario resource table (base gauge 10/5/3, hint items, outing condiment counts), current scenario lineup |
| https://kamigame.jp/umamusume/page/164094542528651333.html | 2021-10-15 17:35 | Rest outcomes +30/+50/+70, Wit refund statement, Friend outing at bond 3, energy drop raises injury likelihood |
| https://kamigame.jp/umamusume/page/114672877806026759.html | 2025-03-07 14:10 | Cross-scenario standing rules: bond 80 for rainbow, rest below 50% energy, keep Peak, camp rainbow x4 target, bad condition to office |
| https://kamigame.jp/umamusume/page/147307607101602682.html | 2022-12-19 10:22 | Named rest events with percentages (n=100): +70 12%, +50 66%, +30 22%, late-night 5%; rest-vs-outing comparison |
| https://kamigame.jp/umamusume/page/146407792159257608.html | 2024-04-14 13:50 | Friend-card outing chains with exact per-stage numbers, unlock event chains per card |
| https://kamigame.jp/umamusume/page/146417173592559219.html | 2021-10-15 16:53 | Five mood tiers with training +20/-20 and race +10/-5 |
| https://kamigame.jp/umamusume/page/149493615532457367.html | 2022-12-19 10:17 | Rainbow threshold 80 as the 4th segment, bond +5 per training, friendship bonus multiply formula, rainbow can fail, camp item list |
| https://kamigame.jp/umamusume/page/146276970408242410.html | 2021-10-15 17:10 | 4 trainings per level, Lv5 cap, camp all facilities at Lv5 for 4 turns, mood tier table |
| https://kamigame.jp/umamusume/page/164086749461487685.html | 2024-04-15 14:02 | Bad-condition cure route ranking, office clears only one, camp rest clears all, shrine less reliable than office |
| https://kamigame.jp/umamusume/page/146301691988282803.html | 2026-08-31 14:56 | Wit support-card ranking page; rendered successfully but contains no training mechanics, only card scores |

---

## Disagreements

| Topic | Value A | Value B | Value C |
|---|---|---|---|
| Mood effect on in-race base stats | GameWith 257538: 絶好調 +4%, 好調 +2%, 不調 -2%, 絶不調 -4% | Game8 372572: 絶好調 +10%, 好調 +5%, 不調 -2%, 絶不調 -5% | Kamigame 146417173592559219 and 146276970408242410: identical to Game8 (+10/+5/-2/-5) |
| Mood effect on training | No disagreement. All three print +20/+10/0/-10/-20 | same | same |
| Rest outcome probabilities | GameWith 257617 (n=3833): +70 25.4%, +50 58.1%, +30 12.8%, +30 with late-night 3.7% | Kamigame 147307607101602682 (n=100): +70 12%, +50 66%, +30 22% of which late-night 5% | Same outcome set, materially different distribution; GameWith's sample is 38x larger |
| Rest event names | GameWith prints recovery amounts only (`70回復`, `50回復`, `30回復`, `30回復+夜ふかし`) | Kamigame prints named events (`休息はバッチリ！`, `リフレッシュ完了`, `寝不足で……`) | Not a numeric conflict, but the two wikis index the same outcomes differently |
| Bond gauge gain per training | Game8 454202: `+7` for joining a card's training, `+5` when the card carries `!` | Kamigame 149493615532457367: `トレーニングをすると5上がり` | Both agree the gauge tops out around 80 for rainbow |
| Guts training secondary stats | GameWith 257432: 根性 raises 根性(大), スピード(中), パワー(中) | Game8 372572: 根性 raises スピード, パワー, 根性(大) | Consistent, no conflict on direction, only on the large/medium labeling |
| Stamina training secondary | GameWith 257432: スタミナ(大up), 根性(中up) | Game8 372572: スタミナ(大アップ), 根性, スキルPt | Same |
| Rainbow strength | Game8 372572: `およそ1.5倍` (approximately 1.5x), stated as an approximation | GameWith 274990: `友情トレーニング自体にボーナス値はありません`, the multiplier comes only from each card's 友情ボーナス, e.g. 1.25 x 1.30 = 1.625 | Kamigame gives only the multiply formula, no flat estimate |
| Failure penalty shape | GameWith 257432: two named events with fixed choice-dependent numbers (stat -5 or -10, sometimes two extra stats -10, energy 0 or +10) | Game8 372572: `体力低下・やる気低下・ケガ（能力低下）のいずれか`, i.e. one of three, and magnitude scales with failure rate | The two describe different shapes; GameWith enumerates the event outcomes, Game8 describes the outcome class. Neither publishes a per-rate penalty table |
| Injury as a system | Game8 372572 lists ケガ as a failure outcome | GameWith 257432 lists no injury timer, only stat drops and 練習ベタ/練習下手 conditions | No page read prints injury duration or severity numbers |
| Wit skill-point payout | GameWith 274990: `基準値は賢さトレーニングが4、それ以外が2` | GameWith 257432 per-scenario tables: URA 4 vs 2, Climax 3 vs 2, Aoharu 5 vs 4, Grand Live 5 vs 4, Grand Masters 5 vs 5 | The 4-vs-2 statement is URA-specific; the scenario tables are the general rule |

Excluded from the ledger deliberately: no prediction model, no race-simulation rule, and no real-world ancestor
information appeared on any page in a form I could tie to a training mechanic, so none is recorded.
