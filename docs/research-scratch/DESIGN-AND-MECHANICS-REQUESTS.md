# Design and Mechanics Requests

## Provenance

This document consolidates the following source files verbatim (no summarization, no deduplication):

- `docs/requests/game-mechanics-condition-labels.md`
- `docs/design-research/verification/design-pass-trainee-detail-2026-09-29.md`
- `docs/flows/create-run-and-legacy-select.md`

---

## game-mechanics-condition-labels.md

### Request: Verified Global mood-pill colours (three lower tiers)

**To:** Game Mechanics Agent
**From:** Pre-Dev / Documentation Agent
**Date:** 2026-09-28
**Blocks:** measured mood-pill colour tokens (design-research §3.6/§3.7 follow-up); nothing else. Neutral tints ship meanwhile.

#### 1. Context and what is already settled

- The five mood tier **words** are measured client strings and are settled:
  `app/Enums/MoodTier.php` (GREAT/GOOD/NORMAL/BAD/AWFUL as verbatim `[Global]`
  values, commit `a7cabc0`). This request does **not** re-ask them.
- Pill **colours**: `docs/design-research/DESIGN.md` §3.7's evidence note records
  `mood-peak` and `mood-good` as measured; the three lower pills' colours are not.
- Owner ruling (2026-09-28, R-5 in the sprint authorization): UI must not present
  verified-looking colours for unverified tiers.

#### 2. Exact ask

For each of the three lower tiers (`NORMAL`, `BAD`, `AWFUL`) of the client's Mood
panel pill:

1. The verbatim `[Global]` client colour as rendered: hex (or rgb), sampled from
   the pill fill, plus the pill's text colour if it differs.
2. Source: which asset or page it was read from (client screenshot with frame
   reference, or `docs/game-screenshots/` file name and pixel region), with
   fetch/verification date.
3. Confidence tier per the reference document's flag scheme (`[S]` official /
   `[A]` guide / `[B]` data export; `⚠️ STALE` if the source predates 90 days).
4. Divergence note if the `[JP]` client differs visibly; the Global value is what
   binds (Global-scope ruling, `docs/UMAMUSUME_REFERENCE.md` preamble).

#### 3. Acceptance criteria

An answer is valid only if every colour carries: source reference, date, server
tag (`[Global]` or `[Both]`), confidence tier. A guess or community-table
approximation is **not** an answer; the correct response when nothing verifiable
exists is exactly:

```text
❌ UNVERIFIED: No current source found.
```

...which is itself useful: it locks neutral tints in as the durable design instead
of a placeholder awaiting drift.

#### 4. Compliance clause for anything accepted

Every returned string or colour used in UI must pass, before `DESIGN.md`/token
adoption: C-4 lore gate (no equine framing in labels), R-02 (no em dashes in any <!-- lore-ignore-line class=1 cite=C-4 -->
accompanying copy), the `N/A` disclosure ruling, and the provenance requirement
(every fact stores source + fetched date; research D-285/D-286 apply: one
correction must propagate to every copy of the literal).

#### 5. Interim rule (binding meanwhile)

```text
Use neutral tints for the three unverified mood/condition pills.
Do not present unverified colours as measured client values anywhere
(tokens, mockups, prototypes, or shipped CSS).
```

---

## design-pass-trainee-detail-2026-09-29.md

### Design pass — trainee detail page and the run screen's skill selector: record

Date: 2026-09-29
Type: **design pass**, not a slice. It produced design briefs and register entries and shipped no view,
so it is not named `slice-N`; that prefix tells a reader to expect a code diff in the block, and there is
none. The date suffix matches the convention the other verification records use.
Tree at the pass: `edb03d5`. Tree at the block's landing: `d0422ce`. Tree at the time of writing this
record: `bc11d9b`, and §6 explains why that difference is the most useful thing on the page.
Rulings: R-1 through R-6. Deliverables: D-1 through D-4, plus four premise corrections and one refusal.

---

#### 1. The block, as landed

Four files, four commits, docs-only. Steps 2–5 landed as one set because KI-35 cites the §4.2 rewrite and
the rewrite cites KI-35; a half-landed block would leave the register describing a specification defect the
specification does not yet acknowledge.

