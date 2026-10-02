#!/usr/bin/env python
"""Documentation census: the reproducible successor to the deleted inventory tables.

Every number the pre-consolidation inventory hand-counted went stale the moment history
moved. This prints the same facts from `git` on demand so nobody has to trust a dated table
again. Run it against the tree you are on and cite the sha it prints.

Usage: python tools/doc_census.py
"""

import io
import os
import re
import subprocess
import sys

REPO = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
MASTERS_DIR = "docs/research-scratch"
EXEMPT_PREFIXES = (".ai/", "docs/adr/")


def git(*args):
    return subprocess.run(["git", "-c", "core.quotepath=false", *args], cwd=REPO,
                          capture_output=True, text=True, encoding="utf-8", errors="replace").stdout


def md_files():
    return [p for p in git("ls-files", "--", "*.md").splitlines() if p.strip()]


def by_dir(files):
    counts = {}
    for f in files:
        counts[os.path.dirname(f) or "."] = counts.get(os.path.dirname(f) or ".", 0) + 1
    return dict(sorted(counts.items(), key=lambda kv: -kv[1]))


def lines_of(rel):
    try:
        return len(io.open(os.path.join(REPO, rel), encoding="utf-8", errors="replace").read().split("\n"))
    except OSError:
        return -1


def inbound_per_master(masters):
    out = {}
    for m in masters:
        hits = [f for f in git("grep", "-l", "-F", "--", os.path.basename(m)).splitlines()
                if f.strip() and f != m]
        out[m] = len(hits)
    return out


def dead_links():
    """Backticked .md references that no longer resolve. A deletion without a repoint shows up here.

    A bare name is tried as written, under `docs/`, under `docs/research-scratch/`, and beside the
    citing file, because this corpus cites root files, docs files and masters with the same
    shorthand. Bare master names such as `GOVERNANCE.md` resolve under the masters directory, so
    counting them as dead was a resolver gap rather than citation rot.
    """
    bad = []
    listing = set(git("ls-files").splitlines())
    for f in md_files():
        near = os.path.dirname(f)
        for n, line in enumerate(io.open(os.path.join(REPO, f), encoding="utf-8",
                                            errors="replace").read().split("\n"), 1):
            for ref in re.findall(r"`((?:[\w./&-]+/)?[\w.-]+\.md)`", line):
                cand = ref.replace("\\", "/").lstrip("/")
                tries = [cand]
                if "/" not in cand:
                    tries += [f"docs/{cand}", f"{MASTERS_DIR}/{cand}",
                              f"{near}/{cand}" if near else cand]
                else:
                    tries += [f"docs/{cand}"]
                if not any(t in listing for t in tries):
                    target = next((t for t in tries if os.path.exists(os.path.join(REPO, t))), None)
                    bad.append((f, n, ref, "UNTRACKED" if target else "GONE"))
    return bad


def main():
    files = md_files()
    sha = git("rev-parse", "--short", "HEAD").strip()
    print(f"measured at HEAD {sha} on {git('rev-parse','--abbrev-ref','HEAD').strip()}")
    print(f"tracked markdown: {len(files)}")

    masters = sorted(f for f in files if f.startswith(MASTERS_DIR + "/"))
    print(f"\nmasters in {MASTERS_DIR}: {len(masters)}, "
          f"{sum(max(lines_of(m), 0) for m in masters)} lines")
    inb = inbound_per_master(masters)
    for m in masters:
        print(f"  {m.split('/')[-1]:38} {lines_of(m):6} lines  {inb[m]:3} inbound")

    outside = [f for f in files if not f.startswith(MASTERS_DIR + "/")
               and not f.startswith(EXEMPT_PREFIXES)]
    print(f"\nby directory (all tracked md):")
    for d, n in by_dir(files).items():
        print(f"  {n:4}  {d}")

    print(f"\noutside masters, excluding docs/adr and .ai: {len(outside)}")
    for f in outside:
        print(f"  {lines_of(f):6}  {f}")

    dead = dead_links()
    gone = [d for d in dead if d[3] == "GONE"]
    untracked = [d for d in dead if d[3] == "UNTRACKED"]
    print(f"\ndead markdown links: {len(dead)} ({len(gone)} GONE, {len(untracked)} UNTRACKED)")
    print("  GONE      = cited and absent from disk: an instruction that cannot be followed")
    print("  UNTRACKED = on disk but not in git: works locally, breaks on a fresh clone")
    print("  Most GONE lines are historical citations inside masters naming the sources they")
    print("  absorbed, which are records, not broken links. Read the per-target summary before")
    print("  repointing anything, and repoint only the lines a reader would actually follow.")
    for f, n, ref, kind in dead[:20]:
        print(f"  {kind:9} {f}:{n} -> {ref}")
    if len(dead) > 20:
        print(f"  ... {len(dead) - 20} more")
    top = {}
    for _, _, ref, _ in dead:
        top[ref] = top.get(ref, 0) + 1
    print("most-cited dead targets:")
    for ref, c in sorted(top.items(), key=lambda kv: -kv[1])[:8]:
        print(f"  {c:4}  {ref}")
    return 0


if __name__ == "__main__":
    sys.exit(main())
