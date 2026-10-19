#!/usr/bin/env python
"""Documentation census: the reproducible successor to the deleted inventory tables.

Every number the pre-consolidation inventory hand-counted went stale the moment history
moved. This prints the same facts from `git` on demand so nobody has to trust a dated table
again. Run it against the tree you are on and cite the sha it prints.

The subject of the measurement is a git ref, HEAD by default, because a count with no fixed
subject can be moved by anyone mid-edit: the same HEAD read from two machines gives two
numbers when either carries uncommitted work. `--working-tree` measures the machine instead,
which is what a developer wants to see when they ask what their own uncommitted edit does to
the count.

Usage: python tools/doc_census.py [--working-tree]
"""

import io
import os
import re
import subprocess
import sys

REPO = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
MASTERS_DIR = "docs/research-scratch"
EXEMPT_PREFIXES = (".ai/", "docs/adr/")

# A file whose header carries this marker is a catalogue: its backticked paths are the subject
# it enumerates, not citations a reader follows. `DOCUMENTATION-INVENTORY-2026-09-30.md` lists every
# markdown file in the tree, and 142 of those names point at files that no longer exist, which is
# the inventory reporting correctly rather than rotting. The flag lives in the file, not in a list
# here, so it travels with the content: promote the inventory again and the exemption comes with it.
# Every excluded file is printed by main(), so opting out is never invisible.
CENSUS_FLAG = "<!-- census: content-not-citations -->"

# A line carrying this marker quotes the text it corrects, so the dead path it names is quoted
# history, not an instruction a reader is asked to follow. The errata doctrine in
# `docs/research-scratch/PROCESS-PLANS.md` requires a dated correction to preserve the original
# sentence, which necessarily re-quotes the dead path and so raises the count every time the
# doctrine is followed. The marker is line-scoped and explicit, like `lore-ignore-line` in
# `tools/lore.php`: one line opts out, and every skipped line is printed by main(), so an opt-out
# is never invisible. It is not applied to any file yet.
QUOTED_ORIGINAL_FLAG = "<!-- census: quoted-original -->"


def git(*args):
    return subprocess.run(["git", "-c", "core.quotepath=false", *args], cwd=REPO,
                          capture_output=True, text=True, encoding="utf-8", errors="replace").stdout


# What a census run measures. A git ref by default, because a count with no fixed subject can
# be moved by anyone mid-edit: the same HEAD read from two machines gives two numbers when
# either carries uncommitted work, and the pin then measures the machine rather than the
# repository. The working tree stays available behind `--working-tree`, because a developer
# wants to see what their own uncommitted edit does to the count.
DEFAULT_REF = "HEAD"


def subject_paths(ref=DEFAULT_REF):
    """Every tracked path at the subject: a ref's tree, or the index for the working tree."""
    if ref:
        return [p for p in git("ls-tree", "-r", "--name-only", ref).splitlines() if p.strip()]
    return [p for p in git("ls-files").splitlines() if p.strip()]


def subject_texts(files, ref=DEFAULT_REF):
    """The files' contents at the subject: a ref's blobs, or the working tree on disk.

    The ref's blobs are read in one `git cat-file --batch` pass, so measuring a ref costs one
    subprocess rather than one per file.
    """
    if not ref:
        out = {}
        for f in files:
            try:
                out[f] = io.open(os.path.join(REPO, f), encoding="utf-8", errors="replace").read()
            except OSError:
                pass
        return out
    if not files:
        return {}
    proc = subprocess.run(["git", "cat-file", "--batch"], cwd=REPO,
                          input=("\n".join(f"{ref}:{f}" for f in files)).encode(),
                          capture_output=True)
    out, pos = {}, 0
    for f in files:
        nl = proc.stdout.find(b"\n", pos)
        if nl < 0:
            break
        header = proc.stdout[pos:nl].decode("utf-8", "replace").split()
        if len(header) < 3:
            break
        size = int(header[2])
        start = nl + 1
        out[f] = proc.stdout[start:start + size].decode("utf-8", "replace")
        pos = start + size + 1
    return out


def md_files(ref=DEFAULT_REF):
    return [p for p in subject_paths(ref) if p.endswith(".md")]


def by_dir(files):
    counts = {}
    for f in files:
        counts[os.path.dirname(f) or "."] = counts.get(os.path.dirname(f) or ".", 0) + 1
    return dict(sorted(counts.items(), key=lambda kv: -kv[1]))


def lines_of(rel, texts=None):
    if texts is not None and rel in texts:
        return len(texts[rel].split("\n"))
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


def heads(texts, depth=5):
    """Map each file to its opening block. The marker sits in the header, where this repo puts the
    other machine-readable stamps (`PRODUCT.md` carries `impeccable:product-schema` on line 3)."""
    out = {}
    for f, text in texts.items():
        out[f] = "\n".join(text.split("\n", depth)[:depth])
    return out


def catalogues(texts):
    """Tracked markdown that declares itself a catalogue, so its paths are excluded from the count."""
    return sorted(f for f, head in heads(texts).items() if CENSUS_FLAG in head)


