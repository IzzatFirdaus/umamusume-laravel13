# docs/design-research/CONSTRAINTS.md: design contract

**Addendum, not replacement.** The repository root's `CONSTRAINTS.md` is the quality bar and this file does not weaken it. Nothing here overrides C-1 through C-8 or the Floor section of the root file. Where this file adds a gate, it is additive: a change must pass both.

Scope: the user-facing layer of Umamusume Trainer Companion. Applies to Blade views, components, CSS, client JS, exported CSV/JSON headers, seed and fixture strings, UI copy, and design artifacts such as mockups and prototypes.

Status: contract for review. No production UI code exists yet, so nothing here has been violated yet either.

---

## 1. Token contract

**D-1. The `@theme` block is law.** Every colour, radius, shadow, and type step used in a view must resolve to a token defined in `DESIGN.md` §3.5. Raw hex literals, arbitrary values (`bg-[#7FCC09]`), and magic numbers (`rounded-[13px]`) are review failures.

**D-2. One source of truth per value.** A token is defined once in the theme and referenced everywhere else. A colour appearing in two files is a colour that will drift.

**D-3. Chrome steps never carry text.** The bright step (500) of any hue is fill, border, ring, and ornament. Any surface carrying text uses a step from §3.4's approved pair table. This is the single most likely violation in this codebase, because the game's own chrome does not obey it.

**D-4. No Tailwind default palette in semantic roles.** `green-500`, `blue-500`, `red-500` and friends must not stand in for a design token. Tailwind's `green-500` is `#22C55E`; the action green is `#7FCC09`. The difference is the product's identity.

**D-5. Radius nests.** Inner radius equals outer radius minus the padding between them. A 14px card inside a 14px panel is a defect.

**D-6. Shadows carry the cool cast.** No neutral-grey shadow, no `backdrop-filter`, no glassmorphism, no neumorphism, no inner shadow. These do not appear anywhere in the source corpus (`RAW-FINDINGS.md` §3.1, §3.5).

**D-7. Circles mean "pick one of five."** Fully round interactive elements are reserved for the discipline affordance and avatars. Rounding anything else dissolves the meaning.

---

## 2. Contrast contract

**D-10. Every text/background pair must pass WCAG 2.1 AA** at its size: 4.5:1 for text under 18.66px bold or 24px regular, 3:1 above. `DESIGN.md` §3.4 lists the approved pairs with computed ratios. A new pair needs its ratio computed and recorded before it ships, not after.

**D-11. The client's own failing chrome is not a precedent.** White on `#7FCC09` measures 1.99:1. Reproducing it because "the game does it" is a violation, not fidelity. Where fidelity and legibility conflict, legibility wins and the departure is recorded in `DESIGN.md`.

**D-12. State is never colour-only.** Every coloured state carries a word, a glyph, or a shape difference. Applies to mood pills, run status, skill acquisition status, validation errors, and grade badges. This is both `DESIGN.md` P5 and a functional requirement: a Trainer who cannot separate the hues must still read the state.

**D-13. Focus visible always.** `outline: none` without a replacement ring is a review failure. The ring is 3px `green` at 35% alpha.

**D-14. The faceted page field and the lattice bleed are decorative** and must not be exposed to assistive technology or carry information.

---

## 3. Lore integrity (NFR-6)

Non-negotiable, blocking, and owned by the Lore Guardian role in `AGENTS.md`. A single confirmed violation fails the change. The grep proposes; the Guardian decides.

This section names the banned patterns in order to forbid them, which is the same practice the root `CONSTRAINTS.md` C-4 list and `CLAUDE.md` Banned Patterns already use. It adds no new vocabulary choice; it adds a dictionary. `make lore` will report hits here, and the Guardian's ruling on these lines is the same as on the root file's own list.


### 3.1 Banned vocabulary for characters

Grep case-insensitively across all UI text, identifiers, seed data, export headers, alt text, and mockup copy:

| Banned | Why | Required instead |
|---|---|---|
| `horse`, `horses` | the core violation | Umamusume | <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->
| `sire`, `dam` | parental animal terms | inheritance parent, parent umamusume | <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->
| `mare`, `stallion`, `filly`, `colt`, `gelding` | sex-specific animal terms | umamusume, trainee | <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->
| `foal` | offspring term | trainee umamusume | <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->
| `equine`, `pony`, `thoroughbred` | animal framing | umamusume | <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->
| `stable` as a noun for a character container | animal framing | roster, catalog, collection | <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->
| `breeding`, `pairing`, `bloodline`, `pedigree`, `lineage` of characters | the cut PRD §6.3 system | inheritance (two parent references only) | <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->
| `🏇` and any horse/riding emoji | costume framing | none | <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->
| `hoof`, `mane`, `tail`, `withers`, `muzzle` | body-part animal framing | avoid; describe the garment or accessory if needed | <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->
| `jockey`, `rider`, `saddle`, `tack`, `reins`, `bit` | the human-animal control frame | Trainer (which is us, and means something different here) |
| `paddock`, `herd`, `flock`, `pack` | collective animal nouns | roster, list, catalog |
| "your horse", "your mount", "the animal", "the girl and her horse" | framing | your trainee, the umamusume | <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->

### 3.2 Context-allowed senses (do not "fix" these)

The grep will hit these. They are not violations, and the Guardian's ruling stands over the tool's count:

- `dam` inside `damaged`, `demand`, `command` <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.2 -->
- `sire` inside `desired`, `surprise`, and the Umamusume name `Red Desire` <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.2 -->
- `stable` as an adjective: "stable growth", "keep the build stable", "a stable control identifier" (`tests/Feature/ReviewFormAccessibilityTest.php:39`, ruled 2026-09-28). The ban is on `stable` as a noun naming a character container; an adjective describing anything is the allowed sense. <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.2 -->
- `mare` inside `nightmare` <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.2 -->
- `tail` inside `detail`, `retail`, `curtail` <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.2 -->
- `account` in the verb idiom "account for": "the list the template has to account for" (`tests/Feature/ReviewQueueTest.php:62`, ruled 2026-09-28). The bill and login senses are what the ban exists for, and `PRD.md` NFR-1 declares this tool has no account and no auth surface, so neither can appear as product language; "account for" means "cover", which is ordinary English in a code comment.
- `account` inside Laravel's own shipped queue defaults: `your-account-id`, the AWS SQS env fallback at `config/queue.php:60` (ruled 2026-09-28). `account` and `login` sit in the `lore-code` word list to catch an auth surface being built, which is a scope question for the Planner Domain Specialist and Architect, not a lore question about a framework stub nobody wrote copy for.
- Japanese source strings and quoted official notices: source data, never a violation in itself
- `docs/PRE-MORTEM.md` legacy quotations, exempt once each under root C-4

### 3.3 Required forms

- The race name is **Umamusume**: a humanoid race. Capital `U` at sentence start or as the proper race name; lowercase `umamusume` mid-sentence.
- **Singular and plural are identical.** "one umamusume, three umamusume." Never "umamusumes", never "umamusume's" for a plural possessive.
- Characters are referred to as **Umamusume** or, in the training context, **Trainee Umamusume**.
- A character with a finished career is a **Veteran Umamusume**. Not "graduated", not "retired mount"; the run status enum case `Retired` is fine because it names the run, not the character.
- The human user is the **Trainer**.
- Sentence case in all UI copy. No decorative emoji in labels, headings, or buttons.

---

## 4. Official terminology (Global client strings)

UI labels must use the Global English client's words, not the JP client's and not a wiki's paraphrase. Source: `docs/UMAMUSUME_REFERENCE.md` §6 (server terminology map), §1.1, §1.3.

| Concept | Required label | Banned alternatives |
|---|---|---|
| The five stats | Speed, Stamina, Power, Guts, Wit | Intelligence (the export key for 賢さ, not the client label) |
| Skill currency | Skill Points, abbreviated SP | Skill Pt, Skill Pts as a UI label |
| Running styles | Front Runner, Pace Chaser, Late Surger, End Closer | Runner, Leader, Betweener, Tracker, Chaser |
| Style abbreviations | Front, Pace, Late, End (aptitude table only) | the four full names inside a dense aptitude grid |
| Distances | Sprint, Mile, Medium, Long | Short, Middle, Staying |
| Surfaces | Turf, Dirt | Grass, Sand |
| Support card types | Speed, Stamina, Power, Guts, Wit, `Pal` **[uncaptured]**, Group | Friend (for 友人) |
| Gacha | Scouts | Gacha, Pickup, Banner |
| Pull currency | Carats | Jewels, Gems |
| Character being trained | Trainee Umamusume | Trainee Uma Musume, Uma Musume being trained |
| Finished character | Veteran Umamusume | Hall of Fame, graduated |
| Inheritance unit | Spark | Factor (JP-side 因子 wording) |
| Inheritance **system** | Inspiration | Inheritance as the English label (that is the JP 継承 word, not the `[Global]` one) |
| Ancestors picked for a run | Legacies | Parents, grandparents, bloodline (D-267 bars these in copy) | <!-- lore-ignore-line class=1 cite=D-267 -->
| Energy | Energy | Stamina for the gauge (collides with the stat) |
| Mood | Mood, with the five state words shown | Motivation (the JP guide gloss), Condition |
| Scenario names | Ura Finale, Unity Cup, Brighter Together Our Grand Concert; Trackblazer or Twinkle Star Climax for the third | Make a new track!!, Climax bare |
| Rest action | Rest | Break, Vacation |
| Bond gauge | Bond | Friendship gauge, trust |
| Friendship training | Friendship Training, Friendship Bonus | Rainbow training (a guide nickname) |
| Hint discount | Hint Lvl N, NN% OFF | Hint level N discount |
| Skill acquisition states | Suggested, Acquired, Skipped (the `SkillAcquisition` enum) | Planned/Used/Ignored, Proposed |
| Run states | Active, Completed, Retired (the `RunStatus` enum) | Ongoing/Finished/Archived |

**What `[uncaptured]` means in this table.** The labels in the middle column are not equally evidenced. The five discipline names are client strings read from captures; **`Pal` is not** — it is Game8 English's word for 友人, and the client's own label for the sixth type has never been read off a frame (D-278). "Do not ship it as though the client said it" is D-278's ruling, and this table's job is to keep a reader of one row from concluding what a reader of both rows knows. Same status applies to the running-style abbreviations' *rendering*, not their words.

**Naming here is already ruled; the residual is narrower than it first looks.** `UMAMUSUME_REFERENCE.md:571` records `[Global]` localising the **system** as *Inspiration*, with *legacies* for the chosen ancestors and *Sparks* for the traits, and the owner confirmed that set on 2026-09-27 in root `DESIGN.md` §6 and `PRODUCT.md`, naming the screen itself **Legacy Select** — which §6.27 and D-260 use throughout. The only open edge is evidentiary, not decisional: **neither "Legacy Select" nor "Inspiration" has been read off a captured frame**, so both are legitimate as *this tool's* vocabulary and neither may be styled as the client's own caption (D-20). Check for an existing ruling before escalating a naming question as though it were new — this note first framed a closed decision as an open one.

**D-20. Never invent a client string.** If a label is needed and no Global string is on record, use a plain descriptive phrase and record the gap. `UMAMUSUME_REFERENCE.md` marks its own unverified items with `❌ UNVERIFIED`; a design artifact must not silently promote one into UI copy. This rule is general and stands. Its mood-tier instance is **resolved, not relaxed**: the client's own Mood Effect panel (owner-supplied capture, 2026-09-27) prints `GREAT` / `GOOD` / `NORMAL` / `BAD` / `AWFUL`, so those five are evidenced client strings and §6.17 of `DESIGN.md` now uses them as UI copy. The strings this rule still blocks are every label the corpus has not captured from the client itself, the outing action's button text among them.

