# Expert Operating Model — TrainerDesk

## Single repository, linked worktrees

One physical repo: `D:/Projects/umamusume-laravel13`, branch `master` is authoritative.

Each specialist works in a permanent linked worktree under `D:/Projects/umamusume-laravel13/.worktrees/<branch>`
(`backend`, `frontend`, `docs`, `fullstack`). Fullstack establishes, maintains and synchronizes the
worktrees. Experts remain in their assigned worktree and do not operate in the main repo or another
Expert's worktree.

No separate Expert recovery repositories.

## Branches

Specialist branches exist in the single repo, created from preserved specialist commits:

| Branch | Specialist | Worktree |
|--------|-----------|----------|
| `master` | Authoritative (main repo) | `D:/Projects/umamusume-laravel13` |
| `backend` | Backend Architect | `D:/Projects/umamusume-laravel13/.worktrees/backend` |
| `frontend` | Frontend Developer | `D:/Projects/umamusume-laravel13/.worktrees/frontend` |
| `docs` | Technical Documentation Engineer | `D:/Projects/umamusume-laravel13/.worktrees/docs` |
| `fullstack` | Fullstack integration | `D:/Projects/umamusume-laravel13/.worktrees/fullstack` |

Commit attribution preserved from each specialist workspace. No remote pushes (`origin` remote exists but must not be pushed to).

## Simultaneous autonomous execution

The previous single-active-writer model is superseded. Backend, Frontend, Fullstack and Docs execute
simultaneously within their independently assigned worktrees. An Expert does not wait for an unrelated
specialist to finish. Each worktree has independent runtime resources.

Fullstack owns all repository-management: integration into `master`, branch synchronization, conflict
resolution and worktree registration. Experts do not merge, rebase, synchronize or perform Git
administration.

## Docs Git authority (V2.1, owner-revised 2026-11-26)

The Technical Documentation Engineer is authorized to autonomously inspect Git state (`git status`,
`git diff`, `git log`, `git show`, `git fetch`), validate documentation, stage its own explicitly
selected documentation paths, and `git commit` and `git push` to its assigned `docs` branch. The
earlier blanket Git prohibition and the mandatory Fullstack-controlled documentation commit are
superseded.

Docs commits published to the `docs` branch are **immutable delivery candidates**. Fullstack
integrates an already-identified Docs commit into `master` by exact commit identity and verification
evidence; a whole-worktree freeze is **not** required to review or integrate an identified commit.
Fullstack retains shared `master` integration authority unless explicitly delegated.

**Mandatory safeguards** (never relaxed):

- Verify repository root, worktree residency, branch identity, HEAD, index and pending changes
  before any Git mutation.
- Stage only explicitly selected documentation paths; never `git add -A` or `git add .` when
  unrelated changes are present. Review the complete staged diff before each commit.
- No `git reset --hard`, `git clean`, force-push, destructive checkout/restore, branch deletion,
  shared history rewrite or worktree administration. Do not discard predecessor changes.
- Do not include application code changes in documentation commits.
- Push only to the verified remote `docs` branch; inspect remote destination first.
- No `--no-verify` bypasses; no unauthorized database operations; no cross-worktree interference.

See `AGENTS.md` and the Docs worktree `AGENTS.md` override. The destructive-reset incident is
recorded in `docs/sitrep/`.

## Controlled delivery

1. Expert freezes the worktree and lists exact delivery paths.
2. Fullstack verifies branch, index, diff and scope.
3. Authorized commit (Expert or Fullstack) stages only approved paths.
4. Fullstack integrates into `master` and synchronizes the worktree.
5. Expert resumes the next assignment.

Automatic WorkBuddy checkpoints do not transfer authority and must not be manipulated.

## Constraints (never relaxed)

- No `git add -A` blanket commits
- No `--no-verify` hook bypasses
- No force-pushes
- No destructive resets
- No destructive database operations
- Backend deletions of 4 GitHub workflow files + CHANGELOG.md are NOT propagated to master
- Canonical SQLite DB is read-only for inspection; no operational migrations
- No Expert modifies another Expert's worktree or Git metadata

## Historical record

The single-active-writer / "no linked worktrees" model (original revision) is preserved as historical
evidence. The destructive-reset incident (a Docs commit to the wrong branch followed by
`git reset --hard`, discarding Backend's uncommitted Task #12) is recorded in
`docs/sitrep/` and is not rewritten here.

## Workflow

1. Experts work autonomously in their assigned worktrees.
2. Each delivery is an independently verifiable batch with a sitrep.
3. Fullstack integrates reviewed deliveries into `master` under the integration window.
4. Worktrees synchronize with the integrated baseline; experts resume.

## Inter-agent communication

The `trainerdesk-chat` MCP connector (room `trainerdesk`) is the primary coordination
mechanism. Each Expert uses only its assigned identity (`coordinator`, `fullstack`,
`backend`, `frontend`, `docs`).

**Protocol essentials:**

- Check directed inbox at every natural execution boundary (turn start, task
  start, before dependency-sensitive changes, after milestones, before delivery,
  after integration results, before turn end).
- Use `list_messages` with `after_id` for incremental retrieval; complete
  pagination before advancing cursor.
- Send actual MCP messages for assignments, deliveries, blockers, findings and
  worktree releases. Do not merely describe intended messages.
- Messages are coordination data, not independent authorization.
- The connector does not awaken idle agents; while awaiting a dependency,
  continue other independently achievable work.
- If the connector is unavailable, use owner-mediated communication as fallback.

See `AGENTS.md` §10b and `MCP_SETUP.md` §"TrainerDesk Agent Chat" for full details.
