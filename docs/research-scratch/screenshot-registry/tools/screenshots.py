"""Screenshot research registry for docs/game-screenshots/.

The registry is the authoritative per-image record for the original in-game corpus.
It is a machine-readable JSONL set under docs/research-scratch/screenshot-registry/.
This script is the only writer for it; visual inspection is never recorded by a
successful run of this tool, only by an explicit `review` call.

Subcommands:
    inventory       regenerate machine-derived fields, preserve review fields
    notes           reconcile legacy note files into notes-index.jsonl
    validate        check registry integrity, exit non-zero on failure
    report          write COVERAGE.md and print the dashboard
    queue           print the prioritized outstanding-work list
    sheet           build a labelled contact sheet for a set of ids
    show            print filtered records for batch preparation
    review          record a review decision for one or more ids
    refs            rebuild the reverse citation index
    disposition     record/remove/list the disposition of an unresolved citation
    evidence        append a mechanic claim backed by supporting frames
    batch           define, inspect, and record results for a batch
    batch-summary   deterministic roll-up of a batch manifest
    selftest        assert the tool's invariants on a throwaway corpus

Concurrency:
    Every command that mutates shared state is serialized on an OS-level
    advisory lock (``inventory.jsonl.lock``): inventory, notes, review,
    evidence, batch, batch-summary, disposition, report.  ``refs`` and
    ``sheet`` are read-only over the inventory and write their own output
    file atomically.  Shared JSONL/JSON writes use a process-unique temp
    file plus ``os.replace``.  The lock is released by the OS if a writer
    dies, so there is no stale-lock window.

Paths are resolved from this file's location, so the script can be run from any
working directory.

Usage:
    python docs/research-scratch/screenshot-registry/tools/screenshots.py inventory
    python ... validate
    python ... report
    python ... queue --limit 40
    python ... show --state not_reviewed --full --limit 20
    python ... review --ids 025344,025400 --state verified --batch B001 --observer X --confidence high --note "..."

Exit codes: 0 clean, 1 validation failure, 2 usage or environment error.
"""

import argparse
import collections
import contextlib
import datetime
import hashlib
import json
import os
import re
import struct
import sys
import unicodedata

ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))))
REG = os.path.join(ROOT, "docs", "research-scratch", "screenshot-registry")
SRC = os.path.join(ROOT, "docs", "game-screenshots")
INV = os.path.join(REG, "inventory.jsonl")
NOTES = os.path.join(REG, "notes-index.jsonl")
EVIDENCE = os.path.join(REG, "evidence.jsonl")
BATCHES = os.path.join(REG, "batches")
REFS_INDEX = os.path.join(REG, "refs-index.jsonl")
DISPOSITIONS = os.path.join(REG, "dispositions.jsonl")
CLUSTERS = os.path.join(ROOT, "docs", "design-research", "_scratch", "analysis", "color", "clusters.json")
SIGNATURES = os.path.join(ROOT, "docs", "design-research", "_scratch", "analysis", "scenarios", "signatures.json")
LEGACY = os.path.join(ROOT, "docs", "research-scratch", "screenshot-notes")
EXTRAS = os.path.join(ROOT, "docs", "research-scratch", "screenshot-notes-extras")

SCHEMA_VERSION = 1

STATES = [
    "not_reviewed",
    "documented_unverified",
    "verified",
    "partial",
    "skipped",
    "duplicate_candidate",
]

# A citation that does not resolve to an inventory record must carry one of
# these dispositions. It is a machine-readable "skip / point at the real frame"
# decision, never a silently-invented alias between two different captures.
DISPOSITION_KINDS = [
    "missing_no_copy",       # absent from disk and git; no way to recover the pixels
    "explicitly_missing",    # an analysis file marks the frame as missing on purpose
    "filename_typo",         # the cited name is a typo; resolves_to names the real frame
]

# Global scenarios the browser reports coverage for. A scenario with no captured
# frames is reported as `capture_required`, not as an empty search result.
SCENARIOS = ["URA Finale", "Unity Cup", "Trackblazer", "Our Grand Concert"]

# Fields the inventory pass owns. Everything else in a record is preserved on
# regeneration, which is what makes review progress survive a re-run.
DERIVED = [
    "schema_version", "sid", "ref", "filename", "path", "ext", "format", "sha256",
    "width", "height", "bytes", "dhash", "dhash_matches_cluster",
    "exact_duplicate_of", "exact_duplicate_group", "exact_duplicate_count",
    "cluster_index", "cluster_rep", "cluster_count", "cluster_size",
    "near_duplicate_cluster", "in_clusters", "in_signatures", "readable",
    "note_refs", "note_files", "category_from_notes", "screen_from_notes",
    "first_seen", "last_seen", "inventory_run",
]

FRAME_RE = re.compile(r"Screenshot (\d{4}-\d{2}-\d{2} \d{6})")
# A bare timestamp the length of a capture ref: `012428`, `232345`. Ambiguous on
# its own because the notes files use this form for cross-references.
BARE_RE = re.compile(r"(?<![\d.`])(0[12][0-9]{4})(?![\d.`])")
SHORT_RE = re.compile(r"(?<![\d`\w])(\d{6})(?![\d`\w])")
FIELD_RE = re.compile(r"^(?P<num>\d+)\.\s+\*\*(?P<label>[^*]+?):\*\*(?P<rest>.*)$")
NOTE_FILES = [
    "00-race-entry-screenshots.md",
    "01-training-screenshots.md",
    "02-career-progression-screenshots.md",
    "03-skill-selection-screenshots.md",
    "04-character-detail-screenshots.md",
    "05-menu-screenshots.md",
    "06-other-screenshots.md",
]
REPORT_FILES = ["PHASE-B-REPORT.md"]
EXTRA_FILES = [
    "Screenshot 2026-07-17 233324.md",
    "Screenshot 2026-07-18 002830.md",
    "Screenshot 2026-07-18 154126.md",
]


def today():
    return datetime.date.today().isoformat()


def _parse_kv(s):
    """Parse ``key=value;key2=value2`` into a dict. A bare token maps to True."""
    out = {}
    for part in (s or "").split(";"):
        part = part.strip()
        if not part:
            continue
        if "=" in part:
            k, v = part.split("=", 1)
            out[k.strip()] = v.strip()
        else:
            out[part] = True
    return out


def rel(path):
    """Repository-relative form of a path; falls back to the raw path when it
    sits on a different drive or is already relative (claimed note paths)."""
    try:
        return os.path.relpath(path, ROOT).replace(os.sep, "/")
    except ValueError:
        return path.replace(os.sep, "/")


def read_jsonl(path):
    out = []
    if not os.path.exists(path):
        return out
    with open(path, encoding="utf-8") as fh:
        for line in fh:
            line = line.strip()
            if line:
                out.append(json.loads(line))
    return out


def write_jsonl(path, rows, key):
    """Atomically write a JSONL file.

    The write is serialised by an OS-level lock on ``<path>.lock`` so two
    processes can never interleave their output on the same file.  If the
    current thread already holds the registry lock (see ``registry_lock``),
    the write proceeds without re-acquiring, since the caller's lock already
    covers this path when ``path`` is the inventory.

    A unique temporary name (``<path>.tmp.<pid>.<tid>``) is used so two
    writers can never collide on the temporary file.
    """
    st = _lock_state()
    if getattr(st, "fd", None) is not None and getattr(st, "path", None) == path + ".lock":
        _write_jsonl_unlocked(path, rows)
        return

    lock_path = path + ".lock"
    fd = _lock_fd(lock_path)
    try:
        _write_jsonl_unlocked(path, rows)
    finally:
        _unlock_fd(fd, lock_path)


def _write_jsonl_unlocked(path, rows):
    """Write the rows to a process-unique temporary file, then replace.

    ``os.replace`` is atomic on the same volume, so a reader either sees
    the old file or the new one.  The temporary name includes the pid and
    thread id so two writers that somehow reach this point concurrently
    still cannot share a temporary file.
    """
    import threading

    tmp = "%s.tmp.%d.%d" % (path, os.getpid(), threading.get_ident())
    try:
        with open(tmp, "w", encoding="utf-8", newline="\n") as fh:
            for r in rows:
                fh.write(json.dumps(r, ensure_ascii=False, sort_keys=True) + "\n")
        os.replace(tmp, path)
    finally:
        if os.path.exists(tmp):
            try:
                os.unlink(tmp)
            except OSError:
                pass


def write_json_atomic(path, obj, indent=1):
    """Atomically write a single JSON object via a process-unique temp file.

    Used for shared single-object files (batch manifests, dispositions) so a
    concurrent reader never sees a half-written document.
    """
    write_text_atomic(path, json.dumps(obj, indent=indent, ensure_ascii=False) + "\n")


def write_text_atomic(path, text):
    """Atomically write text via a process-unique temp file, then replace."""
    import threading

    tmp = "%s.tmp.%d.%d" % (path, os.getpid(), threading.get_ident())
    try:
        with open(tmp, "w", encoding="utf-8", newline="\n") as fh:
            fh.write(text)
        os.replace(tmp, path)
    finally:
        if os.path.exists(tmp):
            try:
                os.unlink(tmp)
            except OSError:
                pass


def _lock_fd(lock_path, timeout=30.0):
    """Open ``lock_path`` and acquire an exclusive OS-level lock on it.

    Returns the open file descriptor; the caller must keep it open for the
    lock to be held and pass it to ``_unlock_fd`` when done.

    The lock is an OS advisory lock (``msvcrt.locking`` on Windows,
    ``fcntl.flock`` on POSIX).  The operating system releases it
    automatically when the descriptor is closed or the process exits,
    so a crashed writer cannot leave a stale lock behind.  No PID
    liveness probe is used (``os.kill(pid, 0)`` terminates the target
    process on Windows, so it is unsafe here).

    Raises TimeoutError if the lock cannot be acquired within ``timeout``.
    """
    import time

    fd = os.open(lock_path, os.O_CREAT | os.O_RDWR)
    # Ensure the file is at least one byte so the byte-range lock is valid.
    if os.fstat(fd).st_size == 0:
        os.write(fd, b"0")
        os.lseek(fd, 0, os.SEEK_SET)

    deadline = time.monotonic() + timeout
    while True:
        try:
            if os.name == "nt":
                import msvcrt
                os.lseek(fd, 0, os.SEEK_SET)
                msvcrt.locking(fd, msvcrt.LK_NBLCK, 1)
            else:
                import fcntl
                fcntl.flock(fd, fcntl.LOCK_EX | fcntl.LOCK_NB)
            return fd
        except OSError:
            if time.monotonic() >= deadline:
                os.close(fd)
                raise TimeoutError(
                    "could not acquire %s within %.0f seconds; "
                    "another writer may be holding it" % (lock_path, timeout)
                )
            time.sleep(0.05)


