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
| `RACE-AND-SLICE-RESEARCH.md` | Race calendar gaps, handoff race read path, slice 4-7 research, session consolidation | 7 sources from design-research/ (note: global-race-sources.md was listed in spec but does not exist on disk) |
| `SUPPORT-CARDS.md` | Support card mechanics record and sourced development plan | 2 sources from design-research/ |
| `PLANS-AND-BRIEFS.md` | Task 16 run-view frame brief, replan mobile-first proposal, D-30 amendment draft | 3 sources from design-research/ |
| `AUDIT-AND-VERIFICATION.md` | Audit verification: the 18 findings against post-fix state | 1 source from design-research/ |

### Publisher references consolidation (Round 3)

| Master | Content | Sources |
|--------|---------|---------|
| `SCENARIO-PUBLISHER-REFERENCES.md` | Owner-supplied Trackblazer and Unity Cup publisher guides: uma.guide strategy take, GameTora Trackblazer mechanics, GameTora Unity Cup (post-2026-07-01 rework). Repo-authored guides 01/02/03/07/08 are not part of this set. | 3 sources from `docs/scenarios/04`–`06`, embedded verbatim with a Disagreements section (KI-15) |

### Round 4 (2026-10-02)

| Master | Content | Sources |
|--------|---------|---------|
| `UX-DELIVERABLES.md` | The three triaged UX write-ups, embedded verbatim with headings demoted one level per part and each file's own NOT MERGED banner preserved. Promoted with the owner's written authorization, which file discipline requires for a new master | 3 sources from `docs/`, deleted by the consolidation in `22e5135`; this master is their only working-tree copy and they stay recoverable from `4ab5ada` |

Round 4 also folded `WEB-FINDINGS.md` (390 lines) into `DESIGN-CORPUS.md` as its tenth source rather than leaving it tracked inside `docs/design-research/_scratch/`, which G-60 lists as ignored scratch.

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
| The UI/UX plan (milestones, workstreams, gates) | `docs/PLAN-UI-UX-2026-10-02.md` (live, executable); frozen 2026-10-02 copy in `PROCESS-PLANS.md` |
| Documentation-register sync plan (analysis A-1..A-12, owner gates O-1..O-3) | `docs/PLAN-DOC-SYNC-2026-10-02.md` |
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
| Audit verification (18 findings) | `AUDIT-AND-VERIFICATION.md` |
| UX deliverables, the three triaged write-ups | `UX-DELIVERABLES.md` |
| Legacy deprecated PDFs and what was ruled about them | `docs/deprecated/README.md`, reviewed in `docs/deprecated/REVIEW-2026-09-30.md` |

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