#!/usr/bin/env python
"""One runnable check for `md_fence_check.py`'s fence state machine, both directions.

A checker that reports every fence, or none, is worthless: the first makes the
defect invisible in noise and the second lets the corruption through. This asserts
the classifier on a planted body rather than on the tree, so it proves the pin
still catches real drift without needing the repository to be wrong.

    python tools/md_fence_check_selftest.py
    python tools/md_fence_check_selftest.py tools/.md_fence_check_candidate.py

Exits non-zero when the classifier misses a spurious closer, invents one, or
misreads a legal construct. Read `tools/md_fence_check.py`'s module docblock for
why an info-bearing fence can never be a closing fence under CommonMark.
"""

import importlib.util
import os
import sys

failures = []

TARGET = sys.argv[1] if len(sys.argv) > 1 else "tools/md_fence_check.py"
spec = importlib.util.spec_from_file_location("fence_check_under_test", TARGET)
fence = importlib.util.module_from_spec(spec)
spec.loader.exec_module(fence)


def check(name, condition, detail=""):
    if not condition:
        failures.append(f"{name}{': ' + detail if detail else ''}")


# 1. The defect class: a block that opens with a language and "closes" with an
#    info-bearing fence. The closer is the reported line, and the block it wrongly
#    sits inside is named so a reader can find both ends.
body = """# Doc

```bash
composer setup
```text

Seeding reads the committed bodies.
"""

invalid, unclosed = (None, None)

# classify() reads from disk, so the body goes through a real file in the repo root.
planted = os.path.join(fence.REPO, ".md_fence_check_planted.md")
with open(planted, "w", encoding="utf-8", newline="") as f:
    f.write(body)

try:
    invalid, unclosed = fence.classify(".md_fence_check_planted.md")

    check("catches the spurious closer", len(invalid) == 1, f"got {invalid}")
    if invalid:
        lineno, text, opened_at, open_info, this_info = invalid[0]
        check("reports the closer's own line", lineno == 5, f"got {lineno}")
        check("names the block it sits in", opened_at == 3, f"got {opened_at}")
        check("reads the opening language", open_info == "bash", f"got {open_info!r}")
        check("reads the closer's info string", this_info == "text", f"got {this_info!r}")

    # 2. The legal shape, which must not be reported: the same block closed with a
    #    bare fence. An adjacent pair with no code between is a language switch, so
    #    it is excluded too, and both are asserted here rather than inferred.
    legal = """# Doc

```bash
composer setup
```

Seeding reads the committed bodies.

```text
plain text
```
"""
    with open(planted, "w", encoding="utf-8", newline="") as f:
        f.write(legal)
    invalid, unclosed = fence.classify(".md_fence_check_planted.md")
    check("stays green on a bare closer", invalid == [], f"got {invalid}")

    # 3. A bare closer that does not match the opening (length or char) is an opener
    #    under CommonMark, not a closer, and must not be reported as one.
    mismatch = """```bash
composer setup
~~~~

prose
"""
    with open(planted, "w", encoding="utf-8", newline="") as f:
        f.write(mismatch)
    invalid, unclosed = fence.classify(".md_fence_check_planted.md")
    check("does not report a mismatched bare fence", invalid == [], f"got {invalid}")

    # 4. An info-bearing fence adjacent to another, with no code between, is a
    #    language switch rather than a spurious closer.
    adjacent = """```bash
```text
prose
"""
    with open(planted, "w", encoding="utf-8", newline="") as f:
        f.write(adjacent)
    invalid, unclosed = fence.classify(".md_fence_check_planted.md")
    check("does not report an adjacent language switch", invalid == [], f"got {invalid}")
finally:
    if os.path.exists(planted):
        os.remove(planted)

if failures:
    print(f"md_fence_check selftest FAILED ({len(failures)}):")
    for f in failures:
        print(f"  - {f}")
    sys.exit(1)

print("md_fence_check selftest passed")
