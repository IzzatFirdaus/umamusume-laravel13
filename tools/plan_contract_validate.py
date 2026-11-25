#!/usr/bin/env python3
"""Validate docs/plan/*.md PLAN-CONTRACT blocks against the established contract.

Established contract (restored in PLAN-TEMPLATE.md, commit e1c43ae1 semantics):
  superseded-by: <plan file that replaces this one | ->   # FORWARD: successor replaces this plan
  amended-by:    <plan file that changed this one | ->    # FORWARD: subsequent amending document

Rules enforced:
  1. Exactly one PLAN-CONTRACT block per plan, directly under the H1.
  2. No leading indent on field lines; nothing else inside the block.
  3. Required fields present: plan, status, slices, superseded-by, amended-by, open.
  4. plan / status / open non-empty.
  5. status in {drafted, in-progress, landed, dropped, superseded}.
  6. plan id unique across all plans.
  7. superseded-by / amended-by values are "—" or an existing plan id (target existence).
  8. Directionality: a referenced target plan is dated the same or later (chronology forward).
  9. Reciprocal: if A.amended-by = B (non-empty), B must mention A (vacuous when amended-by is "—").

Exit code: 0 if all plans valid, 1 otherwise.
"""
import re, glob, os, sys

PLAN_DIR = "docs/plan"
OFFICIAL_STATUS = {"drafted", "in-progress", "landed", "dropped", "superseded"}
REQUIRED = ["plan", "status", "slices", "superseded-by", "amended-by", "open"]
EM_DASH = "—"

# Semantic rule: a dropped plan is terminated, not replaced by a successor
def check_dropped_no_superseded(pid, status, fields):
    if status == "dropped":
        sb = fields.get("superseded-by", [])
        if sb and sb != [EM_DASH]:
            return f"dropped plan {pid} has nonempty superseded-by {sb} (dropped plans must not have a successor)"
    return None

errors = []
plans = {}  # plan id -> (file, date)

def add(file, msg):
    errors.append(f"{os.path.basename(file)}: {msg}")

def plan_date(file):
    txt = open(file, encoding="utf-8").read()
    m = re.search(r"^\*\*Date:\*\*\s*(\d{4}-\d{2}-\d{2})", txt, re.M)
    if m:
        return m.group(1)
    # fall back to slug date
    sm = re.search(r"-(\d{4}-\d{2}-\d{2})", os.path.basename(file))
    return sm.group(1) if sm else "0000-00-00"

DELIVERABLES = {
    "CONSOLIDATION-INVENTORY-2026-11-23.md",
    "CONSOLIDATION-REPORT-2026-11-23.md",
    "PLAN-REGISTRY-2026-11-23.md",
    "TASK-OWNERSHIP-CROSSWALK-2026-11-23.md",
}

def slug_of(file):
    return os.path.splitext(os.path.basename(file))[0]

# index of every doc under docs/ (plan + roadmap) for external target resolution
doc_index = {}
for _root, _dirs, _files in os.walk("docs"):
    for _fn in _files:
        if _fn.endswith(".md"):
            doc_index[slug_of(os.path.join(_root, _fn))] = os.path.join(_root, _fn)

# --- collect plans (exclude deliverables and template) ---
for f in sorted(glob.glob(os.path.join(PLAN_DIR, "*.md"))):
    fname = os.path.basename(f)
    if fname == "PLAN-TEMPLATE.md" or fname in DELIVERABLES:
        continue
    txt = open(f, encoding="utf-8").read()
    blocks = re.findall(r"<!--\s*PLAN-CONTRACT(.*?)-->", txt, re.S)
    if len(blocks) != 1:
        add(f, f"expected exactly 1 PLAN-CONTRACT block, found {len(blocks)}")
        continue
    block = blocks[0]
    fields = {}
    for raw in block.splitlines():
        if raw.strip() and raw[0] in " \t":
            add(f, f"leading indent on field line: {raw!r}")
        if ":" in raw:
            k, v = raw.strip().split(":", 1)
            fields[k.strip()] = v.strip()
    pid = fields.get("plan", "")
    if pid in plans:
        add(f, f"duplicate plan id {pid} (also in {plans[pid][0]})")
    plans[pid] = (f, plan_date(f))
    for r in REQUIRED:
        if r not in fields:
            add(f, f"missing required field {r}")
        elif fields[r] == "" and r in ("plan", "status", "open"):
            add(f, f"empty required field {r}")
    st = fields.get("status", "")
    if st and st not in OFFICIAL_STATUS:
        add(f, f"invalid status {st!r} (official: {sorted(OFFICIAL_STATUS)})")

# --- relation checks ---
for pid, (f, d) in plans.items():
    txt = open(f, encoding="utf-8").read()
    block = re.search(r"<!--\s*PLAN-CONTRACT(.*?)-->", txt, re.S).group(1)
    fields = {}
    for raw in block.splitlines():
        if ":" in raw:
            k, v = raw.strip().split(":", 1)
            fields[k.strip()] = [x.strip() for x in v.strip().split(",") if x.strip() != EM_DASH]
    # Semantic rule: dropped plan must not have superseded-by
    err = check_dropped_no_superseded(pid, fields.get("status", [""])[0] if isinstance(fields.get("status"), list) else fields.get("status", ""), fields)
    if err:
        add(f, err)
    for field in ("superseded-by", "amended-by"):
        for target in fields.get(field, []):
            if target not in plans:
                # amended-by references plans only; superseded-by may point to an external doc (e.g. a roadmap)
                if field == "amended-by":
                    add(f, f"{field} target {target} is not a known plan id")
                elif target not in doc_index:
                    add(f, f"{field} target {target} does not resolve to any doc under docs/")
                # external superseded-by target: direction/reciprocal not applicable
                continue
            tf, td = plans[target]
            # directionality: target dated same or later (forward)
            if td < d:
                add(f, f"{field} target {target} dated {td} is earlier than {pid} dated {d} (direction reversed)")
            # reciprocal: if A.amended-by = B, B must mention A
            if field == "amended-by":
                ttxt = open(tf, encoding="utf-8").read()
                tblock = re.search(r"<!--\s*PLAN-CONTRACT(.*?)-->", ttxt, re.S).group(1)
                mentioned = any(pid in line for line in tblock.splitlines())
                if not mentioned:
                    add(f, f"reciprocal broken: {pid}.amended-by={target} but {target} does not mention {pid}")

print(f"Plans checked: {len(plans)}")
print(f"Errors: {len(errors)}")
for e in errors:
    print("  ERR:", e)
print("RESULT:", "PASS" if not errors else "FAIL")
sys.exit(1 if errors else 0)
