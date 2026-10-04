# Documentation Index

## Master Files

This index maps content types to their master consolidation files under `docs/research-scratch/`. Each master file embeds source content verbatim with no summarization or deduplication.

### Previous consolidation (Round 1)

| Master | Content | Sources |
|--------|---------|---------|
| `GOVERNANCE.md` | Project governance, gate registry, pre-mortem analysis, and the 2026-09-28 frontend audit (findings, decisions, resolutions) | 6 sources: SOURCE-OF-TRUTH.md, GATE-REGISTRY.md, PRE-MORTEM.md, frontend-audit README.md, DECISIONS-NEEDED.md, RESOLUTIONS.md |
| `CATALOG-ROSTER-WORKSTREAM.md` | Complete catalog roster and trainee selector workstream: request, implementation plan, closing report, cross-check, card overview table, cardless-band verification | 6 sources from requests/, data/, verification/ |
| `CHARACTERS-SOURCE.md` | Characters source findings and probe for the profile-block slice, plus the port-and-cleanup report that withdrew ADR-0013 | 3 sources: findings, probe, port report |
| `SLICE-RECORDS.md` | All 15 slice verification records covering stat ceilings, schema decisions, skill imports, frontend audits, and domain modeling | 15 slice-*.md files from verification/ |
| `DESIGN-AND-MECHANICS-REQUESTS.md` | Game mechanics requests (mood-pill colours), design pass for trainee detail page and skill selector, and user flows (create run, legacy select) | 3 sources from requests/, verification/, flows/ |
| `PROCESS-PLANS.md` | Scratch-tree reorganization implementation plan and C-5 down() enforcement gap finding | 2 sources from requests/, reports/ |

### Current consolidation (Round 2)

| Master | Content | Sources |
|--------|---------|---------|
| `DESIGN-CORPUS.md` | Design system and research corpus: DESIGN.md, design-research CONSTRAINTS, frontend audit and spec divergence, mechanics translation triage, external design review, scenario differences, raw findings, screenshot manifest | 9 sources from design-research/ |
| `SKILLS-MECHANICS.md` | Skills mechanics and skills surface: skill facts, mechanics audit and verification, gaps, section reviews, phase B2, reconciliation | 7 sources from design-research/ |
| `RACE-AND-SLICE-RESEARCH.md` | Race calendar gaps and open questions, handoff race read path, slice 4-7 research, session consolidation, and the Global/EN source verification behind the calendar (grade labels, race availability, turn shape, the 2026-07-01 rework) | 8 sources: 7 from design-research/ plus `global-race-sources.md` from root `research-scratch/`, added in Round 7. Its generated data companion is `calendar-tables.md`, which stays a standalone file |
| `SUPPORT-CARDS.md` | Support card mechanics record and sourced development plan | 2 sources from design-research/ |
| `PLANS-AND-BRIEFS.md` | Task 16 run-view frame brief, replan mobile-first proposal, D-30 amendment draft | 3 sources from design-research/ |
| `AUDIT-AND-VERIFICATION.md` | Audits: the 18 findings against post-fix state, plus the 2026-09-30 documentation census and the 2026-09-29 fullstack phase audit | 2 sources: `audit-verification-2026-10-01.md` from design-research/, and `documentation-inventory-and-unfinished-phases.md` from root `research-scratch/` (Round 7) |

### Publisher references consolidation (Round 3)

| Master | Content | Sources |
|--------|---------|---------|
| `SCENARIO-PUBLISHER-REFERENCES.md` | Owner-supplied publisher guides and mechanics extractions: uma.guide strategy take, GameTora Trackblazer mechanics, GameTora Unity Cup (post-2026-07-01 rework), Game8 Global scenario rules and phase structure, and the JP training-mechanics extraction from GameWith, Game8 JP and Kamigame. Repo-authored guides 01/02/03/07/08 are not part of this set. | 5 sources: `docs/scenarios/04`-`06` embedded verbatim with a Disagreements section (KI-15), plus `scrape-game8-scenarios.md` and `scrape-training-heuristics.md` from root `research-scratch/` (Round 7) |

### Round 4 (2026-10-02)

