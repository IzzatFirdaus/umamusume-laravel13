# Project Rules Index

Before planning or editing, find the row whose globs match the file's path and read that rule file.
The planning step itself follows `AGENTS.md` §2a: publish the goal, scope, acceptance criteria,
ordered TODOs, verification and risks before the first edit, and keep the TODOs current.

| Glob               | Rule file                                      |
| ------------------ | ---------------------------------------------- |
| `**/*.php`         | [code-style.md](code-style.md)                 |
| `**/*.blade.php`   | [code-style.md](code-style.md)                 |
| `app/Models/**`    | [eloquent.md](eloquent.md)                     |
| `app/**`           | [architecture.md](architecture.md)             |
| `tests/**`         | [testing-standards.md](testing-standards.md)   |

Recorded against the tree at `fda8bba`. A rule states the convention only; when the code and a rule disagree, the code wins and the rule is stale.
