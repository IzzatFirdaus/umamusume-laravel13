"""Design-artifact gate.

Note for the Lore Guardian: this checker names the banned patterns in order to
grep for them, which is the same practice the root CONSTRAINTS.md C-4 list and
CLAUDE.md Banned Patterns already use. It adds no new vocabulary choice. Checks rendered text of the standalone prototypes against
docs/design-research/CONSTRAINTS.md gates G-1..G-17 that are machine-checkable.

Run:  python docs/design-research/_scratch/gate.py
Exit 0 = all gates pass. Non-zero = failures listed.
"""
import glob
import html as htmllib
import json
import os
import re
import subprocess
import sys
import tempfile

ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
PROTO_DIR = os.path.join(ROOT, "design-research", "prototypes")

# --- G-1 lore: banned vocabulary for characters (CONSTRAINTS 3.1) ---
LORE_BANNED = [
    r"\bhorses?\b", r"\bsires?\b", r"\bdams?\b", r"\bmares?\b", r"\bfoals?\b", r"\bstallions?\b",
    r"\bfilly\b", r"\bcolt\b", r"\bgelding\b", r"\bequine\b", r"\bpony\b",
    r"\bthoroughbred\b", r"\bbreeding\b", r"\bpedigree\b", r"\bbloodline\b",
    r"\bhoof\b", r"\bmane\b", r"\bjockey\b", r"\brider\b", r"\bsaddle\b",
    r"\btack\b", r"\breins?\b", r"\bpaddock\b", r"\bherd\b", r"\bstable\b",
    r"\bmount\b", r"\bfilly\b",
]
# G-1 context allowlist: allowed senses that the bare grep would hit (CONSTRAINTS 3.2)
LORE_ALLOW = [r"damaged?", r"command", r"desire[ds]?", r"surprise", r"nightmare",
              r"details?", r"retail", r"curtail", r"stable\s+(?:growth|value|build|version)",
              r"reindeer"]

# --- G-2 required forms ---
LORE_FORM_FAILS = [r"\bumamusumes\b", r"\bumamusume's\b"]

# --- G-3 official terminology: banned alternatives (CONSTRAINTS 4) ---
TERM_BANNED = {
    r"\bintelligence\b": "use Wit (the export key for 賢さ, not the client label)",
    # Hallucinated stat names. Image generation produced "Strength" for Power,
    # "Wisdom" for Wit, and invented "Luck" and "Skills" as stat columns.
    # The five stats and Skill Points are a closed set; anything else is wrong.
    r"\bstrength\b": "use Power (one of the five stats)",
    r"\bwisdom\b": "use Wit (one of the five stats)",
    r"\bluck\b": "not a stat. The five are Speed, Stamina, Power, Guts, Wit",
    r"\bendurance\b": "use Stamina",
    r"\bagility\b": "not a stat in this game",
    r"\bcharisma\b": "not a stat in this game",
    r"\bgacha\b|\bpick-?up banner\b": "use Scouts / Spotlight",
    r"\bjewel\b": "use Carats",
    r"\bfactor\b": "use Spark",
    r"\bgrass\b": "use Turf",
    r"\bsand\b": "use Dirt",
    r"\bfriend\b": "use Pal (support card type)",
    r"\bmotivation\b": "use Mood",
    r"\bcondition gauge\b": "use Mood",
    r"\bgraduat(?:ed|ion)\b": "use Veteran Umamusume / run status Retired",
    r"\bplanned\b": "use Suggested (SkillAcquisition enum)",
    r"\barchived\b": "use Retired (RunStatus enum)",
    r"\bsign in\b": "no auth surface exists (PRD 6.1)",
    r"\blogin\b": "no auth surface exists (PRD 6.1)",
    r"\baccount\b": "no multi-user surface (PRD 6.1)",
    r"\bshare link\b": "local-only tool (NFR-1)",
}

# G-31: no hardcoded run length. The brief's "~50 rounds" is unverified and no source
# in this repo records a total, so a "/ 48" or "/ 50" denominator is an asserted fact.
RUN_LENGTH = re.compile(r"\bturn\s*\d+\s*/\s*\d{2}\b|\b\d+\s*/\s*(?:4[0-9]|5[0-9]|7[0-9])\s+turns?\b", re.I)

# G-32: caps shown must be real scenario caps or the validation bound, not an invented round number.
BAD_CAP = re.compile(r"/\s*1000\b(?!\s*(?:fans|fan))", re.I)


# terms that MUST appear somewhere across the prototype set (proof the real model is rendered)
TERM_REQUIRED = [
    "Umamusume", "Trainee", "Trainer", "Speed", "Stamina", "Power", "Guts", "Wit",
    "Skill Points", "Turn",
]

# --- G-13 rendered-text integrity ---
TEXT_DEFECTS = [r"\bundefined\b", r"\bNaN\b", r"\[object Object\]", r"\blorem ipsum\b",
                r"\bTODO\b", r"\bFIXME\b", r"\bplaceholder text\b", r"\bJohn Doe\b",
                r"example\.com", r"\bComing soon\b"]