| Master | Content | Sources |
|--------|---------|---------|
| `UX-DELIVERABLES.md` | The three triaged UX write-ups, embedded verbatim with headings demoted one level per part and each file's own NOT MERGED banner preserved. Promoted with the owner's written authorization, which file discipline requires for a new master | 3 sources from `docs/`, deleted by the consolidation in `22e5135`; this master is their only working-tree copy and they stay recoverable from `4ab5ada` |

Round 4 also folded `WEB-FINDINGS.md` (390 lines) into `DESIGN-CORPUS.md` as its tenth source rather than leaving it tracked inside `docs/design-research/_scratch/`, which G-60 lists as ignored scratch.

### Round 5 (2026-10-03) — O-3 promotion from root research-scratch/

| Master | Content | Sources |
|--------|---------|---------|
| `scrape-game8-scenarios.md` | Game8 Global scenario extraction for URA Finale, Unity Cup, Trackblazer (477 lines). Playwright-rendered pages, per-scenario rules, stat caps, phase structures. Cited by `UMAMUSUME_REFERENCE.md` | 1 source from root `research-scratch/`, promoted per O-3 |
| `scrape-training-heuristics.md` | JP training mechanics raw extraction from GameWith, Game8 JP, Kamigame (764 lines). Failure mechanics, energy curves, mood multipliers, per-scenario tables. Cited by `UMAMUSUME_REFERENCE.md` | 1 source from root `research-scratch/`, promoted per O-3 |
| `global-race-sources.md` | Global/EN race calendar verification: grade labels, 17 new dirt races, July 2026 rework, all-races calendar with tiers, Goal vs Scheduled states (225 lines). Cited by `scenarios/09-global-race-calendar.md` | 1 source from root `research-scratch/`, promoted per O-3 |
| `calendar-tables.md` | Generated race calendar tables for all three scenarios (Junior/Classic/Senior years, 400+ rows). Output of `gen_calendar.py`, synced by `resync_doc.py`. Cited by `scenarios/09-global-race-calendar.md` | 1 source from root `research-scratch/`, promoted per O-3 |
| `DOCUMENTATION-INVENTORY-2026-09-30.md` | Full repo documentation census: 30 skills invoked, method limits, 2442 ignored files, 24 tracked strays, 300+ root scratch files (420 lines). Cited by `PROCESS-PLANS.md` | 1 source from root `research-scratch/`, promoted per O-3 |

> **Superseded in part 2026-10-03 (Round 7).** Round 5 promoted five durable files out of the gitignored
> root `research-scratch/` and gave each its own master. Three of those five were single-topic files that
> duplicated a subject an existing master already owned, and were dissolved into it the same day:
> `scrape-game8-scenarios.md` and `scrape-training-heuristics.md` into `SCENARIO-PUBLISHER-REFERENCES.md`,
> `global-race-sources.md` into `RACE-AND-SLICE-RESEARCH.md`. `calendar-tables.md` stays a standalone file
> because it is machine-generated by a pipeline whose scripts cannot be committed with a move; it is now
> bound to the race master as its data companion. See Round 7.

### Round 6 (2026-10-03) — the two root audits, consolidated

| Master | Content | Sources |
|--------|---------|---------|
| `documentation-inventory-and-unfinished-phases.md` | The 2026-09-30 documentation census (202 files in scope, skills invoked, method limits, grouped views, cross-reference map, line-citation rot, duplicate candidates, untracked material, ranked consolidation candidates, do-not-touch list, spot-check) together with the 2026-09-29 fullstack phase audit (unstarted phases, accepted rulings with no code, open register entries, gate and tooling gaps, verification-only gaps, documentation drift, suggested pickup order, two addenda). Both parts are embedded verbatim with every heading demoted one level | 2 sources from root `research-scratch/`, promoted per O-3 |

> **Corrected 2026-10-03.** Round 5's row above describes `DOCUMENTATION-INVENTORY-2026-09-30.md` as
> 420 lines with 30 skills invoked. The file on disk is 654 lines (653 plus one census marker line) and
> its section 1 lists 10 skills. Its content is now Part 1 of the Round 6 master, which leaves the
> standalone tracked copy a duplicate master. It is **not** deleted here: the owner's authorization
> covered creating the consolidated master, not retiring a tracked one, so the delete is the owner's call.