def dead_links(skipped=None, ref=DEFAULT_REF):
    """Backticked .md references that no longer resolve. A deletion without a repoint shows up here.

    A bare name is tried as written, under `docs/`, under `docs/research-scratch/`, and beside the
    citing file, because this corpus cites root files, docs files and masters with the same
    shorthand. Bare master names such as `GOVERNANCE.md` resolve under the masters directory, so
    counting them as dead was a resolver gap rather than citation rot.

    Two further resolver gaps of exactly that kind were measured and closed on 2026-10-12, when the
    ratchet read 784 against a 708 baseline and none of the rise was citation rot in the sense the
    gate polices:

    * A slash-bearing ref was never tried beside the citing file, only as written and under `docs/`.
      So `docs/DESIGN.md` inside `jev-inspect/CHANGELOG.md` counted dead while the tracked
      `jev-inspect/docs/DESIGN.md` sat next to it. A relative ref is now tried beside its file too.
    * A bare name was tried in three directories only, so the shorthand broke for every corpus area
      added after those three. `AGENTS.md:54` telling a reader to open `code-style.md` counted as a
      dead link when `.ai/rules/code-style.md` is tracked and present; so did `02-unity-cup.md` for
      `docs/scenarios/`, `frontend-development-plan.md` for `docs/proposals/`, and the audit and
      screenshot-note names. A bare name now resolves when a tracked markdown file of that name
      exists anywhere, which is what the shorthand means.

    127 of the 784 were these two false positives, and all 27 distinct bare names the widened rule
    resolves match exactly one tracked file today, so nothing was read as live by accident.

    ponytail: the bare-name rule keys on the name, not the path, so a deleted `docs/foo/README.md`
    would read as live via root `README.md`. Ceiling: a false negative only where a basename is not
    unique. Upgrade path: fall back to the basename rule only when that basename has one match in the
    tracked set, and require a path when it has several.

    Files listed by `catalogues()` are skipped whole: their backticked paths are content, not
    citations. The exclusion is printed by `main()` so it cannot silently shrink the ratchet.

    A tracked file that is absent from disk (an uncommitted deletion) is counted in neither kind of
    dead link: it cannot be read, so it contributes no citations either way. It is collected and
    reported as `MISSING_FILE` rather than raised on. Measured 2026-10-03: one peer session deleted
    three tracked masters without staging, `io.open` raised `FileNotFoundError` here, and
    `DocCitationParityTest`, which only shells out to this script, failed for a cause that was not
    citation rot. A tool whose subject is missing files is not entitled to choke on one.

    Un-backticking a dead path lowers the count too: the resolver only ever sees backticked refs.

    The subject is a git ref by default, and the working tree is `ref=None`. On a ref the
    resolver asks the ref's tree what it holds, so a file that exists only on this machine
    reads GONE, which is what a reader of that ref would see; the UNTRACKED class, which is
    the machine-local nuance, belongs to the working tree alone.

    @return tuple[list[tuple[str, int, str, str]], list[str]] the dead links, and the unreadable files
    """
    bad = []
    unreadable = []
    files = md_files(ref)
    texts = subject_texts(files, ref)
    skip = set(catalogues(texts))
    listing = set(subject_paths(ref))
    tracked_md_names = {os.path.basename(p) for p in listing if p.endswith(".md")}
    for f in files:
        if f in skip:
            continue
        near = os.path.dirname(f)
        text = texts.get(f)
        if text is None:
            unreadable.append(f)
            continue
        bad.extend(dead_refs_in_text(f, text, near, listing, tracked_md_names, skipped,
                                     disk=not ref))
    return bad, unreadable


def dead_refs_in_text(f, text, near, listing, names, skipped=None, disk=True):
    """The dead references in one file's text.

    A line carrying `QUOTED_ORIGINAL_FLAG` is not scanned: it quotes the text it corrects, so
    the dead path it names is quoted history rather than an instruction to follow. The skipped
    line is appended to `skipped` when a list is passed, so the caller can report the exclusion.

    `disk` decides the last classification: on the working tree a target that exists here but
    is not tracked is UNTRACKED, and on a ref there is no such thing, so everything unresolved
    is GONE.

    @return list[tuple[str, int, str, str]] the dead references, as (file, line, ref, kind)
    """
    bad = []
    for n, line in enumerate(text.split("\n"), 1):
        if QUOTED_ORIGINAL_FLAG in line:
            if skipped is not None:
                skipped.append((f, n))
            continue
        for ref in re.findall(r"`((?:[\w./&-]+/)?[\w.-]+\.md)`", line):
            cand = ref.replace("\\", "/").lstrip("/")
            tries = [cand]
            if "/" not in cand:
                tries += [f"docs/{cand}", f"{MASTERS_DIR}/{cand}",
                          f"{near}/{cand}" if near else cand]
            else:
                tries += [f"docs/{cand}", f"{near}/{cand}" if near else cand]
            if any(t in listing for t in tries):
                continue
            if "/" not in cand and cand in names:
                continue
            kind = "GONE"
            if disk:
                target = next((t for t in tries if os.path.exists(os.path.join(REPO, t))), None)
                kind = "UNTRACKED" if target else "GONE"
            bad.append((f, n, ref, kind))
    return bad


