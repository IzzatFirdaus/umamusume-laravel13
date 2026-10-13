#!/usr/bin/env python
"""Reorganise loose files in research-scratch and repoint live citations.

Run with `python` (this box has no `python3`). Dry-run unless --apply.

The classifier is the load-bearing part: a citation that records where a measurement
was taken must keep its old path after a move, or the record becomes a lie. Only
pointers a reader would follow to re-run something get rewritten.
"""

import argparse
import io
import json
import os
import re
import subprocess
import sys

REPO = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
SCRATCH = os.path.join(REPO, "research-scratch")
REORG = os.path.join(SCRATCH, "reorg")
MANIFEST = os.path.join(REORG, "manifest.json")
JOURNAL = os.path.join(REORG, "journal.json")
RW_JOURNAL = os.path.join(REORG, "rewrite-journal.json")

# Buckets, in match order. Extension is the first signal; name overrides sort the .md pile.
RULES = [
    ("scripts", re.compile(r"\.py$")),
    ("out", re.compile(r"\.(txt|tsv|json)$")),
    ("reports", re.compile(r"(audit|inventory|findings|review|report)", re.I)),
    ("drafts", re.compile(r"\.md$")),
]
Dated_deliverable = re.compile(r"^(?:[a-z0-9._-]+-)?\d{4}-\d{2}-\d{2}", re.I)

# Paths this tool will never move and never rewrite, with the document that says why.
REFUSALS = [
    (r"\.sqlite(\.bak)?(-wal|-shm)?$", "WAL-mode scratch DB; a copy or move yields malformed, missing-table, or silently empty files, and peers hold some open"),
    (r"^docs/frontend-review/2026-09-28/", "inventory section 11: dated evidence set, 97+ files, editing one capture invalidates the audit that produced it"),
    (r"^docs/design-research/", "forbidden-edit glob from the standing authorization; also holds the DESIGN.md and CONSTRAINTS.md twins cited 41 and 45 times"),
    (r"^docs/design-research/_scratch/", "already named ignored by GATE-REGISTRY G-60; it is where scratch is meant to sit"),
    (r"^\.scratch-uma/", "321 ignored files whose paths are provenance in 180+ citation lines across tracked docs; see --citations"),
    (r"^research-scratch/data/", "cited by path from tracked docs as live command input; moving it buys nothing"),
    (r"^PLAN\.md$|^KNOWN-ISSUES\.md$|^CONSTRAINTS\.md$|^ARCHITECTURE", "hub documents; renames rewrite citations across a fifth of the corpus"),
]


def git(*args):
    return subprocess.run(
        ["git", "-c", "core.quotepath=false", *args],
        cwd=REPO, capture_output=True, text=True, encoding="utf-8", errors="replace",
    )


def bucket(name):
    if Dated_deliverable.match(name):
        return "reports"
    for label, pat in RULES:
        if pat.search(name):
            return label
    return "misc"


def refused(relpath):
    for pat, why in REFUSALS:
        if re.search(pat, relpath.replace("\\", "/")):
            return why
    return None


def citation_count(basename):
    """Hits for this basename in tracked files outside research-scratch."""
    r = git("grep", "-l", "-F", "--", basename, "--", ".", ":!research-scratch")
    return [p for p in r.stdout.splitlines() if p.strip()]


LIVE = re.compile(
    r"(^\s*(?:php|node|python|wc|cat|ls|grep|find|mkdir|cp|mv|curl|vendor/bin|php artisan|npm|composer)\b"
    r"|(?:run|execute|command|reproduce|regenerate|invoke|see|refer to|to\s+build|the script at)\b"
    r"|require\(|file_get_contents\(|\bbash\b|```\s*[a-z]*\s*$)", re.I)
RECORD = re.compile(
    r"(pulled|measured|recorded|observed|held locally|was written|at `?[0-9a-f]{7,40}`?"
    r"|bytes|hash|no file in|did not|were|is not committed|check once|proved|snapshot"
    r"|\d{4}-\d{2}-\d{2}T|\(\d[\d,]* rows)", re.I)


def classify_line(line):
    """LIVE = a reader would follow it. RECORD = it states where evidence sat. UNKNOWN = default refuse."""
    if RECORD.search(line):
        return "RECORD"
    if LIVE.search(line):
        return "LIVE"
    return "UNKNOWN"


