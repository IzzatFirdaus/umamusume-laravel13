# Rice Shower Unity Cup UX walk 9

**Audit ID:** RS-UC-2026-11  
**Status:** Completed walk (owner-ordered rerun, 2026-11-17). Sections §3b–§10b below carry the completed primary pass: 19 screens, run #36 created through the UI at the source's turn-67 position, F=0 / E=3 / U=7, no dead end. The earlier S01-only attempt (ENV-01, hidden embedded surface) and PRE-S01/PRE-S02 remain below as superseded history. Addendum text is append-only; superseded claims are retained, not rewritten.

## 1. Header and evidence boundary

- Source: `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` (SR), read end to end once, lines 1–1653, before the browser pass. User-supplied client transcriptions (UT) are separate reference fixtures.
- Secondary: `docs/UMAMUSUME_REFERENCE.md`, targeted context only, not application evidence.
- Entry: `http://127.0.0.1:8000/`. Application already running; no startup/build/restart/cache clear.
- Initial preparation: 2026-11-17T00:17:00+08:00. Superseded attempt began 00:20:53+08:00, first observation 00:21:46+08:00, last PRE-S02 metadata 00:30:38+08:00. These are not the fresh pass's timestamps.
- Fresh primary attempt: initial-navigation timing was captured with its first observation at **2026-11-17T00:48:32.497+08:00**; exact navigation invocation wall-clock not separately sampled. Final browser observation/end **2026-11-17T00:51:34.445+08:00**. Timezone Asia/Kuala_Lumpur, UTC+08:00.
- Fresh environment: same embedded browser/user agent, viewport 736×785 CSS pixels, DPR 1, document visibility `hidden`. Viewport changed from preliminary 825×785 without an agent resize. `select_page` with `bringToFront:true` was issued once before fresh navigation; screenshot still unavailable. No second tab, browser install, server restart, build, cache clear, data reset, zoom, throttling or emulation changes. Owner explicitly authorized starting again from `/`; thereafter no Back/reload/restart.
- Fresh entry HTTP 200, 277.8 ms. New-career activation twice (initial plus one permitted retry) produced no observed route change or `/career/` resource request. The retry's 30,000 ms wait timed out. Measurement limitation **ENV-01**, not a proven product defect; the interaction failure's root cause is unresolved. Console capture showed only existing Boost logger messages; observed local assets returned 200/304. Preserved pre-rerun requests are not fresh-pass traffic evidence.
- Qoder 0.5.12 / Chrome 150.0.7871.250 / Electron 43.8.4; Windows NT 10.0 Win64/x64 (host 10.0.26300). Read-only MCP DOM/console/network inspection; DevTools dock state not independently measured. User agent: `Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Qoder/0.5.12 Chrome/150.0.7871.250 Electron/43.8.4 Safari/537.36`.
- UI v3.0.0 was recorded during preliminary preparation, not independently re-established by a visible fresh-pass version label. Preliminary document HTTP 200, navigation duration 723.8 ms. Network-list tool initially returned no requests, which does not prove zero traffic. Preliminary console contained only the existing Boost browser-logger activation message.
- Preflight and final-check HEAD `3cbbd8b9c480d9175221471e87ec517f95f8a5f8`, master, dirty shared tree. Application/config/test/docs changes and many deleted earlier audits predated this session. No index operation during preflight; the later authorized consolidation staged only the root walk-7 deletion, as recorded below.
- Source fingerprint: 151468 bytes, preflight line count 1653, SHA-256 `c92eb1c7d97837cb1315f0b35de3ee2c9e1be3b4827ad4072b20c20db5de78bd`, mtime 2026-11-06T19:48:37.622Z; no source diff at preflight.
- Secondary fingerprint: 959519 bytes, preflight line count 3395, SHA-256 `aa5c72c0a60e497a0581ac7485d51241cc93b2f4f8d1892bba382f0e5bd80cf6`, mtime 2026-11-13T17:53:11.600Z; pre-existing +30-line unstaged diff. First read this session, so growth relative to an earlier session is unknown.
- End comparison at 2026-11-17T00:57:07+08:00: both byte sizes and SHA-256 fingerprints match preflight exactly, so neither source changed during these attempts. Only bytes were hashed; contents were not reopened for entry decisions. Python `splitlines()` counted 1652/3394 lines rather than the preflight 1653/3395. A second byte-only check reproduced 1653/3395 with `split(b'\n')`: the terminal empty segment explains the one-line discrepancy, not source growth or loss. Frozen source anchors and transcriptions were not altered.
- Browser preflight listed no open in-app page. Cookies/storage not inspected or cleared. Dashboard already had 11 active careers, 8 finished runs, resume Admire Vega/Unity Cup at run 35, and veteran links 22/21/20. None belong to this audit and none were opened.
- Owner correction during PRE-S01: create a new walk, do not update an existing one. Walks 7 and 8 are reserved in HEAD even where absent at their old paths. This root-level walk-9 path was absent on disk and from HEAD. The initial session draft had been written at the originally specified root-level walk-7 path, which was absent/deleted before the session. A shell move was blocked; no move ran. Walk 9 was created directly. The owner then explicitly requested merging that session draft into this report: its full frozen sheet and coverage statements are preserved here, retaining walk 9's deck comparison and new-report history. The root-level walk-7 draft was retained at that intermediate merge, not treated as another completed walk. The owner then clarified consolidation and rerun: after rechecking exact sheet/coverage preservation, the redundant root draft was removed using `git rm -f -- docs/audits/rice-shower-unity-cup-ux-walk-7.md` because the hook refused shell deletion and required explicit Git operations. This stages only that deletion; no commit or push. Historical walks remain untouched. Removed draft SHA-256: `4f77fbb259515c1aca5126ed69a9806e2c9d8d8189c147214d433b662ca50d7b`.
- Current audit organization, supplied by the owner and verified by filenames only: previous walks in [ux-walkthroughs](ux-walkthroughs/), per-walk reconciliation in [reconciliation-rounds](reconciliation-rounds/), [reconciliation register](reconciliation-register/RECONCILIATION-REGISTER.md), and [capability matrix](capability-matrix/rice-shower-unity-cup-capability-matrix.md). Their contents were not opened or merged. In particular, the historical `ux-walkthroughs/rice-shower-unity-cup-ux-walk-7.md` is not the root-level session draft used in this merge.
- The preflight dirty index included a roadmap path with both staged and unstaged changes. No other deleted audit was restored or staged; existing index entries were left alone. Walk 9 remains untracked, the root walk-7 deletion is staged, and no commit or push was made.

### Coverage and contamination

The revised source §10, lines 1570–1595, says client captures cover Senior-year endgame: Career, Sparks, Perks, Learn, Log/rail, Career Profile (Featured Cards, Unity Cup Available, Umamusume Class, Goals, Umamusume Details), training tile, Team Info, roster and race-day views. It also describes post-career Legacy panels. They do not show the pre-career setup, turn 0, team formation/name selection, the preseason rounds' own play, finals race cards or post-finals state. Any statement about the **client's setup path** here is source-transcribed or publisher-derived, not frame-observed. Observations of Trainer Desk setup must come from this browser pass.

The user supplied frame transcriptions in §§2.3b–g; these are reference fixtures, not frames personally inspected in this session. A delegated repository filename search found none of the supplied October prefixes (e771563c, 2161e855, fd78e996, 1dde6b2f). It located July 15 client captures under `docs/game-screenshots/`, but did not establish correspondence to this October run. Those different-date images are not substituted. The requested high-resolution UG/UG1 confirmation is unavailable; if the field is reached, the unresolved badge will be recorded rather than claimed pixel-verified.

Quarantined context read in the source: `support_cards` rows 30003/30016/30018/30019/30020/30030; `SupportCard::isScenarioLink('unity_cup')`; scenario configuration; `CareerTraineeSelectTest`; the committed GameTora export; maximum effect sets and prior client rows; D8 remediation and `AUDIT-AND-VERIFICATION.md` references. These say nothing about what this pass sees. No prior walk or reconciliation document was opened. The user prompt and automatically supplied repository memories contain prior findings, including claimed rank refusal, removed fan ladder and missing card tabs; those are hypotheses only. A governance-heading search incidentally returned snippets from a historical frontend audit, and the required quality-bar read included historical Legacy-schema rulings; those too are excluded as evidence. Neither prior reproduction nor non-reproduction will be claimed without new browser observation.

### Plan and acceptance

1. Freeze source values before browsing (done).
2. One forward browser pass from Dashboard; find the current-position snapshot door and preserve turn 67/in-progress identity.
3. Record each screen before leaving, controls/inputs/errors/transition and screenshot; document defects without fixes. Stop at genuine dead end, entry failure or bounded 30-second crash/hang.
4. Complete every source disposition and P-01–P-64, expanded findings, method consequences and artifact checks. No second pass planned.

Only this new report/screenshots and ordinary UI submissions are in scope. No source/app/config/environment/test changes, seeders, factories, SQL, direct DB writes, API substitution, request replay or DOM editing. Successful UI submissions would write run data and must be reported, never called “no data modified.”

## 2. Frozen source data sheet

Prepared before any browser navigation. After the pass begins, this sheet alone supplies entry values. `SR` below means the source document named in §1; references give its current section title and line span. `UT` means the user's explicitly supplied frame transcription, not independent image inspection. Parenthetical historical alternatives are preserved rather than silently resolved.

### 2.1 Identity carried unchanged

| Field | Source identity |
| --- | --- |
| Trainee/card | Rice Shower, `[Rosy Dreams]` |
| Rarity/potential | ★3; Potential Lvl 2 (the source also discusses the name awakening) |
| Scenario | Unity Cup; full label `Unity Cup: Shine On, Team Spirit!` |
| Moment | Senior Year Early Oct; turn 67 of 72; 5 turns left; 5 until Unity Cup |
| Lifecycle | Mid-career snapshot, in progress; not completed, Veteran or Legacy |
| Run number | None present |
| Stats | Speed 607 (B), Stamina 577 (C+), Power 549 (C+), Guts 736 (B+), Wit 397 (D+) |
| SP/mood/energy | 173; GREAT; low near-one-third bar, no numeric Energy |
| Fans/class | 209,245; Star; Top Star 240,000; difference 30,755 |
| Deck | Six SSR: three Speed, one Stamina, two Guts; borrowed Super Creek |
| Owned skills | Ten Details chips plus Corner Recovery ○ on Learn; at least eleven |
| Ancestors | Two legacies and four grandparents; two separate Daiwa Scarlet instances; no Wit spark |
| Race state | Four preseason rounds won; finals pending |
| Objectives | Arima Kinen 1st at Senior Late Dec active; Nikkei Sho, Tenno Sho (Spring), Takarazuka Kinen cleared |

### 2.2 Entry sheet and current source anchors

| Datum | Value to enter or retain as unknown | Source reference |
| --- | --- | --- |
| Trainee | Rice Shower | SR §1.1 Run identity and moment, 47–52 |
| Card/epithet | `[Rosy Dreams]` | SR §1.1, 50 |
| Rarity | ★3 | SR §1.1, 50 |
| Potential/awakening | Potential Lvl 2; one value, not two independently known fields | SR §1.1, 50; §1.15 Umamusume Details, 478 |
| Scenario | Unity Cup | SR §1.1, 49 |
| Full scenario label | Unity Cup: Shine On, Team Spirit! | SR §1.1, 49 |
| Snapshot | Senior Year Early Oct | SR §1.1, 50–51 |
| Turn | 67 | UT §2.5: (3−1)×24+(10−1)×2+1=67 |
| Turns remaining | 5 | SR §1.1, 51; 72−67=5 |
| Lifecycle | Mid-career/in progress, not finished | SR §1.6 Unity Cup state, 209–218; §5 The plan for the five remaining turns, 868–895 |
| Run number | Unknown; do not invent | No source value |
| Speed | 607 | SR §1.2 Live stat line and the caps above it, 58 |
| Stamina | 577 | SR §1.2, 59 |
| Power | 549 | SR §1.2, 60 |
| Guts | 736 | SR §1.2, 61 |
| Wit | 397 | SR §1.2, 62 |
| Speed cap | 1325 | SR §1.2, 58 |
| Stamina cap | 1322 | SR §1.2, 59 |
| Power cap | 1368 | SR §1.2, 60 |
| Guts cap | 1308 | SR §1.2, 61 |
| Wit cap | 1800 | SR §1.2, 62 |
| Scenario floor | 1300/1300/1300/1300/1800, not this run's in-force cap | SR §1.2, 64–77 |
| Growth Rate | +0%/+10%/+0%/+20%/+0% | SR §1.2, 58–62; §1.15, 478 |
| SP | 173 unspent | SR §1.2, 64 |
| Fans | 209245 | SR §1.1, 51–52 |
| Mood | GREAT | SR §1.7 Mood, Energy, and the last observed training turn, 247–256 |
| Energy | Unknown numeric value; low bar near one third with warning | SR §1.7, 254–260 |
| Deck card 1 | Tokai Teio `[Dream Big!]`, SSR Speed, 35/35 | SR §1.4 Deck, all six cards, all SSR, 119–128 |
| Deck card 2 | Nishino Flower `[Even the Littlest Bud]`, SSR Speed, 30/30 | SR §1.4, 122 |
| Deck card 3 | Biko Pegasus `[Double Carrot Punch!]`, SSR Speed, 30/30 | SR §1.4, 123 |
| Deck card 4 | Super Creek `[Piece of Mind]`, SSR Stamina, 50/50, borrowed/Friends | SR §1.4, 124, 128–130 |
| Deck card 5 | Matikanetannhauser `[Just Keep Going]`, SSR Guts, 30/30 | SR §1.4, 125, 131–132 |
| Deck card 6 | Haru Urara `[Urara's Day Off!]`, SSR Guts, 35/35 | SR §1.4, 126 |
| Owned Details list | Blue Rose Closer Lvl 3; Red Shift/LP1211-M; Corner Adept ○; Swinging Maestro; Nimble Navigator; Steadfast; Deep Breaths; Extra Tank; Pace Chaser Straightaways ○; Shrewd Step | SR §1.3 Skills owned, 103–109 |
| Additional owned skill | Corner Recovery ○ (Obtained on Learn) | SR §1.3, 108 |
| Legacy 1 | Vodka `[Wild Top Gear]`, B+ | SR §1.5 Inheritance, from the Sparks panels, 173–207; UT §2.3b |
| Legacy 2 | Maruzensky `[Formula R]`, UG, Guest; UG1 unresolved | SR §1.5, 183; §6 What is still missing, 972–974 |
| L1 grandparent A | Daiwa Scarlet `[Peak Blue]`, B+ | SR §1.5, 181; UT §2.3b |
| L1 grandparent B | Seiun Sky `[Reeling in the Big One]`, A | SR §1.5, 182; UT §2.3b |
| L2 grandparent A | Daiwa Scarlet `[Peak Blue]`, S+; prior transcription UG retained as conflict | SR §1.5, 184; §11 Corrections C-03, 1615; UT §2.3b |
| L2 grandparent B | Taiki Shuttle `[Wild Frontier]`, UG | SR §1.5, 185; UT §2.3b |
| Blue sparks | Stamina 3 Seiun; Power 3 Taiki; Speed 2 Maruzensky; Speed 2 L2 Daiwa; Guts 1 L1 Daiwa and Power 1 Vodka in post-career table; in-career Guts 2 and Power 2 in UT | SR §1.5, 190–201; §6, 966–970; UT §2.3b; keep sets separate |
| Team/name/motto | Blue Bloom; Dreaming Big | SR §1.6, 211; §1.8 Team Info panel, 274 |
| Overall Team Rank | S, five stars | SR §1.8, 275 |
| League | 8th | SR §1.8, 274 |
| Unity Trainings | 55 | SR §1.8, 277 |
| Spirit Bursts | 6 | SR §1.8, 278 |
| Extreme Spirit Bursts | 5 | SR §1.8, 279 |
| Members | 20/20, but only 18 named including trainee | SR §1.8, 280; §1.9 The roster, 363–364 |
| Team stat grades | A/A/A/S/A | SR §1.8, 276 |
| Facility levels | 4/4/4/5/4 | SR §1.7, 262–264 |
| Preseason results | R1 after Junior Late Dec WIN; R2 after Classic Late Jun WIN; R3 after Classic Late Dec WIN; R4 after Senior Late Jun WIN | SR §1.6, 211–214; §1.15, 472 |
| Race scheduled now | Kyoto Daishoten, G2, Kyoto Turf 2400m Medium, Right/Outer, Fall, Soft; 2000 fans required | SR §1.6, 220–233 |
| Pending race | Unity Cup finals after Senior Late Dec, not run | SR §1.6, 213–214 |
| Active goal | Place 1st in Arima Kinen, Senior Late Dec/turn 72; Entry criteria met!; 5 turns | SR §1.6, 216–218; §1.15, 476 |
| Cleared goals | Nikkei Sho top 3 Senior Late Mar, fan criterion 2000; Tenno Sho (Spring) 1st Senior Late Apr, 20000; Takarazuka Kinen top 3 Senior Late Jun, 20000 | SR §1.6, 216–218; §1.15, 476 |
| Aptitudes, current frame reading | Turf A/Dirt C; Sprint E/Mile B/Medium A/Long A; Front A/Pace A/Late B/End C | SR §1.2, 88–101; §11 C-01/C-02, 1613–1614; UT §2.3g |
| Aptitudes, old supplied sheet | Turf A/Dirt G; Sprint E/Mile B/Medium A/Long A; Front A/Pace A/Late B/End G | User §2.1/§2.3, preserved as older transcription, not silently replaced |
| Aptitudes, catalogue claim | A/G/E/C/A/A/B/A/C/G | SR dated erratum 91–101; context only, must re-observe picker |

