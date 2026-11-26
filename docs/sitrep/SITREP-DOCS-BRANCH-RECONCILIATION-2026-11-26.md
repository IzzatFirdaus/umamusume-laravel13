# SITREP — Docs Branch Reconciliation & Merge Incident Root Cause (2026-11-26)

**Role:** Docs (TechnicalDocumentationEngineer)
**Authorised branch:** `docs` → `origin/docs`
**Verified master baseline:** `3b9415c6` (restored; clean)

## 1. Verified current state

- `master` = `3b9415c6` — clean, contains restoration fixes `57faa9de` + `b9a714f2`.
- `origin/docs` fast-forwarded `7b34a048 → 3b9415c6` (FF, non-destructive, explicit refspec `master:refs/heads/docs`).
- Effective diff `origin/docs` vs `master`: **empty** (identical trees). Non-documentation diff: **none**.
- Docs contributions from `67e7d947`, `3f78662f`, `7b34a048` are already integrated in master (via `23581aa2`); nothing docs-unique remained to deliver.

## 2. Incident root cause (evidence-based)

Regression `23581aa2` deleted/reverted Task#12 + Check#6 application code on master. Mechanism:

1. `67e7d947` merged docs-side `4039b1ac` with master-side `0f58aefb` (which carried the Task#12/Check#6 code). Merge base `f820ada9` predates the code.
2. The merge resolution took the **docs side wholesale**. Effective diff `0f58aefb → 67e7d947` dropped:
   - `app/Services/DatabaseSafety/AgentSessionGuard.php` (157 → 122 lines)
   - `tests/Feature/Check6FixtureClassificationTest.php` (deleted)
   - `tools/install-local-mcp-adapters.mjs` (deleted)
   - `UmamusumeController`, `CockpitController`, `CatalogController`, `UmamusumeResource`, `Umamusume` model, `tools/agent-run.php`, `DatabaseSafetyTest`, `CatalogRosterTreeTest`, `AgentSessionIsolationTest` (reverted)
   - Only 3 doc files changed (AGENTS/EXPERT-OPERATING-MODEL/MCP_SETUP) — i.e. a docs-only merge that discarded master code.
3. `23581aa2` then merged stale docs `7b34a048` into master `6e17dca0`. Base `0f58aefb` HAD the code; master kept it; docs lacked it → 3-way merge correctly propagated the deletion to master.

**Conclusion:** not branch divergence alone. A docs-side merge (`67e7d947`) resolved conflicts by discarding master's code, then a subsequent docs→master merge propagated the loss. Fixes `57faa9de`/`b9a714f2` restored the code; master is clean.

## 3. Actions taken

- Backup branch `backup/docs/pre-reconcile-20261126T2332` = `7b34a048` (pre-reconcile docs).
- Fast-forwarded `origin/docs` to restored master `3b9415c6` (no history rewrite, no force, no app-code edits).
- Markdown fence parity validated on untracked docs (all balanced).

## 4. Excluded (uncertain ownership / concurrent — per V3 §11)

Main worktree (master) holds concurrent, uncommitted work NOT committed by Docs:
- App-code edits (other agents): `CockpitController.php`, `AdvisorSnapshot.php`, `AdvisorRail.vue`, `Cockpit.vue`, `routes/web.php`.
- Untracked docs WIP (root→docs reorg): `project-status.md`, `docs/{2026-11-25,CUTOVER-PROCEDURE,DELIVERY-MANIFEST,EXPERT-OPERATING-MODEL,RECOVERY-MANIFEST,SESSION_HANDOFF-V2.1-2026-11-26,SOURCE-BACKED-RECONSTRUCTION,TRAINERDESK_REMAINING_WORK_ROADMAP_2026-11-26}.md`, `docs/sitrep/P0-CANONICAL-DB-PRESERVATION-PROCEDURE-2026-11-26.md`; plus worktree deletions of root-level `2026-11-25.md`, `CUTOVER-PROCEDURE.md`, `DELIVERY-MANIFEST.md`, `RECOVERY-MANIFEST.md`, `SOURCE-BACKED-RECONSTRUCTION.md`.

These need owner confirmation of authorship before commit (root md deletions + docs/ additions form one reorg; ownership not established).

## 5. Outstanding / next actions

1. Owner to confirm ownership of the untracked root→docs reorg; then commit as docs-only slice.
2. Local `docs` worktree (`.worktrees/docs`) still holds stale uncommitted reverts (the contamination) and local `docs` ref is behind `origin/docs`. Owner to `git -C .worktrees/docs reset --hard origin/docs` (destructive to stale worktree state only; committed state preserved in backup branch).
3. Fullstack to confirm no further integration depends on pre-reconcile docs ref.
4. `project-status.md` "Latest supplied published master" still cites `6e17dca0`; update to `3b9415c6` when the file is committed.

## 6. Validation commands

```
git merge-base --is-ancestor 7b34a048 master      # FF-safe
git push origin master:refs/heads/docs            # 7b34a048..3b9415c6
git diff --stat origin/docs master                # empty
git diff --name-only origin/docs master -- '*.php' '*.mjs' '*.vue' '*.ts' '*.json'  # none
```