def _unlock_fd(fd, lock_path):
    """Release the OS lock and close the descriptor.  The lock file is left
    on disk (harmless, and removing it would race with other waiters)."""
    try:
        if os.name == "nt":
            import msvcrt
            os.lseek(fd, 0, os.SEEK_SET)
            try:
                msvcrt.locking(fd, msvcrt.LK_UNLCK, 1)
            except OSError:
                pass
        else:
            import fcntl
            try:
                fcntl.flock(fd, fcntl.LOCK_UN)
            except OSError:
                pass
    finally:
        os.close(fd)


_LOCK_STATE = None


def _lock_state():
    """Per-thread record of the lock currently held, for re-entrancy."""
    global _LOCK_STATE
    if _LOCK_STATE is None:
        import threading
        _LOCK_STATE = threading.local()
    return _LOCK_STATE


@contextlib.contextmanager
def registry_lock(timeout=30.0):
    """Context manager that serialises the full read-modify-write on
    ``inventory.jsonl``.

    Usage::

        with registry_lock():
            inv = read_jsonl(INV)
            ... mutate inv ...
            write_jsonl(INV, inv, "filename")

    The lock covers the entire transaction, so concurrent readers see
    either the pre-mutation state or the post-mutation state, never a
    partially-applied one.

    The lock file is ``inventory.jsonl.lock``.  The lock is an OS-level
    advisory lock (``msvcrt.locking`` / ``fcntl.flock``) held on an open
    descriptor; the OS releases it automatically if the holder dies, so
    there is no stale-lock window.  Re-entrant within the same thread:
    ``write_jsonl`` checks whether this thread already holds the lock
    and skips re-acquiring it.
    """
    st = _lock_state()
    if getattr(st, "fd", None) is not None:
        # Re-entrant: this thread already holds the lock.
        yield
        return

    lock_path = INV + ".lock"
    fd = _lock_fd(lock_path, timeout)
    st.fd = fd
    st.path = lock_path
    try:
        yield
    finally:
        st.fd = None
        _unlock_fd(fd, lock_path)
def sha256_of(path):
    h = hashlib.sha256()
    with open(path, "rb") as fh:
        for chunk in iter(lambda: fh.read(1 << 20), b""):
            h.update(chunk)
    return h.hexdigest()


def png_dims(path):
    """Width, height from the PNG IHDR chunk; None if not a PNG."""
    with open(path, "rb") as fh:
        head = fh.read(24)
    if len(head) < 24 or head[:8] != b"\x89PNG\r\n\x1a\n":
        return None
    w, h = struct.unpack(">II", head[16:24])
    return w, h


def dhash(path, size=8):
    """Perceptual hash, same construction as the canonical cluster.py."""
    try:
        from PIL import Image
    except ImportError:
        return None
    with Image.open(path) as im:
        g = im.convert("L").resize((size + 1, size), Image.LANCZOS)
    px = list(g.get_flattened_data() if hasattr(g, "get_flattened_data") else g.getdata())
    bits = 0
    for y in range(size):
        for x in range(size):
            left = px[y * (size + 1) + x]
            right = px[y * (size + 1) + x + 1]
            bits = (bits << 1) | (1 if left > right else 0)
    return format(bits, "016x")


def load_json(path):
    with open(path, encoding="utf-8") as fh:
        return json.load(fh)


def load_clusters():
    """Map filename -> cluster record. Returns (map, ordered list, load error)."""
    if not os.path.exists(CLUSTERS):
        return {}, [], "missing: " + rel(CLUSTERS)
    data = load_json(CLUSTERS)
    cmap, ordered = {}, []
    for idx, c in enumerate(data):
        rec = {"index": idx, "rep": c.get("rep"), "count": c.get("count"),
               "dim": c.get("dim"), "size": len(c.get("members", []))}
        ordered.append(rec)
        for m in c.get("members", []):
            cmap[m] = rec
    return cmap, ordered, None


def load_signature_names():
    if not os.path.exists(SIGNATURES):
        return set()
    return {s.get("name") for s in load_json(SIGNATURES) if s.get("name")}


def new_record(ref):
    return {
        "schema_version": SCHEMA_VERSION,
        "sid": "",
        "ref": ref,
        "filename": "Screenshot %s.png" % ref,
        "path": "",
        "ext": ".png",
        "format": "png",
        "sha256": "",
        "width": None,
        "height": None,
        "bytes": 0,
        "dhash": None,
        "dhash_matches_cluster": None,
        "exact_duplicate_of": None,
        "exact_duplicate_group": None,
        "exact_duplicate_count": 1,
        "cluster_index": None,
        "cluster_rep": None,
        "cluster_count": None,
        "cluster_size": None,
        "near_duplicate_cluster": [],
        "in_clusters": False,
        "in_signatures": False,
        "readable": True,
        "note_refs": [],
        "note_files": [],
        "category_from_notes": None,
        "screen_from_notes": None,
        "first_seen": None,
        "last_seen": None,
        "inventory_run": None,
        "state": "not_reviewed",
        "scenario": None,
        "screen": None,
        "observations": [],
        "evidence": [],
        "review_history": [],
        "skipped_reason": None,
        "next_action": None,
        "confidence": None,
        "outstanding": [],
        "provenance": [],
    }


# ---------------------------------------------------------------- inventory

def _locked(fn):
    """Decorator: run a mutating command under the registry lock."""
    import functools

    @functools.wraps(fn)
    def wrapper(args):
        with registry_lock():
            return fn(args)
    return wrapper


@_locked
def cmd_inventory(args):
    cmap, ordered, cerr = load_clusters()
    sig = load_signature_names()

    disk = sorted(
        f for f in os.listdir(SRC) if os.path.isfile(os.path.join(SRC, f))
    ) if os.path.isdir(SRC) else []

    by_ref = {}
    for f in disk:
        m = re.match(r"^Screenshot (\d{4}-\d{2}-\d{2} \d{6})\.([A-Za-z0-9]+)$", f)
        by_ref[m.group(1)] = f if m else f

    # Files on disk whose name does not follow the capture convention are still
    # inventory records; they keep their own stem as the ref so nothing is lost.
    for f in disk:
        if f not in by_ref.values():
            stem = os.path.splitext(f)[0]
            by_ref.setdefault(stem, f)

    old_rows = read_jsonl(INV)
    old_by_ref = {r["ref"]: r for r in old_rows if r.get("ref")}
    old_by_sid = {r["sid"]: r for r in old_rows if r.get("sid")}
    used_old = set()
    by_hash, by_dhash = collections.defaultdict(list), collections.defaultdict(list)
    cname_by_index = collections.defaultdict(set)
    for m, c in cmap.items():
        cname_by_index[c["index"]].add(m)

    rows = []
    renamed = []
    for ref in sorted(by_ref):
        fname = by_ref[ref]
        fpath = os.path.join(SRC, fname)
        r = old_by_ref.get(ref)
        if r is None:
            r = new_record(ref)
        else:
            used_old.add(ref)

        recsha, w, h, dh, readable = "", None, None, None, True
        try:
            recsha = sha256_of(fpath)
        except OSError:
            readable = False
        if readable:
            try:
                dh = dhash(fpath)
            except (OSError, ValueError):
                # A perceptual hash failure must not mark the file unreadable.
                dh = None
            try:
                dims = png_dims(fpath)
                if not dims:
                    from PIL import Image
                    with Image.open(fpath) as im:
                        dims = im.size
                w, h = dims
            except (OSError, ValueError):
                w, h = None, None

        # Rename recovery: content that appears under a new name inherits the
        # record of the name it used to have. Only attempted for names that had
        # no record of their own, so an exact duplicate under two names never
        # clobbers either record.
        if recsha and old_by_ref.get(ref) is None and recsha[:12] in old_by_sid:
            carried = old_by_sid[recsha[:12]]
            if carried["ref"] != ref:
                r = carried
                used_old.add(carried["ref"])
                renamed.append("%s -> %s" % (carried["ref"], ref))

        r.update({
            "sid": recsha[:12] if recsha else "",
            "ref": ref,
            "filename": fname,
            "path": rel(fpath),
            "ext": os.path.splitext(fname)[1].lower(),
            "format": os.path.splitext(fname)[1][1:].lower() or "unknown",
            "sha256": recsha,
            "width": w,
            "height": h,
            "bytes": os.path.getsize(fpath) if readable and os.path.exists(fpath) else 0,
            "dhash": dh,
            "readable": readable,
            "in_clusters": fname in cmap,
            "in_signatures": fname in sig,
            "last_seen": today(),
            "inventory_run": today(),
        })
        first_seen = r.get("first_seen") or r.get("last_seen") or today()
        r["first_seen"] = first_seen
        if recsha:
            r["exact_duplicate_group"] = recsha[:16]
            by_hash[recsha].append(ref)
        if dh:
            by_dhash[dh].append(ref)
        c = cmap.get(fname)
        if c:
            r["cluster_index"] = c["index"]
            r["cluster_rep"] = c["rep"]
            r["cluster_count"] = c["count"]
            r["cluster_size"] = c["size"]
            r["near_duplicate_cluster"] = sorted(cname_by_index[c["index"]] - {fname})
            r["dhash_matches_cluster"] = (
                None if c["rep"] == fname else (c["rep"] in cmap and cmap[c["rep"]]["index"] == c["index"])
            )
        rows.append(r)

    for h, refs in by_hash.items():
        if len(refs) > 1:
            refs.sort()
            for ref in refs:
                rec = by_ref_ref(rows, ref)
                rec["exact_duplicate_count"] = len(refs)
                rec["exact_duplicate_of"] = refs[0] if refs[0] != ref else None

    rows.sort(key=lambda r: r["filename"])
    write_jsonl(INV, rows, "filename")

    stale = [k for k in old_by_ref if k not in by_ref and k not in used_old]
    print("inventory: %d files on disk, %d records written, %d records dropped as missing"
          % (len(disk), len(rows), len(stale)))
    if stale:
        print("  missing from disk: %s" % ", ".join(sorted(stale)[:12])
              + (" ..." if len(stale) > 12 else ""))
    if renamed:
        print("  renamed or moved, record carried forward: %s"
              % ", ".join(renamed[:12]) + (" ..." if len(renamed) > 12 else ""))
    if cerr:
        print("  WARN clusters.json unavailable: %s" % cerr)
    elif not cerr:
        only_in_clusters = sorted(set(cmap) - set(disk))
        only_on_disk = sorted(set(disk) - set(cmap))
        if only_in_clusters:
            print("  WARN in clusters.json but missing from disk (%d): %s"
                  % (len(only_in_clusters), ", ".join(only_in_clusters[:8])))
        if only_on_disk:
            print("  WARN on disk but absent from clusters.json (%d): %s"
                  % (len(only_on_disk), ", ".join(only_on_disk[:8])))
    print("  exact-duplicate groups: %d" % sum(1 for v in by_hash.values() if len(v) > 1))
    print("  near-duplicate clusters of size > 1: %d"
          % sum(1 for v in cname_by_index.values() if len(v) > 1))
    print("  clusters referenced: %d / %d" % (sum(1 for r in rows if r["in_clusters"]), len(rows)))
    print("  signatures referenced: %d / %d" % (sum(1 for r in rows if r["in_signatures"]), len(rows)))
    return 0


