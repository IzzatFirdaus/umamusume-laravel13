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

## File discipline

New findings, slice records, errata, and requests are appended to the relevant master file as a new dated section. Creating a new standalone markdown file in `docs/research-scratch/` is a violation unless the owner explicitly authorizes it in writing.

Future agents must update these master files instead of creating new top-level markdown files. The master files are the canonical entry points for all documentation content. Source files consolidated into these masters are deleted after verification and should not be cited as live sources. When citing a fact from the consolidated set, cite the master file and part anchor as the reference.

Content that does not fit any existing master should be proposed to the owner with a recommendation for which master to extend, rather than creating a new file. The Provenance section of each master should be updated whenever new content is appended to record the addition.