| Step | Commit | File | Content |
|---|---|---|---|
| 2 | `84faed8` | `KNOWN-ISSUES.md` | KI-33 (+ R-2 and R-5 sub-findings), KI-35, KI-36, KI-37, status block |
| 3 | `bcd8abe` | `DESIGN.md` | §4.2 rewrite, both stale clauses as one dated withdrawal |
| 4 | `da8d6c5` | `docs/design-research/SKILLS-GAPS.md` | G-SK-13's two stale clauses |
| 5 | `d0422ce` | `docs/design-research/CONSTRAINTS.md` | D-289 |

**Step 4 landed as an edit, not a no-op — and that was checked, not assumed.** The sequencing brief warned
that the peer's `edb03d5` *may* have already incorporated the two G-SK-13 wording diffs. Reading the entry
first showed "are the designed answer and remain unbuilt" still on disk and the interim-link sentence
untouched, so both applied verbatim. Landing a diff against a version that no longer exists would have been
the same class of error the pass was closing.

**KI-34 is deliberately reserved, not filed.** The per-character goal-race entry is drafted (it is the
`ura-objectives` finding from the previous pass) and its correction to
`docs/scenarios/09-global-race-calendar.md`'s Known-gaps bullet is written but unlanded. The number is
claimed for whoever lands it. See §5.

---

#### 2. D-289's self-corrections, in the form they were caught

D-289 landed with four corrections made to its own draft during the pass that wrote it. Listed as caught,
not cleaned up, because the rule's first four tests happened inside the paragraph that states the rule and
the sequence is the evidence.

- **The "three weeks" span.** The draft said `docs/scenarios/09-global-race-calendar.md:59` had been
  standing "three weeks after R75 retired that trust." R75 landed **2026-09-29** — the same day. The
  sentence came from the incoming brief and was copied without checking, which is the exact mechanism D-289
  forbids, occurring while writing D-289. Replaced with the date. The instance survives the correction:
  `09:59` still reads *"the map is trusted, the per-row assignment is not independently audited"*, and that
  is still the position R75 retired.
- **`:401` → `:405`.** KI-35's first draft cited `resources/views/runs/show.blade.php:401` for the `"None."`
  copy. The line is **405**. Caught by grepping for the literal before committing — the same command that
  produced the correction is the one the entry now cites.
- **The "eight places" cut.** A draft said `docs/UMAMUSUME_REFERENCE.md` had "gained lines in eight places
  since that row was corrected." No command produces that number; it was written as colour around a real
  fact. Replaced with what a command shows — six commits through the file since 2026-09-26 — and the offset
  stated as `357 → 370`. The rule is about citations a reader can re-derive; an unpinnable count in the
  middle of that rule is a counter-example wearing the rule's clothes.
- **The forward reference removed.** KI-35's first draft pointed at a design-brief file for the
  section-by-section states. **No such file exists.** The entry now says so in its own words and names where
  the brief should land. A register entry pointing at a file nobody wrote is the defect KI-25's withdrawn
  closure was caught for, and filing a second one in the same week, in the pass that was writing the rule
  about it, would have been the pass's own worst exhibit.

The point of listing these rather than one: D-289 was exercised five times around the pass that wrote it,
and every one was caught by opening a file instead of trusting a sentence. That is what the rule looks like
in operation, which is why it is recorded here rather than only asserted in `CONSTRAINTS.md`.

- **The fifth was caught after this record was committed, and it was caught in this record.** `KI-37` cited
  `KNOWN-ISSUES.md:1347` for KI-29's heading, and §5 below cited `:1375` for KI-30's; both were correct
  when written and both rotted **thirteen lines** inside the same commit set, because step 2's status block
  inserted thirteen lines at the top of the file. `KI-37` and §5 now cite the headings by name. The rule's
  own text says a line number is the fastest-decaying fact in a document anyone edits, and the proof is
  that the pass writing that sentence decayed two of its own citations before the sentence finished
  landing — no gate saw it, because no literal changed and nothing was wrong except a pointer.

---

#### 3. The three departures from the draft, with reasons