> **Dissolved 2026-10-03 (Round 7).** The Round 6 master itself did not survive the week. Its two parts are
> audits, and `AUDIT-AND-VERIFICATION.md` is the audits master, so the file was embedded there and removed.
> That makes its Part 1 content live in two places at once, which sharpens the duplicate flagged above
> rather than settling it: `DOCUMENTATION-INVENTORY-2026-09-30.md` is now a byte-duplicate of a section of
> `AUDIT-AND-VERIFICATION.md`. Retiring it, and repointing the two citations at `PROCESS-PLANS.md:29` and
> `:51`, is the one action this round left to the owner.

### Round 7 (2026-10-03) — five single-topic masters folded into the masters that owned their subject

| Absorbed file | Lines | New home | Why there |
|---|---|---|---|
| `global-race-sources.md` | 225 | `RACE-AND-SLICE-RESEARCH.md`, section `global-race-sources.md` | The Global/EN cross-check behind the race calendar, and that master already held `RACE-CALENDAR-GAPS.md` and the race read-path handoff. Its own provenance note claiming the file "does NOT exist anywhere in the repository" was stale from the moment Round 5 promoted it; embedding it makes the note true. |
| `scrape-game8-scenarios.md` | 477 | `SCENARIO-PUBLISHER-REFERENCES.md`, section "Scenario rules and phase structure (Game8, Global)" | A third publisher voice on the same scenarios, with the same extraction shape the other three sources have, including its own Disagreements and read-failure sections. |
| `scrape-training-heuristics.md` | 764 | `SCENARIO-PUBLISHER-REFERENCES.md`, section "Training mechanics (GameWith, Game8 JP, Kamigame)" | Publisher-sourced mechanics with a 30-entry source ledger. Chosen by the owner over `SKILLS-MECHANICS.md`, which is skills-scoped. The file is `[JP]`-sourced while the other four sections are `[Global]`; that split is recorded in that master's Disagreements rather than blended. |
| `documentation-inventory-and-unfinished-phases.md` | 1,062 | `AUDIT-AND-VERIFICATION.md`, section `documentation-inventory-and-unfinished-phases.md` | Both parts are audits (a documentation census with a verification pass, and a fullstack phase audit with verification-only gaps), and that file is the audits master. |
| `calendar-tables.md` | 400+ rows | **Not dissolved.** Bound to `RACE-AND-SLICE-RESEARCH.md` as its generated data companion | 400+ generated rows with no markdown headings, rendered by `research-scratch/scripts/gen_calendar.py` and re-synced by `resync_doc.py` matching its `@@` markers. Both scripts are gitignored, so a move could not be committed with its fix. Nothing in it was redundant. |

Every absorbed file was embedded verbatim with headings demoted one level, per the convention above.
Three inbound citations were repointed: `docs/UMAMUSUME_REFERENCE.md:1007` and `:1055`, and
`docs/scenarios/09-global-race-calendar.md:727`. The pipeline paragraph at
`docs/scenarios/09-global-race-calendar.md:729` also had its two script paths corrected (they live under
`research-scratch/scripts/`, not `research-scratch/`) and its "replaces each table in this file" clause
corrected to name `calendar-tables.md`, which is the file it actually edits.

**Corrected 2026-10-04 (owner instruction, HEAD `228011e`).** The last row above and that closing clause
are both superseded. calendar-tables.md is now dissolved: its 551 lines are embedded in
`RACE-AND-SLICE-RESEARCH.md` as the section of that name and the tracked file is deleted. Reading the two
scripts on disk also shows the Round 7 reason did not hold: `gen_calendar.py` and `resync_doc.py` read the
gitignored root copy research-scratch/calendar-tables.md and `resync_doc.py` writes
`docs/scenarios/09-global-race-calendar.md`, not the tracked table file, so the pipeline never depended on
that file being standalone. The root input no longer exists, so `resync_doc.py` needs its source path
repointed before it runs again; `gen_calendar.py` regenerates the rows.

### Round 9 (2026-10-03) — root `research-scratch/` emptied into the masters