def by_ref_ref(rows, ref):
    for r in rows:
        if r["ref"] == ref:
            return r
    raise KeyError(ref)


# ---------------------------------------------------------------- notes

def parse_note_file(path, label):
    """Return a list of frame records parsed from one legacy note file.

    Merged notes nest each frame under `## Screenshot ...`; the extras use
    `# Screenshot ...` at top level. Both are honoured.
    """
    text = open(path, encoding="utf-8").read()
    out = []
    lines = text.splitlines()
    markers = [(i, m.group(0)) for i, m in
               ((i, re.match(r"^(#{1,2}) Screenshot (\d{4}-\d{2}-\d{2} \d{6})\s*$", ln))
                for i, ln in enumerate(lines)) if m]
    for n, (i, _) in enumerate(markers):
        start = i
        end = markers[n + 1][0] if n + 1 < len(markers) else len(lines)
        block = "\n".join(lines[start:end])
        head = lines[start].lstrip("# ").strip()
        m = re.match(r"^Screenshot (\d{4}-\d{2}-\d{2} \d{6})\s*$", head)
        if not m:
            continue
        body = "\n".join(lines[start + 1:end])
        fm = re.search(r"Frame:\s*`([^`]+)`\s*,\s*(\d+)\s*[×xby\s]+\s*(\d+)", body[:400])
        dm = re.search(r"Read (\d{4}-\d{2}-\d{2})", body[:400])
        fields = {}
        cur = None
        for line in lines[start + 1:end]:
            fm2 = FIELD_RE.match(line)
            if fm2:
                cur = fm2.group("label").strip()
                fields[cur] = [fm2.group("rest").strip()]
            elif cur and re.match(r"^\s{2,}\S", line) and line.strip():
                fields[cur].append(line.strip())
        fields = {k: "\n".join(v).strip() for k, v in fields.items()}
        out.append({
            "note_file": label,
            "note_path": rel(path),
            "ref": m.group(1),
            "frame_path_claim": fm.group(1) if fm else None,
            "dims_claim": [int(fm.group(2)), int(fm.group(3))] if fm else None,
            "read_date": dm.group(1) if dm else None,
            "fields": fields,
            "all_frame_refs": sorted(set(FRAME_RE.findall(block))),
            "short_refs": sorted(set(SHORT_RE.findall(block))),
        })
    return out


@_locked
def cmd_notes(args):
    if not os.path.isdir(LEGACY) and not os.path.isdir(EXTRAS):
        print("error: no legacy note directory found", file=sys.stderr)
        return 2

    inv = read_jsonl(INV)
    by_ref = {r["ref"]: r for r in inv}
    all_refs = sorted(by_ref)
    short_index = collections.defaultdict(list)
    for ref in all_refs:
        short_index[ref[11:17]].append(ref)

    files = []
    for f in NOTE_FILES:
        p = os.path.join(LEGACY, f)
        if os.path.exists(p):
            files.append((p, f))
    for f in REPORT_FILES:
        p = os.path.join(LEGACY, f)
        if os.path.exists(p):
            files.append((p, f))
    for f in EXTRA_FILES:
        p = os.path.join(EXTRAS, f)
        if os.path.exists(p):
            files.append((p, f))
    for extra in sorted(os.listdir(EXTRAS)):
        if extra.endswith(".md") and (os.path.join(EXTRAS, extra), extra) not in files:
            files.append((os.path.join(EXTRAS, extra), extra))

    seen = {}
    warns = []
    for path, label in files:
        for fr in parse_note_file(path, label):
            seen.setdefault(fr["ref"], []).append(fr)

    index = []
    for ref in sorted(seen):
        recs = seen[ref]
        invrec = by_ref.get(ref)
        inv_sha = invrec["sha256"] if invrec else None
        for fr in recs:
            claim = fr["frame_path_claim"]
            if claim:
                claimed = os.path.basename(claim)
            else:
                claimed = "Screenshot %s.png" % ref
            actual = invrec["filename"] if invrec else None
            if not invrec:
                match, why = "unresolved", "no such file on disk"
            elif actual != claimed:
                match, why = "unresolved", "note names %s, disk has %s" % (claimed, actual)
            elif inv_sha and fr["dims_claim"] and invrec["width"] and fr["dims_claim"] != [invrec["width"], invrec["height"]]:
                match, why = "probable", "note and file agree on identity; recorded dimensions disagree"
            else:
                match, why = "exact", "filename and path resolve; dimensions agree or note omitted them"
            shorts = sorted(set(s for s in fr["short_refs"] if s != ref[11:17]))
            resolved, unresolved_shorts, ambiguous_shorts = [], [], []
            for s in shorts:
                hits = short_index.get(s, [])
                if len(hits) == 1:
                    resolved.append(hits[0])
                elif len(hits) == 0:
                    unresolved_shorts.append(s)
                else:
                    ambiguous_shorts.append(s)
            index.append({
                "schema_version": SCHEMA_VERSION,
                "sid": invrec["sid"] if invrec else None,
                "ref": ref,
                "match": match,
                "match_reason": why,
                "claimed_path": rel(claim) if claim else None,
                "actual_filename": actual,
                "note_file": fr["note_file"],
                "note_path": fr["note_path"],
                "read_date": fr["read_date"],
                "dims_claim": fr["dims_claim"],
                "category": (fr["fields"].get("Category") or "").split("\n")[0] or None,
                "screen": (fr["fields"].get("Screen name") or "").split("\n")[0] or None,
                "field_count": len(fr["fields"]),
                "has_obstruction": "Snipping Tool" in json.dumps(fr["fields"]),
                "observation_chars": sum(len(v) for v in fr["fields"].values()),
                "referenced_frames": sorted(set(fr["all_frame_refs"]) - {ref}),
                "referenced_shorts_resolved": resolved,
                "referenced_shorts_ambiguous": ambiguous_shorts,
                "referenced_shorts_unresolved": unresolved_shorts,
            })
            if invrec:
                invrec.setdefault("note_refs", []).append({
                    "note_file": fr["note_file"], "note_path": fr["note_path"],
                    "match": match, "read_date": fr["read_date"], "category": index[-1]["category"],
                    "screen": index[-1]["screen"],
                })
                if fr["note_file"] not in invrec["note_files"]:
                    invrec["note_files"].append(fr["note_file"])
                if invrec.get("category_from_notes") is None and index[-1]["category"]:
                    invrec["category_from_notes"] = index[-1]["category"]
                if invrec.get("screen_from_notes") is None and index[-1]["screen"]:
                    invrec["screen_from_notes"] = index[-1]["screen"]

    # A note reference is a lead, not evidence: it promotes only to
    # documented_unverified, and never past a state already carried by a review.
    for r in inv:
        if not r.get("note_refs"):
            continue
        if r.get("state") in (None, "not_reviewed", "duplicate_candidate"):
            r["state"] = "documented_unverified"
        r.setdefault("provenance", [])
        if "legacy-notes" not in [p.get("kind") for p in r["provenance"]]:
            r["provenance"].append({
                "kind": "legacy-notes", "date": today(),
                "note_files": sorted(r["note_files"]),
                "detail": "reconciled from historical notes; not visually verified in this registry",
            })

    file_stats = []
    for path, label in files:
        secs = [i for i in index if i["note_file"] == label]
        file_stats.append({
            "note_file": label,
            "note_path": rel(path),
            "sections": len(secs),
            "matched": len({s["ref"] for s in secs if s["match"] in ("exact", "probable", "ambiguous")}),
            "unresolved": len({s["ref"] for s in secs if s["match"] == "unresolved"}),
            "note_chars": sum(s.get("observation_chars", 0) for s in secs),
        })
    for fs in file_stats:
        if fs["sections"] == 0:
            warns.append("%s: no `Screenshot` sections found" % fs["note_file"])

    index.sort(key=lambda r: (r["ref"], r["note_file"]))
    write_jsonl(NOTES, index, "ref")
    write_jsonl(INV, inv, "filename")
    write_jsonl(os.path.join(REG, "notes-files.jsonl"), file_stats, "note_file")

    counts = collections.Counter(r["match"] for r in index)
    print("notes: %d note files parsed (%d with sections), %d references reconciled"
          % (len(files), sum(1 for f in file_stats if f["sections"] > 0), len(index)))
    for k in sorted(counts):
        print("  %s: %d" % (k, counts[k]))
    annotated = {r["ref"] for r in index}
    print("  frames with a note: %d of %d" % (len(annotated), len(inv)))
    print("  unmatched note references: %s" % (
        [r["ref"] for r in index if r["match"] == "unresolved"] or "none"))
    for w in warns:
        print("  WARN %s" % w)
    return 0


# ---------------------------------------------------------------- validate