- **`stable anchor` → "a section heading or a name".** Not a synonym swap, and the scope reason is the
  primary one. "Anchor" is HTML-shaped — it reads as an `id=` you can link to — whereas the citations that
  actually survive a line shift in this corpus are symbols: `GametoraCharacterParser::debutForms()`
  (`app/Services/DataPipeline/Parsers/GametoraCharacterParser.php:99`),
  `Skill::scopeAvailableOnGlobal()` (`app/Models/Skill.php:80-85`), `TrainingRunController::syncSkills`
  (`:546-557`). A rule that says "cite an anchor" would exclude half the working citation forms by
  connotation. Secondary reason, stated because it is true: `stable` is a docs-mode lore pattern, so the
  phrase would have added a gate hit on an adjective with no equine sense — allowed in context, but noise
  in a count a human watches. Both reasons are in the landed text's favour; the scope one is the load-bearing
  one.
- **`three weeks` → the date.** Command-verified, per §2.
- **`eight places` → six commits and the `357 → 370` offset.** Command-verified, per §2.

---

#### 4. The refusal, verbatim

> *"I'd be a poor witness for the rule if I copied them into it."*

The draft R-6 handed over contained five citation claims. Three survived contact with the files; two did
not. Refusing the two — rather than softening them into "approximately" or leaving them unverified in a
permanent rule about verifying citations — is the reason the paragraph is trustworthy, and the sentence is
kept here unsmoothed because the shape of the refusal is the deliverable. A rule about citation discipline,
written from a citation list that was not checked, would have been its own counter-example on the day it
landed. The refusal is not modesty about the wording; it is the mechanism, running in the one place it had
to run to prove the rest of the corpus's reading discipline is real rather than performed.

---

#### 5. KI-34's reservation, so the gap is not read as loss

The register carries a numbering hole: `grep -oE '^## KI-[0-9]+'` returns KI-1..15 and KI-17..37, with
**KI-16 missing because it was renamed** — `KI-30` was *filed as* KI-16 on
`fix/frontend-audit-2026-09-28` and renumbered at merge (the `KI-30` heading), and the renumbering left
the old number behind. The hole read, for several slices, as a lost entry, because nothing claimed it.

**KI-34 is the opposite shape and the record notes the difference.** It is not missing and not lost: it is
claimed, empty, and reserved for the per-character goal-race entry whose draft exists and whose `09`
correction is written. The status block in `KNOWN-ISSUES.md` says so in those words rather than leaving the
gap to be rediscovered as a mystery. That distinction — a hole someone owns versus a hole nobody remembers —
is what the register has needed since KI-16 went missing, and it is the reason this section exists rather
than a one-line footnote.

---

#### 6. What moved after the block landed, and why it belongs in this record

Between the block (`d0422ce`) and this file, `bc11d9b` merged the character-card layer and the trainee
selector onto `master`. Three consequences, and they are the live demonstration of D-289 rather than a note
about it:

