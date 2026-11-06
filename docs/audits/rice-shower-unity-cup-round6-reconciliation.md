# Round 6 reconciliation register

Audit ID: `rice-shower-unity-cup-round6`. Walk reconciled: `RS-UC-2026-10`
(`docs/audits/rice-shower-unity-cup-ux-walk-6.md`). Audit tree: `3f295e80` (`master`).
Read commit: `3f295e80`.

Relationship to rounds 1-5: this register supersedes prior corrections by reference. Every
correction to an earlier register, to the walk, or to the round-6 brief is a dated note in
§6. No earlier register, no walk file and no source document is edited.

## 1. Baseline

| Item | Value |
| --- | --- |
| HEAD | `3f295e803871774f4b1bef504816183bf63e8819` (`master`) |
| Read commit | `3f295e80`; the audit's own commit, and the tree every status was read at |
| Dirty set at start | `?? docs/audits/RS-UC-2026-10/`, `?? docs/audits/rice-shower-unity-cup-ux-walk-6.md` |
| Pinned walk hash | `9a88fe396b5ed21a37d711d7dd862eb70ff8b9cc` (`git hash-object docs/audits/rice-shower-unity-cup-ux-walk-6.md`) |
| Walk size | 1,691 lines / 151,710 bytes; 54 findings at `:1295-1352` (17 F, 14 E, 23 U); 27 screenshots under `RS-UC-2026-10/screens/` |
| Source document | `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md`, tracked (`git ls-files` returns one line); 1,394 lines |
| Mechanics reference | `docs/UMAMUSUME_REFERENCE.md`, tracked; 2,786 lines |
| `migrate:status` | Clean. Every migration reads `Ran`, the last being `2026_11_05_084524_add_match_key_ja_to_skills_table` |
| Suite gate | `vendor/bin/pest tests/Feature/ScenarioMetadataTest.php` → **3 passed (52 assertions)**, exit 0, 41.49s |
| `storage/inertia-devtools` | 183 entries. Well under the threshold at which round 3's `R3-32` reports a fatal first request, so nothing was moved aside |
| Browser suite | Not run. This pass makes no rendered claim |
| Gates run | the Pest gate above; `php tools/lore.php` (555 hits, 75 exempt); `php tools/lore.php code` (313 hits) |
| Gates not run | the full Pest suite, the browser suite, `npm run typecheck`, `npm run build`, `pint`, PHPStan, `composer audit`, `npm audit`. This is a read-and-write-a-document pass; no PHP, TypeScript, Vue, config, route or migration file is touched |

**Ownership of the artifacts.** The register is committed by this pass. The walk file and its
screenshot directory are left **untracked** for the owner's decision: they are on disk at the
pinned hash above, and a reader who finds the reconciliation without them should understand
that the walk itself is present but not in version control.

**Documentation placement.** `docs/audits/` is not one of the masters under
`docs/research-scratch/`; this file follows the tracked round-2 through round-5 precedent in
that directory rather than the root standing set, which is closed.

**What a status is.** Classification is by file read at `3f295e80`, with the settling command
recorded per row. No status in this register is a runtime observation, an inference from the
walk, or an inheritance from an earlier register.

## 2. Method

Six statuses, one per row, no seventh:

`Confirmed fixed` · `Confirmed defect` · `Known limitation` · `Unrepresented source data` ·
`Source conflict / open` · `Does not reproduce`

Four rules decide the rows where the briefs disagreed:

1. **`Confirmed fixed` requires a verified repair, or a proven working predecessor plus a
   passing test.** A capability that always existed and the walk did not use is
   `Does not reproduce`, not `Confirmed fixed`. Every row in this register is
   `Confirmed fixed: 0`, because nothing in this pass repairs anything.
2. **`Does not reproduce` means the evidence refutes the claim** — not that a path went
   unexercised. Where the observation reproduces and only the proposed *fix* is refuted, the
   row is `Known limitation`.
3. **Off-path homes.** Stored **and** enterable somewhere, but not on the walked path →
   `Known limitation` with a `field exists, off-path` facet. Stored with no user-reachable
   writer → `Unrepresented source data` with a `stored, no writer` facet. No storage at all →
   `Unrepresented source data`.
4. **A regression label needs a proven predecessor boundary.** Two of the commits the briefs
   name as landings are docs-only (§6, C1), so no row here carries "regression from a landed
   fix" on their strength. Columns:

| Column | Meaning |
| --- | --- |
| `Facets` | The distinct halves of one finding, named. Rows are never merged and never renumbered |
| `Regression` | `no` / `unproven`, with the boundary commit when one is proven |
| `Verification` | `code read`, with the command in the row |
| `Disposition` | What closes it: a slice, a verification-only close, an owner decision, or nothing |

## 3. Reference instantiation

`docs/UMAMUSUME_REFERENCE.md` is the authority for what the client does. The sections below
are the ones the rows lean on, quoted from the tree. Three sections the round-6 brief names do
not exist, and the register cites the nearest real one instead.

| Subject | Section (lines) | What it settles | Marker |
| --- | --- | --- | --- |
| Blue Spark payout | §1.5.2 (`:649-660`), restated §1.3.5 (`:436`) | `One stat up +5, +12, or +21 at career start for 1, 2, or 3 stars` — a **flat stat award**, not a cap raise | None. Basis `[A]`+`[A]`+`[B]` |
| Breakthrough cap raise | §1.3.4 (`:413-426`), row at `:420` | `+16 to that stat's cap per ★3 basic-ability factor inherited; the cap applies at 育成開始時, Classic and Senior inheritance moments`; server scope `[JP]`, publisher ⚠️ STALE (2025-11-21) | None |
| Scenario caps | §1.3.4 (`:418`), §2.1 (`:889-894`, `:907`, `:913`) | `Unity Cup 1300 / 1300 / 1300 / 1300 / 1800`, derived as a `stats` array over a 1200 base. **"Three independent readings" is §2.1 `:907`, not §1.2**; **§2.1.1 does not exist** | None |
| Our Grand Concert uncap | §2.2.4 (`:1134-1136`) | A one-star blue Spark uncaps by 4, two by 9, three by 16, capped at 48 points per parent set — a **scenario-specific cap layer** the other three `[Global]` entries do not claim | None |
| Card types | §1.4.1 (`:475-495`) | `The client sorts cards into seven types, and Group is a type in its own right`; `friend` / 友人 / **Pal** is one of the seven, Group the seventh | None |
| Deck shape | §1.4.5 (`:533`), §1.4.7 (`:590`) | `A deck holds six support card slots`; the legend carries seven chips including a single olive `friend` figure. **The reference does not state that the friend card occupies slot six, and never prints a `Friends` pill** — that wording is the source document's (`[Rosy_Dreams]…:115-118`, `:908-909`) | None |
| SSR level ladder | §1.4.2 (`:497-509`) | `30 + 5 × breaks` for SSR; the ladder is `30 / 35 / 40 / 45 / 50`, base plus four breaks at +5. §7 row 14 grades the figure Medium and its reproducibility Low | None |
| Growth Rate row and Potential Level | §1.3.5 (`:428-449`) | A five-cell row; `a zero-growth stat prints an explicit +0%`; Rice Shower reads `+0% / +10% / +0% / +20% / +0%`, her `stat_bonus [0,10,0,20,0]`; `Potential Lvl 2` is a single number | None |
| Mood | §1.1.6 (`:200-216`) | Five tiers `GREAT` / `GOOD` / `NORMAL` / `BAD` / `AWFUL`; training `+20/+10/0/−10/−20%` and pre-race `+4/+2/0/−2/−4%` — `the five tier strings and the ten percentages are the client's` | None (the ❌ at `:214` is historical) |
| Hint ladder | §1.1.4 (`:152-170`) | `Lv1 10%, Lv2 20%, Lv3 30%, Lv4 35%, Hint Lvl Max 40%`; `Lv Max` is a caption, not a level; not linear past Lv3 | None |
| Ranks | §2.2.2 (`:974-985`) | Per-stat set `G / F / E / D / C / B / A / S`; `S+` is the **overall** team rank, not a per-stat value; facility level `F/G → 1, D/E → 2, B/C → 3, A → 4, S → 5`; `Wit is the outlier at 1800 against 1300 elsewhere` | None |
| Career calendar | §1.2.6 (`:378`) | `12-month × Early/Late grid, 24 turns to a year`, three years to a standard run; it explicitly warns that deriving a turn from a monotone counter `is an assumption the game does not enforce`. **The reference states no 72-turn total** | None (tier B, client observation) |
| Ancestor circle | §1.5.4 (`:686-698`) | `two parents plus four grandparents`; the per-finished-Umamusume spark count is `❌ UNVERIFIED`, with the shape bounded to `1 Blue, 1 Pink, at most 1 Green, unbounded White` | **❌ live at `:692`** |
| Team Trials | §1.6.4 (`:773-790`) | `five races` in Team Trials. **§2.2.2 carries no five-race finals programme**; the nearest five-race statements are §1.6.4 `:775` and §1.6.2 `:761` (Champions Meeting entry) | None |
| Conflict log | §7 (`:2560-2600`) | Rows touching the subjects above, with each row's own status word | Per row |
| Conflict/coverage appendix | §8.2, §8.4, §8.5 | **§8.5 is stale and is not quoted as a current gate result** | — |

Two rules follow from the table. Where the reference flags a value `❌ UNVERIFIED`, the register
cites the flag and does not upgrade the walk's observation into a fact claim. Where the
reference is silent — `Pal slot` as a label, a 72-turn total, a five-race finals fixture — the
register says the document is silent rather than borrowing an adjacent section's authority.

## 4. Reconciliation table

Row IDs are `R6-01..R6-54` in walk order: `F-01..F-17` → `R6-01..R6-17`, `E-01..E-14` →
`R6-18..R6-31`, `U-01..U-23` → `R6-32..R6-54`.

### 4.1 Functional rows

