#!/usr/bin/env python
"""Markdown fence checker: finds closing fences that carry an info string.

A fenced block opens with a language (```bash) and must close with a *bare* fence
(```). CommonMark 0.31 §4.5 says a closing fence carries no info string: the line
that ends a block is the run of backticks and nothing else. A line such as ```text
is therefore always an *opening*, never a closer, so a block written as

    ```bash
    composer setup
    ```text

    Seeding reads ...

does not close at the third line. It opens a second block whose language is `text`,
and every line after it renders as code until the next bare fence. The prose that
follows is swallowed, and what the reader sees is not what the file says.

Run it against the tree you are on and fix what it names. The checker reports only
this defect class; it does not rewrite anything.

Usage: python tools/md_fence_check.py [path ...]
"""

import io
import os
import re
import sys

REPO = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

# An opening or closing fence, captured as (run, info). A run is three or more of
# the same char; the info string is everything after it. Backtick fences cannot
# contain a backtick, which is why the info is anchored to a non-backtick here.
FENCE = re.compile(r"^(`{3,}|~{3,})[ \t]*([^`\n].*)?$")


def classify(path):
    """Classify every fence in one file.

    Returns (invalid_closers, unclosed), where each entry carries the line number a
    reader needs. `invalid_closers` is the defect class above: an info-bearing fence
    sitting between a block's opening and its bare closer, with only code between
    them. An adjacent pair (```bash immediately followed by ```text with no code
    between) is a language switch, not a spurious closer, so it is not reported.

    @return tuple[list[tuple[int, str, int, str, str]], list[tuple[int, str, int, str]]]
    """
    with io.open(os.path.join(REPO, path), encoding="utf-8", errors="replace") as f:
        lines = f.readlines()

    opens = []            # stack of dicts: lineno, char, length, info
    invalid_closers = []  # (lineno, text, opened_at, open_info, this_info)
    unclosed = []         # (lineno, char, length, info)

    for i, raw in enumerate(lines, 1):
        line = raw.rstrip("\r\n")
        m = FENCE.match(line)
        if not m:
            continue
        run, info = m.group(1), (m.group(2) or "").strip()
        char, length = run[0], len(run)

        if info == "":
            # A bare fence: a closer if it matches the block on top of the stack,
            # otherwise an opener (CommonMark requires char and length to match).
            if opens and char == opens[-1]["char"] and length >= opens[-1]["length"]:
                opens.pop()
            else:
                opens.append({"lineno": i, "char": char, "length": length, "info": ""})
            continue

        # An info-bearing fence is an opening. Record it as a spurious closer when it
        # sits inside an open block of a different language with code between the two.
        if opens:
            top = opens[-1]
            if top["info"] and top["info"] != info:
                between = [l for l in lines[top["lineno"]:i - 1] if l.strip() != ""]
                if between:
                    invalid_closers.append((i, line.strip(), top["lineno"], top["info"], info))
        opens.append({"lineno": i, "char": char, "length": length, "info": info})

    unclosed = [(o["lineno"], o["char"], o["length"], o["info"]) for o in opens]
    return invalid_closers, unclosed


def md_files(paths):
    """The markdown files to check, from the arguments or from git when given none.

    `git ls-files` rather than a directory walk so the check sees exactly the tracked
    set the census sees, and never a scratch file another session left behind.
    """
    if paths:
        return [p for p in paths if os.path.isfile(os.path.join(REPO, p))]

    import subprocess

    out = subprocess.run(["git", "ls-files", "--", "*.md"], cwd=REPO,
                         capture_output=True, text=True, encoding="utf-8",
                         errors="replace").stdout
    return [p for p in out.splitlines() if p.strip()]


def main(argv):
    paths = md_files(argv[1:])
    total = 0

    for path in paths:
        invalid, unclosed = classify(path)
        if not invalid and not unclosed:
            continue
        print(f"\n== {path}")
        for (i, text, opened_at, open_info, this_info) in invalid:
            print(f"  INVALID-CLOSER line {i} (block opened {opened_at} [{open_info}]): {text}")
            total += 1
        for (i, char, length, info) in unclosed:
            print(f"  UNCLOSED fence line {i} char={char!r} len={length} info={info!r}")

    print(f"\n{total} invalid closing fence(s) in {len(paths)} file(s)")

    # Exit non-zero only on the defect class this tool exists to catch. An unclosed
    # fence is reported for the reader but is not a failure: a code block that runs
    # to the end of the file is legal CommonMark and the tool has no way to know
    # whether that was intended.
    return 1 if total else 0


if __name__ == "__main__":
    sys.exit(main(sys.argv))
