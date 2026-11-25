# docs/ — folder map and default placement

Every folder under `docs/` owns exactly one class of document. If you are placing or looking for an `.md`
and no path was specified, use the table below; it is the default, not a suggestion.

| Folder | Owns | Constraints |
|---|---|---|
| `docs/adr/` | Architecture Decision Records (0001-0037). `docs/adr/README.md` is the derived index; Accepted binds only, Proposed does not bind. | No ad-hoc decision docs outside this folder. |
| `docs/audits/` | Repository audits only: browser observations, RS-UC slice audits, UX walks, reconciliation rounds. Dated directories, each with `00-coverage.md` or `README.md`; register in `docs/audits/INDEX.md`. | Umamusume game knowledge never goes here. |
| `docs/archive/` | Superseded artifacts: mockups, screenshots, vibe images, HLF_reference captures, old verification. | Read-only archive; not a source, never copy display text from here. |
| `docs/game-screenshots/` | Raw Global client screenshots. | Reference only; no process docs. |
| `docs/imports/` | Legacy tracker run records (e.g. `Umamusume_Progress_Tracker_Runs.md`). Folded into the `TRAINING-RUN-IMPORTS.md` master; files remain as the record. | |
| `docs/plan/` | Dated execution plans (session plans and legacy plans, `implementation-plan-*.md`, `remediation-plan-*.md`). | Consumes roadmap slices via `PLAN-CONTRACT` `slices:`; sitreps report progress against its plans |
| `docs/proposals/` | Descriptive proposals, NOT authoritative. Paths and component names in them are claims — verify against `git ls-files` before reading; plan text must be re-measured against the tree. | Accepted ideas sequence into roadmap slices (cited as source); never spec |
| `docs/research/` | Flow briefs (`flows-*.md`), `SYSTEM-FLOWS.md`, mechanics source research. | |
| `docs/research-scratch/` | Process masters: `GOVERNANCE.md`, `PROCESS-PLANS.md`, `SLICE-RECORDS.md`, `PLANS-AND-BRIEFS.md`, `screenshot-registry/`, ... | No new files unless the owner names the path explicitly. |
| `docs/research-scratch-source/` | Umamusume game knowledge ONLY: `data/html`, `data/json`, `scripts/`, manifests. | Repository process docs never go here. |
| `docs/roadmap/` | Live roadmaps and verifications: `MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md`, `INDEX.md`. | Ordering authority; plans cite its slices; sitreps update slice acceptance status |
| `docs/scenarios/01`-`09` | Per-scenario guides (`08` is JP-only, don't import). | Hardcoded scenario names in code are a defect; use `config/scenarios.php` labels. |
| `docs/sitrep/` | Session reports and handoffs. DB-safety acceptance record: `DB-SAFETY-ACCEPTANCE-2026-11-25.md` (eight-check tracker); reconciliation evidence: `DB-SAFETY-DOCS-RECONCILIATION-2026-11-25.md`; verification-failure record: `sitrep-2026-11-25-verification-failure.md` (1,973-failure comprehensive run at HEAD `11207c42`; preserves the historical failure count; current candidate NOT release-qualified). | Reports progress on plan slices and roadmap status; feeds remaining work back to roadmap |
| `docs/UMAMUSUME_REFERENCE.md` | Source-cited mechanics corpus. | Single tracked file at docs root. |

## Default placement (no path specified)

- New defect → `KNOWN-ISSUES.md` (`KI-nn`, four-part form, never renumber).
- Repository audit artifact → `docs/audits/<dated-slug>/`, then register in `docs/audits/INDEX.md`.
- Umamusume game-mechanics fact → `docs/research-scratch-source/` — game knowledge only, and only on owner direction.
- Architecture decision → `docs/adr/`, then regenerate `docs/adr/README.md`.
- Process or planning doc → append to the owning `docs/research-scratch/` master. Do not create a new master; the owner must name the path.
- Anything else → ask the owner. Do not invent a folder or place by guess.

## Check order

1. `AGENTS.md` §3 documentation map.
2. `docs/INDEX.md` (this file).
3. The folder's own `INDEX.md` — `docs/audits/`, `docs/roadmap/`, `docs/research-scratch-source/`, `docs/design-research/_scratch/` each carry one.
