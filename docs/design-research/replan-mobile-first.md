# Replan — mobile-first UI, Legacy Select, and the reachability of wide regions

Deliverable of Slice 16's UI/UX pass, 2026-09-29. Written against the tree at `ad7cb0a`.
**Status: §1 accepted 2026-09-29; §3 and §5 remain proposal.** The D-40 addendum in §1 is accepted and lands
with Task 16, which carries it into `CONSTRAINTS.md` under D-40 and the matching replacement line into
`DESIGN.md` §2.3. The Spark tokens in §3 are **not** accepted and stay held against R83; the Legacy Select
question in §5 stays deferred. T3 of this slice still stops before any token is committed.

Accepted with one amendment, because the addendum was put for acceptance before the shell was read: §1's 768px
floor is unchanged and holds as written, but `resources/views/components/layout.blade.php:55` sets `main` to
`max-w-5xl`, not the `max-w-6xl` the Task 16 draft assumed. The two-region split therefore sits at the `lg:`
breakpoint (1024px) inside a container that is already 1024px, and the 3fr/2fr columns are viewport-invariant —
left ≈ 581px, right ≈ 387px at 1024px, 1280px and 1440px alike. Nothing in §1 contradicts this: the floor rule
governs what the two wide regions do below 768px, and that is exactly as drafted.

Rules this document is written under, and the reason its sections are ordered as they are: R82 (the D-40
reconciliation is a ruling to be landed, not a negotiation to be had), R83 (the Spark fills cost contrast
work, and the approved kind mapping was wrong), R84 (WCAG 2.1 AA is the mandate; 24×24 is not an obligation
here), R85 (no claim may rest on rendered attributes when it is about behaviour).

Every figure below is either a browser read, a computed value from a named token, or marked
**[Unverified]** with the reason. Nothing is carried over from a previous slice's prose.

---

## 1. Step 0 — D-40 versus the 768px floor, written as a ruling to be landed

### Why this is first

`docs/design-research/CONSTRAINTS.md` D-40 currently ends with:

> Below 1024px the two regions stack on the narrow axis. The brief does not require mobile and no
> mobile-first compromise is accepted in exchange for desktop density.

`DESIGN.md` §2.3 now carries, from `b8c0a54`:

> **768px is the supported minimum** … below it the two wide tables scroll rather than reflow.

Those two sentences are a live contradiction in the working tree. D-40's last line refuses mobile as a
design driver; §2.3 names a mobile width as supported. R85 withdrew KI-25's closure precisely because §2.3's
number was never measured, so the pair cannot be reconciled by pointing at the newer file — the newer file
is the one with the unproven figure.

### The reconciliation, as addendum text

This block is written to be pasted under D-40 verbatim, in the same commit as the Spark tokens if the owner
accepts §3, and in no commit at all otherwise. It is a ruling in the owner's voice with the measurements
that constrain it attached, not an option list.

> **Amended 2026-09-29 (R82): 768px is the supported minimum, and below it the wide regions scroll rather
> than reflow.** This rule's last sentence — "no mobile-first compromise is accepted in exchange for desktop
> density" — stands, and it is now scoped rather than removed. What is refused is *reducing the information
> on a desktop surface to make a phone layout fit*: no column is dropped from the turn log, no cell is
> hidden from the race calendar, no stat band collapses, and the mood pill keeps its arrow and its word.
> What is accepted is that a 460px table inside a 343px viewport is a **scrolling region with a tab stop**,
> because the alternative — the document itself scrolling sideways — was measured and is worse: at 390px the
> turn log's region carries the whole 117px of overflow and `window.scrollX` stays 0.
>
> Below 768px the contract is therefore exactly two things, and no more: the two wide regions stay complete
> and become traversable, and everything else reflows as it already does. Nothing in this amendment makes a
> phone a design driver. `PRODUCT.md`'s "Desktop-first primary use" and PRD §2's desktop browser are
> unchanged; 768px is a floor the tool tolerates, not a target it composes for.
>
> **What this amendment does not buy.** The figure is a supported *minimum*, measured at one viewport
> against one run. It does not assert that the run detail screen is comfortable at 390px, and the race
> calendar's own region has never been measured at any width (§2, M-6). If a future pass shows the calendar
> region cannot be traversed or its 224-cell grid is unreadable at the floor, the floor moves to 1024px and
> this paragraph is superseded by the measurement, not by taste.

