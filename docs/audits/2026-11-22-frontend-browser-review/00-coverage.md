# 00 Coverage | TrainerDesk independent browser audit

**22 November 2026, Malaysia time (UTC+08:00)**  
**Live application** `http://127.0.0.1:8000` in the connected Chrome extension, Windows browser session  
**Scope** Review of rendered pages and safe interaction with existing data

[Findings and historical reconciliation](01-findings.md) · [Plan reconciliation](02-plan-reconciliation.md)

## Baseline and authority

| Baseline | Evidence and limit |
| --- | --- |
| Published repository | GitHub `master` at [`1b987cecd52db460d8f78417fb41f87fdcd70a7b`](https://github.com/IzzatFirdaus/umamusume-laravel13/commit/1b987cecd52db460d8f78417fb41f87fdcd70a7b). Recent published history included [Review filters and bulk Reject](https://github.com/IzzatFirdaus/umamusume-laravel13/commit/39a41ace578efaa68dd4d4f710c94f072755b776) and [race enrichment seeding](https://github.com/IzzatFirdaus/umamusume-laravel13/commit/7bc909e3b17ad53aa5dec09f526db0ae85659012). Windows checkout unavailable to the shell. Local branch, HEAD, dirty status, uncommitted contracts and index write access remain unverified. |
| Live desktop | App rendered with module `spa-DKSRaC9Q.js` during the 1280 px pass and the 768/390 px checks. The audit began at 05:20 MYT. |
| Live asset change | Module `spa-CvVyd8Ax.js` was first captured at 05:32:08 on the 320 px Dashboard. On this later asset, the Review queue still mislabelled race-source rows, the Ledger still advertised the same backlink and all three filters, the Dashboard still named the source career, completed Result still marked its step, and Snapshot still exposed the combobox. Some deeper rechecks were interrupted. A module filename cannot identify the running backend revision. |
| Prior evidence | The 21 November coverage, findings TD-01–TD-08 and plan reconciliation were available as the prior delivered copies. Their evidence was used to locate retests. Later agent reports were treated as leads. No local handoff artifact or later binding amendment was independently established. |

The documentation hierarchy applied to this review is [AGENTS.md §2](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/AGENTS.md#L22) and the [docs index](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/INDEX.md). [PRD](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/PRD.md) and [ARCHITECTURE](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/ARCHITECTURE.md) supply product boundaries, [SCREEN_SPEC](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/SCREEN_SPEC.md) and its HFW overrides govern screens, and [DESIGN](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/DESIGN.md) governs themes and shared UI. Accepted [ADRs](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/adr/README.md), the [consolidated plan](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/plan/implementation-plan-2026-11-20.md), [UI/UX roadmap](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/roadmap/UI-UX-IMPLEMENTATION-ROADMAP-2026-11-18.md) and prior audit identified expectations and work in progress.

## Coverage summary

| Inventory | Count |
| --- | ---: |
| Registered user-facing GET templates | 62 |
| Rendered application screen templates | 58 |
| Career alias redirect checked | 1 |
| Framework health screen checked | 1 |
| Download handlers identified, payloads unverified | 2 |
| Additional exceptional states | One rendered 404 and one unverified POST-only CSV preview |

| CSS viewport | Template coverage | Direct evidence |
| --- | --- | --- |
| 1280 × 900 | 60 | Complete desktop route walk. The route registry and actual rendered headings and destinations were checked. |
| 768 × 900 | 7 | Dashboard, completed Result, Training, Ledger, Review, Help and Snapshot Setup. Ledger reference nav client/scroll widths 463/756 px. Scroll region 461/490 px and tabindex 0. |
| 390 × 900 | 5 | Dashboard, Run Settings, Snapshot Setup, Race catalogue and Wizard Deck. The race table's named scroll region measured 307/640 px and tabindex 0. Deck picker measured 360/360 px horizontally and 862/1058 px vertically, with focus inside before dismissal. |
| 320 × 900 | 6 | Dashboard, Ledger, Snapshot Setup, Review, completed Result and Help. Review's named, focusable scroll region measured 271/672 px. Ledger nav measured 271/756 px and its scroll region 269/490 px. |
| Themes | Midnight Circuit throughout application pages | The published Preferences form persists the selected theme on Save. Live Azure Daybreak and EVA-01 remain unverified. The existing preference was left in place. Framework health uses a separate, unthemed surface. [DESIGN palette section](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/DESIGN.md#L335), [Preferences form](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/resources/js/pages/Preferences/Edit.vue#L301) |

Sampled document widths at 320/390/768 were 305/375/753 px respectively where a vertical scrollbar was present, with equal root scroll width. The table and nav measurements above describe local scroll regions. This check supports containment in the samples and leaves text zoom, every control's focus under sticky chrome, table scroll reach by every input method and unsampled layout combinations unverified.

## Route inventory

All times are 22 November in MYT. Each cell records the first rendered result at that width. Parameterised entries list actual records inspected. “Reviewed” applies to the rendered template and listed evidence.

| Registered template | Class | CSS width and time | Theme | Actual destination and observed headings | Registry |
| --- | --- | --- | --- | --- | --- |
| `/` | Reviewed | 1280 at 05:20:11, 768 at 05:29:35, 390 at 05:30:59, 320 at 05:32:08 | dark | `/` · Trainer Desk / Pick up where you left off / Needs you | [routes/web.php:39](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L39) |
| `/career/setup/scenario` | Reviewed | 1280 at 05:23:31 | dark | `/career/setup/scenario` · Which scenario is this run? / URA Finale | [routes/web.php:48](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L48) |
| `/career/setup/trainee` | Reviewed | 1280 at 05:23:33 | dark | `/career/setup/trainee` · Who are you training? | [routes/web.php:59](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L59) |
| `/career/setup/trainee/{umamusume}` | Reviewed | 1280 at 05:24:55 | dark | `/career/setup/trainee/8` · Vodka ウオッカ / Aptitudes | [routes/web.php:61](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L61) |
| `/career/setup/target` | Reviewed | 1280 at 05:23:35 | dark | `/career/setup/target` · What are you building toward? / What the career is for | [routes/web.php:75](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L75) |
| `/career/setup/legacy` | Reviewed | 1280 at 05:23:38 | dark | `/career/setup/legacy` · Inherit from two parents / Parent A Not chosen | [routes/web.php:88](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L88) |
| `/career/setup/deck` | Reviewed | 1280 at 05:23:40, 390 at 05:31:33 | dark | `/career/setup/deck` · Build the six-card deck / Deck 0 of 6 slots filled | [routes/web.php:90](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L90) |
| `/career/setup/preflight` | Reviewed | 1280 at 05:23:43 | dark | `/career/setup/preflight` · Before the first turn / This run | [routes/web.php:98](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L98) |
| `/career/snapshot` | Reviewed | 1280 at 05:23:45 | dark | `/career/snapshot` · Start from existing snapshot / Record your position | [routes/web.php:109](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L109) |
| `/career/snapshot/setup` | Reviewed | 1280 at 05:20:20, 768 at 05:30:23, 390 at 05:31:15, 320 at 05:32:15 | dark | `/career/snapshot/setup` · Record an existing career / Scenario | [routes/web.php:110](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L110) |
| `/career/snapshot/review` | Reviewed | 1280 at 05:23:47 | dark | `/career/snapshot/review` · Review snapshot | [routes/web.php:115](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L115) |
| `/umamusume` | Reviewed | 1280 at 05:24:58 | dark | `/umamusume` · Umamusume catalog | [routes/web.php:118](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L118) |
| `/umamusume/{slug}` | Reviewed | 1280 at 05:21:00 | dark | `/umamusume/vodka` · Vodka ウオッカ / Base stats and growth | [routes/web.php:119](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L119) |
| `/skills` | Reviewed | 1280 at 05:25:04 | dark | `/skills` · Skill search | [routes/web.php:122](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L122) |
| `/skills/{skill}` | Reviewed | 1280 at 05:25:07 | dark | `/skills/370?from=%2Fdatabase%2Fskills` · #LookatCurren / The source's own engine data | [routes/web.php:123](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L123) |
| `/support-cards` | Reviewed | 1280 at 05:25:16 | dark | `/support-cards` · Every support card / The seven types | [routes/web.php:126](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L126) |
| `/support-cards/{card}` | Reviewed | 1280 at 05:25:19 | dark | `/support-cards/187` · Admire Vega [Aim for the Brightest] / Effects, as the source states them | [routes/web.php:127](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L127) |
| `/training-runs` | Reviewed | 1280 at 05:20:13 | dark | `/training-runs` · Training runs | [routes/web.php:129](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L129) |
| `/training-runs/create` | Reviewed | 1280 at 05:25:29 | dark | `/training-runs/create` · Create a career / Career | [routes/web.php:130](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L130) |
| `/training-runs/import` | Reviewed | 1280 at 05:25:26 | dark | `/training-runs/import` · Bring in a historical career / Source | [routes/web.php:134](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L134) |
| `/training-runs/{run}` | Redirect | 1280 at 05:23:10 | dark | `/training-runs/36/cockpit` after redirect · Rice Shower / Stats and state<br>`/training-runs/import` · Bring in a historical career / Source<br>`/training-runs/create` · Create a career / Career | [routes/web.php:140](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L140) |
| `/training-runs/{run}/cockpit` | Reviewed | 1280 at 05:20:50 | dark | `/training-runs/36/cockpit` · Rice Shower / Stats and state<br>`/training-runs/22/cockpit` · Vodka / This run is complete.<br>`/training-runs/39/cockpit` · Rice Shower / No turn recorded yet | [routes/web.php:147](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L147) |
| `/training-runs/{run}/settings` | Reviewed | 1280 at 05:21:02, 390 at 05:31:06 | dark | `/training-runs/36/settings` · Rice Shower / Run identity | [routes/web.php:154](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L154) |
| `/training-runs/{run}/team` | Reviewed | 1280 at 05:22:16 | dark | `/training-runs/36/team` · Edit team state / Team | [routes/web.php:160](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L160) |
| `/training-runs/{run}/scenario` | Reviewed | 1280 at 05:22:19 | dark | `/training-runs/36/scenario` · Scenario detail / RECORDED ON THIS RUN | [routes/web.php:166](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L166) |
| `/training-runs/{run}/races` | Reviewed | 1280 at 05:22:49 | dark | `/training-runs/36/races` · Enter a race, or skip the turn / Available this turn | [routes/web.php:173](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L173) |
| `/training-runs/{run}/races/planner` | Reviewed | 1280 at 05:23:01 | dark | `/training-runs/36/races/planner` · Plan the calendar / Deadlines | [routes/web.php:180](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L180) |
| `/training-runs/{run}/skills` | Reviewed | 1280 at 05:21:57 | dark | `/training-runs/36/skills` · Spend Skill Points / Skill Point budget | [routes/web.php:186](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L186) |
| `/training-runs/{run}/training` | Reviewed | 1280 at 05:22:36, 768 at 05:29:47 | dark | `/training-runs/36/training` · Pick this turn's action / Disciplines | [routes/web.php:192](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L192) |
| `/training-runs/{run}/inheritance` | Reviewed | 1280 at 05:23:03 | dark | `/training-runs/36/inheritance` · Choose what to inherit / LEGACY SELECTION | [routes/web.php:198](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L198) |
| `/training-runs/{run}/events` | Reviewed | 1280 at 05:22:00 | dark | `/training-runs/36/events` · A training event is asking / What the event shows | [routes/web.php:205](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L205) |
| `/training-runs/{run}/timeline` | Reviewed | 1280 at 05:21:07 | dark | `/training-runs/36/timeline` · Every logged turn, race and event / The record | [routes/web.php:212](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L212) |
| `/training-runs/{run}/result` | Reviewed | 1280 at 05:20:52, 768 at 05:29:40, 320 at 05:32:33 | dark | `/training-runs/22/result` · Vodka · URA Finale / How this career ended<br>`/training-runs/36/result` · Rice Shower · Unity Cup / This career is still running | [routes/web.php:218](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L218) |
| `/training-runs/{run}/delete` | Reviewed | 1280 at 05:21:04 | dark | `/training-runs/36/delete` · Rice Shower / This cannot be undone | [routes/web.php:225](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L225) |
| `/training-runs/{run}/export/{format}` | Download unverified | — | — | CSV and JSON doors in completed Result. | [routes/web.php:227](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L227) |
| `/training-runs/{run}/turns/{turn}/edit` | Reviewed | 1280 at 05:21:20 | dark | `/training-runs/36/turns/29/edit` · Turn 67 / Correct turn 67 by hand | [routes/web.php:235](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L235) |
| `/training-runs/{run}/deck` | Reviewed | 1280 at 05:22:14 | dark | `/training-runs/36/deck` · This run's six slots / The six slots | [routes/web.php:244](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L244) |
| `/review` | Reviewed | 1280 at 05:20:15, 768 at 05:30:06, 320 at 05:32:28 | dark | `/review` · Confirm or reject matches / Pending cases | [routes/web.php:250](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L250) |
| `/review/{candidate}` | Reviewed | 1280 at 05:20:57 | dark | `/review/304` · Is this the right trainee? / Source | [routes/web.php:253](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L253) |
| `/legacy` | Reviewed | 1280 at 05:23:49 | dark | `/legacy` · Build and compare inheritance pairs / Saved legacies | [routes/web.php:268](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L268) |
| `/legacy/compare` | Reviewed | 1280 at 05:23:51 | dark | `/legacy/compare` · Compare two legacies / Compare recorded selections | [routes/web.php:269](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L269) |
| `/legacy/{run}` | Reviewed | 1280 at 05:23:51 | dark | `/legacy/compare` · Compare two legacies / Compare recorded selections<br>`/legacy/39` · Legacy builder / Rice Shower ライスシャワー | [routes/web.php:270](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L270) |
| `/veterans` | Reviewed | 1280 at 05:20:34 | dark | `/veterans` · Veteran library / Spark grades | [routes/web.php:287](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L287) |
| `/veterans/compare` | Reviewed | 1280 at 05:25:45 | dark | `/veterans/compare` · Compare Veterans / Nothing selected | [routes/web.php:288](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L288) |
| `/veterans/{veteran}` | Reviewed | 1280 at 05:23:16 | dark | `/veterans/8` · Vodkaウオッカ / The Record<br>`/veterans/compare` · Compare Veterans / Nothing selected<br>`/veterans/transfers` · Transfer Requests | [routes/web.php:289](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L289) |
| `/veterans/transfers` | Reviewed | 1280 at 05:25:46 | dark | `/veterans/transfers` · Transfer Requests | [routes/web.php:299](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L299) |
| `/training-runs/{run}/veteran` | Reviewed | 1280 at 05:23:14 | dark | `/training-runs/22/veteran` · Save Veteran / What this career recorded | [routes/web.php:318](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L318) |
| `/preferences` | Reviewed | 1280 at 05:25:41 | dark | `/preferences` · Preferences / Layout width | [routes/web.php:327](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L327) |
| `/help` | Reviewed | 1280 at 05:24:38, 768 at 05:30:11, 320 at 05:32:36 | dark | `/help` · How this works / JUMP TO A QUESTION | [routes/web.php:334](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L334) |
| `/preferences/export` | Download unverified | — | — | Export door in Preferences. | [routes/web.php:339](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L339) |
| `/database` | Reviewed | 1280 at 05:24:17 | dark | `/database` · Reference data | [routes/web.php:354](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L354) |
| `/database/trainees` | Reviewed | 1280 at 05:24:19 | dark | `/database/trainees` · Trainees / Umamusume catalog | [routes/web.php:355](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L355) |
| `/database/supports` | Reviewed | 1280 at 05:24:22 | dark | `/database/supports` · Support Cards / Support card catalog | [routes/web.php:356](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L356) |
| `/database/skills` | Reviewed | 1280 at 05:24:25 | dark | `/database/skills` · Skills / Skill catalog | [routes/web.php:357](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L357) |
| `/database/races` | Reviewed | 1280 at 05:24:27, 390 at 05:31:24 | dark | `/database/races` · Race catalog | [routes/web.php:358](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L358) |
| `/database/races/{id}` | Reviewed | 1280 at 05:25:21 | dark | `/database/races/1` · Junior Make Debut / Identity<br>`/database/races/3` · Chukyo Junior Stakes / Identity | [routes/web.php:359](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L359) |
| `/database/scenarios` | Reviewed | 1280 at 05:24:30 | dark | `/database/scenarios` · Scenario matrix / Caps | [routes/web.php:360](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L360) |
| `/database/events` | Reviewed | 1280 at 05:24:32 | dark | `/database/events` · Event catalog / Where events come from | [routes/web.php:363](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L363) |
| `/database/shop-items` | Reviewed | 1280 at 05:24:34 | dark | `/database/shop-items` · Pro Shop catalogue / Trackblazer Pro Shop | [routes/web.php:364](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L364) |
| `/database/sparks` | Reviewed | 1280 at 05:24:36 | dark | `/database/sparks` · Spark families and grades / Families | [routes/web.php:365](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L365) |
| `/database/sources` | Reviewed | 1280 at 05:20:17, 768 at 05:30:01, 320 at 05:32:11 | dark | `/database/sources` · Where every figure came from / Tiers | [routes/web.php:368](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L368) |
| `/up` | Health | 1280 at 05:24:41 | Framework | `/up` · Application up | [bootstrap/app.php:21](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/bootstrap/app.php#L21) |

**Additional error state.** The historical malformed URL `/training-runs/36/cockpit/cockpit` rendered “That page does not exist” at 05:28:01, dark, 1280 px. Existing safe links now reach the canonical Cockpit on the desktop asset. The former malformed destination was used only to observe error recovery, and contributes no registered template to the total.

## Safe interaction and accessibility evidence

| Check | Observed result |
| --- | --- |
| Career exits at 1280 px | Settings Back, Turn Edit Back and Delete Cancel actually reached the same active Cockpit at 05:26:13, 05:26:19 and 05:26:33. Delete stayed unsubmitted. |
| Dashboard row at 1280 px | “Open source career” opened completed Vodka Cockpit at 05:26:42. The accessible column also named the source career. |
| Retired filter at 1280 px | Selected Retired and applied the read filter. Its actual query returned “No careers match these filters” with a Clear filters action at 05:26:48. No Retired record was available for navigation. |
| Review filter at 1280 px | Selected Race and applied the GET filter. It reported zero. Candidate 304 still rendered trainee framing and an enabled Confirm option and Create control. Exact evidence in [TR-01](01-findings.md#tr-01-high-race-origin-work-hidden-by-the-race-filter). |
| Ledger at 1280 px | Kind Transcribed returned 5 of 18. Independent Tier S returned 1 of 18. Combining Transcribed with No fetch recorded removed the controls. The Races conflict round-trip rendered an unmatched return fragment. |
| Snapshot at 768 px | Typing Vodka opened a two-option listbox. ArrowDown set an active descendant. Escape closed the listbox and retained focus in the same editable field at 05:30:29. No choice was saved. |
| Wizard Deck at 390 px | Slot 1 opened a named dialog, focus landed on Close at 05:31:36, and Escape closed it. Focus returned to “Choose a card for Slot 1” at 05:31:38. |
| Help at 320 px | Enter on the source question opened `/help#help-source` and identified the “Where the numbers come from” target at 05:32:39. A sampled heading was white on RGB 18,34,79, calculated at 15.36:1. The main heading's effective background was unmeasured. |
| More at 320 px | Enter showed six destinations. A second Enter closed it with focus still on the disclosure and a computed 2 px outline. The target measured 54.6 × 44 CSS px at 05:32:46. This single sample does not establish all target spacing or occlusion. |

## Verification limits and state

- Available career states included a no-turn active run, an active run with recorded turn 67, an active Result refusal and a completed career. Retired navigation, transferred/re-bank refusal, recorded consecutive-race value or known zero, and other scenario/data variants remain unverified.
- Wizard GETs can mark steps visited in the session. No draft selection or scenario change was committed. Upstream-change notice, selected-form Preflight, alternate warning flows, independent training, and creation remain unverified.
- Snapshot Review rendered the absent-draft state, with Not provided values and Record disabled. Setup showed separate Unknown controls and a bulk action. A transient Speed zero field was entered during the final check, but the bulk interaction timed out and the preservation result is unverified. No Snapshot was submitted. Populated form refusal, linked errors and retained fields remain unverified.
- Empty comparison routes and transfer list rendered. Populated comparison and transfer states, import preview, saved feedback, 419/500 responses, artificial loading states and all app writes were outside the verified state set.
- Chrome interrupted the final checks. The viewport had been returned to 1280 × 900 on the audit tab before interruption, but the override reset could not be verified. The audit used an ephemeral tab. No alternate themes were selected.
- No application code, tests, configuration, persisted records or approved decisions were intentionally modified. No application mutation, setup, suite, build, seeding, restart or Git write was performed. No site-wide WCAG verdict is made.

## External criteria

[WCAG 2.2](https://www.w3.org/TR/WCAG22/) and [W3C reflow](https://www.w3.org/WAI/WCAG22/Understanding/reflow) informed headings, link purpose, contrast and responsive checks. [Focus Not Obscured](https://www.w3.org/WAI/WCAG22/Understanding/focus-not-obscured-minimum) and [Target Size](https://www.w3.org/WAI/WCAG22/Understanding/target-size-minimum) were applied to scoped samples with their criteria and exceptions. [Error Identification](https://www.w3.org/WAI/WCAG22/Understanding/error-identification), [GOV.UK error summary](https://design-system.service.gov.uk/components/error-summary/) and [validation guidance](https://design-system.service.gov.uk/patterns/validation/) inform future safe validation verification. [web.dev responsive design](https://web.dev/articles/responsive-web-design-basics) and [Playwright accessibility testing](https://playwright.dev/docs/accessibility-testing) support measuring actual rendered layouts and retaining human checks.

## Document set note (added 2026-11-22, after landing)

This directory currently holds a mixed set, and the mismatch is recorded here rather than resolved by
inventing the missing files:

| File | Date it was written for | Status |
| --- | --- | --- |
| `00-coverage.md` | 22 November | Current. This is the audit that produced TR-01. |
| `01-findings.md` | 21 November | Superseded. Carries TD-01 to TD-08, not TR-01. |
| `02-plan-reconciliation.md` | 21 November | Superseded. Carries TD-01 to TD-08. |

Consequences a reader must know before trusting a cross-reference:

- The `[TR-01](01-findings.md#tr-01-...)` link in this file has no target. The 22 November findings
  document that defines TR-01 was not present in the repository when this note was written, and was
  not reconstructed here, because inventing reproduction evidence is worse than stating its absence.
- The findings this file's own evidence supports are recorded in the table below, taken from the
  verified observations in "Safe interaction and accessibility evidence" above.

| ID | Finding as measured on 22 November | Where it is recorded |
| --- | --- | --- |
| TR-01 | Race filter returned zero while 304 race-origin candidates existed; candidate 304 still
rendered trainee framing and an enabled Confirm control | This file, Review filter row, plus the
frontend remediation in `Review/Index.vue` / `Review/Detail.vue` |
| TR-02 | Source Ledger filters disappeared when a filter produced zero matches | This file, Ledger row;
fixed by lifting the controls out of the results-gated card |
| TR-03 | Race conflict backlink reached an unmatched fragment | This file, Ledger row; fixed by moving
the fragment id from the question anchor to the row |
| TR-04 | Training Energy explanation contradicted its own absence presentation | Not in the 21 November
set (TD-06 covered a different sentence); fixed in `TrainingDetail.vue` |

The numbering above maps the 22 November observations to stable ids for the remediation record. It
does not replace the superseded 21 November findings, which remain accurate for the routes they cover.

---
**Intended repository location** `docs/audits/2026-11-22-frontend-browser-review/`, with a row in `docs/audits/INDEX.md`. The delivered copy includes the candidate index entry. The live repository was unavailable for writes.

---

## Re-validation — 2026-11-23 (modernization pass)

**Repository HEAD at re-validation:** `3600741e`
**Document baseline:** 2026-11-22

### Coverage status at current HEAD

Coverage claims rest on source-static inspection; no local server available for live re-measurement. All route counts and template counts match baseline values from `routes/web.php` inspection.

### Limitations

No local server available; module hash `spa-CvVyd8Ax.js` captured at 05:32:08 on 22 Nov 2026 cannot be correlated with current HEAD (`3600741e`) without a running build.