| R6 | Walk | Surface | Status | Settling read (file:line at `3f295e80`) | Facets · regression |
| --- | --- | --- | --- | --- | --- |
| R6-01 | F-01 | Build target | Does not reproduce | `app/Http/Requests/StoreBuildTargetRequest.php:164-179` `isEmptyTarget()` returns false as soon as any of `purpose`/`distance`/`surface`/`style` is `filled()`; `:71` then makes all nine keys `required`. `tests/Feature/CareerBuildTargetTest.php:120-142` pins the all-empty save as valid (`assertSessionHasNoErrors`, `buildTarget()` null). Walk S05 had **Distance = Medium** set, so the branch the finding describes was never submitted | Facets: **(a)** the all-empty branch works and is tested — DNR; **(b)** residual, new: no control clears a chosen distance radio (`BuildTarget.vue:571` binds `form.distance`, seeded `''` at `:136`), so the copy's escape is one-way once a radio is chosen — named as a new copy finding, not as F-01. Regression: **no** — `be02297` touched only this request and its test, and `isEmptyTarget()` is byte-identical since |
| R6-02 | F-02 | Trainee picker | Confirmed defect | Picker: `app/Http/Controllers/Career/TraineeSelectController.php:457-466` adds `->whereNotNull('global_release_date')`. Profile: `TraineeProfileController.php:275-283` applies the same scope **without** it. Live row for umamusume 30: `170 Yummy Dreamy Fairy` has `global_release_date = NULL` | Facets: picker filters a real form the profile offers; nothing on either screen says so. Regression: **unproven** |
| R6-03 | F-03 | Trainee picker | Confirmed defect | `TraineeSelectController.php:196-198` redirects with `$this->filterQuery($request)`, and `:211-214` reads `$request->only(['search','sortBy','direction'])`. The page PUTs to the bare route with no query (`TraineeSelect.vue:357`), so the server-side read is empty. `filterQuery()` also omits `surface`/`distance`/`style`, which the page's GET does send (`:251-254`) | Facets: the R4-23 server fix (`3cdc466c`) is unreachable from the browser; the three aptitude facets are dropped even when a query is supplied. Regression: **unproven** — the fix never had a client sender |
| R6-04 | F-04 | Build target / Cockpit | Confirmed defect | Helper `BuildTarget.vue:446` prints `entered(stat) ?? '-'` over `cap(stat)`, and `cap()` at `:219` is `props.caps[stat]`, fed by `BuildTargetController.php:74` `ScenarioCaps::forRun($planning)` — the **run** caps, not the scenario's. The total row `:525-526` labels that sum `Sum of the five scenario caps`. The prop that would be correct, `scenarioCaps` (`:69`, sent at `:78`), is declared and never read. Cockpit caption `CareerStatePanel.vue:164` | Facets: **(a)** the total's label names a source it stopped reading — after a save it prints 7123 over entered ceilings; **(b)** the Cockpit asserts `Bars are drawn against the scenario ceiling` over bars bounded by the entered one (`CockpitController.php:682,693`). Regression: **unproven** |
| R6-05 | F-05 | Legacy step → Preflight → Inheritance | Confirmed defect | Rule accepts the name (`StoreLegacySelectionRequest.php:100`), and the rebuilt record at `:335-347` carries `rank`, `rank_letter`, `is_guest`, `ancestors`, `ancestors_sparks`, `ancestors_rank_letters`, `ancestors_rank`, `sparks`, `pick` — **no `name`**. `LegacySelectController.php:240-245` stores `$request->payload()` into `SetupDraft`, so the name is gone before Preflight exists. Every reader already supports it: `Legacy/LegacySelectionPayload.php:189-193,240-244`, `PreflightController.php:293-308`, `AncestryGraph.php:189-200`, `InheritanceEventController.php:162-199` | Facets: **(a)** manual parent name discarded on every write; **(b)** cold-load manual mode also tests `legacy.legacy_id === null` (`LegacySelect.vue:130-148`) while stored payloads omit `legacy_id`, so the picker cannot re-enter manual mode even once (a) is fixed. Blocking in the walk. Regression: **no** — `9c6bbcaa` added the rule and the readers, never the copy |
| R6-06 | F-06 | Legacy step | Does not reproduce | Table row `LegacySelect.vue:776`: `+5, +12 or +21 to the stat it names, for one, two or three stars` — a stat award. Paragraph `:861-863`: `A Blue Spark that rolls three stars lifts the cap of the stat it names by 16` — a cap raise. Both are sourced: §1.5.2 `:655` and §1.3.4 `:420`. The component's own comment at `:765-772` already records the distinction | Facets: **(a)** the two numbers are two mechanics — DNR; **(b)** residual copy-clarity: both rows name the same three inheritance moments, so they read as one mechanism at a glance; a label change only, not a number change. Regression: no |
| R6-07 | F-07 | Legacy / Training / Team | Confirmed defect | Seven inputs, each `type="number"` with `min` and no `max`: `LegacySelect.vue:485-491` (`Rank (stars)`), `AncestorEditor.vue:139-144` (`Grandparent rank (stars)`), `TrainingDetail.vue:698` (`FANS`), `:702` (`SKILL POINTS`), `UnityCupEditor.vue:148-154` (`Unity Trainings`), `:158-164` (`Spirit Bursts`), `:168-174` (`Extreme Bursts`). The spark `Stars` input proves the pattern is available in the same component family: `LegacySelect.vue:538-545` `min="1" max="3"`, twin at `AncestorEditor.vue:178-185` | Facets: the walk's register lists seven (`:1301`) and its closing assessment says six (`:1482`) — **it is seven**; §6, C8. Regression: **unproven** |
| R6-08 | F-08 | Cockpit / Inheritance | Confirmed defect | Cockpit note is a literal assembled in the controller: `CockpitController.php:629-633` `the six blue sparks account for the Guts extra and nothing else`. It renders beside a **derived** list, `Cockpit.vue:609-617` `capSparkText` over `props.capComposition.sparks` (`CockpitController.php:594-627`, which collects parent **and** grandparent sparks). The Inheritance tile is derived from a narrower scope: `InheritanceEvent.vue:413` over `:145-149`, parents only | Facets: **(a)** a literal describing the source document's tree, not this run's; **(b)** the two counters disagree because one includes grandparent sparks and the other does not. Regression: **unproven** |
| R6-09 | F-09 | Preflight | Confirmed defect | `Preflight.vue:499-503` renders `as chosen above` whenever `props.contract.build.trainee` is present. The contract's trainee is `{id, name, name_ja}` (`PreflightController.php:73-75`), and the controller never reads `character_card_id` (one match, a docblock mention at `:50`) | Facets: a static string with no prop it could resolve from, on a screen whose copy claims it re-asks nothing. Regression: no |
| R6-10 | F-10 | Preflight | Confirmed defect | `Preflight.vue:419-425` returns `Pass` for every entered row that is not `deck`; `entered` for legacy is `props.contract.build.legacy !== null` (`:302`), and the controller returns a non-null section whenever the draft holds a `legacy_selection` (`PreflightController.php:279-284`), names or not. The same row's summary prints `${named} of two parents recorded` (`:253-264`) | Facets: the state word ignores the count its own summary prints; same root as R6-05 but a distinct renderer, so it is fixed and asserted separately. Regression: no |
| R6-11 | F-11 | Deck step → Preflight → run deck | Confirmed defect | Draft keeps it: `DeckSelectController.php:87`, `SetupDraft.php:212`, `StoreDraftDeckRequest.php:85-87`; `DeckSlot.php:33` is fillable and `:76-80` casts it. Preflight creation writes three keys only — `PreflightController.php:143-149` `support_card_id`, `slot_position`, `ownership`; `grep -c card_level` on that file returns 0. `StartCareerRequest.php:98-101` has no `deck.*.card_level` rule and `:182-198` no level accessor. The run-scoped path does persist it: `TrainingRunController.php:1255-1269` | Facets: **(a)** wizard commit drops every level; **(b)** the child re-run validation cannot reject a stale level because it never sees one. Blocking. Regression: **no** — `df624cca` stored and `8acd26c8` bounded the level, neither touched Preflight creation |
| R6-12 | F-12 | Timeline | Confirmed defect | Server: `TimelineController.php:237-238` emits `before` and `actual`; `:355-366` `stats()` returns raw ints including `'energy' => $turn->energy` and no band text; `grep -n "'after'"` → 0. Client: `Timeline.vue:32` declares `after`, `:117-118` reads `entry.after?.energy` and `entry.after?.sp`, `:215-216` renders both with an `N/A` fallback; `entry.actual` is read zero times | Facets: **(a)** property mismatch — SP 173 prints `N/A`; **(b)** no band projection — Energy prints `N/A` even after (a). `TurnEntry::energyReading()` (`:168-184`) already resolves `band` / `exact` / `none` and the Cockpit calls it (`CockpitController.php:701`); the Timeline does not. Regression: **unproven** — `3a1ba9b` covered cockpit and events, and this file was never in its patch |
| R6-13 | F-13 | Wizard → run position | Known limitation | The wizard writes no position: `StartCareerRequest.php:56-70` merges trainee, card, scenario, status, build target, caps, legacy and deck, and `grep -n career_position` there returns nothing. Default `TrainingRun.php:116` `'mode' => 'new_career'`; first turn `:675-689` returns 1. Length is `CareerCalendar.php:30` (24 × 3) and is printed as a literal `CAREER_TURNS = 72` at `Preflight.vue:428`. The 69 is `TeamPanelBuilder.php:726-727` `payoutTurn = 70` minus the current turn | Facets: **(a)** the wizard path is a new-career door by construction; **(b)** the downstream consequence the prior registers never traced — races refuse (`RaceStripBuilder.php:170`), goals and rounds key on `nextTurnToPlay()`; **(c)** a snapshot door exists off this path (`routes/web.php:108-115`, `TrainingRun.php:739-748` honours a stored position), so the cap is reachability, not capability. Regression: no |
| R6-14 | F-14 | Cockpit obligations | Known limitation | `CockpitController.php:873-876` renders `$slot->title` verbatim; the titles are seeded rows (`race_catalog_slots` 405, 406, 408). The only scenario conditional in the builder is the whole-panel gate at `:855`; no scenario-conditional label exists. `KI-122` is **CLOSED — does not reproduce** (`KNOWN-ISSUES.md:3476`) and refutes only the proposal to filter the shared rounds | Facets: **(a)** the observation reproduces — a Unity Cup screen does read `URA Finals Qualifier`; **(b)** the proposed fix is refuted — `KI-122` shows the shared rounds are legitimate and that removing them would delete valid obligations; **(c)** no ruling authorises a rename. Regression: no |
| R6-15 | F-15 | Skills planner | Confirmed defect | `SkillsPlanner.vue:590` prints `{{ 'Hint Lvl ' + step.label }}`, and `HintDiscount.php:25-31,41` already returns the full caption (`'label' => $labels[$key]`, `Hint Lvl 1` … `Hint Lvl Max`). The rendered cell is therefore `Hint Lvl Hint Lvl 1`. The sibling list at `SkillsPlanner.vue:819` prints `{{ row.label }}` alone and is correct; `priceLadder()` (`HintDiscount.php:60-66`) returns the short `Lv1…Lv Max` form the prefix was written for | Facets: all five rows of the `Client caption` column; one-line fix at `:590`. Regression: **unproven** |
| R6-16 | F-16 | Trainee picker → Run settings | Confirmed defect | Promise: `TraineeSelect.vue:605` `The form you pick sets this run's rarity and potential.` Payload: `:179-182` sends `umamusume_id` and `character_card_id` only, accepted by `StoreTraineeRequest.php:42,50,57`; `SetupDraft` holds no rarity or potential key, and `StartCareerRequest` neither validates nor writes them. The fields exist post-commit: `RunSettings.vue:265-281` (`trainee_rarity`, `potential_level`), posting `runs.update` | Facets: **(a)** the sentence attributes a write that does not happen; **(b)** the fields are enterable, off the wizard path (§1.3.5 `:447` documents both). Regression: **unproven** |
| R6-17 | F-17 | Scenario step / Run settings | Confirmed defect | Copy `ScenarioSelect.vue:155-156`: `It cannot be changed after the first turn.` Control `RunSettings.vue:221-233` PUTs to `props.run.update_url` = `runs.update` (`routes/web.php:218`). Server accepts `'scenario' => ['nullable','string',Rule::in(...)]` with no turn or post-start condition (`StoreTrainingRunRequest.php:56`) and applies it (`TrainingRunController.php:761-765`); the model's `booted()` guard (`:135-150`) watches grade period and shop resets only | Facets: the stated invariant is unimplemented copy; the change route is reachable on a started run. Regression: no |

### 4.2 Experiential rows