**Owner's decision line.** Accept as written → it lands under D-40 with the §3 tokens in one commit.
Reject → §2.3's 768px bullet is deleted, KI-25 stays OPEN with no contract attached, and the two documents
stop disagreeing by removing the newer one rather than by amending the older.

---

## 2. Read-only measurement table

Method. Chromium via Playwright, real key events through the CDP keyboard layer, geometry from
`getBoundingClientRect()` and `scrollWidth`/`clientWidth`. No view, token, test or `DESIGN.md` line was
edited to produce any row. Server on `127.0.0.1:8177`, a port nothing was listening on before this pass.

| # | Measurement | Value | Instrument |
|---|---|---|---|
| M-1 | `window.innerWidth` at the 390 viewport | **390** | browser |
| M-2 | `documentElement.clientWidth` | **375** — the 15px difference is the vertical scrollbar, so the content box is not 390 | browser |
| M-3 | `documentElement.scrollWidth` | **375** → **the document does not overflow horizontally** | browser |
| M-4 | Turn-log region: `clientWidth` / `scrollWidth` / max scroll | **343 / 460 / 117** | browser |
| M-5 | Arrow-key traversal, `div[role=region]` focused, 3 × ArrowRight | `scrollLeft` **0 → 117** (full traverse), `window.scrollX` **0** throughout | browser, real key events |
| M-6 | Race-calendar region | **did not render on the page measured**, so untraversed and unmeasured | browser (absence) |
| M-7 | Turn-log table width | **460px** | browser |
| M-8 | Per-column widths, left to right | **42, 52, 65, 53, 42, 36, 28, 77, 65** = 460 | browser |
| M-9 | Column count | **nine**: Turn, Speed, Stamina, Power, Guts, Wit, SP, Condition, Mood | browser, `thead th` |
| M-10 | Elements past `innerWidth` | **11**, and all eleven are inside the turn-log subtree: `table`, `thead`, `tr`, 2 × `th`, `tbody`, `tr`, 2 × `td`, 2 × `span` | browser |
| M-11 | `select` height | **31.0px** (12 on the page) | browser |
| M-12 | `input[type=number]` height | **30.0px** (20 on the page) | browser |
| M-13 | `button[type=submit]` height | **40.0px** (11 on the page) | browser |
| M-14 | `td` / `th` height | **29.0px / 28.5px** | browser |
| M-15 | Theme of the measured render | **dark** (`html[data-theme]` read from the stored preference) | browser |
| M-16 | 768px viewport, all of the above | **[Unverified]** — see the blocker under the table | — |
| M-17 | Light-theme values | **[Unverified]** — geometry is theme-independent, but no light read was taken | — |

### Three things the table changes, stated plainly

1. **M-3 and M-5 vindicate `b8c0a54`'s intent and still do not close KI-25.** The scoping worked: the
   overflow moved from the document to the region, and a focused region traverses fully with arrow keys.
   R85's withdrawal was still correct — those claims were made before anyone pressed a key — and the
   register now says so in the right order: measurement first, closure second.
2. **M-10 settles the attribution that §8.4 asserted from markup.** "Every offender traces to one element"
   was an inference from one measured element out of eleven. Measured, it is true: all eleven are inside the
   turn log's subtree. The claim is re-established, on a different instrument than it was originally made.
3. **M-8 is new information, and it is the useful kind.** The width is not distributed evenly. Condition
   (77) and Mood (65) take 142 of 460px — **31%** — while SP takes 28 and Turn 42. Any "drop columns below
   the breakpoint" option, including the one KI-25 names, would be trading away the two widest and least
   redundant columns, or cutting narrow ones for negligible gain: dropping SP and Wit buys 64px of 117.

### One defect this pass found, which no attribute test could have