def cmd_validate(args):
    fails, warns = [], []
    try:
        inv = read_jsonl(INV)
    except ValueError as e:
        fails.append("inventory.jsonl is malformed: %s" % e)
        inv = []
    try:
        notes = read_jsonl(NOTES)
    except ValueError as e:
        fails.append("notes-index.jsonl is malformed: %s" % e)
        notes = []

    if not inv:
        for f in fails:
            print("FAIL  %s" % f)
        if fails:
            print("validate: 0 records, 0 note refs, %d fails, %d warns" % (len(fails), len(warns)))
            return 1
        print("error: %s is empty or missing; run `inventory` first" % rel(INV), file=sys.stderr)
        return 2

    dispos_refs = {d["ref"] for d in read_dispositions()}
    seen = set()
    seen_refs = set()
    for r in inv:
        sid = r.get("sid") or ""
        if sid in seen:
            fails.append("duplicate sid %s" % sid)
        seen.add(sid)
        if not r.get("ref"):
            fails.append("record without ref")
        ref = r.get("ref") or ""
        if ref in seen_refs:
            fails.append("duplicate ref %s" % ref)
        seen_refs.add(ref)
        if r.get("state") not in STATES:
            fails.append("%s: invalid state %r" % (r["ref"], r.get("state")))
        p = r.get("path") or ""
        if p and not os.path.isabs(p):
            ap = os.path.join(ROOT, p)
            if not os.path.exists(ap) and r["ref"] not in dispos_refs:
                fails.append("%s: path does not exist and has no missing-file disposition: %s"
                             % (r["ref"], p))
        sha = r.get("sha256") or ""
        if sha and r.get("readable"):
            ap = os.path.join(ROOT, p)
            if os.path.exists(ap) and sha256_of(ap) != sha:
                fails.append("%s: sha256 changed on disk" % r["ref"])
        if r.get("state") in ("verified", "partial") and not r.get("review_history"):
            fails.append("%s: state %s without review_history" % (r["ref"], r["state"]))
        if r.get("state") == "skipped" and not r.get("skipped_reason"):
            fails.append("%s: skipped without skipped_reason" % r["ref"])
        if r.get("state") == "verified" and not r.get("observations"):
            fails.append("%s: verified without an observation" % r["ref"])
        elif r.get("state") == "partial" and not r.get("observations"):
            warns.append("%s: partial with no observations" % r["ref"])
        if r.get("state") == "partial" and not r.get("outstanding"):
            warns.append("%s: partial without an outstanding question" % r["ref"])
        if r.get("state") == "skipped" and not r.get("next_action"):
            warns.append("%s: skipped without a next_action" % r["ref"])
        if r.get("confidence") is not None and r["confidence"] not in ("low", "medium", "high"):
            fails.append("%s: invalid confidence %r" % (r["ref"], r["confidence"]))
        for h in r.get("review_history", []):
            if h.get("state") not in STATES:
                fails.append("%s: review_history invalid state %r" % (r["ref"], h.get("state")))
            if h.get("date") and not re.match(r"^\d{4}-\d{2}-\d{2}$", h["date"]):
                fails.append("%s: review_history bad date %r" % (r["ref"], h["date"]))
            if not h.get("by"):
                fails.append("%s: review_history entry without an observer" % r["ref"])
        for o in r.get("observations", []):
            if not o.get("by"):
                fails.append("%s: observation without an observer" % r["ref"])
            if not o.get("text"):
                fails.append("%s: observation without text" % r["ref"])
            for f in ("screen_type", "scenario", "career_phase", "follow_up_question"):
                if f in o and not isinstance(o[f], str):
                    fails.append("%s: observation %s must be a string" % (r["ref"], f))
            for f in ("visible_mechanics", "limitations"):
                if f in o and not (isinstance(o[f], list) and all(isinstance(x, str) for x in o[f])):
                    fails.append("%s: observation %s must be a list of strings" % (r["ref"], f))
            if "direct_values" in o and not isinstance(o["direct_values"], dict):
                fails.append("%s: observation direct_values must be an object" % r["ref"])
        for e in r.get("evidence", []):
            if not e.get("claim"):
                fails.append("%s: evidence without a claim" % r["ref"])
            if e.get("kind") not in ("observed", "inferred"):
                fails.append("%s: evidence with invalid kind %r" % (r["ref"], e.get("kind")))
            if not e.get("supports"):
                warns.append("%s: evidence claim with no supporting frame" % r["ref"])
            for t in e.get("supports", []):
                if t not in {x["ref"] for x in inv}:
                    fails.append("%s: evidence cites unknown frame %r" % (r["ref"], t))
        if r.get("exact_duplicate_count", 1) > 1 and r.get("state") == "verified":
            warns.append("%s: verified despite being an exact duplicate" % r["ref"])

    by_ref = {r["ref"]: r for r in inv}
    for n in notes:
        if n["match"] not in ("exact", "probable", "ambiguous", "unresolved"):
            fails.append("note %s/%s: invalid match class %r" % (n["ref"], n["note_file"], n["match"]))
        if not os.path.exists(os.path.join(ROOT, n["note_path"])):
            fails.append("note %s: note_path missing on disk" % n["note_path"])
        if n.get("category") is None and n["match"] in ("exact", "probable"):
            warns.append("note %s: matched but carries no category" % n["ref"])

    # documented_unverified is only legitimate when a note actually resolves to
    # the frame; an unresolved note alone is not evidence of a note.
    resolved = {n["ref"] for n in notes if n["match"] in ("exact", "probable", "ambiguous")}
    for r in inv:
        if r.get("state") == "documented_unverified" and r["ref"] not in resolved:
            fails.append("%s: documented_unverified with no resolved note reference" % r["ref"])

    on_disk = set()
    if os.path.isdir(SRC):
        for f in os.listdir(SRC):
            m = re.match(r"^Screenshot (\d{4}-\d{2}-\d{2} \d{6})\.[A-Za-z0-9]+$", f)
            on_disk.add(m.group(1) if m else os.path.splitext(f)[0])
    missing = sorted(on_disk - set(by_ref))
    orphaned = sorted(set(by_ref) - on_disk)
    for m in missing:
        fails.append("frame on disk with no inventory record: %s" % m)
    for o in orphaned:
        warns.append("inventory record with no file on disk: %s" % o)

    # Evidence registry integrity.
    ev = read_jsonl(EVIDENCE)
    ev_ids = set()
    for e in ev:
        eid = e.get("eid")
        if not eid:
            fails.append("evidence.jsonl row without an eid")
        elif eid in ev_ids:
            fails.append("duplicate eid %s" % eid)
        ev_ids.add(eid)
        if e.get("kind") not in ("observed", "inferred"):
            fails.append("%s: invalid kind %r" % (eid, e.get("kind")))
        if not e.get("claim"):
            fails.append("%s: no claim text" % eid)
        if not e.get("supports"):
            fails.append("%s: no supporting frames" % eid)
        if e.get("confidence") and e["confidence"] not in ("low", "medium", "high"):
            fails.append("%s: invalid confidence %r" % (eid, e["confidence"]))
        for s in e.get("supports", []):
            if s not in {r["ref"] for r in inv}:
                fails.append("%s: supports unknown frame %r" % (eid, s))
        status = e.get("status") or e.get("kind")
        if status not in ("inferred", "observed", "confirmed"):
            fails.append("%s: invalid status %r" % (eid, status))
        for s in e.get("independent_supports", []):
            if s not in e.get("supports", []):
                fails.append("%s: independent_supports %s is not in supports" % (eid, s))
        if status == "confirmed":
            clusters = {(by_ref[s].get("cluster_rep") or s)
                        for s in e.get("supports", []) if s in by_ref}
            if len(clusters) < 2:
                fails.append("%s: a confirmed claim needs >=2 independent capture contexts "
                             "(near-duplicates of one screen do not confirm)" % eid)

    # Back-links in the inventory must not name a missing evidence id.
    for r in inv:
        for b in r.get("evidence", []):
            if b.get("eid") not in ev_ids:
                fails.append("%s: back-links unknown eid %r" % (r["ref"], b.get("eid")))

    # Reverse citation index: stale if the corpus grew and nobody rebuilt it.
    refs_index = REFS_INDEX
    refs_rows = []
    if not os.path.exists(refs_index):
        warns.append("refs-index.jsonl missing; run `refs` to build the citation index")
    else:
        refs_rows = read_jsonl(refs_index)
        cited = {r["ref"] for r in refs_rows if r.get("in_inventory")}
        if not cited:
            warns.append("refs-index.jsonl cites no inventoried frame; it is empty or stale")

        # Every unresolved citation must carry a documented disposition. A
        # resolvable citation maps to the exact inventory ref; a typo points
        # at the real frame; the rest are machine-readable skips.
        dispos = {d["ref"]: d for d in read_dispositions()}
        unresolved = {r["ref"] for r in refs_rows if not r.get("in_inventory")}
        for ref in sorted(unresolved):
            d = dispos.get(ref)
            if not d:
                fails.append("citation %s has no inventory record and no disposition; "
                             "run `disposition` to record one" % ref)
                continue
            if d.get("kind") not in DISPOSITION_KINDS:
                fails.append("disposition %s: invalid kind %r" % (ref, d.get("kind")))
            if d.get("kind") == "filename_typo":
                if not d.get("resolves_to"):
                    fails.append("disposition %s: filename_typo without resolves_to" % ref)
                elif d["resolves_to"] not in by_ref:
                    fails.append("disposition %s: resolves_to %s is not an inventory frame"
                                 % (ref, d["resolves_to"]))
            elif d.get("resolves_to"):
                warns.append("disposition %s: kind %s should not set resolves_to"
                             % (ref, d["kind"]))
        # A disposition targeting a ref that now resolves is stale.
        for ref in sorted(dispos):
            if ref in by_ref:
                warns.append("disposition %s targets a ref that now resolves; "
                             "remove it with `disposition --remove`" % ref)

    # Append-only audit trail consistency: it may never shrink below the
    # per-record history, and every entry must name a known frame.
    log_path = os.path.join(REG, "review-log.jsonl")
    if os.path.exists(log_path):
        log_rows = read_jsonl(log_path)
        history_total = sum(len(r.get("review_history", [])) for r in inv)
        if len(log_rows) < history_total:
            warns.append("review-log.jsonl holds %d entries but records carry %d review_history "
                         "entries; the log is stale" % (len(log_rows), history_total))
        for row in log_rows:
            if row.get("ref") not in by_ref:
                warns.append("review-log.jsonl cites unknown frame %r" % row.get("ref"))
            if row.get("state") not in STATES:
                fails.append("review-log.jsonl invalid state %r" % row.get("state"))
            if not row.get("by"):
                fails.append("review-log.jsonl entry without an observer")

        # Every per-record history entry must have a matching append-only row.
        # Match on sid, which survives a rename, rather than the mutable ref.
        log_keys = collections.Counter(
            (row.get("sid"), row.get("state"), row.get("by"), row.get("date"))
            for row in log_rows)
        hist_keys = collections.Counter()
        for r in inv:
            for h in r.get("review_history", []):
                hist_keys[(r.get("sid"), h.get("state"), h.get("by"), h.get("date"))] += 1
        orphan_hist = sum(v for k, v in hist_keys.items() if log_keys.get(k, 0) < v)
        if orphan_hist:
            fails.append("%d review_history entries have no matching review-log row" % orphan_hist)

    # Every batch id named in a review_history entry must resolve to a manifest.
    known_batches = set()
    if os.path.isdir(BATCHES):
        for fn in os.listdir(BATCHES):
            if fn.endswith(".json"):
                known_batches.add(fn[:-5])
    for r in inv:
        for h in r.get("review_history", []):
            b = h.get("batch")
            if b and b not in known_batches:
                warns.append("%s: review_history names unknown batch %s" % (r["ref"], b))

    # A claim's supporting frames should be visually reviewed, not merely present.
    for e in ev:
        for s in e.get("supports", []):
            rr = by_ref.get(s)
            if rr and rr.get("state") not in ("verified", "partial"):
                warns.append("%s: support frame %s is not reviewed (%s)"
                             % (e.get("eid"), s, rr.get("state")))

    if cerr_of(inv):
        warns.append(cerr_of(inv))

    # Batch manifests must be self-consistent: unique ids, and a summary that
    # matches the deterministic roll-up of the manifest, log, and inventory.
    if os.path.isdir(BATCHES):
        for fn in sorted(os.listdir(BATCHES)):
            if not fn.endswith(".json"):
                continue
            bid = fn[:-5]
            try:
                manifest = json.load(open(os.path.join(BATCHES, fn), encoding="utf-8"))
            except (OSError, ValueError) as e:
                fails.append("batch %s: unreadable manifest (%s)" % (bid, e))
                continue
            if not isinstance(manifest, dict) or ("id" not in manifest and "ids" not in manifest):
                # A candidate manifest or other non-batch JSON; not a batch.
                continue
            mids = manifest.get("ids", [])
            if len(mids) != len(set(mids)):
                dups = sorted(i for i in set(mids) if mids.count(i) > 1)
                fails.append("batch %s: duplicate frame ids %s" % (bid, dups))
            computed = compute_batch_summary(bid)
            if computed is None:
                continue
            if manifest.get("summary") != computed:
                fails.append("batch %s: summary stale; run `batch-summary --id %s`" % (bid, bid))

    # Generated coverage totals must match the inventory.
    cov_path = os.path.join(REG, "COVERAGE.md")
    counts = collections.Counter(r["state"] for r in inv)
    if os.path.exists(cov_path):
        cov = open(cov_path, encoding="utf-8").read()
        stale = []
        for st in STATES:
            m = re.search(r"\| `%s` \| (\d+) \|" % re.escape(st), cov)
            if m and int(m.group(1)) != counts.get(st, 0):
                stale.append("%s (%s vs %d)" % (st, m.group(1), counts.get(st, 0)))
        if stale:
            fails.append("COVERAGE.md is stale: %s; run `report`" % ", ".join(stale))
    else:
        warns.append("COVERAGE.md missing; run `report`")

    # No malformed JSONL and no duplicate keys in any registry JSONL file.
    for path, label in [(INV, "inventory.jsonl"), (NOTES, "notes-index.jsonl"),
                        (EVIDENCE, "evidence.jsonl"), (REFS_INDEX, "refs-index.jsonl"),
                        (DISPOSITIONS, "dispositions.jsonl"), (log_path, "review-log.jsonl")]:
        if not os.path.exists(path):
            continue
        for lineno, line in enumerate(open(path, encoding="utf-8"), 1):
            if not line.strip():
                continue
            dup = []

            def hook(pairs, _dup=dup):
                keys = [k for k, _ in pairs]
                for k in keys:
                    if keys.count(k) > 1 and k not in _dup:
                        _dup.append(k)
                return dict(pairs)

            try:
                json.loads(line, object_pairs_hook=hook)
            except ValueError as e:
                fails.append("%s line %d: malformed JSON (%s)" % (label, lineno, e))
            if dup:
                fails.append("%s line %d: duplicate keys %s" % (label, lineno, sorted(dup)))

    for w in warns:
        print("WARN  %s" % w)
    for f in fails:
        print("FAIL  %s" % f)
    print("validate: %d records, %d note refs, %d fails, %d warns"
          % (len(inv), len(notes), len(fails), len(warns)))
    return 1 if fails else 0


