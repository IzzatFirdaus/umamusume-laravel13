"""Final build: front matter + body + roster diff + audit into UMAMUSUME_REFERENCE.md.

Rewrites only the five section headers that carried an em dash, and asserts that
no other em dash outside verbatim skill names is present.
"""

import re
from pathlib import Path

S = Path("D:/Projects/umamusume-laravel13/research-scratch")
ROOT = Path("D:/Projects/umamusume-laravel13/UMAMUSUME_REFERENCE.md")

front = (S / "front-matter.md").read_text(encoding="utf-8").rstrip()
# registry table cells hold bare urls; make them links so nothing depends on autolinking
def linkify_table(ln):
    if not ln.startswith("|"):
        return ln
    return re.sub(r"(?<![\[(\w])(?<!/)(https?://[^\s|)\]]+)", r"[\1](\1)", ln)

front = "\n".join(linkify_table(ln) for ln in front.splitlines())
body = (S / "assembled-body.v3.md").read_text(encoding="utf-8")
diff = (S / "sec2-roster-diff.md").read_text(encoding="utf-8").rstrip()
audit = (S / "audit.md").read_text(encoding="utf-8").rstrip()

# section headers: em dash replaced by a colon
for n, title in [(1, "Core Game Mechanics"), (2, "Character Roster"),
                 (3, "Live Operations & Events"), (4, "Gacha & Banner Schedule")]:
    body = body.replace("## Section %d — %s" % (n, title), "## Section %d: %s" % (n, title))

# fold the roster diff in right after 2.4, before Section 3
anchor = "## Section 3: Live Operations & Events"
assert anchor in body, "section 3 anchor missing after header rewrite"
body = body.replace(anchor, diff + "\n\n" + anchor, 1)

prose_dashes = [ln for ln in body.splitlines() if "—" in ln and not ln.startswith("| ")]
assert not prose_dashes, "em dash left in prose: %s" % prose_dashes[0][:120]

# a bare backtick URL is not a clickable link: promote every one outside an existing link
def promote(m):
    return "[%s](%s)" % (m.group(1), m.group(1))

before = len(re.findall(r"`(https?://[^`\s]+)`", body))
body = re.sub(r"(?<!\]\()`(https?://[^`\s]+)`(?!\])", promote, body)
after = len(re.findall(r"(?<!\]\()`(https?://[^`\s]+)`(?!\])", body))
print("backtick urls promoted:", before, "remaining:", after)
assert after == 0, "some backtick-only URLs survived"

# bare full urls in prose: wrap them so no renderer's autolinking is relied upon
BARE = re.compile(r"(?<![\[(`\w/])(https?://[^\s<>|\"`,;）]+)")


def linkify(text):
    def rep(m):
        u = m.group(1)
        while u and u[-1] in ".,;:!?":
            u = u[:-1]
        return "[%s](%s)" % (u, u)

    return BARE.sub(rep, text)

body, front, diff, audit = linkify(body), linkify(front), linkify(diff), linkify(audit)
leftover = [m.group(0) for m in BARE.finditer(body) if not body[max(0, m.start() - 1) : m.start()].count("[")]
print("bare urls remaining after linkify (sample):", leftover[:5])

doc = "\n\n".join([front, body.rstrip(), audit]) + "\n"
ROOT.write_text(doc, encoding="utf-8")

print("wrote", ROOT)
print("lines", doc.count("\n") + 1, "chars", len(doc))
print("table rows", sum(1 for ln in doc.splitlines() if ln.startswith("| ") and not ln.startswith("|---|")))
print("links", len(re.findall(r"\]\(https?://", doc)), "distinct", len(set(re.findall(r"\]\((https?://[^)\s]+)", doc))))
for tok in ["⚠️ STALE", "❌ UNVERIFIED", "[SPECULATION]", "[RUMOR]", "[DATAMINE]"]:
    print(tok, doc.count(tok))
print("in-table em dashes (verbatim skill names):", sum(1 for ln in doc.splitlines() if "—" in ln))
