# 00 — Corrections

Three documentation defects the 2026-11-15 pass created, each with before/after. No browser was needed for this page.

---

## A1 — KI-163 gains its third trap: `errors="replace"`

**Before.** KI-163 recorded two traps (`DB_DATABASE=` shell prefix dropped; `php -S` CWD). The `errors="replace"`
hazard was only in my hand-off prose, not in the register.

**After.** `KNOWN-ISSUES.md` §KI-163 now carries trap 3:

> A Python text-mode read with `errors="replace"` corrupts bytes silently, and a content-hash check against the
> corrupt result reports a match. `errors="replace"` substitutes U+FFFD for every undecodable byte, so the read
> returns valid-looking text with the damage replaced inline. […] The byte-delta mismatch is what caught that
> instance. It would **not** have caught the `errors="replace"` class: U+FFFD substitution preserves the character
> count, so a delta check compares equal and the "hash is byte-identical" verification matches against the
> already-corrupt version. **The CRLF catch does not generalize.**
>
> **Correct pattern.** Bytes-mode reads and writes (`open(path,"rb")` / `open(path,"wb")`) for this file and for any
> tracked `.md` whose EOL is declared by `.gitattributes`. Decode explicitly with `raw.decode("utf-8")` so undecodable
> bytes raise instead of being replaced.
>
> **Pinned by:** nothing. A process trap, not a testable invariant. Recorded as a rule, not a guard.

**Applied via bytes mode**, as the new rule requires: `open(path,"rb").read()` → strict `decode("utf-8")` → same-mode
write. Post-write check: **0 CRLF, 5,340 lone-LF, strict UTF-8 OK**, bytes 447,675 → 449,063 (+1,388 = the trap text).

**One thing this does not fix.** Three earlier passes wrote this file through text mode at some point, and the
working copy may already carry U+FFFD from a peer session I cannot see. A strict decode passes now, which proves it is
valid UTF-8 *today*; it does not prove it was never corrupted. Verifying against `HEAD` blob-by-blob would be the only
proof, and it is out of this dispatch's scope.

---

## A2 — EX-12 splits into EX-12a and EX-12b

**Before.** One row, `EX-12`, collapsing a method decision and a data decision, and carrying my recommendation as if
it were a note.

**After.** Two rows, both **owner-ruled this pass**:

| ID | Decision | Ruling |
|---|---|---|
| **EX-12a** | Walk convention | **(A) Private-copy is the method.** Each pass backs the dev file up into `.scratch-uma/<session>/`, seeds the copy, serves it on a session-private port. The dev file is never mutated by an audit. No per-pass permission needed. |
| **EX-12b** | Dev-file state | **Seed it once to match the walk dataset.** Backed up first (`.scratch-uma/browser-obs-followup/dev-db-pre-seed-backup.sqlite`), then seeded to 19 runs / 8 veterans. Standing rule: only an explicit owner instruction seeds it. |

They genuinely diverge, which is why the split was right: the owner chose **A for the method** and **the populated
state for the data**. Collapsed into one row, one of those would have been lost.

---

## A3 — The deck-builder hole gets its own heading

**Before.** Folded into EX-16 as "the seeder imports no deck", which framed a three-pass coverage gap as a seeder
detail.

**After.** `00-coverage.md` carries a heading **"Coverage holes surviving more than one pass"** → **"The deck builder
has never been walked populated"**, recording all three passes, the follow-up test against a *completed* URA run
(Task D), and the consequence: every claim about the populated deck builder is unverified by observation. Written so
a reader does not mistake the empty-state copy for evidence about the populated state.

---

## A4 — The method-property statement is now a standing header

**Before.** It lived in `04-excluded-findings.md`, read only by someone who reached the exclusions.

**After.** It is the **third line of `00-coverage.md`**, above the observation-surface block, framed as carried
forward across two passes:

> **Method property (carried forward from 2026-11-14 and 2026-11-15).** This instrument detects copy, state-dependent
> labels, count contradictions, computed contrast, geometry, console errors and empty states. It has never, across two
> passes, detected a density, hierarchy, or "reads well" defect. Vision was unavailable on both passes. A clean colour
> or geometry section from this audit does not mean the UI is fine; it means the class of defect this instrument can
> see was not present. Read the register as a measurement audit, not a visual one, until a vision-available pass is
> run.

**Vision was re-tested this pass, not inherited.** The endpoint returned the same `ChatCompletion` adapter error. The
header stays true.

---

## Standing decision — the quiet-tree rule

The dispatch is right that "record the deviation and proceed" has now failed twice and is not a rule. Owner ruling,
recorded verbatim:

> **(A) for full audits, (B) for narrow follow-ups like this one**

Which resolves as: **full audits require a signed window** (owner names start and end; no walk without one), and
**narrow follow-ups carry the timestamped-evidence header** (capture time + build-manifest mtime; findings describe
that build; colour/geometry/count claims are not statements about disk now).

**Applied to this pass immediately**, which is a narrow follow-up: evidence captured **01:29–01:36 on 2026-11-15**
against build manifest mtime **01:26:56**. Findings here describe that build.