- **KI-33's cause sentence is already stale; its finding is not.** The entry says `character_cards`,
  `CharacterCard` and `ADR-0008` "are absent from this ref" (inside `KI-33`'s cause paragraph). They are now present:
  `app/Models/CharacterCard.php`, `database/migrations/2026_09_29_120100_create_character_cards_table.php`,
  `docs/adr/0008-character-card-catalog-layer.md`. The table's columns are `card_id, umamusume_id, title,
  rarity, global_release_date, is_debut_form, unconfirmed` plus the provenance set — **no `skills_innate`
  and no `skills_unique`**, and `GametoraCharacterCardParser` reads no `skills` key. So step 1 of KI-33's
  fix direction (merge the card layer) is **done**, the gap itself is **unchanged**, and the entry's
  dependency ordering is now wrong in a way that makes the work look harder than it is. Nothing to file: the
  defect is the sentence, and D-289 is what tells the next reader to re-derive it before acting.
- **`trainee-combobox.ts` now exists.** `resources/js/trainee-combobox.ts` is a WAI-ARIA APG combobox —
  `role="combobox"` with `aria-expanded`, `aria-autocomplete="list"`, `aria-activedescendant`, a
  `role="listbox"` popup, and trainee group headers deliberately not options. The premise correction this
  pass filed ("nothing to reuse; specify the pattern") was **true against `9e896d5` and false against
  `bc11d9b`**. D-3's reuse paragraph should now be read as converged rather than open: the pattern exists on
  the ref, and the selector work is to apply it to the skills field rather than to design it.
- **`09:59` and the goal-race bullet are untouched by the merge**, so the two live instances D-289 names
  are still standing, and KI-34's reservation still has work behind it.

This section is in the record and not in a new register entry because the block is closed and a record that
files its own entries has stopped being a record. The three bullets are the pass's best evidence: the
corpus's own claims about the tree decay in days, and the decay is invisible to every gate — no literal
changed, so G-60 has nothing to grep, and the tests pass. That is precisely the gap D-289 says a reviewer
has to close by reading.

---

#### 7. What this pass produced that is not a commit

D-2 (trainee detail design brief) and D-3 (skill selector design brief) **have no files**. They were
delivered in the pass reports and live only there. `docs/design-research/` holds no `brief-*.md` for either,
and KI-35's text says so explicitly rather than citing a path that would mislead. If they are to be durable
they need their own commits, and D-3 in particular should be rewritten against `bc11d9b` before anyone
builds from it, because its opening premise — that the combobox pattern does not exist on this ref — is now
the stalest sentence in the set.

**Not done by this pass, by design:** no view, token, schema, migration, controller or test was touched. The
two pages designed here — `resources/views/catalog/show.blade.php` and `resources/views/runs/show.blade.php`
— are byte-identical to how they were found, and the defects filed against them (KI-35, KI-36, KI-37) carry
their own fix directions for the slices that will edit them.

---

## create-run-and-legacy-select.md

### User Flow: Create Run and Legacy Select

**Type:** 5 (user flow)
**Date:** 2026-09-28 (pre-dev agent; evidence from HEAD `9134206` plus noted
uncommitted work)
**Governing rules:** PRD FR-C / US-3 / US-4, `CONSTRAINTS.md` C-7 as scoped by
`docs/adr/0007`, ADR-0002 (bounds), ADR-0003 (schema), research constraints
D-260..D-268, root `DESIGN.md` §6 terminology.

#### 1. Purpose

Pre-run flow: a Trainer creates a training run for one Umamusume, optionally names
its scenario and two inheritance parents (Legacy Select), then starts logging
turns and skill states. This is the product's only ancestor-selection surface and
it is strictly pre-run (D-260).

#### 2. Current implementation status (evidence, not intent)

| Piece | State | Evidence |
|---|---|---|
| Routes `runs.create/store/show/update/destroy`, `runs.turns.*`, `runs.skills.sync`, `runs.export` | Live | `routes/web.php:15-26` (HEAD) |
| Run fields: umamusume_id, scenario (free text), status, notes | Live | `StoreTrainingRunRequest` HEAD lines 29-34 |
| Inheritance parents in validation | Accepted by the Form Request | `inheritance_parent_a_id` / `_b_id`, nullable exists rules (HEAD) |
| Legacy Select UI (picker during create) | **Not implemented** | `resources/views/runs/create.blade.php` at HEAD has no parent fields |
| Turn logging with stats 0..1200, SP, condition, MoodTier | Live | `StoreTurnEntryRequest:40-53`, `app/Enums/MoodTier.php` |
| Skill states Suggested/Acquired/Skipped | Live | `runs.show` sync form, `TrainingRun::setSkillStatus` |
| Scenario config driving panels/widgets | In flight (uncommitted) | `config/scenarios.php` tracked at HEAD; component work dirty in tree |
| `ScenarioSlot`, richer race/inheritance tables | In flight (untracked models/migrations) | working tree only; not committed |

Claim limit: nothing here asserts that six-slot Legacy data persists; see §7.

#### 3. Preconditions

1. Catalog contains at least one Umamusume (manual `umamusume` rows or
   `uma:fetch gametora-characters` + review promotion). Unmet → see §4.
2. Run creation needs no auth, network, or engine state (local-only, NFR-1/2).
3. Legacy Select (when built) additionally needs candidate parents: completed runs
   marked as available ancestors. Today that concept has no table; see §7.
4. Scenario selection is free text today; `config/scenarios.php` exists but the
   create form does not offer a picker. Do not describe a picker that is not in
   HEAD.

#### 4. Empty-state remedies

| Missing | Remedy (required rendering) |
|---|---|
| No Umamusume in catalog | Create form shows empty state: name the two fills (seed for illustration; `uma:fetch` for facts) and link `/umamusume`; submitting is not possible, so the form states why rather than disabling silently (absent-beats-disabled pattern, D-263) |
| No runs yet (`runs.index`) | Existing empty card pointing to "New run" (live: `runs/index.blade.php` @empty) |
| No parents available for Legacy Select | Create the run **without** parents (nullable FKs); do not render empty parent slots (§7) |
| No scenarios picker | Not an error state; scenario stays optional free text |

#### 5. Step sequence

1. Trainer opens `/training-runs/create` (`runs.create`).
2. Selects the Umamusume (required select; existing data source: catalog).
3. Optionally types a scenario name, picks status (default Active), adds notes.
4. Optional Legacy Select, when implemented: a pre-run step choosing exactly two
   inheritance sources, labeled **Parent A** / **Parent B** (identifiers stay
   `inheritance_parent_a_id` / `_b_id`, D-267). Copy may use "Legacy"/"Ancestor"
   and "Inspiration" for the mechanic (root DESIGN §6). Never per-turn reachable
   (D-260): after the run exists, its parents are fixed; editing them from a turn
   view is a defect, not a feature.
5. `POST /training-runs` (StoreTrainingRunRequest). Validation failure returns to
   the form with field errors; success redirects to `runs.show`.
6. On the run page the Trainer logs turns (`runs.turns.store`, `StoreTurnEntryRequest`)
   and sets skill states (`runs.skills.sync`, `StoreRunSkillRequest`); export at
   `runs.export` (csv/json).

#### 6. Validation rules (current truth)

- Turn stats: `0..1200`, `turn >= 1`, unique per run
  (`StoreTurnEntryRequest:40-46`); UI inputs carry `max="1200"`.
- **Warning:** ADR-0002 accepts a future `0..2000` bound. It is not implemented.
  No doc, label, denominator, or help text may present 2000 as accepted today.
  When it lands, the slice must update validator, UI max attributes, soft-cap vs
  scenario-ceiling display (ADR-0002 UI rule), tests, and docs **in the same
  change** (see KNOWN-ISSUES adjacent watch item in the 2026-09-28 audit).
- Mood: nullable, must be a `MoodTier` enum value (measured client strings,
  a7cabc0). Condition pill **colours** for the lower three tiers remain
  unverified: neutral tints only (`docs/requests/game-mechanics-condition-labels.md`).
- Disclosure of untracked numbers: `N/A` + tooltip, never `0`, never an em dash
  (settled ruling; KI-7).

#### 7. D-268 non-persistable panel (binding wording)

Current persistence limit: only **Parent A** and **Parent B** (two nullable FKs to
`umamusume`) may be stored, and only when a UI exposes them; no rank, guest flag,
grandparent circle, Spark list, affinity, or per-Legacy metadata is persistable by
committed schema. Any richer Legacy Select must be marked non-persistable, or the
controls disabled/absent, until a schema proposal on the ADR-0003 pattern lands.
Documenting or mocking the six-slot screen without this notice implies data can be
saved today; it cannot.

#### 8. Error states

| Error | Trigger | Handling |
|---|---|---|
| Validation (422/redirect with `$errors`) | bad stats, duplicate turn, unknown umamusume/parent id | field errors on the form; turn errors render in the runs.show error list |
| 404 | unknown run/slug; turn not owned by the routed run (`abort_unless` in `updateTurn`/`destroyTurn`); unknown export format | framework 404 page; API gets the `{error:{code,message}}` envelope |
| Engine/fetch failure | only upstream of preconditions (catalog empty) | NFR-2: prior data untouched; run flow never depends on live fetch |
| Loading | initial navigation: browser-native (ADR-0007 clause 1); user-initiated async (future refresh) requires explicit indicator (clause 2) |

#### 9. Lore-safe copy guidance

- Characters are Umamusume (humanoid race); no equine vocabulary or iconography in <!-- lore-ignore-line class=1 cite=C-4 -->
  any label, tooltip, empty state, or export header (C-4, DESIGN §6).
- Ancestors: "Parent A" / "Parent B", or "Legacy"/"Ancestor"; mechanic name
  "Inspiration"; screen/widget name "Legacy Select". Never sire/dam/mare/foal or <!-- lore-ignore-line class=1 cite=C-4 -->
  breeding framing, including in `title` attributes and CSV headers. <!-- lore-ignore-line class=1 cite=C-4 -->
- No unverified client strings as UI copy: words pass only when measured and
  provenance-recorded (D-20; MoodTier words are, pill colours are not).

#### 10. Out of scope

Mid-run ancestor editing (forbidden by D-260), six-slot ancestor persistence
(no schema), affinity computation (D-262: entered/fetched, never inferred),
spark probability simulation (D-264: show published rates only).