"""Import a new screenshot capture into the research intake.

A capture lands under `capture/` with a provenance manifest entry.  Nothing is
copied into the canonical corpus without an explicit `--promote` and a reviewer
name, and an existing file is never overwritten.  The manifest is append-only.
"""
import argparse
import hashlib
import json
import os
import shutil
import struct
import sys
import uuid
from datetime import datetime, timezone

HERE = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, HERE)
import screenshots as S  # noqa: E402

CAPTURE = os.path.join(S.REG, "capture")
MANIFEST = os.path.join(CAPTURE, "manifest.jsonl")

PNG_MAGIC = b"\x89PNG\r\n\x1a\n"
JPEG_MAGIC = b"\xff\xd8\xff"


def sha256_of(path):
    h = hashlib.sha256()
    with open(path, "rb") as fh:
        for chunk in iter(lambda: fh.read(65536), b""):
            h.update(chunk)
    return h.hexdigest()


def is_image(path):
    try:
        with open(path, "rb") as fh:
            head = fh.read(8)
    except OSError:
        return False
    return head.startswith(PNG_MAGIC) or head[:3] == JPEG_MAGIC


def image_size(path):
    """Return (width, height) for a PNG, else (None, None)."""
    try:
        with open(path, "rb") as fh:
            data = fh.read(33)
        if data.startswith(PNG_MAGIC) and data[12:16] == b"IHDR":
            w, h = struct.unpack(">II", data[16:24])
            return w, h
    except OSError:
        pass
    return None, None


def _append_manifest(manifest, entry):
    os.makedirs(os.path.dirname(manifest), exist_ok=True)
    with open(manifest, "a", encoding="utf-8", newline="\n") as fh:
        fh.write(json.dumps(entry, ensure_ascii=False, sort_keys=True) + "\n")


def import_capture(source, scenario, screen_type, operator_context,
                   promote=False, reviewed_by=None, capture_timestamp=None,
                   capture_dir=None, corpus_dir=None, game_version=None, region=None):
    """Import one capture. Returns (exit_code, entry_or_None, message)."""
    capture_dir = capture_dir or CAPTURE
    corpus_dir = corpus_dir or S.SRC
    manifest = os.path.join(capture_dir, "manifest.jsonl")

    if not os.path.isfile(source):
        return 2, None, "source not found: %s" % source
    if not is_image(source):
        return 1, None, "not a readable image: %s" % source

    sha = sha256_of(source)
    sha8 = sha[:8]
    ext = os.path.splitext(source)[1].lower() or ".png"
    ts = capture_timestamp or datetime.fromtimestamp(
        os.path.getmtime(source), tz=timezone.utc).strftime("%Y%m%dT%H%M%S")
    w, h = image_size(source)

    stem = "%s_%s" % (ts, sha8)
    dest = os.path.join(capture_dir, stem + ext)
    if os.path.exists(dest):
        if sha256_of(dest) == sha:
            dest = None  # identical bytes already present: never overwrite
        else:
            dest = os.path.join(capture_dir, "%s_%s%s" % (stem, sha8, ext))

    if dest is not None:
        os.makedirs(capture_dir, exist_ok=True)
        shutil.copy2(source, dest)

    promoted = False
    if promote:
        if not reviewed_by:
            return 2, None, "--promote requires --reviewed-by"
        os.makedirs(corpus_dir, exist_ok=True)
        target = os.path.join(corpus_dir, os.path.basename(dest or (stem + ext)))
        if not os.path.exists(target):
            shutil.copy2(source, target)
        promoted = True

    entry = {
        "intake_id": str(uuid.uuid4()),
        "capture_timestamp": ts,
        "scenario": scenario,
        "game_version": game_version,
        "region": region,
        "screen_type": screen_type,
        "source_filename": os.path.basename(source),
        "sha256": sha,
        "width": w,
        "height": h,
        "operator_context": operator_context,
        "promoted": promoted,
        "reviewed_by": reviewed_by if promote else None,
    }
    _append_manifest(manifest, entry)
    return 0, entry, "imported %s" % (dest or "(identical bytes already present)")


def main():
    ap = argparse.ArgumentParser(description="Import a screenshot capture.")
    ap.add_argument("--source", required=True)
    ap.add_argument("--scenario", required=True)
    ap.add_argument("--screen-type", required=True)
    ap.add_argument("--operator-context", required=True)
    ap.add_argument("--promote", action="store_true")
    ap.add_argument("--reviewed-by")
    ap.add_argument("--capture-timestamp", help="ISO-ish stem, e.g. 20261011T160000")
    ap.add_argument("--game-version")
    ap.add_argument("--region")
    ap.add_argument("--capture-dir", default=CAPTURE)
    ap.add_argument("--corpus-dir", default=S.SRC)
    args = ap.parse_args()

    rc, _entry, msg = import_capture(
        args.source, args.scenario, args.screen_type, args.operator_context,
        promote=args.promote, reviewed_by=args.reviewed_by,
        capture_timestamp=args.capture_timestamp, capture_dir=args.capture_dir,
        corpus_dir=args.corpus_dir, game_version=args.game_version, region=args.region)
    if rc != 0:
        print("error: %s" % msg, file=sys.stderr)
    else:
        print("import_captures: %s" % msg)
    return rc


if __name__ == "__main__":
    sys.exit(main())
