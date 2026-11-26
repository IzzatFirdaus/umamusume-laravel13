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

## Docs-specific Git prohibition

The Technical Documentation Engineer executes **no Git commands** during autonomous development
(read-only included). The sole exception is explicitly authorized staging and committing of the
Docs Expert's own reviewed delivery during a controlled delivery stage
(`git add -- <approved paths>` then `git commit`). Fullstack verifies branch/index/diff first; if
verification is unavailable, Fullstack performs the delivery commit. See `AGENTS.md` and the Docs
worktree `AGENTS.md` override for the illustrative prohibited-command list.

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