def main(argv=None):
    argv = sys.argv[1:] if argv is None else argv
    # The subject of the measurement. A ref by default, because a count with no fixed subject
    # can be moved by anyone mid-edit; `--working-tree` measures the machine instead, which is
    # what a developer wants to see when they ask what their own uncommitted edit does.
    if "--working-tree" in argv:
        ref = None
    else:
        unknown = [a for a in argv if not a.startswith("--")]
        if unknown:
            print(f"unknown argument {unknown[0]!r}; usage: doc_census.py [--working-tree]")
            return 2
        ref = DEFAULT_REF

    files = md_files(ref)
    texts = subject_texts(files, ref)
    sha = git("rev-parse", "--short", "HEAD").strip()
    branch = git("rev-parse", "--abbrev-ref", "HEAD").strip()
    if ref:
        print(f"measured at {ref} {sha} on {branch} (the committed tree)")
    else:
        print(f"measured at the working tree on {branch} (uncommitted edits included)")
    print(f"tracked markdown: {len(files)}")

    masters = sorted(f for f in files if f.startswith(MASTERS_DIR + "/"))
    print(f"\nmasters in {MASTERS_DIR}: {len(masters)}, "
          f"{sum(max(lines_of(m, texts), 0) for m in masters)} lines")
    inb = inbound_per_master(masters)
    for m in masters:
        print(f"  {m.split('/')[-1]:38} {lines_of(m, texts):6} lines  {inb[m]:3} inbound")

    outside = [f for f in files if not f.startswith(MASTERS_DIR + "/")
               and not f.startswith(EXEMPT_PREFIXES)]
    print(f"\nby directory (all tracked md):")
    for d, n in by_dir(files).items():
        print(f"  {n:4}  {d}")

    print(f"\noutside masters, excluding docs/adr and .ai: {len(outside)}")
    for f in outside:
        print(f"  {lines_of(f, texts):6}  {f}")

    skipped = []
    dead, unreadable = dead_links(skipped, ref)
    cats = catalogues(texts)
    print(f"\nMISSING_FILE (tracked, absent from disk: an uncommitted deletion, unscannable here): "
          f"{len(unreadable)}")
    for f in unreadable:
        print(f"  MISSING_FILE {f}")
    print(f"\ncatalogue files excluded from the dead-link count ({CENSUS_FLAG}): {len(cats)}")
    for c in cats:
        print(f"  {c}")
    print(f"\nquoted-original lines excluded from the dead-link count ({QUOTED_ORIGINAL_FLAG}): {len(skipped)}")
    for f, n in skipped[:20]:
        print(f"  {f}:{n}")
    if len(skipped) > 20:
        print(f"  ... {len(skipped) - 20} more")
    gone = [d for d in dead if d[3] == "GONE"]
    untracked = [d for d in dead if d[3] == "UNTRACKED"]
    print(f"\ndead markdown links: {len(dead)} ({len(gone)} GONE, {len(untracked)} UNTRACKED)")
    print("  GONE      = cited and absent from the subject: an instruction that cannot be followed")
    print("  UNTRACKED = on disk but not in git: works locally, breaks on a fresh clone")
    print("  Most GONE lines are historical citations inside masters naming the sources they")
    print("  absorbed, which are records, not broken links. Read the per-target summary before")
    print("  repointing anything, and repoint only the lines a reader would actually follow.")
    for f, n, ref_, kind in dead[:20]:
        print(f"  {kind:9} {f}:{n} -> {ref_}")
    if len(dead) > 20:
        print(f"  ... {len(dead) - 20} more")
    top = {}
    for _, _, ref_, _ in dead:
        top[ref_] = top.get(ref_, 0) + 1
    print("most-cited dead targets:")
    for ref_, c in sorted(top.items(), key=lambda kv: -kv[1])[:8]:
        print(f"  {c:4}  {ref_}")

    print(fence_report(files))
    return 0


def fence_report(files):
    """Invalid closing fences per file, from `md_fence_check.py`'s classifier.

    A closing fence carries no info string under CommonMark, so a block written
    ```bash ... ```text does not close where its author meant it to and the prose
    after it renders as code. Reported here because the census is what an agent runs
    first, and a count of 0 is the only state that needs no explanation.

    Run `python tools/md_fence_check.py <file>` for the line numbers.
    """
    import importlib.util

    path = os.path.join(REPO, "tools", "md_fence_check.py")
    spec = importlib.util.spec_from_file_location("md_fence_check", path)
    mod = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(mod)

    offenders = []
    for f in files:
        try:
            invalid, _ = mod.classify(f)
        except OSError:
            continue
        if invalid:
            offenders.append((len(invalid), f))

    total = sum(n for n, _ in offenders)
    lines = [f"\ninvalid closing fences (working tree): {total} across {len(offenders)} file(s)"]
    for n, f in sorted(offenders, key=lambda t: -t[0]):
        lines.append(f"  {n:4}  {f}")
    if total:
        lines.append("  Fix with `python tools/md_fence_check.py <file>`, which names each line.")
    return "\n".join(lines)


if __name__ == "__main__":
    sys.exit(main())
