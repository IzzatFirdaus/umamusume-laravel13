# Gate Registry

This registry and root `CONSTRAINTS.md` are the source of truth for global gates.
Slice plans such as `PLAN.md` may add slice-specific exit criteria but cannot
override C-1 through C-8 or G-60. Precedence for gate questions:

1. `CONSTRAINTS.md` (the bar; never relaxed to pass a check)
2. `docs/GATE-REGISTRY.md` (this file: how each gate is run, evidence, exceptions)
3. ADRs (`docs/adr/`)
4. Root `DESIGN.md`
5. Slice plans (`PLAN.md`, phase docs)

The research corpus `docs/design-research/CONSTRAINTS.md` owns the D-number design
rules and the prototype gate series (G-1..G-17, G-18, G-21, G-40). This registry
does not restate them; it records where they run and how they bind the shipped app.

## Gate record format

```text
ID | Name | Type (Automated/Review/Manual) | Scope | Command | Pass | Fail |
Evidence | Exceptions | Owner | Status
```

## Global gates

| ID | Name | Type | Command | Pass criteria | Fail = | Evidence | Exceptions | Owner | Status |
|---|---|---|---|---|---|---|---|---|---|
| C-1 | Tests | Automated | `vendor/bin/pest --compact` | zero failures | any failure | command output in hand-off | reasoned skips only (`markTestSkipped` with text) | QA | active |
| C-2 | Static analysis | Automated | `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` | `[OK] No errors` at level 6 | any error | command output | none; `@phpstan-ignore` is a Floor violation | Laravel Dev | active |
| C-3 | Style | Automated | `vendor/bin/pint --test --format agent` | `"result":"passed"` | any drift | command output | none | Laravel Dev | active |
| C-4 | Lore | Automated + Review | `make lore` and `make lore-code` (git grep passes) + context ruling | zero *unexplained* hits | violation without an allowed classification (below) | hit list + one-line ruling per hit | four allowed hit classes (below) | Lore Guardian | active; blind spots below |
| C-5 | Migrations | Manual/Approval | `php artisan migrate` + `db:seed` on a fresh scratch DB (`DB_DATABASE=` pointed at an empty file); `migrate:fresh --seed` against the shared dev file is destructive and needs explicit approval | clean up+down, seed idempotent | any error | command output | destructive variant by approval only | Laravel Dev | active |
| C-6 | Floor (no suppressions/stubs/deleted tests) | Review + grep | `git grep -nE "@phpstan-ignore|eslint-disable|@ts-ignore|not implemented|catch \{\}|TODO" app resources tests config` + diff review for deleted/skipped tests | zero hits or an approved, commit-noted reason | unexplained hit | grep output + commit note | none (floor) | QA | active |
| C-7 | UI states | Review (per ADR-0007) | enumerate per data view: empty, error, data required; custom loading required only for user-initiated async actions | every view's state table complete; no decorative skeletons | missing required state, or async action with no feedback | state enumeration in the view's flow/spec doc | initial server-render navigation may rely on browser loading (ADR-0007) | Frontend | active |
| C-8 | Dependencies | Review + automated | `composer show --direct` / `package.json` diff vs approved list; `composer audit`; `npm audit --omit=dev` | no new package without human approval; no reachable critical/high | unapproved addition | diff + audit output | approval recorded in PR/commit | Architect | active |
| G-60 | Retired literals | Automated + scope rules | run via `tools/gate.py` family / reviewer grep of the retired-value registry | retired values absent from active paths | any hit in an active path | grep output | ignored paths below; `RETIRED LITERAL` blocks below | Pre-Dev | **partial: registry live, scanner pending (see Gaps)** |

## G-60: retired-literal scoping (codified per owner ruling + D-286)

- **Checked (active) paths:** root `DESIGN.md`, root `CONSTRAINTS.md`, `app/`,
  `resources/`, `config/`, routes, database, tests, shipped prototypes.
- **Ignored (historical) paths:** `docs/adr/**`,
  `docs/design-research/MECHANICS-TRANSLATION-TRIAGE.md`,
  `docs/PRE-MORTEM.md`, `docs/design-research/_scratch/**`, `.kilo/**`, `vendor/`,
  `node_modules/`. These record what changed and why; quoting a retired value to
  retire it is correct ADR hygiene (precedent: `ADR-0001` §6 RESOLVED block).
