#!/usr/bin/env python
"""One runnable check for `doc_census.py`'s citation resolver, both directions.

`DocCitationParityTest` shells out to the census and gates the total, so a resolver that wrongly
resolved live citations would lower that total and pass the gate for the wrong reason. Asserted
here because the census prints only the first twenty dead links: an assertion on its stdout
cannot fail for the reason it claims.

Every rule is asserted on the resolver, against a synthetic tracked set, so no citation in the
corpus can spend it. The corpus-level properties are kept separately and stated weakly: what
has to hold is that the corpus still exercises each rule, computed from the tree rather than
pinned to a path. An assertion pinned to a specific file still being cited was spent on
2026-10-20, when the last backticked citation of `docs/design-research/CONSTRAINTS.md` was
un-backticked and that target stopped appearing in the dead list at all. A fixture the corpus
can spend is not a test. The corpus-level properties are measured at the census's own default
subject, the ref, so an uncommitted edit on one machine cannot trip them.

    python tools/doc_census_selftest.py
    python tools/doc_census_selftest.py tools/.doc_census_head.py   # a candidate resolver to falsify

Exits non-zero when the resolver over-resolves or under-resolves, when the quoted-original
marker stops being honoured, or when the corpus stops exercising a rule.
"""

import importlib.util
import os
import re
import sys

TARGET = sys.argv[1] if len(sys.argv) > 1 else "tools/doc_census.py"
spec = importlib.util.spec_from_file_location("census_under_test", TARGET)
census = importlib.util.module_from_spec(spec)
spec.loader.exec_module(census)

tracked = set(census.subject_paths(census.DEFAULT_REF))
dead, _ = census.dead_links()
dead_refs = {ref for _, _, ref, _ in dead}
names = {os.path.basename(p) for p in tracked if p.endswith(".md")}


def probe(text, listing, names_, near="", f="planted.md"):
    """The resolver's verdict on a text, against a synthetic tracked set.

    `disk=False`, so the verdict does not depend on what happens to sit on this machine: a
    target that resolves nowhere is GONE whether or not some local file shares its name.
    """
    return census.dead_refs_in_text(f, text, near, set(listing), set(names_), disk=False)


# 1. A bare name whose tracked file lives in a directory the resolver does not try is still live.
#    Measured 2026-10-12: `AGENTS.md` points a reader at `code-style.md`, tracked at
#    `.ai/rules/code-style.md`, and the census counted that instruction dead.
assert probe("it reads `code-style.md`.\n", {"rules/code-style.md"}, {"code-style.md"}) == [], \
    "a live bare-name citation counts dead: the resolver regressed"

# 2. The same name, when no tracked file carries that basename, is dead. The basename rule must
#    not resolve a name nothing in the tree has.
assert len(probe("it reads `code-style.md`.\n", set(), set())) == 1, \
    "a bare name no tracked file carries reads live"

# 3. A relative ref beside its own file resolves to its sibling, not to a root file of that
#    name. Measured 2026-10-12: `jev-inspect/CHANGELOG.md` cites `docs/DESIGN.md` meaning its
#    sibling, which is tracked.
assert probe("it reads `docs/DESIGN.md`.\n", {"jev-inspect/docs/DESIGN.md"}, {"DESIGN.md"},
             near="jev-inspect", f="jev-inspect/CHANGELOG.md") == [], \
    "a sibling-relative citation counts dead: the resolver regressed"

# 4. A path-bearing ref that exists nowhere is dead, and the corpus's own absent targets are
#    counted dead rather than resolved by the basename rule.
assert len(probe("it read `RAW-FINDINGS.md`.\n", {"docs/other.md"}, {"other.md"})) == 1, \
    "the basename rule resolved an absent file"
assert len(probe("it read `docs/design-research/GONE.md`.\n", {"docs/other.md"},
                 {"other.md"})) == 1, \
    "an absent path-bearing target reads live"

# 5. A path-bearing target that exists nowhere reads dead even when a tracked file carries the
#    same basename, so the basename rule cannot rescue it.
assert len(probe("it read `docs/design-research/GONE.md`.\n", {"docs/GONE.md"},
                 {"GONE.md"})) == 1, \
    "the basename rule rescued an absent path-bearing target"

# 6. A line carrying the quoted-original marker is not a citation, and the same line without the
#    marker still is. A dated correction quotes the text it corrects, so the dead path it names is
#    quoted history; the marker is what says so, and it is line-scoped.
quoted = "The original sentence read `RAW-FINDINGS.md` and named it as the source."
marked = quoted + " " + census.QUOTED_ORIGINAL_FLAG

unmarked_bad = probe(quoted + "\n", {"docs/other.md"}, {"other.md"})
assert len(unmarked_bad) == 1, f"the unmarked quote stops counting as dead: {unmarked_bad}"

skipped = []
marked_bad = census.dead_refs_in_text("planted.md", marked + "\n", "",
                                      {"docs/other.md"}, {"other.md"}, skipped, disk=False)
assert marked_bad == [], f"a marked quoted original is still counted dead: {marked_bad}"
assert skipped == [("planted.md", 1)], f"the marked line was not reported as skipped: {skipped}"

# 7. The marker is line-scoped: a dead path on the next line is still counted.
two_lines = marked + "\nThe next sentence also reads `RAW-FINDINGS.md`.\n"
skipped = []
two_bad = census.dead_refs_in_text("planted.md", two_lines, "",
                                   {"docs/other.md"}, {"other.md"}, skipped, disk=False)
assert len(two_bad) == 1, f"the marker skipped a line it does not sit on: {two_bad}"
assert skipped == [("planted.md", 1)], f"the marker skipped a line it does not sit on: {skipped}"


def cited_refs():
    """Every backticked .md reference the corpus carries, live or dead.

    A coarse statistic rather than a second resolver: it only has to know which kinds of
    citation the corpus still exercises, so the regex is the same shape and nothing else.
    """
    texts = census.subject_texts(census.md_files())
    skip = set(census.catalogues(texts))
    out = set()
    for f, text in texts.items():
        if f in skip:
            continue
        out.update(re.findall(r"`((?:[\w./&-]+/)?[\w.-]+\.md)`", text))
    return out


# The corpus-level properties, and what actually has to hold. Each says the corpus still
# exercises one rule, computed from the tree rather than pinned to a path, so a citation can be
# repointed without spending the assertion. A corpus that stops exercising a rule is a real
# signal, and it is the rule that should be looked at, not a resolver assertion.
cited = cited_refs()
assert any("/" not in r and r in names for r in cited), \
    "the corpus no longer cites a bare name that a tracked file's basename covers"
assert any("/" in r for r in cited), \
    "the corpus no longer cites a path-bearing ref"
assert any("/" not in r and r not in names for r in dead_refs), \
    "the corpus no longer carries a dead bare name that no tracked file covers"
assert any("/" in r for _, _, r, _ in dead), \
    "the corpus no longer carries a dead path-bearing ref"

print(f"resolver check passed against {TARGET}: {len(dead)} dead, {len(dead_refs)} distinct targets")
