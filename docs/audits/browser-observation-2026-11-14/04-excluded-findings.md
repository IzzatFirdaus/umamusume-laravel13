# 04 — Excluded findings

Findings that cannot be closed by code alone, with the kind of decision needed and who owns it.

| ID | Finding | Decision needed | Owner |
|---|---|---|---|
| **EX-02** | BO-003 — `Aut Neque` (a lorem-ipsum factory row) is published in the trainee catalogue with a "Released (Global)" badge and a full detail page | **Data + content.** The row must be removed from the dev/seed data, *and* someone must rule whether a fixture row may ever carry a release badge. The data reset is destructive on the shared dev file and needs explicit owner approval (`AGENTS.md` §10). | Owner (data reset) + Lore Guardian (badge wording) |
| **EX-03** | BO-005 — the turn-edit door is unreachable because run #13 has 0 turns | **None — not a defect.** Recorded so the coverage gap is accounted for. Reaching this door needs a run with a logged turn, which is a data state, not a fix. | n/a |
| **EX-04** | BO-012 — no compare affordance exists on `/support-cards` | **Scope.** Whether support-card comparison is a wanted feature is a product question. `SCREEN_SPEC.md` owns whether the screen should have one; the PRD §6 non-goals own whether it is cut. | Architect → Owner |
| **EX-05** | BO-008's third figure — the wizard says `66 trainees are catalogued` where the catalogue says 72 | **Content/product.** Which population the wizard counts (eligible-for-a-run vs all Global) is a product definition, not a wording fix. Slice 7 can annotate the Dashboard figure, but only the owner can say what 66 *is*. | Owner / Planner Domain Specialist |
| **EX-06** | The `eva01` theme was never measured | **External config / budget.** Not a defect: the theme exists and was left unread to keep the pass bounded. Measuring it needs no decision, just a follow-up pass. | next audit |
| **EX-07** | Peer-work contamination of the baseline | **Process.** A peer `playwright test` and a second worktree were live for the whole walk. `test-results/` grew throughout. No app-source file changed, so the findings stand — but a re-walk on a quiet tree is the only way to make the evidence unconditional. | Owner (quiet-tree scheduling) |
| **EX-08** | The vision model was unavailable; no finding in this register is a visual judgement | **Tooling.** All findings are measured values. The pure-craft pass (density, hierarchy feel, "does this card read as raised") **was not performed** and cannot be recovered from this run's data. | next audit, with vision available |
| **EX-09** | `KNOWN-ISSUES.md` carries uncommitted local edits (+131 lines), so it could not be used as the prior-finding census | **Process.** Comparing against a register mid-edit risks filing a peer's in-flight fix as a new defect. Needs a committed register to reconcile against. | Owner (commit the peer work) |
| **EX-10** | The 0-career baseline was **avoidable**: three seeders exist that populate careers, turns, races, skills and Veterans | **Owner action — operational.** This audit recorded "no completed career reachable" as a fixed fact when it was a *state*, and did not name seeding as the owner action that closes it. That was the real documentation gap. **But the remedy is not `migrate:fresh --seed`:** none of the three is called by `DatabaseSeeder`, which runs only `UmamusumeSeeder`, `UmamusumeRosterSeeder`, `SkillSeeder`, `SourceDocumentSeeder`, `ScenarioSlotSeeder`. Each must be invoked explicitly and in order. | Owner (runs the commands; the audit may not) |
| **EX-11** | The `Result` screen with real data was judged unreachable "because no run is terminal" | **Partially wrong, and corrected here.** The correction proposed seeding the Rosy Dreams run to close this gap. **It would not.** That seeder writes `RunStatus::Active` (`RosyDreamsRiceShowerSeeder.php:158`) — it is a *mid-career* run at Senior Early October, turn 67 of 72, read off the client frames. It closes the **populated-Active** gap (panels with data, caps populated, team tally, growth row), **not** the Result gap. The Result gap is closed only by `UraFinaleRunsSeeder`, which carries eight `RunStatus::Completed` runs, or by `TrackerVeteransSeeder`, which files seven careers as Completed + a Veterans row. | Owner |


## What is *not* excluded

Every other finding — BO-001, BO-002, BO-004, BO-006, BO-007, BO-009, BO-010, BO-011 — is in `03-code-change-plan.md`
as a code slice, each touchable without a schema change, an ADR, a content ruling or an external config change.

## The owner action this audit should have named (EX-10, corrected)

Supplied because the audit's own omission — not naming the seeder — is worth more to the next pass than any
single finding. Every command below is an **owner** action; the audit may not run any of them.

| Seeder | What it actually writes (read from its own docblock and `run()`) | Explicit invocation |
|---|---|---|
| `UmamusumeRosterSeeder`, `SourceDocumentSeeder`, `ScenarioSlotSeeder` | The catalog. **Prerequisite** — the run seeders resolve skills by exact catalog name and cards by id, so seeding runs against an empty catalog produces nothing. | `php artisan db:seed` (already in `DatabaseSeeder`) |
| `RosyDreamsRiceShowerSeeder` | **One Active, mid-career** Unity Cup run: `[Rosy Dreams] Rice Shower`, Senior Early October, turn **67 of 72**, `team_league_placement = 8`, per-stat caps and a growth row populated, races (Nikkei Sho / Tenno Sho Spring 1st / Takarazuka Kinen 1st / Kyoto Daishoten Entered / Arima Kinen Entered), skills, and a six-card deck from `/support-cards` rows 30003, 30016, 30018, 30019, 30020, 30030. Idempotent on `import_source`. | `php artisan db:seed --class=RosyDreamsRiceShowerSeeder` |
| `TrackerVeteransSeeder` | Files careers that **ended** in the tracker: runs 3, 4, 10, 12, 14, 15, 16 become `Completed` runs **plus** a `veterans` row each. Shares one writer with `UraFinaleRunsSeeder` for runs 3, 4, 14, 15, 16. | `php artisan db:seed --class=TrackerVeteransSeeder` |
| `UraFinaleRunsSeeder` | Eight URA Finale runs from the committed tracker, of which **eight carry `RunStatus::Completed`** — the only seeder that reaches the Result screen with data. | `php artisan db:seed --class=UraFinaleRunsSeeder` |

**The trap in the obvious remedy.** `php artisan migrate:fresh --seed` calls `DatabaseSeeder`, which calls **none**
of these three. An owner who runs it expecting populated careers still lands at **0 careers, 0 veterans** — exactly
the baseline that cost this audit two coverage rows. The `--class=` invocations are load-bearing, not decorative.

**Order matters:** catalog first, then `UraFinaleRunsSeeder` and `TrackerVeteransSeeder`, then
`RosyDreamsRiceShowerSeeder`. And `migrate:fresh` is destructive against the shared dev file (`AGENTS.md` §10) —
point `DB_DATABASE` at a scratch file instead.