The user authorized consolidating every `.md` in the gitignored root `research-scratch/` into this
directory and deleting the originals after verification. Twenty-five root files (1.91 MB) plus the two
standalone training-run records already living here were sorted into three populations, each handled by
the evidence, not by assumption:

| Population | Files | Action | Home |
|---|---|---|---|
| Already embedded in a master by Rounds 5-8 | `DOCUMENTATION-INVENTORY-2026-09-30.md`, `unfinished-phases-audit-2026-09-29.md`, `scrape-game8-scenarios.md`, `scrape-training-heuristics.md`, `global-race-sources.md`, `calendar-tables.md` | Verified contained line-by-line, then deleted from root. Not re-embedded | their existing masters |
| The 2026-09-27 reference-guide build chain (drafts, three `assembled-body` revisions, front matter, spine, sec-2 cross-checks, self-audit, adversarial review) | 17 files | 16 embedded verbatim in build order in the new master `REFERENCE-GUIDE-BUILD-CHAIN.md`, which carries the census marker; `gen-sec2.md` deleted as verified redundancy, not embedded | the guide is off-limits to appending, so its provenance is this master |
| Screenshot-pipeline records | `phase-c-synthesis.md`, `scratch-priors.md` | Appended to `DESIGN-CORPUS.md` (sources 11-12); the corpus already holds the SCREENSHOT-MANIFEST they rule against | `DESIGN-CORPUS.md` |
| Training-run review pair (lived here, not in root) | `training-run-ux-review-2026-10-03.md`, `training-run-live-2026-10-03.md` | Folded into `AUDIT-AND-VERIFICATION.md`; they cross-reference each other, so one master keeps those pointers under the census exemption | `AUDIT-AND-VERIFICATION.md` |

`gen-sec2.md` (157 KB) was the one true redundancy: all 362 of its distinct non-blank lines are
contained in the assembled bodies (measured 100.0%), so it was deleted without embedding, the check
recorded in the build
chain master's header. Every other file was verified line-by-line fully contained in the destination
(allowing the documented heading demotion) before its root copy was deleted. Root `.md` count after: 0.
The non-`.md` scratch (`.py`, `.json`, `.csv`, `.txt`) and the 32 pipeline scripts under
`research-scratch/scripts/` were left untouched, being outside this instruction. The `lore` gate reads
187 hits / 76 exempt after this round (report-only; exit 0 by design, the Guardian rules, not the grep).
The three Round 9 embed ranges add no banned-vocabulary line: each was checked per needle (n1/n2/n3)
across the training-run pair in the audits master and the two new `DESIGN-CORPUS.md` sections, and all
return zero. `REFERENCE-GUIDE-BUILD-CHAIN.md` is still untracked, so it is invisible to `git grep` until
it is committed, when its census marker absorbs its 36 build-input enumerations the same way the other
two flagged masters already do.

### Round 10 (2026-10-03) — the Phase C edit record folded into the corpus it edited

| Absorbed file | Lines | New home | Why there |
|---|---|---|---|
| `corpus-edit-summary.md` | 140 | `DESIGN-CORPUS.md`, section `corpus-edit-summary.md` (source 13) | Its own first line calls it "Companion to the diff of `docs/research-scratch/DESIGN-CORPUS.md`, not a substitute for it." Its siblings from Round 9 already sit there (the `phase-c-synthesis.md` handoff as source 11 and `scratch-priors.md` as source 12), so the applied-diff record now sits with them. Embedded verbatim, headings demoted one level; line-level containment verified (0 missing) before the original was deleted. |

The original was tracked and clean at HEAD, so its deletion is recoverable from history. Two dated
records still name the old path in prose and are left verbatim per the correction-forward rule: the
embedded `phase-c-synthesis.md` body inside this master, and `screenshot-notes/PHASE-B-REPORT.md`
(items 15 and the Phase C output set). Those are provenance mentions the census tolerates, and the
citation ratchet is unchanged by them.

## Routing Table

