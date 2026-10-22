# The HFW mock catalogue

The wireframe pages are static HTML with no database behind them, so the content they
draw has to come from somewhere. This directory is that somewhere: the catalogue the
app already holds, shaped for a page to read, with no figure invented for it.

**Nothing here is a placeholder string.** Every value is a value the app's own catalogue
holds, or a value the committed export body states, read at the generator and written
here unchanged. The one thing the repository cannot supply is a client-vocabulary
description for a skill, and this dataset says so rather than writing one (see
*Descriptions* below).

## The four files

| File | Holds | Rows |
| --- | --- | --- |
| `umamusume.js` | the trainee roster, each with its card forms | 67 trainees, 106 forms |
| `support-cards.js` | the support-card catalogue | 559 cards |
| `support-effects.js` | the support-effect dictionary | 35 effects |
| `skills.js` | the skill catalogue | 1,910 skills |

Each file is a **classic script**, not a module, because the pages are opened under
`file://` and an ES module loaded from `file://` is blocked by the browser's
cross-origin rules. The sprite in `assets/stat-icons.svg` is inlined for the same
reason, and the set's own note on it says so.

## How to load it

```html
<script src="assets/data/umamusume.js"></script>
<script src="assets/data/support-cards.js"></script>
<script src="assets/data/support-effects.js"></script>
<script src="assets/data/skills.js"></script>
<script>
    const { umamusume, supportCards, supportEffects, skills } = window.TRAINER_DESK_DATA;
    console.log(umamusume.length, supportCards.length, skills.length);
</script>
```

Every file writes into the one namespace, `window.TRAINER_DESK_DATA`, and each also carries its own
schema object beside the data (`TRAINER_DESK_DATA.skillsSchema`, and so on): the field names, what
each one means, and where the values come from. A consumer that reads the schema renders the right
empty state, because the schema is where the absences are stated.

From a module — a Vite page, or Node — the same files work as a side-effect import, because they
write to `globalThis` rather than exporting:

```js
await import('./assets/data/skills.js');
console.log(globalThis.TRAINER_DESK_DATA.skills.length);   // 1910
```

They also export the same object through CommonJS for a consumer that loads one as a `.cjs` module.

## The three entities

### Umamusume (a trainee)

```js
{
  id: 'umamusume-1001',            // the key that never changes
  sourceId: 1001,                  // the source's own character id
  slug: 'special-week',
  name: 'Special Week',
  nameJa: 'スペシャルウィーク',
  releaseStatus: 'GlobalReleased',
  jpDebutDate: '2021-02-24',
  globalDebutDate: '2025-06-26',
  profile: {                       // null where the catalogue holds no profile
    voiceActor: 'Azumi Waki',
    voiceActorJa: '和氣あず未',
    birthDate: '1995-05-02',
    heightCm: 158,
    measurements: { bust: 81, waist: 56, hip: 81 },
  },
  aptitude: {                      // the nine aptitude grades, A to G
    turf: 'A', dirt: 'G',
    sprint: 'F', mile: 'C', medium: 'A', long: 'A',
    frontRunner: 'G', paceChaser: 'A', lateSurger: 'A', endCloser: 'C',
  },
  forms: [                         // the card forms, debut form first
    {
      id: 'card-100101',
      cardId: 100101,
      title: '[Special Dreamer]',
      titleJa: '[スペシャルドリーマー]',
      rarity: 3,                   // the source's code: 1, 2 or 3
      rarityWord: 'SSR',            // the client's word for that code
      isDebutForm: true,
      globalReleaseDate: '2025-06-26',
      baseStats: { speed: 83, stamina: 88, power: 98, guts: 90, wit: 91 },
      growthRate: { speed: 0, stamina: 20, power: 0, guts: 0, wit: 10 },
      breakthrough: {
        fourStar: { speed: 92, stamina: 98, power: 109, guts: 100, wit: 101 },
        fiveStar: { speed: 102, stamina: 108, power: 120, guts: 110, wit: 110 },
      },
      skills: {
        innate: [200512, 201352, 200732],
        unique: [100011],
        awakening: [200162, 201351, 200612, 200511, 204262, 204111],
        event: [200472, 200512, 200332, 200032, 200042, 200562, 200602],
        evolution: [{ from: 201351, to: 100101111 }],
      },
    },
  ],
}
```

**The growth row.** `growthRate` is the source's `stat_bonus`, which the client reads as
the **Growth Rate** row (`docs/UMAMUSUME_REFERENCE.md` §1.3.5). It is 30 points on every
card, in 5-point steps, across one to three stats. A zero is a real figure and the client
prints it as `+0%`; it is never a blank and never a dash. A stat that is absent from the
row is absent, which is not the same thing as a zero.

