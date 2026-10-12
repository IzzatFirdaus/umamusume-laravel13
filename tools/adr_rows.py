#!/usr/bin/env python3
"""Shared row extraction for the ADR checks: data rows typed into ADR bodies."""
import re

ROW = re.compile(r"\|\s*([^|]+?)\s*\|((?:\s*[^|\n]+\s*\|){2,})\s*$")


def cells(s: str) -> tuple[str, ...]:
    return tuple(n.strip().strip("*").strip().replace(",", "") for n in s.strip().split("|") if n.strip())


def numeric_rows(text: str):
    """Yield (label, values) for markdown table rows whose cells are all plain numbers.

    Thousands separators are folded so `1,200` and `1200` compare equal — that difference
    is how two copies of one table can read as different facts.
    """
    for line in text.split("\n"):
        m = ROW.match(line.strip())
        if not m:
            continue
        label = m.group(1).strip().strip("*").strip("`").lower()
        vals = cells(m.group(2))
        if len(vals) < 3 or len(label) < 3:
            continue
        if not all(v.isdigit() and len(v) <= 5 for v in vals):
            continue
        yield label, vals
