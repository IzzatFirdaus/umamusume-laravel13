from pathlib import Path

EMOJI = "\U0001F3C7"
for f in ["front-matter.md", "audit.md", "assembled-body.v3.md", "sec2-roster-diff.md"]:
    p = Path(f)
    if not p.exists():
        continue
    t = p.read_text(encoding="utf-8")
    n = t.count(EMOJI)
    t = (t.replace("no " + EMOJI, "no racing-theme emoji (U+1F3C7)")
          .replace(", " + EMOJI + ",", ", the U+1F3C7 glyph,")
          .replace(" " + EMOJI, " the U+1F3C7 glyph")
          .replace(EMOJI, "the U+1F3C7 glyph"))
    p.write_text(t, encoding="utf-8")
    print(f, "replaced", n, "remaining", t.count(EMOJI))