| If you need... | Read |
|----------------|------|
| Binding rules, governance, product scope | `GOVERNANCE.md` |
| Gate definitions, C-1 through C-9 | `GOVERNANCE.md` (GATE-REGISTRY section) |
| Lore integrity, terminology map | `GOVERNANCE.md` (SOURCE-OF-TRUTH sections 2-3) |
| Scenario registry, stat caps | `GOVERNANCE.md` (section 4) |
| Quality bar, verification sequence | `GOVERNANCE.md` (section 9) |
| ADR index | `GOVERNANCE.md` (section 10) |
| Pre-mortem / legacy repo analysis | `GOVERNANCE.md` (PRE-MORTEM section) |
| Frontend audit findings (F-1 through F-19) | `GOVERNANCE.md` (frontend-review sections) |
| Catalog roster request, plan, report | `CATALOG-ROSTER-WORKSTREAM.md` |
| Card crosscheck table | `CATALOG-ROSTER-WORKSTREAM.md` |
| Cardless band implementation | `CATALOG-ROSTER-WORKSTREAM.md` |
| Characters source probe, profile fields | `CHARACTERS-SOURCE.md` |
| va_en defect, race filter, port history | `CHARACTERS-SOURCE.md` (port-and-cleanup section) |
| Slice verification records | `SLICE-RECORDS.md` |
| Mood-pill colours request | `DESIGN-AND-MECHANICS-REQUESTS.md` |
| Create-run flow, Legacy Select | `DESIGN-AND-MECHANICS-REQUESTS.md` |
| Scratch-tree reorganization | `PROCESS-PLANS.md` |
| C-5 down() enforcement gap | `PROCESS-PLANS.md` (C-5 section) |
| The UI/UX plan (milestones, workstreams, gates) | `PROCESS-PLANS.md` (section PLAN-UI-UX-2026-10-02.md; authoritative copy, open steps continue there, working file deleted 2026-10-03) |
| Documentation-register sync plan (analysis A-1..A-12, owner gates O-1..O-3) | `PROCESS-PLANS.md` (section PLAN-DOC-SYNC-2026-10-02.md; authoritative copy, open steps continue there, working file deleted 2026-10-03) |
| Design system tokens, theme | `DESIGN-CORPUS.md` (DESIGN section) |
| Design-research CONSTRAINTS (D-rules) | `DESIGN-CORPUS.md` (CONSTRAINTS section) |
| Frontend spec divergence | `DESIGN-CORPUS.md` (FRONTEND-SPEC-DIVERGENCE section) |
| Mechanics translation triage | `DESIGN-CORPUS.md` (MECHANICS-TRANSLATION-TRIAGE section) |
| Skill facts and mechanics | `SKILLS-MECHANICS.md` |
| Skills gaps | `SKILLS-MECHANICS.md` (SKILLS-GAPS section) |
| Skills section phase B2 | `SKILLS-MECHANICS.md` (phase-b2 section) |
| Race calendar, slice research, session consolidation | `RACE-AND-SLICE-RESEARCH.md` |
| Support card mechanics | `SUPPORT-CARDS.md` |
| Run view frame brief | `PLANS-AND-BRIEFS.md` (TASK-16 section) |
| Mobile-first replan | `PLANS-AND-BRIEFS.md` (replan section) |
| D-30 amendment draft | `PLANS-AND-BRIEFS.md` (d-30-amendment section) |
| Audit verification (18 findings) | `AUDIT-AND-VERIFICATION.md` (audit-verification section) |
| UX deliverables, the three triaged write-ups | `UX-DELIVERABLES.md` |
| Legacy deprecated PDFs and what was ruled about them | `docs/deprecated/README.md`, reviewed in `docs/deprecated/REVIEW-2026-09-30.md` |
| Game8 Global scenario extraction | `SCENARIO-PUBLISHER-REFERENCES.md` (Game8 section) |
| JP training mechanics raw extraction | `SCENARIO-PUBLISHER-REFERENCES.md` (training mechanics section) |
| Global/EN race calendar verification | `RACE-AND-SLICE-RESEARCH.md` (global-race-sources section) |
| Generated race calendar tables | `RACE-AND-SLICE-RESEARCH.md` (section calendar-tables.md; embedded 2026-10-04, the standalone file is deleted) |
| Training-run UI/UX audit, browser-driven pass with the C-, R- and O- findings (2026-10-04) | `AUDIT-AND-VERIFICATION.md` (section UIX-AUDIT-TRAINING-RUNS.md) |
| Audit decisions owed from that pass, proposals and measurements only | `AUDIT-AND-VERIFICATION.md` (section audit-decisions-2026-10.md) |
| Unity Cup capture schema proposal (team rank, spirit bursts, team races, resource strip) | `PLANS-AND-BRIEFS.md` (section unity-cup-capture.md) |
| Per-card deck state proposal for runs (O-8 2b(d)) | `PLANS-AND-BRIEFS.md` (section o8-per-card-state-proposal.md) |
| BuildTarget + Trainer Advisor spec (Trainer Desk 2.0 subsystem 1; authority is branch-only) | `PLANS-AND-BRIEFS.md` (section trainer-advisor.md, read from trainer-desk-2.0 at `19c2e7d`) |
| Full repo documentation census | `AUDIT-AND-VERIFICATION.md` (documentation-inventory section, Part 1) |
| Unfinished phases, unbacked rulings, register and gate state, pickup order | `AUDIT-AND-VERIFICATION.md` (documentation-inventory section, Part 2) |
| Training run UI/UX review, static record and live browser pass (2026-10-03) | `AUDIT-AND-VERIFICATION.md` (training-run sections) |
| Reference guide build chain: drafts, the three assembled-body revisions, the 2026-09-27 self-audit and adversarial review | `REFERENCE-GUIDE-BUILD-CHAIN.md` |
| Phase C rulings, screenshot-pipeline priors, and the edit-by-edit record | `DESIGN-CORPUS.md` (phase-c-synthesis, scratch-priors, corpus-edit-summary sections) |