def cerr_of(inv):
    msgs = set()
    for r in inv:
        if not r.get("in_clusters") and r.get("readable"):
            msgs.add("frame not present in clusters.json: %s" % r["filename"])
    return "; ".join(sorted(msgs)[:6]) if msgs else ""


# ---------------------------------------------------------------- report

@_locked
def cmd_report(args):
    inv = read_jsonl(INV)
    notes = read_jsonl(NOTES)
    if not inv:
        print("error: no inventory; run `inventory` first", file=sys.stderr)
        return 2

    st = collections.Counter(r["state"] for r in inv)
    cats = collections.Counter(r.get("category_from_notes") or "unassigned" for r in inv)
    dims = collections.Counter("%sx%s" % (r.get("width"), r.get("height")) for r in inv)
    unmatched = [n for n in notes if n["match"] == "unresolved"]
    no_notes = [r for r in inv if not r.get("note_refs")]
    exact_dupes = collections.Counter(r["exact_duplicate_group"] for r in inv
                                      if r.get("exact_duplicate_count", 1) > 1)
    dup_frames = sorted(r["ref"] for r in inv if r.get("exact_duplicate_count", 1) > 1)

    note_files_src = read_jsonl(os.path.join(REG, "notes-files.jsonl"))
    if not note_files_src:
        note_files_src = [{"note_file": n["note_file"], "note_path": n["note_path"],
                           "sections": sum(1 for m in notes if m["note_file"] == n["note_file"]),
                           "matched": 0, "unresolved": 0, "note_chars": 0}
                          for n in sorted({n["note_file"] for n in notes}, key=lambda x: x)]
        note_files_src = sorted({f["note_file"]: f for f in note_files_src}.values(),
                                key=lambda f: f["note_file"])
    note_files = [f["note_file"] for f in note_files_src]
    annotated = len({n["ref"] for n in notes})
    dupes = sum(r.get("exact_duplicate_count", 1) - 1 for r in inv)

    L = []
    A = L.append
    A("# Screenshot corpus coverage")
    A("")
    A("Generated `%s` by `tools/screenshots.py report`. Counts come from "
      "`inventory.jsonl` and `notes-index.jsonl`, never from this text." % today())
    A("")
    A("## Corpus")
    A("")
    A("| Measure | Value |")
    A("| --- | --- |")
    A("| Original screenshots on disk | %d |" % len(inv))
    A("| Readable | %d |" % sum(1 for r in inv if r.get("readable")))
    A("| Unreadable or unsupported | %d |" % sum(1 for r in inv if not r.get("readable")))
    A("| Unique content (SHA-256) | %d |" % len({r["sha256"] for r in inv if r["sha256"]}))
    A("| Exact duplicate frames | %d |" % len(dup_frames))
    A("| Exact duplicate bytes redundant | %d |" % dupes)
    A("| Frames in the canonical clusters.json | %d |" % sum(1 for r in inv if r.get("in_clusters")))
    A("| Frames with no cluster entry | %d |" % sum(1 for r in inv if not r.get("in_clusters")))
    A("| Distinct dimensions | %d |" % len(dims))
    A("| Capture span | %s to %s |" % (min(r["ref"] for r in inv), max(r["ref"] for r in inv)))
    A("")
    A("## Review states")
    A("")
    A("| State | Count | Share |")
    A("| --- | --- | --- |")
    for s in STATES:
        n = st.get(s, 0)
        A("| `%s` | %d | %.1f%% |" % (s, n, 100.0 * n / len(inv) if inv else 0))
    A("| **total** | **%d** | |" % len(inv))
    A("")
    A("`documented_unverified` counts frames a legacy note names. It is not review coverage.")
    A("")
    A("## Note reconciliation")
    A("")
    A("| Measure | Value |")
    A("| --- | --- |")
    A("| Legacy note files parsed | %d |" % len(note_files))
    A("| Note references reconciled | %d |" % len(notes))
    A("| Frames with at least one note | %d |" % annotated)
    A("| Frames with no note at all | %d |" % len(no_notes))
    A("| Unresolved note references | %d |" % len(unmatched))
    A("")
    A("Match classes: %s" % ", ".join("`%s` %d" % (k, v)
                                       for k, v in sorted(collections.Counter(n["match"] for n in notes).items())))
    A("")
    A("### Legacy note files")
    A("")
    A("| Note file | Sections | Matched | Unresolved | Observation chars |")
    A("| --- | --- | --- | --- | --- |")
    for f in note_files_src:
        A("| `%s` | %d | %d | %d | %d |" % (f["note_file"], f.get("sections", 0),
                                              f.get("matched", 0), f.get("unresolved", 0),
                                              f.get("note_chars", 0)))
    A("")
    A("## Categories from notes")
    A("")
    A("| Category | Frames |")
    A("| --- | --- |")
    for k, v in cats.most_common():
        A("| %s | %d |" % (k, v))
    A("")
    A("## Dimensions")
    A("")
    A("| Dimensions | Frames |")
    A("| --- | --- |")
    for k, v in dims.most_common(12):
        A("| %s | %d |" % (k, v))
    A("")
    A("## Near-duplicate clusters")
    A("")
    groups = {}
    for c in inv:
        idx = c.get("cluster_index")
        if idx is None:
            continue
        g = groups.setdefault(idx, {"size": 0, "rep": c.get("cluster_rep")})
        g["size"] = max(g["size"], c.get("cluster_size") or 0)
        if not g["rep"]:
            g["rep"] = c.get("cluster_rep")
    big = sorted(((g["size"], idx, g["rep"]) for idx, g in groups.items()),
                 key=lambda t: (-t[0], t[1]))[:10]
    A("From the canonical `clusters.json` (dHash, Hamming 6, 60-frame rolling window). "
      "A cluster is a perceptual group, not a byte identity: this corpus has **0 exact "
      "duplicate groups**, so every cluster below holds visually similar but distinct frames.")
    A("")
    A("| Cluster | Size | Representative |")
    A("| --- | --- | --- |")
    for size, idx, rep in big:
        A("| %s | %d | `%s` |" % (idx, size, (rep or "").replace("Screenshot ", "").replace(".png", "")))
    A("")
    A("Total distinct clusters on this corpus: %d." % len(groups))
    A("")
    A("## Unmatched note references")
    A("")
    if unmatched:
        for n in unmatched:
            A("- `%s` in `%s`: %s" % (n["ref"], n["note_file"], n["match_reason"]))
    else:
        A("None.")
    A("")
    A("## Outstanding coverage")
    A("")
    A("| Bucket | Count |")
    A("| --- | --- |")
    A("| `not_reviewed` | %d |" % st.get("not_reviewed", 0))
    A("| `documented_unverified` | %d |" % st.get("documented_unverified", 0))
    A("| `partial` | %d |" % st.get("partial", 0))
    A("| `skipped` | %d |" % st.get("skipped", 0))
    A("| `duplicate_candidate` | %d |" % st.get("duplicate_candidate", 0))
    A("| `verified` | %d |" % st.get("verified", 0))
    A("")
    A("The queue is generated, not typed: `tools/screenshots.py queue`.")
    A("")
    out = os.path.join(REG, "COVERAGE.md")
    write_text_atomic(out, "\n".join(L) + "\n")
    print(open(out, encoding="utf-8").read())
    return 0


# ---------------------------------------------------------------- queue

PRIORITY = [
    (1, "no note at all", lambda r: not r.get("note_refs")),
    (2, "unresolved note reference", lambda r: any(n["match"] == "unresolved" for n in r.get("note_refs", []))),
    (3, "partial", lambda r: r.get("state") == "partial"),
    (4, "skipped", lambda r: r.get("state") == "skipped"),
    (5, "duplicate candidate", lambda r: r.get("state") == "duplicate_candidate"),
    (6, "generic note only", lambda r: r.get("state") == "documented_unverified"
                                       and r.get("note_refs")
                                       and all(n.get("observation_chars", 0) < 900 for n in r["note_refs"])),
    (7, "documented but unverified", lambda r: r.get("state") == "documented_unverified"),
    (8, "not reviewed", lambda r: r.get("state") == "not_reviewed"),
]


def queue_rows(inv):
    rows = []
    for r in inv:
        if r.get("state") == "verified":
            continue
        for rank, label, pred in PRIORITY:
            if pred(r):
                rows.append((rank, label, r))
                break
    full = [x for x in rows if (x[2].get("width") == 1920 and x[2].get("height") == 1080)]
    rows.sort(key=lambda x: (x[0], 0 if x in full else 1, x[2]["ref"]))
    return rows