**D-21. Do not key on fan-translation strings.** Some community English names carry banned vocabulary that the official Global label does not (the reference doc's Rainbow Cleat case). Official Global text wins.

---

## 5. Data-model binding

The UI is a view over the schema, and the schema is fixed by `ARCHITECTURE.md` §3. A screen that needs a field the schema does not have is a scope question for the Architect, not a reason to add a column.

**D-30. Render only what exists.** The permitted surface is: `TrainingRun` (umamusume, scenario, status, two inheritance parents, notes, `character_card_id`), `TurnEntry` (turn, Speed, Stamina, Power, Guts, Wit, SP, condition), `run_skills` (status, turn acquired), `Skill` (name, name_ja, sp_cost, type, is_unique), `Umamusume` (name, name_ja, release_status, debut dates, is_manual, aliases), `CharacterCard` (`character_cards`: card_id, umamusume_id, title, rarity, global_release_date, is_debut_form, unconfirmed, and its inline provenance set `source_url`, `snapshot_path`, `fetched_at`, `source_timezone` plus the card's own `is_manual`, per `ADR-0003` Amendment R3 as plan Amendment A1 adopted it on 2026-09-29), `data_sources` (provenance at character grain: `umamusume_id`-scoped, and not where a card's provenance lives), `match_candidates`. `CharacterCard` and `TrainingRun`'s `character_card_id` joined this list on 2026-09-29 under `docs/adr/0008-character-card-catalog-layer.md`, after the owner ruled on the scope question this section's preamble raises: the entry is the record of a ruling, not a relaxation, and the rule stands for every column that has not been asked and answered the same way. `character_cards` was authorized and not migrated; `2026_09_29_120000` and `2026_09_29_120100` landed it on 2026-09-29 with `CharacterCard`, `CardRarity` and `Umamusume::cards()`, so the card columns listed above are renderable today. `TrainingRun`'s `character_card_id` was authorized and not migrated; `2026_09_29_120200` landed it on 2026-09-29 as a nullable foreign key to `character_cards.id`, with `TrainingRun::characterCard()`, a fillable `character_card_id` and a same-trainee `exists` rule on `StoreTrainingRunRequest`, so a screen may now read the form a run started on.

**D-31. The 0..1200 validation bound is a defect, and the UI must stop hiding it.** `PRD.md` FR-C-2 and `CLAUDE.md` Planner Rule 6 fix stat input at 0..1200, but every live scenario caps higher: Ura Finale 1400 across the board, Unity Cup Wit 1800, Trackblazer Stamina 1900, Our Grand Concert Speed 1600 (`UMAMUSUME_REFERENCE.md` §1.3.4). **A real run cannot be recorded.** P0 US-3 therefore fails on legitimate gameplay data, and this is a product blocker, not a display question.

An earlier revision of this rule told the UI to "show `/1200`" and never imply a wider ceiling. That was wrong: it dressed a schema limitation up as a design decision and would have made the tool look internally consistent while being unable to hold the thing it exists to track. Withdrawn.

Current requirements, pending `docs/adr/0002`:
- The stat bar scales to the **scenario base cap for that stat**, per D-160, not to a global constant.
- The 1200 validation limit renders as a visible marker on the bar, labelled as the tool's current bound, so the gap between what the game allows and what this app accepts is explicit rather than silent.
- A value above 1200 must not be rejected with a message implying the game caps at 1200. Until the bound is raised, the error names the limitation as the tool's own.

**D-32. Turn numbers start at 1 and are unique per run.** The UI must reject a duplicate at the input, not silently renumber, because PRD US-3 makes the rejection an acceptance criterion.

**D-33. Every engine-sourced fact shows provenance.** Source URL and fetched date at `meta` weight on any catalog read (FR-A-4, NFR-2). Trainer-entered run data shows no provenance, because the Trainer is the source, and adding a fake one would be a lie.

**D-34. Timezone correctness is visible.** Dates display through `config('uma.display_timezone')`; date-only values render identically in every timezone (US-7). Never hardcode a locale. Any date-arithmetic change needs a `freezeTime()` test.

**D-35. Deterministic and explainable.** Every derived number on screen is a pure function of entered turns (Planner Rules 4 and 5). No estimated outcomes, no "recommended" figures, no confidence scores the Trainer did not produce. If a value cannot be traced to turns on screen, it does not render.

**D-36. No simulation, prediction, or snapshot surface.** PRD §6.11 and §6.12 are absolute. No race prediction, no aptitude grade input, no race-day snapshot, no localStorage-authored run, no trainee image upload (§6.13). A mockup that shows one of these is a scope violation even as a picture, because it sets an expectation the PRD forbids.

**D-37. No auth, no accounts, no multi-user affordance.** No avatar menu, no "sign in", no workspace switcher, no share link. PRD §6.1.

---

## 6. UX architecture: Trainer's Dashboard (Screen A)

The persistent layout contract.

**D-40. Two-region desktop frame.** The frame has two regions with different lifetimes: a **state region** holding run identity, turn chip, resource strip and persistent stat band, and a **work region** holding the turn timeline and the guided step. The state region never scrolls away; the work region scrolls and groups by phase. That split is the rule, and it is the spec's own words: `docs/UX Behavior Specification - Umamusume Trainer Companion.md:136-141` names the two regions, lists what each contains, and gives them exactly those two behaviours ("Persistent, never collapses" / "Scrollable, grouped by phase"). This also mirrors the client's own two-window composition (`RAW-FINDINGS.md` §3.8), and PRD §2 fixes the tool to a desktop browser.

**Axis is not the rule (amended 2026-09-28, R27).** The spec's table draws the regions left and right; the built frame stacks them, state region pinned above the work region at `lg:` and up. The reason is measured, not tasted: the stat band is six columns, and six columns beside a timeline inside `main`'s 64rem render as unreadable slivers, so a left/right frame satisfies the drawing and breaks the legibility the drawing exists to serve. `slice-5-2026-09-28.md` §3 records the persistence measurement that the rule actually gates on (the state region stays on screen through a full-page scroll). A left/right implementation is compliant only if it keeps the band's six columns readable at the widths this tool targets; nothing in this rule prefers one axis over the other.

Below 1024px the two regions stack on the narrow axis. The brief does not require mobile and no mobile-first compromise is accepted in exchange for desktop density.

**D-41. The stat band is persistent.** It does not scroll away and it is never collapsed. A Trainer reading turn 24 needs the totals at the same moment they read the entry, exactly as the client keeps the band on screen during training.

**D-42. The turn chip is the orientation anchor.** It is present at all times, shows turn number and phase, and is the first thing in the reading order.

**D-43. The timeline groups by phase and collapses.** Entries are `DESIGN.md` §6.9 cards; groups use the collapsible phase bar. Default state: the current phase expanded, earlier phases collapsed. A 24-turn run must fit one screen of scrolling, not four.

**D-44. Planned versus actual is one view, not two.** Skill rows carry the `Suggested` / `Acquired` / `Skipped` status inline with the turn it resolved (US-4). Splitting plan and outcome across tabs destroys the comparison the feature exists to enable.

**D-45. Every data region on the dashboard implements all four states** (root C-7): empty, loading, populated, error. An empty run is the first screen a Trainer ever sees, so it is a designed screen, not a gap.

**D-46. The log is append-last and never re-sorts under the reader.** A new turn appears at the end and the view scrolls to it once.

---

## 7. UX architecture: Guided Turn Input (Screen B)

**D-50. Decision cards, not a form.** A turn is logged as a sequence of single-question steps in the game's voice, using the `DESIGN.md` §6.10 choice-card pattern. A wall of six numeric inputs is the failure mode this whole design exists to avoid.

**D-51. Preview before commit, always.** Each option shows its consequence before acceptance, and acceptance is a separate explicit action (P2, `RAW-FINDINGS.md` §5.1). Selecting must never write.

**D-52. One question per step, in the client's decision order.** The sequence is: which training this turn, then the outcome numbers it produced, then any skill event, then a note. Not the schema's column order.

**D-53. The raw-entry escape hatch exists and is secondary.** A Trainer correcting a mistyped turn or importing from a spreadsheet needs direct field access. It is reachable, it is not the default, and it uses the §6.14 field pattern.

**D-54. The step's question text uses official terminology only** (D-20). "Which training are you focusing on this turn?" is acceptable plain English. A step prompt must not introduce a concept the client does not have.

**D-55. Keyboard-first.** 1-5 select a discipline, Enter confirms a step, Escape steps back. The full flow is completable without a pointer.

**D-56. Validation errors return to the step that caused them,** with the offending option re-presented, not to the top of the flow with a generic banner. Range errors cite the bound: "Speed must be between 0 and 1200."

**D-57. Two layout variants must be evaluated before one is chosen.** The brief requires it and the choice is load-bearing for every later screen. Variants differ in where the preview lives (beneath the option versus in a persistent side panel), which is the real design tension at desktop width.

---

## 8. UX architecture: Run List (Screen C) and Skill Search (Screen D)

**D-60. Run list is cards, not a table.** The turn-depth chip wants to be an object, and a row would flatten it. Each card carries: trainee name, scenario, status pill, turn depth, last three stat deltas, and the Suggested/Acquired/Skipped tally.

**D-61. Sort and filter are header-weight controls** at `label` size. A toolbar that outshouts the data inverts P3.

**D-62. Skill search matches on `match_key`,** never on display strings (FR-D-2, root Banned Patterns: no matching on display strings). A Japanese query may return an English row.

**D-63. A search result shows which field matched.** Skill name, Japanese name, or alias: the Trainer needs to know whether they found the skill or an alias of it.

**D-64. Search results are `DESIGN.md` §6.11 skill rows.** The client's skill row is already a search result; inventing a second presentation for the same object is ornament, not design.

**D-65. Empty search and no-results are different states.** The first invites a query; the second says the skill is not in the catalog and offers the review-queue path (US-5), because a missing skill is a data-fetch gap, not a user error.

**D-66. Autocomplete is debounced and cancellable,** and never fires a network request. Everything here is local (NFR-1); a remote call in a search box would be both a bug and a privacy violation.

---

## 9. Anti-slop rules for this product

Beyond the general `antislop` / `antislop-ui` gates, these are the specific ways this UI would go wrong:

**D-70. No purple-blue gradient.** It is the tell of an AI-generated dashboard and it appears nowhere in the corpus.
**D-71. No glassmorphism, no blurred panels, no neon-on-charcoal.** Not present in the source material (`RAW-FINDINGS.md` §3.1).
**D-72. No Inter, no default system-ui as the primary voice.** The client's letterforms are rounded (`DESIGN.md` §4.1).
**D-73. No emoji as icons.** No 🏇, no 🐎, no ✨ sprinkled through labels. <!-- lore-ignore-line class=1 cite=D-73 -->
**D-74. No racetrack clip-art, no horseshoe motifs, no silhouette stock imagery.** Costume, and partly a lore violation. <!-- lore-ignore-line class=1 cite=D-74 -->
**D-75. Ornament budget: at most two motifs per visual region** (`DESIGN.md` §7). The client's density works against a character model we do not have.
**D-76. No fake data presented as real.** Any trainee, skill, or turn appearing in a mockup or prototype is labelled sample data where the reader can see it. Two further requirements:
- **Use real catalog names, not invented ones.** `Rice Shower`, `Oguri Cap`, `Mejiro McQueen`, `Special Week` and the skill names in `UMAMUSUME_REFERENCE.md` are real Global strings. Inventing a trainee named "Star Blazer" puts fabricated data into the catalog's shape and reads as filler.
- **Never invent a stat value that implies a mechanic.** A prototype may show `Speed 302/1200`; it may not show a predicted final stat, an aptitude grade it did not derive, or a win rate. D-35 and D-36.

**D-77. No lorem ipsum in a delivered artifact.** Placeholder copy is a review failure.

**D-78. Do not design the cut features.** No support-card manager, no event calendar, no race planner, no breeding view. PRD §6. <!-- lore-ignore-line class=1 cite=D-78 -->

**D-79. No AI copy voice.** No "unleash", "seamless", "effortless", "level up your game", no exclamation-mark CTAs, no em dash in prose (`CLAUDE.md` Banned Patterns).

**D-80. Every screen earns its elements.** A stat, chip, or card that does not change what a Trainer knows comes out.

**D-81. A prototype control either works or says it does not.** A button that does nothing, a tab that will not switch, or a filter with no effect is the mockup-wearing-a-product tell. In a visual prototype, either wire the interaction, or mark the region visibly as static. An inert control that looks live is a defect; an inert control that says "static prototype" is honest.

**D-82. Colour dose caps are a review item.** `DESIGN.md` §7.1 caps saturated hues per screen region at 2 plus the standing `green` chrome, `crimson` at one element per screen, `gold` at one selection, and glow at two elements. A screen that fails the squint test, where several hues compete, is fixed by making one of them neutral, not by adding spacing.

**D-83. No decorative left stripe.** A coloured left edge must carry information (`is_unique`, active, warning) and name it. `DESIGN.md` §6.11.

**D-84. No invented iconography family.** The client's icons are soft, weighty, miniature renders. A hairline stroke set (Lucide or a clone) placed beside them reads as a different product. Whatever set is chosen is a decision recorded in `DESIGN.md`, not an import. No emoji, ever (D-73).


---

## 10. Motion contract

**D-90. Nothing animates that is not responding to the Trainer.** No idle motion, no ambient drift, no entrance animation on page load.
**D-91. No transition longer than 240ms.**
**D-92. `prefers-reduced-motion: reduce` is supported** and collapses transitions to a 1ms crossfade, including the stat count-up.
**D-93. Motion never carries information alone.** A state change must be legible with animation disabled.

---

## 10b. Data visualisation

**D-94. No chart without a written question.** The question goes in the title. "Stat progression" is not a question; "how did Speed reach 302?" is. If a sentence answers the question better, write the sentence (`DESIGN.md` §8.1).

**D-95. The default progression view is the turn list, not a line chart.** A Trainer's question about a run is which turn caused a change, and a list of per-turn deltas answers that directly while a six-series line chart does not. A chart is an addition, never a replacement.

**D-96. Chart segments and series must clear 3:1 against each other,** and every series is labelled at its line end rather than in a colour-only legend (D-12, `DESIGN.md` §3.6).

**D-97. No delta without a named comparison.** "+14" must say what it is relative to: the previous turn, or turn 1. An unlabelled trend is an invented metric.

---

## 10c. Theming

**D-100. Light is the default theme; dark is a supported theme.** The client is a high-key light interface, so light is what a Trainer sees first. Dark exists because the owner asked for it and because the tool is read for long sessions on a desktop.

**D-101. Dark is a token override, not a fork.** It re-declares the same custom property names (`DESIGN.md` §3.7). A component rule that branches on the theme with `@apply`-level duplication or a `dark:` utility scattered per-element is a defect: if the theme block is complete, no component should need to know which theme is active. The exceptions are the three named in §3.7 (page texture, shadow, ink warmth).

**D-102. Dark anchors come from the raceboard, not from a default palette.** `#121013`, `#24262A`, `#F5B73C`, `#0170D7`, `#FF1618` and `#AAABB5` are measured from `Screenshot 2026-07-18 000919.png`. A dark surface introduced without a measurement or a stated derivation is a review failure. Charcoal-plus-neon and blue-black are specifically not acceptable defaults here.

**D-103. Both themes pass the contrast contract independently.** D-10 applies per theme. A pair that passes in light and fails in dark is a failure, and the ratio must be recorded for whichever theme it belongs to. `DESIGN.md` §3.7 carries the dark table.

**D-104. Theme follows the system, and the preference lives in SQLite, not the browser.** Amended 2026-09-27 after the light-first ruling; **the original justification for `localStorage` was wrong.** It read "a local single-user tool has no server to store a preference on" — but this tool has a database, and `PRD.md` §6.12 cuts browser-side storage precisely because repo #4's localStorage-vs-account split made the browser a second source of truth. Owner ruling: preferences are persisted in SQLite.

Resolution order, first paint included: **stored preference → `prefers-color-scheme` → light**, where light is the *base palette* rather than a forced default. "Light-first" therefore describes which set the tokens hold at `:root`, not that a dark-OS Trainer is served light. The mechanism is an inline script in the document head — a bundled module runs after first paint and flashes the wrong theme (G-20). Once a preference row exists the Blade shell renders `data-theme` server-side and the script only covers the no-preference case.

Verified in a browser on 2026-09-27 with the OS emulated both ways: light yields no `data-theme` and `--color-page` #F2F1F8; dark yields `data-theme="dark"` and #0D0C0F with ink #ECEAF2, on the real layout rather than the review page.

**One blocker before the storage half can be built.** `AGENTS.md` requires every new table or column to cite a PRD requirement. **US-7** covers the display timezone; **nothing in PRD §2 covers a theme preference or the failure-estimate toggle** that `ADR-0001`'s off-by-default implies. So a `preferences` table needs a PRD line first — recorded as an Architect escalation for the owner, not solved by inventing a key in `localStorage`, which would put the app back into the §6.12 failure this ruling exists to close.

**D-105. Never ship a component that only works in one theme.** Every state in §6.15 and every component in §6 must be reviewed in both. A toggle in the prototype header is the review affordance for this.



## 10d. Game-native fidelity rules

These five came out of a review of the first mockup set. Each one is a place where a competent web default produced something that reads as a clean web app rather than as this game.

**D-110. Rounded humanist sans is mandatory; neutral and geometric grotesques are banned.**
Banned as the primary voice: Inter, Roboto, Helvetica, Arial, Geist, Space Grotesk, and `system-ui` / `-apple-system` when they carry the running text. `system-ui` is banned because it is not one font: a build can pass review on the machine it was written on and read as a generic web app on the next. `DESIGN.md` §4.1 records the measured weight coverage of every candidate and the recommended stack.
- Weight coverage is a hard gate, not a preference: the §4.2 scale needs 500, 600, 700 and 800. **Varela Round serves 400 only and Quicksand tops out at 700**, so neither can carry the scale and both are rejected on that basis, not on taste.
- Japanese coverage must resolve inside the declared stack so `name` and `name_ja` share a baseline and x-height.
- Known counter-evidence, recorded so nobody rediscovers it: Cygames' own web properties use Roboto, not a rounded face. The tool follows the **client** deliberately. See `RAW-FINDINGS.md` §6.

**D-111. Icons are filled and chunky. Thin line icons are banned.**
Solid glyphs, or strokes no thinner than 2.4px at a 24px box, with rounded caps, rounded joins, and 1.5-2px radius on the glyph body. The test: reduce to 20px and solid-black; if it survives only as an outline, it is wrong. Banned sets include Lucide, Feather and Heroicons outline, and no hairline library may be imported wholesale. Emoji are banned as icons (D-73, D-84). One family, one grid, no mixing weights.
The five discipline glyphs (shoe, heart, dumbbell, horn, book) have fixed identity and appear at consistent weight in the band header, the discipline buttons and the gain bubbles.

**D-112. Primary buttons carry a hard-edged diagonal sheen.**
A single crisp boundary at roughly 116 degrees from the upper-left, 30% white, covering the upper-left band only. The gradient stop must be a split at one percentage (`34% 34.5%`), not a feathered range. A vertical light-to-dark gradient is explicitly **not** compliant: that is the generic CSS button, and the point of the rule is the moulded-plastic read. Fixed at 30% white in both themes.

**D-113. Stat band headers use the six per-stat tints, and nothing else may.**
The tints in `DESIGN.md` §3.6 are near-invisible individually (1.04-1.14 contrast against the panel) and only read as a group. They are wayfinding, not identity: the label and glyph remain primary, and a tint must never be the only thing distinguishing two columns (D-12).
These tints are the **only** place a per-stat colour may appear. Saturated identity hues remain banned by D-114.

**D-114. No saturated per-stat hue.**
Every saturated hue in the system is already semantic (§3.3). A stat may carry a tint (D-113) but not a colour. This rule survived the tint change deliberately: the collision that killed the earlier draft, Guts sharing `#800014` with the failure-risk colour, is exactly what this prevents.

**D-115. Run cards carry a micro-grade strip.**
Five 18px grade chips in the fixed order Speed, Stamina, Power, Guts, Wit; no values, no labels, order never varies; a 4px dot marks a stat past 1000. The strip is `aria-hidden` and duplicated as visually-hidden text, because a scannable pattern that only works by sight fails P5. Values remain on the card; the strip is a shortcut, never a replacement. No sparkline and no radar chart on a run card (D-94).

**D-116. Grade badges stay subordinate to the value.**
22px in the band, 18px in the micro strip. A grade badge that optically matches the numeral beside it has broken P3.

**D-117. The guided flow has no numbered step sidebar.**
No vertical 1-2-3-4 rail with a highlighted current step. That shape is a SaaS setup wizard and it is the fastest way for the flow to stop reading as an in-game event. One `Step 1 of 3` line with three dots at the foot of the stack. The client's own event screen presents a question and its options, and its only progress cue is the turn counter.

### Audit of these five rules against `antislop-ui`

Checked on 2026-09-27, because a rule written to defeat one slop pattern can install another.

| Rule | Verdict | Note |
|---|---|---|
| D-110 rounded font | **Pass** | `antislop-ui` does not ban Inter or Roboto on principle; it bans "the font that shows up because it was the default". This rule bans them *with a measured brand reason*, which is what the skill asks for. Rejecting Varela Round and Quicksand on weight coverage is an evidence call, not a taste call. |
| D-111 chunky icons | **Pass** | Directly answers the *Lucide Icons* tell. One internal tension found and fixed: §6.0 of DESIGN.md bans generic sparkle glyphs while §7 permitted a sparkle mark. Resolved by narrowing the sparkle to `is_unique` alone, where it denotes rather than decorates. |
| D-112 hard sheen | **Pass** | A specific material with a measured referent, not a default. The risk is the reverse one: a soft feathered gradient masquerading as compliance, which G-24 checks by inspecting the gradient stops. |
| D-113 per-stat tints | **Pass, with the gate carrying the weight** | This is the rule most likely to slide into *Too Many Colors* and *Excessive Accent Color*. It survives only because the tints are sub-perceptual as colour (1.04-1.14 against the panel) and because D-114 still bans saturated stat hues. Without G-25 sampling the range, someone would "improve" these into a rainbow within a week. The gate is the rule. |
| D-115 micro-grade strip | **Pass** | Derived from stored values, so it is not the *Stat Cards With Invented Numbers* tell. The `aria-hidden` plus visually-hidden text requirement is what keeps it out of *colour-only* territory. |
| D-117 no step sidebar | **Strong pass** | The numbered 1-2-3 rail is the *How It Works Always 3 Steps* tell in app clothing. Removing it is the correct instinct, not a simplification preference. |

One residual risk worth naming rather than legislating away: `antislop-ui` also warns against the *Sterile Default*, over-filtering into a flat white nothing with no identity. This system is dense with identity devices (brown ink, lattice, ribbons, arrow-caps, sheen, faceted field, six tints). The failure mode from here is excess, not sterility, which is why §7's two-motif budget and §7.1's colour caps outlive this round of additions.

---

## 10e. Event resolution and scenario flows

**D-130. Four flows, one panel.** F1 standard turn, F2 character event, F3 support card event, F4 scenario event. F2 and F3 share the DESIGN.md §8.6 event panel and differ only in the tag ribbon text and the outcome list. A fifth random-world flow is not modelled, because no in-run dataset supports it: `UMAMUSUME_REFERENCE.md` §1.4.4 carries three chains and §3.4 is live-ops content.

**D-131. The event panel is an overlay, not a modal.** The training scene, the turn chip and the stat band stay visible behind it. A full-page scrim that blanks the run destroys the orientation the client deliberately preserves.

**D-132. Every choice previews its full outcome before commitment, and committing is a separate action.** Applies to all four flows without exception. D-51 extends to events.

**D-133. Delta colour is fixed by the client, not by web convention.** Gains orange, losses blue. Green means action and red means risk. A green up-arrow in an event preview is a review failure.

**D-134. A stepped flow is allowed only for F4, and only when the event genuinely has multiple parts.** D-117 still bans a step rail on a standard turn. A scenario sheet keeps the tag ribbon, the banner choices and the scene behind it. It gains no progress rail and no Back / Next footer.

**D-135. Scenario sheets are data-driven.** No game mechanic is hardcoded into a scenario UI. Where the brief and the cited source disagree about a mechanic, the sheet renders what the record supplies and the disagreement is raised rather than silently resolved by a wireframe. Live instance: summer camp, `UMAMUSUME_REFERENCE.md` §1.1.2 versus the brief.

**D-136. Never hardcode a run length.** No source in this repo records a total turn count and the brief's "~50 rounds" is unverified. The turn chip shows a turn number and a remaining-turn count, as the client does (`DESIGN.md` §6.6 reads both off frame `194819`, plus a second chip for the phase countdown), and derives nothing from an assumed total.

**The remaining count is entered, not computed — this is the arithmetic the earlier wording hid.** "Turns left" looks like `total − current`, and with no recorded total that subtraction has no operand. So the value is a Trainer-entered reading of what the client printed for that turn, stored per turn like any other observed figure, on the D-270 pattern: the tool records what the client showed and never derives it. Two labels must stay distinct in UI and in schema, because they are different claims — remaining turns *in the current period*, which the client's calendar structure implies, and remaining turns *in the career*, which nothing in this repo records. A single field named `turns_left` without a period would silently assert the second one. G-31 stays as the check; the superseded `screen-a-dashboard.html` hardcoded `3` and is the reason it is a gate rather than a preference.

**D-137. `TurnEvent` needs a PRD citation and Architect sign-off before any migration.** `AGENTS.md` makes an uncited table a no-merge. Its `deltas` column stores observed outcomes only; `turn_entries` remains the source of truth for absolute values.

**D-138. Event copy is unverified until sourced.** Event titles, choice labels and mood words appearing in a mockup are sample content and may not become UI strings without a citation (D-20).

---

## 10f. Race calendar and fan gating

**D-150. The race calendar is the client's component, not a new one.** 24 half-month cells under a Junior / Classic / Senior tab bar, as measured in `Screenshot 2026-07-17 230755.png`. Seven cell states are defined in DESIGN.md §6.20 and a cell must always be in exactly one of them.

**D-151. A fan lock shows the number.** `fans_needed` appears on the cell, not just a padlock. Grades 300, 400 and 700 all bottom out at 350 fans, so a lock alone cannot identify which race is blocked or by how much (`UMAMUSUME_REFERENCE.md` §1.2.6).

**D-152. The maiden gate is a distinct state from a fan lock.** The client requires a Debut or Maiden win before standard races are open, which is a conditional lock unrelated to fan count. Rendering it with the fan treatment sends the Trainer to check the wrong number.

**D-153. Tier labels are per-race claims, never inferred from a grade code.** Only grade 100 = G1 and grade 400 = OP were client-confirmed when this was written; codes 200, 300 and 700 were `❌ UNVERIFIED`, so a UI mapping them was asserting a fact the repo did not have. That ban on inference stands.

**Dated per-row exceptions, measured 2026-09-29 (R72, nullified the same day by R75; `race-tier-labels-2026-09-29.json`).** The rule was tested against two publishers read in a rendering browser, joined on the race's own name rather than its numeric code: G1 agrees on **34/34**, G2 on **42/42**, G3 on **76/76** rows of the URA schedule, so those tiers are now per-race sourced and the seeder carries no code-to-label constant at all. **Open and Pre-OP are not settled by this.** Game8's table is graded-only (161 rows: G1 43, G2 42, G3 76), so its silence on Open and Pre-OP is silence, not agreement; and uma.guide alone labels Open as `"OP/L (Open/Listed)"`, a label that conflates two classes. Three Open rows are pinned per row by their own client name carrying 「オープン」, and those three keep the label. The other 115 code-400 rows and all 26 Pre-OP rows are **null**.

**R75 is the owner's ruling on the remaining 115, and it reverses what Slice 14 did with them.** Slice 14 kept the label as a *disclosed generalisation* from the three (`scope: code-level-client-naming-pin`, `per_row_sourced: false`) on the grounds that nulling it dropped a tier the tool had shown correctly since Slice 11. The owner ruled the other way: a tier the export's numeric grade implies but no source states for that race is not seeded, and it is not kept on a disclosure flag either — **a null tier is the honest state**, so the flag is gone from the extraction rather than set to `false`. Seeded distribution is G1 34, G2 42, G3 76, OP 3, null 141; where the tier is null the seeder writes `slot_label` = `Race`, which is what a row with no per-race label has.

**Publisher dates are asymmetric and stay that way in the record:** Game8's page is dated 2026-09-09, which clears the 2026-07-01 bar; uma.guide publishes no date anywhere — no `Last-Modified`, no time element, no generated-at field in the dataset chunk — so only its fetch date is knowable.

**D-154. Race selection is F5 and reuses the event panel.** It appears only on a turn where the calendar holds an entry. Skip is a first-class option with its own banner, never the absence of a click.

**D-155. No race outcome prediction, ranking, or win estimate.** `PRD.md` §6.11 is unmodified on this point. ADR-0001 lifted the non-goal for Energy guidance only and explicitly left race outcomes out of scope. Eligibility arithmetic is permitted; placing projection is not.

**D-156. Fan count is persistent in the run header**, using the client's own sentence form, target plus shortfall (`194819`). No progress bar: the calendar has several stacked thresholds and one bar implies one ceiling.

**D-157. Unverified race claims are attributed, not asserted.** "Up to 2 or 3 races per turn" and the Senior-year Arima Kinen requirement have no source in this repo. If they appear in a design artifact they are labelled as owner-supplied, per D-20.

**D-158. Race and fan data need a PRD requirement before any migration.** `turn_entries` has no `fans` column and there is no `race_goals` or calendar table. `PRD.md` US-10 is P2 and deferred. Building this UI in Phase 1 means promoting US-10 to P1 and writing an FR for it, which is an owner and Architect decision, not an implementation detail. See ADR-0001.

---

## 10g. Scenario awareness

**D-160. The active scenario is a header element, never a subtitle.** Four scenarios are live on Global and they cap differently. A run view that does not name its scenario at header weight reads as a single-scenario tracker.

**D-161. Stat bars scale to that scenario's per-stat base cap, never to a global constant.** A Unity Cup Wit bar and a Ura Finale Wit bar showing the same value must be visibly different lengths. This is the cheapest honest signal of scenario awareness.

**D-162. A cap is displayed as a stack, not a number.** A run's true ceiling is scenario base plus breakthrough at +16 per ★3 inherited factor plus per-stat support-card 限界値アップ effects (`UMAMUSUME_REFERENCE.md` §1.3.4), applied at three separate moments. Where a component is not tracked in the schema, the disclosure line says so. An unqualified "Cap: 1800" is a false claim of completeness.

**D-163. Single-source cap data carries its confidence.** Grand Masters and L'Arc caps are recorded in §1.3.4 as "single-source, not corroborated". They may appear only with that stated. The four primary scenario cap sets come from sources flagged `STALE` and need re-verification before implementation.

**D-164. Scenario must become resolvable before any of this is built.** `training_runs.scenario` is a nullable free-text string. Free text cannot select a cap set. Either an enum or a `scenarios` reference table is required, and cap data needs provenance rows like any other engine-owned fact. See `docs/adr/0002`.

**D-165. No scenario-specific chrome may be invented from assumption.** The entire screenshot corpus is one scenario, Unity Cup. There is no visual evidence for the UI of the other three, nor for differing facility layouts. Scenario-specific interface treatment is either captured from the client or designed from scenario data, and a mockup must label which.

---

## 10h. Persistent resources and the conditional race step

Owner decisions of 2026-09-27, recorded in `docs/adr/0003`. These are requirements, not options.

**D-170. Turn, scenario, Energy and Fan count are persistent in every run view.** None of the four scrolls away. A Trainer mid-turn needs all four to decide the next action, and the schema now stores Energy and Fans as end-of-turn totals so the header shows a real value rather than a derived sum (`ADR-0003`).

**D-171. Energy is prominent at the point of commitment.** In the guided flow the gauge and the caution state sit adjacent to the Confirm control, not only in the page header. A gauge 800px from the button does not answer "can I afford this" at the moment it is asked.

**D-172. Energy and Fans store absolute end-of-turn values, not deltas.** This matches the existing convention for the five stats, which is why an injury is already representable as 302 then 294. A delta series cannot answer a start-of-turn value without a summation that every prior row must survive intact. Deltas stay a presentation-layer subtraction.

**D-173. The three race lock states are visually distinct, and the maiden lock breaks the padlock pattern.** Mandatory Goal races read heavier, never dimmer. Fan locks show the numeric `fans_needed`. The maiden gate uses a dashed outline and its own sentence, because its remedy is an event rather than a quantity. Sharing one treatment between the two locks is a review failure.

**D-174. Locked races stay visible and named.** A fan-locked race renders disabled with its number, never hidden. A race the Trainer did not know existed cannot be planned for.

**D-175. The race selection step is conditional on the calendar holding an entry for that slot.** On turns with no race the flow goes straight to training or rest. A race step on an empty slot is noise that trains the Trainer to ignore the step when it matters.

**D-176. `Skipped` is a stored status, not an absent row.** `race_entries.status` carries `NotOffered`, `Skipped`, `Entered`, `Completed`. A nullable-absent row cannot distinguish "not offered" from "declined", and that distinction is exactly what the calendar renders.

**D-177. Screen D is a generic multi-step scenario event.** No scenario mechanic is hardcoded into the UI. The Summer Camp "choose 2 disciplines" instruction was withdrawn by the owner on 2026-09-27; `UMAMUSUME_REFERENCE.md` §1.1.2 stands.

**D-178. US-10 is P1.** Race calendar and fan gating are Phase 1 scope. Any artifact that treats them as deferred is out of date.

---

## 10j. Corrections taken from the screenshot corpus

Recorded because each of these was visible in the client and my earlier specs got it wrong anyway. A rule that names its own predecessor is easier not to repeat.

**D-185. Grade badges are tinted per grade.** A orange, B pink, C green, D blue, E purple, F and G grey. An earlier revision shipped one flat indigo badge on the argument that the hues were semantically spoken for; that overrode a direct observation recorded in `RAW-FINDINGS.md` §8. The letter always accompanies the colour, so nothing depends on hue discrimination (P5, D-12).

**D-186. The Energy fill sweeps the full gauge spectrum.** Cyan through green and lime into yellow, amber and red toward the right end. Colour encodes **position on the track**, not the current level. An earlier spec said "cyan to lime", which was a measurement artefact: the probe only covered the filled portion of a partly-drained bar, so the amber and red tail sat behind the dark unfilled track and went unmeasured.

**D-187. The discipline banner carries the facility activity name.** Two stacked ribbons: the pale one names the discipline and level, the saturated one names the activity the facility offers (`Breaststroke`, `Freestyle`, `Incline`, `Running`, `Long-Distance Swimming`, `Dirt`). A mockup showing only `Stamina Lvl 1` is incomplete.

**The closing clause of this rule was an over-reading and is withdrawn.** It previously said the activity set is "the best evidence in the corpus that scenarios differ in facility layout". Every frame carrying these six names is **Unity Cup**; the corpus has no Trackblazer or Our Grand Concert training HUD (`SCREENSHOT-MANIFEST.md` coverage table), so within-corpus evidence for the list is variation *per facility within one scenario*, not variation *between scenarios*. What survives: the activity line is required content on the banner, and a future cross-scenario claim needs frames or scenario data behind it, not this list.

**D-188. The support card rail belongs on the dashboard.** Circular avatar, discipline type badge, segmented bond gauge, orange double chevron when friendship training is available, flame mark when the card sits on its own tile. Documented in `RAW-FINDINGS.md` §3.6 and then omitted from every mockup in this package.

**D-189. The failure indicator is a blue pill beneath the stat band.** The client shows a computed percentage because it holds live state. This tool renders the §6.15 band word and **never** a number, because no sourced curve exists (D-155, `ADR-0001` §3).

**D-190. Advisories are NPC speech bubbles, not banners.** A circular staff avatar beside a rounded white bubble with a small green `HINT` badge, which is how the client frames every piece of coaching it gives. This is the single largest immersion gain available and costs nothing, because the alternative is a validation banner that reads as software complaining.

**D-191. A cap increase is never phrased as a stat increase.** The client separates the two: `Max Energy +4` against `Guts +10` in choice previews, and the effect class 「限界値アップ」 rendered Global as `Max Speed`, `Max Stamina`. So a log line reads `Speed cap went up by 4`, never `Speed went up by 4`. Conflating them corrupts run history. Exact client log phrasing is unobserved, so the wording is ours and the distinction is not negotiable.

**D-192. Unique skill chips use a pink-to-blue gradient fill.** An earlier revision flattened this to `indigo-50` on the theory that pink and blue were reserved; those hues are semantic only when used as *meaning*, and a chip background carries no meaning beyond identity. The gradient is the most legible affordance on the skill panel.

**D-193. Scenario claims must name their evidence.** The corpus is predominantly Unity Cup with confirmed Ura Finale material and **no Trackblazer or Our Grand Concert frames**. A `Result Pts` counter and a `TEAM RANK` badge are observed Unity Cup chrome. Any design asserting a third scenario's UI is describing something this repository has not seen.

---

## 10k. Strategy-driven UI rules

Extracted 2026-09-27 by reading the strategy wikis in a real browser. Note for the record: the assumed blocking was not real. GameWith and Game8 both served these pages to a plain Playwright navigation with no user-agent substitution, no injected delays and no scroll tricks. The earlier 403/503 reports came from simple HTTP fetchers failing to execute JavaScript, which is a rendering limitation, not access control. Nothing was bypassed.

Sources read: `gamewith.jp/uma-musume/article/show/257614` (training guide) and `game8.co/.../archives/536322` (Global Special Week build guide).

### Fail probability on event choices

**D-200. A choice preview must show both the success and the failure branch, or say that the choice cannot fail.**
Game8 documents event outcomes as split branches: `Choice 2 (Fail)` yields `-3 Mood · 3 Random stat -10 · Practice Poor`. GameWith documents the same shape. A preview showing only the success numbers is not a preview, it is a best case presented as an expectation, and it is the one UI lie this tool must not tell, because the whole point of preview-before-commit is informed consent.

Required form: a success block, a failure block, and a probability label where one is known. Where the client exposes no probability for that specific choice, render both branches with no percentage and state that the odds are not shown, rather than inventing a number (D-155).

**D-201. Failure outcomes are first-class log entries.** `Practice Poor`, a mood drop, and a stat hit on the last-trained stat plus two random others are recorded facts about the run, not error states. They render in the timeline with the same treatment as a success, using blue for the decreases.

### Mood

**D-202. Mood is both a tier and a number.** Wikis record mood changes as integer deltas, `-2 Mood`, `-3 Mood`, and `+ 20%` style training modifiers. The mood pill shows the tier word; a mood change in a preview or log shows the delta. Both, never one alone. The pill's directional arrow is the third required part of the tier readout (D-259).

**D-203. Withdrawn 2026-09-27, superseded by D-259.** This rule blocked the five tier labels as unverified Global client strings. An owner-supplied capture of the client's Mood Effect panel resolves it: the tiers are `GREAT`, `GOOD`, `NORMAL`, `BAD`, `AWFUL`, with training effects +20 / +10 / 0 / −10 / −20 percent and pre-race attribute effects +4 / +2 / 0 / −2 / −4 percent, and the active tier marked on its own row. `DESIGN.md` §6.17 carries the table and D-20 now records the resolution rather than the block.
The rule's speculation is withdrawn with it: `Practice Poor` is **not** a candidate for the low mood tier. It is a failure condition from an event outcome (D-201, `DESIGN.md` §6.16b), and the client's low tiers are `BAD` and `AWFUL`. A mood widget must never offer a `Practice Poor` state, and the two vocabularies must not be merged.

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

**D-210. Use real Global strings in prototypes and mockups.** Now evidenced from Game8 and usable under D-76: skills `Gourmand`, `Unstoppable`, `Up-Tempo`, `Come What May, See Ya Later!`, `In Body and Mind`, `Homestretch Haste`, `Professor of Curvature`, `Traightaways`, `Playtime's Over`, `564 Escapades`; cards `Oguri Cap (Ashen Miracle)`, `Oguri Cap (Starlight Beat)`; statuses `Practice Poor`. Invented names remain banned.
⚠️ **`Light Hello` removed from this list.** It was recorded here as a skill name. It is not one: `UMAMUSUME_REFERENCE.md` §3 lists **Light Hello [From the Ground Up]** as a **Pal support card** (live on Global 2026-07-22), and `scenarios.json` carries Light Hello as a **character** (`char_id 9008`) linked to two later scenarios. Both readings rule out "skill", so this was a misclassification on our side, not a wiki trap — do not use it as a skill string until re-evidenced from a skill list. `Homestretch Haste` stays: `04-trackblazer-umaguide.md` corroborates it independently as the Legendary epithet reward.
⚠️ **Three of the ten skill strings above are not client strings — corrected 2026-09-29 against the data export (`ADR-0011`).** D-210 sourced these ten from a publisher's skill list, and the list's renderings differ from what the client prints: the export's `name_en` is the client string while `enname` is a literal translation of the Japanese, and the two diverge on 535 of the 623 `[Global]` rows. So `Come What May, See Ya Later!` is **`Come What May`** (id 201701; `Prepared to Die` is the translation), `Playtime's Over` is **`Playtime's Over!`** (id 201661), and **`Traightaways` appears under neither field at all** — the nearest real families are `Straightaway Adept` (200362), `Straightaway Acceleration` (200372) and the `Sprint/Mile/Medium/Long Straightaways ◎/○` distance skills, and picking among them would be guessing at a provenance, so it is withdrawn rather than replaced. The other seven check out exactly as recorded (`Gourmand` 201351, `Unstoppable` 202371, `Up-Tempo` 200722, `In Body and Mind` 200511, `Homestretch Haste` 200512, `Professor of Curvature` 200331, `564 Escapades` 110071), which is the useful part: the list was right about seven and wrong about three, and the gate read all ten as real. **Standing consequence for G-16:** a name evidenced from a publisher's *list* is not the same as a name evidenced from the client — G-16 accepts a string against this rule only when it is the export's `name_en` of a row available on `[Global]`, and `SkillSeeder` now holds exactly those.

---

## 10l. Scenario mechanics extracted from the Game8 scenario guides

Read 2026-09-27 in a real browser. **Coverage is uneven and that is recorded rather than smoothed over:** the URA Finale guide (`archives/536520`) was retrieved in full. The Unity Cup guide (`archives/545572`) and the Trackblazer guide (`archives/580723`) both redirected away from their scenario pages during the session, so **neither scenario's own guide was actually read.** Anything below about Unity Cup or Trackblazer comes from the screenshot corpus or `UMAMUSUME_REFERENCE.md`, not from these two URLs. Do not cite 10l as evidence for them.

### The 1200 line is a real soft cap, not an app artefact

**D-211. Gains past 1200 are halved, and the UI must say so.**
*"Training gains for stats beyond 1200 are always halved."* 1200 is therefore a meaningful in-game inflection point, not just a validation constant this app happens to use.

**Attribution corrected 2026-09-27; this rule previously cited its own source wrongly.** The text here used to read "The URA guide states plainly", and `docs/scenarios/01-ura-finale.md` contains neither the word *halved* nor the figure *1200* anywhere. The sentence is from **Game8's `[Global]` English scenario pages**, read in a browser — `archives/536520` per `docs/adr/0002` amendment 1, and the same phrasing recorded in `UMAMUSUME_REFERENCE.md` under "The July 1, 2026 Global rework" against Unity Cup `545572` (2026-07-07) and Trackblazer `580723` (2026-08-25). The rule stands on a post-rework `[Global]` source; what was wrong was the pointer to it.

The second clause was also misattributed: `Fully Charged` is not in the URA guide either. It is a **Grand Live / Our Grand Concert** mechanic that pays off at Power 1200 or more (`UMAMUSUME_REFERENCE.md` §1.6 conflict 4, still OPEN on its mechanics). Same threshold, different scenario, and the tool must not present a Grand Concert gate as URA documentation.

**Two axes, one threshold — do not merge them.** This rule is about *training gains* being halved past 1200. `UMAMUSUME_REFERENCE.md` §1.3.4 separately records that points past 1200 "have a **reduced effect on the Umamusume during the race**", sourced from Game8 2025-11-21 and Kamigame 2024, both ⚠️ STALE. Those are different claims about different phases of a run, and §1.3.4's baseline row currently states only the race-side one. A UI sentence like "gains halved past 1200" asserts the training axis; it must not be quoted as though it were the race-effect figure.

**Verification status, stated so it is not read as equal to D-212.** The additive cap model in D-212 is confirmed at the data level by `scenarios.json`. This rule's *threshold* is corroborated by the same export (every scenario's cap reproduces as `1200 + stats[i]`), but the **halving itself is prose-only** — no dataset field carries a 50% factor, and `archives/536520` has no recorded date, which D-228 would ordinarily require. So: render the 1200 marker as sourced, attribute the halving to a `[Global]` guide rather than to game data, and treat the exact reduction as unconfirmed pending re-verification.