| R6 | Walk | Surface | Status | Settling read (file:line at `3f295e80`) | Facets · regression |
| --- | --- | --- | --- | --- | --- |
| R6-18 | E-01 | Build target | Confirmed defect | No asterisk, no `required`, no `aria-required` in the file: `grep -n "aria-required\|required="` returns only comment text (`:140,357,361`). Stat inputs carry `:min="0" :max="cap(stat)"` (`:423-436`) and selects `:379-403` carry none. The forward control `:304-305` is disabled on `form.processing` only | Facets: requiredness is discoverable only after a refused submit. Regression: **no** — R4-04/R5-17 were about a different control family |
| R6-19 | E-02 | Trainee picker | Confirmed defect | `TraineeSelect.vue:456-457` pluralises the noun and leaves the verb: `{{ total }} trainee{{ total === 1 ? '' : 's' }} are catalogued.` The denominator line at `:579-580` is correct | Facets: `1 trainee are catalogued.`; one-line fix. Regression: no |
| R6-20 | E-03 | Trainee picker / profile | Confirmed defect | Picker: `TraineeSelect.vue:497-507` a `BaseButton` with `next: 'target'` (`:128`), landing on `career.target` (`TraineeSelectController.php:191-193`). Profile: `TraineeProfile.vue:266-274` a link that opens step 2's profile from the picker. One label, two control types, two destinations one step apart | Facets: the divergence is recorded in the file as an `HFW-OVERRIDE` (`TraineeSelect.vue:472-476`) rather than resolved. Regression: no |
| R6-21 | E-04 | Trainee picker / profile | Confirmed defect | Picker labels come from the controller: `TraineeSelectController.php:67-69` `'Front runner'`, `'Pace chaser'`, `'Late surger'`, rendered `TraineeSelect.vue:779`. Profile restates its own list: `TraineeProfile.vue:186-188` `'Front Runner'`, `'Pace Chaser'`, `'Late Surger'`. The shared vocabulary owner is `lang/en/uma.php:109-111` | Facets: two owners for one vocabulary; the picker's casing is the drifted side. Regression: no |
| R6-22 | E-05 | Trainee profile | Confirmed defect | Heading `TraineeProfile.vue:445` `Scenarios this trainee is linked to`; sub-line `:447-449`; the withdrawing note `:455-461` `No per-trainee link list is carried on this payload, so the scenarios are shown as available rather than as links.` The pills are `'scenarioLinks' => $this->scenarioLabels()` — the config's set, for every trainee | Facets: the heading names a per-trainee relation the payload does not carry; the note says so on the same screen. Regression: no |
| R6-23 | E-06 | Deck step / run deck | Confirmed defect | Label `SupportSlot.vue:135-137` prints `Pal slot` when `is_friend`. The server's own position label is `DeckSlot.php:73` `Slot 6 · Friends`. The card-type table on the same page lists `friend` as `Pal` (`SupportCard.php:127`, rendered `DeckSelect.vue:634-639`), and `:469` says `Position six is the one the client labels Friends.` The component is shared: `DeckSelect.vue:40` and `Support/Builder.vue:7` | Facets: settled by §1.4.1 `:477` (Pal is a card type) and by `ADR-0014:72-74`, quoted in §6 C6 — the ADR ratifies `Friends`, never the word `Pal`; the label was swapped in `0c3fc35c` with no comment and no ruling. Regression: **no** — R5-16 closed on the *slot-naming* question, a different claim |
| R6-24 | E-07 | Deck step / Dashboard | Confirmed defect | Picker `DeckSelectController.php:229-236` filters `whereNotNull('release_global')` (plus already-drafted ids) and `:284` returns `$query->get()` — no pagination, one scroll container at `CardPicker.vue:224`. Measured: `select count(*) from support_cards where release_global is not null` = **251**; `count(*)` = **559**. Dashboard `DashboardController.php:107` `SupportCard::count()`, labelled `Support cards` (`Dashboard.vue:523-525`) | Facets: **(a)** one figure is Global-released, the other is the whole catalogue; **(b)** nothing on either screen reconciles them; **(c)** the list is unpaginated. Regression: **no** — the same two numbers were read in R4-21 and R5-16, so recurrence, not regression |
| R6-25 | E-08 | Deck step | Confirmed defect | Defined in the controller, not config and not Vue: `DeckSelectController.php:129-130` `'unity_cup' => ['Wit cap is 1800. Consider a Wit-heavy deck.']`, passed at `:99`, rendered `DeckSelect.vue:441-445`. Keyed on the scenario alone — no deck read, no dismiss control. `config/scenarios.php` has no hints entry for the scenario | Facets: the hint cannot see the deck it comments on; the advice contradicts this run's deck (source `:115`); not dismissible. Regression: **unproven** — introduced `5e740816`, after the audit tree |
| R6-26 | E-09 | Cockpit | Known limitation | `Cockpit.vue:1032-1039` is a link to `props.run.confirm_delete_url` (`CockpitController.php:388`), route `runs.confirm-delete` (`routes/web.php:224-225`) — a GET door. Actual deletion is `DELETE /training-runs/{run}` (`:219`) and refuses without the flag (`TrainingRunController.php:894-900`); `ConfirmDelete.vue:86-112` requires a checkbox before the button enables | Facets: **(a)** placement reproduces — a destructive door in the cockpit; **(b)** "one click" does not — a confirmation page and a checkbox stand between; **(c)** the walk's `E-10` pairs this with a contrary sentence, which is R6-27. Regression: no |
| R6-27 | E-10 | Preflight / Cockpit | Confirmed defect | `Preflight.vue:783` `I understand this creates an Active run that cannot be un-created; retirement is the only exit.` The cockpit offers a delete door (R6-26's read). The walk located the delete control on Run settings; measured, `grep -ciE 'delete|destroy' RunSettings.vue` returns 0 and its only nav control is `Back to cockpit` (`:183`) | Facets: **(a)** the two sentences cannot both be true — copy defect; **(b)** the walk's surface attribution is corrected to the cockpit (§6, C8). Regression: no |
| R6-28 | E-11 | Training turn form | Confirmed defect | Four `<h2>` — `TrainingDetail.vue:573` Disciplines, `:626` Other actions, `:659` Why this action, `:680` Recorded outcome — plus two expanded write regions `:740-741` `Client totals this turn (required to record)` and `:828-831` Preview readings. Six asterisks (`:748` Turn, `:752` the five stat fields) across 28 field widgets; the six match the six `required` attributes (`:749,761`) exactly | Facets: **(a)** four concerns on one page; **(b)** the walk's `~20 inputs` understates it — 28 widgets, §6 C8. Regression: **no** — R4-26 and R5-14 touched the disclosure default, not the structure |
| R6-29 | E-12 | Team editor | Does not reproduce | `UnityCupEditor.vue:217` `<p v-if="form.recentlySuccessful">Saved.</p>`, introduced `d8fb2345` (2026-10-11). Sibling forms with the same pattern and `role="status"`: `RunSettings.vue:214,235,256,342`, `TurnEdit.vue:209`, `Support/Builder.vue:350` | Facets: **(a)** a confirmation does exist — the claim "no save confirmation of any kind" is refuted; **(b)** residual, real: no `role="status"`, and the component renders no `form.errors` at all, so a refusal is silent; Inertia clears the flag after 2 s, which is the probable reason the second-pass probe missed it. Regression: no |
| R6-30 | E-13 | Inheritance | Confirmed defect | `InheritanceEvent.vue:399-408` heads a `Before / Change / After` table `Result of this pick`, and every cell reads `N/A` with `· this tool computes no inheritance change` and a `title` carrying the same disclaimer. `Change` is a literal; `after` reprints `row.value` | Facets: **(a)** the disclaimer is honest; **(b)** the scaffold promises an output the tool declines to produce; the fix is the heading or the columns, not the numbers. Regression: no |
| R6-31 | E-14 | Legacy step | Known limitation | `LegacySelect.vue:508-561`: each spark row is four fields — `Kind` (`:519-522`), `Category` (`:524-529`), `Target` (`:531-536`), `Stars` (`:537-543`) — plus `Remove Spark` (`:545-552`). `Add Spark` is at `:555-560` inside a collapsed `Sparks for this parent` summary (`:508-510`), so two clicks the first time and one after. The four grandparent nodes carry the same shape (`AncestorEditor.vue:88-95,148-208`) | Facets: **(a)** the workflow cost reproduces; **(b)** the walk's `6-field` count is wrong — four fields, §6 C8. No bulk path and no cost disclosure anywhere on the screen. Regression: no |

### 4.3 Unrepresented rows

| R6 | Walk | Datum | Status | Settling read (file:line at `3f295e80`) | Facets · regression |
| --- | --- | --- | --- | --- | --- |
| R6-32 | U-01 | Full scenario label `Unity Cup: Shine On, Team Spirit!` | Unrepresented source data | `config/scenarios.php:376-378` carries `'label' => 'Unity Cup'` and no other name key. `grep -rn "Shine On, Team Spirit" config app database` → 0 hits. The export **does** carry it: `docs/research-scratch-source/data/json/scenarios.json` `"name_en_full":"Unity Cup: Shine On, Team Spirit!"`; `GametoraScenarioParser.php:54` reads `name_en` then `name_en_old` only, and `create_scenarios_table.php:20-21` is `name` + `name_ja` | Facets: **a mapping gap, not a source gap** — the datum is already fetched and dropped. Corpus copies for corroboration: §2.2.2 context, source `:36`, `docs/scenarios/02-unity-cup.md:28`. Regression: no |
| R6-33 | U-02 | Snapshot point `Senior Year Early Oct` | Known limitation | `career_position` + `career_position_source` (`2026_10_09_153000:30-31`); `CreateSnapshotRun.php:78-79` sets `'imported'`; `TrainingRun.php:792-795` `hasImportedPosition()` is `career_position !== null`; read at `CockpitController.php:276-277`, `TimelineController.php:89-102`; the snapshot screen asks Year/Month/Half (`Snapshot/Setup.vue:210-236`) | Facets: **field exists, off the wizard path**; §1.2.6 `:378` settles that the client's position is a calendar label the tool derives, so the wizard's turn-1 default is a UI architecture boundary, not missing source data. Regression: no |
| R6-34 | U-03 | `Turns remaining = 5` | Unrepresented source data | `CareerPosition.php:49` carries `scenarioCountdown`, serialized `:147`, deserialized `:116`; read `CockpitController.php:277`. No rule in `StoreSnapshotRequest.php:44-65`; no input in `Snapshot/Setup.vue` (0 hits for `countdown`); `CreateSnapshotRun.php:67-73` omits it, so the only snapshot writer always stores null. Client side: `Cockpit.vue:168` declares `scenarioCountdown` and never reads it; the one renderer `CockpitIdentityCard.vue:157-160` is imported nowhere | Facets: **plumbed end to end, settable by nothing, renderable by nothing** — a dead pipe, not a missing feature. Source value: `5 turns left, 5 turns until the Unity Cup` (source `:38`). Regression: no |
| R6-35 | U-04 | `Potential Lvl 2` | Known limitation | Column `2026_10_10_120000:26-27`; read `CockpitController.php:557` → `Cockpit.vue:380-382`; enterable `RunSettings.vue:272-281`. Wizard: 0 occurrences in `StartCareerRequest.php` | Facets: **field exists, off-path**; §1.3.5 `:447` documents the value as a single costume-varying number. Source: `★3, Potential Lvl 2` (`:37`). Regression: no — R4-11 established the field, R5-27 recorded the restore did not happen |
| R6-36 | U-05 | Rarity `★3` | Known limitation | Column `2026_10_10_120000:26`; read `CockpitController.php:556` → `Cockpit.vue:376-378`; `TrainingRunController.php:133`; enterable `RunSettings.vue:265-271`; wizard writes none | Facets: **field exists, off-path**; pair of R6-16's second facet. Regression: no |
| R6-37 | U-06 | Growth rate row | Known limitation | Column `2026_10_10_130000:28`; read `CockpitController.php:559` → `CareerStatePanel.vue:207`; five `%` inputs `RunSettings.vue:289-299`; the run inherits them from the card at `StartCareerRequest.php:162-170` (`character_cards.growth_rates`, `2026_11_02_141639:15`) | Facets: **field exists and is pre-filled for a card-based run, off the wizard path**; §1.3.5 `:447,:449` documents the five-cell row and the explicit `+0%` cells. Source row `+0/+10/+0/+20/+0` (`:869-871`). Regression: no |
| R6-38 | U-07 | `Wit cap 1800` as an entered ceiling | Known limitation | `ScenarioCaps.php:36-41` `ENTERED_CAPS` is four stats; the screen prints the absence at `BuildTarget.vue:453-475` (`No enterable ceiling for this stat`). `BuildTargetPayload::KEYS` has no cap member. §2.2.2 `:971-972` and §1.3.4 `:418` both publish `1800` as the scenario cap | Facets: **no `cap_wit` column by decision** (`2026_10_12_140000:19`, "Four columns, not five"); the 1800 survives as the published cap. Regression: no |
| R6-39 | U-08 | Ancestors' epithets | Unrepresented source data | `Legacy/LegacySelectionPayload.php:48-52` allows `{id,name,costume}` on an ancestor, but `grep -c ancestors_meta StoreLegacySelectionRequest.php` returns **0** — no write path posts it. `AncestorEditor.vue:107-110` is a bare-name input, and `:0` matches for `costume` in that component | Facets: **stored shape exists, no writer**; the epithet is a string the client prints beside the name (source `:165-173`). Regression: no |
| R6-40 | U-09 | Per-card limit-break state | Known limitation | Barred by decision: `ADR-0029` D1 (`docs/adr/0029-per-run-measurement-columns.md:49`) — `card_level` `is not the cap, not the limit-break count, not the perk level`; `ADR-0014:8,199`; enforced by `DeckSlotCardLevelTest.php:38-40` `->not->toContain('card_cap')->not->toContain('limit_break')`. §1.4.2 `:503` gives `30 + 5 × breaks` | Facets: the level is a numerator only; break count is collection state the ADRs decline. Regression: no |
| R6-41 | U-10 | Catalogue-max vs reached effect values | Unrepresented source data | No reached-value column. Derived instead: `SupportCardEffects::atLevel()` interpolates from the card's anchors by `card_level` (`:164`), and `DeckAnalysis.php:106-113` tags `basis: 'catalog'\|'level'`. Deck payload shape at `TrainingRunController.php:534`; rendered `DeckAnalysis.vue:92` as `at Lv N` against `catalogue maximum` | Facets: **(a)** the reached value is computed, never recorded; **(b)** §1.4.7 `:562-594` documents interpolation and the client's own current-level reading; a value outside the card's anchors cannot be stored. Regression: no |
| R6-42 | U-11 | Client warning and quote strings | Unrepresented source data | `grep -rniI 'floor cleaning\|phew\|getting low' app resources config lang tests` → 0 hits. `grep -rniI 'warning' database/migrations/` → 0. No `warning` column on `turn_entries` or `training_runs`. Freeform surfaces that exist: `turn_events.origin_note` (`2026_09_27_093947:27`, max 1000, `StoreTurnEventRequest.php:45`), `training_runs.notes` (max 5000), `veterans.notes`, `turn_entries.condition` (max 255) | Facets: **(a)** no storage and no header prop (`grep -n warning CockpitController.php` → 0); **(b)** every header warning today is **derived** by a builder (`PreflightController.php:398-473`, `TeamPanelBuilder.php:746`), which AGENTS.md §5 forbids for observed client wording; **(c)** the R6-53 reader would create the only free-text run surface a transcribed warning could legally occupy, and that constraint is this row's, not R6-53's. Source strings: `:241-244`. Regression: no |
| R6-43 | U-12 | Fans 209,245 / class `Star` / 30,755 gap | Known limitation | Data intact: `turn_entries.fans` (`2026_09_27_093945:18`); `FanLadder.php:18` exists, is called at `CockpitController.php:562-563`, and ships as `identity.fan_ladder` (`CockpitController.php:283`). Display removed by an override: `CareerStatePanel.vue:263-268` prints the bare count and its own comment records the decision; the prop `fanLadder` (`:62`) has **no reader anywhere in `resources/js`** | Facets: **(a)** the observation reproduces — only the bare count prints; **(b)** the removal is an `HFW-OVERRIDE (text: fan-ladder)` whose first two sentences cite the wireframe corpus and whose third sentence parks a live prop for a caller that does not exist; **(c)** its stated rationale (`the ladder is a Dashboard reading`) is false — `Dashboard` references `FanLadder`/`fan_ladder` nowhere; **(d)** walk 6 recorded no fans value for this run, so nothing could be derived in that session. Source: `209,245`, Top Star `240,000`, `30,755` (`:38-39`); §1.6.2 `:751` and the fan ladders at §2.2.1/§2.2.4 `:951,:1131`. Regression: **unproven** — the removal is `530d62b9`, a deliberate override, not a lost fix |
| R6-44 | U-13 | Failure rate 39%, preview `+18/+8/+6` | Does not reproduce | Columns `2026_09_27_093945`-series `failure_rate` and `preview_gains` (`2026_10_09_180000:31-32`); rules `StoreTurnEntryRequest.php:114-121` (`0..100`; `array:speed,stamina,power,guts,wit,sp`); inputs `TrainingDetail.vue:836-858`; read back `CareerTimeline.vue:215-219`. Walk S10 records the fields present and empty | Facets: the fields exist and are enterable; the walk's sheet carried no values, which the walk itself records as un-entered (`:761-765`). Regression: no — `3b34fb0` landed before the audit tree and survived |
| R6-45 | U-14 | Five unrecorded ancestors' spark sets | Does not reproduce | `LegacySelectionPayload.php:305-341` stores `ancestors_sparks` as a list aligned by ancestor index, two per parent; `MAX_ANCESTORS = 2` per parent. All six reachable: `LegacySelect.vue:716-735` loops both parents and both ancestors, each `AncestorEditor.vue:88-95` `addSpark()`. Cockpit reads them at `CockpitController.php:612-625` | Facets: the capability reproduces; the walk equipped sparks for one branch, which the walk records as its own truncation. §1.5.4 `:688` fixes the six-node model; `:692` bounds the per-node count and stays ❌ UNVERIFIED. Regression: no |
| R6-46 | U-15 | Spark values beyond kind/category/target/stars | Unrepresented source data | `LegacySelectionPayload.php:63` `SPARK_KEYS = ['kind','target','stars']`, enforced by `assertKeys` (`:520`) and emitted by `spark()` (`:539`). Magnitude is derived, never stored: `BLUE_SPARK_BONUS = [1=>5, 2=>12, 3=>21]` (`:92`) | Facets: §1.5.2 `:655-660` publishes the payout per star; the client prints the value the spark rolled, and the schema keeps only the star. Source examples `Ignited Spirit: Guts + ★☆☆` (`:470-473`). Regression: no |
| R6-47 | U-16 | Team member roster 20/20, ACE board | Unrepresented source data | No roster table: no migration creates a roster or team-members table (0 hits). `CockpitController.php:647-666` derives names from `deck_slots`, which yields at most six; `TeamPanelBuilder.php:442-452` reads burst events. The screen says so at `:639` (`no stored roster and no column`) | Facets: **(a)** no storage for 20 members; **(b)** no ACE-per-distance field; **(c)** six names are derivable and are all the app can claim. Source: `20 / 20` and the 17-name roster (`:259-267`, `:300-350`). Regression: no |
| R6-48 | U-17 | Team Rank `S` and the `+30` attribution | Confirmed defect | The copy is false at HEAD: `UnityCupEditor.vue:203-207` says `Team Rank is recorded through a turn's team-race event, not through this editor.` No trainer form writes one — `StoreTurnEventRequest.php:34-44` has no `deltas` rule, and `EventDecisionController.php:66-76` creates events without it. `TeamRankPayload` is constructed **only in tests** (7 sites: `ScenarioPanelTest.php:505,527,541`, `UnityCupPanelTest.php:79,152,208`, `UnityCupSurfacesTest.php:140`) and normalised on save (`TurnEvent.php:183`) | Facets: **(a)** copy names a path no form provides — slice 3; **(b)** the model supports the payload and the reader works (`TurnEvent.php:116`, `TeamPanelBuilder.php:474`), so the gap is a form, `Known limitation`; **(c)** `+30` has no key at all — `TeamRankPayload::KEYS` is `['rank']` — `Unrepresented source data`; **(d)** §2.2.2 `:974-977` makes `S+` an overall rank only. Source: `Team Rank S (five stars)`, `+30` as the league-placement bonus (`:259-267`, `:283-285`). Regression: no |
| R6-49 | U-18 | Four preseason round rows | Known limitation | One counter only: `team_preseason_wins` (`2026_10_10_150000:31`), whose migration docblock says `the round itself is not modelled here, only the tally` (`:15-16`); rule `StoreTrainingRunRequest.php:89` (`0..4`); field `UnityCupEditor.vue:135-144`. No rounds table | Facets: the tally is enterable; the four individual rounds are not. Source: `Round 1 after Junior Late Dec … Round 4 after Senior Late Jun` (`:199-201`). Regression: no |
| R6-50 | U-19 | Finals as a five-race fixture | Unrepresented source data | `race_catalog_slots` holds exactly one finale row for the scenario (id 408, `URA Finals Final (Aoharu)`); `FinaleReader.php:57-76` reads a state. No fixture model (0 migrations). `config/scenarios.php` declares no `finale` key for this scenario | Facets: **(a)** the fixture has no home; **(b)** §1.6.4 `:775` is the five-race statement, and **§2.2.2 carries none** — the brief's citation does not exist (§3). Source: the five legs (`:1038-1044`). Regression: no |
| R6-51 | U-20 | Three cleared, one active objective | Known limitation | `trainee_goals` (`2026_10_13_120000:38-50`) is catalogue data; state is computed `completed/current/upcoming/missed` at `TeamPanelBuilder.php:247-281`, and `:321-358` `objectiveMet()` reads `race_entries`; the cleared count prints at `:305-307`. No `objective_state` column (0 hits) | Facets: **(a)** progress is derived by design; **(b)** the wizard writes no race entries, so nothing can be met on this path — the same reachability limit as R6-13. Source: `Place 1st in Arima Kinen`, three cleared (`:203-205`). Regression: no |
| R6-52 | U-21 | Cap-vs-spark arithmetic gap | Source conflict / open | No field: `grep -rn 'capVariance\|sparkReconciliation\|cap_gap\|cap_vs_spark' app resources/js database/migrations` → 0. The gap is prose only, `CockpitController.php:628-633`. `ScenarioCaps::capRaise()` (`:112`) computes the +16 projection and no surface compares it to a recorded cap | Facets: **(a)** §1.3.4 `:418` and §1.5.2 `:655` document each layer; **(b)** the source records that its own numbers do not reconcile — `Speed +25 is not a sum of 9s, Stamina +22 is not a sum of 16s, and Power +68 needs four activations` — and leaves the residual cap layer open (`:60-72`, `:821-823`). The tool cannot represent a reconciliation the corpus has not established. Regression: no |
| R6-53 | U-22 | Notes / freeform field | Confirmed defect | The column exists: `2026_09_26_162817:21` `text('notes')->nullable()`. Writable from three screens — `StoreTrainingRunRequest.php:61` (`max:5000`) with `Runs/Create.vue:256-269`; `StoreSnapshotRequest.php:60` with `Snapshot/Setup.vue:297-306`; `ImportHistoricalRunRequest.php:199-200` with `Runs/Import.vue:543-553`. **Zero readers**: the only `$run->notes` reference in the tree is an assertion at `CreateSnapshotRunTest.php:123`; `Cockpit`, `Timeline`, `RunSettings` and `CareerStatePanel` return 0 hits. `PRD.md:243` requires free-text notes; `SCREEN_SPEC.md:252` already specifies the field | Facets: **(a)** no input on any of the 20 walked screens; **(b)** the field is write-only app-wide, which is the contract violation; **(c)** no ADR, SCREEN_SPEC row or PRD line keeps notes off the career path — the absence is undocumented, not ruled. Regression: **no** — the column predates every walk |
| R6-54 | U-23 | Deck cards 3-5 not equipped | Does not reproduce | Six slots always render (`DeckSelect.vue:486-499`); `addCard()` fills the next empty slot (`:212-214`); the page reports `3 of 6 slots filled` (`:464`). The observed run equipped positions 1, 2 and 6 | Facets: the walk classifies this as its own truncation (`:1352`), and the code agrees — a walk artefact, not a UI gap. Regression: no |

### 4.4 Status counts

| Status | Count | Rows |
| --- | --- | --- |
| Confirmed defect | 26 | R6-02, -03, -04, -05, -07, -08, -09, -10, -11, -12, -15, -16, -17, -18, -19, -20, -21, -22, -23, -24, -25, -27, -28, -30, -48, -53 |
| Known limitation | 13 | R6-13, -14, -26, -31, -33, -35, -36, -37, -38, -40, -43, -49, -51 |
| Unrepresented source data | 8 | R6-32, -34, -39, -41, -42, -46, -47, -50 |
| Does not reproduce | 6 | R6-01, -06, -29, -44, -45, -54 |
| Source conflict / open | 1 | R6-52 |
| Confirmed fixed | 0 | none — this pass repairs nothing |
| **Total** | **54** | |

## 5. Cross-register mapping

| R6 | Walk | Nearest prior row | Relationship at `3f295e80` |
| --- | --- | --- | --- |
| R6-01 | F-01 | R5-01, R5-02 (owner-gated), D4 (`be02297`) | D4's contract holds and is tested. R5-01/R5-02 remain open on the payload shape. R6-01 itself is DNR; its residual is new |
| R6-02 | F-02 | new | No prior row on the form count |
| R6-03 | F-03 | R4-23 (`3cdc466c`) | R4-23 fixed the server and tested it with a hand-built query string; no client sender exists. Recurrence with a new mechanism |
| R6-04 | F-04 | R5-01 area | New: the total's label and the Cockpit caption read run caps |
| R6-05 | F-05 | R2-02 (DNR), R5-05(b), R4-08 | **Not a regression.** `9c6bbcaa` added the rule and the readers and never copied the name; R5-05's `Confirmed fixed (S2)` cites a docs-only commit (§6, C1) |
| R6-06 | F-06 | new | §1.5.2 `:655` versus §1.3.4 `:420` |
| R6-07 | F-07 | R4-04, R5-17 (adjacent) | Adjacent a11y family, new instances |
| R6-08 | F-08 | R4-06 area, R6-52 | Literal versus two derived counters |
| R6-09 | F-09 | new | — |
| R6-10 | F-10 | R2-03, R4-04 | Same root as R6-05, distinct renderer |
| R6-11 | F-11 | `df624cca`, `8acd26c8`, `b40454b9`; R4-16 adjacent | **Not a regression.** Storage and bounds landed; Preflight creation never carried the key |
| R6-12 | F-12 | R3-01 (`3a1ba9b`), R5-15, R6-44 | `3a1ba9b` covered cockpit, career header and events; the Timeline was never in its patch, and it also reads a key the server does not send |
| R6-13 | F-13 | R2-01, R2-04, R3-11, R4-08, R5-19 | Known limitation with a consequence no prior register traced: races, goals and rounds are all unreachable on this path |
| R6-14 | F-14 | R4-06, R5-12, `KI-122` | `KI-122` CLOSED - does not reproduce; the observation stands, the rename does not |
| R6-15 | F-15 | R5-09 (adjacent copy) | New duplication |
| R6-16 | F-16 | R2-13, R4-11, R5-27 | Fields exist post-commit; the picker's sentence is the defect |
| R6-17 | F-17 | R2-05, R5-18 | New contradiction; the write path is live |
| R6-18 | E-01 | R4-04, R5-03, R5-17 | New instances in a different control family |
| R6-19 | E-02 | new | — |
| R6-20 | E-03 | R5-18 (DNR) | Reopened: one label, two control types, two destinations |
| R6-21 | E-04 | `lang/en/uma.php:109-111` owner | New drift between two renderers |
| R6-22 | E-05 | new | — |
| R6-23 | E-06 | R5-16 (DNR) | Reopened on the label, not the slot-naming question R5-16 settled; `ADR-0014` quoted in §6, C6 |
| R6-24 | E-07 | R4-21, R5-16 | Recurrence; the two numbers are measured, not inferred |
| R6-25 | E-08 | `5e740816` (post-audit-tree) | New |
| R6-26 | E-09 | R5-23 (adjacent) | Placement reproduces; the one-click claim does not |
| R6-27 | E-10 | R2-05 family | New copy contradiction; the walk's surface attribution corrected |
| R6-28 | E-11 | R4-26, R5-14 | Both prior rows touched the disclosure default; the structure is unchanged |
| R6-29 | E-12 | R3-04 (`3a1ba9b`), R5-19 | `Saved.` exists since `d8fb2345`; R5-19's DNR holds; the residual is a11y |
| R6-30 | E-13 | R2-05 family | Copy scaffold versus an honest disclaimer |
| R6-31 | E-14 | R3-10, R4-19, R5-06(b) | Known limitation, unchanged |
| R6-32 | U-01 | new | Mapping gap in the parser, not missing source data |
| R6-33 | U-02 | R2-01, R3-11, R4-08 | Field and snapshot door exist; off the wizard path |
| R6-34 | U-03 | new | Dead pipe: value object and prop, no writer and no renderer |
| R6-35 | U-04 | R2-13 (`8e51b9f`), R4-11, R5-27 | Field exists; R5-27's open half is the restore, which did not happen |
| R6-36 | U-05 | R4-11 pair | Field exists, off-path |
| R6-37 | U-06 | `110ffd3` (2B A6.3) | Field exists, inherited from the card |
| R6-38 | U-07 | R4-12 family (schema) | Four columns by decision |
| R6-39 | U-08 | new | Stored shape, no writer |
| R6-40 | U-09 | `df624cca`, `ADR-0029` D1, `ADR-0014` (Phase 14) | Barred by decision; the level is a numerator |
| R6-41 | U-10 | `ADR-0029` D1 second half | Derived, not recorded |
| R6-42 | U-11 | R5-29 (adjacent, race-day facts) | No storage, no header prop |
| R6-43 | U-12 | `e096137` (2B A6.5), R5-10, `530d62b9` | Service alive and called; display removed by a recorded override; **R5-10's `Known limitation` holds** and this row adds the dangling prop |
| R6-44 | U-13 | `3b34fb0` (2A.3), R5-30 | Fields exist; R5-30's restore is corroborated at HEAD |
| R6-45 | U-14 | R5-06, R5-31 | All six nodes reachable; the walk's truncation |
| R6-46 | U-15 | R3-02 (`a4b2b26`), R2-11 | Star stored, magnitude derived |
| R6-47 | U-16 | R5-33 (DNR on clipping) | No storage; R5-33's clip claim stays DNR |
| R6-48 | U-17 | R4-13, R5-26, R5-08(b) | **R4-13's `recordable through turn events` is contradicted** — no trainer writer exists — §6, C9 |
| R6-49 | U-18 | R5-12 knock-on | Tally only |
| R6-50 | U-19 | R4-10, R5-16 area | No fixture model |
| R6-51 | U-20 | R4-07, R5-32 | Derived from `race_entries`; write path absent |
| R6-52 | U-21 | R4-17, R5-26 area | Corpus-level open question |
| R6-53 | U-22 | new (PRD `:243` contract) | Write-only field |
| R6-54 | U-23 | R5-33 (adjacent) | Walk truncation |

## 6. Dated corrections

Each note below corrects a claim in an earlier register, in the walk, or in the round-6 brief.
No earlier file is edited. The original sentences stand where they are.

**C1 — 2026-11-06. Round 5's slice landing record cannot be read as a code landing.**
Round 5 records `All seven slices landed in a single commit at da6e6367`
(`rice-shower-unity-cup-round5-reconciliation.md:271`, and the tables at `:154-160`, `:275-281`).
Measured this pass: `da6e6367` changes **one** file,
`docs/proposals/2026-11-03-full-project-state.md` (+3/−2); `34fd3c9e` changes **one** file,
`docs/proposals/2026-11-03-completion-phase-0.md` (+10). Neither touches `app/`, `resources/`,
`tests/`, `database/`, `routes/` or `config/`. The code those slices describe is in `dcc562b6`
(2026-11-04, 164 files, 59 code commits follow `34fd3c9e`), which is also the commit that added
walk 5 and the round-5 register (`git log --diff-filter=A` on both paths). Three of round 5's
eleven `Confirmed fixed` rows describe things absent at HEAD: `card_level` is not written at
Preflight creation; the parent `name` is not copied into the payload; the Timeline has no band
projection. **The eleven rows are unverified as attributed and must not be inherited as
settled.** This note does not edit round 5.

**C2 — R6-06: `+21` and `16` are two mechanics, not a contradiction.** §1.5.2 `:655` gives
`+5, +12, or +21` as a **flat stat award at career start**; §1.3.4 `:420` gives `+16` as a
**cap raise per ★3 basic-ability factor**. The row prints the first and the paragraph prints
the second, each correctly. The walk inferred a contradiction from two correct labels.

**C3 — R6-14: `KI-122` refutes a filter, not the observation.** `KI-122` is CLOSED - does not
reproduce (`KNOWN-ISSUES.md:3476`) and its reasoning is that removing every `URA Finals` title
`would delete legitimate shared obligations`. The walk did not propose a filter; it reported
reading `URA Finals Qualifier` on a Unity Cup screen, which reproduces. The row is therefore
`Known limitation` with both facts recorded, not `Does not reproduce`.

**C4 — R6-01: the wholly-empty branch was never exercised.** Walk S05 had `Distance = Medium`
selected, and `isEmptyTarget()` counts any of the four metadata fields as content. The
all-empty save is valid and pinned by `CareerBuildTargetTest.php:120-142`. The unset branch
being "refused" is unproven; the residual that survives is that no control clears a chosen
radio.

**C5 — R6-12: two independent defects, one row.** The page reads `after`; the server sends
`actual` (SP silenced). `stats()` exposes numeric `energy` only, so the band has no
representation here even after the key is fixed (Energy silenced). A one-facet repair would
leave one cell at `N/A`, so both are named in the row and no test may assert only one.

**C6 — R6-23: `ADR-0014` ratifies `Friends`, and never mentions `Pal`.** `ADR-0014:72-74`:
`` `slot_position = 6` is the friend slot. The role belongs to the **slot**, never to the card:
the deck editor labels the sixth position "Friends" and any card may occupy it, so a stat card
parked there is a legal deck (`ADR-0005` correction 1, which stands). `` The word `Pal` occurs
nowhere in that ADR. `SupportSlot.vue:136` prints `Pal slot`, introduced by `0c3fc35c` as a
bare `Friend slot` → `Pal slot` swap with no comment and no ruling, while
`DeckSlot::positionLabel()` still returns `Slot 6 · Friends`. §1.4.1 `:477` makes Pal a card
type. So the ADR settles the slot's role and one label, and the label in the app is a
substitution of the type's word onto the position.

**C7 — R6-43: the fans override is recorded, and its own rationale is false at HEAD.**
`CareerStatePanel.vue:263-268` carries an `HFW-OVERRIDE (text: fan-ladder)` whose first two
sentences cite the cockpit wireframe corpus, and whose third says `` `fanLadder` stays a prop
so a caller that wants it can add it back ``. `props.fanLadder` has no reader in
`resources/js`, and the stated reason (`the ladder is a Dashboard reading`) does not hold:
`Dashboard` references neither `FanLadder` nor `fan_ladder`. The row records both the decision
and the dangling prop rather than reopening the display question.

**C8 — 2026-11-06. Arithmetic and surface corrections to the walk's own register.** These are
the walk's own numbers, wrong in its favour of a larger defect, and they are corrected here
rather than in the audit:

| Walk claim | Location | Measured at `3f295e80` |
| --- | --- | --- |
| "Six number inputs with no `max`" | `:1482` (the register at `:1301` says seven) | **Seven**; the seven are enumerated in R6-07 |
| "a 6-field … task" per spark row | `:1326` | **Four** fields plus a remove control; `LegacySelect.vue:519-552` |
| "`*` on only six of ~20 inputs" | `:1324` | Six asterisks, and they match the six `required` fields exactly, across **28** field widgets |
| Delete control "on the run settings screen" | `:1322` `E-10` | On the **cockpit** (`Cockpit.vue:1032-1039`); `RunSettings.vue` has 0 delete hits |
| "one click" deletion | `:1321` | A GET confirmation door plus a checkbox; the `DELETE` refuses without the flag |
| "No save confirmation of any kind" on the team editor | `:1324` | `Saved.` renders on `recentlySuccessful` (`UnityCupEditor.vue:217`); the residual is the missing `role="status"` and error surface |

**C9 — 2026-11-06. R4-13's "recordable through turn events" is contradicted at HEAD.**
R4-13 reads ``a Trainer records rank **through the turn's event**``, and R5-26 inherited the
claim as wayfinding. Measured: `StoreTurnEventRequest.php:34-44` validates no `deltas` key and
`EventDecisionController.php:66-76` creates the event without one; `TeamRankPayload` is
constructed at seven call sites, all under `tests/`, and normalised on save
(`TurnEvent.php:183`). The reader works (`TurnEvent.php:116`,
`TeamPanelBuilder.php:474`), so the payload is supported by the model and unreachable by a
Trainer. `UnityCupEditor.vue:203-207` states the opposite. R4-13 and R5-26 are not edited;
R6-48 records the copy defect and names the missing form as its `Known limitation` facet.

## 7. Verification-only closes

Items the brief expected to close without code. Each is settled by a read and carries its
outcome; none of them opens a slice.

| # | Item | Outcome | Settling read |
| --- | --- | --- | --- |
| 1 | Potential level, rarity, growth rate (R6-35/36/37) | Fields exist and are enterable off the wizard path; three separate rows, never merged | `RunSettings.vue:265-299`; `StartCareerRequest.php` 0 hits |
| 2 | Team Rank and `+30` (R6-48) | Rank payload supported, no trainer writer; `+30` has no field; copy is false | `TeamRankPayload.php:25`; `UnityCupEditor.vue:203-207`; §6 C9 |
| 3 | Fan class and gap (R6-43) | Service exists and is called; display removed by a recorded override; walk entered no fans value | `CockpitController.php:562-563`; `CareerStatePanel.vue:263-268`; §6 C7 |
| 4 | URA Finals obligations (R6-14) | `KI-122` closed as DNR; observation reproduces; no ruling authorises a rename | `KNOWN-ISSUES.md:3474-3487`; `CockpitController.php:873-876`; §6 C3 |
| 5 | Hint ladder modelled (P-13) | Correct per §1.1.4 `:152-170`; no finding beyond R6-15's duplicated prefix | `HintDiscount.php:25-31` |
| 6 | `Pal slot` versus `Pal` (R6-23) | Reopened as a label defect; `ADR-0014` quoted rather than paraphrased | §6 C6 |
| 7 | Trainee step `Stored choice` | Updates on every walk from 2 onward; no work | — |
| 8 | Spark entry restoration (R6-45's premise) | All six nodes reachable; the walk's truncation | `LegacySelect.vue:716-735` |
| 9 | Outcome no longer required | Optional on the plain form; R4-08's forced-outcome half stays closed | `StoreTurnEntryRequest.php:141-143` |
| 10 | Run id visible | Not printed on any screen; the URL is not a screen. R4-01 stays open | `CockpitController.php` run section carries no `id` prop read by the header |
| 11 | Notes on the walked path (R6-53) | Field exists, three writers, zero readers, no ruling | `2026_09_26_162817:21`; §4.3 |

## 8. Proposed slice order

Proposed only. No slice opens in this deliverable, and nothing here is an approval to code.

**Phase 1 — data loss (highest risk, each has a one-cause fix).**
1. **R6-05** — copy the validated `name` into the rebuilt legacy record; derive manual mode
   from the parent slot plus a non-empty payload name rather than `legacy_id`. Tests: a real
   wizard PUT, a cold GET, the Preflight JSON PUT, then the stored payload, the created run's
   parents and the Inheritance readback, indexed by slot and never by name string.
2. **R6-11** — add the `deck.*.card_level` rule, forward the key into the child deck array, add
   `cardLevelByPosition()`, write the level inside the existing transaction. Test: 35, 50, null
   and a rented slot, by position key.
3. **R6-12** — project the row's own `energyReading()` per turn and read `actual.sp`; delete the
   unused `after` declaration. Test: two turns with Low band, exact 0, Unknown-with-stale-number,
   SP 173, SP 0 and null, so a one-facet fix cannot pass.

**Phase 2 — silent divergence.**
4. **R6-03** — send the filter query from the page, or state that selecting resets it; include
   the three aptitude facets in `filterQuery()`.
5. **R6-04** — one source for the helper and the total label; correct the Cockpit caption.
6. **R6-02** — one query for the form list, or a sentence on the picker naming what it filters.
7. **R6-24** — reconcile the two counts in copy, or paginate the picker.

**Phase 3 — copy, one assertion each.** R6-15, R6-19, R6-09, R6-10, R6-20, R6-21, R6-22,
R6-27, R6-30, R6-23 (label), R6-48 (the sentence), R6-17 (whichever sentence is wrong), and
the two import doors named in §11.2 (`Confirmed defect (copy/IA)`; rule ID `IA-1` primary,
`IA-5` and `H1` secondary, per §11.4).

**Phase 4 — a11y and structure.** R6-07, R6-18, R6-25, R6-08, R6-28.

**Phase 5 — held, with the reason stated.** R6-13, R6-26, R6-31, R6-14, R6-43, R6-32, R6-34,
R6-39, R6-41, R6-42, R6-46, R6-47, R6-50, R6-51, R6-53, R6-52, R6-01's residual, and the
per-turn facility aggregate named in §11.2 (`Unrepresented source data`; the fix needs an owner
ruling on whether the run-level ladder should read the team-rank letter it reads today or the
per-turn columns, per §11.4). Each is a
schema change, a design project, a dead pipe to reconnect, or a question the corpus leaves
open; §11 of `AGENTS.md` sends any new column through an ADR, a digest line and a PRD
citation, and none of them has one.

## 9. Decisions this pass cannot make

| Row | Decision | Why it is not the agent's |
| --- | --- | --- |
| R6-13 | Wizard versus snapshot as the primary door for a captured run | A product decision about which path is normal; both exist today |
| R6-17 | Which sentence is wrong: the immutability claim or the change control | Either reading is coherent; only the owner can pick |
| R6-26 | Whether a destructive door belongs in the cockpit footer | Placement judgement against the cockpit wireframe corpus |
| R6-28 | Whether the training form is restructured into separate pages | Information architecture |
| R6-31 | Whether spark entry gets a bulk path | A design project |
| R6-43 | Whether the fans ladder returns to the cockpit tile | The override cites the wireframe corpus; reversing it needs that corpus's owner |
| R6-53 | Whether notes appear on the wizard or only in a run reader | The PRD requires the field, not its placement |
| R6-52 | Whether the tool represents a residual cap layer | The source leaves the arithmetic open; a field would encode an unresolved mechanism |
| R6-48 | Whether a rank form is built, or the sentence removed | The second is a copy fix; the first is a schema and form decision |

## 10. Fence findings

Out of scope for the 54 rows, and carrying **no** `R6-NN` ID, because numbering them would
break the 1:1 with `F-01..F-17` / `E-01..E-14` / `U-01..U-23`. Each belongs to whichever owner
opens the next cleanup pass.

| Finding | Evidence | Consequence |
| --- | --- | --- |
| `CockpitIdentityCard.vue` is dead code | 8,997 bytes; `grep -rn "from.*CockpitIdentityCard" resources/js/` returns nothing. Its only references are the deletion note at `Career/Cockpit.vue:358` and an assertion at `tests/Feature/CareerSectionNavTest.php:107-111` that the page still mentions it | The only renderer of the scenario countdown and of the fan class plus gap is unreachable; deleting it would retire two open questions by accident |
| Two declared-and-unread props | `Cockpit.vue:158` `careerPosition`, `:168` `scenarioCountdown`; `CareerStatePanel.vue:62` `fanLadder` | Type declarations that promise a renderer no template has; the same class as the `fanLadder` note in §6 C7 |
| A stale browser assertion | `tests/browser/career-cockpit.spec.ts:829` asserts `/30,755 to 240,000/`, text the renderer stopped printing at `530d62b9` | Pre-existing red, unrelated to any R6 row. `530d62b9`'s own message records that the suite still pins the stripped regions |
| `UnityCupEditor` renders no `form.errors` | `grep -n 'errors' resources/js/components/career/UnityCupEditor.vue` → 0 | A refused save is silent here, which is why R6-29's observation was possible even though the success path exists |
| `Saved.` without a live region | `UnityCupEditor.vue:217` has no `role="status"`, unlike `RunSettings.vue`, `TurnEdit.vue` and `Support/Builder.vue` | Assistive technology is not told the save succeeded |
| Trainee-picker filter labels name a family, not a letter | `TraineeSelectController.php:125-131` sends `facets` with `'label' => 'Surface'` / `'Distance'` / `'Running style'` over `letters => TraineeSearchRequest::LETTERS` (`:40`, `S…G`). The predicate is exact-letter equality per column group (`applyFacets()`, `:376-392`), not "at least this grade" | A Trainer looking for a Dirt trainee sees no `Dirt` option, and the letters' meaning is unstated on screen. §4.2's R6-21 records the same controller's style labels on the roster card; this is the filter bar, a separate vocabulary |
| `Preseason wins` ceiling is unexplained | `UnityCupEditor.vue:136` renders the counter with no cap copy; `2026_10_10_150000:31` sets `0..4`, which is this scenario's round count (source `:199-201`) | A run in another scenario has nowhere to go, and the screen does not say why 4 is the ceiling |
| `Extreme Bursts` shortens the reference vocabulary | `UnityCupEditor.vue:167` prints `Extreme Bursts`; §1.8's own field name is `Extreme Spirit Bursts`, and `config/scenarios.php:384` uses the long form | Copy drift against the corpus, not a schema gap: the column is `extreme_bursts_count` and the value persists |
| Scenario card date reads as a run date | `ScenarioSelect.vue:249-252` prints `Global · {{ card.live_on_global }}`; the value is the scenario's Global release date (`config/scenarios.php:377`), never a career date | On the walk's own date the card read `Global · 2025-11-06` and the label gives no clue which date it is |

## 11. Addendum, 2026-11-06: facets the landed rows do not cover

Append-only. Each item below names an existing `R6-NN` and a facet its landed row does not
carry, or a fact the walk recorded with no row at all. **No landed primary status changes**,
and no row is renumbered. The 1:1 with `F-01..F-17` / `E-01..E-14` / `U-01..U-23` is preserved:
items with a home are facets, and items without one are recorded here rather than as a
fifty-fifth row.

### 11.1 Facets to carry when a row's slice opens

**R6-05, facet (c) — the parent headings never react to typing.** Walk 6 records that both
blocks stayed `Parent A Not chosen` / `Parent B Not chosen` after the names were typed, while
the inputs visibly held the values (`walk-6:479-481`). The landed row's two facets are
pre-commit and post-commit; neither is *during typing*. Measured cause: the pill and heading
read `props.parentA` / `props.parentB` (`LegacySelect.vue:85-86`, `:300`), which are server
props built from the Veteran library (`LegacySelectController.php:158-159`), not from
`form.legacies[index].name`. So fixing facets (a) and (b) leaves this live: a typed manual name
produces no `Chosen` state until the step is saved and reloaded. The heading should read the
form when the slot is in manual mode, and the server only on a cold load.

**R6-08, facet (c) — `Projected start` never sees a grandparent spark.** Walk 6 verified it
live: after saving with a grandparent's `Stamina ★★★` recorded, all five rows still read `-`
under both parent columns (`walk-6:492-493`). Measured: the table's parent cells read
`form.legacies[0].sparks` and `form.legacies[1].sparks` only (`LegacySelect.vue:625-645`), and
`legacies[].sparks` is the parent's own set; the grandparent sets are `ancestors_sparks`, keyed
by ancestor index (`LegacySelectionPayload.php:305-341`). The section's own description scopes
it to *the two parents* (`LegacySelect.vue:601-606`), and §1.5.4 `:688` puts all six nodes in
the tree at the same moment, so the scope is a deliberate narrowing the screen never states.
Note the interaction: the `Projected` column is a named absence by `ADR-0020` §3 (no inheritance
outcome is computed), so the fix is the scope sentence and the grandparent columns, not a sum.

**R6-23, facet (b) — the `N/A` → `Owned` auto-fill is a different mechanism.** Walk 6 records
that the Ownership flag flips to `Owned` on equip, so a borrowed card silently reads as owned
until the second button is noticed (`walk-6:591-592`). Measured: `DeckSelect.vue:296-298` seeds
`const ownership = form.deck[filling.value]?.ownership || 'OWNED'` on equip. This is **not a
facet of R6-23's label question** — it is the mechanism round 4 already registered as **R4-20**
(`Confirmed defect (ergonomics)`, `rice-shower-unity-cup-round4-reconciliation.md:129`), which
proposed naming the default in the picker copy or pre-selecting `Owned` visually. Renaming the
slot to `Friends` would leave it live. Recorded here so R6-23's slice does not claim it; the fix
belongs to R4-20's still-open row.

**R6-51, facet (b) — the goal list opens on a Junior-year item.** Walk 6 records
`Participate in Junior Make Debut · Upcoming · Junior year, turn 12 · Record this race` on a
Senior-year run (`walk-6:1180`, `:1193`). Measured: `TeamPanelBuilder.php:210-218` orders by
`career_year`, `turn`, `source_order`, `source_race_choice` over every goal the trainee owns,
with no filter on the run's position, so the list always starts at the run's first career year
regardless of where the run is. Every row reads `Upcoming` because `objectiveMet()` reads
`race_entries` and this run has none (`:249-262`). The landed row names the derivation; this
facet names the ordering, which is a separate fix.

### 11.2 Two facts the walk recorded with no row

**Two import doors on the landing screen.** Walk 6's S01 friction records `Import an existing
career` → `/career/snapshot` and `Import a historical career` → `/training-runs/import`, whose
difference is not stated on that screen (`walk-6:137-139`, repeated at `:645`). Measured: both
exist at HEAD and from the same screen — the first as an inline `Link` in the Active Career empty
state (`Dashboard.vue:397-403`), the second from the quick-actions list
(`DashboardController.php:373`). **`Confirmed defect (copy/IA)`.** No `R6-NN` row names it
because walk 6 filed it as friction rather than as an `F-`/`E-`/`U-` finding, and this register
does not invent walk IDs it was not given. Had the walk numbered it, `E-15` would have been its
home; the next pass that re-reads walk 6 should decide whether to add it.

**Per-turn facility levels are stored and never aggregated.** Walk 6 records the five levels
entered per turn at S10 and observes at S19 that they `appear nowhere on this ladder. So the rule
is documented and disconnected` (`walk-6:1204-1205`, `:1206`; S08's absence sentence at `:626`).
Measured: the columns exist per turn (`2026_10_09_170000:32-36`), written through
`StoreTurnEntryRequest.php:97-101` (`min:1 max:5`) and read back only per turn
(`TimelineController.php:381-385`). The run-level ladder is a **different source entirely**:
`TrainingRun::facilityLevel()` (`:1346-1359`) maps a **team-rank letter** through
`config('scenarios.scenarios.<key>.team_rank_ladder')`, and `TeamPanelBuilder.php:509` feeds it
the rank — so no screen derives a current facility level from the recorded turn columns.
**Unrepresented source data (stored per-turn, no aggregate).** Two mechanisms share one word; the
row that would carry this does not exist, and a fix needs a decision about which one the ladder
should read.

### 11.3 Prior-resolution continuity

- **D2 (the blank trailing Spark row, `a4b2b26`)** is not re-verified by any walk since walk 2.
  Measured at HEAD: the request discards a blank row
  (`StoreLegacySelectionRequest.php`, `a4b2b26`'s 18-line change) and `LegacySelect.vue:558`
  pushes a prefilled row rather than an empty one (`kind: 'blue'`, `category: 'Stat'`,
  `stars: 1`). The fix appears intact, but no walk has exercised it since; whichever pass opens
  R6-05 should assert it rather than assume it.
- **D4's optional-target contract** holds and is asserted by
  `CareerBuildTargetTest.php:120-142`; R6-01's `Does not reproduce` rests on that plus
  `isEmptyTarget()` (`:164-179`). No slice currently exercises it end to end, so a RED-first
  assertion for D4 belongs in whichever slice next touches the target step, as its own test
  rather than as a rider on R6-01.
- **The resolutions doc's "what remains unsupported" list** is largely still accurate at HEAD.
  The items the walk-6 rows now cover: rarity (R6-36), potential (R6-35), growth rate (R6-37),
  limit breaks (R6-40), fan class and gap (R6-43), finals fixture (R6-50). Two it does not:
  **card levels** — R6-11 records the loss and `DeckSlot::card_level` the storage, but no row
  states what the field's reach is across the wizard, the run deck and the collection; and
  **grandparent letter ranks** — `AncestorEditor.vue:130-135` binds
  `ancestors_rank_letters[ancestorIndex]` and `LegacySelect.vue:153` preserves them on read, so
  the field works, and no row says so or says what it does not cover.

### 11.4 Framework rule IDs for the two §11.2 findings

Rule IDs only for the two facts in §11.2, because a framework change audit (M1) cannot cite a
row without one. **No rule ID is added to the 54 landed rows**: their classification stands, and
when a slice opens one, the slice's audit cites the rule, not the register.

| §11.2 finding | Rule ID | Framework file | Why this one |
| --- | --- | --- | --- |
| Two import doors, difference unstated | **`IA-1`** primary; `IA-5` and `H1` secondary | `information-architecture-principles-2026-10-21.md:142`; `:146`; `usability-heuristics-2026-10-21.md:214` | `IA-1` is "every screen is directly reachable and carries its own crumb and way back", which is the front-door question this finding asks. `IA-5` is the empty-state exemplar — relevant only because one of the two doors sits inside the Active Career empty state. `H1` is the status question: the screen never says what the second door is for. |
| Per-turn facility levels, no aggregate | **`GP-1`** primary; `GP-3` and `SHR-7` secondary | `gerhardt-powals-principles-2026-10-21.md:117`; `:119`; `shneiderman-golden-rules-2026-10-21.md:141` | `GP-1` is "the product computes a figure the user would otherwise compute" — the run-level facility level is exactly that figure. `GP-3` is the labelled-summation rule, which applies once five per-turn readings must become one run-level number. `SHR-7` ("the value a decision needs is on the decision screen", sourced from Shneiderman rule 8) is the cross-display half. |

**Two corrections to the rule IDs proposed for this section, both measured against the
framework files and recorded here rather than silently applied:**

1. **`SHR-8` does not exist, and the cause is an off-by-one in the derived-rule table, not a
   missing rule.** The prose carries all eight Shneiderman rules — rule 7 "Keep users in control"
   at `shneiderman-golden-rules-2026-10-21.md:83` and rule 8 "Reduce short-term memory load" at
   `:94` — but the derived table's six rows cite rules 1, 2, 3, 4, 5, 6 and then **`rule 8`**
   (`:141`). Rule 7 has no derived row at all. So the file defines only seven IDs for eight
   rules. **`SHR-7`'s text is rule 8's clause compressed to one screen** — "the value a decision
   needs is on the decision screen" is the within-screen form of "not from one display, used on
   another" — so the row is internally consistent with its own `rule 8` source cell. What is
   missing is rule 7's derived row, not rule 8's. (This corrects `89a4b60d`'s wording, which
   called the text rule 7's content.) `SHR-8` is cited five times in `_audit-prompt.md`
   (`:117`, `:165`, `:182`, `:405`, `:432`) and defined in no file. Two consequences for the
   slices: cite **`SHR-7`**, whose text does describe a value that must reach the screen where
   it is used, and quote rule 8's prose (`shneiderman-golden-rules-2026-10-21.md:98-103`) when
   the cross-display clause itself is the authority; do **not** cite `SHR-8`, because Phase 6
   gate 1 (`_audit-prompt.md:211`) drops any finding whose rule ID is not in the framework file.
   The corpus defect is recorded here, not fixed here.
2. **`IA-5`'s routing label is correct, and this register's earlier note was wrong.**
   `_audit-prompt.md:123` writes "IA 5 (front doors)" and cites `IA-1`. Read against the IA file
   that is coherent: **principle 5 is named Front doors**
   (`information-architecture-principles-2026-10-21.md:116`) and its derived rule is `IA-1`,
   whose own Source column reads `5` (`:142`). The label names the principle; the ID column
   names the rule. So `IA-1` is the right citation for the import-door finding and `IA-5`
   (the empty-state exemplar, `:146`, derived from principle 4) is secondary only for the
   empty-state placement. There is no defect in the prompt's IA row.

Two further notes on scope for the slice audits that follow this plan:

- **`RENDERED_EVIDENCE` is not `FALSE` on this host.** Measured 2026-11-06:
  `chromium.launch()` succeeds under the current sandbox and a page renders
  (`RENDERED_EVIDENCE=TRUE`). The plan's §6 and §8 tables assume `spawn EPERM`; that assumption
  is stale, so a slice audit must **re-probe** rather than inherit the FALSE default, and the
  unmeasured register should not be pre-filled with "no browser" rows that no longer hold.
- **The framework file set is `docs/framework-ui-ux/`, not the repository root.** The plan
  cites `_audit-prompt.md` and `design-framework-index-2026-10-21.md` bare; both live under
  `docs/framework-ui-ux/`, alongside the ten framework files.

### 11.5 Derived-rule sweep (2026-11-06)

The `SHR-7`/`SHR-8` off-by-one prompted a full mechanical sweep: every derived-rule table in
every framework file, comparing each row's **text** against its own **Source** column. Result:
**the Shneiderman table is the only file with a numbering defect.** The other nine are
internally consistent, and three of them have no Source column at all because their IDs are
their own numbering.

| File | Rows | Text-vs-Source check |
| --- | --- | --- |
| `shneiderman-golden-rules-2026-10-21.md:135-141` | `SHR-1..SHR-7` | **Defect.** Eight prose rules, seven derived rows. `SHR-7`'s cell reads `rule 8` and its text is that rule compressed; rule 7 has no row |
| `laws-of-ux-2026-10-21.md:164-173` | `UX-1..UX-10` | Consistent; no Source column |
| `uilaws-2026-10-21.md:94-100` | `UI-1..UI-7` | Consistent; no Source column |
| `gerhardt-powals-principles-2026-10-21.md:117-121` | `GP-1..GP-5` | Consistent: `GP-1→1`, `GP-2→2`, `GP-3→3`, `GP-4→9`, `GP-5→10` |
| `information-architecture-principles-2026-10-21.md:142-147` | `IA-1..IA-6` | Consistent: `1→5, 2→7, 3→6, 4→3, 5→4, 6→8` |
| `norman-interaction-principles-2026-10-21.md:143-148` | `NRM-1..NRM-6` | Consistent; named concepts, not numbers |
| `tognazzini-first-principles-2026-10-21.md:251-258` | `TOG-1..TOG-8` | Consistent; named concepts |
| `gestalt-principles-2026-10-21.md:175-180` | `GT-1..GT-6` | Consistent; named concepts |
| `wcag-2.2-accessibility-2026-10-21.md:205-212` | `WCAG-1..WCAG-8` | Consistent; success criteria |
| `usability-heuristics-2026-10-21.md` | `H1..H10` | No derived-rule table; the IDs are the section headings (`:26` onward) and the map table at `:214-223` |

Two consequences for the slices, both now settled:

- **`GP-2` has rule-file support but no routing-table row.** `_audit-prompt.md`'s routing table
  (`:104-131`) cites `GP-1` and `GP-3` only. A finding grounded in `GP-2` — "a derived figure
  states the rule that produced it and whether the rule is the client's or ours"
  (`gerhardt-powals:118`) — is legitimate and must be filed under `GP-2`, but it will not appear
  in the routing table. S3's plan cites `GP-2` for the band projection; that citation stands, and
  the routing gap is recorded rather than worked around.
- **No other phantom IDs.** `IA-7`, `IA-8`, `UI-8`, `UI-9` and `UI-10` are named in older
  records, but `grep` finds none of them in `_audit-prompt.md` at this tree, and the IA and UI
  files stop at `IA-6` and `UI-7`. The prompt's own `SHR-8` references are the only live
  phantom in it.

## 12. Definition of done

- [x] 54 `R6-NN` rows, 1:1 with `F-01..F-17`, `E-01..E-14`, `U-01..U-23`; each walk ID appears
      exactly once; no row merges another.
- [x] Every status is one of the six; no placeholder words in any status cell.
- [x] Every row carries a settling read with `file:line` at `3f295e80`.
- [x] Every game-mechanic claim cites a `UMAMUSUME_REFERENCE.md` section, and the three
      sections the brief named that do not exist are recorded as absent in §3.
- [x] Every run-specific value cites the source document by section.
- [x] Every correction to the walk, to a prior register or to the brief is a dated note in §6.
- [x] The cross-register mapping is complete and the two docs-only commits are recorded.
- [x] Fence findings are separated and unnumbered.
- [x] Gates run and not run are stated in §1, with the suite gate's output.
- [x] §11 records the ten additions the walk noted without a row: four facets bound to their
      existing `R6-NN`, two facts with no row, four fence items, and the prior-resolution
      continuity note. No landed status changed; the row count stays 54.
- [x] §11.4 carries rule IDs for §11.2's two findings, and the two framework-corpus defects it
      found (`SHR-8` undefined; `IA-5` mislabelled in the routing table) are recorded rather
      than worked around.
- [x] Section numbering is self-consistent: §11 is the addendum, §12 the definition of done,
      and the one surviving "§11 of `AGENTS.md`" reference (§8) points at that document, not
      at this one.
- [x] §11.5 records the derived-rule sweep across all ten framework files, so the slices know
      `SHR-7` is the only mis-numbered row and `GP-2` has no routing-table entry.

**Self-test, run before each commit:**

```
grep -cE '^\| R6-[0-9]{2} ' register → 54      (§4 only; §5's mapping table also matches the pattern)
grep -oE '^\| R6-[0-9]{2} ' §4 | sort -u | wc -l → 54
status-cell tally over §4 → 26 + 13 + 8 + 6 + 1 + 0 = 54
walk-ID coverage over §4 col 3 → 17 F + 14 E + 23 U, each exactly once
placeholder scan → 0
python tools/md_fence_check.py → 0 invalid closing fences
```


## 13. Revision, 2026-11-06: re-base on the re-read evidence

Append-only. This section carries the dated corrections a round-7 planning read produced
against a later state of the two reference documents. **The 54 rows in §4, their IDs, their
statuses and their counts are untouched**; R6-34, R6-13 and R6-14 gain named facets below
without changing their primary status. Nothing in §1–§12 is edited.

### 13.0 What changed underneath this register

Two documents were revised after §1–§12 were written. Measured this pass:

| Document | At §1's baseline | At this revision | Effect on the register |
| --- | --- | --- | --- |
| `docs/UMAMUSUME_REFERENCE.md` | 2,786 lines | **2,932** (+146) | Settles A3 (countdown vocabulary), reverses A5, widens A6, softens A7, strengthens A4 |
| `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` | 1,394 lines | **1,483** (+89), new §10 | Adds the capture-coverage constraint quoted in 13.9 |

The framework files under `docs/framework-ui-ux/`, the walk-6 audit and rounds 2–5 are
unchanged (Oct 21–Nov 3). HEAD is `67a48308`; the register's own reads at `3f295e80` stand,
and every new read below is at HEAD with the commit named.

### 13.1 A1 — the round 2–4 re-measurement is withdrawn as a task

The round-7 brief asked for rounds 2, 3 and 4's claimed fixes to be re-measured, because §6's
C1 proved round 5's landing record misattributed its seven slices to a docs-only commit. That
inference does not generalise. Measured:

| Register | Claimed commit | Subject | Code files changed |
| --- | --- | --- | --- |
| R2-01 / R2-04 | `65d7068` | snapshot door outside both career branches | 2 |
| R2 phases 4–5 | `0025f37` | the second career entry point | 9 |
| R2 cockpit position | `3b6f3cc` | read career position and countdown | 5 |
| R3-16 | `2470a4c` | count grandparent Sparks | 2 |
| R3-02 | `d7f41dc` | deck_slot query in step with the picker | 2 |
| R3-01/03/04 | `3a1ba9b` | correction save and recorded Energy band | 12 |

Every one is a code commit with real paths under `app/`, `resources/` or `tests/`, and every
fix survives at HEAD with its test: the snapshot door renders at `Dashboard.vue:397-403`,
`hasImportedPosition()` still gates the cockpit (`CockpitController.php:276`),
`energyReading()` is still called by `CockpitController`, `EventDecisionController` and
`TrainingDecisionController`, and `AncestorRankTest`, `GrandparentSparksTest`,
`LegacyDisambiguationTest`, `CockpitEnergyBandDisplayTest`, `DeckSlotCardLevelTest` and
`EnergyReadingConsistencyTest` all exist.

**Only round 5 was misattributed.** Rounds 2–4 need no rescue, and the re-measurement is
downgraded from four slices of work to this note. A future pass that wants to re-measure them
should do it as a read, not as a plan item.

### 13.2 A3 — two widgets, two targets, two conventions; and the convention is the finding

§4's R6-13 recorded `payoutTurn = 70` minus the current turn as the sole derivation. Measured
at HEAD, there are two derivations of "turns to a Unity Cup event", and they answer different
questions from different bases:

| Widget | Source | Formula | Convention | At turn 67 logged | At turn 1 logged |
| --- | --- | --- | --- | --- | --- |
| Finale proximity | `TrainerAdvisor::finaleContext` (`app/Services/Advisor/TrainerAdvisor.php:241-247`), rendered by `NextDecision.php:495-506` | `(3 × 24) + 1 − nextTurnNumber()` | **next turn to play**, inclusive of the turn being decided (documented `:219-221`) | `73 − 68 = **5**` | `73 − 2 = **71**, suppressed` |
| Spirit Burst payout | `TeamPanelBuilder::burstCountdown` (`app/Services/Career/TeamPanelBuilder.php:720-727`), rendered `Cockpit.vue:540-542` | `70 − latestLoggedTurn` | **latest logged turn** | `70 − 67 = **3**` | `70 − 1 = **69**` |

Two further constraints make the finale widget reachable only at short range:
`FINALE_TURNS_AHEAD = 5` (`TrainerAdvisor.php:70`) suppresses it beyond five turns, so a
turn-1 run shows no finale line at all; and the burst widget returns `null` until at least one
turn is logged (`TeamPanelBuilder.php:721-724`).

**Settlement.** Walk 5's `Unity Cup is 5 turns away` is the **finale** widget at turn 67
(`73 − 68 = 5`), and it is correct. Walk 6's `Need 2 more bursts in 69 turns` is the **burst**
widget at turn 1 (`70 − 1 = 69`), measuring to the Spirit Burst payout at turn 70 — a
different event from the finals. So R6-13's "derives 69 where the source states 5" compares two
different quantities from two different widgets; the numbers were never meant to agree.

**The finding that survives** is a copy defect, not an arithmetic one: neither string names the
event it counts to. `Unity Cup is 5 turns away` does not say *the finals*; `… in 69 turns for
Gold Lv3 (Senior November payout)` names a payout but not that it is the Spirit Burst payout
rather than the career's end. `finaleContext`'s label is
`config("scenarios.scenarios.<key>.label")` (`app/Services/Scenario/FinaleReader.php:75`), which
is the scenario's own name — so the widget is structurally unable to distinguish the finals
from the scenario.

**Provenance boundary, stated because §6's method requires one:**
`ad8dc777` (2026-10-09) introduced `FINALE_TURNS_AHEAD` and `finaleContext` and **is an
ancestor of `34fd3c9e`**, walk 5's tree — so the widget walk 5 read existed then.
`388e98fd` (2026-11-04) added the Spirit Burst countdown and is **not** an ancestor of
`34fd3c9e` — it did not exist at walk 5's tree. These are two features, not one regressed one.

**R6-34's status stands for the field and its framing is corrected.** `CareerPosition`'s
`scenarioCountdown` is still a dead pipe (no writer, no renderer, its one component unimported —
§4.3's read). But "no renderer" is false as a statement about the *quantity*: it renders twice,
by derivation, in two widgets. The facet is therefore **field is a dead pipe; the quantity
renders by two unnamed derivations**. Primary status unchanged.

### 13.3 A5 — reversed: the reference is right and the application contradicts it

The round-7 brief proposed that the reference document and the screen agree against the client.
Measured, the reference was already corrected:

`docs/UMAMUSUME_REFERENCE.md:190` — `| Rest (お休み) | … | +50 Energy modal, +70 (big success),
+30 (failure, sometimes with 夜ふかし気味 / Late-Night Feeling) | [GameWith 257617 (n=3833) and
Kamigame …] |`

The application disagrees with it in two places **on the same screen**:

- `config/advisor.php:32-37` declares `rest_recovery => 30`, `source => 'GameWith, 2026-09-25'`,
  `confidence => 'current'` — the **failure tier** carrying a current-confidence label.
- `NextDecision.php:485` prints `Rest recovers +30 Energy. Source: GameWith, 2026-09-25, read
  2026-11-02.` from that constant.
- The same file's `recorded_forecast` branches (`NextDecision.php:238-243`) print
  `Success +50 (modal) / Big success +70 / Poor Rest +30 (may carry a stayed-up-late penalty)`
  — the correct tiers.

The file's own comment block (`NextDecision.php:220-235`) already records the disagreement and
says a single number "would be a guess wearing the tool's voice" — which is what the constant
then does.

**Classification: `Confirmed defect` on `config/advisor.php:32`.** The corpus is not at fault
and needs no correction; the constant presents the failure tier as the recovery figure and
labels it current. No new row is needed — this is an application defect the walk did not have
the reading to find, recorded here rather than as an `R6-NN` row because no walk-6 finding
covers it.

### 13.4 A6 — widened to three scenarios, and the repetition half is a read, not a ruling

§11.2 recorded the facility aggregate as needing an owner decision. Measured against the
revised reference and the config, only one small question actually needs an owner.

The reference is scenario-scoped: §2.2.2 says of Unity Cup **"Team ranks drive facilities.
Team stat rank raises Training Facility levels"**; §2.2.1 says of URA Finale **"Facility
progression by repetition. Training facility level rises from using the same discipline
repeatedly, rather than from a team rank or a parallel meter"**; §2.2.3 gives Trackblazer the
purchases variant.

`config/scenarios.php` already carries the distinction as a key: `facility_level_source` is
`team_rank` for `unity_cup` (`:407`), `repetition` for the URA entry (`:191`) and for Our Grand
Concert (`:1061`), `repetition_plus_purchases` for Trackblazer (`:624`).

**Nothing reads that key.** `TrainingRun::facilityLevel()` (`:1346-1359`) walks
`team_rank_ladder` in every scenario, and both call sites pass a rank letter
(`NextDecision.php:409`, `TeamPanelBuilder.php:509`). So Unity Cup is correct **by accident**,
and the URA, Our Grand Concert and Trackblazer surfaces print a team-rank-derived level where
the client uses repetition. That is a defect in three of four scenarios, not a design question.

The correct rule per key, all from data the application already stores:

| `facility_level_source` | Scenarios | Run-level figure | Source in the data model |
| --- | --- | --- | --- |
| `team_rank` | Unity Cup | The team-rank letter mapped through `team_rank_ladder` — current behaviour | `TeamRankPayload` → `facilityLevel()` (`TrainingRun.php:1346`) |
| `repetition` | URA Finale, Our Grand Concert | The latest logged turn's own level for that stat | `turn_entries.facility_speed` … `facility_wit`, validated `min:1 max:5` (`StoreTurnEntryRequest.php:97-101`), read per turn at `TimelineController.php:379-386` |
| `repetition_plus_purchases` | Trackblazer | The same, plus the Pro Shop's recorded increments | The above, plus `TurnEventType::Scenario` / `source_name = 'Shop'` events written by `storePurchase` (`TrainingRunController.php:866-882`) |

**The one owner question left:** whether a run with no logged turns should show a facility level
at all. `burstCountdown` already answers the analogous question by returning `null`
(`TeamPanelBuilder.php:721-724`); the repetition ladder should decide the same way explicitly
rather than by whatever `max()` on an empty collection happens to return. That is a display
policy, not a mechanic, and it is the only part of this that the corpus does not settle.

### 13.5 A7 — softened: a convention the tool must state, not a number to correct

The revised reference states all three totals and rules on them (`:396`):
"the player is given a total of 72 turns, excluding the 6-turn finale … Adding that finale gives
78 … Community automation models 75 as 72 plus three finale races … **a tool must state which
convention it stores rather than treat 78 as a correction of 75.**"

`CareerCalendar::TURNS_PER_CAREER` is 72 (`:30`) and `CockpitController.php:493` comments that
"a career is exactly 72 turns and the finale is a separate phase outside them" — so the
application stores the 72 convention deliberately, which the reference permits.

**The defect is narrower than the round-7 brief assumed:** nothing to correct, but R6-14's three
URA Finals obligations render inside a grid that excludes the phase they belong to. The
reachability fact belongs on R6-14 as a facet; the extent itself is a schema-grade item for an
ADR (per-scenario career extent), not a slice, and not a "78 is right" change.

### 13.6 A4 — holds, with a carrier obligation the reference now makes explicit

`config/scenarios.php:476-480` carries the four bands and the derived payload
`'10–12' => 'Gold hint Lv1, +30 matching stat, +30 SP'` is read at `TeamPanelBuilder.php:504,675`
and rendered. So §4.3's R6-48 facet (c) — "`+30` has no key" — is true of storage and silent
about display.

The revised reference strengthens the copy obligation. Conflict row 57 (`:2745`) records
GameTora printing `"4 to 9"` and `"7 to 9"` as its first two bands — an overlap that "cannot
both be" right — states that the file adopts `config/scenarios.php`'s `4–6 / 7–9 / 10–12 / 13+`
boundaries, and rules: **"No tool should print a band the run cannot be placed in, which is why
`config/scenarios.php` renders the caveat rather than a placement."**

Two obligations for whichever slice touches this surface: the screen must name its carrier the
way the rest of the copy names sources, and it must carry the low-band caveat rather than
printing band edges as settled. Plus the visual collision already recorded: the band's `+30`
and the eighth-place league bonus's `+30` sit on one panel and mean unrelated things.

### 13.7 A8 — unchanged, and now explicitly guarded by the reference

§4.1's R6-04 separates the scenario floor from the in-force cap. The revised reference adds the
guard directly (`:444`): observed URA ceilings of `1327/1322/1404/1306/1802` are "**observed
ceilings for those two runs, not … the URA scenario cap**", and "a tool must not print
1327/1322/1404/1306/1802 as 'the URA cap'."

`ScenarioCaps` already computes both numbers and keeps them apart: `scenarioCaps()` (`:178`) is
the published floor, `enteredCaps()` (`:151`) the run's own, and `forRun()` (`:131-133`)
overlays the second on the first. So the fix R6-04 needs is **labels only** — the total row and
the Cockpit caption must name which of the two they print. The bars are already faithful.

### 13.8 A2 — confirmed, unchanged

`R2-01`'s verdict at `round2:23` reads "Clarify in docs: the walkthrough should use the snapshot
import path, not the wizard… **Verdict, 2026-10-10:** held." Passes 3–6 took the wizard anyway.
This stands as written in §4 and needs no correction.

### 13.9 The capture-coverage constraint, from source §10

The source document gained §10 in this revision, and it changes what a next walk can claim:

> "The setup screens and turn 0 have no client capture at all. They are also the parts of the
> flow that published documentation covers in the most detail, which is why they read as
> well-sourced — and it is worth stating plainly that no frame in this corpus has ever been
> compared against them."

Its coverage table marks career setup, turn 0 / pre-debut, the Tazuna tutorial, team formation,
the four preseason rounds and the finals programme as **not covered** by any frame; only the
Senior-year endgame is.

Consequence for any round-7 walk brief: every setup-path and turn-0 claim must be labelled
publisher-derived rather than observed, or the pass must capture those screens from the client
first. A brief that routes the next walk through the snapshot door and then reports setup-screen
findings from it would be claiming frame evidence it does not have.

### 13.10 Framework citations for these corrections

All rule IDs below exist in `docs/framework-ui-ux/`, re-verified this pass:

| Correction | Rule ID | Where it is defined |
| --- | --- | --- |
| 13.2's unnamed counter targets | `H1` status; `GP-2` derived figure states its rule | `usability-heuristics…:214`; `gerhardt-powals…:118` |
| 13.4's scenario-keyed ladder | `H4` one component per role; `GP-1` product computes | `usability-heuristics…:217`; `gerhardt-powals…:117` |
| 13.5's stated convention | `GP-2` again, plus `SHR-7` | `gerhardt-powals…:118`; `shneiderman…:141` |
| 13.6's carrier and caveat | `GP-2`, `GP-3` summation labelled | `gerhardt-powals…:118-119` |
| 13.7's two cap numbers | `GP-3`; `H2` client vocabulary | `gerhardt-powals…:119`; `usability-heuristics…:215` |

Two corpus defects stand as recorded in §11.4 and §11.5 and are **not** repaired here:
`SHR-8` is undefined (cite `SHR-7`), and `GP-2` has no routing-table row (cite it anyway; the
routing gap is the prompt's, not the finding's). `docs/audit-corrections.md`, named in
`PROCESS-PLANS.md:15941` as the home for these five framework defects, does not exist; this
register does not create it, and the defects stay homed in §11.4, §11.5 and here.

### 13.11 Revised slice order

Supersedes §8. Rows and statuses do not move; only the order and grouping do.

| Pass | Contents | Shape |
| --- | --- | --- |
| **S1** | The lost-data trio: R6-05 parent name (payload builder + cold-load manual mode), R6-11 deck card level (rule + accessor + transactional write), R6-12 Timeline key and band projection | Three facts, one failure class; each with a RED assertion, then GREEN |
| **S2** | The self-contradicting constants and labels: `config/advisor.php:32` rest tier; the scenario-keyed facility ladder (`facility_level_source`, three scenarios); R6-04's two cap labels; the counter copy from 13.2 | Config and copy; no schema |
| **S3** | The copy batch: R6-15 hint prefix, R6-23 `Pal slot`, R6-09 placeholder, R6-10 `Pass`, R6-08 literal "six", 13.6's carrier and caveat, the two unrelated thirties | One assertion per item |
| **Held** | R6-13, R6-14's extent, R6-17, R6-26, R6-28, R6-31, R6-43, R6-52, and every §11.2 field item | Owner or ADR; unchanged from §8/§9 |

### 13.12 Revised owner decisions

§9 listed nine. This revision answers four from the evidence and leaves five open:

| Row | Was | Now |
| --- | --- | --- |
| R6-14 extent | owner call on wizard vs snapshot | **answered**: the tool stores the 72 convention, which the reference permits; the ADR item is a per-scenario extent, not a correction |
| R6-43 fans | owner call on restoring the ladder | **unchanged**, still the wireframe corpus owner's |
| §11.2 facility aggregate | owner call on which mechanism reads | **answered**: `facility_level_source` decides, and three scenarios are currently wrong. The only owner question left is the no-logged-turn display policy (13.4) |
| R6-48 `+30` | unrepresented source data | **reclassified**: derived and displayed; the obligation is copy (carrier + caveat), not a column |
| R6-13 counter | mid-career entry | **narrowed**: entry is unchanged, and the counter's two unnamed targets are S2 copy |
| R6-26, R6-28, R6-31, R6-17, R6-52, R6-53 | open | **unchanged** |

### 13.13 Three corrections to the round-7 brief

Recorded so this register shows what was revised rather than implying the brief was always
right:

1. **A1 was wrong** to instruct a re-measurement of rounds 2–4; those commits are real and their
   fixes hold (13.1).
2. **A5 was wrong** to expect a corpus correction; the reference carried the correct tiers and
   the application's constant is the defect (13.3).
3. **A3's framing was incomplete** — it posited one formula and two readings; there are two
   widgets with two conventions, and R6-13 compared quantities that were never meant to agree
   (13.2).