def self_test():
    cases = [
        ('php -r \'$r=json_decode(file_get_contents("research-scratch/data/json/x.json"))\'', "LIVE"),
        ("mkdir -p research-scratch/data/json research-scratch/data/html", "LIVE"),
        ("The dataset already sits at `research-scratch/data/json/character-cards.json` (268 rows, fetched 2026-09-27)", "RECORD"),
        ("`research-scratch/data/json/characters.json` (manifest hash `c6676539`)", "RECORD"),
        ("no file in `.scratch-uma/` carries a modification time in the window", "RECORD"),
        ("see research-scratch/reports/audit.md for the method", "LIVE"),
        ("something mentioned research-scratch/scripts/gates.py in passing", "UNKNOWN"),
    ]
    bad = [(t, want, classify_line(t)) for t, want in cases if classify_line(t) != want]
    for t, want, got in bad:
        print(f"  FAIL want={want} got={got} :: {t[:70]}")
    print(f"self-test: {len(cases) - len(bad)}/{len(cases)} classifier cases pass")
    return 1 if bad else 0


def scan():
    if not os.path.isdir(SCRATCH):
        sys.exit(f"no {SCRATCH}")
    entries, blocked = [], []
    for name in sorted(os.listdir(SCRATCH)):
        full = os.path.join(SCRATCH, name)
        if not os.path.isfile(full) or name == os.path.basename(__file__):
            continue
        rel = os.path.join("research-scratch", name).replace("\\", "/")
        why = refused(rel)
        if why:
            blocked.append({"path": rel, "reason": why})
            continue
        dest = os.path.join("research-scratch", bucket(name), name).replace("\\", "/")
        cited = citation_count(name)
        entries.append({"from": rel, "to": dest, "bucket": bucket(name),
                        "cited_by": cited, "movable": not cited})
    plan = {"moves": entries, "blocked": blocked}
    os.makedirs(REORG, exist_ok=True)
    io.open(MANIFEST, "w", encoding="utf-8", newline="\n").write(json.dumps(plan, indent=2))
    ok = [e for e in entries if e["movable"]]
    print(f"movable {len(ok)} / candidates {len(entries)} / refused-paths {len(blocked)}")
    for e in entries:
        flag = "MOVE " if e["movable"] else "HELD "
        print(f"  {flag} {os.path.basename(e['from']):<46} -> {e['bucket']}/"
              + (f"   cited by {len(e['cited_by'])}" if e["cited_by"] else ""))
    return plan


def relpath(p):
    return os.path.relpath(p, REPO).replace("\\", "/")


def apply_moves(plan, force):
    moves = [m for m in plan["moves"] if m["movable"] or force]
    if not moves:
        print("nothing to move")
        return []
    done = []
    for m in moves:
        src, dst = os.path.join(REPO, m["from"]), os.path.join(REPO, m["to"])
        if os.path.exists(dst):
            print(f"  SKIP target exists: {m['to']}")
            continue
        os.makedirs(os.path.dirname(dst), exist_ok=True)
        tracked = git("ls-files", "--error-unmatch", "--", m["from"]).returncode == 0
        if tracked:
            git("mv", "--", m["from"], m["to"])
        else:
            os.replace(src, dst)
        done.append({**m, "tracked": tracked})
        print(f"  MOVED {m['from']} -> {m['to']}" + ("  [git mv]" if tracked else ""))
    io.open(JOURNAL, "w", encoding="utf-8", newline="\n").write(
        json.dumps(done, indent=2, ensure_ascii=False))
    return done


def rollback():
    if not os.path.isfile(JOURNAL):
        sys.exit(f"no journal at {JOURNAL}")
    for m in reversed(json.load(io.open(JOURNAL, encoding="utf-8"))):
        src, dst = os.path.join(REPO, m["to"]), os.path.join(REPO, m["from"])
        if os.path.exists(dst) or not os.path.exists(src):
            print(f"  SKIP cannot restore {m['from']}")
            continue
        os.makedirs(os.path.dirname(dst), exist_ok=True)
        os.replace(src, dst)
        print(f"  RESTORED {m['from']}")
    os.remove(JOURNAL)


def doc_files(oldpaths):
    """Tracked docs plus untracked-but-not-ignored docs that mention any moved path."""
    hits = set()
    others = [c for c in git("ls-files", "--others", "--exclude-standard").stdout.splitlines() if c.strip()]
    for p in oldpaths:
        r = git("grep", "-l", "-F", "--", p, "--", "*.md", "*.txt", "*.php", "*.py", "*.html")
        hits.update(x for x in r.stdout.splitlines() if x.strip())
        for cand in others:
            if GUARD_DIRS.search(cand):
                continue
            if not re.search(r"\.(md|txt|php|py|html)$", cand):
                continue
            try:
                if p in io.open(os.path.join(REPO, cand), encoding="utf-8", errors="replace").read():
                    hits.add(cand.replace("\\", "/"))
            except OSError:
                pass
    return sorted(hits)