- **Supersede blocks:** if a retired literal must appear in an active file, it is
  wrapped in `> [!WARNING] RETIRED LITERAL:` and the scanner skips that block via
  the admonition marker. No semantic parsing; path scoping plus the marker are the
  whole mechanism.
- **Exit behavior:** 0 = clean; nonzero with per-hit path:line list. A `_scratch`
  script being re-run must have its literals updated before execution (D-286).
- **Known limitation:** the retired-value list itself lives with the owning slice
  (cap rows, transposed Trackblazer stats, withdrawn glosses); G-60 fails closed
  only for values registered there.

## Disclosure pattern (false zero), settled ruling 2026-09-28

- A stat, metric, or field that is zero because it is untracked or not applicable
  renders as `N/A` with a tooltip where useful, e.g. `title="Not tracked in this
  scenario"`, optionally plus a static disclosure line ("breakthrough not tracked
  + deck untracked", per `stat-band.blade.php:153`).
- Never render `0` for a value the schema cannot observe (Planner Rule 5).
- Never use an em dash as the disclosure glyph in shipped copy (R-02; no
  C-4/R-02 carve-out granted). Open breach: `KNOWN-ISSUES.md` KI-7 (Frontend,
  do-not-land).
- Checked by: review (G-C7 style enumeration in the component spec); `tools/gate.py`
  checks D-79 em dash only in prototype HTML today, not Blade (Gaps below).

## C-4 allowed hit classes (grep proposes, Guardian decides)

1. Naming the banned list to forbid it (rules files, this registry, ADR text).
2. Substrings inside ordinary words or proper nouns: `damaged`, `desired`, <!-- lore-ignore-line class=1 cite=GATE-REGISTRY.md#C-4 -->
   adjective `stable`, `Red Desire`, "La dama perfetta". <!-- lore-ignore-line class=2 cite=GATE-REGISTRY.md#C-4 -->
3. Verbatim quoted game/client source data where the term is data, not framing
   (display path is the gated surface; dataset keys like `intelligence`,
   `friend` are out of scope per C-4's copy-and-framing boundary).
4. A gate's own source: the pattern list in `tools/gate.py` and the grep list in
   `tools/lore.php` match `make lore` by construction, so each scanner is a permanent
   self-hit. Expect them, do not clear them. Composition on the `ebfc227` tree: the sweep
   prints 98 match lines plus 49 exempt lines, of which 5 distinct `tools/lore.php` lines and 3
   `tools/gate.py` lines are the scanners naming their own patterns. The total counts
   prints, not distinct lines - a line matching two of the three greps prints twice - and
   it moves whenever a rules file quotes a banned word, so treat it as a measurement of
   that day, never as a threshold. `lore-code` cannot self-hit: `tools/` is outside its
   path list.
Ambiguous framing (a real violation vs a quote) requires a Lore Guardian or owner
ruling before merge; the ruling is recorded next to the hit list.

## lore-ignore-line, the line-scoped exemption (R51, 2026-09-29)

A line that exists to state or itemize a lore ruling is the gate's own text, not a new hit every
time the gate runs. `lore-ignore-line` makes that case explicit and auditable instead of
re-counting it each slice. The recorded readings ran 131 → 132 → 133 → 137 → 140 → 144 → 147
(`slice-3-2026-09-28.md:58,206`, `slice-5-2026-09-28.md:278`, `slice-6-2026-09-28.md:40,174`,
`slice-7-2026-09-28.md:147`, `slice-9-2026-09-28.md:20`), and every step up was a rules file or a
record writing the banned words into a table in order to rule on them. The 147 is this slice's
opening reading, carried by `slice-10-2026-09-29.md`.

- Shape: an HTML comment on the line it exempts, carrying one allowed-hit class from above plus
  the ruling it answers to - `<!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->`.
- Scope: one line. Not a file, not a block, not a directory. Removing the marker restores the hit.
- Where: `docs/` only. `LoreGateParityTest` fails on a marker anywhere else, and the `lore-code`
  gate does not honour markers at all, so one planted outside `docs/` buys nothing - the line it
  tried to hide still prints in the app-path sweep.
- What it is not: a ruling. It records a class and a citation that already exist, and a marker
  missing either is a test failure. The Guardian still decides the case; the marker only stops the
  count from re-litigating a settled one.
- Both runners honour it, and the two are measured against each other: `make lore` pipes each grep
  through `grep -v` on the comment opener quoted above, `composer lore` skips the same literal in
  docs mode. On the `ebfc227` tree the shell stages print 20 + 35 + 43 = 98 and the runner reports
  98 with 49 exempt.

Baseline moved 147 → 98 on 2026-09-29 by marking 41 ruling-table lines across 10 `docs/` files:
the §3.1 vocabulary table and the §3.2 allowed-sense list in `design-research/CONSTRAINTS.md`, the
two C-4 class rows here, the hit-itemization rows in the slice-3, slice-5, slice-6 and slice-9
records, the rule lines in `flows/create-run-and-legacy-select.md`,
`requests/game-mechanics-condition-labels.md` and the frontend-review grep report, and the JP-only
warning in `scenarios/08`.

What stays counted, on purpose: the 20 `docs/UMAMUSUME_REFERENCE.md` lines and the
`docs/scenarios/*` guide rows that quote client and wiki vocabulary as source data; five
`design-research/DESIGN.md` prose lines and the D-186 and D-268 lines in
`design-research/CONSTRAINTS.md`, where a banned word is used as ordinary English rather than to
name the ban; three `design-research/_scratch` patch scripts holding quoted doc text; ADR-0005's
adjective use; and one PNG that matches as a binary file and cannot carry a comment. Those are
reword-or-rule cases, not self-reference, and marking them would hide the question instead of
answering it.

## Known gate gaps (recorded, not hidden)

| Gap | Consequence | Fix owner |
|---|---|---|
| `make lore` uses `git grep` on tracked files: blind to untracked copy | fresh, unstaged copy is unswept by the repo-wide pass; `lore-code` reads untracked but only inside app paths, so an unsaved `docs/` edit is unseen by both | Pre-Dev (registry tells reviewers to sweep dirty files; KI-4 closed 2026-09-28 for the runner, not for this scope split) |
| `make lore` vocabulary: 23 words over three greps (`ee97869`), while client-string terminology stays gate.py's scope | two vocabularies exist; both `make lore` and `composer lore` read one list, and `LoreGateParityTest` fails if the Makefile and `tools/lore.php` drift | Pre-Dev: point both at `docs/design-research/CONSTRAINTS.md` §3.1 |
| The shipped-Blade dash check lives in the Pest suite, not in `tools/gate.py` | `composer test` fails on an en or em dash in any `.blade.php` under `resources/views` (`RenderedCopyHygieneTest`, which strips the three comment forms prose hides in); running `gate.py` alone still checks D-79 only in prototype HTML, so a scan-by-gate.py pass is not proof the Blade sweep ran | Pre-Dev: fold the view sweep into `gate.py` or cite the test wherever the scanner is the only gate |
| G-60 scanner does not yet read a retired-value registry file | G-60 currently reviewer-enforced | Pre-Dev at next ADR amendment cycle |
| C-7 loading-state enforcement is interpretive | per ADR-0007 clause 2; review checks the state enumeration | Frontend |
| Stale mirrors: `.kilo/worktrees/giddy-chronometer/docs/design-research/_scratch/gate.py` exists | edits there are inert; never treat as the live gate | whoever prunes the worktree cache |

## Tooling placement

```text
Canonical gate script: tools/gate.py   (moved 2026-09-28 from
docs/design-research/_scratch/; run: python tools/gate.py; exit 0 = pass)
Forbidden stale copies: docs/design-research/_scratch/gate.py (moved away),
.kilo/worktrees/**/gate.py (stale mirrors, not authoritative)
Data still in provenance: docs/design-research/_scratch/tokens.json (read by
tools/gate.py as a measured-anchor input; it is data, not tooling)
Makefile gate targets: lore, lore-code (git grep); tests/lint/stan as before
```

## Changelog

- 2026-09-28: registry created (T4). C-7 note added to `CONSTRAINTS.md`
  referencing ADR-0007. gate.py relocated. G-60 scoping codified from the owner
  ruling + D-286. Disclosure pattern codified from the settled `N/A` ruling.
