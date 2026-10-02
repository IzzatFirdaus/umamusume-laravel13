<!-- census: content-not-citations -->
# Documentation inventory, 2026-09-30

Snapshot SHA: `c1e14a3`. Every count, line number and hash below was read from that tree.

This is an inventory. It merges nothing, deletes nothing, edits nothing. Section 10 proposes; the owner rules.

## 1. Skills invoked

| Skill | Used for | Result |
|---|---|---|
| `documentation-and-adrs` | Governing skill. Type classification, ADR lifecycle, the heading-over-line citation convention | Loaded. Its ADR template says `docs/decisions/`; this repo uses `docs/adr/NNNN-title.md` with an inline `Status:` line, so the repo convention won, as the skill instructs |
| `gstack:careful` | The read-only fence | Loaded. Its own telemetry line writes to `~/.gstack/analytics/skill-usage.jsonl`; I skipped that write because the fence says read-only |
| `antislop-copywriting` | Every Description cell and every finding in this file | Loaded. No em dash in this document's own prose except inside verbatim filenames |
| `doubt-driven-development` | The Status column, which carries a burden of proof | Loaded earlier in the session. Its fresh-context reviewer step was not run; see section 2, limit 5 |
| `source-driven-development` | Citation resolution: a link is live only if the target resolves | Applied through the scan in section 5, not by fetching external docs |
| `humanizer` | Final prose pass | Requested by the brief. Not invoked before writing this draft; it is still owed on the sections the owner decides to keep |
| `using-agent-skills` | Confirming the skill set named in the brief is current | Confirmed against the live session roster rather than by loading the skill |
| `interview-me` | Ambiguous fence | Not needed as a flow: the two ambiguities went to `AskUserQuestion` and both were answered (see section 2, limit 1) |
| `document-generate` (Diataxis) | Type sanity-check | Unavailable under that bare name in this session; it is `gstack:document-generate`. Diataxis was applied by hand to the reference/explanation split in section 3.1 |
| `guard` | Alternative fence | Not loaded. The brief allowed `careful` or `guard`; `careful` was taken |

## 2. Method note, and its limits

**What was scanned.** Every tracked and every untracked-but-not-ignored file whose basename ends `.md`, `.txt`, `.rst`, `.adoc` or `.org`. Plus `.json`, `.yaml`, `.csv` and `.sql` under `docs/`. Plus `Makefile`, the one extensionless tracked file that carries prose. `artisan` is extensionless and tracked but is code, so it is out.

**What was excluded, with counts.** `.rst`, `.adoc` and `.org`: none exist. Ignored doc-shaped files: 2442, and they are not documentation of this project. The top of that pile: `vendor/` 351, `.agents/` 197, `node_modules/` 144, then a long tail of agent-config directories the repo carries but does not track: `.opencode` 137, `.kilo` 135, `.claude` 132, `.cursor` 127, `.kiro` 117, `.gemini` 106, `.augment` 60, `.codebuddy` 60, `.codewhale` 60, `.continue` 60, `.factory` 60. `research-scratch/` is ignored and holds 300-plus working files; `docs/vibe_images/` is untracked and holds 10 PNGs and no text. Both are noted, not inventoried.

**Tool limitations, all of which cost accuracy before I caught them.**

1. Two fences in the standing instruction collided with this brief: it asked for the deliverable at `docs/design-research/DOCUMENTATION-INVENTORY-2026-09-30.md`, and an earlier Authorized Execution Turn put `docs/design-research/**` on the forbidden-edit list. I stopped and asked. Owner's answer: write to `research-scratch/` instead. That is why this file is where it is.
2. The depth of section 5 was also ambiguous, and also asked. Owner's answer: full verification.
3. `git ls-files` quotes non-ASCII paths. That hid one untracked file, `docs/UMAMUSUME PRETTY DERBY — COMPREHENSIVE UX DELIVERABLES.md`, from my first two enumerations because the quoted line ends with `"` and not `.md`. Every count after `core.quotepath=false` is the corrected one. Anyone re-running this scan should pass that flag first.
4. Two regular-expression bugs reached a first draft, and I caught both only because the output looked wrong rather than because the check would have failed. `js` before `json` in an alternation matched every `.json` citation as `.js`. A greedy path prefix turned `PRD.md` into `D.md`, which zeroed the inbound count for all eleven root documents and made them look orphaned. A checker that reports the same shape for a correct and an inverted result is not a checker; both bugs were found by reading the output, not by the script.
5. Independent review was not performed. `doubt-driven-development` wants a fresh-context adversarial reviewer for each Status verdict, and three Explore agents in an earlier pass died on a daily usage limit. The verdicts here are single-observer. Each carries its evidence so a second reader can disagree against a fact rather than against a feeling.
6. **The tree moved during the scan.** It started at `85b37a4`, was at `89675e6` mid-pass, and finished at `c1e14a3`, three commits later, all three of them documentation commits by a concurrent session. `docs/adr/0013-character-profile-source.md` and `docs/data/2026-09-30-characters-source-probe.md` were branch-only at the start of this session and are tracked on master now, with ADR-0013 marked Withdrawn. A prior audit of mine said the opposite; that audit is at `research-scratch/unfinished-phases-audit-2026-09-29.md` and section 11 of it needs correcting forward, not overwriting.
7. Classification is judgment. Type and Status are the only judgment columns, Status is the one with a burden of proof, and every non-`unknown` Status in section 4 names its evidence in the same row.

## 3. Summary counts

**202 files in scope.** 193 tracked, 9 untracked, 0 ignored-and-in-scope.

| Dimension | Breakdown |
|---|---|
| By tracked state | tracked 193, untracked 9 |
| By extension | `.md` 100, `.txt` 95, `.json` 6, extensionless 1 (`Makefile`) |
| By directory | `docs/frontend-review/2026-09-28/` 97, `docs/design-research/**` 44, repo root 13, `docs/adr/` 13, `.ai/**` 12, `docs/scenarios/` 9, `docs/requests/**` 8, `docs/` (top) 5, `docs/data/` 3, `docs/flows/` 1, `docs/GATE-REGISTRY.md` and 3 sibling top-level `docs/` files counted above, `public/` 1 |
| By type | probe-record 104, reference 19, verification 15, adr 13, audit-report 9, constraint 6, brief 6, config-doc 5, spec 5, scenario 5, plan 3, research 3, readme 2, notes 2, other 2, session-log 2, issue-register 1 |
| By status | current 169, unknown 20, stale 8, superseded 5, duplicate 0 |

Why `duplicate` is empty: no two files here are duplicates in the sense a consolidation pass can act on. Six pairs cover overlapping ground while describing different things, and section 8 lists them as candidates rather than as duplicates. `probe-record` carrying 104 of 202 rows is also not an accident: 94 of them are one frozen capture set, described in section 4.2.

`unknown` is 20 rather than a smaller number because the Status column carries a burden of proof and most of those files have no evidence either way. Under `doubt-driven-development` the honest verdict for "this looks old but I cannot say what would prove it is current" is `unknown`, not `stale`.

## 4. The flat inventory

Columns: Path | bytes / lines | Tracked | Last commit | Type | Description | Status | Superseded by or duplicates | Inbound cites | Line-citation risk.

`Inbound cites` is how many other in-scope files name this file, from section 6. Read it precisely: the count is **distinct in-scope files whose citation resolves to this exact path**, so a file citing both `CONSTRAINTS.md` and `docs/design-research/CONSTRAINTS.md` contributes 1 to each of the two rows. A looser count of files whose text contains the bare basename gives 52 for root `CONSTRAINTS.md` instead of 45, because 29 files write the full path to the design-research twin and 22 of those write both. The gap is section 6.2's collision, not a counting error. `Line-citation risk` is `yes` when the file itself cites another file by `path:NNN`.

Verification of these columns is in section 14.

### 4.1 Authored documentation (105 files)