GUARD_DIRS = re.compile(r"^(?:docs/design-research/|docs/frontend-review/|PLAN\.md$|KNOWN-ISSUES\.md$)")


def refused_guard_dirs(cand):
    return GUARD_DIRS.search(cand.replace("\\", "/")) is not None


def rewrite(files, mapping, rewrite_all, dry):
    edits, ledger = 0, []
    for f in files:
        fp = os.path.join(REPO, f)
        try:
            lines = io.open(fp, encoding="utf-8", errors="replace").read().split("\n")
        except OSError as e:
            print(f"  UNREADABLE {f}: {e}")
            continue
        changed = 0
        for i, line in enumerate(lines):
            for old, new in mapping.items():
                if old not in line:
                    continue
                kind = classify_line(line)
                if kind == "RECORD" and not rewrite_all:
                    ledger.append({"file": f, "line": i + 1, "class": kind, "path": old, "applied": False})
                    continue
                if kind == "UNKNOWN" and not rewrite_all:
                    ledger.append({"file": f, "line": i + 1, "class": kind, "path": old, "applied": False})
                    continue
                lines[i] = line.replace(old, new)
                line = lines[i]
                changed += 1
                ledger.append({"file": f, "line": i + 1, "class": kind, "path": old, "applied": not dry})
        if changed and not dry:
            io.open(fp, "w", encoding="utf-8", newline="\n").write("\n".join(lines))
        if changed:
            edits += 1
            print(f"  {'WOULD EDIT' if dry else 'EDITED'} {f}: {changed} line(s)")
        else:
            print(f"  UNTOUCHED {f}: every hit is RECORD or UNKNOWN")
    io.open(RW_JOURNAL, "w", encoding="utf-8", newline="\n").write(
        json.dumps({"files": files, "mapping": mapping, "ledger": ledger}, indent=2, ensure_ascii=False))
    by = {}
    for e in ledger:
        by[e["class"]] = by.get(e["class"], 0) + 1
    print(f"files touched {edits}; line verdicts {by}; ledger -> {relpath(RW_JOURNAL)}")
    remaining = [p for p in mapping if git("grep", "-l", "-F", "--", p, "--", "*.md").stdout.strip()]
    print(f"unresolved live-pointer check: {len(remaining)} old path(s) still match tracked docs (expected: the RECORD-class ones)")


def main():
    ap = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    ap.add_argument("--scan", action="store_true", help="classify candidates, write manifest")
    ap.add_argument("--apply", action="store_true", help="perform the moves")
    ap.add_argument("--force", action="store_true", help="also move cited basenames")
    ap.add_argument("--rewrite", action="store_true", help="repoint live citations after a move")
    ap.add_argument("--rewrite-all", action="store_true", help="also rewrite RECORD and UNKNOWN lines; audited use only")
    ap.add_argument("--dry-run", action="store_true", help="print rewrites without editing")
    ap.add_argument("--rollback", action="store_true", help="reverse the last --apply from the journal")
    ap.add_argument("--self-test", action="store_true", help="check the citation classifier")
    a = ap.parse_args()

    if a.self_test:
        sys.exit(self_test())
    if a.rollback:
        sys.exit(rollback())

    plan = scan() if (a.scan or not os.path.isfile(MANIFEST)) else json.load(io.open(MANIFEST, encoding="utf-8"))
    if not (a.apply or a.rewrite):
        print("\ndry run only. nothing moved. next: --apply, then --rewrite --dry-run")
        return
    moved = apply_moves(plan, a.force) if a.apply else json.load(io.open(JOURNAL, encoding="utf-8")) if os.path.isfile(JOURNAL) else []
    if a.rewrite:
        mapping = {m["from"]: m["to"] for m in moved}
        files = doc_files(list(mapping))
        if not files:
            print("no documentation file references the moved paths")
            return
        guard = [f for f in files if re.search(refused_guard_dirs(f), re.I)]
        if guard:
            print(f"REFUSED {len(guard)} do-not-touch file(s): {', '.join(guard[:8])}")
        files = [f for f in files if f not in guard]
        rewrite(files, mapping, a.rewrite_all, a.dry_run)


if __name__ == "__main__":
    main()
