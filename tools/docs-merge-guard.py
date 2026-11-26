#!/usr/bin/env python3
"""Non-destructive guard against unauthorised application/tooling changes arriving via Docs deliveries.

Fullstack runs this BEFORE integrating any Docs worktree or branch into master. It is read-only:
it inspects `git diff --cached --name-only` plus untracked files and exits 1 if a path outside the
Docs allowlist changed while the delivery author is not the Docs author, or if a forbidden path
changed regardless of author.

Allowed Docs deliverable paths:
- docs/** (anything under docs/)
- AGENTS.md, EXPERT-OPERATING-MODEL.md, MCP_SETUP.md, CHANGELOG.md, KNOWN-ISSUES.md, README.md

Everything else — app/, routes/, config/, resources/, database/, tests/, public/, artisan,
composer.json, .github/workflows/, vite.config.js, tailwind configs, tools/*, *.json lockfiles
except docs-owned docs/** — is application/tooling surface and Docs does not own it. A Docs
delivery that touches it is stale-branch contamination (see incident 23581aa2): Fullstack rejects
the delivery and returns it to the delivering Specialist's branch; no merge, no reset, no force.

Usage:
    python tools/docs-merge-guard.py <author> [base-ref]

<author> is the delivery author identity (fullstack|backend|frontend|docs|coordinator). `docs` is
exempt because docs-owned commits are legitimate documentation; every other author must not touch
application/tooling paths without explicit owner approval.

`base-ref` (optional) is the ref the delivery is measured against (e.g. `master`); when given the
guard reads `base-ref..HEAD` instead of the working tree, which is the shape a frozen delivery has.

Exit 0 = delivery surface clean; exit 1 = violation listed; exit 2 = not a git repo.
"""

from __future__ import annotations

import subprocess
import sys

DOCS_ALLOWLIST_PREFIXES = ("docs/",)
DOCS_ALLOWLIST_EXACT = {
    "AGENTS.md",
    "EXPERT-OPERATING-MODEL.md",
    "MCP_SETUP.md",
    "CHANGELOG.md",
    "KNOWN-ISSUES.md",
    "README.md",
}

# Paths that are application/tooling surface and must never arrive in a Docs delivery.
FORBIDDEN_PREFIXES = (
    "app/",
    "routes/",
    "config/",
    "resources/",
    "database/",
    "tests/",
    "public/",
    "artisan",
    "composer.json",
    "composer.lock",
    "package.json",
    "package-lock.json",
    "vite.config.js",
    "tailwind.config.js",
    "postcss.config.js",
    ".github/workflows/",
    "nativephp/",
    "phpstan.neon",
    "pint.json",
    "tools/",
)


def git(*args: str) -> str:
    return subprocess.check_output(["git", *args], text=True).strip()


def changed_paths(base: str | None) -> list[str]:
    """Paths a delivery changes: `base..HEAD` when a base ref is given, else staged + untracked."""
    if base is not None:
        return [p for p in git("diff", "--name-only", f"{base}..HEAD").splitlines() if p]
    staged: list[str] = []
    try:
        subprocess.check_call(["git", "diff", "--cached", "--quiet"], stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    except subprocess.CalledProcessError:
        staged = git("diff", "--cached", "--name-only").splitlines()
    untracked = git("ls-files", "--others", "--exclude-standard").splitlines()
    return [p for p in (*staged, *untracked) if p]


def is_docs_owned(path: str) -> bool:
    return path.startswith(DOCS_ALLOWLIST_PREFIXES) or path in DOCS_ALLOWLIST_EXACT


def is_forbidden(path: str) -> bool:
    return path.startswith(FORBIDDEN_PREFIXES) or path in FORBIDDEN_PREFIXES


def main(argv: list[str]) -> int:
    if len(argv) not in (2, 3):
        print("usage: python tools/docs-merge-guard.py <author> [base-ref]", file=sys.stderr)
        return 2
    author = argv[1]
    base = argv[2] if len(argv) == 3 else None

    try:
        paths = changed_paths(base)
    except subprocess.CalledProcessError as exc:
        print(f"docs-merge-guard: not a git repository or git failed: {exc}", file=sys.stderr)
        return 2

    if not paths:
        print("docs-merge-guard: no staged or untracked paths; delivery surface clean.")
        return 0

    violations: list[str] = []
    for path in paths:
        if is_forbidden(path) and not is_docs_owned(path):
            violations.append(path)

    if not violations:
        print(f"docs-merge-guard: {len(paths)} path(s), all docs-owned; delivery surface clean.")
        return 0

    print("docs-merge-guard: DELIVERY REJECTED — unauthorised application/tooling surface:", file=sys.stderr)
    for path in sorted(violations):
        print(f"  ! {path}", file=sys.stderr)
    print(
        f"docs-merge-guard: author={author} is not the Docs delivery owner for these paths. "
        "Return the delivery to its Specialist branch; Fullstack will not merge, reset, or force.",
        file=sys.stderr,
    )
    return 1


if __name__ == "__main__":
    raise SystemExit(main(sys.argv))
