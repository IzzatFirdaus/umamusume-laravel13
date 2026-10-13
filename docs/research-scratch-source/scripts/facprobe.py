import json
from pathlib import Path

J = Path("D:/Projects/umamusume-laravel13/research-scratch/data/json")
fac = json.loads((J / "factors.json").read_text(encoding="utf-8"))
print("type:", type(fac).__name__)
if isinstance(fac, dict):
    for k, v in fac.items():
        print(" key", repr(k), "type", type(v).__name__, "len", len(v) if hasattr(v, "__len__") else "")
    probe = fac.get("21") or fac.get(21)
    print(" probe 21:", json.dumps(probe, ensure_ascii=False)[:400] if probe else None)
    if probe is None:
        first = next(iter(fac.values()))
        print(" first value sample:", json.dumps(first, ensure_ascii=False)[:600])
else:
    print(" sample:", json.dumps(fac[0], ensure_ascii=False)[:600])