This reframes `ADR-0002`. The defect there is real, the app rejects values a real run reaches, but the fix is not "raise the number to an arbitrary 2000". The bar should show **1200 as the soft-cap line where returns halve**, which is a fact about the game, and separately show the scenario ceiling, which is a different fact. Two markers, two meanings.

**D-212. Caps are expressed as base plus bonus, and the client renders the bonus in gold.**
*"The URA Finale Scenario now has +200 stat caps for Speed, Stamina, Power, Guts, and Wit."* So URA's 1400 is `1200 base + 200 scenario`, not a flat 1400. The cap stack in `DESIGN.md` §6.22 is confirmed as the right model, and its rows should read as additions, not totals.
**Confirmed at the data level.** `scenarios.json` stores a five-element `stats` array per scenario that is exactly this bonus, and JP scenario id=8 carries a **negative** Stamina bonus (`-200` → a cap of 1000, below the base). A field that can sit under the base is a delta, which settles the model rather than merely corroborating it. Two consequences: a cap row must be able to render a ceiling **below 1200** without breaking, and the additive display is the honest one.

**D-213. Gold text is the client's own signal for a cap increase. Adopt it.**
*"When a stat's cap is about to be increased, the values that will be added will always be indicated in gold text."* This resolves the open wording question in D-191, which had said the distinction was real but the client's phrasing was unobserved. It is now observed: **cap increases render in gold.** So a `+4` in `gold` means the ceiling moved and the stat did not, and a `+4` in `orange` means the stat moved. The colour carries the distinction the prose was carrying, which is stronger than a wording rule. Keep the word `cap` in the text as well, so the distinction survives for anyone who cannot separate gold from orange (P5, D-12).

