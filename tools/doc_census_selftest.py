#!/usr/bin/env python
"""One runnable check for `doc_census.py`'s citation resolver, both directions.

`DocCitationParityTest` shells out to the census and gates the total, so a resolver that wrongly
resolved live citations would lower that total and pass the gate for the wrong reason. Asserted
here because the census prints only the first twenty dead links: an assertion on its stdout
cannot fail for the reason it claims.

    python tools/doc_census_selftest.py
    python tools/doc_census_selftest.py tools/.doc_census_head.py   # a candidate resolver to falsify

Exits non-zero when the resolver over-resolves or under-resolves, or when the quoted-original
marker stops being honoured. Run it against the copy of the pre-2026-10-12 resolver kept beside
it to see the first two assertions fail.
"""

import importlib.util
import os
import sys

TARGET = sys.argv[1] if len(sys.argv) > 1 else "tools/doc_census.py"
spec = importlib.util.spec_from_file_location("census_under_test", TARGET)
census = importlib.util.module_from_spec(spec)
spec.loader.exec_module(census)

tracked = set(census.git("ls-files").splitlines())
dead, _ = census.dead_links()
dead_refs = {ref for _, _, ref, _ in dead}
names = {os.path.basename(p) for p in tracked if p.endswith(".md")}

# 1. A bare name whose tracked file lives in a directory the resolver does not try is still live.
#    Measured 2026-10-12: `AGENTS.md` points a reader at `code-style.md`, tracked at
#    `.ai/rules/code-style.md`, and the census counted that instruction dead.
assert ".ai/rules/code-style.md" in tracked, "the fixture citation moved; pick a live bare name"
assert "code-style.md" not in dead_refs, "a live bare-name citation counts dead: the resolver regressed"

# 2. The same must hold for a relative ref beside its own file: `jev-inspect/CHANGELOG.md` cites
#    `docs/DESIGN.md` meaning its sibling, which is tracked.
assert "jev-inspect/docs/DESIGN.md" in tracked, "the sibling fixture moved"
sibling_dead = [(f, n) for f, n, ref, _ in dead
                if ref == "docs/DESIGN.md" and f.startswith("jev-inspect/")]
assert not sibling_dead, f"a sibling-relative citation counts dead: {sibling_dead[:3]}"

# 3. Widening must not have neutered detection. The retired `docs/design-research/` tree is absent
#    everywhere, so a body citing one of its paths must still read dead.
#    Asserted on the resolver, not on the corpus. The corpus-level fixture was spent on 2026-10-20:
#    the last backticked citation of `docs/design-research/CONSTRAINTS.md` was un-backticked, so that
#    target stopped appearing in the dead list at all and the old assertion could no longer hold. A
#    fixture the corpus can spend is not a test.
absent = "docs/design-research/__selftest-absent-target__.md"
assert not os.path.exists(os.path.join(census.REPO, absent)) and absent not in tracked
probe = census.dead_refs_in_text("selftest.md", f"it read `{absent}` once.\n", "", tracked, names)
assert len(probe) == 1 and probe[0][3] == "GONE", f"an absent target reads live: {probe}"

#    The corpus itself must still carry absent path-bearing targets, or the ratchet measures nothing
#    path-shaped.
assert any("/" in ref and kind == "GONE" for _, _, ref, kind in dead), \
    "no path-bearing dead target remains in the corpus"

# 4. A deleted file with a name no tracked markdown carries cannot be resolved by the basename rule.
assert "RAW-FINDINGS.md" not in tracked and "RAW-FINDINGS.md" not in names
assert "RAW-FINDINGS.md" in dead_refs, "the bare-name rule resolved an absent file"

# 5. A line carrying the quoted-original marker is not a citation, and the same line without the
#    marker still is. A dated correction quotes the text it corrects, so the dead path it names is
#    quoted history; the marker is what says so, and it is line-scoped.
quoted = "The original sentence read `RAW-FINDINGS.md` and named it as the source."
marked = quoted + " " + census.QUOTED_ORIGINAL_FLAG

unmarked_bad = census.dead_refs_in_text("planted.md", quoted + "\n", "", tracked, names)
assert len(unmarked_bad) == 1, f"the unmarked quote stops counting as dead: {unmarked_bad}"

skipped = []
marked_bad = census.dead_refs_in_text("planted.md", marked + "\n", "", tracked, names, skipped)
assert marked_bad == [], f"a marked quoted original is still counted dead: {marked_bad}"
assert skipped == [("planted.md", 1)], f"the marked line was not reported as skipped: {skipped}"

# 6. The marker is line-scoped: a dead path on the next line is still counted.
two_lines = marked + "\nThe next sentence also reads `RAW-FINDINGS.md`.\n"
skipped = []
two_bad = census.dead_refs_in_text("planted.md", two_lines, "", tracked, names, skipped)
assert len(two_bad) == 1, f"the marker skipped a line it does not sit on: {two_bad}"
assert skipped == [("planted.md", 1)], f"the marker skipped a line it does not sit on: {skipped}"

print(f"resolver check passed against {TARGET}: {len(dead)} dead, {len(dead_refs)} distinct targets")
