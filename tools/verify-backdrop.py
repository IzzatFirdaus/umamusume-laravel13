#!/usr/bin/env python3
"""Verify the identity backdrop plates stay inside their contrast band.

The two G-18 pairs whose background is `page` -- `rule on page` (3:1 boundary) and
`brand-accent-ink on page` (4.5:1 text) -- bound the luminance any page fill may take.
Each shipped plate is a duotone generated inside that interval, so contrast holds under
arbitrary text. This re-measures the decoded bytes, which is the part a re-generate can
break: WebP chroma error pushed an earlier dark plate to 0.0158 against a 0.0105 cap.

Run: python tools/verify-backdrop.py
Exit 0 = every plate inside its band. Exit 1 = at least one drifted; re-generate.
"""

from __future__ import annotations

import re
import sys
from pathlib import Path

import numpy as np
from PIL import Image

ROOT = Path(__file__).resolve().parent.parent
IMG = ROOT / "resources" / "img"

# The band is DERIVED from the shipped tokens on every run rather than kept as a second
# constant: `rule on page` (3:1, the card boundary) and `brand-accent-ink on page` (4.5:1)
# are the only two G-18 pairs whose background is `page`, so together they set the darkest
# page a plate may paint. The ceiling is pure white, which always passes. Re-colour the
# ramp and this follows; a hand-copied floor would silently go stale, which is exactly the
# drift that shipped a 0.868 floor as an 0.808 band.
PLATE_BOUND_PAIRS = (("rule", "page", 3.0), ("brand-accent-ink", "page", 4.5))
# eva01 prints several more foregrounds directly on `page`, and its page is DARK, so each is a
# ceiling rather than a floor. `risk` is the tightest of them at 0.0318.
EVA01_EXTRA_BOUND_PAIRS = (
    ("risk", "page", 4.5),
    ("brand-accent-ink", "page", 4.5),
    ("ink-muted", "page", 4.5),
    ("status-positive", "page", 4.5),
    ("status-caution", "page", 4.5),
    ("brand-accent", "page", 4.5),
    ("rule", "page", 3.0),
)
FILES = {
    "daybreak": ("backdrop-daybreak-1920.webp", "backdrop-daybreak-2560.webp"),
    "midnight": ("backdrop-midnight-1920.webp", "backdrop-midnight-2560.webp"),
    "eva01": ("backdrop-eva01-1920.webp", "backdrop-eva01-2560.webp"),
}
# Light plates are encoded lossy and the dark band is 0.011 wide, so each theme carries its
# own tolerance: the dark one is a lossless round-trip and may be held tight.
TOLERANCE = {"daybreak": 0.004, "midnight": 0.0006, "eva01": 0.004}
CSS = ROOT / "resources" / "css" / "app.css"

WEIGHTS = np.array([0.2126, 0.7152, 0.0722], dtype=float)


def linearise(rgb: np.ndarray) -> np.ndarray:
    scaled = np.asarray(rgb, dtype=float) / 255.0

    return np.where(scaled <= 0.04045, scaled / 12.92, ((scaled + 0.055) / 1.055) ** 2.4)


def luminance(path: Path) -> tuple[float, float]:
    decoded = np.asarray(Image.open(path).convert("RGB"), dtype=float)
    lum = linearise(decoded).dot(WEIGHTS)

    return float(lum.min()), float(lum.max())


def hex_luminance(value: str) -> float:
    """Linear Rec.709 Y of a `#rrggbb` string."""

    digits = value.lstrip("#")
    if len(digits) == 3:
        digits = "".join(c * 2 for c in digits)
    channels = np.array([int(digits[i : i + 2], 16) for i in (0, 2, 4)], dtype=float)

    return float(linearise(channels).dot(WEIGHTS))