`resources/views/runs/show.blade.php` carries **two elements with the identical accessible name
"Turn log"**: a `<section aria-label="Turn log" class="mt-2">` at line 82, which predates this slice, and
`<div … role="region" tabindex="0" aria-label="Turn log">` at line 211, which `b8c0a54` added inside it. A
screen reader announces a nested landmark with the same name as its parent. `TurnLogScrollRegionTest` passes
and cannot see this: it asserts the div's label *contains* "Turn log", so it asserts the duplicate into
existence.

This is R85's argument in one concrete case — an attribute read proves an attribute is present, never that
it is right. **Fix not applied here**, because the region and its name are layout decisions and T3 forbids
them until the replan is accepted. The candidate fixes and their cost are in §5.

### The blocker on rows M-16 and M-17, and on the calendar region

The measurement harness is not reproducible today, and the reason is worth recording because it will bite
the next pass too:

- `artisan serve` started with `DB_DATABASE` pointing at `.scratch-uma/r17.sqlite` **did not serve that
  file**: `/training-runs/2` and `/5` returned 404 while r17 holds five runs, and run 1 rendered a Grade
  Point meter that `ura_finale`'s config disables. So the rows above were taken from a database this slice
  does not control — almost certainly `database/database.sqlite`, which was being written by the concurrent
  session during this pass (its mtime moved while I read it). **That is a breach of the standing
  scratch-DB-only rule and the reason M-1…M-15 are labelled by instrument and not by fixture.**
- Every one of the 30 scratch databases under `.scratch-uma/` predates the skills session's
  `add_reference_fields_to_skills_table` migration. Served against the current code they answer **500** on
  `/training-runs/*`, so there is no compliant fixture to re-measure on.
- Producing one needs a migrate against a scratch file. That command was refused in this session as
  "code" under T3. It writes no tracked file and touches no repo artifact — the approval asked for in the
  reply is exactly:
  `DB_DATABASE="<abs>/scratch-mobile.sqlite" php artisan migrate --seed --force`
  followed by a run whose `scenario` is non-null so the calendar panel self-gates *in*.

With that fixture, M-16, M-17, the calendar region and the two-theme pairs all close in one pass.

---

## 3. The Spark token cost (R83)

### Corrected mapping, and what was withdrawn

The fills are the client's category colours, measured in D-265 and recorded twice more in
`docs/design-research/DESIGN.md` §6's kind table. They read:

| Kind word the form offers | Payload `kind` | Client fill | Second value D-265 records |
|---|---|---|---|
| stat | `blue` | `#3CB4F0` | `#60C0F0` |
| aptitude | `pink` | `#FC84B4` | `#FC78B4` |
| Unique Skill | `green` | `#90CC30` | — |

The mapping approved earlier in this slice's conversation — aptitude with `#90CC30`, Unique Skill with
`#FC84B4` — transposed the last two and is **withdrawn**; the withdrawal is recorded in D-265's own block so
a future reader checks the fills against the rule that governs them. It reached no tracked file, which is
why it had to be found by reading the rule rather than by grepping.

### What D-10 would require before any of this renders

Computed from the token values in `resources/css/app.css` (light `panel #F8F8FB`, `raised #FFFFFF`,
`sunken #E7E7EC`; dark `panel #121013`, `raised #24262A`, `sunken #1A181D`, `ink-strong #FFFFFF`).
WCAG relative-luminance ratio, the same method the repo's own pair table uses.

**As a text or label colour on a surface** — the 4.5:1 that `text-xs` and `text-sm` demand:

| Fill | light panel | light raised | dark panel | dark raised |
|---|---|---|---|---|
| `#3CB4F0` | 2.21 ✗ | 2.34 ✗ | 8.09 ✓ | 6.48 ✓ |
| `#FC84B4` | 2.18 ✗ | 2.31 ✗ | 8.20 ✓ | 6.57 ✓ |
| `#90CC30` | 1.82 ✗ | 1.93 ✗ | 9.80 ✓ | 7.84 ✓ |

**As a boundary against `raised`** — the 3:1 the repo applies to non-text edges:

| Fill | light | dark |
|---|---|---|
| `#3CB4F0` | 2.34 ✗ | 6.48 ✓ |
| `#FC84B4` | 2.31 ✗ | 6.57 ✓ |
| `#90CC30` | 1.93 ✗ | 7.84 ✓ |

