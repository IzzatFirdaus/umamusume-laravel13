"""Build a deterministic evidence packet for a research topic.

A packet gathers the claims, supporting frames, observations, limitations and
contracts that bear on one topic, so an implementation agent can consume the
evidence without re-reading the whole registry.  Generation is read-only and
deterministic: sorted keys, no timestamps, no run ids.
"""
import argparse
import json
import os
import sys
from collections import Counter

HERE = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, HERE)
import screenshots as S  # noqa: E402


def topic_matches(slug, text):
    """True when ``slug`` names ``text`` (case-insensitive, dash/space agnostic)."""
    if not text:
        return False
    t = text.lower()
    s = slug.lower()
    return s in t or s.replace("-", " ") in t or s.replace("_", " ") in t


def load_registry(registry):
    inv = S.read_jsonl(os.path.join(registry, "inventory.jsonl"))
    ev_path = os.path.join(registry, "evidence.jsonl")
    ev = S.read_jsonl(ev_path) if os.path.exists(ev_path) else []
    contracts = {}
    cdir = os.path.join(registry, "contracts")
    if os.path.isdir(cdir):
        for name in sorted(os.listdir(cdir)):
            if name.endswith(".json"):
                with open(os.path.join(cdir, name), encoding="utf-8") as fh:
                    contracts[name] = json.load(fh)
    return inv, ev, contracts


def build_packet(slug, registry):
    inv, ev, contracts = load_registry(registry)
    by_ref = {r["ref"]: r for r in inv}

    claims = [e for e in ev
              if topic_matches(slug, e.get("eid")) or topic_matches(slug, e.get("claim"))]

    contracts_hit = sorted(name for name, c in contracts.items()
                           if topic_matches(slug, c.get("screen_type")))

    frame_refs = set()
    for e in claims:
        frame_refs.update(e.get("supports", []))
    for r in inv:
        for o in r.get("observations", []):
            if (topic_matches(slug, o.get("screen_type"))
                    or any(topic_matches(slug, m) for m in (o.get("visible_mechanics") or []))):
                frame_refs.add(r["ref"])
                break

    frames = [by_ref[ref] for ref in sorted(frame_refs) if ref in by_ref]
    reps = Counter((f.get("cluster_rep") or f["ref"]) for f in frames)
    independent = [f for f in frames if reps[f.get("cluster_rep") or f["ref"]] == 1]
    members = [f for f in frames if reps[f.get("cluster_rep") or f["ref"]] > 1]

    return {
        "inv": inv,
        "by_ref": by_ref,
        "claims": sorted(claims, key=lambda e: e.get("eid") or ""),
        "contracts": contracts_hit,
        "frames": frames,
        "independent": independent,
        "members": members,
    }


def render_markdown(slug, pkt):
    L = ["# Evidence packet: %s" % slug, ""]
    L.append("## Claims")
    if pkt["claims"]:
        for e in pkt["claims"]:
            L.append("- %s [%s] (%s) %s" % (e.get("eid"), e.get("status"),
                                            e.get("confidence") or "-", e.get("claim")))
    else:
        L.append("- (none)")
    L.append("")

    L.append("## Supporting frames")
    if pkt["frames"]:
        for f in pkt["frames"]:
            L.append("- %s (%s, %sx%s, %s) — cluster %s"
                     % (f["ref"], f.get("sid") or "-", f.get("width"), f.get("height"),
                        f.get("state"), f.get("cluster_index")))
    else:
        L.append("- (none)")
    L.append("")

    L.append("## Observations")
    if pkt["frames"]:
        for f in pkt["frames"]:
            for o in f.get("observations", []):
                L.append("- %s: %s" % (f["ref"], o.get("text")))
    else:
        L.append("- (none)")
    L.append("")

    L.append("## Limitations")
    any_lim = False
    for e in pkt["claims"]:
        for lim in e.get("limits", []):
            L.append("- %s: %s" % (e.get("eid"), lim))
            any_lim = True
    if not any_lim:
        L.append("- (none)")
    L.append("")

    L.append("## Relevant contracts")
    if pkt["contracts"]:
        for name in pkt["contracts"]:
            L.append("- %s" % name)
    else:
        L.append("- (none)")
    L.append("")
    return "\n".join(L)


def build_manifest(slug, pkt):
    return {
        "topic": slug,
        "claims": [{"eid": e.get("eid"), "status": e.get("status"),
                    "confidence": e.get("confidence")} for e in pkt["claims"]],
        "supporting_frames": [
            {"ref": f["ref"], "sid": f.get("sid"), "cluster_id": f.get("cluster_index"),
             "independent_context": f in pkt["independent"]} for f in pkt["frames"]],
        "contracts": pkt["contracts"],
        "evidence_strength": {
            "independent_frames": len(pkt["independent"]),
            "cluster_members": len(pkt["members"]),
            "note": ("frames counted by perceptual cluster; near-duplicate cluster members "
                     "are not independent support"),
        },
    }


def main():
    ap = argparse.ArgumentParser(description="Build a deterministic evidence packet.")
    ap.add_argument("--topic", required=True, help="topic slug, e.g. hint-discount")
    ap.add_argument("--out", help="output directory; default packets/<slug>/")
    ap.add_argument("--registry", default=S.REG, help="registry root")
    args = ap.parse_args()

    out = args.out or os.path.join(S.REG, "packets", args.topic)
    pkt = build_packet(args.topic, args.registry)
    md = render_markdown(args.topic, pkt)
    manifest = build_manifest(args.topic, pkt)

    os.makedirs(out, exist_ok=True)
    with open(os.path.join(out, "packet.md"), "w", encoding="utf-8", newline="\n") as fh:
        fh.write(md)
    with open(os.path.join(out, "manifest.json"), "w", encoding="utf-8", newline="\n") as fh:
        fh.write(json.dumps(manifest, ensure_ascii=False, indent=1, sort_keys=True) + "\n")
    print("evidence_packet: wrote %s (%d claims, %d frames)"
          % (out, len(pkt["claims"]), len(pkt["frames"])))
    return 0


if __name__ == "__main__":
    sys.exit(main())