### Scenario structure

**D-214. Summer camp runs in both Classic and Senior years, four turns each.**
*"There are 4 turns of each Summer training and they occur during Early July until Late August of both Classic and Senior Years."* That is eight camp turns across a run, not one window. `UMAMUSUME_REFERENCE.md` §1.1.2 describes the window without saying it recurs per year, so this is a genuine refinement. The calendar must mark both windows.

**D-215. Scenario NPCs appear in training at calendar milestones, and they are the speech-bubble speakers.**
The URA guide names Director Akikawa, who appears in training after the debut and gives +30 Energy on the third camp turn, and Reporter Etsuko Otonashi, who appears from Early July. These are the characters behind the `HINT` bubble pattern in D-190, and **which NPC appears is scenario-specific**, so the advisory speaker is scenario chrome, not a fixed avatar. Note the rights position: the client's NPC artwork is not ours to ship, so the bubble carries a name and a monogram, not an illustration.

**D-216. A Scenario Link is a per-scenario property that some scenarios do not have.**
~~Each scenario has a Scenario Link Character.~~ **Withdrawn.** URA Finale has Aoi Kiryuin and Unity Cup has five linked characters selected through Team Name, but **Trackblazer has none**, and `docs/scenarios/05-trackblazer-gametora.md` states it directly: *"no new original story characters and no Scenario Link mechanic."* The game data agrees without interpretation — Trackblazer's `scenario_linked_characters` is an empty array where URA's has one entry and Unity Cup's has five. So scenario link is an **optional, variable-cardinality** field: the run header must render zero, one, or many, and any layout that reserves a slot for exactly one link character is wrong on the third scenario. This is the same class of defect as D-220's fixed resource strip, found a rule earlier.

**D-217. Event choices are documented positionally as Top, Mid, Bottom.**
The guide writes outcomes as `Choices: Top: Stat +10 / Mid: Energy +20 / Bottom: Skill Points +20`. So the client presents a vertical stack of up to three, and `choice_index` in `ADR-0003` maps cleanly onto that. Design the choice list for one to three options, not a scrolling list of many.

### Staleness warning

**D-218. URA Finale was reworked on 2026-07-01 on Global, ahead of its planned release.**
*"Updates to the URA Finale scenario were made on July 1, 2026 in the Global version."* The cap table in `UMAMUSUME_REFERENCE.md` §1.3.4 traces to Game8 2025-11-21 and Kamigame 2024-02-19, both already flagged `⚠️ STALE`, and the URA row is demonstrably from before a rework that changed caps.
**Re-verification is now done, and it moved off prose onto data.** The live source is GameTora's `scenarios` dataset (manifest key `scenarios`, hash `61b7c51c` as of 2026-09-27), which carries `stats` as a per-stat bonus over a 1200 base and `hard_caps` as a separate 2000 ceiling. Its four Global rows reproduce the published post-rework caps exactly. **Seeding must trace to that dataset, not to §1.3.4**, which is stale in two ways: it predates the rework and its Climax row is transposed. See D-227 for the durable provenance rule and D-228 for the dating rule.

---

## 10m. Scenario divergence rules

Derived from `docs/scenarios/01-ura-finale.md`, `02-unity-cup.md`, `03-trackblazer.md`, then corrected against `04-trackblazer-umaguide.md`, `05-trackblazer-gametora.md`, `06-unity-cup-gametora.md` and the live `scenarios.json` dataset. Full analysis: `docs/design-research/SCENARIO-DIFFERENCES.md`.