**As a chip background with ink on top:**

| Ink | on `#3CB4F0` | on `#FC84B4` | on `#90CC30` |
|---|---|---|---|
| light `ink-strong #482720` | 5.66 ✓ | 5.73 ✓ | 6.85 ✓ |
| dark `ink-strong #FFFFFF` | 2.34 ✗ | 2.31 ✗ | 1.93 ✗ |
| fixed `#121013` | **8.09 ✓** | **8.20 ✓** | **9.80 ✓** |

### The cost, in tokens

The three fills **cannot be used as text or as a boundary in the light theme at all** — every ratio is
between 1.82 and 2.34 against a 3:1 or 4.5:1 requirement — and they pass both roles in dark. That is the
same shape as `--color-green`, which is 1.88 on the light panel and therefore ships a second value,
`--color-green-deep #4E7906`, for light. Three options, with their real cost:

1. **Chip background + one fixed dark ink.** Four new tokens: `--color-spark-stat`,
   `--color-spark-aptitude`, `--color-spark-unique` holding the client fills unchanged in *both* themes,
   plus `--color-on-spark: #121013` fixed in both. Every text-on-fill ratio above clears 8:1. This is the
   only option where the client's own colour is what ships, and it needs **no** theme-conditional value.
2. **Fill as label colour or underline.** Three more light-only `-deep` variants, tuned until each clears
   4.5:1 on `panel`/`raised`. Cheaper markup, and it **stops being the client's colour** — a deepened pink
   is no longer the `#FC84B4` a Trainer reads on screen, which is what D-265's "measured fills" exist to
   preserve. This is the option the containment rule was written against.
3. **Ship nothing yet.** D-265's own sentence already licenses it: the fills "never appear without their
   word and their star count, so the colour is a scanning aid rather than the carrier of meaning". A
   scanning aid is droppable; meaning is not. **The Sparks list renders kind as word plus star count on
   neutral chrome** (`border-rule`, `text-ink`, `bg-raised`) and G-52 has nothing to contain, because no
   fill appears anywhere.

Option 3 is what applies **until the tokens in option 1 ship**, and it is the recommendation in §6: it
carries the full information the containment rule treats as load-bearing, costs zero tokens, and keeps
D-10 from being quietly satisfied by a colour that only works in one theme.

**G-52's gate needs writing either way.** It greps the three hexes outside the Sparks list and expects zero.
Today the hexes appear in no CSS and no component at all, so G-52 passes vacuously. A gate that passes
because nothing exists to check is not evidence, and the same trap is how KI-23 was filed — a fixture that
repeats the parser's wrong key proves nothing about the live document. If option 1 lands, G-52 should be
extended to catch the *token names* as well as the literals, or a `-deep` variant reintroduced later slips
past a hex-only grep.

---

## 4. WCAG scope (R84)

**The mandate in this repository is WCAG 2.1 AA.** D-10 says so in the register, in that wording, and the
pair table in `DESIGN.md` §3.4 is computed against it.

- **24×24 CSS px is not an obligation here.** It is WCAG 2.2 SC 2.5.8 *Target Size (Minimum)*, and 44×44 is
  SC 2.5.5 *Target Size (Enhanced)* at AAA. Neither is 2.1, and 2.1 contains no target-size criterion at all.
- The one place a 2.2 figure has already entered this project's record is §8.4's withdrawn sentence — "its
  31px height clears WCAG 2.2's 24px minimum" — which `ad7cb0a` struck **on scope**, not on arithmetic. The
  number was a real box read; the standard it was checked against is not this tool's standard.
- **Measured sizes may be cited as evidence.** M-11…M-14 are reported because they are the facts any
  decision needs, and they are reported without a verdict attached.
- **2.2 may be proposed as a new gate; it is not one.** If the owner wants it, the honest form is a gate row
  naming the SC, the size, the scope, and the instrument — which is what every other gate in the registry
  has, and what a bare "use WCAG 2.2" would not.