| `Makefile` | 3377 / 69 | tracked | `5820e77` 2026-09-29 | config-doc | Names the repo's gate targets: `lore`, `lore-code`, `test`, `lint`, `stan`. | current | - | 0 | no |
| `.ai/guidelines/custom/domain.md` | 2462 / 44 | tracked | `a72ee76` 2026-09-27 | config-doc | Tells agents the domain vocabulary, the directory boundaries and the authorization pattern to follow. | stale | - | 1 | no |
| `.ai/guidelines/framework/core.md` | 3000 / 62 | tracked | `a72ee76` 2026-09-27 | config-doc | Laravel Boost's framework rules for this repo: strict types, thin controllers, named routes. | stale | - | 0 | no |
| `.ai/rules/code-style.md` | 2444 / 68 | tracked | `a72ee76` 2026-09-27 | constraint | Records the formatting conventions Pint enforces, written down so agents can read them. | unknown | - | 0 | no |
| `.ai/rules/index.md` | 147 / 5 | tracked | `a72ee76` 2026-09-27 | config-doc | Maps file globs to the rule file an agent must read before editing that path. | stale | - | 3 | no |
| `.ai/rules/testing-standards.md` | 2697 / 59 | tracked | `a72ee76` 2026-09-27 | constraint | Sets the Pest 4 test conventions: layout, naming, database handling. | unknown | - | 0 | no |
| `.ai/skills/creating-models/SKILL.md` | 4379 / 141 | tracked | `a72ee76` 2026-09-27 | reference | How to scaffold an Eloquent model, migration, factory and seeder in this stack. | unknown | - | 0 | no |
| `.ai/skills/deploying-to-cloud/SKILL.md` | 2729 / 84 | tracked | `a72ee76` 2026-09-27 | reference | How to deploy to Laravel Cloud. Never triggered here; the product is local-only. | unknown | - | 0 | no |
| `.ai/skills/infer-conventions/SKILL.md` | 2810 / 57 | tracked | `a72ee76` 2026-09-27 | reference | How an agent should read neighbouring files and copy their conventions. | unknown | - | 0 | no |
| `.ai/skills/laravel-best-practices/SKILL.md` | 3410 / 109 | tracked | `a72ee76` 2026-09-27 | reference | Laravel review checklist: controllers, models, migrations, requests, policies, jobs. | unknown | - | 0 | no |
| `.ai/skills/pest-testing/SKILL.md` | 2388 / 77 | tracked | `a72ee76` 2026-09-27 | reference | Pest 4 syntax and structure for tests in this repo. | unknown | - | 0 | no |
| `.ai/skills/running-tests/SKILL.md` | 1511 / 43 | tracked | `a72ee76` 2026-09-27 | reference | How to invoke the suite and read the result. | unknown | - | 0 | no |
| `.ai/skills/tailwindcss-development/SKILL.md` | 1805 / 55 | tracked | `a72ee76` 2026-09-27 | reference | Tailwind v4 CSS-first usage: `@theme`, no config file, responsive grids. | unknown | - | 0 | no |
| `AGENTS.md` | 26470 / 390 | tracked | `6f9c98a` 2026-09-27 | spec | The role table, per-agent duties, escalation paths and the lore gate, plus embedded Laravel Boost guidance. | stale | - | 27 | no |
| `ARCHITECTURE-ESSENTIALS.md` | 13015 / 94 | tracked | `26aa9fe` 2026-09-29 | reference | The short digest of ARCHITECTURE.md that gets pasted into agent context. | unknown | - | 12 | no |
| `ARCHITECTURE.md` | 22672 / 324 | tracked | `26aa9fe` 2026-09-29 | spec | System design: schema, fetch pipeline, routes, security model, revision 0.2 marked by repo of origin. | unknown | - | 20 | no |
| `CONSTRAINTS.md` | 5308 / 48 | tracked | `e9a779d` 2026-09-30 | constraint | The global quality bar C-1 to C-9, each with a threshold and the command that proves it. | current | - | 45 | no |
| `DESIGN.md` (root) | 27646 / 394 | tracked | `bcd8abe` 2026-09-29 | constraint | The shipped visual system for Trainer Desk: tokens, components, surfaces. | current | - | 41 | yes |
| `KNOWN-ISSUES.md` | 150145 / 1944 | tracked | `89675e6` 2026-09-30 | issue-register | The defect register. Each entry names the command or file that proves it. | current | - | 25 | yes |
| `PLAN.md` | 68985 / 701 | tracked | `a4da6d1` 2026-09-30 | plan | The slice-by-slice frontend plan, rulings R-nn, and the open Livewire question with its evidence. | stale | - | 16 | yes |
| `PRD.md` | 20450 / 153 | tracked | `3bac088` 2026-09-30 | spec | Product truth: users, stories, functional requirements, non-goals, open questions. | current | - | 35 | no |
| `PRODUCT.md` | 8409 / 170 | tracked | `e697ce3` 2026-09-28 | reference | Product summary for design and agent context: name, audience, commitments. | unknown | - | 8 | no |
| `README.md` | 7657 / 136 | tracked | `bbfa3de` 2026-09-29 | readme | The front door and the documentation map that says which file owns which question. | current | - | 0 | no |
| `SKILL.md` | 19502 / 231 | tracked | `775b88a` 2026-09-27 | reference | A hand-counted table of the skills installed in this project. | stale | the `refresh-skill-registry` skill, which rebuilds this from disk (no in-repo file) | 1 | no |
| `source.md` | 9988 / 172 | tracked | `775b88a` 2026-09-27 | notes | The system prompt that initialised this repo through Laravel Boost, kept as the record of that step. | current | - | 1 | no |
| `docs/GATE-REGISTRY.md` | 12724 / 159 | tracked | `31f97a5` 2026-09-29 | constraint | Which gate enforces what, where the gate lives, and which gates are knowingly not automated. | current | - | 12 | yes |
| `docs/PRE-MORTEM.md` | 12682 / 98 | tracked | `775b88a` 2026-09-27 | research | What could go wrong in the consolidation, written before the artefacts it then shaped. | current | - | 11 | no |
| `docs/SKILL_AUTOMATION.md` | 3077 / 91 | tracked | `cf8021d` 2026-09-27 | config-doc | How the repo's skill-discovery hook finds and loads skills. | unknown | - | 1 | no |
| `docs/SOURCE-OF-TRUTH.md` | 19283 / 295 | tracked | `2033434` 2026-09-29 | reference | One page that restates the binding rules from seven other documents for agents that read only one. | stale | - | 10 | no |
| `docs/UMAMUSUME_REFERENCE.md` | 448522 / 2179 | tracked | `24e491c` 2026-09-29 | reference | The mechanics corpus, eight sections, source-cited and dated. Everything the UI asserts about the game traces here. | current | - | 41 | yes |
| `docs/data/2026-09-29-global-roster-crosscheck.md` | 36974 / 504 | tracked | `07941eb` 2026-09-30 | probe-record | Every Global costume card checked against two publishers, with the method and its date. | current | - | 2 | no |
| `docs/data/2026-09-30-characters-source-probe.md` | 13228 / 201 | tracked | `4ba2962` 2026-09-30 | probe-record | Re-resolves the `characters` source: coverage per field over 163 rows, measured before any column was drawn. | current | - | 3 | yes |
| `docs/data/roster-crosscheck-table.md` | 13112 / 109 | tracked | `cadb4e3` 2026-09-30 | other | The generated 107-row card table that the crosscheck narrative reads, produced by `tools/roster-crosscheck.php`. | current | - | 2 | no |
| `docs/adr/0001-lift-no-prediction-nongoal-for-energy-guidance.md` | 12315 / 118 | tracked | `27ff1f6` 2026-09-27 | adr | Accepted in part: the Energy guidance decision stands, its schema section moved into ADR-0003. | superseded | ADR-0003 for section 5, recorded inside the file | 1 | no |
| `docs/adr/0002-scenario-caps-exceed-validation-bound.md` | 14012 / 119 | tracked | `27ff1f6` 2026-09-27 | adr | Accepted: scenario caps pass the validation bound, so the bound moves to 0..2000. Not implemented yet. | current | - | 2 | no |
| `docs/adr/0003-consolidated-phase1-schema-expansion.md` | 23562 / 231 | tracked | `5c65597` 2026-09-28 | adr | The one schema amendment that absorbs Energy, Fans, `turn_events` and race tracking. | current | - | 3 | yes |
| `docs/adr/0004-aptitude-and-scenario-cap-reference-data.md` | 4859 / 76 | tracked | `775b88a` 2026-09-27 | adr | Accepted: aptitude letters and scenario caps stored as reference data rather than computed. | current | - | 1 | no |
| `docs/adr/0005-support-card-entities.md` | 13887 / 187 | tracked | `94db315` 2026-09-28 | adr | Declined for Phase 1 under owner ruling R37: no support-card entities, and the question stays closed. | superseded | closed by R37; the file is the record of that | 2 | no |
| `docs/adr/0006-design-authority-and-theme-default.md` | 5735 / 101 | tracked | `da6747a` 2026-09-27 | adr | Accepted, Option 2: light base, stored preference, then `prefers-color-scheme`. | current | - | 0 | no |
| `docs/adr/0007-c7-loading-state-scope-for-server-rendered-views.md` | 3879 / 73 | tracked | `e697ce3` 2026-09-28 | adr | Narrows C-7: an initial server render may rely on the browser's own loading, user-initiated async may not. | current | - | 1 | no |
| `docs/adr/0008-character-card-catalog-layer.md` | 36463 / 448 | tracked | `038dd43` 2026-09-30 | adr | The costume-card catalog layer, its tables, and a card reference on the run. | current | - | 5 | yes |
| `docs/adr/0009-scenario-slot-seeding.md` | 11917 / 174 | tracked | `b295d16` 2026-09-29 | adr | Ruled in part: Option A only, URA Finale from a committed client export. Options B and C stay open. | current | - | 1 | yes |
| `docs/adr/0010-legacy-selection-payload.md` | 7730 / 71 | tracked | `26aa9fe` 2026-09-29 | adr | Accepted: the Legacy Select read-back is one typed JSON payload on the run, not columns. | current | - | 1 | yes |
| `docs/adr/0011-skills-reference-import.md` | 12210 / 161 | tracked | `aa5b05c` 2026-09-29 | adr | Accepted in part: what the skills export actually carries, and the fields the owner authorised storing. | current | - | 2 | yes |
| `docs/adr/0012-card-detail-fields-and-images.md` | 18955 / 225 | tracked | `038dd43` 2026-09-30 | adr | Card detail fields, stat arrays, images, objectives. Its Decision 4 is what replaced ADR-0013. | current | - | 3 | yes |
| `docs/adr/0013-character-profile-source.md` | 16155 / 216 | tracked | `5a50900` 2026-09-30 | adr | The character profile source, withdrawn on 2026-09-30 in favour of ADR-0012 Decision 4. | superseded | ADR-0012 Decision 4, stated in its own Status line | 1 | yes |
| `docs/design-research/CONSTRAINTS.md` | 142636 / 973 | tracked | `9300e6c` 2026-09-29 | constraint | The user-facing design contract, D-numbered, covering Blade, CSS, client JS, copy and export headers. | current | - | 30 | yes |
| `docs/design-research/DESIGN.md` | 152232 / 1640 | tracked | `a22d38c` 2026-09-30 | spec | The Phase 2 design contract: screens, tokens, component anatomy and motion budgets. | current | - | 21 | yes |
| `docs/design-research/EXTERNAL-DESIGN-REVIEW-TRIAGE-2026-09-28.md` | 7908 / 131 | tracked | `3ad10da` 2026-09-28 | audit-report | Disposition of an outside design review, including an erratum against a count that was read off the wrong tree. | current | - | 0 | yes |
| `docs/design-research/HANDOFF-RACE-READ-PATH-2026-09-29.md` | 6051 / 80 | tracked | `7897684` 2026-09-29 | notes | Handoff note: the race read path moves to `race_catalog_slots`, and three collisions get named before anyone edits. | current | - | 1 | yes |
| `docs/design-research/RACE-CALENDAR-GAPS.md` | 19727 / 352 | tracked | `a8a52cd` 2026-09-29 | research | The open questions on the race calendar, each left open on purpose with what would close it. | current | - | 2 | yes |
| `docs/design-research/RAW-FINDINGS.md` | 34485 / 367 | tracked | `a7cabc0` 2026-09-27 | research | Visual research from the game client and store properties, recorded before the design contract was written. | current | - | 8 | no |
| `docs/design-research/SCENARIO-DIFFERENCES.md` | 29737 / 211 | tracked | `a51e2f0` 2026-09-27 | reference | Comparison matrix of the three Global career scenarios, built so the UI could be scenario-aware. | current | - | 9 | no |
| `docs/design-research/SCREENSHOT-MANIFEST.md` | 7617 / 80 | tracked | `a51e2f0` 2026-09-27 | reference | Triage of `docs/game-screenshots/` by scenario and screen type, so gaps in coverage are visible. | current | - | 5 | no |
| `docs/design-research/SESSION-CONSOLIDATION-2026-09-30.md` | 39814 / 187 | tracked | `c1e14a3` 2026-09-30 | audit-report | Verification of eight session reports against the tree, with the refutation rate and the claims that survived. | current | - | 0 | yes |
| `docs/design-research/SKILLS-GAPS.md` | 58715 / 710 | tracked | `da8d6c5` 2026-09-29 | audit-report | The skill-system gap audit, with the dark-theme measurement pass in section 8. | current | - | 6 | yes |
| `docs/design-research/replan-mobile-first.md` | 26200 / 372 | tracked | `9e896d5` 2026-09-29 | plan | Slice 16's replan: mobile-first, Legacy Select, and whether narrow screens can reach the content. | current | - | 2 | yes |
| `docs/design-research/_scratch/WEB-FINDINGS.md` | 28769 / 390 | tracked | `e0e043c` 2026-09-27 | probe-record | Measured findings from Cygames' web properties. Its own first line calls it the companion to `CLIENT-FINDINGS.md`, which is not in the tree. | stale | - | 1 | no |
| `docs/design-research/_scratch/accents.json` | 9643 / 519 | tracked | `e0e043c` 2026-09-27 | probe-record | Accent-colour measurements pulled from the screenshots. | unknown | - | 1 | no |
| `docs/design-research/_scratch/clusters.json` | 192287 / 10174 | tracked | `e0e043c` 2026-09-27 | probe-record | Colour clusters computed from the screenshot set. | unknown | - | 2 | no |
| `docs/design-research/_scratch/colorprobes.json` | 9616 / 685 | tracked | `e0e043c` 2026-09-27 | probe-record | Point colour probes taken against the screenshots. | unknown | - | 1 | no |
| `docs/design-research/_scratch/colorprobes2.json` | 3992 / 293 | tracked | `e0e043c` 2026-09-27 | probe-record | The second round of point colour probes. | unknown | - | 1 | no |
| `docs/design-research/_scratch/signatures.json` | 249719 / 1 | tracked | `e0e043c` 2026-09-27 | probe-record | Per-screenshot luminance, saturation and cell signatures, one JSON array on a single line. | unknown | - | 1 | no |
| `docs/design-research/_scratch/tokens.json` | 5584 / 252 | tracked | `e0e043c` 2026-09-27 | probe-record | The measured design tokens `tools/gate.py` reads as its anchor input. | current | - | 1 | no |
| `docs/design-research/prototypes/superseded/README.md` | 1550 / 16 | tracked | `e0e043c` 2026-09-27 | readme | Says plainly that the four prototypes here are kept for the record and are not the current design. | current | - | 0 | no |
| `docs/design-research/verification/cardless-band-2026-09-30.md` | 8404 / 130 | tracked | `8acb678` 2026-09-30 | verification | Record of the cardless band in the default trainee list, with the pixel measurements that left the fold question open. | current | - | 0 | yes |
| `docs/design-research/verification/design-pass-trainee-detail-2026-09-29.md` | 11848 / 170 | tracked | `a18d77a` 2026-09-29 | verification | Design pass on the trainee detail page and the run screen's skill selector, and what it deliberately did not touch. | current | - | 1 | yes |
| `docs/design-research/verification/slice-2-2026-09-28.md` | 21100 / 406 | tracked | `78697e9` 2026-09-28 | verification | Slice 2 record, including the destructive gate a safety policy refused three times. | current | - | 3 | yes |
| `docs/design-research/verification/slice-3-2026-09-28.md` | 12233 / 210 | tracked | `8c9faf9` 2026-09-29 | verification | Slice 3 record, KI-4's resolution and the untracked-file scope left open. | current | - | 2 | no |
| `docs/design-research/verification/slice-5-2026-09-28.md` | 17049 / 294 | tracked | `8c9faf9` 2026-09-29 | verification | Slice 5 record, and section 4, the round-trip latency numbers the Livewire decision rests on. | current | - | 5 | no |
| `docs/design-research/verification/slice-6-2026-09-28.md` | 16607 / 238 | tracked | `8c9faf9` 2026-09-29 | verification | Slice 6 record: adjudicating what Slice 5 left open and landing the doc amendments. | current | - | 3 | yes |
| `docs/design-research/verification/slice-7-2026-09-28.md` | 15006 / 246 | tracked | `0d2dbdc` 2026-09-28 | verification | Slice 7 record, the schema session R33-R37 where three questions were ruled together. | current | - | 2 | yes |
| `docs/design-research/verification/slice-8-2026-09-28.md` | 8912 / 131 | tracked | `5d2ddcc` 2026-09-28 | verification | Slice 8 record: the Unity Cup and Trackblazer panels on Slice 7's payloads. | current | - | 2 | yes |
| `docs/design-research/verification/slice-9-2026-09-28.md` | 5799 / 82 | tracked | `8c9faf9` 2026-09-29 | verification | Slice 9 record, R43-R49: branch reconciliation, register landing, audit fixes. | current | - | 5 | yes |
| `docs/design-research/verification/slice-10-2026-09-29.md` | 28995 / 426 | tracked | `93f860b` 2026-09-29 | verification | Slice 10 record: maintenance and drift, the lore line marker, the `scenario_slots` proposal. | current | - | 2 | yes |
| `docs/design-research/verification/slice-11-2026-09-29.md` | 13262 / 231 | tracked | `6300221` 2026-09-30 | verification | Slice 11 record: calendar slots, the free-race writer, KI-20 closed. | current | - | 3 | yes |
| `docs/design-research/verification/slice-12-2026-09-29.md` | 20612 / 360 | tracked | `c849fc1` 2026-09-29 | verification | Slice 12 record, and the halt: why T4 was not run and nothing was pushed. | superseded | master's own history, which moved past the halt it describes | 2 | yes |
| `docs/design-research/verification/slice-13-2026-09-29.md` | 20718 / 344 | tracked | `debc4d0` 2026-09-29 | verification | Slice 13 record: KI-21 closed, the ordinal fixed, R70's deferred push. | current | - | 3 | yes |
| `docs/design-research/verification/slice-14-2026-09-29.md` | 13999 / 255 | tracked | `952f41a` 2026-09-29 | verification | Slice 14 record: the tier question settled on per-race sourced evidence. | current | - | 4 | yes |
| `docs/design-research/verification/slice-15-2026-09-29.md` | 51714 / 822 | tracked | `ad7cb0a` 2026-09-29 | verification | Slice 15, the schema session: record, verification, and the register write that carried a peer's entries. | current | - | 2 | yes |
| `docs/flows/create-run-and-legacy-select.md` | 7275 / 117 | tracked | `8c9faf9` 2026-09-29 | reference | The create-run and Legacy Select flow, with the validation bound and the error states. | current | - | 2 | yes |
| `docs/requests/2026-09-29-catalog-roster-and-trainee-selector.md` | 16760 / 89 | tracked | `7fea901` 2026-09-29 | brief | The request that opened the catalog roster and searchable trainee selector work. | current | - | 2 | yes |
| `docs/requests/2026-09-29-catalog-roster-and-trainee-selector-plan.md` | 229496 / 3999 | tracked | `d7ae84c` 2026-09-30 | plan | The 3999-line implementation plan for that request, task by task, with the acceptance values quoted from the constraint files. | current | - | 4 | yes |
| `docs/requests/2026-09-29-catalog-roster-report.md` | 18935 / 357 | tracked | `6300221` 2026-09-30 | audit-report | The closing report on the roster and selector work. | current | - | 3 | yes |
| `docs/requests/game-mechanics-condition-labels.md` | 2752 / 61 | tracked | `8c9faf9` 2026-09-29 | brief | Asks for the three lowest Global mood-pill colours, and accepts an explicit UNVERIFIED answer. | current | - | 3 | no |
| `docs/requests/reports/2026-09-30-port-and-cleanup.md` | 11430 / 169 | tracked | `8c16edc` 2026-09-30 | session-log | Run report: three artefacts ported, ADR-0013 withdrawn, the branch deleted. | current | - | 1 | yes |
| `docs/scenarios/01-ura-finale.md` | 10687 / 133 | tracked | `3f631fc` 2026-09-29 | scenario | URA Finale guide for the Global EN server: the baseline scenario every other one is compared to. | current | - | 13 | no |
| `docs/scenarios/02-unity-cup.md` | 25669 / 259 | tracked | `3f631fc` 2026-09-29 | scenario | Unity Cup (Aoharu Hai) guide, Global EN: the team mechanic that replaces solo training. | current | - | 13 | no |
| `docs/scenarios/03-trackblazer.md` | 7664 / 69 | tracked | `3acbb06` 2026-09-27 | scenario | Trackblazer (Make a New Track) guide, Global EN, including its JP release history. | current | - | 7 | no |
| `docs/scenarios/04-trackblazer-umaguide.md` | 15960 / 266 | tracked | `3acbb06` 2026-09-27 | reference | The uma.guide community framing of Trackblazer decks: two frameworks and their assumptions. | current | - | 12 | no |
| `docs/scenarios/05-trackblazer-gametora.md` | 11514 / 216 | tracked | `3acbb06` 2026-09-27 | reference | GameTora's Trackblazer reference: objectives, grade points, conditions. | current | - | 15 | no |
| `docs/scenarios/06-unity-cup-gametora.md` | 18787 / 323 | tracked | `3acbb06` 2026-09-27 | reference | GameTora's Unity Cup reference, and the patch that makes older material wrong. | current | - | 8 | no |
| `docs/scenarios/07-grand-concert.md` | 8372 / 118 | tracked | `3f631fc` 2026-09-29 | scenario | Our Grand Concert: a known-gap stub. Every row is quoted from material already in the repository. | current | - | 10 | yes |
| `docs/scenarios/08-grand-masters-jp-only.md` | 12562 / 180 | tracked | `8c9faf9` 2026-09-29 | scenario | Grand Masters as a `[JP-Only]` note. Nothing in it may be imported until a Global date exists. | current | - | 4 | no |
| `docs/scenarios/09-global-race-calendar.md` | 81903 / 751 | tracked | `122d12b` 2026-09-28 | reference | Which race exists at which turn and what it costs to enter: the availability calendar `scenario_slots` reads. | current | - | 15 | yes |
| `public/robots.txt` | 24 / 2 | tracked | `fda6ff0` 2026-09-27 | other | Disallows all crawlers, which is the machine-readable form of the local-only promise. | current | - | 8 | no |
| `docs/Scenario-Specific User Flows & Frontend Specifications.md` | 65085 / 1174 | untracked | `never` | brief | Per-scenario user flows and frontend specifications, grounded in the reference docs already in the repo. | unknown | - | 0 | no |
| `docs/UMAMUSUME PRETTY DERBY — COMPREHENSIVE UX DELIVERABLES.md` | 103979 / 2144 | untracked | `never` | brief | An incoming external UX write-up, marked at the top as triaged on 2026-09-27 and retained rather than adopted. | superseded | its own banner, plus `docs/design-research/MECHANICS-TRANSLATION-TRIAGE.md` | 0 | yes |
| `docs/UX Behavior Specification - Umamusume Trainer Companion.md` | 32288 / 576 | untracked | `never` | spec | A synthesis of DESIGN.md, CONSTRAINTS.md, PRD.md, the ADRs and the scenario files into one behavioural contract. | unknown | - | 0 | no |
| `docs/design-research/FRONTEND-BRIEF-AUDIT.md` | 14644 / 191 | untracked | `never` | audit-report | Checks the incoming frontend brief against the tree and lists the corrections the brief owes. | current | - | 6 | yes |
| `docs/design-research/FRONTEND-SPEC-DIVERGENCE.md` | 22055 / 297 | untracked | `never` | audit-report | Divergence audit between a pasted frontend spec and what this app actually is. | current | - | 10 | yes |
| `docs/design-research/MECHANICS-TRANSLATION-TRIAGE.md` | 19343 / 234 | untracked | `never` | audit-report | Triage of an incoming write-up that recast every game system as a UX pattern catalog. | current | - | 10 | yes |
| `docs/design-research/TASK-16-RUN-VIEW-FRAME-BRIEF.md` | 30220 / 481 | untracked | `never` | brief | Task 16's brief for the run view axis: the two-region frame and the scenario comparison, revised against the tree at `a8a52cd`. | current | - | 1 | yes |
| `docs/requests/reports/2026-09-30-c5-down-enforcement-gap.md` | 4151 / 67 | untracked | `never` | audit-report | Records that C-5 requires a `down()` on every migration and nothing verifies it. | current | - | 0 | no |
| `docs/requests/reports/2026-09-30-characters-source-findings.md` | 12477 / 231 | untracked | `never` | probe-record | Characters-source findings handed to the profile-block slice. | current | - | 0 | no |