**D-220. The persistent resource strip is scenario-composed, never fixed.** Ura Finale has no team system, no shop and no mid-scenario currency, so its dashboard is the minimal layout: turn, trainee, scenario, Energy, Fans. Unity Cup adds Team Rank, Spirit Burst count and Result Pts. Trackblazer adds Grade Points against deadlines and a Shop Coin balance. A URA mockup showing a team panel is a factual error, not a stylistic choice. The strip must stay composable with a **fourth** scenario in place — Our Grand Concert is already live on Global and is not represented here at all.

**D-221. The Race Calendar is conditional on the scenario having race goals.** Trackblazer has none; it substitutes Grade Point deadlines, a shop and Rival races. `scenario_races` and `race_entries` from `ADR-0003` are URA-shaped and must be generalised to a `scenario_slots` table with a `kind` discriminator (`GoalRace`, `TeamRace`, `GradeDeadline`, `ScriptedEvent`) before any scenario other than URA or Unity Cup can render a timeline.

**D-222. A facility level chip must say what drove it, and there are now three possible causes.** URA derives level from repeating one stat four times. Unity Cup derives it from the team's aggregate rank in that stat (`G/F=1, D/E=2, B/C=3, A=4, S=5`) — the identical label `Lvl 5` therefore means something opposite to the URA case. Trackblazer uses the URA rule **and additionally allows permanent purchased levels** (Training Application items, 150 coins each), so a Trackblazer Level 5 can be partly bought rather than earned. A level without its cause is misleading in at least one of the three, and the purchased path is the one no other scenario has.

**D-223. Spirit Burst state is a six-state machine and "spent" is not terminal.** Chargeable (white flame) → charged → held (charged, deliberately untriggered) → normal burst spent → **Extreme chargeable** → Extreme spent. The 2026-07-01 update made an **Extreme Spirit Burst available on a teammate's next Unity Training after their normal burst**, which invalidates the pre-patch rule *"each character only gets one Spirit Burst per career"*. Rendering a spent teammate as a dead end is now factually wrong.
Two further pre-patch carry-overs corrected here: **the additional energy cost on Special Training was removed**, so the preview must not apply an energy penalty (the Wit case survives only as a burst bonus of **+5 extra energy recovery**), and burst skill hints are **no longer random** but draw from that support card's own hint pool, falling back to A-rank aptitude when exhausted — the tool may name the pool but must never predict the draw (Rule 1).

**D-224. Fan thresholds come in two unrelated kinds and must not share a widget.** Race *entry* gates run 350 to 25,000 (`UMAMUSUME_REFERENCE.md` §1.2.6). URA *event* gates for Unique Skill upgrades run 60,000 / 70,000 / 120,000 Turf and 40,000 / 60,000 / 80,000 Dirt. The next threshold the Trainer cares about depends on which kind is imminent; a single `Fans: 1,943` readout serves neither.

**D-225. Race advice is scenario-gated or must be withheld.** In Trackblazer, racing often is the strategy and fans are the payoff. In Unity Cup, *"do not try to maximize regular races"* because every racing turn is a turn not spent on team rank. The same suggestion is correct in one scenario and harmful in the other.

**D-226. NPC presence is itself scenario-specific, and friendship plus burst state are logged as typed `turn_events` payloads, not `turn_entries` columns.**
~~NPC friendship is a universal run resource.~~ Corrected: **Chairman Akikawa is absent from Unity Cup** — Riko Kashimoto stands in as acting chairman, which is why Unity Cup's April Unique Skill level-up carries **no bond condition** while URA's does. So a gauge for an NPC who is not in the scenario is worse than no gauge, and the set of tracked NPCs is a per-scenario list, not a constant. The storage rule is unchanged: both friendship bars and burst state are scenario-specific per-turn facts, so columns like `akikawa_bars` or `bursts_triggered` on a shared table would push Unity Cup and URA chrome into every run's core record.

**D-227. The cap conflict is closed, and the fix is a provenance rule rather than a number.**
Resolved: Global Unity Cup caps **are** `1300/1300/1300/1300/1800` and the 1,800 Wit denominator in `DESIGN.md` §6.22 and the v7/v8 mockups stands, confirmed by `06-unity-cup-gametora.md` (dated after the patch) and by `scenarios.json`. The durable rule is the one that would have caught it sooner: **every stored game value needs a server qualifier and a date.** `UMAMUSUME_REFERENCE.md` §1.3.4's Climax row had neither, and it is both stale and transposed — it reads `…/1500/1200` for Guts/Wit where the game data is `…/1200/1500`. A value that cannot state which server it describes and when it was true is not seedable.

**D-228. A source's authority is its date, not its publisher, and a correct-as-written prediction still expires.** `03-trackblazer.md` described an unreleased scenario six months after it shipped. `05-trackblazer-gametora.md` is dated the launch day itself and predicts Global will keep the flat 1200 caps — a reasoned prediction that the 2026-07-01 rework then overturned. Both were doing their job honestly; both are now wrong on numbers. Any rule, mockup value, or seed trace that depends on a dated claim must re-check on a source dated **after** the most recent patch it knows about, and hedged language ("expected", "may be adjusted", "check current patch notes") is the signal to re-check first.

**D-229. Surface hidden information the client obscures.** The Unity Cup guide records that the Unity Training icon overlay hides skill hint icons on facilities, forcing manual inspection. Where the tool can show a fact the client makes hard to see, that is the value being designed for, and it outranks visual fidelity to the client's layout.

**D-230. A probability may print as a number only when it is sourced from client data or from two independent sources — and then derive it from entered turns.** `ADR-0001` concluded training failure rates could not be sourced and had to ship as bands. Trackblazer's **Race Fatigue** table is a different case and is fully quantified: it keys on the count of **consecutive races** (1 race: 0-15% mood down; 2: 0-33%; 3: 60-90%+; **4+: 100%**, plus 33% Skin Outbreak and 40% for 3 random stats −10). Because consecutive-race count is already recoverable from `turn_entries`, this satisfies Planner Rule 4 — it is a pure function of logged turns, not a simulation.

**Amended 2026-09-28 (R48), and the amendment narrows what this rule authorises.** The clause
above is false as the schema stands: `race_entries` links to a `scenario_slots` row (month, half,
tier) and never to a turn, and the guided flow offers no race choice, so no logged turn can be
identified as a race turn. The count is therefore **entered**, as a `RaceFatiguePayload`
(`{consecutive_races}`) on the turn the Trainer says it applies to, and KI-17 carries the gap so a
later slice can close it with a real link rather than a guess. Two things do not change: the number
is still a pure function of what the Trainer logged, so it is not a simulation, and the surface
still prints one qualitative word for the band rather than the published percentages, because
GameTora is one publisher and D-230 requires two. Entering the count instead of deriving it moves
where the trust sits — from the tool's arithmetic to the Trainer's reading — which is the same
trade D-270 already makes for Energy, mood and fans.

**The opening clause is bounded on purpose, because as first written it was a general principle a future round could cite for any number.** "Genuinely sourced" here means **client data or two independent sources**, not "a guide I like". Race Fatigue clears that bar on *form* (a quantified table) but not on *count*: it comes from `04-trackblazer-umaguide.md`, **a single source**, so the table ships as a **provisional exception**, marked as such where it renders, and it is not precedent for the training-failure case — `ADR-0001` still governs there with bands. Also note the `90%+` cell: the `+` makes it a **lower bound, not a figure**, so a UI that prints "90%" states something no source claims. Either render the band as `≥90%` or keep that cell in band form.

**D-231. Race Fatigue is switched off after Late December, and a sourced 0% must silence a warning rather than shrink it.** The fatigue events *"cannot occur after Late December"*, so the advisory has a hard calendar end and must disappear in the endgame stretch, not fade. Separately and more generally: when a mechanic sets a rate to zero — Race Fatigue after December, an **Extreme Spirit Burst's 0% failure at its facility**, or a purchased **Good-Luck Charm** (40 coins, one turn) — the risk affordance goes fully quiet. Rendering "low risk" where the game says "impossible" invents a warning the player is being told to ignore.

**D-232. Trackblazer's economy is two countdowns on one strip: the shop rotation and the Grade Point deadline.** Shop: the lineup **refreshes every 6 turns** with a timer the client itself displays; a Trainer **cannot hold more than 5 copies** of one item; offers may be **Limited** (own availability window, flagged top-right) or on **Sale** (10-20% off, flagged top-left). Multi-turn items cannot be re-used while active, and buying a weaker effect after a stronger one **overwrites** it — so the order of two purchases is a real, lossy decision. Unspent coins die with the run and Climax races pay none, which makes "how long until rotation" the shop's primary number, not the balance. Grade Points: there are **four objectives** (Debut race, then 60, +300, +300, with 30/200 reductions on the Dirt-leaning aptitude track), assessed at the end of each year, and **surplus never carries forward**. So the meter must show progress against the *current* objective only and reset visibly, because a running total would imply a banking strategy that does not exist.

**D-233. The end-of-career structure is scenario-composed, like the resource strip.** URA Finale ends in an elimination progression (qualifier → semifinals → finals). Unity Cup ends after **4 Team Races** against Team Zenith and then the URA-style final races. Trackblazer replaces the bracket with the **Twinkle Star Climax**, a **3-race points league** scored on Victory Points (1st 10 · 2nd 8 · 3rd 6 · 4th 4 · 5th-6th 3 · 7th-9th 2 · 10th-13th 1 · 14th+ 0; 30 maximum) where winning the aggregate is enough and winning all three is not. A finale panel that hard-codes a three-round knockout is a URA shape imposed on a scenario that does not have one.

**D-234. Unique Skill gating is a third distinct scheme per scenario, and Trackblazer's is two-dimensional.** URA gates on fans alone at three Senior-year events. Unity Cup gates on the same fan numbers but **drops the April bond condition** because Akikawa is absent. Trackblazer replaces both with an annual **"Umamusume of the Year"** selection in Late December requiring **fans and Akikawa bond together** — uma.guide reads 5,000/19, 60,000/31, 120,000/51 while GameTora states the same gates as bond bars (blue 1, blue 2, green 3); the two agree in shape and differ in unit, so show the pair, not a false precision. No single "next gate" widget can serve all three, and Trackblazer's needs two inputs to be legible at all.

**D-235. Wiki English is not client English; normalise terminology on import, not on display.** The three new source files carry **"Wisdom"** for **Wit** and **"Motivation"** for **Mood** in their own table headings, and their item rows quote those headings directly. A third trap sits alongside them: all three say **"scenario factor"** where the client says **Spark** — `06-unity-cup-gametora.md` glosses its own heading as *"Scenario Factor (Inheritance Spark)"*, which is the mapping spelled out. Their numbers are usable; their column labels are not.
This is already enforced where it matters: `gate.py`'s `TERM_BANNED` includes `\bwisdom\b`, `\bmotivation\b` and `\bfactor\b`, and it scans prototype rendered text, so a paste that imports the wiki vocabulary fails G-3. The gap was never the check, it was the assumption that a trustworthy table arrives with trustworthy headers. `Light Hello` is a separate, worse case in kind: it sat in D-210's "real Global strings" list as a **skill** while being a **Pal support card** and a character. Nothing in a source table's *row label* certifies what category the value belongs to, so an imported name needs its kind confirmed too.

## 10n. Scenario Configuration Matrix

The single table a reviewer checks a screen against. One column per scenario, three dimensions: **what appears**, **how the turn flow changes**, **what must be legible at a glance**. A cell reading *absent* is a hard requirement, not an option — rendering a widget a scenario has no mechanic for states something false about the run (D-220).

Sourced from `docs/scenarios/01`–`06`, `UMAMUSUME_REFERENCE.md`, and `scenarios.json`.

### Dashboard layout shifts

| Widget | URA Finale | Unity Cup | Trackblazer | Our Grand Concert |
|---|---|---|---|---|
| Turn chip, trainee + scenario identity, Energy gauge, Mood tier, stat band, timeline | present | present | present | present |
| Resource strip | **baseline**: turn, trainee, scenario, Energy, Fans | + Team Rank, Spirit Burst count | + Grade Points, Shop Coins | must degrade to baseline |
| Race Calendar (goal races) | present — the scenario's spine | present, **plus** Team Race slots as a distinct slot kind | **absent** | unknown → absent until sourced |
| Grade Point meter with deadline | absent | absent | present | absent |
| Team Rank gauge (per-stat letter) | absent | present | absent | absent |
| Spirit Burst roster (per teammate) | absent | present | absent | absent |
| Team Race schedule + countdown | absent | present | absent | absent |
| Shop Coin counter + restock countdown | absent | absent | present | absent |
| Epithet route tracker | absent | absent | present | absent |
| NPC friendship bars | present (Akikawa, Otonashi) | **Akikawa absent** — Riko Kashimoto stands in; the April gate has no bond condition | present (Akikawa, bond gates Unique Skill) | unknown |
| Scenario Link identity | Aoi Kiryuin (one) | five, chosen via Team Name | **none** | five per `scenarios.json` |
| Finale panel | qualifier → semis → finals, elimination | 4 Team Races then Team Zenith, then URA-style finals | **Twinkle Star Climax: 3-race points league** | unknown |

**The fourth column is the architectural test.** Our Grand Concert is live on Global, caps Speed at 1600, and has no mechanics guide in `docs/scenarios/`. A layout that renders correctly for three known scenarios but *crashes or invents chrome* for an undescribed fourth is not scenario-aware — it is scenario-hardcoded. The required behaviour is baseline strip plus known caps, and silence about the rest.

### Guided input flow adaptations

| Flow step | URA Finale | Unity Cup | Trackblazer |
|---|---|---|---|
| Choose activity | Training / Rest / Recreation / Race | same, but the training tile shows **which teammates are present** and how many white flames | Training / Rest / Recreation / Race, **racing is the strategy not the interruption** |
| Facility prompt | level from personal repetition | level from **team rank**; prompt must show occupancy, because multi-uma bonus scales 2/3/4/5 flames | level from repetition **plus purchased levels** |
| Extra step | none | **Team Race step**: pick 1 of 3 NPC opponents, then field 1–3 teammates per distance across 5 races | **Shop step**: spend coins; must warn that a weaker item bought after a stronger one overwrites it |
| Race prompt detail | goal + fan eligibility | goal + team-race slot; **extra racing is discouraged**, so no nudge to race | Grade Point yield + coin yield + **Race Fatigue risk from consecutive-race count** + VS flag |
| Energy at commitment | adjacent to Confirm (D-171) | same, and **no energy penalty on Special Training** (post-2026-07-01) | same; the Debut race itself costs Energy here |
| Preview honesty | success/failure branches, "Odds not shown" | same; burst hints name the **card's pool**, never the specific hint | same; fatigue may show a real % (D-230) |

### Visual indicators