What the numbers say if a target-size rule *were* adopted. On the run detail screen the interactive controls
are **31px** (select), **30px** (number input) and **40px** (submit button); the turn log's cells are
**29px** and **28.5px**. So a 24×24 rule scoped to *controls* passes today at every one of the three
measurements, and a 44×44 rule fails all three controls and both cell sizes — the latter is the option that
re-lays out the two densest surfaces on the screen, which is exactly the trade D-40 refuses and this
document does not recommend.

The gap is not the number. It is that **no gate, no rule and no measurement of target size exists anywhere
in the repo**, and `DESIGN.md`'s "tap targets via labeled controls" is prose with no instrument behind it. A
ruling with a number and no way to check it is an aspiration; the register's own gate table says so.

---

## 5. The Legacy premise, narrowed

**Run-detail Legacy content is read-only.** Three reasons, in the order that decides them:

1. **D-260 fixes the choice at run start.** Legacy Select is a pre-run surface; its controls belong to run
   creation, and no rule in the register licenses one on a live run.
2. **A mid-run editor would imply a mutation the game forbids.** In the client, a trainee's Legacies are
   chosen when the career begins and do not change during it. A tool that edits them at turn 24 asserts that
   the career could have had different ancestors, which is not true of the game being recorded.
3. **It would silently invalidate every turn logged before it.** Stats, skill points and grade finishes
   entered across turns were produced by the trainee that those Legacies made. Re-pointing the ancestors
   retroactively leaves `turn_entries` describing a career the run no longer has — and the tool computes
   nothing, per PRD §6 non-goal 3, so it could not even warn.

So "read-only panel vs read/write editor" is **not** the open question, and treating it as one would have
put the design work on the wrong branch. The narrower question the replan actually answers:

> **Does Legacy Select exist as a pre-run step at run creation — sparks and affinity entered per D-262 and
> D-264 — or does it remain schema-only, at ADR-0010's present state?**

`ADR-0010` answers the second half itself. Its sunset clause says the column stays justified only while the
screen is being built, and that if the screen is dropped it should be **removed rather than left as an empty
bag**. A payload column with no writer is not a schema, it is a habit: nothing enforces its shape, nothing
tests its contents, and `LegacySelectionPayload`'s strict key set currently guards a value no code path can
produce. Recorded as open item §10.3.2 in the slice-15 record the day it landed.

Three concrete obstacles stand between "yes, at run creation" and a buildable form, and they are the reason
this is a replan rather than a ticket:

- **The payload keys Sparks per Legacy; the brief describes one repeater and one list.** D-268's own words
  are "per Legacy: … a Spark list". Either the form has two repeaters, matching the landed shape at the cost
  of doubling the controls on the narrowest screen in the tool, or one list is stored against the pair, which
  is cheaper on mobile and contradicts accepted schema. Not resolvable by design.
- **Three of the payload's four per-Legacy keys have no control.** `rank`, `is_guest` and `ancestors` are
  required by `LegacySelectionPayload::legacy()`, and D-263 forbids the Guest control outright. A subset form
  must either fabricate values (`is_guest => false`, an ancestor list that reads as "has none" when it means
  "was not asked") or relax the typed shape — which is a schema decision, and the schema session is not this
  one.
- **Unique-Spark name validation now sits on different ground than the brief assumed.** The skills session's
  `aa5b05c` takes `skills` from ten seeded rows to 1,910 stored with 623 reaching a Trainer through
  `Skill::availableOnGlobal()`, and `f71a10e` moved the seeder's names onto `name_en`, retiring
  `Traightaways`. "Validate against the skills catalogue" therefore has to name *which* set — all rows, or
  the 623 a Trainer can actually see — and `SKILLS-GAPS.md` G-SK-5 already asks the adjacent question
  (does a Spark target resolve through `match_key` at read time or a stored `skill_id` at write time). That
  is the Legacy owner's and the Architect's, not a layout ticket's.

**And the defect from §2 travels with the panel:** wherever the read content lands, the two elements named
"Turn log" need reconciling first, because a Legacy panel added to that same screen inherits the duplicate
name problem in a third place. Cheapest fix: drop the `aria-label` from the inner scroll region and let the
region inherit its name from the section it sits in — but that is a layout decision, and it is listed here
rather than made.

---

## 6. Options, costs, and one recommendation