**The base row.** `baseStats` sums to 450 on a three-star card, 425 on a two-star and 400
on a one-star. **The breakthrough rows** are the star-breakthrough budget: the four-star
row sums to 500 and the five-star row to 550, on every card.

### Support card

```js
{
  id: 'support-10001',
  sourceId: 10001,
  character: { sourceId: 1001, name: 'Special Week', nameJa: 'スペシャルウィーク' },
  title: '[Tracen Academy]',
  titleJa: '[トレセン学園]',
  rarity: 1,                   // the source's code: 1, 2 or 3
  rarityWord: 'R',             // the client's word: R, SR or SSR
  type: 'guts',                // the export's key
  typeWord: 'Guts',            // the client's word: Wit for intelligence, Pal for friend
  releaseJp: '2021-02-24',
  releaseGlobal: '2025-06-26',
  effects: [
    { effectId: 1, levels: { 1: 5, 15: 10, 20: 10, 35: 15 }, cap: { level: 35, value: 15 } },
  ],
  hintSkills: [200162, 200232],
  eventSkills: [200762],
}
```

**The join.** An effect row carries `effectId` and nothing else. The effect's name, its
unit and its description live once, in `support-effects.js`:

```js
TRAINER_DESK_DATA.supportEffects.find((e) => e.effectId === 1);
// { effectId: 1, name: 'Friendship Bonus', nameJa: '友情ボーナス',
//   calc: 'mult', symbol: 'percent',
//   description: 'Increases the effectiveness of Friendship Training' }
```

**The anchors.** A card's effect row is a positional vector of eleven card levels (1, 5,
10, 15, 20, 25, 30, 35, 40, 45, 50) plus the effect's id, and `-1` means the source holds
no entry at that level, **not** that the effect is zero. So the stated anchors are kept and
the absent levels are left out: a missing level is not a zero. `cap` is the last anchor
the source states, and its value.

### Skill

```js
{
  id: 'skill-201351',
  sourceId: 201351,
  name: 'Gourmand',
  nameJa: '食いしん坊',
  type: 'Recovery',            // the pipeline's DERIVED word, or null
  spCost: 180,                 // null where the source states none
  rarity: 2,                   // the source's class code; it names no tier
  isUnique: false,             // the mark the pipeline derives from the class code
  releaseStatus: 'GlobalReleased',
  activation: 1,               // the source's own flag; no tracked source decodes it
  classCodes: ['ldr', 'l_1'],  // the source's gate keys; their meaning is not established
  conditions: [
    {
      condition: 'running_style==2&phase_random==1',
      precondition: null,
      baseTime: 0,
      effects: [{ type: 9, value: 550 }],
    },
  ],
  description: null,           // always null; see below
  sourceText: { en: '…', end: '…' },
}
```

**The type word is derived, and labelled as derived.** The export's own `type` field is a
list of gate keys that say *when* a skill fires, not what kind it is. The pipeline reads
the category off the effect codes: 27 and 22 are Speed, 9 and 28 are Recovery, and 1, 2
and 3 are Passive **only where the effect's value is positive** (a negative value is an
averseness, and reading the code alone would have labelled 32 of them Passive). Twenty-two
effect codes are unmapped, and a row carrying one gets `null` rather than a guess
(`app/Services/DataPipeline/Parsers/GametoraSkillsParser.php`).

**The numbers stay numbers.** `rarity` is the source's class code: 1 and 2 carry a cost
and are learnable, 3, 4 and 5 are bound to a character, and 6 is an evolved row. The
source names no tier, so no rarity word is attached. The same holds for `classCodes` and
for the effect codes inside `conditions`: recording them is a fact, translating them is
not.

## Descriptions, and why `description` is null

**There is no client-vocabulary description for a skill in this repository, and adding
one is a ruled-against move.** G-SK-20 in `docs/research-scratch/SKILLS-MECHANICS.md`
holds that no description column may be added until a client-string source for
descriptions exists, and it shows why with a measurement: the export's English fields
fail the terminology table 189 times and 72 times respectively, so neither of them is
`[Global]` prose, and the one that reads most like game copy is the one that contradicts
the vocabulary the client uses. The skills parser says the same in its own words: a
description is evidence of what an effect *does* and is never a source of copy.

So this dataset writes `description: null` on every skill, which is what a surface that
binds to the field needs in order to print the **"no client string"** state the
wireframe's own skill detail page already draws:

