#!/usr/bin/env python
"""One runnable check for `doc_census.py`'s citation resolver, both directions.

`DocCitationParityTest` shells out to the census and gates the total, so a resolver that wrongly
resolved live citations would lower that total and pass the gate for the wrong reason. Asserted
here because the census prints only the first twenty dead links: an assertion on its stdout
cannot fail for the reason it claims.

    python tools/doc_census_selftest.py
    python tools/doc_census_selftest.py tools/.doc_census_head.py   # a candidate resolver to falsify

Exits non-zero when the resolver over-resolves or under-resolves. Run it against the copy of the
pre-2026-10-12 resolver kept beside it to see the first two assertions fail.
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

# 3. Widening must not have neutered detection. `docs/design-research/` was retired, so its files
#    are absent everywhere and the corpus's 61 provenance lines naming one stay dead.
assert not os.path.exists(os.path.join(census.REPO, "docs/design-research/CONSTRAINTS.md"))
assert "docs/design-research/CONSTRAINTS.md" in dead_refs, "a genuinely deleted target reads live"

# 4. A deleted file with a name no tracked markdown carries cannot be resolved by the basename rule.
assert "RAW-FINDINGS.md" not in tracked and "RAW-FINDINGS.md" not in {os.path.basename(p) for p in tracked}
assert "RAW-FINDINGS.md" in dead_refs, "the bare-name rule resolved an absent file"

print(f"resolver check passed against {TARGET}: {len(dead)} dead, {len(dead_refs)} distinct targets")