| Indicator | Scenario | Requirement |
|---|---|---|
| Spirit Burst state | Unity Cup | **six** states: chargeable → charged → held → normal spent → **Extreme chargeable** → Extreme spent. "Spent" is never terminal (D-223) |
| Extreme burst active | Unity Cup | purple, and it **silences** the facility's failure risk rather than lowering it (D-231) |
| Team Rank letter | Unity Cup | letters `G…S` **plus S+**, decoupled from facility level, which still tops out at 5 (D-222) |
| Facility level chip | all three | names its cause: repetition, team rank, or purchased (D-222) |
| Grade Point meter | Trackblazer | progress against the **next** deadline only, with a visible "surplus does not carry over" marker (D-232) |
| Shop countdown | Trackblazer | turns until rotation; plus max-5-copies holding limit, Sale flag top-left, Limited flag top-right (D-232) |
| Epithet route progress | Trackblazer | per-route won/needed count, e.g. a 3-race route at 2/3 — the routes are what shape the racing schedule |
| Race Fatigue chip | Trackblazer | consecutive-race count with its band; **must disappear after Late December** (D-230, D-231) |
| Fan readout | all | two unrelated scales never share a widget: entry gates 350–25,000 vs event gates 60k/70k/120k (D-224). Trackblazer's Unique Skill gate is **two-dimensional**: fans *and* Akikawa bond (D-234) |
| Stat cap stack | all | `1,200 base · +scenario bonus · +breakthrough · deck untracked`, per-stat denominators, never one global constant (§6.22, D-212) |
| Locked race cell | URA, Unity Cup | shows the `fans_needed` figure and distinguishes fan-lock from maiden-lock (G-16a, G-16b) |

### Matrix rules

**D-240. Scenario configuration is data the layout reads, not branching the layout contains.** Every row above resolves from the run's scenario, and the set of scenarios is open — a fifth exists on JP and Global already has four. Implement as a per-scenario descriptor (which widgets, which slot kinds, which gates) consumed by one composable strip and one composable timeline, not as three conditionals in a template.

**D-241. An undescribed scenario must render as baseline plus known caps, never as a guess.** The matrix's fourth column is the acceptance case: our own design must be able to display a scenario it has no guide for without inventing a Shop panel or a Team Rank gauge. Absence is the safe state, and it is also the honest one.

**D-242. Widget presence and widget meaning both change per scenario, so a shared widget must carry its cause.** A Level 5, a fan count, and a "race" prompt each mean something different depending on the scenario. Rendering the same glyph with a different underlying rule and no label is the failure this matrix exists to prevent.

## 10o. Mockup review findings — v9 scenario-aware round

Four mockups were generated against §10n and then read back at full resolution, because D-184 says a PNG cannot be gate-verified and every prior round proved it: image generation invents plausible labels. Twelve findings, five fixed by regeneration, seven recorded as rules.

### Fixed in the v9b regeneration

| # | Defect | Fix |
|---|---|---|
| F1 | URA skill list contained **"Endurance Up"** — `endurance` is banned by `gate.py`'s `TERM_BANNED` (→ Stamina). A banned word arrived inside a *fabricated skill name*, so no existing check could have caught the name, only the word | Replaced with four evidenced strings from D-210: Homestretch Haste, Unstoppable, Gourmand, Up-Tempo |
| F2 | Unity Cup read "Power facility Lv 4 ← team rank S" and "Wit facility Lv 4 ← team rank S". Rank **S yields Lv 5**. The one widget whose entire purpose is to state the cause of a level stated it wrongly | Corrected, and the legend `G F → 1 · D E → 2 · B C → 3 · A → 4 · S → 5` now printed beside it |
| F3 | The same teammate was slotted into two distance rows at once (Rice Shower in Mile *and* Long) | One member per distance enforced |
| F4 | **Heart glyph did double duty** for Stamina and Energy, both green, both in the header band | Energy is now a rounded battery glyph; heart belongs to Stamina alone |
| F5 | Advisory bubble carried monogram **"TK"**, which maps to no NPC in any scenario | "AK" for Aoi Kiryuin, the URA Scenario Link |

### Recorded as rules

**D-250. Bar fill is one colour; only the header tint varies per stat.** The URA frame rendered all five stat bars in the neutral lime fill; the Unity Cup frame rendered them blue, pink, green, orange and purple. Both were produced from prompts describing the same system, and they disagree. D-114 already bans saturated stat identity hues, and the §3.6 tints exist precisely so stat colour can be *suggested* without being *shouted*. A per-stat bar fill re-introduces the withdrawn five-colour scheme through the back door and makes the two frames read as two different products.

**D-251. A glyph may carry one meaning across the whole system, and the flame currently carries three.** Guts is a flame, Spirit Burst "chargeable" is a flame, and facility occupancy is a cluster of flames. The occupancy flames are defensible — they *are* burst fuel — but Guts-as-flame makes the stat band and the burst roster speak the same symbol for different things. Pick a distinct Guts glyph, or a distinct burst glyph, and record the choice in §6.0.

**D-252. Never render a per-flame gain as a constant.** The Unity Cup facility rows all read "+3 primary / +1 secondary". Those numbers are a function of flame count *and* facility — the sourced table gives Wit at 2 flames as +1 Wit, +0 Speed, 0 SP, and 5 flames as +6 Wit, +2 Speed, 6 SP, while Speed at 2 flames is +2 primary, +0 secondary, 1 SP. A uniform figure is invented filler sitting in the exact place the tool's most valuable number belongs, and it is the kind of plausible constant that survives review until someone checks it against the table.

**D-253. `Suggested` must not be blue.** The URA skill list rendered the `Suggested` chip in blue outline, and blue is the **loss** colour under D-133. A suggested skill is not a negative event. The three acquisition states need a palette of their own that does not borrow from gain/loss semantics.

**D-254. Navigation labels are scenario-composed too.** The Trackblazer guided-input frame's dimmed background still showed a nav item reading **CALENDAR**, in the one scenario that has no race calendar (D-221). Removing the panel while leaving its name in the rail states the falsehood the rule exists to prevent, and it is easy to miss because the offending element sits in the background layer of a composition about something else.

**D-255. Sample state must vary, and must be able to be wrong.** All six support-card slots rendered a **full** bond meter, including the R card. Bond is the resource three URA event gates and Trackblazer's Unique Skill gates actually read, so a mockup where it is uniformly maxed depicts the one state where no gate ever fails. Same for the invented training activity names "Hill Repeat" and "Easy Run" in the Screen B background — D-76 requires sample data to be real Global strings, and an unverified activity name is a fabricated client string in a place nobody thinks to check.

### What the round demonstrated about the process

Every factual defect above was caught by a human reading the rendered image at full resolution, and none would have been caught by `gate.py` — it scans HTML prototypes, not PNGs. F1 is the sharpest case: the banned word `endurance` *is* in the gate's list, so had that skill name been pasted into a prototype the gate would have failed it, but as a mockup label it was invisible to every automated check. **The mockups are art direction, not evidence.** Any string that must be correct belongs in an HTML prototype where G-1, G-2, G-3 and G-13 can reach it.

## 10p. Convergence and derivation rules

Recorded after the v10 build, which replaced the PNG Screen A set with a single HTML artifact so the gate could reach the labels.

**D-256. A derived value must print its own boundaries, and must say whose derivation it is.** The grade badge is the sharpest case in the system: it is a single letter presented with total confidence, and nothing in this repository defines the scale it comes from. `screen-a-scenario-v10.html` computes the letter from the entered value through a 150-point banding and prints the full table beside it under a `[Provisional]` marker. That is the minimum acceptable shape for any derived figure: the number, the rule that produced it, and whether the rule is the client's or ours. A letter with no visible derivation is a fabricated client string wearing a badge, and it fails Planner Rule 5 as well as D-20.

**D-257. Converge to one layout spine; let scenario composition carry the difference.** The dashboard and the guided input are one vertical spine, and the only region that varies is the goal panel set. Three consequences worth stating because each cost something to give up:
- The per-scenario anchors from the v9 mockups (top-left lead, off-grid editorial, centred-low) are dropped. Variety across screens was the more attractive design and the worse product: three anchors read as three applications, and a Trainer moving between scenarios loses every positional cue.
- The two guided-input variations are dropped in favour of the inline preview. The side column separated a choice from its consequence and had already shipped a defect where the preview rendered above the choices.
- Retired artifacts move to `prototypes/superseded/` with a README naming their replacement, rather than being deleted. `gate.py` sweeps `prototypes/*.html`, so the subfolder also stops the gate from certifying files that contradict the current system. Three of the four retired files rendered stat band headers that are not the §3.6 tint system at all, one of them white on `#106F9F`.

**D-258. A contrast rule must name its second colour.** "1.04 to 1.14 against the panel" is not checkable while the component is painted on `--raised`. The dark tint set was solved against the surface it sits on and measures 1.09 to 1.11; the light set was solved against `--panel` and measures 1.10 to 1.21 where it actually renders. Same rule, two reference surfaces, one of them quietly failing. Every contrast threshold in this file must state both colours, or it cannot be verified by anyone, including the author.