def cmd_queue(args):
    inv = read_jsonl(INV)
    rows = queue_rows(inv)
    if args.state:
        rows = [x for x in rows if x[2]["state"] == args.state]
    for rank, label, r in rows[:args.limit]:
        extra = ""
        if r.get("category_from_notes"):
            extra += " [%s]" % r["category_from_notes"]
        if r.get("scenario"):
            extra += " <%s>" % r["scenario"]
        if r.get("skipped_reason"):
            extra += " SKIP: %s" % r["skipped_reason"]
        if r.get("outstanding"):
            extra += " Q: %s" % "; ".join(r["outstanding"][:2])
        print("%2d  %-22s %-19s %-10s %-7d  %s%s"
              % (rank, label, r["ref"], r["state"], r.get("bytes", 0) // 1024, r["sid"], extra))
    print("-- %d rows shown of %d outstanding" % (min(args.limit, len(rows)), len(rows)))
    return 0


# ---------------------------------------------------------------- sheet

def cmd_sheet(args):
    inv = {r["ref"]: r for r in read_jsonl(INV)}
    from PIL import Image, ImageDraw

    ids = []
    if args.ids:
        ids = args.ids.split(",")
    elif args.state:
        ids = [r["ref"] for r in read_jsonl(INV) if r["state"] == args.state]
    else:
        ids = [r["ref"] for r in queue_rows(read_jsonl(INV))][:args.limit]

    missing = [i for i in ids if i not in inv]
    if missing:
        print("error: unknown ids: %s" % ", ".join(missing), file=sys.stderr)
        return 2

    cols, cell_w, cell_h, label = 5, 384, 216, 20
    per = cols * args.rows
    ids = ids[:per * 12]

    mapping = []
    n = 0
    sheets = (len(ids) + per - 1) // per if ids else 1
    for s in range(sheets):
        chunk = ids[s * per:(s + 1) * per]
        img = Image.new("RGB", (cols * cell_w, args.rows * (cell_h + label)), (18, 18, 22))
        d = ImageDraw.Draw(img)
        for k, ref in enumerate(chunk):
            r = inv[ref]
            p = os.path.join(ROOT, r["path"])
            x = (k % cols) * cell_w
            y = (k // cols) * (cell_h + label)
            try:
                with Image.open(p) as im:
                    im = im.convert("RGB")
                    im.thumbnail((cell_w - 6, cell_h - 6), Image.LANCZOS)
                img.paste(im, (x + (cell_w - 6 - im.size[0]) // 2, y + (cell_h - 6 - im.size[1]) // 2))
            except OSError:
                d.text((x + 6, y + 8), "(unreadable)", fill=(220, 120, 120))
            d.rectangle([x, y + cell_h, x + cell_w - 2, y + cell_h + label - 2], outline=(110, 110, 130))
            tag = "%s %s %dx%d" % (ref[5:], r["state"][:4], r.get("width") or 0, r.get("height") or 0)
            d.text((x + 4, y + cell_h + 5), tag)
            mapping.append({"cell": n, "sheet": s + 1, "col": k % cols + 1,
                            "row": k // cols + 1, "ref": ref, "sid": r["sid"],
                            "state": r["state"]})
            n += 1
        out = os.path.join(REG, "sheets", "sheet_%d_of_%d_%s.png" % (s + 1, sheets, args.state or "queue"))
        os.makedirs(os.path.dirname(out), exist_ok=True)
        img.save(out)
        print("wrote %s (%d cells)" % (rel(out), len(chunk)))
    mpath = os.path.join(REG, "sheets", "sheet_%s.json" % today())
    with open(mpath, "w", encoding="utf-8") as fh:
        json.dump(mapping, fh, indent=1)
    print("wrote %s (%d cells, every cell mapped to a sid)" % (rel(mpath), len(mapping)))
    return 0


# ---------------------------------------------------------------- show

def cmd_show(args):
    inv = read_jsonl(INV)
    rows = inv
    if args.state:
        rows = [r for r in rows if r["state"] in (args.state if isinstance(args.state, list) else [args.state])]
    if args.category:
        rows = [r for r in rows if (r.get("category_from_notes") or "").startswith(args.category)]
    if args.scenario:
        rows = [r for r in rows if r.get("scenario") == args.scenario]
    if args.note:
        rows = [r for r in rows if any(n["note_file"] == args.note for n in r.get("note_refs", []))]
    if args.full:
        rows = [r for r in rows if r.get("width") == 1920 and r.get("height") == 1080]
    if args.no_notes:
        rows = [r for r in rows if not r.get("note_refs")]
    if args.ids:
        want = set(args.ids.split(","))
        rows = [r for r in rows if r["ref"] in want]
    rows = rows[:args.limit]
    for r in rows:
        if args.json:
            print(json.dumps(r, ensure_ascii=False, sort_keys=True))
        else:
            print("%-19s %-10s %-12s %-10s %s%s" % (
                r["ref"], r["state"], r.get("category_from_notes") or "-",
                "%sx%s" % (r.get("width"), r.get("height")), r["sid"],
                " notes=%d" % len(r.get("note_refs", [])) if r.get("note_refs") else ""))
    print("-- %d shown" % len(rows))
    return 0


# ---------------------------------------------------------------- review

def cmd_review(args):
    with registry_lock():
        inv = read_jsonl(INV)
        by_ref = {r["ref"]: r for r in inv}
        ids = [i.strip() for i in args.ids.split(",") if i.strip()]
        unknown = [i for i in ids if i not in by_ref]
        if unknown:
            print("error: unknown ids: %s" % ", ".join(unknown), file=sys.stderr)
            return 2
        if args.state == "verified" and not args.note:
            print("error: `verified` requires --note; a script run never marks a frame verified",
                  file=sys.stderr)
            return 2
        if args.state == "skipped" and not args.reason:
            print("error: `skipped` requires --reason", file=sys.stderr)
            return 2
        changed = 0
        for i in ids:
            r = by_ref[i]
            r["review_history"].append({
                "date": today(), "state": args.state, "by": args.observer,
                "batch": args.batch, "confidence": args.confidence,
                "note": args.note,
            })
            if args.note:
                obs = {
                    "date": today(), "by": args.observer, "batch": args.batch, "text": args.note,
                }
                # Optional structured fields. Nullable: a field is present only
                # when the screenshot establishes it. The free-text `text`
                # above is always kept, so old readers still work.
                if getattr(args, "screen_type", None):
                    obs["screen_type"] = args.screen_type
                if args.scenario:
                    obs["scenario"] = args.scenario
                if getattr(args, "career_phase", None):
                    obs["career_phase"] = args.career_phase
                if getattr(args, "mechanics", None):
                    obs["visible_mechanics"] = [x.strip() for x in args.mechanics.split(";") if x.strip()]
                if getattr(args, "values", None):
                    obs["direct_values"] = _parse_kv(args.values)
                if getattr(args, "limitations", None):
                    obs["limitations"] = [x.strip() for x in args.limitations.split(";") if x.strip()]
                if getattr(args, "follow_up", None):
                    obs["follow_up_question"] = args.follow_up
                r.setdefault("observations", []).append(obs)
            r["state"] = args.state
            r["confidence"] = args.confidence or r.get("confidence")
            r["skipped_reason"] = args.reason if args.state == "skipped" else r.get("skipped_reason")
            r["next_action"] = args.next_action
            if args.scenario:
                r["scenario"] = args.scenario
            if args.category:
                r["screen"] = args.category
            if args.questions:
                r["outstanding"] = [q.strip() for q in args.questions.split(";") if q.strip()]
            changed += 1
        write_jsonl(INV, inv, "filename")
    # Append-only audit trail: a state change is recoverable even if a record is
    # edited by hand. This file is never rewritten or truncated by the tool.
    with open(os.path.join(REG, "review-log.jsonl"), "a", encoding="utf-8", newline="\n") as fh:
        for i in ids:
            fh.write(json.dumps({
                "date": today(), "ref": i, "sid": by_ref[i].get("sid"),
                "state": args.state, "by": args.observer, "batch": args.batch,
                "confidence": args.confidence, "note": args.note, "reason": args.reason,
            }, ensure_ascii=False) + "\n")
    if args.batch:
        os.makedirs(BATCHES, exist_ok=True)
        bpath = os.path.join(BATCHES, "%s.jsonl" % re.sub(r"[^A-Za-z0-9._-]", "_", args.batch))
        with open(bpath, "a", encoding="utf-8", newline="\n") as fh:
            fh.write(json.dumps({
                "batch": args.batch, "date": today(), "by": args.observer,
                "state": args.state, "confidence": args.confidence,
                "ids": ids, "note": args.note, "reason": args.reason,
            }, ensure_ascii=False) + "\n")
    print("review: %d frames set to %s" % (changed, args.state))
    return 0


def cmd_refs(args):
    """Reverse index: which documents cite which frames.

    Scans the repository for `Screenshot YYYY-MM-DD HHMMSS` references and
    records every hit, so a future agent can see every place a frame is used
    as evidence without rescanning the tree. Writes refs-index.jsonl.
    """
    inv = {r["ref"]: r for r in read_jsonl(INV)}
    skip_dirs = {"vendor", "node_modules", "public", ".git", "storage", "docs/game-screenshots"}
    hits = collections.defaultdict(list)
    scanned = 0
    for base, dirs, files in os.walk(ROOT):
        dirs[:] = [d for d in dirs if d not in skip_dirs]
        for f in files:
            if not f.endswith((".md", ".php", ".vue", ".ts", ".json")):
                continue
            ap = os.path.join(base, f)
            try:
                text = open(ap, encoding="utf-8", errors="replace").read()
            except OSError:
                continue
            scanned += 1
            rp = rel(ap)
            for m in FRAME_RE.finditer(text):
                ref = m.group(1)
                hits[ref].append({
                    "doc": rp,
                    "in_inventory": ref in inv,
                    "line": text.count("\n", 0, m.start()) + 1,
                    "context": re.sub(r"\s+", " ", text[max(0, m.start() - 90):m.end() + 90])[:190],
                })
    rows = []
    for ref in sorted(hits):
        docs = sorted({h["doc"] for h in hits[ref]})
        rows.append({
            "schema_version": SCHEMA_VERSION,
            "ref": ref,
            "sid": inv.get(ref, {}).get("sid"),
            "citations": len(hits[ref]),
            "docs": docs,
            "in_inventory": ref in inv,
            "state": inv.get(ref, {}).get("state"),
            "first_citation": hits[ref][0],
        })
    write_jsonl(os.path.join(REG, "refs-index.jsonl"), rows, "ref")
    print("refs: %d docs scanned, %d frames cited, %d of them in the inventory"
          % (scanned, len(rows), sum(1 for r in rows if r["in_inventory"])))
    print("  cited but with no inventory record: %d" % sum(1 for r in rows if not r["in_inventory"]))
    return 0


@_locked
def cmd_evidence(args):
    """Append a mechanic claim to evidence.jsonl and back-link the supporting frames.

    Evidence is a separate registry from the per-image inventory: a claim may span
    many frames, and a frame may support many claims. Every claim must name at
    least one supporting frame, or it is not evidence.
    """
    inv = read_jsonl(INV)
    by_ref = {r["ref"]: r for r in inv}
    supports = [s.strip() for s in args.supports.split(",") if s.strip()]
    unknown = [s for s in supports if s not in by_ref]
    if unknown:
        print("error: unknown supporting frames: %s" % ", ".join(unknown), file=sys.stderr)
        return 2
    if not args.claim:
        print("error: --claim is required", file=sys.stderr)
        return 2
    if not supports:
        print("error: a claim must name at least one supporting frame", file=sys.stderr)
        return 2

    eid = "%s-%04d" % (re.sub(r"[^A-Za-z0-9-]", "-", args.id), len(read_jsonl(EVIDENCE)) + 1)
    status = getattr(args, "status", None) or args.kind
    independent = [s.strip() for s in (getattr(args, "independent", None) or "").split(",") if s.strip()]
    bad_indep = [s for s in independent if s not in supports]
    if bad_indep:
        print("error: --independent frames must be a subset of --supports: %s"
              % ", ".join(bad_indep), file=sys.stderr)
        return 2
    rec = {
        "schema_version": SCHEMA_VERSION,
        "eid": eid,
        "claim": args.claim,
        "kind": args.kind,
        "status": status,
        "supports": supports,
        "independent_supports": independent,
        "scenario": args.scenario,
        "topic": args.topic,
        "confidence": args.confidence,
        "by": args.observer,
        "date": today(),
        "batch": args.batch,
        "limits": [x.strip() for x in args.limits.split(";") if x.strip()] if args.limits else [],
        "contradicts": [x.strip() for x in args.contradicts.split(";") if x.strip()] if args.contradicts else [],
        "open": [x.strip() for x in args.open.split(";") if x.strip()] if args.open else [],
        "next_question": getattr(args, "next_question", None),
    }
    with open(EVIDENCE, "a", encoding="utf-8", newline="\n") as fh:
        fh.write(json.dumps(rec, ensure_ascii=False, sort_keys=True) + "\n")
    for s in supports:
        by_ref[s].setdefault("evidence", []).append({
            "eid": eid, "claim": args.claim, "kind": args.kind,
            "status": status, "supports": supports, "confidence": args.confidence,
        })
    write_jsonl(INV, inv, "filename")
    print("evidence: appended %s supporting %d frame(s)" % (eid, len(supports)))
    return 0


def cmd_evidence_query(args):
    """List evidence claims by status, confidence, scenario, or open question."""
    ev = read_jsonl(EVIDENCE)
    rows = ev
    if args.status:
        rows = [e for e in rows if (e.get("status") or e.get("kind")) == args.status]
    if args.confidence:
        rows = [e for e in rows if e.get("confidence") == args.confidence]
    if args.scenario:
        rows = [e for e in rows if e.get("scenario") == args.scenario]
    if args.open_only:
        rows = [e for e in rows if e.get("open") or e.get("next_question")]
    if args.json:
        print(json.dumps(rows, indent=1, ensure_ascii=False, sort_keys=True))
        return 0
    for e in rows:
        st = e.get("status") or e.get("kind")
        print("%-28s %-9s %-7s %-10s %s"
              % (e.get("eid"), st, e.get("confidence") or "-",
                 e.get("scenario") or "-", (e.get("claim") or "")[:70]))
        if e.get("open") or e.get("next_question"):
            print("    open: %s" % (e.get("next_question") or "; ".join(e.get("open", []))))
    print("-- %d claim(s)" % len(rows))
    return 0


def cmd_browse(args):
    """Filter reviewed frames and evidence; emit Markdown or JSON. Read-only.

    A scenario with no captured frames is reported as `capture_required`, not
    as an empty result, so the gap is explicit rather than silently absent.
    """
    inv = read_jsonl(INV)
    ev = read_jsonl(EVIDENCE)

    frames = inv
    if args.state:
        frames = [r for r in frames if r["state"] in args.state]
    if args.screen_type:
        frames = [r for r in frames
                  if any(o.get("screen_type") == args.screen_type for o in r.get("observations", []))]
    if args.mechanic:
        frames = [r for r in frames
                  if any(args.mechanic in (o.get("visible_mechanics") or [])
                         for o in r.get("observations", []))]
    if args.note_coverage == "has":
        frames = [r for r in frames if r.get("note_refs")]
    elif args.note_coverage == "none":
        frames = [r for r in frames if not r.get("note_refs")]
    if args.confidence:
        frames = [r for r in frames if r.get("confidence") == args.confidence]
    if args.scenario:
        frames = [r for r in frames if r.get("scenario") == args.scenario]

    claims = ev
    if args.status:
        claims = [e for e in claims if (e.get("status") or e.get("kind")) == args.status]
    if args.confidence:
        claims = [e for e in claims if e.get("confidence") == args.confidence]
    if args.scenario:
        claims = [e for e in claims if e.get("scenario") == args.scenario]

    reps = {r.get("cluster_rep") for r in frames if r.get("cluster_rep")}
    counts = {
        "unique_frames": len(frames),
        "cluster_representatives": len(reps),
        "independent_contexts": len(reps),
        "claims": len(claims),
        "open_questions": sum(1 for e in claims if e.get("open") or e.get("next_question")),
    }

    gaps = []
    for s in SCENARIOS:
        tagged = any(r.get("scenario") == s
                     or any(s.lower() in (o.get("text") or "").lower()
                            for o in r.get("observations", []))
                     for r in inv)
        if not tagged:
            gaps.append(s)

    if args.json:
        print(json.dumps({
            "counts": counts,
            "frames": sorted(r["ref"] for r in frames),
            "claims": sorted(e["eid"] for e in claims),
            "capture_required": gaps,
        }, indent=1, ensure_ascii=False, sort_keys=True))
        return 0

    L = ["# Evidence browser", "", "| metric | value |", "| --- | --- |"]
    for k in ("unique_frames", "cluster_representatives", "independent_contexts",
              "claims", "open_questions"):
        L.append("| %s | %d |" % (k, counts[k]))
    L.append("")
    if gaps:
        L.append("## Capture required")
        L.append("")
        for s in gaps:
            L.append("- %s: no captured frames in the corpus" % s)
        L.append("")
    L.append("## Frames (%d)" % len(frames))
    for r in sorted(frames, key=lambda x: x["ref"])[:100]:
        L.append("- %s (%s, %s)" % (r["ref"], r["state"], r.get("scenario") or "-"))
    L.append("")
    L.append("## Claims (%d)" % len(claims))
    for e in claims:
        L.append("- %s [%s] %s" % (e["eid"], e.get("status") or e.get("kind"), e.get("claim")))
    print("\n".join(L))
    return 0


@_locked
def cmd_batch(args):
    """Define, list, and inspect persisted batch definitions.

    A batch is the unit of resumable work: which frames, why, the required
    output, the verification criteria, and the next step. It is written to
    batches/<id>.json; a completed run is appended to the same file as a
    result record, so a session can pick up where another left off.

    Mutates shared state (the batch manifest), so it is serialized on the
    registry lock.
    """
    bpath = os.path.join(BATCHES, "%s.json" % re.sub(r"[^A-Za-z0-9._-]", "_", args.id))
    os.makedirs(BATCHES, exist_ok=True)

    if args.show and not os.path.exists(bpath):
        print("error: no batch %s" % args.id, file=sys.stderr)
        return 2
    if args.show:
        print(open(bpath, encoding="utf-8").read())
        return 0

    inv = {r["ref"]: r for r in read_jsonl(INV)}
    ids = [i.strip() for i in args.ids.split(",") if i.strip()]
    unknown = [i for i in ids if i not in inv]
    if unknown:
        print("error: unknown ids: %s" % ", ".join(unknown), file=sys.stderr)
        return 2
    rec = {
        "id": args.id,
        "question": args.question,
        "ids": ids,
        "why": args.why,
        "output_schema": "review + evidence per confirmed frame",
        "verify": args.verify or "screenshots.py validate stays clean; each verified id has review_history + observations",
        "next": args.next,
        "created": today(),
        "results": [],
    }
    if os.path.exists(bpath):
        existing = json.load(open(bpath, encoding="utf-8"))
        rec["results"] = existing.get("results", [])
    if args.state:
        rec["results"].append({
            "date": today(), "by": args.observer, "state": args.state,
            "note": args.note, "confidence": args.confidence, "reason": args.reason,
        })
    write_json_atomic(bpath, rec)
    print("batch %s: %d frames defined" % (args.id, len(ids)))
    return 0


def _batch_paths(bid):
    stem = re.sub(r"[^A-Za-z0-9._-]", "_", bid)
    return (os.path.join(BATCHES, "%s.json" % stem),
            os.path.join(BATCHES, "%s.jsonl" % stem))


def compute_batch_summary(bid):
    """Deterministic roll-up of a batch from its manifest, log, and inventory.

    Totals come from the batch's unique frame ids and the latest valid review
    event for each frame (the batch JSONL log first, then the inventory). The
    result is free of timestamps so a second run is byte-identical.
    """
    bpath, lpath = _batch_paths(bid)
    if not os.path.exists(bpath):
        return None
    manifest = json.load(open(bpath, encoding="utf-8"))
    ids = list(manifest.get("ids", []))
    unique_ids = sorted(set(ids))
    dupes = sorted(i for i in set(ids) if ids.count(i) > 1)

    latest = {}
    log_rows = read_jsonl(lpath) if os.path.exists(lpath) else []
    for row in log_rows:
        if row.get("state") not in STATES:
            continue
        for i in row.get("ids", []):
            latest[i] = row["state"]

    inv = {r["ref"]: r for r in read_jsonl(INV)}
    states = collections.Counter()
    unresolved_ids = []
    for i in unique_ids:
        st = latest.get(i) or inv.get(i, {}).get("state")
        if st:
            states[st] += 1
        else:
            unresolved_ids.append(i)

    return {
        "batch": bid,
        "frame_count": len(ids),
        "unique_frame_count": len(unique_ids),
        "duplicate_ids": dupes,
        "log_entries": len(log_rows),
        "states": {k: states.get(k, 0) for k in STATES if states.get(k, 0)},
        "unresolved_ids": unresolved_ids,
    }


@_locked
def cmd_batch_summary(args):
    """Compute (and optionally write) a deterministic batch roll-up.

    ``--check`` compares the stored summary with the computed one and exits
    non-zero when stale. ``--dry-run`` prints without writing. A plain run
    writes the summary into the manifest, idempotently: the same inputs
    produce the same bytes, and the append-only ``results`` list is untouched.
    """
    s = compute_batch_summary(args.id)
    if s is None:
        print("error: no batch %s" % args.id, file=sys.stderr)
        return 2
    bpath, _ = _batch_paths(args.id)
    manifest = json.load(open(bpath, encoding="utf-8"))
    stored = manifest.get("summary")

    if args.check:
        if stored == s:
            print("batch-summary %s: OK (%d frames, %s)"
                  % (args.id, s["unique_frame_count"], s["states"]))
            return 0
        print("batch-summary %s: STALE" % args.id)
        print("  stored:   %s" % json.dumps(stored, sort_keys=True))
        print("  computed: %s" % json.dumps(s, sort_keys=True))
        return 1
    if args.dry_run:
        print(json.dumps(s, indent=1, sort_keys=True))
        return 0

    manifest["summary"] = s
    write_json_atomic(bpath, manifest)
    print("batch-summary %s: wrote %d frames (%s)"
          % (args.id, s["unique_frame_count"], s["states"]))
    return 0


def read_dispositions():
    """Disposition records for citations that do not resolve to the inventory."""
    if not os.path.exists(DISPOSITIONS):
        return []
    return read_jsonl(DISPOSITIONS)


def _dispose(ref, kind, resolves_to, rationale, evidence, observer):
    """Upsert a machine-readable disposition for an unresolved citation.

    Replaces any existing record for ``ref`` (a disposition is a current
    decision, not append-only history), and records a provenance line.
    """
    rows = [r for r in read_dispositions() if r["ref"] != ref]
    rec = {
        "schema_version": SCHEMA_VERSION,
        "ref": ref,
        "kind": kind,
        "resolves_to": resolves_to,
        "rationale": rationale,
        "evidence": evidence,
        "by": observer,
        "date": today(),
    }
    rows.append(rec)
    rows.sort(key=lambda r: r["ref"])
    write_jsonl(DISPOSITIONS, rows, "ref")
    return rec


@_locked
def cmd_disposition(args):
    """Record or remove the disposition of an unresolved citation.

    An unresolved citation is a ``Screenshot ...`` reference that does not
    match any inventory record. Rather than inventing an alias, the registry
    records why it is unresolved and what (if anything) it legitimately
    points at. Kinds: missing_no_copy, explicitly_missing, filename_typo.
    """
    inv = {r["ref"]: r for r in read_jsonl(INV)}
    if args.list:
        for d in read_dispositions():
            print("%-19s %-18s -> %s" % (d["ref"], d["kind"], d.get("resolves_to") or "-"))
        return 0
    if args.kind == "filename_typo" and not args.resolves_to:
        print("error: filename_typo requires --resolves-to", file=sys.stderr)
        return 2
    if args.kind == "filename_typo":
        if args.resolves_to not in inv:
            print("error: resolves-to %s is not an inventory frame; "
                  "a typo must point at a real frame" % args.resolves_to, file=sys.stderr)
            return 2
        rec = _dispose(args.ref, "filename_typo", args.resolves_to,
                       args.rationale or "citation typo for %s" % args.resolves_to,
                       args.evidence or [], args.observer)
    elif args.remove:
        rows = [r for r in read_dispositions() if r["ref"] != args.ref]
        write_jsonl(DISPOSITIONS, rows, "ref")
        rec = None
    else:
        rec = _dispose(args.ref, args.kind, None,
                       args.rationale or "unresolved citation; skip",
                       args.evidence or [], args.observer)
    if rec:
        print("disposition: %s = %s%s" % (rec["ref"], rec["kind"],
                                           (" -> " + rec["resolves_to"]) if rec.get("resolves_to") else ""))
    else:
        print("disposition: removed %s" % args.ref)
    return 0


def cmd_selftest(args):
    """Delegate to screenshots_selftest.py so the assertions live in one place."""
    mod = os.path.join(os.path.dirname(os.path.abspath(__file__)), "screenshots_selftest.py")
    if not os.path.exists(mod):
        print("error: %s not found" % rel(mod), file=sys.stderr)
        return 2
    import subprocess
    proc = subprocess.run([sys.executable, mod], capture_output=True, text=True)
    if proc.stdout:
        sys.stdout.write(proc.stdout)
    if proc.stderr:
        sys.stderr.write(proc.stderr)
    return proc.returncode


def main(argv=None):
    p = argparse.ArgumentParser(prog="screenshots.py", description=__doc__,
                                formatter_class=argparse.RawDescriptionHelpFormatter)
    sub = p.add_subparsers(dest="cmd", required=True)

    s = sub.add_parser("inventory", help="regenerate machine-derived fields, preserve review fields")
    s.set_defaults(fn=cmd_inventory)

    s = sub.add_parser("notes", help="reconcile legacy note files")
    s.set_defaults(fn=cmd_notes)

    s = sub.add_parser("validate", help="check registry integrity")
    s.set_defaults(fn=cmd_validate)

    s = sub.add_parser("report", help="write and print COVERAGE.md")
    s.set_defaults(fn=cmd_report)

    s = sub.add_parser("queue", help="prioritized outstanding work")
    s.add_argument("--limit", type=int, default=40)
    s.add_argument("--state", help="restrict to one state")
    s.set_defaults(fn=cmd_queue)

    s = sub.add_parser("show", help="filtered records")
    s.add_argument("--state", nargs="+")
    s.add_argument("--category")
    s.add_argument("--scenario")
    s.add_argument("--note", help="note file name")
    s.add_argument("--ids", help="comma separated refs or sids")
    s.add_argument("--full", action="store_true", help="only 1920x1080 frames")
    s.add_argument("--no-notes", action="store_true")
    s.add_argument("--limit", type=int, default=50)
    s.add_argument("--json", action="store_true", help="full records, not the table")
    s.set_defaults(fn=cmd_show)

    s = sub.add_parser("sheet", help="labelled contact sheet plus cell mapping")
    s.add_argument("--ids", help="comma separated refs")
    s.add_argument("--state", help="select by state instead")
    s.add_argument("--rows", type=int, default=8)
    s.add_argument("--limit", type=int, default=40)
    s.set_defaults(fn=cmd_sheet)

    s = sub.add_parser("review", help="record a review decision")
    s.add_argument("--ids", required=True)
    s.add_argument("--state", required=True, choices=STATES)
    s.add_argument("--batch", help="batch identifier")
    s.add_argument("--observer", required=True, help="who inspected, e.g. agent name or owner")
    s.add_argument("--confidence", choices=["low", "medium", "high"])
    s.add_argument("--note", help="what was observed; required for `verified`")
    s.add_argument("--reason", help="why a frame was skipped")
    s.add_argument("--next-action")
    s.add_argument("--scenario")
    s.add_argument("--category")
    s.add_argument("--questions", help="semicoloned outstanding questions")
    s.add_argument("--screen-type", help="structured observation: screen type")
    s.add_argument("--career-phase", help="structured observation: career phase")
    s.add_argument("--mechanics", help="structured observation: semicoloned visible mechanics")
    s.add_argument("--values", help="structured observation: semicoloned key=value direct values")
    s.add_argument("--limitations", help="structured observation: semicoloned limitations")
    s.add_argument("--follow-up", help="structured observation: follow-up question")
    s.set_defaults(fn=cmd_review)

    s = sub.add_parser("batch", help="define, list, and inspect persisted batches")
    s.add_argument("--id", required=True, help="batch id, e.g. B001")
    s.add_argument("--ids", help="comma separated frame refs")
    s.add_argument("--question", help="the research question this batch answers")
    s.add_argument("--why", help="why these frames were selected")
    s.add_argument("--verify", help="the verification criteria")
    s.add_argument("--next", help="the expected next step")
    s.add_argument("--state", choices=STATES, help="record a result for the batch")
    s.add_argument("--observer", help="who recorded the result")
    s.add_argument("--confidence", choices=["low", "medium", "high"])
    s.add_argument("--note")
    s.add_argument("--reason")
    s.add_argument("--show", action="store_true", help="print the persisted batch instead of writing")
    s.set_defaults(fn=cmd_batch)

    s = sub.add_parser("batch-summary", help="deterministic roll-up of a batch manifest")
    s.add_argument("--id", required=True, help="batch id, e.g. B001")
    s.add_argument("--check", action="store_true", help="compare stored vs computed; non-zero when stale")
    s.add_argument("--dry-run", action="store_true", help="print the summary without writing")
    s.set_defaults(fn=cmd_batch_summary)

    s = sub.add_parser("refs", help="reverse index of frame citations across the repository")
    s.set_defaults(fn=cmd_refs)

    s = sub.add_parser("disposition", help="record the disposition of an unresolved citation")
    s.add_argument("--ref", required=True, help="the cited ref, e.g. 2026-07-18 235511")
    s.add_argument("--kind", choices=DISPOSITION_KINDS, required=True)
    s.add_argument("--resolves-to", help="the real frame a filename_typo points at")
    s.add_argument("--rationale")
    s.add_argument("--evidence", help="semicolon-separated sources, e.g. DESIGN-CORPUS.md:sect-6")
    s.add_argument("--observer", required=True)
    s.add_argument("--remove", action="store_true", help="remove the disposition for this ref")
    s.add_argument("--list", action="store_true", help="list all dispositions")
    s.set_defaults(fn=cmd_disposition)

    s = sub.add_parser("evidence", help="append a mechanic claim backed by supporting frames")
    s.add_argument("--id", required=True, help="topic prefix, e.g. mood-effects")
    s.add_argument("--claim", required=True)
    s.add_argument("--supports", required=True, help="comma separated refs")
    s.add_argument("--kind", required=True, choices=["observed", "inferred"])
    s.add_argument("--scenario")
    s.add_argument("--topic")
    s.add_argument("--confidence", choices=["low", "medium", "high"])
    s.add_argument("--observer", required=True)
    s.add_argument("--batch")
    s.add_argument("--limits", help="semicoloned limits")
    s.add_argument("--contradicts", help="semicoloned contradicted claims")
    s.add_argument("--open", help="semicoloned open questions")
    s.add_argument("--status", choices=["inferred", "observed", "confirmed"],
                   help="claim status; defaults to --kind")
    s.add_argument("--independent", help="comma separated supports that are independent capture contexts")
    s.add_argument("--next-question", help="the next verification question")
    s.set_defaults(fn=cmd_evidence)

    s = sub.add_parser("evidence-query", help="list evidence claims by status/confidence/scenario")
    s.add_argument("--status", choices=["inferred", "observed", "confirmed"])
    s.add_argument("--confidence", choices=["low", "medium", "high"])
    s.add_argument("--scenario")
    s.add_argument("--open-only", action="store_true", help="only claims with an open question")
    s.add_argument("--json", action="store_true")
    s.set_defaults(fn=cmd_evidence_query)

    s = sub.add_parser("browse", help="filter reviewed frames and evidence; Markdown or JSON")
    s.add_argument("--state", nargs="+")
    s.add_argument("--screen-type")
    s.add_argument("--mechanic")
    s.add_argument("--scenario")
    s.add_argument("--note-coverage", choices=["has", "none"])
    s.add_argument("--confidence", choices=["low", "medium", "high"])
    s.add_argument("--status", choices=["inferred", "observed", "confirmed"])
    s.add_argument("--json", action="store_true")
    s.set_defaults(fn=cmd_browse)

    s = sub.add_parser("selftest", help="assert the tool's invariants on a throwaway corpus")
    s.set_defaults(fn=cmd_selftest)

    a = p.parse_args(argv)
    return a.fn(a)


if __name__ == "__main__":
    sys.exit(main())