### Deliberate exceptions, outside the masters

Two files stay outside `docs/research-scratch/` on purpose. They are listed here so a future pass
does not "consolidate" them and destroy the reason they exist.

| File | Why it stays |
|---|---|
| `docs/SKILL_AUTOMATION.md` (90 lines) | Documents the skill layer that lives in `.ai/skills/`, so it belongs with tooling, not with research. Cited by `KNOWN-ISSUES.md` |
| `docs/design-research/prototypes/superseded/README.md` (16 lines) | Its whole job is to stand in a directory of four retired prototypes and say they are not the current design |
| `docs/deprecated/` (3 PDFs, review, README) | Binary originals that no gate can read and a dated review of them. Restored 2026-10-02 from the agent checkpoint `9a38915` after the consolidation deleted the folder, and now tracked so it cannot be lost the same way twice |

## File discipline

New findings, slice records, errata, and requests are appended to the relevant master file as a new dated section. Creating a new standalone markdown file in `docs/research-scratch/` is a violation unless the owner explicitly authorizes it in writing.

Future agents must update these master files instead of creating new top-level markdown files. The master files are the canonical entry points for all documentation content. Source files consolidated into these masters are deleted after verification and should not be cited as live sources. When citing a fact from the consolidated set, cite the master file and part anchor as the reference.

Content that does not fit any existing master should be proposed to the owner with a recommendation for which master to extend, rather than creating a new file. The Provenance section of each master should be updated whenever new content is appended to record the addition.

### Root directory (owner instruction 2026-10-02)

No new documentation file is created in the repository root, and no new documentation content is added there either: it goes into the relevant master above. The root files the repo's documentation precedence and role ownership already name form a standing set and stay put: `AGENTS.md`, `CLAUDE.md`, `PRD.md`, `ARCHITECTURE.md`, `ARCHITECTURE-ESSENTIALS.md`, `CONSTRAINTS.md`, `DESIGN.md`, `KNOWN-ISSUES.md`, `PLAN.md`, `README.md`. That list is exhaustive; a root markdown file not in it is unconsolidated by definition.