### 4.2 Frozen frontend-review captures (94 files)

Every file under `docs/frontend-review/2026-09-28/` with a `.txt` extension. All 94 landed in one commit, `c6c0567`, on 2026-09-28, except the three under `resolutions/`, which landed in `b6fa798` on 2026-09-29. Status is `current` for all 94 on one argument: a capture is a record of what was observed on a date, and the date is in the directory name. Nothing can make it stale except deleting it, and the brief forbids that judgment here.

| `docs/frontend-review/2026-09-28/api-training-runs-detail.txt` | 854 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Raw JSON body of the `api-training-runs-detail` endpoint as served on 2026-09-28. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/api-training-runs-index.txt` | 2079 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Raw JSON body of the `api-training-runs-index` endpoint as served on 2026-09-28. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/api-umamusume-detail.txt` | 333 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Raw JSON body of the `api-umamusume-detail` endpoint as served on 2026-09-28. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/api-umamusume-index.txt` | 686 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Raw JSON body of the `api-umamusume-index` endpoint as served on 2026-09-28. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-detail-populated-dark.png.console.txt` | 211 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Costume-card detail page in the `populated` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-detail-populated-dark.png.network.txt` | 343 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Costume-card detail page in the `populated` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-detail-populated-light.png.console.txt` | 211 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Costume-card detail page in the `populated` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-detail-populated-light.png.network.txt` | 343 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Costume-card detail page in the `populated` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-empty-dark.png.console.txt` | 214 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Catalog index list in the `empty` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-empty-dark.png.network.txt` | 349 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Catalog index list in the `empty` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-empty-light.png.console.txt` | 214 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Catalog index list in the `empty` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-empty-light.png.network.txt` | 349 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Catalog index list in the `empty` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-filter-announced-dark.png.console.txt` | 221 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Catalog index list in the `filter-announced` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-filter-announced-dark.png.network.txt` | 363 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Catalog index list in the `filter-announced` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-filter-announced-light.png.console.txt` | 221 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Catalog index list in the `filter-announced` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-filter-announced-light.png.network.txt` | 363 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Catalog index list in the `filter-announced` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-filter-global-light.png.console.txt` | 220 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Catalog index list in the `filter-global` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-filter-global-light.png.network.txt` | 361 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Catalog index list in the `filter-global` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-page2-out-of-range-dark.png.console.txt` | 205 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Catalog index list in the `page2-out-of-range` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-page2-out-of-range-dark.png.network.txt` | 331 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Catalog index list in the `page2-out-of-range` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-page2-out-of-range-light.png.console.txt` | 205 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Catalog index list in the `page2-out-of-range` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-page2-out-of-range-light.png.network.txt` | 331 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Catalog index list in the `page2-out-of-range` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-populated-dark.png.console.txt` | 198 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Catalog index list in the `populated` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-populated-dark.png.network.txt` | 317 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Catalog index list in the `populated` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-populated-light.png.console.txt` | 198 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Catalog index list in the `populated` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-populated-light.png.network.txt` | 317 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Catalog index list in the `populated` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-search-dark.png.console.txt` | 213 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Catalog index list in the `search` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-search-dark.png.network.txt` | 347 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Catalog index list in the `search` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-search-light.png.console.txt` | 213 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Catalog index list in the `search` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-search-light.png.network.txt` | 347 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Catalog index list in the `search` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/design-preview-all-dark.png.console.txt` | 309 / 2 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Design preview surface in the `all` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/design-preview-all-dark.png.network.txt` | 327 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Design preview surface in the `all` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/design-preview-all-light.png.console.txt` | 309 / 2 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Design preview surface in the `all` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/design-preview-all-light.png.network.txt` | 327 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Design preview surface in the `all` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/error-404-catalog-light.png.console.txt` | 207 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Not-found page in the `catalog` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/error-404-catalog-light.png.network.txt` | 335 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Not-found page in the `catalog` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/error-404-run-light.png.console.txt` | 205 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Not-found page in the `run` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/error-404-run-light.png.network.txt` | 331 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Not-found page in the `run` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/export-csv-run1.txt` | 96 / 3 | tracked | `c6c0567` 2026-09-28 | probe-record | Captured CSV export of run 1, kept at the capture root. The two copies are not identical, so this pair is a before/after, not a duplicate. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/export-json-run1.txt` | 1816 / 68 | tracked | `c6c0567` 2026-09-28 | probe-record | Captured JSON export of run 1, kept at the capture root. The two copies are not identical, so this pair is a before/after, not a duplicate. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/landing-default-light.png.console.txt` | 189 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Landing page in the `default` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/landing-default-light.png.network.txt` | 464 / 2 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Landing page in the `default` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/resolutions/export-csv-run1.txt` | 143 / 3 | tracked | `c6c0567` 2026-09-28 | probe-record | Captured CSV export of run 1, kept under `resolutions/`. The two copies are not identical, so this pair is a before/after, not a duplicate. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/resolutions/export-json-run1.txt` | 2004 / 74 | tracked | `c6c0567` 2026-09-28 | probe-record | Captured JSON export of run 1, kept under `resolutions/`. The two copies are not identical, so this pair is a before/after, not a duplicate. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/resolutions/run-detail-guided-preview-prg.txt` | 2869 / 60 | tracked | `c6c0567` 2026-09-28 | probe-record | Text of the run-detail guided-step preview state, captured while settling a resolution. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/review-queue-empty-dark.png.console.txt` | 195 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Match-candidate review queue in the `empty` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/review-queue-empty-dark.png.network.txt` | 311 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Match-candidate review queue in the `empty` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/review-queue-empty-light.png.console.txt` | 195 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Match-candidate review queue in the `empty` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/review-queue-empty-light.png.network.txt` | 311 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Match-candidate review queue in the `empty` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/review-queue-populated-dark.png.console.txt` | 195 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Match-candidate review queue in the `populated` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/review-queue-populated-dark.png.network.txt` | 311 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Match-candidate review queue in the `populated` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/review-queue-populated-light.png.console.txt` | 195 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Match-candidate review queue in the `populated` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/review-queue-populated-light.png.network.txt` | 311 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Match-candidate review queue in the `populated` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-create-form-dark.png.console.txt` | 209 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run creation form in the `default` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-create-form-dark.png.network.txt` | 339 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run creation form in the `default` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-create-form-light.png.console.txt` | 209 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run creation form in the `default` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-create-form-light.png.network.txt` | 339 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run creation form in the `default` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-create-form-validation-error-light.png.console.txt` | 224 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run creation form in the `validation-error` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-create-form-validation-error-light.png.network.txt` | 369 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run creation form in the `validation-error` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-empty-first-turn-dark.png.console.txt` | 204 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run detail page in the `empty-first-turn` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-empty-first-turn-dark.png.network.txt` | 329 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run detail page in the `empty-first-turn` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-empty-first-turn-light.png.console.txt` | 204 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run detail page in the `empty-first-turn` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-empty-first-turn-light.png.network.txt` | 329 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run detail page in the `empty-first-turn` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-guided-preview-dark.png.console.txt` | 225 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run detail guided-step preview in the `default` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-guided-preview-dark.png.network.txt` | 371 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run detail guided-step preview in the `default` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-guided-preview-light.png.console.txt` | 225 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run detail guided-step preview in the `default` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-guided-preview-light.png.network.txt` | 371 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run detail guided-step preview in the `default` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-no-scenario-dark.png.console.txt` | 204 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run detail page in the `no-scenario` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-no-scenario-dark.png.network.txt` | 329 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run detail page in the `no-scenario` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-no-scenario-light.png.console.txt` | 204 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run detail page in the `no-scenario` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-no-scenario-light.png.network.txt` | 329 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run detail page in the `no-scenario` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-trackblazer-dark.png.console.txt` | 204 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run detail page in the `trackblazer` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-trackblazer-dark.png.network.txt` | 329 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run detail page in the `trackblazer` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-trackblazer-light.png.console.txt` | 204 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run detail page in the `trackblazer` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-trackblazer-light.png.network.txt` | 329 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run detail page in the `trackblazer` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-unity-cup-dark.png.console.txt` | 204 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run detail page in the `unity-cup` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-unity-cup-dark.png.network.txt` | 329 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run detail page in the `unity-cup` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-unity-cup-light.png.console.txt` | 204 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run detail page in the `unity-cup` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-unity-cup-light.png.network.txt` | 329 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run detail page in the `unity-cup` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-unpriceable-dark.png.console.txt` | 204 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run detail page in the `unpriceable` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-unpriceable-dark.png.network.txt` | 329 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run detail page in the `unpriceable` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-unpriceable-light.png.console.txt` | 204 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run detail page in the `unpriceable` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-unpriceable-light.png.network.txt` | 329 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run detail page in the `unpriceable` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-ura-finale-dark.png.console.txt` | 204 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run detail page in the `ura-finale` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-ura-finale-dark.png.network.txt` | 329 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run detail page in the `ura-finale` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-ura-finale-light.png.console.txt` | 204 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run detail page in the `ura-finale` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-ura-finale-light.png.network.txt` | 329 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run detail page in the `ura-finale` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/runs-index-empty-light.png.console.txt` | 242 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run index list in the `empty` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/runs-index-empty-light.png.network.txt` | 405 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run index list in the `empty` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/runs-index-populated-dark.png.console.txt` | 202 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run index list in the `populated` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/runs-index-populated-dark.png.network.txt` | 325 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run index list in the `populated` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/runs-index-populated-light.png.console.txt` | 202 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run index list in the `populated` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/runs-index-populated-light.png.network.txt` | 325 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run index list in the `populated` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/up-health-page.txt` | 1876 / 44 | tracked | `c6c0567` 2026-09-28 | probe-record | Response body of the health-check page, captured during the 2026-09-28 review run. | current | - | 0 | no |

### 4.3 Frozen audit narrative (3 files)

These three are the prose half of the same capture set. The brief names the directory as do-not-touch, so they are inventoried and left alone.

| `docs/frontend-review/2026-09-28/README.md` | 28112 / 426 | tracked | `8c9faf9` 2026-09-29 | audit-report | The 2026-09-28 frontend audit: every user-facing page reviewed, with the findings and the evidence each one rests on. | current | - | 2 | yes |
| `docs/frontend-review/2026-09-28/DECISIONS-NEEDED.md` | 4692 / 82 | tracked | `b6fa798` 2026-09-29 | brief | The questions the audit could not answer itself, written up for the owner to rule on. | current | - | 1 | no |
| `docs/frontend-review/2026-09-28/RESOLUTIONS.md` | 13641 / 192 | tracked | `416d3a2` 2026-09-29 | session-log | The owner's answers to those questions, plus the re-captures that prove each resolution landed. | current | - | 1 | yes |

## 5. Grouped views

### 5.1 By type

| Type | Files | What the group is |
|---|---|---|
| probe-record | 104 | Measurements kept as evidence: 94 browser and API captures, 6 colour and token JSON files, the roster crosscheck, the characters probe |
| reference | 19 | Lookup material. Diataxis reference: the reader asks a question and looks up an answer. `docs/UMAMUSUME_REFERENCE.md`, `docs/SOURCE-OF-TRUTH.md`, `ARCHITECTURE-ESSENTIALS.md`, `PRODUCT.md`, `SKILL.md`, the flow doc, the two design-research comparison files, the four publisher-sourced scenario files, and the seven `.ai/skills/**` guides |
| verification | 15 | One record per slice, 2 and 3 and 5 to 15, plus the design pass and the cardless band |
| adr | 13 | `docs/adr/0001` to `0013`, each with an inline Status line. Two are not standing: 0001 superseded in part, 0013 withdrawn |
| audit-report | 9 | The frontend audit README, skills gaps, session consolidation, brief audit, spec divergence, mechanics triage, external design review triage, roster closing report, and the C-5 enforcement gap |
| constraint | 6 | `CONSTRAINTS.md`, `docs/design-research/CONSTRAINTS.md`, `docs/GATE-REGISTRY.md`, `.ai/rules/code-style.md`, `.ai/rules/testing-standards.md`, root `DESIGN.md` |
| config-doc | 5 | `Makefile`, the two `.ai/guidelines/**`, `.ai/rules/index.md`, `docs/SKILL_AUTOMATION.md` |
| spec | 5 | `PRD.md`, `ARCHITECTURE.md`, `AGENTS.md`, `docs/design-research/DESIGN.md`, and the untracked UX behaviour spec |
| brief | 6 | Two repo requests, `DECISIONS-NEEDED.md` from the audit, and three incoming external write-ups parked untracked. Also `TASK-16-RUN-VIEW-FRAME-BRIEF.md` |
| scenario | 5 | `docs/scenarios/01`, `02`, `03`, `07`, `08`. The four publisher-sourced files, `04`, `05`, `06`, `09`, are typed `reference` instead |
| plan | 3 | `PLAN.md`, `docs/design-research/replan-mobile-first.md`, the 3999-line roster implementation plan |
| research | 3 | `docs/PRE-MORTEM.md`, `RAW-FINDINGS.md`, `RACE-CALENDAR-GAPS.md` |
| readme | 2 | Root `README.md` and `docs/design-research/prototypes/superseded/README.md` |
| notes | 2 | `source.md`, `docs/design-research/HANDOFF-RACE-READ-PATH-2026-09-29.md` |
| other | 2 | `public/robots.txt`, `docs/data/roster-crosscheck-table.md`, the generated table |
| issue-register | 1 | `KNOWN-ISSUES.md` |
| session-log | 2 | `docs/requests/reports/2026-09-30-port-and-cleanup.md`, and `RESOLUTIONS.md` from the frozen audit |

Two type calls worth defending:

- `docs/frontend-review/2026-09-28/README.md` is `audit-report`, not `readme`. The filename says README; the content is a 426-line audit. A glob for `README.md` will treat it as an entry point and get that wrong.
- The nine `docs/scenarios/*.md` split across two types because they answer two different questions. `01`, `02`, `03`, `07`, `08` describe the game as this repo understands it. `04`, `05`, `06`, `09` are publisher and community material held at arm's length with their own dates. Consolidating on filename similarity would merge a primary source into a secondary one.

### 5.2 By lifecycle state

| Status | Files | Members worth naming |
|---|---|---|
| current | 169 | All 94 captures, all 11 live ADRs, the scenario guides, the slice records, the constraint files |
| stale | 8 | `SKILL.md`, `AGENTS.md`, `PLAN.md`, `docs/SOURCE-OF-TRUTH.md`, `.ai/guidelines/custom/domain.md`, `.ai/guidelines/framework/core.md`, `.ai/rules/index.md`, `docs/design-research/_scratch/WEB-FINDINGS.md` |
| superseded | 5 | `ADR-0013` by ADR-0012 Decision 4; `ADR-0001` section 5 by ADR-0003; `ADR-0005` closed by R37; `slice-12` by master's own history; the untracked `COMPREHENSIVE UX DELIVERABLES` by its own triage banner |
| unknown | 20 | The seven `.ai/skills/**` guides, the five `_scratch/*.json` measurement files, `.ai/rules/code-style.md`, `.ai/rules/testing-standards.md`, the three untracked incoming specs, `ARCHITECTURE.md`, `ARCHITECTURE-ESSENTIALS.md`, `PRODUCT.md`, `docs/SKILL_AUTOMATION.md`, the UX behaviour spec |
| duplicate | 0 | Nothing met the bar. Section 8 explains the six near-misses |

### 5.3 By authoring session

The repo has one committer, `IzzatFirdaus`, across every commit, so "which session wrote this" is only recoverable to the commit that last touched the file. Guessing beyond that would be invention. 61 distinct commits last touched a file in scope.

| Last-touching commit | Files | Subject |
|---|---|---|
| `c6c0567` | 91 | `docs(frontend-review): screenshot audit of every user-facing page` |
| `a72ee76` | 12 | `docs: add Laravel project coding rules and boost guidelines` |
| no commit | 9 | The untracked set: four incoming write-ups, four reports, one spec |
| `e0e043c` | 8 | `docs(research): commit design research reports and measurement provenance` |
| `8c9faf9` | 8 | `feat(lore,docs): add a line-scoped lore marker and re-baseline the count on it` |
| `775b88a` | 4 | `docs: record product truth, design system, and schema ADRs` |
| `b6fa798` | 4 | `docs(frontend-review): resolutions record, decision requests, and re-captures` |
| `3acbb06` | 4 | `docs(scenarios): add Type-3 metadata blocks; supersede pre-release Trackblazer` |
| `26aa9fe` | 3 | `feat(legacy): record the Legacy Select read-back as one typed payload (D-268)` |
| `3f631fc` | 3 | `docs(scenarios): point 01, 02 and 07 at the race calendar instead of restating` |
| the other 51 commits | 53 | One, two or three files each |

The shape matters more than the rows: 91 of 202 files were written by one commit on one day, and the remaining 111 came from 60 separate decisions. A consolidation pass that touches the capture set touches 45 percent of the inventory at once.

### 5.4 By directory

| Directory | Files | Role |
|---|---|---|
| `docs/frontend-review/2026-09-28/` | 97 | One frozen audit: 3 narrative files, 94 captures |
| `docs/design-research/` | 44 | 16 top-level, 15 verification records, 6 `_scratch` JSON, 1 prototypes README, plus the 4 untracked files that live here |
| repo root | 13 | The governing set: PRD, ARCHITECTURE, CONSTRAINTS, DESIGN, KNOWN-ISSUES, PLAN, AGENTS, README, PRODUCT, SKILL, source, ARCHITECTURE-ESSENTIALS, Makefile |
| `docs/adr/` | 13 | ADR-0001 to 0013, one file per number, no gaps |
| `.ai/` | 12 | Boost-generated rules, guidelines and skills, all written 2026-09-27 and none updated since |
| `docs/scenarios/` | 9 | Per-scenario guides and publisher references |
| `docs/requests/` | 8 | 4 tracked at its top, 3 untracked and 1 tracked under `reports/` |
| `docs/data/` | 3 | Dated measurements: the crosscheck narrative, its generated table, the characters probe |
| `docs/` top level | 5 | PRE-MORTEM, SOURCE-OF-TRUTH, GATE-REGISTRY, SKILL_AUTOMATION, UMAMUSUME_REFERENCE |
| `docs/flows/` | 1 | The create-run flow |
| `public/` | 1 | robots.txt |
| 3 untracked files with `docs/` paths | 3 | Two incoming specs and the triaged deliverables write-up, all with spaces in their names |

## 6. Cross-reference map

Method matters here, so it goes first. This repo does not use markdown links between its documents. Across all 202 files there are 4 links written as a markdown link. Everything else is an inline path citation: "per `docs/PRE-MORTEM.md`", a backticked `CONSTRAINTS.md` with a gate number, "see `slice-5-2026-09-28.md` section 4". A consolidation pass that grepped for markdown link syntax would find four edges and conclude the corpus is unlinked. It is the most densely linked thing I have measured here.

Built from 3238 path-shaped tokens, the map holds **1218 resolved edges across 88 source files**. Of those tokens, 2555 name a file that exists and 683 do not. Section 6.3 explains the 683, because most of them are not errors.

The adjacency list is at `research-scratch/docinv_xref.json`, next to this file. It is a working artefact, not a repo document.

### 6.1 Orphans: 107 files nothing cites

94 of them are the capture set, and a capture is not meant to be cited. The other 13 are worth reading as a list, because being uncited is usually a signal.

| Orphan | Reading |
|---|---|
| `README.md` | The front door and the documentation map. Nothing links to it, so an agent that starts anywhere else never finds the map. Cheapest orphan to close |
| `.ai/guidelines/framework/core.md`, `.ai/rules/code-style.md`, `.ai/rules/testing-standards.md`, and all seven `.ai/skills/**/SKILL.md` | Loaded by tooling through `.ai/rules/index.md` and the Boost integration rather than by prose. Orphaned by design, which is why `.ai/rules/index.md` being stale matters |
| `docs/adr/0006-design-authority-and-theme-default.md` | The ADR that settled the theme default, cited by nobody. PLAN.md's Open Decisions list still names the theme default as open, and that is the cost of an orphaned ruling |
| `docs/design-research/SESSION-CONSOLIDATION-2026-09-30.md` | The verification of eight session reports, cited by nothing, landed in the same commit as its own subject line |
| `docs/design-research/EXTERNAL-DESIGN-REVIEW-TRIAGE-2026-09-28.md` | Its erratum is load-bearing and unread |
| `docs/design-research/prototypes/superseded/README.md` | Says four prototypes are not current. It has to exist for exactly that reason, so this orphan is fine |
| `docs/design-research/verification/cardless-band-2026-09-30.md` | Newest record in the tree. The ruling it carries lives in its body rather than in a register |
| `docs/requests/reports/2026-09-30-c5-down-enforcement-gap.md` | Marked "recorded, not filed". It is an orphan because it is waiting |
| `docs/requests/reports/2026-09-30-characters-source-findings.md` | Handoff material for a slice that has since landed |
| The three untracked `docs/` specs | Section 9 |

### 6.2 Hubs: 60 files carry five or more inbound citations

| File | Inbound | File | Inbound |
|---|---|---|---|
| `CONSTRAINTS.md` | 45 | `docs/scenarios/05-trackblazer-gametora.md` | 15 |
| `docs/UMAMUSUME_REFERENCE.md` | 41 | `docs/scenarios/09-global-race-calendar.md` | 15 |
| `DESIGN.md` at the root | 41 | `docs/scenarios/01-ura-finale.md` | 13 |
| `PRD.md` | 35 | `docs/scenarios/02-unity-cup.md` | 13 |
| `docs/design-research/CONSTRAINTS.md` | 30 | `docs/scenarios/04-trackblazer-umaguide.md` | 12 |
| `AGENTS.md` | 27 | `ARCHITECTURE-ESSENTIALS.md` | 12 |
| `KNOWN-ISSUES.md` | 25 | `docs/GATE-REGISTRY.md` | 12 |
| `resources/css/app.css` | 21 | `docs/PRE-MORTEM.md` | 11 |
| `config/uma.php` | 21 | `docs/SOURCE-OF-TRUTH.md` | 10 |
| `docs/design-research/DESIGN.md` | 21 | `docs/scenarios/07-grand-concert.md` | 10 |

The four highest inbound counts belong to two pairs of files that share a bare name. A citation reading `CONSTRAINTS.md` resolves to one of two files. A citation reading `DESIGN.md` resolves to one of two files. Consolidation must not break either pair, and section 7 shows the ambiguity already broke ten citations.

Two more numbers worth holding. `app/Models/TrainingRun.php` takes 13 inbound citations from documentation, and `tools/gate.py` takes 16. Documents reach into code more often than they reach into other documents, so any plan to merge docs has to account for the code anchors as well.

### 6.3 Dead links: the 683 unresolved tokens, sorted by what they mean

Calling these dead links would be wrong, and the distinction is the finding.

| Bucket | Tokens | What it is |
|---|---|---|
| Game-client data-file names | 368 | `scenarios.json`, `items.json`, `support-cards.json`, `events__champions-meeting.json`. Provenance for a claim about the client, never a repo path. Correct as written |
| Local database dumps | 116 | `database/database.sql`, `database/scratch-catalog.sql`, and a `PWD/` variant of the second. Gitignored working data, named as the thing a measurement ran against |
| Gitignored repo files | 67 | `CLAUDE.md` 22, `.agents/skills.json` 11, `research-scratch/**` and others. These exist on this machine and nowhere else |
| Bare view names needing a directory | 33 | `catalog/index.blade.php`, `runs/show.blade.php`, `show.blade.php`. Four files share the basename `index.blade.php`. Resolvable to a human, unresolvable to a script |
| Route names read as filenames | 26 | `runs.sh` 19 and `catalog.sh` 6 are the route names `runs.show` and `catalog.show`. My regex's fault, not the repo's |
| Renamed or absent JS config | 16 | `resources/js/app.js` 8 while the tree holds `app.ts`; `tailwind.config.js` 5 while Tailwind v4 is CSS-first with no config file |
| Deleted files | 19 | `welcome.blade.php` 12 plus `resources/views/welcome.blade.php` 7. Real dead links, all left by one deletion, covered in section 7 |
| Scratch scripts | 8 | `final_measure.py`, `scan-by-gate.py` |
| Library names | 7 | `Alpine.js`. Not a path |
| Absent class files | 7 | `app/Providers/EventServiceProvider.php`, `app/Actions/UpsertCharacterCard.php` |
| Short or fragmentary names | 12 | `DELIVERABLES.md` 6, `Companion.md` 4, the elided migration path 2 |

Two rows deserve a sentence each, because they are defects rather than grammar.

`CLAUDE.md` is cited 22 times by tracked documents and is ignored by `.gitignore:52`. `AGENTS.md` tells every agent to read it. A fresh clone gets the instruction and not the file. `docs/SOURCE-OF-TRUTH.md` compounds this by listing `CLAUDE.md` as one of the seven documents it consolidates.

`tailwind.config.js` is cited 5 times. The project's own frontend rule says the `@theme` block in `resources/css/app.css` is law, with no config file. Whatever cites the config file describes a stack that was replaced before the first commit.

### 6.4 Cycles: 65 mutual pairs

The cycles here are structural and intentional. `CONSTRAINTS.md` and `docs/GATE-REGISTRY.md` cite each other because the registry says CONSTRAINTS wins and CONSTRAINTS says the registry holds the enforcement detail. `AGENTS.md` and `PRD.md` do it because roles point at product truth and product truth points at roles. The concentration sits in the governing set, which is what a small corpus with real precedence looks like.

One pair needs naming. `docs/design-research/CONSTRAINTS.md` and root `CONSTRAINTS.md` cite each other by bare name. Both are hubs. Neither can be renamed without editing the other.

## 7. Line-citation rot scan

391 citations name a target plus a line or a range. I opened each one and matched it against what the citing sentence claims. Verdicts are deliberately conservative: a citation is only called wrong when the evidence proves it, and 184 stay unknown because proving either direction needs a human reading the citing sentence and the target together.

| Verdict | Count | Basis |
|---|---|---|
| Resolvable | 155 | The cited range holds the anchor the citing line names, or a distinctive identifier from it |
| Stale | 19 | The named anchor exists in the target at a demonstrably different line |
| Broken | 33 | The target file or the target range does not exist |
| Unknown | 184 | No anchor and no shared identifier. This is not a claim that they are wrong |

Six of the 19 stale rows are false positives of my own detector: it read an ADR's own number as an anchor that had moved. I excluded those from 7.3. The remaining rows are real, and three I confirmed by hand.

### 7.1 Broken, grouped by cause

| Cause | Rows | Fix shape |
|---|---|---|
| Twin-name ambiguity. `DESIGN.md` and `CONSTRAINTS.md` cited bare, and the line only resolves under `docs/design-research/` | 10 | Rewrite with the full path, or as a heading citation |
| Bare view name where four files share the basename | 9 | Add the directory |
| `resources/views/welcome.blade.php`, deleted at `65f8b92` on 2026-09-29 while closing KI-20 | 7 | Re-point or retire. `KNOWN-ISSUES.md` carries three, `PLAN.md` one, `slice-7` and `slice-10` one each |
| Path written as an ellipsis: `...create_scenario_slots_table.php:58-59` | 2 | Write the real filename |
| `DELIVERABLES.md` standing in for an untracked file whose real name contains an em dash | 1 | Decide the file's fate first, then cite it properly |
| `Companion.md:136-141` used as a fragment | 1 | Full path |
| `research-scratch/rehearse-apply.php:69`, a gitignored script cited by a tracked record | 1 | Quote the measured value into the record instead |
| `design-preview.blade.php:12-27`, a prototype-era view that no longer exists | 1 | Historical, inside a frozen audit. Leave it |
| `CharacterProfileTest.php:183`, on a branch and never on master | 1 | `docs/requests/reports/2026-09-30-port-and-cleanup.md:144` cites a test that was withdrawn along with ADR-0013 |

### 7.2 The rot the brief predicted, measured

The brief gave one example: `DESIGN.md:191` cites `KNOWN-ISSUES.md:855-861`. I checked it, and I checked the correction that was later written for it.

`docs/design-research/DESIGN.md:191` says the four ratios KI-20 records live at `KNOWN-ISSUES.md:855-861`. KI-20's heading is at line **994**. Lines 855 to 861 sit inside **KI-15**, which begins at 837.

`docs/design-research/SESSION-CONSOLIDATION-2026-09-30.md:59` found this defect and recorded the fix as "KI-20 begins at `:987`". The heading is at 994, not 987. Its own range claim, that 855 to 861 "now sits inside KI-10", is also wrong: KI-10 begins at 575 and KI-11 at 657, so 855 is two entries further down the file.

That exchange is the reason section 11 exists. A correction written as a line number becomes a second wrong citation. The heading is the durable reference, the `## KI-20` line with its title, and this repo already has that convention available in every register entry.

### 7.3 Stale rows proven

| Citation | Anchor actually at | Note |
|---|---|---|
| `design-pass-trainee-detail-2026-09-29.md:70` to `KNOWN-ISSUES.md:1347` for KI-29 | 1372 | Line 1347 is a table separator. Off by 25 |
| `MECHANICS-TRANSLATION-TRIAGE.md:173` to `SCENARIO-DIFFERENCES.md:122` for ADR-0002 | 106 | |
| `SKILLS-GAPS.md:217` to `PRD.md:73` for NFR-3 | 117 | Two rows in the same file, same mistake |
| `slice-6-2026-09-28.md:147` to `docs/design-research/DESIGN.md:1338` for KI-14 | 1438 | |
| `slice-10-2026-09-29.md:44` to `app/Enums/SpiritBurstState.php:47` for KI-18 | 40 | |
| `FRONTEND-SPEC-DIVERGENCE.md:258` to `config/scenarios.php:83,116` | The D-240 marker the sentence means sits at line 13 | The cited range describes markers that file does not have |

### 7.4 Files that carry the rot

| File | Line citations | Of them broken or stale |
|---|---|---|
| `KNOWN-ISSUES.md` | 66 | 3 broken |
| `docs/design-research/FRONTEND-SPEC-DIVERGENCE.md` | 29 | 6 broken, 1 stale |
| `docs/design-research/verification/slice-10-2026-09-29.md` | 20 | 3 broken, 2 stale |
| `docs/GATE-REGISTRY.md` | 6 | 0 |
| `docs/design-research/verification/slice-15-2026-09-29.md` | 4 | 1 broken |
| `docs/requests/2026-09-29-catalog-roster-and-trainee-selector-plan.md` | 9 of 83 outbound edges | 3 broken |

`KNOWN-ISSUES.md` holds 66 of the 391 line citations, and the file runs to 1944 lines. Every entry in it has a heading. It is the clearest case in the repository for switching to heading citations.

## 8. Duplicate candidates

No pair met the definition of a duplicate: same content, one file to keep. Six pairs look like duplicates and are not. Three are genuine consolidation targets. All nine are below, with the overlap cited.

| Candidate pair | Shared ground | Newer | More complete | Recommendation |
|---|---|---|---|---|
| `CONSTRAINTS.md` at the root (48 lines) and `docs/design-research/CONSTRAINTS.md` (973) | Both are constraint contracts with numbered gates | Root, touched `e9a779d` 2026-09-30 | design-research, by 925 lines | **Keep both.** The root file holds C-1 to C-9 for the whole repo; the other holds D-numbered rules for the user-facing layer only. `docs/GATE-REGISTRY.md:3` states the precedence between them. Merge would destroy a two-level gate system. The real defect is the shared basename, not the shared subject |
| `DESIGN.md` at the root (394) and `docs/design-research/DESIGN.md` (1640) | Both specify tokens, components and surfaces | Root, `bcd8abe` 2026-09-29 | design-research, by 1246 lines | **Keep both, rename one or disambiguate.** The root file's H1 is "Trainer Desk design system"; the other's is "Umamusume Trainer Companion design system", the name from before OQ-1 closed on 2026-09-27. Ten broken citations in section 7.1 come from readers not knowing which one a bare `DESIGN.md` means |
| `docs/data/2026-09-29-global-roster-crosscheck.md` and `docs/data/roster-crosscheck-table.md` | The same 107 cards | narrative, `07941eb` | narrative | **Keep both.** Line 141 of the narrative says the table is generated by `php tools/roster-crosscheck.php` and the narrative reads it back. They are a document and its data file. One drift trap: the narrative carries a date in its name and the generated table does not |
| `docs/scenarios/03-trackblazer.md`, `04-trackblazer-umaguide.md`, `05-trackblazer-gametora.md` | Trackblazer | `3f631fc` 2026-09-29 touched 03 | 04 and 05 by length | **Keep all three.** 03 is this repo's guide. 04 is uma.guide's community framing and 05 is GameTora's reference, each a separate publisher with its own reliability. Merging them would put a primary claim and a secondary claim in the same table cell |
| `docs/requests/2026-09-29-catalog-roster-and-trainee-selector.md`, `-plan.md`, `-report.md` | One workstream | report, `6300221` 2026-09-30 | plan, at 3999 lines | **Keep all three.** Request, plan and closing report are three different acts. What is missing is a line at the top of each naming the other two, because the filenames differ only by suffix and the reader has to guess the order |
| `docs/frontend-review/2026-09-28/export-csv-run1.txt` and `.../resolutions/export-csv-run1.txt`, plus the JSON pair | Identical names | `resolutions/`, `b6fa798` 2026-09-29 | differs by content | **Keep both.** This is the one the brief warned about. `git hash-object` gives `37661caa` and `a5d7a771` for the CSV pair, `097e1ce4` and `3e03d32e` for the JSON. The resolutions copies are re-captures that prove a fix landed. A duplicate pass keyed on filename would delete the evidence |
| `docs/UMAMUSUME PRETTY DERBY — COMPREHENSIVE UX DELIVERABLES.md` (2144) and `docs/Scenario-Specific User Flows & Frontend Specifications.md` (1174) and `docs/UX Behavior Specification - Umamusume Trainer Companion.md` (576) | Incoming external UX material, 3894 lines between them | All three dated in their banners as reviewed 2026-09-27 | deliverables, by 968 lines | **Decide, then merge or delete.** All three carry a "not merged" banner and all three are untracked. Their audits, `FRONTEND-SPEC-DIVERGENCE.md` and `MECHANICS-TRANSLATION-TRIAGE.md`, are also untracked. This is the largest single block of undecided documentation in the repo |
| `README.md` at the root, `docs/frontend-review/2026-09-28/README.md`, `docs/design-research/prototypes/superseded/README.md` | Same filename | root, `bbfa3de` | different jobs | **Keep all three.** The audit's README is a 426-line audit report and the prototypes README is a 16-line warning label. Only the root one is a readme |
| `.ai/skills/*/SKILL.md` (7 files) and `SKILL.md` at the root | Skill documentation | root, `775b88a` | the seven, each a real skill | **Keep the seven, resolve the root.** Root `SKILL.md` names itself "Skill Registry" and hand-counts what is installed. Section 10 explains why it should go |

## 9. Untracked material

Nine text files, no history, no owner of record. Nothing here is committed and nothing here should be, until the owner decides.

| Path | Size / lines | Contents in one sentence | Plausible origin | Cited by a tracked file | Reads as |
|---|---|---|---|---|---|
| `docs/Scenario-Specific User Flows & Frontend Specifications.md` | 65085 / 1174 | Per-scenario flows and screen specs, with a banner saying it was reviewed and largely sound but not merged | Incoming external write-up, reviewed 2026-09-27 | Yes, by its own banner pointing at `FRONTEND-SPEC-DIVERGENCE.md` section 6. Nothing tracked cites it by path | Finished, and deliberately parked |
| `docs/UMAMUSUME PRETTY DERBY — COMPREHENSIVE UX DELIVERABLES.md` | 103979 / 2144 | An external UX deliverables document triaged on 2026-09-27 and kept for reference rather than adopted | Incoming external write-up | Yes, six times, as the short name `DELIVERABLES.md`, which resolves to nothing. `docs/UMAMUSUME_REFERENCE.md:386` is one | Finished, rejected by its own banner |
| `docs/UX Behavior Specification - Umamusume Trainer Companion.md` | 32288 / 576 | A behavioural contract synthesised from DESIGN, CONSTRAINTS, PRD, the ADRs and the scenario files | Incoming external write-up, reviewed 2026-09-27 | Only through its banner. Its closing line claims every input and state is sourced from the design documents | Finished, with three load-bearing corrections named in its banner |
| `docs/design-research/FRONTEND-BRIEF-AUDIT.md` | 14644 / 191 | Compares an incoming frontend brief against the tree and lists the corrections that brief owes | This repo's own analysis, 2026-09-27 | 6 inbound | This repo's output, and the audit side of a pair the repo has not decided to keep |
| `docs/design-research/FRONTEND-SPEC-DIVERGENCE.md` | 22055 / 297 | Divergence audit between a pasted frontend spec and the app this repo is building | This repo's own analysis | 10 inbound | In progress. PLAN.md lines 556 to 565 list corrections it owes that were never applied, and it holds 6 broken and 1 stale citation of its own |
| `docs/design-research/MECHANICS-TRANSLATION-TRIAGE.md` | 19343 / 234 | Triage of a write-up that recast every game system as a UX pattern catalog | This repo's own analysis | 10 inbound | Finished. Its section 7 is the item-by-item audit the deliverables banner points at |
| `docs/design-research/TASK-16-RUN-VIEW-FRAME-BRIEF.md` | 30220 / 481 | Task 16's brief for the run view axis, revised against the tree at `a8a52cd` | A dispatch brief written for another session | 1 inbound, plus 2 broken citations out | In progress. The task it describes has a verification record, `slice-12`, and no landed frame |
| `docs/requests/reports/2026-09-30-c5-down-enforcement-gap.md` | 4151 / 67 | Says C-5 requires a `down()` on every migration and nothing checks for it | This session's finding, 2026-09-30 | No | Deliberately parked. Its own header says recorded and not filed, because a peer session is mid-write on ten tracked files |
| `docs/requests/reports/2026-09-30-characters-source-findings.md` | 12477 / 231 | Characters-source findings handed to the profile-block slice | This repo's measurement pass, 2026-09-30 | No | Superseded on the spot. Its subject landed: `docs/data/2026-09-30-characters-source-probe.md` is tracked and covers the same ground |

One more untracked path, noted not inventoried as the brief instructs: `docs/vibe_images/` holds 10 PNGs totalling roughly 13 MB, dated 2026-09-29, named after prototype screens such as `screen-a-ura-finale-v9_...png`. They are generated design mockups, not text, and no tracked file names them.

They are also untracked by accident. `git check-ignore -v docs/vibe_images/` answers with `.gitignore:102`, and line 102 is not the rule anyone meant to write. `cat -A` on lines 98 to 103 shows the file's tail is partly UTF-16LE: line 102 reads `.^@s^@c^@r^@a^@t^@c^@h^@-^@u^@m^@a^@/^@^M^@$`, which is `.scratch-uma/` with a NUL between every character and a CRLF at the end. The intended `/vibe_images/` rule sits at line 100 and is root-anchored, so it cannot match `docs/vibe_images/`. Git therefore reports the dead UTF-16 line as the match, matches nothing, and leaves 13 MB of PNGs in every `git status`. The same encoding damage explains why `grep` called `.gitignore` a binary file earlier in this pass.

The consequence for this inventory is small and worth stating: those 10 files look like untracked documentation noise to every agent that reads `git status`, and one of the two reasons they are noise is a NUL byte.

**Two facts about the untracked set that a consolidation pass has to survive.**

The lore exposure is small and specific. A plain grep, because `make lore` uses `git grep` and cannot see any of these files, finds 2 word-boundary hits in the deliverables document, both the word `lineage` at lines 741 and 762, and 2 in the UX behaviour spec, both the word `equine` at lines 124 and 566, where the lines are compliance-table rows naming the ban rather than breaking it. The other 34 pattern matches across these three files are substrings inside words like `detailed` and `Tailwind`, and the correct whole-word count for `tail` in all three files is zero. Under `docs/GATE-REGISTRY.md` allowed-hit classes 1 and 2 most of this clears, and the Guardian rules, not the grep.

The triage record is as untracked as the material it rejects. Three incoming write-ups and the two audits that triaged them are all untracked. A fresh clone gets none of the five, while `docs/UMAMUSUME_REFERENCE.md` on master cites the deliverables file by a short name. Either the whole set gets tracked together or the citing line gets rewritten. Half of it cannot be decided.

## 10. Consolidation candidates, ranked

Each row states the evidence, which is in an earlier section, and what acting on it would cost.

1. **Fix the twin-name collision before anything else.** Ten broken citations in 7.1 come from readers writing `DESIGN.md` or `CONSTRAINTS.md` when they mean `docs/design-research/…`, and both pairs are hubs with 41 and 45 inbound. Two options, and they are different jobs: require the full path in every citation, or rename the design-research pair. Renaming breaks 51 inbound citations in the same commit. The path requirement costs nothing but discipline and is reversible file by file. This is the only item on the list that would prevent new defects rather than clean old ones.
2. **Sweep the seven `welcome.blade.php` citations.** `65f8b92` deleted the view on 2026-09-29 and closed KI-20 in the same commit, and the commit touched no document that cited the file. `KNOWN-ISSUES.md` holds three of them. Small, mechanical, and it is the only case where the correct fix might be a retirement note rather than a re-point, because the citation records what was measured before the deletion.
3. **Regenerate or delete root `SKILL.md`.** Its frontmatter description claims a complete registry of installed skills. It was written on 2026-09-27 in commit `775b88a` and no commit has touched it since, while the repo gained `.ai/skills/**` content, plugin skills, and 2442 ignored doc files across a dozen agent directories. The `refresh-skill-registry` skill rebuilds exactly this list from disk. A hand-count that cannot be trusted and is cited by one file is worse than no registry.
4. **Add a regeneration trigger to `docs/SOURCE-OF-TRUTH.md`.** It consolidates seven documents, two of which are `CLAUDE.md` (gitignored) and `SKILL.md` (stale). It is cited by 10 files and its own content has no marker saying when it was derived. A derived document with no derivation stamp drifts silently, and this one is a hub. Not a merge. A header.
5. **Decide the five untracked incoming write-ups as one set.** 3894 lines of external material and 488 lines of this repo's rejection of it, none tracked, one cited by name from master. Options: track all five as a labelled archive, delete them, or keep them ignored and rewrite the citing line in `docs/UMAMUSUME_REFERENCE.md:386`. What it cannot stay is split, because the reject and the rejected have to travel together.
6. **Give the roster trio an order.** `…selector.md`, `…selector-plan.md`, `…report.md` are a request, a plan and a closing report for one workstream, and the only difference a reader sees is the suffix. One line at the top of each, naming the other two.
7. **Close the slice-record gap.** `docs/design-research/verification/` holds slices 2, 3, 5 through 15. Slice 1 and slice 4 have no record, and slice 16 has none while `KNOWN-ISSUES.md` KI-25's closure once cited a `slice-16` file that does not exist. Either the convention started at slice 2, which makes 1 and 4 historical and fine, or two records were never written. The register needs one sentence saying which.
8. **Refresh `.ai/**`, or mark it generated.** Twelve files, all written 2026-09-27 in `a72ee76`, none touched since. Three of them are demonstrably wrong against current code: `domain.md` calls the frontend vanilla JavaScript while `resources/js/` holds four TypeScript files compiled under C-9, and says `app/Actions/` is intended when three action classes are tracked there; `framework/core.md` cites `resources/js/app.js`; `.ai/rules/index.md` says no rules are recorded while two rule files sit beside it. This is Boost-generated material, so the fix is regeneration, not hand-editing, and that is a decision about whether the repo owns those files.
9. **Rewrite the tail of `.gitignore` as UTF-8.** Not a documentation merge, but it is a documentation-adjacent defect this inventory tripped over three times: `make lore` and `git grep` behaviour, the `docs/vibe_images/` exposure in section 9, and a `grep -c` that reported `.gitignore` as a binary file all trace to NUL bytes in lines 100 to 103. One line of that tail is a real rule that is root-anchored and cannot match, and one is dead. A tool that cannot read its own ignore file will keep mis-scoping every future inventory.

## 11. Do-not-touch list

Load-bearing by citation count, by role, or by an instruction that outranks this inventory.

| Item | Why it is off-limits to a consolidation pass |
|---|---|
| `docs/frontend-review/2026-09-28/` in full, 97 files | The brief forbids editing the audit's README or its captures. Independently: it is a dated evidence set, and 45 percent of the inventory. Editing one capture invalidates the audit that produced it |
| `CONSTRAINTS.md` at the root | Gate precedence R-6 puts it first. Agents are told to read it before writing code and never to weaken a threshold. `docs/GATE-REGISTRY.md:3` names it and this registry as the joint source of truth for global gates |
| `docs/GATE-REGISTRY.md` | Same precedence tier. It also records the gates that are knowingly not automated, and deleting an honest gap entry hides a gap |
| `KNOWN-ISSUES.md` | 1944 lines, 25 inbound citations, 66 line citations, and the active register. `AGENTS.md` and every slice record write into it. It is the single file where concurrent sessions collide most, and its renumbering history (KI-16 as a hole, KI-30 and KI-31 renumbered from KI-16 and KI-17) shows what editing it carelessly costs |
| `docs/adr/0001` through `0012` | Accepted decisions. `documentation-and-adrs` is explicit: do not delete old ADRs, and supersede by writing a new one. ADR-0013 is the exception the rule allows, and it already carries its own Withdrawn banner |
| `PRD.md` | Product truth, and the file the Architect role must cite for every new table, column or class. `AGENTS.md` routes all scope changes through it |
| The ten hubs with 10 or more inbound | `CONSTRAINTS.md`, `docs/UMAMUSUME_REFERENCE.md`, both `DESIGN.md` files, `PRD.md`, `docs/design-research/CONSTRAINTS.md`, `AGENTS.md`, `KNOWN-ISSUES.md`, `docs/design-research/DESIGN.md`, `docs/PRE-MORTEM.md`, `ARCHITECTURE-ESSENTIALS.md`, `docs/GATE-REGISTRY.md`, `docs/SOURCE-OF-TRUTH.md`. Renaming or merging any of them rewrites citations across a fifth of the corpus |
| `docs/UMAMUSUME_REFERENCE.md` | 41 inbound and the dated-snapshot policy recorded in project memory. Its eight sections are the mechanics corpus the UI traces to, and its preamble is the current map |
| `PLAN.md` and `docs/design-research/**` | On the forbidden-edit list from the standing Authorized Execution Turn that governed this session. `docs/design-research/SESSION-CONSOLIDATION-2026-09-30.md` is separately named do-not-touch by the brief |
| `.ai/guidelines/**` and `.ai/rules/**` | Boost-generated and auto-injected. Hand edits get overwritten by the next regeneration, and the repo's own rule says to record a rule only when the owner asks for one |
| Anything whose status is `unknown` here | 20 files. Absence of evidence is not evidence of staleness, and an inventory that lets a later pass act on `unknown` as though it meant `stale` has quietly lowered the bar |

## 12. Files that resisted the Description column

None resisted outright. Two resisted in a way worth naming, because the brief is right that a file hard to describe is a file hard to consolidate.

`docs/UMAMUSUME PRETTY DERBY — COMPREHENSIVE UX DELIVERABLES.md` describes fine as a document, and resists as an *intention*. Its banner says triaged and not adopted, its content is 2144 lines of someone else's design system, its name contains an em dash that breaks `git ls-files` output under default `core.quotepath`, and the repo's own reference guide cites it by a short name that does not resolve. Every description I could write either says what the file is or says why it is here, and those are different sentences. That is the signature of material that needs a decision rather than a merge.

`docs/design-research/FRONTEND-SPEC-DIVERGENCE.md` resists for the opposite reason. Its purpose is clear and its content is rotting underneath: 10 inbound citations from tracked files, 29 line citations of its own, 6 of them broken and 1 stale, and a list of corrections in PLAN.md that were never applied. It is an untracked file that other tracked files lean on. Describing it took one sentence; deciding what to do with it is the whole job.

## 13. Fence confirmation

Read-only against the repository. No tracked file was created, edited, renamed, moved or deleted. No commit was made. No branch was created or switched. No `git stash`, `git reset`, `git gc`, `git prune` or reflog operation ran. No migration ran and no database was opened for writing; the two Artisan reads I made were `tinker --execute` SELECT queries against `database/database.sqlite`, one of which returned `no such table` for `umamusume_profiles`.

Writes that did happen, all outside tracked space: this file and its JSON companion in `research-scratch/`, which `.gitignore` covers, and four analysis scripts in the OS temp directory. The `/careful` skill's own telemetry line, which appends to `~/.gstack/analytics/skill-usage.jsonl`, was skipped because the fence says read-only.

`git status --short` at the end of the pass reports the same nine untracked text files and the same `docs/vibe_images/` directory that it reported at the start, plus this file inside an ignored directory. The tree is as I found it, and it moved under me three times while I measured it.

One observation the fence note has to carry. Midway through the pass, `git status --short` listed `M  docs/design-research/SESSION-CONSOLIDATION-2026-09-30.md` as **staged**, and on the next call it was gone. I never wrote to that file, never staged anything, and `git diff --cached --stat` against it returned empty both times I looked. A concurrent session staged and unstaged it inside my measurement window. `git status` in this worktree is not a stable read, and any later pass that treats a clean status as proof of no concurrent writer will be wrong.

## 14. Spot-check, corrections to this inventory, and the prose audit

Written after the four report items the owner ruled on. Appended, not folded into sections 6 and 8, so the original claims stay readable next to what was found wrong with them.

### 14.1 Ten-row spot-check against the tree

Selection method: `random.seed("c1e14a3")` then `random.sample` over the 202 table rows. Reproducible from the snapshot SHA rather than chosen by hand, so the sample cannot be picked to flatter the scan.

Ten rows verified independently of the scripts that produced them: file size from `os.path.getsize`, line count from a fresh read, tracked state from `git ls-files` membership, and last commit from `git log -1 --format='%h %ad' -- <path>`, which is a different code path from the `git log --reverse --name-only` pass that built section 4.

| Row | bytes / lines | tracked | last commit | verdict |
|---|---|---|---|---|
| `docs/frontend-review/2026-09-28/catalog-detail-populated-dark.png.network.txt` | 343 / 1 | tracked | `c6c0567` 2026-09-28 | match |
| `docs/requests/2026-09-29-catalog-roster-report.md` | 18935 / 357 | tracked | `6300221` 2026-09-30 | match |
| `docs/design-research/verification/slice-7-2026-09-28.md` | 15006 / 246 | tracked | `0d2dbdc` 2026-09-28 | match |
| `docs/requests/reports/2026-09-30-c5-down-enforcement-gap.md` | 4151 / 67 | untracked | never | match |
| `docs/frontend-review/2026-09-28/landing-default-light.png.console.txt` | 189 / 1 | tracked | `c6c0567` 2026-09-28 | match |
| `AGENTS.md` | 26470 / 390 | tracked | `6f9c98a` 2026-09-27 | match |
| `docs/frontend-review/2026-09-28/run-detail-unpriceable-light.png.console.txt` | 204 / 1 | tracked | `c6c0567` 2026-09-28 | match |
| `docs/frontend-review/2026-09-28/catalog-index-page2-out-of-range-dark.png.console.txt` | 205 / 1 | tracked | `c6c0567` 2026-09-28 | match |
| `docs/design-research/verification/slice-13-2026-09-29.md` | 20718 / 344 | tracked | `debc4d0` 2026-09-29 | match |
| `docs/frontend-review/2026-09-28/review-queue-populated-dark.png.network.txt` | 311 / 1 | tracked | `c6c0567` 2026-09-28 | match |

**Ten for ten on the measurable columns.**

The derived column was checked separately, on ten hub rows, against a recount that reads every in-scope file for the basename. Seven matched exactly. Three did not, and both directions of error are recorded:

| Row | section 4 | recount | why |
|---|---|---|---|
| `CONSTRAINTS.md` | 45 | 52 | The recount counts any file whose text contains the basename. 29 files write `docs/design-research/CONSTRAINTS.md` in full and 22 of those also write the bare token. Section 4 counts citations that resolve to this path. The 7 difference is the twin collision from finding 2, leaking into the number |
| `docs/UMAMUSUME_REFERENCE.md` | 41 | 40 | The recount dropped one citer |
| `PRD.md` | 35 | 34 | The dropped citer is `docs/UMAMUSUME PRETTY DERBY — COMPREHENSIVE UX DELIVERABLES.md`. The recount's own path handling tripped on the em-dash filename, the same `core.quotepath` trap recorded in section 2 limit 3. Section 4 is right here |

So the counts stand, and the inbound column now carries its definition in section 4 rather than relying on the reader to infer it.

### 14.2 Two corrections to this inventory

**Section 8, roster trio row, was partly wrong.** It said "What is missing is a line at the top of each naming the other two." On opening the files: `…-and-trainee-selector.md` already carried `**Plan:** 2026-09-29-catalog-roster-and-trainee-selector-plan.md`. The real gap was narrower: nothing named the report, so a reader could not tell which of the three was last. Landed at `9003631` as six relative links across the three files, all resolving, in the shape of a triangle.

**Section 6.3, the `app.js` row, was under-specified.** It read as if the eight citations were stale claims about a live entrypoint. Two of the three files that carry them are doing something else. `KNOWN-ISSUES.md` mentions `app.js` inside the KI-1 record, where the whole point is that `app.js` did not exist, and KI-1 is resolved: `layout.blade.php:24` now requests `resources/js/app.ts`. The genuinely stale guidance is in two other files, `.ai/guidelines/framework/core.md` and `.ai/skills/tailwindcss-development/SKILL.md`, both of which still teach the `@vite(['resources/css/app.css', 'resources/js/app.js'])` boilerplate as current. `AGENTS.md` repeats it through the embedded Boost text. Nothing tests documentation strings, so the rename that closed KI-1 is invisible to any gate. That distinction is recorded in the KI-1 forward note at `9a009f5` rather than by rewriting this section.

One number in section 6.3 also needs reading as a token count, not a file count. 2555 tokens resolved and 683 did not, out of 3238. A single file citing the same path five times contributes five. The inbound column in section 4 counts files. The two columns are not comparable with each other and were never meant to be.

### 14.3 Prose audit of this document, against `humanizer`

Counts and locations, no rewrites, per the owner's ruling. Patterns that scored zero are listed so the absence is checkable rather than assumed.

| Pattern | Hits | Where |
|---|---|---|
| §1 not-X-but-Y, clipped negative tail | 0 by formula | 21 sentences contain `not`; 6 of them are contrasts that carry information and are kept (`which is the expected result, not a broken gate`; `My regex's fault, not the repo's`; `a working artefact, not a repo document`) |
| §2 one-line closer, repeated closer | **1, at scale** | The clause "The two copies are not identical, so this pair is a before/after, not a duplicate." appears 4 times verbatim, in the four `export-*-run1.txt` rows of section 4.2. The content is correct and the brief asked for it; the repetition is the tell |
| §3 aphorism formula | 2 | Section 11 `Anything whose status is unknown here`: "Absence of evidence is not evidence of staleness". Section 8 `CONSTRAINTS.md` row: "The real defect is the shared basename, not the shared subject" |
| §4 staged run-up | 1 | Section 6 opener, "Method matters here, so it goes first." |
| §5 arguing with no one | 1, mild | Section 7 table, Unknown row: "This is not a claim that they are wrong". It pre-empts a real misreading of an `unknown` verdict, so it carries information |
| §6 forced triads | 0 | 2 `A, B, and C` constructions, both inside table cells listing real items |
| §8 dash as connector | **0** | 5 em dashes in the file, all inside the verbatim filename `docs/UMAMUSUME PRETTY DERBY — COMPREHENSIVE UX DELIVERABLES.md`, which section 2 limit 3 explains must stay exact |
| §9 stacked qualifiers | 0 | |
| §12 overused AI vocabulary | 1 real, 12 exempt | 9 hits are the technical noun `gate`, which the skill exempts. 2 hits are `actually` inside a literal ADR title or a table column header meaning "the line it really sits at". One is real: section 11, "has quietly lowered the bar" |
| §13 inflated significance | 0 | |
| §15 shallow `-ing` riders | 0 | |
| §16 sales language | 0 | |
| §18 avoids is/are/has | 0 | |
| §19 bold as decoration, labeled lists | 0 | Bold appears in table cells and lead-ins, none as `- **Label:**` bullets |
| §20 decorative headings, rules, repeated title | 0 | Headings are sentence-ish and numbered; no `---` separators between sections; no emoji |
| §22 chatbot residue | 0 | |
| §23 knowledge-limit disclaimers and guesses | 0 | Section 11's `unknown` verdicts name what is missing rather than guessing |
| §24 heading restated in first sentence | 0 | |
| §25 writing about the previous version | 1, deliberate | Section 2 limit 6 and section 14 exist to correct earlier claims forward instead of overwriting them. That is the repo's stated convention, not a tell |

Sentence cadence across the prose lines runs 5 to 97 words with no even mid-length run, which `humanizer` lists as a human signal rather than something to fix.

**Net: six rows to look at, all of them small.** The repeated export clause is the only one a rewriter would touch first, and only because it appears four times. "Quietly lowered the bar" and "Method matters here, so it goes first" are the other two worth a decision. The `unknown` pre-empt and the correction sections are load-bearing and should stay.