**D-259. A mood pill renders the directional arrow. Colour plus word without it is a review failure.** The five tiers are `GREAT` ↑ +20%, `GOOD` ↑ +10%, `NORMAL` → 0%, `BAD` ↓ −10%, `AWFUL` ↓ −20% (`DESIGN.md` §6.17, from the client's own Mood Effect panel), and the arrow is not ornament: it is the only part of the readout that carries the ordinal direction. The middle tier's mark is read from the capture as neutral, and whether the client draws a flat arrow there or nothing at all is the one glyph worth re-confirming on the next capture; the rule is unaffected, since the tiers either side of it are unambiguous.

The reason it is now a rule rather than a preference is arithmetic. Three of the five pill colours are derived, not measured, and were derived at the mean relative luminance of the two measured anchors per the owner's fallback instruction (`DESIGN.md` §3.5). At equal luminance the ramp's own structure collapses: `GOOD` `#ED8036` and `BAD` `#D48556` are 3.3 degrees of hue apart at the same brightness, and `GREAT` `#FB5590` and `AWFUL` `#D47E9E` are 1.0 degree apart. Two pairs of *opposite* meaning therefore render as one colour, and no amount of re-picking saturation fixes that while the luminance is held equal. What the equal-luminance derivation buys is a row of five pills of even weight, which is what was asked for; what it costs is hue as an ordering signal, and the arrow is what pays for it.

Consequences, all checkable:
- The arrow renders on every mood pill, at every size, in every theme, including the compact forms in the log and the guided preview.
- The arrow is the client's own grammar (up for the two positive tiers, flat at `NORMAL`, down for the two negative ones), so it is a measured pattern rather than an invention, and it must not be replaced by a sign-prefixed number or a colour-only chip.
- G-6 (colour independence) is necessary but not sufficient for a mood pill: G-6 passes on a word-only pill, and a word-only pill still fails here. G-57 is the specific check.
- When the three provisional colours are replaced by measured ones, this rule does **not** lapse. The arrow is a client behaviour being copied, not a compensation this system chose.

## 10q. Legacy Select rules

From seven client frames dated 2026-07-15 and the Global Legacy and Sparks guides. Screen
anatomy in `DESIGN.md` §6.27.

**D-260. Legacy Select is a pre-run step, never a per-turn one.** The client reaches it before training begins: create run, choose scenario, choose Trainee Umamusume, choose Legacies, then turn one. It must not appear in the guided turn flow, must not be reachable from a turn, and its result is fixed for the life of the run. A per-turn Legacy control would imply the Trainer can re-choose ancestors mid-run, which the game does not allow and which would silently invalidate every turn entered before the change.

**D-261. Every Spark names the Legacy it came from and its generation.** The right-hand list groups under `1st Legacy` and `2nd Legacy`, each headed by the ancestor's portrait and rank badge, and second-generation Sparks carry half the inheritance rate. So the group heading is not decoration, it is the provenance: the same `Speed ★2` means different odds depending on which group it sits in. A flattened Spark list that drops the grouping throws away the one fact that changes the number's meaning (Planner Rule 5).

**D-262. Affinity is entered or fetched, never inferred.** As written this rule asked that affinity "be deterministic and explainable". That is not achievable here, and stating it unchanged would license a guess. The client derives affinity from shared competitive history, principally overlapping G1 wins between the Trainee and the Legacy, plus canonical relationships. **The schema stores none of that**: `training_runs` has two nullable FKs to `umamusume` and no per-character race record, so no function over local data can produce an affinity value. The honest form is therefore: the Trainer enters the affinity the client showed them, or it arrives from a fetched dataset with provenance, and the UI displays it as recorded. It is explainable precisely because it is not computed. Any proposal to compute it is an Architect escalation and almost certainly a PRD §6 non-goal.

**D-263. A Guest Legacy is read-only, and absent beats disabled.** A borrowed friend Umamusume carries a `Guest` pill over its portrait. The Trainer cannot alter another player's Umamusume, so the controls that would mutate it are not rendered at all rather than greyed out. A disabled control invites a click and then refuses it; absence states the fact up front (D-26 family). Borrowing costs are the game's economy, not this tool's: record that the Legacy was a guest, never a Monies figure.

**D-264. Inheritance is a roll. Show the published chance and the observed result, never a projection.** The client's rates are public: stat Sparks 70 / 80 / 90% at ★1 / ★2 / ★3, aptitude Sparks 1 / 3 / 5%, Unique Skill Sparks 5 / 10 / 15%, and half again for second-generation Sparks, with affinity applied as a percentage multiplier. A row may read `Stat spark, 90% chance`, because that is a sourced constant. It may not read `likely to pass Speed`, because that is a guess about a random event, and Planner Rule 1 forbids simulation while Rule 4 requires every number to be a pure function of entered turns. This is the sharpest boundary on this screen: it is the one place in the product where the underlying game is explicitly random, and the temptation to model it is constant.

**D-265. Spark category colours are contained to the Sparks list.** Measured fills: stat `#3CB4F0`, aptitude `#FC84B4`, Unique Skill `#90CC30`. These mean *category*, and they collide on sight with §3.3, where blue already means "this stat decreased". The containment rule: no delta, badge, status pill or chart may reuse these three fills, and the Sparks list is the only surface where they appear. They also never appear without their word and their star count, so the colour is a scanning aid rather than the carrier of meaning (P5, D-12).

> **Withdrawn mapping (2026-09-29, R83).** While deciding how a Legacy Select form would label the Spark
> kinds, a mapping was put to the owner that paired **aptitude with `#90CC30` green and Unique Skill with
> `#FC84B4` pink** — the two fills transposed. It was approved in that form and is **withdrawn**: this
> rule's measurements are the authority, and they read stat `#3CB4F0` blue, aptitude `#FC84B4` pink,
> Unique Skill `#90CC30` green. The transposed mapping reached no tracked file — `git grep` over the
> three fills returns only this rule, `DESIGN.md` §6's kind table and `MECHANICS-TRANSLATION-TRIAGE.md`,
> all three already correct — so this note records the withdrawal where the fills live rather than
> correcting a shipped line. It is filed here because D-265 is what a future reader checks the fills
> against, and an approved-but-wrong mapping that leaves no trace is the one correction that cannot be
> found by grepping.

**D-266. Reproduce the client's own inconsistency rather than normalising it.** The slots read `Legacy 1` / `Legacy 2`; the Spark groups read `1st Legacy` / `2nd Legacy`. Two numbering styles for the same two objects in one screen. It is the client's inconsistency, not ours, and a Trainer reconciling the tool against the game needs the strings to match what they see. Invent a third form and the mapping breaks; silently unify the two and the labels no longer correspond to anything on screen.

**D-267. The "parent" ban cannot be applied to the schema in a UI phase.** UI copy uses Legacy and Ancestor throughout, which is free. But the shipped columns are `inheritance_parent_a_id` and `inheritance_parent_b_id`, the relations are `inheritanceParentA()` and `inheritanceParentB()`, and Game8's Global guide itself glosses Legacies as "parents". Renaming columns is a migration with a PRD citation, out of a documentation and mockup phase. Recorded as a known divergence between the copy rule and the identifier rule, for the phase that owns the schema to settle.

**D-268. The screen has more state than the schema can hold.** Legacy Select shows, per Legacy: the chosen Umamusume, its rank, whether it is a Guest, its own two ancestors, and a Spark list with per-Spark kind, target and star rank, plus an affinity value for the pairing. The `training_runs` record holds two character ids. Phase 4's provenance requirement is therefore **not met by the existing columns**, and closing it is a schema proposal on the `ADR-0003` pattern, most likely a typed json payload keyed to the run rather than a wide table of nullable columns. Documenting the screen without stating this would imply the data can be persisted today.

**Closed as a schema question 2026-09-29 (Slice 15, `ADR-0010`), and it is still open as a screen.** `training_runs.legacy_selection` is now one nullable json column read through `App\Models\Legacy\LegacySelectionPayload`, holding exactly the state enumerated above, with the two `inheritance_parent_*_id` foreign keys left where they are. Three things this does **not** do, so a reader of the paragraph above does not infer them: it builds no Legacy Select UI (D-260 still governs where a control may appear once one exists); it computes nothing from the payload, which is the half PRD §6 non-goal 3 keeps banned; and it stores one affinity grade for the chosen pair rather than the per-link grades §1.5.4 actually grades, so a screen that shows the four deeper links of the diagram needs the column revisited. `ancestors` holds names, not ids, because the Umamusume two steps back are frequently absent from the local catalogue.

## 10r. Run Completion state rules

Note on numbering: this was requested as "10p", but §10p is already Convergence and derivation
rules and §10q is Legacy Select, so it continues the sequence as §10r. Component anatomy lives
in `DESIGN.md` §6.28, not here, because §10x in this file is for rules and DESIGN §6 is for
anatomy.

**D-270. Run Completion is a terminal, read-only summary, and its headline number is entered, not computed.** Career Rank and Rating are the client's own score of a finished run, derived from final stats, race results and fans across the whole career. Recomputing them here would be a simulation and is barred by Planner Rule 1; Rule 4 requires every displayed number to be a pure function of entered turns, and the rating is not. So the Trainer records what the client showed, the UI labels it as entered, and an absent rating renders as absent rather than as zero or as an estimate.

**D-271. A Major Win may only be rendered if its source is real data.** Single races must exist in the scenario calendar or race dataset. Composite achievements such as `Senior Autumn Triple Crown` and `Triple Tiara` are not races, have no row in a race table, and must come from a curated title list. The failure this prevents is the one this project has hit repeatedly: a plausible-sounding string generated into a surface that looks sourced. A gold medal icon and a blue tier badge also encode different kinds of achievement and must not be interchanged.

**D-272. The fan class ladder is a fourth, separate fan scale and may not be merged with the others.** Measured thresholds: Bronze 5,000, Silver 20,000, Gold 50,000, Platinum 100,000, Star 160,000, Top Star 240,000, Legend 320,000, over a Debut/Maiden and Beginner base. These are distinct from race entry gates (350 to 25,000), URA event gates (60,000 / 70,000 / 120,000) and Trackblazer's Unique Skill gates (5,000 / 60,000 / 120,000 with bond). D-224 already forbade merging two of these; the ladder is a fourth and the same reasoning holds. It is also the scale that matters at run end, so the summary must name the tier reached and mark it, not leave a bare fan count to be looked up.

**D-273. Grade badge palette, corrected from frames.** `A` orange, `B`/`B+` pink, `C` green, `D` blue, `F` periwinkle, `G` grey, `S` gold. An earlier draft grouped `F` with `G` as grey; the client renders F in the same violet-blue seen on the Legacy Select frames, and greying it destroys the distinction that makes the ladder scannable. The letter always accompanies the colour regardless (P5, D-12).

**D-274. The `U/G` badge must not be rendered, mapped, or explained until it is understood.** Speed 1245 on a finished run carries a large violet `U` with a smaller grey `G` at its lower right, which is not a member of the G-to-SS ladder. Its meaning is unestablished. The temptation is to treat it as the top tier and assign it a threshold; that would promote an uninterpreted glyph into a rule and silently corrupt the grade function above roughly 1200, which is exactly the range a completed run lives in. Absent beats invented.

**D-275. The race tier set is not closed, because `EX` exists.** Pre-OP, OP, G3, G2 and G1 are the five this package had recorded; the Climax result header shows an `EX` badge, and the same badge appears on the Legacy Select frames, so it is a general class marker. This is direct evidence for D-153's ban on deriving a tier label from a grade code: an unseen code would be silently mislabelled rather than flagged.

**D-276. The final stats list is mandatory and the radar chart is optional.** The client offers a swap between them. The list carries values and grade badges; the pentagon shows shape without magnitude, so a run can be reviewed without it but not without the numerals. A chart that is the only carrier of a number is a chart placed because the space looked bare (D-94).

---

## 10s. Support card rules

Recorded from the deck-editor frame `Screenshot 2026-07-15 155016.png`, the six card detail panels
captured the same minute, and the export decode in `UMAMUSUME_REFERENCE.md` §1.4.7. These describe the
client, so they hold whichever way `ADR-0005`'s scope question is answered.

**D-277. `Friends` names the sixth slot, never the card in it.** The client frames slot six in pink and
captions it `Friends` under the level, and the captured deck puts a `stamina` card there. So the caption
is a slot role and the type chip on that same card still reads heart. A design artifact, mockup or schema
that treats "friend" as a property of the card is wrong in a way that is invisible until a Trainer puts a
stat card in slot six and the app calls it a Pal. The export's `type: friend` is a separate, real thing —
the 23 NPC staff cards — and the two must not be merged (D-20, G-38).

**D-278. The client's own word for the sixth type is still not on record.** "Pal" comes from Game8's
list, not from a captured string, and the export key is `friend`. Render the discipline names the client
uses — Speed, Stamina, Power, Guts, **Wit** — and store the export key `intelligence` behind that word at
the model boundary. For the two special types, label them from a capture or mark the label
`[Unverified]`; do not ship "Pal" as though the client said it.

**D-279. Effect magnitudes are interpolated, and the interpolation is shown.** The client's value at any
level is the **floor** of a straight line between the stored anchor levels (1, 5, 10, 15, 20, 25, 30, 35,
40, 45, 50), verified against the client at levels 30, 35 and 40 on five effects at once. Two obligations
follow: truncation, not round-half, because the client truncates and a one-off figure reads as a data
error; and the anchors the value was drawn between print beside it, per D-256. A materialised value-per-
level table is forbidden — it is 50 rows of derived data per effect that can disagree with the rule that
generated it.

**D-280. Never display a Unique Perk magnitude.** The perk has its own level axis, independent of the
card's (`Lvl 50 / 50` with perk `Lvl 30`; `Lvl 35` with perk `Lvl 40`), and no source in this repository
carries its values — for `[Dream Big!]` Tokai Teio one of the two named perk effects is not even present
in that card's effect list. So a perk block may show the name, and the level only where the Trainer
entered it, and nothing else. A percentage under a perk heading is an invented statistic (D-20, D-256).

**D-281. `Scenario Link` is derived from the scenario, never stored on the card.** The badge appears on
exactly those cards whose character is on the running scenario's linked list; in the captured Unity Cup
deck one card of six carries it and it is the deck's only linked character. A stored flag would freeze a
per-scenario fact into the card and would then be wrong in the other three scenarios, which is the same
class of error D-227 warns about for unqualified numbers.

**D-282. The slot's four corners are the contract.** Rarity ribbon top-left, type chip top-right, four
limit-break diamonds bottom-left, `Lvl N` bottom-right, with the friend-slot caption below that. The
diamond count is four because a card takes four breaks, and the level readout is
`base(rarity) + 5 × breaks` (bases 20 / 25 / 30), so a slot showing three filled diamonds and `Lvl 50` is
a defect, not a style choice. The type legend under the grid prints `xN` only for non-zero counts, and
that absence is the read a Trainer is actually after.

---

## 10t. Ingested third-party design prose

Numbering continues from §10s. The full triage of the incoming document is in
`MECHANICS-TRANSLATION-TRIAGE.md`; this section carries only the rule that outlives it.

**D-283. A design write-up is a source of patterns, never of values, copy, or vocabulary.** An external "game mechanics translated into UI/UX design language" document arrived with fifteen sections, correct shape, and mostly unusable content: it uses **Factor** where the Global client says **Spark** and **Motivation** where it says **Mood** as its primary vocabulary rather than incidentally; it asserts "typically 70+ turns", which is the D-136 defect with a larger number; it claims "Speed > 2000 unlocks Full Spurt" against its own §4.2 statement that 2000 *is* the hard ceiling; and three of its sections describe systems with no Global row in `scenarios.json`. The disposition is therefore per-axis, not accept/reject: the **pattern** survives translation ("a progress-gated unlock with a visible threshold"), the **number** attached to it does not, and the **copy** never does. This is not a new obligation — it is D-227's provenance requirement and D-256's derived-value rule applied one stage earlier, to the moment a figure enters a design doc rather than the moment it renders. The failure it prevents is the one this repository has been catching all session: a plausible constant arriving from prose, being seeded, then appearing on a surface that looks sourced.

**D-284. Adopting a pattern from prose requires an existing evidence anchor.** A pattern that only the write-up supports stays in `MECHANICS-TRANSLATION-TRIAGE.md`; a pattern that this package can already point at in a capture, a dataset field, or a measured frame is the one worth naming in `DESIGN.md`. Both of the adoptions made from this document pass that test — informed risk-taking is already implemented as the preview step and D-171's energy-beside-Confirm rule, and behaviour gates are already drawn as §6.5's second marker — and the ones that fail it, like support card anatomy, stay out of the design system regardless of how good the writing is.

**D-285. A document that cites this repository is a mirror, not corroboration.** A second incoming write-up (`docs/UMAMUSUME PRETTY DERBY — COMPREHENSIVE UX DELIVERABLES.md`) states at line 5 that its numbers are "drawn from the source-cited reference guide (compiled 2026-09-27)", and it is telling the truth: its Trackblazer cap row reproduces `UMAMUSUME_REFERENCE.md` §1.3.4's scenario-cap row — the cell now carrying the "Climax row corrected 2026-09-27" note — **verbatim, including the Guts/Wit transposition that `SCENARIO-DIFFERENCES.md` diagnosed**, and its mood table reproduces the `❌ UNVERIFIED` status that §1.1.6 and `ADR-0001` §6 both closed earlier the same day. Two consequences, and the second is the uncomfortable one. First, two artifacts agreeing is evidence of a shared source, never of the source being right — so a third-party doc that matches our reference cannot be cited back at ourselves. Second, when an incoming artifact disagrees with an authority here, check the authority's *internal* consistency before concluding the outsider invented the figure; in both cases here the "invention" was our own stale row, still circulating because the correction had been applied at one location and not the other. The corrected line 357 now names this risk in its own source cell.

**D-286. A correction is not finished until every copy is gone.** Changing a value in one table while it stands corrected-elsewhere-and-wrong-here is the defect that produced D-285, and it is invisible to review: the file you edited reads correctly. The unit of correction is the literal, not the file. This applies hardest to figures that mockups, seeds, configs and other documents quote — the cap rows and the mood percentages are both in that class. The same rule cuts the other way for deliberate historical text: `ADR-0001` §6 keeps the superseded glosses below a dated "RESOLVED" block, and `DESIGN.md` §6.22 records the pre-correction row as a withdrawn instruction. Those are citations of an error, not copies of it, and G-60's grep is read with that distinction, not applied blind. **A third category exists and is exempt:** the inert one-shot patch scripts under `docs/design-research/_scratch/`, which are how several of these literals were written into the corpus in the first place. They are provenance records, not live text, and they will keep matching every retired string forever — so G-60 counts hits in `docs/**` prose, `config/**`, `resources/**` and prototypes, and treats `_scratch/` as history unless a script is being re-run, in which case its literal must be updated before it executes.

**D-287. A correct rule with a wrong reason still fails review.** Two findings in this repository were of that shape, and both survived every check that looks at the rule rather than its citation. D-211's halved-gains rule is right and its source was misattributed — "The URA guide states plainly", in a file containing neither the word *halved* nor the figure *1200*; the sentence is from Game8's `[Global]` English pages, as `ADR-0002` amendment 1 already recorded. The same rule also credited `Fully Charged` to URA when it is an Our Grand Concert mechanic. D-187's banner rule is right and its stated evidence was an over-reading — six activity names, all from Unity Cup frames, offered as proof that layouts differ *between* scenarios in a corpus with zero Trackblazer training HUDs. Both defects had been copied outward before they were caught: D-211's into `CONSTRAINTS.md`, D-187's into three files. So review asks two questions of every rule, in this order: **is the requirement true, and does the reason given hold up to the evidence it names?** A rule that passes the first and fails the second will still mislead the next implementer, because the reason is what they generalise from.

**D-288. Theme tokens are declared `@theme static`, because Tailwind prunes what nothing references.** Tailwind v4 emits a `@theme` custom property only if some generated utility uses it. A token added for runtime theming but not yet referenced by a class is dropped from the stylesheet, and `var(--color-x)` then resolves to nothing — so the property inherits the element's existing value instead of failing. The practical danger is not a broken render, it is a **silent false pass**: a contrast probe on an undefined token measures whatever colour the probe element already had and reports a green result. Observed 2026-09-27 while verifying the light-first flip — `--color-sp-ink` was present in the dark override and pruned from the default theme, and a naive check scored it 6.56:1 against its real 5.05:1.

Two obligations follow. The declaration block is `@theme static` so every token ships whether or not a class has adopted it yet, and a contrast gate must read the **resolved property value** (`getComputedStyle(root).getPropertyValue`) and fail loudly on empty or unparsable, never sample through an element that already has a colour. Cost of `static`: 0.03 kB in this project's bundle. A third-party write-up that describes this as "just use CSS variables" is describing the failure, not a fix.

**D-289. A specification clause is a claim about the implementation, and it ages like one.** A line of `DESIGN.md`, or a note in a corpus file, that states a treatment, a value, a token, or what a source carries is a claim — not decoration — and it is worth only as much as the last read of the tree it describes.

Five have been caught this run by someone opening the file instead of the doc, and **two are still standing** — which is the honest shape of the problem and the reason the rule is written down rather than settled.

**Standing.** First, `docs/scenarios/09-global-race-calendar.md:59` states *"the map is trusted, the per-row assignment is not independently audited"* — the position R75 retired on 2026-09-29 by nullifying 115 per-row tier labels, in a file that never received the correction. Second, `09`'s Known-gaps bullet *"Per-character Goal races are not in the export… no GameTora key carries them"* is falsified by `ura-objectives`, which is in the manifest the file never enumerated; the dated withdrawal exists as a draft and has not landed, so the false sentence is still the one a reader finds.

**Caught and closed.** First, and the strongest case in the set, because the rule caught it in its own draft: **`D-285` — the rule saying a document that cites this repository is a mirror, not corroboration — carried a citation rotted by exactly the mechanism D-289 is about**, pointing at the Trackblazer cap row as "§1.3.4 line 357" while the corrected row had slid thirteen lines to `:370`. A rule about citation chains whose own citation is one link behind the tree it names is not a counterexample; it is the demonstration, and the demonstration is worth more kept as a dated erratum than kept as a live broken pointer — leaving it patched-never would have made the exception that lets a rule dodge itself. Corrected to cite the row by its heading and its own "Climax row corrected 2026-09-27" note. Second, `skills` was described as ten seeded names until `ADR-0011` measured the import at 1,910 rows with 623 reaching a Trainer. Third, two clauses in one paragraph of the root `DESIGN.md` §4.2 — "dateless rows show 'Unknown'", which turned `CLAUDE.md:23`'s **storage** rule ("no sentinel dates … use nullable date + `release_status`") into display copy that exists on no other surface, and "JapanOnly renders the amber notice", a treatment the token set cannot produce — were rewritten alongside the KI-35 filing (`bcd8abe`), both clauses in one dated withdrawal because they were written together and read together.

The clause about line numbers is not pedantry, and the case that pays for it is the rule's own: **a clause that cites a location should cite a section heading or a name, not a line number.** Line numbers are the fastest-decaying fact in a document anyone edits — `git log --since=2026-09-26 -- docs/UMAMUSUME_REFERENCE.md` shows six commits through that file since the cap row was corrected, which is how `D-285`'s pointer slid from `357` to `:370` without anyone touching `D-285`. **And the corpus owes the same to itself: any slice that edits a file with section-anchored references checks that the anchors still resolve, because the alternative is a rule that quietly stops pointing at anything.** `D-285`'s corrected citation is the demonstration — a rule about a citation chain whose own citation had fallen one link behind the tree it names, caught while the rule about it was being drafted. Anchoring by section or by name at least fails visibly, to a heading a reader can search for, rather than silently, to a line that still exists and says something else.

The obligation therefore runs in both directions, and this is the half that costs a reviewer something. D-285 asks that an incoming document not be cited back at ourselves; **D-289 asks that our own clauses not be cited at all without being re-derived against the tree first.** Any review that leans on a specification line as authority re-derives it, and a clause found stale is corrected by that review rather than carried forward — the correction lands in the same commit as the work that needed it, which is how §4.2 and KI-35 arrived together. It is the same discipline D-283 and D-284 apply to a write-up that arrives from outside, and D-287 applies to a rule's stated reason; what it adds is that the corpus owes it to itself, on a schedule, in the ordinary course of reading.

**The gate that cannot cover this.** G-60 greps *retired literals*, so it fires when a wrong value survives an edit; it has nothing to check when the value is right and the sentence about it goes stale, because no literal changed. That gap is the reason this is a review obligation and not a script.

---

## 11. Verification gates

These are the checks a reviewer runs on any UI change. They are additive to the root file's sequence.

**Scripted versus manual, so no gate reads as stronger than it is.** This table is a checklist, not a report of automation, and the distinction matters: a reviewer who assumes G-60 runs itself will repeat the exact defect it describes.

- **Enforced by `gate.py`** over the prototype HTML: **G-1** lore, **G-2** required forms, **G-3** terminology, **G-4** token discipline, **G-13** rendered-text sweep, **G-16** sample-data integrity, **G-27** `node --check`, **G-31** total-turn denominator, **G-32** cap denominator honesty, plus the D-79 em dash and D-84 emoji checks.
- **Manual or browser-assisted, no automation:** everything from **G-33 through G-60**, and the rows `gate.py` itself lists as not machine-checkable — **G-5** contrast, **G-6**, **G-7**, **G-9**, **G-11**, **G-12**, **G-14**, **G-15**, **G-17**. Contrast and computed style are measured in a browser by an agent, not by the script.

G-60 is the case worth naming: it is the check that would have caught the transposed cap row surviving one file while another was corrected, and it currently runs only when a person remembers to run it. Automating it needs a registry of retired literals, so that is offered as a decision, not assumed. Two side observations from writing this: `gate.py`'s own PASS banner under-reports its coverage (it omits G-27/G-31/G-32, which it does enforce), and this very bullet was first written from grep fragments with the manual and automated lists swapped — caught only by reading the file, which is D-287's point about checking the reason rather than the rule.

| # | Gate | How it is checked | Pass |
|---|---|---|---|
| G-1 | Lore | `make lore`, then the §3.1 pattern list over views, lang files, seeders, factories, exports, and prototype HTML | zero unexplained hits after context review |
| G-2 | Required forms | grep for `umamusumes`, `Uma Musumes`, and animal collectives | zero hits |
| G-3 | Terminology | grep the §4 banned-alternative column | zero hits in UI-facing strings |
| G-4 | Token discipline | grep views and CSS for hex literals and arbitrary-value utilities | zero outside the theme block |
| G-5 | Contrast | recompute every new text/background pair | AA at size, recorded in `DESIGN.md` §3.4 |
| G-6 | Colour independence | audit each state chip for a word or glyph | every state readable without hue |
| G-7 | UI states | render empty, loading, populated, error for every data view | all four present (root C-7) |
| G-8 | Stat bounds | input 1201 and 0 and a duplicate turn number | rejected with the bound named (D-31, D-32) |
| G-9 | Provenance | inspect any catalog field on a detail view | source URL and fetched date visible (D-33) |
| G-10 | Scope fence | grep the design artifacts for prediction, snapshot, sim, upload, sign-in | zero (D-36, D-37) |
| G-11 | Keyboard | complete a guided turn entry with no pointer | full flow operable (D-55) |
| G-12 | Reduced motion | toggle the OS setting, repeat the flow | legible, no long transitions |
| G-13 | Rendered-text sweep | assert visible text for `undefined`, `NaN`, `[object Object]`, and placeholder copy | zero hits |
| G-14 | Dose caps | count saturated hues, crimson elements, gold selections, glows per screen | within `DESIGN.md` §7.1 (D-82) |
| G-15 | Control honesty | click or tab through every interactive element in a prototype | works, or visibly marked static (D-81) |
| G-16 | Sample-data integrity | list every trainee and skill name in an artifact | all real Global strings, marked as sample (D-76) |
| G-28 | Lock-state distinctness | render a fan-locked cell beside a maiden-locked cell | distinguishable without reading the text (D-173) |
| G-29 | Conditional race step | advance a turn whose calendar slot is empty | no race step appears (D-175) |
| G-30 | Energy at commitment | measure the distance from the gauge to the Confirm control in the guided flow | gauge is adjacent, not header-only (D-171) |
| G-15a | Scenario legibility | open a run in each of the four Global scenarios | the header names the scenario and the bar scale visibly changes (D-160, D-161) |
| G-15b | Cap completeness | inspect any displayed cap | the stack shows what is included and what is untracked (D-162) |
| G-15c | Bound honesty | enter a stat of 1350 | the rejection names the tool's limitation, not a game ceiling (D-31) |
| G-16a | Fan lock legibility | inspect a locked race cell | the `fans_needed` figure is shown, not a padlock alone (D-151) |
| G-16b | Lock kinds | inspect a maiden-gated race and a fan-gated race | they are visually distinct (D-152) |
| G-16c | Tier inference | grep for grade-code to tier-label mapping | none in `database/seeders/` or `config/` since R72: the seeder joins per race on the dated extraction, so it holds no code-to-label constant at all. One known residue, held as a documented dissent rather than a pass: `GametoraRaceCatalogParser.php` still carries a five-entry map, unreconciled under R72 because the coordination condition needs the peer session idle, not merely its file clean. See D-153's dated exceptions |
| G-16d | Race outcome projection | inspect the race selection panel | eligibility only, no placing or win estimate (D-155) |
| G-17a | Event overlay | trigger an event panel and inspect what is behind it | scene, turn chip and stat band still visible (D-131) |
| G-17b | Delta colour | inspect every gain and loss in an event preview | gains orange, losses blue, no green or red (D-133) |
| G-17c | Run length assumption | grep views and JS for a total-turn constant | zero (D-136) |
| G-17 | Chart justification | for each chart, read its title | it is a question the chart answers (D-94) |
| G-31 | Run length assumption | grep views and JS for a total-turn denominator | zero — the brief's "~50 rounds" is unsourced, so `/ 48` asserts a fact (D-136) |
| G-32 | Cap denominator honesty | grep for a `/1000` stat ceiling | zero, unless a sourced scenario cap says so. An invented round number is not a cap (D-212) |
| G-27 | Script parses | `node --check` every inline script in each prototype | all parse. A static text scan passes happily on a page whose JS is broken, so the interaction stays invisible until a browser is opened (D-184) |
| G-33 | Scenario composition | render the same run header in all four Global scenarios | each shows only the resources that scenario actually has, and URA's strip is visibly the sparsest (D-220) |
| G-34 | Calendar applicability | open a Trackblazer run | no Race Calendar panel, and Grade Point progress appears in its place with a deadline and a no-carry-over note (D-221) |
| G-35 | Level provenance | inspect a Level 5 chip in each scenario | it names its cause: repetition, team rank, or a purchased level (D-222) |
| G-36 | Burst state completeness | advance a Unity Cup teammate through normal then Extreme burst | six distinct states render, "spent" is never terminal, and no energy penalty appears on Special Training (D-223) |
| G-37 | Zero-rate silence | trigger a sourced 0% risk (Extreme Burst facility, Race Fatigue after Late Dec, Good-Luck Charm) | the warning is removed entirely, not downgraded to "low" (D-231) |
| G-38 | Imported label check | grep artifacts sourced from wiki tables for `Wisdom` and `Motivation` | zero — both must read Wit and Mood (D-235) |
| G-39 | Source currency | for each seeded numeric, read its source's date | it postdates the 2026-07-01 Global rework, or the value is marked provisional (D-228) |
| G-40 | Matrix conformance | for each screen, read the §10n row matching its scenario | every widget present on the screen is required by that column, and every *absent* cell is genuinely absent (D-240) |
| G-41 | Undescribed scenario | render a run in a scenario with no guide and no fixture data | baseline strip plus known caps; no invented panel, no crash, no placeholder team or shop (D-241) |
| G-42 | Glyph singleness | list every glyph used and count the meanings each carries | one meaning per glyph; the flame currently carries three and must be resolved (D-251) |
| G-43 | Derived-number integrity | inspect any repeated figure across facility or teammate rows | it varies where the sourced table says it varies; a constant in a computed slot is invented (D-252) |
| G-44 | Cross-frame coherence | place two scenario mockups side by side | bar fill, tint, radius and chip palette are identical; only scenario-composed content differs (D-250) |
| G-45 | Background layer audit | inspect the dimmed or scrolled-past region of every overlay screen | no nav label, activity name or sample state contradicts the scenario in focus (D-254, D-255) |
| G-46 | Derived-value disclosure | find every letter or number the artifact computes rather than records | its rule is printed in the UI and labelled client-sourced or ours (D-256) |
| G-47 | Badge contrast | measure all nine grade badge letters against all nine fills, in both themes | ink-strong passes 9.00 and above; **white on the light fills measures 1.20-1.44 and is banned** (D-258) |
| G-48 | Single spine | place the four scenario renders side by side in one artifact | identical section order and positions; only the goal region differs (D-257) |
| G-49 | No inheritance prediction | grep the Legacy surface for projected outcomes | zero. Published chances and observed results only; no "likely", "expected", or rolled value (D-264) |
| G-50 | Spark provenance | inspect any Spark row | its Legacy group and generation are visible, since generation halves the rate (D-261) |
| G-51 | Guest affordance | inspect a Guest Legacy | mutating controls are absent, not disabled, and no in-game currency figure is shown (D-263) |
| G-52 | Colour containment | grep for the three Spark fills outside the Sparks list | zero occurrences (D-265) |
| G-53 | No computed score | inspect Career Rank and Rating on a completion surface | they are entered values labelled as such, never derived, never defaulted to zero (D-270) |
| G-54 | Win title provenance | for each Major Win shown | it resolves to a calendar race row or to the curated composite title list (D-271) |
| G-55 | Fan scale separation | count the fan scales rendered in one widget | one. Entry gates, event gates, skill gates and the class ladder never share a readout (D-272) |
| G-56 | No uninterpreted glyph | grep grade rendering for `U/G` | absent, and no threshold is assigned to it (D-274) |
| G-57 | Mood arrow present | render all five mood tiers, plus the same pill in the log and the guided preview | each carries its directional arrow (up, up, neutral, down, down) beside the word. A colour-plus-word pill with no arrow fails (D-259) |
| G-58 | Deck slot integrity | build a deck with a stat card in slot six and a non-linked character in slot three | slot six is captioned `Friends` with its own type chip unchanged, no `Scenario Link` badge appears on the non-linked card, and every slot's diamonds and level agree with `base + 5 × breaks` (D-277, D-281, D-282) |
| G-59 | Prose-sourced figure | take every numeric in a design artifact that arrived from a write-up rather than a capture or dataset, and name its source | each traces to a client frame, a dataset field, or a measured value with a date. An untraced figure is either `unsourced` in this file or absent from the artifact — it never reaches seed data or UI copy (D-283, D-284) |
| G-60 | Correction completeness | after retiring a sourced literal (a cap row, a percentage, a tier string), grep the **retired** literal across all tracked files | zero hits, or every remaining hit is a line that quotes it as withdrawn/superseded and says so in place. A file that reads correctly in isolation is not evidence the correction propagated (D-285, D-286) |
| G-18 | Both themes | toggle to dark, repeat G-5, G-6, G-7 and G-13 | every pair passes in dark too; no component is light-only (D-103, D-105) |
| G-19 | Theme fork check | grep components for `dark:` utilities and theme conditionals | zero outside the theme override block (D-101) |
| G-20 | No flash of wrong theme | reload with the OS set to dark | first paint is already dark (D-104) |
| G-21 | Font identity | computed `font-family` of body, a heading and a numeral | resolves to the declared rounded face, never to a banned grotesque (D-110) — **RULING B5 (2026-09-27): C-8 exemption granted; system stack (`ui-sans-serif, system-ui, sans-serif`) is the shipped identity. G-21 technically fails on the system grotesque but is exempted per KI-6. The rounded face (Nunito/M PLUS Rounded 1s) is parked pending a C-8 dependency approval that has not been granted. The exemption is recorded here so the gate's failure is a known decision, not noise.**
| G-22 | Weight availability | request 500, 600, 700, 800 from the font's own CSS | all four served, no synthetic bolding (D-110) |
| G-23 | Icon weight | render every glyph at 20px solid black | legible as a filled shape, not as an outline (D-111) |
| G-24 | Sheen geometry | inspect the primary button's gradient stops | a single hard split, not a feathered range or a vertical ramp (D-112) |
| G-25 | Tint discipline | sample the six band tints **against the surface they are actually painted on**, which is `--raised` in the stat band | no saturated stat hue anywhere; dark set measures 1.09-1.11 and passes, light set measures 1.10-1.21 and **fails the documented 1.04-1.14**, pending the §11.9 ruling on which surface is normative (D-113, D-114) |
| G-26 | Micro-strip equivalence | read a run card with CSS hidden | the grade text is still announced (D-115) |


G-13 deserves its note: a sweep that only checks element presence and ARIA attributes will pass a button that renders "Add turn: NaN". Assert on rendered text.

---

## 12. Floor (design-side, never)

- Never weaken a root `CONSTRAINTS.md` threshold to make a design fit. Escalate to the human owner (root C-4 path, `AGENTS.md` escalation 4).
- Never add a schema column to make a mockup work. That is an Architect escalation with a PRD citation, or it does not happen.
- Never add a dependency (font, icon pack, chart library, animation library) without approval. Root C-8.
- Never ship a saturated chrome surface under text. D-3.
- Never use equine vocabulary in any artifact, including a prototype that will be thrown away. NFR-6 has no scratch exemption. <!-- lore-ignore-line class=1 cite=NFR-6 -->
- Never promote an `❌ UNVERIFIED` reference item into UI copy. D-20.
- Never seed a game numeric without a server qualifier and a source date, and never carry a JP value under a Global scenario name. D-227, D-228.
- Never delete or stub a state view to pass a gate. Root floor: no stub bodies.
