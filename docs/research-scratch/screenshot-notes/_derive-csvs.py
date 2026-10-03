import csv
import json
import os
import re

SHA = "0ce49656c816c87befc2787c7007c00845806241cc6f350bab9e3bd04d7c4d1d"
# Moved by the _scratch reorganization of 2026-10-03. It is a frame-clustering output
# filed under analysis/color/, so the bucket name is wrong and the path is current.
CANON = "docs/design-research/_scratch/analysis/color/clusters.json"
NOTES = "research-scratch/screenshot-notes"
PORTRAITS = {"2026-07-14 194819", "2026-07-14 202142"}

clusters = json.load(open(CANON, encoding="utf-8"))
ident = lambda n: n.replace("Screenshot ", "")[:-4]

old = {r["identifier"]: r for r in csv.DictReader(
    open(os.path.join(NOTES, "_readability-sample.csv"), encoding="utf-8"))}

sample, cl_rows = [], []
for idx, c in enumerate(clusters, start=1):
    rep = ident(c["rep"])
    full = c["dim"] == [1920, 1080]
    note = os.path.exists(os.path.join(NOTES, "Screenshot %s.md" % rep))
    cl_rows.append({
        "cluster_id": "C%03d" % idx, "rep": rep, "count": c["count"],
        "dim_w": c["dim"][0], "dim_h": c["dim"][1],
        "full_size": "yes" if full else "no",
        "note_exists": "yes" if note else "no", "source_sha": SHA})
    if not (full or rep in PORTRAITS):
        continue
    prev = old.get(rep, {})
    text = open(os.path.join(NOTES, "Screenshot %s.md" % rep), encoding="utf-8").read()
    cat = re.search(r"(?m)^1\. \*\*Category:\*\* (\S+)", text).group(1)
    f2 = re.search(r"(?m)^2\. \*\*Screen name:\*\*.*?(?=^3\. )", text, re.S).group(0)
    f4 = re.search(r"(?m)^4\. \*\*Information presented:\*\*.*?(?=^5\. )", text, re.S).group(0)
    name = prev.get("trainee_name", "")
    sample.append({
        "identifier": rep,
        "note_path": "research-scratch/screenshot-notes/Screenshot %s.md" % rep,
        "category": cat,
        "cluster_id": "C%03d" % idx,
        "cluster_count": c["count"],
        "selection": "cluster-rep" if full else "portrait-addition",
        "obstruction": prev.get("obstruction", ""),
        "trainee_name_in_frame": "yes" if name else "no",
        "trainee_name": name,
        "name_in_field2": "yes" if name and name in f2 else ("no" if name else "n/a"),
        "name_in_field4": "yes" if name and name in f4 else ("no" if name else "n/a"),
        "source_sha": SHA})

with open(os.path.join(NOTES, "_screen-clusters.csv"), "w", encoding="utf-8", newline="") as fh:
    w = csv.DictWriter(fh, fieldnames=["cluster_id", "rep", "count", "dim_w", "dim_h",
                                       "full_size", "note_exists", "source_sha"])
    w.writeheader()
    w.writerows(cl_rows)

sample.sort(key=lambda r: r["identifier"])
with open(os.path.join(NOTES, "_readability-sample.csv"), "w", encoding="utf-8", newline="") as fh:
    w = csv.DictWriter(fh, fieldnames=["identifier", "note_path", "category", "cluster_id",
                                       "cluster_count", "selection", "obstruction",
                                       "trainee_name_in_frame", "trainee_name",
                                       "name_in_field2", "name_in_field4", "source_sha"])
    w.writeheader()
    w.writerows(sample)

import collections
print("cluster rows:", len(cl_rows), "full-size:", sum(1 for r in cl_rows if r["full_size"] == "yes"))
print("sample rows:", len(sample))
print("categories:", dict(collections.Counter(r["category"] for r in sample)))
print("field2 yes:", sum(1 for r in sample if r["name_in_field2"] == "yes"),
      "field4 yes:", sum(1 for r in sample if r["name_in_field4"] == "yes"),
      "named:", sum(1 for r in sample if r["trainee_name"]))
print("obstructed:", sum(1 for r in sample if r["obstruction"]))
print("orphans (in CSV, no note file):",
      [r["identifier"] for r in sample
       if not os.path.exists(os.path.join(NOTES, "Screenshot %s.md" % r["identifier"]))])
print("notes with no CSV row:",
      [f for f in os.listdir(NOTES) if f.startswith("Screenshot ")
       and f[11:-3].replace("_", " ") not in {r["identifier"] for r in sample}])
