# Skills Mechanics and Skills Surface

## Provenance

This master file embeds the complete text of all 7 Group B source files from `docs/design-research/`, listed below in the order they appear:

1. `skill-facts-2026-10-01.md`
2. `skills-mechanics-audit-2026-10-01.md`
3. `skills-mechanics-audit-verification-2026-10-01.md`
4. `SKILLS-GAPS.md`
5. `skills-section-review-2026-10-01.md`
6. `skills-section-phase-b2-2026-10-01.md`
7. `skills-section-reconciliation-2026-10-01.md`

Internal headings have been demoted by one level. Large files are split into numbered `####` subsections preserving original numbering. All tables, code blocks, blockquotes, checkboxes, checklists, and erratum notes are preserved verbatim.

---

## skill-facts-2026-10-01.md

## Skill facts, Global English set (2026-10-01)

**Type of document:** extracted facts, no recommendations. Phase B1 of the skill-reasoning work.

**Source:** `database/seeders/data/skills.609afe88.json`, the GameTora skills export, read only. No network source was consulted and no source search was run. The `best_for` question is closed by `docs/design-research/slice-5-phase-a-best-for-2026-10-01.md` and was not reopened here.

**Coverage predicate:** a row is Global when the export carries no `unreleased` key for it. That is the rule at `docs/adr/0011-skills-reference-import.md:34`: "Array of server codes. Every populated value contains en; absence is the Global statement."

### Provenance warning on the source file

`skills.609afe88.json` is **not tracked by git**. `git ls-files --error-unmatch` returns no for it, and `git check-ignore -v` returns no rule either, so it was simply never added. The same is true of `characters.c6676539.json` and `gametora-characters.e9e9ee6d.json` in the same directory, while `support-cards.88dea522.json`, `support_effects.ca447e53.json`, `races.json`, `race_instances.json`, `ura-races.json` and `race-tier-labels-2026-09-29.json` are tracked. The dispatch that ordered this pass described the export as the committed GameTora source. That is false of the file this extraction reads, so every number below is reproducible only against a working tree that still holds it. This is the same durability gap already recorded for `.scratch-uma/`.

### Method

Format chosen: **Markdown, one table row per skill**, not JSON. Reason: the owner has to read this before Phase B2 can be scoped, and the value is the pairing of a client string with its activation predicate, which reads well in a table and badly as nested objects. The identifiers a machine would need (`id`, `name_en`, `rarity`, `cost`, `char`) are plain columns, so a JSON view can be generated from this file mechanically. The reverse loses the reading.

Field provenance. Every fact cites the export field it came from, and nothing is inferred:

| Fact | Export field | Citation for its meaning |
|---|---|---|
| What it does, client wording | `desc_en`, `endesc`, `jpdesc` | ADR-0011:31 fixes `name_en` as the client string and `enname` as a third-party rendering. The same rule is applied to descriptions, so `enname` is never used |
| When it fires | `condition_groups[].condition`, `.precondition`, `.effects`, `.base_time` | slice-5-phase-a:64 names the field the activation DSL. Reproduced verbatim: no tracked source defines the individual atoms |
| Engine class | `type` | ADR-0011:35 calls it an array of gate keys and names several tokens, but defines none of them. Meanings are marked unverified |
| What it costs | `cost` | ADR-0011:34: SP cost, present on rarity 1 and 2 only, absent on 3 to 6 |
| Uniqueness | `char`, `rarity` | ADR-0011:36: the trainee cards whose unique skill this is |
| Availability | absence of `unreleased` | ADR-0011:34 as quoted. `release_status` is an application enum at `app/Enums/ReleaseStatus.php:16`, not an export field |
| Style eligibility | `running_style==N` inside the DSL | docs/UMAMUSUME_REFERENCE.md:231-234 maps 1 to Front Runner, 2 to Pace Chaser, 3 to Late Surger, 4 to End Closer |

Decoding rules applied, and only these:

1. A `running_style==N` atom is rendered as the client label for N from the reference table. The four labels are the shipped ones; the literal export renderings such as Runner, Leader, Betweener and Chaser are not used as labels, per UMAMUSUME_REFERENCE.md:229-234.
2. Every other atom is emitted exactly as stored. No glossary exists for them, so a paraphrase would be an invented fact.
3. A missing field prints as `absent` or `none`. It is never filled by inference, and the totals are published so a reader can see how much is missing.

### Coverage, with the canary for each number

Every count below came from the same script that wrote this file, and each is paired with a positive control. Per the consolidation §6 finding, no zero is reported without proof that the pattern can fire.

| Measure | Value | Canary, and what it proves |
|---|---|---|
| Records in the export | 1910 | The `unreleased` bucket sum is 1910, so every record lands in exactly one bucket and none is double counted |
| Global rows extracted (no `unreleased` key) | 623 | Buckets: ABSENT=623, en=972, zh_tw,en=155, ko,zh_tw,en=160. They sum to 1910, so the figure is not a filter artifact |
| `name_en` present | 623 of 623 | Zero absences, so no row in the name column is blank for an invisible reason |
| `desc_en` present | 623 of 623 | Zero absences. Field-wide it exists on 985 of 1910, so the Global subset holds all of them |
| `endesc` and `jpdesc` present | 623 and 623 of 623 | Both exist on all 1910 records, so any absence here would be a bug rather than a gap |
| `cost` present | 476 of 623 | Positive control by rarity: rarity 1 329/344; rarity 2 147/152; rarity 3 0/17; rarity 4 0/17; rarity 5 0/93. The absences are the documented shape, not a parse failure |
| `char` present, so a trainee unique skill | 438 of 623 | Both an absent key and an empty array were observed, and both print as none |
| `condition_groups` non-empty | 623 of 623 | The empty case is 0, and the non-empty case matched on every row, so the zero is meaningful |
| Skills gated on exactly one style | 121 of 623 | Per label: Front Runner 26, Pace Chaser 35, Late Surger 33, End Closer 27. Multi-style gating occurs on 2 skills, which is the case a single-label decode would otherwise hide |

One reconciliation to flag. UMAMUSUME_REFERENCE.md:231-234 publishes gated counts of 107, 220, 166 and 115. Measuring that same definition across all 1910 records gives 107, 220, 166, 115, which reproduces the reference exactly. Measuring it across the Global subset gives 26, 35, 33, 27. The reference table therefore counts the whole export and does not say so. Any UI that shows those counts next to a Global-only list is wrong.

### Tokens and codes, without invented meanings

Type tokens in the Global set, with counts: `nac` 358 · `l_1` 123 · `l_2` 69 · `dbf` 53 · `cor` 49 · `f_c` 40 · `ldr` 37 · `med` 36 · `str` 35 · `btw` 35 · `dir` 35 · `lng` 34 · `l_3` 33 · `l_0` 33 · `cha` 27 · `mil` 27 · `run` 26 · `sho` 26 · `f_s` 20 · `slo` 14 · `tur` 4. ADR-0011:35 confirms the field is a gate-key array and names several tokens, but no tracked source defines what any individual token means. Recording the tokens is a fact. Translating `nac` into a category would not be. Status: **token meanings UNVERIFIED**.

Effect codes in `condition_groups[].effects[].type`, by occurrence: 27=299, 31=120, 9=110, 1=57, 2=57, 21=30, 5=23, 3=22, 8=22, 28=22, 4=18, 13=4, 22=4, 10=3, 14=2, 35=2, 6=1, 29=1, 37=1, 501=1, 502=1, 503=1. No tracked source decodes these numbers either.

Condition atoms seen: accumulatetime, activate_count_all, activate_count_end_after, activate_count_heal, activate_count_later_half, activate_count_middle, activate_count_start, all_corner_random, always, base_power, bashin_diff_behind, bashin_diff_infront, behind_near_lane_time, behind_near_lane_time_set1, blocked_front, blocked_front_continuetime, blocked_side_continuetime, change_order_onetime, change_order_up_end_after, change_order_up_finalcorner_after, compete_fight_count, corner, course_distance, distance_diff_rate, distance_diff_top, distance_rate, distance_rate_after_random, distance_type, down_slope_random, grade, ground_condition, ground_type, hp_per, infront_near_lane_time, is_activate_any_skill, is_activate_other_skill_detail, is_badstart, is_basis_distance, is_behind_in, is_dirtgrade, is_exist_chara_id, is_finalcorner, is_finalcorner_laterhalf, is_finalcorner_random, is_last_straight, is_last_straight_onetime, is_lastspurt, is_move_lane, is_other_character_activate_advantage_skill, is_overtake, is_surrounded, is_temptation, is_tight_track, lane_type, last_straight_random, lastspurt, motivation, near_count, order, order_rate, order_rate_in20_continue, order_rate_in50_continue, order_rate_in80_continue, order_rate_out20_continue, order_rate_out40_continue, order_rate_out50_continue, order_rate_out70_continue, overtake_target_no_order_up_time, overtake_target_time, phase, phase_firsthalf_random, phase_laterhalf_random, phase_random, popularity, post_number, random_lot, random_lot_shared, remain_distance, remain_distance_viewer_id, rotation, running_style, running_style_count_nige_otherself, running_style_count_oikomi_otherself, running_style_count_same, running_style_count_same_rate, running_style_count_sashi_otherself, running_style_count_senko_otherself, running_style_equal_popularity_one, running_style_temptation_opponent_count_nige, running_style_temptation_opponent_count_oikomi, running_style_temptation_opponent_count_sashi, running_style_temptation_opponent_count_senko, same_skill_horse_count, season, slope, straight_front_type, straight_random, temptation_count, temptation_opponent_count_behind, temptation_opponent_count_infront, time, track_id, up_slope_random, weather. Printed as found, so that writing a glossary later is a bounded job against a real list instead of a guess.

Rarity values in the Global set: 1=344, 2=152, 3=17, 4=17, 5=93. ADR-0011:33 lists six values across the whole export but names no tier, so the numbers stay numbers.

### Worked example, one skill in full

Export id 201241, `Front Runner Straightaways ◎`.

- What it does, client wording (`desc_en`): Moderately increase velocity on a straight. (Front Runner)
- Second client-side rendering in the export (`endesc`): Runner・At a random point on a random straight, your speed will slightly increase
- Source text (`jpdesc`): いずれかの直線のどこかで速度が少し上がる＜作戦・逃げ＞
- Engine class (`type`): run, str. Token meanings unverified, see above.
- Cost (`cost`): 140 SP.
- Uniqueness: no `char` key, so it is not a trainee's unique skill; `rarity` 1.
- Availability: no `unreleased` key, so Global-released under the ADR-0011 rule.
- Style eligibility: fires only for Front Runner, from `running_style==1`.
- When it fires, verbatim DSL: condition `running_style==1&straight_random==1`, precondition `(none)`, effects [{"type":27,"value":2500}], base_time 30000

Read as advice this would say take it when you run a given style. It says nothing of the kind. It gives the client's own description, the price, who may use it, and the exact predicate the engine evaluates. Ranking one skill above another is a recommendation and is out of scope here.

### The table

When it fires holds the activation DSL verbatim, one entry per condition group, groups separated by `++`. Atoms are unexplained because no glossary exists. Style eligibility is decoded from `running_style==N` against UMAMUSUME_REFERENCE.md:231-234. Pipe characters inside client text are replaced with a slash so the table stays parseable; no other character was altered.

| id | name_en (client) | type | cost SP | rarity | unique to (card ids) | style eligibility | when it fires, verbatim |
|---|---|---|---|---|---|---|---|
| 110031 | Certain Victory | nac | absent | 5 | 100302 | not style gated | cond: is_last_straight==1 / pre: is_finalcorner==1&is_overtake==1&order<=5&order_rate<=50&overtake_target_no_order_up_time>=2 / effects: type 27 value 4500 / base_time: 50000 |
| 110131 | Legacy of the Strong | l_2, l_3, f_s, f_c, nac | absent | 5 | 101302 | not style gated | cond: phase>=2&is_finalcorner==1&order<=4&overtake_target_time>=1 / pre: (none) / effects: type 27 value 3500 / base_time: 60000 |
| 10071 | Warning Shot! | nac | absent | 3 | 100701 | not style gated | cond: distance_rate>=50&distance_rate<=60&order_rate>50 / pre: (none) / effects: type 27 value 1500 / base_time: 60000 |
| 10081 | Xceleration | nac | absent | 3 | 100801 | not style gated | cond: order>=3&order_rate<=50&remain_distance<=200&bashin_diff_infront<=1@order>=3&order_rate<=50&remain_distance<=200&bashin_diff_behind<=1 / pre: (none) / effects: type 27 value 2500 / base_time: 50000 |
| 10091 | Red Ace | nac | absent | 3 | 100901 | not style gated | cond: distance_rate>=50&order==1&bashin_diff_behind<=1@distance_rate>=50&order==2&is_overtake==1 / pre: (none) / effects: type 27 value 1500; type 31 value 2000 / base_time: 50000 |
| 10111 | Focused Mind | nac | absent | 3 | 101101 | not style gated | cond: is_last_straight==1&change_order_onetime<0&order>=3 / pre: (none) / effects: type 27 value 2500 / base_time: 50000 |
| 10141 | Corazón ☆ Ardiente | nac | absent | 3 | 101401 | not style gated | cond: is_last_straight==1&hp_per>=30&order<=2 / pre: (none) / effects: type 27 value 1500; type 31 value 2000 / base_time: 50000 |
| 10181 | Empress's Pride | f_c, cor, nac | absent | 3 | 101801 | not style gated | cond: is_finalcorner==1&corner!=0&order>=3&change_order_onetime<0 / pre: (none) / effects: type 27 value 2500 / base_time: 50000 |
| 10241 | 1st Place Kiss☆ | f_s, f_c, nac | absent | 3 | 102401 | not style gated | cond: is_finalcorner==1&blocked_side_continuetime>=2&order<=3 / pre: (none) / effects: type 27 value 1500; type 31 value 2000 / base_time: 50000 |
| 10271 | Feel the Burn! | l_2, l_3, cor, nac | absent | 3 | 102701 | not style gated | cond: phase>=2&corner!=0&order_rate>=65&order_rate<=70 / pre: (none) / effects: type 31 value 3000 / base_time: 40000 |
| 10321 | Introduction to Physiology | cor, nac | absent | 3 | 103201 | not style gated | cond: distance_rate>=50&corner!=0&order>=3&order_rate<=40 / pre: (none) / effects: type 9 value 350; type 27 value 1500 / base_time: 40000 |
| 10351 | V Is for Victory! | nac | absent | 3 | 103501 | not style gated | cond: is_last_straight==1&order<=5 / pre: is_finalcorner==1&blocked_side_continuetime>=2 / effects: type 27 value 2500 / base_time: 50000 |
| 10411 | Class Rep + Speed = Bakushin | nac | absent | 3 | 104101 | not style gated | cond: distance_rate>=50&order<=3&blocked_side_continuetime>=2 / pre: (none) / effects: type 27 value 2500 / base_time: 50000 |
| 10451 | Clear Heart | l_1, nac | absent | 3 | 104501 | not style gated | cond: phase_random==1&order>=2&order_rate<=40 / pre: (none) / effects: type 9 value 550 / base_time: 0 |
| 10521 | Super-Duper Stoked | f_c, cor, nac | absent | 3 | 105201 | not style gated | cond: is_finalcorner==1&corner!=0&order_rate>50&near_count>=1 / pre: (none) / effects: type 9 value 350 / base_time: 0 |
| 10561 | Luck Be with Me! | l_2, l_3, nac | absent | 3 | 105601 | not style gated | cond: phase>=2&order>=3&blocked_front==1 / pre: (none) / effects: type 27 value 2500; type 31 value 1000 / base_time: 50000 |
| 10601 | I Can Win Sometimes, Right? | l_2, l_3, nac | absent | 3 | 106001 | not style gated | cond: phase>=2&order==3&bashin_diff_behind<=1 / pre: (none) / effects: type 27 value 2500 / base_time: 50000 |
| 10611 | Call Me King | nac | absent | 3 | 106101 | not style gated | cond: temptation_count==0&remain_distance<=201&remain_distance>=199&order>=4&order_rate<=70 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 100011 | Shooting Star | l_2, l_3, nac | absent | 5 | 100101 | not style gated | cond: phase>=2&order>=1&order_rate<=70&change_order_onetime<0 / pre: (none) / effects: type 22 value 3500; type 31 value 1000 / base_time: 60000 |
| 100021 | The View from the Lead Is Mine! | nac, l_3 | absent | 5 | 100201 | not style gated | cond: distance_rate>=50&order==1 / pre: (none) / effects: type 27 value 3500 / base_time: 50000  ++  cond: is_activate_other_skill_detail==1&phase==3&order==1&bashin_diff_behind>=1 / pre: (none) / effects: type 27 value 1500 / base_time: 40000 |
| 100031 | Sky-High Teio Step | ldr, nac | absent | 5 | 100301 | Pace Chaser (2) | cond: is_last_straight==1&running_style==2&course_distance==2400 / pre: phase>=2&order_rate<=50&bashin_diff_infront<=1&is_overtake==1 / effects: type 27 value 4500; type 27 value 500 / base_time: 50000  ++  cond: is_last_straight==1 / pre: phase>=2&order_rate<=50&bashin_diff_infront<=1&is_overtake==1 / effects: type 27 value 4500 / base_time: 50000 |
| 100041 | Red Shift/LP1211-M | f_s, f_c, nac | absent | 5 | 100401 | not style gated | cond: is_finalcorner==1&order<=5&order_rate<=50 / pre: (none) / effects: type 31 value 4000 / base_time: 40000 |
| 100061 | Triumphant Pulse | nac | absent | 5 | 100601 | not style gated | cond: order>=2&order<=5&order_rate<=50&remain_distance<=201&remain_distance>=199 / pre: (none) / effects: type 27 value 4500 / base_time: 50000 |
| 100071 | Anchors Aweigh! | nac | absent | 4 | 100701 | not style gated | cond: distance_rate>=50&distance_rate<=60&order_rate>50 / pre: (none) / effects: type 27 value 2500 / base_time: 60000 |
| 100081 | Cut and Drive! | nac | absent | 4 | 100801 | not style gated | cond: order>=3&order_rate<=50&remain_distance<=200&bashin_diff_infront<=1@order>=3&order_rate<=50&remain_distance<=200&bashin_diff_behind<=1 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 100091 | Resplendent Red Ace | nac | absent | 4 | 100901 | not style gated | cond: distance_rate>=50&order==1&bashin_diff_behind<=1@distance_rate>=50&order==2&is_overtake==1 / pre: (none) / effects: type 27 value 2500; type 31 value 3000 / base_time: 50000 |
| 100101 | Shooting for Victory! | f_c, cor, nac | absent | 5 | 101001 | not style gated | cond: is_finalcorner_laterhalf==1&corner!=0&order>=3&order_rate<=40 / pre: (none) / effects: type 31 value 4000 / base_time: 50000 |
| 100111 | Where There's a Will, There's a Way | nac | absent | 4 | 101101 | not style gated | cond: is_last_straight==1&change_order_onetime<0&order>=3 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 100131 | The Duty of Dignity Calls | f_c, cor, lng, nac | absent | 5 | 101301 | not style gated | cond: is_finalcorner==1&corner!=0&distance_diff_rate<=30&distance_type==4&lastspurt==2 / pre: (none) / effects: type 27 value 4500 / base_time: 50000  ++  cond: is_finalcorner==1&corner!=0&distance_diff_rate<=30 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 100141 | Victoria por plancha ☆ | nac | absent | 4 | 101401 | not style gated | cond: is_last_straight==1&order_rate<=40&course_distance==2400&lastspurt==2 / pre: (none) / effects: type 27 value 5500; type 9 value -400 / base_time: 50000  ++  cond: is_last_straight==1&order_rate<=40 / pre: (none) / effects: type 27 value 2500; type 31 value 3000 / base_time: 50000 |
| 100151 | This Dance Is for Vittoria! | f_s, f_c, nac | absent | 5 | 101501 | not style gated | cond: is_finalcorner==1&bashin_diff_behind<=1&order<=4@is_finalcorner==1&bashin_diff_infront<=1&order<=4 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 100171 | Behold Thine Emperor's Divine Might | nac | absent | 5 | 101701 | not style gated | cond: is_last_straight==1&change_order_up_end_after>=3 / pre: (none) / effects: type 27 value 4500 / base_time: 50000 |
| 100181 | Blazing Pride | f_c, cor, nac | absent | 4 | 101801 | not style gated | cond: is_finalcorner==1&corner!=0&order>=3&change_order_onetime<0 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 100231 | ∴win Q.E.D. | l_2, l_3, f_s, f_c, nac | absent | 5 | 102301 | not style gated | cond: phase>=2&is_finalcorner==1&order<=4&temptation_count==0 / pre: (none) / effects: type 27 value 4500 / base_time: 50000  ++  cond: phase>=2&is_finalcorner==1&order<=4 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 100241 | Flashy☆Landing | f_s, f_c, nac | absent | 4 | 102401 | not style gated | cond: is_finalcorner==1&blocked_side_continuetime>=2&order<=3 / pre: (none) / effects: type 27 value 2500; type 31 value 3000 / base_time: 50000 |
| 100261 | G00 1st. F∞; | nac | absent | 5 | 102601 | not style gated | cond: is_badstart==0&order<=3&is_last_straight==1&order_rate_in20_continue==1 / pre: (none) / effects: type 27 value 4500 / base_time: 50000  ++  cond: is_badstart==0&order<=3&is_last_straight==1 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 100271 | Let's Pump Some Iron! | l_2, l_3, cor, nac | absent | 4 | 102701 | not style gated | cond: phase>=2&corner!=0&order_rate>=65&order_rate<=70 / pre: (none) / effects: type 31 value 4000 / base_time: 40000 |
| 100301 | Blue Rose Closer | nac | absent | 5 | 103001 | not style gated | cond: is_last_straight==1 / pre: phase>=2&order<=4&change_order_onetime<0 / effects: type 27 value 3500 / base_time: 50000 |
| 100321 | U=ma2 | cor, nac | absent | 4 | 103201 | not style gated | cond: distance_rate>=50&corner!=0&order>=3&order_rate<=40 / pre: (none) / effects: type 9 value 550; type 27 value 2500 / base_time: 40000 |
| 100351 | Our Ticket to Win! | nac | absent | 4 | 103501 | not style gated | cond: is_last_straight==1&order<=5 / pre: is_finalcorner==1&blocked_side_continuetime>=2 / effects: type 27 value 3500 / base_time: 50000 |
| 100411 | Genius x Bakushin = Victory | nac | absent | 4 | 104101 | not style gated | cond: distance_rate>=50&order<=3&blocked_side_continuetime>=2 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 100451 | Pure Heart | l_1, nac | absent | 4 | 104501 | not style gated | cond: phase_random==1&order>=2&order_rate<=40 / pre: (none) / effects: type 9 value 750 / base_time: 0 |
| 100521 | Super-Duper Climax | f_c, cor, nac | absent | 4 | 105201 | not style gated | cond: is_finalcorner==1&corner!=0&order_rate>50&near_count>=1 / pre: (none) / effects: type 9 value 550 / base_time: 0 |
| 100561 | I See Victory in My Future! | l_2, l_3, nac | absent | 4 | 105601 | not style gated | cond: phase>=2&order>=3&blocked_front==1 / pre: (none) / effects: type 27 value 3500; type 31 value 1000 / base_time: 50000 |
| 100601 | Just a Little Farther! | l_2, l_3, nac | absent | 4 | 106001 | not style gated | cond: phase>=2&order==3&bashin_diff_behind<=1 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 100611 | Prideful King | nac | absent | 4 | 106101 | not style gated | cond: temptation_count==0&remain_distance<=201&remain_distance>=199&order>=4&order_rate<=70 / pre: (none) / effects: type 27 value 4500 / base_time: 50000 |
| 200011 | Right-Handed ◎ | nac | 110 | 1 | none | not style gated | cond: rotation==1 / pre: (none) / effects: type 1 value 600000 / base_time: -1 |
| 200012 | Right-Handed ○ | nac | 90 | 1 | 105602, 102201, 106701, 105802, 106703, 106803, 111901, 102303, 111101, 112701, 110902, 111301, 110602, 100503, 107403, 114901 | not style gated | cond: rotation==1 / pre: (none) / effects: type 1 value 400000 / base_time: -1 |
| 200013 | Right-Handed × | nac | 50 | 1 | none | not style gated | cond: rotation==1 / pre: (none) / effects: type 1 value -400000 / base_time: -1 |
| 200021 | Left-Handed ◎ | nac | 110 | 1 | none | not style gated | cond: rotation==2 / pre: (none) / effects: type 1 value 600000 / base_time: -1 |
| 200022 | Left-Handed ○ | nac | 90 | 1 | 100201, 107101, 109401, 109501, 112402 | not style gated | cond: rotation==2 / pre: (none) / effects: type 1 value 400000 / base_time: -1 |
| 200023 | Left-Handed × | nac | 50 | 1 | none | not style gated | cond: rotation==2 / pre: (none) / effects: type 1 value -400000 / base_time: -1 |
| 200031 | Tokyo Racecourse ◎ | nac | 110 | 1 | none | not style gated | cond: track_id==10006 / pre: (none) / effects: type 2 value 600000 / base_time: -1 |
| 200032 | Tokyo Racecourse ○ | nac | 90 | 1 | 103101, 107102, 108401, 110401, 107202, 103703 | not style gated | cond: track_id==10006 / pre: (none) / effects: type 2 value 400000 / base_time: -1 |
| 200033 | Tokyo Racecourse × | nac | 50 | 1 | none | not style gated | cond: track_id==10006 / pre: (none) / effects: type 2 value -400000 / base_time: -1 |
| 200041 | Nakayama Racecourse ◎ | nac | 110 | 1 | none | not style gated | cond: track_id==10005 / pre: (none) / effects: type 2 value 600000 / base_time: -1 |
| 200042 | Nakayama Racecourse ○ | nac | 90 | 1 | 108301 | not style gated | cond: track_id==10005 / pre: (none) / effects: type 2 value 400000 / base_time: -1 |
| 200043 | Nakayama Racecourse × | nac | 50 | 1 | none | not style gated | cond: track_id==10005 / pre: (none) / effects: type 2 value -400000 / base_time: -1 |
| 200051 | Hanshin Racecourse ◎ | nac | 110 | 1 | none | not style gated | cond: track_id==10009 / pre: (none) / effects: type 2 value 600000 / base_time: -1 |
| 200052 | Hanshin Racecourse ○ | nac | 90 | 1 | 104001, 105102 | not style gated | cond: track_id==10009 / pre: (none) / effects: type 2 value 400000 / base_time: -1 |
| 200053 | Hanshin Racecourse × | nac | 50 | 1 | none | not style gated | cond: track_id==10009 / pre: (none) / effects: type 2 value -400000 / base_time: -1 |
| 200061 | Kyoto Racecourse ◎ | nac | 110 | 1 | none | not style gated | cond: track_id==10008 / pre: (none) / effects: type 2 value 600000 / base_time: -1 |
| 200062 | Kyoto Racecourse ○ | nac | 90 | 1 | 105602, 105901, 105902, 110601, 103003 | not style gated | cond: track_id==10008 / pre: (none) / effects: type 2 value 400000 / base_time: -1 |
| 200063 | Kyoto Racecourse × | nac | 50 | 1 | none | not style gated | cond: track_id==10008 / pre: (none) / effects: type 2 value -400000 / base_time: -1 |
| 200071 | Chukyo Racecourse ◎ | nac | 110 | 1 | none | not style gated | cond: track_id==10007 / pre: (none) / effects: type 2 value 600000 / base_time: -1 |
| 200072 | Chukyo Racecourse ○ | nac | 90 | 1 | none | not style gated | cond: track_id==10007 / pre: (none) / effects: type 2 value 400000 / base_time: -1 |
| 200073 | Chukyo Racecourse × | nac | 50 | 1 | none | not style gated | cond: track_id==10007 / pre: (none) / effects: type 2 value -400000 / base_time: -1 |
| 200081 | Sapporo Racecourse ◎ | nac | 90 | 1 | none | not style gated | cond: track_id==10001 / pre: (none) / effects: type 2 value 600000 / base_time: -1 |
| 200082 | Sapporo Racecourse ○ | nac | 70 | 1 | none | not style gated | cond: track_id==10001 / pre: (none) / effects: type 2 value 400000 / base_time: -1 |
| 200083 | Sapporo Racecourse × | nac | 40 | 1 | none | not style gated | cond: track_id==10001 / pre: (none) / effects: type 2 value -400000 / base_time: -1 |
| 200091 | Hakodate Racecourse ◎ | nac | 90 | 1 | none | not style gated | cond: track_id==10002 / pre: (none) / effects: type 2 value 600000 / base_time: -1 |
| 200092 | Hakodate Racecourse ○ | nac | 70 | 1 | none | not style gated | cond: track_id==10002 / pre: (none) / effects: type 2 value 400000 / base_time: -1 |
| 200093 | Hakodate Racecourse × | nac | 40 | 1 | none | not style gated | cond: track_id==10002 / pre: (none) / effects: type 2 value -400000 / base_time: -1 |
| 200101 | Fukushima Racecourse ◎ | nac | 90 | 1 | none | not style gated | cond: track_id==10004 / pre: (none) / effects: type 2 value 600000 / base_time: -1 |
| 200102 | Fukushima Racecourse ○ | nac | 70 | 1 | none | not style gated | cond: track_id==10004 / pre: (none) / effects: type 2 value 400000 / base_time: -1 |
| 200103 | Fukushima Racecourse × | nac | 40 | 1 | none | not style gated | cond: track_id==10004 / pre: (none) / effects: type 2 value -400000 / base_time: -1 |
| 200111 | Niigata Racecourse ◎ | nac | 90 | 1 | none | not style gated | cond: track_id==10003 / pre: (none) / effects: type 2 value 600000 / base_time: -1 |
| 200112 | Niigata Racecourse ○ | nac | 70 | 1 | none | not style gated | cond: track_id==10003 / pre: (none) / effects: type 2 value 400000 / base_time: -1 |
| 200113 | Niigata Racecourse × | nac | 40 | 1 | none | not style gated | cond: track_id==10003 / pre: (none) / effects: type 2 value -400000 / base_time: -1 |
| 200121 | Kokura Racecourse ◎ | nac | 90 | 1 | none | not style gated | cond: track_id==10010 / pre: (none) / effects: type 2 value 600000 / base_time: -1 |
| 200122 | Kokura Racecourse ○ | nac | 70 | 1 | 106001 | not style gated | cond: track_id==10010 / pre: (none) / effects: type 2 value 400000 / base_time: -1 |
| 200123 | Kokura Racecourse × | nac | 40 | 1 | none | not style gated | cond: track_id==10010 / pre: (none) / effects: type 2 value -400000 / base_time: -1 |
| 200131 | Standard Distance ◎ | nac | 110 | 1 | none | not style gated | cond: is_basis_distance==1 / pre: (none) / effects: type 2 value 600000 / base_time: -1 |
| 200132 | Standard Distance ○ | nac | 90 | 1 | 103901, 103503, 110502, 103201 | not style gated | cond: is_basis_distance==1 / pre: (none) / effects: type 2 value 400000 / base_time: -1 |
| 200133 | Standard Distance × | nac | 50 | 1 | none | not style gated | cond: is_basis_distance==1 / pre: (none) / effects: type 2 value -400000 / base_time: -1 |
| 200141 | Non-Standard Distance ◎ | nac | 110 | 1 | none | not style gated | cond: is_basis_distance==0 / pre: (none) / effects: type 2 value 600000 / base_time: -1 |
| 200142 | Non-Standard Distance ○ | nac | 90 | 1 | 101501, 105801, 102502 | not style gated | cond: is_basis_distance==0 / pre: (none) / effects: type 2 value 400000 / base_time: -1 |
| 200143 | Non-Standard Distance × | nac | 50 | 1 | none | not style gated | cond: is_basis_distance==0 / pre: (none) / effects: type 2 value -400000 / base_time: -1 |
| 200151 | Firm Conditions ◎ | nac | 110 | 1 | none | not style gated | cond: ground_condition==1 / pre: (none) / effects: type 3 value 600000 / base_time: -1 |
| 200152 | Firm Conditions ○ | nac | 90 | 1 | 106701, 102202, 104201, 107701, 106003, 102802, 103501 | not style gated | cond: ground_condition==1 / pre: (none) / effects: type 3 value 400000 / base_time: -1 |
| 200153 | Firm Conditions × | nac | 50 | 1 | none | not style gated | cond: ground_condition==1 / pre: (none) / effects: type 3 value -400000 / base_time: -1 |
| 200161 | Wet Conditions ◎ | nac | 110 | 1 | none | not style gated | cond: ground_condition==2@ground_condition==3@ground_condition==4 / pre: (none) / effects: type 3 value 600000 / base_time: -1 |
| 200162 | Wet Conditions ○ | nac | 90 | 1 | 100101, 101901, 102301, 104601, 101002, 108701, 102102, 104301, 100703, 107402, 108801, 107002, 113701 | not style gated | cond: ground_condition==2@ground_condition==3@ground_condition==4 / pre: (none) / effects: type 3 value 400000 / base_time: -1 |
| 200163 | Wet Conditions × | nac | 50 | 1 | none | not style gated | cond: ground_condition==2@ground_condition==3@ground_condition==4 / pre: (none) / effects: type 3 value -400000 / base_time: -1 |
| 200171 | Spring Runner ◎ | nac | 110 | 1 | none | not style gated | cond: season==1@season==5 / pre: (none) / effects: type 1 value 600000 / base_time: -1 |
| 200172 | Spring Runner ○ | nac | 90 | 1 | 100302, 101301, 106901, 102002, 106102, 101602, 110501, 112701 | not style gated | cond: season==1@season==5 / pre: (none) / effects: type 1 value 400000 / base_time: -1 |
| 200173 | Spring Runner × | nac | 50 | 1 | none | not style gated | cond: season==1@season==5 / pre: (none) / effects: type 1 value -400000 / base_time: -1 |
| 200181 | Summer Runner ◎ | nac | 110 | 1 | none | not style gated | cond: season==2 / pre: (none) / effects: type 1 value 600000 / base_time: -1 |
| 200182 | Summer Runner ○ | nac | 90 | 1 | 103202, 106301 | not style gated | cond: season==2 / pre: (none) / effects: type 1 value 400000 / base_time: -1 |
| 200183 | Summer Runner × | nac | 50 | 1 | none | not style gated | cond: season==2 / pre: (none) / effects: type 1 value -400000 / base_time: -1 |
| 200191 | Fall Runner ◎ | nac | 110 | 1 | none | not style gated | cond: season==3 / pre: (none) / effects: type 1 value 600000 / base_time: -1 |
| 200192 | Fall Runner ○ | nac | 90 | 1 | 101302, 101702, 102201, 102202, 104701, 104702, 112101, 109302 | not style gated | cond: season==3 / pre: (none) / effects: type 1 value 400000 / base_time: -1 |
| 200193 | Fall Runner × | nac | 50 | 1 | none | not style gated | cond: season==3 / pre: (none) / effects: type 1 value -400000 / base_time: -1 |
| 200201 | Winter Runner ◎ | nac | 110 | 1 | none | not style gated | cond: season==4 / pre: (none) / effects: type 1 value 600000 / base_time: -1 |
| 200202 | Winter Runner ○ | nac | 90 | 1 | 101502, 103401, 107402, 101103, 107602 | not style gated | cond: season==4 / pre: (none) / effects: type 1 value 400000 / base_time: -1 |
| 200203 | Winter Runner × | nac | 50 | 1 | none | not style gated | cond: season==4 / pre: (none) / effects: type 1 value -400000 / base_time: -1 |
| 200211 | Sunny Days ◎ | nac | 110 | 1 | none | not style gated | cond: weather==1 / pre: (none) / effects: type 4 value 600000 / base_time: -1 |
| 200212 | Sunny Days ○ | nac | 90 | 1 | 100102, 102801 | not style gated | cond: weather==1 / pre: (none) / effects: type 4 value 400000 / base_time: -1 |
| 200221 | Cloudy Days ◎ | nac | 110 | 1 | none | not style gated | cond: weather==2 / pre: (none) / effects: type 4 value 600000 / base_time: -1 |
| 200222 | Cloudy Days ○ | nac | 90 | 1 | 100601 | not style gated | cond: weather==2 / pre: (none) / effects: type 4 value 400000 / base_time: -1 |
| 200231 | Rainy Days ◎ | nac | 110 | 1 | none | not style gated | cond: weather==3 / pre: (none) / effects: type 4 value 600000 / base_time: -1 |
| 200232 | Rainy Days ○ | nac | 90 | 1 | 102601, 102501, 104002, 105701, 102701 | not style gated | cond: weather==3 / pre: (none) / effects: type 4 value 400000 / base_time: -1 |
| 200233 | Rainy Days × | nac | 50 | 1 | none | not style gated | cond: weather==3 / pre: (none) / effects: type 4 value -400000 / base_time: -1 |
| 200241 | Snowy Days ◎ | nac | 110 | 1 | none | not style gated | cond: weather==4 / pre: (none) / effects: type 4 value 600000 / base_time: -1 |
| 200242 | Snowy Days ○ | nac | 90 | 1 | 105202, 102901 | not style gated | cond: weather==4 / pre: (none) / effects: type 4 value 400000 / base_time: -1 |
| 200251 | Inner Post Proficiency ◎ | nac | 110 | 1 | none | not style gated | cond: post_number<=3 / pre: (none) / effects: type 5 value 600000 / base_time: -1 |
| 200252 | Inner Post Proficiency ○ | nac | 90 | 1 | 101502 | not style gated | cond: post_number<=3 / pre: (none) / effects: type 5 value 400000 / base_time: -1 |
| 200253 | Inner Post Averseness | nac | 50 | 1 | none | not style gated | cond: post_number<=3 / pre: (none) / effects: type 5 value -400000 / base_time: -1 |
| 200261 | Outer Post Proficiency ◎ | nac | 110 | 1 | none | not style gated | cond: post_number>=6 / pre: (none) / effects: type 1 value 600000 / base_time: -1 |
| 200262 | Outer Post Proficiency ○ | nac | 90 | 1 | 102301, 106101 | not style gated | cond: post_number>=6 / pre: (none) / effects: type 1 value 400000 / base_time: -1 |
| 200263 | Outer Post Averseness | nac | 50 | 1 | none | not style gated | cond: post_number>=6 / pre: (none) / effects: type 1 value -400000 / base_time: -1 |
| 200271 | Maverick ◎ | nac | 110 | 1 | none | not style gated | cond: running_style_count_same<=1 / pre: (none) / effects: type 1 value 800000 / base_time: -1 |
| 200272 | Maverick ○ | nac | 90 | 1 | none | not style gated | cond: running_style_count_same<=1 / pre: (none) / effects: type 1 value 600000 / base_time: -1 |
| 200281 | Competitive Spirit ◎ | nac | 110 | 1 | none | not style gated | cond: running_style_count_same_rate>=40 / pre: (none) / effects: type 3 value 600000 / base_time: -1 |
| 200282 | Competitive Spirit ○ | nac | 90 | 1 | 102902, 106502, 100901 | not style gated | cond: running_style_count_same_rate>=40 / pre: (none) / effects: type 3 value 400000 / base_time: -1 |
| 200283 | Wallflower | nac | 50 | 1 | none | not style gated | cond: running_style_count_same_rate>=40 / pre: (none) / effects: type 3 value -400000 / base_time: -1 |
| 200291 | Target in Sight ◎ | nac | 110 | 1 | none | not style gated | cond: running_style_equal_popularity_one==1 / pre: (none) / effects: type 4 value 600000 / base_time: -1 |
| 200292 | Target in Sight ○ | nac | 90 | 1 | 102702, 111701 | not style gated | cond: running_style_equal_popularity_one==1 / pre: (none) / effects: type 4 value 400000 / base_time: -1 |
| 200301 | Long Shot ◎ | nac | 110 | 1 | none | not style gated | cond: popularity>=4 / pre: (none) / effects: type 1 value 600000 / base_time: -1 |
| 200302 | Long Shot ○ | nac | 90 | 1 | 104801, 103702, 106002, 106401, 107801, 106501, 110601, 104802, 105201 | not style gated | cond: popularity>=4 / pre: (none) / effects: type 1 value 400000 / base_time: -1 |
| 200311 | G1 Averseness | nac | 50 | 1 | none | not style gated | cond: grade==100 / pre: (none) / effects: type 1 value -400000 / base_time: -1 |
| 200321 | Paddock Fright | nac | 50 | 1 | none | not style gated | cond: popularity==1 / pre: (none) / effects: type 2 value -400000 / base_time: -1 |
| 200331 | Professor of Curvature | cor, nac | 180 | 2 | 101701, 104502 | not style gated | cond: all_corner_random==1 / pre: (none) / effects: type 27 value 3500 / base_time: 24000 |
| 200332 | Corner Adept ○ | cor, nac | 180 | 1 | 101701, 102402, 104502, 100103, 112901 | not style gated | cond: all_corner_random==1 / pre: (none) / effects: type 27 value 1500 / base_time: 24000 |
| 200333 | Corner Adept × | cor, nac | 100 | 1 | none | not style gated | cond: all_corner_random==1 / pre: (none) / effects: type 21 value -2000 / base_time: 24000 |
| 200341 | Corner Connoisseur | cor, nac | 180 | 2 | 100601, 101802, 102902 | not style gated | cond: all_corner_random==1 / pre: (none) / effects: type 31 value 4000 / base_time: 30000 |
| 200342 | Corner Acceleration ○ | cor, nac | 180 | 1 | 100601, 101802, 106901, 107201, 107801, 102902, 106103 | not style gated | cond: all_corner_random==1 / pre: (none) / effects: type 31 value 2000 / base_time: 30000 |
| 200343 | Corner Acceleration × | cor, nac | 100 | 1 | none | not style gated | cond: all_corner_random==1 / pre: (none) / effects: type 31 value -2000 / base_time: 30000 |
| 200351 | Swinging Maestro | l_1, cor, nac | 170 | 2 | 102402, 101502, 105802, 104501 | not style gated | cond: phase==1&corner!=0 / pre: (none) / effects: type 9 value 550 / base_time: 0 |
| 200352 | Corner Recovery ○ | l_1, cor, nac | 170 | 1 | 100302, 100402, 102402, 103002, 101502, 105802, 100703, 104501 | not style gated | cond: phase==1&corner!=0 / pre: (none) / effects: type 9 value 150 / base_time: 0 |
| 200353 | Corner Recovery × | l_1, cor, nac | 100 | 1 | none | not style gated | cond: phase==1&corner!=0 / pre: (none) / effects: type 9 value -200 / base_time: 0 |
| 200361 | Beeline Burst | str, nac | 170 | 2 | 101302, 101601, 105101, 106902 | not style gated | cond: straight_random==1 / pre: (none) / effects: type 27 value 3500 / base_time: 30000 |
| 200362 | Straightaway Adept | str, nac | 170 | 1 | 100401, 101001, 101302, 101501, 101601, 105101, 106902, 108401, 106402, 101401 | not style gated | cond: straight_random==1 / pre: (none) / effects: type 27 value 1500 / base_time: 30000 |
| 200371 | Rushing Gale! | str, nac | 170 | 2 | 104202 | not style gated | cond: straight_random==1 / pre: (none) / effects: type 31 value 4000; type 27 value 1500 / base_time: 30000 |
| 200372 | Straightaway Acceleration | str, nac | 170 | 1 | 104202, 100801 | not style gated | cond: straight_random==1 / pre: (none) / effects: type 31 value 2000 / base_time: 30000 |
| 200381 | Breath of Fresh Air | l_1, str, nac | 170 | 2 | 110201, 100801, 102401 | not style gated | cond: phase==1&corner==0 / pre: (none) / effects: type 9 value 550 / base_time: 0 |
| 200382 | Straightaway Recovery | l_1, str, nac | 170 | 1 | 100202, 110201, 107602, 100801, 102401 | not style gated | cond: phase==1&corner==0 / pre: (none) / effects: type 9 value 150 / base_time: 0 |
| 200391 | Ramp Revulsion | slo, nac | 100 | 1 | none | not style gated | cond: up_slope_random==1 / pre: (none) / effects: type 9 value -200 / base_time: 0 |
| 200401 | Packphobia | nac | 100 | 1 | none | not style gated | cond: accumulatetime>=2&is_surrounded==1 / pre: (none) / effects: type 9 value -200 / base_time: 0 |
| 200411 | Defeatist | f_s, nac | 100 | 1 | none | not style gated | cond: last_straight_random==1&distance_diff_rate>=75 / pre: (none) / effects: type 21 value -2000 / base_time: 30000 |
| 200421 | Reckless | nac | 100 | 1 | none | not style gated | cond: remain_distance==200&order==1&bashin_diff_behind>=1 / pre: (none) / effects: type 21 value -2000 / base_time: 30000 |
| 200431 | Concentration | nac | 140 | 2 | 100201, 102602, 103102, 104603, 114501 | not style gated | cond: always==1 / pre: (none) / effects: type 10 value 4000 / base_time: 0 |
| 200432 | Focus | nac | 140 | 1 | 100201, 100401, 102402, 103701, 102602, 106801, 101303, 103102, 104603, 114501, 106601 | not style gated | cond: always==1 / pre: (none) / effects: type 10 value 9000 / base_time: 0 |
| 200433 | Gatekept | nac | 70 | 1 | none | not style gated | cond: always==1 / pre: (none) / effects: type 10 value 15000 / base_time: 0 |
| 200441 | Iron Will | l_0, l_1, nac | 160 | 2 | 105201 | not style gated | cond: phase<=1&accumulatetime>=5&blocked_front_continuetime>=1 / pre: (none) / effects: type 9 value 550; type 28 value 350 / base_time: 20000 |
| 200442 | Lay Low | l_0, l_1, nac | 160 | 1 | 105201 | not style gated | cond: phase<=1&accumulatetime>=5&blocked_front_continuetime>=1 / pre: (none) / effects: type 9 value 150; type 28 value 150 / base_time: 20000 |
| 200451 | Center Stage | l_0, nac | 120 | 2 | 104601, 105202 | not style gated | cond: phase_random==0 / pre: (none) / effects: type 28 value 450 / base_time: 30000 |
| 200452 | Prudent Positioning | l_0, nac | 120 | 1 | 100301, 100302, 104601, 105202, 108001, 108702, 104603, 103103, 108002 | not style gated | cond: phase_random==0 / pre: (none) / effects: type 28 value 350 / base_time: 30000 |
| 200461 | It's On! | l_1, nac | 170 | 2 | 100102, 107201, 101902, 102102, 108201 | not style gated | cond: phase==1&change_order_onetime<0 / pre: (none) / effects: type 27 value 3500 / base_time: 18000 |
| 200462 | Ramp Up | l_1, nac | 170 | 1 | 100102, 101302, 101802, 103801, 107201, 103601, 105301, 101902, 102102, 103503, 104503, 108201, 106103, 104501 | not style gated | cond: phase==1&change_order_onetime<0 / pre: (none) / effects: type 27 value 1500 / base_time: 18000 |
| 200471 | Indomitable | l_1, nac | 170 | 2 | 105801, 107401, 107102, 105201 | not style gated | cond: phase==1&change_order_onetime>0 / pre: (none) / effects: type 9 value 550 / base_time: 0 |
| 200472 | Pace Strategy | l_1, nac | 170 | 1 | 101102, 105801, 107401, 107102, 102701, 105201 | not style gated | cond: phase==1&change_order_onetime>0 / pre: (none) / effects: type 9 value 150 / base_time: 0 |
| 200481 | Unruffled | l_1, nac | 170 | 2 | 106201 | not style gated | cond: phase==1&is_surrounded==1 / pre: (none) / effects: type 9 value 550 / base_time: 0 |
| 200482 | Calm in a Crowd | l_1, nac | 170 | 1 | 106201 | not style gated | cond: phase==1&is_surrounded==1 / pre: (none) / effects: type 9 value 150 / base_time: 0 |
| 200491 | No Stopping Me! | nac | 150 | 2 | 103901, 106002, 102901, 100802, 105501, 111601, 102401 | not style gated | cond: infront_near_lane_time>=1&is_lastspurt==1&hp_per>=1 / pre: (none) / effects: type 31 value 4000; type 28 value 250 / base_time: 30000 |
| 200492 | Nimble Navigator | nac | 150 | 1 | 100601, 103901, 103301, 106002, 105002, 102901, 100802, 105501, 105302, 107001, 108601, 109101, 103203, 111901, 111601, 102401 | not style gated | cond: infront_near_lane_time>=1&is_lastspurt==1&hp_per>=1 / pre: (none) / effects: type 31 value 2000; type 28 value 50 / base_time: 30000 |
| 200501 | Lane Legerdemain | l_2, nac | 120 | 2 | 103701, 101801 | not style gated | cond: phase_random==2 / pre: (none) / effects: type 28 value 350 / base_time: 30000 |
| 200502 | Go with the Flow | l_2, nac | 120 | 1 | 100301, 101201, 103701, 108501, 101801, 102401 | not style gated | cond: phase_random==2 / pre: (none) / effects: type 28 value 250 / base_time: 30000 |
| 200511 | In Body and Mind | l_3, nac | 170 | 2 | 100101, 101601, 103701, 102302, 108901 | not style gated | cond: is_lastspurt==1&phase_firsthalf_random==3 / pre: (none) / effects: type 27 value 3500 / base_time: 24000 |
| 200512 | Homestretch Haste | l_3, nac | 170 | 1 | 100101, 101402, 101601, 103701, 102302, 108901, 101101, 106201 | not style gated | cond: is_lastspurt==1&phase_firsthalf_random==3 / pre: (none) / effects: type 27 value 1500 / base_time: 24000 |
| 200521 | Running Idle | nac | 100 | 1 | none | not style gated | cond: accumulatetime>=2&order==1&bashin_diff_behind>=1 / pre: (none) / effects: type 9 value -200 / base_time: 0 |
| 200531 | Taking the Lead | run | 120 | 2 | 102601, 106801, 108701, 106802, 110401, 106502, 106601 | Front Runner (1) | cond: running_style==1&always==1 / pre: (none) / effects: type 31 value 4000 / base_time: 12000 |
| 200532 | Early Lead | run | 120 | 1 | 100401, 102601, 106801, 102002, 106401, 108701, 106802, 104102, 110401, 106402, 106803, 108001, 106502, 104603, 106601 | Front Runner (1) | cond: running_style==1&always==1 / pre: (none) / effects: type 31 value 2000 / base_time: 12000 |
| 200541 | Escape Artist | run, l_1 | 180 | 2 | 102001, 102602, 102002, 106802, 100202, 110701, 102003 | Front Runner (1) | cond: running_style==1&phase_random==1&order_rate<=50 / pre: (none) / effects: type 27 value 3500 / base_time: 30000 |
| 200542 | Fast-Paced | run, l_1 | 180 | 1 | 100201, 102001, 102602, 102002, 106802, 104102, 100202, 110701, 102003 | Front Runner (1) | cond: running_style==1&phase_random==1&order_rate<=50 / pre: (none) / effects: type 27 value 1500 / base_time: 30000 |
| 200551 | Unrestrained | run, f_c | 180 | 2 | 100201 | Front Runner (1) | cond: running_style==1&is_finalcorner_random==1&order==1 / pre: (none) / effects: type 31 value 4000 / base_time: 30000 |
| 200552 | Final Push | run, f_c | 180 | 1 | 100201, 110401 | Front Runner (1) | cond: running_style==1&is_finalcorner_random==1&order==1 / pre: (none) / effects: type 31 value 2000 / base_time: 30000 |
| 200561 | Calm and Collected | ldr, l_0 | 180 | 2 | 101301, 102301, 103002, 101702, 109901, 100303, 101401 | Pace Chaser (2) | cond: running_style==2&phase_laterhalf_random==0&order_rate<=50 / pre: (none) / effects: type 9 value 550 / base_time: 0 |
| 200562 | Stamina to Spare | ldr, l_0 | 180 | 1 | 101301, 101302, 101501, 102301, 103002, 101702, 101303, 109901, 100303, 107703, 100901, 101401 | Pace Chaser (2) | cond: running_style==2&phase_laterhalf_random==0&order_rate<=50 / pre: (none) / effects: type 9 value 150 / base_time: 0 |
| 200571 | Race Planner | ldr, l_1 | 180 | 2 | 100302, 100501, 104801, 107101, 104701, 100901, 106301, 103201 | Pace Chaser (2) | cond: running_style==2&phase_random==1&order_rate<=50 / pre: (none) / effects: type 9 value 550 / base_time: 0 |
| 200572 | Preferred Position | ldr, l_1 | 180 | 1 | 100302, 100501, 101601, 101701, 104801, 102302, 102101, 107101, 104701, 100901, 106301, 103201 | Pace Chaser (2) | cond: running_style==2&phase_random==1&order_rate<=50 / pre: (none) / effects: type 9 value 150 / base_time: 0 |
| 200581 | Speed Star | ldr, f_c | 180 | 2 | 101501, 105801, 102201, 106901, 100502, 109301 | Pace Chaser (2) | cond: running_style==2&is_finalcorner_random==1&order_rate<=50 / pre: (none) / effects: type 27 value 3500 / base_time: 18000 |
| 200582 | Prepared to Pass | ldr, f_c | 180 | 1 | 101501, 103801, 105801, 102201, 106901, 100502, 109301, 110901, 100901 | Pace Chaser (2) | cond: running_style==2&is_finalcorner_random==1&order_rate<=50 / pre: (none) / effects: type 27 value 1500 / base_time: 18000 |
| 200591 | Fast & Furious | btw, l_1 | 180 | 2 | 100102, 102101, 108801, 105401, 103501 | Late Surger (3) | cond: running_style==3&phase_random==1&order_rate>=40 / pre: (none) / effects: type 27 value 3500 / base_time: 24000 |
| 200592 | Position Pilfer | btw, l_1 | 180 | 1 | 100102, 102101, 103702, 103502, 108301, 108801, 101101, 105401, 103501 | Late Surger (3) | cond: running_style==3&phase_random==1&order_rate>=40 / pre: (none) / effects: type 27 value 1500 / base_time: 24000 |
| 200601 | On Your Left! | btw, l_2 | 180 | 2 | 104001, 104801, 105302, 108302, 106003, 101101 | Late Surger (3) | cond: running_style==3&phase_firsthalf_random==2&order_rate>=40 / pre: (none) / effects: type 31 value 4000 / base_time: 18000 |
| 200602 | Slick Surge | btw, l_2 | 180 | 1 | 104001, 105602, 104801, 106202, 105302, 108302, 106003, 100801, 101101, 106201 | Late Surger (3) | cond: running_style==3&phase_firsthalf_random==2&order_rate>=40 / pre: (none) / effects: type 31 value 2000 / base_time: 18000 |
| 200611 | Rising Dragon | btw, f_c, cor | 180 | 2 | 101402, 103901, 106102, 105301, 102701 | Late Surger (3) | cond: running_style==3&is_finalcorner==1&corner!=0&is_behind_in==1&change_order_onetime<0 / pre: (none) / effects: type 27 value 3500 / base_time: 30000 |
| 200612 | Outer Swell | btw, f_c, cor | 180 | 1 | 100101, 101402, 103901, 106102, 100103, 105301, 106703, 102701 | Late Surger (3) | cond: running_style==3&is_finalcorner==1&corner!=0&is_behind_in==1&change_order_onetime<0 / pre: (none) / effects: type 27 value 1500 / base_time: 30000 |
| 200621 | Sleeping Lion | cha, l_1 | 180 | 2 | 105001 | End Closer (4) | cond: running_style==4&phase_random==1&distance_diff_rate>=75 / pre: (none) / effects: type 9 value 550 / base_time: 0 |
| 200622 | Standing By | cha, l_1 | 180 | 1 | 105001, 100701 | End Closer (4) | cond: running_style==4&phase_random==1&distance_diff_rate>=75 / pre: (none) / effects: type 9 value 150 / base_time: 0 |
| 200631 | Sturm und Drang | cha, l_3 | 180 | 2 | 101201, 103301, 108401 | End Closer (4) | cond: running_style==4&phase_firsthalf_random==3&is_lastspurt==1 / pre: phase==2&distance_diff_rate>=50 / effects: type 27 value 3500 / base_time: 30000 |
| 200632 | Masterful Gambit | cha, l_3 | 180 | 1 | 101201, 103301, 108401, 101202, 100703, 104402, 108402 | End Closer (4) | cond: running_style==4&phase_firsthalf_random==3&is_lastspurt==1 / pre: phase==2&distance_diff_rate>=50 / effects: type 27 value 1500 / base_time: 30000 |
| 200641 | Encroaching Shadow | cha, str | 180 | 2 | 105001, 103402, 105701, 100703, 102502 | End Closer (4) | cond: running_style==4&is_lastspurt==1&corner==0 / pre: (none) / effects: type 31 value 4000 / base_time: 9000 |
| 200642 | Straightaway Spurt | cha, str | 180 | 1 | 105001, 102101, 103401, 104401, 103402, 105701, 108401, 101202, 100703, 102502 | End Closer (4) | cond: running_style==4&is_lastspurt==1&corner==0 / pre: (none) / effects: type 31 value 2000 / base_time: 9000 |
| 200651 | Turbo Sprint | sho, l_2 | 160 | 2 | 103802, 105302, 104101 | not style gated | cond: distance_type==1&phase_random==2 / pre: (none) / effects: type 31 value 4000 / base_time: 30000 |
| 200652 | Sprinting Gear | sho, l_2 | 160 | 1 | 105101, 103802, 105302, 105401, 104101, 105201 | not style gated | cond: distance_type==1&phase_random==2 / pre: (none) / effects: type 31 value 2000 / base_time: 30000 |
| 200662 | Wait-and-See | sho, l_1 | 160 | 1 | none | not style gated | cond: distance_type==1&phase_random==1&order_rate>50 / pre: (none) / effects: type 9 value 150; type 31 value 1000 / base_time: 30000 |
| 200671 | Blinding Flash | sho, l_2 | 160 | 2 | 105401, 106101 | not style gated | cond: distance_type==1&phase_random==2&order_rate>50 / pre: (none) / effects: type 27 value 3500; type 31 value 1000 / base_time: 30000 |
| 200672 | Gap Closer | sho, l_2 | 160 | 1 | 106102, 105401, 106101 | not style gated | cond: distance_type==1&phase_random==2&order_rate>50 / pre: (none) / effects: type 27 value 1500; type 31 value 500 / base_time: 30000 |
| 200681 | Mile Maven | mil, l_0 | 160 | 2 | 100402, 101001, 101002, 106502, 108602 | not style gated | cond: distance_type==2&phase_laterhalf_random==0&order_rate<=50 / pre: (none) / effects: type 27 value 3500 / base_time: 30000 |
| 200682 | Productive Plan | mil, l_0 | 160 | 1 | 100402, 101001, 101002, 106902, 106502, 100403, 108602 | not style gated | cond: distance_type==2&phase_laterhalf_random==0&order_rate<=50 / pre: (none) / effects: type 27 value 1500 / base_time: 30000 |
| 200691 | Keen Eye | dbf, mil, l_0 | 160 | 2 | 104001 | not style gated | cond: distance_type==2&phase_laterhalf_random==0&order_rate>50 / pre: (none) / effects: type 9 value 550; type 21 value -2000 / base_time: 30000 |
| 200692 | Watchful Eye | dbf, mil, l_0 | 160 | 1 | 104001, 106102 | not style gated | cond: distance_type==2&phase_laterhalf_random==0&order_rate>50 / pre: (none) / effects: type 9 value 150; type 21 value -500 / base_time: 30000 |
| 200701 | Furious Feat | mil, l_2 | 160 | 2 | 101402, 106103, 108402, 100801 | not style gated | cond: distance_type==2&phase_random==2&order_rate>50 / pre: (none) / effects: type 31 value 4000 / base_time: 30000 |
| 200702 | Updrafters | mil, l_2 | 160 | 1 | 101402, 101902, 106103, 108402, 100801 | not style gated | cond: distance_type==2&phase_random==2&order_rate>50 / pre: (none) / effects: type 31 value 2000 / base_time: 30000 |
| 200711 | Trackblazer | med, l_1 | 160 | 2 | 102601 | not style gated | cond: distance_type==3&phase_random==1&order<=3 / pre: (none) / effects: type 9 value 550 / base_time: 0 |
| 200712 | Rosy Outlook | med, l_1 | 160 | 1 | 100201, 102601, 102002, 106802 | not style gated | cond: distance_type==3&phase_random==1&order<=3 / pre: (none) / effects: type 9 value 150 / base_time: 0 |
| 200721 | Killer Tunes | med, l_1 | 160 | 2 | 101501, 102102, 107001, 111301, 100901, 102701 | not style gated | cond: distance_type==3&phase_random==1&order_rate<=50 / pre: (none) / effects: type 27 value 3500 / base_time: 24000 |
| 200722 | Up-Tempo | med, l_1 | 160 | 1 | 101501, 105901, 102102, 107001, 110701, 111301, 110202, 100901, 101401, 101801, 102701 | not style gated | cond: distance_type==3&phase_random==1&order_rate<=50 / pre: (none) / effects: type 27 value 1500 / base_time: 24000 |
| 200731 | Unyielding | med, f_s, f_c | 160 | 2 | 101201, 107201 | not style gated | cond: distance_type==3&is_finalcorner==1&overtake_target_time>=2 / pre: (none) / effects: type 27 value 3500; type 31 value 1000 / base_time: 30000 |
| 200732 | Steadfast | med, f_s, f_c | 160 | 1 | 100101, 101201, 107201, 106902, 110401, 103501 | not style gated | cond: distance_type==3&is_finalcorner==1&overtake_target_time>=2 / pre: (none) / effects: type 27 value 1500; type 31 value 500 / base_time: 30000 |
| 200741 | Cooldown | lng, l_1 | 160 | 2 | 101102, 101302, 102301, 103001, 112701 | not style gated | cond: distance_type==4&phase_random==1 / pre: (none) / effects: type 9 value 550 / base_time: 0 |
| 200742 | Deep Breaths | lng, l_1 | 160 | 1 | 101102, 101302, 102301, 103001, 101602, 102502, 104503, 112701, 102401, 104501 | not style gated | cond: distance_type==4&phase_random==1 / pre: (none) / effects: type 9 value 150 / base_time: 0 |
| 200751 | Innate Experience | lng, f_c, cor | 160 | 2 | 105602, 100602, 100702, 103402, 103602, 102503 | not style gated | cond: distance_type==4&is_finalcorner==1&corner!=0&lane_type==0 / pre: (none) / effects: type 27 value 3500 / base_time: 30000 |
| 200752 | Inside Scoop | lng, f_c, cor | 160 | 1 | 105602, 101702, 100602, 100702, 103402, 103602, 102503 | not style gated | cond: distance_type==4&is_finalcorner==1&corner!=0&lane_type==0 / pre: (none) / effects: type 27 value 1500 / base_time: 30000 |
| 200761 | Adrenaline Rush | lng | 160 | 2 | none | not style gated | cond: distance_type==4&hp_per<=30 / pre: (none) / effects: type 9 value 550; type 27 value 3500 / base_time: 18000 |
| 200762 | Extra Tank | lng | 160 | 1 | 102602 | not style gated | cond: distance_type==4&hp_per<=30 / pre: (none) / effects: type 9 value 150 / base_time: 0 |
| 200771 | Trick (Front) | dbf, l_1, nac | 140 | 1 | 100501, 101102, 103002, 100502 | not style gated | cond: phase==1&order_rate<=50&temptation_opponent_count_behind>=1 / pre: (none) / effects: type 9 value -100 / base_time: 0 |
| 200781 | Trick (Rear) | dbf, l_1, nac | 140 | 1 | 104401, 105601 | not style gated | cond: phase==1&order_rate>50&temptation_opponent_count_infront>=1 / pre: (none) / effects: type 9 value -100 / base_time: 0 |
| 200791 | Frenzied Front Runners | dbf, nac | 130 | 1 | none | not style gated | cond: running_style_temptation_opponent_count_nige>=1&is_temptation==0 / pre: (none) / effects: type 13 value 50000 / base_time: 0 |
| 200801 | Frenzied Pace Chasers | dbf, nac | 130 | 1 | none | not style gated | cond: running_style_temptation_opponent_count_senko>=1&is_temptation==0 / pre: (none) / effects: type 13 value 50000 / base_time: 0 |
| 200811 | Frenzied Late Surgers | dbf, nac | 130 | 1 | none | not style gated | cond: running_style_temptation_opponent_count_sashi>=1&is_temptation==0 / pre: (none) / effects: type 13 value 50000 / base_time: 0 |
| 200821 | Frenzied End Closers | dbf, nac | 130 | 1 | 102301 | not style gated | cond: running_style_temptation_opponent_count_oikomi>=1&is_temptation==0 / pre: (none) / effects: type 13 value 50000 / base_time: 0 |
| 200831 | Subdued Front Runners | dbf, l_0, nac | 130 | 1 | none | not style gated | cond: running_style_count_nige_otherself>=1&phase_random==0&accumulatetime>=5 / pre: (none) / effects: type 9 value -100 / base_time: 0 |
| 200841 | Flustered Front Runners | dbf, l_1, nac | 130 | 1 | 104101 | not style gated | cond: running_style_count_nige_otherself>=1&phase_random==1 / pre: (none) / effects: type 9 value -100 / base_time: 0 |
| 200851 | Hesitant Front Runners | dbf, l_2, nac | 130 | 1 | 103002, 103802, 108501 | not style gated | cond: running_style_count_nige_otherself>=1&phase_random==2 / pre: (none) / effects: type 21 value -1500 / base_time: 30000 |
| 200861 | Subdued Pace Chasers | dbf, l_0, nac | 130 | 1 | 103001, 105801, 103301, 109901 | not style gated | cond: running_style_count_senko_otherself>=1&phase_random==0&accumulatetime>=5 / pre: (none) / effects: type 9 value -100 / base_time: 0 |
| 200871 | Flustered Pace Chasers | dbf, l_1, nac | 130 | 1 | 101101 | not style gated | cond: running_style_count_senko_otherself>=1&phase_random==1 / pre: (none) / effects: type 9 value -100 / base_time: 0 |
| 200881 | Hesitant Pace Chasers | dbf, l_2, nac | 130 | 1 | 102001, 104501 | not style gated | cond: running_style_count_senko_otherself>=1&phase_random==2 / pre: (none) / effects: type 21 value -1500 / base_time: 30000 |
| 200891 | Subdued Late Surgers | dbf, l_0, nac | 130 | 1 | 101701 | not style gated | cond: running_style_count_sashi_otherself>=1&phase_random==0&accumulatetime>=5 / pre: (none) / effects: type 9 value -100 / base_time: 0 |
| 200901 | Flustered Late Surgers | dbf, l_1, nac | 130 | 1 | none | not style gated | cond: running_style_count_sashi_otherself>=1&phase_random==1 / pre: (none) / effects: type 9 value -100 / base_time: 0 |
| 200911 | Hesitant Late Surgers | dbf, l_2, nac | 130 | 1 | 106001 | not style gated | cond: running_style_count_sashi_otherself>=1&phase_random==2 / pre: (none) / effects: type 21 value -1500 / base_time: 30000 |
| 200921 | Subdued End Closers | dbf, l_0, nac | 130 | 1 | none | not style gated | cond: running_style_count_oikomi_otherself>=1&phase_random==0&accumulatetime>=5 / pre: (none) / effects: type 9 value -100 / base_time: 0 |
| 200931 | Flustered End Closers | dbf, l_1, nac | 130 | 1 | 101801 | not style gated | cond: running_style_count_oikomi_otherself>=1&phase_random==1 / pre: (none) / effects: type 9 value -100 / base_time: 0 |
| 200941 | Hesitant End Closers | dbf, l_2, nac | 130 | 1 | 101801 | not style gated | cond: running_style_count_oikomi_otherself>=1&phase_random==2 / pre: (none) / effects: type 21 value -1500 / base_time: 30000 |
| 200951 | Oi Racecourse ◎ | nac | 110 | 1 | none | not style gated | cond: track_id==10101 / pre: (none) / effects: type 2 value 600000 / base_time: -1 |
| 200952 | Oi Racecourse ○ | nac | 90 | 1 | 104601, 103402 | not style gated | cond: track_id==10101 / pre: (none) / effects: type 2 value 400000 / base_time: -1 |
| 200953 | Oi Racecourse × | nac | 50 | 1 | none | not style gated | cond: track_id==10101 / pre: (none) / effects: type 2 value -400000 / base_time: -1 |
| 200961 | Sprint Straightaways ◎ | sho, str | 110 | 1 | none | not style gated | cond: distance_type==1&straight_random==1 / pre: (none) / effects: type 27 value 2500 / base_time: 30000 |
| 200962 | Sprint Straightaways ○ | sho, str | 100 | 1 | 104201, 104102, 112001 | not style gated | cond: distance_type==1&straight_random==1 / pre: (none) / effects: type 27 value 1500 / base_time: 30000 |
| 200971 | Sprint Corners ◎ | sho, cor | 110 | 1 | none | not style gated | cond: distance_type==1&all_corner_random==1 / pre: (none) / effects: type 27 value 2500 / base_time: 30000 |
| 200972 | Sprint Corners ○ | sho, cor | 100 | 1 | 105301, 108701, 105302, 106103 | not style gated | cond: distance_type==1&all_corner_random==1 / pre: (none) / effects: type 27 value 1500 / base_time: 30000 |
| 200981 | Staggering Lead | sho, l_1 | 170 | 2 | 104101 | not style gated | cond: distance_type==1&phase==1&bashin_diff_behind>=3&order==1 / pre: (none) / effects: type 27 value 3500 / base_time: 30000 |
| 200982 | Huge Lead | sho, l_1 | 170 | 1 | 104101 | not style gated | cond: distance_type==1&phase==1&bashin_diff_behind>=3&order==1 / pre: (none) / effects: type 27 value 1500 / base_time: 30000 |
| 200991 | Plan X | sho, l_1 | 160 | 2 | none | not style gated | cond: distance_type==1&phase_laterhalf_random==1&order>=2&order_rate<=50 / pre: (none) / effects: type 31 value 4000 / base_time: 30000 |
| 200992 | Countermeasure | sho, l_1 | 160 | 1 | 109301, 104101 | not style gated | cond: distance_type==1&phase_laterhalf_random==1&order>=2&order_rate<=50 / pre: (none) / effects: type 31 value 2000 / base_time: 30000 |
| 201001 | Perfect Prep! | sho, l_1 | 140 | 2 | 102801 | not style gated | cond: distance_type==1&phase_random==1 / pre: (none) / effects: type 28 value 350; type 31 value 3000 / base_time: 40000 |
| 201002 | Meticulous Measures | sho, l_1 | 140 | 1 | 102801, 103801, 105401 | not style gated | cond: distance_type==1&phase_random==1 / pre: (none) / effects: type 28 value 250; type 31 value 2000 / base_time: 40000 |
| 201012 | Intimidate | dbf, sho, l_0 | 170 | 1 | 102801, 103801, 103802, 108702 | not style gated | cond: distance_type==1&phase_random==0&order_rate<=50&accumulatetime>=5 / pre: (none) / effects: type 21 value -2000 / base_time: 30000 |
| 201021 | You've Got No Shot | dbf, sho, l_0 | 170 | 2 | none | not style gated | cond: distance_type==1&phase_random==0&order_rate>50&accumulatetime>=5 / pre: (none) / effects: type 9 value -300; type 31 value -2000 / base_time: 12000 |
| 201022 | Stop Right There! | dbf, sho, l_0 | 170 | 1 | none | not style gated | cond: distance_type==1&phase_random==0&order_rate>50&accumulatetime>=5 / pre: (none) / effects: type 9 value -100; type 31 value -500 / base_time: 12000 |
| 201031 | Mile Straightaways ◎ | mil, str | 110 | 1 | none | not style gated | cond: distance_type==2&straight_random==1 / pre: (none) / effects: type 27 value 2500 / base_time: 30000 |
| 201032 | Mile Straightaways ○ | mil, str | 100 | 1 | 101001, 110001, 106501, 108001, 100403, 107802, 113101, 100801 | not style gated | cond: distance_type==2&straight_random==1 / pre: (none) / effects: type 27 value 1500 / base_time: 30000 |
| 201041 | Mile Corners ◎ | mil, cor | 110 | 1 | none | not style gated | cond: distance_type==2&all_corner_random==1 / pre: (none) / effects: type 27 value 2500 / base_time: 30000 |
| 201042 | Mile Corners ○ | mil, cor | 100 | 1 | 100501, 100502, 102202, 101002, 107801, 105102, 102902, 108201 | not style gated | cond: distance_type==2&all_corner_random==1 / pre: (none) / effects: type 27 value 1500 / base_time: 30000 |
| 201051 | Changing Gears | mil, l_1 | 160 | 2 | 100401, 101001, 102202, 107801, 106501 | not style gated | cond: distance_type==2&phase_random==1&order_rate<=50 / pre: (none) / effects: type 27 value 3500 / base_time: 24000 |
| 201052 | Shifting Gears | mil, l_1 | 160 | 1 | 100401, 100501, 101001, 102202, 107801, 106501, 104301, 100403 | not style gated | cond: distance_type==2&phase_random==1&order_rate<=50 / pre: (none) / effects: type 27 value 1500 / base_time: 24000 |
| 201061 | Step on the Gas! | mil | 160 | 2 | 100401 | not style gated | cond: distance_type==2&distance_rate>=50&change_order_onetime<0 / pre: (none) / effects: type 31 value 4000 / base_time: 30000 |
| 201062 | Acceleration | mil | 160 | 1 | 100401, 100601, 104001, 105101 | not style gated | cond: distance_type==2&distance_rate>=50&change_order_onetime<0 / pre: (none) / effects: type 31 value 2000 / base_time: 30000 |
| 201071 | Big-Sisterly | mil | 120 | 2 | 100501, 101802, 104201, 110001, 100403, 112402 | not style gated | cond: distance_type==2&is_overtake==1&accumulatetime>=5 / pre: (none) / effects: type 27 value 3500 / base_time: 24000 |
| 201072 | Unyielding Spirit | mil | 120 | 1 | 100501, 101402, 101802, 103901, 105202, 104201, 110001, 104301, 105102, 102902, 104202, 100403, 112402, 104302, 106101 | not style gated | cond: distance_type==2&is_overtake==1&accumulatetime>=5 / pre: (none) / effects: type 27 value 1500 / base_time: 24000 |
| 201081 | Greed for Speed | dbf, mil, l_1 | 160 | 2 | none | not style gated | cond: distance_type==2&phase_random==1&order<=3 / pre: (none) / effects: type 21 value -2000; type 27 value 2500 / base_time: 30000 |
| 201082 | Speed Eater | dbf, mil, l_1 | 160 | 1 | 100402, 103101, 103102 | not style gated | cond: distance_type==2&phase_random==1&order<=3 / pre: (none) / effects: type 21 value -1500; type 27 value 1500 / base_time: 30000 |
| 201091 | Battle Formation | dbf, mil, l_0 | 160 | 2 | 104002 | not style gated | cond: distance_type==2&phase_random==0&order_rate>50&accumulatetime>=3 / pre: (none) / effects: type 31 value -3000 / base_time: 30000 |
| 201092 | Opening Gambit | dbf, mil, l_0 | 160 | 1 | 104002 | not style gated | cond: distance_type==2&phase_random==0&order_rate>50&accumulatetime>=3 / pre: (none) / effects: type 31 value -1000 / base_time: 30000 |
| 201101 | Medium Straightaways ◎ | med, str | 110 | 1 | none | not style gated | cond: distance_type==3&straight_random==1 / pre: (none) / effects: type 27 value 2500 / base_time: 30000 |
| 201102 | Medium Straightaways ○ | med, str | 100 | 1 | 101601, 101901, 104502, 104801, 103702, 106801, 106701, 105902, 102901, 105802, 104901, 107102, 100703, 111001, 111101, 110702, 107002, 108802, 107301, 110301, 102701, 103201 | not style gated | cond: distance_type==3&straight_random==1 / pre: (none) / effects: type 27 value 1500 / base_time: 30000 |
| 201111 | Medium Corners ◎ | med, cor | 110 | 1 | none | not style gated | cond: distance_type==3&all_corner_random==1 / pre: (none) / effects: type 27 value 2500 / base_time: 30000 |
| 201112 | Medium Corners ○ | med, cor | 100 | 1 | 103701, 105801, 102201, 106901, 109801, 100802, 104701, 102702, 106202, 107001, 106703, 103902, 108801, 103302, 107301, 110301, 103201 | not style gated | cond: distance_type==3&all_corner_random==1 / pre: (none) / effects: type 27 value 1500 / base_time: 30000 |
| 201121 | Clairvoyance | med, l_0 | 110 | 2 | 101401 | not style gated | cond: distance_type==3&phase_random==0 / pre: (none) / effects: type 8 value 150000 / base_time: 30000 |
| 201122 | Hawkeye | med, l_0 | 110 | 1 | 101401 | not style gated | cond: distance_type==3&phase_random==0 / pre: (none) / effects: type 8 value 100000 / base_time: 30000 |
| 201131 | Lightning Step | med, l_1 | 140 | 2 | 100301 | not style gated | cond: distance_type==3&phase_random==1 / pre: (none) / effects: type 28 value 350; type 31 value 3000 / base_time: 40000 |
| 201132 | Thunderbolt Step | med, l_1 | 140 | 1 | 100102, 100301, 102101 | not style gated | cond: distance_type==3&phase_random==1 / pre: (none) / effects: type 28 value 250; type 31 value 2000 / base_time: 40000 |
| 201141 | Miraculous Step | med | 160 | 2 | 100302 | not style gated | cond: distance_type==3&is_move_lane==1&accumulatetime>=10@distance_type==3&is_move_lane==2&accumulatetime>=10 / pre: (none) / effects: type 9 value 550 / base_time: 0 |
| 201142 | Soft Step | med | 160 | 1 | 100301, 100302, 105901, 107101, 105701, 104802, 106101 | not style gated | cond: distance_type==3&is_move_lane==1&accumulatetime>=10@distance_type==3&is_move_lane==2&accumulatetime>=10 / pre: (none) / effects: type 9 value 150 / base_time: 0 |
| 201151 | Dominator | dbf, med, l_2 | 160 | 2 | 101701, 103702, 106002, 101101, 103201 | not style gated | cond: distance_type==3&phase_random==2&order_rate>50 / pre: (none) / effects: type 21 value -2500 / base_time: 30000 |
| 201152 | Tether | dbf, med, l_2 | 160 | 1 | 101701, 103702, 103301, 106002, 101202, 101101, 103201 | not style gated | cond: distance_type==3&phase_random==2&order_rate>50 / pre: (none) / effects: type 21 value -1500 / base_time: 30000 |
| 201161 | Mystifying Murmur | dbf, med, l_1 | 160 | 2 | 102402, 104502, 106001 | not style gated | cond: distance_type==3&phase==1&blocked_front_continuetime>=1 / pre: (none) / effects: type 9 value -300 / base_time: 0 |
| 201162 | Murmur | dbf, med, l_1 | 160 | 1 | 102402, 105001, 104502, 106001 | not style gated | cond: distance_type==3&phase==1&blocked_front_continuetime>=1 / pre: (none) / effects: type 9 value -100 / base_time: 0 |
| 201171 | Long Straightaways ◎ | lng, str | 110 | 1 | none | not style gated | cond: distance_type==4&straight_random==1 / pre: (none) / effects: type 27 value 2500 / base_time: 30000 |
| 201172 | Long Straightaways ○ | lng, str | 100 | 1 | 103002, 106801, 100103, 100902, 104701, 107701, 106402, 104503, 103003, 102303 | not style gated | cond: distance_type==4&straight_random==1 / pre: (none) / effects: type 27 value 1500 / base_time: 30000 |
| 201181 | Long Corners ◎ | lng, cor | 110 | 1 | none | not style gated | cond: distance_type==4&all_corner_random==1 / pre: (none) / effects: type 27 value 2500 / base_time: 30000 |
| 201182 | Long Corners ○ | lng, cor | 100 | 1 | 101301, 100103, 100902, 107602 | not style gated | cond: distance_type==4&all_corner_random==1 / pre: (none) / effects: type 27 value 1500 / base_time: 30000 |
| 201191 | Vanguard Spirit | lng, l_1 | 160 | 2 | 101301, 102001, 106401 | not style gated | cond: distance_type==4&phase_random==1&bashin_diff_behind>=1&order==1 / pre: (none) / effects: type 27 value 3500 / base_time: 30000 |
| 201192 | Keeping the Lead | lng, l_1 | 160 | 1 | 101301, 102001, 106401 | not style gated | cond: distance_type==4&phase_random==1&bashin_diff_behind>=1&order==1 / pre: (none) / effects: type 27 value 1500 / base_time: 30000 |
| 201202 | Passing Pro | lng | 160 | 1 | 102501, 102302 | not style gated | cond: distance_type==4&is_overtake==1&accumulatetime>=5 / pre: (none) / effects: type 9 value 150 / base_time: 0 |
| 201211 | Overwhelming Pressure | lng, l_2 | 160 | 2 | 105602, 100103, 107402, 106201 | not style gated | cond: distance_type==4&phase==2&change_order_onetime<0 / pre: (none) / effects: type 27 value 3500; type 31 value 1000 / base_time: 30000 |
| 201212 | Pressure | lng, l_2 | 160 | 1 | 105602, 107401, 100103, 107402, 100701, 106201 | not style gated | cond: distance_type==4&phase==2&change_order_onetime<0 / pre: (none) / effects: type 27 value 1500; type 31 value 500 / base_time: 30000 |
| 201221 | Stamina Siphon | dbf, lng, l_1 | 160 | 2 | 102501, 106702, 103602 | not style gated | cond: distance_type==4&phase_random==1&order>=5 / pre: (none) / effects: type 9 value -100; type 9 value 350 / base_time: 0 |
| 201222 | Stamina Eater | dbf, lng, l_1 | 160 | 1 | 102501, 107401, 106702, 103602 | not style gated | cond: distance_type==4&phase_random==1&order>=5 / pre: (none) / effects: type 9 value -50; type 9 value 150 / base_time: 0 |
| 201231 | Illusionist | dbf, lng, l_2 | 110 | 2 | 105601 | not style gated | cond: distance_type==4&phase_random==2 / pre: (none) / effects: type 8 value -100000 / base_time: 30000 |
| 201232 | Smoke Screen | dbf, lng, l_2 | 110 | 1 | 105601 | not style gated | cond: distance_type==4&phase_random==2 / pre: (none) / effects: type 8 value -50000 / base_time: 30000 |
| 201241 | Front Runner Straightaways ◎ | run, str | 140 | 1 | none | Front Runner (1) | cond: running_style==1&straight_random==1 / pre: (none) / effects: type 27 value 2500 / base_time: 30000 |
| 201242 | Front Runner Straightaways ○ | run, str | 130 | 1 | 102601, 106401, 106502, 112001 | Front Runner (1) | cond: running_style==1&straight_random==1 / pre: (none) / effects: type 27 value 1500 / base_time: 30000 |
| 201251 | Front Runner Corners ◎ | run, cor | 140 | 1 | none | Front Runner (1) | cond: running_style==1&all_corner_random==1 / pre: (none) / effects: type 27 value 2500 / base_time: 30000 |
| 201252 | Front Runner Corners ○ | run, cor | 130 | 1 | 102601, 104602, 103102, 100202, 106601 | Front Runner (1) | cond: running_style==1&all_corner_random==1 / pre: (none) / effects: type 27 value 1500 / base_time: 30000 |
| 201262 | Dodging Danger | run, l_0 | 110 | 1 | 106802, 102403, 113701 | Front Runner (1) | cond: running_style==1&phase==0&blocked_front_continuetime>=1@running_style==1&phase==0&blocked_side_continuetime>=1 / pre: (none) / effects: type 28 value 250; type 35 value 5000 / base_time: 30000 |
| 201272 | Leader's Pride | run, l_0, l_1 | 180 | 1 | 106801, 102002, 102401 | Front Runner (1) | cond: running_style==1&phase<=1&change_order_onetime>0&accumulatetime>=5@running_style==1&phase<=1&blocked_side_continuetime>=2&accumulatetime>=5 / pre: (none) / effects: type 27 value 1500 / base_time: 30000 |
| 201281 | Restless | run, slo | 180 | 2 | 100402, 103101, 100902 | Front Runner (1) | cond: running_style==1&slope==1&accumulatetime>=10 / pre: (none) / effects: type 9 value 550 / base_time: 0 |
| 201282 | Moxie | run, slo | 180 | 1 | 100402, 103101, 100902, 106601 | Front Runner (1) | cond: running_style==1&slope==1&accumulatetime>=10 / pre: (none) / effects: type 9 value 150 / base_time: 0 |
| 201291 | Reignition | run, l_2 | 180 | 2 | none | Front Runner (1) | cond: running_style==1&phase_random==2 / pre: (none) / effects: type 31 value 4000; type 9 value -200 / base_time: 30000 |
| 201292 | Second Wind | run, l_2 | 180 | 1 | none | Front Runner (1) | cond: running_style==1&phase_random==2 / pre: (none) / effects: type 31 value 2000; type 9 value -200 / base_time: 30000 |
| 201302 | Restart | dbf, run, l_0 | 130 | 1 | none | Front Runner (1) | cond: running_style==1&phase_random==0&order>=2&accumulatetime>=5 / pre: (none) / effects: type 31 value -1000 / base_time: 30000 |
| 201311 | Pace Chaser Straightaways ◎ | ldr, str | 140 | 1 | none | Pace Chaser (2) | cond: running_style==2&straight_random==1 / pre: (none) / effects: type 27 value 2500 / base_time: 30000 |
| 201312 | Pace Chaser Straightaways ○ | ldr, str | 130 | 1 | 101702, 102302, 101502, 109301, 100303, 110901, 104702, 113301, 102802, 101401, 107301, 104101 | Pace Chaser (2) | cond: running_style==2&straight_random==1 / pre: (none) / effects: type 27 value 1500 / base_time: 30000 |
| 201321 | Pace Chaser Corners ◎ | ldr, cor | 140 | 1 | none | Pace Chaser (2) | cond: running_style==2&all_corner_random==1 / pre: (none) / effects: type 27 value 2500 / base_time: 30000 |
| 201322 | Pace Chaser Corners ○ | ldr, cor | 130 | 1 | 101701, 101802, 104502, 107101, 107201, 103802, 101002, 102901, 104701, 102702, 106902, 107102, 103203, 111601, 107702, 109501, 107301 | Pace Chaser (2) | cond: running_style==2&all_corner_random==1 / pre: (none) / effects: type 27 value 1500 / base_time: 30000 |
| 201331 | Technician | ldr | 120 | 2 | 100301, 110301 | Pace Chaser (2) | cond: running_style==2&is_move_lane==1@running_style==2&is_move_lane==2 / pre: (none) / effects: type 31 value 3000 / base_time: 30000 |
| 201332 | Shrewd Step | ldr | 120 | 1 | 100301, 102402, 100502, 109901, 110301 | Pace Chaser (2) | cond: running_style==2&is_move_lane==1@running_style==2&is_move_lane==2 / pre: (none) / effects: type 31 value 2000 / base_time: 30000 |
| 201341 | Determined Descent | ldr, slo | 120 | 2 | 103001, 102201, 105101, 102901, 102702, 110902 | Pace Chaser (2) | cond: running_style==2&down_slope_random==1 / pre: (none) / effects: type 31 value 3000 / base_time: 30000 |
| 201342 | Straight Descent | ldr, slo | 120 | 1 | 103001, 101702, 102201, 105101, 102901, 102702, 110902, 111301, 111302 | Pace Chaser (2) | cond: running_style==2&down_slope_random==1 / pre: (none) / effects: type 31 value 2000 / base_time: 30000 |
| 201351 | Gourmand | ldr, l_1 | 180 | 2 | 100101, 100601, 102801, 100602 | Pace Chaser (2) | cond: running_style==2&phase_random==1 / pre: (none) / effects: type 9 value 550 / base_time: 0 |
| 201352 | Hydrate | ldr, l_1 | 180 | 1 | 100101, 100601, 101301, 102801, 103001, 104502, 100602, 108601, 108901 | Pace Chaser (2) | cond: running_style==2&phase_random==1 / pre: (none) / effects: type 9 value 150 / base_time: 0 |
| 201361 | Shatterproof | ldr, l_1 | 120 | 2 | none | Pace Chaser (2) | cond: running_style==2&phase_random==1&order_rate>40 / pre: (none) / effects: type 31 value 3000 / base_time: 30000 |
| 201362 | Tactical Tweak | ldr, l_1 | 120 | 1 | 102801 | Pace Chaser (2) | cond: running_style==2&phase_random==1&order_rate>40 / pre: (none) / effects: type 31 value 2000 / base_time: 30000 |
| 201371 | Dazzling Disorientation | dbf, ldr, l_2 | 110 | 2 | 103801, 101801, 104501 | Pace Chaser (2) | cond: running_style==2&phase_random==2&order_rate<=50 / pre: (none) / effects: type 8 value -50000 / base_time: 30000 |
| 201372 | Disorient | dbf, ldr, l_2 | 110 | 1 | 103801, 101801, 104501 | Pace Chaser (2) | cond: running_style==2&phase_random==2&order_rate<=50 / pre: (none) / effects: type 8 value -30000 / base_time: 30000 |
| 201381 | Late Surger Straightaways ◎ | btw, str | 140 | 1 | none | Late Surger (3) | cond: running_style==3&straight_random==1 / pre: (none) / effects: type 27 value 2500 / base_time: 30000 |
| 201382 | Late Surger Straightaways ○ | btw, str | 130 | 1 | 101901, 103901, 105901, 106102, 104901, 100802, 106702, 107601, 110601, 106003, 109401, 103703, 101103, 102503, 105401 | Late Surger (3) | cond: running_style==3&straight_random==1 / pre: (none) / effects: type 27 value 1500 / base_time: 30000 |
| 201391 | Late Surger Corners ◎ | btw, cor | 140 | 1 | none | Late Surger (3) | cond: running_style==3&all_corner_random==1 / pre: (none) / effects: type 27 value 2500 / base_time: 30000 |
| 201392 | Late Surger Corners ○ | btw, cor | 130 | 1 | 104002, 103702, 105101, 108501, 103602, 101103, 104003, 110602, 103501 | Late Surger (3) | cond: running_style==3&all_corner_random==1 / pre: (none) / effects: type 27 value 1500 / base_time: 30000 |
| 201401 | Hard Worker | btw | 120 | 2 | 105202, 103501 | Late Surger (3) | cond: running_style==3&is_overtake==1&accumulatetime>=5 / pre: (none) / effects: type 31 value 3000 / base_time: 40000 |
| 201402 | Fighter | btw | 120 | 1 | 105202, 103501 | Late Surger (3) | cond: running_style==3&is_overtake==1&accumulatetime>=5 / pre: (none) / effects: type 31 value 2000 / base_time: 40000 |
| 201411 | 15,000,000 CC | btw, slo | 120 | 2 | 103502 | Late Surger (3) | cond: running_style==3&up_slope_random==1 / pre: (none) / effects: type 27 value 3500 / base_time: 24000 |
| 201412 | 1,500,000 CC | btw, slo | 120 | 1 | 101402, 103502, 111001, 113201, 105201 | Late Surger (3) | cond: running_style==3&up_slope_random==1 / pre: (none) / effects: type 27 value 1500 / base_time: 24000 |
| 201421 | Relax | btw, l_1 | 180 | 2 | 101102, 102501, 110601 | Late Surger (3) | cond: running_style==3&phase_random==1&order_rate>=40 / pre: (none) / effects: type 9 value 550 / base_time: 0 |
| 201422 | A Small Breather | btw, l_1 | 180 | 1 | 101102, 102501, 100602, 110601, 105601, 106001 | Late Surger (3) | cond: running_style==3&phase_random==1&order_rate>=40 / pre: (none) / effects: type 9 value 150 / base_time: 0 |
| 201431 | The Bigger Picture | btw, l_1 | 120 | 2 | 106101 | Late Surger (3) | cond: running_style==3&phase_random==1 / pre: (none) / effects: type 8 value 150000 / base_time: 30000 |
| 201432 | Studious | btw, l_1 | 120 | 1 | 106101 | Late Surger (3) | cond: running_style==3&phase_random==1 / pre: (none) / effects: type 8 value 50000 / base_time: 30000 |
| 201441 | All-Seeing Eyes | dbf, btw, l_2 | 180 | 2 | 105901, 106001 | Late Surger (3) | cond: running_style==3&phase_random==2&order_rate>50 / pre: (none) / effects: type 9 value -300 / base_time: 0 |
| 201442 | Sharp Gaze | dbf, btw, l_2 | 180 | 1 | 105901, 109101, 106001 | Late Surger (3) | cond: running_style==3&phase_random==2&order_rate>50 / pre: (none) / effects: type 9 value -100 / base_time: 0 |
| 201451 | End Closer Straightaways ◎ | cha, str | 140 | 1 | none | End Closer (4) | cond: running_style==4&straight_random==1 / pre: (none) / effects: type 27 value 2500 / base_time: 30000 |
| 201452 | End Closer Straightaways ○ | cha, str | 130 | 1 | 101201, 105001, 103401, 103601, 105002, 105003, 108402, 111902 | End Closer (4) | cond: running_style==4&straight_random==1 / pre: (none) / effects: type 27 value 1500 / base_time: 30000 |
| 201461 | End Closer Corners ◎ | cha, cor | 140 | 1 | none | End Closer (4) | cond: running_style==4&all_corner_random==1 / pre: (none) / effects: type 27 value 2500 / base_time: 30000 |
| 201462 | End Closer Corners ○ | cha, cor | 130 | 1 | 101201, 107401, 110801, 112101, 111501, 111401 | End Closer (4) | cond: running_style==4&all_corner_random==1 / pre: (none) / effects: type 27 value 1500 / base_time: 30000 |
| 201471 | The Coast Is Clear! | cha | 110 | 2 | 100701 | End Closer (4) | cond: running_style==4&is_move_lane==1@running_style==4&is_move_lane==2 / pre: (none) / effects: type 8 value 100000 / base_time: 30000 |
| 201472 | I Can See Right Through You | cha | 110 | 1 | 103402, 100701 | End Closer (4) | cond: running_style==4&is_move_lane==1@running_style==4&is_move_lane==2 / pre: (none) / effects: type 8 value 50000 / base_time: 30000 |
| 201481 | Go-Home Specialist | cha, slo | 170 | 2 | 104401, 100701 | End Closer (4) | cond: running_style==4&slope==2&accumulatetime>=10 / pre: (none) / effects: type 9 value 550 / base_time: 0 |
| 201482 | After-School Stroll | cha, slo | 170 | 1 | 104401, 100702, 100701 | End Closer (4) | cond: running_style==4&slope==2&accumulatetime>=10 / pre: (none) / effects: type 9 value 150 / base_time: 0 |
| 201491 | Serenity | cha | 180 | 2 | 103301 | End Closer (4) | cond: running_style==4&infront_near_lane_time>=1&accumulatetime>=10 / pre: (none) / effects: type 9 value 550 / base_time: 0 |
| 201492 | Levelheaded | cha | 180 | 1 | 103301, 103601 | End Closer (4) | cond: running_style==4&infront_near_lane_time>=1&accumulatetime>=10 / pre: (none) / effects: type 9 value 150 / base_time: 0 |
| 201501 | Crusader | cha, l_2 | 110 | 2 | none | End Closer (4) | cond: running_style==4&phase_random==2&order_rate>50 / pre: (none) / effects: type 8 value 150000 / base_time: 30000 |
| 201502 | Strategist | cha, l_2 | 110 | 1 | 103601 | End Closer (4) | cond: running_style==4&phase_random==2&order_rate>50 / pre: (none) / effects: type 8 value 50000 / base_time: 30000 |
| 201511 | Petrifying Gaze | dbf, cha, l_2 | 180 | 2 | 104401 | End Closer (4) | cond: running_style==4&phase_random==2&order>=2 / pre: (none) / effects: type 21 value -2500 / base_time: 30000 |
| 201512 | Intense Gaze | dbf, cha, l_2 | 180 | 1 | 104401, 111701 | End Closer (4) | cond: running_style==4&phase_random==2&order>=2 / pre: (none) / effects: type 21 value -1500 / base_time: 30000 |
| 201521 | Front Runner Savvy ◎ | run | 130 | 1 | none | Front Runner (1) | cond: running_style==1 / pre: (none) / effects: type 5 value 600000; type 8 value 100000 / base_time: -1 |
| 201522 | Front Runner Savvy ○ | run | 110 | 1 | 102001, 104601, 102602, 103102, 104102 | Front Runner (1) | cond: running_style==1 / pre: (none) / effects: type 5 value 400000; type 8 value 50000 / base_time: -1 |
| 201531 | Pace Chaser Savvy ◎ | ldr | 130 | 1 | none | Pace Chaser (2) | cond: running_style==2 / pre: (none) / effects: type 5 value 600000; type 8 value 100000 / base_time: -1 |
| 201532 | Pace Chaser Savvy ○ | ldr | 110 | 1 | 101001, 101802, 102302, 106901, 103802, 101303, 109801, 110001 | Pace Chaser (2) | cond: running_style==2 / pre: (none) / effects: type 5 value 400000; type 8 value 50000 / base_time: -1 |
| 201541 | Late Surger Savvy ◎ | btw | 130 | 1 | none | Late Surger (3) | cond: running_style==3 / pre: (none) / effects: type 5 value 600000; type 8 value 100000 / base_time: -1 |
| 201542 | Late Surger Savvy ○ | btw | 110 | 1 | 101102, 103701, 104002, 106002, 105501 | Late Surger (3) | cond: running_style==3 / pre: (none) / effects: type 5 value 400000; type 8 value 50000 / base_time: -1 |
| 201551 | End Closer Savvy ◎ | cha | 130 | 1 | none | End Closer (4) | cond: running_style==4 / pre: (none) / effects: type 5 value 600000; type 8 value 100000 / base_time: -1 |
| 201552 | End Closer Savvy ○ | cha | 110 | 1 | 105002 | End Closer (4) | cond: running_style==4 / pre: (none) / effects: type 5 value 400000; type 8 value 50000 / base_time: -1 |
| 201561 | Super Lucky Seven | nac | 110 | 2 | 105601 | not style gated | cond: random_lot==50&post_number==7 / pre: (none) / effects: type 1 value 600000; type 2 value 600000; type 3 value 600000 / base_time: -1 |
| 201562 | Lucky Seven | nac | 110 | 1 | 100702, 105601 | not style gated | cond: random_lot==50&post_number==7 / pre: (none) / effects: type 1 value 400000; type 2 value 400000; type 3 value 400000 / base_time: -1 |
| 201571 | Triple 7s | nac | 160 | 1 | 100402, 100602, 105601 | not style gated | cond: remain_distance<=778&remain_distance>=776 / pre: (none) / effects: type 9 value 150 / base_time: 0 |
| 201581 | Highlander | slo, nac | 160 | 1 | 103001, 102501, 103003 | not style gated | cond: up_slope_random==1 / pre: (none) / effects: type 31 value 2000 / base_time: 30000 |
| 201591 | Uma Stan | nac | 160 | 1 | 101901, 101902, 105701, 110201, 103203, 107702, 105702, 105502, 113501, 113401, 100701 | not style gated | cond: near_count>=3&accumulatetime>=5 / pre: (none) / effects: type 27 value 1500 / base_time: 30000 |
| 201601 | Groundwork | nac | 100 | 1 | 102602, 108701, 106802, 103102, 106402, 108001, 106502, 102403, 109001, 110902, 108002, 112402 | not style gated | cond: activate_count_start>=3 / pre: (none) / effects: type 31 value 2000 / base_time: 30000 |
| 201611 | Tail Held High | nac | 100 | 1 | 100102, 102001, 102101, 103401, 102102, 107601, 100202, 100303, 110701, 108901, 106803, 108502, 107202 | not style gated | cond: activate_count_middle>=3 / pre: (none) / effects: type 27 value 1500 / base_time: 30000 |
| 201621 | Shake It Out | nac | 100 | 1 | 100602, 105202, 106201 | not style gated | cond: activate_count_end_after>=3 / pre: (none) / effects: type 9 value 150 / base_time: 0 |
| 201631 | Sympathy | nac | 70 | 1 | 110502 | not style gated | cond: same_skill_horse_count>=5 / pre: (none) / effects: type 1 value 400000 / base_time: -1 |
| 201641 | Lone Wolf | nac | 70 | 1 | 101601, 105001 | not style gated | cond: same_skill_horse_count==1 / pre: (none) / effects: type 1 value 400000 / base_time: -1 |
| 201651 | Slipstream | nac | 160 | 1 | 104001, 103101, 106501, 110501, 108901, 106003, 110201, 107202, 104402, 110602, 105502, 111902 | not style gated | cond: infront_near_lane_time>=3&accumulatetime>=10 / pre: (none) / effects: type 27 value 1500 / base_time: 30000 |
| 201661 | Playtime's Over! | nac | 160 | 1 | 106401, 110201, 112401, 103103 | not style gated | cond: behind_near_lane_time>=3&accumulatetime>=10 / pre: (none) / effects: type 27 value 1500 / base_time: 30000 |
| 100381 | #LookatCurren | nac | absent | 5 | 103801 | not style gated | cond: distance_rate>=50&distance_rate<=65&order>=2&order_rate<=50&change_order_onetime<0 / pre: (none) / effects: type 27 value 2500; type 31 value 3000 / base_time: 50000 |
| 201011 | Adored by All | dbf, sho, l_0 | 170 | 2 | 103801, 103802 | not style gated | cond: distance_type==1&phase_random==0&order_rate<=50&accumulatetime>=5 / pre: (none) / effects: type 21 value -2500 / base_time: 30000 |
| 100501 | Nemesis | f_s, f_c, nac | absent | 5 | 105001 | not style gated | cond: is_finalcorner==1&order_rate>=40&order_rate<=75&is_overtake==1 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 100461 | SPARKLY☆STARDOM | l_1, str, nac | absent | 5 | 104601 | not style gated | cond: phase==1&corner==0&order<=2&bashin_diff_behind<=1 / pre: (none) / effects: type 27 value 2500; type 31 value 3000 / base_time: 50000 |
| 201671 | Trending in the Charts! | dir, l_1 | 180 | 2 | 104601, 104602, 109901 | not style gated | cond: ground_type==2&phase==1&blocked_side_continuetime>=2 / pre: (none) / effects: type 27 value 3500 / base_time: 24000 |
| 201672 | Top Pick | dir, l_1 | 180 | 1 | 104601, 104602, 109901 | not style gated | cond: ground_type==2&phase==1&blocked_side_continuetime>=2 / pre: (none) / effects: type 27 value 1500 / base_time: 24000 |
| 100161 | Shadow Break | f_s, f_c, nac | absent | 5 | 101601 | not style gated | cond: is_finalcorner==1&order>=2&order_rate<=75&is_behind_in==1&change_order_onetime<0 / pre: phase==1&blocked_side_continuetime>=2 / effects: type 27 value 4500 / base_time: 50000  ++  cond: is_finalcorner==1&order>=2&order_rate<=75&is_behind_in==1&change_order_onetime<0 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 110181 | Eternal Moments | l_1, nac | absent | 5 | 101802 | not style gated | cond: phase==1&order>=3&order_rate<=50&is_overtake==1 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 110241 | Flowery☆Maneuver | f_s, f_c, nac | absent | 5 | 102402 | not style gated | cond: is_finalcorner==1&order_rate<=40&change_order_onetime<0 / pre: (none) / effects: type 27 value 3500 / base_time: 50000  ++  cond: is_finalcorner==1&order_rate>=50&order_rate<=80&change_order_onetime<0 / pre: (none) / effects: type 31 value 4000 / base_time: 40000 |
| 201261 | Sixth Sense | run, l_0 | 110 | 2 | none | Front Runner (1) | cond: running_style==1&phase==0&blocked_front_continuetime>=1@running_style==1&phase==0&blocked_side_continuetime>=1 / pre: (none) / effects: type 28 value 350; type 35 value 5000 / base_time: 30000 |
| 100201 | Angling and Scheming | l_2, l_3, cor, nac | absent | 5 | 102001 | not style gated | cond: phase>=2&corner!=0&order==1 / pre: (none) / effects: type 31 value 4000 / base_time: 40000 |
| 100121 | You and Me! One-on-One! | nac | absent | 5 | 101201 | not style gated | cond: is_last_straight==1 / pre: is_finalcorner==1&is_behind_in==1&change_order_onetime<0&order_rate>=40 / effects: type 27 value 3500 / base_time: 50000 |
| 110111 | Superior Heal | l_1, nac | absent | 5 | 101102 | not style gated | cond: phase==1&change_order_onetime>0&order_rate>=40 / pre: (none) / effects: type 9 value 750 / base_time: 0 |
| 110141 | Condor's Fury | f_c, cor, nac | absent | 5 | 101402 | not style gated | cond: is_finalcorner==1&corner!=0&is_overtake==1&order>=4&order_rate<=75 / pre: (none) / effects: type 31 value 4000 / base_time: 40000 |
| 100051 | Lights of Vaudeville | nac | absent | 5 | 100501 | not style gated | cond: remain_distance<=300 / pre: phase>=2&order_rate<=40&behind_near_lane_time_set1>=1 / effects: type 27 value 4500 / base_time: 50000 |
| 100401 | KEEP IT REAL. | nac | absent | 5 | 104001 | not style gated | cond: distance_rate>=50&order_rate>=40&order_rate<=80&is_overtake==1 / pre: (none) / effects: type 27 value 2500 / base_time: 60000 |
| 110011 | Dazzl'n ♪ Diver | l_1, nac | absent | 5 | 100102 | not style gated | cond: phase==1&order>=2&order_rate<=80&activate_count_middle>=2 / pre: (none) / effects: type 9 value 550; type 27 value 2500 / base_time: 50000 |
| 110041 | A Kiss for Courage | nac | absent | 5 | 100402 | not style gated | cond: distance_rate>=50&activate_count_heal>=1&order<=3 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 100581 | I Never Goof Up! | l_2, l_3, nac | absent | 5 | 105801 | not style gated | cond: phase>=2&change_order_onetime<0 / pre: (none) / effects: type 27 value 2500; type 31 value 3000 / base_time: 50000 |
| 100371 | Schwarzes Schwert | nac | absent | 5 | 103701 | not style gated | cond: temptation_count==0&order>=3&is_last_straight==1&order_rate_in80_continue==1&order_rate_out40_continue==1 / pre: (none) / effects: type 27 value 4500 / base_time: 50000  ++  cond: temptation_count==0&order>=3&is_last_straight==1 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 110561 | Bountiful Harvest | nac | absent | 5 | 105602 | not style gated | cond: distance_rate>=50&order_rate>=40&overtake_target_time>=2 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 210011 | Burning Spirit SPD | l_1, nac | 200 | 2 | none | not style gated | cond: phase_random==1 / pre: (none) / effects: type 27 value 3500 / base_time: 18000 |
| 210012 | Ignited Spirit SPD | l_1, nac | 200 | 1 | none | not style gated | cond: phase_random==1 / pre: (none) / effects: type 27 value 1500 / base_time: 18000 |
| 210021 | Burning Spirit STA | l_1, nac | 200 | 2 | none | not style gated | cond: phase_random==1 / pre: (none) / effects: type 9 value 550 / base_time: 0 |
| 210022 | Ignited Spirit STA | l_1, nac | 200 | 1 | none | not style gated | cond: phase_random==1 / pre: (none) / effects: type 9 value 150 / base_time: 0 |
| 210031 | Burning Spirit PWR | l_2, nac | 200 | 2 | none | not style gated | cond: phase_random==2 / pre: (none) / effects: type 31 value 4000 / base_time: 12000 |
| 210032 | Ignited Spirit PWR | l_2, nac | 200 | 1 | none | not style gated | cond: phase_random==2 / pre: (none) / effects: type 31 value 2000 / base_time: 12000 |
| 210041 | Burning Spirit GUTS | l_2, nac | 200 | 2 | none | not style gated | cond: phase_random==2 / pre: (none) / effects: type 27 value 2500; type 31 value 3000 / base_time: 18000 |
| 210042 | Ignited Spirit GUTS | l_2, nac | 200 | 1 | none | not style gated | cond: phase_random==2 / pre: (none) / effects: type 27 value 500; type 31 value 1000 / base_time: 18000 |
| 210051 | Burning Spirit WIT | l_0, nac | 200 | 2 | none | not style gated | cond: phase_random==0 / pre: (none) / effects: type 28 value 350; type 8 value 150000 / base_time: 40000 |
| 210052 | Ignited Spirit WIT | l_0, nac | 200 | 1 | none | not style gated | cond: phase_random==0 / pre: (none) / effects: type 28 value 150; type 8 value 50000 / base_time: 40000 |
| 100281 | YUMMY☆SPEED! | sho, nac | absent | 5 | 102801 | not style gated | cond: distance_rate>=45&distance_rate<=60&order>=2&order_rate<=50&is_overtake==1&distance_type==1 / pre: (none) / effects: type 22 value 2500 / base_time: 50000  ++  cond: distance_rate>=45&distance_rate<=60&order>=2&order_rate<=50&is_overtake==1 / pre: (none) / effects: type 27 value 2500 / base_time: 50000 |
| 100191 | OMG! (ﾟ∀ﾟ)  The Final Sprint! ☆ | nac | absent | 5 | 101901 | not style gated | cond: change_order_up_end_after>=2 / pre: (none) / effects: type 27 value 3500; type 28 value 350 / base_time: 50000 |
| 201592 | Superstan | nac | 160 | 2 | 101901, 103203 | not style gated | cond: near_count>=3&accumulatetime>=5 / pre: (none) / effects: type 27 value 3500 / base_time: 30000 |
| 201681 | Lead the Charge! | dir, l_2 | 140 | 2 | 101901 | not style gated | cond: ground_type==2&phase_random==2 / pre: (none) / effects: type 28 value 350; type 31 value 3000 / base_time: 30000 |
| 201682 | Forward, March! | dir, l_2 | 140 | 1 | 101901, 101902 | not style gated | cond: ground_type==2&phase_random==2 / pre: (none) / effects: type 28 value 250; type 31 value 2000 / base_time: 30000 |
| 110301 | Every Rose Has Its Fangs | dbf, l_1, nac | absent | 5 | 103002 | not style gated | cond: phase==1&order>=2&order_rate<=50 / pre: (none) / effects: type 9 value 550; type 9 value -50; type 27 value 2500 / base_time: 50000 |
| 110451 | Give Mummy a Hug ♡ | nac | absent | 5 | 104502 | not style gated | cond: is_last_straight==1&order<=4&bashin_diff_infront<=1 / pre: (none) / effects: type 27 value 2500; type 31 value 3000 / base_time: 50000 |
| 200772 | Tantalizing Trick | dbf, l_1, nac | 140 | 2 | 103002, 100502 | not style gated | cond: phase==1&order_rate<=50&temptation_opponent_count_behind>=1 / pre: (none) / effects: type 9 value -300 / base_time: 0 |
| 201691 | Lie in Wait | btw, l_0 | 180 | 2 | 104002, 106701, 106202 | Late Surger (3) | cond: running_style==3&phase_laterhalf_random==0&order_rate>=40 / pre: (none) / effects: type 9 value 550 / base_time: 0 |
| 201692 | Be Still | btw, l_0 | 180 | 1 | 104002, 106701, 108301, 106202 | Late Surger (3) | cond: running_style==3&phase_laterhalf_random==0&order_rate>=40 / pre: (none) / effects: type 9 value 150 / base_time: 0 |
| 300011 | Unquenched Thirst | nac | absent | 1 | none | not style gated | cond: track_id==10008 / pre: (none) / effects: type 2 value 400000 / base_time: -1 |
| 300021 | Unchanging | nac | absent | 2 | none | not style gated | cond: season==4 / pre: (none) / effects: type 1 value 800000 / base_time: -1 |
| 100391 | A Princess Must Seize Victory! | nac | absent | 5 | 103901 | not style gated | cond: is_last_straight==1&blocked_side_continuetime>=2 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 100251 | Chasing After You | dbf, nac | absent | 5 | 102501 | not style gated | cond: distance_rate>=50&order_rate>=40&order_rate<=70 / pre: (none) / effects: type 27 value 2500; type 21 value -500 / base_time: 60000 |
| 201701 | Come What May | med, l_2, l_3 | 160 | 2 | 107101, 103502, 110501, 107001, 111001, 113601 | not style gated | cond: distance_type==3&is_last_straight==1&order_rate>=20&order_rate<=60&phase>=2 / pre: (none) / effects: type 27 value 3500 / base_time: 30000 |
| 201702 | All I've Got | med, l_2, l_3 | 160 | 1 | 104801, 107101, 103502, 104901, 110501, 105501, 107001, 103202, 108302, 111001, 109601, 111002, 113601, 106301 | not style gated | cond: distance_type==3&is_last_straight==1&order_rate>=20&order_rate<=60&phase>=2 / pre: (none) / effects: type 27 value 1500 / base_time: 30000 |
| 110171 | Arrows Whistle, Shadows Disperse | f_s, f_c, nac | absent | 5 | 101702 | not style gated | cond: is_finalcorner==1 / pre: phase>=2&order_rate<=50&overtake_target_time>=2 / effects: type 27 value 3500 / base_time: 60000 |
| 110401 | Dancing in the Leaves | f_c, cor, nac | absent | 5 | 104002 | not style gated | cond: is_finalcorner==1&corner!=0&order_rate>=30&order_rate<=70&blocked_side_continuetime>=2 / pre: (none) / effects: type 27 value 2500; type 31 value 3000 / base_time: 50000 |
| 200194 | Fall Frenzy | nac | 130 | 2 | 101702, 104702 | not style gated | cond: season==3 / pre: (none) / effects: type 1 value 600000; type 3 value 600000 / base_time: -1 |
| 100481 | Pop & Polish | nac | absent | 5 | 104801 | not style gated | cond: is_last_straight==1&order_rate<=40&overtake_target_time>=1@is_last_straight==1&order_rate<=40&is_overtake==1 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 201801 | ♡ 3D Nail Art | nac | 50 | 1 | none | not style gated | cond: ground_condition==1 / pre: (none) / effects: type 1 value -400000 / base_time: -1 |
| 100591 | Moving Past, and Beyond | l_2, l_3, cor, l_1, f_c, nac | absent | 5 | 105901 | not style gated | cond: phase>=2&corner!=0&is_finalcorner==0&temptation_count==0&order_rate>=50&order_rate<=70@phase==1&corner!=0&is_finalcorner==1&temptation_count==0&order_rate>=50&order_rate<=70 / pre: (none) / effects: type 31 value 4000 / base_time: 40000 |
| 200064 | Yodo Invicta | nac | 130 | 2 | 105901, 103003 | not style gated | cond: track_id==10008 / pre: (none) / effects: type 2 value 600000; type 5 value 600000; type 1 value 600000 / base_time: -1 |
| 110061 | Festive Miracle | nac | absent | 5 | 100602 | not style gated | cond: activate_count_heal>=3&distance_rate>=50 / pre: (none) / effects: type 27 value 2500; type 31 value 3000; type 9 value 350 / base_time: 50000 |
| 110231 | Presents from X | l_1, nac | absent | 5 | 102302 | not style gated | cond: order_rate_in50_continue==1&phase==1&distance_rate>=50&order>=2&order_rate<=40 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 201201 | VIP Pass | lng | 160 | 2 | 102302 | not style gated | cond: distance_type==4&is_overtake==1&accumulatetime>=5 / pre: (none) / effects: type 9 value 550 / base_time: 0 |
| 201902 | Head-On | ldr, l_2 | 180 | 1 | 100502, 102202, 101002, 104201, 105802, 102102, 107102, 103202, 109301, 108601, 100303, 110901, 112401, 108602, 111301, 109302, 109501, 102802, 109201 | Pace Chaser (2) | cond: running_style==2&phase_firsthalf_random==2&order_rate<=50 / pre: (none) / effects: type 31 value 2000 / base_time: 18000 |
| 100221 | Fairy Tale | nac | absent | 5 | 102201 | not style gated | cond: distance_rate>=50&order>=2&order_rate<=40&blocked_side_continuetime>=2 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 202002 | Familiar Ground | dir, l_1 | 180 | 1 | 103401 | not style gated | cond: ground_type==2&phase_random==1&order_rate>=40 / pre: (none) / effects: type 9 value 150 / base_time: 0 |
| 100211 | White Lightning Comin' Through! | str, nac | absent | 5 | 102101 | not style gated | cond: distance_rate>=50&corner==0&order_rate>=70&order_rate<=75&is_overtake==1@distance_rate>=50&corner==0&order_rate<=30&order_rate>=20 / pre: (none) / effects: type 27 value 3500; type 31 value 1000 / base_time: 50000 |
| 201612 | Tail Nine | nac | 180 | 2 | 102101, 103401, 110701 | not style gated | cond: activate_count_middle>=3 / pre: (none) / effects: type 27 value 3500 / base_time: 30000 |
| 300031 | Towards the Scenery I Seek | nac | absent | 1 | none | not style gated | cond: always==1 / pre: (none) / effects: type 1 value 400000 / base_time: -1 |
| 300041 | Creeping Anxiety | nac | absent | 1 | none | not style gated | cond: always==1 / pre: (none) / effects: type 1 value -400000 / base_time: -1 |
| 300051 | Blatant Fear | nac | absent | 2 | none | not style gated | cond: remain_distance>=800&remain_distance<=850 / pre: (none) / effects: type 21 value -3000 / base_time: 50000 |
| 300061 | Dream Run | nac | absent | 2 | none | not style gated | cond: is_last_straight==1 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 300071 | Show Me What Lies Beyond! | nac | absent | 1 | none | not style gated | cond: is_exist_chara_id==1002 / pre: (none) / effects: type 1 value 400000 / base_time: -1 |
| 300081 | Hoiya! Have a Good Run! | nac | absent | 1 | none | not style gated | cond: is_exist_chara_id==1002 / pre: (none) / effects: type 2 value 400000 / base_time: -1 |
| 300091 | As a Friend and Rival | nac | absent | 1 | none | not style gated | cond: is_exist_chara_id==1002 / pre: (none) / effects: type 3 value 400000 / base_time: -1 |
| 300101 | Cheers of a Fellow Dreamer | nac | absent | 2 | none | not style gated | cond: is_exist_chara_id==1002&remain_distance_viewer_id>=800&remain_distance_viewer_id<=850 / pre: (none) / effects: type 9 value 550 / base_time: 0 |
| 110151 | Barcarole of Blessings | nac | absent | 5 | 101502 | not style gated | cond: remain_distance<=401&remain_distance>=399&order_rate<=40&activate_count_all>=7 / pre: (none) / effects: type 27 value 4500 / base_time: 50000  ++  cond: remain_distance<=401&remain_distance>=399&order_rate<=40&activate_count_all<=6 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 110521 | 114th Time's the Charm | f_c, cor, nac | absent | 5 | 105202 | not style gated | cond: is_finalcorner==1&corner!=0&distance_diff_top>=7 / pre: (none) / effects: type 27 value 2500 / base_time: 50000 |
| 202011 | Headliner | lng, l_1 | 160 | 2 | 101502, 101303, 107701, 112702 | not style gated | cond: distance_type==4&phase_laterhalf_random==1&order_rate<=50 / pre: (none) / effects: type 27 value 3500 / base_time: 24000 |
| 202012 | Feature Act | lng, l_1 | 160 | 1 | 101502, 106701, 101303, 100902, 107701, 103003, 112702 | not style gated | cond: distance_type==4&phase_laterhalf_random==1&order_rate<=50 / pre: (none) / effects: type 27 value 1500 / base_time: 24000 |
| 202021 | Daring Strike | cha, l_1 | 180 | 2 | 101202 | End Closer (4) | cond: running_style==4&phase_random==1&order_rate>=40 / pre: (none) / effects: type 27 value 2500 / base_time: 40000 |
| 202022 | Early Start | cha, l_1 | 180 | 1 | 101202 | End Closer (4) | cond: running_style==4&phase_random==1&order_rate>=40 / pre: (none) / effects: type 27 value 500 / base_time: 40000 |
| 202031 | Nothing Ventured | nac | 120 | 2 | 104901 | not style gated | cond: distance_rate_after_random==50 / pre: (none) / effects: type 27 value 4500; type 9 value -10000 / base_time: 18000 |
| 202032 | Risky Business | nac | 120 | 1 | 100702, 104901 | not style gated | cond: distance_rate_after_random==50 / pre: (none) / effects: type 27 value 2500; type 9 value -10000 / base_time: 18000 |
| 1000011 | Carnival Bonus | nac | absent | 1 | none | not style gated | cond: (none) / pre: (none) / effects: type 501 value 10000 / base_time: -1 |
| 100691 | Ambition to Surpass the Sakura | nac | absent | 5 | 106901 | not style gated | cond: remain_distance<=300&order_rate<=40&bashin_diff_infront<=1 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 200174 | Spring Spectacle | nac | 130 | 2 | 106901, 106102 | not style gated | cond: season==1@season==5 / pre: (none) / effects: type 1 value 600000; type 3 value 600000 / base_time: -1 |
| 100711 | A Lifelong Dream, A Moment's Flight | nac | absent | 5 | 107101 | not style gated | cond: is_last_straight==1&bashin_diff_behind<=1 / pre: (none) / effects: type 27 value 4500; type 9 value -100 / base_time: 40000 |
| 110261 | Operation Cacao | l_1, cor, nac | absent | 5 | 102602 | not style gated | cond: order<=4&phase==1&corner!=0&bashin_diff_behind<=3 / pre: (none) / effects: type 27 value 3500; type 9 value 150 / base_time: 50000 |
| 110371 | Guten Appetit ♪ | nac | absent | 5 | 103702 | not style gated | cond: change_order_up_finalcorner_after>=2&is_last_straight==1 / pre: (none) / effects: type 27 value 3500 / base_time: 60000 |
| 201103 | Flash Forward | med, str | 150 | 2 | 103702, 107102 | not style gated | cond: distance_type==3&straight_random==1 / pre: (none) / effects: type 27 value 3500 / base_time: 30000 |
| 202041 | In High Spirits | sho, l_1 | 160 | 2 | 108701, 104102 | not style gated | cond: distance_type==1&phase_laterhalf_random==1&order_rate<=50 / pre: (none) / effects: type 27 value 3500 / base_time: 24000 |
| 202042 | Light as a Feather | sho, l_1 | 160 | 1 | 104201, 108701, 104102, 109301 | not style gated | cond: distance_type==1&phase_laterhalf_random==1&order_rate<=50 / pre: (none) / effects: type 27 value 1500 / base_time: 24000 |
| 100331 | Shooting Star of Dioskouroi | nac | absent | 5 | 103301 | not style gated | cond: is_last_straight==1&distance_diff_top>=5&order_rate<80 / pre: (none) / effects: type 27 value 3500 / base_time: 50000  ++  cond: is_last_straight==1&distance_diff_top>=5&order_rate>=80 / pre: (none) / effects: type 27 value 4500 / base_time: 50000 |
| 10621 | Ready, Go! | l_1, nac | absent | 3 | 106201 | not style gated | cond: distance_rate>=50&phase==1&order>=3&order_rate<=70 / pre: (none) / effects: type 9 value 350; type 27 value 1500 / base_time: 50000 |
| 100621 | Go, Go, Mun! | l_1, nac | absent | 4 | 106201 | not style gated | cond: distance_rate>=50&phase==1&order>=3&order_rate<=70 / pre: (none) / effects: type 9 value 550; type 27 value 2500 / base_time: 50000 |
| 100681 | Victory Cheer! | l_2, str, nac, cor | absent | 5 | 106801 | not style gated | cond: phase==2&straight_front_type==2&order<=2 / pre: (none) / effects: type 27 value 2500; type 31 value 3000 / base_time: 50000  ++  cond: distance_rate>=50&corner==3&order<=2 / pre: (none) / effects: type 27 value 2500 / base_time: 50000 |
| 200154 | Firm Course Menace | nac | 130 | 2 | 107701 | not style gated | cond: ground_condition==1 / pre: (none) / effects: type 3 value 600000; type 1 value 600000 / base_time: -1 |
| 201173 | Blast Forward | lng, str | 150 | 2 | 106801, 106402 | not style gated | cond: distance_type==4&straight_random==1 / pre: (none) / effects: type 27 value 3500 / base_time: 30000 |
| 202051 | Runaway | run | 200 | 2 | none | Front Runner (1) | cond: running_style==1 / pre: (none) / effects: type 6 value 0 / base_time: -1 |
| 210061 | Radiant Star | nac | 200 | 2 | none | not style gated | cond: distance_rate_after_random==50 / pre: (none) / effects: type 27 value 2500; type 31 value 3000; type 9 value 350 / base_time: 12000 |
| 210062 | Glittering Star | nac | 200 | 1 | none | not style gated | cond: distance_rate_after_random==50 / pre: (none) / effects: type 27 value 500; type 31 value 1000; type 9 value 50 / base_time: 12000 |
| 100671 | Eternal Encompassing Shine | f_s, nac | absent | 5 | 106701 | not style gated | cond: is_last_straight_onetime==1&order>=2&order<=5&distance_diff_top<=5 / pre: (none) / effects: type 27 value 4500 / base_time: 50000  ++  cond: is_last_straight_onetime==1&order>=2&order<=5&distance_diff_top>5 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 200014 | Right-Handed Demon | nac | 130 | 2 | 106701, 111901 | not style gated | cond: rotation==1 / pre: (none) / effects: type 1 value 600000; type 3 value 600000 / base_time: -1 |
| 100741 | Lovely Spring Breeze | nac | absent | 5 | 107401 | not style gated | cond: distance_rate>=50&order_rate>=40&order_rate<=80 / pre: (none) / effects: type 27 value 1500 / base_time: 50000 |
| 202061 | Best in Japan | lng, f_c | 360 | 2 | 100103 | not style gated | cond: distance_type==4&is_finalcorner_random==1 / pre: (none) / effects: type 27 value 3500 / base_time: 30000 |
| 202071 | Of Calm Mind | lng, l_1 | 170 | 2 | 107401 | not style gated | cond: distance_type==4&phase_firsthalf_random==1&order_rate>=40&order_rate<=80 / pre: (none) / effects: type 9 value 750; type 21 value -1500 / base_time: 12000 |
| 202072 | Free-Spirited | lng, l_1 | 170 | 1 | 107401, 103602 | not style gated | cond: distance_type==4&phase_firsthalf_random==1&order_rate>=40&order_rate<=80 / pre: (none) / effects: type 9 value 350; type 21 value -1500 / base_time: 12000 |
| 300111 | Chin Up, Derby Umamusume! | nac | absent | 1 | none | not style gated | cond: always==1 / pre: (none) / effects: type 1 value 400000 / base_time: -1 |
| 300121 | For the Team | nac | absent | 2 | none | not style gated | cond: always==1 / pre: (none) / effects: type 2 value 800000 / base_time: -1 |
| 110051 | Ravissant | f_s, f_c, nac | absent | 5 | 100502 | not style gated | cond: is_finalcorner==1&change_order_onetime<0&order_rate<=40 / pre: (none) / effects: type 27 value 4500 / base_time: 40000 |
| 110201 | Break It Down! | l_2, l_3, str, nac | absent | 5 | 102002 | not style gated | cond: phase>=2&corner==0&order<=2&straight_front_type==2 / pre: (none) / effects: type 27 value 3500; type 31 value 2000 / base_time: 50000  ++  cond: phase>=2&corner==0&order<=2 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 201271 | Top Runner | run, l_0, l_1 | 180 | 2 | 102002 | Front Runner (1) | cond: running_style==1&phase<=1&change_order_onetime>0&accumulatetime>=5@running_style==1&phase<=1&blocked_side_continuetime>=2&accumulatetime>=5 / pre: (none) / effects: type 27 value 3500 / base_time: 30000 |
| 202081 | From the Brink | med, l_2 | 160 | 2 | 105902, 105002, 106202, 104802, 106703, 108801, 110602 | not style gated | cond: distance_type==3&phase_firsthalf_random==2&order_rate>=40 / pre: (none) / effects: type 31 value 4000 / base_time: 18000 |
| 202082 | Take the Chance | med, l_2 | 160 | 1 | 106002, 104401, 105902, 105002, 106202, 104802, 106703, 108302, 108801, 110502, 109601, 110602 | not style gated | cond: distance_type==3&phase_firsthalf_random==2&order_rate>=40 / pre: (none) / effects: type 31 value 2000 / base_time: 18000 |
| 202091 | Burning Soul | med, l_1 | 160 | 2 | 106902, 108302, 109601 | not style gated | cond: distance_type==3&phase==1&order_rate<=80&order_rate>=30&blocked_side_continuetime>=2 / pre: (none) / effects: type 27 value 3500; type 9 value 350 / base_time: 24000 |
| 202092 | Fighting Spirit | med, l_1 | 160 | 1 | 107201, 106902, 108302, 109601, 109402 | not style gated | cond: distance_type==3&phase==1&order_rate<=80&order_rate>=30&blocked_side_continuetime>=2 / pre: (none) / effects: type 27 value 1500; type 9 value 50 / base_time: 24000 |
| 202101 | Elated | med, l_1 | 160 | 2 | 103601, 100702, 105501, 103503, 111101 | not style gated | cond: distance_type==3&phase_random==1&order_rate>=40 / pre: (none) / effects: type 27 value 3500 / base_time: 24000 |
| 202102 | Eager | med, l_1 | 160 | 1 | 103601, 100702, 105501, 103902, 103503, 111101 | not style gated | cond: distance_type==3&phase_random==1&order_rate>=40 / pre: (none) / effects: type 27 value 1500 / base_time: 24000 |
| 100511 | Budding Blossom | l_2, l_3, f_c, f_s, str, nac | absent | 5 | 105101 | not style gated | cond: phase>=2&is_finalcorner_laterhalf==1&order>=3&order_rate<=40@phase>=2&is_finalcorner==1&corner==0&order>=3&order_rate<=40 / pre: phase==1&blocked_side_continuetime>=2&corner!=0 / effects: type 31 value 4000 / base_time: 40000 |
| 201901 | Neck and Neck | ldr, l_2 | 180 | 2 | 102202, 101002, 105802, 109301, 100303, 110901, 111301, 102802 | Pace Chaser (2) | cond: running_style==2&phase_firsthalf_random==2&order_rate<=50 / pre: (none) / effects: type 31 value 4000 / base_time: 18000 |
| 1000012 | Carnival Bonus | nac | absent | 1 | none | not style gated | cond: (none) / pre: (none) / effects: type 502 value 10000; type 503 value 10000 / base_time: -1 |
| 100721 | Peerless Dance of Flowering Flames | nac | absent | 5 | 107201 | not style gated | cond: remain_distance<=300&order_rate<=40 / pre: is_finalcorner==1&change_order_onetime<0 / effects: type 27 value 3500 / base_time: 50000 |
| 110601 | Go☆Go☆Goal! | nac | absent | 5 | 106002 | not style gated | cond: is_last_straight==1&order_rate>=40&order_rate<=70&popularity>=4 / pre: is_finalcorner==1&change_order_onetime<0 / effects: type 27 value 3500 / base_time: 60000  ++  cond: is_last_straight==1&order_rate>=40&order_rate<=70&popularity<4 / pre: is_finalcorner==1&change_order_onetime<0 / effects: type 27 value 3500 / base_time: 50000 |
| 110611 | Louder! Tracen Cheer! | nac | absent | 5 | 106102 | not style gated | cond: is_last_straight==1 / pre: distance_rate>=50&order_rate_out70_continue==1&temptation_count==0 / effects: type 31 value 4000 / base_time: 40000 |
| 202111 | Full of Vigor | mil, f_c | 160 | 2 | 105301 | not style gated | cond: distance_type==2&is_finalcorner_random==1&order_rate>=40 / pre: (none) / effects: type 27 value 3500 / base_time: 18000 |
| 202112 | Pumped | mil, f_c | 160 | 1 | 105301 | not style gated | cond: distance_type==2&is_finalcorner_random==1&order_rate>=40 / pre: (none) / effects: type 27 value 1500 / base_time: 18000 |
| 202121 | Dauntless | btw | 180 | 2 | 105902, 104802, 109101, 109601 | Late Surger (3) | cond: running_style==3&distance_rate_after_random==50&order_rate>=30&order_rate<=80 / pre: (none) / effects: type 27 value 3500; type 31 value 1000 / base_time: 24000 |
| 202122 | Fearless | btw | 180 | 1 | 105902, 105301, 106702, 104802, 105302, 109101, 109601 | Late Surger (3) | cond: running_style==3&distance_rate_after_random==50&order_rate>=30&order_rate<=80 / pre: (none) / effects: type 27 value 1500; type 31 value 500 / base_time: 24000 |
| 1100011 | Feelin' a Bit Silly | nac | absent | 1 | none | not style gated | cond: always==1 / pre: (none) / effects: type 31 value -180000; type 14 value 1000 / base_time: 20000 |
| 100311 | All Charged! It's Go Time! | slo, nac | absent | 5 | 103101 | not style gated | cond: remain_distance<=299&remain_distance>=295&order<=2&slope==0@remain_distance<=299&remain_distance>=295&order<=2&slope==2 / pre: remain_distance<=305&remain_distance>=300&slope==1 / effects: type 27 value 4500 / base_time: 50000  ++  cond: remain_distance<=299&remain_distance>=295&order<=2 / pre: (none) / effects: type 27 value 2500 / base_time: 50000 |
| 202131 | Wild Wind | dbf, med, l_1 | 160 | 2 | 103101 | not style gated | cond: distance_type==3&phase_random==1&order<=3 / pre: (none) / effects: type 27 value 3500; type 21 value -1500 / base_time: 18000 |
| 202132 | With All My Soul | dbf, med, l_1 | 160 | 1 | 103101, 104602 | not style gated | cond: distance_type==3&phase_random==1&order<=3 / pre: (none) / effects: type 27 value 1500; type 21 value -350 / base_time: 18000 |
| 100641 | Keep Pushing Ahead | nac | absent | 5 | 106401 | not style gated | cond: distance_rate>=50&order_rate_in20_continue==1 / pre: (none) / effects: type 9 value 550; type 27 value 2500 / base_time: 60000 |
| 201662 | See Ya Later! | nac | 160 | 2 | 106401, 103103 | not style gated | cond: behind_near_lane_time>=3&accumulatetime>=10 / pre: (none) / effects: type 27 value 3500 / base_time: 30000 |
| 110221 | Best Day Ever | l_2, l_3, f_s, f_c, nac | absent | 5 | 102202 | not style gated | cond: remain_distance>=401&phase>=2&is_finalcorner==1&order_rate>=20&order_rate<=40 / pre: (none) / effects: type 27 value 3500; type 31 value 1000 / base_time: 50000  ++  cond: phase>=2&is_finalcorner==1&order_rate>=20&order_rate<=40 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 110381 | One True Color | nac | absent | 5 | 103802 | not style gated | cond: remain_distance<=350&order_rate<=40&order_rate>=20&bashin_diff_behind<=1 / pre: (none) / effects: type 27 value 2500; type 31 value 3000 / base_time: 50000 |
| 202151 | Keep Going! | btw, l_1 | 160 | 2 | 107601, 103703, 101103, 104003, 106301 | Late Surger (3) | cond: running_style==3&phase_random==1 / pre: (none) / effects: type 27 value 4500; type 9 value -200 / base_time: 24000 |
| 202152 | Full Throttle | btw, l_1 | 160 | 1 | 105902, 103502, 100802, 107601, 103703, 101103, 104003, 109102, 106301 | Late Surger (3) | cond: running_style==3&phase_random==1 / pre: (none) / effects: type 27 value 2500; type 9 value -200 / base_time: 24000 |
| 100341 | Now We're Cruisin'! | nac | absent | 5 | 103401 | not style gated | cond: compete_fight_count>0 / pre: distance_rate>=50&order_rate_out40_continue==1 / effects: type 27 value 4500 / base_time: 50000 |
| 202001 | Master of the Sands | dir, l_1 | 200 | 2 | 103401 | not style gated | cond: ground_type==2&phase_random==1&order_rate>=40 / pre: (none) / effects: type 9 value 550; type 27 value 3500 / base_time: 18000 |
| 100441 | Victory belongs to me—Strelitzia! ☆ | nac | absent | 5 | 104401 | not style gated | cond: remain_distance<=300 / pre: order_rate_out50_continue==1&temptation_count==0&is_finalcorner==1 / effects: type 27 value 3500 / base_time: 60000 |
| 202141 | You're Not the Boss of Me! | nac | 0 | 1 | none | not style gated | cond: always==1 / pre: (none) / effects: type 14 value 850; type 5 value -400000 / base_time: -1 |
| 202161 | Restraint | nac | 160 | 1 | 110501, 108302, 106003, 111001, 108702, 113401, 106301 | not style gated | cond: always==1 / pre: (none) / effects: type 5 value 600000; type 29 value -30000 / base_time: -1 |
| 110101 | Joyful Voyage! | nac | absent | 5 | 101002 | not style gated | cond: distance_diff_top<=5&order>=2&order_rate<=40&remain_distance<=201&remain_distance>=199 / pre: (none) / effects: type 27 value 3500; type 22 value 1500 / base_time: 50000  ++  cond: order>=2&order_rate<=40&remain_distance<=201&remain_distance>=199 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 110591 | Wherever This Wonder Leads | slo, l_1, nac | absent | 5 | 105902 | not style gated | cond: distance_rate>=60&slope==2&phase==1&order_rate>=40&order_rate<=80&remain_distance>=500 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 202172 | Downhill Speedster | slo, nac | 170 | 1 | 103502, 102702, 105102, 103902, 109101, 104803, 114901 | not style gated | cond: down_slope_random==1 / pre: (none) / effects: type 27 value 1500 / base_time: 24000 |
| 100361 | trigger:BEAT | nac | absent | 5 | 103601 | not style gated | cond: is_last_straight==1 / pre: is_finalcorner==1&order_rate>=40&order_rate<=75&lane_type==0 / effects: type 27 value 3500; type 28 value 350 / base_time: 50000 |
| 201453 | Moonlit Flash | cha, str | 150 | 2 | 103601, 108402 | End Closer (4) | cond: running_style==4&straight_random==1 / pre: (none) / effects: type 27 value 3500 / base_time: 30000 |
| 202181 | 99 Problems | nac | 0 | 1 | none | not style gated | cond: always==1 / pre: (none) / effects: type 4 value -400000; type 5 value -400000 / base_time: -1 |
| 120011 | Dreams Donned with Pride! | l_2, l_3, f_c, cor, nac | absent | 5 | 100103 | not style gated | cond: phase>=2&is_finalcorner==1&corner!=0&is_activate_any_skill==1 / pre: track_id==10005 / effects: type 27 value 4500 / base_time: 50000  ++  cond: phase>=2&is_finalcorner==1&corner!=0&is_activate_any_skill==1 / pre: (none) / effects: type 27 value 2500 / base_time: 50000 |
| 201113 | Refraction Arc | med, cor | 150 | 2 | 104701, 106703, 107301 | not style gated | cond: distance_type==3&all_corner_random==1 / pre: (none) / effects: type 27 value 3500 / base_time: 30000 |
| 300131 | Ruler of Japan | nac | absent | 5 | none | not style gated | cond: remain_distance>=350&remain_distance<=400 / pre: (none) / effects: type 27 value 3500 / base_time: 30000 |
| 300141 | Indomitable | nac | absent | 5 | none | not style gated | cond: remain_distance>=350&remain_distance<=400 / pre: (none) / effects: type 27 value 3500 / base_time: 30000 |
| 1200011 | Medium Race Enthusiast | med, tur | absent | 1 | none | not style gated | cond: distance_type==3&ground_type==1 / pre: (none) / effects: type 5 value 600000 / base_time: -1 |
| 1200021 | Long Race Enthusiast | lng, tur | absent | 1 | none | not style gated | cond: distance_type==4&ground_type==1 / pre: (none) / effects: type 2 value 600000 / base_time: -1 |
| 1200031 | Mile Race Enthusiast | mil, tur | absent | 1 | none | not style gated | cond: distance_type==2&ground_type==1 / pre: (none) / effects: type 3 value 600000 / base_time: -1 |
| 110071 | 564 Escapades | nac | absent | 5 | 100702 | not style gated | cond: distance_rate_after_random==50 / pre: (none) / effects: type 27 value 1500; type 37 value 20000 / base_time: 50000 |
| 120131 | Your Smile Sparkles as the Waves | nac | absent | 5 | 101303 | not style gated | cond: distance_rate>=50&order_rate<=40 / pre: (none) / effects: type 27 value 1500 / base_time: 50000 |
| 202191 | Heart All Set | ldr, lng, l_1 | 170 | 2 | 101303, 107702 | Pace Chaser (2) | cond: is_badstart==0&running_style==2&distance_type==4&phase_firsthalf_random==1 / pre: (none) / effects: type 9 value 750 / base_time: 0 |
| 202192 | All Set | ldr, lng, l_1 | 170 | 1 | 101303, 101602, 107701, 107702 | Pace Chaser (2) | cond: is_badstart==0&running_style==2&distance_type==4&phase_firsthalf_random==1 / pre: (none) / effects: type 9 value 350 / base_time: 0 |
| 100531 | Red-Hot Discipline! | l_2, l_3, f_s, f_c, nac | absent | 5 | 105301 | not style gated | cond: phase>=2&order_rate>=50&is_finalcorner==1&bashin_diff_infront<=1 / pre: (none) / effects: type 31 value 1000; type 31 value 1000 / base_time: 20000 |
| 100981 | Luck Runs My Way | l_1, nac | absent | 5 | 109801 | not style gated | cond: phase_laterhalf_random==1 / pre: (none) / effects: type 27 value 2500; type 27 value 500; type 31 value 500 / base_time: 50000 |
| 202201 | Kawasaki Racecourse ◎ | nac | 90 | 1 | none | not style gated | cond: track_id==10103 / pre: (none) / effects: type 2 value 600000 / base_time: -1 |
| 202202 | Kawasaki Racecourse ○ | nac | 70 | 1 | 109901 | not style gated | cond: track_id==10103 / pre: (none) / effects: type 2 value 400000 / base_time: -1 |
| 202203 | Kawasaki Racecourse × | nac | 40 | 1 | none | not style gated | cond: track_id==10103 / pre: (none) / effects: type 2 value -400000 / base_time: -1 |
| 202211 | Funabashi Racecourse ◎ | nac | 90 | 1 | none | not style gated | cond: track_id==10104 / pre: (none) / effects: type 2 value 600000 / base_time: -1 |
| 202212 | Funabashi Racecourse ○ | nac | 70 | 1 | none | not style gated | cond: track_id==10104 / pre: (none) / effects: type 2 value 400000 / base_time: -1 |
| 202213 | Funabashi Racecourse × | nac | 40 | 1 | none | not style gated | cond: track_id==10104 / pre: (none) / effects: type 2 value -400000 / base_time: -1 |
| 202221 | Morioka Racecourse ◎ | nac | 90 | 1 | none | not style gated | cond: track_id==10105 / pre: (none) / effects: type 2 value 600000 / base_time: -1 |
| 202222 | Morioka Racecourse ○ | nac | 70 | 1 | none | not style gated | cond: track_id==10105 / pre: (none) / effects: type 2 value 400000 / base_time: -1 |
| 202223 | Morioka Racecourse × | nac | 40 | 1 | none | not style gated | cond: track_id==10105 / pre: (none) / effects: type 2 value -400000 / base_time: -1 |
| 202231 | Night Races ◎ | nac | 90 | 1 | none | not style gated | cond: time==4 / pre: (none) / effects: type 5 value 600000 / base_time: -1 |
| 202232 | Night Races ○ | nac | 70 | 1 | none | not style gated | cond: time==4 / pre: (none) / effects: type 5 value 400000 / base_time: -1 |
| 202233 | Night Races × | nac | 40 | 1 | none | not style gated | cond: time==4 / pre: (none) / effects: type 5 value -400000 / base_time: -1 |
| 202241 | Sharp Turns ◎ | nac | 90 | 1 | none | not style gated | cond: is_tight_track==1 / pre: (none) / effects: type 5 value 600000 / base_time: -1 |
| 202242 | Sharp Turns ○ | nac | 70 | 1 | none | not style gated | cond: is_tight_track==1 / pre: (none) / effects: type 5 value 400000 / base_time: -1 |
| 202243 | Sharp Turns × | nac | 40 | 1 | none | not style gated | cond: is_tight_track==1 / pre: (none) / effects: type 5 value -400000 / base_time: -1 |
| 202251 | Collaborative Graded Races ◎ | nac | 90 | 1 | none | not style gated | cond: is_dirtgrade==1 / pre: (none) / effects: type 1 value 600000 / base_time: -1 |
| 202252 | Collaborative Graded Races ○ | nac | 70 | 1 | 109801, 107901, 109802, 108101 | not style gated | cond: is_dirtgrade==1 / pre: (none) / effects: type 1 value 400000 / base_time: -1 |
| 202253 | Collaborative Graded Races × | nac | 40 | 1 | none | not style gated | cond: is_dirtgrade==1 / pre: (none) / effects: type 1 value -400000 / base_time: -1 |
| 202261 | Chance of Victory | dir, l_1 | 180 | 2 | 109801 | not style gated | cond: ground_type==2&phase_laterhalf_random==1&order_rate<=50 / pre: (none) / effects: type 27 value 3500 / base_time: 24000 |
| 202262 | Sunny Sign | dir, l_1 | 180 | 1 | 109801 | not style gated | cond: ground_type==2&phase_laterhalf_random==1&order_rate<=50 / pre: (none) / effects: type 27 value 1500 / base_time: 24000 |
| 202271 | Can't Keep Me Down | dir, l_1 | 180 | 2 | 101902 | not style gated | cond: ground_type==2&phase_random==1&order_rate>=40 / pre: (none) / effects: type 27 value 3500 / base_time: 24000 |
| 202272 | Comeback | dir, l_1 | 180 | 1 | 101902 | not style gated | cond: ground_type==2&phase_random==1&order_rate>=40 / pre: (none) / effects: type 27 value 1500 / base_time: 24000 |
| 202281 | Full Speed! | dir, l_3 | 180 | 2 | 104301 | not style gated | cond: ground_type==2&phase_random==3&order_rate<=50&is_lastspurt==1 / pre: (none) / effects: type 27 value 3500 / base_time: 30000 |
| 202282 | Full Tilt | dir, l_3 | 180 | 1 | 104301 | not style gated | cond: ground_type==2&phase_random==3&order_rate<=50&is_lastspurt==1 / pre: (none) / effects: type 27 value 1500 / base_time: 30000 |
| 202291 | Crystal Clear | dir, l_1 | 150 | 2 | none | not style gated | cond: ground_type==2&order_rate>=40&activate_count_heal>=1&distance_rate<=42&phase==1 / pre: (none) / effects: type 27 value 3500 / base_time: 18000 |
| 202292 | Rational | dir, l_1 | 150 | 1 | none | not style gated | cond: ground_type==2&order_rate>=40&activate_count_heal>=1&distance_rate<=42&phase==1 / pre: (none) / effects: type 27 value 1500 / base_time: 18000 |
| 202301 | Dancer in the Dirt | dir | 160 | 2 | 110001 | not style gated | cond: ground_type==2&accumulatetime>=5&infront_near_lane_time>=3 / pre: (none) / effects: type 27 value 3500 / base_time: 30000 |
| 202302 | Down in the Dirt ○ | dir | 160 | 1 | 110001, 109902, 110002 | not style gated | cond: ground_type==2&accumulatetime>=5&infront_near_lane_time>=3 / pre: (none) / effects: type 27 value 1500 / base_time: 30000 |
| 202303 | Down in the Dirt × | dir | 100 | 1 | none | not style gated | cond: ground_type==2&accumulatetime>=5&infront_near_lane_time>=3 / pre: (none) / effects: type 21 value -2000 / base_time: 30000 |
| 202311 | Be the Center! | dir, l_2 | 180 | 2 | 104602 | not style gated | cond: ground_type==2&phase_random==2&order_rate<=50 / pre: (none) / effects: type 31 value 4000 / base_time: 30000 |
| 202312 | Got the Spirit! | dir, l_2 | 180 | 1 | 104602 | not style gated | cond: ground_type==2&phase_random==2&order_rate<=50 / pre: (none) / effects: type 31 value 2000 / base_time: 30000 |
| 202321 | Run Like Crazy! | dir, l_2 | 180 | 2 | none | not style gated | cond: ground_type==2&phase_random==2&order_rate>=40 / pre: (none) / effects: type 31 value 4000 / base_time: 30000 |
| 202322 | Rapid Gain | dir, l_2 | 180 | 1 | 103402 | not style gated | cond: ground_type==2&phase_random==2&order_rate>=40 / pre: (none) / effects: type 31 value 2000 / base_time: 30000 |
| 202331 | Strong Steps | dir | 120 | 2 | 109801 | not style gated | cond: ground_type==2&base_power>=1200 / pre: (none) / effects: type 1 value 800000 / base_time: -1  ++  cond: ground_type==2&base_power>=1000&base_power<1200 / pre: (none) / effects: type 1 value 600000 / base_time: -1 |
| 202332 | Solid Steps | dir | 120 | 1 | 109801, 110001, 109902 | not style gated | cond: ground_type==2&base_power>=1200 / pre: (none) / effects: type 1 value 400000 / base_time: -1  ++  cond: ground_type==2&base_power>=1000&base_power<1200 / pre: (none) / effects: type 1 value 200000 / base_time: -1 |
| 202341 | Maestro of the Mud | dir | 130 | 2 | 104301 | not style gated | cond: ground_type==2&ground_condition==3@ground_type==2&ground_condition==4 / pre: (none) / effects: type 1 value 600000; type 3 value 600000 / base_time: -1 |
| 202342 | Muddy ◎ | dir | 110 | 1 | none | not style gated | cond: ground_type==2&ground_condition==3@ground_type==2&ground_condition==4 / pre: (none) / effects: type 1 value 600000 / base_time: -1 |
| 202343 | Muddy ○ | dir | 90 | 1 | 104301 | not style gated | cond: ground_type==2&ground_condition==3@ground_type==2&ground_condition==4 / pre: (none) / effects: type 1 value 400000 / base_time: -1 |
| 202344 | Muddy × | dir | 50 | 1 | none | not style gated | cond: ground_type==2&ground_condition==3@ground_type==2&ground_condition==4 / pre: (none) / effects: type 1 value -400000 / base_time: -1 |
| 202351 | Dust Cloud Idol | dbf, dir, l_0 | 120 | 2 | none | not style gated | cond: ground_type==2&accumulatetime>=10&phase==0&order<=1&bashin_diff_behind<=1 / pre: (none) / effects: type 21 value -2500 / base_time: 30000 |
| 202352 | Dust Cloud | dbf, dir, l_0 | 120 | 1 | 104602 | not style gated | cond: ground_type==2&accumulatetime>=10&phase==0&order<=1&bashin_diff_behind<=1 / pre: (none) / effects: type 21 value -1500 / base_time: 30000 |
| 202361 | Catch 'Em Off Guard | dbf, dir | 180 | 2 | none | not style gated | cond: ground_type==2&distance_rate>=50&is_other_character_activate_advantage_skill==9 / pre: (none) / effects: type 21 value -2500 / base_time: 30000 |
| 202362 | Oppression | dbf, dir | 180 | 1 | none | not style gated | cond: ground_type==2&distance_rate>=50&is_other_character_activate_advantage_skill==9 / pre: (none) / effects: type 21 value -1500 / base_time: 30000 |
| 110461 | α-star* | dir, nac | absent | 5 | 104602 | not style gated | cond: distance_rate>=40&distance_rate<=50&distance_diff_rate<=10&ground_type==2 / pre: (none) / effects: type 27 value 2500; type 9 value 350 / base_time: 60000  ++  cond: distance_rate>=40&distance_rate<=50&distance_diff_rate<=10 / pre: (none) / effects: type 27 value 2500 / base_time: 60000 |
| 202371 | Unstoppable | l_1, ldr | 180 | 2 | 103202, 102902, 104503, 108602, 109501, 111201 | Pace Chaser (2) | cond: phase_random==1&running_style==2 / pre: (none) / effects: type 27 value 3500 / base_time: 24000 |
| 202372 | On the Attack | l_1, ldr | 180 | 1 | 105802, 103202, 102902, 104503, 104702, 107702, 108602, 109501, 111201 | Pace Chaser (2) | cond: phase_random==1&running_style==2 / pre: (none) / effects: type 27 value 1500 / base_time: 24000 |
| 210071 | I Wanna Win with You | nac | 200 | 2 | none | not style gated | cond: distance_rate_after_random==50&order_rate<=65 / pre: (none) / effects: type 27 value 3500; type 31 value 2000 / base_time: 12000 |
| 210072 | On the Way to Our Dream | nac | 200 | 1 | none | not style gated | cond: distance_rate_after_random==50&order_rate<=65 / pre: (none) / effects: type 27 value 1500; type 31 value 700 / base_time: 12000 |
| 110351 | Ticket to Your Dreams! | nac | absent | 5 | 103502 | not style gated | cond: is_lastspurt==1&is_last_straight==1&order_rate>=40 / pre: (none) / effects: type 27 value 2500; type 27 value 500 / base_time: 60000 |
| 110501 | Hephaestus | l_2, l_3, f_c, nac | absent | 5 | 105002 | not style gated | cond: phase>=2&is_finalcorner_laterhalf==1&order_rate<=75&order_rate>=40 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 202381 | Breakin' Ahead | l_1, cha | 180 | 2 | 105002 | End Closer (4) | cond: phase_random==1&running_style==4&order_rate>=40 / pre: (none) / effects: type 27 value 3500; type 28 value 350 / base_time: 24000 |
| 202382 | Breakin' Out | l_1, cha | 180 | 1 | 105002 | End Closer (4) | cond: phase_random==1&running_style==4&order_rate>=40 / pre: (none) / effects: type 27 value 1500; type 28 value 150 / base_time: 24000 |
| 202391 | Givin' It 1000% | l_1, run | 180 | 2 | 110702, 106601 | Front Runner (1) | cond: phase_firsthalf_random==1&running_style==1 / pre: (none) / effects: type 27 value 4500; type 9 value -400 / base_time: 27000 |
| 202392 | Mad Dash | l_1, run | 180 | 1 | 110402, 110702, 106601 | Front Runner (1) | cond: phase_firsthalf_random==1&running_style==1 / pre: (none) / effects: type 27 value 2500; type 9 value -400 / base_time: 27000 |
| 100291 | Snow Bright, Snow Flight | nac | absent | 5 | 102901 | not style gated | cond: distance_diff_top<=5&remain_distance<=300&order_rate<=40 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 100421 | I'm Possible! | nac | absent | 5 | 104201 | not style gated | cond: remain_distance<=201&remain_distance>=199&distance_diff_top<=5&order>=2 / pre: (none) / effects: type 27 value 4500 / base_time: 50000  ++  cond: remain_distance<=201&remain_distance>=199&distance_diff_top<=10&order>=2 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 200963 | Shocking Flash | sho, str | 150 | 2 | 104201, 104102, 112001 | not style gated | cond: distance_type==1&straight_random==1 / pre: (none) / effects: type 27 value 3500 / base_time: 30000 |
| 202401 | Lightning Surge | sho, l_2, l_3, mil | 180 | 2 | 108501, 108502, 112101 | not style gated | cond: distance_type==1&phase>=2&order_rate>=40&is_overtake==1@distance_type==2&phase>=2&order_rate>=40&is_overtake==1 / pre: (none) / effects: type 31 value 4000 / base_time: 20000 |
| 202402 | Leap Forward | sho, l_2, l_3, mil | 180 | 1 | 108501, 108502, 112101, 113101 | not style gated | cond: distance_type==1&phase>=2&order_rate>=40&is_overtake==1@distance_type==2&phase>=2&order_rate>=40&is_overtake==1 / pre: (none) / effects: type 31 value 2000 / base_time: 20000 |
| 110191 | THE MOE AAAA Thanks for My Life | l_1, cor, nac | absent | 5 | 101902 | not style gated | cond: near_count>=3&phase==1&corner!=0&order_rate>=40 / pre: (none) / effects: type 27 value 3500 / base_time: 50000 |
| 110581 | Spooky, Scary, Happy | dbf, f_s, nac | absent | 5 | 105802 | not style gated | cond: is_last_straight_onetime==1&order_rate<=40 / pre: (none) / effects: type 27 value 3500; type 21 value -500; type 21 value -500 / base_time: 50000 |
| 201652 | Perfect Spot! | nac | 160 | 2 | 106003, 110602 | not style gated | cond: infront_near_lane_time>=3&accumulatetime>=10 / pre: (none) / effects: type 27 value 3500 / base_time: 30000 |
| 100871 | Silent Letter | nac | absent | 5 | 108701 | not style gated | cond: remain_distance<=400&order<=2&overtake_target_time>=1 / pre: (none) / effects: type 27 value 2500; type 31 value 3000 / base_time: 50000 |
| 100781 | Sunny Breeze | f_c, nac | absent | 5 | 107801 | not style gated | cond: is_finalcorner_laterhalf==1&distance_diff_rate<=50&order==2 / pre: distance_rate>=50&order_rate_out20_continue==1 / effects: type 31 value 2000 / base_time: 80000  ++  cond: is_finalcorner_laterhalf==1&distance_diff_rate<=50 / pre: distance_rate>=50&order_rate_out20_continue==1 / effects: type 31 value 1000 / base_time: 80000 |
| 202411 | Ambitious Breeze | ldr, l_1 | 180 | 2 | 107801, 112401, 107802 | Pace Chaser (2) | cond: running_style==2&order>=2&distance_diff_top<=5&distance_rate>=60&phase==1 / pre: (none) / effects: type 27 value 2500 / base_time: 40000 |
| 202412 | Aspire | ldr, l_1 | 180 | 1 | 107801, 112401, 107802 | Pace Chaser (2) | cond: running_style==2&order>=2&distance_diff_top<=5&distance_rate>=60&phase==1 / pre: (none) / effects: type 27 value 500 / base_time: 40000 |
| 202421 | Claw Forward | lng, btw | 180 | 2 | 106702, 108301 | Late Surger (3) | cond: distance_type==4&running_style==3&is_lastspurt==1&order_rate>=40 / pre: (none) / effects: type 31 value 4000; type 9 value -200 / base_time: 15000 |
| 202422 | Scramble | lng, btw | 180 | 1 | 106702, 108301, 105501 | Late Surger (3) | cond: distance_type==4&running_style==3&is_lastspurt==1&order_rate>=40 / pre: (none) / effects: type 31 value 2000; type 9 value -200 / base_time: 15000 |
| 110211 | Lightning Flare | nac | absent | 5 | 102102 | not style gated | cond: activate_count_middle>=2 / pre: (none) / effects: type 27 value 2500; type 27 value 250 / base_time: 60000 |
| 110341 | Firelight | f_s, nac | absent | 5 | 103402 | not style gated | cond: is_last_straight_onetime==1 / pre: phase>=2&corner!=0&order_rate>=60&is_overtake==1 / effects: type 27 value 3500 / base_time: 50000 |
| 202431 | Solid Strike | med, ldr, l_1 | 170 | 2 | 108901, 111002 | Pace Chaser (2) | cond: distance_type==3&running_style==2&phase_laterhalf_random==1&distance_diff_top<=10 / pre: (none) / effects: type 27 value 2500; type 31 value 3000 / base_time: 30000 |
| 202432 | Steady Gait | med, ldr, l_1 | 170 | 1 | 108901, 111002 | Pace Chaser (2) | cond: distance_type==3&running_style==2&phase_laterhalf_random==1&distance_diff_top<=10 / pre: (none) / effects: type 27 value 500; type 31 value 1000 / base_time: 30000 |
| 100491 | Laugh at the Odds | nac | absent | 5 | 104901 | not style gated | cond: remain_distance<=400&order_rate>=30&order_rate<=50&popularity>=4 / pre: is_finalcorner==1&is_overtake==1 / effects: type 27 value 4500 / base_time: 50000  ++  cond: remain_distance<=400&order_rate>=30&order_rate<=50&popularity<4 / pre: is_finalcorner==1&is_overtake==1 / effects: type 27 value 3500 / base_time: 50000 |
| 202441 | Risk-Maker | nac | 180 | 2 | 104901 | not style gated | cond: popularity>=4&random_lot_shared==60 / pre: (none) / effects: type 1 value 800000; type 3 value 800000; type 4 value 800000 / base_time: -1  ++  cond: popularity<=3&random_lot_shared==30 / pre: (none) / effects: type 1 value 800000; type 3 value 800000; type 4 value 800000 / base_time: -1 |
| 202442 | Risk-Taker | nac | 180 | 1 | 104901 | not style gated | cond: popularity>=4&random_lot_shared==30 / pre: (none) / effects: type 1 value 400000; type 3 value 400000; type 4 value 400000 / base_time: -1  ++  cond: popularity<=3&random_lot_shared==15 / pre: (none) / effects: type 1 value 400000; type 3 value 400000; type 4 value 400000 / base_time: -1 |
| 101001 | Never Say Never | dir, nac | absent | 5 | 110001 | not style gated | cond: remain_distance>=299&remain_distance<=301&order_rate>=20&order_rate<=40&distance_diff_top<=5&ground_type==2 / pre: (none) / effects: type 27 value 2500; type 22 value 2500 / base_time: 50000  ++  cond: remain_distance>=299&remain_distance<=301&order_rate>=20&order_rate<=40 / pre: (none) / effects: type 27 value 2500 / base_time: 50000 |
| 201383 | Sharp Streak | btw, str | 150 | 2 | none | Late Surger (3) | cond: running_style==3&straight_random==1 / pre: (none) / effects: type 27 value 3500 / base_time: 30000 |
| 210081 | Past My Limits | l_3, nac | 200 | 2 | none | not style gated | cond: is_lastspurt==1&phase_random==3 / pre: (none) / effects: type 27 value 3500 / base_time: 24000 |
| 210082 | Eyes on the Goal | l_3, nac | 200 | 1 | none | not style gated | cond: is_lastspurt==1&phase_random==3 / pre: (none) / effects: type 27 value 1500 / base_time: 24000 |
| 210091 | Racing Spirit: Speed | l_1, nac | 150 | 1 | none | not style gated | cond: phase_laterhalf_random==1 / pre: (none) / effects: type 27 value 1500 / base_time: 20000 |
| 210101 | Racing Spirit: Stamina | l_3, nac | 150 | 1 | none | not style gated | cond: phase_random==3&hp_per>=2&is_lastspurt==1 / pre: (none) / effects: type 27 value 2500; type 9 value -200 / base_time: 30000 |
| 210111 | Racing Spirit: Power | l_3, nac | 150 | 1 | none | not style gated | cond: phase==3&is_overtake==1&is_lastspurt==1 / pre: (none) / effects: type 27 value 1500 / base_time: 40000 |
| 210121 | Racing Spirit: Guts | nac | 150 | 1 | none | not style gated | cond: compete_fight_count>0 / pre: (none) / effects: type 31 value 2000 / base_time: 12000 |
| 210131 | Racing Spirit: Wit | nac | 150 | 1 | none | not style gated | cond: activate_count_later_half>=2 / pre: (none) / effects: type 27 value 1500 / base_time: 20000 |
| 210141 | Racing Spirit: Mood | nac | 150 | 1 | none | not style gated | cond: motivation>=4 / pre: (none) / effects: type 2 value 400000; type 4 value 400000; type 5 value 400000 / base_time: -1 |
| 1200041 | Sprint Race Enthusiast | sho, tur | absent | 1 | none | not style gated | cond: distance_type==1&ground_type==1 / pre: (none) / effects: type 1 value 600000 / base_time: -1 |
| 1200051 | Dirt Race Enthusiast | med, dir | absent | 1 | none | not style gated | cond: distance_type==3&ground_type==2 / pre: (none) / effects: type 4 value 600000 / base_time: -1 |
| 110081 | Into High Gear! | slo, nac | absent | 5 | 100802 | not style gated | cond: slope==0@slope==1 / pre: phase>=1&slope==2&order_rate>=50&order_rate<=80&track_id==10006 / effects: type 27 value 3500; type 31 value 1000 / base_time: 50000  ++  cond: slope==0@slope==1 / pre: phase>=1&slope==2&order_rate>=50&order_rate<=80 / effects: type 27 value 3500 / base_time: 40000 |
| 110091 | Queen's Lumination | str, nac | absent | 5 | 100902 | not style gated | cond: distance_rate>=50&corner==0&order==1&bashin_diff_behind<=1 / pre: (none) / effects: type 27 value 3500 / base_time: 60000  ++  cond: distance_rate>=50&corner==0&order<=2 / pre: (none) / effects: type 27 value 2500 / base_time: 60000 |
| 202451 | Top Gear | btw | 180 | 2 | 100802, 103902, 104902, 109402 | Late Surger (3) | cond: running_style==3&is_last_straight==1&order>=2&distance_diff_top<=10 / pre: (none) / effects: type 27 value 4500 / base_time: 24000 |
| 202452 | Pedal to the Metal | btw | 180 | 1 | 100802, 106702, 106703, 103602, 103902, 103703, 104902, 109402 | Late Surger (3) | cond: running_style==3&is_last_straight==1&order>=2&distance_diff_top<=10 / pre: (none) / effects: type 27 value 2500 / base_time: 24000 |
| 202461 | Can't Even Catch My Shadow | run | 180 | 2 | 100902, 110401, 110702 | Front Runner (1) | cond: running_style==1&is_last_straight==1&order==1&bashin_diff_behind<=1 / pre: (none) / effects: type 27 value 4500 / base_time: 24000 |
| 202462 | Firm Resolve | run | 180 | 1 | 100902, 110401, 110702 | Front Runner (1) | cond: running_style==1&is_last_straight==1&order==1&bashin_diff_behind<=1 / pre: (none) / effects: type 27 value 2500 / base_time: 24000 |
| 202471 | Hot Pursuit | ldr, btw | 180 | 2 | 101602, 107202, 108802, 107602 | Pace Chaser (2), Late Surger (3) | cond: running_style==2&distance_rate>=50&is_overtake==1@running_style==3&distance_rate>=50&is_overtake==1 / pre: (none) / effects: type 27 value 3500 / base_time: 24000 |
| 202472 | Latch On | ldr, btw | 180 | 1 | 101602, 107601, 105102, 103902, 107202, 104702, 104202, 107002, 113301, 108802, 105502, 104803, 110002, 107602, 104302 | Pace Chaser (2), Late Surger (3) | cond: running_style==2&distance_rate>=50&is_overtake==1@running_style==3&distance_rate>=50&is_overtake==1 / pre: (none) / effects: type 27 value 1500 / base_time: 24000 |
| 202482 | My True Strength | lng, ldr, l_2 | 180 | 1 | 101602, 107701, 100303, 104503, 103003, 112701, 100603 | Pace Chaser (2) | cond: distance_type==4&running_style==2&phase==2 / pre: distance_diff_top<=10&distance_rate>=60&phase==1 / effects: type 31 value 2000 / base_time: 12000 |
| 202481 | Beast Mode | lng, ldr, l_2 | 180 | 2 | 101602, 104503, 112701, 100603 | Pace Chaser (2) | cond: distance_type==4&running_style==2&phase==2 / pre: distance_diff_top<=10&distance_rate>=60&phase==1 / effects: type 31 value 4000 / base_time: 12000 |
| 100991 | Shine On, Tomakomai! ☆ | dir | absent | 5 | 109901 | not style gated | cond: is_lastspurt==1&order_rate<=40&order_rate>=30&ground_type==2&lastspurt==2 / pre: phase==1&blocked_side_continuetime>=2&corner!=0 / effects: type 31 value 4000 / base_time: 40000  ++  cond: is_lastspurt==1&order_rate<=40&order_rate>=30&ground_type==2 / pre: phase==1&blocked_side_continuetime>=2&corner!=0 / effects: type 31 value 2000 / base_time: 40000 |
| 202501 | Reckless Charge | mil, l_1 | 180 | 2 | 106501, 110901 | not style gated | cond: distance_type==2&phase_laterhalf_random==1&order_rate<=50 / pre: (none) / effects: type 27 value 3500 / base_time: 24000 |
| 202502 | No Looking Back | mil, l_1 | 180 | 1 | 106501, 110901, 108201, 109001 | not style gated | cond: distance_type==2&phase_laterhalf_random==1&order_rate<=50 / pre: (none) / effects: type 27 value 1500 / base_time: 24000 |

### Not in this document

- No recommendation column, no ranking, no advice. `best_for` stays closed: no source publishes it.
- No schema change, no migration, no column. This is a document.
- No UI. How these facts attach to a run's skill list is Phase B2, a separate dispatch.
- No glossary for type tokens, effect codes or condition atoms. The atom list above is the input to that job.
- Nothing was made tracked here. The three untracked JSON exports stay untracked, which is the same open decision already queued for `.scratch-uma/`.


---

## skills-mechanics-audit-2026-10-01.md

## Skill mechanics audit - 2026-10-01

**Verified against:** master `6476a22`. Claims about shipped code describe that commit. Working-tree-only material is confined to §6 and labelled there; where working tree and `HEAD` differ the report says which one it means. Read-only audit: one file written (this one), no command run that writes to the repository or the shared database.

**Request.** A comprehensive audit of the codebase for documented, incomplete full-stack implementations related to Umamusume skill mechanics - unfinished features, TODO comments, partial implementations concerning unique / innate / awakening / evolved / event skills and their associated backend logic and frontend components.

### 1. Method, and the marker question answered first

**There are zero `TODO`, `FIXME` and `XXX` markers in `app/` and `resources/views/`.** The grep was run 2026-10-01, scoped per directory after a repo-wide pattern timed out. The zero is genuine and it is not an accident: this repository records unfinished work in prose registers (`KNOWN-ISSUES.md`, `docs/design-research/SKILLS-GAPS.md`, dated review documents), never in code markers. A marker grep on this repo cannot find the incomplete work, because the work was never written down in code markers. The registers are the index; this audit reads the code against them.

Two rules followed throughout:

- Registers are dated snapshots and lag the code (precedent: KI-21). Where a register contradicted the code, the code was taken as truth and the lag recorded as a finding (§5-E).
- The working tree is dirty with a concurrent peer session's in-flight work. Working-tree reads were checked against `git show HEAD:` wherever the difference mattered, so every claim below is attributable to `6476a22` or explicitly to the working tree (§6).

### 2. Shipped end-to-end - so "incomplete" is a measured list

These are complete on `6476a22`, end to end:

1. **Skills import.** `GametoraSkillsParser` + `StoreSkills` + `PipelineRunner` branch; first live import 1,901 created / 9 adopted / 0 to review / 1,910 stored, of which 623 rows are `[Global]` and client-named (`SKILLS-GAPS.md` §8; `ADR-0011`).
2. **Screen D** (`GET /skills`, `routes/web.php:17`; `resources/views/skills/index.blade.php`, whole file): search, type facet, unique facet, empty-catalog state (names both commands and the KI-27 trap, `:86-91`), no-match state with the G-SK-23 note (`:94-105`), rows with the unique pill and SP marks (`:112-152`), derivation footnote (`:159-163`).
3. **Cards carry innate and unique.** `character_cards.skills_innate` / `skills_unique` json (migration `2026_09_30_073029_add_skill_lists_to_character_cards_table.php`; commit `dd90330`); parser `intList()` keeps them type-filtered (`GametoraCharacterCardParser.php:113-134`); all 1,513 referenced ids resolve against `skills.export_id` and 22 of 268 card records carry two uniques (KI-33; `100701` holds `10071` and `100071`).
4. **Run creation pre-populates.** `prePopulateSkills()` (`TrainingRunController.php:175-197`): union of innate + unique, deduped, filtered through `availableOnGlobal()`, written as `Suggested` rows inside `store()`'s transaction (`:140-154`). Creation only, never a backfill, and the absence of a backfill is pinned by a test.
5. **Run skills pivot.** `run_skills` with `status` / `turn_acquired`; `SkillAcquisition` enum (`Suggested` / `Acquired` / `Skipped`); `setSkillStatus()` uses `syncWithoutDetaching` (`TrainingRun.php:243-251`); the no-detach rule is documented in three places.
6. **Run screen skills section.** Read-only status groups and the edit repeater with per-control labels, plus the hint-absence copy (`resources/views/runs/show.blade.php`; at HEAD the repeater had no `old()`/`@error` - see §5-D and §6).

### 3. Mechanic-by-mechanic matrix

| Mechanic | Parse | Storage | Write path | Read path / UI | Status |
|---|---|---|---|---|---|
| **Unique** | `skills_unique`, `intList()` (`GametoraCharacterCardParser.php:113-134`) | `character_cards.skills_unique` json | Pre-populated as `Suggested` (`TrainingRunController.php:175-197`) | ✦ Unique pill (Screen D `:128-133`), run screen marks | Shipped end to end |
| **Innate** | `skills_innate`, same helper | `character_cards.skills_innate` json | Same pre-populate union | No innate-specific grouping; the four-group picker (innate / unique / awakening / everything else) is unbuilt | Data end to end; UI partial; blocked on generalizing `resources/js/trainee-combobox.ts` (KI-33, `KNOWN-ISSUES.md:1618-1623`) |
| **Awakening** | In the card document as `skills_awakening` / `skills_awakening_en`; **dropped at parse** (`GametoraCharacterCardParser.php:92-93`) | None | None | None; implied by the four-group picker entry only | Unbuilt, and the drop is deliberate-but-unfiled - no register entry owns the storage question |
| **Evolved** | Present (rarity 6); parser docblock records the card-document mismatch (`GametoraSkillsParser.php:30-34`) | Row exists in `skills`, but all 672 rarity-6 rows are unreleased | N/A for Global | None | **Settled JP-only** (G-SK-10 closed; conflict row 45). Not a gap; do not re-open |
| **Event** | In the card document as `skills_event`; **dropped at parse** (`GametoraCharacterCardParser.php:92-93`) | None | None | None | Unbuilt, and **no register entry names it** - the only artifact even mentioning `skills_event` is the stale view comment in §5-A. Unfiled; worst-covered mechanic |
| **Hints** | Hint keys not read | No `run_skills.hint_level` column | None | Screen D renders none by ruling; run screen states the absence (`runs/show.blade.php:464-471`) | Open and decision-shaped: G-SK-3 / G-SK-4; copy ruled "level only, no percentage, anywhere"; curve unsettled (conflict row 16) |
| **Sparks** | - | - | - | - | No spark-to-skill join exists: `app/` grep finds only `LegacySelectionPayload` (validates, computes nothing) and `TeamRankPayload` (facility hint, unrelated). Out of skill mechanics as built |
| **Type derivation** | Derived in parser from effect codes (`GametoraSkillsParser.php:65-79`); `Debuff` unevidenced and deliberately not asserted | `skills.type` | Imported | Screen D footnotes the derivation instead of printing a taxonomy (`:159-163`) | Shipped with deliberate caps (G-SK-14) |
| **Rarity** | Export class code 1..6 stored; never a client label (`Skill.php:21-23`) | `skills.rarity` | Imported | None; no label exists to render | Stored, intentionally unrendered (G-SK-14) |

Counts worth pinning, all measured on the live import and recorded in `SKILLS-GAPS.md` §8 and `ADR-0011`: 623 Global client-named rows; `is_unique` 294 by the rarity rule vs 290 by card join, a delta recorded in ADR-0011 §5; cost present on 906 rows; derived type Speed 913 / Passive 120 / Recovery 82 / null 795.

### 4. Open items across the registers

Routing only - no new rulings. Each row already has an owner in its register; this table is the single index the audit produces.

| Item | Register ref | State | Owner |
|---|---|---|---|
| Hint-level column + acquisition curve | G-SK-3, G-SK-4 (`SKILLS-GAPS.md` §7.1) | Open; needs a schema decision and the curve | Owner (copy) / Architect (schema) |
| Fixture path exclusion for `"enname": "Sand Expert"` | G-SK-17 | Two measured facts recorded; owner's pen; gate tooling | Owner |
| Icon and description | G-SK-19, G-SK-20 | No source this repository holds; a description needs a client-string source first | Owner (new source decision) |
| Japanese search key (`match_key` is English-only, `StoreSkills.php:68`) | G-SK-22 | Needs a PRD amendment first | Owner / Architect |
| FR-D-2 run-UI autocomplete | G-SK-13, `SKILLS-GAPS.md` §7.5 | Unsettled; interim link at `runs/show.blade.php:439`; a server-filled native `<datalist>` noted as the no-JS option | Owner |
| D-63 / D-65 amendment asks | G-SK-23 | Open | Owner |
| Unique-row chip gradient | G-SK-24 | Needs capture `232345` and a material-fill token; flat pill on Screen D is a known deviation | Owner / design |
| Four-group picker | KI-33 `:1618-1623` | Open end, unbuilt | Laravel Dev (blocked on combobox generalization) |
| Full-catalogue copy cost | Peer review F-5 (`skills-section-review-2026-10-01.md`) | 20-skill run 13,167 options / 1.84MB / 1.82s vs 6-skill 4,389 / 638KB / 0.51s | Owner (this is the cost behind G-SK-13) |
| `/api/v1` + CSV/JSON skill shape | G-SK-15 | Unchanged by design; "widening a published shape is its own decision" | Architect |
| Trainee detail page skills section | Phase B2 (`skill-facts-2026-10-01.md:709-715`) | Separate dispatch; the storage prerequisite it was waiting on is now done | Owner |
| KI-35 Class B sentence | `KNOWN-ISSUES.md` KI-35 | Stale: says the card layer is "on its branch (G-SK-6)" after it merged | Docs Writer |
| KI-49 / KI-50 / KI-51 | `KNOWN-ISSUES.md` | Filed today: untracked source bodies; `Blueprint::check()` no-op on SQLite; untracked seeders | As routed inside each entry |
| KI-27 fetch trap | `KNOWN-ISSUES.md:1311` | Open; the trap is named inside Screen D's empty state (`:86-91`) | Data Engineer |

### 5. Findings this audit adds

**A. `resources/views/catalog/show.blade.php:208-229` overstates the deferral, and three of its claims are now false.** The comment says "`character_cards` does not store the arrays", "every id is unresolvable from the database alone", and "columns ADR-0012 explicitly keeps them off the card". Since `dd90330`:

1. Innate and unique **are** stored (`skills_innate` / `skills_unique`).
2. Every id referenced by those two lists **is** resolvable - all 1,513/1,513 via `skills.export_id` (KI-33), and `ADR-0012:157` itself says "the detail page can join rather than degrade".
3. `ADR-0012` kept **schema** off the card; it did not keep the stored lists out of the view. The visible copy (`:225-229`) repeats the same misreading to Trainers.

What remains true and belongs in a correction: awakening and event arrays are unstored; `skills` has no `char` column (migrations `2026_09_29_021157`); descriptions are not stored (`ADR-0012:159-161`). The block needs a rewrite, not a deletion - and the visible sentence is Trainer-facing copy, so it is an owner-visible edit, not a comment fix.

**B. `app/Models/Skill.php:69-79` docblock overclaims the scope's reach.** "Every Trainer-facing surface - the run screen's skill select, FR-D-2's search, the CSV/JSON export, the `/api/v1` run resource - starts here." True for the picker (`TrainingRunController.php:301`) and Screen D (`SkillController.php:92`, `:125`). False for the rest:

- CSV export: eleven fixed columns, no skills at all (`TrainingRunController.php:852`).
- JSON export: loads the relation without the scope (`:841`) - and drops the deck key while it is there (peer review F-3).
- `/api/v1`: `$run->load(['umamusume', 'turnEntries', 'skills', 'deckSlots.supportCard'])` (`Api/V1/TrainingRunController.php:38`), no scope.
- At HEAD the write path was the sharpest instance: `syncSkills` and its `Rule::exists` were unscoped (peer review F-2), so a JP-only id could be written and then not render. The working tree fixes that (§6).

Either the docblock's list shrinks to the surfaces that apply the scope, or the surfaces move under it. A docblock that names four surfaces and binds two is the same silent-no-op shape KI-50 files for constraints: the source reads as a guarantee.

**C. `skills_event` is dropped at parse, uncolumned, unrendered, and unfiled.** `GametoraCharacterCardParser.php:92-93` keeps only innate and unique. No migration adds an event column, no view mentions event skills except the stale comment in Finding A, and no register entry (KI-33's four-group picker spans innate / unique / awakening / everything else) owns the storage question. Event skills are the worst-covered mechanic in the set: even the awakened drop is at least implied by the picker entry, and event skills are implied by nothing. This is a register gap, not a code defect - filing it is the honest find.

**D. The peer review's F-1 / F-2 / F-10 are true at HEAD and the peer's uncommitted work addresses them.** F-1 (a rejected submit loses typed skill rows at HEAD; `old()` handling landed in the working tree's repeater), F-2 (unscoped write path), F-10 (in-flight `@error` key bug). Verified by reading both revisions. Not acted on here; §6 records the separation. F-4 (`SkillFactory` `match_key` desync), F-6 (`ApiV1Test.php:44` named for skills but asserting turns; `TrainingRunTest` second POST omits `character_card_id`), and F-7 (pivot shape untested) remain loose ends on the peer's side, not re-derived by this audit.

**E. Register lag, three instances found.** G-SK-6 is resolved by `dd90330` (storage shipped) but is still cited as pending; KI-35's Class B says "the card layer is on its branch (G-SK-6)" after that branch merged; `SKILLS-GAPS.md` §8 still carries pre-Screen-D text for G-SK-14 ("stored and not rendered" reads as a current gap list where part has since been ruled). All three are dated-snapshot lag, the KI-21 disease; the registers are correct as of their own verification dates.

**F. The marker question, generalized.** Zero TODO markers, by idiom not by accident. Any future "audit the code for TODOs" on this repository should read the registers; conversely, this audit found no work item that exists only as a marker and nowhere in a register - the registers are complete as an index, with Finding C as the single exception the audit adds to it.

### 6. Working-tree separation (peer session in flight)

Not part of master `6476a22`, present in the working tree when read:

- `app/Http/Controllers/TrainingRunController.php` - `syncSkills` now scoped: `Skill::availableOnGlobal()->find(...)` with the comment "The same scope the request rule applies, so the two cannot disagree" (working copy `:698-712`). Fixes review F-2 for the write path.
- `resources/views/runs/show.blade.php` - the skills repeater rehydrates `old()` (`:500-502`) with pre-built `@error` keys (`:507-509`). Fixes F-1 and F-10.
- `docs/design-research/skills-section-review-2026-10-01.md` - untracked; the F-1..F-11 review this audit cross-checks against.
- KI-49's three untracked source bodies and KI-51's three untracked seeders remain on disk; no ref carries them.

Every working-tree-sensitive claim in this report was verified with `git show HEAD:` against the same path; the at-HEAD state is what is reported for `6476a22`, and the working-tree deltas above are the peer's.

### 7. Not covered, deliberately

- Off-limits files were not audit targets and were not edited: `docs/scenarios/**`, `docs/UMAMUSUME_REFERENCE.md`, `PLAN.md`, `Makefile`, `docs/GATE-REGISTRY.md`, `docs/SOURCE-OF-TRUTH.md`, `PRD.md` (their content enters below only through register citations).
- Read-only audit: no live network, no database writes, the test suite was not executed. Claims are source and register citations; where a number is quoted it is quoted from its register with the register named.
- No new rulings were made; §4 is routing. The owner decisions pending are exactly the rows in §4, and each already has an owner on record.

This file is untracked; committing it is the owner's call.


---

## skills-mechanics-audit-verification-2026-10-01.md

## Skills-mechanics audit: verification pass, and the items the audit's index does not carry, 2026-10-01

**Verified against:** master `d6ed712`. `git diff --stat 6476a22..d6ed712` is a single file, `KNOWN-ISSUES.md`,
+22 lines (the KI-51 entry), so the code claims in `docs/design-research/skills-mechanics-audit-2026-10-01.md`
(anchored at `6476a22`) hold at HEAD as written. Working-tree files that differ from HEAD are listed in section 4.
Read-only apart from this file: no register, PRD, ADR or `CONSTRAINTS.md` edit, and nothing committed.

**What this file is, and is not.** A concurrent session wrote `skills-mechanics-audit-2026-10-01.md`
(108 lines, untracked) against the same brief this session received: the current state of every incomplete
skill-mechanics implementation, the files where it is incomplete, and what is missing. This file is the
independent half of that record. Section 2 re-derives the audit's load-bearing claims from source rather than
repeating them. Section 3 carries the open items the audit's section 4 index does not, in the same
state / files / missing form. Nothing in the audit or in the registers was edited; where this file disagrees
with the audit's wording, the point is stated here and left for the consolidation pass.

**Method.** Register text read against code on 2026-10-01, in the working tree at `d6ed712` plus the peer diffs
named in section 4. No live network. No write to the shared database; the suite ran against the `:memory:` pin
at `phpunit.xml:41`. Line numbers were valid at the reads recorded here, and the tree moves under concurrent
sessions.

### 1. The marker question, re-run

Zero `TODO`, `FIXME` and `XXX` markers in `app/` and `resources/views/` (pattern `TODO|FIXME|XXX`, run
2026-10-01). The code directories are unchanged across `6476a22..d6ed712` (the only diff is `KNOWN-ISSUES.md`),
so the result is HEAD's too. The repository records unfinished work in prose registers, never in code markers;
the registers are the index, and the audit's section 1 states the same result. For the brief's "search for
TODO comments", this is the measured answer.

### 2. Verification of the audit's load-bearing claims

Each row was re-derived independently this pass; the evidence column names what was read, not a summary.

| Claim (audit section) | Evidence read 2026-10-01 | Verdict |
|---|---|---|
| Cards carry `skills_innate` / `skills_unique`, and run creation pre-populates from them (2.3, 2.4) | Migration `2026_09_30_073029_add_skill_lists_to_character_cards_table.php`; `GametoraCharacterCardParser.php:80-95` (record keeps exactly the two lists) and `:113-134` (`intList()`); `TrainingRunController.php:140-154` (`store()` transaction) and `:175-197` (`prePopulateSkills`, filtered through `availableOnGlobal`) | Confirmed |
| The working tree scopes `syncSkills`; HEAD did not (5-B, 6) | `TrainingRunController.php:698-712` in the working tree: `Skill::availableOnGlobal()->find(...)` with the "cannot disagree" comment | Confirmed |
| `Skill::scopeAvailableOnGlobal`'s docblock names four surfaces and binds two (5-B) | `Skill.php:69-85` verbatim; call sites by grep: `TrainingRunController.php:194`, `:301`, `:704`, `SkillController.php:92`, `:125`; `TrainingRunController::export()` docblock ("csv (turn rows) or json") and its 11-column turn-only CSV | Confirmed |
| The catalog deferral block states three things that are no longer true (5-A) | `catalog/show.blade.php:213-229` verbatim; `dd90330` is an ancestor of HEAD (`git merge-base --is-ancestor`, exit 0); `ADR-0012:3-13` records the columns as present and as serving "a run's own skill lists", and `ADR-0012:155-159` contains the "the detail page can join rather than degrade" cite the audit uses | Confirmed (the block predates the storage and was never reconciled) |
| `skills_awakening` / `skills_event` never leave the parser (3, 5-C) | `GametoraCharacterCardParser.php:80-95`: no other skill key appears in the record builder | Confirmed |
| KI-33's one open end is the four-group picker, blocked on generalizing `trainee-combobox.ts` (2.2, 4) | `KNOWN-ISSUES.md:1618-1623` verbatim ("her innate / her unique / her awakening / everything else", "unblocked; it is not closed here"); `resources/js/trainee-combobox.ts` exists | Confirmed |
| Registers lag the code in dated places (5-E) | Sampled: the `KNOWN-ISSUES.md` status header `:10-17` (dated 2026-09-30) still names the ADR-0008 gap; `docs/adr/0008:236`, `:439` have since recorded the two columns. Section 3.3 extends this item, because the header itself routes the doc gap | Confirmed |

Not re-derived by this pass, so left to the audit's own provenance: the import and join counts it quotes from the
registers (1,910 stored, 623 Global-named, 22 two-unique card records, the 1,513-of-1,513 id join, `is_unique`
294 by rule versus 290 by card join). This pass did not re-run the import or the join; those numbers stand on
KI-33 and `SKILLS-GAPS.md` section 8 as the audit cites them.

### 3. Open items the audit's section 4 index does not carry

The audit's section 4 table reads as the complete pending set ("The owner decisions pending are exactly the
rows in section 4", its section 7). Four items that belong to the same brief are absent from it. They are
added here in the state / files / missing form; whether the audit file absorbs them is the consolidation
pass's call, and nothing there was edited. For set boundaries: KI-26 and KI-29 (on `/umamusume`) and KI-32
(design system) remain routed inside `SKILLS-GAPS.md` section 9 and outside the skill-mechanics surface.

#### 3.1 G-SK-11, scenario-exclusive skill surfaces

- **Current state.** The burst-reward ladder values exist in the scenario files, and `x-spirit-burst-roster`
  renders six teammate burst states from `turn_events` payloads while `x-team-rank-gauge` renders the ladder,
  but neither names a skill and no panel reads the burst reward ladder at a given team rank. A UI decision, not
  an import. Open in the register's own section 9 list; owner on record: Frontend with the Planner Domain
  Specialist; absent from the audit's section 4.
- **Files.** `docs/design-research/SKILLS-GAPS.md` section 8, G-SK-11 (`:321-331`); the two components above;
  `docs/scenarios/02`, `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md`, and `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Twinkle Star Climax (The Finale)".
- **Missing.** One row per the section 4.1 ladder keyed to the reported team rank; an "It's On!" row for S/S+;
  the Trackblazer Climax name from `04:246`. Constraints already recorded: do not render the Zenith names
  (G section 4.1); labels verbatim from a scenario file only (D-20).

#### 3.2 KI-37, the run screen's controls measure 31/30/40px

- **Current state.** Measured in a browser: select 31.0px, number input 30.0px, submit 40.0px against
  `DESIGN.md` section 6.14's 44; page-wide 12 selects, 20 inputs and 11 buttons, so it is the run screen's
  default, not one control. OPEN; owner Frontend with the design-system owner; filed by the skill-selector
  pass and absent from the audit's section 4. Precedent: `5ff7aca` sized Screen D's identical readings to 44
  with no token change.
- **Files.** `resources/views/runs/show.blade.php` (the guided-turn block, the skills editor, the race form);
  `docs/design-research/DESIGN.md` section 6.14 (`:849`); the density question at
  `docs/design-research/CONSTRAINTS.md:171`.
- **Missing.** The height raise plus the density decision it is tied to. It is not a phone-width item: a
  control below its specified height fails at 1280px exactly as it fails at 390px.

#### 3.3 The doc gap `KNOWN-ISSUES.md:14-17` routes to "the next register pass"

- **Current state, read 2026-10-01.** Of the four files the register names, `docs/adr/0008` has since recorded
  the two columns (`:236`, `:439`); `ARCHITECTURE.md` and `ARCHITECTURE-ESSENTIALS.md` still name neither
  (grep, no matches); D-30 (`docs/design-research/CONSTRAINTS.md:138`) still does not list them among the
  card's permitted render fields; `DocSchemaDriftTest` still pins only `training_runs`. The register itself
  filed this as an open item ("That doc gap is the next register pass's business"), and the audit's section 4
  does not carry it.
- **Files.** `ARCHITECTURE.md` section 3; `ARCHITECTURE-ESSENTIALS.md` (the digest); `docs/design-research/CONSTRAINTS.md`
  D-30; `tests/Feature/DocSchemaDriftTest.php`.
- **Missing.** The register-pass amendments themselves. Architect for section 3 and the digest. D-30 is the
  owner's pen, because the permitted-render list is a ruling record rather than a doc chore; whoever lands the
  four-group picker (which groups by which card list an id sits on) or the trainee-detail skills section
  should have the list extended or a ruling recorded, the way the `CharacterCard` entry itself joined the list
  on 2026-09-29.

#### 3.4 KI-24, the stale source hash answering 200 with stale content

- **Current state.** OPEN (`KNOWN-ISSUES.md:1147`), filed by the skills import pass (recorded at
  `SKILLS-GAPS.md:672-674`: "KI-23 and KI-24 came from the import pass"). A pinned URL can fail silently when
  the hash is stale and the fetch reports success. Routed inside its own entry.
- **Files.** `KNOWN-ISSUES.md` KI-24; the fetch pipeline under `app/Services/DataPipeline/`.
- **Missing.** The fix the entry itself names. Included here for set completeness only: the audit carries
  KI-27 (also fetch infrastructure, also skills-pass-filed) and this sibling was in the same filing pass.

### 4. What this pass ran, and the working-tree split

- **Suite.** `vendor/bin/pest` over `RunSkillRowLabelsTest`, `SkillSearchScreenTest`, `GametoraSkillsParserTest`,
  `SkillsFetchTest`, `CharacterCardParserTest` and `TrainingRunTest`: **88 passed (437 assertions)**, 10.57s,
  under the `:memory:` pin. The audit's section 7 records the suite was not executed there (source and register
  citations only); this run fills that in. Green here means the shipped surfaces work; it closes none of the
  gaps above, which are feature-absent rather than broken.
- **Marker sweep.** Run in both the working tree read and re-run for this file; zero hits (section 1).
- **Peer-diff files (uncommitted, read as working tree).** `app/Http/Controllers/TrainingRunController.php`,
  `app/Http/Requests/StoreRunSkillRequest.php`, `resources/views/runs/show.blade.php`,
  `tests/Feature/RunSkillRowLabelsTest.php`, `tests/Feature/TrainingRunTest.php`, `config/uma.php`,
  `database/seeders/DatabaseSeeder.php`, `database/seeders/UmamusumeSeeder.php`, the untracked seeders and
  source readers (KI-49, KI-51), and the `phpunit.xml` comment above the pin. Every claim above names which
  revision it describes where the two could disagree.
- This file is untracked; committing it is the owner's call, alongside the audit it verifies and the review
  the audit cross-checks.


---

## SKILLS-GAPS.md

## Skill system gaps

`verified-against 3711894 (master)` · written 2026-09-29 during the skills audit pass, in response to
a brief that described three acquisition paths and a six-row hint-discount table. Every anchor below
was opened and read on that commit; the corrections an incoming draft of this file needed are in §9.

**Scope:** Global only. Nothing here may import `[JP]`-only mechanics, evolved-skill data, or JP wiki
skill tables. Where a source is JP-side it is marked and its Global status is stated.

**Do not re-derive numbers from this file.** The acquisition paths are the authority; every figure is
only as good as the anchor beside it, and §2.2/§5 record the figures this repo has already declined.

**Concurrent work on this tree — corrected 2026-09-29, it is no longer concurrent.** This paragraph used to
warn that the Legacy Select read-back was landing untracked alongside this file. It has landed:
`app/Models/Legacy/LegacySelectionPayload.php`,
`database/migrations/2026_09_29_012615_add_legacy_selection_to_training_runs.php` and
`docs/adr/0010-legacy-selection-payload.md` are all tracked on master, and `training_runs.legacy_selection`
is a nullable json column. What survives from the warning is the scope note it was there for: that payload
stores per-Legacy Spark kind, target and star rank plus an affinity grade, and **G-SK-5 is scoped against
it** — the skill join is still missing, and G-SK-22 is the same question from the other side.
`docs/scenarios/03-trackblazer.md` remains superseded and untouched, per its own rule
(`SOURCE-OF-TRUTH.md` §4.1).

**The shape to look for, because four entries in this repository are the same defect wearing different
clothes.** *A tool reports success against a target that is not the thing the claim is about, and the report
reads as verification.* **KI-22**: `grep` looked for `isFreeRace`, found nothing, and a missing identifier
was read as a missing branch — the search was over a name the code never used. **KI-23**: the fixture was
authored to the parser's expectation, so the suite passed green against a document key that does not exist;
the assertion was reading its own assumption. **KI-25**: the test asserted rendered attributes while the
claim was keyboard behaviour, so it could not fail for the reason it was run. **KI-27**: `SourceFetcher`
checks the document's hash on a disk shared by every database, and reports "unchanged" against a database it
never wrote to — the check was about the wrong target. None of the four was caught by a gate; each was
caught by someone noticing that a green result and the claim it was supposed to support were not about the
same thing. So the question to ask of any check in this repo is not "did it pass" but **"what would have had
to be true for this to fail, and is that the thing I am claiming?"** A fifth is coming, and it will look
exactly like one of these.

---

### 1. Acquisition paths — eight, not three

The brief named inheritance, personal defaults and star-rank unlock. Confirmed Global paths:

| # | Path | Anchor | Status |
|---|---|---|---|
| 1 | Innate / default skills, raised by Potential Level | `UMAMUSUME_REFERENCE.md:386` names **Potential Levels** (`覚醒Lv`) as a real pre-run system, and `:395` records that which UI owns the export's `stat_bonus` row is `❌ UNVERIFIED`; `[S]` Umamusume Global news 1064 (2026-09-23) "Potential Levels and Books of Hints"; `:771` §1.6.10 | Name and existence confirmed. The "starts with 3 hints, 7 after awakening" counts are `❌ UNVERIFIED` — no anchor in the corpus carries a number |
| 2 | Inspiration / Legacy Sparks | `REFERENCE` §1.5.1–1.5.4 (`:586`–`:641`); `docs/scenarios/01-ura-finale.md:38` | Confirmed, multi-source. Three payout moments (career start, early April Classic, early April Senior); White Sparks pay a mid-run hint +1..+5 and never fire at career start; Green Sparks carry the trainee's own Unique Skill at 1–3 hint levels |
| 3 | Star-rank unlock | `REFERENCE:588` (unique-skill Spark guaranteed once the trainee stands at ★3+); §1.3.5 `:385` (★4/★5 breakthrough rows, +50 stat budget per rank past native rarity); §1.6.9 `:750`, `:762` and the terminology row `:1957` | Confirmed for the ★3 consequence. The currency route is Trainee Star Pieces (Transfer Requests, duplicates) and the Statue Exchange at 650 pieces to max a trainee — see §5.1: the phrasing "Goddess Statues raise rank" is a retired reading |
| 4 | Support card events | `REFERENCE` §1.4.4 `:465`–`:467`; §1.4.7/§1.4.8 `:550`–`:555` | Confirmed, export-backed. Per-card `hint_skills` / `hint_others`; Hint Levels (id 17) and Hint Frequency (id 18); one event pays `1 + card hint level` |
| 5 | Race results | `REFERENCE` §2.3 Trackblazer `:891-892`: beating a rival grants a hint tied to the race distance or the running style, **requiring C aptitude or better in that distance**; `03-trackblazer.md:41-43`, `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Rival Races", `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Rivals" | Confirmed. The draw is distance/style-keyed, not "mainly passive-type" as the brief put it. Cite by line: Section 2 numbers two different headings `2.3` |
| 6 | Training-goal and epithet achievement | `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Epithets (Race Route Bonuses)" (route wins pay `hint +1`, e.g. Mile Straightaways, Homestretch Haste, Top Pick); `:246` Climax → Radiant Star; section "Unique Skill Level-Ups" finale-skill hint plus unique-skill level-up events; URA's Unique Skill fan gates are in `design-research/CONSTRAINTS.md` D-224 | Confirmed for Trackblazer and URA. The route→skill mappings exist in the guides and are not consolidated anywhere structured |
| 7 | Scenario-exclusive skills | §4 below | Confirmed |
| 8 | Negative skills from losses | Not in this repository. Read this pass from [umamusu.wiki Game:Skills](https://umamusu.wiki/Game:Skills) (rev. 2026-08-30, tier `[A]`) — obtained by placing 6th or lower in races or from other specific events, and removed by spending SP — and from [Game8's Global all-skills list](https://game8.co/games/Umamusume-Pretty-Derby/archives/535927) (read 2026-09-29, tier `[A]`) | Existence: two independent `[A]` domains, **single readable pass, no `[S]`/client capture**. Everything past existence is `❌ UNVERIFIED` here — see G-SK-9 |

Paths 1–7 have anchors; the work on them is consolidation. Path 8 is the only genuinely new material
this pass produced, and it is new only in the sense that the corpus has never recorded it.

---

### 2. Hint levels

#### 2.1 Confirmed

- A per-skill hint level exists, it lowers the SP cost of that skill, and it is raised by four routes:
  the trainee's own starting hints (path 1), White Spark inheritance +1..+5 (path 2), support-card hint
  events paying `1 + card hint level` (path 4), and the Books of Hints item route (`REFERENCE` §1.6.10
  `:771`).
- The item route is documented at **Lv3, 必要スキルPt 30% 減少**, with 12 / 6 / 30 of 「ヒント本 /
  ヒント専門書 / 夢の煌めき」 to take an unenhanced card there (`REFERENCE:467`).
- Hint levels demonstrably go past Lv3: a card at hint level 4 pays five levels from one event, and
  Unity Cup burst hints start at Lv2 (`02-unity-cup.md:95`, `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Spirit Burst").
- A hint for an already-learned skill is spent, not banked: Unity Cup substitutes a different random
  Ignited hint when the skill is maxed or owned (`02:109`, `06:186`).

#### 2.2 What is not confirmed

**The per-level discount curve is unsettled and this repo has already ruled on it.** Conflict Log
**row 16** (`REFERENCE:2002`) is the authority, verbatim in disposition: keep **10%/level and −30% at
Lv3** as the published value because it is quoted from client-adjacent `[JP]` text and matches the Lv3
ceiling; treat the umamusu.wiki **~8% per hint, max 40%** — plus its separate **Fast Learner −10%** —
as *possibly folding that effect in*; and **do not encode either as a multiplier without an in-client
check**. `REFERENCE` §1.1.4 `:144` is stricter still: "Exact per-level discount percentages: ❌
UNVERIFIED: No current source found", and §8.4 `:2093` keeps the same gap open.

Working position for this repo, following the stricter record: **a hint level may be displayed as a
level; a discount percentage may not be computed or rendered** until an in-client read settles it. The
`NN% OFF` caption shape is already pinned in `SOURCE-OF-TRUTH.md:93` for the day it becomes honest.

#### 2.3 The brief's table is rejected as a unit

`Lv0–Lv5 = 0/10/20/30/35/40%` restates a curve row 16 declined to settle and appends Lv4/Lv5 values no
source in two domains carries. It is recorded here so a future pass does not re-import it from the same
brief. The 40% figure is the wiki's *ceiling*, not a fifth level's value, and row 16 already notes the
two shapes cannot both describe one quantity.

#### 2.4 In-client read recorded, 2026-10-03

A Global client read on 2026-10-03 shows the caption ladder directly (Unity Cup run, `[Rosy Dreams]`
Rice Shower, Senior Late December, Learn screen at 173 SP; 38-frame capture set, exemplar frames
`09e754d6`, `2bc7fd23`, `5fe202bf`, `a2741c59`, `c8b2dcad`, `b1e22dd7`):

| Caption | Sampled skills (displayed SP) | Arithmetic on a sampled pair |
|---|---|---|
| `Hint Lvl 1` `10% OFF` | Rushing Gale! 323; Outer Swell 162; Ignited Spirit SPD 180; Plan X 272 | 180 × 0.9 = 162 |
| `Hint Lvl 2` `20% OFF` | Hesitant Pace Chasers 104; Cut and Drive! 160; Countermeasure 128 | 130 × 0.8 = 104 |
| `Hint Lvl 3` `30% OFF` | It's On! 289; Ignited Spirit STA 140; Front Runner Corners ○ 91; Productive Plan 112 | 200 × 0.7 = 140 |
| `Hint Lvl 4` `35% OFF` | Unruffled 280; Pace Chaser Straightaways ◎ 91; Ignited Spirit GUTS 130 | 140 × 0.65 = 91 |
| `Hint Lvl Max` `40% OFF` | Soft Step 96 | 160 × 0.6 = 96 |

Displayed cost is base × (1 − discount), floored to the integer: Pace Chaser Corners ○ reads 84 at
Lv4 against a base of 130 (`130 × 0.65 = 84.5`); every other sampled pair is exact. The top caption
is `Lv Max`, not a level number.

Compared against the two shapes §2.2 holds apart: Lv1–Lv3 match the published **10% per level**
(10/20/30), and the **40% ceiling** the wiki tied to its max is real at `Lv Max`; `−30%` is the Lv3
value, not the ladder's ceiling, and the step is not constant past Lv3 (+5 to Lv4, +5 to Max). This
read is the in-client check §2.2 conditions on, and it supersedes §2.3 as a statement about sources:
an in-client source now carries Lv4 and Max captions. The §2.2 rendering position and conflict row 16
are the owner's to revisit against it; this section records the read only.

---

### 3. Skill points

Confirmed (`REFERENCE` §1.1.4 `:144`): Wit pays +4 to +5 per session against +2 for the four
energy-spending disciplines; races pay more the harder they were; training events pay; support effects
raise either the post-race package (Race Bonus, id 15) or the discount on a hinted skill. Trackblazer's
shop economy records 30 SP on a loss and coins scaling with placement (`:837`, `:889-890`).

**Not confirmed:** the brief's per-grade magnitudes (G1 +45, G2/G3 +35, OP +30). Direction is sourced,
the numbers are not, and the repo's grade-code mapping is itself only partly settled (conflict row 12:
code 700 remains `❌ UNVERIFIED`). See G-SK-12.

---

### 4. Scenario-exclusive skills

#### 4.1 Unity Cup

Authority: `docs/scenarios/02-unity-cup.md:199-241` and `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Spirit Burst", section "Unity Cup Special Skills",
`:257-258`, `:300-320`. `REFERENCE` §2.2 `:849`–`:862` is the summary, and §2.2's own rework note `:1005`
dates the Global rework to **2026-07-01**.

- **Burst hint ladder** (`02:205-208`, `06:232-235`): 4-6 → white hint Lv1 +10 stat +10 SP · 7-9 → white
  Lv3 +20/+20 · 10-12 → gold Lv1 +30/+30 · 13+ → gold Lv3 +40/+40. `SCENARIO-DIFFERENCES.md:150` records
  the correction that matters: the denominator is now **bursts plus Extreme bursts combined**, and that
  same row flags the older "13+ → Burning Spirit X Lv.3 + 20 SP" reading as superseded.
- **"Ignited Spirit"** facility-specific hints (`02:109`, `06:185-186`) start at Lv1 and scale with the
  card's Hint Level bonus. **"Burning"** as a rarity label appears only inside the superseded row above
  and in the incoming write-ups — no active scenario file names it. Treat the pair as `❌ UNVERIFIED`
  client copy before putting either word in a screen (D-20).
- **"It's On!"** (`02:224-229`, `06:257-258`): Team Rank S grants the hint at Lv1, **Lv3 for a scenario
  *story* character** (the guide's example is Haru Urara) — not for any scenario-*linked* card; the
  linked list is a different thing (`REFERENCE:530`: Taiki Shuttle, Rice Shower, Haru Urara,
  Matikanefukukitaru, Riko Kashimoto). S+ grants a second hint, which is how the level is maxed, and the
  ladder's own UI consequence is already built into `x-team-rank-gauge` (`:42`, `:63`).
- **Team Zenith victory skills** — the brief names `Cooldown` (Rice Shower) and `Indomitable` (Haru
  Urara) as post-finals rewards. **No file in this repository contains "Team Zenith", "Cooldown" or
  "Indomitable"** (grep across `docs/` and root returns only the incoming UX write-up's own line 1594).
  Recorded as an incoming claim, not a repo fact. Do not encode the names or the effect class until a
  scenario file carries them.

#### 4.2 Trackblazer

`docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Twinkle Star Climax (The Finale)" (Climax → Radiant Star), `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Unique Skill Level-Ups"
(finale-skill hint; unique-skill level-up events pay a hint when the trainee is selected), `05:188-190`
(the scenario Spark). `04:1170` in the incoming flows doc concedes the Radiant Star mechanics were never
extracted, so the name is all this repo has.

#### 4.3 Other scenarios

`07-grand-concert.md` is a Known-Gap Stub and `08-grand-masters-jp-only.md` is `[JP-Only]`; per
`SOURCE-OF-TRUTH.md` §4.1 the latter must not be imported into app data, config, or UI copy. Neither may
be used to fill a skill surface.

---

### 5. What the brief got wrong

| Brief claim | Disposition |
|---|---|
| Three acquisition paths | Eight (§1) |
| Hint table 10/20/30/35/40% | Rejected as a unit; conflict row 16 already declined the curve, and Lv4/Lv5 carry no source in any domain |
| "A blue mark on the icon means the skill cannot be inherited" | `❌ UNVERIFIED`. No repo doc and neither wiki read this pass states it. Do not encode a flag for it (G-SK-5) |
| Skill Points G1 +45 / G2+G3 +35 / OP +30 | Direction confirmed, magnitudes `❌ UNVERIFIED` (§3) |
| "3 hints → 7 via Potential Level" | Potential Levels confirmed by name; the counts are `❌ UNVERIFIED` (§1 path 1) |
| "Scenario-linked trainee such as Taiki Shuttle gets +3 hint levels on It's On!" | The Lv3 case is a scenario **story** character, not any linked card (§4.1) |
| "Evolution skills — confirm Global status; the Global site announced additional ones" | Already settled at conflict **row 45**: two independent negative readings, no `[S]` notice, the wiki itself says the system is JP-exclusive. Treat as `[JP-Only]` (G-SK-10) |

#### 5.1 Claims in this brief that the conflict log already retired

Recorded because a re-import of a retired claim is the failure this repo has been catching all slice:

- **"Goddess Statues" as permanent scenario buffs / as what raises star rank** — retired by conflict
  **row 43**, with the deliberate instruction not to re-type the retired literal in tracked text. The
  corpus position (`REFERENCE:762`, `:1957`) is that 「女神像」/Goddess Statue is **currency only**,
  exchanged for Trainee Star Pieces in the Statue Exchange on an escalating ×1→×5 rate, and grants no
  buff. §1 path 3 is written from that reading. Row 43 also retired a guaranteed-training-success
  consumable in the same family; the only documented zero-failure levers are Trackblazer's Good-Luck
  Charm, an Extreme Spirit Burst on its own facility, and Failure Protection (id 27).
- **"Skill Set" bulk planning** — `[JP]`-only, excluded by `MECHANICS-TRANSLATION-TRIAGE.md` §3.
- **The 1200-Power evolution gate** — triage §7.2 `:182` shows it is Grand Live's *Fully Charged* gate
  misfiled as an evolution condition, alongside a 340 SP figure that belongs to Swinging Maestro.
- **Mood pre-race percentages** — the incoming docs' +10/+5 are contradicted by the client's own Mood
  Effect panel (+4/+2/0/−2/−4), triage §7.2 `:172`.

Triage §4 `:88` already ruled the ~10% hint discount unsourced. None of these four is reopened here.

---

### 6. Gap entries

#### G-SK-1 — Skill master columns are PRD-shaped; the *data* is absent

- **Measured:** `skills` = `id, name, name_ja, match_key, sp_cost, type, is_unique, timestamps`, 10 rows,
  `sp_cost` and `type` NULL across all ten, `is_unique` false across all ten, `run_skills` empty.
- **Correction to the incoming draft of this file:** those six columns are exactly `PRD.md:63` FR-D-1
  ("English name, Japanese name (nullable until cross-referenced), match key, SP cost (nullable), type
  string (nullable), `is_unique` flag"). Nothing is missing from the shape. **`rarity` is not a PRD
  field**, so adding it is a scope change for the owner (AGENTS.md escalation 2), not a gap to fill.
- **What is missing:** the values. Cost, type and uniqueness arrive with the import (G-SK-2), and
  rarity — if authorized — arrives with the same export field.
- **Read this with G-SK-18** if the derived `type` is ever refactored: the sign of an effect value is
  part of the rule, not a detail, and a code-only reading prints `Passive` beside an averseness.
- **Owner:** Architect (any column beyond FR-D-1), Data Engineer (values).

#### G-SK-2 — No skills import

- **Measured:** `app/Services/DataPipeline/Parsers/` holds `GametoraCharacterParser`,
  `GametoraScenarioParser`, `GametoraRaceCatalogParser`; `config/uma.php:44-93` configures exactly **two**
  sources, `gametora-characters` and `gametora-race-catalog`. So a scenario parser exists without a
  configured scenario source, and skills have neither.
- **Why not built:** outside this pass, and every cost/type/rarity UI item downstream is blocked on it.
- **What is needed:** a `GametoraSkillsParser` behind its own `SkillSourceParser` contract — master
  already carries `Contracts/SourceParser.php`, `ScenarioSourceParser.php` and
  `RaceCatalogSourceParser.php`, and the card branch adds `CharacterCardSourceParser.php`, so one
  contract per source is the existing pattern, not a new one — a `config('uma.sources')` entry for
  `https://gametora.com/data/umamusume/skills.f4a1e02d.json` — a **different document** from the two
  approved ones, so the owner-gate argument written at `config/uma.php:69-77` has to be re-made rather
  than inherited — plus the promote path, since a live fetch cannot create catalog rows
  (`CrossReferenceMatcher` returns `None`, `PRD.md` FR-B-3). Cross-check rarities against Game8.
  **The export's skill row count is not recorded in this repository**; `PRD.md:73` NFR-3 budgets roughly
  2,000 for the performance gate, which is a budget, not a count.
- **Owner:** Data Engineer, with Architect sign-off and the owner's fetch approval on record.

#### G-SK-3 — No hint-level tracking

- **Measured:** `run_skills` carries `status` and `turn_acquired` only. No `hint_level`, no `skill_hints`
  table, in any of the 29 migrations on this tree.
- **Why not built:** the payout consequence is unsettled (G-SK-4), and a stored level whose discount
  cannot be computed would invite exactly the multiplier row 16 forbids.
- **What is needed:** `run_skills.hint_level` plus a stated ceiling once one is sourced; per-hint
  provenance wants its own row, not a column, if more than the latest level matters.
- **Owner:** Architect, gated on G-SK-4.

#### G-SK-4 — Hint-level → cost curve

- **What is missing:** the curve. Conflict row 16 holds the published value and forbids encoding it
  without an in-client check; §1.1.4 and §8.4 keep the gap open.
- **What is needed:** a client read of the skill-cost calculator against a known base cost, or a fresh
  `[S]`/`[A]` source dated against the current Global build. Until then: level yes, percentage no.
- **Owner:** Design system with the Data Engineer; the copy ruling in §7 is the owner's.

#### G-SK-5 — Inheritance: provenance is landing, the skill join is not

- **In flight (uncommitted):** `training_runs.legacy_selection` (typed JSON, nullable) records per
  Legacy the chosen Umamusume, rank, Guest flag, its own two ancestors, a Spark list with **kind, target
  and star rank**, and an affinity grade. That is the half the incoming draft called missing.
- **Still missing:** no join from a Green/White Spark **target** to a row in `skills`, so a Spark carrying
  a Unique Skill names a target string rather than a catalog skill; no inheritable/not-inheritable flag
  (and the blue-mark rule that would justify one is `❌ UNVERIFIED`, §5); no source attribution for which
  Spark produced a run's hint.
- **Why not built here:** the payload is another session's surface, and the flag's only proposed evidence
  has no source.
- **What is needed:** coordinate with the Legacy owner on whether Spark target strings resolve through
  `match_key` at read time or through a stored `skill_id` at write time; then a second-domain source for
  the inheritable rule. **Same question as G-SK-22, from the other side:** a Spark target naming a skill in
  Japanese has nothing to resolve against, because `match_key` is written from the client English name
  (`StoreSkills.php:68`) — whichever way this join goes, it inherits the single-key answer.
- **Owner:** Planner Domain Specialist with the Architect, after the Legacy work lands.

#### G-SK-6 — Star rank and the card layer are on a branch, not on master

- **Correction to the incoming draft:** the card layer is **built**, just not here. Branch
  `feat/catalog-roster-and-trainee-selector` (worktree `D:/Projects/umamusume-laravel13-catalog-roster`,
  tip `cf5fc75`) carries ADR-0008, `character_cards`, `training_runs.character_card_id` and a
  `GametoraCharacterCardParser` (commits `c02600e`, `98d9a03`, `cd26d8d`, `db8603c`). Master has none of
  it, and `docs/adr/` on master stops at 0007 plus 0009 — **there is no ADR-0008 file on this ref**.
- **Measured on master:** `umamusume` = `id, slug, name, name_ja, match_key, release_status,
  jp_debut_date, global_debut_date, is_manual, timestamps` plus the aptitude columns from
  `2026_09_27_090000`. No star rank, no Potential Level, no card rows.
- **Consequence:** nothing on master can show what ★3 unlocks for a named trainee, because master cannot
  state any trainee's star rank at all.
- **What is needed:** a merge decision on the card branch before any star-rank UI is planned; then the
  rank lives on that layer, not on `umamusume`.
- **Owner:** Architect with the card-branch session.

#### G-SK-7 — Support card hint pools: **closed, not open**

- **Correction to the incoming draft:** `ADR-0005` is not "Proposed". Status line 3: **DECLINED for
  Phase 1 (owner ruling R37, 2026-09-28)**, "the question is closed rather than parked", `PRD.md` §6 item
  9 stands, the entity shapes are retained as later-phase reference only, and **"no slice may cite this
  ADR as permission."**
- **Reopen trigger, as written:** a new PRD story by the owner authorizing support-card entities and
  saying which domain it wants (reference data, or the Trainer's collection and deck).
- **Consequence for the brief's Step 4:** the requested "support card hint pool" panel is not a gap to
  fill; it is a cut system, and the corpus can describe `hint_skills` per card (`REFERENCE:465`) without
  the app storing a card.
- **Owner:** none until the owner writes the story.

#### G-SK-8 — Race → hint mapping

- **What is missing:** no table or config maps a race to the hint it can pay. The rule is documented
  (distance or style, C aptitude or better, `REFERENCE:891-892`) and the route rewards are enumerated in
  prose (`04:84-127`), including named ones like `Mile Straightaways`, `Homestretch Haste` and `Top Pick`
  — two of which are already in `SkillSeeder`'s ten names.
- **Why not built:** consolidation, not research, and it only earns its keep once a race entry is worth
  attributing a hint to.
- **What is needed:** a scenario-scoped mapping with provenance (`race_catalog_slots` is the grain),
  read from the existing guides rather than invented per row.
- **Owner:** Planner Domain Specialist.

#### G-SK-9 — Negative skills

- **What is missing:** everything. No column, no corpus section, no scenario doc, no UI.
- **Confirmed this pass:** existence, from placing 6th or lower or from specific events; removable by
  spending SP. Two `[A]` domains agree.
- **`❌ UNVERIFIED`, do not encode:** the full list, per-skill removal cost, the claimed rule that holding
  a negative skill locks its positive counterpart (incoming, single-source, untested here), and the label
  itself — neither source names a client English string for the category, and "purple" is a colour read,
  not a captured label. `DESIGN.md` §3 reserves hues as semantic; a fifth skill colour is a token decision
  with a contrast pass behind it (`DesignTokensTest` pins the token count).
- **What is needed:** a client capture of the negative-skill row plus one `[A]` list page, then a column
  on the master (`is_negative`) and a removal cost, before any UI.
- **Owner:** Data Engineer with the Design system.

#### G-SK-10 — Evolved skills

- **Status:** settled at conflict **row 45** — the wiki's own text says the system is currently exclusive
  to the Japanese version, Game8's Global skill pages carry no Evolved category, and no `[S]` notice
  exists. `REFERENCE:353` and `:706` name evolved skills inside `[JP]` gate text, so shared gate copy is
  **not** evidence of a Global system; row 45 says so explicitly.
- **What is needed:** a Global notice, or the gap stays closed. If it ever opens, it is its own slice.
- **Owner:** owner on the Global release question; Data Engineer to re-check.

#### G-SK-11 — Scenario-exclusive skills have data and no surface

- **Measured:** `x-spirit-burst-roster` renders six teammate burst **states** from `turn_events` payloads
  and `x-team-rank-gauge` renders the ladder with the S+ second-hint note. Neither names a skill, and no
  panel reads the burst reward ladder at a given team rank.
- **Why not built:** a UI decision, not a data import — the values already exist in `02`/`06` and the
  composition matrix gates the panels.
- **What is needed:** one row per the §4.1 ladder keyed to the reported team rank, an "It's On!" row for
  S/S+, and the Trackblazer Climax name from `04:246`. Do **not** render the Zenith names (G §4.1).
  Labels verbatim from a scenario file only, per D-20.
- **Owner:** Frontend with the Planner Domain Specialist.

#### G-SK-12 — Per-grade SP figures

- **What is needed:** a source for the magnitudes, or the run keeps SP as an entered number with no
  per-grade breakdown. Direction is confirmed; the grade codes themselves are only partly settled
  (conflict row 12).
- **Owner:** Data Engineer.

---

### 7. What is genuinely open

Reduced from two questions to the decisions that are actually unmade:

1. **Does this pass land the skills import (G-SK-2)?** Every cost, type and rarity display in a Step-4
   pass is blocked behind it, and it needs its own owner fetch approval because it is a new document.
2. **Is a `rarity` column authorized?** `skills` already matches FR-D-1 exactly, so this is a PRD change,
   not a schema tidy-up.
3. **Hint-level copy (§2.2 and G-SK-4):** ship level-without-percentage (the safe reading of row 16),
   or ship 30%-at-Lv3 as a single-source caption carrying an explicit `[Unverified]` marker. A copy
   ruling, and the owner's.

Support-card entities (G-SK-7) and evolved skills (G-SK-10) are **not** open — both are ruled. No Step-4
code work on skills begins until (1) is answered.

**All three were answered the same day, and §8 records what landed:** import and UI in this pass;
`rarity` authorized as a PRD amendment and stored as the source's code with no label; level with no
percentage anywhere.

**Still open after Screen D landed the same day** — these are the decisions nobody has made, not work
somebody skipped:

1. **Hint level** (G-SK-3, G-SK-4): a `run_skills.hint_level` column and conflict row 16's curve. Screen D
   renders no hint anything, by ruling and by scope.
2. **The fixture path exclusion** (G-SK-17): gate tooling, owner's pen, two facts recorded there.
3. **Icon and description** (G-SK-19, G-SK-20): both are §6.11 elements with no source this repository
   holds. A description column specifically needs a client-string source first, not a re-read of `desc_en`.
4. **A Japanese search key** (G-SK-22): a new column, so a PRD amendment first.
5. **FR-D-2's second word.** The PRD asks for "skill search/autocomplete **for the run UI**" while §8.4 makes
   Screen D a surface of its own. The surface landed, and the run screen links to it, so a Trainer can narrow
   623 rows before choosing. Whether the run screen *also* gets in-place autocomplete is unsettled; a native
   `<datalist>` filled server-side would do it with no JS dependency, which is the one constraint here that
   no option may break.
6. **The D-63 and D-65 amendment asks** (G-SK-23), including whether `skills` should have aliases at all.
7. **The unique-row chip** (G-SK-24): §6.11's pink-to-blue gradient needs a measured pair from capture
   `232345` and a material-fill token before it can be built. The flat pill on Screen D is a known deviation
   from a section that rejects flat fills, and it is open because the hues are not in evidence, not because
   nobody noticed.

---

### 8. Landed 2026-09-29 (same pass, after the owner's three rulings)

The owner chose the widest option on scope ("import + UI in this pass"), ruled `rarity` in as a PRD
amendment, ruled hint copy to **level only, no percentage**, chose to store every row with
`release_status` + `name_is_client`, and chose correction over substitution for the seeder names.

**Shipped:** `config/uma.php` gains `gametora-skills` (manifest-resolved, pinned URL kept as the
documented fallback); `Contracts/SkillSourceParser`; `Parsers/GametoraSkillsParser`;
`Actions/StoreSkills`; a `PipelineRunner` branch so skills never reach the review queue; the
`skills` reference-columns migration; `Skill::availableOnGlobal()`; the seeder name correction with the
D-210 addendum; and the run screen's filter plus `✦ Unique` / SP cost marks and the hint-level absence
said out loud. `ADR-0011` carries the measurements; `KI-23`/`KI-24` carry the two defects found on the way.

**Everything after `acab4d8` on this branch is the Screen D pass**, and its own commits are three: the
rendered-output absence check that G-SK-17 leans on (`83086b0`, `SkillsFetchTest::skillsFixtureGlobalRenderings()`)
and the two register corrections (`c3bdda3`, `acab4d8`) already sit before that mark, so `git log acab4d8..HEAD`
is the range rather than a list that goes stale. What the range carries: the fixture's three added rows, the
`GET /skills` surface, G-SK-19 to G-SK-23, and **KI-26**. Listing the SHAs here instead of a range would mean
a file that cites commits it cannot contain.

**Measured on the first live import** (`uma:fetch gametora-skills`): 1,901 created, 9 adopted, 0 to
review, 1,910 stored — **623** rows are `[Global]` and client-named, and those 623 are exactly what
`availableOnGlobal()` returns. `is_unique` 294, cost present on 906, derived type Speed 913 / Passive 120 /
Recovery 82 / null 795. A second run reports "unchanged since last snapshot", so the snapshot-hash
idempotence covers this source too.

**Gaps this created, which are not the same as the ones above:**

- **G-SK-13 — the run screen's picker now lists 623 options.** That is the honest consequence of importing
  real data: the select was designed when the table held ten names. `FR-D-2` and `DESIGN.md` §8.4 (Screen D)
  are the designed answer, and **Screen D has since landed** (`route('skills.index')`,
  `resources/views/skills/index.blade.php`); what remains unbuilt is the run-UI half of FR-D-2 — the
  selector on the run screen, which is what this entry is about. A `select` of this size is the reason both
  exist.
  **Interim answer, landed with Screen D:** the run screen's skill caption links to `/skills`
  (`resources/views/runs/show.blade.php:439`) and the shell's nav carries the route. That link stays when
  the selector lands, but its role changes: it becomes the "see the full catalogue" escape hatch from the
  selector's no-match state, not the run screen's way out of a broken control. Until then it is exactly
  what it looks like — a way *out*, not a replacement — and the picker is still an unsearchable `<select>`
  over every Global row on the run screen itself, so FR-D-2's own words ("search/autocomplete **for the
  run UI**") are still only half-met. A search-backed picker is the remaining build, and whatever replaces
  it should reuse `skillsFixtureGlobalRenderings()` rather than re-derive the negative — third use, helper
  time (G-SK-17).
- **G-SK-14 — `rarity` and `type` are stored and not rendered.** `rarity` has no label to render (six codes,
  three client rarities). `type` has a derived word but it is this tool's reading of effect codes, and
  `Debuff` is unevidenced, so the panel footnotes the derivation instead of printing a taxonomy.
- **G-SK-15 — `/api/v1` and the CSV/JSON export are unchanged.** `TrainingRunResource` still emits
  `id / name / status / turnAcquired`. Widening a published shape is its own decision, not a side effect
  of the import.
- **G-SK-16 — neither English description field in the skills document is client text, and the gate proves
  it twice over.** `SOURCE-OF-TRUTH.md` §3 pins the stat as Stamina, the second stat as Wit and the gauge
  word as Mood. Run the gate's own code-mode patterns over the document (measured 2026-09-29 while auditing
  Screen D, because `DESIGN.md` §6.11 puts a description in the skill row):
  `desc_en` matches **189** times, 172 of them `endurance`; `endesc` matches **72** times, 52 `wisdom` and
  16 `motivation`. Two fields, two independent vocabulary tells, and both are the §3 banned-synonym set —
  so neither is the client's prose, whichever one reads more like game copy. `desc_en` sits on 985 rows
  (exactly the `name_en` population, and all 623 `[Global]` rows); `endesc` sits on all 1,910. They differ
  on **every** row carrying both — 0 identical of 985.
  The consequence is a rule, not a preference: **descriptions are evidence and never copy.** The parser's
  category table reads them to decide what an effect code does (that is how code 9 is known to be a
  recovery) and quotes none of them into a label, and no description column is authorized by this register.
  By the same logic `name_en` is *not* suspect in the way the descriptions are: its hits are verbatim
  client skill names — `Paddock Fright`, `Wisdom of the Sun`, `Master of the Sands` — which C-4 gates on
  the display path rather than in the data, while `endurance` and `wisdom` in a description are the field
  itself using the wrong word for a stat.
- **G-SK-17 — the fixture was cut to the keys the parser reads, and one verbatim hit remains.** It was
  drafted as nine whole document records including their description keys, which put four banned-term hits
  into `tests/Fixtures/` — and that draft **never reached a commit**: the file has one commit, `aa5b05c`, at
  4,472 bytes. So the 22 KB to 4.5 KB cut and the `lore-code` 15-hits-down-to-8 are measurements of a
  working tree, not of shipped history, and no reader should go looking for the wide version in `git log`.
  They are also measurements the register cannot re-derive: the description strings are gone, so which four
  hits they were is not recoverable — what is recoverable is G-SK-16's counts over the live document (189 in
  `desc_en`, 72 in `endesc`), which explain why cutting those keys moved the number at all. Those fields are
  not read by anything, so they went: `id, name_en, enname, jpname, rarity, cost, unreleased,
  condition_groups` is the whole of it now, with the skills pair still passing (145 assertions as of
  `83086b0`). The survivor is `"enname": "Sand Expert"` —
  a value the parser genuinely reads (it is the name fallback), which `GametoraSkillsParserTest` pins out
  of parser output and `SkillsFetchTest` now pins out of the **rendered run screen**, so removing it would
  be editing the data to quiet a grep. The class is the one `R62` already ruled on for
  `database/seeders/data/**`, and code mode filters no marker, so any remedy is a **path** exclusion in
  `tools/lore.php` **and** the Makefile with a docblock naming the class — gate tooling, with
  `LoreGateParityTest` holding the two in step, and still the owner's call. Not touched here.
  **Two facts the owner needs before ruling, both measured after the review asked for them.** A path
  exclusion must name `tests/Fixtures/gametora-skills.sample.json` and nothing wider; `tests/**` would
  re-open the exact class `KI-23` came from, a filter that quietly stops seeing the thing it guards. And
  the provenance clause cannot sit in the fixture itself — JSON has no comment slot, which is why the
  capture note (source, manifest hash `609afe88`, pulled 2026-09-29, cut verbatim) reads in
  `GametoraSkillsParserTest.php`'s docblock instead. If the owner would rather have provenance and
  exclusion in one place, the alternative is a captured-source folder, e.g.
  `tests/Fixtures/gametora/`, holding the document beside a sidecar note, with that **directory** excluded
  in both gate surfaces. Either way the assertion that carries the weight is the one in `SkillsFetchTest`:
  eight derived renderings forbidden over decoded page text, with the row's own client string required
  present so the absence cannot pass on an extraction that saw nothing.
- **G-SK-18 — the category derivation reads the sign, not only the code. Do not "simplify" this away.**
  Effect code 1 is an axis, not a meaning: `Right-Handed ◎` reads `+600000` on it and `G1 Averseness`
  reads `−400000`. A derivation that switched on the code alone would print `Passive` beside a skill whose
  client text says *decrease performance in G1 races*, and the mistake is invisible in review because the
  code table still reads correctly for every positive row. `GametoraSkillsParser::category()` therefore
  returns null unless every effect is a positive integer on a mapped code, and
  `GametoraSkillsParserTest` pins the averseness row to null specifically. Measured consequence: 53 of
  the 623 `[Global]` rows lose a label to this rule. The same discipline is what found `KI-23` — a check
  that cannot fail is worse than no check, and here the check that would have failed was only missing a
  negative number in a fixture. **If a future slice refactors the derivation, keep the sign test or
  delete the derived `type` column; a taxonomy that quietly absorbs negatives is the failure this
  repository has been catching all session.**
- **G-SK-19 — §6.11's skill icon has no source in this repository, so Screen D renders none.** The document
  states `iconid` on **all 1,910 rows**, and nothing stores it: no column, no asset path, and no committed
  manifest that names where an icon is served from. Landing it is two decisions before it is any code — a
  PRD amendment for the column (FR-D-1's set is name, name_ja, match key, sp_cost, type, `is_unique`, plus
  `ADR-0011` §3's rarity and provenance and FR-D-3's availability; an icon is outside all of that), and an
  owner ruling on whether image assets are fetched at all, which `CONSTRAINTS.md` C-4 scope notes have kept
  out of Phase 1. Screen D's rows carry the name, the Japanese name, the cost, the derived type and the
  `is_unique` mark, which is the whole of what `D-30` permits and the whole of what the data holds.
- **G-SK-20 — §6.11's description has no client string behind it, and the vocabulary is the proof.** See
  G-SK-16 for the measurement: `desc_en` fails the terminology table 189 times (172 of them `endurance`) and
  `endesc` fails it 72 times (52 `wisdom`, 16 `motivation`), so neither field is `[Global]` prose, and the
  one that *looks* most like game copy is precisely the one that contradicts `SOURCE-OF-TRUTH.md` §3.
  **The rule this entry exists to keep:** no description column may be added until a client-string source for
  descriptions exists — an in-client capture, not a re-read of the export. A future pass should not reach for
  `desc_en` because it reads better than `endesc`; that is D-20's failure mode with a flattering example.
- **G-SK-21 — two `[Global]` skills share a client name, and the Unique badge falls on the one no card
  names.** Measured on the imported document: 623 rows, **621 distinct client names**. `Indomitable` is
  export **200471** (class code 2, learnable, 170 SP, derived `Recovery`) and export **300141** (class code
  5, no SP cost, derived `Speed`) — and 300141 is one of the four rows `ADR-0011` §5 names as beyond the
  card join. `Carnival Bonus` is the other collision (1000011, 1001012, both class code 1, neither priced).
  So a Trainer searching `Indomitable` gets two rows, and the code rule badges exactly one of them.
  **Ruled 2026-09-29 (owner, option b of three):** ship the code rule, because it is what a one-document
  parser can defend, and put the limit on the badge itself. **The string, verbatim, at
  `resources/views/skills/index.blade.php:125`:**

  > `title="Marked from the source's skill class code, not from a card's own skill list."`

  Checked against the three tests the ruling sets: it names no snapshot count a Trainer cannot verify (the
  290-of-294 figure lives here and in `ADR-0011` §5, not in UI text that would outlive it); it states the
  basis without performing a join; and its only label word, `Unique`, is §6.11's captured tag rather than
  something invented here. **On the proposed shorter form — "Unique by code; no released card names this
  skill" — that copy was rejected, and it is worth recording why:** it asserts a per-row fact. This screen
  has no card document in its read path, so it can say *how the mark was derived* and cannot say *that no
  card names this skill* — and for the 290 rows the join does reach, the shorter sentence would be false.
  The disclosure is about the rule, which is the thing the surface actually knows.
  Both rows render, and they are distinguishable without a rarity word because cost and derived type differ.
  `SkillSearchScreenTest` pins the collision through the producer path, with both rows in the fixture
  verbatim, so it cannot be quietly "fixed" by a future dedupe.
- **G-SK-22 — a Japanese query cannot reach a skill, so D-63's "which field matched" has one answer today.**
  `StoreSkills.php:68` writes `match_key` from `$record['name']`, which is the client English string, and
  `NameNormalizer` does not transliterate. Measured against the live document: of 200 sampled `[Global]`
  `jpname` values, **1** finds a row through `match_key LIKE` — and that one is export 200311-style noise, a
  row whose `jpname` is the Latin string `U=ma2`. `DESIGN.md` §8.4 and D-62 both say a Japanese query may
  return an English row; for skills it returns nothing. D-63's matched-field report therefore has exactly one
  field to report. Fixing it means a normalized key derived from `name_ja` (or a second searchable key
  column), which is a new column, which is a PRD amendment first. **What was not done instead:** matching on
  the `name_ja` display string directly, which the root Banned Patterns D-62 cites forbid, and which would
  have made the screen look right while breaking the rule that keeps case- and width-folded search honest.
  **Cross-referenced from G-SK-5:** the Spark-target join asks the same question from the inheritance side,
  so a second key (or a key derived from both names) settles both at once, and neither should be built
  against the other's deadline.
- **G-SK-23 — D-65's review-queue offer does not map to skills, and Screen D says so rather than linking to
  an empty place.** D-65's no-results state offers the review-queue path (US-5) because a missing skill is a
  data gap, not a user error — that half is true and is kept. The queue half is not reachable: `StoreSkills`
  writes straight past `match_candidates` by design, and `SkillsFetchTest` asserts the queue holds zero
  skills after a full import. So the screen states the gap in the fetched data and names
  `php artisan uma:fetch gametora-skills` when the table is empty outright, and links to `/review` nowhere.
  **Two amendment asks, both the owner's pen, because `CONSTRAINTS.md` is not this agent's file to widen:**
  D-65's queue clause scoped to cross-referenced entities, and D-63's "skill name, Japanese name, or alias"
  reduced to what `skills` can back — the table has no alias column and no alias relation (contrast
  `umamusume`, whose `aliases` `CatalogController.php:47` searches), so "alias" is a field that does not
  exist on this entity. Either the clause gains a "where the backing exists" scope or skills gain aliases;
  neither is decided here.
  **The rule this pair of asks produces, and it is a working rule, not a note:** *brief citations are
  checked against the file, not adopted.* Two of this thread's briefs cited a constraint that turned out to
  live elsewhere — D-65's review-queue offer, which does not map to an entity whose import bypasses the
  queue, and D-220 for the `N/A` + `title` disclosure, which is the resource-strip rule in
  `CONSTRAINTS.md` while the disclosure form actually lives at `ARCHITECTURE-ESSENTIALS.md:18`, beside the
  ban on the em dash as the glyph. Both were caught by opening the file; neither would have been caught by
  building what was written. If this deserves a place in `.ai/rules` it is the owner's call to record it,
  not an agent's.

- **G-SK-24 — §6.11's unique-row gradient chip is not implemented, and the flat pill on Screen D is the
  revision that section already rejects.** §6.11 asks for a sparkle mark, a `Unique` text tag, and a
  **pink-to-blue gradient chip fill** matching client capture `232345`, and it names the earlier flat
  `indigo-50` attempt as losing "the single most legible affordance on the panel, since the gradient is what
  makes a unique skill findable without reading". `resources/views/skills/index.blade.php` renders a flat
  `bg-sunken` pill. **It was left flat because it cannot be built honestly here:**
  `resources/css/app.css` has no pink or blue material token (its only pink is the mood pill at `:133`, and
  its gradients are the enamel and lattice overlays), and §6.11 states no hex pair — the hues come from a
  capture this repository has not measured. Inventing two hex values in a Blade class to satisfy a
  screenshot would be D-102's failure with the numbers made up. What the screen does satisfy is **D-12**: the
  state carries a word and a mark, not a colour alone. **Needed:** a measured pink-to-blue pair from
  `232345`, a material-fill token beside the mood pill's, then the chip. Owner: design-system, with the
  capture as the precondition. **Found in the browser pass, not in the tests** — a DOM assertion cannot see
  that a flat grey pill is the rejected revision.
- **G-SK-25 — two `[Global]` skills really do cost 0 SP, and `0 SP` is not the bare-zero defect.** The same
  pass showed `99 Problems · 0 SP`, and a reader will reasonably suspect D-220's "never a default" being
  broken. Checked against the document: `cost: 0` is stated on **9 rows**, two of which are `[Global]` and
  client-named (`99 Problems`, `You're Not the Boss of Me!`). The parser copies what the source says, so the
  render is faithful and **must not be "fixed"** into `N/A` — `N/A` now means something specific (no cost
  stated for that class of skill), and collapsing a real zero into it would destroy the difference.
  Same family, also browser-found: **3 of the 288 unlabelled rows carry a type word in their client name**
  (`Corner Recovery ×`, `Greed for Speed`, `Speed Eater`), so the `Unspecified` facet shows rows that look
  like they should have had a label. Each is voided by the derivation on a different clause — 200353 has
  code 9 at **−200** (the sign rule), while 201081 and 201082 pair a positive code 27 with **code 21 at a
  negative value**, the unmapped negative family G-SK-9 already tracks. The names are the client's; the
  absence is this tool's refusal, and both are correct in the same row.

**Landed with Screen D, 2026-09-29 (the same owner pass, decisions 1 to 5).** `GET /skills` is the second
server-driven filter surface and the answer to G-SK-13's 623-option select: the run screen now links to it
and the shell's nav carries it, so the long list has a way out. Facets are **search, type (with
`Unspecified` for the 288 rows the sign rule withheld) and unique**; `rarity` and cost-present are columns,
not controls, because a filter over a code no Trainer can read is D-64's ornament. The param is `search`,
matching `/umamusume`, and the query is escaped before the `LIKE` — the older surface is not, which is
**KI-26**, filed and left there deliberately. D-65's two states were separated in the **view**, not softened
in the test: the invitation now stops speaking once a query is in flight, and the assertion that caught it
was right to stay as written.

**Measured in a browser, 2026-09-29, against the filled `database/database.sqlite` (623 rows) at 1280×800
and 390×844.** These are the numbers, because a server-render assertion cannot establish any of them:
`documentElement.scrollWidth` **1265** at 1280 and **375** at 390, so no page-level horizontal overflow at
either width, and zero elements crossing the right edge (the KI-25 class of defect, checked rather than
assumed absent). Tab order is skip link → Catalog → **Skills** → Training runs → Review → search → type →
unique → Filter → pagination pages, every control reachable, and each stop computes
`outline: solid 2px` (green `rgb(127,204,9)` on the screen's own controls, `rgb(78,121,6)` on the shell's) —
D-13 measured, not inherited from a class name. Contrast: `ink-muted` on `raised` **5.78:1**, on the page
**5.15:1**, `h1` **11.79:1**, badge **10.74:1**, all above 4.5 at the 12–14px sizes actually used (light
theme; the screen has no in-page theme control, so dark is unmeasured). The `Unspecified` facet returns
**288 of 623** and the facet survives paging (`?type=Unspecified&page=2`, still 288) — the count G-SK-18
predicts, reached through the browser's own form post rather than a request helper. Searching
`indomitable` renders the G-SK-21 collision as two rows: `Indomitable | 不屈の心 | · Recovery · 170 SP` and
`Indomitable | 不撓不屈 | ✦ Unique | · Speed · N/A`. The run screen's picker was counted at **623 options**
with its caption link landing on `/skills`. Three findings came out of looking rather than asserting:
**G-SK-24** (the flat badge), **G-SK-25** (the faithful `0 SP`, and the three name-suggests-type rows), and
the form-control sizes, which measured **30/31/16px** against §6.14's 44 and are fixed on this screen —
`/umamusume` measures the same 30/31/32 and is **KI-29**. (This line read KI-28 when it was committed; KI-28
is another session's SQLite hazard, and the control-size filing is KI-29. Corrected forward, not edited
silently.)

**Dark measured the same way, on the app's own path.** The shell has no in-page theme control: with no
`preferences` row set, `layout.blade.php:15-22` emits an inline script that reads `prefers-color-scheme`, so
the theme was driven by emulating that media feature rather than by poking `dataset.theme` — the same route a
Trainer's machine takes. `data-theme="dark"` was on the document and the page background resolved to
`#0D0C0F`. Contrast: `ink-muted` on the list **6.64**, on the page **8.55**, `h1` **19.51**, row name
**15.15**, badge **17.61** — every pair better than its light-theme value and none near the 4.5 floor. No
page-level overflow (`1265` at 1280, `375` at 390), the focus ring identical to light (`solid 2px
rgb(127,204,9)`), the `Unspecified` facet still **288 of 623**, and the empty-database state names both
commands. Light was re-measured after the size fix and is unchanged (5.78 / 5.15 / 11.79 / 10.74) with
controls now 44/44/24. **One dark finding, filed not fixed:** no `color-scheme` is declared anywhere in
`app.css`, so native controls keep the light UA skin on a dark page — the same unchecked checkbox computed
`rgb(255,255,255)` on one load and `rgb(36,38,42)` on another. That is **KI-32**, and it is why this paragraph
claims the theme was *measured* rather than that native widgets are correct.

**Then it was filled for real, and two things came out of that.** `database/database.sqlite` had been
migrated but never imported, so `uma:fetch gametora-skills` reported *unchanged since last snapshot* and
wrote nothing — the snapshot check keys on the document hash under today's date in a `storage/` shared by
every database in the tree, which is **KI-27**. `uma:reparse` (same bytes, no network) filled it: `7
updated, 1903 created, 0 to review`, and the screen renders **623 of 623** with the type distribution the
register states for a second database (null 288, `Speed` 199, `Passive` 82, `Recovery` 54) — G-SK-18's
numbers reproduced outside the scratch DB that produced them. Rendering the filled table also found a
display defect no fixture could have shown: **18 of the 623 `[Global]` rows store the same string in both
name columns**, because the source's `jpname` for those skills is Latin script (`#LookatCurren`, `U=ma2`,
`∴win Q.E.D.`), and the row printed the name twice. Fixed in the view by rendering `name_ja` only where it
differs, with a test; the data stays verbatim, per C-4's split between copy and source.

**G-SK-1 through G-SK-2, read with the above:** G-SK-2 (no skills import) is closed by this pass.
G-SK-1 stays as written because it was the *correction* that mattered — the columns were already PRD-shaped,
and what arrived is data, plus one authorized column. G-SK-3, G-SK-4 (hint level and its curve) are
untouched by the import: the document states no per-run hint level, and row 16 still forbids the multiplier.
G-SK-9 gains a data path (`effect code 21`, 60 rows, all negative-valued) and still has no cost, no list,
and no client label. G-SK-10 is now machine-confirmed rather than wiki-only: **672 evolved rows, all of them
`unreleased` containing `en`, none on `[Global]`** — and all 270 disagreements against the card document's
`release_en` are exactly those rows. One count above needs its arithmetic stated beside it, not in a
footnote: **`is_unique` 294** is the code rule (rarity 3/4/5) while the card join reaches **290**, and
`ADR-0011` §5 names the four rows in between rather than leaving two counts to look like a typo.

### 9. Cross-references

- `docs/UMAMUSUME_REFERENCE.md` — the mechanics authority. Skill-adjacent: §1.1.4 `:142`, §1.2 gating
  `:208`–`:312`, §1.3.5 `:378`, §1.4.4 `:463`, §1.4.7 `:550`, §1.5.1–1.5.4 `:582`, §1.6.9–1.6.10 `:748`,
  §2.2 `:849`, §2.3 `:863`, Conflict Log rows 12, 16, 43, 44, 45, 46, §8.4 `:2083`.
- `docs/design-research/MECHANICS-TRANSLATION-TRIAGE.md` — rulings this file must not reopen: §1
  terminology, §3 out-of-scope, §4 `:88`, §7.2 `:172`/`:182`.
- `docs/design-research/CONSTRAINTS.md` — D-20 (no unverified strings in UI copy), D-30 (render only what
  exists), D-44 (planned vs actual in one view), D-62..D-65 (skill search), D-192, D-210 (the ten names),
  D-223, D-224, D-229, D-253, G-16, G-49.
- `docs/design-research/DESIGN.md` §6.11 (skill row), §8.4 (Screen D), §3 (sparkle = `is_unique` only).
- `docs/design-research/RACE-CALENDAR-GAPS.md` — the sibling register this file follows.
- `docs/SOURCE-OF-TRUTH.md` §3 (Spark / Inspiration / Legacies; `Hint Lvl N, NN% OFF`), §4.1 (supersedence
  and the JP-only import filter), §5 (tier ladder: a `[B]` dataset needs `[A]`/`[S]` confirmation).
- `PRD.md` §4 C-3 `:58`, FR-D `:62-64`, NFR-3 `:73`, §6 item 9 `:91`.
- `docs/adr/0005` (support cards declined, R37), `0009` (committed client export precedent, Option A),
  and the unmerged ADR-0008 on the card branch.
- `KNOWN-ISSUES.md` — KI-5 (a fabricated skill name, fixed) stands. **This register now files defects as
  well as gaps**, and the four this thread opened are KI-26, KI-27, KI-29 and KI-32; KI-23 and KI-24 came
  from the import pass. The earlier sentence here read "no KI is filed here", which was true when the
  register held only gaps and stopped being true during the Screen D pass.

**What the Screen D pass left open, and whose pen each one is (2026-09-29).** Four defects are filed and
deliberately unfixed: **KI-26** (unescaped `LIKE`) and **KI-29** (30/31/32px controls) both live on
`/umamusume` — `CatalogController` and `catalog/index.blade.php` — and go to whoever next owns that surface,
which can clear both in one pass; **KI-27** (a fetch reporting "unchanged" against a database it never
wrote to) needs the Architect, because the fix is a rule about every source rather than a patch to one;
**KI-32** (no `color-scheme`, so native controls paint light on dark) is two declarations in the dark block
`D-101` already owns, and belongs to design-system. Six gaps need a decision rather than code:
**G-SK-3/G-SK-4** hint level (Architect for the column, copy already ruled), **FR-D-2's run-UI half**
(owner: `<datalist>` versus a filter-param round trip), **G-SK-11** scenario-exclusive rows (Frontend with
the Planner Domain Specialist), **G-SK-19/G-SK-20/G-SK-24** icon, description and the gradient chip (each
blocked on a capture or a PRD amendment, not on effort), **G-SK-22** the Japanese search key (PRD amendment
first, and the same question as G-SK-5), and the **D-63/D-65 amendment asks** in G-SK-23, which only the
owner can write into `CONSTRAINTS.md`.

---

### 10. Corrections applied to the incoming draft of this file

The draft arrived with anchors asserted rather than opened. Each of these was measured before the edit:

| Draft said | Ground truth |
|---|---|
| Team Zenith victory skills `Cooldown` / `Indomitable`, anchored to `06` | Neither name, nor "Team Zenith", appears in any repo file; kept as an incoming claim (§4.1) |
| "Balance Adjustments (Nov. 11, 2025)" at `06:300-320` | That range is the pre/post Spirit Burst rework diff; the Global rework dates from **2026-07-01** (`REFERENCE:1005`), and no file carries a "Balance Adjustments" list |
| `character_cards` "authorized but not built (no migration)" | Built on `feat/catalog-roster-and-trainee-selector` with ADR-0008; absent from master, where `docs/adr/` has no 0008 |
| ADR-0005 "Proposed, not Accepted", question open | DECLINED for Phase 1 (R37, 2026-09-28); question closed; reopen needs an owner-written PRD story; no slice may cite it as permission |
| "the export's ~1,910 skills" | No row count exists in the repo; `PRD.md:73`'s ~2,000 is a performance budget |
| Rarity anchored to §1.1.4 | That section is SP acquisition; the three-rarity reading comes from Game8's Global list, and FR-D-1 has no rarity field |
| 30%-at-Lv3 is "the only one to encode" | Row 16 forbids encoding **either** figure as a multiplier without an in-client check |
| "§2.2 (URA fan gates)" | URA is §2.1; §2.2 is Unity Cup, and Section 2 numbers two headings 2.1/2.2/2.3 twice — cite by line |
| Inheritance hint resolution wholly missing | `training_runs.legacy_selection` is landing now with Spark kind/target/star rank and affinity; only the skill join and the inheritable rule remain (G-SK-5) |
| Negative-skill base-lock rule stated as a "confirmed rule" | Single-source incoming claim; marked `❌ UNVERIFIED` (G-SK-9) |
| Hint levels "raised by three sources", four listed | Rewritten as four |
| "Star Pieces from duplicates or Goddess Statues raise star rank" | Carried the buff framing conflict row 43 retired; rewritten from `:762`/`:1957` as currency only (§1 path 3, §5.1) |


---

## skills-section-review-2026-10-01.md

## Run screen Skills section: read-only review

**Date:** 2026-10-01
**Scope:** the `Skills` section of the run detail screen, its data source, its tests, the surfaces
adjacent to it, and every claim in the review brief.
**Fence:** no shipped code was changed. No migration, commit, seed, or edit to a peer-owned file. One
document written. Browser measurement ran against an isolated scratch database on a throwaway port.

### Method and evidence base

- Source read with line numbers. Every verdict below cites `file:line`.
- Browser measurement with Playwright against a scratch SQLite database, viewport 1280x800 and
  390x844, serving the real application on port 8199. Scratch database lived outside the repository.
- Shipped catalogue counted from the committed source document, then imported into the scratch
  database so the picker was measured at its real width rather than at a fixture width.
- Test suite run directly. No parallel test runner. 39 tests, 198 assertions, all passing.
- Lore check: case-insensitive grep for the banned vocabulary (`horse`, `sire`, `dam`, `mare`, `foal`,
  `breeding`, `racehorse`, `stable` as a character container, `🏇`) against every file cited, per
  the Lore Guardian role. See H.1.
- This document carries two measurement passes. The first ran the browser against a scratch database
  that has since been deleted. A second pass re-built that scratch database from the shipped source
  document and re-measured the option counts, and it is the second pass that the numbers in sections
  C, E and F come from. Where the two passes disagree, "Measurement reconciliation" in section E
  records both figures rather than quietly keeping the newer one.

### A. What ships today

The section is **two surfaces**, not one. They are frequently conflated, and the conflation is the
source of most of the brief's premises.

#### A.1 Read-only grouped summary (`resources/views/runs/show.blade.php:427-458`)

- `h2` "Skills" at `:427`.
- Three status groups hard-coded at `:429`: `Suggested`, `Acquired`, `Skipped`.
- Each group renders an `h3` at `:433`, then either the literal `None.` (`:435`) or a `ul` of skills.
- Each `li` (`:439-454`) prints the client name, then optionally: `✦ Unique` when
  `$skill->is_unique` (`:441-446`), `· N SP` when `sp_cost` is not null (`:448-450`), and
  `· turn N` when the pivot carries `turn_acquired` (`:451-453`).
- Rows are filtered client-side by pivot status at `:432`, from the relation loaded at
  `app/Http/Controllers/TrainingRunController.php:201`.
- No component is invoked anywhere in this surface. Raw Blade.

#### A.2 Prose and escape hatch (`resources/views/runs/show.blade.php:460-471`)

- A paragraph at `:464-471` states that SP is the source's cost and that skill-point discounts are not
  shown because no source settles them.
- `:469` links to `route('skills.index')` under the words "Search the skill catalog". This is
  `G-SK-13`'s interim escape hatch, and the link's own comment at `:467-468` says so.

#### A.3 Edit form (`resources/views/runs/show.blade.php:484-558` as it stands now, `:484-526` at HEAD)

- One form, POSTing to `route('runs.skills.sync', $run)` at `:484`, with `@csrf` at `:485`.
- Row count is computed at `:480-481`:
  - `$skillRows = $run->skills->sortBy('name')->values();`
  - `$rowTotal = max(1, $skillRows->count() + 1);`
  - So the form renders one editable row per skill the run already carries, plus exactly one spare.
    With zero skills the spare is still rendered. The repeater's rationale is documented in the
    view's own comment at `:473-478`, which also states that `syncSkills` upserts and never detaches.
- The sort is a **PHP** `sortBy('name')` at `:480`, not a SQL `ORDER BY`. Ordering is therefore
  byte order on the client name.
- The repeater is `@for ($row = 0; $row < $rowTotal; $row++)` at `:486`, with
  `$entry = $skillRows->get($row)` at `:488`.
- Three controls per row, each with its own `<label for>`:

| Control | Line now | `id` | `name` | Notes |
|---|---|---|---|---|
| Skill select | `:518-526` | `skill-N-id` | `skills[N][skill_id]` | placeholder `Choose a skill` at `:519`; options at `:520-526`; label decoration ` · N SP ` at `:525` |
| Status select | `:535-538` | `skill-N-status` | `skills[N][status]` | cases from `SkillAcquisition` at `:536`; no placeholder |
| Turn input | `:547-549` | `skill-N-turn` | `skills[N][turn_acquired]` | `type="number" min="1"`, placeholder `Turn`, **no `step` attribute** (confirmed in the browser: `step` is `null`) |

- One submit button at `:557`, labelled "Save skill status".
- **At HEAD this range carried no `old()` call and no `@error` directive.** That is no longer true of
  the working tree: an uncommitted peer edit added dotted `old()` accessors at `:500-502` and three
  `@error` blocks at `:528`, `:540` and `:550`. See "Working-tree drift during this review" and F-1.
- The status select still has no explicit `selected` option for a row with no `$entry`, so a spare row's
  status falls to the browser's first-option default rather than an authored one.

#### A.4 Component inventory for the whole section

**Zero Blade components are invoked in `:427-558`.** The section is raw Blade throughout. The
nearest component, `resources/views/components/deck-panel.blade.php`, is a sibling on the same
screen, not a wrapper or a child of this section. It declares `run` and `cards` and both are passed
at the call site (`show.blade.php:227`, `<x-deck-panel :run="$run" :cards="$deckCards" …/>`). There is
no unused, undeclared, or default-valued prop in the deck panel.

### B. Tests that pin the behaviour

#### B.1 `tests/Feature/RunSkillRowLabelsTest.php`

A DOM-parsing helper at `:32` (`skillsFormControls`) and a second at `:62` (`skillsFormLabels`) are the
instruments, so these are rendering assertions, not source assertions. The fixture at `:78-97`
(`labelledRun`) creates its three skills with **both** `release_status = GlobalReleased` and
`name_is_client = true` (`:87-88`), which is what makes them visible to the picker at
`TrainingRunController.php:301`. That is the opposite of B.2's fixture, and the difference is why this
file tests the screen while `TrainingRunTest` tests the endpoint.

| Line | Test | What it pins |
|---|---|---|
| `:99` | every control has its own label | `:125` expects 3 distinct accessible names, so the assertion is real and not a tautology |
| `:131` | one row per skill plus a spare | the row count |
| `:147` | preselects status and turn | `@selected` read back through XPath at `:158-:163` |
| `:167` | keeps the typed edits when a submit is refused | **uncommitted as this review runs, and red.** See F-10 |
| `:224` | saves several rows in one submit | `:237` expects `count()` still 3. **No-detach is pinned here.** |
| `:245` | accepts a submit with the spare row left empty | `:258` expects `count()` still 3 |
| `:262` | adds a skill through the spare row without disturbing the rows above it | `:286` expects `count()` 4 |

This file is the strongest coverage in the section. It pins the repeater, the accessible names, the
preselection, the upsert, and the no-detach rule.

#### B.2 `tests/Feature/TrainingRunTest.php`

- `:73-89` posts a run-skills batch using `Skill::factory()`. **The fixture is invisible to the
  picker.** `database/factories/SkillFactory.php:21-33` sets no `release_status`, no `name_is_client`
  and no `export_id` at all, so `availableOnGlobal()` cannot match one; measured on a scratch database,
  a factory row carries `release_status = NULL` (nullable, no default per
  `database/migrations/2026_09_29_021157_add_reference_fields_to_skills_table.php:53`) and
  `name_is_client = false` (default per `:54`). The endpoint still accepts it, so the test is green
  against a shape the UI cannot produce.
- `:340-372` is the test the brief calls the no-backfill proof. **It is defective.** Its comment
  at `:359` claims a second run "through the real route" against the same card, but the second POST at
  `:360-363` omits `character_card_id`, so the second run has no card and `prePopulateSkills()`
  returns at its own guard (`TrainingRunController.php:190`). The test does not exercise the same-card
  path it describes. Its assertions still hold for the case it actually built.

#### B.3 `tests/Feature/ApiV1Test.php`

- `:44` is named "returns a run detail with turns and skills". Its only assertions are
  `:50` `data.turns.0.turn` and `:51` `data.turns.0.speed`. **There is no skills assertion.** The
  name overstates the coverage.
- There is no `ApiV1RunSkillPivotTest.php` in `tests/Feature`. The pivot shape published by
  `TrainingRunResource` has **no dedicated test anywhere in the suite**. Confirmed by search: no test
  in `tests/` asserts `data.skills`, `data.skills.0.status` or `turnAcquired`. The only `data.*` skill
  or deck path assertions in the suite are deck assertions, at
  `tests/Feature/ApiV1SupportCardTest.php:138-144` and `:152`.

#### B.4 Suite state

Suite counts in this section are time-sensitive, because a peer is editing two of these files while the
review is open. Two readings, both real:

- The four files named below, run before the peer's edit landed: **38 passed, 198 assertions, 5.33s**.
  The same four files again after HEAD moved: **39 passed, 198 assertions, 5.95s**.
- `RunSkillRowLabelsTest.php` alone, after the peer's uncommitted test landed:
  **1 failed, 6 passed, 69 assertions, 8.28s**. The failure is F-10.

The gaps in B.2 and B.3 are gaps in what the green tests assert, not red tests. The one red test is
uncommitted work in flight, not a shipped defect.

### C. Data source and picker behaviour

#### C.1 How the picker list is built

`app/Http/Controllers/TrainingRunController.php:301`:

```php
'skills' => Skill::query()->availableOnGlobal()->orderBy('name')->get(['id', ...]),
```

`app/Models/Skill.php:80-85` defines the scope: `release_status = GlobalReleased` **and**
`name_is_client = true`. The docblock at `:72-75` claims this scope is the starting point for "the run
screen's skill select, FR-D-2's search, the CSV/JSON export, the `/api/v1` run resource". The last two
are not true today; see D-6 and F-3.

#### C.2 How rows are seeded on run creation

`TrainingRunController::store()` at `:140` calls `$this->prePopulateSkills($run)` at `:148`.
`prePopulateSkills()` at `:175-198`:

- builds `$exportIds` at `:185` from the character card's `skills_innate` and `skills_unique`, deduped;
- returns early when that list is empty at `:190`;
- at `:194` selects `Skill::query()->availableOnGlobal()->whereIn('export_id', $exportIds)` and writes
  `Suggested` pivot rows.

It runs **only inside `store()`**. Nothing backfills an existing run, and `create()`
(`TrainingRunController.php:63`) passes no skills, so the create screen has no skill control at all.

#### C.3 Storage is complete and the four-group picker is not

`KNOWN-ISSUES.md:1618-1623` is explicit: the storage `G-SK-13` needed is landed, and the
**four-group picker** ("her innate / her unique / her awakening / everything else") is **unbuilt**,
deliberately, because reusing `resources/js/trainee-combobox.ts` means generalising a module
hardwired to the trainee payload. The shipped storage is verified end to end:

| Layer | Citation |
|---|---|
| columns | `database/migrations/2026_09_30_073029_add_skill_lists_to_character_cards_table.php` |
| model | `app/Models/CharacterCard.php:35-36` (docblock), `:40` (fillable), `:66-67` (array casts) |
| parser | `app/Services/DataPipeline/Parsers/GametoraCharacterCardParser.php:90-93`, where `:90` records that `skills_unique` holds two ids on 22 of 268 records |
| store | `app/Actions/StoreCharacterCards.php:91-92` |
| consumer | `TrainingRunController.php:185-194` |

**The three `h3` headings in A.1 are a status grouping of a read-only list, not a picker grouping.**
They are not evidence that a four-group picker exists, and they are not the same feature.

#### C.4 Catalogue size, measured

Counted from the shipped source document (`database/seeders/data/skills.609afe88.json`, sha-256
prefix `609afe88`) and re-derived through the parser's own rule:

| Quantity | Count |
|---|---|
| records in the source document | 1910 |
| records the parser returns | 1910 |
| rows the parser marks Global | 623 |
| of those, rows with a client name | 623 |
| options in one shipped picker | 624 (623 plus `Choose a skill`) |
| status options in the same row | 3 |
| options in the picker per editable row | identical, every row |

Re-measured against a scratch database built by `migrate` plus `StoreSkills::handle()` over that same
document: `parsed=1910 stored=1910 available=623`. A second scratch database left from the earlier
import pass (`skills-c5.sqlite`) reports the same figure grouped as `GlobalReleased|1|623` and
`JapanOnly|0|1287`.

The second Global skill document in `.scratch-uma/` (`skills_f4a1.json`, sha prefix `f4a1e02d`, also
1910 records) yields **621** rows with a client name, and its Global set is a subset of the `609afe88`
set: union 623, intersection 621, only-in-`609afe88` 2, only-in-`f4a1e02d` 0. So no combination of the
two documents in this working tree produces more than 623.

The picker is the **same full list in every row**. It is not filtered to the card, and it is not
filtered by what is already on the run. The deck panel makes the opposite choice and says so at
`resources/views/components/deck-panel.blade.php:15`, where its options are the Global set
**concatenated with the cards this run already uses**.

#### C.5 How the save is written

`TrainingRunController::syncSkills()` at `:698`:

- `:701` resolves each row with `Skill::find((int) $entry['skill_id'])`. **No `availableOnGlobal()`
  scope.** Any existing skill id is accepted, including a JP-only or factory row.
- `TrainingRun::setSkillStatus()` at `app/Models/TrainingRun.php:243-245` writes through
  `syncWithoutDetaching()` at `:245`, against the relation at `:228` which carries
  `withPivot(['status', 'turn_acquired'])` at `:231` and `using(RunSkill::class)` at `:232`.
- The relation declares **no ordering** at `:228-232`, so any consumer other than the view gets rows
  in whatever order the join returns.

`app/Http/Requests/StoreRunSkillRequest.php`:

- `:31-40` `prepareForValidation()` filters the batch with
  `array_values(array_filter(...))` at `:40`, which is what discards an untouched spare row.
- `:54` validates `Rule::exists('skills', 'id')`. **No availability constraint.**
- `:56` `turn_acquired` is `nullable`, `integer`, `min:1`. Nothing ties it to a real turn row.

The no-detach rule is documented in three places, not just the request: the request docblock at
`:12-14`, the view comment at `show.blade.php:477`, and the test at `RunSkillRowLabelsTest.php:237`.

### D. Premise verdicts

| # | Brief's claim | Verdict | Evidence |
|---|---|---|---|
| D-1 | Row count is one per skill plus a spare | **CONFIRMED** | `show.blade.php:480-481`, `$rowTotal = max(1, $skillRows->count() + 1)` |
| D-2 | The form re-preselects current status and turn | **CONFIRMED** | `show.blade.php:511` and `:519`; pinned by `RunSkillRowLabelsTest.php:147` |
| D-3 | The four-group picker is unbuilt, storage landed | **CONFIRMED** | `KNOWN-ISSUES.md:1618-1623`; storage table in C.3 |
| D-4 | The picker lists the whole catalogue in every row | **CONFIRMED** | `TrainingRunController.php:301` has no per-run or per-card filter; re-measured at 624 options per row against the shipped catalogue. See C.4 and E |
| D-5 | `turn_acquired` is unlinked | **CONFIRMED** | `StoreRunSkillRequest.php:56` validates `integer, min:1` only; no relation on the pivot; `TurnEntryResource` has no skill field |
| D-6 | The CSV/JSON export loses or drifts skill state | **PARTIAL** | CSV drops skills entirely. JSON preserves them exactly. See F-3 for the real drift, which is in the other direction. |
| D-7 | No dedicated test pins the row controls | **REFUTED** | `RunSkillRowLabelsTest.php` has five tests over a DOM parser at `:32`, covering labels, row count, preselection, upsert, and no-detach |
| D-8 | The write path is blind to availability | **CONFIRMED** | `TrainingRunController.php:701` unscoped `Skill::find`; `StoreRunSkillRequest.php:54` unscoped `exists` |
| D-9 | The factory diverges from production match keys | **CONFIRMED, and worse than stated** | See F-4, measured |
| D-10 | The row count is a single Blade file, not a component | **CONFIRMED** | Zero component invocations in `:427-526` |
| D-11 | A rejected submit loses the typed turns | **CONFIRMED at HEAD, remediation in flight** | No `old()` in `:427-526` as committed. An uncommitted peer edit rehydrates all three controls at `:500-502`. See F-1 and F-10 |
| D-12 | Skill status writes are what the tests exercise | **CONFIRMED** | `TrainingRunTest.php:73-89`, `RunSkillRowLabelsTest.php:167-229` |
| D-13 | The API publishes the pivot shape | **CONFIRMED, untested** | `TrainingRunResource.php:33-38`; zero tests assert it |

### E. Measured browser behaviour

Measured with Playwright against a scratch database, real application, real routes, after importing the
full shipped catalogue. Server on `127.0.0.1:8127`, database at `.scratch-uma/skreview.sqlite` (inside
the repository's ignored scratch directory, never the shared file), 1910 skills stored and 623 of them
available, two runs created at 6 and 20 skills.

| Property | 1280x800 | 390x844 |
|---|---|---|
| 6-skill run: rows, skill options per row, options in the form | 7 rows, 624, 4389 | same |
| 20-skill run: rows, skill options per row, options in the form | 21 rows, 624, 13167 | same |
| Skill select height | 31px | 31px |
| Status select height | 31px | 31px |
| Turn input height | 30px | 30px |
| Submit button height | 32px | 32px |
| Controls in the 20-skill form with no accessible name | 0 of 63 | 0 of 63 |
| `h2` "Skills" occurrences on the page | 1 | 1 |
| Form action | `POST /training-runs/2/skills` | same |
| Horizontal overflow on the 20-skill page | no | no (`scrollWidth` 375 at 390) |

Render weight, 20-skill run at 1280x800:

| Quantity | Value |
|---|---|
| `<option>` elements in the skills form | 13167 |
| Rendered HTML for the page | 1835222 bytes serialised from the live DOM, 1870477 bytes on the wire |
| DOM nodes | 13699 |
| Server render, warm | 1.82s and 1.87s over two requests, after a 4.02s cold first hit |
| Server render, 6-skill run | 0.51s |

The first pass recorded 2324ms for the same page on a cold-ish server. The re-timed warm figures above
are the ones to quote, and the 6-skill row is new: 0.51s against 1.82s is the cost of fourteen extra
rows, which is the clearest statement of what the per-row catalogue copy buys nobody.

The 6-skill run is 4389 options, 638098 bytes, 4739 nodes. The cost is linear in row count and each row
carries a full catalogue copy: 21 rows at 624 options, then 21 rows at 3 status options.

**Design rule violated.** `docs/design-research/DESIGN.md:884` requires form controls at
**height 44**. All four measurements are below it at both viewports. `DESIGN.md:888` also requires
number inputs to get steppers; `show.blade.php:518` ships a bare `<input type="number" min="1">`
with no `step` attribute (confirmed in the browser: `step` is `null`) and no stepper control.

**Option ordering, verified.** 623 names ascend in byte order on `name`, so the `orderBy('name')` at
`TrainingRunController.php:301` does what it says. Three names lead with a character that is neither a
letter nor a digit (`#LookatCurren` first, then `∴win Q.E.D.` and `♡ 3D Nail Art` last), and digit-led
names (`1,500,000 CC`, `114th Time's the Charm`) sit at the head beside them. `α-star*` closes the Latin
run. That is byte order behaving correctly, and it reads oddly to a Trainer scrolling the top of the
list.

**Blank scenario.** With `scenario` empty, the page returns 200. `hasScenario()` is false,
`scenarioKey()` falls back, and the `h2`, all three `h3` groups and all seven rows render. The
section does not depend on scenario state. `app/Models/TrainingRun.php:302-306` confirms the premise
this rests on: `hasScenario()` uses `filled()`, not a null test, and its own comment at `:304-305`
records why.

#### A discarded measurement

A first pass compared raw `<option>` text and reported the list as **not** sorted. That was wrong,
and the reason is worth recording because it would have become a false finding. The option label is
decorated: `show.blade.php:502` appends ` · N SP ` to the name. The first disagreement the raw
comparison reported was between a decorated label ending in `·` and one that continued into a
different word, which is a difference in the decoration, not in the sort key. Re-measured against
the name alone, the list is sorted. The instrument was wrong, not the code. Reported here because a
review that quietly drops a failing measurement is indistinguishable from one that never ran it.

#### Measurement reconciliation: the two-row difference

The first pass reported **626** options per picker, **625** names in ascending order, **13209** options
in the 20-skill form and **4403** in the 6-skill form, at 1838028 and 639222 bytes. The re-measured pass
reports 624, 623, 13167 and 4389. The two passes are not two readings of one number: 13209 minus 13167
is 42, which is exactly 21 rows times 2, and 4403 minus 4389 is 14, which is 7 rows times 2. So the
first pass really did serve a picker with two more rows in it than the shipped catalogue holds.

That scratch database has since been deleted and cannot be inspected, and no document available here
explains the extra two rows. The `609afe88` body yields 623, the `f4a1e02d` body yields 621, and their
union is 623, so importing both in either order still cannot reach 625. The most likely explanation is
that the first pass served a database carrying two rows the shipped pipeline does not produce, which is
exactly the reproducibility gap KI-49 files against these untracked source bodies.

The figures carried above are the re-measured ones, because they trace to a database built by a command
line in this document from a file in this tree. **No finding in section F changes either way:** every
row shipping a copy of the whole catalogue is true at 623 or 625, and the arithmetic that makes F-5
worth reading is the per-row copy, not the last two digits of the list.


### F. Findings

**F-1. A rejected skills submit silently discards everything typed, at HEAD.** `show.blade.php:484-526`
as committed carries no `old()` and no `@error`. The deck panel, on the same screen, rehydrates from
`old()` at `deck-panel.blade.php:64` and documents at `:61-62` that it exists so "a rejected submission
does not wipe the six picks". The skills form drops the same data with no message and no recovery. A
Trainer who types twenty turns, submits, and hits one validation error loses all twenty. This is the
most user-damaging finding in the section and it is invisible from the source alone.

**This finding was being fixed while it was being written.** An uncommitted working-tree edit adds the
three dotted `old()` accessors at `show.blade.php:500-502` and three `@error` blocks at `:528`, `:540`
and `:550`, and the same edit adds the test at `RunSkillRowLabelsTest.php:167` that asks for exactly
this. The rehydration half is real: that test's value assertions at `:202-204` pass. Its message
assertion at `:214` does not. See F-10, and the fence note about acting on a moving tree.

**F-2. The write path accepts any skill id, and a green test proves it does.** `Skill::find` at
`TrainingRunController.php:701` and `Rule::exists` at `StoreRunSkillRequest.php:54` are both
unscoped, so the server accepts a JP-only or factory skill. Measured: a factory row has
`release_status = NULL` and `name_is_client = false` (not null — the migration default at
  `database/migrations/2026_09_29_021157_add_reference_fields_to_skills_table.php:11` sets
  `default(false)`, so SQLite returns the boolean, never null) and matches `availableOnGlobal()` zero times.
`TrainingRunTest.php:73-89` posts exactly such a row and passes. `Skill.php:72-75` claims the scope
guards this path. It does not, so availability is enforced only by which options the browser
happens to render, which is not a trust boundary.

**F-3. The export drift runs the opposite way from the brief.** The CSV drops skills entirely:
`TrainingRunController.php:849` builds an eleven-column turn header with no skill columns, and the
scratch measurement returned exactly that header and nothing else, 63 bytes, for a run with 20 skills.
That was always the design, and `:828-830` says so. The actual drift is in the **JSON**: `:838` loads
`['umamusume', 'turnEntries', 'skills']` but **not** `deckSlots`, while
`TrainingRunResource.php:43` emits `deck` under `whenLoaded('deckSlots')`. Measured over HTTP on the
scratch server, `GET /training-runs/2/export/json` returns 3985 bytes whose `data` object holds
`id, umamusume, scenario, status, notes, turns, skills` and **no `deck` key at all**, while its `skills`
array carries all 20 rows with `status` and `turnAcquired` intact. The resource behaved exactly as its
own comment at `:41-42` describes; the export controller is the defect. A run with a full support deck
exports JSON that does not contain the deck. The skills themselves survive; the deck does not.

The drift is invisible to the suite for a reason worth naming: the deck shape *is* pinned over HTTP, at
`tests/Feature/ApiV1SupportCardTest.php:138-144` and `:152`, but those assertions drive
`GET /api/v1/training-runs/{id}`, a path that loads `deckSlots`. Nothing drives
`GET /training-runs/{run}/export/json`, so the one endpoint that silently drops the key has no test on
it. The contract and the export disagree, and the test suite is aimed at the contract.

**F-4. The factory's `match_key` can describe a different string than its `name`.**
`database/factories/SkillFactory.php:23` computes `$name` from faker, and `:28` derives `match_key`
from that local variable. Overriding `name` at the call site does not change the key. Measured on
the scratch database: `Skill::factory()->create(['name' => 'Factory Probe'])` produced
`name = "Factory Probe"` and `match_key = "corruptimagnam"`, the slug of the faker words. The
production normalizer disagrees too, and in a different way:
`app/Services/DataPipeline/NameNormalizer.php:26` applies NFKD, `:34` strips combining marks, and
`:37` removes `FOLDED_CHARACTERS` from `:22`, which includes both middle dots, the hyphen, and both
space forms. The factory at `:28` does `Str::slug` and removes only the hyphen, so it folds neither
`・` nor `･` nor the ideographic space, and does not fold half-width to full-width katakana. Two
different derivations, and the factory's silently detaches from `name` on override.

**F-5. Every row ships a full catalogue copy, and the deck does not.** Measured in E: 13167 options
and 1.84 MB of serialised DOM for a 20-skill run, against 4389 options and 638 KB for a 6-skill run,
and 1.82s of server render against 0.51s. `deck-panel.blade.php:15` shows the cheap alternative already
in the repo: options scoped to the run's own cards. The skill picker has no such narrowing and no option
count, while the deck discloses its count in plain text at `:101`. Two forms on one screen, one
discloses its size and one does not.

**F-6. Two test names promise coverage their bodies do not deliver.**
`ApiV1Test.php:44` is named for turns and skills and asserts only turns. `TrainingRunTest.php:340-372`
is the brief's no-backfill proof and omits `character_card_id` from the very POST that is supposed to
carry the card. Both are green. A reviewer trusting the names would record two capabilities that no
assertion covers.

**F-7. The published pivot shape has no test.** `TrainingRunResource.php:33-38` is the API's
contract for skill state. No test in the suite asserts `data.skills`, `status`, or `turnAcquired`.
The section can change shape without a single test failing. The relation that feeds it declares no
ordering (`TrainingRun.php:228-232`), so even the array order is unspecified and nothing pins it.

**F-8. Three `h3` headings are not a picker.** The brief's "grouped" framing attaches to
`show.blade.php:429-433`, a read-only list grouped by acquisition status. The four-group picker
`G-SK-13` names is a different, unbuilt feature (`KNOWN-ISSUES.md:1618-1623`). Both facts are true;
merging them would make the unbuilt feature look shipped.

**F-9. KI-37's third measurement does not reproduce, and its anchors have rotted as it predicted.**
`KNOWN-ISSUES.md:1740` files "The run screen's form controls measure 31/30/40px against DESIGN.md
§6.14's 44", and its body at `:1742-1746` names `<select>` at `show.blade.php:447` = **31.0px**,
`<input type=number>` at `:460` = **30.0px**, and `button[type=submit]` ("Save skill status", `:462`) =
**40.0px**, against `DESIGN.md:849`.

Two of the three reproduce exactly. The select measures 31 and the number input measures 30 at both
viewports and on both run sizes. **"Save skill status" measures 32, not 40.** That is not the anchor
rotting under a padding change: `git show ff16101f:resources/views/runs/show.blade.php` puts the button
at line 462 with the class string `enamel rounded-full bg-chrome px-3 py-1.5 font-semibold
text-on-chrome`, which is character for character what `:557` carries today.

Where the 40 actually lives: this page currently holds six submit buttons measuring 40, 32, 40, 32, 32
and 36 in document order. The two at 40 are "Change scenario" and "Preview this turn". So the entry's
number is a real figure from this screen, attached to the wrong control, and its page-wide claim of
"11 submit buttons at 40px" no longer describes the page at all.

Every anchor in the entry has moved: `:447`, `:460` and `:462` are now `:518`, `:547` and `:557`, and
§6.14's height-44 line is now `DESIGN.md:884`, not `:849`. The entry anticipated exactly this failure
mode in its own KI-29 paragraph at `:1750-1755`, which is why it cites by number rather than by line;
the measurement is the part that did not survive.

**Consequence for whoever picks this up.** The violation stands, on three controls rather than four:
31, 31, 30 and 32 are all below 44. Re-measure before quoting 31/30/40, and if the fix is sized by
that triple the row grows 12px rather than 8px on the button, which changes the density argument
`:1762-1770` is making.

**F-10. The in-flight fix rehydrates the values and still loses the message.**
`RunSkillRowLabelsTest.php:167`, added uncommitted as this review ran, fails:

```
1 failed, 6 passed (69 assertions), 8.28s
  ➜ 214▕     expect(array_filter($errorText))->not->toBe([]);
      tests\Feature\RunSkillRowLabelsTest.php:214
```

Everything before `:214` passed, which pins the fault precisely. `:190` proves the error bag holds
`skills.1.turn_acquired`. `:202-204` prove the `old()` rehydration works for all three controls. What
fails is that no `p.text-risk` renders inside the skills form, so the Trainer sees a form that looks
saved.

The cause is on the surface in the same edit. The view's own comment at `show.blade.php:503-506` states
that "`@error` compiles its argument to a single-quoted PHP string, so a key written as
`skills.{$row}.turn_acquired` reaches the error bag as that literal and matches nothing", and builds
`$skillIdError`, `$statusError` and `$turnError` at `:507-509` for that reason. `:528` and `:540` pass
the finished variables. **`:550` passes the literal string the comment says cannot work**, and the turn
field is the one field the test asks about. Applying the rule the edit wrote for itself at `:550` is the
whole of the remaining fix. This review did not change it: the file is a peer's, uncommitted, and the
fence is read-only.

**F-11. KI-49's evidence does not reproduce, and this review's own reconciliation depends on it.**
`KNOWN-ISSUES.md:2294` files the untracked-source-body gap and states "Sizes on disk: 4,399,937,
2,068,129 and 2,791,919 bytes", and "`config/uma.php` names all three bodies through `seed_file` keys
(`config/uma.php:75`, `:113`, `:180` in the working copy)".

The finding is right and both of those lines are wrong.

- Untracked: confirmed independently. `git ls-files --error-unmatch` on
  `database/seeders/data/skills.609afe88.json` returns "did not match any file(s) known to git".
  Sweeping every real `seed_file` value in `config/uma.php`, exactly three are untracked, which is
  KI-49's count and exactly its three names: `skills.609afe88.json`,
  `gametora-characters.e9e9ee6d.json`, `characters.c6676539.json`. The other four bodies are tracked.
  `example.json` at `config/uma.php:47` is a docblock sample, not a pointer.
- Sizes: measured 2,502,242, 251,294 and 120,351 bytes against the entry's 4,399,937, 2,068,129 and
  2,791,919. No permutation of the two lists agrees, and no file of any of the three quoted sizes
  exists in `database/`, `storage/app/` or `.scratch-uma/`.
- Pointers: `:75` and `:113` both name `gametora-characters.e9e9ee6d.json`, so the list double-counts
  one body, and `characters.c6676539.json` is named at `:236`, which the entry does not cite.
- `git show HEAD:config/uma.php` has 0 `seed_file` matches, exactly as the entry says. That half
  reproduces.

**Why a review of the run screen bothers with a register entry.** Section E's reconciliation leans on
KI-49 to explain where the first pass's two extra catalogue rows came from. An entry whose three quoted
sizes are wrong is not safe to lean on for a different conclusion, so E now carries the attribution as
"a plausible root, filed by an entry whose own evidence line does not reproduce" rather than as an
explanation. The measurable part stands on its own: no skills body in this working tree yields more
than 623 picker rows.

**Owner.** Docs Writer, as a correction to KI-49's evidence paragraph, not as a reopening of its
verdict. Per this repository's convention the quoted sizes become an erratum beside the original
sentence rather than a silent edit of it.

### Working-tree drift during this review

The tree moved under the review three times. Recorded because two of the three changed what this
document is allowed to say.

| Event | Evidence | Effect on this document |
|---|---|---|
| HEAD moved from `adf6a6d` to `b1fd0f4`, then to `fea55d5` | `git log --oneline -6` now ends `fea55d5 docs(slice-6): file P-7…`, `6253a34 docs(issues): file KI-49…` | Docs only. No cited line in sections A, C or D moves. |
| A peer uncommitted edit landed in `resources/views/runs/show.blade.php` | `git status --porcelain` reports ` M`; `git diff --stat` reports 40 insertions, 4 deletions; the file went from 534 to 566 lines | A.3 re-anchored, D-11 re-worded, F-1 scoped to HEAD, F-10 added. |
| The same edit added a seventh test to `RunSkillRowLabelsTest.php` | ` M`, 57 insertions, 290 lines, new `it()` at `:167` | B.1 rebuilt with current anchors, B.4 now carries two suite readings, `:180` citation moved to `:237`. |

The brief warned that `git status` in this worktree is not a stable read and that a peer-edited file
must be re-read rather than assumed. That instruction is the reason F-1 is scoped to HEAD instead of
stated flat: the first read of `:427-526` was accurate when it was taken and became wrong inside the
same session. The re-read at `:427`, `:432`, `:458` and `:469` confirms the read-only summary block did
not move, so A.1 and A.2 needed no change.

Nothing in sections A, C or D was edited by this review. The files a peer is holding are named in the
do-not-touch list below.

### G. Next dispatch options

Each is a separate session with a stated fence. They are ordered so that the cheapest structural fix
comes before the expensive one.

**Option 1. Finish the sticky, honest edit that is already in the tree (smallest diff, highest user
value).** A peer session has the rehydration landed uncommitted at `show.blade.php:500-502` and has
written the test for it at `RunSkillRowLabelsTest.php:167`. Two things remain, and both are in that
same file and the two files beside it: pass `$turnError` to the directive at `:550` instead of the
literal key, which is what F-10 says is failing, and scope the write path at
`TrainingRunController.php:701` plus `StoreRunSkillRequest.php:54`. Fixes F-1, F-2 and F-10. No new
component, no new dependency, no migration. **Coordinate before starting: the view and that test file
are someone else's uncommitted work right now.**

**Option 2. Close the test-name gap.**
Repair `ApiV1Test.php:44` to assert the pivot, add the same-card case `TrainingRunTest.php:340-372`
describes, and make `SkillFactory` derive `match_key` from the final `name` through `NameNormalizer`
so a name override cannot desynchronise it. Three test-side files plus the factory. Fixes F-4 and
F-6 and starts F-7. No production behaviour changes, so it is safe to land first if the team prefers
green-only commits.

**Option 3. Narrow the picker without a combobox.**
Render the option list once per page as a `datalist` or narrow the query to the card's own
`skills_innate` and `skills_unique` plus the run's current skills, following the precedent already
at `deck-panel.blade.php:15`. This is the `G-SK-13` payload without the `trainee-combobox.ts`
refactor that `KNOWN-ISSUES.md:1620-1622` correctly identifies as belonging to another surface. Cuts
the 13167 options in E to a number a Trainer can scan. Decide explicitly whether this closes
`G-SK-13` or only halves it; the four groups are the unbuilt part and should not be claimed.

**Option 4. Fix the export contract, deliberately.**
Add `deckSlots` to the load at `TrainingRunController.php:838` so JSON stops dropping the deck, and
decide in writing whether the CSV gains skill columns or the round-trip loss is accepted and
documented. `G-SK-15` deliberately froze the JSON shape, so this needs an Architect decision, not a
Laravel Dev one. The header order at `:849` must not move; its own comment at `:843-845` records why.

**Not recommended as its own dispatch:** the four-group picker. `KNOWN-ISSUES.md:1620-1622` already
identifies why, and the prerequisite it needs is a `trainee-combobox.ts` refactor on a surface this
review did not examine.

### H. Lore and accessibility audit

#### H.1 Banned-pattern grep on all touched code paths

A case-insensitive grep for the Lore Guardian's banned vocabulary (`horse`, `sire`, `dam`, `mare`,
`foal`, `breeding`, `racehorse`, `stable` as a character container, and the `🏇` emoji) was run against
every file cited in this review.

| File | Match | Verdict |
|---|---|---|
| `resources/views/runs/show.blade.php:427-558` | none | PASS |
| `app/Http/Controllers/TrainingRunController.php` | `unpostable` at `:73` — false positive on `stable` substring; no actual hit | PASS |
| `app/Http/Controllers/Api/V1/TrainingRunController.php` | none | PASS |
| `app/Http/Requests/StoreRunSkillRequest.php` | none | PASS |
| `app/Models/Skill.php`, `app/Models/RunSkill.php`, `app/Models/TrainingRun.php` | none | PASS |
| `database/migrations/2026_09_29_021157_add_reference_fields_to_skills_table.php` | none | PASS |
| `database/factories/SkillFactory.php` | none | PASS |
| `app/Services/DataPipeline/Parsers/GametoraSkillsParser.php` | none | PASS |
| `app/Services/DataPipeline/NameNormalizer.php` | none | PASS |
| `resources/css/app.css:302` (enamel utility) | none | PASS |
| `database/seeders/data/skills.609afe88.json` | none | PASS |

The only surface hit was `TrainingRunController.php:73`, where `stable` appears only as a substring of
`unpostable` — the Lore Guardian's rule C-4 permits "stable as an adjective"; the instrument
over-reported and the Guardian discharges it. **No lore violations in the Skills section or its data
pipeline.**

#### H.2 Accessibility — label coverage

Every control in the edit form has an explicit `<label for>` pairing, measured at 1280x800 and 390x844
(see the table in E: `0 of 63` controls without an accessible name). `RunSkillRowLabelsTest.php:99`
pins this at the DOM level by asserting three distinct accessible names per row. This coverage is green
and passing.

#### H.3 Accessibility — touch-target height (KI-37, OPEN)

`docs/design-research/DESIGN.md:884` requires form controls at **height 44 px**. Measured:

| Control | Height | Meets §6.14 |
|---|---|---|
| Skill `<select>` | 31px | No |
| Status `<select>` | 31px | No |
| Turn `<input type=number>` | 30px | No |
| Submit button | 32px | No |

All four are below the 44px requirement at both viewports. The button height was 32px in both measurement
passes, not 40px as KI-37's body paragraphs claim — the 40px figure attaches to a different set of buttons
on the page ("Change scenario" and "Preview this turn"). KI-37's violation count (three, not four) is
closer to the truth, but the height gap remains at 12px on the button and 13-14px on the inputs. `step` is
absent on the number input, which also misses `DESIGN.md:888`'s requirement for stepper arrows. **This
finding is OPEN in `KNOWN-ISSUES.md` and was not remediated during the fence period.**

### I. Do-not-touch list for the next dispatch

Each line is a file another session is holding or a surface this review did not examine. Touching one
without coordination produces the same collision this document recorded in F-1 and F-10.

| Path | State observed | Why it is on the list |
|---|---|---|
| `resources/views/runs/show.blade.php` | ` M`, 40 insertions uncommitted | The peer's F-1 fix is in it right now. Option 1's remaining change is one line at `:550`. |
| `tests/Feature/RunSkillRowLabelsTest.php` | ` M`, 57 insertions uncommitted, one red test | Same edit. The red test at `:167` is that session's own work item. |
| `config/uma.php` | ` M`, and it is where every `seed_file` pointer lives | Peer-dirty before this review began. KI-49's fix options both rewrite it. |
| `phpunit.xml` | ` M`, holds the `:memory:` pin at `:41` | Peer-dirty. Changing it silently re-points every test run in the worktree. |
| `database/seeders/DatabaseSeeder.php`, `UmamusumeSeeder.php`, the four untracked `database/seeders/**` files | ` M` / `??` | Batch 2 extraction in flight, and three of the bodies are the untracked ones KI-49 names. |
| `docs/design-research/skill-facts-2026-10-01.md` | committed at `b1fd0f4`, 715 lines | Phase B1's fact set. Its own provenance warning covers the untracked body. |
| `docs/scenarios/03-trackblazer.md` | untouched here | Standing exclusion from the Screen D brief, still in force. |
| `docs/deprecated/**`, `docs/frontend-review/**`, the slice deliverables, the consolidation doc | untouched here | Fence exclusions, unchanged. |
| `database/database.sqlite` and its `-wal` / `-shm` sidecars | read-only, hash proved unchanged | Shared with every concurrent session, and the WAL state is exactly what KI-50's neighbour commits are filing hazards about. Use a scratch file. |
| `resources/js/trainee-combobox.ts` and `resources/views/runs/create.blade.php` | clean, not examined by this review | The four-group picker's prerequisite lives here. G-SK-13 work starts with a refactor of a single-instance module, which is why this review did not recommend it as a dispatch. |

### Drift and fence confirmation

- HEAD moved during the review, from `adf6a6d` to `b1fd0f4 docs(skills): extract the Global skill
  fact set for Phase B1`. That commit adds only 
  `docs/design-research/skill-facts-2026-10-01.md`, 715 insertions, no code. Every `file:line`
  above was read against the working tree and is unaffected. Three further doc-only commits landed on top
  before this section was finalised: `6253a34 docs(issues): file KI-49`, `9ef1b3a docs(slice-6): file
  P-7`, and `2d42f4b docs(consolidation): erratum on the CHECK scan claim`. A subsequent commit
  `4309619 docs(issues): file KI-50` and `5d0aae4 docs(consolidation): file the shared-database SHA
  hazard in §6` were present at delivery time. None of these touch code or file:line citations.
- Pre-existing peer changes were present before this review began and were not touched:
  `config/uma.php`, `phpunit.xml`, `database/seeders/DatabaseSeeder.php`,
  `database/seeders/UmamusumeSeeder.php`, and the untracked Batch 2 seeder files.
- Shared database `database/database.sqlite`: size 1310720 and mtime 2026-09-30T18:41:21.7903368Z
  before and after, unchanged. **The earlier hedged statement in this bullet is superseded by a
  measurement.** The first pass reported that a SHA-256 could not be read because another process held
  the file open. On the re-measured pass the content hash read cleanly on both sides of every browser
  and test operation, and the two reads are identical:
  `sha256(database/database.sqlite) = 550c6050bf84216df4d0474c46c354d4556c7f226be6c661c35cd5835ed9f7f3`,
  taken before the scratch import and again after the last HTTP request, with the sidecar
  `database/database.sqlite-wal` also unchanged at 168952 bytes and mtime 2026-10-01 03:03:21 during
  that window. The no-change claim now rests on a content hash, not on size and mtime.
- No code edits, no migrations, no commits, no seeders, no database writes to the shared database,
  no `paratest`, and no changes to `config/uma.php`, `phpunit.xml`, deprecated docs, the slice
  deliverables, or `docs/frontend-review/**`.
- **Where the scratch work actually lived, corrected.** The first pass described its database as
  "outside the repository". The re-measured pass deliberately used the repository's own ignored scratch
  directory, because `open_basedir` in this CLI build refuses an absolute PDO path outside the project
  (measured: `PDO::connect` on `D:/…/database/database.sqlite?mode=ro` returns
  "open_basedir prohibits opening"), so a path outside the tree cannot be opened by the app at all.
  The second scratch database is `.scratch-uma/skreview.sqlite`, created by
  `DB_DATABASE=.scratch-uma/skreview.sqlite php artisan migrate`, filled by `StoreSkills::handle()`
  over the shipped body (1910 stored, 623 available), and served on `127.0.0.1:8127` and `:8128`.
  Both ports were closed by PID after measurement (`taskkill //PID 22748`, then
  `curl` to the port returns 000). Nothing in `.scratch-uma/` is tracked or tracked-eligible;
  `git status` does not list it.
- **Test runs and their DB target.** `phpunit.xml:41` pins `DB_DATABASE=:memory:` in the working copy,
  so the four suite runs in B.4 could not have touched any file database. Confirmed by reading the
  pin, not assumed.

---

### Final report summary

| Metric | Value |
|---|---|
| Documents written | 1 |
| Files modified | 1 (this document, superseding the untracked 447-line draft) |
| Code files touched | 0 |
| Migrations added | 0 |
| Database writes to shared DB | 0 |
| Browser sessions used (scratch only) | Two passes: the first on port 8199 with a database since deleted, the second on ports 8127 and 8128 against `.scratch-uma/skreview.sqlite`, at 1280x800 and 390x844 |
| Test runs executed | Six. Four in the first pass, plus the four-file suite re-run and `RunSkillRowLabelsTest.php` alone in the re-measured pass |
| Sections delivered | 10 (Method, A–H) + Drift + this summary |
| Open issues filed | 0 (KI-37 pre-exists; KI-49 and KI-50 were filed by a concurrent session) |

#### Verdict at delivery

The Skills section of the run detail screen is **functionally correct for its stated scope** but has one
user-damaging defect (F-1: rejected submits silently discard typed data) for which an uncommitted fix was
in flight during review (F-10: the fix rehydrates values but fails to surface an error message). The
picker renders all 623 Global skills plus a placeholder in every row, producing 13,167 `<option>` elements
in a 20-skill run — a performance and scanability problem that has no user-facing mitigation today.

The Lore Guardian audit finds **no violations**. The accessibility audit finds **one open finding**
(form-control heights below DESIGN.md §6.14's 44px, tracked as KI-37).

No code changes were made. The fence held.


---

## skills-section-phase-b2-2026-10-01.md

## Skills section, Phase B2: design proposal for fact attachment, picker inventory, and control sizing

**Status: DRAFT. Not committed. Goes to the owner before any implementation slice opens.**

**Date:** 2026-10-01
**Role:** UI/UX agent. This is a design proposal, not an implementation.
**Dispatch plus five steers.** The original dispatch asked Q1, Q2, Q3. The first steer added the
skills-mechanics audit and asked Q4, Q5, Q6 (answered in §10, §9, §11). The second added four race-panel and
calendar findings as context for §4 only. The third added D-30, Screen D's separate job, and G-SK-11's
exclusion. The fourth added the fresh-clone empty state, the committed status of the stale copy, and the
naming collision. The fifth added the pivot's data ceiling, the correction-path requirement, the three
validation shapes, and copy ownership. All five are integrated; scope stayed inside the six questions.
§4.8 through §4.11 and §9 exist because of them.
**Fence held:** one file written, this one. No view, controller, model, migration, component, test,
config, or design-record file was edited. No commit, no push, no migration, no browser session.
**Verified against:** master `4e17997`. HEAD moved twice during this pass, from `80cb4dd` to `d6ed712` and
then to `4e17997`, and the second move changed what this document is allowed to say about the peer's work.
§2 and §7 are written against `4e17997`; §12 item P-13 records the move and what it falsified mid-pass.
Line citations describe the tree as read on 2026-10-01 and are paired with a heading or a name wherever
one exists, per `CONSTRAINTS.md` D-289.

---

### 1. Method note

Read in full:

- `docs/design-research/skills-section-review-2026-10-01.md` (706 lines). Its sections A, C, E, F, H and I
  are the evidence base for everything below. Its measurements are quoted, not re-run, and each figure
  inherited from it in this proposal names it as the source.
- `docs/design-research/skills-mechanics-audit-2026-10-01.md` (109 lines, untracked), added by the first
  steer. Its §3 mechanic matrix and §5 Findings A, B, C were each checked against the source before being
  used; where my reading differs from the audit's, §12 item P-10 says so.
- `docs/design-research/CONSTRAINTS.md` D-30 (the permitted render surface, §5 "Data-model binding") and
  the `KNOWN-ISSUES.md` status block that routes the D-30 gap to a future register pass.
- `database/seeders/SkillSeeder.php` (whole file) and `git show HEAD:database/seeders/DatabaseSeeder.php`,
  which reaches `SkillSeeder` at its line 15. This is the basis of the picker's empty state in §4.9.
- `docs/design-research/skill-facts-2026-10-01.md` (715 lines): the Provenance warning, Method, the
  coverage table with its canaries, the token and code lists, and the worked example at export id
  `201241`. The 623-row table itself was sampled, not read line by line.
- `docs/design-research/DESIGN.md` §6.11 (Skill row), §6.14 (Form field), §6.16 (Advisory and
  recommendation row), §6.18 (Empty, loading, error), §6.5 (Stat band), §8.4 (Skill search), §10
  (Accessibility).
- `KNOWN-ISSUES.md`: the KI-33 heading and its closure, the "One open end, and it is not this entry's"
  paragraph inside KI-33, the KI-36 closure, the whole of KI-37, and the KI-29, KI-38, KI-39 headings.
- `docs/design-research/SKILLS-GAPS.md`: G-SK-19, G-SK-20, G-SK-21, and the "Still open after Screen D
  landed the same day" list.
- Code, read not changed: `resources/views/runs/show.blade.php:427-558`, the whole of
  `resources/views/components/deck-panel.blade.php`, `resources/views/skills/index.blade.php`,
  `resources/views/runs/create.blade.php:9-75`, `resources/js/trainee-combobox.ts` (the grouping and
  payload regions), `app/Models/Skill.php`, `app/Models/CharacterCard.php`, both Gametora skill and card
  parsers, the two skills migrations, and the card skill-list migration.
- `git diff` on the four peer-dirty files this proposal depends on. That diff is load-bearing, so it is
  described in §2 rather than assumed.

Measured here, with the command stated so a second pass can re-run it:

- `grep -rln 'h-11' resources/views/ resources/css/`: nine files, listed in §5.2.
  `grep -c 'h-11' resources/views/components/guided-step.blade.php` returned **0**. This bears on a
  citation error recorded in §12.
- `sha256sum database/database.sqlite` plus `stat -c '%s %y'` on it and its `-wal` sidecar, before and
  after, in §13.
- `grep -n 'awakening' ` over `app/` and `resources/`: one hit, a comment at
  `resources/views/catalog/show.blade.php:215`. `GametoraCharacterCardParser.php:92-93` keeps
  `skills_innate` and `skills_unique` and nothing else.
- `git show HEAD:resources/views/catalog/show.blade.php | grep -n 'Skill lists are not shown'`: hit at
  `:226`. The stale sentence is on master, not only in the working tree.
- `git log --format='%h %ad' -n1` on two commits: `555b0cbd` (the commit that wrote that sentence) is
  `2026-09-30 03:44`; `dd90330` (the storage that falsified it) is `2026-09-30 16:09`, and
  `git merge-base --is-ancestor dd90330 HEAD` returns true while the same test with `555b0cbd` as the
  descendant returns false. **The sentence was true when written and aged twelve and a half hours.**
  That changes what the replacement copy has to do, per §9.
- `git ls-files database/seeders/`: `SkillSeeder.php` and `DatabaseSeeder.php` tracked;
  `ReadsCommittedSource.php`, `SourceDocumentSeeder.php`, `UmamusumeRosterSeeder.php` and the three data
  bodies untracked.

Provenance discipline for the second pass, since this proposal will be re-derived:

- **Corrected in place 2026-10-01, before any owner read.** The follow-up dispatch found that §9.3 and §10
  carried KI-35's stale measurement ("2 rows and all ten columns NULL"). A read-only PDO query returns **67
  `umamusume` rows with all ten `aptitude_*` columns non-null on all 67**, and the deeper correction is that
  section 2 is not unbuilt at all: `resources/views/components/aptitude-grid.blade.php` renders the ten
  letters and `catalog/partials/form-detail.blade.php:56` invokes it, landed by `555b0cb` on 2026-09-30
  03:44. §9.3 row 2, §9.5's second bullet, §10.1's L3 row and §10.3's layer map now carry the corrected
  state, and §9.5's D-30 ask is re-stated as catch-up on shipped code rather than as prospective permission.
  The recommendation in Q5 does not change: section 3 is still the one section to build, and it is now
  clearly the only one. The KI-35 erratum that records this landed in the same follow-up dispatch.

- Every number attributed to another document names that document and its section in the same sentence.
  The browser figures are the review's §E, at its own re-measured pass, and none was re-run here.
- Nothing here was measured in a browser. No server, no scratch database, no test run. Where a claim about
  behaviour is derived rather than measured, the word "derived" appears with its citations, and §4.9 is
  the one place that matters.
- **Naming collision checked; all skills citations resolve to `app/Models/Skill.php`,
  `app/Actions/StoreSkills.php`, `app/Services/DataPipeline/Parsers/GametoraSkillsParser.php`, the
  `skills` table, or the register documents.** Nothing in this proposal cites `app/Services/SkillRegistry.php`,
  `SkillMatcher.php` or `SkillExecutor.php`, which are the dev-tooling agent-automation subsystem and read
  `.agents/skills.json`, not the game catalogue. Verified by grep over this file: zero hits for those three
  names.

Parallel findings, named not analysed: the race panel and calendar surfaces carry findings filed
separately (the three-field picker against a four-field document, the third dead cell on
`race_catalog_slots.is_manual`, the circles guard reachable from the picker, the missing race-entry update
and delete routes, the dead-method sweep, and G-SK-11's scenario-exclusive surfaces). **They inform §4
only.** Nothing in §5, §9, §10 or §11 depends on them, and this proposal files none of them and proposes no
fix to any race surface.

Assumed, and flagged as assumption:

- The review's browser figures were not re-measured. Re-running them needs a scratch database and a server,
  and the review already carries both passes with a reconciliation. They are quoted with their source.
- Where the working tree and `HEAD` disagree, this proposal describes the **working tree**, because that is
  what the next slice inherits, and says so at each point.

---

### 2. Current state summary

The Skills section is two surfaces under one `h2`: a read-only list of the run's skills grouped by
acquisition status, and an edit repeater of three controls per row plus one spare
(`resources/views/runs/show.blade.php:427-458` and `:484-558`). The list is where a Trainer re-reads a
decision; the repeater is where a Trainer makes one. Neither renders a description, an activation
condition, or a style label today: the list prints name, `✦ Unique`, `N SP`, and `turn N`
(`:440-453`), and the picker's option label appends the SP cost to the name (`:525`).

Every picker row ships the whole Global catalogue: 624 options per row, 623 skills plus the
`Choose a skill` placeholder, which is 13,167 `<option>` elements, 1.84 MB of serialised DOM and 1.82s of
warm server render on a 20-skill run, against 4,389 options and 0.51s on a 6-skill run
(review §C.4 and §E). The deck panel beside it scopes its options (`deck-panel.blade.php:15`) and
discloses the resulting count in plain text (`:101`). The skills form does neither.

**The peer's in-flight fix has landed, while this proposal was being written.** It arrived as `4e17997`
("fix(runs): keep typed skill edits on reject, and refuse ineligible skills on write (F-1, F-2)", five
files, 171 insertions) at 13:17. Its content is what an earlier read of the working tree described: F-1's
rehydration is in (`show.blade.php:500-502`), F-10's remaining line is **fixed** (`:550` now passes the
`$turnError` variable, not the literal key), and the write-path scoping the review filed as F-2 is landed on
both halves (`StoreRunSkillRequest.php` now carries `Rule::exists(...)->where(release_status,
name_is_client)`, and `syncSkills` reads `Skill::availableOnGlobal()->find(...)`), with a data-driven
refusal test in `tests/Feature/TrainingRunTest.php`. Nothing in this proposal needs those lines, but the
review's Option 1 is therefore **closed**, and §8's ranking reflects that. What remains open from the review
is Option 2 (test names, the untested pivot shape) and Option 4 (the export contract), not Option 1.

Of the seven facts the dispatch names as "the fact set", **four are stored and three are not**. `Skill`
holds `name`, `name_ja`, `match_key`, `sp_cost`, `type`, `is_unique`, `export_id`, `rarity`,
`release_status`, `name_is_client` and four provenance columns (migrations `2026_09_26_162816` and
`2026_09_29_021157`; `app/Models/Skill.php:47-61`). There is no column for the client description, the
activation predicate, or the style label, and the fact document says so of itself: "No schema change, no
migration, no column. This is a document." That is the constraint that shapes Q1, and it is not a
rendering problem the renderer can solve.

---

### 3. Q1. How Batch 2's facts attach to a skill row

#### 3.0 The finding that comes before the design

Two of the three un-stored facts are **already ruled off the display path**, and a proposal that ignored
the ruling would be re-litigating it.

G-SK-20 (`docs/design-research/SKILLS-GAPS.md`, the entry headed "G-SK-20") measures `desc_en` failing the
terminology table 189 times and `endesc` failing it 72 times, concludes that neither field is `[Global]`
prose, and states the rule: **no description column may be added until a client-string source for
descriptions exists, an in-client capture, not a re-read of the export.** `resources/views/skills/index.blade.php:9-13`
implements the ban and cites it. So "client description" is not an attachable fact in Phase B2. It is a
blocked one, and the design below does not reach for it.

The same entry pairs it with G-SK-19 on the icon. Nothing here proposes either.

That leaves two facts the export carries, the document publishes, and no ruling bars:

- **The activation predicate.** Not client prose. It is engine DSL, emitted verbatim by the fact document's
  own rule 2: atoms other than `running_style` are printed as stored, because no glossary exists and a
  paraphrase would be an invented fact. This is source data with a provenance chain, not a claim about
  meaning, so it clears D-20 where the description does not.
- **Style eligibility.** Decoded from `running_style==N` against
  `docs/UMAMUSUME_REFERENCE.md:229-234`, which publishes the four client labels. This is the one decode the
  fact document performs, and it is the one the design may render as a word.

`type` is a third, with a warning attached: the stored column is **this tool's derivation**, one of three
words, refusing any skill that carries two kinds of effect (`GametoraSkillsParser.php:45-46, 102`, and the
footnote at `skills/index.blade.php:159-163` which says so on screen). The export's `type` field is a
different object entirely: an array of gate keys whose meanings are recorded as UNVERIFIED in the fact
document. Two things named `type` must not both reach one row.

#### 3.1 Where facts appear

**On the read-only list, at `show.blade.php:427-458`. Not in the edit repeater.**

Three reasons, in order of weight:

1. The owner's ruling is "record what the Trainer chose, with facts attached". A record is the read-only
   surface. The repeater is a form, and a form's job is the next keystroke.
2. The dispatch's own DOM constraint decides it mechanically. Facts cost bytes. In the repeater they cost
   bytes multiplied by the row count, which is the exact arithmetic that produced the 13,167 options in
   §2. On the list they are paid once per skill the run actually holds.
3. Facts change the decision only at the moment of choosing, and the moment of choosing already has a
   surface built for it: Screen D, reached by the `Search the skill catalog` link at `:469`. Adding a fact
   panel to the picker row would build a second, worse Screen D inside a `<select>`, which cannot hold one
   anyway.

The practical consequence for a Trainer: the run page tells you what you took and why it fires; the
catalog page tells you what is available. Neither duplicates the other, which is D-64's argument
(`skills/index.blade.php:5-7`) applied here rather than to markup.

#### 3.2 What the fact block looks like

One row per skill, in the existing order, extended. The shape is §6.11's Skill row subtracted to what the
data holds, because §6.11 already specifies this object's anatomy (name, description ending in a
parenthesised tag, badge, cost) and `skills/index.blade.php:114` calls its own row "§6.11's row, reduced to
what D-30 permits and what the data holds". The run list and Screen D should render the same reduced row,
from one place.

**Proposal: a single `x-skill-row` Blade component**, used by the run screen's read-only list and by
`skills/index.blade.php`'s `<li>`, with a `:facts` boolean prop that is true on the run screen and false on
Screen D until Screen D's own fact decision is made. One component, not two, because the review's §A.4
finding that the section invokes zero components is the reason the two surfaces have already drifted in
markup: the list marks Unique as a bare text span (`show.blade.php:446`) and Screen D marks it as a pill
with a `title` (`skills/index.blade.php:128-133`). Same state, two renderings.

The row, top line, in reading order:

```
{name}                                    font-semibold, text-ink-strong
  [✦ Unique]                              pill, only when is_unique
  [style label]                           only when style-gated, e.g. "Late Surger"
  [derived type word]                     only when the parser derived one
  · N SP    · N/A SP    · turn N          right-aligned, text-ink-muted, text-xs
```

Concretely, for export `202451 Top Gear` (from the fact document's own table row):

```
Top Gear  ✦ Unique  Late Surger                    · 180 SP · turn 24
▸ When it fires
```

and for `200283 Wallflower`:

```
Wallflower                                            · 50 SP
```

Sizing and role notes for the implementer, so nothing here needs re-deciding:

- Name span: `font-semibold text-ink-strong`, `text-sm` inherited from the list. Unchanged from
  `show.blade.php:440`.
- Unique mark: reuse Screen D's pill verbatim, including its `title` string
  (`skills/index.blade.php:128-133`). The mark and the word both carry it, which is D-12's requirement that
  a state never live in a glyph alone, and §6.11's sparkle is the client's own mark. It is not ornament and
  must not be copied to any other state.
- Style label: plain `text-ink-muted` word, **no pill, no colour**. It is a qualification of the row, not a
  state. The four permitted strings are exactly the client labels at `UMAMUSUME_REFERENCE.md:231-234`:
  Front Runner, Pace Chaser, Late Surger, End Closer. The literal export renderings (Runner, Leader,
  Betweener, Chaser) are barred by the same citation and must not appear.
- Derived type word: render the stored `type` column only, never the export's gate-key array, and only
  beside the footnote that already exists for it. If the row is in a context too small to carry that
  footnote, omit the word rather than show it unsubstantiated.
- SP and turn: unchanged from `:448-453`.
- Disclosure summary line: `▸ When it fires`, `text-xs text-ink-muted`, present only when the row has a
  predicate to show. The glyph is `▸`, rotated 90 degrees when open, matching the run screen's existing
  `<details>` at `show.blade.php:406-407`. It is not an em dash: `skills/index.blade.php:13` records the
  standing bar on the em dash as a disclosure glyph (R-02, D-79, KI-7).

Density: one line per skill plus, at most, one summary line. A 20-skill run grows by 20 short lines in the
default state, not by 20 panels.

#### 3.3 Interaction: hover, focus, disclosure

**Hover: nothing. Focus: nothing. Disclosure: yes, and only for the predicate.**

- Hover-only content is unreachable on touch and unreliable with a keyboard, and §10's keyboard commitment
  is a repo-wide one. A fact a screen reader cannot reach is a fact that is not in the product.
- Focus rings are already specified (§6.14's 2px green plus glow, §10's 3px ring). Attaching a hover card to
  focus would mean a popup triggered by navigation, which moves content under a stationary cursor and
  breaks the guided flow's roving focus.
- Name-only content inside a `title` attribute is the same failure in a different costume. Note precisely
  what this bars, because the repo already uses `title` legitimately: Screen D's `title` at
  `skills/index.blade.php:131` and `:147` qualifies a value that is **also visible**. That pattern is fine
  and is reused. What is barred is any fact whose only rendering is a `title`.
- The predicate is the one fact whose honest rendering is long. Verbatim DSL for a two-group skill is two
  lines of `cond:` / `pre:` / `effects:` / `base_time:` text. Inlining that would bury the run. So it goes
  behind `<details>`, which is the app's only disclosure mechanism (`show.blade.php:406`) and needs no new
  JavaScript.

**The disclosure body, in full:**

```html
<details>
  <summary>▸ When it fires</summary>
  <p class="font-mono text-xs text-ink-muted">
    condition: running_style==3&amp;is_last_straight==1&amp;order&gt;=2&amp;distance_diff_top&lt;=10<br>
    precondition: (none)<br>
    effects: type 27 value 4500<br>
    base time: 24000
  </p>
</details>
```

Rules the implementer must not vary:

1. The DSL is printed **as stored**, escaped, in `font-mono`. No atom is translated. The only substitution
   permitted is `running_style==N` to its client label, and that substitution appears in the row's style
   slot, not inside the monospace block, so the block always stays a faithful copy of the source.
2. Multiple condition groups render as separate blocks, labelled `Group 1`, `Group 2`, in source order. The
   fact document's `++` separator is a text-table device, not a UI grammar.
3. A `precondition` of `(none)` prints as the word `none`, not as blank. Blank and none are different
   statements and the source distinguishes them.
4. The block carries no verdict. No "good for", no "pair with", no ranking, no colour, no icon. This is why
   the row must not adopt §6.16's advisory shape: the speech bubble and the `HINT` badge are the design
   record's own device for *advice*, and §6.16's rule ("a suggestion must carry its arithmetic") is a
   licence the predicate does not need and must not borrow. The client's coaching voice is not the voice
   for engine data.
5. The export's numeric effect codes and `base_time` are printed as numbers with no gloss, and the
   `type` token array does not appear at all in this block, for the reason in §3.0.

#### 3.4 Degradation for a skill with a missing fact

The dispatch's premise here is wrong and correcting it changes the answer, so read §12 item P-1 before
implementing this section. Per the fact document's coverage table, on the 623 Global rows:
`condition_groups` is non-empty on **623 of 623** (the empty case is measured at 0), and `endesc`,
`jpdesc`, `desc_en` and `name_en` are present on **623 of 623**. There is no skill in the Global set that
lacks a predicate. There is no Global skill that lacks `endesc`, and `endesc` is barred anyway.

The absences that are real, and their handling:

| Missing | How many | Rendering |
|---|---|---|
| `cost` / `sp_cost` | 147 of 623 (every rarity 3 to 6 row) | `· N/A SP` with a `title` naming the kind of absence. Screen D already does exactly this at `skills/index.blade.php:140-148` and cites D-220 for it. Copy that, do not reinvent. |
| Style gate (`running_style` absent from the predicate) | 500 of 623 | Slot omitted entirely. "Not style gated" is a true statement about 500 rows and printing it 500 times is noise, not information. The absence is the normal case, so it renders as absence. |
| Derived `type` word (two effect kinds, or a negative value) | measured, not asserted: whatever the parser returns null for | Slot omitted. The `Unspecified` filter on Screen D exists for these rows; the run row simply carries no word. |
| `char` list (which card's unique this is) | 185 of 623 | Not rendered. `is_unique` is the boolean the schema holds; the card list is not stored and §6.11's unique treatment needs only the boolean. |
| Predicate for a row whose facts were never imported | 0 today | The disclosure line is not rendered. The row does not show an empty disclosure, because an empty disclosure is a control that does nothing, which is the non-functional-control failure this repository already bars. |

The governing sentence, which the row must be able to say when asked: the absence is stated as the kind of
absence it is. Under a kept heading, "not yet recorded" is permitted; a silent blank is not (root
`DESIGN.md` §4.2, "Catalog detail", as rewritten 2026-09-29, and D-220). Note the file qualifier, and see
§12 item P-10: this repository has **two** files named `DESIGN.md`, and §6.11, §6.14, §6.16, §6.18, §8.4
and §10 live in `docs/design-research/DESIGN.md` while the trainee page's section list and its absence rule
live in the root one.

#### 3.5 Accessibility story

- **Reading order** is the visual order, because nothing is absolutely positioned and nothing is
  `aria-hidden`. Name, unique mark, style, type, cost, turn, disclosure.
- The unique pill keeps the visible word `Unique` alongside the glyph, so the mark is not the only carrier
  (D-12, and `skills/index.blade.php:128-133`). Its `title` adds provenance and removes nothing.
- The disclosure is a native `<details>`/`<summary>`. It is in the tab order, announced as a group
  disclosure, and needs no ARIA of any kind. Twenty rows cost twenty additional tab stops on a run screen
  that already carries six forms; that is the honest cost, and it is smaller than the cost of content
  nobody can reach.
- The predicate block is `font-mono text-xs`. It is read as text, not as a table, because a one-row table
  announced as a table is a worse reader experience than a paragraph.
- No `aria-live`, no `role="status"` on any of it: this is static reference data, and announcing it would
  compete with the gain bubbles, which §10 does commit to `aria-live="polite"`.
- Label coverage is untouched. The change is in the read-only list, which holds no form controls. The
  review's measured `0 of 63` unnamed controls in the form cannot regress from this design, and the guard
  against regressing it is `tests/Feature/RunSkillRowLabelsTest.php`'s first test, which asserts three
  distinct accessible names per row at the DOM level. That test must stay green.
- Contrast: every slot uses `ink-strong`, `ink` or `ink-muted` on `raised` or `panel`, all of which are
  already contract-passing pairs in §3.4. **No new colour is introduced by this design**, which is
  deliberate: G-52 colour containment, and §7.1's dose caps. A style label in green would read as a state,
  and §3.6 already ruled that stats are not colour-coded.

#### 3.6 The default state, and why it is the right default

A Trainer who loads a run and touches nothing sees: which skills she has, split into Suggested, Acquired,
Skipped; for each one, the client name, whether it is a unique, which running style it is gated to when it
is gated at all, what it cost in SP, and on which turn it was taken. Nothing about when it fires is on
screen until asked.

That is the right default because of what the page is for. The run screen answers "what did I do"; the
predicate answers "why does the engine do it", and a Trainer reaches for that only when a result surprised
them. The facts that belong in the default state are the ones that disambiguate a name: two `[Global]`
skills share the name `Indomitable` (G-SK-21), and `✦ Unique` plus the SP cost is what tells those two rows
apart without opening anything. The predicate does not disambiguate, so it is not default.

The counter-proposal, all predicates open by default, was rejected on arithmetic: at four lines each, a
20-skill run grows the section by roughly 80 lines and pushes the deck and the turn log off the first
screen, which is the density failure KI-37's own argument is about.

---

### 4. Q2. The picker's inventory problem

#### 4.1 The three named directions, assessed

The dispatch offers two directions from the review's Option 3 plus a third. Assessed against the tree:

**`datalist` / one shared option list: rejected.** A `<datalist>` cannot carry the split the current
contract depends on. `show.blade.php:525` sets `option[value]` to the skill id and the label text to
`name · N SP`; a `datalist` submits its `value` and, on WebKit, displays `value` rather than `label`, so
the decoration a Trainer reads is the decoration that goes into the box. It also cannot carry the four
groups, cannot show a cost to a screen reader in a single fixed order, and offers no grouping affordance at all
(ARIA has no group semantics for `datalist`). It fixes bytes by breaking the control.

**Scope the options (the `deck-panel.blade.php:15` precedent): viable, and insufficient as stated.** The
deck panel's set is "Global releases plus any card this run already uses", which is a complete answer for
a deck because a deck is exactly six slots drawn from one pool. A skill build is not. Scoping the skill
picker to the card's `skills_innate` and `skills_unique` plus the run's current rows offers somewhere
between two and eight skills. The learnable ones are where the SP is spent: 496 of the 623 Global rows are
rarity 1 or 2 (`344 + 152`), the fact document's `cost` coverage is 476 of 623, and those are not card
skills. A picker scoped to her card would make the majority of a Trainer's actual choices
unofferable. So scoping is not a smaller version of the right answer; it is a different feature, and it
breaks the thing the review's F-2 fix just protected, which is that the accepted set and the offered set
are the same set.

**Rebuild as a combobox: recommended.** With one correction to the review's framing, and one correction to
KI-33's.

#### 4.2 Recommendation: the combobox, built on the create-screen pattern that already exists

The review declined the four-group picker as a dispatch because "the prerequisite it needs is a
`trainee-combobox.ts` refactor on a surface this review did not examine" (§G, and the §I row). This
proposal examined it. The prerequisite is smaller than it looks, because the hard parts are already solved
once in that file and the solution is documented in comments rather than in a generalised API:

- **Payload shape.** `create.blade.php:75` ships `<script type="application/json" id="trainee-roster">@json($rosterJson)</script>`
  and `trainee-combobox.ts:322-360` parses it once, with a shape check that is not just syntax. Options are
  built from the filtered subset, never from the full set. **This is what actually kills the inventory
  problem: the initial DOM holds zero `<option>` elements regardless of row count or catalogue size.** Not
  fewer. Zero. The 623 names travel once, as JSON, in one script tag.
- **The no-script path.** `create.blade.php:9-29` is the pattern's other half and it is the migration-cost
  answer the dispatch asks for: a native `<select>` is rendered, the combobox input and its hidden partner
  are shipped `disabled` in the markup, and the script flips the pair in one block. Reversed, a scriptless
  POST loses the selection. That reasoning transfers directly.
- **Grouping.** `trainee-combobox.ts:261-266` records why a group header is `role="presentation"` and not
  `role="group"`: a group must own its options to name them, so a sibling header produces an unnamed group
  and a screen reader hears a skill title with no band attached. The band name therefore goes **into each
  option's accessible label**, exactly as the trainee's name goes into each option's `aria-label` at `:305`.
  This is the four-group picker's central accessibility question, and it has one correct answer already in
  the repo. A second implementation would either find that answer again or get it wrong.
- **id collision.** `:282-285` records that `aria-activedescendant` resolves through `getElementById`, so a
  placeholder id reused across rows makes the cursor name the first of them. With 21 comboboxes on one
  page this is not hypothetical; it is the same bug at 21 times the surface.

**What the refactor actually is.** Not a rewrite. The module's coupling is to the *row shape*, not to
trainees. The minimum generalisation: lift the filtering, listbox building, cursor, and ARIA plumbing behind
a factory that takes (a) a payload accessor, (b) a comparator, (c) a band function, (d) a label formatter,
(e) an id prefix. The trainee call site supplies its current closures; the skill call site supplies four
bands and a name-only comparator. That is a sized, single-surface refactor, and it is the prerequisite the
review named but did not measure.

**Why not ship the scoping option first instead.** Because it is not a smaller version of the same
destination; it is a different destination. It changes which skills a Trainer can name at all, it moves
`availableOnGlobal()` into a second narrower place that the request rule must then be re-scoped to match
(the peer just wrote both halves of that predicate, and the comment in `StoreRunSkillRequest.php` says the
repetition is on purpose so the two cannot drift), and it does not shrink the DOM by enough to be worth two
slice's coordination cost. If the byte pressure alone must be relieved before the combobox lands, the
cheaper move is §4.5's stopgap, not §4.1's scoping.

#### 4.3 The picker's rendered shape

One row of the repeater, after the change:

```
Skill                       Acquisition status        Turn acquired
[ Top Gear            ▾ ]   [ Acquired            ▾ ]  [ 24        ]
```

and the open listbox, four bands, headers as presentation:

```
Her unique skills
  Top Gear · Late Surger · 180 SP
Her innate skills
  Raised Ark · 100 SP
Already on this run
  Golden DNA · turn 11
Everything else  (598 skills · showing matches, type at least 2 characters)
  1,500,000 CC · 40 SP
  ...
```

Design rules:

- Bands render in the order above. A band with nothing in it is **not rendered**, so a run for a card with
  no unique skills shows three headers rather than four empty ones. The empty-spare-row case (§4.6) shows
  one band.
- **A skill appears in exactly one band, and the card bands win.** This is a correction to the first draft
  of this section, caught by the reachability sweep the second steer asked for. `prePopulateSkills()`
  (`TrainingRunController.php:175-197`, per the audit's §2 item 4) writes the union of the card's innate and
  unique lists as `Suggested` pivot rows at `store()`. So on a freshly created run every one of her skills is
  simultaneously a card-band member and "already on this run". Two band headers listing the same rows is not
  grouping, it is duplication, and a Trainer reading it learns that the bands mean nothing. Precedence:
  `Her unique` > `Her innate` > `Already on this run` > `Everything else`. A skill the Trainer added by hand
  that is also one of hers stays in her band, which is the more informative statement of the two.
- **When the run names no card, the two card bands do not exist.** `training_runs.character_card_id` is a
  nullable foreign key (`CONSTRAINTS.md` D-30's own paragraph records it landing as nullable on 2026-09-29),
  so this is a state a Trainer can create, not a hypothetical. The picker then renders two bands,
  `Already on this run` and `Everything else`, and says why in one line:
  `This run does not name a costume form, so her own skills cannot be grouped.` Naming a card is the only
  writer for those two bands, and that is the correction path in §4.10.
- The band count is disclosed in the band header, in the deck panel's words (`deck-panel.blade.php:101`),
  because F-5's finding is that one form on this screen discloses its size and the other does not. Both
  must.
- Each option's accessible label is the band plus the visible text: `Late Surger unique skill: Top Gear,
  180 SP`. A sighted reader gets the band from the header; a screen-reader reader gets it from the option,
  per `:263-266`.
- The option label carries the SP cost because the current one does (`:525`), for the reason its own comment
  gives: the choice being made is a spending choice. It does not carry the predicate. Predicates live on
  the read-only row (§3.1), and a picker that expands into §6.11's full row per option is how a combobox
  becomes a second catalogue.

#### 4.4 The 623-name byte-order quirk

The review verified it (§E, "Option ordering, verified"): 623 names ascend in byte order, three lead with a
non-alphanumeric character (`#LookatCurren` first, `∴win Q.E.D.` and `♡ 3D Nail Art` last), digit-led names
sit at the head beside them, and `α-star*` closes the Latin run. It notes this reads oddly at the top.

**Decision: leave the byte order alone in `Everything else`.** Reasons, and the honest cost:

- Byte order is what `TrainingRunController.php:301`'s `orderBy('name')` does, it is deterministic, it
  matches the PHP `sortBy('name')` at `:480` that orders the read-only rows, and it matches whatever order
  Screen D paginates in. A locale collation (`SQLite` `COLLATE NOCASE`, or `Collator` in PHP) would produce
  a **third** order, different at every extreme, and would need a deliberate decision about where `♡` sorts
  that no source in this repository informs.
- The quirk's real cost is scroll-to-top-of-list, and the combobox removes that cost directly: an empty
  input shows the first N results in band order, and a two-character query reduces the set before the
  Trainer ever reaches the extremes. The fix is the filter, not the collation.
- In `Her unique skills`, `Her innate skills` and `Already on this run` the bands are two to eight rows and
  byte order is not a scanability problem.

If the owner wants the extremes reordered anyway, that is a separate ruling with a named collator and a
statement of what happens to `#`, `∴`, `♡`, `,` and the ideographic space, and it should apply to Screen D
in the same breath. This proposal does not decide it.

#### 4.5 The form contract, and its migration cost

**`name=` structure does not change.** The submitted keys stay `skills[N][skill_id]`,
`skills[N][status]`, `skills[N][turn_acquired]`. The combobox's visible input is `disabled` in the markup
(it is not a form control and never was a value carrier), and the hidden input that carries
`skills[N][skill_id]` is what the script enables and writes, exactly as the trainee pair works.

- `StoreRunSkillRequest::prepareForValidation()`'s `array_filter` at `:31-40` keeps working unchanged: an
  untouched spare row still arrives as an empty `skill_id` and is still discarded.
- The peer's scoped `Rule::exists(...)` keeps working unchanged, because the ids written are the same ids
  the `<select>` used to write.
- `syncSkills` needs no edit.
- **Migration cost: zero on the server, one file on the client, plus the generalisation in §4.2.**

The one contract question that is genuinely open and is **not** decided here: whether the four bands should
become four *submitted* groups, so the store can distinguish a Trainer's pick from her card's suggestion.
That is a schema and PRD question (FR-C, and `prePopulateSkills` writing `Suggested`), not a rendering one.
It stays in §6.

Stopgap, if byte pressure cannot wait for the refactor: add the deck panel's disclosed count line under the
repeater (one `<p>`, one file, no behaviour change), and narrow the *displayed* option label. This does not
fix F-5 and must not be described as if it did. It is listed because §4.2's refactor has a dependency, and
the owner may want the honesty line on screen before the mechanism lands.

#### 4.6 The empty spare row

Must stay renderable and submittable. It is what the repeater exists for.

- No-script path: the `<select>` with its `Choose a skill` empty option, unchanged, and the spare row is
  offered like any other. `RunSkillRowLabelsTest.php`'s row-count and spare-row tests keep passing
  untouched.
- Script path: an empty combobox input shows the placeholder text, the listbox opens on the default band,
  and typing narrows. Submitting with nothing chosen sends an empty `skill_id`, which `array_filter`
  discards. Same outcome as today.
- The status select on a spare row still has no authored default (review §A.3, the last bullet). That is a
  one-line fix in the peer's file, not a design decision, and §6 leaves it with them.

#### 4.7 Which reading of G-SK-13 this closes, and which it does not

Named precisely, because the review's Option 3 asks for exactly this and the register has already been
over-claimed once in this area (F-8: three `h3` headings are not a picker).

- **Closes:** the inventory problem behind G-SK-13's own diagnosis (the list is too long to scan in a
  native select), and the four-group *picker* in the shape KI-33's residue describes, for the three bands
  whose data exists.
- **Does not close, and must not be claimed as closed:** the fourth band. KI-33's residue names the groups
  as "her innate / her unique / her awakening / everything else". `GametoraCharacterCardParser.php:92-93`
  stores `skills_innate` and `skills_unique` and nothing else. The word `awakening` appears once in all of
  `app/` and `resources/`, in a comment at `resources/views/catalog/show.blade.php:215`. The
  mechanics audit of the same date (`docs/design-research/skills-mechanics-audit-2026-10-01.md` §3, the
  Awakening row, and §6-C for the event array) records that the source card document does carry
  `skills_awakening` / `skills_awakening_en` and that they are **dropped at parse**, deliberately and
  unfiled. So: three of four bands are buildable on stored data; the fourth has a source that is
  discarded, which is a storage decision owned by Data Engineer and Architect, not a picker layout.
- **Design answer that does not need the data (per the owner's standing framing):** with the fourth band
  absent, the picker renders three bands and says which one is missing, in one line under the listbox:
  `Awakening skills are not stored yet, so this picker cannot group them.` Absence stated as absence,
  under a kept heading, rather than a four-group control that delivers three and implies nothing.

**Event skills are out of the group list, by ruling, and the design must not grow them.** The card document
also carries `skills_event`, and `GametoraCharacterCardParser.php:92-93` drops it alongside
`skills_awakening`: no column, no read path, no UI, and no register entry names the storage question (audit
§5 Finding C). The audit's recommendation, accepted, is to rule event out of scope rather than build a
fifth band, because widening a picker that has not shipped its first four is not the next move. So the
bands in §4.3 are **four at most and three today**, and they are closed: no fifth group appears in the
markup, in the payload, or in the copy. If event skills ever become storable, that is a new dispatch with
its own band, not an extension of this one.

#### 4.8 D-30: the two fields the picker reads are not on the permitted render list

`CONSTRAINTS.md` D-30 ("Render only what exists", inside §5 "Data-model binding") enumerates the permitted
surface per table. Its `Skill` entry is `name, name_ja, sp_cost, type, is_unique`. Its `CharacterCard`
entry is `card_id, umamusume_id, title, rarity, global_release_date, is_debut_form, unconfirmed` plus the
inline provenance set. **Neither `skills_innate` nor `skills_unique` appears**, even though both columns have
existed since `dd90330`.

The gap is known and routed, not overlooked: the `KNOWN-ISSUES.md` status block for the KI-33 / KI-36 pass
says the two columns "are not yet in ADR-0008, `ARCHITECTURE.md` §3, the ESSENTIALS digest or D-30, because
this session was barred from those files", and calls the doc gap "the next register pass's business, not a
reason to hold the schema." `dd90330`'s own commit message concedes the same four documents still describe
twelve fillable columns.

The answer this proposal takes is the **second option with the first option's ask attached**, because the two
halves of the design sit on different sides of the line:

- **Grouping is not rendering a field.** The picker reads the two lists to decide *which band an option
  falls into*. The option's visible text is `name` and `sp_cost`, both on D-30's list. No shipped string
  anywhere in §4.3's design names a column: the headers say "Her unique skills" and "Her innate skills",
  which are Trainer-facing category words the client itself uses, not `skills_unique`. On this reading the
  picker's grouping is inside D-30 today and needs no amendment to be built.
- **The absence line does name a field, so it is the exception, and it is the part that needs the ask.**
  "Awakening skills are not stored yet" makes a claim about the schema in Trainer-facing copy. That is the
  sentence D-30's discipline is actually about, and it is the one this proposal should not ship on the
  strength of an inference. Two ways out, and the owner picks: amend D-30 to name `skills_innate`,
  `skills_unique` and the grouping use they are permitted for (the amendment text is one clause, and
  ADR-0012's Decision 1 row already records the fourteen-column count, so the amendment is a catch-up
  rather than a ruling); **or** drop the awakening line and let the absence be silent, which is the failure
  KI-35's Class B paragraph says silence produces, because silence on a page that groups three bands reads
  as "she has no awakening skills".

This proposal recommends the amendment, and states plainly that it is a **register pass and the owner's pen**,
not this dispatch's, and not a blocker on the picker's first three bands. Do not silently render a field the
constraint list omits: that is the class the session has filed twice this week, and §4.8 exists to keep this
design from adding a third instance of its own kind.

#### 4.9 The empty catalogue is the fresh-clone default, and the picker needs a stated state

The dispatches assumed a populated picker. The review measured 624 options per row, and it could only do so
after **importing the shipped body into a scratch database itself** (review §E: "after importing the full
shipped catalogue", built by `migrate` plus `StoreSkills::handle()`). That import path is not on a fresh
clone. Derived, with its citations:

1. `git show HEAD:database/seeders/DatabaseSeeder.php` calls `SkillSeeder::class` at its line 15, so
   `php artisan db:seed` runs the tracked seeder.
2. `SkillSeeder.php:60-68` writes nine rows and sets only `match_key`, `sp_cost` and `is_unique`. It sets no
   `release_status` and no `name_is_client`; the docblock at `:34-38` says the omission is deliberate, and
   `database/migrations/2026_09_29_021157_add_reference_fields_to_skills_table.php:53-54` gives those two
   columns `nullable` and `default(false)`.
3. `Skill::scopeAvailableOnGlobal()` requires `release_status = 'GlobalReleased'` **and**
   `name_is_client = true` (review §C.1).

So **no tracked writer in this tree produces an offerable skill row.** The nine seeded rows exist in the
table and are invisible to the picker, and the bodies that would make 623 rows offerable are untracked
(`database/seeders/data/skills.609afe88.json`, plus `ReadsCommittedSource.php` and
`SourceDocumentSeeder.php`, and the `config/uma.php` `seed_file` pointers that wire them, which are
themselves uncommitted peer work). The empty picker is the ordinary first state of a new install, not an
edge case. **This is the register's own finding, not one this proposal adds:** `d6ed712` filed KI-51, whose
heading is that the seeder reading the untracked bodies is itself untracked, which is the same gap from the
pipeline side. §4.9 contributes only the UI consequence: what the Trainer sees, and the copy that names the
command. Naming the command is not a substitute for KI-51, and the empty state should not be read as closing
it.

The design, then, must state it, and Screen D already owns the pattern to follow:
`skills/index.blade.php:86-92` renders the empty-catalogue panel and names both commands
(`uma:fetch gametora-skills`, and `uma:reparse gametora-skills` for the KI-27 snapshot short-circuit that
lies about a database it never wrote to). The picker's version is shorter and points at that rather than
duplicating the caveat:

```
No skills are available to choose yet. The catalogue fills with
`php artisan uma:fetch gametora-skills`. Search the skill catalog
to see whether that command has run.
```

Rules: the placeholder option stays so the form still renders and still submits; the control is not hidden
and not disabled, because a disabled picker gives the Trainer no way to learn the command; and the
combobox's empty listbox is where this copy lives on the script path, so an empty popup never appears
blank. On the no-script path the same sentence sits under the `<select>`, which means it must be written
once, in the Blade, not in the TypeScript. The review recorded no empty-catalogue state for the picker at
all, which is the gap this section closes.

#### 4.10 Four checks the picker shape has to pass, and its answers

The second, third, fourth and fifth steers each raised a constraint class by name. Answered per class,
against the design in §4.3 through §4.9, not in the abstract.

**Option set versus write path: what makes them agree, and which validation shape.** Today the offered set
comes from `Skill::availableOnGlobal()` at `TrainingRunController.php:301`. The accepted set comes from a
*duplicated spelling* of the same predicate: `StoreRunSkillRequest.php` as committed at `4e17997` inlines
`where(release_status, GlobalReleased)->where(name_is_client, true)` rather than calling the scope, and its
own comment says the repetition is deliberate because `Rule::exists` cannot call a model scope and an
extracted constraint "would be one more place for the two to drift apart." **That is coordination by
convention, and the comment is the evidence.** The proposal replaces the convention with a mechanism, in
two parts:

1. One named predicate. Either a `Skill::scopeOfferable()` that the request's query callback calls, or the
   scope itself inside the existing callback, since the callback receives an Eloquent builder and
   `->where(fn ($q) => $q->availableOnGlobal())` is expressible. That makes "everything offered is writable"
   true by construction rather than by two people editing two files in step.
2. A test that pins it: render the picker, collect the option ids, assert their set equals the set the
   request accepts, one row each. A drift between the two sides then fails a test instead of producing a
   crash.

**Validation shape: silent accept and persist, with validation error as the backstop.** The picker's option
set is derived from the write path's own predicate, so everything it offers persists, and
`syncWithoutDetaching` writes it (`TrainingRun.php:243-251`, per the audit's §2 item 5). Anything arriving
that the picker did not offer gets the first shape, a validation error, which is what `4e17997`'s
`Rule::exists()->where()` produces and what its new refusal test asserts
(`assertSessionHasErrors('skills.0.skill_id')`). That is recoverable, it is the only one of the three shapes
a Trainer can act on, and it is what keeps this surface out of the circles-guard class: there is no throw
anywhere in the skills write path, and this design adds none. Silent accept and drop is the shape this design
must never take, and it does not: no picker value is discarded, and §6 leaves the four-bands-as-submitted-
groups question explicitly unbuilt rather than accepted-and-dropped.

**Every rendered state has a named writer.** Enumerated, because the sweep asked for exactly this:

| Rendered state | Writer | Reachable? |
|---|---|---|
| `Her unique skills` band | `StoreCharacterCards.php:91-92` via `GametoraCharacterCardParser.php:93` | Yes. `dd90330`. |
| `Her innate skills` band | same, parser `:92` | Yes. |
| `Already on this run` band | `prePopulateSkills()` and `syncSkills` through `setSkillStatus` | Yes. |
| `Everything else` band | `Skill::availableOnGlobal()` | Only after an import; §4.9 covers every other case. |
| `✦ Unique` on a row | `GametoraSkillsParser.php:187`, the class-code rule | Yes, and it is the marker's known limit (G-SK-21, and the `title` string Screen D carries for it). |
| `N SP` / `N/A SP` | parser `:188`, null where the source states no cost | Yes, both branches. |
| `turn N` | `run_skills.turn_acquired`, written by `syncSkills` | Yes. |
| Style label, predicate block | **no writer exists** | **Not reachable today.** This is the storage prerequisite in §6 item 1, and §3.4's degradation rules are what render until it lands. |

Nothing in the picker renders a source-of-choice marker, and it must not. `run_skills` carries `status` and
`turn_acquired` and nothing else, so it cannot record how a skill arrived; four of the six acquisition
routes in `SKILLS-GAPS.md` §1 are unrepresentable in that schema. The card bands are computed at render time
from `TrainingRun::characterCard` and the two stored lists. They are a view over data the schema holds, not a
state the pivot pretends to remember, and that distinction is what keeps this design from becoming the
fourth dead cell. **Q1 inherits the same ceiling and stays inside it:** nothing in §3.2 or §3.3 says or
implies how a skill came to be on the run. It prints `turn N` because the pivot holds that integer, and the
pivot's `turn_acquired` is validated `min:1` with no foreign key to `turn_entries` (review §D-5,
`StoreRunSkillRequest.php`'s own rule), so `turn 24` is a Trainer's statement, not a claim that turn 24
exists. No "unlocked mid-run" and no "won in Race 3" appears anywhere in this proposal.

**Correction path, and the one thing a Trainer cannot fix.** `syncWithoutDetaching` upserts, so a wrong
status or turn is corrected by submitting the same row again, and the bands recompute from the card. The
genuine limit is **removal**: `syncSkills` never detaches, documented in three places (the request docblock,
the view comment at `show.blade.php:477`, and the test's count-still-3 assertion). A skill added to a run by
mistake stays on it. That is deliberate, and it is this surface's own instance of the "user cannot correct"
class, so the picker says it rather than letting a Trainer discover it: one line under the save button,
`Adding a skill here cannot be undone from this page; the run keeps every skill it has been given.` Naming
it costs one sentence and is the difference between breaking the pattern and reproducing it silently.

**Copy ownership, and the rule that keeps it true.** Every string this design introduces, with its file and
its maintenance mechanism:

| Copy | Lives in | Kept true by |
|---|---|---|
| Band headers | the picker partial in `resources/views/` | Static category words. Cannot drift, because they name no data. |
| Band option count | same | Derived from the collection at render time. **Cannot drift**: it is not authored. |
| Awakening absence line | same | Falsifiable by a schema change, so its removal trigger is named: when `skills_awakening` gains a column and a writer, this line becomes a band. |
| "cannot be undone" line | same | The no-detach rule is pinned by an existing test, so the sentence is contradicted by a failing test rather than by a reader. |
| Empty-catalogue line | same | Names two commands registered in `config/uma.php`. Drift is possible if a command is renamed, which is why it mirrors Screen D's existing line rather than authoring a new one. |
| `When it fires` summary | `x-skill-row` | Names no skill and no field. Cannot drift. |

The governing rule this table exists to enforce: **no authored string in this design names a skill.** The
fifth steer's live example is the failure that produces. The epithet reward at
`config/scenarios.php:270`, `'Mile Straightaways hint +1'`, renders verbatim at
`epithet-checklist.blade.php:49` for a skill name no catalogue row carries, since the real client rows are
`Mile Straightaways ◎` (export 201031) and `Mile Straightaways ○` (201032). A copy string that names a skill
which does not exist is the smallest form of the drift class, and a picker would be its next host if band
copy were written by hand. It is not. Every skill name in the picker comes from a `Skill` row.

#### 4.11 Does this shape propagate to the other pickers?

Asked directly by the second steer. Answer: **the combobox does not propagate; the disclosure and the
predicate rule do.**

- **Deck picker (`deck-panel.blade.php`): no.** Six fixed slots over a set already scoped to Global plus the
  run's own cards, so the option count is small and the count disclosure at `:101` already solves the
  scanability problem a combobox exists to solve. Converting it would cost the no-script path's clarity on a
  surface where the native select is the better control, for no gain. The deck panel is the precedent this
  proposal *cites*, not the one it *replaces*.
- **Race picker, race calendar, trainee combobox: no shape change proposed here.** Those surfaces carry
  findings filed separately and sit outside this fence. What transfers is the two-line constraint in §4.10: a
  rendered state needs a named writer, and an option set needs to share a predicate with the write path. That
  is stated as a constraint rather than a migration plan, because the drift class is repo-wide and this
  dispatch is not the place that gets to sequence work on surfaces it was told not to touch.
- **Future pickers: yes, those two.** Any picker offering from a catalogue discloses how many it offers and
  where the rest lives, and any picker whose options are a subset of a table derives its accepted set from
  the same predicate the validator uses. Both are cheap, and neither waits on the combobox mechanism.

---

### 5. Q3. KI-37, the sizing decision

#### 5.1 What is actually established

The spec, quoted from `docs/design-research/DESIGN.md` §6.14 (Form field): input `raised` fill, 1px
`--color-rule`, radius 8, **height 44**. Same section, last bullet: number inputs get steppers, matching
§6.11's cost stepper, "so the tool's two numeric idioms are one idiom". §10 (Accessibility) independently:
"Target size: 44px minimum on anything clickable."

The measurement, from the review's §E and §H.3: skill select **31**, status select **31**, turn input
**30**, submit button **32**, at both 1280x800 and 390x844, on both the 6-skill and the 20-skill run. The
turn input has no `step` attribute at all and no stepper, missing §6.14's second requirement.

The register: KI-37's heading says "31/30/40px"; its body says the select is 31.0, the number input 30.0
and the submit button **40.0**, and claims the page holds 12 selects at 31, 20 number inputs at 30 and 11
submit buttons at 40. Two of the three figures reproduce. The button is 32, not 40, and the review's F-9
found the 40s belong to different controls ("Change scenario", "Preview this turn"). The page-wide "11
submit buttons at 40px" claim does not describe the page either.

**Correction to this dispatch's phrasing, recorded rather than inherited:** §12 item P-4. The violation is
**four controls at three distinct heights**, all below 44. "Three controls" is what KI-37's symptom
enumerates and it is wrong; the review's own §H.3 table lists four rows. The erratum should say four.

#### 5.2 The mechanism, and it is not contested

`5ff7aca` moved Screen D's identical `px-2 py-1` controls from 30/31/16px to 44/44/24 with no token change,
no layout change and no new utility. The repo's idiom for the value is `h-11`, which is 2.75rem = 44px
exactly. Nine files already use it: `epithet-checklist`, `grade-point-meter`, `race-calendar`,
`race-panel`, `shop-panel`, `spirit-burst-roster`, `team-race-panel`, `team-rank-gauge`,
`skills/index`. The run screen's skills editor, deck panel, guided turn block and race form carry none.

**`h-11` on the two selects, the number input, and the submit button. Not 43, not `min-h-10`, not
`py-2.5`.** The dispatch's instruction not to propose a partial fix is sound and the spec gives no room:
§6.14 states a height, not a floor.

#### 5.3 The density question, and the recommendation

The two options as posed:

**(A) Accept the density change on the run screen.** Every form control on `runs/show` goes to 44.
Vertical growth: the skills editor's 21 rows gain 14px each, so roughly 294px, about one third of a phone
screen. The guided-turn block and the race form grow on the same arithmetic.

**(B) Scope the fix to the sections where the spec is load-bearing.** Touch the skills editor only, or the
controls a Trainer must hit with a thumb, and leave the rest.

**Recommendation: (A), with one scoping refinement that removes most of B's reason to exist.**

Why not (B):

- §6.14 and §10 contain no section exemption. A partial fix creates a third state on one screen: controls at
  44 and controls at 30, both described by the same spec clause, which is precisely the two-renderings-of-
  one-state failure §3.2 flags for the Unique mark.
- The precedent is repo-wide and already split across two surfaces: KI-29 owns `/umamusume`'s catalog index
  and KI-37 owns the run screen, deliberately kept apart so one cannot close the other by proximity
  (KI-37's own "Why this is a separate entry" paragraph). Scoping *within* a surface would need a third
  ruling that neither entry anticipates.
- The load-bearing test does not select for sections. §10 says 44 minimum on *anything clickable*. Every
  one of the four measured controls is clickable, on a screen the mobile-first replan already names as a
  supported surface. There is no honest line inside the page where the rule stops applying.

The refinement that answers the real worry. The dispatch's concern, and KI-37's, is that 30 to 44 grows
three blocks at once. It grows them **vertically only**. The run screen already scrolls two of its regions
independently, and the race calendar's and turn log's wrappers are focusable regions precisely so a
keyboard user can scroll them (`show.blade.php`, and `tests/Feature/TurnLogScrollRegionTest.php`).
Vertical growth is what a scroll region is for. What a 44px row cannot do is drop a column, and nothing
here drops one, so the replan's refusal (`docs/design-research/replan-mobile-first.md` §1, 768px as the
supported minimum, and `CONSTRAINTS.md:171` refusing mobile compromise bought with desktop density) is not
engaged by this change at all. KI-37 already makes this argument in its "What the fix has to say"
paragraph; the recommendation is to land the fix, not to keep debating it.

Two things that ride with it, because §6.14 requires them and they are in the same four lines:

1. `step="1"` on the turn input, and the stepper. §6.14's last bullet ties the tool's two numeric idioms to
   §6.11's cost stepper, and the browser confirmed `step` is `null`. A native `type="number"` shows steppers
   by default when `step` is present and the field is not styled away; if the styling suppresses them, the
   fix is the explicit stepper control, not the omission.
2. `focus-visible` treatment on the two selects, copied from `skills/index.blade.php:31` and `:40`, which
   already carry `focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green`.
   Screen D's controls got both the height and the ring in `5ff7aca`; the run screen's need the height and
   should take the ring in the same breath, since §10 requires an always-visible focus ring and the run
   screen's selects currently have none.

What is **not** proposed: shrinking anything else to pay for it. The stat band, the column count, and the
race calendar are out of scope by KI-37's own sentence ("It does not belong with dropping columns or
shrinking the stat band"), and §3.2's new disclosure rows in Q1 are on the read-only list, which is not a
form control and is not governed by §6.14.

Sequencing: this lands **after** the peer's in-flight edit to `show.blade.php` commits, or in coordination
with it. Both touch lines inside `:484-558`, and the sizing change is a class-string edit on the same three
controls the peer just added `@error` blocks beneath.

---

### 6. What this proposal does not decide

Deliberately left to a follow-up slice or another role's pen:

1. **Storage for the predicate and the style label.** Two columns at minimum (a condition-group JSON and a
   style code), a parser change, an ADR, and a PRD amendment: FR-D-1's permitted surface is name, name_ja,
   match key, sp_cost, type, `is_unique` plus ADR-0011's rarity and provenance and FR-D-3's availability
   (G-SK-19's own enumeration). D-30 has to move first. Owner: Architect with Data Engineer. **Q1's design
   is implementable against a `null` for both columns** (the degradation rules in §3.4 say what renders), so
   the design does not block on this and the data does not block on the design.
2. **The client description.** G-SK-20's precondition, an in-client capture, is not satisfied by anything in
   this tree and this proposal does not attempt it.
3. **The awakening band's storage.** The source key exists and is dropped at parse; whether to keep it is a
   parser and PRD call. §4.7 gives the UI answer for both outcomes.
4. **Whether this closes G-SK-13**, and what the register says when it does. Owner's pen, per the standing
   rule that rulings and closures are the owner's to write.
5. **Four bands as four submitted groups** (§4.5). Schema and semantics, not layout.
6. **Byte-order collation policy** (§4.4), if any. Applies to Screen D equally.
7. **KI-37's erratum text**, and KI-37's re-anchored citations: its body's `show.blade.php:447`, `:460`,
   `:462` and `DESIGN.md:849` are all stale, and the review's F-9 already gives the current values.
8. **The spare row's status default** (§4.6), a one-line change inside the peer's file.
9. **The `x-skill-row` component's Screen D adoption date.** Q1 proposes the component with the fact block
   off on Screen D; whether Screen D switches its `<li>` to the component in the same slice is a scoping
   call for whoever lands it.
10. **`hint_level`** (G-SK-3, G-SK-4, conflict row 16), the **Japanese search key** (G-SK-22), and the
    **icon** (G-SK-19). All three are on SKILLS-GAPS' "still open after Screen D" list and none is
    reachable from this dispatch.
11. Whether the derived `type` word and the export's token array should ever coexist on one surface (§3.0).
    This proposal keeps them apart; it does not decide whether a future glossary closes the gap.
12. **The D-30 amendment itself**, in either form §4.8 and §9.5 ask for: one clause naming
    `skills_innate` and `skills_unique` with their grouping use, and a second decision on whether the ten
    `aptitude_*` columns join `Umamusume`'s permitted list. Register pass and owner's pen. The picker's
    first three bands do not wait on it; the awakening absence line and the trainee page's section 2 do.
13. **Whether the trainee page ships section 2 (Aptitudes) in the same slice as section 3 (Skills)**, given
    that section 2's first impression on an un-imported database is ten absences. §9.3 says build section 3
    now and put section 2 beside it, not before it; which of the two a slice actually carries is a scoping
    call for the owner.
14. **Section 6, "Her runs", and therefore the trainee page's primary action.** It needs a per-trainee run
    list that does not exist, so KI-35's "no primary action" finding stays open after this proposal lands.
    Building it is a route and a query, not a layout decision.
15. **Approval of the three Trainer-facing strings this proposal drafts and does not place:** the
    awakening-absence line (§4.7), the "cannot be undone" removal limit (§4.10), and the empty-catalogue
    line (§4.9). Each has a named drift mechanism in §4.10's copy table and none is owner-approved copy.
16. Which of §4.10's two mechanisms for the shared predicate is taken: a named `scopeOfferable()`, or the
    existing scope called inside the request's query callback. The proposal recommends the second as the
    smaller diff and does not decide it.

---

### 7. Do-not-touch list

Carried forward from the review's §I, then updated with what `git status --porcelain` shows today. The
review's list is now stale in three places and those are marked.

| Path | State at `80cb4dd` | Why |
|---|---|---|
| `resources/views/runs/show.blade.php`, `tests/Feature/RunSkillRowLabelsTest.php`, `app/Http/Controllers/TrainingRunController.php`, `app/Http/Requests/StoreRunSkillRequest.php`, `tests/Feature/TrainingRunTest.php` | **all clean at `4e17997`** | The peer's F-1, F-2 and F-10 work committed during this pass. Q1, Q2, Q3 and Q5 all land inside these files, and the earlier "coordinate before starting" warning on five paths is now a normal branch workflow rather than a collision risk. |
| `config/uma.php` | ` M` | Peer-dirty before the review. Every `seed_file` pointer lives here. |
| `phpunit.xml` | ` M` | Peer-dirty. `:41` pins `DB_DATABASE=:memory:`; changing it silently re-points every run in the worktree. |
| `database/seeders/DatabaseSeeder.php`, `UmamusumeSeeder.php`, and the four untracked `database/seeders/**` files | ` M` / `??` | Batch 2 in flight; three bodies are KI-49's untracked ones. |
| `docs/design-research/skill-facts-2026-10-01.md` | clean, committed | Phase B1's fact set. Read-only input here. |
| `KNOWN-ISSUES.md` | ` M` | Contended register. Nothing here proposes an entry; §6 item 7 defers even the erratum wording. |
| `docs/design-research/skills-section-review-2026-10-01.md` | **`??`, untracked** | **The review's own §Final report says "Files modified 1"; it is not committed.** Anyone citing it cites a file that a clean checkout does not have. |
| `docs/design-research/SKILLS-GAPS.md` | clean | G-SK-19 and G-SK-20 govern Q1. Read, do not amend. |
| `docs/design-research/DESIGN.md` | clean | Read, not changed, per this dispatch's fence. |
| `resources/js/trainee-combobox.ts`, `resources/views/runs/create.blade.php` | clean | Q2's prerequisite and its pattern. Untouched by this proposal; the refactor is a future slice. |
| `docs/scenarios/03-trackblazer.md` | clean | Standing exclusion. |
| `docs/deprecated/**`, `docs/frontend-review/**`, slice deliverables, the consolidation doc | excluded | Fence exclusions, unchanged. |
| `database/database.sqlite` + `-wal` / `-shm` | read-only, hash unchanged | Shared with every session. `.bak` is the 4 KiB WAL decoy, not a database. |
| `resources/views/components/deck-panel.blade.php` | clean | Q2's precedent. Not a Q1 or Q3 target. |
| `docs/design-research/skills-mechanics-audit-2026-10-01.md` | **`??`, untracked** | Named nowhere in the original dispatch and read because the first steer added it. Its awakening and event findings are cited in §4.7 and §9 and were checked against the parser before use. Its Finding A's third claim is qualified in §9.2 and P-11. |
| `resources/views/catalog/show.blade.php` | clean at HEAD | Q5's target and the home of the stale sentence at `:225-229`. Read only. The fence names it explicitly, and §9.4's copy is a draft awaiting the owner. |
| `resources/views/components/guided-step.blade.php`, `race-panel.blade.php`, `epithet-checklist.blade.php`, `race-calendar.blade.php`, `team-rank-gauge.blade.php`, `spirit-burst-roster.blade.php` | clean | Cited for convention (`h-11`, the absence vocabulary) or excluded by G-SK-11 and the race findings. No change proposed to any of them. |
| `docs/design-research/CONSTRAINTS.md`, root `DESIGN.md` | clean | Read for D-30, D-220 and §4.2. Both are design records this dispatch may not amend, and §4.8 plus §9.5 ask the owner to. |

No file in this table was modified by this proposal. The only write is this document.

---

### 8. What the next dispatch should be

Ranked, and the ranking is a dependency argument, not a preference. **First: Q3 (KI-37 sizing), as its own
small slice.** It was previously second behind "land the peer's edit and let the tree settle", and that
obstacle removed itself while this document was being written: `4e17997` committed the five files, so Q3 now
touches a clean `show.blade.php`. Four class strings plus a `step` plus a focus ring, mechanism proven cheap
by `5ff7aca`, no design decision outstanding once §5.3's recommendation is accepted, and it closes an OPEN
register entry while the Q1, Q2 and Q5 arguments are still being had. **Second, and this is the ordering the
steers changed: the trainee page's skills section plus the D-30 amendment.** It is the cheapest host for
`x-skill-row` in the tree (§9.5), it has no form, no repeater, no JavaScript and no dependency on the picker,
it fixes a false Trainer-facing claim that has been on master for a day, and it lands the amendment the
picker then reads from. Building the row component on a page with no controls, before building it inside a
623-row picker, is the smaller first step. **Third: Q2 (the combobox), refactor-first**, per §4.2, gated
behind that second item because its bands reuse the same grouping vocabulary and the D-30 ask. **Fourth:
Q1's fact block**, gated behind the storage decision in §6 item 1; the design is finished and has nowhere to
read from, so it should ride with the migration rather than precede it. Also cheap and independent, if the
owner wants a fifth: the empty-catalogue state in §4.9 is one Blade string on the existing `<select>` and
needs none of the above, and today's fresh clone ships without it. The review's remaining options are now
Option 2 (the two misnamed tests, the untested pivot shape, and the factory algorithm, which is KI-39) and
Option 4 (the export contract, which needs an Architect ruling on G-SK-15's frozen JSON shape); Option 1
is closed by `4e17997`. Both are still open and both are cheaper than anything above except Q3.

---

### 9. Q5. The trainee detail page

#### 9.1 What the page is today, measured

`resources/views/catalog/show.blade.php` is 282 lines (`wc -l`). The first steer's correction holds: the
page has grown past what KI-35 described when it was filed. It now carries:

| Region | Line today | State |
|---|---|---|
| Name header, `h1` | `:12` | Ships |
| "Basic information" `h2` and the profile block | `:38`, `:40-107` | Ships, with a named no-profile state at `:53-54` |
| Aliases, provenance | later in file | Ships |
| Costume forms, empty-cards branch | `:199-207` | Ships |
| Costume forms, `x-form-tabs` / `form-detail` partial | `:231-241` | Ships |
| **Skills** | nowhere | **Absent, and the copy says why falsely** |
| Aptitudes | nowhere | Absent; `grep -rn aptitude resources/views/` returns zero hits (KI-35 Class A) |
| Goal races, Her runs | nowhere | Absent, and genuinely blocked |

The stale block is the comment at `:209-224` and the **visible** sentence at `:225-229`:

> `Skill lists are not shown: the card document's skill id arrays are not stored, and ADR-0012 keeps them
> off the card row. Objectives and card images are not shown either; that ADR records why.`

Verified on master, not just in the working tree: `git show HEAD:resources/views/catalog/show.blade.php`
carries that sentence at its line 226.

#### 9.2 The correction is a date problem, not an error problem

The steer asked whether Finding A is contested, i.e. whether the copy has an owner-decided rationale that
would bar me from rewriting it. It does not, and the evidence is worth the three lines it takes:

- `git log` gives `555b0cbd`, the commit that wrote the sentence, at **2026-09-30 03:44**. `dd90330`, the
  commit that stored the two arrays, is at **2026-09-30 16:09**. `dd90330` is not an ancestor of `555b0cbd`
  and is an ancestor of HEAD. **The sentence was true for its first twelve and a half hours and has been
  false ever since.** This is `CONSTRAINTS.md` D-289's failure mode with a measured interval attached to it.
- The owner's rationale is on record and it points the other way. ADR-0012's Decision 1 row, at its `:73`,
  now counts "fourteen now that `dd90330` (2026-09-30) added `skills_innate` and `skills_unique`", and its
  `:7` carries the same correction. The ADR the copy cites as the prohibition is the ADR that records the
  columns as present.
- ADR-0012's fact 2, quoted by the audit, is explicit: "Every skill id referenced by a card resolves against
  `skills`, so the detail page can join rather than degrade."

**Where my reading differs from the audit's, stated for the second pass.** Audit Finding A lists the copy's
three claims as "now false". Two are. The third, "every id is unresolvable from the database alone", was
defensible when written, because with no arrays stored there was nothing to resolve; ADR-0012's fact 2
describes the quality of a join, not the presence of the arrays. So the copy did not misread the ADR at
03:44, it read a schema that later changed. That matters for the fix, because it means the replacement
should not carry a dated apology. There was no mistake to date. It also means the sentence's own framing
("both are a new decision, so the line says so instead of drawing four empty groups") is still the right
instinct for the parts that remain absent, and §9.4 keeps it.

#### 9.3 Recommendation: rewrite and build

Of the two shapes the steer offered, **rewrite + build**, and the reason is the page's own history: it has
already been extended twice for sections KI-35 listed as absent (the profile block and the costume-form
tabs), so adding the skills section continues a pattern the page carries rather than opening a new one. A
deferral would correct the copy and leave a Trainer with a page that now says nothing untrue about skills
and still shows them nowhere, which converts a false claim into a silence. KI-35's Class B names what
silence costs: a page that lists no skills reads as "she has none", and on sections 3 and 4 that is the
wrong statement.

Root `DESIGN.md` §4.2 ("Catalog detail `/umamusume/{slug}`") is the spec this answers to, and its eight
sections divide cleanly:

| # | Section | Ship in this slice? | Why |
|---|---|---|---|
| 1 | Identity | Already ships | Profile block and metadata at `:38-107` |
| 2 | Aptitudes | **Already ships** | Corrected 2026-10-01, see the method note. `resources/views/components/aptitude-grid.blade.php` renders all ten letters and `catalog/partials/form-detail.blade.php:56` invokes `<x-aptitude-grid :umamusume="$trainee" />`. `git log --diff-filter=A` dates the grid to `555b0cb` (2026-09-30 03:44), the commit that also landed the profile block and the form tabs. The columns are populated too: a read-only PDO read on 2026-10-01 returns 67 `umamusume` rows with all ten `aptitude_*` non-null on all 67, where this draft first recorded KI-35's "2 rows, all ten NULL". What remains for section 2 is not a build but a record: the render exists while `CONSTRAINTS.md` D-30 does not name the columns (§9.5) |
| 3 | **Skills** | **Yes** | `skills_innate` and `skills_unique` are stored, every id resolves, and the page already knows how to render a card-scoped list |
| 4 | Costume forms | Already ships | `:231-241` |
| 5 | Goal races | No | `ura-objectives` is verified and not ingested; the register entry for the correction is reserved by the owner |
| 6 | Her runs | No | No per-trainee run list exists, and this is the section that would give the page its primary action, which root `DESIGN.md` §4.2 item 6 names. Its absence is why KI-35's "no primary action" finding stays open |
| 7 | Aliases | Already ships | |
| 8 | Provenance | Already ships | |

So: **one section to build, five already shipping, two blocked**, and the deferrals are the ones the
register already owns rather than new ones this proposal invents. Section 2 moved into "already shipping"
when the correction above landed, and it is worth stating plainly that this proposal recommended building
a section the repository had already built thirty-five hours earlier.

#### 9.4 The replacement copy, drafted

Two edits, both inside `:209-229`. The comment first, because a future reader of the view is the one who
will otherwise re-derive the wrong schema.

**Comment, replacing `:213-220`:**

```
**Skills, and what is and is not stored.** The card document carries
`skills_unique`, `skills_innate`, `skills_awakening` and `skills_event` as id
arrays. `dd90330` put the first two on `character_cards` as json lists, and
every id in them resolves against `skills.export_id` (ADR-0012 fact 2, KI-33),
so the two that are stored are joined here and rendered. `skills_awakening`
and `skills_event` are still dropped at parse
(`GametoraCharacterCardParser.php:92-93`): no column, no read path. That is
why this section shows two groups and not four, and the heading says so in
Trainer-facing words rather than drawing two empty groups.
```

**Visible line, replacing `:226-228`:**

```
Her innate and unique skills below are the lists the source publishes for this
form. The source also carries awakening and event skill lists; this tool does
not store them yet, so they are not shown. Card objectives and card images are
not shown either, and `ADR-0012` records why for both.
```

The draft obeys the rules this proposal has been enforcing elsewhere: it names no skill, so nothing in it can
drift the way `config/scenarios.php:270` drifted. It states each absence as the kind of absence it is, per
root `DESIGN.md` §4.2's two-form rule. It keeps ADR-0012's two real decisions (objectives, images) instead of
deleting the sentence that carried them. And it replaces a present-tense claim about the schema with one
that has a named removal trigger: the awakening and event clause goes away the day the parser keeps those
keys.

**This copy is a draft, not an edit.** The fence here is read-only, the file is Trainer-facing, and the
standing rule is that owner-visible copy lands on the owner's word. Section 9 exists so the word, when it
comes, has text attached to it.

#### 9.5 What the section renders, and the D-30 ask it carries

The skills section on the trainee page reuses §4.3's band vocabulary and §3.2's row, because "her innate /
her unique" is the same grouping in the picker and on the page, and two renderings of one grouping is the
drift this proposal keeps naming. It differs in one respect: the page has no spare row and no form, so it
renders groups and rows with no controls at all. That makes it the cheapest possible first host for
`x-skill-row`, cheaper than the run screen, which is a reason to build it before the picker rather than
after it.

The D-30 ask is larger here than in §4.8 and should be one amendment covering both tables:

- `CharacterCard`'s permitted list omits `skills_innate` and `skills_unique`, which this section renders.
- `Umamusume`'s permitted list is `name, name_ja, release_status, debut dates, is_manual, aliases`. It omits
  all ten `aptitude_*` columns, which root `DESIGN.md` §4.2 lists as section 2, which
  `GametoraCharacterParser` reads, and which `aptitude-grid.blade.php` has been rendering since `555b0cb`.
  Corrected 2026-10-01: this draft first described that gap as prospective, something a future section 2
  would run into. It is retrospective. The render is shipped.

So a page that ships section 3 renders fields from two tables that D-30 does not name, for two different
reasons, and the register currently routes only the first. The proposal's position:
**name both in one D-30 amendment, with the use stated**, so that the constraint list says what the schema
holds and what the design is permitted to do with each. Landing section 3 before that amendment is written
is the silent-render path §4.8 refuses, and this document exists partly to make sure it is not taken. The
aptitude half of the problem no longer waits on anyone's permission being sought first, because the render
already exists; it waits on the register catching up. That weakens nothing in the ask and strengthens it,
because a constraint list that omits a shipped render is the failure D-30 was written to prevent.

#### 9.6 Lore and label coverage on the touched surface

The second steer's addition applies here. **Lore:** the region read (`catalog/show.blade.php:198-242`)
carries no banned vocabulary, and the drafted copy in §9.4 carries none; "forms", "costume", "innate",
"unique" and "awakening" are the client's own words and the register's own. The one word to watch in a
skills section on a trainee page is the container metaphor KI-35 already flags, and this section is a list of
skills belonging to a person, so no allowed-but-close sense is being leaned on. **Labels:** the section adds
no form control to that page, so its label exposure is one `h2` and one `h3` per group. The existing
coverage is clean and nothing here can regress it; the guard if a future slice adds a control there is the
same accessible-name assertion that protects the run screen's repeater.

---

### 10. Q4. The three layers of fact, and the fourth the steer named

The first steer is right that the fact set is per-skill and that this is a different object from a run's
context. Naming it "three layers" undercounts by one: there is a fourth, the per-race reading
("this is a Medium race, so does it fire here"), and the honest answer about that layer is that it does not
render. Four layers, one display, and the display must never compose two of them into a sentence that reads
as evaluation.

| Layer | What it is about | Statements it can carry | Where they come from | Can it render today? |
|---|---|---|---|---|
| **L1 per-skill** | the skill, identically for every Trainer and every run | client name, SP cost, whether it is a unique, the tool's derived type word, which running style gates it, the activation predicate verbatim | `skills` columns (`Skill.php`'s fillable set), plus stored conditions and a style code, which do not exist yet | Partially. Name, cost, unique and type today. Style and predicate after §6 item 1's storage |
| **L2 per-run** | what this Trainer recorded | status, the turn they say it was taken | `run_skills`: `status`, `turn_acquired`. **Two columns, nothing else** | Yes, fully |
| **L3 per-trainee** | who she is, independent of any run | her aptitude grades including the four style aptitudes; which skills are hers by innateness or uniqueness | `umamusume.aptitude_*` (ten columns, parsed, and populated: 67 rows with all ten non-null on the 2026-10-01 read, already rendered by `aptitude-grid.blade.php`); `character_cards.skills_innate` / `skills_unique` | Groups yes. Aptitudes yes, and they ship today |
| **L4 per-race** | the race this run is entering | would be: whether a gate is satisfied at this distance, on this ground, in this season | predicate atoms such as `course_distance==2400`, `distance_type`, `ground_type`, `season`, against `race_catalog_slots` | **No. Not rendered at all, in this design or any later one without new work** |

#### 10.1 The rule that keeps L1 and L3 from becoming advice

L1 and L3 are individually facts and jointly a recommendation. `Top Gear` is gated to Late Surger (L1).
`aptitude_late_surger` holds a letter grade (L3). Print them adjacent and the reader's conclusion is "this
skill suits her", which is a judgment the page never states and cannot source: the grade scale's meaning for
skill-gate purposes is published by no source in this repository, and the atom that would tie them is
`running_style==3`, a gate on *which style the client selects in the race*, not a measure of how well she
performs it.

`DESIGN.md` §6.16's advisory rule is the standard the pair would have to meet: a suggestion must carry its
arithmetic, name the constant and the stored value that produced it, and cite its source with a date, and
"a suggestion that cannot produce that line does not render." No composition of L1 with L3 can produce that
line, because there is no arithmetic between a style gate and an aptitude letter. So the design rule is
spatial and it is testable, and it is testable today rather than prospectively: `aptitude-grid.blade.php`
has printed the four style aptitudes on the trainee page since `555b0cb`, so the surface that would tempt
the composition already exists.

1. **L1 and L3 never appear in one row, one line, or one labelled group.**
2. L3 lives in two places only: the trainee page's own sections, and the picker's band assignment, which is
   membership rather than quality and prints no grade.3. L1 lives on the run screen's read-only row and in the disclosure.
4. L2 sits with L1, because a turn number and a cost are both records of what happened.
5. A style label is L1 and means "the client restricts this skill to that style". It is never coloured,
   weighted or positioned as though it were a verdict, which is why §3.2 gives it no pill.

#### 10.2 What does not appear at all, and the two reasons

- **L4 entirely.** Two independent reasons, and both are needed. First, no mechanism: there is no stored
  link from a run's skill set to a specific upcoming race that would let a statement be made about this
  race, and `race_entries` to `turn_entries` and `race_catalog_slots` joins exist for other purposes.
  Second, and this is the one that survives a schema change, evaluating a gate requires decoding atoms whose
  meanings are recorded as unverified: the fact document prints `course_distance`, `distance_type`,
  `ground_type` and `season` as atoms with no glossary, and its rule 2 forbids paraphrasing them. L4 would
  be a page of invented facts with a confident layout.
- **Any statement about how a skill entered the run.** `run_skills` cannot source it (fifth steer, item 1).
  "Yours because innate", "unlocked mid-run" and "won in Race 3" are all L2 claims the L2 layer does not
  hold, and §4.10 refuses the marker for the same reason. The bands are L3, computed from the card at render
  time, and they say whose skill it is, not how it got here.
- **Any decoded effect magnitude.** `effects: type 27 value 4500` prints as an L1 verbatim fact inside the
  disclosure and never as "increases speed by 15%", because no tracked source decodes code 27.

#### 10.3 Where each layer sits in the interface

```
Run screen, read-only list          L1 (row) + L2 (cost, turn)  +  L1 predicate on disclosure
Run screen, picker                  L3 (band membership) + L1 (name, cost)
Trainee page, skills section        L3 (groups) + L1 (rows)
Trainee page, aptitudes section     L3 alone, shipped by x-aptitude-grid, with its own absence branch
Screen D results                    L1 only, exactly as it ships today
Nowhere                             L4
```

`x-skill-row` renders L1 plus the L2 slots handed to it as props. It takes no aptitude prop and no race prop,
and that is the mechanism, not a convention: a component with no way to receive layer 3 cannot compose it
into a row.

---

### 11. Q6. Whether the fact panel attaches to the picker, the run summary, or both

**Both, with different facts, and the split is the whole answer.** Q1 said "the read-only list, not the
repeater" and this section refines rather than reverses that, because the first steer's framing is correct
that the two moments each have useful facts. They are not the same facts.

| Moment | Question the Trainer is asking | What attaches | What does not |
|---|---|---|---|
| Choosing, in the picker | "is this the one I mean, and can I afford it" | L3 band, L1 name, L1 SP cost | Predicate, style label, type word, anything from §3.3's disclosure |
| Reading, in the run summary | "what did she take, and why does that fire" | Full L1 row, L2 status and turn, L1 predicate on disclosure | Bands, and every L3 field |

Three reasons the picker gets the narrow set, not the panel:

1. **Arithmetic.** A fact block in a repeater row is paid once per row, and the row count is the thing that
   produced 13,167 options. The picker's whole problem in §4 is its per-row cost; attaching the panel there
   would be proposing the same mistake twice.
2. **A `<select>` is not a surface.** It cannot hold a disclosure, its option labels are read by the browser
   in a native popup of the browser's choosing, and §6.11's row would be reduced to something worse than the
   one line it already carries.
3. **The choosing moment does not need the predicate.** A Trainer picking `Top Gear` is identifying it and
   pricing it. "Which turn does the engine evaluate this on" is a question about a skill they have already
   picked, which is the read surface.

And the reason the run summary gets the panel rather than only the picker: the owner's ruling names the
record. `record what the Trainer chose, with facts attached` puts the facts on the record of the choice, and
the record is the read-only list and its pivot row. A fact attached only at pick time is gone the moment the
run is reopened, which is precisely the re-reading the ruling is for.

**The third moment, which neither option covers.** Exploration, on Screen D, is where a Trainer who does not
know what they want goes, and it already exists. §4.11 answers the duplicate-job question the third steer
raised: the combobox returns a value into a form and Screen D returns nothing, so the picker gains
type-to-narrow and band ordering and gains **no facets, no pagination, no detail panel and no
provenance block**. If the picker ever needs a facet, that is evidence Screen D should grow a picker, not
evidence the picker should become Screen D. The `Search the skill catalog` link at
`show.blade.php:469` stays, because it is the honest statement that 623 rows is a search problem and not a
scrolling one, and G-SK-13's interim escape-hatch role is recorded in the comment above it.

**Neither, rejected.** The option of attaching facts to nothing, keeping the section as it ships and
deferring the whole question to the picker slice, was considered and is wrong on its own terms: it leaves
`skill-facts-2026-10-01.md` as a 715-line document that renders nowhere, and its worked example at export
`201241` states the combination the design exists to show. A fact set with no display is a document, and Phase
B1 already produced that document.

---

### 12. Premises in this dispatch that were wrong

Expected section. Each was checked against the tree, not argued.

**P-1. Q1's degradation premise is falsified by the fact document's own coverage table.** The dispatch
asks what happens "for a skill with a missing fact (some skills have no `condition_groups`, or no
`endesc`)". Measured in `skill-facts-2026-10-01.md` §Coverage: `condition_groups` non-empty on **623 of
623**, with the empty case measured at 0 and the canary stating the zero is meaningful; `endesc` present on
**623 of 623**, and it "exists on all 1910 records, so any absence here would be a bug rather than a gap".
Neither case exists in the Global set. §3.4 answers the questions that are real instead (the `cost`
absence on 147 rows, the style-gate absence on 500, the derived-`type` null).

**P-2. Three of the seven "facts" are not attachable, and one of the three is barred by a ruling.** The
dispatch lists "client description, activation conditions, engine class, SP cost, uniqueness, availability,
style eligibility" as though they were one set of equal standing. Four are stored columns. The client
description is in the export and is **barred from the display path** by G-SK-20 until an in-client capture
exists, which is why `skills/index.blade.php:9-13` renders none. The activation conditions and the style
label are in the export document and in no column. And "engine class" is ambiguous: the stored `type` is
this tool's three-word derivation, the export's `type` is an UNVERIFIED gate-key array, and they are not
the same object. §3.0 separates them before §3.1 designs anything.

**P-3. The fence list understates the peer's in-flight work, and the review's Option 1 is partly landed
already.** The dispatch says the peer holds `show.blade.php` and `RunSkillRowLabelsTest.php`. `git status`
shows five files, including `TrainingRunController.php` and `StoreRunSkillRequest.php`, and the diffs are
the review's F-2 write-path scoping plus a new refusal test. Further: F-10's remaining fix, "pass
`$turnError` to the directive at `:550`", is **already done** in the working tree at `show.blade.php:550`.
A design that assumed F-1, F-2 and F-10 were all open would be designing fixes that exist. §7 lists all
five, and §2 states the position.

**P-4. "The violation stands on three controls" does not match either measurement set.** The dispatch
restates the review's F-9 conclusion. F-9's own sentence says three "rather than four" and then lists four
values (31, 31, 30, 32); §H.3's table has four rows. KI-37's body enumerates three controls. The
measurement supports **four controls at three distinct heights**. §5.1 says so rather than inheriting the
phrasing, because the count is what a fix gets sized by.

**P-5. The dispatch conflated two different `DESIGN.md` files, and "§2.3" is the tell.** It asks for
"`DESIGN.md` §6.14 (form field spec), §2.3 (screen structure), §6.5 (stat band markers)" from
`docs/design-research/DESIGN.md`. §6.14 and §6.5 are correct there. There is no §2.3 in that file: its §2 is
"Principles" with no numbered subsections, and its only `2.3` is a row number in §12's review log at `:1621`.
But the root `DESIGN.md` **does** have a §2.3, and it is "Spacing and layout", not screen structure. So the
citation resolves in neither file to the thing described. The nearest shipped statement of a single primary
action per screen is root `DESIGN.md` §4.2's section-6 line ("the page's **primary action**"), and KI-35's
attribution of "§2.3's one-primary-action-per-screen" inherits the same broken pointer. Not fixed (fence),
and it should be re-anchored wherever it is cited. See P-10, which is the larger problem behind this one.

**P-10. There are two `DESIGN.md` files with different structures, and this dispatch's citations could not
resolve until they were distinguished.** `DESIGN.md` at the repository root (27,675 bytes) is the **surface
specification**: §4 "Surface specifications" with §4.1 catalog index, §4.2 catalog detail, §4.4 run detail.
`docs/design-research/DESIGN.md` (152,232 bytes) is the **design system**: §6 component anatomy, §8 UX
architecture, §10 accessibility. The dispatch's context list names the second path but its §2.3 pointer
only exists in the first, and §9 of this proposal depends on the first (§4.2's eight sections and its
two-form absence rule) while §3 through §5 depend on the second. Every citation in this document is
therefore written with its file named. The register and the review docs carry the same ambiguity, so a
second pass that greps for "`DESIGN.md` §4.2" or "§6.14" without checking which file is meant will find
either nothing or the wrong thing. **Recommendation for the next register pass: the two files need distinct
names or one needs to move, and "which DESIGN.md" should stop being answerable only by opening both.**

**P-6. `guided-step.blade.php` is not a disclosure precedent and holds no `h-11`.** The dispatch names it
"the second precedent, for how the app handles per-row disclosure". It has no disclosure mechanism: the
file's roles are `group`, `radiogroup` and `img`, its interaction is a roving-focus radio set, and `grep`
for `<details`, `<summary`, `aria-expanded` and `dialog` returns nothing. It also holds **zero** `h-11`
occurrences, which matters because `skills/index.blade.php:20` tells the next implementer that "the
section headers and the guided-step rows use it". The app's only disclosure precedent is the `<details>` at
`show.blade.php:406-407`, which is what §3.3 uses.

**P-7. The same comment cites the wrong KI number.** `skills/index.blade.php:20-23` says the older,
unsized surface "stays as KI-28". KI-28 is "SQLite reads an unknown double-quoted identifier as a string
literal"; the sizing entry for `/umamusume` is **KI-29**. A reader who followed that citation would land on
an unrelated hazard. Not edited (fence), recorded here.

**P-8. KI-33 is CLOSED, so "the four-group picker question from KI-33" needs its anchor named.** The
dispatch's context line calls it "the four-group picker question from KI-33" and lists "KI-33 (four-group
picker open end)". KI-33's heading ends "CLOSED 2026-09-30". The four-group picker survives as a paragraph
inside the closed entry headed "One open end, and it is not this entry's", which also says plainly that the
slice "is unblocked; it is not closed here". Substantively the dispatch is right; citationally, pointing a
new slice at an entry number whose status word is CLOSED is how an unbuilt feature starts reading as
shipped, which is F-8's exact failure. §4.7 cites the paragraph by name.

**P-11. Two sharpenings on the audit and the steers, in the direction of worse.**

- Audit Finding A calls the trainee page copy's three claims "now false". Two are false now. The third,
  "every id is unresolvable from the database alone", was defensible at the moment it was written, because
  with nothing stored there was no join to attempt. §9.2 carries the reasoning and the measured twelve and a
  half hour interval. The consequence is practical rather than pedantic: the replacement copy needs no dated
  apology, because there was no error to date.
- The third and fourth steers say a fresh clone "may" render only the picker placeholder. It is worse than
  that, and the derivation is in §4.9: `DatabaseSeeder` at HEAD calls `SkillSeeder`, whose nine rows set
  neither `release_status` nor `name_is_client`, so **no tracked writer in this tree can produce a row the
  picker offers.** The empty picker is the default first state of a new install, not a possibility, and the
  empty state is therefore a requirement rather than a courtesy.

**P-13. The tree moved four commits during this pass and falsified four statements before they were filed.**
HEAD went `80cb4dd` → `d6ed712` (files KI-51) → `7a94565` and `70abea5` (both docs) → `4e17997`. The last
move committed the peer's five-file fix, which made §2's "all of it is uncommitted", §7's five dirty rows,
§8's "first: land the peer's edit", and §4.10's "the peer's working-tree rule" all wrong as written. They
are corrected above rather than quietly rewritten, and the correction is attributed: this is the review's own
"Working-tree drift during this review" failure mode recurring one document later, in a pass that had read
that section and cited it. Two related facts worth the reader's attention. The audit is described by its
steer as "committed, untracked-optional but landed"; `git status` reports `??` for it, so it is untracked and
the description was half right. And a
`skills-mechanics-audit-verification-2026-10-01.md` now also exists, untracked, which is the second pass on
the audit this proposal leans on; it was not in any steer's context list, is not cited anywhere above, and
its findings have not been reconciled against §9's reading of Finding A. That reconciliation belongs to the
third pass, not to this document.

**P-12. Everything else in the five steers checked out as stated.** The audit's §2 shipped list, its §3
matrix rows for unique, innate, awakening and event, and its Finding C were each verified against
`GametoraCharacterCardParser.php:92-93`, `CharacterCard.php:35-36, 40, 66-67` and the
`2026_09_30_073029` migration before being relied on, and all agree. The steer's claim that `dd90330` is an
ancestor of HEAD is confirmed. KI-35's eight-section candidate shape is real and lives in root `DESIGN.md`
§4.2 (see P-10). The D-30 omission is exactly where the steer said, and its `Umamusume` half, which the
steer did not mention, is missing too (§9.5).

**P-14. The proposal's own §9.3 was wrong, and the follow-up dispatch caught it.** This draft recommended
building the trainee page's section 2 (Aptitudes) while citing KI-35's stale "2 rows, all ten NULL"
measurement, and it placed section 2 in the "not yet" column of the ship list. The grid shipped the day
before this draft was written: `git log --diff-filter=A` dates `resources/views/components/aptitude-grid.blade.php`
to `555b0cb` (2026-09-30 03:44), and `grep -rn aptitude resources/views/` now returns three files rather
than the zero hits KI-35 records. So the draft advised building something that exists, and it built that
advice on a register entry that was accurate when filed and superseded the next morning. Two lessons the
follow-up should carry forward: a measurement quoted from a register is still a register claim and needs a
fresh read before it steers a recommendation, and the D-30 ask in §9.5 changed character without changing
direction, because a render already shipped is catch-up rather than permission. Recorded here rather than
silently edited, and the §9.3, §9.5, §10.1 and §10.3 corrections are noted in the method section.

**P-9. Minor, recorded for completeness.** The dispatch's "review's §I" for the do-not-touch list is
correct as a heading. Its "Option 3 (narrow the picker)" is correct. Its Q2 figures (13,167 options, 1.84
MB, 1.82s vs 0.51s) reproduce the review's §E exactly. Its claim that "0 unnamed controls" was measured
reproduces §E and §H.2. Its four `h-11` component names all check out; only the guided-step claim in P-6
does not. The review file itself, cited as landed, is untracked (§7).

---

### 13. Fence and shared-DB confirmation

- Files written: **1**, this document, untracked, DRAFT. No commit was made and nothing was staged.
- Files edited: **0**. No view, component, controller, model, migration, test, factory, config, CSS,
  `PRD.md`, `AGENTS.md`, `CONSTRAINTS.md`, `DESIGN.md`, `KNOWN-ISSUES.md`, `SKILLS-GAPS.md` or ADR file was
  modified.
- Commands run that write: none. No `migrate`, `migrate:fresh`, `db:wipe`, `migrate:rollback`, no seeder, no
  `paratest`, no test run, no `pint`, no push, merge, rebase, stash, checkout or reset.
- No browser session and no server were needed: every figure this proposal rests on is either cited from the
  review's two measured passes or read from source. No scratch database was created, so the earlier
  scratch-path collision hazard was not approached.
- **Shared database fingerprint, before and after, unchanged.** Method: `sha256sum database/database.sqlite`
  plus `stat -c '%s %y'` on the file and its `-wal` sidecar, taken once before the write and once after.
  - Before: `550c6050bf84216df4d0474c46c354d4556c7f226be6c661c35cd5835ed9f7f3`, 1,310,720 bytes,
    mtime `2026-10-01 02:41:21.790 +0800`; `-wal` 168,952 bytes, mtime `03:03:21.193`.
  - After: §13.1 below.
  - Note the "before" hash is **identical to the value the review recorded** at its own fence section, which
    is independent corroboration that this pass touched nothing the review also held read-only.
- Em dashes: none in prose added here. The character does not appear in this file. Where a cited register
  sentence contains one, the sentence was paraphrased rather than quoted, since the dispatch's single
  exception is a verbatim *client* string.
- **Lore, measured rather than asserted.** `php tools/lore.php` was run after the file was written and
  reports **136 hits / 57 exempt lines** across the docs scope at `80cb4dd` with the working tree as it
  stands. The repo-wide figure has moved since the 101/57 baseline recorded in the 2026-09-29 pass, and most
  of that movement is other sessions' untracked docs, not this file. This file's own contribution, measured
  with the tool's own two word-boundary patterns:
  - Four lines matched on the first sweep. Three were incidental adjectives or noun forms carrying no
    animal sense at all, and each was reworded to a plainer synonym: at `§4.1`, at `§11`, and at `§12` P-9.
    A fourth matched inside this bullet, because the bullet was describing the words it had just removed,
    which is a loop worth naming: a gate report that quotes the banned list becomes a gate hit by quoting
    it. This version describes the changes without reproducing any of the four terms.
  - **Final measured state of this file: zero matches under both of the tool's patterns.** Re-runnable as
    `grep -inwE` with each of the two pattern groups from `tools/lore.php:63` and `:70` against this path.
  - No character is framed as an animal anywhere in this proposal, and the running-style words are the
    client's own four labels (`UMAMUSUME_REFERENCE.md:231-234`). §9.6 carries the same verdict for the
    Trainer-facing copy §9 drafts, which is the part a second pass should re-check rather than trust.
  - **Correction kept on the record:** an earlier draft of this bullet asserted the file contained none of
    the banned vocabulary. That was false. It was written from reading my own prose instead of from running
    the instrument, which is the exact failure mode this repository has filed repeatedly, and the gate
    falsified it within one command. The repo-wide total at `80cb4dd` is the tool's to report and this
    pass did not change it, because no tracked file was edited.
- Skill invocation: five of the eight named skills resolve on disk (`design-review`,
  `design-consultation`, `careful` under the Claude skills directory; `antislop-copywriting` under
  `.agents/skills`; `infer-conventions` under the project's `.agents/skills`). Three named skills
  (**`source-driven-development`, `frontend-ui-engineering`, `interview-me`**) were not found in any scope
  this session can read, and none of the eight was invoked through the Skill tool, because none appears in
  this session's registered skill listing. Their intent was honoured directly instead: file-and-line
  citation for every current-state claim, the accessibility and layout constraints in §3.5 and §5, the
  existing conventions read before any shape was proposed in §4.2, and no question asked of the owner
  because the review plus the fact document resolved the fact panel's intent rather than leaving it
  ambiguous. **This is a reportable deviation, not a silent substitution.**

### 13.1 Shared database fingerprint, after

`550c6050bf84216df4d0474c46c354d4556c7f226be6c661c35cd5835ed9f7f3`, 1,310,720 bytes, mtime
`2026-10-01 02:41:21.790 +0800`; `-wal` 168,952 bytes, mtime `03:03:21.193`. **Identical to the before
reading on all four values**, and identical to the hash the review recorded at its own fence section. The
`-shm` and the 4 KiB `.bak` (the WAL decoy, not a database) were not opened. No `artisan` command ran in
this session against any database, so no session row was written anywhere.


---

## skills-section-reconciliation-2026-10-01.md

## DRAFT: reconciliation memo, the stale skills sentence in `catalog/show.blade.php`

Read-only reconciliation at `HEAD` `4e179978`. One new untracked file (this memo); no existing file was edited and no state-mutating git command was run. Subject: the Trainer-facing sentence at `resources/views/catalog/show.blade.php:225-229` (line 226 on `HEAD`), quoted in the proposal at `skills-section-phase-b2-2026-10-01.md:992-993`: "Skill lists are not shown: the card document's skill id arrays are not stored, and `ADR-0012` keeps them off the card row. Objectives and card images are not shown either; that ADR records why."

### 1. Method note

Read in full: `docs/design-research/skills-mechanics-audit-2026-10-01.md` (108 lines) and `docs/design-research/skills-mechanics-audit-verification-2026-10-01.md` (122 lines), plus the proposal's section 9 at `docs/design-research/skills-section-phase-b2-2026-10-01.md:972-1099` (heading found by `grep` at `:998`). Read in part: `docs/adr/0012-card-detail-fields-and-images.md` whole, its revision at `555b0cbd` via `git show 555b0cbd:docs/adr/0012-card-detail-fields-and-images.md`, `resources/views/catalog/show.blade.php:200-239`, `app/Services/DataPipeline/Parsers/GametoraCharacterCardParser.php:70-110`, and migration `database/migrations/2026_09_30_073029_add_skill_lists_to_character_cards_table.php`.

Commands run, all read-only: `git log --format='%h %ad %s' --date=format:'%Y-%m-%d %H:%M' -n1 555b0cbd` (03:44) and `-n1 dd90330` (16:09); `git merge-base --is-ancestor dd90330 HEAD && echo ancestor-of-HEAD` (printed) and `git merge-base --is-ancestor dd90330 555b0cbd; echo $?` (exit 1); `git merge-base dd90330 555b0cbd` (returned `555b0cbd`); `git show HEAD:resources/views/catalog/show.blade.php | grep -n 'Skill lists are not shown'` (line 226); `git rev-parse HEAD` (`4e179978`); `sed -n '145,165p' docs/adr/0012-card-detail-fields-and-images.md`; `git log -- docs/adr/0012-card-detail-fields-and-images.md` and `git log -- resources/views/catalog/show.blade.php`; `git show dd90330 --stat`; `git show 038dd43 --stat`; `git merge-base --is-ancestor 038dd43 HEAD`; `git status --porcelain` on the touched paths (three design-research docs untracked, no working-tree diff vs `HEAD` under `docs/adr`, `resources/views/catalog` or the parser).

Could not verify: the 1,513 of 1,513 id join, carried from KI-33 via `docs/adr/0012-card-detail-fields-and-images.md:157`, the migration's `:24-25` and the audit's `:69`, and not re-run here (the verification pass did not re-run it either, its `:43-46`). The test suite was not run, per the task fence. `HEAD` has moved since both documents were pinned (audit at `6476a22`, verification at `d6ed712`), so line numbers below are as of `4e179978` unless a commit is named.

### 2. The two readings, stated fairly

Reading A, "aged rather than erred": `docs/design-research/skills-section-phase-b2-2026-10-01.md:998-1021` (section 9.2). The sentence was true when written at `555b0cbd` (2026-09-30 03:44) and became false when `dd90330` (16:09) stored the two json skill lists. Two of its claims are now false; the third, "every id is unresolvable from the database alone" (the comment at `show.blade.php:217-219`), was defensible at writing time because with no arrays stored there was nothing to resolve, and `ADR-0012`'s fact 2 describes the quality of a join, not the presence of the arrays. The fix should not carry a dated apology.

Reading B: `docs/design-research/skills-mechanics-audit-verification-2026-10-01.md:38` (section 2 table row). It restates audit Finding A (`docs/design-research/skills-mechanics-audit-2026-10-01.md:66-72`, section 5) as "The catalog deferral block states three things that are no longer true" and returns the verdict "Confirmed (the block predates the storage and was never reconciled)". It does not repeat the audit's error word for the copy, "repeats the same misreading to Trainers" (`skills-mechanics-audit-2026-10-01.md:70`); its stated cause is temporal, the block predating the storage, and it treats all three claims alike.

### 3. Which reading the evidence supports

The date reading, Reading A, and specifically its split of the third claim. The evidence:

1. `git merge-base dd90330 555b0cbd` returns `555b0cbd`, so the sentence commit precedes the storage commit on the same line of history; `git merge-base --is-ancestor dd90330 555b0cbd` exits 1, so the storage was not in the sentence's history when it was written; `git merge-base --is-ancestor dd90330 HEAD` exits 0, so master carries both.
2. `ADR-0012` at `555b0cbd` (that revision's lines 104 and 115-119) carried both "The card skill fields stay out as **schema on the card**" and fact 2, "Every skill id referenced by a card resolves against `skills`, so the detail page can join rather than degrade." Two coexisting sentences can only mean fact 2 spoke to join quality rather than to stored arrays, which is Reading A's argument for the third claim.
3. The sentence has not been touched since writing: `git log -- resources/views/catalog/show.blade.php` shows `555b0cb` as the file's most recent commit, and the sentence still prints at `HEAD` line 226. "Never reconciled" (`skills-mechanics-audit-verification-2026-10-01.md:38`) is measured, not assumed.
4. Precision on Reading A's interval: the storage clause ("are not stored") was falsified at `dd90330` (16:09); the ADR clause's textual support lapsed at `038dd43` (2026-09-30 18:47, "widen the twelve-column statements to the current fourteen", touching only `docs/adr/0008` and `docs/adr/0012`), which is when `ADR-0012` began recording the two columns as present (`docs/adr/0012-card-detail-fields-and-images.md:6-8` and `:73`). `dd90330` itself did not edit the ADR; `git show dd90330 --stat` lists no `docs/adr` path.

Conclusion: the two readings are compatible and the difference is emphasis. Reading B's "no longer true" and "predates the storage" are themselves the date reading; nothing in either document asserts the copy was wrong when written except the audit's "misreading" word, which fits the post-storage state but not the writing time. There is no third reading to prefer.

### 4. Does the proposal's 9.4 copy draft need revision?

No, judged only against this reconciliation. The draft's factual base (`skills-section-phase-b2-2026-10-01.md:1055-1076`) holds at `4e179978`: the two lists are stored as json after `dd90330` (migration `:41-42`, parser `:92-93`); ids resolve (`ADR-0012:157`, migration `:24-25`, audit `:69`, KI-33 carried); awakening and event are still dropped at parse (the parser's record builder at `:79-94` keeps only the two skill keys; audit `:83`); objectives and images reasons remain on record (`ADR-0012` Decision 2 at `:74`, Decision 3 at `:75`; the SUPERSEDED callout at `:76-95` withdraws Decision 4, not these two). The date reading endorses the draft's two choices (proposal `:1078-1083`): no dated apology, and a removal trigger on the awakening and event clause. The copy may keep "every id in them resolves against `skills.export_id`" because `ADR-0012:157` and the migration `:24-25` state it; this memo did not re-measure the figure, and neither pass did, so that citation stands on KI-33.

### 5. Does audit Finding A need a forward note?

Yes: Finding A's "repeats the same misreading" (`skills-mechanics-audit-2026-10-01.md:70`) reads as an at-writing-time error that the evidence does not support, and the verification row confirms the finding without dating it. Proposed words, to be appended to Finding A by whoever owns the consolidation pass (`skills-mechanics-audit-verification-2026-10-01.md:13-14`), and not added by this memo:

> Forward note, added by the 2026-10-01 reconciliation. The three claims are dated, not errored. `555b0cbd` (2026-09-30 03:44) wrote the copy, and `git merge-base dd90330 555b0cbd` returns `555b0cbd`, so `dd90330` (16:09) landed later; at the writing date no card skill array was stored and `character_cards` carried twelve columns (`ADR-0012:73`). `ADR-0012` at `555b0cbd` (its lines 104 and 115-119) carried both "The card skill fields stay out as **schema on the card**" and fact 2, "Every skill id referenced by a card resolves against `skills`", so fact 2 described the join's quality, not the arrays' presence. Read "now false" as dated at `dd90330` (16:09) for the storage claims and at `038dd43` (18:47, the twelve-to-fourteen widening) for the ADR claim. The third claim, "every id is unresolvable from the database alone", was true when written, because the database held no card skill id to resolve. The rewrite advice below is unaffected.

### 6. Open questions for the owner

1. Where should the forward note land: the audit's Finding A, the verification row at `docs/design-research/skills-mechanics-audit-verification-2026-10-01.md:38`, or the consolidation pass the verification doc already names (`:13-14`)?
2. Should the proposal's section 9.2 "false ever since" line (`skills-section-phase-b2-2026-10-01.md:1005-1006`) be re-dated to the two dates in section 3 item 4 above? The reading does not change, only the precision.
3. Commit disposition for the four untracked documents (audit, verification, proposal, this memo): both documents leave it to the owner (`skills-mechanics-audit-2026-10-01.md:108`, `skills-mechanics-audit-verification-2026-10-01.md:121-122`).


---