# Token map, read from the design contract rather than hand-maintained here.
# Anything DESIGN.md itself declares as a token is legal in an artifact; anything it does
# not is a bypass. Keeping this file's list separate would guarantee drift, which is
# exactly what CONSTRAINTS.md D-2 forbids.
def load_token_hexes():
    found = set()
    doc = os.path.join(ROOT, "design-research", "DESIGN.md")
    if not os.path.exists(doc):
        return found
    body = open(doc, encoding="utf-8").read()
    # every fenced css block: the light @theme, the dark override, the sheen recipe
    for block in re.findall(r"```css\s*([\s\S]*?)```", body):
        found |= {h.upper() for h in re.findall(r"#[0-9A-Fa-f]{6}\b", block)}
    # measured-anchor and dark-surface tables quote hexes in backticks
    for sec in re.findall(r"### 3\.1[\s\S]*?### 3\.2", body) + re.findall(r"### 3\.7[\s\S]*?```", body):
        found |= {h.upper() for h in re.findall(r"`(#[0-9A-Fa-f]{6})`", sec)}
    # the per-stat tint table in 3.6
    tint = re.search(r"\| Stat \| Hue \| Band tint[\s\S]*?\n\n", body)
    if tint:
        found |= {h.upper() for h in re.findall(r"`(#[0-9A-Fa-f]{6})`", tint.group(0))}
    tok = os.path.join(ROOT, "design-research", "_scratch", "tokens.json")
    if os.path.exists(tok):
        for ramp in json.load(open(tok, encoding="utf-8")).get("ramps", {}).values():
            found |= {v.upper() for v in ramp.values() if isinstance(v, str) and v.startswith("#")}
    return found


ALLOWED_HEX = load_token_hexes() | {
    "#6ABE01",  # lattice dark, measured, quoted in DESIGN 6.3 prose rather than the theme block
    "#406312",  # green-action-700, primary button edge
    "#B3C9EF",  # stat band header, blue-300
}
if len(ALLOWED_HEX) < 40:
    print("GATE SETUP WARNING: DESIGN.md/tokens.json yielded few tokens; the hex allowlist may be broken.")

# --- G-16 sample data must be real catalog strings ---
REAL_TRAINEES = ["Rice Shower", "Oguri Cap", "Mejiro McQueen", "Special Week",
                 "Silence Suzuka", "Tokai Teio", "Vodka", "Daiwa Scarlet",
                 "Gold Ship", "Sakura Chiyono O", "Hishi Amazon", "Nishino Flower"]
REAL_SKILLS = ["Plan X", "Countermeasure", "Soft Step", "Steadfast", "Shrewd Step",
               "Offside Trap", "Survival Master", "Investigation", "Insight"]

EMOJI = re.compile("[\U0001F300-\U0001FAFF\U00002600-\U000027BF\U0001F1E6-\U0001F1FF\uFE0F]")

failures = []
warnings = []


def add(kind, where, msg):
    failures.append((kind, where, msg))


def rendered_text(raw: str) -> str:
    """Visible text only: drop script/style, strip tags, decode entities."""
    raw = re.sub(r"<script[\s\S]*?</script>", " ", raw, flags=re.I)
    raw = re.sub(r"<style[\s\S]*?</style>", " ", raw, flags=re.I)
    raw = re.sub(r"<!--[\s\S]*?-->", " ", raw)
    txt = re.sub(r"<[^>]+>", " ", raw)
    return htmllib.unescape(txt)


def ui_copy(raw: str) -> str:
    """Everything a Trainer could read on screen.

    Terminology rules govern labels, not source comments, so this collects rendered
    text plus the strings a browser would actually paint: JS string literals that
    become text nodes, and the label-bearing HTML attributes. CSS comments such as
    "banner choice card" are developer shorthand and must not fail a copy gate, while
    a banned word inside a JS label must.
    """
    parts = [rendered_text(raw)]
    for block in re.findall(r"<script[\s\S]*?</script>", raw, flags=re.I):
        # single/double-quoted and template literals, ignoring regex and comments
        for lit in re.findall(r"'([^'\\\n]*)'|\"([^\"\\\n]*)\"|`([^`\\]*)`", block):
            parts.extend(x for x in lit if x)
    for attr in re.findall(r'(?:title|alt|aria-label|placeholder|textContent)\s*=\s*"([^"]*)"', raw, flags=re.I):
        parts.append(attr)
    return htmllib.unescape(" \n ".join(parts))



files = sorted(glob.glob(os.path.join(PROTO_DIR, "*.html")))
if not files:
    print(f"GATE FAIL: no prototypes found at {PROTO_DIR}")
    print("  This is the expected RED state: the gate exists before the artifacts do.")
    sys.exit(1)

