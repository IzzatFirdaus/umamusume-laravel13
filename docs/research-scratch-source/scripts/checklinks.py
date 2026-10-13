"""HEAD/GET sweep over every distinct link target in the assembled body.

Phase 3 requires citations that resolve. This finds the ones that do not.
"""

import re
import sys
import urllib.error
import urllib.request
from concurrent.futures import ThreadPoolExecutor
from pathlib import Path

S = Path("D:/Projects/umamusume-laravel13/research-scratch")
src = S / (sys.argv[1] if len(sys.argv) > 1 else "assembled-body.v2.md")
urls = sorted(set(re.findall(r"\]\((https?://[^)\s]+)", src.read_text(encoding="utf-8"))))
UA = {"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120 Safari/537.36"}


def check(u):
    try:
        req = urllib.request.Request(u, headers=UA, method="GET")
        with urllib.request.urlopen(req, timeout=30) as r:
            return (u, r.status, len(r.read(2000)))
    except urllib.error.HTTPError as e:
        return (u, e.code, 0)
    except Exception as e:
        return (u, "ERR:" + type(e).__name__, 0)


with ThreadPoolExecutor(max_workers=12) as pool:
    results = list(pool.map(check, urls))

bad = [r for r in results if r[1] != 200]
print("checked:", len(urls))
print("non-200:", len(bad))
for u, code, _ in sorted(bad, key=lambda r: str(r[1])):
    print("  %-6s %s" % (code, u))
Path(S / "link-report.txt").write_text("\n".join("%s\t%s" % (c, u) for u, c, _ in results), encoding="utf-8")
print("wrote link-report.txt")
