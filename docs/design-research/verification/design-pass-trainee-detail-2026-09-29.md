# Design pass — trainee detail page and the run screen's skill selector: record

Date: 2026-09-29
Type: **design pass**, not a slice. It produced design briefs and register entries and shipped no view,
so it is not named `slice-N`; that prefix tells a reader to expect a code diff in the block, and there is
none. The date suffix matches the convention the other verification records use.
Tree at the pass: `edb03d5`. Tree at the block's landing: `d0422ce`. Tree at the time of writing this
record: `bc11d9b`, and §6 explains why that difference is the most useful thing on the page.
Rulings: R-1 through R-6. Deliverables: D-1 through D-4, plus four premise corrections and one refusal.

---

## 1. The block, as landed

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

## 2. D-289's self-corrections, in the form they were caught

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

The point of four rather than one: D-289 was exercised four times before it could be cited, and every one
was caught by opening a file instead of trusting a sentence. That is what the rule looks like in operation,
which is why it is recorded here rather than only asserted in `CONSTRAINTS.md`.

---

## 3. The three departures from the draft, with reasons

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

## 4. The refusal, verbatim

> *"I'd be a poor witness for the rule if I copied them into it."*

The draft R-6 handed over contained five citation claims. Three survived contact with the files; two did
not. Refusing the two — rather than softening them into "approximately" or leaving them unverified in a
permanent rule about verifying citations — is the reason the paragraph is trustworthy, and the sentence is
kept here unsmoothed because the shape of the refusal is the deliverable. A rule about citation discipline,
written from a citation list that was not checked, would have been its own counter-example on the day it
landed. The refusal is not modesty about the wording; it is the mechanism, running in the one place it had
to run to prove the rest of the corpus's reading discipline is real rather than performed.

---

## 5. KI-34's reservation, so the gap is not read as loss

The register carries a numbering hole: `grep -oE '^## KI-[0-9]+'` returns KI-1..15 and KI-17..37, with
**KI-16 missing because it was renamed** — `KI-30` was *filed as* KI-16 on
`fix/frontend-audit-2026-09-28` and renumbered at merge (`KNOWN-ISSUES.md:1375`), and the renumbering left
the old number behind. The hole read, for several slices, as a lost entry, because nothing claimed it.

**KI-34 is the opposite shape and the record notes the difference.** It is not missing and not lost: it is
claimed, empty, and reserved for the per-character goal-race entry whose draft exists and whose `09`
correction is written. The status block in `KNOWN-ISSUES.md` says so in those words rather than leaving the
gap to be rediscovered as a mystery. That distinction — a hole someone owns versus a hole nobody remembers —
is what the register has needed since KI-16 went missing, and it is the reason this section exists rather
than a one-line footnote.

---

## 6. What moved after the block landed, and why it belongs in this record

Between the block (`d0422ce`) and this file, `bc11d9b` merged the character-card layer and the trainee
selector onto `master`. Three consequences, and they are the live demonstration of D-289 rather than a note
about it:

- **KI-33's cause sentence is already stale; its finding is not.** The entry says `character_cards`,
  `CharacterCard` and `ADR-0008` "are absent from this ref" (`KNOWN-ISSUES.md:1491`). They are now present:
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

## 7. What this pass produced that is not a commit

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