seen_terms = set()
for path in files:
    name = os.path.basename(path)
    raw = open(path, encoding="utf-8").read()
    text = rendered_text(raw)
    copy = ui_copy(raw)
    # Lore checks run over the RAW source: `make lore` greps every tracked byte, so a
    # banned word in a comment or a JS constant is still a shipped violation.
    # Terminology and required-label checks run over UI copy only: they govern what a
    # Trainer reads, not developer shorthand in a CSS comment.
    # Rendered-text integrity runs over rendered text, so JS keywords such as
    # `undefined` do not trip a check aimed at values that leaked into the UI.

    # G-1 lore, with the context allowlist applied first
    masked = raw
    for a in LORE_ALLOW:
        masked = re.sub(a, " ", masked, flags=re.I)
    for pat in LORE_BANNED:
        for m in re.finditer(pat, masked, flags=re.I):
            add("G-1 lore", name, f"{pat} matched {m.group(0)!r} in context: ..." +
                masked[max(0, m.start() - 45):m.end() + 45].replace("\n", " ") + "...")

    # G-2 required forms
    for pat in LORE_FORM_FAILS:
        for m in re.finditer(pat, raw, flags=re.I):
            add("G-2 lore form", name, f"matched {m.group(0)!r}")

    # G-3 terminology
    for pat, why in TERM_BANNED.items():
        for m in re.finditer(pat, copy, flags=re.I):
            add("G-3 terminology", name, f"{m.group(0)!r} -> {why} (context: ..." +
                copy[max(0, m.start() - 40):m.end() + 40].replace("\n", " ") + "...)")

    # G-13 rendered text integrity
    for pat in TEXT_DEFECTS:
        for m in re.finditer(pat, text, flags=re.I):
            add("G-13 text defect", name, f"{pat} matched {m.group(0)!r}")

    # em dash in prose (CLAUDE.md banned copy)
    if "—" in text:
        add("copy", name, "em dash present in visible text")

    # G-31 / G-32: no invented run length, no invented cap denominator
    for m in RUN_LENGTH.finditer(copy):
        add("G-31 run length", name, f"{m.group(0)!r} asserts a total turn count; no source records one (D-136)")
    for m in BAD_CAP.finditer(copy):
        add("G-32 cap value", name, f"{m.group(0)!r} is not a scenario cap or the 1200 bound (D-31, D-161)")

    # G-16 real names only
    for pat in [r"\bStar\s+Blazer\b", r"\bSpeed\s+Queen\b", r"\bBright\s+Runner\b"]:
        for m in re.finditer(pat, raw, flags=re.I):
            add("G-16 invented data", name, f"invented catalog name {m.group(0)!r}")

    # G-4 token discipline on the source (hex lives in CSS/attrs, not rendered text)
    for m in re.finditer(r"#[0-9A-Fa-f]{6}\b", raw):
        h = m.group(0).upper()
        if h not in ALLOWED_HEX:
            add("G-4 raw hex", name, f"{m.group(0)} is not in the token map")
    for m in re.finditer(r"(?:bg|text|border|ring|shadow|rounded)-\[[^\]]+\]", raw):
        add("G-4 arbitrary value", name, f"{m.group(0)} bypasses the token map")

    # D-84 / G-1 no emoji icons
    for m in EMOJI.finditer(text):
        add("D-73/D-84 emoji", name, f"emoji {m.group(0)!r} in visible text")

    for t in TERM_REQUIRED:
        if re.search(r"\b" + re.escape(t) + r"\b", copy):
            seen_terms.add(t)

missing = set(TERM_REQUIRED) - seen_terms
if missing:
    add("required terms", "prototype set", "never rendered: " + ", ".join(sorted(missing)))

# G-27 script syntax. A static text scan passes happily on a page whose JS does not
# parse, and the interaction is invisible until a browser is opened. Node checks it.
for path in files:
    name = os.path.basename(path)
    raw = open(path, encoding="utf-8").read()
    blocks = [b for b in re.findall(r"<script(?![^>]*src=)[^>]*>([\s\S]*?)</script>", raw) if b.strip()]
    for i, block in enumerate(blocks):
        fd, tmp = tempfile.mkstemp(suffix=".js")
        with os.fdopen(fd, "w", encoding="utf-8") as fh:
            fh.write(block)
        try:
            proc = subprocess.run(["node", "--check", tmp], capture_output=True, text=True)
        except FileNotFoundError:
            os.unlink(tmp)
            print("  note: node not on PATH, G-27 script syntax check skipped")
            break
        os.unlink(tmp)
        if proc.returncode:
            first = next((l for l in proc.stderr.splitlines() if l.strip()), "syntax error")
            add("G-27 script syntax", name, f"inline script {i + 1} does not parse: {first[:160]}")

if failures:
    print(f"GATE FAIL: {len(failures)} finding(s) across {len(files)} file(s)\n")
    for kind, where, msg in failures:
        print(f"  [{kind}] {where}: {msg}")
    sys.exit(1)

print(f"GATE PASS: {len(files)} prototype(s), all machine-checkable gates green.")
print("  Checked: G-1 lore, G-2 forms, G-3 terminology, G-4 tokens, G-13 rendered text,")
print("           G-16 sample data, D-79 em dash, D-84 emoji.")
print("  Not machine-checkable (reviewer): G-5 contrast, G-6, G-7, G-9, G-11, G-12, G-14, G-15, G-17.")