> **Corrected forward 2026-10-03 (Round 8, owner-authorized).** The standing set still holds: no file
> was deleted from the root, and the ten names above all still exist and still resolve. What changed is
> that three of them are now pointer stubs whose binding content lives in a master: `CONSTRAINTS.md`
> → `GOVERNANCE.md` section "CONSTRAINTS.md (root quality bar, C-1 to C-9)"; `PLAN.md` →
> `PROCESS-PLANS.md` section "PLAN.md (root frontend slice plan and owner rulings)"; and
> `KNOWN-ISSUES.md` → `AUDIT-AND-VERIFICATION.md` section "KNOWN-ISSUES.md (defect register)", with the
> root register **still the live append target** for new KI entries. "Stay put" keeps its meaning: the
> names do not move, citations resolve, and the gate precedence chain is unchanged. The rest of the set
> (`AGENTS.md`, `CLAUDE.md`, `README.md`, `PRD.md`, both `ARCHITECTURE` files, root `DESIGN.md`) holds
> its full content at the root as before.

The standing set is documentation. Two root files are generated tooling artifacts and are exempt from this clause by type, not by name: `PRODUCT.md`, owned by the `impeccable` plugin, which carries `<!-- impeccable:product-schema 1 -->` and rewrites the file on its next pass, and `SKILL.md`, the human-readable half of the skill registry pair recorded in `GOVERNANCE.md` ("`.agents/skills/skills.json` (machine), `SKILL.md` (human)"). Neither is documentation to consolidate, and hand-editing either one loses to the generator. `PRODUCT.md` is refreshed by the plugin; `SKILL.md` is rebuilt by `refresh-skill-registry`, which scans every skill scope from disk, because the file's own description claims a complete roster while its content omits `~/.qoder/skills` and every plugin skill.

`source.md` (171 lines, a pasted Laravel bootstrap prompt) was deleted on 2026-10-02. It was neither documentation nor a tooling artifact, and the only substantive sentence about it, `docs/UMAMUSUME_REFERENCE.md:2135`, is a ruling that it never was a source registry. That ruling stays verbatim with the deletion recorded beside it. Other apparent citations were substring noise inside `ADR-0013-character-profile-source.md`.

### Disposition of the strays (executed 2026-10-02)

24 tracked markdown files measured outside `docs/research-scratch/`, excluding `docs/adr/` (19, governance by design) and `.ai/**` (14, generated). Ten are the root governance set, six are the repo-authored scenario guides `01`, `02`, `03`, `07`, `08`, `09` that the publisher-references round left alone, one is `docs/UMAMUSUME_REFERENCE.md`, named off-limits by the roster workstream's own fence (`CATALOG-ROSTER-WORKSTREAM.md:161`) and cited by 53 tracked files, and three were root files handled above. That accounts for 20.

Four files were unclassified. They are resolved, not merely recorded, and the resolution for three of the four was to leave the file where it is and register it here, because a file whose job is locational stops working when it moves.

| File | Resolution |
|---|---|
| `docs/_UMAMUSUME UX DELIVERABLES - MERGED.md` | Promoted to master `16`, now `docs/research-scratch/UX-DELIVERABLES.md`. It held the only working-tree copies of three write-ups the consolidation deleted, so folding it into an existing master would have produced a 25,000-line file and leaving it `_`-prefixed in `docs/` put it outside the index. Byte-identical move; sha256 unchanged; 25 lines of provenance and erratum added, zero body lines deleted. |
| `docs/design-research/_scratch/WEB-FINDINGS.md` | Folded into `DESIGN-CORPUS.md` as `## WEB-FINDINGS.md`, then deleted. It was tracked inside a directory G-60 lists as ignored historical scratch. 335 non-blank lines verified present, 22 headings demoted with no depth errors, and the corpus's own pointer to it repointed at the embedded section. |
| `docs/SKILL_AUTOMATION.md` | **Deliberate exception, left in place.** 90 lines, cited by `KNOWN-ISSUES.md`. It documents automation for the skill layer that lives in `.ai/skills/`, so it belongs with the code-side tooling tree, not in a research master. Registered in the Routing Table below. |
| `docs/design-research/prototypes/superseded/README.md` | **Deliberate exception, left in place.** 16 lines whose entire function is to stand in a directory of four retired prototypes and say they are not the current design. Moving it destroys the warning it exists to give. Registered in the Routing Table below. |

No repo-level protected-file registry exists to check future strays against: the pre-consolidation inventory that carried one is, per `CONSOLIDATION-LOG.md`, absent from disk and from all git history. The reproducible census in `CONSOLIDATION-LOG.md` §Census is the substitute.