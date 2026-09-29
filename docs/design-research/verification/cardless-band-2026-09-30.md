# Cardless band in the default trainee list — record and verification

Date: 2026-09-30 local (the measurements below were taken at 2026-09-29 19:25–19:37 UTC).
Branch: `feat/catalog-roster-and-trainee-selector`, worktree `../umamusume-laravel13-catalog-roster`.
Opening HEAD `cadb4e3`; the band lands on top of `07941eb`.
Ruling implemented: the owner's 2026-09-30 decision, "**Cardless rows: separate band, not
bottom-of-list** … render the default list as two bands: recent-10 first, then a divider, then cardless
trainees ('no confirmed costume card yet') above the fold … the assertion that she's reachable by name
must exist either way."

---

## 1. What changed

`resources/js/trainee-combobox.ts`:

- `collect`'s empty-query branch returns two bands instead of one flat date sort:
  `[...carded, ...cardless]`, carded newest-first, cardless by trainee name. The old shape sorted every
  row by `releaseDate` descending, and a cardless row's `releaseDate` is `''`, so she sat below the cap
  on any roster holding even one confirmed card.
- `windowFor` (new, extracted from `render`'s one-line slice) gives **each band its own window** at the
  existing `DEFAULT_VISIBLE` cap. A single slice of the banded list is the shape that hid her.
- `bandDivider` (new) paints one `role="presentation"` row reading **"No confirmed costume card yet"**,
  appended once and only when a confirmed band precedes it. It carries `data-band-divider`, never
  `role="option"`, so it cannot enter `options()` or receive the cursor.
- `isCardless` (new) is the one spelling of the question the band turns on, used by `collect`,
  `windowFor`, the seam in the loop and the element id below, so the five sites cannot each spell it
  slightly differently and drift.
- `option.id` for a cardless row is keyed on her **trainee** id, not `selectionId`. See §4.

`tests/Feature/TraineeSelectorTest.php`: one new test,
`it('puts the cardless band in the default list, under a divider, with no typing')`, pinning the band
order, the two windows (`substr_count($window, 'DEFAULT_VISIBLE') === 2`), the divider's role/attr/copy,
its once-only gated append, and the unique-id fix. The set-parity and name-reachability proofs the owner
asked for already existed (`makes the payload trainee set the same set the no-script select offers`,
`lists every Global trainee in the no-script select when the database holds no costume card at all`).

## 2. The state it was measured against

`database/scratch-ui.sqlite` (gitignored; `database/database.sqlite` was not opened, read or written),
built by `research-scratch/make-ui-db.php`: the rehearsed 107-card scratch state, with each trainee's
`name`/`name_ja` replaced by the strings the **shipped** `GametoraCharacterParser` reads from the same body
(joined on the same `external_ref` the card store resolves through), and three cards set `unconfirmed = 1`
so their trainees hold no confirmed form. The rehearsal database itself names every trainee by her ref
(`research-scratch/rehearse-apply.php:69`, `'name' => $ref`), which is why it could not be used for a
read of the UI as a Trainer would see it.

Result: 68 Global trainees, 65 holding a confirmed card, 104 confirmed cards + 3 cardless trainees =
**107 rows**. Server on `127.0.0.1` with an absolute `DB_DATABASE` (port 8431 for the first pass, 8432
for the re-measure after the helper refactor), assets from `npm run build`.

## 3. Measured, in the browser, with nothing typed

Focusing the input paints 27 rows: ten `header`/`option` pairs newest-first (2026-09-28 down to
2026-08-18), then the divider, then three cardless trainees. Every figure below was read again against
the final bundle (`app-CZXqo5_6.js`, the one carrying `isCardless`) and reproduced unchanged: 27 rows,
13 options, 13 distinct ids, divider at row 21 with `role=presentation`, live region
`13 of 107 (keep typing)`, divider at y=601 in a 286 px viewport over 813 px of content.

| check | measured |
|---|---|
| live region | `13 of 107 (keep typing)` |
| options in the paint | 13 (10 carded + 3 cardless) |
| divider | `role=presentation`, `data-band-divider`, text `No confirmed costume card yet`, at DOM row 21 |
| cardless option text | `No costume card confirmed yet`, under a header naming her (`Aston Machan アストンマーチャン`) |
| `aria-label` of a cardless option | `Wonder Acute · No costume card confirmed yet` |
| distinct option ids | 13 of 13 (`trainee-option-u65`, `-u62`, `-u68` for the cardless three) |
| ArrowDown ×13 | `aria-activedescendant` named 13 distinct `role=option` elements, never the divider; ×14 wraps to the first |

Commit path: pressing the divider changes nothing (`umamusume_id` and `character_card_id` both stay
empty). Pressing a cardless row sets the visible field to `Aston Machan · No costume card confirmed
yet`, `umamusume_id` to `65`, leaves `character_card_id` empty, closes the listbox and disables the
native select. Submitting that form created `training_runs` id 1 with `umamusume_id = 65` and
`character_card_id = NULL`.

Typed path, unchanged by the band: `Won` → one row, `1 match`; `zzz` → `No trainee or card found.`;
clearing the field repaints both bands.

## 4. What the browser pass caught that reading did not

`option.id` was built from `hit.card.selectionId`, and a cardless row's `selectionId` is the placeholder
`0`. One cardless row on screen was invisible; **three shared `trainee-option-0`**, and
`aria-activedescendant` is set from `options()[active].id` and resolved by a screen reader through
`getElementById` — so with the cursor on the second or third cardless row, the announced row was the
first. Duplicate ids are also invalid HTML (WCAG 4.1.1). Pre-existing, and reachable before this change
on any database that paints more than one cardless row (a seeded install paints ten), but the band makes
it the normal case rather than the seed-state case. Fixed by keying a cardless row's id on her trainee
id, with the reason in the comment and the shape pinned in the test.

## 5. Where the ruling is only partly met — open

"Recent-10 first" and "cardless above the fold" are in tension at the popup's current height, and the
numbers are not close:

| | px |
|---|---|
| listbox viewport (`max-h-72`) | 286 |
| content at scrollTop 0 | 813 |
| divider position | 601 |
| first cardless option | 662 |
| rows fully visible without scrolling | 9 of 27 |

Ten carded trainees cost twenty rows because each one is a header plus an option (~60 px), so the seam
lands at 601 px no matter what follows it. What this change does deliver: she is in the default paint at
row 11 of 13 instead of row 98 of 107, and no typing is required. What it does not deliver, literally: the
band is below the popup's scroll fold.

Three ways to close it, each costing something the ruling named:

1. **Cap the carded band by height, not count** (≈ 4 trainees at the current 286 px). The seam and the
   cardless band land on screen; "recent-10" becomes "recent-4".
2. **Raise the listbox cap** so the whole default window fits (~813 px). Keeps 10 and the fold, at the
   cost of a popup taller than a phone viewport — and `max-h-72` has no standard step near 813 px, so
   the class would be an arbitrary value, which `docs/design-research/CONSTRAINTS.md:13` (D-1: "The
   `@theme` block is law") makes a review failure.
3. **Drop the per-trainee header where a trainee has exactly one row**, folding her name into the option
   line. Halves both bands (~300 px for ten carded), keeps recent-10, and puts the seam just below the
   fold rather than two screens below. It changes the visible row shape the last three slices settled, so
   it is a design decision and not a fix.

Recommendation: 3, then re-measure; 1 if the fold matters more than the ten.

## 6. Gates

Full suite after the last edit: **783 passed, 2 skipped (2729 assertions)**, exit 0. This file alone:
`tests/Feature/TraineeSelectorTest.php` → **26 passed (174 assertions)**. `npm run typecheck` (C-9)
clean; `npm run build` clean; `vendor/bin/pint` passed on the changed test; PHPStan level 6
`[OK] No errors`, run against this slice's tree (`app/` carries no change in it, which is why the gate
is quoted rather than re-run per edit). `composer lore` 115 hits / 57 exempt and `composer lore-code` 8,
both unchanged from the recorded baseline; the new copy carries no dash and no equine term.