**Option 1 — Land nothing. Accept §1's addendum, keep the fills off, leave Legacy Select schema-only.**
- Cost: `legacy_selection` stays a column with no writer, which ADR-0010's own sunset clause calls a habit.
  The mobile contract still has to be amended or KI-25 stays open with no contract beside it.
- Buys: zero tokens, zero new surfaces, and the register stops carrying two contradictory width claims.
- Risk: the ADR-0010 debt is now three slices old and gets cheaper to delete every time nobody writes it.

**Option 2 — Read-only Legacy panel at run detail, no colour.**
- Cost: one component plus a host line in `runs/show.blade.php`, which the owner froze for this slice; the
  §5 duplicate-name fix has to land first or the panel inherits it. Renders `TrainingRun::legacySelection()`
  over a payload that **no code path can currently write**, so on every real run it renders the N/A state —
  a designed empty state for data that cannot exist yet.
- Buys: the read half of D-268 demonstrated, at no colour cost, testable now.
- Risk: shipping a panel whose happy path is unreachable is the "demo wearing a product's clothes" failure
  in miniature, and it is the reason this option is listed with its weakness named rather than recommended.

**Option 3 — Pre-run Legacy Select at run creation (sparks + affinity), neutral chips, then the read panel.**
- Cost: the three obstacles in §5 first — the per-Legacy-vs-one-list decision, the payload's three
  uncollected keys (a schema change), and the skills-catalogue validation set. Plus a repeater whose rows
  must rebuild from flashed input on a server-rendered form, which is the hardest data-state problem in the
  whole surface and the one ADR-0007 does not cover.
- Buys: `legacy_selection` gets a writer, ADR-0010's sunset clause is answered, D-262/D-264 entered data
  lands, and Option 2's read panel becomes a read of something real.
- Risk: it is the largest of the three and it is blocked on rulings, not on effort.

**Option 4 — Full mobile-first re-layout of the run detail screen.**
- Cost: D-40's refusal is the whole bill. 44×44 targets alone fail every control and both cell sizes (§4) and
  re-lay out the two densest surfaces; a column-dropping turn log trades the two widest columns (Condition
  77px + Mood 65px = 31% of the width) for the least data.
- Buys: nothing the §1 amendment does not already buy, which is traversal without loss.
- Risk: it is where "make it mobile-first" turns into shipping a worse desktop tool.

**Recommendation.** Take **Option 1 plus the §1 addendum as written, and schedule Option 3 as the next
slice behind two named rulings.** The reasons, in order of how much they matter:

1. **The mobile contract is the only thing here that is both broken and cheap to fix.** §1's addendum turns
   a live contradiction between D-40 and §2.3 into one scoped sentence, and M-3/M-5 already supply its
   evidence. That needs no component, no token and no schema.
2. **Colour is not load-bearing and the light theme cannot carry it.** Every fill fails both 4.5:1 and 3:1 in
   light (§3). Option 1's four-token chip is the only version that ships the client's actual colour, and it
   should land *with* a Legacy surface or not at all — spending four tokens on a panel with no writer is
   Option 2's weakness multiplied.
3. **24×24 should not become a gate by accident.** It is 2.2, the mandate is 2.1, and the measured controls
   already pass it (§4). Recording that as "no action needed" is the honest output; adopting 2.5.8 at 24px
   costs nothing today and should be ruled deliberately, with an instrument, if it is ruled at all.
4. **Option 3 is where the real decisions are, and two of them belong to other owners.** The payload's three
   uncollected keys and the per-Legacy-vs-one-list shape are schema rulings; the Spark-target validation set
   is a data-model ruling that `SKILLS-GAPS.md` G-SK-5 has already queued. Building the form before those
   land is how Slice 15's brief ended up naming a column — `inherited_sparks` — that no commit ever created.
5. **One prerequisite regardless of the option chosen:** finish the measurement properly. §2's blocker is a
   fixture problem, not a design problem — a current-schema scratch database plus a run with a non-null
   scenario, and M-16, M-17 and the race-calendar region close in one pass. Until the calendar region has
   been traversed at the floor, §1's own last paragraph says the floor moves to 1024px. The plan should be
   re-accepted on that measurement, not on this one.