### 2.3 Per-instance ancestry fixtures

Use the in-career UT §2.3b rows if entering the in-career set, with SR §1.5 table retained as the post-career alternative. No assertion that held and transferred mean the same thing. Numeric star counts below stand for filled stars out of three; painted kind is distinct from name.

| Node | Blue | Pink | Green | White rows |
| --- | --- | --- | --- | --- |
| L1 Vodka, B+ | Power 2 | Late Surger 2 | Cut and Drive! 2 | Oka Sho 1; Victoria Mile 1; Shuka Sho 2; Japan C. 2; Extra Tank 2; Soft Step 3 |
| L1 Daiwa Scarlet, B+ | Guts 2 | Mile 1 | Resplendent Red Ace 2 | Osaka Hai 2; Tenno Sho (Autumn) 1; Competitive Spirit ○ 3; Pace Chaser Straightaways ○ 2 |
| L1 Seiun Sky, A | Stamina 3 | Turf 2 | Angling and Scheming 2 | Kikuka Sho 1; Corner Recovery ○ 2; URA Finale 1 |
| L2 Maruzensky, UG/UG1 unresolved, Guest | Speed 2 | Turf 2 | Red Shift/LP1211-M 1 | Takarazuka Kinen 2; Asahi Hai F.S. 2; Corner Adept ○ 2; Ramp Up 2; Mile Straightaways ○ 1; Ignited Spirit: Guts + 1 |
| L2 Daiwa Scarlet, S+ | Speed 2 | Front Runner 2 | Resplendent Red Ace 2 | Osaka Hai 2; Tenno Sho (Spring) 2; NHK Mile C. 2; Japan C. 2; Corner Adept ○ 2; Unyielding Spirit 2; Front Runner Savvy ○ 1 |
| L2 Taiki Shuttle, UG | Power 3 | Pace Chaser 2 | Shooting for Victory! 2 | NHK Mile C. 2; Sprinters S. 1; JBC L. Classic 2; Tokyo Daishoten 2; Corner Adept ○ 2; Corner Acceleration ○ 2; Productive Plan 2; Shifting Gears 1; Pace Chaser Straightaways ○ 3; Disorient 2; Sympathy 2; Head-On 2; Glittering Star 2 |

SR §1.5 differs: Vodka Power 1; L1 Daiwa Guts 1 and green 1; L2 Daiwa green 1. UT prose also says green 1 on one branch and 2 on the other, unlike its own detailed table's 2/2. These conflicts are open, not cleaned up. The two tree shapes are post-career two-column bracketed branches versus in-career vertical rows under `1st Legacy`/`2nd Legacy` tabs. Blue/white reach three in this fixture and pink/green two; this observation alone does not prove a universal game limit.

### 2.4 Separate Perks and Learn fixtures

Perks list (UT §2.3c, SR §1.12 The Perks panel at 415–417 describes shape, not these eleven names): Updrafters (two source portraits), Steadfast, Deep Breaths, Hesitant Pace Chasers, Meticulous Measures, Stop Right There!, Thunderbolt Step, Soft Step, Pace Chaser Corners ○, Shrewd Step, Fighter (one portrait each for the latter ten). Do not mark this entire pool owned or merge it with Details. Groups: `Trainee Skills`, `Card Skills`. Category tiles are described in UT as orange/blue/red/pink in Perks, while SR §1.12 names orange/blue/green like Learn; unresolved without originals.

Learn reference: SR §1.13 The Learn screen, 419–462; UT §2.3d. Price is the displayed discounted price. Hints 1/2/3/4/Max mean 10/20/30/35/40% off. Floor, not round (130×0.65=84.5 becomes 84).

| Skill | Hint | Client price |
| --- | --- | --- |
| Ignited Spirit SPD | 1 | 180 |
| Ignited Spirit STA | 3 | 140 |
| Ignited Spirit PWR | 1 | 180 |
| Ignited Spirit GUTS | 4 | 130 |
| Straight Descent | none shown | 120 |
| Highlander | none shown | 160 |
| Front Runner Corners ○ | 3 | 91 |
| Pace Chaser Straightaways ○ | 4 | 91 |
| Pace Chaser Corners ○ | 4 | 84 |
| Plan X | 1 | 272 |
| Countermeasure | 2 | 128 |
| Soft Step | Max | 96 |
| Subdued Front Runners | 1 | 117 |
| Subdued Pace Chasers | 1 | 130 |
| Hesitant Pace Chasers | 2 | 104 |
| Outer Swell | 1 | 162 |
| Gap Closer | 1 | 144 |
| Productive Plan | 3 | 112 |
| Calm in a Crowd | none shown | 170 |
| Ramp Up | none shown | 170 |
| Straightaway Acceleration | none shown | 170 |
| It's On! | 3 | 289 |
| Unruffled | 4 | 280 |
| Rushing Gale! | 1 | 323 |
| Long Shot ○ | 1 | 81 |
| Firm Conditions ○ | 1 | 81 |
| Sunny Days ○ | 1 | 81 |
| Inner Post Proficiency ○ | 1 | 81 |
| Kyoto Racecourse ○ | 1 | 81 |
| Hakodate Racecourse ○ | 2 | 56 |
| Cut and Drive! | 2 | 160 |
| Resplendent Red Ace | 1 | 180 |

SR §2.1/§2.2, 501/518 calls the 91-point Straightaways upgrade `◎`, unlike §1.13 and UT's `○`; SR §2.2, 521 says Subdued Pace Chasers has no hint, unlike §1.13/UT. Preserve these as open reference conflicts. SR §11 says thirty-three prices; the frozen table enumerates 32. No purchase is implied by a price fixture. Learn `Obtained` set is Steadfast, Deep Breaths, Extra Tank, Shrewd Step, Nimble Navigator, Corner Recovery ○, Corner Adept ○, Swinging Maestro, Blue Rose Closer, Red Shift/LP1211-M. Blue Rose Closer Lv3 has rainbow tint; Swinging Maestro and some other rows olive-gold; meaning unknown. Learn icon tiles: orange running/positioning, blue recovery/endurance, green condition/venue. Effect text includes applicable style/distance in parentheses.

### 2.5 Other reference facts available during the pass

- Client rail: `Jukebox`, `Sparks`, `Log`, `Career Profile`, `Agenda`, `Item Request`, `Menu`; Log empty text `There is no log.` SR §1.14, 464–466.
- Career (SR §1.10, 386–403): `5 turn(s) left` goal timer; `5 Turn(s) Until the Unity Cup` finals timer; `Entry criteria met!` separate goal state. `Senior Year Early Oct`; `Star`; grades beside in-force caps; `Skill Pts 173`. `Rest`, `Training`, `Skills`, `Infirmary`, `Recreation`, `Races` with `Scheduled Race`.
- Client voices (SR §1.7, 254–256 and §1.10, 397–398): Tazuna `Your trainee's energy is getting low, so she's more susceptible to injury. Rest is important too.` Trainee alternatives `Phew... I guess I'm just... a little tired...` and `...I'm okay. I'm not tired... or anything... I'm just fine...`, not merged into one quote.
- Mood panel (SR §1.7, 249–252; UT §2.3f): GREAT increases training results 20%, attributes while running 4%; GOOD +10/+2; NORMAL no change; BAD lowers 10/2; AWFUL lowers 20/4; `Current ->` marker. Full sentences in UT.
- Class ladder (SR §1.15, 474): Legend 320000; Top Star 240000; Star 160000 current; Platinum 100000; Gold 50000; Silver 20000; Bronze 5000; Beginner 1st place; Debut/Maiden base. Fans 209245; Top Star gap 240000−209245=30755.
- Featured Nishino Flower (SR §1.15, 470): SSR; Lv30/30; `0 SP to next level`; unique `Even the Littlest Bud` Lv30, `Mood Effect and Initial Friendship Gauge`; `Unity Cup Exclusive`; grade B. Stats 806/1080 A, 591/860 C+, 680/960 B, 625/880 B, 610/850 B. Aptitudes Turf A/Dirt F, Sprint A/Mile A/Medium D/Long C, Front F/Pace A/Late A/End C. Tabs `Support Effects`, `Skills`, `Career Events`, `Flavor Text`; current Friendship Bonus 15%, Mood Effect 20%, Training Effectiveness 5%; descriptions in UT. Blue hat icon meaning unknown.
- Effects context (SR §1.4, 139–150): Tokai current FB16/Mood45 versus max20/60; Flower current FB15/Mood20/Training5 versus max20/30/10; Biko current FB20/Training10 versus max25/15; Creek current/max FB25/Training15; Matikanetannhauser current FB20/Training5 versus max25/5; Urara current FB16/Training11 versus max20/15. Padlock 35/45 means unlock level, not effect magnitude. Catalog maxima are not browser evidence.
- Roster (SR §1.9, 340–364): six deck members above plus Fine Motion, King Halo, Seeking the Pearl, Shinko Windy, Inari One, Nakayama Festa, Agnes Digital, Eishin Flash, Matikanefukukitaru, Taiki Shuttle, Gold Ship, plus Rice Shower. Seventeen non-trainee names, not seventeen Guest R cards; the source has six deck members and eleven Guest rows. Two identities unknown. Do not fabricate them.
- Team context: tooltip `All Attributes + 30`; source later ties it to league 8th, not Team Rank S (SR §1.8, 294–301; §7, 1068–1080). Facility mechanism A→4/S→5 is publisher-derived (SR §2.6, 754–831), not training-count-derived.
- Countdown reference: requested audit uses Senior Late November turn70 payout (70−67=3), distinct from turn72 finals (72−67=5); SR §2.4, 611–621 preserves Early/Late November publisher dispute. SR §8.7, 1390–1396 says seven half-months between Early Oct and Late Dec; the supplied calendar formula gives five. Do not use its seven to alter turn67.
- Recovery context: 30 under-slept, 50 ordinary, 70 big success. No recovery is to be invented as an observed turn result.

### 2.6 Secondary-reference context frozen before navigation

Targeted read by a research agent, no browser involvement: `UMAMUSUME_REFERENCE.md` §1.1.5 at 183–201 gives rest 50/70/30, while §1.1.6 at 221 still says rest returns 30; preserve the conflict. Mood strings and modifiers are at 203–219. §1.2.6 at 393–397 explains 72 calendar turns, 75 counting finale races, 78 counting six finale turns; client Turn Count is goal-relative. §1.3.4 at 432–457 separates scenario caps and reduced gains/effectiveness beyond 1200. §1.3.5 at 447–468 names Growth Rate and +0% cells, but conflicts internally over a multiplier versus a ceiling. §1.4.1 at 494–510 names seven card types including Pal, not a rental status. §1.4.2 at 516–524 gives SSR breaks 0/1/2/3/4 → level caps 30/35/40/45/50. Any breaks derived from the displayed cap must be logged as arithmetic, not source-transcribed breaks. §1.4.7 at 583–605 gives floored interpolation and preserves an unresolved Unique Perk level/unlock interpretation. §1.5.2 at 670–687 separates Blue/Pink/Green/White/Scenario; §1.5.4 at 707–715 gives two parents/four grandparents and rental/repeat context. §2.2.2 at 1031 onward carries the per-member Spirit gauge and at 1160–1163 the grade-to-facility mapping; its S+ statement concerns team stat grades, not an ancestor's final rank. The prompt's reference to §8.4 for league 8th does not match that reference's current Known gaps section at 3176; the source report's §8.4 supplies that statement instead. No mechanic here establishes an application capability.

### 2.7 Deck comparison and arithmetic entry note

All six SSR (SR §1.4 111–171). Pal type is not Friends/borrowed status.

| Card | Type | Current/cap | Borrowed | Current versus max effects (context) |
| --- | --- | --- | --- | --- |
| Tokai Teio `[Dream Big!]` | Speed | 35/35 | No | FB16/Mood45 vs20/60 |
| Nishino Flower `[Even the Littlest Bud]` | Speed | 30/30 | No | FB15/Mood20/Training5 vs20/30/10 |
| Biko Pegasus `[Double Carrot Punch!]` | Speed | 30/30 | No | FB20/Training10 vs25/15 |
| Super Creek `[Piece of Mind]` | Stamina | 50/50 | Yes, Friends | FB25/Training15 current=max |
| Matikanetannhauser `[Just Keep Going]` | Guts | 30/30 | No | FB20/Training5 vs25/5 |
| Haru Urara `[Urara's Day Off!]` | Guts | 35/35 | No | FB16/Training11 vs20/15 |

Source lists Creek fourth but calls the Friends slot sixth. Preserve borrowed role if the UI positions it sixth. Breaks are not measured or source-transcribed inputs: only if required, derive 1/0/0/4/0/1 from SSR cap 30/35/40/45/50 and log the arithmetic derivation.

## Superseded preliminary attempt

The owner explicitly ordered a fresh rerun after consolidation. PRE-S01/PRE-S02 below preserve the earlier interrupted attempt, not evidence for the new primary walk. Its screen counts, screenshot and observations are excluded from the fresh pass's totals. The source was read end to end before that attempt; its identical SHA-256 was verified again before the rerun, so the unchanged frozen sheet remains the entry fixture.

### Preliminary ledger

| Screen | Route | Arrived from/control | Exit |
| --- | --- | --- | --- |
| PRE-S01 | `/` | Supplied entry URL | `All careers` clicked once |
| PRE-S02 | `/training-runs` | PRE-S01 `All careers` | No exit; paused for requested document merge |

### Preliminary screen descriptions

### PRE-S01 -- Dashboard

#### Route and trigger

Initial navigation to `http://127.0.0.1:8000/`; title `Dashboard`, HTTP200. First observation00:21:46+08:00. [PRE-S01-entry.png](../RS-UC-2026-11/screens/S01-entry.png).

#### What the screen asks for

Navigation, no form. Visual order: collapsed icon rail; blue header; heading/actions; resume/status columns; count tiles; recent veterans/quick actions.

DOM-order controls: `Skip to content`→#main; `Expand navigation` button (collapsed); rail `Dashboard`→/, `New Career`→/career/setup/scenario, `Careers`→/training-runs, `Legacy Lab`→/legacy, `Veterans`→/veterans, `Review`→/review, `Support Cards`→/support-cards, `Skills`→/skills, `Database`→/database, `Settings`→/preferences, `Data sources`→/database/sources; header `Help`→/help; `New career`, `All careers`; resume `Open cockpit`→/training-runs/35/cockpit; status `Source ledger`, `Open the library`, `Open review`; `Browse catalog`, `Skill search`, `Browse cards`; three veteran `Open` links to22/21/20, `All veterans`; quick actions `Import a historical career`→/training-runs/import, `Review queue`, `Open settings`, `Database hub`; closed `Why four destinations` disclosure.

Exact helper: `A historical career arrives as a CSV turn log; a career you are already partway through is recorded at its current position instead.` Opened disclosure once: `Quick actions group the destinations the sidebar lists, with one primary action per card.` No further door. No required fields/defaults/bounds/countdowns.

#### Values entered + source trace

None. One disclosure expansion; no data probe/submission.

#### Source data with no UI home

Entry deferred from this navigation page, not declared unsupported.35/22/21/20 are link IDs, not requested run numbers.

#### Friction / errors / dead ends

New-career and historical doors visible, snapshot not linked here after disclosure. Method-only discovery recorded in §7.9, not a finding ID. No crash or error banner. Existing dark appearance untouched. Icon rail has accessible link names.

#### Outcome / next screen

Clicked visible `All careers` once. The immediate snapshot showed `Loading the page you chose…` and `Page load progress`; the next observation reached PRE-S02 without another click. The owner's new-report correction occurred before leaving Dashboard or entering any run data. It did not cause a browser restart, reload or second pass. Elapsed navigation time was not judged from the intervening document work: browser resource timing later reported 392.1 ms, HTTP 200.

### PRE-S02 -- Careers list

#### Route and trigger

`http://127.0.0.1:8000/training-runs`, reached from PRE-S01 `All careers`. Title `Careers`; heading `Training runs`. DOM metadata captured at 2026-11-17T00:30:38+08:00, after the owner's document-merge instruction. Resource timing identifies an XMLHttpRequest to this route, HTTP 200, 392.1 ms; its HTTP method was not exposed by this timing entry. No career form was submitted.

