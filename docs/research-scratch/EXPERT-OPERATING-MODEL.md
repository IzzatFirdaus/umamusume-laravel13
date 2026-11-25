# Expert Operating Model — TrainerDesk

## Single repository

One physical repo: `D:/Projects/umamusume-laravel13`, branch `master` is authoritative.

No separate Expert recovery repositories. No linked worktrees for daily work.

## Branches only

Specialist branches exist in the single repo, created from preserved specialist commits:

| Branch | Specialist | Commit | Source |
|--------|-----------|--------|--------|
| `master` | Authoritative | `5b8bfdaa` | Consolidation commit |
| `backend` | Backend Architect | `9e87eab2` | trainerdesk-backend |
| `frontend` | Frontend Developer | `186eb10b` | trainerdesk-frontend |
| `docs` | Technical Documentation Engineer | `06185d54` | trainerdesk-docs |
| `fullstack` | Fullstack integration | `1d85c86c` | origin/fullstack |

Commit attribution preserved from each specialist workspace. No remote pushes (`origin` remote exists but must not be pushed to).

## One active writer at a time

Only one Expert writes implementation code at a time. The active writer owns their branch; other Experts review via PR/comment, not direct writes. Handoff between writers must be documented in `docs/sitrep/`.

## Constraints (never relaxed)

- No `git add -A` blanket commits
- No `--no-verify` hook bypasses
- No force-pushes
- No destructive resets
- No destructive database operations
- Backend deletions of 4 GitHub workflow files + CHANGELOG.md are NOT propagated to master
- Canonical SQLite DB is read-only for inspection; no operational migrations

## Workflow

1. Active writer works on their branch
2. Changes merged to `master` via explicit merge commit (preserving attribution)
3. Sitrep written in `docs/sitrep/` documenting the handoff
4. Next writer takes over