def resolve_light_tokens() -> dict[str, str]:
    """The light `--color-*` declarations, read from the `@theme static` block.

    Only the base block is read: it is the light theme, and both plates are bounded by
    whichever theme paints them, so the light floor is the one that constrains daybreak
    while midnight's own band comes from the dark block below.
    """

    source = CSS.read_text(encoding="utf-8")
    start = source.find("@theme static")
    if start < 0:
        raise SystemExit("verify-backdrop: no `@theme static` block in app.css")
    body_start = source.find("{", start)
    depth = 0
    end = body_start
    for index in range(body_start, len(source)):
        if source[index] == "{":
            depth += 1
        elif source[index] == "}":
            depth -= 1
            if depth == 0:
                end = index
                break
    block = source[body_start + 1 : end]
    block = re.sub(r"/\*.*?\*/", "", block, flags=re.S)

    return dict(re.findall(r"--color-([a-z0-9-]+)\s*:\s*(#[0-9A-Fa-f]{3,8})\s*;", block))


def resolve_theme_tokens(selector: str) -> dict[str, str]:
    """The `--color-*` declarations of one theme block, comments stripped."""

    source = CSS.read_text(encoding="utf-8")
    start = source.find(selector)
    if start < 0:
        raise SystemExit(f"verify-backdrop: no `{selector}` block in app.css")
    body_start = source.find("{", start)
    depth = 0
    end = body_start
    for index in range(body_start, len(source)):
        if source[index] == "{":
            depth += 1
        elif source[index] == "}":
            depth -= 1
            if depth == 0:
                end = index
                break
    block = re.sub(r"/\*.*?\*/", "", source[body_start + 1 : end], flags=re.S)

    return dict(re.findall(r"--color-([a-z0-9-]+)\s*:\s*(#[0-9A-Fa-f]{3,8})\s*;", block))


def daybreak_band(tokens: dict[str, str]) -> tuple[float, float]:
    """The darkest and lightest page luminance the light G-18 page pairs permit."""

    floor = 0.0
    for foreground, _background, threshold in PLATE_BOUND_PAIRS:
        if foreground not in tokens:
            raise SystemExit(f"verify-backdrop: --color-{foreground} missing from the light block")
        foreground_luminance = hex_luminance(tokens[foreground])
        # (page + 0.05) / (fg + 0.05) >= threshold, page being the lighter side.
        floor = max(floor, threshold * (foreground_luminance + 0.05) - 0.05)

    return floor, 1.0


def eva01_band(tokens: dict[str, str]) -> tuple[float, float]:
    """The darkest and lightest page luminance eva01's own page pairs permit.

    eva01's page is dark, so the binding pairs are the ones that print a light foreground
    ON the page: each sets a CEILING (a lighter page shrinks the ratio), and the tightened
    of them governs. The floor is black, which always passes.
    """

    ceiling = 1.0
    for foreground, _background, threshold in PLATE_BOUND_PAIRS + EVA01_EXTRA_BOUND_PAIRS:
        if foreground not in tokens:
            continue
        foreground_luminance = hex_luminance(tokens[foreground])
        # (fg + 0.05) / (page + 0.05) >= threshold  ->  page <= (fg + 0.05) / threshold - 0.05
        ceiling = min(ceiling, (foreground_luminance + 0.05) / threshold - 0.05)

    return 0.0, ceiling


def main() -> int:
    tokens = resolve_light_tokens()
    bands = {
        "daybreak": daybreak_band(tokens),
        # Midnight keeps its literal interval: it is the dark block's own arithmetic, not the
        # light floor, and the dark ramp is untouched by the daybreak re-colour.
        "midnight": (0.00044, 0.01052),
        "eva01": eva01_band(resolve_theme_tokens("html[data-theme='eva01']")),
    }
    failures: list[str] = []

    for theme, (low_bound, high_bound) in bands.items():
        tolerance = TOLERANCE[theme]
        for name in FILES[theme]:
            path = IMG / name
            if not path.is_file():
                failures.append(f"{name}: missing")
                continue

            low, high = luminance(path)
            inside = low >= low_bound - tolerance and high <= high_bound + tolerance
            print(
                f"{name}: Y {low:.5f}..{high:.5f} "
                f"floor {low_bound:.5f}..{high_bound:.5f} {'ok' if inside else 'DRIFTED'}"
            )

            if not inside:
                failures.append(f"{name}: {low:.5f}..{high:.5f} outside {low_bound:.5f}..{high_bound:.5f}")

    for failure in failures:
        print(f"FAIL {failure}")

    return 1 if failures else 0


if __name__ == "__main__":
    sys.exit(main())