Screenshot attempt failed with `NATIVE_BROWSER_VIEWPORT_UNAVAILABLE`: viewport825×785, visible=false, attached=true, cdpAttached=true, visibilityState=hidden. No PRE-S02 image is claimed. The accessibility snapshot and read-only DOM inspection succeeded. This is an unavailable visual surface, not evidence of an application crash or blank screen; no retry, reopen, reload or resize was performed.

#### What the screen asks for

Shared navigation as PRE-S01, header `Careers`, `11 active careers`, `Help`; page links `New career` → `/career/setup/scenario` and `Import a run` → `/training-runs/import`. No visible existing-career snapshot link in the captured structure.

Filter form, DOM order:

| Control | Type/options/default | Required/bounds/help |
| --- | --- | --- |
| `Search trainee` | Search input, empty | Optional; placeholder `Rice Shower, Maruzensky…`; no min/max/step |
| `Scenario` | All scenarios (default), URA Finale, Unity Cup, Trackblazer, Our Grand Concert | Optional native select |
| `Status` | All statuses (default), Active, Completed, Retired | Optional native select |
| `Sort` | Newest first (default), Oldest first, Trainee A-Z | Optional native select |
| `Apply filters` | Submit button | Form declares GET `/training-runs`; not submitted |
| `Clear` | Link to `/training-runs` | Not clicked |

Table columns `TRAINEE`, `SCENARIO`, `TURN`, `POSITION`, `STATUS`, `ACTIONS`. Nineteen rows, `19 careers · page 1 of 1`; `Previous`/`Next` rendered as non-link text. Eleven active rows have `Cockpit` links for IDs35,34,31,30,29,28,27,26,25,24,23; eight completed rows have `Result` links for22,21,20,19,18,17,16,15. No row opened. Displayed `#` IDs are not requested user run numbers.

Existing Rice Shower row: `Rice Shower`, `ライスシャワー · [Rosy Dreams]`, `#34`, `· ★3`, `Unity Cup`, `67 / 72`, `Senior Year · Early October`, `Active`, `Cockpit` → `/training-runs/34/cockpit`. This is pre-existing data, not this walk's creation or persistence proof. Admire Vega row35 shows `0 / 72`, `N/A`, `, no turn has been logged on this run yet.`; completed rows show `Finished`. Row25 shows turn73 and `Senior Year · Early January`; it is unrelated existing data and has not been investigated.

#### Values entered + source trace

None. Filters and existing careers left untouched. The Rice Shower row matches some frozen identity/position values but was not produced by this session.

#### Source data with no UI home

This list has search filters, not snapshot input. Source fields remain deferred/unmeasured. No application-wide unsupported-data finding is inferred from the list.

#### Friction / errors / dead ends

PRE-S01 says `Import a historical career`; PRE-S02 says `Import a run` for the same target. The target itself has not been visited, so its accepted input is unknown. No application error/banner observed. Visual layout is unmeasured because the browser surface became hidden; DOM-order evidence is not represented as visual QA.

#### Outcome / next screen

Paused on PRE-S02 while merging the documents as requested. No selected creation door, no newly created run, no turn input and no event countdown measured yet. No subsequent screen is claimed tested.

## 3. Screen ledger

Fresh primary pass ordered by the owner after consolidation. S01 onward below belongs only to this rerun.

| Screen | Route | Arrived from/control | Exit |
| --- | --- | --- | --- |
| S01 | `/` | Fresh supplied-URL navigation | `New career` attempted twice; remained `/`; stop ENV-01 |

## 4. Screen-by-screen walk

### S01 -- Dashboard (fresh pass)

#### Route and trigger

Owner-ordered fresh initial navigation to `/`; first DOM observation2026-11-17T00:48:32+08:00. HTTP200,277.8ms. Browser version unchanged; current viewport736×785,DPR1,visibility hidden. The host viewport differs from preliminary825px without an agent resize. Selecting the existing page with bringToFront did not make its surface visible. Screenshot attempt returned `NATIVE_BROWSER_VIEWPORT_UNAVAILABLE`; no fresh S01 screenshot exists. Structural browser evidence only.

#### What the screen asks for

Heading `Trainer Desk`; `11 active careers, 8 finished runs, and 4 permanent Global scenarios.` Accessible controls in order: `Skip to content`, `Help`, `New career`→`/career/setup/scenario`, `All careers`→`/training-runs`; resume `Open cockpit`→run35; `Source ledger`, `Open the library`, `Open review`; `Browse catalog`, `Skill search`, `Browse cards`; three veteran `Open` links→22/21/20, `All veterans`; `Import a historical career`→`/training-runs/import`, `Review queue`, `Open settings`, `Database hub`, closed `Why four destinations`; mobile `Home`, `Career`, `Legacy`, `Cards`, closed `More`. Desktop rail links are in the DOM but absent from the accessibility snapshot at this width, not counted as visually available.

Helper `A historical career arrives as a CSV turn log; a career you are already partway through is recorded at its current position instead.` Resume card Admire Vega/Unity Cup has `not recorded` for TURN/ENERGY/TEAM RANK and `No turn has been logged for this career yet.` No form/default/required field/numeric bound/countdown.

Other captured content: Global data `Current`, `Verified 2026-11-02`, GameTora `scenarios.json`; Veterans `8 banked`; review queue `304 pending`; tiles for 11 careers, 133 trainees/72 Global, 1910 skills and 559 cards/251 Global. Recent veterans were Vodka22, Vodka21 and Daiwa Scarlet20, each dated 2026-11-14. These are pre-existing dashboard records, not source entries or persistence evidence. The screenshot failed, so their visual spacing, clipping and color were not assessed.

#### Values entered + source trace

None. Source sheet unchanged. No existing run opened.

#### Source data with no UI home

Navigation only; all source entry remains deferred. No unsupported-data finding from this page.

#### Friction / errors / dead ends

New-career and historical-file links observed; no snapshot/current-position link among accessible controls or DOM links. Door choice is method (§7.9), not a finding. No application error; unavailable screenshot is a host-surface limitation, not a blank application.

#### Outcome / next screen

The first applicable accessible `New career` link was selected; the source is not a CSV turn log, so historical-file import was not chosen. The initial native click reported success, but the next snapshot still showed Dashboard. A read-only check at 00:49:57.277+08:00 found the correct href `/career/setup/scenario`, `display:flex`, `pointer-events:auto`, rectangle x493.67/y119.59/width105.625/height44, document visibility `hidden`, and active element `body`. No `/career/` resource request was observed. These properties establish a rendered link, not delivery of the native input.

One permitted retry used the same accessible link. The tool again reported a successful click. A 30,000 ms wait for either `Trackblazer` or `Our Grand Concert` timed out; these were destination-text probes, not text seen on a reached setup screen. Final observation at **00:51:34.445+08:00** still returned `/`, title `Dashboard`, visibility `hidden`, unchanged page text and no `/career/` resource entry. There was no observed destination request, HTTP method, response or redirect to record. No second route is claimed.

The pass stopped at S01 under **ENV-01** after the single retry. No keyboard fallback, scripted click, direct destination navigation, request replay, browser-provider switch or application fix was attempted. The hidden surface and ineffective native activation prevent reliable attribution to the application.

## 5. Improvisation log

**None entered.** Zero form fields were filled, zero forms were submitted and no run number, numeric Energy, ancestor rank, break count or missing roster identity was invented. The prepared turn arithmetic and conditional break derivation in §2 are reference calculations, not submitted values.

| Screen | Field/control | Exact value | Reason | Alternatives rejected |
| --- | --- | --- | --- | --- |
| S01 | No data-entry control reached | None | Navigation stopped before any form | No direct-data substitute or fabricated input |

## 6. Findings register

**Confirmed application findings: F = 0; blocking F = 0; E = 0; U = 0.** This is a coverage-limited result, not proof that the application has no defects or unsupported fields. Only one navigation screen was reached in the fresh pass. Door/turn decisions appear solely in §7.9 and receive no finding IDs.

| Kind | Confirmed IDs | Scope of conclusion |
| --- | --- | --- |
| Functional (F) | None | No form or destination behavior reached; no application fault established |
| Experiential (E) | None | Fresh screenshot unavailable; navigation observations insufficient to assert an experiential defect |
| Unrepresented source data (U) | None | Unvisited input/readback surfaces cannot establish missing capability |

### ENV-01: hidden native surface and ineffective activation

| Field | Evidence |
| --- | --- |
| Classification | Blocking measurement/environment limitation, outside F/E/U |
| Screen | S01, `/`, 736×785 CSS pixels |
| Detection | Native screenshot error, accessibility snapshots, read-only DOM/resource inspection and one bounded interaction retry |
| Expected measurement | Capture the screen and follow the ordinary `New career` link to its offered route |
| Actual | Screenshot returned `NATIVE_BROWSER_VIEWPORT_UNAVAILABLE`; native surface `visible=false`, `attached=true`, `cdpAttached=true`, `visibilityState=hidden`. Two native activations reported success without observed navigation or a `/career/` resource request |
| Bounded check | One retry, then a 30,000 ms text wait timed out; final URL remained `/` at 00:51:34.445+08:00 |
| Impact | No setup, snapshot input, historical import, cockpit or subsequent surface measured; no fresh visual evidence |
| Attribution limit | Hidden screenshot surface is directly reported by the host. Whether the navigation failure arises in native-input delivery or application behavior was not established |
| Recovery/deviation | One `bringToFront:true` selection before the fresh pass did not restore visibility. No further workaround, app change or third attempt |
| Next prerequisite | Restore a visible, interactive in-app browser surface before a separately authorized fresh walk; this report does not claim that doing so will necessarily fix navigation |

## 7. Source-to-UI traceability

**M0 = not entered, not accepted, not persisted, and representation unmeasured because the relevant surface was not reached after S01.** M0 is neither unsupported nor a U finding. Every row below receives that disposition. The full value and source anchor remain in the frozen §2; this matrix adds measurement status without changing those values.

### 7.1 Primary entry sheet

All 59 datum rows of §2.2 are accounted for below: **0/59 entered; 0/59 acceptance-tested; 0/59 persistence-verified**. This denominator counts the primary sheet's rows, not individual nested skill/spark attributes or all application capabilities.

| §2.2 datum | Disposition |
| --- | --- |
| Trainee | M0 |
| Card/epithet | M0 |
| Rarity | M0 |
| Potential/awakening | M0 |
| Scenario | M0 |
| Full scenario label | M0 |
| Snapshot | M0 |
| Turn | M0 |
| Turns remaining | M0 |
| Lifecycle | M0 |
| Run number | M0; remains unknown in source |
| Speed | M0 |
| Stamina | M0 |
| Power | M0 |
| Guts | M0 |
| Wit | M0 |
| Speed cap | M0 |
| Stamina cap | M0 |
| Power cap | M0 |
| Guts cap | M0 |
| Wit cap | M0 |
| Scenario floor | M0 |
| Growth Rate | M0 |
| SP | M0 |
| Fans | M0 |
| Mood | M0 |
| Energy | M0; numeric value remains unknown in source |
| Deck card 1 | M0 |
| Deck card 2 | M0 |
| Deck card 3 | M0 |
| Deck card 4 | M0 |
| Deck card 5 | M0 |
| Deck card 6 | M0 |
| Owned Details list | M0 for each of its ten named skills and the unique's level |
| Additional owned skill | M0 for Corner Recovery ○ |
| Legacy 1 | M0 |
| Legacy 2 | M0; UG/UG1 unresolved |
| L1 grandparent A | M0 |
| L1 grandparent B | M0 |
| L2 grandparent A | M0; S+/older UG conflict retained |
| L2 grandparent B | M0 |
| Blue sparks | M0 for every per-instance star value and its conflicting alternative |
| Team/name/motto | M0 |
| Overall Team Rank | M0 |
| League | M0 |
| Unity Trainings | M0 |
| Spirit Bursts | M0 |
| Extreme Spirit Bursts | M0 |
| Members | M0; two identities remain unknown |
| Team stat grades | M0 |
| Facility levels | M0 |
| Preseason results | M0 for each of the four round/date/WIN entries |
| Race scheduled now | M0 for Kyoto Daishoten and every listed race attribute/fan criterion |
| Pending race | M0; no finals result fabricated |
| Active goal | M0 for Arima Kinen, placing/date/turn, eligibility and timer |
| Cleared goals | M0 for each named goal, placing/date and fan criterion |
| Aptitudes, current frame reading | M0 for all ten current grades |
| Aptitudes, old supplied sheet | M0; retained as conflicting reference, not entered |
| Aptitudes, catalogue claim | M0; catalogue picker not visited |

### 7.2 Expanded fixtures and display-only facts

Rows referencing a frozen table apply to **every named cell and sub-item of that table**, not a sample. They account for finer-grained fixtures that the primary sheet groups into one row. None was tested by opening a pre-existing career.

| Frozen source group | Exact scope receiving M0 |
| --- | --- |
| §2.1 stat grades | B/C+/C+/B+/D+ beside 607/577/549/736/397; no rendered run stat line measured |
| §2.1 fans/class | 209245, Star, next Top Star at 240000 and gap 30755 |
| §2.3 L1 Vodka | Identity/rank; Power 2, Late Surger 2, Cut and Drive! 2; all six white rows and their stars |
| §2.3 L1 Daiwa Scarlet | Distinct B+ instance; Guts 2, Mile 1, Resplendent Red Ace 2; all four white rows and their stars |
| §2.3 L1 Seiun Sky | Identity/rank; Stamina 3, Turf 2, Angling and Scheming 2; all three white rows and their stars |
| §2.3 L2 Maruzensky | Identity, unresolved UG/UG1 and Guest status; Speed 2, Turf 2, Red Shift/LP1211-M 1; all six white rows and their stars |
| §2.3 L2 Daiwa Scarlet | Separate S+ instance; Speed 2, Front Runner 2, Resplendent Red Ace 2; all seven white rows and their stars |
| §2.3 L2 Taiki Shuttle | Identity/rank; Power 3, Pace Chaser 2, Shooting for Victory! 2; all thirteen white rows and their stars |
| §2.3 distinctions | No Wit spark; blue/pink/green/white kinds; white race/scenario/skill identity; held versus transferred values; conflicting post-career/prose stars; two-column versus vertical tree shape. 0/6 ancestry instances tested |
| §2.4 Perks | Each of the eleven named perks; Updrafters' two grant portraits and each other perk's one portrait; Trainee Skills/Card Skills separation; circle variants and unresolved category colors. 0/11 named rows tested |
| §2.4 Learn price table | Every skill/hint/price triple in the 32-row table, from Ignited Spirit SPD through Resplendent Red Ace, including both venue rows, both ancestor uniques and all discounted/undiscounted rows. 0/32 triples tested |
| §2.4 Learn semantics | 1/2/3/4/Max discount ladder, floor arithmetic, 84-point Pace Chaser Corners ○, 96-point Soft Step, 32-versus-33 count conflict, ○/◎ and Subdued Pace Chasers hint conflicts |
| §2.4 acquired state | All ten named Obtained entries, their relationship to the eleven owned skills, Blue Rose Closer Lv3, replaced price/control state, rainbow/olive-gold tint and unknown tint meaning |
| §2.4 effect/category display | Parenthesized style/distance, running/positioning versus recovery/endurance versus condition/venue groups and repeated grant origins |
| §2.5 client rail/log | Jukebox, Sparks, Log, Career Profile, Agenda, Item Request, Menu; exact empty text `There is no log.`; no run-rail counterpart reached |
| §2.5 Career surface | Both exact five-turn strings, eligibility, Senior Year Early Oct, Star, Skill Pts 173, stat grades/caps, and Rest/Training/Skills/Infirmary/Recreation/Races/Scheduled Race controls |
| §2.5 voices | Tazuna's low-Energy advisory and both separately recorded trainee-dialogue alternatives; distinct UI homes unmeasured |
| §2.5 mood panel | GREAT/GOOD/NORMAL/BAD/AWFUL, all training/running modifiers, wording and `Current ->` marker |
| §2.5 fan ladder | All nine named tiers, every recorded threshold, current Star marker, fan total and next-tier difference |
| §2.5 featured Flower | Identity, SSR, 30/30, 0 SP, unique-perk title/unlock/text, Unity Cup Exclusive badge, grade B, all five stat/cap/grade triples and ten aptitudes |
| §2.5 Flower detail tabs/effects | Support Effects, Skills, Career Events, Flavor Text; FB15/Mood20/Training5, descriptions and unresolved blue hat icon |
| §2.5 roster | Each of the six deck members, eleven additional named non-trainee members, Rice Shower, 20/20 total and two unknown identities |
| §2.5 team context | All Attributes + 30 tooltip/league interpretation and A→4/S→5 facility mapping, separate from overall rank/training count |
| §2.5 event arithmetic | Turn70 payout/three turns versus turn72 finals/five turns; Early/Late November conflict and erroneous source seven-half-month count |
| §2.5 rest | 30 under-slept, 50 ordinary, 70 big success; no actual rest outcome entered |
| §2.6 mechanics context | Each frozen reference distinction: rest conflict, mood, 72/75/78 counts, scenario ceilings/effectiveness, Growth Rate conflict, Pal/card types, SSR cap/break mapping, effect interpolation/unlock ambiguity, spark categories/ancestry, Spirit and facility mapping. These are comparison hypotheses, not observed product behavior |
| §2.7 six-card comparison | Every identity/type/current-cap/borrowed/current-versus-max effect cell for all six cards; 0/6 cards selected or inspected |
| §2.7 conditional arithmetic | Breaks 1/0/0/4/0/1 and Creek's fourth-listed/sixth-Friends position conflict; neither mapping applied to a form |

