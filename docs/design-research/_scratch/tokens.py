"""Derive Tailwind v4 ramps from measured anchors and compute WCAG contrast for the real pairs."""
import json
import math
import os

OUT = r"docs/design-research/_scratch"

# anchors measured in RAW-FINDINGS 3.2
ANCHORS = {
    "action-green": "#7FCC09",
    "banner-green": "#52C518",
    "confirm-green": "#4E7906",
    "info-blue": "#0088E0",
    "skill-cyan": "#009FE1",
    "mood-great": "#FB5590",
    "mood-good": "#ED8036",
    "discount-orange": "#FF9A2C",
    "selected-gold": "#EFC96A",
    "grade-indigo": "#351F70",
    "alert-crimson": "#800014",
    "ink-body": "#6A5641",
    "ink-strong": "#482720",
    "surface-panel": "#F8F8FB",
    "surface-idle": "#D2D2DB",
    "surface-disabled": "#D0D1D0",
}


def srgb_to_lin(c):
    c /= 255
    return c / 12.92 if c <= 0.04045 else ((c + 0.055) / 1.055) ** 2.4


def lin_to_srgb(c):
    v = 12.92 * c if c <= 0.0031308 else 1.055 * (c ** (1 / 2.4)) - 0.055
    return max(0, min(255, round(v * 255)))


def rel_lum(hexc):
    hexc = hexc.lstrip("#")
    r, g, b = (int(hexc[i : i + 2], 16) for i in (0, 2, 4))
    return 0.2126 * srgb_to_lin(r) + 0.7152 * srgb_to_lin(g) + 0.0722 * srgb_to_lin(b)


def contrast(a, b):
    la, lb = rel_lum(a), rel_lum(b)
    hi, lo = max(la, lb), min(la, lb)
    return round((hi + 0.05) / (lo + 0.05), 2)


# --- ramp generation by linear-light mixing toward white / toward a deep neutral ---
def hex_to_rgb(h):
    h = h.lstrip("#")
    return [int(h[i : i + 2], 16) for i in (0, 2, 4)]


def rgb_to_hex(rgb):
    return "#{:02X}{:02X}{:02X}".format(*[max(0, min(255, round(v))) for v in rgb])


WHITE = [255.0, 255.0, 255.0]
DEEP = [24.0, 18.0, 30.0]  # the cool near-black the client's own shadows read as


def mix(a, b, t):
    """Mix in linear light so tints stay chromatic instead of going milky."""
    la = [srgb_to_lin(v) for v in a]
    lb = [srgb_to_lin(v) for v in b]
    out = [x + (y - x) * t for x, y in zip(la, lb)]
    return [lin_to_srgb(v) for v in out]


# step -> (toward white, toward deep). 500 is the measured anchor, untouched.
LADDER = {
    "50": (0.93, 0.00),
    "100": (0.84, 0.00),
    "200": (0.68, 0.00),
    "300": (0.45, 0.00),
    "400": (0.20, 0.00),
    "500": (0.00, 0.00),
    "600": (0.00, 0.18),
    "700": (0.00, 0.36),
    "800": (0.00, 0.55),
    "900": (0.00, 0.72),
}


def ramp(anchor):
    base = [float(v) for v in hex_to_rgb(anchor)]
    out = {"anchor": anchor.upper()}
    for step, (tw, td) in LADDER.items():
        if tw:
            out[step] = rgb_to_hex(mix(base, WHITE, tw))
        elif td:
            out[step] = rgb_to_hex(mix(base, DEEP, td))
        else:
            out[step] = anchor.upper()
    return out


ramps = {}
for name, hexc in ANCHORS.items():
    ramps[name] = ramp(hexc)

PAIRS = [
    ("ink-body on surface-panel", ANCHORS["ink-body"], ANCHORS["surface-panel"]),
    ("ink-body on white", ANCHORS["ink-body"], "#FFFFFF"),
    ("ink-strong on surface-panel", ANCHORS["ink-strong"], ANCHORS["surface-panel"]),
    ("white on action-green", "#FFFFFF", ANCHORS["action-green"]),
    ("white on banner-green", "#FFFFFF", ANCHORS["banner-green"]),
    ("white on confirm-green", "#FFFFFF", ANCHORS["confirm-green"]),
    ("white on info-blue", "#FFFFFF", ANCHORS["info-blue"]),
    ("white on skill-cyan", "#FFFFFF", ANCHORS["skill-cyan"]),
    ("white on mood-great", "#FFFFFF", ANCHORS["mood-great"]),
    ("white on mood-good", "#FFFFFF", ANCHORS["mood-good"]),
    ("white on discount-orange", "#FFFFFF", ANCHORS["discount-orange"]),
    ("ink-body on selected-gold", ANCHORS["ink-body"], ANCHORS["selected-gold"]),
    ("ink-body on surface-idle", ANCHORS["ink-body"], ANCHORS["surface-idle"]),
    ("ink-body on surface-disabled", ANCHORS["ink-body"], ANCHORS["surface-disabled"]),
    ("action-green on surface-panel", ANCHORS["action-green"], ANCHORS["surface-panel"]),
    ("info-blue on surface-panel", ANCHORS["info-blue"], ANCHORS["surface-panel"]),
    ("alert-crimson on surface-panel", ANCHORS["alert-crimson"], ANCHORS["surface-panel"]),
    ("grade-indigo on white", ANCHORS["grade-indigo"], "#FFFFFF"),
    ("discount-orange on surface-panel", ANCHORS["discount-orange"], ANCHORS["surface-panel"]),
    ("mood-great on surface-panel", ANCHORS["mood-great"], ANCHORS["surface-panel"]),
]


def grade(ratio, large=False):
    if large:
        return "AA" if ratio >= 3.0 else "fail"
    return "AAA" if ratio >= 7.0 else ("AA" if ratio >= 4.5 else ("AA-large" if ratio >= 3.0 else "FAIL"))


print("=== WCAG contrast, measured anchors ===")
for label, a, b in PAIRS:
    r = contrast(a, b)
    print(f"{label:<38} {a} / {b}  {r:>6}  normal:{grade(r):<9} large:{grade(r, True)}")

print("\n=== derived ramps (500 = measured anchor, verbatim) ===")
for name, r in ramps.items():
    print(f"{name:<18} " + "  ".join(f"{k}:{r[k]}" for k in LADDER))

print("\n=== which ramp steps can carry white text / be read as text on panel ===")
for name, r in ramps.items():
    cells = []
    for k in LADDER:
        w = contrast("#FFFFFF", r[k])
        on_panel = contrast(r[k], "#F8F8FB")
        mark = "W" if w >= 4.5 else ("w" if w >= 3 else ".")
        mark += "T" if on_panel >= 4.5 else ("t" if on_panel >= 3 else ".")
        cells.append(f"{k}={mark}")
    print(f"{name:<18} " + " ".join(cells))

with open(os.path.join(OUT, "tokens.json"), "w", encoding="utf-8") as fh:
    json.dump({"anchors": ANCHORS, "ramps": ramps, "contrast": {lbl: contrast(a, b) for lbl, a, b in PAIRS}}, fh, indent=1)
