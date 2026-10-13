from pathlib import Path

p = Path("D:/Projects/umamusume-laravel13/research-scratch/assembled-body.v3.md")
t = p.read_text(encoding="utf-8")
pairs = [
    ("local exports `", "data export datasets `"),
    ("local export `", "data export `"),
    ("Local export ", "Export "),
    ("the local `", "the `"),
    ("local `", "`"),
    ("local GameTora export", "GameTora data export"),
    ("local export", "data export"),
    ("local records", "export records"),
    ("No local record", "No export record"),
    ("local `", "`"),
]
for a, b in pairs:
    t = t.replace(a, b)
p.write_text(t, encoding="utf-8")
print("remaining 'local':", t.lower().count("local"))
for i, ln in enumerate(t.splitlines(), 1):
    if "local" in ln.lower():
        print("  %d: %s" % (i, ln[:190]))