### 7.3 Event counters and resulting state

| Event | Frozen expectation | Exact fresh application string |
| --- | --- | --- |
| Active Arima Kinen goal | Turn72 minus turn67 = five; client `5 turn(s) left` | Not observed; no target-run screen reached |
| Unity Cup finals | Turn72 minus turn67 = five; client `5 Turn(s) Until the Unity Cup` | Not observed; no target-run screen reached |
| November payout | Requested turn70 minus turn67 = three; source date dispute retained | Not observed; no target-run screen reached |
| Goal eligibility | Client `Entry criteria met!` | Not observed; no target-run screen reached |

These strings are reference fixtures only. S01's `11 active careers`, `8 finished runs` and catalogue totals are counts, not event countdowns. Its unrelated resume card's `not recorded` labels are not missing values on the requested Rice Shower run.

## 7.8 Definition-of-done checklist

- [x] Every reached fresh screen (S01 only) has its own ordered section and six required subheadings; superseded screens are separate.
- [x] Entered-value accounting is complete: zero entered, so no untraced or improvised submitted value.
- [x] Every frozen source group has a disposition; unvisited remains distinct from unsupported.
- [x] No career was completed, reclassified as Veteran/Legacy or supplied a fabricated run number.
- [ ] A newly created run's mid-career lifecycle was verified. Blocked before creation, not demonstrated by non-action.
- [x] Attempted door and intended turn index are recorded in §7.9, with consequences for measurement.
- [ ] Rendered target-run position was checked against turn67. No target-run screen reached.
- [x] Event-counter inventory explicitly records zero observed target-run countdowns and distinguishes reference expectations.
- [x] F/E/U are separate from ENV-01; the register and expanded assessment agree.
- [x] Stopping point, retry and timing are stated.
- [x] All 64 probes have an answer or explicit unanswered disposition in Appendix A.
- [x] Primary-sheet traceability covers 59/59 rows; nested/reference fixtures are separately covered in §7.2.
- [x] Contamination and superseded-attempt boundaries are stated.
- [x] Mutation and screenshot availability are stated, without a database-byte-unchanged claim.
- [ ] End-to-end creation, cockpit and subsequent-flow completion were demonstrated. Blocked at S01 by ENV-01.
- [ ] Fresh visual QA was completed. Screenshot capture unavailable; structural evidence is not visual QA.

Checked reporting requirements do not turn the unchecked behavioral requirements into passes. This is a completed **partial-attempt report**, not a successful end-to-end walk.

## 7.9 Method decisions and consequences

| Decision | Fresh observation or choice | What it permits or leaves unmeasured |
| --- | --- | --- |
| Entry/forward-only rule | Owner explicitly ordered a new initial navigation after consolidation. S01 is the new pass; PRE-S01/PRE-S02 remain separate | No Back/reload/second tab during the fresh attempt; previous observations do not increase fresh coverage |
| New-career door | S01 accessible `New career` targets `/career/setup/scenario`; selected as the first applicable forward control | Two native activations did not advance; offered setup fields and default position unknown |
| Existing-career/current-position door | No explicit snapshot link in captured S01 accessible controls or DOM links; helper distinguishes a partway-through career. `Why four destinations` and mobile `More` stayed closed | This is entry-page discovery only, not an application-wide absence claim. Snapshot capability and any route through a closed menu remain unmeasured |
| Historical-file door | `Import a historical career` targets `/training-runs/import`; helper says CSV turn log | Not selected: a current snapshot is not the supplied historical CSV. Actual import fields, lifecycle support and acceptance not tested |
| Requested position | `(3−1)×24+(10−1)×2+1=67`; `72−67=5` | Intended turn67 never entered. Turn1 was not accepted, and no default or resulting position was observed |
| Run identity | No source run number; no existing career opened or modified | No generated run ID, no new persistence evidence and no forced completed/Veteran/Legacy state |
| Bounded probe | S01 has no input form, so no input probe; one native-link retry followed by a 30-second wait | Does not consume or prove a validation probe on an unreached screen; no orphan run created |
| Visibility and stop | Hidden native surface; screenshot unavailable; unchanged Dashboard after retry | Structural S01 observations only. No setup, cockpit, scenario, skill, race, profile or subsequent navigation measurement |

Missing snapshot discovery alone did not stop the walk: the alternative new-career door was attempted. The observed interaction limit stopped it. The intended source position remains 67; source conflicts were preserved rather than altered to fit hypothetical fields. No method choice receives an F/E/U ID.

## 8. Closing assessment

### 8.1 What worked

The fresh Dashboard document returned HTTP 200 in 277.8 ms. Its accessibility tree and read-only DOM could be inspected, and named creation/import destinations could be recorded. The helper distinguishes a historical CSV log from recording a career at its current position. No application error banner or console error/warning was captured in the inspected messages. These facts establish a readable entry page, not a functioning end-to-end creation flow.

The frozen source, complete ancestry/skill fixtures and six-card comparison survived consolidation. Source/reference byte fingerprints stayed unchanged. No source values were reinterpreted to match a field.

### 8.2 Functional findings

**No confirmed F findings, including no confirmed blocking F.** The sole blocking observation is ENV-01 (§6): a hidden native surface plus two ineffective reported activations. The correct link href and readable DOM do not prove native input delivery, while the absence of a route request does not identify an application root cause. Calling the `New career` route broken would exceed the evidence.

No form validation, required run number, turn-index acceptance, save operation, persistence, scenario arithmetic or downstream navigation was tested. Prior-audit claims were neither reproduced nor disproved.

### 8.3 Experiential findings

**No confirmed E findings.** Fresh visual QA was unavailable, and the limited navigation sample does not establish a product-level usability defect. S01 exposes new-career and historical-import labels plus a partway-through helper; the missing explicit snapshot link in that captured structure is a method constraint in §7.9, not an experiential finding by itself.

There is no fresh evidence on picker search, scrolling, form density, keyboard usability, focus transitions, contrast, mobile overflow or cross-screen consistency. The one saved preliminary screenshot cannot substitute for the fresh 736px pass.

### 8.4 Unrepresented source data

**No confirmed U findings.** All 59 primary rows and the expanded source fixtures are unmeasured under M0, not classified as unsupported. This includes the current moment/lifecycle, stats/caps/aptitudes, six supports, eleven owned skills, separate Perks/Learn pools, six ancestry instances with distinct star sets, team/roster state, races/goals, profile, mood/fan ladder and all event counters.

No acceptance or persistence result exists for any entered value because nothing was entered. Unknown run number, numeric Energy, two roster identities and disputed source values remain unresolved; there is no fabricated final state.

### 8.5 Walk completion and mutation boundary

**Partial; one fresh screen, S01 Dashboard.** The attempt ended at 00:51:34.445+08:00 after the initial `New career` activation, one retry and a timed-out 30,000 ms wait. The final URL was `http://127.0.0.1:8000/`. No creation door was entered, no cockpit reached, and no subsequent flow completed. End-to-end acceptance remains unverified.

Fresh evidence: one route, zero screenshots, zero form fields, zero submissions, zero newly created runs, zero persistence checks and zero target-run countdowns. Superseded evidence: PRE-S01/PRE-S02, two routes and one saved preliminary image at `RS-UC-2026-11/screens/S01-entry.png`; excluded from those fresh totals. The requested PRE-S02 and fresh S01 screenshots were not created.

The only environment differences/actions were the host's viewport/visibility state and one pre-pass page-selection request with `bringToFront:true`. No server startup/restart, build, cache clear, browser installation, config/environment change, data reset, resize, zoom or throttling change was performed. The explicit rerun was a new primary attempt, not an unreported stability pass; no Appendix B or second stability pass exists.

No application code, routes, views, schemas, labels, tests, configuration, environment file, source document or historical audit was edited. No seeders, factories, SQL, direct database mutation, state-mutating artisan/tinker/REPL call, API substitute, DOM edit or request replay was used. No form input or run mutation was performed through the UI. Ordinary GET/session/logger side effects were not independently measured, so this is **not** a claim that all database bytes remained unchanged.

The authorized document consolidation removed the root-level session walk-7 draft after preservation checks, staging that deletion; walk9 is the new report. Historical walk7 under `ux-walkthroughs/` and the owner's relocation files were not touched. No commit or push was made. A visible, interactive browser surface and a separately authorized fresh attempt are required for the unmeasured flow; no automatic retry is promised.

### 8.6 Artifact verification

Verification concerns this partial report, not the uncompleted application flow.

| Instrument | Observed result |
| --- | --- |
| Read-only Python section-digest check | Frozen §2 unchanged through finalization: SHA-256 `a8c8cde9c26c82828a65ef113987a98e744537fa2774e64f45872c7de895fc66` |
| Python ordered-table assertions | 59/59 primary datum labels match the frozen sheet; six ancestry rows, 32 Learn rows and six deck-comparison rows preserved |
| Python structure assertions | 64 unique ordered probe rows; 63 unanswered/one partial; one fresh ledger screen and matching section with six headings; one H1; no unfinished placeholders |
| Python path checks | All five local Markdown links resolve; root session walk7 absent, historical walk7 present; only the preliminary `S01-entry.png` exists in the screenshot directory |
| `python tools/md_fence_check.py docs/audits/rice-shower-unity-cup-ux-walk-9.md` | `0 invalid closing fence(s) in 1 file(s)` |
| Source/reference byte-only SHA-256 checks | Both unchanged; sizes match preflight; line-count instrument discrepancy explained in §1 |
| `git rev-parse HEAD`; scoped `git diff --cached --name-status` | HEAD remains `3cbbd8b9c480d9175221471e87ec517f95f8a5f8`; only root walk7 is staged for deletion among the three scoped walk paths |
| Independent read-only report review | No issues found within review scope; checked internal consistency against supplied fresh-pass evidence, not source accuracy or historical preservation |

No application test suite, build, lint, migration or repair was run: none would establish the missing UI interaction evidence, and no application change was authorized. The historical-file existence check is not a content-equivalence check; this session did not edit it. Earlier exact merge checks established frozen-sheet/coverage preservation before deleting the redundant session draft.

## Appendix A -- Probe answers

All **64/64** requested probes are dispositioned below. Only P-03 has a partial door-discovery answer; **0/64 are fully resolved** by this fresh pass. Every other answer remains explicitly unanswered. S01 identifies the stopping boundary, not evidence that the unvisited capability is missing.

| Probe | Question focus | Fresh-pass answer and evidence |
| --- | --- | --- |
| P-01 | In-progress snapshot versus forced completed creation | **Unanswered.** S01 stopped before any lifecycle or creation form. No career was marked completed. |
| P-02 | Required run number, unknown or skip | **Unanswered.** S01 exposed no run-number field; none invented or entered. |
| P-03 | Available doors, chosen door and position alternatives | **Partial, S01.** New career and historical-file links observed; no explicit snapshot link in captured controls. New career attempted; neither destination nor position alternatives reached (§7.9). |
| P-04 | Turn67 versus default, resulting position/fans/SP/goals/races | **Unanswered.** S01 provided no position input or target-run result. Intended67 was not submitted; no default inferred. |
| P-05 | Caps, sparks and reconciliation | **Unanswered.** No stat or inheritance surface after S01; source cap/star conflicts retained. |
| P-06 | Scenario floor versus in-force cap labels | **Unanswered.** No target-run cap display after S01. |
| P-07 | Growth Rate display | **Unanswered.** No trainee details after S01. |
| P-08 | No Wit spark represented as absence | **Unanswered.** No ancestry input/display after S01; absence was not converted to a zero or invented spark. |
| P-09 | Repeated card with distinct spark sets | **Unanswered.** No ancestor selection after S01; both Daiwa instances remain separate fixtures. |
| P-10 | Guest ancestor | **Unanswered.** No ancestor role/Guest control after S01. |
| P-11 | Borrowed Super Creek50/50 | **Unanswered.** No deck selection or borrowed-role readback after S01. |
| P-12 | Current-level versus maximum effects | **Unanswered.** No support profile after S01; the frozen six-card comparison is not application evidence. |
| P-13 | Level versus breaks | **Unanswered.** No level/break control after S01; conditional arithmetic never entered. |
| P-14 | Pal type versus Friends/borrowed Stamina | **Unanswered.** No support type/role fields after S01; no relabeling performed. |
| P-15 | Eleven individual owned skills versus count/scroll | **Unanswered.** No target-run skill list after S01; neither rows nor scroll behavior tested. |
| P-16 | Unique level3 versus hint level | **Unanswered.** No unique/hint field after S01. |
| P-17 | Acquired versus planned skills | **Unanswered.** No skill-state input or display after S01. |
| P-18 | Individual won/placed/pending races | **Unanswered.** No target-run race list after S01. |
| P-19 | Pending finals without fabricated outcome | **Unanswered.** No finals field after S01; no outcome entered. Non-entry does not prove support for pending state. |
| P-20 | Cleared/active goals and current era | **Unanswered.** No target-run goals after S01. |
| P-21 | Five GREAT/GOOD/NORMAL/BAD/AWFUL states | **Unanswered.** No mood input/panel after S01. |
| P-22 | Numeric/band/absent Energy and improvisation | **Unanswered.** No target-run Energy field after S01. Resume run35's `not recorded` is unrelated; no numeric Energy invented. |
| P-23 | Five turns remaining | **Unanswered.** No target-run position/countdown after S01. |
| P-24 | Exact November-payout versus December-finals timers | **Unanswered.** No event countdown observed on S01; three/five are reference arithmetic only (§7.3). |
| P-25 | Ordinary rest50 versus failed30 | **Unanswered.** No rest action/result after S01; no recovery outcome manufactured. |
| P-26 | Unspent173 SP | **Unanswered.** No SP input or target-run balance after S01. |
| P-27 | Team rank/league/trainings/bursts/extreme/members | **Unanswered.** No target-run team panel after S01. |
| P-28 | Eighteen known identities with20/20 total | **Unanswered.** No roster input after S01; two unknown identities not invented. |
| P-29 | Picker MileB/FrontA/LateB versus catalogueC/B/C | **Unanswered.** Trainee picker not reached after S01; neither claimed set observed there. |
| P-30 | Potential versus awakening distinction | **Unanswered.** No trainee metadata field after S01. |
| P-31 | A/A/A/S/A grades versus overallS/S+ | **Unanswered.** No team-stat/rank display after S01; no terminology inference from source alone. |
| P-32 | GradeA→facility4 andS→5 | **Unanswered.** No facility display or calculation after S01. |
| P-33 | Calendar72 versus finale-included78 | **Unanswered.** No target-run turn calendar after S01. |
| P-34 | Full Unity Cup scenario name | **Unanswered.** S01's unrelated resume uses `Unity Cup`; target scenario selector/full label not reached. |
| P-35 | Both Peak Blue Daiwa instances | **Unanswered.** No ancestor tree after S01; no deduplication or acceptance measured. |
| P-36 | Four spark colors and white subtypes | **Unanswered.** No spark display after S01; fresh screenshot unavailable. |
| P-37 | Blue/white3 and pink/green2 fixture stars | **Unanswered.** No star picker/readback after S01; no universal limit inferred from fixtures. |
| P-38 | Ancestor ranks and Guest marking | **Unanswered.** No ancestor metadata after S01; Maruzensky badge still unresolved. |
| P-39 | Skill-grant cards and two Updrafters grants | **Unanswered.** No Perks/grant-origin surface after S01. |
| P-40 | Circle variants and categories | **Unanswered.** No skill rows after S01; source ○/◎ conflict retained. |
| P-41 | Discount prices, hint ladder and floor arithmetic | **Unanswered.** No Learn/purchase surface after S01; 0/32 price fixtures tested. |
| P-42 | Obtained replacing price/controls | **Unanswered.** No obtained-skill row after S01. |
| P-43 | Trainee/card origins and repeated grants | **Unanswered.** No skill-origin grouping after S01. |
| P-44 | Sparks/Agenda/Log equivalents and empty wording | **Unanswered.** S01 global/mobile navigation is not a run rail. No counterpart or empty-log view reached. |
| P-45 | Effect parentheses, style/distance, same-row circle | **Unanswered.** No skill effect description after S01. |
| P-46 | Ancestor uniques in learning pool | **Unanswered.** No inherited learning pool after S01. |
| P-47 | Running/recovery/condition/venue grouping | **Unanswered.** No learning/category surface after S01. |
| P-48 | Venue versus condition categories/different prices | **Unanswered.** Kyoto/Hakodate and condition rows not reached after S01. |
| P-49 | Rainbow/gold row tints | **Unanswered.** No skill surface after S01; no fresh visual evidence. |
| P-50 | AncestorS+ acceptance | **Unanswered.** No rank field after S01; earlier refusal claims neither reproduced nor disproved. |
| P-51 | Held versus transferred sparks | **Unanswered.** No corresponding inputs/displays after S01; source sets not reconciled. |
| P-52 | Goal/finals timers and eligibility as distinct states | **Unanswered.** None of the three target-run strings observed after S01; see §7.3. |
| P-53 | Two-column versus vertical ancestry tree | **Unanswered.** No tree view after S01, and fresh screenshot unavailable. |
| P-54 | Advisor hint versus trainee dialogue homes | **Unanswered.** No target-run dialogue/advisory surface after S01. |
| P-55 | Per-round held/date/result/next flag/timer | **Unanswered.** No preseason-round detail after S01. |
| P-56 | Unique perk, scenario badge, letters and caps | **Unanswered.** No featured-card profile after S01. |
| P-57 | Next-level SP including0 | **Unanswered.** No support-level profile after S01. |
| P-58 | Support Effects/Skills/Career Events/Flavor Text tabs | **Unanswered.** No support-detail tabs after S01; prior missing-tab claims not retested. |
| P-59 | Exact mood sentences and current marker | **Unanswered.** No mood dialog after S01; reference wording not observed application copy. |
| P-60 | Nine-tier fan ladder/thresholds/current marker | **Unanswered.** No class dialog after S01; no removal or presence finding established. |
| P-61 | Goal fan criteria/CLEAR!/in-progress/turns left | **Unanswered.** No target-run goal rows after S01. |
| P-62 | Dirt/EndC current aptitudes versus catalogue | **Unanswered.** No current-aptitude editor or picker after S01; conflicts preserved. |
| P-63 | Skill tint consistency across screens | **Unanswered.** Zero skill screens reached after S01; no cross-screen visual comparison possible. |
| P-64 | Unified Career Profile destination | **Unanswered.** No target-run navigation after S01; global links do not establish profile organization. |