> The plain description is a convenience reading of the tokens above, never a replacement
> for them. Where a client string exists it is printed verbatim; where none does, this
> block says "no client string".

The publisher's own English is carried beside it, under `sourceText`, which is a name
that cannot be mistaken for copy. It is reference material: it is the export's English,
not the client's, and it is not to be rendered on a display path.

## What is not in the dataset, and why

A development database is a working surface, and this one carries four rows the game does
not: trainees named "Form Counted 1" to "Form Counted 4" and twelve card forms titled
"[Sample Form]", all written by a test against the `.test` host. The app's own counters
include them, which is why the live dashboard reads **71 trainees** where the catalogue
holds **67** real ones.

The dataset's rule is a join and not a name filter: a trainee is in it when the pipeline
stamped it with a source reference *and* the committed character document states that
character, and a form is in it when the committed character-card body states that card.
The generator then checks that every row the join left out was a row that was never
sourced, so a real trainee that fell out of the join would fail the build rather than
vanish from the dataset.

## The rules a consumer inherits

1. **A missing value is not a zero.** A growth stat that is absent is absent; a growth
   stat that is `0` is a real figure the client prints as `+0%`.
2. **A missing anchor is not a zero.** A card level the source does not state for an
   effect is absent from `levels`.
3. **No rarity word where the source names no tier.** A skill's `rarity` is a number.
4. **`sourceText` is not copy.** It is the publisher's English, held as source data.
5. **No effect code is decoded here.** The codes are the source's own, and a surface that
   wants a name for one must have a source for that name.
6. **A skill name is the client's own, and so is a condition atom.** Four of the skill
   names carry a word the lore gate lists, and two of the engine's condition atoms do
   too. They are the client's strings and the export's, held as data: a verbatim skill
   name and a quoted expression are not framing, and the gate's own rules say so. A
   surface that prints a skill's name prints the client's name; it does not paraphrase
   it, and it does not write an engine atom into a sentence.

## Regenerating

```
php build-data.php
```

from this directory's parent (`docs/design-research/frontend-v2-wireframes`). The
generator reads the dev database and the committed export bodies, writes the four files
and checks its own output before it writes: the counts must equal the catalogue's, every
growth row must still sum to 30, every base row must still sum to 450, 425 or 400 by the
card's rarity, every effect a card carries must be in the dictionary, and every excluded
row must be a row that was never sourced.

The dev database is gitignored and is rebuilt by `php artisan migrate:fresh --seed`,
which seeds from the committed bodies, so the output is reproducible from a clean
checkout. Run that first if the database file is absent.

## The check

```
node data-check.mjs
```

in this directory's parent. It holds the catalogue to the rules above, and adds three of its
own: that no skill carries a description, that the schema names every field the data carries
so the two cannot drift, and that the four files load **as classic scripts under `file://`** in
Chromium and publish the namespace, which is the load a wireframe page actually does. Where
Chromium cannot launch, that last rule is reported as UNVERIFIED rather than passing.

Each rule was proven to have teeth by mutating the data and watching the check fail with the
finding the rule was written for: a growth row changed, a base stat changed, a breakthrough row
changed, an effect removed from the dictionary, a description added to a skill, a field removed
from the schema, and a test-fixture trainee added back.

## The ageing rule, and this capture's identity

**Frozen 2026-10-23.** The four generated files carry the same header block, and it is the thing to
read before trusting a figure they carry:

- **capture** — `2026-10-23T05:50:00+08:00`, ISO 8601 with the offset, so a reader in another zone
  reads the same instant.
- **source commit** — `55f67f1e`, the tree the inputs were read at. A catalogue rebuilt from a
  different tree is a different catalogue even when every count matches.
- **seed identity** — the ten `database/seeders/data/*.json` files, each named with the first twelve
  hex of its SHA-256. The seed files are named by content hash already, so a renamed-then-edited seed
  shows up as a changed digest here rather than as the same filename with different content.

**The rule.** A capture is stale once any seed digest above differs from the file on disk, or once a
page reads a figure the app's own tables no longer hold. Staleness is a fact about the pair (capture,
seed set), not about age: a week-old capture over an unchanged seed set is current, and a one-minute-old
capture over an edited seed is stale. Re-freeze by regenerating with `php build-data.php` and
re-stamping the header in all five places, not by editing the figures by hand.

**Where the figures come from.** `build-data.php` reads the dev database joined with the committed
GameTora export bodies and refuses to write a dataset whose growth rows do not sum to 30, whose base
rows do not sum to 450/425/400, or whose effect join does not resolve. Nothing in this directory is a
number a page invented; the four files are the app's own rows, shaped for a static page to read.