## 3b. Screen ledger — completed fresh pass (2026-11-17)

Owner-ordered rerun executed in the Hermes session's own CDP-backed Chromium (`browser_exec`, named sessions `rs-uc-2026-11-rerun` then `rs-uc-rerun2` after a harness daemon crash). Interaction delivery was verified before the pass: one pre-pass probe click on `/training-runs` changed URL and title (superseded preparation, PRE-S03, excluded from totals). The prior fresh attempt's S01 (hidden embedded surface, ENV-01) is retained above as superseded history; the ledger below is the completed pass.

Walk window: 2026-11-17T01:40:55+08:00 – 03:17:27+08:00 (Asia/Kuala_Lumpur). Run **#36** created through the UI at 01:44:08+08:00 (`Record snapshot` is irreversible and was submitted knowingly; the created run row is reported, not claimed absent).

| Screen | Route | Arrived from/control | Exit |
| --- | --- | --- | --- |
| S01 | `/` | Fresh supplied-URL navigation | Read-only; door chosen in §7.9b |
| S02 | `/career/snapshot` | Direct URL (no dashboard link observed — §7.9b, E-02) | `Enter the snapshot` |
| S03 | `/career/snapshot/setup` | S02 button | `Review snapshot` |
| S04 | `/career/snapshot/review` | S03 `Review snapshot` | `Record snapshot` (irreversible; run #36 created) |
| S05 | `/training-runs/36/cockpit` | S04 `Record snapshot` redirect | nav `Training` |
| S06 | `/training-runs/36/training` | S05 nav | nav `Races` (after ENV-DEV-01) |
| S07 | `/training-runs/36/races` | S06 nav | nav `Skills` |
| S08 | `/skills` | S07 nav label `Skills` — **wrong door, global library** (E-01) | Cockpit re-nav by href |
| S09 | `/training-runs/36/skills` | Cockpit nav by exact href (after ENV-DEV-02/03) | nav `Events` |
| S10 | `/training-runs/36/events` | S09 nav (deep-link after ENV-DEV-03) | — |
| S11 | `/training-runs/36/inheritance` | Deep-link | — |
| S12 | `/training-runs/36/deck` | Deep-link | — |
| S13 | `/training-runs/36/timeline` | Deep-link | — |
| S14 | `/training-runs/36/settings` | Deep-link | fields filled, `Save identity`, reload-verified |
| S15 | `/training-runs/36/team` | Deep-link | — |
| S16 | `/training-runs/36/scenario` | Deep-link | — |
| S17 | `/training-runs/36/team` | S15 form filled + saved + reload-verified | — |
| S18 | `/training-runs/36/deck` | Six cards equipped, ownership flags set, `Save and exit`, reload-verified | — |
| S19 | `/training-runs/36/result` | Deep-link | Walk end: mid-career stop state |

Environment deviations during the pass, each logged at encounter time in the session ledger and counted here: **ENV-DEV-01…04** — four tab resets to `about:blank` mid-pass (browser-harness daemon instability); each resumed by re-navigation/deep-link, no state lost. **ENV-DEV-05** — daemon crash (`PermissionError [WinError 5]` on port file) → session switched `rs-uc-2026-11-rerun` → `rs-uc-rerun2`; logged as environment mutation. **ENV-DEV-06/07** — two further resets; one-call redos. No reload was used to re-enter a completed step or to second-guess an entry; every resume re-observed the current screen before continuing. No Back-button returns, no second tab, no state manipulation.

## 4b. Screen-by-screen walk — completed pass

### S01 — Dashboard (completed pass)

#### Route and trigger

`http://127.0.0.1:8000/`, title `Dashboard`, first observation 2026-11-17T01:40:55+08:00. Screenshot `RS-UC-2026-11/screens/S01-dashboard.png`.

#### What the screen asks for

Navigation only. Visible controls: `Skip to content`, `Collapse navigation`, rail (`Dashboard`, `New Career`, `Careers`, `Legacy Lab`, `Veterans`, `Review`, `Support Cards`, `Skills`, `Database`, `Settings`, `Data sources`), `?Help`, header `New career` → `/career/setup/scenario`, `All careers` → `/training-runs`, resume card (`Open cockpit` → run 35), `Source ledger`, `Open the library`, `Open review`, `Browse catalog`, `Skill search`, `Browse cards`, three veteran `Open` links (runs 22/21/20), `All veterans`, `Import a historical career` → `/training-runs/import`, `Review queue`, `Open settings`, `Database hub`, mobile bar (`Home`, `Career`, `Legacy`, `Cards`, `More`). No form fields.

#### Values entered + source trace

None. Read-only.

#### Source data with no UI home

None on this screen; entry deferred. No snapshot-door link present among the controls (see E-02).

#### Friction / errors / dead ends

No error banners. Pre-existing state observed read-only: `11 active careers`, `8 finished runs`, resume card Admire Vega `not recorded` ×3, review queue `304 pending matches`, catalogue counts. None opened; none attributed to this walk.

#### Outcome / next screen

Door chosen in §7.9b (snapshot, by URL) → S02.

### S02 — Snapshot entry

#### Route and trigger

`http://127.0.0.1:8000/career/snapshot`, title `Start from existing snapshot`, 01:41:16+08:00. Arrived by direct URL; no dashboard or careers-list link to this route was observed on S01 or in the PRE-S02 capture. Screenshot `S02-snapshot-entry.png`.

#### What the screen asks for

Stepper `Entry | Setup | Review`; statement `Enter at least one record to continue.`; copy: `Record a career that is already underway, exactly as far along as your client shows it. Nothing pretends it is turn 1: the position you enter becomes the run's own position, and every surface that counts from there counts from that point.` and `Enter what you can read and leave what you cannot. A figure you have not read is recorded as absent rather than guessed at, so the tool never states a number you did not give it.` Controls: `Enter the snapshot`, `Back to the dashboard`.

#### Values entered + source trace

None (entry page).

#### Source data with no UI home

None.

#### Friction / errors / dead ends

Route reachable only by URL in observed captures — no in-app link (E-02). Door promise matches the source's mid-career position need exactly (P-01/P-03/P-04 supporting evidence).

#### Outcome / next screen

`Enter the snapshot` → S03.

### S03 — Snapshot setup

#### Route and trigger

`http://127.0.0.1:8000/career/snapshot/setup`, title `Record an existing career`, 01:41:59+08:00. Screenshot `S03-snapshot-setup.png` (empty) and `S03-snapshot-setup-filled.png`.

#### What the screen asks for

Header copy: `Enter what your client shows right now. The position becomes the run's own position, and a field you leave empty is recorded as not provided rather than guessed at. You will read the snapshot back before it is written.` Fields (DOM order): `Scenario *` (Choose a scenario/URA Finale/Unity Cup/Trackblazer/Our Grand Concert); `Trainee *` (searchable list of ~133 names); `Year *` (Junior/Classic/Senior Year); `Month *` (1–12); `Half *` (Early/Late); numeric `Speed`, `Stamina`, `Power`, `Guts`, `Wit`, `Energy`, `Fans`, `Skill Points`, each with an `I don't know this value` checkbox; textarea `Anything worth remembering about this career`; button `Review snapshot`.

#### Values entered + source trace

- `Scenario = Unity Cup` → frozen sheet §2.2 (SR §1.1, 49). Direct.
- `Trainee = Rice Shower` → §2.2 (SR §1.1, 47–52). Direct.
- `Year = Senior Year`, `Month = 10`, `Half = Early` → §2.2 (SR §1.1, 50–51; UT §2.5 derivation). Direct.
- `Speed 607 / Stamina 577 / Power 549 / Guts 736 / Wit 397` → §2.2 (SR §1.2, 58–62). Direct.
- `Energy`: `0` typed first, then its `I don't know this value` checkbox ticked (census `5:1`) — value superseded by Unknown; source prints no number (SR §1.7, 254–260). Derived/Unknown; improvisation §5b row 1.
- `Fans` and `Skill Points`: **not entered although the frozen sheet carries 209,245 and 173** — walk deviation §5b row 2; acceptance of those two values therefore untested.
- Growth Rate, caps, deck, skills, ancestry, team: no fields on this screen (§7.7b dispositions).

#### Source data with no UI home

On this screen: card/epithet, rarity, Potential Lvl 2, full scenario label, run number (none in source — none asked), caps in force, Growth Rate, deck composition, borrowed flag, owned skills, ancestors/sparks, team state, roster, race history, objectives. Dispositions in §7.7b; several have later homes (S14–S18).

#### Friction / errors / dead ends

Energy accepts a bare `0` before the unknown-checkbox is used; the checkbox is the correct instrument and was used. No validation errors on submit.

#### Outcome / next screen

`Review snapshot` → S04.

### S04 — Snapshot review

#### Route and trigger

`http://127.0.0.1:8000/career/snapshot/review`, title `Review snapshot`, 01:43:38+08:00. Screenshot `S04-snapshot-review.png`.

#### What the screen asks for

Statement: `This is exactly what will be written. Read it back: a field marked Unknown is one the client did not show, and a field marked Not provided is one you left alone. They are different statements, and the run keeps them apart.` Read-back list: `Position: Senior Year · Early October`; `Known · Trainee Rice Shower`; `Known · Scenario Unity Cup`; `Known · Speed 607`; `Known · Stamina 577`; `Known · Power 549`; `Known · Guts 736`; `Known · Wit 397`; `Unknown · Energy`; `Not provided · Fans`; `Not provided · Skill Points`. Acknowledgement checkbox `I understand this snapshot cannot be undone.`; buttons `Record snapshot`, `Edit the snapshot`.

#### Values entered + source trace

Acknowledgement ticked. `Record snapshot` clicked knowingly: irreversible run-row creation, authorized by the brief (§3.5.4), reported here and in §10 of the addendum's final report.

#### Source data with no UI home

Three-state read-back covers exactly the entered set; the unentered frozen-sheet values (Fans/SP) display as `Not provided` — correct per the screen's own vocabulary, caused by §5b row 2.

#### Friction / errors / dead ends

None observed. The Unknown/Not-provided distinction is itself the P-22 answer.

#### Outcome / next screen

`Record snapshot` → S05, run **#36** created at 01:44:08+08:00.

### S05 — Cockpit (immediately after creation)

#### Route and trigger

`http://127.0.0.1:8000/training-runs/36/cockpit`, title `Career: Rice Shower`, 01:44:08+08:00. Flash: `Snapshot recorded.` Screenshot `S05-after-record.png`; post-save re-read screenshot `S05-cockpit-after-saves.png`.

#### What the screen asks for

Header `Rice Shower · Unity Cup #36 · Cockpit`, `Turn 67 logged · Active`, position line `Senior Year · Early October · Turn 67 of 72`. Nav: Cockpit, Training, Races, Skills, Events, Inheritance, Deck, Timeline, Result (`Result: Available when the career is completed.`). Stats strip pre-save: `607 / 1,300 floor` … `397 / 1,800 floor` with note `No build target was set for this run, so bars are drawn against this run's ceilings: the ceilings you entered where you entered one, the scenario's own figure where you did not.` `ENERGY Energy N/A`; `MOOD N/A, not recorded`; `FANS N/A, Fans has not been recorded for this run.`; `SKILL POINTS N/A, Skill Points has not been recorded for this run.` Scenario board: `Unity Cup`, `Guide held`, `Finale 5 turns away`, `Team N/A, not recorded`, `League N/A`, `Bursts N/A`, `Team rank not recorded`, team stat grades N/A, `Unity Trainings N/A`. Caps panel pre-save `1,300×4 / 1,800` + `Blue sparks No blue spark is recorded in this run's Legacy.` + the source's own non-reconciliation note. Advisor: `not reached`; backlog warning `▲ Junior Make Debut fell due on turn 12, before the position this run stands at, and no race is recorded against it.`; `Not enough information: Energy is not recorded for this run, so there is no band to read.` Obligations table: `Junior Make Debut … not reached, Junior Year · Turn 12`; `URA Finals Qualifier / Semifinal / Final (Aoharu) … unknown, 5 turns away` (shared-calendar rows on a Unity Cup run; mechanism note in §8b). Where-to-go grid: Training decision, Race decision, Skills planner, Run deck, Timeline, Career result, Run settings, Scenario detail, Team setup.

#### Values entered + source trace

None at first visit. Persistence read-back: position, five stats, Unknown Energy, Not-provided Fans/SP all rendered — the S03/S04 entries survived the write.

#### Source data with no UI home

At this visit: caps in force showed as scenario `floor` figures until S14 entry; deck/skills/ancestry/team unrecorded (later screens). Mood `N/A` (source GREAT — no field at snapshot door; S06 records mood only on a recorded turn).

#### Friction / errors / dead ends

`URA Finals …` labels on Unity Cup obligations are shared-calendar rows, corroborated by the page's own `CAREER FINALS, EVERY SCENARIO` / `UNITY CUP ONLY` group headings; recorded as an observation with mechanism, not a defect (skill register precedent).

#### Outcome / next screen

`Training` → S06. After S14/S17/S18 saves, a re-read (documented trap procedure) showed the upgraded strip: `Speed +0% | 607 / 1,325 cap` … `Guts +20% | 736 / 1,308 cap`, `Caps 1,325/1,322/1,368/1,308/1,800`, `Team Blue Bloom · Dreaming Big`, `League 8 · +30 all attributes`, `Bursts 11 (6 spirit · 5 extreme)`, `Finale 5 turns away`.

### S06 — Training decision

#### Route and trigger

`http://127.0.0.1:8000/training-runs/36/training`, title `Training decision: Rice Shower`, 01:44:45+08:00. Screenshot `S06-training.png`.

#### What the screen asks for

`Pick this turn's action. Five disciplines, rest, recreation and an optional race. The advisor's recommendation is marked; nothing is committed until you record the turn.` Disciplines 1–5 each print `Feeds … · Lv not recorded base` and `No facility level is recorded for this training on any turn this run has logged, so the published per-level table has no row to read; a Level-1 figure would state a level nobody entered.` Other actions: `Rest` (`Refills Energy. A poor rest refills less and can leave a stayed-up-late penalty.`), `Recreation`, `Unity Training`, `Spirit Burst`, `Race`. Rest figure: `Rest pays +50 on 58.1% of results, +70 on 25.4%, +30 on 12.8%, and +30 with Late-Night Feeling on 3.7% (n=3,833).` Recorded-outcome block: `ENERGY AFTER`, `MOOD AFTER` (Unchanged/GREAT/GOOD/NORMAL/BAD/AWFUL), `FANS`, `SKILL POINTS`; caption `These fields exist because the client shows them, not because the tool computes them. An empty field records "not read off the screen", which is different from a zero.` Controls `Undo turn 67`, `Discard`, `Record turn 68`, `Compare disciplines`, `Client totals this turn (required to record)`.

#### Values entered + source trace

None — recording turn 68 would advance past the source's turn-67 snapshot and fabricate outcomes; not done.

#### Source data with no UI home

Facility levels 4/4/4/5/4: the screen's mechanism is per-turn recorded levels, not the client's team-grade mapping (P-32 mechanism answer recorded; the source's grade-derived Guts 5 has no input home without a recorded turn). Mood GREAT has no current-value home short of recording a turn.

#### Friction / errors / dead ends

None. The +50 modal rest figure matches the client's ordinary tier (P-25 answered; 30 appears only as the failure tier, labelled as such inside the distribution).

#### Outcome / next screen

`Races` → S07 (after ENV-DEV-01 resume).

### S07 — Race decision

#### Route and trigger

`http://127.0.0.1:8000/training-runs/36/races`, title `RACE DECISION — Enter a race, or skip the turn` (page title `Races`), 01:49:31+08:00 (ENV-DEV-01 resume 01:49:14). Screenshot `S07-races.png`.

#### What the screen asks for

`Available this turn` — the calendar's races at turn 67, 11 rows with grade, surface·distance·aptitude letter, `FANS NEEDED`, `SKILL PT`, `Enter`: Fuchu Umamusume Stakes (G2, Turf·Mile C, Poor fit, 1800, +35), Green Channel Cup (OP, Dirt·Sprint E, 350, +30), **Kyoto Daishoten (G2, Turf · Medium A, 2000, +35)**, Ladies' Prelude, M.C. Nambu Hai (G1, Dirt, 12000), Mainichi Okan, October Stakes, Opal Stakes, Shinetsu Stakes, Tokyo Hai, Uzumasa Stakes. Detail panel for the selected race prints placement payouts where published: `1st 35 SP / +8 stat / fans scales`; 2nd/3rd `N/A, Only the winner's payout is published`; note `A first-place finish at 0 Race Bonus pays G1 +10 stat / +45 SP, G2-G3 +8 / +35 and OP-Pre-OP +5 / +30`. `Consecutive races … Races in a row so far: not recorded`; `No probability is printed for this scenario.` Controls: `Undo turn 67`, `Train instead`, `Enter a race`, `Race not on the calendar`.

#### Values entered + source trace

None — entering Kyoto Daishoten would record a race outcome the source does not provide (race not yet run at the snapshot).

#### Source data with no UI home

None: Kyoto Daishoten matches the source's scheduled race including the 2,000-fan criterion (P-18/P-61 partial support).

#### Friction / errors / dead ends

None. Aptitude letters shown are the catalogue's (`Mile C` on Fuchu etc.); the source's trainee-aptitude conflict is not re-litigated here (P-29: the picker shows letters per race axis, not a trainee aptitude editor).

#### Outcome / next screen

`Skills` → S08 (wrong door, E-01) → resumed at S09.


### S08 — Skills (global library — wrong door)

#### Route and trigger

`http://127.0.0.1:8000/skills`, title `Library · Skills`, 01:49:45+08:00. Arrived by clicking nav label `Skills` on S07. Screenshot `S08-skills.png`.

#### What the screen asks for

Global skill catalog: `623 skills available on Global`, scope filter (Global only/Every skill/Japan only), search, type/distance/unique filters, table `SKILL | TYPE | EFFECT | CONDITION (ENGINE) | BASE COST` with `Open` detail links.

#### Values entered + source trace

None — wrong destination; no data belongs here.

#### Source data with no UI home

Not applicable (wrong screen).

#### Friction / errors / dead ends

**E-01 (filed):** the run rail's `Skills` and the sidebar's global `Skills` carry the identical accessible name on the same screen; the first click went to the global library. Outcome was correct once the run's own href (`/training-runs/36/skills`) was used. This reproduces the duplicate-label finding known from prior walks (recorded here from this walk's own observation).

#### Outcome / next screen

Cockpit re-nav by exact href → S09.

### S09 — Skills planner (run)

#### Route and trigger

`http://127.0.0.1:8000/training-runs/36/skills`, title `Skills planner: Rice Shower`, 01:58:24+08:00 (after ENV-DEV-02/03 resumes). Screenshot `S09-run-skills.png`.

#### What the screen asks for

`Spend Skill Points. A hint lowers a skill's cost; the ladder is 10% / 20% / 30% / 35% / 40% off. A hint you have not earned still buys the skill at full price.` Budget: `SKILL POINTS N/A`, `PLANNED SPEND 0`, `REMAINING N/A`, `Learn planned skills`. Search/type/hint filters; owned-skills area: `No skill is recorded on this run yet.` Hint-ladder table (`Hint Lvl 1…Max`, 10/20/30/35/40%) with note: `The ladder steps +10, +10, +10, +5, +5, and it is not linear past Lv3. The measured sample reconciles exactly: 130 → 104 / 91 / 84, and 160 → 144 / 128 / 112 / 96. The one rounding case is 130 × 0.65 = 84.5, floored to 84. "Lv Max" is a caption, not a fifth number.` Add-a-skill searchable picker; `Add to run`; `Build target and recorded skills`.

#### Values entered + source trace

None — source-owned skills (11 named) belong to the trainee at this snapshot; the planner's "no skill recorded" state is correct for a fresh snapshot entry. Adding them would be improvisation beyond the snapshot contract (P-15/P-17: acquisition vs planning distinction — the planner models *planned* purchases, and the owned set has no entry path at snapshot time; recorded as U-class).

#### Source data with no UI home

The 11 owned skills (Blue Rose Closer Lvl 3, Red Shift/LP1211-M, Corner Adept ○, Swinging Maestro, Nimble Navigator, Steadfast, Deep Breaths, Extra Tank, Pace Chaser Straightaways ○, Shrewd Step + Corner Recovery ○) have **no entry path on any screen reached** — U-02.

#### Friction / errors / dead ends

None on the screen itself.

#### Outcome / next screen

`Events` → S10.

### S10 — Event decision

#### Route and trigger

`http://127.0.0.1:8000/training-runs/36/events`, title `Event decision: Rice Shower`, 02:02:02+08:00 (ENV-DEV-03 resume). Screenshot `S10-events.png`.

#### What the screen asks for

`A training event is asking — Pick one option. The event resolves immediately and cannot be re-run; an Alarm Clock retries a lost goal race, not an event.` Fields: `Name the event as the client shows it` (text, `Source not set`), options list (`The option's own words`, `Selecting shown; outcome not predicted.`), provenance block `Where this event came from (required to record)`, outcome recorder: `ENERGY / MOOD / SKILL POINTS / STAT CHANGED — Not recorded` with note `This build does not record an event's Energy, Mood, Skill-Point or stat delta, so those four state "not recorded" rather than a zero. The note and the bond change are the two outcome fields this tool can store.` Then `BOND CHANGE`, `NOTE`; provenance table `Source Client screen / Effect table Not published / Retryable No: Alarm Clock covers goal races only`. Controls: `Undo turn 67`, `Skip`, `Record event`, `Recorded events on this run`.

#### Values entered + source trace

None — no event is named at the snapshot; the screen is reached from the run rail without a pending event and correctly shows a blank recorder.

#### Source data with no UI home

The client's two event voices (§2.5 frozen voices) would be transcribable here as text; none reached.

#### Friction / errors / dead ends

None.

#### Outcome / next screen

Deep-link → S11.

### S11 — Inheritance

#### Route and trigger

`http://127.0.0.1:8000/training-runs/36/inheritance`, title `Inheritance event: Rice Shower`, 02:04:27+08:00. Screenshot `S11-inheritance.png`.

#### What the screen asks for

`The inheritance moment happens at career start, Classic and Senior. Pick the spark you want carried from each parent; the run's caps move by the result.` Tree slots: two grandparents + parent per side around `TRAINEE Rice Shower / Unity Cup`, each `Not set / slot open`; `COMPATIBILITY Not recorded — raises trigger chance and payout`. Per-side parent panels: `No parent recorded for this slot.` Note: `One selection per run is what this tool stores. The moment grants one spark per parent, but the run carries a single legacy selection, so this screen lists the sparks each parent carries and the run's recorded selection rather than promising per-parent radios the column cannot hold.` Result table: `Speed/Stamina/Power/Guts cap (scenario) 1300 … Wit 1800`, CHANGE `N/A · this tool computes no inheritance change`, AFTER unchanged; `BLUE SPARKS 0 recorded · 0★`; `APTITUDE SHIFTS none recorded this pick`; `SKILL HINTS none recorded this pick`. Note: `Two ★3 blue parents raise a cap by 16 each at the start of a career, and this run's ceilings are already above its scenario's published cap where an entered ceiling was recorded; inheritance adds to the entered value, never to the published one.` Controls: `Skip this moment`, `Record inheritance`, `Record what this moment granted (required to record)`, `Observed inheritance events (0)`, `Inheritance milestones`.

#### Values entered + source trace

None — the six-ancestor tree with per-instance spark chains (frozen §2.3) has no per-node input here (U-01); entering a single legacy selection would misrepresent two branches with distinct sets.

#### Source data with no UI home

Per-instance chains for 6 ancestors (Vodka, 2× Daiwa Scarlet with different ranks/spark sets, Seiun Sky, Maruzensky Guest, Taiki Shuttle); Guest flags; ancestor rank badges (B+/A/UG/S+); held-vs-transferred distinction. All U-01.

#### Friction / errors / dead ends

None — the screen itself states the single-selection limitation.

#### Outcome / next screen

Deep-link → S12.

### S12 — Deck (first visit, empty)

#### Route and trigger

`http://127.0.0.1:8000/training-runs/36/deck`, title `This run's six slots`, 02:09:48+08:00. Screenshot `S12-deck.png`.

#### What the screen asks for

`This is this run's own deck; changing it here does not touch any other career. Effects apply from the next turn.` `The six slots — A deck is six cards. Position six is the friend slot, and that role belongs to the position: any card may sit in it.` Slots 1–5 `Not equipped.` with `Ownership N/A, No ownership has been recorded for this slot yet.` + `Owned`/`Rented` toggle + `Choose a card in Slot N`; Slot 6 headed `Slot 6 · Friends` with `Friends slot` tag. Footer: `The owned or rented flag is saved with the deck, one value per slot, and stays on the run after a reload. A slot you have not classified reads as N/A rather than as Owned.` `0 of 6 slots filled`; `Cancel`, `Save and exit`; warning `Saving replaces the whole deck: slots left empty are cleared.`

#### Values entered + source trace

None at first visit; fill happens at S18.

#### Source data with no UI home

None here; the deck values were enterable (S18).

#### Friction / errors / dead ends

None.

#### Outcome / next screen

Deep-link → S13; deck fill at S18.

### S13 — Career timeline

#### Route and trigger

`http://127.0.0.1:8000/training-runs/36/timeline`, title `Career timeline: Rice Shower`, 02:15:13+08:00. Screenshot `S13-timeline.png`.

#### What the screen asks for

`Every logged turn, race and event — Rice Shower's Unity Cup career, in order. 1 entry recorded.` Counters: `TURNS RECORDED 1`, `RACES ENTERED 0`, `EVENTS 0`, `FINALE Not reached`. Filters (KIND/YEAR). Record table: `67 | Senior · Early Oct | Turn | Turn recorded · Recorded as entered | Edit turn 67 | Energy N/A | SP N/A`. Note: `Turn numbers are the run's own; the position is the calendar's label for that turn. … an N/A is "not recorded", never zero.` Phase grouping: `Junior Year · 0 entries — Turns 1-24. Duration unknown for most entries; the Debut race is the one dated obligation.` `Classic Year · 0 entries`, `Senior Year · 1 entry`, `After the grid · finale block (0)`. Controls: `Career result`, `Undo turn 67`, `All careers`.

#### Values entered + source trace

None. Persistence evidence: turn 67 row `Recorded as entered` — the snapshot's position write is visible on a later application screen (P-04).

#### Source data with no UI home

None new.

#### Friction / errors / dead ends

None.

#### Outcome / next screen

Deep-link → S14.

### S14 — Run settings (identity, growth, ceilings)

#### Route and trigger

`http://127.0.0.1:8000/training-runs/36/settings`, title `Run settings: Rice Shower`, 02:15:26+08:00. Screenshot `S14-settings.png`, `S14-settings-saved.png`.

#### What the screen asks for

`Run identity — What this run was recorded as.` Fields: `Status` (Active/Completed/Retired radio row, default `Active`), `Scenario` (No scenario set/URA Finale/Unity Cup/Trackblazer/Our Grand Concert — shows Unity Cup), `Rarity` (Not stated/★1/★2/★3), `Potential Level` (number), `Growth rate` (five % numbers: Speed/Stamina/Power/Guts/Wit), `Stat ceilings` (five numbers labelled `Scenario 1300` ×4 and `Scenario 1800`), `Save identity`.

#### Values entered + source trace

- `Rarity ★3` clicked; `Potential Level 2` → §2.2 (SR §1.1, 50). Direct.
- Growth rate `+0/+10/+0/+20/+0` → §2.2 (SR §1.2, 58–62; §1.15, 478). Direct.
- Stat ceilings `1325/1322/1368/1308/1800` → §2.2 (SR §1.2, 58–62). Direct. (Wit left at scenario 1800 — equal value, no edit needed.)
- `Save identity` clicked; reload verified all values persisted.

#### Source data with no UI home

No run-number field exists (source has none — correct, P-02 answered: nothing required, nothing to skip).

#### Friction / errors / dead ends

ENV-DEV-04 interrupted the first save attempt (logged); one-call redo succeeded. The ceilings input labels read `Scenario 1300` — the in-force overrides are entered *against* a scenario-labelled field; the cockpit disambiguates (`floor` vs `cap`), but the settings labels alone are E-class (E-03).

#### Outcome / next screen

Deep-link → S15.

### S15 — Team setup (first visit)

#### Route and trigger

`http://127.0.0.1:8000/training-runs/36/team`, title `Team setup: Rice Shower`, 02:34:31+08:00. Screenshot `S15-team.png`.

#### What the screen asks for

`Recorded team state for Unity Cup. Every field stays blank until you state it, and a blank field posts nothing rather than a zero.` Fields: `Team name`, `Team motto`, `League placement`, `Preseason wins`, `Unity Trainings`, `Spirit Bursts`, `Extreme Bursts`, `Current Spirit (manual proxy)`, `Team stat grades` — five selects (Speed/Stamina/Power/Guts/Wit), each `Not recorded/G/F/E/D/C/B/A/S`. Notes: `Per-stat grades are G to S. S+ is the overall team rank, recorded elsewhere.` `Team Rank is recorded through a turn's team-race event, not through this editor.` `Save team state`.

#### Values entered + source trace

None at first visit; fill at S17.

#### Source data with no UI home

Overall Team Rank `S (five stars)` — no field on this form (per the screen's own note it belongs to a team-race turn event; recorded as U-class with the stated mechanism, not a silent absence). Members 20/20 and the roster have no fields anywhere reached (U-04).

#### Friction / errors / dead ends

None.

#### Outcome / next screen

Deep-link → S16.

### S16 — Scenario detail

#### Route and trigger

`http://127.0.0.1:8000/training-runs/36/scenario`, title `Scenario detail: Rice Shower`, 02:34:46+08:00. Screenshot `S16-scenario.png`, `S16-scenario-goals.png`.

#### What the screen asks for

Boards: `Team Race — No team races recorded. Circles are entered on the race form as the number the client showed before you committed.` `Burst count bands` table (4–6/7–9/10–12/13+ → Ignited/Burning Spirit rewards) with explicit conflict warnings (`Three parts of this table are publisher conflicts, not settled facts…`). `Spirit Burst — No teammate burst states recorded. The six states are chargeable, charged, held, normal spent, extreme chargeable and extreme spent.` `Special Training` notes. `Trainee goals` — 8 goals with state + turn: Junior Make Debut `Not reached` turn 12; Spring Stakes top 5 turn 6; Tokyo Yushun top 5 turn 10; Kikuka Sho top 3 turn 20; Nikkei Sho top 3 Senior turn 6; Tenno Sho (Spring) 1st Senior turn 8; Takarazuka Kinen top 3 Senior turn 12; Arima Kinen 1st `Upcoming` Senior turn 24; `Goals Achieved 0 of 8`; per-goal `Record this race`. `Mandatory races` (Debut + 3 URA Finals rows, `Upcoming`, `N/A` dates). `Team race coverage` table (Sprint/Mile/Medium/Long/Dirt, `N/A`, note `no column stores it`). `Team race shape`, `Opponent projection N/A`.

#### Values entered + source trace

None — goals' cleared/active states in the source (3 cleared, 1 active) have no input control on this read-only board; recording them requires race entries that would fabricate outcomes (U-05).

#### Source data with no UI home

Goal fan criteria (2,000/20,000) not printed on the board; the races screen carries fan thresholds per race instead (P-61 partial). Unity Cup per-round rows (R1–R4 WIN + finals countdown) not present as a round list; `Preseason wins 4` count on S15/S17 is the single-number representation the source's row model contradicts (P-55 → U-06).

#### Friction / errors / dead ends

None.

#### Outcome / next screen

Team fill → S17.

### S17 — Team setup (filled)

#### Route and trigger

`/training-runs/36/team`, 02:49–02:53+08:00. Screenshots `S17-team-saved.png`.

#### What the screen asks for

Same form as S15.

#### Values entered + source trace

- `Team name Blue Bloom`, `Team motto Dreaming Big` → §2.2 (SR §1.6, 211; §1.8, 274). Direct.
- `League placement 8` → §2.2 (SR §1.8, 274). Direct.
- `Preseason wins 4` → §2.2 (SR §1.6, 211–214). Direct.
- `Unity Trainings 55`, `Spirit Bursts 6`, `Extreme Bursts 5` → §2.2 (SR §1.8, 277–279). Direct.
- Team stat grades `A/A/A/S/A` → §2.2 (SR §1.8, 276). Direct.
- `Save team state`; reload verified: selects `A|A|A|S|A`, all seven text values returned.

#### Source data with no UI home

`Current Spirit (manual proxy)` left blank — the source's spirit gauge is per-member (reference §2.2.2); no run-level figure exists in the source; correctly left absent. Tooltip `All Attributes + 30` appeared on the cockpit board after save, tied to league 8 (P-27/P-31 answered).

#### Friction / errors / dead ends

ENV-DEV-07 during verify (logged); v3/v4/v5/v6 sequence converged; grades are native selects, first button-based selector failed (walk instrumentation, not an app defect).

#### Outcome / next screen

Deep-link → S18.

### S18 — Deck (filled)

#### Route and trigger

`/training-runs/36/deck`, 03:00–03:16+08:00. Screenshots `S18-deck-picker.png`, `S18-deck-fill.png`, `S18-deck-filled.png`, `S18-deck-ownership.png`.

#### What the screen asks for

Per-slot chooser navigates to `?slot=N` with `Filter — N of 251 cards available`, GET search form (`query=`), rows with `Equip to Slot N`. Staging is URL-param based (`deck[1]=250`); a fresh navigation drops staged picks (observed); `Save and exit` writes the whole deck.

#### Values entered + source trace

- Slot 1 Tokai Teio `[Dream Big!]` SSR Speed → §2.2/§2.7 (SR §1.4, 119). Direct.
- Slot 2 Nishino Flower `[Even the Littlest Bud!]` SSR Speed → §1.4, 122. Direct.
- Slot 3 Biko Pegasus `[Double Carrot Punch!]` SSR Speed → §1.4, 123. Direct.
- Slot 4 Super Creek `[Piece of Mind]` SSR Stamina → §1.4, 124. Direct.
- Slot 5 Matikanetannhauser `[Just Keep Going]` SSR Guts → §1.4, 125. Direct.
- Slot 6 Haru Urara `[Urara's Day Off!]` SSR Guts → §1.4, 126. Direct.
- Ownership: slot 4 `Rented`, others `Owned` → §2.7 borrowed column (SR §1.4, 128–130). Direct.
- `Save and exit`; reload verified: all six cards by name/epithet/rarity/type + `6 of 6 slots filled` + flags aria-pressed state.
- **Position conflict recorded:** source lists Creek fourth but calls the Friends slot sixth; the tool defines the friend slot as position six, so Creek sits in Slot 4 flagged Rented — both facts preserved (frozen §2.7 note), not smoothed.

#### Source data with no UI home

Card levels (35/35, 30/30, 50/50, 35/35): the slot panel prints `Level` + `Card effects (N)` + `Not a Scenario Link` / `Scenario Link` but no level input was found in the reached UI (U-07); limit-break state likewise absent. Current-vs-max effect values: not on this surface (catalog comparison context only).

#### Friction / errors / dead ends

Staged-equips-lost on fresh navigation (walk instrumentation cost, recorded as friction); ownership selector first pass hit slot 1's pills six times (instrumentation); both fixed in-walk.

#### Outcome / next screen

Deep-link → S19.

### S19 — Career result (stop state)

#### Route and trigger

`http://127.0.0.1:8000/training-runs/36/result`, title `Career result: Rice Shower`, 03:17:27+08:00. Screenshot `S19-result.png`.

#### What the screen asks for

`This career is still running — This career is still running, so there is no result to read yet. The result screen reads a run whose status is Completed; until then the Cockpit is where the next turn is decided and where the status changes.` Controls: `Back to the Cockpit`, `Career Timeline`. Nav pill `Result` renders as a non-link span (`aria=step`).

#### Values entered + source trace

None. Lifecycle preserved: the run is Active at turn 67; the walk stops here without recording turns 68–72, without marking Completed/Veteran/Legacy, without inventing outcomes.

#### Source data with no UI home

None new.

#### Friction / errors / dead ends

None — this is the correct mid-career stop state.

#### Outcome / next screen

Walk end. 03:17:27+08:00.


## 5b. Improvisation log (completed pass)

Values entered that the source does not provide, or entered with a known deviation:

| # | Screen | Field/control | Value chosen | Why | Alternatives rejected |
| --- | --- | --- | --- | --- | --- |
| 1 | S03 | `Energy` | Typed `0`, then ticked its `I don't know this value` checkbox | The screen needs a value before the unknown flag supersedes it; census `5:1` confirms Energy row is Unknown, so no false figure persists | Leaving the number only (would persist a false 0); entering a guessed band value |
| 2 | S03 | `Fans`, `Skill Points` | Not entered although frozen sheet carries 209,245 / 173 | Walk deviation: fields skipped during the fill pass; logged, not smoothed. Acceptance of these two values is therefore untested | Entering them late via settings (no Fans/SP field exists there) |
| 3 | S18 | Deck save timing | Equipped all six, then one `Save and exit` | Staged picks live in URL params; fresh navigation drops them (observed) | Per-slot save (not offered; saving clears empty slots) |
| 4 | S18 | Creek slot position | Slot 4 (Rented) | Source lists Creek fourth; tool defines friend slot as position six — both facts preserved | Moving Creek to slot 6 (would falsify the source's listed order) |

No run number, ancestor rank, spark count, roster identity, numeric Energy or rest outcome was invented anywhere.

## 6b. Findings register (completed pass)

**F = 0 · E = 3 · U = 7 (10 findings; 0 blocking).** ENV-01 and ENV-DEV-01…07 remain environmental records, not findings.

| ID | Type | Screen(s) | Short description | Blocking? |
| --- | --- | --- | --- | --- |
| E-01 | Experiential | S07→S08 | Run-rail `Skills` and sidebar `Skills` carry identical accessible names; first click went to the global library (`/skills`) | No |
| E-02 | Experiential | S01/S02 | No in-app link to `/career/snapshot` was observed on Dashboard or Careers; the door that matches a mid-career snapshot is reachable only by URL | No |
| E-03 | Experiential | S14 | Ceiling inputs are labelled `Scenario 1300` while they hold the run's in-force ceilings; disambiguation exists only on the Cockpit strip (`floor` vs `cap`) | No |
| U-01 | Unrepresented data | S11 | Per-ancestor spark chains: 6 ancestors × (stat/aptitude/unique/white rows with per-node stars), Guest flags, rank badges (B+/A/UG/S+), two Daiwa instances with different sets, held-vs-transferred distinction — the tool stores one legacy selection per run | No |
| U-02 | Unrepresented data | S03/S09 | The 11 owned skills (incl. Blue Rose Closer Lvl 3, Corner Recovery ○) have no entry path at snapshot creation or on the run; the planner models planned purchases only | No |
| U-03 | Unrepresented data | S03 | Rarity and Potential Lvl 2 absent from the snapshot form (later entered on S14); full scenario label `Unity Cup: Shine On, Team Spirit!` not shown — selector prints `Unity Cup` | No |
| U-04 | Unrepresented data | S15/S17 | Roster (18 named identities vs 20/20), two unknown member identities, overall Team Rank S-five-stars (recorded via turn team-race events per the screen's own note) | No |
| U-05 | Unrepresented data | S16 | Goal states (3 CLEAR, 1 active) and fan criteria (2,000/20,000) not enterable on the read-only goals board | No |
| U-06 | Unrepresented data | S15/S16 | Unity Cup rounds as rows (R1–R4 WIN with dates + finals countdown) — stored as a single `Preseason wins` count | No |
| U-07 | Unrepresented data | S18 | Card levels (35/35, 30/30, 50/50, 35/35) and limit-break state have no input on the deck surface; card panel prints `Level` label only | No |

## 7.9b Method decisions and consequences (completed pass)

- **Door:** `/career/snapshot` (snapshot entry), per the amended plan. Visible alternatives on S01: `New career` → `/career/setup/scenario`, `Import a historical career` → `/training-runs/import`. No link to the snapshot door was found on either S01 or the PRE-S02 careers list; the direct URL was chosen deliberately and recorded as E-02 rather than silently treated as the normal path. What the wizard would have measured differently: a turn-1 default position and no per-instance ancestry input either; the snapshot door carried the source's actual position, which is the point of this run.
- **Turn index:** 67, carried by the door itself (`Year Senior / Month 10 / Half Early`); rendered back as `Senior Year · Early October · Turn 67 of 72` and `Turn 67 logged · Active`. Derivation `(3−1)×24+(10−1)×2+1=67` preserved from frozen §2.5. The position is stored, not derived: timeline row `Recorded as entered`.
- **What this enabled:** every run-level surface read at the source's real position; obligation countdowns (`5 turns away`) counted from 67; facility-level mechanism (per-turn recorded) observed; growth/ceilings/identity editable post-hoc; deck enterable to the source's exact six cards.
- **What it prevented measuring:** preseason rounds R1–R4 as rows (no round list exists — U-06), ancestor spark entry (U-01), owned-skill acquisition (U-02), goal clearance states (U-05), mood/energy current values (no field short of recording a turn), Fans/SP acceptance (improvisation §5b row 2).
- **Second door sentence:** the wizard would have measured the turn-1 default and the wizard's own 7-step data entry, hiding whether the app can hold a mid-career position at all — walk 6's artifact.

## 7.7b Traceability matrix (completed pass)

Entry statuses: **persisted** = entered + accepted + reload-verified; **read-back** = rendered on later screen without separate edit; **skipped (§5b-2)** = walk data-entry omission, acceptance untested; **U-nn** = no field anywhere reached; **shown** = rendered by the tool from position/catalogue, not entered.

| Datum | Value | Screen | Field/control | Outcome |
| --- | --- | --- | --- | --- |
| Trainee | Rice Shower | S03/S04/S13 | `Trainee *` select | persisted |
| Card/epithet | `[Rosy Dreams]` | S03 | picker row display | shown in picker; standalone field U-03 |
| Rarity | ★3 | S14 | `Rarity` segmented | persisted |
| Potential/awakening | 2 | S14 | `Potential Level` | persisted |
| Scenario | Unity Cup | S03/S14 | `Scenario *` | persisted |
| Full scenario label | Unity Cup: Shine On, Team Spirit! | — | — | U-03 (never shown) |
| Snapshot | Senior Year Early Oct | S03/S04/S05 | `Year/Month/Half` | persisted |
| Turn | 67 | S03/S13 | door carries; timeline row | persisted (`Recorded as entered`) |
| Turns remaining | 5 | S05 | derived | shown `Finale 5 turns away` |
| Lifecycle | mid-career in progress | S04/S19 | snapshot door; `Status Active` | preserved (S19 stop) |
| Run number | none in source | — | — | no field exists (P-02 answered) |
| Speed | 607 | S03/S05 | stat input | persisted |
| Stamina | 577 | S03/S05 | stat input | persisted |
| Power | 549 | S03/S05 | stat input | persisted |
| Guts | 736 | S03/S05 | stat input | persisted |
| Wit | 397 | S03/S05 | stat input | persisted |
| Speed cap | 1325 | S14/S05 | `Stat ceilings` | persisted (strip `cap`) |
| Stamina cap | 1322 | S14/S05 | `Stat ceilings` | persisted |
| Power cap | 1368 | S14/S05 | `Stat ceilings` | persisted |
| Guts cap | 1308 | S14/S05 | `Stat ceilings` | persisted |
| Wit cap | 1800 | S05 | scenario default | shown (equal to floor; no edit needed) |
| Scenario floor | 1300×4/1800 | S05/S11 | labels | shown as `floor`/`(scenario)` |
| Growth Rate | +0/+10/+0/+20/+0 | S14/S05 | `Growth rate` % row | persisted (`+N%` on strip) |
| SP | 173 | — | — | skipped (§5b-2); `SKILL POINTS N/A` |
| Fans | 209,245 | — | — | skipped (§5b-2); `FANS N/A` |
| Mood | GREAT | S06 | outcome picker ladder | no current-value home (`N/A, not recorded`); ladder exists |
| Energy | unknown, low | S03/S05 | `I don't know this value` | persisted as Unknown (`Energy N/A`) |
| Deck card 1 | Tokai Teio `[Dream Big!]` SSR Speed | S18 | slot 1 chooser | persisted |
| Deck card 2 | Nishino Flower `[Even the Littlest Bud!]` | S18 | slot 2 | persisted |
| Deck card 3 | Biko Pegasus `[Double Carrot Punch!]` | S18 | slot 3 | persisted |
| Deck card 4 | Super Creek `[Piece of Mind]`, borrowed | S18 | slot 4 + Rented flag | persisted (position conflict preserved) |
| Deck card 5 | Matikanetannhauser `[Just Keep Going]` | S18 | slot 5 | persisted |
| Deck card 6 | Haru Urara `[Urara's Day Off!]` | S18 | slot 6 · Friends | persisted |
| Owned Details list | 10 named skills | — | — | U-02 (no entry path) |
| Additional owned skill | Corner Recovery ○ | — | — | U-02 |
| Legacy 1 | Vodka `[Wild Top Gear]` B+ | S11 | tree slot (empty) | U-01 (single-selection) |
| Legacy 2 | Maruzensky `[Formula R]` UG, Guest | S11 | tree slot | U-01 |
| L1 grandparent A | Daiwa Scarlet `[Peak Blue]` B+ | S11 | tree slot | U-01 |
| L1 grandparent B | Seiun Sky `[Reeling in the Big One]` A | S11 | tree slot | U-01 |
| L2 grandparent A | Daiwa Scarlet `[Peak Blue]` S+ | S11 | tree slot | U-01 (S+ unenterable) |
| L2 grandparent B | Taiki Shuttle `[Wild Frontier]` UG | S11 | tree slot | U-01 |
| Blue sparks | 6 per-instance values | S05 | caps panel | shown as absence note (`No blue spark…`); entry U-01 |
| Team/name/motto | Blue Bloom · Dreaming Big | S17/S05 | form | persisted (cockpit board) |
| Overall Team Rank | S five stars | S15 | — | U-04 (turn-event path per screen note) |
| League | 8 | S17/S05 | form | persisted (`League 8 · +30 all attributes`) |
| Unity Trainings | 55 | S17/S05 | form | persisted |
| Spirit Bursts | 6 | S17/S05 | form | persisted (`Bursts 11 (6 spirit · 5 extreme)`) |
| Extreme Spirit Bursts | 5 | S17/S05 | form | persisted |
| Members | 20/20, 18 named + 2 unknown | — | — | U-04 |
| Team stat grades | A/A/A/S/A | S17/S05 | selects | persisted (grades row) |
| Facility levels | 4/4/4/5/4 | S06 | — | per-turn recorded mechanism; grade-derived Guts 5 needs a recorded turn (P-32) |
| Preseason results | R1–R4 WIN w/ dates | S15/S17 | `Preseason wins 4` | U-06 (count only) |
| Race scheduled now | Kyoto Daishoten G2 Medium A 2000 fans | S07 | calendar row | shown at turn 67 (not entered) |
| Pending race | finals after Senior Late Dec | S05 | obligation row | shown `unknown 5 turns away` (P-19) |
| Active goal | Arima Kinen 1st turn 72 | S16 | goals board | shown `Upcoming`; state not enterable (U-05) |
| Cleared goals | Nikkei Sho / Tenno Sho (Spring) / Takarazuka | S16 | goals board | shown `Not reached`; clearance not enterable (U-05) |
| Aptitudes, current frame reading | Turf A/Dirt C; E/B/A/A; A/A/B/C | S07 | race rows | catalogue letters shown per axis; no trainee editor at snapshot (P-29) |
| Aptitudes, old supplied sheet | Turf A/Dirt G … End G | — | — | retained as conflicting reference, not entered |
| Aptitudes, catalogue claim | Mile C / Front B / Late C | S07 | race rows | observed as catalogue side of conflict |

## Appendix A2 — Probe answers (completed pass)

Answers from this pass; superseded-attempt rows in Appendix A stand as pre-pass dispositions. Where a prior claim was reproduced, both are noted.

| Probe | Answer (completed pass) |
| --- | --- |
| P-01 | **Yes** — snapshot door records in-progress state; never forced completed (S02–S05, S19). |
| P-02 | **No run number asked anywhere; nothing to skip** (S03 form fields enumerated; S14 has none). |
| P-03 | **Snapshot door** carried the position; wizard and CSV import visible as alternatives; no snapshot link in-app (E-02). |
| P-04 | **67, stored not derived** — `Turn 67 of 72`, `Turn 67 logged`, timeline `Recorded as entered`; countdowns count from 67. |
| P-05 | Caps asked (S14 overrides) and shown; sparks not enterable (U-01); the run's own non-reconciliation is stated on-screen rather than warned-against. |
| P-06 | **Both vocabularies present and distinct:** `floor` (scenario) vs `cap` (in-force) on the cockpit strip; settings labels say `Scenario` for the override inputs (E-03). |
| P-07 | **Yes — `Growth rate`** five-% row on S14; renders `+N%` beside each stat on the strip. |
| P-08 | **Absence representable by not-entering;** cockpit prints `No blue spark is recorded in this run's Legacy.` (explicit absence, not zero). |
| P-09 | No — single legacy selection per run (screen-stated); two Daiwa instances with distinct sets unrepresentable (U-01). |
| P-10 | No Guest field reached (U-01; screen shows six slots but one selection). |
| P-11 | **Yes — friend slot is positional (slot 6) + per-slot `Owned`/`Rented` flag**, persisted (S18). |
| P-12 | No — current-vs-max effect values not on the deck surface (catalog context only). |
| P-13 | **Level: no input (U-07);** limit-break: not representable; one value only, effectively. |
| P-14 | Distinguishable by type label (Creek = Stamina card in Friends-positioned deck); "Pal" is a card-type word in the catalog rows, not a slot role — no conflation on the deck screen. |
| P-15 | Skills modeled as a planned-purchase list; owned 11 have no entry path (U-02); count fields absent. |
| P-16 | Hint levels are part of the purchase ladder (S09 note); Blue Rose Closer Lvl 3 as an *owned* level has no home (U-02). |
| P-17 | Planner = planned; owned = not represented at snapshot (U-02). Distinction exists structurally but the owned side is unreachable. |
| P-18 | Races listed per turn with grade/surface/distance/aptitude/fan-need/SP; per-race won/placed/pending states only via recorded turns (not entered by design). |
| P-19 | Yes — finals pending is the obligation row `unknown … 5 turns away`; no fabricated outcome (P-19 answered). |
| P-20 | Goals shown with states; cleared/active not enterable (U-05); board opens on the run's own era (Junior rows listed, Senior rows flagged). |
| P-21 | Mood five-state ladder present (S06 outcome picker: Unchanged/GREAT/GOOD/NORMAL/BAD/AWFUL); current value has no snapshot-time home (`N/A, not recorded`). |
| P-22 | **Energy is a number-with-unknown-flag**: type a value or tick `I don't know this value`; persisted as Unknown; renders `Energy N/A`; advisor states the missing band explicitly (S03/S05). |
| P-23 | Turns remaining derived from position, not a field; renders `5 turns away` / `Turn 67 of 72`. |
| P-24 | **Two counters, two events:** `Finale 5 turns away` (obligation table, = finals) and the `▲` backlog note for turn-12 Debut; no separate Senior-Late-Nov payout counter observed. |
| P-25 | **Yes — rest modal figure is +50** (`Rest pays +50 on 58.1%…`), with +70 and +30 tiers labelled inside the distribution; 30 never called ordinary. |
| P-26 | SP field exists at snapshot (skipped, §5b-2) and budget bar on S09 (`SKILL POINTS N/A` until recorded). |
| P-27 | **Yes — all six fields** on S15/S17 (name, motto, league, trainings, bursts, extreme bursts; members not present) — persisted and rendered on the cockpit board. |
| P-28 | No roster entry anywhere reached (U-04); 20/20 not representable. |
| P-29 | Picker letters: race-axis catalogue letters observed on S07 rows (e.g. Kyoto `Turf · Medium A`); no trainee-aptitude editor at snapshot; the frozen conflict stands unresolved as recorded. |
| P-30 | One label only: `Potential Level` (S14). No separate awakening field. |
| P-31 | **Per-stat grades G–S with explicit `S+` exclusion note** (`S+ is the overall team rank, recorded elsewhere`); overall rank via turn event (U-04 path). |
| P-32 | Facility levels are **per-turn recorded** facts on this app (`Lv not recorded base`), not grade-derived; the client's A→4/S→5 rule has no home (mechanism answer recorded). |
| P-33 | Calendar extent: `Turn 67 of 72`; timeline shows `After the grid · finale block (0)` — 72-turn convention stated by the tool's own labels. |
| P-34 | `Unity Cup` only; full label never shown (U-03). |
| P-35 | No (U-01, screen-stated single selection). |
| P-36 | Spark kinds shown as `Blue sparks` count only; four-colour client vocabulary and white-row subtypes unrepresentable (U-01). |
| P-37 | Star counts not enterable (U-01). |
| P-38 | Rank badges/Guest not enterable (U-01). |
| P-39 | Grant-origin portraits: not on any skill surface reached (U-02 adjacent). |
| P-40 | `○` corner variants appear in skill names in the catalog table (e.g. `Corner Adept ○` visible in S09 picker text? — observed in catalog rows on S08/S09 pickers); grouping tiles not present. |
| P-41 | **Yes — discounted price ladder with exact fixture match** (130→104/91/84; 160→144/128/112/96; 84.5→84 floored; `Lv Max` caption) — S09 note block. |
| P-42 | No `Obtained` wording reached on the planner; owned state simply has no row (U-02). |
| P-43 | Skill-origin grouping (Trainee/Card skills) not reached (U-02). |
| P-44 | Run rail: Cockpit/Training/Races/Skills/Events/Inheritance/Deck/Timeline/Result (9 items); client's 7-item rail has **no Log equivalent** observed; no `There is no log.` empty-state string (timeline covers logged history). |
| P-45 | Effect text with style/distance in parentheses present in catalog table (`Slightly increase velocity on an uphill. (Late Surger)`); `○` on the same row where applicable. |
| P-46 | Not tested — ancestry-sourced uniques (`Cut and Drive!`, `Resplendent Red Ace`) not probed in the picker (bounded walk; left unmeasured). |
| P-47 | No category tiles on planner rows (client taxonomy absent; U-02 adjacent). |
| P-48 | Venue-vs-condition skills not probed in the picker (unmeasured). |
| P-49 | Row tints not reachable (owned skills unrepresentable); unmeasured. |
| P-50 | Ancestor `S+` — no field (U-01); refusal untested because the field is absent. |
| P-51 | Held-vs-transferred — single selection; no distinction (U-01). |
| P-52 | **Three states observed distinctly on S05:** goal countdown (via `Turn 67 of 72` + obligations due), finals countdown (`Finale 5 turns away`), and eligibility state (`not reached`/`unknown` per obligation; Debut backlog `▲` note). No `Entry criteria met!` string equivalent observed. |
| P-53 | Tree shape: two-column bracket layout (grandparent×2 + parent per side around trainee) = post-career shape, not the in-career vertical list. |
| P-54 | No UI home for the two client voices (U-class; advisor prose only). |
| P-55 | No — single `Preseason wins` count (U-06); rounds-as-rows unrepresentable. |
| P-56 | Card panel shows `Card effects (N)` + `Level` label + `Not a Scenario Link`/`Scenario Link`; no `Unique Perk` name, no `Unity Cup Exclusive` badge, no per-stat letter+cap line (U-07 adjacent). |
| P-57 | No next-level SP figure (`0 SP to next level` absent) — U-07. |
| P-58 | No tabs on the card surface (single block) — U-07. |
| P-59 | Mood panel `Mood effect at every tier` present on cockpit; exact per-tier sentences not read in this pass (tier names + effects rendered; captured pre-save text shows the panel but not full sentences — recorded as partially measured). |
| P-60 | Fan ladder: **absent from the cockpit**; Fans `N/A` with no ladder (P-60 answered as the recorded-override claim reproducing, this pass). |
| P-61 | Fan criteria per goal: shown per race on S07 (`FANS NEEDED 2000` for Kyoto); per-goal thresholds not on the goals board (U-05). |
| P-62 | Catalogue letters observed on race rows; client-vs-catalogue conflict unresolved and unreconciled (P-29). |
| P-63 | Skill tints unmeasured (no owned-skill rows). |
| P-64 | No single Career-Profile destination; facts spread across Cockpit/Settings/Deck/Inheritance/Scenario (structure answer). |

## 8b. Closing assessment (completed pass)

### 8b.1 What worked

The snapshot door carried the source's mid-career identity end to end: position stored not derived, read back on review, persisted (`Turn 67 logged · Active`, timeline `Recorded as entered`), countdowns counting from 67. Every subsequently reachable form accepted its frozen-sheet values with explicit unknown/absent semantics (Energy Unknown, team fields blank-post-nothing), and all persisted across reloads (verified six times). The deck took the exact six cards by name/epithet/type with a per-slot rented flag. The cockpit renders both cap vocabularies distinctly (`floor` vs `cap`) and carries the source's own cap-vs-sparks non-reconciliation on screen rather than papering over it. The result screen is the correct mid-career stop.

### 8b.2 Functional findings (completed pass)

**Zero.** No screen rejected valid source data; nothing lost or contradicted; no dead ends. The deck staging behavior (URL-param staging lost on fresh navigation) is a walk-instrumentation interaction, recorded as friction, not an application defect: the tool's intended flow stages and saves within one visit.

### 8b.3 Experiential findings

E-01 (duplicate `Skills` label misroutes on first click — reproduced in this pass by this walk's own click), E-02 (snapshot door unlinked from Dashboard/Careers), E-03 (settings ceiling labels say `Scenario` while holding in-force values). All non-blocking.

### 8b.4 Unrepresented source data

Seven findings (U-01…U-07 above). Largest: per-ancestor spark chains with per-node star counts, ranks and Guest flags (the tool stores one legacy selection and states why on-screen); owned-skill inventory (no entry path at snapshot time); Unity Cup rounds as rows (count only); card levels/limit-breaks (labels but no inputs); roster and overall Team Rank (turn-event path only, per the screen's own note).

### 8b.5 Walk completion

**Complete to the flow's end for this snapshot: 19 screens, no dead end.** The walk stops at the mid-career boundary by design — recording turns 68–72 would fabricate the source's future. Measurements unavailable by door choice or tool design are enumerated in §7.9b and the U-register. Final persisted state contains exactly the frozen-sheet values listed in §7.7b plus two deliberately-absent states (Fans/SP skipped per §5b-2); no value in the persisted run contradicts the source.

### 8b.6 Mutation boundary and mutation log

- **Run row created through the UI:** run **#36** (Rice Shower, Unity Cup, turn 67, Active) at 01:44:08+08:00 via `Record snapshot` — irreversible by the screen's own warning, submitted knowingly, reported here.
- **Run mutations after creation (all through the UI):** S14 identity save (rarity/potential/growth/ceilings), S17 team state save, S18 deck save (6 cards + ownership flags). Each reload-verified.
- **Not done:** no app code/config/test/doc (except this addendum), no seeders/factories/SQL/direct DB writes, no API substitution, no DOM editing, no devtools mutation, no run #34 touch, no status change to Completed/Veteran/Legacy, no invented identity fields.
- **Environment mutations:** browser session switch after daemon crash (ENV-DEV-05); harness tab resets ENV-DEV-01/02/03/04/06/07 (recovered by re-navigation); no viewport zoom, no cache clear, no server restart, no build.
- Pre-existing run #35 resume card and #34 row observed read-only only.

## Appendix C — Remediation ideas (non-binding, not implemented)

Ordered by blast radius:
1. **Snapshot door link** (E-02): surface `/career/snapshot` on Dashboard + Careers list; smallest change, highest findability gain.
2. **Ceiling labels** (E-03): rename settings inputs `Run ceiling (scenario floor 1300)` style, or an explicit two-row display; one string each.
3. **Owned-skills entry at snapshot** (U-02): a post-create owned-skills editor would close the largest data gap for mid-career runs.
4. **Round rows for Unity Cup** (U-06): a `preseason_rounds` JSON or per-round rows would replace the single count; needs ADR.
5. **Per-ancestor sparks** (U-01): schema change (per-node rows); the screen's own note already documents the ceiling; largest change, lowest frequency need.
6. **Card level input** (U-07): one numeric field per slot with the level the client shows.


## 9b. Counters observed (completed pass)

| Event | Exact string observed | Screen | Points at |
| --- | --- | --- | --- |
| Finals countdown | `Finale	5 turns away` | S05 (and post-save re-read) | Unity Cup finals after Senior Late Dec |
| Finals countdown (obligations) | `URA Finals Qualifier / Semifinal / Final (Aoharu) … unknown	5 turns away` | S05 | same finals block (shared-calendar rows, grouped under `CAREER FINALS, EVERY SCENARIO` / `UNITY CUP ONLY`) |
| Backlog note | `▲ Junior Make Debut fell due on turn 12, before the position this run stands at, and no race is recorded against it.` | S05 | past-due turn-12 Debut |
| Position counters | `Turn 67 logged · Active`, `Senior Year · Early October · Turn 67 of 72`, timeline `TURNS RECORDED 1` | S05/S13 | run's own position |

No `Entry criteria met!` equivalent; no separate Senior-Late-November payout counter observed anywhere reached. The client's two-counter structure (`5 turn(s) left` + `5 Turn(s) Until the Unity Cup`) has one app-side counterpart (`Finale 5 turns away`) plus per-obligation due states.

## 10b. Definition-of-done (completed pass)

- [x] Every reached screen (S01–S19) has its own ordered section with the six required subheadings.
- [x] Every entered value carries a source trace or is marked not-in-source (frozen §2.2 anchors used).
- [x] Every source datum without a UI home is named (U-01…U-07, listed per screen).
- [x] Lifecycle preserved mid-career/in-progress throughout; never Veteran/Legacy/Completed.
- [x] Door and turn index recorded in §7.9b, no finding IDs there.
- [x] Rendered position == source position (turn 67), verified on five screens.
- [x] Each observed counter recorded with string + event (§9b).
- [x] Functional and experiential findings separate (F=0; E-01..03; U-01..07).
- [x] Findings register matches sections; counts: 10 records · 10 active · 0 withdrawn.
- [x] Stopping point stated (S19, by design).
- [x] All 64 probes answered or explicitly unanswered-marked (Appendix A2; P-46/P-48/P-49/P-63 explicitly unmeasured).
- [x] Traceability matrix complete (59/59 primary rows dispositioned).
- [x] Contamination statement present (§1) and extended: this addendum read only the frozen sheet, the probe list and the plan; historical walks/reconciliations unopened.
- [x] Run-row creation and post-creation mutations reported, not denied.
- [x] Screenshot census: 19 reached screens; 20 PNGs in `RS-UC-2026-11/screens/` (incl. picker + post-save re-read); every cited one exists.
- [x] One H1; fenced code check clean; ledger IDs match section order (parent verification below).

## Appendix D — Final report (completed pass)

1. **Audit file:** `docs/audits/rice-shower-unity-cup-ux-walk-9.md` (this addendum folded in place; superseded attempts preserved).
2. **Screens visited:** 19 — S01–S19 (S08 is the wrong-door observation).
3. **Door taken:** `/career/snapshot` (snapshot entry) by direct URL — no in-app link found (E-02); wizard and CSV import were the visible alternatives on S01. **Turn index entered: 67** (Senior Year · Early October), carried by the door, stored not derived.
4. **End of flow reached:** yes — S19 Result is the correct mid-career stop state (`This career is still running`); no dead end, no forced completion.
5. **Functional findings:** 0 (0 blocking).
6. **Experiential findings:** 3 (E-01 duplicate `Skills` label; E-02 snapshot door unlinked; E-03 ceiling labels say `Scenario`).
7. **Unrepresented-data findings:** 7 (U-01 per-ancestor sparks; U-02 owned skills; U-03 rarity/potential/full-label at snapshot; U-04 roster + overall Team Rank; U-05 goal states/fan criteria; U-06 rounds-as-rows; U-07 card levels/limit-breaks). Largest gap: per-ancestor spark chains — the tool stores one legacy selection and states the reason on-screen.
8. **Counters:** `Finale 5 turns away` (finals after Senior Late Dec); obligations `unknown 5 turns away`; Debut backlog `▲` note; `Turn 67 of 72` / `Turn 67 logged`. No payout-event counter, no `Entry criteria met!` equivalent.
9. **Deviations from one-pass:** ENV-DEV-01…07 (tab resets/daemon crash) each logged at encounter time, each resumed by re-navigation with state preserved; one bounded post-state-change re-read of the cockpit after saves (documented trap procedure); deck staged-and-lost retry (instrumentation). Fans/SP entry skipped (§5b-2) — a data-entry omission, not a navigation deviation.
10. **No application code, configuration or docs (except this audit) modified.** Verified by tree delta at close.
11. **No seeder, factory, SQL, direct DB write, API substitution or devtools path used for data entry;** all entries through the UI. Run #36 and its settings/team/deck saves were created by UI submission and are reported; "no data modified" is **not** claimed.
12. **Stability probe pass:** not run (Appendix B empty; the deviations above are environment recovery, not probes).
